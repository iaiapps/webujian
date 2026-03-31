/**
 * LocalStorage Module
 * Module untuk CRUD localStorage dengan kompresi dan error handling
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const LocalStorage = {
    // Configuration
    config: {
        prefix: 'examweb_',
        maxSize: 5 * 1024 * 1024, // 5MB limit
        compressionEnabled: true,
        expiryDays: 1
    },

    /**
     * Check if localStorage is available
     * @returns {boolean}
     */
    isAvailable: function() {
        try {
            const test = '__localStorage_test__';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch (e) {
            console.warn('localStorage not available:', e);
            return false;
        }
    },

    /**
     * Get storage key with prefix
     * @param {string} key 
     * @returns {string}
     */
    getKey: function(key) {
        return this.config.prefix + key;
    },

    /**
     * Compress data using LZ-string
     * @param {string} data 
     * @returns {string}
     */
    compress: function(data) {
        if (!this.config.compressionEnabled) return data;
        
        try {
            // Simple compression: remove whitespace and encode
            // For production, consider using LZ-string library
            return btoa(unescape(encodeURIComponent(data)));
        } catch (e) {
            console.warn('Compression failed:', e);
            return data;
        }
    },

    /**
     * Decompress data
     * @param {string} data 
     * @returns {string}
     */
    decompress: function(data) {
        if (!this.config.compressionEnabled) return data;
        
        try {
            return decodeURIComponent(escape(atob(data)));
        } catch (e) {
            // If decompression fails, return original (might be uncompressed)
            return data;
        }
    },

    /**
     * Calculate storage usage
     * @returns {object}
     */
    getStorageInfo: function() {
        let used = 0;
        let count = 0;
        
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.config.prefix)) {
                const value = localStorage.getItem(key);
                used += (key.length + value.length) * 2; // UTF-16 = 2 bytes per char
                count++;
            }
        }
        
        return {
            used: used,
            total: this.config.maxSize,
            available: this.config.maxSize - used,
            usedPercent: (used / this.config.maxSize) * 100,
            itemCount: count
        };
    },

    /**
     * Check if storage is nearly full
     * @param {number} thresholdPercent 
     * @returns {boolean}
     */
    isNearlyFull: function(thresholdPercent = 80) {
        const info = this.getStorageInfo();
        return info.usedPercent >= thresholdPercent;
    },

    /**
     * Save data to localStorage
     * @param {string} key 
     * @param {any} data 
     * @param {object} options 
     * @returns {boolean}
     */
    set: function(key, data, options = {}) {
        if (!this.isAvailable()) {
            console.error('localStorage not available');
            return false;
        }

        const fullKey = this.getKey(key);
        const expiry = options.expiry || this.config.expiryDays;
        
        const storageData = {
            data: data,
            timestamp: Date.now(),
            expiry: expiry * 24 * 60 * 60 * 1000, // Convert to milliseconds
            version: '1.0'
        };

        try {
            const jsonString = JSON.stringify(storageData);
            const compressed = this.compress(jsonString);
            
            // Check size before saving
            const size = (fullKey.length + compressed.length) * 2;
            if (size > this.config.maxSize * 0.5) { // Max 50% for single item
                console.warn('Data too large, cleaning old entries...');
                this.cleanOldEntries();
            }
            
            localStorage.setItem(fullKey, compressed);
            
            // Check if we're approaching limit
            if (this.isNearlyFull(90)) {
                this.cleanOldEntries();
            }
            
            return true;
        } catch (e) {
            if (e.name === 'QuotaExceededError') {
                console.warn('Storage quota exceeded, cleaning...');
                this.cleanOldEntries();
                
                // Retry once
                try {
                    localStorage.setItem(fullKey, compressed);
                    return true;
                } catch (retryError) {
                    console.error('Failed to save even after cleanup:', retryError);
                }
            } else {
                console.error('Error saving to localStorage:', e);
            }
            return false;
        }
    },

    /**
     * Get data from localStorage
     * @param {string} key 
     * @returns {any|null}
     */
    get: function(key) {
        if (!this.isAvailable()) return null;

        const fullKey = this.getKey(key);
        
        try {
            const compressed = localStorage.getItem(fullKey);
            if (!compressed) return null;
            
            const jsonString = this.decompress(compressed);
            const storageData = JSON.parse(jsonString);
            
            // Check expiry
            if (storageData.expiry && Date.now() - storageData.timestamp > storageData.expiry) {
                this.remove(key);
                return null;
            }
            
            return storageData.data;
        } catch (e) {
            console.error('Error reading from localStorage:', e);
            return null;
        }
    },

    /**
     * Remove data from localStorage
     * @param {string} key 
     * @returns {boolean}
     */
    remove: function(key) {
        if (!this.isAvailable()) return false;

        const fullKey = this.getKey(key);
        
        try {
            localStorage.removeItem(fullKey);
            return true;
        } catch (e) {
            console.error('Error removing from localStorage:', e);
            return false;
        }
    },

    /**
     * Remove all exam-related data
     * @returns {boolean}
     */
    clear: function() {
        if (!this.isAvailable()) return false;

        try {
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.config.prefix)) {
                    keysToRemove.push(key);
                }
            }
            
            keysToRemove.forEach(key => localStorage.removeItem(key));
            return true;
        } catch (e) {
            console.error('Error clearing localStorage:', e);
            return false;
        }
    },

    /**
     * Clean old/expired entries
     * @param {number} keepCount - Keep only N most recent items
     */
    cleanOldEntries: function(keepCount = 50) {
        if (!this.isAvailable()) return;

        const entries = [];
        
        // Collect all entries with timestamps
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.config.prefix)) {
                try {
                    const compressed = localStorage.getItem(key);
                    const jsonString = this.decompress(compressed);
                    const data = JSON.parse(jsonString);
                    
                    entries.push({
                        key: key,
                        timestamp: data.timestamp || 0,
                        isExpired: data.expiry && Date.now() - data.timestamp > data.expiry
                    });
                } catch (e) {
                    // If can't parse, consider it old
                    entries.push({ key: key, timestamp: 0, isExpired: true });
                }
            }
        }
        
        // Remove expired entries
        entries.filter(e => e.isExpired).forEach(e => {
            localStorage.removeItem(e.key);
        });
        
        // If still too many, remove oldest
        const remaining = entries.filter(e => !e.isExpired);
        if (remaining.length > keepCount) {
            remaining.sort((a, b) => a.timestamp - b.timestamp);
            remaining.slice(0, remaining.length - keepCount).forEach(e => {
                localStorage.removeItem(e.key);
            });
        }
    },

    /**
     * Get all keys matching pattern
     * @param {string} pattern 
     * @returns {Array}
     */
    keys: function(pattern = '') {
        if (!this.isAvailable()) return [];

        const keys = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.config.prefix)) {
                const shortKey = key.replace(this.config.prefix, '');
                if (!pattern || shortKey.includes(pattern)) {
                    keys.push(shortKey);
                }
            }
        }
        return keys;
    },

    /**
     * Subscribe to storage changes (cross-tab sync)
     * @param {function} callback 
     */
    onChange: function(callback) {
        window.addEventListener('storage', function(e) {
            if (e.key && e.key.startsWith(LocalStorage.config.prefix)) {
                callback({
                    key: e.key.replace(LocalStorage.config.prefix, ''),
                    oldValue: e.oldValue,
                    newValue: e.newValue,
                    url: e.url
                });
            }
        });
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocalStorage;
}

// Make available globally
window.LocalStorage = LocalStorage;