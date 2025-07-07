<?php
/**
 * Enhanced Tagline Generator - Phase 5 Implementation with Security Enhancements
 * 
 * Comprehensive PHP backend for tagline generation following established patterns
 * from Topics, Biography, and Guest Intro generators. Supports multi-option
 * generation, service integration, and data persistence.
 * 
 * @package Media_Kit_Content_Generator
 * @version 1.1 - Phase 5 Implementation with Enhanced Security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Enhanced_Tagline_Generator {
    
    private $pods_service;
    private $authority_hook_service;
    private $impact_intro_service;
    private $api_service;
    private $allowed_styles = ['problem-focused', 'solution-focused', 'outcome-focused', 'authority-focused'];
    private $allowed_tones = ['professional', 'conversational', 'bold', 'friendly'];
    private $allowed_lengths = ['short', 'medium', 'long'];
    
    /**
     * Constructor: Initialize services
     */
    public function __construct() {
        $this->init_services();
    }
    
    /**
     * Initialize required services
     */
    private function init_services() {
        // Get services from global scope or create new instances
        global $authority_hook_service, $impact_intro_service, $pods_service, $api_service;
        
        $this->authority_hook_service = $authority_hook_service;
        $this->impact_intro_service = $impact_intro_service;
        $this->pods_service = $pods_service;
        $this->api_service = $api_service;
        
        // Fallback: create services if not available globally
        if (!$this->authority_hook_service && class_exists('MKCG_Authority_Hook_Service')) {
            $this->authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        if (!$this->impact_intro_service && class_exists('MKCG_Impact_Intro_Service')) {
            $this->impact_intro_service = new MKCG_Impact_Intro_Service();
        }
        
        if (!$this->pods_service && class_exists('MKCG_Pods_Service')) {
            $this->pods_service = new MKCG_Pods_Service();
        }
        
        if (!$this->api_service && class_exists('MKCG_API_Service')) {
            $this->api_service = new MKCG_API_Service();
        }
        
        error_log('MKCG Tagline Generator: Services initialized');
    }
    
    /**
     * Get template data for the tagline generator
     * Following Topics and Biography generator patterns
     */
    public function get_template_data($post_id = 0) {
        // Determine post ID from various sources
        if (!$post_id) {
            $post_id = $this->determine_post_id();
        }
        
        // Sanitize post ID
        $post_id = absint($post_id);
        
        error_log("MKCG Tagline: Getting template data for post ID: {$post_id}");
        
        // Initialize data structure
        $template_data = [
            'post_id' => $post_id,
            'has_data' => false,
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
            ]
        ];
        
        if ($post_id > 0) {
            // Load Authority Hook data
            $template_data['authority_hook_components'] = $this->load_authority_hook_data($post_id);
            
            // Load Impact Intro data
            $template_data['impact_intro_components'] = $this->load_impact_intro_data($post_id);
            
            // Load tagline-specific data
            $template_data['tagline_data'] = $this->load_tagline_data($post_id);
            
            // Load additional context
            $template_data['additional_context'] = $this->load_additional_context($post_id);
            
            // Load personal information
            $template_data['personal_info'] = $this->load_personal_info($post_id);
            
            // Determine if we have meaningful data
            $template_data['has_data'] = $this->has_meaningful_data($template_data);
        }
        
        error_log("MKCG Tagline: Template data loaded, has_data: " . ($template_data['has_data'] ? 'true' : 'false'));
        
        return $template_data;
    }
    
    /**
     * Determine post ID from various sources
     * 
     * @return int Sanitized post ID
     */
    private function determine_post_id() {
        // Try URL parameters first
        if (isset($_GET['post_id'])) {
            return absint($_GET['post_id']);
        }
        
        if (isset($_GET['entry'])) {
            return absint($_GET['entry']);
        }
        
        // Try to get the most recent guest post for testing
        $recent_guest = get_posts([
            'post_type' => 'guests',
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (!empty($recent_guest)) {
            return absint($recent_guest[0]->ID);
        }
        
        return 0;
    }
    
    /**
     * Load Authority Hook data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Authority Hook components
     */
    private function load_authority_hook_data($post_id) {
        $post_id = absint($post_id);
        
        $authority_hook = [
            'who' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_authority_hook_who', true) ?: ''),
            'what' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_authority_hook_what', true) ?: ''),
            'when' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_authority_hook_when', true) ?: ''),
            'how' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_authority_hook_how', true) ?: '')
        ];
        
        // Build complete authority hook if all components exist
        if (!empty($authority_hook['who']) && !empty($authority_hook['what']) && 
            !empty($authority_hook['when']) && !empty($authority_hook['how'])) {
            $authority_hook['complete'] = "I help {$authority_hook['who']} {$authority_hook['what']} when {$authority_hook['when']} {$authority_hook['how']}.";
        } else {
            $authority_hook['complete'] = '';
        }
        
        return $authority_hook;
    }
    
    /**
     * Load Impact Intro data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Impact Intro components
     */
    private function load_impact_intro_data($post_id) {
        $post_id = absint($post_id);
        
        $impact_intro = [
            'where' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_impact_intro_where', true) ?: ''),
            'why' => sanitize_textarea_field(get_post_meta($post_id, 'mkcg_impact_intro_why', true) ?: '')
        ];
        
        // Build complete impact intro if both components exist
        if (!empty($impact_intro['where']) && !empty($impact_intro['why'])) {
            $impact_intro['complete'] = "{$impact_intro['where']}. {$impact_intro['why']}";
        } else {
            $impact_intro['complete'] = '';
        }
        
        return $impact_intro;
    }
    
    /**
     * Load tagline-specific data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Tagline data
     */
    private function load_tagline_data($post_id) {
        $post_id = absint($post_id);
        
        // Get raw data
        $selected_tagline = get_post_meta($post_id, '_selected_tagline', true) ?: '';
        $generated_taglines = get_post_meta($post_id, '_generated_taglines', true) ?: [];
        $style = get_post_meta($post_id, '_tagline_style', true) ?: 'problem-focused';
        $tone = get_post_meta($post_id, '_tagline_tone', true) ?: 'professional';
        $length = get_post_meta($post_id, '_tagline_length', true) ?: 'medium';
        
        // Validate style, tone, and length against allowed values
        $style = in_array($style, $this->allowed_styles) ? $style : 'problem-focused';
        $tone = in_array($tone, $this->allowed_tones) ? $tone : 'professional';
        $length = in_array($length, $this->allowed_lengths) ? $length : 'medium';
        
        // Sanitize selected tagline
        $selected_tagline = sanitize_text_field($selected_tagline);
        
        // Sanitize generated taglines array
        if (is_array($generated_taglines)) {
            foreach ($generated_taglines as &$tagline) {
                if (is_array($tagline)) {
                    $tagline['text'] = isset($tagline['text']) ? sanitize_text_field($tagline['text']) : '';
                    $tagline['style'] = isset($tagline['style']) && in_array($tagline['style'], $this->allowed_styles) ? 
                        $tagline['style'] : 'problem-focused';
                    $tagline['tone'] = isset($tagline['tone']) && in_array($tagline['tone'], $this->allowed_tones) ? 
                        $tagline['tone'] : 'professional';
                    $tagline['length'] = isset($tagline['length']) && in_array($tagline['length'], $this->allowed_lengths) ? 
                        $tagline['length'] : 'medium';
                    $tagline['id'] = isset($tagline['id']) ? sanitize_key($tagline['id']) : '';
                }
            }
        } else {
            // Ensure it's an array if somehow corrupted
            $generated_taglines = [];
        }
        
        return [
            'selected_tagline' => $selected_tagline,
            'generated_taglines' => $generated_taglines,
            'style' => $style,
            'tone' => $tone,
            'length' => $length
        ];
    }
    
    /**
     * Load additional context data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Additional context data
     */
    private function load_additional_context($post_id) {
        $post_id = absint($post_id);
        
        return [
            'industry' => sanitize_text_field(get_post_meta($post_id, '_guest_industry', true) ?: ''),
            'unique_factors' => sanitize_textarea_field(get_post_meta($post_id, '_tagline_unique_factors', true) ?: ''),
            'existing_taglines' => sanitize_textarea_field(get_post_meta($post_id, '_tagline_existing', true) ?: '')
        ];
    }
    
    /**
     * Load personal information from post meta
     * 
     * @param int $post_id Post ID
     * @return array Personal information
     */
    private function load_personal_info($post_id) {
        $post_id = absint($post_id);
        
        // Get post title with fallback
        $post_title = '';
        if ($post_id > 0) {
            $post = get_post($post_id);
            if ($post) {
                $post_title = $post->post_title;
            }
        }
        
        return [
            'name' => sanitize_text_field(get_post_meta($post_id, '_guest_name', true) ?: $post_title),
            'title' => sanitize_text_field(get_post_meta($post_id, '_guest_title', true) ?: ''),
            'organization' => sanitize_text_field(get_post_meta($post_id, '_guest_company', true) ?: '')
        ];
    }
    
    /**
     * Check if we have meaningful data (not just defaults)
     * 
     * @param array $template_data Template data structure
     * @return bool True if meaningful data exists
     */
    private function has_meaningful_data($template_data) {
        // Check if we have any authority hook components
        $has_authority_hook = !empty($template_data['authority_hook_components']['who']) ||
                             !empty($template_data['authority_hook_components']['what']) ||
                             !empty($template_data['authority_hook_components']['when']) ||
                             !empty($template_data['authority_hook_components']['how']);
        
        // Check if we have any impact intro components
        $has_impact_intro = !empty($template_data['impact_intro_components']['where']) ||
                           !empty($template_data['impact_intro_components']['why']);
        
        // Check if we have any tagline data
        $has_tagline_data = !empty($template_data['tagline_data']['selected_tagline']) ||
                           !empty($template_data['tagline_data']['generated_taglines']);
        
        // Check if we have personal info
        $has_personal_info = !empty($template_data['personal_info']['name']) ||
                            !empty($template_data['personal_info']['title']) ||
                            !empty($template_data['personal_info']['organization']);
        
        return $has_authority_hook || $has_impact_intro || $has_tagline_data || $has_personal_info;
    }
    
    /**
     * AJAX Handler: Generate 10 tagline options
     * Enhanced security and validation in Phase 5
     */
    public function ajax_generate_taglines() {
        // Enhanced verification with rate limiting check
        if (!$this->verify_ajax_request()) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'security_failure'
            ]);
            return;
        }
        
        // Check for rate limiting
        if ($this->is_rate_limited('generate_taglines')) {
            wp_send_json_error([
                'message' => 'Too many requests. Please wait a moment before generating more taglines.',
                'code' => 'rate_limited'
            ]);
            return;
        }
        
        // Collect and sanitize form data
        $form_data = $this->collect_form_data();
        
        // Enhanced validation with detailed error messages
        $validation = $this->validate_generation_data($form_data);
        if (!$validation['valid']) {
            wp_send_json_error([
                'message' => $validation['message'],
                'code' => 'validation_failure',
                'fields' => $validation['fields'] ?? []
            ]);
            return;
        }
        
        error_log('MKCG Tagline: Starting generation with validated data');
        
        try {
            // Record this generation attempt for rate limiting
            $this->record_api_request('generate_taglines');
            
            // Generate 10 tagline options with error handling
            $taglines = $this->generate_tagline_options($form_data);
            
            if (empty($taglines)) {
                throw new Exception('No taglines were generated');
            }
            
            // Save generated taglines to post meta if post ID is valid
            if ($form_data['post_id'] > 0) {
                $this->save_generated_taglines($form_data['post_id'], $taglines, $form_data);
            }
            
            wp_send_json_success([
                'taglines' => $taglines,
                'count' => count($taglines),
                'message' => 'Generated ' . count($taglines) . ' tagline options successfully',
                'generation_settings' => [
                    'style' => $form_data['style'],
                    'tone' => $form_data['tone'],
                    'length' => $form_data['length']
                ],
                'post_id' => $form_data['post_id']
            ]);
            
        } catch (Exception $e) {
            error_log('MKCG Tagline Generation Error: ' . $e->getMessage());
            
            // Generate demo taglines as fallback with clear indication
            $demo_taglines = $this->generate_demo_taglines($form_data);
            
            wp_send_json_success([
                'taglines' => $demo_taglines,
                'count' => count($demo_taglines),
                'message' => 'Generated demo taglines - AI temporarily unavailable',
                'is_demo' => true,
                'error' => $e->getMessage(),
                'post_id' => $form_data['post_id']
            ]);
        }
    }
    
    /**
     * AJAX Handler: Save selected tagline
     * Enhanced security and validation in Phase 5
     */
    public function ajax_save_tagline() {
        // Enhanced verification
        if (!$this->verify_ajax_request()) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'security_failure'
            ]);
            return;
        }
        
        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error([
                'message' => 'Post ID is required',
                'code' => 'missing_post_id'
            ]);
            return;
        }
        
        // Verify post exists and user has permission to edit it
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error([
                'message' => 'Post not found',
                'code' => 'post_not_found'
            ]);
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error([
                'message' => 'You do not have permission to edit this post',
                'code' => 'permission_denied'
            ]);
            return;
        }
        
        // Get and validate selected tagline
        $selected_tagline = isset($_POST['selected_tagline']) ? sanitize_text_field($_POST['selected_tagline']) : '';
        if (empty($selected_tagline)) {
            wp_send_json_error([
                'message' => 'Selected tagline is required',
                'code' => 'missing_tagline'
            ]);
            return;
        }
        
        // Additional data with sanitization
        $tagline_id = isset($_POST['tagline_id']) ? sanitize_key($_POST['tagline_id']) : '';
        
        // Handle generated_taglines with proper sanitization
        $generated_taglines = [];
        if (isset($_POST['generated_taglines']) && is_array($_POST['generated_taglines'])) {
            foreach ($_POST['generated_taglines'] as $tagline) {
                if (is_array($tagline)) {
                    $sanitized_tagline = [
                        'text' => isset($tagline['text']) ? sanitize_text_field($tagline['text']) : '',
                        'id' => isset($tagline['id']) ? sanitize_key($tagline['id']) : '',
                        'style' => isset($tagline['style']) && in_array($tagline['style'], $this->allowed_styles) ? 
                            $tagline['style'] : 'problem-focused',
                        'tone' => isset($tagline['tone']) && in_array($tagline['tone'], $this->allowed_tones) ? 
                            $tagline['tone'] : 'professional',
                        'length' => isset($tagline['length']) && in_array($tagline['length'], $this->allowed_lengths) ? 
                            $tagline['length'] : 'medium'
                    ];
                    $generated_taglines[] = $sanitized_tagline;
                }
            }
        }
        
        // Get and validate generation settings
        $generation_settings = [];
        if (isset($_POST['generation_settings']) && is_array($_POST['generation_settings'])) {
            $style = isset($_POST['generation_settings']['style']) ? sanitize_text_field($_POST['generation_settings']['style']) : '';
            $tone = isset($_POST['generation_settings']['tone']) ? sanitize_text_field($_POST['generation_settings']['tone']) : '';
            $length = isset($_POST['generation_settings']['length']) ? sanitize_text_field($_POST['generation_settings']['length']) : '';
            
            $generation_settings = [
                'style' => in_array($style, $this->allowed_styles) ? $style : 'problem-focused',
                'tone' => in_array($tone, $this->allowed_tones) ? $tone : 'professional',
                'length' => in_array($length, $this->allowed_lengths) ? $length : 'medium'
            ];
        }
        
        try {
            // Transaction-like approach - collect all updates before committing
            $updates = [];
            
            // Save selected tagline
            $updates[] = update_post_meta($post_id, '_selected_tagline', $selected_tagline);
            $updates[] = update_post_meta($post_id, '_selected_tagline_id', $tagline_id);
            $updates[] = update_post_meta($post_id, '_tagline_save_date', current_time('mysql'));
            
            // Save generation settings if provided
            if (!empty($generation_settings)) {
                if (isset($generation_settings['style'])) {
                    $updates[] = update_post_meta($post_id, '_tagline_style', $generation_settings['style']);
                }
                if (isset($generation_settings['tone'])) {
                    $updates[] = update_post_meta($post_id, '_tagline_tone', $generation_settings['tone']);
                }
                if (isset($generation_settings['length'])) {
                    $updates[] = update_post_meta($post_id, '_tagline_length', $generation_settings['length']);
                }
            }
            
            // Save all generated taglines if provided
            if (!empty($generated_taglines)) {
                $updates[] = update_post_meta($post_id, '_generated_taglines', $generated_taglines);
            }
            
            // Check for any failures
            $success = !in_array(false, $updates, true);
            
            if ($success) {
                wp_send_json_success([
                    'message' => 'Tagline saved successfully',
                    'post_id' => $post_id,
                    'selected_tagline' => $selected_tagline,
                    'save_date' => current_time('mysql')
                ]);
            } else {
                throw new Exception('One or more updates failed');
            }
            
        } catch (Exception $e) {
            error_log('MKCG Tagline Save Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Failed to save tagline: ' . $e->getMessage(),
                'code' => 'save_failure'
            ]);
        }
    }
    
    /**
     * Collect form data from AJAX request with enhanced sanitization
     * 
     * @return array Sanitized form data
     */
    private function collect_form_data() {
        return [
            // Metadata
            'post_id' => isset($_POST['post_id']) ? absint($_POST['post_id']) : 0,
            'nonce' => isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '',
            
            // Service components
            'authorityHook' => isset($_POST['authorityHook']) ? sanitize_textarea_field($_POST['authorityHook']) : '',
            'impactIntro' => isset($_POST['impactIntro']) ? sanitize_textarea_field($_POST['impactIntro']) : '',
            
            // Authority Hook components
            'authority_who' => isset($_POST['authority_who']) ? sanitize_textarea_field($_POST['authority_who']) : '',
            'authority_what' => isset($_POST['authority_what']) ? sanitize_textarea_field($_POST['authority_what']) : '',
            'authority_when' => isset($_POST['authority_when']) ? sanitize_textarea_field($_POST['authority_when']) : '',
            'authority_how' => isset($_POST['authority_how']) ? sanitize_textarea_field($_POST['authority_how']) : '',
            
            // Impact Intro components
            'impact_where' => isset($_POST['impact_where']) ? sanitize_textarea_field($_POST['impact_where']) : '',
            'impact_why' => isset($_POST['impact_why']) ? sanitize_textarea_field($_POST['impact_why']) : '',
            
            // Additional context
            'industry' => isset($_POST['industry']) ? sanitize_text_field($_POST['industry']) : '',
            'uniqueFactors' => isset($_POST['uniqueFactors']) ? sanitize_textarea_field($_POST['uniqueFactors']) : '',
            'existingTaglines' => isset($_POST['existingTaglines']) ? sanitize_textarea_field($_POST['existingTaglines']) : '',
            
            // Tagline settings - validate against allowed values
            'style' => isset($_POST['style']) && in_array($_POST['style'], $this->allowed_styles) ? 
                sanitize_text_field($_POST['style']) : 'problem-focused',
            'tone' => isset($_POST['tone']) && in_array($_POST['tone'], $this->allowed_tones) ? 
                sanitize_text_field($_POST['tone']) : 'professional',
            'length' => isset($_POST['length']) && in_array($_POST['length'], $this->allowed_lengths) ? 
                sanitize_text_field($_POST['length']) : 'medium'
        ];
    }
    
    /**
     * Validate generation data with enhanced validation
     * 
     * @param array $form_data Form data to validate
     * @return array Validation result with field-specific errors
     */
    private function validate_generation_data($form_data) {
        $errors = [];
        $invalid_fields = [];
        
        // Require at least one service component for context
        if (empty($form_data['authorityHook']) && empty($form_data['impactIntro']) &&
            empty($form_data['authority_who']) && empty($form_data['authority_what'])) {
            $errors[] = 'Please complete either an Authority Hook or Impact Intro to provide context for tagline generation.';
            $invalid_fields[] = 'authorityHook';
            $invalid_fields[] = 'impactIntro';
        }
        
        // Validate style
        if (!in_array($form_data['style'], $this->allowed_styles)) {
            $errors[] = 'Invalid tagline style specified.';
            $invalid_fields[] = 'style';
        }
        
        // Validate tone
        if (!in_array($form_data['tone'], $this->allowed_tones)) {
            $errors[] = 'Invalid tagline tone specified.';
            $invalid_fields[] = 'tone';
        }
        
        // Validate length
        if (!in_array($form_data['length'], $this->allowed_lengths)) {
            $errors[] = 'Invalid tagline length specified.';
            $invalid_fields[] = 'length';
        }
        
        // Validate post ID if provided
        if ($form_data['post_id'] > 0) {
            $post = get_post($form_data['post_id']);
            if (!$post) {
                $errors[] = 'Invalid post ID specified.';
                $invalid_fields[] = 'post_id';
            } else if (!current_user_can('edit_post', $form_data['post_id'])) {
                $errors[] = 'You do not have permission to edit this post.';
                $invalid_fields[] = 'post_id';
            }
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => implode(' ', $errors),
                'fields' => $invalid_fields
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate 10 tagline options using AI or demo logic
     * Following multi-option approach like Topics Generator
     * 
     * @param array $form_data Validated form data
     * @return array Generated taglines
     */
    private function generate_tagline_options($form_data) {
        // For Phase 5 implementation, use sophisticated demo generation
        // In production, this would integrate with OpenAI API like Biography Generator
        
        return $this->generate_intelligent_demo_taglines($form_data);
    }
    
    /**
     * Generate intelligent demo taglines based on form data
     * Creates diverse, high-quality options based on user's context
     * 
     * @param array $form_data Validated form data
     * @return array Generated taglines with metadata
     */
    private function generate_intelligent_demo_taglines($form_data) {
        $taglines = [];
        
        // Extract key terms from authority hook and impact intro
        $context = $this->analyze_context($form_data);
        
        // Generate taglines based on style preference
        $base_taglines = $this->get_base_taglines_by_style($form_data['style'], $context);
        
        // Create 10 diverse variations
        $count = 0;
        foreach ($base_taglines as $base_tagline) {
            if ($count >= 10) break;
            
            $tagline = $this->adjust_tagline_for_preferences($base_tagline, $form_data, $context);
            
            // Sanitize before returning
            $taglines[] = [
                'text' => sanitize_text_field($tagline),
                'style' => $form_data['style'],
                'tone' => $form_data['tone'],
                'length' => $form_data['length'],
                'id' => 'tagline_' . ($count + 1)
            ];
            
            $count++;
        }
        
        // Fill remaining slots with creative variations if needed
        while (count($taglines) < 10) {
            $creative_tagline = $this->generate_creative_variation($taglines, $form_data, $context);
            $taglines[] = [
                'text' => sanitize_text_field($creative_tagline),
                'style' => $form_data['style'],
                'tone' => $form_data['tone'],
                'length' => $form_data['length'],
                'id' => 'tagline_' . (count($taglines) + 1)
            ];
        }
        
        return $taglines;
    }
    
    /**
     * Analyze context from form data to inform tagline generation
     * 
     * @param array $form_data Validated form data
     * @return array Context analysis
     */
    private function analyze_context($form_data) {
        $context = [
            'who' => $form_data['authority_who'],
            'what' => $form_data['authority_what'],
            'industry' => $form_data['industry'],
            'unique_factors' => $form_data['uniqueFactors'],
            'credentials' => $form_data['impact_where'],
            'mission' => $form_data['impact_why']
        ];
        
        // Extract key terms for intelligent generation
        $context['key_terms'] = [];
        
        // Extract from WHO field
        if (!empty($context['who'])) {
            $context['key_terms']['audience'] = $this->extract_key_terms($context['who']);
        }
        
        // Extract from WHAT field
        if (!empty($context['what'])) {
            $context['key_terms']['outcome'] = $this->extract_key_terms($context['what']);
        }
        
        // Extract from industry
        if (!empty($context['industry'])) {
            $context['key_terms']['industry'] = $this->extract_key_terms($context['industry']);
        }
        
        return $context;
    }
    
    /**
     * Extract key terms from text for intelligent tagline generation
     * 
     * @param string $text Input text
     * @return array Key terms extracted
     */
    private function extract_key_terms($text) {
        // Simple key term extraction - in production would use NLP
        $text = sanitize_text_field($text);
        $words = preg_split('/\s+/', strtolower($text));
        $meaningful_words = array_filter($words, function($word) {
            // Filter out common words and sanitize each word
            $word = sanitize_text_field($word);
            $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
            return strlen($word) > 3 && !in_array($word, $stop_words);
        });
        
        return array_slice($meaningful_words, 0, 3); // Return top 3 terms
    }
    
    /**
     * Get base taglines by style category
     * 
     * @param string $style Tagline style
     * @param array $context Context analysis
     * @return array Base tagline templates
     */
    private function get_base_taglines_by_style($style, $context) {
        $base_taglines = [];
        
        // Validate style against allowed values
        if (!in_array($style, $this->allowed_styles)) {
            $style = 'problem-focused'; // Default fallback
        }
        
        switch ($style) {
            case 'problem-focused':
                $base_taglines = [
                    'Ending [Problem] for [Audience]',
                    'No More [Problem]',
                    'Solving [Problem] Once and For All',
                    'The End of [Problem]',
                    'Breaking Free from [Problem]',
                    'Conquering [Problem]',
                    'Eliminating [Problem]',
                    'Overcoming [Problem]'
                ];
                break;
                
            case 'solution-focused':
                $base_taglines = [
                    'The [Solution] Expert',
                    'Your [Solution] Solution',
                    'Mastering [Solution]',
                    'The [Solution] Framework',
                    'Systems for [Solution]',
                    'The [Solution] Method',
                    'Strategic [Solution]',
                    'Proven [Solution]'
                ];
                break;
                
            case 'outcome-focused':
                $base_taglines = [
                    'Creating [Outcome] for [Audience]',
                    'Delivering [Outcome]',
                    'Your Path to [Outcome]',
                    'Achieving [Outcome]',
                    'Transforming into [Outcome]',
                    'Building [Outcome]',
                    'Generating [Outcome]',
                    'Producing [Outcome]'
                ];
                break;
                
            case 'authority-focused':
                $base_taglines = [
                    'The [Industry] Expert',
                    'Authority in [Industry]',
                    'Leading [Industry] Professional',
                    'Trusted [Industry] Advisor',
                    'Premier [Industry] Specialist',
                    'Top [Industry] Consultant',
                    'Expert [Industry] Guide',
                    'Recognized [Industry] Authority'
                ];
                break;
                
            default:
                // Fallback - should never reach here due to validation
                $base_taglines = [
                    'Professional [Industry] Expert',
                    'Helping [Audience] Achieve [Outcome]',
                    'Creating Success Through [Solution]',
                    'Transforming [Audience] Results'
                ];
        }
        
        // Sanitize all taglines
        return array_map('sanitize_text_field', $base_taglines);
    }
    
    /**
     * Adjust tagline for tone and length preferences
     * 
     * @param string $base_tagline Base tagline template
     * @param array $form_data Validated form data
     * @param array $context Context analysis
     * @return string Adjusted tagline
     */
    private function adjust_tagline_for_preferences($base_tagline, $form_data, $context) {
        $tagline = $base_tagline;
        
        // Replace placeholders with actual context
        $tagline = $this->replace_tagline_placeholders($tagline, $context);
        
        // Validate tone and length
        $tone = in_array($form_data['tone'], $this->allowed_tones) ? $form_data['tone'] : 'professional';
        $length = in_array($form_data['length'], $this->allowed_lengths) ? $form_data['length'] : 'medium';
        
        // Adjust for tone
        $tagline = $this->adjust_for_tone($tagline, $tone);
        
        // Adjust for length
        $tagline = $this->adjust_for_length($tagline, $length);
        
        return sanitize_text_field($tagline);
    }
    
    /**
     * Replace placeholders in tagline templates with actual context
     * 
     * @param string $tagline Tagline template with placeholders
     * @param array $context Context analysis
     * @return string Tagline with replacements
     */
    private function replace_tagline_placeholders($tagline, $context) {
        // Sanitize inputs
        $tagline = sanitize_text_field($tagline);
        $who = sanitize_text_field($context['who'] ?: 'professionals');
        $industry = sanitize_text_field($context['industry'] ?: 'Business');
        
        $replacements = [
            '[Audience]' => $who,
            '[Problem]' => $this->extract_problem_from_context($context),
            '[Solution]' => $this->extract_solution_from_context($context),
            '[Outcome]' => $this->extract_outcome_from_context($context),
            '[Industry]' => $industry
        ];
        
        foreach ($replacements as $placeholder => $replacement) {
            $tagline = str_replace($placeholder, $replacement, $tagline);
        }
        
        return sanitize_text_field($tagline);
    }
    
    /**
     * Extract problem focus from context
     * 
     * @param array $context Context analysis
     * @return string Problem focus
     */
    private function extract_problem_from_context($context) {
        // Analyze context to identify problems being solved
        $problems = [
            'Business Chaos',
            'Marketing Overwhelm', 
            'Revenue Stagnation',
            'Growth Bottlenecks',
            'System Inefficiencies',
            'Strategic Confusion',
            'Performance Gaps',
            'Scaling Challenges'
        ];
        
        // In production, would analyze context more intelligently
        $selected = $problems[array_rand($problems)];
        return sanitize_text_field($selected);
    }
    
    /**
     * Extract solution focus from context
     * 
     * @param array $context Context analysis
     * @return string Solution focus
     */
    private function extract_solution_from_context($context) {
        $solutions = [
            'Growth Framework',
            'Success System',
            'Strategic Method',
            'Performance Solution',
            'Scaling Strategy',
            'Results Process',
            'Transformation Approach',
            'Achievement Path'
        ];
        
        $selected = $solutions[array_rand($solutions)];
        return sanitize_text_field($selected);
    }
    
    /**
     * Extract outcome focus from context
     * 
     * @param array $context Context analysis
     * @return string Outcome focus
     */
    private function extract_outcome_from_context($context) {
        $outcomes = [
            'Breakthrough Results',
            'Sustainable Growth',
            'Market Leadership',
            'Exceptional Performance',
            'Strategic Success',
            'Competitive Advantage',
            'Exponential Growth',
            'Industry Authority'
        ];
        
        $selected = $outcomes[array_rand($outcomes)];
        return sanitize_text_field($selected);
    }
    
    /**
     * Adjust tagline for specified tone
     * 
     * @param string $tagline Base tagline
     * @param string $tone Desired tone
     * @return string Tone-adjusted tagline
     */
    private function adjust_for_tone($tagline, $tone) {
        // Validate tone
        if (!in_array($tone, $this->allowed_tones)) {
            $tone = 'professional'; // Default fallback
        }
        
        $tagline = sanitize_text_field($tagline);
        
        switch ($tone) {
            case 'conversational':
                // Make it more personal and approachable
                if (strpos($tagline, 'Your') === false && strpos($tagline, 'The') === 0) {
                    $tagline = 'Your ' . substr($tagline, 4);
                }
                break;
                
            case 'bold':
                // Make it more assertive
                $tagline = str_replace(['Helping', 'Supporting'], ['Transforming', 'Revolutionizing'], $tagline);
                break;
                
            case 'friendly':
                // Add warmth
                if (strpos($tagline, '✨') === false) {
                    $tagline = $tagline . ' ✨';
                }
                break;
                
            case 'professional':
            default:
                // Keep as is - already professional
                break;
        }
        
        return sanitize_text_field($tagline);
    }
    
    /**
     * Adjust tagline for specified length
     * 
     * @param string $tagline Base tagline
     * @param string $length Desired length
     * @return string Length-adjusted tagline
     */
    private function adjust_for_length($tagline, $length) {
        // Validate length
        if (!in_array($length, $this->allowed_lengths)) {
            $length = 'medium'; // Default fallback
        }
        
        $tagline = sanitize_text_field($tagline);
        
        switch ($length) {
            case 'short':
                // Aim for 2-4 words
                $words = explode(' ', $tagline);
                if (count($words) > 4) {
                    $tagline = implode(' ', array_slice($words, 0, 4));
                }
                break;
                
            case 'long':
                // Aim for 9-12 words, add descriptive elements
                $words = explode(' ', $tagline);
                if (count($words) < 9) {
                    $tagline .= ' for Modern Businesses';
                }
                break;
                
            case 'medium':
            default:
                // Aim for 5-8 words - keep as is if reasonable
                break;
        }
        
        return sanitize_text_field($tagline);
    }
    
    /**
     * Generate creative variation when we need more options
     * 
     * @param array $existing_taglines Already generated taglines
     * @param array $form_data Validated form data
     * @param array $context Context analysis
     * @return string Creative tagline variation
     */
    private function generate_creative_variation($existing_taglines, $form_data, $context) {
        $creative_patterns = [
            'The [Industry] Transformation Expert',
            'Where [Problem] Meets [Solution]',
            'Bridging [Challenge] and [Success]',
            'From [Current State] to [Desired State]',
            'Making [Complex Thing] Simple',
            'The [Audience] Success Partner',
            'Turning [Challenge] into [Opportunity]',
            'Your [Industry] Game Changer'
        ];
        
        // Select a random pattern and apply context
        $pattern = $creative_patterns[array_rand($creative_patterns)];
        $tagline = $this->replace_tagline_placeholders($pattern, $context);
        
        // Additional replacements for creative variations
        $replacements = [
            '[Challenge]' => sanitize_text_field($this->extract_problem_from_context($context)),
            '[Success]' => sanitize_text_field($this->extract_outcome_from_context($context)),
            '[Current State]' => 'Struggle',
            '[Desired State]' => 'Success',
            '[Complex Thing]' => 'Business Growth',
            '[Opportunity]' => 'Advantage'
        ];
        
        foreach ($replacements as $placeholder => $replacement) {
            $tagline = str_replace($placeholder, $replacement, $tagline);
        }
        
        return sanitize_text_field($tagline);
    }
    
    /**
     * Generate basic demo taglines as absolute fallback
     * 
     * @param array $form_data Validated form data
     * @return array Demo taglines
     */
    private function generate_demo_taglines($form_data) {
        $demo_taglines = [
            'The Growth Expert',
            'Transforming Business Results',
            'Your Success Partner',
            'Strategic Growth Solutions',
            'Breakthrough Performance',
            'Exceptional Results Delivered',
            'The Authority in Excellence',
            'Powering Your Success',
            'Strategic Transformation Leader',
            'Results That Matter'
        ];
        
        $taglines = [];
        for ($i = 0; $i < 10; $i++) {
            $text = $demo_taglines[$i] ?? "Professional Tagline Option " . ($i + 1);
            
            // Adjust for user preferences
            $text = $this->adjust_for_tone($text, $form_data['tone']);
            $text = $this->adjust_for_length($text, $form_data['length']);
            
            $taglines[] = [
                'text' => sanitize_text_field($text),
                'style' => $form_data['style'],
                'tone' => $form_data['tone'],
                'length' => $form_data['length'],
                'id' => 'demo_' . ($i + 1)
            ];
        }
        
        return $taglines;
    }
    
    /**
     * Save generated taglines to post meta
     * 
     * @param int $post_id Post ID
     * @param array $taglines Generated taglines
     * @param array $form_data Validated form data
     * @throws Exception If save fails
     */
    private function save_generated_taglines($post_id, $taglines, $form_data) {
        // Validate post ID and ensure user has permission
        $post_id = absint($post_id);
        $post = get_post($post_id);
        
        if (!$post) {
            throw new Exception('Invalid post ID');
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            throw new Exception('You do not have permission to edit this post');
        }
        
        try {
            // Transaction-like approach - collect updates before applying
            $updates = [];
            
            // Save all generated taglines
            $updates[] = update_post_meta($post_id, '_generated_taglines', $taglines);
            
            // Save generation settings
            $updates[] = update_post_meta($post_id, '_tagline_style', $form_data['style']);
            $updates[] = update_post_meta($post_id, '_tagline_tone', $form_data['tone']);
            $updates[] = update_post_meta($post_id, '_tagline_length', $form_data['length']);
            $updates[] = update_post_meta($post_id, '_tagline_generation_date', current_time('mysql'));
            
            // Save context used for generation
            if (!empty($form_data['industry'])) {
                $updates[] = update_post_meta($post_id, '_guest_industry', sanitize_text_field($form_data['industry']));
            }
            if (!empty($form_data['uniqueFactors'])) {
                $updates[] = update_post_meta($post_id, '_tagline_unique_factors', sanitize_textarea_field($form_data['uniqueFactors']));
            }
            if (!empty($form_data['existingTaglines'])) {
                $updates[] = update_post_meta($post_id, '_tagline_existing', sanitize_textarea_field($form_data['existingTaglines']));
            }
            
            // Check if any updates failed
            if (in_array(false, $updates, true)) {
                throw new Exception('Failed to save one or more tagline metadata fields');
            }
            
            error_log("MKCG Tagline: Successfully saved " . count($taglines) . " generated taglines to post {$post_id}");
            
        } catch (Exception $e) {
            error_log('MKCG Tagline Save Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if user is rate limited for API actions
     * 
     * @param string $action The action being rate limited
     * @return bool True if rate limited
     */
    private function is_rate_limited($action) {
        // Get user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false; // No user to rate limit
        }
        
        // Get rate limit settings
        $rate_limits = [
            'generate_taglines' => [
                'limit' => 10, // 10 requests
                'period' => 60, // per 60 seconds
            ]
        ];
        
        if (!isset($rate_limits[$action])) {
            return false; // No rate limit for this action
        }
        
        $limit = $rate_limits[$action]['limit'];
        $period = $rate_limits[$action]['period'];
        
        // Get user's recent API requests
        $user_requests = get_user_meta($user_id, '_mkcg_api_requests', true);
        if (!$user_requests) {
            $user_requests = [];
        }
        
        // Filter to only this action and recent requests
        $current_time = time();
        $recent_requests = array_filter($user_requests, function($request) use ($action, $current_time, $period) {
            return $request['action'] === $action && ($current_time - $request['time']) < $period;
        });
        
        // Check if over limit
        return count($recent_requests) >= $limit;
    }
    
    /**
     * Record an API request for rate limiting
     * 
     * @param string $action The action being performed
     */
    private function record_api_request($action) {
        // Get user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            return; // No user to record
        }
        
        // Get existing requests
        $user_requests = get_user_meta($user_id, '_mkcg_api_requests', true);
        if (!$user_requests) {
            $user_requests = [];
        }
        
        // Add this request
        $user_requests[] = [
            'action' => $action,
            'time' => time(),
            'ip' => sanitize_text_field($_SERVER['REMOTE_ADDR'])
        ];
        
        // Prune old requests (older than 1 hour)
        $current_time = time();
        $user_requests = array_filter($user_requests, function($request) use ($current_time) {
            return ($current_time - $request['time']) < 3600; // 1 hour
        });
        
        // Save updated requests
        update_user_meta($user_id, '_mkcg_api_requests', $user_requests);
    }
    
    /**
     * Verify AJAX request security with enhanced checks
     * 
     * @return bool True if request is valid
     */
    private function verify_ajax_request() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('MKCG Tagline Security: Invalid request method ' . $_SERVER['REQUEST_METHOD']);
            return false;
        }
        
        // Check referer matches site
        $referer = wp_get_referer();
        if (!$referer || strpos($referer, home_url()) !== 0) {
            error_log('MKCG Tagline Security: Invalid referer: ' . ($referer ?: 'none'));
            return false;
        }
        
        // Check user authentication
        if (!is_user_logged_in()) {
            error_log('MKCG Tagline Security: User not logged in');
            return false;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            error_log('MKCG Tagline Security: User lacks edit_posts capability');
            return false;
        }
        
        // Verify nonce with different possible field names
        $nonce = '';
        foreach (['nonce', 'security', 'mkcg_nonce', '_wpnonce'] as $possible_field) {
            if (!empty($_POST[$possible_field])) {
                $nonce = sanitize_text_field($_POST[$possible_field]);
                break;
            }
        }
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log('MKCG Tagline Security: Nonce verification failed');
            return false;
        }
        
        return true;
    }
}
