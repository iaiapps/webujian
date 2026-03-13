@extends('layouts.dashboard')

@section('title', 'Edit Paket Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Paket Kredit</h2>
        <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.credit-packages.update', $creditPackage) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $creditPackage->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $creditPackage->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah Kredit <span class="text-danger">*</span></label>
                                <input type="number" name="credit_amount" class="form-control @error('credit_amount') is-invalid @enderror" value="{{ old('credit_amount', $creditPackage->credit_amount) }}" required min="1">
                                @error('credit_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bonus Kredit</label>
                                <input type="number" name="bonus_credits" class="form-control @error('bonus_credits') is-invalid @enderror" value="{{ old('bonus_credits', $creditPackage->bonus_credits) }}" min="0">
                                @error('bonus_credits')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Kredit</label>
                                <div class="form-control bg-light" id="total-credits">{{ $creditPackage->getTotalCredits() }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $creditPackage->price) }}" required min="0">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $creditPackage->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $creditPackage->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Paket Aktif (ditampilkan kepada user)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Paket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Preview</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary mb-1">{{ $creditPackage->credit_amount }}</h4>
                        <small class="text-muted">Kredit Dasar</small>
                        
                        @if($creditPackage->bonus_credits > 0)
                        <div class="mt-2">
                            <span class="badge bg-success">+{{ $creditPackage->bonus_credits }} Bonus</span>
                        </div>
                        <div class="mt-1">
                            <small class="text-success">Total: {{ $creditPackage->getTotalCredits() }} Kredit</small>
                        </div>
                        @endif
                        
                        <div class="mt-3">
                            <h5 class="mb-0 text-primary">{{ $creditPackage->getFormattedPrice() }}</h5>
                            @if($creditPackage->bonus_credits > 0)
                            <small class="text-muted">
                                {{ number_format($creditPackage->getPricePerCredit(), 0, ',', '.') }}/kredit
                            </small>
                            @endif
                        </div>
                    </div>
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
