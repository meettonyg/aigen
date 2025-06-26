<?php
/**
 * Questions Generator Template - Enhanced with Inline Topic Editing
 * Matches original Topics Generator design with editing functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';
$debug_info = [];

// Try to get entry from URL parameters
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    $debug_info[] = 'Entry key from URL: ' . $entry_key;
    
    // Use the Formidable service to resolve entry ID
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
            $debug_info[] = 'Resolved entry ID: ' . $entry_id;
        } else {
            $debug_info[] = 'Failed to resolve entry: ' . $entry_data['message'];
        }
    } else {
        $debug_info[] = 'Formidable service not available';
    }
} elseif (isset($_GET['entry_id'])) {
    $entry_id = intval($_GET['entry_id']);
    $debug_info[] = 'Entry ID from URL: ' . $entry_id;
}

// Get available topics from custom post meta
$available_topics = [];
$existing_questions = [];
$post_id = null;
$topics_debug = [];

if ($entry_id && isset($formidable_service)) {
    $topics_debug[] = 'Getting topics from custom post meta';
    $topics_debug[] = 'Entry ID: ' . $entry_id;
    
    // Get the post ID associated with this entry
    $post_id = $formidable_service->get_post_id_from_entry($entry_id);
    
    if ($post_id) {
        $topics_debug[] = 'Found associated post ID: ' . $post_id;
        
        // Get topics from custom post meta
        $available_topics = $formidable_service->get_topics_from_post($post_id);
        
        if (!empty($available_topics)) {
            $topics_debug[] = 'SUCCESS: Found ' . count($available_topics) . ' topics in post meta';
        } else {
            $topics_debug[] = 'No topics found in post meta fields';
        }
        
        // Get existing questions organized by topic
        $existing_questions = $formidable_service->get_all_questions_by_topic($post_id);
    } else {
        $topics_debug[] = 'No associated post found for entry ' . $entry_id;
    }
}

// Always ensure we have 5 topic slots
$all_topics = [];
for ($i = 1; $i <= 5; $i++) {
    if (isset($available_topics[$i]) && !empty($available_topics[$i])) {
        $all_topics[$i] = $available_topics[$i];
    } else {
        $all_topics[$i] = '';
    }
}

// Debug output for development
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- DEBUG INFO: ' . implode(' | ', $debug_info) . ' -->';
    echo '<!-- TOPICS DEBUG: ' . implode(' | ', $topics_debug) . ' -->';
}

// Error if no topics found - redirect to topics generator
if (empty($available_topics)) {
    $topics_url = '';
    if ($entry_key) {
        $topics_url = site_url('/topics/?entry=' . urlencode($entry_key));
    } elseif ($entry_id) {
        $topics_url = site_url('/topics/?entry_id=' . $entry_id);
    }
    
    echo '<div class="mkcg-error-notice">';
    echo '<h3>No Topics Found</h3>';
    echo '<p>Please generate your interview topics first before creating questions.</p>';
    
    if ($topics_url) {
        echo '<a href="' . esc_url($topics_url) . '" class="mkcg-button">Generate Topics First</a>';
    }
    echo '</div>';
    return;
}
?>

<div class="mkcg-questions-generator-wrapper">
    <div class="mkcg-container">
        <div class="mkcg-onboard-header">
            <h1 class="mkcg-tool-title">Create Your Interview Questions</h1>
        </div>
        
        <div class="mkcg-content-wrapper">
            <!-- LEFT PANEL -->
            <div class="mkcg-left-panel">
                <!-- Introduction Text -->
                <p class="mkcg-intro-text">
                    Generate compelling interview questions based on your selected topic. Questions will be crafted to showcase your expertise while providing maximum value to podcast listeners.
                </p>
                
                <!-- Topic Selector with Enhanced Design -->
                <div class="mkcg-topic-selector">
                    <div class="mkcg-selector-header">
                        <h3 class="mkcg-section-title">Choose Your Topic</h3>
                        <a href="<?php echo $entry_key ? '/topics/?entry=' . urlencode($entry_key) : '/topics/'; ?>" class="mkcg-edit-topics-button" id="mkcg-edit-topics">
                            🤖 Edit Topics with AI
                        </a>
                    </div>
                    
                    <div class="mkcg-topics-grid" id="mkcg-topics-grid">
                        <?php foreach ($all_topics as $topic_id => $topic_text): ?>
                            <div class="mkcg-topic-card <?php echo $topic_id === 1 ? 'mkcg-topic-card--active' : ''; ?> <?php echo empty($topic_text) ? 'mkcg-topic-card--empty' : ''; ?>" 
                                 data-topic="<?php echo esc_attr($topic_id); ?>"
                                 title="<?php echo empty($topic_text) ? 'Click to add topic' : 'Click to select, click pencil to edit'; ?>">
                                
                                <div class="mkcg-topic-card__number">
                                    <?php echo esc_html($topic_id); ?>
                                </div>
                                
                                <div class="mkcg-topic-card__content" data-topic-id="<?php echo esc_attr($topic_id); ?>">
                                    <div class="mkcg-topic-card__text" data-original-text="<?php echo esc_attr($topic_text); ?>">
                                        <?php if (!empty($topic_text)): ?>
                                            <?php echo esc_html($topic_text); ?>
                                        <?php else: ?>
                                            <span class="mkcg-topic-card__placeholder">Click to add topic</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <textarea class="mkcg-topic-card__editor" 
                                              style="display: none;" 
                                              placeholder="Enter your topic here..."
                                              rows="2"><?php echo esc_textarea($topic_text); ?></textarea>
                                </div>
                                
                                <?php if (!empty($topic_text)): ?>
                                    <div class="mkcg-topic-card__edit-icon" title="Edit this topic">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="mkcg-topic-card__add-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mkcg-topic-card__actions" style="display: none;">
                                    <button class="mkcg-topic-card__save" 
                                            data-topic-id="<?php echo esc_attr($topic_id); ?>" 
                                            title="Save (Ctrl+Enter)"
                                            type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20,6 9,17 4,12"></polyline>
                                        </svg>
                                    </button>
                                    <button class="mkcg-topic-card__cancel" 
                                            data-topic-id="<?php echo esc_attr($topic_id); ?>" 
                                            title="Cancel (Esc)"
                                            type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Selected Topic Result -->
                <div class="mkcg-selected-topic-result" id="mkcg-selected-topic-result">
                    <div class="mkcg-result-header">
                        <span class="mkcg-star-icon">★</span>
                        <h3 class="mkcg-result-title">Selected Topic</h3>
                        <span class="mkcg-ai-badge">FROM TOPICS</span>
                    </div>
                    
                    <div class="mkcg-selected-topic-content">
                        <p id="mkcg-selected-topic-text"><?php echo !empty($all_topics[1]) ? esc_html($all_topics[1]) : 'Click to add topic'; ?></p>
                    </div>
                    
                    <div class="mkcg-result-actions">
                        <button class="mkcg-generate-button" id="mkcg-generate-questions" type="button">
                            Generate Questions with AI
                        </button>
                    </div>
                </div>
                
                <!-- Loading indicator -->
                <div class="mkcg-ai-loading" id="mkcg-loading" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                    </svg>
                    Generating questions...
                </div>
                
                <!-- Questions result -->
                <div class="mkcg-questions-result" id="mkcg-questions-result" style="display: none;">
                    <div class="mkcg-questions-list" id="mkcg-questions-list">
                        <!-- Generated questions will be listed here -->
                    </div>
                </div>
                
                <!-- Field Selection Modal -->
                <div class="mkcg-modal" id="mkcg-field-modal">
                    <div class="mkcg-modal-content">
                        <div class="mkcg-modal-header">
                            <h3 class="mkcg-modal-title">Enter the field number to update (1-5):</h3>
                        </div>
                        <input type="number" min="1" max="5" class="mkcg-field-input" id="mkcg-field-number" value="1">
                        <div class="mkcg-modal-actions">
                            <button class="mkcg-ok-button" id="mkcg-modal-ok" type="button">OK</button>
                            <button class="mkcg-cancel-button" id="mkcg-modal-cancel" type="button">Cancel</button>
                        </div>
                    </div>
                </div>
                
                <!-- Form Fields - Topic-specific Questions -->
                <div class="mkcg-form-step">
                    <?php for ($topic_num = 1; $topic_num <= 5; $topic_num++): ?>
                        <div class="mkcg-topic-questions" id="mkcg-topic-<?php echo $topic_num; ?>-questions" style="<?php echo $topic_num === 1 ? 'display: block;' : 'display: none;'; ?>">
                            <div class="mkcg-topic-questions-header">
                                <h3>Questions for Topic <?php echo $topic_num; ?>: <span class="topic-title"><?php echo !empty($all_topics[$topic_num]) ? esc_html($all_topics[$topic_num]) : 'Add topic above'; ?></span></h3>
                            </div>
                            
                            <?php 
                            // Get existing questions for this topic
                            $topic_questions = isset($existing_questions[$topic_num]) ? $existing_questions[$topic_num] : [];
                            ?>
                            
                            <?php for ($q = 1; $q <= 5; $q++): ?>
                                <div class="mkcg-form-field">
                                    <div class="mkcg-form-field-label">
                                        <div class="mkcg-form-field-number"><?php echo $q; ?></div>
                                        <div class="mkcg-form-field-title">Question <?php echo $q; ?></div>
                                    </div>
                                    <textarea 
                                        class="mkcg-form-field-input" 
                                        id="mkcg-question-field-<?php echo $topic_num; ?>-<?php echo $q; ?>" 
                                        name="field_question_<?php echo $topic_num; ?>_<?php echo $q; ?>" 
                                        placeholder="Enter question <?php echo $q; ?> for this topic..."
                                        rows="3"
                                    ><?php echo isset($topic_questions[$q]) ? esc_textarea($topic_questions[$q]) : ''; ?></textarea>
                                    
                                    <?php if ($q === 1): // Only show examples for first question ?>
                                    <div class="mkcg-form-examples">
                                        <p>Examples:</p>
                                        <div class="mkcg-form-example">"What led you to develop this approach to [topic area]?"</div>
                                        <div class="mkcg-form-example">"Can you walk us through your step-by-step process for [topic implementation]?"</div>
                                        <div class="mkcg-form-example">"What's the biggest mistake you see people make with [topic area]?"</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                    
                    <!-- Hidden fields for AJAX -->
                    <input type="hidden" id="mkcg-entry-id" value="<?php echo esc_attr($entry_id); ?>">
                    <input type="hidden" id="mkcg-entry-key" value="<?php echo esc_attr($entry_key); ?>">
                    <input type="hidden" id="mkcg-questions-nonce" value="<?php echo wp_create_nonce('generate_topics_nonce'); ?>">
                    <input type="hidden" id="mkcg-selected-topic-id" value="1">
                    <input type="hidden" id="mkcg-post-id" value="<?php echo esc_attr($post_id); ?>">
                </div>
                
                <!-- Save All Button -->
                <div class="mkcg-save-section">
                    <button class="mkcg-save-all-button" id="mkcg-save-all-data" type="button">
                        💾 Save All Topics & Questions
                    </button>
                    <div class="mkcg-save-status" id="mkcg-save-status" style="display: none;"></div>
                </div>
            </div>
            
            <!-- RIGHT PANEL -->
            <div class="mkcg-right-panel">
                <h2 class="mkcg-right-panel-header">Crafting Effective Interview Questions</h2>
                <p class="mkcg-right-panel-subtitle">Well-crafted questions help podcast hosts guide the conversation while giving you opportunities to showcase your expertise. Each question should be open-ended and allow you to deliver valuable insights to listeners.</p>
                
                <div class="mkcg-formula-box">
                    <span class="mkcg-formula-label">APPROACH</span>
                    Balance <span class="mkcg-highlight">specific questions</span> that demonstrate your expertise with <span class="mkcg-highlight">story-based questions</span> that engage the audience.
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Frame Questions for Stories</h3>
                        <p class="mkcg-process-description">
                            Include questions that prompt you to share real-world examples and stories. Listeners connect with narratives, and hosts appreciate guests who illustrate points with compelling stories.
                        </p>
                    </div>
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Show Range and Depth</h3>
                        <p class="mkcg-process-description">
                            Mix high-level strategic questions with tactical implementation details. This demonstrates both your big-picture understanding and your practical expertise in executing solutions.
                        </p>
                    </div>
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Include Audience Transformation</h3>
                        <p class="mkcg-process-description">
                            Create questions that allow you to describe the transformation your clients or audience experience. Podcast hosts love when guests can articulate clear before-and-after scenarios.
                        </p>
                    </div>
                </div>
                
                <h3 class="mkcg-examples-header">Question Types to Include:</h3>
                
                <div class="mkcg-example-card">
                    <strong>Origin Questions:</strong>
                    <p>"What led you to develop this approach to content creation?"</p>
                    <p>"How did you discover this common mistake in SaaS scaling?"</p>
                </div>
                
                <div class="mkcg-example-card">
                    <strong>Process Questions:</strong>
                    <p>"Can you walk us through your step-by-step approach to building self-sufficient teams?"</p>
                    <p>"What does your content creation process look like from start to finish?"</p>
                </div>
                
                <div class="mkcg-example-card">
                    <strong>Result Questions:</strong>
                    <p>"What kind of results have your clients seen after implementing these strategies?"</p>
                    <p>"How does a properly scaled SaaS business operate differently than one that's struggling?"</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript for Topic Editing -->
<script type="text/javascript">
// Topics data from PHP
const MKCG_TopicsData = <?php echo json_encode($all_topics); ?>;

// Existing questions data from PHP
const MKCG_ExistingQuestions = <?php echo json_encode($existing_questions); ?>;

// Post ID for saving
const MKCG_PostId = <?php echo json_encode($post_id); ?>;

// Enhanced Questions Generator with Inline Editing
jQuery(document).ready(function($) {
    console.log('🎯 Questions Generator Enhanced: Initializing with inline topic editing');
    console.log('Topics data:', MKCG_TopicsData);
    console.log('Post ID:', MKCG_PostId);
    
    // Topic card selection and editing
    $('.mkcg-topic-card').on('click', function(e) {
        // Don't trigger if clicking on action buttons
        if ($(e.target).closest('.mkcg-topic-card__actions').length) {
            return;
        }
        
        const $card = $(this);
        const topicId = parseInt($card.data('topic'));
        const isEmpty = $card.hasClass('mkcg-topic-card--empty');
        const isEditing = $card.hasClass('mkcg-topic-card--editing');
        
        if (isEditing) {
            return; // Already editing
        }
        
        if (isEmpty) {
            // Empty topics: start editing immediately
            startEditingTopic(topicId);
        } else {
            // Existing topics: select on single click, edit on double click
            const now = Date.now();
            const lastClick = $card.data('lastClick') || 0;
            
            if (now - lastClick < 500) {
                // Double click: start editing
                startEditingTopic(topicId);
            } else {
                // Single click: select topic
                selectTopic(topicId);
            }
            
            $card.data('lastClick', now);
        }
    });
    
    // Save/Cancel button handlers
    $(document).on('click', '.mkcg-topic-card__save', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const topicId = parseInt($(this).data('topic-id'));
        saveTopicEdit(topicId);
    });
    
    $(document).on('click', '.mkcg-topic-card__cancel', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const topicId = parseInt($(this).data('topic-id'));
        cancelTopicEdit(topicId);
    });
    
    // Keyboard shortcuts for editing
    $(document).on('keydown', '.mkcg-topic-card__editor', function(e) {
        if (e.key === 'Escape') {
            const topicId = parseInt($(this).closest('.mkcg-topic-card').data('topic'));
            cancelTopicEdit(topicId);
        } else if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            const topicId = parseInt($(this).closest('.mkcg-topic-card').data('topic'));
            saveTopicEdit(topicId);
        }
    });
    
    // Auto-save on blur
    $(document).on('blur', '.mkcg-topic-card__editor', function() {
        const $card = $(this).closest('.mkcg-topic-card');
        if ($card.hasClass('mkcg-topic-card--editing')) {
            // Small delay to allow save/cancel buttons to work
            setTimeout(() => {
                if ($card.hasClass('mkcg-topic-card--editing')) {
                    const topicId = parseInt($card.data('topic'));
                    console.log('💾 Auto-saving topic on blur:', topicId);
                    saveTopicEdit(topicId);
                }
            }, 100);
        }
    });
    
    // Prevent blur when clicking action buttons
    $(document).on('mousedown', '.mkcg-topic-card__actions', function(e) {
        e.preventDefault();
    });
    
    // Select topic function
    function selectTopic(topicId) {
        console.log('Selecting topic:', topicId);
        
        // Update active state
        $('.mkcg-topic-card').removeClass('active');
        $(`.mkcg-topic-card[data-topic="${topicId}"]`).addClass('active');
        
        // Show selected topic questions, hide others
        $('.mkcg-topic-questions').hide();
        $(`#mkcg-topic-${topicId}-questions`).show();
        
        // Update selected topic display
        const topicText = MKCG_TopicsData[topicId] || 'Click to add topic';
        $('#mkcg-selected-topic-text').text(topicText);
        $('#mkcg-selected-topic-id').val(topicId);
        
        // Update question section title
        $(`.mkcg-topic-questions .topic-title`).text(topicText);
    }
    
    // Start editing function
    function startEditingTopic(topicId) {
        console.log('🖊️ Starting edit for topic:', topicId);
        
        const $card = $(`.mkcg-topic-card[data-topic="${topicId}"]`);
        if ($card.hasClass('mkcg-topic-card--editing')) {
            return; // Already editing
        }
        
        const $content = $card.find('.mkcg-topic-card__content');
        const $text = $content.find('.mkcg-topic-card__text');
        const $editor = $content.find('.mkcg-topic-card__editor');
        const $actions = $card.find('.mkcg-topic-card__actions');
        
        // Get current text
        const currentText = $text.data('original-text') || '';
        
        // Set editor value
        $editor.val(currentText);
        
        // Show editing state
        $card.addClass('mkcg-topic-card--editing');
        $text.hide();
        $editor.show();
        $actions.show();
        
        // Focus and select text
        $editor.focus();
        if (currentText) {
            $editor[0].select();
        }
    }
    
    // Save topic edit function
    function saveTopicEdit(topicId) {
        console.log('💾 Saving topic:', topicId);
        
        const $card = $(`.mkcg-topic-card[data-topic="${topicId}"]`);
        const $content = $card.find('.mkcg-topic-card__content');
        const $text = $content.find('.mkcg-topic-card__text');
        const $editor = $content.find('.mkcg-topic-card__editor');
        const $actions = $card.find('.mkcg-topic-card__actions');
        
        const newText = $editor.val().trim();
        
        // Show saving indicator
        showSavingIndicator($card, '💾 Saving...');
        
        // Save to backend
        if (MKCG_PostId) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'mkcg_save_topic',
                    post_id: MKCG_PostId,
                    topic_number: topicId,
                    topic_text: newText,
                    nonce: $('#mkcg-questions-nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Topic saved successfully');
                        
                        // Update display
                        updateTopicDisplay($card, $text, newText);
                        
                        // Update local data
                        MKCG_TopicsData[topicId] = newText;
                        
                        // Update selected topic if active
                        if ($card.hasClass('active')) {
                            $('#mkcg-selected-topic-text').text(newText || 'Click to add topic');
                            $(`.mkcg-topic-questions .topic-title`).text(newText || 'Add topic above');
                        }
                        
                        showSavingIndicator($card, '✅ Saved', 'success');
                    } else {
                        console.error('❌ Save failed:', response.data?.message);
                        showSavingIndicator($card, '❌ Error', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Network error:', error);
                    showSavingIndicator($card, '❌ Error', 'error');
                },
                complete: function() {
                    // Exit editing mode
                    exitEditingMode($card, $text, $editor, $actions);
                }
            });
        } else {
            // No backend save, just update display
            updateTopicDisplay($card, $text, newText);
            MKCG_TopicsData[topicId] = newText;
            exitEditingMode($card, $text, $editor, $actions);
        }
    }
    
    // Cancel topic edit function
    function cancelTopicEdit(topicId) {
        console.log('❌ Canceling edit for topic:', topicId);
        
        const $card = $(`.mkcg-topic-card[data-topic="${topicId}"]`);
        const $content = $card.find('.mkcg-topic-card__content');
        const $text = $content.find('.mkcg-topic-card__text');
        const $editor = $content.find('.mkcg-topic-card__editor');
        const $actions = $card.find('.mkcg-topic-card__actions');
        
        exitEditingMode($card, $text, $editor, $actions);
    }
    
    // Update topic display
    function updateTopicDisplay($card, $text, newText) {
        if (newText) {
            $text.html(newText);
            $card.removeClass('mkcg-topic-card--empty');
            $card.find('.mkcg-topic-card__add-icon').hide();
        } else {
            $text.html('<span class="mkcg-topic-card__placeholder">Click to add topic</span>');
            $card.addClass('mkcg-topic-card--empty');
            $card.find('.mkcg-topic-card__add-icon').show();
        }
        $text.data('original-text', newText);
    }
    
    // Exit editing mode
    function exitEditingMode($card, $text, $editor, $actions) {
        $card.removeClass('mkcg-topic-card--editing');
        $text.show();
        $editor.hide();
        $actions.hide();
    }
    
    // Show saving indicator
    function showSavingIndicator($card, message, type = 'saving') {
        // Remove existing indicators
        $card.find('.save-indicator').remove();
        
        const $indicator = $('<div class="save-indicator"></div>')
            .text(message)
            .css({
                position: 'absolute',
                top: '5px',
                right: '5px',
                background: type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : '#f87f34',
                color: 'white',
                padding: '2px 6px',
                borderRadius: '3px',
                fontSize: '10px',
                fontWeight: 'bold',
                zIndex: 10
            });
        
        $card.css('position', 'relative').append($indicator);
        
        if (type === 'success' || type === 'error') {
            setTimeout(() => $indicator.fadeOut(), 3000);
        }
    }
    
    // Initialize: Select first topic
    selectTopic(1);
    
    // AI generation functionality (existing code)
    $('#mkcg-generate-questions').on('click', function() {
        const selectedTopic = $('#mkcg-selected-topic-id').val();
        const topicText = $('#mkcg-selected-topic-text').text();
        const entryId = $('#mkcg-entry-id').val();
        const nonce = $('#mkcg-questions-nonce').val();
        
        if (!selectedTopic || !topicText || topicText === 'Click to add topic') {
            alert('Please select a topic first.');
            return;
        }
        
        // Show loading
        $('#mkcg-loading').show();
        $('#mkcg-questions-result').hide();
        
        // Make AJAX request
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'generate_interview_questions',
                security: nonce,
                entry_id: entryId,
                topic: topicText,
                topic_number: selectedTopic
            },
            success: function(response) {
                $('#mkcg-loading').hide();
                
                if (response.success && response.data.questions) {
                    displayGeneratedQuestions(response.data.questions, selectedTopic);
                } else {
                    alert('Error generating questions: ' + (response.data?.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $('#mkcg-loading').hide();
                alert('Failed to generate questions: ' + error);
            }
        });
    });
    
    // Display generated questions
    function displayGeneratedQuestions(questions, topicNumber) {
        let questionsHtml = '';
        
        questions.forEach((question, index) => {
            questionsHtml += `
                <div class="mkcg-question-item">
                    <div class="mkcg-question-number">
                        <strong>Question ${index + 1}:</strong>
                    </div>
                    <div class="mkcg-question-text">
                        ${question}
                    </div>
                    <button class="mkcg-use-question-btn" data-question="${question}" data-position="${index + 1}">Use</button>
                </div>`;
        });
        
        $('#mkcg-questions-list').html(questionsHtml);
        $('#mkcg-questions-result').show();
    }
    
    // Handle "Use" button clicks
    $(document).on('click', '.mkcg-use-question-btn', function() {
        const question = $(this).data('question');
        const position = $(this).data('position');
        const selectedTopic = $('#mkcg-selected-topic-id').val();
        
        // Set the question in the appropriate field
        const fieldId = `#mkcg-question-field-${selectedTopic}-${position}`;
        $(fieldId).val(question);
        
        alert(`Question ${position} has been added to Topic ${selectedTopic}.`);
    });
    
    console.log('✅ Questions Generator Enhanced: Initialization complete');
});
</script>
