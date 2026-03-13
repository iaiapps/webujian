<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class CreditSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $settings = [
            'credit_default' => Setting::get('credit_default', 10),
            'credit_price' => Setting::get('credit_price', 5000),
            'credit_bonus_threshold' => Setting::get('credit_bonus_threshold', 5),
            'credit_bonus_amount' => Setting::get('credit_bonus_amount', 1),
            'credit_min_purchase' => Setting::get('credit_min_purchase', 5),
        ];

        return view('admin.settings.credits', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'credit_default' => ['required', 'integer', 'min:0'],
            'credit_price' => ['required', 'integer', 'min:1000'],
            'credit_bonus_threshold' => ['required', 'integer', 'min:1'],
            'credit_bonus_amount' => ['required', 'integer', 'min:0'],
            'credit_min_purchase' => ['required', 'integer', 'min:1'],
        ], [
            'credit_default.required' => 'Kredit default registrasi wajib diisi',
            'credit_default.min' => 'Kredit default minimal 0',
            'credit_price.required' => 'Harga per kredit wajib diisi',
            'credit_price.min' => 'Harga per kredit minimal Rp 1.000',
            'credit_bonus_threshold.required' => 'Threshold bonus wajib diisi',
            'credit_bonus_threshold.min' => 'Threshold bonus minimal 1',
            'credit_bonus_amount.required' => 'Jumlah bonus wajib diisi',
            'credit_min_purchase.required' => 'Minimum pembelian wajib diisi',
            'credit_min_purchase.min' => 'Minimum pembelian minimal 1 kredit',
        ]);

        // Update settings
        Setting::set('credit_default', $request->credit_default, 'integer', 'credits');
        Setting::set('credit_price', $request->credit_price, 'integer', 'credits');
        Setting::set('credit_bonus_threshold', $request->credit_bonus_threshold, 'integer', 'credits');
        Setting::set('credit_bonus_amount', $request->credit_bonus_amount, 'integer', 'credits');
        Setting::set('credit_min_purchase', $request->credit_min_purchase, 'integer', 'credits');

        return redirect()->route('admin.settings.credits')
            ->with('success', 'Pengaturan kredit berhasil diupdate');
    }
}
