<?php
/**
 * Enhanced Biography Generator - Complete Backend PHP Class
 * 
 * PROMPT 4 IMPLEMENTATION: Complete PHP backend with OpenAI integration,
 * and Pure Pods compatibility following unified architecture patterns.
 * 
 * Handles biography generation, AJAX requests, OpenAI integration, and WordPress post meta storage.
 * Follows unified generator architecture and service-based approach.
 *
 * @package Media_Kit_Content_Generator
 * @version 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Enhanced_Biography_Generator {
    
    /**
     * Version for cache busting and feature tracking
     */
    const VERSION = '2.0';
    
    /**
     * Rate limiting settings for API protection
     */
    const RATE_LIMIT_REQUESTS = 10;
    const RATE_LIMIT_PERIOD = 3600; // 1 hour
    
    /**
     * Maximum API request timeout
     */
    const API_TIMEOUT = 60;
    
    /**
     * Cache duration for API responses
     */
    const CACHE_DURATION = 1800; // 30 minutes
    
    /**
     * OpenAI model to use for generation
     */
    const OPENAI_MODEL = 'gpt-4';
    
    /**
     * Default biography settings
     */
    private $default_settings = [
        'tone' => 'professional',
        'pov' => 'third',
        'length' => 'medium',
    ];
    
    /**
     * Post meta field mappings for WordPress storage
     */
    private $post_meta_fields = [
        'short_bio' => '_biography_short',
        'medium_bio' => '_biography_medium',
        'long_bio' => '_biography_long',
        'tone' => '_biography_tone',
        'pov' => '_biography_pov',
        'generation_date' => '_biography_generation_date',
        'generation_count' => '_biography_generation_count',
        'last_modified' => '_biography_last_modified',
        'api_usage' => '_biography_api_usage',
        'cache_key' => '_biography_cache_key'
    ];
    
    /**
     * Authority Hook Service for centralized data
     */
    private $authority_hook_service;
    
    /**
     * Impact Intro Service for centralized data
     */
    private $impact_intro_service;
    
    /**
     * Rate limiting cache
     */
    private static $rate_limit_cache = [];
    
    /**
     * Initialize the generator with enhanced features
     */
    public function __construct() {
        // Initialize services
        $this->init_services();
        
        // Register AJAX handlers with enhanced security
        // NOTE: AJAX handlers are now registered by main plugin to avoid double registration
        // $this->register_ajax_handlers();
        
        // Register WordPress hooks
        $this->register_hooks();
        
        // Initialize rate limiting
        $this->init_rate_limiting();
        
        error_log('MKCG Biography Generator: Enhanced backend initialized v' . self::VERSION . ' (AJAX handlers managed by main plugin)');
    }
    
    /**
     * Initialize centralized services - Pure Pods integration
     */
    private function init_services() {
        // Initialize Authority Hook Service
        if (class_exists('MKCG_Authority_Hook_Service')) {
            $this->authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        // Initialize Impact Intro Service
        if (class_exists('MKCG_Impact_Intro_Service')) {
            $this->impact_intro_service = new MKCG_Impact_Intro_Service();
        }
        
        error_log('MKCG Biography Generator: Initialized with centralized services (Pure Pods integration)');
    }
    
    /**
     * Register all AJAX handlers with enhanced security
     */
    private function register_ajax_handlers() {
        add_action('wp_ajax_mkcg_generate_biography', [$this, 'ajax_generate_biography']);
        add_action('wp_ajax_mkcg_modify_biography_tone', [$this, 'ajax_modify_biography_tone']);
        add_action('wp_ajax_mkcg_save_biography_data', [$this, 'ajax_save_biography_data']);
        add_action('wp_ajax_mkcg_save_biography_field', [$this, 'ajax_save_biography_field']);
        
        error_log('MKCG Biography Generator: AJAX handlers registered (Pure Pods integration)');
        add_action('wp_ajax_mkcg_get_biography_data', [$this, 'ajax_get_biography_data']);
        add_action('wp_ajax_mkcg_validate_biography_data', [$this, 'ajax_validate_biography_data']);
        add_action('wp_ajax_mkcg_regenerate_biography', [$this, 'ajax_regenerate_biography']);
        
        error_log('MKCG Biography Generator: All AJAX handlers registered');
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Register scripts and styles - hook into the main plugin
        add_action('mkcg_register_scripts', [$this, 'register_scripts']);
        add_action('mkcg_enqueue_generator_scripts', [$this, 'enqueue_scripts'], 10, 1);
        
        // Add meta boxes for biography data
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        
        // Save post hooks
        add_action('save_post', [$this, 'save_post_data'], 10, 2);
    }
    
    /**
     * Initialize rate limiting system
     */
    private function init_rate_limiting() {
        // Clean old rate limit entries
        $this->cleanup_rate_limit_cache();
    }
    
    /**
     * Register scripts and styles
     */
    public function register_scripts() {
        // Register biography generator script
        wp_register_script(
            'mkcg-biography-generator',
            plugins_url('assets/js/generators/biography-generator.js', MKCG_PLUGIN_FILE),
            [],
            self::VERSION,
            true
        );
        
        // Register biography results script
        wp_register_script(
            'mkcg-biography-results',
            plugins_url('assets/js/generators/biography-results.js', MKCG_PLUGIN_FILE),
            [],
            self::VERSION,
            true
        );
    }
    
    /**
     * Enqueue scripts for specific generator
     * 
     * @param string $generator_type The generator type
     */
    public function enqueue_scripts($generator_type) {
        if ($generator_type === 'biography') {
            // Check if we're on the results page
            $is_results_page = isset($_GET['results']) && $_GET['results'] === 'true';
            
            if ($is_results_page) {
                wp_enqueue_script('mkcg-biography-results');
            } else {
                wp_enqueue_script('mkcg-biography-generator');
            }
        }
    }
    
    /**
     * Get template data for rendering - Pure Pods integration
     * 
     * @return array Template data
     */
    public function get_template_data() {
        // Get post ID and entry ID from URL
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
        $entry_key = isset($_GET['entry_key']) ? sanitize_text_field($_GET['entry_key']) : '';
        
        // Initialize template data
        $template_data = [
            'post_id' => $post_id,
            'entry_id' => $entry_id,
            'entry_key' => $entry_key,
            'has_data' => false
        ];
        
        // If we have a post ID, try to get biography data
        if ($post_id > 0) {
            $biography_data = $this->get_biography_data($post_id);
            
            if ($biography_data['has_data']) {
                $template_data = array_merge($template_data, $biography_data);
                $template_data['has_data'] = true;
            }
        }
        
        return $template_data;
    }
    
    /**
     * Get results page template data
     * 
     * @return array Results template data
     */
    public function get_results_data() {
        // Get post ID and entry ID from URL
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
        
        // Initialize results data
        $results_data = [
            'post_id' => $post_id,
            'entry_id' => $entry_id,
            'has_data' => false,
            'biographies' => [
                'short' => '',
                'medium' => '',
                'long' => ''
            ],
            'settings' => $this->default_settings,
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'generation_date' => current_time('mysql')
        ];
        
        // If we have a post ID, try to get biography data
        if ($post_id > 0) {
            $biography_data = $this->get_biography_data($post_id);
            
            if ($biography_data['has_data']) {
                $results_data = array_merge($results_data, $biography_data);
                $results_data['has_data'] = true;
            }
        }
        
        return $results_data;
    }
    
    /**
     * Get biography data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Biography data
     */
    public function get_biography_data($post_id) {
        // Initialize data structure
        $biography_data = [
            'has_data' => false,
            'biographies' => [
                'short' => '',
                'medium' => '',
                'long' => ''
            ],
            'settings' => $this->default_settings,
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'generation_date' => current_time('mysql')
        ];
        
        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            return $biography_data;
        }
        
        // Get biographies from post meta
        $short_bio = get_post_meta($post_id, $this->post_meta_fields['short_bio'], true);
        $medium_bio = get_post_meta($post_id, $this->post_meta_fields['medium_bio'], true);
        $long_bio = get_post_meta($post_id, $this->post_meta_fields['long_bio'], true);
        
        // Get settings from post meta
        $tone = get_post_meta($post_id, $this->post_meta_fields['tone'], true);
        $pov = get_post_meta($post_id, $this->post_meta_fields['pov'], true);
        $generation_date = get_post_meta($post_id, $this->post_meta_fields['generation_date'], true);
        
        // Get personal info
        $name = get_post_meta($post_id, '_guest_name', true);
        if (empty($name)) {
            $name = $post->post_title;
        }
        
        $title = get_post_meta($post_id, '_guest_title', true);
        $organization = get_post_meta($post_id, '_guest_company', true);
        
        // Check if we have data
        $has_data = !empty($short_bio) || !empty($medium_bio) || !empty($long_bio);
        
        if ($has_data) {
            $biography_data['has_data'] = true;
            $biography_data['biographies'] = [
                'short' => $short_bio,
                'medium' => $medium_bio,
                'long' => $long_bio
            ];
            $biography_data['settings'] = [
                'tone' => $tone ?: $this->default_settings['tone'],
                'pov' => $pov ?: $this->default_settings['pov'],
                'length' => $this->default_settings['length'] // Always use default length
            ];
            $biography_data['personal_info'] = [
                'name' => $name,
                'title' => $title,
                'organization' => $organization
            ];
            $biography_data['generation_date'] = $generation_date ?: current_time('mysql');
        } else {
            // If no biography data, still populate personal info
            $biography_data['personal_info'] = [
                'name' => $name,
                'title' => $title,
                'organization' => $organization
            ];
        }
        
        return $biography_data;
    }
    
    /**
     * Enhanced security verification with comprehensive checks
     */
    private function verify_security_comprehensive($action = 'generate') {
        // Check user authentication
        if (!is_user_logged_in()) {
            error_log('MKCG Biography: Security check failed - user not logged in');
            return ['success' => false, 'message' => 'User authentication required'];
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            error_log('MKCG Biography: Security check failed - insufficient capabilities');
            return ['success' => false, 'message' => 'Insufficient user permissions'];
        }
        
        // Verify nonce (check both POST and GET)
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? $_GET['nonce'] ?? $_GET['security'] ?? '';
        if (!wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log('MKCG Biography: Security check failed - invalid nonce');
            return ['success' => false, 'message' => 'Security verification failed'];
        }
        
        // Check rate limiting
        $rate_check = $this->check_rate_limit();
        if (!$rate_check['allowed']) {
            error_log('MKCG Biography: Rate limit exceeded for user ' . get_current_user_id());
            return ['success' => false, 'message' => 'Rate limit exceeded. Please wait before making another request.'];
        }
        
        return ['success' => true, 'message' => 'Security verification passed'];
    }
    
    /**
     * Rate limiting implementation
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $current_time = time();
        $cache_key = 'mkcg_rate_limit_' . $user_id;
        
        // Get existing rate limit data
        $rate_data = get_transient($cache_key);
        
        if (!$rate_data) {
            $rate_data = [
                'count' => 0,
                'start_time' => $current_time
            ];
        }
        
        // Reset if period has passed
        if (($current_time - $rate_data['start_time']) >= self::RATE_LIMIT_PERIOD) {
            $rate_data = [
                'count' => 0,
                'start_time' => $current_time
            ];
        }
        
        // Check if limit exceeded
        if ($rate_data['count'] >= self::RATE_LIMIT_REQUESTS) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $rate_data['start_time'] + self::RATE_LIMIT_PERIOD
            ];
        }
        
        // Increment counter
        $rate_data['count']++;
        set_transient($cache_key, $rate_data, self::RATE_LIMIT_PERIOD);
        
        return [
            'allowed' => true,
            'remaining' => self::RATE_LIMIT_REQUESTS - $rate_data['count'],
            'reset_time' => $rate_data['start_time'] + self::RATE_LIMIT_PERIOD
        ];
    }
    
    /**
     * Clean up old rate limit cache entries
     */
    private function cleanup_rate_limit_cache() {
        global $wpdb;
        
        // Clean up old transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name LIKE %s",
                '%_transient_timeout_mkcg_rate_limit_%',
                '%' . (time() - self::RATE_LIMIT_PERIOD) . '%'
            )
        );
    }
    
    /**
     * Enhanced input validation and sanitization
     */
    private function validate_and_sanitize_input($data) {
        $sanitized = [];
        $errors = [];
        
        // Validate required fields
        $required_fields = ['name'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        // Sanitize all text fields
        $text_fields = ['name', 'title', 'organization', 'tone', 'pov', 'length'];
        foreach ($text_fields as $field) {
            $sanitized[$field] = isset($data[$field]) ? sanitize_text_field($data[$field]) : '';
        }
        
        // Sanitize textarea fields
        $textarea_fields = ['authority_hook', 'impact_intro', 'existing_bio', 'additional_notes'];
        foreach ($textarea_fields as $field) {
            $sanitized[$field] = isset($data[$field]) ? sanitize_textarea_field($data[$field]) : '';
        }
        
        // Validate tone options
        $valid_tones = ['professional', 'conversational', 'authoritative', 'friendly'];
        if (!empty($sanitized['tone']) && !in_array($sanitized['tone'], $valid_tones)) {
            $errors[] = 'Invalid tone specified';
            $sanitized['tone'] = $this->default_settings['tone'];
        }
        
        // Validate POV options
        $valid_povs = ['first', 'third'];
        if (!empty($sanitized['pov']) && !in_array($sanitized['pov'], $valid_povs)) {
            $errors[] = 'Invalid point of view specified';
            $sanitized['pov'] = $this->default_settings['pov'];
        }
        
        // Validate length options
        $valid_lengths = ['short', 'medium', 'long'];
        if (!empty($sanitized['length']) && !in_array($sanitized['length'], $valid_lengths)) {
            $errors[] = 'Invalid length specified';
            $sanitized['length'] = $this->default_settings['length'];
        }
        
        // Validate post ID
        if (isset($data['post_id'])) {
            $sanitized['post_id'] = intval($data['post_id']);
            if ($sanitized['post_id'] <= 0) {
                $errors[] = 'Invalid post ID';
            }
        }
        
        return [
            'success' => empty($errors),
            'data' => $sanitized,
            'errors' => $errors
        ];
    }
    
    /**
     * Enhanced biography generation with comprehensive error handling and caching
     * 
     * @param array $data Form data
     * @return array Generated biographies
     */
    private function generate_biography($data) {
        // Check cache first
        $cache_key = $this->generate_cache_key($data);
        $cached_result = $this->get_cached_biography($cache_key);
        
        if ($cached_result) {
            error_log('MKCG Biography: Using cached result for request');
            return $cached_result;
        }
        // Initialize results
        $biographies = [
            'short' => '',
            'medium' => '',
            'long' => ''
        ];
        
        // Set up length guidelines
        $length_guidelines = [
            'short' => '50-75 words',
            'medium' => '100-150 words',
            'long' => '200-300 words'
        ];
        
        // Set up tone guidelines
        $tone_guidelines = [
            'professional' => 'formal, authoritative, and business-appropriate',
            'conversational' => 'friendly, approachable, and conversational',
            'authoritative' => 'expert, confident, and authoritative',
            'friendly' => 'warm, approachable, and personable'
        ];
        
        // Set up POV guidelines
        $pov_guidelines = [
            'first' => 'first person (using "I" and "my")',
            'third' => 'third person (using their name and "he", "she", or "they")'
        ];
        
        // Check if we have the required data
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'Name is required to generate a biography.'
            ];
        }
        
        // Prepare the prompt
        $prompt = "You are a professional biography writer tasked with creating compelling professional biographies for {$data['name']}";
        
        if (!empty($data['title'])) {
            $prompt .= ", a {$data['title']}";
        }
        
        if (!empty($data['organization'])) {
            $prompt .= " at {$data['organization']}";
        }
        
        $prompt .= ".\n\n";
        
        $prompt .= "Please create three versions of the biography:\n";
        $prompt .= "1. SHORT BIO: {$length_guidelines['short']}\n";
        $prompt .= "2. MEDIUM BIO: {$length_guidelines['medium']}\n";
        $prompt .= "3. LONG BIO: {$length_guidelines['long']}\n\n";
        
        $prompt .= "Use a {$tone_guidelines[$data['tone']]} tone and write in the {$pov_guidelines[$data['pov']]}.\n\n";
        
        $prompt .= "Use the following information as the foundation for the biography:\n\n";
        
        if (!empty($data['authority_hook'])) {
            $prompt .= "AUTHORITY HOOK (core expertise and value proposition):\n{$data['authority_hook']}\n\n";
        }
        
        if (!empty($data['impact_intro'])) {
            $prompt .= "IMPACT INTRO (credentials and mission):\n{$data['impact_intro']}\n\n";
        }
        
        if (!empty($data['existing_bio'])) {
            $prompt .= "EXISTING BIOGRAPHY (use this as reference but improve it):\n{$data['existing_bio']}\n\n";
        }
        
        if (!empty($data['additional_notes'])) {
            $prompt .= "ADDITIONAL NOTES:\n{$data['additional_notes']}\n\n";
        }
        
        $prompt .= "Follow these additional guidelines:\n";
        $prompt .= "- Focus on their expertise, achievements, and the value they provide\n";
        $prompt .= "- Include specific credentials and quantifiable results when available\n";
        $prompt .= "- Ensure the biography flows naturally and engages the reader\n";
        $prompt .= "- Maintain consistent messaging across all three versions\n";
        $prompt .= "- Each biography should be self-contained and complete\n\n";
        
        $prompt .= "Format your response as follows:\n";
        $prompt .= "SHORT BIO:\n[Short biography here]\n\n";
        $prompt .= "MEDIUM BIO:\n[Medium biography here]\n\n";
        $prompt .= "LONG BIO:\n[Long biography here]\n";
        
        // Check if OpenAI API key is available with enhanced validation
        $api_key = get_option('mkcg_openai_api_key');
        if (empty($api_key)) {
            error_log('MKCG Biography: OpenAI API key not configured');
            return [
                'success' => false,
                'message' => 'OpenAI API key is not configured. Please set it in the plugin settings.',
                'error_code' => 'API_KEY_MISSING'
            ];
        }
        
        // Validate API key format
        if (!$this->validate_api_key_format($api_key)) {
            error_log('MKCG Biography: Invalid API key format');
            return [
                'success' => false,
                'message' => 'Invalid OpenAI API key format. Please check your configuration.',
                'error_code' => 'API_KEY_INVALID'
            ];
        }
        
        try {
            // Prepare API request
            $headers = [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ];
            
            $request_body = [
                'model' => self::OPENAI_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional biography writer who creates compelling, accurate, and tailored biographies.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];
            
            // Send request to OpenAI API
            $response = wp_remote_post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => $headers,
                    'body' => wp_json_encode($request_body),
                    'timeout' => self::API_TIMEOUT,
                    'data_format' => 'body'
                ]
            );
            
            // Check for errors
            if (is_wp_error($response)) {
                error_log('MKCG Biography: OpenAI API error - ' . $response->get_error_message());
                return [
                    'success' => false,
                    'message' => 'Error connecting to OpenAI API: ' . $response->get_error_message()
                ];
            }
            
            // Parse response
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            // Check for API errors
            if (isset($response_data['error'])) {
                error_log('MKCG Biography: OpenAI API error - ' . $response_data['error']['message']);
                return [
                    'success' => false,
                    'message' => 'OpenAI API error: ' . $response_data['error']['message']
                ];
            }
            
            // Extract content from response
            $content = $response_data['choices'][0]['message']['content'] ?? '';
            
            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'OpenAI API returned empty response.'
                ];
            }
            
            // Parse the biographies from the response
            preg_match('/SHORT BIO:\s*(.*?)(?=\s*MEDIUM BIO:|$)/s', $content, $short_matches);
            preg_match('/MEDIUM BIO:\s*(.*?)(?=\s*LONG BIO:|$)/s', $content, $medium_matches);
            preg_match('/LONG BIO:\s*(.*?)(?=\s*$)/s', $content, $long_matches);
            
            // Clean up the matches
            if (!empty($short_matches[1])) {
                $biographies['short'] = trim($short_matches[1]);
            }
            
            if (!empty($medium_matches[1])) {
                $biographies['medium'] = trim($medium_matches[1]);
            }
            
            if (!empty($long_matches[1])) {
                $biographies['long'] = trim($long_matches[1]);
            }
            
            // Check if we have at least one biography
            if (empty($biographies['short']) && empty($biographies['medium']) && empty($biographies['long'])) {
                error_log('MKCG Biography: Failed to parse biographies from OpenAI response');
                return [
                    'success' => false,
                    'message' => 'Failed to parse biographies from OpenAI response.',
                    'error_code' => 'PARSE_FAILED',
                    'raw_response' => substr($content, 0, 500) // First 500 chars for debugging
                ];
            }
            
            // Cache successful result
            $result = [
                'success' => true,
                'biographies' => $biographies,
                'settings' => [
                    'tone' => $data['tone'],
                    'pov' => $data['pov']
                ],
                'personal_info' => [
                    'name' => $data['name'],
                    'title' => $data['title'],
                    'organization' => $data['organization']
                ],
                'generation_date' => current_time('mysql'),
                'cache_key' => $cache_key
            ];
            
            $this->cache_biography_result($cache_key, $result);
            
            // Save biographies to post meta if we have a post ID
            if (!empty($data['post_id'])) {
                $this->save_biographies_to_post_meta($data['post_id'], $biographies, [
                    'tone' => $data['tone'],
                    'pov' => $data['pov']
                ]);
                
                // Update generation count and API usage
                $this->update_generation_stats($data['post_id']);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('MKCG Biography: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating biography: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modify biography tone
     * 
     * @param array $data Form data
     * @return array Modified biographies
     */
    private function modify_biography_tone($data) {
        // Check if we have post ID
        if (empty($data['post_id'])) {
            return [
                'success' => false,
                'message' => 'Post ID is required to modify tone.'
            ];
        }
        
        // Get existing biographies
        $biography_data = $this->get_biography_data($data['post_id']);
        
        // Check if we have biographies to modify
        if (!$biography_data['has_data']) {
            return [
                'success' => false,
                'message' => 'No biographies found to modify.'
            ];
        }
        
        // Set up tone guidelines
        $tone_guidelines = [
            'professional' => 'formal, authoritative, and business-appropriate',
            'conversational' => 'friendly, approachable, and conversational',
            'authoritative' => 'expert, confident, and authoritative',
            'friendly' => 'warm, approachable, and personable'
        ];
        
        // Get current tone
        $current_tone = $biography_data['settings']['tone'];
        
        // Check if requested tone is the same as current tone
        if ($current_tone === $data['tone']) {
            return [
                'success' => true,
                'biographies' => $biography_data['biographies'],
                'settings' => [
                    'tone' => $current_tone,
                    'pov' => $biography_data['settings']['pov']
                ],
                'message' => 'Tone is already set to ' . $data['tone'] . '.'
            ];
        }
        
        // Prepare the prompt for tone modification
        $prompt = "You are a professional biography editor. I have three versions of a professional biography for {$biography_data['personal_info']['name']}";
        
        if (!empty($biography_data['personal_info']['title'])) {
            $prompt .= ", a {$biography_data['personal_info']['title']}";
        }
        
        if (!empty($biography_data['personal_info']['organization'])) {
            $prompt .= " at {$biography_data['personal_info']['organization']}";
        }
        
        $prompt .= ".\n\n";
        
        $prompt .= "The current tone is {$tone_guidelines[$current_tone]}. I need you to rewrite these biographies with a {$tone_guidelines[$data['tone']]} tone instead.\n\n";
        
        $prompt .= "Please maintain the same content, length, and point of view, but change only the tone to be {$tone_guidelines[$data['tone']]}.\n\n";
        
        // Add biographies to prompt
        $prompt .= "SHORT BIO:\n{$biography_data['biographies']['short']}\n\n";
        $prompt .= "MEDIUM BIO:\n{$biography_data['biographies']['medium']}\n\n";
        $prompt .= "LONG BIO:\n{$biography_data['biographies']['long']}\n\n";
        
        $prompt .= "Format your response as follows:\n";
        $prompt .= "SHORT BIO:\n[Modified short biography here]\n\n";
        $prompt .= "MEDIUM BIO:\n[Modified medium biography here]\n\n";
        $prompt .= "LONG BIO:\n[Modified long biography here]\n";
        
        // Check if OpenAI API key is available
        $api_key = get_option('mkcg_openai_api_key');
        if (empty($api_key)) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is not configured. Please set it in the plugin settings.'
            ];
        }
        
        try {
            // Prepare API request
            $headers = [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ];
            
            $request_body = [
                'model' => self::OPENAI_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional biography editor who specializes in tone adjustments.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];
            
            // Send request to OpenAI API
            $response = wp_remote_post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => $headers,
                    'body' => wp_json_encode($request_body),
                    'timeout' => 60,
                    'data_format' => 'body'
                ]
            );
            
            // Check for errors
            if (is_wp_error($response)) {
                error_log('MKCG Biography: OpenAI API error - ' . $response->get_error_message());
                return [
                    'success' => false,
                    'message' => 'Error connecting to OpenAI API: ' . $response->get_error_message()
                ];
            }
            
            // Parse response
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            // Check for API errors
            if (isset($response_data['error'])) {
                error_log('MKCG Biography: OpenAI API error - ' . $response_data['error']['message']);
                return [
                    'success' => false,
                    'message' => 'OpenAI API error: ' . $response_data['error']['message']
                ];
            }
            
            // Extract content from response
            $content = $response_data['choices'][0]['message']['content'] ?? '';
            
            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'OpenAI API returned empty response.'
                ];
            }
            
            // Parse the biographies from the response
            preg_match('/SHORT BIO:\s*(.*?)(?=\s*MEDIUM BIO:|$)/s', $content, $short_matches);
            preg_match('/MEDIUM BIO:\s*(.*?)(?=\s*LONG BIO:|$)/s', $content, $medium_matches);
            preg_match('/LONG BIO:\s*(.*?)(?=\s*$)/s', $content, $long_matches);
            
            // Initialize modified biographies with existing ones
            $modified_biographies = $biography_data['biographies'];
            
            // Update with modified biographies if available
            if (!empty($short_matches[1])) {
                $modified_biographies['short'] = trim($short_matches[1]);
            }
            
            if (!empty($medium_matches[1])) {
                $modified_biographies['medium'] = trim($medium_matches[1]);
            }
            
            if (!empty($long_matches[1])) {
                $modified_biographies['long'] = trim($long_matches[1]);
            }
            
            // Save modified biographies to post meta
            $this->save_biographies_to_post_meta($data['post_id'], $modified_biographies, [
                'tone' => $data['tone'],
                'pov' => $biography_data['settings']['pov']
            ]);
            
            return [
                'success' => true,
                'biographies' => $modified_biographies,
                'settings' => [
                    'tone' => $data['tone'],
                    'pov' => $biography_data['settings']['pov']
                ],
                'message' => 'Biography tone updated successfully.'
            ];
        } catch (Exception $e) {
            error_log('MKCG Biography: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error modifying biography tone: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Save biographies to post meta
     * 
     * @param int $post_id Post ID
     * @param array $biographies Biographies to save
     * @param array $settings Settings to save
     * @return bool Success status
     */
    private function save_biographies_to_post_meta($post_id, $biographies, $settings) {
        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        // Save biographies
        update_post_meta($post_id, $this->post_meta_fields['short_bio'], $biographies['short']);
        update_post_meta($post_id, $this->post_meta_fields['medium_bio'], $biographies['medium']);
        update_post_meta($post_id, $this->post_meta_fields['long_bio'], $biographies['long']);
        
        // Save settings
        update_post_meta($post_id, $this->post_meta_fields['tone'], $settings['tone']);
        update_post_meta($post_id, $this->post_meta_fields['pov'], $settings['pov']);
        update_post_meta($post_id, $this->post_meta_fields['generation_date'], current_time('mysql'));
        
        return true;
    }
    
    /**
     * Generate cache key for biography request
     */
    private function generate_cache_key($data) {
        $key_data = [
            'name' => $data['name'],
            'title' => $data['title'],
            'organization' => $data['organization'],
            'tone' => $data['tone'],
            'pov' => $data['pov'],
            'authority_hook' => $data['authority_hook'],
            'impact_intro' => $data['impact_intro'],
            'existing_bio' => $data['existing_bio']
        ];
        
        return 'mkcg_bio_' . md5(serialize($key_data));
    }
    
    /**
     * Get cached biography result
     */
    private function get_cached_biography($cache_key) {
        return get_transient($cache_key);
    }
    
    /**
     * Cache biography result
     */
    private function cache_biography_result($cache_key, $result) {
        set_transient($cache_key, $result, self::CACHE_DURATION);
    }
    
    /**
     * Validate OpenAI API key format
     */
    private function validate_api_key_format($api_key) {
        // OpenAI API keys typically start with 'sk-' and are 51 characters long
        return (strlen($api_key) >= 20 && strpos($api_key, 'sk-') === 0);
    }
    
    /**
     * Update generation statistics
     */
    private function update_generation_stats($post_id) {
        // Update generation count
        $current_count = get_post_meta($post_id, $this->post_meta_fields['generation_count'], true);
        $new_count = intval($current_count) + 1;
        update_post_meta($post_id, $this->post_meta_fields['generation_count'], $new_count);
        
        // Update last modified
        update_post_meta($post_id, $this->post_meta_fields['last_modified'], current_time('mysql'));
        
        // Update API usage statistics
        $api_usage = get_post_meta($post_id, $this->post_meta_fields['api_usage'], true);
        if (!is_array($api_usage)) {
            $api_usage = [];
        }
        
        $today = date('Y-m-d');
        if (!isset($api_usage[$today])) {
            $api_usage[$today] = 0;
        }
        $api_usage[$today]++;
        
        // Keep only last 30 days
        $cutoff_date = date('Y-m-d', strtotime('-30 days'));
        foreach ($api_usage as $date => $count) {
            if ($date < $cutoff_date) {
                unset($api_usage[$date]);
            }
        }
        
        update_post_meta($post_id, $this->post_meta_fields['api_usage'], $api_usage);
    }
    
    /**
     * Add meta boxes for biography data
     */
    public function add_meta_boxes() {
        $post_types = ['post', 'page', 'guests'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'mkcg_biography_data',
                'Biography Generator Data',
                [$this, 'render_meta_box'],
                $post_type,
                'normal',
                'default'
            );
        }
    }
    
    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        $biography_data = $this->get_biography_data($post->ID);
        
        wp_nonce_field('mkcg_biography_meta_box', 'mkcg_biography_nonce');
        
        echo '<div class="mkcg-meta-box">';
        echo '<h4>Generated Biographies</h4>';
        
        if ($biography_data['has_data']) {
            foreach (['short', 'medium', 'long'] as $length) {
                $bio = $biography_data['biographies'][$length];
                if (!empty($bio)) {
                    echo '<div class="biography-section">';
                    echo '<h5>' . ucfirst($length) . ' Biography</h5>';
                    echo '<textarea readonly rows="4" style="width:100%;">' . esc_textarea($bio) . '</textarea>';
                    echo '<p><small>Word count: ' . str_word_count($bio) . '</small></p>';
                    echo '</div>';
                }
            }
            
            echo '<div class="biography-settings">';
            echo '<p><strong>Settings:</strong> Tone: ' . esc_html($biography_data['settings']['tone']) . ', POV: ' . esc_html($biography_data['settings']['pov']) . '</p>';
            echo '<p><strong>Generated:</strong> ' . esc_html($biography_data['generation_date']) . '</p>';
            echo '</div>';
        } else {
            echo '<p>No biographies generated yet.</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Save post data hook
     */
    public function save_post_data($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['mkcg_biography_nonce']) || !wp_verify_nonce($_POST['mkcg_biography_nonce'], 'mkcg_biography_meta_box')) {
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Auto-save handling
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Update last modified timestamp
        update_post_meta($post_id, $this->post_meta_fields['last_modified'], current_time('mysql'));
    }
    
    /**
     * Enhanced AJAX handler for generating biography with comprehensive security
     */
    public function ajax_generate_biography() {
        // Enhanced security verification
        $security_check = $this->verify_security_comprehensive('generate');
        if (!$security_check['success']) {
            wp_send_json_error($security_check);
            return;
        }
        
        // Enhanced data collection and validation
        $raw_data = [
            'post_id' => isset($_POST['post_id']) ? $_POST['post_id'] : 0,
            'entry_id' => isset($_POST['entry_id']) ? $_POST['entry_id'] : 0,
            'authority_hook' => isset($_POST['authority_hook']) ? $_POST['authority_hook'] : '',
            'impact_intro' => isset($_POST['impact_intro']) ? $_POST['impact_intro'] : '',
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'organization' => isset($_POST['organization']) ? $_POST['organization'] : '',
            'tone' => isset($_POST['tone']) ? $_POST['tone'] : $this->default_settings['tone'],
            'pov' => isset($_POST['pov']) ? $_POST['pov'] : $this->default_settings['pov'],
            'length' => isset($_POST['length']) ? $_POST['length'] : $this->default_settings['length'],
            'existing_bio' => isset($_POST['existing_bio']) ? $_POST['existing_bio'] : '',
            'additional_notes' => isset($_POST['additional_notes']) ? $_POST['additional_notes'] : ''
        ];
        
        // Validate and sanitize input
        $validation_result = $this->validate_and_sanitize_input($raw_data);
        if (!$validation_result['success']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors']),
                'errors' => $validation_result['errors']
            ]);
            return;
        }
        
        $data = $validation_result['data'];
        
        // Generate biography
        $result = $this->generate_biography($data);
        
        if ($result['success']) {
            // Log successful generation
            error_log('MKCG Biography: Successfully generated biography for post ' . $data['post_id']);
            wp_send_json_success($result);
        } else {
            // Log failed generation
            error_log('MKCG Biography: Failed to generate biography - ' . $result['message']);
            wp_send_json_error($result);
        }
    }
    
    /**
     * Enhanced AJAX handler for modifying biography tone
     */
    public function ajax_modify_biography_tone() {
        // Enhanced security verification
        $security_check = $this->verify_security_comprehensive('modify_tone');
        if (!$security_check['success']) {
            wp_send_json_error($security_check);
            return;
        }
        
        // Enhanced data collection and validation
        $raw_data = [
            'post_id' => isset($_POST['post_id']) ? $_POST['post_id'] : 0,
            'tone' => isset($_POST['tone']) ? $_POST['tone'] : $this->default_settings['tone']
        ];
        
        // Validate input
        $validation_result = $this->validate_and_sanitize_input($raw_data);
        if (!$validation_result['success']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors']),
                'errors' => $validation_result['errors']
            ]);
            return;
        }
        
        $data = $validation_result['data'];
        
        // Modify biography tone
        $result = $this->modify_biography_tone($data);
        
        if ($result['success']) {
            error_log('MKCG Biography: Successfully modified tone for post ' . $data['post_id']);
            wp_send_json_success($result);
        } else {
            error_log('MKCG Biography: Failed to modify tone - ' . $result['message']);
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX handler for saving biography to Formidable Forms
     */
    public function ajax_save_biography_to_formidable() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Check if we have entry ID
        if (!isset($_POST['entry_id']) || intval($_POST['entry_id']) <= 0) {
            wp_send_json_error(['message' => 'Entry ID is required.']);
            return;
        }
        
        // Check if we have post ID
        if (!isset($_POST['post_id']) || intval($_POST['post_id']) <= 0) {
            wp_send_json_error(['message' => 'Post ID is required.']);
            return;
        }
        
        // Get post ID and entry ID
        $post_id = intval($_POST['post_id']);
        $entry_id = intval($_POST['entry_id']);
        
        // Get biography data from post meta
        $biography_data = $this->get_biography_data($post_id);
        
        // Check if we have biographies to save
        if (!$biography_data['has_data']) {
            wp_send_json_error(['message' => 'No biographies found to save.']);
            return;
        }
        
        // Save biographies to Formidable Forms
        $result = $this->save_biographies_to_formidable($entry_id, $biography_data['biographies'], $biography_data['settings']);
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Biographies saved to Formidable Forms successfully.',
                'entry_id' => $entry_id
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save biographies to Formidable Forms.',
                'entry_id' => $entry_id
            ]);
        }
    }
    
    /**
     * AJAX handler for saving all biography data - Following Topics Generator pattern
     */
    public function ajax_save_biography_data() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Check if we have post ID
        if (!isset($_POST['post_id']) || intval($_POST['post_id']) <= 0) {
            wp_send_json_error(['message' => 'Post ID is required.']);
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        
        // Collect form data
        $form_data = [
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'organization' => isset($_POST['organization']) ? sanitize_text_field($_POST['organization']) : '',
            'tone' => isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : $this->default_settings['tone'],
            'length' => isset($_POST['length']) ? sanitize_text_field($_POST['length']) : $this->default_settings['length'],
            'pov' => isset($_POST['pov']) ? sanitize_text_field($_POST['pov']) : $this->default_settings['pov'],
            'existingBio' => isset($_POST['existingBio']) ? sanitize_textarea_field($_POST['existingBio']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
        ];
        
        // Authority Hook components
        $authority_hook = [
            'who' => isset($_POST['authority_who']) ? sanitize_text_field($_POST['authority_who']) : '',
            'what' => isset($_POST['authority_what']) ? sanitize_text_field($_POST['authority_what']) : '',
            'when' => isset($_POST['authority_when']) ? sanitize_text_field($_POST['authority_when']) : '',
            'how' => isset($_POST['authority_how']) ? sanitize_text_field($_POST['authority_how']) : ''
        ];
        
        // Impact Intro components
        $impact_intro = [
            'where' => isset($_POST['impact_where']) ? sanitize_text_field($_POST['impact_where']) : '',
            'why' => isset($_POST['impact_why']) ? sanitize_text_field($_POST['impact_why']) : ''
        ];
        
        // Biographies (if provided)
        $biographies = [];
        if (isset($_POST['biographies']) && is_array($_POST['biographies'])) {
            $biographies = [
                'short' => isset($_POST['biographies']['short']) ? sanitize_textarea_field($_POST['biographies']['short']) : '',
                'medium' => isset($_POST['biographies']['medium']) ? sanitize_textarea_field($_POST['biographies']['medium']) : '',
                'long' => isset($_POST['biographies']['long']) ? sanitize_textarea_field($_POST['biographies']['long']) : ''
            ];
        }
        
        // Save basic biography fields to post meta
        update_post_meta($post_id, '_biography_name', $form_data['name']);
        update_post_meta($post_id, '_biography_title', $form_data['title']);
        update_post_meta($post_id, '_biography_organization', $form_data['organization']);
        update_post_meta($post_id, '_biography_existing', $form_data['existingBio']);
        update_post_meta($post_id, '_biography_notes', $form_data['notes']);
        
        // Save settings
        update_post_meta($post_id, $this->post_meta_fields['tone'], $form_data['tone']);
        update_post_meta($post_id, $this->post_meta_fields['pov'], $form_data['pov']);
        
        // Save Authority Hook data using centralized service
        if (class_exists('MKCG_Authority_Hook_Service')) {
            $authority_service = new MKCG_Authority_Hook_Service();
            $authority_service->save_authority_hook_data($post_id, $authority_hook);
        }
        
        // Save Impact Intro data using centralized service
        if (class_exists('MKCG_Impact_Intro_Service')) {
            $impact_service = new MKCG_Impact_Intro_Service();
            $impact_service->save_impact_intro_data($post_id, $impact_intro);
        }
        
        // Save biographies if provided
        if (!empty($biographies['short']) || !empty($biographies['medium']) || !empty($biographies['long'])) {
            $this->save_biographies_to_post_meta($post_id, $biographies, [
                'tone' => $form_data['tone'],
                'pov' => $form_data['pov']
            ]);
        }
        
        wp_send_json_success([
            'message' => 'Biography data saved successfully.',
            'post_id' => $post_id,
            'form_data' => $form_data,
            'biographies' => $biographies
        ]);
    }
    
    /**
     * AJAX handler for auto-saving individual biography fields
     */
    public function ajax_save_biography_field() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Check if we have post ID
        if (!isset($_POST['post_id']) || intval($_POST['post_id']) <= 0) {
            wp_send_json_error(['message' => 'Post ID is required.']);
            return;
        }
        
        // Check if we have field name and value
        if (!isset($_POST['field_name']) || !isset($_POST['field_value'])) {
            wp_send_json_error(['message' => 'Field name and value are required.']);
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $field_name = sanitize_text_field($_POST['field_name']);
        $field_value = sanitize_textarea_field($_POST['field_value']);
        
        // Map field names to post meta keys
        $field_mapping = [
            'biography-name' => '_biography_name',
            'name' => '_biography_name',
            'biography-title' => '_biography_title',
            'title' => '_biography_title',
            'biography-organization' => '_biography_organization',
            'organization' => '_biography_organization',
            'biography-existing' => '_biography_existing',
            'existingBio' => '_biography_existing',
            'biography-notes' => '_biography_notes',
            'notes' => '_biography_notes'
        ];
        
        // Get the meta key for this field
        $meta_key = isset($field_mapping[$field_name]) ? $field_mapping[$field_name] : '_biography_' . $field_name;
        
        // Save the field
        $result = update_post_meta($post_id, $meta_key, $field_value);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Field saved successfully.',
                'field_name' => $field_name,
                'meta_key' => $meta_key
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save field.',
                'field_name' => $field_name
            ]);
        }
    }
    
    /**
     * AJAX handler for getting biography data
     */
    public function ajax_get_biography_data() {
        // Enhanced security verification
        $security_check = $this->verify_security_comprehensive('get');
        if (!$security_check['success']) {
            wp_send_json_error($security_check);
            return;
        }
        
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        // Get biography data
        $biography_data = $this->get_biography_data($post_id);
        
        wp_send_json_success($biography_data);
    }
    
    /**
     * AJAX handler for validating biography data
     */
    public function ajax_validate_biography_data() {
        // Enhanced security verification
        $security_check = $this->verify_security_comprehensive('validate');
        if (!$security_check['success']) {
            wp_send_json_error($security_check);
            return;
        }
        
        // Collect data for validation
        $raw_data = [
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'organization' => isset($_POST['organization']) ? $_POST['organization'] : '',
            'tone' => isset($_POST['tone']) ? $_POST['tone'] : '',
            'pov' => isset($_POST['pov']) ? $_POST['pov'] : '',
            'authority_hook' => isset($_POST['authority_hook']) ? $_POST['authority_hook'] : '',
            'impact_intro' => isset($_POST['impact_intro']) ? $_POST['impact_intro'] : ''
        ];
        
        // Validate input
        $validation_result = $this->validate_and_sanitize_input($raw_data);
        
        wp_send_json_success([
            'valid' => $validation_result['success'],
            'errors' => $validation_result['errors'],
            'data' => $validation_result['data']
        ]);
    }
    
    /**
     * AJAX handler for regenerating biography with modified settings
     */
    public function ajax_regenerate_biography() {
        // Enhanced security verification
        $security_check = $this->verify_security_comprehensive('regenerate');
        if (!$security_check['success']) {
            wp_send_json_error($security_check);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        // Get existing biography data
        $existing_data = $this->get_biography_data($post_id);
        
        if (!$existing_data['has_data']) {
            wp_send_json_error(['message' => 'No existing biography data found']);
            return;
        }
        
        // Merge with new settings
        $regeneration_data = array_merge($existing_data['personal_info'], [
            'post_id' => $post_id,
            'tone' => isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : $existing_data['settings']['tone'],
            'pov' => isset($_POST['pov']) ? sanitize_text_field($_POST['pov']) : $existing_data['settings']['pov'],
            'length' => isset($_POST['length']) ? sanitize_text_field($_POST['length']) : $this->default_settings['length'],
            'authority_hook' => isset($_POST['authority_hook']) ? sanitize_textarea_field($_POST['authority_hook']) : '',
            'impact_intro' => isset($_POST['impact_intro']) ? sanitize_textarea_field($_POST['impact_intro']) : '',
            'existing_bio' => isset($_POST['existing_bio']) ? sanitize_textarea_field($_POST['existing_bio']) : '',
            'additional_notes' => isset($_POST['additional_notes']) ? sanitize_textarea_field($_POST['additional_notes']) : ''
        ]);
        
        // Clear cache for this regeneration
        $cache_key = $this->generate_cache_key($regeneration_data);
        delete_transient($cache_key);
        
        // Generate new biography
        $result = $this->generate_biography($regeneration_data);
        
        if ($result['success']) {
            error_log('MKCG Biography: Successfully regenerated biography for post ' . $post_id);
            wp_send_json_success($result);
        } else {
            error_log('MKCG Biography: Failed to regenerate biography - ' . $result['message']);
            wp_send_json_error($result);
        }
    }
}

// Initialize the generator
new MKCG_Enhanced_Biography_Generator();