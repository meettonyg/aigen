<?php
/**
 * MKCG Topics Generator
 * Generates interview topics based on Authority Hook and audience
 */

class MKCG_Topics_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'topics';
    
    // Enhanced configuration - same as Questions Generator
    protected $max_topics = 5;
    protected $max_retries = 3;
    protected $cache_duration = 3600; // 1 hour
    
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
            $topics = $api_response;
        } else {
            // Fallback if raw string returned
            $topics = [];
            if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
                $topics = array_map('trim', $matches[1]);
            } else {
                $topics = array_filter(array_map(function($t) {
                    return trim($t, " '\"");
                }, explode("\n", $api_response)));
            }
        }
        
        // Format for Formidable field mapping
        $formatted = [
            'topics' => $topics,
            'count' => count($topics)
        ];
        
        // Map individual topics to fields for form 515
        for ($i = 0; $i < min(5, count($topics)); $i++) {
            $formatted['topic_' . ($i + 1)] = $topics[$i];
        }
        
        return $formatted;
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
     * STANDALONE: Get field mappings for Formidable (Form 515) - Enhanced structure
     */
    protected function get_field_mappings() {
        return [
            'topics' => [
                'topic_1' => 8498,  // Topic 1
                'topic_2' => 8499,  // Topic 2
                'topic_3' => 8500,  // Topic 3
                'topic_4' => 8501,  // Topic 4
                'topic_5' => 8502   // Topic 5
            ],
            'authority_hook' => [
                'who' => 10296,     // WHO field
                'result' => 10297,  // RESULT field
                'when' => 10387,    // WHEN field
                'how' => 10298,     // HOW field
                'complete' => 10358 // Complete Authority Hook
            ]
        ];
    }
    
    /**
     * Get authority hook component field mappings for form 515
     */
    public function get_authority_hook_field_mappings() {
        return [
            'who' => 10296,    // WHO do you help?
            'result' => 10297, // WHAT result do you help them achieve?
            'when' => 10387,   // WHEN do they need you?
            'how' => 10298,    // HOW do you help them?
            'complete' => 10358 // Complete Authority Hook
        ];
    }
    
    /**
     * Build authority hook from components
     */
    public function build_authority_hook_from_components($entry_id) {
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        $who = $this->formidable_service->get_field_value($entry_id, $field_mappings['who']) ?: 'your audience';
        $result = $this->formidable_service->get_field_value($entry_id, $field_mappings['result']) ?: 'achieve their goals';
        $when = $this->formidable_service->get_field_value($entry_id, $field_mappings['when']) ?: 'they need help';
        $how = $this->formidable_service->get_field_value($entry_id, $field_mappings['how']) ?: 'through your method';
        
        return "I help {$who} {$result} when {$when} {$how}.";
    }
    
    /**
     * Save authority hook components to Formidable
     */
    public function save_authority_hook_components($entry_id, $who, $result, $when, $how) {
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        // Save individual components
        $components = [
            'who' => $who,
            'result' => $result,
            'when' => $when,
            'how' => $how
        ];
        
        $saved_fields = [];
        foreach ($components as $component => $value) {
            if (isset($field_mappings[$component])) {
                $result = $this->formidable_service->save_generated_content(
                    $entry_id,
                    [$component => $value],
                    [$component => $field_mappings[$component]]
                );
                
                if ($result['success']) {
                    $saved_fields[$component] = $field_mappings[$component];
                }
            }
        }
        
        // Build and save complete authority hook
        $complete_hook = "I help {$who} {$result} when {$when} {$how}.";
        $complete_result = $this->formidable_service->save_generated_content(
            $entry_id,
            ['complete' => $complete_hook],
            ['complete' => $field_mappings['complete']]
        );
        
        if ($complete_result['success']) {
            $saved_fields['complete'] = $field_mappings['complete'];
        }
        
        return [
            'success' => count($saved_fields) > 0,
            'saved_fields' => $saved_fields,
            'authority_hook' => $complete_hook
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
        
        // Save topics to individual fields
        $this->save_to_formidable($entry_id, $formatted_output);
        
        // Return in legacy format for compatibility
        wp_send_json_success([
            'topics' => $formatted_output['topics']
        ]);
    }
    
    /**
     * STANDALONE: Initialize with enhanced AJAX actions for independent operation
     */
    public function init() {
        parent::init();
        
        // STANDALONE: Enhanced AJAX handlers for independent topics data loading
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'handle_get_topics_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_get_topics_data', [$this, 'handle_get_topics_data_ajax']);
        
        // STANDALONE: Enhanced topics data save handler
        add_action('wp_ajax_mkcg_save_topics_data', [$this, 'handle_save_topics_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topics_data', [$this, 'handle_save_topics_data_ajax']);
        
        // CRITICAL FIX: Add individual topic save for inline editing (same as Questions Generator)
        add_action('wp_ajax_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        
        // STANDALONE: Enhanced authority hook save with unified nonce
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'handle_save_authority_hook_unified']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook', [$this, 'handle_save_authority_hook_unified']);
        
        // Legacy AJAX actions for backwards compatibility
        add_action('wp_ajax_generate_interview_topics', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_topics', [$this, 'handle_ajax_generation']);
        
        // Legacy authority hook actions
        add_action('wp_ajax_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
        add_action('wp_ajax_nopriv_fetch_authority_hook', [$this, 'handle_fetch_authority_hook']);
        
        add_action('wp_ajax_save_authority_hook_components', [$this, 'handle_save_authority_hook_components']);
        add_action('wp_ajax_nopriv_save_authority_hook_components', [$this, 'handle_save_authority_hook_components']);
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
    
    /**
     * STANDALONE: Load existing topics data independently - SAME PATTERN AS QUESTIONS GENERATOR
     */
    public function handle_get_topics_data_ajax() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        
        if (!$entry_id && !$entry_key) {
            wp_send_json_error(['message' => 'No entry ID or key provided']);
            return;
        }
        
        if ($entry_key) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if (!$entry_data['success']) {
                wp_send_json_error(['message' => 'Entry not found: ' . $entry_key]);
                return;
            }
            $entry_id = $entry_data['entry_id'];
        }
        
        // Get post ID for enhanced data loading
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if (!$post_id) {
            // Try to get topics directly from Formidable entry as fallback
            $topics_from_entry = $this->get_topics_from_entry_direct($entry_id);
            
            wp_send_json_success([
                'topics' => $topics_from_entry['topics'],
                'data_quality' => $topics_from_entry['data_quality'],
                'authority_hook' => $this->get_authority_hook_data($entry_id),
                'entry_id' => $entry_id,
                'source' => 'formidable_entry'
            ]);
            return;
        }
        
        // Enhanced topic retrieval from custom post
        $topics_result = $this->formidable_service->get_topics_from_post_enhanced($post_id);
        
        // Also get authority hook data
        $authority_hook_result = $this->get_authority_hook_data($entry_id);
        
        error_log('MKCG Topics: Loaded ' . count(array_filter($topics_result['topics'])) . ' topics from post ' . $post_id);
        
        wp_send_json_success([
            'topics' => $topics_result['topics'],
            'data_quality' => $topics_result['data_quality'],
            'authority_hook' => $authority_hook_result,
            'entry_id' => $entry_id,
            'post_id' => $post_id,
            'source' => 'custom_post',
            'metadata' => $topics_result['metadata']
        ]);
    }
    
    /**
     * STANDALONE: Get topics directly from Formidable entry (fallback method)
     */
    private function get_topics_from_entry_direct($entry_id) {
        $topics = [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
        
        $field_mappings = $this->get_field_mappings();
        
        if (isset($field_mappings['topics'])) {
            foreach ($field_mappings['topics'] as $topic_key => $field_id) {
                $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                if (!empty($value)) {
                    $topics[$topic_key] = $value;
                }
            }
        }
        
        $non_empty_count = count(array_filter($topics));
        
        return [
            'topics' => $topics,
            'data_quality' => $non_empty_count >= 4 ? 'good' : ($non_empty_count >= 2 ? 'fair' : ($non_empty_count > 0 ? 'poor' : 'missing'))
        ];
    }
    
    /**
     * Handle saving authority hook components
     */
    public function handle_save_authority_hook_components() {
        if (!check_ajax_referer('generate_topics_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        $who = sanitize_text_field($_POST['who'] ?? '');
        $result = sanitize_text_field($_POST['result'] ?? '');
        $when = sanitize_text_field($_POST['when'] ?? '');
        $how = sanitize_text_field($_POST['how'] ?? '');
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID']);
            return;
        }
        
        $save_result = $this->save_authority_hook_components($entry_id, $who, $result, $when, $how);
        
        if ($save_result['success']) {
            wp_send_json_success($save_result);
        } else {
            wp_send_json_error(['message' => 'Failed to save authority hook components']);
        }
    }
    
    /**
     * STANDALONE: Get authority hook data from entry (independent operation)
     */
    private function get_authority_hook_data($entry_id) {
        if (!$this->formidable_service) {
            return [
                'who' => 'your audience',
                'result' => 'achieve their goals', 
                'when' => 'they need help',
                'how' => 'through your method',
                'complete' => 'I help your audience achieve their goals when they need help through your method.'
            ];
        }
        
        // Get authority hook components using Form 515 field IDs
        $components = [
            'who' => $this->formidable_service->get_field_value($entry_id, 10296),
            'result' => $this->formidable_service->get_field_value($entry_id, 10297),
            'when' => $this->formidable_service->get_field_value($entry_id, 10387),
            'how' => $this->formidable_service->get_field_value($entry_id, 10298),
            'complete' => $this->formidable_service->get_field_value($entry_id, 10358)
        ];
        
        // Provide defaults for empty components
        $components['who'] = $components['who'] ?: 'your audience';
        $components['result'] = $components['result'] ?: 'achieve their goals';
        $components['when'] = $components['when'] ?: 'they need help';
        $components['how'] = $components['how'] ?: 'through your method';
        
        // Build complete hook if missing
        if (empty($components['complete'])) {
            $components['complete'] = "I help {$components['who']} {$components['result']} when {$components['when']} {$components['how']}.";
        }
        
        return $components;
    }
    
    /**
     * STANDALONE: Enhanced authority hook save with unified nonce
     */
    public function handle_save_authority_hook_unified() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $who = isset($_POST['who']) ? sanitize_text_field($_POST['who']) : '';
        $result = isset($_POST['result']) ? sanitize_text_field($_POST['result']) : '';
        $when = isset($_POST['when']) ? sanitize_text_field($_POST['when']) : '';
        $how = isset($_POST['how']) ? sanitize_text_field($_POST['how']) : '';
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Entry ID is required']);
            return;
        }
        
        error_log("MKCG Topics: Saving authority hook for entry {$entry_id}");
        
        // Save authority hook components
        $save_result = $this->save_authority_hook_components($entry_id, $who, $result, $when, $how);
        
        if ($save_result['success']) {
            error_log("MKCG Topics: Authority hook saved successfully for entry {$entry_id}");
            
            wp_send_json_success([
                'message' => 'Authority hook saved successfully',
                'authority_hook' => $save_result['authority_hook'],
                'components' => [
                    'who' => $who,
                    'result' => $result,
                    'when' => $when,
                    'how' => $how
                ],
                'saved_fields' => $save_result['saved_fields']
            ]);
        } else {
            error_log("MKCG Topics: Failed to save authority hook for entry {$entry_id}");
            
            wp_send_json_error([
                'message' => 'Failed to save authority hook'
            ]);
        }
    }
    
    /**
     * ENHANCED: Save topics data with comprehensive validation - SAME AS QUESTIONS GENERATOR
     */
    public function handle_save_topics_data_ajax() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $topics_data = isset($_POST['topics']) ? $_POST['topics'] : null;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        // Validate topics data
        $validation_result = $this->validate_topics_data($topics_data);
        
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Topics data validation failed',
                'errors' => $validation_result['errors']
            ]);
            return;
        }
        
        $saved_topics = 0;
        $save_errors = [];
        
        // Save all 5 topics
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            if (isset($validation_result['normalized_data'][$topic_num])) {
                $topic_text = $validation_result['normalized_data'][$topic_num];
                
                if (!empty(trim($topic_text))) {
                    $result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_num, $topic_text);
                    
                    if ($result) {
                        $saved_topics++;
                        error_log("MKCG Topics: Saved topic {$topic_num}: '" . substr($topic_text, 0, 50) . "...");
                    } else {
                        $save_errors[] = "Failed to save Topic {$topic_num}";
                        error_log("MKCG Topics: Failed to save topic {$topic_num}");
                    }
                }
            }
        }
        
        if ($saved_topics > 0) {
            // Update timestamp
            update_post_meta($post_id, '_mkcg_topics_updated', time());
            
            error_log("MKCG Topics: Successfully saved {$saved_topics} topics to post {$post_id}");
            
            wp_send_json_success([
                'message' => "Successfully saved {$saved_topics} topics",
                'saved_topics' => $saved_topics,
                'post_id' => $post_id,
                'warnings' => $save_errors
            ]);
        } else {
            error_log("MKCG Topics: No topics were saved to post {$post_id}");
            
            wp_send_json_error([
                'message' => 'No topics were saved',
                'errors' => $save_errors
            ]);
        }
    }
    
    /**
     * ENHANCED: Validate topics data structure - SAME PATTERN AS QUESTIONS GENERATOR
     */
    private function validate_topics_data($topics_data) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => []
        ];
        
        if ($topics_data === null || $topics_data === '') {
            $validation['errors'][] = 'No topics data provided';
            return $validation;
        }
        
        // Handle JSON string
        if (is_string($topics_data)) {
            $decoded = json_decode($topics_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $topics_data = $decoded;
            } else {
                $validation['errors'][] = 'Invalid JSON format';
                return $validation;
            }
        }
        
        // Convert object to array if needed
        if (is_object($topics_data)) {
            $topics_data = (array) $topics_data;
        }
        
        // Must be array
        if (!is_array($topics_data)) {
            $validation['errors'][] = 'Topics data must be an array';
            return $validation;
        }
        
        $valid_topics = 0;
        
        // Validate and normalize each topic (check multiple possible keys)
        for ($i = 1; $i <= 5; $i++) {
            $topic_text = '';
            
            // Check various possible keys
            $possible_keys = ["topic_{$i}", $i, "topic{$i}", $i - 1]; // Also check 0-based indexing
            
            foreach ($possible_keys as $key) {
                if (isset($topics_data[$key]) && !empty($topics_data[$key])) {
                    $topic_text = $topics_data[$key];
                    break;
                }
            }
            
            if (is_string($topic_text)) {
                $sanitized = sanitize_textarea_field(trim($topic_text));
                $validation['normalized_data'][$i] = $sanitized;
                
                if (!empty($sanitized)) {
                    $valid_topics++;
                }
            } else {
                $validation['normalized_data'][$i] = '';
            }
        }
        
        if ($valid_topics === 0) {
            $validation['errors'][] = 'No valid topics found';
        } else {
            $validation['valid'] = true;
        }
        
        return $validation;
    }
    
    /**
     * CRITICAL FIX: AJAX handler for saving individual topics (inline editing) - SAME AS QUESTIONS GENERATOR
     */
    public function handle_save_topic_ajax() {
        // CRITICAL FIX: Use unified nonce strategy with proper validation
        $nonce_verified = false;
        $nonce_value = '';
        
        // Try multiple nonce fields with correct actions
        $nonce_checks = [
            ['field' => 'security', 'action' => 'mkcg_save_nonce'],
            ['field' => 'nonce', 'action' => 'mkcg_nonce'], 
            ['field' => 'save_nonce', 'action' => 'mkcg_save_nonce'],
            ['field' => 'mkcg_nonce', 'action' => 'mkcg_nonce']
        ];
        
        foreach ($nonce_checks as $check) {
            if (isset($_POST[$check['field']]) && !empty($_POST[$check['field']])) {
                $nonce_value = $_POST[$check['field']];
                if (wp_verify_nonce($nonce_value, $check['action'])) {
                    $nonce_verified = true;
                    error_log("MKCG Topic Save: ✅ Nonce verified using field '{$check['field']}' with action '{$check['action']}'");
                    break;
                }
            }
        }
        
        if (!$nonce_verified) {
            error_log('MKCG Topic Save: ❌ Security check failed - no valid nonce found');
            error_log('MKCG Topic Save: Available POST fields: ' . implode(', ', array_keys($_POST)));
            wp_send_json_error([
                'message' => 'Security check failed', 
                'debug' => 'Nonce verification failed - please refresh the page'
            ]);
            return;
        }
        
        // Extract and validate parameters
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 0;
        $topic_text = isset($_POST['topic_text']) ? sanitize_textarea_field($_POST['topic_text']) : '';
        
        error_log("MKCG Topic Save: Processing request - post_id: {$post_id}, topic_number: {$topic_number}, text_length: " . strlen($topic_text));
        
        // Enhanced parameter validation
        $validation_errors = [];
        
        if (!$post_id) {
            $validation_errors[] = 'Post ID is required';
        }
        
        if (!$topic_number || $topic_number < 1 || $topic_number > 5) {
            $validation_errors[] = 'Topic number must be between 1 and 5';
        }
        
        if (empty(trim($topic_text))) {
            $validation_errors[] = 'Topic text cannot be empty';
        } elseif (strlen(trim($topic_text)) < 5) {
            $validation_errors[] = 'Topic text must be at least 5 characters';
        } elseif (strlen(trim($topic_text)) > 500) {
            $validation_errors[] = 'Topic text cannot exceed 500 characters';
        }
        
        if (!empty($validation_errors)) {
            error_log('MKCG Topic Save: Validation failed: ' . implode(', ', $validation_errors));
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_errors),
                'errors' => $validation_errors
            ]);
            return;
        }
        
        // Verify post exists and is accessible
        $post = get_post($post_id);
        if (!$post) {
            error_log("MKCG Topic Save: Post {$post_id} does not exist");
            wp_send_json_error([
                'message' => 'Post not found',
                'debug' => "Post ID {$post_id} does not exist"
            ]);
            return;
        }
        
        // Check Formidable service availability
        if (!$this->formidable_service) {
            error_log('MKCG Topic Save: Formidable service not available');
            wp_send_json_error([
                'message' => 'Backend service not available',
                'debug' => 'Formidable service initialization failed'
            ]);
            return;
        }
        
        // Trim and prepare final text
        $final_topic_text = trim($topic_text);
        
        try {
            // Save topic to post meta using Formidable service
            $result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_number, $final_topic_text);
            
            if ($result) {
                // Also update topics timestamp for sync tracking
                update_post_meta($post_id, '_mkcg_topics_updated', time());
                
                error_log("MKCG Topic Save: SUCCESS - Saved topic {$topic_number} to post {$post_id}: '" . substr($final_topic_text, 0, 50) . (strlen($final_topic_text) > 50 ? '...' : '') . "'");
                
                wp_send_json_success([
                    'message' => 'Topic saved successfully',
                    'post_id' => $post_id,
                    'topic_number' => $topic_number,
                    'topic_text' => $final_topic_text,
                    'char_count' => strlen($final_topic_text),
                    'timestamp' => time()
                ]);
            } else {
                error_log("MKCG Topic Save: FAILED - save_single_topic_to_post returned false for topic {$topic_number}");
                wp_send_json_error([
                    'message' => 'Failed to save topic to database',
                    'debug' => 'Backend save operation failed'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("MKCG Topic Save: EXCEPTION - " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Save failed due to server error',
                'debug' => 'Exception occurred during save: ' . $e->getMessage()
            ]);
        }
    }
}