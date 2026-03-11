@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-hourglass-split"></i> Menunggu Persetujuan
                </div>

                <div class="card-body text-center py-5">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <i class="bi bi-clock-history text-warning" style="font-size: 5rem;"></i>
                    </div>

                    <h4>Registrasi Berhasil!</h4>
                    <p class="text-muted mb-4">
                        Akun Anda sedang menunggu persetujuan dari administrator.<br>
                        Kami akan mengirimkan notifikasi melalui email setelah akun Anda disetujui.
                    </p>

                    <div class="alert alert-info">
                        <strong>Informasi:</strong><br>
                        Proses persetujuan biasanya membutuhkan waktu 1x24 jam pada hari kerja.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Login
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
