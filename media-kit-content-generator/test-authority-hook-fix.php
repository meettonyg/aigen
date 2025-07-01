<?php
/**
 * CRITICAL FIX TEST SCRIPT
 * Tests the Authority Hook field processing fixes
 * 
 * Place this file in the plugin root and access via browser
 * URL: /wp-content/plugins/media-kit-content-generator/test-authority-hook-fix.php
 */

// Load WordPress
$wp_config_path = dirname(__FILE__) . '/../../../../wp-config.php';
if (file_exists($wp_config_path)) {
    require_once $wp_config_path;
} else {
    die('WordPress not found. Please check the path.');
}

// Load our Formidable service
require_once dirname(__FILE__) . '/includes/services/class-mkcg-formidable-service.php';

// Entry ID from console logs
$entry_id = 74492;

echo "<h2>CRITICAL FIX TEST - Authority Hook Field Processing</h2>";
echo "<h3>Entry ID: {$entry_id}</h3>";
echo "<hr>";

// Initialize the Formidable service
$formidable_service = new MKCG_Formidable_Service();

echo "<h3>Testing Enhanced Field Processing</h3>";

// Run diagnostic
$diagnosis = $formidable_service->diagnose_authority_hook_fields($entry_id);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Field ID</th><th>Description</th><th>Raw Data Found</th><th>Processed Value</th><th>Processing Method</th><th>Status</th></tr>";

foreach ($diagnosis as $field_id => $result) {
    $status = 'UNKNOWN';
    $bg_color = '#ffffff';
    
    if ($result['raw_value'] === null) {
        $status = 'NO DATA';
        $bg_color = '#ffcccc';
    } elseif (!empty($result['processed_value']) && $result['processed_value'] !== 'achieve their goals' && $result['processed_value'] !== 'they need help' && $result['processed_value'] !== 'through your method') {
        $status = 'SUCCESS - REAL DATA';
        $bg_color = '#ccffcc';
    } elseif (!empty($result['processed_value'])) {
        $status = 'DEFAULT VALUE';
        $bg_color = '#ffffcc';
    } else {
        $status = 'FAILED';
        $bg_color = '#ffcccc';
    }
    
    echo "<tr style='background-color: {$bg_color};'>";
    echo "<td>{$field_id}</td>";
    echo "<td>{$result['description']}</td>";
    echo "<td>" . ($result['raw_value'] ? 'YES (' . $result['raw_length'] . ' chars)' : 'NO') . "</td>";
    echo "<td>" . htmlspecialchars($result['processed_value'] ?: 'NULL') . "</td>";
    echo "<td>{$result['processing_method']}</td>";
    echo "<td><strong>{$status}</strong></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Full Entry Data Test</h3>";

// Test the full entry data retrieval
$entry_data = $formidable_service->get_entry_data($entry_id);

if ($entry_data['success']) {
    echo "<p style='color: green;'>✅ Entry data retrieved successfully</p>";
    
    $authority_fields = ['10296', '10297', '10387', '10298', '10358'];
    
    echo "<h4>Authority Hook Field Values from get_entry_data:</h4>";
    echo "<ul>";
    
    foreach ($authority_fields as $field_id) {
        if (isset($entry_data['fields'][$field_id])) {
            $field = $entry_data['fields'][$field_id];
            $value = $field['value'];
            $quality = $field['data_quality'];
            
            echo "<li><strong>Field {$field_id}:</strong> '{$value}' (Quality: {$quality})</li>";
        } else {
            echo "<li><strong>Field {$field_id}:</strong> NOT FOUND</li>";
        }
    }
    
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Failed to retrieve entry data: " . $entry_data['message'] . "</p>";
}

echo "<hr>";
echo "<h3>Direct Database Check</h3>";

global $wpdb;
$direct_fields = $wpdb->get_results($wpdb->prepare(
    "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas 
     WHERE item_id = %d AND field_id IN ('10296', '10297', '10387', '10298', '10358')
     ORDER BY field_id",
    $entry_id
), ARRAY_A);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Field ID</th><th>Raw Database Value</th><th>Length</th><th>Serialized?</th></tr>";

foreach ($direct_fields as $field) {
    $is_serialized = is_serialized($field['meta_value']) ? 'YES' : 'NO';
    $display_value = htmlspecialchars(substr($field['meta_value'], 0, 100));
    if (strlen($field['meta_value']) > 100) {
        $display_value .= '...';
    }
    
    echo "<tr>";
    echo "<td>{$field['field_id']}</td>";
    echo "<td>{$display_value}</td>";
    echo "<td>" . strlen($field['meta_value']) . "</td>";
    echo "<td>{$is_serialized}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><strong>Fix Status:</strong> The critical fix has been applied to the Formidable service. If fields 10297, 10387, and 10298 still show default values, check the console logs for detailed processing information.</p>";
echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>