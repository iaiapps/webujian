<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer',
        'is_correct',
        'is_doubt',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'is_doubt' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
