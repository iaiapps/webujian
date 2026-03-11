@extends('layouts.admin')

@section('title', 'Kelola Guru')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Guru</h2>
        <a href="{{ route('admin.users.pending') }}" class="btn btn-warning">
            <i class="bi bi-hourglass-split"></i> Menunggu Approval
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, institusi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="plan" class="form-select">
                        <option value="">Semua Plan</option>
                        <option value="free" {{ request('plan') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="pro" {{ request('plan') == 'pro' ? 'selected' : '' }}>Pro</option>
                        <option value="advanced" {{ request('plan') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Institusi</th>
                            <th>Plan</th>
                            <th>Siswa</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>{{ $user->institution_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->plan == 'free' ? 'secondary' : ($user->plan == 'pro' ? 'primary' : 'success') }}">
                                    {{ strtoupper($user->plan) }}
                                </span>
                            </td>
                            <td>{{ $user->students_count ?? $user->students()->count() }}</td>
                            <td>
                                @if(!$user->approved_at)
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data guru</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
