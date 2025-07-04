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
        
        // REMOVED: init() is now called separately to prevent timing issues
        // AJAX actions are registered directly in the main plugin file
    }
    
    /**
     * Initialize AJAX handlers - direct registration, no complexity
     * FIXED: Only register for logged-in users (no nopriv) for save actions
     */
    public function init() {
        $actions = [
            'mkcg_save_topics_data' => 'handle_save_topics',
            'mkcg_get_topics_data' => 'handle_get_topics',
            'mkcg_save_authority_hook' => 'handle_save_authority_hook',
            'mkcg_generate_topics' => 'handle_generate_topics',
            'mkcg_save_topic_field' => 'handle_save_topic_field'  // Add missing auto-save handler
        ];
        
        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [$this, $method]);
            // REMOVED nopriv registration - save actions require logged-in users
            // add_action('wp_ajax_nopriv_' . $action, [$this, $method]);
        }
        
        error_log('MKCG AJAX: Handlers registered for logged-in users only');
    }
    
    /**
     * Handle save topics request - Pure Pods integration
     */
    public function handle_save_topics() {
        error_log('MKCG AJAX: Starting save_topics_data handler - Pure Pods');
        error_log('MKCG AJAX: Raw _POST: ' . print_r($_POST, true));
        
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
        
        // ENHANCED: Extract both topics and authority hook data with better debugging
        $topics_data = $this->extract_topics_data();
        $authority_hook_data = $this->extract_authority_hook_data();
        
        error_log('MKCG AJAX: Extracted topics count: ' . count($topics_data));
        error_log('MKCG AJAX: Extracted authority components count: ' . count($authority_hook_data));
        error_log('MKCG AJAX: Topics data: ' . print_r($topics_data, true));
        error_log('MKCG AJAX: Authority hook data: ' . print_r($authority_hook_data, true));
        
        // IMPROVED: Check if ANY data was provided (be more lenient)
        if (empty($topics_data) && empty($authority_hook_data)) {
            error_log('MKCG AJAX: No data extracted from request');
            wp_send_json_error([
                'message' => 'No data provided to save',
                'debug' => [
                    'topics_empty' => empty($topics_data),
                    'authority_empty' => empty($authority_hook_data),
                    'post_keys' => array_keys($_POST)
                ]
            ]);
            return;
        }
        
        $results = [];
        
        // Save topics using Pods service
        if (!empty($topics_data)) {
            $topics_result = $this->pods_service->save_topics($post_id, $topics_data);
            $results['topics'] = $topics_result;
            error_log('MKCG AJAX: Topics save result: ' . json_encode($topics_result));
        }
        
        // Save authority hook using centralized Authority Hook Service (post meta)
        if (!empty($authority_hook_data)) {
            global $authority_hook_service;
            if (!$authority_hook_service) {
                require_once dirname(__FILE__) . '/../services/class-mkcg-authority-hook-service.php';
                $authority_hook_service = new MKCG_Authority_Hook_Service();
            }
            
            // Convert 'result' to 'what' for service compatibility
            if (isset($authority_hook_data['result'])) {
                $authority_hook_data['what'] = $authority_hook_data['result'];
                unset($authority_hook_data['result']);
            }
            
            $auth_result = $authority_hook_service->save_authority_hook_data($post_id, $authority_hook_data);
            $results['authority_hook'] = $auth_result;
            error_log('MKCG AJAX: Authority hook save result (post meta): ' . json_encode($auth_result));
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
     * Handle save authority hook request - Use centralized Authority Hook Service
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
        
        // Use centralized Authority Hook Service for saving to post meta
        global $authority_hook_service;
        if (!$authority_hook_service) {
            require_once dirname(__FILE__) . '/../services/class-mkcg-authority-hook-service.php';
            $authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        // Convert 'result' to 'what' for service compatibility
        if (isset($authority_hook_data['result'])) {
            $authority_hook_data['what'] = $authority_hook_data['result'];
            unset($authority_hook_data['result']);
        }
        
        $result = $authority_hook_service->save_authority_hook_data($post_id, $authority_hook_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook saved to post meta successfully',
                'post_id' => $post_id,
                'components' => $result['components']
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
     * Handle save single topic field request (auto-save)
     */
    public function handle_save_topic_field() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        $field_name = sanitize_text_field($_POST['field_name'] ?? '');
        $field_value = sanitize_textarea_field($_POST['field_value'] ?? '');
        
        if (empty($field_name) || empty($field_value)) {
            wp_send_json_error(['message' => 'Field name and value required']);
            return;
        }
        
        // Save using Pods service
        $result = $this->pods_service->save_single_field($post_id, $field_name, $field_value);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Field saved successfully',
                'field_name' => $field_name,
                'post_id' => $post_id
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * Simple request verification - enhanced debugging
     */
    private function verify_request() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            error_log('MKCG AJAX: User not logged in');
            return false;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            error_log('MKCG AJAX: User lacks edit_posts capability');
            return false;
        }
        
        // Enhanced nonce debugging
        error_log('MKCG AJAX: POST data keys: ' . implode(', ', array_keys($_POST)));
        
        // Simple nonce check - try multiple nonce fields
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? $_GET['nonce'] ?? '';
        if (empty($nonce)) {
            error_log('MKCG AJAX: No nonce provided in request');
            error_log('MKCG AJAX: Checked fields: nonce, security, GET[nonce]');
            return false;
        }
        
        error_log('MKCG AJAX: Found nonce value: ' . $nonce);
        
        // Try multiple nonce names
        $nonce_names = ['mkcg_nonce', 'mkcg-nonce', 'topics_nonce', 'wp_rest'];
        $verified = false;
        
        foreach ($nonce_names as $nonce_name) {
            if (wp_verify_nonce($nonce, $nonce_name)) {
                $verified = true;
                error_log('MKCG AJAX: Nonce verified with name: ' . $nonce_name);
                break;
            }
        }
        
        if (!$verified) {
            error_log('MKCG AJAX: Nonce verification failed for all names: ' . implode(', ', $nonce_names));
            error_log('MKCG AJAX: Provided nonce: ' . $nonce);
            
            // Last resort - check if it's a valid WordPress nonce at all
            $nonce_data = wp_verify_nonce($nonce, -1);
            if ($nonce_data !== false) {
                error_log('MKCG AJAX: Nonce is valid but for different action');
            }
            
            return false;
        }
        
        error_log('MKCG AJAX: Request verification successful');
        return true;
    }
    
    /**
     * Get post ID from request
     */
    private function get_post_id() {
        return isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    }
    
    /**
     * BULLETPROOF: Extract topics data - simple and reliable
     */
    private function extract_topics_data() {
        $topics = [];
        
        error_log('MKCG AJAX: === TOPICS EXTRACTION START ===');
        
        // Method 1: Check if topics came as array notation first (from updated AJAX)
        $array_topics = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'topics[') === 0) {
                // Extract the topic key from topics[topic_1] format
                preg_match('/topics\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $array_topics[$matches[1]] = sanitize_textarea_field($value);
                    error_log("MKCG AJAX: Found array notation topic {$matches[1]}: {$value}");
                }
            }
        }
        
        if (!empty($array_topics)) {
            $topics = $array_topics;
            error_log('MKCG AJAX: Using array notation topics, count: ' . count($topics));
        }
        
        // Method 2: JSON-encoded topics object (fallback)
        if (empty($topics) && isset($_POST['topics']) && !empty($_POST['topics'])) {
            // FIX: Check if it's already an array before calling stripslashes
            if (is_array($_POST['topics'])) {
                $topics_raw = $_POST['topics'];
            } else {
                $topics_raw = stripslashes($_POST['topics']); // Remove slashes that might be added
            }
            error_log('MKCG AJAX: Found topics in POST, type: ' . gettype($topics_raw));
            error_log('MKCG AJAX: Topics raw value: ' . print_r($topics_raw, true));
            
            if (is_string($topics_raw)) {
                // Try to decode JSON
                $decoded = json_decode($topics_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    error_log('MKCG AJAX: Successfully decoded JSON topics');
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $topics[$key] = sanitize_textarea_field($value);
                            error_log("MKCG AJAX: Added JSON topic {$key}: {$value}");
                        }
                    }
                } else {
                    error_log('MKCG AJAX: JSON decode failed: ' . json_last_error_msg());
                    error_log('MKCG AJAX: Raw JSON string: ' . $topics_raw);
                }
            } elseif (is_array($topics_raw)) {
                error_log('MKCG AJAX: Topics is already array');
                foreach ($topics_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $topics[$key] = sanitize_textarea_field($value);
                        error_log("MKCG AJAX: Added array topic {$key}: {$value}");
                    }
                }
            }
        }
        
        // Method 3: Individual topic fields (final fallback)
        if (empty($topics)) {
            error_log('MKCG AJAX: No topics from JSON, trying individual fields');
            for ($i = 1; $i <= 5; $i++) {
                $field_name = 'topic_' . $i;
                if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                    $topics[$field_name] = sanitize_textarea_field($_POST[$field_name]);
                    error_log("MKCG AJAX: Added individual topic {$field_name}: {$_POST[$field_name]}");
                }
            }
        }
        
        error_log('MKCG AJAX: Final topics extracted: ' . count($topics) . ' items');
        error_log('MKCG AJAX: Topics: ' . print_r($topics, true));
        error_log('MKCG AJAX: === TOPICS EXTRACTION END ===');
        
        return $topics;
    }
    
    /**
     * BULLETPROOF: Extract authority hook data - simple and reliable
     */
    private function extract_authority_hook_data() {
        $components = [];
        
        error_log('MKCG AJAX: === AUTHORITY HOOK EXTRACTION START ===');
        
        // Method 1: Check if authority_hook came as array notation first (from updated AJAX)
        $array_components = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'authority_hook[') === 0) {
                // Extract the component key from authority_hook[who] format
                preg_match('/authority_hook\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $field = $matches[1];
                    // Map 'result' to 'what' if needed
                    if ($field === 'result') {
                        $field = 'what';
                    }
                    $array_components[$field] = sanitize_textarea_field($value);
                    error_log("MKCG AJAX: Found array notation authority component {$field}: {$value}");
                }
            }
        }
        
        if (!empty($array_components)) {
            $components = $array_components;
            error_log('MKCG AJAX: Using array notation authority hook, count: ' . count($components));
        }
        
        // Method 2: JSON-encoded authority_hook object (fallback)
        if (empty($components) && isset($_POST['authority_hook']) && !empty($_POST['authority_hook'])) {
            // FIX: Check if it's already an array before calling stripslashes
            if (is_array($_POST['authority_hook'])) {
                $auth_raw = $_POST['authority_hook'];
            } else {
                $auth_raw = stripslashes($_POST['authority_hook']); // Remove slashes that might be added
            }
            error_log('MKCG AJAX: Found authority_hook in POST, type: ' . gettype($auth_raw));
            error_log('MKCG AJAX: Authority hook raw value: ' . print_r($auth_raw, true));
            
            if (is_string($auth_raw)) {
                // Try to decode JSON
                $decoded = json_decode($auth_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    error_log('MKCG AJAX: Successfully decoded JSON authority hook');
                    
                    // Map the fields properly
                    $field_map = [
                        'who' => 'who',
                        'what' => 'what',
                        'result' => 'what', // Map result to what
                        'when' => 'when',
                        'how' => 'how'
                    ];
                    
                    foreach ($decoded as $key => $value) {
                        if (isset($field_map[$key]) && !empty(trim($value))) {
                            $mapped_key = $field_map[$key];
                            $components[$mapped_key] = sanitize_textarea_field($value);
                            error_log("MKCG AJAX: Added JSON authority component {$mapped_key}: {$value}");
                        }
                    }
                } else {
                    error_log('MKCG AJAX: Authority hook JSON decode failed: ' . json_last_error_msg());
                    error_log('MKCG AJAX: Raw JSON string: ' . $auth_raw);
                }
            } elseif (is_array($auth_raw)) {
                error_log('MKCG AJAX: Authority hook is already array');
                foreach ($auth_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $components[$key] = sanitize_textarea_field($value);
                        error_log("MKCG AJAX: Added array authority component {$key}: {$value}");
                    }
                }
            }
        }
        
        // Method 3: Individual component fields (final fallback)
        if (empty($components)) {
            error_log('MKCG AJAX: No authority hook from JSON, trying individual fields');
            $fields = ['who', 'what', 'result', 'when', 'how'];
            foreach ($fields as $field) {
                if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                    $mapped_field = ($field === 'result') ? 'what' : $field;
                    $components[$mapped_field] = sanitize_textarea_field($_POST[$field]);
                    error_log("MKCG AJAX: Added individual component {$mapped_field}: {$_POST[$field]}");
                }
            }
        }
        
        // Build complete authority hook if we have components
        if (!empty($components) && count(array_filter($components)) >= 2) {
            $who = $components['who'] ?? 'your audience';
            $what = $components['what'] ?? 'achieve their goals';
            $when = $components['when'] ?? 'they need help';
            $how = $components['how'] ?? 'through your method';
            
            $complete_hook = "I help {$who} {$what} when {$when} {$how}.";
            $components['complete'] = $complete_hook;
            error_log("MKCG AJAX: Built complete authority hook: {$complete_hook}");
        }
        
        error_log('MKCG AJAX: Final authority hook extracted: ' . count($components) . ' components');
        error_log('MKCG AJAX: Authority hook: ' . print_r($components, true));
        error_log('MKCG AJAX: === AUTHORITY HOOK EXTRACTION END ===');
        
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
