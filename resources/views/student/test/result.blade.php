{{-- resources/views/student/test/result.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Hasil Tes')

@section('content')
    <script>
        let leaderboardPolling = null;
        
        function loadLeaderboard() {
            fetch('{{ route("student.test.leaderboard", $package->id) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.leaderboard) {
                        updateLeaderboardTable(data.leaderboard, data.userRank);
                    }
                })
                .catch(error => console.error('Error loading leaderboard:', error));
        }
        
        function updateLeaderboardTable(leaderboard, userRank) {
            const tbody = document.getElementById('leaderboard-body');
            if (!tbody) return;
            
            tbody.innerHTML = leaderboard.map((entry, index) => {
                const isCurrentUser = entry.rank == userRank;
                const durationText = entry.duration !== null ? entry.duration + ' menit' : '-';
                let rankBadge = '';
                if (entry.rank == 1) rankBadge = '<span class="badge bg-warning text-dark fs-6">🥇</span>';
                else if (entry.rank == 2) rankBadge = '<span class="badge bg-secondary fs-6">🥈</span>';
                else if (entry.rank == 3) rankBadge = '<span class="badge bg-danger fs-6">🥉</span>';
                else rankBadge = '<span class="text-muted fw-bold">' + entry.rank + '</span>';
                
                return `
                    <tr class="${isCurrentUser ? 'table-warning' : ''}">
                        <td class="text-center">${rankBadge}</td>
                        <td class="fw-medium">
                            ${entry.name}
                            ${isCurrentUser ? '<span class="badge bg-primary ms-1">Kamu</span>' : ''}
                        </td>
                        <td class="text-center"><span class="fw-bold">${entry.score}</span></td>
                        <td class="text-center text-muted">${durationText}</td>
                    </tr>
                `;
            }).join('');
            
            // Update rank info
            const rankInfo = document.getElementById('rank-info');
            if (rankInfo && userRank) {
                rankInfo.textContent = `Peringkat ${userRank} dari ${leaderboard.length} peserta`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Start polling every 15 seconds
            leaderboardPolling = setInterval(loadLeaderboard, 15000);
        });
        
        // Stop polling when leaving page
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && leaderboardPolling) {
                clearInterval(leaderboardPolling);
                leaderboardPolling = null;
            } else if (!document.hidden && !leaderboardPolling) {
                leaderboardPolling = setInterval(loadLeaderboard, 15000);
                loadLeaderboard();
            }
        });
    </script>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle text-success icon-success"></i>
                    <h2 class="mt-3">Tes Selesai!</h2>
                    <p class="text-muted">{{ $package->title }}</p>
                </div>

                {{-- Score Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="text-muted mb-3">TOTAL SKOR</h6>
                        <h1 class="display-1 fw-bold text-primary mb-0">{{ number_format($attempt->total_score, 1) }}</h1>

                        @if ($ranking)
                            <p class="text-muted mt-3" id="rank-info">
                                <i class="bi bi-trophy"></i> Peringkat {{ $ranking }} dari {{ $totalAttempts }} peserta
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Leaderboard --}}
                @if ($leaderboard && $leaderboard->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-trophy text-warning"></i> Leaderboard - Top 10
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center leaderboard-th-rank">Peringkat</th>
                                            <th>Nama</th>
                                            <th class="text-center">Skor</th>
                                            <th class="text-center">Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leaderboard-body">
                                        @foreach ($leaderboard as $index => $entry)
                                            @php
                                                $isCurrentUser = $entry['rank'] == $ranking;
                                                $duration = $entry['duration'];
                                                $durationText = $duration !== null 
                                                    ? $duration . ' menit' 
                                                    : '-';
                                            @endphp
                                            <tr class="{{ $isCurrentUser ? 'table-warning' : '' }}">
                                                <td class="text-center">
                                                    @if ($entry['rank'] == 1)
                                                        <span class="badge bg-warning text-dark fs-6">
                                                            🥇
                                                        </span>
                                                    @elseif ($entry['rank'] == 2)
                                                        <span class="badge bg-secondary fs-6">
                                                            🥈
                                                        </span>
                                                    @elseif ($entry['rank'] == 3)
                                                        <span class="badge bg-danger fs-6">
                                                            🥉
                                                        </span>
                                                    @else
                                                        <span class="text-muted fw-bold">{{ $entry['rank'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="fw-medium">
                                                    {{ $entry['name'] }}
                                                    @if ($isCurrentUser)
                                                        <span class="badge bg-primary ms-1">Kamu</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold">{{ number_format($entry['score'], 1) }}</span>
                                                </td>
                                                <td class="text-center text-muted">
                                                    {{ $durationText }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

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

                {{-- Flagged / Reset Token --}}
                @if ($attempt->is_flagged)
                    <div class="card border-0 shadow-sm mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle"></i> Anda Dikeluarkan dari Ujian
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                Anda telah dikeluarikan dari ujian karena terlalu banyak pelanggaran 
                                ({{ $attempt->violations_count }} pelanggaran).
                            </p>
                            
                            @if ($attempt->reset_token && $attempt->reset_token_expires_at && now()->lessThan($attempt->reset_token_expires_at))
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">Token Reset Tersedia!</h6>
                                    <p class="mb-2">Hubungi guru untuk mendapatkan token reset. Token akan expired pada 
                                        {{ $attempt->reset_token_expires_at->format('d M Y H:i') }}</p>
                                    
                                    <form action="{{ route('student.test.reset', $attempt) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="reset_token" class="form-control" 
                                            placeholder="Masukkan token reset" required>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-arrow-repeat"></i> Gunakan Token
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-info-circle"></i>
                                    Silakan hubungi guru untuk meminta token reset agar dapat mengikuti ujian ulang.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

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
                                    <div class="progress progress-sm">
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
