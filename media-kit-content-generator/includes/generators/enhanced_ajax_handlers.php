<?php
/**
 * Simplified AJAX Handlers
 * Single responsibility: Handle AJAX requests cleanly
 * Eliminates: Multiple nonce strategies, excessive error handling, backward compatibility
 */

class Enhanced_AJAX_Handlers {
    
    private $pods_service;
    private $topics_generator;
    
    /**
     * Simple constructor - Pure Pods integration
     */
    public function __construct($pods_service, $topics_generator) {
        $this->pods_service = $pods_service;
        $this->topics_generator = $topics_generator;
        
        // Initialize Pods service if not provided
        if (!$this->pods_service) {
            require_once dirname(__FILE__) . '/../services/class-mkcg-pods-service.php';
            $this->pods_service = new MKCG_Pods_Service();
        }
        
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
     * Handle save topics request - Pure Pods integration
     */
    public function handle_save_topics() {
        error_log('MKCG AJAX: Starting save_topics_data handler - Pure Pods');
        
        if (!$this->verify_request()) {
            error_log('MKCG AJAX: Security verification failed');
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            error_log('MKCG AJAX: No post ID provided');
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        error_log('MKCG AJAX: Processing save for post ID: ' . $post_id);
        
        // Extract both topics and authority hook data
        $topics_data = $this->extract_topics_data();
        $authority_hook_data = $this->extract_authority_hook_data();
        
        error_log('MKCG AJAX: Extracted topics count: ' . count($topics_data));
        error_log('MKCG AJAX: Extracted authority components count: ' . count($authority_hook_data));
        
        if (empty($topics_data) && empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No data provided to save']);
            return;
        }
        
        $results = [];
        
        // Save topics using Pods service
        if (!empty($topics_data)) {
            $topics_result = $this->pods_service->save_topics($post_id, $topics_data);
            $results['topics'] = $topics_result;
            error_log('MKCG AJAX: Topics save result: ' . json_encode($topics_result));
        }
        
        // Save authority hook using Pods service
        if (!empty($authority_hook_data)) {
            $auth_result = $this->pods_service->save_authority_hook_components($post_id, $authority_hook_data);
            $results['authority_hook'] = $auth_result;
            error_log('MKCG AJAX: Authority hook save result: ' . json_encode($auth_result));
        }
        
        // Determine overall success
        $overall_success = (!empty($results['topics']) && $results['topics']['success']) || 
                          (!empty($results['authority_hook']) && $results['authority_hook']['success']);
        
        if ($overall_success) {
            $response_data = [
                'message' => 'Data saved successfully',
                'post_id' => $post_id,
                'results' => $results
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
                'results' => $results
            ]);
        }
    }
    
    /**
     * Handle get topics request - Pure Pods integration
     */
    public function handle_get_topics() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get topics using Pods service
        $guest_data = $this->pods_service->get_guest_data($post_id);
        
        wp_send_json_success([
            'post_id' => $post_id,
            'topics' => $guest_data['topics'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            'has_data' => $guest_data['has_data']
        ]);
    }
    
    /**
     * Handle save authority hook request - Pure Pods integration
     */
    public function handle_save_authority_hook() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        $authority_hook_data = $this->extract_authority_hook_data();
        if (empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No authority hook data provided']);
            return;
        }
        
        // Save using Pods service
        $result = $this->pods_service->save_authority_hook_components($post_id, $authority_hook_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook saved successfully',
                'saved_count' => $result['saved_count'],
                'post_id' => $post_id
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
     * Get post ID from request
     */
    private function get_post_id() {
        return isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
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
    

}
