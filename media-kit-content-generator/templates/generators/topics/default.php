<?php
/**
 * Topics Generator Template - BEM Methodology
 * Modern design with proper BEM class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>



<?php

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
    // MKCG Debug Info
    console.log('🎯 MKCG Topics: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // ENHANCED DEBUG: Show what authority hook data we're passing to JavaScript
    console.log('🔍 Authority Hook Components from PHP:', <?php echo json_encode($authority_hook_components); ?>);
    console.log('🔍 Current URL parameters:', {
        entry: '<?php echo esc_js($_GET['entry'] ?? ''); ?>',
        post_id: '<?php echo esc_js($_GET['post_id'] ?? ''); ?>',
        frm_action: '<?php echo esc_js($_GET['frm_action'] ?? ''); ?>'
    });
    
    // CLEAN CODE: Template data - always empty defaults, loads real data if exists
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
    
    console.log('✅ MKCG Topics: Final data loaded', window.MKCG_Topics_Data);
    
    // ENHANCED DEBUG: Add debug helpers to window
    window.MKCG_Debug = {
        checkAuthorityHook: function() {
            console.log('🔍 Authority Hook Debug Check:');
            console.log('1. Template Data:', window.MKCG_Topics_Data);
            console.log('2. Authority Hook Builder Available:', typeof window.AuthorityHookBuilder);
            
            if (window.AuthorityHookBuilder && window.AuthorityHookBuilder.debug) {
                window.AuthorityHookBuilder.debug.checkFields();
            }
            
            // Check if Authority Hook Service is working
            const postId = window.MKCG_Topics_Data.postId;
            console.log('3. Post ID from template:', postId);
            
            // Check for Authority Hook display elements
            const displayElement = document.getElementById('topics-generator-authority-hook-text');
            console.log('4. Authority Hook display element:', displayElement);
            if (displayElement) {
                console.log('   Current text:', displayElement.textContent);
            }
        },
        reloadAuthorityHook: function() {
            console.log('🔄 Attempting to reload Authority Hook data...');
            if (window.AuthorityHookBuilder && window.AuthorityHookBuilder.debug) {
                window.AuthorityHookBuilder.debug.rePopulate();
            }
        }
    };
    
    console.log('🔧 Debug helpers added to window.MKCG_Debug');
    console.log('   Run window.MKCG_Debug.checkAuthorityHook() to debug');
    console.log('   Run window.MKCG_Debug.reloadAuthorityHook() to retry population');
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Topics_Data.hasData) {
        console.log('📋 MKCG Topics: Data found - should populate automatically');
        
        // ROOT FIX: Check if authority hook text element exists and populate if needed
        const hookText = document.getElementById('topics-generator-authority-hook-text');
        if (hookText) {
            console.log('✅ Authority hook element found with text:', hookText.textContent);
            
            // ROOT FIX: If element is empty but we have authority hook data, populate it
            if (!hookText.textContent.trim() && window.MKCG_Topics_Data.authorityHook.complete) {
                hookText.textContent = window.MKCG_Topics_Data.authorityHook.complete;
                console.log('✅ Populated empty authority hook element with template data');
            }
        } else {
            console.error('❌ Authority hook element not found - check selector mismatch');
        }
        
        // Check if topic fields exist and have values
        for (let i = 1; i <= 5; i++) {
            const field = document.getElementById(`topics-generator-topic-field-${i}`);
            if (field) {
                console.log(`✅ Topic ${i} field found with value:`, field.value);
            } else {
                console.error(`❌ Topic ${i} field not found`);
            }
        }
        
    } else {
        console.log('⚠️ MKCG Topics: No data found - using defaults');
    }
    
    // COMPREHENSIVE SAVE: Initialize when DOM is ready - delegated to main JavaScript file
    // The saveAllTopics functionality is now handled by topics-generator.js
    // which already has enhanced UI feedback integration
    
    // Global reference for backward compatibility
    window.saveAllTopicsEnhanced = function() {
        console.log('🔄 Delegating to TopicsGenerator.saveAllTopics()');
        if (window.TopicsGenerator && typeof window.TopicsGenerator.saveAllTopics === 'function') {
            window.TopicsGenerator.saveAllTopics();
        } else {
            console.warn('⚠️ TopicsGenerator not initialized yet');
        }
    };
    
    // CRITICAL FIX: Authority Hook Pre-population Enhancement
    // Ensure fields are populated immediately if data exists
    if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.authorityHook) {
        console.log('🔧 IMMEDIATE POPULATION: Setting up authority hook fields with data');
        
        // Set up a function to populate the fields once they exist
        const populateAuthorityHookFields = function() {
            const fieldMappings = [
                { field: 'who', selector: '#mkcg-who' },
                { field: 'what', selector: '#mkcg-result' },
                { field: 'when', selector: '#mkcg-when' },
                { field: 'how', selector: '#mkcg-how' }
            ];
            
            let fieldsFound = 0;
            let fieldsPopulated = 0;
            
            fieldMappings.forEach(({ field, selector }) => {
                const input = document.querySelector(selector);
                if (input) {
                    fieldsFound++;
                    const value = window.MKCG_Topics_Data.authorityHook[field] || '';
                    if (value && value.trim()) {
                        input.value = value;
                        fieldsPopulated++;
                        console.log(`✅ Populated ${selector} with: "${value}"`);
                        
                        // Trigger events for other scripts
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        console.log(`⚠️ No value for ${selector} (${field}): "${value}"`);
                    }
                } else {
                    console.error(`❌ Field not found: ${selector}`);
                }
            });
            
            console.log(`🔧 POPULATION COMPLETE: Found ${fieldsFound}/4 fields, populated ${fieldsPopulated} fields`);
            
            // Update the main authority hook display
            const hookDisplayElement = document.getElementById('topics-generator-authority-hook-text');
            if (hookDisplayElement && window.MKCG_Topics_Data.authorityHook.complete) {
                hookDisplayElement.textContent = window.MKCG_Topics_Data.authorityHook.complete;
                console.log('✅ Updated main authority hook display');
            }
            
            return fieldsFound === 4;
        };
        
        // Try immediate population
        const immediateSuccess = populateAuthorityHookFields();
        
        if (!immediateSuccess) {
            console.log('🔄 Fields not ready immediately, setting up observers...');
            
            // Set up mutation observer to watch for fields being added
            const observer = new MutationObserver(function(mutations) {
                let shouldTryAgain = false;
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if any of our target fields were added
                        for (let node of mutation.addedNodes) {
                            if (node.nodeType === 1) { // Element node
                                if (node.id && ['mkcg-who', 'mkcg-result', 'mkcg-when', 'mkcg-how'].includes(node.id)) {
                                    shouldTryAgain = true;
                                    break;
                                }
                                // Also check descendants
                                if (node.querySelector && node.querySelector('#mkcg-who, #mkcg-result, #mkcg-when, #mkcg-how')) {
                                    shouldTryAgain = true;
                                    break;
                                }
                            }
                        }
                    }
                });
                
                if (shouldTryAgain) {
                    console.log('🔄 Authority hook fields detected, attempting population...');
                    const success = populateAuthorityHookFields();
                    if (success) {
                        console.log('✅ Population successful, disconnecting observer');
                        observer.disconnect();
                    }
                }
            });
            
            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Stop observing after 10 seconds
            setTimeout(() => {
                observer.disconnect();
                console.log('⏰ Authority hook field observer timed out');
            }, 10000);
            
            // Also try again after a delay
            setTimeout(() => {
                console.log('🔄 Retry population after 1 second...');
                populateAuthorityHookFields();
            }, 1000);
            
            setTimeout(() => {
                console.log('🔄 Final retry population after 3 seconds...');
                populateAuthorityHookFields();
            }, 3000);
        }
    } else {
        console.log('⚠️ No authority hook data available for immediate population');
    }
    
    // ENHANCED: Real-time Authority Hook display updates handled by centralized service
    // Update the main display element when Authority Hook changes
    document.addEventListener('authority-hook-updated', function(e) {
        const displayElement = document.getElementById('topics-generator-authority-hook-text');
        if (displayElement && e.detail.completeHook) {
            displayElement.textContent = e.detail.completeHook;
            console.log('✅ Authority hook display updated via event:', e.detail.completeHook);
        }
    });
    
    // CRITICAL DEBUG: Set up manual debugging functions
    window.MKCG_Topics_Debug = {
        checkFields: function() {
            console.log('🔍 DEBUGGING: Checking authority hook fields...');
            const fields = ['mkcg-who', 'mkcg-result', 'mkcg-when', 'mkcg-how'];
            fields.forEach(id => {
                const field = document.getElementById(id);
                if (field) {
                    console.log(`✅ Field ${id}: found, value = "${field.value}"`);
                } else {
                    console.log(`❌ Field ${id}: NOT FOUND`);
                }
            });
            
            // Check authority hook display
            const display = document.getElementById('topics-generator-authority-hook-text');
            if (display) {
                console.log(`✅ Authority hook display: found, text = "${display.textContent}"`);
            } else {
                console.log(`❌ Authority hook display: NOT FOUND`);
            }
            
            // Check if builder is visible
            const builder = document.getElementById('topics-generator-authority-hook-builder');
            if (builder) {
                const isHidden = builder.classList.contains('generator__builder--hidden');
                console.log(`✅ Authority hook builder: found, hidden = ${isHidden}`);
            } else {
                console.log(`❌ Authority hook builder: NOT FOUND`);
            }
        },
        
        forcePopulate: function() {
            console.log('🔄 DEBUGGING: Force populating fields...');
            if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.authorityHook) {
                const data = window.MKCG_Topics_Data.authorityHook;
                const fieldMappings = [
                    { field: 'who', selector: '#mkcg-who' },
                    { field: 'what', selector: '#mkcg-result' },
                    { field: 'when', selector: '#mkcg-when' },
                    { field: 'how', selector: '#mkcg-how' }
                ];
                
                fieldMappings.forEach(({ field, selector }) => {
                    const input = document.querySelector(selector);
                    if (input && data[field]) {
                        input.value = data[field];
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        console.log(`🔄 Force populated ${selector} with: "${data[field]}"`);
                    }
                });
                
                // Update display
                if (data.complete) {
                    const display = document.getElementById('topics-generator-authority-hook-text');
                    if (display) {
                        display.textContent = data.complete;
                        console.log('🔄 Force updated display');
                    }
                }
            } else {
                console.log('❌ No data available to populate');
            }
        },
        
        showBuilder: function() {
            const builder = document.getElementById('topics-generator-authority-hook-builder');
            if (builder) {
                builder.classList.remove('generator__builder--hidden');
                console.log('✅ Builder shown');
            } else {
                console.log('❌ Builder not found');
            }
        }
    };
    
    console.log('🔧 Debug functions available: window.MKCG_Topics_Debug.checkFields(), .forcePopulate(), .showBuilder()');
    
    console.log('✅ MKCG Topics: Template loaded - Enhanced debugging and population fixes applied');
    
    // IMMEDIATE CHECK: Run debug check after a short delay
    setTimeout(() => {
        console.log('🔍 IMMEDIATE DEBUG CHECK:');
        if (window.MKCG_Topics_Debug) {
            window.MKCG_Topics_Debug.checkFields();
        }
    }, 500);
    
    // CRITICAL FIX: Authority Hook Hidden Field Auto-Population
    (function() {
        'use strict';
        
        console.log('🔧 HIDDEN FIELD FIX: Setting up Authority Hook auto-population on builder show');
        
        let fieldsPopulated = false;
        
        const populateFieldsWhenVisible = function() {
            if (fieldsPopulated) return;
            
            if (!window.MKCG_Topics_Data || !window.MKCG_Topics_Data.authorityHook) {
                console.log('❌ No authority hook data available');
                return;
            }
            
            const data = window.MKCG_Topics_Data.authorityHook;
            const fieldMappings = [
                { field: 'who', selector: '#mkcg-who' },
                { field: 'what', selector: '#mkcg-result' },
                { field: 'when', selector: '#mkcg-when' },
                { field: 'how', selector: '#mkcg-how' }
            ];
            
            let populatedCount = 0;
            
            fieldMappings.forEach(({ field, selector }) => {
                const input = document.querySelector(selector);
                if (input && data[field] && data[field].trim()) {
                    if (!input.value || input.value.trim() === '') {
                        input.value = data[field];
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        populatedCount++;
                        console.log(`✅ AUTO-POPULATED ${selector} with: "${data[field]}"`);
                    }
                }
            });
            
            if (populatedCount > 0) {
                fieldsPopulated = true;
                console.log(`🎉 SUCCESS: Auto-populated ${populatedCount} authority hook fields!`);
                
                const display = document.getElementById('topics-generator-authority-hook-text');
                if (display && data.complete) {
                    display.textContent = data.complete;
                }
            }
        };
        
        // Listen for "Edit Components" button click
        const setupButtonListener = function() {
            const toggleButton = document.getElementById('topics-generator-toggle-builder');
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    console.log('🔧 "Edit Components" clicked - will populate fields when builder shows');
                    setTimeout(() => {
                        const builder = document.getElementById('topics-generator-authority-hook-builder');
                        if (builder && !builder.classList.contains('generator__builder--hidden')) {
                            populateFieldsWhenVisible();
                        }
                    }, 100);
                });
                console.log('✅ "Edit Components" button listener attached');
            }
        };
        
        // Watch for builder visibility changes
        const setupVisibilityWatcher = function() {
            const builder = document.getElementById('topics-generator-authority-hook-builder');
            if (builder) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const target = mutation.target;
                            if (target.id === 'topics-generator-authority-hook-builder') {
                                if (!target.classList.contains('generator__builder--hidden')) {
                                    setTimeout(populateFieldsWhenVisible, 50);
                                }
                            }
                        }
                    });
                });
                
                observer.observe(builder, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        };
        
        // Initialize
        const init = function() {
            setupButtonListener();
            setupVisibilityWatcher();
            
            // Add to debug functions
            if (window.MKCG_Topics_Debug) {
                window.MKCG_Topics_Debug.autoPopulate = populateFieldsWhenVisible;
            }
        };
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        
        setTimeout(init, 500);
        
    })();
    
    // Manual test function
    window.testAuthorityHookPopulation = function() {
        const builder = document.getElementById('topics-generator-authority-hook-builder');
        if (builder) {
            builder.classList.remove('generator__builder--hidden');
        }
        setTimeout(() => {
            if (window.MKCG_Topics_Debug && window.MKCG_Topics_Debug.forcePopulate) {
                window.MKCG_Topics_Debug.forcePopulate();
            }
        }, 200);
    };
    
    // CRITICAL FIX: Authority Hook Hidden Field Population
    // Auto-populate fields when Authority Hook Builder becomes visible
    (function() {
        'use strict';
        
        console.log('🔧 HIDDEN FIELD FIX: Setting up Authority Hook auto-population on builder show');
        
        // Flag to prevent duplicate population
        let fieldsPopulated = false;
        
        // The working population function (from your successful force populate)
        const populateFieldsWhenVisible = function() {
            if (fieldsPopulated) {
                console.log('⚠️ Fields already populated, skipping...');
                return;
            }
            
            if (!window.MKCG_Topics_Data || !window.MKCG_Topics_Data.authorityHook) {
                console.log('❌ No authority hook data available');
                return;
            }
            
            const data = window.MKCG_Topics_Data.authorityHook;
            const fieldMappings = [
                { field: 'who', selector: '#mkcg-who' },
                { field: 'what', selector: '#mkcg-result' },
                { field: 'when', selector: '#mkcg-when' },
                { field: 'how', selector: '#mkcg-how' }
            ];
            
            let populatedCount = 0;
            
            fieldMappings.forEach(({ field, selector }) => {
                const input = document.querySelector(selector);
                if (input && data[field] && data[field].trim()) {
                    // Only populate if field is empty
                    if (!input.value || input.value.trim() === '') {
                        input.value = data[field];
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        populatedCount++;
                        console.log(`✅ AUTO-POPULATED ${selector} with: "${data[field]}"`);
                    }
                }
            });
            
            if (populatedCount > 0) {
                fieldsPopulated = true;
                console.log(`🎉 SUCCESS: Auto-populated ${populatedCount} authority hook fields!`);
                
                // Update the main display
                const display = document.getElementById('topics-generator-authority-hook-text');
                if (display && data.complete) {
                    display.textContent = data.complete;
                    console.log('✅ Updated main authority hook display');
                }
            }
        };
        
        // SOLUTION 1: Listen for "Edit Components" button click
        const setupButtonListener = function() {
            const toggleButton = document.getElementById('topics-generator-toggle-builder');
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    console.log('🔧 "Edit Components" clicked - will populate fields when builder shows');
                    
                    // Wait for builder to become visible, then populate
                    setTimeout(() => {
                        const builder = document.getElementById('topics-generator-authority-hook-builder');
                        if (builder && !builder.classList.contains('generator__builder--hidden')) {
                            console.log('🔧 Builder is now visible, populating fields...');
                            populateFieldsWhenVisible();
                        }
                    }, 100);
                });
                console.log('✅ "Edit Components" button listener attached');
            } else {
                console.log('⚠️ "Edit Components" button not found, will retry...');
            }
        };
        
        // SOLUTION 2: Watch for builder visibility changes
        const setupVisibilityWatcher = function() {
            const builder = document.getElementById('topics-generator-authority-hook-builder');
            if (builder) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const target = mutation.target;
                            if (target.id === 'topics-generator-authority-hook-builder') {
                                if (!target.classList.contains('generator__builder--hidden')) {
                                    console.log('🔧 Authority Hook Builder became visible, auto-populating...');
                                    setTimeout(populateFieldsWhenVisible, 50);
                                }
                            }
                        }
                    });
                });
                
                observer.observe(builder, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                
                console.log('✅ Builder visibility watcher set up');
            }
        };
        
        // SOLUTION 3: Try immediate population if builder is already visible
        const tryImmediatePopulation = function() {
            const builder = document.getElementById('topics-generator-authority-hook-builder');
            if (builder && !builder.classList.contains('generator__builder--hidden')) {
                console.log('🔧 Builder already visible, attempting immediate population...');
                populateFieldsWhenVisible();
            } else {
                console.log('🔧 Builder currently hidden, waiting for user to show it...');
            }
        };
        
        // Initialize all solutions
        const init = function() {
            console.log('🔧 Initializing hidden field population fix...');
            
            setupButtonListener();
            setupVisibilityWatcher();
            tryImmediatePopulation();
            
            // Add manual trigger to debug object
            if (window.MKCG_Topics_Debug) {
                window.MKCG_Topics_Debug.autoPopulate = populateFieldsWhenVisible;
                console.log('🔧 Added autoPopulate() to debug functions');
            }
        };
        
        // Run when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        
        // Also try after delays
        setTimeout(init, 500);
        setTimeout(init, 2000);
        
    })();
    
    // MANUAL TEST FUNCTION
    window.testAuthorityHookPopulation = function() {
        console.log('🧪 MANUAL TEST: Testing authority hook population...');
        
        // Show the builder first
        const builder = document.getElementById('topics-generator-authority-hook-builder');
        if (builder) {
            builder.classList.remove('generator__builder--hidden');
            console.log('✅ Builder shown');
        }
        
        // Wait a moment, then populate
        setTimeout(() => {
            if (window.MKCG_Topics_Debug && window.MKCG_Topics_Debug.forcePopulate) {
                window.MKCG_Topics_Debug.forcePopulate();
                console.log('✅ Force populate triggered');
            }
        }, 200);
    };
    
    console.log('🔧 Hidden field fix loaded. Test with: window.testAuthorityHookPopulation()');
</script>

<!-- Authority Hook Builder functionality is now handled by topics-generator.js - duplicate script removed -->

<!-- Initialize enhanced elements check -->
<script>
// Check if enhanced elements loaded
setTimeout(() => {
    const credentialsManager = document.querySelector('.credentials-manager');
    const addToListButtons = document.querySelectorAll('.add-to-list');
    
    console.log('🔍 Enhanced elements check after 1 second:');
    console.log('credentials-manager found:', !!credentialsManager);
    console.log('add-to-list buttons found:', addToListButtons.length);
    
    if (!credentialsManager && addToListButtons.length === 0) {
        console.log('❌ Enhanced elements still missing - include may have failed');
    } else {
        console.log('✅ Enhanced elements found - include worked!');
    }
    
    console.log('✅ Topics container with editable fields initialized');
}, 1000);
</script>