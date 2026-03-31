/**
 * ExamRestore Module
 * Mengelola restore data ujian saat browser refresh/crash
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const ExamRestore = {
    // Configuration
    config: {
        restoreModalId: 'restoreModal',
        autoRestoreDelay: 3000, // 3 detik untuk auto-restore
        maxInactiveTime: 30 * 60 * 1000 // 30 menit
    },
    
    // State
    hasChecked: false,
    restoreData: null,

    /**
     * Check for existing exam on page load
     * @returns {object|null}
     */
    checkExistingExam: function() {
        if (this.hasChecked) return this.restoreData;
        
        this.hasChecked = true;
        
        // Cek menggunakan ExamState
        if (window.ExamState) {
            const existing = window.ExamState.checkExistingAttempt();
            
            if (existing) {
                // Cek apakah masih aktif (tidak expired)
                if (!this.isExpired(existing)) {
                    this.restoreData = existing;
                    return existing;
                } else {
                    // Hapus data yang expired
                    this.clearExpiredData(existing.attemptId);
                }
            }
        }
        
        return null;
    },

    /**
     * Check if exam is expired
     * @param {object} data 
     * @returns {boolean}
     */
    isExpired: function(data) {
        if (!data || !data.startTime) return true;
        
        const startTime = new Date(data.startTime);
        const duration = data.timeRemaining || 0;
        const endTime = new Date(startTime.getTime() + duration * 1000);
        
        return Date.now() > endTime.getTime();
    },

    /**
     * Check if exam is inactive (no activity for long time)
     * @param {object} data 
     * @returns {boolean}
     */
    isInactive: function(data) {
        if (!data || !data.lastActivity) return true;
        
        const inactiveTime = Date.now() - data.lastActivity;
        return inactiveTime > this.config.maxInactiveTime;
    },

    /**
     * Clear expired data
     * @param {number} attemptId 
     */
    clearExpiredData: function(attemptId) {
        if (window.LocalStorage) {
            window.LocalStorage.remove(`attempt_${attemptId}`);
            
            // Clear backups juga
            for (let i = 0; i < 5; i++) {
                window.LocalStorage.remove(`attempt_${attemptId}_backup_${i}`);
            }
        }
    },

    /**
     * Show restore modal
     * @param {object} data 
     * @param {function} onRestore 
     * @param {function} onDiscard 
     */
    showRestoreModal: function(data, onRestore, onDiscard) {
        const answeredCount = Object.keys(data.answers || {}).length;
        const currentQuestion = data.currentQuestion || 1;
        const timeRemaining = this.formatTime(data.timeRemaining || 0);
        
        // Create modal HTML
        const modalHtml = `
            <div id="${this.config.restoreModalId}" class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>
                                Lanjutkan Ujian?
                            </h5>
                            <button type="button" class="btn-close btn-close-white" 
                                    onclick="ExamRestore.closeModal()"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                Kami mendeteksi ada ujian yang belum selesai:
                            </p>
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-check-circle me-2"></i>Dijawab:</span>
                                    <strong>${answeredCount} soal</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-file-text me-2"></i>Soal saat ini:</span>
                                    <strong>No. ${currentQuestion}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-clock me-2"></i>Sisa waktu:</span>
                                    <strong>${timeRemaining}</strong>
                                </div>
                            </div>
                            <p class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Jawaban Anda tersimpan di browser ini
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="ExamRestore.handleDiscard()">
                                <i class="bi bi-x-circle me-2"></i>Batalkan
                            </button>
                            <button type="button" class="btn btn-primary" 
                                    onclick="ExamRestore.handleRestore()">
                                <i class="bi bi-play-circle me-2"></i>Lanjutkan Ujian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Append modal to body
        const existingModal = document.getElementById(this.config.restoreModalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = modalHtml;
        document.body.appendChild(wrapper.firstElementChild);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById(this.config.restoreModalId));
        modal.show();
        
        // Store callbacks
        this._onRestore = onRestore;
        this._onDiscard = onDiscard;
    },

    /**
     * Close modal
     */
    closeModal: function() {
        const modalEl = document.getElementById(this.config.restoreModalId);
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
            modalEl.remove();
        }
    },

    /**
     * Handle restore action
     */
    handleRestore: function() {
        this.closeModal();
        
        if (this._onRestore && this.restoreData) {
            this._onRestore(this.restoreData);
        }
    },

    /**
     * Handle discard action
     */
    handleDiscard: function() {
        if (this.restoreData) {
            this.clearExpiredData(this.restoreData.attemptId);
        }
        
        this.closeModal();
        
        if (this._onDiscard) {
            this._onDiscard();
        }
    },

    /**
     * Auto-restore dengan countdown
     * @param {object} data 
     * @param {function} onRestore 
     * @param {function} onCancel 
     */
    autoRestore: function(data, onRestore, onCancel) {
        let countdown = Math.floor(this.config.autoRestoreDelay / 1000);
        
        const answeredCount = Object.keys(data.answers || {}).length;
        
        const modalHtml = `
            <div id="${this.config.restoreModalId}" class="modal fade" data-bs-backdrop="static" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>
                                Melanjutkan Ujian...
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            </div>
                            <p class="mb-2">
                                Ujian sebelumnya ditemukan (${answeredCount} soal dijawab)
                            </p>
                            <p class="text-muted">
                                Melanjutkan dalam <span id="restoreCountdown" class="fw-bold text-primary">${countdown}</span> detik
                            </p>
                            <div class="progress mt-3" style="height: 6px;">
                                <div id="restoreProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                                     style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="ExamRestore.cancelAutoRestore()">
                                <i class="bi bi-x-circle me-2"></i>Batalkan
                            </button>
                            <button type="button" class="btn btn-primary" 
                                    onclick="ExamRestore.immediateRestore()">
                                <i class="bi bi-play-circle me-2"></i>Lanjutkan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Append modal
        const existingModal = document.getElementById(this.config.restoreModalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = modalHtml;
        document.body.appendChild(wrapper.firstElementChild);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById(this.config.restoreModalId));
        modal.show();
        
        // Store callbacks
        this._onRestore = onRestore;
        this._onCancel = onCancel;
        
        // Animate countdown
        const countdownEl = document.getElementById('restoreCountdown');
        const progressEl = document.getElementById('restoreProgress');
        
        this._autoRestoreInterval = setInterval(() => {
            countdown--;
            
            if (countdownEl) countdownEl.textContent = countdown;
            if (progressEl) {
                const progress = ((this.config.autoRestoreDelay / 1000 - countdown) / (this.config.autoRestoreDelay / 1000)) * 100;
                progressEl.style.width = `${progress}%`;
            }
            
            if (countdown <= 0) {
                this.immediateRestore();
            }
        }, 1000);
        
        // Auto-restore after delay
        this._autoRestoreTimeout = setTimeout(() => {
            this.immediateRestore();
        }, this.config.autoRestoreDelay);
    },

    /**
     * Cancel auto-restore
     */
    cancelAutoRestore: function() {
        this.clearAutoRestoreTimers();
        this.closeModal();
        
        // Clear data
        if (this.restoreData) {
            this.clearExpiredData(this.restoreData.attemptId);
        }
        
        if (this._onCancel) {
            this._onCancel();
        }
    },

    /**
     * Immediate restore
     */
    immediateRestore: function() {
        this.clearAutoRestoreTimers();
        this.closeModal();
        
        if (this._onRestore && this.restoreData) {
            this._onRestore(this.restoreData);
        }
    },

    /**
     * Clear auto-restore timers
     */
    clearAutoRestoreTimers: function() {
        if (this._autoRestoreInterval) {
            clearInterval(this._autoRestoreInterval);
            this._autoRestoreInterval = null;
        }
        
        if (this._autoRestoreTimeout) {
            clearTimeout(this._autoRestoreTimeout);
            this._autoRestoreTimeout = null;
        }
    },

    /**
     * Format time in seconds to readable string
     * @param {number} seconds 
     * @returns {string}
     */
    formatTime: function(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}j ${minutes}m ${secs}d`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}d`;
        } else {
            return `${secs}d`;
        }
    },

    /**
     * Initialize restore check
     * @param {object} options 
     */
    init: function(options = {}) {
        Object.assign(this.config, options);
        
        // Check for existing exam
        const existing = this.checkExistingExam();
        
        if (existing) {
            // Show restore options
            if (options.autoRestore) {
                this.autoRestore(existing, options.onRestore, options.onCancel);
            } else {
                this.showRestoreModal(existing, options.onRestore, options.onDiscard);
            }
            
            return true;
        }
        
        return false;
    },

    /**
     * Get restore data
     * @returns {object|null}
     */
    getRestoreData: function() {
        return this.restoreData;
    },

    /**
     * Clear all exam data
     */
    clearAll: function() {
        if (window.LocalStorage) {
            const keys = window.LocalStorage.keys('attempt_');
            keys.forEach(key => {
                window.LocalStorage.remove(key);
            });
        }
        
        this.restoreData = null;
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamRestore;
}

window.ExamRestore = ExamRestore;