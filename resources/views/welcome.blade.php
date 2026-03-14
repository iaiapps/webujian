<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Platform Tes Kompetensi Akademik Digital untuk Guru dan Siswa - Buat tryout, latihan soal, dan ujian dengan mudah">

    <title>TKA - Platform Tes & Tryout Digital</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
        rel="stylesheet">

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
                <i class="bi bi-mortarboard-fill me-2"></i>Exam Web
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
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
                        Platform Tes & Tryout Digital
                    </h1>
                    <p class="hero-subtitle fade-up">
                        Buat tryout, latihan soal, dan ujian online untuk siswa Anda.
                        Gratis daftar, dapat 10 kredit langsung! Hanya Rp 4.000 per paket tes.
                    </p>
                    <div class="d-flex gap-3 flex-wrap fade-up">
                        <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                            <i class="bi bi-gift me-2"></i>Coba Gratis Sekarang
                        </a>
                        <a href="#fitur" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-play-circle me-2"></i>Lihat Fitur
                        </a>
                    </div>

                    {{-- <div class="d-flex gap-4 mt-5 fade-up">
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
                    </div> --}}
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="text-center fade-up">
                        <iframe src="{{ asset('dashboard-preview.html') }}" class="hero-iframe img-fluid"
                            style="width: 100%; max-width: 600px; height: 380px; border: none; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);"
                            title="Dashboard Preview"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Welcome Bonus Banner -->
    <section class="py-4 bg-warning">
        <div class="container text-center">
            <h3 class="mb-2">
                GRATIS 10 KREDIT SAAT DAFTAR!
            </h3>
            <p class="mb-0">Langsung dapat 10 paket tes untuk mencoba platform kami</p>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Utama</h2>
                <p>Semua yang Anda butuhkan untuk membuat tes digital dengan mudah</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <h3 class="feature-title">Bank soal Canggih</h3>
                        <p class="feature-desc">
                            Buat dan kategorisasi soal dengan mudah.
                            Support gambar, 3-5 opsi jawaban per soal.
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
                            anti-kecurangan, dan auto-simpan jawaban.
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
                            Scoring otomatis & analisis detail.
                            Lihat ranking & progress siswa secara real-time.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <h3 class="feature-title">Pembelian Instan</h3>
                        <p class="feature-desc">
                            Beli kredit via Mayar - QRIS, VA, E-wallet, CC.
                            Kredit langsung masuk setelah pembayaran.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-section">
        <div class="container">
            <div class="section-title">
                <h2>Keunggulan Kami</h2>
                <p>Mengapa ribuan guru memilih platform kami</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <i class="bi bi-gift text-primary" style="font-size: 2.5rem;"></i>
                        <h5 class="mt-3">GRATIS 10 Kredit</h5>
                        <p class="text-muted mb-0">Saat daftar langsung dapat 10 paket tes gratis!</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <i class="bi bi-infinity text-success" style="font-size: 2.5rem;"></i>
                        <h5 class="mt-3">Tidak Ada Batas</h5>
                        <p class="text-muted mb-0">1 kredit = 1 paket tes untuk unlimited siswa</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <i class="bi bi-clock-history text-info" style="font-size: 2.5rem;"></i>
                        <h5 class="mt-3">Kredit Tidak Expired</h5>
                        <p class="text-muted mb-0">Kredit tidak hangus, gunakan kapan saja</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <i class="bi bi-lightning-fill text-warning" style="font-size: 2.5rem;"></i>
                        <h5 class="mt-3">Pembayaran Instant</h5>
                        <p class="text-muted mb-0">Kredit langsung masuk via Mayar</p>
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
                <p>Mulai dari nol hingga punya tes digital dalam hitungan menit</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">1</div>
                        <h4>Daftar Gratis</h4>
                        <p class="text-muted">Buat akun guru, langsung aktif tanpa approval. Dapat 10 kredit gratis!
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">2</div>
                        <h4>Buat Paket Tes</h4>
                        <p class="text-muted">Pilih soal dari bank soal, atur durasi & jadwal, lalu kirim ke siswa.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-center">
                        <div class="step-number">3</div>
                        <h4>Lihat Hasil</h4>
                        <p class="text-muted">Scoring otomatis, analisis lengkap, dan export hasil ke Excel.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing/Credit Section -->
    <section id="harga" class="pricing-section">
        <div class="container">
            <div class="section-title">
                <h2>Paket Kredit</h2>
                <p>Harga mulai Rp 4.000 per paket tes. Bonus lebih besar untuk pembelian lebih banyak!</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Paket 1 Kredit -->
                {{-- <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <h3 class="pricing-name">Paket 1 Kredit</h3>
                        <div class="pricing-price">Rp 5rb<span></span></div>
                        <p class="pricing-period">1 Paket Tes</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>1 Paket Tes</strong></li>
                            <li><i class="bi bi-check-circle-fill"></i> Cocok untuk coba-coba</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tidak ada batas waktu</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Pilih</a>
                    </div>
                </div> --}}

                <!-- Paket 5 Kredit -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <h3 class="pricing-name">Paket 5 Kredit</h3>
                        <div class="pricing-price">Rp 25rb<span></span></div>
                        <p class="pricing-period">+1 Kredit Bonus 🔥</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>6 Paket Tes</strong></li>
                            <li><i class="bi bi-check-circle-fill"></i> Hanya Rp 4.167/kredit</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bonus langsung masuk</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tidak ada batas waktu</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Coba Sekarang</a>
                    </div>
                </div>

                <!-- Paket 10 Kredit -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card featured">
                        <span class="pricing-badge">Terpopuler!</span>
                        <h3 class="pricing-name">Paket 10 Kredit</h3>
                        <div class="pricing-price">Rp 50rb<span></span></div>
                        <p class="pricing-period">+2 Kredit Bonus 🔥</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>12 Paket Tes</strong></li>
                            <li><i class="bi bi-check-circle-fill"></i> Hanya Rp 4.167/kredit</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bonus langsung masuk</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tidak ada batas waktu</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-accent w-100">Pilih Paket 10</a>
                    </div>
                </div>

                <!-- Paket 25 Kredit -->
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <span class="pricing-badge">Best Value!</span>
                        <h3 class="pricing-name">Paket 25 Kredit</h3>
                        <div class="pricing-price">Rp 125rb<span></span></div>
                        <p class="pricing-period">+5 Kredit Bonus 🔥</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>30 Paket Tes</strong></li>
                            <li><i class="bi bi-check-circle-fill"></i> Hemat Rp 25.000</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bonus 5 kredit (20%)</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tidak ada batas waktu</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Paket Hemat</a>
                    </div>
                </div>

                <!-- Paket 50 Kredit -->
                {{-- <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <span class="pricing-badge">Big Saver!</span>
                        <h3 class="pricing-name">Paket 50 Kredit</h3>
                        <div class="pricing-price">Rp 250rb<span></span></div>
                        <p class="pricing-period">+10 Kredit Bonus 🔥</p>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>60 Paket Tes</strong></li>
                            <li><i class="bi bi-check-circle-fill"></i> Hemat Rp 50.000</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bonus 10 kredit (20%)</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tidak ada batas waktu</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Paket Besar</a>
                    </div>
                </div> --}}
            </div>

            <div class="text-center mt-5">
                <p class="text-muted mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    Pembayaran aman via Mayar
                </p>
                <p class="mb-0">
                    <strong>Metode pembayaran:</strong> QRIS • Gopay • OVO • DANA • LinkAja • VA BCA • VA BRI • VA BNI •
                    VA Mandiri • Credit Card
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="text-white mb-4" style="font-size: 2.5rem;">Mulai Buat Tryout Sekarang!</h2>
            <p class="text-white-50 mb-4" style="font-size: 1.2rem;">
                Daftar gratis dapat 10 kredit langsung. Tidak perlu kartu kredit.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                    <i class="bi bi-gift me-2"></i>Daftar Gratis Dapat 10 Kredit
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
                        <i class="bi bi-mortarboard-fill me-2"></i>Exam Web
                    </a>
                    <p>Platform Tes & Tryout Digital untuk institusi pendidikan Indonesia.</p>
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
                <p>&copy; {{ date('Y') }} Exam Web - Platform Tes & Tryout Digital. All rights reserved.</p>
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
        }, {
            threshold: 0.1
        });

        fadeElements.forEach(el => observer.observe(el));
    </script>
</body>

</html>
