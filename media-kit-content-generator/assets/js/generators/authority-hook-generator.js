/**
 * Authority Hook Generator JavaScript - WITH AUDIENCE MANAGEMENT INTEGRATION
 * Handles dedicated Authority Hook page functionality
 * ROOT FIX: Properly integrates with Authority Hook Builder audience management system
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
                hiddenField: '#mkcg-authority-hook',
                // AUDIENCE MANAGEMENT SELECTORS
                tagInput: '#tag_input',
                addTagButton: '#add_tag',
                tagsContainer: '#tags_container',
                audienceCount: '#audience-count',
                selectedCount: '#selected-count'
            },
            ajax: {
                action: 'mkcg_save_authority_hook',
                timeout: 30000
            }
        },
        
        // ROOT FIX: Add audience management state
        audienceState: {
            tags: [],
            initialized: false
        },
        
        // Initialize the generator
        init: function() {
            console.log('🔧 Authority Hook Generator: Initializing with audience management...');
            
            this.bindEvents();
            this.setupRealTimeUpdates();
            this.populateFields();
            
            // ROOT FIX: Initialize audience management system
            this.initializeAudienceManagement();
            
            console.log('✅ Authority Hook Generator: Initialized successfully with audience management');
        },
        
        // ROOT FIX: Initialize audience management system
        initializeAudienceManagement: function() {
            console.log('🎯 ROOT FIX: Initializing audience management integration...');
            
            // Wait for Authority Hook Builder to be available
            const waitForBuilder = () => {
                if (typeof window.AuthorityHookBuilder !== 'undefined' || 
                    document.getElementById(this.config.selectors.tagInput.substring(1))) {
                    this.setupAudienceManager();
                    this.loadExistingAudiences();
                    this.setupExampleChips();
                    console.log('✅ Audience management system initialized');
                } else {
                    console.log('⏳ Waiting for Authority Hook Builder...');
                    setTimeout(waitForBuilder, 500);
                }
            };
            
            waitForBuilder();
        },
        
        // ROOT FIX: Setup audience manager integration
        setupAudienceManager: function() {
            const tagInput = $(this.config.selectors.tagInput);
            const addButton = $(this.config.selectors.addTagButton);
            
            if (!tagInput.length || !addButton.length) {
                console.warn('⚠️ Audience manager elements not found');
                return;
            }
            
            const self = this;
            
            // Add tag button click
            addButton.off('click').on('click', function(e) {
                e.preventDefault();
                const text = tagInput.val().trim();
                if (text) {
                    self.addAudienceTag(text, true);
                    tagInput.val('');
                }
            });
            
            // Enter key in input
            tagInput.off('keypress').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    const text = $(this).val().trim();
                    if (text) {
                        self.addAudienceTag(text, true);
                        $(this).val('');
                    }
                }
            });
            
            console.log('✅ Audience manager events bound');
        },
        
        // ROOT FIX: Add audience tag to management system
        addAudienceTag: function(text, checked = true) {
            // Check for duplicates
            const existing = this.audienceState.tags.find(tag => tag.text === text);
            if (existing) {
                console.log('⚠️ Audience tag already exists:', text);
                return;
            }
            
            // Add to state
            const tagData = { text: text, checked: checked };
            this.audienceState.tags.push(tagData);
            
            // Create visual tag
            this.createVisualTag(tagData);
            
            // Update WHO field
            this.updateWhoField();
            
            // Update status
            this.updateAudienceStatus();
            
            console.log('✅ Added audience tag:', text);
        },
        
        // ROOT FIX: Create visual tag element
        createVisualTag: function(tagData) {
            const container = $(this.config.selectors.tagsContainer);
            if (!container.length) return;
            
            const tagEl = $(`
                <div class="audience-tag ${tagData.checked ? 'active' : ''}">
                    <input type="checkbox" ${tagData.checked ? 'checked' : ''}>
                    <span>${this.escapeHtml(tagData.text)}</span>
                    <span class="credential-remove">&times;</span>
                </div>
            `);
            
            const self = this;
            
            // Remove button
            tagEl.find('.credential-remove').on('click', function() {
                self.removeAudienceTag(tagData.text);
            });
            
            // Checkbox change
            tagEl.find('input[type="checkbox"]').on('change', function() {
                tagData.checked = $(this).is(':checked');
                tagEl.toggleClass('active', tagData.checked);
                self.updateWhoField();
                self.updateAudienceStatus();
            });
            
            container.append(tagEl);
        },
        
        // ROOT FIX: Remove audience tag
        removeAudienceTag: function(text) {
            // Remove from state
            this.audienceState.tags = this.audienceState.tags.filter(tag => tag.text !== text);
            
            // Remove visual element
            $(this.config.selectors.tagsContainer + ' .audience-tag').each(function() {
                if ($(this).find('span').first().text() === text) {
                    $(this).remove();
                }
            });
            
            // Update WHO field
            this.updateWhoField();
            
            // Update status
            this.updateAudienceStatus();
            
            console.log('✅ Removed audience tag:', text);
        },
        
        // ROOT FIX: Update WHO field with selected audiences
        updateWhoField: function() {
            const checkedTags = this.audienceState.tags.filter(tag => tag.checked);
            let text = '';
            
            if (checkedTags.length === 1) {
                text = checkedTags[0].text;
            } else if (checkedTags.length === 2) {
                text = checkedTags.map(tag => tag.text).join(' and ');
            } else if (checkedTags.length > 2) {
                const texts = checkedTags.map(tag => tag.text);
                text = texts.slice(0, -1).join(', ') + ', and ' + texts.slice(-1);
            }
            
            $(this.config.selectors.whoField).val(text);
            
            // Trigger update events
            this.updatePreview();
            this.updateHiddenField();
            
            console.log('✅ Updated WHO field:', text);
        },
        
        // ROOT FIX: Update audience status display
        updateAudienceStatus: function() {
            const total = this.audienceState.tags.length;
            const checked = this.audienceState.tags.filter(tag => tag.checked).length;
            
            $(this.config.selectors.audienceCount).text(total);
            $(this.config.selectors.selectedCount).text(checked);
        },
        
        // ROOT FIX: Load existing audiences from WHO field
        loadExistingAudiences: function() {
            const whoField = $(this.config.selectors.whoField);
            const existingValue = whoField.val();
            
            if (!existingValue || existingValue.trim() === '' || this.audienceState.tags.length > 0) {
                return;
            }
            
            // Parse existing WHO field value into audiences
            const audiences = existingValue.trim().split(/,\s*and\s*|\s*and\s*|,\s*/).filter(Boolean);
            
            audiences.forEach(text => {
                if (text && text.trim()) {
                    this.addAudienceTag(text.trim(), true);
                }
            });
            
            this.updateAudienceStatus();
            
            console.log('✅ Loaded existing audiences:', audiences);
        },
        
        // ROOT FIX: Setup example chips integration
        setupExampleChips: function() {
            const self = this;
            
            // Use event delegation for example chips
            $(document).off('click.authority-hook-examples').on('click.authority-hook-examples', '.tag__add-link', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const tag = $(this).closest('.tag');
                const targetField = tag.attr('data-target');
                const value = tag.attr('data-value');
                
                console.log('🎯 Example chip clicked:', { targetField, value });
                
                if (targetField && value) {
                    if (targetField === 'mkcg-who') {
                        // Add to audience management system
                        self.addAudienceTag(value, true);
                        
                        // Visual feedback
                        $(this).text('✓ Added to List')
                               .css({ backgroundColor: '#d4edda', color: '#155724' });
                        
                        setTimeout(() => {
                            $(this).text('+ Add')
                                   .css({ backgroundColor: '', color: '' });
                        }, 2000);
                    } else {
                        // Regular field population
                        $('#' + targetField).val(value).trigger('input');
                        
                        // Visual feedback
                        $(this).text('✓ Added')
                               .css({ backgroundColor: '#d4edda', color: '#155724' });
                        
                        setTimeout(() => {
                            $(this).text('+ Add')
                                   .css({ backgroundColor: '', color: '' });
                        }, 2000);
                    }
                }
            });
            
            console.log('✅ Example chips integration setup');
        },
        
        // ROOT FIX: Collect audience data for saving
        collectAudienceData: function() {
            console.log('🔄 ROOT FIX: Collecting audience data for save...');
            
            // Priority 1: Check audience management system
            const checkedTags = this.audienceState.tags.filter(tag => tag.checked);
            if (checkedTags.length > 0) {
                const audienceText = this.formatAudienceList(checkedTags.map(tag => tag.text));
                console.log('✅ Using audience management data:', audienceText);
                return audienceText;
            }
            
            // Priority 2: Check WHO field directly
            const whoField = $(this.config.selectors.whoField).val();
            if (whoField && whoField.trim() && whoField.trim() !== 'your audience') {
                console.log('✅ Using WHO field data:', whoField.trim());
                return whoField.trim();
            }
            
            // No audience data found
            console.log('⚠️ No audience data found');
            return '';
        },
        
        // ROOT FIX: Format audience list with proper grammar
        formatAudienceList: function(audiences) {
            if (!audiences || audiences.length === 0) {
                return '';
            }
            
            if (audiences.length === 1) {
                return audiences[0];
            }
            
            if (audiences.length === 2) {
                return audiences.join(' and ');
            }
            
            // For 3+ audiences: "A, B, and C"
            const lastAudience = audiences.pop();
            return audiences.join(', ') + ', and ' + lastAudience;
        },
        
        // ROOT FIX: HTML escape utility
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
        
        // ROOT FIX: Save Authority Hook data with audience management integration
        saveAuthorityHook: function() {
            console.log('🔄 Starting Authority Hook save operation with audience management...');
            
            const postId = $(this.config.selectors.postIdField).val();
            const nonce = $(this.config.selectors.nonceField).val();
            
            if (!postId || postId === '0') {
                this.showMessage('No post ID found. Please refresh the page.', 'error');
                return;
            }
            
            // ROOT FIX: Collect Authority Hook data with proper audience integration
            const authorityHookData = {
                who: this.collectAudienceData(), // Use audience management system
                what: $(this.config.selectors.whatField).val() || '',
                when: $(this.config.selectors.whenField).val() || '',
                how: $(this.config.selectors.howField).val() || ''
            };
            
            // Also ensure WHO field is updated with latest audience data
            if (authorityHookData.who) {
                $(this.config.selectors.whoField).val(authorityHookData.who);
            }
            
            // Validate data
            const validation = this.validateData(authorityHookData);
            if (!validation.valid) {
                this.showMessage('Please fill in all fields: ' + validation.errors.join(', '), 'error');
                return;
            }
            
            console.log('📊 Saving Authority Hook data with audience management:', authorityHookData);
            console.log('🎯 Audience data source:', {
                audienceManagementTags: this.audienceState.tags.length,
                checkedTags: this.audienceState.tags.filter(tag => tag.checked).length,
                whoFieldValue: $(this.config.selectors.whoField).val(),
                finalWhoValue: authorityHookData.who
            });
            
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
                        console.log('✅ Save successful with audience management:', response);
                        
                        // Update window data
                        if (window.MKCG_Authority_Hook_Data) {
                            window.MKCG_Authority_Hook_Data.authorityHook = authorityHookData;
                            window.MKCG_Authority_Hook_Data.hasData = true;
                        }
                        
                        // Trigger saved event
                        $(document).trigger('authority-hook-saved', {
                            authorityHook: authorityHookData,
                            audienceData: {
                                tags: this.audienceState.tags,
                                selectedCount: this.audienceState.tags.filter(tag => tag.checked).length
                            },
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
    
    // ROOT FIX: Enhanced debug functions with audience management
    window.debugAuthorityHook = function() {
        AuthorityHookGenerator.debug();
    };
    
    // ROOT FIX: Debug audience management system
    window.debugAudienceManagement = function() {
        console.log('🔍 Authority Hook Generator Audience Management Debug:', {
            audienceState: AuthorityHookGenerator.audienceState,
            audienceTags: AuthorityHookGenerator.audienceState.tags,
            checkedTags: AuthorityHookGenerator.audienceState.tags.filter(tag => tag.checked),
            whoFieldValue: $(AuthorityHookGenerator.config.selectors.whoField).val(),
            collectedAudienceData: AuthorityHookGenerator.collectAudienceData(),
            audienceManagerElements: {
                tagInput: $(AuthorityHookGenerator.config.selectors.tagInput).length,
                addButton: $(AuthorityHookGenerator.config.selectors.addTagButton).length,
                container: $(AuthorityHookGenerator.config.selectors.tagsContainer).length
            },
            exampleChips: $('.tag__add-link').length
        });
    };
    
    // ROOT FIX: Test audience management functionality
    window.testAudienceManagement = function() {
        console.log('🧪 Testing audience management...');
        
        // Test adding a tag
        AuthorityHookGenerator.addAudienceTag('Test Audience', true);
        
        // Test collecting data
        const collected = AuthorityHookGenerator.collectAudienceData();
        console.log('✅ Test complete. Collected data:', collected);
        
        return collected;
    };
    
    console.log('✅ Authority Hook Generator script loaded with audience management integration');
    console.log('🔧 Debug functions: window.debugAuthorityHook(), window.debugAudienceManagement(), window.testAudienceManagement()');
    
})(jQuery);
