{{-- resources/views/student/test/review.blade.php --}}
@extends('layouts.student')

@section('title', 'Review Pembahasan - ' . $package->title)

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Review Pembahasan</h2>
                <p class="text-muted mb-0">{{ $package->title }}</p>
            </div>
            <div>
                <select class="form-select form-select-sm" id="filterStatus" style="width: auto;">
                    <option value="all">Semua Soal</option>
                    <option value="wrong">Soal Salah</option>
                    <option value="correct">Soal Benar</option>
                    <option value="unanswered">Tidak Dijawab</option>
                </select>
                <a href="{{ route('student.test.result', $attempt) }}" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-3">
            @foreach ($questions as $index => $question)
                @php
                    $answer = $answersMap->get($question->id);
                    $studentAnswer = $answer ? $answer->answer : null;
                    $isCorrect = $answer ? $answer->is_correct : false;
                    $isAnswered = $answer && $answer->answer;

                    $correctAnswers = explode(',', $question->correct_answer);
                    $studentAnswers = $studentAnswer ? explode(',', $studentAnswer) : [];

                    $status = !$isAnswered ? 'unanswered' : ($isCorrect ? 'correct' : 'wrong');
                @endphp

                <div class="col-12 question-review" data-status="{{ $status }}">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Soal {{ $index + 1 }}</h5>
                                    <span class="badge bg-info">{{ $question->category->name }}</span>
                                    <span
                                        class="badge bg-{{ $question->question_type === 'single' ? 'primary' : 'success' }}">
                                        {{ $question->question_type === 'single' ? 'PG' : 'PG Kompleks' }}
                                    </span>
                                </div>
                                <div>
                                    @if ($isCorrect)
                                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Benar</span>
                                    @elseif(!$isAnswered)
                                        <span class="badge bg-secondary fs-6"><i class="bi bi-dash-circle"></i> Tidak
                                            Dijawab</span>
                                    @else
                                        <span class="badge bg-danger fs-6"><i class="bi bi-x-circle"></i> Salah</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            {{-- Question --}}
                            <div class="mb-4">
                                <p class="fs-5">{{ $question->question_text }}</p>
                                @if ($question->question_image)
                                    <img src="{{ Storage::url($question->question_image) }}" alt="Question Image"
                                        class="img-fluid rounded mb-3" style="max-width: 100%;">
                                @endif
                            </div>

                            {{-- Options --}}
                            <div class="mb-4">
                                @foreach (['A', 'B', 'C', 'D', 'E'] as $opt)
                                    @php
                                        $isStudentChoice = in_array($opt, $studentAnswers);
                                        $isCorrectOption = in_array($opt, $correctAnswers);

                                        $bgClass = '';
                                        if ($isCorrectOption) {
                                            $bgClass = 'bg-success bg-opacity-10 border-success';
                                        } elseif ($isStudentChoice && !$isCorrectOption) {
                                            $bgClass = 'bg-danger bg-opacity-10 border-danger';
                                        }
                                    @endphp

                                    <div class="border rounded p-3 mb-2 {{ $bgClass }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong>{{ $opt }}.</strong>
                                                {{ $question->{'option_' . strtolower($opt)} }}
                                            </div>
                                            <div class="ms-2">
                                                @if ($isCorrectOption)
                                                    <span class="badge bg-success"><i class="bi bi-check"></i> Kunci
                                                        Jawaban</span>
                                                @endif
                                                @if ($isStudentChoice)
                                                    <span class="badge bg-{{ $isCorrectOption ? 'success' : 'danger' }}">
                                                        <i class="bi bi-person"></i> Jawaban Anda
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Explanation --}}
                            @if ($question->explanation)
                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="bi bi-lightbulb"></i> Pembahasan:</h6>
                                    <p class="mb-0">{{ $question->explanation }}</p>
                                </div>
                            @else
                                <div class="alert alert-secondary">
                                    <i class="bi bi-info-circle"></i> Pembahasan tidak tersedia untuk soal ini.
                                </div>
                            @endif

                            {{-- Your Answer Summary --}}
                            <div class="card bg-light border-0 mt-3">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <strong>Jawaban Anda:</strong><br>
                                            <span
                                                class="badge bg-{{ $isCorrect ? 'success' : ($isAnswered ? 'danger' : 'secondary') }} fs-6 mt-1">
                                                {{ $studentAnswer ?: '-' }}
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Kunci Jawaban:</strong><br>
                                            <span class="badge bg-success fs-6 mt-1">{{ $question->correct_answer }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Status:</strong><br>
                                            @if ($isCorrect)
                                                <span class="text-success fs-5"><i class="bi bi-check-circle-fill"></i>
                                                    Benar</span>
                                            @elseif(!$isAnswered)
                                                <span class="text-secondary fs-5"><i class="bi bi-dash-circle-fill"></i>
                                                    Tidak Dijawab</span>
                                            @else
                                                <span class="text-danger fs-5"><i class="bi bi-x-circle-fill"></i>
                                                    Salah</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('filterStatus').addEventListener('change', function() {
                const filter = this.value;
                const questions = document.querySelectorAll('.question-review');

                questions.forEach(q => {
                    if (filter === 'all') {
                        q.style.display = 'block';
                    } else {
                        q.style.display = q.dataset.status === filter ? 'block' : 'none';
                    }
                });
            });
        </script>
    @endpush
@endsection
