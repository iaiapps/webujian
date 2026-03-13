<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credit_amount',
        'bonus_credits',
        'price',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'credit_amount' => 'integer',
        'bonus_credits' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('credit_amount');
    }

    // Helpers
    public function getTotalCredits(): int
    {
        return $this->credit_amount + $this->bonus_credits;
    }

    public function getPricePerCredit(): float
    {
        if ($this->getTotalCredits() === 0) {
            return 0;
        }

        return $this->price / $this->getTotalCredits();
    }

    public function hasBonus(): bool
    {
        return $this->bonus_credits > 0;
    }

    public function getFormattedPrice(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }
}
