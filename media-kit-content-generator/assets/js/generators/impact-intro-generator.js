/**
 * Impact Intro Generator JavaScript - WITH CREDENTIAL MANAGEMENT INTEGRATION
 * Handles dedicated Impact Intro page functionality
 * ROOT FIX: Properly integrates with Impact Intro Builder credential management system
 */

(function($) {
    'use strict';
    
    // Impact Intro Generator object
    window.ImpactIntroGenerator = {
        
        // Configuration
        config: {
            selectors: {
                saveButton: '#save-button',
                saveStatus: '#save-status', 
                saveMessages: '#save-messages',
                postIdField: '#post-id',
                nonceField: '#nonce',
                whereField: '#mkcg-where',
                whyField: '#mkcg-why',
                copyToClipboard: '#copy-impact-intro-btn',
                hiddenField: '#mkcg-impact-intro',
                // CREDENTIAL MANAGEMENT SELECTORS
                credentialInput: '#credential_input',
                addCredentialButton: '#add_credential',
                credentialsContainer: '#credentials_container',
                credentialCount: '#credential-count',
                selectedCredentialCount: '#selected-credential-count'
            },
            ajax: {
                action: 'mkcg_save_impact_intro',
                timeout: 30000
            }
        },
        
        // ROOT FIX: Add credential management state
        credentialState: {
            credentials: [],
            initialized: false
        },
        
        // Initialize the generator
        init: function() {
            console.log('🔧 Impact Intro Generator: Initializing with credential management...');
            
            this.bindEvents();
            this.setupRealTimeUpdates();
            this.populateFields();
            
            // ROOT FIX: Initialize credential management system
            this.initializeCredentialManagement();
            
            console.log('✅ Impact Intro Generator: Initialized successfully with credential management');
        },
        
        // ROOT FIX: Initialize credential management system
        initializeCredentialManagement: function() {
            console.log('🎯 ROOT FIX: Initializing credential management integration...');
            
            // Wait for Impact Intro Builder to be available
            const waitForBuilder = () => {
                if (document.getElementById(this.config.selectors.credentialInput.substring(1))) {
                    this.setupCredentialManager();
                    this.loadExistingCredentials();
                    this.setupExampleChips();
                    console.log('✅ Credential management system initialized');
                } else {
                    console.log('⏳ Waiting for Impact Intro Builder...');
                    setTimeout(waitForBuilder, 500);
                }
            };
            
            waitForBuilder();
        },
        
        // ROOT FIX: Setup credential manager integration
        setupCredentialManager: function() {
            const credentialInput = $(this.config.selectors.credentialInput);
            const addButton = $(this.config.selectors.addCredentialButton);
            
            if (!credentialInput.length || !addButton.length) {
                console.warn('⚠️ Credential manager elements not found');
                return;
            }
            
            const self = this;
            
            // Add credential button click
            addButton.off('click').on('click', function(e) {
                e.preventDefault();
                const text = credentialInput.val().trim();
                if (text) {
                    self.addCredential(text, true);
                    credentialInput.val('');
                }
            });
            
            // Enter key in input
            credentialInput.off('keypress').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    const text = $(this).val().trim();
                    if (text) {
                        self.addCredential(text, true);
                        $(this).val('');
                    }
                }
            });
            
            console.log('✅ Credential manager events bound');
        },
        
        // ROOT FIX: Add credential to management system
        addCredential: function(text, checked = true) {
            // Check for duplicates
            const existing = this.credentialState.credentials.find(cred => cred.text === text);
            if (existing) {
                console.log('⚠️ Credential already exists:', text);
                return;
            }
            
            // Add to state
            const credData = { text: text, checked: checked };
            this.credentialState.credentials.push(credData);
            
            // Create visual credential
            this.createVisualCredential(credData);
            
            // Update WHERE field
            this.updateWhereField();
            
            // Update status
            this.updateCredentialStatus();
            
            console.log('✅ Added credential:', text);
        },
        
        // ROOT FIX: Create visual credential element
        createVisualCredential: function(credData) {
            const container = $(this.config.selectors.credentialsContainer);
            if (!container.length) return;
            
            const credEl = $(`
                <div class="credential-tag ${credData.checked ? 'active' : ''}">
                    <input type="checkbox" ${credData.checked ? 'checked' : ''}>
                    <span>${this.escapeHtml(credData.text)}</span>
                    <span class="credential-remove">&times;</span>
                </div>
            `);
            
            const self = this;
            
            // Remove button
            credEl.find('.credential-remove').on('click', function() {
                self.removeCredential(credData.text);
            });
            
            // Checkbox change
            credEl.find('input[type="checkbox"]').on('change', function() {
                credData.checked = $(this).is(':checked');
                credEl.toggleClass('active', credData.checked);
                self.updateWhereField();
                self.updateCredentialStatus();
            });
            
            container.append(credEl);
        },
        
        // ROOT FIX: Remove credential
        removeCredential: function(text) {
            // Remove from state
            this.credentialState.credentials = this.credentialState.credentials.filter(cred => cred.text !== text);
            
            // Remove visual element
            $(this.config.selectors.credentialsContainer + ' .credential-tag').each(function() {
                if ($(this).find('span').first().text() === text) {
                    $(this).remove();
                }
            });
            
            // Update WHERE field
            this.updateWhereField();
            
            // Update status
            this.updateCredentialStatus();
            
            console.log('✅ Removed credential:', text);
        },
        
        // ROOT FIX: Update WHERE field with selected credentials
        updateWhereField: function() {
            const checkedCreds = this.credentialState.credentials.filter(cred => cred.checked);
            let text = '';
            
            if (checkedCreds.length === 1) {
                text = checkedCreds[0].text;
            } else if (checkedCreds.length === 2) {
                text = checkedCreds.map(cred => cred.text).join(' and ');
            } else if (checkedCreds.length > 2) {
                const texts = checkedCreds.map(cred => cred.text);
                text = texts.slice(0, -1).join(', ') + ', and ' + texts.slice(-1);
            }
            
            $(this.config.selectors.whereField).val(text);
            
            // Trigger update events
            this.updatePreview();
            this.updateHiddenField();
            
            console.log('✅ Updated WHERE field:', text);
        },
        
        // ROOT FIX: Update credential status display
        updateCredentialStatus: function() {
            const total = this.credentialState.credentials.length;
            const checked = this.credentialState.credentials.filter(cred => cred.checked).length;
            
            $(this.config.selectors.credentialCount).text(total);
            $(this.config.selectors.selectedCredentialCount).text(checked);
        },
        
        // ROOT FIX: Load existing credentials from WHERE field
        loadExistingCredentials: function() {
            const whereField = $(this.config.selectors.whereField);
            const existingValue = whereField.val();
            
            if (!existingValue || existingValue.trim() === '' || this.credentialState.credentials.length > 0) {
                return;
            }
            
            // Parse existing WHERE field value into credentials
            const credentials = existingValue.trim().split(/,\s*and\s*|\s*and\s*|,\s*/).filter(Boolean);
            
            credentials.forEach(text => {
                if (text && text.trim()) {
                    this.addCredential(text.trim(), true);
                }
            });
            
            this.updateCredentialStatus();
            
            console.log('✅ Loaded existing credentials:', credentials);
        },
        
        // ROOT FIX: Setup example chips integration
        setupExampleChips: function() {
            const self = this;
            
            // Use event delegation for example chips
            $(document).off('click.impact-intro-examples').on('click.impact-intro-examples', '.tag__add-link', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const tag = $(this).closest('.tag');
                const targetField = tag.attr('data-target');
                const value = tag.attr('data-value');
                
                console.log('🎯 Example chip clicked:', { targetField, value });
                
                if (targetField && value) {
                    if (targetField === 'mkcg-where') {
                        // Add to credential management system
                        self.addCredential(value, true);
                        
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
        
        // ROOT FIX: Collect credential data for saving
        collectCredentialData: function() {
            console.log('🔄 ROOT FIX: Collecting credential data for save...');
            
            // Priority 1: Check credential management system
            const checkedCreds = this.credentialState.credentials.filter(cred => cred.checked);
            if (checkedCreds.length > 0) {
                const credentialText = this.formatCredentialList(checkedCreds.map(cred => cred.text));
                console.log('✅ Using credential management data:', credentialText);
                return credentialText;
            }
            
            // Priority 2: Check WHERE field directly
            const whereField = $(this.config.selectors.whereField).val();
            if (whereField && whereField.trim()) {
                console.log('✅ Using WHERE field data:', whereField.trim());
                return whereField.trim();
            }
            
            // No credential data found
            console.log('⚠️ No credential data found');
            return '';
        },
        
        // ROOT FIX: Format credential list with proper grammar
        formatCredentialList: function(credentials) {
            if (!credentials || credentials.length === 0) {
                return '';
            }
            
            if (credentials.length === 1) {
                return credentials[0];
            }
            
            if (credentials.length === 2) {
                return credentials.join(' and ');
            }
            
            // For 3+ credentials: "A, B, and C"
            const lastCredential = credentials.pop();
            return credentials.join(', ') + ', and ' + lastCredential;
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
                self.saveImpactIntro();
            });
            
            // Field change listeners for real-time updates
            const fieldSelectors = [
                this.config.selectors.whereField,
                this.config.selectors.whyField
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
            $(document).on('input change', this.config.selectors.whereField + ',' + 
                                          this.config.selectors.whyField, function() {
                self.updatePreview();
                self.updateHiddenField();
            });
            
            console.log('✅ Real-time updates configured');
        },
        
        // Populate fields with existing data
        populateFields: function() {
            if (window.MKCG_Impact_Intro_Data && window.MKCG_Impact_Intro_Data.hasData) {
                const data = window.MKCG_Impact_Intro_Data.impactIntro;
                
                $(this.config.selectors.whereField).val(data.where || '');
                $(this.config.selectors.whyField).val(data.why || '');
                
                this.updatePreview();
                this.updateHiddenField();
                
                console.log('✅ Fields populated with existing data');
            }
        },
        
        // Update the live preview
        updatePreview: function() {
            const where = $(this.config.selectors.whereField).val() || '[WHERE]';
            const why = $(this.config.selectors.whyField).val() || '[WHY]';
            
            const completeIntro = `I've ${where}. My mission is to ${why}.`;
            
            // Update preview content with highlighting
            const previewElement = $('#impact-intro-content');
            if (previewElement.length) {
                previewElement.html(
                    `I've <span class="impact-intro__highlight">${where}</span>. ` +
                    `My mission is to <span class="impact-intro__highlight">${why}</span>.`
                );
            }
            
            // Trigger custom event for other components
            $(document).trigger('impact-intro-updated', {
                where: where,
                why: why,
                completeIntro: completeIntro
            });
        },
        
        // Update hidden field with complete intro
        updateHiddenField: function() {
            const where = $(this.config.selectors.whereField).val() || '';
            const why = $(this.config.selectors.whyField).val() || '';
            
            const completeIntro = `I've ${where}. My mission is to ${why}.`;
            $(this.config.selectors.hiddenField).val(completeIntro);
        },
        
        // Copy complete impact intro to clipboard
        copyToClipboard: function() {
            const where = $(this.config.selectors.whereField).val() || '';
            const why = $(this.config.selectors.whyField).val() || '';
            
            // Validate that we have content to copy
            if (!where && !why) {
                this.showMessage('⚠️ Please fill in the impact intro fields before copying.', 'warning');
                return;
            }
            
            const completeIntro = `I've ${where}. My mission is to ${why}.`;
            
            // Use modern clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(completeIntro).then(() => {
                    this.showMessage('📋 Impact Intro copied to clipboard!', 'success');
                    console.log('✅ Impact Intro copied to clipboard:', completeIntro);
                }).catch((err) => {
                    console.error('❌ Failed to copy to clipboard:', err);
                    this.fallbackCopyToClipboard(completeIntro);
                });
            } else {
                // Fallback for older browsers
                this.fallbackCopyToClipboard(completeIntro);
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
                    this.showMessage('📋 Impact Intro copied to clipboard!', 'success');
                    console.log('✅ Impact Intro copied via fallback method');
                } else {
                    this.showMessage('❌ Failed to copy to clipboard. Please copy manually.', 'error');
                }
            } catch (err) {
                console.error('❌ Fallback copy failed:', err);
                this.showMessage('❌ Copy not supported. Please copy manually.', 'error');
            }
            
            document.body.removeChild(textArea);
        },
        
        // ROOT FIX: Save Impact Intro data with credential management integration
        saveImpactIntro: function() {
            console.log('🔄 Starting Impact Intro save operation with credential management...');
            
            const postId = $(this.config.selectors.postIdField).val();
            const nonce = $(this.config.selectors.nonceField).val();
            
            if (!postId || postId === '0') {
                this.showMessage('No post ID found. Please refresh the page.', 'error');
                return;
            }
            
            // ROOT FIX: Collect Impact Intro data with proper credential integration
            const impactIntroData = {
                where: this.collectCredentialData(), // Use credential management system
                why: $(this.config.selectors.whyField).val() || ''
            };
            
            // Also ensure WHERE field is updated with latest credential data
            if (impactIntroData.where) {
                $(this.config.selectors.whereField).val(impactIntroData.where);
            }
            
            // Validate data
            const validation = this.validateData(impactIntroData);
            if (!validation.valid) {
                this.showMessage('Please fill in all fields: ' + validation.errors.join(', '), 'error');
                return;
            }
            
            console.log('📊 Saving Impact Intro data with credential management:', impactIntroData);
            console.log('🎯 Credential data source:', {
                credentialManagementCredentials: this.credentialState.credentials.length,
                checkedCredentials: this.credentialState.credentials.filter(cred => cred.checked).length,
                whereFieldValue: $(this.config.selectors.whereField).val(),
                finalWhereValue: impactIntroData.where
            });
            
            this.showLoading();
            
            // Prepare AJAX data
            const ajaxData = {
                action: this.config.ajax.action,
                nonce: nonce,
                post_id: postId,
                where: impactIntroData.where,
                why: impactIntroData.why
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
                        this.showMessage('✅ Impact Intro saved successfully!', 'success');
                        console.log('✅ Save successful with credential management:', response);
                        
                        // Update window data
                        if (window.MKCG_Impact_Intro_Data) {
                            window.MKCG_Impact_Intro_Data.impactIntro = impactIntroData;
                            window.MKCG_Impact_Intro_Data.hasData = true;
                        }
                        
                        // Trigger saved event
                        $(document).trigger('impact-intro-saved', {
                            impactIntro: impactIntroData,
                            credentialData: {
                                credentials: this.credentialState.credentials,
                                selectedCount: this.credentialState.credentials.filter(cred => cred.checked).length
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
        
        // Validate Impact Intro data
        validateData: function(data) {
            const errors = [];
            
            if (!data.where || data.where.trim() === '') {
                errors.push('WHERE');
            }
            
            if (!data.why || data.why.trim() === '') {
                errors.push('WHY');
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
            this.showMessage('Saving Impact Intro...', 'info');
        },
        
        // Hide loading state
        hideLoading: function() {
            const $button = $(this.config.selectors.saveButton);
            
            $button.prop('disabled', false)
                   .html('💾 Save Impact Intro');
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
            console.log('🔍 Impact Intro Generator Debug Info:', {
                postId: $(this.config.selectors.postIdField).val(),
                nonce: $(this.config.selectors.nonceField).val()?.substring(0, 10) + '...',
                fields: {
                    where: $(this.config.selectors.whereField).val(),
                    why: $(this.config.selectors.whyField).val()
                },
                windowData: window.MKCG_Impact_Intro_Data,
                copyButton: $(this.config.selectors.copyToClipboard).length
            });
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize if we're on the Impact Intro generator page
        if ($('[data-generator="impact-intro"]').length) {
            ImpactIntroGenerator.init();
        }
    });
    
    // ROOT FIX: Enhanced debug functions with credential management
    window.debugImpactIntro = function() {
        ImpactIntroGenerator.debug();
    };
    
    // ROOT FIX: Debug credential management system
    window.debugCredentialManagement = function() {
        console.log('🔍 Impact Intro Generator Credential Management Debug:', {
            credentialState: ImpactIntroGenerator.credentialState,
            credentials: ImpactIntroGenerator.credentialState.credentials,
            checkedCredentials: ImpactIntroGenerator.credentialState.credentials.filter(cred => cred.checked),
            whereFieldValue: $(ImpactIntroGenerator.config.selectors.whereField).val(),
            collectedCredentialData: ImpactIntroGenerator.collectCredentialData(),
            credentialManagerElements: {
                credentialInput: $(ImpactIntroGenerator.config.selectors.credentialInput).length,
                addButton: $(ImpactIntroGenerator.config.selectors.addCredentialButton).length,
                container: $(ImpactIntroGenerator.config.selectors.credentialsContainer).length
            },
            exampleChips: $('.tag__add-link').length
        });
    };
    
    // ROOT FIX: Test credential management functionality
    window.testCredentialManagement = function() {
        console.log('🧪 Testing credential management...');
        
        // Test adding a credential
        ImpactIntroGenerator.addCredential('Test Credential', true);
        
        // Test collecting data
        const collected = ImpactIntroGenerator.collectCredentialData();
        console.log('✅ Test complete. Collected data:', collected);
        
        return collected;
    };
    
    console.log('✅ Impact Intro Generator script loaded with credential management integration');
    console.log('🔧 Debug functions: window.debugImpactIntro(), window.debugCredentialManagement(), window.testCredentialManagement()');
    
})(jQuery);
