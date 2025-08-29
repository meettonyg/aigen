<?php
/**
 * Media Kit Content Generator Asset Manager
 * 
 * Handles conditional loading of plugin assets following event-driven architecture
 * No polling, no timeouts - purely event-based detection and loading
 * 
 * @package MediaKitContentGenerator
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Asset_Manager {
    
    private $plugin_slug = 'media-kit-content-generator';
    private $version;
    private $assets_loaded = false;
    private $plugin_url;
    private $plugin_path;
    private $page_mappings;
    private $current_page_id;
    
    public function __construct($version = '1.0.0', $plugin_url = '', $plugin_path = '') {
        $this->version = $version;
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;
        
        // Define page ID to generator mappings based on page IDs
        $this->page_mappings = [
            46202 => 'tagline',      // Tagline Generator
            46200 => 'guest_intro',  // Guest Intro
            46198 => 'impact_intro', // Impact Intro
            46195 => 'authority_hook', // Authority Hook
            46178 => 'questions',    // Questions
            46176 => 'topics',       // Topics
            46174 => 'offers',       // Offer Generator
            46171 => 'biography',    // Biography Generator
        ];
        
        // Event-driven initialization - no polling
        add_action('wp_enqueue_scripts', array($this, 'conditional_enqueue_assets'), 10);
        add_action('admin_enqueue_scripts', array($this, 'admin_conditional_enqueue'), 10);
        
        // Listen for generator-specific events
        add_action('mkcg_generator_loaded', array($this, 'on_generator_loaded'), 10, 1);
        add_action('mkcg_shortcode_detected', array($this, 'on_shortcode_detected'), 10, 1);
    }
    
    /**
     * Get current page ID from various sources
     */
    private function get_current_page_id() {
        if ($this->current_page_id) {
            return $this->current_page_id;
        }
        
        // Method 1: Direct page ID
        global $post;
        if ($post && is_object($post)) {
            $this->current_page_id = $post->ID;
            error_log('MKCG Asset Manager: Page ID detected via $post: ' . $this->current_page_id);
            return $this->current_page_id;
        }
        
        // Method 2: URL parameter
        if (isset($_GET['page_id'])) {
            $this->current_page_id = intval($_GET['page_id']);
            error_log('MKCG Asset Manager: Page ID detected via URL parameter: ' . $this->current_page_id);
            return $this->current_page_id;
        }
        
        // Method 3: Queried object
        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->ID)) {
            $this->current_page_id = $queried_object->ID;
            error_log('MKCG Asset Manager: Page ID detected via queried object: ' . $this->current_page_id);
            return $this->current_page_id;
        }
        
        error_log('MKCG Asset Manager: No page ID could be determined');
        return 0;
    }
    
    /**
     * Determine generator type based on current page ID
     */
    private function get_generator_type_by_page_id($page_id = null) {
        if ($page_id === null) {
            $page_id = $this->get_current_page_id();
        }
        
        return isset($this->page_mappings[$page_id]) ? $this->page_mappings[$page_id] : null;
    }
    
    /**
     * Check if current page is a generator page
     */
    private function is_generator_page() {
        $page_id = $this->get_current_page_id();
        $is_generator = isset($this->page_mappings[$page_id]);
        
        error_log('MKCG Asset Manager: is_generator_page() - Page ID: ' . $page_id . ', Result: ' . ($is_generator ? 'YES' : 'NO') . ', Available IDs: ' . implode(', ', array_keys($this->page_mappings)));
        
        return $is_generator;
    }
    
    /**
     * Conditional asset loading for frontend
     * ✅ Event-driven initialization - no polling
     * ✅ Root cause detection - multiple detection methods
     */
    public function conditional_enqueue_assets() {
        if ($this->assets_loaded) {
            return; // Prevent duplicate loading
        }
        
        // CRITICAL DEBUG: Log every attempt to load assets
        $current_page_id = $this->get_current_page_id();
        $is_generator_page = $this->is_generator_page();
        $should_load = $this->should_load_assets();
        
        error_log('MKCG Asset Manager DEBUG: Page ID: ' . $current_page_id . ', Is Generator: ' . ($is_generator_page ? 'YES' : 'NO') . ', Should Load: ' . ($should_load ? 'YES' : 'NO'));
        
        if ($should_load) {
            error_log('MKCG Asset Manager: LOADING ASSETS on page ID: ' . $current_page_id);
            $this->enqueue_frontend_assets();
            
            // Load generator-specific assets based on page ID
            $generator_type = $this->get_generator_type_by_page_id();
            if ($generator_type) {
                $this->enqueue_generator_assets($generator_type);
                error_log('MKCG Asset Manager: Loaded page-specific assets for generator: ' . $generator_type . ' (Page ID: ' . $this->get_current_page_id() . ')');
            }
            
            $this->assets_loaded = true;
            
            // Trigger event for other components
            do_action('mkcg_assets_loaded', 'frontend');
        } else {
            error_log('MKCG Asset Manager: SKIPPING ASSETS on page ID: ' . $current_page_id);
        }
    }
    
    /**
     * ✅ WordPress best practices - proper hook usage
     */
    public function admin_conditional_enqueue($hook) {
        $allowed_admin_pages = array(
            'post.php',
            'post-new.php',
            'edit.php',
            'toplevel_page_' . $this->plugin_slug,
            $this->plugin_slug . '_page_settings'
        );
        
        if (in_array($hook, $allowed_admin_pages) || $this->is_plugin_admin_page()) {
            $this->enqueue_admin_assets();
            do_action('mkcg_admin_assets_loaded', $hook);
        }
    }
    
    /**
     * ✅ Event handler - dependency-aware loading
     */
    public function on_generator_loaded($generator_type) {
        if (!$this->assets_loaded) {
            $this->enqueue_frontend_assets();
            $this->assets_loaded = true;
        }
        
        $this->enqueue_generator_assets($generator_type);
    }
    
    /**
     * Event handler for shortcode detection
     */
    public function on_shortcode_detected($shortcode) {
        // Force asset loading when shortcode is detected
        if (!$this->assets_loaded) {
            $this->enqueue_frontend_assets();
            $this->assets_loaded = true;
        }
        
        // Try to determine generator type from shortcode
        $shortcode_mappings = [
            'mkcg_biography' => 'biography',
            'mkcg_topics' => 'topics', 
            'mkcg_questions' => 'questions',
            'mkcg_offers' => 'offers',
            'mkcg_guest_intro' => 'guest_intro',
            'mkcg_tagline' => 'tagline',
            'mkcg_authority_hook' => 'authority_hook',
            'mkcg_impact_intro' => 'impact_intro'
        ];
        
        if (isset($shortcode_mappings[$shortcode])) {
            $this->enqueue_generator_assets($shortcode_mappings[$shortcode]);
        }
        
        error_log('MKCG Asset Manager: Shortcode detected: ' . $shortcode);
    }
    
    /**
     * ✅ Root cause detection - strict page ID checking ONLY
     */
    private function should_load_assets() {
        // Method 1: Page ID detection (PRIMARY and ONLY method for standard page loads)
        if ($this->is_generator_page()) {
            error_log('MKCG Asset Manager: Loading assets for generator page ID: ' . $this->get_current_page_id());
            return true;
        }
        
        // Method 2: AJAX request detection (for generator AJAX calls only)
        if ($this->is_plugin_ajax_request()) {
            error_log('MKCG Asset Manager: Loading assets for AJAX request');
            return true;
        }
        
        // REMOVED: Shortcode detection - was causing assets to load on any page with shortcodes
        // REMOVED: URL parameter detection - was too broad and triggered on unrelated pages
        
        // Log when we decide NOT to load assets
        $current_page_id = $this->get_current_page_id();
        error_log('MKCG Asset Manager: NOT loading assets - Page ID: ' . $current_page_id . ' (not a generator page)');
        
        return false;
    }
    
    private function has_plugin_shortcodes() {
        global $post;
        
        if (!$post || empty($post->post_content)) {
            return false;
        }
        
        // Only check for actual generator shortcodes, not any mention of the plugin
        $shortcodes = array(
            'mkcg_biography',
            'mkcg_topics', 
            'mkcg_questions',
            'mkcg_offers',
            'mkcg_guest_intro',
            'mkcg_tagline',
            'mkcg_authority_hook',
            'mkcg_impact_intro'
        );
        
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                do_action('mkcg_shortcode_detected', $shortcode);
                error_log('MKCG Asset Manager: Found shortcode: ' . $shortcode . ' on page ID: ' . $post->ID);
                return true;
            }
        }
        
        return false;
    }
    
    private function has_specific_generator_parameters() {
        // Only check for very specific generator parameters that indicate we're actually on a generator page
        $specific_generator_params = array('mkcg_generator', 'generator_type', 'mkcg_action');
        
        foreach ($specific_generator_params as $param) {
            if (isset($_GET[$param])) {
                return true;
            }
        }
        
        // Check for Formidable edit pages with our entries
        if (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit' && isset($_GET['entry'])) {
            return true;
        }
        
        return false;
    }
    
    private function is_plugin_ajax_request() {
        if (!wp_doing_ajax()) {
            return false;
        }
        
        $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
        return strpos($action, 'mkcg_') === 0;
    }
    
    private function is_plugin_admin_page() {
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }
        
        return strpos($screen->id, 'media-kit') !== false || 
               strpos($screen->id, 'mkcg') !== false;
    }
    
    /**
     * ✅ Correct enqueuing with proper dependencies for PUBLIC FRONTEND
     * UPDATED: Loads general styles and scripts that should be on all generator pages
     */
    private function enqueue_frontend_assets() {
        // ✅ General CSS for ALL generator pages
        wp_enqueue_style(
            'mkcg-unified-styles',
            $this->plugin_url . 'assets/css/mkcg-unified-styles.css',
            array(),
            $this->version,
            'all'
        );
        
        // Cross-browser fixes CSS
        wp_enqueue_style(
            'mkcg-cross-browser-fixes',
            $this->plugin_url . 'assets/css/cross-browser-fixes.css',
            array('mkcg-unified-styles'),
            $this->version,
            'all'
        );
        
        // ✅ General JavaScript files that should load on ALL generator pages
        wp_enqueue_script(
            'simple-event-bus',
            $this->plugin_url . 'assets/js/simple-event-bus.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'simple-notifications',
            $this->plugin_url . 'assets/js/simple-notifications.js',
            array('simple-event-bus'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'mkcg-simple-ajax',
            $this->plugin_url . 'assets/js/simple-ajax.js',
            array('simple-notifications'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'mkcg-form-utils',
            $this->plugin_url . 'assets/js/mkcg-form-utils.js',
            array('mkcg-simple-ajax'),
            $this->version,
            true
        );
        
        // Cross-browser compatibility scripts
        wp_enqueue_script(
            'cross-browser-compatibility',
            $this->plugin_url . 'assets/js/cross-browser-compatibility.js',
            array('mkcg-form-utils'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'cross-browser-fixes',
            $this->plugin_url . 'assets/js/cross-browser-fixes.js',
            array('cross-browser-compatibility'),
            $this->version,
            true
        );
        
        wp_enqueue_script(
            'enhanced-ui-feedback',
            $this->plugin_url . 'assets/js/enhanced-ui-feedback.js',
            array('cross-browser-fixes'),
            $this->version,
            true
        );
        
        // ✅ Localize with proper handle
        wp_localize_script('mkcg-simple-ajax', 'mkcg_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
        ));
        
        error_log('MKCG Asset Manager: General frontend assets loaded for all generator pages');
    }
    
    private function enqueue_admin_assets() {
        wp_enqueue_style(
            'mkcg-admin-styles',
            $this->plugin_url . 'assets/css/admin-styles.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_script(
            'mkcg-admin-scripts',
            $this->plugin_url . 'assets/js/admin/admin-core.js',
            array('jquery'),
            $this->version,
            true
        );
    }
    
    /**
     * ✅ Generator-specific asset loading with proper dependencies
     * UPDATED: Loads specific styles and scripts only for matching generator pages
     */
    private function enqueue_generator_assets($generator_type) {
        // Generator-specific CSS files
        $generator_styles = array(
            'biography' => [
                'generators/biography-generator.css'
            ],
            'topics' => [
                'generators/topics-generator.css'
            ],
            'questions' => [
                'generators/questions-generator.css'
            ],
            'offers' => [
                'generators/offers-generator.css'
            ],
            'guest_intro' => [
                'generators/guest-intro-generator.css'
            ],
            'tagline' => [
                'generators/tagline-generator.css'
            ],
            'authority_hook' => [
                'generators/authority-hook-generator.css'
            ],
            'impact_intro' => [
                'generators/impact-intro-generator.css'
            ]
        );
        
        // Generator-specific JavaScript files
        $generator_scripts = array(
            'biography' => [
                'biography-generator.js',
                'biography-results.js'
            ],
            'topics' => [
                'topics-generator.js'
            ],
            'questions' => [
                'questions-generator.js'
            ],
            'offers' => [
                'offers-generator.js'
            ],
            'guest_intro' => [
                'guest-intro-generator.js'
            ],
            'tagline' => [
                'tagline-generator.js'
            ],
            'authority_hook' => [
                'authority-hook-generator.js'
            ],
            'impact_intro' => [
                'impact-intro-generator.js'
            ]
        );
        
        // Enqueue generator-specific CSS
        if (isset($generator_styles[$generator_type])) {
            foreach ($generator_styles[$generator_type] as $style_file) {
                $style_name = str_replace('.css', '', basename($style_file));
                $style_handle = $style_name . '-css';
                
                // Check if the CSS file exists before enqueueing
                $css_path = $this->plugin_path . 'assets/css/' . $style_file;
                if (file_exists($css_path)) {
                    wp_enqueue_style(
                        $style_handle,
                        $this->plugin_url . 'assets/css/' . $style_file,
                        array('mkcg-unified-styles', 'mkcg-cross-browser-fixes'),
                        $this->version,
                        'all'
                    );
                    
                    error_log('MKCG Asset Manager: Loaded ' . $style_file . ' CSS for ' . $generator_type . ' generator (Page ID: ' . $this->get_current_page_id() . ')');
                } else {
                    error_log('MKCG Asset Manager: CSS file not found: ' . $css_path);
                }
            }
        }
        
        // Enqueue generator-specific JavaScript
        if (isset($generator_scripts[$generator_type])) {
            $base_dependencies = array('enhanced-ui-feedback', 'mkcg-simple-ajax', 'mkcg-form-utils', 'simple-notifications');
            
            foreach ($generator_scripts[$generator_type] as $script_file) {
                $script_name = str_replace('.js', '', $script_file);
                $script_handle = $script_name . '-js';
                
                wp_enqueue_script(
                    $script_handle,
                    $this->plugin_url . 'assets/js/generators/' . $script_file,
                    $base_dependencies,
                    $this->version,
                    true
                );
                
                error_log('MKCG Asset Manager: Loaded ' . $script_file . ' for ' . $generator_type . ' generator (Page ID: ' . $this->get_current_page_id() . ')');
            }
            
            // ROOT FIX: Authority Hook Builder is needed by MULTIPLE generators
            // Topics, Questions, Offers, Biography all use the Authority Hook Builder
            $generators_needing_authority_hook = ['topics', 'questions', 'offers', 'biography', 'authority_hook'];
            
            if (in_array($generator_type, $generators_needing_authority_hook)) {
                wp_enqueue_script(
                    'authority-hook-builder-js',
                    $this->plugin_url . 'assets/js/authority-hook-builder.js',
                    $base_dependencies,
                    $this->version,
                    true
                );
                error_log('MKCG Asset Manager: ROOT FIX - Loaded authority-hook-builder.js for ' . $generator_type . ' generator');
            }
            
            do_action('mkcg_generator_assets_loaded', $generator_type);
        }
    }
    
    public function force_load_assets() {
        if (!$this->assets_loaded) {
            $this->enqueue_frontend_assets();
            $this->assets_loaded = true;
        }
    }
    
    public function are_assets_loaded() {
        return $this->assets_loaded;
    }
    
    /**
     * Get debug information about current asset loading
     */
    public function get_debug_info() {
        $page_id = $this->get_current_page_id();
        $generator_type = $this->get_generator_type_by_page_id($page_id);
        
        return [
            'current_page_id' => $page_id,
            'generator_type' => $generator_type,
            'is_generator_page' => $this->is_generator_page(),
            'assets_loaded' => $this->assets_loaded,
            'page_mappings' => $this->page_mappings,
            'should_load_assets' => $this->should_load_assets()
        ];
    }
    
    /**
     * Get list of enqueued MKCG scripts and styles for debugging
     */
    public function get_enqueued_scripts() {
        global $wp_scripts, $wp_styles;
        $mkcg_assets = ['scripts' => [], 'styles' => []];
        
        // Get scripts
        if (isset($wp_scripts->registered)) {
            foreach ($wp_scripts->registered as $handle => $script) {
                if (strpos($handle, 'mkcg-') === 0 || 
                    strpos($handle, 'simple-') === 0 ||
                    strpos($handle, 'cross-browser') === 0 ||
                    strpos($handle, 'enhanced-') === 0 ||
                    strpos($script->src, 'media-kit-content-generator') !== false) {
                    $mkcg_assets['scripts'][$handle] = [
                        'src' => $script->src,
                        'deps' => $script->deps,
                        'enqueued' => wp_script_is($handle, 'enqueued')
                    ];
                }
            }
        }
        
        // Get styles
        if (isset($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                if (strpos($handle, 'mkcg-') === 0 || 
                    strpos($handle, 'simple-') === 0 ||
                    strpos($handle, 'cross-browser') === 0 ||
                    strpos($handle, 'enhanced-') === 0 ||
                    strpos($style->src, 'media-kit-content-generator') !== false) {
                    $mkcg_assets['styles'][$handle] = [
                        'src' => $style->src,
                        'deps' => $style->deps,
                        'enqueued' => wp_style_is($handle, 'enqueued')
                    ];
                }
            }
        }
        
        return $mkcg_assets;
    }
}