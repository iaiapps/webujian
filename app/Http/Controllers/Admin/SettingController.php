<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            
            'free_max_students' => 'required|integer|min:1',
            'free_max_packages' => 'required|integer|min:1',
            'free_max_questions' => 'required|integer|min:1',
            'free_max_classes' => 'required|integer|min:1',
            
            'pro_max_students' => 'required|integer|min:1',
            'pro_max_packages' => 'required|integer|min:1',
            'pro_max_questions' => 'required|integer|min:1',
            'pro_max_classes' => 'required|integer|min:1',
            
            'advanced_max_students' => 'required|integer|min:1',
            'advanced_max_packages' => 'required|integer|min:1',
            'advanced_max_questions' => 'required|integer|min:1',
            'advanced_max_classes' => 'required|integer|min:1',
            
            'pro_price_monthly' => 'required|integer|min:0',
            'pro_price_yearly' => 'required|integer|min:0',
            'advanced_price_monthly' => 'required|integer|min:0',
            'advanced_price_yearly' => 'required|integer|min:0',
            
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'payment_whatsapp' => 'nullable|string|max:20',
        ]);

        // General
        Setting::set('app_name', $request->app_name, 'string', 'general');
        Setting::set('app_timezone', $request->app_timezone, 'string', 'general');

        // Limits - Free
        Setting::set('free_max_students', $request->free_max_students, 'integer', 'limits');
        Setting::set('free_max_packages', $request->free_max_packages, 'integer', 'limits');
        Setting::set('free_max_questions', $request->free_max_questions, 'integer', 'limits');
        Setting::set('free_max_classes', $request->free_max_classes, 'integer', 'limits');

        // Limits - Pro
        Setting::set('pro_max_students', $request->pro_max_students, 'integer', 'limits');
        Setting::set('pro_max_packages', $request->pro_max_packages, 'integer', 'limits');
        Setting::set('pro_max_questions', $request->pro_max_questions, 'integer', 'limits');
        Setting::set('pro_max_classes', $request->pro_max_classes, 'integer', 'limits');

        // Limits - Advanced
        Setting::set('advanced_max_students', $request->advanced_max_students, 'integer', 'limits');
        Setting::set('advanced_max_packages', $request->advanced_max_packages, 'integer', 'limits');
        Setting::set('advanced_max_questions', $request->advanced_max_questions, 'integer', 'limits');
        Setting::set('advanced_max_classes', $request->advanced_max_classes, 'integer', 'limits');

        // Pricing
        Setting::set('pro_price_monthly', $request->pro_price_monthly, 'integer', 'pricing');
        Setting::set('pro_price_yearly', $request->pro_price_yearly, 'integer', 'pricing');
        Setting::set('advanced_price_monthly', $request->advanced_price_monthly, 'integer', 'pricing');
        Setting::set('advanced_price_yearly', $request->advanced_price_yearly, 'integer', 'pricing');

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
