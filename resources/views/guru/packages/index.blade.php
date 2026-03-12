{{-- resources/views/guru/packages/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Paket Tes')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Paket Tes</h2>
                <p class="text-muted mb-0">
                    {{-- SISTEM KREDIT - Tampilkan kredit bukan paket limit --}}
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-coin me-1"></i>{{ auth()->user()->credits }} Kredit
                    </span>
                    tersedia untuk membuat paket
                </p>
            </div>
            <a href="{{ route('guru.packages.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Buat Paket Tes
            </a>
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
                <form method="GET" action="{{ route('guru.packages.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Cari judul paket..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Akan Datang
                            </option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Berakhir
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('guru.packages.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Packages List --}}
        <div class="row g-3">
            @forelse($packages as $package)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="mb-0">{{ $package->title }}</h5>
                                @if ($package->isAvailable())
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($package->start_date > now())
                                    <span class="badge bg-info">Akan Datang</span>
                                @else
                                    <span class="badge bg-secondary">Berakhir</span>
                                @endif
                            </div>

                            @if ($package->description)
                                <p class="text-muted small mb-3">{{ Str::limit($package->description, 80) }}</p>
                            @endif

                            <div class="small text-muted mb-3">
                                <div class="mb-1">
                                    <i class="bi bi-question-circle"></i> {{ $package->questions_count }} soal
                                    | <i class="bi bi-clock"></i> {{ $package->duration }} menit
                                </div>
                                <div class="mb-1">
                                    <i class="bi bi-calendar"></i> {{ $package->start_date->format('d M Y') }} -
                                    {{ $package->end_date->format('d M Y') }}
                                </div>
                                <div>
                                    <i class="bi bi-people"></i> {{ $package->test_attempts_count }} peserta
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('guru.packages.show', $package) }}"
                                    class="btn btn-sm btn-info flex-fill">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="{{ route('guru.packages.edit', $package) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('guru.packages.destroy', $package) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus paket {{ $package->title }}?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-4">Belum ada paket tes. Buat paket tes pertama Anda!</p>
                            <a href="{{ route('guru.packages.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Buat Paket Tes
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($packages->hasPages())
            <div class="mt-4">
                {{ $packages->links() }}
            </div>
        @endif
    </div>

    {{-- Limit Modal --}}
    @if (session('limit_reached'))
        <div class="modal fade show" id="limitModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">⚠️ Limit Tercapai</h5>
                        <button type="button" class="btn-close"
                            onclick="this.closest('.modal').style.display='none'"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('limit_reached')['message'] }}</p>
                        <p class="mb-0"><strong>Saat ini:</strong>
                            {{ session('limit_reached')['current'] }}/{{ session('limit_reached')['limit'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="this.closest('.modal').style.display='none'">Tutup</button>
                        <a href="{{ route('guru.credits.topup') }}" class="btn btn-primary">Beli Kredit</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
