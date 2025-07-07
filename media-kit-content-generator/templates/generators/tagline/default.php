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

// ROOT FIX: COMPREHENSIVE DATA LOADING - Following Guest Intro Generator Pattern EXACTLY
$template_data = [];
$debug_info = [];

// Primary Method: Try to get data from generator instance
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    $debug_info[] = '✅ Got data from generator instance';
    error_log('MKCG Tagline Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Generator instance not available';
    
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
        // ROOT FIX: Load Authority Hook and Impact Intro data using services - SAME as Guest Intro
        $authority_hook_components = [];
        $impact_intro_components = [];
        
        // Load Authority Hook data
        if (class_exists('MKCG_Authority_Hook_Service')) {
            try {
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                $authority_hook_data = $authority_hook_service->get_authority_hook_data($post_id);
                
                if (!empty($authority_hook_data)) {
                    $authority_hook_components = [
                        'who' => $authority_hook_data['components']['who'] ?? '',
                        'what' => $authority_hook_data['components']['what'] ?? '',
                        'when' => $authority_hook_data['components']['when'] ?? '',
                        'how' => $authority_hook_data['components']['how'] ?? '',
                        'complete' => $authority_hook_data['complete'] ?? ''
                    ];
                    $debug_info[] = "✅ Authority Hook loaded: " . strlen($authority_hook_components['complete']) . " chars";
                } else {
                    $debug_info[] = "⚠️ Authority Hook service returned empty data";
                }
            } catch (Exception $e) {
                $debug_info[] = "❌ Authority Hook service error: " . $e->getMessage();
            }
        }
        
        // ROOT FIX: Load Impact Intro data - EXACTLY as Guest Intro does
        if (class_exists('MKCG_Impact_Intro_Service')) {
            try {
                $impact_intro_service = new MKCG_Impact_Intro_Service();
                $impact_intro_data = $impact_intro_service->get_impact_intro_data($post_id);
                
                if (!empty($impact_intro_data)) {
                    $impact_intro_components = [
                        'where' => $impact_intro_data['components']['where'] ?? '',
                        'why' => $impact_intro_data['components']['why'] ?? '',
                        'complete' => $impact_intro_data['complete'] ?? ''
                    ];
                    $debug_info[] = "✅ Impact Intro loaded: " . strlen($impact_intro_components['complete']) . " chars";
                } else {
                    $debug_info[] = "⚠️ Impact Intro service returned empty data";
                }
            } catch (Exception $e) {
                $debug_info[] = "❌ Impact Intro service error: " . $e->getMessage();
            }
        }
        
        // Load tagline-specific data
        $tagline_data = [
            'selected_tagline' => get_post_meta($post_id, '_selected_tagline', true),
            'generated_taglines' => get_post_meta($post_id, '_generated_taglines', true) ?: [],
            'style' => get_post_meta($post_id, '_tagline_style', true) ?: 'problem-focused',
            'tone' => get_post_meta($post_id, '_tagline_tone', true) ?: 'professional',
            'length' => get_post_meta($post_id, '_tagline_length', true) ?: 'medium'
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
            'authority_hook_components' => $authority_hook_components,
            'impact_intro_components' => $impact_intro_components,
            'tagline_data' => $tagline_data,
            'additional_context' => $additional_context,
            'personal_info' => $personal_info,
            'has_data' => !empty($authority_hook_components['complete']) || !empty($impact_intro_components['complete'])
        ];
        
        $debug_info[] = "✅ Template data loaded for post {$post_id}";
        
    } else {
        $debug_info[] = "❌ No valid post ID found";
        
        // Create empty structure when no data found
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
                'style' => 'problem-focused',
                'tone' => 'professional',
                'length' => 'medium'
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

// Debug logging - same as Guest Intro
error_log('MKCG Tagline Template: Authority Hook Complete: ' . ($authority_hook_components['complete'] ?? 'EMPTY'));
error_log('MKCG Tagline Template: Impact Intro Complete: ' . ($impact_intro_components['complete'] ?? 'EMPTY'));
error_log('MKCG Tagline Template: Has Data: ' . ($has_data ? 'true' : 'false'));
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
                        // ROOT FIX: Show Authority Hook content if available - exactly like Guest Intro
                        if (!empty($authority_hook_components['complete'])) {
                            echo esc_html($authority_hook_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Authority Hook will appear here once you fill in the WHO, WHAT, WHEN, and HOW components below.</em>';
                        }
                    ?></p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <button type="button" class="generator__button generator__button--outline" id="tagline-generator-toggle-authority-builder">
                        Edit Components
                    </button>
                </div>
            </div>
            
            <!-- Authority Hook Builder - ROOT FIX: Render using service exactly like Guest Intro -->
            <div class="generator__builder generator__builder--hidden" id="tagline-generator-authority-hook-builder">
            <?php 
            // ROOT FIX: Render Authority Hook Builder using loaded data - SAME as Guest Intro
            if (class_exists('MKCG_Authority_Hook_Service')) {
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                
                $render_options = array(
                    'show_preview' => false,
                    'show_examples' => true,
                    'show_audience_manager' => true,
                    'css_classes' => 'authority-hook tagline-authority-hook',
                    'field_prefix' => 'mkcg-',
                    'tabs_enabled' => true
                );
                
                echo $authority_hook_service->render_authority_hook_builder('tagline', $authority_hook_components, $render_options);
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
                        // ROOT FIX: Show Impact Intro content if available - exactly like Guest Intro
                        if (!empty($impact_intro_components['complete'])) {
                            echo esc_html($impact_intro_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Impact Intro will appear here once you fill in the WHERE credentials and WHY mission components below.</em>';
                        }
                    ?></p>
                </div>
                
                <div class="generator__impact-intro-actions">
                    <button type="button" class="generator__button generator__button--outline" id="tagline-generator-toggle-impact-builder">
                        Edit Impact Intro
                    </button>
                </div>
            </div>
            
            <!-- Impact Intro Builder - ROOT FIX: Render using service exactly like Guest Intro -->
            <div class="generator__builder generator__builder--hidden" id="tagline-generator-impact-intro-builder">
            <?php 
            // ROOT FIX: Render Impact Intro Builder using loaded data - SAME as Guest Intro
            if (class_exists('MKCG_Impact_Intro_Service')) {
                $impact_intro_service = new MKCG_Impact_Intro_Service();
                
                $render_options = array(
                    'show_preview' => false,
                    'show_examples' => true,
                    'show_credential_manager' => true,
                    'css_classes' => 'impact-intro tagline-impact-intro',
                    'field_prefix' => 'mkcg-',
                    'tabs_enabled' => true
                );
                
                echo $impact_intro_service->render_impact_intro_builder('tagline', $impact_intro_components, $render_options);
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
            
            <!-- Hidden fields for data transmission -->
            <?php if ($post_id): ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
            <?php endif; ?>
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
    // MKCG Debug Info
    console.log('🎯 MKCG Tagline: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // Pass PHP data to JavaScript
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
        }
    };
    
    console.log('✅ MKCG Tagline: Data loaded', window.MKCG_Tagline_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Register tagline generator script
    if (typeof wp !== 'undefined' && wp.hooks && wp.hooks.addAction) {
        wp.hooks.addAction('mkcg.scriptsLoaded', 'mkcg/tagline', function() {
            console.log('Loading Tagline Generator script');
            if (document.querySelector('.tagline-generator')) {
                const script = document.createElement('script');
                script.src = '<?php echo MKCG_PLUGIN_URL; ?>assets/js/generators/tagline-generator.js';
                script.async = true;
                document.head.appendChild(script);
            }
        });
    } else {
        // Fallback for non-WordPress environments
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.tagline-generator')) {
                const script = document.createElement('script');
                script.src = '<?php echo MKCG_PLUGIN_URL; ?>assets/js/generators/tagline-generator.js';
                script.async = true;
                document.head.appendChild(script);
            }
        });
    }
</script>