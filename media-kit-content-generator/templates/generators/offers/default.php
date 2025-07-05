<?php
/**
 * Offers Generator Template - Unified Design System
 * Uses mkcg-unified-styles.css for consistent design across all generators
 * Follows Topics Generator pattern with unified class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ENHANCED DATA LOADING: Root-level fixes for Pods data loading - Pure Pods
$template_data = [];
$debug_info = [];

// CHECK FOR ENTRY PARAMETER: Don't show defaults if no entry param provided
$has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                   (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');

if (!$has_entry_param) {
    // NO ENTRY PARAM: Create empty structure with no defaults
    $template_data = [
        'post_id' => 0,
        'authority_hook_components' => [
            'who' => '',
            'what' => '',
            'when' => '',
            'how' => '',
            'complete' => ''
        ],
        'business_data' => [
            'business_type' => '',
            'target_audience' => '',
            'price_range' => 'mid',
            'delivery_method' => 'online',
            'offer_count' => 5
        ],
        'has_data' => false,
        'no_entry_param' => true
    ];
    $debug_info[] = "🚫 No entry parameter found - using empty structure (no defaults)";
    error_log('MKCG Offers Template: No entry param found - no default values shown');
} else {
    // HAS ENTRY PARAM: Proceed with normal data loading
    
    // Primary Method: Try to get data from generator instance
    if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
        $template_data = $generator_instance->get_template_data();
        $debug_info[] = '✅ Got data from generator instance';
        error_log('MKCG Offers Template: Got data from generator instance');
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
                    'business_data' => [
                        'business_type' => get_post_meta($post_id, 'offers_business_type', true) ?: '',
                        'target_audience' => get_post_meta($post_id, 'offers_target_audience', true) ?: '',
                        'price_range' => get_post_meta($post_id, 'offers_price_range', true) ?: 'mid',
                        'delivery_method' => get_post_meta($post_id, 'offers_delivery_method', true) ?: 'online',
                        'offer_count' => get_post_meta($post_id, 'offers_offer_count', true) ?: 5
                    ],
                    'has_data' => $guest_data['has_data']
                ];
                $debug_info[] = "✅ Loaded data via direct Pods service";
                $debug_info[] = "🔑 Authority hook WHO: " . $guest_data['authority_hook_components']['who'];
            } else {
                $debug_info[] = "❌ No valid post ID found";
            }
        } else {
            $debug_info[] = "❌ MKCG_Pods_Service not available";
        }
        
        // Fallback: Create empty structure when entry param exists but no data found
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
                'business_data' => [
                    'business_type' => '',
                    'target_audience' => '',
                    'price_range' => 'mid',
                    'delivery_method' => 'online',
                    'offer_count' => 5
                ],
                'has_data' => false
            ];
            $debug_info[] = "⚠️ Using empty structure (entry param exists but no data found)";
        }
        
        error_log('MKCG Offers Template: ' . implode(' | ', $debug_info));
    }
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$business_data = $template_data['business_data'];
$has_data = $template_data['has_data'];

// Define right panel content using Offers Generator classes
$content = '<div class="offers-generator__guidance">
    <h2 class="offers-generator__guidance-header">Crafting Perfect Interview Offers</h2>
    <p class="offers-generator__guidance-subtitle">
      Strategic offers turn podcast interviews into business opportunities. Based on your Authority Hook, these offers create a natural bridge from listener to prospect, allowing you to capture value from each interview appearance.
    </p>
    
    <div class="offers-generator__formula-box">
      <span class="offers-generator__formula-label">FORMULA</span>
      <span class="offers-generator__highlight">[FREE OFFER]</span> → <span class="offers-generator__highlight">[LOW-TICKET OFFER]</span> → <span class="offers-generator__highlight">[PREMIUM OFFER]</span> = Complete Value Ladder for Podcast Audiences
    </div>
    
    <div class="offers-generator__process-step">
      <div class="offers-generator__process-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
      </div>
      <div class="offers-generator__process-content">
        <h3 class="offers-generator__process-title">Why Podcast-Specific Offers Matter</h3>
        <p class="offers-generator__process-description">
          Podcast listeners want immediate value after hearing your interview. A well-crafted offer creates a frictionless path from listener to subscriber to client. Without a dedicated offer, you\'re leaving relationship-building opportunities and revenue on the table with every interview.
        </p>
      </div>
    </div>
    
    <div class="offers-generator__process-step">
      <div class="offers-generator__process-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
      </div>
      <div class="offers-generator__process-content">
        <h3 class="offers-generator__process-title">The Perfect Offer Structure</h3>
        <p class="offers-generator__process-description">
          The most effective podcast offers follow a tiered structure: a free lead magnet to capture initial interest, a low-ticket offer ($97-$497) for those ready to invest, and a premium offer ($997+) for committed prospects. This gives listeners options regardless of where they are in their journey.
        </p>
      </div>
    </div>
    
    <h3 class="offers-generator__examples-header">Example Offers:</h3>
    
    <div class="offers-generator__example-card">
      <strong>For Lead Generation:</strong>
      <p>1. "The AI Marketing Audit Checklist" – A practical guide to identify AI-driven opportunities in your marketing funnel, complete with implementation templates and ROI calculators.</p>
      <p>2. "AI Marketing Amplifier Workshop ($197)" – A 3-hour virtual workshop where SaaS founders learn how to implement AI tools that automate lead generation and nurture campaigns.</p>
      <p>3. "Elite AI Growth Accelerator ($4,997)" – A 3-month done-with-you program implementing complete AI marketing systems for your SaaS business.</p>
    </div>
    
    <div class="offers-generator__example-card">
      <strong>For Consulting Services:</strong>
      <p>1. "The Strategic Planning Toolkit" – Free 15-page guide with templates for quarterly business planning and goal setting.</p>
      <p>2. "Strategy Sprint Session ($497)" – 2-hour intensive strategy session to identify your biggest growth opportunities and create an action plan.</p>
      <p>3. "Executive Growth Program ($2,997)" – 6-month program with monthly strategy sessions and ongoing support for scaling your business.</p>
    </div>
    
    <div class="offers-generator__example-card">
      <strong>For Coaching Services:</strong>
      <p>1. "The Confidence Building Challenge" – Free 7-day email series with daily exercises to boost professional confidence.</p>
      <p>2. "Leadership Breakthrough Workshop ($297)" – Half-day virtual workshop on developing authentic leadership presence and communication skills.</p>
      <p>3. "Executive Leadership Mastery ($5,997)" – 3-month intensive coaching program with weekly sessions and 24/7 Slack support.</p>
    </div>
    
    <div class="offers-generator__process-step">
      <div class="offers-generator__process-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="16"></line>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
      </div>
      <div class="offers-generator__process-content">
        <h3 class="offers-generator__process-title">How to Mention Your Offer</h3>
        <p class="offers-generator__process-description">
          When mentioning your offer during an interview, keep it concise and natural. Use a clear call-to-action with a memorable URL or text option. For example: "If you\'d like my AI Marketing Audit Checklist we discussed, just text \'AI AUDIT\' to 555-123-4567 and I\'ll send it right over."
        </p>
      </div>
    </div>
    
    <div class="offers-generator__process-step">
      <div class="offers-generator__process-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="4 7 4 4 20 4 20 7"></polyline>
          <line x1="9" y1="20" x2="15" y2="20"></line>
          <line x1="12" y1="4" x2="12" y2="20"></line>
        </svg>
      </div>
      <div class="offers-generator__process-content">
        <h3 class="offers-generator__process-title">Tracking Offer Effectiveness</h3>
        <p class="offers-generator__process-description">
          Create unique tracking links or coupon codes for each podcast appearance. This helps you measure which shows drive the most conversions and where to focus your future guesting efforts. Remember to follow up with podcast listeners who take your free offer within 48 hours.
        </p>
      </div>
    </div>
</div>';

// CRITICAL DEBUG: Log the actual authority hook data
error_log('MKCG Offers Template: Authority Hook Components: ' . json_encode($authority_hook_components));
error_log('MKCG Offers Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container offers-generator" data-generator="offers">
    
        <div class="generator__header">
            <h1 class="generator__title">Generate Offers with AI <span class="generator__badge">BETA</span></h1>
        </div>
        
        <div class="generator__content">
            <!-- LEFT PANEL -->
            <div class="generator__panel generator__panel--left">
                <!-- Introduction Text -->
                <p class="offers-generator__intro">
                    Let AI suggest conversion offers based on your Authority Hook. Toggle the switch to activate the AI generator.
                </p>
                
                <!-- Authority Hook Result -->
                <div class="generator__authority-hook">
                    <div class="generator__authority-hook-header">
                        <span class="generator__authority-hook-icon">★</span>
                        <h3 class="generator__authority-hook-title">Your Authority Hook</h3>
                        <span class="generator__badge">AI GENERATED</span>
                    </div>
                    
                    <div class="generator__authority-hook-content">
                        <p id="offers-generator-authority-hook-text"><?php echo isset($template_data['no_entry_param']) && $template_data['no_entry_param'] ? '' : esc_html($authority_hook_components['complete']); ?></p>
                    </div>
                    
                    <div class="generator__authority-hook-actions">
                        <!-- Generate Button -->
                        <button class="generator__button generator__button--secondary offers-generator__button--generate" id="offers-generator-generate-offers">
                            🚀 Generate Offer Suggestions
                        </button>
                        <button type="button" class="generator__button generator__button--outline" id="offers-generator-toggle-builder">
                            Edit Components
                        </button>
                    </div>
                </div>
                
                <!-- Authority Hook Builder - CENTRALIZED SERVICE -->                
                <div class="generator__builder generator__builder--hidden mkcg-authority-hook authority-hook-builder" id="offers-generator-authority-hook-builder" data-component="authority-hook">
                    <?php 
                    // USE CENTRALIZED AUTHORITY HOOK SERVICE - PROPER ARCHITECTURE
                    
                    // Initialize the service if not already available
                    if (!isset($GLOBALS['authority_hook_service'])) {
                        $GLOBALS['authority_hook_service'] = new MKCG_Authority_Hook_Service();
                    }
                    $authority_hook_service = $GLOBALS['authority_hook_service'];
                    
                    // Prepare current values for the service
                    $current_values = [
                        'who' => $authority_hook_components['who'] ?? 'your audience',
                        'what' => $authority_hook_components['what'] ?? 'achieve their goals', 
                        'when' => $authority_hook_components['when'] ?? 'they need help',
                        'how' => $authority_hook_components['how'] ?? 'through your method'
                    ];
                    
                    // Render options for Offers Generator
                    $render_options = [
                        'show_preview' => false, // No preview in offers generator
                        'show_examples' => true,
                        'show_audience_manager' => true,
                        'css_classes' => 'authority-hook',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // Render the Authority Hook Builder using centralized service
                    echo $authority_hook_service->render_authority_hook_builder('offers', $current_values, $render_options);
                    ?>
                </div>
                
                <!-- Business Information Section -->
                <div class="offers-generator__business-container">
                    <div class="offers-generator__business-header">
                        <h3>Business Information</h3>
                        <p class="offers-generator__business-subheading">Provide context for better offer suggestions</p>
                    </div>
                    
                    <div class="field">
                        <label for="offers-business-type" class="field__label">Business Type</label>
                        <select id="offers-business-type" name="business_type" class="field__input" required>
                            <option value="">Select your business type</option>
                            <option value="consulting" <?php selected($business_data['business_type'], 'consulting'); ?>>Consulting</option>
                            <option value="coaching" <?php selected($business_data['business_type'], 'coaching'); ?>>Coaching</option>
                            <option value="training" <?php selected($business_data['business_type'], 'training'); ?>>Training</option>
                            <option value="service" <?php selected($business_data['business_type'], 'service'); ?>>Service Provider</option>
                            <option value="product" <?php selected($business_data['business_type'], 'product'); ?>>Product Business</option>
                            <option value="other" <?php selected($business_data['business_type'], 'other'); ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="offers-target-audience" class="field__label">Target Audience</label>
                        <textarea id="offers-target-audience" 
                                  name="target_audience" 
                                  class="field__input"
                                  rows="3" 
                                  placeholder="Describe your ideal clients in detail. e.g., Small business owners with 10-50 employees who struggle with marketing"
                                  required><?php echo esc_textarea($business_data['target_audience']); ?></textarea>
                        <p class="field__helper-text">
                            Be specific about who you serve, their challenges, and their goals.
                        </p>
                    </div>
                    
                    <div class="field">
                        <label for="offers-price-range" class="field__label">Price Range</label>
                        <select id="offers-price-range" name="price_range" class="field__input">
                            <option value="budget" <?php selected($business_data['price_range'], 'budget'); ?>>Budget ($100-$500)</option>
                            <option value="mid" <?php selected($business_data['price_range'], 'mid'); ?>>Mid-range ($500-$2,000)</option>
                            <option value="premium" <?php selected($business_data['price_range'], 'premium'); ?>>Premium ($2,000-$10,000)</option>
                            <option value="luxury" <?php selected($business_data['price_range'], 'luxury'); ?>>Luxury ($10,000+)</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="offers-delivery-method" class="field__label">Delivery Method</label>
                        <select id="offers-delivery-method" name="delivery_method" class="field__input">
                            <option value="online" <?php selected($business_data['delivery_method'], 'online'); ?>>Online/Virtual</option>
                            <option value="in-person" <?php selected($business_data['delivery_method'], 'in-person'); ?>>In-Person</option>
                            <option value="hybrid" <?php selected($business_data['delivery_method'], 'hybrid'); ?>>Hybrid</option>
                            <option value="self-paced" <?php selected($business_data['delivery_method'], 'self-paced'); ?>>Self-Paced</option>
                            <option value="group" <?php selected($business_data['delivery_method'], 'group'); ?>>Group Sessions</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="offers-count" class="field__label">Number of Offers to Generate</label>
                        <input type="number" 
                               id="offers-count" 
                               name="offer_count" 
                               class="field__input"
                               min="1" 
                               max="10" 
                               value="<?php echo esc_attr($business_data['offer_count']); ?>">
                        <p class="field__helper-text">
                            Generate between 1-10 different service offers.
                        </p>
                    </div>
                </div>
                
                <!-- Loading indicator -->
                <div class="generator__loading generator__loading--hidden" id="offers-generator-loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                    </svg>
                    Generating offers...
                </div>
                
                <!-- Results Section (Initially Hidden) -->
                <div id="offers-results" class="generator__results generator__results--hidden">
                    <div class="offers-generator__results-header">
                        <h3>Generated Offers</h3>
                    </div>
                    <div id="offers-list" class="offers-generator__results-display">
                        <!-- Offers will be populated here by JavaScript -->
                    </div>
                    
                    <div class="offers-generator__button-group">
                        <button type="button" id="copy-all-offers-btn" class="generator__button generator__button--outline">
                            Copy All Offers
                        </button>
                        <button type="button" id="regenerate-offers-btn" class="generator__button generator__button--secondary">
                            Regenerate Offers
                        </button>
                    </div>
                </div>
                
                <!-- Hidden fields for AJAX - Pure Pods -->
                <input type="hidden" id="offers-generator-post-id" value="<?php echo esc_attr($post_id); ?>">
                <input type="hidden" id="offers-generator-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
                <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="">
                
            </div>
            
            <!-- RIGHT PANEL -->
            <div class="generator__panel generator__panel--right">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="offers-loading-overlay" class="offers-generator__loading-overlay" style="display: none;">
        <div class="offers-generator__loading-content">
            <div class="offers-generator__loading-spinner"></div>
            <div class="offers-generator__loading-message">Generating service offers...</div>
        </div>
    </div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Offers: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    window.MKCG_Offers_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who']); ?>',
            what: '<?php echo esc_js($authority_hook_components['what']); ?>',
            when: '<?php echo esc_js($authority_hook_components['when']); ?>',
            how: '<?php echo esc_js($authority_hook_components['how']); ?>',
            complete: '<?php echo esc_js($authority_hook_components['complete']); ?>'
        },
        businessData: {
            business_type: '<?php echo esc_js($business_data['business_type']); ?>',
            target_audience: '<?php echo esc_js($business_data['target_audience']); ?>',
            price_range: '<?php echo esc_js($business_data['price_range']); ?>',
            delivery_method: '<?php echo esc_js($business_data['delivery_method']); ?>',
            offer_count: <?php echo intval($business_data['offer_count']); ?>
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>',
        noEntryParam: <?php echo isset($template_data['no_entry_param']) && $template_data['no_entry_param'] ? 'true' : 'false'; ?>
    };
    
    console.log('✅ MKCG Offers: Final data loaded', window.MKCG_Offers_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Offers_Data.hasData) {
        console.log('📋 MKCG Offers: Data found - should populate automatically');
        
        // Check if authority hook text element exists and has content
        const hookText = document.getElementById('offers-generator-authority-hook-text');
        if (hookText) {
            console.log('✅ Authority hook element found with text:', hookText.textContent);
        } else {
            console.error('❌ Authority hook element not found - check selector mismatch');
        }
        
    } else {
        console.log('⚠️ MKCG Offers: No data found - using defaults');
    }
    
    // ENHANCED: Real-time Authority Hook display updates handled by centralized service
    // Update the main display element when Authority Hook changes
    document.addEventListener('authority-hook-updated', function(e) {
        const displayElement = document.getElementById('offers-generator-authority-hook-text');
        if (displayElement && e.detail.completeHook) {
            displayElement.textContent = e.detail.completeHook;
        }
        
        // Update hidden field for form submission
        const hiddenField = document.getElementById('mkcg-authority-hook');
        if (hiddenField && e.detail.completeHook) {
            hiddenField.value = e.detail.completeHook;
        }
    });
    
    console.log('✅ MKCG Offers: Template loaded - Authority Hook functionality handled by centralized service');
</script>
