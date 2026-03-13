<?php

namespace Database\Seeders;

use App\Models\CreditTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles only (role-only approach - no permissions)
        $adminRole = Role::create(['name' => 'admin']);
        $guruRole = Role::create(['name' => 'guru']);
        $siswaRole = Role::create(['name' => 'siswa']);

        // Create Super Admin User
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create Demo Guru User
        $guru = User::create([
            'name' => 'Guru Demo',
            'email' => 'guru@demo.com',
            'password' => Hash::make('password'),
            'institution_name' => 'Bimbel Demo',
            'phone' => '081234567890',
            // SISTEM KREDIT - Mulai dari 0, nanti ditambah via transaction
            'credits' => 0,
            'max_students' => 50,
            'max_questions' => 100,
            'email_verified_at' => now(),
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $guru->assignRole('guru');

        // SISTEM KREDIT - Buat transaksi initial kredit untuk guru demo
        CreditTransaction::create([
            'user_id' => $guru->id,
            'type' => 'bonus',
            'amount' => 10,
            'balance_before' => 0,
            'balance_after' => 10,
            'description' => 'Kredit awal registrasi - Guru Demo',
            'reference_id' => null,
            'reference_type' => 'registration',
            'performed_by' => null,
            'notes' => 'Selamat datang! Anda mendapatkan 10 kredit gratis.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update credits guru
        $guru->update(['credits' => 10]);

        // Create Demo Student
        $studentUser = User::create([
            'name' => 'Siswa Demo',
            'email' => 'siswa@demo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $studentUser->assignRole('siswa');

        // Create Student record untuk siswa demo
        Student::create([
            'user_id' => $guru->id, // Siswa ini milik guru demo
            'username' => 'siswa_demo',
            'password' => Hash::make('password'),
            'name' => 'Siswa Demo',
            'email' => 'siswa@demo.com',
            'nisn' => '1234567890',
            'is_active' => true,
        ]);

        $this->command->info('Roles created successfully!');
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Guru: guru@demo.com / password');
        $this->command->info('Siswa: siswa@demo.com / password');
    }
}
