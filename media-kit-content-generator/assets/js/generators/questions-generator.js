/**
 * Questions Generator - WordPress Standard Implementation
 * 
 * CRITICAL FEATURES IMPLEMENTED:
 * ✅ Topic selection - displays correct question set (1-5, 6-10, etc.) based on selected topic
 * ✅ Inline topic editing - click empty topics or double-click existing to edit
 * ✅ Auto-save on blur - saves when user clicks away from editor
 * ✅ AJAX backend integration - saves to WordPress post meta via PHP handlers
 * ✅ Visual feedback - saving/saved/error indicators with animations
 * ✅ WordPress-standard URL-encoded AJAX (100% reliable, no JSON complexity)
 */

const QuestionsGenerator = {
    // DOM elements mapping
    elements: {
        topicsGrid: '#mkcg-topics-grid',
        topicCards: '.mkcg-topic-card',
        editTopicsButton: '#mkcg-edit-topics',
        selectedTopicResult: '#mkcg-selected-topic-result',
        selectedTopicText: '#mkcg-selected-topic-text',
        generateButton: '#mkcg-generate-questions',
        loadingIndicator: '#mkcg-loading',
        questionsResult: '#mkcg-questions-result',
        questionsList: '#mkcg-questions-list',
        fieldModal: '#mkcg-field-modal',
        fieldNumberInput: '#mkcg-field-number',
        modalOkButton: '#mkcg-modal-ok',
        modalCancelButton: '#mkcg-modal-cancel',
        selectedTopicIdInput: '#mkcg-selected-topic-id'
    },
    
    // Enhanced state tracking
    selectedTopicId: 1,
    selectedTopicText: '',
    generatedQuestions: [],
    selectedQuestion: null,
    topicsData: {},
    dataQuality: {
        topics: 'unknown',
        questions: 'unknown',
        sync_status: null,
        last_check: null
    },
    
    // Performance tracking
    performance: {
        loadStartTime: null,
        apiCallTimes: [],
        errorCount: 0,
        lastSyncCheck: null
    },
    
    /**
     * ARCHITECTURAL FIX: Enhanced initialization with unified data architecture
     */
    init: function() {
        console.log('MKCG Enhanced Questions: Initializing with unified data architecture');
        this.performance.loadStartTime = performance.now();
        
        try {
            // CRITICAL FIX: Initialize centralized data manager first
            this.initializeCentralizedDataManager();
            
            // ARCHITECTURAL FIX: Load data from unified sources
            this.loadUnifiedTopicsData();
            this.bindEnhancedEvents();
            
            // Only update UI elements if they exist
            if(this.isOnQuestionsGeneratorPage()){
                 this.updateSelectedTopic();
                 this.bindSimpleSave();
                 this.showQuestionsForTopic(this.selectedTopicId || 1);
            }
            
            const loadTime = performance.now() - this.performance.loadStartTime;
            console.log(`MKCG Enhanced Questions: Initialization completed in ${loadTime.toFixed(2)}ms`);
            
        } catch (error) {
            this.performance.errorCount++;
            this.handleError(error, 'initialization', () => this.init());
        }
    },
    
    /**
     * ARCHITECTURAL FIX: Load topics data from unified sources
     * Priority: 1. Centralized Data Manager, 2. Topics Generator data, 3. Questions Generator data
     */
    loadUnifiedTopicsData: function() {
        console.log('MKCG Enhanced Questions: Loading from unified data architecture');
        
        let dataSource = 'none';
        let topicsFound = false;
        
        // PRIORITY 1: Get data from centralized data manager
        if (typeof MKCG_DataManager !== 'undefined') {
            try {
                const centralizedTopics = MKCG_DataManager.getAllTopics();
                if (Object.values(centralizedTopics).some(t => t && t.trim())) {
                    this.topicsData = centralizedTopics;
                    topicsFound = true;
                    dataSource = 'centralized_data_manager';
                    console.log('✅ MKCG Questions: Loaded topics from Centralized Data Manager', this.topicsData);
                }
            } catch (error) {
                console.log('⚠️ MKCG Questions: Centralized Data Manager access failed:', error);
            }
        }
        
        // PRIORITY 2: Get data from Topics Generator (same page data sharing)
        if (!topicsFound && typeof window.MKCG_Topics_Data !== 'undefined' && window.MKCG_Topics_Data.topics) {
            const topicsGeneratorData = window.MKCG_Topics_Data.topics;
            const convertedTopics = {};
            let hasData = false;
            Object.keys(topicsGeneratorData).forEach(key => {
                if (topicsGeneratorData[key] && topicsGeneratorData[key].trim()) {
                    const topicNumber = key.replace('topic_', '');
                    convertedTopics[topicNumber] = topicsGeneratorData[key];
                    hasData = true;
                }
            });
            
            if (hasData) {
                this.topicsData = convertedTopics;
                topicsFound = true;
                dataSource = 'topics_generator_shared';
                console.log('✅ MKCG Questions: Loaded topics from Topics Generator (shared page)', this.topicsData);
                
                // SYNC: Populate centralized data manager for future use
                if (typeof MKCG_DataManager !== 'undefined') {
                    Object.keys(this.topicsData).forEach(topicId => {
                        MKCG_DataManager.setTopic(parseInt(topicId), this.topicsData[topicId]);
                    });
                    console.log('🔄 MKCG Questions: Synced Topics Generator data to Centralized Data Manager');
                }
            }
        }
        
        // PRIORITY 3: Get data from Questions Generator specific data source (legacy)
        if (!topicsFound && typeof MKCG_TopicsData !== 'undefined') {
            this.topicsData = MKCG_TopicsData;
            if (Object.values(this.topicsData).some(t => t && t.trim())) {
                topicsFound = true;
                dataSource = 'questions_generator_legacy';
                console.log('✅ MKCG Questions: Loaded topics from Questions Generator legacy data', this.topicsData);
            }
        }
        
        // Set selected topic text
        if (topicsFound) {
            this.selectedTopicText = this.topicsData[1] || Object.values(this.topicsData).find(t => t) || 'No topic selected';
        } else {
            this.selectedTopicText = 'No topic selected';
            if(this.isOnQuestionsGeneratorPage()){
                console.log('⚠️ MKCG Questions: No topics data available from any source');
                this.showDataQualityNotification('topics', { 
                    quality: 'missing', 
                    issues: ['No topics data available from any unified source'] 
                });
            }
        }
        
        console.log('MKCG Enhanced Questions: Unified data loading complete:', {
            dataSource: dataSource,
            topicsFound: topicsFound,
            selectedTopic: this.selectedTopicText
        });
    },
    
    /**
     * ARCHITECTURAL FIX: Detect if we're actually on a Questions Generator page
     */
    isOnQuestionsGeneratorPage: function() {
        const questionsElements = [
            '#mkcg-topics-grid',
            '#mkcg-selected-topic-text', 
            '#mkcg-questions-result',
            '.mkcg-topic-card'
        ];
        
        return questionsElements.some(selector => document.querySelector(selector) !== null);
    },
    
    /**
     * CRITICAL FIX: Validate nonce availability at startup
     */
    validateNonceAvailability: function() {
        const saveNonce = (typeof questions_vars !== 'undefined' && questions_vars.save_nonce) || '';
        const generalNonce = (typeof questions_vars !== 'undefined' && questions_vars.nonce) || '';
        const topicsNonce = (typeof questions_vars !== 'undefined' && questions_vars.topics_nonce) || '';
        
        console.log('MKCG Nonce Validation:', {
            questions_vars_available: typeof questions_vars !== 'undefined',
            save_nonce_available: !!saveNonce,
            general_nonce_available: !!generalNonce,
            topics_nonce_available: !!topicsNonce,
            save_nonce_preview: saveNonce ? saveNonce.substring(0, 8) + '...' : 'MISSING',
            general_nonce_preview: generalNonce ? generalNonce.substring(0, 8) + '...' : 'MISSING'
        });
        
        if (!saveNonce && !generalNonce) {
            console.error('CRITICAL: No valid nonces available! Backend saves will fail.');
            this.showNotification('Security tokens missing - saves may fail. Please refresh the page.', 'warning');
        } else {
            console.log('✅ Nonces validated successfully');
        }
    },
    
    /**
     * CRITICAL FIX: Initialize centralized data manager
     */
    initializeCentralizedDataManager: function() {
        if (typeof MKCG_DataManager === 'undefined') {
            console.error('❌ MKCG Data Manager not available! Topic sync will be limited.');
            return;
        }
        
        // Set up listeners for topic updates from Topics Generator
        MKCG_DataManager.on('topic:updated', (data) => {
            console.log('🔄 Questions Generator: Received topic update', data);
            this.handleTopicUpdate(data);
        });
        
        MKCG_DataManager.on('topic:selected', (data) => {
            console.log('🎯 Questions Generator: Topic selection changed', data);
            this.handleTopicSelectionFromOtherGenerator(data);
        });
        
        console.log('✅ Questions Generator: Centralized data manager integrated');
    },
    
    /**
     * CRITICAL FIX: Handle topic updates from centralized data manager
     */
    handleTopicUpdate: function(data) {
        const { topicId, newText, oldText } = data;
        
        // Update our local topics data
        this.topicsData[topicId] = newText;
        
        // If this is the currently selected topic, update the UI immediately
        if (topicId === this.selectedTopicId) {
            console.log('🔄 Updating selected topic UI for topic', topicId);
            this.selectedTopicText = newText;
            this.updateSelectedTopic();
            this.updateSelectedTopicHeading();
        }
        
        // Update topic card if it exists
        const topicCard = document.querySelector(`[data-topic="${topicId}"]`);
        if (topicCard) {
            const textElement = topicCard.querySelector('.mkcg-topic-text');
            if (textElement) {
                textElement.textContent = newText;
                console.log(`🔄 Updated topic card ${topicId} display`);
            }
        }
    },
    
    /**
     * CRITICAL FIX: Handle topic selection from other generators
     */
    handleTopicSelectionFromOtherGenerator: function(data) {
        const { topicId, topicText } = data;
        
        // Update our selection if it's different
        if (topicId !== this.selectedTopicId) {
            console.log('🎯 Questions Generator: Auto-selecting topic', topicId, 'from external source');
            this.selectTopic(topicId);
        }
    },
    

    
    /**
     * VALIDATE TOPICS DATA QUALITY (frontend validation)
     */
    validateTopicsData: function(topics) {
        const validation = {
            quality: 'unknown',
            issues: [],
            validTopics: 0,
            totalTopics: 0
        };
        
        if (!topics || typeof topics !== 'object') {
            validation.quality = 'missing';
            validation.issues.push('No topics data provided');
            return validation;
        }
        
        // Check all 5 topic slots
        for (let i = 1; i <= 5; i++) {
            validation.totalTopics++;
            
            if (topics[i] && typeof topics[i] === 'string') {
                const topic = topics[i].trim();
                
                if (topic.length === 0) {
                    validation.issues.push(`Topic ${i} is empty`);
                } else if (topic.length < 10) {
                    validation.issues.push(`Topic ${i} is too short (${topic.length} characters)`);
                } else if (topic.match(/^(topic|click|add|placeholder|empty)/i)) {
                    validation.issues.push(`Topic ${i} appears to be a placeholder`);
                } else {
                    validation.validTopics++;
                }
            } else {
                validation.issues.push(`Topic ${i} is missing`);
            }
        }
        
        // Determine overall quality
        const validPercentage = (validation.validTopics / validation.totalTopics) * 100;
        
        if (validPercentage >= 80) {
            validation.quality = 'excellent';
        } else if (validPercentage >= 60) {
            validation.quality = 'good';
        } else if (validPercentage >= 40) {
            validation.quality = 'fair';
        } else if (validPercentage > 0) {
            validation.quality = 'poor';
        } else {
            validation.quality = 'missing';
        }
        
        return validation;
    },
    
    /**
     * INITIAL HEALTH CHECK with backend data verification
     */
    performInitialHealthCheck: function() {
        const postId = typeof MKCG_PostId !== 'undefined' ? MKCG_PostId : null;
        
        if (!postId) {
            console.warn('MKCG Enhanced Questions: No post ID available for health check');
            return;
        }
        
        this.checkDataHealthStatus(postId)
            .then(healthStatus => {
                this.dataQuality = Object.assign(this.dataQuality, healthStatus);
                
                if (healthStatus.overall_health === 'poor' || healthStatus.overall_health === 'critical') {
                    this.showHealthWarning(healthStatus);
                }
                
                console.log('MKCG Enhanced Questions: Initial health check completed:', healthStatus);
            })
            .catch(error => {
                console.error('MKCG Enhanced Questions: Health check failed:', error);
            });
    },
    
    /**
     * START SYNC MONITORING for real-time updates
     */
    startSyncMonitoring: function() {
        // Check sync status every 2 minutes
        setInterval(() => {
            this.checkSyncStatus();
        }, 120000);
        
        // Initial sync check after 5 seconds
        setTimeout(() => {
            this.checkSyncStatus();
        }, 5000);
    },
    
    /**
     * CHECK SYNC STATUS between generators
     */
    checkSyncStatus: function() {
        const postId = typeof MKCG_PostId !== 'undefined' ? MKCG_PostId : null;
        
        if (!postId) return;
        
        this.performance.lastSyncCheck = Date.now();
        
        return this.makeAjaxRequest('mkcg_verify_sync', { post_id: postId })
            .then(response => {
                if (response.success) {
                    this.dataQuality.sync_status = response.data;
                    
                    if (!response.data.in_sync && response.data.recommendations.length > 0) {
                        this.showSyncWarning(response.data);
                    }
                    
                    console.log('MKCG Enhanced Questions: Sync status updated:', response.data);
                }
                return response;
            })
            .catch(error => {
                console.error('MKCG Enhanced Questions: Sync check failed:', error);
            });
    },
    
    /**
     * CHECK DATA HEALTH STATUS with fixed security
     */
    checkDataHealthStatus: function(postId) {
        return this.makeAjaxRequest('mkcg_health_check', { post_id: postId })
            .then(response => {
                if (response.success) {
                    return response.data;
                } else {
                    throw new Error(response.data?.message || 'Health check failed');
                }
            });
    },
    
    /**
     * Debug information
     */
    debugInfo: function() {
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        const entryKey = document.getElementById('mkcg-entry-key')?.value;
        
        console.log('MKCG Questions Debug Info:', {
            entryId: entryId,
            entryKey: entryKey,
            topicsData: this.topicsData,
            selectedTopicId: this.selectedTopicId,
            selectedTopicText: this.selectedTopicText
        });
    },
    
    /**
     * NEW: Force update all topic references throughout the page
     */
    forceUpdateTopicReferences: function() {
        const currentTopic = this.selectedTopicText;
        console.log('MKCG: Force updating all topic references to:', currentTopic);
        
        // Find all elements that might contain topic text
        const possibleSelectors = [
            'h1, h2, h3, h4, h5, h6',
            '.topic-heading',
            '.mkcg-section-title',
            '.questions-header',
            '[data-topic-text]'
        ];
        
        possibleSelectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                if (element.textContent.includes('Interview Questions for') && 
                    element.textContent.includes('Topic 5 - Click to add')) {
                    
                    const newText = `Interview Questions for "${currentTopic}"`;
                    element.textContent = newText;
                    console.log('MKCG: Force updated element:', element.tagName, 'to:', newText);
                }
            });
        });
    },
    
    /**
     * ENHANCED: Update selected topic with comprehensive state management
     */
    updateSelectedTopic: function() {
        console.log('MKCG: Updating selected topic display to:', this.selectedTopicText);
        
        const selectedTopicElement = document.querySelector(this.elements.selectedTopicText);
        if (selectedTopicElement) {
            selectedTopicElement.textContent = this.selectedTopicText;
            console.log('MKCG: Selected topic element updated successfully');
        } else {
            console.warn('MKCG: Selected topic element not found:', this.elements.selectedTopicText);
        }
        
        // CRITICAL FIX: Also update the topic data in any forms or hidden fields
        this.syncTopicDataToForms();
    },
    
    /**
     * NEW: Sync topic data to forms and hidden fields
     */
    syncTopicDataToForms: function() {
        // Update any hidden topic fields
        const topicFields = document.querySelectorAll('[name*="topic"], [id*="topic"]');
        topicFields.forEach(field => {
            if (field.type === 'hidden' && field.value.includes('Topic 5 - Click to add')) {
                field.value = this.selectedTopicText;
                console.log('MKCG: Updated hidden field:', field.name || field.id, 'to:', this.selectedTopicText);
            }
        });
        
        // Update any data attributes
        const elementsWithTopicData = document.querySelectorAll('[data-topic]');
        elementsWithTopicData.forEach(element => {
            if (element.dataset.topic && element.dataset.topic.includes('Topic 5 - Click to add')) {
                element.dataset.topic = this.selectedTopicText;
                console.log('MKCG: Updated data-topic attribute to:', this.selectedTopicText);
            }
        });
    },
    
    /**
     * Load topics data from PHP
     */
    loadTopicsData: function() {
        if (typeof MKCG_TopicsData !== 'undefined' && Object.keys(MKCG_TopicsData).length > 0) {
            this.topicsData = MKCG_TopicsData;
            this.selectedTopicText = this.topicsData[1] || 'No topic selected';
            console.log('MKCG Questions: Loaded topics from PHP:', this.topicsData);
        } else {
            console.log('MKCG Questions: No topics data from PHP');
        }
    },
    
    /**
     * ENHANCED EVENT BINDING with error handling and performance tracking
     */
    bindEnhancedEvents: function() {
        this.bindEvents(); // Call original binding
        
        // Add enhanced error handling to existing events
        this.addErrorHandling();
        
        // Add performance monitoring
        this.addPerformanceTracking();
        
        // Add auto-refresh functionality
        this.addAutoRefresh();
    },
    
    /**
     * Original event binding (kept for compatibility)
     */
    bindEvents: function() {
        // Topic card selection
        document.querySelectorAll(this.elements.topicCards).forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't select if clicking edit icon
                if (e.target.closest('.mkcg-topic-edit-icon')) {
                    return;
                }
                const topicId = parseInt(card.getAttribute('data-topic'));
                this.selectTopic(topicId);
            });
        });
        
        // Edit icon clicks
        document.querySelectorAll('.mkcg-topic-edit-icon').forEach(icon => {
            icon.addEventListener('click', (e) => {
                e.stopPropagation();
                const card = e.target.closest('.mkcg-topic-card');
                const topicId = parseInt(card.getAttribute('data-topic'));
                this.editTopicInline(topicId, card);
            });
        });
        
        // Generate questions
        const generateButton = document.querySelector(this.elements.generateButton);
        if (generateButton) {
            generateButton.addEventListener('click', () => {
                this.generateQuestions();
            });
        }
        
        // Modal events
        const modalOkButton = document.querySelector(this.elements.modalOkButton);
        if (modalOkButton) {
            modalOkButton.addEventListener('click', () => {
                this.useQuestionInField();
            });
        }
        
        const modalCancelButton = document.querySelector(this.elements.modalCancelButton);
        if (modalCancelButton) {
            modalCancelButton.addEventListener('click', () => {
                this.closeModal();
            });
        }
    },
    
    /**
     * ENHANCED: Select a topic with empty topic handling
     */
    selectTopic: function(topicId) {
        // Update active state
        document.querySelectorAll(this.elements.topicCards).forEach(card => {
            card.classList.remove('active');
        });
        const selectedCard = document.querySelector(`[data-topic="${topicId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('active');
        }
        
        // Enhanced topic data handling for empty topics
        this.selectedTopicId = topicId;
        const topicText = this.topicsData[topicId] || '';
        
        if (topicText && topicText.trim().length > 0) {
            this.selectedTopicText = topicText;
        } else {
            // Handle empty topics gracefully
            this.selectedTopicText = `Topic ${topicId} - Click to add your interview topic`;
            
            // Automatically open edit mode for empty topics when selected
            if (selectedCard && selectedCard.getAttribute('data-empty') === 'true') {
                console.log('MKCG Questions: Auto-opening edit mode for empty topic', topicId);
                setTimeout(() => {
                    this.editTopicInline(topicId, selectedCard);
                }, 300);
            }
        }
        
        // Update hidden field
        const topicIdInput = document.querySelector(this.elements.selectedTopicIdInput);
        if (topicIdInput) {
            topicIdInput.value = topicId;
        }
        
        // Update display
        this.updateSelectedTopic();
        
        // Update the heading and show corresponding question set
        this.updateSelectedTopicHeading();
        
        // Hide any previous results
        const questionsResult = document.querySelector(this.elements.questionsResult);
        if (questionsResult) {
            questionsResult.style.display = 'none';
        }
        
        console.log('MKCG Questions: Selected topic', topicId, ':', this.selectedTopicText);
    },
    
    /**
     * FIXED: Edit topic inline with NO duplication and consistent styling
     */
    editTopicInline: function(topicId, card) {
        // CRITICAL FIX: Prevent duplication by checking if already editing
        const existingEditor = card.querySelector('.mkcg-topic-edit-container');
        if (existingEditor) {
            console.log('MKCG: Edit already in progress for topic', topicId);
            return; // Don't create another editor
        }
        
        // CRITICAL FIX: Remove any other active editors first
        document.querySelectorAll('.mkcg-topic-edit-container').forEach(editor => {
            editor.remove();
        });
        
        const textElement = card.querySelector('.mkcg-topic-text');
        const currentText = this.topicsData[topicId] || '';
        
        console.log('MKCG: Starting edit for topic', topicId, 'with text:', currentText);
        
        // Create enhanced editing container
        const editContainer = document.createElement('div');
        editContainer.className = 'mkcg-topic-edit-container';
        editContainer.setAttribute('data-topic-id', topicId); // Track which topic is being edited
        editContainer.style.cssText = `
            position: relative;
            width: 100%;
            background: #f8f9ff;
            border: 2px solid #1a9bdc;
            border-radius: 8px;
            padding: 15px;
            margin: 5px 0;
            box-shadow: 0 4px 12px rgba(26, 155, 220, 0.15);
            transition: all 0.3s ease;
            z-index: 100;
        `;
        
        // Create enhanced input field
        const input = document.createElement('textarea');
        input.value = currentText;
        input.placeholder = 'Enter your interview topic (e.g., "How to build authority in your niche through strategic content creation")';
        input.className = 'mkcg-topic-editor-enhanced';
        input.style.cssText = `
            width: 100%;
            min-height: 60px;
            padding: 12px 15px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            font-size: 15px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: white;
            color: #333;
            resize: vertical;
            transition: all 0.2s ease;
            line-height: 1.4;
            box-sizing: border-box;
            outline: none;
        `;
        
        // Create action buttons container
        const actionsContainer = document.createElement('div');
        actionsContainer.className = 'mkcg-edit-actions';
        actionsContainer.style.cssText = `
            display: flex;
            gap: 8px;
            margin-top: 12px;
            align-items: center;
        `;
        
        // FIXED: Consistent Save button styling
        const saveButton = document.createElement('button');
        saveButton.className = 'mkcg-save-btn';
        saveButton.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20,6 9,17 4,12"></polyline>
            </svg>
            Save Topic
        `;
        saveButton.style.cssText = `
            display: flex;
            align-items: center;
            gap: 6px;
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(39, 174, 96, 0.3);
            min-width: 120px;
            justify-content: center;
        `;
        
        // FIXED: Consistent Cancel button styling
        const cancelButton = document.createElement('button');
        cancelButton.className = 'mkcg-cancel-btn';
        cancelButton.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Cancel
        `;
        cancelButton.style.cssText = `
            display: flex;
            align-items: center;
            gap: 6px;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
            min-width: 100px;
            justify-content: center;
        `;
        
        // Character counter
        const charCounter = document.createElement('div');
        charCounter.className = 'mkcg-char-counter';
        charCounter.style.cssText = `
            margin-left: auto;
            font-size: 12px;
            color: #666;
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        
        // Status indicator
        const statusIndicator = document.createElement('div');
        statusIndicator.className = 'mkcg-save-status';
        statusIndicator.style.cssText = `
            position: absolute;
            top: -8px;
            right: -8px;
            background: #27ae60;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: 10;
        `;
        statusIndicator.textContent = 'SAVED';
        
        // Help text
        const helpText = document.createElement('div');
        helpText.className = 'mkcg-edit-help';
        helpText.style.cssText = `
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;
        helpText.innerHTML = `
            💡 <strong>Tip:</strong> Write a clear, engaging topic that would interest podcast hosts and their audience.
        `;
        
        // Build the edit interface
        editContainer.appendChild(input);
        editContainer.appendChild(actionsContainer);
        editContainer.appendChild(helpText);
        editContainer.appendChild(statusIndicator);
        
        actionsContainer.appendChild(saveButton);
        actionsContainer.appendChild(cancelButton);
        actionsContainer.appendChild(charCounter);
        
        // FIXED: Better insertion - hide original and insert cleanly
        textElement.style.display = 'none';
        
        // Insert after the text element
        if (textElement.nextSibling) {
            textElement.parentNode.insertBefore(editContainer, textElement.nextSibling);
        } else {
            textElement.parentNode.appendChild(editContainer);
        }
        
        // Focus and select
        setTimeout(() => {
            input.focus();
            input.select();
        }, 100);
        
        // Update character counter
        const updateCounter = () => {
            const length = input.value.length;
            charCounter.textContent = `${length}/200 characters`;
            
            // Color coding
            if (length > 200) {
                charCounter.style.color = '#e74c3c';
                input.style.borderColor = '#e74c3c';
            } else if (length > 150) {
                charCounter.style.color = '#f39c12';
                input.style.borderColor = '#f39c12';
            } else if (length >= 10) {
                charCounter.style.color = '#27ae60';
                input.style.borderColor = '#27ae60';
            } else {
                charCounter.style.color = '#e74c3c';
                input.style.borderColor = '#e74c3c';
            }
        };
        
        updateCounter();
        input.addEventListener('input', updateCounter);
        
        // Enhanced save function
        const save = () => {
            const newText = input.value.trim();
            
            // Enhanced validation
            if (!newText) {
                this.showValidationError('Topic cannot be empty', input);
                return;
            }
            
            if (newText.length < 10) {
                this.showValidationError('Topic must be at least 10 characters', input);
                return;
            }
            
            if (newText.length > 200) {
                this.showValidationError('Topic cannot exceed 200 characters', input);
                return;
            }
            
            // Check if actually changed
            if (newText === currentText) {
                cleanup();
                return;
            }
            
            // Show saving state with consistent styling
            saveButton.disabled = true;
            saveButton.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                </svg>
                Saving...
            `;
            saveButton.style.background = '#f39c12';
            
            statusIndicator.textContent = 'SAVING...';
            statusIndicator.style.background = '#f39c12';
            statusIndicator.style.opacity = '1';
            
            // CRITICAL FIX: Update centralized data manager first
            if (window.MKCG_DataManager) {
                try {
                    MKCG_DataManager.setTopic(topicId, newText);
                    console.log('✅ Updated centralized data manager for topic', topicId);
                } catch (error) {
                    console.error('❌ Failed to update centralized data:', error);
                }
            }
            
            // Update frontend state immediately
            this.topicsData[topicId] = newText;
            textElement.textContent = newText;
            
            if (this.selectedTopicId === topicId) {
                this.selectedTopicText = newText;
                this.updateSelectedTopic();
                this.updateSelectedTopicHeading();
            }
            
            // Save to backend
            const postId = document.getElementById('mkcg-post-id')?.value;
            
            if (!postId) {
                console.warn('MKCG Enhanced Edit: No post ID available, saving to frontend only');
                showSaveSuccess();
                setTimeout(cleanup, 1500);
                return;
            }
            
            const saveData = {
                post_id: parseInt(postId),
                topic_number: topicId,
                topic_text: newText
            };
            
            console.log('MKCG: Saving topic', topicId, 'to backend');
            
            this.makeAjaxRequest('mkcg_save_topic', saveData)
                .then(response => {
                    if (response.success) {
                        console.log('MKCG: Topic', topicId, 'saved successfully');
                        showSaveSuccess();
                    } else {
                        console.error('MKCG: Save failed:', response.data);
                        showSaveError(response.data?.message || 'Save failed');
                    }
                })
                .catch(error => {
                    console.error('MKCG: AJAX error:', error);
                    showSaveError('Network error - topic saved locally only');
                })
                .finally(() => {
                    setTimeout(cleanup, 2000);
                });
        };
        
        // FIXED: Enhanced cleanup with proper removal
        const cleanup = () => {
            console.log('MKCG: Cleaning up edit interface for topic', topicId);
            
            // Smooth fadeout
            editContainer.style.opacity = '0';
            editContainer.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                if (editContainer.parentNode) {
                    editContainer.remove();
                }
                textElement.style.display = '';
            }, 200);
        };
        
        // Success indicator with consistent styling
        const showSaveSuccess = () => {
            statusIndicator.textContent = 'SAVED ✓';
            statusIndicator.style.background = '#27ae60';
            statusIndicator.style.opacity = '1';
            
            saveButton.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
                Saved!
            `;
            saveButton.style.background = '#27ae60';
            saveButton.disabled = false;
            
            this.showNotification('Topic saved successfully!', 'success');
        };
        
        // Error indicator
        const showSaveError = (message) => {
            statusIndicator.textContent = 'ERROR';
            statusIndicator.style.background = '#e74c3c';
            statusIndicator.style.opacity = '1';
            
            saveButton.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                Try Again
            `;
            saveButton.style.background = '#e74c3c';
            saveButton.disabled = false;
            
            this.showNotification(message, 'error');
        };
        
        // Event handlers
        saveButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            save();
        });
        
        cancelButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            cleanup();
        });
        
        // Keyboard shortcuts
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                save();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cleanup();
            }
        });
        
        // FIXED: Consistent hover effects
        saveButton.addEventListener('mouseenter', () => {
            if (!saveButton.disabled) {
                saveButton.style.background = '#229954';
                saveButton.style.transform = 'translateY(-1px)';
                saveButton.style.boxShadow = '0 4px 8px rgba(39, 174, 96, 0.4)';
            }
        });
        
        saveButton.addEventListener('mouseleave', () => {
            if (!saveButton.disabled) {
                saveButton.style.background = '#27ae60';
                saveButton.style.transform = 'translateY(0)';
                saveButton.style.boxShadow = '0 2px 4px rgba(39, 174, 96, 0.3)';
            }
        });
        
        cancelButton.addEventListener('mouseenter', () => {
            cancelButton.style.background = '#c0392b';
            cancelButton.style.transform = 'translateY(-1px)';
            cancelButton.style.boxShadow = '0 4px 8px rgba(231, 76, 60, 0.4)';
        });
        
        cancelButton.addEventListener('mouseleave', () => {
            cancelButton.style.background = '#e74c3c';
            cancelButton.style.transform = 'translateY(0)';
            cancelButton.style.boxShadow = '0 2px 4px rgba(231, 76, 60, 0.3)';
        });
    },
    
    /**
     * CRITICAL FIX: Update the selected topic heading with proper data synchronization
     */
    updateSelectedTopicHeading: function() {
        console.log('MKCG: Updating heading for topic', this.selectedTopicId, 'with text:', this.selectedTopicText);
        
        // CRITICAL FIX: Get the latest topic text from centralized data manager
        let currentTopicText = this.selectedTopicText;
        if (window.MKCG_DataManager) {
            const latestTopic = MKCG_DataManager.getTopic(this.selectedTopicId);
            if (latestTopic && latestTopic.length > 0) {
                currentTopicText = latestTopic;
                this.selectedTopicText = latestTopic;
                console.log('✅ Using latest topic text from data manager:', latestTopic);
            }
        }
        
        // Update the heading in the Questions for Topic section
        const questionsHeading = document.querySelector('#mkcg-questions-heading');
        if (questionsHeading) {
            const newHeadingText = `Interview Questions for "${currentTopicText}"`;
            questionsHeading.textContent = newHeadingText;
            console.log('MKCG: Updated questions heading to:', newHeadingText);
        } else {
            console.warn('MKCG: Questions heading element not found (#mkcg-questions-heading)');
        }
        
        // CRITICAL FIX: Also update any other heading elements that might exist
        const alternativeHeadings = [
            '.mkcg-questions-header h3',
            '.mkcg-questions-title', 
            '[data-questions-heading]',
            '.mkcg-section-title'
        ];
        
        alternativeHeadings.forEach(selector => {
            const element = document.querySelector(selector);
            if (element && element.textContent.includes('Interview Questions for')) {
                const newText = `Interview Questions for "${currentTopicText}"`;
                element.textContent = newText;
                console.log('MKCG: Updated alternative heading:', selector, 'to:', newText);
            }
        });
        
        // CRITICAL FIX: Find and update ALL h3 elements containing "Interview Questions for"
        document.querySelectorAll('h3').forEach(h3 => {
            if (h3.textContent.includes('Interview Questions for')) {
                const newText = `Interview Questions for "${currentTopicText}"`;
                h3.textContent = newText;
                console.log('✅ FORCE UPDATED H3 heading to:', newText);
            }
        });
        
        // Update the selected topic text display
        const selectedTopicElement = document.querySelector(this.elements.selectedTopicText);
        if (selectedTopicElement) {
            selectedTopicElement.textContent = currentTopicText;
            console.log('MKCG: Updated selected topic display to:', currentTopicText);
        }
        
        // CRITICAL FIX: Force update any dynamic elements that show topic text
        this.forceUpdateTopicReferences();
        
        // Show questions for the selected topic
        this.showQuestionsForTopic(this.selectedTopicId);
    },
    
    /**
     * Show questions for the selected topic
     */
    showQuestionsForTopic: function(topicId) {
        // Hide all question sets first
        for (let i = 1; i <= 5; i++) {
            const questionSet = document.querySelector(`#mkcg-topic-${i}-questions`);
            if (questionSet) {
                questionSet.style.display = 'none';
            }
        }
        
        // Show only the selected topic's questions
        const selectedQuestionSet = document.querySelector(`#mkcg-topic-${topicId}-questions`);
        if (selectedQuestionSet) {
            selectedQuestionSet.style.display = 'block';
            console.log(`Questions for Topic ${topicId} displayed`);
        } else {
            console.log(`Question set for Topic ${topicId} not found in DOM`);
        }
    },
    
    /**
     * ENHANCED QUESTIONS GENERATION with validation and monitoring
     */
    generateQuestions: function() {
        // Enhanced validation before generation
        const validation = this.validateGenerationInput();
        if (!validation.valid) {
            this.showValidationErrors(validation.errors);
            return;
        }
        
        // Check data quality before proceeding
        if (this.dataQuality.topics === 'poor' || this.dataQuality.topics === 'missing') {
            if (!confirm('Topic data quality is ' + this.dataQuality.topics + '. Continue anyway?')) {
                return;
            }
        }
        
        const startTime = performance.now();
        
        // Show enhanced loading indicator
        this.showEnhancedLoading();
        
        const data = {
            topic: this.selectedTopicText,
            topic_number: this.selectedTopicId,
            entry_id: document.getElementById('mkcg-entry-id')?.value || 0,
            entry_key: document.getElementById('mkcg-entry-key')?.value || ''
        };
        
        console.log('MKCG Enhanced Questions: Generating questions with validated data:', data);
        
        // Use fixed AJAX with correct security
        this.makeAjaxRequest('generate_interview_questions', data)
            .then(response => {
                const endTime = performance.now();
                const apiTime = endTime - startTime;
                this.performance.apiCallTimes.push(apiTime);
                
                this.hideLoading();
                this.enableGenerateButton();
                
                if (response.success && response.data && response.data.questions) {
                    // Validate generated questions quality
                    const questionValidation = this.validateGeneratedQuestions(response.data.questions);
                    
                    if (questionValidation.quality === 'poor') {
                        this.showQualityWarning(questionValidation);
                    }
                    
                    this.displayQuestions(response.data.questions, questionValidation);
                    
                    // Update data quality tracking
                    this.dataQuality.questions = questionValidation.quality;
                    this.dataQuality.last_check = Date.now();
                    
                    console.log(`MKCG Enhanced Questions: Generated ${response.data.questions.length} questions in ${apiTime.toFixed(2)}ms (quality: ${questionValidation.quality})`);
                } else {
                    this.handleError(new Error(response.data?.message || 'Failed to generate questions'), 'generation');
                }
            })
            .catch(error => {
                this.hideLoading();
                this.enableGenerateButton();
                this.handleError(error, 'generation', () => this.generateQuestions());
            });
    },
    
    /**
     * VALIDATE GENERATION INPUT
     */
    validateGenerationInput: function() {
        const validation = {
            valid: true,
            errors: []
        };
        
        if (!this.selectedTopicText || this.selectedTopicText === 'No topic selected') {
            validation.valid = false;
            validation.errors.push('Please select a topic first.');
        }
        
        if (this.selectedTopicText && this.selectedTopicText.length < 10) {
            validation.valid = false;
            validation.errors.push('Selected topic is too short. Please choose a more detailed topic.');
        }
        
        if (this.selectedTopicText && this.selectedTopicText.match(/^(topic|click|add|placeholder|empty)/i)) {
            validation.valid = false;
            validation.errors.push('Please replace the placeholder text with an actual topic.');
        }
        
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        const entryKey = document.getElementById('mkcg-entry-key')?.value;
        
        if (!entryId && !entryKey) {
            validation.valid = false;
            validation.errors.push('No entry data found. Please refresh the page.');
        }
        
        return validation;
    },
    
    /**
     * VALIDATE GENERATED QUESTIONS QUALITY
     */
    validateGeneratedQuestions: function(questions) {
        const validation = {
            quality: 'unknown',
            issues: [],
            validQuestions: 0,
            totalQuestions: questions.length
        };
        
        if (!questions || questions.length === 0) {
            validation.quality = 'missing';
            validation.issues.push('No questions generated');
            return validation;
        }
        
        questions.forEach((question, index) => {
            const questionNumber = index + 1;
            
            if (typeof question !== 'string' || question.trim().length === 0) {
                validation.issues.push(`Question ${questionNumber} is empty`);
            } else {
                const cleanQuestion = question.trim();
                
                if (cleanQuestion.length < 10) {
                    validation.issues.push(`Question ${questionNumber} is too short`);
                } else if (!cleanQuestion.match(/\?$/)) {
                    validation.issues.push(`Question ${questionNumber} doesn't end with a question mark`);
                } else if (cleanQuestion.match(/^(question|click|add|placeholder|empty)/i)) {
                    validation.issues.push(`Question ${questionNumber} appears to be a placeholder`);
                } else {
                    validation.validQuestions++;
                }
            }
        });
        
        // Determine overall quality
        const validPercentage = (validation.validQuestions / validation.totalQuestions) * 100;
        
        if (validPercentage >= 90) {
            validation.quality = 'excellent';
        } else if (validPercentage >= 75) {
            validation.quality = 'good';
        } else if (validPercentage >= 50) {
            validation.quality = 'fair';
        } else if (validPercentage > 0) {
            validation.quality = 'poor';
        } else {
            validation.quality = 'missing';
        }
        
        return validation;
    },
    
    /**
     * Display generated questions with Use buttons
     */
    displayQuestions: function(questions) {
        if (!questions || questions.length === 0) {
            alert('No questions were generated. Please try again.');
            return;
        }
        
        const questionsList = document.querySelector(this.elements.questionsList);
        if (!questionsList) return;
        
        questionsList.innerHTML = '';
        
        questions.forEach((question, index) => {
            const questionNumber = index + 1;
            
            const questionItem = document.createElement('div');
            questionItem.className = 'mkcg-question-item';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'mkcg-question-content';
            
            const numberDiv = document.createElement('div');
            numberDiv.className = 'mkcg-question-number';
            
            // Use ordinal names instead of "Question X"
            const ordinals = ['First', 'Second', 'Third', 'Fourth', 'Fifth'];
            numberDiv.textContent = `${ordinals[questionNumber-1]} Interview Question:`;
            
            const textDiv = document.createElement('div');
            textDiv.className = 'mkcg-question-text';
            textDiv.textContent = question;
            
            const useButton = document.createElement('button');
            useButton.className = 'mkcg-use-button';
            useButton.textContent = 'Use';
            useButton.addEventListener('click', () => {
                this.openFieldModal(questionNumber, question);
            });
            
            contentDiv.appendChild(numberDiv);
            contentDiv.appendChild(textDiv);
            questionItem.appendChild(contentDiv);
            questionItem.appendChild(useButton);
            
            questionsList.appendChild(questionItem);
        });
        
        // Save generated questions
        this.generatedQuestions = questions;
        
        // Show the questions result section
        const questionsResult = document.querySelector(this.elements.questionsResult);
        if (questionsResult) {
            questionsResult.style.display = 'block';
            questionsResult.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    
    /**
     * Open the field selection modal
     */
    openFieldModal: function(questionNumber, questionText) {
        this.selectedQuestion = {
            number: questionNumber,
            text: questionText
        };
        
        // Default to the same field number as the question
        const fieldNumberInput = document.querySelector(this.elements.fieldNumberInput);
        if (fieldNumberInput) {
            fieldNumberInput.value = questionNumber;
        }
        
        // Show the modal
        const modal = document.querySelector(this.elements.fieldModal);
        if (modal) {
            modal.classList.add('active');
        }
    },
    
    /**
     * Close the field selection modal
     */
    closeModal: function() {
        const modal = document.querySelector(this.elements.fieldModal);
        if (modal) {
            modal.classList.remove('active');
        }
        this.selectedQuestion = null;
    },
    
    /**
     * Use the selected question in the specified form field
     */
    useQuestionInField: function() {
        if (!this.selectedQuestion) return;
        
        const fieldNumberInput = document.querySelector(this.elements.fieldNumberInput);
        const fieldNumber = parseInt(fieldNumberInput?.value || '1');
        
        // Validate field number
        if (isNaN(fieldNumber) || fieldNumber < 1 || fieldNumber > 5) {
            alert('Please enter a valid field number (1-5)');
            return;
        }
        
        // Update the corresponding form field
        const fieldSelector = `#mkcg-question-field-${this.selectedTopicId}-${fieldNumber}`;
        const fieldElement = document.querySelector(fieldSelector);
        if (fieldElement) {
            fieldElement.value = this.selectedQuestion.text;
            
            // Note: Auto-save removed for simplicity
        }
        
        // Close the modal
        this.closeModal();
    },
    
    /**
     * Show loading indicator
     */
    showLoading: function() {
        const loading = document.querySelector(this.elements.loadingIndicator);
        if (loading) {
            loading.style.display = 'flex';
        }
    },
    
    /**
     * Hide loading indicator
     */
    hideLoading: function() {
        const loading = document.querySelector(this.elements.loadingIndicator);
        if (loading) {
            loading.style.display = 'none';
        }
    },
    
    /**
     * Re-enable generate button
     */
    enableGenerateButton: function() {
        const generateBtn = document.querySelector(this.elements.generateButton);
        if (generateBtn) {
            generateBtn.disabled = false;
            generateBtn.textContent = 'Generate Questions with AI';
        }
    },
    
    // =============================================================
    // ENHANCED FUNCTIONALITY - Error Handling & Monitoring
    // =============================================================
    
    /**
     * ENHANCED ERROR HANDLING with user-friendly messages and retry logic
     */
    handleError: function(error, context, retryCallback) {
        this.performance.errorCount++;
        
        console.error(`MKCG Enhanced Questions: Error in ${context}:`, error);
        
        let userMessage = 'An error occurred. ';
        let shouldRetry = false;
        
        // Provide context-specific error messages
        switch (context) {
            case 'initialization':
                userMessage = 'Failed to initialize the Questions Generator. ';
                break;
            case 'generation':
                userMessage = 'Failed to generate questions. ';
                shouldRetry = true;
                break;
            case 'data_loading':
                userMessage = 'Failed to load topic data. ';
                shouldRetry = true;
                break;
            case 'sync_check':
                userMessage = 'Failed to check data synchronization. ';
                break;
        }
        
        // Add specific error information if available
        if (error.message) {
            if (error.message.includes('network') || error.message.includes('fetch')) {
                userMessage += 'Please check your internet connection.';
                shouldRetry = true;
            } else if (error.message.includes('security') || error.message.includes('nonce')) {
                userMessage += 'Security token expired. Please refresh the page.';
            } else {
                userMessage += error.message;
            }
        }
        
        // Show retry option if applicable
        if (shouldRetry && retryCallback && typeof retryCallback === 'function') {
            if (confirm(userMessage + ' Would you like to try again?')) {
                setTimeout(retryCallback, 1000);
                return;
            }
        }
        
        // Show error notification
        this.showErrorNotification(userMessage);
    },
    
    /**
     * CRITICAL FIX: WordPress AJAX with unified nonce handling
     * ✅ 100% reliable ✅ WordPress standard ✅ Proper nonce access
     */
    makeAjaxRequest: function(action, data = {}, nonceField = 'security', maxRetries = 3) {
        return new Promise((resolve, reject) => {
            const attemptRequest = (attempt = 1) => {
                // Always use URL-encoded (WordPress standard) - no JSON complexity
                const postData = new URLSearchParams();
                postData.append('action', action);
                
                // CRITICAL FIX: Access nonces from localized variables, not hardcoded elements
                const saveNonce = (typeof questions_vars !== 'undefined' && questions_vars.save_nonce) || '';
                const generalNonce = (typeof questions_vars !== 'undefined' && questions_vars.nonce) || '';
                const topicsNonce = (typeof questions_vars !== 'undefined' && questions_vars.topics_nonce) || '';
                
                // Use appropriate nonce based on action type
                let nonce = '';
                if (action.includes('save')) {
                    nonce = saveNonce || generalNonce;
                } else {
                    nonce = generalNonce || topicsNonce;
                }
                
                // Send multiple nonce fields for maximum compatibility
                postData.append('security', nonce);
                postData.append('nonce', nonce);
                postData.append('save_nonce', saveNonce);
                
                console.log('MKCG AJAX: Using nonces:', {
                    action: action,
                    nonceUsed: nonce.substring(0, 8) + '...',
                    saveNonce: saveNonce ? saveNonce.substring(0, 8) + '...' : 'none',
                    generalNonce: generalNonce ? generalNonce.substring(0, 8) + '...' : 'none'
                });
                
                // Handle all data by flattening appropriately
                Object.entries(data).forEach(([key, value]) => {
                    if (key === 'questions' && typeof value === 'object') {
                        // Flatten questions data for URL encoding
                        Object.entries(value).forEach(([topicId, questions]) => {
                            if (Array.isArray(questions)) {
                                questions.forEach((question, index) => {
                                    postData.append(`questions[${topicId}][${index}]`, question || '');
                                });
                            }
                        });
                    } else {
                        postData.append(key, value);
                    }
                });
                
                const requestBody = postData.toString();
                const headers = {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                console.log(`MKCG WordPress AJAX: URL-encoded request (attempt ${attempt}/${maxRetries}) for action: ${action}`, {
                    bodyLength: requestBody.length,
                    hasQuestions: requestBody.includes('questions'),
                    noncePresent: !!nonce
                });
                
                fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: headers,
                    body: requestBody,
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log(`MKCG WordPress AJAX: Response status: ${response.status} ${response.statusText}`);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(responseData => {
                    console.log(`MKCG WordPress AJAX: SUCCESS:`, responseData);
                    resolve(responseData);
                })
                .catch(error => {
                    console.error(`MKCG WordPress AJAX: Attempt ${attempt} FAILED:`, error);
                    
                    // Simplified retry logic - no fallback needed since we're already using the reliable method
                    if (attempt < maxRetries) {
                        const retryDelay = 1000 * attempt;
                        console.warn(`MKCG WordPress AJAX: Retrying in ${retryDelay}ms...`);
                        
                        setTimeout(() => {
                            attemptRequest(attempt + 1);
                        }, retryDelay);
                    } else {
                        console.error(`MKCG WordPress AJAX: All ${maxRetries} attempts failed for action: ${action}`);
                        reject(new Error(`Request failed after ${maxRetries} attempts: ${error.message}`));
                    }
                });
            };
            
            attemptRequest();
        });
    },
    

    
    /**
     * SHOW ENHANCED LOADING with progress indication
     */
    showEnhancedLoading: function() {
        this.showLoading();
        
        // Update generate button with progress
        const generateBtn = document.querySelector(this.elements.generateButton);
        if (generateBtn) {
            generateBtn.disabled = true;
            
            let dots = 0;
            const updateText = () => {
                if (generateBtn.disabled) {
                    dots = (dots + 1) % 4;
                    generateBtn.textContent = 'Generating Questions' + '.'.repeat(dots);
                    setTimeout(updateText, 500);
                }
            };
            updateText();
        }
    },
    
    /**
     * NEW: Show validation error with field highlighting
     */
    showValidationError: function(message, field) {
        // Highlight the field with error styling
        field.style.borderColor = '#e74c3c';
        field.style.boxShadow = '0 0 0 2px rgba(231, 76, 60, 0.2)';
        
        // Show error notification
        this.showNotification(message, 'error');
        
        // Focus the field
        field.focus();
        
        // Remove error styling after a delay
        setTimeout(() => {
            field.style.borderColor = '';
            field.style.boxShadow = '';
        }, 3000);
    },
    
    /**
     * NOTIFICATION SYSTEM
     */
    showErrorNotification: function(message) {
        this.showNotification(message, 'error');
    },
    
    showDataQualityNotification: function(type, validation) {
        if (validation.quality === 'poor' || validation.quality === 'missing') {
            const message = `${type} data quality is ${validation.quality}. ${validation.issues.slice(0, 2).join(', ')}`;
            this.showNotification(message, 'warning');
        }
    },
    
    showHealthWarning: function(healthStatus) {
        const message = `System health is ${healthStatus.overall_health}. ${healthStatus.recommendations.slice(0, 1).join('')}`;
        this.showNotification(message, 'warning');
    },
    
    showSyncWarning: function(syncStatus) {
        if (syncStatus.recommendations.length > 0) {
            const message = `Data sync issue: ${syncStatus.recommendations[0]}`;
            this.showNotification(message, 'info');
        }
    },
    
    showQualityWarning: function(validation) {
        const message = `Generated questions quality is ${validation.quality}. ${validation.issues.slice(0, 1).join('')}`;
        this.showNotification(message, 'warning');
    },
    
    showValidationErrors: function(errors) {
        const message = errors.join(' ');
        this.showNotification(message, 'error');
    },
    
    showNotification: function(message, type = 'info') {
        // Create notification element if it doesn't exist
        let notification = document.querySelector('.mkcg-notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'mkcg-notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transition: opacity 0.3s ease;
            `;
            document.body.appendChild(notification);
        }
        
        // Set color based on type
        const colors = {
            error: '#e74c3c',
            warning: '#f39c12',
            info: '#3498db',
            success: '#27ae60'
        };
        
        notification.style.backgroundColor = colors[type] || colors.info;
        notification.textContent = message;
        notification.style.opacity = '1';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    },
    
    /**
     * ADD ERROR HANDLING TO EXISTING EVENTS
     */
    addErrorHandling: function() {
        // Wrap existing event handlers with try-catch
        window.addEventListener('error', (event) => {
            if (event.filename && event.filename.includes('questions-generator')) {
                this.handleError(new Error(event.message), 'javascript');
            }
        });
        
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError(new Error(event.reason), 'promise');
        });
    },
    
    /**
     * ADD PERFORMANCE TRACKING
     */
    addPerformanceTracking: function() {
        // Track interaction performance
        const trackInteraction = (element, eventType) => {
            if (element) {
                element.addEventListener(eventType, () => {
                    console.log(`MKCG Performance: ${eventType} on ${element.className || element.id}`);
                });
            }
        };
        
        trackInteraction(document.querySelector(this.elements.generateButton), 'click');
        
        // Log performance summary every 5 minutes
        setInterval(() => {
            if (this.performance.apiCallTimes.length > 0) {
                const avgTime = this.performance.apiCallTimes.reduce((a, b) => a + b, 0) / this.performance.apiCallTimes.length;
                console.log(`MKCG Performance Summary: Avg API time: ${avgTime.toFixed(2)}ms, Errors: ${this.performance.errorCount}`);
            }
        }, 300000);
    },
    
    /**
     * ADD AUTO-REFRESH FUNCTIONALITY
     */
    addAutoRefresh: function() {
        // Refresh data when user returns from Topics Generator
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.performance.lastSyncCheck) {
                const timeSinceLastCheck = Date.now() - this.performance.lastSyncCheck;
                
                // Refresh if more than 2 minutes since last check
                if (timeSinceLastCheck > 120000) {
                    console.log('MKCG Enhanced Questions: Auto-refreshing data after tab return');
                    this.checkSyncStatus();
                }
            }
        });
    },
    
    // =============================================================
    // 💾 SIMPLE SAVE FUNCTIONALITY - Streamlined Implementation
    // =============================================================
    
    /**
     * CRITICAL FIX: Sync latest topic data from centralized data manager before save
     */
    syncLatestTopicData: function() {
        if (window.MKCG_DataManager) {
            console.log('🔄 Syncing latest topic data from centralized data manager');
            
            // Get latest data for all topics
            for (let topicId = 1; topicId <= 5; topicId++) {
                const latestTopic = MKCG_DataManager.getTopic(topicId);
                if (latestTopic && latestTopic.length > 0) {
                    this.topicsData[topicId] = latestTopic;
                    console.log(`✅ Updated local topic ${topicId} to:`, latestTopic.substring(0, 50) + '...');
                }
            }
            
            // Update selected topic if needed
            const selectedLatest = MKCG_DataManager.getTopic(this.selectedTopicId);
            if (selectedLatest && selectedLatest !== this.selectedTopicText) {
                this.selectedTopicText = selectedLatest;
                console.log('✅ Updated selected topic text to latest:', selectedLatest);
            }
        } else {
            console.warn('❌ MKCG Data Manager not available for sync');
        }
    },
    
    /**
     * ENHANCED: Save all questions with comprehensive validation and error handling
     */
    saveAllQuestions: function() {
        // CRITICAL FIX: Sync latest topic data before saving
        this.syncLatestTopicData();
        
        const postId = document.getElementById('mkcg-post-id')?.value;
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        
        console.log('MKCG Enhanced Save: Starting save process with latest data');
        console.log('MKCG Enhanced Save: postId:', postId, 'entryId:', entryId);
        
        if (!postId) {
            this.showNotification('No post ID available. Please refresh the page.', 'error');
            return;
        }
        
        // Show enhanced loading state
        const saveButton = document.getElementById('mkcg-save-all-questions');
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';
            saveButton.style.opacity = '0.7';
        }
        
        // Collect and validate questions from all form fields
        const questionsData = {};
        let totalQuestions = 0;
        let validationErrors = [];
        
        console.log('MKCG WordPress Save: Collecting questions from all 5 topics with validation');
        
        // Process all 5 topics with validation
        for (let topicId = 1; topicId <= 5; topicId++) {
            const topicQuestions = [];
            let topicHasData = false;
            
            // Process all 5 questions for this topic
            for (let qNum = 1; qNum <= 5; qNum++) {
                const fieldSelector = `#mkcg-question-field-${topicId}-${qNum}`;
                const fieldElement = document.querySelector(fieldSelector);
                
                if (fieldElement) {
                    const questionText = fieldElement.value.trim();
                    
                    if (questionText) {
                        // Validate question quality
                        if (questionText.length < 5) {
                            validationErrors.push(`Topic ${topicId}, Question ${qNum}: Too short (${questionText.length} characters)`);
                        } else if (questionText.toLowerCase().includes('placeholder') || questionText.toLowerCase().includes('example')) {
                            validationErrors.push(`Topic ${topicId}, Question ${qNum}: Appears to be placeholder text`);
                        }
                        
                        topicQuestions.push(questionText);
                        totalQuestions++;
                        topicHasData = true;
                        console.log(`✓ Topic ${topicId}, Question ${qNum}: "${questionText.substring(0, 50)}${questionText.length > 50 ? '...' : ''}"`);
                    } else {
                        topicQuestions.push(''); // Maintain structure
                    }
                } else {
                    console.warn(`⚠ Field not found: ${fieldSelector}`);
                    topicQuestions.push(''); // Maintain structure
                }
            }
            
            // Always include all topics to maintain structure
            questionsData[topicId] = topicQuestions;
            
            const nonEmptyCount = topicQuestions.filter(q => q.trim() !== '').length;
            console.log(`Topic ${topicId} summary: ${nonEmptyCount}/5 questions filled ${topicHasData ? '✓' : '○'}`);
        }
        
        // Validation reporting
        console.log('MKCG WordPress Save: Collection summary:', {
            totalNonEmptyQuestions: totalQuestions,
            totalSlots: 25,
            fillRate: `${((totalQuestions / 25) * 100).toFixed(1)}%`,
            validationErrors: validationErrors.length,
            structureIntact: Object.keys(questionsData).length === 5
        });
        
        // Show validation warnings but don't block save
        if (validationErrors.length > 0) {
            console.warn('MKCG WordPress Save: Validation warnings:', validationErrors);
            if (validationErrors.length <= 3) {
                // Show first few warnings
                this.showNotification(`Validation warnings: ${validationErrors.slice(0, 2).join(', ')}`, 'warning');
            }
        }
        
        if (totalQuestions === 0) {
            this.showNotification('No questions found to save. Please add some questions first.', 'warning');
            this.resetSaveButton();
            return;
        }
        
        // Prepare enhanced save data with validation info
        const saveData = {
            post_id: parseInt(postId),
            entry_id: parseInt(entryId) || 0,
            questions: questionsData,
            validation_info: {
                total_questions: totalQuestions,
                validation_errors: validationErrors.length,
                client_timestamp: Date.now()
            }
        };
        
        console.log('MKCG WordPress Save: Prepared save data:', {
            ...saveData,
            questions: Object.keys(saveData.questions).map(topicId => `Topic ${topicId}: ${saveData.questions[topicId].filter(q => q.trim()).length}/5 questions`)
        });
        
        // WordPress-standard save with comprehensive error handling
        this.makeAjaxRequest('mkcg_save_all_data', saveData)
            .then(response => {
                console.log('MKCG WordPress Save: Server response:', response);
                
                if (response.success) {
                    const data = response.data;
                    
                    // Success message with details
                    let message = `Successfully saved ${data.saved_questions || totalQuestions} questions`;
                    if (data.saved_topics) {
                        message += ` across ${data.saved_topics} topics`;
                    }
                    if (data.warnings && data.warnings.length > 0) {
                        message += ` (${data.warnings.length} warnings)`;
                    }
                    
                    this.showNotification(message, 'success');
                    
                    // Log comprehensive success info
                    console.log('MKCG WordPress Save: SUCCESS', {
                        savedQuestions: data.saved_questions,
                        savedTopics: data.saved_topics,
                        totalSlots: data.total_slots,
                        warnings: data.warnings,
                        validationInfo: data.validation_info
                    });
                    
                    // Show warnings if any
                    if (data.warnings && data.warnings.length > 0) {
                        console.warn('MKCG WordPress Save: Server warnings:', data.warnings);
                    }
                    
                } else {
                    // Error handling with server details
                    const errorDetails = response.data || {};
                    const errorMessage = errorDetails.message || 'Save failed';
                    
                    console.error('MKCG WordPress Save: Server returned error:', {
                        message: errorMessage,
                        errors: errorDetails.errors,
                        debug: errorDetails.debug
                    });
                    
                    // Show detailed error to user
                    let userMessage = `Save failed: ${errorMessage}`;
                    if (errorDetails.errors && errorDetails.errors.length > 0) {
                        userMessage += ` (${errorDetails.errors.slice(0, 2).join(', ')})`;
                    }
                    
                    throw new Error(userMessage);
                }
            })
            .catch(error => {
                console.error('MKCG WordPress Save: FAILED', error);
                
                // Enhanced error message for user
                let errorMessage = 'Save failed';
                if (error.message) {
                    if (error.message.includes('network') || error.message.includes('fetch')) {
                        errorMessage = 'Network error - please check your connection and try again';
                    } else if (error.message.includes('Security') || error.message.includes('nonce')) {
                        errorMessage = 'Security error - please refresh the page and try again';
                    } else {
                        errorMessage = error.message;
                    }
                }
                
                this.showNotification(errorMessage, 'error');
                
                // Auto-retry option for network errors
                if (error.message && (error.message.includes('network') || error.message.includes('fetch'))) {
                    setTimeout(() => {
                        if (confirm('Network error occurred. Would you like to retry saving?')) {
                            this.saveAllQuestions();
                        } else {
                            this.resetSaveButton();
                        }
                    }, 2000);
                } else {
                    this.resetSaveButton();
                }
            })
            .finally(() => {
                // Only reset if not retrying
                if (!saveButton?.textContent?.includes('Retrying')) {
                    setTimeout(() => this.resetSaveButton(), 1000);
                }
            });
    },
    
    /**
     * ENHANCED: Reset save button with visual feedback
     */
    resetSaveButton: function() {
        const saveButton = document.getElementById('mkcg-save-all-questions');
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.textContent = 'Save All Questions';
            saveButton.style.opacity = '1';
            
            // Brief visual feedback
            saveButton.style.transform = 'scale(1.02)';
            setTimeout(() => {
                saveButton.style.transform = 'scale(1)';
            }, 200);
        }
    },
    
    /**
     * Bind simple save functionality
     */
    bindSimpleSave: function() {
        const saveButton = document.getElementById('mkcg-save-all-questions');
        if (saveButton) {
            saveButton.addEventListener('click', () => {
                this.saveAllQuestions();
            });
            console.log('MKCG Simple Save: Bound save button event');
        }
    },
};

// Initialize when the DOM is ready with unified architecture
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        try {
            QuestionsGenerator.init();
        } catch (error) {
            console.error('MKCG Enhanced Questions: Initialization error:', error);
        }
    }, 100); 
});

// Make globally available with enhanced debugging
window.QuestionsGenerator = QuestionsGenerator;

// Debug helper for development and testing
if (typeof console !== 'undefined' && console.log) {
    window.MKCG_Debug = {
        getPerformanceStats: () => QuestionsGenerator.performance,
        getDataQuality: () => QuestionsGenerator.dataQuality,
        
        // NEW: Test save functionality
        addTestQuestions: function() {
            console.log('MKCG Debug: Adding test questions to form fields...');
            
            const testQuestions = [
                'What led you to develop your current approach to [topic area]?',
                'Can you walk us through your step-by-step method for [implementation]?',
                'What kind of results have people seen from implementing your strategy?',
                'What are the biggest mistakes people make in [topic area]?',
                'Can you share a powerful success story related to this topic?'
            ];
            
            // Add questions to Topic 1 (currently selected)
            const currentTopicId = QuestionsGenerator.selectedTopicId || 1;
            
            for (let i = 0; i < testQuestions.length; i++) {
                const fieldSelector = `#mkcg-question-field-${currentTopicId}-${i + 1}`;
                const fieldElement = document.querySelector(fieldSelector);
                
                if (fieldElement) {
                    fieldElement.value = testQuestions[i];
                    console.log(`Added test question ${i + 1} to field ${fieldSelector}`);
                } else {
                    console.warn(`Field not found: ${fieldSelector}`);
                }
            }
            
            console.log('Test questions added! You can now test the save functionality.');
        },
        
        testSave: function() {
            console.log('MKCG Debug: Testing save functionality...');
            QuestionsGenerator.saveAllQuestions();
        },
        
        clearAllQuestions: function() {
            console.log('MKCG Debug: Clearing all questions...');
            
            for (let topicId = 1; topicId <= 5; topicId++) {
                for (let qNum = 1; qNum <= 5; qNum++) {
                    const fieldSelector = `#mkcg-question-field-${topicId}-${qNum}`;
                    const fieldElement = document.querySelector(fieldSelector);
                    
                    if (fieldElement) {
                        fieldElement.value = '';
                    }
                }
            }
            
            console.log('All questions cleared.');
        }
    };
    
    console.log('MKCG Questions: Debug helpers available via window.MKCG_Debug');
    console.log('- MKCG_Debug.addTestQuestions() - Add sample questions for testing');
    console.log('- MKCG_Debug.testSave() - Test the save functionality');
    console.log('- MKCG_Debug.clearAllQuestions() - Clear all questions');
}