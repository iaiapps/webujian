@extends('layouts.dashboard')

@section('title', 'Analytics')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Analytics</h2>

    {{-- Overview Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-primary mb-0">{{ number_format($stats['total_users']) }}</h4>
                    <small class="text-muted">Total Guru</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-success mb-0">{{ number_format($stats['total_students']) }}</h4>
                    <small class="text-muted">Total Siswa</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-info mb-0">{{ number_format($stats['total_questions']) }}</h4>
                    <small class="text-muted">Total Soal</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-warning mb-0">{{ number_format($stats['total_packages']) }}</h4>
                    <small class="text-muted">Total Paket</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-secondary mb-0">{{ number_format($stats['total_attempts']) }}</h4>
                    <small class="text-muted">Tes Selesai</small>
                </div>
            </div>
        </div>
        {{-- SISTEM KREDIT - Revenue card dihapus --}}
    </div>

    <div class="row g-4">
        {{-- SISTEM KREDIT - Distribusi Kredit (ganti dari Distribusi Plan) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Distribusi Kredit Guru</h5>
                </div>
                <div class="card-body">
                    @forelse($creditDistribution as $credit)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold">{{ $credit->credit_range }}</span>
                            <span>{{ $credit->count }} guru</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $percentage = $stats['total_users'] > 0 ? ($credit->count / $stats['total_users']) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted text-center">Belum ada data kredit</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Test Statistics --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Statistik Tes</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3 class="text-primary">{{ number_format($testStats['avg_score'] ?? 0, 1) }}</h3>
                            <small class="text-muted">Rata-rata Skor</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-success">{{ number_format($testStats['highest_score'] ?? 0) }}</h3>
                            <small class="text-muted">Skor Tertinggi</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-info">{{ number_format($testStats['total_correct']) }}</h3>
                            <small class="text-muted">Jawaban Benar</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-danger">{{ number_format($testStats['total_wrong']) }}</h3>
                            <small class="text-muted">Jawaban Salah</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISTEM KREDIT - Revenue by Month dihapus --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Sistem</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Sistem Kredit Aktif</h6>
                        <p class="mb-0 small">Guru membutuhkan 1 kredit untuk setiap paket tes yang dibuat. Kredit dapat dibeli melalui menu Kredit di dashboard guru.</p>
                    </div>
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle me-2"></i>Sistem Approval</h6>
                        <p class="mb-0 small">Pendaftaran guru langsung aktif tanpa persetujuan manual.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Teachers --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top 10 Guru (by Siswa)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Institusi</th>
                                    {{-- SISTEM KREDIT - Ganti Plan dengan Kredit --}}
                                    <th>Kredit</th>
                                    <th>Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topTeachers as $index => $teacher)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $teacher->name }}</td>
                                    <td>{{ Str::limit($teacher->institution_name, 20) }}</td>
                                    {{-- SISTEM KREDIT - Tampilkan kredit --}}
                                    <td><span class="badge bg-warning text-dark"><i class="bi bi-coin me-1"></i>{{ $teacher->credits ?? 0 }}</span></td>
                                    <td><strong>{{ $teacher->students_count }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISTEM KREDIT - Recent Subscriptions dihapus, ganti dengan info --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Penggunaan Sistem</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3 class="text-primary">{{ number_format($stats['total_packages']) }}</h3>
                            <small class="text-muted">Total Paket Tes</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-success">{{ number_format($stats['total_attempts']) }}</h3>
                            <small class="text-muted">Tes Selesai</small>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Sistem menggunakan model kredit untuk membuat paket tes
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
