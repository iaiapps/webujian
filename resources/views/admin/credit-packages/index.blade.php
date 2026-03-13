@extends('layouts.dashboard')

@section('title', 'Paket Kredit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Paket Kredit</h2>
        <a href="{{ route('admin.credit-packages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Paket
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Nama</th>
                            <th>Kredit</th>
                            <th>Bonus</th>
                            <th>Total</th>
                            <th>Harga</th>
                            <th>Harga/Kredit</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                        <tr>
                            <td>{{ $package->sort_order }}</td>
                            <td>
                                <strong>{{ $package->name }}</strong>
                                @if($package->description)
                                <br><small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $package->credit_amount }}</td>
                            <td>
                                @if($package->bonus_credits > 0)
                                    <span class="badge bg-success">+{{ $package->bonus_credits }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ $package->getTotalCredits() }}</strong></td>
                            <td>{{ $package->getFormattedPrice() }}</td>
                            <td>{{ number_format($package->getPricePerCredit(), 0, ',', '.') }}</td>
                            <td>
                                @if($package->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.credit-packages.toggle-status', $package) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $package->is_active ? 'warning' : 'success' }}" title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $package->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.credit-packages.edit', $package) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.credit-packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada paket kredit
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
