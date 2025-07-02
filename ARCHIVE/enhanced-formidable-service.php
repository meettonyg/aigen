<?php
/**
 * Simplified Formidable Service
 * Single responsibility: Handle Formidable Forms data operations cleanly
 * Eliminates: Dual-save complexity, excessive error tracking, multiple fallback strategies
 */

class Enhanced_Formidable_Service {
    
    /**
     * Simple constructor - no complex initialization
     */
    public function __construct() {
        // That's it. No phase loading, no race condition workarounds.
    }
    
    /**
     * Save data to Formidable entry - single, direct approach
     */
    public function save_entry_data($entry_id, $field_data) {
        if (!$entry_id || empty($field_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'frm_item_metas';
        $saved_count = 0;
        
        foreach ($field_data as $field_id => $value) {
            if (empty($value)) continue;
            
            // Check if field exists, update or insert accordingly
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($exists) {
                $result = $wpdb->update(
                    $table,
                    ['meta_value' => $value],
                    ['item_id' => $entry_id, 'field_id' => $field_id],
                    ['%s'],
                    ['%d', '%d']
                );
            } else {
                $result = $wpdb->insert(
                    $table,
                    ['item_id' => $entry_id, 'field_id' => $field_id, 'meta_value' => $value],
                    ['%d', '%d', '%s']
                );
            }
            
            if ($result !== false) {
                $saved_count++;
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Data saved successfully' : 'No data saved'
        ];
    }
    
    /**
     * Get field value from entry - direct database query
     */
    public function get_field_value($entry_id, $field_id) {
        if (!$entry_id || !$field_id) {
            return '';
        }
        
        global $wpdb;
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
            $entry_id, $field_id
        ));
        
        return $this->process_field_value($value);
    }
    
    /**
     * Get all field data for an entry
     */
    public function get_entry_data($entry_id) {
        if (!$entry_id) {
            return ['success' => false, 'message' => 'No entry ID provided'];
        }
        
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d",
            $entry_id
        ), ARRAY_A);
        
        if (empty($results)) {
            return ['success' => false, 'message' => 'No data found'];
        }
        
        $field_data = [];
        foreach ($results as $row) {
            $field_data[$row['field_id']] = $this->process_field_value($row['meta_value']);
        }
        
        return [
            'success' => true,
            'entry_id' => $entry_id,
            'field_data' => $field_data
        ];
    }
    
    /**
     * Simple field value processing - no complex serialization handling
     */
    private function process_field_value($value) {
        if (empty($value)) {
            return '';
        }
        
        // Handle basic serialized data
        if (is_serialized($value)) {
            $unserialized = @unserialize($value);
            if (is_array($unserialized) && !empty($unserialized)) {
                return trim(array_values($unserialized)[0]);
            }
            if (is_string($unserialized)) {
                return trim($unserialized);
            }
        }
        
        return trim($value);
    }
    
    /**
     * Get entry ID from post ID
     */
    public function get_entry_id_from_post($post_id) {
        if (!$post_id) {
            return null;
        }
        
        // Check post meta first
        $entry_id = get_post_meta($post_id, '_frm_entry_id', true);
        if ($entry_id) {
            return intval($entry_id);
        }
        
        // Direct database query as fallback
        global $wpdb;
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
            $post_id
        ));
        
        return $entry_id ? intval($entry_id) : null;
    }
    
    /**
     * Get post ID from entry ID
     */
    public function get_post_id_from_entry($entry_id) {
        if (!$entry_id) {
            return null;
        }
        
        global $wpdb;
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        return $post_id ? intval($post_id) : null;
    }
    
    /**
     * Save data to WordPress post meta
     */
    public function save_post_meta($post_id, $meta_data) {
        if (!$post_id || empty($meta_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $saved_count = 0;
        foreach ($meta_data as $meta_key => $meta_value) {
            if (empty($meta_value)) continue;
            
            $result = update_post_meta($post_id, $meta_key, $meta_value);
            if ($result !== false) {
                $saved_count++;
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Meta data saved successfully' : 'No meta data saved'
        ];
    }
}
