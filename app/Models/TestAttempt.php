<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'package_id',
        'start_time',
        'end_time',
        'submitted_at',
        'total_score',
        'correct_answers',
        'wrong_answers',
        'unanswered',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'submitted_at' => 'datetime',
        'total_score' => 'decimal:2',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'unanswered' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function package()
    {
        return $this->belongsTo(TestPackage::class, 'package_id');
    }

    public function answers()
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }

    // Helper Methods
    public function isOngoing()
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isExpired()
    {
        return $this->end_time && $this->end_time < now() && $this->status === 'ongoing';
    }

    public function calculateStatistics()
    {
        $totalQuestions = $this->package->total_questions;
        $answeredCount = $this->answers()->count();

        $this->update([
            'correct_answers' => $this->answers()->where('is_correct', true)->count(),
            'wrong_answers' => $this->answers()->where('is_correct', false)->count(),
            'unanswered' => $totalQuestions - $answeredCount,
        ]);
    }

    public function calculateScore()
    {
        $package = $this->package;
        
        $correct = $this->correct_answers;
        $wrong = $this->wrong_answers;
        $empty = $this->unanswered;

        // Scoring dari setting paket tes
        $score = ($correct * $package->score_correct) 
               + ($wrong * $package->score_wrong) 
               + ($empty * $package->score_empty);

        return max(0, $score);
    }
}
