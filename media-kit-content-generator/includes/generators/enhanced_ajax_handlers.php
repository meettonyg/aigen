<?php
/**
 * Simplified AJAX Handlers
 * Single responsibility: Handle AJAX requests cleanly
 * Eliminates: Multiple nonce strategies, excessive error handling, backward compatibility
 */

class Enhanced_AJAX_Handlers {
    
    private $formidable_service;
    private $topics_generator;
    
    /**
     * Simple constructor
     */
    public function __construct($formidable_service, $topics_generator) {
        $this->formidable_service = $formidable_service;
        $this->topics_generator = $topics_generator;
        $this->init();
    }
    
    /**
     * Initialize AJAX handlers - direct registration, no complexity
     */
    public function init() {
        $actions = [
            'mkcg_save_topics_data' => 'handle_save_topics',
            'mkcg_get_topics_data' => 'handle_get_topics',
            'mkcg_save_authority_hook' => 'handle_save_authority_hook',
            'mkcg_generate_topics' => 'handle_generate_topics'
        ];
        
        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [$this, $method]);
            add_action('wp_ajax_nopriv_' . $action, [$this, $method]);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save topics request with comprehensive dual-save support
     */
    public function handle_save_topics() {
        error_log('MKCG AJAX: Starting save_topics_data handler');
        
        if (!$this->verify_request()) {
            error_log('MKCG AJAX: Security verification failed');
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            error_log('MKCG AJAX: No entry ID provided');
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        error_log('MKCG AJAX: Processing save for entry ID: ' . $entry_id);
        
        // Extract both topics and authority hook data
        $topics_data = $this->extract_topics_data();
        $authority_hook_data = $this->extract_authority_hook_data();
        
        error_log('MKCG AJAX: Extracted topics count: ' . count($topics_data));
        error_log('MKCG AJAX: Extracted authority components count: ' . count($authority_hook_data));
        
        if (empty($topics_data) && empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No data provided to save']);
            return;
        }
        
        $results = [
            'formidable' => ['success' => false, 'message' => 'No data to save'],
            'post_meta' => ['success' => false, 'message' => 'No data to save']
        ];
        
        // Save to Formidable if we have data
        if (!empty($topics_data) || !empty($authority_hook_data)) {
            $formidable_data = [];
            
            // Add topics to formidable data
            if (!empty($topics_data)) {
                $topics_field_mappings = $this->get_topics_field_mappings();
                foreach ($topics_data as $topic_key => $topic_value) {
                    if (isset($topics_field_mappings[$topic_key])) {
                        $formidable_data[$topics_field_mappings[$topic_key]] = $topic_value;
                        error_log("MKCG AJAX: Mapping {$topic_key} to field {$topics_field_mappings[$topic_key]}");
                    }
                }
            }
            
            // Add authority hook to formidable data
            if (!empty($authority_hook_data)) {
                $auth_field_mappings = $this->get_authority_hook_field_mappings();
                foreach ($authority_hook_data as $component => $value) {
                    if (isset($auth_field_mappings[$component])) {
                        $formidable_data[$auth_field_mappings[$component]] = $value;
                        error_log("MKCG AJAX: Mapping {$component} to field {$auth_field_mappings[$component]}");
                    }
                }
            }
            
            if (!empty($formidable_data)) {
                $formidable_result = $this->formidable_service->save_entry_data($entry_id, $formidable_data);
                $results['formidable'] = $formidable_result;
                error_log('MKCG AJAX: Formidable save result: ' . json_encode($formidable_result));
            }
        }
        
        // Also save to WordPress post meta for backup/compatibility
        $post_id = $this->get_post_id_from_entry($entry_id);
        if ($post_id) {
            $post_meta_data = [];
            
            // Add topics to post meta
            foreach ($topics_data as $topic_key => $topic_value) {
                $post_meta_data["mkcg_{$topic_key}"] = $topic_value;
            }
            
            // Add authority hook to post meta
            foreach ($authority_hook_data as $component => $value) {
                $post_meta_data["mkcg_authority_{$component}"] = $value;
            }
            
            if (!empty($post_meta_data)) {
                $post_meta_result = $this->formidable_service->save_post_meta($post_id, $post_meta_data);
                $results['post_meta'] = $post_meta_result;
                error_log('MKCG AJAX: Post meta save result: ' . json_encode($post_meta_result));
            }
        }
        
        // Determine overall success
        $overall_success = $results['formidable']['success'] || $results['post_meta']['success'];
        
        if ($overall_success) {
            $response_data = [
                'message' => 'Data saved successfully',
                'entry_id' => $entry_id,
                'formidable' => $results['formidable'],
                'post_meta' => $results['post_meta']
            ];
            
            // Add complete authority hook if available
            if (!empty($authority_hook_data['complete'])) {
                $response_data['authority_hook_complete'] = $authority_hook_data['complete'];
            }
            
            error_log('MKCG AJAX: Overall save successful');
            wp_send_json_success($response_data);
        } else {
            error_log('MKCG AJAX: Overall save failed');
            wp_send_json_error([
                'message' => 'Failed to save data',
                'formidable_error' => $results['formidable']['message'],
                'post_meta_error' => $results['post_meta']['message']
            ]);
        }
    }
    
    /**
     * Handle get topics request
     */
    public function handle_get_topics() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        $field_mappings = $this->get_topics_field_mappings();
        $topics = [];
        
        foreach ($field_mappings as $topic_key => $field_id) {
            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
            $topics[$topic_key] = $value ?: '';
        }
        
        wp_send_json_success([
            'entry_id' => $entry_id,
            'topics' => $topics,
            'has_data' => !empty(array_filter($topics))
        ]);
    }
    
    /**
     * Handle save authority hook request
     */
    public function handle_save_authority_hook() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        $authority_hook_data = $this->extract_authority_hook_data();
        if (empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No authority hook data provided']);
            return;
        }
        
        // Get field mappings from config
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        // Prepare data for Formidable
        $formidable_data = [];
        foreach ($authority_hook_data as $component => $value) {
            if (isset($field_mappings[$component])) {
                $formidable_data[$field_mappings[$component]] = $value;
            }
        }
        
        $result = $this->formidable_service->save_entry_data($entry_id, $formidable_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook saved successfully',
                'saved_count' => $result['saved_count'],
                'entry_id' => $entry_id
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * Handle generate topics request
     */
    public function handle_generate_topics() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $authority_hook = sanitize_textarea_field($_POST['authority_hook'] ?? '');
        if (empty($authority_hook)) {
            wp_send_json_error(['message' => 'Authority hook is required']);
            return;
        }
        
        // Simple demo topic generation (replace with actual API call)
        $topics = $this->generate_demo_topics($authority_hook);
        
        wp_send_json_success([
            'topics' => $topics,
            'count' => count($topics),
            'authority_hook' => $authority_hook
        ]);
    }
    
    /**
     * Simple request verification - single method, no fallbacks
     */
    private function verify_request() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            return false;
        }
        
        // Simple nonce check
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (empty($nonce)) {
            return false;
        }
        
        return wp_verify_nonce($nonce, 'mkcg_nonce');
    }
    
    /**
     * Get entry ID from request
     */
    private function get_entry_id() {
        return isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
    }
    
    /**
     * CRITICAL FIX: Extract topics data from request with multiple format support
     */
    private function extract_topics_data() {
        $topics = [];
        
        // Handle topics object/array from JavaScript
        if (isset($_POST['topics'])) {
            $topics_raw = $_POST['topics'];
            
            // If it's a JSON string, decode it
            if (is_string($topics_raw)) {
                $topics_decoded = json_decode($topics_raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $topics_raw = $topics_decoded;
                }
            }
            
            if (is_array($topics_raw)) {
                foreach ($topics_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        // Normalize key format
                        if (is_numeric($key)) {
                            $topic_key = 'topic_' . intval($key);
                        } elseif (strpos($key, 'topic_') === 0) {
                            $topic_key = $key;
                        } else {
                            continue; // Skip invalid keys
                        }
                        
                        $topics[$topic_key] = sanitize_textarea_field($value);
                        error_log("MKCG AJAX: Extracted topic {$topic_key}: {$value}");
                    }
                }
            }
        }
        
        // Handle individual topic fields as fallback
        for ($i = 1; $i <= 5; $i++) {
            $field_name = 'topic_' . $i;
            if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                $topics[$field_name] = sanitize_textarea_field($_POST[$field_name]);
                error_log("MKCG AJAX: Extracted individual topic {$field_name}: {$_POST[$field_name]}");
            }
        }
        
        return $topics;
    }
    
    /**
     * CRITICAL FIX: Extract authority hook data from request with flexible format support
     */
    private function extract_authority_hook_data() {
        $components = [];
        
        // Handle authority_hook object/array from JavaScript
        if (isset($_POST['authority_hook'])) {
            $auth_raw = $_POST['authority_hook'];
            
            // If it's a JSON string, decode it
            if (is_string($auth_raw)) {
                $auth_decoded = json_decode($auth_raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $auth_raw = $auth_decoded;
                }
            }
            
            if (is_array($auth_raw)) {
                $fields = ['who', 'result', 'when', 'how', 'complete'];
                foreach ($fields as $field) {
                    if (isset($auth_raw[$field]) && !empty(trim($auth_raw[$field]))) {
                        $components[$field] = sanitize_textarea_field($auth_raw[$field]);
                        error_log("MKCG AJAX: Extracted authority {$field}: {$auth_raw[$field]}");
                    }
                }
            }
        }
        
        // Handle individual component fields as fallback
        $fields = ['who', 'result', 'when', 'how'];
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                $components[$field] = sanitize_textarea_field($_POST[$field]);
                error_log("MKCG AJAX: Extracted individual component {$field}: {$_POST[$field]}");
            }
        }
        
        // Build complete authority hook if components provided
        if (!empty($components) && count(array_filter($components)) >= 2) {
            $who = $components['who'] ?? 'your audience';
            $result = $components['result'] ?? 'achieve their goals';
            $when = $components['when'] ?? 'they need help';
            $how = $components['how'] ?? 'through your method';
            
            $complete_hook = "I help {$who} {$result} when {$when} {$how}.";
            $components['complete'] = $complete_hook;
            error_log("MKCG AJAX: Built complete authority hook: {$complete_hook}");
        }
        
        return $components;
    }
    
    /**
     * Generate demo topics - simple placeholder
     */
    private function generate_demo_topics($authority_hook) {
        // Simple topic templates based on authority hook keywords
        if (stripos($authority_hook, 'business') !== false || stripos($authority_hook, 'revenue') !== false) {
            return [
                'Proven Strategies for Business Growth in Uncertain Times',
                'How to Turn Challenges into Revenue Opportunities',
                'Building a Resilient Business That Thrives During Crises',
                'The Power of Community in Business Success',
                'Streamlining Operations for Maximum Efficiency'
            ];
        }
        
        return [
            'The Authority Positioning Framework for Your Niche',
            'Creating Content That Converts and Builds Audience',
            'Systems for Success: Automating Your Business',
            'The Podcast Guest Formula for High-Value Clients',
            'Building a Sustainable Business Model'
        ];
    }
    
    /**
     * Get topics field mappings from config
     */
    private function get_topics_field_mappings() {
        // Should come from centralized config, but simplified here
        return [
            'topic_1' => '8498',
            'topic_2' => '8499',
            'topic_3' => '8500',
            'topic_4' => '8501',
            'topic_5' => '8502'
        ];
    }
    
    /**
     * Get authority hook field mappings from config
     */
    private function get_authority_hook_field_mappings() {
        // Should come from centralized config, but simplified here
        return [
            'who' => '10296',
            'result' => '10297',
            'when' => '10387',
            'how' => '10298',
            'complete' => '10358'
        ];
    }
    
    /**
     * Get post ID from entry ID
     */
    private function get_post_id_from_entry($entry_id) {
        if (!$entry_id) {
            return null;
        }
        
        global $wpdb;
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        return $post_id ? intval($post_id) : null;
    }
}
