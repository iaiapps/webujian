<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionCategory;

class QuestionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Tes Kemampuan Akademik
            // Numerasi
            [
                'name' => 'Numerasi Matematika',
                'slug' => 'numerasi-matematika',
                'description' => 'Kemampuan berpikir logis, analitis, dan sistematis',
                'order' => 1,
            ],

            // Literasi
            [
                'name' => 'Literasi Bahasa Indonesia',
                'slug' => 'literasi-bahasa-indonesia',
                'description' => 'Kemampuan memahami dan menganalisis teks bahasa Indonesia',
                'order' => 2,
            ],
            [
                'name' => 'Literasi Bahasa Inggris',
                'slug' => 'literasi-bahasa-inggris',
                'description' => 'Kemampuan memahami dan menganalisis teks bahasa Inggris',
                'order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            QuestionCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Question categories created successfully!');
    }
}
