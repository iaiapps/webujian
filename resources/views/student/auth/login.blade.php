@extends('layouts.auth')

@section('title', 'Login Siswa')

@section('content')
    <div class="container">
        <div class="auth-card">
            <!-- Logo/Brand -->
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold mb-2">ExamWeb</h1>
                <p class="text-muted">Masuk sebagai siswa</p>
            </div>

            <!-- Card -->
            <div class="card">
                <div class="card-body p-4">
                    @if (session('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.login.post') }}">
                        @csrf

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                autofocus class="form-control @error('username') is-invalid @enderror">
                            @error('username')
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

                        <!-- Remember -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                            Masuk
                        </button>
                    </form>
                </div>

                <!-- Footer Links -->
                <div class="card-footer bg-light p-4 text-center">
                    <div class="border-top pt-3">
                        <p class="mb-2 text-muted small">Bukan siswa?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-person-badge me-1"></i>
                            Login Guru/Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
