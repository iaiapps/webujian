{{-- resources/views/guru/students/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Kelola Siswa')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Kelola Siswa</h2>
                <p class="text-muted mb-0">
                    <span
                        class="badge bg-primary">{{ auth()->user()->studentsCount() }}/{{ auth()->user()->max_students }}</span>
                    siswa digunakan
                </p>
            </div>
            <div class="btn-group">
                <a href="{{ route('guru.students.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Siswa
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

        @if (session('import_success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-check-circle"></i> Import Berhasil!</h5>
                <p>Berhasil import <strong>{{ session('import_success')['count'] }}</strong> siswa.</p>
                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#credentialsModal">
                    <i class="bi bi-eye"></i> Lihat Kredensial
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filter & Search --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('guru.students.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, username, NISN..."
                            value="{{ request('search') }}">
                    </div>
                    {{-- KELAS DINONAKTIFKAN - Filter kelas dihilangkan --}}
                    {{-- <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('guru.students.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>NISN</th>
                                {{-- KELAS DINONAKTIFKAN - Kolom kelas dihilangkan --}}
                                {{-- <th>Kelas</th> --}}
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                                <tr>
                                    <td>{{ $students->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $student->name }}</strong>
                                    </td>
                                    <td><code>{{ $student->username }}</code></td>
                                    <td>{{ $student->nisn ?? '-' }}</td>
                                    {{-- KELAS DINONAKTIFKAN --}}
                                    {{-- <td>
                                        @if ($student->classRoom)
                                            <span class="badge bg-info">{{ $student->classRoom->name }}</span>
                                        @else
                                            -
                                        @endif
                                    </td> --}}
                                    <td>{{ $student->email ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                                            {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('guru.students.show', $student) }}" class="btn btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('guru.students.edit', $student) }}" class="btn btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('guru.students.destroy', $student) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('Hapus siswa {{ $student->name }}?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada siswa. <a href="{{ route('guru.students.create') }}">Tambah siswa
                                            pertama</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($students->hasPages())
                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('guru.students.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Siswa dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Petunjuk:</h6>
                            <ol class="mb-0 ps-3">
                                <li>Download template Excel</li>
                                <li>Isi data siswa sesuai format</li>
                                <li>Upload file Excel</li>
                                <li>Set password default untuk semua siswa</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('guru.students.import.template') }}" class="btn btn-sm btn-success">
                                <i class="bi bi-download"></i> Download Template Excel
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File Excel <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file"
                                accept=".xlsx,.xls" required>
                        </div>

                        <div class="mb-3">
                            <label for="default_password" class="form-label">Password Default <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="default_password" name="default_password"
                                placeholder="Contoh: 123456" required>
                            <small class="text-muted">Password ini akan digunakan untuk semua siswa yang di-import</small>
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

    {{-- Credentials Modal --}}
    @if (session('import_success'))
        <div class="modal fade" id="credentialsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kredensial Siswa yang Di-import</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Penting!</strong> Simpan atau print kredensial ini. Data ini tidak akan ditampilkan
                            lagi.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Password</th>
                                        <th>Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (session('import_success')['credentials'] as $index => $cred)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $cred['name'] }}</td>
                                            <td><code>{{ $cred['username'] }}</code></td>
                                            <td><code>{{ $cred['password'] }}</code></td>
                                            <td>{{ $cred['class'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
