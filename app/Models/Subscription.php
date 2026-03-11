<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'plan',
        'billing_cycle',
        'payment_method',
        'bank_name',
        'account_number',
        'account_name',
        'amount',
        'proof_of_payment',
        'status',
        'paid_at',
        'confirmed_at',
        'confirmed_by',
        'started_at',
        'expired_at',
        'admin_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function histories()
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expired_at >= now();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isWaitingConfirmation(): bool
    {
        return $this->status === 'waiting_confirmation';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expired_at && $this->expired_at < now());
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWaitingConfirmation($query)
    {
        return $query->where('status', 'waiting_confirmation');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expired_at', '>=', now());
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }
}
