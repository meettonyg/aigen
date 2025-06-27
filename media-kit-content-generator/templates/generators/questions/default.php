<style>
    /* Add style for the subheading */
    .mkcg-topic-questions-subheading {
      font-size: 14px;
      color: #5a6d7e;
      margin-top: 5px;
      margin-bottom: 15px;
      font-style: italic;
    }
  </style><?php
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
    
    // ENHANCED: Get and validate post association
$post_id = $formidable_service->get_post_id_from_entry($entry_id);

if ($post_id) {
    $topics_debug[] = 'Found associated post ID: ' . $post_id;
    
    // Validate post association integrity
    $validation_result = $formidable_service->validate_post_association($entry_id, $post_id);
    
    if ($validation_result['valid']) {
    $topics_debug[] = 'Post association validation: PASSED';
        
    // Get enhanced topics data with quality validation
        $topics_result = $formidable_service->get_topics_from_post_enhanced($post_id);
        $available_topics = $topics_result['topics'];
        
        if (!empty($available_topics)) {
            $topics_debug[] = 'SUCCESS: Found ' . count(array_filter($available_topics)) . ' topics (quality: ' . $topics_result['data_quality'] . ')';
            
            if ($topics_result['auto_healed']) {
                $topics_debug[] = 'Auto-healing applied to topics data';
            }
        } else {
            $topics_debug[] = 'No topics found in post meta fields';
        }
        
        // Get existing questions with integrity checking
        $questions_result = $formidable_service->get_questions_with_integrity_check($post_id);
        $existing_questions = [];
        
        // Organize questions by topic for template compatibility
        for ($topic = 1; $topic <= 5; $topic++) {
            $topic_questions_result = $formidable_service->get_questions_with_integrity_check($post_id, $topic);
            $existing_questions[$topic] = $topic_questions_result['questions'];
        }
        
        $topics_debug[] = 'Questions integrity: ' . $questions_result['integrity_status'];
        
        if ($questions_result['auto_healed']) {
            $topics_debug[] = 'Auto-healing applied to questions data';
        }
        
    } else {
        $topics_debug[] = 'Post association validation: FAILED - ' . implode(', ', $validation_result['issues']);
        
        if (!empty($validation_result['auto_fixed'])) {
            $topics_debug[] = 'Auto-fixes applied: ' . implode(', ', $validation_result['auto_fixed']);
        }
    }
    
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

// ENHANCED ERROR HANDLING - Show detailed feedback with auto-healing options
if (empty($available_topics) || count(array_filter($available_topics)) === 0) {
    $topics_url = '';
    if ($entry_key) {
        $topics_url = site_url('/topics/?entry=' . urlencode($entry_key));
    } elseif ($entry_id) {
        $topics_url = site_url('/topics/?entry_id=' . $entry_id);
    }
    
    echo '<div class="mkcg-error-notice mkcg-enhanced-error">';
    echo '<h3>⚠️ Topics Data Required</h3>';
    
    // Show data quality information if available
    if (isset($topics_result)) {
        echo '<div class="mkcg-data-status">';
        echo '<p><strong>Data Quality:</strong> ' . esc_html(ucfirst($topics_result['data_quality'])) . '</p>';
        
        if (!empty($topics_result['validation_status'])) {
            echo '<p><strong>Status:</strong> ' . esc_html(implode(', ', $topics_result['validation_status'])) . '</p>';
        }
        
        if ($topics_result['auto_healed']) {
            echo '<p class="mkcg-auto-healed">✨ <strong>Auto-healing attempted</strong> - Some placeholder data may have been added.</p>';
        }
        echo '</div>';
    }
    
    echo '<p>Please generate your interview topics first before creating questions. The Topics Generator will create the foundation content needed for question generation.</p>';
    
    if ($topics_url) {
        echo '<div class="mkcg-action-buttons">';
        echo '<a href="' . esc_url($topics_url) . '" class="mkcg-button mkcg-primary-button">🚀 Generate Topics First</a>';
        
        if (isset($post_id) && $post_id) {
            echo '<button onclick="location.reload()" class="mkcg-button mkcg-secondary-button">🔄 Refresh Page</button>';
        }
        echo '</div>';
    }
    
    // Show debug info in development mode
    if (defined('WP_DEBUG') && WP_DEBUG && !empty($topics_debug)) {
        echo '<details class="mkcg-debug-info">';
        echo '<summary>🔧 Debug Information</summary>';
        echo '<ul>';
        foreach ($topics_debug as $debug_line) {
            echo '<li>' . esc_html($debug_line) . '</li>';
        }
        echo '</ul>';
        echo '</details>';
    }
    
    echo '</div>';
    return;
}
?>

<div class="mkcg-questions-generator-wrapper">
    <style>
    /* Enhanced styles for data quality indicators and error handling */
    .mkcg-topic-questions-subheading {
      font-size: 14px;
      color: #5a6d7e;
      margin-top: 5px;
      margin-bottom: 15px;
      font-style: italic;
    }
    
    .mkcg-enhanced-error {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 30px;
        margin: 20px 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .mkcg-data-status {
        background: #f1f3f4;
        border: 1px solid #dadce0;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
        font-size: 14px;
    }
    
    .mkcg-auto-healed {
        color: #1a73e8;
        font-weight: 500;
    }
    
    .mkcg-action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .mkcg-primary-button {
        background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(26, 115, 232, 0.3);
    }
    
    .mkcg-primary-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .mkcg-secondary-button {
        background: white;
        color: #5f6368;
        padding: 12px 24px;
        border: 2px solid #dadce0;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .mkcg-secondary-button:hover {
        border-color: #1a73e8;
        color: #1a73e8;
        text-decoration: none;
    }
    
    .mkcg-debug-info {
        margin-top: 20px;
        font-size: 12px;
        color: #666;
    }
    
    .mkcg-debug-info summary {
        cursor: pointer;
        font-weight: 600;
        padding: 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    
    .mkcg-debug-info ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .mkcg-script-error {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Enhanced topic card indicators */
    .mkcg-topic-card[data-quality="poor"] {
        border-left: 4px solid #ffa726;
    }
    
    .mkcg-topic-card[data-quality="excellent"] {
        border-left: 4px solid #66bb6a;
    }
    
    .mkcg-topic-card[data-quality="missing"] {
        border-left: 4px solid #ef5350;
    }
    </style>
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
                
                <!-- Clean Topic Selector -->
                <div class="mkcg-topic-selector">
                    <div class="mkcg-selector-header">
                        <h3 class="mkcg-section-title">Choose Your Topic</h3>
                        <button class="mkcg-edit-topics-button" id="mkcg-edit-topics" type="button">
                            ✎ Edit Topics
                        </button>
                    </div>
                    
                    <div class="mkcg-topics-grid" id="mkcg-topics-grid">
                        <?php 
                        // Always show 5 topic slots
                        for ($topic_id = 1; $topic_id <= 5; $topic_id++): 
                            $topic_text = isset($all_topics[$topic_id]) ? $all_topics[$topic_id] : '';
                            $is_active = ($topic_id === 1) ? 'active' : '';
                            $is_empty = empty($topic_text);
                        ?>
                            <div class="mkcg-topic-card <?php echo $is_active; ?>" 
                                 data-topic="<?php echo esc_attr($topic_id); ?>">
                                
                                <div class="mkcg-topic-number">
                                    <?php echo esc_html($topic_id); ?>
                                </div>
                                
                                <div class="mkcg-topic-text">
                                    <?php if (!$is_empty): ?>
                                        <?php echo esc_html($topic_text); ?>
                                    <?php else: ?>
                                        Click to add topic
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mkcg-topic-edit-icon" title="Edit this topic">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </div>
                                
                            </div>
                        <?php endfor; ?>
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
                                <h3 id="mkcg-questions-heading">Interview Questions for "<?php echo !empty($all_topics[$topic_num]) ? esc_html($all_topics[$topic_num]) : 'Add topic above'; ?>"</h3>
                                <p class="mkcg-topic-questions-subheading">Each topic has 5 interview questions</p>
                            </div>
                            
                            <?php 
                            // Get existing questions for this topic
                            $topic_questions = isset($existing_questions[$topic_num]) ? $existing_questions[$topic_num] : [];
                            ?>
                            
                            <?php for ($q = 1; $q <= 5; $q++): ?>
                                <div class="mkcg-form-field">
                                    <div class="mkcg-form-field-label">
                                    <div class="mkcg-form-field-number"><?php echo $q; ?></div>
                                    <div class="mkcg-form-field-title"><?php 
                                    $ordinals = ['First', 'Second', 'Third', 'Fourth', 'Fifth'];
                                    echo $ordinals[$q-1] . ' Interview Question'; 
                                    ?></div>
                                </div>
                                    <textarea 
                                        class="mkcg-form-field-input" 
                                        id="mkcg-question-field-<?php echo $topic_num; ?>-<?php echo $q; ?>" 
                                        name="field_question_<?php echo $topic_num; ?>_<?php echo $q; ?>" 
                                        placeholder="Enter the <?php echo $ordinals[$q-1]; ?> interview question for this topic..."
                                        rows="3"
                                    ><?php echo isset($topic_questions[$q]) ? esc_textarea($topic_questions[$q]) : ''; ?></textarea>
                                    
                                    <?php // Examples removed as requested ?>
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

<!-- ENHANCED JavaScript Integration with Data Quality Information -->
<script type="text/javascript">
// Enhanced data from PHP with quality information
const MKCG_TopicsData = <?php echo json_encode($all_topics); ?>;
const MKCG_ExistingQuestions = <?php echo json_encode($existing_questions); ?>;
const MKCG_PostId = <?php echo json_encode($post_id); ?>;

// Enhanced metadata for frontend validation
const MKCG_DataMetadata = <?php 
echo json_encode([
    'topics_quality' => isset($topics_result) ? $topics_result['data_quality'] : 'unknown',
    'topics_source' => isset($topics_result) ? $topics_result['source_pattern'] : 'unknown',
    'questions_integrity' => isset($questions_result) ? $questions_result['integrity_status'] : 'unknown',
    'auto_healed' => [
        'topics' => isset($topics_result) ? $topics_result['auto_healed'] : false,
        'questions' => isset($questions_result) ? $questions_result['auto_healed'] : false
    ],
    'validation_passed' => isset($validation_result) ? $validation_result['valid'] : false,
    'entry_id' => $entry_id,
    'entry_key' => $entry_key,
    'timestamp' => time()
]);
?>;

// Enhanced initialization with error handling and debugging
document.addEventListener('DOMContentLoaded', function() {
    console.log('MKCG Enhanced Questions: DOM ready, initializing with metadata:', MKCG_DataMetadata);
    
    try {
        if (typeof QuestionsGenerator !== 'undefined') {
            QuestionsGenerator.init();
        } else {
            console.error('MKCG Enhanced Questions: QuestionsGenerator script not loaded correctly');
            
            // Show user-friendly error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mkcg-script-error';
            errorDiv.style.cssText = `
                background: #f8d7da;
                color: #721c24;
                padding: 15px;
                margin: 20px 0;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                text-align: center;
            `;
            errorDiv.innerHTML = `
                <strong>⚠️ Script Loading Error</strong><br>
                The Questions Generator failed to load properly. Please refresh the page.
                <br><br>
                <button onclick="location.reload()" style="background: #721c24; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Refresh Page</button>
            `;
            
            const container = document.querySelector('.mkcg-questions-generator-wrapper');
            if (container) {
                container.insertBefore(errorDiv, container.firstChild);
            }
        }
    } catch (error) {
        console.error('MKCG Enhanced Questions: Critical initialization error:', error);
    }
});

// Debug information for development
if (typeof console !== 'undefined') {
    console.log('MKCG Enhanced Questions: Data loaded successfully', {
        topics: Object.keys(MKCG_TopicsData).length,
        questions: Object.keys(MKCG_ExistingQuestions).length,
        postId: MKCG_PostId,
        metadata: MKCG_DataMetadata
    });
}
</script>
