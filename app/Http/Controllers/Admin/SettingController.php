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
            'email' => Setting::getByGroup('email'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',

            // Global limits
            'global_max_students' => 'required|integer|min:1',
            'global_max_questions' => 'required|integer|min:1',

            // Credit settings
            'credit_default' => 'required|integer|min:0',
        ]);

        // General
        Setting::set('app_name', $request->app_name, 'string', 'general');
        Setting::set('app_timezone', $request->app_timezone, 'string', 'general');

        // Global limits
        Setting::set('global_max_students', $request->global_max_students, 'integer', 'limits');
        Setting::set('global_max_questions', $request->global_max_questions, 'integer', 'limits');

        // Credit settings
        Setting::set('credit_default', $request->credit_default, 'integer', 'credits');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
