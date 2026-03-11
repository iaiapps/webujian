@extends('layouts.dashboard')

@section('title', 'Pengaturan')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Pengaturan Aplikasi</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
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
                            <input type="text" name="app_name" class="form-control" value="{{ $settings['general']['app_name'] ?? 'TKA SaaS' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="app_timezone" class="form-select">
                                <option value="Asia/Jakarta" {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar" {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" {{ ($settings['general']['app_timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Payment Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Bank Transfer</h6>
                        <div class="mb-3">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ $settings['payment']['bank_name'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control" value="{{ $settings['payment']['bank_account_number'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ $settings['payment']['bank_account_name'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cabang (opsional)</label>
                            <input type="text" name="bank_branch" class="form-control" value="{{ $settings['payment']['bank_branch'] ?? '' }}">
                        </div>

                        <hr>
                        <h6 class="text-muted mb-3">QRIS</h6>
                        <div class="mb-3">
                            <label class="form-label">Nama Merchant</label>
                            <input type="text" name="qris_merchant_name" class="form-control" value="{{ $settings['payment']['qris_merchant_name'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar QRIS</label>
                            <input type="file" name="qris_image" class="form-control" accept="image/*">
                            @if(!empty($settings['payment']['qris_image']))
                                <small class="text-muted">Sudah ada gambar QRIS</small>
                            @endif
                        </div>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label">WhatsApp Konfirmasi</label>
                            <input type="text" name="payment_whatsapp" class="form-control" value="{{ $settings['payment']['payment_whatsapp'] ?? '' }}" placeholder="6281234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instruksi Pembayaran</label>
                            <textarea name="payment_instructions" class="form-control" rows="2">{{ $settings['payment']['payment_instructions'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Plan Limits --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-sliders"></i> Limit Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Max Siswa</th>
                                        <th>Max Kelas</th>
                                        <th>Max Soal</th>
                                        <th>Max Paket</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-secondary">FREE</span></td>
                                        <td><input type="number" name="free_max_students" class="form-control form-control-sm" value="{{ $settings['limits']['free_max_students'] ?? 30 }}"></td>
                                        <td><input type="number" name="free_max_classes" class="form-control form-control-sm" value="{{ $settings['limits']['free_max_classes'] ?? 1 }}"></td>
                                        <td><input type="number" name="free_max_questions" class="form-control form-control-sm" value="{{ $settings['limits']['free_max_questions'] ?? 100 }}"></td>
                                        <td><input type="number" name="free_max_packages" class="form-control form-control-sm" value="{{ $settings['limits']['free_max_packages'] ?? 3 }}"></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary">PRO</span></td>
                                        <td><input type="number" name="pro_max_students" class="form-control form-control-sm" value="{{ $settings['limits']['pro_max_students'] ?? 150 }}"></td>
                                        <td><input type="number" name="pro_max_classes" class="form-control form-control-sm" value="{{ $settings['limits']['pro_max_classes'] ?? 5 }}"></td>
                                        <td><input type="number" name="pro_max_questions" class="form-control form-control-sm" value="{{ $settings['limits']['pro_max_questions'] ?? 500 }}"></td>
                                        <td><input type="number" name="pro_max_packages" class="form-control form-control-sm" value="{{ $settings['limits']['pro_max_packages'] ?? 999999 }}"></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">ADVANCED</span></td>
                                        <td><input type="number" name="advanced_max_students" class="form-control form-control-sm" value="{{ $settings['limits']['advanced_max_students'] ?? 999999 }}"></td>
                                        <td><input type="number" name="advanced_max_classes" class="form-control form-control-sm" value="{{ $settings['limits']['advanced_max_classes'] ?? 999999 }}"></td>
                                        <td><input type="number" name="advanced_max_questions" class="form-control form-control-sm" value="{{ $settings['limits']['advanced_max_questions'] ?? 999999 }}"></td>
                                        <td><input type="number" name="advanced_max_packages" class="form-control form-control-sm" value="{{ $settings['limits']['advanced_max_packages'] ?? 999999 }}"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-tags"></i> Harga Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">PRO Bulanan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="pro_price_monthly" class="form-control" value="{{ $settings['pricing']['pro_price_monthly'] ?? 149000 }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">PRO Tahunan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="pro_price_yearly" class="form-control" value="{{ $settings['pricing']['pro_price_yearly'] ?? 1490000 }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">ADVANCED Bulanan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="advanced_price_monthly" class="form-control" value="{{ $settings['pricing']['advanced_price_monthly'] ?? 399000 }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">ADVANCED Tahunan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="advanced_price_yearly" class="form-control" value="{{ $settings['pricing']['advanced_price_yearly'] ?? 3990000 }}">
                                </div>
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
