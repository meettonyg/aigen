<?php
/**
 * MKCG Centralized Configuration
 * 
 * Single source of truth for all field mappings, meta keys, and system configuration.
 * As recommended by Gemini - this decouples the service from implementation details.
 */

class MKCG_Config {
    
    /**
     * 🗃️ FIELD MAPPINGS - Formidable Form 515 field IDs
     */
    public static function get_field_mappings() {
        return [
            'topics' => [
                'fields' => [
                    'topic_1' => 8498,
                    'topic_2' => 8499,
                    'topic_3' => 8500,
                    'topic_4' => 8501,
                    'topic_5' => 8502
                ],
                'meta_prefix' => 'topic_',
                'max_items' => 5,
                'type' => 'single_value'
            ],
            'questions' => [
                'fields' => [
                    1 => ['8505', '8506', '8507', '8508', '8509'],     // Topic 1 → Questions 1-5
                    2 => ['8510', '8511', '8512', '8513', '8514'],     // Topic 2 → Questions 6-10
                    3 => ['10370', '10371', '10372', '10373', '10374'], // Topic 3 → Questions 11-15
                    4 => ['10375', '10376', '10377', '10378', '10379'], // Topic 4 → Questions 16-20
                    5 => ['10380', '10381', '10382', '10383', '10384']  // Topic 5 → Questions 21-25
                ],
                'meta_prefix' => 'question_',
                'max_items' => 25,
                'type' => 'grouped_values',
                'items_per_group' => 5
            ],
            'authority_hook' => [
                'fields' => [
                    'who' => 10296,     // WHO field
                    'result' => 10297,  // RESULT field  
                    'when' => 10387,    // WHEN field
                    'how' => 10298,     // HOW field
                    'complete' => 10358 // Complete Authority Hook
                ],
                'meta_prefix' => 'authority_hook_',
                'max_items' => 5,
                'type' => 'component_based'
            ],
            // PLACEHOLDER: Biography and Offers field mappings (to prevent warnings)
            'biography' => [
                'fields' => [
                    // Placeholder field mappings - update when Biography generator is implemented
                    'short_bio' => 99001,    // Unique placeholder ID
                    'medium_bio' => 99002,   // Unique placeholder ID
                    'long_bio' => 99003      // Unique placeholder ID
                ],
                'meta_prefix' => 'biography_',
                'max_items' => 3,
                'type' => 'multi_length',
                'status' => 'placeholder'
            ],
            'offers' => [
                'fields' => [
                    // Placeholder field mappings - update when Offers generator is implemented
                    'offer_1' => 99004,      // Unique placeholder ID
                    'offer_2' => 99005,      // Unique placeholder ID
                    'offer_3' => 99006       // Unique placeholder ID
                ],
                'meta_prefix' => 'offer_',
                'max_items' => 3,
                'type' => 'single_value',
                'status' => 'placeholder'
            ]
        ];
    }
    
    /**
     * Get a specific field ID for cleaner code access
     * @param string $data_type The data type (topics, questions, etc.)
     * @param string|int $field_key The field key
     * @return string|null The field ID or null if not found
     */
    public static function get_field_id(string $data_type, string|int $field_key) {
        $field_mappings = self::get_field_mappings();
        
        if (!isset($field_mappings[$data_type]['fields'])) {
            return null;
        }
        
        return $field_mappings[$data_type]['fields'][$field_key] ?? null;
    }
    
    /**
     * 🔑 META KEY PATTERNS - WordPress post meta key generation
     */
    public static function get_meta_key_pattern($data_type) {
        $patterns = [
            'topics' => 'topic_%d',          // topic_1, topic_2, etc.
            'questions' => 'question_%d',    // question_1, question_2, etc.
            'authority_hook' => 'authority_hook_%s', // authority_hook_who, etc.
        ];
        
        return $patterns[$data_type] ?? null;
    }
    
    /**
     * 📋 VALIDATION RULES - Data validation configuration
     */
    public static function get_validation_rules($data_type) {
        $rules = [
            'topics' => [
                'min_length' => 5,
                'max_length' => 500,
                'required_count' => 0, // At least 0 topics required
                'max_count' => 5,
                'sanitization' => 'sanitize_textarea_field'
            ],
            'questions' => [
                'min_length' => 10,
                'max_length' => 1000,
                'required_count' => 0,
                'max_count' => 25,
                'sanitization' => 'sanitize_textarea_field'
            ],
            'authority_hook' => [
                'components' => [
                    'who' => ['min_length' => 2, 'max_length' => 100],
                    'result' => ['min_length' => 5, 'max_length' => 200],
                    'when' => ['min_length' => 2, 'max_length' => 100],
                    'how' => ['min_length' => 2, 'max_length' => 100]
                ],
                'complete' => ['min_length' => 20, 'max_length' => 500],
                'sanitization' => 'sanitize_text_field'
            ]
        ];
        
        return $rules[$data_type] ?? null;
    }
    
    /**
     * 🔧 AJAX ACTIONS - Centralized AJAX action names
     */
    public static function get_ajax_actions() {
        return [
            'get_data' => 'mkcg_get_data',
            'save_data' => 'mkcg_save_data', 
            'save_item' => 'mkcg_save_item',
            'authority_hook' => 'mkcg_authority_hook',
            
            // Legacy support for existing implementations
            'legacy_topics' => 'generate_interview_topics',
            'legacy_questions' => 'generate_interview_questions',
            'legacy_get_topics' => 'mkcg_get_topics'
        ];
    }
    
    /**
     * 🔐 SECURITY CONFIGURATION - Nonce actions and verification
     */
    public static function get_security_config() {
        return [
            'nonce_actions' => [
                'primary' => 'mkcg_nonce',
                'save' => 'mkcg_save_nonce',
                'legacy' => 'generate_topics_nonce'
            ],
            'nonce_fields' => [
                'security', 'nonce', 'save_nonce', 'mkcg_nonce', '_wpnonce'
            ],
            'cache_duration' => 3600 // 1 hour
        ];
    }
    
    /**
     * 📊 RESPONSE TEMPLATES - Standardized response structures
     */
    public static function get_response_template($type = 'success') {
        $templates = [
            'success' => [
                'success' => true,
                'data' => [
                    'message' => '',
                    'items' => [],
                    'count' => 0,
                    'post_id' => 0,
                    'warnings' => [],
                    'metadata' => []
                ]
            ],
            'error' => [
                'success' => false,
                'data' => [
                    'message' => '',
                    'errors' => [],
                    'debug' => null,
                    'validation_errors' => []
                ]
            ]
        ];
        
        return $templates[$type] ?? $templates['error'];
    }
    
    /**
     * 🎯 DATA TYPE CONFIGURATION - Supported content types
     */
    public static function get_supported_data_types() {
        return [
            'topics' => [
                'label' => 'Interview Topics',
                'description' => 'AI-generated podcast interview topics',
                'generator_class' => 'MKCG_Topics_Generator',
                'ai_enabled' => true
            ],
            'questions' => [
                'label' => 'Interview Questions',
                'description' => 'AI-generated questions for specific topics',
                'generator_class' => 'MKCG_Questions_Generator', 
                'ai_enabled' => true
            ],
            'authority_hook' => [
                'label' => 'Authority Hook',
                'description' => 'Expert positioning statement',
                'generator_class' => null, // No generator needed
                'ai_enabled' => false
            ],
            
            // Future data types can be added here
            'biography' => [
                'label' => 'Media Biography',
                'description' => 'AI-generated media kit biography',
                'generator_class' => 'MKCG_Biography_Generator',
                'ai_enabled' => true
            ],
            'offers' => [
                'label' => 'Service Offers',
                'description' => 'AI-generated service offerings',
                'generator_class' => 'MKCG_Offers_Generator', 
                'ai_enabled' => true
            ]
        ];
    }
    
    /**
     * 📁 FILE PATHS - Plugin file structure
     */
    public static function get_file_paths() {
        $base_path = plugin_dir_path(__FILE__);
        
        return [
            'generators' => $base_path . 'includes/generators/',
            'services' => $base_path . 'includes/services/',
            'templates' => $base_path . 'templates/',
            'assets' => $base_path . 'assets/',
            'css' => $base_path . 'assets/css/',
            'js' => $base_path . 'assets/js/'
        ];
    }
    
    /**
     * ⚙️ SYSTEM CONFIGURATION - Plugin-wide settings
     */
    public static function get_system_config() {
        return [
            'plugin_version' => '1.0.0',
            'min_wordpress_version' => '5.0',
            'required_plugins' => ['formidable/formidable.php'],
            'form_id' => 515, // Primary Formidable form
            'post_type' => 'media_kit_content',
            'taxonomy' => 'content_category',
            'cache_prefix' => 'mkcg_',
            'log_prefix' => 'MKCG',
            'api_timeout' => 30,
            'max_retries' => 3
        ];
    }
    
    /**
     * 🔍 CONFIGURATION VALIDATION - Ensure config is valid
     */
    public static function validate_configuration() {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Check field mappings exist
        $field_mappings = self::get_field_mappings();
        if (empty($field_mappings)) {
            $validation['valid'] = false;
            $validation['errors'][] = 'No field mappings configured';
        }
        
        // Check for duplicate field IDs
        $all_fields = [];
        foreach ($field_mappings as $data_type => $config) {
            // Skip placeholder configurations from duplicate field ID checks
            if (isset($config['status']) && $config['status'] === 'placeholder') {
                continue;
            }
            
            if (isset($config['fields'])) {
                foreach ($config['fields'] as $field_key => $field_id) {
                    if (is_array($field_id)) {
                        foreach ($field_id as $sub_field) {
                            if (in_array($sub_field, $all_fields)) {
                                $validation['warnings'][] = "Duplicate field ID: {$sub_field}";
                            }
                            $all_fields[] = $sub_field;
                        }
                    } else {
                        if (in_array($field_id, $all_fields)) {
                            $validation['warnings'][] = "Duplicate field ID: {$field_id}";
                        }
                        $all_fields[] = $field_id;
                    }
                }
            }
        }
        
        // Check supported data types have configuration
        $data_types = self::get_supported_data_types();
        foreach ($data_types as $type => $config) {
            if (!isset($field_mappings[$type])) {
                $validation['warnings'][] = "Data type '{$type}' has no field mapping";
            } elseif (isset($field_mappings[$type]['status']) && $field_mappings[$type]['status'] === 'placeholder') {
                // Silently skip placeholder configurations - they're expected to be incomplete
                continue;
            }
        }
        
        return $validation;
    }
    
    /**
     * 🔄 LEGACY COMPATIBILITY - Map old configurations to new structure
     */
    public static function get_legacy_field_mapping($generator_type) {
        $legacy_mappings = [
            'topics' => [
                8498 => 'topic_1',
                8499 => 'topic_2', 
                8500 => 'topic_3',
                8501 => 'topic_4',
                8502 => 'topic_5'
            ],
            'questions' => [
                // Topic 1
                8505 => 'question_1',
                8506 => 'question_2',
                8507 => 'question_3', 
                8508 => 'question_4',
                8509 => 'question_5',
                // Topic 2
                8510 => 'question_6',
                8511 => 'question_7',
                8512 => 'question_8',
                8513 => 'question_9',
                8514 => 'question_10',
                // Continue for all 25 questions...
            ]
        ];
        
        return $legacy_mappings[$generator_type] ?? [];
    }
    
    /**
     * 🔍 DATA FLOW VALIDATION - Validate that data extraction is working correctly
     */
    public static function validate_data_extraction($entry_id, $data_type = 'topics') {
        $validation_result = [
            'success' => false,
            'entry_id' => $entry_id,
            'data_type' => $data_type,
            'fields_tested' => 0,
            'fields_found' => 0,
            'field_details' => [],
            'errors' => [],
            'timestamp' => time()
        ];
        
        if (!$entry_id) {
            $validation_result['errors'][] = 'No entry ID provided';
            return $validation_result;
        }
        
        // Get field mappings for the data type
        $field_mappings = self::get_field_mappings();
        if (!isset($field_mappings[$data_type])) {
            $validation_result['errors'][] = "No field mappings found for data type: {$data_type}";
            return $validation_result;
        }
        
        $fields_to_test = $field_mappings[$data_type]['fields'];
        $validation_result['fields_tested'] = count($fields_to_test);
        
        // Test database connectivity
        global $wpdb;
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        
        foreach ($fields_to_test as $field_key => $field_id) {
            $field_test = [
                'field_key' => $field_key,
                'field_id' => $field_id,
                'found' => false,
                'value_length' => 0,
                'value_preview' => '',
                'raw_type' => 'unknown'
            ];
            
            try {
                $raw_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$item_metas_table} WHERE item_id = %d AND field_id = %d",
                    $entry_id, $field_id
                ));
                
                if ($raw_value !== null) {
                    $field_test['found'] = true;
                    $field_test['raw_type'] = gettype($raw_value);
                    $field_test['value_length'] = is_string($raw_value) ? strlen($raw_value) : 0;
                    $field_test['value_preview'] = substr((string)$raw_value, 0, 50);
                    $validation_result['fields_found']++;
                }
                
            } catch (Exception $e) {
                $field_test['error'] = $e->getMessage();
                $validation_result['errors'][] = "Field {$field_id} query failed: " . $e->getMessage();
            }
            
            $validation_result['field_details'][$field_key] = $field_test;
        }
        
        $validation_result['success'] = ($validation_result['fields_found'] > 0);
        
        return $validation_result;
    }
}
?>