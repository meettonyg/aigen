<?php
/**
 * Topics Generator Template - BEM Methodology
 * Default template for generating interview topics
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

<div class="generator topics-generator">
    <div class="generator__title">Interview Topics Generator</div>
    
    <!-- Section 1: Authority Hook -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Build Your Authority Hook</div>
        </div>
        <div class="section__content">
            <?php
            // Include the shared Authority Hook component
            $current_values = [];
            include MKCG_PLUGIN_PATH . 'templates/shared/authority-hook-component.php';
            ?>
        </div>
    </div>
    
    <!-- Section 2: Target Audience (Optional) -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Target Audience (Optional)</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="topics-audience" class="field__label">Specific Target Audience</label>
                <input type="text" 
                       id="topics-audience" 
                       name="audience" 
                       placeholder="e.g., SaaS startup founders, real estate investors"
                       class="field__input">
                <p class="field__help">
                    Leave blank for general topics, or specify a particular audience to focus the topics on their specific challenges and goals.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Generation Controls -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generate Topics</div>
        </div>
        <div class="section__content">
            <p class="field__description">
                Generate 5 compelling podcast interview topics based on your authority hook and target audience. 
                Topics will be tailored to highlight your expertise and attract podcast hosts.
            </p>
            
            <div class="button-group">
                <button type="button" id="generate-topics-btn" class="button button--ai">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="button__icon">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Generate Topics with AI
                </button>
            </div>
        </div>
    </div>
    
    <!-- Results Section (Initially Hidden) -->
    <div id="topics-results" class="section" style="display: none;">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generated Topics</div>
        </div>
        <div class="section__content">
            <div id="topics-list" class="results">
                <!-- Topics will be populated here by JavaScript -->
            </div>
            
            <div class="button-group">
                <button type="button" id="copy-all-topics-btn" class="button button--copy">
                    Copy All Topics
                </button>
                <button type="button" id="regenerate-topics-btn" class="button button--ai">
                    Regenerate Topics
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="topics-loading-overlay" class="loading" style="display: none;">
        <div class="loading__content">
            <div class="loading__spinner"></div>
            <div class="loading__message">Generating interview topics...</div>
        </div>
    </div>
    
    <!-- Hidden fields for data -->
    <input type="hidden" id="topics-entry-id" value="<?php echo esc_attr($entry_id); ?>">
    <input type="hidden" id="topics-entry-key" value="<?php echo esc_attr($entry_key); ?>">
    <input type="hidden" id="topics-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Topics Generator
    const TopicsGenerator = {
        
        init: function() {
            this.bindEvents();
            this.loadExistingData();
        },
        
        bindEvents: function() {
            const generateBtn = document.getElementById('generate-topics-btn');
            const regenerateBtn = document.getElementById('regenerate-topics-btn');
            const copyAllBtn = document.getElementById('copy-all-topics-btn');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generateTopics());
            }
            
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', () => this.generateTopics());
            }
            
            if (copyAllBtn) {
                copyAllBtn.addEventListener('click', () => this.copyAllTopics());
            }
        },
        
        generateTopics: function() {
            const authorityHook = document.getElementById('mkcg-authority-hook')?.value;
            const audience = document.getElementById('topics-audience')?.value;
            const entryId = document.getElementById('topics-entry-id')?.value;
            const entryKey = document.getElementById('topics-entry-key')?.value;
            
            if (!authorityHook || authorityHook.trim() === '') {
                alert('Please complete your Authority Hook first.');
                return;
            }
            
            // Show loading
            this.showLoading('Generating compelling interview topics...');
            
            // Prepare data
            const data = {
                authority_hook: authorityHook,
                audience: audience || '',
                entry_id: entryId || '',
                entry_key: entryKey || ''
            };
            
            // Make AJAX request using FormUtils
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.wp.makeAjaxRequest('generate_topics', data, {
                    onSuccess: (response) => {
                        this.hideLoading();
                        this.displayTopics(response.content.topics);
                    },
                    onError: (error) => {
                        this.hideLoading();
                        alert('Error generating topics: ' + error);
                    }
                });
            } else {
                // Fallback for legacy compatibility
                this.generateTopicsLegacy(data);
            }
        },
        
        generateTopicsLegacy: function(data) {
            // Legacy AJAX call for backwards compatibility
            const postData = new URLSearchParams();
            postData.append('action', 'generate_interview_topics');
            postData.append('security', document.getElementById('topics-nonce')?.value || '');
            postData.append('entry_id', data.entry_id);
            postData.append('audience', data.audience);
            
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
                    this.displayTopics(response.data.topics);
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to generate topics'));
                }
            })
            .catch(error => {
                this.hideLoading();
                alert('Network error: ' + error.message);
            });
        },
        
        displayTopics: function(topics) {
            const resultsSection = document.getElementById('topics-results');
            const topicsList = document.getElementById('topics-list');
            
            if (!topicsList || !topics || topics.length === 0) {
                alert('No topics were generated. Please try again.');
                return;
            }
            
            // Clear previous results
            topicsList.innerHTML = '';
            
            // Add topics to the list
            topics.forEach((topic, index) => {
                const topicElement = document.createElement('div');
                topicElement.className = 'results__item';
                topicElement.innerHTML = `
                    <span class="results__number">${index + 1}.</span>
                    <span class="results__text">${this.escapeHtml(topic)}</span>
                    <button type="button" class="button button--use" onclick="TopicsGenerator.useTopic('${this.escapeHtml(topic)}', ${index + 1})">
                        Use Topic
                    </button>
                `;
                topicsList.appendChild(topicElement);
            });
            
            // Show results section
            resultsSection.style.display = 'block';
            
            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        
        useTopic: function(topic, number) {
            // Store the selected topic and redirect to questions generator
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('selected_topic', topic);
                localStorage.setItem('topic_number', number);
            }
            
            alert('Topic selected! You can now generate interview questions for this topic.');
        },
        
        copyAllTopics: function() {
            const topicElements = document.querySelectorAll('.results__text');
            if (topicElements.length === 0) {
                alert('No topics to copy.');
                return;
            }
            
            let allTopics = '';
            topicElements.forEach((element, index) => {
                allTopics += `${index + 1}. ${element.textContent}\n`;
            });
            
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.ui.copyToClipboard(allTopics);
            } else {
                // Fallback
                this.copyToClipboard(allTopics);
            }
        },
        
        copyToClipboard: function(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(() => alert('Topics copied to clipboard!'))
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
                alert('Topics copied to clipboard!');
            } catch (err) {
                alert('Unable to copy. Please copy manually.');
            }
            document.body.removeChild(textarea);
        },
        
        showLoading: function(message = 'Loading...') {
            const overlay = document.getElementById('topics-loading-overlay');
            if (overlay) {
                const messageEl = overlay.querySelector('.loading__message');
                if (messageEl) {
                    messageEl.textContent = message;
                }
                overlay.style.display = 'flex';
            }
        },
        
        hideLoading: function() {
            const overlay = document.getElementById('topics-loading-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        },
        
        loadExistingData: function() {
            // Load any existing data from the entry
            const entryId = document.getElementById('topics-entry-id')?.value;
            
            if (entryId && typeof MKCG_FormUtils !== 'undefined') {
                // Could load existing authority hook data here
            }
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when page loads
    TopicsGenerator.init();
    
    // Make globally available
    window.TopicsGenerator = TopicsGenerator;
});
</script>