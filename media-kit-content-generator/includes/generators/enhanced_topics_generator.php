<?php
/**
 * Simplified Topics Generator
 * Single responsibility: Generate interview topics cleanly
 * Eliminates: Complex initialization, multiple loading strategies, diagnostic systems
 */

class Enhanced_Topics_Generator {
    
    private $api_service;
    private $formidable_service;
    private $ajax_handlers;
    
    /**
     * Simple constructor - direct initialization, no phases or race condition workarounds
     */
    public function __construct($api_service, $formidable_service) {
        $this->api_service = $api_service;
        $this->formidable_service = $formidable_service;
        $this->init();
    }
    
    /**
     * Initialize - direct and simple
     */
    public function init() {
        // Initialize AJAX handlers
        $this->ajax_handlers = new Enhanced_AJAX_Handlers($this->formidable_service, $this);
        
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
     * CRITICAL FIX: Get template data for rendering - Proper structure with correct field mappings
     */
    public function get_template_data($entry_key = '') {
        error_log('MKCG Topics Generator: Starting get_template_data for entry_key: ' . $entry_key);
        
        $template_data = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'form_field_values' => [
                'topic_1' => '',
                'topic_2' => '',
                'topic_3' => '',
                'topic_4' => '',
                'topic_5' => ''
            ],
            'authority_hook_components' => [
                'who' => 'your audience',
                'result' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method',
                'complete' => 'I help your audience achieve their goals when they need help through your method.'
            ],
            'has_entry' => false
        ];
        
        // Get entry ID from entry key
        if (!empty($entry_key)) {
            $template_data['entry_id'] = $this->resolve_entry_id($entry_key);
            error_log('MKCG Topics Generator: Resolved entry ID: ' . $template_data['entry_id']);
        }
        
        // Load actual data if entry ID found
        if ($template_data['entry_id'] > 0) {
            $template_data = $this->load_template_data_fixed($template_data);
            error_log('MKCG Topics Generator: Template data loaded for entry ' . $template_data['entry_id']);
        } else {
            error_log('MKCG Topics Generator: No valid entry ID - using default data');
        }
        
        return $template_data;
    }
    
    /**
     * CRITICAL FIX: Load actual data from Formidable with proper error handling
     */
    private function load_template_data_fixed($template_data) {
        $entry_id = $template_data['entry_id'];
        
        try {
            // Load topic field values with proper mapping
            $topics_fields = $this->get_topics_field_mappings();
            $has_topic_data = false;
            
            foreach ($topics_fields as $topic_key => $field_id) {
                $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                if (!empty($value)) {
                    $template_data['form_field_values'][$topic_key] = $value;
                    $has_topic_data = true;
                    error_log("MKCG Topics Generator: Loaded {$topic_key} from field {$field_id}: {$value}");
                }
            }
            
            // Load authority hook components
            $auth_fields = $this->get_authority_hook_field_mappings();
            $has_auth_data = false;
            
            foreach ($auth_fields as $component => $field_id) {
                $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                if (!empty($value)) {
                    $template_data['authority_hook_components'][$component] = $value;
                    $has_auth_data = true;
                    error_log("MKCG Topics Generator: Loaded {$component} from field {$field_id}: {$value}");
                }
            }
            
            // Build complete authority hook if we have components
            if ($has_auth_data) {
                $complete_hook = sprintf(
                    'I help %s %s when %s %s.',
                    $template_data['authority_hook_components']['who'],
                    $template_data['authority_hook_components']['result'],
                    $template_data['authority_hook_components']['when'],
                    $template_data['authority_hook_components']['how']
                );
                $template_data['authority_hook_components']['complete'] = $complete_hook;
                error_log('MKCG Topics Generator: Built complete authority hook: ' . $complete_hook);
            }
            
            if ($has_topic_data || $has_auth_data) {
                $template_data['has_entry'] = true;
                error_log('MKCG Topics Generator: Data successfully loaded from entry');
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Error loading template data: ' . $e->getMessage());
        }
        
        return $template_data;
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
     * Save topics to Formidable entry
     */
    public function save_topics($entry_id, $topics_data) {
        if (!$entry_id || empty($topics_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        $field_mappings = $this->get_topics_field_mappings();
        $formidable_data = [];
        
        foreach ($topics_data as $topic_key => $topic_value) {
            if (isset($field_mappings[$topic_key]) && !empty($topic_value)) {
                $formidable_data[$field_mappings[$topic_key]] = $topic_value;
            }
        }
        
        return $this->formidable_service->save_entry_data($entry_id, $formidable_data);
    }
    
    /**
     * Save authority hook to Formidable entry
     */
    public function save_authority_hook($entry_id, $authority_hook_data) {
        if (!$entry_id || empty($authority_hook_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        $field_mappings = $this->get_authority_hook_field_mappings();
        $formidable_data = [];
        
        foreach ($authority_hook_data as $component => $value) {
            if (isset($field_mappings[$component]) && !empty($value)) {
                $formidable_data[$field_mappings[$component]] = $value;
            }
        }
        
        return $this->formidable_service->save_entry_data($entry_id, $formidable_data);
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
     * Get topics field mappings - should come from centralized config
     */
    private function get_topics_field_mappings() {
        return [
            'topic_1' => '8498',
            'topic_2' => '8499',
            'topic_3' => '8500',
            'topic_4' => '8501',
            'topic_5' => '8502'
        ];
    }
    
    /**
     * Get authority hook field mappings - should come from centralized config
     */
    private function get_authority_hook_field_mappings() {
        return [
            'who' => '10296',
            'result' => '10297',
            'when' => '10387',
            'how' => '10298',
            'complete' => '10358'
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
