{{-- resources/views/guru/students/show.blade.php --}}
@extends('layouts.guru')

@section('title', 'Detail Siswa - ' . $student->name)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Siswa</h2>
            <div>
                <a href="{{ route('guru.students.edit', $student) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('guru.students.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('new_student_credentials'))
            <div class="alert alert-info alert-dismissible">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Kredensial Login Siswa</h5>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="100">Username</td>
                        <td><strong><code>{{ session('new_student_credentials')['username'] }}</code></strong></td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td><strong><code>{{ session('new_student_credentials')['password'] }}</code></strong></td>
                    </tr>
                </table>
                <small class="text-muted">Simpan kredensial ini dan berikan kepada siswa.</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px;">
                                <i class="bi bi-person-fill text-primary" style="font-size: 3rem;"></i>
                            </div>
                        </div>

                        <h5 class="text-center mb-3">{{ $student->name }}</h5>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Username</td>
                                <td><code>{{ $student->username }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">NISN</td>
                                <td>{{ $student->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td>{{ $student->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kelas</td>
                                <td>
                                    @if ($student->classRoom)
                                        <span class="badge bg-info">{{ $student->classRoom->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Terdaftar</td>
                                <td>{{ $student->created_at->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Login Terakhir</td>
                                <td>{{ $student->last_login_at ? $student->last_login_at->format('d M Y H:i') : 'Belum pernah' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Histori Tes</h5>
                    </div>
                    <div class="card-body">
                        @if ($student->testAttempts->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Paket Tes</th>
                                            <th>Tanggal</th>
                                            <th>Skor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($student->testAttempts as $attempt)
                                            <tr>
                                                <td>{{ $attempt->package->title }}</td>
                                                <td>{{ $attempt->created_at->format('d M Y H:i') }}</td>
                                                <td><strong>{{ number_format($attempt->total_score) }}</strong></td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $attempt->status === 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($attempt->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-4 mb-0">Belum ada histori tes</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
