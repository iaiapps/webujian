{{-- resources/views/guru/results/package.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Hasil - ' . $package->title)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $package->title }}</h2>
                <p class="text-muted mb-0">Hasil & Analisis</p>
            </div>
            <div>
                <a href="{{ route('guru.results.export', $package) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Export Excel
                </a>
                <a href="{{ route('guru.results.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-people text-primary fs-1"></i>
                        <h3 class="mt-2">{{ $statistics['total_attempts'] }}</h3>
                        <p class="text-muted mb-0">Total Peserta</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up text-success fs-1"></i>
                        <h3 class="mt-2">{{ number_format($statistics['avg_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Rata-rata Skor</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-trophy text-warning fs-1"></i>
                        <h3 class="mt-2">{{ number_format($statistics['highest_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Skor Tertinggi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-down text-danger fs-1"></i>
                        <h3 class="mt-2">{{ number_format($statistics['lowest_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Skor Terendah</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Ranking --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Ranking Peserta</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Nama</th>
                                        {{-- KELAS DINONAKTIFKAN - Kolom kelas dihilangkan --}}
                                        {{-- <th>Kelas</th> --}}
                                        <th>Skor</th>
                                        <th>Benar</th>
                                        <th>Salah</th>
                                        <th>Kosong</th>
                                        <th>Durasi</th>
                                        <th>Pelanggaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attempts as $index => $attempt)
                                        <tr>
                                            <td>
                                                @if ($index < 3)
                                                    <span
                                                        class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }}">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                @else
                                                    #{{ $index + 1 }}
                                                @endif
                                            </td>
                                            <td><strong>{{ $attempt->student->name }}</strong></td>
                                            {{-- KELAS DINONAKTIFKAN --}}
                                            {{-- <td>
                                                @if ($attempt->student->classRoom)
                                                    <span
                                                        class="badge bg-info">{{ $attempt->student->classRoom->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td> --}}
                                            <td><strong
                                                    class="text-primary">{{ number_format($attempt->total_score, 1) }}</strong>
                                            </td>
                                            <td><span class="badge bg-success">{{ $attempt->correct_answers }}</span></td>
                                            <td><span class="badge bg-danger">{{ $attempt->wrong_answers }}</span></td>
                                            <td><span class="badge bg-secondary">{{ $attempt->unanswered }}</span></td>
                                            <td>
                                                @php
                                                    $duration = $attempt->start_time && $attempt->submitted_at 
                                                        ? $attempt->submitted_at->diffInMinutes($attempt->start_time) 
                                                        : null;
                                                @endphp
                                                {{ $duration !== null ? $duration . ' menit' : '-' }}
                                            </td>
                                            <td>
                                                @if ($attempt->violations_count > 0)
                                                    @php
                                                        $violationsLog = $attempt->violations_log ? json_decode($attempt->violations_log, true) : [];
                                                        $logHtml = '';
                                                        foreach ($violationsLog as $log) {
                                                            $typeLabel = match($log['type']) {
                                                                'tab_switch' => 'Pindah Tab',
                                                                'window_blur' => 'Klik Luar',
                                                                'right_click' => 'Klik Kanan',
                                                                'copy' => 'Copy',
                                                                'cut' => 'Cut',
                                                                'paste' => 'Paste',
                                                                'devtools' => 'DevTools',
                                                                default => $log['type'],
                                                            };
                                                            $logHtml .= '<div>• ' . $typeLabel . ' (' . \Carbon\Carbon::parse($log['time'])->format('H:i:s') . ')</div>';
                                                        }
                                                    @endphp
                                                    @if ($attempt->is_flagged)
                                                        <span class="badge bg-danger" 
                                                            data-bs-toggle="popover" 
                                                            data-bs-title="Detail Pelanggaran" 
                                                            data-bs-html="true"
                                                            data-bs-content="{{ $logHtml }}">
                                                            Terflag ({{ $attempt->violations_count }}x)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark" 
                                                            data-bs-toggle="popover" 
                                                            data-bs-title="Detail Pelanggaran" 
                                                            data-bs-html="true"
                                                            data-bs-content="{{ $logHtml }}">
                                                            {{ $attempt->violations_count }}x
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('guru.results.student', $attempt->student) }}"
                                                        class="btn btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if ($attempt->is_flagged)
                                                        @if ($attempt->reset_token)
                                                            <button type="button" class="btn btn-secondary" 
                                                                data-bs-toggle="popover" 
                                                                data-bs-title="Token Reset" 
                                                                data-bs-content="{{ $attempt->reset_token }} (exp: {{ $attempt->reset_token_expires_at?->format('d/m H:i') }})">
                                                                <i class="bi bi-key"></i>
                                                            </button>
                                                            <form action="{{ route('guru.results.clear-reset-token', $attempt) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger" 
                                                                    onclick="return confirm('Hapus token?')">
                                                                    <i class="bi bi-x-lg"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('guru.results.reset-token', $attempt) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning">
                                                                    <i class="bi bi-key"></i> Generate Token
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Question Analysis --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Analisis Soal</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Soal Paling Sulit (Success Rate Terendah):</h6>
                            @foreach (array_slice($questionAnalysis, 0, 5) as $index => $qa)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <span class="badge bg-info">{{ $qa['category'] }}</span>
                                            <span
                                                class="badge bg-{{ $qa['difficulty'] === 'easy' ? 'success' : ($qa['difficulty'] === 'medium' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($qa['difficulty']) }}
                                            </span>
                                            <p class="mb-1 small mt-1">{{ Str::limit($qa['question_text'], 100) }}</p>
                                        </div>
                                        <div class="text-end ms-3">
                                            <strong
                                                class="text-{{ $qa['success_rate'] < 30 ? 'danger' : ($qa['success_rate'] < 60 ? 'warning' : 'success') }}">
                                                {{ $qa['success_rate'] }}%
                                            </strong><br>
                                            <small
                                                class="text-muted">{{ $qa['correct_count'] }}/{{ $qa['total_answers'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Score Distribution --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Distribusi Skor</h5>
                    </div>
                    <div class="card-body">
                        @foreach (['80-100' => 'success', '60-79' => 'primary', '40-59' => 'warning', '20-39' => 'danger', '0-19' => 'secondary'] as $range => $color)
                            @php
                                $count = $scoreDistribution[$range] ?? 0;
                                $percentage =
                                    $statistics['total_attempts'] > 0
                                        ? round(($count / $statistics['total_attempts']) * 100)
                                        : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $range }}</span>
                                    <span><strong>{{ $count }}</strong> siswa</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%">
                                        {{ $percentage }}%
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Statistik Jawaban</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Rata-rata Benar</span>
                                <strong class="text-success">{{ number_format($statistics['avg_correct'], 1) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Rata-rata Salah</span>
                                <strong class="text-danger">{{ number_format($statistics['avg_wrong'], 1) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Rata-rata Kosong</span>
                                <strong
                                    class="text-secondary">{{ number_format($statistics['avg_unanswered'], 1) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
