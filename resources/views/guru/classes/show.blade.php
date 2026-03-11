{{-- resources/views/guru/classes/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Detail Kelas - ' . $class->name)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $class->name }}</h2>
                <p class="text-muted mb-0">{{ $class->academic_year ?? '-' }}</p>
            </div>
            <div>
                <a href="{{ route('guru.classes.edit', $class) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('guru.classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Class Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Deskripsi</h6>
                        <p>{{ $class->description ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Jumlah Siswa</h6>
                        <h3 class="mb-0">{{ $class->students->count() }}</h3>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Dibuat</h6>
                        <p>{{ $class->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Students List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Siswa</h5>
                    <a href="{{ route('guru.students.create') }}?class_id={{ $class->id }}"
                        class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Siswa
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($class->students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($class->students as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td><code>{{ $student->username }}</code></td>
                                        <td>{{ $student->email ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                                                {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('guru.students.show', $student) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">
                        Belum ada siswa di kelas ini.
                        <a href="{{ route('guru.students.create') }}?class_id={{ $class->id }}">Tambah siswa</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
