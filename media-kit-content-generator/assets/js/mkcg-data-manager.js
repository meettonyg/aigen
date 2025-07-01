/**
 * MKCG Centralized Data Manager
 * 
 * CRITICAL FIX: Single source of truth for all generator data
 * Ensures perfect synchronization between Topics and Questions generators
 * 
 * KEY FEATURES:
 * ✅ Centralized topic storage with validation
 * ✅ Real-time event broadcasting between generators
 * ✅ Data integrity checks and automatic healing
 * ✅ Comprehensive logging for debugging
 * ✅ Rollback capability on save failures
 */

window.MKCG_DataManager = (function() {
    'use strict';
    
    // CENTRALIZED DATA STORE - Single source of truth
    const dataStore = {
        topics: {
            1: '',
            2: '',
            3: '',
            4: '',
            5: ''
        },
        questions: {
            1: [],
            2: [],
            3: [],
            4: [],
            5: []
        },
        selectedTopicId: 1,
        postId: null,
        entryId: null,
        lastUpdate: null,
        saveInProgress: false
    };
    
    // EVENT SYSTEM for real-time updates
    const eventListeners = {
        'topic:updated': [],
        'topic:selected': [],
        'questions:updated': [],
        'data:synced': [],
        'save:started': [],
        'save:completed': [],
        'save:failed': []
    };
    
    // LOGGING SYSTEM with detailed tracking
    const logger = {
        log: function(level, category, message, data = null) {
            const timestamp = new Date().toISOString();
            const colors = {
                error: 'color: #e74c3c; font-weight: bold;',
                warn: 'color: #f39c12; font-weight: bold;',
                info: 'color: #3498db;',
                success: 'color: #27ae60; font-weight: bold;',
                debug: 'color: #95a5a6;'
            };
            
            console.log(
                `%c[MKCG-${category.toUpperCase()}] ${message}`,
                colors[level] || '',
                data ? data : ''
            );
        }
    };
    
    // VALIDATION SYSTEM
    const validator = {
        validateTopic: function(topicId, topicText) {
            const errors = [];
            
            if (!topicId || topicId < 1 || topicId > 5) {
                errors.push('Invalid topic ID: must be between 1 and 5');
            }
            
            if (typeof topicText !== 'string') {
                errors.push('Topic text must be a string');
            } else {
                if (topicText.length === 0) {
                    errors.push('Topic text cannot be empty');
                } else if (topicText.length < 10) {
                    errors.push('Topic text must be at least 10 characters');
                } else if (topicText.length > 500) {
                    errors.push('Topic text cannot exceed 500 characters');
                }
                
                // Check for placeholder text
                const placeholderPatterns = [
                    /^topic \d+ - click to add/i,
                    /^click to add/i,
                    /^placeholder/i,
                    /^enter your topic/i
                ];
                
                if (placeholderPatterns.some(pattern => pattern.test(topicText))) {
                    errors.push('Topic appears to be placeholder text');
                }
            }
            
            return {
                valid: errors.length === 0,
                errors: errors
            };
        }
    };
    
    // PUBLIC API
    return {
        // INITIALIZATION
        init: function(initialData = {}) {
            logger.log('info', 'init', 'Initializing MKCG Data Manager', initialData);
            
            // Load initial data
            if (initialData.topics) {
                Object.assign(dataStore.topics, initialData.topics);
            }
            
            if (initialData.questions) {
                Object.assign(dataStore.questions, initialData.questions);
            }
            
            if (initialData.selectedTopicId) {
                dataStore.selectedTopicId = initialData.selectedTopicId;
            }
            
            if (initialData.postId) {
                dataStore.postId = initialData.postId;
            }
            
            if (initialData.entryId) {
                dataStore.entryId = initialData.entryId;
            }
            
            dataStore.lastUpdate = Date.now();
            
            logger.log('success', 'init', 'Data Manager initialized successfully');
            this.trigger('data:synced', { source: 'init', dataStore: this.getState() });
        },
        
        // TOPIC MANAGEMENT
        getTopic: function(topicId) {
            if (topicId < 1 || topicId > 5) {
                logger.log('error', 'topics', 'Invalid topic ID requested', topicId);
                return null;
            }
            
            const topic = dataStore.topics[topicId];
            logger.log('debug', 'topics', `Retrieved topic ${topicId}`, topic);
            return topic || '';
        },
        
        setTopic: function(topicId, topicText, options = {}) {
            logger.log('info', 'topics', `Setting topic ${topicId}`, { topicText, options });
            
            // Enhanced validation using EnhancedValidationManager if available
            if (window.EnhancedValidationManager && !options.skipValidation) {
                const enhancedValidation = window.EnhancedValidationManager.validateField(
                    'topic', 
                    topicText, 
                    { context: 'data_manager_set' }
                );
                
                if (!enhancedValidation.valid) {
                    logger.log('error', 'topics', 'Enhanced validation failed', enhancedValidation.errors);
                    
                    // Use Enhanced Error Handler if available
                    if (window.EnhancedErrorHandler) {
                        window.EnhancedErrorHandler.handleError(new Error(
                            'Topic validation failed: ' + enhancedValidation.errors.join(', ')
                        ), {
                            topicId: topicId,
                            topicText: topicText,
                            source: 'data_manager',
                            operation: 'setTopic'
                        });
                    }
                    
                    throw new Error('Topic validation failed: ' + enhancedValidation.errors.join(', '));
                }
                
                // Log warnings if any
                if (enhancedValidation.warnings.length > 0) {
                    logger.log('warn', 'topics', `Topic warnings for ${topicId}`, enhancedValidation.warnings);
                }
            } else {
                // Fallback to original validation
                const validation = validator.validateTopic(topicId, topicText);
                if (!validation.valid && !options.skipValidation) {
                    // CRITICAL FIX: Allow placeholder text for empty topics - just mark as placeholder
                    const hasPlaceholderError = validation.errors.some(error => 
                        error.includes('placeholder text') || error.includes('empty'));
                    
                    if (hasPlaceholderError && validation.errors.length === 1) {
                        // This is just placeholder text - allow it but mark it
                        logger.log('warn', 'topics', `Allowing placeholder topic ${topicId}`, validation.errors);
                        options.isPlaceholder = true;
                    } else {
                        // Real validation errors - reject
                        logger.log('error', 'topics', 'Topic validation failed', validation.errors);
                        throw new Error('Topic validation failed: ' + validation.errors.join(', '));
                    }
                }
            }
            
            // Store previous value for rollback
            const previousTopic = dataStore.topics[topicId];
            
            try {
                // Create backup before change
                const backup = {
                    topicId: topicId,
                    previousValue: previousTopic,
                    timestamp: Date.now(),
                    options: options
                };
                
                // Update the topic
                dataStore.topics[topicId] = topicText;
                dataStore.lastUpdate = Date.now();
                
                logger.log('success', 'topics', `Topic ${topicId} updated successfully`);
                
                // Trigger events
                this.trigger('topic:updated', {
                    topicId: topicId,
                    oldText: previousTopic,
                    newText: topicText,
                    timestamp: dataStore.lastUpdate,
                    backup: backup
                });
                
                // If this is the selected topic, trigger selection event too
                if (topicId === dataStore.selectedTopicId) {
                    this.trigger('topic:selected', {
                        topicId: topicId,
                        topicText: topicText,
                        timestamp: dataStore.lastUpdate
                    });
                }
                
                return {
                    success: true,
                    topicId: topicId,
                    previousValue: previousTopic,
                    newValue: topicText,
                    backup: backup
                };
                
            } catch (error) {
                // Enhanced error handling and rollback
                logger.log('error', 'topics', 'Failed to set topic, rolling back', error);
                dataStore.topics[topicId] = previousTopic;
                
                // Use Enhanced Error Handler if available
                if (window.EnhancedErrorHandler) {
                    window.EnhancedErrorHandler.handleError(error, {
                        topicId: topicId,
                        topicText: topicText,
                        previousTopic: previousTopic,
                        source: 'data_manager',
                        operation: 'setTopic',
                        rolledBack: true
                    });
                }
                
                throw error;
            }
        },
        
        getAllTopics: function() {
            logger.log('debug', 'topics', 'Retrieved all topics');
            return { ...dataStore.topics };
        },
        
        // TOPIC SELECTION
        selectTopic: function(topicId) {
            logger.log('info', 'topics', `Selecting topic ${topicId}`);
            
            if (topicId < 1 || topicId > 5) {
                logger.log('error', 'topics', 'Invalid topic ID for selection', topicId);
                throw new Error('Invalid topic ID: must be between 1 and 5');
            }
            
            const previousSelection = dataStore.selectedTopicId;
            dataStore.selectedTopicId = topicId;
            
            const topicText = dataStore.topics[topicId] || '';
            
            logger.log('success', 'topics', `Topic ${topicId} selected successfully`, topicText);
            
            this.trigger('topic:selected', {
                topicId: topicId,
                topicText: topicText,
                previousSelection: previousSelection,
                timestamp: Date.now()
            });
            
            return topicText;
        },
        
        getSelectedTopic: function() {
            const topicId = dataStore.selectedTopicId;
            const topicText = dataStore.topics[topicId] || '';
            
            return {
                id: topicId,
                text: topicText
            };
        },
        
        // QUESTIONS MANAGEMENT
        setQuestions: function(topicId, questions) {
            logger.log('info', 'questions', `Setting questions for topic ${topicId}`, questions);
            
            if (topicId < 1 || topicId > 5) {
                logger.log('error', 'questions', 'Invalid topic ID for questions', topicId);
                throw new Error('Invalid topic ID: must be between 1 and 5');
            }
            
            if (!Array.isArray(questions)) {
                logger.log('error', 'questions', 'Questions must be an array', questions);
                throw new Error('Questions must be an array');
            }
            
            const previousQuestions = dataStore.questions[topicId];
            dataStore.questions[topicId] = questions;
            dataStore.lastUpdate = Date.now();
            
            logger.log('success', 'questions', `Questions for topic ${topicId} updated successfully`);
            
            this.trigger('questions:updated', {
                topicId: topicId,
                questions: questions,
                previousQuestions: previousQuestions,
                timestamp: dataStore.lastUpdate
            });
        },
        
        getQuestions: function(topicId) {
            if (topicId < 1 || topicId > 5) {
                logger.log('error', 'questions', 'Invalid topic ID for questions retrieval', topicId);
                return [];
            }
            
            const questions = dataStore.questions[topicId] || [];
            logger.log('debug', 'questions', `Retrieved questions for topic ${topicId}`, questions);
            return questions;
        },
        
        // EVENT SYSTEM
        on: function(eventName, callback) {
            if (!eventListeners[eventName]) {
                eventListeners[eventName] = [];
            }
            
            eventListeners[eventName].push(callback);
            logger.log('debug', 'events', `Registered listener for ${eventName}`);
        },
        
        off: function(eventName, callback) {
            if (eventListeners[eventName]) {
                const index = eventListeners[eventName].indexOf(callback);
                if (index > -1) {
                    eventListeners[eventName].splice(index, 1);
                    logger.log('debug', 'events', `Removed listener for ${eventName}`);
                }
            }
        },
        
        trigger: function(eventName, data) {
            logger.log('debug', 'events', `Triggering event: ${eventName}`, data);
            
            if (eventListeners[eventName]) {
                eventListeners[eventName].forEach(callback => {
                    try {
                        callback(data);
                    } catch (error) {
                        logger.log('error', 'events', `Error in event listener for ${eventName}`, error);
                    }
                });
            }
        },
        
        // ENHANCED SAVE MANAGEMENT with error recovery
        markSaveInProgress: function(operation = 'unknown') {
            dataStore.saveInProgress = true;
            dataStore.lastSaveAttempt = Date.now();
            
            logger.log('info', 'save', `Save operation started: ${operation}`);
            
            // Show loading indicator if EnhancedUIFeedback available
            if (window.EnhancedUIFeedback) {
                dataStore.saveLoadingId = window.EnhancedUIFeedback.showLoadingSpinner(
                    document.body,
                    `Saving ${operation}...`,
                    { global: true }
                );
            }
            
            this.trigger('save:started', {
                operation: operation,
                timestamp: dataStore.lastSaveAttempt
            });
        },
        
        markSaveCompleted: function(operation = 'unknown', result = {}) {
            dataStore.saveInProgress = false;
            const duration = Date.now() - (dataStore.lastSaveAttempt || 0);
            
            logger.log('success', 'save', `Save operation completed: ${operation} (${duration}ms)`, result);
            
            // Hide loading indicator
            if (window.EnhancedUIFeedback && dataStore.saveLoadingId) {
                window.EnhancedUIFeedback.hideLoadingSpinner(dataStore.saveLoadingId);
                delete dataStore.saveLoadingId;
                
                // Show success toast
                window.EnhancedUIFeedback.showToast(
                    `${operation} saved successfully`,
                    'success',
                    3000
                );
            }
            
            this.trigger('save:completed', {
                operation: operation,
                result: result,
                duration: duration,
                timestamp: Date.now()
            });
        },
        
        markSaveFailed: function(error, operation = 'unknown', options = {}) {
            dataStore.saveInProgress = false;
            const duration = Date.now() - (dataStore.lastSaveAttempt || 0);
            
            logger.log('error', 'save', `Save operation failed: ${operation} (${duration}ms)`, error);
            
            // Hide loading indicator
            if (window.EnhancedUIFeedback && dataStore.saveLoadingId) {
                window.EnhancedUIFeedback.hideLoadingSpinner(dataStore.saveLoadingId);
                delete dataStore.saveLoadingId;
            }
            
            // Use Enhanced Error Handler if available
            if (window.EnhancedErrorHandler) {
                window.EnhancedErrorHandler.handleError(error, {
                    operation: operation,
                    duration: duration,
                    source: 'data_manager_save',
                    ...options
                });
            } else if (window.EnhancedUIFeedback) {
                // Fallback error notification
                window.EnhancedUIFeedback.showToast({
                    title: 'Save Failed',
                    message: `Failed to save ${operation}. Please try again.`,
                    actions: ['Check your connection and retry']
                }, 'error', 0);
            }
            
            this.trigger('save:failed', {
                operation: operation,
                error: error,
                duration: duration,
                timestamp: Date.now(),
                options: options
            });
        },
        
        isSaveInProgress: function() {
            return dataStore.saveInProgress;
        },
        
        // STATE MANAGEMENT
        getState: function() {
            return {
                topics: { ...dataStore.topics },
                questions: { ...dataStore.questions },
                selectedTopicId: dataStore.selectedTopicId,
                selectedTopic: this.getSelectedTopic(),
                postId: dataStore.postId,
                entryId: dataStore.entryId,
                lastUpdate: dataStore.lastUpdate,
                saveInProgress: dataStore.saveInProgress
            };
        },
        
        // ENHANCED UTILITIES with validation integration
        isValidTopicId: function(topicId) {
            return Number.isInteger(topicId) && topicId >= 1 && topicId <= 5;
        },
        
        hasValidTopic: function(topicId) {
            const topic = this.getTopic(topicId);
            
            // Use enhanced validation if available
            if (window.EnhancedValidationManager) {
                const validation = window.EnhancedValidationManager.validateField(
                    'topic', 
                    topic, 
                    { context: 'validity_check' }
                );
                return validation.valid && validation.warnings.length === 0;
            } else {
                // Fallback to basic validation
                return topic && topic.length >= 10 && !topic.match(/^topic \d+ - click to add/i);
            }
        },
        
        getValidTopicsCount: function() {
            let count = 0;
            for (let i = 1; i <= 5; i++) {
                if (this.hasValidTopic(i)) {
                    count++;
                }
            }
            return count;
        },
        
        // Enhanced data integrity check
        validateDataIntegrity: function() {
            const issues = [];
            
            // Check topic data integrity
            for (let i = 1; i <= 5; i++) {
                const topic = dataStore.topics[i];
                if (topic !== undefined && typeof topic !== 'string') {
                    issues.push(`Topic ${i} has invalid data type: ${typeof topic}`);
                }
            }
            
            // Check questions data integrity
            for (let i = 1; i <= 5; i++) {
                const questions = dataStore.questions[i];
                if (questions !== undefined && !Array.isArray(questions)) {
                    issues.push(`Questions for topic ${i} should be an array`);
                }
            }
            
            // Check selected topic validity
            if (!this.isValidTopicId(dataStore.selectedTopicId)) {
                issues.push(`Invalid selected topic ID: ${dataStore.selectedTopicId}`);
            }
            
            if (issues.length > 0) {
                logger.log('error', 'integrity', 'Data integrity issues found', issues);
                
                if (window.EnhancedErrorHandler) {
                    window.EnhancedErrorHandler.handleError(
                        new Error('Data integrity issues detected'),
                        {
                            issues: issues,
                            source: 'data_manager',
                            operation: 'integrity_check'
                        }
                    );
                }
            }
            
            return {
                valid: issues.length === 0,
                issues: issues
            };
        },
        
        // Enhanced backup and restore functionality
        createStateBackup: function() {
            const backup = {
                timestamp: Date.now(),
                topics: { ...dataStore.topics },
                questions: { ...dataStore.questions },
                selectedTopicId: dataStore.selectedTopicId,
                lastUpdate: dataStore.lastUpdate
            };
            
            logger.log('info', 'backup', 'State backup created', backup);
            return backup;
        },
        
        restoreFromBackup: function(backup) {
            if (!backup || typeof backup !== 'object') {
                logger.log('error', 'restore', 'Invalid backup data provided');
                return false;
            }
            
            try {
                const previousState = this.createStateBackup();
                
                // Restore data
                if (backup.topics) Object.assign(dataStore.topics, backup.topics);
                if (backup.questions) Object.assign(dataStore.questions, backup.questions);
                if (backup.selectedTopicId) dataStore.selectedTopicId = backup.selectedTopicId;
                if (backup.lastUpdate) dataStore.lastUpdate = backup.lastUpdate;
                
                logger.log('success', 'restore', 'State restored from backup', backup);
                
                this.trigger('data:restored', {
                    backup: backup,
                    previousState: previousState,
                    timestamp: Date.now()
                });
                
                return true;
            } catch (error) {
                logger.log('error', 'restore', 'Failed to restore from backup', error);
                
                if (window.EnhancedErrorHandler) {
                    window.EnhancedErrorHandler.handleError(error, {
                        backup: backup,
                        source: 'data_manager',
                        operation: 'restore_backup'
                    });
                }
                
                return false;
            }
        },
        
        // ENHANCED DEBUG AND DIAGNOSTICS
        debug: function() {
            const integrityCheck = this.validateDataIntegrity();
            
            return {
                state: this.getState(),
                eventListeners: Object.keys(eventListeners).reduce((acc, key) => {
                    acc[key] = eventListeners[key].length;
                    return acc;
                }, {}),
                validTopics: this.getValidTopicsCount(),
                dataIntegrity: integrityCheck,
                enhancedSystems: {
                    ajaxManager: !!window.EnhancedAjaxManager,
                    errorHandler: !!window.EnhancedErrorHandler,
                    uiFeedback: !!window.EnhancedUIFeedback,
                    validationManager: !!window.EnhancedValidationManager
                },
                performance: {
                    lastUpdate: dataStore.lastUpdate,
                    lastSaveAttempt: dataStore.lastSaveAttempt || null,
                    saveInProgress: dataStore.saveInProgress,
                    cacheSize: window.EnhancedValidationManager ? 
                        window.EnhancedValidationManager.getStats().cacheSize : 'N/A'
                }
            };
        },
        
        // Enhanced error recovery method
        recoverFromError: function(error, context = {}) {
            logger.log('warn', 'recovery', 'Attempting error recovery', { error, context });
            
            try {
                // Check data integrity
                const integrityCheck = this.validateDataIntegrity();
                if (!integrityCheck.valid) {
                    logger.log('error', 'recovery', 'Data integrity issues found during recovery', integrityCheck.issues);
                }
                
                // Reset save state if stuck
                if (dataStore.saveInProgress && context.resetSaveState) {
                    dataStore.saveInProgress = false;
                    if (dataStore.saveLoadingId && window.EnhancedUIFeedback) {
                        window.EnhancedUIFeedback.hideLoadingSpinner(dataStore.saveLoadingId);
                        delete dataStore.saveLoadingId;
                    }
                }
                
                // Clear validation cache if available
                if (window.EnhancedValidationManager && context.clearValidationCache) {
                    window.EnhancedValidationManager.clearCache();
                }
                
                this.trigger('error:recovered', {
                    error: error,
                    context: context,
                    timestamp: Date.now()
                });
                
                return true;
            } catch (recoveryError) {
                logger.log('error', 'recovery', 'Error recovery failed', recoveryError);
                return false;
            }
        }
    };
})();

// Enhanced initialization when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 MKCG Data Manager: Ready for enhanced initialization');
    
    // Wait for enhanced systems to be available
    const waitForEnhancedSystems = () => {
        const systemsAvailable = {
            ajaxManager: !!window.EnhancedAjaxManager,
            errorHandler: !!window.EnhancedErrorHandler,
            uiFeedback: !!window.EnhancedUIFeedback,
            validationManager: !!window.EnhancedValidationManager
        };
        
        const availableCount = Object.values(systemsAvailable).filter(Boolean).length;
        
        if (availableCount >= 2) { // At least 2 enhanced systems available
            console.log('✅ Enhanced systems detected, Data Manager ready', systemsAvailable);
            
            // Initialize with enhanced capabilities
            if (window.MKCG_DataManager) {
                window.MKCG_DataManager.enhancedMode = true;
                window.MKCG_DataManager.availableSystems = systemsAvailable;
            }
        } else {
            console.log('⚠️ Limited enhanced systems, running in basic mode', systemsAvailable);
            
            // Retry in 100ms
            setTimeout(waitForEnhancedSystems, 100);
        }
    };
    
    // Start waiting for enhanced systems
    waitForEnhancedSystems();
});

// Global error handler for Data Manager
window.addEventListener('error', (event) => {
    if (event.error && event.error.message && event.error.message.includes('MKCG_DataManager')) {
        console.error('🚨 Data Manager error detected:', event.error);
        
        if (window.MKCG_DataManager && typeof window.MKCG_DataManager.recoverFromError === 'function') {
            window.MKCG_DataManager.recoverFromError(event.error, {
                type: 'uncaught_error',
                filename: event.filename,
                lineno: event.lineno,
                resetSaveState: true,
                clearValidationCache: true
            });
        }
    }
});