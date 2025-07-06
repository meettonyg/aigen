<?php
/**
 * Biography Generator Template - Unified BEM Architecture
 * Updated template following Topics Generator patterns with two-panel layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';
$post_id = 0;

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

// Try to get post ID from URL parameters
if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
    $post_id = intval($_GET['post_id']);
} else if (isset($_GET['entry']) && intval($_GET['entry']) > 0) {
    $post_id = intval($_GET['entry']);
}

// Get template data if generator instance is available
$template_data = [];
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    error_log('MKCG Biography Template: Got data from generator instance');
} else {
    // Fallback: Create empty structure
    $template_data = [
        'post_id' => $post_id,
        'entry_id' => $entry_id,
        'has_data' => false
    ];
    error_log('MKCG Biography Template: Using fallback empty structure');
}

// Extract data for easier access
$post_id = $template_data['post_id'] ?? $post_id;
$entry_id = $template_data['entry_id'] ?? $entry_id;
$has_data = $template_data['has_data'] ?? false;

error_log('MKCG Biography Template: Rendering with post_id=' . $post_id . ', entry_id=' . $entry_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container biography-generator" data-generator="biography">
    <div class="generator__header">
        <h1 class="generator__title">Professional Biography Generator</h1>
        <p class="generator__subtitle">Create compelling professional biographies in multiple lengths using AI</p>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="biography-generator__intro">
                Generate professional biographies in three different lengths (short, medium, and long) 
                based on your authority hook, impact intro, and professional details. Each biography 
                will be tailored to showcase your expertise and connect with your target audience.
            </p>
            
            <!-- Authority Hook Section -->
            <div class="generator__authority-hook">
                <div class="generator__authority-hook-header">
                    <span class="generator__authority-hook-icon">★</span>
                    <h3 class="generator__authority-hook-title">Your Authority Hook</h3>
                    <span class="generator__badge">AI GENERATED</span>
                </div>
                
                <div class="generator__authority-hook-content">
                    <p id="biography-generator-authority-hook-text">
                        <!-- Authority hook text will be populated by service -->
                    </p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <button type="button" class="generator__button generator__button--outline" id="biography-generator-toggle-authority-builder">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        Edit Authority Hook
                    </button>
                </div>
            </div>
            
            <!-- Authority Hook Builder - Centralized Service -->
            <div class="generator__builder generator__builder--hidden biography-authority-hook-builder" id="biography-generator-authority-hook-builder" data-component="authority-hook">
                <?php
                // Get Authority Hook Service
                $authority_hook_service = null;
                if (isset($GLOBALS['authority_hook_service'])) {
                    $authority_hook_service = $GLOBALS['authority_hook_service'];
                } else {
                    // Create new instance if not available
                    if (!class_exists('MKCG_Authority_Hook_Service')) {
                        $service_path = defined('MKCG_PLUGIN_PATH') 
                            ? MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php'
                            : dirname(dirname(dirname(__FILE__))) . '/includes/services/class-mkcg-authority-hook-service.php';
                        require_once $service_path;
                    }
                    $authority_hook_service = new MKCG_Authority_Hook_Service();
                    $GLOBALS['authority_hook_service'] = $authority_hook_service;
                }
                
                if ($authority_hook_service) {
                    // Get current authority hook data
                    $authority_hook_data = [];
                    if ($post_id) {
                        $hook_result = $authority_hook_service->get_authority_hook_data($post_id);
                        $authority_hook_data = $hook_result['components'];
                    }
                    
                    // Render options for Biography Generator
                    $render_options = [
                        'show_preview' => false, // Biography doesn't need preview in builder
                        'show_examples' => true,
                        'show_audience_manager' => true,
                        'css_classes' => 'authority-hook',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // Render Authority Hook Builder
                    echo $authority_hook_service->render_authority_hook_builder('biography', $authority_hook_data, $render_options);
                    error_log('MKCG Biography: Authority Hook Builder rendered via centralized service');
                } else {
                    echo '<div class="generator__message generator__message--error">Authority Hook Service not available</div>';
                    error_log('MKCG Biography: ERROR - Authority Hook Service not available');
                }
                ?>
            </div>
            
            <!-- Impact Intro Section -->
            <div class="generator__authority-hook biography-generator__impact-intro">
                <div class="generator__authority-hook-header">
                    <span class="generator__authority-hook-icon">🎯</span>
                    <h3 class="generator__authority-hook-title">Your Impact Intro</h3>
                    <span class="generator__badge">CREDENTIALS & MISSION</span>
                </div>
                
                <div class="generator__authority-hook-content">
                    <p id="biography-generator-impact-intro-text">
                        <!-- Impact intro text will be populated by service -->
                    </p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <button type="button" class="generator__button generator__button--outline" id="biography-generator-toggle-impact-builder">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        Edit Impact Intro
                    </button>
                </div>
            </div>
            
            <!-- Impact Intro Builder - Centralized Service -->
            <div class="generator__builder generator__builder--hidden biography-impact-intro-builder" id="biography-generator-impact-intro-builder" data-component="impact-intro">
                <?php
                // Get Impact Intro Service
                $impact_intro_service = null;
                if (isset($GLOBALS['impact_intro_service'])) {
                    $impact_intro_service = $GLOBALS['impact_intro_service'];
                } else {
                    // Create new instance if not available
                    if (!class_exists('MKCG_Impact_Intro_Service')) {
                        $service_path = defined('MKCG_PLUGIN_PATH') 
                            ? MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-impact-intro-service.php'
                            : dirname(dirname(dirname(__FILE__))) . '/includes/services/class-mkcg-impact-intro-service.php';
                        require_once $service_path;
                    }
                    $impact_intro_service = new MKCG_Impact_Intro_Service();
                    $GLOBALS['impact_intro_service'] = $impact_intro_service;
                }
                
                if ($impact_intro_service) {
                    // Get current impact intro data
                    $impact_intro_data = [];
                    if ($post_id) {
                        $intro_result = $impact_intro_service->get_impact_intro_data($post_id);
                        $impact_intro_data = $intro_result['components'];
                    }
                    
                    // Render options for Biography Generator
                    $render_options = [
                        'show_preview' => false, // Biography doesn't need preview in builder
                        'show_examples' => true,
                        'show_credential_manager' => true,
                        'css_classes' => 'impact-intro',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // Render Impact Intro Builder
                    echo $impact_intro_service->render_impact_intro_builder('biography', $impact_intro_data, $render_options);
                    error_log('MKCG Biography: Impact Intro Builder rendered via centralized service');
                } else {
                    echo '<div class="generator__message generator__message--error">Impact Intro Service not available</div>';
                    error_log('MKCG Biography: ERROR - Impact Intro Service not available');
                }
                ?>
            </div>
            
            <!-- Basic Information Section -->
            <div class="biography-generator__basic-info">
                <h3 class="biography-generator__section-title">Basic Information</h3>
                <p class="field__description">
                    Provide your basic professional information to personalize your biography.
                </p>
                
                <div class="field">
                    <label for="biography-name" class="field__label">Full Name</label>
                    <input type="text" 
                           id="biography-name" 
                           name="name" 
                           class="field__input"
                           placeholder="Enter your full name">
                </div>
                
                <div class="field">
                    <label for="biography-title" class="field__label">Professional Title</label>
                    <input type="text" 
                           id="biography-title" 
                           name="title" 
                           class="field__input"
                           placeholder="e.g., CEO, Marketing Consultant, Business Coach">
                </div>
                
                <div class="field">
                    <label for="biography-organization" class="field__label">Organization/Company (Optional)</label>
                    <input type="text" 
                           id="biography-organization" 
                           name="organization" 
                           class="field__input"
                           placeholder="Your company or organization name">
                </div>
            </div>
            
            <!-- Biography Settings Section -->
            <div class="biography-generator__settings">
                <h3 class="biography-generator__section-title">Biography Settings</h3>
                <p class="field__description">
                    Customize how your biography will be written and presented.
                </p>
                
                <div class="biography-generator__settings-grid">
                    <div class="field">
                        <label for="biography-tone" class="field__label">Tone</label>
                        <select id="biography-tone" name="tone" class="field__input">
                            <option value="professional">Professional</option>
                            <option value="conversational">Conversational</option>
                            <option value="authoritative">Authoritative</option>
                            <option value="friendly">Friendly</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="biography-length" class="field__label">Length</label>
                        <select id="biography-length" name="length" class="field__input">
                            <option value="short">Short (50-75 words)</option>
                            <option value="medium" selected>Medium (100-150 words)</option>
                            <option value="long">Long (200-300 words)</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="biography-pov" class="field__label">Point of View</label>
                        <select id="biography-pov" name="pov" class="field__input">
                            <option value="third" selected>Third Person (He/She/They)</option>
                            <option value="first">First Person (I/My)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Additional Content Section -->
            <div class="biography-generator__additional-content">
                <h3 class="biography-generator__section-title">Additional Content</h3>
                <p class="field__description">
                    Include existing content or additional details to enhance your biography.
                </p>
                
                <div class="field field--textarea">
                    <label for="biography-existing" class="field__label">Existing Biography (Optional)</label>
                    <textarea id="biography-existing" 
                              name="existing_bio" 
                              class="field__input field__textarea"
                              rows="4" 
                              placeholder="Paste your current biography here to improve it, or leave blank to create a new one"></textarea>
                </div>
                
                <div class="field field--textarea">
                    <label for="biography-notes" class="field__label">Additional Notes (Optional)</label>
                    <textarea id="biography-notes" 
                              name="additional_notes" 
                              class="field__input field__textarea"
                              rows="3" 
                              placeholder="Any specific achievements, awards, or details you want included"></textarea>
                </div>
            </div>
            
            <!-- Generation Controls -->
            <div class="biography-generator__generation-controls">
                <h3 class="biography-generator__section-title">Generate Biography</h3>
                <p class="field__description">
                    Generate professional biographies in three different lengths based on your information.
                </p>
                
                <div class="biography-generator__button-group">
                    <button type="button" id="biography-preview-data" class="generator__button generator__button--outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        Preview Information
                    </button>
                    <button type="button" id="biography-generate-with-ai" class="generator__button generator__button--primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Generate Biography with AI
                    </button>
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div class="generator__loading generator__loading--hidden" id="biography-generator-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                </svg>
                Generating your professional biography...
            </div>
            
            <!-- Results container -->
            <div class="generator__results generator__results--hidden" id="biography-generator-results">
                <div class="biography-generator__results-header">
                    <h3>Your Generated Biographies</h3>
                    <p>Three versions optimized for different use cases</p>
                </div>
                <div class="biography-generator__results-content" id="biography-generator-results-content">
                    <!-- Generated biographies will be inserted here -->
                </div>
            </div>
            
            <!-- Hidden fields for data transmission -->
            <input type="hidden" id="biography-post-id" value="<?php echo esc_attr($post_id); ?>">
            <input type="hidden" id="biography-entry-id" value="<?php echo esc_attr($entry_id); ?>">
            <input type="hidden" id="biography-entry-key" value="<?php echo esc_attr($entry_key); ?>">
            <input type="hidden" id="biography-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
            
            <!-- Data storage for services -->
            <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="">
            <input type="hidden" id="mkcg-impact-intro" name="impact_intro" value="">
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <h2 class="generator__guidance-header">Crafting Your Perfect Biography</h2>
            <p class="generator__guidance-subtitle">Your professional biography is an essential marketing tool that combines your Authority Hook and Impact Intro into a comprehensive narrative. A powerful biography communicates your credibility, expertise, results, and mission in a way that connects with your audience.</p>
            
            <div class="generator__formula-box">
                <span class="generator__formula-label">FORMULA</span>
                I help <span class="generator__highlight">[WHO]</span> achieve <span class="generator__highlight">[RESULT]</span> when <span class="generator__highlight">[WHEN]</span> through <span class="generator__highlight">[HOW]</span>. I've <span class="generator__highlight">[WHERE]</span>. My mission is to <span class="generator__highlight">[WHY]</span>.
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
                    <h3 class="generator__process-title">Why Professional Biographies Matter</h3>
                    <p class="generator__process-description">
                        Your biography is often the first impression potential clients, podcast hosts, or event organizers have of you. A powerful biography combines your Authority Hook and Impact Intro into a cohesive story that establishes credibility, showcases results, and communicates your purpose.
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
                    <h3 class="generator__process-title">What Makes a Great Biography</h3>
                    <p class="generator__process-description">
                        The best biographies are specific, outcome-focused, and authentic. They clearly identify who you help, what results you deliver, what problems you solve, and how you achieve those results. They also establish credibility through specific achievements and communicate your deeper mission.
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
                    <h3 class="generator__process-title">Using Your Generated Biographies</h3>
                    <p class="generator__process-description">
                        You'll receive three versions: Short (for social media and brief introductions), Medium (for speaker bios and website about pages), and Long (for detailed professional profiles and comprehensive marketing materials). Each version maintains your core message while adapting to different contexts.
                    </p>
                </div>
            </div>
            
            <h3 class="generator__examples-header">Example Biography Structures:</h3>
            
            <div class="generator__example-card">
                <strong>Business Coach Biography:</strong>
                <p>I help ambitious entrepreneurs build scalable businesses without burning out. Through my proven systems, I've guided over 200 business owners to achieve 6-figure growth while working fewer hours. My mission is to prove that business success and personal fulfillment aren't mutually exclusive.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Marketing Consultant Biography:</strong>
                <p>I help B2B companies generate qualified leads and increase sales through strategic digital marketing. I've helped over 150 companies achieve an average 300% increase in lead generation within 90 days. My mission is to democratize effective marketing strategies for businesses of all sizes.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Author & Speaker Biography:</strong>
                <p>I help thought leaders transform their expertise into bestselling books and powerful speaking opportunities. As the author of three Amazon bestsellers, I've helped over 500 experts become published authors and sought-after speakers. My mission is to amplify voices that can make a positive impact in the world.</p>
            </div>
            
            <h3 class="generator__examples-header">Biography Length Guidelines:</h3>
            
            <div class="generator__example-card">
                <strong>Short Biography (50-75 words):</strong>
                <p>Perfect for social media profiles, brief introductions, and situations where space is limited. Focus on your core value proposition and one key credential.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Medium Biography (100-150 words):</strong>
                <p>Ideal for speaker introductions, website about pages, and professional profiles. Include your Authority Hook, key achievements, and mission statement.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Long Biography (200-300 words):</strong>
                <p>Best for comprehensive professional profiles, detailed marketing materials, and situations where you need to establish complete credibility. Include full Authority Hook, Impact Intro, detailed achievements, and personal elements.</p>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // Biography Generator data for JavaScript
    window.MKCG_Biography_Data = {
        postId: <?php echo intval($post_id); ?>,
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('mkcg_nonce'); ?>'
    };
    
    console.log('MKCG Biography: Template data loaded', window.MKCG_Biography_Data);
</script>