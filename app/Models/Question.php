<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'question_type',
        'question_text',
        'question_image',
        'correct_answer',
        'explanation',
        'difficulty',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    public function testPackages()
    {
        return $this->belongsToMany(TestPackage::class, 'test_package_questions', 'question_id', 'package_id')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function getOptionByLabel($label)
    {
        return $this->options()->where('label', $label)->first();
    }

    // Helper Methods
    public function checkAnswer($studentAnswer)
    {
        if ($this->question_type === 'single') {
            return strtoupper(trim($this->correct_answer)) === strtoupper(trim($studentAnswer));
        } elseif ($this->question_type === 'complex') {
            // Complex: A,C,E
            $correctAnswers = array_map('trim', array_map('strtoupper', explode(',', $this->correct_answer)));
            $studentAnswers = array_map('trim', array_map('strtoupper', explode(',', $studentAnswer)));

            sort($correctAnswers);
            sort($studentAnswers);

            return $correctAnswers === $studentAnswers;
        } elseif ($this->question_type === 'category') {
            // Category: A:B,B:S,C:B,D:B,E:S
            $correctMap = $this->parseCategoryAnswer($this->correct_answer);
            $studentMap = $this->parseCategoryAnswer($studentAnswer);

            return $correctMap === $studentMap;
        }

        return false;
    }

    private function parseCategoryAnswer($answerString)
    {
        $result = [];
        $pairs = explode(',', $answerString);

        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $statement = strtoupper(trim($parts[0]));
                $value = strtoupper(trim($parts[1]));
                $result[$statement] = $value;
            }
        }

        // Sort by key to ensure consistent comparison
        ksort($result);

        return $result;
    }

    public function getCorrectAnswersArray()
    {
        return explode(',', $this->correct_answer);
    }

    public function isUsed()
    {
        return $this->usage_count > 0;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    // Scopes
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('question_type', $type);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
