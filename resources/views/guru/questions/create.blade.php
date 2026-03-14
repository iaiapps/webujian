{{-- resources/views/guru/questions/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Tambah Soal')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tambah Soal Baru</h2>
                    <a href="{{ route('guru.questions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('guru.questions.store') }}" enctype="multipart/form-data"
                            id="questionForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Kategori <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                                        name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="question_type" class="form-label">Tipe Soal <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('question_type') is-invalid @enderror"
                                        id="question_type" name="question_type" required>
                                        <option value="single" {{ old('question_type') === 'single' ? 'selected' : '' }}>
                                            Pilihan Ganda</option>
                                        <option value="complex" {{ old('question_type') === 'complex' ? 'selected' : '' }}>
                                            PG Kompleks</option>
                                        <option value="category" {{ old('question_type') === 'category' ? 'selected' : '' }}>
                                            PG Kompleks (Kategori)</option>
                                    </select>
                                    @error('question_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="difficulty" class="form-label">Tingkat Kesulitan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('difficulty') is-invalid @enderror" id="difficulty"
                                        name="difficulty" required>
                                        <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Mudah
                                        </option>
                                        <option value="medium"
                                            {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Sedang
                                        </option>
                                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Sulit
                                        </option>
                                    </select>
                                    @error('difficulty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="question_text" class="form-label">Pertanyaan <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('question_text') is-invalid @enderror" id="question_text" name="question_text"
                                    rows="4" required>{{ old('question_text') }}</textarea>
                                @error('question_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="question_image" class="form-label">Gambar (Opsional)</label>
                                <input type="file" class="form-control @error('question_image') is-invalid @enderror"
                                    id="question_image" name="question_image" accept="image/*">
                                <small class="text-muted">Max 2MB. Format: JPG, PNG</small>
                                @error('question_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Pilihan Jawaban</h5>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Minimal 3 pilihan (A, B, C) wajib diisi. Pilihan D dan E opsional.
                            </div>

                            {{-- Option A (Required) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">A. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('options.0.content') is-invalid @enderror" 
                                    name="options[0][content]" placeholder="Pilihan A" value="{{ old('options.0.content') }}" required>
                                <input type="hidden" name="options[0][label]" value="A">
                                @error('options.0.content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Option B (Required) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">B. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('options.1.content') is-invalid @enderror" 
                                    name="options[1][content]" placeholder="Pilihan B" value="{{ old('options.1.content') }}" required>
                                <input type="hidden" name="options[1][label]" value="B">
                                @error('options.1.content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Option C (Required) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">C. <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('options.2.content') is-invalid @enderror" 
                                    name="options[2][content]" placeholder="Pilihan C" value="{{ old('options.2.content') }}" required>
                                <input type="hidden" name="options[2][label]" value="C">
                                @error('options.2.content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Option D (Optional) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">D. <span class="text-muted">(opsional)</span></label>
                                <input type="text" class="form-control @error('options.3.content') is-invalid @enderror" 
                                    name="options[3][content]" placeholder="Pilihan D (opsional)" value="{{ old('options.3.content') }}">
                                <input type="hidden" name="options[3][label]" value="D">
                                @error('options.3.content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Option E (Optional) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">E. <span class="text-muted">(opsional)</span></label>
                                <input type="text" class="form-control @error('options.4.content') is-invalid @enderror" 
                                    name="options[4][content]" placeholder="Pilihan E (opsional)" value="{{ old('options.4.content') }}">
                                <input type="hidden" name="options[4][label]" value="E">
                                @error('options.4.content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Jawaban Benar</h5>

                            {{-- Single Choice --}}
                            <div id="single-choice" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Pilih <strong>satu</strong> jawaban yang benar.
                                </div>
                                <div class="d-flex gap-4">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="correct_answer" 
                                                value="{{ $opt }}" id="single_{{ $opt }}" {{ old('correct_answer') === $opt ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="single_{{ $opt }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Complex Choice (Multiple) --}}
                            <div id="complex-choice" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Centang <strong>semua</strong> jawaban yang benar (bisa lebih dari satu).
                                </div>
                                <div class="d-flex gap-4">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="correct_answer[]" 
                                                value="{{ $opt }}" id="complex_{{ $opt }}" 
                                                {{ is_array(old('correct_answer')) && in_array($opt, old('correct_answer')) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="complex_{{ $opt }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Category Choice (True/False per option) --}}
                            <div id="category-choice" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Tentukan apakah setiap pilihan <strong>BENAR</strong> atau <strong>SALAH</strong>.
                                </div>
                                <div class="row">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                                        <div class="col-md-6 mb-3">
                                            <div class="border p-3 rounded">
                                                <label class="fw-bold mb-2">Pilihan {{ $opt }}:</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" 
                                                            name="correct_answer[{{ $opt }}]" value="B" id="category_{{ $opt }}_true"
                                                            {{ old('correct_answer.' . $opt) === 'B' ? 'checked' : '' }}>
                                                        <label class="form-check-label text-success fw-bold" for="category_{{ $opt }}_true">
                                                            <i class="bi bi-check-circle"></i> Benar
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" 
                                                            name="correct_answer[{{ $opt }}]" value="S" id="category_{{ $opt }}_false"
                                                            {{ old('correct_answer.' . $opt) === 'S' ? 'checked' : '' }}>
                                                        <label class="form-check-label text-danger fw-bold" for="category_{{ $opt }}_false">
                                                            <i class="bi bi-x-circle"></i> Salah
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @error('correct_answer')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror

                            <hr class="my-4">

                            <div class="mb-3">
                                <label for="explanation" class="form-label">Pembahasan (Opsional)</label>
                                <textarea class="form-control @error('explanation') is-invalid @enderror" id="explanation" name="explanation"
                                    rows="4">{{ old('explanation') }}</textarea>
                                @error('explanation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Simpan Soal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('question_type');
                const singleChoice = document.getElementById('single-choice');
                const complexChoice = document.getElementById('complex-choice');
                const categoryChoice = document.getElementById('category-choice');

                function toggleQuestionType() {
                    const type = typeSelect.value;
                    
                    // Hide all sections first
                    singleChoice.style.display = 'none';
                    complexChoice.style.display = 'none';
                    categoryChoice.style.display = 'none';
                    
                    // Show appropriate section
                    if (type === 'single') {
                        singleChoice.style.display = 'block';
                    } else if (type === 'complex') {
                        complexChoice.style.display = 'block';
                    } else if (type === 'category') {
                        categoryChoice.style.display = 'block';
                    }
                }

                typeSelect.addEventListener('change', toggleQuestionType);
                toggleQuestionType(); // Init
            });
        </script>
    @endpush
@endsection
