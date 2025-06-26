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
}