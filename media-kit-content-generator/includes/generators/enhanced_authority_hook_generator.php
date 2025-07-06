<?php
/**
 * Enhanced Authority Hook Generator - Dedicated Authority Hook Page
 * Single responsibility: Manage Authority Hook creation and editing using centralized Authority Hook Service
 * Uses: Dynamic post_id from query string, Pods service for data persistence
 */

class Enhanced_Authority_Hook_Generator {
    
    private $authority_hook_service;
    private $pods_service;
    
    /**
     * Constructor - Pure Authority Hook service integration
     */
    public function __construct() {
        // Initialize Authority Hook Service
        if (!isset($GLOBALS['authority_hook_service'])) {
            require_once dirname(__FILE__) . '/../services/class-mkcg-authority-hook-service.php';
            $GLOBALS['authority_hook_service'] = new MKCG_Authority_Hook_Service();
        }
        $this->authority_hook_service = $GLOBALS['authority_hook_service'];
        
        // Initialize Pods service
        require_once dirname(__FILE__) . '/../services/class-mkcg-pods-service.php';
        $this->pods_service = new MKCG_Pods_Service();
        
        $this->init();
    }
    
    /**
     * Initialize - direct and simple
     */
    public function init() {
        // Add any WordPress hooks needed
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue required scripts
     */
    public function enqueue_scripts() {
        // Only enqueue on authority hook generator pages
        if (!$this->is_authority_hook_page()) {
            return;
        }
        
        wp_enqueue_script(
            'enhanced-authority-hook-generator',
            plugins_url('assets/js/generators/authority-hook-generator.js', dirname(dirname(__FILE__))),
            ['jquery', 'simple-event-bus', 'simple-ajax', 'authority-hook-builder'],
            '1.0.0',
            true
        );
        
        wp_localize_script('enhanced-authority-hook-generator', 'authorityHookVars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce')
        ]);
    }
    
    /**
     * Check if current page is authority hook generator
     */
    private function is_authority_hook_page() {
        // Simple check - customize as needed
        return is_page() && (strpos(get_post()->post_content, '[mkcg_authority_hook]') !== false);
    }
    
    /**
     * Get template data using dynamic post_id - Pure Authority Hook integration
     */
    public function get_template_data($post_id = null) {
        error_log('MKCG Authority Hook Generator: Starting get_template_data - Pure Authority Hook Integration');
        
        // Get post_id from request parameters or use provided one
        if (!$post_id) {
            $post_id = $this->get_post_id_from_request();
        }
        
        if (!$post_id) {
            error_log('MKCG Authority Hook Generator: No valid post ID found');
            return $this->get_default_template_data();
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            error_log('MKCG Authority Hook Generator: Post ' . $post_id . ' is not a guests post type');
            return $this->get_default_template_data();
        }
        
        error_log('MKCG Authority Hook Generator: Loading data for guests post ID: ' . $post_id);
        
        // Load Authority Hook data from service
        $authority_hook_data = $this->authority_hook_service->get_authority_hook_data($post_id);
        
        // Transform to template format
        $template_data = [
            'post_id' => $post_id,
            'has_data' => $authority_hook_data['has_data'],
            'authority_hook_components' => $authority_hook_data['components'],
            'complete_hook' => $authority_hook_data['complete_hook']
        ];
        
        error_log('MKCG Authority Hook Generator: Data loaded successfully from Authority