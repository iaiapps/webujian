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
                    
                    {{-- SISTEM KREDIT - Tampilkan kredit bukan plan --}}
                    <span class="badge bg-warning text-dark mb-3">
                        <i class="bi bi-coin"></i> {{ $user->credits ?? 0 }} Kredit
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
                {{-- KELAS DINONAKTIFKAN - Tidak ditampilkan --}}
                {{-- <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ $stats['total_classes'] }}</h3>
                            <small class="text-muted">Kelas</small>
                            <div class="small text-muted">Max: {{ $user->max_classes }}</div>
                        </div>
                    </div>
                </div> --}}
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
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ $user->credits ?? 0 }}</h3>
                            <small class="text-muted">Kredit</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Credit Info (was Subscription History) --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Kredit</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Kredit Tersedia:</strong> {{ $user->credits ?? 0 }}</p>
                            <p class="mb-0"><small class="text-muted">1 Kredit = 1 Paket Tes</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
