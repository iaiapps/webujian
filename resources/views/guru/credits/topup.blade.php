@extends('layouts.dashboard')

@section('title', 'Beli Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Beli Kredit</h2>
        <a href="{{ route('guru.credits.history') }}" class="btn btn-outline-primary">
            <i class="bi bi-clock-history"></i> Riwayat Kredit
        </a>
    </div>

    <div class="row">
        {{-- Credit Packages --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pilih Paket Kredit</h5>
                </div>
                <div class="card-body">
                    @if($creditPackages->count() > 0)
                        <div class="row g-3">
                            @foreach($creditPackages as $package)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border {{ session('selected_package_id') == $package->id ? 'border-primary' : '' }}" 
                                     id="package-{{ $package->id }}"
                                     onclick="selectPackage({{ $package->id }}, {{ $package->credit_amount }}, {{ $package->bonus_credits }}, {{ $package->price }}, '{{ $package->name }}')"
                                     style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <h4 class="text-primary mb-1">{{ $package->credit_amount }}</h4>
                                        <small class="text-muted">Kredit Dasar</small>
                                        
                                        @if($package->bonus_credits > 0)
                                        <div class="mt-2">
                                            <span class="badge bg-success">+{{ $package->bonus_credits }} Bonus</span>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-success">Total: {{ $package->getTotalCredits() }} Kredit</small>
                                        </div>
                                        @endif
                                        
                                        @if($package->description)
                                        <div class="mt-2">
                                            <small class="text-muted">{{ $package->description }}</small>
                                        </div>
                                        @endif
                                        
                                        <div class="mt-3">
                                            <h5 class="mb-0 text-primary">{{ $package->getFormattedPrice() }}</h5>
                                            @if($package->bonus_credits > 0)
                                            <small class="text-muted">
                                                {{ number_format($package->getPricePerCredit(), 0, ',', '.') }}/kredit
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white text-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm select-btn" data-package-id="{{ $package->id }}">
                                            Pilih Paket
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Belum ada paket kredit tersedia. Silakan hubungi admin.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Detail Pembelian</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guru.credits.purchase') }}" enctype="multipart/form-data" id="purchase-form">
                        @csrf
                        
                        <input type="hidden" name="package_id" id="package_id" value="">

                        <div id="package-detail" class="alert alert-light border d-none">
                            <h6 class="mb-2" id="detail-name">-</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <small>Kredit Dasar:</small>
                                <strong id="detail-credits">0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small>Bonus:</small>
                                <strong class="text-success" id="detail-bonus">0</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <strong>Total Kredit:</strong>
                                <strong class="text-primary" id="detail-total">0</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <strong>Harga:</strong>
                                <strong class="text-primary" id="detail-price">Rp 0</strong>
                            </div>
                        </div>

                        <div id="no-package-selected" class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Silakan pilih paket kredit di sebelah kiri
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
                            <div class="ms-4 mt-2 p-2 bg-light rounded">
                                <small class="text-muted">
                                    <strong>{{ $payment['bank_name'] }}</strong><br>
                                    {{ $payment['bank_account_number'] }}<br>
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
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-1"></i>Instruksi:</h6>
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
    let selectedPackageId = null;

    function selectPackage(id, credits, bonus, price, name) {
        // Remove selection from all cards
        document.querySelectorAll('.card').forEach(card => {
            card.classList.remove('border-primary');
        });
        
        // Add selection to clicked card
        document.getElementById('package-' + id).classList.add('border-primary');
        
        // Update form
        document.getElementById('package_id').value = id;
        
        // Update detail display
        document.getElementById('detail-name').textContent = name;
        document.getElementById('detail-credits').textContent = credits;
        document.getElementById('detail-bonus').textContent = bonus;
        document.getElementById('detail-total').textContent = credits + bonus;
        document.getElementById('detail-price').textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
        
        // Show detail, hide warning
        document.getElementById('package-detail').classList.remove('d-none');
        document.getElementById('no-package-selected').classList.add('d-none');
        
        // Enable submit button
        document.getElementById('submit-btn').disabled = false;
        
        selectedPackageId = id;
    }

    // Handle card click
    document.querySelectorAll('.select-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const packageId = this.dataset.packageId;
            document.getElementById('package-' + packageId).click();
        });
    });
</script>
@endpush
@endsection
