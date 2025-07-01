<?php
/**
 * WordPress Integration Test - Authority Hook Data Source Fix
 * Place this file in your WordPress root and access via browser
 */

// Load WordPress
require_once('wp-config.php');

echo "<h1>🎯 WordPress Integration Test - Authority Hook Fix</h1>\n";
echo "<h2>Testing Real WordPress Environment</h2>\n";

// Get your actual entry data
$entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
$test_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$test_entry_id = isset($_GET['entry_id']) ? intval($_GET['entry_id']) : 0;

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;'>\n";
echo "<h3>📝 Test Parameters</h3>\n";
echo "<p><strong>Entry Key:</strong> " . ($entry_key ?: 'Not provided') . "</p>\n";
echo "<p><strong>Post ID:</strong> " . ($test_post_id ?: 'Not provided') . "</p>\n";
echo "<p><strong>Entry ID:</strong> " . ($test_entry_id ?: 'Not provided') . "</p>\n";

if (!$entry_key && !$test_post_id) {
    echo "<p><strong>⚠️ Usage:</strong></p>\n";
    echo "<p>Add parameters to URL:</p>\n";
    echo "<p><code>?entry=your_entry_key</code></p>\n";
    echo "<p><code>?post_id=123&entry_id=456</code></p>\n";
}
echo "</div>\n";

// Test the fixed Topics Data Service
if (class_exists('MKCG_Topics_Data_Service')) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>\n";
    echo "<h3>✅ Topics Data Service Found</h3>\n";
    
    try {
        // Create service instance
        $formidable_service = null;
        if (class_exists('MKCG_Formidable_Service')) {
            $formidable_service = new MKCG_Formidable_Service();
        }
        
        $topics_service = new MKCG_Topics_Data_Service($formidable_service);
        
        // Test with provided parameters
        if ($entry_key || ($test_post_id && $test_entry_id)) {
            echo "<h4>🔄 Testing Authority Hook Data Retrieval</h4>\n";
            
            $result = $topics_service->get_topics_data($test_entry_id, $entry_key, $test_post_id);
            
            if ($result['success']) {
                echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0; border: 1px solid #bee5eb;'>\n";
                echo "<h5>✅ Data Retrieved Successfully</h5>\n";
                echo "<p><strong>Entry ID:</strong> {$result['entry_id']}</p>\n";
                echo "<p><strong>Post ID:</strong> {$result['post_id']}</p>\n";
                echo "<p><strong>Data Source:</strong> {$result['source']}</p>\n";
                
                if (isset($result['authority_hook'])) {
                    $auth_hook = $result['authority_hook'];
                    echo "<h6>Authority Hook Components:</h6>\n";
                    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>\n";
                    echo "<tr style='background: #f8f9fa;'><th>Component</th><th>Value</th></tr>\n";
                    echo "<tr><td><strong>WHO</strong></td><td style='color: " . ($auth_hook['who'] === 'Authors launching a book' ? 'green' : ($auth_hook['who'] === 'your audience' ? 'orange' : 'red')) . ";'><code>{$auth_hook['who']}</code></td></tr>\n";
                    echo "<tr><td><strong>RESULT</strong></td><td><code>{$auth_hook['result']}</code></td></tr>\n";
                    echo "<tr><td><strong>WHEN</strong></td><td><code>{$auth_hook['when']}</code></td></tr>\n";
                    echo "<tr><td><strong>HOW</strong></td><td><code>{$auth_hook['how']}</code></td></tr>\n";
                    echo "</table>\n";
                    
                    echo "<h6>Complete Authority Hook:</h6>\n";
                    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 3px; font-style: italic;'>\n";
                    echo htmlspecialchars($auth_hook['complete']) . "\n";
                    echo "</div>\n";
                    
                    // Test result analysis
                    if ($auth_hook['who'] === 'Authors launching a book') {
                        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>\n";
                        echo "<h5>🎉 SUCCESS: Root Fix Working!</h5>\n";
                        echo "<p>WHO field correctly shows 'Authors launching a book' from WordPress custom post meta.</p>\n";
                        echo "</div>\n";
                    } elseif ($auth_hook['who'] === 'your audience') {
                        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7;'>\n";
                        echo "<h5>⚠️ Using Fallback Data</h5>\n";
                        echo "<p>Showing default 'your audience' - check custom post meta setup.</p>\n";
                        echo "</div>\n";
                    }
                }
                echo "</div>\n";
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
                echo "<h5>❌ Data Retrieval Failed</h5>\n";
                echo "<p><strong>Error:</strong> {$result['message']}</p>\n";
                echo "</div>\n";
            }
        }
        
        // Direct post meta test
        if ($test_post_id) {
            echo "<h4>🔍 Direct Post Meta Check</h4>\n";
            
            $meta_keys = ['authority_who', 'authority_result', 'authority_when', 'authority_how', 'authority_complete'];
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f8f9fa;'><th>Meta Key</th><th>Value</th><th>Status</th></tr>\n";
            
            foreach ($meta_keys as $meta_key) {
                $value = get_post_meta($test_post_id, $meta_key, true);
                $status = empty($value) ? '❌ Empty' : '✅ Has Data';
                $color = empty($value) ? 'red' : 'green';
                
                echo "<tr>\n";
                echo "<td><code>{$meta_key}</code></td>\n";
                echo "<td style='color: {$color};'>" . ($value ? htmlspecialchars($value) : '<em>Empty</em>') . "</td>\n";
                echo "<td style='color: {$color};'>{$status}</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
            
            $who_value = get_post_meta($test_post_id, 'authority_who', true);
            if ($who_value === 'Authors launching a book') {
                echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>\n";
                echo "<h5>🎉 Perfect! Custom Post Meta is Correct</h5>\n";
                echo "<p>The 'authority_who' meta key contains the expected value.</p>\n";
                echo "</div>\n";
            } elseif (empty($who_value)) {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
                echo "<h5>❌ Missing Custom Post Meta</h5>\n";
                echo "<p>No 'authority_who' meta found. Check your Formidable custom action setup.</p>\n";
                echo "</div>\n";
            } else {
                echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7;'>\n";
                echo "<h5>⚠️ Unexpected Meta Value</h5>\n";
                echo "<p>Found: '<strong>{$who_value}</strong>' but expected 'Authors launching a book'</p>\n";
                echo "</div>\n";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
        echo "<h5>❌ Exception Occurred</h5>\n";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "</div>\n";
    }
    
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>\n";
    echo "<h3>❌ Topics Data Service Not Found</h3>\n";
    echo "<p>The MKCG_Topics_Data_Service class is not available. Check plugin activation.</p>\n";
    echo "</div>\n";
}

// WordPress environment info
echo "<div style='background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 8px;'>\n";
echo "<h3>🔧 WordPress Environment</h3>\n";
echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>\n";
echo "<p><strong>Active Theme:</strong> " . get_option('stylesheet') . "</p>\n";
echo "<p><strong>Plugin Directory:</strong> " . WP_PLUGIN_DIR . "</p>\n";

// Check if Formidable is active
if (class_exists('FrmEntry') || class_exists('FrmForm')) {
    echo "<p><strong>Formidable Forms:</strong> ✅ Active</p>\n";
} else {
    echo "<p><strong>Formidable Forms:</strong> ❌ Not Active</p>\n";
}

echo "</div>\n";

// Instructions
echo "<div style='background: #d1ecf1; padding: 15px; margin: 20px 0; border: 1px solid #bee5eb;'>\n";
echo "<h3>📋 Next Steps</h3>\n";
echo "<ol>\n";
echo "<li><strong>Get Your Entry Data:</strong></li>\n";
echo "<ul>\n";
echo "<li>Find your Formidable entry key (e.g., 'abc123')</li>\n";
echo "<li>Or find your WordPress post ID associated with the entry</li>\n";
echo "</ul>\n";
echo "<li><strong>Test with Real Data:</strong></li>\n";
echo "<ul>\n";
echo "<li>Add <code>?entry=your_entry_key</code> to this URL</li>\n";
echo "<li>Or add <code>?post_id=123&entry_id=456</code></li>\n";
echo "</ul>\n";
echo "<li><strong>Verify Formidable Custom Action:</strong></li>\n";
echo "<ul>\n";
echo "<li>Check that field 10296 saves to 'authority_who' post meta</li>\n";
echo "<li>Verify all authority hook fields save to post meta</li>\n";
echo "</ul>\n";
echo "</ol>\n";
echo "</div>\n";
?>