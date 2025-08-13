<?php
/**
 * Topics Generator Template - BEM Methodology
 * Modern design with proper BEM class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ✅ Trigger asset loading for templates accessed directly
do_action('mkcg_shortcode_detected', 'mkcg_topics');
do_action('mkcg_generator_loaded', 'topics');

// CLEAN CODE: Simple data loading - no parameter checking needed
$template_data = [];
$debug_info = [];

// Primary Method: Try to get data from generator instance
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    $debug_info[] = '✅ Got data from generator instance';
    error_log('MKCG Topics Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Generator instance not available';
    
    // Fallback Method: Try direct Pods service
    if (class_exists('MKCG_Pods_Service')) {
        $pods_service = new MKCG_Pods_Service();
        
        // Try to get post ID from various sources
        $post_id = 0;
        if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            $post_id = intval($_GET['post_id']);
            $debug_info[] = "📍 Using post_id from URL: {$post_id}";
        } else if (isset($_GET['entry']) && intval($_GET['entry']) > 0) {
            $post_id = intval($_GET['entry']);
            $debug_info[] = "📍 Using entry from URL: {$post_id}";
        } else {
            // Get the most recent guest post for testing
            $recent_guest = get_posts([
                'post_type' => 'guests',
                'post_status' => 'publish',
                'numberposts' => 1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            if (!empty($recent_guest)) {
                $post_id = $recent_guest[0]->ID;
                $debug_info[] = "🎯 Using most recent guest post: {$post_id}";
            }
        }
        
        if ($post_id > 0) {
            $guest_data = $pods_service->get_guest_data($post_id);
            $template_data = [
                'post_id' => $post_id,
                'authority_hook_components' => $guest_data['authority_hook_components'],
                'form_field_values' => $guest_data['topics'],
                'has_data' => $guest_data['has_data']
            ];
            $debug_info[] = "✅ Loaded data via direct Pods service";
            $debug_info[] = "📊 Topics found: " . count(array_filter($guest_data['topics']));
            $debug_info[] = "🔑 Authority hook WHO: " . $guest_data['authority_hook_components']['who'];
        } else {
            $debug_info[] = "❌ No valid post ID found";
        }
    } else {
        $debug_info[] = "❌ MKCG_Pods_Service not available";
    }
    
    // Fallback: Create empty structure when no data found
    if (empty($template_data)) {
        $template_data = [
            'post_id' => 0,
            'authority_hook_components' => [
                'who' => '',
                'what' => '',
                'when' => '',
                'how' => '',
                'complete' => ''
            ],
            'form_field_values' => [
                'topic_1' => '',
                'topic_2' => '',
                'topic_3' => '',
                'topic_4' => '',
                'topic_5' => ''
            ],
            'has_data' => false
        ];
        $debug_info[] = "⚠️ Using empty structure (no data found)";
    }
    
    error_log('MKCG Topics Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$form_field_values = $template_data['form_field_values'];
$has_data = $template_data['has_data'];

// CRITICAL DEBUG: Log the actual authority hook data
error_log('MKCG Topics Template: Authority Hook Components: ' . json_encode($authority_hook_components));
error_log('MKCG Topics Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container topics-generator" data-generator="topics">
    <div class="generator__header">
        <h1 class="generator__title">Create Your Interview Topics</h1>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="topics-generator__intro">
                    Generate 5 compelling podcast interview topics based on your authority hook and target audience. 
                    Topics will be tailored to highlight your expertise and attract podcast hosts.
                </p>
                
            <!-- Authority Hook Result -->
            <div class="generator__authority-hook">
                <div class="generator__authority-hook-header">
                    <span class="generator__authority-hook-icon">★</span>
                    <h3 class="generator__authority-hook-title">Your Authority Hook</h3>
                    <span class="generator__badge">AI GENERATED</span>
                </div>
                
                <div class="generator__authority-hook-content">
                    <p id="topics-generator-authority-hook-text"><?php 
                        // CLEAN CODE: Show text only when all components have real data
$all_components_exist = !empty($authority_hook_components['who']) && 
                        !empty($authority_hook_components['what']) && 
                        !empty($authority_hook_components['when']) && 
                        !empty($authority_hook_components['how']);

if ($all_components_exist) {
                        echo esc_html($authority_hook_components['complete']);
}
// Empty when incomplete - no defaults
                    ?></p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <!-- Generate Button -->
                    <button class="generator__button generator__button--secondary" id="topics-generator-generate-topics">
                        Generate Topics with AI
                    </button>
                    <button type="button" class="generator__button generator__button--outline" id="topics-generator-toggle-builder">
                        Edit Components
                    </button>
                    </div>
                </div>
                
            <!-- Authority Hook Builder - CENTRALIZED SERVICE -->                
            <div class="generator__builder generator__builder--hidden mkcg-authority-hook authority-hook-builder" id="topics-generator-authority-hook-builder" data-component="authority-hook">
            <?php 
            // ROOT FIX: Ensure Authority Hook Service is properly loaded and configured for clean slate
            
            // First, try to get from globals
            $authority_hook_service = null;
            if (isset($GLOBALS['authority_hook_service'])) {
                $authority_hook_service = $GLOBALS['authority_hook_service'];
                error_log('MKCG Topics Template: Using global authority_hook_service');
            }
            
            // If not available, create new instance
            if (!$authority_hook_service || !is_object($authority_hook_service)) {
                // Ensure the class is loaded
                if (!class_exists('MKCG_Authority_Hook_Service')) {
                    // Check if plugin constant is defined
                    if (defined('MKCG_PLUGIN_PATH')) {
                        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';
                    } else {
                        // Fallback path calculation
                        $plugin_path = dirname(dirname(dirname(__FILE__))) . '/';
                        require_once $plugin_path . 'includes/services/class-mkcg-authority-hook-service.php';
                        error_log('MKCG Topics Template: Used fallback path for Authority Hook Service');
                    }
                }
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                $GLOBALS['authority_hook_service'] = $authority_hook_service;
                error_log('MKCG Topics Template: Created new authority_hook_service instance');
            }
            
            // CLEAN CODE: Pass values as-is to Authority Hook Service
            $current_values = [
                'who' => $authority_hook_components['who'] ?? '',
                'what' => $authority_hook_components['what'] ?? '', 
                'when' => $authority_hook_components['when'] ?? '',
                'how' => $authority_hook_components['how'] ?? ''
            ];
            
            error_log('MKCG Topics Template: Authority Hook Components: ' . json_encode($authority_hook_components));
            error_log('MKCG Topics Template: Current Values: ' . json_encode($current_values));
                
                    // CLEAN CODE: Render options for Topics Generator
                    $render_options = [
                        'show_preview' => false, // No preview in topics generator
                        'show_examples' => true,
                        'show_audience_manager' => true,
                        'css_classes' => 'authority-hook',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // CLEAN CODE: Render the Authority Hook Builder
                    error_log('MKCG Topics Template: About to render authority hook builder');
                    error_log('MKCG Topics Template: Service class: ' . get_class($authority_hook_service));
                    try {
                        $rendered_output = $authority_hook_service->render_authority_hook_builder('topics', $current_values, $render_options);
                        if (empty($rendered_output)) {
                            error_log('MKCG Topics Template: WARNING - Authority hook builder returned empty output');
                            // Fallback to simple form
                            echo '<div class="authority-hook-fallback" style="padding: 20px; border: 2px solid red; background: #ffe6e6;">';
                            echo '<h3>Authority Hook Builder (Fallback Mode)</h3>';
                            echo '<p>Service failed to render. Using fallback form.</p>';
                            echo '<div class="field"><label>WHO:</label><input type="text" id="mkcg-who" value="' . esc_attr($current_values['who']) . '"></div>';
                            echo '<div class="field"><label>WHAT:</label><input type="text" id="mkcg-result" value="' . esc_attr($current_values['what']) . '"></div>';
                            echo '<div class="field"><label>WHEN:</label><input type="text" id="mkcg-when" value="' . esc_attr($current_values['when']) . '"></div>';
                            echo '<div class="field"><label>HOW:</label><input type="text" id="mkcg-how" value="' . esc_attr($current_values['how']) . '"></div>';
                            echo '</div>';
                        } else {
                            echo $rendered_output;
                            error_log('MKCG Topics Template: Authority hook builder rendered successfully (' . strlen($rendered_output) . ' characters)');
                        }
                    } catch (Exception $e) {
                        error_log('MKCG Topics Template: ERROR rendering authority hook builder: ' . $e->getMessage());
                        // Emergency fallback
                        echo '<div class="authority-hook-error" style="padding: 20px; border: 2px solid red; background: #ffe6e6;">';
                        echo '<h3>Authority Hook Builder (Error)</h3>';
                        echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
                        echo '<div class="field"><label>WHO:</label><input type="text" id="mkcg-who" value="' . esc_attr($current_values['who']) . '"></div>';
                        echo '<div class="field"><label>WHAT:</label><input type="text" id="mkcg-result" value="' . esc_attr($current_values['what']) . '"></div>';
                        echo '<div class="field"><label>WHEN:</label><input type="text" id="mkcg-when" value="' . esc_attr($current_values['when']) . '"></div>';
                        echo '<div class="field"><label>HOW:</label><input type="text" id="mkcg-how" value="' . esc_attr($current_values['how']) . '"></div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
            <!-- Loading indicator -->
            <div class="generator__loading generator__loading--hidden" id="topics-generator-loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                    </svg>
                    Generating topics...
                </div>
                
            <!-- Topics result - with "Use" buttons -->
            <div class="generator__results generator__results--hidden" id="topics-generator-topics-result">
                <div class="topics-generator__topics-list" id="topics-generator-topics-list">
                        <!-- Generated topics will be listed here with "Use" buttons -->
                    </div>
                </div>
                
                <!-- Topics Display Container with Editable Form Fields -->
                <div class="topics-generator__topics-container" id="topics-generator-topics-container">
                    <div class="topics-generator__topics-header">
                        <h3 id="topics-generator-topics-heading">Interview Topics for Your Authority Hook</h3>
                        <p class="topics-generator__topics-subheading">Five compelling podcast interview topics</p>
                    </div>
                    
                    <div class="topics-generator__topics-display">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="topics-generator__topic-item" id="topics-generator-topic-item-<?php echo $i; ?>">
                                <div class="topics-generator__topic-number">
                                    <?php echo $i; ?>
                                </div>
                                <div class="topics-generator__topic-content">
                                    <input type="text" 
                                           class="topics-generator__topic-input mkcg-topic-field" 
                                           id="topics-generator-topic-field-<?php echo $i; ?>"
                                           name="field_<?php echo 8497 + $i; ?>" 
                                           data-field-id="<?php echo 8497 + $i; ?>" 
                                           data-topic-number="<?php echo $i; ?>"
                                           data-field-type="topic"
                                           placeholder="<?php echo $i == 5 ? 'Click to add your interview topic' : 'Enter your interview topic ' . $i; ?>"
                                           value="<?php echo esc_attr($form_field_values['topic_' . $i] ?? ''); ?>">
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Enhanced Save Section - Comprehensive Save to Both Locations -->
                    <div class="generator__save-section">
                        <!-- Prominent Save Button with Enhanced Functionality -->
                        <button class="generator__button--call-to-action" id="topics-generator-save-topics" type="button">
                            💾 Save All Topics & Authority Hook
                        </button>
                        
                        <!-- Descriptive Help Text -->
                        <div class="topics-generator__save-description">
                            <p class="topics-generator__save-help">
                                💡 <strong>Comprehensive Save:</strong> Saves all 5 topics and 4 authority hook components to both WordPress custom post meta and Formidable entry fields in a single atomic operation.
                            </p>
                        </div>
                        
                        <!-- Dual-Location Status Indicators -->
                        <div class="topics-generator__save-status-container" id="topics-generator-save-status-container" style="display: none;">
                            <!-- WordPress Status -->
                            <div class="topics-generator__save-status topics-generator__save-status--wordpress" id="topics-generator-save-status-wordpress">
                                <span class="topics-generator__save-status-icon">🔄</span>
                                <span class="topics-generator__save-status-label">WordPress:</span>
                                <span class="topics-generator__save-status-text">Preparing...</span>
                            </div>
                            
                            <!-- Formidable Status -->
                            <div class="topics-generator__save-status topics-generator__save-status--formidable" id="topics-generator-save-status-formidable">
                                <span class="topics-generator__save-status-icon">🔄</span>
                                <span class="topics-generator__save-status-label">Formidable:</span>
                                <span class="topics-generator__save-status-text">Preparing...</span>
                            </div>
                            
                            <!-- Overall Progress Indicator -->
                            <div class="topics-generator__save-progress" id="topics-generator-save-progress">
                                <div class="topics-generator__save-progress-bar">
                                    <div class="topics-generator__save-progress-fill" id="topics-generator-save-progress-fill"></div>
                                </div>
                                <div class="topics-generator__save-progress-text" id="topics-generator-save-progress-text">
                                    Initializing comprehensive save operation...
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timestamp of Last Successful Save -->
                        <div class="topics-generator__save-timestamp" id="topics-generator-save-timestamp" style="display: none;">
                            <small class="topics-generator__save-timestamp-text">
                                🕒 Last saved: <span id="topics-generator-save-timestamp-value">Never</span>
                            </small>
                        </div>
                        
                        <!-- Enhanced Error/Success Messages -->
                        <div class="topics-generator__save-messages" id="topics-generator-save-messages"></div>
                    </div>
                </div>
                
            <!-- Field Selection Modal -->
            <div class="generator__modal" id="topics-generator-field-modal">
                <div class="generator__modal-content">
                    <div class="generator__modal-header">
                        <h3 class="generator__modal-title">Enter the field number to update (1-5):</h3>
                    </div>
                    <input type="number" min="1" max="5" class="generator__field-input" id="topics-generator-field-number" value="1">
                    <div class="generator__modal-actions">
                        <button class="generator__button generator__button--primary" id="topics-generator-modal-ok">OK</button>
                        <button class="generator__button generator__button--outline" id="topics-generator-modal-cancel">Cancel</button>
                    </div>
                    </div>
                </div>
                
            <!-- Hidden fields for AJAX - Pure Pods -->
            <input type="hidden" id="topics-generator-post-id" value="<?php echo esc_attr($post_id); ?>">
            <input type="hidden" id="topics-generator-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
            
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
                <h2 class="generator__guidance-header">Crafting Perfect Interview Topics</h2>
                <p class="generator__guidance-subtitle">Strong interview topics provide value to listeners, suggest a structure for the conversation, and showcase your expertise. They should be focused on one concept at a time while remaining general enough to allow for discussion.</p>
                
                <div class="generator__formula-box">
                    <span class="generator__formula-label">APPROACH</span>
                    Provide <span class="generator__highlight">solutions</span> that focus on <span class="generator__highlight">one concept</span> per topic while remaining <span class="generator__highlight">general enough</span> to expand upon.
                </div>
                
                <div class="generator__process-step">
                    <div class="generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <div class="generator__process-content">
                        <h3 class="generator__process-title">Focus on Value for Listeners</h3>
                        <p class="generator__process-description">
                            Great topics provide actionable solutions that listeners can implement. Think about your audience's pain points and how your knowledge can help solve their problems. Avoid overly promotional topics and focus on delivering value first.
                        </p>
                    </div>
                </div>
                
                <div class="generator__process-step">
                    <div class="generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="generator__process-content">
                        <h3 class="generator__process-title">One Concept per Topic</h3>
                        <p class="generator__process-description">
                            Each topic should focus on a single concept, similar to a blog post. This makes it easier for hosts to structure the interview and helps listeners follow along. You'll have the opportunity to go into detail during the conversation.
                        </p>
                    </div>
                </div>
                
                <div class="generator__process-step">
                    <div class="generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="generator__process-content">
                        <h3 class="generator__process-title">Tailored to the Audience</h3>
                        <p class="generator__process-description">
                            While you can have core topics you're prepared to discuss, the best approach is to tailor them to each podcast's specific audience. Research the show beforehand and adjust your topics to align with what their listeners would find most valuable.
                        </p>
                    </div>
                </div>
                
                <h3 class="generator__examples-header">Example Topic Sets:</h3>
                
                <div class="generator__example-card">
                    <strong>For a Marketing Podcast:</strong>
                    <p>1. The 3-step framework for landing high-profile podcast interviews</p>
                    <p>2. How to craft a compelling story that makes you memorable</p>
                    <p>3. Converting podcast appearances into high-ticket clients</p>
                </div>
                
                <div class="generator__example-card">
                    <strong>For a Business Growth Podcast:</strong>
                    <p>1. The 5 most common mistakes when scaling SaaS businesses</p>
                    <p>2. Building a team that can operate without your daily involvement</p>
                    <p>3. Creating systems that allow for sustainable growth</p>
                </div>
                
                <div class="generator__example-card">
                    <strong>For an Author/Content Creator Podcast:</strong>
                    <p>1. Turning your expertise into a bestselling book</p>
                    <p>2. Building an audience that eagerly awaits your content</p>
                    <p>3. Leveraging your book to open doors to speaking and media opportunities</p>
                </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // ✅ Assets are now loaded by Asset Manager - simplified data preparation
    
    // Pass PHP data to JavaScript for when assets are loaded
    window.MKCG_Topics_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who'] ?? ''); ?>',
            what: '<?php echo esc_js($authority_hook_components['what'] ?? ''); ?>',
            when: '<?php echo esc_js($authority_hook_components['when'] ?? ''); ?>',
            how: '<?php echo esc_js($authority_hook_components['how'] ?? ''); ?>',
            complete: '<?php echo esc_js($authority_hook_components['complete'] ?? ''); ?>'
        },
        topics: {
            topic_1: '<?php echo esc_js($form_field_values['topic_1'] ?? ''); ?>',
            topic_2: '<?php echo esc_js($form_field_values['topic_2'] ?? ''); ?>',
            topic_3: '<?php echo esc_js($form_field_values['topic_3'] ?? ''); ?>',
            topic_4: '<?php echo esc_js($form_field_values['topic_4'] ?? ''); ?>',
            topic_5: '<?php echo esc_js($form_field_values['topic_5'] ?? ''); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>'
    };
    
    console.log('✅ MKCG Topics: Data prepared for Asset Manager', window.MKCG_Topics_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
</script>