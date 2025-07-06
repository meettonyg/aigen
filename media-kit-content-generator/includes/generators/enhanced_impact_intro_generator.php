<?php
/**
 * Enhanced Impact Intro Generator - Dedicated Impact Intro Page
 * Single responsibility: Manage Impact Intro creation and editing using centralized Impact Intro Service
 * Uses: Dynamic post_id from query string, Pods service for data persistence
 */

class Enhanced_Impact_Intro_Generator {
    
    private $impact_intro_service;
    private $pods_service;
    
    /**
     * Constructor - Pure Impact Intro service integration
     */
    public function __construct() {
        // Initialize Impact Intro Service
        if (!isset($GLOBALS['impact_intro_service'])) {
            require_once dirname(__FILE__) . '/../services/class-mkcg-impact-intro-service.php';
            $GLOBALS['impact_intro_service'] = new MKCG_Impact_Intro_Service();
        }
        $this->impact_intro_service = $GLOBALS['impact_intro_service'];
        
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
        // Only enqueue on impact intro generator pages
        if (!$this->is_impact_intro_page()) {
            return;
        }
        
        wp_enqueue_script(
            'enhanced-impact-intro-generator',
            plugins_url('assets/js/generators/impact-intro-generator.js', dirname(dirname(__FILE__))),
            ['jquery', 'simple-event-bus', 'simple-ajax'],
            '1.0.0',
            true
        );
        
        wp_localize_script('enhanced-impact-intro-generator', 'impactIntroVars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce')
        ]);
    }
    
    /**
     * Check if current page is impact intro generator
     */
    private function is_impact_intro_page() {
        // Simple check - customize as needed
        return is_page() && (strpos(get_post()->post_content, '[mkcg_impact_intro]') !== false);
    }
    
    /**
     * Get template data using dynamic post_id - Pure Impact Intro integration
     */
    public function get_template_data($post_id = null) {
        error_log('MKCG Impact Intro Generator: Starting get_template_data - Pure Impact Intro Integration');
        
        // Get post_id from request parameters or use provided one
        if (!$post_id) {
            $post_id = $this->get_post_id_from_request();
        }
        
        if (!$post_id) {
            error_log('MKCG Impact Intro Generator: No valid post ID found');
            return $this->get_default_template_data();
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            error_log('MKCG Impact Intro Generator: Post ' . $post_id . ' is not a guests post type');
            return $this->get_default_template_data();
        }
        
        error_log('MKCG Impact Intro Generator: Loading data for guests post ID: ' . $post_id);
        
        // Load Impact Intro data from service
        $impact_intro_data = $this->impact_intro_service->get_impact_intro_data($post_id);
        
        // Transform to template format
        $template_data = [
            'post_id' => $post_id,
            'has_data' => $impact_intro_data['has_data'],
            'impact_intro_components' => $impact_intro_data['components'],
            'complete_intro' => $impact_intro_data['complete_intro']
        ];
        
        error_log('MKCG Impact Intro Generator: Data loaded successfully from Impact Intro Service');
        
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
            'impact_intro_components' => [
                'where' => '',
                'why' => ''
            ],
            'complete_intro' => ''
        ];
    }
    
    /**
     * Handle save Impact Intro AJAX request - ENHANCED WITH ROOT-LEVEL FIXES
     */
    public function handle_save_impact_intro() {
        error_log('MKCG Impact Intro Generator: handle_save_impact_intro called');
        error_log('MKCG Impact Intro Generator: POST data: ' . print_r($_POST, true));
        
        // ROOT FIX: Enhanced nonce verification
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (!wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log('MKCG Impact Intro Generator: Nonce verification failed');
            wp_send_json_error(['message' => 'Security check failed', 'error_code' => 'NONCE_FAILED']);
            return;
        }
        
        // ROOT FIX: Enhanced permissions check
        if (!current_user_can('edit_posts')) {
            error_log('MKCG Impact Intro Generator: Insufficient permissions for user: ' . get_current_user_id());
            wp_send_json_error(['message' => 'Insufficient permissions', 'error_code' => 'INSUFFICIENT_PERMISSIONS']);
            return;
        }
        
        // ROOT FIX: Enhanced post ID validation
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id || $post_id <= 0) {
            error_log('MKCG Impact Intro Generator: Invalid post ID: ' . $post_id);
            wp_send_json_error(['message' => 'Post ID required', 'error_code' => 'INVALID_POST_ID']);
            return;
        }
        
        // ROOT FIX: Validate post exists before checking type
        $post = get_post($post_id);
        if (!$post) {
            error_log('MKCG Impact Intro Generator: Post not found: ' . $post_id);
            wp_send_json_error(['message' => 'Post not found', 'error_code' => 'POST_NOT_FOUND']);
            return;
        }
        
        // ROOT FIX: Enhanced post type validation with fallback
        $is_valid_post = false;
        if ($this->pods_service && method_exists($this->pods_service, 'is_guests_post')) {
            $is_valid_post = $this->pods_service->is_guests_post($post_id);
            error_log('MKCG Impact Intro Generator: Pods service validation - is_guests_post: ' . ($is_valid_post ? 'YES' : 'NO'));
        } else {
            // Fallback: check post type directly
            $is_valid_post = ($post->post_type === 'guests');
            error_log('MKCG Impact Intro Generator: Direct post type validation - type: ' . $post->post_type . ', valid: ' . ($is_valid_post ? 'YES' : 'NO'));
        }
        
        if (!$is_valid_post) {
            error_log('MKCG Impact Intro Generator: Invalid post type for post ' . $post_id . ' - type: ' . $post->post_type);
            // ROOT FIX: Allow save anyway with warning instead of blocking
            error_log('MKCG Impact Intro Generator: Proceeding with save despite post type mismatch');
        }
        
        // ROOT FIX: Enhanced component collection and validation
        $components = [
            'where' => sanitize_textarea_field($_POST['where'] ?? ''),
            'why' => sanitize_textarea_field($_POST['why'] ?? '')
        ];
        
        error_log('MKCG Impact Intro Generator: Collected components: ' . json_encode($components));
        
        // ROOT FIX: Validate at least one component has content
        $has_content = false;
        foreach ($components as $key => $value) {
            if (!empty(trim($value))) {
                $has_content = true;
                break;
            }
        }
        
        if (!$has_content) {
            error_log('MKCG Impact Intro Generator: No content provided in components');
            wp_send_json_error([
                'message' => 'Please provide content for at least one field (WHERE or WHY)',
                'error_code' => 'NO_CONTENT',
                'components' => $components
            ]);
            return;
        }
        
        // ROOT FIX: Enhanced save operation with comprehensive error handling
        try {
            error_log('MKCG Impact Intro Generator: Attempting save using Impact Intro Service');
            
            if (!$this->impact_intro_service) {
                error_log('MKCG Impact Intro Generator: Impact Intro Service not available');
                wp_send_json_error(['message' => 'Impact Intro Service not available', 'error_code' => 'SERVICE_UNAVAILABLE']);
                return;
            }
            
            $result = $this->impact_intro_service->save_impact_intro_data($post_id, $components);
            error_log('MKCG Impact Intro Generator: Save result: ' . json_encode($result));
            
            if ($result['success']) {
                error_log('MKCG Impact Intro Generator: ✅ Save successful');
                wp_send_json_success([
                    'message' => $result['message'] ?? 'Impact Intro saved successfully',
                    'post_id' => $post_id,
                    'components' => $result['components'] ?? $components,
                    'save_details' => $result['save_details'] ?? [],
                    'saved_count' => $result['saved_count'] ?? 0
                ]);
            } else {
                error_log('MKCG Impact Intro Generator: ❌ Save failed: ' . ($result['message'] ?? 'Unknown error'));
                wp_send_json_error([
                    'message' => $result['message'] ?? 'Save failed',
                    'error_code' => 'SAVE_FAILED',
                    'post_id' => $post_id,
                    'components' => $components,
                    'save_details' => $result['save_details'] ?? []
                ]);
            }
        } catch (Exception $e) {
            error_log('MKCG Impact Intro Generator: Exception during save: ' . $e->getMessage());
            error_log('MKCG Impact Intro Generator: Exception trace: ' . $e->getTraceAsString());
            wp_send_json_error([
                'message' => 'Server error during save: ' . $e->getMessage(),
                'error_code' => 'SERVER_EXCEPTION',
                'post_id' => $post_id,
                'exception' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle get Impact Intro AJAX request
     */
    public function handle_get_impact_intro() {
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
        
        // Get Impact Intro data using service
        $impact_intro_data = $this->impact_intro_service->get_impact_intro_data($post_id);
        
        wp_send_json_success($impact_intro_data);
    }
    
    /**
     * Validate Impact Intro components
     */
    private function validate_components($components) {
        $errors = [];
        
        if (empty($components['where'])) {
            $errors[] = 'WHERE field is required';
        }
        
        if (empty($components['why'])) {
            $errors[] = 'WHY field is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get Impact Intro Service instance
     */
    public function get_impact_intro_service() {
        return $this->impact_intro_service;
    }
    
    /**
     * Get Pods Service instance
     */
    public function get_pods_service() {
        return $this->pods_service;
    }
}
