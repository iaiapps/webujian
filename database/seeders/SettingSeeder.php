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

            // ============================================================
            // SISTEM KREDIT - Global Limits (bukan per-plan)
            // ============================================================
            ['key' => 'global_max_students', 'value' => '50', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'global_max_questions', 'value' => '100', 'type' => 'integer', 'group' => 'limits'],

            // ============================================================
            // SISTEM KREDIT - Credit Settings
            // ============================================================
            ['key' => 'credit_default', 'value' => '10', 'type' => 'integer', 'group' => 'credits'],
            ['key' => 'credit_price', 'value' => '5000', 'type' => 'integer', 'group' => 'credits'],
            ['key' => 'credit_bonus_threshold', 'value' => '5', 'type' => 'integer', 'group' => 'credits'],
            ['key' => 'credit_bonus_amount', 'value' => '1', 'type' => 'integer', 'group' => 'credits'],

            // Email Settings
            ['key' => 'email_from_address', 'value' => 'lontarnesia@gmail.com', 'type' => 'string', 'group' => 'email'],
            ['key' => 'email_from_name', 'value' => 'TKA Web App', 'type' => 'string', 'group' => 'email'],

            // Payment Settings - Bank Transfer
            ['key' => 'bank_name', 'value' => 'Bank BCA', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'bank_account_number', 'value' => '1234567890', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'bank_account_name', 'value' => 'PT TKA Web App INDONESIA', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'bank_branch', 'value' => 'KCP Jakarta Pusat', 'type' => 'string', 'group' => 'payment'],

            // Payment Settings - QRIS
            ['key' => 'qris_image', 'value' => null, 'type' => 'string', 'group' => 'payment'],
            ['key' => 'qris_merchant_name', 'value' => 'TKA Web App', 'type' => 'string', 'group' => 'payment'],

            // Payment Settings - General
            ['key' => 'payment_confirmation_email', 'value' => 'lontarnesia@gmail.com', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_whatsapp', 'value' => '6285232213939', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_instructions', 'value' => 'Setelah melakukan pembayaran, silakan upload bukti transfer melalui halaman kredit.', 'type' => 'string', 'group' => 'payment'],
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
