@extends('layouts.dashboard')

@section('title', 'Beli Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Beli Kredit</h2>
        </div>
    </div>

    <div class="row">
        {{-- Credit Packages --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Paket Kredit</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($creditPackages as $package)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h4 class="text-primary mb-0">{{ $package['credits'] }}</h4>
                                    <small class="text-muted">Kredit</small>
                                    @if($package['bonus'] > 0)
                                    <div class="mt-2">
                                        <span class="badge bg-success">Bonus {{ $package['bonus'] }} Kredit</span>
                                    </div>
                                    @endif
                                    <div class="mt-3">
                                        <strong class="fs-5">Rp {{ number_format($package['price'], 0, ',', '.') }}</strong>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary mt-3 w-100"
                                        onclick="selectCreditPackage({{ $package['credits'] }}, {{ $package['price'] }}, {{ $package['total'] }})">
                                        Pilih
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pembelian Kredit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guru.credits.purchase') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div id="selected-package-info" class="alert alert-info d-none">
                            <strong>Paket Terpilih:</strong> <span id="selected-credits">0</span> Kredit<br>
                            <strong>Total:</strong> Rp <span id="selected-price">0</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Kredit yang Ingin Dibeli</label>
                            <select name="credit_amount" id="credit_amount" class="form-select" required>
                                <option value="">Pilih Paket</option>
                                @foreach($creditPackages as $package)
                                <option value="{{ $package['credits'] }}" data-price="{{ $package['price'] }}" data-total="{{ $package['total'] }}">
                                    {{ $package['credits'] }} Kredit (+{{ $package['bonus'] }} bonus) = Rp {{ number_format($package['price'], 0, ',', '.') }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Pembayaran</label>
                            <div class="fs-4 fw-bold text-primary">Rp <span id="total-price">0</span></div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Metode Pembayaran</h6>

                        @if(!empty($payment['bank_name']))
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bank_transfer">
                                    Bank Transfer
                                </label>
                            </div>
                            <div class="ms-4 mt-2">
                                <small class="text-muted">
                                    {{ $payment['bank_name'] }} - {{ $payment['bank_account_number'] }}<br>
                                    A/n {{ $payment['bank_account_name'] }}
                                </small>
                            </div>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Upload Bukti Transfer <span class="text-danger">*</span></label>
                            <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                            <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                        </div>

                        @if(!empty($payment['payment_instructions']))
                        <div class="alert alert-info mb-3">
                            <h6 class="alert-heading">Instruksi Pembayaran:</h6>
                            <small>{{ $payment['payment_instructions'] }}</small>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100" id="submit-btn" disabled>
                            <i class="bi bi-check-circle"></i> Konfirmasi Pembelian
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const creditAmountSelect = document.getElementById('credit_amount');
    const totalPriceSpan = document.getElementById('total-price');
    const selectedCreditsSpan = document.getElementById('selected-credits');
    const selectedPriceSpan = document.getElementById('selected-price');
    const selectedPackageInfo = document.getElementById('selected-package-info');
    const submitBtn = document.getElementById('submit-btn');

    creditAmountSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            const price = option.dataset.price;
            const total = option.dataset.total;
            
            totalPriceSpan.textContent = parseInt(price).toLocaleString('id-ID');
            selectedCreditsSpan.textContent = total;
            selectedPriceSpan.textContent = parseInt(price).toLocaleString('id-ID');
            selectedPackageInfo.classList.remove('d-none');
            submitBtn.disabled = false;
        } else {
            totalPriceSpan.textContent = '0';
            selectedPackageInfo.classList.add('d-none');
            submitBtn.disabled = true;
        }
    });

    function selectCreditPackage(credits, price, total) {
        creditAmountSelect.value = credits;
        creditAmountSelect.dispatchEvent(new Event('change'));
        
        document.getElementById('credit_amount').scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endpush
@endsection