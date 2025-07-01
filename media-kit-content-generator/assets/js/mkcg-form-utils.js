/**
 * MKCG Enhanced FormUtils - BEM Compatible
 * Unified JavaScript utilities for all generators with BEM class support
 */

const MKCG_FormUtils = {
    
    // Configuration
    config: {
        debug: true,
        ajaxTimeout: 30000,
        retryAttempts: 2
    },
    
    // Event system for cross-generator communication
    events: {
        listeners: {},
        
        // Add event listener
        on: function(event, callback) {
            if (!this.listeners[event]) {
                this.listeners[event] = [];
            }
            this.listeners[event].push(callback);
            MKCG_FormUtils.log('Event listener added for: ' + event);
            return this;
        },
        
        // Remove event listener
        off: function(event, callback) {
            if (!this.listeners[event]) return this;
            
            if (callback) {
                this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
            } else {
                delete this.listeners[event];
            }
            
            return this;
        },
        
        // Trigger event
        trigger: function(event, data) {
            MKCG_FormUtils.log('Event triggered: ' + event, data);
            if (!this.listeners[event]) return;
            
            this.listeners[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Error in event listener:', error);
                }
            });
            
            return this;
        }
    },
    
    // Cross-generator data sharing
    data: {
        cache: {},
        
        // Set data
        set: function(key, value) {
            this.cache[key] = value;
            
            // Trigger data change event
            MKCG_FormUtils.events.trigger('data:change:' + key, value);
            MKCG_FormUtils.events.trigger('data:change', { key: key, value: value });
            
            return this;
        },
        
        // Get data
        get: function(key, defaultValue = null) {
            // Return from memory cache
            return (this.cache[key] !== undefined) ? this.cache[key] : defaultValue;
        },
        
        // Remove data
        remove: function(key) {
            delete this.cache[key];
            return this;
        },
        
        // Clear all data
        clear: function() {
            this.cache = {};
            return this;
        }
    },
    
    // Initialize the FormUtils
    init: function() {
        this.log('MKCG FormUtils initialized with BEM support');
        this.bindGlobalEvents();
    },
    
    // Logging utility
    log: function(message, data = null) {
        if (this.config.debug) {
            console.log('[MKCG FormUtils]', message, data || '');
        }
    },
    
    // Global event bindings
    bindGlobalEvents: function() {
        document.addEventListener('DOMContentLoaded', () => {
            this.log('DOM Content Loaded - initializing BEM components');
            this.authorityHook.init();
            this.ui.init();
        });
    },
    
    // Toggle management utilities (BEM compatible)
    toggle: {
        show: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.style.display = 'block';
                element.classList.remove('hidden');
                element.setAttribute('aria-hidden', 'false');
            }
        },
        
        hide: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.style.display = 'none';
                element.classList.add('hidden');
                element.setAttribute('aria-hidden', 'true');
            }
        },
        
        toggle: function(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                if (element.style.display === 'none' || element.classList.contains('hidden')) {
                    this.show(element);
                } else {
                    this.hide(element);
                }
            }
        }
    },
    
    // Field value management (BEM selectors)
    fieldValue: {
        get: function(selector) {
            const element = document.querySelector(selector);
            return element ? element.value.trim() : '';
        },
        
        set: function(selector, value) {
            const element = document.querySelector(selector);
            if (element) {
                element.value = value;
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
        
        clear: function(selector) {
            this.set(selector, '');
        },
        
        // Get multiple field values at once (BEM friendly)
        getMultiple: function(selectors) {
            const values = {};
            Object.keys(selectors).forEach(key => {
                values[key] = this.get(selectors[key]);
            });
            return values;
        },
        
        // Clear all fields in a section
        clearSection: function(sectionSelector) {
            const section = document.querySelector(sectionSelector);
            if (section) {
                const fields = section.querySelectorAll('.field__input, .field__textarea, .field__select');
                fields.forEach(field => {
                    field.value = '';
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }
        }
    },
    
    // WordPress/AJAX utilities - ENHANCED with enterprise-grade error handling
    wp: {
        makeAjaxRequest: function(action, data, callbacks = {}) {
            MKCG_FormUtils.log('Making ENHANCED AJAX request', { action, data });
            
            // Use Enhanced AJAX Manager if available, fallback to legacy
            if (window.EnhancedAjaxManager) {
                return window.EnhancedAjaxManager.makeRequest(action, data, {
                    onStart: callbacks.onStart,
                    onProgress: callbacks.onProgress,
                    onSuccess: (result) => {
                        MKCG_FormUtils.log('Enhanced AJAX success', result);
                        if (callbacks.onSuccess) {
                            callbacks.onSuccess(result);
                        }
                    },
                    onError: (error) => {
                        MKCG_FormUtils.log('Enhanced AJAX error', error);
                        
                        // Use Enhanced Error Handler if available
                        if (window.EnhancedErrorHandler) {
                            window.EnhancedErrorHandler.handleError(error, {
                                action: action,
                                data: data,
                                source: 'mkcg_form_utils'
                            });
                        }
                        
                        if (callbacks.onError) {
                            callbacks.onError(error.message || error);
                        }
                    },
                    onComplete: callbacks.onComplete,
                    timeout: callbacks.timeout || 30000,
                    retryAttempts: callbacks.retryAttempts || 3
                }).catch(error => {
                    // Final error handling
                    MKCG_FormUtils.log('Enhanced AJAX final error', error);
                    if (callbacks.onError) {
                        callbacks.onError(error.message || 'Request failed');
                    }
                    throw error;
                });
            } else {
                // Legacy fallback with basic error handling
                console.warn('⚠️ Enhanced AJAX Manager not available, using legacy method');
                return this.makeLegacyAjaxRequest(action, data, callbacks);
            }
        },
        
        // Legacy AJAX method for fallback
        makeLegacyAjaxRequest: function(action, data, callbacks = {}) {
            const postData = new URLSearchParams();
            postData.append('action', action);
            postData.append('security', mkcg_vars?.nonce || '');
            
            // Add data to post
            Object.keys(data).forEach(key => {
                if (typeof data[key] === 'object') {
                    postData.append(key, JSON.stringify(data[key]));
                } else {
                    postData.append(key, data[key]);
                }
            });
            
            // Show loading if callback provided
            if (callbacks.onStart) {
                callbacks.onStart();
            }
            
            const ajaxUrl = mkcg_vars?.ajax_url || window.ajaxurl || '/wp-admin/admin-ajax.php';
            
            return fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: postData.toString()
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(response => {
                MKCG_FormUtils.log('Legacy AJAX response received', response);
                
                if (callbacks.onComplete) {
                    callbacks.onComplete();
                }
                
                if (response.success) {
                    if (callbacks.onSuccess) {
                        callbacks.onSuccess(response.data);
                    }
                    return response.data;
                } else {
                    const errorMessage = response.data?.message || 'Request failed';
                    MKCG_FormUtils.log('Legacy AJAX error', response);
                    if (callbacks.onError) {
                        callbacks.onError(errorMessage);
                    }
                    throw new Error(errorMessage);
                }
            })
            .catch(error => {
                MKCG_FormUtils.log('Legacy AJAX fetch error', error);
                
                if (callbacks.onComplete) {
                    callbacks.onComplete();
                }
                
                const errorMessage = error.message || 'Network error occurred';
                if (callbacks.onError) {
                    callbacks.onError(errorMessage);
                }
                throw error;
            });
        },
        
        // Get Formidable entry data
        getFormidableData: function() {
            const entryElement = document.querySelector('#actual-entry-id');
            const entryId = entryElement ? entryElement.value : '';
            
            if (!entryId) {
                // Try to get from URL
                const urlParams = new URLSearchParams(window.location.search);
                const entryKey = urlParams.get('entry');
                return { entry_key: entryKey };
            }
            
            return { entry_id: entryId };
        }
    },
    
    // UI utilities (BEM compatible)
    ui: {
        init: function() {
            this.bindClipboardEvents();
            this.bindModalEvents();
            this.bindFieldClearEvents();
        },
        
        showLoading: function(message = 'Loading...', target = null) {
            // Use Enhanced UI Feedback if available
            if (window.EnhancedUIFeedback) {
                return window.EnhancedUIFeedback.showLoadingSpinner(
                    target || document.body, 
                    message, 
                    { global: !target }
                );
            } else {
                // Legacy fallback
                return this.showLegacyLoading(message);
            }
        },
        
        hideLoading: function(loadingId = null) {
            // Use Enhanced UI Feedback if available
            if (window.EnhancedUIFeedback && loadingId) {
                window.EnhancedUIFeedback.hideLoadingSpinner(loadingId);
            } else if (window.EnhancedUIFeedback) {
                window.EnhancedUIFeedback.hideAllLoading();
            } else {
                // Legacy fallback
                this.hideLegacyLoading();
            }
        },
        
        // Legacy loading methods for fallback
        showLegacyLoading: function(message = 'Loading...') {
            let overlay = document.querySelector('.loading');
            if (!overlay) {
                overlay = this.createLoadingOverlay();
            }
            
            const messageEl = overlay.querySelector('.loading__message');
            if (messageEl) {
                messageEl.textContent = message;
            }
            
            overlay.style.display = 'flex';
            return 'legacy_loading';
        },
        
        hideLegacyLoading: function() {
            const overlay = document.querySelector('.loading');
            if (overlay) {
                overlay.style.display = 'none';
            }
        },
        
        createLoadingOverlay: function() {
            const overlay = document.createElement('div');
            overlay.className = 'loading';
            overlay.innerHTML = `
                <div class="loading__content">
                    <div class="loading__spinner"></div>
                    <div class="loading__message">Loading...</div>
                </div>
            `;
            document.body.appendChild(overlay);
            return overlay;
        },
        
        showAlert: function(message, type = 'info', duration = 5000) {
            // Use Enhanced UI Feedback if available
            if (window.EnhancedUIFeedback) {
                return window.EnhancedUIFeedback.showToast(message, type, duration);
            } else {
                // Legacy fallback
                alert(typeof message === 'string' ? message : message.message || 'Notification');
            }
        },
        
        showSuccess: function(message, duration = 3000) {
            return this.showAlert(message, 'success', duration);
        },
        
        showError: function(message, duration = 0) {
            return this.showAlert(message, 'error', duration);
        },
        
        showWarning: function(message, duration = 5000) {
            return this.showAlert(message, 'warning', duration);
        },
        
        showInfo: function(message, duration = 4000) {
            return this.showAlert(message, 'info', duration);
        },
        
        bindClipboardEvents: function() {
            document.addEventListener('click', (e) => {
                // BEM compatible button selectors
                if (e.target.classList.contains('button--copy') || 
                    e.target.closest('.button--copy') ||
                    e.target.id.includes('copy-')) {
                    this.handleCopyClick(e);
                }
            });
        },
        
        handleCopyClick: function(e) {
            e.preventDefault();
            
            let textToCopy = '';
            const button = e.target.closest('button') || e.target;
            
            // Determine what to copy based on button
            if (button.id === 'copy-authority-hook-btn') {
                textToCopy = MKCG_FormUtils.authorityHook.generate();
            } else if (button.dataset.copyTarget) {
                const targetElement = document.querySelector(button.dataset.copyTarget);
                textToCopy = targetElement ? targetElement.textContent || targetElement.value : '';
            }
            
            this.copyToClipboard(textToCopy);
        },
        
        copyToClipboard: function(text) {
            if (!text) {
                alert('Nothing to copy. Please fill out the required fields first.');
                return;
            }
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(() => {
                        this.showSuccess('Copied to clipboard!');
                    })
                    .catch(err => {
                        console.error('Could not copy text: ', err);
                        this.fallbackCopyToClipboard(text);
                    });
            } else {
                this.fallbackCopyToClipboard(text);
            }
        },
        
        fallbackCopyToClipboard: function(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showSuccess('Copied to clipboard!');
                } else {
                    this.showError('Unable to copy to clipboard. Please copy the text manually.');
                }
            } catch (err) {
                console.error('Error copying to clipboard:', err);
                this.showError('Unable to copy to clipboard. Please copy the text manually.');
            }
            
            document.body.removeChild(textarea);
        },
        
        bindModalEvents: function() {
            // Handle modal close events (BEM compatible)
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal__close') || 
                    e.target.classList.contains('modal') ||
                    e.target.classList.contains('modal-close')) {
                    const modal = e.target.closest('.modal');
                    this.closeModal(modal);
                }
            });
        },
        
        closeModal: function(modal) {
            if (modal) {
                modal.remove();
            }
        },
        
        bindFieldClearEvents: function() {
            // Handle field clear buttons (BEM compatible)
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('field__clear')) {
                    const fieldId = e.target.dataset.fieldId;
                    if (fieldId) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = '';
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                }
            });
        }
    },
    
    // Authority Hook component management (BEM compatible)
    authorityHook: {
        init: function() {
            MKCG_FormUtils.log('Initializing Authority Hook component with BEM');
            this.bindEvents();
            this.update();
        },
        
        bindEvents: function() {
            const fields = ['#mkcg-who', '#mkcg-result', '#mkcg-when', '#mkcg-how'];
            
            fields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    field.addEventListener('input', () => {
                        this.update();
                    });
                }
            });
            
            // Bind edit link
            const editLink = document.getElementById('edit-authority-components');
            if (editLink) {
                editLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.scrollToFirstField();
                });
            }
        },
        
        update: function() {
            const content = this.generate();
            const displayElement = document.querySelector('#authority-hook-content');
            const hiddenField = document.querySelector('#mkcg-authority-hook');
            
            if (displayElement) {
                displayElement.innerHTML = this.formatForDisplay(content);
            }
            
            if (hiddenField) {
                hiddenField.value = content;
            }
        },
        
        generate: function() {
            const who = MKCG_FormUtils.fieldValue.get('#mkcg-who') || 'your audience';
            const result = MKCG_FormUtils.fieldValue.get('#mkcg-result') || 'achieve results';
            const when = MKCG_FormUtils.fieldValue.get('#mkcg-when') || 'they need you';
            const how = MKCG_FormUtils.fieldValue.get('#mkcg-how') || 'through your method';
            
            const formattedWho = this.formatAudienceList(who);
            
            let hook = 'I help ' + formattedWho;
            if (result) hook += ' ' + result;
            if (when) hook += ' when ' + when;
            if (how) hook += ' ' + how;
            hook += '.';
            
            return hook;
        },
        
        formatForDisplay: function(content) {
            // Add highlighting to key components using BEM classes
            const who = MKCG_FormUtils.fieldValue.get('#mkcg-who');
            const result = MKCG_FormUtils.fieldValue.get('#mkcg-result');
            const when = MKCG_FormUtils.fieldValue.get('#mkcg-when');
            const how = MKCG_FormUtils.fieldValue.get('#mkcg-how');
            
            let formatted = content;
            
            if (who) {
                const formattedWho = this.formatAudienceList(who);
                formatted = formatted.replace(formattedWho, `<span class="authority-hook__highlight">${formattedWho}</span>`);
            }
            if (result) formatted = formatted.replace(result, `<span class="authority-hook__highlight">${result}</span>`);
            if (when) formatted = formatted.replace(when, `<span class="authority-hook__highlight">${when}</span>`);
            if (how) formatted = formatted.replace(how, `<span class="authority-hook__highlight">${how}</span>`);
            
            return formatted;
        },
        
        formatAudienceList: function(audienceString) {
            if (audienceString.indexOf(',') === -1) {
                return audienceString;
            }
            
            const audiences = audienceString.split(',')
                .map(item => item.trim())
                .filter(item => item !== '');
            
            if (audiences.length === 0) {
                return 'your audience';
            } else if (audiences.length === 1) {
                return audiences[0];
            } else if (audiences.length === 2) {
                return audiences.join(' and ');
            } else {
                const lastAudience = audiences.pop();
                return audiences.join(', ') + ', and ' + lastAudience;
            }
        },
        
        scrollToFirstField: function() {
            const firstField = document.querySelector('#mkcg-who');
            if (firstField) {
                // Check first tab
                const firstTab = document.querySelector('#tabwho');
                if (firstTab) {
                    firstTab.checked = true;
                }
                
                firstField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstField.focus();
            }
        },
        
        extract: function() {
            return {
                who: MKCG_FormUtils.fieldValue.get('#mkcg-who'),
                result: MKCG_FormUtils.fieldValue.get('#mkcg-result'),
                when: MKCG_FormUtils.fieldValue.get('#mkcg-when'),
                how: MKCG_FormUtils.fieldValue.get('#mkcg-how'),
                complete_hook: this.generate()
            };
        }
    },
    
    // Tag management (BEM compatible)
    tags: {
        create: function(containerSelector, inputSelector, options = {}) {
            const container = document.querySelector(containerSelector);
            const input = document.querySelector(inputSelector);
            
            if (!container || !input) {
                MKCG_FormUtils.log('Tag manager: Required elements not found', { containerSelector, inputSelector });
                return;
            }
            
            const tagManager = {
                container: container,
                input: input,
                options: {
                    allowDuplicates: false,
                    maxTags: 0,
                    onAdd: null,
                    onRemove: null,
                    onChange: null,
                    ...options
                },
                
                addTag: function(text, isChecked = true) {
                    if (!text.trim()) return;
                    
                    // Check for duplicates
                    if (!this.options.allowDuplicates) {
                        const existing = Array.from(this.container.querySelectorAll('.tag'))
                            .some(tag => tag.dataset.value?.toLowerCase() === text.toLowerCase());
                        if (existing) return;
                    }
                    
                    // Check max tags
                    if (this.options.maxTags > 0) {
                        const currentCount = this.container.querySelectorAll('.tag').length;
                        if (currentCount >= this.options.maxTags) return;
                    }
                    
                    const tagElement = this.createTagElement(text, isChecked);
                    this.container.appendChild(tagElement);
                    
                    if (this.options.onAdd) {
                        this.options.onAdd(text, isChecked);
                    }
                    
                    this.input.value = '';
                },
                
                createTagElement: function(text, isChecked) {
                    const tag = document.createElement('div');
                    tag.className = 'tag tag--audience';
                    tag.dataset.value = text;
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'tag__checkbox';
                    checkbox.checked = isChecked;
                    
                    const span = document.createElement('span');
                    span.className = 'tag__text';
                    span.textContent = text;
                    
                    const removeBtn = document.createElement('span');
                    removeBtn.className = 'tag__remove';
                    removeBtn.textContent = '×';
                    
                    tag.appendChild(checkbox);
                    tag.appendChild(span);
                    tag.appendChild(removeBtn);
                    
                    // Bind events
                    removeBtn.addEventListener('click', () => {
                        tag.remove();
                        if (this.options.onRemove) {
                            this.options.onRemove(text);
                        }
                    });
                    
                    checkbox.addEventListener('change', () => {
                        if (this.options.onChange) {
                            this.options.onChange(text, checkbox.checked);
                        }
                    });
                    
                    return tag;
                },
                
                getSelectedTags: function() {
                    return Array.from(this.container.querySelectorAll('.tag__checkbox:checked'))
                        .map(checkbox => checkbox.closest('.tag').dataset.value);
                },
                
                getAllTags: function() {
                    return Array.from(this.container.querySelectorAll('.tag'))
                        .map(tag => ({
                            text: tag.dataset.value,
                            checked: tag.querySelector('.tag__checkbox').checked
                        }));
                }
            };
            
            // Bind input events
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    tagManager.addTag(input.value.trim());
                }
            });
            
            // Bind add button if it exists
            const addButton = document.querySelector('#add_tag');
            if (addButton) {
                addButton.addEventListener('click', () => {
                    tagManager.addTag(input.value.trim());
                });
            }
            
            return tagManager;
        }
    },
    
    // Form validation utilities
    validation: {
        validateField: function(selector, rules = {}) {
            const field = document.querySelector(selector);
            if (!field) return { valid: false, message: 'Field not found' };
            
            const value = field.value.trim();
            const errors = [];
            
            if (rules.required && !value) {
                errors.push('This field is required');
            }
            
            if (rules.minLength && value.length < rules.minLength) {
                errors.push(`Minimum ${rules.minLength} characters required`);
            }
            
            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(`Maximum ${rules.maxLength} characters allowed`);
            }
            
            if (rules.pattern && !rules.pattern.test(value)) {
                errors.push(rules.message || 'Invalid format');
            }
            
            return {
                valid: errors.length === 0,
                errors: errors
            };
        },
        
        validateSection: function(sectionSelector, fieldRules = {}) {
            const section = document.querySelector(sectionSelector);
            if (!section) return { valid: false, message: 'Section not found' };
            
            const results = {};
            let allValid = true;
            
            Object.keys(fieldRules).forEach(fieldSelector => {
                const result = this.validateField(fieldSelector, fieldRules[fieldSelector]);
                results[fieldSelector] = result;
                if (!result.valid) allValid = false;
            });
            
            return {
                valid: allValid,
                fields: results
            };
        }
    }
};

// Auto-initialize when loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MKCG_FormUtils.init());
} else {
    MKCG_FormUtils.init();
}

// Make globally available
window.MKCG_FormUtils = MKCG_FormUtils;