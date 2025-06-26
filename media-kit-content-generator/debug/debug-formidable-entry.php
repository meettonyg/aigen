<?php
/**
 * Debug Tool for Formidable Entry Investigation
 * Add this as a shortcode [debug_formidable_entry] or visit directly
 */

// Prevent direct access if not in WordPress context
if (!defined('ABSPATH')) {
    // For direct access, you'll need to bootstrap WordPress
    require_once('../../../../../wp-config.php');
}

function debug_formidable_entry() {
    // Get entry from URL parameter
    $entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
    $entry_id = isset($_GET['entry_id']) ? intval($_GET['entry_id']) : 0;
    
    if (!$entry_key && !$entry_id) {
        return '<p>Please provide entry key: ?entry=y8ver or entry ID: ?entry_id=123</p>';
    }
    
    // Check if Formidable is available
    if (!class_exists('FrmEntry')) {
        return '<p>❌ Formidable Forms is not active or available</p>';
    }
    
    // Try to get the Formidable service
    if (class_exists('MKCG_Formidable_Service')) {
        $formidable_service = new MKCG_Formidable_Service();
    } else {
        return '<p>❌ MKCG_Formidable_Service not available</p>';
    }
    
    $output = '<div style="font-family: monospace; background: #f9f9f9; padding: 20px; border-radius: 8px;">';
    $output .= '<h2>🔍 Formidable Entry Debug Tool</h2>';
    
    // Try different resolution methods
    if ($entry_key) {
        $output .= '<h3>📝 Entry Key: ' . esc_html($entry_key) . '</h3>';
        
        // Method 1: Using our service
        $entry_data = $formidable_service->get_entry_data($entry_key);
        
        if ($entry_data['success']) {
            $output .= '<p>✅ Entry found via MKCG service</p>';
            $output .= '<p><strong>Entry ID:</strong> ' . $entry_data['entry_id'] . '</p>';
            $entry_id = $entry_data['entry_id'];
        } else {
            $output .= '<p>❌ Entry not found via MKCG service: ' . $entry_data['message'] . '</p>';
            
            // Method 2: Direct database query
            global $wpdb;
            $frm_entries_table = $wpdb->prefix . 'frm_items';
            
            $direct_entry_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $frm_entries_table WHERE item_key = %s",
                $entry_key
            ));
            
            if ($direct_entry_id) {
                $output .= '<p>✅ Entry found via direct query: ID ' . $direct_entry_id . '</p>';
                $entry_id = $direct_entry_id;
            } else {
                $output .= '<p>❌ Entry not found in database</p>';
                $output .= '<p><strong>Checked table:</strong> ' . $frm_entries_table . '</p>';
                
                // Show available entries for comparison
                $sample_entries = $wpdb->get_results(
                    "SELECT id, item_key FROM $frm_entries_table ORDER BY id DESC LIMIT 10"
                );
                
                if ($sample_entries) {
                    $output .= '<p><strong>Recent entries for comparison:</strong></p>';
                    $output .= '<ul>';
                    foreach ($sample_entries as $sample) {
                        $output .= '<li>ID: ' . $sample->id . ', Key: ' . $sample->item_key . '</li>';
                    }
                    $output .= '</ul>';
                }
                
                $output .= '</div>';
                return $output;
            }
        }
    }
    
    if (!$entry_id) {
        $output .= '<p>❌ No valid entry ID found</p>';
        $output .= '</div>';
        return $output;
    }
    
    // Now debug the entry fields
    $output .= '<h3>📊 Entry Fields Analysis</h3>';
    
    // Get all fields for this entry
    global $wpdb;
    $item_metas_table = $wpdb->prefix . 'frm_item_metas';
    $fields_table = $wpdb->prefix . 'frm_fields';
    
    $all_fields = $wpdb->get_results($wpdb->prepare(
        "SELECT fm.field_id, fm.meta_value, ff.name, ff.field_key, ff.type
         FROM $item_metas_table fm 
         LEFT JOIN $fields_table ff ON fm.field_id = ff.id
         WHERE fm.item_id = %d
         ORDER BY fm.field_id",
        $entry_id
    ));
    
    if (empty($all_fields)) {
        $output .= '<p>❌ No fields found for this entry</p>';
    } else {
        $output .= '<p>✅ Found ' . count($all_fields) . ' fields</p>';
        
        // Topic fields specifically
        $topic_fields = ['8498', '8499', '8500', '8501', '8502'];
        $output .= '<h4>🎯 Topic Fields (8498-8502)</h4>';
        $output .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $output .= '<tr><th>Field ID</th><th>Name</th><th>Value</th><th>Status</th></tr>';
        
        $topics_found = 0;
        foreach ($topic_fields as $topic_field_id) {
            $field_found = false;
            foreach ($all_fields as $field) {
                if ($field->field_id == $topic_field_id) {
                    $field_found = true;
                    $has_value = !empty($field->meta_value);
                    $status = $has_value ? '✅ HAS VALUE' : '⚠️ EMPTY';
                    if ($has_value) $topics_found++;
                    
                    $output .= '<tr>';
                    $output .= '<td>' . $field->field_id . '</td>';
                    $output .= '<td>' . ($field->name ?: 'Unknown') . '</td>';
                    $output .= '<td>' . substr($field->meta_value, 0, 100) . ($field->meta_value && strlen($field->meta_value) > 100 ? '...' : '') . '</td>';
                    $output .= '<td>' . $status . '</td>';
                    $output .= '</tr>';
                    break;
                }
            }
            
            if (!$field_found) {
                $output .= '<tr>';
                $output .= '<td>' . $topic_field_id . '</td>';
                $output .= '<td>—</td>';
                $output .= '<td>—</td>';
                $output .= '<td>❌ NOT FOUND</td>';
                $output .= '</tr>';
            }
        }
        $output .= '</table>';
        
        $output .= '<p><strong>Topics with values: ' . $topics_found . '/5</strong></p>';
        
        // All fields for reference
        $output .= '<h4>📋 All Fields</h4>';
        $output .= '<details><summary>Click to expand all fields</summary>';
        $output .= '<table border="1" style="border-collapse: collapse; width: 100%; font-size: 12px;">';
        $output .= '<tr><th>Field ID</th><th>Name</th><th>Type</th><th>Value Preview</th></tr>';
        
        foreach ($all_fields as $field) {
            $output .= '<tr>';
            $output .= '<td>' . $field->field_id . '</td>';
            $output .= '<td>' . ($field->name ?: 'Unknown') . '</td>';
            $output .= '<td>' . ($field->type ?: 'Unknown') . '</td>';
            $output .= '<td>' . substr($field->meta_value, 0, 50) . ($field->meta_value && strlen($field->meta_value) > 50 ? '...' : '') . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        $output .= '</details>';
    }
    
    // Test the MKCG service
    $output .= '<h3>🧪 MKCG Service Test</h3>';
    
    $service_entry_data = $formidable_service->get_entry_data($entry_id);
    if ($service_entry_data['success']) {
        $output .= '<p>✅ MKCG service can read entry</p>';
        $service_topics = [];
        
        foreach ($topic_fields as $index => $field_id) {
            if (isset($service_entry_data['fields'][$field_id])) {
                $field_value = $service_entry_data['fields'][$field_id]['value'];
                if (!empty($field_value)) {
                    $service_topics[$index + 1] = $field_value;
                }
            }
        }
        
        $output .= '<p><strong>Topics found by service:</strong> ' . count($service_topics) . '</p>';
        if (!empty($service_topics)) {
            $output .= '<ul>';
            foreach ($service_topics as $num => $topic) {
                $output .= '<li>Topic ' . $num . ': ' . esc_html($topic) . '</li>';
            }
            $output .= '</ul>';
        }
    } else {
        $output .= '<p>❌ MKCG service failed: ' . $service_entry_data['message'] . '</p>';
    }
    
    $output .= '</div>';
    
    return $output;
}

// If accessed directly
if (!function_exists('add_shortcode')) {
    echo debug_formidable_entry();
} else {
    // Add as shortcode
    add_shortcode('debug_formidable_entry', 'debug_formidable_entry');
}
?>