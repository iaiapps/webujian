{{-- resources/views/guru/results/student.blade.php --}}
@extends('layouts.guru')

@section('title', 'Hasil Siswa - ' . $student->name)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $student->name }}</h2>
                <p class="text-muted mb-0">
                    Username: <code>{{ $student->username }}</code> |
                    Kelas: {{ $student->classRoom ? $student->classRoom->name : '-' }}
                </p>
            </div>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Statistics --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-primary">{{ $statistics['total_tests'] }}</h3>
                        <p class="text-muted mb-0">Total Tes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ number_format($statistics['avg_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Rata-rata Skor</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ number_format($statistics['highest_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Skor Tertinggi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-danger">{{ number_format($statistics['lowest_score'], 1) }}</h3>
                        <p class="text-muted mb-0">Skor Terendah</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- History --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Riwayat Tes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Paket Tes</th>
                                        <th>Skor</th>
                                        <th>Benar</th>
                                        <th>Salah</th>
                                        <th>Kosong</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attempts as $attempt)
                                        <tr>
                                            <td>{{ $attempt->submitted_at->format('d M Y') }}</td>
                                            <td>{{ $attempt->package->title }}</td>
                                            <td><strong
                                                    class="text-primary">{{ number_format($attempt->total_score, 1) }}</strong>
                                            </td>
                                            <td><span class="badge bg-success">{{ $attempt->correct_answers }}</span></td>
                                            <td><span class="badge bg-danger">{{ $attempt->wrong_answers }}</span></td>
                                            <td><span class="badge bg-secondary">{{ $attempt->unanswered }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance by Category --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Performa per Kategori</h5>
                    </div>
                    <div class="card-body">
                        @forelse($categoryPerformance as $perf)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small"><strong>{{ $perf['category'] }}</strong></span>
                                    <span class="small">{{ $perf['correct'] }}/{{ $perf['total'] }}</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $perf['percentage'] >= 70 ? 'success' : ($perf['percentage'] >= 50 ? 'warning' : 'danger') }}"
                                        style="width: {{ $perf['percentage'] }}%">
                                        {{ $perf['percentage'] }}%
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center">Belum ada data kategori</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
