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
    private $ajax_handlers = null;

    private $generators = [];
    
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
     * ROOT FIX: Direct AJAX handler for save topics to avoid complex handler chain
     */
    public function ajax_save_topics() {
        error_log('MKCG: Starting ajax_save_topics handler - direct implementation');
        
        // Verify request
        if (!$this->verify_ajax_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        error_log('MKCG: Processing save for post ID: ' . $post_id);
        error_log('MKCG: POST data: ' . print_r($_POST, true));
        
        // Extract topics data
        $topics_data = $this->extract_topics_data();
        $authority_hook_data = $this->extract_authority_hook_data();
        
        error_log('MKCG: Extracted topics: ' . print_r($topics_data, true));
        error_log('MKCG: Extracted authority hook: ' . print_r($authority_hook_data, true));
        
        if (empty($topics_data) && empty($authority_hook_data)) {
            wp_send_json_error(['message' => 'No data provided to save']);
            return;
        }
        
        $results = [];
        
        // Save topics using Pods service
        if (!empty($topics_data)) {
            $topics_result = $this->pods_service->save_topics($post_id, $topics_data);
            $results['topics'] = $topics_result;
            error_log('MKCG: Topics save result: ' . json_encode($topics_result));
        }
        
        // Save authority hook using Pods service
        if (!empty($authority_hook_data)) {
            $auth_result = $this->pods_service->save_authority_hook_components($post_id, $authority_hook_data);
            $results['authority_hook'] = $auth_result;
            error_log('MKCG: Authority hook save result: ' . json_encode($auth_result));
            
            // ROOT FIX: Save audience taxonomy if WHO field contains audience data
            if (!empty($authority_hook_data['who']) && $authority_hook_data['who'] !== 'your audience') {
                $audience_result = $this->save_audience_taxonomy($post_id, $authority_hook_data['who']);
                $results['audience_taxonomy'] = $audience_result;
                error_log('MKCG: Audience taxonomy save result: ' . json_encode($audience_result));
            }
        }
        
        // Determine overall success
        $overall_success = (!empty($results['topics']) && $results['topics']['success']) || 
                          (!empty($results['authority_hook']) && $results['authority_hook']['success']);
        
        if ($overall_success) {
            wp_send_json_success([
                'message' => 'Data saved successfully',
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
     * ROOT FIX: Extract topics data with comprehensive fallback strategies
     */
    private function extract_topics_data() {
        $topics = [];
        
        // Method 1: Array notation (topics[topic_1], etc.)
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'topics[') === 0) {
                preg_match('/topics\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $topics[$matches[1]] = sanitize_textarea_field($value);
                }
            }
        }
        
        // Method 2: JSON string in topics field
        if (empty($topics) && isset($_POST['topics'])) {
            $topics_raw = is_array($_POST['topics']) ? $_POST['topics'] : stripslashes($_POST['topics']);
            
            if (is_string($topics_raw)) {
                $decoded = json_decode($topics_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $topics[$key] = sanitize_textarea_field($value);
                        }
                    }
                }
            } elseif (is_array($topics_raw)) {
                foreach ($topics_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $topics[$key] = sanitize_textarea_field($value);
                    }
                }
            }
        }
        
        // Method 3: Individual topic fields
        if (empty($topics)) {
            for ($i = 1; $i <= 5; $i++) {
                $field_name = 'topic_' . $i;
                if (isset($_POST[$field_name]) && !empty(trim($_POST[$field_name]))) {
                    $topics[$field_name] = sanitize_textarea_field($_POST[$field_name]);
                }
            }
        }
        
        return $topics;
    }
    
    /**
     * ROOT FIX: Extract authority hook data with comprehensive fallback strategies
     */
    private function extract_authority_hook_data() {
        $components = [];
        
        // Method 1: Array notation (authority_hook[who], etc.)
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'authority_hook[') === 0) {
                preg_match('/authority_hook\[(.*?)\]/', $key, $matches);
                if (isset($matches[1]) && !empty(trim($value))) {
                    $field = $matches[1];
                    if ($field === 'result') $field = 'what'; // Map result to what
                    $components[$field] = sanitize_textarea_field($value);
                }
            }
        }
        
        // Method 2: JSON string in authority_hook field
        if (empty($components) && isset($_POST['authority_hook'])) {
            $auth_raw = is_array($_POST['authority_hook']) ? $_POST['authority_hook'] : stripslashes($_POST['authority_hook']);
            
            if (is_string($auth_raw)) {
                $decoded = json_decode($auth_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $key => $value) {
                        if (!empty(trim($value))) {
                            $mapped_key = ($key === 'result') ? 'what' : $key;
                            $components[$mapped_key] = sanitize_textarea_field($value);
                        }
                    }
                }
            } elseif (is_array($auth_raw)) {
                foreach ($auth_raw as $key => $value) {
                    if (!empty(trim($value))) {
                        $mapped_key = ($key === 'result') ? 'what' : $key;
                        $components[$mapped_key] = sanitize_textarea_field($value);
                    }
                }
            }
        }
        
        // Method 3: Individual component fields
        if (empty($components)) {
            $fields = ['who', 'what', 'result', 'when', 'how'];
            foreach ($fields as $field) {
                if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                    $mapped_field = ($field === 'result') ? 'what' : $field;
                    $components[$mapped_field] = sanitize_textarea_field($_POST[$field]);
                }
            }
        }
        
        return $components;
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
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_get_topics();
    }
    
    public function ajax_save_authority_hook() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_save_authority_hook();
    }
    
    public function ajax_generate_topics() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_generate_topics();
    }
    
    public function ajax_save_topic_field() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_save_topic_field();
    }
    
    /**
     * ROOT FIX: Questions Generator AJAX handlers
     */
    public function ajax_save_questions() {
        // Delegate to Questions Generator
        if (isset($this->generators['questions'])) {
            $this->generators['questions']->handle_save_questions();
        } else {
            wp_send_json_error(['message' => 'Questions generator not available']);
        }
    }
    
    public function ajax_generate_questions() {
        // Delegate to Questions Generator
        if (isset($this->generators['questions'])) {
            $this->generators['questions']->handle_generate_questions();
        } else {
            wp_send_json_error(['message' => 'Questions generator not available']);
        }
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
        // Delegate to Questions Generator
        if (isset($this->generators['questions'])) {
            $this->generators['questions']->handle_get_questions();
        } else {
            wp_send_json_error(['message' => 'Questions generator not available']);
        }
    }
    
    /**
     * ROOT FIX: Offers Generator AJAX handlers
     */
    public function ajax_generate_offers() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_generate_offers();
    }
    
    public function ajax_save_offers() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_save_offers();
    }
    
    /**
     * Simple AJAX request verification
     */
    private function verify_ajax_request() {
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
     * SIMPLEST SOLUTION: Ensure AJAX handlers exist
     */
    private function ensure_ajax_handlers() {
        if (!$this->ajax_handlers) {
            // Ensure services exist
            if (!$this->pods_service) {
                require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-pods-service.php';
                $this->pods_service = new MKCG_Pods_Service();
            }
            
            // Ensure Authority Hook Service is available globally (used by AJAX handlers)
            if (!isset($GLOBALS['authority_hook_service'])) {
                require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';
                $GLOBALS['authority_hook_service'] = new MKCG_Authority_Hook_Service();
            }
            
            // Create AJAX handlers
            require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_ajax_handlers.php';
            $this->ajax_handlers = new Enhanced_AJAX_Handlers($this->pods_service, null);
            
            error_log('MKCG: AJAX handlers initialized on demand');
        }
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); // Also load in admin
        add_action('wp_head', [$this, 'add_ajax_url_to_head']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // CRITICAL FIX: Register AJAX actions directly here - simplest solution
        add_action('wp_ajax_mkcg_save_topics_data', [$this, 'ajax_save_topics']);
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'ajax_get_topics']);
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'ajax_save_authority_hook']);
        add_action('wp_ajax_mkcg_generate_topics', [$this, 'ajax_generate_topics']);
        add_action('wp_ajax_mkcg_save_topic_field', [$this, 'ajax_save_topic_field']);
        
        // ROOT FIX: Add debug logging for AJAX registration
        error_log('MKCG: Registered AJAX action wp_ajax_mkcg_save_topics_data');
        
        // ROOT FIX: Add missing Questions Generator AJAX handlers
        add_action('wp_ajax_mkcg_save_questions', [$this, 'ajax_save_questions']);
        add_action('wp_ajax_mkcg_generate_questions', [$this, 'ajax_generate_questions']);
        add_action('wp_ajax_mkcg_save_single_question', [$this, 'ajax_save_single_question']);
        add_action('wp_ajax_mkcg_get_questions_data', [$this, 'ajax_get_questions']);
        
        // ROOT FIX: Add missing Offers Generator AJAX handlers
        add_action('wp_ajax_mkcg_generate_offers', [$this, 'ajax_generate_offers']);
        add_action('wp_ajax_mkcg_save_offers', [$this, 'ajax_save_offers']);
    }
    
    private function load_dependencies() {
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-config.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-pods-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';

        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_topics_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_questions_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_offers_generator.php';
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
        
        // CRITICAL FIX: Make Authority Hook Service available globally for templates
        global $authority_hook_service;
        $authority_hook_service = $this->authority_hook_service;
        
        error_log('MKCG: Services initialized with Pods as primary data source and centralized Authority Hook service');
        error_log('MKCG: Authority Hook Service made available globally for templates');
    }
    
    /**
     * CRITICAL FIX: Ensure global services are available
     */
    private function ensure_global_services() {
        global $authority_hook_service, $pods_service, $api_service;
        
        if (!$authority_hook_service && isset($this->authority_hook_service)) {
            $authority_hook_service = $this->authority_hook_service;
            error_log('MKCG: Global authority_hook_service variable set');
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
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type, $authority_hook_service;
        $pods_service = $this->pods_service; // Primary data source
        $authority_hook_service = $this->authority_hook_service; // Centralized Authority Hook functionality
        $generator_instance = isset($this->generators['topics']) ? $this->generators['topics'] : null;
        $generator_type = 'topics';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading topics template with pure Pods generator and centralized Authority Hook service');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/topics/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Biography Generator Shortcode (placeholder)
     */
    public function biography_shortcode($atts) {
        // CRITICAL FIX: Ensure global variables are set
        $this->ensure_global_services();
        
        return '<div class="mkcg-placeholder">Biography Generator - Coming Soon</div>';
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
    
    public function enqueue_scripts() {
        // Load CSS
        wp_enqueue_style(
            'mkcg-unified-styles', 
            MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css', 
            [], 
            MKCG_VERSION,
            'all'
        );
        
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
        
        // Pass data to JavaScript - UPDATED for Pods integration
        wp_localize_script('simple-ajax', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'data_source' => 'pods', // Indicate we're using Pods
            'fields' => [
                'topics' => [
                    'topic_1' => 'topic_1',
                    'topic_2' => 'topic_2', 
                    'topic_3' => 'topic_3',
                    'topic_4' => 'topic_4',
                    'topic_5' => 'topic_5'
                ],
                'authority_hook' => [
                    'who' => 'guest_title',
                    'when' => 'hook_when',
                    'what' => 'hook_what',
                    'how' => 'hook_how',
                    'where' => 'hook_where',
                    'why' => 'hook_why'
                ],
                'questions' => [
                    'pattern' => 'question_{number}' // question_1, question_2, etc.
                ]
            ],
            'ajax_actions' => [
                'save_topics' => 'mkcg_save_topics_data',
                'get_topics' => 'mkcg_get_topics_data',
                'save_authority_hook' => 'mkcg_save_authority_hook',
                'generate_topics' => 'mkcg_generate_topics',
                'save_questions' => 'mkcg_save_questions',
                'get_questions' => 'mkcg_get_questions_data',
                'generate_questions' => 'mkcg_generate_questions',
                'save_single_question' => 'mkcg_save_single_question',
                'generate_offers' => 'mkcg_generate_offers',
                'save_offers' => 'mkcg_save_offers'
            ]
        ]);
        
        // Make services available globally for testing
        wp_add_inline_script('simple-ajax', '
            window.MKCG_Pods_Service = true;
            window.MKCG_Formidable_Service = true;
            window.podsService = true;
            window.formidableService = true;
        ');
        
        error_log('MKCG: Simplified script loading completed with simple notifications');
    }
    
    // SIMPLIFIED: No complex generator detection needed
    
    // SIMPLIFIED: No separate script loading methods needed
    
    // SIMPLIFIED: All complex script loading methods removed
    
    private function should_load_scripts() {
        // Always load for now during development/testing
        return true;
        
        // Check if we're on a page that uses any of our generators
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check for shortcodes in post content
        $generator_shortcodes = ['mkcg_biography', 'mkcg_offers', 'mkcg_topics', 'mkcg_questions'];
        foreach ($generator_shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                return true;
            }
        }
        
        // Check for Formidable edit pages
        if (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit' && isset($_GET['entry'])) {
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