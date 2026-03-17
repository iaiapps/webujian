<div class="sidebar {{ $class ?? '' }}" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-{{ $role === 'student' ? 'mortarboard-fill' : 'shield-fill-check' }}"></i>
        </div>
        <span class="brand-text">{{ $role === 'student' ? 'ExamWeb' : 'ExamWeb Admin' }}</span>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu</div>

            @if ($role === 'admin')
                <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i> Kelola Guru
                </a>

                <a class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                    href="{{ route('admin.categories.index') }}">
                    <i class="bi bi-tags"></i> Kategori
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}"
                    href="{{ route('admin.analytics.index') }}">
                    <i class="bi bi-bar-chart"></i> Analytics
                </a>

                <a class="sidebar-link {{ request()->routeIs('admin.credits.*') ? 'active' : '' }}"
                    href="{{ route('admin.credits.index') }}">
                    <i class="bi bi-coin"></i> Manajemen Kredit
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.credit-packages.*') ? 'active' : '' }}"
                    href="{{ route('admin.credit-packages.index') }}">
                    <i class="bi bi-box-seam"></i> Paket Kredit
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.settings.credits*') ? 'active' : '' }}"
                    href="{{ route('admin.settings.credits') }}">
                    <i class="bi bi-coin"></i> Pengaturan Kredit
                </a>
                <a class="sidebar-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"
                    href="{{ route('admin.settings.index') }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            @elseif($role === 'guru')
                <a class="sidebar-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"
                    href="{{ route('guru.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                {{-- KELAS DINONAKTIFKAN --}}
                {{-- <a class="sidebar-link {{ request()->routeIs('guru.classes.*') ? 'active' : '' }}" href="{{ route('guru.classes.index') }}">
                    <i class="bi bi-door-open"></i> Kelas
                </a> --}}
                <a class="sidebar-link {{ request()->routeIs('guru.students.*') ? 'active' : '' }}"
                    href="{{ route('guru.students.index') }}">
                    <i class="bi bi-people"></i> Siswa
                </a>
                <a class="sidebar-link {{ request()->routeIs('guru.questions.*') ? 'active' : '' }}"
                    href="{{ route('guru.questions.index') }}">
                    <i class="bi bi-question-circle"></i> Bank Soal
                </a>
                <a class="sidebar-link {{ request()->routeIs('guru.packages.*') ? 'active' : '' }}"
                    href="{{ route('guru.packages.index') }}">
                    <i class="bi bi-box"></i> Paket Tes
                </a>
                <a class="sidebar-link {{ request()->routeIs('guru.results.*') ? 'active' : '' }}"
                    href="{{ route('guru.results.index') }}">
                    <i class="bi bi-bar-chart"></i> Hasil
                </a>
                <a class="sidebar-link {{ request()->routeIs('guru.monitoring.*') ? 'active' : '' }}"
                    href="{{ route('guru.monitoring.index') }}">
                    <i class="bi bi-broadcast"></i> Monitoring
                </a>
                {{-- SISTEM KREDIT - Ganti dari Subscription --}}
                <a class="sidebar-link {{ request()->routeIs('guru.credits.*') ? 'active' : '' }}"
                    href="{{ route('guru.credits.index') }}">
                    <i class="bi bi-coin"></i> Kredit
                </a>
            @elseif($role === 'student')
                <a class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"
                    href="{{ route('student.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="sidebar-link {{ request()->routeIs('student.test.*') ? 'active' : '' }}"
                    href="{{ route('student.test.history') }}">
                    <i class="bi bi-journal-text"></i> Ujian
                </a>
            @endif
        </div>
    </nav>

    @auth('web')
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role">
                    @if ($role === 'admin')
                        Administrator
                    @elseif($role === 'guru')
                        Guru
                    @else
                        Siswa
                    @endif
                </div>
            </div>
            <button class="sidebar-user-dropdown" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-chevron-up"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if ($role === 'guru')
                    <li>
                        <a class="dropdown-item" href="{{ route('guru.profile.edit') }}">
                            <i class="bi bi-person"></i> Profile
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    @endauth

    @auth('student')
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(auth()->guard('student')->user()->name, 0, 2)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->guard('student')->user()->name }}</div>
                <div class="sidebar-user-role">Siswa</div>
            </div>
            <button class="sidebar-user-dropdown" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-chevron-up"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('student.profile.edit') }}">
                        <i class="bi bi-person"></i> Profile
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    @endauth
</div>
