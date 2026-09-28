<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderInstallment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InstallmentReceiptService
{
    /**
     * Record an actually received installment.
     *
     * Idempotent under concurrent requests:
     * the installment row is locked first and a deterministic
     * internal payment reference is used for the payment ledger.
     */
    public function markAsPaid(
        OrderInstallment $installment,
        int $adminUserId,
        ?string $notes = null
    ): OrderInstallment {
        return DB::transaction(function () use (
            $installment,
            $adminUserId,
            $notes
        ) {
            $locked = OrderInstallment::query()
                ->whereKey($installment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'paid') {
                return $locked->fresh([
                    'order',
                    'paidBy',
                ]);
            }

            if ($locked->status === 'cancelled') {
                throw new RuntimeException(
                    'قسط لغوشده قابل ثبت به‌عنوان پرداخت‌شده نیست.'
                );
            }

            $order = Order::query()
                ->whereKey($locked->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === 'cancelled') {
                throw new RuntimeException(
                    'برای سفارش لغوشده نمی‌توان وصول جدید ثبت کرد.'
                );
            }

            if ((float) $locked->amount <= 0) {
                throw new RuntimeException(
                    'مبلغ قسط معتبر نیست.'
                );
            }

            $reference = 'installment-' . $locked->id;

            Payment::query()->firstOrCreate(
                [
                    'authority' => $reference,
                ],
                [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'gateway' => 'internal',
                    'transaction_id' => $reference,
                    'amount' => $locked->amount,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'metadata' => [
                        'payment_method' => 'installment',
                        'payment_flow' => 'internal_installment',
                        'installment_id' => $locked->id,
                        'recorded_by' => $adminUserId,
                    ],
                ]
            );

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by_user_id' => $adminUserId,
                'notes' => filled($notes)
                    ? $notes
                    : $locked->notes,
            ]);

            $allPaid = $order->installments()
                ->where('status', '!=', 'paid')
                ->doesntExist();

            if ($allPaid) {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'installment',
                    'payment_provider' => 'livora',
                ]);
            } else {
                $order->update([
                    'payment_status' => 'pending',
                    'payment_method' => 'installment',
                    'payment_provider' => 'livora',
                ]);
            }

            return $locked->fresh([
                'order',
                'paidBy',
            ]);
        }, attempts: 3);
    }
}
