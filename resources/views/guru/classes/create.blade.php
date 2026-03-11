{{-- resources/views/guru/classes/create.blade.php --}}
@extends('layouts.guru')

@section('title', 'Tambah Kelas')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tambah Kelas Baru</h2>
                    <a href="{{ route('guru.classes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('guru.classes.store') }}">
                            @csrf

                            {{-- Name --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Kelas <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}"
                                    placeholder="Contoh: XII IPA 1, Kelas Intensif UTBK" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Academic Year --}}
                            <div class="mb-3">
                                <label for="academic_year" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control @error('academic_year') is-invalid @enderror"
                                    id="academic_year" name="academic_year" value="{{ old('academic_year') }}"
                                    placeholder="Contoh: 2024/2025">
                                @error('academic_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="Deskripsi singkat tentang kelas ini (opsional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Simpan Kelas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
