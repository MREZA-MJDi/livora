<?php

namespace App\Services\Installments;

use App\Models\Order;
use App\Models\OrderInstallment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class InstallmentPlanService
{
    /**
     * Create or refresh an internal Livora installment plan.
     *
     * Flow:
     *
     * Order
     *   ↓
     * Product installment settings
     *   ↓
     * Validate compatible plan
     *   ↓
     * Calculate cash + cheques
     *   ↓
     * Snapshot on order
     *   ↓
     * Create installment records
     */
    public function create(Order $order): Order
    {
        $this->validateOrder($order);

        $lockedOrder = DB::transaction(
            function () use ($order) {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Reload everything we need while the order
                 * is locked.
                 */
                $lockedOrder->load([
                    'items.product',
                    'installments',
                ]);

                $this->validateOrder($lockedOrder);

                if ($lockedOrder->items->isEmpty()) {
                    throw new RuntimeException(
                        'سفارش آیتمی ندارد.'
                    );
                }

                /*
                 * Never silently rebuild a plan that already
                 * contains a paid installment.
                 */
                if (
                    $lockedOrder->installments
                        ->contains(
                            fn (OrderInstallment $installment) =>
                                $installment->status === 'paid'
                        )
                ) {
                    throw new RuntimeException(
                        'این طرح اقساطی دارای قسط پرداخت‌شده است و قابل بازسازی نیست.'
                    );
                }

                $plan =
                    $this->resolvePlanFromOrderProducts(
                        $lockedOrder
                    );

                $this->validatePlan($plan);

                $financial =
                    $this->calculateAmounts(
                        total: (float) $lockedOrder->total,
                        cashPercent: $plan['cash_percent'],
                        chequeCount: $plan['cheque_count'],
                    );

                /*
                 * Save immutable financial snapshot.
                 */
                $this->saveOrderSnapshot(
                    $lockedOrder,
                    $plan,
                    $financial
                );

                /*
                 * Since no installment is paid yet, we can safely
                 * replace a previously generated pending plan.
                 */
                $this->deleteExistingInstallments(
                    $lockedOrder
                );

                $this->createCashInstallment(
                    $lockedOrder,
                    $financial['cash_amount']
                );

                $this->createChequeInstallments(
                    $lockedOrder,
                    $financial['cheque_amounts'],
                    $plan['interval_months']
                );

                return $lockedOrder->fresh([
                    'items',
                    'installments',
                ]);
            }
        );

        return $lockedOrder;
    }

    /**
     * Preview installment plan without persisting anything.
     */
    public function preview(Order $order): array
    {
        $this->validateOrder($order);

        $order->loadMissing([
            'items.product',
        ]);

        if ($order->items->isEmpty()) {
            throw new RuntimeException(
                'سفارش آیتمی ندارد.'
            );
        }

        $plan =
            $this->resolvePlanFromOrderProducts(
                $order
            );

        $this->validatePlan($plan);

        $financial =
            $this->calculateAmounts(
                total: (float) $order->total,
                cashPercent: $plan['cash_percent'],
                chequeCount: $plan['cheque_count'],
            );

        $firstDueDate =
            now()
                ->startOfDay()
                ->addMonths(
                    $plan['interval_months']
                )
                ->toDateString();

        return [
            'enabled' => true,

            'total' =>
                round(
                    (float) $order->total,
                    2
                ),

            'cash_percent' =>
                $plan['cash_percent'],

            'cash_amount' =>
                $financial['cash_amount'],

            'deferred_amount' =>
                $financial['deferred_amount'],

            'remainder_method' =>
                $plan['remainder_method'],

            'cheque_count' =>
                $plan['cheque_count'],

            'cheque_amounts' =>
                $financial['cheque_amounts'],

            'interval_months' =>
                $plan['interval_months'],

            'first_due_date' =>
                $firstDueDate,
        ];
    }

    /**
     * Resolve one compatible installment plan
     * from all products in the order.
     *
     * All installment-enabled products in one order
     * must currently use the same terms.
     */
    protected function resolvePlanFromOrderProducts(
        Order $order
    ): array {
        $plans = $order->items
            ->map(function ($item) {
                $product = $item->product;

                if (!$product) {
                    throw new RuntimeException(
                        'یکی از محصولات سفارش پیدا نشد.'
                    );
                }

                return [
                    'enabled' =>
                        (bool) $product->installment_enabled,

                    'cash_percent' =>
                        (int) (
                            $product->installment_cash_percent
                            ?? 0
                        ),

                    'remainder_method' =>
                        $product->installment_remainder_method,

                    'cheque_count' =>
                        $product->installment_cheque_count !== null
                            ? (int) $product->installment_cheque_count
                            : null,

                    'interval_months' =>
                        $product->installment_interval_months !== null
                            ? (int) $product->installment_interval_months
                            : null,
                ];
            })
            ->values();

        if ($plans->isEmpty()) {
            throw new RuntimeException(
                'شرایط اقساطی برای سفارش پیدا نشد.'
            );
        }

        if (
            $plans->contains(
                fn (array $plan) =>
                    $plan['enabled'] !== true
            )
        ) {
            throw new RuntimeException(
                'حداقل یکی از محصولات این سفارش فروش اقساطی ندارد.'
            );
        }

        $first = $plans->first();

        foreach ($plans->skip(1) as $plan) {
            if (
                $plan['cash_percent']
                !== $first['cash_percent']
                || $plan['remainder_method']
                !== $first['remainder_method']
                || $plan['cheque_count']
                !== $first['cheque_count']
                || $plan['interval_months']
                !== $first['interval_months']
            ) {
                throw new RuntimeException(
                    'شرایط فروش اقساطی محصولات این سفارش یکسان نیست.'
                );
            }
        }

        return $first;
    }

    /**
     * Validate installment configuration.
     */
    protected function validatePlan(
        array $plan
    ): void {
        if (
            $plan['cash_percent'] < 1
            || $plan['cash_percent'] > 99
        ) {
            throw new RuntimeException(
                'درصد پیش‌پرداخت باید بین 1 تا 99 باشد.'
            );
        }

        if (
            $plan['remainder_method'] !== 'cheque'
        ) {
            throw new RuntimeException(
                'روش تسویه باقی‌مانده معتبر نیست.'
            );
        }

        if (
            $plan['cheque_count'] === null
            || $plan['cheque_count'] < 1
        ) {
            throw new RuntimeException(
                'تعداد چک باید حداقل یک عدد باشد.'
            );
        }

        if (
            $plan['interval_months'] === null
            || $plan['interval_months'] < 1
        ) {
            throw new RuntimeException(
                'فاصله سررسید چک معتبر نیست.'
            );
        }
    }

    /**
     * Calculate cash amount, deferred amount,
     * and individual cheque amounts.
     *
     * The last cheque absorbs rounding difference so:
     *
     * cash + cheque total = order total
     */
    protected function calculateAmounts(
        float $total,
        int $cashPercent,
        int $chequeCount
    ): array {
        if ($total <= 0) {
            throw new RuntimeException(
                'مبلغ سفارش معتبر نیست.'
            );
        }

        if (
            $cashPercent < 1
            || $cashPercent > 99
        ) {
            throw new RuntimeException(
                'درصد پیش‌پرداخت معتبر نیست.'
            );
        }

        if ($chequeCount < 1) {
            throw new RuntimeException(
                'تعداد چک معتبر نیست.'
            );
        }

        $total = round(
            $total,
            2
        );

        $cashAmount = round(
            $total * ($cashPercent / 100),
            2
        );

        $deferredAmount = round(
            $total - $cashAmount,
            2
        );

        $baseChequeAmount = round(
            $deferredAmount / $chequeCount,
            2
        );

        $chequeAmounts = [];

        $distributed = 0.0;

        for (
            $i = 1;
            $i <= $chequeCount;
            $i++
        ) {
            if (
                $i === $chequeCount
            ) {
                /*
                 * Last cheque absorbs any rounding
                 * difference.
                 */
                $amount = round(
                    $deferredAmount - $distributed,
                    2
                );
            } else {
                $amount = $baseChequeAmount;
            }

            if ($amount <= 0) {
                throw new RuntimeException(
                    'یکی از مبالغ چک‌ها معتبر نیست.'
                );
            }

            $chequeAmounts[] =
                $amount;

            $distributed =
                round(
                    $distributed + $amount,
                    2
                );
        }

        /*
         * Final arithmetic safety check.
         */
        $chequeTotal =
            round(
                array_sum($chequeAmounts),
                2
            );

        if (
            round(
                $cashAmount + $chequeTotal,
                2
            ) !== $total
        ) {
            throw new RuntimeException(
                'جمع مبالغ طرح اقساطی با مبلغ سفارش مطابقت ندارد.'
            );
        }

        return [
            'cash_amount' =>
                $cashAmount,

            'deferred_amount' =>
                $deferredAmount,

            'cheque_amounts' =>
                $chequeAmounts,
        ];
    }

    /**
     * Store immutable installment snapshot on order.
     */
    protected function saveOrderSnapshot(
        Order $order,
        array $plan,
        array $financial
    ): void {
        $order->update([
            'payment_method' =>
                'installment',

            'payment_provider' =>
                'livora',

            'installment_enabled' =>
                true,

            'installment_cash_percent' =>
                $plan['cash_percent'],

            'installment_cash_amount' =>
                $financial['cash_amount'],

            'installment_deferred_amount' =>
                $financial['deferred_amount'],

            'installment_remainder_method' =>
                $plan['remainder_method'],

            'installment_cheque_count' =>
                $plan['cheque_count'],

            'installment_interval_months' =>
                $plan['interval_months'],
        ]);
    }

    /**
     * Delete existing pending/unpaid installment rows.
     *
     * Paid rows are protected at the caller level.
     */
    protected function deleteExistingInstallments(
        Order $order
    ): void {
        $order->installments()
            ->where(
                'status',
                '!=',
                'paid'
            )
            ->delete();
    }

    /**
     * Create cash installment.
     */
    protected function createCashInstallment(
        Order $order,
        float $amount
    ): OrderInstallment {
        if ($amount <= 0) {
            throw new RuntimeException(
                'مبلغ پیش‌پرداخت معتبر نیست.'
            );
        }

        return OrderInstallment::create([
            'order_id' =>
                $order->id,

            'sequence' =>
                1,

            'type' =>
                'cash',

            'amount' =>
                $amount,

            'due_date' =>
                now()->toDateString(),

            'status' =>
                'pending',

            'notes' =>
                'پیش‌پرداخت نقدی سفارش',
        ]);
    }

    /**
     * Create cheque installments.
     */
    protected function createChequeInstallments(
        Order $order,
        array $chequeAmounts,
        int $intervalMonths
    ): void {
        if ($intervalMonths < 1) {
            throw new RuntimeException(
                'فاصله سررسید معتبر نیست.'
            );
        }

        foreach (
            $chequeAmounts
            as $index => $amount
        ) {
            $chequeNumber =
                $index + 1;

            $dueDate =
                now()
                    ->startOfDay()
                    ->addMonths(
                        $intervalMonths
                        * $chequeNumber
                    )
                    ->toDateString();

            OrderInstallment::create([
                'order_id' =>
                    $order->id,

                /*
                 * Sequence 1 = cash
                 * Sequence 2+ = cheques
                 */
                'sequence' =>
                    $chequeNumber + 1,

                'type' =>
                    'cheque',

                'amount' =>
                    $amount,

                'due_date' =>
                    $dueDate,

                'status' =>
                    'pending',

                'notes' =>
                    "قسط چک شماره {$chequeNumber}",
            ]);
        }
    }

    /**
     * Validate order state.
     */
    protected function validateOrder(
        Order $order
    ): void {
        if (
            $order->payment_status === 'paid'
        ) {
            throw new RuntimeException(
                'این سفارش قبلاً پرداخت شده است.'
            );
        }

        if (
            $order->status === 'cancelled'
        ) {
            throw new RuntimeException(
                'سفارش لغو شده قابل اقساطی شدن نیست.'
            );
        }

        if (
            (float) $order->total <= 0
        ) {
            throw new RuntimeException(
                'مبلغ سفارش معتبر نیست.'
            );
        }
    }
}
