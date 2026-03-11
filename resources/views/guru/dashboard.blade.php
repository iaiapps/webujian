{{-- resources/views/guru/dashboard.blade.php --}}
@extends('layouts.guru')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="container-fluid py-4">
        {{-- Welcome Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <h2>Selamat datang, {{ auth()->user()->name }}! 👋</h2>
                <p class="text-muted">{{ auth()->user()->institution_name }}</p>
            </div>
        </div>

        {{-- Pending Payment Alert --}}
        @if (isset($pendingSubscription) && $pendingSubscription)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-hourglass-split me-2"></i>
                <strong>Menunggu Verifikasi!</strong> Permintaan upgrade ke <strong>{{ strtoupper($pendingSubscription->plan) }}</strong> 
                ({{ $pendingSubscription->billing_cycle == 'monthly' ? 'Bulanan' : 'Tahunan' }}) sedang diproses admin.
                <br><small>Invoice: {{ $pendingSubscription->invoice_number }} | Diajukan: {{ $pendingSubscription->created_at->format('d M Y H:i') }}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Plan Info Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($planInfo['current_plan'] == 'free')
                                    <span class="badge bg-secondary fs-6 px-3 py-2">FREE</span>
                                @elseif($planInfo['current_plan'] == 'pro')
                                    <span class="badge bg-primary fs-6 px-3 py-2">PRO</span>
                                @else
                                    <span class="badge bg-success fs-6 px-3 py-2">ADVANCED</span>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-0">Plan Aktif</h6>
                                @if($planInfo['current_plan'] != 'free' && $planInfo['expired_at'])
                                    <small class="text-muted">
                                        Berlaku sampai: {{ $planInfo['expired_at']->format('d M Y') }}
                                        @if($planInfo['days_remaining'] > 0)
                                            <span class="badge bg-{{ $planInfo['days_remaining'] < 7 ? 'warning' : 'info' }}">
                                                {{ $planInfo['days_remaining'] }} hari lagi
                                            </span>
                                        @endif
                                    </small>
                                @elseif($planInfo['current_plan'] == 'free')
                                    <small class="text-muted">Tidak ada batas waktu</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        @if($planInfo['current_plan'] == 'free')
                            <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                            </a>
                        @else
                            <a href="{{ route('guru.subscription.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-receipt"></i> Lihat Subscription
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Expired Alert --}}
        @if ($planInfo['is_expired'])
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Plan Anda telah expired!</strong> Akun Anda telah dikembalikan ke FREE plan.
                <a href="{{ route('guru.subscription.pricing') }}" class="alert-link">Upgrade sekarang</a> untuk mengakses
                fitur premium.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif($planInfo['days_remaining'] !== null && $planInfo['days_remaining'] <= 7 && $planInfo['days_remaining'] > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-clock-fill me-2"></i>
                Plan {{ strtoupper($planInfo['current_plan']) }} Anda akan berakhir dalam
                <strong>{{ $planInfo['days_remaining'] }} hari</strong>.
                <a href="{{ route('guru.subscription.pricing') }}" class="alert-link">Perpanjang sekarang</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Over Limit Warning --}}
        @if (isset($overLimit) && count($overLimit) > 0)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Perhatian!</strong> Beberapa data melebihi limit plan FREE Anda:
                <ul class="mb-0 mt-2">
                    @foreach($overLimit as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <small>Data lama tetap tersimpan, namun Anda tidak bisa menambah baru. <a href="{{ route('guru.subscription.pricing') }}" class="alert-link">Upgrade</a> untuk menambah kapasitas.</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Usage Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1">Siswa</p>
                                <h3 class="mb-0">{{ $stats['total_students'] }}/{{ $stats['max_students'] }}</h3>
                            </div>
                            <span class="badge bg-{{ $usage['students'] >= 80 ? 'danger' : 'primary' }}">
                                {{ $usage['students'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $usage['students'] >= 80 ? 'danger' : 'primary' }}"
                                style="width: {{ $usage['students'] }}%"></div>
                        </div>
                        @if ($usage['students'] >= 80)
                            <small class="text-danger">Hampir mencapai limit!</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1">Kelas</p>
                                <h3 class="mb-0">{{ $stats['total_classes'] }}/{{ $stats['max_classes'] }}</h3>
                            </div>
                            <span class="badge bg-success">{{ $usage['classes'] }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $usage['classes'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1">Soal</p>
                                <h3 class="mb-0">{{ $stats['total_questions'] }}/{{ $stats['max_questions'] }}</h3>
                            </div>
                            <span class="badge bg-info">{{ $usage['questions'] }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $usage['questions'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1">Paket Tes</p>
                                <h3 class="mb-0">{{ $stats['total_packages'] }}/{{ $stats['max_packages'] }}</h3>
                            </div>
                            <span class="badge bg-warning">{{ $usage['packages'] }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $usage['packages'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="{{ route('guru.students.create') }}" class="btn btn-primary w-100 py-3">
                    <i class="bi bi-person-plus-fill fs-4 d-block mb-2"></i>
                    Tambah Siswa
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('guru.questions.create') }}" class="btn btn-success w-100 py-3">
                    <i class="bi bi-file-earmark-plus-fill fs-4 d-block mb-2"></i>
                    Buat Soal
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('guru.packages.create') }}" class="btn btn-info w-100 py-3">
                    <i class="bi bi-box-fill fs-4 d-block mb-2"></i>
                    Buat Paket Tes
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('guru.results.index') }}" class="btn btn-warning w-100 py-3">
                    <i class="bi bi-bar-chart-fill fs-4 d-block mb-2"></i>
                    Lihat Hasil
                </a>
            </div>
        </div>

        <div class="row g-3">
            {{-- Active Tests --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Tes Aktif Berlangsung</h5>
                    </div>
                    <div class="card-body">
                        @forelse($activeTests as $test)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $test->title }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $test->duration }} menit
                                            | <i class="bi bi-calendar"></i> {{ $test->start_date->format('d M Y H:i') }} -
                                            {{ $test->end_date->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success">{{ $test->test_attempts_count }} peserta</span>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('guru.packages.show', $test->id) }}"
                                        class="btn btn-sm btn-outline-primary">Detail</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">Tidak ada tes yang sedang berlangsung</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Tes Dikerjakan</h6>
                        <h2 class="mb-0">{{ number_format($totalAttempts) }}</h2>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Rata-rata Skor</h6>
                        <h2 class="mb-0">{{ number_format($avgScore ?? 0, 1) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Limit Reached Modal --}}
    @if (session('limit_reached'))
        <div class="modal fade" id="limitModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">⚠️ Limit Tercapai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('limit_reached')['message'] }}</p>
                        <p class="mb-0"><strong>Current:</strong>
                            {{ session('limit_reached')['current'] }}/{{ session('limit_reached')['limit'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-primary">Upgrade Plan</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new bootstrap.Modal(document.getElementById('limitModal')).show();
            });
        </script>
    @endif
@endsection
