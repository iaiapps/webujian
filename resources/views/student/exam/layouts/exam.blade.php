<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Ujian') - ExamWeb</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Exam CSS -->
    <link href="{{ asset('css/exam.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>
    <div class="exam-wrapper">
        <header class="exam-header">
            <div class="exam-logo">
                <div class="logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span>ExamWeb</span>
            </div>

            <div class="exam-timer" id="timer">
                <i class="bi bi-clock"></i>
                <span>@yield('timer', '--:--')</span>
            </div>

            @hasSection('violations')
            <div class="exam-violations" title="Pelanggaran">
                <i class="bi bi-exclamation-triangle text-warning"></i>
                <span class="badge bg-warning" id="violation-badge">@yield('violation-count', '0/3')</span>
            </div>
            @endif

            @hasSection('header-actions')
            <div class="exam-actions">
                @yield('header-actions')
            </div>
            @endif
        </header>

        <main class="exam-main">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Exam System Modules -->
    <script src="{{ asset('js/exam/localStorage.js') }}"></script>
    <script src="{{ asset('js/exam/examState.js') }}"></script>
    <script src="{{ asset('js/exam/syncManager.js') }}"></script>
    <script src="{{ asset('js/exam/restore.js') }}"></script>
    <script src="{{ asset('js/exam/question-loader.js') }}"></script>
    <script src="{{ asset('js/exam/lazy-image.js') }}"></script>
    <script src="{{ asset('js/exam/index.js') }}"></script>
    
    <!-- Global Loading Component Script -->
    <script>
        /**
         * Global Loading Component
         * Fungsi untuk menampilkan loading modal yang reusable
         * 
         * Usage:
         * window.LoadingComponent.show({
         *     title: 'Menyiapkan Ujian...',
         *     message: 'Mohon tunggu',
         *     estimatedSeconds: 15,
         *     type: 'primary' // 'primary', 'success', 'warning', 'danger'
         * });
         * 
         * window.LoadingComponent.hide();
         */
        window.LoadingComponent = {
            modal: null,
            progressInterval: null,
            
            show: function(options) {
                const defaults = {
                    title: 'Loading...',
                    message: 'Mohon tunggu',
                    estimatedSeconds: 10,
                    type: 'primary',
                    backdrop: 'static',
                    keyboard: false
                };
                
                const config = { ...defaults, ...options };
                
                // Create modal element if not exists
                let modalEl = document.getElementById('globalLoadingModal');
                if (!modalEl) {
                    modalEl = this.createModalElement();
                    document.body.appendChild(modalEl);
                }
                
                // Update content
                const titleEl = modalEl.querySelector('.loading-title');
                const messageEl = modalEl.querySelector('.loading-message');
                const textEl = modalEl.querySelector('.loading-text');
                const progressBar = modalEl.querySelector('.loading-progress');
                const spinner = modalEl.querySelector('.loading-spinner');
                
                if (titleEl) titleEl.textContent = config.title;
                if (messageEl) messageEl.textContent = config.message;
                if (textEl) textEl.textContent = `Estimasi: ${config.estimatedSeconds} detik`;
                
                // Update colors based on type
                const typeColors = {
                    primary: 'text-primary',
                    success: 'text-success',
                    warning: 'text-warning',
                    danger: 'text-danger'
                };
                const bgColors = {
                    primary: 'bg-primary',
                    success: 'bg-success',
                    warning: 'bg-warning',
                    danger: 'bg-danger'
                };
                
                // Reset classes
                spinner.className = 'spinner-border loading-spinner';
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated loading-progress';
                textEl.className = 'fw-bold loading-text';
                
                // Add type classes
                spinner.classList.add(typeColors[config.type] || typeColors.primary);
                progressBar.classList.add(bgColors[config.type] || bgColors.primary);
                textEl.classList.add(typeColors[config.type] || typeColors.primary);
                
                // Show modal
                this.modal = new bootstrap.Modal(modalEl, {
                    backdrop: config.backdrop,
                    keyboard: config.keyboard
                });
                this.modal.show();
                
                // Animate progress
                this.animateProgress(config.estimatedSeconds);
                
                return this;
            },
            
            hide: function() {
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
                
                if (this.modal) {
                    this.modal.hide();
                    this.modal = null;
                }
            },
            
            animateProgress: function(estimatedSeconds) {
                const progressBar = document.querySelector('.loading-progress');
                if (!progressBar) return;
                
                let progress = 0;
                const increment = 100 / (estimatedSeconds * 10); // Update tiap 100ms
                
                progressBar.style.width = '0%';
                
                this.progressInterval = setInterval(() => {
                    progress += increment;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                    if (progressBar) {
                        progressBar.style.width = `${progress}%`;
                    }
                }, 100);
            },
            
            createModalElement: function() {
                const modal = document.createElement('div');
                modal.id = 'globalLoadingModal';
                modal.className = 'modal fade';
                modal.setAttribute('tabindex', '-1');
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-5">
                                <div class="mb-4">
                                    <div class="spinner-border loading-spinner text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <h4 class="mb-3 loading-title">Loading...</h4>
                                <p class="text-muted mb-4 loading-message">Mohon tunggu</p>
                                
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated loading-progress bg-primary" 
                                         style="width: 0%"></div>
                                </div>
                                
                                <p class="loading-text text-primary fw-bold">Estimasi: 10 detik</p>
                                <p class="text-muted small mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Mohon tidak menutup atau me-refresh halaman ini
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                return modal;
            }
        };
        
        // Prevent accidental refresh when loading is active
        window.addEventListener('beforeunload', function(e) {
            const modal = document.getElementById('globalLoadingModal');
            if (modal && modal.classList.contains('show')) {
                e.preventDefault();
                e.returnValue = 'Sedang memproses. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
    </script>
    
    @stack('scripts')
</body>

</html>
