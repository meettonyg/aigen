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
     * Handle save topics request
     */
    public function handle_save_topics() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        $topics_data = $this->extract_topics_data();
        if (empty($topics_data)) {
            wp_send_json_error(['message' => 'No topics data provided']);
            return;
        }
        
        // Get field mappings from config
        $field_mappings = $this->get_topics_field_mappings();
        
        // Prepare data for Formidable
        $formidable_data = [];
        foreach ($topics_data as $topic_key => $topic_value) {
            if (isset($field_mappings[$topic_key])) {
                $formidable_data[$field_mappings[$topic_key]] = $topic_value;
            }
        }
        
        $result = $this->formidable_service->save_entry_data($entry_id, $formidable_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Topics saved successfully',
                'saved_count' => $result['saved_count'],
                'entry_id' => $entry_id
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
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
     * Extract topics data from request
     */
    private function extract_topics_data() {
        $topics = [];
        
        // Handle topics array
        if (isset($_POST['topics']) && is_array($_POST['topics'])) {
            foreach ($_POST['topics'] as $key => $value) {
                if (!empty(trim($value))) {
                    $topic_key = 'topic_' . intval($key);
                    $topics[$topic_key] = sanitize_textarea_field($value);
                }
            }
        }
        
        // Handle individual topic fields
        for ($i = 1; $i <= 5; $i++) {
            $field_name = 'topic_' . $i;
            if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                $topics[$field_name] = sanitize_textarea_field($_POST[$field_name]);
            }
        }
        
        return $topics;
    }
    
    /**
     * Extract authority hook data from request
     */
    private function extract_authority_hook_data() {
        $components = [];
        $fields = ['who', 'result', 'when', 'how'];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                $components[$field] = sanitize_textarea_field($_POST[$field]);
            }
        }
        
        // Build complete authority hook if components provided
        if (!empty($components) && count($components) >= 2) {
            $who = $components['who'] ?? 'your audience';
            $result = $components['result'] ?? 'achieve their goals';
            $when = $components['when'] ?? 'they need help';
            $how = $components['how'] ?? 'through your method';
            
            $components['complete'] = "I help {$who} {$result} when {$when} {$how}.";
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
}
