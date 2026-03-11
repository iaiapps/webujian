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

                            <div id="single-choice" style="display: none;">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer" value="A"
                                            id="answer_a" {{ old('correct_answer') === 'A' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="answer_a">A.</label>
                                    </div>
                                    <input type="text" class="form-control @error('option_a') is-invalid @enderror mt-2"
                                        name="option_a" placeholder="Pilihan A" value="{{ old('option_a') }}" required>
                                    @error('option_a')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer" value="B"
                                            id="answer_b" {{ old('correct_answer') === 'B' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="answer_b">B.</label>
                                    </div>
                                    <input type="text" class="form-control @error('option_b') is-invalid @enderror mt-2"
                                        name="option_b" placeholder="Pilihan B" value="{{ old('option_b') }}" required>
                                    @error('option_b')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer"
                                            value="C" id="answer_c"
                                            {{ old('correct_answer') === 'C' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="answer_c">C.</label>
                                    </div>
                                    <input type="text"
                                        class="form-control @error('option_c') is-invalid @enderror mt-2" name="option_c"
                                        placeholder="Pilihan C" value="{{ old('option_c') }}" required>
                                    @error('option_c')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer"
                                            value="D" id="answer_d"
                                            {{ old('correct_answer') === 'D' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="answer_d">D.</label>
                                    </div>
                                    <input type="text"
                                        class="form-control @error('option_d') is-invalid @enderror mt-2" name="option_d"
                                        placeholder="Pilihan D" value="{{ old('option_d') }}" required>
                                    @error('option_d')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer"
                                            value="E" id="answer_e"
                                            {{ old('correct_answer') === 'E' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="answer_e">E.</label>
                                    </div>
                                    <input type="text"
                                        class="form-control @error('option_e') is-invalid @enderror mt-2" name="option_e"
                                        placeholder="Pilihan E" value="{{ old('option_e') }}" required>
                                    @error('option_e')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="complex-choice" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Untuk PG Kompleks, centang <strong>semua</strong>
                                    jawaban yang benar.
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="correct_answer[]"
                                            value="A" id="complex_a">
                                        <label class="form-check-label fw-bold" for="complex_a">A.</label>
                                    </div>
                                    <input type="text" class="form-control mt-2" name="option_a_complex"
                                        placeholder="Pilihan A">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="correct_answer[]"
                                            value="B" id="complex_b">
                                        <label class="form-check-label fw-bold" for="complex_b">B.</label>
                                    </div>
                                    <input type="text" class="form-control mt-2" name="option_b_complex"
                                        placeholder="Pilihan B">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="correct_answer[]"
                                            value="C" id="complex_c">
                                        <label class="form-check-label fw-bold" for="complex_c">C.</label>
                                    </div>
                                    <input type="text" class="form-control mt-2" name="option_c_complex"
                                        placeholder="Pilihan C">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="correct_answer[]"
                                            value="D" id="complex_d">
                                        <label class="form-check-label fw-bold" for="complex_d">D.</label>
                                    </div>
                                    <input type="text" class="form-control mt-2" name="option_d_complex"
                                        placeholder="Pilihan D">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="correct_answer[]"
                                            value="E" id="complex_e">
                                        <label class="form-check-label fw-bold" for="complex_e">E.</label>
                                    </div>
                                    <input type="text" class="form-control mt-2" name="option_e_complex"
                                        placeholder="Pilihan E">
                                </div>
                            </div>

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

                function toggleQuestionType() {
                    const type = typeSelect.value;
                    if (type === 'single') {
                        singleChoice.style.display = 'block';
                        complexChoice.style.display = 'none';
                        // Copy values from complex to single
                        document.querySelector('input[name="option_a"]').value = document.querySelector(
                            'input[name="option_a_complex"]').value || '';
                        document.querySelector('input[name="option_b"]').value = document.querySelector(
                            'input[name="option_b_complex"]').value || '';
                        document.querySelector('input[name="option_c"]').value = document.querySelector(
                            'input[name="option_c_complex"]').value || '';
                        document.querySelector('input[name="option_d"]').value = document.querySelector(
                            'input[name="option_d_complex"]').value || '';
                        document.querySelector('input[name="option_e"]').value = document.querySelector(
                            'input[name="option_e_complex"]').value || '';
                    } else {
                        singleChoice.style.display = 'none';
                        complexChoice.style.display = 'block';
                        // Copy values from single to complex
                        document.querySelector('input[name="option_a_complex"]').value = document.querySelector(
                            'input[name="option_a"]').value || '';
                        document.querySelector('input[name="option_b_complex"]').value = document.querySelector(
                            'input[name="option_b"]').value || '';
                        document.querySelector('input[name="option_c_complex"]').value = document.querySelector(
                            'input[name="option_c"]').value || '';
                        document.querySelector('input[name="option_d_complex"]').value = document.querySelector(
                            'input[name="option_d"]').value || '';
                        document.querySelector('input[name="option_e_complex"]').value = document.querySelector(
                            'input[name="option_e"]').value || '';
                    }
                }

                typeSelect.addEventListener('change', toggleQuestionType);
                toggleQuestionType(); // Init

                // Form submit handler for complex type
                document.getElementById('questionForm').addEventListener('submit', function(e) {
                    if (typeSelect.value === 'complex') {
                        // Copy complex values to main option fields
                        document.querySelector('input[name="option_a"]').value = document.querySelector(
                            'input[name="option_a_complex"]').value;
                        document.querySelector('input[name="option_b"]').value = document.querySelector(
                            'input[name="option_b_complex"]').value;
                        document.querySelector('input[name="option_c"]').value = document.querySelector(
                            'input[name="option_c_complex"]').value;
                        document.querySelector('input[name="option_d"]').value = document.querySelector(
                            'input[name="option_d_complex"]').value;
                        document.querySelector('input[name="option_e"]').value = document.querySelector(
                            'input[name="option_e_complex"]').value;
                    }
                });
            });
        </script>
    @endpush
@endsection
