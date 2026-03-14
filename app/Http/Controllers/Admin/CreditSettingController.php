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
        ];

        return view('admin.settings.credits', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'credit_default' => ['required', 'integer', 'min:0'],
        ], [
            'credit_default.required' => 'Kredit default registrasi wajib diisi',
            'credit_default.min' => 'Kredit default minimal 0',
        ]);

        // Update settings
        Setting::set('credit_default', $request->credit_default, 'integer', 'credits');

        return redirect()->route('admin.settings.credits')
            ->with('success', 'Pengaturan kredit berhasil diupdate');
    }
}
