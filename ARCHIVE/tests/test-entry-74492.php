<?php
/**
 * Specific Test for Entry 74492 (y8ver)
 * Quick verification and setup for your test entry
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Load plugin dependencies
require_once plugin_dir_path(__FILE__) . 'includes/services/class-mkcg-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/services/enhanced_formidable_service.php';

// Initialize services
$formidable_service = new Enhanced_Formidable_Service();

// Your specific entry details
$test_entry_id = 74492;
$test_entry_key = 'y8ver';

// Check if entry exists
global $wpdb;
$entry_exists = $wpdb->get_row($wpdb->prepare(
    "SELECT id, item_key, post_id, created_date FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $test_entry_id
));

// Handle quick actions
$message = '';
$message_type = '';

if ($_POST) {
    $action = sanitize_text_field($_POST['action'] ?? '');
    
    switch ($action) {
        case 'setup_test_data':
            $result = setup_test_data_for_entry($test_entry_id, $formidable_service);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'clear_test_data':
            $result = clear_test_data_for_entry($test_entry_id, $formidable_service);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
    }
}

function setup_test_data_for_entry($entry_id, $formidable_service) {
    // Sample Formidable data
    $formidable_data = [
        '10387' => 'when they\'re struggling to create compelling content that converts and builds their authority',
        '10297' => 'develop a strong personal brand and create content that attracts their ideal clients',
        '10298' => 'through my proven storytelling framework and content strategy system that turns expertise into influence',
        '10359' => 'featured in Forbes, Entrepreneur.com, and spoken at 50+ industry events with measurable client results',
        '10360' => 'I believe every entrepreneur has a unique story that can transform their business when told correctly and authentically'
    ];
    
    // Save Formidable data
    $formidable_result = $formidable_service->save_entry_data($entry_id, $formidable_data);
    
    if (!$formidable_result['success']) {
        return ['success' => false, 'message' => 'Failed to save Formidable data: ' . $formidable_result['message']];
    }
    
    // Get associated post ID
    $post_id = $formidable_service->get_post_id_from_entry($entry_id);
    
    if ($post_id) {
        // Sample custom post data
        $custom_post_data = [
            'mkcg_who' => 'entrepreneurs and small business owners who want to build authority and attract ideal clients',
            'mkcg_topic_1' => 'Building Your Authority Through Storytelling',
            'mkcg_topic_2' => 'Content Strategy That Converts Prospects to Clients',
            'mkcg_topic_3' => 'Personal Branding for Business Growth',
            'mkcg_topic_4' => 'Social Media Strategies That Build Trust',
            'mkcg_topic_5' => 'Creating Content Systems That Scale',
            // Sample questions for topic 1
            'mkcg_question_1_1' => 'What\'s the biggest mistake you see entrepreneurs make when trying to build authority?',
            'mkcg_question_1_2' => 'How do you identify the stories that will resonate most with your ideal clients?',
            'mkcg_question_1_3' => 'What\'s the framework you use to structure compelling authority-building content?',
            'mkcg_question_1_4' => 'How do you measure the effectiveness of your storytelling efforts?',
            'mkcg_question_1_5' => 'What role does vulnerability play in building authentic authority?'
        ];
        
        // Save custom post data
        $saved_count = 0;
        foreach ($custom_post_data as $meta_key => $meta_value) {
            $result = update_post_meta($post_id, $meta_key, $meta_value);
            if ($result !== false) {
                $saved_count++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Test data setup complete! Saved {$formidable_result['saved_count']} Formidable fields and {$saved_count} custom post meta fields."
        ];
    } else {
        return [
            'success' => true,
            'message' => "Formidable data saved ({$formidable_result['saved_count']} fields), but no associated post found for custom meta."
        ];
    }
}

function clear_test_data_for_entry($entry_id, $formidable_service) {
    // Clear Formidable data
    global $wpdb;
    $field_ids = ['10387', '10297', '10298', '10359', '10360'];
    
    $cleared_formidable = 0;
    foreach ($field_ids as $field_id) {
        $result = $wpdb->delete(
            $wpdb->prefix . 'frm_item_metas',
            ['item_id' => $entry_id, 'field_id' => $field_id],
            ['%d', '%d']
        );
        if ($result !== false) {
            $cleared_formidable++;
        }
    }
    
    // Clear custom post data
    $post_id = $formidable_service->get_post_id_from_entry($entry_id);
    $cleared_post_meta = 0;
    
    if ($post_id) {
        $meta_keys = [
            'mkcg_who', 'mkcg_topic_1', 'mkcg_topic_2', 'mkcg_topic_3', 'mkcg_topic_4', 'mkcg_topic_5',
            'mkcg_question_1_1', 'mkcg_question_1_2', 'mkcg_question_1_3', 'mkcg_question_1_4', 'mkcg_question_1_5'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $result = delete_post_meta($post_id, $meta_key);
            if ($result) {
                $cleared_post_meta++;
            }
        }
    }
    
    return [
        'success' => true,
        'message' => "Test data cleared! Removed {$cleared_formidable} Formidable fields and {$cleared_post_meta} custom post meta fields."
    ];
}

function analyze_entry_74492($formidable_service) {
    global $wpdb, $test_entry_id;
    
    echo "<h3>🔍 Analysis for Entry 74492 (y8ver)</h3>";
    
    // Check entry exists
    $entry = $wpdb->get_row($wpdb->prepare(
        "SELECT id, item_key, post_id, created_date FROM {$wpdb->prefix}frm_items WHERE id = %d",
        $test_entry_id
    ));
    
    if (!$entry) {
        echo "<p style='color: red;'>❌ Entry 74492 not found in database!</p>";
        return;
    }
    
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
    echo "<strong>Entry Details:</strong><br>";
    echo "ID: {$entry->id}<br>";
    echo "Key: {$entry->item_key}<br>";
    echo "Post ID: " . ($entry->post_id ?: 'None') . "<br>";
    echo "Created: {$entry->created_date}<br>";
    echo "</div>";
    
    // Check Formidable fields
    echo "<h4>Formidable Fields Status:</h4>";
    $field_ids = [10387 => 'WHEN', 10297 => 'WHAT', 10298 => 'HOW', 10359 => 'WHERE', 10360 => 'WHY'];
    
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>ID</th><th>Status</th><th>Preview</th></tr>";
    
    foreach ($field_ids as $field_id => $field_name) {
        $value = $formidable_service->get_field_value($test_entry_id, $field_id);
        $status = !empty($value) ? '✅ Has Data' : '⚠️ No Data';
        $preview = !empty($value) ? substr($value, 0, 50) . '...' : '-';
        
        echo "<tr style='border-bottom: 1px solid #ddd;'>";
        echo "<td style='padding: 8px; font-weight: bold;'>{$field_name}</td>";
        echo "<td style='padding: 8px;'>{$field_id}</td>";
        echo "<td style='padding: 8px;'>{$status}</td>";
        echo "<td style='padding: 8px; font-style: italic;'>{$preview}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check custom post meta
    if ($entry->post_id) {
        echo "<h4>Custom Post Meta Status:</h4>";
        
        $meta_fields = [
            'mkcg_who' => 'WHO',
            'mkcg_topic_1' => 'Topic 1',
            'mkcg_topic_2' => 'Topic 2', 
            'mkcg_topic_3' => 'Topic 3',
            'mkcg_topic_4' => 'Topic 4',
            'mkcg_topic_5' => 'Topic 5'
        ];
        
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Meta Key</th><th>Status</th><th>Value</th></tr>";
        
        foreach ($meta_fields as $meta_key => $field_name) {
            $value = get_post_meta($entry->post_id, $meta_key, true);
            $status = !empty($value) ? '✅ Has Data' : '⚠️ No Data';
            $preview = !empty($value) ? substr($value, 0, 40) . '...' : '-';
            
            echo "<tr style='border-bottom: 1px solid #ddd;'>";
            echo "<td style='padding: 8px; font-weight: bold;'>{$field_name}</td>";
            echo "<td style='padding: 8px; font-family: monospace;'>{$meta_key}</td>";
            echo "<td style='padding: 8px;'>{$status}</td>";
            echo "<td style='padding: 8px; font-style: italic;'>{$preview}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check questions
        $questions_count = 0;
        for ($topic = 1; $topic <= 5; $topic++) {
            for ($q = 1; $q <= 5; $q++) {
                $question = get_post_meta($entry->post_id, "mkcg_question_{$topic}_{$q}", true);
                if (!empty($question)) {
                    $questions_count++;
                }
            }
        }
        echo "<p><strong>Total Questions Found:</strong> {$questions_count}</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No associated post found - custom post meta testing not available</p>";
    }
    
    // Test centralized config
    echo "<h4>Centralized Configuration Test:</h4>";
    try {
        $config_data = MKCG_Config::load_data_for_entry($test_entry_id, $formidable_service);
        
        $topics_with_data = count(array_filter($config_data['form_field_values']));
        $auth_components = count(array_filter($config_data['authority_hook_components']));
        
        echo "<p>✅ Centralized config working</p>";
        echo "<p><strong>Topics loaded:</strong> {$topics_with_data}/5</p>";
        echo "<p><strong>Authority components:</strong> {$auth_components}</p>";
        echo "<p><strong>Has entry data:</strong> " . ($config_data['has_entry'] ? 'YES' : 'NO') . "</p>";
        
        if (!empty($config_data['authority_hook_components']['complete'])) {
            echo "<p><strong>Authority Hook:</strong> " . substr($config_data['authority_hook_components']['complete'], 0, 100) . "...</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Centralized config failed: " . $e->getMessage() . "</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Entry 74492 (y8ver) - MKCG</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f1f1f1;
            color: #23282d;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .status-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-weight: 600;
        }
        
        .button:hover {
            background: #005a87;
        }
        
        .button-danger {
            background: #d63638;
        }
        
        .button-danger:hover {
            background: #b32d2e;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .action-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            background: #fafafa;
        }
        
        .action-card h3 {
            margin-top: 0;
            color: #0073aa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Test Entry 74492</h1>
            <p>Dedicated testing for entry ID 74492 (Key: y8ver)</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <div class="status-box">
            <h3>📊 Entry Status</h3>
            <?php if ($entry_exists): ?>
                <p><strong>✅ Entry Found:</strong> ID <?php echo $entry_exists->id; ?> (Key: <?php echo $entry_exists->item_key; ?>)</p>
                <p><strong>Created:</strong> <?php echo $entry_exists->created_date; ?></p>
                <p><strong>Associated Post:</strong> <?php echo $entry_exists->post_id ? "Post ID {$entry_exists->post_id}" : 'None'; ?></p>
            <?php else: ?>
                <p style="color: red;"><strong>❌ Entry Not Found:</strong> Entry 74492 does not exist in the database</p>
            <?php endif; ?>
        </div>

        <?php if ($entry_exists): ?>
            <div class="quick-actions">
                <div class="action-card">
                    <h3>🚀 Setup Test Data</h3>
                    <p>Populate entry 74492 with sample data for comprehensive testing.</p>
                    <form method="post" style="margin: 0;">
                        <input type="hidden" name="action" value="setup_test_data">
                        <button type="submit" class="button">Setup Test Data</button>
                    </form>
                </div>

                <div class="action-card">
                    <h3>🧹 Clear Test Data</h3>
                    <p>Remove all test data from entry 74492 to start fresh.</p>
                    <form method="post" style="margin: 0;">
                        <input type="hidden" name="action" value="clear_test_data">
                        <button type="submit" class="button button-danger">Clear Test Data</button>
                    </form>
                </div>
            </div>

            <div class="status-box">
                <?php analyze_entry_74492($formidable_service); ?>
            </div>

            <div class="status-box">
                <h3>🔗 Quick Links</h3>
                <a href="test-centralized-services.php" class="button">📋 Main Test Interface</a>
                <a href="debug-data-flow.php?debug_entry=74492" class="button">🔍 Debug This Entry</a>
                <a href="test-tools.php" class="button">🏠 Test Tools Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
