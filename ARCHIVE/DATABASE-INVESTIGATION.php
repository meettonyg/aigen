<?php
/**
 * DATABASE INVESTIGATION TOOL
 * Find where the old default values are stored
 */

if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once('../../../wp-config.php');
}

echo "<h1>🕵️ Database Investigation Tool</h1>\n";
echo "<h2>Finding where old default values are stored for Post ID: 32372</h2>\n";

$post_id = 32372;

echo "<h3>1. WordPress Post Meta Investigation</h3>\n";

// Get ALL post meta for this post
$all_meta = get_post_meta($post_id);
echo "<p><strong>All post meta for post $post_id:</strong></p>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background: #f0f0f0;'><th>Meta Key</th><th>Meta Value</th><th>Suspicious?</th></tr>\n";

foreach ($all_meta as $key => $values) {
    $value = is_array($values) ? $values[0] : $values;
    $suspicious = '';
    
    // Check if this contains our problem values
    if (strpos($value, 'your audience') !== false || 
        strpos($value, 'achieve their goals') !== false ||
        strpos($value, 'they need help') !== false ||
        strpos($value, 'through your method') !== false) {
        $suspicious = '🚨 CONTAINS OLD DEFAULTS!';
    }
    
    echo "<tr>\n";
    echo "<td><strong>$key</strong></td>\n";
    echo "<td>" . esc_html($value) . "</td>\n";
    echo "<td style='color: red;'>$suspicious</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h3>2. Specific Field Mappings Check</h3>\n";

// Check the specific fields mentioned in the Authority Hook Service
$field_mappings = [
    'who' => 'guest_title',
    'what' => 'hook_what',
    'when' => 'hook_when', 
    'how' => 'hook_how'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background: #f0f0f0;'><th>Component</th><th>Meta Key</th><th>Stored Value</th><th>Action Needed</th></tr>\n";

foreach ($field_mappings as $component => $meta_key) {
    $value = get_post_meta($post_id, $meta_key, true);
    $action = '';
    
    if (!empty($value)) {
        if (in_array($value, ['your audience', 'achieve their goals', 'they need help', 'through your method'])) {
            $action = '🗑️ DELETE THIS VALUE';
        } else {
            $action = '✅ Keep (real data)';
        }
    } else {
        $action = '✅ Already empty';
    }
    
    echo "<tr>\n";
    echo "<td><strong>$component</strong></td>\n";
    echo "<td>$meta_key</td>\n";
    echo "<td>" . esc_html($value) . "</td>\n";
    echo "<td style='font-weight: bold;'>$action</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h3>3. Pods Fields Investigation</h3>\n";

// Check if this is using Pods
if (function_exists('pods')) {
    try {
        $pod = pods('guests', $post_id);
        if ($pod && $pod->exists()) {
            echo "<p>✅ Post exists in Pods system</p>\n";
            
            // Check Pods fields
            $pods_fields_to_check = [
                'guest_title', 'hook_what', 'hook_when', 'hook_how',
                'authority_hook', 'who', 'what', 'when', 'how'
            ];
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'><th>Pods Field</th><th>Value</th><th>Suspicious?</th></tr>\n";
            
            foreach ($pods_fields_to_check as $field) {
                $value = $pod->field($field);
                $suspicious = '';
                
                if (!empty($value) && (
                    strpos($value, 'your audience') !== false || 
                    strpos($value, 'achieve their goals') !== false ||
                    strpos($value, 'they need help') !== false ||
                    strpos($value, 'through your method') !== false)) {
                    $suspicious = '🚨 OLD DEFAULT FOUND!';
                }
                
                echo "<tr>\n";
                echo "<td><strong>$field</strong></td>\n";
                echo "<td>" . esc_html($value) . "</td>\n";
                echo "<td style='color: red;'>$suspicious</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
        } else {
            echo "<p>❌ Post not found in Pods system</p>\n";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking Pods: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p>⚠️ Pods function not available</p>\n";
}

echo "<h3>4. Formidable Forms Investigation</h3>\n";

// Check Formidable database tables
global $wpdb;

// Look for Formidable entries for this post
$frm_entries = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}frm_items 
    WHERE post_id = %d
", $post_id));

if (!empty($frm_entries)) {
    echo "<p>✅ Found Formidable entries for this post</p>\n";
    
    foreach ($frm_entries as $entry) {
        echo "<p><strong>Formidable Entry ID:</strong> {$entry->id}</p>\n";
        
        // Get field values for this entry
        $field_values = $wpdb->get_results($wpdb->prepare("
            SELECT fmv.field_id, fmv.meta_value, ff.name, ff.field_key
            FROM {$wpdb->prefix}frm_item_metas fmv
            LEFT JOIN {$wpdb->prefix}frm_fields ff ON ff.id = fmv.field_id
            WHERE fmv.item_id = %d
        ", $entry->id));
        
        if (!empty($field_values)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'><th>Field ID</th><th>Field Name</th><th>Field Key</th><th>Value</th><th>Suspicious?</th></tr>\n";
            
            foreach ($field_values as $field) {
                $suspicious = '';
                
                if (!empty($field->meta_value) && (
                    strpos($field->meta_value, 'your audience') !== false || 
                    strpos($field->meta_value, 'achieve their goals') !== false ||
                    strpos($field->meta_value, 'they need help') !== false ||
                    strpos($field->meta_value, 'through your method') !== false)) {
                    $suspicious = '🚨 OLD DEFAULT FOUND!';
                }
                
                echo "<tr>\n";
                echo "<td>{$field->field_id}</td>\n";
                echo "<td>{$field->name}</td>\n";
                echo "<td>{$field->field_key}</td>\n";
                echo "<td>" . esc_html($field->meta_value) . "</td>\n";
                echo "<td style='color: red;'>$suspicious</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
    }
} else {
    echo "<p>⚠️ No Formidable entries found for this post</p>\n";
}

echo "<h3>5. Custom Database Tables Investigation</h3>\n";

// Look for any custom tables that might contain this data
$custom_tables = $wpdb->get_results("SHOW TABLES LIKE '%mkcg%'");
if (!empty($custom_tables)) {
    echo "<p>✅ Found custom MKCG tables:</p>\n";
    foreach ($custom_tables as $table) {
        $table_name = array_values((array)$table)[0];
        echo "<p>- $table_name</p>\n";
        
        // Try to find data in this table
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id = $post_id OR id = $post_id LIMIT 5");
        if (!empty($results)) {
            echo "<p>Found data in $table_name:</p>\n";
            echo "<pre>" . print_r($results, true) . "</pre>\n";
        }
    }
} else {
    echo "<p>⚠️ No custom MKCG tables found</p>\n";
}

echo "<hr>\n";
echo "<h2>🧹 CLEANUP ACTIONS</h2>\n";
echo "<p>Based on the investigation above, here are the actions needed:</p>\n";

echo "<h3>Quick Cleanup SQL Commands:</h3>\n";
echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>\n";
echo "<p><strong>To clean post meta (run in WordPress admin → Tools → Database):</strong></p>\n";

foreach ($field_mappings as $component => $meta_key) {
    $value = get_post_meta($post_id, $meta_key, true);
    if (in_array($value, ['your audience', 'achieve their goals', 'they need help', 'through your method'])) {
        echo "<code>DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = $post_id AND meta_key = '$meta_key';</code><br>\n";
    }
}

echo "</div>\n";

echo "<h3>Alternative: WordPress Admin Cleanup</h3>\n";
echo "<p>You can also clean these values by:</p>\n";
echo "<ol>\n";
echo "<li>Go to Posts → All Posts → Edit post $post_id</li>\n";
echo "<li>Scroll down to 'Custom Fields' section (may need to enable in Screen Options)</li>\n";
echo "<li>Delete any fields containing the old default values</li>\n";
echo "</ol>\n";

echo "<h3>Automatic Cleanup Script</h3>\n";
echo "<form method='post'>\n";
echo "<p><strong>⚠️ DANGER ZONE:</strong> This will automatically delete all old default values</p>\n";
echo "<input type='hidden' name='cleanup_post_id' value='$post_id'>\n";
echo "<input type='submit' name='cleanup_defaults' value='🗑️ Clean Old Defaults for Post $post_id' style='background: red; color: white; padding: 10px;' onclick='return confirm(\"Are you sure you want to delete old default values?\")'>\n";
echo "</form>\n";

// Handle cleanup if requested
if (isset($_POST['cleanup_defaults']) && $_POST['cleanup_post_id'] == $post_id) {
    echo "<h3>🧹 Cleanup Results:</h3>\n";
    $cleaned = 0;
    
    foreach ($field_mappings as $component => $meta_key) {
        $value = get_post_meta($post_id, $meta_key, true);
        if (in_array($value, ['your audience', 'achieve their goals', 'they need help', 'through your method'])) {
            delete_post_meta($post_id, $meta_key);
            echo "<p>✅ Deleted old default for $component ($meta_key): '$value'</p>\n";
            $cleaned++;
        }
    }
    
    if ($cleaned > 0) {
        echo "<p style='color: green;'><strong>🎉 Cleaned $cleaned old default values!</strong></p>\n";
        echo "<p>Refresh the page to see the updated investigation results.</p>\n";
    } else {
        echo "<p>ℹ️ No old default values found to clean.</p>\n";
    }
}

?>
