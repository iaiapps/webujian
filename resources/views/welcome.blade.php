<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="TKA - Platform Tes Kompetensi Akademik Digital untuk Guru dan Siswa">
    
    <title>TKA - Platform Tes Kompetensi Akademik</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS -->
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-mortarboard-fill me-2"></i>TKA
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#harga">Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('student.login') }}">Login Siswa</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link nav-link-cta" href="{{ route('login') }}">Login Guru</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title fade-up">
                        Platform Tes Kompetensi Akademik
                    </h1>
                    <p class="hero-subtitle fade-up">
                        Buat soal, kelola ujian, dan analisis hasil dengan mudah. 
                        Hemat waktu dan berikan pengalaman belajar terbaik untuk siswa Anda.
                    </p>
                    <div class="d-flex gap-3 flex-wrap fade-up">
                        <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                            <i class="bi bi-rocket-takeoff me-2"></i>Coba Gratis
                        </a>
                        <a href="#fitur" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-play-circle me-2"></i>Lihat Fitur
                        </a>
                    </div>
                    
                    <div class="d-flex gap-4 mt-5 fade-up">
                        <div class="stat-card">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Guru Aktif</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Siswa</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">5000+</div>
                            <div class="stat-label">Tes Dikerjakan</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="text-center fade-up">
                        <img src="https://placehold.co/600x400/1e3a5f/white?text=Dashboard+TKA" 
                             alt="TKA Dashboard" 
                             class="hero-image img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Utama</h2>
                <p>Semua yang Anda butuhkan untuk mengelola ujian digital dengan efisien</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <h3 class="feature-title">Bank Soal Canggih</h3>
                        <p class="feature-desc">
                            Buat dan kategorisasi soal dengan mudah. 
                            Support gambar untuk soal dan opsi jawaban.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <h3 class="feature-title">Tes Digital</h3>
                        <p class="feature-desc">
                            Ujian online dengan timer real-time, 
                            auto-save jawaban, dan auto-submit.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3 class="feature-title">Hasil Instan</h3>
                        <p class="feature-desc">
                            Scoring otomatis dengan analisis detail. 
                            Lihat ranking dan progress siswa.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <h3 class="feature-title">Multi-Platform</h3>
                        <p class="feature-desc">
                            Akses dari desktop maupun mobile. 
                            Tampilan responsif di semua устройствах.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="steps-section">
        <div class="container">
            <div class="section-title">
                <h2>Cara Kerja</h2>
                <p>Mulai dari nol hingga punya ujian digital dalam hitungan menit</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">1</div>
                        <h4>Daftar & Aktifasi</h4>
                        <p class="text-muted">Buat akun guru, tunggu persetujuan admin, dan mulai gunakan platform.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">2</div>
                        <h4>Buat Konten</h4>
                        <p class="text-muted">Tambahkan siswa, buat kelas, dan kumpulkan soal di bank soal.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">3</div>
                        <h3>Buat Paket Tes</h3>
                        <p class="text-muted">Pilih soal, atur jadwal dan durasi, lalu发给 siswa.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="harga" class="pricing-section">
        <div class="container">
            <div class="section-title">
                <h2>Pilih Paket Anda</h2>
                <p>Mulai gratis, upgrade kapan saja sesuai kebutuhan</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <!-- Free Plan -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <h3 class="pricing-name">Free</h3>
                        <div class="pricing-price">Rp 0<span>/bln</span></div>
                        <p class="pricing-period">Untuk memulai</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> 30 Siswa</li>
                            <li><i class="bi bi-check-circle-fill"></i> 1 Kelas</li>
                            <li><i class="bi bi-check-circle-fill"></i> 50 Soal</li>
                            <li><i class="bi bi-check-circle-fill"></i> 2 Paket Tes</li>
                            <li><i class="bi bi-check-circle-fill"></i> Basic Analytics</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Daftar Gratis</a>
                    </div>
                </div>
                
                <!-- Pro Plan -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card featured">
                        <span class="pricing-badge">Terpopuler</span>
                        <h3 class="pricing-name">Pro</h3>
                        <div class="pricing-price">Rp 49rb<span>/bln</span></div>
                        <p class="pricing-period">atau Rp 490rb/tahun</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> 60 Siswa</li>
                            <li><i class="bi bi-check-circle-fill"></i> 3 Kelas</li>
                            <li><i class="bi bi-check-circle-fill"></i> 100 Soal</li>
                            <li><i class="bi bi-check-circle-fill"></i> 4 Paket Tes</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced Analytics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Export Hasil Excel</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-accent w-100">Pilih Pro</a>
                    </div>
                </div>
                
                <!-- Advanced Plan -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <h3 class="pricing-name">Advanced</h3>
                        <div class="pricing-price">Rp 99rb<span>/bln</span></div>
                        <p class="pricing-period">atau Rp 990rb/tahun</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> 120 Siswa</li>
                            <li><i class="bi bi-check-circle-fill"></i> 6 Kelas</li>
                            <li><i class="bi bi-check-circle-fill"></i> 200 Soal</li>
                            <li><i class="bi bi-check-circle-fill"></i> 8 Paket Tes</li>
                            <li><i class="bi bi-check-circle-fill"></i> Full Analytics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Priority Support</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Pilih Advanced</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="text-white mb-4" style="font-size: 2.5rem;">Siap Mengubah Cara Mengajar?</h2>
            <p class="text-white-50 mb-4" style="font-size: 1.2rem;">
                Bergabunglah dengan ratusan guru yang sudah menggunakan TKA
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                    <i class="bi bi-rocket-takeoff me-2"></i>Mulai Gratis Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a href="/" class="footer-brand">
                        <i class="bi bi-mortarboard-fill me-2"></i>TKA
                    </a>
                    <p>Platform Tes Kompetensi Akademik Digital untuk institusi pendidikan Indonesia.</p>
                </div>
                
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Produk</h5>
                    <ul class="footer-links">
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#harga">Harga</a></li>
                        <li><a href="{{ route('login') }}">Login</a></li>
                    </ul>
                </div>
                
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Perusahaan</h5>
                    <ul class="footer-links">
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Kontak</a></li>
                        <li><a href="#">Karir</a></li>
                    </ul>
                </div>
                
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Legal</h5>
                    <ul class="footer-links">
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat Layanan</a></li>
                    </ul>
                </div>
                
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#">Bantuan</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} TKA - Platform Tes Kompetensi Akademik. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animation Script -->
    <script>
        // Simple fade-up animation on scroll
        const fadeElements = document.querySelectorAll('.fade-up');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(el => observer.observe(el));
    </script>
</body>

</html>