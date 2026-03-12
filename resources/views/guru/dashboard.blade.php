{{-- resources/views/guru/dashboard.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Guru')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Selamat datang, {{ auth()->user()->name }}!</h1>
        <p class="page-subtitle">{{ auth()->user()->institution_name }}</p>
    </div>

    {{-- Pending Payment Alert --}}
    @if (isset($pendingSubscription) && $pendingSubscription)
        <div class="alert alert-warning mb-4">
            <i class="bi bi-hourglass-split me-2"></i>
            <strong>Menunggu Verifikasi!</strong> Permintaan upgrade ke
            <strong>{{ strtoupper($pendingSubscription->plan) }}</strong>
            ({{ $pendingSubscription->billing_cycle == 'monthly' ? 'Bulanan' : 'Tahunan' }}) sedang diproses admin.
            <br><small>Invoice: {{ $pendingSubscription->invoice_number }} | Diajukan:
                {{ $pendingSubscription->created_at->format('d M Y H:i') }}</small>
        </div>
    @endif

    {{-- Plan Info Card --}}
    <x-ui.card class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        @if ($planInfo['current_plan'] == 'free')
                            <span class="badge badge-primary" style="font-size: 0.9rem; padding: 8px 16px;">FREE</span>
                        @elseif($planInfo['current_plan'] == 'pro')
                            <span class="badge badge-orange" style="font-size: 0.9rem; padding: 8px 16px;">PRO</span>
                        @else
                            <span class="badge badge-success" style="font-size: 0.9rem; padding: 8px 16px;">ADVANCED</span>
                        @endif
                    </div>
                    <div>
                        <h6 class="mb-0" style="font-weight: 600;">Plan Aktif</h6>
                        @if ($planInfo['current_plan'] != 'free' && $planInfo['expired_at'])
                            <small class="text-muted">
                                Berlaku sampai: {{ $planInfo['expired_at']->format('d M Y') }}
                                @if ($planInfo['days_remaining'] > 0)
                                    <span class="badge badge-{{ $planInfo['days_remaining'] < 7 ? 'warning' : 'info' }}"
                                        style="margin-left: 8px;">
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
                @if ($planInfo['current_plan'] == 'free')
                    <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-accent">
                        <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                    </a>
                @else
                    <a href="{{ route('guru.subscription.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-receipt"></i> Lihat Subscription
                    </a>
                @endif
            </div>
        </div>
    </x-ui.card>

    {{-- Plan Expired Alert --}}
    @if ($planInfo['is_expired'])
        <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Plan Anda telah expired!</strong> Akun Anda telah dikembalikan ke FREE plan.
            <a href="{{ route('guru.subscription.pricing') }}" class="alert-link">Upgrade sekarang</a> untuk mengakses
            fitur premium.
        </div>
    @elseif($planInfo['days_remaining'] !== null && $planInfo['days_remaining'] <= 7 && $planInfo['days_remaining'] > 0)
        <div class="alert alert-warning mb-4">
            <i class="bi bi-clock-fill me-2"></i>
            Plan {{ strtoupper($planInfo['current_plan']) }} Anda akan berakhir dalam
            <strong>{{ $planInfo['days_remaining'] }} hari</strong>.
            <a href="{{ route('guru.subscription.pricing') }}" class="alert-link">Perpanjang sekarang</a>
        </div>
    @endif

    {{-- Over Limit Warning --}}
    @if (isset($overLimit) && count($overLimit) > 0)
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Perhatian!</strong> Beberapa data melebihi limit plan FREE Anda:
            <ul class="mb-0 mt-2">
                @foreach ($overLimit as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <small>Data lama tetap tersimpan, namun Anda tidak bisa menambah baru. <a
                    href="{{ route('guru.subscription.pricing') }}" class="alert-link">Upgrade</a> untuk menambah
                kapasitas.</small>
        </div>
    @endif

    {{-- Usage Statistics Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <x-ui.stat-card icon="people" icon-variant="{{ $usage['students'] >= 80 ? 'danger' : 'primary' }}"
                value="{{ $stats['total_students'] }}/{{ $stats['max_students'] }}" label="Siswa" />
        </div>

        {{-- KELAS DINONAKTIFKAN - Card kelas disembunyikan --}}
        {{-- <div class="col-xl-4 col-md-6">
            <x-ui.stat-card
                icon="door-open"
                icon-variant="success"
                value="{{ $stats['total_classes'] }}/{{ $stats['max_classes'] }}"
                label="Kelas"
            />
        </div> --}}

        <div class="col-xl-4 col-md-6">
            <x-ui.stat-card icon="question-circle" icon-variant="info"
                value="{{ $stats['total_questions'] }}/{{ $stats['max_questions'] }}" label="Soal" />
        </div>

        <div class="col-xl-4 col-md-6">
            <x-ui.stat-card icon="box" icon-variant="warning"
                value="{{ $stats['total_packages'] }}/{{ $stats['max_packages'] }}" label="Paket Tes" />
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
            <a href="{{ route('guru.packages.create') }}" class="btn btn-info w-100 py-3"
                style="background: var(--info); border-color: var(--info); color: white;">
                <i class="bi bi-box-fill fs-4 d-block mb-2"></i>
                Buat Paket Tes
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('guru.results.index') }}" class="btn btn-warning w-100 py-3" style="color: white;">
                <i class="bi bi-bar-chart-fill fs-4 d-block mb-2"></i>
                Lihat Hasil
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Active Tests --}}
        <div class="col-lg-8">
            <x-ui.card title="Tes Aktif Berlangsung">
                @forelse($activeTests as $test)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1" style="font-weight: 600;">{{ $test->title }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $test->duration }} menit
                                    | <i class="bi bi-calendar"></i> {{ $test->start_date->format('d M Y H:i') }} -
                                    {{ $test->end_date->format('d M Y H:i') }}
                                </small>
                            </div>
                            <span class="badge badge-success">{{ $test->test_attempts_count }} peserta</span>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('guru.packages.show', $test->id) }}"
                                class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">Tidak ada tes yang sedang berlangsung</p>
                @endforelse
            </x-ui.card>
        </div>

        {{-- Quick Stats --}}
        <div class="col-lg-4">
            <x-ui.card class="mb-3">
                <div class="text-center">
                    <h6 class="text-muted mb-2">Total Tes Dikerjakan</h6>
                    <h2 class="mb-0" style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">
                        {{ number_format($totalAttempts) }}</h2>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <h6 class="text-muted mb-2">Rata-rata Skor</h6>
                    <h2 class="mb-0" style="font-size: 2.5rem; font-weight: 800; color: var(--success);">
                        {{ number_format($avgScore ?? 0, 1) }}</h2>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Limit Reached Modal --}}
    @if (session('limit_reached'))
        <div class="modal fade" id="limitModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--warning);">
                        <h5 class="modal-title">⚠️ Limit Tercapai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('limit_reached')['message'] }}</p>
                        <p class="mb-0"><strong>Current:</strong>
                            {{ session('limit_reached')['current'] }}/{{ session('limit_reached')['limit'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
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
