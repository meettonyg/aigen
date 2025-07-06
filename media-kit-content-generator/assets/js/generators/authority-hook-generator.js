/**
 * Authority Hook Generator JavaScript
 * Handles dedicated Authority Hook page functionality
 */

(function($) {
    'use strict';
    
    // Authority Hook Generator object
    window.AuthorityHookGenerator = {
        
        // Configuration
        config: {
            selectors: {
                saveButton: '#save-button',
                saveStatus: '#save-status', 
                saveMessages: '#save-messages',
                postIdField: '#post-id',
                nonceField: '#nonce',
                whoField: '#mkcg-who',
                whatField: '#mkcg-result',
                whenField: '#mkcg-when',
                howField: '#mkcg-how',
                copyToClipboard: '#copy-authority-hook-btn',
                hiddenField: '#mkcg-authority-hook'
            },
            ajax: {
                action: 'mkcg_save_authority_hook',
                timeout: 30000
            }
        },
        
        // Initialize the generator
        init: function() {
            console.log('🔧 Authority Hook Generator: Initializing...');
            
            this.bindEvents();
            this.setupRealTimeUpdates();
            this.populateFields();
            
            console.log('✅ Authority Hook Generator: Initialized successfully');
        },
        
        // Bind event handlers
        bindEvents: function() {
            const self = this;
            
            // Copy to clipboard button click
            $(this.config.selectors.copyToClipboard).on('click', function(e) {
                e.preventDefault();
                self.copyToClipboard();
            });
            
            // Save button click
            $(this.config.selectors.saveButton).on('click', function(e) {
                e.preventDefault();
                self.saveAuthorityHook();
            });
            
            // Field change listeners for real-time updates
            const fieldSelectors = [
                this.config.selectors.whoField,
                this.config.selectors.whatField,
                this.config.selectors.whenField,
                this.config.selectors.howField
            ];
            
            fieldSelectors.forEach(selector => {
                $(selector).on('input change', function() {
                    self.updatePreview();
                    self.updateHiddenField();
                });
            });
            
            console.log('✅ Event handlers bound successfully');
        },
        
        // Setup real-time preview updates
        setupRealTimeUpdates: function() {
            const self = this;
            
            // Listen for field changes and update preview
            $(document).on('input change', this.config.selectors.whoField + ',' + 
                                          this.config.selectors.whatField + ',' + 
                                          this.config.selectors.whenField + ',' + 
                                          this.config.selectors.howField, function() {
                self.updatePreview();
                self.updateHiddenField();
            });
            
            console.log('✅ Real-time updates configured');
        },
        
        // Populate fields with existing data
        populateFields: function() {
            if (window.MKCG_Authority_Hook_Data && window.MKCG_Authority_Hook_Data.hasData) {
                const data = window.MKCG_Authority_Hook_Data.authorityHook;
                
                $(this.config.selectors.whoField).val(data.who || '');
                $(this.config.selectors.whatField).val(data.what || '');
                $(this.config.selectors.whenField).val(data.when || '');
                $(this.config.selectors.howField).val(data.how || '');
                
                this.updatePreview();
                this.updateHiddenField();
                
                console.log('✅ Fields populated with existing data');
            }
        },
        
        // Update the live preview
        updatePreview: function() {
            const who = $(this.config.selectors.whoField).val() || '[WHO]';
            const what = $(this.config.selectors.whatField).val() || '[RESULT]';
            const when = $(this.config.selectors.whenField).val() || '[WHEN]';
            const how = $(this.config.selectors.howField).val() || '[HOW]';
            
            const completeHook = `I help ${who} ${what} when ${when} ${how}.`;
            
            // Update preview content with highlighting
            const previewElement = $('#authority-hook-content');
            if (previewElement.length) {
                previewElement.html(
                    `I help <span class="authority-hook__highlight">${who}</span> ` +
                    `<span class="authority-hook__highlight">${what}</span> when ` +
                    `<span class="authority-hook__highlight">${when}</span> ` +
                    `<span class="authority-hook__highlight">${how}</span>.`
                );
            }
            
            // Trigger custom event for other components
            $(document).trigger('authority-hook-updated', {
                who: who,
                what: what,
                when: when,
                how: how,
                completeHook: completeHook
            });
        },
        
        // Update hidden field with complete hook
        updateHiddenField: function() {
            const who = $(this.config.selectors.whoField).val() || '';
            const what = $(this.config.selectors.whatField).val() || '';
            const when = $(this.config.selectors.whenField).val() || '';
            const how = $(this.config.selectors.howField).val() || '';
            
            const completeHook = `I help ${who} ${what} when ${when} ${how}.`;
            $(this.config.selectors.hiddenField).val(completeHook);
        },
        
        // Copy complete authority hook to clipboard
        copyToClipboard: function() {
            const who = $(this.config.selectors.whoField).val() || '';
            const what = $(this.config.selectors.whatField).val() || '';
            const when = $(this.config.selectors.whenField).val() || '';
            const how = $(this.config.selectors.howField).val() || '';
            
            // Validate that we have content to copy
            if (!who && !what && !when && !how) {
                this.showMessage('⚠️ Please fill in the authority hook fields before copying.', 'warning');
                return;
            }
            
            const completeHook = `I help ${who} ${what} when ${when} ${how}.`;
            
            // Use modern clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(completeHook).then(() => {
                    this.showMessage('📋 Authority Hook copied to clipboard!', 'success');
                    console.log('✅ Authority Hook copied to clipboard:', completeHook);
                }).catch((err) => {
                    console.error('❌ Failed to copy to clipboard:', err);
                    this.fallbackCopyToClipboard(completeHook);
                });
            } else {
                // Fallback for older browsers
                this.fallbackCopyToClipboard(completeHook);
            }
        },
        
        // Fallback copy method for older browsers
        fallbackCopyToClipboard: function(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showMessage('📋 Authority Hook copied to clipboard!', 'success');
                    console.log('✅ Authority Hook copied via fallback method');
                } else {
                    this.showMessage('❌ Failed to copy to clipboard. Please copy manually.', 'error');
                }
            } catch (err) {
                console.error('❌ Fallback copy failed:', err);
                this.showMessage('❌ Copy not supported. Please copy manually.', 'error');
            }
            
            document.body.removeChild(textArea);
        },
        
        // Save Authority Hook data
        saveAuthorityHook: function() {
            console.log('🔄 Starting Authority Hook save operation...');
            
            const postId = $(this.config.selectors.postIdField).val();
            const nonce = $(this.config.selectors.nonceField).val();
            
            if (!postId || postId === '0') {
                this.showMessage('No post ID found. Please refresh the page.', 'error');
                return;
            }
            
            // Collect Authority Hook data
            const authorityHookData = {
                who: $(this.config.selectors.whoField).val() || '',
                what: $(this.config.selectors.whatField).val() || '',
                when: $(this.config.selectors.whenField).val() || '',
                how: $(this.config.selectors.howField).val() || ''
            };
            
            // Validate data
            const validation = this.validateData(authorityHookData);
            if (!validation.valid) {
                this.showMessage('Please fill in all fields: ' + validation.errors.join(', '), 'error');
                return;
            }
            
            console.log('📊 Saving Authority Hook data:', authorityHookData);
            
            this.showLoading();
            
            // Prepare AJAX data
            const ajaxData = {
                action: this.config.ajax.action,
                nonce: nonce,
                post_id: postId,
                who: authorityHookData.who,
                what: authorityHookData.what,
                when: authorityHookData.when,
                how: authorityHookData.how
            };
            
            // Make AJAX request
            $.ajax({
                url: window.ajaxurl,
                type: 'POST',
                data: ajaxData,
                timeout: this.config.ajax.timeout,
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success) {
                        this.showMessage('✅ Authority Hook saved successfully!', 'success');
                        console.log('✅ Save successful:', response);
                        
                        // Update window data
                        if (window.MKCG_Authority_Hook_Data) {
                            window.MKCG_Authority_Hook_Data.authorityHook = authorityHookData;
                            window.MKCG_Authority_Hook_Data.hasData = true;
                        }
                        
                        // Trigger saved event
                        $(document).trigger('authority-hook-saved', {
                            authorityHook: authorityHookData,
                            timestamp: Date.now()
                        });
                        
                    } else {
                        const errorMessage = response.data?.message || 'Save failed';
                        this.showMessage('❌ ' + errorMessage, 'error');
                        console.error('❌ Save failed:', response);
                    }
                },
                error: (xhr, status, error) => {
                    this.hideLoading();
                    
                    let errorMessage = 'Save operation failed';
                    if (status === 'timeout') {
                        errorMessage = 'Save timed out. Please try again.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMessage = xhr.responseJSON.data.message || errorMessage;
                    }
                    
                    this.showMessage('❌ ' + errorMessage, 'error');
                    console.error('❌ AJAX error:', { xhr, status, error });
                }
            });
        },
        
        // Validate Authority Hook data
        validateData: function(data) {
            const errors = [];
            
            if (!data.who || data.who.trim() === '') {
                errors.push('WHO');
            }
            
            if (!data.what || data.what.trim() === '') {
                errors.push('WHAT');
            }
            
            if (!data.when || data.when.trim() === '') {
                errors.push('WHEN');
            }
            
            if (!data.how || data.how.trim() === '') {
                errors.push('HOW');
            }
            
            return {
                valid: errors.length === 0,
                errors: errors
            };
        },
        
        // Show loading state
        showLoading: function() {
            const $button = $(this.config.selectors.saveButton);
            const $status = $(this.config.selectors.saveStatus);
            
            $button.prop('disabled', true)
                   .html('🔄 Saving...');
            
            $status.show();
            this.showMessage('Saving Authority Hook...', 'info');
        },
        
        // Hide loading state
        hideLoading: function() {
            const $button = $(this.config.selectors.saveButton);
            
            $button.prop('disabled', false)
                   .html('💾 Save Authority Hook');
        },
        
        // Show message to user
        showMessage: function(message, type = 'info') {
            const $messages = $(this.config.selectors.saveMessages);
            const $status = $(this.config.selectors.saveStatus);
            
            const messageClass = `generator__message generator__message--${type}`;
            const messageHtml = `<div class="${messageClass}">${message}</div>`;
            
            $messages.html(messageHtml);
            $status.show();
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    $status.fadeOut();
                }, 5000);
            }
        },
        
        // Debug helper
        debug: function() {
            console.log('🔍 Authority Hook Generator Debug Info:', {
                postId: $(this.config.selectors.postIdField).val(),
                nonce: $(this.config.selectors.nonceField).val()?.substring(0, 10) + '...',
                fields: {
                    who: $(this.config.selectors.whoField).val(),
                    what: $(this.config.selectors.whatField).val(),
                    when: $(this.config.selectors.whenField).val(),
                    how: $(this.config.selectors.howField).val()
                },
                windowData: window.MKCG_Authority_Hook_Data,
                copyButton: $(this.config.selectors.copyToClipboard).length
            });
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize if we're on the Authority Hook generator page
        if ($('[data-generator="authority-hook"]').length) {
            AuthorityHookGenerator.init();
        }
    });
    
    // Make debug function available globally
    window.debugAuthorityHook = function() {
        AuthorityHookGenerator.debug();
    };
    
    console.log('✅ Authority Hook Generator script loaded');
    
})(jQuery);
