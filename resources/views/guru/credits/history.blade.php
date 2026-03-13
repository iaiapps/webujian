@extends('layouts.dashboard')

@section('title', 'Riwayat Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Riwayat Kredit</h2>
        <div>
            <a href="{{ route('guru.credits.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('guru.credits.topup') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Beli Kredit
            </a>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-0">{{ auth()->user()->credits }}</h4>
                    <small class="text-muted">Kredit Saat Ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-success mb-0">+{{ $stats['total_in'] }}</h4>
                    <small class="text-muted">Total Kredit Masuk</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-danger mb-0">-{{ $stats['total_out'] }}</h4>
                    <small class="text-muted">Total Kredit Keluar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Semua Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Balance</th>
                            <th>Deskripsi</th>
                            <th>Reference</th>
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
                                    <span class="text-success fw-bold">+{{ $transaction->amount }}</span>
                                @else
                                    <span class="text-danger fw-bold">{{ $transaction->amount }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $transaction->balance_before }} → <strong>{{ $transaction->balance_after }}</strong></small>
                            </td>
                            <td>{{ $transaction->description }}</td>
                            <td>
                                @if($transaction->reference_id)
                                    <small class="text-muted">{{ $transaction->reference_id }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada riwayat transaksi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
