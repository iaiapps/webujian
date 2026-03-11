<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'Berapakah hasil dari 25 x 4?',
                'single',
                '90',
                '100',
                '110',
                '120',
                '',
                'B',
                'numerasi-matematika',
                'Perkalian 25 x 4 = 100',
            ],
            [
                'Manakah bilangan prima dari pilihan berikut?',
                'complex',
                '2',
                '4',
                '7',
                '9',
                '11',
                'A,C,E',
                'numerasi-matematika',
                'Bilangan prima adalah 2, 7, dan 11',
            ],
            [
                'Sinonim dari kata "elok" adalah...',
                'single',
                'Jelek',
                'Indah',
                'Buruk',
                'Kotor',
                '',
                'B',
                'literasi-bahasa-indonesia',
                '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'question',
            'type',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'correct_answer',
            'category',
            'explanation',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 12,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 15,
            'I' => 12,
            'J' => 40,
        ];
    }
}
