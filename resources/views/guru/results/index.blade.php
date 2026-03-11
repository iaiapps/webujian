{{-- resources/views/guru/results/index.blade.php --}}
@extends('layouts.guru')

@section('title', 'Hasil & Analisis')

@section('content')
    <div class="container-fluid py-4">
        <h2 class="mb-4">Hasil & Analisis</h2>

        <div class="row g-3">
            @forelse($packages as $package)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ $package->title }}</h5>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Peserta</span>
                                    <strong>{{ $package->test_attempts_count }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Selesai</span>
                                    <strong class="text-success">{{ $package->completed_attempts_count }}</strong>
                                </div>
                            </div>

                            @if ($package->completed_attempts_count > 0)
                                <a href="{{ route('guru.results.package', $package) }}" class="btn btn-primary w-100">
                                    <i class="bi bi-bar-chart"></i> Lihat Detail
                                </a>
                            @else
                                <button class="btn btn-secondary w-100" disabled>
                                    Belum Ada Hasil
                                </button>
                            @endif
                        </div>
                        <div class="card-footer bg-light text-muted small">
                            {{ $package->start_date->format('d M Y') }} - {{ $package->end_date->format('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">Belum ada paket tes yang sudah dilaksanakan</p>
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
@endsection
