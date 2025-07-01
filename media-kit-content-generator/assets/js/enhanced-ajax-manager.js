/**
 * Enhanced AJAX Manager - Enterprise-Grade Error Handling
 * Replaces basic fetch() with comprehensive error recovery and retry logic
 */

class EnhancedAjaxManager {
    constructor() {
        this.requestQueue = new Map();
        this.activeRequests = new Map();
        this.retryConfig = {
            maxAttempts: 3,
            baseDelay: 1000,      // 1 second
            maxDelay: 10000,      // 10 seconds
            backoffMultiplier: 2,
            retryableStatusCodes: [500, 502, 503, 504, 408, 429]
        };
        this.timeout = 30000; // 30 seconds
        this.networkStatus = navigator.onLine ? 'online' : 'offline';
        this.setupNetworkDetection();
        this.requestId = 0;
        
        console.log('🚀 Enhanced AJAX Manager initialized with enterprise-grade error handling');
    }

    /**
     * Setup network detection for offline/online transitions
     */
    setupNetworkDetection() {
        window.addEventListener('online', () => {
            this.networkStatus = 'online';
            console.log('📡 Network: Online - Retrying queued requests');
            this.retryQueuedRequests();
            this.triggerNetworkEvent('online');
        });

        window.addEventListener('offline', () => {
            this.networkStatus = 'offline';
            console.log('📡 Network: Offline - Queuing requests for retry');
            this.triggerNetworkEvent('offline');
        });
    }

    /**
     * Enhanced AJAX request with automatic retry and error recovery
     */
    async makeRequest(action, data = {}, options = {}) {
        const requestId = ++this.requestId;
        const requestConfig = {
            id: requestId,
            action,
            data,
            options: {
                timeout: options.timeout || this.timeout,
                retryAttempts: options.retryAttempts || this.retryConfig.maxAttempts,
                onStart: options.onStart || (() => {}),
                onProgress: options.onProgress || (() => {}),
                onSuccess: options.onSuccess || (() => {}),
                onError: options.onError || (() => {}),
                onComplete: options.onComplete || (() => {}),
                validateResponse: options.validateResponse || true,
                skipRetryOnValidation: options.skipRetryOnValidation || false
            },
            attempt: 1,
            startTime: Date.now()
        };

        console.log(`🔄 [${requestId}] Starting AJAX request: ${action}`, { data, options });

        // Check for duplicate requests
        const requestKey = this.generateRequestKey(action, data);
        if (this.activeRequests.has(requestKey)) {
            console.log(`⚠️ [${requestId}] Duplicate request detected, using existing`, requestKey);
            return this.activeRequests.get(requestKey);
        }

        // Create request promise
        const requestPromise = this.executeRequest(requestConfig);
        this.activeRequests.set(requestKey, requestPromise);

        try {
            const result = await requestPromise;
            this.activeRequests.delete(requestKey);
            return result;
        } catch (error) {
            this.activeRequests.delete(requestKey);
            throw error;
        }
    }

    /**
     * Execute the actual request with retry logic
     */
    async executeRequest(config) {
        const { id, action, data, options, attempt } = config;

        // Network connectivity check
        if (this.networkStatus === 'offline') {
            console.log(`📡 [${id}] Network offline, queuing request for retry`);
            return this.queueForRetry(config);
        }

        // Start callback
        if (attempt === 1) {
            options.onStart();
        }

        try {
            // Prepare request data
            const requestData = await this.prepareRequestData(action, data);
            
            // Create AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                controller.abort();
            }, options.timeout);

            console.log(`📡 [${id}] Sending request attempt ${attempt}/${options.retryAttempts}`);

            // Make the actual request
            const response = await fetch(this.getAjaxUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: requestData,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            // Log response details
            console.log(`📡 [${id}] Response received:`, {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            // Handle non-OK responses
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            // Parse JSON response
            const result = await response.json();
            
            // Validate response if enabled
            if (options.validateResponse) {
                this.validateResponse(result, action);
            }

            // Log successful completion
            const duration = Date.now() - config.startTime;
            console.log(`✅ [${id}] Request completed successfully in ${duration}ms`);

            // Success callback
            options.onSuccess(result.data || result);
            options.onComplete();

            return result.data || result;

        } catch (error) {
            console.log(`❌ [${id}] Request failed on attempt ${attempt}:`, error.message);

            // Determine if retry is appropriate
            if (this.shouldRetry(error, config)) {
                return this.handleRetry(config);
            } else {
                // No more retries, handle final error
                const enhancedError = this.enhanceError(error, config);
                options.onError(enhancedError);
                options.onComplete();
                throw enhancedError;
            }
        }
    }

    /**
     * Prepare request data with proper formatting
     */
    async prepareRequestData(action, data) {
        const requestData = new URLSearchParams();
        requestData.append('action', action);
        
        // Add nonce from multiple possible sources
        const nonce = this.getNonce();
        if (nonce) {
            requestData.append('nonce', nonce);
            requestData.append('security', nonce);
        }

        // Add data parameters
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                if (typeof data[key] === 'object') {
                    requestData.append(key, JSON.stringify(data[key]));
                } else {
                    requestData.append(key, data[key]);
                }
            }
        });

        return requestData.toString();
    }

    /**
     * Get nonce from multiple possible sources
     */
    getNonce() {
        // Try multiple nonce sources
        const nonceSources = [
            window.mkcg_vars?.nonce,
            window.topics_vars?.nonce,
            window.questions_vars?.nonce,
            document.querySelector('#topics-generator-nonce')?.value,
            document.querySelector('#questions-generator-nonce')?.value,
            document.querySelector('input[name*="nonce"]')?.value
        ];

        for (const nonce of nonceSources) {
            if (nonce) {
                return nonce;
            }
        }

        console.warn('⚠️ No nonce found - request may fail security validation');
        return '';
    }

    /**
     * Get AJAX URL
     */
    getAjaxUrl() {
        return window.ajaxurl || 
               window.mkcg_vars?.ajax_url || 
               '/wp-admin/admin-ajax.php';
    }

    /**
     * Validate response data
     */
    validateResponse(response, action) {
        if (!response) {
            throw new Error('Empty response received');
        }

        // Check for WordPress AJAX error format
        if (response.success === false) {
            const message = response.data?.message || response.data || 'Request failed';
            throw new Error(`Server error: ${message}`);
        }

        // Check for specific action requirements
        if (action.includes('get_') && !response.data && response.success !== true) {
            throw new Error('Expected data not found in response');
        }

        console.log(`✅ Response validation passed for action: ${action}`);
    }

    /**
     * Determine if request should be retried
     */
    shouldRetry(error, config) {
        const { attempt, options } = config;

        // Don't retry if max attempts reached
        if (attempt >= options.retryAttempts) {
            console.log(`❌ Max retry attempts (${options.retryAttempts}) reached`);
            return false;
        }

        // Don't retry validation errors unless specifically enabled
        if (error.message.includes('validation') && options.skipRetryOnValidation) {
            console.log(`❌ Validation error - not retrying`);
            return false;
        }

        // Check if error is retryable
        const isRetryableError = this.isRetryableError(error);
        
        console.log(`🔄 Error retry evaluation:`, {
            attempt: `${attempt}/${options.retryAttempts}`,
            isRetryable: isRetryableError,
            error: error.message
        });

        return isRetryableError;
    }

    /**
     * Check if error is retryable
     */
    isRetryableError(error) {
        const message = error.message.toLowerCase();
        
        // Network errors (retryable)
        if (message.includes('network') || 
            message.includes('fetch') || 
            message.includes('timeout') ||
            message.includes('aborted') ||
            message.includes('connection')) {
            return true;
        }

        // Server errors (retryable)
        const statusMatch = message.match(/http (\d+)/);
        if (statusMatch) {
            const status = parseInt(statusMatch[1]);
            return this.retryConfig.retryableStatusCodes.includes(status);
        }

        // WordPress specific errors (retryable)
        if (message.includes('internal server error') ||
            message.includes('service unavailable') ||
            message.includes('gateway timeout')) {
            return true;
        }

        return false;
    }

    /**
     * Handle retry with exponential backoff
     */
    async handleRetry(config) {
        const { attempt } = config;
        const delay = this.calculateBackoffDelay(attempt);
        
        console.log(`⏰ Retrying in ${delay}ms (attempt ${attempt + 1})`);
        
        // Update progress if callback available
        if (config.options.onProgress) {
            config.options.onProgress({
                type: 'retry',
                attempt: attempt + 1,
                maxAttempts: config.options.retryAttempts,
                delay: delay
            });
        }

        // Wait for backoff delay
        await this.delay(delay);

        // Increment attempt and retry
        config.attempt++;
        return this.executeRequest(config);
    }

    /**
     * Calculate exponential backoff delay
     */
    calculateBackoffDelay(attempt) {
        const delay = this.retryConfig.baseDelay * Math.pow(this.retryConfig.backoffMultiplier, attempt - 1);
        return Math.min(delay, this.retryConfig.maxDelay);
    }

    /**
     * Queue request for retry when network comes online
     */
    async queueForRetry(config) {
        return new Promise((resolve, reject) => {
            const queueEntry = {
                config,
                resolve,
                reject,
                timestamp: Date.now()
            };

            this.requestQueue.set(config.id, queueEntry);
            
            console.log(`📋 Request ${config.id} queued for network retry`);

            // Notify user about offline state
            if (config.options.onProgress) {
                config.options.onProgress({
                    type: 'queued',
                    message: 'Request queued - will retry when network is available'
                });
            }
        });
    }

    /**
     * Retry all queued requests when network comes online
     */
    async retryQueuedRequests() {
        const queuedRequests = Array.from(this.requestQueue.values());
        
        if (queuedRequests.length === 0) {
            return;
        }

        console.log(`🔄 Retrying ${queuedRequests.length} queued requests`);

        for (const queueEntry of queuedRequests) {
            try {
                const result = await this.executeRequest(queueEntry.config);
                queueEntry.resolve(result);
                this.requestQueue.delete(queueEntry.config.id);
            } catch (error) {
                queueEntry.reject(error);
                this.requestQueue.delete(queueEntry.config.id);
            }
        }
    }

    /**
     * Enhance error with additional context
     */
    enhanceError(error, config) {
        const enhancedError = new Error(error.message);
        enhancedError.originalError = error;
        enhancedError.requestConfig = {
            action: config.action,
            attempt: config.attempt,
            maxAttempts: config.options.retryAttempts,
            duration: Date.now() - config.startTime
        };
        enhancedError.networkStatus = this.networkStatus;
        enhancedError.timestamp = new Date().toISOString();
        
        return enhancedError;
    }

    /**
     * Generate unique key for request deduplication
     */
    generateRequestKey(action, data) {
        const dataString = JSON.stringify(data);
        return `${action}:${this.hashCode(dataString)}`;
    }

    /**
     * Simple hash function for request deduplication
     */
    hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return hash.toString();
    }

    /**
     * Trigger network status events
     */
    triggerNetworkEvent(status) {
        const event = new CustomEvent('mkcg:network', {
            detail: { status, timestamp: Date.now() }
        });
        window.dispatchEvent(event);
    }

    /**
     * Utility delay function
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Get current status and diagnostics
     */
    getStatus() {
        return {
            networkStatus: this.networkStatus,
            activeRequests: this.activeRequests.size,
            queuedRequests: this.requestQueue.size,
            totalRequests: this.requestId,
            retryConfig: this.retryConfig
        };
    }
}

// Initialize global instance
window.EnhancedAjaxManager = new EnhancedAjaxManager();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedAjaxManager;
}

console.log('✅ Enhanced AJAX Manager loaded successfully');
