<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'academic_year',
        'student_count',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'student_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function activeStudents()
    {
        return $this->students()->where('is_active', true);
    }

    public function testPackages()
    {
        return $this->belongsToMany(TestPackage::class, 'test_package_classes', 'class_id', 'package_id');
    }

    // Update student count
    public function updateStudentCount(): void
    {
        $this->update([
            'student_count' => $this->activeStudents()->count(),
        ]);
    }
}
