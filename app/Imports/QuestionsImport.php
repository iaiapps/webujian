<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionsImport implements ToCollection, WithHeadingRow, WithValidation
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

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['question'])) {
                continue;
            }

            // Determine type (single, complex, or category)
            $type = strtolower($row['type'] ?? 'single');
            if ($type === 'multiple') {
                $type = 'complex';
            }
            if (! in_array($type, ['single', 'complex', 'category'])) {
                $type = 'single';
            }

            // Parse correct answer based on type
            $correctAnswer = strtoupper(trim($row['correct_answer'] ?? 'A'));

            // Get options from row
            $optionA = trim($row['option_a'] ?? '');
            $optionB = trim($row['option_b'] ?? '');
            $optionC = trim($row['option_c'] ?? '');
            $optionD = trim($row['option_d'] ?? '');
            $optionE = trim($row['option_e'] ?? '');

            // Validate: minimal 3 options (A, B, C) must be filled
            if (empty($optionA) || empty($optionB) || empty($optionC)) {
                continue; // Skip this row
            }

            // Build options array
            $options = [
                ['label' => 'A', 'content' => $optionA, 'order' => 0],
                ['label' => 'B', 'content' => $optionB, 'order' => 1],
                ['label' => 'C', 'content' => $optionC, 'order' => 2],
            ];

            if (! empty($optionD)) {
                $options[] = ['label' => 'D', 'content' => $optionD, 'order' => 3];
            }
            if (! empty($optionE)) {
                $options[] = ['label' => 'E', 'content' => $optionE, 'order' => 4];
            }

            // Validate category format: A:B,B:S,C:B,D:B,E:S
            if ($type === 'category') {
                // Get available labels
                $availableLabels = array_column($options, 'label');

                // Parse and validate
                $pairs = explode(',', $correctAnswer);
                $validPairs = [];
                foreach ($pairs as $pair) {
                    $parts = explode(':', $pair);
                    if (count($parts) === 2) {
                        $label = strtoupper(trim($parts[0]));
                        $value = strtoupper(trim($parts[1]));
                        if (in_array($label, $availableLabels) && in_array($value, ['B', 'S'])) {
                            $validPairs[] = "{$label}:{$value}";
                        }
                    }
                }

                if (empty($validPairs)) {
                    // Default if invalid
                    $correctAnswer = $options[0]['label'].':B,'.$options[1]['label'].':S,'.$options[2]['label'].':S';
                } else {
                    $correctAnswer = implode(',', $validPairs);
                }
            }

            // Get category_id from code
            $categoryCode = strtolower($row['category'] ?? 'pu');
            $categoryId = $this->categories[$categoryCode] ?? ($this->categories['pu'] ?? 1);

            // Create question
            $question = Question::create([
                'user_id' => $this->user->id,
                'category_id' => $categoryId,
                'question_type' => $type,
                'question_text' => $row['question'],
                'correct_answer' => $correctAnswer,
                'explanation' => $row['explanation'] ?? null,
                'difficulty' => 'medium',
            ]);

            // Create options
            foreach ($options as $option) {
                $question->options()->create($option);
            }

            $this->importedCount++;
        }
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
