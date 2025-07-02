/**
 * Enhanced Error Handler - User-Friendly Error Management
 * Converts technical errors into actionable user messages
 */

class EnhancedErrorHandler {
    constructor() {
        this.errorClassifications = {
            network: {
                patterns: [/network|fetch|timeout|connection|offline/i],
                severity: 'warning',
                category: 'connectivity'
            },
            server: {
                patterns: [/500|502|503|504|internal server|service unavailable/i],
                severity: 'error',
                category: 'server'
            },
            validation: {
                patterns: [/validation|required|invalid|missing|empty/i],
                severity: 'info',
                category: 'user_input'
            },
            permission: {
                patterns: [/403|401|unauthorized|permission|nonce|security/i],
                severity: 'error',
                category: 'authorization'
            },
            timeout: {
                patterns: [/timeout|aborted|took too long/i],
                severity: 'warning',
                category: 'performance'
            },
            duplicate: {
                patterns: [/duplicate|already exists|conflict/i],
                severity: 'info',
                category: 'data_conflict'
            }
        };

        this.userMessages = {
            network: {
                title: 'Connection Issue',
                message: 'Unable to connect to the server. Please check your internet connection and try again.',
                actions: ['Check your internet connection', 'Try again in a moment', 'Contact support if the problem persists']
            },
            server: {
                title: 'Server Error',
                message: 'The server encountered an issue while processing your request.',
                actions: ['Wait a moment and try again', 'The issue has been automatically reported', 'Contact support if this continues']
            },
            validation: {
                title: 'Input Required',
                message: 'Please check your input and fill in all required fields.',
                actions: ['Review the highlighted fields', 'Ensure all required information is provided', 'Try submitting again']
            },
            permission: {
                title: 'Access Denied',
                message: 'You don\'t have permission to perform this action, or your session has expired.',
                actions: ['Refresh the page and try again', 'Log out and log back in', 'Contact an administrator for access']
            },
            timeout: {
                title: 'Request Timeout',
                message: 'The operation is taking longer than expected.',
                actions: ['Wait for the operation to complete', 'Try again with a smaller request', 'Check your internet connection']
            },
            duplicate: {
                title: 'Already Exists',
                message: 'This information already exists in the system.',
                actions: ['Check for existing entries', 'Modify your input to make it unique', 'Use the existing entry instead']
            },
            generic: {
                title: 'Something Went Wrong',
                message: 'An unexpected error occurred. The issue has been logged for review.',
                actions: ['Try the operation again', 'Refresh the page if the problem persists', 'Contact support with details of what you were doing']
            }
        };

        this.errorLog = [];
        this.maxLogSize = 100;
        
        console.log('🛡️ Enhanced Error Handler initialized');
    }

    /**
     * Main error handling entry point
     */
    handleError(error, context = {}) {
        console.log('🔍 Enhanced Error Handler: Processing error', { error, context });

        // Log the error
        this.logError(error, context);

        // Classify the error
        const classification = this.classifyError(error);
        
        // Get user-friendly message
        const userMessage = this.getUserFriendlyMessage(error, classification);
        
        // Determine how to display the error
        const displayOptions = {
            type: classification.severity,
            category: classification.category,
            showActions: true,
            autoDismiss: classification.severity === 'info',
            duration: this.getDisplayDuration(classification.severity),
            context: context
        };

        // Show the error to user
        this.displayError(userMessage, displayOptions);

        // Return classification for programmatic use
        return {
            classification,
            userMessage,
            handled: true
        };
    }

    /**
     * Classify error type and severity
     */
    classifyError(error) {
        const errorText = this.extractErrorText(error);
        
        // Check each classification pattern
        for (const [type, config] of Object.entries(this.errorClassifications)) {
            for (const pattern of config.patterns) {
                if (pattern.test(errorText)) {
                    console.log(`🏷️ Error classified as: ${type}`, { errorText, pattern: pattern.toString() });
                    return {
                        type,
                        severity: config.severity,
                        category: config.category,
                        confidence: 'high'
                    };
                }
            }
        }

        // Default classification
        console.log('🏷️ Error classified as: generic (no pattern match)', errorText);
        return {
            type: 'generic',
            severity: 'error',
            category: 'unknown',
            confidence: 'low'
        };
    }

    /**
     * Extract error text from various error formats
     */
    extractErrorText(error) {
        if (typeof error === 'string') {
            return error;
        }

        if (error instanceof Error) {
            return error.message || error.toString();
        }

        if (error && typeof error === 'object') {
            return error.message || 
                   error.data?.message || 
                   error.error?.message || 
                   JSON.stringify(error);
        }

        return 'Unknown error occurred';
    }

    /**
     * Generate user-friendly error message
     */
    getUserFriendlyMessage(error, classification) {
        const baseMessage = this.userMessages[classification.type] || this.userMessages.generic;
        
        // Extract specific details from error for enhanced messaging
        const errorDetails = this.extractErrorDetails(error);
        
        const message = {
            title: baseMessage.title,
            message: this.personalizeMessage(baseMessage.message, errorDetails),
            actions: baseMessage.actions,
            details: errorDetails,
            severity: classification.severity,
            timestamp: new Date().toISOString()
        };

        // Add specific context for certain error types
        if (classification.type === 'validation') {
            message.fields = this.extractValidationFields(error);
        }

        if (classification.type === 'network' && navigator.onLine === false) {
            message.message = 'You appear to be offline. Please check your internet connection.';
        }

        console.log('💬 Generated user-friendly message:', message);
        return message;
    }

    /**
     * Extract additional error details for context
     */
    extractErrorDetails(error) {
        const details = {
            code: null,
            field: null,
            operation: null,
            timestamp: new Date().toISOString()
        };

        if (error && typeof error === 'object') {
            details.code = error.code || error.status || null;
            details.field = error.field || null;
            details.operation = error.requestConfig?.action || null;
        }

        return details;
    }

    /**
     * Extract validation field information
     */
    extractValidationFields(error) {
        const fields = [];
        const errorText = this.extractErrorText(error);
        
        // Common field patterns
        const fieldPatterns = [
            /field['"]\s*['"]([\w_]+)['"]/gi,
            /([\w_]+)\s+is\s+required/gi,
            /missing\s+([\w_]+)/gi
        ];

        for (const pattern of fieldPatterns) {
            let match;
            while ((match = pattern.exec(errorText)) !== null) {
                if (match[1] && !fields.includes(match[1])) {
                    fields.push(match[1]);
                }
            }
        }

        return fields;
    }

    /**
     * Personalize message based on error details
     */
    personalizeMessage(baseMessage, details) {
        let message = baseMessage;

        // Add operation context if available
        if (details.operation) {
            const operationNames = {
                'mkcg_save_topics': 'saving topics',
                'mkcg_generate_topics': 'generating topics',
                'mkcg_save_authority_hook': 'saving authority hook',
                'mkcg_get_topics_data': 'loading topics data'
            };
            
            const operationName = operationNames[details.operation] || details.operation;
            message = `There was an issue while ${operationName}. ${message}`;
        }

        return message;
    }

    /**
     * Display error to user using enhanced UI feedback
     */
    displayError(userMessage, options) {
        console.log('📢 Displaying error to user:', { userMessage, options });

        // Use enhanced UI feedback if available
        if (window.EnhancedUIFeedback) {
            window.EnhancedUIFeedback.showErrorMessage(userMessage, options);
        } else {
            // Fallback to enhanced alert
            this.showEnhancedAlert(userMessage, options);
        }

        // Log to console for debugging
        console.error('❌ Error displayed to user:', userMessage.title, userMessage.message);
    }

    /**
     * Enhanced alert fallback when UI feedback not available
     */
    showEnhancedAlert(userMessage, options) {
        const alertMessage = `${userMessage.title}\n\n${userMessage.message}`;
        
        if (userMessage.actions && userMessage.actions.length > 0) {
            const actionsText = '\n\nSuggested actions:\n• ' + userMessage.actions.join('\n• ');
            alert(alertMessage + actionsText);
        } else {
            alert(alertMessage);
        }
    }

    /**
     * Get display duration based on severity
     */
    getDisplayDuration(severity) {
        const durations = {
            'info': 5000,     // 5 seconds
            'warning': 8000,  // 8 seconds
            'error': 0        // Manual dismiss
        };

        return durations[severity] || 6000;
    }

    /**
     * Log error for debugging and analytics
     */
    logError(error, context) {
        const logEntry = {
            timestamp: new Date().toISOString(),
            error: this.serializeError(error),
            context: context,
            userAgent: navigator.userAgent,
            url: window.location.href,
            networkStatus: navigator.onLine ? 'online' : 'offline'
        };

        this.errorLog.push(logEntry);

        // Keep log size manageable
        if (this.errorLog.length > this.maxLogSize) {
            this.errorLog = this.errorLog.slice(-this.maxLogSize);
        }

        // Console logging for development
        console.error('📝 Error logged:', logEntry);

        // Send to analytics if configured
        this.sendToAnalytics(logEntry);
    }

    /**
     * Serialize error for logging
     */
    serializeError(error) {
        if (typeof error === 'string') {
            return { message: error, type: 'string' };
        }

        if (error instanceof Error) {
            return {
                name: error.name,
                message: error.message,
                stack: error.stack,
                type: 'Error'
            };
        }

        if (error && typeof error === 'object') {
            try {
                return {
                    ...error,
                    type: 'object',
                    serialized: JSON.stringify(error)
                };
            } catch (e) {
                return {
                    message: 'Error object could not be serialized',
                    type: 'unserializable'
                };
            }
        }

        return {
            message: String(error),
            type: typeof error
        };
    }

    /**
     * Send error to analytics (placeholder for future implementation)
     */
    sendToAnalytics(logEntry) {
        // Future implementation for error tracking
        // Could send to services like Sentry, LogRocket, etc.
        console.log('📊 Error analytics (placeholder):', logEntry.error.message);
    }

    /**
     * Get error statistics
     */
    getErrorStats() {
        const stats = {
            total: this.errorLog.length,
            byType: {},
            bySeverity: {},
            recent: this.errorLog.slice(-10)
        };

        this.errorLog.forEach(entry => {
            const classification = this.classifyError(entry.error);
            
            stats.byType[classification.type] = (stats.byType[classification.type] || 0) + 1;
            stats.bySeverity[classification.severity] = (stats.bySeverity[classification.severity] || 0) + 1;
        });

        return stats;
    }

    /**
     * Clear error log
     */
    clearErrorLog() {
        this.errorLog = [];
        console.log('🗑️ Error log cleared');
    }

    /**
     * Check if error should be suppressed (for duplicate/spam errors)
     */
    shouldSuppressError(error) {
        const errorText = this.extractErrorText(error);
        const recentErrors = this.errorLog.slice(-5);
        
        // Check for duplicate errors in recent history
        const duplicateCount = recentErrors.filter(entry => 
            this.extractErrorText(entry.error) === errorText
        ).length;

        return duplicateCount >= 3; // Suppress after 3 duplicates
    }

    /**
     * Public method for manual error reporting
     */
    reportError(message, context = {}) {
        const error = new Error(message);
        error.userReported = true;
        return this.handleError(error, context);
    }
}

// Initialize global instance
window.EnhancedErrorHandler = new EnhancedErrorHandler();

// Global error handler for uncaught errors
window.addEventListener('error', (event) => {
    console.log('🚨 Uncaught error detected:', event.error);
    window.EnhancedErrorHandler.handleError(event.error, {
        type: 'uncaught',
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno
    });
});

// Global handler for unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
    console.log('🚨 Unhandled promise rejection:', event.reason);
    window.EnhancedErrorHandler.handleError(event.reason, {
        type: 'unhandled_promise'
    });
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedErrorHandler;
}

console.log('✅ Enhanced Error Handler loaded successfully');
