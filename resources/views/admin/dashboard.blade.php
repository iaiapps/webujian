{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang kembali! Berikut ringkasan sistem Anda.</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <x-ui.stat-card
                icon="people-fill"
                icon-variant="primary"
                value="{{ $stats['total_users'] }}"
                label="Total Guru"
                change="{{ $stats['active_users'] }} aktif"
                change-type="positive"
            />
        </div>

        <div class="col-md-6 col-xl-3">
            <x-ui.stat-card
                icon="person-badge-fill"
                icon-variant="success"
                value="{{ number_format($stats['total_students']) }}"
                label="Total Siswa"
            />
        </div>

        <div class="col-md-6 col-xl-3">
            <x-ui.stat-card
                icon="question-circle-fill"
                icon-variant="info"
                value="{{ number_format($stats['total_questions']) }}"
                label="Total Soal"
            />
        </div>

        <div class="col-md-6 col-xl-3">
            <x-ui.stat-card
                icon="currency-dollar"
                icon-variant="orange"
                value="Rp {{ number_format($revenue['this_month']) }}"
                label="Revenue Bulan Ini"
            />
        </div>
    </div>

    <div class="row g-4">
        {{-- Pending Approval --}}
        <div class="col-lg-6">
            <x-ui.card title="Menunggu Persetujuan ({{ $stats['pending_approval'] }})">
                @forelse($pendingUsers as $user)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <h6 class="mb-0" style="font-weight: 600;">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->institution_name }}</small><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-primary">
                            Review
                        </a>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">Tidak ada guru yang menunggu persetujuan</p>
                @endforelse

                @if ($pendingUsers->count() > 0)
                    <a href="{{ route('admin.users.pending') }}" class="btn btn-outline-primary w-100 mt-2">
                        Lihat Semua
                    </a>
                @endif
            </x-ui.card>
        </div>

        {{-- Plan Distribution --}}
        <div class="col-lg-6">
            <x-ui.card title="Distribusi Plan">
                @foreach ($planDistribution as $plan)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-uppercase fw-semibold">{{ $plan->plan }}</span>
                            <span class="fw-bold">{{ $plan->total }} guru</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-{{ $plan->plan === 'free' ? 'secondary' : ($plan->plan === 'pro' ? 'primary' : 'success') }}"
                                style="width: {{ $stats['total_users'] > 0 ? ($plan->total / $stats['total_users']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-ui.card>
        </div>

        {{-- Recent Subscriptions --}}
        <div class="col-12">
            <x-ui.card title="Transaksi Terbaru">
                <div class="table-container" style="border: none; border-radius: 0;">
                    <table class="table">
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
                                    <td><span class="badge badge-primary">{{ strtoupper($sub->plan) }}</span></td>
                                    <td>Rp {{ number_format($sub->amount) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $sub->status === 'active' ? 'success' : ($sub->status === 'pending' ? 'warning' : 'danger') }}">
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
            </x-ui.card>
        </div>
    </div>
@endsection