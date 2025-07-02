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
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-config.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-api-service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/services/enhanced_formidable_service.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_topics_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_questions_generator.php';
        require_once MKCG_PLUGIN_PATH . 'includes/generators/enhanced_ajax_handlers.php';
    }
    
    /**
     * ROOT-LEVEL FIX: Enhanced service initialization with detailed debugging
     */
    private function init_services() {
        error_log('MKCG: Starting ROOT-LEVEL enhanced service initialization');
        
        try {
            // 1. API Service (no dependencies)
            if (class_exists('MKCG_API_Service')) {
                $this->api_service = new MKCG_API_Service();
                $api_class = get_class($this->api_service);
                error_log('MKCG: ✅ API Service initialized as: ' . $api_class);
            } else {
                error_log('MKCG: ❌ MKCG_API_Service class not found - checking available classes');
                $available_classes = get_declared_classes();
                $api_classes = array_filter($available_classes, function($class) {
                    return strpos($class, 'API') !== false;
                });
                error_log('MKCG: Available API-related classes: ' . implode(', ', $api_classes));
                throw new Exception('MKCG_API_Service class not found');
            }
            
            // 2. Enhanced Formidable Service (no dependencies) 
            if (class_exists('Enhanced_Formidable_Service')) {
                $this->formidable_service = new Enhanced_Formidable_Service();
                $formidable_class = get_class($this->formidable_service);
                error_log('MKCG: ✅ Enhanced Formidable Service initialized as: ' . $formidable_class);
                
                // Verify methods exist
                $required_methods = ['save_entry_data', 'get_field_value', 'get_entry_data'];
                foreach ($required_methods as $method) {
                    if (method_exists($this->formidable_service, $method)) {
                        error_log('MKCG: ✅ Method verified: ' . $method);
                    } else {
                        error_log('MKCG: ❌ Missing method: ' . $method);
                    }
                }
            } else {
                error_log('MKCG: ❌ Enhanced_Formidable_Service class not found - checking available classes');
                $available_classes = get_declared_classes();
                $formidable_classes = array_filter($available_classes, function($class) {
                    return strpos(strtolower($class), 'formidable') !== false;
                });
                error_log('MKCG: Available Formidable-related classes: ' . implode(', ', $formidable_classes));
                throw new Exception('Enhanced_Formidable_Service class not found');
            }
            
            // ROOT-LEVEL FIX: Enhanced validation with type checking
            if (!$this->api_service || !$this->formidable_service) {
                throw new Exception('Core services failed to initialize');
            }
            
            if (!is_object($this->api_service) || !is_object($this->formidable_service)) {
                throw new Exception('Services are not valid objects');
            }
            
            error_log('MKCG: ✅ All ROOT-LEVEL enhanced services initialized successfully');
            error_log('MKCG: Final service types - API: ' . get_class($this->api_service) . ', Formidable: ' . get_class($this->formidable_service));
            
        } catch (Exception $e) {
            error_log('MKCG: ❌ CRITICAL - ROOT-LEVEL service initialization failed: ' . $e->getMessage());
            error_log('MKCG: Stack trace: ' . $e->getTraceAsString());
            
            // Set services to null on failure
            $this->api_service = null;
            $this->formidable_service = null;
        }
    }
    
    // SIMPLIFIED: Basic validation no longer needed with simplified architecture
    
    /**
     * ROOT-LEVEL FIX: Enhanced generator initialization with detailed debugging
     */
    private function init_generators() {
        error_log('MKCG: Starting ROOT-LEVEL enhanced generator initialization');
        
        // Check if services are available
        if (!$this->api_service || !$this->formidable_service) {
            error_log('MKCG: ⚠️ Services not available - skipping generator initialization');
            error_log('MKCG: API Service available: ' . ($this->api_service ? 'YES' : 'NO'));
            error_log('MKCG: Formidable Service available: ' . ($this->formidable_service ? 'YES' : 'NO'));
            return;
        }
        
        try {
            // Initialize only the enhanced topics generator
            if (class_exists('Enhanced_Topics_Generator')) {
                error_log('MKCG: ✅ Enhanced_Topics_Generator class found, attempting initialization...');
                
                $this->generators['topics'] = new Enhanced_Topics_Generator(
                    $this->api_service,
                    $this->formidable_service
                );
                
                $generator_class = get_class($this->generators['topics']);
                error_log('MKCG: ✅ Enhanced Topics Generator initialized as: ' . $generator_class);
                
                // Verify generator methods exist
                $required_methods = ['get_template_data', 'generate_topics', 'save_topics'];
                foreach ($required_methods as $method) {
                    if (method_exists($this->generators['topics'], $method)) {
                        error_log('MKCG: ✅ Generator method verified: ' . $method);
                    } else {
                        error_log('MKCG: ❌ Generator missing method: ' . $method);
                    }
                }
                
            } else {
                error_log('MKCG: ❌ Enhanced_Topics_Generator class not found - checking available classes');
                $available_classes = get_declared_classes();
                $generator_classes = array_filter($available_classes, function($class) {
                    return strpos(strtolower($class), 'generator') !== false;
                });
                error_log('MKCG: Available Generator-related classes: ' . implode(', ', $generator_classes));
            }
            
            // Initialize Questions Generator
            if (class_exists('Enhanced_Questions_Generator')) {
                error_log('MKCG: ✅ Enhanced_Questions_Generator class found, attempting initialization...');
                $this->generators['questions'] = new Enhanced_Questions_Generator(
                    $this->api_service,
                    $this->formidable_service
                );
                
                $generator_class = get_class($this->generators['questions']);
                error_log('MKCG: ✅ Enhanced Questions Generator initialized as: ' . $generator_class);
                
                // Verify generator methods exist
                $required_methods = ['get_template_data', 'generate_questions_for_topic', 'save_questions'];
                foreach ($required_methods as $method) {
                    if (method_exists($this->generators['questions'], $method)) {
                        error_log('MKCG: ✅ Questions generator method verified: ' . $method);
                    } else {
                        error_log('MKCG: ❌ Questions generator missing method: ' . $method);
                    }
                }
            } else {
                error_log('MKCG: ❌ Enhanced_Questions_Generator class not found - check file loading');
            }
            
            error_log('MKCG: ✅ ROOT-LEVEL enhanced generator initialization completed - ' . count($this->generators) . ' generators loaded');
            error_log('MKCG: Available generators: ' . implode(', ', array_keys($this->generators)));
            
        } catch (Exception $e) {
            error_log('MKCG: ❌ CRITICAL - ROOT-LEVEL generator initialization failed: ' . $e->getMessage());
            error_log('MKCG: Stack trace: ' . $e->getTraceAsString());
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
        $generator_instance = isset($this->generators['questions']) ? $this->generators['questions'] : null;
        $generator_type = 'questions';
        
        // Also make services available
        global $api_service;
        $api_service = $this->api_service;
        
        // CRITICAL FIX: Get template data using Questions Generator if available
        if ($generator_instance && method_exists($generator_instance, 'get_template_data')) {
            $template_data = $generator_instance->get_template_data($entry_key);
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
        
        // Load Simple Notifications System (loaded first for global availability)
        wp_enqueue_script(
            'simple-notifications',
            MKCG_PLUGIN_URL . 'assets/js/simple-notifications.js',
            [],
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
        
        // Load Simple AJAX System
        wp_enqueue_script(
            'simple-ajax',
            MKCG_PLUGIN_URL . 'assets/js/simple-ajax.js',
            ['jquery'],
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
        
        // Pass data to JavaScript
        wp_localize_script('simple-ajax', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'fields' => [
                'topics' => [
                    'topic_1' => '8498',
                    'topic_2' => '8499', 
                    'topic_3' => '8500',
                    'topic_4' => '8501',
                    'topic_5' => '8502'
                ],
                'authority_hook' => [
                    'who' => '10296',
                    'result' => '10297',
                    'when' => '10387',
                    'how' => '10298',
                    'complete' => '10358'
                ]
            ],
            'ajax_actions' => [
                'save_topics' => 'mkcg_save_topics_data',
                'get_topics' => 'mkcg_get_topics_data',
                'save_authority_hook' => 'mkcg_save_authority_hook',
                'generate_topics' => 'mkcg_generate_topics'
            ]
        ]);
        
        // Make Formidable service available globally for testing
        wp_add_inline_script('simple-ajax', '
            window.MKCG_Formidable_Service = true;
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