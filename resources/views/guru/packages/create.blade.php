{{-- resources/views/guru/packages/create.blade.php --}}
@extends('layouts.guru')

@section('title', 'Buat Paket Tes')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Buat Paket Tes Baru</h2>
            <a href="{{ route('guru.packages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('guru.packages.store') }}" id="packageForm">
            @csrf

            <div class="row g-3">
                {{-- Left Column: Basic Info --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Informasi Dasar</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Paket <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="title" name="title" value="{{ old('title') }}"
                                    placeholder="Contoh: Try Out UTBK 2025 #1" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="duration" class="form-label">Durasi (menit) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror"
                                        id="duration" name="duration" value="{{ old('duration', 120) }}" min="1"
                                        required>
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="start_date" class="form-label">Mulai <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('start_date') is-invalid @enderror" id="start_date"
                                        name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="end_date" class="form-label">Selesai <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('end_date') is-invalid @enderror" id="end_date"
                                        name="end_date" value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Select Questions --}}
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
                                    <div id="available-questions" class="border rounded p-2"
                                        style="height: 400px; overflow-y: auto;">
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-inbox"></i><br>
                                            Klik "Filter" untuk memuat soal
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Soal Dipilih: <span id="selected-count">0</span></h6>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearSelected()">
                                            <i class="bi bi-trash"></i> Hapus Semua
                                        </button>
                                    </div>
                                    <div id="selected-questions" class="border rounded p-2"
                                        style="height: 400px; overflow-y: auto;">
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-inbox"></i><br>
                                            Belum ada soal dipilih
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#randomModal">
                                    <i class="bi bi-shuffle"></i> Pilih Random
                                </button>
                            </div>

                            <div id="question-ids-container"></div>
                            @error('question_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Right Column: Settings --}}
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
                                        id="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
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
                                            {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_{{ $class->id }}">
                                            {{ $class->name }} ({{ $class->students_count ?? 0 }} siswa)
                                        </label>
                                    </div>
                                @endforeach
                                @if ($classes->isEmpty())
                                    <p class="text-muted small mb-0">Belum ada kelas. <a
                                            href="{{ route('guru.classes.create') }}">Buat kelas</a></p>
                                @endif
                            </div>

                            <hr>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_result" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_result" value="1"
                                        id="show_result" {{ old('show_result', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_result">
                                        Tampilkan Hasil
                                    </label>
                                </div>
                                <small class="text-muted">Siswa dapat melihat skor setelah selesai</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_explanation" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_explanation"
                                        value="1" id="show_explanation"
                                        {{ old('show_explanation', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_explanation">
                                        Tampilkan Pembahasan
                                    </label>
                                </div>
                                <small class="text-muted">Siswa dapat melihat pembahasan soal</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_ranking" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_ranking" value="1"
                                        id="show_ranking" {{ old('show_ranking', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_ranking">
                                        Tampilkan Ranking
                                    </label>
                                </div>
                                <small class="text-muted">Siswa dapat melihat ranking kelas</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="shuffle_questions" value="0">
                                    <input class="form-check-input" type="checkbox" name="shuffle_questions"
                                        value="1" id="shuffle_questions"
                                        {{ old('shuffle_questions', 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_questions">
                                        Acak Urutan Soal
                                    </label>
                                </div>
                                <small class="text-muted">Urutan soal akan diacak untuk setiap siswa</small>
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
                                        name="score_correct" value="{{ old('score_correct', 4) }}">
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="score_wrong" class="form-label">Skor Salah</label>
                                    <input type="number" step="0.01" class="form-control" id="score_wrong" 
                                        name="score_wrong" value="{{ old('score_wrong', -1) }}">
                                </div>
                                <div class="col-4 mb-3">
                                    <label for="score_empty" class="form-label">Skor Kosong</label>
                                    <input type="number" step="0.01" class="form-control" id="score_empty" 
                                        name="score_empty" value="{{ old('score_empty', 0) }}">
                                </div>
                            </div>
                            <small class="text-muted">Default: Benar +4, Salah -1, Kosong 0 (sistem UTBK)</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Simpan Paket Tes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Random Modal --}}
    <div class="modal fade" id="randomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Soal Random</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Soal</label>
                        <input type="number" id="random_count" class="form-control" min="1" max="100"
                            value="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori (opsional)</label>
                        <select id="random_category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tingkat Kesulitan (opsional)</label>
                        <select id="random_difficulty" class="form-select">
                            <option value="">Semua Tingkat</option>
                            <option value="easy">Mudah</option>
                            <option value="medium">Sedang</option>
                            <option value="hard">Sulit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="selectRandom()">Pilih</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let selectedQuestions = [];

            function loadQuestions() {
                const params = new URLSearchParams({
                    category_id: document.getElementById('filter_category').value,
                    question_type: document.getElementById('filter_type').value,
                    difficulty: document.getElementById('filter_difficulty').value,
                });

                const container = document.getElementById('available-questions');
                container.innerHTML = '<div class="text-center py-3"><i class="bi bi-hourglass"></i> Memuat...</div>';

                fetch(`{{ url('/guru/packages/get-questions') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        renderAvailableQuestions(data.data || []);
                    })
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = '<div class="text-center text-danger py-3">Gagal memuat soal: ' + err.message + '</div>';
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
                    <span class="badge bg-info">${q.category.name}</span>
                    <span class="badge bg-${q.question_type === 'single' ? 'primary' : 'success'}">${q.question_type === 'single' ? 'PG' : 'Complex'}</span>
                    <p class="mb-1 small">${q.question_text.substring(0, 100)}...</p>
                </div>
                <button type="button" class="btn btn-sm btn-success" onclick="selectQuestion(${q.id}, this.closest('.question-item'))">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
        </div>
    `).join('');
            }

            function selectQuestion(id, element) {
                if (selectedQuestions.includes(id)) return;

                selectedQuestions.push(id);
                element.classList.add('d-none');

                // Get question data
                const questionText = element.querySelector('p').textContent;
                const badges = element.querySelectorAll('.badge');

                renderSelectedQuestions();
            }

            function removeQuestion(id) {
                selectedQuestions = selectedQuestions.filter(qId => qId !== id);

                // Show in available
                const availableItem = document.querySelector(`#available-questions .question-item[data-id="${id}"]`);
                if (availableItem) {
                    availableItem.classList.remove('d-none');
                }

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

                // Hidden inputs
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

            function selectRandom() {
                const count = document.getElementById('random_count').value;
                const category = document.getElementById('random_category').value;
                const difficulty = document.getElementById('random_difficulty').value;

                const params = new URLSearchParams({
                    count,
                    category_id: category,
                    difficulty
                });

                fetch(`/guru/packages/get-random-questions?${params}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(q => {
                            if (!selectedQuestions.includes(q.id)) {
                                selectedQuestions.push(q.id);
                            }
                        });
                        renderSelectedQuestions();
                        bootstrap.Modal.getInstance(document.getElementById('randomModal')).hide();
                        loadQuestions(); // Reload to hide selected
                    })
                    .catch(err => alert('Gagal memilih soal random'));
            }

            // Form validation
            document.getElementById('packageForm').addEventListener('submit', function(e) {
                if (selectedQuestions.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal 1 soal!');
                    return false;
                }
            });

            // Load questions on page load
            document.addEventListener('DOMContentLoaded', () => {
                loadQuestions();
            });
        </script>
    @endpush
@endsection
