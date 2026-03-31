/**
 * ExamState Module
 * Mengelola state ujian dengan struktur data terstandarisasi
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const ExamState = {
    // Current attempt data
    currentAttempt: null,
    
    // Configuration
    config: {
        autoSaveInterval: 30000, // 30 detik
        maxBackupCount: 5 // Simpan 5 backup terakhir
    },

    /**
     * Initialize new exam attempt
     * @param {object} attemptData 
     * @returns {object}
     */
    init: function(attemptData) {
        const state = {
            attemptId: attemptData.attemptId,
            packageId: attemptData.packageId,
            studentId: attemptData.studentId,
            startTime: new Date().toISOString(),
            lastActivity: Date.now(),
            currentQuestion: 1,
            timeRemaining: attemptData.duration * 60, // dalam detik
            answers: {},
            doubtQuestions: [],
            syncStatus: {},
            version: 1
        };

        this.currentAttempt = state;
        this.saveToStorage();
        
        // Start auto-save
        this.startAutoSave();
        
        return state;
    },

    /**
     * Get storage key for current attempt
     * @returns {string}
     */
    getStorageKey: function() {
        if (!this.currentAttempt) return null;
        return `attempt_${this.currentAttempt.attemptId}`;
    },

    /**
     * Save current state to localStorage
     * @returns {boolean}
     */
    saveToStorage: function() {
        if (!this.currentAttempt) return false;
        
        const key = this.getStorageKey();
        const data = {
            ...this.currentAttempt,
            lastSaved: Date.now()
        };

        // Simpan backup sebelumnya
        this.rotateBackups();
        
        const result = LocalStorage.set(key, data, { expiry: 2 }); // 2 hari expiry
        
        if (result) {
            this.emitEvent('stateSaved', data);
        }
        
        return result;
    },

    /**
     * Load state from localStorage
     * @param {number} attemptId 
     * @returns {object|null}
     */
    loadFromStorage: function(attemptId) {
        const key = `attempt_${attemptId}`;
        const data = LocalStorage.get(key);
        
        if (data) {
            this.currentAttempt = data;
            this.emitEvent('stateLoaded', data);
            return data;
        }
        
        return null;
    },

    /**
     * Rotate backups (keep last N versions)
     */
    rotateBackups: function() {
        if (!this.currentAttempt) return;
        
        const baseKey = this.getStorageKey();
        const backupKey = `${baseKey}_backup_`;
        
        // Shift backups
        for (let i = this.config.maxBackupCount - 1; i > 0; i--) {
            const oldData = LocalStorage.get(`${backupKey}${i - 1}`);
            if (oldData) {
                LocalStorage.set(`${backupKey}${i}`, oldData);
            }
        }
        
        // Save current as backup 0
        if (this.currentAttempt) {
            LocalStorage.set(`${backupKey}0`, this.currentAttempt);
        }
    },

    /**
     * Restore from backup
     * @param {number} backupIndex 
     * @returns {object|null}
     */
    restoreFromBackup: function(backupIndex = 0) {
        if (!this.currentAttempt) return null;
        
        const baseKey = this.getStorageKey();
        const backupKey = `${baseKey}_backup_${backupIndex}`;
        const data = LocalStorage.get(backupKey);
        
        if (data) {
            this.currentAttempt = data;
            this.saveToStorage();
            this.emitEvent('stateRestored', data);
            return data;
        }
        
        return null;
    },

    /**
     * Save answer for a question
     * @param {number} questionId 
     * @param {string} answer 
     * @param {boolean} isDoubt 
     * @returns {boolean}
     */
    saveAnswer: function(questionId, answer, isDoubt = false) {
        if (!this.currentAttempt) return false;
        
        this.currentAttempt.answers[questionId] = {
            answer: answer,
            isDoubt: isDoubt,
            timestamp: Date.now(),
            synced: false
        };
        
        this.currentAttempt.lastActivity = Date.now();
        
        // Update sync status
        this.currentAttempt.syncStatus[questionId] = 'pending';
        
        // Auto-save
        this.debouncedSave();
        
        this.emitEvent('answerSaved', { questionId, answer, isDoubt });
        
        return true;
    },

    /**
     * Get answer for a question
     * @param {number} questionId 
     * @returns {object|null}
     */
    getAnswer: function(questionId) {
        if (!this.currentAttempt) return null;
        return this.currentAttempt.answers[questionId] || null;
    },

    /**
     * Get all answers
     * @returns {object}
     */
    getAllAnswers: function() {
        if (!this.currentAttempt) return {};
        return this.currentAttempt.answers;
    },

    /**
     * Mark question as doubt
     * @param {number} questionId 
     * @param {boolean} isDoubt 
     * @returns {boolean}
     */
    markDoubt: function(questionId, isDoubt) {
        if (!this.currentAttempt) return false;
        
        const doubtIndex = this.currentAttempt.doubtQuestions.indexOf(questionId);
        
        if (isDoubt && doubtIndex === -1) {
            this.currentAttempt.doubtQuestions.push(questionId);
        } else if (!isDoubt && doubtIndex > -1) {
            this.currentAttempt.doubtQuestions.splice(doubtIndex, 1);
        }
        
        // Update answer if exists
        if (this.currentAttempt.answers[questionId]) {
            this.currentAttempt.answers[questionId].isDoubt = isDoubt;
        }
        
        this.currentAttempt.lastActivity = Date.now();
        this.debouncedSave();
        
        this.emitEvent('doubtChanged', { questionId, isDoubt });
        
        return true;
    },

    /**
     * Check if question is marked as doubt
     * @param {number} questionId 
     * @returns {boolean}
     */
    isDoubt: function(questionId) {
        if (!this.currentAttempt) return false;
        return this.currentAttempt.doubtQuestions.includes(questionId);
    },

    /**
     * Update sync status for a question
     * @param {number} questionId 
     * @param {string} status 
     */
    updateSyncStatus: function(questionId, status) {
        if (!this.currentAttempt) return;
        
        this.currentAttempt.syncStatus[questionId] = status;
        
        if (this.currentAttempt.answers[questionId]) {
            this.currentAttempt.answers[questionId].synced = (status === 'synced');
        }
        
        this.saveToStorage();
    },

    /**
     * Get questions that need syncing
     * @returns {Array}
     */
    getUnsyncedQuestions: function() {
        if (!this.currentAttempt) return [];
        
        return Object.keys(this.currentAttempt.answers).filter(questionId => {
            return this.currentAttempt.syncStatus[questionId] !== 'synced';
        });
    },

    /**
     * Set current question number
     * @param {number} questionNumber 
     */
    setCurrentQuestion: function(questionNumber) {
        if (!this.currentAttempt) return;
        this.currentAttempt.currentQuestion = questionNumber;
        this.currentAttempt.lastActivity = Date.now();
        this.debouncedSave();
    },

    /**
     * Get current question number
     * @returns {number}
     */
    getCurrentQuestion: function() {
        if (!this.currentAttempt) return 1;
        return this.currentAttempt.currentQuestion;
    },

    /**
     * Update time remaining
     * @param {number} seconds 
     */
    updateTimeRemaining: function(seconds) {
        if (!this.currentAttempt) return;
        this.currentAttempt.timeRemaining = seconds;
        this.debouncedSave();
    },

    /**
     * Get time remaining
     * @returns {number}
     */
    getTimeRemaining: function() {
        if (!this.currentAttempt) return 0;
        return this.currentAttempt.timeRemaining;
    },

    /**
     * Get exam statistics
     * @returns {object}
     */
    getStats: function() {
        if (!this.currentAttempt) return null;
        
        const answers = this.currentAttempt.answers;
        const totalAnswered = Object.keys(answers).length;
        const totalDoubt = this.currentAttempt.doubtQuestions.length;
        
        return {
            totalAnswered: totalAnswered,
            totalDoubt: totalDoubt,
            lastActivity: this.currentAttempt.lastActivity,
            timeRemaining: this.currentAttempt.timeRemaining
        };
    },

    /**
     * Check if exam is expired
     * @returns {boolean}
     */
    isExpired: function() {
        if (!this.currentAttempt) return false;
        
        const startTime = new Date(this.currentAttempt.startTime);
        const duration = this.currentAttempt.timeRemaining;
        const endTime = new Date(startTime.getTime() + duration * 1000);
        
        return Date.now() > endTime.getTime();
    },

    /**
     * Clear current attempt
     * @param {boolean} keepBackup 
     */
    clear: function(keepBackup = false) {
        if (!this.currentAttempt) return;
        
        const key = this.getStorageKey();
        
        // Remove from localStorage
        LocalStorage.remove(key);
        
        // Remove backups
        if (!keepBackup) {
            for (let i = 0; i < this.config.maxBackupCount; i++) {
                LocalStorage.remove(`${key}_backup_${i}`);
            }
        }
        
        this.stopAutoSave();
        this.currentAttempt = null;
        
        this.emitEvent('stateCleared', {});
    },

    /**
     * Debounced save to avoid excessive writes
     */
    debouncedSave: function() {
        if (this._saveTimeout) {
            clearTimeout(this._saveTimeout);
        }
        
        this._saveTimeout = setTimeout(() => {
            this.saveToStorage();
        }, 500); // Tunggu 500ms setelah last change
    },

    /**
     * Start auto-save interval
     */
    startAutoSave: function() {
        this.stopAutoSave();
        
        this._autoSaveInterval = setInterval(() => {
            this.saveToStorage();
        }, this.config.autoSaveInterval);
    },

    /**
     * Stop auto-save interval
     */
    stopAutoSave: function() {
        if (this._autoSaveInterval) {
            clearInterval(this._autoSaveInterval);
            this._autoSaveInterval = null;
        }
        
        if (this._saveTimeout) {
            clearTimeout(this._saveTimeout);
            this._saveTimeout = null;
        }
    },

    /**
     * Event emitter
     */
    _events: {},
    
    on: function(event, callback) {
        if (!this._events[event]) {
            this._events[event] = [];
        }
        this._events[event].push(callback);
    },
    
    off: function(event, callback) {
        if (!this._events[event]) return;
        this._events[event] = this._events[event].filter(cb => cb !== callback);
    },
    
    emitEvent: function(event, data) {
        if (!this._events[event]) return;
        this._events[event].forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error('Event handler error:', e);
            }
        });
    },

    /**
     * Check for existing attempt in storage
     * @returns {object|null}
     */
    checkExistingAttempt: function() {
        const keys = LocalStorage.keys('attempt_');
        
        for (const key of keys) {
            const data = LocalStorage.get(key);
            if (data && !this.isAttemptComplete(data)) {
                return data;
            }
        }
        
        return null;
    },

    /**
     * Check if attempt is complete
     * @param {object} data 
     * @returns {boolean}
     */
    isAttemptComplete: function(data) {
        // Cek apakah sudah expired atau sudah selesai
        const startTime = new Date(data.startTime);
        const duration = data.timeRemaining || 0;
        const endTime = new Date(startTime.getTime() + duration * 1000);
        
        return Date.now() > endTime.getTime();
    },

    /**
     * Export data for sync
     * @returns {object|null}
     */
    exportForSync: function() {
        if (!this.currentAttempt) return null;
        
        return {
            attemptId: this.currentAttempt.attemptId,
            answers: Object.entries(this.currentAttempt.answers).map(([questionId, data]) => ({
                questionId: parseInt(questionId),
                answer: data.answer,
                isDoubt: data.isDoubt
            })),
            currentQuestion: this.currentAttempt.currentQuestion,
            timestamp: Date.now()
        };
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamState;
}

window.ExamState = ExamState;