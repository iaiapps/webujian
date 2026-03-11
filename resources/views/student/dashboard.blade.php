{{-- resources/views/student/dashboard.blade.php --}}
@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
    <div class="container-fluid py-4">
        {{-- Welcome Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <h2>Selamat datang, {{ auth()->guard('student')->user()->name }}! 👋</h2>
                @if (auth()->guard('student')->user()->classRoom)
                    <p class="text-muted">{{ auth()->guard('student')->user()->classRoom->name }}</p>
                @endif
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="opacity-75">Total Tes Selesai</h6>
                        <h2 class="mb-0">{{ $stats['total_completed'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body text-center">
                        <h6 class="opacity-75">Rata-rata Skor</h6>
                        <h2 class="mb-0">{{ number_format($stats['avg_score'] ?? 0, 1) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body text-center">
                        <h6 class="opacity-75">Skor Tertinggi</h6>
                        <h2 class="mb-0">{{ number_format($stats['highest_score'] ?? 0) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-white">
                    <div class="card-body text-center">
                        <h6 class="opacity-75">Total Jawaban Benar</h6>
                        <h2 class="mb-0">{{ number_format($stats['total_correct']) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ongoing Test --}}
        @if ($ongoingTest && $ongoingTest->package)
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <h5 class="alert-heading">Tes Sedang Berlangsung</h5>
                <p class="mb-2"><strong>{{ $ongoingTest->package->title }}</strong></p>
                <p class="mb-3">Anda masih memiliki tes yang belum selesai.</p>
                <a href="{{ route('student.test.continue', $ongoingTest->id) }}" class="btn btn-info">
                    Lanjutkan Tes
                </a>
            </div>
        @endif

        <div class="row g-3">
            {{-- Available Tests --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Tes Tersedia</h5>
                    </div>
                    <div class="card-body">
                        @forelse($availableTests as $test)
                            <div class="border rounded p-3 mb-3">
                                <h6 class="mb-1">{{ $test->title }}</h6>
                                <p class="text-muted small mb-2">{{ $test->description }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $test->duration }} menit
                                        | <i class="bi bi-question-circle"></i> {{ $test->total_questions }} soal
                                        | <i class="bi bi-calendar"></i> Sampai {{ $test->end_date->format('d M Y H:i') }}
                                    </small>
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
                    </div>
                </div>
            </div>

            {{-- History --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Histori Tes</h5>
                    </div>
                    <div class="card-body">
                        @forelse($completedTests->take(5) as $test)
                            @if($test->package)
                            <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                <div>
                                    <h6 class="mb-0 small">{{ $test->package->title }}</h6>
                                    <small class="text-muted">{{ $test->submitted_at?->format('d M Y') ?? '-' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">{{ number_format($test->total_score) }}</span>
                                    <br>
                                    <a href="{{ route('student.test.result', $test->id) }}" class="small">Detail</a>
                                </div>
                            </div>
                            @endif
                        @empty
                            <p class="text-muted text-center py-3 mb-0">Belum ada tes yang dikerjakan</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
