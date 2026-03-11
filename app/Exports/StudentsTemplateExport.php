<?php

// app/Exports/StudentsTemplateExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Budi Santoso', '1234567890', 'budi@example.com', 'XII IPA 1'],
            ['Ani Pratiwi', '0987654321', 'ani@example.com', 'XII IPA 1'],
            ['', '', '', ''], // Empty row for user to fill
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NISN',
            'Email',
            'Kelas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
