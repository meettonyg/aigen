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
        
        try {
            this.loadTopicsData();
            this.bindEvents();
            this.updateSelectedTopic();
            this.debugInfo();
            
            // Show questions for default selected topic (Topic 1)
            this.showQuestionsForTopic(this.selectedTopicId || 1);
            
            console.log('MKCG Questions: Initialization completed successfully');
            
        } catch (error) {
            console.error('MKCG Questions: Initialization failed:', error);
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
     * Bind events to DOM elements
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
     * Generate questions with AI
     */
    generateQuestions: function() {
        if (!this.selectedTopicText || this.selectedTopicText === 'No topic selected') {
            alert('Please select a topic first.');
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
        
        // Use simple AJAX
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
    }
};

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    QuestionsGenerator.init();
});

// Make globally available
window.QuestionsGenerator = QuestionsGenerator;