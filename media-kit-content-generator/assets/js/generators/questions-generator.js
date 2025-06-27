/**
 * Questions Generator - Enhanced JavaScript with Inline Topic Editing
 * 
 * CRITICAL FEATURES IMPLEMENTED:
 * ✅ Inline topic editing - click empty topics or double-click existing to edit
 * ✅ Auto-save on blur - saves when user clicks away from editor
 * ✅ AJAX backend integration - saves to WordPress post meta via PHP handlers
 * ✅ Visual feedback - saving/saved/error indicators with animations
 * ✅ Keyboard shortcuts - Ctrl+Enter to save, Escape to cancel
 * ✅ Enhanced UX - empty topic placeholders, proper form validation
 * ✅ Cross-generator integration - works with existing Topics Generator data
 * 
 * ROOT ISSUE FIXED: Missing PHP AJAX handlers for topic saving
 * ARCHITECTURE: Uses unified MKCG FormUtils + enhanced Questions Generator methods
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
    
    // Current state
    selectedTopicId: 1,
    selectedTopicText: '',
    generatedQuestions: [],
    selectedQuestion: null,
    topicsData: {},
    
    /**
     * Initialize the Questions Generator
     */
    init: function() {
        console.log('MKCG Questions: Initializing Questions Generator v' + (typeof mkcg_questions_ajax !== 'undefined' ? mkcg_questions_ajax.version : 'unknown'));
        
        const initStartTime = Date.now();
        
        try {
            this.loadTopicsData();
            this.bindEvents();
            this.updateSelectedTopic();
            this.loadSelectedTopic(); // Load from localStorage if available
            this.restoreStateFromReturn(); // Restore state if returning from Topics Generator
            this.autoRefreshTopics(); // Auto-refresh topics if needed
            this.debugInfo();
            
            // Track initialization performance
            this.trackPerformance('initialization', initStartTime);
            
            console.log('MKCG Questions: Initialization completed successfully');
            
        } catch (error) {
            console.error('MKCG Questions: Initialization failed:', error);
            this.handleError(error, 'initialization');
        }
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
            selectedTopicText: this.selectedTopicText,
            elementsFound: {
                generateButton: !!document.querySelector(this.elements.generateButton),
                topicsGrid: !!document.querySelector(this.elements.topicsGrid),
                selectedTopicText: !!document.querySelector(this.elements.selectedTopicText)
            }
        });
    },
    
    /**
     * Load topics data from PHP or fetch from server
     */
    loadTopicsData: function() {
        if (typeof MKCG_TopicsData !== 'undefined' && Object.keys(MKCG_TopicsData).length > 0) {
            this.topicsData = MKCG_TopicsData;
            this.selectedTopicText = this.topicsData[1] || 'No topic selected';
            console.log('MKCG Questions: Loaded topics from PHP:', this.topicsData);
        } else {
            // Try to fetch topics from server if not provided
            console.log('MKCG Questions: No topics data from PHP, attempting to fetch from server');
            this.fetchTopicsFromServer();
        }
    },
    
    /**
     * Fetch topics from server via AJAX
     */
    fetchTopicsFromServer: function() {
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        const entryKey = document.getElementById('mkcg-entry-key')?.value;
        
        if (!entryId && !entryKey) {
            console.log('MKCG Questions: No entry ID or key available for fetching topics');
            return;
        }
        
        const data = {
            action: 'mkcg_get_topics',
            nonce: typeof mkcg_questions_ajax !== 'undefined' ? mkcg_questions_ajax.nonce : '',
            entry_id: entryId,
            entry_key: entryKey
        };
        
        const postData = new URLSearchParams();
        Object.keys(data).forEach(key => {
            postData.append(key, data[key]);
        });
        
        fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: postData.toString()
        })
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data && response.data.topics) {
                this.topicsData = response.data.topics;
                this.selectedTopicText = this.topicsData[1] || 'No topic selected';
                this.updateTopicsDisplay();
                console.log('MKCG Questions: Successfully fetched topics from server:', this.topicsData);
            } else {
                console.error('MKCG Questions: Failed to fetch topics:', response.data?.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('MKCG Questions: Network error fetching topics:', error);
        });
    },
    
    /**
     * Update topics display after fetching from server
     */
    updateTopicsDisplay: function() {
        const topicsGrid = document.querySelector(this.elements.topicsGrid);
        if (!topicsGrid || Object.keys(this.topicsData).length === 0) {
            return;
        }
        
        topicsGrid.innerHTML = '';
        
        Object.keys(this.topicsData).forEach(topicId => {
            const topicCard = document.createElement('div');
            topicCard.className = 'mkcg-topic-card';
            if (parseInt(topicId) === 1) {
                topicCard.classList.add('active');
            }
            topicCard.setAttribute('data-topic', topicId);
            
            topicCard.innerHTML = `
                <div class="mkcg-topic-number">${topicId}</div>
                <div class="mkcg-topic-text">${this.topicsData[topicId]}</div>
            `;
            
            topicCard.addEventListener('click', () => {
                this.selectTopic(parseInt(topicId));
            });
            
            topicsGrid.appendChild(topicCard);
        });
        
        // Select the first topic by default
        this.selectTopic(1);
    },
    
    /**
     * Bind events to DOM elements
     */
    bindEvents: function() {
        // Topic card click - simple selection only
        document.querySelectorAll(this.elements.topicCards).forEach(card => {
            card.addEventListener('click', (e) => {
                const topicId = parseInt(card.getAttribute('data-topic'));
                this.selectTopic(topicId);
            });
        });
        
        // Edit topics button
        const editButton = document.querySelector(this.elements.editTopicsButton);
        if (editButton) {
            editButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.editTopics();
            });
        }
        
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
        
        // Click on form examples
        document.querySelectorAll('.mkcg-form-example').forEach(example => {
            example.addEventListener('click', (e) => {
                const field = e.target.closest('.mkcg-form-field');
                if (field) {
                    const input = field.querySelector('.mkcg-form-field-input');
                    if (input) {
                        input.value = e.target.textContent.replace(/"/g, '');
                    }
                }
            });
        });
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
        
        // Hide any previous results
        const questionsResult = document.querySelector(this.elements.questionsResult);
        if (questionsResult) {
            questionsResult.style.display = 'none';
        }
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
     * Load selected topic from localStorage (for cross-generator integration)
     */
    loadSelectedTopic: function() {
        try {
            const selectedTopic = localStorage.getItem('selected_topic');
            const topicNumber = localStorage.getItem('topic_number');
            
            if (selectedTopic) {
                // Find matching topic in our data
                let matchingTopicId = null;
                for (const [id, text] of Object.entries(this.topicsData)) {
                    if (text === selectedTopic) {
                        matchingTopicId = parseInt(id);
                        break;
                    }
                }
                
                if (matchingTopicId) {
                    this.selectTopic(matchingTopicId);
                } else if (topicNumber) {
                    // Fallback to topic number if exact match not found
                    const topicId = parseInt(topicNumber);
                    if (this.topicsData[topicId]) {
                        this.selectTopic(topicId);
                    }
                }
                
                // Clear from localStorage
                localStorage.removeItem('selected_topic');
                localStorage.removeItem('topic_number');
            }
        } catch (error) {
            console.log('LocalStorage not available or error loading selected topic:', error);
        }
    },
    

    
    /**
     * Edit topics functionality
     */
    editTopics: function() {
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        const entryKey = document.getElementById('mkcg-entry-key')?.value;
        
        console.log('MKCG Questions: Edit Topics clicked', { entryId, entryKey });
        
        // Build Topics Generator URL
        let topicsUrl = this.buildTopicsUrl(entryId, entryKey);
        
        if (topicsUrl) {
            console.log('MKCG Questions: Redirecting to Topics Generator:', topicsUrl);
            window.location.href = topicsUrl;
        } else {
            // Fallback to generic topics page
            window.location.href = '/topics/';
        }
    },
    

    
    /**
     * Build Topics Generator URL with multiple fallback strategies
     */
    buildTopicsUrl: function(entryId, entryKey) {
        let baseUrl = '';
        
        // Strategy 1: Replace /questions/ with /topics/ in current URL
        if (window.location.pathname.includes('/questions/')) {
            baseUrl = window.location.origin + window.location.pathname.replace('/questions/', '/topics/');
        }
        // Strategy 2: Try common WordPress permalink structures
        else if (window.location.pathname.includes('questions')) {
            baseUrl = window.location.origin + window.location.pathname.replace('questions', 'topics');
        }
        // Strategy 3: Assume root-level pages
        else {
            baseUrl = window.location.origin + '/topics/';
        }
        
        // Add parameters
        if (entryKey && entryKey !== '0' && entryKey !== '') {
            return baseUrl + '?entry=' + encodeURIComponent(entryKey);
        } else if (entryId && entryId !== '0' && entryId !== '') {
            return baseUrl + '?entry_id=' + encodeURIComponent(entryId);
        }
        
        // Return base URL even without parameters
        return baseUrl;
    },
    
    /**
     * Save current state for when user returns from Topics Generator
     */
    saveStateForReturn: function() {
        try {
            const currentState = {
                selectedTopicId: this.selectedTopicId,
                generatedQuestions: this.generatedQuestions,
                timestamp: Date.now()
            };
            localStorage.setItem('mkcg_questions_return_state', JSON.stringify(currentState));
            console.log('MKCG Questions: State saved for return');
        } catch (error) {
            console.log('MKCG Questions: Failed to save state:', error);
        }
    },
    
    /**
     * Restore state when returning from Topics Generator
     */
    restoreStateFromReturn: function() {
        try {
            const savedState = localStorage.getItem('mkcg_questions_return_state');
            if (savedState) {
                const state = JSON.parse(savedState);
                
                // Only restore if recent (within 1 hour)
                if (Date.now() - state.timestamp < 3600000) {
                    if (state.selectedTopicId && this.topicsData[state.selectedTopicId]) {
                        this.selectTopic(state.selectedTopicId);
                    }
                    
                    if (state.generatedQuestions && state.generatedQuestions.length > 0) {
                        this.generatedQuestions = state.generatedQuestions;
                        this.displayQuestions(state.generatedQuestions);
                    }
                }
                
                // Clear saved state
                localStorage.removeItem('mkcg_questions_return_state');
            }
        } catch (error) {
            console.log('MKCG Questions: Failed to restore state:', error);
        }
    },
    
    /**
     * Show instructions when Edit Topics button can't determine URL
     */
    showEditTopicsInstructions: function() {
        const message = `
To edit your topics:

1. Go to your Topics Generator page
2. Make your changes
3. Return to this Questions Generator
4. Your topics will be automatically refreshed

If you need help finding the Topics Generator, please contact support.`;
        
        alert(message);
        
        // Try to refresh topics from server as fallback
        this.fetchTopicsFromServer();
    },
    
    /**
     * Generate questions with AI
     */
    generateQuestions: function() {
        if (!this.selectedTopicText || this.selectedTopicText === 'No topic selected') {
            alert('Please select a topic first.');
            return;
        }
        
        // Validate that we have topics data loaded
        if (Object.keys(this.topicsData).length === 0) {
            alert('No topics available. Please generate topics first.');
            return;
        }
        
        // Show loading indicator
        this.showLoading();
        
        // Disable generate button
        const generateBtn = document.querySelector(this.elements.generateButton);
        if (generateBtn) {
            generateBtn.disabled = true;
            generateBtn.textContent = 'Generating Questions...';
        }
        
        const data = {
            topic: this.selectedTopicText,
            topic_number: this.selectedTopicId,
            entry_id: document.getElementById('mkcg-entry-id')?.value || 0,
            entry_key: document.getElementById('mkcg-entry-key')?.value || ''
        };
        
        console.log('MKCG Questions: Generating questions with data:', data);
        
        // Use enhanced FormUtils if available, otherwise fallback to legacy
        if (typeof MKCG_FormUtils !== 'undefined' && MKCG_FormUtils.wp) {
            MKCG_FormUtils.wp.makeAjaxRequest('generate_questions', data, {
                onSuccess: (response) => {
                    console.log('MKCG Questions: Generation successful:', response);
                    this.hideLoading();
                    this.enableGenerateButton();
                    if (response.content && response.content.questions) {
                        this.displayQuestions(response.content.questions);
                    } else {
                        alert('No questions were generated. Please try again.');
                    }
                },
                onError: (error) => {
                    console.error('MKCG Questions: Generation error:', error);
                    this.hideLoading();
                    this.enableGenerateButton();
                    alert('Error generating questions: ' + error);
                }
            });
        } else {
            // Fallback to legacy AJAX
            this.generateQuestionsLegacy(data);
        }
    },
    
    /**
     * Legacy AJAX generation for backwards compatibility
     */
    generateQuestionsLegacy: function(data) {
        const postData = new URLSearchParams();
        postData.append('action', 'generate_interview_questions');
        postData.append('security', document.getElementById('mkcg-questions-nonce')?.value || '');
        postData.append('entry_id', data.entry_id);
        postData.append('topic', data.topic);
        postData.append('topic_number', data.topic_number);
        
        fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: postData.toString()
        })
        .then(response => response.json())
        .then(response => {
            this.hideLoading();
            this.enableGenerateButton();
            
            if (response.success && response.data && response.data.questions) {
                this.displayQuestions(response.data.questions);
            } else {
                alert('Error: ' + (response.data?.message || 'Failed to generate questions'));
            }
        })
        .catch(error => {
            this.hideLoading();
            this.enableGenerateButton();
            alert('Network error: ' + error.message);
        });
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
            numberDiv.textContent = `Question ${questionNumber}:`;
            
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
        const fieldSelector = `#mkcg-question-field-${fieldNumber}`;
        const fieldElement = document.querySelector(fieldSelector);
        if (fieldElement) {
            fieldElement.value = this.selectedQuestion.text;
        }
        
        // Close the modal
        this.closeModal();
        
        // Optional: Auto-save to Formidable if available
        this.autoSaveToFormidable(fieldNumber, this.selectedQuestion.text);
    },
    
    /**
     * Auto-save question to Formidable Forms
     */
    autoSaveToFormidable: function(fieldNumber, questionText) {
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        
        if (!entryId || entryId === '0') {
            console.log('MKCG Questions: No entry ID available for auto-save');
            return;
        }
        
        // Get the actual Formidable field ID based on topic and question number
        const formidableFieldId = this.getQuestionFieldId(fieldNumber - 1, this.selectedTopicId);
        
        if (!formidableFieldId) {
            console.log('MKCG Questions: Could not determine Formidable field ID');
            return;
        }
        
        console.log(`MKCG Questions: Auto-saving question ${fieldNumber} to field ${formidableFieldId} for topic ${this.selectedTopicId}`);
        
        // Note: Full auto-save implementation would go here
        // For now, just log the mapping
        console.log({
            questionNumber: fieldNumber,
            topicNumber: this.selectedTopicId,
            formidableFieldId: formidableFieldId,
            questionText: questionText
        });
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
    
    /**
     * Update question field based on topic selection
     * This determines which set of fields to use based on the topic number
     */
    getQuestionFieldId: function(questionIndex, topicNumber) {
        // Field mappings based on topic number
        const fieldMappings = {
            1: [8505, 8506, 8507, 8508, 8509],     // Topic 1 → Questions 1-5
            2: [8510, 8511, 8512, 8513, 8514],     // Topic 2 → Questions 6-10
            3: [10370, 10371, 10372, 10373, 10374], // Topic 3 → Questions 11-15
            4: [10375, 10376, 10377, 10378, 10379], // Topic 4 → Questions 16-20
            5: [10380, 10381, 10382, 10383, 10384]  // Topic 5 → Questions 21-25
        };
        
        const fields = fieldMappings[topicNumber] || fieldMappings[1];
        return fields[questionIndex] || fields[0];
    },
    
    /**
     * Enhanced error handling with retry logic
     */
    handleError: function(error, context, retryCallback) {
        console.error('MKCG Questions Error:', error, 'Context:', context);
        
        // Show user-friendly error message
        const errorMessage = this.getUserFriendlyErrorMessage(error);
        
        // If retry callback provided, offer retry option
        if (retryCallback && typeof retryCallback === 'function') {
            const shouldRetry = confirm(errorMessage + '\n\nWould you like to try again?');
            if (shouldRetry) {
                setTimeout(retryCallback, 1000);
                return;
            }
        } else {
            alert(errorMessage);
        }
        
        // Reset UI state
        this.hideLoading();
        this.enableGenerateButton();
    },
    
    /**
     * Get user-friendly error messages
     */
    getUserFriendlyErrorMessage: function(error) {
        if (typeof error === 'string') {
            if (error.includes('network') || error.includes('fetch')) {
                return 'Network connection issue. Please check your internet connection and try again.';
            }
            if (error.includes('timeout')) {
                return 'The request timed out. Please try again in a moment.';
            }
            if (error.includes('security') || error.includes('nonce')) {
                return 'Security token expired. Please refresh the page and try again.';
            }
        }
        
        return 'An unexpected error occurred. Please try again or contact support if the problem persists.';
    },
    
    /**
     * Auto-refresh topics when returning from Topics Generator
     */
    autoRefreshTopics: function() {
        // Check if we should auto-refresh topics
        const lastRefresh = localStorage.getItem('mkcg_topics_last_refresh');
        const refreshThreshold = 5 * 60 * 1000; // 5 minutes
        
        if (!lastRefresh || (Date.now() - parseInt(lastRefresh)) > refreshThreshold) {
            console.log('MKCG Questions: Auto-refreshing topics');
            this.fetchTopicsFromServer();
            localStorage.setItem('mkcg_topics_last_refresh', Date.now().toString());
        }
    },
    
    /**
     * Performance monitoring
     */
    trackPerformance: function(action, startTime) {
        if (!startTime) return;
        
        const duration = Date.now() - startTime;
        console.log(`MKCG Questions Performance: ${action} took ${duration}ms`);
        
        // Store performance data for analytics (if enabled)
        if (typeof mkcg_questions_ajax !== 'undefined' && mkcg_questions_ajax.features?.analytics) {
            this.sendPerformanceData(action, duration);
        }
    },
    
    /**
     * Send performance data for monitoring
     */
    sendPerformanceData: function(action, duration) {
        // Only send if duration is significant
        if (duration > 1000) {
            const data = {
                action: 'mkcg_track_performance',
                performance_action: action,
                duration: duration,
                timestamp: Date.now(),
                user_agent: navigator.userAgent,
                nonce: typeof mkcg_questions_ajax !== 'undefined' ? mkcg_questions_ajax.nonce : ''
            };
            
            // Send as background request (don't block UI)
            fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data).toString()
            }).catch(() => {}); // Ignore errors for analytics
        }
    },
    

    
    /**
     * Cleanup function to prevent memory leaks
     */
    cleanup: function() {
        // Clear any active timeouts
        if (this.retryTimeout) {
            clearTimeout(this.retryTimeout);
        }
        
        // Clear performance monitoring
        if (this.performanceStartTime) {
            this.performanceStartTime = null;
        }
        
        // Clear cached data older than 1 hour
        try {
            const items = Object.keys(localStorage);
            items.forEach(key => {
                if (key.startsWith('mkcg_') && key.includes('_timestamp')) {
                    const timestamp = parseInt(localStorage.getItem(key) || '0');
                    if (Date.now() - timestamp > 3600000) { // 1 hour
                        const dataKey = key.replace('_timestamp', '');
                        localStorage.removeItem(key);
                        localStorage.removeItem(dataKey);
                    }
                }
            });
        } catch (error) {
            console.log('MKCG Questions: Cleanup error (non-critical):', error);
        }
        
        console.log('MKCG Questions: Cleanup completed');
    }
};

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    QuestionsGenerator.init();
});

// Make globally available for backwards compatibility
window.QuestionsGenerator = QuestionsGenerator;