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

                        <div class="row">
                            <div class="col-md-6 mb-3">
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimum Pembelian <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="credit_min_purchase" class="form-control @error('credit_min_purchase') is-invalid @enderror" value="{{ old('credit_min_purchase', $settings['credit_min_purchase']) }}" required min="1">
                                    <span class="input-group-text">kredit</span>
                                </div>
                                <small class="text-muted">Minimum kredit yang bisa dibeli sekaligus</small>
                                @error('credit_min_purchase')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga per Kredit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="credit_price" class="form-control @error('credit_price') is-invalid @enderror" value="{{ old('credit_price', $settings['credit_price']) }}" required min="1000" step="100">
                            </div>
                            <small class="text-muted">Harga dasar per 1 kredit (sebelum diskon paket)</small>
                            @error('credit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Threshold Bonus <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="credit_bonus_threshold" class="form-control @error('credit_bonus_threshold') is-invalid @enderror" value="{{ old('credit_bonus_threshold', $settings['credit_bonus_threshold']) }}" required min="1">
                                    <span class="input-group-text">kredit</span>
                                </div>
                                <small class="text-muted">Beli berapa kredit untuk dapat bonus</small>
                                @error('credit_bonus_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Bonus <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="credit_bonus_amount" class="form-control @error('credit_bonus_amount') is-invalid @enderror" value="{{ old('credit_bonus_amount', $settings['credit_bonus_amount']) }}" required min="0">
                                    <span class="input-group-text">kredit</span>
                                </div>
                                <small class="text-muted">Bonus kredit yang didapat per threshold</small>
                                @error('credit_bonus_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                    <p class="mb-2"><strong>Harga per Kredit:</strong> Digunakan sebagai patokan untuk paket kredit.</p>
                    <p class="mb-2"><strong>Threshold Bonus:</strong> Contoh: beli 5 kredit dapat 1 bonus (5:1).</p>
                    <p class="mb-0"><strong>Catatan:</strong> Perubahan hanya berlaku untuk transaksi baru.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Kalkulasi Bonus</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">Dengan setting saat ini:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1">• Beli <strong>{{ $settings['credit_bonus_threshold'] }}</strong> kredit</li>
                        <li class="mb-1">• Bonus <strong>{{ $settings['credit_bonus_amount'] }}</strong> kredit</li>
                        <li>• Total: <strong>{{ $settings['credit_bonus_threshold'] + $settings['credit_bonus_amount'] }}</strong> kredit</li>
                    </ul>
                    <hr>
                    <p class="mb-0 text-muted">Harga: Rp {{ number_format($settings['credit_price'], 0, ',', '.') }}/kredit</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
