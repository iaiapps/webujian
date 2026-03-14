@extends('layouts.dashboard')

@section('title', 'Pengaturan')

@section('content')
    <div class="container-fluid py-4">
        <h2 class="mb-4">Pengaturan Aplikasi</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- General Settings --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-gear"></i> Umum</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control"
                                    value="{{ $settings['general']['app_name'] ?? 'ExamWeb' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Timezone</label>
                                <select name="app_timezone" class="form-select">
                                    <option value="Asia/Jakarta"
                                        {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' }}>
                                        Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar"
                                        {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>
                                        Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura"
                                        {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>
                                        Asia/Jayapura (WIT)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Credit Settings --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-coin"></i> Kredit Default</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Kredit Default untuk User Baru</label>
                                <input type="number" name="credit_default" class="form-control"
                                    value="{{ $settings['limits']['credit_default'] ?? 10 }}" min="0">
                                <small class="text-muted">Jumlah kredit yang diberikan saat registrasi</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Global Limits --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-sliders"></i> Limit Global</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Limit ini berlaku untuk semua guru.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Siswa</label>
                                    <input type="number" name="global_max_students" class="form-control"
                                        value="{{ $settings['limits']['global_max_students'] ?? 50 }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Soal</label>
                                    <input type="number" name="global_max_questions" class="form-control"
                                        value="{{ $settings['limits']['global_max_questions'] ?? 100 }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
