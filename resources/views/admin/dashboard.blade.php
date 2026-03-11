{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Dashboard Admin</h2>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Guru</p>
                                <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                                <small class="text-success">{{ $stats['active_users'] }} aktif</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-people-fill text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Siswa</p>
                                <h3 class="mb-0">{{ number_format($stats['total_students']) }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-person-badge-fill text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Soal</p>
                                <h3 class="mb-0">{{ number_format($stats['total_questions']) }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-question-circle-fill text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Revenue Bulan Ini</p>
                                <h3 class="mb-0">Rp {{ number_format($revenue['this_month']) }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-currency-dollar text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Pending Approval --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Menunggu Persetujuan ({{ $stats['pending_approval'] }})</h5>
                    </div>
                    <div class="card-body">
                        @forelse($pendingUsers as $user)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                    <small class="text-muted">{{ $user->institution_name }}</small><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3">Tidak ada guru yang menunggu persetujuan</p>
                        @endforelse

                        @if ($pendingUsers->count() > 0)
                            <a href="{{ route('admin.users.pending') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                Lihat Semua
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Plan Distribution --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Distribusi Plan</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($planDistribution as $plan)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-uppercase">{{ $plan->plan }}</span>
                                    <span class="fw-bold">{{ $plan->total }} guru</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $plan->plan === 'free' ? 'secondary' : ($plan->plan === 'pro' ? 'primary' : 'success') }}"
                                        style="width: {{ $stats['total_users'] > 0 ? ($plan->total / $stats['total_users']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Subscriptions --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Guru</th>
                                        <th>Plan</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSubscriptions as $sub)
                                        <tr>
                                            <td>{{ $sub->created_at->format('d M Y H:i') }}</td>
                                            <td>{{ $sub->user->name }}</td>
                                            <td><span class="badge bg-primary">{{ strtoupper($sub->plan) }}</span></td>
                                            <td>Rp {{ number_format($sub->amount) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $sub->status === 'active' ? 'success' : ($sub->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($sub->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Belum ada transaksi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
