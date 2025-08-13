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
    
    public function __construct($version = '1.0.0', $plugin_url = '', $plugin_path = '') {
        $this->version = $version;
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;
        
        // Event-driven initialization - no polling
        add_action('wp_enqueue_scripts', array($this, 'conditional_enqueue_assets'), 10);
        add_action('admin_enqueue_scripts', array($this, 'admin_conditional_enqueue'), 10);
        
        // Listen for generator-specific events
        add_action('mkcg_generator_loaded', array($this, 'on_generator_loaded'), 10, 1);
        add_action('mkcg_shortcode_detected', array($this, 'on_shortcode_detected'), 10, 1);
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
        
        if ($this->should_load_assets()) {
            $this->enqueue_frontend_assets();
            $this->assets_loaded = true;
            
            // Trigger event for other components
            do_action('mkcg_assets_loaded', 'frontend');
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
     * ✅ Root cause detection - no global object sniffing
     */
    private function should_load_assets() {
        // Method 1: Shortcode detection
        if ($this->has_plugin_shortcodes()) {
            return true;
        }
        
        // Method 2: URL parameter detection
        if ($this->has_generator_parameters()) {
            return true;
        }
        
        // Method 3: Template detection
        if ($this->is_plugin_template()) {
            return true;
        }
        
        // Method 4: AJAX request detection
        if ($this->is_plugin_ajax_request()) {
            return true;
        }
        
        return false;
    }
    
    private function has_plugin_shortcodes() {
        global $post;
        
        if (!$post || empty($post->post_content)) {
            return false;
        }
        
        $shortcodes = array(
            'mkcg_biography',
            'mkcg_topics', 
            'mkcg_questions',
            'mkcg_offers',
            'mkcg_guest_intro',
            'mkcg_tagline'
        );
        
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                do_action('mkcg_shortcode_detected', $shortcode);
                return true;
            }
        }
        
        return false;
    }
    
    private function has_generator_parameters() {
        $generator_params = array('post_id', 'entry', 'generator_type', 'mkcg_generator');
        
        foreach ($generator_params as $param) {
            if (isset($_GET[$param])) {
                return true;
            }
        }
        
        return false;
    }
    
    private function is_plugin_template() {
        $template = get_page_template_slug();
        return strpos($template, 'media-kit') !== false || strpos($template, 'mkcg') !== false;
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
     * FIXED: Uses actual JavaScript files that exist
     */
    private function enqueue_frontend_assets() {
        // ✅ CSS for ALL USERS (public-facing pages)
        wp_enqueue_style(
            'mkcg-unified-styles',
            $this->plugin_url . 'assets/css/mkcg-unified-styles.css',
            array(),
            $this->version,
            'all'
        );
        
        // ✅ FIXED: Use actual JavaScript files that exist
        wp_enqueue_script(
            'mkcg-simple-ajax',
            $this->plugin_url . 'assets/js/simple-ajax.js',
            array('jquery'),
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
        
        wp_enqueue_script(
            'mkcg-notifications',
            $this->plugin_url . 'assets/js/simple-notifications.js',
            array('mkcg-form-utils'),
            $this->version,
            true
        );
        
        // ✅ Localize with proper handle
        wp_localize_script('mkcg-simple-ajax', 'mkcg_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
        ));
        
        error_log('MKCG Asset Manager: Frontend assets loaded for PUBLIC users');
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
     * FIXED: Uses correct dependency chain with actual scripts
     */
    private function enqueue_generator_assets($generator_type) {
        $generator_scripts = array(
            'biography' => 'biography-generator.js',
            'topics' => 'topics-generator.js',
            'questions' => 'questions-generator.js',
            'offers' => 'offers-generator.js',
            'guest_intro' => 'guest-intro-generator.js',
            'tagline' => 'tagline-generator.js'
        );
        
        if (isset($generator_scripts[$generator_type])) {
            $script_handle = 'mkcg-' . $generator_type . '-generator';
            
            wp_enqueue_script(
                $script_handle,
                $this->plugin_url . 'assets/js/generators/' . $generator_scripts[$generator_type],
                array('mkcg-simple-ajax', 'mkcg-form-utils', 'mkcg-notifications'), // ✅ FIXED dependency chain
                $this->version,
                true
            );
            
            error_log('MKCG Asset Manager: Loaded ' . $generator_type . ' generator assets for PUBLIC users');
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
}