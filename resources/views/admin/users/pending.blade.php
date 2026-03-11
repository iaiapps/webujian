@extends('layouts.admin')

@section('title', 'Guru Menunggu Approval')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Guru Menunggu Approval</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        @forelse($users as $user)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->institution_name }}</p>
                    
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-envelope"></i> {{ $user->email }}</li>
                        <li><i class="bi bi-telephone"></i> {{ $user->phone ?? '-' }}</li>
                        <li><i class="bi bi-calendar"></i> Daftar: {{ $user->created_at->format('d M Y H:i') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-lg"></i> Setujui
                            </button>
                        </form>
                        <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="flex-fill" onsubmit="return confirm('Yakin ingin menolak user ini? User akan dihapus.')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-lg"></i> Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                Tidak ada guru yang menunggu persetujuan
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
