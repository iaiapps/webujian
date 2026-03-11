{{-- resources/views/student/test/work.blade.php --}}
@extends('layouts.exam')

@section('title', 'Mengerjakan - ' . $package->title)

@push('styles')
<style>
    .exam-header {
        background: var(--primary);
        color: white;
        padding: 12px 0;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .exam-timer {
        font-size: 2rem;
        font-weight: 800;
        font-family: var(--font-heading);
    }

    .exam-timer.warning {
        color: var(--accent);
    }

    .exam-timer.danger {
        color: var(--danger);
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .question-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-light);
    }

    .question-number {
        font-family: var(--font-heading);
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
    }

    .question-text {
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .option-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid var(--border) !important;
    }

    .option-item:hover {
        border-color: var(--primary) !important;
        background: var(--primary-subtle);
    }

    .option-item.selected {
        border-color: var(--primary) !important;
        background: var(--primary-subtle);
    }

    .option-item input:checked + label {
        font-weight: 600;
    }

    .nav-sidebar {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        position: sticky;
        top: 80px;
    }

    .nav-btn {
        border-radius: var(--radius-sm);
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .nav-btn.answered {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .nav-btn.doubt {
        border-color: var(--accent);
        background: var(--accent-light);
        color: var(--accent);
    }

    .nav-btn.current {
        box-shadow: 0 0 0 3px var(--primary-subtle);
    }

    .btn-selesai {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .btn-selesai:hover {
        background: #059669;
        color: white;
    }
</style>
@endpush

@section('header-actions')
    <button type="button" class="btn btn-warning" onclick="confirmSubmit()" style="color: white;">
        <i class="bi bi-send"></i> Akhiri Tes
    </button>
@endsection

@section('content')
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
                                            class="img-fluid rounded mb-3" style="max-width: 100%;">
                                    @endif
                                </div>

                                {{-- Options --}}
                                @if ($question->question_type === 'single')
                                    {{-- Single Choice (Radio) --}}
                                    <div class="options-container">
                                        @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                            <div class="form-check mb-3 p-3 border rounded option-item"
                                                onclick="selectOption({{ $question->id }}, '{{ $opt }}', this)">
                                                <input class="form-check-input" type="radio"
                                                    name="answer_{{ $question->id }}" value="{{ $opt }}"
                                                    id="q{{ $question->id }}_{{ $opt }}"
                                                    {{ ($existingAnswers[$question->id] ?? '') === $opt ? 'checked' : '' }}>
                                                <label class="form-check-label w-100"
                                                    for="q{{ $question->id }}_{{ $opt }}">
                                                    <strong>{{ $opt }}.</strong>
                                                    {{ $question->{'option_' . strtolower($opt)} }}
                                                </label>
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
                                        @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                            @php
                                                $existingAnswer = $existingAnswers[$question->id] ?? '';
                                                $selectedOpts = explode(',', $existingAnswer);
                                                $isChecked = in_array($opt, $selectedOpts);
                                            @endphp
                                            <div
                                                class="form-check mb-3 p-3 border rounded option-item {{ $isChecked ? 'border-primary' : '' }}">
                                                <input class="form-check-input" type="checkbox"
                                                    name="answer_{{ $question->id }}[]" value="{{ $opt }}"
                                                    id="q{{ $question->id }}_{{ $opt }}"
                                                    {{ $isChecked ? 'checked' : '' }}
                                                    onchange="selectComplexOption({{ $question->id }})">
                                                <label class="form-check-label w-100"
                                                    for="q{{ $question->id }}_{{ $opt }}">
                                                    <strong>{{ $opt }}.</strong>
                                                    {{ $question->{'option_' . strtolower($opt)} }}
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
                                        <i class="bi bi-question-circle"></i> Ragu-ragu dengan jawaban ini
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
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Navigasi Nomor</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2" style="grid-template-columns: repeat(5, 1fr); display: grid;">
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
                                <div class="btn btn-sm btn-primary me-2" style="width: 30px;"></div>
                                <span>Sudah dijawab</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="btn btn-sm btn-outline-primary me-2" style="width: 30px;"></div>
                                <span>Belum dijawab</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="btn btn-sm btn-warning me-2" style="width: 30px;"></div>
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
            const endTime = new Date("{{ $attempt->end_time->toIso8601String() }}");
            let currentIndex = 0;
            const totalQuestions = {{ $questions->count() }};
            let timerInterval;

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
                        alert('⚠️ Waktu tersisa 10 menit!');
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
                    const type = questionEl.querySelector('input[type="radio"]') ? 'single' : 'complex';

                    if (type === 'single') {
                        const checked = questionEl.querySelector(`input[name="answer_${questionId}"]:checked`);
                        answer = checked ? checked.value : null;
                    } else {
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
