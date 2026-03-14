<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'ExamWeb') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>

@php
    // Auto-detect role
    if (auth()->guard('student')->check()) {
        $role = 'student';
    } elseif (auth()->user()->hasRole('admin')) {
        $role = 'admin';
    } else {
        $role = 'guru';
    }
@endphp

<body class="{{ $role === 'student' ? 'student-layout' : '' }} {{ $bodyClass ?? '' }}">

    <div class="app-wrapper">
        @include('components.layout.sidebar', ['role' => $role])

        <div class="main-content">
            <header class="top-navbar">
                <div class="navbar-left">
                    <button class="navbar-icon-btn d-lg-none" type="button" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="navbar-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="navbar-right">
                    @if ($role === 'guru')
                        {{-- SISTEM KREDIT - Tampilkan kredit bukan plan --}}
                        <span class="navbar-plan-badge badge-orange me-3">
                            <i class="bi bi-coin me-1"></i>{{ auth()->user()->credits ?? 0 }} Kredit
                        </span>
                    @endif
                    <button class="navbar-icon-btn">
                        <i class="bi bi-bell"></i>
                    </button>
                </div>
            </header>

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.querySelector('.sidebar-overlay').classList.toggle('show');
        }
    </script>
    @stack('scripts')
</body>

</html>
