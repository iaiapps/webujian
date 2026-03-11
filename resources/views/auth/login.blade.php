@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="container">
        <div class="auth-card">
            <!-- Logo/Brand -->
            <div class="text-center mb-4">
                <div class="brand-icon mb-3 mx-auto">
                    <i class="bi bi-book text-white" style="font-size: 32px;"></i>
                </div>
                <h1 class="h2 fw-bold mb-2">TKA Web App</h1>
                <p class="text-muted">Masuk ke akun Anda</p>
            </div>

            <!-- Card -->
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" id="password" required
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                            Masuk
                        </button>
                    </form>
                </div>

                <!-- Footer Links -->
                <div class="card-footer bg-light p-4 text-center">
                    <p class="mb-3 text-muted small">Belum punya akun?
                        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">
                            Daftar sekarang
                        </a>
                    </p>
                    <div class="border-top pt-3">
                        <p class="mb-2 text-muted small">Masuk sebagai siswa?</p>
                        <a href="{{ route('student.login') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-mortarboard me-1"></i>
                            Login Siswa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
