<?php

// app/Exports/StudentsCredentialsExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsCredentialsExport implements FromArray, WithHeadings, WithStyles
{
    protected $credentials;

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    public function array(): array
    {
        return collect($this->credentials)->map(function ($cred) {
            return [
                $cred['name'],
                $cred['username'],
                $cred['password'],
                $cred['class'],
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Username',
            'Password',
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
