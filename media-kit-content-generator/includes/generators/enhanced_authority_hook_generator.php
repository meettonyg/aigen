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
        
        error_log('MKCG Authority Hook Generator: Data loaded successfully from Authority Hook Service');
        
        return $template_data;
    }
    
    /**
     * Get post_id from request parameters
     */
    private function get_post_id_from_request() {
        // Method 1: post_id parameter
        if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            return intval($_GET['post_id']);
        }
        
        // Method 2: entry parameter (Formidable compatibility)
        if (isset($_GET['entry']) && intval($_GET['entry']) > 0) {
            return intval($_GET['entry']);
        }
        
        // Method 3: Check if we're on a specific guest post page
        if (is_singular('guests') && get_the_ID()) {
            return get_the_ID();
        }
        
        return 0;
    }
    
    /**
     * Get default template data structure
     */
    private function get_default_template_data() {
        return [
            'post_id' => 0,
            'has_data' => false,
            'authority_hook_components' => [
                'who' => '',
                'what' => '',
                'when' => '',
                'how' => ''
            ],
            'complete_hook' => ''
        ];
    }
    
    /**
     * Handle save Authority Hook AJAX request
     */
    public function handle_save_authority_hook() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            wp_send_json_error(['message' => 'Invalid post type - must be guests post']);
            return;
        }
        
        // Collect Authority Hook components
        $components = [
            'who' => sanitize_text_field($_POST['who'] ?? ''),
            'what' => sanitize_text_field($_POST['what'] ?? ''),
            'when' => sanitize_text_field($_POST['when'] ?? ''),
            'how' => sanitize_text_field($_POST['how'] ?? '')
        ];
        
        // Save using Authority Hook Service
        $result = $this->authority_hook_service->save_authority_hook_data($post_id, $components);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Authority Hook saved successfully',
                'post_id' => $post_id,
                'components' => $result['components']
            ]);
        } else {
            wp_send_json_error([
                'message' => $result['message'] ?? 'Save failed',
                'post_id' => $post_id
            ]);
        }
    }
    
    /**
     * Handle get Authority Hook AJAX request
     */
    public function handle_get_authority_hook() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = intval($_GET['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID required']);
            return;
        }
        
        // Get Authority Hook data using service
        $authority_hook_data = $this->authority_hook_service->get_authority_hook_data($post_id);
        
        wp_send_json_success($authority_hook_data);
    }
    
    /**
     * Validate Authority Hook components
     */
    private function validate_components($components) {
        $errors = [];
        
        if (empty($components['who'])) {
            $errors[] = 'WHO field is required';
        }
        
        if (empty($components['what'])) {
            $errors[] = 'WHAT field is required';
        }
        
        if (empty($components['when'])) {
            $errors[] = 'WHEN field is required';
        }
        
        if (empty($components['how'])) {
            $errors[] = 'HOW field is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get Authority Hook Service instance
     */
    public function get_authority_hook_service() {
        return $this->authority_hook_service;
    }
    
    /**
     * Get Pods Service instance
     */
    public function get_pods_service() {
        return $this->pods_service;
    }
}
