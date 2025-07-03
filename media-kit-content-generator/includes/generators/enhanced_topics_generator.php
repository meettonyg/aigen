<?php
/**
 * Enhanced Topics Generator - Pods Custom Post Type Integration
 * Single responsibility: Generate interview topics using Pods "guests" custom post type
 * Uses: Pods service as single source of truth for all data
 */

class Enhanced_Topics_Generator {
    
    private $api_service;
    private $pods_service;
    private $ajax_handlers;
    
    /**
     * Constructor with Pods service integration
     */
    public function __construct($api_service, $formidable_service = null) {
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
        // Initialize AJAX handlers (pass null for formidable service since we don't use it)
        $this->ajax_handlers = new Enhanced_AJAX_Handlers(null, $this);
        
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
     * Get template data using Pods service - Single source of truth
     */
    public function get_template_data($entry_key = '') {
        error_log('MKCG Topics Generator: Starting get_template_data - Pods Integration');
        
        // Get post_id from request parameters
        $post_id = $this->get_post_id_from_request();
        
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
        
        // Transform to expected template format
        $template_data = [
            'post_id' => $post_id,
            'entry_id' => $this->pods_service->get_entry_id_from_post($post_id), // Legacy compatibility
            'entry_key' => $entry_key, // Legacy compatibility
            'has_entry' => $guest_data['has_data'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            'form_field_values' => $guest_data['topics'],
            'contact' => $guest_data['contact'],
            'messaging' => $guest_data['messaging']
        ];
        
        error_log('MKCG Topics Generator: Data loaded successfully from Pods service');
        return $template_data;
    }
    
    /**
     * Get post_id from request parameters - handles entry ID conversion
     */
    private function get_post_id_from_request() {
        // Check for direct post_id parameter (new format)
        if (isset($_GET['post_id'])) {
            return intval($_GET['post_id']);
        }
        
        // Handle legacy entry parameter by converting to post_id
        if (isset($_GET['entry'])) {
            $entry_id = intval($_GET['entry']);
            if ($entry_id > 0) {
                // Get the associated custom post ID for this entry
                $post_id = $this->get_post_id_from_entry($entry_id);
                error_log('MKCG Topics Generator: Converted entry ' . $entry_id . ' to post_id ' . $post_id);
                return $post_id;
            }
        }
        
        // Check for global post context
        global $post;
        if ($post && $post->ID) {
            return $post->ID;
        }
        
        // Check if we're on a post page
        if (is_single() || is_page()) {
            return get_the_ID();
        }
        
        return 0;
    }
    
    /**
     * Convert entry ID to post ID (for legacy URL support)
     */
    private function get_post_id_from_entry($entry_id) {
        global $wpdb;
        
        // Query to find the post_id associated with this entry_id
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        return $post_id ? intval($post_id) : 0;
    }
    
    // Removed load_authority_hook_from_post() and load_topics_from_post() 
    // These are now handled by the Pods service
    
    /**
     * Get default template data structure
     */
    private function get_default_template_data() {
        return [
            'post_id' => 0,
            'entry_id' => 0,
            'entry_key' => '',
            'has_entry' => false,
            'authority_hook_components' => [
                'who' => 'your audience',
                'what' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method',
                'where' => 'in their situation',
                'why' => 'because they deserve success',
                'complete' => 'I help your audience achieve their goals when they need help by showing them through your method in their situation because they deserve success.'
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
     * Resolve entry ID from entry key
     */
    private function resolve_entry_id($entry_key) {
        if (is_numeric($entry_key)) {
            return intval($entry_key);
        }
        
        global $wpdb;
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE item_key = %s",
            $entry_key
        ));
        
        return $entry_id ? intval($entry_id) : 0;
    }
    
    /**
     * Get empty topics array
     */
    private function get_empty_topics_array() {
        return [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
    }
    
    /**
     * Get empty authority hook structure
     */
    private function get_empty_authority_hook() {
        return [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => '',
            'complete' => ''
        ];
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
