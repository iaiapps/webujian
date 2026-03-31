<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'current_count',
        'max_count',
        'is_limit_reached',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'current_count' => 'integer',
        'max_count' => 'integer',
        'is_limit_reached' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
