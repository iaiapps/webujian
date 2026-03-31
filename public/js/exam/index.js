/**
 * Exam System Main Loader
 * Menggabungkan dan menginisialisasi semua module exam
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Check if dependencies are loaded
    if (typeof window.LocalStorage === 'undefined') {
        console.error('LocalStorage module not loaded');
        return;
    }

    if (typeof window.ExamState === 'undefined') {
        console.error('ExamState module not loaded');
        return;
    }

    if (typeof window.SyncManager === 'undefined') {
        console.error('SyncManager module not loaded');
        return;
    }

    if (typeof window.ExamRestore === 'undefined') {
        console.error('ExamRestore module not loaded');
        return;
    }

    /**
     * ExamSystem - Main controller
     */
    const ExamSystem = {
        initialized: false,
        
        // Configuration
        config: {
            autoSaveInterval: 30000,
            syncInterval: 30000,
            keepAliveInterval: 300000 // 5 menit
        },

        /**
         * Initialize exam system
         * @param {object} options 
         */
        init: function(options = {}) {
            if (this.initialized) {
                console.warn('ExamSystem already initialized');
                return;
            }

            Object.assign(this.config, options);

            console.log('ExamSystem initializing...');

            // 1. Initialize LocalStorage
            if (!LocalStorage.isAvailable()) {
                console.error('localStorage not available, falling back to session-only mode');
                this.showStorageWarning();
            }

            // 2. Initialize SyncManager
            SyncManager.init({
                autoSyncInterval: this.config.syncInterval
            });

            // 3. Set up event listeners
            this.setupEventListeners();

            // 4. Start keep-alive
            this.startKeepAlive();

            this.initialized = true;
            console.log('ExamSystem initialized successfully');

            return this;
        },

        /**
         * Initialize exam session
         * @param {object} attemptData 
         */
        initSession: function(attemptData) {
            if (!this.initialized) {
                console.error('ExamSystem not initialized');
                return;
            }

            console.log('Initializing exam session:', attemptData.attemptId);

            // Initialize ExamState
            ExamState.init(attemptData);

            // Start auto-save
            ExamState.startAutoSave();

            // Sync event listener
            ExamState.on('answerSaved', (data) => {
                SyncManager.enqueue(
                    data.questionId,
                    data.answer,
                    data.isDoubt,
                    attemptData.attemptId
                );
            });

            // Restore answers to UI
            this.restoreAnswersToUI();

            console.log('Exam session initialized');
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            // Listen for online/offline
            window.addEventListener('online', () => {
                console.log('Connection restored');
                this.showNotification('Koneksi kembali. Menyinkronkan data...', 'success');
            });

            window.addEventListener('offline', () => {
                console.log('Connection lost');
                this.showNotification('Mode offline - jawaban tersimpan lokal', 'warning');
            });

            // Listen for visibility change (tab switch)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    // Force sync when tab becomes visible
                    if (SyncManager.queue.length > 0) {
                        SyncManager.forceSync();
                    }
                }
            });

            // Before unload
            window.addEventListener('beforeunload', (e) => {
                if (ExamState.currentAttempt) {
                    // Final save
                    ExamState.saveToStorage();
                    
                    // Warn if unsynced data
                    const unsynced = ExamState.getUnsyncedQuestions();
                    if (unsynced.length > 0) {
                        e.preventDefault();
                        e.returnValue = 'Ada jawaban yang belum tersimpan. Yakin ingin meninggalkan halaman?';
                    }
                }
            });
        },

        /**
         * Start keep-alive for session
         */
        startKeepAlive: function() {
            setInterval(() => {
                fetch('/api/keep-alive', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                }).catch(e => {
                    // Silent fail
                });
            }, this.config.keepAliveInterval);
        },

        /**
         * Save answer
         * @param {number} questionId 
         * @param {string} answer 
         * @param {boolean} isDoubt 
         */
        saveAnswer: function(questionId, answer, isDoubt = false) {
            if (!ExamState.currentAttempt) {
                console.error('No active exam session');
                return false;
            }

            // Save to local state
            ExamState.saveAnswer(questionId, answer, isDoubt);

            return true;
        },

        /**
         * Get answer
         * @param {number} questionId 
         * @returns {object|null}
         */
        getAnswer: function(questionId) {
            return ExamState.getAnswer(questionId);
        },

        /**
         * Mark doubt
         * @param {number} questionId 
         * @param {boolean} isDoubt 
         */
        markDoubt: function(questionId, isDoubt) {
            return ExamState.markDoubt(questionId, isDoubt);
        },

        /**
         * Restore answers to UI
         */
        restoreAnswersToUI: function() {
            const answers = ExamState.getAllAnswers();
            
            Object.entries(answers).forEach(([questionId, data]) => {
                const questionEl = document.querySelector(`[data-question-id="${questionId}"]`);
                if (questionEl) {
                    this.restoreAnswerToQuestion(questionEl, data);
                }
            });
        },

        /**
         * Restore answer to specific question element
         * @param {HTMLElement} questionEl 
         * @param {object} data 
         */
        restoreAnswerToQuestion: function(questionEl, data) {
            const questionId = questionEl.dataset.questionId;
            
            // Detect question type
            const categoryInputs = questionEl.querySelectorAll(`input[name^="answer_${questionId}_"]`);
            const singleInput = questionEl.querySelector(`input[name="answer_${questionId}"]`);
            const complexInputs = questionEl.querySelectorAll(`input[name="answer_${questionId}[]"]`);
            
            if (categoryInputs.length > 0 && data.answer) {
                // Category type
                const pairs = data.answer.split(',');
                pairs.forEach(pair => {
                    const [opt, val] = pair.split(':');
                    if (opt && val) {
                        const radio = questionEl.querySelector(`input[name="answer_${questionId}_${opt}"][value="${val}"]`);
                        if (radio) radio.checked = true;
                    }
                });
            } else if (singleInput && data.answer) {
                // Single choice
                const radio = questionEl.querySelector(`input[name="answer_${questionId}"][value="${data.answer}"]`);
                if (radio) radio.checked = true;
            } else if (complexInputs.length > 0 && data.answer) {
                // Complex choice
                const values = data.answer.split(',');
                values.forEach(val => {
                    const checkbox = questionEl.querySelector(`input[name="answer_${questionId}[]"][value="${val}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            // Restore doubt status
            if (data.isDoubt) {
                const doubtCheckbox = document.getElementById(`doubt_${questionId}`);
                if (doubtCheckbox) {
                    doubtCheckbox.checked = true;
                }
            }
        },

        /**
         * Show notification
         * @param {string} message 
         * @param {string} type 
         * @param {number} duration 
         */
        showNotification: function(message, type = 'info', duration = 5000) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    notification.remove();
                }, duration);
            }
            
            // Bootstrap alert
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Alert(notification);
            }
        },

        /**
         * Show storage warning
         */
        showStorageWarning: function() {
            this.showNotification(
                'Browser Anda tidak support penyimpanan lokal. Jawaban mungkin hilang jika koneksi terputus.',
                'warning',
                10000
            );
        },

        /**
         * Get sync status
         * @returns {object}
         */
        getSyncStatus: function() {
            return {
                ...SyncManager.getStatus(),
                unsyncedCount: ExamState.getUnsyncedQuestions().length
            };
        },

        /**
         * Force sync
         * @returns {Promise}
         */
        forceSync: function() {
            return SyncManager.forceSync();
        },

        /**
         * Submit exam
         * @returns {Promise}
         */
        submit: async function() {
            // Final sync
            await SyncManager.forceSync();
            
            // Clear exam state
            ExamState.clear();
            
            return true;
        }
    };

    // Export
    window.ExamSystem = ExamSystem;

    // Auto-initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        ExamSystem.init();
    });

    console.log('Exam modules loaded: LocalStorage, ExamState, SyncManager, ExamRestore');
})();