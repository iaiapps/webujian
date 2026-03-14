@extends('layouts.dashboard')

@section('title', 'Pengaturan Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pengaturan Kredit</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Konfigurasi Sistem Kredit</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.credits.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kredit Default Registrasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="credit_default" class="form-control @error('credit_default') is-invalid @enderror" value="{{ old('credit_default', $settings['credit_default']) }}" required min="0">
                                <span class="input-group-text">kredit</span>
                            </div>
                            <small class="text-muted">Kredit gratis yang didapat user saat registrasi</small>
                            @error('credit_default')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Kredit Default:</strong> User baru otomatis mendapatkan kredit ini saat registrasi.</p>
                    <p class="mb-0"><strong>Catatan:</strong> Untuk mengatur paket kredit dan harga, gunakan menu <a href="{{ route('admin.credit-packages.index') }}">Paket Kredit</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
