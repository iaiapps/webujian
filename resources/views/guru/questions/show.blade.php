{{-- resources/views/guru/questions/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Detail Soal')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Detail Soal</h2>
                    <div>
                        <a href="{{ route('guru.questions.edit', $question) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('guru.questions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-info">{{ $question->category->name }}</span>
                            <span class="badge bg-{{ $question->question_type === 'single' ? 'primary' : 'success' }}">
                                {{ $question->question_type === 'single' ? 'Pilihan Ganda' : 'PG Kompleks' }}
                            </span>
                            <span
                                class="badge bg-{{ $question->difficulty === 'easy' ? 'success' : ($question->difficulty === 'medium' ? 'warning' : 'danger') }}">
                                {{ ucfirst($question->difficulty) }}
                            </span>
                        </div>

                        <h5 class="mb-3">Pertanyaan:</h5>
                        <p class="border-start border-3 border-primary ps-3">{{ $question->question_text }}</p>

                        @if ($question->question_image)
                            <div class="mb-3">
                                <img src="{{ Storage::url($question->question_image) }}" alt="Question Image"
                                    class="img-fluid rounded" style="max-width: 100%;">
                            </div>
                        @endif

                        <h5 class="mb-3">Pilihan Jawaban:</h5>
                        @php
                            $correctAnswers = explode(',', $question->correct_answer);
                        @endphp

                        <div class="list-group mb-3">
                            <div
                                class="list-group-item {{ in_array('A', $correctAnswers) ? 'list-group-item-success' : '' }}">
                                <strong>A.</strong> {{ $question->option_a }}
                                @if (in_array('A', $correctAnswers))
                                    <span class="badge bg-success float-end">Benar</span>
                                @endif
                            </div>
                            <div
                                class="list-group-item {{ in_array('B', $correctAnswers) ? 'list-group-item-success' : '' }}">
                                <strong>B.</strong> {{ $question->option_b }}
                                @if (in_array('B', $correctAnswers))
                                    <span class="badge bg-success float-end">Benar</span>
                                @endif
                            </div>
                            <div
                                class="list-group-item {{ in_array('C', $correctAnswers) ? 'list-group-item-success' : '' }}">
                                <strong>C.</strong> {{ $question->option_c }}
                                @if (in_array('C', $correctAnswers))
                                    <span class="badge bg-success float-end">Benar</span>
                                @endif
                            </div>
                            <div
                                class="list-group-item {{ in_array('D', $correctAnswers) ? 'list-group-item-success' : '' }}">
                                <strong>D.</strong> {{ $question->option_d }}
                                @if (in_array('D', $correctAnswers))
                                    <span class="badge bg-success float-end">Benar</span>
                                @endif
                            </div>
                            <div
                                class="list-group-item {{ in_array('E', $correctAnswers) ? 'list-group-item-success' : '' }}">
                                <strong>E.</strong> {{ $question->option_e }}
                                @if (in_array('E', $correctAnswers))
                                    <span class="badge bg-success float-end">Benar</span>
                                @endif
                            </div>
                        </div>

                        @if ($question->explanation)
                            <h5 class="mb-3">Pembahasan:</h5>
                            <div class="alert alert-info">
                                <i class="bi bi-lightbulb"></i> {{ $question->explanation }}
                            </div>
                        @endif

                        <hr>

                        <div class="row text-muted small">
                            <div class="col-md-6">
                                <strong>Dibuat:</strong> {{ $question->created_at->format('d M Y H:i') }}
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Terakhir diupdate:</strong> {{ $question->updated_at->format('d M Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
