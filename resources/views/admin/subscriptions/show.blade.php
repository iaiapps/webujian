{{-- resources/views/admin/subscriptions/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Detail Subscription #' . $subscription->id)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Subscription #{{ $subscription->id }}</h2>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            {{-- Subscription Info --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Informasi Subscription</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Guru</h6>
                                <p class="mb-0">
                                    <strong>{{ $subscription->user->name }}</strong><br>
                                    {{ $subscription->user->email }}<br>
                                    {{ $subscription->user->phone }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Lembaga</h6>
                                <p class="mb-0">{{ $subscription->user->institution_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Plan</h6>
                                <span class="badge bg-primary fs-6">{{ strtoupper($subscription->plan) }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Siklus Pembayaran</h6>
                                <p class="mb-0">
                                    {{ $subscription->billing_cycle === 'monthly' ? 'Bulanan (1 bulan)' : 'Tahunan (12 bulan)' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Jumlah</h6>
                                <h4 class="mb-0 text-primary">Rp {{ number_format($subscription->amount) }}</h4>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Status</h6>
                                @if ($subscription->status === 'pending')
                                    <span class="badge bg-warning fs-6">Pending</span>
                                @elseif($subscription->status === 'active')
                                    <span class="badge bg-success fs-6">Active</span>
                                @elseif($subscription->status === 'expired')
                                    <span class="badge bg-secondary fs-6">Expired</span>
                                @elseif($subscription->status === 'failed')
                                    <span class="badge bg-danger fs-6">Ditolak</span>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Tanggal Submit</h6>
                                <p class="mb-0">{{ $subscription->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Expired At</h6>
                                <p class="mb-0">
                                    {{ $subscription->expired_at ? $subscription->expired_at->format('d M Y') : '-' }}</p>
                            </div>
                        </div>

                        @if ($subscription->notes)
                            <hr>
                            <h6 class="text-muted">Catatan</h6>
                            <p class="mb-0">{{ $subscription->notes }}</p>
                        @endif
                    </div>
                </div>

                {{-- History --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">History</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($subscription->histories as $history)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span
                                            class="badge bg-{{ $history->action === 'upgraded' ? 'success' : ($history->action === 'created' ? 'primary' : 'secondary') }}">
                                            {{ ucfirst($history->action) }}
                                        </span>
                                        @if ($history->old_plan)
                                            <span class="small">{{ strtoupper($history->old_plan) }} →
                                                {{ strtoupper($history->new_plan) }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                                </div>
                                @if ($history->notes)
                                    <small class="text-muted">{{ $history->notes }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions & Payment Proof --}}
            <div class="col-lg-4">
                {{-- Payment Proof --}}
                @if ($subscription->proof_of_payment)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Bukti Transfer</h6>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($subscription->proof_of_payment) }}" alt="Payment Proof"
                                class="img-fluid rounded" style="cursor: pointer;"
                                onclick="window.open(this.src, '_blank')">
                            <p class="small text-muted mt-2 mb-0">Klik untuk memperbesar</p>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                @if ($subscription->status === 'pending')
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Aksi</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.subscriptions.approve', $subscription) }}" method="POST"
                                class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Approve subscription ini?\n\nUser akan langsung mendapat akses {{ strtoupper($subscription->plan) }} plan.')">
                                    <i class="bi bi-check-circle"></i> Approve & Aktifkan
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Contact Info --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Kontak Guru</h6>
                        <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $subscription->user->phone) }}"
                            target="_blank" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        <a href="mailto:{{ $subscription->user->email }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-envelope"></i> Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.subscriptions.reject', $subscription) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Tolak Pembayaran</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="reject_reason" name="reject_reason" rows="3" required
                                placeholder="Contoh: Bukti transfer tidak jelas, nominal tidak sesuai, dll"></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> User akan menerima email notifikasi dengan alasan
                            penolakan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Tolak Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
