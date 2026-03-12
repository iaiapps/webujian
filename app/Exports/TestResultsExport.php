<?php

// app/Exports/TestResultsExport.php

namespace App\Exports;

use App\Models\TestPackage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TestResultsExport implements WithMultipleSheets
{
    protected $package;

    public function __construct(TestPackage $package)
    {
        $this->package = $package;
    }

    public function sheets(): array
    {
        return [
            new RankingSheet($this->package),
            new DetailSheet($this->package),
            new QuestionAnalysisSheet($this->package),
        ];
    }
}

class RankingSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $package;

    public function __construct(TestPackage $package)
    {
        $this->package = $package;
    }

    public function collection()
    {
        $attempts = $this->package->completedAttempts()
            // ->with('student.classRoom') // DINONAKTIFKAN
            ->orderBy('total_score', 'desc')
            ->get();

        return $attempts->map(function ($attempt, $index) {
            return [
                'rank' => $index + 1,
                'name' => $attempt->student->name,
                // 'class' => $attempt->student->classRoom ? $attempt->student->classRoom->name : '-', // DINONAKTIFKAN
                'score' => $attempt->total_score,
                'correct' => $attempt->correct_answers,
                'wrong' => $attempt->wrong_answers,
                'unanswered' => $attempt->unanswered,
                'submitted_at' => $attempt->submitted_at->format('d-m-Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Nama Siswa',
            // 'Kelas', // DINONAKTIFKAN
            'Skor',
            'Benar',
            'Salah',
            'Kosong',
            'Waktu Submit',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ranking';
    }
}

class DetailSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $package;

    public function __construct(TestPackage $package)
    {
        $this->package = $package;
    }

    public function collection()
    {
        $attempts = $this->package->completedAttempts()
            // ->with('student.classRoom') // DINONAKTIFKAN
            ->get();

        return $attempts->map(function ($attempt) {
            return [
                'name' => $attempt->student->name,
                'username' => $attempt->student->username,
                // 'class' => $attempt->student->classRoom ? $attempt->student->classRoom->name : '-', // DINONAKTIFKAN
                'start_time' => $attempt->start_time->format('d-m-Y H:i'),
                'submit_time' => $attempt->submitted_at->format('d-m-Y H:i'),
                'duration' => $attempt->start_time->diffInMinutes($attempt->submitted_at).' menit',
                'score' => $attempt->total_score,
                'correct' => $attempt->correct_answers,
                'wrong' => $attempt->wrong_answers,
                'unanswered' => $attempt->unanswered,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Username',
            // 'Kelas', // DINONAKTIFKAN
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi',
            'Skor',
            'Benar',
            'Salah',
            'Kosong',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Detail Peserta';
    }
}

class QuestionAnalysisSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $package;

    public function __construct(TestPackage $package)
    {
        $this->package = $package;
    }

    public function collection()
    {
        $questions = $this->package->questions;
        $data = [];

        foreach ($questions as $index => $question) {
            $answers = \DB::table('test_answers')
                ->join('test_attempts', 'test_answers.attempt_id', '=', 'test_attempts.id')
                ->where('test_attempts.package_id', $this->package->id)
                ->where('test_answers.question_id', $question->id)
                ->where('test_attempts.status', 'completed')
                ->select('test_answers.is_correct')
                ->get();

            $totalAnswers = $answers->count();
            $correctCount = $answers->where('is_correct', true)->count();
            $successRate = $totalAnswers > 0 ? round(($correctCount / $totalAnswers) * 100, 1) : 0;

            $data[] = [
                'no' => $index + 1,
                'category' => $question->category->name,
                'question' => substr($question->question_text, 0, 100).'...',
                'difficulty' => ucfirst($question->difficulty),
                'total_answers' => $totalAnswers,
                'correct' => $correctCount,
                'wrong' => $totalAnswers - $correctCount,
                'success_rate' => $successRate.'%',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori',
            'Pertanyaan',
            'Tingkat',
            'Total Jawaban',
            'Benar',
            'Salah',
            'Success Rate',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Analisis Soal';
    }
}
