<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'app_name', 'value' => 'TKA Web App', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app_logo', 'value' => null, 'type' => 'string', 'group' => 'general'],
            ['key' => 'app_timezone', 'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general'],

            // Global Limits
            ['key' => 'global_max_students', 'value' => '50', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'global_max_questions', 'value' => '100', 'type' => 'integer', 'group' => 'limits'],

            // Credit Settings (Default for new users)
            ['key' => 'credit_default', 'value' => '10', 'type' => 'integer', 'group' => 'credits'],

            // Email Settings
            ['key' => 'email_from_address', 'value' => 'lontarnesia@gmail.com', 'type' => 'string', 'group' => 'email'],
            ['key' => 'email_from_name', 'value' => 'EXAM WEB', 'type' => 'string', 'group' => 'email'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Settings created successfully!');
    }
}
