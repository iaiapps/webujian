<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_id',
        'reference_type',
        'credit_purchase_id',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function creditPurchase()
    {
        return $this->belongsTo(CreditPurchase::class);
    }

    // Scopes
    public function scopeIn($query)
    {
        return $query->whereIn('type', ['purchase', 'bonus', 'manual_add', 'refund']);
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'usage');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers
    public function isIn(): bool
    {
        return in_array($this->type, ['purchase', 'bonus', 'manual_add', 'refund']);
    }

    public function isOut(): bool
    {
        return $this->type === 'usage' || $this->type === 'manual_deduct';
    }

    public function getTypeLabel(): string
    {
        $labels = [
            'purchase' => 'Pembelian',
            'usage' => 'Penggunaan',
            'bonus' => 'Bonus',
            'manual_add' => 'Penambahan Manual',
            'manual_deduct' => 'Pengurangan Manual',
            'refund' => 'Refund',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeBadgeClass(): string
    {
        $classes = [
            'purchase' => 'success',
            'usage' => 'danger',
            'bonus' => 'info',
            'manual_add' => 'primary',
            'manual_deduct' => 'warning',
            'refund' => 'secondary',
        ];

        return $classes[$this->type] ?? 'secondary';
    }
}
