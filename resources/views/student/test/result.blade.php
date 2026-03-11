{{-- resources/views/student/test/result.blade.php --}}
@extends('layouts.student')

@section('title', 'Hasil Tes')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h2 class="mt-3">Tes Selesai!</h2>
                    <p class="text-muted">{{ $package->title }}</p>
                </div>

                {{-- Score Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="text-muted mb-3">TOTAL SKOR</h6>
                        <h1 class="display-1 fw-bold text-primary mb-0">{{ number_format($attempt->total_score, 1) }}</h1>

                        @if ($ranking)
                            <p class="text-muted mt-3">
                                <i class="bi bi-trophy"></i> Peringkat {{ $ranking }} dari {{ $totalAttempts }} peserta
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body">
                                <i class="bi bi-check-circle text-success fs-1"></i>
                                <h3 class="mt-2 mb-0">{{ $attempt->correct_answers }}</h3>
                                <p class="text-muted mb-0">Benar</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body">
                                <i class="bi bi-x-circle text-danger fs-1"></i>
                                <h3 class="mt-2 mb-0">{{ $attempt->wrong_answers }}</h3>
                                <p class="text-muted mb-0">Salah</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body">
                                <i class="bi bi-dash-circle text-secondary fs-1"></i>
                                <h3 class="mt-2 mb-0">{{ $attempt->unanswered }}</h3>
                                <p class="text-muted mb-0">Tidak Dijawab</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Per Category --}}
                @php
                    $categories = $attempt->answers->groupBy('question.category.name');
                @endphp
                @if ($categories->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Hasil per Kategori</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($categories as $categoryName => $answers)
                                @php
                                    $correct = $answers->where('is_correct', true)->count();
                                    $total = $answers->count();
                                    $percentage = $total > 0 ? round(($correct / $total) * 100) : 0;
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><strong>{{ $categoryName }}</strong></span>
                                        <span class="text-muted">{{ $correct }}/{{ $total }}
                                            ({{ $percentage }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}"
                                            style="width: {{ $percentage }}%">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Review Button --}}
                @if ($package->show_explanation)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h5 class="mb-3">Ingin melihat pembahasan soal?</h5>
                            <a href="{{ route('student.test.review', $attempt) }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-book"></i> Lihat Pembahasan
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="text-center">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Kembali ke Dashboard
                    </a>
                    <a href="{{ route('student.test.history') }}" class="btn btn-outline-primary">
                        <i class="bi bi-clock-history"></i> Lihat Histori
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
