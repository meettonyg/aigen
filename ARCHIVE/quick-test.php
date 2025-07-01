<?php
/**
 * Quick Authority Hook Test - Plugin Directory Version
 * Access via: your-site.com/wp-content/plugins/media-kit-content-generator/quick-test.php
 */

// Load WordPress from plugin directory
$wp_load_paths = [
    '../../../../wp-config.php',
    '../../../wp-config.php', 
    '../../wp-config.php',
    '../wp-config.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('❌ Cannot load WordPress. Please check file location.');
}

echo "<h1>🎯 Quick Authority Hook Test</h1>\n";
echo "<h2>Plugin Directory Integration Test</h2>\n";

// Get parameters
$entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
$test_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;'>\n";
echo "<h3>📝 Test Parameters</h3>\n";
echo "<p><strong>Entry Key:</strong> " . ($entry_key ?: 'Not provided') . "</p>\n";
echo "<p><strong>Post ID:</strong> " . ($test_post_id ?: 'Not provided') . "</p>\n";

if (!$entry_key && !$test_post_id) {
    echo "<p><strong>⚠️ Usage:</strong> Add <code>?entry=y8ver</code> to URL</p>\n";
}
echo "</div>\n";

// Basic WordPress check
echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>\n";
echo "<h3>✅ WordPress Loaded Successfully</h3>\n";
echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>\n";
echo "<p><strong>Current User ID:</strong> " . get_current_user_id() . "</p>\n";
echo "</div>\n";

// Test entry key resolution
if ($entry_key) {
    echo "<div style='background: #d1ecf1; padding: 15px; margin: 10px 0; border: 1px solid #bee5eb;'>\n";
    echo "<h3>🔍 Testing Entry Resolution</h3>\n";
    
    // Try to find entry in database
    global $wpdb;
    $entry = $wpdb->get_row($wpdb->prepare(
        "SELECT id, item_key, form_id, post_id FROM {$wpdb->prefix}frm_items WHERE item_key = %s",
        $entry_key
    ));
    
    if ($entry) {
        echo "<p><strong>✅ Entry Found!</strong></p>\n";
        echo "<p><strong>Entry ID:</strong> {$entry->id}</p>\n";
        echo "<p><strong>Form ID:</strong> {$entry->form_id}</p>\n";
        echo "<p><strong>Post ID:</strong> {$entry->post_id}</p>\n";
        
        $test_post_id = $entry->post_id;
        
        // Test custom post meta directly
        if ($test_post_id) {
            echo "<h4>📊 Custom Post Meta Check</h4>\n";
            
            $meta_keys = [
                'authority_who' => get_post_meta($test_post_id, 'authority_who', true),
                'authority_result' => get_post_meta($test_post_id, 'authority_result', true),
                'authority_when' => get_post_meta($test_post_id, 'authority_when', true),
                'authority_how' => get_post_meta($test_post_id, 'authority_how', true),
                'authority_complete' => get_post_meta($test_post_id, 'authority_complete', true)
            ];
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f8f9fa;'><th>Meta Key</th><th>Value</th><th>Status</th></tr>\n";
            
            $found_data = false;
            foreach ($meta_keys as $key => $value) {
                $status = empty($value) ? '❌ Empty' : '✅ Has Data';
                $color = empty($value) ? 'red' : 'green';
                
                if (!empty($value)) {
                    $found_data = true;
                }
                
                echo "<tr>\n";
                echo "<td><code>{$key}</code></td>\n";
                echo "<td style='color: {$color};'>" . ($value ? htmlspecialchars($value) : '<em>Empty</em>') . "</td>\n";
                echo "<td style='color: {$color};'>{$status}</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
            
            // Results analysis
            if ($found_data) {
                $who_value = $meta_keys['authority_who'];
                if ($who_value === 'Authors launching a book') {
                    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>\n";
                    echo "<h4>🎉 SUCCESS: Fix Working Perfectly!</h4>\n";
                    echo "<p>The 'authority_who' field contains the expected value: '<strong>Authors launching a book</strong>'</p>\n";
                    echo "<p>The root fix is working - data is being read from WordPress custom post meta!</p>\n";
                    echo "</div>\n";
                } elseif (!empty($who_value)) {
                    echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7;'>\n";
                    echo "<h4>⚠️ Different Data Found</h4>\n";
                    echo "<p>Found: '<strong>{$who_value}</strong>' but expected 'Authors launching a book'</p>\n";
                    echo "<p>The custom post meta is working, but contains different data than expected.</p>\n";
                    echo "</div>\n";
                } else {
                    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
                    echo "<h4>❌ No Authority Hook Data in Post Meta</h4>\n";
                    echo "<p>Custom post meta keys exist but are empty. Check Formidable custom action setup.</p>\n";
                    echo "</div>\n";
                }
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
                echo "<h4>❌ No Custom Post Meta Found</h4>\n";
                echo "<p>No authority hook data found in WordPress custom post meta.</p>\n";
                echo "<p><strong>This means your Formidable custom action is not saving to post meta.</strong></p>\n";
                echo "</div>\n";
            }
        }
        
        // Test Formidable entry fields as comparison
        echo "<h4>🔄 Formidable Entry Fields Check (Fallback)</h4>\n";
        
        $formidable_fields = [
            '10296' => 'WHO field',
            '10297' => 'RESULT field', 
            '10387' => 'WHEN field',
            '10298' => 'HOW field',
            '10358' => 'COMPLETE field'
        ];
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f8f9fa;'><th>Field ID</th><th>Description</th><th>Value</th></tr>\n";
        
        foreach ($formidable_fields as $field_id => $description) {
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                $entry->id, $field_id
            ));
            
            echo "<tr>\n";
            echo "<td><code>{$field_id}</code></td>\n";
            echo "<td>{$description}</td>\n";
            echo "<td>" . ($value ? htmlspecialchars(substr($value, 0, 100)) : '<em>Empty</em>') . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
    } else {
        echo "<p><strong>❌ Entry Not Found</strong></p>\n";
        echo "<p>No Formidable entry found with key: <code>{$entry_key}</code></p>\n";
        
        // Show available entries
        $recent_entries = $wpdb->get_results(
            "SELECT id, item_key, form_id, post_id, created_date 
             FROM {$wpdb->prefix}frm_items 
             WHERE form_id = 515 
             ORDER BY created_date DESC 
             LIMIT 5"
        );
        
        if ($recent_entries) {
            echo "<h4>📋 Recent Form 515 Entries:</h4>\n";
            echo "<ul>\n";
            foreach ($recent_entries as $recent) {
                $test_url = add_query_arg('entry', $recent->item_key, $_SERVER['REQUEST_URI']);
                echo "<li><a href='{$test_url}'>Entry: {$recent->item_key}</a> (ID: {$recent->id}, Post: {$recent->post_id})</li>\n";
            }
            echo "</ul>\n";
        }
    }
    echo "</div>\n";
}

// Check plugin classes
echo "<div style='background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 8px;'>\n";
echo "<h3>🔧 Plugin Status Check</h3>\n";

$plugin_classes = [
    'MKCG_Topics_Data_Service' => 'Topics Data Service',
    'MKCG_Formidable_Service' => 'Formidable Service',
    'MKCG_Topics_Generator' => 'Topics Generator'
];

foreach ($plugin_classes as $class => $name) {
    $exists = class_exists($class);
    $status = $exists ? '✅ Available' : '❌ Missing';
    $color = $exists ? 'green' : 'red';
    echo "<p style='color: {$color};'><strong>{$name}:</strong> {$status}</p>\n";
}

echo "</div>\n";

echo "<div style='background: #d1ecf1; padding: 15px; margin: 20px 0; border: 1px solid #bee5eb;'>\n";
echo "<h3>📋 Next Steps</h3>\n";
echo "<ol>\n";
echo "<li><strong>Use Entry Key:</strong> Add <code>?entry=y8ver</code> to this URL</li>\n";
echo "<li><strong>Check Results:</strong> Look for 'SUCCESS' or 'No Custom Post Meta Found' messages</li>\n";
echo "<li><strong>If No Meta Found:</strong> Your Formidable custom action needs to save field 10296 to 'authority_who' post meta</li>\n";
echo "<li><strong>Test Topics Generator:</strong> Go to your Topics Generator page after fixing</li>\n";
echo "</ol>\n";
echo "</div>\n";
?>