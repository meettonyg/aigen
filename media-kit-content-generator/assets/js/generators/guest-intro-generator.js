/**
 * Guest Intro Generator JavaScript
 * 
 * Handles all client-side functionality for the Guest Intro Generator
 * including form submission, API calls, and UI updates.
 */

(function() {
    'use strict';

    // Constants
    const SELECTORS = {
        FORM: '.guest-intro-generator__form',
        GENERATE_BUTTON: '.guest-intro-generator__generate-button',
        LOADING_INDICATOR: '.guest-intro-generator__loading',
        RESULTS_CONTAINER: '.guest-intro-generator__results',
        TABS: '.guest-intro-generator__tab',
        INTRO_CONTENT: '.guest-intro-generator__intro-content',
        COPY_BUTTON: '.guest-intro-generator__copy-button',
        SAVE_BUTTON: '.guest-intro-generator__save-button',
        TONE_RADIO: 'input[name="intro_tone"]',
        HOOK_STYLE_RADIO: 'input[name="intro_hook_style"]'
    };

    // State
    let state = {
        loading: false,
        currentTab: 'short',
        generatedIntros: {
            short: '',
            medium: '',
            long: ''
        },
        settings: {
            intro_tone: 'professional',
            intro_hook_style: 'question'
        }
    };

    /**
     * Toggle builder visibility
     */
    function toggleBuilder(builderType) {
        const builderId = builderType === 'authority-hook' 
            ? 'guest-intro-generator-authority-hook-builder'
            : 'guest-intro-generator-impact-intro-builder';
        
        const builder = document.getElementById(builderId);
        if (!builder) return;
        
        const isHidden = builder.classList.contains('generator__builder--hidden');
        
        if (isHidden) {
            // Show builder
            builder.classList.remove('generator__builder--hidden');
            
            // Update button text
            const buttonId = builderType === 'authority-hook'
                ? 'guest-intro-generator-toggle-authority-builder'
                : 'guest-intro-generator-toggle-impact-builder';
            
            const button = document.getElementById(buttonId);
            if (button) {
                button.textContent = builderType === 'authority-hook' ? 'Hide Components' : 'Hide Impact Intro';
            }
            
            // Populate fields with existing data
            setTimeout(() => {
                populateBuilderFields(builderType);
            }, 100);
            
            // Scroll to builder
            builder.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            // Hide builder
            builder.classList.add('generator__builder--hidden');
            
            // Reset button text
            const buttonId = builderType === 'authority-hook'
                ? 'guest-intro-generator-toggle-authority-builder'
                : 'guest-intro-generator-toggle-impact-builder';
            
            const button = document.getElementById(buttonId);
            if (button) {
                button.textContent = builderType === 'authority-hook' ? 'Edit Components' : 'Edit Impact Intro';
            }
        }
    }

    /**
     * Populate builder fields with existing data
     */
    function populateBuilderFields(builderType) {
        if (!window.MKCG_Guest_Intro_Data) return;
        
        if (builderType === 'authority-hook') {
            const data = window.MKCG_Guest_Intro_Data.authorityHook;
            const fieldMappings = [
                { field: 'who', selector: '#mkcg-who' },
                { field: 'what', selector: '#mkcg-result' },
                { field: 'when', selector: '#mkcg-when' },
                { field: 'how', selector: '#mkcg-how' }
            ];
            
            fieldMappings.forEach(({ field, selector }) => {
                const input = document.querySelector(selector);
                if (input && data[field] && data[field].trim()) {
                    input.value = data[field];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
            
        } else if (builderType === 'impact-intro') {
            const data = window.MKCG_Guest_Intro_Data.impactIntro;
            const fieldMappings = [
                { field: 'where', selector: '#mkcg-where' },
                { field: 'why', selector: '#mkcg-why' }
            ];
            
            fieldMappings.forEach(({ field, selector }) => {
                const input = document.querySelector(selector);
                if (input && data[field] && data[field].trim()) {
                    input.value = data[field];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    }

    /**
     * Initialize the Guest Intro Generator
     */
    function init() {
        console.log('🚀 Guest Intro Generator: Initializing...');
        
        // Setup event listeners
        setupEventListeners();
        
        // Setup field change listeners for real-time updates
        setupFieldChangeListeners();
        
        // Update displays with initial data
        setTimeout(() => {
            updateAuthorityHookDisplay();
            updateImpactIntroDisplay();
        }, 100);
        
        // Check if we need to load existing data
        const postId = getPostId();
        if (postId) {
            loadExistingData(postId);
        }
        
        console.log('✅ Guest Intro Generator: Initialization complete');
    }

    /**
     * Setup field change listeners for real-time updates
     */
    function setupFieldChangeListeners() {
        // Retry setup multiple times to catch dynamically loaded fields
        const setupFields = () => {
            // Authority Hook field listeners
            const authorityFields = ['#mkcg-who', '#mkcg-result', '#mkcg-when', '#mkcg-how'];
            let authorityFieldsFound = 0;
            
            authorityFields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    authorityFieldsFound++;
                    // Remove existing listeners to avoid duplicates
                    field.removeEventListener('input', updateAuthorityHookDisplay);
                    field.removeEventListener('change', updateAuthorityHookDisplay);
                    // Add new listeners
                    field.addEventListener('input', updateAuthorityHookDisplay);
                    field.addEventListener('change', updateAuthorityHookDisplay);
                }
            });
            
            // Impact Intro field listeners
            const impactFields = ['#mkcg-where', '#mkcg-why'];
            let impactFieldsFound = 0;
            
            impactFields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    impactFieldsFound++;
                    // Remove existing listeners to avoid duplicates
                    field.removeEventListener('input', updateImpactIntroDisplay);
                    field.removeEventListener('change', updateImpactIntroDisplay);
                    // Add new listeners
                    field.addEventListener('input', updateImpactIntroDisplay);
                    field.addEventListener('change', updateImpactIntroDisplay);
                }
            });
            
            console.log(`🔧 Guest Intro: Found ${authorityFieldsFound}/4 authority fields, ${impactFieldsFound}/2 impact fields`);
            return { authorityFieldsFound, impactFieldsFound };
        };
        
        // Try immediately
        const initial = setupFields();
        
        // Retry if not all fields found
        if (initial.authorityFieldsFound < 4 || initial.impactFieldsFound < 2) {
            setTimeout(setupFields, 500);
            setTimeout(setupFields, 1000);
            setTimeout(setupFields, 2000);
        }
    }

    /**
     * Update Authority Hook display in real-time
     */
    function updateAuthorityHookDisplay() {
        const who = getFieldValue('mkcg-who');
        const what = getFieldValue('mkcg-result');
        const when = getFieldValue('mkcg-when');
        const how = getFieldValue('mkcg-how');
        
        const display = document.getElementById('guest-intro-generator-authority-hook-text');
        if (!display) return;
        
        if (who && what && when && how) {
            const authorityHook = `I help ${who} ${what} when ${when} through ${how}.`;
            display.textContent = authorityHook;
            display.style.fontStyle = 'normal';
            display.style.color = '';
        } else {
            display.innerHTML = '<em style="color: #666;">Authority Hook will appear here once you fill in the WHO, WHAT, WHEN, and HOW components below.</em>';
        }
    }

    /**
     * Update Impact Intro display in real-time
     */
    function updateImpactIntroDisplay() {
        const where = getFieldValue('mkcg-where');
        const why = getFieldValue('mkcg-why');
        
        const display = document.getElementById('guest-intro-generator-impact-intro-text');
        if (!display) return;
        
        if (where && why) {
            const impactIntro = `${where}. ${why}.`;
            display.textContent = impactIntro;
            display.style.fontStyle = 'normal';
            display.style.color = '';
        } else {
            display.innerHTML = '<em style="color: #666;">Impact Intro will appear here once you fill in the WHERE credentials and WHY mission components below.</em>';
        }
    }

    /**
     * Set up all event listeners
     */
    function setupEventListeners() {
        // Generate button
        const generateButton = document.querySelector(SELECTORS.GENERATE_BUTTON);
        if (generateButton) {
            generateButton.addEventListener('click', handleGenerateClick);
        }

        // Tab switching
        const tabs = document.querySelectorAll(SELECTORS.TABS);
        tabs.forEach(tab => {
            tab.addEventListener('click', handleTabClick);
        });

        // Copy buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest(SELECTORS.COPY_BUTTON)) {
                const button = e.target.closest(SELECTORS.COPY_BUTTON);
                const contentType = button.dataset.type || state.currentTab;
                copyIntroToClipboard(contentType);
            }
        });

        // Save button
        const saveButton = document.querySelector(SELECTORS.SAVE_BUTTON);
        if (saveButton) {
            saveButton.addEventListener('click', handleSaveClick);
        }

        // Settings changes
        const toneRadios = document.querySelectorAll(SELECTORS.TONE_RADIO);
        toneRadios.forEach(radio => {
            radio.addEventListener('change', handleSettingChange);
        });

        const hookStyleRadios = document.querySelectorAll(SELECTORS.HOOK_STYLE_RADIO);
        hookStyleRadios.forEach(radio => {
            radio.addEventListener('change', handleSettingChange);
        });

        // Authority Hook and Impact Intro toggle buttons
        const authorityToggle = document.getElementById('guest-intro-generator-toggle-authority-builder');
        if (authorityToggle) {
            authorityToggle.addEventListener('click', () => toggleBuilder('authority-hook'));
        }

        const impactToggle = document.getElementById('guest-intro-generator-toggle-impact-builder');
        if (impactToggle) {
            impactToggle.addEventListener('click', () => toggleBuilder('impact-intro'));
        }
    }

    /**
     * Get the post ID from the URL
     */
    function getPostId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('post_id') || 0;
    }

    /**
     * Load existing data for a post
     */
    function loadExistingData(postId) {
        // This would load any saved guest intro data
        // For this implementation, we'll just leave it empty
    }

    /**
     * Handle generate button click
     */
    function handleGenerateClick(e) {
        e.preventDefault();
        
        // Get form data
        const form = document.querySelector(SELECTORS.FORM);
        if (!form) return;

        // Validate form
        if (!validateForm(form)) {
            showNotification('Please fill in all required fields.', 'error');
            return;
        }

        // Show loading state
        setLoading(true);

        // Collect form data
        const formData = collectFormData(form);

        // Call the API
        generateGuestIntro(formData);
    }

    /**
     * Validate the form
     */
    function validateForm(form) {
        // Check for required fields
        const guestName = form.querySelector('input[name="guest_name"]');
        if (!guestName || !guestName.value.trim()) {
            guestName.classList.add('error');
            return false;
        }

        return true;
    }

    /**
     * Collect form data
     */
    function collectFormData(form) {
        const formData = new FormData(form);
        const data = {};

        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        // Get Authority Hook data from the builder fields
        const authorityHookData = {
            who: getFieldValue('mkcg-who'),
            what: getFieldValue('mkcg-result'), 
            when: getFieldValue('mkcg-when'),
            how: getFieldValue('mkcg-how')
        };
        
        // Only include authority hook if we have some data
        if (Object.values(authorityHookData).some(val => val && val.trim())) {
            data.authority_hook = authorityHookData;
        }

        // Get Impact Intro data from the builder fields
        const impactIntroData = {
            where: getFieldValue('mkcg-where'),
            why: getFieldValue('mkcg-why')
        };
        
        // Only include impact intro if we have some data
        if (Object.values(impactIntroData).some(val => val && val.trim())) {
            data.impact_intro = impactIntroData;
        }

        return data;
    }

    /**
     * Helper function to get field value
     */
    function getFieldValue(fieldId) {
        const field = document.getElementById(fieldId);
        return field ? field.value.trim() : '';
    }

    /**
     * Generate guest introduction via AJAX
     */
    function generateGuestIntro(formData) {
        // Save current settings to state
        state.settings.intro_tone = formData.intro_tone;
        state.settings.intro_hook_style = formData.intro_hook_style;

        // Create AJAX request
        const ajaxData = new FormData();
        ajaxData.append('action', 'generate_guest_intro');
        ajaxData.append('security', window.mkcg_vars.nonce);
        ajaxData.append('form_data', JSON.stringify(formData));

        // Make the request
        fetch(window.mkcg_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: ajaxData
        })
        .then(response => response.json())
        .then(response => {
            setLoading(false);

            if (response.success) {
                // Save generated intros to state
                state.generatedIntros = {
                    short: response.data.short,
                    medium: response.data.medium,
                    long: response.data.long
                };

                // Show the results
                displayResults();
                showNotification('Guest introductions generated successfully!', 'success');
            } else {
                showNotification(response.data.message || 'Error generating introductions.', 'error');
            }
        })
        .catch(error => {
            setLoading(false);
            showNotification('Error: ' + error.message, 'error');
            console.error('Error generating guest intro:', error);
        });
    }

    /**
     * Set loading state
     */
    function setLoading(isLoading) {
        state.loading = isLoading;
        
        const generateButton = document.querySelector(SELECTORS.GENERATE_BUTTON);
        const loadingIndicator = document.querySelector(SELECTORS.LOADING_INDICATOR);
        
        if (generateButton) {
            if (isLoading) {
                generateButton.setAttribute('disabled', 'disabled');
                generateButton.textContent = 'Generating...';
            } else {
                generateButton.removeAttribute('disabled');
                generateButton.textContent = 'Generate Guest Introductions';
            }
        }
        
        if (loadingIndicator) {
            loadingIndicator.style.display = isLoading ? 'flex' : 'none';
        }
    }

    /**
     * Display the generated results
     */
    function displayResults() {
        const resultsContainer = document.querySelector(SELECTORS.RESULTS_CONTAINER);
        if (!resultsContainer) return;

        // Make results visible
        resultsContainer.style.display = 'block';
        
        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Set the active tab
        setActiveTab(state.currentTab);
        
        // Update the content
        updateActiveContent();
    }

    /**
     * Handle tab click
     */
    function handleTabClick(e) {
        const tab = e.currentTarget;
        const tabType = tab.dataset.type;
        
        if (tabType && state.generatedIntros[tabType]) {
            setActiveTab(tabType);
            updateActiveContent();
        }
    }

    /**
     * Set the active tab
     */
    function setActiveTab(tabType) {
        state.currentTab = tabType;
        
        // Update tab UI
        const tabs = document.querySelectorAll(SELECTORS.TABS);
        tabs.forEach(tab => {
            if (tab.dataset.type === tabType) {
                tab.classList.add('guest-intro-generator__tab--active');
            } else {
                tab.classList.remove('guest-intro-generator__tab--active');
            }
        });
    }

    /**
     * Update the active content based on the current tab
     */
    function updateActiveContent() {
        const content = state.generatedIntros[state.currentTab] || '';
        const contentContainer = document.querySelector(SELECTORS.INTRO_CONTENT);
        
        if (contentContainer) {
            contentContainer.innerHTML = content;
            
            // Update reading time
            updateReadingTime(content, contentContainer);
        }
    }

    /**
     * Update the reading time for the current content
     */
    function updateReadingTime(content, container) {
        const words = content.split(/\s+/).length;
        const readingTimeMinutes = Math.max(1, Math.ceil(words / 130)); // Average reading speed
        
        let readingTimeText;
        if (readingTimeMinutes < 1) {
            readingTimeText = 'Less than 1 min';
        } else if (readingTimeMinutes === 1) {
            readingTimeText = '~1 min';
        } else {
            readingTimeText = `~${readingTimeMinutes} mins`;
        }
        
        // Find or create reading time element
        let readingTimeElement = container.querySelector('.guest-intro-generator__intro-reading-time');
        
        if (!readingTimeElement) {
            readingTimeElement = document.createElement('div');
            readingTimeElement.className = 'guest-intro-generator__intro-reading-time';
            container.appendChild(readingTimeElement);
        }
        
        readingTimeElement.textContent = readingTimeText;
    }

    /**
     * Copy introduction text to clipboard
     */
    function copyIntroToClipboard(type) {
        const content = state.generatedIntros[type] || '';
        
        if (!content) {
            showNotification('No content to copy', 'error');
            return;
        }
        
        // Create a temporary element to hold the text
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showNotification(`${capitalizeFirstLetter(type)} introduction copied to clipboard!`, 'success');
        } catch (err) {
            showNotification('Failed to copy text: ' + err, 'error');
        }
        
        document.body.removeChild(textarea);
    }

    /**
     * Handle save button click
     */
    function handleSaveClick(e) {
        e.preventDefault();
        
        const postId = getPostId();
        if (!postId) {
            showNotification('No post ID found for saving results.', 'error');
            return;
        }
        
        // Prepare data for saving
        const resultsData = {
            short: state.generatedIntros.short,
            medium: state.generatedIntros.medium,
            long: state.generatedIntros.long,
            settings: state.settings
        };
        
        // Create AJAX request
        const ajaxData = new FormData();
        ajaxData.append('action', 'save_guest_intro_results');
        ajaxData.append('security', window.mkcg_vars.nonce);
        ajaxData.append('post_id', postId);
        ajaxData.append('results_data', JSON.stringify(resultsData));
        
        // Make the request
        fetch(window.mkcg_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: ajaxData
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                showNotification('Guest introductions saved successfully!', 'success');
            } else {
                showNotification(response.data.message || 'Error saving introductions.', 'error');
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
            console.error('Error saving guest intro results:', error);
        });
    }

    /**
     * Handle setting change
     */
    function handleSettingChange(e) {
        const setting = e.target.name;
        const value = e.target.value;
        
        // Update state
        if (setting === 'intro_tone') {
            state.settings.intro_tone = value;
        } else if (setting === 'intro_hook_style') {
            state.settings.intro_hook_style = value;
        }
    }

    /**
     * Show a notification message
     */
    function showNotification(message, type = 'info') {
        // Check if we have a notification service
        if (window.simpleNotifications && typeof window.simpleNotifications.show === 'function') {
            window.simpleNotifications.show(message, type);
            return;
        }
        
        // Fallback to alert for simplicity
        alert(message);
    }

    /**
     * Capitalize the first letter of a string
     */
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Initialize on DOM content loaded
    document.addEventListener('DOMContentLoaded', init);

})();
