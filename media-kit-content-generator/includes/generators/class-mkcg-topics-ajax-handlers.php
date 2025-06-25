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
     * Initialize AJAX handlers
     */
    public function init() {
        // Field saving handlers
        add_action('wp_ajax_mkcg_save_field', [$this, 'save_field']);
        add_action('wp_ajax_nopriv_mkcg_save_field', [$this, 'save_field']);
        
        // Topic saving handlers
        add_action('wp_ajax_mkcg_save_topic', [$this, 'save_topic']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'save_topic']);
        
        // Authority hook update handlers
        add_action('wp_ajax_mkcg_update_authority_hook', [$this, 'update_authority_hook']);
        add_action('wp_ajax_nopriv_mkcg_update_authority_hook', [$this, 'update_authority_hook']);
        
        // Load entry data handlers
        add_action('wp_ajax_mkcg_load_entry_data', [$this, 'load_entry_data']);
        add_action('wp_ajax_nopriv_mkcg_load_entry_data', [$this, 'load_entry_data']);
    }
    
    /**
     * Save individual field value
     */
    public function save_field() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_topics_nonce')) {
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_topics_nonce')) {
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_topics_nonce')) {
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
     * Load entry data for a given entry key or ID
     */
    public function load_entry_data() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_topics_nonce')) {
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
}