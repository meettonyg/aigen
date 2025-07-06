/**
 * Biography Results - Core Functionality
 * 
 * Handles biography results display, management, and modification functionality with a modern vanilla JS approach.
 * Enhances the results page with interactive features and user experience improvements.
 *
 * @package Media_Kit_Content_Generator
 * @version 1.0
 */

(function() {
    'use strict';
    
    /**
     * Biography Results Class
     * Follows a module pattern for clean organization
     */
    const BiographyResults = {
        // Configuration
        config: {
            selectors: {
                container: '.biography-generator',
                resultsTabs: '.biography-generator__results-tab',
                resultItems: '.biography-generator__result-item',
                toneOptions: '.biography-generator__tone-option',
                toneRadios: '.biography-generator__tone-radio',
                updateToneButton: '#biography-update-tone',
                copyButtons: '[id^="copy-"][id$="-bio"]',
                downloadButtons: '[id^="download-"][id$="-bio"]',
                emailButtons: '[id^="email-"][id$="-bio"]',
                saveButton: '#biography-save-to-post-meta',
                saveStatus: '#biography-save-status',
                saveStatusText: '#biography-save-status-text',
                // Hidden fields
                postIdField: '#biography-post-id',
                entryIdField: '#biography-entry-id',
                currentToneField: '#biography-current-tone',
                currentPovField: '#biography-current-pov',
                nonceField: '#biography-nonce'
            },
            endpoints: {
                saveBiography: 'mkcg_save_biography_to_post_meta',
                modifyBiographyTone: 'mkcg_modify_biography_tone'
            },
            classes: {
                activeTab: 'biography-generator__results-tab--active',
                activeTone: 'biography-generator__tone-option--active',
                loading: 'generator__button--loading'
            }
        },
        
        // Data storage
        data: {
            postId: 0,
            entryId: 0,
            nonce: '',
            currentTone: 'professional',
            currentPov: 'third',
            biographies: {
                short: '',
                medium: '',
                long: ''
            },
            personalInfo: {
                name: '',
                title: '',
                organization: ''
            }
        },
        
        /**
         * Initialize the Biography Results
         */
        init: function() {
            // Load configuration from global data if available
            if (window.MKCG_Biography_Results) {
                this.data.postId = window.MKCG_Biography_Results.postId || 0;
                this.data.entryId = window.MKCG_Biography_Results.entryId || 0;
                this.data.nonce = window.MKCG_Biography_Results.nonce || '';
                this.data.biographies = window.MKCG_Biography_Results.biographies || { short: '', medium: '', long: '' };
                this.data.currentTone = window.MKCG_Biography_Results.settings?.tone || 'professional';
                this.data.currentPov = window.MKCG_Biography_Results.settings?.pov || 'third';
                this.data.personalInfo = window.MKCG_Biography_Results.personalInfo || { name: '', title: '', organization: '' };
            }
            
            // Get references to key elements
            this.elements = {};
            for (const [key, selector] of Object.entries(this.config.selectors)) {
                if (selector.includes('#')) {
                    // Single element
                    this.elements[key] = document.querySelector(selector);
                } else {
                    // Multiple elements
                    this.elements[key] = document.querySelectorAll(selector);
                }
            }
            
            // If any critical elements are missing, log an error and exit
            if (!this.elements.container) {
                console.error('MKCG Biography Results: Critical elements not found.');
                return;
            }
            
            // Attach event listeners
            this.attachEventListeners();
            
            console.log('MKCG Biography Results: Initialized');
        },
        
        /**
         * Attach event listeners to elements
         */
        attachEventListeners: function() {
            // Tab switching functionality
            if (this.elements.resultsTabs && this.elements.resultsTabs.length) {
                this.elements.resultsTabs.forEach(tab => {
                    tab.addEventListener('click', () => this.switchTab(tab));
                });
            }
            
            // Tone selector functionality
            if (this.elements.toneOptions && this.elements.toneOptions.length) {
                this.elements.toneOptions.forEach(option => {
                    option.addEventListener('click', () => this.selectTone(option));
                });
            }
            
            // Update tone button
            if (this.elements.updateToneButton) {
                this.elements.updateToneButton.addEventListener('click', () => this.updateTone());
            }
            
            // Copy to clipboard functionality
            if (this.elements.copyButtons && this.elements.copyButtons.length) {
                this.elements.copyButtons.forEach(button => {
                    button.addEventListener('click', () => this.copyToClipboard(button));
                });
            }
            
            // Download as text functionality
            if (this.elements.downloadButtons && this.elements.downloadButtons.length) {
                this.elements.downloadButtons.forEach(button => {
                    button.addEventListener('click', () => this.downloadAsText(button));
                });
            }
            
            // Email functionality
            if (this.elements.emailButtons && this.elements.emailButtons.length) {
                this.elements.emailButtons.forEach(button => {
                    button.addEventListener('click', () => this.emailBiography(button));
                });
            }
            
            // Save to WordPress Post Meta functionality
            if (this.elements.saveButton) {
                this.elements.saveButton.addEventListener('click', () => this.saveBiographies());
            }
        },
        
        /**
         * Switch between biography tabs
         * @param {HTMLElement} tab - The tab element that was clicked
         */
        switchTab: function(tab) {
            // Get the tab's data-tab attribute
            const tabType = tab.getAttribute('data-tab');
            
            // Remove active class from all tabs
            this.elements.resultsTabs.forEach(t => {
                t.classList.remove(this.config.classes.activeTab);
            });
            
            // Add active class to clicked tab
            tab.classList.add(this.config.classes.activeTab);
            
            // Hide all result items
            this.elements.resultItems.forEach(item => {
                item.style.display = 'none';
            });
            
            // Show the selected result item
            const selectedItem = document.getElementById(`biography-${tabType}-result`);
            if (selectedItem) {
                selectedItem.style.display = 'block';
            }
        },
        
        /**
         * Select a tone option
         * @param {HTMLElement} option - The tone option element that was clicked
         */
        selectTone: function(option) {
            // Find the radio input inside this option
            const radio = option.querySelector('input[type="radio"]');
            if (!radio) return;
            
            // Uncheck all radios
            this.elements.toneRadios.forEach(r => {
                r.checked = false;
            });
            
            // Check this radio
            radio.checked = true;
            
            // Remove active class from all options
            this.elements.toneOptions.forEach(o => {
                o.classList.remove(this.config.classes.activeTone);
            });
            
            // Add active class to clicked option
            option.classList.add(this.config.classes.activeTone);
            
            // Update hidden field
            if (this.elements.currentToneField) {
                this.elements.currentToneField.value = radio.value;
            }
            
            // Update data
            this.data.currentTone = radio.value;
        },
        
        /**
         * Update biography tone via AJAX
         */
        updateTone: function() {
            // Get selected tone
            const selectedTone = document.querySelector('input[name="biography-tone"]:checked').value;
            
            // Get post ID and nonce
            const postId = this.elements.postIdField ? parseInt(this.elements.postIdField.value) : this.data.postId;
            const nonce = this.elements.nonceField ? this.elements.nonceField.value : this.data.nonce;
            
            // Add loading state to button
            if (this.elements.updateToneButton) {
                this.elements.updateToneButton.classList.add(this.config.classes.loading);
                this.elements.updateToneButton.setAttribute('disabled', 'disabled');
            }
            
            // Prepare AJAX request
            const data = new FormData();
            data.append('action', this.config.endpoints.modifyBiographyTone);
            data.append('post_id', postId);
            data.append('tone', selectedTone);
            data.append('nonce', nonce);
            
            // Send request
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                // Remove loading state
                if (this.elements.updateToneButton) {
                    this.elements.updateToneButton.classList.remove(this.config.classes.loading);
                    this.elements.updateToneButton.removeAttribute('disabled');
                }
                
                if (response.success && response.data) {
                    // Update biography content
                    if (response.data.biographies) {
                        const biographies = response.data.biographies;
                        
                        // Update data
                        this.data.biographies = biographies;
                        
                        // Update UI
                        if (biographies.short) {
                            const shortContent = document.getElementById('biography-short-content');
                            if (shortContent) {
                                shortContent.innerHTML = biographies.short.replace(/\n/g, '<br>');
                            }
                        }
                        
                        if (biographies.medium) {
                            const mediumContent = document.getElementById('biography-medium-content');
                            if (mediumContent) {
                                mediumContent.innerHTML = biographies.medium.replace(/\n/g, '<br>');
                            }
                        }
                        
                        if (biographies.long) {
                            const longContent = document.getElementById('biography-long-content');
                            if (longContent) {
                                longContent.innerHTML = biographies.long.replace(/\n/g, '<br>');
                            }
                        }
                        
                        // Display success message
                        alert('Biography tone updated successfully!');
                    }
                } else {
                    console.error('Error updating tone:', response);
                    alert('Failed to update biography tone. Please try again.');
                }
            })
            .catch(error => {
                console.error('AJAX request failed:', error);
                
                // Remove loading state
                if (this.elements.updateToneButton) {
                    this.elements.updateToneButton.classList.remove(this.config.classes.loading);
                    this.elements.updateToneButton.removeAttribute('disabled');
                }
                
                alert('Failed to update biography tone. Please check your internet connection and try again.');
            });
        },
        
        /**
         * Copy biography to clipboard
         * @param {HTMLElement} button - The copy button that was clicked
         */
        copyToClipboard: function(button) {
            // Get the biography type from the button ID
            const bioType = button.id.replace('copy-', '').replace('-bio', '');
            
            // Get the biography text
            const bioText = this.data.biographies[bioType];
            
            // Copy to clipboard
            navigator.clipboard.writeText(bioText).then(() => {
                // Change button text temporarily
                const originalText = button.innerHTML;
                button.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> Copied!';
                
                // Reset button text after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy to clipboard. Please try again.');
            });
        },
        
        /**
         * Download biography as text file
         * @param {HTMLElement} button - The download button that was clicked
         */
        downloadAsText: function(button) {
            // Get the biography type from the button ID
            const bioType = button.id.replace('download-', '').replace('-bio', '');
            
            // Get the biography text
            const bioText = this.data.biographies[bioType];
            
            // Get personal info
            const name = this.data.personalInfo.name || 'Professional';
            
            // Create file name
            const fileName = `${name.replace(/\s+/g, '_')}_${bioType}_biography.txt`;
            
            // Create temporary link
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(bioText));
            element.setAttribute('download', fileName);
            
            element.style.display = 'none';
            document.body.appendChild(element);
            
            element.click();
            
            document.body.removeChild(element);
        },
        
        /**
         * Email biography using mailto link
         * @param {HTMLElement} button - The email button that was clicked
         */
        emailBiography: function(button) {
            // Get the biography type from the button ID
            const bioType = button.id.replace('email-', '').replace('-bio', '');
            
            // Get the biography text
            const bioText = this.data.biographies[bioType];
            
            // Get personal info
            const name = this.data.personalInfo.name || 'Professional';
            
            // Create email subject and body
            const subject = `Professional Biography - ${name} (${bioType.charAt(0).toUpperCase() + bioType.slice(1)} Version)`;
            const body = `Here is my professional biography (${bioType} version):\n\n${bioText}`;
            
            // Create mailto link
            const mailtoLink = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            
            // Open email client
            window.location.href = mailtoLink;
        },
        
        /**
         * Save biographies to WordPress Post Meta
         */
        saveBiographies: function() {
            // Get post ID and entry ID
            const postId = this.elements.postIdField ? parseInt(this.elements.postIdField.value) : this.data.postId;
            const entryId = this.elements.entryIdField ? parseInt(this.elements.entryIdField.value) : this.data.entryId;
            const nonce = this.elements.nonceField ? this.elements.nonceField.value : this.data.nonce;
            
            // Show save status
            if (this.elements.saveStatus) {
                this.elements.saveStatus.style.display = 'block';
            }
            
            if (this.elements.saveStatusText) {
                this.elements.saveStatusText.textContent = 'Saving biographies...';
            }
            
            // Add loading state to button
            if (this.elements.saveButton) {
                this.elements.saveButton.classList.add(this.config.classes.loading);
                this.elements.saveButton.setAttribute('disabled', 'disabled');
            }
            
            // Prepare AJAX request
            const data = new FormData();
            data.append('action', this.config.endpoints.saveBiography);
            data.append('post_id', postId);
            // Note: entryId not needed for WordPress post meta saving
            data.append('nonce', nonce);
            data.append('short_bio', this.data.biographies.short);
            data.append('medium_bio', this.data.biographies.medium);
            data.append('long_bio', this.data.biographies.long);
            data.append('tone', this.data.currentTone);
            data.append('pov', this.data.currentPov);
            
            // Send request
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.json())
            .then(response => {
                // Remove loading state
                if (this.elements.saveButton) {
                    this.elements.saveButton.classList.remove(this.config.classes.loading);
                    this.elements.saveButton.removeAttribute('disabled');
                }
                
                if (response.success) {
                    // Update save status
                    if (this.elements.saveStatus) {
                        this.elements.saveStatus.style.background = '#d4edda';
                        this.elements.saveStatus.style.borderColor = '#c3e6cb';
                    }
                    
                    if (this.elements.saveStatusText) {
                        this.elements.saveStatusText.textContent = 'Biographies saved successfully!';
                    }
                    
                    // Hide status after 3 seconds
                    setTimeout(() => {
                        if (this.elements.saveStatus) {
                            this.elements.saveStatus.style.display = 'none';
                        }
                    }, 3000);
                } else {
                    console.error('Error saving biographies:', response);
                    
                    // Update save status
                    if (this.elements.saveStatus) {
                        this.elements.saveStatus.style.background = '#f8d7da';
                        this.elements.saveStatus.style.borderColor = '#f5c6cb';
                    }
                    
                    if (this.elements.saveStatusText) {
                        this.elements.saveStatusText.textContent = 'Failed to save biographies. Please try again.';
                    }
                }
            })
            .catch(error => {
                console.error('AJAX request failed:', error);
                
                // Remove loading state
                if (this.elements.saveButton) {
                    this.elements.saveButton.classList.remove(this.config.classes.loading);
                    this.elements.saveButton.removeAttribute('disabled');
                }
                
                // Update save status
                if (this.elements.saveStatus) {
                    this.elements.saveStatus.style.background = '#f8d7da';
                    this.elements.saveStatus.style.borderColor = '#f5c6cb';
                }
                
                if (this.elements.saveStatusText) {
                    this.elements.saveStatusText.textContent = 'Failed to save biographies. Please check your internet connection.';
                }
            });
        }
    };
    
    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        BiographyResults.init();
    });
    
    // Make BiographyResults available globally for other scripts
    window.BiographyResults = BiographyResults;
})();