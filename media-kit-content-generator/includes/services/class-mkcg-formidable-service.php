<?php

class MKCG_Formidable_Service {
    
    private $max_retry_attempts = 3;
    private $retry_delay_seconds = 2;
    
    public function save_to_both_locations($post_id, $topics_data, $authority_hook_data) {
        $result = [
            'success' => false,
            'post_meta' => [
                'success' => false,
                'saved_fields' => [],
                'errors' => []
            ],
            'formidable' => [
                'success' => false,
                'saved_fields' => [],
                'errors' => [],
                'entry_id' => null
            ],
            'overall_errors' => []
        ];
        
        if (!$post_id) {
            $result['overall_errors'][] = 'No post ID provided';
            return $result;
        }
        
        // Save to WordPress Post Meta
        $post_meta_result = $this->save_topics_and_authority_hook_to_post($post_id, $topics_data, $authority_hook_data);
        $result['post_meta'] = $post_meta_result;
        
        // Save to Formidable Entry
        $entry_id = $this->get_entry_id_from_post($post_id);
        if ($entry_id) {
            $result['formidable']['entry_id'] = $entry_id;
            $formidable_result = $this->save_topics_and_authority_hook_to_formidable($entry_id, $topics_data, $authority_hook_data);
            $result['formidable'] = array_merge($result['formidable'], $formidable_result);
        } else {
            $result['formidable']['errors'][] = 'Could not resolve entry ID';
            $result['overall_errors'][] = 'Entry ID resolution failed';
        }
        
        $result['success'] = $result['post_meta']['success'] || $result['formidable']['success'];
        
        if ($result['post_meta']['success'] && $result['formidable']['success']) {
            $result['status'] = 'excellent';
        } elseif ($result['post_meta']['success']) {
            $result['status'] = 'acceptable';
        } elseif ($result['formidable']['success']) {
            $result['status'] = 'partial';
        } else {
            $result['status'] = 'failed';
        }
        
        return $result;
    }
    
    public function save_topics_and_authority_hook_to_post($post_id, $topics_data, $authority_hook_data) {
        if (!$post_id) {
            return [
                'success' => false,
                'errors' => ['No post ID provided'],
                'saved_fields' => [],
                'saved_count' => 0
            ];
        }
        
        $saved_fields = [];
        $errors = [];
        $saved_count = 0;
        
        // Save topics
        if (!empty($topics_data) && is_array($topics_data)) {
            for ($i = 1; $i <= 5; $i++) {
                $topic_key = 'topic_' . $i;
                if (isset($topics_data[$topic_key]) && !empty(trim($topics_data[$topic_key]))) {
                    $meta_key = 'topic_' . $i;
                    $topic_value = trim($topics_data[$topic_key]);
                    
                    $result = update_post_meta($post_id, $meta_key, $topic_value);
                    if ($result !== false) {
                        $saved_fields['topics'][$topic_key] = $meta_key;
                        $saved_count++;
                    } else {
                        $errors[] = "Failed to save topic {$i} to post meta";
                    }
                }
            }
        }
        
        // Save authority hook components
        if (!empty($authority_hook_data) && is_array($authority_hook_data)) {
            $auth_components = ['who', 'result', 'when', 'how', 'complete'];
            foreach ($auth_components as $component) {
                if (isset($authority_hook_data[$component]) && !empty(trim($authority_hook_data[$component]))) {
                    $meta_key = 'authority_' . $component;
                    $component_value = trim($authority_hook_data[$component]);
                    
                    $result = update_post_meta($post_id, $meta_key, $component_value);
                    if ($result !== false) {
                        $saved_fields['authority_hook'][$component] = $meta_key;
                        $saved_count++;
                    } else {
                        $errors[] = "Failed to save authority {$component} to post meta";
                    }
                }
            }
        }
        
        // Save combined data
        update_post_meta($post_id, 'all_topics', $topics_data);
        update_post_meta($post_id, 'all_authority_hook', $authority_hook_data);
        update_post_meta($post_id, '_mkcg_last_save', current_time('mysql'));
        
        return [
            'success' => $saved_count > 0,
            'saved_fields' => $saved_fields,
            'errors' => $errors,
            'saved_count' => $saved_count
        ];
    }
    
    public function save_topics_and_authority_hook_to_formidable($entry_id, $topics_data, $authority_hook_data) {
        if (!$entry_id) {
            return [
                'success' => false,
                'errors' => ['No entry ID provided'],
                'saved_fields' => [],
                'total_saved' => 0
            ];
        }
        
        $saved_fields = [];
        $errors = [];
        $config = MKCG_Config::get_field_mappings();
        
        // Save topics
        if (!empty($topics_data) && isset($config['topics']['fields'])) {
            foreach ($config['topics']['fields'] as $topic_key => $field_id) {
                if (isset($topics_data[$topic_key]) && !empty(trim($topics_data[$topic_key]))) {
                    $topic_value = trim($topics_data[$topic_key]);
                    $result = $this->save_to_formidable_field($entry_id, $field_id, $topic_value);
                    
                    if ($result['success']) {
                        $saved_fields['topics'][$topic_key] = $field_id;
                    } else {
                        $errors[] = "Failed to save {$topic_key}: " . $result['error'];
                    }
                }
            }
        }
        
        // Save authority hook components
        if (!empty($authority_hook_data) && isset($config['authority_hook']['fields'])) {
            foreach ($config['authority_hook']['fields'] as $component => $field_id) {
                if (isset($authority_hook_data[$component]) && !empty(trim($authority_hook_data[$component]))) {
                    $component_value = trim($authority_hook_data[$component]);
                    $result = $this->save_to_formidable_field($entry_id, $field_id, $component_value);
                    
                    if ($result['success']) {
                        $saved_fields['authority_hook'][$component] = $field_id;
                    } else {
                        $errors[] = "Failed to save authority_{$component}: " . $result['error'];
                    }
                }
            }
        }
        
        // Update entry timestamp
        $this->update_entry_timestamp($entry_id);
        
        $total_saved = count($saved_fields, COUNT_RECURSIVE) - count($saved_fields);
        
        return [
            'success' => !empty($saved_fields),
            'saved_fields' => $saved_fields,
            'errors' => $errors,
            'entry_id' => $entry_id,
            'total_saved' => $total_saved
        ];
    }
    
    private function save_to_formidable_field($entry_id, $field_id, $value) {
        global $wpdb;
        
        if (!$entry_id || !$field_id || $value === null || $value === '') {
            return [
                'success' => false,
                'error' => 'Invalid parameters'
            ];
        }
        
        $table = $wpdb->prefix . 'frm_item_metas';
        
        // Check if field entry already exists
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
        
        if ($result !== false) {
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => 'Database operation failed: ' . $wpdb->last_error
            ];
        }
    }
    
    private function get_entry_id_from_post($post_id) {
        // Check post_meta
        $entry_id = get_post_meta($post_id, '_frm_entry_id', true);
        if ($entry_id && is_numeric($entry_id)) {
            return intval($entry_id);
        }
        
        // Check alternative post_meta
        $entry_id = get_post_meta($post_id, 'frm_entry_id', true);
        if ($entry_id && is_numeric($entry_id)) {
            update_post_meta($post_id, '_frm_entry_id', $entry_id);
            return intval($entry_id);
        }
        
        // Direct database query
        global $wpdb;
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
            $post_id
        ));
        
        if ($entry_id) {
            update_post_meta($post_id, '_frm_entry_id', $entry_id);
            return intval($entry_id);
        }
        
        return null;
    }
    
    private function update_entry_timestamp($entry_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'frm_items',
            ['updated_date' => current_time('mysql')],
            ['id' => $entry_id],
            ['%s'],
            ['%d']
        );
    }
    
    public function get_entry_data($entry_identifier) {
        global $wpdb;
        
        $entry_id = 0;
        
        if (is_numeric($entry_identifier)) {
            $entry_id = intval($entry_identifier);
        } else {
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
        
        // Get all field values for this entry
        $item_metas_table = $wpdb->prefix . 'frm_item_metas';
        $all_meta_values = $wpdb->get_results($wpdb->prepare(
            "SELECT field_id, meta_value FROM $item_metas_table WHERE item_id = %d ORDER BY field_id",
            $entry_id
        ), ARRAY_A);
        
        if (empty($all_meta_values)) {
            return [
                'success' => false,
                'message' => 'No field data found for entry ID: ' . $entry_id
            ];
        }
        
        $field_data = [];
        foreach ($all_meta_values as $meta) {
            $processed_value = $this->process_field_value($meta['meta_value'], $meta['field_id']);
            $field_data[$meta['field_id']] = [
                'id' => $meta['field_id'],
                'value' => $processed_value,
                'raw_value' => $meta['meta_value']
            ];
        }
        
        return [
            'success' => true,
            'entry_id' => $entry_id,
            'field_data' => $field_data
        ];
    }
    
    public function process_field_value($raw_value, $field_id) {
        if ($raw_value === null || $raw_value === '') {
            return '';
        }
        
        // Handle serialized data
        if (is_serialized($raw_value)) {
            $unserialized = @unserialize($raw_value);
            if ($unserialized !== false) {
                if (is_array($unserialized)) {
                    return !empty($unserialized) ? array_values($unserialized)[0] : '';
                }
                return (string) $unserialized;
            }
        }
        
        return trim((string) $raw_value);
    }
    
    public function get_field_value($entry_id, $field_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'frm_item_metas';
        $raw_value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$table} WHERE item_id = %d AND field_id = %d",
            $entry_id, $field_id
        ));
        
        return $this->process_field_value($raw_value, $field_id);
    }
    
    public function get_post_id_from_entry($entry_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
    }
}
