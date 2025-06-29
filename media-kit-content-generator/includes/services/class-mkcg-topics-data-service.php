<?php
/**
 * MKCG Topics Data Service
 * Unified service for topics data operations shared by Topics and Questions generators
 */

class MKCG_Topics_Data_Service {
    
    private $formidable_service;
    private $field_mappings;
    
    public function __construct($formidable_service) {
        $this->formidable_service = $formidable_service;
        $this->init_field_mappings();
    }
    
    /**
     * Initialize field mappings for Form 515
     */
    private function init_field_mappings() {
        $this->field_mappings = [
            'topics' => [
                'fields' => [
                    'topic_1' => 8498, 'topic_2' => 8499, 'topic_3' => 8500,
                    'topic_4' => 8501, 'topic_5' => 8502
                ]
            ],
            'questions' => [
                'fields' => [
                    1 => ['8505', '8506', '8507', '8508', '8509'],
                    2 => ['8510', '8511', '8512', '8513', '8514'], 
                    3 => ['10370', '10371', '10372', '10373', '10374'],
                    4 => ['10375', '10376', '10377', '10378', '10379'],
                    5 => ['10380', '10381', '10382', '10383', '10384']
                ]
            ],
            'authority_hook' => [
                'fields' => [
                    'who' => 10296, 'result' => 10297, 'when' => 10387,
                    'how' => 10298, 'complete' => 10358
                ]
            ]
        ];
    }
    
    /**
     * UNIFIED: Get topics data for any generator
     */
    public function get_topics_data($entry_id = null, $entry_key = null, $post_id = null) {
        $result = [
            'success' => false,
            'topics' => [],
            'data_quality' => 'missing',
            'authority_hook' => null,
            'entry_id' => 0,
            'post_id' => 0,
            'source' => 'none',
            'metadata' => []
        ];
        
        // Resolve entry ID if needed
        if (!$entry_id && $entry_key) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if ($entry_data['success']) {
                $entry_id = $entry_data['entry_id'];
            }
        }
        
        if (!$entry_id) {
            $result['message'] = 'No entry ID or key provided';
            return $result;
        }
        
        $result['entry_id'] = $entry_id;
        
        // Get post ID if not provided
        if (!$post_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if ($post_id) {
            $result['post_id'] = $post_id;
            
            // Get topics from post with quality validation
            $topics_result = $this->formidable_service->get_topics_from_post_enhanced($post_id);
            $result['topics'] = $topics_result['topics'];
            $result['data_quality'] = $topics_result['data_quality'];
            $result['source'] = 'custom_post';
            $result['metadata'] = $topics_result['metadata'];
        } else {
            // Fallback: Get topics from Formidable entry directly
            $topics_from_entry = $this->get_topics_from_entry_direct($entry_id);
            $result['topics'] = $topics_from_entry['topics'];
            $result['data_quality'] = $topics_from_entry['data_quality'];
            $result['source'] = 'formidable_entry';
        }
        
        // Always get authority hook data
        $result['authority_hook'] = $this->get_authority_hook_data($entry_id);
        
        $result['success'] = (count(array_filter($result['topics'])) > 0);
        
        return $result;
    }
    
    /**
     * UNIFIED: Get questions data for any generator
     */
    public function get_questions_data($entry_id = null, $entry_key = null, $post_id = null) {
        $result = [
            'success' => false,
            'questions' => [],
            'data_quality' => 'missing',
            'total_questions' => 0,
            'entry_id' => 0,
            'post_id' => 0,
            'source' => 'none',
            'metadata' => []
        ];
        
        // Resolve entry ID if needed
        if (!$entry_id && $entry_key) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if ($entry_data['success']) {
                $entry_id = $entry_data['entry_id'];
            }
        }
        
        if (!$entry_id) {
            $result['message'] = 'No entry ID or key provided';
            return $result;
        }
        
        $result['entry_id'] = $entry_id;
        
        // Get post ID if not provided
        if (!$post_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if ($post_id) {
            $result['post_id'] = $post_id;
            
            // Get questions from post meta (organized by topic)
            $questions_by_topic = [];
            $total_found = 0;
            
            for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
                $topic_questions = [];
                
                for ($q_num = 1; $q_num <= 5; $q_num++) {
                    $question_number = (($topic_num - 1) * 5) + $q_num;
                    $meta_key = 'question_' . $question_number;
                    $question = get_post_meta($post_id, $meta_key, true);
                    
                    $topic_questions[] = $question ?: '';
                    if (!empty($question)) {
                        $total_found++;
                    }
                }
                
                $questions_by_topic[$topic_num] = $topic_questions;
            }
            
            $result['questions'] = $questions_by_topic;
            $result['total_questions'] = $total_found;
            $result['source'] = 'custom_post';
            
            // Determine data quality
            if ($total_found >= 20) {
                $result['data_quality'] = 'excellent';
            } elseif ($total_found >= 15) {
                $result['data_quality'] = 'good';
            } elseif ($total_found >= 10) {
                $result['data_quality'] = 'fair';
            } elseif ($total_found > 0) {
                $result['data_quality'] = 'poor';
            } else {
                $result['data_quality'] = 'missing';
            }
            
        } else {
            // Fallback: Get questions from Formidable entry directly
            $questions_from_entry = $this->get_questions_from_entry_direct($entry_id);
            $result['questions'] = $questions_from_entry['questions'];
            $result['data_quality'] = $questions_from_entry['data_quality'];
            $result['total_questions'] = $questions_from_entry['total_questions'];
            $result['source'] = 'formidable_entry';
        }
        
        $result['success'] = ($result['total_questions'] > 0);
        
        return $result;
    }
    
    /**
     * UNIFIED: Save questions data (25 questions across 5 topics)
     */
    public function save_questions_data($questions_data, $post_id = null, $entry_id = null) {
        $result = [
            'success' => false,
            'saved_count' => 0,
            'errors' => [],
            'warnings' => []
        ];
        
        // Validate input
        $validation = $this->validate_questions_data($questions_data);
        if (!$validation['valid']) {
            $result['errors'] = $validation['errors'];
            return $result;
        }
        
        // Ensure we have post ID
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            $result['errors'][] = 'No post ID available for saving';
            return $result;
        }
        
        // Save all questions organized by topic
        $saved_count = 0;
        $normalized_data = $validation['normalized_data'];
        
        foreach ($normalized_data as $topic_num => $topic_questions) {
            for ($q_index = 0; $q_index < 5; $q_index++) {
                if (isset($topic_questions[$q_index])) {
                    $question_text = $topic_questions[$q_index];
                    
                    if (!empty(trim($question_text))) {
                        $question_number = (($topic_num - 1) * 5) + ($q_index + 1);
                        $meta_key = 'question_' . $question_number;
                        
                        $save_result = update_post_meta($post_id, $meta_key, trim($question_text));
                        
                        if ($save_result !== false) {
                            $saved_count++;
                        } else {
                            $result['warnings'][] = "Failed to save Question {$question_number}";
                        }
                    }
                }
            }
        }
        
        // Also save to Formidable fields if entry_id provided
        if ($entry_id && isset($this->field_mappings['questions'])) {
            $this->save_questions_to_formidable_fields($entry_id, $normalized_data);
        }
        
        if ($saved_count > 0) {
            // Update timestamp for sync tracking
            update_post_meta($post_id, '_mkcg_questions_updated', time());
            $result['success'] = true;
            $result['saved_count'] = $saved_count;
        }
        
        return $result;
    }
    
    /**
     * UNIFIED: Save single question (for inline editing)
     */
    public function save_single_question($question_number, $question_text, $post_id = null, $entry_id = null) {
        $result = [
            'success' => false,
            'message' => '',
            'post_id' => $post_id,
            'question_number' => $question_number
        ];
        
        // Validation
        if ($question_number < 1 || $question_number > 25) {
            $result['message'] = 'Question number must be between 1 and 25';
            return $result;
        }
        
        if (empty(trim($question_text))) {
            $result['message'] = 'Question text cannot be empty';
            return $result;
        }
        
        // Ensure we have post ID
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            $result['message'] = 'No post ID available for saving';
            return $result;
        }
        
        // Save the question
        $meta_key = 'question_' . $question_number;
        $save_result = update_post_meta($post_id, $meta_key, trim($question_text));
        
        if ($save_result !== false) {
            update_post_meta($post_id, '_mkcg_questions_updated', time());
            $result['success'] = true;
            $result['message'] = 'Question saved successfully';
            $result['post_id'] = $post_id;
        } else {
            $result['message'] = 'Failed to save question';
        }
        
        return $result;
    }
    
    /**
     * UNIFIED: Save topics data (works for both generators)
     */
    public function save_topics_data($topics_data, $post_id = null, $entry_id = null) {
        $result = [
            'success' => false,
            'saved_count' => 0,
            'errors' => [],
            'warnings' => []
        ];
        
        // Validate input
        $validation = $this->validate_topics_data($topics_data);
        if (!$validation['valid']) {
            $result['errors'] = $validation['errors'];
            return $result;
        }
        
        // Ensure we have post ID
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            $result['errors'][] = 'No post ID available for saving';
            return $result;
        }
        
        // Save all topics
        $saved_count = 0;
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            if (isset($validation['normalized_data'][$topic_num])) {
                $topic_text = $validation['normalized_data'][$topic_num];
                
                if (!empty(trim($topic_text))) {
                    $save_result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_num, $topic_text);
                    
                    if ($save_result) {
                        $saved_count++;
                    } else {
                        $result['warnings'][] = "Failed to save Topic {$topic_num}";
                    }
                }
            }
        }
        
        if ($saved_count > 0) {
            // Update timestamp for sync tracking
            update_post_meta($post_id, '_mkcg_topics_updated', time());
            $result['success'] = true;
            $result['saved_count'] = $saved_count;
        }
        
        return $result;
    }
    
    /**
     * UNIFIED: Save single topic (for inline editing)
     */
    public function save_single_topic($topic_number, $topic_text, $post_id = null, $entry_id = null) {
        $result = [
            'success' => false,
            'message' => '',
            'post_id' => $post_id,
            'topic_number' => $topic_number
        ];
        
        // Validation
        if ($topic_number < 1 || $topic_number > 5) {
            $result['message'] = 'Topic number must be between 1 and 5';
            return $result;
        }
        
        if (empty(trim($topic_text))) {
            $result['message'] = 'Topic text cannot be empty';
            return $result;
        }
        
        // Ensure we have post ID
        if (!$post_id && $entry_id) {
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        }
        
        if (!$post_id) {
            $result['message'] = 'No post ID available for saving';
            return $result;
        }
        
        // Save the topic
        $save_result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_number, $topic_text);
        
        if ($save_result) {
            update_post_meta($post_id, '_mkcg_topics_updated', time());
            $result['success'] = true;
            $result['message'] = 'Topic saved successfully';
            $result['post_id'] = $post_id;
        } else {
            $result['message'] = 'Failed to save topic';
        }
        
        return $result;
    }
    
    /**
     * UNIFIED: Get authority hook data
     */
    public function get_authority_hook_data($entry_id) {
        if (!$this->formidable_service) {
            return $this->get_default_authority_hook();
        }
        
        // Get components using Form 515 field IDs
        $components = [
            'who' => $this->formidable_service->get_field_value($entry_id, 10296),
            'result' => $this->formidable_service->get_field_value($entry_id, 10297),
            'when' => $this->formidable_service->get_field_value($entry_id, 10387),
            'how' => $this->formidable_service->get_field_value($entry_id, 10298),
            'complete' => $this->formidable_service->get_field_value($entry_id, 10358)
        ];
        
        // Fill in defaults for missing components
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
     * UNIFIED: Save authority hook components
     */
    public function save_authority_hook($entry_id, $who, $result, $when, $how) {
        if (!$this->formidable_service) {
            return ['success' => false, 'message' => 'Service not available'];
        }
        
        // Field mappings for Form 515
        $field_mappings = [
            'who' => 10296,
            'result' => 10297,
            'when' => 10387,
            'how' => 10298,
            'complete' => 10358
        ];
        
        // Build complete hook
        $complete_hook = "I help {$who} {$result} when {$when} {$how}.";
        
        $components = [
            'who' => $who,
            'result' => $result,
            'when' => $when,
            'how' => $how,
            'complete' => $complete_hook
        ];
        
        $saved_fields = [];
        $save_errors = [];
        
        // Save each component
        foreach ($components as $component => $value) {
            if (isset($field_mappings[$component])) {
                $field_id = $field_mappings[$component];
                $save_result = $this->formidable_service->save_generated_content(
                    $entry_id,
                    [$component => $value],
                    [$component => $field_id]
                );
                
                if ($save_result['success']) {
                    $saved_fields[] = $component;
                } else {
                    $save_errors[] = "Failed to save {$component}";
                }
            }
        }
        
        return [
            'success' => count($saved_fields) > 0,
            'authority_hook' => $complete_hook,
            'saved_fields' => $saved_fields,
            'errors' => $save_errors
        ];
    }
    
    /**
     * UNIFIED: Validate topics data structure
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
            $possible_keys = ["topic_{$i}", $i, "topic{$i}", $i - 1];
            
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
     * Helper: Get topics directly from Formidable entry
     */
    private function get_topics_from_entry_direct($entry_id) {
        $topics = [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
        
        // Use centralized field mappings
        $field_mappings = $this->field_mappings['topics']['fields'];
        
        foreach ($field_mappings as $topic_key => $field_id) {
            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
            if (!empty($value)) {
                $topics[$topic_key] = $value;
            }
        }
        
        $non_empty_count = count(array_filter($topics));
        
        return [
            'topics' => $topics,
            'data_quality' => $non_empty_count >= 4 ? 'good' : ($non_empty_count >= 2 ? 'fair' : ($non_empty_count > 0 ? 'poor' : 'missing'))
        ];
    }
    
    /**
     * Helper: Get questions directly from Formidable entry
     */
    private function get_questions_from_entry_direct($entry_id) {
        $questions_by_topic = [];
        $total_questions = 0;
        
        // Use centralized field mappings
        $questions_fields = $this->field_mappings['questions']['fields'];
        
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            $topic_questions = [];
            
            if (isset($questions_fields[$topic_num])) {
                foreach ($questions_fields[$topic_num] as $field_id) {
                    $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                    $topic_questions[] = $value ?: '';
                    
                    if (!empty($value)) {
                        $total_questions++;
                    }
                }
            } else {
                // Fill with empty questions if no mapping
                $topic_questions = ['', '', '', '', ''];
            }
            
            $questions_by_topic[$topic_num] = $topic_questions;
        }
        
        // Determine data quality based on total questions
        if ($total_questions >= 20) {
            $data_quality = 'excellent';
        } elseif ($total_questions >= 15) {
            $data_quality = 'good';
        } elseif ($total_questions >= 10) {
            $data_quality = 'fair';
        } elseif ($total_questions > 0) {
            $data_quality = 'poor';
        } else {
            $data_quality = 'missing';
        }
        
        return [
            'questions' => $questions_by_topic,
            'data_quality' => $data_quality,
            'total_questions' => $total_questions
        ];
    }
    
    /**
     * UNIFIED: Validate questions data structure
     */
    private function validate_questions_data($questions_data) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => []
        ];
        
        if ($questions_data === null || $questions_data === '') {
            $validation['errors'][] = 'No questions data provided';
            return $validation;
        }
        
        // Handle JSON string
        if (is_string($questions_data)) {
            $decoded = json_decode($questions_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $questions_data = $decoded;
            } else {
                $validation['errors'][] = 'Invalid JSON format';
                return $validation;
            }
        }
        
        // Convert object to array if needed
        if (is_object($questions_data)) {
            $questions_data = (array) $questions_data;
        }
        
        // Must be array
        if (!is_array($questions_data)) {
            $validation['errors'][] = 'Questions data must be an array';
            return $validation;
        }
        
        $valid_questions = 0;
        
        // Validate and normalize each topic's questions
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            $topic_questions = [];
            
            if (isset($questions_data[$topic_num]) && is_array($questions_data[$topic_num])) {
                $topic_data = $questions_data[$topic_num];
                
                for ($q_index = 0; $q_index < 5; $q_index++) {
                    if (isset($topic_data[$q_index])) {
                        $question = sanitize_textarea_field(trim($topic_data[$q_index]));
                        $topic_questions[] = $question;
                        
                        if (!empty($question)) {
                            $valid_questions++;
                        }
                    } else {
                        $topic_questions[] = '';
                    }
                }
            } else {
                // Fill with empty questions if topic not provided
                $topic_questions = ['', '', '', '', ''];
            }
            
            $validation['normalized_data'][$topic_num] = $topic_questions;
        }
        
        if ($valid_questions === 0) {
            $validation['errors'][] = 'No valid questions found';
        } else {
            $validation['valid'] = true;
        }
        
        return $validation;
    }
    
    /**
     * Helper: Save questions to Formidable fields
     */
    private function save_questions_to_formidable_fields($entry_id, $normalized_data) {
        if (!isset($this->field_mappings['questions']['fields'])) {
            return false;
        }
        
        $questions_fields = $this->field_mappings['questions']['fields'];
        $saved_count = 0;
        
        foreach ($normalized_data as $topic_num => $topic_questions) {
            if (isset($questions_fields[$topic_num])) {
                $field_ids = $questions_fields[$topic_num];
                
                for ($q_index = 0; $q_index < 5; $q_index++) {
                    if (isset($field_ids[$q_index]) && isset($topic_questions[$q_index])) {
                        $field_id = $field_ids[$q_index];
                        $question_text = $topic_questions[$q_index];
                        
                        if (!empty(trim($question_text))) {
                            $save_result = $this->formidable_service->save_generated_content(
                                $entry_id,
                                ['question' => $question_text],
                                ['question' => $field_id]
                            );
                            
                            if ($save_result['success']) {
                                $saved_count++;
                            }
                        }
                    }
                }
            }
        }
        
        return $saved_count;
    }
    
    /**
     * UNIFIED: Standard AJAX response formatter
     */
    private function format_response($success, $data = [], $message = '') {
        return [
            'success' => $success,
            'data' => array_merge([
                'message' => $message,
                'timestamp' => time(),
                'source' => 'unified_service'
            ], $data)
        ];
    }
    
    /**
     * Helper: Get default authority hook
     */
    private function get_default_authority_hook() {
        return [
            'who' => 'your audience',
            'result' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method',
            'complete' => 'I help your audience achieve their goals when they need help through your method.'
        ];
    }
}
