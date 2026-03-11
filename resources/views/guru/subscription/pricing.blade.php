{{-- resources/views/guru/subscription/pricing.blade.php --}}
@extends('layouts.guru')

@section('title', 'Upgrade Plan')

@section('content')
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2>Pilih Plan yang Sesuai</h2>
            <p class="text-muted">Tingkatkan limit dan akses fitur premium</p>
        </div>

        <div class="row g-4 mb-5">
            @foreach ($plans as $key => $plan)
                <div class="col-lg-4">
                    <div class="card border-0 shadow-lg h-100 {{ $key === 'pro' ? 'border-primary' : '' }}">
                        @if ($key === 'pro')
                            <div class="badge bg-primary position-absolute top-0 start-50 translate-middle">
                                PALING POPULER
                            </div>
                        @endif

                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h3 class="text-uppercase fw-bold">{{ $plan['name'] }}</h3>
                                @if ($key === 'free')
                                    <h1 class="display-4 my-3">GRATIS</h1>
                                @else
                                    <h1 class="display-4 my-3">
                                        Rp {{ number_format($plan['price']) }}
                                        <small class="fs-6 text-muted">/bulan</small>
                                    </h1>
                                    <p class="text-muted small">atau Rp {{ number_format($plan['price_yearly']) }}/tahun
                                        <br><span class="badge bg-success">Hemat 2 bulan!</span></p>
                                @endif
                            </div>

                            <ul class="list-unstyled mb-4">
                                @foreach ($plan['features'] as $feature)
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>

                            <div class="d-grid">
                                @if ($key === 'free')
                                    @if (auth()->user()->plan === 'free')
                                        <button class="btn btn-outline-secondary" disabled>Plan Aktif</button>
                                    @else
                                        <button class="btn btn-outline-secondary" disabled>Downgrade via Admin</button>
                                    @endif
                                @else
                                    @if (auth()->user()->plan === $key)
                                        <button class="btn btn-success" disabled>
                                            <i class="bi bi-check-circle"></i> Plan Aktif
                                        </button>
                                    @else
                                        <button class="btn btn-{{ $key === 'pro' ? 'primary' : 'dark' }}"
                                            data-bs-toggle="modal" data-bs-target="#upgradeModal"
                                            data-plan="{{ $key }}" data-plan-name="{{ $plan['name'] }}">
                                            Upgrade Sekarang
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Payment Info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-info-circle"></i> Cara Pembayaran</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Transfer Bank:</h6>
                        <div class="bg-light p-3 rounded mb-3">
                            <strong>{{ $payment['bank_name'] ?? 'Bank BCA' }}</strong><br>
                            No. Rekening: <code>{{ $payment['bank_account_number'] ?? '-' }}</code><br>
                            A/n: {{ $payment['bank_account_name'] ?? '-' }}
                            @if(!empty($payment['bank_branch']))
                                <br><small class="text-muted">{{ $payment['bank_branch'] }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>QRIS:</h6>
                        <div class="bg-light p-3 rounded text-center">
                            @if(!empty($payment['qris_image']))
                                <img src="{{ Storage::url($payment['qris_image']) }}" alt="QRIS" class="img-fluid" style="max-width: 200px;">
                            @else
                                <div class="py-4 text-muted">
                                    <i class="bi bi-qr-code fs-1"></i>
                                    <p class="small mb-0 mt-2">QRIS belum tersedia</p>
                                </div>
                            @endif
                            @if(!empty($payment['qris_merchant_name']))
                                <p class="small text-muted mb-0 mt-2">{{ $payment['qris_merchant_name'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0">
                    <strong>Penting:</strong> 
                    @if(!empty($payment['payment_instructions']))
                        {{ $payment['payment_instructions'] }}
                    @else
                        Setelah transfer, upload bukti pembayaran.
                    @endif
                    @if(!empty($payment['payment_whatsapp']))
                        <br>Konfirmasi via WhatsApp: <strong>{{ $payment['payment_whatsapp'] }}</strong>
                    @endif
                    <br>Admin akan memverifikasi dalam 1x24 jam.
                </div>
            </div>
        </div>
    </div>

    {{-- Upgrade Modal --}}
    <div class="modal fade" id="upgradeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('guru.subscription.upgrade') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upgrade ke <span id="modal-plan-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="plan" id="modal-plan-input">

                        <div class="mb-3">
                            <label class="form-label">Siklus Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="billing_cycle" id="billing_cycle" required>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan (Hemat 2 bulan!)</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <strong>Total Pembayaran:</strong>
                            <h3 class="mb-0 mt-2" id="total-amount">Rp 0</h3>
                        </div>

                        <div class="mb-3">
                            <label for="payment_proof" class="form-label">Upload Bukti Transfer <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('payment_proof') is-invalid @enderror"
                                id="payment_proof" name="payment_proof" accept="image/*" required>
                            <small class="text-muted">Format: JPG, PNG. Max 2MB</small>
                            @error('payment_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"
                                placeholder="Contoh: Transfer dari rekening BCA a/n John Doe"></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Setelah submit, jangan lupa kirim konfirmasi via WhatsApp ke <strong>08123456789</strong>
                            agar proses verifikasi lebih cepat.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-submit-upgrade">
                            <i class="bi bi-send"></i> Kirim Permintaan Upgrade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const planPrices = {
                pro: {
                    monthly: {{ $plans['pro']['price'] }},
                    yearly: {{ $plans['pro']['price_yearly'] }}
                },
                advanced: {
                    monthly: {{ $plans['advanced']['price'] }},
                    yearly: {{ $plans['advanced']['price_yearly'] }}
                }
            };

            document.getElementById('upgradeModal').addEventListener('show.bs.modal', function(e) {
                const button = e.relatedTarget;
                const plan = button.getAttribute('data-plan');
                const planName = button.getAttribute('data-plan-name');

                document.getElementById('modal-plan-input').value = plan;
                document.getElementById('modal-plan-name').textContent = planName;

                updateTotalAmount(plan);
            });

            document.getElementById('billing_cycle').addEventListener('change', function() {
                const plan = document.getElementById('modal-plan-input').value;
                updateTotalAmount(plan);
            });

            function updateTotalAmount(plan) {
                const cycle = document.getElementById('billing_cycle').value;
                const amount = planPrices[plan][cycle];
                document.getElementById('total-amount').textContent = 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Konfirmasi sebelum submit
            document.getElementById('btn-submit-upgrade').addEventListener('click', function() {
                const plan = document.getElementById('modal-plan-input').value;
                const cycle = document.getElementById('billing_cycle').value;
                const amount = planPrices[plan][cycle];
                const planName = document.getElementById('modal-plan-name').textContent;
                const cycleText = cycle === 'monthly' ? 'Bulanan' : 'Tahunan';
                
                const paymentProof = document.getElementById('payment_proof');
                if (!paymentProof.files || !paymentProof.files[0]) {
                    alert('Silakan upload bukti transfer terlebih dahulu!');
                    return;
                }

                const confirmMsg = `Konfirmasi Upgrade Plan\n\n` +
                    `Plan: ${planName}\n` +
                    `Siklus: ${cycleText}\n` +
                    `Total: Rp ${amount.toLocaleString('id-ID')}\n\n` +
                    `Pastikan Anda sudah transfer sesuai nominal.\n` +
                    `Admin akan memverifikasi dalam 1x24 jam.\n\n` +
                    `Lanjutkan?`;

                if (confirm(confirmMsg)) {
                    document.querySelector('#upgradeModal form').submit();
                }
            });
        </script>
    @endpush
@endsection
