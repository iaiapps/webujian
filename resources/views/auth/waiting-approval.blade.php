@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-check-circle"></i> Registrasi Berhasil
                    </div>

                    <div class="card-body text-center py-5">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>

                        <h4>Selamat! Registrasi Anda Berhasil!</h4>
                        <p class="text-muted mb-4">
                            Anda sekarang dapat mengakses semua fitur ExamWeb.<br>
                            Silakan lengkapi profil dan mulai membuat tes untuk siswa Anda.
                        </p>

                        <div class="mt-4">
                            <a href="{{ route('guru.dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-speedometer2"></i> Ke Dashboard
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
