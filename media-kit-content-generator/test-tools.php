<?php
/**
 * Simple Test Launcher
 * Quick access to all test tools from plugin root
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MKCG Test Tools</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 40px;
            background: #f1f1f1;
            color: #23282d;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #0073aa;
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        
        .header p {
            margin: 0;
            color: #666;
            font-size: 1.1em;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .tool-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        .tool-card:hover {
            border-color: #0073aa;
            box-shadow: 0 2px 8px rgba(0,115,170,0.1);
        }
        
        .tool-card h3 {
            margin-top: 0;
            color: #0073aa;
            font-size: 1.3em;
        }
        
        .tool-card p {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .button {
            display: inline-block;
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .button:hover {
            background: #005a87;
        }
        
        .button-secondary {
            background: #666;
        }
        
        .button-secondary:hover {
            background: #555;
        }
        
        .status-info {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .status-info h4 {
            margin-top: 0;
            color: #0056b3;
        }
        
        .quick-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        
        @media (max-width: 600px) {
            .tools-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-links {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 MKCG Test Tools</h1>
            <p>Media Kit Content Generator - Centralized Services Testing</p>
        </div>

        <div class="status-info">
            <h4>🎯 Your Test Entry: 74492 (y8ver)</h4>
            <?php
            // Check your specific entry
            global $wpdb;
            $test_entry = $wpdb->get_row("SELECT id, item_key, post_id FROM {$wpdb->prefix}frm_items WHERE id = 74492");
            
            if ($test_entry) {
                echo "<p style='font-size: 1.1em; color: #0073aa; font-weight: bold;'>";
                echo "✅ Your test entry is ready: ID {$test_entry->id} (Key: {$test_entry->item_key})";
                if ($test_entry->post_id) {
                    echo " - Associated with Post {$test_entry->post_id}";
                }
                echo "</p>";
                echo "<p><a href='test-entry-74492.php' style='background: #667eea; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🚀 Test Entry 74492 Specifically</a></p>";
            } else {
                echo "<p style='color: #d63638; font-weight: bold;'>❌ Test entry 74492 not found in database</p>";
            }
            
            // Quick service availability check
            $config_available = class_exists('MKCG_Config') ? '✅' : '❌';
            $formidable_available = class_exists('Enhanced_Formidable_Service') ? '✅' : '❌';
            
            $entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}frm_items") ?: 0;
            $posts_with_mkcg = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'mkcg_%'") ?: 0;
            
            echo "<p style='margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 0.9em;'>";
            echo "{$config_available} MKCG_Config | ";
            echo "{$formidable_available} Enhanced_Formidable_Service | ";
            echo "📊 {$entries} Formidable Entries | ";
            echo "📝 {$posts_with_mkcg} Posts with MKCG Data";
            echo "</p>";
            ?>
        </div>

        <div class="tools-grid">
            <div class="tool-card" style="border: 2px solid #667eea; background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);">
                <h3 style="color: #667eea;">🎯 Entry 74492 Test</h3>
                <p><strong>Your specific test entry!</strong> Dedicated testing interface for entry 74492 (y8ver) with setup/clear functions and detailed analysis.</p>
                <a href="test-entry-74492.php" class="button" style="background: #667eea;">Test Entry 74492</a>
            </div>

            <div class="tool-card">
                <h3>📋 Main Test Interface</h3>
                <p>Comprehensive form-based testing for all centralized services. Load, view, and save data from both Formidable fields and custom post meta.</p>
                <a href="test-centralized-services.php" class="button">Open Main Test</a>
            </div>

            <div class="tool-card">
                <h3>🔍 Data Flow Debug</h3>
                <p>Real-time debugging and tracing tool. Track data flow through services and identify issues step-by-step.</p>
                <a href="debug-data-flow.php" class="button">Open Debug Tool</a>
            </div>

            <div class="tool-card">
                <h3>🎯 Topics Generator</h3>
                <p>Test the Topics Generator shortcode and AJAX functionality. Verify form rendering and data saving.</p>
                <a href="functional-test.html" class="button button-secondary">Open Functional Test</a>
            </div>
        </div>

        <div class="status-info">
            <h4>📚 Quick Reference</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <strong>Formidable Fields:</strong><br>
                    • WHEN: 10387<br>
                    • WHAT: 10297<br>
                    • HOW: 10298<br>
                    • WHERE: 10359<br>
                    • WHY: 10360
                </div>
                <div>
                    <strong>Custom Post Meta:</strong><br>
                    • WHO: mkcg_who<br>
                    • Topics: mkcg_topic_1-5<br>
                    • Questions: mkcg_question_{topic}_{num}
                </div>
            </div>
        </div>

        <div class="quick-links">
            <a href="test-centralized-services.php" class="button">🧪 Start Testing</a>
            <a href="debug-data-flow.php" class="button">🔍 Debug Data</a>
            <a href="../../../wp-admin/" class="button button-secondary">🔙 WP Admin</a>
        </div>
    </div>
</body>
</html>
