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
        // Enhanced dependency loading with error checking and logging
        $required_files = [
            // Core services first
            'includes/services/class-mkcg-config.php' => 'MKCG_Config',
            'includes/services/class-mkcg-api-service.php' => 'MKCG_API_Service',
            'includes/services/class-mkcg-formidable-service.php' => 'MKCG_Formidable_Service',
            'includes/services/class-mkcg-authority-hook-service.php' => 'MKCG_Authority_Hook_Service',
            
            // CRITICAL: Topics Data Service MUST be loaded before generators
            'includes/services/class-mkcg-topics-data-service.php' => 'MKCG_Topics_Data_Service',
            'includes/services/class-mkcg-unified-data-service.php' => 'MKCG_Unified_Data_Service',
            
            // Base generator
            'includes/generators/class-mkcg-base-generator.php' => 'MKCG_Base_Generator',
            
            // Specific generators (Topics Data Service dependency already loaded)
            'includes/generators/class-mkcg-biography-generator.php' => 'MKCG_Biography_Generator',
            'includes/generators/class-mkcg-offers-generator.php' => 'MKCG_Offers_Generator',
            'includes/generators/class-mkcg-topics-generator.php' => 'MKCG_Topics_Generator',
            'includes/generators/class-mkcg-questions-generator.php' => 'MKCG_Questions_Generator',
            
            // AJAX handlers
            'includes/generators/class-mkcg-topics-ajax-handlers.php' => 'MKCG_Topics_AJAX_Handlers'
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
        $critical_classes = ['MKCG_Topics_Data_Service', 'MKCG_API_Service', 'MKCG_Formidable_Service'];
        foreach ($critical_classes as $class) {
            if (!class_exists($class)) {
                error_log("MKCG: FATAL - Critical class {$class} is not available");
                wp_die("Media Kit Content Generator: Critical dependency {$class} failed to load. Please check file permissions and paths.");
            }
        }
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
        
        // Initialize AJAX handlers for Topics generator
        if (isset($this->generators['topics'])) {
            new MKCG_Topics_AJAX_Handlers($this->generators['topics']);
        }
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
        
        // CRITICAL FIX: Set ALL required global variables for template
        global $formidable_service, $generator_instance, $generator_type;
        $formidable_service = $this->formidable_service;
        $generator_instance = $this->generators['topics'];
        $generator_type = 'topics';
        
        // Also make services available
        global $api_service, $authority_hook_service;
        $api_service = $this->api_service;
        $authority_hook_service = $this->authority_hook_service;
        
        error_log('MKCG Shortcode: Loading topics template with generator_instance available: ' . (is_object($generator_instance) ? 'YES' : 'NO'));
        
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
        // Always load CSS and core shared scripts
        $css_file = MKCG_PLUGIN_PATH . 'assets/css/mkcg-unified-styles.css';
        
        // Debug: Check if CSS file exists
        if (!file_exists($css_file)) {
            error_log('MKCG: CSS file not found at: ' . $css_file);
        }
        
        // Enqueue unified CSS with high priority and proper cache busting
        wp_enqueue_style(
            'mkcg-unified-styles', 
            MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css', 
            [], 
            MKCG_VERSION . '_' . filemtime($css_file), // Cache busting with file modification time
            'all'
        );
        
        // Add CSS loading debugging
        wp_add_inline_style('mkcg-unified-styles', '
        /* MKCG CSS Loaded Successfully - ' . date('Y-m-d H:i:s') . ' */
        .mkcg-css-test { color: #1a9bdc; }
        ');
        
        // Force CSS to load with higher priority
        add_action('wp_head', function() {
            echo '<link rel="stylesheet" id="mkcg-force-css" href="' . MKCG_PLUGIN_URL . 'assets/css/mkcg-unified-styles.css?v=' . MKCG_VERSION . '" type="text/css" media="all" />' . "\n";
        }, 1);
        
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        
        // CRITICAL FIX: Detect which generator is being used
        $current_generator = $this->detect_current_generator();
        
        error_log("MKCG Script Loading: Detected generator type: " . ($current_generator ?: 'none'));
        
        // Always load core shared scripts
        wp_enqueue_script(
            'mkcg-data-manager', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-data-manager.js', 
            [], // No dependencies - pure vanilla JS
            MKCG_VERSION, 
            true
        );
        
        // CONDITIONAL LOADING: Only load scripts for the detected generator
        switch ($current_generator) {
            case 'topics':
                $this->enqueue_topics_scripts();
                break;
                
            case 'questions':
                $this->enqueue_questions_scripts();
                break;
                
            case 'biography':
                $this->enqueue_biography_scripts();
                break;
                
            case 'offers':
                $this->enqueue_offers_scripts();
                break;
                
            default:
                // No specific generator detected - load minimal shared scripts only
                error_log('MKCG Script Loading: No specific generator detected, loading shared scripts only');
                wp_enqueue_script(
                    'mkcg-form-utils', 
                    MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
                    ['jquery', 'mkcg-data-manager'], 
                    MKCG_VERSION, 
                    true
                );
                break;
        }
        
        // Pass base data to JavaScript (always needed)
        wp_localize_script('mkcg-data-manager', 'mkcg_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'current_generator' => $current_generator
        ]);
        
        error_log('MKCG: Conditional script loading completed for generator: ' . ($current_generator ?: 'none'));
    }
    
    /**
     * CRITICAL FIX: More precise generator detection logic (Gemini's approach)
     */
    private function detect_current_generator() {
        global $post;
        
        // Enhanced debugging
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        error_log('MKCG Precise Generator Detection:');
        error_log('- REQUEST_URI: ' . $request_uri);
        
        // Method 1: Check for a specific query parameter first (most reliable)
        if (isset($_GET['generator'])) {
            $generator = sanitize_text_field($_GET['generator']);
            error_log('- Found generator parameter: ' . $generator);
            if (in_array($generator, ['topics', 'questions', 'biography', 'offers'])) {
                error_log('- MATCHED: URL generator parameter');
                return $generator;
            }
        }

        // Method 2: Check for shortcodes in the current post content
        if ($post && !empty($post->post_content)) {
            error_log('- Checking shortcodes in post content...');
            // Check in order of specificity
            if (has_shortcode($post->post_content, 'mkcg_questions')) {
                error_log('- MATCHED: mkcg_questions shortcode');
                return 'questions';
            }
            if (has_shortcode($post->post_content, 'mkcg_topics')) {
                error_log('- MATCHED: mkcg_topics shortcode');
                return 'topics';
            }
            if (has_shortcode($post->post_content, 'mkcg_biography')) {
                error_log('- MATCHED: mkcg_biography shortcode');
                return 'biography';
            }
            if (has_shortcode($post->post_content, 'mkcg_offers')) {
                error_log('- MATCHED: mkcg_offers shortcode');
                return 'offers';
            }
        }

        // Method 3: Check the URL Path with precise regex patterns
        if ($request_uri) {
            $uri_lower = strtolower($request_uri);
            error_log('- Checking precise URL patterns in: ' . $uri_lower);
            
            // Check for '/questions' but not if it's part of another word
            if (preg_match('/\/questions(\/|\?|$)/', $uri_lower)) {
                error_log('- MATCHED: Questions URL pattern');
                return 'questions';
            }
            
            // Check for '/topics' but not if it's part of another word
            if (preg_match('/\/topics(\/|\?|$)/', $uri_lower)) {
                error_log('- MATCHED: Topics URL pattern');
                return 'topics';
            }
            
            // Check for biography patterns
            if (preg_match('/\/biography(\/|\?|$)/', $uri_lower)) {
                error_log('- MATCHED: Biography URL pattern');
                return 'biography';
            }
            
            // Check for offers patterns
            if (preg_match('/\/offers(\/|\?|$)/', $uri_lower)) {
                error_log('- MATCHED: Offers URL pattern');
                return 'offers';
            }
        }

        // Method 4: Fallback content analysis (least reliable)
        if ($post) {
            $content_to_check = strtolower($post->post_title . ' ' . $post->post_name);
            error_log('- Fallback: checking title and slug patterns...');
            
            if (strpos($content_to_check, 'questions') !== false && strpos($content_to_check, 'interview') !== false) {
                error_log('- FALLBACK MATCHED: Questions in title/slug');
                return 'questions';
            }
            if (strpos($content_to_check, 'topics') !== false && strpos($content_to_check, 'interview') !== false) {
                error_log('- FALLBACK MATCHED: Topics in title/slug');
                return 'topics';
            }
        }
        
        error_log('- RESULT: No specific generator detected');
        return null; // No specific generator detected
    }
    
    /**
     * Load Topics Generator specific scripts
     */
    private function enqueue_topics_scripts() {
        error_log('MKCG: Loading Topics Generator scripts');
        
        // Topics needs Authority Hook Builder and Form Utils
        wp_enqueue_script(
            'mkcg-authority-hook-builder', 
            MKCG_PLUGIN_URL . 'assets/js/authority-hook-builder.js', 
            [], 
            MKCG_VERSION, 
            true
        );
        
        wp_enqueue_script(
            'mkcg-form-utils', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
            ['jquery', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // ONLY load Topics Generator script
        wp_enqueue_script(
            'mkcg-topics-generator', 
            MKCG_PLUGIN_URL . 'assets/js/generators/topics-generator.js', 
            ['mkcg-authority-hook-builder', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // Topics-specific data
        wp_localize_script('mkcg-topics-generator', 'topics_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'topics_nonce' => wp_create_nonce('mkcg_nonce'),
            'save_nonce' => wp_create_nonce('mkcg_save_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'entry_id' => $this->get_current_entry_id(),
            'entry_key' => $this->get_current_entry_key(),
            'field_mappings' => [
                'authority_hook' => [
                    'who' => 10296,
                    'result' => 10297,
                    'when' => 10387,
                    'how' => 10298,
                    'complete' => 10358
                ],
                'topics' => [
                    'topic_1' => 8498,
                    'topic_2' => 8499,
                    'topic_3' => 8500,
                    'topic_4' => 8501,
                    'topic_5' => 8502
                ]
            ]
        ]);
    }
    
    /**
     * Load Questions Generator specific scripts
     */
    private function enqueue_questions_scripts() {
        error_log('MKCG: Loading Questions Generator scripts');
        
        // Questions needs Form Utils (for enhanced FormUtils)
        wp_enqueue_script(
            'mkcg-form-utils', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
            ['jquery', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // ONLY load Questions Generator script
        wp_enqueue_script(
            'mkcg-questions-generator', 
            MKCG_PLUGIN_URL . 'assets/js/generators/questions-generator.js', 
            ['mkcg-form-utils', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // Questions-specific data
        wp_localize_script('mkcg-questions-generator', 'questions_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'topics_nonce' => wp_create_nonce('mkcg_nonce'),
            'save_nonce' => wp_create_nonce('mkcg_save_nonce'),
            'plugin_url' => MKCG_PLUGIN_URL,
            'entry_id' => $this->get_current_entry_id(),
            'entry_key' => $this->get_current_entry_key(),
            'field_mappings' => [
                'topics' => [
                    'topic_1' => 8498,
                    'topic_2' => 8499,
                    'topic_3' => 8500,
                    'topic_4' => 8501,
                    'topic_5' => 8502
                ],
                'questions' => [
                    'topic_1' => ['8505', '8506', '8507', '8508', '8509'],
                    'topic_2' => ['8510', '8511', '8512', '8513', '8514'],
                    'topic_3' => ['10370', '10371', '10372', '10373', '10374'],
                    'topic_4' => ['10375', '10376', '10377', '10378', '10379'],
                    'topic_5' => ['10380', '10381', '10382', '10383', '10384']
                ]
            ]
        ]);
    }
    
    /**
     * Load Biography Generator specific scripts (placeholder)
     */
    private function enqueue_biography_scripts() {
        error_log('MKCG: Loading Biography Generator scripts (placeholder)');
        
        wp_enqueue_script(
            'mkcg-form-utils', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
            ['jquery', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // Biography generator script would go here when implemented
    }
    
    /**
     * Load Offers Generator specific scripts (placeholder)
     */
    private function enqueue_offers_scripts() {
        error_log('MKCG: Loading Offers Generator scripts (placeholder)');
        
        wp_enqueue_script(
            'mkcg-form-utils', 
            MKCG_PLUGIN_URL . 'assets/js/mkcg-form-utils.js', 
            ['jquery', 'mkcg-data-manager'], 
            MKCG_VERSION, 
            true
        );
        
        // Offers generator script would go here when implemented
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
    
    /**
     * Get current entry ID from URL parameters
     */
    public function get_current_entry_id() {
        if (isset($_GET['entry'])) {
            $entry_key = sanitize_text_field($_GET['entry']);
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            
            if ($entry_data['success']) {
                return $entry_data['entry_id'];
            }
        }
        
        return 0;
    }
    
    /**
     * Get current entry key from URL parameters
     */
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