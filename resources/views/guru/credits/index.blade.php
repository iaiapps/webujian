@extends('layouts.dashboard')

@section('title', 'Kredit Saya')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Kredit Saya</h2>
        </div>
    </div>

    {{-- Credit Balance Card --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Kredit Tersedia</h6>
                            <h1 class="mb-0 fw-bold">{{ $creditInfo['current_credits'] }}</h1>
                            <small>Kredit untuk membuat paket tes</small>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Cara Menggunakan Kredit</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> 1 Kredit = 1 Paket Tes</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Tidak ada batasan paket tes</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Setiap pembuatan paket mengurangi 1 kredit</li>
                        <li class="mb-0"><i class="bi bi-info-circle text-info me-2"></i> Kredit tidak hangus jika paket dihapus</li>
                    </ul>
                    <a href="{{ route('guru.credits.topup') }}" class="btn btn-accent mt-3">
                        <i class="bi bi-plus-circle"></i> Beli Kredit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Info --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    @if(!empty($payment['bank_name']))
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Bank Transfer</h6>
                            <p class="mb-1"><strong>Bank:</strong> {{ $payment['bank_name'] }}</p>
                            <p class="mb-1"><strong>Nomor Rekening:</strong> {{ $payment['bank_account_number'] }}</p>
                            <p class="mb-0"><strong>Atas Nama:</strong> {{ $payment['bank_account_name'] }}</p>
                        </div>
                        @if(!empty($payment['qris_merchant_name']))
                        <div class="col-md-6">
                            <h6>QRIS</h6>
                            <p class="mb-1"><strong>Merchant:</strong> {{ $payment['qris_merchant_name'] }}</p>
                            @if(!empty($payment['qris_image']))
                            <p class="mb-0"><small class="text-muted">Scan QRIS untuk pembayaran</small></p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @else
                    <p class="text-muted">Belum ada informasi pembayaran. Silakan hubungi admin.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection