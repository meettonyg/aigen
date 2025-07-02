<?php

class MKCG_Topics_AJAX_Handlers {
    
    private $topics_generator;
    
    public function __construct($topics_generator) {
        $this->topics_generator = $topics_generator;
        $this->init();
    }
    
    public function init() {
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'save_authority_hook']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook', [$this, 'save_authority_hook']);
        
        add_action('wp_ajax_mkcg_save_authority_hook_components_safe', [$this, 'save_authority_hook_components_safe']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook_components_safe', [$this, 'save_authority_hook_components_safe']);
        
        add_action('wp_ajax_mkcg_generate_topics', [$this, 'generate_topics']);
        add_action('wp_ajax_nopriv_mkcg_generate_topics', [$this, 'generate_topics']);
        
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'get_topics_data']);
        add_action('wp_ajax_nopriv_mkcg_get_topics_data', [$this, 'get_topics_data']);
        
        add_action('wp_ajax_mkcg_save_topics_data', [$this, 'save_topics_data']);
        add_action('wp_ajax_nopriv_mkcg_save_topics_data', [$this, 'save_topics_data']);
        
        add_action('wp_ajax_mkcg_health_check', [$this, 'handle_health_check']);
        add_action('wp_ajax_nopriv_mkcg_health_check', [$this, 'handle_health_check']);
    }
    
    public function save_topics_data() {
        if (!$this->verify_nonce()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
            return;
        }
        
        $topics_data = $this->extract_topics_data($_POST);
        if (empty($topics_data)) {
            wp_send_json_error(['message' => 'No topics data provided']);
            return;
        }
        
        $result = $this->topics_generator->handle_save_topics_data_ajax();
    }
    
    private function extract_topics_data($request_data) {
        $topics_data = [];
        
        if (isset($request_data['topics']) && is_array($request_data['topics'])) {
            foreach ($request_data['topics'] as $key => $value) {
                if (!empty(trim($value))) {
                    $topic_key = strpos($key, 'topic_') === 0 ? $key : 'topic_' . $key;
                    $topics_data[$topic_key] = trim($value);
                }
            }
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $topic_key = 'topic_' . $i;
            if (isset($request_data[$topic_key]) && !empty(trim($request_data[$topic_key]))) {
                $topics_data[$topic_key] = trim($request_data[$topic_key]);
            }
        }
        
        return $topics_data;
    }
    
    public function save_authority_hook_components_safe() {
        if (!$this->verify_nonce()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
            return;
        }
        
        $who = isset($_POST['who']) ? sanitize_textarea_field($_POST['who']) : 'your audience';
        $result = isset($_POST['result']) ? sanitize_textarea_field($_POST['result']) : 'achieve their goals';
        $when = isset($_POST['when']) ? sanitize_textarea_field($_POST['when']) : 'they need help';
        $how = isset($_POST['how']) ? sanitize_textarea_field($_POST['how']) : 'through your method';
        
        $save_result = $this->topics_generator->save_authority_hook_components_safe(
            $entry_id, $who, $result, $when, $how
        );
        
        if ($save_result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook saved successfully',
                'authority_hook' => $save_result['authority_hook'],
                'components' => [
                    'who' => $who,
                    'result' => $result,
                    'when' => $when,
                    'how' => $how
                ]
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save authority hook components',
                'errors' => $save_result['errors'] ?? ['Unknown error']
            ]);
        }
    }
    
    public function save_authority_hook() {
        $this->save_authority_hook_components_safe();
    }
    
    public function generate_topics() {
        if (!$this->verify_nonce()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $authority_hook = isset($_POST['authority_hook']) ? sanitize_textarea_field($_POST['authority_hook']) : '';
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        if (empty($authority_hook)) {
            wp_send_json_error(['message' => 'Authority hook is required']);
            return;
        }
        
        $generated_topics = $this->generate_demo_topics($authority_hook);
        
        wp_send_json_success([
            'topics' => $generated_topics,
            'count' => count($generated_topics),
            'authority_hook' => $authority_hook,
            'entry_id' => $entry_id
        ]);
    }
    
    private function generate_demo_topics($authority_hook) {
        if (stripos($authority_hook, 'revenue') !== false || stripos($authority_hook, 'business') !== false) {
            return [
                "Navigating Turbulent Times: Proven Strategies for Small Businesses to Survive and Thrive During Crises",
                "From Adversity to Advantage: How Businesses Can Turn Challenges into Opportunities for Growth",
                "The Power of Community: How Small Businesses Can Collaborate to Overcome Economic Uncertainty",
                "Building a Resilient Business: Core Mindset Frameworks That Empower Business Leaders",
                "Streamlining Operations: How to Identify and Eliminate Revenue-Draining Inefficiencies"
            ];
        } else {
            return [
                "The Authority Positioning Framework: How to Become the Go-To Expert in Your Niche",
                "Creating Content That Converts: A Strategic Approach to Audience Building",
                "Systems for Success: Automating Your Business to Create More Freedom", 
                "The Podcast Guest Formula: How to Turn Interviews into High-Value Clients",
                "Building a Sustainable Business Model That Serves Your Lifestyle Goals"
            ];
        }
    }
    
    public function get_topics_data() {
        if (!$this->verify_nonce()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        
        if (!$entry_id && !$entry_key) {
            wp_send_json_error(['message' => 'Entry ID or key required']);
            return;
        }
        
        $this->topics_generator->handle_get_topics_data_ajax();
    }
    
    public function handle_health_check() {
        wp_send_json_success([
            'status' => 'healthy',
            'timestamp' => current_time('mysql'),
            'ajax_handler' => 'working',
            'topics_generator_available' => $this->topics_generator ? true : false
        ]);
    }
    
    private function verify_nonce() {
        $nonce_fields = ['nonce', 'security', 'mkcg_nonce', '_wpnonce'];
        $nonce_actions = ['mkcg_nonce', 'save_topics_nonce'];
        
        foreach ($nonce_fields as $field) {
            if (isset($_POST[$field]) && !empty($_POST[$field])) {
                foreach ($nonce_actions as $action) {
                    if (wp_verify_nonce($_POST[$field], $action)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    private function can_edit_entry($entry_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        if (!current_user_can('edit_posts')) {
            return false;
        }
        
        if (current_user_can('administrator')) {
            return true;
        }
        
        return true;
    }
}