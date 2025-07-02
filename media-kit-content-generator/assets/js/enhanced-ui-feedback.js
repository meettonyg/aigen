/**
 * Enhanced UI Feedback System - SIMPLIFIED VERSION
 * 
 * SIMPLIFIED FROM: 400+ lines of complex animations, queuing, progress bars
 * SIMPLIFIED TO: ~50 lines of essential notification functionality
 * 
 * MAINTAINS BACKWARD COMPATIBILITY while dramatically reducing complexity
 * Uses existing Simple Notifications system for actual implementation
 */

class EnhancedUIFeedback {
    constructor() {
        this.notificationId = 0;
        console.log('🎨 Enhanced UI Feedback initialized (simplified version)');
    }

    /**
     * Show toast notification - SIMPLIFIED to use Simple Notifications
     */
    showToast(message, type = 'info', duration = 3000, options = {}) {
        const id = ++this.notificationId;
        
        console.log(`📢 Showing toast notification (${id}):`, { message, type, duration });

        // Extract message content
        let content;
        if (typeof message === 'string') {
            content = message;
        } else if (typeof message === 'object') {
            content = message.message || message.title || 'Notification';
        }

        // Use Simple Notifications system (already loaded)
        if (window.showNotification) {
            window.showNotification(content, type, duration);
        } else {
            // Fallback to console if Simple Notifications not available
            console.log(`💬 ${type.toUpperCase()}: ${content}`);
        }

        return id;
    }

    /**
     * Show loading spinner - SIMPLIFIED to basic implementation
     */
    showLoadingSpinner(target, message = 'Loading...', options = {}) {
        const targetElement = typeof target === 'string' ? 
            document.querySelector(target) : target;

        if (!targetElement) {
            console.warn('Loading target not found:', target);
            return null;
        }

        const loadingId = 'loading_' + Date.now();
        
        // Create simple loading overlay
        const overlay = document.createElement('div');
        overlay.className = 'simple-loading-overlay';
        overlay.id = loadingId;
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: inherit;
        `;

        overlay.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="
                    width: 24px;
                    height: 24px;
                    border: 2px solid #ecf0f1;
                    border-top: 2px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 8px;
                "></div>
                <div style="font-size: 14px; color: #34495e;">${message}</div>
            </div>
        `;

        // Add basic spin animation if not exists
        if (!document.getElementById('simple-loading-styles')) {
            const style = document.createElement('style');
            style.id = 'simple-loading-styles';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }

        // Ensure target has relative positioning
        const computedStyle = window.getComputedStyle(targetElement);
        if (computedStyle.position === 'static') {
            targetElement.style.position = 'relative';
        }
        
        targetElement.appendChild(overlay);

        console.log(`⏳ Loading spinner shown: ${loadingId} - ${message}`);
        return loadingId;
    }

    /**
     * Hide loading spinner
     */
    hideLoadingSpinner(loadingId) {
        const overlay = document.getElementById(loadingId);
        
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
            console.log(`✅ Loading spinner hidden: ${loadingId}`);
        }
    }

    /**
     * Show error message - SIMPLIFIED wrapper
     */
    showErrorMessage(userMessage, options = {}) {
        const duration = options.autoDismiss ? (options.duration || 8000) : 0;
        return this.showToast(userMessage, 'error', duration);
    }

    /**
     * Clear all notifications - Use Simple Notifications
     */
    clearAllNotifications() {
        if (window.SimpleNotifications) {
            window.SimpleNotifications.clearAll();
        }
        console.log('🗑️ All notifications cleared');
    }

    // Legacy method aliases for backward compatibility
    showProgress() { console.log('Progress bars removed in simplified version'); }
    hideProgress() { console.log('Progress bars removed in simplified version'); }
    showErrorBanner() { console.log('Error banners removed in simplified version'); }
    clearErrorBanners() { console.log('Error banners removed in simplified version'); }
    hideAllLoading() { console.log('Global loading management simplified'); }
}

// Initialize global instance
window.EnhancedUIFeedback = new EnhancedUIFeedback();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedUIFeedback;
}

console.log('✅ Enhanced UI Feedback loaded successfully (simplified version)');
