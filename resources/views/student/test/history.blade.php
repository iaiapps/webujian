{{-- resources/views/student/test/history.blade.php --}}
@extends('layouts.student')

@section('title', 'Histori Tes')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Histori Tes</h2>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @forelse($attempts as $attempt)
                    @if($attempt->package)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">{{ $attempt->package->title }}</h5>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> {{ $attempt->submitted_at?->format('d M Y H:i') ?? '-' }}
                                </small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h3 class="mb-0 text-primary">{{ number_format($attempt->total_score, 1) }}</h3>
                                <small class="text-muted">Skor</small>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ route('student.test.result', $attempt) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada histori tes</p>
                    </div>
                @endforelse

                @if ($attempts->hasPages())
                    <div class="mt-3">
                        {{ $attempts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
