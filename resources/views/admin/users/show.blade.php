@extends('layouts.dashboard')

@section('title', 'Detail Guru - ' . $user->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Guru</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- User Info --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->institution_name }}</p>
                    
                    <span class="badge bg-{{ $user->plan == 'free' ? 'secondary' : ($user->plan == 'pro' ? 'primary' : 'success') }} mb-3">
                        {{ strtoupper($user->plan) }}
                    </span>

                    @if(!$user->approved_at)
                        <div class="alert alert-warning py-2">Menunggu Approval</div>
                    @elseif(!$user->is_active)
                        <div class="alert alert-danger py-2">Akun Nonaktif</div>
                    @else
                        <div class="alert alert-success py-2">Akun Aktif</div>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Email</span>
                        <span>{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Telepon</span>
                        <span>{{ $user->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Bergabung</span>
                        <span>{{ $user->created_at->format('d M Y') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Disetujui</span>
                        <span>{{ $user->approved_at ? $user->approved_at->format('d M Y') : '-' }}</span>
                    </li>
                    @if($user->plan_expired_at)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Plan Expired</span>
                        <span>{{ $user->plan_expired_at->format('d M Y') }}</span>
                    </li>
                    @endif
                </ul>
                <div class="card-footer">
                    @if(!$user->approved_at)
                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Setujui
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-{{ $user->is_active ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ $stats['total_students'] }}</h3>
                            <small class="text-muted">Siswa</small>
                            <div class="small text-muted">Max: {{ $user->max_students }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ $stats['total_classes'] }}</h3>
                            <small class="text-muted">Kelas</small>
                            <div class="small text-muted">Max: {{ $user->max_classes }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-info">{{ $stats['total_questions'] }}</h3>
                            <small class="text-muted">Soal</small>
                            <div class="small text-muted">Max: {{ $user->max_questions }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $stats['total_packages'] }}</h3>
                            <small class="text-muted">Paket Tes</small>
                            <div class="small text-muted">Max: {{ $user->max_packages }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscription History --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Riwayat Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Plan</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->subscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->invoice_number }}</td>
                                    <td>{{ strtoupper($sub->plan) }}</td>
                                    <td>Rp {{ number_format($sub->amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sub->status == 'active' ? 'success' : ($sub->status == 'waiting_confirmation' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $sub->created_at->format('d M Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada subscription</td>
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
