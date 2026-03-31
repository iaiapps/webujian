{{-- resources/views/student/exam/work.blade.php --}}
@extends('student.exam.layouts.exam')

@section('title', 'Mengerjakan - ' . $package->title)

@section('violations')
    <i class="bi bi-exclamation-triangle text-warning"></i>
    <span class="badge bg-warning" id="violation-badge">{{ $violationCount ?? 0 }}/{{ $package->max_violations ?? 3 }}</span>
@endsection

@section('header-actions')
    <button type="button" class="btn btn-warning text-white" onclick="confirmSubmit()">
        <i class="bi bi-send"></i> Akhiri Tes
    </button>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="question-area">
                    <div class="question-card">
                        @foreach ($questions as $index => $question)
                            <div class="question-item {{ $index === 0 ? '' : 'd-none' }}"
                                data-question-id="{{ $question->id }}" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Soal {{ $index + 1 }} dari {{ $questions->count() }}</h5>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-info">{{ $question->category->name }}</span>
                                        <span
                                            class="badge bg-{{ $question->question_type === 'single' ? 'primary' : 'success' }}">
                                            {{ $question->question_type === 'single' ? 'Pilihan Ganda' : 'PG Kompleks' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="fs-5">{{ $question->question_text }}</p>

                                    @if ($question->question_image)
                                        <img src="{{ Storage::url($question->question_image) }}" alt="Question Image"
                                            class="img-fluid rounded mb-3">
                                    @endif
                                </div>

                                {{-- Options --}}
                                @if ($question->question_type === 'single')
                                    {{-- Single Choice (Radio) --}}
                                    <div class="options-container">
                                        @foreach ($question->options as $option)
                                            <div class="form-check mb-3 p-3 border rounded option-item"
                                                onclick="selectOption({{ $question->id }}, '{{ $option->label }}', this)">
                                                <input class="form-check-input" type="radio"
                                                    name="answer_{{ $question->id }}" value="{{ $option->label }}"
                                                    id="q{{ $question->id }}_{{ $option->label }}"
                                                    {{ ($existingAnswers[$question->id] ?? '') === $option->label ? 'checked' : '' }}>
                                                <label class="form-check-label w-100"
                                                    for="q{{ $question->id }}_{{ $option->label }}">
                                                    <strong>{{ $option->label }}.</strong>
                                                    {{ $option->content }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($question->question_type === 'category')
                                    {{-- Category Type (True/False for each statement) --}}
                                    @php
                                        // Parse existing category answer: "A:B,B:S,C:B" → ["A" => "B", "B" => "S", ...]
                                        $existingCategoryAnswers = [];
                                        $existingAnswerStr = $existingAnswers[$question->id] ?? '';
                                        foreach (explode(',', $existingAnswerStr) as $pair) {
                                            $parts = explode(':', $pair);
                                            if (count($parts) === 2) {
                                                $existingCategoryAnswers[trim($parts[0])] = trim($parts[1]);
                                            }
                                        }
                                    @endphp
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i> Tentukan apakah setiap pernyataan di bawah ini
                                        <strong>BENAR</strong> atau <strong>SALAH</strong>.
                                    </div>
                                    <div class="options-container">
                                        @foreach ($question->options as $option)
                                            <div class="card mb-3 border">
                                                <div class="card-body">
                                                    <p class="mb-3"><strong>{{ $option->label }}.</strong>
                                                        {{ $option->content }}
                                                    </p>
                                                    <div class="d-flex gap-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="answer_{{ $question->id }}_{{ $option->label }}"
                                                                value="B"
                                                                id="q{{ $question->id }}_{{ $option->label }}_true"
                                                                {{ ($existingCategoryAnswers[$option->label] ?? '') === 'B' ? 'checked' : '' }}
                                                                onchange="selectCategoryOption({{ $question->id }})">
                                                            <label class="form-check-label text-success fw-bold"
                                                                for="q{{ $question->id }}_{{ $option->label }}_true">
                                                                <i class="bi bi-check-circle"></i> Benar
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="answer_{{ $question->id }}_{{ $option->label }}"
                                                                value="S"
                                                                id="q{{ $question->id }}_{{ $option->label }}_false"
                                                                {{ ($existingCategoryAnswers[$option->label] ?? '') === 'S' ? 'checked' : '' }}
                                                                onchange="selectCategoryOption({{ $question->id }})">
                                                            <label class="form-check-label text-danger fw-bold"
                                                                for="q{{ $question->id }}_{{ $option->label }}_false">
                                                                <i class="bi bi-x-circle"></i> Salah
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Complex Choice (Checkbox) --}}
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i> Pilih semua jawaban yang benar (bisa lebih dari
                                        satu)
                                    </div>
                                    <div class="options-container">
                                        @foreach ($question->options as $option)
                                            @php
                                                $existingAnswer = $existingAnswers[$question->id] ?? '';
                                                $selectedOpts = explode(',', $existingAnswer);
                                                $isChecked = in_array($option->label, $selectedOpts);
                                            @endphp
                                            <div
                                                class="form-check mb-3 p-3 border rounded option-item {{ $isChecked ? 'border-primary' : '' }}">
                                                <input class="form-check-input" type="checkbox"
                                                    name="answer_{{ $question->id }}[]" value="{{ $option->label }}"
                                                    id="q{{ $question->id }}_{{ $option->label }}"
                                                    {{ $isChecked ? 'checked' : '' }}
                                                    onchange="selectComplexOption({{ $question->id }})">
                                                <label class="form-check-label w-100"
                                                    for="q{{ $question->id }}_{{ $option->label }}">
                                                    <strong>{{ $option->label }}.</strong>
                                                    {{ $option->content }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Ragu-ragu --}}
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="doubt_{{ $question->id }}"
                                        {{ in_array($question->id, $doubtQuestions) ? 'checked' : '' }}
                                        onchange="toggleDoubt({{ $question->id }})">
                                    <label class="form-check-label" for="doubt_{{ $question->id }}">
                                        Ragu-ragu dengan jawaban ini?
                                    </label>
                                </div>

                                {{-- Navigation --}}
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="prevQuestion()"
                                        {{ $index === 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-chevron-left"></i> Sebelumnya
                                    </button>
                                    @if ($index < $questions->count() - 1)
                                        <button type="button" class="btn btn-primary" onclick="nextQuestion()">
                                            Selanjutnya <i class="bi bi-chevron-right"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success" onclick="confirmSubmit()">
                                            <i class="bi bi-send"></i> Selesai & Submit
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
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

            // Initialize badge on page load
            if (violationCount > 0) {
                updateViolationDisplay();
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

            function updateViolationDisplay() {
                const badge = document.getElementById('violation-badge');
                if (badge) {
                    badge.textContent = violationCount + '/' + maxViolations;
                    if (violationCount >= maxViolations - 1) {
                        badge.classList.remove('bg-warning');
                        badge.classList.add('bg-danger');
                    }
                    
                    // Add animation effect
                    badge.style.animation = 'none';
                    badge.offsetHeight; // Trigger reflow
                    badge.style.animation = 'pulse 0.5s ease-in-out';
                    
                    // Play warning sound
                    playWarningSound();
                }
            }
            
            // Track user interaction for audio initialization
            let hasUserInteracted = false;
            let audioContext = null;
            
            function initAudio() {
                if (!hasUserInteracted) {
                    hasUserInteracted = true;
                    // Initialize audio context after user interaction
                    try {
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    } catch (e) {
                        console.log('AudioContext not supported');
                    }
                }
            }
            
            // Listen for user interactions
            document.addEventListener('click', initAudio, { once: true });
            document.addEventListener('keydown', initAudio, { once: true });
            document.addEventListener('touchstart', initAudio, { once: true });
            
            // Play warning sound for violations
            function playWarningSound() {
                if (!hasUserInteracted) return;
                
                try {
                    // Use AudioContext for better browser support
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
                        // Fallback to Audio element
                        const audio = new Audio();
                        audio.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE';
                        audio.volume = 0.5;
                        audio.play().catch(e => console.log('Sound play failed:', e));
                    }
                } catch (e) {
                    console.log('Audio not supported');
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
                    showViolationModal('Anda keluar dari mode fullscreen. Masuk kembali ke fullscreen atau akan dicatat sebagai pelanggaran.', false);
                    
                    // Auto re-enter fullscreen after 3 seconds
                    setTimeout(() => {
                        enterFullscreen();
                        fullscreenWarningShown = false;
                    }, 3000);
                    
                    // Report as violation after warning
                    setTimeout(() => {
                        reportViolation('exit_fullscreen');
                    }, 5000);
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
                showQuestion(index);
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
                const answered = document.querySelectorAll('.nav-btn.btn-primary').length;
                const unanswered = totalQuestions - answered;

                let msg = `Anda akan mengakhiri tes.\n\n`;
                msg += `Dijawab: ${answered} soal\n`;
                msg += `Belum dijawab: ${unanswered} soal\n\n`;
                msg += `Yakin ingin submit?`;

                if (confirm(msg)) {
                    submitTest();
                }
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
                        }
                    })
                    .catch(err => {
                        console.error('Submit error:', err);
                        alert('Gagal submit. Silakan coba lagi.');
                    });
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

            // Initialize
            document.addEventListener('DOMContentLoaded', () => {
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
