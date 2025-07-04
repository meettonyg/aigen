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
        $entry_data = $formidable_service->get_entry_by_key($entry_key);
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

// UNIFIED: Always ensure we have 5 topic slots with consistent data source
$all_topics = [];
for ($i = 1; $i <= 5; $i++) {
    if (isset($mkcg_template_data['form_field_values']) && isset($mkcg_template_data['form_field_values']['topic_' . $i])) {
        $all_topics[$i] = $mkcg_template_data['form_field_values']['topic_' . $i];
    } elseif (isset($available_topics[$i]) && !empty($available_topics[$i])) {
        $all_topics[$i] = $available_topics[$i];
    } else {
        $all_topics[$i] = '';
    }
}

// CRITICAL FIX: Always show the form - don't hide it based on topics
$displayable_topics = array_filter($all_topics);
$has_meaningful_content = false;

// Check if displayed topics have meaningful content (not just placeholders)
foreach ($displayable_topics as $topic) {
    if (!empty($topic) && !preg_match('/^(Topic \d+|Click|Add|Placeholder|Empty)/i', trim($topic))) {
        $has_meaningful_content = true;
        break;
    }
}

// CHECK FOR ENTRY PARAMETER: Don't show defaults if no entry param provided
$has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                   (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');

if (!$has_entry_param) {
    // NO ENTRY PARAM: Don't add test data, keep empty structure
    if (empty($all_topics) || count(array_filter($all_topics)) === 0) {
        $all_topics = [
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => ''
        ];
        $debug_info[] = 'NO ENTRY PARAM: Using empty topics structure (no defaults)';
        $has_meaningful_content = false;
    }
} else {
    // HAS ENTRY PARAM: Only add test data if nothing exists and entry param is present
    if (empty($all_topics) || count(array_filter($all_topics)) === 0) {
        $all_topics = [
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => ''
        ];
        $debug_info[] = 'ENTRY PARAM EXISTS: Using empty structure (no test data needed)';
        $has_meaningful_content = false;
    }
}

// Debug output for development
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- DEBUG INFO: ' . implode(' | ', $debug_info) . ' -->';
    echo '<!-- TOPICS DEBUG: ' . implode(' | ', $topics_debug) . ' -->';
    echo '<!-- DISPLAY TOPICS: ' . count($displayable_topics) . ' topics, meaningful: ' . ($has_meaningful_content ? 'YES' : 'NO') . ' -->';
}

// ROOT-LEVEL FIX: Show info notice but always continue with form rendering
if (false) { // Never show this notice in current fix
    $topics_url = '';
    if ($entry_key) {
        $topics_url = site_url('/topics/?entry=' . urlencode($entry_key));
    } elseif ($entry_id) {
        $topics_url = site_url('/topics/?entry_id=' . $entry_id);
    }
    
    echo '<div class="mkcg-info-notice mkcg-enhanced-info">';
    echo '<h3>🚀 Ready to Create Questions</h3>';
    
    echo '<p>You can add your interview topics directly here and generate questions right away. Use the topic cards below to get started.</p>';
    
    // Only show technical data quality info in debug mode
    if (defined('WP_DEBUG') && WP_DEBUG && isset($topics_result)) {
        echo '<div class="mkcg-data-status">';
        echo '<p><strong>Debug - Data Quality:</strong> ' . esc_html(ucfirst($topics_result['data_quality'])) . '</p>';
        
        if (!empty($topics_result['validation_status'])) {
            echo '<p><strong>Debug - Status:</strong> ' . esc_html(implode(', ', $topics_result['validation_status'])) . '</p>';
        }
        
        if ($topics_result['auto_healed']) {
            echo '<p class="mkcg-auto-healed">✨ <strong>Debug - Auto-healing applied</strong></p>';
        }
        echo '</div>';
    }
    
    if ($topics_url) {
        echo '<div class="mkcg-action-buttons">';
        echo '<a href="' . esc_url($topics_url) . '" class="mkcg-button mkcg-secondary-button">🚀 Use Topics Generator</a>';
        echo '<button onclick="this.parentNode.parentNode.style.display=\'none\'" class="mkcg-button mkcg-primary-button">✏️ Create Topics Here</button>';
        echo '</div>';
    }
    
    // Show debug info in development mode (collapsed by default)
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
    // ✨ CRITICAL CHANGE: Always continue with form rendering for testing
}
?>

<div class="mkcg-questions-generator-wrapper" data-generator="questions">
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
    
    /* ENHANCED: Friendly info notice for empty topics */
    .mkcg-enhanced-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
        border: 2px solid #bbdefb;
        border-radius: 12px;
        padding: 25px;
        margin: 20px 0;
        box-shadow: 0 3px 10px rgba(33, 150, 243, 0.1);
    }
    
    .mkcg-enhanced-info h3 {
        color: #1976d2;
        margin-top: 0;
    }
    
    .mkcg-info-notice {
        animation: slideInFade 0.4s ease-out;
    }
    
    @keyframes slideInFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
    
    /* Simple Save Section Styles */
    .mkcg-save-section {
        margin: 30px 0;
        text-align: center;
    }
    
    /* ENHANCED: Empty topic card styling */
    .mkcg-topic-empty {
        border: 2px dashed #e0e6ed !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%) !important;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .mkcg-topic-empty:hover {
        border-color: #1a9bdc !important;
        opacity: 1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 155, 220, 0.2);
    }
    
    .mkcg-topic-placeholder {
        font-style: italic;
        color: #6c757d;
    }
    
    .mkcg-placeholder-text {
        display: inline-block;
        padding: 2px 8px;
        background: rgba(26, 155, 220, 0.1);
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        color: #1a9bdc;
        transition: all 0.2s ease;
    }
    
    .mkcg-topic-empty:hover .mkcg-placeholder-text {
        background: rgba(26, 155, 220, 0.2);
        color: #0d7dad;
    }
    
    /* Enhanced visual feedback for empty cards */
    .mkcg-topic-empty .mkcg-topic-edit-icon {
        color: #1a9bdc;
        background: rgba(26, 155, 220, 0.1);
        border-radius: 50%;
        padding: 4px;
        transition: all 0.2s ease;
    }
    
    .mkcg-topic-empty:hover .mkcg-topic-edit-icon {
        background: rgba(26, 155, 220, 0.2);
        transform: scale(1.1);
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
                        // Always show 5 topic slots with enhanced empty state handling
                        for ($topic_id = 1; $topic_id <= 5; $topic_id++): 
                            $topic_text = isset($all_topics[$topic_id]) ? $all_topics[$topic_id] : '';
                            $is_active = ($topic_id === 1) ? 'active' : '';
                            $is_empty = empty(trim($topic_text));
                            
                            // Enhanced styling for empty topics
                            $card_classes = $is_active;
                            if ($is_empty) {
                                $card_classes .= ' mkcg-topic-empty';
                            }
                        ?>
                            <div class="mkcg-topic-card <?php echo trim($card_classes); ?>" 
                                 data-topic="<?php echo esc_attr($topic_id); ?>"
                                 data-empty="<?php echo $is_empty ? 'true' : 'false'; ?>"
                                 title="<?php echo $is_empty ? 'Click to add your topic ' . $topic_id : 'Topic ' . $topic_id . ': ' . esc_attr($topic_text); ?>">
                                
                                <div class="mkcg-topic-number">
                                    <?php echo esc_html($topic_id); ?>
                                </div>
                                
                                <div class="mkcg-topic-text <?php echo $is_empty ? 'mkcg-topic-placeholder' : ''; ?>">
                                    <?php if (!$is_empty): ?>
                                        <?php echo esc_html($topic_text); ?>
                                    <?php else: ?>
                                        <span class="mkcg-placeholder-text">Click to add your interview topic</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mkcg-topic-edit-icon" title="<?php echo $is_empty ? 'Add topic' : 'Edit this topic'; ?>">
                                    <?php if ($is_empty): ?>
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    <?php else: ?>
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    <?php endif; ?>
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
                                        class="mkcg-form-field-input mkcg-question-field" 
                                        id="mkcg-question-field-<?php echo $topic_num; ?>-<?php echo $q; ?>" 
                                        name="field_question_<?php echo $topic_num; ?>_<?php echo $q; ?>" 
                                        data-field-type="question"
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
                    
                    <!-- Simple Save Button -->
                    <div class="mkcg-save-section">
                        <button class="mkcg-generate-button" id="mkcg-save-all-questions" type="button">
                            Save All Questions
                        </button>
                    </div>
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

<!-- CRITICAL FIX: Standardized JavaScript data output to match Topics Generator -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Questions: Template data loading...', {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
        postId: <?php echo json_encode($post_id); ?>,
        templateLoadTime: new Date().toISOString()
    });
    
    // CRITICAL FIX: Standardize on the same global variable as the Topics Generator.
    window.MKCG_Topics_Data = {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
        authorityHook: {
             // This can be populated if needed, otherwise left empty.
        },
        topics: <?php echo json_encode(array_filter($all_topics)); ?>, // Pass the topics data
        // We can add questions data here if needed by other scripts
        questions: <?php echo json_encode(array_filter($existing_questions)); ?>,
        dataSource: 'questions_generator_template' // Identifier for debugging
    };
    
    console.log('✅ MKCG Questions: Standardized data loaded into window.MKCG_Topics_Data', window.MKCG_Topics_Data);
    
    // UNIFIED: Data validation for standardized structure
    function validateDataStructure(data) {
        const validation = {
            valid: true,
            issues: [],
            dataSource: data.dataSource || 'unknown'
        };
        
        // Check required structure
        if (!data.topics || typeof data.topics !== 'object') {
            validation.valid = false;
            validation.issues.push('Missing topics object');
        } else {
            // Validate unified topic format (numeric keys from PHP array)
            const actualKeys = Object.keys(data.topics);
            const hasTopicData = actualKeys.some(key => {
                return data.topics[key] && data.topics[key].trim().length > 0;
            });
            
            if (!hasTopicData) {
                validation.issues.push('No topic data found');
            } else {
                console.log('✅ Topics data validation: Found data in', actualKeys.length, 'slots');
            }
        }
        
        // Check authority hook structure (optional)
        if (!data.authorityHook || typeof data.authorityHook !== 'object') {
            validation.issues.push('Authority hook object missing or empty (non-critical)');
        }
        
        return validation;
    }
    
    // Validate the data we just loaded
    const validation = validateDataStructure(window.MKCG_Topics_Data);
    if (!validation.valid) {
        console.error('🚨 MKCG Questions: Data structure validation failed:', validation.issues);
    } else {
        console.log('✅ MKCG Questions: Data structure validation passed (source: ' + validation.dataSource + ')');
    }
    
    // Enhanced initialization with error handling and debugging
    document.addEventListener('DOMContentLoaded', function() {
        console.log('MKCG Enhanced Questions: DOM ready, initializing with standardized data');
        
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
        console.log('MKCG Enhanced Questions: Standardized data loaded successfully', {
            topics: Object.keys(window.MKCG_Topics_Data.topics).length,
            questions: Object.keys(window.MKCG_Topics_Data.questions).length,
            entryId: window.MKCG_Topics_Data.entryId,
            dataSource: window.MKCG_Topics_Data.dataSource
        });
    }
</script>
