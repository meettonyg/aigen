<?php
/**
 * MKCG Topics Generator
 * Generates interview topics based on Authority Hook and audience
 */

class MKCG_Topics_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'topics';
    // STANDALONE MODE: Simplified configuration
    protected $max_topics = 5;
    protected $max_retries = 3;
    protected $cache_duration = 3600; // 1 hour
    
    // CRITICAL FIX: Add missing service properties
    protected $unified_data_service;
    protected $topics_data_service;
    
    /**
     * Constructor - ENHANCED with proper service initialization
     */
    public function __construct($api_service, $formidable_service, $authority_hook_service = null) {
        parent::__construct($api_service, $formidable_service, $authority_hook_service);
        
        // CRITICAL FIX: Initialize Topics Data Service if available
        $this->init_data_services();
        
        error_log('MKCG Topics Generator: 📋 Initialized with enhanced service integration');
    }
    
    /**
     * CRITICAL FIX: Initialize data services with proper error handling
     */
    private function init_data_services() {
        try {
            // Initialize Topics Data Service
            if (class_exists('MKCG_Topics_Data_Service')) {
                $this->topics_data_service = new MKCG_Topics_Data_Service($this->formidable_service);
                error_log('MKCG Topics Generator: ✅ Topics Data Service initialized');
            } else {
                error_log('MKCG Topics Generator: ⚠️ Topics Data Service class not available');
            }
            
            // Initialize Unified Data Service if available
            if (class_exists('MKCG_Unified_Data_Service')) {
                $this->unified_data_service = new MKCG_Unified_Data_Service();
                error_log('MKCG Topics Generator: ✅ Unified Data Service initialized');
            } else {
                error_log('MKCG Topics Generator: ⚠️ Unified Data Service class not available');
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Exception initializing data services: ' . $e->getMessage());
            $this->topics_data_service = null;
            $this->unified_data_service = null;
        }
    }
    
    /**
     * CRITICAL FIX: Check if Topics Data Service is available
     */
    public function is_topics_service_available() {
        return ($this->topics_data_service !== null && is_object($this->topics_data_service));
    }
    
    /**
     * Get form fields configuration
     */
    public function get_form_fields() {
        return [
            'authority_hook' => [
                'type' => 'textarea',
                'label' => 'Authority Hook',
                'required' => true,
                'description' => 'Your expert introduction statement'
            ],
            'audience' => [
                'type' => 'text',
                'label' => 'Target Audience (Optional)',
                'required' => false,
                'description' => 'Specific audience for the topics'
            ]
        ];
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data) {
        $errors = [];
        
        if (empty($data['authority_hook'])) {
            $errors[] = 'Authority Hook is required';
        }
        
        if (!empty($data['authority_hook']) && strlen($data['authority_hook']) < 10) {
            $errors[] = 'Authority Hook must be at least 10 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for topic generation
     */
    public function build_prompt($data) {
        $authority_hook = $data['authority_hook'];
        $audience = isset($data['audience']) ? $data['audience'] : '';
        
        $prompt = "You are an AI assistant specialized in generating **highly relevant interview topics** that align **only with the expert's authority**.

The expert's area of expertise is: \"$authority_hook\".

### **Key Requirements for Topics:**
- Topics **must directly relate to the expert's authority**—avoid unrelated subjects like podcasting unless explicitly relevant.
- Topics should be **intriguing, insightful, and results-driven** to attract podcast hosts.
- Use **specific strategies, case studies, or proven methods** within the expert's domain.
- If an audience is provided, ensure topics speak **specifically to their challenges and goals**.

";

        if (!empty($audience)) {
            $prompt .= "### **Target Audience:** \"$audience\"\nEnsure the topics focus on **the concerns, pain points, and goals of this audience.**\n";
        }

        $prompt .= "### **Example High-Performing Podcast Topics in the Given Niche:**
1. 'The Hidden Wealth in Tax Overages: How Future Retirees Can Unlock Passive Income'
2. 'Tax Overages Explained: How to Identify and Claim Overlooked Funds'
3. '5 Steps to Turning Tax Surplus into a Lucrative Side Business'
4. 'How Future Retirees Can Secure Financial Freedom with Unclaimed Tax Overages'
5. 'From Overages to Income: The Legal and Financial Aspects of Claiming Tax Surpluses'

### **Now generate 5 unique, compelling podcast topics based on the expert introduction. Format as a numbered list (1., 2., etc.), with one topic per line.**";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service already formats topics as an array
        if (is_array($api_response)) {
            $topics = $api_response;
        } else {
            // Fallback if raw string returned
            $topics = [];
            if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
                $topics = array_map('trim', $matches[1]);
            } else {
                $topics = array_filter(array_map(function($t) {
                    return trim($t, " '\"");
                }, explode("\n", $api_response)));
            }
        }
        
        // Format for Formidable field mapping
        $formatted = [
            'topics' => $topics,
            'count' => count($topics)
        ];
        
        // Map individual topics to fields for form 515
        for ($i = 0; $i < min(5, count($topics)); $i++) {
            $formatted['topic_' . ($i + 1)] = $topics[$i];
        }
        
        return $formatted;
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        return [
            'audience' => isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : ''
        ];
    }
    
    /**
     * Get field mappings using centralized configuration
     */
    protected function get_field_mappings() {
        return MKCG_Config::get_field_mappings()['topics'];
    }
    
    /**
     * Get specific field ID using centralized helper
     */
    protected function get_field_id($field_key) {
        return MKCG_Config::get_field_id('topics', $field_key);
    }
    
    /**
     * Get authority hook component field mappings using centralized configuration
     */
    public function get_authority_hook_field_mappings() {
        return MKCG_Config::get_field_mappings()['authority_hook']['fields'];
    }
    
    /**
     * Build authority hook from components - DELEGATED TO SERVICE
     */
    public function build_authority_hook_from_components($entry_id) {
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        $components = [];
        foreach (['who', 'result', 'when', 'how'] as $component) {
            $components[$component] = $this->formidable_service->get_field_value($entry_id, $field_mappings[$component]) ?: '';
        }
        
        // Delegate to centralized Authority Hook Service
        return $this->authority_hook_service->build_authority_hook($components);
    }
    
    /**
     * Save authority hook components to Formidable - DELEGATED TO SERVICE
     */
    public function save_authority_hook_components($entry_id, $who, $result, $when, $how) {
        $components = [
            'who' => $who,
            'result' => $result,
            'when' => $when,
            'how' => $how
        ];
        
        // Build complete authority hook using centralized service
        $complete_hook = $this->authority_hook_service->build_authority_hook($components);
        
        // Save using Formidable service with proper field mappings
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        $data_to_save = $components;
        $data_to_save['complete'] = $complete_hook;
        
        $field_mapping_for_save = [];
        foreach ($data_to_save as $key => $value) {
            if (isset($field_mappings[$key])) {
                $field_mapping_for_save[$key] = $field_mappings[$key];
            }
        }
        
        $result = $this->formidable_service->save_generated_content(
            $entry_id,
            $data_to_save,
            $field_mapping_for_save
        );
        
        return [
            'success' => $result['success'],
            'saved_fields' => $field_mapping_for_save,
            'authority_hook' => $complete_hook,
            'errors' => $result['errors'] ?? []
        ];
    }
    
    /**
     * CRITICAL FIX: Safe authority hook components save with enhanced error handling
     */
    public function save_authority_hook_components_safe($entry_id, $who, $result, $when, $how) {
        try {
            error_log('MKCG Topics Generator: Starting safe authority hook save for entry ' . $entry_id);
            
            // Validate entry ID
            if (!$entry_id || $entry_id <= 0) {
                return [
                    'success' => false,
                    'errors' => ['Invalid entry ID provided'],
                    'authority_hook' => '',
                    'saved_fields' => []
                ];
            }
            
            // Sanitize components
            $components = [
                'who' => sanitize_textarea_field($who ?: 'your audience'),
                'result' => sanitize_textarea_field($result ?: 'achieve their goals'),
                'when' => sanitize_textarea_field($when ?: 'they need help'),
                'how' => sanitize_textarea_field($how ?: 'through your method')
            ];
            
            error_log('MKCG Topics Generator: Sanitized components: ' . json_encode($components));
            
            // Use the existing method but with enhanced error handling
            $save_result = $this->save_authority_hook_components(
                $entry_id, 
                $components['who'], 
                $components['result'], 
                $components['when'], 
                $components['how']
            );
            
            // Enhanced error handling and logging
            if ($save_result['success']) {
                error_log('MKCG Topics Generator: ✅ Authority hook components saved successfully');
                return $save_result;
            } else {
                error_log('MKCG Topics Generator: ❌ Authority hook save failed: ' . json_encode($save_result['errors'] ?? ['Unknown error']));
                
                // Return formatted error response
                return [
                    'success' => false,
                    'errors' => $save_result['errors'] ?? ['Failed to save authority hook components'],
                    'authority_hook' => $this->authority_hook_service->build_authority_hook($components),
                    'saved_fields' => [],
                    'debug_info' => [
                        'entry_id' => $entry_id,
                        'components_provided' => !empty($who) || !empty($result) || !empty($when) || !empty($how),
                        'authority_hook_service_available' => is_object($this->authority_hook_service),
                        'formidable_service_available' => is_object($this->formidable_service)
                    ]
                ];
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Exception in save_authority_hook_components_safe: ' . $e->getMessage());
            
            return [
                'success' => false,
                'errors' => ['Server error: ' . $e->getMessage()],
                'authority_hook' => '',
                'saved_fields' => [],
                'exception_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
    
    // REMOVED: Redundant field processing methods - now using centralized Formidable Service
    
    /**
     * STANDALONE MODE: Get template data directly from Formidable service
     * Simplified data loading for standalone operation
     */
    public function get_template_data($entry_key = '') {
        // Initialize empty data structure
        $template_data = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'authority_hook_components' => [
                'who' => 'your audience',
                'result' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method',
                'complete' => 'I help your audience achieve their goals when they need help through your method.'
            ],
            'form_field_values' => [
                'topic_1' => '',
                'topic_2' => '',
                'topic_3' => '',
                'topic_4' => '',
                'topic_5' => ''
            ],
            'has_entry' => false
        ];
        
        // If no entry key provided, try to get from URL
        if (empty($entry_key) && isset($_GET['entry'])) {
            $entry_key = sanitize_text_field($_GET['entry']);
            $template_data['entry_key'] = $entry_key;
        }
        
        // STANDALONE MODE: Load data directly from Formidable service
        if (!empty($entry_key) && $this->formidable_service) {
            error_log('MKCG Topics Generator: Loading data directly from Formidable for entry_key: ' . $entry_key);
            
            try {
                // Get entry ID from entry key
                $entry_data = $this->formidable_service->get_entry_data($entry_key);
                
                if (!empty($entry_data['entry_id'])) {
                    $entry_id = $entry_data['entry_id'];
                    $template_data['entry_id'] = $entry_id;
                    $template_data['has_entry'] = true;
                    
                    // Load topics from Formidable fields directly
                    $field_mappings = $this->get_field_mappings();
                    
                    foreach (['topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5'] as $topic_key) {
                        if (isset($field_mappings['fields'][$topic_key])) {
                            $field_id = $field_mappings['fields'][$topic_key];
                            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                            if (!empty($value)) {
                                $template_data['form_field_values'][$topic_key] = $value;
                            }
                        }
                    }
                    
                    // Load authority hook components from Formidable fields
                    $auth_mappings = $this->get_authority_hook_field_mappings();
                    foreach (['who', 'result', 'when', 'how'] as $component) {
                        if (isset($auth_mappings[$component])) {
                            $field_id = $auth_mappings[$component];
                            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                            if (!empty($value)) {
                                $template_data['authority_hook_components'][$component] = $value;
                            }
                        }
                    }
                    
                    // Rebuild complete authority hook
                    $template_data['authority_hook_components']['complete'] = $this->authority_hook_service->build_authority_hook($template_data['authority_hook_components']);
                    
                    error_log('MKCG Topics Generator: ✅ Successfully loaded data from Formidable');
                    error_log('MKCG Topics Generator: Entry ID: ' . $template_data['entry_id']);
                    error_log('MKCG Topics Generator: Topics loaded: ' . json_encode($template_data['form_field_values']));
                    
                } else {
                    error_log('MKCG Topics Generator: ❌ Could not get entry ID from entry_key: ' . $entry_key);
                }
                
            } catch (Exception $e) {
                error_log('MKCG Topics Generator: ❌ Exception loading from Formidable: ' . $e->getMessage());
            }
        } elseif (!empty($entry_key) && !$this->formidable_service) {
            error_log('MKCG Topics Generator: ❌ CRITICAL ERROR - Formidable service not available for entry_key: ' . $entry_key);
        } elseif (empty($entry_key)) {
            error_log('MKCG Topics Generator: No entry_key provided - using defaults');
        }
        
        // STANDALONE MODE: Simple default handling
        if ($template_data['entry_id'] === 0) {
            error_log('MKCG Topics Generator: No entry found - using defaults');
        } else {
            error_log('MKCG Topics Generator: Entry found - using loaded data');
        }
        
        return $template_data;
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
    }
    
    /**
     * Override AJAX generation to handle legacy compatibility
     */
    public function handle_ajax_generation() {
        // Handle legacy action name for backwards compatibility
        if (isset($_POST['action']) && $_POST['action'] === 'generate_interview_topics') {
            $this->handle_legacy_topics_generation();
            return;
        }
        
        // Call parent method for new unified handling
        parent::handle_ajax_generation();
    }
    
    /**
     * Handle legacy topics generation (for backwards compatibility)
     */
    private function handle_legacy_topics_generation() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            error_log('Invalid entry ID: ' . $_POST['entry_id']);
            wp_send_json_error(['message' => 'Invalid entry ID.']);
            return;
        }
        
        // Get Authority Hook using the service
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if (!$authority_hook_result['success']) {
            wp_send_json_error($authority_hook_result);
            return;
        }
        
        $authority_hook = $authority_hook_result['value'];
        $audience = isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : '';
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'authority_hook' => $authority_hook,
            'audience' => $audience
        ];
        
        // Validate
        $validation_result = $this->validate_input($input_data);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($input_data);
        
        // Generate content
        $api_response = $this->api_service->generate_content($prompt, $this->generator_type);
        
        if (!$api_response['success']) {
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        // Save topics using unified service
        $topics_wrapper = $this->unified_data_service->get_topics_service();
        
        // Map topics to proper format for unified service
        $topics_data = [];
        for ($i = 0; $i < min(5, count($formatted_output['topics'])); $i++) {
            $topics_data[$i + 1] = $formatted_output['topics'][$i];
        }
        
        // Get post_id from entry
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if ($post_id) {
            $save_result = $topics_wrapper->save_topics_data($topics_data, $post_id, $entry_id);
            
            if (!$save_result['success']) {
                error_log('MKCG Topics Generator: Failed to save via unified service: ' . print_r($save_result, true));
            }
        } else {
            error_log('MKCG Topics Generator: No post_id found for entry ' . $entry_id);
        }
        
        // Return in legacy format for compatibility
        wp_send_json_success([
            'topics' => $formatted_output['topics']
        ]);
    }
    
    /**
     * Initialize with all required AJAX actions
     */
    public function init() {
        parent::init();
        
        // CRITICAL FIX: Add back missing AJAX handlers that JavaScript depends on
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'handle_get_topics_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_get_topics_data', [$this, 'handle_get_topics_data_ajax']);
        
        add_action('wp_ajax_mkcg_save_topics_data', [$this, 'handle_save_topics_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topics_data', [$this, 'handle_save_topics_data_ajax']);
        
        add_action('wp_ajax_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        
        // CRITICAL FIX: Add missing authority hook save handlers
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'handle_save_authority_hook_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook', [$this, 'handle_save_authority_hook_ajax']);
        
        add_action('wp_ajax_mkcg_save_field', [$this, 'handle_save_field_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_field', [$this, 'handle_save_field_ajax']);
        
        add_action('wp_ajax_mkcg_save_topic_field', [$this, 'handle_save_topic_field_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topic_field', [$this, 'handle_save_topic_field_ajax']);
        
        // Legacy AJAX actions for backwards compatibility
        add_action('wp_ajax_generate_interview_topics', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_topics', [$this, 'handle_ajax_generation']);
        
        add_action('wp_ajax_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
        add_action('wp_ajax_nopriv_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
    }
    
    /**
     * Handle legacy fetch authority hook request
     */
    public function handle_fetch_authority_hook() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID']);
            return;
        }
        
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if ($authority_hook_result['success']) {
            wp_send_json_success([
                'authority_hook' => $authority_hook_result['value']
            ]);
        } else {
            wp_send_json_error($authority_hook_result);
        }
    }
    
    /**
     * CRITICAL FIX: Handle get topics data AJAX request using Topics Data Service
     */
    public function handle_get_topics_data_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security([]);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        error_log('MKCG Topics AJAX: Get topics data request - entry_id=' . $entry_id . ', entry_key=' . $entry_key . ', post_id=' . $post_id);
        
        // CRITICAL FIX: Use Topics Data Service (same as Questions Generator)
        if ($this->is_topics_service_available()) {
            try {
                // Use Topics Data Service for consistent data loading
                $result = $this->topics_data_service->get_topics_data($entry_id, $entry_key, $post_id);
                
                if ($result['success']) {
                    wp_send_json_success([
                        'entry_id' => $result['entry_id'],
                        'authority_hook' => $result['authority_hook'],
                        'topics' => $result['topics'],
                        'has_entry' => true,
                        'data_quality' => $result['data_quality'],
                        'source' => $result['source']
                    ]);
                } else {
                    wp_send_json_error([
                        'message' => $result['message'] ?? 'Failed to load topics data',
                        'entry_key' => $entry_key,
                        'entry_id' => $entry_id
                    ]);
                }
            } catch (Exception $e) {
                error_log('MKCG Topics AJAX: Exception in Topics Data Service: ' . $e->getMessage());
                wp_send_json_error([
                    'message' => 'Service error: ' . $e->getMessage()
                ]);
            }
        } else {
            error_log('MKCG Topics AJAX: Topics Data Service not available - using fallback');
            wp_send_json_error([
                'message' => 'Topics Data Service not available',
                'fallback' => true
            ]);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save topics data AJAX request
     */
    public function handle_save_topics_data_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['post_id', 'topics']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $topics = $_POST['topics'];
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        // Delegate to unified service if available
        if ($this->unified_data_service) {
            $topics_service = $this->unified_data_service->get_topics_service();
            $result = $topics_service->save_topics_data($topics, $post_id, $entry_id);
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
        } else {
            wp_send_json_error(['message' => 'Unified data service not available']);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save single topic AJAX request
     */
    public function handle_save_topic_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['post_id', 'topic_number', 'topic_text']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $topic_number = intval($_POST['topic_number']);
        $topic_text = sanitize_textarea_field($_POST['topic_text']);
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        if (!$post_id || !$topic_number || empty($topic_text)) {
            wp_send_json_error(['message' => 'Missing required parameters']);
            return;
        }
        
        if ($topic_number < 1 || $topic_number > 5) {
            wp_send_json_error(['message' => 'Topic number must be between 1 and 5']);
            return;
        }
        
        // Delegate to unified service if available
        if ($this->unified_data_service) {
            $topics_service = $this->unified_data_service->get_topics_service();
            $result = $topics_service->save_single_topic($topic_number, $topic_text, $post_id, $entry_id);
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
        } else {
            wp_send_json_error(['message' => 'Unified data service not available']);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save authority hook components AJAX request - ENHANCED
     */
    public function handle_save_authority_hook_ajax() {
        // Enhanced error handling and debugging
        try {
            error_log('MKCG Topics Generator: Starting enhanced handle_save_authority_hook_ajax');
            
            // ROBUST: Enhanced security validation with multiple fallbacks
            $security_check = $this->validate_ajax_security_enhanced(['entry_id']);
            if (is_wp_error($security_check)) {
                error_log('MKCG Topics Generator: Security validation failed: ' . $security_check->get_error_message());
                wp_send_json_error([
                    'message' => $security_check->get_error_message(),
                    'error_type' => 'security_validation_failed'
                ]);
                return;
            }
            
            $entry_id = intval($_POST['entry_id']);
            
            if (!$entry_id) {
                error_log('MKCG Topics Generator: Invalid entry_id: ' . print_r($_POST['entry_id'], true));
                wp_send_json_error([
                    'message' => 'Entry ID is required',
                    'error_type' => 'invalid_entry_id'
                ]);
                return;
            }
            
            // Get components from POST data with enhanced validation
            $components = $this->extract_authority_components_from_post();
            
            error_log('MKCG Topics Generator: Processing components for entry ' . $entry_id . ': ' . json_encode($components));
            
            // ENHANCED: Use safe authority hook building with comprehensive error handling
            $save_result = $this->save_authority_hook_components_safe(
                $entry_id, 
                $components['who'], 
                $components['result'], 
                $components['when'], 
                $components['how']
            );
            
            if ($save_result['success']) {
                error_log('MKCG Topics Generator: ✅ Authority hook saved successfully');
                wp_send_json_success([
                    'message' => 'Authority hook components saved successfully',
                    'authority_hook' => $save_result['authority_hook'],
                    'saved_fields' => $save_result['saved_fields'],
                    'components' => $components,
                    'success_type' => 'authority_hook_saved'
                ]);
            } else {
                error_log('MKCG Topics Generator: ❌ Save failed: ' . print_r($save_result, true));
                wp_send_json_error([
                    'message' => 'Failed to save authority hook components',
                    'details' => $save_result['errors'] ?? ['Unknown error'],
                    'debug_info' => $save_result['debug_info'] ?? [],
                    'error_type' => 'save_failed'
                ]);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Critical exception in handle_save_authority_hook_ajax: ' . $e->getMessage());
            error_log('MKCG Topics Generator: Exception stack trace: ' . $e->getTraceAsString());
            
            wp_send_json_error([
                'message' => 'Critical server error during authority hook save',
                'details' => $e->getMessage(),
                'error_type' => 'critical_exception',
                'exception_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }
    
    /**
     * CRITICAL FIX: Enhanced security validation with better error handling
     */
    private function validate_ajax_security_enhanced($required_fields = []) {
        try {
            // Use the base class validation first
            $base_validation = $this->validate_ajax_security($required_fields);
            
            if (is_wp_error($base_validation)) {
                return $base_validation;
            }
            
            // Additional validation for Topics Generator specific requirements
            if (!current_user_can('edit_posts')) {
                return new WP_Error('insufficient_permissions', 'User does not have permission to edit content');
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception in validate_ajax_security_enhanced: ' . $e->getMessage());
            return new WP_Error('security_validation_exception', 'Security validation failed due to server error');
        }
    }
    
    /**
     * CRITICAL FIX: Extract and validate authority components from POST data
     */
    private function extract_authority_components_from_post() {
        $components = [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => ''
        ];
        
        try {
            foreach ($components as $key => $default) {
                if (isset($_POST[$key])) {
                    $value = sanitize_textarea_field($_POST[$key]);
                    $components[$key] = !empty($value) ? $value : $default;
                } else {
                    // Use defaults for missing components
                    $defaults = [
                        'who' => 'your audience',
                        'result' => 'achieve their goals',
                        'when' => 'they need help',
                        'how' => 'through your method'
                    ];
                    $components[$key] = $defaults[$key];
                }
            }
            
            error_log('MKCG Topics Generator: Extracted components: ' . json_encode($components));
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception extracting components: ' . $e->getMessage());
            // Return defaults on error
            $components = [
                'who' => 'your audience',
                'result' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method'
            ];
        }
        
        return $components;
    }
    
    /**
     * CRITICAL FIX: Handle save individual field AJAX request
     */
    public function handle_save_field_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_id = isset($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : '';
        $value = isset($_POST['value']) ? sanitize_textarea_field($_POST['value']) : '';
        
        if (!$entry_id || !$field_id) {
            wp_send_json_error(['message' => 'Entry ID and field ID are required']);
            return;
        }
        
        // Save using Formidable service
        if ($this->formidable_service) {
            $result = $this->formidable_service->save_generated_content(
                $entry_id,
                [$field_id => $value],
                [$field_id => $field_id]
            );
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => 'Field saved successfully',
                    'entry_id' => $entry_id,
                    'field_id' => $field_id,
                    'value' => $value
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to save field',
                    'details' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } else {
            wp_send_json_error(['message' => 'Formidable service not available']);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save topic field AJAX request
     */
    public function handle_save_topic_field_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
        $field_value = isset($_POST['field_value']) ? sanitize_textarea_field($_POST['field_value']) : '';
        
        if (!$entry_id || !$field_name) {
            wp_send_json_error(['message' => 'Entry ID and field name are required']);
            return;
        }
        
        // Extract field ID from field name (e.g., 'field_8498' -> '8498')
        $field_id = '';
        if (preg_match('/field_(\d+)/', $field_name, $matches)) {
            $field_id = $matches[1];
        }
        
        if (!$field_id) {
            wp_send_json_error(['message' => 'Invalid field name format']);
            return;
        }
        
        // Save using Formidable service
        if ($this->formidable_service) {
            $result = $this->formidable_service->save_generated_content(
                $entry_id,
                [$field_id => $field_value],
                [$field_id => $field_id]
            );
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => 'Topic field saved successfully',
                    'entry_id' => $entry_id,
                    'field_name' => $field_name,
                    'field_id' => $field_id,
                    'value' => $field_value
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to save topic field',
                    'details' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } else {
            wp_send_json_error(['message' => 'Formidable service not available']);
        }
    }
}