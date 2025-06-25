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
    private $formidable_service;
    private $authority_hook_service;
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
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); // Also load in admin
        add_action('wp_head', [$this, 'add_ajax_url_to_head']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    private function load_dependencies() {
        // Load shared services
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-formidable-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php';
        
        // Load base generator
        require_once MKCG_PLUGIN_PATH . 'includes/generators/class-mkcg-base-generator.php';
        
        // Load specific generators
        require_once MKCG_PLUGIN_PATH . 'includes/generators/class-mkcg-biography-generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/class-mkcg-offers-generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/class-mkcg-topics-generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/class-mkcg-questions-generator.php';
    }
    
    private function init_services() {
        $this->api_service = new MKCG_API_Service();
        $this->formidable_service = new MKCG_Formidable_Service();
        $this->authority_hook_service = new MKCG_Authority_Hook_Service($this->formidable_service);
    }
    
    private function init_generators() {
        $this->generators['biography'] = new MKCG_Biography_Generator(
            $this->api_service, 
            $this->formidable_service,
            $this->authority_hook_service
        );
        
        $this->generators['offers'] = new MKCG_Offers_Generator(
            $this->api_service, 
            $this->formidable_service,
            $this->authority_hook_service
        );
        
        $this->generators['topics'] = new MKCG_Topics_Generator(
            $this->api_service, 
            $this->formidable_service,
            $this->authority_hook_service
        );
        
        $this->generators['questions'] = new MKCG_Questions_Generator(
            $this->api_service, 
            $this->formidable_service,
            $this->authority_hook_service
        );
    }
    
    public function init() {
        // Initialize each generator
        foreach ($this->generators as $generator) {
            $generator->init();
        }
        
        // Register shortcodes
        $this->register_shortcodes();
    }
    
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
     * Topics Generator Shortcode
     */
    public function topics_shortcode($atts) {
        $atts = shortcode_atts([
            'entry_id' => 0,
            'entry_key' => ''
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // Set global variables for template
        global $formidable_service;
        $formidable_service = $this->formidable_service;
        
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
     * Questions Generator Shortcode (placeholder)
     */
    public function questions_shortcode($atts) {
        return '<div class="mkcg-placeholder">Questions Generator - Coming Soon</div>';
    }
    
    public function enqueue_scripts() {
        // Always load CSS and JS for now (for testing)
        $css_file = MKCG_PLUGIN_PATH . 'assets/css/mkcg-unified-styles.css';
        
        // Debug: Check if CSS file exists
        if (!file_exists($css_file)) {
            error_log('MKCG: CSS file not found at: ' . $css_file);
        }
        
        // Enqueue unified CSS with high priority
        wp_enqueue_style(
            'mkcg-unified-styles', 
            MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css', 
            [], 
            MKCG_VERSION . '_' . filemtime($css_file), // Cache busting
            'all'
        );
        
        // Add inline CSS for immediate testing
        $inline_css = '
        /* MKCG Inline Test CSS */
        .topics-generator {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 20px !important;
            background-color: #f5f7fa !important;
        }
        
        .topics-generator__title {
            font-size: 32px !important;
            font-weight: 700 !important;
            color: #2c3e50 !important;
            text-align: center !important;
            margin-bottom: 30px !important;
        }
        
        .topics-generator__content {
            display: flex !important;
            gap: 30px !important;
            flex-wrap: wrap !important;
        }
        
        .topics-generator__panel {
            flex: 1 !important;
            min-width: 300px !important;
        }
        
        .topics-generator__panel--right {
            background-color: #f9fafb !important;
            padding: 25px !important;
            border-radius: 8px !important;
            border: 1px solid #e0e0e0 !important;
        }
        
        .topics-generator__authority-hook {
            background-color: #f9fafb !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            padding: 20px !important;
            margin-bottom: 25px !important;
        }
        
        .topics-generator__button {
            display: inline-flex !important;
            align-items: center !important;
            padding: 8px 16px !important;
            border: none !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            margin-right: 10px !important;
        }
        
        .topics-generator__button--generate {
            background-color: #f87f34 !important;
            color: white !important;
        }
        
        .topics-generator__button--edit {
            background-color: white !important;
            color: #1a9bdc !important;
            border: 1px solid #1a9bdc !important;
        }
        
        .topics-generator__builder--hidden {
            display: none !important;
        }
        
        .topics-generator__input {
            width: 100% !important;
            padding: 12px !important;
            border: 1px solid #dce1e5 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
        }
        
        .topics-generator__form-field {
            margin-bottom: 20px !important;
        }
        
        .topics-generator__form-field-input {
            width: 100% !important;
            padding: 12px 15px !important;
            border: 1px solid #dce1e5 !important;
            border-radius: 4px !important;
            font-size: 14px !important;
        }
        
        @media (max-width: 768px) {
            .topics-generator__content {
                flex-direction: column !important;
            }
        }
        ';
        
        wp_add_inline_style('mkcg-unified-styles', $inline_css);
        
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        
        // Enqueue enhanced FormUtils
        wp_enqueue_script(
            'mkcg-form-utils', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
            ['jquery'], 
            MKCG_VERSION, 
            true
        );
        
        // Enqueue Authority Hook Builder (vanilla JS)
        wp_enqueue_script(
            'mkcg-authority-hook-builder', 
            MKCG_PLUGIN_URL . 'assets/js/authority-hook-builder.js', 
            [], // No dependencies - vanilla JS
            MKCG_VERSION, 
            true
        );
        
        // Enqueue Topics Generator (depends on Authority Hook Builder)
        wp_enqueue_script(
            'mkcg-topics-generator', 
            MKCG_PLUGIN_URL . 'assets/js/generators/topics-generator.js', 
            ['mkcg-authority-hook-builder'], 
            MKCG_VERSION, 
            true
        );
        
        // Pass data to JavaScript
        wp_localize_script('mkcg-form-utils', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL
        ]);
        
        // Also pass topics-specific nonce
        wp_localize_script('mkcg-topics-generator', 'topics_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('generate_topics_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL
        ]);
        
        // Debug output
        error_log('MKCG: Scripts enqueued. CSS URL: ' . MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css');
    }
    
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
    
    // Getter methods for services (for generator access)
    public function get_api_service() {
        return $this->api_service;
    }
    
    public function get_formidable_service() {
        return $this->formidable_service;
    }
    
    public function get_authority_hook_service() {
        return $this->authority_hook_service;
    }
    
    public function get_generator($type) {
        return isset($this->generators[$type]) ? $this->generators[$type] : null;
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