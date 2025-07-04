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
     * SIMPLE AJAX HANDLERS - Direct implementation
     */
    public function ajax_save_topics() {
        // Initialize on demand
        $this->ensure_ajax_handlers();
        $this->ajax_handlers->handle_save_topics();
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
    }
    
    private function load_dependencies() {
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-config.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-pods-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';

        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_topics_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_questions_generator.php';
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
        
        error_log('MKCG: Generators initialized: ' . implode(', ', array_keys($this->generators)));
    }
    

    
    /**
     * SIMPLIFIED: Basic initialization
     */
    public function init() {
        error_log('MKCG: Starting simplified plugin initialization');
        
        // CRITICAL FIX: Ensure global services are available early
        $this->ensure_global_services();
        
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
     * Offers Generator Shortcode (placeholder)
     */
    public function offers_shortcode($atts) {
        // CRITICAL FIX: Ensure global variables are set
        $this->ensure_global_services();
        
        return '<div class="mkcg-placeholder">Offers Generator - Coming Soon</div>';
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
        
        // Load Authority Hook Builder FIRST (needed by other scripts)
        wp_enqueue_script(
            'authority-hook-builder',
            MKCG_PLUGIN_URL . 'assets/js/authority-hook-builder.js',
            ['jquery'],
            MKCG_VERSION,
            true
        );
        
        // Load Authority Hook Service Integration (centralized service)
        wp_enqueue_script(
            'authority-hook-service-integration',
            MKCG_PLUGIN_URL . 'assets/js/authority-hook-service-integration.js',
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
            ['simple-event-bus', 'simple-ajax', 'authority-hook-builder', 'authority-hook-service-integration'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'questions-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/questions-generator.js',
            ['simple-event-bus', 'simple-ajax', 'authority-hook-service-integration'],
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
                'save_questions' => 'mkcg_save_questions_data',
                'get_questions' => 'mkcg_get_questions_data'
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