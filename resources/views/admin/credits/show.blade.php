@extends('layouts.dashboard')

@section('title', 'Detail Kredit - ' . $user->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Detail Kredit</h2>
            <p class="text-muted">{{ $user->name }} ({{ $user->email }})</p>
        </div>
        <div>
            <a href="{{ route('admin.credits.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- Credit Info & Management --}}
        <div class="col-lg-4">
            {{-- Current Balance --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Kredit Saat Ini</h6>
                    <h1 class="display-4 fw-bold text-primary">{{ $user->credits }}</h1>
                    <span class="badge bg-success fs-6">Kredit Tersedia</span>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Ringkasan</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Masuk:</span>
                        <strong class="text-success">+{{ $stats['total_in'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Keluar:</span>
                        <strong class="text-danger">-{{ $stats['total_out'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Paket Tes Dibuat:</span>
                        <strong>{{ $stats['total_packages'] }}</strong>
                    </div>
                </div>
            </div>

            {{-- Add Credits Form --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Kredit</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.credits.add', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jumlah Kredit</label>
                            <input type="number" name="amount" class="form-control" min="1" required placeholder="Masukkan jumlah">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea name="reason" class="form-control" rows="2" required placeholder="Contoh: Kompensasi bug, Bonus promo, dll"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> Tambah Kredit
                        </button>
                    </form>
                </div>
            </div>

            {{-- Deduct Credits Form --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-dash-circle"></i> Kurangi Kredit</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.credits.deduct', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jumlah Kredit</label>
                            <input type="number" name="amount" class="form-control" min="1" max="{{ $user->credits }}" required placeholder="Masukkan jumlah">
                            <small class="text-muted">Maksimal: {{ $user->credits }} kredit</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea name="reason" class="form-control" rows="2" required placeholder="Contoh: Salah input, Refund, dll"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Yakin ingin mengurangi kredit?')">
                            <i class="bi bi-dash-circle"></i> Kurangi Kredit
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Transaction History --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Balance</th>
                                    <th>Deskripsi</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->getTypeBadgeClass() }}">
                                            {{ $transaction->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($transaction->amount > 0)
                                            <span class="text-success">+{{ $transaction->amount }}</span>
                                        @else
                                            <span class="text-danger">{{ $transaction->amount }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $transaction->balance_before }} → {{ $transaction->balance_after }}</small>
                                    </td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>
                                        @if($transaction->performed_by)
                                            <small class="text-muted">Admin: {{ $transaction->performedBy?->name ?? 'Unknown' }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
