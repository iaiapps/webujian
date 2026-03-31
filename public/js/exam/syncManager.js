/**
 * SyncManager Module
 * Mengelola sinkronisasi data ke server dengan queue dan retry mechanism
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const SyncManager = {
    // Queue untuk jawaban yang pending
    queue: [],
    
    // Configuration
    config: {
        autoSyncInterval: 30000,      // Sync otomatis tiap 30 detik
        retryInterval: 5000,          // Retry tiap 5 detik
        maxRetries: 3,                // Max 3 kali retry
        batchSize: 10,                // Sync 10 jawaban per batch
        syncEndpoint: '/student/test/{attemptId}/save-answer',
        bulkSyncEndpoint: '/student/test/{attemptId}/bulk-sync'
    },
    
    // State
    isOnline: navigator.onLine,
    isSyncing: false,
    lastSync: null,
    retryCount: {},
    
    // Event callbacks
    _events: {},

    /**
     * Initialize sync manager
     * @param {object} options 
     */
    init: function(options = {}) {
        Object.assign(this.config, options);
        
        // Listen for online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Start auto-sync
        this.startAutoSync();
        
        // Try to restore queue from storage
        this.restoreQueue();
        
        console.log('SyncManager initialized');
    },

    /**
     * Handle online event
     */
    handleOnline: function() {
        console.log('Connection restored, resuming sync...');
        this.isOnline = true;
        this.emit('online', {});
        
        // Immediately sync pending items
        this.syncQueue();
    },

    /**
     * Handle offline event
     */
    handleOffline: function() {
        console.log('Connection lost, pausing sync...');
        this.isOnline = false;
        this.emit('offline', {});
    },

    /**
     * Add item to sync queue
     * @param {number} questionId 
     * @param {string} answer 
     * @param {boolean} isDoubt 
     * @param {number} attemptId 
     */
    enqueue: function(questionId, answer, isDoubt = false, attemptId = null) {
        const item = {
            id: `${Date.now()}_${questionId}`,
            questionId: questionId,
            answer: answer,
            isDoubt: isDoubt,
            attemptId: attemptId || this.getAttemptId(),
            timestamp: Date.now(),
            retries: 0
        };
        
        // Cek apakah sudah ada item untuk question ini
        const existingIndex = this.queue.findIndex(q => q.questionId === questionId);
        if (existingIndex > -1) {
            // Replace dengan data terbaru
            this.queue[existingIndex] = item;
        } else {
            this.queue.push(item);
        }
        
        // Save queue to storage
        this.saveQueue();
        
        this.emit('enqueued', { item, queueLength: this.queue.length });
        
        // Jika online, sync segera
        if (this.isOnline && !this.isSyncing) {
            this.debouncedSync();
        }
    },

    /**
     * Get current attempt ID
     * @returns {number|null}
     */
    getAttemptId: function() {
        if (window.ExamState && window.ExamState.currentAttempt) {
            return window.ExamState.currentAttempt.attemptId;
        }
        return null;
    },

    /**
     * Save queue to localStorage
     */
    saveQueue: function() {
        if (window.LocalStorage) {
            window.LocalStorage.set('sync_queue', this.queue, { expiry: 2 });
        }
    },

    /**
     * Restore queue from localStorage
     */
    restoreQueue: function() {
        if (window.LocalStorage) {
            const saved = window.LocalStorage.get('sync_queue');
            if (saved && Array.isArray(saved)) {
                this.queue = saved;
                console.log(`Restored ${saved.length} items from queue`);
            }
        }
    },

    /**
     * Clear queue
     */
    clearQueue: function() {
        this.queue = [];
        this.saveQueue();
        this.emit('queueCleared', {});
    },

    /**
     * Sync queue to server
     * @returns {Promise}
     */
    syncQueue: async function() {
        if (this.isSyncing || !this.isOnline || this.queue.length === 0) {
            return { success: true, synced: 0 };
        }
        
        this.isSyncing = true;
        this.emit('syncStarted', { queueLength: this.queue.length });
        
        const attemptId = this.getAttemptId();
        if (!attemptId) {
            console.error('No attempt ID found');
            this.isSyncing = false;
            return { success: false, error: 'No attempt ID' };
        }
        
        // Ambil batch untuk sync
        const batch = this.queue.slice(0, this.config.batchSize);
        
        try {
            // Coba bulk sync dulu
            if (batch.length > 1) {
                const result = await this.bulkSync(batch, attemptId);
                
                if (result.success) {
                    // Hapus item yang sudah synced
                    const syncedIds = result.syncedIds || batch.map(item => item.id);
                    this.removeFromQueue(syncedIds);
                    
                    this.lastSync = Date.now();
                    this.emit('syncCompleted', { synced: syncedIds.length });
                } else {
                    // Bulk sync gagal, coba satu per satu
                    await this.syncOneByOne(batch, attemptId);
                }
            } else {
                // Cuma 1 item, sync satu per satu
                await this.syncOneByOne(batch, attemptId);
            }
            
            // Update ExamState
            if (window.ExamState) {
                batch.forEach(item => {
                    window.ExamState.updateSyncStatus(item.questionId, 'synced');
                });
            }
            
        } catch (error) {
            console.error('Sync error:', error);
            this.emit('syncError', { error });
        } finally {
            this.isSyncing = false;
            
            // Jika masih ada item di queue, schedule retry
            if (this.queue.length > 0) {
                setTimeout(() => this.syncQueue(), this.config.retryInterval);
            }
        }
        
        return { success: true, remaining: this.queue.length };
    },

    /**
     * Bulk sync multiple answers
     * @param {Array} batch 
     * @param {number} attemptId 
     * @returns {Promise}
     */
    bulkSync: async function(batch, attemptId) {
        const endpoint = this.config.bulkSyncEndpoint.replace('{attemptId}', attemptId);
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                answers: batch.map(item => ({
                    question_id: item.questionId,
                    answer: item.answer,
                    is_doubt: item.isDoubt
                }))
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    },

    /**
     * Sync items one by one
     * @param {Array} batch 
     * @param {number} attemptId 
     */
    syncOneByOne: async function(batch, attemptId) {
        for (const item of batch) {
            try {
                const success = await this.syncSingle(item, attemptId);
                
                if (success) {
                    this.removeFromQueue([item.id]);
                    
                    if (window.ExamState) {
                        window.ExamState.updateSyncStatus(item.questionId, 'synced');
                    }
                } else {
                    // Increment retry count
                    item.retries++;
                    
                    if (item.retries >= this.config.maxRetries) {
                        // Max retries reached, remove from queue
                        this.removeFromQueue([item.id]);
                        this.emit('syncFailed', { item });
                    }
                }
            } catch (error) {
                console.error('Sync single error:', error);
                item.retries++;
            }
        }
    },

    /**
     * Sync single answer
     * @param {object} item 
     * @param {number} attemptId 
     * @returns {Promise<boolean>}
     */
    syncSingle: async function(item, attemptId) {
        const endpoint = this.config.syncEndpoint.replace('{attemptId}', attemptId);
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                question_id: item.questionId,
                answer: item.answer,
                is_doubt: item.isDoubt
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data.success === true;
    },

    /**
     * Remove items from queue
     * @param {Array} ids 
     */
    removeFromQueue: function(ids) {
        this.queue = this.queue.filter(item => !ids.includes(item.id));
        this.saveQueue();
    },

    /**
     * Get CSRF token
     * @returns {string}
     */
    getCsrfToken: function() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    },

    /**
     * Debounced sync
     */
    debouncedSync: function() {
        if (this._syncTimeout) {
            clearTimeout(this._syncTimeout);
        }
        
        this._syncTimeout = setTimeout(() => {
            this.syncQueue();
        }, 1000); // Tunggu 1 detik setelah enqueue
    },

    /**
     * Start auto-sync interval
     */
    startAutoSync: function() {
        this.stopAutoSync();
        
        this._autoSyncInterval = setInterval(() => {
            if (this.isOnline && this.queue.length > 0 && !this.isSyncing) {
                this.syncQueue();
            }
        }, this.config.autoSyncInterval);
    },

    /**
     * Stop auto-sync interval
     */
    stopAutoSync: function() {
        if (this._autoSyncInterval) {
            clearInterval(this._autoSyncInterval);
            this._autoSyncInterval = null;
        }
        
        if (this._syncTimeout) {
            clearTimeout(this._syncTimeout);
            this._syncTimeout = null;
        }
    },

    /**
     * Get sync status
     * @returns {object}
     */
    getStatus: function() {
        return {
            isOnline: this.isOnline,
            isSyncing: this.isSyncing,
            queueLength: this.queue.length,
            lastSync: this.lastSync,
            timeSinceLastSync: this.lastSync ? Date.now() - this.lastSync : null
        };
    },

    /**
     * Force sync now
     * @returns {Promise}
     */
    forceSync: function() {
        return this.syncQueue();
    },

    /**
     * Event handlers
     */
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
    
    emit: function(event, data) {
        if (!this._events[event]) return;
        this._events[event].forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error('SyncManager event error:', e);
            }
        });
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SyncManager;
}

window.SyncManager = SyncManager;