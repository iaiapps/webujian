<?php

namespace Database\Seeders;

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
            // SISTEM KREDIT - Default credits
            'credits' => 10,
            'max_students' => 50,
            'max_questions' => 100,
            'email_verified_at' => now(),
            'is_active' => true,
            'approved_at' => now(),
        ]);
        $guru->assignRole('guru');

        $this->command->info('Roles created successfully!');
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Guru: guru@demo.com / password');
    }
}
