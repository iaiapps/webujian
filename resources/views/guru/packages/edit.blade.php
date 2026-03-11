@extends('layouts.dashboard')

@section('title', 'Edit Paket Tes')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Paket Tes</h2>
            <a href="{{ route('guru.packages.show', $package) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('guru.packages.update', $package) }}" id="packageForm">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Informasi Dasar</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Paket <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="title" name="title" value="{{ old('title', $package->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="duration" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror"
                                        id="duration" name="duration" value="{{ old('duration', $package->duration) }}" min="1" required>
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="start_date" class="form-label">Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date" 
                                        value="{{ old('start_date', $package->start_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="end_date" class="form-label">Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date" 
                                        value="{{ old('end_date', $package->end_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Pilih Soal</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select id="filter_category" class="form-select form-select-sm">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_type" class="form-select form-select-sm">
                                        <option value="">Semua Tipe</option>
                                        <option value="single">PG Single</option>
                                        <option value="complex">PG Kompleks</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_difficulty" class="form-select form-select-sm">
                                        <option value="">Semua Tingkat</option>
                                        <option value="easy">Mudah</option>
                                        <option value="medium">Sedang</option>
                                        <option value="hard">Sulit</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="loadQuestions()">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Bank Soal:</h6>
                                    <div id="available-questions" class="border rounded p-2" style="height: 400px; overflow-y: auto;">
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-inbox"></i><br>
                                            Klik "Filter" untuk memuat soal
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Soal Dipilih: <span id="selected-count">{{ count($selectedQuestions) }}</span></h6>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearSelected()">
                                            <i class="bi bi-trash"></i> Hapus Semua
                                        </button>
                                    </div>
                                    <div id="selected-questions" class="border rounded p-2" style="height: 400px; overflow-y: auto;"></div>
                                </div>
                            </div>

                            <div id="question-ids-container">
                                @foreach($selectedQuestions as $qId)
                                    <input type="hidden" name="question_ids[]" value="{{ $qId }}">
                                @endforeach
                            </div>
                            @error('question_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Pengaturan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active">
                                        Paket Aktif
                                    </label>
                                </div>
                                <small class="text-muted">Nonaktifkan untuk menyembunyikan dari siswa</small>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label">Assign ke Kelas</label>
                                @foreach ($classes as $class)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="class_ids[]"
                                            value="{{ $class->id }}" id="class_{{ $class->id }}"
                                            {{ in_array($class->id, old('class_ids', $selectedClasses)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_{{ $class->id }}">
                                            {{ $class->name }}
                                        </label>
                                    </div>
                                @endforeach
                                @if ($classes->isEmpty())
                                    <p class="text-muted small mb-0">Belum ada kelas.</p>
                                @endif
                            </div>

                            <hr>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_result" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_result" value="1"
                                        id="show_result" {{ old('show_result', $package->show_result) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_result">Tampilkan Hasil</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_explanation" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_explanation" value="1"
                                        id="show_explanation" {{ old('show_explanation', $package->show_explanation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_explanation">Tampilkan Pembahasan</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_ranking" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_ranking" value="1"
                                        id="show_ranking" {{ old('show_ranking', $package->show_ranking) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_ranking">Tampilkan Ranking</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="shuffle_questions" value="0">
                                    <input class="form-check-input" type="checkbox" name="shuffle_questions" value="1"
                                        id="shuffle_questions" {{ old('shuffle_questions', $package->shuffle_questions) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_questions">Acak Urutan Soal</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Penilaian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 mb-3">
                                    <label for="score_correct" class="form-label">Skor Benar</label>
                                    <input type="number" step="0.01" class="form-control" id="score_correct" 
                                        name="score_correct" value="{{ old('score_correct', $package->score_correct) }}">
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="score_wrong" class="form-label">Skor Salah</label>
                                    <input type="number" step="0.01" class="form-control" id="score_wrong" 
                                        name="score_wrong" value="{{ old('score_wrong', $package->score_wrong) }}">
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="score_empty" class="form-label">Skor Kosong</label>
                                    <input type="number" step="0.01" class="form-control" id="score_empty" 
                                        name="score_empty" value="{{ old('score_empty', $package->score_empty) }}">
                                </div>
                            </div>
                            <small class="text-muted">Default: Benar +4, Salah -1, Kosong 0 (sistem UTBK)</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Update Paket Tes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let selectedQuestions = @json($selectedQuestions);

            document.addEventListener('DOMContentLoaded', () => {
                loadQuestions();
                renderSelectedQuestions();
            });

            function loadQuestions() {
                const params = new URLSearchParams({
                    category_id: document.getElementById('filter_category').value,
                    question_type: document.getElementById('filter_type').value,
                    difficulty: document.getElementById('filter_difficulty').value,
                });

                const container = document.getElementById('available-questions');
                container.innerHTML = '<div class="text-center py-3"><i class="bi bi-hourglass"></i> Memuat...</div>';

                fetch(`{{ url('/guru/packages/get-questions') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => renderAvailableQuestions(data.data || []))
                    .catch(err => {
                        container.innerHTML = '<div class="text-center text-danger py-3">Gagal memuat soal</div>';
                    });
            }

            function renderAvailableQuestions(questions) {
                const container = document.getElementById('available-questions');
                if (questions.length === 0) {
                    container.innerHTML = '<div class="text-center text-muted py-3">Tidak ada soal</div>';
                    return;
                }

                container.innerHTML = questions.map(q => `
                    <div class="border-bottom p-2 question-item ${selectedQuestions.includes(q.id) ? 'd-none' : ''}" data-id="${q.id}">
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1">
                                <span class="badge bg-info">${q.category?.name || '-'}</span>
                                <span class="badge bg-${q.question_type === 'single' ? 'primary' : 'success'}">${q.question_type === 'single' ? 'PG' : 'Complex'}</span>
                                <p class="mb-1 small">${q.question_text.substring(0, 100)}...</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="selectQuestion(${q.id})">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            function selectQuestion(id) {
                if (selectedQuestions.includes(id)) return;
                selectedQuestions.push(id);
                
                const item = document.querySelector(`#available-questions .question-item[data-id="${id}"]`);
                if (item) item.classList.add('d-none');
                
                renderSelectedQuestions();
            }

            function removeQuestion(id) {
                selectedQuestions = selectedQuestions.filter(qId => qId !== id);
                
                const item = document.querySelector(`#available-questions .question-item[data-id="${id}"]`);
                if (item) item.classList.remove('d-none');
                
                renderSelectedQuestions();
            }

            function renderSelectedQuestions() {
                const container = document.getElementById('selected-questions');
                const countEl = document.getElementById('selected-count');
                const idsContainer = document.getElementById('question-ids-container');

                countEl.textContent = selectedQuestions.length;

                if (selectedQuestions.length === 0) {
                    container.innerHTML = '<div class="text-center text-muted py-3">Belum ada soal dipilih</div>';
                    idsContainer.innerHTML = '';
                    return;
                }

                container.innerHTML = selectedQuestions.map((id, index) => `
                    <div class="border-bottom p-2 d-flex justify-content-between align-items-center">
                        <span><strong>${index + 1}.</strong> Soal ID: ${id}</span>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${id})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `).join('');

                idsContainer.innerHTML = selectedQuestions.map(id =>
                    `<input type="hidden" name="question_ids[]" value="${id}">`
                ).join('');
            }

            function clearSelected() {
                if (!confirm('Hapus semua soal yang dipilih?')) return;
                selectedQuestions.forEach(id => {
                    const item = document.querySelector(`#available-questions .question-item[data-id="${id}"]`);
                    if (item) item.classList.remove('d-none');
                });
                selectedQuestions = [];
                renderSelectedQuestions();
            }

            document.getElementById('packageForm').addEventListener('submit', function(e) {
                if (selectedQuestions.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal 1 soal!');
                }
            });
        </script>
    @endpush
@endsection
