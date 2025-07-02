/**
 * Questions Generator JavaScript - Simplified Version  
 * Handles questions generation with cross-generator communication
 * Version: 1.0.0 - SIMPLIFIED
 */

(function() {
    'use strict';
    
    /**
     * Questions Generator - Main functionality
     */
    const QuestionsGenerator = {
        // Current state
        selectedTopicId: 1,
        selectedTopicText: '',
        currentQuestions: [],
        
        /**
         * Initialize the Questions Generator
         */
        init: function() {
            console.log('🎯 Questions Generator: Initializing...');
            
            // Load existing data
            this.loadExistingData();
            
            // Bind events
            this.bindEvents();
            
            // Setup cross-generator communication
            this.setupEventBusCommunication();
            
            // Update display
            this.updateDisplay();
            
            console.log('✅ Questions Generator: Initialization completed');
        },
        
        /**
         * Setup event bus communication for cross-generator sync
         */
        setupEventBusCommunication: function() {
            if (window.AppEvents) {
                // Listen for topic selection changes from Topics Generator
                window.AppEvents.on('topic:selected', (data) => {
                    this.handleTopicSelectionChange(data);
                });
                
                // Listen for topic updates
                window.AppEvents.on('topic:updated', (data) => {
                    this.handleTopicUpdate(data);
                });
                
                console.log('✅ Questions Generator: Event bus communication setup complete');
            } else {
                console.warn('⚠️ AppEvents not available - running in standalone mode');
            }
        },
        
        /**
         * Handle topic selection changes from Topics Generator
         */
        handleTopicSelectionChange: function(data) {
            console.log('🎯 Questions Generator: Topic selection received', data);
            
            if (data.topicId && data.topicText) {
                this.selectedTopicId = data.topicId;
                this.selectedTopicText = data.topicText;
                
                // Update the selected topic display
                this.updateSelectedTopic(data.topicId, data.topicText);
                
                // Switch to the selected topic's questions
                this.switchToTopic(data.topicId);
            }
        },
        
        /**
         * Handle topic update events
         */
        handleTopicUpdate: function(data) {
            console.log('🎯 Questions Generator: Topic update received', data);
            
            if (data.topicId && data.topicText) {
                // Update the topic card if it exists
                const topicCard = document.querySelector(`[data-topic="${data.topicId}"]`);
                if (topicCard) {
                    const textElement = topicCard.querySelector('.mkcg-topic-text');
                    if (textElement) {
                        textElement.textContent = data.topicText;
                        textElement.classList.remove('mkcg-topic-placeholder');
                    }
                    
                    // Update empty state
                    topicCard.setAttribute('data-empty', 'false');
                    topicCard.classList.remove('mkcg-topic-empty');
                }
                
                // Update questions heading if this is the current topic
                if (data.topicId === this.selectedTopicId) {
                    this.updateQuestionsHeading(data.topicText);
                }
            }
        },
        
        /**
         * Load existing data from PHP/AJAX
         */
        loadExistingData: function() {
            // Check if standardized data is available from PHP
            if (window.MKCG_Topics_Data) {
                console.log('📝 Questions Generator: Loading data from window.MKCG_Topics_Data');
                this.populateFromStandardizedData(window.MKCG_Topics_Data);
            } else {
                console.log('⚠️ Questions Generator: No standardized data available');
            }
        },
        
        /**
         * Populate from standardized data structure
         */
        populateFromStandardizedData: function(data) {
            // Load topics data
            if (data.topics && Object.keys(data.topics).length > 0) {
                console.log('📝 Questions Generator: Loading topics from standardized data');
                
                Object.keys(data.topics).forEach(key => {
                    const topicText = data.topics[key];
                    if (topicText) {
                        this.updateTopicInUI(key, topicText);
                    }
                });
            }
            
            // Load questions data if available
            if (data.questions && Object.keys(data.questions).length > 0) {
                console.log('📝 Questions Generator: Loading questions from standardized data');
                this.currentQuestions = data.questions;
                this.populateQuestionFields();
            }
            
            // Set initial topic selection
            if (data.topics && Object.keys(data.topics).length > 0) {
                const firstTopic = Object.keys(data.topics)[0];
                const firstTopicText = data.topics[firstTopic];
                this.selectedTopicId = parseInt(firstTopic);
                this.selectedTopicText = firstTopicText;
                this.updateSelectedTopic(this.selectedTopicId, firstTopicText);
            }
        },
        
        /**
         * Update topic in UI
         */
        updateTopicInUI: function(topicId, topicText) {
            const topicCard = document.querySelector(`[data-topic="${topicId}"]`);
            if (topicCard) {
                const textElement = topicCard.querySelector('.mkcg-topic-text');
                if (textElement) {
                    textElement.textContent = topicText;
                    textElement.classList.remove('mkcg-topic-placeholder');
                    
                    // Update empty state
                    topicCard.setAttribute('data-empty', 'false');
                    topicCard.classList.remove('mkcg-topic-empty');
                }
            }
        },
        
        /**
         * Populate question fields from current questions data
         */
        populateQuestionFields: function() {
            Object.keys(this.currentQuestions).forEach(topicId => {
                const topicQuestions = this.currentQuestions[topicId];
                Object.keys(topicQuestions).forEach(questionNum => {
                    const questionText = topicQuestions[questionNum];
                    const fieldId = `mkcg-question-field-${topicId}-${questionNum}`;
                    const field = document.getElementById(fieldId);
                    if (field && questionText) {
                        field.value = questionText;
                        console.log(`✅ Populated question ${topicId}-${questionNum}: ${questionText.substring(0, 50)}...`);
                    }
                });
            });
        },
        
        /**
         * Update display after loading data
         */
        updateDisplay: function() {
            // Update questions heading if we have a selected topic
            if (this.selectedTopicText) {
                this.updateQuestionsHeading(this.selectedTopicText);
            }
        },
        
        /**
         * Bind events to DOM elements
         */
        bindEvents: function() {
            // Topic card clicks
            document.querySelectorAll('.mkcg-topic-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    const topicId = parseInt(card.getAttribute('data-topic'));
                    const topicText = card.querySelector('.mkcg-topic-text').textContent;
                    this.selectTopic(topicId, topicText);
                });
            });
            
            // Generate questions button
            const generateBtn = document.getElementById('mkcg-generate-questions');
            if (generateBtn) {
                generateBtn.addEventListener('click', () => {
                    this.generateQuestions();
                });
            }
            
            // Save all questions button  
            const saveBtn = document.getElementById('mkcg-save-all-questions');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    this.saveAllQuestions();
                });
            }
            
            // Auto-save on question field changes
            document.querySelectorAll('[id^="mkcg-question-field-"]').forEach(field => {
                field.addEventListener('blur', () => {
                    this.autoSaveQuestion(field);
                });
            });
            
            console.log('✅ Questions Generator: Events bound successfully');
        },
        
        /**
         * Select a topic
         */
        selectTopic: function(topicId, topicText) {
            console.log(`🎯 Questions Generator: Topic ${topicId} selected: ${topicText}`);
            
            this.selectedTopicId = topicId;
            this.selectedTopicText = topicText;
            
            // Update UI
            this.updateSelectedTopic(topicId, topicText);
            this.switchToTopic(topicId);
            
            // Trigger event for other generators
            if (window.AppEvents) {
                window.AppEvents.trigger('questions:topic-selected', {
                    topicId: topicId,
                    topicText: topicText,
                    source: 'questions-generator',
                    timestamp: Date.now()
                });
            }
        },
        
        /**
         * Update selected topic display
         */
        updateSelectedTopic: function(topicId, topicText) {
            const selectedTopicElement = document.getElementById('mkcg-selected-topic-text');
            if (selectedTopicElement) {
                selectedTopicElement.textContent = topicText || 'Click to add topic';
            }
            
            // Update active topic card
            document.querySelectorAll('.mkcg-topic-card').forEach(card => {
                card.classList.remove('active');
            });
            
            const activeCard = document.querySelector(`[data-topic="${topicId}"]`);
            if (activeCard) {
                activeCard.classList.add('active');
            }
        },
        
        /**
         * Switch to topic's questions
         */
        switchToTopic: function(topicId) {
            // Hide all question sections
            document.querySelectorAll('[id^="mkcg-topic-"][id$="-questions"]').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected topic's questions
            const targetSection = document.getElementById(`mkcg-topic-${topicId}-questions`);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
            
            // Update questions heading
            this.updateQuestionsHeading(this.selectedTopicText);
        },
        
        /**
         * Update questions heading
         */
        updateQuestionsHeading: function(topicText) {
            const heading = document.getElementById('mkcg-questions-heading');
            if (heading) {
                heading.textContent = `Interview Questions for "${topicText || 'Add topic above'}"`;  
            }
        },
        
        /**
         * Generate questions for selected topic
         */
        generateQuestions: function() {
            if (!this.selectedTopicText) {
                this.showNotification('Please select a topic first', 'warning');
                return;
            }
            
            console.log('🎯 Questions Generator: Generating questions for:', this.selectedTopicText);
            
            // Show loading
            this.showLoading();
            
            // Make AJAX request
            this.makeAjaxRequest('mkcg_generate_questions', {
                topic: this.selectedTopicText,
                entry_id: document.getElementById('mkcg-entry-id')?.value || 0
            })
            .then(data => {
                console.log('✅ Questions generated:', data);
                this.hideLoading();
                
                if (data.questions && data.questions.length > 0) {
                    this.displayGeneratedQuestions(data.questions);
                    this.showNotification(`Generated ${data.questions.length} questions for: ${data.topic}`, 'success');
                } else {
                    this.showNotification('No questions generated', 'warning');
                }
            })
            .catch(error => {
                console.error('❌ Question generation failed:', error);
                this.hideLoading();
                this.showNotification('Failed to generate questions: ' + error.message, 'error');
            });
        },
        
        /**
         * Display generated questions
         */
        displayGeneratedQuestions: function(questions) {
            // Populate the question fields for current topic
            questions.forEach((question, index) => {
                const questionNum = index + 1;
                const fieldId = `mkcg-question-field-${this.selectedTopicId}-${questionNum}`;
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = question;
                }
            });
        },
        
        /**
         * Save all questions
         */
        saveAllQuestions: function() {
            console.log('💾 Questions Generator: Saving all questions');
            
            const entryId = document.getElementById('mkcg-entry-id')?.value;
            if (!entryId || entryId === '0') {
                this.showNotification('No entry ID found. Please refresh the page.', 'error');
                return;
            }
            
            // Collect all question data
            const questionsData = {};
            let totalQuestions = 0;
            
            for (let topic = 1; topic <= 5; topic++) {
                const topicQuestions = {};
                for (let q = 1; q <= 5; q++) {
                    const field = document.getElementById(`mkcg-question-field-${topic}-${q}`);
                    if (field && field.value.trim()) {
                        topicQuestions[q] = field.value.trim();
                        totalQuestions++;
                    }
                }
                if (Object.keys(topicQuestions).length > 0) {
                    questionsData[topic] = topicQuestions;
                }
            }
            
            if (totalQuestions === 0) {
                this.showNotification('No questions to save. Please add some questions first.', 'warning');
                return;
            }
            
            // Show saving state
            const saveBtn = document.getElementById('mkcg-save-all-questions');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
            }
            
            // Make AJAX request
            this.makeAjaxRequest('mkcg_save_questions', {
                entry_id: entryId,
                questions: questionsData
            })
            .then(data => {
                console.log('✅ Questions saved:', data);
                this.showNotification(`Successfully saved ${totalQuestions} questions!`, 'success');
                
                // Trigger event for cross-generator communication
                if (window.AppEvents) {
                    window.AppEvents.trigger('questions:saved', {
                        questionsData: questionsData,
                        totalCount: totalQuestions,
                        timestamp: Date.now()
                    });
                }
            })
            .catch(error => {
                console.error('❌ Questions save failed:', error);
                this.showNotification('Failed to save questions: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset save button
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save All Questions';
                }
            });
        },
        
        /**
         * Auto-save individual question
         */
        autoSaveQuestion: function(field) {
            const entryId = document.getElementById('mkcg-entry-id')?.value;
            if (!entryId || entryId === '0' || !field.value.trim()) {
                return;
            }
            
            // Extract topic and question number from field ID
            const match = field.id.match(/mkcg-question-field-(\\d+)-(\\d+)/);
            if (!match) {
                return;
            }
            
            const topicId = match[1];
            const questionNum = match[2];
            const metaKey = `mkcg_question_${topicId}_${questionNum}`;
            
            console.log(`💾 Auto-saving question ${topicId}-${questionNum}`);
            
            // Debounce the save
            clearTimeout(this.autoSaveTimers?.[field.id]);
            if (!this.autoSaveTimers) this.autoSaveTimers = {};
            
            this.autoSaveTimers[field.id] = setTimeout(() => {
                this.makeAjaxRequest('mkcg_save_single_question', {
                    entry_id: entryId,
                    meta_key: metaKey,
                    question: field.value.trim()
                })
                .then(() => {
                    console.log(`✅ Auto-saved question ${topicId}-${questionNum}`);
                    this.showFieldSaved(field);
                })
                .catch(error => {
                    console.error(`❌ Auto-save failed for question ${topicId}-${questionNum}:`, error);
                });
            }, 1000);
        },
        
        /**
         * Show field saved indicator
         */
        showFieldSaved: function(field) {
            // Add temporary saved styling
            field.style.borderColor = '#27ae60';
            field.style.boxShadow = '0 0 5px rgba(39, 174, 96, 0.3)';
            
            // Reset after 2 seconds
            setTimeout(() => {
                field.style.borderColor = '';
                field.style.boxShadow = '';
            }, 2000);
        },
        
        /**
         * Show loading state
         */
        showLoading: function() {
            const loading = document.getElementById('mkcg-loading');
            if (loading) {
                loading.style.display = 'block';
            }
        },
        
        /**
         * Hide loading state
         */
        hideLoading: function() {
            const loading = document.getElementById('mkcg-loading');
            if (loading) {
                loading.style.display = 'none';
            }
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type = 'info') {
            if (window.showNotification) {
                window.showNotification(message, type);
            } else {
                console.log(`📢 ${type.toUpperCase()}: ${message}`);
            }
        },
        
        /**
         * Make AJAX request
         */
        makeAjaxRequest: function(action, data) {
            // Use global AJAX function if available
            if (window.makeAjaxRequest) {
                return window.makeAjaxRequest(action, data);
            }
            
            // Fallback to basic fetch
            const requestData = new URLSearchParams();
            requestData.append('action', action);
            requestData.append('nonce', document.getElementById('mkcg-questions-nonce')?.value || '');
            
            Object.keys(data).forEach(key => {
                if (typeof data[key] === 'object') {
                    requestData.append(key, JSON.stringify(data[key]));
                } else {
                    requestData.append(key, data[key]);
                }
            });
            
            return fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: requestData.toString()
            })
            .then(response => response.json())
            .then(result => {
                if (result.success === false) {
                    throw new Error(result.data?.message || 'Request failed');
                }
                return result.data || result;
            });
        }
    };
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 Questions Generator: DOM Ready');
        QuestionsGenerator.init();
    });
    
    // Make globally available
    window.QuestionsGenerator = QuestionsGenerator;
    
    console.log('✅ Questions Generator script loaded successfully');
    
})();
