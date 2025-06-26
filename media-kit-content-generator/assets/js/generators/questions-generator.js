/**
 * Questions Generator - Enhanced JavaScript
 * Unified implementation with topic selection and enhanced FormUtils integration
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
        console.log('MKCG Questions: Initializing Questions Generator');
        this.loadTopicsData();
        this.bindEvents();
        this.updateSelectedTopic();
        this.loadSelectedTopic(); // Load from localStorage if available
        this.debugInfo();
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
        // Topic selection
        document.querySelectorAll(this.elements.topicCards).forEach(card => {
            card.addEventListener('click', () => {
                const topicId = parseInt(card.getAttribute('data-topic'));
                this.selectTopic(topicId);
            });
        });
        
        // Edit topics button
        const editButton = document.querySelector(this.elements.editTopicsButton);
        if (editButton) {
            editButton.addEventListener('click', () => {
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
     * Edit topics (redirect to topics generator or open editor)
     */
    editTopics: function() {
        // Try to redirect to topics generator page
        const entryId = document.getElementById('mkcg-entry-id')?.value;
        const entryKey = document.getElementById('mkcg-entry-key')?.value;
        
        let topicsUrl = window.location.origin + window.location.pathname.replace('/questions/', '/topics/');
        
        if (entryKey) {
            topicsUrl += '?entry=' + encodeURIComponent(entryKey);
        } else if (entryId) {
            topicsUrl += '?entry_id=' + encodeURIComponent(entryId);
        }
        
        // Open in new tab
        window.open(topicsUrl, '_blank');
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
    }
};

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    QuestionsGenerator.init();
});

// Make globally available for backwards compatibility
window.QuestionsGenerator = QuestionsGenerator;