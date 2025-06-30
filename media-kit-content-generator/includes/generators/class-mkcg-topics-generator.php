<?php
/**
 * MKCG Topics Generator
 * Generates interview topics based on Authority Hook and audience
 */

class MKCG_Topics_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'topics';
    protected $unified_data_service;
    protected $topics_data_service; // CRITICAL FIX: Same service as Questions Generator
    
    // Enhanced configuration - same as Questions Generator
    protected $max_topics = 5;
    protected $max_retries = 3;
    protected $cache_duration = 3600; // 1 hour
    
    /**
     * Constructor - Initialize with Topics Data Service like Questions Generator
     */
    public function __construct($api_service, $formidable_service, $authority_hook_service = null) {
        parent::__construct($api_service, $formidable_service, $authority_hook_service);
        
        // CRITICAL FIX: Use same Topics Data Service as Questions Generator
        $this->topics_data_service = null;
        
        try {
            // Verify critical class availability
            if (!class_exists('MKCG_Topics_Data_Service')) {
                throw new Exception('MKCG_Topics_Data_Service class not found');
            }
            
            // Initialize Topics Data Service (same as Questions Generator)
            $this->topics_data_service = new MKCG_Topics_Data_Service($formidable_service);
            
            if (!$this->topics_data_service) {
                throw new Exception('Topics Data Service failed to initialize');
            }
            
            error_log('MKCG Topics Generator: ✅ Topics Data Service initialized successfully');
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ EXCEPTION during Topics Data Service initialization: ' . $e->getMessage());
            $this->topics_data_service = null;
        }
        
        // Initialize unified data service for compatibility
        $this->unified_data_service = new MKCG_Unified_Data_Service($formidable_service);
    }
    
    /**
     * Check if Topics Data Service is available (same as Questions Generator)
     */
    private function is_topics_service_available() {
        return ($this->topics_data_service !== null);
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
    
    // REMOVED: Redundant field processing methods - now using centralized Formidable Service
    
    /**
     * CRITICAL FIX: Get template data using Topics Data Service (same as Questions Generator)
     * Loads data from WordPress custom post meta, NOT Formidable entry fields
     */
    public function get_template_data($entry_key = '') {
        // Initialize empty data structure
        $template_data = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'authority_hook_components' => [
                'who' => '',
                'result' => '',
                'when' => '',
                'how' => '',
                'complete' => ''
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
        
        // CRITICAL FIX: Use Topics Data Service (same as Questions Generator)
        if (!empty($entry_key) && $this->topics_data_service) {
            error_log('MKCG Topics Generator: Using Topics Data Service to load data for entry_key: ' . $entry_key);
            
            try {
                // Get data using Topics Data Service (loads from custom post meta)
                $topics_result = $this->topics_data_service->get_topics_data(null, $entry_key, null);
                
                if ($topics_result['success']) {
                    $template_data['entry_id'] = $topics_result['entry_id'];
                    $template_data['has_entry'] = true;
                    
                    // Load topics data from custom post meta
                    $template_data['form_field_values'] = $topics_result['topics'];
                    
                    // Load authority hook data
                    $template_data['authority_hook_components'] = $topics_result['authority_hook'];
                    
                    error_log('MKCG Topics Generator: ✅ Successfully loaded data via Topics Data Service');
                    error_log('MKCG Topics Generator: Entry ID: ' . $template_data['entry_id']);
                    error_log('MKCG Topics Generator: Topics loaded: ' . json_encode($template_data['form_field_values']));
                    error_log('MKCG Topics Generator: Authority Hook: ' . $template_data['authority_hook_components']['complete']);
                    
                } else {
                    error_log('MKCG Topics Generator: ❌ Topics Data Service failed: ' . ($topics_result['message'] ?? 'Unknown error'));
                }
                
            } catch (Exception $e) {
                error_log('MKCG Topics Generator: ❌ Exception using Topics Data Service: ' . $e->getMessage());
            }
        } elseif (!empty($entry_key) && !$this->topics_data_service) {
            error_log('MKCG Topics Generator: ❌ CRITICAL ERROR - Topics Data Service not available for entry_key: ' . $entry_key);
        } elseif (empty($entry_key)) {
            error_log('MKCG Topics Generator: No entry_key provided - using defaults');
        }
        
        // Apply defaults ONLY for new entries (no entry_id)
        if ($template_data['entry_id'] === 0) {
            $template_data['authority_hook_components']['who'] = 'your audience';
            $template_data['authority_hook_components']['result'] = 'achieve their goals';
            $template_data['authority_hook_components']['when'] = 'they need help';
            $template_data['authority_hook_components']['how'] = 'through your method';
            $template_data['authority_hook_components']['complete'] = 'I help your audience achieve their goals when they need help through your method.';
            error_log('MKCG Topics Generator: New entry - applied defaults');
        } elseif (empty($template_data['authority_hook_components']['complete'])) {
            // Minimal fallback for existing entries
            $template_data['authority_hook_components']['complete'] = 'I help my audience achieve their goals.';
            error_log('MKCG Topics Generator: Existing entry - applied minimal fallback');
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
}