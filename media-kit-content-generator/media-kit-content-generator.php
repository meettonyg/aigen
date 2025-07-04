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
        
        // SIMPLIFIED: No separate AJAX initialization needed - handled in generator
    }
    
    // SIMPLIFIED: AJAX handlers are now initialized directly in the Enhanced_Topics_Generator
    
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); // Also load in admin
        add_action('wp_head', [$this, 'add_ajax_url_to_head']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    private function load_dependencies() {
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-config.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-pods-service.php';

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
        

        
        error_log('MKCG: Services initialized with Pods as primary data source');
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
        
        // SIMPLIFIED: Set required global variables for template
        global $pods_service, $generator_instance, $generator_type;
        $pods_service = $this->pods_service; // Primary data source
        $generator_instance = isset($this->generators['topics']) ? $this->generators['topics'] : null;
        $generator_type = 'topics';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading topics template with pure Pods generator');
        
        // Include the template
        include MKCG_PLUGIN_PATH . 'templates/generators/topics/default.php';
        
        return ob_get_clean();
    }
    
    /**
     * Biography Generator Shortcode (placeholder)
     */
    public function biography_shortcode($atts) {
        return '<div class="mkcg-placeholder">Biography Generator - Coming Soon</div>';
    }
    
    /**
     * Offers Generator Shortcode (placeholder)
     */
    public function offers_shortcode($atts) {
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
        
        // CRITICAL FIX: Set ALL required global variables for template
        global $pods_service, $generator_instance, $generator_type;
        $pods_service = $this->pods_service; // Primary data source
        $generator_instance = isset($this->generators['questions']) ? $this->generators['questions'] : null;
        $generator_type = 'questions';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        // CRITICAL FIX: Get template data using Questions Generator if available
        if ($generator_instance && method_exists($generator_instance, 'get_template_data')) {
            $template_data = $generator_instance->get_template_data();
            error_log('MKCG Shortcode: Got template data from Questions Generator: ' . json_encode(array_keys($template_data)));
        } else {
            error_log('MKCG Shortcode: Questions generator not available - using basic data');
            $template_data = ['topics' => [], 'questions' => [], 'has_data' => false];
        }
        
        error_log('MKCG Shortcode: Loading questions template with generator_instance available: ' . (is_object($generator_instance) ? 'YES' : 'NO'));
        
        if (!$generator_instance) {
            error_log('MKCG Shortcode: WARNING - Questions generator instance not found. Available generators: ' . implode(', ', array_keys($this->generators)));
        }
        
        // Pass template data to Questions template
        global $mkcg_template_data;
        $mkcg_template_data = isset($template_data) ? $template_data : [];
        
        error_log('MKCG Shortcode: Passing template data: ' . json_encode(array_keys($mkcg_template_data)));
        
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
            ['simple-event-bus', 'simple-ajax'],
            MKCG_VERSION,
            true
        );
        
        wp_enqueue_script(
            'questions-generator',
            MKCG_PLUGIN_URL . 'assets/js/generators/questions-generator.js',
            ['simple-event-bus', 'simple-ajax'],
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
            console.log('MKCG: AJAX URL set to:', ajaxurl);
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
    

    
    // SIMPLIFIED: Basic getter methods
    public function get_api_service() {
        return $this->api_service;
    }
    
    public function get_pods_service() {
        return $this->pods_service;
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

// Start the plugin
mkcg_init();

// Debug function to test CSS loading
function mkcg_debug_css() {
    if (current_user_can('administrator')) {
        echo '<!-- MKCG Debug: Plugin URL = ' . MKCG_PLUGIN_URL . ' -->';
        echo '<!-- MKCG Debug: CSS Path = ' . MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css -->';
    }
}
add_action('wp_head', 'mkcg_debug_css');