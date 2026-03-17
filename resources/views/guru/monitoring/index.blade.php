{{-- resources/views/guru/monitoring/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Monitoring - Semua Tes Aktif')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Monitoring</h2>
                <p class="text-muted mb-0">Pantau semua tes yang sedang berlangsung</p>
            </div>
        </div>

        @if ($activePackages->count() > 0)
            <div class="row g-4">
                @foreach ($activePackages as $package)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-truncate" style="max-width: 70%;" title="{{ $package->title }}">
                                    {{ $package->title }}
                                </h5>
                                @if ($package->flagged_count > 0)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle"></i> {{ $package->flagged_count }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-0 text-primary">{{ $package->test_attempts_count }}</h4>
                                            <small class="text-muted">Sedang Mengerjakan</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-0 text-warning">{{ $package->flagged_count }}</h4>
                                            <small class="text-muted">Terflag</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="bi bi-clock"></i> 
                                        {{ $package->start_date->format('d M Y H:i') }} - 
                                        {{ $package->end_date->format('d M Y H:i') }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-hourglass"></i> 
                                        {{ $package->duration }} menit
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="{{ route('guru.monitoring.package', $package) }}" 
                                   class="btn btn-warning w-100 text-white">
                                    <i class="bi bi-broadcast"></i> Buka Monitoring
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">Tidak ada tes yang sedang berlangsung</h5>
                    <p class="text-muted mb-0">Tes aktif akan muncul di sini saat sedang berlangsung</p>
                    <a href="{{ route('guru.packages.index') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-box"></i> Lihat Paket Tes
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
