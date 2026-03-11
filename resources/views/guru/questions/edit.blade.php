@extends('layouts.dashboard')

@section('title', 'Edit Soal')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit Soal</h2>
                <a href="{{ route('guru.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('guru.questions.update', $question) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $question->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="question_type" class="form-label">Tipe Soal <span class="text-danger">*</span></label>
                                <select class="form-select @error('question_type') is-invalid @enderror" id="question_type" name="question_type" required>
                                    <option value="single" {{ old('question_type', $question->question_type) == 'single' ? 'selected' : '' }}>Pilihan Ganda</option>
                                    <option value="complex" {{ old('question_type', $question->question_type) == 'complex' ? 'selected' : '' }}>PG Kompleks</option>
                                </select>
                                @error('question_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="difficulty" class="form-label">Tingkat Kesulitan <span class="text-danger">*</span></label>
                                <select class="form-select @error('difficulty') is-invalid @enderror" id="difficulty" name="difficulty" required>
                                    <option value="easy" {{ old('difficulty', $question->difficulty) == 'easy' ? 'selected' : '' }}>Mudah</option>
                                    <option value="medium" {{ old('difficulty', $question->difficulty) == 'medium' ? 'selected' : '' }}>Sedang</option>
                                    <option value="hard" {{ old('difficulty', $question->difficulty) == 'hard' ? 'selected' : '' }}>Sulit</option>
                                </select>
                                @error('difficulty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="question_text" class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror" id="question_text" name="question_text" rows="4" required>{{ old('question_text', $question->question_text) }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="question_image" class="form-label">Gambar</label>
                            @if($question->question_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($question->question_image) }}" alt="Current Image" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('question_image') is-invalid @enderror" id="question_image" name="question_image" accept="image/*">
                            <small class="text-muted">Max 2MB. Format: JPG, PNG. Kosongkan jika tidak ingin mengubah.</small>
                            @error('question_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Pilihan Jawaban</h5>

                        @php $correctAnswers = explode(',', $question->correct_answer); @endphp

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="A" id="answer_a" {{ in_array('A', $correctAnswers) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="answer_a">A.</label>
                            </div>
                            <input type="text" class="form-control mt-2" name="option_a" value="{{ old('option_a', $question->option_a) }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="B" id="answer_b" {{ in_array('B', $correctAnswers) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="answer_b">B.</label>
                            </div>
                            <input type="text" class="form-control mt-2" name="option_b" value="{{ old('option_b', $question->option_b) }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="C" id="answer_c" {{ in_array('C', $correctAnswers) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="answer_c">C.</label>
                            </div>
                            <input type="text" class="form-control mt-2" name="option_c" value="{{ old('option_c', $question->option_c) }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="D" id="answer_d" {{ in_array('D', $correctAnswers) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="answer_d">D.</label>
                            </div>
                            <input type="text" class="form-control mt-2" name="option_d" value="{{ old('option_d', $question->option_d) }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="E" id="answer_e" {{ in_array('E', $correctAnswers) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="answer_e">E.</label>
                            </div>
                            <input type="text" class="form-control mt-2" name="option_e" value="{{ old('option_e', $question->option_e) }}" required>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="explanation" class="form-label">Pembahasan</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror" id="explanation" name="explanation" rows="4">{{ old('explanation', $question->explanation) }}</textarea>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Update Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
