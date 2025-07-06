<?php
/**
 * Impact Intro Generator Template - Dedicated Impact Intro Page
 * Focused solely on Impact Intro creation and editing
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
    error_log('MKCG Impact Intro Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Generator instance not available';
    
    // Fallback Method: Try direct Impact Intro service
    if (class_exists('MKCG_Impact_Intro_Service')) {
        $impact_intro_service = new MKCG_Impact_Intro_Service();
        
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
            $impact_intro_data = $impact_intro_service->get_impact_intro_data($post_id);
            $template_data = [
                'post_id' => $post_id,
                'impact_intro_components' => $impact_intro_data['components'],
                'has_data' => $impact_intro_data['has_data'],
                'complete_intro' => $impact_intro_data['complete_intro']
            ];
            $debug_info[] = "✅ Loaded data via direct Impact Intro service";
            $debug_info[] = "🔑 Impact intro WHERE: " . $impact_intro_data['components']['where'];
        } else {
            $debug_info[] = "❌ No valid post ID found";
        }
    } else {
        $debug_info[] = "❌ MKCG_Impact_Intro_Service not available";
    }
    
    // Fallback: Create empty structure when no data found
    if (empty($template_data)) {
        $template_data = [
            'post_id' => 0,
            'impact_intro_components' => [
                'where' => '',
                'why' => ''
            ],
            'has_data' => false
        ];
        $debug_info[] = "⚠️ Using empty structure (no data found)";
    }
    
    error_log('MKCG Impact Intro Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$impact_intro_components = $template_data['impact_intro_components'];
$has_data = $template_data['has_data'];
$complete_intro = $template_data['complete_intro'] ?? '';

// CRITICAL DEBUG: Log the actual impact intro data
error_log('MKCG Impact Intro Template: Impact Intro Components: ' . json_encode($impact_intro_components));
error_log('MKCG Impact Intro Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container" data-generator="impact-intro">
    <div class="generator__header">
        <h1 class="generator__title">Create Your Impact Intro</h1>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="generator__intro">
                Your Impact Intro follows your Authority Hook and establishes deeper credibility. It showcases specific results and shares your mission or big idea that drives your work.
            </p>
            
            <!-- Impact Intro Builder - CENTRALIZED SERVICE -->                
            <div class="generator__builder" id="impact-intro-builder" data-component="impact-intro">
            <?php 
            // ROOT FIX: Ensure Impact Intro Service is properly loaded and configured for clean slate
            
            // First, try to get from globals
            $impact_intro_service = null;
            if (isset($GLOBALS['impact_intro_service'])) {
                $impact_intro_service = $GLOBALS['impact_intro_service'];
                error_log('MKCG Impact Intro Template: Using global impact_intro_service');
            }
            
            // If not available, create new instance
            if (!$impact_intro_service || !is_object($impact_intro_service)) {
                // Ensure the class is loaded
                if (!class_exists('MKCG_Impact_Intro_Service')) {
                    // Check if plugin constant is defined
                    if (defined('MKCG_PLUGIN_PATH')) {
                        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-impact-intro-service.php';
                    } else {
                        // Fallback path calculation
                        $plugin_path = dirname(dirname(dirname(__FILE__))) . '/';
                        require_once $plugin_path . 'includes/services/class-mkcg-impact-intro-service.php';
                        error_log('MKCG Impact Intro Template: Used fallback path for Impact Intro Service');
                    }
                }
                $impact_intro_service = new MKCG_Impact_Intro_Service();
                $GLOBALS['impact_intro_service'] = $impact_intro_service;
                error_log('MKCG Impact Intro Template: Created new impact_intro_service instance');
            }
            
            // CLEAN CODE: Pass values as-is to Impact Intro Service
            $current_values = [
                'where' => $impact_intro_components['where'] ?? '',
                'why' => $impact_intro_components['why'] ?? ''
            ];
            
            error_log('MKCG Impact Intro Template: Impact Intro Components: ' . json_encode($impact_intro_components));
            error_log('MKCG Impact Intro Template: Current Values: ' . json_encode($current_values));
                
            // CLEAN CODE: Render options for Impact Intro Generator
            $render_options = [
                'show_preview' => true, // Show preview in dedicated page
                'show_examples' => true,
                'show_credential_manager' => true,
                'css_classes' => 'impact-intro',
                'field_prefix' => 'mkcg-',
                'tabs_enabled' => true
            ];
            
            // CLEAN CODE: Render the Impact Intro Builder
            error_log('MKCG Impact Intro Template: About to render impact intro builder');
            error_log('MKCG Impact Intro Template: Service class: ' . get_class($impact_intro_service));
            try {
                $rendered_output = $impact_intro_service->render_impact_intro_builder('impact-intro', $current_values, $render_options);
                if (empty($rendered_output)) {
                    error_log('MKCG Impact Intro Template: WARNING - Impact intro builder returned empty output');
                    // Fallback to simple form
                    echo '<div class="impact-intro-fallback" style="padding: 20px; border: 2px solid red; background: #ffe6e6;">';
                    echo '<h3>Impact Intro Builder (Fallback Mode)</h3>';
                    echo '<p>Service failed to render. Using fallback form.</p>';
                    echo '<div class="field"><label>WHERE:</label><input type="text" id="mkcg-where" value="' . esc_attr($current_values['where']) . '"></div>';
                    echo '<div class="field"><label>WHY:</label><input type="text" id="mkcg-why" value="' . esc_attr($current_values['why']) . '"></div>';
                    echo '</div>';
                } else {
                    echo $rendered_output;
                    error_log('MKCG Impact Intro Template: Impact intro builder rendered successfully (' . strlen($rendered_output) . ' characters)');
                }
            } catch (Exception $e) {
                error_log('MKCG Impact Intro Template: ERROR rendering impact intro builder: ' . $e->getMessage());
                // Emergency fallback
                echo '<div class="impact-intro-error" style="padding: 20px; border: 2px solid red; background: #ffe6e6;">';
                echo '<h3>Impact Intro Builder (Error)</h3>';
                echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
                echo '<div class="field"><label>WHERE:</label><input type="text" id="mkcg-where" value="' . esc_attr($current_values['where']) . '"></div>';
                echo '<div class="field"><label>WHY:</label><input type="text" id="mkcg-why" value="' . esc_attr($current_values['why']) . '"></div>';
                echo '</div>';
            }
            ?>
            </div>
            
            <!-- Save Section -->
            <div class="generator__save-section">
                <button class="generator__button--call-to-action" id="save-button" type="button">
                    💾 Save Impact Intro
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
            <h2 class="generator__guidance-header">Crafting Your Perfect Impact Intro</h2>
            <p class="generator__guidance-subtitle">
                Your Impact Intro follows your Authority Hook and establishes deeper credibility. It showcases specific results and shares your mission or big idea that drives your work.
            </p>
            
            <div class="generator__formula-box">
                <span class="generator__formula-label">FORMULA</span>
                I've <span class="generator__highlight">[WHERE]</span>. My mission is <span class="generator__highlight">[WHY]</span>.
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
                    <h3 class="generator__process-title">Why Impact Intros Matter</h3>
                    <p class="generator__process-description">
                        After grabbing attention with your Authority Hook, podcast hosts want to know about your proven results and the deeper purpose behind your work. A strong Impact Intro provides social proof and connects to your mission, helping hosts understand not just what you do, but why it matters.
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
                    <h3 class="generator__process-title">What Makes a Great Impact Intro</h3>
                    <p class="generator__process-description">
                        The best Impact Intros combine quantifiable results with an inspiring purpose. Use concrete numbers when possible for the WHERE component, and connect to a mission or big idea for the WHY that shows podcast hosts and listeners that you're driven by something meaningful.
                    </p>
                </div>
            </div>
            
            <h3 class="generator__examples-header">Example Impact Intros:</h3>
            
            <div class="generator__example-card">
                <h4>SaaS Growth Expert:</h4>
                <p>"I've helped 200+ startups land funding and secure high-ticket clients. My mission is to democratize access to growth strategies that were once only available to venture-backed companies."</p>
            </div>
            
            <div class="generator__example-card">
                <h4>Business Coach:</h4>
                <p>"My strategies have helped over 500 coaches generate six-figure businesses. I'm driven by the belief that expert-led businesses create more fulfillment and positive impact than traditional corporate careers."</p>
            </div>
            
            <div class="generator__example-card">
                <h4>Author:</h4>
                <p>"My latest book, <em>The Authority Code</em>, became an Amazon bestseller in its category within three months. My purpose is to help thought leaders amplify messages that can transform lives and businesses."</p>
            </div>
            
            <div class="generator__example-card">
                <h4>Nonprofit Leader:</h4>
                <p>"Our organization has provided safe water to over 200,000 people. My why is simple: I believe clean water is a fundamental human right that creates the foundation for education, economic opportunity, and health."</p>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Impact Intro: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // ENHANCED DEBUG: Show what impact intro data we're passing to JavaScript
    console.log('🔍 Impact Intro Components from PHP:', <?php echo json_encode($impact_intro_components); ?>);
    console.log('🔍 Current URL parameters:', {
        entry: '<?php echo esc_js($_GET['entry'] ?? ''); ?>',
        post_id: '<?php echo esc_js($_GET['post_id'] ?? ''); ?>',
        frm_action: '<?php echo esc_js($_GET['frm_action'] ?? ''); ?>'
    });
    
    // CLEAN CODE: Template data - always empty defaults, loads real data if exists
    window.MKCG_Impact_Intro_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        impactIntro: {
            where: '<?php echo esc_js($impact_intro_components['where'] ?? ''); ?>',
            why: '<?php echo esc_js($impact_intro_components['why'] ?? ''); ?>',
            complete: '<?php echo esc_js($complete_intro ?? ''); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>'
    };
    
    console.log('✅ MKCG Impact Intro: Final data loaded', window.MKCG_Impact_Intro_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Impact_Intro_Data.hasData) {
        console.log('📋 MKCG Impact Intro: Data found - should populate automatically');
        
        // Check if impact intro text element exists and populate if needed
        const introDisplay = document.getElementById('impact-intro-content');
        if (introDisplay) {
            console.log('✅ Impact intro display element found');
            
            // ROOT FIX: If element is empty but we have impact intro data, populate it
            if (!introDisplay.textContent.trim() && window.MKCG_Impact_Intro_Data.impactIntro.complete) {
                const completeIntro = window.MKCG_Impact_Intro_Data.impactIntro.complete;
                // Update the preview content with highlighting
                const parts = completeIntro.match(/I've (.+?)\. My mission is to (.+?)\./);
                if (parts) {
                    introDisplay.innerHTML = `I've <span class="impact-intro__highlight">${parts[1]}</span>. My mission is to <span class="impact-intro__highlight">${parts[2]}</span>.`;
                } else {
                    introDisplay.textContent = completeIntro;
                }
                console.log('✅ Populated empty impact intro display with template data');
            }
        } else {
            console.error('❌ Impact intro display element not found - check selector mismatch');
        }
        
    } else {
        console.log('⚠️ MKCG Impact Intro: No data found - using defaults');
    }
    
    // ENHANCED: Real-time Impact Intro display updates handled by centralized service
    // Update the main display element when Impact Intro changes
    document.addEventListener('impact-intro-updated', function(e) {
        const displayElement = document.getElementById('impact-intro-content');
        if (displayElement && e.detail.completeIntro) {
            // Update with highlighting
            const completeIntro = e.detail.completeIntro;
            const parts = completeIntro.match(/I've (.+?)\. My mission is to (.+?)\./);
            if (parts) {
                displayElement.innerHTML = `I've <span class="impact-intro__highlight">${parts[1]}</span>. My mission is to <span class="impact-intro__highlight">${parts[2]}</span>.`;
            } else {
                displayElement.textContent = completeIntro;
            }
            console.log('✅ Impact intro display updated via event:', completeIntro);
        }
    });
    
    console.log('✅ MKCG Impact Intro: Template loaded - Enhanced debugging and population fixes applied');
</script>
