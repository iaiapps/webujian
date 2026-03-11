{{-- resources/views/admin/subscriptions/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Kelola Subscription')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Subscription</h2>
            @if ($pendingCount > 0)
                <span class="badge bg-warning fs-5">
                    <i class="bi bi-bell"></i> {{ $pendingCount }} Pending
                </span>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filter --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="plan" class="form-select">
                            <option value="">Semua Plan</option>
                            <option value="pro" {{ request('plan') === 'pro' ? 'selected' : '' }}>PRO</option>
                            <option value="advanced" {{ request('plan') === 'advanced' ? 'selected' : '' }}>ADVANCED
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Guru</th>
                                <th>Lembaga</th>
                                <th>Plan</th>
                                <th>Siklus</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $sub)
                                <tr class="{{ $sub->status === 'pending' ? 'table-warning' : '' }}">
                                    <td>#{{ $sub->id }}</td>
                                    <td>{{ $sub->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $sub->user->name }}</strong><br>
                                        <small class="text-muted">{{ $sub->user->email }}</small>
                                    </td>
                                    <td>{{ $sub->user->institution_name }}</td>
                                    <td><span class="badge bg-primary">{{ strtoupper($sub->plan) }}</span></td>
                                    <td>{{ $sub->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</td>
                                    <td><strong>Rp {{ number_format($sub->amount) }}</strong></td>
                                    <td>
                                        @if ($sub->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($sub->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($sub->status === 'expired')
                                            <span class="badge bg-secondary">Expired</span>
                                        @elseif($sub->status === 'failed')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscriptions.show', $sub) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data subscription</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($subscriptions->hasPages())
                    <div class="mt-3">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
