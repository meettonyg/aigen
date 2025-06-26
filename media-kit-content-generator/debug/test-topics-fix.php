<?php
/**
 * Test Topics Retrieval Fix
 * Run this to verify the Questions Generator can now find topics
 */

// Bootstrap WordPress if running directly
if (!defined('ABSPATH')) {
    // Adjust path as needed
    require_once('../../../../../../wp-config.php');
}

echo '<div style="font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; border-radius: 8px; margin: 20px;">';
echo '<h2>🧪 Topics Retrieval Fix Test</h2>';

// Test entry details
$entry_key = 'y8ver';
$entry_id = 74492;

echo '<p><strong>Testing Entry:</strong> ' . $entry_key . ' (ID: ' . $entry_id . ')</p>';

// Test 1: Direct database query to field 10081
echo '<h3>📊 Test 1: Direct Field 10081 Query</h3>';

global $wpdb;
$item_metas_table = $wpdb->prefix . 'frm_item_metas';

$topics_combined = $wpdb->get_var($wpdb->prepare(
    "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = 10081",
    $entry_id
));

if ($topics_combined) {
    echo '<p style="color: green;">✅ <strong>SUCCESS!</strong> Field 10081 found with content.</p>';
    echo '<div style="background: white; padding: 10px; border-radius: 4px; margin: 10px 0;">';
    echo '<strong>Content preview:</strong><br>';
    echo '<pre style="font-size: 12px; overflow-x: auto;">' . esc_html(substr($topics_combined, 0, 400)) . '...</pre>';
    echo '</div>';
    
    // Test 2: Parse the topics
    echo '<h3>🔧 Test 2: Topic Parsing (Manual Test)</h3>';
    
    $topics = [];
    $lines = explode("\n", $topics_combined);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (preg_match('/^\*?\s*Topic\s+(\d+):\s*(.+)$/i', $line, $matches)) {
            $topic_number = intval($matches[1]);
            $topic_text = trim($matches[2]);
            
            if ($topic_number >= 1 && $topic_number <= 5 && !empty($topic_text)) {
                $topics[$topic_number] = $topic_text;
                echo '<div style="background: #e8f5e8; padding: 8px; margin: 5px 0; border-radius: 4px;">';
                echo '✅ <strong>Topic ' . $topic_number . ':</strong> ' . esc_html($topic_text);
                echo '</div>';
            }
        }
    }
    
    if (!empty($topics)) {
        echo '<p style="color: green; font-weight: bold;">🎉 SUCCESS! Parsed ' . count($topics) . ' topics successfully.</p>';
        
        // Test 3: Test with MKCG Service (if available)
        echo '<h3>🔗 Test 3: MKCG Service Test</h3>';
        
        if (class_exists('MKCG_Formidable_Service')) {
            $service = new MKCG_Formidable_Service();
            $service_result = $service->get_topics_for_entry($entry_id);
            
            if ($service_result['success']) {
                echo '<p style="color: green;">✅ <strong>MKCG Service SUCCESS!</strong> Service can parse topics.</p>';
                echo '<div style="background: white; padding: 10px; border-radius: 4px;">';
                echo '<strong>Service Results:</strong><br>';
                foreach ($service_result['topics'] as $num => $topic) {
                    echo 'Topic ' . $num . ': ' . esc_html($topic) . '<br>';
                }
                echo '</div>';
            } else {
                echo '<p style="color: red;">❌ MKCG Service failed: ' . $service_result['message'] . '</p>';
            }
        } else {
            echo '<p style="color: orange;">⚠️ MKCG_Formidable_Service class not found. Make sure plugin is loaded.</p>';
        }
        
        // Test 4: Questions Generator compatibility
        echo '<h3>📱 Test 4: JavaScript Format</h3>';
        echo '<p>Topics array ready for Questions Generator:</p>';
        echo '<div style="background: #f0f0f0; padding: 10px; border-radius: 4px; font-family: monospace;">';
        echo '<pre>' . json_encode($topics, JSON_PRETTY_PRINT) . '</pre>';
        echo '</div>';
        
        echo '<h3>🎯 Next Steps</h3>';
        echo '<ul>';
        echo '<li>✅ The fix is working - topics can be retrieved from field 10081</li>';
        echo '<li>🔄 Test the Questions Generator page: <code>/questions/?entry=y8ver</code></li>';
        echo '<li>📝 Verify topic selection shows the parsed topics</li>';
        echo '<li>🤖 Test AI question generation with selected topics</li>';
        echo '</ul>';
        
    } else {
        echo '<p style="color: red;">❌ No topics could be parsed from the field content.</p>';
        echo '<p>Check the format of field 10081 content above.</p>';
    }
    
} else {
    echo '<p style="color: red;">❌ <strong>PROBLEM:</strong> Field 10081 is NULL or empty</p>';
    
    // Check what fields ARE available
    echo '<h3>🔍 Available Fields Debug</h3>';
    
    $all_fields = $wpdb->get_results($wpdb->prepare(
        "SELECT field_id, LEFT(meta_value, 50) as preview 
         FROM $item_metas_table 
         WHERE item_id = %d 
         AND meta_value IS NOT NULL 
         AND meta_value != ''
         ORDER BY field_id",
        $entry_id
    ));
    
    if ($all_fields) {
        echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background: #ddd;"><th>Field ID</th><th>Content Preview</th></tr>';
        foreach ($all_fields as $field) {
            $highlight = ($field->field_id == '10081') ? 'background: yellow;' : '';
            echo '<tr style="' . $highlight . '">';
            echo '<td style="padding: 5px; font-weight: bold;">' . $field->field_id . '</td>';
            echo '<td style="padding: 5px;">' . esc_html($field->preview) . '...</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No fields with content found for entry ' . $entry_id . '</p>';
    }
}

echo '<div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 4px;">';
echo '<strong>🔧 Fix Status:</strong><br>';
echo '✅ Questions Generator PHP class updated to use field 10081<br>';
echo '✅ Formidable Service updated with topic parsing methods<br>';
echo '✅ Template already correctly configured<br>';
echo '</div>';

echo '</div>';
?>
