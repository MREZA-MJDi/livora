<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sequence',
        'type',
        'amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Order
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCash(): bool
    {
        return $this->type === 'cash';
    }

    public function isCheque(): bool
    {
        return $this->type === 'cheque';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | Labels
    |--------------------------------------------------------------------------
    */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cash' => 'پیش‌پرداخت نقدی',
            'cheque' => 'چک',
            default => 'نامشخص',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'failed' => 'ناموفق',
            'cancelled' => 'لغو شده',
            default => 'نامشخص',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute(): string
    {
        return number_format(
                (float) $this->amount
            ) . ' تومان';
    }

    public function getFormattedDueDateAttribute(): ?string
    {
        return $this->due_date
            ? $this->due_date->format('Y/m/d')
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Due Date
    |--------------------------------------------------------------------------
    */

    public function isDue(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->status === 'pending'
            && $this->due_date->isToday();
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->status === 'pending'
            && $this->due_date->isBefore(
                today()
            );
    }

    public function isUpcoming(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->status === 'pending'
            && $this->due_date->isAfter(
                today()
            );
    }
}
