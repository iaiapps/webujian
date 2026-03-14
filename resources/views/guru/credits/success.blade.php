@extends('layouts.dashboard')

@section('title', 'Pembayaran Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5" id="status-container">
                    {{-- Loading State --}}
                    <div id="loading-state">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h3 class="mb-3">Menunggu Pembayaran</h3>
                        <p class="text-muted mb-4">
                            Silakan selesaikan pembayaran Anda di halaman Mayar.<br>
                            Halaman ini akan otomatis update setelah pembayaran berhasil.
                        </p>
                    </div>

                    {{-- Pending State (if still pending after initial load) --}}
                    <div id="pending-state" class="d-none">
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="mb-3">Menunggu Pembayaran</h3>
                        <p class="text-muted mb-4">
                            Invoice Anda masih menunggu pembayaran.<br>
                            <a href="{{ $purchase->payment_link }}" target="_blank" class="btn btn-warning btn-sm mt-2">
                                <i class="bi bi-box-arrow-up-right"></i> Kembali ke Halaman Pembayaran
                            </a>
                        </p>
                    </div>

                    {{-- Success State --}}
                    <div id="success-state" class="d-none">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="mb-3">Pembelian Kredit Berhasil!</h3>
                        
                        <div class="bg-light rounded p-4 mb-4">
                            <div class="row">
                                <div class="col-6 text-start">
                                    <p class="mb-1">Paket</p>
                                    <p class="mb-1">Kredit Dasar</p>
                                    <p class="mb-1">Bonus Kredit</p>
                                    <p class="mb-0 fw-bold">Total Kredit Diterima</p>
                                </div>
                                <div class="col-6 text-end">
                                    <p class="mb-1"><strong>{{ $purchase->creditPackage->name }}</strong></p>
                                    <p class="mb-1">{{ $purchase->credits_amount }} Kredit</p>
                                    <p class="mb-1 text-success">+{{ $purchase->bonus_credits }} Kredit</p>
                                    <p class="mb-0 fw-bold text-primary" id="success-total-credits">{{ $purchase->total_credits }} Kredit</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6 text-start">
                                    <p class="mb-0">Total Pembayaran</p>
                                </div>
                                <div class="col-6 text-end">
                                    <p class="mb-0 fw-bold">{{ $purchase->getFormattedAmount() }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="success-message">
                                Kredit telah ditambahkan ke akun Anda. Total kredit sekarang: <strong id="current-credits">{{ auth()->user()->credits }}</strong>
                            </span>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('guru.credits.index') }}" class="btn btn-primary">
                                <i class="bi bi-wallet2"></i> Lihat Kredit Saya
                            </a>
                            <a href="{{ route('guru.packages.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-box-seam"></i> Buat Paket Tes
                            </a>
                        </div>
                    </div>

                    {{-- Expired State --}}
                    <div id="expired-state" class="d-none">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="mb-3">Invoice Kadaluarsa</h3>
                        <p class="text-muted mb-4">
                            Invoice pembayaran Anda telah kadaluarsa.<br>
                            Silakan buat pembelian baru.
                        </p>
                        <a href="{{ route('guru.credits.topup') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat"></i> Coba Lagi
                        </a>
                    </div>

                    {{-- Check Status Button (Manual Fallback) --}}
                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="check-status-btn" onclick="checkStatusManual()">
                            <i class="bi bi-arrow-clockwise"></i> Cek Status Manual
                        </button>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            ID Invoice: {{ $purchase->mayar_invoice_id }}<br>
                            Status: <span id="status-badge" class="badge bg-warning">{{ $purchase->getStatusLabel() }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let purchaseId = {{ $purchase->id }};
    let isPaid = {{ $purchase->isPaid() ? 'true' : 'false' }};
    let pollingInterval;
    let pollCount = 0;
    const maxPolls = 60; // 5 minutes (60 * 5 seconds)

    function showState(state) {
        document.getElementById('loading-state').classList.add('d-none');
        document.getElementById('pending-state').classList.add('d-none');
        document.getElementById('success-state').classList.add('d-none');
        document.getElementById('expired-state').classList.add('d-none');
        
        document.getElementById(state + '-state').classList.remove('d-none');
    }

    function updateStatusBadge(status, label) {
        const badge = document.getElementById('status-badge');
        badge.textContent = label;
        badge.className = 'badge ' + (status === 'paid' ? 'bg-success' : status === 'expired' ? 'bg-danger' : 'bg-warning');
    }

    function checkStatus() {
        if (isPaid || pollCount >= maxPolls) {
            clearInterval(pollingInterval);
            return;
        }

        pollCount++;

        fetch('{{ route("guru.credits.check-status") }}?purchase_id=' + purchaseId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateStatusBadge(data.status, data.status_label);

            if (data.is_paid) {
                isPaid = true;
                clearInterval(pollingInterval);
                showState('success');
                document.getElementById('current-credits').textContent = data.current_credits;
                
                // Redirect setelah 3 detik (opsional)
                // setTimeout(() => {
                //     window.location.href = '{{ route("guru.credits.index") }}';
                // }, 3000);
            } else if (data.is_expired) {
                clearInterval(pollingInterval);
                showState('expired');
            } else {
                showState('pending');
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
        });
    }

    function checkStatusManual() {
        document.getElementById('check-status-btn').disabled = true;
        document.getElementById('check-status-btn').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memeriksa...';
        
        checkStatus();
        
        setTimeout(() => {
            document.getElementById('check-status-btn').disabled = false;
            document.getElementById('check-status-btn').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Cek Status Manual';
        }, 2000);
    }

    // Start polling
    if (!isPaid) {
        // Check immediately
        checkStatus();
        // Then every 5 seconds
        pollingInterval = setInterval(checkStatus, 5000);
    } else {
        showState('success');
    }
</script>
@endpush
@endsection
