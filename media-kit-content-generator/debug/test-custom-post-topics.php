<?php
/**
 * Test Custom Post Topics Retrieval
 * Tests the CORRECTED approach using custom post meta instead of field 10081
 */

// Bootstrap WordPress if running directly
if (!defined('ABSPATH')) {
    // Adjust path as needed
    require_once('../../../../../../wp-config.php');
}

echo '<div style="font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; border-radius: 8px; margin: 20px;">';
echo '<h2>🔧 Custom Post Topics Retrieval Test</h2>';

// Test entry details
$entry_key = 'y8ver';
$entry_id = 74492;

echo '<p><strong>Testing Entry:</strong> ' . $entry_key . ' (ID: ' . $entry_id . ')</p>';

// Test 1: Check if MKCG service is available
echo '<h3>📋 Test 1: MKCG Service Availability</h3>';

if (class_exists('MKCG_Formidable_Service')) {
    echo '<p style="color: green;">✅ MKCG_Formidable_Service class found</p>';
    
    $service = new MKCG_Formidable_Service();
    
    // Test 2: Get post ID from entry
    echo '<h3>🔗 Test 2: Get Associated Post ID</h3>';
    
    $post_id = $service->get_post_id_from_entry($entry_id);
    
    if ($post_id) {
        echo '<p style="color: green;">✅ <strong>Found associated post ID:</strong> ' . $post_id . '</p>';
        
        // Get post details
        $post = get_post($post_id);
        if ($post) {
            echo '<div style="background: white; padding: 10px; border-radius: 4px; margin: 10px 0;">';
            echo '<strong>Post Details:</strong><br>';
            echo 'Title: ' . esc_html($post->post_title) . '<br>';
            echo 'Type: ' . esc_html($post->post_type) . '<br>';
            echo 'Status: ' . esc_html($post->post_status) . '<br>';
            echo 'Date: ' . esc_html($post->post_date) . '<br>';
            echo '</div>';
        }
        
        // Test 3: Get topics from post meta
        echo '<h3>🎯 Test 3: Get Topics from Post Meta</h3>';
        
        $topics = $service->get_topics_from_post($post_id);
        
        if (!empty($topics)) {
            echo '<p style="color: green;">✅ <strong>SUCCESS!</strong> Found ' . count($topics) . ' topics in post meta</p>';
            
            echo '<div style="background: white; padding: 10px; border-radius: 4px; margin: 10px 0;">';
            echo '<strong>Topics Found:</strong><br>';
            foreach ($topics as $num => $topic) {
                echo '<div style="background: #e8f5e8; padding: 5px; margin: 5px 0; border-radius: 4px;">';
                echo 'Topic ' . $num . ': ' . esc_html($topic);
                echo '</div>';
            }
            echo '</div>';
            
            // Test 4: JavaScript format
            echo '<h3>📱 Test 4: JavaScript Compatibility</h3>';
            echo '<p>Topics ready for Questions Generator:</p>';
            echo '<div style="background: #f0f0f0; padding: 10px; border-radius: 4px; font-family: monospace;">';
            echo '<pre>' . json_encode($topics, JSON_PRETTY_PRINT) . '</pre>';
            echo '</div>';
            
        } else {
            echo '<p style="color: orange;">⚠️ No topics found in post meta</p>';
            
            // Debug: Check what post meta IS available
            echo '<h4>🔍 Available Post Meta Fields</h4>';
            
            $all_meta = get_post_meta($post_id);
            
            if (!empty($all_meta)) {
                echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
                echo '<tr style="background: #ddd;"><th>Meta Key</th><th>Value Preview</th></tr>';
                foreach ($all_meta as $key => $values) {
                    $value = is_array($values) ? $values[0] : $values;
                    $preview = is_string($value) ? substr($value, 0, 100) : print_r($value, true);
                    echo '<tr>';
                    echo '<td style="padding: 5px; font-weight: bold;">' . esc_html($key) . '</td>';
                    echo '<td style="padding: 5px;">' . esc_html($preview) . '...</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No post meta found for post ' . $post_id . '</p>';
            }
        }
        
    } else {
        echo '<p style="color: red;">❌ <strong>No associated post found</strong></p>';
        
        // Debug: Check the frm_items table
        echo '<h4>🔍 Debug: Check frm_items table</h4>';
        
        global $wpdb;
        
        $item_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        if ($item_data) {
            echo '<div style="background: white; padding: 10px; border-radius: 4px;">';
            echo '<strong>Entry Data:</strong><br>';
            foreach ($item_data as $key => $value) {
                echo $key . ': ' . esc_html($value) . '<br>';
            }
            echo '</div>';
        } else {
            echo '<p>Entry not found in frm_items table</p>';
        }
    }
    
} else {
    echo '<p style="color: red;">❌ MKCG_Formidable_Service class not found</p>';
    echo '<p>Make sure the plugin is loaded and active.</p>';
}

// Test 5: Compare with old approach
echo '<h3>📊 Test 5: Compare with Previous Approach</h3>';

global $wpdb;
$item_metas_table = $wpdb->prefix . 'frm_item_metas';

$field_10081_content = $wpdb->get_var($wpdb->prepare(
    "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = 10081",
    $entry_id
));

if ($field_10081_content) {
    echo '<p style="color: orange;">⚠️ Field 10081 still contains data (but we\'re not using it anymore)</p>';
    echo '<div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0;">';
    echo '<strong>Field 10081 content:</strong><br>';
    echo '<pre style="font-size: 12px;">' . esc_html(substr($field_10081_content, 0, 300)) . '...</pre>';
    echo '</div>';
} else {
    echo '<p style="color: green;">✅ Field 10081 is empty (as expected with custom post approach)</p>';
}

echo '<div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 4px; border: 1px solid #c3e6cb;">';
echo '<strong>📋 Implementation Status:</strong><br>';
echo '✅ Questions Generator updated to use custom post meta<br>';
echo '✅ Formidable Service updated with post meta methods<br>';
echo '✅ Template updated to use custom post approach<br>';
echo '✅ No longer dependent on field 10081<br>';
echo '</div>';

echo '<div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">';
echo '<strong>🎯 Next Steps:</strong><br>';
echo '1. Test the Questions Generator page: <code>/questions/?entry=y8ver</code><br>';
echo '2. Check WordPress debug logs for detailed messages<br>';
echo '3. Verify Topics Generator saves to post meta when generating new topics<br>';
echo '4. Confirm Questions Generator loads topics correctly<br>';
echo '</div>';

echo '</div>';
?>
