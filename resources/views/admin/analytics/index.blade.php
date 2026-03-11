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
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h4 class="text-danger mb-0">Rp {{ number_format($stats['total_revenue'] / 1000000, 1) }}jt</h4>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Plan Distribution --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Distribusi Plan</h5>
                </div>
                <div class="card-body">
                    @foreach($planDistribution as $plan)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-uppercase fw-bold">{{ $plan->plan }}</span>
                            <span>{{ $plan->count }} guru</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $percentage = $stats['total_users'] > 0 ? ($plan->count / $stats['total_users']) * 100 : 0;
                                $color = match($plan->plan) {
                                    'free' => 'secondary',
                                    'pro' => 'primary',
                                    'advanced' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
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

        {{-- Revenue by Month --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Revenue per Bulan ({{ now()->year }})</h5>
                </div>
                <div class="card-body">
                    @php
                        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                        $maxRevenue = $revenueByMonth->max('total') ?: 1;
                    @endphp
                    @foreach($revenueByMonth as $rev)
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2" style="width: 30px;">{{ $months[$rev->month] }}</span>
                        <div class="progress flex-fill" style="height: 20px;">
                            <div class="progress-bar bg-success" style="width: {{ ($rev->total / $maxRevenue) * 100 }}%">
                                Rp {{ number_format($rev->total / 1000) }}k
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($revenueByMonth->isEmpty())
                        <p class="text-muted text-center">Belum ada data revenue</p>
                    @endif
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
                                    <th>Plan</th>
                                    <th>Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topTeachers as $index => $teacher)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $teacher->name }}</td>
                                    <td>{{ Str::limit($teacher->institution_name, 20) }}</td>
                                    <td><span class="badge bg-{{ $teacher->plan == 'free' ? 'secondary' : ($teacher->plan == 'pro' ? 'primary' : 'success') }}">{{ strtoupper($teacher->plan) }}</span></td>
                                    <td><strong>{{ $teacher->students_count }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Subscriptions --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Subscription Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Guru</th>
                                    <th>Plan</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSubscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->confirmed_at->format('d M Y') }}</td>
                                    <td>{{ $sub->user->name }}</td>
                                    <td><span class="badge bg-primary">{{ strtoupper($sub->plan) }}</span></td>
                                    <td>Rp {{ number_format($sub->amount) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
