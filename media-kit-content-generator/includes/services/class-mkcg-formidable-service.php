<?php
/**
 * MKCG Formidable Service
 * Handles all Formidable Forms interactions
 */

class MKCG_Formidable_Service {
    
    /**
     * Get entry data by entry ID or entry key
     */
    public function get_entry_data($entry_identifier) {
        global $wpdb;
        
        $entry_id = 0;
        
        // If it's numeric, treat as entry ID
        if (is_numeric($entry_identifier)) {
            $entry_id = intval($entry_identifier);
        } else {
            // Treat as entry key and resolve to ID
            $frm_entries_table = $wpdb->prefix . 'frm_items';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$frm_entries_table'") == $frm_entries_table) {
                $entry_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $frm_entries_table WHERE item_key = %s",
                    $entry_identifier
                ));
            }
        }
        
        if (!$entry_id) {
            return [
                'success' => false,
                'message' => 'Invalid entry identifier: ' . $entry_identifier
            ];
        }
        
        // Debug: Log the entry ID we're working with
        error_log('MKCG Formidable Service: Working with entry ID: ' . $entry_id);
        
        // Get all field values for this entry - ENHANCED QUERY
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $fields_table = $wpdb->prefix . 'frm_fields';
        
        // Try multiple query approaches
        
        // Method 1: With field join (preferred)
        $all_meta_values = $wpdb->get_results($wpdb->prepare(
            "SELECT fm.field_id, fm.meta_value, ff.name, ff.field_key 
             FROM $item_metas_table fm 
             LEFT JOIN $fields_table ff ON fm.field_id = ff.id
             WHERE fm.item_id = %d
             ORDER BY fm.field_id",
            $entry_id
        ), ARRAY_A);
        
        error_log('MKCG Formidable Service: Method 1 found ' . count($all_meta_values) . ' fields');
        
        // Method 2: Direct meta query (fallback)
        if (empty($all_meta_values)) {
            $all_meta_values = $wpdb->get_results($wpdb->prepare(
                "SELECT field_id, meta_value, field_id as name, field_id as field_key 
                 FROM $item_metas_table 
                 WHERE item_id = %d
                 ORDER BY field_id",
                $entry_id
            ), ARRAY_A);
            
            error_log('MKCG Formidable Service: Method 2 found ' . count($all_meta_values) . ' fields');
        }
        
        // Method 3: Check specifically for our topic fields
        $topic_fields = ['8498', '8499', '8500', '8501', '8502'];
        $specific_check = [];
        
        foreach ($topic_fields as $field_id) {
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($value !== null) {
                $specific_check[] = "Field {$field_id}: '{$value}'";
                
                // Add to our results if not already there
                $found = false;
                foreach ($all_meta_values as $existing) {
                    if ($existing['field_id'] == $field_id) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $all_meta_values[] = [
                        'field_id' => $field_id,
                        'meta_value' => $value,
                        'name' => 'Topic Field ' . $field_id,
                        'field_key' => 'topic_' . $field_id
                    ];
                }
            }
        }
        
        error_log('MKCG Formidable Service: Specific topic field check: ' . implode(', ', $specific_check));
        
        if (empty($all_meta_values)) {
            error_log('MKCG Formidable Service: No field data found for entry ID: ' . $entry_id);
            return [
                'success' => false,
                'message' => 'No field data found for entry ID: ' . $entry_id
            ];
        }
        
        // Organize the data
        $field_data = [];
        foreach ($all_meta_values as $meta) {
            $field_data[$meta['field_id']] = [
                'id' => $meta['field_id'],
                'name' => $meta['name'] ?: 'Unknown',
                'key' => $meta['field_key'] ?: '',
                'value' => $meta['meta_value']
            ];
        }
        
        // Debug: Log what we found
        $found_field_ids = array_keys($field_data);
        error_log('MKCG Formidable Service: Found field IDs: ' . implode(', ', $found_field_ids));
        
        // Check specifically for topic fields
        $topic_found = [];
        foreach ($topic_fields as $field_id) {
            if (isset($field_data[$field_id]) && !empty($field_data[$field_id]['value'])) {
                $topic_found[] = "Field {$field_id}: '" . substr($field_data[$field_id]['value'], 0, 50) . "'";
            }
        }
        
        if (!empty($topic_found)) {
            error_log('MKCG Formidable Service: Topic fields found: ' . implode(', ', $topic_found));
        } else {
            error_log('MKCG Formidable Service: NO topic fields found in fields: ' . implode(', ', $found_field_ids));
        }
        
        return [
            'success' => true,
            'entry_id' => $entry_id,
            'fields' => $field_data,
            'raw_data' => $all_meta_values,
            'debug_info' => [
                'total_fields' => count($field_data),
                'topic_fields_found' => count($topic_found),
                'specific_check' => $specific_check
            ]
        ];
    }
    
    /**
     * Find Authority Hook field using multiple strategies
     * Based on your Topics generator logic
     */
    public function find_authority_hook($entry_id) {
        global $wpdb;
        
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $fields_table = $wpdb->prefix . 'frm_fields';
        
        // Possible field names for Authority Hook
        $possible_names = ['authority_hook', 'authorityhook', 'authority', 'hook', 'expert', 'bio', 'introduction'];
        
        // Get all field values for this entry
        $all_meta_values = $wpdb->get_results($wpdb->prepare(
            "SELECT fm.field_id, fm.meta_value, ff.name, ff.field_key 
             FROM $item_metas_table fm 
             JOIN $fields_table ff ON fm.field_id = ff.id
             WHERE fm.item_id = %d",
            $entry_id
        ), ARRAY_A);
        
        error_log('MKCG Formidable Service: Found ' . count($all_meta_values) . ' field values for entry ' . $entry_id);
        
        // Strategy 1: Look for fields matching common Authority Hook names
        foreach ($all_meta_values as $meta) {
            $field_name = strtolower($meta['name']);
            $field_key = strtolower($meta['field_key']);
            
            foreach ($possible_names as $name) {
                if (strpos($field_name, $name) !== false || strpos($field_key, $name) !== false) {
                    if (!empty($meta['meta_value'])) {
                        error_log('MKCG Formidable Service: Found Authority Hook by name: ' . $meta['name']);
                        return [
                            'success' => true,
                            'field_id' => $meta['field_id'],
                            'value' => $meta['meta_value'],
                            'method' => 'name_match'
                        ];
                    }
                }
            }
        }
        
        // Strategy 2: Try known field ID for complete Authority Hook (Form 515)
        $known_field_id = 10358; // Complete Authority Hook field
        $direct_query = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = %d",
            $entry_id, $known_field_id
        ));
        
        if ($direct_query) {
            error_log('MKCG Formidable Service: Found Authority Hook using known field ID ' . $known_field_id);
            return [
                'success' => true,
                'field_id' => $known_field_id,
                'value' => $direct_query,
                'method' => 'known_field_id'
            ];
        }
        
        // Strategy 2b: Try to build from components if complete hook is empty
        $component_fields = [
            'who' => 10296,    // WHO do you help?
            'result' => 10297, // WHAT result do you help them achieve?
            'when' => 10387,   // WHEN do they need you?
            'how' => 10298     // HOW do you help them?
        ];
        
        $components = [];
        $has_components = false;
        
        foreach ($component_fields as $component => $field_id) {
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($value) {
                $components[$component] = $value;
                $has_components = true;
            } else {
                $components[$component] = $this->get_default_component($component);
            }
        }
        
        if ($has_components) {
            $built_hook = "I help {$components['who']} {$components['result']} when {$components['when']} {$components['how']}.";
            error_log('MKCG Formidable Service: Built Authority Hook from components');
            
            // Save the built hook to the complete field
            $wpdb->replace(
                $item_metas_table,
                [
                    'item_id' => $entry_id,
                    'field_id' => $known_field_id,
                    'meta_value' => $built_hook
                ],
                ['%d', '%d', '%s']
            );
            
            return [
                'success' => true,
                'field_id' => $known_field_id,
                'value' => $built_hook,
                'method' => 'built_from_components'
            ];
        }
        
        // Strategy 3: Find the longest text field as fallback
        foreach ($all_meta_values as $meta) {
            if (!empty($meta['meta_value']) && strlen($meta['meta_value']) > 50) {
                error_log('MKCG Formidable Service: Using fallback field: ' . $meta['name']);
                return [
                    'success' => true,
                    'field_id' => $meta['field_id'],
                    'value' => $meta['meta_value'],
                    'method' => 'fallback_longest'
                ];
            }
        }
        
        // Generate debug information
        $field_info = [];
        foreach ($all_meta_values as $meta) {
            $field_info[] = "ID: {$meta['field_id']}, Name: {$meta['name']}, Value: " . substr($meta['meta_value'], 0, 20) . "...";
        }
        
        error_log('MKCG Formidable Service: No Authority Hook found. Available fields: ' . implode('; ', $field_info));
        
        return [
            'success' => false,
            'message' => 'No Authority Hook field found for this entry. Please fill in your Authority Hook first.',
            'debug_fields' => $field_info
        ];
    }
    
    /**
     * Get field value by field ID
     */
    public function get_field_value($entry_id, $field_id) {
        global $wpdb;
        
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = %d",
            $entry_id, $field_id
        ));
        
        return $value ? $value : '';
    }
    
    /**
     * Save generated content back to form fields
     */
    public function save_generated_content($entry_id, $content, $field_mappings) {
        global $wpdb;
        
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $saved_fields = [];
        
        foreach ($field_mappings as $content_key => $field_id) {
            if (isset($content[$content_key])) {
                $content_value = is_array($content[$content_key]) ? 
                    json_encode($content[$content_key]) : 
                    $content[$content_key];
                
                // Check if the field already exists
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = %d",
                    $entry_id, $field_id
                ));
                
                if ($existing !== null) {
                    // Update existing field
                    $result = $wpdb->update(
                        $item_metas_table,
                        ['meta_value' => $content_value],
                        ['item_id' => $entry_id, 'field_id' => $field_id],
                        ['%s'],
                        ['%d', '%d']
                    );
                } else {
                    // Insert new field
                    $result = $wpdb->insert(
                        $item_metas_table,
                        [
                            'item_id' => $entry_id,
                            'field_id' => $field_id,
                            'meta_value' => $content_value
                        ],
                        ['%d', '%d', '%s']
                    );
                }
                
                if ($result !== false) {
                    $saved_fields[$content_key] = $field_id;
                }
            }
        }
        
        return [
            'success' => count($saved_fields) > 0,
            'saved_fields' => $saved_fields
        ];
    }
    
    /**
     * Debug entry fields (from your Topics generator)
     */
    public function debug_entry_fields($entry_id) {
        $debug_info = [
            'entry_id' => $entry_id,
            'fields' => []
        ];
        
        global $wpdb;
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $fields_table = $wpdb->prefix . 'frm_fields';
        
        $query = $wpdb->prepare(
            "SELECT fm.field_id, fm.meta_value, ff.name, ff.field_key 
             FROM $item_metas_table fm 
             JOIN $fields_table ff ON fm.field_id = ff.id
             WHERE fm.item_id = %d",
            $entry_id
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        if ($results) {
            foreach ($results as $row) {
                $debug_info['fields'][] = [
                    'field_id' => $row['field_id'],
                    'name' => $row['name'],
                    'field_key' => $row['field_key'],
                    'value_preview' => substr($row['meta_value'], 0, 100)
                ];
            }
        } else {
            $debug_info['error'] = 'No fields found for this entry';
        }
        
        return $debug_info;
    }
    
    /**
     * Check if Formidable is installed and active
     */
    public function is_formidable_active() {
        return class_exists('FrmEntry') || class_exists('FrmForm');
    }
    
    /**
     * Get current entry ID from URL parameters
     */
    public function get_current_entry_id() {
        if (isset($_GET['entry'])) {
            $entry_key = sanitize_text_field($_GET['entry']);
            $entry_data = $this->get_entry_data($entry_key);
            
            if ($entry_data['success']) {
                return $entry_data['entry_id'];
            }
        }
        
        return 0;
    }
    
    /**
     * Get default component values for Authority Hook
     */
    private function get_default_component($component) {
        $defaults = [
            'who' => 'your audience',
            'result' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method'
        ];
        
        return isset($defaults[$component]) ? $defaults[$component] : '';
    }
    
    /**
     * Get post ID associated with Formidable entry
     * This is how we connect to the custom post
     */
    public function get_post_id_from_entry($entry_id) {
        global $wpdb;
        
        // Method 1: Check if there's a direct post_id field in the entry
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        if ($post_id) {
            error_log('MKCG Formidable: Found post ID via frm_items.post_id: ' . $post_id);
            return $post_id;
        }
        
        // Method 2: Look for post ID in item_metas (common Formidable pattern)
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas 
             WHERE item_id = %d 
             AND field_id IN (
                 SELECT id FROM {$wpdb->prefix}frm_fields 
                 WHERE type = 'hidden' 
                 AND (field_key LIKE '%post_id%' OR name LIKE '%post%')
             )",
            $entry_id
        ));
        
        if ($post_id && is_numeric($post_id)) {
            error_log('MKCG Formidable: Found post ID via meta field: ' . $post_id);
            return intval($post_id);
        }
        
        // Method 3: Check for "Create Post" action results
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d AND post_id IS NOT NULL",
            $entry_id
        ));
        
        if ($post_id) {
            error_log('MKCG Formidable: Found post ID via create post action: ' . $post_id);
            return $post_id;
        }
        
        error_log('MKCG Formidable: No post ID found for entry ' . $entry_id);
        return false;
    }
    
    /**
     * ENHANCED BULLETPROOF DATA RETRIEVAL - Get topics with comprehensive validation
     */
    public function get_topics_from_post_enhanced($post_id) {
        $retrieval_result = [
            'topics' => [],
            'data_quality' => 'unknown',
            'source_pattern' => 'none',
            'validation_status' => [],
            'auto_healed' => false,
            'metadata' => []
        ];
        
        if (!$post_id) {
            $retrieval_result['validation_status'][] = 'No post ID provided';
            return $retrieval_result;
        }
        
        // Priority-ordered topic meta field patterns
        $topic_meta_patterns = [
            'topic_%d',          // topic_1, topic_2, etc. (PRIMARY)
            'interview_topic_%d', // interview_topic_1, etc.
            'generated_topic_%d', // generated_topic_1, etc.
            'topic%d',           // topic1, topic2, etc.
            'content_topic_%d'   // content_topic_1, etc.
        ];
        
        $best_topics = [];
        $best_quality_score = 0;
        $best_pattern = 'none';
        
        // Try each pattern and score the results
        foreach ($topic_meta_patterns as $pattern) {
            $found_topics = [];
            $quality_score = 0;
            
            // Check for 5 topics (1-5)
            for ($i = 1; $i <= 5; $i++) {
                $meta_key = sprintf($pattern, $i);
                $topic_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($topic_value)) {
                    $validated_topic = $this->validate_topic_content($topic_value);
                    if ($validated_topic['valid']) {
                        $found_topics[$i] = $validated_topic['cleaned_content'];
                        $quality_score += $validated_topic['quality_score'];
                        error_log("MKCG Enhanced: Found quality topic {$i} in '{$meta_key}': " . substr($validated_topic['cleaned_content'], 0, 50) . '...');
                    }
                }
            }
            
            // Use this pattern if it has better quality than previous
            if ($quality_score > $best_quality_score) {
                $best_topics = $found_topics;
                $best_quality_score = $quality_score;
                $best_pattern = $pattern;
                error_log('MKCG Enhanced: New best pattern: ' . $pattern . ' (score: ' . $quality_score . ')');
            }
        }
        
        // Try alternative: single meta field with all topics (enhanced parsing)
        if (empty($best_topics) || $best_quality_score < 15) { // Minimum quality threshold
            $all_topics_patterns = [
                'all_topics',
                'generated_topics', 
                'interview_topics',
                'topics_list',
                'combined_topics'
            ];
            
            foreach ($all_topics_patterns as $meta_key) {
                $topics_data = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($topics_data)) {
                    error_log('MKCG Enhanced: Found combined topics in meta key: ' . $meta_key);
                    
                    $parsed_topics = $this->parse_combined_topics_data($topics_data);
                    if (!empty($parsed_topics)) {
                        $combined_quality = $this->calculate_topics_quality($parsed_topics);
                        
                        if ($combined_quality > $best_quality_score) {
                            $best_topics = $parsed_topics;
                            $best_quality_score = $combined_quality;
                            $best_pattern = 'combined_' . $meta_key;
                            error_log('MKCG Enhanced: Using combined pattern: ' . $meta_key . ' (score: ' . $combined_quality . ')');
                        }
                    }
                }
            }
        }
        
        // Ensure we always have 5 topic slots (data normalization)
        $normalized_topics = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($best_topics[$i]) && !empty(trim($best_topics[$i]))) {
                $normalized_topics[$i] = trim($best_topics[$i]);
            } else {
                $normalized_topics[$i] = ''; // Empty slot - will be handled by frontend
            }
        }
        
        // Determine data quality level
        $total_topics = count(array_filter($normalized_topics));
        if ($total_topics >= 4) {
            $data_quality = 'excellent';
        } elseif ($total_topics >= 2) {
            $data_quality = 'good';
        } elseif ($total_topics >= 1) {
            $data_quality = 'poor';
        } else {
            $data_quality = 'missing';
        }
        
        // Auto-heal if data quality is poor
        $auto_healed = false;
        if ($data_quality === 'poor' || $data_quality === 'missing') {
            $healing_result = $this->heal_missing_data($post_id, 5);
            if ($healing_result['success']) {
                $auto_healed = true;
                error_log('MKCG Enhanced: Auto-healed missing topic data for post ' . $post_id);
            }
        }
        
        $retrieval_result = [
            'topics' => $normalized_topics,
            'data_quality' => $data_quality,
            'source_pattern' => $best_pattern,
            'validation_status' => ['Topics retrieved successfully'],
            'auto_healed' => $auto_healed,
            'metadata' => [
                'total_topics' => $total_topics,
                'quality_score' => $best_quality_score,
                'timestamp' => time()
            ]
        ];
        
        if (empty($normalized_topics) || $total_topics === 0) {
            $retrieval_result['validation_status'] = ['No valid topics found in post meta for post ' . $post_id];
            error_log('MKCG Enhanced: No topics found in post meta for post ' . $post_id);
        } else {
            error_log('MKCG Enhanced: Retrieved ' . $total_topics . ' topics from post ' . $post_id . ' using pattern: ' . $best_pattern);
        }
        
        return $retrieval_result;
    }
    
    /**
     * Legacy wrapper for backward compatibility
     */
    public function get_topics_from_post($post_id) {
        $enhanced_result = $this->get_topics_from_post_enhanced($post_id);
        return $enhanced_result['topics'];
    }
    
    /**
     * Enhanced topic content validation with quality scoring
     */
    private function validate_topic_content($topic_content) {
        $validation = [
            'valid' => false,
            'cleaned_content' => '',
            'quality_score' => 0,
            'issues' => []
        ];
        
        if (empty($topic_content)) {
            $validation['issues'][] = 'Empty topic content';
            return $validation;
        }
        
        // Clean and sanitize the content
        $cleaned = trim(strip_tags($topic_content));
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Normalize whitespace
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned); // Remove control characters
        
        if (empty($cleaned)) {
            $validation['issues'][] = 'Topic content empty after cleaning';
            return $validation;
        }
        
        // Quality scoring (0-10 scale)
        $quality_score = 0;
        
        // Length check (optimal: 20-150 characters)
        $length = strlen($cleaned);
        if ($length >= 20 && $length <= 150) {
            $quality_score += 3;
        } elseif ($length >= 10 && $length <= 200) {
            $quality_score += 2;
        } elseif ($length >= 5) {
            $quality_score += 1;
        }
        
        // Content quality checks
        if (!preg_match('/^(Topic|Click|Add|Placeholder|Empty)/i', $cleaned)) {
            $quality_score += 2; // Not a placeholder
        }
        
        if (preg_match('/\b(how|what|why|when|guide|strategy|method|system|framework)\b/i', $cleaned)) {
            $quality_score += 2; // Contains topic-relevant keywords
        }
        
        if (preg_match('/\b(interview|podcast|discuss|talk|conversation)\b/i', $cleaned)) {
            $quality_score += 1; // Interview-relevant content
        }
        
        // Completeness check
        if (!preg_match('/\.\.\.|click|add|placeholder|empty|todo/i', $cleaned)) {
            $quality_score += 2; // Appears complete
        }
        
        $validation['valid'] = ($quality_score >= 3); // Minimum quality threshold
        $validation['cleaned_content'] = $cleaned;
        $validation['quality_score'] = $quality_score;
        
        if (!$validation['valid']) {
            $validation['issues'][] = 'Quality score too low: ' . $quality_score;
        }
        
        return $validation;
    }
    
    /**
     * Enhanced combined topics data parsing
     */
    private function parse_combined_topics_data($topics_data) {
        $topics = [];
        
        if (empty($topics_data)) {
            return $topics;
        }
        
        // Try JSON first (enhanced)
        if (is_string($topics_data)) {
            $json_decoded = json_decode($topics_data, true);
            if (is_array($json_decoded)) {
                foreach ($json_decoded as $index => $topic) {
                    if (!empty($topic)) {
                        $validated = $this->validate_topic_content($topic);
                        if ($validated['valid']) {
                            $key = is_numeric($index) ? $index + 1 : count($topics) + 1;
                            if ($key >= 1 && $key <= 5) {
                                $topics[$key] = $validated['cleaned_content'];
                            }
                        }
                    }
                }
                if (!empty($topics)) {
                    return $topics;
                }
            }
        }
        
        // Handle direct array
        if (is_array($topics_data)) {
            foreach ($topics_data as $index => $topic) {
                if (!empty($topic)) {
                    $validated = $this->validate_topic_content($topic);
                    if ($validated['valid']) {
                        $key = is_numeric($index) ? $index + 1 : count($topics) + 1;
                        if ($key >= 1 && $key <= 5) {
                            $topics[$key] = $validated['cleaned_content'];
                        }
                    }
                }
            }
            return $topics;
        }
        
        // Parse string formats (enhanced)
        if (is_string($topics_data)) {
            $lines = explode("\n", $topics_data);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Enhanced pattern matching
                $patterns = [
                    '/^\*?\s*Topic\s+(\d+):\s*(.+)$/i',           // "Topic 1: Content"
                    '/^\s*(\d+)\.\s*(.+)$/i',                     // "1. Content"
                    '/^\s*(\d+)\)\s*(.+)$/i',                     // "1) Content"
                    '/^\s*-\s*Topic\s+(\d+):\s*(.+)$/i',          // "- Topic 1: Content"
                    '/^\s*\[(\d+)\]\s*(.+)$/i'                   // "[1] Content"
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $line, $matches)) {
                        $topic_number = intval($matches[1]);
                        $topic_text = trim($matches[2]);
                        
                        if ($topic_number >= 1 && $topic_number <= 5 && !empty($topic_text)) {
                            $validated = $this->validate_topic_content($topic_text);
                            if ($validated['valid']) {
                                $topics[$topic_number] = $validated['cleaned_content'];
                            }
                        }
                        break; // Stop at first match
                    }
                }
            }
        }
        
        return $topics;
    }
    
    /**
     * Calculate overall quality score for a set of topics
     */
    private function calculate_topics_quality($topics) {
        $total_score = 0;
        $topic_count = 0;
        
        foreach ($topics as $topic) {
            if (!empty($topic)) {
                $validation = $this->validate_topic_content($topic);
                $total_score += $validation['quality_score'];
                $topic_count++;
            }
        }
        
        return $topic_count > 0 ? $total_score : 0;
    }
    
    /**
     * Legacy method - kept for backward compatibility
     */
    private function parse_topics_string($topics_string) {
        $result = $this->parse_combined_topics_data($topics_string);
        return $result;
    }
    
    /**
     * Save topics to custom post meta (for Topics Generator)
     */
    public function save_topics_to_post($post_id, $topics) {
        if (!$post_id || empty($topics)) {
            return false;
        }
        
        $saved_count = 0;
        
        // Save individual topic meta fields
        foreach ($topics as $index => $topic) {
            if (!empty($topic)) {
                $meta_key = 'topic_' . $index;
                $result = update_post_meta($post_id, $meta_key, trim($topic));
                
                if ($result !== false) {
                    $saved_count++;
                    error_log("MKCG Formidable: Saved topic {$index} to post meta: {$meta_key}");
                }
            }
        }
        
        // Also save as a combined array for backup
        update_post_meta($post_id, 'all_topics', $topics);
        
        error_log("MKCG Formidable: Saved {$saved_count} topics to post {$post_id}");
        return $saved_count > 0;
    }
    
    /**
     * CRITICAL FIX: Save single topic to custom post meta (for inline editing)
     */
    public function save_single_topic_to_post($post_id, $topic_number, $topic_text) {
        if (!$post_id || !$topic_number || ($topic_number < 1 || $topic_number > 5)) {
            return false;
        }
        
        $meta_key = 'topic_' . $topic_number;
        $result = update_post_meta($post_id, $meta_key, trim($topic_text));
        
        if ($result !== false) {
            // Also update the combined topics array
            $all_topics = get_post_meta($post_id, 'all_topics', true);
            if (!is_array($all_topics)) {
                $all_topics = [];
            }
            $all_topics[$topic_number] = trim($topic_text);
            update_post_meta($post_id, 'all_topics', $all_topics);
            
            error_log("MKCG Formidable: Saved single topic {$topic_number} to post {$post_id}: {$meta_key}");
            return true;
        }
        
        error_log("MKCG Formidable: Failed to save single topic {$topic_number} to post {$post_id}");
        return false;
    }
    
    /**
     * ENHANCED QUESTIONS RETRIEVAL - Get questions with integrity validation
     */
    public function get_questions_with_integrity_check($post_id, $topic_number = null) {
        $retrieval_result = [
            'questions' => [],
            'integrity_status' => 'unknown',
            'gaps_detected' => [],
            'validation_issues' => [],
            'auto_healed' => false,
            'metadata' => []
        ];
        
        if (!$post_id) {
            $retrieval_result['validation_issues'][] = 'No post ID provided';
            return $retrieval_result;
        }
        
        $questions = [];
        $gaps_detected = [];
        $validation_issues = [];
        
        if ($topic_number) {
            // Get questions for specific topic (1-5) with integrity checking
            for ($i = 1; $i <= 5; $i++) {
                $question_number = (($topic_number - 1) * 5) + $i; // Calculate global question number
                $meta_key = 'question_' . $question_number;
                $question_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($question_value)) {
                    $validated_question = $this->validate_question_content($question_value);
                    if ($validated_question['valid']) {
                        $questions[$i] = $validated_question['cleaned_content'];
                        error_log("MKCG Enhanced: Found quality question {$i} for topic {$topic_number}: " . substr($validated_question['cleaned_content'], 0, 50) . '...');
                    } else {
                        $validation_issues[] = "Question {$i} for topic {$topic_number} failed validation";
                        $gaps_detected[] = $meta_key;
                    }
                } else {
                    $gaps_detected[] = $meta_key;
                }
            }
        } else {
            // Get all questions (1-25) with integrity checking
            for ($i = 1; $i <= 25; $i++) {
                $meta_key = 'question_' . $i;
                $question_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($question_value)) {
                    $validated_question = $this->validate_question_content($question_value);
                    if ($validated_question['valid']) {
                        $questions[$i] = $validated_question['cleaned_content'];
                    } else {
                        $validation_issues[] = "Question {$i} failed validation";
                        $gaps_detected[] = $meta_key;
                    }
                } else {
                    $gaps_detected[] = $meta_key;
                }
            }
        }
        
        // Determine integrity status
        $total_expected = $topic_number ? 5 : 25;
        $total_found = count($questions);
        $gap_count = count($gaps_detected);
        
        if ($gap_count === 0 && count($validation_issues) === 0) {
            $integrity_status = 'excellent';
        } elseif ($total_found >= ($total_expected * 0.8)) {
            $integrity_status = 'good';
        } elseif ($total_found >= ($total_expected * 0.5)) {
            $integrity_status = 'fair';
        } else {
            $integrity_status = 'poor';
        }
        
        // Auto-heal if integrity is poor
        $auto_healed = false;
        if ($integrity_status === 'poor' || $integrity_status === 'fair') {
            $healing_result = $this->heal_missing_data($post_id, 5);
            if ($healing_result['questions_healed'] > 0) {
                $auto_healed = true;
                error_log('MKCG Enhanced: Auto-healed ' . $healing_result['questions_healed'] . ' questions for post ' . $post_id);
            }
        }
        
        $retrieval_result = [
            'questions' => $questions,
            'integrity_status' => $integrity_status,
            'gaps_detected' => $gaps_detected,
            'validation_issues' => $validation_issues,
            'auto_healed' => $auto_healed,
            'metadata' => [
                'total_found' => $total_found,
                'total_expected' => $total_expected,
                'gap_count' => $gap_count,
                'timestamp' => time()
            ]
        ];
        
        return $retrieval_result;
    }
    
    /**
     * Legacy wrapper for backward compatibility
     */
    public function get_questions_from_post($post_id, $topic_number = null) {
        $enhanced_result = $this->get_questions_with_integrity_check($post_id, $topic_number);
        return $enhanced_result['questions'];
    }
    
    /**
     * Validate question content quality
     */
    private function validate_question_content($question_content) {
        $validation = [
            'valid' => false,
            'cleaned_content' => '',
            'quality_score' => 0,
            'issues' => []
        ];
        
        if (empty($question_content)) {
            $validation['issues'][] = 'Empty question content';
            return $validation;
        }
        
        // Clean and sanitize
        $cleaned = trim(strip_tags($question_content));
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned);
        
        if (empty($cleaned)) {
            $validation['issues'][] = 'Question content empty after cleaning';
            return $validation;
        }
        
        // Quality scoring for questions
        $quality_score = 0;
        
        // Length check (optimal: 15-200 characters)
        $length = strlen($cleaned);
        if ($length >= 15 && $length <= 200) {
            $quality_score += 3;
        } elseif ($length >= 8 && $length <= 250) {
            $quality_score += 2;
        } elseif ($length >= 5) {
            $quality_score += 1;
        }
        
        // Question format checks
        if (preg_match('/\?$/', $cleaned)) {
            $quality_score += 2; // Ends with question mark
        }
        
        if (preg_match('/^(what|how|why|when|where|which|who|can you|could you|would you|tell us|share|describe)/i', $cleaned)) {
            $quality_score += 2; // Starts with question word
        }
        
        // Not a placeholder
        if (!preg_match('/^(Question|Click|Add|Placeholder|Empty|Todo)/i', $cleaned)) {
            $quality_score += 2;
        }
        
        // Interview relevance
        if (preg_match('/\b(experience|story|example|advice|tip|strategy|approach|method)\b/i', $cleaned)) {
            $quality_score += 1;
        }
        
        $validation['valid'] = ($quality_score >= 4); // Minimum quality threshold
        $validation['cleaned_content'] = $cleaned;
        $validation['quality_score'] = $quality_score;
        
        if (!$validation['valid']) {
            $validation['issues'][] = 'Question quality score too low: ' . $quality_score;
        }
        
        return $validation;
    }
    
    /**
     * ENHANCED: Save questions to both post meta AND Formidable entry fields
     */
    public function save_questions_to_post($post_id, $questions, $topic_number) {
        error_log("MKCG DUAL SAVE: Starting save for topic {$topic_number} with " . count($questions) . " questions");
        
        if (!$post_id || empty($questions)) {
            error_log("MKCG DUAL SAVE: Early return - invalid parameters");
            return false;
        }
        
        $saved_count = 0;
        $formidable_saved = 0;
        
        // Get Formidable field mappings for this topic
        $field_mappings = $this->get_formidable_field_mappings($topic_number);
        error_log("MKCG DUAL SAVE: Field mappings for topic {$topic_number}: " . print_r($field_mappings, true));
        
        // Get entry ID associated with this post
        $entry_id = $this->get_entry_id_from_post($post_id);
        error_log("MKCG DUAL SAVE: Found entry ID: " . ($entry_id ?: 'NONE'));
        
        // Save questions with global numbering
        foreach ($questions as $index => $question) {
            $question_trimmed = trim($question);
            $question_number = (($topic_number - 1) * 5) + ($index + 1); // Calculate global question number
            $meta_key = 'question_' . $question_number;
            
            error_log("MKCG DUAL SAVE: Processing Q{$question_number} (topic {$topic_number}, index {$index}): '{$question_trimmed}'");
            
            if (!empty($question_trimmed)) {
                // SAVE 1: WordPress Post Meta (existing functionality)
                $meta_result = update_post_meta($post_id, $meta_key, $question_trimmed);
                
                if ($meta_result !== false) {
                    $saved_count++;
                    error_log("MKCG DUAL SAVE: ✅ Saved to post meta: {$meta_key}");
                } else {
                    error_log("MKCG DUAL SAVE: ❌ Failed to save to post meta: {$meta_key}");
                }
                
                // SAVE 2: Formidable Entry Field (NEW FUNCTIONALITY)
                if ($entry_id && isset($field_mappings[$index])) {
                    $formidable_field_id = $field_mappings[$index];
                    $formidable_result = $this->save_to_formidable_field($entry_id, $formidable_field_id, $question_trimmed);
                    
                    if ($formidable_result) {
                        $formidable_saved++;
                        error_log("MKCG DUAL SAVE: ✅ Saved to Formidable field {$formidable_field_id} (Q{$question_number})");
                    } else {
                        error_log("MKCG DUAL SAVE: ❌ Failed to save to Formidable field {$formidable_field_id} (Q{$question_number})");
                    }
                } else {
                    if (!$entry_id) {
                        error_log("MKCG DUAL SAVE: ⚠️ No entry ID - skipping Formidable save for Q{$question_number}");
                    } else {
                        error_log("MKCG DUAL SAVE: ⚠️ No field mapping for index {$index} - skipping Formidable save for Q{$question_number}");
                    }
                }
            }
        }
        
        error_log("MKCG DUAL SAVE: Summary - Post meta: {$saved_count}/" . count($questions) . ", Formidable: {$formidable_saved}/" . count($questions));
        
        // Return true if either save method worked
        return ($saved_count > 0 || $formidable_saved > 0);
    }
    
    /**
     * Get Formidable field mappings for a specific topic
     */
    private function get_formidable_field_mappings($topic_number) {
        // Field mapping for Questions Generator (from the PHP code)
        $field_mappings = [
            1 => ['8505', '8506', '8507', '8508', '8509'], // Topic 1 → Questions 1-5
            2 => ['8510', '8511', '8512', '8513', '8514'], // Topic 2 → Questions 6-10
            3 => ['10370', '10371', '10372', '10373', '10374'], // Topic 3 → Questions 11-15
            4 => ['10375', '10376', '10377', '10378', '10379'], // Topic 4 → Questions 16-20
            5 => ['10380', '10381', '10382', '10383', '10384']  // Topic 5 → Questions 21-25
        ];
        
        return $field_mappings[$topic_number] ?? [];
    }
    
    /**
     * Get entry ID from post ID (reverse lookup)
     */
    private function get_entry_id_from_post($post_id) {
        global $wpdb;
        
        // Look for entry that created this post
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
            $post_id
        ));
        
        return $entry_id ? intval($entry_id) : null;
    }
    
    /**
     * Save data directly to Formidable entry field
     */
    private function save_to_formidable_field($entry_id, $field_id, $value) {
        global $wpdb;
        
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
    }
    
    /**
     * Get all questions organized by topic
     */
    public function get_all_questions_by_topic($post_id) {
        $questions_by_topic = [];
        
        for ($topic = 1; $topic <= 5; $topic++) {
            $questions_by_topic[$topic] = $this->get_questions_from_post($post_id, $topic);
        }
        
        return $questions_by_topic;
    }
    
    /**
     * ENHANCED DATA VALIDATION - Validate post association integrity
     */
    public function validate_post_association($entry_id, $post_id) {
        $validation_result = [
            'valid' => false,
            'post_exists' => false,
            'post_accessible' => false,
            'meta_writable' => false,
            'issues' => [],
            'auto_fixed' => []
        ];
        
        // Check if post exists
        if (!$post_id) {
            $validation_result['issues'][] = 'No post ID provided';
            return $validation_result;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            $validation_result['issues'][] = 'Post does not exist: ' . $post_id;
            
            // Attempt auto-creation if we have entry data
            if ($entry_id) {
                $created_post = $this->create_missing_post_for_entry($entry_id);
                if ($created_post) {
                    $validation_result['auto_fixed'][] = 'Created missing post: ' . $created_post;
                    $post_id = $created_post;
                    $post = get_post($post_id);
                }
            }
        }
        
        if ($post) {
            $validation_result['post_exists'] = true;
            
            // Check if post is accessible
            if ($post->post_status === 'publish' || $post->post_status === 'draft' || $post->post_status === 'private') {
                $validation_result['post_accessible'] = true;
                
                // Test meta field write capability
                $test_meta_key = '_mkcg_test_' . time();
                $test_result = update_post_meta($post_id, $test_meta_key, 'test_value');
                if ($test_result !== false) {
                    $validation_result['meta_writable'] = true;
                    // Clean up test meta
                    delete_post_meta($post_id, $test_meta_key);
                } else {
                    $validation_result['issues'][] = 'Cannot write to post meta for post: ' . $post_id;
                }
            } else {
                $validation_result['issues'][] = 'Post not accessible (status: ' . $post->post_status . ')';
            }
        }
        
        // Overall validation
        $validation_result['valid'] = $validation_result['post_exists'] && 
                                    $validation_result['post_accessible'] && 
                                    $validation_result['meta_writable'];
        
        // Log validation results
        if (!$validation_result['valid']) {
            error_log('MKCG Data Validation FAILED for entry ' . $entry_id . ', post ' . $post_id . ': ' . implode(', ', $validation_result['issues']));
        } else {
            error_log('MKCG Data Validation PASSED for entry ' . $entry_id . ', post ' . $post_id);
        }
        
        if (!empty($validation_result['auto_fixed'])) {
            error_log('MKCG Auto-fixes applied: ' . implode(', ', $validation_result['auto_fixed']));
        }
        
        return $validation_result;
    }
    
    /**
     * Create missing post for entry (auto-healing)
     */
    private function create_missing_post_for_entry($entry_id) {
        try {
            $post_data = [
                'post_title' => 'Media Kit Entry ' . $entry_id,
                'post_content' => 'Auto-created post for Formidable entry ' . $entry_id,
                'post_status' => 'draft',
                'post_type' => 'post', // or your custom post type
                'meta_input' => [
                    '_mkcg_entry_id' => $entry_id,
                    '_mkcg_auto_created' => time()
                ]
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                // Update Formidable entry to link to this post
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'frm_items',
                    ['post_id' => $post_id],
                    ['id' => $entry_id],
                    ['%d'],
                    ['%d']
                );
                
                error_log('MKCG Auto-healing: Created post ' . $post_id . ' for entry ' . $entry_id);
                return $post_id;
            }
        } catch (Exception $e) {
            error_log('MKCG Auto-healing failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * ENHANCED DATA HEALING - Repair missing or corrupted data
     */
    public function heal_missing_data($post_id, $expected_topics = 5) {
        $healing_result = [
            'topics_healed' => 0,
            'questions_healed' => 0,
            'gaps_filled' => [],
            'issues_found' => [],
            'success' => false
        ];
        
        if (!$post_id) {
            $healing_result['issues_found'][] = 'No post ID provided';
            return $healing_result;
        }
        
        // Heal missing topic slots
        $current_topics = $this->get_topics_from_post($post_id);
        $topics_healed = 0;
        
        for ($i = 1; $i <= $expected_topics; $i++) {
            if (!isset($current_topics[$i]) || empty(trim($current_topics[$i]))) {
                $placeholder_topic = 'Topic ' . $i . ' - Click to add your interview topic';
                $meta_key = 'topic_' . $i;
                
                if (update_post_meta($post_id, $meta_key, $placeholder_topic)) {
                    $healing_result['gaps_filled'][] = 'topic_' . $i;
                    $topics_healed++;
                }
            }
        }
        
        $healing_result['topics_healed'] = $topics_healed;
        
        // Heal question numbering gaps
        $questions_healed = 0;
        for ($topic = 1; $topic <= 5; $topic++) {
            for ($q = 1; $q <= 5; $q++) {
                $question_number = (($topic - 1) * 5) + $q;
                $meta_key = 'question_' . $question_number;
                $existing_question = get_post_meta($post_id, $meta_key, true);
                
                if (empty($existing_question)) {
                    // Check if topic exists to determine if we should add placeholder
                    if (isset($current_topics[$topic]) && !empty(trim($current_topics[$topic]))) {
                        $placeholder_question = 'Question ' . $q . ' for Topic ' . $topic . ' - Click to add';
                        if (update_post_meta($post_id, $meta_key, $placeholder_question)) {
                            $healing_result['gaps_filled'][] = $meta_key;
                            $questions_healed++;
                        }
                    }
                }
            }
        }
        
        $healing_result['questions_healed'] = $questions_healed;
        $healing_result['success'] = ($topics_healed > 0 || $questions_healed > 0);
        
        if ($healing_result['success']) {
            error_log('MKCG Data Healing: Healed ' . $topics_healed . ' topics and ' . $questions_healed . ' questions for post ' . $post_id);
        }
        
        return $healing_result;
    }
}