<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student')->except('logout');
    }

    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        $student = Student::where('username', $request->username)->first();

        if (!$student) {
            return back()->withErrors(['username' => 'Username tidak ditemukan'])->withInput();
        }

        if (!$student->is_active) {
            return back()->withErrors(['username' => 'Akun tidak aktif. Hubungi guru Anda.'])->withInput();
        }

        if (!Hash::check($request->password, $student->password)) {
            return back()->withErrors(['password' => 'Password salah'])->withInput();
        }

        Auth::guard('student')->login($student, $request->filled('remember'));

        $student->update(['last_login_at' => now()]);

        return redirect()->intended(route('student.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
