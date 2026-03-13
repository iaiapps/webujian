@extends('layouts.dashboard')

@section('title', 'Tambah Paket Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Paket Kredit</h2>
        <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.credit-packages.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Paket 10, Paket Hemat 50">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah Kredit <span class="text-danger">*</span></label>
                                <input type="number" name="credit_amount" class="form-control @error('credit_amount') is-invalid @enderror" value="{{ old('credit_amount') }}" required min="1" placeholder="10">
                                @error('credit_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bonus Kredit</label>
                                <input type="number" name="bonus_credits" class="form-control @error('bonus_credits') is-invalid @enderror" value="{{ old('bonus_credits', 0) }}" min="0" placeholder="0">
                                <small class="text-muted">Opsional, untuk promo</small>
                                @error('bonus_credits')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Kredit</label>
                                <div class="form-control bg-light" id="total-credits">0</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required min="0" placeholder="50000">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Deskripsi opsional">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Simpan Paket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Beri nama yang mudah dipahami user</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Bonus kredit untuk promo/hemat</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Atur urutan untuk tampilan</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i> Paket yang dinonaktifkan tidak akan ditampilkan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const creditInput = document.querySelector('[name="credit_amount"]');
    const bonusInput = document.querySelector('[name="bonus_credits"]');
    const totalDisplay = document.getElementById('total-credits');

    function updateTotal() {
        const credits = parseInt(creditInput.value) || 0;
        const bonus = parseInt(bonusInput.value) || 0;
        totalDisplay.textContent = credits + bonus;
    }

    creditInput.addEventListener('input', updateTotal);
    bonusInput.addEventListener('input', updateTotal);
</script>
@endpush
@endsection
