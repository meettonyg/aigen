<?php
/**
 * Enhanced Questions Generator
 * Handles questions generation functionality for the Media Kit Content Generator
 */

class Enhanced_Questions_Generator {
    
    private $api_service;
    private $formidable_service;
    
    public function __construct($api_service, $formidable_service) {
        $this->api_service = $api_service;
        $this->formidable_service = $formidable_service;
        $this->init();
    }
    
    public function init() {
        // Register AJAX actions for questions
        add_action('wp_ajax_mkcg_generate_questions', [$this, 'handle_generate_questions']);
        add_action('wp_ajax_nopriv_mkcg_generate_questions', [$this, 'handle_generate_questions']);
        add_action('wp_ajax_mkcg_save_questions', [$this, 'handle_save_questions']);
        add_action('wp_ajax_nopriv_mkcg_save_questions', [$this, 'handle_save_questions']);
        add_action('wp_ajax_mkcg_get_questions_data', [$this, 'handle_get_questions']);
        add_action('wp_ajax_nopriv_mkcg_get_questions_data', [$this, 'handle_get_questions']);
    }
    
    /**
     * Get template data for Questions Generator
     */
    public function get_template_data($entry_key = '') {
        $template_data = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'topics' => [],
            'questions' => [],
            'has_data' => false
        ];
        
        // Get entry ID from entry key
        if (!empty($entry_key)) {
            $template_data['entry_id'] = $this->resolve_entry_id($entry_key);
        }
        
        // Load data if entry ID found
        if ($template_data['entry_id'] > 0) {
            $template_data = $this->load_template_data($template_data);
        }
        
        return $template_data;
    }
    
    /**
     * Load template data from database
     */
    private function load_template_data($template_data) {
        $entry_id = $template_data['entry_id'];
        
        // Get post ID from entry
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if ($post_id) {
            // Load topics from post meta
            $topics = [];
            for ($i = 1; $i <= 5; $i++) {
                $topic = get_post_meta($post_id, "mkcg_topic_{$i}", true);
                if (!empty($topic)) {
                    $topics[$i] = $topic;
                }
            }
            $template_data['topics'] = $topics;
            
            // Load questions from post meta
            $questions = [];
            for ($topic = 1; $topic <= 5; $topic++) {
                $topic_questions = [];
                for ($q = 1; $q <= 5; $q++) {
                    $question = get_post_meta($post_id, "mkcg_question_{$topic}_{$q}", true);
                    if (!empty($question)) {
                        $topic_questions[$q] = $question;
                    }
                }
                if (!empty($topic_questions)) {
                    $questions[$topic] = $topic_questions;
                }
            }
            $template_data['questions'] = $questions;
            
            $template_data['has_data'] = !empty($topics) || !empty($questions);
        }
        
        return $template_data;
    }
    
    /**
     * Handle generate questions AJAX request
     */
    public function handle_generate_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $topic = sanitize_textarea_field($_POST['topic'] ?? '');
        if (empty($topic)) {
            wp_send_json_error(['message' => 'Topic is required']);
            return;
        }
        
        // Generate questions using API service or demo
        $questions = $this->generate_questions_for_topic($topic);
        
        wp_send_json_success([
            'questions' => $questions,
            'topic' => $topic,
            'count' => count($questions)
        ]);
    }
    
    /**
     * Handle save questions AJAX request
     */
    public function handle_save_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        $questions_data = $this->extract_questions_data();
        if (empty($questions_data)) {
            wp_send_json_error(['message' => 'No questions data provided']);
            return;
        }
        
        $result = $this->save_questions($entry_id, $questions_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Questions saved successfully',
                'saved_count' => $result['saved_count'],
                'entry_id' => $entry_id
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * Handle get questions AJAX request
     */
    public function handle_get_questions() {
        if (!$this->verify_request()) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = $this->get_entry_id();
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID required']);
            return;
        }
        
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'No associated post found']);
            return;
        }
        
        // Get questions from post meta
        $questions = [];
        for ($topic = 1; $topic <= 5; $topic++) {
            $topic_questions = [];
            for ($q = 1; $q <= 5; $q++) {
                $question = get_post_meta($post_id, "mkcg_question_{$topic}_{$q}", true);
                if (!empty($question)) {
                    $topic_questions[$q] = $question;
                }
            }
            if (!empty($topic_questions)) {
                $questions[$topic] = $topic_questions;
            }
        }
        
        wp_send_json_success([
            'entry_id' => $entry_id,
            'post_id' => $post_id,
            'questions' => $questions,
            'has_data' => !empty($questions)
        ]);
    }
    
    /**
     * Generate questions for a topic
     */
    public function generate_questions_for_topic($topic) {
        // Demo questions - replace with API call when available
        return [
            "What inspired you to develop your approach to {$topic}?",
            "Can you walk us through your step-by-step process for {$topic}?", 
            "What's the biggest mistake you see people making when it comes to {$topic}?",
            "What results have your clients seen after implementing your {$topic} strategies?",
            "What advice would you give to someone just starting with {$topic}?"
        ];
    }
    
    /**
     * Save questions to database
     */
    public function save_questions($entry_id, $questions_data) {
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if (!$post_id) {
            return ['success' => false, 'message' => 'No associated post found'];
        }
        
        $saved_count = 0;
        
        foreach ($questions_data as $key => $value) {
            if (!empty($value)) {
                $result = update_post_meta($post_id, $key, $value);
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Questions saved successfully' : 'No questions saved'
        ];
    }
    
    /**
     * Extract questions data from request
     */
    private function extract_questions_data() {
        $questions = [];
        
        // Handle questions array
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $topic => $topic_questions) {
                if (is_array($topic_questions)) {
                    foreach ($topic_questions as $q => $question) {
                        if (!empty(trim($question))) {
                            $key = "mkcg_question_{$topic}_{$q}";
                            $questions[$key] = sanitize_textarea_field($question);
                        }
                    }
                }
            }
        }
        
        return $questions;
    }
    
    /**
     * Verify AJAX request
     */
    private function verify_request() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        if (!current_user_can('edit_posts')) {
            return false;
        }
        
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (empty($nonce)) {
            return false;
        }
        
        return wp_verify_nonce($nonce, 'mkcg_nonce');
    }
    
    /**
     * Get entry ID from request
     */
    private function get_entry_id() {
        return isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
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
}
