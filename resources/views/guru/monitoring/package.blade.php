{{-- resources/views/guru/monitoring/package.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Monitoring - ' . $package->title)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Live Monitoring</h2>
                <p class="text-muted mb-0">{{ $package->title }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('guru.results.package', $package) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Hasil
                </a>
                <a href="{{ route('guru.packages.show', $package) }}" class="btn btn-outline-primary">
                    <i class="bi bi-box"></i> Detail Paket
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Sedang Mengerjakan</h6>
                                <h3 class="mb-0" id="stat-ongoing">{{ $ongoingAttempts->count() }}</h3>
                            </div>
                            <i class="bi bi-people fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Total Pelanggaran</h6>
                                <h3 class="mb-0" id="stat-violations">{{ $ongoingAttempts->sum('violations_count') }}</h3>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Terflag / Dikeluarkan</h6>
                                <h3 class="mb-0" id="stat-flagged">{{ $flaggedAttempts->count() }}</h3>
                            </div>
                            <i class="bi bi-shield-exclamation fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Aman</h6>
                                <h3 class="mb-0" id="stat-safe">
                                    {{ $ongoingAttempts->where('violations_count', 0)->count() }}</h3>
                            </div>
                            <i class="bi bi-shield-check fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monitoring Table - Ongoing --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Siswa Sedang Mengerjakan</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark" id="last-update">
                        Update: <span class="ms-1" id="update-time">{{ now()->format('H:i:s') }}</span>
                    </span>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="monitoring-table">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Start</th>
                                <th>Sisa Waktu</th>
                                <th>Pelanggaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="monitoring-body">
                            @forelse($ongoingAttempts as $attempt)
                                <tr data-attempt-id="{{ $attempt->id }}">
                                    <td>
                                        <strong>{{ $attempt->student->name }}</strong>
                                        <div class="text-muted small">{{ $attempt->student->username }}</div>
                                    </td>
                                    <td>{{ $attempt->student->classRoom?->name ?? 'Tidak ada kelas' }}</td>
                                    <td>{{ $attempt->start_time->format('H:i:s') }}</td>
                                    <td>
                                        <span
                                            class="{{ $attempt->end_time && $attempt->end_time->diffInMinutes(now()) < 10 ? 'text-danger fw-bold' : '' }}">
                                            {{ $attempt->end_time ? $attempt->end_time->diffForHumans() : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $attempt->violations_count >= $attempt->package->max_violations - 1 ? 'bg-danger' : ($attempt->violations_count > 0 ? 'bg-warning text-dark' : 'bg-success') }}">
                                            {{ $attempt->violations_count }}/{{ $attempt->package->max_violations ?? 3 }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($attempt->violations_count >= $attempt->package->max_violations)
                                            <span class="badge bg-danger">Terflag</span>
                                        @elseif ($attempt->violations_count >= $attempt->package->max_violations - 1)
                                            <span class="badge bg-warning text-dark">Risiko Tinggi</span>
                                        @elseif ($attempt->violations_count > 0)
                                            <span class="badge bg-info">Waspada</span>
                                        @else
                                            <span class="badge bg-success">Aman</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info"
                                            onclick="showViolations({{ $attempt->id }})">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                        Tidak ada siswa yang sedang mengerjakan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Flagged Students Table --}}
        @if ($flaggedAttempts->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>Siswa Terflag (Butuh Token Reset)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Waktu Dikeluarkan</th>
                                    <th>Jumlah Pelanggaran</th>
                                    <th>Token Reset</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($flaggedAttempts as $attempt)
                                    <tr>
                                        <td>
                                            <strong>{{ $attempt->student->name }}</strong>
                                            <div class="text-muted small">{{ $attempt->student->username }}</div>
                                        </td>
                                        <td>{{ $attempt->student->classRoom?->name ?? 'Tidak ada kelas' }}</td>
                                        <td>{{ $attempt->flagged_at?->format('d M Y H:i:s') ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $attempt->violations_count }}x</span>
                                        </td>
                                        <td>
                                            @if ($attempt->reset_token && $attempt->reset_token_expires_at && now()->lessThan($attempt->reset_token_expires_at))
                                                <div class="d-flex align-items-center">
                                                    <code
                                                        class="bg-warning text-dark px-3 py-2 rounded fw-bold fs-5">{{ $attempt->reset_token }}</code>
                                                    <button class="btn btn-sm btn-outline-secondary ms-2"
                                                        onclick="navigator.clipboard.writeText('{{ $attempt->reset_token }}').then(() => alert('Token copied!'))">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    Exp: {{ $attempt->reset_token_expires_at->format('d/m H:i') }}
                                                </div>
                                            @else
                                                <span class="text-muted">Belum ada token</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info"
                                                    onclick="showViolations({{ $attempt->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @if (!$attempt->reset_token || now()->greaterThan($attempt->reset_token_expires_at))
                                                    <form action="{{ route('guru.results.reset-token', $attempt) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning"
                                                            onclick="return confirm('Generate token reset untuk siswa ini?')">
                                                            <i class="bi bi-key"></i> Generate
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('guru.results.clear-reset-token', $attempt) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm('Hapus token ini?')">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
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
        @endif
    </div>

    {{-- Violation Detail Modal --}}
    <div class="modal fade" id="violationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pelanggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="violation-modal-body">
                    {{-- Content loaded via JS --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const packageId = {{ $package->id }};
        let refreshInterval;

        // Load monitoring data via AJAX
        async function loadData() {
            try {
                const response = await fetch(`/guru/monitoring/${packageId}/data`);
                const data = await response.json();

                updateStats(data);
                updateTable(data.attempts);
                updateTime();
            } catch (error) {
                console.error('Error loading monitoring data:', error);
            }
        }

        // Update statistics
        function updateStats(data) {
            document.getElementById('stat-ongoing').textContent = data.total_ongoing;
            document.getElementById('stat-violations').textContent = data.total_violations;

            const highRisk = data.attempts.filter(a => a.violations_count >= a.max_violations - 1).length;
            const safe = data.attempts.filter(a => a.violations_count === 0).length;

            // Note: flagged count comes from PHP rendered data, not AJAX
        }

        // Update table via AJAX
        function updateTable(attempts) {
            const tbody = document.getElementById('monitoring-body');

            if (attempts.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                            Tidak ada siswa yang sedang mengerjakan
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = attempts.map(attempt => {
                const violationPercent = (attempt.violations_count / attempt.max_violations) * 100;
                let statusClass = 'bg-success';
                let statusText = 'Aman';

                if (attempt.violations_count >= attempt.max_violations) {
                    statusClass = 'bg-danger';
                    statusText = 'Terflag';
                } else if (attempt.violations_count >= attempt.max_violations - 1) {
                    statusClass = 'bg-warning text-dark';
                    statusText = 'Risiko Tinggi';
                } else if (attempt.violations_count > 0) {
                    statusClass = 'bg-info';
                    statusText = 'Waspada';
                }

                const rowClass = attempt.violations_count > 0 ? 'table-warning' : '';

                return `
                    <tr class="${rowClass}" data-attempt-id="${attempt.id}">
                        <td>
                            <strong>${attempt.student.name}</strong>
                            <div class="text-muted small">${attempt.student.username}</div>
                        </td>
                        <td>${attempt.student.class}</td>
                        <td>${attempt.start_time}</td>
                        <td>
                            <span class="${attempt.minutes_remaining < 10 ? 'text-danger fw-bold' : ''}">
                                ${attempt.time_remaining}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${violationPercent >= 66 ? 'bg-danger' : violationPercent >= 33 ? 'bg-warning text-dark' : 'bg-success'}">
                                ${attempt.violations_count}/${attempt.max_violations}
                            </span>
                        </td>
                        <td><span class="badge ${statusClass}">${statusText}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="showViolations(${attempt.id})">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Update last update time
        function updateTime() {
            const now = new Date();
            document.getElementById('update-time').textContent = now.toLocaleTimeString('id-ID');
        }

        // Show violation details
        async function showViolations(attemptId) {
            try {
                const response = await fetch(`/guru/monitoring/attempt/${attemptId}/violations`);
                const data = await response.json();

                const modalBody = document.getElementById('violation-modal-body');

                if (data.violations_log.length === 0) {
                    modalBody.innerHTML = '<p class="text-muted">Belum ada pelanggaran</p>';
                } else {
                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <strong>Total Pelanggaran:</strong>
                            <span class="badge ${data.violations_count >= data.max_violations ? 'bg-danger' : 'bg-warning'}">
                                ${data.violations_count}/${data.max_violations}
                            </span>
                        </div>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Jenis</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.violations_log.map(log => `
                                                                                <tr>
                                                                                    <td>${new Date(log.time).toLocaleString('id-ID')}</td>
                                                                                    <td>${log.type}</td>
                                                                                </tr>
                                                                            `).join('')}
                            </tbody>
                        </table>
                    `;
                }

                const modal = new bootstrap.Modal(document.getElementById('violationModal'));
                modal.show();
            } catch (error) {
                console.error('Error loading violations:', error);
            }
        }

        // Refresh button
        function refreshData() {
            loadData();
        }

        // Auto refresh every 5 seconds
        function startAutoRefresh() {
            refreshInterval = setInterval(loadData, 5000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            startAutoRefresh();
        });

        // Stop refresh when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
                loadData();
            }
        });
    </script>
@endpush
