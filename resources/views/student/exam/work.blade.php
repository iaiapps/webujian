{{-- resources/views/student/exam/work.blade.php --}}
@extends('student.exam.layouts.exam')

@section('title', 'Mengerjakan - ' . $package->title)

@section('violations')
    <i class="bi bi-exclamation-triangle text-warning"></i>
    <span class="badge bg-warning" id="violation-badge">{{ $violationCount ?? 0 }}/{{ $package->max_violations ?? 3 }}</span>
@endsection

@section('header-actions')
    <button type="button" id="btnFinishHeader" class="btn btn-warning text-white" onclick="confirmSubmit()">
        <i class="bi bi-send"></i> Akhiri Tes
    </button>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="question-area">
                    <div class="question-card">
                        <!-- Question Loading State -->
                        <div id="questionSkeleton" class="question-skeleton">
                            <div class="skeleton-header mb-4">
                                <div class="skeleton-text" style="width: 40%; height: 24px;"></div>
                                <div class="d-flex gap-2 mt-2">
                                    <div class="skeleton-badge"></div>
                                    <div class="skeleton-badge"></div>
                                </div>
                            </div>
                            <div class="skeleton-body mb-4">
                                <div class="skeleton-text mb-2" style="width: 100%;"></div>
                                <div class="skeleton-text mb-2" style="width: 90%;"></div>
                                <div class="skeleton-text mb-2" style="width: 95%;"></div>
                                <div class="skeleton-text" style="width: 60%;"></div>
                            </div>
                            <div class="skeleton-options">
                                <div class="skeleton-option mb-3"></div>
                                <div class="skeleton-option mb-3"></div>
                                <div class="skeleton-option mb-3"></div>
                                <div class="skeleton-option mb-3"></div>
                            </div>
                        </div>
                        
                        <!-- Question Container -->
                        <div id="questionContainer" class="question-container" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 id="questionNumber">Soal <span id="currentQNum">1</span> dari <span id="totalQNum">{{ $package->total_questions }}</span></h5>
                                <div class="d-flex gap-2">
                                    <span id="questionCategory" class="badge bg-info"></span>
                                    <span id="questionType" class="badge"></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p id="questionText" class="fs-5"></p>
                                <div id="questionImageContainer"></div>
                            </div>

                            <!-- Options Container -->
                            <div id="optionsContainer" class="options-container"></div>

                            <!-- Ragu-ragu -->
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="doubtCheckbox" onchange="toggleCurrentDoubt()">
                                <label class="form-check-label" for="doubtCheckbox">
                                    <i class="bi bi-question-circle me-2"></i>Tandai soal ini (ragu-ragu)
                                </label>
                            </div>

                            <!-- Navigation -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" id="btnPrev" class="btn btn-outline-primary" onclick="goToPrevQuestion()">
                                    <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                                </button>
                                <button type="button" id="btnNext" class="btn btn-primary" onclick="goToNextQuestion()">
                                    Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Error State -->
                        <div id="questionError" class="question-error text-center py-5" style="display: none;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Gagal memuat soal</h5>
                            <p class="text-muted">Silakan coba lagi</p>
                            <button type="button" class="btn btn-primary" onclick="retryLoadQuestion()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Sidebar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top-100 rounded-4">
                    <div class="card-header bg-white py-3 rounded-top-4">
                        <h6 class="mb-0">Navigasi Nomor</h6>
                    </div>
                    <div class="card-body">
                        <div class="nav-numbers">
                            @foreach ($questions as $index => $question)
                                <button type="button" class="btn btn-sm btn-outline-primary nav-btn"
                                    data-index="{{ $index }}" data-question-id="{{ $question->id }}"
                                    onclick="goToQuestion({{ $index }})">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <hr>

                        <div class="small">
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-box legend-box-answered me-2"></div>
                                <span>Sudah dijawab</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-box legend-box-unanswered me-2"></div>
                                <span>Belum dijawab</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="legend-box legend-box-doubt me-2"></div>
                                <span>Ragu-ragu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Loading Modal -->
    <div id="submitLoadingModal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="loading-animation mb-4">
                        <div class="spinner-border text-success" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                    </div>
                    <h4 class="mb-3">Mengirim Jawaban...</h4>
                    <p class="text-muted mb-4">Mohon tunggu, sistem sedang memproses hasil ujian Anda.</p>
                    
                    <div class="progress mb-3" style="height: 8px;">
                        <div id="submitProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             style="width: 0%"></div>
                    </div>
                    
                    <p id="submitLoadingText" class="text-success fw-bold">Estimasi: 7 detik</p>
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
            const attemptId = {{ $attempt->id }};
            const endTime = new Date("{{ $attempt->end_time ? $attempt->end_time->toIso8601String() : now()->addMinutes($package->duration)->toIso8601String() }}");
            let currentIndex = 0;
            const totalQuestions = {{ $questions->count() }};
            let timerInterval;
            let violationCount = {{ $violationCount ?? 0 }};
            const maxViolations = {{ $package->max_violations ?? 3 }};
            let isFlagged = {{ $attempt->is_flagged ? 'true' : 'false' }};
            let lastViolationTime = 0;
            const violationCooldown = 3000; // 3 seconds cooldown
            
            // Audio initialization variables
            let hasUserInteracted = false;
            let audioContext = null;

            // Initialize badge on page load
            if (violationCount > 0) {
                updateViolationDisplay();
            }
            
            // Initialize audio on user interaction
            function initAudio() {
                if (!hasUserInteracted) {
                    hasUserInteracted = true;
                    try {
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    } catch (e) {
                        console.log('AudioContext not supported');
                    }
                }
            }
            
            document.addEventListener('click', initAudio, { once: true });
            document.addEventListener('keydown', initAudio, { once: true });
            document.addEventListener('touchstart', initAudio, { once: true });
            
            // Play warning sound for violations
            function playWarningSound() {
                if (!hasUserInteracted) return;
                
                try {
                    if (audioContext && audioContext.state === 'running') {
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        
                        oscillator.frequency.value = 800;
                        oscillator.type = 'sine';
                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                    } else {
                        const audio = new Audio();
                        audio.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE';
                        audio.volume = 0.5;
                        audio.play().catch(e => console.log('Sound play failed:', e));
                    }
                } catch (e) {
                    console.log('Audio not supported');
                }
            }
            
            // Update violation badge display
            function updateViolationDisplay() {
                const badge = document.getElementById('violation-badge');
                if (badge) {
                    badge.textContent = `${violationCount}/${maxViolations}`;
                    
                    if (violationCount >= maxViolations) {
                        badge.classList.remove('bg-warning');
                        badge.classList.add('bg-danger');
                    }
                    
                    badge.style.animation = 'none';
                    badge.offsetHeight;
                    badge.style.animation = 'pulse 0.5s ease-in-out';
                    
                    playWarningSound();
                }
            }

            // Report violation to server with rate limiting
            async function reportViolation(type) {
                if (isFlagged) return;
                
                // Rate limiting: check cooldown
                const now = Date.now();
                if (now - lastViolationTime < violationCooldown) {
                    console.log('Violation cooldown active, skipping:', type);
                    return;
                }
                lastViolationTime = now;

                try {
                    const response = await fetch(`/student/result/${attemptId}/violation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: type
                        })
                    });

                    const data = await response.json();

                    if (data.flagged) {
                        isFlagged = true;
                        showViolationModal(data.message, true);
                    } else {
                        violationCount = data.violations_count;
                        showViolationModal(data.message, false);
                        updateViolationDisplay();
                    }
                } catch (error) {
                    console.error('Error reporting violation:', error);
                }
            }

            // Fullscreen warning modal with OK button
            function showFullscreenWarningModal() {
                // Remove any existing fullscreen modal
                const existingModal = document.getElementById('fullscreen-warning-modal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Create modal HTML
                const modal = document.createElement('div');
                modal.id = 'fullscreen-warning-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                `;
                
                const content = document.createElement('div');
                content.style.cssText = `
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    max-width: 450px;
                    text-align: center;
                    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
                `;
                
                content.innerHTML = `
                    <div style="font-size: 56px; margin-bottom: 20px;">⚠️</div>
                    <h4 style="color: #f59e0b; margin-bottom: 15px; font-size: 1.3rem;">
                        Keluar dari Mode Fullscreen
                    </h4>
                    <p style="margin-bottom: 25px; color: #333; line-height: 1.5;">
                        Anda telah keluar dari mode fullscreen. <br>
                        <strong style="color: #dc3545;">Anda harus kembali ke fullscreen</strong> untuk melanjutkan ujian.
                    </p>
                    <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                        Jika tidak masuk fullscreen dalam 5 detik, ini akan dicatat sebagai pelanggaran.
                    </p>
                    <button id="fullscreenOkBtn" style="
                        background: #1e3a5f;
                        color: white;
                        border: none;
                        padding: 12px 40px;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: bold;
                        cursor: pointer;
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#2d5a8a'" onmouseout="this.style.background='#1e3a5f'">
                        <i class="bi bi-fullscreen" style="margin-right: 8px;"></i>OK, Masuk Fullscreen
                    </button>
                `;
                
                modal.appendChild(content);
                document.body.appendChild(modal);
                
                // Add click handler for OK button
                const okBtn = document.getElementById('fullscreenOkBtn');
                okBtn.addEventListener('click', () => {
                    enterFullscreen();
                    modal.remove();
                    fullscreenWarningShown = false;
                });
                
                // Auto report violation after 5 seconds if still not in fullscreen
                const violationTimeout = setTimeout(() => {
                    const isFullscreen = document.fullscreenElement || 
                                        document.webkitFullscreenElement || 
                                        document.mozFullScreenElement ||
                                        document.msFullscreenElement;
                    
                    if (!isFullscreen) {
                        reportViolation('exit_fullscreen');
                        modal.remove();
                        fullscreenWarningShown = false;
                    }
                }, 5000);
                
                // Clear timeout if modal is removed manually
                modal.addEventListener('remove', () => {
                    clearTimeout(violationTimeout);
                });
            }

            // Custom modal for violations (cannot be blocked like alert)
            function showViolationModal(message, isFlagged) {
                // Remove any existing modal
                const existingModal = document.getElementById('violation-modal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Create modal HTML
                const modal = document.createElement('div');
                modal.id = 'violation-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                `;
                
                const content = document.createElement('div');
                content.style.cssText = `
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                `;
                
                content.innerHTML = `
                    <div style="font-size: 48px; margin-bottom: 15px;">${isFlagged ? '⛔' : '⚠️'}</div>
                    <h4 style="color: ${isFlagged ? '#dc3545' : '#ffc107'}; margin-bottom: 15px;">
                        ${isFlagged ? 'Ujian Dihentikan' : 'Pelanggaran Terdeteksi'}
                    </h4>
                    <p style="margin-bottom: 20px; color: #333;">${message}</p>
                    ${isFlagged ? '<p style="color: #666; font-size: 14px;">Anda akan diarahkan ke halaman hasil...</p>' : ''}
                `;
                
                modal.appendChild(content);
                document.body.appendChild(modal);
                
                // Auto redirect if flagged
                if (isFlagged) {
                    setTimeout(() => {
                        window.location.href = '/student/result/' + attemptId;
                    }, 3000);
                } else {
                    // Auto close after 5 seconds for non-flagged violations
                    setTimeout(() => {
                        modal.remove();
                    }, 5000);
                }
            }

            // Anti-cheating: Tab switch / visibility change
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    reportViolation('tab_switch');
                }
            });

            // Anti-cheating: Window blur (click outside) with delay
            let blurStartTime = null;
            const blurDelay = 500; // 500ms delay to filter out accidental blurs
            
            window.addEventListener('blur', () => {
                blurStartTime = Date.now();
            });
            
            window.addEventListener('focus', () => {
                if (blurStartTime) {
                    const blurDuration = Date.now() - blurStartTime;
                    if (blurDuration > blurDelay) {
                        reportViolation('window_blur');
                    }
                    blurStartTime = null;
                }
            });

            // Anti-cheating: Disable right-click
            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                reportViolation('right_click');
            });

            // Anti-cheating: Disable copy
            document.addEventListener('copy', (e) => {
                e.preventDefault();
                reportViolation('copy');
            });

            // Anti-cheating: Disable cut
            document.addEventListener('cut', (e) => {
                e.preventDefault();
                reportViolation('cut');
            });

            // Anti-cheating: Disable paste
            document.addEventListener('paste', (e) => {
                e.preventDefault();
                reportViolation('paste');
            });

            // Anti-cheating: Detect DevTools (F12 and resize)
            let lastWindowWidth = window.innerWidth;
            let lastWindowHeight = window.innerHeight;
            
            document.addEventListener('keydown', (e) => {
                if (e.key === 'F12' ||
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i')) ||
                    (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j')) ||
                    (e.ctrlKey && (e.key === 'U' || e.key === 'u'))) {
                    e.preventDefault();
                    reportViolation('devtools');
                }
            });
            
            // Detect DevTools via window resize
            window.addEventListener('resize', () => {
                const widthDiff = Math.abs(window.innerWidth - lastWindowWidth);
                const heightDiff = Math.abs(window.innerHeight - lastWindowHeight);
                
                // If window size changed significantly (likely due to DevTools opening)
                if (widthDiff > 100 || heightDiff > 100) {
                    // Check if user is still in fullscreen
                    const isFullscreen = document.fullscreenElement || 
                                        document.webkitFullscreenElement || 
                                        document.mozFullScreenElement ||
                                        document.msFullscreenElement;
                    
                    if (!isFullscreen) {
                        reportViolation('devtools_resize');
                    }
                }
                
                lastWindowWidth = window.innerWidth;
                lastWindowHeight = window.innerHeight;
            });

            // Anti-cheating: Fullscreen mode enforcement
            let fullscreenWarningShown = false;
            
            function enterFullscreen() {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
            }
            
            function checkFullscreen() {
                const isFullscreen = document.fullscreenElement || 
                                     document.webkitFullscreenElement || 
                                     document.mozFullScreenElement ||
                                     document.msFullscreenElement;
                
                if (!isFullscreen && !fullscreenWarningShown) {
                    fullscreenWarningShown = true;
                    // Show custom fullscreen warning modal with OK button
                    showFullscreenWarningModal();
                } else if (isFullscreen && fullscreenWarningShown) {
                    // User kembali ke fullscreen, remove warning modal if exists
                    const warningModal = document.getElementById('fullscreen-warning-modal');
                    if (warningModal) {
                        warningModal.remove();
                    }
                    fullscreenWarningShown = false;
                }
            }
            
            // Monitor fullscreen changes
            document.addEventListener('fullscreenchange', checkFullscreen);
            document.addEventListener('webkitfullscreenchange', checkFullscreen);
            document.addEventListener('mozfullscreenchange', checkFullscreen);
            document.addEventListener('MSFullscreenChange', checkFullscreen);

            // Timer
            function startTimer() {
                timerInterval = setInterval(() => {
                    const now = new Date();
                    const diff = endTime - now;

                    if (diff <= 0) {
                        clearInterval(timerInterval);
                        autoSubmit();
                        return;
                    }

                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    document.getElementById('timer').textContent =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    // Warning 10 minutes
                    if (diff <= 600000 && diff > 599000) {
                        showViolationModal('⚠️ Waktu tersisa 10 menit!', false);
                    }
                }, 1000);
            }

            // Navigation
            function showQuestion(index) {
                document.querySelectorAll('.question-item').forEach(el => el.classList.add('d-none'));
                document.querySelector(`.question-item[data-index="${index}"]`).classList.remove('d-none');
                currentIndex = index;
            }

            function nextQuestion() {
                if (currentIndex < totalQuestions - 1) {
                    showQuestion(currentIndex + 1);
                }
            }

            function prevQuestion() {
                if (currentIndex > 0) {
                    showQuestion(currentIndex - 1);
                }
            }

            function goToQuestion(index) {
                if (questionLoader) {
                    navigateToQuestion(index + 1);
                }
            }

            // Save Answer
            function selectOption(questionId, answer, element) {
                saveAnswer(questionId, answer);
                updateNavButton(questionId, 'answered');
            }

            function selectComplexOption(questionId) {
                const checked = Array.from(document.querySelectorAll(`input[name="answer_${questionId}[]"]:checked`))
                    .map(el => el.value);

                const answer = checked.join(',');
                saveAnswer(questionId, answer);
                updateNavButton(questionId, 'answered');
            }

            function selectCategoryOption(questionId) {
                // Collect answers for each statement (A, B, C, D, E)
                const answers = [];
                ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
                    const checkedRadio = document.querySelector(`input[name="answer_${questionId}_${opt}"]:checked`);
                    if (checkedRadio) {
                        answers.push(`${opt}:${checkedRadio.value}`);
                    }
                });

                // Format: "A:B,B:S,C:B,D:B,E:S"
                const answer = answers.join(',');

                // Only save if all 5 statements are answered
                if (answers.length === 5) {
                    saveAnswer(questionId, answer);
                    updateNavButton(questionId, 'answered');
                }
            }

            function toggleDoubt(questionId) {
                const isDoubt = document.getElementById(`doubt_${questionId}`).checked;
                saveAnswer(questionId, null, isDoubt);

                if (isDoubt) {
                    updateNavButton(questionId, 'doubt');
                }
            }

            function saveAnswer(questionId, answer = null, isDoubt = false) {
                // Get current answer if not provided
                if (answer === null) {
                    const questionEl = document.querySelector(`.question-item[data-question-id="${questionId}"]`);

                    // Detect question type
                    // Category type has inputs like: answer_{questionId}_A, answer_{questionId}_B, etc.
                    const categoryInputs = questionEl.querySelectorAll(`input[name^="answer_${questionId}_"]`);
                    const singleInput = questionEl.querySelector(`input[name="answer_${questionId}"]`);
                    const complexInputs = questionEl.querySelectorAll(`input[name="answer_${questionId}[]"]`);

                    if (categoryInputs.length > 0) {
                        // Category type: collect A:B,B:S,C:B,D:B,E:S format
                        const answers = [];
                        ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
                            const checkedRadio = questionEl.querySelector(
                                `input[name="answer_${questionId}_${opt}"]:checked`);
                            if (checkedRadio) {
                                answers.push(`${opt}:${checkedRadio.value}`);
                            }
                        });
                        answer = answers.join(',');
                    } else if (singleInput) {
                        // Single choice type
                        const checked = questionEl.querySelector(`input[name="answer_${questionId}"]:checked`);
                        answer = checked ? checked.value : null;
                    } else if (complexInputs.length > 0) {
                        // Complex type (checkbox)
                        const checked = Array.from(questionEl.querySelectorAll(`input[name="answer_${questionId}[]"]:checked`))
                            .map(el => el.value);
                        answer = checked.join(',');
                    }
                }

                fetch(`/student/test/${attemptId}/save-answer`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            question_id: questionId,
                            answer: answer,
                            is_doubt: isDoubt
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.expired) {
                            alert('Waktu habis! Tes akan di-submit otomatis.');
                            window.location.href = `/student/result/${attemptId}`;
                        }
                    })
                    .catch(err => console.error('Save error:', err));
            }

            function updateNavButton(questionId, status) {
                const btn = document.querySelector(`.nav-btn[data-question-id="${questionId}"]`);
                if (!btn) return;

                btn.classList.remove('btn-outline-primary', 'btn-primary', 'btn-warning');

                if (status === 'answered') {
                    btn.classList.add('btn-primary');
                } else if (status === 'doubt') {
                    btn.classList.add('btn-warning');
                } else {
                    btn.classList.add('btn-outline-primary');
                }
            }

            function confirmSubmit() {
                console.log('confirmSubmit called');
                
                const answered = document.querySelectorAll('.nav-btn.btn-primary').length;
                const unanswered = totalQuestions - answered;

                let msg = `Anda akan mengakhiri tes.\n\n`;
                msg += `Dijawab: ${answered} soal\n`;
                msg += `Belum dijawab: ${unanswered} soal\n\n`;
                msg += `Yakin ingin submit?`;

                if (confirm(msg)) {
                    // Disable all submit buttons to prevent double click
                    const submitBtnHeader = document.getElementById('btnFinishHeader');
                    const submitBtnNav = document.getElementById('btnNext');
                    
                    if (submitBtnHeader) {
                        submitBtnHeader.disabled = true;
                        submitBtnHeader.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                    }
                    
                    if (submitBtnNav) {
                        submitBtnNav.disabled = true;
                        submitBtnNav.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                    }
                    
                    // Show loading modal with random delay 5-15 seconds (maksimal 15 detik)
                    const delay = 5000 + Math.random() * 10000; // 5-15 detik
                    const estimatedSeconds = Math.ceil(delay / 1000);
                    
                    console.log('Submit delay:', delay, 'ms, estimated:', estimatedSeconds, 'seconds');
                    
                    showSubmitLoading(estimatedSeconds);
                    
                    // Delay kemudian submit
                    setTimeout(() => {
                        console.log('Executing submitTest after delay');
                        submitTest();
                    }, delay);
                }
            }

            function showSubmitLoading(estimatedSeconds) {
                const modal = new bootstrap.Modal(document.getElementById('submitLoadingModal'));
                const progressBar = document.getElementById('submitProgress');
                const loadingText = document.getElementById('submitLoadingText');
                
                progressBar.style.width = '0%';
                loadingText.textContent = `Estimasi: ${estimatedSeconds} detik`;
                
                modal.show();

                // Animate progress bar
                let progress = 0;
                const increment = 100 / (estimatedSeconds * 10); // Update tiap 100ms
                
                const progressInterval = setInterval(() => {
                    progress += increment;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(progressInterval);
                    }
                    progressBar.style.width = `${progress}%`;
                }, 100);
            }

            function submitTest() {
                fetch(`/student/test/${attemptId}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect || `/student/result/${attemptId}`;
                        } else {
                            alert(data.error || 'Gagal submit');
                            hideSubmitLoading();
                        }
                    })
                    .catch(err => {
                        console.error('Submit error:', err);
                        alert('Gagal submit. Silakan coba lagi.');
                        hideSubmitLoading();
                    });
            }

            function hideSubmitLoading() {
                console.log('hideSubmitLoading called');
                
                const modalEl = document.getElementById('submitLoadingModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                
                // Re-enable submit buttons
                const submitBtnHeader = document.getElementById('btnFinishHeader');
                const submitBtnNav = document.getElementById('btnNext');
                
                if (submitBtnHeader) {
                    submitBtnHeader.disabled = false;
                    submitBtnHeader.innerHTML = '<i class="bi bi-send"></i> Akhiri Tes';
                }
                
                if (submitBtnNav) {
                    submitBtnNav.disabled = false;
                    const currentQ = questionLoader ? questionLoader.getCurrentNumber() : 1;
                    btnNext.innerHTML = currentQ >= totalQuestions ? 
                        'Selesai<i class="bi bi-check-lg ms-2"></i>' : 
                        'Selanjutnya<i class="bi bi-arrow-right ms-2"></i>';
                }
            }

            function autoSubmit() {
                alert('Waktu habis! Tes Anda akan di-submit otomatis.');
                submitTest();
            }

            // Prevent back button
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
            };

            // ==================== LAZY LOADING FUNCTIONS ====================
            
            // Initialize QuestionLoader
            let questionLoader = null;
            let currentQuestionData = null;
            
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize QuestionLoader
                if (typeof QuestionLoader !== 'undefined') {
                    QuestionLoader.init({
                        attemptId: attemptId,
                        totalQuestions: totalQuestions,
                        currentQuestion: 1
                    });
                    
                    questionLoader = QuestionLoader;
                    
                    // Load first question
                    loadQuestion(1);
                }
            });
            
            // Load question by number
            async function loadQuestion(questionNumber) {
                if (!questionLoader) return;
                
                // Show skeleton
                showQuestionSkeleton();
                
                try {
                    const question = await questionLoader.loadQuestion(questionNumber);
                    renderQuestion(question);
                    currentQuestionData = question;
                    
                    // Update navigation buttons
                    updateNavButtons(questionNumber);
                    
                    // Update ExamState
                    if (typeof ExamState !== 'undefined') {
                        ExamState.setCurrentQuestion(questionNumber);
                    }
                    
                } catch (error) {
                    console.error('Failed to load question:', error);
                    showQuestionError();
                }
            }
            
            // Render question data to UI
            function renderQuestion(question) {
                // Hide skeleton, show container
                document.getElementById('questionSkeleton').style.display = 'none';
                document.getElementById('questionError').style.display = 'none';
                document.getElementById('questionContainer').style.display = 'block';
                
                // Update question info
                document.getElementById('currentQNum').textContent = question.number;
                document.getElementById('totalQNum').textContent = question.total_questions;
                document.getElementById('questionCategory').textContent = question.category || 'Umum';
                document.getElementById('questionText').textContent = question.text;
                
                // Update question type badge
                const typeBadge = document.getElementById('questionType');
                if (question.type === 'single') {
                    typeBadge.className = 'badge bg-primary';
                    typeBadge.textContent = 'Pilihan Ganda';
                } else if (question.type === 'category') {
                    typeBadge.className = 'badge bg-success';
                    typeBadge.textContent = 'Kategori';
                } else {
                    typeBadge.className = 'badge bg-success';
                    typeBadge.textContent = 'PG Kompleks';
                }
                
                // Render image if exists
                const imageContainer = document.getElementById('questionImageContainer');
                if (question.image) {
                    imageContainer.innerHTML = `
                        <img data-src="${question.image}" alt="Question Image" 
                             class="img-fluid rounded mb-3 lazy-image" 
                             style="background-color: #f0f0f0; min-height: 100px;">
                    `;
                    
                    // Initialize lazy loading for this image
                    if (typeof LazyImage !== 'undefined') {
                        setTimeout(() => LazyImage.refresh(), 100);
                    }
                } else {
                    imageContainer.innerHTML = '';
                }
                
                // Render options
                renderOptions(question);
                
                // Restore doubt status
                const doubtCheckbox = document.getElementById('doubtCheckbox');
                if (doubtCheckbox) {
                    doubtCheckbox.checked = question.is_doubt || false;
                }
            }
            
            // Render options based on question type
            function renderOptions(question) {
                const container = document.getElementById('optionsContainer');
                container.innerHTML = '';
                
                if (question.type === 'single') {
                    // Single choice
                    question.options.forEach(option => {
                        const isChecked = question.existing_answer === option.label;
                        const optionEl = document.createElement('div');
                        optionEl.className = 'form-check mb-3 p-3 border rounded option-item';
                        optionEl.onclick = () => selectOption(question.id, option.label);
                        optionEl.innerHTML = `
                            <input class="form-check-input" type="radio" 
                                   name="answer_${question.id}" value="${option.label}" 
                                   id="q${question.id}_${option.label}" ${isChecked ? 'checked' : ''}
                                   onchange="saveCurrentAnswer()">
                            <label class="form-check-label w-100" for="q${question.id}_${option.label}">
                                <strong>${option.label}.</strong> ${option.content}
                            </label>
                        `;
                        container.appendChild(optionEl);
                    });
                } else if (question.type === 'category') {
                    // Category type
                    const info = document.createElement('div');
                    info.className = 'alert alert-info mb-3';
                    info.innerHTML = '<i class="bi bi-info-circle"></i> Tentukan apakah setiap pernyataan di bawah ini <strong>BENAR</strong> atau <strong>SALAH</strong>.';
                    container.appendChild(info);
                    
                    // Parse existing answers
                    const existingPairs = {};
                    if (question.existing_answer) {
                        question.existing_answer.split(',').forEach(pair => {
                            const [opt, val] = pair.split(':');
                            if (opt && val) existingPairs[opt.trim()] = val.trim();
                        });
                    }
                    
                    question.options.forEach(option => {
                        const isTrue = existingPairs[option.label] === 'B';
                        const isFalse = existingPairs[option.label] === 'S';
                        
                        const optionEl = document.createElement('div');
                        optionEl.className = 'card mb-3 border';
                        optionEl.innerHTML = `
                            <div class="card-body">
                                <p class="mb-3"><strong>${option.label}.</strong> ${option.content}</p>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="answer_${question.id}_${option.label}" value="B"
                                               id="q${question.id}_${option.label}_true" ${isTrue ? 'checked' : ''}
                                               onchange="saveCurrentAnswer()">
                                        <label class="form-check-label text-success fw-bold" for="q${question.id}_${option.label}_true">
                                            <i class="bi bi-check-circle"></i> Benar
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="answer_${question.id}_${option.label}" value="S"
                                               id="q${question.id}_${option.label}_false" ${isFalse ? 'checked' : ''}
                                               onchange="saveCurrentAnswer()">
                                        <label class="form-check-label text-danger fw-bold" for="q${question.id}_${option.label}_false">
                                            <i class="bi bi-x-circle"></i> Salah
                                        </label>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.appendChild(optionEl);
                    });
                } else {
                    // Complex type
                    const info = document.createElement('div');
                    info.className = 'alert alert-info mb-3';
                    info.innerHTML = '<i class="bi bi-info-circle"></i> Pilih semua jawaban yang benar (bisa lebih dari satu)';
                    container.appendChild(info);
                    
                    const existingAnswers = question.existing_answer ? question.existing_answer.split(',') : [];
                    
                    question.options.forEach(option => {
                        const isChecked = existingAnswers.includes(option.label);
                        const optionEl = document.createElement('div');
                        optionEl.className = 'form-check mb-3 p-3 border rounded option-item';
                        optionEl.innerHTML = `
                            <input class="form-check-input" type="checkbox" 
                                   name="answer_${question.id}" value="${option.label}" 
                                   id="q${question.id}_${option.label}" ${isChecked ? 'checked' : ''}
                                   onchange="saveCurrentAnswer()">
                            <label class="form-check-label w-100" for="q${question.id}_${option.label}">
                                <strong>${option.label}.</strong> ${option.content}
                            </label>
                        `;
                        container.appendChild(optionEl);
                    });
                }
            }
            
            // Show skeleton loading
            function showQuestionSkeleton() {
                document.getElementById('questionSkeleton').style.display = 'block';
                document.getElementById('questionContainer').style.display = 'none';
                document.getElementById('questionError').style.display = 'none';
            }
            
            // Show error state
            function showQuestionError() {
                document.getElementById('questionSkeleton').style.display = 'none';
                document.getElementById('questionContainer').style.display = 'none';
                document.getElementById('questionError').style.display = 'block';
            }
            
            // Retry loading
            function retryLoadQuestion() {
                if (questionLoader) {
                    loadQuestion(questionLoader.getCurrentNumber());
                }
            }
            
            // Update navigation buttons state
            function updateNavButtons(questionNumber) {
                console.log('updateNavButtons called:', questionNumber, 'of', totalQuestions);
                
                const btnPrev = document.getElementById('btnPrev');
                const btnNext = document.getElementById('btnNext');
                
                if (btnPrev) {
                    btnPrev.disabled = questionNumber <= 1;
                    console.log('btnPrev disabled:', btnPrev.disabled);
                }
                if (btnNext) {
                    // Enable button, hanya ubah text
                    btnNext.disabled = false;
                    if (questionNumber >= totalQuestions) {
                        btnNext.innerHTML = 'Selesai<i class="bi bi-check-lg ms-2"></i>';
                        btnNext.classList.remove('btn-primary');
                        btnNext.classList.add('btn-success');
                    } else {
                        btnNext.innerHTML = 'Selanjutnya<i class="bi bi-arrow-right ms-2"></i>';
                        btnNext.classList.remove('btn-success');
                        btnNext.classList.add('btn-primary');
                    }
                    console.log('btnNext text:', btnNext.innerHTML, 'disabled:', btnNext.disabled);
                }
            }
            
            // Go to next question
            function goToNextQuestion() {
                console.log('goToNextQuestion clicked, questionLoader:', questionLoader);
                
                if (!questionLoader) {
                    console.error('QuestionLoader not initialized');
                    alert('Sistem belum siap. Mohon tunggu sebentar.');
                    return;
                }
                
                const currentNum = questionLoader.getCurrentNumber();
                const nextNum = currentNum + 1;
                
                console.log('Current:', currentNum, 'Next:', nextNum, 'Total:', totalQuestions);
                
                if (nextNum <= totalQuestions) {
                    saveCurrentAnswer();
                    loadQuestion(nextNum);
                } else {
                    // Last question, show submit
                    console.log('Last question, calling confirmSubmit');
                    const nextBtn = document.getElementById('btnNext');
                    if (nextBtn) {
                        nextBtn.disabled = true;
                        nextBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                    }
                    confirmSubmit();
                }
            }
            
            // Go to previous question
            function goToPrevQuestion() {
                if (questionLoader) {
                    const prevNum = questionLoader.getCurrentNumber() - 1;
                    if (prevNum >= 1) {
                        saveCurrentAnswer();
                        loadQuestion(prevNum);
                    }
                }
            }
            
            // Save current answer
            function saveCurrentAnswer() {
                if (!currentQuestionData) return;
                
                const questionId = currentQuestionData.id;
                const questionType = currentQuestionData.type;
                let answer = null;
                
                if (questionType === 'single') {
                    const checked = document.querySelector(`input[name="answer_${questionId}"]:checked`);
                    answer = checked ? checked.value : null;
                } else if (questionType === 'category') {
                    const answers = [];
                    ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
                        const checked = document.querySelector(`input[name="answer_${questionId}_${opt}"]:checked`);
                        if (checked) {
                            answers.push(`${opt}:${checked.value}`);
                        }
                    });
                    answer = answers.join(',');
                } else {
                    // Complex
                    const checked = document.querySelectorAll(`input[name="answer_${questionId}"]:checked`);
                    answer = Array.from(checked).map(el => el.value).join(',');
                }
                
                // Save to ExamState and SyncManager
                if (typeof ExamState !== 'undefined') {
                    const isDoubt = document.getElementById('doubtCheckbox')?.checked || false;
                    ExamState.saveAnswer(questionId, answer, isDoubt);
                }
                
                // Update nav button
                if (answer) {
                    updateNavButton(questionId, 'answered');
                }
            }
            
            // Toggle doubt for current question
            function toggleCurrentDoubt() {
                if (!currentQuestionData) return;
                
                const questionId = currentQuestionData.id;
                const isDoubt = document.getElementById('doubtCheckbox').checked;
                
                if (typeof ExamState !== 'undefined') {
                    ExamState.markDoubt(questionId, isDoubt);
                }
                
                updateNavButton(questionId, isDoubt ? 'doubt' : (getCurrentAnswer() ? 'answered' : ''));
            }
            
            // Get current answer
            function getCurrentAnswer() {
                if (!currentQuestionData) return null;
                
                const questionId = currentQuestionData.id;
                const questionType = currentQuestionData.type;
                
                if (questionType === 'single') {
                    const checked = document.querySelector(`input[name="answer_${questionId}"]:checked`);
                    return checked ? checked.value : null;
                } else if (questionType === 'category') {
                    const answers = [];
                    ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
                        const checked = document.querySelector(`input[name="answer_${questionId}_${opt}"]:checked`);
                        if (checked) answers.push(`${opt}:${checked.value}`);
                    });
                    return answers.join(',');
                } else {
                    const checked = document.querySelectorAll(`input[name="answer_${questionId}"]:checked`);
                    return Array.from(checked).map(el => el.value).join(',');
                }
            }
            
            // Navigate to question number
            function navigateToQuestion(number) {
                if (number >= 1 && number <= totalQuestions) {
                    saveCurrentAnswer();
                    loadQuestion(number);
                }
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize LazyImage
                if (typeof LazyImage !== 'undefined') {
                    LazyImage.init();
                }
                
                // Check fullscreen status and show banner
                setTimeout(() => {
                    checkFullscreen();
                }, 100);
                
                // Try to enter fullscreen (may not work without user gesture, but worth trying)
                // Fullscreen should persist from start.blade.php redirect
                setTimeout(() => {
                    const isFullscreen = document.fullscreenElement || 
                                        document.webkitFullscreenElement || 
                                        document.mozFullScreenElement ||
                                        document.msFullscreenElement;
                    
                    if (!isFullscreen) {
                        console.log('Attempting to enter fullscreen...');
                        enterFullscreen();
                    }
                }, 1000);
                
                startTimer();

                // Mark answered questions
                @foreach ($existingAnswers as $qId => $answer)
                    updateNavButton({{ $qId }}, 'answered');
                @endforeach

                // Mark doubt questions
                @foreach ($doubtQuestions as $qId)
                    updateNavButton({{ $qId }}, 'doubt');
                @endforeach
            });
        </script>
    @endpush
@endsection
