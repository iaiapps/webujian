@extends('layouts.dashboard')

@section('title', 'Manajemen Kredit')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Kredit</h2>
            <a href="{{ route('admin.credits.transactions') }}" class="btn btn-info">
                <i class="bi bi-clock-history"></i> Semua Transaksi
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Statistics Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-primary mb-0">{{ number_format($stats['total_users']) }}</h4>
                        <small class="text-muted">Total Guru</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-success mb-0">{{ number_format($stats['total_credits']) }}</h4>
                        <small class="text-muted">Total Kredit Beredar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-danger mb-0">{{ number_format($stats['empty_credits']) }}</h4>
                        <small class="text-muted">Guru Kredit Habis</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-warning mb-0">{{ number_format($stats['low_credits']) }}</h4>
                        <small class="text-muted">Guru Kredit Kritis (&lt;5)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/email..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="credit_filter" class="form-select">
                            <option value="">Semua Kredit</option>
                            <option value="empty" {{ request('credit_filter') == 'empty' ? 'selected' : '' }}>Kredit Habis
                                (0)</option>
                            <option value="low" {{ request('credit_filter') == 'low' ? 'selected' : '' }}>Kredit Kritis
                                (1-5)</option>
                            <option value="high" {{ request('credit_filter') == 'high' ? 'selected' : '' }}>Kredit Tinggi
                                (>50)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Users Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th>Kredit</th>
                                <th>Total Transaksi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->name }}</strong><br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @if ($user->credits == 0)
                                            <span class="badge bg-danger">{{ $user->credits }} Kredit</span>
                                        @elseif($user->credits <= 5)
                                            <span class="badge bg-warning text-dark">{{ $user->credits }} Kredit</span>
                                        @else
                                            <span class="badge bg-success">{{ $user->credits }} Kredit</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->credit_transactions_count }} transaksi</td>
                                    <td>
                                        <a href="{{ route('admin.credits.show', $user) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Tidak ada data guru</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
