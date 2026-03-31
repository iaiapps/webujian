{{-- resources/views/student/dashboard.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Siswa')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Selamat datang, {{ auth()->guard('student')->user()->name }}!</h1>
        @if (auth()->guard('student')->user()->classRoom)
            <p class="page-subtitle">{{ auth()->guard('student')->user()->classRoom->name }}</p>
        @endif
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <x-ui.stat-card icon="check-circle" icon-variant="primary" value="{{ $stats['total_completed'] }}"
                label="Total Tes Selesai" />
        </div>
        <div class="col-md-3">
            <x-ui.stat-card icon="graph-up-arrow" icon-variant="success"
                value="{{ number_format($stats['avg_score'] ?? 0, 1) }}" label="Rata-rata Skor" />
        </div>
        <div class="col-md-3">
            <x-ui.stat-card icon="trophy" icon-variant="info" value="{{ number_format($stats['highest_score'] ?? 0) }}"
                label="Skor Tertinggi" />
        </div>
        <div class="col-md-3">
            <x-ui.stat-card icon="check2-all" icon-variant="orange" value="{{ number_format($stats['total_correct']) }}"
                label="Total Jawaban Benar" />
        </div>
    </div>

    {{-- Ongoing Test --}}
    @if ($ongoingTest && $ongoingTest->package)
        <div class="alert alert-info mb-4">
            <h5 class="alert-heading">Tes Sedang Berlangsung</h5>
            <p class="mb-2"><strong>{{ $ongoingTest->package->title }}</strong></p>
            <p class="mb-3">Anda masih memiliki tes yang belum selesai.</p>
            <a href="{{ route('student.test.continue', $ongoingTest->id) }}" class="btn btn-primary">
                Lanjutkan Tes
            </a>
        </div>
    @endif

    <div class="row g-4">
        {{-- Available Tests --}}
        <div class="col-lg-8">
            <x-ui.card title="Tes Tersedia">
                @forelse($availableTests as $test)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-1" style="font-weight: 600;">{{ $test->title }}</h6>
                        <p class="text-muted small mb-2">{{ $test->description }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock"></i> {{ $test->duration }} menit
                                    | <i class="bi bi-question-circle"></i> {{ $test->total_questions }} soal
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Mulai {{ $test->start_date->format('d M Y H:i') }}
                                    - Sampai {{ $test->end_date->format('d M Y H:i') }}
                                </small>
                            </div>
                            <a href="{{ route('student.test.start', $test->id) }}" class="btn btn-primary btn-sm">
                                Mulai Tes
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Tidak ada tes yang tersedia saat ini</p>
                    </div>
                @endforelse
            </x-ui.card>
        </div>

        {{-- History --}}
        <div class="col-lg-4">
            <x-ui.card title="Histori Tes">
                @forelse($completedTests->take(5) as $test)
                    @if ($test->package)
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-0 small" style="font-weight: 600;">{{ $test->package->title }}</h6>
                                <small class="text-muted">{{ $test->submitted_at?->format('d M Y') ?? '-' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-primary">{{ number_format($test->total_score) }}</span>
                                <br>
                                <a href="{{ route('student.test.result', $test->id) }}" class="small">Detail</a>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-muted text-center py-3 mb-0">Belum ada tes yang dikerjakan</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>
@endsection
