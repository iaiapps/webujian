@extends('layouts.dashboard')

@section('title', 'Upgrade Berhasil')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h2 class="mb-3">Permintaan Upgrade Terkirim!</h2>
                    
                    <p class="text-muted mb-4">
                        Terima kasih! Permintaan upgrade Anda telah kami terima. 
                        Silakan transfer sesuai dengan informasi yang tertera, 
                        kemudian tunggu konfirmasi dari admin.
                    </p>

                    @if(session('subscription'))
                        <div class="alert alert-info text-start">
                            <h6><i class="bi bi-info-circle"></i> Detail Pembayaran:</h6>
                            <ul class="mb-0">
                                <li>Plan: <strong>{{ session('subscription.plan') }}</strong></li>
                                <li>Periode: <strong>{{ session('subscription.billing_cycle') }}</strong></li>
                                <li>Total: <strong>Rp {{ number_format(session('subscription.amount'), 0, ',', '.') }}</strong></li>
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-warning text-start">
                        <h6><i class="bi bi-exclamation-triangle"></i> Langkah Selanjutnya:</h6>
                        <ol class="mb-0">
                            <li>Transfer sesuai nominal ke rekening yang tertera</li>
                            <li>Upload bukti transfer di halaman subscription</li>
                            <li>Tunggu konfirmasi dari admin (1x24 jam)</li>
                            <li>Akun Anda akan otomatis di-upgrade setelah dikonfirmasi</li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('guru.subscription.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Subscription
                        </a>
                        <a href="{{ route('guru.dashboard') }}" class="btn btn-outline-secondary">
                            Ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
