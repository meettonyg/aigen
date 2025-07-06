<?php
/**
 * Authority Hook Generator Template - Dedicated Authority Hook Page
 * Focused solely on Authority Hook creation and editing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// CLEAN CODE: Simple data loading - no parameter checking needed
$template_data = [];
$debug_info = [];

// Primary Method: Try to get data from generator instance
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    $debug_info[] = '✅ Got data from generator instance';
    error_log('MKCG Authority Hook Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Generator instance not available';
    
    // Fallback Method: Try direct Authority Hook service
    if (class_exists('MKCG_Authority_Hook_Service')) {
        $authority_hook_service = new MKCG_Authority_Hook_Service();
        
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
            $authority_hook_data = $authority_hook_service->get_authority_hook_data($post_id);
            $template_data = [
                'post_id' => $post_id,
                'authority_hook_components' => $authority_hook_data['components'],
                'has_data' => $authority_hook_data['has_data'],
                'complete_hook' => $authority_hook_data['complete_hook']
            ];
            $debug_info[] = "✅ Loaded data via direct Authority Hook service";
            $debug_info[] = "🔑 Authority hook WHO: " . $authority_hook_data['components']['who'];
        } else {
            $debug_info[] = "❌ No valid post ID found";
        }
    } else {
        $debug_info[] = "❌ MKCG_Authority_Hook_Service not available";
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
            'has_data' => false
        ];
        $debug_info[] = "⚠️ Using empty structure (no data found)";
    }
    
    error_log('MKCG Authority Hook Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$has_data = $template_data['has_data'];
$complete_hook = $template_data['complete_hook'] ?? '';

// CRITICAL DEBUG: Log the actual authority hook data
error_log('MKCG Authority Hook Template: Authority Hook Components: ' . json_encode($authority_hook_components));
error_log('MKCG Authority Hook Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container" data-generator="authority-hook">
    <div class="generator__header">
        <h1 class="generator__title">Create Your Authority Hook</h1>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="generator__intro">
                Your Authority Hook is the opening line that establishes your credibility when reaching out to podcast hosts. A powerful hook immediately communicates who you help, what results you deliver, when they need you, and how you do it.
            </p>
            
            <!-- Authority Hook Builder - CENTRALIZED SERVICE -->                
            <div class="generator__builder" id="authority-hook-builder" data-component="authority-hook">
            <?php 
            // ROOT FIX: Ensure Authority Hook Service is properly loaded and configured for clean slate
            
            // First, try to get from globals
            $authority_hook_service = null;
            if (isset($GLOBALS['authority_hook_service'])) {
                $authority_hook_service = $GLOBALS['authority_hook_service'];
                error_log('MKCG Authority Hook Template: Using global authority_hook_service');
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
                        error_log('MKCG Authority Hook Template: Used fallback path for Authority Hook Service');
                    }
                }
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                $GLOBALS['authority_hook_service'] = $authority_hook_service;
                error_log('MKCG Authority Hook Template: Created new authority_hook_service instance');
            }
            
            // CLEAN CODE: Pass values as-is to Authority Hook Service
            $current_values = [
                'who' => $authority_hook_components['who'] ?? '',
                'what' => $authority_hook_components['what'] ?? '', 
                'when' => $authority_hook_components['when'] ?? '',
                'how' => $authority_hook_components['how'] ?? ''
            ];
            
            error_log('MKCG Authority Hook Template: Authority Hook Components: ' . json_encode($authority_hook_components));
            error_log('MKCG Authority Hook Template: Current Values: ' . json_encode($current_values));
                
            // CLEAN CODE: Render options for Authority Hook Generator
            $render_options = [
                'show_preview' => true, // Show preview in dedicated page
                'show_examples' => true,
                'show_audience_manager' => true,
                'css_classes' => 'authority-hook',
                'field_prefix' => 'mkcg-',
                'tabs_enabled' => true
            ];
            
            // CLEAN CODE: Render the Authority Hook Builder
            error_log('MKCG Authority Hook Template: About to render authority hook builder');
            error_log('MKCG Authority Hook Template: Service class: ' . get_class($authority_hook_service));
            try {
                $rendered_output = $authority_hook_service->render_authority_hook_builder('authority-hook', $current_values, $render_options);
                if (empty($rendered_output)) {
                    error_log('MKCG Authority Hook Template: WARNING - Authority hook builder returned empty output');
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
                    error_log('MKCG Authority Hook Template: Authority hook builder rendered successfully (' . strlen($rendered_output) . ' characters)');
                }
            } catch (Exception $e) {
                error_log('MKCG Authority Hook Template: ERROR rendering authority hook builder: ' . $e->getMessage());
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
            
            <!-- Save Section -->
            <div class="generator__save-section">
                <button class="generator__button--call-to-action" id="save-button" type="button">
                    💾 Save Authority Hook
                </button>
                
                <div class="generator__save-status" id="save-status" style="display: none;">
                    <div class="generator__save-messages" id="save-messages"></div>
                </div>
            </div>
            
            <!-- Hidden fields for AJAX - Pure Pods -->
            <input type="hidden" id="post-id" value="<?php echo esc_attr($post_id); ?>">
            <input type="hidden" id="nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
            
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <h2 class="generator__guidance-header">Crafting Your Perfect Authority Hook</h2>
            <p class="generator__guidance-subtitle">
                Your Authority Hook is the opening line that establishes your credibility when reaching out to podcast hosts. A powerful hook immediately communicates who you help, what results you deliver, when they need you, and how you do it.
            </p>
            
            <div class="generator__formula-box">
                <span class="generator__formula-label">FORMULA</span>
                I help <span class="generator__highlight">[WHO]</span> achieve <span class="generator__highlight">[RESULT]</span> when <span class="generator__highlight">[WHEN]</span> through <span class="generator__highlight">[HOW]</span>.
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
                    <h3 class="generator__process-title">Why Authority Hooks Matter</h3>
                    <p class="generator__process-description">
                        Podcast hosts receive dozens of guest pitches daily. A strong Authority Hook immediately signals your value to their audience and increases your chances of getting booked.
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
                    <h3 class="generator__process-title">What Makes a Great Hook</h3>
                    <p class="generator__process-description">
                        The best Authority Hooks are specific, outcome-focused, and address timing. Avoid vague terms like "entrepreneurs" or "success" – be precise about who you help, what results you deliver, when they need you, and how you achieve it.
                    </p>
                </div>
            </div>
            
            <h3 class="generator__examples-header">Example Authority Hooks:</h3>
            
            <div class="generator__example-card">
                <p>"I help SaaS founders scale to $1M+ ARR when they're struggling to break revenue plateaus through AI-driven marketing."</p>
            </div>
            
            <div class="generator__example-card">
                <p>"I help coaches fill their programs with high-ticket clients when their current marketing isn't working through podcast guesting."</p>
            </div>
            
            <div class="generator__example-card">
                <p>"I help authors turn their books into six-figure businesses when they're launching new titles through media exposure."</p>
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
                    <h3 class="generator__process-title">How to Use Your Authority Hook</h3>
                    <p class="generator__process-description">
                        Once generated, your Authority Hook should be used as the opening line in your podcast guest pitches, social media bios, speaker introductions, and anywhere you need to quickly establish credibility.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Authority Hook: Template data loaded', {
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
    window.MKCG_Authority_Hook_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who'] ?? ''); ?>',
            what: '<?php echo esc_js($authority_hook_components['what'] ?? ''); ?>',
            when: '<?php echo esc_js($authority_hook_components['when'] ?? ''); ?>',
            how: '<?php echo esc_js($authority_hook_components['how'] ?? ''); ?>',
            complete: '<?php echo esc_js($complete_hook ?? ''); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>'
    };
    
    console.log('✅ MKCG Authority Hook: Final data loaded', window.MKCG_Authority_Hook_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Authority_Hook_Data.hasData) {
        console.log('📋 MKCG Authority Hook: Data found - should populate automatically');
        
        // Check if authority hook text element exists and populate if needed
        const hookDisplay = document.getElementById('authority-hook-content');
        if (hookDisplay) {
            console.log('✅ Authority hook display element found');
            
            // ROOT FIX: If element is empty but we have authority hook data, populate it
            if (!hookDisplay.textContent.trim() && window.MKCG_Authority_Hook_Data.authorityHook.complete) {
                const completeHook = window.MKCG_Authority_Hook_Data.authorityHook.complete;
                // Update the preview content with highlighting
                const parts = completeHook.match(/I help (.+?) (.+?) when (.+?) (.+?)\./);
                if (parts) {
                    hookDisplay.innerHTML = `I help <span class="authority-hook__highlight">${parts[1]}</span> <span class="authority-hook__highlight">${parts[2]}</span> when <span class="authority-hook__highlight">${parts[3]}</span> <span class="authority-hook__highlight">${parts[4]}</span>.`;
                } else {
                    hookDisplay.textContent = completeHook;
                }
                console.log('✅ Populated empty authority hook display with template data');
            }
        } else {
            console.error('❌ Authority hook display element not found - check selector mismatch');
        }
        
    } else {
        console.log('⚠️ MKCG Authority Hook: No data found - using defaults');
    }
    
    // ENHANCED: Real-time Authority Hook display updates handled by centralized service
    // Update the main display element when Authority Hook changes
    document.addEventListener('authority-hook-updated', function(e) {
        const displayElement = document.getElementById('authority-hook-content');
        if (displayElement && e.detail.completeHook) {
            // Update with highlighting
            const completeHook = e.detail.completeHook;
            const parts = completeHook.match(/I help (.+?) (.+?) when (.+?) (.+?)\./);
            if (parts) {
                displayElement.innerHTML = `I help <span class="authority-hook__highlight">${parts[1]}</span> <span class="authority-hook__highlight">${parts[2]}</span> when <span class="authority-hook__highlight">${parts[3]}</span> <span class="authority-hook__highlight">${parts[4]}</span>.`;
            } else {
                displayElement.textContent = completeHook;
            }
            console.log('✅ Authority hook display updated via event:', completeHook);
        }
    });
    
    console.log('✅ MKCG Authority Hook: Template loaded - Enhanced debugging and population fixes applied');
</script>
