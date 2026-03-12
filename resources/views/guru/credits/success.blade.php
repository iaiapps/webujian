@extends('layouts.dashboard')

@section('title', 'Pembelian Kredit Berhasil')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="mb-3">Pembelian Kredit Berhasil!</h3>
                    
                    @if(isset($result))
                    <div class="bg-light rounded p-4 mb-4">
                        <div class="row">
                            <div class="col-6 text-start">
                                <p class="mb-1">Invoice</p>
                                <p class="mb-1">Kredit Dibeli</p>
                                <p class="mb-1">Bonus Kredit</p>
                                <p class="mb-0 fw-bold">Total Kredit Diterima</p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1"><strong>{{ $result['invoice_number'] }}</strong></p>
                                <p class="mb-1">{{ $result['credit_amount'] }} Kredit</p>
                                <p class="mb-1 text-success">+{{ $result['bonus_credits'] }} Kredit</p>
                                <p class="mb-0 fw-bold text-primary">{{ $result['total_credits'] }} Kredit</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6 text-start">
                                <p class="mb-0">Total Pembayaran</p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-0 fw-bold">Rp {{ number_format($result['total_price'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        Kredit telah ditambahkan ke akun Anda. Sekarang Anda dapat membuat paket tes.
                    </div>
                    @endif

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('guru.credits.index') }}" class="btn btn-primary">
                            <i class="bi bi-wallet2"></i> Lihat Kredit Saya
                        </a>
                        <a href="{{ route('guru.packages.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-box-seam"></i> Buat Paket Tes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection