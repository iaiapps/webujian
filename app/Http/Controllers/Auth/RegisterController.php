<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    // ============================================================
    // APPROVAL MANUAL DINONAKTIFKAN
    // User langsung ke dashboard setelah register
    // ============================================================
    protected $redirectTo = '/guru/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'institution_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'institution_name.required' => 'Nama institusi/bimbel wajib diisi',
            'phone.required' => 'Nomor telepon wajib diisi',
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'institution_name' => $data['institution_name'],
            'phone' => $data['phone'],
            'plan' => 'free',
            'max_students' => Setting::get('global_max_students', 50),
            'max_questions' => Setting::get('global_max_questions', 100),
            // ============================================================
            // SISTEM KREDIT - Default 10 kredit untuk guru baru
            // ============================================================
            'credits' => Setting::get('credit_default', 10),
            'is_active' => true,
            // ============================================================
            // APPROVAL MANUAL DINONAKTIFKAN
            // User langsung di-approve saat registrasi
            // ============================================================
            'approved_at' => now(),
        ]);

        $user->assignRole('guru');

        return $user;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // ============================================================
        // APPROVAL MANUAL DINONAKTIFKAN
        // Langsung login dan redirect ke dashboard
        // ============================================================
        $this->guard()->login($user);

        return redirect($this->redirectTo)
            ->with('success', 'Registrasi berhasil! Selamat datang di TKA.');
    }
}
