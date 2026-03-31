<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration',
        'start_date',
        'end_date',
        'show_result',
        'show_explanation',
        'show_ranking',
        'shuffle_questions',
        'is_active',
        'total_questions',
        'attempt_count',
        'score_correct',
        'score_wrong',
        'score_empty',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'show_result' => 'boolean',
        'show_explanation' => 'boolean',
        'show_ranking' => 'boolean',
        'shuffle_questions' => 'boolean',
        'is_active' => 'boolean',
        'total_questions' => 'integer',
        'attempt_count' => 'integer',
        'score_correct' => 'decimal:2',
        'score_wrong' => 'decimal:2',
        'score_empty' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'test_package_questions', 'package_id', 'question_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('test_package_questions.order');
    }

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'test_package_classes', 'package_id', 'class_id')
            ->withTimestamps();
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class, 'package_id');
    }

    public function completedAttempts()
    {
        return $this->testAttempts()->where('status', 'completed');
    }

    // Helper Methods
    public function isAvailable()
    {
        $now = now();

        return $this->is_active
            && $this->start_date <= $now
            && $this->end_date >= $now;
    }

    public function isExpired()
    {
        return $this->end_date < now();
    }

    public function updateTotalQuestions()
    {
        $this->update(['total_questions' => $this->questions()->count()]);
    }

    public function incrementAttemptCount()
    {
        $this->increment('attempt_count');
    }
}
