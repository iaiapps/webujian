/**
 * QuestionLoader Module
 * Mengelola loading soal via AJAX dengan lazy loading
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const QuestionLoader = {
    // Configuration
    config: {
        prefetchCount: 3,      // Prefetch 3 soal berikutnya
        cacheSize: 10,         // Cache 10 soal di memory
        retryDelay: 3000,      // Retry tiap 3 detik
        maxRetries: 3
    },
    
    // State
    currentQuestion: 1,
    totalQuestions: 0,
    attemptId: null,
    cache: new Map(),
    loading: new Set(),
    retries: {},
    
    // Event callbacks
    _events: {},

    /**
     * Initialize question loader
     * @param {object} options 
     */
    init: function(options = {}) {
        Object.assign(this.config, options);
        
        this.attemptId = options.attemptId;
        this.totalQuestions = options.totalQuestions;
        this.currentQuestion = options.currentQuestion || 1;
        
        // Preload current question
        this.loadQuestion(this.currentQuestion);
        
        // Prefetch next questions
        this.prefetchNext();
        
        console.log('QuestionLoader initialized');
    },

    /**
     * Load question by number
     * @param {number} questionNumber 
     * @returns {Promise}
     */
    loadQuestion: async function(questionNumber) {
        // Validate
        if (questionNumber < 1 || questionNumber > this.totalQuestions) {
            return Promise.reject(new Error('Invalid question number'));
        }
        
        // Check cache
        if (this.cache.has(questionNumber)) {
            this.emit('questionLoaded', {
                number: questionNumber,
                question: this.cache.get(questionNumber),
                fromCache: true
            });
            return this.cache.get(questionNumber);
        }
        
        // Check if already loading
        if (this.loading.has(questionNumber)) {
            return new Promise((resolve, reject) => {
                const checkInterval = setInterval(() => {
                    if (this.cache.has(questionNumber)) {
                        clearInterval(checkInterval);
                        resolve(this.cache.get(questionNumber));
                    } else if (!this.loading.has(questionNumber)) {
                        clearInterval(checkInterval);
                        reject(new Error('Loading failed'));
                    }
                }, 100);
            });
        }
        
        // Mark as loading
        this.loading.add(questionNumber);
        this.emit('questionLoading', { number: questionNumber });
        
        try {
            const question = await this.fetchQuestion(questionNumber);
            
            // Cache the question
            this.addToCache(questionNumber, question);
            
            // Remove from loading
            this.loading.delete(questionNumber);
            
            // Reset retries
            delete this.retries[questionNumber];
            
            this.emit('questionLoaded', {
                number: questionNumber,
                question: question,
                fromCache: false
            });
            
            return question;
            
        } catch (error) {
            this.loading.delete(questionNumber);
            
            // Retry logic
            this.retries[questionNumber] = (this.retries[questionNumber] || 0) + 1;
            
            if (this.retries[questionNumber] < this.config.maxRetries) {
                console.warn(`Retrying question ${questionNumber}, attempt ${this.retries[questionNumber]}`);
                await this.delay(this.config.retryDelay);
                return this.loadQuestion(questionNumber);
            }
            
            this.emit('questionLoadError', {
                number: questionNumber,
                error: error.message
            });
            
            throw error;
        }
    },

    /**
     * Fetch question from server
     * @param {number} questionNumber 
     * @returns {Promise}
     */
    fetchQuestion: async function(questionNumber) {
        const response = await fetch(`/student/test/${this.attemptId}/question/${questionNumber}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to load question');
        }
        
        return data.question;
    },

    /**
     * Add question to cache
     * @param {number} questionNumber 
     * @param {object} question 
     */
    addToCache: function(questionNumber, question) {
        // Remove oldest if cache full
        if (this.cache.size >= this.config.cacheSize) {
            const oldestKey = this.cache.keys().next().value;
            this.cache.delete(oldestKey);
        }
        
        this.cache.set(questionNumber, question);
    },

    /**
     * Get cached question
     * @param {number} questionNumber 
     * @returns {object|null}
     */
    getCached: function(questionNumber) {
        return this.cache.get(questionNumber) || null;
    },

    /**
     * Prefetch next questions
     */
    prefetchNext: function() {
        const nextNumbers = [];
        
        for (let i = 1; i <= this.config.prefetchCount; i++) {
            const nextNum = this.currentQuestion + i;
            if (nextNum <= this.totalQuestions && !this.cache.has(nextNum)) {
                nextNumbers.push(nextNum);
            }
        }
        
        // Prefetch in background
        nextNumbers.forEach(num => {
            this.loadQuestion(num).catch(e => {
                // Silent fail for prefetch
                console.log(`Prefetch failed for question ${num}:`, e.message);
            });
        });
    },

    /**
     * Prefetch previous questions
     */
    prefetchPrev: function() {
        const prevNumbers = [];
        
        for (let i = 1; i <= 2; i++) {
            const prevNum = this.currentQuestion - i;
            if (prevNum >= 1 && !this.cache.has(prevNum)) {
                prevNumbers.push(prevNum);
            }
        }
        
        prevNumbers.forEach(num => {
            this.loadQuestion(num).catch(e => {
                // Silent fail for prefetch
                console.log(`Prefetch failed for question ${num}:`, e.message);
            });
        });
    },

    /**
     * Navigate to question
     * @param {number} questionNumber 
     * @returns {Promise}
     */
    navigateTo: async function(questionNumber) {
        if (questionNumber < 1 || questionNumber > this.totalQuestions) {
            return Promise.reject(new Error('Invalid question number'));
        }
        
        this.emit('navigationStart', { from: this.currentQuestion, to: questionNumber });
        
        try {
            const question = await this.loadQuestion(questionNumber);
            this.currentQuestion = questionNumber;
            
            // Prefetch next
            this.prefetchNext();
            
            // Prefetch prev if going backward
            if (questionNumber < this.currentQuestion) {
                this.prefetchPrev();
            }
            
            this.emit('navigationComplete', { 
                number: questionNumber, 
                question: question 
            });
            
            return question;
            
        } catch (error) {
            this.emit('navigationError', { 
                number: questionNumber, 
                error: error.message 
            });
            throw error;
        }
    },

    /**
     * Go to next question
     * @returns {Promise}
     */
    next: function() {
        if (this.currentQuestion >= this.totalQuestions) {
            return Promise.reject(new Error('Already at last question'));
        }
        return this.navigateTo(this.currentQuestion + 1);
    },

    /**
     * Go to previous question
     * @returns {Promise}
     */
    prev: function() {
        if (this.currentQuestion <= 1) {
            return Promise.reject(new Error('Already at first question'));
        }
        return this.navigateTo(this.currentQuestion - 1);
    },

    /**
     * Get current question number
     * @returns {number}
     */
    getCurrentNumber: function() {
        return this.currentQuestion;
    },

    /**
     * Get total questions
     * @returns {number}
     */
    getTotal: function() {
        return this.totalQuestions;
    },

    /**
     * Clear cache
     */
    clearCache: function() {
        this.cache.clear();
    },

    /**
     * Get cache info
     * @returns {object}
     */
    getCacheInfo: function() {
        return {
            size: this.cache.size,
            maxSize: this.config.cacheSize,
            loading: this.loading.size,
            cachedNumbers: Array.from(this.cache.keys())
        };
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
     * Delay helper
     * @param {number} ms 
     * @returns {Promise}
     */
    delay: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
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
                console.error('QuestionLoader event error:', e);
            }
        });
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuestionLoader;
}

window.QuestionLoader = QuestionLoader;