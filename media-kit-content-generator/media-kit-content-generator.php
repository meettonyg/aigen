<?php
/**
 * Plugin Name: Media Kit Content Generator
 * Plugin URI: https://guestify.com
 * Description: Unified content generator for biography, offers, topics, and interview questions
 * Version: 1.0.0
 * Author: Guestify
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define MKCG_PLUGIN_FILE if not already defined
if (!defined('MKCG_PLUGIN_FILE')) {
    define('MKCG_PLUGIN_FILE', __FILE__);
}

// Define plugin constants
define('MKCG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKCG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MKCG_VERSION', '1.0.0');

// Main plugin class
class Media_Kit_Content_Generator {
    
    private static $instance = null;
    private $api_service;
    private $pods_service;
    private $authority_hook_service;
    private $impact_intro_service;
    private $ajax_handlers = null;

    private $generators = [];
    
    /**
     * Helper method for conditional debug logging
     */
    private function debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($message);
        }
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
        $this->init_services();
        $this->init_generators();
        
        // CRITICAL FIX: Ensure global services are available immediately
        $this->ensure_global_services();
        
        // AJAX handlers are now initialized on-demand when needed
    }
    
    // REMOVED: Old init_ajax_handlers() method - replaced with on-demand initialization
    
    /**
     * Non-logged-in AJAX handler (returns error - security measure)
     */
    public function ajax_save_topics_data_nopriv() {
        wp_send_json_error(['message' => 'Authentication required']);
    }
    
    /**
     * CRITICAL FIX: Simplified AJAX handler for save topics - Direct implementation
     */
    public function ajax_save_topics_data() {
        // CRITICAL: Set proper headers first
        header('Content-Type: application/json');
        
        // Enhanced logging for debugging
        error_log('MKCG CRITICAL FIX: Starting simplified ajax_save_topics_data handler');
        error_log('MKCG: Request method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('MKCG: POST data: ' . print_r($_POST, true));
        
        // Basic security check - simplified but effective
        if (!is_user_logged_in()) {
            error_log('MKCG: User not logged in');
            wp_send_json_error(['message' => 'Authentication required']);
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            error_log('MKCG: User lacks edit_posts capability');
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        // Simplified nonce check
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log('MKCG: Nonce verification failed. Provided: ' . $nonce);
            wp_send_json_error(['message' => 'Security verification failed']);
            return;
        }
        
        error_log('MKCG: Security checks passed');
        
        // Get post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            error_log('MKCG: No valid post ID provided');
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        error_log('MKCG: Processing save for post ID: ' . $post_id);
        
        // DIRECT data extraction - no complex methods
        $topics_data = [];
        $authority_hook_data = [];
        
        // Extract topics - multiple strategies
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'topics[') === 0) {
                preg_match('/topics\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $topics_data[$matches[1]] = sanitize_textarea_field($value);
                    error_log('MKCG: Found topic via array notation: ' . $matches[1] . ' = ' . $value);
                }
            }
        }
        
        // Fallback: JSON-encoded topics
        if (empty($topics_data) && isset($_POST['topics'])) {
            $topics_raw = $_POST['topics'];
            if (is_string($topics_raw)) {
                $decoded = json_decode(stripslashes($topics_raw), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $topics_data[$key] = sanitize_textarea_field($value);
                        }
                    }
                    error_log('MKCG: Found topics via JSON decode');
                }
            } elseif (is_array($topics_raw)) {
                foreach ($topics_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $topics_data[$key] = sanitize_textarea_field($value);
                    }
                }
                error_log('MKCG: Found topics via direct array');
            }
        }
        
        // Extract authority hook - multiple strategies
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'authority_hook[') === 0) {
                preg_match('/authority_hook\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $field = $matches[1];
                    if ($field === 'result') $field = 'what';
                    $authority_hook_data[$field] = sanitize_textarea_field($value);
                    error_log('MKCG: Found authority hook via array notation: ' . $field . ' = ' . $value);
                }
            }
        }
        
        // Fallback: JSON-encoded authority hook
        if (empty($authority_hook_data) && isset($_POST['authority_hook'])) {
            $auth_raw = $_POST['authority_hook'];
            if (is_string($auth_raw)) {
                $decoded = json_decode(stripslashes($auth_raw), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $mapped_key = ($key === 'result') ? 'what' : $key;
                            $authority_hook_data[$mapped_key] = sanitize_textarea_field($value);
                        }
                    }
                    error_log('MKCG: Found authority hook via JSON decode');
                }
            } elseif (is_array($auth_raw)) {
                foreach ($auth_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $authority_hook_data[$key] = sanitize_textarea_field($value);
                    }
                }
                error_log('MKCG: Found authority hook via direct array');
            }
        }
        
        error_log('MKCG: Extracted data - Topics: ' . count($topics_data) . ', Authority Hook: ' . count($authority_hook_data));
        
        // Check if we have any data to save
        if (empty($topics_data) && empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No data provided to save']);
            return;
        }
        
        $results = [];
        $overall_success = false;
        
        // Save using direct post meta (most reliable)
        if (!empty($topics_data)) {
            foreach ($topics_data as $key => $value) {
                $meta_key = 'mkcg_' . $key;
                $result = update_post_meta($post_id, $meta_key, $value);
                if ($result !== false) {
                    $overall_success = true;
                    error_log('MKCG: Saved topic ' . $key . ' to post meta');
                }
            }
            $results['topics'] = ['success' => true, 'count' => count($topics_data)];
        }
        
        if (!empty($authority_hook_data)) {
            foreach ($authority_hook_data as $key => $value) {
                $meta_key = 'mkcg_authority_hook_' . $key;
                $result = update_post_meta($post_id, $meta_key, $value);
                if ($result !== false) {
                    $overall_success = true;
                    error_log('MKCG: Saved authority hook ' . $key . ' to post meta');
                }
            }
            $results['authority_hook'] = ['success' => true, 'count' => count($authority_hook_data)];
        }
        
        if ($overall_success) {
            wp_send_json_success([
                'message' => 'Data saved successfully to post meta',
                'post_id' => $post_id,
                'results' => $results
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save data',
                'results' => $results
            ]);
        }
    }
    
    /**
     * DEPRECATED: Moved to direct implementation in ajax_save_topics_data
     */
    private function extract_topics_data() {
        // This method is no longer used - extraction is done directly in the AJAX handler
        return [];
    }
    
    /**
     * DEPRECATED: Moved to direct implementation in ajax_save_topics_data
     */
    private function extract_authority_hook_data() {
        // This method is no longer used - extraction is done directly in the AJAX handler
        return [];
    }
    
    /**
     * ROOT FIX: Save audience taxonomy from WHO field content
     */
    private function save_audience_taxonomy($post_id, $who_content) {
        if (!$post_id || empty($who_content) || $who_content === 'your audience') {
            return ['success' => false, 'message' => 'No valid audience data to save'];
        }
        
        error_log('MKCG: Parsing audience string: "' . $who_content . '"');
        
        // Parse audience string to extract individual audiences
        $audiences = $this->parse_audience_string($who_content);
        
        if (empty($audiences)) {
            return ['success' => false, 'message' => 'No audiences found in WHO field'];
        }
        
        error_log('MKCG: Extracted audiences: ' . json_encode($audiences));
        
        // Get or create taxonomy terms
        $term_ids = [];
        foreach ($audiences as $audience_name) {
            $audience_name = trim($audience_name);
            if (empty($audience_name)) continue;
            
            // Check if term exists
            $existing_term = get_term_by('name', $audience_name, 'audience');
            
            if ($existing_term) {
                $term_ids[] = $existing_term->term_id;
                error_log('MKCG: Found existing audience term: ' . $audience_name . ' (ID: ' . $existing_term->term_id . ')');
            } else {
                // Create new term
                $new_term = wp_insert_term($audience_name, 'audience');
                if (!is_wp_error($new_term)) {
                    $term_ids[] = $new_term['term_id'];
                    error_log('MKCG: Created new audience term: ' . $audience_name . ' (ID: ' . $new_term['term_id'] . ')');
                } else {
                    error_log('MKCG: Failed to create audience term: ' . $audience_name . ' - ' . $new_term->get_error_message());
                }
            }
        }
        
        if (empty($term_ids)) {
            return ['success' => false, 'message' => 'No valid audience terms to assign'];
        }
        
        // Assign terms to post
        $result = wp_set_post_terms($post_id, $term_ids, 'audience', false); // false = replace existing terms
        
        if (is_wp_error($result)) {
            error_log('MKCG: Failed to assign audience terms: ' . $result->get_error_message());
            return ['success' => false, 'message' => 'Failed to assign audience terms: ' . $result->get_error_message()];
        }
        
        // Clear taxonomy cache
        wp_cache_delete($post_id, 'audience_relationships');
        clean_object_term_cache($post_id, 'audience');
        
        error_log('MKCG: Successfully assigned ' . count($term_ids) . ' audience terms to post ' . $post_id);
        
        return [
            'success' => true,
            'message' => 'Audience taxonomy saved successfully',
            'audiences_saved' => $audiences,
            'term_ids' => $term_ids
        ];
    }
    
    /**
     * ROOT FIX: Parse audience string to extract individual audience names
     * IMPROVED: Handles natural language patterns properly
     */
    private function parse_audience_string($who_content) {
        // Handle various formats:
        // "Authors launching a book"
        // "2nd value and Authors launching a book" 
        // "2nd value, Authors launching a book, and 3 value"
        
        $audiences = [];
        
        error_log('MKCG: Starting to parse audience string: "' . $who_content . '"');
        
        // IMPROVED LOGIC: Handle natural language patterns more robustly
        // Remove any leading/trailing whitespace
        $who_content = trim($who_content);
        
        // Skip if empty or default
        if (empty($who_content) || $who_content === 'your audience') {
            error_log('MKCG: Skipping empty or default audience string');
            return [];
        }
        
        // Strategy 1: Handle the Oxford comma pattern properly
        // Pattern: "A, B, and C" should become ["A", "B", "C"]
        if (strpos($who_content, ', and ') !== false) {
            error_log('MKCG: Detected Oxford comma pattern');
            
            // Split on ', and ' first to get the last item
            $parts = explode(', and ', $who_content);
            $last_item = array_pop($parts); // "3 value"
            
            // The remaining part should be split by comma
            if (!empty($parts)) {
                $remaining = $parts[0]; // "2nd value, Authors launching a book"
                $middle_items = explode(', ', $remaining);
                
                // Combine all items
                foreach ($middle_items as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $audiences[] = $item;
                    }
                }
            }
            
            // Add the last item
            $last_item = trim($last_item);
            if (!empty($last_item)) {
                $audiences[] = $last_item;
            }
        }
        // Strategy 2: Handle simple "A and B" pattern
        elseif (strpos($who_content, ' and ') !== false && strpos($who_content, ',') === false) {
            error_log('MKCG: Detected simple "A and B" pattern');
            $parts = explode(' and ', $who_content);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $audiences[] = $part;
                }
            }
        }
        // Strategy 3: Handle comma-separated without "and"
        elseif (strpos($who_content, ',') !== false) {
            error_log('MKCG: Detected comma-separated pattern');
            $parts = explode(',', $who_content);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $audiences[] = $part;
                }
            }
        }
        // Strategy 4: Single audience (no separators)
        else {
            error_log('MKCG: Detected single audience');
            $audiences[] = $who_content;
        }
        
        // Clean up and validate each audience
        $clean_audiences = [];
        foreach ($audiences as $audience) {
            $audience = trim($audience);
            
            // CORRECTED: Only filter obviously invalid terms, not potentially valid business terms
            $is_valid_audience = !empty($audience) && 
                                $audience !== 'your audience' &&
                                strlen($audience) > 1; // Minimum length check only
            
            // REMOVED: Overly aggressive filtering that blocks valid terms
            // Only filter truly invalid patterns, not test data that might be legitimate
            
            if ($is_valid_audience) {
                $clean_audiences[] = $audience;
            } else {
                error_log('MKCG: Filtered out invalid audience term: "' . $audience . '"');
            }
        }
        
        error_log('MKCG: Successfully parsed "' . $who_content . '" into ' . count($clean_audiences) . ' audiences: ' . json_encode($clean_audiences));
        
        return $clean_audiences;
    }
    
    public function ajax_get_topics() {
        // Direct handler - no need for ensure_ajax_handlers
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get data directly using post meta
        $topics = [];
        for ($i = 1; $i <= 5; $i++) {
            $meta_key = 'mkcg_topic_' . $i;
            $value = get_post_meta($post_id, $meta_key, true);
            if ($value) {
                $topics['topic_' . $i] = $value;
            }
        }
        
        $authority_hook = [];
        $auth_fields = ['who', 'what', 'when', 'how'];
        foreach ($auth_fields as $field) {
            $meta_key = 'mkcg_authority_hook_' . $field;
            $value = get_post_meta($post_id, $meta_key, true);
            if ($value) {
                $authority_hook[$field] = $value;
            }
        }
        
        wp_send_json_success([
            'post_id' => $post_id,
            'topics' => $topics,
            'authority_hook_components' => $authority_hook,
            'has_data' => !empty($topics) || !empty($authority_hook)
        ]);
    }
    
    public function ajax_save_authority_hook() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Extract authority hook data
        $auth_fields = ['who', 'what', 'when', 'how'];
        $saved_count = 0;
        
        foreach ($auth_fields as $field) {
            $value = sanitize_textarea_field($_POST[$field] ?? '');
            if (!empty($value)) {
                $meta_key = 'mkcg_authority_hook_' . $field;
                update_post_meta($post_id, $meta_key, $value);
                $saved_count++;
            }
        }
        
        if ($saved_count > 0) {
            wp_send_json_success([
                'message' => 'Authority hook saved successfully',
                'post_id' => $post_id,
                'components_saved' => $saved_count
            ]);
        } else {
            wp_send_json_error(['message' => 'No authority hook data to save']);
        }
    }
    
    public function ajax_generate_topics() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $authority_hook = sanitize_textarea_field($_POST['authority_hook'] ?? '');
        if (empty($authority_hook)) {
            wp_send_json_error(['message' => 'Authority hook is required']);
            return;
        }
        
        // Simple demo topic generation
        $topics = [
            'The Authority Positioning Framework: How to Become the Go-To Expert in Your Niche',
            'Creating Content That Converts: A Strategic Approach to Audience Building',
            'Systems for Success: Automating Your Business to Create More Freedom',
            'The Podcast Guest Formula: How to Turn Interviews into High-Value Clients',
            'Building a Sustainable Business Model That Serves Your Lifestyle Goals'
        ];
        
        wp_send_json_success([
            'topics' => $topics,
            'count' => count($topics),
            'authority_hook' => $authority_hook
        ]);
    }
    
    public function ajax_save_topic_field() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
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
        
        // Save using post meta
        $meta_key = 'mkcg_' . $field_name;
        $result = update_post_meta($post_id, $meta_key, $field_value);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Field saved successfully',
                'field_name' => $field_name,
                'post_id' => $post_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save field']);
        }
    }
    
    /**
     * ROOT FIX: Questions Generator AJAX handlers - SIMPLIFIED following Topics Generator pattern
     */
    public function ajax_save_questions() {
        // Direct handler implementation - no delegation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Extract questions data using same pattern as Topics Generator
        $questions_data = [];
        
        // Method 1: Array notation questions[question_1_1] format
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'questions[') === 0) {
                preg_match('/questions\\[(.*?)\\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $questions_data[$matches[1]] = sanitize_textarea_field($value);
                    error_log('MKCG Questions: Found array notation question ' . $matches[1] . ' = ' . $value);
                }
            }
        }
        
        // Method 2: JSON-encoded questions
        if (empty($questions_data) && isset($_POST['questions'])) {
            $questions_raw = $_POST['questions'];
            if (is_string($questions_raw)) {
                $decoded = json_decode(stripslashes($questions_raw), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $questions_data[$key] = sanitize_textarea_field($value);
                        }
                    }
                    error_log('MKCG Questions: Found questions via JSON decode');
                }
            } elseif (is_array($questions_raw)) {
                foreach ($questions_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $questions_data[$key] = sanitize_textarea_field($value);
                    }
                }
                error_log('MKCG Questions: Found questions via direct array');
            }
        }
        
        // Method 3: Individual question fields
        if (empty($questions_data)) {
            for ($topic = 1; $topic <= 5; $topic++) {
                for ($q = 1; $q <= 5; $q++) {
                    $field_name = "question_{$topic}_{$q}";
                    if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                        $questions_data[$field_name] = sanitize_textarea_field($_POST[$field_name]);
                    }
                }
            }
        }
        
        error_log('MKCG Questions: Extracted ' . count($questions_data) . ' questions');
        
        if (empty($questions_data)) {
            wp_send_json_error(['message' => 'No questions data provided']);
            return;
        }
        
        $results = [];
        $overall_success = false;
        
        // Save using direct post meta (most reliable)
        foreach ($questions_data as $key => $value) {
            $meta_key = 'mkcg_' . $key;
            $result = update_post_meta($post_id, $meta_key, $value);
            if ($result !== false) {
                $overall_success = true;
                error_log('MKCG Questions: Saved question ' . $key . ' to post meta');
            }
        }
        
        if ($overall_success) {
            wp_send_json_success([
                'message' => 'Questions saved successfully to post meta',
                'post_id' => $post_id,
                'saved_count' => count($questions_data)
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save questions',
                'post_id' => $post_id
            ]);
        }
    }
    
    public function ajax_generate_questions() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $topic = sanitize_textarea_field($_POST['topic'] ?? '');
        if (empty($topic)) {
            wp_send_json_error(['message' => 'Topic is required']);
            return;
        }
        
        // Simple demo question generation
        $questions = [
            "What inspired you to develop your approach to {$topic}?",
            "Can you walk us through your step-by-step process for {$topic}?", 
            "What's the biggest mistake you see people making when it comes to {$topic}?",
            "What results have your clients seen after implementing your {$topic} strategies?",
            "What advice would you give to someone just starting with {$topic}?"
        ];
        
        wp_send_json_success([
            'questions' => $questions,
            'topic' => $topic,
            'count' => count($questions)
        ]);
    }
    
    public function ajax_save_single_question() {
        // Handle single question save (auto-save functionality)
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        $meta_key = sanitize_text_field($_POST['meta_key'] ?? '');
        $question = sanitize_textarea_field($_POST['question'] ?? '');
        
        if (empty($meta_key) || empty($question)) {
            wp_send_json_error(['message' => 'Meta key and question required']);
            return;
        }
        
        // Save using WordPress post meta
        $result = update_post_meta($post_id, $meta_key, $question);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Question saved successfully',
                'meta_key' => $meta_key,
                'post_id' => $post_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save question']);
        }
    }
    
    public function ajax_get_questions() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get questions data directly using post meta
        $questions = [];
        for ($topic = 1; $topic <= 5; $topic++) {
            for ($q = 1; $q <= 5; $q++) {
                $meta_key = 'mkcg_question_' . $topic . '_' . $q;
                $value = get_post_meta($post_id, $meta_key, true);
                if ($value) {
                    $questions['question_' . $topic . '_' . $q] = $value;
                }
            }
        }
        
        wp_send_json_success([
            'post_id' => $post_id,
            'questions' => $questions,
            'has_data' => !empty($questions)
        ]);
    }
    
    /**
     * ROOT FIX: Offers Generator AJAX handlers - SIMPLIFIED following Topics Generator pattern
     */
    public function ajax_generate_offers() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $authority_hook = sanitize_textarea_field($_POST['authority_hook'] ?? '');
        if (empty($authority_hook)) {
            wp_send_json_error(['message' => 'Authority hook is required']);
            return;
        }
        
        $business_type = sanitize_text_field($_POST['business_type'] ?? 'consulting');
        $target_audience = sanitize_text_field($_POST['target_audience'] ?? '');
        $price_range = sanitize_text_field($_POST['price_range'] ?? 'mid');
        
        // Simple demo offer generation
        $offers = [
            "Free Lead Magnet: '{$authority_hook}' Quick Start Guide - Learn the fundamentals in 30 minutes",
            "Low-Ticket Offer: '{$authority_hook}' Masterclass - Complete training program for {$target_audience}",
            "Core Offer: 1-on-1 {$business_type} Package - Personalized implementation of {$authority_hook}",
            "Premium Offer: {$authority_hook} VIP Experience - Done-with-you intensive program",
            "Group Program: {$authority_hook} Community - Monthly coaching and peer support"
        ];
        
        wp_send_json_success([
            'offers' => $offers,
            'authority_hook' => $authority_hook,
            'count' => count($offers)
        ]);
    }
    
    public function ajax_save_offers() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Extract offers data
        $offers_data = [];
        
        // Method 1: Array notation offers[offer_1] format
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'offers[') === 0) {
                preg_match('/offers\\[(.*?)\\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $offers_data[$matches[1]] = sanitize_textarea_field($value);
                }
            }
        }
        
        // Method 2: JSON-encoded offers
        if (empty($offers_data) && isset($_POST['offers'])) {
            $offers_raw = $_POST['offers'];
            if (is_string($offers_raw)) {
                $decoded = json_decode(stripslashes($offers_raw), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $offers_data[$key] = sanitize_textarea_field($value);
                        }
                    }
                }
            } elseif (is_array($offers_raw)) {
                foreach ($offers_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $offers_data[$key] = sanitize_textarea_field($value);
                    }
                }
            }
        }
        
        // Method 3: Individual offer fields
        if (empty($offers_data)) {
            for ($i = 1; $i <= 10; $i++) {
                $field_name = "offer_{$i}";
                if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                    $offers_data[$field_name] = sanitize_textarea_field($_POST[$field_name]);
                }
            }
        }
        
        // Extract business data
        $business_data = [
            'business_type' => sanitize_text_field($_POST['business_type'] ?? ''),
            'target_audience' => sanitize_text_field($_POST['target_audience'] ?? ''),
            'price_range' => sanitize_text_field($_POST['price_range'] ?? 'mid'),
            'delivery_method' => sanitize_text_field($_POST['delivery_method'] ?? 'online')
        ];
        
        if (empty($offers_data) && empty($business_data['business_type'])) {
            wp_send_json_error(['message' => 'No offers or business data provided']);
            return;
        }
        
        $results = [];
        $overall_success = false;
        
        // Save using direct post meta
        if (!empty($offers_data)) {
            foreach ($offers_data as $key => $value) {
                $meta_key = 'mkcg_' . $key;
                $result = update_post_meta($post_id, $meta_key, $value);
                if ($result !== false) {
                    $overall_success = true;
                }
            }
            $results['offers'] = ['success' => true, 'count' => count($offers_data)];
        }
        
        if (!empty($business_data['business_type'])) {
            foreach ($business_data as $key => $value) {
                $meta_key = 'mkcg_offers_' . $key;
                $result = update_post_meta($post_id, $meta_key, $value);
                if ($result !== false) {
                    $overall_success = true;
                }
            }
            $results['business_data'] = ['success' => true, 'count' => count($business_data)];
        }
        
        if ($overall_success) {
            wp_send_json_success([
                'message' => 'Offers data saved successfully to post meta',
                'post_id' => $post_id,
                'results' => $results
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save offers data',
                'post_id' => $post_id
            ]);
        }
    }
    
    /**
     * ROOT FIX: Impact Intro Generator AJAX handlers - SIMPLIFIED following Topics Generator pattern
     */
    public function ajax_save_impact_intro() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Extract Impact Intro data (WHERE/WHY)
        $impact_fields = ['where', 'why'];
        $saved_count = 0;
        
        foreach ($impact_fields as $field) {
            $value = sanitize_textarea_field($_POST[$field] ?? '');
            if (!empty($value)) {
                $meta_key = 'mkcg_impact_intro_' . $field;
                update_post_meta($post_id, $meta_key, $value);
                $saved_count++;
            }
        }
        
        if ($saved_count > 0) {
            wp_send_json_success([
                'message' => 'Impact Intro saved successfully',
                'post_id' => $post_id,
                'components_saved' => $saved_count
            ]);
        } else {
            wp_send_json_error(['message' => 'No Impact Intro data to save']);
        }
    }
    
    public function ajax_get_impact_intro() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get data directly using post meta
        $impact_intro = [];
        $impact_fields = ['where', 'why'];
        foreach ($impact_fields as $field) {
            $meta_key = 'mkcg_impact_intro_' . $field;
            $value = get_post_meta($post_id, $meta_key, true);
            if ($value) {
                $impact_intro[$field] = $value;
            }
        }
        
        wp_send_json_success([
            'post_id' => $post_id,
            'impact_intro_components' => $impact_intro,
            'has_data' => !empty($impact_intro)
        ]);
    }
    
    /**
     * Helper method to get a generator instance, initializing if needed
     * NOTE: This is now mainly for backward compatibility - direct AJAX handlers are preferred
     */
    private function get_generator_instance($type) {
        if (!isset($this->generators[$type])) {
            $class_map = [
                'biography' => 'MKCG_Enhanced_Biography_Generator',
                'topics' => 'Enhanced_Topics_Generator',
                'questions' => 'Enhanced_Questions_Generator',
                'offers' => 'Enhanced_Offers_Generator',
                'authority_hook' => 'Enhanced_Authority_Hook_Generator',
                'impact_intro' => 'Enhanced_Impact_Intro_Generator',
                'guest_intro' => 'MKCG_Enhanced_Guest_Intro_Generator'
            ];
            
            $file_path = MKCG_PLUGIN_PATH . 'includes/generators/enhanced_' . $type . '_generator.php';
            
            if (isset($class_map[$type]) && file_exists($file_path)) {
                require_once $file_path;
                $this->generators[$type] = new $class_map[$type]();
                $this->debug_log('MKCG: Initialized ' . $type . ' generator on demand');
            }
        }
        return $this->generators[$type] ?? null;
    }
    
    /**
     * ROOT FIX: Biography Generator AJAX handlers - SIMPLIFIED following Topics Generator pattern
     */
    public function ajax_generate_biography() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get form data
        $name = sanitize_text_field($_POST['name'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        $organization = sanitize_text_field($_POST['organization'] ?? '');
        $authority_hook = sanitize_textarea_field($_POST['authority_hook'] ?? '');
        $impact_intro = sanitize_textarea_field($_POST['impact_intro'] ?? '');
        $tone = sanitize_text_field($_POST['tone'] ?? 'professional');
        $pov = sanitize_text_field($_POST['pov'] ?? 'third');
        
        if (empty($name)) {
            wp_send_json_error(['message' => 'Name is required to generate a biography']);
            return;
        }
        
        // Generate simple demo biographies
        $personal_info = $name;
        if (!empty($title)) {
            $personal_info .= ", a {$title}";
        }
        if (!empty($organization)) {
            $personal_info .= " at {$organization}";
        }
        
        $pronoun = ($pov === 'first') ? 'I' : 'They';
        $possessive = ($pov === 'first') ? 'my' : 'their';
        
        $biographies = [
            'short' => "{$personal_info} specializes in helping clients achieve exceptional results through proven methodologies and expert guidance.",
            'medium' => "{$personal_info} is a recognized expert who has dedicated {$possessive} career to delivering transformational outcomes for clients. With a focus on practical solutions and measurable results, {$pronoun} help organizations and individuals achieve their most important goals through strategic guidance and proven methodologies.",
            'long' => "{$personal_info} brings extensive experience and a track record of success to every client engagement. {$pronoun} specialize in developing customized strategies that deliver measurable results and lasting impact. Through {$possessive} comprehensive approach and commitment to excellence, {$pronoun} have helped numerous clients transform their businesses and achieve breakthrough performance. {$possessive} methodology combines proven frameworks with innovative thinking to create solutions that are both practical and powerful."
        ];
        
        // Save biographies to post meta
        update_post_meta($post_id, '_biography_short', $biographies['short']);
        update_post_meta($post_id, '_biography_medium', $biographies['medium']);
        update_post_meta($post_id, '_biography_long', $biographies['long']);
        update_post_meta($post_id, '_biography_tone', $tone);
        update_post_meta($post_id, '_biography_pov', $pov);
        update_post_meta($post_id, '_biography_generation_date', current_time('mysql'));
        
        wp_send_json_success([
            'biographies' => $biographies,
            'settings' => [
                'tone' => $tone,
                'pov' => $pov
            ],
            'personal_info' => [
                'name' => $name,
                'title' => $title,
                'organization' => $organization
            ],
            'generation_date' => current_time('mysql'),
            'message' => 'Biography generated successfully'
        ]);
    }
    
    public function ajax_modify_biography_tone() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        $new_tone = sanitize_text_field($_POST['tone'] ?? 'professional');
        
        // Get existing biographies
        $short_bio = get_post_meta($post_id, '_biography_short', true);
        $medium_bio = get_post_meta($post_id, '_biography_medium', true);
        $long_bio = get_post_meta($post_id, '_biography_long', true);
        
        if (empty($short_bio) && empty($medium_bio) && empty($long_bio)) {
            wp_send_json_error(['message' => 'No existing biographies found to modify']);
            return;
        }
        
        // Update tone in post meta
        update_post_meta($post_id, '_biography_tone', $new_tone);
        
        wp_send_json_success([
            'biographies' => [
                'short' => $short_bio,
                'medium' => $medium_bio,
                'long' => $long_bio
            ],
            'settings' => [
                'tone' => $new_tone,
                'pov' => get_post_meta($post_id, '_biography_pov', true) ?: 'third'
            ],
            'message' => 'Biography tone updated successfully'
        ]);
    }
    
    public function ajax_save_biography_data() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Save form data to post meta
        $fields = [
            'name' => '_biography_name',
            'title' => '_biography_title', 
            'organization' => '_biography_organization',
            'tone' => '_biography_tone',
            'pov' => '_biography_pov',
            'existingBio' => '_biography_existing',
            'notes' => '_biography_notes'
        ];
        
        $saved_count = 0;
        foreach ($fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                $value = sanitize_textarea_field($_POST[$field]);
                update_post_meta($post_id, $meta_key, $value);
                $saved_count++;
            }
        }
        
        wp_send_json_success([
            'message' => 'Biography data saved successfully',
            'post_id' => $post_id,
            'saved_count' => $saved_count
        ]);
    }
    
    public function ajax_save_biography_field() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $field_name = sanitize_text_field($_POST['field_name'] ?? '');
        $field_value = sanitize_textarea_field($_POST['field_value'] ?? '');
        
        if (!$post_id || empty($field_name)) {
            wp_send_json_error(['message' => 'Post ID and field name required']);
            return;
        }
        
        $meta_key = '_biography_' . $field_name;
        $result = update_post_meta($post_id, $meta_key, $field_value);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Field saved successfully',
                'field_name' => $field_name,
                'meta_key' => $meta_key
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save field']);
        }
    }
    
    public function ajax_get_biography_data() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get biography data from post meta
        $biography_data = [
            'has_data' => false,
            'biographies' => [
                'short' => get_post_meta($post_id, '_biography_short', true),
                'medium' => get_post_meta($post_id, '_biography_medium', true),
                'long' => get_post_meta($post_id, '_biography_long', true)
            ],
            'settings' => [
                'tone' => get_post_meta($post_id, '_biography_tone', true) ?: 'professional',
                'pov' => get_post_meta($post_id, '_biography_pov', true) ?: 'third'
            ],
            'personal_info' => [
                'name' => get_post_meta($post_id, '_biography_name', true),
                'title' => get_post_meta($post_id, '_biography_title', true),
                'organization' => get_post_meta($post_id, '_biography_organization', true)
            ],
            'generation_date' => get_post_meta($post_id, '_biography_generation_date', true)
        ];
        
        $biography_data['has_data'] = !empty($biography_data['biographies']['short']) || 
                                     !empty($biography_data['biographies']['medium']) || 
                                     !empty($biography_data['biographies']['long']);
        
        wp_send_json_success($biography_data);
    }
    
    public function ajax_validate_biography_data() {
        // Direct handler implementation
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        $tone = sanitize_text_field($_POST['tone'] ?? 'professional');
        $valid_tones = ['professional', 'conversational', 'authoritative', 'friendly'];
        if (!in_array($tone, $valid_tones)) {
            $errors[] = 'Invalid tone specified';
        }
        
        $pov = sanitize_text_field($_POST['pov'] ?? 'third');
        $valid_povs = ['first', 'third'];
        if (!in_array($pov, $valid_povs)) {
            $errors[] = 'Invalid point of view specified';
        }
        
        wp_send_json_success([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }
    
    public function ajax_regenerate_biography() {
        // Direct handler implementation - same as generate
        $this->ajax_generate_biography();
    }
    
    public function ajax_save_biography_to_formidable() {
        // Redirect to the new save method for WordPress post meta
        $this->ajax_save_biography_data();
    }
    
    /**
     * CRITICAL FIX: Simplified AJAX request verification
     */
    private function verify_ajax_request() {
        // Basic security checks
        if (!is_user_logged_in()) {
            error_log('MKCG: User not logged in');
            return false;
        }
        
        if (!current_user_can('edit_posts')) {
            error_log('MKCG: User lacks edit_posts capability');
            return false;
        }
        
        // Simple nonce check
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (empty($nonce)) {
            error_log('MKCG: No nonce provided');
            return false;
        }
        
        if (!wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log('MKCG: Nonce verification failed');
            return false;
        }
        
        return true;
    }
    
    /**
     * DEPRECATED: Ensure AJAX handlers exist - now handled by register_ajax_handlers
     */
    private function ensure_ajax_handlers() {
        // This method is now deprecated - AJAX handlers are registered via wp_loaded
        // Keeping for backward compatibility but not initializing the old handlers
        error_log('MKCG: ensure_ajax_handlers called but deprecated - using new registration system');
        return;
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); // Also load in admin
        add_action('wp_head', [$this, 'add_ajax_url_to_head']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // CRITICAL FIX: Register AJAX actions with higher priority to avoid conflicts
        add_action('wp_loaded', [$this, 'register_ajax_handlers'], 5);
        
        error_log('MKCG: Init hooks registered with AJAX handlers on wp_loaded');
    }
    
    /**
     * CRITICAL FIX: Register all AJAX handlers at the right time
     */
    public function register_ajax_handlers() {
        // Ensure WordPress is fully loaded
        if (!function_exists('wp_send_json_success')) {
            error_log('MKCG: WordPress AJAX functions not available yet');
            return;
        }
        
        // Topics Generator AJAX handlers
        add_action('wp_ajax_mkcg_save_topics_data', [$this, 'ajax_save_topics_data']);
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'ajax_get_topics']);
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'ajax_save_authority_hook']);
        add_action('wp_ajax_mkcg_generate_topics', [$this, 'ajax_generate_topics']);
        add_action('wp_ajax_mkcg_save_topic_field', [$this, 'ajax_save_topic_field']);
        
        // Security: Non-logged-in handler (returns error)
        add_action('wp_ajax_nopriv_mkcg_save_topics_data', [$this, 'ajax_save_topics_data_nopriv']);
        
        // Questions Generator AJAX handlers
        add_action('wp_ajax_mkcg_save_questions', [$this, 'ajax_save_questions']);
        add_action('wp_ajax_mkcg_generate_questions', [$this, 'ajax_generate_questions']);
        add_action('wp_ajax_mkcg_save_single_question', [$this, 'ajax_save_single_question']);
        add_action('wp_ajax_mkcg_get_questions_data', [$this, 'ajax_get_questions']);
        
        // Offers Generator AJAX handlers
        add_action('wp_ajax_mkcg_generate_offers', [$this, 'ajax_generate_offers']);
        add_action('wp_ajax_mkcg_save_offers', [$this, 'ajax_save_offers']);
        
        // Authority Hook Generator AJAX handlers
        add_action('wp_ajax_mkcg_save_authority_hook_data', [$this, 'ajax_save_authority_hook']);
        add_action('wp_ajax_mkcg_get_authority_hook_data', [$this, 'ajax_save_authority_hook']);
        
        // Impact Intro Generator AJAX handlers
        add_action('wp_ajax_mkcg_save_impact_intro', [$this, 'ajax_save_impact_intro']);
        add_action('wp_ajax_mkcg_get_impact_intro', [$this, 'ajax_get_impact_intro']);
        
        // Biography Generator AJAX handlers
        add_action('wp_ajax_mkcg_generate_biography', [$this, 'ajax_generate_biography']);
        add_action('wp_ajax_mkcg_modify_biography_tone', [$this, 'ajax_modify_biography_tone']);
        add_action('wp_ajax_mkcg_save_biography_to_formidable', [$this, 'ajax_save_biography_to_formidable']);
        add_action('wp_ajax_mkcg_save_biography_to_post_meta', [$this, 'ajax_save_biography_data']);
        add_action('wp_ajax_mkcg_save_biography_data', [$this, 'ajax_save_biography_data']);
        add_action('wp_ajax_mkcg_save_biography_field', [$this, 'ajax_save_biography_field']);
        add_action('wp_ajax_mkcg_get_biography_data', [$this, 'ajax_get_biography_data']);
        add_action('wp_ajax_mkcg_validate_biography_data', [$this, 'ajax_validate_biography_data']);
        add_action('wp_ajax_mkcg_regenerate_biography', [$this, 'ajax_regenerate_biography']);
        
        // Guest Intro Generator AJAX handlers
        add_action('wp_ajax_generate_guest_intro', [$this->generators['guest_intro'], 'ajax_generate_guest_intro']);
        add_action('wp_ajax_save_guest_intro_data', [$this->generators['guest_intro'], 'ajax_save_guest_intro_data']);
        add_action('wp_ajax_save_guest_intro_results', [$this->generators['guest_intro'], 'ajax_save_guest_intro_results']);
        
        // Tagline Generator AJAX handlers
        add_action('wp_ajax_mkcg_generate_taglines', [$this->generators['tagline'], 'ajax_generate_taglines']);
        add_action('wp_ajax_mkcg_save_tagline', [$this->generators['tagline'], 'ajax_save_tagline']);
        
        error_log('MKCG: Successfully registered all AJAX handlers via wp_loaded');
        error_log('MKCG: AJAX URL: ' . admin_url('admin-ajax.php'));
        
        // Test if the main handler is callable
        if (is_callable([$this, 'ajax_save_topics_data'])) {
            error_log('MKCG: ajax_save_topics_data method is callable');
        } else {
            error_log('MKCG: ERROR - ajax_save_topics_data method is NOT callable');
        }
    }
    
    private function load_dependencies() {
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-config.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-pods-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-impact-intro-service.php';

        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_topics_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_questions_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_offers_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_authority_hook_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_impact_intro_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_biography_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_guest_intro_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_tagline_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_ajax_handlers.php';
    }
    
    /**
     * SIMPLIFIED: Service initialization - WordPress will handle errors naturally
     * UPDATED: Pure Pods service, no Formidable dependencies
     */
    private function init_services() {
        // Initialize API Service
        $this->api_service = new MKCG_API_Service();
        
        // Initialize Pods Service (primary data source)
        $this->pods_service = new MKCG_Pods_Service();
        
        // Initialize Authority Hook Service (centralized functionality)
        $this->authority_hook_service = new MKCG_Authority_Hook_Service();
        
        // Initialize Impact Intro Service (centralized functionality)
        $this->impact_intro_service = new MKCG_Impact_Intro_Service();
        
        // CRITICAL FIX: Make services available globally for templates
        global $authority_hook_service, $impact_intro_service;
        $authority_hook_service = $this->authority_hook_service;
        $impact_intro_service = $this->impact_intro_service;
        
        error_log('MKCG: Services initialized with Pods as primary data source and centralized services');
        error_log('MKCG: Authority Hook and Impact Intro Services made available globally for templates');
    }
    
    /**
     * Helper method to prepare template data structure
     */
    private function prepare_template_data($generator_type, $additional_data = []) {
        return array_merge([
            'pods_service' => $this->pods_service,
            'authority_hook_service' => $this->authority_hook_service,
            'impact_intro_service' => $this->impact_intro_service,
            'api_service' => $this->api_service,
            'generator_instance' => $this->generators[$generator_type] ?? null,
            'generator_type' => $generator_type,
        ], $additional_data);
    }
    
    /**
     * CRITICAL FIX: Ensure global services are available
     */
    private function ensure_global_services() {
        global $authority_hook_service, $impact_intro_service, $pods_service, $api_service;
        
        if (!$authority_hook_service && isset($this->authority_hook_service)) {
            $authority_hook_service = $this->authority_hook_service;
            error_log('MKCG: Global authority_hook_service variable set');
        }
        
        if (!$impact_intro_service && isset($this->impact_intro_service)) {
            $impact_intro_service = $this->impact_intro_service;
            error_log('MKCG: Global impact_intro_service variable set');
        }
        
        if (!$pods_service && isset($this->pods_service)) {
            $pods_service = $this->pods_service;
            error_log('MKCG: Global pods_service variable set');
        }
        
        if (!$api_service && isset($this->api_service)) {
            $api_service = $this->api_service;
            error_log('MKCG: Global api_service variable set');
        }
        
        // Debug confirmation
        error_log('MKCG: Global services status - Authority Hook: ' . (isset($authority_hook_service) ? 'SET' : 'NOT SET') . 
                  ', Impact Intro: ' . (isset($impact_intro_service) ? 'SET' : 'NOT SET') . 
                  ', Pods: ' . (isset($pods_service) ? 'SET' : 'NOT SET') . 
                  ', API: ' . (isset($api_service) ? 'SET' : 'NOT SET'));
    }
    
    // SIMPLIFIED: Basic validation no longer needed with simplified architecture
    
    /**
     * SIMPLIFIED: Generator initialization
     */
    private function init_generators() {
        // Initialize Topics Generator (pure Pods)
        $this->generators['topics'] = new Enhanced_Topics_Generator(
            $this->api_service
        );
        
        // Initialize Questions Generator (pure Pods)
        $this->generators['questions'] = new Enhanced_Questions_Generator(
            $this->api_service
        );
        
        // Initialize Offers Generator (pure Pods)
        $this->generators['offers'] = new Enhanced_Offers_Generator(
            $this->api_service
        );
        
        // Initialize Authority Hook Generator (pure Authority Hook service)
        $this->generators['authority_hook'] = new Enhanced_Authority_Hook_Generator();
        
        // Initialize Impact Intro Generator (pure Impact Intro service)
        $this->generators['impact_intro'] = new Enhanced_Impact_Intro_Generator();
        
        // Initialize Biography Generator (comprehensive AI-powered biography generation)
        $this->generators['biography'] = new MKCG_Enhanced_Biography_Generator();
        
        // Initialize Guest Intro Generator (AI-powered guest introduction generator)
        $this->generators['guest_intro'] = new MKCG_Enhanced_Guest_Intro_Generator();
        
        // Initialize Tagline Generator (AI-powered tagline generation with multi-option selection)
        $this->generators['tagline'] = new MKCG_Enhanced_Tagline_Generator();
        
        error_log('MKCG: Generators initialized: ' . implode(', ', array_keys($this->generators)));
    }
    

    
    /**
     * SIMPLIFIED: Basic initialization
     */
    public function init() {
        error_log('MKCG: Starting simplified plugin initialization');
        
        // CRITICAL FIX: Ensure global services are available early
        $this->ensure_global_services();
        
        // ROOT FIX: Register audience taxonomy for taxonomy saving
        $this->register_audience_taxonomy();
        
        // AJAX handlers are initialized on-demand when AJAX requests come in
        
        // Initialize generators if available
        if (!empty($this->generators)) {
            foreach ($this->generators as $type => $generator) {
                if (is_object($generator) && method_exists($generator, 'init')) {
                    $generator->init();
                    error_log("MKCG: ✅ Generator '{$type}' initialized successfully");
                }
            }
        }
        
        // Register shortcodes
        $this->register_shortcodes();
        
        error_log('MKCG: ✅ Simplified plugin initialization completed');
    }
    
    /**
     * ROOT FIX: Register audience taxonomy for audience management
     */
    private function register_audience_taxonomy() {
        register_taxonomy('audience', ['guests', 'post'], [
            'labels' => [
                'name' => 'Audiences',
                'singular_name' => 'Audience',
                'menu_name' => 'Audiences',
                'all_items' => 'All Audiences',
                'edit_item' => 'Edit Audience',
                'view_item' => 'View Audience',
                'update_item' => 'Update Audience',
                'add_new_item' => 'Add New Audience',
                'new_item_name' => 'New Audience Name',
                'search_items' => 'Search Audiences',
                'not_found' => 'No audiences found'
            ],
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'audience',
                'with_front' => false
            ],
            'meta_box_cb' => 'post_categories_meta_box', // Use checkbox interface
        ]);
        
        error_log('MKCG: Audience taxonomy registered for guests and post types');
    }
    
    // SIMPLIFIED: Complex error handling and validation removed
    
    /**
     * Register shortcodes for each generator
     */
    private function register_shortcodes() {
        add_shortcode('mkcg_topics', [$this, 'topics_shortcode']);
        add_shortcode('mkcg_biography', [$this, 'biography_shortcode']);
        add_shortcode('mkcg_offers', [$this, 'offers_shortcode']);
        add_shortcode('mkcg_questions', [$this, 'questions_shortcode']);
        add_shortcode('mkcg_authority_hook', [$this, 'authority_hook_shortcode']);
        add_shortcode('mkcg_impact_intro', [$this, 'impact_intro_shortcode']);
        add_shortcode('mkcg_guest_intro', [$this, 'guest_intro_shortcode']);
        add_shortcode('mkcg_tagline', [$this, 'tagline_shortcode']);
    }
    
    /**
     * Topics Generator Shortcode - Pure Pods integration
     */
    public function topics_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // OPTIMIZED: Use helper method for template data preparation
        $template_data = $this->prepare_template_data('topics', ['post_id' => $atts['post_id']]);
        
        // Set global variables for backward compatibility
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service, $api_service;
        $pods_service = $template_data['pods_service'];
        $authority_hook_service = $template_data['authority_hook_service'];
        $generator_instance = $template_data['generator_instance'];
        $generator_type = $template_data['generator_type'];
        $api_service = $template_data['api_service'];
        
        $this->debug_log('MKCG Shortcode: Loading topics template with optimized data structure');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/topics/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Tagline Generator Shortcode - AI-powered tagline generation with multi-option selection
     */
    public function tagline_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service, $impact_intro_service;
        $pods_service = $this->pods_service;
        $authority_hook_service = $this->authority_hook_service;
        $impact_intro_service = $this->impact_intro_service;
        $generator_instance = isset($this->generators['tagline']) ? $this->generators['tagline'] : null;
        $generator_type = 'tagline';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading tagline template with centralized services');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/tagline/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Biography Generator Shortcode
     */
    public function biography_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service, $impact_intro_service;
        $pods_service = $this->pods_service;
        $authority_hook_service = $this->authority_hook_service;
        $impact_intro_service = $this->impact_intro_service;
        
        // Create biography generator instance if needed
        if (!isset($this->generators['biography'])) {
            // Include the enhanced biography generator class
            require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_biography_generator.php';
            $this->generators['biography'] = new MKCG_Enhanced_Biography_Generator();
        }
        
        $generator_instance = $this->generators['biography'];
        $generator_type = 'biography';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        // Check if we're on the results page
        if (isset($_GET['results']) && $_GET['results'] === 'true') {
            include MKCG_PLUGIN_PATH . 'templates/generators/biography/results.php';
        } else {
            include MKCG_PLUGIN_PATH . 'templates/generators/biography/default.php';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Offers Generator Shortcode - Active implementation
     */
    public function offers_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service;
        $pods_service = $this->pods_service; // Primary data source
        $authority_hook_service = $this->authority_hook_service; // Centralized Authority Hook functionality
        $generator_instance = isset($this->generators['offers']) ? $this->generators['offers'] : null;
        $generator_type = 'offers';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading offers template with centralized Authority Hook service');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/offers/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Questions Generator Shortcode - Pure Pods integration
     */
    public function questions_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // CRITICAL FIX: Set ALL required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service;
        $pods_service = $this->pods_service; // Primary data source
        $authority_hook_service = $this->authority_hook_service; // Centralized Authority Hook functionality
        $generator_instance = isset($this->generators['questions']) ? $this->generators['questions'] : null;
        $generator_type = 'questions';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        // ROOT FIX: Get template data using Questions Generator with post_id parameter
        $post_id = isset($atts['post_id']) && intval($atts['post_id']) > 0 ? intval($atts['post_id']) : 0;
        
        // Try to get post_id from URL if not provided in shortcode
        if (!$post_id && isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            $post_id = intval($_GET['post_id']);
        }
        
        if ($generator_instance && method_exists($generator_instance, 'get_template_data')) {
            $template_data = $generator_instance->get_template_data($post_id);
            error_log('MKCG Shortcode: Got template data from Questions Generator with post_id ' . $post_id . ': ' . json_encode(array_keys($template_data)));
        } else {
            error_log('MKCG Shortcode: Questions generator not available - using basic data');
            $template_data = ['form_field_values' => [], 'questions' => [], 'has_data' => false];
        }
        
        error_log('MKCG Shortcode: Loading questions template with post_id ' . $post_id . ', generator_instance available: ' . (is_object($generator_instance) ? 'YES' : 'NO'));
        
        if (!$generator_instance) {
            error_log('MKCG Shortcode: WARNING - Questions generator instance not found. Available generators: ' . implode(', ', array_keys($this->generators)));
        }
        
        // ROOT FIX: Pass template data to Questions template with proper structure
        global $mkcg_template_data;
        $mkcg_template_data = isset($template_data) ? $template_data : [
            'post_id' => $post_id,
            'has_data' => false,
            'form_field_values' => [],
            'questions' => [],
            'authority_hook_components' => []
        ];
        
        error_log('MKCG Shortcode: Passing template data with ' . count($mkcg_template_data['form_field_values']) . ' topics and ' . count($mkcg_template_data['questions']) . ' questions');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/questions/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Authority Hook Generator Shortcode - Dedicated Authority Hook page
     */
    public function authority_hook_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service;
        $pods_service = $this->pods_service; // Primary data source
        $authority_hook_service = $this->authority_hook_service; // Centralized Authority Hook functionality
        $generator_instance = isset($this->generators['authority_hook']) ? $this->generators['authority_hook'] : null;
        $generator_type = 'authority_hook';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading authority hook template with centralized Authority Hook service');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/authority-hook/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Impact Intro Generator Shortcode - Dedicated Impact Intro page
     */
    public function impact_intro_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $impact_intro_service;
        $pods_service = $this->pods_service; // Primary data source
        $impact_intro_service = $this->impact_intro_service; // Centralized Impact Intro functionality
        $generator_instance = isset($this->generators['impact_intro']) ? $this->generators['impact_intro'] : null;
        $generator_type = 'impact_intro';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading impact intro template with centralized Impact Intro service');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/impact-intro/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Guest Intro Generator Shortcode - AI-powered guest introductions
     */
    public function guest_intro_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => 0
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Ensure global variables are set for template
        $this->ensure_global_services();
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service, $impact_intro_service;
        $pods_service = $this->pods_service; // Primary data source
        $authority_hook_service = $this->authority_hook_service; // Centralized Authority Hook functionality
        $impact_intro_service = $this->impact_intro_service; // Centralized Impact Intro functionality
        $generator_instance = isset($this->generators['guest_intro']) ? $this->generators['guest_intro'] : null;
        $generator_type = 'guest_intro';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading guest intro template with centralized services');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/guest-intro/default.php';
        
        return ob_get_clean();
    }
    
    public function enqueue_scripts() {
        // Only load scripts if needed
        if (!$this->should_load_scripts()) {
            return;
        }
        
        $this->debug_log('MKCG: Enqueuing scripts for current page');
        
        // Load CSS
        wp_enqueue_style(
            'mkcg-unified-styles', 
            MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css', 
            [], 
            MKCG_VERSION,
            'all'
        );
        
        // Cross-browser compatibility is now built into the unified styles
        // No separate cross-browser CSS file needed
        
        // Load jQuery
        wp_enqueue_script('jquery');
        
        // Load Authority Hook Builder (includes all functionality)
        wp_enqueue_script(
            'authority-hook-builder',
            MKCG_PLUGIN_URL . 'assets/js/authority-hook-builder.js',
            ['jquery'],
            MKCG_VERSION,
            true
        );
        
        // Load Simple AJAX System (single AJAX solution)
        wp_enqueue_script(
            'simple-ajax',
            MKCG_PLUGIN_URL . 'assets/js/simple-ajax.js',
            ['jquery'],
            MKCG_VERSION,
            true
        );
        
        // Load Simple Event Bus (replaces complex MKCG_DataManager)
        wp_enqueue_script(
            'simple-event-bus',
            MKCG_PLUGIN_URL . 'assets/js/simple-event-bus.js',
            [],
            MKCG_VERSION,
            true
        );
        
        // Load Simple Notifications System
        wp_enqueue_script(
            'simple-notifications',
            MKCG_PLUGIN_URL . 'assets/js/simple-notifications.js',
            [],
            MKCG_VERSION,
            true
        );
        
        // Load generators
        wp_enqueue_script(
            'topics-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/topics-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'questions-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/questions-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'offers-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/offers-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'authority-hook-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/authority-hook-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        // Biography Generator
        wp_enqueue_script(
            'biography-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/biography-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'biography-results',
            MKCG_PLUGIN_URL . 'assets/js/generators/biography-results.js',
            ['simple-event-bus', 'simple-ajax'],
            MKCG_VERSION,
            true
        );
        
        // Guest Intro Generator
        wp_enqueue_script(
            'guest-intro-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/guest-intro-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        // Tagline Generator
        wp_enqueue_script(
            'tagline-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/tagline-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            MKCG_VERSION,
            true
        );
        
        // Load Cross-Browser Compatibility JS (after all generators)
        wp_enqueue_script(
            'mkcg-cross-browser-fixes',
            MKCG_PLUGIN_URL . 'assets/js/cross-browser-fixes.js',
            ['jquery', 'simple-event-bus', 'simple-ajax', 'tagline-generator'],
            MKCG_VERSION,
            true
        );
        
        // Pass data to JavaScript - CRITICAL FIX: Ensure proper AJAX setup
        wp_localize_script('simple-ajax', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'data_source' => 'post_meta', // Using direct post meta for reliability
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'browser_info' => [
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'platform' => php_uname('s') . ' ' . php_uname('r'),
                'version' => MKCG_VERSION
            ],
            'fields' => [
                'topics' => [
                    'topic_1' => 'topic_1',
                    'topic_2' => 'topic_2', 
                    'topic_3' => 'topic_3',
                    'topic_4' => 'topic_4',
                    'topic_5' => 'topic_5'
                ],
                'authority_hook' => [
                    'who' => 'who',
                    'what' => 'what',
                    'when' => 'when',
                    'how' => 'how'
                ]
            ],
            'ajax_actions' => [
                'save_topics' => 'mkcg_save_topics_data',
                'get_topics' => 'mkcg_get_topics_data',
                'save_authority_hook' => 'mkcg_save_authority_hook',
                'generate_topics' => 'mkcg_generate_topics',
                'save_topic_field' => 'mkcg_save_topic_field',
                'save_questions' => 'mkcg_save_questions',
                'generate_questions' => 'mkcg_generate_questions',
                'get_questions' => 'mkcg_get_questions_data',
                'save_offers' => 'mkcg_save_offers',
                'generate_offers' => 'mkcg_generate_offers',
                'save_impact_intro' => 'mkcg_save_impact_intro',
                'get_impact_intro' => 'mkcg_get_impact_intro',
                'generate_biography' => 'mkcg_generate_biography',
                'save_biography' => 'mkcg_save_biography_data',
                'get_biography' => 'mkcg_get_biography_data',
                'generate_guest_intro' => 'generate_guest_intro',
                'save_guest_intro_data' => 'save_guest_intro_data',
                'save_guest_intro_results' => 'save_guest_intro_results',
                'generate_taglines' => 'mkcg_generate_taglines',
                'save_tagline' => 'mkcg_save_tagline'
            ]
        ]);
        
        // Make services available globally for testing
        wp_add_inline_script('simple-ajax', '
            window.MKCG_Pods_Service = true;
            window.MKCG_Formidable_Service = true;
            window.podsService = true;
            window.formidableService = true;
        ');
        
        error_log('MKCG: Script loading completed with cross-browser compatibility support');
    }
    
    // SIMPLIFIED: No complex generator detection needed
    
    // SIMPLIFIED: No separate script loading methods needed
    
    // SIMPLIFIED: All complex script loading methods removed
    
    private function should_load_scripts() {
        // Check if we're on a page that uses any of our generators
        global $post;
        
        if (!$post) {
            // Check for admin pages that need our scripts
            if (is_admin()) {
                $screen = get_current_screen();
                if ($screen && ($screen->id === 'mkcg-tests' || strpos($screen->id, 'mkcg-') === 0)) {
                    return true;
                }
            }
            return false;
        }
        
        // Check for shortcodes in post content
        $generator_shortcodes = ['mkcg_biography', 'mkcg_offers', 'mkcg_topics', 'mkcg_questions', 'mkcg_authority_hook', 'mkcg_impact_intro', 'mkcg_guest_intro'];
        foreach ($generator_shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $this->debug_log('MKCG: Loading scripts for shortcode: ' . $shortcode);
                return true;
            }
        }
        
        // Check for Formidable edit pages
        if (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit' && isset($_GET['entry'])) {
            $this->debug_log('MKCG: Loading scripts for Formidable edit page');
            return true;
        }
        
        // Check for biography results pages
        if (isset($_GET['results']) && $_GET['results'] === 'true') {
            $this->debug_log('MKCG: Loading scripts for results page');
            return true;
        }
        
        return false;
    }
    
    public function add_ajax_url_to_head() {
        ?>
        <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            // CRITICAL FIX: Ensure nonce is available globally
            var mkcg_nonce = "<?php echo wp_create_nonce('mkcg_nonce'); ?>";
            
            // Make sure mkcg_vars exists with nonce
            window.mkcg_vars = window.mkcg_vars || {};
            window.mkcg_vars.nonce = window.mkcg_vars.nonce || mkcg_nonce;
            window.mkcg_vars.ajax_url = window.mkcg_vars.ajax_url || ajaxurl;
            
            console.log('MKCG: AJAX URL set to:', ajaxurl);
            console.log('MKCG: Nonce set:', mkcg_nonce.substring(0, 10) + '...');
        </script>
        <?php
    }
    
    public function activate() {
        // Plugin activation tasks
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Plugin deactivation tasks
        flush_rewrite_rules();
    }
    
    /**
     * Add admin menu for testing and diagnostics
     */
    public function add_admin_menu() {
        if (current_user_can('administrator')) {
            add_menu_page(
                'MKCG Tests', // Page title
                'MKCG Tests', // Menu title
                'manage_options', // Capability
                'mkcg-tests', // Menu slug
                [$this, 'admin_test_page'], // Callback
                'dashicons-clipboard', // Icon
                80 // Position
            );
            
            add_submenu_page(
                'mkcg-tests',
                'Authority Hook Service Test',
                'Authority Hook Test',
                'manage_options',
                'mkcg-authority-hook-test',
                [$this, 'authority_hook_test_page']
            );
        }
    }
    
    /**
     * Main admin test page
     */
    public function admin_test_page() {
        echo '<div class="wrap">';
        echo '<h1>Media Kit Content Generator - Tests & Diagnostics</h1>';
        echo '<div class="card" style="max-width: none;">';
        echo '<h2>Available Tests</h2>';
        echo '<p>Select a test to validate the plugin functionality:</p>';
        echo '<ul>';
        echo '<li><a href="' . admin_url('admin.php?page=mkcg-authority-hook-test') . '" class="button button-primary">🧪 Authority Hook Service Architecture Test</a> - Validate centralized service implementation</li>';
        echo '<li><a href="' . plugins_url('test-authority-hook-service-architecture.php', __FILE__) . '" class="button button-secondary" target="_blank">🔗 Direct Test Link</a> - Run test in new window</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="card" style="max-width: none; margin-top: 20px;">';
        echo '<h2>Plugin Information</h2>';
        echo '<p><strong>Version:</strong> ' . MKCG_VERSION . '</p>';
        echo '<p><strong>Plugin Path:</strong> ' . MKCG_PLUGIN_PATH . '</p>';
        echo '<p><strong>Plugin URL:</strong> ' . MKCG_PLUGIN_URL . '</p>';
        echo '<p><strong>Authority Hook Service:</strong> ' . (class_exists('MKCG_Authority_Hook_Service') ? '✅ Available' : '❌ Not Available') . '</p>';
        echo '<p><strong>API Service:</strong> ' . (isset($this->api_service) ? '✅ Initialized' : '❌ Not Initialized') . '</p>';
        echo '<p><strong>Pods Service:</strong> ' . (isset($this->pods_service) ? '✅ Initialized' : '❌ Not Initialized') . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Authority Hook Service test page (embedded)
     */
    public function authority_hook_test_page() {
        echo '<div class="wrap">';
        echo '<h1>Authority Hook Service Architecture Test</h1>';
        echo '<div style="background: white; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        
        // Include and run the test
        if (file_exists(MKCG_PLUGIN_PATH . 'test-authority-hook-service-architecture.php')) {
            // Capture the test output
            ob_start();
            include_once MKCG_PLUGIN_PATH . 'test-authority-hook-service-architecture.php';
            $test_output = ob_get_clean();
            
            // Extract just the body content (remove html/head tags)
            if (preg_match('/<body[^>]*>(.*?)<\/body>/s', $test_output, $matches)) {
                echo $matches[1];
            } else {
                echo $test_output;
            }
        } else {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px;">';
            echo '<strong>❌ Test File Not Found</strong><br>';
            echo 'The test file <code>test-authority-hook-service-architecture.php</code> was not found in the plugin directory.';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    // SIMPLIFIED: Basic getter methods
    public function get_api_service() {
        return $this->api_service;
    }
    
    public function get_pods_service() {
        return $this->pods_service;
    }
    
    public function get_authority_hook_service() {
        return $this->authority_hook_service;
    }
    

    
    public function get_generator($type) {
        return isset($this->generators[$type]) ? $this->generators[$type] : null;
    }
    
    public function get_current_entry_id() {
        if (isset($_GET['entry'])) {
            $entry_key = sanitize_text_field($_GET['entry']);
            // Simple numeric check
            if (is_numeric($entry_key)) {
                return intval($entry_key);
            }
        }
        return 0;
    }
    
    public function get_current_entry_key() {
        return isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
    }
}

// Initialize the plugin
function mkcg_init() {
    return Media_Kit_Content_Generator::get_instance();
}

// Hook into plugins_loaded to ensure WordPress is ready
add_action('plugins_loaded', 'mkcg_init');

// Debug function to test CSS loading
function mkcg_debug_css() {
    if (current_user_can('administrator')) {
        echo '<!-- MKCG Debug: Plugin URL = ' . MKCG_PLUGIN_URL . ' -->';
        echo '<!-- MKCG Debug: CSS Path = ' . MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css -->';
    }
}
add_action('wp_head', 'mkcg_debug_css');