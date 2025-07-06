/**
 * Biography Generator - Core Functionality
 * 
 * Handles biography generation, preview, and management functionality with a modern vanilla JS approach.
 * Follows the Topics Generator pattern while enhancing with biography-specific features.
 *
 * @package Media_Kit_Content_Generator
 * @version 1.0
 */

(function() {
    'use strict';
    
    /**
     * Biography Generator Class
     * Follows a module pattern for clean organization
     */
    const BiographyGenerator = {
        // Configuration
        config: {
            selectors: {
                container: '.biography-generator',
                authorityHookText: '#biography-generator-authority-hook-text',
                authorityHookBuilder: '#biography-generator-authority-hook-builder',
                toggleAuthorityBuilder: '#biography-generator-toggle-authority-builder',
                impactIntroText: '#biography-generator-impact-intro-text',
                impactIntroBuilder: '#biography-generator-impact-intro-builder',
                toggleImpactBuilder: '#biography-generator-toggle-impact-builder',
                previewDataButton: '#biography-preview-data',
                generateButton: '#biography-generate-with-ai',
                loadingIndicator: '#biography-generator-loading',
                resultsContainer: '#biography-generator-results',
                resultsContent: '#biography-generator-results-content',
                authorityHookField: '#mkcg-authority-hook',
                impactIntroField: '#mkcg-impact-intro',
                postIdField: '#biography-post-id',
                entryIdField: '#biography-entry-id',
                entryKeyField: '#biography-entry-key',
                nonceField: '#biography-nonce',
                // Form fields
                nameField: '#biography-name',
                titleField: '#biography-title',
                organizationField: '#biography-organization',
                toneField: '#biography-tone',
                lengthField: '#biography-length',
                povField: '#biography-pov',
                existingBioField: '#biography-existing',
                notesField: '#biography-notes'
            },
            endpoints: {
                generateBiography: 'mkcg_generate_biography',
                saveBiography: 'mkcg_save_biography',
                modifyBiographyTone: 'mkcg_modify_biography_tone'
            },
            classes: {
                hidden: 'generator__builder--hidden',
                loading: 'generator__loading--hidden',
                results: 'generator__results--hidden'
            }
        },
        
        // Data storage
        data: {
            postId: 0,
            entryId: 0,
            entryKey: '',
            nonce: '',
            authorityHook: '',
            impactIntro: '',
            hasData: false,
            biographies: {
                short: '',
                medium: '',
                long: ''
            }
        },
        
        /**
         * Initialize the Biography Generator
         */
        init: function() {
            // Load configuration from global data if available
            if (window.MKCG_Biography_Data) {
                this.data.postId = window.MKCG_Biography_Data.postId || 0;
                this.data.entryId = window.MKCG_Biography_Data.entryId || 0;
                this.data.entryKey = window.MKCG_Biography_Data.entryKey || '';
                this.data.nonce = window.MKCG_Biography_Data.nonce || '';
                this.data.hasData = window.MKCG_Biography_Data.hasData || false;
            }
            
            // Get references to key elements
            this.elements = {};
            for (const [key, selector] of Object.entries(this.config.selectors)) {
                this.elements[key] = document.querySelector(selector);
            }
            
            // If any critical elements are missing, log an error and exit
            if (!this.elements.container || !this.elements.generateButton) {
                console.error('MKCG Biography: Critical elements not found.');
                return;
            }
            
            // Attach event listeners
            this.attachEventListeners();
            
            // Load Authority Hook and Impact Intro
            this.loadAuthorityHook();
            this.loadImpactIntro();
            
            console.log('MKCG Biography Generator: Initialized');
        },
        
        /**
         * Attach event listeners to elements
         */
        attachEventListeners: function() {
            // Toggle Authority Hook Builder
            if (this.elements.toggleAuthorityBuilder) {
                this.elements.toggleAuthorityBuilder.addEventListener('click', () => this.toggleBuilder('authorityHookBuilder'));
            }
            
            // Toggle Impact Intro Builder
            if (this.elements.toggleImpactBuilder) {
                this.elements.toggleImpactBuilder.addEventListener('click', () => this.toggleBuilder('impactIntroBuilder'));
            }
            
            // Preview Data Button
            if (this.elements.previewDataButton) {
                this.elements.previewDataButton.addEventListener('click', () => this.previewData());
            }
            
            // Generate Button
            if (this.elements.generateButton) {
                this.elements.generateButton.addEventListener('click', () => this.generateBiography());
            }
            
            // Listen for Authority Hook updates from the service
            document.addEventListener('authority-hook-updated', (event) => {
                if (event.detail && event.detail.completeHook) {
                    this.updateAuthorityHook(event.detail.completeHook);
                }
            });
            
            // Listen for Impact Intro updates from the service
            document.addEventListener('impact-intro-updated', (event) => {
                if (event.detail && event.detail.completeIntro) {
                    this.updateImpactIntro(event.detail.completeIntro);
                }
            });
        },
        
        /**
         * Toggle visibility of a builder component
         * @param {string} builderType - Type of builder to toggle (authorityHookBuilder or impactIntroBuilder)
         */
        toggleBuilder: function(builderType) {
            if (this.elements[builderType]) {
                this.elements[builderType].classList.toggle(this.config.classes.hidden);
            }
        },
        
        /**
         * Load Authority Hook from centralized service
         */
        loadAuthorityHook: function() {
            // If no element to display the hook, exit
            if (!this.elements.authorityHookText) return;
            
            // If we already have authority hook data in the hidden field, use it
            if (this.elements.authorityHookField && this.elements.authorityHookField.value) {
                this.updateAuthorityHook(this.elements.authorityHookField.value);
                return;
            }
            
            // Otherwise, try to request it via AJAX if we have a post ID
            if (this.data.postId > 0) {
                // We use the existing AJAX endpoint from the Authority Hook Service
                const data = new FormData();
                data.append('action', 'mkcg_get_authority_hook');
                data.append('post_id', this.data.postId);
                data.append('nonce', this.data.nonce);
                
                fetch(window.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success && response.data) {
                        this.updateAuthorityHook(response.data.complete_hook);
                    } else {
                        console.warn('MKCG Biography: Failed to load Authority Hook data');
                    }
                })
                .catch(error => {
                    console.error('MKCG Biography: Error loading Authority Hook', error);
                });
            }
        },
        
        /**
         * Load Impact Intro from centralized service
         */
        loadImpactIntro: function() {
            // If no element to display the intro, exit
            if (!this.elements.impactIntroText) return;
            
            // If we already have impact intro data in the hidden field, use it
            if (this.elements.impactIntroField && this.elements.impactIntroField.value) {
                this.updateImpactIntro(this.elements.impactIntroField.value);
                return;
            }
            
            // Otherwise, try to request it via AJAX if we have a post ID
            if (this.data.postId > 0) {
                // We use the existing AJAX endpoint from the Impact Intro Service
                const data = new FormData();
                data.append('action', 'mkcg_get_impact_intro');
                data.append('post_id', this.data.postId);
                data.append('nonce', this.data.nonce);
                
                fetch(window.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success && response.data) {
                        this.updateImpactIntro(response.data.complete_intro);
                    } else {
                        console.warn('MKCG Biography: Failed to load Impact Intro data');
                    }
                })
                .catch(error => {
                    console.error('MKCG Biography: Error loading Impact Intro', error);
                });
            }
        },
        
        /**
         * Update Authority Hook display
         * @param {string} hookText - Complete Authority Hook text
         */
        updateAuthorityHook: function(hookText) {
            if (!hookText) return;
            
            // Update data
            this.data.authorityHook = hookText;
            
            // Update display
            if (this.elements.authorityHookText) {
                this.elements.authorityHookText.textContent = hookText;
            }
            
            // Update hidden field
            if (this.elements.authorityHookField) {
                this.elements.authorityHookField.value = hookText;
            }
        },
        
        /**
         * Update Impact Intro display
         * @param {string} introText - Complete Impact Intro text
         */
        updateImpactIntro: function(introText) {
            if (!introText) return;
            
            // Update data
            this.data.impactIntro = introText;
            
            // Update display
            if (this.elements.impactIntroText) {
                this.elements.impactIntroText.textContent = introText;
            }
            
            // Update hidden field
            if (this.elements.impactIntroField) {
                this.elements.impactIntroField.value = introText;
            }
        },
        
        /**
         * Preview the data that will be used for generation
         */
        previewData: function() {
            // Collect all form data
            const formData = this.collectFormData();
            
            // Create a formatted preview
            const preview = `
                <h3>Authority Hook:</h3>
                <p>${formData.authority_hook || 'Not provided'}</p>
                
                <h3>Impact Intro:</h3>
                <p>${formData.impact_intro || 'Not provided'}</p>
                
                <h3>Personal Information:</h3>
                <p><strong>Name:</strong> ${formData.name || 'Not provided'}</p>
                <p><strong>Title:</strong> ${formData.title || 'Not provided'}</p>
                <p><strong>Organization:</strong> ${formData.organization || 'Not provided'}</p>
                
                <h3>Biography Settings:</h3>
                <p><strong>Tone:</strong> ${this.getToneLabel(formData.tone)}</p>
                <p><strong>Length:</strong> ${this.getLengthLabel(formData.length)}</p>
                <p><strong>Point of View:</strong> ${this.getPOVLabel(formData.pov)}</p>
                
                <h3>Additional Content:</h3>
                <p><strong>Existing Biography:</strong> ${formData.existing_bio ? 'Provided' : 'Not provided'}</p>
                <p><strong>Additional Notes:</strong> ${formData.additional_notes ? 'Provided' : 'Not provided'}</p>
            `;
            
            // Display preview in an alert
            alert('Biography Generation Preview:\n\n' + 
                  'Authority Hook: ' + (formData.authority_hook || 'Not provided') + '\n\n' +
                  'Impact Intro: ' + (formData.impact_intro || 'Not provided') + '\n\n' +
                  'Name: ' + (formData.name || 'Not provided') + '\n' +
                  'Title: ' + (formData.title || 'Not provided') + '\n' +
                  'Organization: ' + (formData.organization || 'Not provided') + '\n\n' +
                  'Tone: ' + this.getToneLabel(formData.tone) + '\n' +
                  'Length: ' + this.getLengthLabel(formData.length) + '\n' +
                  'Point of View: ' + this.getPOVLabel(formData.pov) + '\n\n' +
                  'Existing Bio: ' + (formData.existing_bio ? 'Provided' : 'Not provided') + '\n' +
                  'Additional Notes: ' + (formData.additional_notes ? 'Provided' : 'Not provided')
            );
        },
        
        /**
         * Get human-readable label for tone setting
         * @param {string} tone - Tone value
         * @return {string} Human-readable tone label
         */
        getToneLabel: function(tone) {
            const toneLabels = {
                'professional': 'Professional',
                'conversational': 'Conversational',
                'authoritative': 'Authoritative',
                'friendly': 'Friendly'
            };
            
            return toneLabels[tone] || tone;
        },
        
        /**
         * Get human-readable label for length setting
         * @param {string} length - Length value
         * @return {string} Human-readable length label
         */
        getLengthLabel: function(length) {
            const lengthLabels = {
                'short': 'Short (50-75 words)',
                'medium': 'Medium (100-150 words)',
                'long': 'Long (200-300 words)'
            };
            
            return lengthLabels[length] || length;
        },
        
        /**
         * Get human-readable label for point of view setting
         * @param {string} pov - Point of view value
         * @return {string} Human-readable POV label
         */
        getPOVLabel: function(pov) {
            const povLabels = {
                'first': 'First Person (I/My)',
                'third': 'Third Person (He/She/They)'
            };
            
            return povLabels[pov] || pov;
        },
        
        /**
         * Collect all form data needed for biography generation
         * @return {Object} Form data object
         */
        collectFormData: function() {
            // Get data from form fields
            const formData = {
                // Core fields
                post_id: this.elements.postIdField ? parseInt(this.elements.postIdField.value) : this.data.postId,
                entry_id: this.elements.entryIdField ? parseInt(this.elements.entryIdField.value) : this.data.entryId,
                entry_key: this.elements.entryKeyField ? this.elements.entryKeyField.value : this.data.entryKey,
                nonce: this.elements.nonceField ? this.elements.nonceField.value : this.data.nonce,
                
                // Component data
                authority_hook: this.elements.authorityHookField ? this.elements.authorityHookField.value : this.data.authorityHook,
                impact_intro: this.elements.impactIntroField ? this.elements.impactIntroField.value : this.data.impactIntro,
                
                // Personal info
                name: this.elements.nameField ? this.elements.nameField.value : '',
                title: this.elements.titleField ? this.elements.titleField.value : '',
                organization: this.elements.organizationField ? this.elements.organizationField.value : '',
                
                // Settings
                tone: this.elements.toneField ? this.elements.toneField.value : 'professional',
                length: this.elements.lengthField ? this.elements.lengthField.value : 'medium',
                pov: this.elements.povField ? this.elements.povField.value : 'third',
                
                // Additional content
                existing_bio: this.elements.existingBioField ? this.elements.existingBioField.value : '',
                additional_notes: this.elements.notesField ? this.elements.notesField.value : ''
            };
            
            return formData;
        },
        
        /**
         * Validate form data before generation
         * @param {Object} formData - Collected form data
         * @return {Object} Validation result with status and message
         */
        validateFormData: function(formData) {
            // Required fields
            if (!formData.name) {
                return {
                    valid: false,
                    message: 'Please enter your full name.'
                };
            }
            
            if (!formData.title) {
                return {
                    valid: false,
                    message: 'Please enter your professional title.'
                };
            }
            
            // Check if we have at least one of authority hook or impact intro
            if (!formData.authority_hook && !formData.impact_intro) {
                return {
                    valid: false,
                    message: 'Please provide either an Authority Hook or Impact Intro before generating your biography.'
                };
            }
            
            return { valid: true };
        },
        
        /**
         * Generate the biography using collected data
         */
        generateBiography: function() {
            // Collect all form data
            const formData = this.collectFormData();
            
            // Validate form data
            const validation = this.validateFormData(formData);
            if (!validation.valid) {
                alert(validation.message);
                return;
            }
            
            // Show loading indicator
            if (this.elements.loadingIndicator) {
                this.elements.loadingIndicator.classList.remove(this.config.classes.loading);
            }
            
            // Disable generate button to prevent multiple submissions
            if (this.elements.generateButton) {
                this.elements.generateButton.setAttribute('disabled', 'disabled');
            }
            
            // Prepare AJAX request
            const data = new FormData();
            
            // Add all form data to request
            for (const [key, value] of Object.entries(formData)) {
                data.append(key, value);
            }
            
            // Add action
            data.append('action', this.config.endpoints.generateBiography);
            
            // Send request
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                // Re-enable generate button
                if (this.elements.generateButton) {
                    this.elements.generateButton.removeAttribute('disabled');
                }
                
                // Hide loading indicator
                if (this.elements.loadingIndicator) {
                    this.elements.loadingIndicator.classList.add(this.config.classes.loading);
                }
                
                if (response.success && response.data) {
                    // Store generated biographies
                    this.data.biographies = response.data.biographies;
                    
                    // Display results
                    this.displayResults(response.data);
                } else {
                    console.error('MKCG Biography: Generation failed', response);
                    alert('Failed to generate biography. Please try again.\n\n' + 
                          (response.data && response.data.message ? response.data.message : 'Unknown error.'));
                }
            })
            .catch(error => {
                console.error('MKCG Biography: Generation error', error);
                
                // Re-enable generate button
                if (this.elements.generateButton) {
                    this.elements.generateButton.removeAttribute('disabled');
                }
                
                // Hide loading indicator
                if (this.elements.loadingIndicator) {
                    this.elements.loadingIndicator.classList.add(this.config.classes.loading);
                }
                
                alert('An error occurred while generating your biography. Please try again.');
            });
        },
        
        /**
         * Display the generated biography results
         * @param {Object} data - Response data containing biographies
         */
        displayResults: function(data) {
            // If no results container, exit
            if (!this.elements.resultsContainer || !this.elements.resultsContent) {
                console.error('MKCG Biography: Results container not found');
                return;
            }
            
            // Show results container
            this.elements.resultsContainer.classList.remove(this.config.classes.results);
            
            // Create HTML for each biography version
            const biographiesHtml = `
                <div class="biography-generator__tabs">
                    <button class="biography-generator__tab biography-generator__tab--active" data-tab="short">
                        Short <span class="biography-generator__length-indicator biography-generator__length-short">(50-75 words)</span>
                    </button>
                    <button class="biography-generator__tab" data-tab="medium">
                        Medium <span class="biography-generator__length-indicator biography-generator__length-medium">(100-150 words)</span>
                    </button>
                    <button class="biography-generator__tab" data-tab="long">
                        Long <span class="biography-generator__length-indicator biography-generator__length-long">(200-300 words)</span>
                    </button>
                </div>
                
                <div class="biography-generator__result-item" id="biography-tab-short" style="display:block;">
                    <div class="biography-generator__result-header">
                        <h4 class="biography-generator__result-title">Short Biography</h4>
                        <span class="biography-generator__result-badge">SOCIAL MEDIA & BRIEF INTROS</span>
                    </div>
                    <div class="biography-generator__result-content">
                        ${data.biographies.short.replace(/\n/g, '<br>')}
                    </div>
                    <div class="biography-generator__result-actions">
                        <button type="button" class="biography-generator__action-button" data-action="copy" data-version="short">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                            </svg>
                            Copy to Clipboard
                        </button>
                    </div>
                </div>
                
                <div class="biography-generator__result-item" id="biography-tab-medium" style="display:none;">
                    <div class="biography-generator__result-header">
                        <h4 class="biography-generator__result-title">Medium Biography</h4>
                        <span class="biography-generator__result-badge">WEBSITES & SPEAKER INTROS</span>
                    </div>
                    <div class="biography-generator__result-content">
                        ${data.biographies.medium.replace(/\n/g, '<br>')}
                    </div>
                    <div class="biography-generator__result-actions">
                        <button type="button" class="biography-generator__action-button" data-action="copy" data-version="medium">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                            </svg>
                            Copy to Clipboard
                        </button>
                    </div>
                </div>
                
                <div class="biography-generator__result-item" id="biography-tab-long" style="display:none;">
                    <div class="biography-generator__result-header">
                        <h4 class="biography-generator__result-title">Long Biography</h4>
                        <span class="biography-generator__result-badge">DETAILED MARKETING MATERIALS</span>
                    </div>
                    <div class="biography-generator__result-content">
                        ${data.biographies.long.replace(/\n/g, '<br>')}
                    </div>
                    <div class="biography-generator__result-actions">
                        <button type="button" class="biography-generator__action-button" data-action="copy" data-version="long">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                            </svg>
                            Copy to Clipboard
                        </button>
                    </div>
                </div>
                
                <div class="biography-generator__button-group" style="justify-content: center; margin-top: 30px;">
                    <a href="?generator=biography&post_id=${this.data.postId}&entry=${this.data.entryId}&results=true" class="generator__button generator__button--primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 19H5V5h7V3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.6l-9.8 9.8 1.4 1.4L19 6.4V10h2V3h-7z"/>
                        </svg>
                        View Full Results Page
                    </a>
                </div>
            `;
            
            // Update results content
            this.elements.resultsContent.innerHTML = biographiesHtml;
            
            // Add event listeners for tabs
            const tabs = this.elements.resultsContent.querySelectorAll('.biography-generator__tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('biography-generator__tab--active'));
                    // Add active class to clicked tab
                    tab.classList.add('biography-generator__tab--active');
                    
                    // Get tab version
                    const version = tab.getAttribute('data-tab');
                    
                    // Hide all tab content
                    const tabContents = this.elements.resultsContent.querySelectorAll('.biography-generator__result-item');
                    tabContents.forEach(content => {
                        content.style.display = 'none';
                    });
                    
                    // Show the selected tab content
                    const selectedContent = this.elements.resultsContent.querySelector(`#biography-tab-${version}`);
                    if (selectedContent) {
                        selectedContent.style.display = 'block';
                    }
                });
            });
            
            // Add event listeners for copy buttons
            const copyButtons = this.elements.resultsContent.querySelectorAll('[data-action="copy"]');
            copyButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Get version
                    const version = button.getAttribute('data-version');
                    
                    // Get biography text
                    const biography = data.biographies[version];
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(biography).then(() => {
                        // Temporary feedback
                        const originalText = button.innerHTML;
                        button.innerHTML = `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            Copied!
                        `;
                        
                        // Reset after 2 seconds
                        setTimeout(() => {
                            button.innerHTML = originalText;
                        }, 2000);
                    }).catch(err => {
                        console.error('Failed to copy text: ', err);
                        alert('Failed to copy to clipboard. Please try again.');
                    });
                });
            });
            
            // Scroll to results
            this.elements.resultsContainer.scrollIntoView({ behavior: 'smooth' });
        }
    };
    
    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        BiographyGenerator.init();
    });
    
    // Make BiographyGenerator available globally for other scripts
    window.BiographyGenerator = BiographyGenerator;
})();