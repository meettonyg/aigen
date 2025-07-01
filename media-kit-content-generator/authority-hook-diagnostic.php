<?php
/**
 * Authority Hook Field Diagnostic Script
 * 
 * This script will help diagnose why fields 10297, 10387, and 10298 
 * are not loading correctly in the Topics Generator
 * 
 * USAGE: Place this file in the plugin root and access via browser
 */

// Prevent direct access except for debugging
if (!defined('ABSPATH')) {
    // For debugging purposes, simulate WordPress environment
    // NOTE: Remove this in production
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
    require_once ABSPATH . 'wp-config.php';
    require_once ABSPATH . 'wp-includes/wp-db.php';
    global $wpdb;
}

// Entry ID from the console logs
$entry_id = 74492;

// Fields we need to diagnose
$target_fields = [
    '10296' => 'WHO (working)',
    '10297' => 'RESULT (not working)', 
    '10387' => 'WHEN (not working)',
    '10298' => 'HOW (not working)',
    '10358' => 'COMPLETE (reference)'
];

echo "<h2>Authority Hook Field Diagnostic Report</h2>";
echo "<h3>Entry ID: {$entry_id}</h3>";
echo "<hr>";

// Check if entry exists
$entry_exists = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $entry_id
));

if (!$entry_exists) {
    echo "<p style='color: red;'>ERROR: Entry {$entry_id} does not exist!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Entry {$entry_id} exists in frm_items table</p>";

// Get all field data for this entry
$all_fields = $wpdb->get_results($wpdb->prepare(
    "SELECT fm.field_id, fm.meta_value, ff.name, ff.field_key, ff.type 
     FROM {$wpdb->prefix}frm_item_metas fm 
     LEFT JOIN {$wpdb->prefix}frm_fields ff ON fm.field_id = ff.id
     WHERE fm.item_id = %d
     ORDER BY fm.field_id",
    $entry_id
), ARRAY_A);

echo "<h3>All Fields for Entry {$entry_id}:</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Field ID</th><th>Field Name</th><th>Field Key</th><th>Type</th><th>Raw Value</th><th>Value Length</th><th>Is Serialized?</th></tr>";

foreach ($all_fields as $field) {
    $is_serialized = is_serialized($field['meta_value']) ? 'YES' : 'NO';
    $value_length = strlen($field['meta_value']);
    $display_value = htmlspecialchars(substr($field['meta_value'], 0, 100));
    if (strlen($field['meta_value']) > 100) {
        $display_value .= '...';
    }
    
    // Highlight target fields
    $row_style = '';
    if (array_key_exists($field['field_id'], $target_fields)) {
        $row_style = ' style="background-color: #ffffcc; font-weight: bold;"';
    }
    
    echo "<tr{$row_style}>";
    echo "<td>{$field['field_id']}</td>";
    echo "<td>{$field['name']}</td>";
    echo "<td>{$field['field_key']}</td>";
    echo "<td>{$field['type']}</td>";
    echo "<td>{$display_value}</td>";
    echo "<td>{$value_length}</td>";
    echo "<td>{$is_serialized}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Detailed Analysis of Target Fields:</h3>";

foreach ($target_fields as $field_id => $description) {
    echo "<h4>Field {$field_id} - {$description}</h4>";
    
    // Get the raw value
    $raw_value = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
        $entry_id, $field_id
    ));
    
    if ($raw_value === null) {
        echo "<p style='color: red;'>❌ NO DATA FOUND</p>";
        continue;
    }
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<p><strong>Raw Value:</strong> " . htmlspecialchars($raw_value) . "</p>";
    echo "<p><strong>Length:</strong> " . strlen($raw_value) . "</p>";
    echo "<p><strong>Type:</strong> " . gettype($raw_value) . "</p>";
    
    // Check if serialized
    if (is_serialized($raw_value)) {
        echo "<p style='color: orange;'>⚠️ Value is SERIALIZED</p>";
        
        // Try to unserialize
        $unserialized = @unserialize($raw_value);
        if ($unserialized !== false) {
            echo "<p style='color: green;'>✅ Successfully unserialized</p>";
            echo "<p><strong>Unserialized Value:</strong></p>";
            echo "<pre>" . htmlspecialchars(print_r($unserialized, true)) . "</pre>";
            
            // Try to extract meaningful value
            if (is_array($unserialized)) {
                $first_value = '';
                foreach ($unserialized as $key => $value) {
                    if (!empty(trim($value))) {
                        $first_value = trim($value);
                        break;
                    }
                }
                echo "<p><strong>Extracted First Non-Empty Value:</strong> " . htmlspecialchars($first_value) . "</p>";
            } else {
                echo "<p><strong>Direct Value:</strong> " . htmlspecialchars($unserialized) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to unserialize - possibly corrupted</p>";
            
            // Try regex extraction as fallback
            if (preg_match('/"([^"]+)"/', $raw_value, $matches)) {
                echo "<p style='color: blue;'>💡 Regex extracted: " . htmlspecialchars($matches[1]) . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ Value is NOT serialized (plain text)</p>";
        echo "<p><strong>Direct Value:</strong> " . htmlspecialchars($raw_value) . "</p>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h3>Recommendations:</h3>";

// Generate recommendations based on findings
echo "<ul>";
echo "<li>Check if the non-working fields have different serialization format than field 10296</li>";
echo "<li>Verify that the Formidable service's process_field_value_enhanced() method handles all serialization types</li>";
echo "<li>Test the field value processing with the actual data shown above</li>";
echo "<li>Consider adding specific handling for these field IDs if they have unique formats</li>";
echo "</ul>";

// Quick test of the actual processing method
echo "<h3>Testing Formidable Service Processing:</h3>";

// Load the Formidable service if available
$plugin_path = dirname(__FILE__) . '/includes/services/class-mkcg-formidable-service.php';
if (file_exists($plugin_path)) {
    require_once $plugin_path;
    
    if (class_exists('MKCG_Formidable_Service')) {
        $formidable_service = new MKCG_Formidable_Service();
        
        echo "<div style='background-color: #f0f0f0; padding: 10px;'>";
        echo "<h4>Live Processing Test:</h4>";
        
        foreach ($target_fields as $field_id => $description) {
            $raw_value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($raw_value !== null) {
                // Test the processing method
                try {
                    $processed_value = $formidable_service->process_field_value_enhanced($raw_value, $field_id);
                    echo "<p><strong>{$description}:</strong> ";
                    echo "Raw: '" . htmlspecialchars(substr($raw_value, 0, 50)) . "' ";
                    echo "→ Processed: '" . htmlspecialchars($processed_value) . "'</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'><strong>{$description}:</strong> Processing failed - " . $e->getMessage() . "</p>";
                }
            }
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ MKCG_Formidable_Service class not available for testing</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Formidable service file not found for testing</p>";
}

echo "<hr>";
echo "<p><em>Diagnostic completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>