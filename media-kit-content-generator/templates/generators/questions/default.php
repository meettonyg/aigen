<?php
/**
 * Questions Generator Template - Unified BEM Architecture
 * Follows the same patterns as Topics and Offers generators
 * Uses base .generator__* classes + .questions-generator__* specific classes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ROOT FIX: Use template data from generator class (passed from shortcode)
global $mkcg_template_data, $generator_instance;

// Get template data from generator instance if available
if (!isset($mkcg_template_data) && $generator_instance && method_exists($generator_instance, 'get_template_data')) {
    $mkcg_template_data = $generator_instance->get_template_data();
    error_log('MKCG Questions Template: Loaded template data from generator instance');
}

// Set default if no template data available
if (!isset($mkcg_template_data) || !is_array($mkcg_template_data)) {
    $mkcg_template_data = [
        'post_id' => 0,
        'has_data' => false,
        'form_field_values' => [],
        'questions' => [],
        'authority_hook_components' => []
    ];
    error_log('MKCG Questions Template: Using default template data structure');
}

// Extract data for template use
$post_id = isset($mkcg_template_data['post_id']) ? $mkcg_template_data['post_id'] : 0;
$entry_id = $post_id; // Use post_id as entry_id for backward compatibility
$entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
$debug_info = ['Template data loaded from generator class'];

// ROOT FIX: Use template data instead of complex Formidable logic
$available_topics = isset($mkcg_template_data['form_field_values']) ? $mkcg_template_data['form_field_values'] : [];
$existing_questions = isset($mkcg_template_data['questions']) ? $mkcg_template_data['questions'] : [];
$authority_hook_components = isset($mkcg_template_data['authority_hook_components']) ? $mkcg_template_data['authority_hook_components'] : [];
$topics_debug = ['Using template data from generator class'];

if ($post_id && !empty($available_topics)) {
    $topics_debug[] = 'SUCCESS: Found ' . count(array_filter($available_topics)) . ' topics from template data';
} else {
    $topics_debug[] = 'No topics found in template data';
}

// ROOT FIX: Use template data directly for topics
$all_topics = [];
for ($i = 1; $i <= 5; $i++) {
    if (isset($mkcg_template_data['form_field_values']['topic_' . $i])) {
        $all_topics[$i] = $mkcg_template_data['form_field_values']['topic_' . $i];
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

// Define right panel content using Questions Generator classes
$content = '<div class="questions-generator__guidance">
    <h2 class="questions-generator__guidance-header">Crafting Effective Interview Questions</h2>
    <p class="questions-generator__guidance-subtitle">
        Well-crafted questions help podcast hosts guide the conversation while giving you opportunities to showcase your expertise. Each question should be open-ended and allow you to deliver valuable insights to listeners.
    </p>
    
    <div class="questions-generator__formula-box">
        <span class="questions-generator__formula-label">APPROACH</span>
        Balance <span class="questions-generator__highlight">specific questions</span> that demonstrate your expertise with <span class="questions-generator__highlight">story-based questions</span> that engage the audience.
    </div>
    
    <div class="questions-generator__process-step">
        <div class="questions-generator__process-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
        </div>
        <div class="questions-generator__process-content">
            <h3 class="questions-generator__process-title">Frame Questions for Stories</h3>
            <p class="questions-generator__process-description">
                Include questions that prompt you to share real-world examples and stories. Listeners connect with narratives, and hosts appreciate guests who illustrate points with compelling stories.
            </p>
        </div>
    </div>
    
    <div class="questions-generator__process-step">
        <div class="questions-generator__process-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
        </div>
        <div class="questions-generator__process-content">
            <h3 class="questions-generator__process-title">Show Range and Depth</h3>
            <p class="questions-generator__process-description">
                Mix high-level strategic questions with tactical implementation details. This demonstrates both your big-picture understanding and your practical expertise in executing solutions.
            </p>
        </div>
    </div>
    
    <div class="questions-generator__process-step">
        <div class="questions-generator__process-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="questions-generator__process-content">
            <h3 class="questions-generator__process-title">Include Audience Transformation</h3>
            <p class="questions-generator__process-description">
                Create questions that allow you to describe the transformation your clients or audience experience. Podcast hosts love when guests can articulate clear before-and-after scenarios.
            </p>
        </div>
    </div>
    
    <h3 class="questions-generator__examples-header">Question Types to Include:</h3>
    
    <div class="questions-generator__example-card">
        <strong>Origin Questions:</strong>
        <p>"What led you to develop this approach to content creation?"</p>
        <p>"How did you discover this common mistake in SaaS scaling?"</p>
    </div>
    
    <div class="questions-generator__example-card">
        <strong>Process Questions:</strong>
        <p>"Can you walk us through your step-by-step approach to building self-sufficient teams?"</p>
        <p>"What does your content creation process look like from start to finish?"</p>
    </div>
    
    <div class="questions-generator__example-card">
        <strong>Result Questions:</strong>
        <p>"What kind of results have your clients seen after implementing these strategies?"</p>
        <p>"How does a properly scaled SaaS business operate differently than one that\'s struggling?"</p>
    </div>
</div>';

// CRITICAL DEBUG: Log the actual authority hook data
error_log('MKCG Questions Template: Authority Hook Components: ' . json_encode($authority_hook_components));
error_log('MKCG Questions Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_meaningful_content ? 'true' : 'false'));
?>

<div class="generator__container questions-generator" data-generator="questions">

    <div class="generator__header">
        <h1 class="generator__title">Create Your Interview Questions</h1>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="questions-generator__intro">
                Generate compelling interview questions based on your selected topic. Questions will be crafted to showcase your expertise while providing maximum value to podcast listeners.
            </p>
                
            <!-- Clean Topic Selector -->
            <div class="questions-generator__topic-selector">
                <div class="questions-generator__selector-header">
                    <h3 class="questions-generator__section-title">Choose Your Topic</h3>
                    <button class="generator__button generator__button--outline" id="questions-generator-edit-topics" type="button">
                        ✎ Edit Topics
                    </button>
                </div>
                    
                <div class="questions-generator__topics-grid" id="questions-generator-topics-grid">
                    <?php 
                    // Always show 5 topic slots with enhanced empty state handling
                    for ($topic_id = 1; $topic_id <= 5; $topic_id++): 
                        $topic_text = isset($all_topics[$topic_id]) ? $all_topics[$topic_id] : '';
                        $is_active = ($topic_id === 1) ? 'questions-generator__topic-card--active' : '';
                        $is_empty = empty(trim($topic_text));
                        
                        // Enhanced styling for empty topics
                        $card_classes = 'questions-generator__topic-card ' . $is_active;
                        if ($is_empty) {
                            $card_classes .= ' questions-generator__topic-card--empty';
                        }
                    ?>
                        <div class="<?php echo trim($card_classes); ?>" 
                             data-topic="<?php echo esc_attr($topic_id); ?>"
                             data-empty="<?php echo $is_empty ? 'true' : 'false'; ?>"
                             title="<?php echo $is_empty ? 'Click to add your topic ' . $topic_id : 'Topic ' . $topic_id . ': ' . esc_attr($topic_text); ?>">
                            
                            <div class="questions-generator__topic-number">
                                <?php echo esc_html($topic_id); ?>
                            </div>
                            
                            <div class="questions-generator__topic-text <?php echo $is_empty ? 'questions-generator__topic-text--placeholder' : ''; ?>">
                                <?php if (!$is_empty): ?>
                                    <?php echo esc_html($topic_text); ?>
                                <?php else: ?>
                                    <span class="questions-generator__placeholder-text">Click to add your interview topic</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="questions-generator__topic-edit-icon" title="<?php echo $is_empty ? 'Add topic' : 'Edit this topic'; ?>">
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
            <div class="questions-generator__selected-topic-result" id="questions-generator-selected-topic-result">
                <div class="questions-generator__result-header">
                    <span class="questions-generator__star-icon">★</span>
                    <h3 class="questions-generator__result-title">Selected Topic</h3>
                    <span class="generator__badge">FROM TOPICS</span>
                </div>
                
                <div class="questions-generator__selected-topic-content">
                    <p id="questions-generator-selected-topic-text"><?php echo !empty($all_topics[1]) ? esc_html($all_topics[1]) : 'Click to add topic'; ?></p>
                </div>
                
                <div class="questions-generator__result-actions">
                    <button class="generator__button generator__button--secondary" id="questions-generator-generate-questions" type="button">
                        Generate Questions with AI
                    </button>
                </div>
            </div>
                
            <!-- Loading indicator -->
            <div class="generator__loading generator__loading--hidden" id="questions-generator-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                </svg>
                Generating questions...
            </div>
            
            <!-- Questions result -->
            <div class="generator__results generator__results--hidden" id="questions-generator-questions-result">
                <div class="questions-generator__questions-list" id="questions-generator-questions-list">
                    <!-- Generated questions will be listed here -->
                </div>
            </div>
                
            <!-- Field Selection Modal -->
            <div class="generator__modal" id="questions-generator-field-modal">
                <div class="generator__modal-content">
                    <div class="generator__modal-header">
                        <h3 class="generator__modal-title">Enter the field number to update (1-5):</h3>
                    </div>
                    <input type="number" min="1" max="5" class="generator__field-input" id="questions-generator-field-number" value="1">
                    <div class="generator__modal-actions">
                        <button class="generator__button generator__button--primary" id="questions-generator-modal-ok" type="button">OK</button>
                        <button class="generator__button generator__button--outline" id="questions-generator-modal-cancel" type="button">Cancel</button>
                    </div>
                </div>
            </div>
                
            <!-- Form Fields - Topic-specific Questions -->
            <div class="questions-generator__form-step">
                <?php for ($topic_num = 1; $topic_num <= 5; $topic_num++): ?>
                    <div class="questions-generator__topic-questions" id="questions-generator-topic-<?php echo $topic_num; ?>-questions" style="<?php echo $topic_num === 1 ? 'display: block;' : 'display: none;'; ?>">
                        <div class="questions-generator__topic-questions-header">
                            <h3 id="questions-generator-questions-heading">Interview Questions for "<?php echo !empty($all_topics[$topic_num]) ? esc_html($all_topics[$topic_num]) : 'Add topic above'; ?>"</h3>
                            <p class="questions-generator__topic-questions-subheading">Each topic has 5 interview questions</p>
                        </div>
                            
                            <?php 
                            // Get existing questions for this topic
                            $topic_questions = isset($existing_questions[$topic_num]) ? $existing_questions[$topic_num] : [];
                            ?>
                            
                        <?php for ($q = 1; $q <= 5; $q++): ?>
                            <div class="generator__field">
                                <div class="questions-generator__form-field-label">
                                    <div class="questions-generator__form-field-number"><?php echo $q; ?></div>
                                    <div class="questions-generator__form-field-title"><?php 
                                    $ordinals = ['First', 'Second', 'Third', 'Fourth', 'Fifth'];
                                    echo $ordinals[$q-1] . ' Interview Question'; 
                                    ?></div>
                                </div>
                                <textarea 
                                    class="generator__field-input questions-generator__question-field" 
                                    id="questions-generator-question-field-<?php echo $topic_num; ?>-<?php echo $q; ?>" 
                                    name="field_question_<?php echo $topic_num; ?>_<?php echo $q; ?>" 
                                    data-field-type="question"
                                    placeholder="Enter the <?php echo $ordinals[$q-1]; ?> interview question for this topic..."
                                    rows="3"
                                ><?php echo isset($topic_questions[$q]) ? esc_textarea($topic_questions[$q]) : ''; ?></textarea>
                            </div>
                        <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                    
                <!-- Hidden fields for AJAX -->
                <input type="hidden" id="questions-generator-entry-id" value="<?php echo esc_attr($entry_id); ?>">
                <input type="hidden" id="questions-generator-entry-key" value="<?php echo esc_attr($entry_key); ?>">
                <input type="hidden" id="questions-generator-questions-nonce" value="<?php echo wp_create_nonce('generate_topics_nonce'); ?>">
                <input type="hidden" id="questions-generator-selected-topic-id" value="1">
                <input type="hidden" id="questions-generator-post-id" value="<?php echo esc_attr($post_id); ?>">
                
                <!-- Simple Save Button -->
                <div class="questions-generator__save-section">
                    <button class="generator__button generator__button--secondary" id="questions-generator-save-all-questions" type="button">
                        Save All Questions
                    </button>
                </div>
            </div>

        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <h2 class="questions-generator__guidance-header">Crafting Effective Interview Questions</h2>
            <p class="questions-generator__guidance-subtitle">Well-crafted questions help podcast hosts guide the conversation while giving you opportunities to showcase your expertise. Each question should be open-ended and allow you to deliver valuable insights to listeners.</p>
                
            <div class="questions-generator__formula-box">
                <span class="questions-generator__formula-label">APPROACH</span>
                Balance <span class="questions-generator__highlight">specific questions</span> that demonstrate your expertise with <span class="questions-generator__highlight">story-based questions</span> that engage the audience.
            </div>
                
            <div class="questions-generator__process-step">
                <div class="questions-generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                </div>
                <div class="questions-generator__process-content">
                    <h3 class="questions-generator__process-title">Frame Questions for Stories</h3>
                    <p class="questions-generator__process-description">
                        Include questions that prompt you to share real-world examples and stories. Listeners connect with narratives, and hosts appreciate guests who illustrate points with compelling stories.
                    </p>
                </div>
            </div>
                
            <div class="questions-generator__process-step">
                <div class="questions-generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </div>
                <div class="questions-generator__process-content">
                    <h3 class="questions-generator__process-title">Show Range and Depth</h3>
                    <p class="questions-generator__process-description">
                        Mix high-level strategic questions with tactical implementation details. This demonstrates both your big-picture understanding and your practical expertise in executing solutions.
                    </p>
                </div>
            </div>
                
            <div class="questions-generator__process-step">
                <div class="questions-generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="questions-generator__process-content">
                    <h3 class="questions-generator__process-title">Include Audience Transformation</h3>
                    <p class="questions-generator__process-description">
                        Create questions that allow you to describe the transformation your clients or audience experience. Podcast hosts love when guests can articulate clear before-and-after scenarios.
                    </p>
                </div>
            </div>
                
            <h3 class="questions-generator__examples-header">Question Types to Include:</h3>
            
            <div class="questions-generator__example-card">
                <strong>Origin Questions:</strong>
                <p>"What led you to develop this approach to content creation?"</p>
                <p>"How did you discover this common mistake in SaaS scaling?"</p>
            </div>
            
            <div class="questions-generator__example-card">
                <strong>Process Questions:</strong>
                <p>"Can you walk us through your step-by-step approach to building self-sufficient teams?"</p>
                <p>"What does your content creation process look like from start to finish?"</p>
            </div>
            
            <div class="questions-generator__example-card">
                <strong>Result Questions:</strong>
                <p>"What kind of results have your clients seen after implementing these strategies?"</p>
                <p>"How does a properly scaled SaaS business operate differently than one that's struggling?"</p>
            </div>
    </div>
</div>

<!-- Questions Generator JavaScript Data -->
<script type="text/javascript">
    // Questions Generator Debug Info
    console.log('🎯 Questions Generator: Template data loading...', {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
        postId: <?php echo json_encode($post_id); ?>,
        templateLoadTime: new Date().toISOString()
    });
    
    // ROOT FIX: Use consistent data structure matching Topics Generator
    window.MKCG_Questions_Data = {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        postId: <?php echo intval($post_id); ?>,
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
        authorityHook: <?php echo json_encode($mkcg_template_data['authority_hook_components'] ?? []); ?>,
        topics: <?php echo json_encode($all_topics); ?>, // All 5 topics (including empty ones)
        questions: <?php echo json_encode($existing_questions); ?>,
        hasData: <?php echo !empty($all_topics) && count(array_filter($all_topics)) > 0 ? 'true' : 'false'; ?>,
        dataSource: 'questions_generator_template' // Identifier for debugging
    };
    
    console.log('✅ Questions Generator: Standardized data loaded into window.MKCG_Questions_Data', window.MKCG_Questions_Data);
    
    // Data validation for standardized structure
    function validateQuestionsDataStructure(data) {
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
    const validation = validateQuestionsDataStructure(window.MKCG_Questions_Data);
    if (!validation.valid) {
        console.error('🚨 Questions Generator: Data structure validation failed:', validation.issues);
    } else {
        console.log('✅ Questions Generator: Data structure validation passed (source: ' + validation.dataSource + ')');
    }
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // Enhanced initialization with error handling and debugging
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Questions Generator: DOM ready, initializing with unified BEM architecture');
        
        try {
            if (typeof QuestionsGenerator !== 'undefined') {
                QuestionsGenerator.init();
            } else {
                console.error('Questions Generator: QuestionsGenerator script not loaded correctly');
                
                // Show user-friendly error with unified styling
                const errorDiv = document.createElement('div');
                errorDiv.className = 'generator__toast generator__toast--error';
                errorDiv.innerHTML = `
                    <strong>⚠️ Script Loading Error</strong><br>
                    The Questions Generator failed to load properly. Please refresh the page.
                    <br><br>
                    <button onclick="location.reload()" class="generator__button generator__button--primary">Refresh Page</button>
                `;
                
                const container = document.querySelector('.questions-generator');
                if (container) {
                    container.insertBefore(errorDiv, container.firstChild);
                }
            }
        } catch (error) {
            console.error('Questions Generator: Critical initialization error:', error);
        }
    });
    
    // Debug information for development
    if (typeof console !== 'undefined') {
        console.log('Questions Generator: Unified BEM architecture data loaded successfully', {
            topics: Object.keys(window.MKCG_Questions_Data.topics).length,
            questions: Object.keys(window.MKCG_Questions_Data.questions).length,
            entryId: window.MKCG_Questions_Data.entryId,
            dataSource: window.MKCG_Questions_Data.dataSource
        });
    }
</script>
