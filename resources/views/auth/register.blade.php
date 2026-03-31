@extends('layouts.auth')

@section('title', 'Registrasi')

@section('content')
    <div class="container">
        <div class="auth-card-wide bg-white rounded shadow rounded-4">
            <!-- Logo/Brand -->
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold mb-2 pt-4">Buat Akun Baru</h1>
                <p class="text-muted">Daftar sebagai guru/lembaga bimbel</p>
            </div>

            <!-- Card -->
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    autofocus class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Institution -->
                            <div class="col-md-6">
                                <label for="institution_name" class="form-label fw-semibold">Nama Institusi/Bimbel</label>
                                <input type="text" name="institution_name" id="institution_name"
                                    value="{{ old('institution_name') }}" required
                                    class="form-control @error('institution_name') is-invalid @enderror">
                                @error('institution_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                    class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Nomor WhatsApp</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                    placeholder="08xxxxxxxxxx" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" id="password" required
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi
                                    Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="form-control">
                            </div>
                        </div>

                        <!-- Info Box -->
                        {{-- <div class="alert alert-info d-flex align-items-start mt-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                            <div class="small">
                                <strong>Pendaftaran memerlukan approval admin</strong><br>
                                Setelah registrasi, akun Anda akan direview dalam 1x24 jam. Notifikasi akan dikirim via
                                email.
                            </div>
                        </div> --}}

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold mt-3">
                            Daftar Sekarang
                        </button>
                    </form>
                </div>

                <!-- Footer Links -->
                <div class="card-footer bg-light p-4 text-center">
                    <p class="mb-0 text-muted small">Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
