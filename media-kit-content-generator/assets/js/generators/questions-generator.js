/**
 * Questions Generator JavaScript - Unified BEM Architecture
 * Handles questions generation with cross-generator communication
 * Version: 2.0.1 - Updated for BEM Architecture
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
            console.log('🎯 Questions Generator: Initializing with BEM architecture...');
            
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
                    const textElement = topicCard.querySelector('.questions-generator__topic-text');
                    if (textElement) {
                        textElement.textContent = data.topicText;
                        textElement.classList.remove('questions-generator__topic-text--placeholder');
                        
                        // Remove placeholder span if it exists
                        const placeholder = textElement.querySelector('.questions-generator__placeholder-text');
                        if (placeholder) {
                            placeholder.remove();
                        }
                    }
                    
                    // Update empty state
                    topicCard.setAttribute('data-empty', 'false');
                    topicCard.classList.remove('questions-generator__topic-card--empty');
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
            if (window.MKCG_Questions_Data) {
                console.log('📝 Questions Generator: Loading data from window.MKCG_Questions_Data');
                this.populateFromStandardizedData(window.MKCG_Questions_Data);
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
                if (firstTopicText && firstTopicText.trim()) {
                    this.selectedTopicId = parseInt(firstTopic);
                    this.selectedTopicText = firstTopicText;
                    this.updateSelectedTopic(this.selectedTopicId, firstTopicText);
                    this.switchToTopic(this.selectedTopicId);
                }
            }
        },
        
        /**
         * Update topic in UI
         */
        updateTopicInUI: function(topicId, topicText) {
            const topicCard = document.querySelector(`[data-topic="${topicId}"]`);
            if (topicCard) {
                const textElement = topicCard.querySelector('.questions-generator__topic-text');
                if (textElement) {
                    textElement.textContent = topicText;
                    textElement.classList.remove('questions-generator__topic-text--placeholder');
                    
                    // Remove placeholder span if it exists
                    const placeholder = textElement.querySelector('.questions-generator__placeholder-text');
                    if (placeholder) {
                        placeholder.remove();
                    }
                    
                    // Update empty state
                    topicCard.setAttribute('data-empty', 'false');
                    topicCard.classList.remove('questions-generator__topic-card--empty');
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
                    const fieldId = `questions-generator-question-field-${topicId}-${questionNum}`;
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
            document.querySelectorAll('.questions-generator__topic-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    const topicId = parseInt(card.getAttribute('data-topic'));
                    const topicTextElement = card.querySelector('.questions-generator__topic-text');
                    let topicText = '';
                    
                    if (topicTextElement) {
                        // Check if it's empty/placeholder
                        const isPlaceholder = topicTextElement.classList.contains('questions-generator__topic-text--placeholder');
                        if (!isPlaceholder) {
                            const placeholderSpan = topicTextElement.querySelector('.questions-generator__placeholder-text');
                            if (placeholderSpan) {
                                // This is an empty topic with placeholder text
                                topicText = '';
                            } else {
                                topicText = topicTextElement.textContent.trim();
                            }
                        }
                    }
                    
                    this.selectTopic(topicId, topicText);
                });
            });
            
            // Generate questions button
            const generateBtn = document.getElementById('questions-generator-generate-questions');
            if (generateBtn) {
                generateBtn.addEventListener('click', () => {
                    this.generateQuestions();
                });
            }
            
            // Save all questions button  
            const saveBtn = document.getElementById('questions-generator-save-all-questions');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    this.saveAllQuestions();
                });
            }
            
            // Auto-save on question field changes
            document.querySelectorAll('[id^="questions-generator-question-field-"]').forEach(field => {
                field.addEventListener('blur', () => {
                    this.autoSaveQuestion(field);
                });
            });
            
            // ROOT FIX: Edit Topics button handler
            const editTopicsBtn = document.getElementById('questions-generator-edit-topics');
            if (editTopicsBtn) {
                editTopicsBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleEditTopicsClick();
                });
                console.log('✅ Edit Topics button handler bound');
            }
            
            // ROOT FIX: Individual topic edit icon handlers
            document.querySelectorAll('.questions-generator__topic-edit-icon').forEach(icon => {
                icon.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent topic card click
                    this.handleTopicEditClick(icon);
                });
            });
            console.log('✅ Topic edit icon handlers bound');
            
            console.log('✅ Questions Generator: Events bound successfully');
        },
        
        /**
         * Select a topic
         */
        selectTopic: function(topicId, topicText) {
            console.log(`🎯 Questions Generator: Topic ${topicId} selected: "${topicText}"`);
            
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
            const selectedTopicElement = document.getElementById('questions-generator-selected-topic-text');
            if (selectedTopicElement) {
                selectedTopicElement.textContent = topicText || 'Click to add topic';
            }
            
            // Update active topic card
            document.querySelectorAll('.questions-generator__topic-card').forEach(card => {
                card.classList.remove('questions-generator__topic-card--active');
            });
            
            const activeCard = document.querySelector(`[data-topic="${topicId}"]`);
            if (activeCard) {
                activeCard.classList.add('questions-generator__topic-card--active');
            }
        },
        
        /**
         * Switch to topic's questions
         */
        switchToTopic: function(topicId) {
            // Hide all question sections
            document.querySelectorAll('[id^="questions-generator-topic-"][id$="-questions"]').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected topic's questions
            const targetSection = document.getElementById(`questions-generator-topic-${topicId}-questions`);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
            
            // Update questions heading
            this.updateQuestionsHeading(this.selectedTopicText);
            
            console.log(`✅ Switched to topic ${topicId} questions section`);
        },
        
        /**
        * Update questions heading
        */
        updateQuestionsHeading: function(topicText) {
        const heading = document.getElementById('questions-generator-questions-heading');
        if (heading) {
        heading.textContent = `Interview Questions for "${topicText || 'Add topic above'}"`;
        }
        },
        
        /**
         * ROOT FIX: Handle Edit Topics button click
         */
        handleEditTopicsClick: function() {
            console.log('🎨 Edit Topics button clicked');
            
            // Check if we have access to Topics Generator or Authority Hook Builder
            if (window.AuthorityHookBuilder) {
                console.log('🔄 Opening Authority Hook Builder for topics editing');
                window.AuthorityHookBuilder.show();
            } else {
                // Fallback: Navigate to Topics Generator if on separate page
                const topicsUrl = this.getTopicsGeneratorUrl();
                if (topicsUrl) {
                    window.open(topicsUrl, '_blank');
                } else {
                    this.showNotification('Topics editing functionality not available', 'warning');
                }
            }
        },
        
        /**
         * ROOT FIX: Handle individual topic edit icon click
         */
        handleTopicEditClick: function(icon) {
            console.log('🎨 Topic edit icon clicked', icon);
            
            // Find the parent topic card
            const topicCard = icon.closest('.questions-generator__topic-card');
            if (!topicCard) {
                console.error('❌ Could not find parent topic card');
                return;
            }
            
            const topicId = parseInt(topicCard.getAttribute('data-topic'));
            const topicTextElement = topicCard.querySelector('.questions-generator__topic-text');
            
            if (!topicTextElement) {
                console.error('❌ Could not find topic text element');
                return;
            }
            
            // Create inline editing interface
            this.enableInlineTopicEditing(topicCard, topicId, topicTextElement);
        },
        
        /**
         * ROOT FIX: Enable inline editing for individual topic
         */
        enableInlineTopicEditing: function(topicCard, topicId, topicTextElement) {
            console.log('🔍 DEBUGGING: Starting topic edit for ID:', topicId);
            
            // ROOT FIX: Check if already in edit mode and extract value from existing input
            const existingInput = topicTextElement.querySelector('input.questions-generator__topic-edit-input');
            if (existingInput) {
                console.log('🔴 Already in edit mode - extracting value from existing input:', existingInput.value);
                // Already in edit mode, just focus the existing input
                existingInput.focus();
                existingInput.select();
                return;
            }
            
            console.log('🔍 DEBUGGING: topicTextElement:', topicTextElement);
            console.log('🔍 DEBUGGING: topicTextElement.innerHTML:', topicTextElement.innerHTML);
            console.log('🔍 DEBUGGING: topicTextElement.textContent:', topicTextElement.textContent);
            console.log('🔍 DEBUGGING: topicTextElement classes:', Array.from(topicTextElement.classList));
            
            // ROOT FIX: Better text extraction logic to preserve existing values
            let currentText = '';
            
            // Get current text - handle both placeholder and real content
            const isPlaceholder = topicTextElement.classList.contains('questions-generator__topic-text--placeholder');
            const placeholderSpan = topicTextElement.querySelector('.questions-generator__placeholder-text');
            
            console.log('🔍 DEBUGGING: isPlaceholder:', isPlaceholder);
            console.log('🔍 DEBUGGING: placeholderSpan:', placeholderSpan);
            
            if (placeholderSpan) {
                // This is a placeholder - use empty text
                currentText = '';
                console.log('🎨 Found placeholder span - using empty text');
            } else if (isPlaceholder) {
                // Has placeholder class but no span - might be old format
                currentText = '';
                console.log('🎨 Has placeholder class - using empty text');
            } else {
                // Real content - get the actual text
                currentText = topicTextElement.textContent.trim();
                console.log('🎨 Extracting real content:', currentText);
            }
            
            // ROOT FIX: MORE AGGRESSIVE fallback - always try to get text if element has content
            if (!currentText || currentText === '') {
                const rawText = topicTextElement.textContent || topicTextElement.innerText || '';
                console.log('🔍 DEBUGGING: Raw text attempt:', rawText);
                
                if (rawText && rawText.trim()) {
                    const trimmedText = rawText.trim();
                    // Only skip if it's obviously placeholder text
                    if (!trimmedText.toLowerCase().includes('click to add') && 
                        !trimmedText.toLowerCase().includes('add topic') &&
                        !trimmedText.toLowerCase().includes('placeholder')) {
                        currentText = trimmedText;
                        console.log('🎨 Aggressive fallback extracted text:', currentText);
                    }
                }
            }
            
            console.log('🔍 DEBUGGING: Final text for input field:', JSON.stringify(currentText));
            
            // Create input field
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentText; // This should now have the correct text
            input.className = 'questions-generator__topic-edit-input';
            input.style.cssText = `
                width: 100%;
                padding: 4px 8px;
                border: 2px solid var(--mkcg-primary, #1a9bdc);
                border-radius: 4px;
                font-size: inherit;
                font-family: inherit;
                background: white;
                z-index: 1000;
            `;
            
            console.log('🔍 DEBUGGING: Created input with value:', JSON.stringify(input.value));
            
            // ROOT FIX: Store original content more carefully
            const originalContent = {
                innerHTML: topicTextElement.innerHTML,
                textContent: topicTextElement.textContent,
                classList: Array.from(topicTextElement.classList)
            };
            
            console.log('🔍 DEBUGGING: Stored original content:', originalContent);
            
            // Replace text element with input
            topicTextElement.innerHTML = '';
            topicTextElement.appendChild(input);
            
            // Focus and select all
            input.focus();
            input.select();
            
            console.log('🔍 DEBUGGING: Input field focused and selected');
            
            // ROOT FIX: Prevent multiple blur handlers by using a flag
            let saveInProgress = false;
            
            // Handle save/cancel
            const saveEdit = () => {
                if (saveInProgress) return;
                saveInProgress = true;
                
                const newText = input.value.trim();
                console.log('💾 Saving with text:', JSON.stringify(newText));
                if (newText) {
                    this.saveTopicEdit(topicId, newText, topicTextElement, originalContent);
                } else {
                    this.cancelTopicEdit(topicTextElement, originalContent);
                }
            };
            
            const cancelEdit = () => {
                if (saveInProgress) return;
                saveInProgress = true;
                
                console.log('❌ Canceling edit');
                this.cancelTopicEdit(topicTextElement, originalContent);
            };
            
            // Bind events
            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveEdit();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelEdit();
                }
            });
            
            console.log('✅ Inline editing enabled for topic', topicId, 'with final text:', JSON.stringify(currentText));
        },
        
        /**
         * ROOT FIX: Save topic edit
         */
        saveTopicEdit: function(topicId, newText, topicTextElement, originalContent) {
            console.log('💾 Saving topic edit:', topicId, newText);
            
            // Update UI immediately
            topicTextElement.innerHTML = newText;
            topicTextElement.classList.remove('questions-generator__topic-text--placeholder');
            
            // Update topic card state
            const topicCard = topicTextElement.closest('.questions-generator__topic-card');
            if (topicCard) {
                topicCard.setAttribute('data-empty', 'false');
                topicCard.classList.remove('questions-generator__topic-card--empty');
            }
            
            // Update internal state
            if (topicId === this.selectedTopicId) {
                this.selectedTopicText = newText;
                this.updateQuestionsHeading(newText);
            }
            
            // Save to backend
            this.saveTopicToBackend(topicId, newText)
                .then(() => {
                    this.showNotification('Topic updated successfully', 'success');
                    
                    // Trigger cross-generator event
                    if (window.AppEvents) {
                        window.AppEvents.trigger('topic:updated', {
                            topicId: topicId,
                            topicText: newText,
                            source: 'questions-generator'
                        });
                    }
                })
                .catch(error => {
                    console.error('❌ Failed to save topic:', error);
                    // Revert UI on error
                    topicTextElement.innerHTML = originalContent.innerHTML;
                    topicTextElement.className = originalContent.classList.join(' ');
                    this.showNotification('Failed to save topic: ' + error.message, 'error');
                });
        },
        
        /**
         * ROOT FIX: Cancel topic edit
         */
        cancelTopicEdit: function(topicTextElement, originalContent) {
            topicTextElement.innerHTML = originalContent.innerHTML;
            topicTextElement.className = originalContent.classList.join(' ');
            console.log('❌ Topic edit cancelled - restored original content');
        },
        
        /**
         * ROOT FIX: Save topic to backend
         */
        saveTopicToBackend: function(topicId, topicText) {
            // Get post_id
            let postId = 0;
            if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.postId) {
                postId = window.MKCG_Questions_Data.postId;
            }
            
            if (!postId) {
                return Promise.reject(new Error('No post ID found'));
            }
            
            // Save using Topics Generator endpoint
            return this.makeAjaxRequest('mkcg_save_topic_field', {
                post_id: postId,
                field_name: `topic_${topicId}`,
                field_value: topicText
            });
        },
        
        /**
         * ROOT FIX: Get Topics Generator URL for fallback navigation
         */
        getTopicsGeneratorUrl: function() {
            // Try to find Topics Generator page URL from data or configuration
            if (window.MKCG_Config && window.MKCG_Config.topicsGeneratorUrl) {
                return window.MKCG_Config.topicsGeneratorUrl;
            }
            
            // Fallback: construct URL with current post_id
            let postId = 0;
            if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.postId) {
                postId = window.MKCG_Questions_Data.postId;
            }
            
            if (postId) {
                // Assuming Topics Generator is at a known URL pattern
                return `/topics-generator/?post_id=${postId}`;
            }
            
            return null;
        },
        
        /**
         * Generate questions for selected topic
         */
        generateQuestions: function() {
            if (!this.selectedTopicText || this.selectedTopicText.trim() === '') {
                this.showNotification('Please select a topic with content first', 'warning');
                return;
            }
            
            console.log('🎯 Questions Generator: Generating questions for:', this.selectedTopicText);
            
            // Show loading
            this.showLoading();
            
            // ROOT FIX: Get post_id from template data
            let postId = 0;
            if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.postId) {
                postId = window.MKCG_Questions_Data.postId;
            }
            
            // Make AJAX request
            this.makeAjaxRequest('mkcg_generate_questions', {
                topic: this.selectedTopicText,
                post_id: postId  // ← Fixed: use post_id instead of entry_id
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
                const fieldId = `questions-generator-question-field-${this.selectedTopicId}-${questionNum}`;
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
            
            // ROOT FIX: Get post_id from template data or URL parameter
            let postId = 0;
            
            // Try to get post_id from standardized data first
            if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.postId) {
                postId = window.MKCG_Questions_Data.postId;
            }
            
            // Fallback: try hidden field
            if (!postId) {
                const postIdField = document.getElementById('questions-generator-post-id');
                if (postIdField && postIdField.value) {
                    postId = parseInt(postIdField.value);
                }
            }
            
            // Final fallback: try URL parameter
            if (!postId) {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('post_id')) {
                    postId = parseInt(urlParams.get('post_id'));
                }
            }
            
            if (!postId || postId === 0) {
                this.showNotification('No post ID found. Please ensure you access this page with ?post_id=XXXXX parameter.', 'error');
                return;
            }
            
            console.log('💾 Using post_id:', postId);
            
            // ROOT FIX: Use EXACTLY same format as Topics Generator (flat object, not nested)
            const questionsData = {};
            let totalQuestions = 0;
            
            for (let topic = 1; topic <= 5; topic++) {
                for (let q = 1; q <= 5; q++) {
                    const field = document.getElementById(`questions-generator-question-field-${topic}-${q}`);
                    if (field && field.value.trim()) {
                        // Use flat key format like Topics Generator: "question_1_1", "question_1_2"
                        const key = `question_${topic}_${q}`;
                        questionsData[key] = field.value.trim();
                        totalQuestions++;
                        console.log(`🔍 Found question ${key}: ${field.value.trim().substring(0, 30)}...`);
                    }
                }
            }
            
            console.log('🔍 QUESTIONS DEBUG: Final questionsData object (FLAT like Topics):', questionsData);
            console.log('🔍 QUESTIONS DEBUG: Total questions collected:', totalQuestions);
            console.log('🔍 QUESTIONS DEBUG: Sample keys:', Object.keys(questionsData).slice(0, 3));
            
            if (totalQuestions === 0) {
                this.showNotification('No questions to save. Please add some questions first.', 'warning');
                return;
            }
            
            // Show saving state
            const saveBtn = document.getElementById('questions-generator-save-all-questions');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
            }
            
            // ROOT FIX: Make AJAX request with post_id instead of entry_id
            this.makeAjaxRequest('mkcg_save_questions', {
                post_id: postId,  // ← Fixed: send post_id instead of entry_id
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
            // ROOT FIX: Get post_id from template data
            let postId = 0;
            
            if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.postId) {
                postId = window.MKCG_Questions_Data.postId;
            }
            
            if (!postId || postId === 0 || !field.value.trim()) {
                return;
            }
            
            // Extract topic and question number from field ID
            const match = field.id.match(/questions-generator-question-field-(\\d+)-(\\d+)/);
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
                    post_id: postId,  // ← Fixed: use post_id instead of entry_id
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
            const loading = document.getElementById('questions-generator-loading');
            if (loading) {
                loading.classList.remove('generator__loading--hidden');
            }
        },
        
        /**
         * Hide loading state
         */
        hideLoading: function() {
            const loading = document.getElementById('questions-generator-loading');
            if (loading) {
                loading.classList.add('generator__loading--hidden');
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
            requestData.append('nonce', document.getElementById('questions-generator-questions-nonce')?.value || '');
            
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
        // CRITICAL FIX: Only initialize if this generator's DOM elements exist
        const questionsContainer = document.querySelector('.questions-generator');
        if (!questionsContainer) {
            console.log('🎯 Questions Generator: DOM elements not found - skipping initialization');
            return;
        }
        
        console.log('🎯 Questions Generator: DOM Ready - BEM Architecture');
        QuestionsGenerator.init();
    });
    
    // Make globally available
    window.QuestionsGenerator = QuestionsGenerator;
    
    console.log('✅ Questions Generator script loaded successfully - BEM Architecture');
    
})();
