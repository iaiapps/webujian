<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credit_package_id',
        'mayar_invoice_id',
        'mayar_transaction_id',
        'payment_link',
        'amount',
        'credits_amount',
        'bonus_credits',
        'total_credits',
        'status',
        'expired_at',
        'paid_at',
        'internal_ref',
        'payment_method',
        'mayar_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credits_amount' => 'integer',
        'bonus_credits' => 'integer',
        'total_credits' => 'integer',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'mayar_response' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditPackage()
    {
        return $this->belongsTo(CreditPackage::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->isPending() && $this->expired_at->isPast());
    }

    public function markAsPaid(?string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function getStatusBadgeClass(): string
    {
        $classes = [
            'pending' => 'warning',
            'paid' => 'success',
            'expired' => 'danger',
            'cancelled' => 'secondary',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getFormattedAmount(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }
}
