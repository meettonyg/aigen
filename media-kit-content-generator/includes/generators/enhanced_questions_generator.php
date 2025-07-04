<?php
/**
 * Enhanced Questions Generator
 * Handles questions generation functionality for the Media Kit Content Generator
 */

class Enhanced_Questions_Generator {
    
    private $api_service;
    private $pods_service;
    
    public function __construct($api_service) {
        $this->api_service = $api_service;
        
        // Initialize Pods service
        require_once dirname(__FILE__) . '/../services/class-mkcg-pods-service.php';
        $this->pods_service = new MKCG_Pods_Service();
        
        $this->init();
    }
    
    public function init() {
        // ROOT FIX: AJAX actions are now registered in main plugin file to prevent conflicts
        // This ensures single source of truth for AJAX handler registration
        error_log('MKCG Questions Generator: Initialized (AJAX handlers managed by main plugin)');
    }
    
    /**
     * ROOT FIX: Get template data using dynamic post_id - CONSISTENT with Topics Generator
     */
    public function get_template_data($post_id = null) {
        error_log('MKCG Questions Generator: Starting get_template_data - ROOT FIX for post_id consistency');
        
        // Get post_id from parameter or request (same logic as Topics Generator)
        if (!$post_id) {
            $post_id = $this->get_post_id_from_request();
        }
        
        if (!$post_id) {
            error_log('MKCG Questions Generator: No valid post ID found');
            return $this->get_default_template_data();
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            error_log('MKCG Questions Generator: Post ' . $post_id . ' is not a guests post type');
            return $this->get_default_template_data();
        }
        
        error_log('MKCG Questions Generator: Loading data for guests post ID: ' . $post_id);
        
        // Load ALL data from Pods service
        $guest_data = $this->pods_service->get_guest_data($post_id);
        
        // ROOT FIX: Transform to CONSISTENT template format matching Topics Generator
        $template_data = [
            'post_id' => $post_id,
            'has_data' => $guest_data['has_data'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            // CRITICAL FIX: Use 'form_field_values' key like Topics Generator for consistency
            'form_field_values' => $guest_data['topics'], // Topics data with consistent naming
            'questions' => $guest_data['questions'], // Questions data (specific to Questions Generator)
            'contact' => $guest_data['contact'],
            'messaging' => $guest_data['messaging']
        ];
        
        error_log('MKCG Questions Generator: Data loaded successfully with consistent structure');
        return $template_data;
    }
    
    /**
     * Get post_id from request parameters - Dynamic post ID detection
     */
    private function get_post_id_from_request() {
        error_log('MKCG Questions Generator: Starting dynamic post ID detection');
        
        // Strategy 1: Direct post_id parameter (primary method)
        if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            $post_id = intval($_GET['post_id']);
            error_log('MKCG Questions Generator: Found post_id parameter: ' . $post_id);
            
            // Validate it's a guests post
            if (get_post($post_id) && get_post($post_id)->post_type === 'guests') {
                return $post_id;
            } else {
                error_log('MKCG Questions Generator: Post ID ' . $post_id . ' is not a valid guests post');
            }
        }
        
        // Strategy 2: Check for global post context
        global $post;
        if ($post && $post->ID && $post->post_type === 'guests') {
            error_log('MKCG Questions Generator: Using global post context: ' . $post->ID);
            return $post->ID;
        }
        
        // Strategy 3: Check if we're on a guest post page
        if (is_single() || is_page()) {
            $current_id = get_the_ID();
            if ($current_id && get_post_type($current_id) === 'guests') {
                error_log('MKCG Questions Generator: Found guest post page: ' . $current_id);
                return $current_id;
            }
        }
        
        // Strategy 4: Look for the most recent guest post for testing
        $recent_guest = get_posts([
            'post_type' => 'guests',
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (!empty($recent_guest)) {
            $post_id = $recent_guest[0]->ID;
            error_log('MKCG Questions Generator: Using most recent guest post for testing: ' . $post_id);
            return $post_id;
        }
        
        error_log('MKCG Questions Generator: No valid post ID found with any strategy');
        return 0;
    }
    
    /**
     * Get default template data structure - Pure Pods
     * Updated to check for entry parameters and not provide defaults when no entry param
     */
    private function get_default_template_data() {
        // Check for entry parameter - don't show defaults if no entry param provided
        $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                           (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        
        if (!$has_entry_param) {
            // NO ENTRY PARAM: Return empty structure with no defaults
            error_log('MKCG Questions Generator: No entry parameter found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                // CONSISTENT: Use same structure as Topics Generator
                'form_field_values' => [
                    'topic_1' => '',
                    'topic_2' => '',
                    'topic_3' => '',
                    'topic_4' => '',
                    'topic_5' => ''
                ],
                'questions' => [],
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'contact' => [],
                'messaging' => [],
                'no_entry_param' => true
            ];
        } else {
            // HAS ENTRY PARAM: Return empty structure (entry param exists but no data found)
            error_log('MKCG Questions Generator: Entry parameter exists but no data found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                // CONSISTENT: Use same structure as Topics Generator
                'form_field_values' => [
                    'topic_1' => '',
                    'topic_2' => '',
                    'topic_3' => '',
                    'topic_4' => '',
                    'topic_5' => ''
                ],
                'questions' => [],
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'contact' => [],
                'messaging' => []
            ];
        }
    }
    
    /**
     * Handle generate questions AJAX request
     */
    public function handle_generate_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $topic = sanitize_textarea_field($_POST['topic'] ?? '');
        if (empty($topic)) {
            wp_send_json_error(['message' => 'Topic is required']);
            return;
        }
        
        // Generate questions using API service or demo
        $questions = $this->generate_questions_for_topic($topic);
        
        wp_send_json_success([
            'questions' => $questions,
            'topic' => $topic,
            'count' => count($questions)
        ]);
    }
    
    /**
     * Handle save questions AJAX request using POST ID
     */
    public function handle_save_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        $questions_data = $this->extract_questions_data();
        if (empty($questions_data)) {
            wp_send_json_error(['message' => 'No questions data provided']);
            return;
        }
        
        $result = $this->save_questions($post_id, $questions_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Questions saved successfully',
                'saved_count' => $result['saved_count'],
                'post_id' => $post_id
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * Handle get questions AJAX request using POST ID
     */
    public function handle_get_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = $this->get_post_id();
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get questions using Pods service
        $guest_data = $this->pods_service->get_guest_data($post_id);
        
        wp_send_json_success([
            'post_id' => $post_id,
            'questions' => $guest_data['questions'],
            'topics' => $guest_data['topics'],
            'has_data' => !empty($guest_data['questions'])
        ]);
    }
    
    /**
     * Generate questions for a topic
     */
    public function generate_questions_for_topic($topic) {
        // Demo questions - replace with API call when available
        return [
            "What inspired you to develop your approach to {$topic}?",
            "Can you walk us through your step-by-step process for {$topic}?", 
            "What's the biggest mistake you see people making when it comes to {$topic}?",
            "What results have your clients seen after implementing your {$topic} strategies?",
            "What advice would you give to someone just starting with {$topic}?"
        ];
    }
    
    /**
     * Save questions using Pods service
     */
    public function save_questions($post_id, $questions_data) {
        if (!$post_id || empty($questions_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        // Use Pods service for saving
        return $this->pods_service->save_questions($post_id, $questions_data);
    }
    
    /**
     * Extract questions data from request
     */
    private function extract_questions_data() {
        $questions = [];
        
        // Handle questions array
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $topic => $topic_questions) {
                if (is_array($topic_questions)) {
                    foreach ($topic_questions as $q => $question) {
                        if (!empty(trim($question))) {
                            $key = "mkcg_question_{$topic}_{$q}";
                            $questions[$key] = sanitize_textarea_field($question);
                        }
                    }
                }
            }
        }
        
        return $questions;
    }
    
    /**
     * Verify AJAX request
     */
    private function verify_request() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        if (!current_user_can('edit_posts')) {
            return false;
        }
        
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
     * ADDED: Get post ID from request
     */
    private function get_post_id() {
        return isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    }
    
    /**
     * Resolve entry ID from entry key
     */
    private function resolve_entry_id($entry_key) {
        if (is_numeric($entry_key)) {
            return intval($entry_key);
        }
        
        global $wpdb;
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE item_key = %s",
            $entry_key
        ));
        
        return $entry_id ? intval($entry_id) : 0;
    }
}
