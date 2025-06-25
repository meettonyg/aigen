<?php
/**
 * MKCG Topics Generator
 * Generates interview topics based on Authority Hook and audience
 */

class MKCG_Topics_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'topics';
    
    /**
     * Get form fields configuration
     */
    public function get_form_fields() {
        return [
            'authority_hook' => [
                'type' => 'textarea',
                'label' => 'Authority Hook',
                'required' => true,
                'description' => 'Your expert introduction statement'
            ],
            'audience' => [
                'type' => 'text',
                'label' => 'Target Audience (Optional)',
                'required' => false,
                'description' => 'Specific audience for the topics'
            ]
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
            $errors[] = 'Authority Hook must be at least 10 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for topic generation
     */
    public function build_prompt($data) {
        $authority_hook = $data['authority_hook'];
        $audience = isset($data['audience']) ? $data['audience'] : '';
        
        $prompt = "You are an AI assistant specialized in generating **highly relevant interview topics** that align **only with the expert's authority**.

The expert's area of expertise is: \"$authority_hook\".

### **Key Requirements for Topics:**
- Topics **must directly relate to the expert's authority**—avoid unrelated subjects like podcasting unless explicitly relevant.
- Topics should be **intriguing, insightful, and results-driven** to attract podcast hosts.
- Use **specific strategies, case studies, or proven methods** within the expert's domain.
- If an audience is provided, ensure topics speak **specifically to their challenges and goals**.

";

        if (!empty($audience)) {
            $prompt .= "### **Target Audience:** \"$audience\"\nEnsure the topics focus on **the concerns, pain points, and goals of this audience.**\n";
        }

        $prompt .= "### **Example High-Performing Podcast Topics in the Given Niche:**
1. 'The Hidden Wealth in Tax Overages: How Future Retirees Can Unlock Passive Income'
2. 'Tax Overages Explained: How to Identify and Claim Overlooked Funds'
3. '5 Steps to Turning Tax Surplus into a Lucrative Side Business'
4. 'How Future Retirees Can Secure Financial Freedom with Unclaimed Tax Overages'
5. 'From Overages to Income: The Legal and Financial Aspects of Claiming Tax Surpluses'

### **Now generate 5 unique, compelling podcast topics based on the expert introduction. Format as a numbered list (1., 2., etc.), with one topic per line.**";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service already formats topics as an array
        if (is_array($api_response)) {
            return [
                'topics' => $api_response,
                'count' => count($api_response)
            ];
        }
        
        // Fallback if raw string returned
        $topics = [];
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
            $topics = array_map('trim', $matches[1]);
        } else {
            $topics = array_filter(array_map(function($t) {
                return trim($t, " '\"");
            }, explode("\n", $api_response)));
        }
        
        return [
            'topics' => $topics,
            'count' => count($topics)
        ];
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        return [
            'audience' => isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : ''
        ];
    }
    
    /**
     * Get field mappings for Formidable
     */
    protected function get_field_mappings() {
        // Map generated content to Formidable field IDs
        // These would be configured based on your specific form
        return [
            'topics' => 10359, // Example field ID for topics
            'topic_count' => 10360 // Example field ID for topic count
        ];
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
    }
    
    /**
     * Override AJAX generation to handle legacy compatibility
     */
    public function handle_ajax_generation() {
        // Handle legacy action name for backwards compatibility
        if (isset($_POST['action']) && $_POST['action'] === 'generate_interview_topics') {
            $this->handle_legacy_topics_generation();
            return;
        }
        
        // Call parent method for new unified handling
        parent::handle_ajax_generation();
    }
    
    /**
     * Handle legacy topics generation (for backwards compatibility)
     */
    private function handle_legacy_topics_generation() {
        // Use the original Topics generator logic for existing implementations
        if (!check_ajax_referer('generate_topics_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            error_log('Invalid entry ID: ' . $_POST['entry_id']);
            wp_send_json_error(['message' => 'Invalid entry ID.']);
            return;
        }
        
        // Get Authority Hook using the service
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if (!$authority_hook_result['success']) {
            wp_send_json_error($authority_hook_result);
            return;
        }
        
        $authority_hook = $authority_hook_result['value'];
        $audience = isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : '';
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'authority_hook' => $authority_hook,
            'audience' => $audience
        ];
        
        // Validate
        $validation_result = $this->validate_input($input_data);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($input_data);
        
        // Generate content
        $api_response = $this->api_service->generate_content($prompt, $this->generator_type);
        
        if (!$api_response['success']) {
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        // Return in legacy format for compatibility
        wp_send_json_success([
            'topics' => $formatted_output['topics']
        ]);
    }
    
    /**
     * Initialize legacy AJAX actions for backwards compatibility
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions
        add_action('wp_ajax_generate_interview_topics', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_topics', [$this, 'handle_ajax_generation']);
        
        // Keep the legacy fetch authority hook action
        add_action('wp_ajax_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
        add_action('wp_ajax_nopriv_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
    }
    
    /**
     * Handle legacy fetch authority hook request
     */
    public function handle_fetch_authority_hook() {
        if (!check_ajax_referer('generate_topics_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID']);
            return;
        }
        
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if ($authority_hook_result['success']) {
            wp_send_json_success([
                'authority_hook' => $authority_hook_result['value']
            ]);
        } else {
            wp_send_json_error($authority_hook_result);
        }
    }
}