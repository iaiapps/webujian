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
                            @php
                                $typeLabels = [
                                    'single' => ['label' => 'Pilihan Ganda', 'class' => 'primary'],
                                    'complex' => ['label' => 'PG Kompleks', 'class' => 'success'],
                                    'category' => ['label' => 'PG Kompleks (Kategori)', 'class' => 'warning']
                                ];
                                $typeInfo = $typeLabels[$question->question_type] ?? ['label' => $question->question_type, 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeInfo['class'] }}">
                                {{ $typeInfo['label'] }}
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

                        @if ($question->question_type === 'category')
                            {{-- Category Type --}}
                            <h5 class="mb-3">Pernyataan:</h5>
                            @php
                                // Parse category answers: "A:B,B:S,C:B" → ["A" => "B", "B" => "S", ...]
                                $categoryAnswers = [];
                                foreach (explode(',', $question->correct_answer) as $pair) {
                                    $parts = explode(':', $pair);
                                    if (count($parts) === 2) {
                                        $categoryAnswers[trim($parts[0])] = trim($parts[1]);
                                    }
                                }
                            @endphp

                            <div class="list-group mb-3">
                                @foreach($question->options as $option)
                                    @php
                                        $isTrue = ($categoryAnswers[$option->label] ?? '') === 'B';
                                    @endphp
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $option->label }}.</strong> {{ $option->content }}
                                        </div>
                                        <span class="badge bg-{{ $isTrue ? 'success' : 'danger' }}">
                                            {{ $isTrue ? 'Benar' : 'Salah' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="alert alert-light border">
                                <strong>Kunci Jawaban:</strong> <code>{{ $question->correct_answer }}</code>
                            </div>
                        @else
                            {{-- Single/Complex Type --}}
                            <h5 class="mb-3">Pilihan Jawaban:</h5>
                            @php
                                $correctAnswers = explode(',', $question->correct_answer);
                            @endphp

                            <div class="list-group mb-3">
                                @foreach($question->options as $option)
                                    <div class="list-group-item {{ in_array($option->label, $correctAnswers) ? 'list-group-item-success' : '' }}">
                                        <strong>{{ $option->label }}.</strong> {{ $option->content }}
                                        @if (in_array($option->label, $correctAnswers))
                                            <span class="badge bg-success float-end">Benar</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

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
