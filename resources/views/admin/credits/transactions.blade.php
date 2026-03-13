@extends('layouts.dashboard')

@section('title', 'Semua Transaksi Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Semua Transaksi Kredit</h2>
        <a href="{{ route('admin.credits.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-success mb-0">+{{ number_format($summary['total_in']) }}</h4>
                    <small class="text-muted">Total Kredit Masuk</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-danger mb-0">-{{ number_format($summary['total_out']) }}</h4>
                    <small class="text-muted">Total Kredit Keluar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Pembelian</option>
                        <option value="usage" {{ request('type') == 'usage' ? 'selected' : '' }}>Penggunaan</option>
                        <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                        <option value="manual_add" {{ request('type') == 'manual_add' ? 'selected' : '' }}>Manual Add</option>
                        <option value="manual_deduct" {{ request('type') == 'manual_deduct' ? 'selected' : '' }}>Manual Deduct</option>
                        <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Guru</th>
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
                                <strong>{{ $transaction->user->name }}</strong><br>
                                <small class="text-muted">{{ $transaction->user->email }}</small>
                            </td>
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
                                @if($transaction->performed_by)
                                    <small class="text-muted">{{ $transaction->performedBy?->name ?? 'Unknown' }}</small>
                                @else
                                    <small class="text-muted">System</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi</td>
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
