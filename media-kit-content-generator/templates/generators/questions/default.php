<?php
/**
 * Questions Generator Template - BEM Methodology
 * Default template for generating interview questions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';

// Try to get entry from URL parameters
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    
    // Use the Formidable service to resolve entry ID
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
        }
    }
}
?>

<div class="generator questions-generator">
    <div class="generator__title">Interview Questions Generator</div>
    
    <!-- Section 1: Topic Input -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Podcast Topic</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="questions-topic" class="field__label">Enter the podcast topic you want to generate questions for:</label>
                <textarea id="questions-topic" 
                          name="topic" 
                          class="field__textarea"
                          rows="3" 
                          placeholder="e.g., How to Scale Your Business Without Burning Out"
                          required></textarea>
                <p class="field__help">
                    Enter a specific topic or select one from your generated topics list. The questions will be tailored to this topic.
                </p>
            </div>
            
            <div class="field">
                <label for="questions-topic-number" class="field__label">Topic Number (Optional)</label>
                <input type="number" 
                       id="questions-topic-number" 
                       name="topic_number" 
                       class="field__input"
                       min="1" 
                       max="10"
                       placeholder="1">
                <p class="field__help">
                    If this is part of a series, enter the topic number.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Generation Controls -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generate Questions</div>
        </div>
        <div class="section__content">
            <p class="field__description">
                Generate 10 compelling podcast interview questions based on your topic. 
                Questions will include origin, process, results, mistakes, and transformation-focused questions.
            </p>
            
            <div class="button-group">
                <button type="button" id="generate-questions-btn" class="button button--ai">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="button__icon">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Generate Questions with AI
                </button>
            </div>
        </div>
    </div>
    
    <!-- Results Section (Initially Hidden) -->
    <div id="questions-results" class="section" style="display: none;">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generated Questions</div>
        </div>
        <div class="section__content">
            <div id="questions-list" class="results">
                <!-- Questions will be populated here by JavaScript -->
            </div>
            
            <div class="button-group">
                <button type="button" id="copy-all-questions-btn" class="button button--copy">
                    Copy All Questions
                </button>
                <button type="button" id="regenerate-questions-btn" class="button button--ai">
                    Regenerate Questions
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="questions-loading-overlay" class="loading" style="display: none;">
        <div class="loading__content">
            <div class="loading__spinner"></div>
            <div class="loading__message">Generating interview questions...</div>
        </div>
    </div>
    
    <!-- Hidden fields for data -->
    <input type="hidden" id="questions-entry-id" value="<?php echo esc_attr($entry_id); ?>">
    <input type="hidden" id="questions-entry-key" value="<?php echo esc_attr($entry_key); ?>">
    <input type="hidden" id="questions-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Questions Generator
    const QuestionsGenerator = {
        
        init: function() {
            this.bindEvents();
            this.loadSelectedTopic();
        },
        
        bindEvents: function() {
            const generateBtn = document.getElementById('generate-questions-btn');
            const regenerateBtn = document.getElementById('regenerate-questions-btn');
            const copyAllBtn = document.getElementById('copy-all-questions-btn');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generateQuestions());
            }
            
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', () => this.generateQuestions());
            }
            
            if (copyAllBtn) {
                copyAllBtn.addEventListener('click', () => this.copyAllQuestions());
            }
        },
        
        loadSelectedTopic: function() {
            // Check if a topic was selected from the Topics generator
            if (typeof localStorage !== 'undefined') {
                const selectedTopic = localStorage.getItem('selected_topic');
                const topicNumber = localStorage.getItem('topic_number');
                
                if (selectedTopic) {
                    const topicField = document.getElementById('questions-topic');
                    const numberField = document.getElementById('questions-topic-number');
                    
                    if (topicField) {
                        topicField.value = selectedTopic;
                    }
                    
                    if (numberField && topicNumber) {
                        numberField.value = topicNumber;
                    }
                    
                    // Clear from localStorage
                    localStorage.removeItem('selected_topic');
                    localStorage.removeItem('topic_number');
                }
            }
        },
        
        generateQuestions: function() {
            const topic = document.getElementById('questions-topic')?.value;
            const topicNumber = document.getElementById('questions-topic-number')?.value;
            const entryId = document.getElementById('questions-entry-id')?.value;
            const entryKey = document.getElementById('questions-entry-key')?.value;
            
            if (!topic || topic.trim() === '') {
                alert('Please enter a podcast topic first.');
                return;
            }
            
            // Show loading
            this.showLoading('Generating compelling interview questions...');
            
            // Prepare data
            const data = {
                topic: topic,
                topic_number: topicNumber || 1,
                entry_id: entryId || '',
                entry_key: entryKey || ''
            };
            
            // Make AJAX request using FormUtils
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.wp.makeAjaxRequest('generate_questions', data, {
                    onSuccess: (response) => {
                        this.hideLoading();
                        this.displayQuestions(response.content.questions);
                    },
                    onError: (error) => {
                        this.hideLoading();
                        alert('Error generating questions: ' + error);
                    }
                });
            } else {
                // Fallback for legacy compatibility
                this.generateQuestionsLegacy(data);
            }
        },
        
        generateQuestionsLegacy: function(data) {
            // Legacy AJAX call for backwards compatibility
            const postData = new URLSearchParams();
            postData.append('action', 'generate_interview_questions');
            postData.append('security', document.getElementById('questions-nonce')?.value || '');
            postData.append('entry_id', data.entry_id);
            postData.append('topic', data.topic);
            postData.append('topic_number', data.topic_number);
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: postData.toString()
            })
            .then(response => response.json())
            .then(response => {
                this.hideLoading();
                if (response.success) {
                    this.displayQuestions(response.data.questions);
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to generate questions'));
                }
            })
            .catch(error => {
                this.hideLoading();
                alert('Network error: ' + error.message);
            });
        },
        
        displayQuestions: function(questions) {
            const resultsSection = document.getElementById('questions-results');
            const questionsList = document.getElementById('questions-list');
            
            if (!questionsList || !questions || questions.length === 0) {
                alert('No questions were generated. Please try again.');
                return;
            }
            
            // Clear previous results
            questionsList.innerHTML = '';
            
            // Add questions to the list
            questions.forEach((question, index) => {
                const questionElement = document.createElement('div');
                questionElement.className = 'results__item';
                questionElement.innerHTML = `
                    <span class="results__number">${index + 1}.</span>
                    <span class="results__text">${this.escapeHtml(question)}</span>
                    <button type="button" class="button button--copy" onclick="QuestionsGenerator.copyQuestion('${this.escapeHtml(question)}')">
                        Copy
                    </button>
                `;
                questionsList.appendChild(questionElement);
            });
            
            // Show results section
            resultsSection.style.display = 'block';
            
            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        
        copyQuestion: function(question) {
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.ui.copyToClipboard(question);
            } else {
                this.copyToClipboard(question);
            }
        },
        
        copyAllQuestions: function() {
            const questionElements = document.querySelectorAll('.results__text');
            if (questionElements.length === 0) {
                alert('No questions to copy.');
                return;
            }
            
            let allQuestions = '';
            questionElements.forEach((element, index) => {
                allQuestions += `${index + 1}. ${element.textContent}\n`;
            });
            
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.ui.copyToClipboard(allQuestions);
            } else {
                this.copyToClipboard(allQuestions);
            }
        },
        
        copyToClipboard: function(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(() => alert('Copied to clipboard!'))
                    .catch(() => this.fallbackCopy(text));
            } else {
                this.fallbackCopy(text);
            }
        },
        
        fallbackCopy: function(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Copied to clipboard!');
            } catch (err) {
                alert('Unable to copy. Please copy manually.');
            }
            document.body.removeChild(textarea);
        },
        
        showLoading: function(message = 'Loading...') {
            const overlay = document.getElementById('questions-loading-overlay');
            if (overlay) {
                const messageEl = overlay.querySelector('.loading__message');
                if (messageEl) {
                    messageEl.textContent = message;
                }
                overlay.style.display = 'flex';
            }
        },
        
        hideLoading: function() {
            const overlay = document.getElementById('questions-loading-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when page loads
    QuestionsGenerator.init();
    
    // Make globally available
    window.QuestionsGenerator = QuestionsGenerator;
});
</script>