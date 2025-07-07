<?php
/**
 * Tagline Generator Template - Unified BEM Architecture
 * Following Topics and Biography Generator patterns with two-panel layout
 * 
 * @version 1.1 - Phase 5 Implementation with cross-browser compatibility
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
    $debug_info[] = '✅ Got data from Tagline generator instance';
    error_log('MKCG Tagline Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Tagline generator instance not available';
    
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
            
            // Get tagline-specific data from post meta
            $tagline_data = [
                'selected_tagline' => get_post_meta($post_id, '_selected_tagline', true),
                'generated_taglines' => get_post_meta($post_id, '_generated_taglines', true) ?: [],
                'tagline_style' => get_post_meta($post_id, '_tagline_style', true) ?: 'problem-focused',
                'tagline_tone' => get_post_meta($post_id, '_tagline_tone', true) ?: 'professional',
                'tagline_length' => get_post_meta($post_id, '_tagline_length', true) ?: 'medium'
            ];
            
            $additional_context = [
                'industry' => get_post_meta($post_id, '_guest_industry', true),
                'unique_factors' => get_post_meta($post_id, '_tagline_unique_factors', true),
                'existing_taglines' => get_post_meta($post_id, '_tagline_existing', true)
            ];
            
            $personal_info = [
                'name' => get_post_meta($post_id, '_guest_name', true) ?: get_the_title($post_id),
                'title' => get_post_meta($post_id, '_guest_title', true),
                'organization' => get_post_meta($post_id, '_guest_company', true)
            ];
            
            $template_data = [
                'post_id' => $post_id,
                'authority_hook_components' => $guest_data['authority_hook_components'],
                'impact_intro_components' => $guest_data['impact_intro_components'],
                'tagline_data' => $tagline_data,
                'additional_context' => $additional_context,
                'personal_info' => $personal_info,
                'has_data' => $guest_data['has_data'] || !empty($tagline_data['selected_tagline'])
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
            'tagline_data' => [
                'selected_tagline' => '',
                'generated_taglines' => [],
                'tagline_style' => 'problem-focused',
                'tagline_tone' => 'professional',
                'tagline_length' => 'medium'
            ],
            'additional_context' => [
                'industry' => '',
                'unique_factors' => '',
                'existing_taglines' => ''
            ],
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'has_data' => false
        ];
        $debug_info[] = "⚠️ Using empty structure (no data found)";
    }
    
    error_log('MKCG Tagline Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$impact_intro_components = $template_data['impact_intro_components'];
$tagline_data = $template_data['tagline_data'];
$additional_context = $template_data['additional_context'];
$personal_info = $template_data['personal_info'];
$has_data = $template_data['has_data'];

error_log('MKCG Tagline Template: Rendering with post_id=' . $post_id . ', has_data=' . ($has_data ? 'true' : 'false'));
?>

<div class="generator__container tagline-generator" data-generator="tagline">
    <div class="generator__header">
        <h1 class="generator__title">Professional Tagline Generator</h1>
        <p class="generator__subtitle">Create memorable taglines that distill your expertise into powerful statements</p>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Introduction Text -->
            <p class="tagline-generator__intro">
                Generate 10 compelling tagline options that distill your expertise into memorable, powerful statements. 
                Your taglines will combine your authority hook, impact intro, and unique positioning to create phrases 
                that stick with audiences long after your interview ends.
            </p>
            
            <!-- Authority Hook Section -->
            <div class="generator__authority-hook">
                <div class="generator__authority-hook-header">
                    <span class="generator__authority-hook-icon">★</span>
                    <h3 class="generator__authority-hook-title">Your Authority Hook</h3>
                    <span class="generator__badge">AI GENERATED</span>
                </div>
                
                <div class="generator__authority-hook-content">
                    <p id="tagline-generator-authority-hook-text"><?php 
                        // CLEAN CODE: Show text only when all components have real data
                        $all_components_exist = !empty($authority_hook_components['who']) && 
                                                !empty($authority_hook_components['what']) && 
                                                !empty($authority_hook_components['when']) && 
                                                !empty($authority_hook_components['how']);

                        if ($all_components_exist) {
                            echo esc_html($authority_hook_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Authority Hook will appear here once you fill in the WHO, WHAT, WHEN, and HOW components below.</em>';
                        }
                        // Empty when incomplete - no defaults
                    ?></p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <button type="button" class="generator__button generator__button--outline" id="tagline-generator-toggle-authority-builder">
                        Edit Components
                    </button>
                </div>
            </div>
            
            <!-- Authority Hook Builder - Centralized Service -->
            <div class="generator__builder generator__builder--hidden tagline-authority-hook-builder" id="tagline-generator-authority-hook-builder" data-component="authority-hook">
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
                    
                    error_log('MKCG Tagline Template: Authority Hook Components: ' . json_encode($authority_hook_components));
                    error_log('MKCG Tagline Template: Current Values: ' . json_encode($current_values));
                    
                    // Render options for Tagline Generator
                    $render_options = [
                        'show_preview' => false, // Tagline doesn't need preview in builder
                        'show_examples' => true,
                        'show_audience_manager' => true,
                        'css_classes' => 'authority-hook',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // Render Authority Hook Builder
                    echo $authority_hook_service->render_authority_hook_builder('tagline', $current_values, $render_options);
                    error_log('MKCG Tagline: Authority Hook Builder rendered via centralized service');
                } else {
                    echo '<div class="generator__message generator__message--error">Authority Hook Service not available</div>';
                    error_log('MKCG Tagline: ERROR - Authority Hook Service not available');
                }
                ?>
            </div>
            
            <!-- Impact Intro Section -->
            <div class="generator__impact-intro">
                <div class="generator__impact-intro-header">
                    <span class="generator__impact-intro-icon">🎯</span>
                    <h3 class="generator__impact-intro-title">Your Impact Intro</h3>
                    <span class="generator__badge">CREDENTIALS & MISSION</span>
                </div>
                
                <div class="generator__impact-intro-content">
                    <p id="tagline-generator-impact-intro-text"><?php 
                        // CLEAN CODE: Show text only when components have real data
                        $impact_components_exist = !empty($impact_intro_components['where']) && 
                                                  !empty($impact_intro_components['why']);

                        if ($impact_components_exist) {
                            echo esc_html($impact_intro_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Impact Intro will appear here once you fill in the WHERE credentials and WHY mission components below.</em>';
                        }
                        // Empty when incomplete - no defaults
                    ?></p>
                </div>
                
                <div class="generator__impact-intro-actions">
                    <button type="button" class="generator__button generator__button--outline" id="tagline-generator-toggle-impact-builder">
                        Edit Impact Intro
                    </button>
                </div>
            </div>
            
            <!-- Impact Intro Builder - Centralized Service -->
            <div class="generator__builder generator__builder--hidden tagline-impact-intro-builder" id="tagline-generator-impact-intro-builder" data-component="impact-intro">
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
                    
                    error_log('MKCG Tagline Template: Impact Intro Components: ' . json_encode($impact_intro_components));
                    error_log('MKCG Tagline Template: Current Impact Values: ' . json_encode($current_impact_values));
                    
                    // Render options for Tagline Generator
                    $render_options = [
                        'show_preview' => false, // Tagline doesn't need preview in builder
                        'show_examples' => true,
                        'show_credential_manager' => true,
                        'css_classes' => 'impact-intro',
                        'field_prefix' => 'mkcg-',
                        'tabs_enabled' => true
                    ];
                    
                    // Render Impact Intro Builder
                    echo $impact_intro_service->render_impact_intro_builder('tagline', $current_impact_values, $render_options);
                    error_log('MKCG Tagline: Impact Intro Builder rendered via centralized service');
                } else {
                    echo '<div class="generator__message generator__message--error">Impact Intro Service not available</div>';
                    error_log('MKCG Tagline: ERROR - Impact Intro Service not available');
                }
                ?>
            </div>
            
            <!-- Additional Context Section -->
            <div class="tagline-generator__additional-context">
                <h3 class="tagline-generator__section-title">Additional Context</h3>
                <p class="field__description">
                    Provide additional context to help create more targeted and relevant taglines.
                </p>
                
                <div class="field">
                    <label for="tagline-industry" class="field__label">Industry</label>
                    <input type="text" 
                           id="tagline-industry" 
                           name="industry" 
                           class="field__input"
                           value="<?php echo esc_attr($additional_context['industry']); ?>"
                           placeholder="e.g., Marketing, Business Consulting, SaaS">
                </div>
                
                <div class="field field--textarea">
                    <label for="tagline-unique-factors" class="field__label">Unique Factors</label>
                    <textarea id="tagline-unique-factors" 
                              name="unique_factors" 
                              class="field__input field__textarea"
                              rows="3" 
                              placeholder="What makes you unique? Special methodologies, frameworks, or approaches..."><?php echo esc_textarea($additional_context['unique_factors']); ?></textarea>
                </div>
                
                <div class="field field--textarea">
                    <label for="tagline-existing-taglines" class="field__label">Existing Taglines (Optional)</label>
                    <textarea id="tagline-existing-taglines" 
                              name="existing_taglines" 
                              class="field__input field__textarea"
                              rows="3" 
                              placeholder="List any current taglines you use or want to improve upon..."><?php echo esc_textarea($additional_context['existing_taglines']); ?></textarea>
                </div>
            </div>
            
            <!-- Tagline Settings Section -->
            <div class="tagline-generator__settings">
                <h3 class="tagline-generator__section-title">Tagline Settings</h3>
                <p class="field__description">
                    Customize the style and tone of your generated taglines.
                </p>
                
                <div class="tagline-generator__settings-grid">
                    <div class="field">
                        <label for="tagline-style" class="field__label">Style Focus</label>
                        <select id="tagline-style" name="style" class="field__input">
                            <option value="problem-focused"<?php echo $tagline_data['tagline_style'] === 'problem-focused' ? ' selected' : ''; ?>>Problem-Focused</option>
                            <option value="solution-focused"<?php echo $tagline_data['tagline_style'] === 'solution-focused' ? ' selected' : ''; ?>>Solution-Focused</option>
                            <option value="outcome-focused"<?php echo $tagline_data['tagline_style'] === 'outcome-focused' ? ' selected' : ''; ?>>Outcome-Focused</option>
                            <option value="authority-focused"<?php echo $tagline_data['tagline_style'] === 'authority-focused' ? ' selected' : ''; ?>>Authority-Focused</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="tagline-tone" class="field__label">Tone</label>
                        <select id="tagline-tone" name="tone" class="field__input">
                            <option value="professional"<?php echo $tagline_data['tagline_tone'] === 'professional' ? ' selected' : ''; ?>>Professional</option>
                            <option value="conversational"<?php echo $tagline_data['tagline_tone'] === 'conversational' ? ' selected' : ''; ?>>Conversational</option>
                            <option value="bold"<?php echo $tagline_data['tagline_tone'] === 'bold' ? ' selected' : ''; ?>>Bold</option>
                            <option value="friendly"<?php echo $tagline_data['tagline_tone'] === 'friendly' ? ' selected' : ''; ?>>Friendly</option>
                        </select>
                    </div>
                    
                    <div class="field">
                        <label for="tagline-length" class="field__label">Length Preference</label>
                        <select id="tagline-length" name="length" class="field__input">
                            <option value="short"<?php echo $tagline_data['tagline_length'] === 'short' ? ' selected' : ''; ?>>Short (2-4 words)</option>
                            <option value="medium"<?php echo $tagline_data['tagline_length'] === 'medium' ? ' selected' : ''; ?>>Medium (5-8 words)</option>
                            <option value="long"<?php echo $tagline_data['tagline_length'] === 'long' ? ' selected' : ''; ?>>Long (9-12 words)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Generation Controls -->
            <div class="tagline-generator__generation-controls">
                <h3 class="tagline-generator__section-title">Generate Taglines</h3>
                <p class="field__description">
                    Generate 10 diverse tagline options based on your information and preferences.
                </p>
                
                <div class="tagline-generator__button-group">
                    <button type="button" id="tagline-preview-data" class="generator__button generator__button--outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        Preview Information
                    </button>
                    <button type="button" id="tagline-generate-with-ai" class="generator__button--call-to-action">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Generate 10 Taglines with AI
                    </button>
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div class="generator__loading generator__loading--hidden" id="tagline-generator-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                </svg>
                Generating your professional taglines...
            </div>
            
            <!-- Results container -->
            <div class="generator__results generator__results--hidden" id="tagline-generator-results">
                <div class="tagline-generator__results-header">
                    <h3>Your Generated Taglines</h3>
                    <p>Select your preferred tagline from the options below</p>
                </div>
                <div class="tagline-generator__options-grid" id="tagline-generator-options-grid">
                    <!-- Generated tagline options will be inserted here -->
                </div>
                <div class="tagline-generator__selected-container generator__hidden" id="tagline-generator-selected-container">
                    <div class="tagline-generator__selected-header">
                        <h4>Selected Tagline</h4>
                        <div class="tagline-generator__selected-actions">
                            <button type="button" id="tagline-copy-selected" class="generator__button generator__button--outline">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                </svg>
                                Copy
                            </button>
                            <button type="button" id="tagline-save-selected" class="generator__button generator__button--primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                                </svg>
                                Save Tagline
                            </button>
                        </div>
                    </div>
                    <div class="tagline-generator__selected-content" id="tagline-generator-selected-content">
                        <!-- Selected tagline will be displayed here -->
                    </div>
                </div>
            </div>
            
            <!-- Hidden fields for data transmission - Pure Pods -->
            <input type="hidden" id="tagline-post-id" value="<?php echo esc_attr($post_id); ?>">
            <input type="hidden" id="tagline-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
            
            <!-- Data storage for services -->
            <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="">
            <input type="hidden" id="mkcg-impact-intro" name="impact_intro" value="">
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <h2 class="generator__guidance-header">Crafting Your Perfect Tagline</h2>
            <p class="generator__guidance-subtitle">Your professional tagline is a powerful statement that distills your expertise into a memorable phrase that sticks with audiences long after your interview ends. A great tagline communicates who you help, what you do, and why you're uniquely qualified to do it.</p>
            
            <div class="generator__formula-box">
                <span class="generator__formula-label">TAGLINE FORMULA</span>
                <span class="generator__highlight">[WHO you help]</span> + <span class="generator__highlight">[WHAT you do]</span> + <span class="generator__highlight">[HOW you're unique]</span>
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
                    <h3 class="generator__process-title">Why Taglines Matter</h3>
                    <p class="generator__process-description">
                        Your tagline serves as your professional signature - a concise way to communicate your value proposition that works across all contexts. Whether it's a podcast introduction, social media bio, or networking conversation, your tagline should immediately convey your expertise and make people want to know more.
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
                    <h3 class="generator__process-title">What Makes Great Taglines</h3>
                    <p class="generator__process-description">
                        The best taglines are memorable, specific, and authentic. They avoid jargon and instead use clear language that your ideal audience can immediately understand and relate to. Great taglines also hint at a transformation or outcome without being overly promotional.
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
                    <h3 class="generator__process-title">Tagline Categories</h3>
                    <p class="generator__process-description">
                        Different tagline styles serve different purposes. Problem-focused taglines address pain points, solution-focused ones highlight your methods, outcome-focused taglines emphasize results, and authority-focused ones establish credibility. You'll get options across all categories.
                    </p>
                </div>
            </div>
            
            <h3 class="generator__examples-header">Example Tagline Categories:</h3>
            
            <div class="generator__example-card">
                <strong>Problem-Focused:</strong>
                <p>"Profit Without Chaos"</p>
                <p>"Ending Marketing Overwhelm"</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Solution-Focused:</strong>
                <p>"The Scaling Framework"</p>
                <p>"Systems That Stick"</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Outcome-Focused:</strong>
                <p>"Freedom Through Systems"</p>
                <p>"Million-Dollar Results"</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Authority-Focused:</strong>
                <p>"The Growth Expert"</p>
                <p>"Trusted by 500+ CEOs"</p>
            </div>
            
            <h3 class="generator__examples-header">Where to Use Your Tagline:</h3>
            
            <div class="generator__example-card">
                <strong>Professional Contexts:</strong>
                <p>• Podcast guest introductions</p>
                <p>• Social media profiles and bios</p>
                <p>• Speaking event introductions</p>
                <p>• Email signatures and business cards</p>
                <p>• Networking conversations</p>
                <p>• Website headers and about pages</p>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // Tagline Generator data for JavaScript - Pure Pods integration
    console.log('🎯 MKCG Tagline: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // ENHANCED DEBUG: Show what data we're passing to JavaScript
    console.log('🔍 Authority Hook Components from PHP:', <?php echo json_encode($authority_hook_components); ?>);
    console.log('🔍 Impact Intro Components from PHP:', <?php echo json_encode($impact_intro_components); ?>);
    console.log('🔍 Personal Info from PHP:', <?php echo json_encode($personal_info); ?>);
    console.log('🔍 Tagline Data from PHP:', <?php echo json_encode($tagline_data); ?>);
    console.log('🔍 Current URL parameters:', {
        entry: '<?php echo esc_js($_GET['entry'] ?? ''); ?>',
        post_id: '<?php echo esc_js($_GET['post_id'] ?? ''); ?>'
    });
    
    // CLEAN CODE: Template data - always empty defaults, loads real data if exists
    window.MKCG_Tagline_Data = {
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
        additionalContext: {
            industry: '<?php echo esc_js($additional_context['industry'] ?? ''); ?>',
            uniqueFactors: '<?php echo esc_js($additional_context['unique_factors'] ?? ''); ?>',
            existingTaglines: '<?php echo esc_js($additional_context['existing_taglines'] ?? ''); ?>'
        },
        taglineData: {
            selectedTagline: '<?php echo esc_js($tagline_data['selected_tagline'] ?? ''); ?>',
            generatedTaglines: <?php echo json_encode($tagline_data['generated_taglines']); ?>,
            style: '<?php echo esc_js($tagline_data['tagline_style'] ?? 'problem-focused'); ?>',
            tone: '<?php echo esc_js($tagline_data['tagline_tone'] ?? 'professional'); ?>',
            length: '<?php echo esc_js($tagline_data['tagline_length'] ?? 'medium'); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : 'fallback'; ?>'
    };
    
    console.log('✅ MKCG Tagline: Final data loaded', window.MKCG_Tagline_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Tagline_Data.hasData) {
        console.log('📋 MKCG Tagline: Data found - should populate automatically');
        
        // ROOT FIX: Check if authority hook text element exists and populate if needed
        const hookText = document.getElementById('tagline-generator-authority-hook-text');
        if (hookText) {
            console.log('✅ Authority hook element found with text:', hookText.textContent);
            
            // ROOT FIX: If element is empty but we have authority hook data, populate it
            if (!hookText.textContent.trim() && window.MKCG_Tagline_Data.authorityHook.complete) {
                hookText.textContent = window.MKCG_Tagline_Data.authorityHook.complete;
                console.log('✅ Populated empty authority hook element with template data');
            }
        } else {
            console.error('❌ Authority hook element not found - check selector mismatch');
        }
        
        // Check if impact intro element exists and populate if needed
        const impactText = document.getElementById('tagline-generator-impact-intro-text');
        if (impactText) {
            console.log('✅ Impact intro element found with text:', impactText.textContent);
            
            if (!impactText.textContent.trim() && window.MKCG_Tagline_Data.impactIntro.complete) {
                impactText.textContent = window.MKCG_Tagline_Data.impactIntro.complete;
                console.log('✅ Populated empty impact intro element with template data');
            }
        }
        
    } else {
        console.log('⚠️ MKCG Tagline: No data found - using defaults');
    }
    
    console.log('✅ MKCG Tagline: Template loaded - Pure Pods integration applied');
    
    // Load cross-browser compatibility utilities
    document.addEventListener('DOMContentLoaded', function() {
        if (window.MKCG_Compatibility && typeof window.MKCG_Compatibility.init === 'function') {
            console.log('✅ Initializing cross-browser compatibility utilities');
            window.MKCG_Compatibility.init();
        } else {
            console.log('⚠️ Cross-browser compatibility utilities not available');
        }
    });
</script>