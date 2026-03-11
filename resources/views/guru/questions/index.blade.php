{{-- resources/views/guru/questions/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Bank Soal')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Bank Soal</h2>
                <p class="text-muted mb-0">
                    <span
                        class="badge bg-primary">{{ auth()->user()->questionsCount() }}/{{ auth()->user()->max_questions }}</span>
                    soal digunakan
                </p>
            </div>
            <div class="btn-group">
                <a href="{{ route('guru.questions.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Soal
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-excel"></i> Import Excel
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filter & Search --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('guru.questions.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari pertanyaan..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="question_type" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="single" {{ request('question_type') === 'single' ? 'selected' : '' }}>Pilihan
                                Ganda</option>
                            <option value="complex" {{ request('question_type') === 'complex' ? 'selected' : '' }}>PG
                                Kompleks</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="difficulty" class="form-select">
                            <option value="">Semua Tingkat</option>
                            <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Mudah</option>
                            <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Sedang
                            </option>
                            <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Sulit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('guru.questions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Questions List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @forelse($questions as $question)
                    <div class="border rounded p-3 mb-3">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-info">{{ $question->category->name }}</span>
                                    <span
                                        class="badge bg-{{ $question->question_type === 'single' ? 'primary' : 'success' }}">
                                        {{ $question->question_type === 'single' ? 'Pilihan Ganda' : 'PG Kompleks' }}
                                    </span>
                                    <span
                                        class="badge bg-{{ $question->difficulty === 'easy' ? 'success' : ($question->difficulty === 'medium' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($question->difficulty) }}
                                    </span>
                                </div>

                                <p class="mb-2"><strong>{{ Str::limit($question->question_text, 150) }}</strong></p>

                                @if ($question->question_image)
                                    <img src="{{ Storage::url($question->question_image) }}" alt="Question Image"
                                        class="img-thumbnail mb-2" style="max-width: 200px;">
                                @endif

                                <div class="small text-muted">
                                    Jawaban: <code>{{ $question->correct_answer }}</code> |
                                    Dibuat: {{ $question->created_at->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group-vertical gap-2">
                                    <a href="{{ route('guru.questions.show', $question) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </a>
                                    <a href="{{ route('guru.questions.edit', $question) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('guru.questions.destroy', $question) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger w-100"
                                            onclick="return confirm('Hapus soal ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-4">Belum ada soal. Buat soal pertama Anda!</p>
                        <a href="{{ route('guru.questions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Soal
                        </a>
                    </div>
                @endforelse

                @if ($questions->hasPages())
                    <div class="mt-4">
                        {{ $questions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('guru.questions.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Soal dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Petunjuk:</h6>
                            <ol class="mb-0 ps-3">
                                <li>Download template Excel</li>
                                <li>Isi soal sesuai format</li>
                                <li>Untuk PG Kompleks, jawaban: A,C,E (pisah koma)</li>
                                <li>Upload file Excel</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('guru.questions.import.template') }}" class="btn btn-sm btn-success">
                                <i class="bi bi-download"></i> Download Template Excel
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File Excel <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file"
                                accept=".xlsx,.xls" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Limit Modal --}}
    @if (session('limit_reached'))
        <div class="modal fade show" id="limitModal" tabindex="-1"
            style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">⚠️ Limit Tercapai</h5>
                        <button type="button" class="btn-close"
                            onclick="this.closest('.modal').style.display='none'"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('limit_reached')['message'] }}</p>
                        <p class="mb-0"><strong>Saat ini:</strong>
                            {{ session('limit_reached')['current'] }}/{{ session('limit_reached')['limit'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="this.closest('.modal').style.display='none'">Tutup</button>
                        <a href="{{ route('guru.subscription.pricing') }}" class="btn btn-primary">Upgrade Plan</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
