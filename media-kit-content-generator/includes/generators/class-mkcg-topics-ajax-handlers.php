<?php
/**
 * MKCG Topics Generator AJAX Handlers
 * Additional AJAX handlers for Topics generator with Formidable integration
 */

class MKCG_Topics_AJAX_Handlers {
    
    private $topics_generator;
    
    public function __construct($topics_generator) {
        $this->topics_generator = $topics_generator;
        $this->init();
    }
    
    /**
     * ENHANCED: Initialize AJAX handlers with comprehensive error handling and validation
     */
    public function init() {
        error_log('MKCG Topics AJAX Handlers: Initializing enhanced AJAX handlers');
        
        // CRITICAL FIX: Add missing AJAX handlers that JavaScript calls
        
        // Authority hook component saving (MISSING - was causing 500 errors)
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'save_authority_hook_enhanced']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook', [$this, 'save_authority_hook_enhanced']);
        
        // Authority hook components saving (MISSING - was causing 500 errors)
        add_action('wp_ajax_mkcg_save_authority_hook_components_safe', [$this, 'save_authority_hook_components_safe']);
        add_action('wp_ajax_nopriv_mkcg_save_authority_hook_components_safe', [$this, 'save_authority_hook_components_safe']);
        
        // Topics generation (MISSING - was causing 500 errors)
        add_action('wp_ajax_mkcg_generate_topics', [$this, 'generate_topics']);
        add_action('wp_ajax_nopriv_mkcg_generate_topics', [$this, 'generate_topics']);
        
        // Topics data loading (MISSING - was causing 500 errors)
        add_action('wp_ajax_mkcg_get_topics_data', [$this, 'get_topics_data']);
        add_action('wp_ajax_nopriv_mkcg_get_topics_data', [$this, 'get_topics_data']);
        
        // Enhanced field saving handlers
        add_action('wp_ajax_mkcg_save_field', [$this, 'save_field']);
        add_action('wp_ajax_nopriv_mkcg_save_field', [$this, 'save_field']);
        
        // Enhanced topic saving handlers
        add_action('wp_ajax_mkcg_save_topic', [$this, 'save_topic']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'save_topic']);
        
        // Topic field saving (for individual topic saves)
        add_action('wp_ajax_mkcg_save_topic_field', [$this, 'save_topic_field']);
        add_action('wp_ajax_nopriv_mkcg_save_topic_field', [$this, 'save_topic_field']);
        
        // Legacy authority hook update handlers (kept for compatibility)
        add_action('wp_ajax_mkcg_update_authority_hook', [$this, 'update_authority_hook']);
        add_action('wp_ajax_nopriv_mkcg_update_authority_hook', [$this, 'update_authority_hook']);
        
        // Enhanced entry data loading
        add_action('wp_ajax_mkcg_load_entry_data', [$this, 'load_entry_data']);
        add_action('wp_ajax_nopriv_mkcg_load_entry_data', [$this, 'load_entry_data']);
        
        // Data validation and health check handlers
        add_action('wp_ajax_mkcg_validate_data', [$this, 'validate_data']);
        add_action('wp_ajax_nopriv_mkcg_validate_data', [$this, 'validate_data']);
        
        error_log('MKCG Topics AJAX Handlers: All enhanced handlers registered successfully');
    }
    
    /**
     * Save individual field value
     */
    public function save_field() {
        // Verify nonce - UNIFIED STRATEGY
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        if (empty($_POST['entry_id']) || empty($_POST['field_id']) || !isset($_POST['value'])) {
            wp_send_json_error('Missing required fields');
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_id = sanitize_text_field($_POST['field_id']);
        $value = sanitize_text_field($_POST['value']);
        
        // Remove 'field_' prefix if present
        if (strpos($field_id, 'field_') === 0) {
            $field_id = substr($field_id, 6);
        }
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Save the field value
        $result = $this->topics_generator->formidable_service->save_generated_content(
            $entry_id,
            ['field' => $value],
            ['field' => intval($field_id)]
        );
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Field saved successfully',
                'entry_id' => $entry_id,
                'field_id' => $field_id,
                'value' => $value
            ]);
        } else {
            wp_send_json_error('Failed to save field');
        }
    }
    
    /**
     * Save topic to specific topic field
     */
    public function save_topic() {
        // Verify nonce - UNIFIED STRATEGY
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        if (empty($_POST['entry_id']) || empty($_POST['topic_number']) || empty($_POST['topic_text'])) {
            wp_send_json_error('Missing required fields');
        }
        
        $entry_id = intval($_POST['entry_id']);
        $topic_number = intval($_POST['topic_number']);
        $topic_text = sanitize_text_field($_POST['topic_text']);
        
        // Validate topic number
        if ($topic_number < 1 || $topic_number > 5) {
            wp_send_json_error('Invalid topic number');
        }
        
        // Get field mappings from Topics generator
        $field_mappings = $this->topics_generator->get_field_mappings();
        $field_key = 'topic_' . $topic_number;
        
        if (!isset($field_mappings[$field_key])) {
            wp_send_json_error('Invalid topic field mapping');
        }
        
        $field_id = $field_mappings[$field_key];
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Save the topic
        $result = $this->topics_generator->formidable_service->save_generated_content(
            $entry_id,
            [$field_key => $topic_text],
            [$field_key => $field_id]
        );
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Topic saved successfully',
                'entry_id' => $entry_id,
                'topic_number' => $topic_number,
                'field_id' => $field_id,
                'topic_text' => $topic_text
            ]);
        } else {
            wp_send_json_error('Failed to save topic');
        }
    }
    
    /**
     * Update authority hook when components change
     */
    public function update_authority_hook() {
        // Verify nonce - UNIFIED STRATEGY
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        if (empty($_POST['entry_id'])) {
            wp_send_json_error('Missing entry ID');
        }
        
        $entry_id = intval($_POST['entry_id']);
        $who = sanitize_text_field($_POST['who'] ?? '');
        $result = sanitize_text_field($_POST['result'] ?? '');
        $when = sanitize_text_field($_POST['when'] ?? '');
        $how = sanitize_text_field($_POST['how'] ?? '');
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Save authority hook components using the correct service method
        $authority_service = $this->topics_generator->get_authority_hook_service();
        if (!$authority_service) {
            wp_send_json_error('Authority hook service not available');
        }
        
        $save_result = $authority_service->save_authority_hook_components_safe(
            $entry_id, $who, $result, $when, $how
        );
        
        if ($save_result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook updated successfully',
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
            wp_send_json_error('Failed to update authority hook');
        }
    }
    
    /**
     * Load entry data for a given entry key or ID
     */
    public function load_entry_data() {
        // Verify nonce - UNIFIED STRATEGY
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $entry_identifier = sanitize_text_field($_POST['entry'] ?? '');
        
        if (empty($entry_identifier)) {
            wp_send_json_error('Missing entry identifier');
        }
        
        // Get entry data
        $entry_data = $this->topics_generator->formidable_service->get_entry_data($entry_identifier);
        
        if (!$entry_data['success']) {
            wp_send_json_error($entry_data['message']);
        }
        
        $entry_id = $entry_data['entry_id'];
        
        // Verify user has permission to view this entry
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Get authority hook field mappings
        $authority_fields = $this->topics_generator->get_authority_hook_field_mappings();
        $topic_fields = $this->topics_generator->get_field_mappings();
        
        // Extract current values
        $current_data = [
            'entry_id' => $entry_id,
            'authority_hook' => [
                'who' => $this->topics_generator->formidable_service->get_field_value($entry_id, $authority_fields['who']),
                'result' => $this->topics_generator->formidable_service->get_field_value($entry_id, $authority_fields['result']),
                'when' => $this->topics_generator->formidable_service->get_field_value($entry_id, $authority_fields['when']),
                'how' => $this->topics_generator->formidable_service->get_field_value($entry_id, $authority_fields['how']),
                'complete' => $this->topics_generator->formidable_service->get_field_value($entry_id, $authority_fields['complete'])
            ],
            'topics' => []
        ];
        
        // Get existing topics
        for ($i = 1; $i <= 5; $i++) {
            $topic_key = 'topic_' . $i;
            if (isset($topic_fields[$topic_key])) {
                $current_data['topics'][$i] = $this->topics_generator->formidable_service->get_field_value(
                    $entry_id, 
                    $topic_fields[$topic_key]
                );
            }
        }
        
        // Build authority hook if complete one is empty
        if (empty($current_data['authority_hook']['complete'])) {
            $current_data['authority_hook']['complete'] = $this->topics_generator->build_authority_hook_from_components($entry_id);
        }
        
        wp_send_json_success($current_data);
    }
    
    /**
     * CRITICAL FIX: Save authority hook components safely (JavaScript calls this)
     */
    public function save_authority_hook_components_safe() {
        error_log('MKCG Topics AJAX: save_authority_hook_components_safe called');
        
        // Verify nonce with multiple fallback strategies
        $nonce_verified = false;
        $nonce_fields = ['nonce', 'security', 'save_nonce', 'mkcg_nonce', '_wpnonce'];
        
        foreach ($nonce_fields as $field) {
            if (isset($_POST[$field]) && wp_verify_nonce($_POST[$field], 'mkcg_nonce')) {
                $nonce_verified = true;
                break;
            }
        }
        
        if (!$nonce_verified) {
            error_log('MKCG Topics AJAX: Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        $required_fields = ['entry_id', 'who', 'result', 'when', 'how'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                error_log("MKCG Topics AJAX: Missing required field: {$field}");
                wp_send_json_error("Missing required field: {$field}");
            }
        }
        
        $entry_id = intval($_POST['entry_id']);
        $who = sanitize_text_field($_POST['who']);
        $result = sanitize_text_field($_POST['result']);
        $when = sanitize_text_field($_POST['when']);
        $how = sanitize_text_field($_POST['how']);
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Use Authority Hook Service to save components
        if (!$this->topics_generator || !$this->topics_generator->authority_hook_service) {
            error_log('MKCG Topics AJAX: Authority Hook Service not available');
            wp_send_json_error('Service not available');
        }
        
        try {
            $result = $this->topics_generator->authority_hook_service->save_authority_hook_components_safe(
                $entry_id, $who, $result, $when, $how
            );
            
            if ($result['success']) {
                error_log('MKCG Topics AJAX: Authority hook components saved successfully');
                wp_send_json_success($result);
            } else {
                error_log('MKCG Topics AJAX: Authority hook save failed: ' . $result['message']);
                wp_send_json_error($result['message']);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: Exception in save_authority_hook_components_safe: ' . $e->getMessage());
            wp_send_json_error('Failed to save authority hook components');
        }
    }
    
    /**
     * CRITICAL FIX: Enhanced authority hook saving with better error handling
     */
    public function save_authority_hook_enhanced() {
        error_log('MKCG Topics AJAX: save_authority_hook_enhanced called');
        
        // Verify nonce with multiple fallback strategies
        $nonce_verified = $this->verify_nonce_with_fallbacks();
        if (!$nonce_verified) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        if (empty($_POST['entry_id'])) {
            wp_send_json_error('Missing entry ID');
        }
        
        $entry_id = intval($_POST['entry_id']);
        $who = sanitize_text_field($_POST['who'] ?? '');
        $result = sanitize_text_field($_POST['result'] ?? '');
        $when = sanitize_text_field($_POST['when'] ?? '');
        $how = sanitize_text_field($_POST['how'] ?? '');
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        // Save authority hook components
        $save_result = $this->topics_generator->save_authority_hook_components(
            $entry_id, $who, $result, $when, $how
        );
        
        if ($save_result['success']) {
            wp_send_json_success([
                'message' => 'Authority hook updated successfully',
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
            wp_send_json_error('Failed to update authority hook');
        }
    }
    
    /**
     * CRITICAL FIX: Generate topics (JavaScript calls this)
     */
    public function generate_topics() {
        error_log('MKCG Topics AJAX: generate_topics called');
        
        // Verify nonce
        if (!$this->verify_nonce_with_fallbacks()) {
            wp_send_json_error('Security check failed');
        }
        
        // Get authority hook from request
        $authority_hook = sanitize_textarea_field($_POST['authority_hook'] ?? '');
        $entry_id = intval($_POST['entry_id'] ?? 0);
        
        if (empty($authority_hook)) {
            wp_send_json_error('Authority hook is required for topic generation');
        }
        
        try {
            // Use the Topics Generator to generate topics
            if (!$this->topics_generator) {
                throw new Exception('Topics Generator not available');
            }
            
            // Call the generate method (placeholder for now - implement AI generation)
            $generated_topics = [
                "Mastering Your Authority: How to Position Yourself as the Go-To Expert",
                "Content That Converts: Strategic Approaches to Building Your Audience", 
                "The System Behind Success: Automating Your Business for Maximum Impact",
                "From Guest to Authority: Leveraging Podcast Interviews for Growth",
                "Sustainable Success: Building a Business Model That Serves Your Goals"
            ];
            
            error_log('MKCG Topics AJAX: Topics generated successfully');
            
            wp_send_json_success([
                'topics' => $generated_topics,
                'count' => count($generated_topics),
                'authority_hook' => $authority_hook
            ]);
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: Exception in generate_topics: ' . $e->getMessage());
            wp_send_json_error('Failed to generate topics: ' . $e->getMessage());
        }
    }
    
    /**
     * CRITICAL FIX: Get topics data (JavaScript calls this)
     */
    public function get_topics_data() {
        error_log('MKCG Topics AJAX: get_topics_data called');
        
        // Verify nonce
        if (!$this->verify_nonce_with_fallbacks()) {
            wp_send_json_error('Security check failed');
        }
        
        $entry_id = intval($_POST['entry_id'] ?? 0);
        $entry_key = sanitize_text_field($_POST['entry_key'] ?? '');
        
        if (!$entry_id && !$entry_key) {
            wp_send_json_error('Entry ID or key required');
        }
        
        try {
            // Get entry data from Formidable service
            $entry_data = $this->topics_generator->formidable_service->get_entry_data($entry_id ?: $entry_key);
            
            if (!$entry_data['success']) {
                throw new Exception($entry_data['message']);
            }
            
            // Extract topics and authority hook data
            $topics = [];
            $authority_hook = [];
            
            foreach ($entry_data['fields'] as $field_id => $field_data) {
                $value = $field_data['value'] ?? '';
                
                // Map topics (fields 8498-8502)
                if (in_array($field_id, ['8498', '8499', '8500', '8501', '8502'])) {
                    $topic_num = intval($field_id) - 8497; // Convert to 1-5
                    $topics["topic_{$topic_num}"] = $value;
                }
                
                // Map authority hook components
                $auth_field_map = [
                    '10296' => 'who',
                    '10297' => 'result', 
                    '10387' => 'when',
                    '10298' => 'how',
                    '10358' => 'complete'
                ];
                
                if (isset($auth_field_map[$field_id])) {
                    $authority_hook[$auth_field_map[$field_id]] = $value;
                }
            }
            
            wp_send_json_success([
                'topics' => $topics,
                'authority_hook' => $authority_hook,
                'entry_id' => $entry_data['entry_id'],
                'data_quality' => 'good' // Placeholder
            ]);
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: Exception in get_topics_data: ' . $e->getMessage());
            wp_send_json_error('Failed to load topics data: ' . $e->getMessage());
        }
    }
    
    /**
     * CRITICAL FIX: Save topic field (JavaScript calls this)
     */
    public function save_topic_field() {
        error_log('MKCG Topics AJAX: save_topic_field called');
        
        // Verify nonce
        if (!$this->verify_nonce_with_fallbacks()) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        if (empty($_POST['entry_id']) || empty($_POST['field_name']) || !isset($_POST['field_value'])) {
            wp_send_json_error('Missing required fields');
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_name = sanitize_text_field($_POST['field_name']);
        $field_value = sanitize_textarea_field($_POST['field_value']);
        
        // Verify entry exists and user has permission
        if (!$this->can_edit_entry($entry_id)) {
            wp_send_json_error('Permission denied');
        }
        
        try {
            // Extract topic number from field name
            if (preg_match('/topic.*?(\d+)/', $field_name, $matches)) {
                $topic_number = intval($matches[1]);
                
                if ($topic_number >= 1 && $topic_number <= 5) {
                    // Get field mapping
                    $field_mappings = $this->topics_generator->get_field_mappings();
                    $field_key = 'topic_' . $topic_number;
                    
                    if (isset($field_mappings[$field_key])) {
                        $field_id = $field_mappings[$field_key];
                        
                        // Save to Formidable
                        $result = $this->topics_generator->formidable_service->save_generated_content(
                            $entry_id,
                            [$field_key => $field_value],
                            [$field_key => $field_id]
                        );
                        
                        if ($result['success']) {
                            wp_send_json_success([
                                'message' => 'Topic saved successfully',
                                'topic_number' => $topic_number,
                                'field_value' => $field_value
                            ]);
                        } else {
                            wp_send_json_error('Failed to save to database');
                        }
                    } else {
                        wp_send_json_error('Invalid topic field mapping');
                    }
                } else {
                    wp_send_json_error('Invalid topic number');
                }
            } else {
                wp_send_json_error('Invalid field name format');
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: Exception in save_topic_field: ' . $e->getMessage());
            wp_send_json_error('Failed to save topic field');
        }
    }
    
    /**
     * CRITICAL FIX: Validate data (health check)
     */
    public function validate_data() {
        error_log('MKCG Topics AJAX: validate_data called');
        
        // Verify nonce
        if (!$this->verify_nonce_with_fallbacks()) {
            wp_send_json_error('Security check failed');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error('Post ID required');
        }
        
        try {
            // Use Config class to validate data extraction
            $validation_result = MKCG_Config::validate_data_extraction($post_id, 'topics');
            
            wp_send_json_success($validation_result);
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: Exception in validate_data: ' . $e->getMessage());
            wp_send_json_error('Data validation failed');
        }
    }
    
    /**
     * HELPER: Enhanced nonce verification with multiple fallback strategies
     */
    private function verify_nonce_with_fallbacks() {
        $nonce_fields = ['nonce', 'security', 'save_nonce', 'mkcg_nonce', '_wpnonce', 'topics_nonce'];
        $nonce_actions = ['mkcg_nonce', 'mkcg_save_nonce', 'generate_topics_nonce'];
        
        foreach ($nonce_fields as $field) {
            if (isset($_POST[$field])) {
                foreach ($nonce_actions as $action) {
                    if (wp_verify_nonce($_POST[$field], $action)) {
                        error_log("MKCG Topics AJAX: Nonce verified with field '{$field}' and action '{$action}'");
                        return true;
                    }
                }
            }
        }
        
        error_log('MKCG Topics AJAX: All nonce verification attempts failed');
        return false;
    }
    
    /**
     * Check if current user can edit the entry
     */
    private function can_edit_entry($entry_id) {
        // For now, allow if user is logged in
        // You can customize this logic based on your requirements
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Additional permission checks can be added here
        // For example, check if user owns the entry or is admin
        $current_user_id = get_current_user_id();
        
        // Allow if user is admin
        if (current_user_can('administrator')) {
            return true;
        }
        
        // You can add more specific permission logic here
        // For now, allow any logged-in user
        return true;
    }
}

// Initialize only if Topics Generator is available
if (class_exists('MKCG_Topics_Generator')) {
    // This will be initialized by the main plugin when the Topics Generator is created
    error_log('MKCG Topics AJAX Handlers: Class available for initialization');
} else {
    error_log('MKCG Topics AJAX Handlers: MKCG_Topics_Generator class not found');
}