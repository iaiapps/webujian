<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi admin.');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('guru')) {
            if (!$user->isApproved()) {
                auth()->logout();
                return redirect()->route('auth.waiting-approval');
            }
            return redirect()->route('guru.dashboard');
        }

        return redirect('/');
    }

    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }
}
