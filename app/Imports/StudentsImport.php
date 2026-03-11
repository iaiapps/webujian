<?php

// app/Imports/StudentsImport.php
namespace App\Imports;

use App\Models\Student;
use App\Models\ClassRoom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    protected $user;
    protected $defaultPassword;
    protected $importedCount = 0;
    protected $credentials = [];

    public function __construct($user, $defaultPassword)
    {
        $this->user = $user;
        $this->defaultPassword = $defaultPassword;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['nama']) || empty($row['kelas'])) {
                continue;
            }

            // Check limit
            if (!$this->user->canAddStudent()) {
                break;
            }

            // Find or create class
            $class = ClassRoom::firstOrCreate(
                [
                    'user_id' => $this->user->id,
                    'name' => $row['kelas'],
                ],
                [
                    'academic_year' => date('Y') . '/' . (date('Y') + 1),
                ]
            );

            // Generate username
            $username = !empty($row['nisn']) ? $row['nisn'] : $this->generateUsername($row['nama']);

            // Check if username already exists
            if (Student::where('username', $username)->exists()) {
                $username = $this->generateUsername($row['nama']);
            }

            // Create student
            $student = $this->user->students()->create([
                'class_id' => $class->id,
                'name' => $row['nama'],
                'nisn' => $row['nisn'] ?? null,
                'email' => $row['email'] ?? null,
                'username' => $username,
                'password' => Hash::make($this->defaultPassword),
                'is_active' => true,
            ]);

            // Update class count
            $class->updateStudentCount();

            $this->importedCount++;
            $this->credentials[] = [
                'name' => $student->name,
                'username' => $student->username,
                'password' => $this->defaultPassword,
                'class' => $class->name,
            ];
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    private function generateUsername($name)
    {
        $base = Str::slug(Str::lower($name));
        $username = $base;
        $counter = 1;

        while (Student::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
