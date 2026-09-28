<?php

namespace App\Services\Inventory;

use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Reserve every stock-bearing item in an order.
     *
     * The caller may already be inside a DB transaction.
     * Every stock row is locked before the quantity check/update.
     */
    public function reserveOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $this->reserveItem($order, $item);
        }
    }

    /**
     * Convert reservations into consumed stock state.
     * Stock was already decremented at reservation time.
     */
    public function consumeOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $reservations = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                if ($reservation->status !== 'reserved') {
                    continue;
                }

                $reservation->update([
                    'status' => 'consumed',
                    'consumed_at' => now(),
                ]);
            }
        }, attempts: 3);
    }

    /**
     * Release any still-reserved stock for an order.
     */
    public function releaseOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $reservations = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                $this->releaseReservation($reservation);
            }
        }, attempts: 3);
    }

    protected function reserveItem(
        Order $order,
        OrderItem $item
    ): void {
        $quantity = (int) $item->quantity;

        if ($quantity < 1) {
            throw new RuntimeException(
                'تعداد یکی از اقلام سفارش معتبر نیست.'
            );
        }

        $variantIds = $item->selectedVariantIds();

        if (empty($variantIds)) {
            $product = Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw new RuntimeException(
                    "محصول «{$item->product_name}» پیدا نشد."
                );
            }

            $reference = implode(':', [
                'order',
                $order->id,
                'item',
                $item->id,
                'variant',
                'base',
            ]);

            $existing = InventoryReservation::query()
                ->where('reference_key', $reference)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return;
            }

            $this->ensureAvailable(
                (int) $product->stock,
                $quantity,
                $product->name
            );

            $product->decrement('stock', $quantity);

            $this->createReservation(
                $order,
                $item,
                $quantity,
                null
            );

            return;
        }

        $variants = ProductVariant::query()
            ->where('product_id', $item->product_id)
            ->whereIn('id', $variantIds)
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($variants->count() !== count(array_unique($variantIds))) {
            throw new RuntimeException(
                "یکی از تنوع‌های «{$item->product_name}» دیگر در دسترس نیست."
            );
        }

        foreach ($variants as $variant) {
            $reference = implode(':', [
                'order',
                $order->id,
                'item',
                $item->id,
                'variant',
                $variant->id,
            ]);

            $existing = InventoryReservation::query()
                ->where('reference_key', $reference)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                continue;
            }

            $this->ensureAvailable(
                (int) $variant->stock,
                $quantity,
                $item->product_name . ' / ' . $variant->value
            );

            $variant->decrement('stock', $quantity);

            $this->createReservation(
                $order,
                $item,
                $quantity,
                $variant->id
            );
        }
    }

    protected function createReservation(
        Order $order,
        OrderItem $item,
        int $quantity,
        ?int $variantId
    ): InventoryReservation {
        $reference = implode(':', [
            'order',
            $order->id,
            'item',
            $item->id,
            'variant',
            $variantId ?? 'base',
        ]);

        return InventoryReservation::query()->firstOrCreate(
            [
                'reference_key' => $reference,
            ],
            [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'status' => 'reserved',
                'reserved_at' => now(),
            ]
        );
    }

    protected function releaseReservation(
        InventoryReservation $reservation
    ): void {
        if ($reservation->status !== 'reserved') {
            return;
        }

        if ($reservation->product_variant_id) {
            $variant = ProductVariant::query()
                ->whereKey($reservation->product_variant_id)
                ->lockForUpdate()
                ->first();

            if ($variant) {
                $variant->increment(
                    'stock',
                    (int) $reservation->quantity
                );
            }
        } elseif ($reservation->product_id) {
            $product = Product::query()
                ->whereKey($reservation->product_id)
                ->lockForUpdate()
                ->first();

            if ($product) {
                $product->increment(
                    'stock',
                    (int) $reservation->quantity
                );
            }
        }

        $reservation->update([
            'status' => 'released',
            'released_at' => now(),
        ]);
    }

    protected function ensureAvailable(
        int $available,
        int $requested,
        string $label
    ): void {
        if ($requested > $available) {
            throw new RuntimeException(
                "موجودی «{$label}» کافی نیست. موجودی فعلی: {$available}"
            );
        }
    }
}
