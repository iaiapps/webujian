{{-- resources/views/guru/classes/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Kelola Kelas')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Kelola Kelas</h2>
                <p class="text-muted mb-0">
                    <span
                        class="badge bg-primary">{{ auth()->user()->classesCount() }}/{{ auth()->user()->max_classes }}</span>
                    kelas digunakan
                </p>
            </div>
            <a href="{{ route('guru.classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kelas
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Search --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('guru.classes.index') }}" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama kelas..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('guru.classes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Classes List --}}
        <div class="row g-3">
            @forelse($classes as $class)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">{{ $class->name }}</h5>
                                    <small class="text-muted">{{ $class->academic_year ?? '-' }}</small>
                                </div>
                                <span class="badge bg-primary">{{ $class->students_count }} siswa</span>
                            </div>

                            @if ($class->description)
                                <p class="text-muted small mb-3">{{ Str::limit($class->description, 80) }}</p>
                            @endif

                            <div class="d-flex gap-2">
                                <a href="{{ route('guru.classes.show', $class) }}" class="btn btn-sm btn-info flex-fill">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <a href="{{ route('guru.classes.edit', $class) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('guru.classes.destroy', $class) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus kelas {{ $class->name }}? Siswa di kelas ini akan tetap ada.')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-0 small text-muted">
                            Dibuat: {{ $class->created_at->format('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-4">Belum ada kelas. Buat kelas pertama Anda!</p>
                            <a href="{{ route('guru.classes.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Kelas
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($classes->hasPages())
            <div class="mt-4">
                {{ $classes->links() }}
            </div>
        @endif
    </div>

    {{-- Limit Reached Modal --}}
    @if (session('limit_reached'))
        <div class="modal fade show" id="limitModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">⚠️ Limit Tercapai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            onclick="this.closest('.modal').style.display='none'"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('limit_reached')['message'] }}</p>
                        <p class="mb-0"><strong>Saat ini:</strong>
                            {{ session('limit_reached')['current'] }}/{{ session('limit_reached')['limit'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="this.closest('.modal').style.display='none'">Tutup</button>
                        {{-- KELAS DINONAKTIFKAN - Link ini tidak akan pernah muncul --}}
                        <span class="btn btn-secondary">Fitur Kelas Dinonaktifkan</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
