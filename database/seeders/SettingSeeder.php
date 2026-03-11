<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

            // Plan Limits - FREE
            ['key' => 'free_max_students', 'value' => '30', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'free_max_packages', 'value' => '2', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'free_max_questions', 'value' => '50', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'free_max_classes', 'value' => '1', 'type' => 'integer', 'group' => 'limits'],

            // Plan Limits - PRO
            ['key' => 'pro_max_students', 'value' => '60', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'pro_max_packages', 'value' => '4', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'pro_max_questions', 'value' => '100', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'pro_max_classes', 'value' => '3', 'type' => 'integer', 'group' => 'limits'],

            // Plan Limits - ADVANCED
            ['key' => 'advanced_max_students', 'value' => '120', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'advanced_max_packages', 'value' => '8', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'advanced_max_questions', 'value' => '200', 'type' => 'integer', 'group' => 'limits'],
            ['key' => 'advanced_max_classes', 'value' => '6', 'type' => 'integer', 'group' => 'limits'],

            // Pricing
            ['key' => 'pro_price_monthly', 'value' => '49000', 'type' => 'integer', 'group' => 'pricing'],
            ['key' => 'pro_price_yearly', 'value' => '490000', 'type' => 'integer', 'group' => 'pricing'],
            ['key' => 'advanced_price_monthly', 'value' => '99000', 'type' => 'integer', 'group' => 'pricing'],
            ['key' => 'advanced_price_yearly', 'value' => '990000', 'type' => 'integer', 'group' => 'pricing'],

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
            ['key' => 'payment_instructions', 'value' => 'Setelah melakukan pembayaran, silakan upload bukti transfer melalui halaman subscription.', 'type' => 'string', 'group' => 'payment'],
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
