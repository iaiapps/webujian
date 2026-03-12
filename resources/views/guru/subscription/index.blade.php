@extends('layouts.dashboard')

@section('title', 'Subscription')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Subscription</h2>
            @if (!$activeSubscription || auth()->user()->isFree())
                <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Current Plan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Plan Aktif</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">
                            <span
                                class="badge bg-{{ auth()->user()->plan === 'free' ? 'secondary' : (auth()->user()->plan === 'pro' ? 'primary' : 'success') }} fs-5">
                                {{ strtoupper(auth()->user()->plan) }}
                            </span>
                        </h3>

                        @if (!auth()->user()->isFree())
                            <p class="mb-2">
                                <strong>Berlaku sampai:</strong>
                                {{ auth()->user()->plan_expired_at ? auth()->user()->plan_expired_at->format('d M Y') : '-' }}
                                @if (auth()->user()->plan_expired_at)
                                    @php
                                        $daysLeft = now()->diffInDays(auth()->user()->plan_expired_at, false);
                                    @endphp
                                    @if ($daysLeft > 0)
                                        <span class="badge bg-{{ $daysLeft < 7 ? 'warning' : 'info' }}">
                                            {{ $daysLeft }} hari lagi
                                        </span>
                                    @elseif($daysLeft < 0)
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                @endif
                            </p>
                        @endif

                        <div class="row mt-3">
                            <div class="col-md-3">
                                <small class="text-muted">Siswa</small><br>
                                <strong>{{ auth()->user()->studentsCount() }}/{{ auth()->user()->max_students }}</strong>
                            </div>
                            {{-- KELAS DINONAKTIFKAN --}}
                            {{-- <div class="col-md-3">
                                <small class="text-muted">Kelas</small><br>
                                <strong>{{ auth()->user()->classesCount() }}/{{ auth()->user()->max_classes }}</strong>
                            </div> --}}
                            <div class="col-md-3">
                                <small class="text-muted">Soal</small><br>
                                <strong>{{ auth()->user()->questionsCount() }}/{{ auth()->user()->max_questions }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Paket</small><br>
                                <strong>{{ auth()->user()->packagesCount() }}/{{ auth()->user()->max_packages }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        @if (auth()->user()->isFree())
                            <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                            </a>
                        @else
                            <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Plan Lain
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Subscription History --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Riwayat Subscription</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Tanggal</th>
                                <th>Plan</th>
                                <th>Siklus</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $sub)
                                <tr>
                                    <td><small>{{ $sub->invoice_number }}</small></td>
                                    <td>{{ $sub->created_at->format('d M Y') }}</td>
                                    <td><span class="badge bg-primary">{{ strtoupper($sub->plan) }}</span></td>
                                    <td>{{ $sub->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</td>
                                    <td><strong>Rp {{ number_format($sub->amount) }}</strong></td>
                                    <td>
                                        @if ($sub->status === 'pending')
                                            <span class="badge bg-warning">Menunggu Verifikasi</span>
                                        @elseif($sub->status === 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($sub->status === 'expired')
                                            <span class="badge bg-secondary">Expired</span>
                                        @elseif($sub->status === 'rejected')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @elseif($sub->status === 'cancelled')
                                            <span class="badge bg-secondary">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sub->status === 'pending')
                                            <form action="{{ route('guru.subscription.cancel', $sub) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Batalkan permintaan upgrade?')">
                                                    <i class="bi bi-x"></i> Batalkan
                                                </button>
                                            </form>
                                        @elseif($sub->status === 'rejected' && $sub->rejection_reason)
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="tooltip" title="{{ $sub->rejection_reason }}">
                                                <i class="bi bi-info-circle"></i> Alasan
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada riwayat subscription</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($subscriptions->hasPages())
                    <div class="mt-3">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Plan Change History --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Riwayat Perubahan Plan</h5>
            </div>
            <div class="card-body">
                @php
                    $histories = collect();
                    foreach($subscriptions as $sub) {
                        foreach($sub->histories as $history) {
                            $histories->push($history);
                        }
                    }
                    $histories = $histories->sortByDesc('created_at')->take(10);
                @endphp

                @if($histories->count() > 0)
                    <div class="timeline">
                        @foreach($histories as $history)
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    @if($history->action === 'upgraded')
                                        <span class="badge bg-success rounded-pill p-2">
                                            <i class="bi bi-arrow-up"></i>
                                        </span>
                                    @elseif($history->action === 'downgraded')
                                        <span class="badge bg-warning rounded-pill p-2">
                                            <i class="bi bi-arrow-down"></i>
                                        </span>
                                    @elseif($history->action === 'created')
                                        <span class="badge bg-primary rounded-pill p-2">
                                            <i class="bi bi-plus"></i>
                                        </span>
                                    @elseif($history->action === 'cancelled')
                                        <span class="badge bg-danger rounded-pill p-2">
                                            <i class="bi bi-x"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill p-2">
                                            <i class="bi bi-info"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ ucfirst($history->action) }}</strong>
                                        <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                    @if($history->old_plan && $history->new_plan)
                                        <small>
                                            <span class="badge bg-secondary">{{ strtoupper($history->old_plan) }}</span>
                                            <i class="bi bi-arrow-right"></i>
                                            <span class="badge bg-primary">{{ strtoupper($history->new_plan) }}</span>
                                        </small>
                                    @endif
                                    @if($history->amount)
                                        <small class="d-block text-muted">Rp {{ number_format($history->amount) }}</small>
                                    @endif
                                    @if($history->notes)
                                        <small class="d-block text-muted">{{ $history->notes }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted mb-0">Belum ada riwayat perubahan plan</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    @endpush
@endsection
