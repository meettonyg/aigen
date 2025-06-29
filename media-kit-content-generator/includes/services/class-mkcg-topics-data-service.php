<?php
/**
 * MKCG Topics Data Service - ROOT LEVEL FIX
 * Self-contained, robust service for topics data operations
 * Fixed to work independently without relying on potentially missing dependencies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Topics_Data_Service {
    
    private $formidable_service;
    private $field_mappings;
    private $is_formidable_available = false;
    
    /**
     * Constructor with defensive initialization
     */
    public function __construct($formidable_service = null) {
        // DEFENSIVE: Check if formidable service is properly initialized
        if ($formidable_service && is_object($formidable_service)) {
            $this->formidable_service = $formidable_service;
            $this->is_formidable_available = true;
        } else {
            $this->formidable_service = null;
            $this->is_formidable_available = false;
            error_log('MKCG Topics Data Service: WARNING - Formidable service not available, using fallback methods');
        }
        
        $this->init_field_mappings();
        
        // Log successful initialization
        error_log('MKCG Topics Data Service: ✅ Successfully initialized');
    }
    
    /**
     * Initialize field mappings for Form 515 - SELF-CONTAINED
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
     * ROBUST: Get topics data with comprehensive fallback handling
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
        
        try {
            // Resolve entry ID if needed
            if (!$entry_id && $entry_key && $this->is_formidable_available) {
                $entry_data = $this->safe_get_entry_data($entry_key);
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
                $post_id = $this->safe_get_post_id_from_entry($entry_id);
            }
            
            if ($post_id) {
                $result['post_id'] = $post_id;
                
                // Get topics from post meta using WordPress functions directly
                $topics_result = $this->get_topics_from_post_direct($post_id);
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_topics_data: ' . $e->getMessage());
            $result['message'] = 'Error retrieving topics data: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * ROBUST: Get questions data with comprehensive fallback handling
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
        
        try {
            // Resolve entry ID if needed
            if (!$entry_id && $entry_key && $this->is_formidable_available) {
                $entry_data = $this->safe_get_entry_data($entry_key);
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
                $post_id = $this->safe_get_post_id_from_entry($entry_id);
            }
            
            if ($post_id) {
                $result['post_id'] = $post_id;
                
                // Get questions from post meta directly using WordPress functions
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_questions_data: ' . $e->getMessage());
            $result['message'] = 'Error retrieving questions data: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * ROBUST: Save questions data with comprehensive error handling
     */
    public function save_questions_data($questions_data, $post_id = null, $entry_id = null) {
        $result = [
            'success' => false,
            'saved_count' => 0,
            'errors' => [],
            'warnings' => []
        ];
        
        try {
            // Validate input
            $validation = $this->validate_questions_data($questions_data);
            if (!$validation['valid']) {
                $result['errors'] = $validation['errors'];
                return $result;
            }
            
            // Ensure we have post ID
            if (!$post_id && $entry_id) {
                $post_id = $this->safe_get_post_id_from_entry($entry_id);
            }
            
            if (!$post_id) {
                $result['errors'][] = 'No post ID available for saving';
                return $result;
            }
            
            // Save all questions organized by topic using WordPress functions directly
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
            
            // Also save to Formidable fields if entry_id provided and service available
            if ($entry_id && $this->is_formidable_available && isset($this->field_mappings['questions'])) {
                $this->save_questions_to_formidable_fields($entry_id, $normalized_data);
            }
            
            if ($saved_count > 0) {
                // Update timestamp for sync tracking
                update_post_meta($post_id, '_mkcg_questions_updated', time());
                $result['success'] = true;
                $result['saved_count'] = $saved_count;
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in save_questions_data: ' . $e->getMessage());
            $result['errors'][] = 'Save failed: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * SELF-CONTAINED: Get topics from post meta directly
     */
    private function get_topics_from_post_direct($post_id) {
        $topics = [];
        $metadata = ['total_topics' => 0, 'quality_score' => 0];
        
        try {
            // Get topics directly from post meta
            for ($i = 1; $i <= 5; $i++) {
                $topic_key = "topic_{$i}";
                $topic_value = get_post_meta($post_id, $topic_key, true);
                $topics[$topic_key] = $topic_value ?: '';
                
                if (!empty($topic_value)) {
                    $metadata['total_topics']++;
                }
            }
            
            // Calculate quality score
            $metadata['quality_score'] = ($metadata['total_topics'] / 5) * 100;
            
            // Determine data quality
            if ($metadata['total_topics'] >= 4) {
                $data_quality = 'good';
            } elseif ($metadata['total_topics'] >= 2) {
                $data_quality = 'fair';
            } elseif ($metadata['total_topics'] > 0) {
                $data_quality = 'poor';
            } else {
                $data_quality = 'missing';
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_topics_from_post_direct: ' . $e->getMessage());
            $data_quality = 'missing';
        }
        
        return [
            'topics' => $topics,
            'data_quality' => $data_quality,
            'metadata' => $metadata
        ];
    }
    
    /**
     * SAFE WRAPPER: Get entry data with fallback
     */
    private function safe_get_entry_data($entry_key) {
        if (!$this->is_formidable_available) {
            return ['success' => false, 'message' => 'Formidable service not available'];
        }
        
        try {
            if (method_exists($this->formidable_service, 'get_entry_data')) {
                return $this->formidable_service->get_entry_data($entry_key);
            } else {
                return $this->fallback_get_entry_data($entry_key);
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in safe_get_entry_data: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * SAFE WRAPPER: Get post ID from entry with fallback
     */
    private function safe_get_post_id_from_entry($entry_id) {
        if (!$this->is_formidable_available) {
            return 0;
        }
        
        try {
            if (method_exists($this->formidable_service, 'get_post_id_from_entry')) {
                return $this->formidable_service->get_post_id_from_entry($entry_id);
            } else {
                return $this->fallback_get_post_id_from_entry($entry_id);
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in safe_get_post_id_from_entry: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * FALLBACK: Get entry data using direct database query
     */
    private function fallback_get_entry_data($entry_key) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'frm_items';
            $entry = $wpdb->get_row($wpdb->prepare(
                "SELECT id, item_key, form_id, post_id FROM {$table} WHERE item_key = %s",
                $entry_key
            ));
            
            if ($entry) {
                return [
                    'success' => true,
                    'entry_id' => $entry->id,
                    'form_id' => $entry->form_id,
                    'post_id' => $entry->post_id
                ];
            } else {
                return ['success' => false, 'message' => 'Entry not found'];
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in fallback_get_entry_data: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * FALLBACK: Get post ID from entry using direct database query
     */
    private function fallback_get_post_id_from_entry($entry_id) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'frm_items';
            $post_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$table} WHERE id = %d",
                $entry_id
            ));
            
            return $post_id ? (int) $post_id : 0;
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in fallback_get_post_id_from_entry: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * SELF-CONTAINED: Get topics from Formidable entry using direct queries
     */
    private function get_topics_from_entry_direct($entry_id) {
        $topics = [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
        
        try {
            if ($this->is_formidable_available) {
                // Use centralized field mappings
                $field_mappings = $this->field_mappings['topics']['fields'];
                
                foreach ($field_mappings as $topic_key => $field_id) {
                    $value = $this->safe_get_field_value($entry_id, $field_id);
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_topics_from_entry_direct: ' . $e->getMessage());
            return [
                'topics' => $topics,
                'data_quality' => 'missing'
            ];
        }
    }
    
    /**
     * SELF-CONTAINED: Get questions from Formidable entry using direct queries
     */
    private function get_questions_from_entry_direct($entry_id) {
        $questions_by_topic = [];
        $total_questions = 0;
        
        try {
            if ($this->is_formidable_available) {
                // Use centralized field mappings
                $questions_fields = $this->field_mappings['questions']['fields'];
                
                for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
                    $topic_questions = [];
                    
                    if (isset($questions_fields[$topic_num])) {
                        foreach ($questions_fields[$topic_num] as $field_id) {
                            $value = $this->safe_get_field_value($entry_id, $field_id);
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
            } else {
                // Initialize with empty structure if no Formidable service
                for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
                    $questions_by_topic[$topic_num] = ['', '', '', '', ''];
                }
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_questions_from_entry_direct: ' . $e->getMessage());
            $data_quality = 'missing';
        }
        
        return [
            'questions' => $questions_by_topic,
            'data_quality' => $data_quality,
            'total_questions' => $total_questions
        ];
    }
    
    /**
     * SAFE WRAPPER: Get field value with fallback
     */
    private function safe_get_field_value($entry_id, $field_id) {
        try {
            if ($this->is_formidable_available && method_exists($this->formidable_service, 'get_field_value')) {
                return $this->formidable_service->get_field_value($entry_id, $field_id);
            } else {
                return $this->fallback_get_field_value($entry_id, $field_id);
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in safe_get_field_value: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * FALLBACK: Get field value using direct database query
     */
    private function fallback_get_field_value($entry_id, $field_id) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'frm_item_metas';
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$table} WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            return $value ?: '';
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in fallback_get_field_value: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * SELF-CONTAINED: Get authority hook data with comprehensive fallback
     */
    public function get_authority_hook_data($entry_id) {
        try {
            if (!$this->is_formidable_available || !$entry_id) {
                return $this->get_default_authority_hook();
            }
            
            // Get components using Form 515 field IDs
            $components = [
                'who' => $this->safe_get_field_value($entry_id, 10296),
                'result' => $this->safe_get_field_value($entry_id, 10297),
                'when' => $this->safe_get_field_value($entry_id, 10387),
                'how' => $this->safe_get_field_value($entry_id, 10298),
                'complete' => $this->safe_get_field_value($entry_id, 10358)
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in get_authority_hook_data: ' . $e->getMessage());
            return $this->get_default_authority_hook();
        }
    }
    
    /**
     * SELF-CONTAINED: Validate questions data structure
     */
    private function validate_questions_data($questions_data) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'normalized_data' => []
        ];
        
        try {
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
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in validate_questions_data: ' . $e->getMessage());
            $validation['errors'][] = 'Validation failed: ' . $e->getMessage();
        }
        
        return $validation;
    }
    
    /**
     * SAFE WRAPPER: Save questions to Formidable fields
     */
    private function save_questions_to_formidable_fields($entry_id, $normalized_data) {
        if (!$this->is_formidable_available || !isset($this->field_mappings['questions']['fields'])) {
            return false;
        }
        
        try {
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
                                if ($this->save_single_field_direct($entry_id, $field_id, $question_text)) {
                                    $saved_count++;
                                }
                            }
                        }
                    }
                }
            }
            
            return $saved_count;
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in save_questions_to_formidable_fields: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * SELF-CONTAINED: Save single field using direct database query
     */
    private function save_single_field_direct($entry_id, $field_id, $value) {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'frm_item_metas';
            
            // Check if field already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$table} WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($existing !== null) {
                // Update existing field
                $result = $wpdb->update(
                    $table,
                    ['meta_value' => $value],
                    ['item_id' => $entry_id, 'field_id' => $field_id],
                    ['%s'],
                    ['%d', '%d']
                );
            } else {
                // Insert new field
                $result = $wpdb->insert(
                    $table,
                    [
                        'item_id' => $entry_id,
                        'field_id' => $field_id,
                        'meta_value' => $value
                    ],
                    ['%d', '%d', '%s']
                );
            }
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log('MKCG Topics Data Service: Exception in save_single_field_direct: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * SELF-CONTAINED: Get default authority hook
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
