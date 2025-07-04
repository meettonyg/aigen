<?php
/**
 * Enhanced Topics Generator - Pure Pods Integration
 * Single responsibility: Generate interview topics using Pods "guests" custom post type
 * Uses: Dynamic post_id from query string, no Formidable dependencies
 */

class Enhanced_Topics_Generator {
    
    private $api_service;
    private $pods_service;
    
    /**
     * Constructor - Pure Pods service integration
     */
    public function __construct($api_service) {
        $this->api_service = $api_service;
        
        // Initialize Pods service
        require_once dirname(__FILE__) . '/../services/class-mkcg-pods-service.php';
        $this->pods_service = new MKCG_Pods_Service();
        
        $this->init();
    }
    
    /**
     * Initialize - direct and simple
     */
    public function init() {
        // REMOVED: AJAX handlers now initialized at root level in main plugin file
        // This prevents timing issues and ensures handlers are registered properly
        
        // Add any WordPress hooks needed
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue required scripts
     */
    public function enqueue_scripts() {
        // Only enqueue on topics generator pages
        if (!$this->is_topics_page()) {
            return;
        }
        
        wp_enqueue_script(
            'enhanced-topics-generator',
            plugins_url('assets/js/topics-generator.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
        
        wp_localize_script('enhanced-topics-generator', 'topicsVars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce')
        ]);
    }
    
    /**
     * Check if current page is topics generator
     */
    private function is_topics_page() {
        // Simple check - customize as needed
        return is_page() && (strpos(get_post()->post_content, '[topics_generator]') !== false);
    }
    
    /**
     * Get template data using dynamic post_id - Pure Pods integration
     */
    public function get_template_data($post_id = null) {
        error_log('MKCG Topics Generator: Starting get_template_data - Pure Pods Integration');
        
        // Get post_id from request parameters or use provided one
        if (!$post_id) {
            $post_id = $this->get_post_id_from_request();
        }
        
        if (!$post_id) {
            error_log('MKCG Topics Generator: No valid post ID found');
            return $this->get_default_template_data();
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            error_log('MKCG Topics Generator: Post ' . $post_id . ' is not a guests post type');
            return $this->get_default_template_data();
        }
        
        error_log('MKCG Topics Generator: Loading data for guests post ID: ' . $post_id);
        
        // Load ALL data from Pods service
        $guest_data = $this->pods_service->get_guest_data($post_id);
        
        // Transform to template format (no Formidable references)
        $template_data = [
            'post_id' => $post_id,
            'has_data' => $guest_data['has_data'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            'form_field_values' => $guest_data['topics'],
            'contact' => $guest_data['contact'],
            'messaging' => $guest_data['messaging']
        ];
        
        error_log('MKCG Topics Generator: Data loaded successfully from Pods service');
        return $template_data;
    }
    
    /**
     * Get post_id from request parameters - Dynamic post ID detection
     */
    private function get_post_id_from_request() {
        error_log('MKCG Topics Generator: Starting dynamic post ID detection');
        
        // Strategy 1: Direct post_id parameter (primary method)
        if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            $post_id = intval($_GET['post_id']);
            error_log('MKCG Topics Generator: Found post_id parameter: ' . $post_id);
            
            // Validate it's a guests post
            if (get_post($post_id) && get_post($post_id)->post_type === 'guests') {
                return $post_id;
            } else {
                error_log('MKCG Topics Generator: Post ID ' . $post_id . ' is not a valid guests post');
            }
        }
        
        // Strategy 2: Check for global post context
        global $post;
        if ($post && $post->ID && $post->post_type === 'guests') {
            error_log('MKCG Topics Generator: Using global post context: ' . $post->ID);
            return $post->ID;
        }
        
        // Strategy 3: Check if we're on a guest post page
        if (is_single() || is_page()) {
            $current_id = get_the_ID();
            if ($current_id && get_post_type($current_id) === 'guests') {
                error_log('MKCG Topics Generator: Found guest post page: ' . $current_id);
                return $current_id;
            }
        }
        
        // Strategy 4: Look for the most recent guest post for testing
        $recent_guest = get_posts([
            'post_type' => 'guests',
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (!empty($recent_guest)) {
            $post_id = $recent_guest[0]->ID;
            error_log('MKCG Topics Generator: Using most recent guest post for testing: ' . $post_id);
            return $post_id;
        }
        
        error_log('MKCG Topics Generator: No valid post ID found with any strategy');
        return 0;
    }
    
    /**
     * Get default template data structure - Pure Pods
     * Updated to check for entry parameters and not provide defaults when no entry param
     */
    private function get_default_template_data() {
        // Check for entry parameter - don't show defaults if no entry param provided
        $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                           (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        
        if (!$has_entry_param) {
            // NO ENTRY PARAM: Return empty structure with no defaults
            error_log('MKCG Topics Generator: No entry parameter found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'form_field_values' => [
                    'topic_1' => '',
                    'topic_2' => '',
                    'topic_3' => '',
                    'topic_4' => '',
                    'topic_5' => ''
                ],
                'contact' => [],
                'messaging' => [],
                'no_entry_param' => true
            ];
        } else {
            // HAS ENTRY PARAM: Return empty structure (entry param exists but no data found)
            error_log('MKCG Topics Generator: Entry parameter exists but no data found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'form_field_values' => [
                    'topic_1' => '',
                    'topic_2' => '',
                    'topic_3' => '',
                    'topic_4' => '',
                    'topic_5' => ''
                ],
                'contact' => [],
                'messaging' => []
            ];
        }
    }
    
    /**
     * Generate topics using API service
     */
    public function generate_topics($authority_hook, $audience = '') {
        if (empty($authority_hook)) {
            return [
                'success' => false,
                'message' => 'Authority hook is required'
            ];
        }
        
        $prompt = $this->build_prompt($authority_hook, $audience);
        
        $api_response = $this->api_service->generate_content($prompt, 'topics');
        
        if (!$api_response['success']) {
            return $api_response;
        }
        
        $topics = $this->parse_topics_from_response($api_response['content']);
        
        return [
            'success' => true,
            'topics' => $topics,
            'count' => count($topics)
        ];
    }
    
    /**
     * Save topics using Pods service
     */
    public function save_topics($post_id, $topics_data) {
        if (!$post_id || empty($topics_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        // Use Pods service for saving
        return $this->pods_service->save_topics($post_id, $topics_data);
    }
    
    /**
     * Save authority hook using Pods service
     */
    public function save_authority_hook($post_id, $authority_hook_data) {
        if (!$post_id || empty($authority_hook_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        // Use Pods service for saving
        return $this->pods_service->save_authority_hook_components($post_id, $authority_hook_data);
    }
    
    /**
     * Build prompt for API service
     */
    private function build_prompt($authority_hook, $audience = '') {
        $prompt = "Generate 5 compelling podcast interview topics based on this expert's authority:\n\n";
        $prompt .= "Expert Authority: {$authority_hook}\n\n";
        
        if (!empty($audience)) {
            $prompt .= "Target Audience: {$audience}\n\n";
        }
        
        $prompt .= "Requirements:\n";
        $prompt .= "- Topics must directly relate to the expert's authority area\n";
        $prompt .= "- Make topics intriguing and results-driven to attract podcast hosts\n";
        $prompt .= "- Use specific strategies, case studies, or proven methods\n";
        $prompt .= "- Format as numbered list (1., 2., 3., 4., 5.)\n\n";
        
        return $prompt;
    }
    
    /**
     * Parse topics from API response
     */
    private function parse_topics_from_response($response) {
        $topics = [];
        
        // Split by lines and look for numbered items
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Match numbered format (1., 2., etc.)
            if (preg_match('/^\d+\.\s*(.+)/', $line, $matches)) {
                $topic = trim($matches[1], ' "\'');
                if (!empty($topic)) {
                    $topics[] = $topic;
                }
            }
        }
        
        // Ensure we have exactly 5 topics
        $topics = array_slice($topics, 0, 5);
        
        // Pad with empty strings if needed
        while (count($topics) < 5) {
            $topics[] = '';
        }
        
        return $topics;
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data) {
        $errors = [];
        
        if (empty($data['authority_hook'])) {
            $errors[] = 'Authority Hook is required';
        }
        
        if (!empty($data['authority_hook']) && strlen($data['authority_hook']) < 10) {
            $errors[] = 'Authority Hook must be at least 10 characters long';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
