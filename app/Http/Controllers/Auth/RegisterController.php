<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/auth/waiting-approval';

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
            'max_students' => Setting::get('free_max_students', 30),
            'max_packages' => Setting::get('free_max_packages', 3),
            'max_questions' => Setting::get('free_max_questions', 100),
            'max_classes' => Setting::get('free_max_classes', 1),
            'is_active' => true,
        ]);

        $user->assignRole('guru');

        return $user;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return redirect()->route('auth.waiting-approval')
            ->with('success', 'Registrasi berhasil! Silakan tunggu persetujuan admin.');
    }
}
