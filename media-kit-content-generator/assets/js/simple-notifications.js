/**
 * Simple Notification System - Clean, Lightweight UX Enhancement
 * Replaces complex UI feedback systems with a straightforward notification solution
 * 
 * FEATURES:
 * ✅ Simple showNotification() function
 * ✅ Auto-dismiss after 3 seconds
 * ✅ Support for success, error, warning, info types
 * ✅ Clean CSS styling and animations
 * ✅ Non-blocking user experience
 * ✅ No dependencies
 */

const SimpleNotifications = {
    container: null,
    notificationCount: 0,

    /**
     * Initialize the notification system
     */
    init: function() {
        this.createContainer();
        this.injectStyles();
        console.log('✅ Simple Notifications system initialized');
    },

    /**
     * Create notification container
     */
    createContainer: function() {
        if (this.container) return;

        this.container = document.createElement('div');
        this.container.id = 'simple-notifications-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            pointer-events: none;
        `;
        
        document.body.appendChild(this.container);
    },

    /**
     * Inject CSS styles
     */
    injectStyles: function() {
        const styleId = 'simple-notifications-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .simple-notification {
                background: white;
                color: #333;
                padding: 12px 20px;
                margin-bottom: 10px;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-left: 4px solid #3498db;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.4;
                pointer-events: auto;
                cursor: pointer;
                transition: all 0.3s ease;
                animation: slideIn 0.3s ease;
                max-width: 100%;
                word-wrap: break-word;
            }

            .simple-notification:hover {
                transform: translateX(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            .simple-notification--success {
                border-left-color: #27ae60;
                background: #f8fff9;
            }

            .simple-notification--error {
                border-left-color: #e74c3c;
                background: #fff8f8;
            }

            .simple-notification--warning {
                border-left-color: #f39c12;
                background: #fffdf8;
            }

            .simple-notification--info {
                border-left-color: #3498db;
                background: #f8fbff;
            }

            .simple-notification__content {
                display: flex;
                align-items: flex-start;
                gap: 8px;
            }

            .simple-notification__icon {
                font-size: 16px;
                line-height: 1;
                flex-shrink: 0;
                margin-top: 1px;
            }

            .simple-notification__message {
                flex: 1;
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            @media (max-width: 480px) {
                #simple-notifications-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
            }
        `;
        
        document.head.appendChild(style);
    },

    /**
     * Show notification - Main function
     */
    showNotification: function(message, type = 'info', duration = 3000) {
        this.notificationCount++;
        
        const notification = this.createNotification(message, type);
        this.container.appendChild(notification);
        
        console.log(`📢 Simple notification shown: ${type} - ${message}`);
        
        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotification(notification);
            }, duration);
        }
        
        // Click to dismiss
        notification.addEventListener('click', () => {
            this.removeNotification(notification);
        });
        
        return notification;
    },

    /**
     * Create notification element
     */
    createNotification: function(message, type) {
        const notification = document.createElement('div');
        notification.className = `simple-notification simple-notification--${type}`;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        const icon = icons[type] || icons.info;
        
        notification.innerHTML = `
            <div class="simple-notification__content">
                <div class="simple-notification__icon">${icon}</div>
                <div class="simple-notification__message">${this.escapeHtml(message)}</div>
            </div>
        `;
        
        return notification;
    },

    /**
     * Remove notification with animation
     */
    removeNotification: function(notification) {
        if (!notification || !notification.parentNode) return;
        
        notification.style.animation = 'slideOut 0.3s ease';
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Clear all notifications
     */
    clearAll: function() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    },

    /**
     * Convenience methods
     */
    success: function(message, duration = 3000) {
        return this.showNotification(message, 'success', duration);
    },

    error: function(message, duration = 5000) {
        return this.showNotification(message, 'error', duration);
    },

    warning: function(message, duration = 4000) {
        return this.showNotification(message, 'warning', duration);
    },

    info: function(message, duration = 3000) {
        return this.showNotification(message, 'info', duration);
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => SimpleNotifications.init());
} else {
    SimpleNotifications.init();
}

// Make globally available
window.SimpleNotifications = SimpleNotifications;

// Provide global showNotification function for easy access
window.showNotification = function(message, type = 'info', duration = 3000) {
    return SimpleNotifications.showNotification(message, type, duration);
};

console.log('✅ Simple Notifications loaded successfully');
