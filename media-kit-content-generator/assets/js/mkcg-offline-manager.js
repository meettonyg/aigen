/**
 * MKCG Offline Manager - Network Awareness & Offline Capability
 * Provides seamless offline/online transitions with request queuing
 */

class MKCG_OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.offlineQueue = new Map();
        this.pendingOperations = new Set();
        this.networkStatusListeners = [];
        this.offlineIndicator = null;
        this.localStorageKey = 'mkcg_offline_data';
        this.maxOfflineStorage = 50; // Max number of items to store offline
        
        this.init();
        console.log('🌐 MKCG Offline Manager initialized');
    }

    /**
     * Initialize offline manager
     */
    init() {
        this.setupNetworkListeners();
        this.createOfflineIndicator();
        this.loadOfflineData();
        this.bindOfflineDetection();
        
        // Initial status check
        this.updateNetworkStatus(this.isOnline);
    }

    /**
     * Setup network event listeners
     */
    setupNetworkListeners() {
        window.addEventListener('online', () => {
            console.log('🌐 Network: Back online - processing queued operations');
            this.isOnline = true;
            this.updateNetworkStatus(true);
            this.processOfflineQueue();
        });

        window.addEventListener('offline', () => {
            console.log('🌐 Network: Gone offline - queuing operations');
            this.isOnline = false;
            this.updateNetworkStatus(false);
            this.showOfflineNotification();
        });

        // Enhanced connectivity detection
        this.periodicConnectivityCheck();
    }

    /**
     * Create network status indicator
     */
    createOfflineIndicator() {
        this.offlineIndicator = document.createElement('div');
        this.offlineIndicator.id = 'mkcg-network-status';
        this.offlineIndicator.style.cssText = `
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10001;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: white;
            display: none;
            animation: slideDown 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        `;

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                to { transform: translateX(-50%) translateY(0); opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateX(-50%) translateY(0); opacity: 1; }
                to { transform: translateX(-50%) translateY(-100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(this.offlineIndicator);
    }

    /**
     * Update network status display
     */
    updateNetworkStatus(isOnline) {
        if (isOnline) {
            this.offlineIndicator.style.backgroundColor = '#27ae60';
            this.offlineIndicator.innerHTML = '🌐 Connected - All features available';
            this.offlineIndicator.style.display = 'block';
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                this.offlineIndicator.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => {
                    this.offlineIndicator.style.display = 'none';
                    this.offlineIndicator.style.animation = 'slideDown 0.3s ease';
                }, 300);
            }, 3000);
        } else {
            this.offlineIndicator.style.backgroundColor = '#e74c3c';
            this.offlineIndicator.innerHTML = '📱 Offline - Changes saved locally';
            this.offlineIndicator.style.display = 'block';
        }

        // Notify listeners
        this.networkStatusListeners.forEach(listener => {
            try {
                listener(isOnline);
            } catch (error) {
                console.error('Error in network status listener:', error);
            }
        });
    }

    /**
     * Show offline notification with guidance
     */
    showOfflineNotification() {
        if (window.EnhancedUIFeedback) {
            window.EnhancedUIFeedback.showToast({
                title: 'You\'re Offline',
                message: 'Don\'t worry! Your changes are being saved locally and will sync when you\'re back online.',
                actions: [
                    'Continue working - all data is preserved',
                    'Changes will automatically sync when connected'
                ]
            }, 'info', 8000);
        }
    }

    /**
     * Queue operation for when back online
     */
    queueOperation(operationType, data, callbacks = {}) {
        const operationId = 'op_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        const operation = {
            id: operationId,
            type: operationType,
            data: data,
            callbacks: callbacks,
            timestamp: Date.now(),
            retryCount: 0,
            maxRetries: 3
        };

        this.offlineQueue.set(operationId, operation);
        this.saveOfflineData();

        console.log(`📋 Queued operation: ${operationType}`, operation);

        // Show user feedback
        if (window.EnhancedUIFeedback) {
            window.EnhancedUIFeedback.showToast(
                `${this.getOperationDisplayName(operationType)} queued for sync`,
                'info',
                3000
            );
        }

        return operationId;
    }

    /**
     * Process all queued operations when back online
     */
    async processOfflineQueue() {
        if (!this.isOnline || this.offlineQueue.size === 0) {
            return;
        }

        console.log(`🔄 Processing ${this.offlineQueue.size} queued operations`);

        // Show processing notification
        if (window.EnhancedUIFeedback) {
            window.EnhancedUIFeedback.showToast({
                title: 'Syncing Changes',
                message: `Uploading ${this.offlineQueue.size} queued operations...`,
                actions: ['Please wait while we sync your offline changes']
            }, 'info', 5000);
        }

        const operations = Array.from(this.offlineQueue.values());
        let successCount = 0;
        let failureCount = 0;

        for (const operation of operations) {
            try {
                const success = await this.executeQueuedOperation(operation);
                if (success) {
                    successCount++;
                    this.offlineQueue.delete(operation.id);
                } else {
                    failureCount++;
                    operation.retryCount++;
                    
                    // Remove if max retries exceeded
                    if (operation.retryCount >= operation.maxRetries) {
                        this.offlineQueue.delete(operation.id);
                        console.warn(`Max retries exceeded for operation:`, operation);
                    }
                }
            } catch (error) {
                console.error(`Failed to execute queued operation:`, operation, error);
                failureCount++;
                operation.retryCount++;
                
                if (operation.retryCount >= operation.maxRetries) {
                    this.offlineQueue.delete(operation.id);
                }
            }
        }

        // Save updated queue
        this.saveOfflineData();

        // Show completion notification
        if (window.EnhancedUIFeedback) {
            if (failureCount === 0) {
                window.EnhancedUIFeedback.showToast(
                    `✅ Successfully synced ${successCount} operations`,
                    'success',
                    4000
                );
            } else {
                window.EnhancedUIFeedback.showToast({
                    title: 'Sync Partially Complete',
                    message: `Synced ${successCount} operations, ${failureCount} failed`,
                    actions: ['Failed operations will retry automatically']
                }, 'warning', 6000);
            }
        }

        console.log(`✅ Queue processing complete: ${successCount} success, ${failureCount} failed`);
    }

    /**
     * Execute a queued operation
     */
    async executeQueuedOperation(operation) {
        console.log(`🔄 Executing queued operation: ${operation.type}`, operation);

        try {
            // Use Enhanced AJAX Manager if available
            if (window.EnhancedAjaxManager && window.MKCG_FormUtils) {
                const result = await window.MKCG_FormUtils.wp.makeAjaxRequest(
                    operation.type,
                    operation.data,
                    {
                        timeout: 15000,
                        retryAttempts: 1 // Limited retries for queued operations
                    }
                );

                // Call success callback if provided
                if (operation.callbacks.onSuccess) {
                    operation.callbacks.onSuccess(result);
                }

                return true;
            } else {
                console.warn('Enhanced AJAX Manager not available for queued operation');
                return false;
            }
        } catch (error) {
            console.error(`Queued operation failed:`, operation, error);
            
            // Call error callback if provided
            if (operation.callbacks.onError) {
                operation.callbacks.onError(error);
            }

            return false;
        }
    }

    /**
     * Save offline data to localStorage
     */
    saveOfflineData() {
        try {
            const offlineData = {
                queue: Array.from(this.offlineQueue.entries()),
                timestamp: Date.now()
            };

            // Limit storage size
            if (offlineData.queue.length > this.maxOfflineStorage) {
                offlineData.queue = offlineData.queue.slice(-this.maxOfflineStorage);
                console.warn(`Offline storage trimmed to ${this.maxOfflineStorage} items`);
            }

            localStorage.setItem(this.localStorageKey, JSON.stringify(offlineData));
            console.log(`💾 Saved ${offlineData.queue.length} operations to offline storage`);
        } catch (error) {
            console.error('Failed to save offline data:', error);
        }
    }

    /**
     * Load offline data from localStorage
     */
    loadOfflineData() {
        try {
            const storedData = localStorage.getItem(this.localStorageKey);
            if (storedData) {
                const offlineData = JSON.parse(storedData);
                
                // Check if data is not too old (24 hours)
                const maxAge = 24 * 60 * 60 * 1000; // 24 hours
                if (Date.now() - offlineData.timestamp < maxAge) {
                    this.offlineQueue = new Map(offlineData.queue);
                    console.log(`📂 Loaded ${this.offlineQueue.size} operations from offline storage`);
                } else {
                    console.log('Offline data too old, clearing');
                    localStorage.removeItem(this.localStorageKey);
                }
            }
        } catch (error) {
            console.error('Failed to load offline data:', error);
            localStorage.removeItem(this.localStorageKey);
        }
    }

    /**
     * Enhanced connectivity detection
     */
    periodicConnectivityCheck() {
        setInterval(() => {
            // Test actual connectivity, not just navigator.onLine
            this.testConnectivity().then(isConnected => {
                if (isConnected !== this.isOnline) {
                    this.isOnline = isConnected;
                    this.updateNetworkStatus(isConnected);
                    
                    if (isConnected) {
                        this.processOfflineQueue();
                    }
                }
            });
        }, 30000); // Check every 30 seconds
    }

    /**
     * Test actual network connectivity
     */
    async testConnectivity() {
        try {
            // Try to fetch a small resource from the same domain
            const response = await fetch(window.location.origin + '/favicon.ico', {
                method: 'HEAD',
                mode: 'no-cors',
                cache: 'no-cache',
                timeout: 5000
            });
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Bind offline detection to forms and inputs
     */
    bindOfflineDetection() {
        // Add event listeners to detect when user tries to submit while offline
        document.addEventListener('submit', (event) => {
            if (!this.isOnline) {
                event.preventDefault();
                this.handleOfflineSubmit(event);
            }
        });

        // Monitor AJAX requests
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            if (!this.isOnline) {
                return Promise.reject(new Error('Offline - request queued for later'));
            }
            return originalFetch(...args);
        };
    }

    /**
     * Handle form submission while offline
     */
    handleOfflineSubmit(event) {
        const form = event.target;
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        // Determine operation type from form action or data
        const operationType = this.determineOperationType(form, data);
        
        // Queue the operation
        this.queueOperation(operationType, data);

        // Show user feedback
        if (window.EnhancedUIFeedback) {
            window.EnhancedUIFeedback.showToast({
                title: 'Saved Offline',
                message: 'Your form data has been saved locally and will be submitted when you\'re back online.',
                actions: ['Continue working - all changes are preserved']
            }, 'success', 5000);
        }
    }

    /**
     * Determine operation type from form or data
     */
    determineOperationType(form, data) {
        // Check form action attribute
        if (form.action && form.action.includes('save_topics')) {
            return 'mkcg_save_topics_data';
        }
        
        if (form.action && form.action.includes('authority_hook')) {
            return 'mkcg_save_authority_hook';
        }

        // Check data content
        if (data.topics || data.topic_1) {
            return 'mkcg_save_topics_data';
        }
        
        if (data.who || data.result || data.when || data.how) {
            return 'mkcg_save_authority_hook';
        }

        // Default
        return 'mkcg_save_field';
    }

    /**
     * Get user-friendly operation name
     */
    getOperationDisplayName(operationType) {
        const names = {
            'mkcg_save_topics_data': 'Topic changes',
            'mkcg_save_authority_hook': 'Authority hook',
            'mkcg_save_topic': 'Topic',
            'mkcg_save_field': 'Form field',
            'mkcg_generate_topics': 'Topic generation'
        };

        return names[operationType] || 'Data';
    }

    /**
     * Add network status listener
     */
    addNetworkStatusListener(callback) {
        this.networkStatusListeners.push(callback);
    }

    /**
     * Remove network status listener
     */
    removeNetworkStatusListener(callback) {
        const index = this.networkStatusListeners.indexOf(callback);
        if (index > -1) {
            this.networkStatusListeners.splice(index, 1);
        }
    }

    /**
     * Get current network status
     */
    getNetworkStatus() {
        return {
            isOnline: this.isOnline,
            queuedOperations: this.offlineQueue.size,
            pendingOperations: this.pendingOperations.size
        };
    }

    /**
     * Clear offline queue (for testing/debugging)
     */
    clearOfflineQueue() {
        this.offlineQueue.clear();
        this.saveOfflineData();
        console.log('🗑️ Offline queue cleared');
    }

    /**
     * Force sync (for manual trigger)
     */
    forcSync() {
        if (this.isOnline) {
            this.processOfflineQueue();
        } else {
            if (window.EnhancedUIFeedback) {
                window.EnhancedUIFeedback.showToast(
                    'Cannot sync while offline',
                    'warning',
                    3000
                );
            }
        }
    }
}

// Initialize global instance
window.MKCG_OfflineManager = new MKCG_OfflineManager();

// Integration with Topics Generator
document.addEventListener('DOMContentLoaded', () => {
    if (window.TopicsGenerator) {
        // Add network status awareness to Topics Generator
        window.MKCG_OfflineManager.addNetworkStatusListener((isOnline) => {
            if (window.TopicsGenerator.updateNetworkStatus) {
                window.TopicsGenerator.updateNetworkStatus(isOnline);
            }
        });
    }
});

console.log('✅ MKCG Offline Manager loaded successfully');
