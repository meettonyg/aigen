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
        
        // ROOT-LEVEL FIX: Enhanced admin notices for better user experience
        add_action('admin_notices', [$this, 'show_admin_notices']);
    }
    
    private function load_dependencies() {
        // SIMPLIFIED: Load only essential simplified classes
        $required_files = [
            // Essential services only
            'includes/services/class-mkcg-config.php' => 'MKCG_Config',
            'includes/services/class-mkcg-api-service.php' => 'MKCG_API_Service',
            'includes/services/enhanced_formidable_service.php' => 'Enhanced_Formidable_Service',
            
            // Simplified generators and handlers
            'includes/generators/enhanced_topics_generator.php' => 'Enhanced_Topics_Generator',
            'includes/generators/enhanced_ajax_handlers.php' => 'Enhanced_AJAX_Handlers'
        ];
        
        $loading_errors = [];
        
        foreach ($required_files as $file_path => $expected_class) {
            $full_path = MKCG_PLUGIN_PATH . $file_path;
            
            // Check file existence
            if (!file_exists($full_path)) {
                $loading_errors[] = "File not found: {$file_path}";
                error_log("MKCG: CRITICAL - File not found: {$full_path}");
                continue;
            }
            
            // Check file readability
            if (!is_readable($full_path)) {
                $loading_errors[] = "File not readable: {$file_path}";
                error_log("MKCG: CRITICAL - File not readable: {$full_path}");
                continue;
            }
            
            // Include file with error catching
            try {
                require_once $full_path;
                
                // Verify class was successfully loaded
                if (!class_exists($expected_class)) {
                    $loading_errors[] = "Class {$expected_class} not found after loading {$file_path}";
                    error_log("MKCG: CRITICAL - Class {$expected_class} not defined after loading {$full_path}");
                } else {
                    error_log("MKCG: ✅ Successfully loaded {$expected_class} from {$file_path}");
                }
                
            } catch (ParseError $e) {
                $loading_errors[] = "Parse error in {$file_path}: " . $e->getMessage();
                error_log("MKCG: CRITICAL - Parse error in {$full_path}: " . $e->getMessage());
            } catch (Error $e) {
                $loading_errors[] = "Fatal error in {$file_path}: " . $e->getMessage();
                error_log("MKCG: CRITICAL - Fatal error in {$full_path}: " . $e->getMessage());
            } catch (Exception $e) {
                $loading_errors[] = "Exception in {$file_path}: " . $e->getMessage();
                error_log("MKCG: CRITICAL - Exception in {$full_path}: " . $e->getMessage());
            }
        }
        
        // Report loading status
        if (empty($loading_errors)) {
            error_log('MKCG: ✅ All dependencies loaded successfully');
        } else {
            error_log('MKCG: ❌ Dependency loading errors: ' . implode('; ', $loading_errors));
            
            // Add admin notice for critical errors
            add_action('admin_notices', function() use ($loading_errors) {
                echo '<div class="notice notice-error"><p><strong>Media Kit Content Generator:</strong> Critical loading errors detected. Check error logs for details.</p></div>';
            });
        }
        
        // Final verification of critical classes
        $critical_classes = ['MKCG_API_Service', 'Enhanced_Formidable_Service'];
        foreach ($critical_classes as $class) {
            if (!class_exists($class)) {
                error_log("MKCG: FATAL - Critical class {$class} is not available");
                wp_die("Media Kit Content Generator: Critical dependency {$class} failed to load. Please check file permissions and paths.");
            }
        }
        
        // ROOT-LEVEL FIX: Log admin integration status
        if (class_exists('MKCG_Authority_Hook_Test_Admin')) {
            error_log('MKCG: ✅ Authority Hook Test Admin integration loaded successfully');
        } else {
            error_log('MKCG: ⚠️ Authority Hook Test Admin integration not loaded (may be normal in non-admin contexts)');
        }
    }
    
    /**
     * SIMPLIFIED: Basic service initialization with essential services only
     */
    private function init_services() {
        error_log('MKCG: Starting simplified service initialization');
        
        try {
            // 1. API Service (no dependencies)
            if (class_exists('MKCG_API_Service')) {
                $this->api_service = new MKCG_API_Service();
                error_log('MKCG: ✅ API Service initialized');
            } else {
                throw new Exception('MKCG_API_Service class not found');
            }
            
            // 2. Enhanced Formidable Service (no dependencies)
            if (class_exists('Enhanced_Formidable_Service')) {
                $this->formidable_service = new Enhanced_Formidable_Service();
                error_log('MKCG: ✅ Enhanced Formidable Service initialized');
            } else {
                throw new Exception('Enhanced_Formidable_Service class not found');
            }
            
            // Simplified validation
            if (!$this->api_service || !$this->formidable_service) {
                throw new Exception('Core services failed to initialize');
            }
            
            error_log('MKCG: ✅ All simplified services initialized successfully');
            
        } catch (Exception $e) {
            error_log('MKCG: ❌ CRITICAL - Service initialization failed: ' . $e->getMessage());
            
            // Set services to null on failure
            $this->api_service = null;
            $this->formidable_service = null;
        }
    }
    
    // SIMPLIFIED: Basic validation no longer needed with simplified architecture
    
    /**
     * SIMPLIFIED: Basic generator initialization with essential generators only
     */
    private function init_generators() {
        error_log('MKCG: Starting simplified generator initialization');
        
        // Check if services are available
        if (!$this->api_service || !$this->formidable_service) {
            error_log('MKCG: ⚠️ Services not available - skipping generator initialization');
            return;
        }
        
        try {
            // Initialize only the enhanced topics generator
            if (class_exists('Enhanced_Topics_Generator')) {
                $this->generators['topics'] = new Enhanced_Topics_Generator(
                    $this->api_service,
                    $this->formidable_service
                );
                
                error_log('MKCG: ✅ Enhanced Topics Generator initialized successfully');
            } else {
                error_log('MKCG: ❌ Enhanced_Topics_Generator class not found');
            }
            
            error_log('MKCG: ✅ Simplified generator initialization completed - ' . count($this->generators) . ' generators loaded');
            
        } catch (Exception $e) {
            error_log('MKCG: ❌ CRITICAL - Generator initialization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * CRITICAL FIX: Validate generator initialization
     */
    private function validate_generator_initialization() {
        $expected_generators = ['topics', 'questions'];
        $initialized_generators = array_keys($this->generators);
        
        $missing_generators = array_diff($expected_generators, $initialized_generators);
        
        if (!empty($missing_generators)) {
            error_log('MKCG: ⚠️ Missing critical generators: ' . implode(', ', $missing_generators));
        }
        
        foreach ($this->generators as $type => $generator) {
            if (!is_object($generator)) {
                error_log("MKCG: ❌ Generator '{$type}' is not a valid object");
            } else {
                error_log("MKCG: ✅ Generator '{$type}' validated successfully");
            }
        }
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
        
        // SIMPLIFIED: Set required global variables for template
        global $formidable_service, $generator_instance, $generator_type;
        $formidable_service = $this->formidable_service;
        $generator_instance = isset($this->generators['topics']) ? $this->generators['topics'] : null;
        $generator_type = 'topics';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        error_log('MKCG Shortcode: Loading topics template with simplified generator');
        
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
     * Questions Generator Shortcode
     */
    public function questions_shortcode($atts) {
        $atts = shortcode_atts([
            'entry_id' => 0,
            'entry_key' => ''
        ], $atts);
        
        // Force load scripts for shortcode
        $this->enqueue_scripts();
        
        ob_start();
        
        // CRITICAL FIX: Set ALL required global variables for template
        global $formidable_service, $generator_instance, $generator_type;
        $formidable_service = $this->formidable_service;
        $generator_instance = $this->generators['questions'];
        $generator_type = 'questions';
        
        // Also make services available
        global $api_service, $authority_hook_service;
        $api_service = $this->api_service;
        $authority_hook_service = $this->authority_hook_service;
        
        error_log('MKCG Shortcode: Loading questions template with generator_instance available: ' . (is_object($generator_instance) ? 'YES' : 'NO'));
        
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
        
        // Load Simple AJAX Manager
        wp_enqueue_script(
            'simple-ajax-manager',
            MKCG_PLUGIN_URL . 'assets/js/simple_ajax_manager.js',
            ['jquery'],
            MKCG_VERSION,
            true
        );
        
        // Pass data to JavaScript
        wp_localize_script('simple-ajax-manager', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL
        ]);
        
        error_log('MKCG: Simplified script loading completed');
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
    
    /**
     * ROOT-LEVEL FIX: Enhanced admin notices
     */
    public function show_admin_notices() {
        // Only show notices to users who can manage options
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if test script exists and show helpful notice
        $test_script_path = MKCG_PLUGIN_PATH . 'test-authority-hook-fix.php';
        if (file_exists($test_script_path) && isset($_GET['page']) && $_GET['page'] !== 'mkcg-authority-hook-test') {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>Media Kit Content Generator:</strong> Authority Hook test tools are available. ';
            echo '<a href="' . admin_url('tools.php?page=mkcg-authority-hook-test') . '">Access via Tools > Authority Hook Test</a> ';
            echo 'for a better testing experience.</p>';
            echo '</div>';
        }
    }
    
    // SIMPLIFIED: Basic getter methods
    public function get_api_service() {
        return $this->api_service;
    }
    
    public function get_formidable_service() {
        return $this->formidable_service;
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