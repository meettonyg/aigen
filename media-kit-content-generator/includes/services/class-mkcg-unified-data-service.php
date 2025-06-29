<?php
/**
 * MKCG Unified Data Service
 * 
 * Eliminates 80-90% code duplication by centralizing all data operations.
 * Implements Gemini's recommendations: centralized config, standardized responses, stateless design.
 */

class MKCG_Unified_Data_Service {
    
    private $formidable_service;
    private $field_mappings;
    private $supported_data_types;
    
    /**
     * Constructor - Initialize with existing Formidable service
     */
    public function __construct($formidable_service) {
        $this->formidable_service = $formidable_service;
        $this->init_configuration();
        $this->init_ajax_handlers();
    }
    
    /**
     * 🔧 INITIALIZE CONFIGURATION - Use centralized config
     */
    private function init_configuration() {
        $this->field_mappings = MKCG_Config::get_field_mappings();
        $this->supported_data_types = array_keys(MKCG_Config::get_supported_data_types());
        
        // Validate configuration on initialization
        $validation = MKCG_Config::validate_configuration();
        if (!$validation['valid']) {
            error_log('MKCG Unified Service: Configuration validation failed: ' . implode(', ', $validation['errors']));
        }
        
        if (!empty($validation['warnings'])) {
            error_log('MKCG Unified Service: Configuration warnings: ' . implode(', ', $validation['warnings']));
        }
    }
    
    /**
     * 🎯 UNIFIED AJAX HANDLERS - Single endpoints for all generators
     */
    private function init_ajax_handlers() {
        $actions = MKCG_Config::get_ajax_actions();
        
        // Unified data retrieval
        add_action('wp_ajax_' . $actions['get_data'], [$this, 'handle_get_data_unified']);
        add_action('wp_ajax_nopriv_' . $actions['get_data'], [$this, 'handle_get_data_unified']);
        
        // Unified data saving
        add_action('wp_ajax_' . $actions['save_data'], [$this, 'handle_save_data_unified']);
        add_action('wp_ajax_nopriv_' . $actions['save_data'], [$this, 'handle_save_data_unified']);
        
        // Unified single item save (topics/questions)
        add_action('wp_ajax_' . $actions['save_item'], [$this, 'handle_save_item_unified']);
        add_action('wp_ajax_nopriv_' . $actions['save_item'], [$this, 'handle_save_item_unified']);
        
        // Unified authority hook management
        add_action('wp_ajax_' . $actions['authority_hook'], [$this, 'handle_authority_hook_unified']);
        add_action('wp_ajax_nopriv_' . $actions['authority_hook'], [$this, 'handle_authority_hook_unified']);
    }
    
    /**
     * 🔍 UNIFIED DATA RETRIEVAL - Single method for all data types
     */
    public function handle_get_data_unified() {
        // Unified nonce verification
        if (!$this->verify_request_security()) {
            $this->send_standardized_response(['success' => false, 'message' => 'Security check failed']);
            return;
        }
        
        $data_type = isset($_POST['data_type']) ? sanitize_text_field($_POST['data_type']) : '';
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        error_log("MKCG Unified Service: Get data request - type: {$data_type}, entry_id: {$entry_id}, post_id: {$post_id}");
        
        // Validate data type
        if (!in_array($data_type, $this->supported_data_types)) {
            $this->send_standardized_response([
                'success' => false, 
                'message' => 'Unsupported data type: ' . $data_type,
                'errors' => ['Supported types: ' . implode(', ', $this->supported_data_types)]
            ]);
            return;
        }
        
        // Route to appropriate handler
        $result = $this->get_data_by_type($data_type, $entry_id, $entry_key, $post_id);
        
        // Use standardized response format
        $this->send_standardized_response($result);
    }
    
    /**
     * 💾 UNIFIED DATA SAVING - Single method for all data types
     */
    public function handle_save_data_unified() {
        if (!$this->verify_request_security()) {
            $this->send_standardized_response(['success' => false, 'message' => 'Security check failed']);
            return;
        }
        
        // Handle both JSON and form-encoded requests
        $input_data = $this->get_request_data();
        
        $data_type = isset($input_data['data_type']) ? sanitize_text_field($input_data['data_type']) : '';
        $post_id = isset($input_data['post_id']) ? intval($input_data['post_id']) : 0;
        $entry_id = isset($input_data['entry_id']) ? intval($input_data['entry_id']) : 0;
        $data_payload = isset($input_data['data']) ? $input_data['data'] : null;
        
        error_log("MKCG Unified Service: Save data request - type: {$data_type}, post_id: {$post_id}, entry_id: {$entry_id}");
        
        // Validate inputs
        $validation = $this->validate_save_request($data_type, $post_id, $data_payload);
        if (!$validation['valid']) {
            $this->send_standardized_response($validation);
            return;
        }
        
        // Route to appropriate save handler
        $result = $this->save_data_by_type($data_type, $validation['normalized_data'], $post_id, $entry_id);
        
        // Use standardized response format
        $this->send_standardized_response($result);
    }
    
    /**
     * 📝 UNIFIED SINGLE ITEM SAVE - For inline editing
     */
    public function handle_save_item_unified() {
        if (!$this->verify_request_security()) {
            $this->send_standardized_response(['success' => false, 'message' => 'Security check failed']);
            return;
        }
        
        $data_type = isset($_POST['data_type']) ? sanitize_text_field($_POST['data_type']) : '';
        $item_number = isset($_POST['item_number']) ? intval($_POST['item_number']) : 0;
        $item_text = isset($_POST['item_text']) ? sanitize_textarea_field($_POST['item_text']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        error_log("MKCG Unified Service: Save single item - type: {$data_type}, item: {$item_number}, post_id: {$post_id}");
        
        // Route to appropriate single item save
        $result = $this->save_single_item($data_type, $item_number, $item_text, $post_id, $entry_id);
        
        // Use standardized response format
        $this->send_standardized_response($result);
    }
    
    /**
     * 👤 UNIFIED AUTHORITY HOOK MANAGEMENT
     */
    public function handle_authority_hook_unified() {
        if (!$this->verify_request_security()) {
            $this->send_standardized_response(['success' => false, 'message' => 'Security check failed']);
            return;
        }
        
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'save';
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        if ($action_type === 'get') {
            $result = $this->get_authority_hook_data($entry_id, '');
        } else {
            $who = isset($_POST['who']) ? sanitize_text_field($_POST['who']) : '';
            $result_text = isset($_POST['result']) ? sanitize_text_field($_POST['result']) : '';
            $when = isset($_POST['when']) ? sanitize_text_field($_POST['when']) : '';
            $how = isset($_POST['how']) ? sanitize_text_field($_POST['how']) : '';
            
            $hook_data = [
                'who' => $who,
                'result' => $result_text,
                'when' => $when,
                'how' => $how
            ];
            
            $result = $this->save_authority_hook_data($hook_data, 0, $entry_id);
        }
        
        $this->send_standardized_response($result);
    }
    
    /**
     * 📤 STANDARDIZED RESPONSE HANDLER - Consistent JSON structure
     */
    private function send_standardized_response($result) {
        if ($result['success']) {
            $response = MKCG_Config::get_response_template('success');
            $response['data']['message'] = $result['message'] ?? 'Operation completed successfully';
            $response['data']['items'] = $result['items'] ?? $result['data'] ?? [];
            $response['data']['count'] = $result['count'] ?? 0;
            $response['data']['post_id'] = $result['post_id'] ?? 0;
            $response['data']['warnings'] = $result['warnings'] ?? [];
            $response['data']['metadata'] = $result['metadata'] ?? [];
            
            wp_send_json($response);
        } else {
            $response = MKCG_Config::get_response_template('error');
            $response['data']['message'] = $result['message'] ?? 'Operation failed';
            $response['data']['errors'] = $result['errors'] ?? [$result['message'] ?? 'Unknown error'];
            $response['data']['debug'] = $result['debug'] ?? null;
            $response['data']['validation_errors'] = $result['validation_errors'] ?? [];
            
            wp_send_json($response);
        }
    }
    
    /**
     * 🔐 UNIFIED SECURITY VERIFICATION - Consistent nonce checking
     */
    private function verify_request_security() {
        $security_config = MKCG_Config::get_security_config();
        $nonce_actions = $security_config['nonce_actions'];
        $nonce_fields = $security_config['nonce_fields'];
        
        foreach ($nonce_fields as $field_name) {
            if (isset($_POST[$field_name]) && !empty($_POST[$field_name])) {
                foreach ($nonce_actions as $action_name) {
                    if (wp_verify_nonce($_POST[$field_name], $action_name)) {
                        error_log("MKCG Unified Service: ✅ Nonce verified using field '{$field_name}' with action '{$action_name}'");
                        return true;
                    }
                }
            }
        }
        
        error_log('MKCG Unified Service: ❌ Security check failed - no valid nonce found');
        return false;
    }
    
    /**
     * 🔍 DATA TYPE ROUTER - Get data based on type
     */
    private function get_data_by_type($data_type, $entry_id, $entry_key, $post_id) {
        switch ($data_type) {
            case 'topics':
                return $this->get_topics_data($entry_id, $entry_key, $post_id);
                
            case 'questions':
                return $this->get_questions_data($entry_id, $entry_key, $post_id);
                
            case 'authority_hook':
                return $this->get_authority_hook_data($entry_id, $entry_key);
                
            default:
                return ['success' => false, 'message' => 'Unknown data type: ' . $data_type];
        }
    }
    
    /**
     * 💾 DATA TYPE ROUTER - Save data based on type
     */
    private function save_data_by_type($data_type, $data_payload, $post_id, $entry_id) {
        switch ($data_type) {
            case 'topics':
                return $this->save_topics_data($data_payload, $post_id, $entry_id);
                
            case 'questions':
                return $this->save_questions_data($data_payload, $post_id, $entry_id);
                
            case 'authority_hook':
                return $this->save_authority_hook_data($data_payload, $post_id, $entry_id);
                
            default:
                return ['success' => false, 'message' => 'Unknown data type: ' . $data_type];
        }
    }
    
    /**
     * ✅ UNIFIED VALIDATION - Consistent data validation patterns
     */
    private function validate_save_request($data_type, $post_id, $data_payload) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => null
        ];
        
        // Common validations
        if (!in_array($data_type, $this->supported_data_types)) {
            $validation['errors'][] = 'Unsupported data type: ' . $data_type;
            return $validation;
        }
        
        if (!$post_id) {
            $validation['errors'][] = 'Post ID is required';
            return $validation;
        }
        
        if ($data_payload === null) {
            $validation['errors'][] = 'Data payload is required';
            return $validation;
        }
        
        // Type-specific validation using centralized rules
        $validation_rules = MKCG_Config::get_validation_rules($data_type);
        if (!$validation_rules) {
            $validation['errors'][] = 'No validation rules available for data type: ' . $data_type;
            return $validation;
        }
        
        return $this->validate_data_by_type($data_type, $data_payload, $validation_rules);
    }
    
    /**
     * 📊 TYPE-SPECIFIC DATA OPERATIONS
     */
    
    /**
     * Get topics data - moved from Topics Generator
     */
    public function get_topics_data($entry_id, $entry_key = '', $post_id = 0) {
        error_log("MKCG Unified Service: Getting topics data - entry_id: {$entry_id}, post_id: {$post_id}");
        
        // If entry_key provided, resolve to entry_id
        if ($entry_key && !$entry_id) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if (!$entry_data['success']) {
                return ['success' => false, 'message' => 'Entry not found: ' . $entry_key];
            }
            $entry_id = $entry_data['entry_id'];
        }
        
        // Get post_id if not provided
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            return [
                'success' => false,
                'message' => 'No custom post found for this entry',
                'debug' => 'Entry ' . $entry_id . ' has no associated post'
            ];
        }
        
        // Get topics from post meta using centralized field mapping
        $field_config = $this->field_mappings['topics'];
        $topics = [];
        $quality_score = 0;
        
        for ($i = 1; $i <= $field_config['max_items']; $i++) {
            $meta_key = sprintf(MKCG_Config::get_meta_key_pattern('topics'), $i);
            $topic_text = get_post_meta($post_id, $meta_key, true);
            
            $topics[$i] = $topic_text ? trim($topic_text) : '';
            
            if (!empty($topics[$i])) {
                $quality_score += strlen($topics[$i]) > 20 ? 20 : 10;
            }
        }
        
        $non_empty_count = count(array_filter($topics));
        $data_quality = $this->assess_data_quality($non_empty_count, $field_config['max_items'], $quality_score);
        
        return [
            'success' => true,
            'items' => $topics,
            'count' => $non_empty_count,
            'post_id' => $post_id,
            'data_quality' => $data_quality,
            'metadata' => [
                'total_topics' => $field_config['max_items'],
                'quality_score' => $quality_score,
                'last_updated' => get_post_meta($post_id, '_mkcg_topics_updated', true)
            ]
        ];
    }
    
    /**
     * Save topics data - moved from Topics Generator
     */
    public function save_topics_data($topics_data, $post_id, $entry_id = 0) {
        error_log("MKCG Unified Service: Saving topics data to post {$post_id}");
        
        $field_config = $this->field_mappings['topics'];
        $saved_count = 0;
        $warnings = [];
        
        for ($i = 1; $i <= $field_config['max_items']; $i++) {
            $topic_text = isset($topics_data[$i]) ? trim($topics_data[$i]) : '';
            
            if (!empty($topic_text)) {
                $meta_key = sprintf(MKCG_Config::get_meta_key_pattern('topics'), $i);
                $result = update_post_meta($post_id, $meta_key, $topic_text);
                
                if ($result !== false) {
                    $saved_count++;
                } else {
                    $warnings[] = "Failed to save topic {$i}";
                }
                
                // Also save to Formidable if entry_id provided
                if ($entry_id && isset($field_config['fields']['topic_' . $i])) {
                    $field_id = $field_config['fields']['topic_' . $i];
                    $this->formidable_service->save_generated_content(
                        $entry_id,
                        ['topic_' . $i => $topic_text],
                        ['topic_' . $i => $field_id]
                    );
                }
            }
        }
        
        // Update timestamp
        update_post_meta($post_id, '_mkcg_topics_updated', time());
        
        return [
            'success' => $saved_count > 0,
            'message' => "Successfully saved {$saved_count} topics",
            'count' => $saved_count,
            'post_id' => $post_id,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Get questions data - moved from Questions Generator
     */
    public function get_questions_data($entry_id, $entry_key = '', $post_id = 0) {
        error_log("MKCG Unified Service: Getting questions data - entry_id: {$entry_id}, post_id: {$post_id}");
        
        // Similar logic to topics but for questions
        if ($entry_key && !$entry_id) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if (!$entry_data['success']) {
                return ['success' => false, 'message' => 'Entry not found: ' . $entry_key];
            }
            $entry_id = $entry_data['entry_id'];
        }
        
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            return [
                'success' => false,
                'message' => 'No custom post found for this entry'
            ];
        }
        
        // Get questions from post meta - all 25 questions
        $field_config = $this->field_mappings['questions'];
        $questions_by_topic = [];
        $total_found = 0;
        
        for ($topic = 1; $topic <= 5; $topic++) {
            $questions_by_topic[$topic] = [];
            
            for ($q = 1; $q <= $field_config['items_per_group']; $q++) {
                $question_number = (($topic - 1) * 5) + $q;
                $meta_key = sprintf(MKCG_Config::get_meta_key_pattern('questions'), $question_number);
                $question_text = get_post_meta($post_id, $meta_key, true);
                
                $questions_by_topic[$topic][] = $question_text ? trim($question_text) : '';
                
                if (!empty($question_text)) {
                    $total_found++;
                }
            }
        }
        
        $integrity_status = $this->assess_questions_integrity($total_found, $field_config['max_items']);
        
        return [
            'success' => true,
            'items' => $questions_by_topic,
            'count' => $total_found,
            'post_id' => $post_id,
            'integrity_status' => $integrity_status,
            'metadata' => [
                'total_slots' => $field_config['max_items'],
                'topics_count' => 5,
                'last_updated' => get_post_meta($post_id, '_mkcg_questions_updated', true)
            ]
        ];
    }
    
    /**
     * Save questions data - moved from Questions Generator  
     */
    public function save_questions_data($questions_data, $post_id, $entry_id = 0) {
        error_log("MKCG Unified Service: Saving questions data to post {$post_id}");
        
        $saved_count = 0;
        $warnings = [];
        
        // Save questions for all 5 topics
        for ($topic = 1; $topic <= 5; $topic++) {
            if (isset($questions_data[$topic]) && is_array($questions_data[$topic])) {
                $topic_questions = $questions_data[$topic];
                
                for ($q = 0; $q < 5; $q++) {
                    $question_text = isset($topic_questions[$q]) ? trim($topic_questions[$q]) : '';
                    
                    if (!empty($question_text)) {
                        $question_number = (($topic - 1) * 5) + ($q + 1);
                        $meta_key = sprintf(MKCG_Config::get_meta_key_pattern('questions'), $question_number);
                        $result = update_post_meta($post_id, $meta_key, $question_text);
                        
                        if ($result !== false) {
                            $saved_count++;
                        } else {
                            $warnings[] = "Failed to save question {$question_number}";
                        }
                    }
                }
            }
        }
        
        // Update timestamp
        update_post_meta($post_id, '_mkcg_questions_updated', time());
        
        return [
            'success' => $saved_count > 0,
            'message' => "Successfully saved {$saved_count} questions",
            'count' => $saved_count,
            'post_id' => $post_id,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Get authority hook data
     */
    public function get_authority_hook_data($entry_id, $entry_key = '') {
        if ($entry_key && !$entry_id) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if (!$entry_data['success']) {
                return ['success' => false, 'message' => 'Entry not found: ' . $entry_key];
            }
            $entry_id = $entry_data['entry_id'];
        }
        
        // Use existing formidable service to find authority hook
        $hook_result = $this->formidable_service->find_authority_hook($entry_id);
        
        if ($hook_result['success']) {
            return [
                'success' => true,
                'items' => ['authority_hook' => $hook_result['value']],
                'count' => 1,
                'field_id' => $hook_result['field_id'],
                'method' => $hook_result['method']
            ];
        }
        
        return $hook_result;
    }
    
    /**
     * Save authority hook data
     */
    public function save_authority_hook_data($hook_data, $post_id, $entry_id) {
        $field_config = $this->field_mappings['authority_hook'];
        $saved_components = 0;
        
        // Save individual components
        foreach (['who', 'result', 'when', 'how'] as $component) {
            if (isset($hook_data[$component]) && isset($field_config['fields'][$component])) {
                $field_id = $field_config['fields'][$component];
                $result = $this->formidable_service->save_generated_content(
                    $entry_id,
                    [$component => $hook_data[$component]],
                    [$component => $field_id]
                );
                
                if ($result['success']) {
                    $saved_components++;
                }
            }
        }
        
        // Build and save complete hook
        $complete_hook = sprintf(
            "I help %s %s when %s %s.",
            $hook_data['who'] ?? 'your audience',
            $hook_data['result'] ?? 'achieve their goals', 
            $hook_data['when'] ?? 'they need help',
            $hook_data['how'] ?? 'through your method'
        );
        
        $complete_result = $this->formidable_service->save_generated_content(
            $entry_id,
            ['complete' => $complete_hook],
            ['complete' => $field_config['fields']['complete']]
        );
        
        return [
            'success' => $saved_components > 0 || $complete_result['success'],
            'message' => 'Authority hook saved successfully',
            'items' => ['authority_hook' => $complete_hook],
            'count' => 1,
            'saved_components' => $saved_components
        ];
    }
    
    /**
     * Save single item (topic or question)
     */
    public function save_single_item($data_type, $item_number, $item_text, $post_id, $entry_id = 0) {
        error_log("MKCG Unified Service: Saving single {$data_type} #{$item_number} to post {$post_id}");
        
        $field_config = $this->field_mappings[$data_type] ?? null;
        if (!$field_config) {
            return ['success' => false, 'message' => 'Unsupported data type for single item save: ' . $data_type];
        }
        
        $validation_rules = MKCG_Config::get_validation_rules($data_type);
        
        // Validate item text
        if (empty(trim($item_text))) {
            return ['success' => false, 'message' => ucfirst($data_type) . ' text cannot be empty'];
        }
        
        if (strlen($item_text) < $validation_rules['min_length']) {
            return ['success' => false, 'message' => ucfirst($data_type) . ' must be at least ' . $validation_rules['min_length'] . ' characters'];
        }
        
        if (strlen($item_text) > $validation_rules['max_length']) {
            return ['success' => false, 'message' => ucfirst($data_type) . ' cannot exceed ' . $validation_rules['max_length'] . ' characters'];
        }
        
        // Validate item number
        if ($item_number < 1 || $item_number > $field_config['max_items']) {
            return ['success' => false, 'message' => 'Invalid item number: ' . $item_number];
        }
        
        // Save to post meta
        $meta_key = sprintf(MKCG_Config::get_meta_key_pattern($data_type), $item_number);
        $result = update_post_meta($post_id, $meta_key, trim($item_text));
        
        if ($result !== false) {
            // Update timestamp
            update_post_meta($post_id, '_mkcg_' . $data_type . '_updated', time());
            
            return [
                'success' => true,
                'message' => ucfirst($data_type) . ' saved successfully',
                'items' => [$data_type . '_' . $item_number => trim($item_text)],
                'count' => 1,
                'post_id' => $post_id,
                'item_number' => $item_number
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to save ' . $data_type . ' to database'];
    }
    
    /**
     * 🛠️ UTILITY METHODS
     */
    
    /**
     * Enhanced request data parsing - handles JSON and form data
     */
    private function get_request_data() {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        $is_json_request = (
            strpos($content_type, 'application/json') !== false ||
            ($request_method === 'POST' && empty($_POST))
        );
        
        if ($is_json_request) {
            $raw_input = file_get_contents('php://input');
            if (!empty($raw_input)) {
                $json_data = json_decode($raw_input, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json_data;
                }
            }
        }
        
        return $_POST;
    }
    
    /**
     * Assess data quality based on content analysis
     */
    private function assess_data_quality($filled_count, $total_count, $quality_score) {
        $fill_percentage = ($filled_count / $total_count) * 100;
        $avg_quality = $quality_score / max($filled_count, 1);
        
        if ($fill_percentage >= 80 && $avg_quality >= 15) {
            return 'excellent';
        } elseif ($fill_percentage >= 60 && $avg_quality >= 10) {
            return 'good';
        } elseif ($fill_percentage >= 20) {
            return 'poor';
        } else {
            return 'missing';
        }
    }
    
    /**
     * Assess questions integrity
     */
    private function assess_questions_integrity($total_found, $total_slots) {
        $percentage = ($total_found / $total_slots) * 100;
        
        if ($percentage >= 80) {
            return 'excellent';
        } elseif ($percentage >= 60) {
            return 'good';
        } elseif ($percentage >= 40) {
            return 'fair';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Type-specific data validation
     */
    private function validate_data_by_type($data_type, $data_payload, $validation_rules) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => null
        ];
        
        switch ($data_type) {
            case 'topics':
                return $this->validate_topics_data($data_payload, $validation_rules);
            case 'questions':
                return $this->validate_questions_data($data_payload, $validation_rules);
            case 'authority_hook':
                return $this->validate_authority_hook_data($data_payload, $validation_rules);
            default:
                $validation['errors'][] = 'No validation available for: ' . $data_type;
                return $validation;
        }
    }
    
    /**
     * Validate topics data
     */
    private function validate_topics_data($topics_data, $validation_rules) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => []
        ];
        
        if (!is_array($topics_data)) {
            $validation['errors'][] = 'Topics data must be an array';
            return $validation;
        }
        
        $valid_topics = 0;
        
        for ($i = 1; $i <= 5; $i++) {
            $topic_text = isset($topics_data[$i]) ? trim($topics_data[$i]) : '';
            
            if (!empty($topic_text)) {
                if (strlen($topic_text) < $validation_rules['min_length']) {
                    $validation['errors'][] = "Topic {$i} is too short (minimum {$validation_rules['min_length']} characters)";
                } elseif (strlen($topic_text) > $validation_rules['max_length']) {
                    $validation['errors'][] = "Topic {$i} is too long (maximum {$validation_rules['max_length']} characters)";
                } else {
                    $valid_topics++;
                }
            }
            
            $validation['normalized_data'][$i] = $topic_text;
        }
        
        if ($valid_topics >= $validation_rules['required_count']) {
            $validation['valid'] = true;
        } else {
            $validation['errors'][] = "At least {$validation_rules['required_count']} valid topics required";
        }
        
        return $validation;
    }
    
    /**
     * Validate questions data
     */
    private function validate_questions_data($questions_data, $validation_rules) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => []
        ];
        
        if (!is_array($questions_data)) {
            $validation['errors'][] = 'Questions data must be an array';
            return $validation;
        }
        
        $total_valid = 0;
        
        for ($topic = 1; $topic <= 5; $topic++) {
            $validation['normalized_data'][$topic] = [];
            
            if (isset($questions_data[$topic]) && is_array($questions_data[$topic])) {
                for ($q = 0; $q < 5; $q++) {
                    $question_text = isset($questions_data[$topic][$q]) ? trim($questions_data[$topic][$q]) : '';
                    
                    if (!empty($question_text)) {
                        if (strlen($question_text) >= $validation_rules['min_length']) {
                            $total_valid++;
                        }
                    }
                    
                    $validation['normalized_data'][$topic][] = $question_text;
                }
            } else {
                $validation['normalized_data'][$topic] = ['', '', '', '', ''];
            }
        }
        
        $validation['valid'] = ($total_valid >= $validation_rules['required_count']);
        
        if (!$validation['valid'] && $total_valid === 0) {
            $validation['errors'][] = 'No valid questions found';
        }
        
        return $validation;
    }
    
    /**
     * Validate authority hook data
     */
    private function validate_authority_hook_data($hook_data, $validation_rules) {
        $validation = [
            'valid' => true,
            'errors' => [],
            'normalized_data' => []
        ];
        
        if (!is_array($hook_data)) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Authority hook data must be an array';
            return $validation;
        }
        
        $required_components = ['who', 'result', 'when', 'how'];
        
        foreach ($required_components as $component) {
            $value = isset($hook_data[$component]) ? trim($hook_data[$component]) : '';
            
            if (isset($validation_rules['components'][$component])) {
                $component_rules = $validation_rules['components'][$component];
                
                if (strlen($value) < $component_rules['min_length']) {
                    $validation['errors'][] = ucfirst($component) . " component is too short";
                } elseif (strlen($value) > $component_rules['max_length']) {
                    $validation['errors'][] = ucfirst($component) . " component is too long";
                }
            }
            
            $validation['normalized_data'][$component] = $value;
        }
        
        if (!empty($validation['errors'])) {
            $validation['valid'] = false;
        }
        
        return $validation;
    }
    
    /**
     * 🎯 PUBLIC API - Methods for generators to use
     */
    
    // Wrapper methods for backward compatibility
    public function get_topics_service() {
        return new MKCG_Topics_Data_Wrapper($this);
    }
    
    public function get_questions_service() {
        return new MKCG_Questions_Data_Wrapper($this);
    }
    
    // Direct access methods
    public function get_field_mappings() {
        return $this->field_mappings;
    }
    
    public function get_supported_types() {
        return $this->supported_data_types;
    }
}

/**
 * 🔄 COMPATIBILITY WRAPPERS - Maintain existing generator APIs
 */

class MKCG_Topics_Data_Wrapper {
    private $unified_service;
    
    public function __construct($unified_service) {
        $this->unified_service = $unified_service;
    }
    
    // Maintain existing Topics Generator method signatures
    public function get_topics_data($entry_id, $entry_key, $post_id) {
        return $this->unified_service->get_topics_data($entry_id, $entry_key, $post_id);
    }
    
    public function save_topics_data($topics_data, $post_id, $entry_id) {
        return $this->unified_service->save_topics_data($topics_data, $post_id, $entry_id);
    }
    
    public function save_single_topic($topic_number, $topic_text, $post_id, $entry_id) {
        return $this->unified_service->save_single_item('topics', $topic_number, $topic_text, $post_id, $entry_id);
    }
    
    public function save_authority_hook($entry_id, $who, $result, $when, $how) {
        $hook_data = [
            'who' => $who,
            'result' => $result,
            'when' => $when,
            'how' => $how
        ];
        return $this->unified_service->save_authority_hook_data($hook_data, 0, $entry_id);
    }
}

class MKCG_Questions_Data_Wrapper {
    private $unified_service;
    
    public function __construct($unified_service) {
        $this->unified_service = $unified_service;
    }
    
    // Maintain existing Questions Generator method signatures  
    public function get_questions_data($entry_id, $entry_key, $post_id) {
        return $this->unified_service->get_questions_data($entry_id, $entry_key, $post_id);
    }
    
    public function save_questions_data($questions_data, $post_id, $entry_id) {
        return $this->unified_service->save_questions_data($questions_data, $post_id, $entry_id);
    }
}
?>