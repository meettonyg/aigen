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
    }
    
    public function enqueue_scripts() {
        // Only enqueue scripts on relevant pages
        if ($this->should_load_scripts()) {
            // Enqueue unified CSS
            wp_enqueue_style(
                'mkcg-unified-styles', 
                MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css', 
                [], 
                MKCG_VERSION
            );
            
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
            
            // Pass data to JavaScript
            wp_localize_script('mkcg-form-utils', 'mkcg_vars', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mkcg_nonce'),
                'plugin_url' => MKCG_PLUGIN_URL
            ]);
        }
    }
    
    private function should_load_scripts() {
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
        if ($this->should_load_scripts()) {
            ?>
            <script type="text/javascript">
                var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            </script>
            <?php
        }
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