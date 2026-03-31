{{-- resources/views/student/exam/start.blade.php --}}
@extends('student.exam.layouts.exam')

@section('title', 'Mulai Tes - ' . $package->title)

@section('content')
    <div class="exam-start-wrapper">
        <div class="exam-start-card">
            <div class="exam-start-header">
                <i class="bi bi-file-earmark-text"></i>
                <h2>{{ $package->title }}</h2>
                @if ($package->description)
                    <p class="mb-0 opacity-75">{{ $package->description }}</p>
                @endif
            </div>

            <div class="exam-start-body">
                <div class="exam-info-grid">
                    <div class="exam-info-item">
                        {{-- <i class="bi bi-question-circle"></i> --}}
                        <h4>{{ $package->total_questions }}</h4>
                        <span>Soal</span>
                    </div>
                    <div class="exam-info-item">
                        {{-- <i class="bi bi-clock"></i> --}}
                        <h4>{{ $package->duration }}</h4>
                        <span>Menit</span>
                    </div>
                </div>

                <div class="alert alert-warning mb-3">
                    <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Perhatian Penting!</h6>
                    <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                        <li><strong style="color: #dc3545;">📱 Mode Fullscreen WAJIB:</strong> Browser akan masuk fullscreen dan harus tetap aktif selama ujian. Keluar fullscreen akan dicatat sebagai pelanggaran.</li>
                        <li><strong>⏱️ Timer Otomatis:</strong> Timer berjalan otomatis dan tidak bisa di-pause setelah dimulai</li>
                        <li><strong>💾 Penyimpanan:</strong> Jawaban tersimpan otomatis setiap 30 detik dan saat Anda pindah soal</li>
                        <li><strong>🔄 Refresh:</strong> Jika browser refresh, data tersimpan dan bisa dilanjutkan</li>
                        <li><strong>⏰ Auto Submit:</strong> Tes akan ter-submit otomatis jika waktu habis</li>
                        <li><strong>📶 Koneksi:</strong> Pastikan koneksi internet stabil</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('student.test.work', ['attempt' => 'new']) }}" id="startForm">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $package->id }}">

                    <div class="form-check mb-4 p-3" style="background: var(--bg-main); border-radius: var(--radius-md);">
                        <input class="form-check-input" type="checkbox" id="agree" required>
                        <label class="form-check-label" for="agree" style="font-size: 0.9rem;">
                            Saya setuju untuk mengerjakan tes dengan jujur dan mematuhi aturan yang berlaku
                        </label>
                    </div>

                    <button type="button" id="btn-start" class="btn btn-start btn-lg w-100 py-3" onclick="confirmStart()">
                        <i class="bi bi-play-circle me-2"></i>Saya Siap, Mulai Tes!
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="loading-animation mb-4">
                        <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                    </div>
                    <h4 class="mb-3">Menyiapkan Ujian...</h4>
                    <p class="text-muted mb-4">Mohon tunggu, sistem sedang memuat soal.</p>
                    
                    <div class="progress mb-3" style="height: 8px;">
                        <div id="loadingProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 0%"></div>
                    </div>
                    
                    <p id="loadingText" class="text-primary fw-bold">Estimasi: 15 detik</p>
                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Mohon tidak menutup atau me-refresh halaman ini
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Session storage key untuk tracking loading state
            const LOADING_STATE_KEY = 'exam_loading_state_{{ $package->id }}';
            const DELAY_MIN = 10000; // 10 detik
            const DELAY_MAX = 15000; // 15 detik
            let isStarting = false;
            let progressInterval = null;
            let keepAliveInterval = null;

            function enterFullscreen() {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    return elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    return elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    return elem.msRequestFullscreen();
                }
                return Promise.resolve();
            }

            function showLoadingModal(estimatedSeconds) {
                const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
                const progressBar = document.getElementById('loadingProgress');
                const loadingText = document.getElementById('loadingText');
                
                progressBar.style.width = '0%';
                loadingText.textContent = `Estimasi: ${estimatedSeconds} detik`;
                
                modal.show();

                // Animate progress bar
                let progress = 0;
                const increment = 100 / (estimatedSeconds * 10); // Update tiap 100ms
                
                progressInterval = setInterval(() => {
                    progress += increment;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(progressInterval);
                    }
                    progressBar.style.width = `${progress}%`;
                }, 100);
            }

            function hideLoadingModal() {
                const modalEl = document.getElementById('loadingModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                if (progressInterval) {
                    clearInterval(progressInterval);
                }
                if (keepAliveInterval) {
                    clearInterval(keepAliveInterval);
                }
            }

            function startKeepAlive() {
                // Kirim keep-alive tiap 5 menit untuk menjaga session
                keepAliveInterval = setInterval(() => {
                    fetch('/api/keep-alive', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).catch(e => console.log('Keep-alive failed:', e));
                }, 300000); // 5 menit
            }

            function generateRandomDelay() {
                return DELAY_MIN + Math.random() * (DELAY_MAX - DELAY_MIN);
            }

            function saveLoadingState(startTime, delay) {
                const state = {
                    startTime: startTime,
                    delay: delay,
                    packageId: {{ $package->id }},
                    isLoading: true
                };
                sessionStorage.setItem(LOADING_STATE_KEY, JSON.stringify(state));
            }

            function clearLoadingState() {
                sessionStorage.removeItem(LOADING_STATE_KEY);
            }

            function checkExistingLoadingState() {
                const saved = sessionStorage.getItem(LOADING_STATE_KEY);
                if (!saved) return null;
                
                try {
                    const state = JSON.parse(saved);
                    // Cek apakah state ini untuk package yang sama
                    if (state.packageId !== {{ $package->id }}) {
                        clearLoadingState();
                        return null;
                    }
                    return state;
                } catch (e) {
                    clearLoadingState();
                    return null;
                }
            }

            function resumeLoadingFromState(state) {
                const elapsed = Date.now() - state.startTime;
                const remaining = state.delay - elapsed;
                
                if (remaining <= 0) {
                    // Delay sudah selesai, lanjutkan ke create attempt
                    clearLoadingState();
                    createAttempt();
                } else {
                    // Masih ada sisa delay, lanjutkan
                    const remainingSeconds = Math.ceil(remaining / 1000);
                    showLoadingModal(remainingSeconds);
                    startKeepAlive();
                    
                    setTimeout(() => {
                        clearLoadingState();
                        createAttempt();
                    }, remaining);
                }
            }

            function createAttempt() {
                hideLoadingModal();
                
                // Fullscreen sudah dipanggil di confirmStart(), langsung create attempt
                fetch('{{ route('student.test.create-attempt') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            package_id: {{ $package->id }}
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.attempt_id) {
                            window.location.href = '/student/test/' + data.attempt_id + '/work';
                        } else {
                            alert('Gagal memulai tes. Silakan coba lagi.');
                            document.getElementById('btn-start').disabled = false;
                            isStarting = false;
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Gagal memulai tes. Silakan coba lagi.');
                        document.getElementById('btn-start').disabled = false;
                        isStarting = false;
                    });
            }

            function confirmStart() {
                // Cek apakah sudah ada loading state (misal karena refresh)
                const existingState = checkExistingLoadingState();
                if (existingState) {
                    if (confirm('Anda sebelumnya sedang memulai ujian. Lanjutkan?')) {
                        resumeLoadingFromState(existingState);
                    } else {
                        clearLoadingState();
                    }
                    return;
                }

                if (!document.getElementById('agree').checked) {
                    alert('Anda harus menyetujui pernyataan terlebih dahulu');
                    return;
                }

                if (isStarting) return; // Prevent double click
                
                if (confirm('Yakin ingin memulai tes?\n\n⚠️ PERHATIAN PENTING:\n━━━━━━━━━━━━━━━━━━━━━\n📱 Mode Fullscreen WAJIB aktif\n⏱️  Timer berjalan setelah loading\n🚫 Keluar fullscreen = pelanggaran\n\nPastikan Anda siap dan fokus!\n\nKlik OK untuk mulai...')) {
                    isStarting = true;
                    document.getElementById('btn-start').disabled = true;
                    
                    // LANGSUNG fullscreen setelah klik (harus dalam user gesture context)
                    enterFullscreen().then(() => {
                        console.log('Fullscreen activated');
                    }).catch(err => {
                        console.log('Fullscreen ditolak atau tidak didukung:', err);
                        // Tetap lanjut meski fullscreen gagal
                    });
                    
                    // Generate random delay 10-15 detik
                    const delay = generateRandomDelay();
                    const estimatedSeconds = Math.ceil(delay / 1000);
                    
                    // Simpan state untuk handle refresh
                    const startTime = Date.now();
                    saveLoadingState(startTime, delay);
                    
                    // Tampilkan loading modal
                    showLoadingModal(estimatedSeconds);
                    
                    // Start keep-alive untuk session
                    startKeepAlive();
                    
                    // Delay kemudian create attempt
                    setTimeout(() => {
                        clearLoadingState();
                        createAttempt();
                    }, delay);
                }
            }

            // Cek existing state saat halaman load
            document.addEventListener('DOMContentLoaded', () => {
                const existingState = checkExistingLoadingState();
                if (existingState) {
                    if (confirm('Anda sebelumnya sedang memulai ujian. Lanjutkan?')) {
                        resumeLoadingFromState(existingState);
                    } else {
                        clearLoadingState();
                    }
                }
            });

            // Prevent accidental refresh saat loading
            window.addEventListener('beforeunload', (e) => {
                const state = checkExistingLoadingState();
                if (state) {
                    e.preventDefault();
                    e.returnValue = 'Ujian sedang dimuat. Yakin ingin meninggalkan halaman?';
                    return e.returnValue;
                }
            });
        </script>
    @endpush
@endsection
