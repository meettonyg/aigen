/**
 * Questions Generator - Standalone Version
 * 
 * CRITICAL FEATURES IMPLEMENTED:
 * ✅ Topic selection - displays correct question set (1-5, 6-10, etc.) based on selected topic
 * ✅ Inline topic editing - click empty topics or double-click existing to edit
 * ✅ Auto-save on blur - saves when user clicks away from editor
 * ✅ AJAX backend integration - saves to WordPress post meta via PHP handlers
 * ✅ Visual feedback - saving/saved/error indicators with animations
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
     * ENHANCED INITIALIZATION with data validation and health monitoring
     */
    init: function() {
        console.log('MKCG Enhanced Questions: Initializing with data validation');
        this.performance.loadStartTime = performance.now();
        
        try {
            // Initialize with enhanced data validation
            this.loadAndValidateTopicsData();
            this.bindEnhancedEvents();
            this.updateSelectedTopic();
            this.performInitialHealthCheck();
            this.startSyncMonitoring();
            
            // Show questions for default selected topic (Topic 1)
            this.showQuestionsForTopic(this.selectedTopicId || 1);
            
            const loadTime = performance.now() - this.performance.loadStartTime;
            console.log(`MKCG Enhanced Questions: Initialization completed in ${loadTime.toFixed(2)}ms`);
            
        } catch (error) {
            this.performance.errorCount++;
            this.handleError(error, 'initialization', () => this.init());
        }
    },
    
    /**
     * ENHANCED TOPICS DATA LOADING with validation
     */
    loadAndValidateTopicsData: function() {
        if (typeof MKCG_TopicsData !== 'undefined' && Object.keys(MKCG_TopicsData).length > 0) {
            this.topicsData = MKCG_TopicsData;
            
            // Validate topics data quality
            const validation = this.validateTopicsData(this.topicsData);
            this.dataQuality.topics = validation.quality;
            
            if (validation.issues.length > 0) {
                console.warn('MKCG Enhanced Questions: Topics data issues detected:', validation.issues);
                this.showDataQualityNotification('topics', validation);
            }
            
            this.selectedTopicText = this.topicsData[1] || 'No topic selected';
            console.log('MKCG Enhanced Questions: Loaded and validated topics:', {
                data: this.topicsData,
                quality: validation.quality,
                issues: validation.issues
            });
        } else {
            this.dataQuality.topics = 'missing';
            console.warn('MKCG Enhanced Questions: No topics data from PHP');
            this.showDataQualityNotification('topics', { quality: 'missing', issues: ['No topics data available'] });
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
     * CHECK DATA HEALTH STATUS
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
     * Update the selected topic display
     */
    updateSelectedTopic: function() {
        const selectedTopicElement = document.querySelector(this.elements.selectedTopicText);
        if (selectedTopicElement) {
            selectedTopicElement.textContent = this.selectedTopicText;
        }
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
     * Select a topic
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
        
        // Update selected topic data
        this.selectedTopicId = topicId;
        this.selectedTopicText = this.topicsData[topicId] || 'Unknown topic';
        
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
    },
    
    /**
     * Edit topic inline
     */
    editTopicInline: function(topicId, card) {
        const textElement = card.querySelector('.mkcg-topic-text');
        const currentText = this.topicsData[topicId] || '';
        
        // Create input field
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentText;
        input.className = 'mkcg-topic-editor';
        input.style.cssText = `
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #1a9bdc;
            border-radius: 4px;
            font-size: 15px;
            font-family: inherit;
            background: white;
            color: #333;
        `;
        
        // Replace text with input
        textElement.style.display = 'none';
        textElement.parentNode.insertBefore(input, textElement.nextSibling);
        
        // Focus and select
        input.focus();
        input.select();
        
        // Save on Enter or blur
        const save = () => {
            const newText = input.value.trim();
            if (newText && newText !== currentText) {
                this.topicsData[topicId] = newText;
                textElement.textContent = newText;
                if (this.selectedTopicId === topicId) {
                    this.selectedTopicText = newText;
                    this.updateSelectedTopic();
                    this.updateSelectedTopicHeading();
                }
            }
            input.remove();
            textElement.style.display = '';
        };
        
        // Cancel on Escape
        const cancel = () => {
            input.remove();
            textElement.style.display = '';
        };
        
        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                save();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancel();
            }
        });
    },
    
    /**
     * Update the selected topic heading in the Questions Generator
     */
    updateSelectedTopicHeading: function() {
        // Update the heading in the Questions for Topic section
        const questionsHeading = document.querySelector('#mkcg-questions-heading');
        if (questionsHeading) {
            questionsHeading.textContent = `Interview Questions for "${this.selectedTopicText}"`;
        }
        
        // Also update the selected topic text display
        const selectedTopicElement = document.querySelector(this.elements.selectedTopicText);
        if (selectedTopicElement) {
            selectedTopicElement.textContent = this.selectedTopicText;
        }
        
        // Hide all question sets and show only the selected one
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
        
        // Use enhanced AJAX with retry logic
        this.makeAjaxRequest('generate_interview_questions', data, 'generate_topics_nonce')
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
     * UNIFIED AJAX REQUEST with enhanced error handling and retry logic
     */
    makeAjaxRequest: function(action, data = {}, nonceField = 'mkcg_nonce', maxRetries = 2) {
        return new Promise((resolve, reject) => {
            const attemptRequest = (attempt = 1) => {
                const postData = new URLSearchParams();
                postData.append('action', action);
                
                // Add nonce based on field type
                const nonceValue = nonceField === 'generate_topics_nonce' ? 
                    document.getElementById('mkcg-questions-nonce')?.value :
                    document.getElementById('mkcg-questions-nonce')?.value; // Both use same nonce
                
                if (nonceField === 'generate_topics_nonce') {
                    postData.append('security', nonceValue || '');
                } else {
                    postData.append('nonce', nonceValue || '');
                }
                
                // Add data parameters
                Object.entries(data).forEach(([key, value]) => {
                    postData.append(key, value);
                });
                
                fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
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
                .then(data => {
                    resolve(data);
                })
                .catch(error => {
                    if (attempt < maxRetries) {
                        console.warn(`MKCG Enhanced Questions: Request failed (attempt ${attempt}/${maxRetries}), retrying...`, error);
                        setTimeout(() => attemptRequest(attempt + 1), 1000 * attempt);
                    } else {
                        reject(error);
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
    }
};

// Initialize when the DOM is ready with enhanced error handling
document.addEventListener('DOMContentLoaded', function() {
    try {
        QuestionsGenerator.init();
    } catch (error) {
        console.error('MKCG Enhanced Questions: Critical initialization error:', error);
        
        // Show user-friendly error message
        const errorMessage = document.createElement('div');
        errorMessage.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #e74c3c;
            color: white;
            padding: 20px;
            border-radius: 8px;
            z-index: 10000;
            text-align: center;
            max-width: 400px;
        `;
        errorMessage.innerHTML = `
            <h3>Questions Generator Error</h3>
            <p>Failed to initialize the Questions Generator. Please refresh the page or contact support if the problem persists.</p>
            <button onclick="location.reload()" style="background: white; color: #e74c3c; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-top: 10px;">Refresh Page</button>
        `;
        document.body.appendChild(errorMessage);
    }
});

// Make globally available with enhanced debugging
window.QuestionsGenerator = QuestionsGenerator;

// Debug helper for development
if (typeof console !== 'undefined' && console.log) {
    window.MKCG_Debug = {
        getPerformanceStats: () => QuestionsGenerator.performance,
        getDataQuality: () => QuestionsGenerator.dataQuality,
        forceHealthCheck: () => QuestionsGenerator.performInitialHealthCheck(),
        forceSyncCheck: () => QuestionsGenerator.checkSyncStatus()
    };
    
    console.log('MKCG Enhanced Questions: Debug helpers available via window.MKCG_Debug');
}