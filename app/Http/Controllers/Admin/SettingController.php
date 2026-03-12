<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $settings = [
            'general' => Setting::getByGroup('general'),
            'limits' => Setting::getByGroup('limits'),
            'pricing' => Setting::getByGroup('pricing'),
            'payment' => Setting::getByGroup('payment'),
            'email' => Setting::getByGroup('email'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',

            // ============================================================
            // SISTEM KREDIT - Global limits, bukan per-plan
            // ============================================================
            'global_max_students' => 'required|integer|min:1',
            'global_max_questions' => 'required|integer|min:1',

            // Credit settings
            'credit_default' => 'required|integer|min:0',
            'credit_price' => 'required|integer|min:1000',
            'credit_bonus_threshold' => 'required|integer|min:1',
            'credit_bonus_amount' => 'required|integer|min:0',

            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'payment_whatsapp' => 'nullable|string|max:20',
        ]);

        // General
        Setting::set('app_name', $request->app_name, 'string', 'general');
        Setting::set('app_timezone', $request->app_timezone, 'string', 'general');

        // ============================================================
        // SISTEM KREDIT - Global limits
        // ============================================================
        Setting::set('global_max_students', $request->global_max_students, 'integer', 'limits');
        Setting::set('global_max_questions', $request->global_max_questions, 'integer', 'limits');

        // Credit settings
        Setting::set('credit_default', $request->credit_default, 'integer', 'credits');
        Setting::set('credit_price', $request->credit_price, 'integer', 'credits');
        Setting::set('credit_bonus_threshold', $request->credit_bonus_threshold, 'integer', 'credits');
        Setting::set('credit_bonus_amount', $request->credit_bonus_amount, 'integer', 'credits');

        // Payment
        Setting::set('bank_name', $request->bank_name, 'string', 'payment');
        Setting::set('bank_account_number', $request->bank_account_number, 'string', 'payment');
        Setting::set('bank_account_name', $request->bank_account_name, 'string', 'payment');
        Setting::set('bank_branch', $request->bank_branch ?? '', 'string', 'payment');
        Setting::set('payment_whatsapp', $request->payment_whatsapp ?? '', 'string', 'payment');
        Setting::set('payment_instructions', $request->payment_instructions ?? '', 'string', 'payment');
        Setting::set('qris_merchant_name', $request->qris_merchant_name ?? '', 'string', 'payment');

        // Handle QRIS image upload
        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('settings', 'public');
            Setting::set('qris_image', $path, 'string', 'payment');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
