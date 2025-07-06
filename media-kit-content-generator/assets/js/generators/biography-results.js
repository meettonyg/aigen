/**
 * Biography Results - Enhanced Functionality
 * 
 * Comprehensive biography results management with advanced features including:
 * - Biography comparison and management
 * - Inline editing capabilities
 * - Real-time character counting and statistics
 * - Advanced sharing and export options
 * - Keyboard shortcuts and accessibility
 * - Auto-save and version control
 * - Modern UX with smooth animations
 *
 * @package Media_Kit_Content_Generator
 * @version 2.0
 */

(function() {
    'use strict';
    
    /**
     * Enhanced Biography Results Class
     * Comprehensive module for biography management and user interactions
     */
    const BiographyResults = {
        // Configuration
        config: {
            selectors: {
                container: '.biography-generator',
                resultsTabs: '.biography-generator__results-tab',
                resultItems: '.biography-generator__result-item',
                resultContent: '[id$="-content"]',
                toneOptions: '.biography-generator__tone-option',
                toneRadios: '.biography-generator__tone-radio',
                updateToneButton: '#biography-update-tone',
                copyButtons: '[id^="copy-"][id$="-bio"]',
                downloadButtons: '[id^="download-"][id$="-bio"]',
                emailButtons: '[id^="email-"][id$="-bio"]',
                shareButtons: '[id^="share-"][id$="-bio"]',
                editButtons: '[id^="edit-"][id$="-bio"]',
                printButtons: '[id^="print-"][id$="-bio"]',
                saveButton: '#biography-save-to-post-meta',
                saveStatus: '#biography-save-status',
                saveStatusText: '#biography-save-status-text',
                compareButton: '#biography-compare-versions',
                regenerateButton: '#biography-regenerate',
                // Statistics elements
                statsContainer: '.biography-generator__stats',
                wordCount: '.biography-generator__word-count',
                charCount: '.biography-generator__char-count',
                readingTime: '.biography-generator__reading-time',
                // Hidden fields
                postIdField: '#biography-post-id',
                entryIdField: '#biography-entry-id',
                currentToneField: '#biography-current-tone',
                currentPovField: '#biography-current-pov',
                nonceField: '#biography-nonce'
            },
            endpoints: {
                saveBiography: 'mkcg_save_biography_to_post_meta',
                modifyBiographyTone: 'mkcg_modify_biography_tone',
                regenerateBiography: 'mkcg_regenerate_biography'
            },
            classes: {
                activeTab: 'biography-generator__results-tab--active',
                activeTone: 'biography-generator__tone-option--active',
                loading: 'generator__button--loading',
                editing: 'biography-generator__result-item--editing',
                comparing: 'biography-generator--comparing',
                animating: 'biography-generator--animating'
            },
            animations: {
                duration: 300,
                easing: 'ease-in-out'
            },
            autoSave: {
                enabled: true,
                delay: 2000
            },
            shortcuts: {
                enabled: true,
                keymap: {
                    'ctrl+1': 'switch-short',
                    'ctrl+2': 'switch-medium', 
                    'ctrl+3': 'switch-long',
                    'ctrl+c': 'copy-current',
                    'ctrl+e': 'edit-current',
                    'ctrl+s': 'save-all',
                    'ctrl+p': 'print-current',
                    'ctrl+z': 'undo',
                    'ctrl+y': 'redo',
                    'escape': 'cancel-action'
                }
            }
        },
        
        // Data storage and state management
        data: {
            postId: 0,
            entryId: 0,
            nonce: '',
            currentTab: 'short',
            currentTone: 'professional',
            currentPov: 'third',
            isEditing: false,
            isComparing: false,
            biographies: {
                short: '',
                medium: '',
                long: ''
            },
            originalBiographies: {
                short: '',
                medium: '',
                long: ''
            },
            editHistory: [],
            editHistoryIndex: -1,
            personalInfo: {
                name: '',
                title: '',
                organization: ''
            },
            autoSaveTimer: null,
            lastSaved: null
        },
        
        // Statistics calculation cache
        statsCache: new Map(),
        
        /**
         * Initialize the Biography Results
         */
        init: function() {
            // Load configuration from global data if available
            this.loadInitialData();
            
            // Get references to key elements
            this.cacheElements();
            
            // If any critical elements are missing, log an error and exit
            if (!this.elements.container) {
                console.error('MKCG Biography Results: Critical elements not found.');
                return;
            }
            
            // Initialize all functionality
            this.initializeFeatures();
            
            console.log('MKCG Biography Results: Enhanced functionality initialized');
        },
        
        /**
         * Load initial data from global configuration
         */
        loadInitialData: function() {
            if (window.MKCG_Biography_Results) {
                const globalData = window.MKCG_Biography_Results;
                this.data.postId = globalData.postId || 0;
                this.data.entryId = globalData.entryId || 0;
                this.data.nonce = globalData.nonce || '';
                this.data.biographies = globalData.biographies || { short: '', medium: '', long: '' };
                this.data.originalBiographies = JSON.parse(JSON.stringify(this.data.biographies));
                this.data.currentTone = globalData.settings?.tone || 'professional';
                this.data.currentPov = globalData.settings?.pov || 'third';
                this.data.personalInfo = globalData.personalInfo || { name: '', title: '', organization: '' };
            }
        },
        
        /**
         * Cache DOM elements for performance
         */
        cacheElements: function() {
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
        },
        
        /**
         * Initialize all enhanced features
         */
        initializeFeatures: function() {
            // Core functionality
            this.attachEventListeners();
            this.initializeStatistics();
            
            // Enhanced features
            this.initializeKeyboardShortcuts();
            this.initializeEditingFeatures();
            this.initializeComparisonFeatures();
            this.initializeAutoSave();
            this.initializeAnimations();
            this.initializePrintFeatures();
            this.initializeAdvancedSharing();
            this.initializeAdvancedDownloads();
            
            // Set initial active tab
            this.setActiveTab('short');
        },
        
        /**
         * Attach event listeners to elements
         */
        attachEventListeners: function() {
            // Tab switching with enhanced animations
            if (this.elements.resultsTabs && this.elements.resultsTabs.length) {
                this.elements.resultsTabs.forEach(tab => {
                    tab.addEventListener('click', (e) => this.switchTabEnhanced(tab));
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
            
            // Basic action buttons
            this.attachBasicActionListeners();
            
            // Enhanced action buttons
            this.attachEnhancedActionListeners();
            
            // Content editing listeners
            this.attachEditingListeners();
            
            // Global event listeners
            this.attachGlobalListeners();
        },
        
        /**
         * Attach basic action listeners (copy, download, email, save)
         */
        attachBasicActionListeners: function() {
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
         * Attach enhanced action listeners
         */
        attachEnhancedActionListeners: function() {
            // Share functionality
            if (this.elements.shareButtons && this.elements.shareButtons.length) {
                this.elements.shareButtons.forEach(button => {
                    button.addEventListener('click', () => this.openShareModal(button));
                });
            }
            
            // Edit functionality
            if (this.elements.editButtons && this.elements.editButtons.length) {
                this.elements.editButtons.forEach(button => {
                    button.addEventListener('click', () => this.toggleEdit(button));
                });
            }
            
            // Print functionality
            if (this.elements.printButtons && this.elements.printButtons.length) {
                this.elements.printButtons.forEach(button => {
                    button.addEventListener('click', () => this.printBiography(button));
                });
            }
            
            // Compare versions functionality
            if (this.elements.compareButton) {
                this.elements.compareButton.addEventListener('click', () => this.toggleComparison());
            }
            
            // Regenerate functionality
            if (this.elements.regenerateButton) {
                this.elements.regenerateButton.addEventListener('click', () => this.regenerateBiography());
            }
        },
        
        /**
         * Attach editing-specific listeners
         */
        attachEditingListeners: function() {
            // Content editing listeners will be attached dynamically when editing mode is enabled
            document.addEventListener('input', (e) => {
                if (e.target.classList.contains('biography-generator__editable-content')) {
                    this.handleContentEdit(e);
                }
            });
            
            document.addEventListener('blur', (e) => {
                if (e.target.classList.contains('biography-generator__editable-content')) {
                    this.handleEditBlur(e);
                }
            });
        },
        
        /**
         * Attach global listeners for keyboard shortcuts and window events
         */
        attachGlobalListeners: function() {
            // Window beforeunload for unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges()) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            
            // Visibility change for auto-save
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && this.hasUnsavedChanges()) {
                    this.autoSave();
                }
            });
        },
        
        /**
         * Enhanced tab switching with smooth animations
         */
        switchTabEnhanced: function(tab) {
            const tabType = tab.getAttribute('data-tab');
            
            if (this.data.currentTab === tabType) return;
            
            // Start animation
            this.elements.container.classList.add(this.config.classes.animating);
            
            // Remove active class from all tabs
            this.elements.resultsTabs.forEach(t => {
                t.classList.remove(this.config.classes.activeTab);
            });
            
            // Add active class to clicked tab
            tab.classList.add(this.config.classes.activeTab);
            
            // Animate tab switch
            this.animateTabSwitch(tabType);
            
            // Update current tab
            this.data.currentTab = tabType;
            
            // Update statistics for new tab
            this.updateStatistics(tabType);
            
            // Save tab preference
            this.saveTabPreference(tabType);
        },
        
        /**
         * Animate tab switch with smooth transitions
         */
        animateTabSwitch: function(tabType) {
            const currentActive = document.querySelector('.biography-generator__result-item[style*="block"]');
            const newActive = document.getElementById(`biography-${tabType}-result`);
            
            if (!newActive) return;
            
            // Fade out current
            if (currentActive && currentActive !== newActive) {
                currentActive.style.opacity = '0';
                currentActive.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    currentActive.style.display = 'none';
                    currentActive.style.opacity = '';
                    currentActive.style.transform = '';
                }, this.config.animations.duration / 2);
            }
            
            // Fade in new
            setTimeout(() => {
                newActive.style.display = 'block';
                newActive.style.opacity = '0';
                newActive.style.transform = 'translateX(20px)';
                
                // Force reflow
                newActive.offsetHeight;
                
                newActive.style.transition = `opacity ${this.config.animations.duration}ms ${this.config.animations.easing}, transform ${this.config.animations.duration}ms ${this.config.animations.easing}`;
                newActive.style.opacity = '1';
                newActive.style.transform = 'translateX(0)';
                
                setTimeout(() => {
                    newActive.style.transition = '';
                    this.elements.container.classList.remove(this.config.classes.animating);
                }, this.config.animations.duration);
            }, currentActive ? this.config.animations.duration / 2 : 0);
        },
        
        /**
         * Initialize real-time statistics calculation
         */
        initializeStatistics: function() {
            // Create statistics elements if they don't exist
            this.createStatisticsElements();
            
            // Calculate initial statistics
            this.updateAllStatistics();
            
            // Set up observers for content changes
            this.observeContentChanges();
        },
        
        /**
         * Create statistics display elements
         */
        createStatisticsElements: function() {
            this.elements.resultItems.forEach(item => {
                const existingStats = item.querySelector('.biography-generator__stats');
                if (!existingStats) {
                    const statsHtml = `
                        <div class="biography-generator__stats">
                            <div class="biography-generator__stat-item">
                                <span class="biography-generator__stat-label">Words:</span>
                                <span class="biography-generator__word-count">0</span>
                            </div>
                            <div class="biography-generator__stat-item">
                                <span class="biography-generator__stat-label">Characters:</span>
                                <span class="biography-generator__char-count">0</span>
                            </div>
                            <div class="biography-generator__stat-item">
                                <span class="biography-generator__stat-label">Reading time:</span>
                                <span class="biography-generator__reading-time">0s</span>
                            </div>
                        </div>
                    `;
                    
                    const header = item.querySelector('.biography-generator__result-header');
                    if (header) {
                        header.insertAdjacentHTML('afterend', statsHtml);
                    }
                }
            });
        },
        
        /**
         * Calculate statistics for biography content
         */
        calculateStatistics: function(content, type) {
            const cacheKey = `${type}-${content.length}`;
            
            if (this.statsCache.has(cacheKey)) {
                return this.statsCache.get(cacheKey);
            }
            
            // Remove HTML tags for accurate counting
            const plainText = content.replace(/<[^>]*>/g, '').trim();
            
            // Word count
            const words = plainText.split(/\s+/).filter(word => word.length > 0);
            const wordCount = words.length;
            
            // Character count (with and without spaces)
            const charCount = plainText.length;
            const charCountNoSpaces = plainText.replace(/\s/g, '').length;
            
            // Reading time (average 200 words per minute)
            const readingTimeMinutes = Math.max(1, Math.ceil(wordCount / 200));
            
            const stats = {
                wordCount,
                charCount,
                charCountNoSpaces,
                readingTimeMinutes,
                readingTimeSeconds: readingTimeMinutes * 60
            };
            
            // Cache the result
            this.statsCache.set(cacheKey, stats);
            
            return stats;
        },
        
        /**
         * Update statistics display for specific type
         */
        updateStatistics: function(type) {
            const content = this.data.biographies[type] || '';
            const stats = this.calculateStatistics(content, type);
            
            const resultItem = document.getElementById(`biography-${type}-result`);
            if (!resultItem) return;
            
            const wordCountEl = resultItem.querySelector('.biography-generator__word-count');
            const charCountEl = resultItem.querySelector('.biography-generator__char-count');
            const readingTimeEl = resultItem.querySelector('.biography-generator__reading-time');
            
            if (wordCountEl) wordCountEl.textContent = stats.wordCount;
            if (charCountEl) charCountEl.textContent = stats.charCount;
            if (readingTimeEl) {
                if (stats.readingTimeMinutes >= 1) {
                    readingTimeEl.textContent = `${stats.readingTimeMinutes}m`;
                } else {
                    readingTimeEl.textContent = `${Math.max(1, Math.floor(stats.readingTimeSeconds))}s`;
                }
            }
        },
        
        /**
         * Update statistics for all biography types
         */
        updateAllStatistics: function() {
            ['short', 'medium', 'long'].forEach(type => {
                this.updateStatistics(type);
            });
        },
        
        /**
         * Initialize keyboard shortcuts
         */
        initializeKeyboardShortcuts: function() {
            if (!this.config.shortcuts.enabled) return;
            
            document.addEventListener('keydown', (e) => {
                this.handleKeyboardShortcut(e);
            });
            
            // Add keyboard shortcuts help
            this.createShortcutsHelp();
        },
        
        /**
         * Handle keyboard shortcuts
         */
        handleKeyboardShortcut: function(e) {
            // Don't trigger shortcuts when typing in inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
                // Allow Ctrl+Z and Ctrl+Y in editing mode
                if ((e.ctrlKey && (e.key === 'z' || e.key === 'y')) && this.data.isEditing) {
                    e.preventDefault();
                    if (e.key === 'z') {
                        this.undo();
                    } else if (e.key === 'y') {
                        this.redo();
                    }
                }
                return;
            }
            
            const shortcut = this.getShortcutString(e);
            const action = this.config.shortcuts.keymap[shortcut];
            
            if (action) {
                e.preventDefault();
                this.executeShortcutAction(action);
            }
        },
        
        /**
         * Get shortcut string from keyboard event
         */
        getShortcutString: function(e) {
            const parts = [];
            if (e.ctrlKey) parts.push('ctrl');
            if (e.altKey) parts.push('alt');
            if (e.shiftKey) parts.push('shift');
            parts.push(e.key.toLowerCase());
            return parts.join('+');
        },
        
        /**
         * Execute shortcut action
         */
        executeShortcutAction: function(action) {
            switch (action) {
                case 'switch-short':
                    this.setActiveTab('short');
                    break;
                case 'switch-medium':
                    this.setActiveTab('medium');
                    break;
                case 'switch-long':
                    this.setActiveTab('long');
                    break;
                case 'copy-current':
                    this.copyCurrentBiography();
                    break;
                case 'edit-current':
                    this.toggleEditCurrent();
                    break;
                case 'save-all':
                    this.saveBiographies();
                    break;
                case 'print-current':
                    this.printCurrentBiography();
                    break;
                case 'undo':
                    this.undo();
                    break;
                case 'redo':
                    this.redo();
                    break;
                case 'cancel-action':
                    this.cancelCurrentAction();
                    break;
            }
        },
        
        /**
         * Set active tab programmatically
         */
        setActiveTab: function(type) {
            const tab = document.querySelector(`[data-tab="${type}"]`);
            if (tab) {
                this.switchTabEnhanced(tab);
            }
        },
        
        /**
         * Initialize editing features
         */
        initializeEditingFeatures: function() {
            // Add edit buttons to result actions if they don't exist
            this.addEditButtons();
            
            // Initialize edit history
            this.saveEditState();
        },
        
        /**
         * Add edit buttons to biography actions
         */
        addEditButtons: function() {
            const actionGroups = document.querySelectorAll('.biography-generator__result-actions');
            
            actionGroups.forEach((group, index) => {
                const types = ['short', 'medium', 'long'];
                const type = types[index];
                
                if (!group.querySelector(`#edit-${type}-bio`)) {
                    const editButton = document.createElement('button');
                    editButton.type = 'button';
                    editButton.className = 'biography-generator__action-button';
                    editButton.id = `edit-${type}-bio`;
                    editButton.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        Edit Biography
                    `;
                    
                    // Insert before the last button
                    const lastButton = group.lastElementChild;
                    group.insertBefore(editButton, lastButton);
                    
                    // Add event listener
                    editButton.addEventListener('click', () => this.toggleEdit(editButton));
                }
            });
        },
        
        /**
         * Toggle edit mode for biography
         */
        toggleEdit: function(button) {
            const bioType = button.id.replace('edit-', '').replace('-bio', '');
            const resultItem = document.getElementById(`biography-${bioType}-result`);
            const contentElement = document.getElementById(`biography-${bioType}-content`);
            
            if (!resultItem || !contentElement) return;
            
            const isEditing = resultItem.classList.contains(this.config.classes.editing);
            
            if (isEditing) {
                this.exitEditMode(bioType, button, resultItem, contentElement);
            } else {
                this.enterEditMode(bioType, button, resultItem, contentElement);
            }
        },
        
        /**
         * Enter edit mode for biography
         */
        enterEditMode: function(bioType, button, resultItem, contentElement) {
            // Save current state
            this.saveEditState();
            
            // Set editing flag
            this.data.isEditing = true;
            
            // Add editing class
            resultItem.classList.add(this.config.classes.editing);
            
            // Make content editable
            contentElement.contentEditable = true;
            contentElement.classList.add('biography-generator__editable-content');
            
            // Update button
            button.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                Save Changes
            `;
            
            // Focus content
            contentElement.focus();
            
            // Add editing controls
            this.addEditingControls(resultItem, bioType);
            
            // Show keyboard shortcuts hint
            this.showEditingHint();
        },
        
        /**
         * Exit edit mode for biography
         */
        exitEditMode: function(bioType, button, resultItem, contentElement) {
            // Get edited content
            const newContent = contentElement.textContent || contentElement.innerText || '';
            
            // Update data
            this.data.biographies[bioType] = newContent;
            
            // Update display
            contentElement.innerHTML = newContent.replace(/\n/g, '<br>');
            
            // Remove editing
            contentElement.contentEditable = false;
            contentElement.classList.remove('biography-generator__editable-content');
            resultItem.classList.remove(this.config.classes.editing);
            
            // Update button
            button.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
                Edit Biography
            `;
            
            // Remove editing controls
            this.removeEditingControls(resultItem);
            
            // Update statistics
            this.updateStatistics(bioType);
            
            // Save state
            this.saveEditState();
            
            // Clear editing flag
            this.data.isEditing = false;
            
            // Auto-save if enabled
            if (this.config.autoSave.enabled) {
                this.scheduleAutoSave();
            }
            
            // Show save notification
            this.showNotification('Biography updated successfully!', 'success');
        },
        
        /**
         * Add editing controls to result item
         */
        addEditingControls: function(resultItem, bioType) {
            const existingControls = resultItem.querySelector('.biography-generator__editing-controls');
            if (existingControls) return;
            
            const controls = document.createElement('div');
            controls.className = 'biography-generator__editing-controls';
            controls.innerHTML = `
                <div class="biography-generator__editing-toolbar">
                    <button type="button" class="biography-generator__edit-action" data-action="undo" title="Undo (Ctrl+Z)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/>
                        </svg>
                    </button>
                    <button type="button" class="biography-generator__edit-action" data-action="redo" title="Redo (Ctrl+Y)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/>
                        </svg>
                    </button>
                    <div class="biography-generator__edit-separator"></div>
                    <span class="biography-generator__edit-hint">Press Ctrl+Z to undo, Ctrl+Y to redo, Esc to cancel</span>
                </div>
            `;
            
            const header = resultItem.querySelector('.biography-generator__result-header');
            if (header) {
                header.insertAdjacentElement('afterend', controls);
            }
            
            // Add event listeners
            controls.querySelector('[data-action="undo"]').addEventListener('click', () => this.undo());
            controls.querySelector('[data-action="redo"]').addEventListener('click', () => this.redo());
        },
        
        /**
         * Remove editing controls
         */
        removeEditingControls: function(resultItem) {
            const controls = resultItem.querySelector('.biography-generator__editing-controls');
            if (controls) {
                controls.remove();
            }
        },
        
        /**
         * Initialize comparison features
         */
        initializeComparisonFeatures: function() {
            this.addComparisonButton();
        },
        
        /**
         * Add comparison button to the interface
         */
        addComparisonButton: function() {
            const modificationControls = document.querySelector('.biography-generator__modification-controls');
            if (modificationControls && !document.getElementById('biography-compare-versions')) {
                const compareButton = document.createElement('button');
                compareButton.type = 'button';
                compareButton.id = 'biography-compare-versions';
                compareButton.className = 'generator__button generator__button--outline';
                compareButton.style.marginTop = '20px';
                compareButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                    Compare Versions
                `;
                
                modificationControls.appendChild(compareButton);
                compareButton.addEventListener('click', () => this.toggleComparison());
            }
        },
        
        /**
         * Toggle comparison view
         */
        toggleComparison: function() {
            const isComparing = this.data.isComparing;
            
            if (isComparing) {
                this.exitComparisonMode();
            } else {
                this.enterComparisonMode();
            }
        },
        
        /**
         * Enter comparison mode
         */
        enterComparisonMode: function() {
            this.data.isComparing = true;
            this.elements.container.classList.add(this.config.classes.comparing);
            
            // Create comparison view
            this.createComparisonView();
            
            // Update button text
            const compareButton = document.getElementById('biography-compare-versions');
            if (compareButton) {
                compareButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                    Exit Comparison
                `;
            }
        },
        
        /**
         * Exit comparison mode
         */
        exitComparisonMode: function() {
            this.data.isComparing = false;
            this.elements.container.classList.remove(this.config.classes.comparing);
            
            // Remove comparison view
            this.removeComparisonView();
            
            // Update button text
            const compareButton = document.getElementById('biography-compare-versions');
            if (compareButton) {
                compareButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                    Compare Versions
                `;
            }
        },
        
        /**
         * Create comparison view
         */
        createComparisonView: function() {
            const resultsContainer = document.querySelector('.biography-generator__results-container');
            if (!resultsContainer) return;
            
            const comparisonView = document.createElement('div');
            comparisonView.className = 'biography-generator__comparison-view';
            comparisonView.innerHTML = `
                <div class="biography-generator__comparison-header">
                    <h3>Biography Comparison</h3>
                    <p>Compare all three versions side by side to understand the differences and choose the best fit for your needs.</p>
                </div>
                <div class="biography-generator__comparison-grid">
                    <div class="biography-generator__comparison-column">
                        <h4>Short Biography</h4>
                        <div class="biography-generator__comparison-stats">
                            ${this.generateComparisonStats('short')}
                        </div>
                        <div class="biography-generator__comparison-content">
                            ${this.data.biographies.short.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    <div class="biography-generator__comparison-column">
                        <h4>Medium Biography</h4>
                        <div class="biography-generator__comparison-stats">
                            ${this.generateComparisonStats('medium')}
                        </div>
                        <div class="biography-generator__comparison-content">
                            ${this.data.biographies.medium.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    <div class="biography-generator__comparison-column">
                        <h4>Long Biography</h4>
                        <div class="biography-generator__comparison-stats">
                            ${this.generateComparisonStats('long')}
                        </div>
                        <div class="biography-generator__comparison-content">
                            ${this.data.biographies.long.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
            `;
            
            resultsContainer.appendChild(comparisonView);
        },
        
        /**
         * Generate comparison statistics HTML
         */
        generateComparisonStats: function(type) {
            const stats = this.calculateStatistics(this.data.biographies[type], type);
            return `
                <div class="biography-generator__comparison-stat">
                    <span class="label">Words:</span>
                    <span class="value">${stats.wordCount}</span>
                </div>
                <div class="biography-generator__comparison-stat">
                    <span class="label">Characters:</span>
                    <span class="value">${stats.charCount}</span>
                </div>
                <div class="biography-generator__comparison-stat">
                    <span class="label">Reading time:</span>
                    <span class="value">${stats.readingTimeMinutes >= 1 ? stats.readingTimeMinutes + 'm' : Math.max(1, Math.floor(stats.readingTimeSeconds)) + 's'}</span>
                </div>
            `;
        },
        
        /**
         * Remove comparison view
         */
        removeComparisonView: function() {
            const comparisonView = document.querySelector('.biography-generator__comparison-view');
            if (comparisonView) {
                comparisonView.remove();
            }
        },
        
        /**
         * Initialize auto-save functionality
         */
        initializeAutoSave: function() {
            if (!this.config.autoSave.enabled) return;
            
            // Set up periodic auto-save
            setInterval(() => {
                if (this.hasUnsavedChanges()) {
                    this.autoSave();
                }
            }, this.config.autoSave.delay);
        },
        
        /**
         * Schedule auto-save
         */
        scheduleAutoSave: function() {
            if (this.data.autoSaveTimer) {
                clearTimeout(this.data.autoSaveTimer);
            }
            
            this.data.autoSaveTimer = setTimeout(() => {
                this.autoSave();
            }, this.config.autoSave.delay);
        },
        
        /**
         * Perform auto-save
         */
        autoSave: function() {
            if (!this.hasUnsavedChanges()) return;
            
            this.saveBiographies(true); // Silent save
            this.data.lastSaved = Date.now();
        },
        
        /**
         * Check if there are unsaved changes
         */
        hasUnsavedChanges: function() {
            return JSON.stringify(this.data.biographies) !== JSON.stringify(this.data.originalBiographies);
        },
        
        /**
         * Initialize animations and transitions
         */
        initializeAnimations: function() {
            // Add CSS custom properties for animations
            document.documentElement.style.setProperty('--mkcg-animation-duration', this.config.animations.duration + 'ms');
            document.documentElement.style.setProperty('--mkcg-animation-easing', this.config.animations.easing);
        },
        
        /**
         * Initialize print features
         */
        initializePrintFeatures: function() {
            this.addPrintButtons();
            this.createPrintStyles();
        },
        
        /**
         * Add print buttons to biography actions
         */
        addPrintButtons: function() {
            const actionGroups = document.querySelectorAll('.biography-generator__result-actions');
            
            actionGroups.forEach((group, index) => {
                const types = ['short', 'medium', 'long'];
                const type = types[index];
                
                if (!group.querySelector(`#print-${type}-bio`)) {
                    const printButton = document.createElement('button');
                    printButton.type = 'button';
                    printButton.className = 'biography-generator__action-button';
                    printButton.id = `print-${type}-bio`;
                    printButton.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                        </svg>
                        Print Biography
                    `;
                    
                    group.appendChild(printButton);
                    printButton.addEventListener('click', () => this.printBiography(printButton));
                }
            });
        },
        
        /**
         * Print specific biography
         */
        printBiography: function(button) {
            const bioType = button.id.replace('print-', '').replace('-bio', '');
            const content = this.data.biographies[bioType];
            const stats = this.calculateStatistics(content, bioType);
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Professional Biography - ${bioType.charAt(0).toUpperCase() + bioType.slice(1)} Version</title>
                    <style>
                        body {
                            font-family: Georgia, serif;
                            line-height: 1.6;
                            max-width: 800px;
                            margin: 0 auto;
                            padding: 40px 20px;
                            color: #333;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 40px;
                            border-bottom: 2px solid #333;
                            padding-bottom: 20px;
                        }
                        .title {
                            font-size: 24px;
                            font-weight: bold;
                            margin-bottom: 10px;
                        }
                        .subtitle {
                            font-size: 14px;
                            color: #666;
                            text-transform: uppercase;
                            letter-spacing: 1px;
                        }
                        .content {
                            font-size: 16px;
                            line-height: 1.8;
                            margin-bottom: 40px;
                        }
                        .stats {
                            display: flex;
                            justify-content: space-around;
                            font-size: 12px;
                            color: #666;
                            border-top: 1px solid #eee;
                            padding-top: 20px;
                        }
                        @media print {
                            body { margin: 0; padding: 20px; }
                            .header { break-after: avoid; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="title">${this.data.personalInfo.name || 'Professional Biography'}</div>
                        <div class="subtitle">${bioType.charAt(0).toUpperCase() + bioType.slice(1)} Version</div>
                    </div>
                    <div class="content">
                        ${content.replace(/\n/g, '<br><br>')}
                    </div>
                    <div class="stats">
                        <div>Words: ${stats.wordCount}</div>
                        <div>Characters: ${stats.charCount}</div>
                        <div>Reading time: ${stats.readingTimeMinutes >= 1 ? stats.readingTimeMinutes + ' minutes' : Math.max(1, Math.floor(stats.readingTimeSeconds)) + ' seconds'}</div>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        },
        
        /**
         * Create print styles
         */
        createPrintStyles: function() {
            const printStyles = document.createElement('style');
            printStyles.type = 'text/css';
            printStyles.innerHTML = `
                @media print {
                    .biography-generator__result-actions,
                    .biography-generator__modification-controls,
                    .biography-generator__save-section,
                    .generator__panel--right {
                        display: none !important;
                    }
                    
                    .biography-generator__result-content {
                        font-size: 14px !important;
                        line-height: 1.6 !important;
                    }
                    
                    .biography-generator__results-tabs {
                        display: none !important;
                    }
                    
                    .biography-generator__result-item {
                        display: block !important;
                        page-break-inside: avoid;
                        margin-bottom: 40px;
                    }
                }
            `;
            document.head.appendChild(printStyles);
        },
        
        /**
         * Initialize advanced sharing features
         */
        initializeAdvancedSharing: function() {
            this.addShareButtons();
        },
        
        /**
         * Add share buttons to biography actions
         */
        addShareButtons: function() {
            const actionGroups = document.querySelectorAll('.biography-generator__result-actions');
            
            actionGroups.forEach((group, index) => {
                const types = ['short', 'medium', 'long'];
                const type = types[index];
                
                if (!group.querySelector(`#share-${type}-bio`)) {
                    const shareButton = document.createElement('button');
                    shareButton.type = 'button';
                    shareButton.className = 'biography-generator__action-button';
                    shareButton.id = `share-${type}-bio`;
                    shareButton.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
                        </svg>
                        Share Biography
                    `;
                    
                    group.appendChild(shareButton);
                    shareButton.addEventListener('click', () => this.openShareModal(shareButton));
                }
            });
        },
        
        /**
         * Open share modal for biography
         */
        openShareModal: function(button) {
            const bioType = button.id.replace('share-', '').replace('-bio', '');
            const content = this.data.biographies[bioType];
            
            this.createShareModal(bioType, content);
        },
        
        /**
         * Create share modal
         */
        createShareModal: function(type, content) {
            // Remove existing modal
            const existingModal = document.querySelector('.biography-generator__share-modal');
            if (existingModal) {
                existingModal.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = 'biography-generator__share-modal';
            modal.innerHTML = `
                <div class="biography-generator__share-backdrop"></div>
                <div class="biography-generator__share-content">
                    <div class="biography-generator__share-header">
                        <h3>Share ${type.charAt(0).toUpperCase() + type.slice(1)} Biography</h3>
                        <button type="button" class="biography-generator__share-close">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="biography-generator__share-options">
                        <button type="button" class="biography-generator__share-option" data-action="copy-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/>
                            </svg>
                            Copy Shareable Link
                        </button>
                        <button type="button" class="biography-generator__share-option" data-action="share-linkedin">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            Share on LinkedIn
                        </button>
                        <button type="button" class="biography-generator__share-option" data-action="share-twitter">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Share on Twitter
                        </button>
                        <button type="button" class="biography-generator__share-option" data-action="copy-text">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                            </svg>
                            Copy Biography Text
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Add event listeners
            modal.querySelector('.biography-generator__share-close').addEventListener('click', () => {
                this.closeShareModal();
            });
            
            modal.querySelector('.biography-generator__share-backdrop').addEventListener('click', () => {
                this.closeShareModal();
            });
            
            modal.querySelectorAll('.biography-generator__share-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    this.executeShareAction(e.target.closest('[data-action]').dataset.action, type, content);
                });
            });
            
            // Show modal with animation
            requestAnimationFrame(() => {
                modal.classList.add('biography-generator__share-modal--show');
            });
        },
        
        /**
         * Execute share action
         */
        executeShareAction: function(action, type, content) {
            switch (action) {
                case 'copy-link':
                    this.copyShareableLink(type);
                    break;
                case 'share-linkedin':
                    this.shareOnLinkedIn(content);
                    break;
                case 'share-twitter':
                    this.shareOnTwitter(content);
                    break;
                case 'copy-text':
                    this.copyBiographyText(content);
                    break;
            }
            
            this.closeShareModal();
        },
        
        /**
         * Copy shareable link
         */
        copyShareableLink: function(type) {
            const url = new URL(window.location);
            url.searchParams.set('biography_type', type);
            
            navigator.clipboard.writeText(url.toString()).then(() => {
                this.showNotification('Shareable link copied to clipboard!', 'success');
            }).catch(() => {
                this.showNotification('Failed to copy link', 'error');
            });
        },
        
        /**
         * Share on LinkedIn
         */
        shareOnLinkedIn: function(content) {
            const text = `Check out my professional biography:\n\n${content}`;
            const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}&summary=${encodeURIComponent(text)}`;
            window.open(url, '_blank', 'width=600,height=400');
        },
        
        /**
         * Share on Twitter
         */
        shareOnTwitter: function(content) {
            const text = content.length > 240 ? content.substring(0, 237) + '...' : content;
            const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank', 'width=600,height=400');
        },
        
        /**
         * Copy biography text
         */
        copyBiographyText: function(content) {
            navigator.clipboard.writeText(content).then(() => {
                this.showNotification('Biography text copied to clipboard!', 'success');
            }).catch(() => {
                this.showNotification('Failed to copy text', 'error');
            });
        },
        
        /**
         * Close share modal
         */
        closeShareModal: function() {
            const modal = document.querySelector('.biography-generator__share-modal');
            if (modal) {
                modal.classList.remove('biography-generator__share-modal--show');
                setTimeout(() => {
                    modal.remove();
                }, this.config.animations.duration);
            }
        },
        
        /**
         * Initialize advanced download features
         */
        initializeAdvancedDownloads: function() {
            this.addAdvancedDownloadOptions();
        },
        
        /**
         * Add advanced download options
         */
        addAdvancedDownloadOptions: function() {
            // This would typically involve creating PDF and Word document downloads
            // For now, we'll enhance the existing text download functionality
            
            // Add context menu for download buttons
            this.elements.downloadButtons.forEach(button => {
                button.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    this.showDownloadContextMenu(e, button);
                });
            });
        },
        
        /**
         * Show download context menu
         */
        showDownloadContextMenu: function(event, button) {
            const bioType = button.id.replace('download-', '').replace('-bio', '');
            
            // Create context menu
            const menu = document.createElement('div');
            menu.className = 'biography-generator__context-menu';
            menu.innerHTML = `
                <div class="biography-generator__context-option" data-format="txt">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    Download as Text (.txt)
                </div>
                <div class="biography-generator__context-option" data-format="html">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,17.56L16.07,16.43L16.62,10.33H9.38L9.2,8.3H16.8L17,6.31H7L7.56,12.32H14.45L14.22,14.9L12,15.5L9.78,14.9L9.64,13.24H7.64L7.93,16.43L12,17.56M4.07,3H19.93L18.5,19.2L12,21L5.5,19.2L4.07,3Z"/>
                    </svg>
                    Download as HTML (.html)
                </div>
                <div class="biography-generator__context-option" data-format="pdf">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    Download as PDF (.pdf)
                </div>
            `;
            
            // Position menu
            menu.style.position = 'fixed';
            menu.style.left = event.clientX + 'px';
            menu.style.top = event.clientY + 'px';
            menu.style.zIndex = '9999';
            
            document.body.appendChild(menu);
            
            // Add event listeners
            menu.querySelectorAll('.biography-generator__context-option').forEach(option => {
                option.addEventListener('click', () => {
                    this.downloadInFormat(bioType, option.dataset.format);
                    menu.remove();
                });
            });
            
            // Remove menu when clicking outside
            setTimeout(() => {
                document.addEventListener('click', function removeMenu() {
                    menu.remove();
                    document.removeEventListener('click', removeMenu);
                });
            }, 100);
        },
        
        /**
         * Download biography in specific format
         */
        downloadInFormat: function(type, format) {
            const content = this.data.biographies[type];
            const name = this.data.personalInfo.name || 'Professional';
            const fileName = `${name.replace(/\s+/g, '_')}_${type}_biography`;
            
            switch (format) {
                case 'txt':
                    this.downloadAsTextFile(content, fileName + '.txt');
                    break;
                case 'html':
                    this.downloadAsHTML(content, fileName + '.html', type);
                    break;
                case 'pdf':
                    this.downloadAsPDF(content, fileName + '.pdf', type);
                    break;
            }
        },
        
        /**
         * Download as text file
         */
        downloadAsTextFile: function(content, fileName) {
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
            element.setAttribute('download', fileName);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        },
        
        /**
         * Download as HTML file
         */
        downloadAsHTML: function(content, fileName, type) {
            const stats = this.calculateStatistics(content, type);
            const html = `
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Professional Biography - ${this.data.personalInfo.name}</title>
                    <style>
                        body { font-family: Georgia, serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 40px 20px; }
                        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        .content { font-size: 16px; line-height: 1.8; }
                        .stats { margin-top: 40px; font-size: 14px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${this.data.personalInfo.name}</h1>
                        <p>${type.charAt(0).toUpperCase() + type.slice(1)} Professional Biography</p>
                    </div>
                    <div class="content">
                        ${content.replace(/\n/g, '<br><br>')}
                    </div>
                    <div class="stats">
                        <p><strong>Statistics:</strong> ${stats.wordCount} words, ${stats.charCount} characters, ${stats.readingTimeMinutes >= 1 ? stats.readingTimeMinutes + ' minutes' : Math.max(1, Math.floor(stats.readingTimeSeconds)) + ' seconds'} reading time</p>
                    </div>
                </body>
                </html>
            `;
            
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/html;charset=utf-8,' + encodeURIComponent(html));
            element.setAttribute('download', fileName);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        },
        
        /**
         * Download as PDF (simplified - would typically use a PDF library)
         */
        downloadAsPDF: function(content, fileName, type) {
            // This is a simplified implementation
            // In a production environment, you would use a library like jsPDF
            this.showNotification('PDF download feature requires additional library. Using print dialog instead.', 'info');
            this.printBiography({ id: `print-${type}-bio` });
        },
        
        /**
         * Show notification message
         */
        showNotification: function(message, type = 'info') {
            // Check if notification system is available
            if (window.SimpleNotifications) {
                window.SimpleNotifications.show(message, type);
                return;
            }
            
            // Fallback notification
            const notification = document.createElement('div');
            notification.className = `biography-generator__notification biography-generator__notification--${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 4px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                opacity: 0;
                transform: translateY(-10px);
                transition: opacity 0.3s ease, transform 0.3s ease;
            `;
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.style.backgroundColor = '#28a745';
                    break;
                case 'error':
                    notification.style.backgroundColor = '#dc3545';
                    break;
                case 'warning':
                    notification.style.backgroundColor = '#ffc107';
                    notification.style.color = '#212529';
                    break;
                default:
                    notification.style.backgroundColor = '#17a2b8';
            }
            
            document.body.appendChild(notification);
            
            // Show notification
            requestAnimationFrame(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
            });
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        },
        
        /**
         * Save current edit state for undo/redo
         */
        saveEditState: function() {
            const state = {
                timestamp: Date.now(),
                biographies: JSON.parse(JSON.stringify(this.data.biographies))
            };
            
            // Remove future states if we're in the middle of history
            if (this.data.editHistoryIndex < this.data.editHistory.length - 1) {
                this.data.editHistory = this.data.editHistory.slice(0, this.data.editHistoryIndex + 1);
            }
            
            this.data.editHistory.push(state);
            this.data.editHistoryIndex = this.data.editHistory.length - 1;
            
            // Limit history size
            if (this.data.editHistory.length > 50) {
                this.data.editHistory = this.data.editHistory.slice(-50);
                this.data.editHistoryIndex = this.data.editHistory.length - 1;
            }
        },
        
        /**
         * Undo last edit
         */
        undo: function() {
            if (this.data.editHistoryIndex > 0) {
                this.data.editHistoryIndex--;
                const state = this.data.editHistory[this.data.editHistoryIndex];
                this.restoreEditState(state);
                this.showNotification('Undid last change', 'info');
            }
        },
        
        /**
         * Redo last undone edit
         */
        redo: function() {
            if (this.data.editHistoryIndex < this.data.editHistory.length - 1) {
                this.data.editHistoryIndex++;
                const state = this.data.editHistory[this.data.editHistoryIndex];
                this.restoreEditState(state);
                this.showNotification('Redid last change', 'info');
            }
        },
        
        /**
         * Restore edit state
         */
        restoreEditState: function(state) {
            this.data.biographies = JSON.parse(JSON.stringify(state.biographies));
            
            // Update display
            ['short', 'medium', 'long'].forEach(type => {
                const contentElement = document.getElementById(`biography-${type}-content`);
                if (contentElement) {
                    contentElement.innerHTML = this.data.biographies[type].replace(/\n/g, '<br>');
                }
                this.updateStatistics(type);
            });
        },
        
        /**
         * Handle content editing
         */
        handleContentEdit: function(event) {
            const element = event.target;
            const bioType = element.id.replace('biography-', '').replace('-content', '');
            const newContent = element.textContent || element.innerText || '';
            
            // Update data
            this.data.biographies[bioType] = newContent;
            
            // Update statistics
            this.updateStatistics(bioType);
            
            // Schedule auto-save
            if (this.config.autoSave.enabled) {
                this.scheduleAutoSave();
            }
        },
        
        /**
         * Handle edit blur (when user stops editing)
         */
        handleEditBlur: function(event) {
            // Save edit state for undo/redo
            this.saveEditState();
        },
        
        /**
         * Observe content changes for statistics updates
         */
        observeContentChanges: function() {
            if (!window.MutationObserver) return;
            
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        const target = mutation.target.closest('[id$="-content"]');
                        if (target) {
                            const bioType = target.id.replace('biography-', '').replace('-content', '');
                            this.updateStatistics(bioType);
                        }
                    }
                });
            });
            
            // Observe all content elements
            this.elements.resultContent.forEach(element => {
                observer.observe(element, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            });
        },
        
        /**
         * Create keyboard shortcuts help
         */
        createShortcutsHelp: function() {
            // Add keyboard shortcuts indicator
            const helpButton = document.createElement('button');
            helpButton.type = 'button';
            helpButton.className = 'biography-generator__help-button';
            helpButton.title = 'Keyboard Shortcuts (?)';
            helpButton.innerHTML = '?';
            helpButton.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #007cba;
                color: white;
                border: none;
                font-size: 18px;
                font-weight: bold;
                cursor: pointer;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            `;
            
            document.body.appendChild(helpButton);
            
            helpButton.addEventListener('click', () => {
                this.showShortcutsModal();
            });
        },
        
        /**
         * Show keyboard shortcuts modal
         */
        showShortcutsModal: function() {
            const modal = document.createElement('div');
            modal.className = 'biography-generator__shortcuts-modal';
            modal.innerHTML = `
                <div class="biography-generator__shortcuts-backdrop"></div>
                <div class="biography-generator__shortcuts-content">
                    <div class="biography-generator__shortcuts-header">
                        <h3>Keyboard Shortcuts</h3>
                        <button type="button" class="biography-generator__shortcuts-close">×</button>
                    </div>
                    <div class="biography-generator__shortcuts-list">
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + 1</kbd>
                            <span>Switch to Short Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + 2</kbd>
                            <span>Switch to Medium Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + 3</kbd>
                            <span>Switch to Long Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + C</kbd>
                            <span>Copy Current Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + E</kbd>
                            <span>Edit Current Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + S</kbd>
                            <span>Save All Biographies</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + P</kbd>
                            <span>Print Current Biography</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + Z</kbd>
                            <span>Undo Last Change</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Ctrl + Y</kbd>
                            <span>Redo Last Change</span>
                        </div>
                        <div class="biography-generator__shortcut-item">
                            <kbd>Esc</kbd>
                            <span>Cancel Current Action</span>
                        </div>
                    </div>
                </div>
            `;
            
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10001;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            document.body.appendChild(modal);
            
            // Add event listeners
            modal.querySelector('.biography-generator__shortcuts-close').addEventListener('click', () => {
                this.closeShortcutsModal();
            });
            
            modal.querySelector('.biography-generator__shortcuts-backdrop').addEventListener('click', () => {
                this.closeShortcutsModal();
            });
            
            // Show modal
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
            });
        },
        
        /**
         * Close shortcuts modal
         */
        closeShortcutsModal: function() {
            const modal = document.querySelector('.biography-generator__shortcuts-modal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        },
        
        /**
         * Show editing hint
         */
        showEditingHint: function() {
            this.showNotification('Editing mode active. Use Ctrl+Z to undo, Ctrl+Y to redo, Esc to cancel.', 'info');
        },
        
        /**
         * Save tab preference
         */
        saveTabPreference: function(tabType) {
            try {
                localStorage.setItem('biography-generator-active-tab', tabType);
            } catch (e) {
                // localStorage not available, ignore
            }
        },
        
        /**
         * Utility functions for shortcuts
         */
        copyCurrentBiography: function() {
            const button = document.querySelector(`#copy-${this.data.currentTab}-bio`);
            if (button) {
                this.copyToClipboard(button);
            }
        },
        
        toggleEditCurrent: function() {
            const button = document.querySelector(`#edit-${this.data.currentTab}-bio`);
            if (button) {
                this.toggleEdit(button);
            }
        },
        
        printCurrentBiography: function() {
            const button = document.querySelector(`#print-${this.data.currentTab}-bio`);
            if (button) {
                this.printBiography(button);
            }
        },
        
        cancelCurrentAction: function() {
            if (this.data.isEditing) {
                // Cancel editing
                const editButton = document.querySelector(`#edit-${this.data.currentTab}-bio`);
                if (editButton) {
                    this.toggleEdit(editButton);
                }
            } else if (this.data.isComparing) {
                // Exit comparison mode
                this.exitComparisonMode();
            } else {
                // Close any open modals
                this.closeShareModal();
                this.closeShortcutsModal();
            }
        },
        
        // Legacy methods for backward compatibility
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
        
        updateTone: function() {
            // Get selected tone
            const selectedToneEl = document.querySelector('input[name="biography-tone"]:checked');
            if (!selectedToneEl) return;
            
            const selectedTone = selectedToneEl.value;
            
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
                        ['short', 'medium', 'long'].forEach(type => {
                            if (biographies[type]) {
                                const contentElement = document.getElementById(`biography-${type}-content`);
                                if (contentElement) {
                                    contentElement.innerHTML = biographies[type].replace(/\n/g, '<br>');
                                }
                            }
                        });
                        
                        // Update statistics
                        this.updateAllStatistics();
                        
                        // Save edit state
                        this.saveEditState();
                        
                        // Display success message
                        this.showNotification('Biography tone updated successfully!', 'success');
                    }
                } else {
                    console.error('Error updating tone:', response);
                    this.showNotification('Failed to update biography tone. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('AJAX request failed:', error);
                
                // Remove loading state
                if (this.elements.updateToneButton) {
                    this.elements.updateToneButton.classList.remove(this.config.classes.loading);
                    this.elements.updateToneButton.removeAttribute('disabled');
                }
                
                this.showNotification('Failed to update biography tone. Please check your internet connection and try again.', 'error');
            });
        },
        
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
                
                this.showNotification('Biography copied to clipboard!', 'success');
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                this.showNotification('Failed to copy to clipboard. Please try again.', 'error');
            });
        },
        
        downloadAsText: function(button) {
            // Get the biography type from the button ID
            const bioType = button.id.replace('download-', '').replace('-bio', '');
            
            // Get the biography text
            const bioText = this.data.biographies[bioType];
            
            // Get personal info
            const name = this.data.personalInfo.name || 'Professional';
            
            // Create file name
            const fileName = `${name.replace(/\s+/g, '_')}_${bioType}_biography.txt`;
            
            this.downloadAsTextFile(bioText, fileName);
            this.showNotification('Biography download started!', 'success');
        },
        
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
        
        saveBiographies: function(silent = false) {
            // Get post ID and entry ID
            const postId = this.elements.postIdField ? parseInt(this.elements.postIdField.value) : this.data.postId;
            const entryId = this.elements.entryIdField ? parseInt(this.elements.entryIdField.value) : this.data.entryId;
            const nonce = this.elements.nonceField ? this.elements.nonceField.value : this.data.nonce;
            
            if (!silent) {
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
            }
            
            // Prepare AJAX request
            const data = new FormData();
            data.append('action', this.config.endpoints.saveBiography);
            data.append('post_id', postId);
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
                if (!silent) {
                    // Remove loading state
                    if (this.elements.saveButton) {
                        this.elements.saveButton.classList.remove(this.config.classes.loading);
                        this.elements.saveButton.removeAttribute('disabled');
                    }
                }
                
                if (response.success) {
                    // Update original biographies
                    this.data.originalBiographies = JSON.parse(JSON.stringify(this.data.biographies));
                    
                    if (!silent) {
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
                        this.showNotification('Auto-saved biographies', 'success');
                    }
                } else {
                    console.error('Error saving biographies:', response);
                    
                    if (!silent) {
                        // Update save status
                        if (this.elements.saveStatus) {
                            this.elements.saveStatus.style.background = '#f8d7da';
                            this.elements.saveStatus.style.borderColor = '#f5c6cb';
                        }
                        
                        if (this.elements.saveStatusText) {
                            this.elements.saveStatusText.textContent = 'Failed to save biographies. Please try again.';
                        }
                    } else {
                        this.showNotification('Auto-save failed', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('AJAX request failed:', error);
                
                if (!silent) {
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
                } else {
                    this.showNotification('Auto-save failed - connection error', 'error');
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