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
     * Get topics from custom post meta fields
     * This is where the Topics Generator saves the generated topics
     */
    public function get_topics_from_post($post_id) {
        $topics = [];
        
        // Common topic meta field patterns
        $topic_meta_patterns = [
            'topic_%d',          // topic_1, topic_2, etc.
            'interview_topic_%d', // interview_topic_1, etc.
            'topic%d',           // topic1, topic2, etc.
            'generated_topic_%d'  // generated_topic_1, etc.
        ];
        
        foreach ($topic_meta_patterns as $pattern) {
            $found_topics = [];
            
            // Check for 5 topics (1-5)
            for ($i = 1; $i <= 5; $i++) {
                $meta_key = sprintf($pattern, $i);
                $topic_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($topic_value)) {
                    $found_topics[$i] = trim($topic_value);
                    error_log("MKCG Formidable: Found topic {$i} in meta key '{$meta_key}': " . substr($topic_value, 0, 50) . '...');
                }
            }
            
            // If we found topics with this pattern, use them
            if (!empty($found_topics)) {
                error_log('MKCG Formidable: Using topic pattern: ' . $pattern);
                return $found_topics;
            }
        }
        
        // Try alternative: single meta field with all topics (like your field 10081 but in post meta)
        $all_topics_patterns = [
            'all_topics',
            'generated_topics',
            'interview_topics',
            'topics_list'
        ];
        
        foreach ($all_topics_patterns as $meta_key) {
            $topics_data = get_post_meta($post_id, $meta_key, true);
            
            if (!empty($topics_data)) {
                error_log('MKCG Formidable: Found combined topics in meta key: ' . $meta_key);
                
                // If it's an array, use it directly
                if (is_array($topics_data)) {
                    $parsed_topics = [];
                    foreach ($topics_data as $index => $topic) {
                        if (!empty($topic)) {
                            $parsed_topics[$index + 1] = trim($topic);
                        }
                    }
                    if (!empty($parsed_topics)) {
                        return $parsed_topics;
                    }
                }
                
                // If it's a string, try to parse it
                if (is_string($topics_data)) {
                    $parsed_topics = $this->parse_topics_string($topics_data);
                    if (!empty($parsed_topics)) {
                        return $parsed_topics;
                    }
                }
            }
        }
        
        error_log('MKCG Formidable: No topics found in post meta for post ' . $post_id);
        return [];
    }
    
    /**
     * Parse topics from string format (similar to field 10081 but more flexible)
     */
    private function parse_topics_string($topics_string) {
        $topics = [];
        
        if (empty($topics_string)) {
            return $topics;
        }
        
        // Try JSON first
        $json_decoded = json_decode($topics_string, true);
        if (is_array($json_decoded)) {
            foreach ($json_decoded as $index => $topic) {
                if (!empty($topic)) {
                    $topics[is_numeric($index) ? $index + 1 : count($topics) + 1] = trim($topic);
                }
            }
            return $topics;
        }
        
        // Parse line-by-line format (like field 10081)
        $lines = explode("\n", $topics_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for "Topic X:" or "* Topic X:" patterns
            if (preg_match('/^\*?\s*Topic\s+(\d+):\s*(.+)$/i', $line, $matches)) {
                $topic_number = intval($matches[1]);
                $topic_text = trim($matches[2]);
                
                if ($topic_number >= 1 && $topic_number <= 5 && !empty($topic_text)) {
                    $topics[$topic_number] = $topic_text;
                }
            }
            // Also try numbered list format "1. Topic text"
            elseif (preg_match('/^\s*(\d+)\.\s*(.+)$/i', $line, $matches)) {
                $topic_number = intval($matches[1]);
                $topic_text = trim($matches[2]);
                
                if ($topic_number >= 1 && $topic_number <= 5 && !empty($topic_text)) {
                    $topics[$topic_number] = $topic_text;
                }
            }
        }
        
        return $topics;
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
     * Get questions from custom post meta for a specific topic
     */
    public function get_questions_from_post($post_id, $topic_number = null) {
        $questions = [];
        
        if ($topic_number) {
            // Get questions for specific topic (1-5)
            for ($i = 1; $i <= 5; $i++) {
                $question_number = (($topic_number - 1) * 5) + $i; // Calculate global question number
                $meta_key = 'question_' . $question_number;
                $question_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($question_value)) {
                    $questions[$i] = trim($question_value);
                    error_log("MKCG Formidable: Found question {$i} for topic {$topic_number}: " . substr($question_value, 0, 50) . '...');
                }
            }
        } else {
            // Get all questions (1-25)
            for ($i = 1; $i <= 25; $i++) {
                $meta_key = 'question_' . $i;
                $question_value = get_post_meta($post_id, $meta_key, true);
                
                if (!empty($question_value)) {
                    $questions[$i] = trim($question_value);
                }
            }
        }
        
        return $questions;
    }
    
    /**
     * Save questions to custom post meta (for Questions Generator)
     */
    public function save_questions_to_post($post_id, $questions, $topic_number) {
        if (!$post_id || empty($questions)) {
            return false;
        }
        
        $saved_count = 0;
        
        // Save questions with global numbering
        foreach ($questions as $index => $question) {
            if (!empty($question)) {
                $question_number = (($topic_number - 1) * 5) + ($index + 1); // Calculate global question number
                $meta_key = 'question_' . $question_number;
                $result = update_post_meta($post_id, $meta_key, trim($question));
                
                if ($result !== false) {
                    $saved_count++;
                    error_log("MKCG Formidable: Saved question {$question_number} (topic {$topic_number}, pos {$index}) to post meta: {$meta_key}");
                }
            }
        }
        
        error_log("MKCG Formidable: Saved {$saved_count} questions for topic {$topic_number} to post {$post_id}");
        return $saved_count > 0;
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
}