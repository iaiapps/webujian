/**
 * LazyImage Module
 * Mengelola lazy loading gambar soal untuk menghemat bandwidth
 * 
 * @author ExamWeb System
 * @version 1.0.0
 */

const LazyImage = {
    // Configuration
    config: {
        rootMargin: '50px 0px',    // Load gambar 50px sebelum masuk viewport
        threshold: 0.01,            // Trigger saat 1% gambar visible
        placeholderColor: '#f0f0f0',
        errorImage: null            // URL gambar error (optional)
    },
    
    // Observer instance
    observer: null,
    
    // Image cache
    imageCache: new Map(),
    
    // Loading queue
    loadingQueue: new Set(),

    /**
     * Initialize lazy image loader
     * @param {object} options 
     */
    init: function(options = {}) {
        Object.assign(this.config, options);
        
        // Check if IntersectionObserver is supported
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                (entries) => this.handleIntersection(entries),
                {
                    rootMargin: this.config.rootMargin,
                    threshold: this.config.threshold
                }
            );
        } else {
            // Fallback: load all images immediately
            this.loadAllImages();
        }
        
        // Observe existing lazy images
        this.observeImages();
        
        console.log('LazyImage initialized');
    },

    /**
     * Handle intersection changes
     * @param {Array} entries 
     */
    handleIntersection: function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    },

    /**
     * Observe all images with data-src attribute
     */
    observeImages: function() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            // Skip if already processed
            if (img.classList.contains('lazy-processed')) return;
            
            // Add placeholder
            this.addPlaceholder(img);
            
            // Observe
            if (this.observer) {
                this.observer.observe(img);
            }
            
            img.classList.add('lazy-processed');
        });
    },

    /**
     * Add placeholder to image
     * @param {HTMLImageElement} img 
     */
    addPlaceholder: function(img) {
        // Save original dimensions if available
        const width = img.width || img.naturalWidth;
        const height = img.height || img.naturalHeight;
        
        // Add placeholder styling
        img.style.backgroundColor = this.config.placeholderColor;
        img.style.minHeight = '100px';
        
        // Add loading class
        img.classList.add('lazy-image');
        
        // If no src, add transparent pixel
        if (!img.src || img.src === window.location.href) {
            img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        }
    },

    /**
     * Load image
     * @param {HTMLImageElement} img 
     */
    loadImage: function(img) {
        const src = img.dataset.src;
        if (!src) return;
        
        // Check cache
        if (this.imageCache.has(src)) {
            img.src = src;
            img.classList.add('loaded');
            return;
        }
        
        // Check if already loading
        if (this.loadingQueue.has(src)) {
            // Wait for it to load
            const checkInterval = setInterval(() => {
                if (this.imageCache.has(src)) {
                    clearInterval(checkInterval);
                    img.src = src;
                    img.classList.add('loaded');
                }
            }, 100);
            return;
        }
        
        // Mark as loading
        this.loadingQueue.add(src);
        
        // Create new image to preload
        const preloadImg = new Image();
        
        preloadImg.onload = () => {
            // Cache the image
            this.imageCache.set(src, true);
            this.loadingQueue.delete(src);
            
            // Update original image
            img.src = src;
            img.classList.add('loaded');
            
            // Remove background color
            img.style.backgroundColor = '';
            
            // Trigger event
            this.emit('imageLoaded', { src: src, element: img });
        };
        
        preloadImg.onerror = () => {
            this.loadingQueue.delete(src);
            
            // Show error image if configured
            if (this.config.errorImage) {
                img.src = this.config.errorImage;
            }
            
            img.classList.add('error');
            this.emit('imageError', { src: src, element: img });
        };
        
        preloadImg.src = src;
    },

    /**
     * Load all images immediately (fallback)
     */
    loadAllImages: function() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => this.loadImage(img));
    },

    /**
     * Preload image
     * @param {string} src 
     * @returns {Promise}
     */
    preload: function(src) {
        return new Promise((resolve, reject) => {
            if (this.imageCache.has(src)) {
                resolve(src);
                return;
            }
            
            const img = new Image();
            img.onload = () => {
                this.imageCache.set(src, true);
                resolve(src);
            };
            img.onerror = reject;
            img.src = src;
        });
    },

    /**
     * Preload multiple images
     * @param {Array} sources 
     */
    preloadMultiple: function(sources) {
        return Promise.all(sources.map(src => this.preload(src)));
    },

    /**
     * Refresh and observe new images
     */
    refresh: function() {
        this.observeImages();
    },

    /**
     * Get cache info
     * @returns {object}
     */
    getCacheInfo: function() {
        return {
            cached: this.imageCache.size,
            loading: this.loadingQueue.size
        };
    },

    /**
     * Clear image cache
     */
    clearCache: function() {
        this.imageCache.clear();
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
    
    emit: function(event, data) {
        if (!this._events[event]) return;
        this._events[event].forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error('LazyImage event error:', e);
            }
        });
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LazyImage;
}

window.LazyImage = LazyImage;