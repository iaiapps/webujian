<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    protected $importedCount = 0;
    protected $categories = [];

    public function __construct(User $user)
    {
        $this->user = $user;
        // Cache categories by slug
        $this->categories = QuestionCategory::where('is_active', true)->pluck('id', 'slug')->toArray();
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['question'])) {
            return null;
        }

        // Determine type (single or complex)
        $type = strtolower($row['type'] ?? 'single');
        if ($type === 'multiple') {
            $type = 'complex';
        }
        if (!in_array($type, ['single', 'complex'])) {
            $type = 'single';
        }

        // Parse correct answer
        $correctAnswer = strtoupper(trim($row['correct_answer'] ?? 'A'));

        // Get category_id from code
        $categoryCode = strtolower($row['category'] ?? 'pu');
        $categoryId = $this->categories[$categoryCode] ?? ($this->categories['pu'] ?? 1);

        $this->importedCount++;

        return new Question([
            'user_id' => $this->user->id,
            'category_id' => $categoryId,
            'question_type' => $type,
            'question_text' => $row['question'],
            'option_a' => $row['option_a'] ?? '',
            'option_b' => $row['option_b'] ?? '',
            'option_c' => $row['option_c'] ?? '',
            'option_d' => $row['option_d'] ?? '',
            'option_e' => $row['option_e'] ?? '',
            'correct_answer' => $correctAnswer,
            'explanation' => $row['explanation'] ?? null,
            'difficulty' => 'medium',
        ]);
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'type' => 'nullable|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'correct_answer' => 'required|string',
            'category' => 'nullable|string',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
