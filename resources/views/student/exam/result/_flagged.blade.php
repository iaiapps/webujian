{{-- resources/views/student/exam/result/_flagged.blade.php --}}
{{-- Layout for students who were flagged due to violations --}}

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="result-header">
                <i class="bi bi-shield-exclamation result-icon flagged"></i>
                <h3 class="text-danger">Anda Dikeluarkan dari Ujian</h3>
                {{-- <p class="text-muted fs-3">{{ $package->title }}</p> --}}
            </div>

            {{-- Violation Summary --}}
            <div class="violation-card mb-4">
                <div class="violation-header">
                    <h5 class="mb-0">
                        {{-- <i class="bi bi-exclamation-triangle"></i> --}}
                        Detail Pelanggaran ({{ $attempt->violations_count }}x)
                    </h5>
                </div>
                <div class="violation-list">
                    @php
                        $violationsLog = $attempt->violations_log ? json_decode($attempt->violations_log, true) : [];
                        $typeLabels = [
                            'tab_switch' => 'Pindah Tab',
                            'window_blur' => 'Klik Luar Window',
                            'right_click' => 'Klik Kanan',
                            'copy' => 'Copy',
                            'cut' => 'Cut',
                            'paste' => 'Paste',
                            'devtools' => 'Membuka DevTools',
                            'exit_fullscreen' => 'Keluar Fullscreen',
                        ];
                    @endphp

                    @forelse($violationsLog as $log)
                        <div class="violation-item">
                            <span>
                                <i class="bi bi-x-circle text-danger"></i>
                                {{ $typeLabels[$log['type']] ?? $log['type'] }}
                            </span>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($log['time'])->format('H:i:s') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            Tidak ada detail pelanggaran
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Reset Token Section --}}
            @if ($attempt->reset_token && $attempt->reset_token_expires_at && now()->lessThan($attempt->reset_token_expires_at))
                <div class="token-box">
                    <h5 class="text-warning mb-3">
                        <i class="bi bi-key"></i> Token Reset Tersedia!
                    </h5>
                    <p class="mb-3">
                        Hubungi guru untuk mendapatkan token reset.
                        Token akan expired pada
                        <strong>{{ $attempt->reset_token_expires_at->format('d M Y H:i') }}</strong>
                    </p>

                    <form action="{{ route('student.test.reset', $attempt) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Masukkan Token Reset (6 karakter):</label>
                            <div class="input-group">
                                <input type="text" name="reset_token" class="form-control text-uppercase"
                                    placeholder="Contoh: A1B2C3" maxlength="6"
                                    style="text-transform: uppercase; letter-spacing: 4px; font-weight: bold; font-size: 1.2rem;"
                                    required>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-arrow-repeat"></i> Reset Ujian
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>Token Belum Tersedia</strong><br>
                    Silakan hubungi guru Anda untuk meminta token reset agar dapat mengikuti ujian ulang.
                </div>
            @endif

            {{-- Actions --}}
            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Kembali ke Dashboard
                </a>
            </div>

            {{-- Minimal Stats --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Progress Ujian</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-success">{{ $attempt->correct_answers }}</h4>
                            <small class="text-muted">Benar</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger">{{ $attempt->wrong_answers }}</h4>
                            <small class="text-muted">Salah</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-secondary">{{ $attempt->unanswered }}</h4>
                            <small class="text-muted">Belum Dijawab</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
