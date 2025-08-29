<?php
/**
 * Biography Generator Template - Unified BEM Architecture
 * Updated template following Topics Generator patterns with two-panel layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// CLEAN CODE: Simple data loading following Topics Generator pattern
$template_data = [];
$debug_info = [];

// Primary Method: Try to get data from generator instance
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    $debug_info[] = '✅ Got data from Biography generator instance';
    error_log('MKCG Biography Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Biography generator instance not available';
    
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
            
            // Get biography-specific data from post meta
            $biography_data = [
                'short' => get_post_meta($post_id, '_biography_short', true),
                'medium' => get_post_meta($post_id, '_biography_medium', true),
                'long' => get_post_meta($post_id, '_biography_long', true)
            ];
            
            $personal_info = [
                'name' => get_post_meta($post_id, '_guest_name', true) ?: get_the_title($post_id),
                'title' => get_post_meta($post_id, '_guest_title', true),
                'organization' => get_post_meta($post_id, '_guest_company', true)
            ];
            
            $settings = [
                'tone' => get_post_meta($post_id, '_biography_tone', true) ?: 'professional',
                'pov' => get_post_meta($post_id, '_biography_pov', true) ?: 'third'
            ];
            
            $template_data = [
                'post_id' => $post_id,
                'authority_hook_components' => $guest_data['authority_hook_components'],
                'impact_intro_components' => $guest_data['impact_intro_components'],
                'biographies' => $biography_data,
                'personal_info' => $personal_info,
                'settings' => $settings,
                'has_data' => $guest_data['has_data'] || !empty($biography_data['short']) || !empty($biography_data['medium']) || !empty($biography_data['long'])
            ];
            $debug_info[] = "✅ Loaded data via direct Pods service";
            $debug_info[] = "👤 Name: " . $personal_info['name'];
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
            'impact_intro_components' => [
                'where' => '',
                'why' => '',
                'complete' => ''
            ],
            'biographies' => [
                'short' => '',
                'medium' => '',
                'long' => ''
            ],
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'settings' => [
                'tone' => 'professional',
                'pov' => 'third'
            ],
            'has_data' => false
        ];
        $debug_info[] = "⚠️ Using empty structure (no data found)";
    }
    
    error_log('MKCG Biography Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$impact_intro_components = $template_data['impact_intro_components'];
$biographies = $template_data['biographies'];
$personal_info = $template_data['personal_info'];
$settings = $template_data['settings'];
$has_data = $template_data['has_data'];

error_log('MKCG Biography Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
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
                    <p id="biography-generator-authority-hook-text"><?php 
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
                    // CLEAN CODE: Pass values as-is to Authority Hook Service
                    $current_values = [
                        'who' => $authority_hook_components['who'] ?? '',
                        'what' => $authority_hook_components['what'] ?? '', 
                        'when' => $authority_hook_components['when'] ?? '',
                        'how' => $authority_hook_components['how'] ?? ''
                    ];
                    
                    error_log('MKCG Biography Template: Authority Hook Components: ' . json_encode($authority_hook_components));
                    error_log('MKCG Biography Template: Current Values: ' . json_encode($current_values));
                    
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
                    echo $authority_hook_service->render_authority_hook_builder('biography', $current_values, $render_options);
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
                    <p id="biography-generator-impact-intro-text"><?php 
                        // CLEAN CODE: Show text only when components have real data
                        $impact_components_exist = !empty($impact_intro_components['where']) && 
                                                  !empty($impact_intro_components['why']);

                        if ($impact_components_exist) {
                            echo esc_html($impact_intro_components['complete']);
                        }
                        // Empty when incomplete - no defaults
                    ?></p>
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
                    // CLEAN CODE: Pass values as-is to Impact Intro Service
                    $current_impact_values = [
                        'where' => $impact_intro_components['where'] ?? '',
                        'why' => $impact_intro_components['why'] ?? ''
                    ];
                    
                    error_log('MKCG Biography Template: Impact Intro Components: ' . json_encode($impact_intro_components));
                    error_log('MKCG Biography Template: Current Impact Values: ' . json_encode($current_impact_values));
                    
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
                    echo $impact_intro_service->render_impact_intro_builder('biography', $current_impact_values, $render_options);
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
                           value="<?php echo esc_attr($personal_info['name']); ?>"
                           placeholder="Enter your full name">
                </div>
                
                <div class="field">
                    <label for="biography-title" class="field__label">Professional Title</label>
                    <input type="text" 
                           id="biography-title" 
                           name="title" 
                           class="field__input"
                           value="<?php echo esc_attr($personal_info['title']); ?>"
                           placeholder="e.g., CEO, Marketing Consultant, Business Coach">
                </div>
                
                <div class="field">
                    <label for="biography-organization" class="field__label">Organization/Company (Optional)</label>
                    <input type="text" 
                           id="biography-organization" 
                           name="organization" 
                           class="field__input"
                           value="<?php echo esc_attr($personal_info['organization']); ?>"
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
                            <option value="professional"<?php echo $settings['tone'] === 'professional' ? ' selected' : ''; ?>>Professional</option>
                            <option value="conversational"<?php echo $settings['tone'] === 'conversational' ? ' selected' : ''; ?>>Conversational</option>
                            <option value="authoritative"<?php echo $settings['tone'] === 'authoritative' ? ' selected' : ''; ?>>Authoritative</option>
                            <option value="friendly"<?php echo $settings['tone'] === 'friendly' ? ' selected' : ''; ?>>Friendly</option>
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
                            <option value="third"<?php echo $settings['pov'] === 'third' ? ' selected' : ''; ?>>Third Person (He/She/They)</option>
                            <option value="first"<?php echo $settings['pov'] === 'first' ? ' selected' : ''; ?>>First Person (I/My)</option>
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
                    <button type="button" id="biography-generator-generate" class="generator__button--call-to-action">
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
            
            <!-- Hidden fields for data transmission - Pure Pods -->
            <input type="hidden" id="biography-post-id" value="<?php echo esc_attr($post_id); ?>">
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
    // Biography Generator data for JavaScript - Pure Pods integration
    console.log('🎯 MKCG Biography: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // ENHANCED DEBUG: Show what data we're passing to JavaScript
    console.log('🔍 Authority Hook Components from PHP:', <?php echo json_encode($authority_hook_components); ?>);
    console.log('🔍 Impact Intro Components from PHP:', <?php echo json_encode($impact_intro_components); ?>);
    console.log('🔍 Personal Info from PHP:', <?php echo json_encode($personal_info); ?>);
    console.log('🔍 Current URL parameters:', {
        entry: '<?php echo esc_js($_GET['entry'] ?? ''); ?>',
        post_id: '<?php echo esc_js($_GET['post_id'] ?? ''); ?>'
    });
    
    // CLEAN CODE: Template data - always empty defaults, loads real data if exists
    window.MKCG_Biography_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who'] ?? ''); ?>',
            what: '<?php echo esc_js($authority_hook_components['what'] ?? ''); ?>',
            when: '<?php echo esc_js($authority_hook_components['when'] ?? ''); ?>',
            how: '<?php echo esc_js($authority_hook_components['how'] ?? ''); ?>',
            complete: '<?php echo esc_js($authority_hook_components['complete'] ?? ''); ?>'
        },
        impactIntro: {
            where: '<?php echo esc_js($impact_intro_components['where'] ?? ''); ?>',
            why: '<?php echo esc_js($impact_intro_components['why'] ?? ''); ?>',
            complete: '<?php echo esc_js($impact_intro_components['complete'] ?? ''); ?>'
        },
        personalInfo: {
            name: '<?php echo esc_js($personal_info['name'] ?? ''); ?>',
            title: '<?php echo esc_js($personal_info['title'] ?? ''); ?>',
            organization: '<?php echo esc_js($personal_info['organization'] ?? ''); ?>'
        },
        settings: {
            tone: '<?php echo esc_js($settings['tone'] ?? 'professional'); ?>',
            pov: '<?php echo esc_js($settings['pov'] ?? 'third'); ?>'
        },
        biographies: {
            short: '<?php echo esc_js($biographies['short'] ?? ''); ?>',
            medium: '<?php echo esc_js($biographies['medium'] ?? ''); ?>',
            long: '<?php echo esc_js($biographies['long'] ?? ''); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>'
    };
    
    console.log('✅ MKCG Biography: Final data loaded', window.MKCG_Biography_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Biography_Data.hasData) {
        console.log('📋 MKCG Biography: Data found - should populate automatically');
        
        // ROOT FIX: Check if authority hook text element exists and populate if needed
        const hookText = document.getElementById('biography-generator-authority-hook-text');
        if (hookText) {
            console.log('✅ Authority hook element found with text:', hookText.textContent);
            
            // ROOT FIX: If element is empty but we have authority hook data, populate it
            if (!hookText.textContent.trim() && window.MKCG_Biography_Data.authorityHook.complete) {
                hookText.textContent = window.MKCG_Biography_Data.authorityHook.complete;
                console.log('✅ Populated empty authority hook element with template data');
            }
        } else {
            console.error('❌ Authority hook element not found - check selector mismatch');
        }
        
        // Check if impact intro element exists and populate if needed
        const impactText = document.getElementById('biography-generator-impact-intro-text');
        if (impactText) {
            console.log('✅ Impact intro element found with text:', impactText.textContent);
            
            if (!impactText.textContent.trim() && window.MKCG_Biography_Data.impactIntro.complete) {
                impactText.textContent = window.MKCG_Biography_Data.impactIntro.complete;
                console.log('✅ Populated empty impact intro element with template data');
            }
        }
        
    } else {
        console.log('⚠️ MKCG Biography: No data found - using defaults');
    }
    
    console.log('✅ MKCG Biography: Template loaded - Pure Pods integration applied');
</script>