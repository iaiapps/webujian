{{-- resources/views/guru/packages/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Detail Paket - ' . $package->title)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $package->title }}</h2>
                @if ($package->isAvailable())
                    <span class="badge bg-success">Sedang Berlangsung</span>
                @elseif($package->start_date > now())
                    <span class="badge bg-info">Akan Datang</span>
                @else
                    <span class="badge bg-secondary">Berakhir</span>
                @endif
            </div>
            <div>
                <a href="{{ route('guru.packages.edit', $package) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('guru.packages.index') }}" class="btn btn-outline-secondary">
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

        <div class="row g-3">
            {{-- Package Info --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Informasi Paket</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Deskripsi</h6>
                                <p>{{ $package->description ?? '-' }}</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted">Durasi</h6>
                                <p><strong>{{ $package->duration }}</strong> menit</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted">Total Soal</h6>
                                <p><strong>{{ $package->total_questions }}</strong> soal</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Waktu Pelaksanaan</h6>
                                <p>
                                    <i class="bi bi-calendar-event"></i> {{ $package->start_date->formatIndo('datetime') }}<br>
                                    <i class="bi bi-calendar-x"></i> {{ $package->end_date->formatIndo('datetime') }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Pengaturan</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($package->show_result)
                                        <span class="badge bg-success">Tampilkan Hasil</span>
                                    @endif
                                    @if ($package->show_explanation)
                                        <span class="badge bg-info">Tampilkan Pembahasan</span>
                                    @endif
                                    @if ($package->show_ranking)
                                        <span class="badge bg-primary">Tampilkan Ranking</span>
                                    @endif
                                    @if ($package->shuffle_questions)
                                        <span class="badge bg-warning">Acak Soal</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Kelas yang Mengikuti:</h6>
                        @if ($package->classes->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($package->classes as $class)
                                    <span class="badge bg-secondary">{{ $class->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Belum ada kelas yang di-assign</p>
                        @endif
                    </div>
                </div>

                {{-- Questions List --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Daftar Soal ({{ $package->questions->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Pertanyaan</th>
                                        <th>Kategori</th>
                                        <th>Tipe</th>
                                        <th>Tingkat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($package->questions as $question)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ Str::limit($question->question_text, 80) }}</td>
                                            <td><span class="badge bg-info">{{ $question->category->name }}</span></td>
                                            <td><span
                                                    class="badge bg-{{ $question->question_type === 'single' ? 'primary' : 'success' }}">{{ $question->question_type === 'single' ? 'PG' : 'Complex' }}</span>
                                            </td>
                                            <td><span
                                                    class="badge bg-{{ $question->difficulty === 'easy' ? 'success' : ($question->difficulty === 'medium' ? 'warning' : 'danger') }}">{{ ucfirst($question->difficulty) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Statistik</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Peserta</span>
                                <strong>{{ $statistics['total_attempts'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Selesai</span>
                                <strong class="text-success">{{ $statistics['completed'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Sedang Mengerjakan</span>
                                <strong class="text-warning">{{ $statistics['ongoing'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Rata-rata Skor</span>
                                <strong class="text-primary">{{ number_format($statistics['avg_score'] ?? 0, 1) }}</strong>
                            </div>
                        </div>

                        @if ($statistics['completed'] > 0)
                            <a href="{{ route('guru.results.package', $package) }}" class="btn btn-primary w-100">
                                <i class="bi bi-bar-chart"></i> Lihat Hasil Lengkap
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Recent Attempts --}}
                @if ($package->testAttempts->count() > 0)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($package->testAttempts->take(10) as $attempt)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $attempt->student->name }}</strong>
                                        <span
                                            class="badge bg-{{ $attempt->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($attempt->status) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        @if ($attempt->status === 'completed')
                                            Skor: {{ number_format($attempt->total_score) }} |
                                            {{ $attempt->submitted_at->diffForHumans() }}
                                        @else
                                            Dimulai {{ $attempt->start_time->diffForHumans() }}
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
