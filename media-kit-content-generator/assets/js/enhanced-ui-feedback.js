/**
 * Enhanced UI Feedback System - Modern User Interface Components
 * Replaces alert() calls with professional toast notifications and loading states
 */

class EnhancedUIFeedback {
    constructor() {
        this.toastContainer = null;
        this.loadingOverlays = new Map();
        this.progressIndicators = new Map();
        this.notificationId = 0;
        
        this.setupToastContainer();
        this.setupGlobalStyles();
        
        console.log('🎨 Enhanced UI Feedback initialized');
    }

    /**
     * Setup toast notification container
     */
    setupToastContainer() {
        if (this.toastContainer) {
            return;
        }

        this.toastContainer = document.createElement('div');
        this.toastContainer.id = 'mkcg-toast-container';
        this.toastContainer.className = 'mkcg-toast-container';
        
        // Position container at top-right of viewport
        this.toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
            max-width: 400px;
        `;

        document.body.appendChild(this.toastContainer);
        console.log('📋 Toast container created');
    }

    /**
     * Setup global CSS styles for UI components
     */
    setupGlobalStyles() {
        if (document.getElementById('mkcg-enhanced-ui-styles')) {
            return;
        }

        const styleSheet = document.createElement('style');
        styleSheet.id = 'mkcg-enhanced-ui-styles';
        styleSheet.textContent = `
            /* Toast Notifications */
            .mkcg-toast {
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                margin-bottom: 12px;
                padding: 16px;
                pointer-events: auto;
                position: relative;
                border-left: 4px solid #3498db;
                animation: mkcg-toast-slide-in 0.3s ease-out;
                max-width: 100%;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }

            .mkcg-toast--success {
                border-left-color: #27ae60;
            }

            .mkcg-toast--error {
                border-left-color: #e74c3c;
            }

            .mkcg-toast--warning {
                border-left-color: #f39c12;
            }

            .mkcg-toast--info {
                border-left-color: #3498db;
            }

            .mkcg-toast__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 8px;
            }

            .mkcg-toast__title {
                font-weight: 600;
                font-size: 14px;
                color: #2c3e50;
                margin: 0;
            }

            .mkcg-toast__close {
                background: none;
                border: none;
                font-size: 18px;
                color: #7f8c8d;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: background-color 0.2s;
            }

            .mkcg-toast__close:hover {
                background-color: #ecf0f1;
            }

            .mkcg-toast__message {
                font-size: 13px;
                color: #34495e;
                line-height: 1.4;
                margin: 0 0 12px 0;
            }

            .mkcg-toast__actions {
                margin-top: 12px;
            }

            .mkcg-toast__action {
                display: block;
                font-size: 12px;
                color: #7f8c8d;
                margin: 4px 0;
                padding-left: 12px;
                position: relative;
            }

            .mkcg-toast__action:before {
                content: "•";
                position: absolute;
                left: 0;
            }

            .mkcg-toast__progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background-color: rgba(52, 152, 219, 0.3);
                border-radius: 0 0 4px 4px;
                transition: width 0.1s linear;
            }

            /* Loading Overlays */
            .mkcg-loading-overlay {
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
            }

            .mkcg-loading-overlay--global {
                position: fixed;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
            }

            .mkcg-loading-content {
                text-align: center;
                padding: 20px;
            }

            .mkcg-loading-spinner {
                width: 32px;
                height: 32px;
                border: 3px solid #ecf0f1;
                border-top: 3px solid #3498db;
                border-radius: 50%;
                animation: mkcg-spin 1s linear infinite;
                margin: 0 auto 12px;
            }

            .mkcg-loading-message {
                font-size: 14px;
                color: #34495e;
                font-weight: 500;
            }

            /* Progress Bars */
            .mkcg-progress-bar {
                width: 100%;
                height: 6px;
                background-color: #ecf0f1;
                border-radius: 3px;
                overflow: hidden;
                margin: 8px 0;
            }

            .mkcg-progress-bar__fill {
                height: 100%;
                background-color: #3498db;
                border-radius: 3px;
                transition: width 0.3s ease;
                position: relative;
            }

            .mkcg-progress-bar__fill--animated {
                background-image: linear-gradient(
                    45deg,
                    rgba(255, 255, 255, 0.2) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.2) 50%,
                    rgba(255, 255, 255, 0.2) 75%,
                    transparent 75%,
                    transparent
                );
                background-size: 20px 20px;
                animation: mkcg-progress-stripes 1s linear infinite;
            }

            /* Error Banners */
            .mkcg-error-banner {
                background: #fdf2f2;
                border: 1px solid #f5c6cb;
                border-radius: 6px;
                padding: 12px 16px;
                margin: 12px 0;
                color: #721c24;
            }

            .mkcg-error-banner--warning {
                background: #fff3cd;
                border-color: #ffeaa7;
                color: #856404;
            }

            .mkcg-error-banner--info {
                background: #d1ecf1;
                border-color: #bee5eb;
                color: #0c5460;
            }

            /* PHASE 2B: Enhanced Field State Styles */
            .field-state-indicator {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                white-space: nowrap;
                user-select: none;
                transition: all 0.3s ease;
            }
            
            .field--saving {
                position: relative;
            }
            
            .field--saving::after {
                content: '';
                position: absolute;
                right: 30px;
                top: 50%;
                transform: translateY(-50%);
                width: 12px;
                height: 12px;
                border: 2px solid #3498db;
                border-top: 2px solid transparent;
                border-radius: 50%;
                animation: mkcg-spin 1s linear infinite;
            }
            
            .field--saved {
                animation: pulse-success 0.6s ease;
            }
            
            .field--error {
                animation: shake-error 0.6s ease;
            }
            
            .field--offline-saved {
                animation: pulse-warning 0.6s ease;
            }
            
            .field--offline-mode {
                background-color: #fef9e7;
                border-color: #f39c12;
            }
            
            /* Network Status Enhancements */
            #mkcg-network-status {
                backdrop-filter: blur(10px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            /* Enhanced Button States */
            button:disabled {
                cursor: not-allowed;
                filter: grayscale(0.5);
                transition: all 0.3s ease;
            }
            
            button:disabled:hover {
                transform: none;
                box-shadow: none;
            }

            /* Animations */
            @keyframes mkcg-toast-slide-in {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes mkcg-toast-slide-out {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            @keyframes mkcg-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @keyframes mkcg-progress-stripes {
                0% { background-position: 0 0; }
                100% { background-position: 20px 0; }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes pulse-success {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.02); }
            }
            
            @keyframes pulse-warning {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.01); }
            }
            
            @keyframes shake-error {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
                20%, 40%, 60%, 80% { transform: translateX(2px); }
            }

            /* Responsive Design */
            @media (max-width: 480px) {
                .mkcg-toast-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }

                .mkcg-toast {
                    margin-bottom: 8px;
                    padding: 12px;
                }
            }
        `;

        document.head.appendChild(styleSheet);
        console.log('🎨 Enhanced UI styles injected');
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 5000, options = {}) {
        const id = ++this.notificationId;
        
        console.log(`📢 Showing toast notification (${id}):`, { message, type, duration });

        const toast = this.createToastElement(message, type, options);
        toast.dataset.id = id;

        // Add to container
        this.toastContainer.appendChild(toast);

        // Auto-dismiss if duration is set
        if (duration > 0) {
            this.startToastTimer(toast, duration);
        }

        // Bind close button
        const closeBtn = toast.querySelector('.mkcg-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.removeToast(toast));
        }

        return id;
    }

    /**
     * Create toast element
     */
    createToastElement(message, type, options) {
        const toast = document.createElement('div');
        toast.className = `mkcg-toast mkcg-toast--${type}`;

        // Determine message structure
        let title, content, actions;
        
        if (typeof message === 'string') {
            title = this.getDefaultTitle(type);
            content = message;
            actions = options.actions || [];
        } else if (typeof message === 'object') {
            title = message.title || this.getDefaultTitle(type);
            content = message.message || '';
            actions = message.actions || options.actions || [];
        }

        // Build toast HTML
        let html = `
            <div class="mkcg-toast__header">
                <h4 class="mkcg-toast__title">${this.escapeHtml(title)}</h4>
                <button class="mkcg-toast__close" aria-label="Close">×</button>
            </div>
            <div class="mkcg-toast__message">${this.escapeHtml(content)}</div>
        `;

        // Add actions if provided
        if (actions.length > 0) {
            html += '<div class="mkcg-toast__actions">';
            actions.forEach(action => {
                html += `<div class="mkcg-toast__action">${this.escapeHtml(action)}</div>`;
            });
            html += '</div>';
        }

        toast.innerHTML = html;
        return toast;
    }

    /**
     * Get default title for toast type
     */
    getDefaultTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };
        return titles[type] || 'Notification';
    }

    /**
     * Start toast auto-dismiss timer
     */
    startToastTimer(toast, duration) {
        // Add progress bar
        const progressBar = document.createElement('div');
        progressBar.className = 'mkcg-toast__progress';
        progressBar.style.width = '100%';
        toast.appendChild(progressBar);

        // Animate progress bar
        const startTime = Date.now();
        const updateProgress = () => {
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, 1 - (elapsed / duration));
            
            progressBar.style.width = (remaining * 100) + '%';
            
            if (remaining > 0) {
                requestAnimationFrame(updateProgress);
            } else {
                this.removeToast(toast);
            }
        };
        
        requestAnimationFrame(updateProgress);
    }

    /**
     * Remove toast with animation
     */
    removeToast(toast) {
        toast.style.animation = 'mkcg-toast-slide-out 0.3s ease-out';
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    /**
     * Show error message (enhanced version)
     */
    showErrorMessage(userMessage, options = {}) {
        const duration = options.autoDismiss ? (options.duration || 8000) : 0;
        
        return this.showToast(userMessage, 'error', duration, {
            actions: userMessage.actions
        });
    }

    /**
     * Show loading spinner
     */
    showLoadingSpinner(target, message = 'Loading...', options = {}) {
        const targetElement = typeof target === 'string' ? 
            document.querySelector(target) : target;

        if (!targetElement) {
            console.warn('Loading target not found:', target);
            return null;
        }

        const loadingId = 'loading_' + Date.now();
        
        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.className = options.global ? 
            'mkcg-loading-overlay mkcg-loading-overlay--global' : 
            'mkcg-loading-overlay';
        overlay.id = loadingId;

        overlay.innerHTML = `
            <div class="mkcg-loading-content">
                <div class="mkcg-loading-spinner"></div>
                <div class="mkcg-loading-message">${this.escapeHtml(message)}</div>
            </div>
        `;

        // Position and add overlay
        if (options.global) {
            document.body.appendChild(overlay);
        } else {
            // Ensure target has relative positioning
            const computedStyle = window.getComputedStyle(targetElement);
            if (computedStyle.position === 'static') {
                targetElement.style.position = 'relative';
            }
            targetElement.appendChild(overlay);
        }

        this.loadingOverlays.set(loadingId, {
            overlay,
            target: targetElement,
            message
        });

        console.log(`⏳ Loading spinner shown: ${loadingId} - ${message}`);
        return loadingId;
    }

    /**
     * Hide loading spinner
     */
    hideLoadingSpinner(loadingId) {
        const loadingInfo = this.loadingOverlays.get(loadingId);
        
        if (!loadingInfo) {
            console.warn('Loading spinner not found:', loadingId);
            return;
        }

        const { overlay } = loadingInfo;
        
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }

        this.loadingOverlays.delete(loadingId);
        console.log(`✅ Loading spinner hidden: ${loadingId}`);
    }

    /**
     * Hide all loading spinners
     */
    hideAllLoading() {
        const loadingIds = Array.from(this.loadingOverlays.keys());
        loadingIds.forEach(id => this.hideLoadingSpinner(id));
        console.log('✅ All loading spinners hidden');
    }

    /**
     * Show progress bar
     */
    showProgress(target, progress, options = {}) {
        const targetElement = typeof target === 'string' ? 
            document.querySelector(target) : target;

        if (!targetElement) {
            console.warn('Progress target not found:', target);
            return null;
        }

        const progressId = 'progress_' + Date.now();
        let progressBar = targetElement.querySelector('.mkcg-progress-bar');

        // Create progress bar if it doesn't exist
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.className = 'mkcg-progress-bar';
            progressBar.innerHTML = `
                <div class="mkcg-progress-bar__fill ${options.animated ? 'mkcg-progress-bar__fill--animated' : ''}"></div>
            `;
            targetElement.appendChild(progressBar);
        }

        const fill = progressBar.querySelector('.mkcg-progress-bar__fill');
        if (fill) {
            fill.style.width = Math.max(0, Math.min(100, progress)) + '%';
        }

        this.progressIndicators.set(progressId, {
            progressBar,
            target: targetElement
        });

        return progressId;
    }

    /**
     * Hide progress bar
     */
    hideProgress(progressId) {
        const progressInfo = this.progressIndicators.get(progressId);
        
        if (!progressInfo) {
            return;
        }

        const { progressBar } = progressInfo;
        
        if (progressBar && progressBar.parentNode) {
            progressBar.parentNode.removeChild(progressBar);
        }

        this.progressIndicators.delete(progressId);
    }

    /**
     * Show inline error banner
     */
    showErrorBanner(target, message, type = 'error') {
        const targetElement = typeof target === 'string' ? 
            document.querySelector(target) : target;

        if (!targetElement) {
            console.warn('Error banner target not found:', target);
            return;
        }

        // Remove existing banners
        targetElement.querySelectorAll('.mkcg-error-banner').forEach(banner => {
            banner.remove();
        });

        const banner = document.createElement('div');
        banner.className = `mkcg-error-banner mkcg-error-banner--${type}`;
        banner.textContent = message;

        targetElement.insertBefore(banner, targetElement.firstChild);
        
        return banner;
    }

    /**
     * Clear all error banners
     */
    clearErrorBanners(target = document) {
        const targetElement = typeof target === 'string' ? 
            document.querySelector(target) : target;

        targetElement.querySelectorAll('.mkcg-error-banner').forEach(banner => {
            banner.remove();
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Clear all notifications
     */
    clearAllNotifications() {
        if (this.toastContainer) {
            this.toastContainer.innerHTML = '';
        }
        this.hideAllLoading();
        this.clearErrorBanners();
        console.log('🗑️ All notifications cleared');
    }

    /**
     * Get feedback statistics
     */
    getStats() {
        return {
            activeToasts: this.toastContainer ? this.toastContainer.children.length : 0,
            activeLoading: this.loadingOverlays.size,
            activeProgress: this.progressIndicators.size,
            totalNotifications: this.notificationId
        };
    }
}

// Initialize global instance
window.EnhancedUIFeedback = new EnhancedUIFeedback();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedUIFeedback;
}

console.log('✅ Enhanced UI Feedback loaded successfully');
