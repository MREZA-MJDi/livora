<?php

namespace App\Services\Inventory;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class OrderInventoryService
{
    /**
     * Allocate inventory after an online payment has been confirmed.
     *
     * The caller must hold a write lock on the order and run this method
     * inside the same database transaction as the payment state change.
     * All stock requirements are validated before any decrement is applied.
     */
    public function deductForPaidOrder(Order $order): void
    {
        if (in_array($order->inventory_status, [
            'deducted',
            'stock_conflict',
            'needs_reconciliation',
        ], true)) {
            return;
        }

        $items = OrderItem::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            $this->markConflict($order, [
                'reason' => 'The paid order has no line items.',
                'order_id' => $order->id,
            ]);

            return;
        }

        $productRequirements = [];
        $variantRequirements = [];
        $issues = [];

        foreach ($items as $item) {
            if (! $item->product_id) {
                $issues[] = [
                    'order_item_id' => $item->id,
                    'reason' => 'The product reference is missing.',
                    'product_name' => $item->product_name,
                ];

                continue;
            }

            $selectedVariantIds = collect(
                is_array($item->variant_options)
                    ? $item->variant_options
                    : []
            )
                ->pluck('id')
                ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            // Keep support for pre-snapshot orders containing one variant.
            if (
                $selectedVariantIds === []
                && $item->product_variant_id
            ) {
                $selectedVariantIds = [(int) $item->product_variant_id];
            }

            $quantity = max(0, (int) $item->quantity);

            if ($quantity < 1) {
                $issues[] = [
                    'order_item_id' => $item->id,
                    'reason' => 'The order item quantity is invalid.',
                ];

                continue;
            }

            if ($selectedVariantIds === []) {
                $productId = (int) $item->product_id;
                $productRequirements[$productId] =
                    ($productRequirements[$productId] ?? 0) + $quantity;

                continue;
            }

            foreach ($selectedVariantIds as $variantId) {
                if (
                    isset($variantRequirements[$variantId])
                    && $variantRequirements[$variantId]['product_id'] !== (int) $item->product_id
                ) {
                    $issues[] = [
                        'order_item_id' => $item->id,
                        'variant_id' => $variantId,
                        'reason' => 'A selected variant is associated with multiple products.',
                    ];

                    continue;
                }

                $variantRequirements[$variantId] = [
                    'product_id' => (int) $item->product_id,
                    'quantity' => ($variantRequirements[$variantId]['quantity'] ?? 0) + $quantity,
                ];
            }
        }

        $productIds = collect(array_keys($productRequirements))
            ->merge(collect($variantRequirements)->pluck('product_id'))
            ->unique()
            ->sort()
            ->values();

        // Stable lock order reduces deadlocks when concurrent orders contain
        // overlapping product / variant stock.
        $products = $productIds->isEmpty()
            ? collect()
            : Product::withTrashed()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

        $variantIds = collect(array_keys($variantRequirements))
            ->sort()
            ->values();

        $variants = $variantIds->isEmpty()
            ? collect()
            : ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

        foreach ($productRequirements as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                $issues[] = [
                    'product_id' => $productId,
                    'reason' => 'The product no longer exists.',
                ];

                continue;
            }

            if ((int) $product->stock < $quantity) {
                $issues[] = [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'requested_quantity' => $quantity,
                    'available_stock' => (int) $product->stock,
                    'reason' => 'Insufficient base-product stock after payment.',
                ];
            }
        }

        foreach ($variantRequirements as $variantId => $requirement) {
            $variant = $variants->get($variantId);

            if (
                ! $variant
                || (int) $variant->product_id !== $requirement['product_id']
            ) {
                $issues[] = [
                    'variant_id' => $variantId,
                    'product_id' => $requirement['product_id'],
                    'reason' => 'A selected product variant no longer exists or does not match the purchased product.',
                ];

                continue;
            }

            if ((int) $variant->stock < $requirement['quantity']) {
                $issues[] = [
                    'variant_id' => $variantId,
                    'product_id' => $requirement['product_id'],
                    'variant_name' => $variant->name,
                    'requested_quantity' => $requirement['quantity'],
                    'available_stock' => (int) $variant->stock,
                    'reason' => 'Insufficient variant stock after payment.',
                ];
            }
        }

        // Payment has already been verified by the gateway. Never roll back or
        // report the payment itself as failed just because inventory is short.
        // Flag the order for an administrator and avoid partially deducting it.
        if ($issues !== []) {
            $this->markConflict($order, [
                'order_id' => $order->id,
                'issues' => $issues,
            ]);

            return;
        }

        $adjustments = [];

        foreach ($productRequirements as $productId => $quantity) {
            $product = $products->get($productId);
            $product->decrement('stock', $quantity);

            $adjustments[] = [
                'type' => 'product',
                'product_id' => (int) $productId,
                'quantity' => $quantity,
            ];
        }

        foreach ($variantRequirements as $variantId => $requirement) {
            $variant = $variants->get($variantId);
            $variant->decrement('stock', $requirement['quantity']);

            $adjustments[] = [
                'type' => 'variant',
                'variant_id' => (int) $variantId,
                'product_id' => $requirement['product_id'],
                'quantity' => $requirement['quantity'],
            ];
        }

        $order->update([
            'inventory_status' => 'deducted',
            'inventory_processed_at' => now(),
            'inventory_metadata' => [
                'processed_at' => now()->toISOString(),
                'adjustments' => $adjustments,
            ],
        ]);

        $order->setAttribute('inventory_status', 'deducted');
    }

    private function markConflict(Order $order, array $metadata): void
    {
        $order->update([
            'inventory_status' => 'stock_conflict',
            'inventory_processed_at' => now(),
            'inventory_metadata' => array_merge($metadata, [
                'detected_at' => now()->toISOString(),
                'requires_admin_review' => true,
            ]),
        ]);

        $order->setAttribute('inventory_status', 'stock_conflict');

        Log::critical('Livora confirmed a payment but could not allocate all order inventory.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'inventory_metadata' => $metadata,
        ]);
    }
}
