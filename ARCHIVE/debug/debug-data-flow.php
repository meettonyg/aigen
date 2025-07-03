<?php
/**
 * Data Flow Debug Script
 * Real-time debugging and monitoring for centralized services
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

/**
 * Debug function to trace data flow
 */
function debug_data_flow($entry_id) {
    echo "<h3>🔍 Tracing Data Flow for Entry ID: {$entry_id}</h3>";
    
    $formidable_service = new Enhanced_Formidable_Service();
    
    echo "<div class='debug-step'>";
    echo "<h4>Step 1: Check Entry Exists</h4>";
    
    global $wpdb;
    $entry = $wpdb->get_row($wpdb->prepare(
        "SELECT id, item_key, post_id, created_date FROM {$wpdb->prefix}frm_items WHERE id = %d",
        $entry_id
    ));
    
    if ($entry) {
        echo "✅ Entry found: ID {$entry->id}, Key: {$entry->item_key}, Post ID: {$entry->post_id}<br>";
        echo "Created: {$entry->created_date}<br>";
    } else {
        echo "❌ Entry not found<br>";
        return;
    }
    echo "</div>";
    
    echo "<div class='debug-step'>";
    echo "<h4>Step 2: Check Formidable Field Data</h4>";
    
    $field_ids = [10387, 10297, 10298, 10359, 10360];
    $field_names = ['WHEN', 'WHAT', 'HOW', 'WHERE', 'WHY'];
    
    $formidable_data = [];
    for ($i = 0; $i < count($field_ids); $i++) {
        $field_id = $field_ids[$i];
        $field_name = $field_names[$i];
        
        $value = $formidable_service->get_field_value($entry_id, $field_id);
        $formidable_data[$field_id] = $value;
        
        if (!empty($value)) {
            echo "✅ {$field_name} (Field {$field_id}): " . substr($value, 0, 50) . "...<br>";
        } else {
            echo "⚠️ {$field_name} (Field {$field_id}): No data<br>";
        }
    }
    echo "</div>";
    
    echo "<div class='debug-step'>";
    echo "<h4>Step 3: Check Custom Post Meta Data</h4>";
    
    if ($entry->post_id) {
        echo "Checking post ID: {$entry->post_id}<br>";
        
        // Check WHO field
        $who = get_post_meta($entry->post_id, 'mkcg_who', true);
        echo ($who ? "✅" : "⚠️") . " WHO: " . ($who ?: 'No data') . "<br>";
        
        // Check Topics
        $topics_found = 0;
        for ($i = 1; $i <= 5; $i++) {
            $topic = get_post_meta($entry->post_id, "mkcg_topic_{$i}", true);
            if ($topic) {
                echo "✅ Topic {$i}: {$topic}<br>";
                $topics_found++;
            } else {
                echo "⚠️ Topic {$i}: No data<br>";
            }
        }
        
        // Check Questions (sample)
        $questions_found = 0;
        for ($topic = 1; $topic <= 5; $topic++) {
            for ($q = 1; $q <= 5; $q++) {
                $question = get_post_meta($entry->post_id, "mkcg_question_{$topic}_{$q}", true);
                if ($question) {
                    $questions_found++;
                }
            }
        }
        echo "📝 Total Questions Found: {$questions_found}<br>";
        
    } else {
        echo "❌ No associated post found<br>";
    }
    echo "</div>";
    
    echo "<div class='debug-step'>";
    echo "<h4>Step 4: Test Centralized Configuration</h4>";
    
    try {
        $config_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
        
        echo "✅ Centralized config loaded successfully<br>";
        echo "Has entry: " . ($config_data['has_entry'] ? 'YES' : 'NO') . "<br>";
        echo "Topics loaded: " . count(array_filter($config_data['form_field_values'])) . "/5<br>";
        echo "Auth components: " . count(array_filter($config_data['authority_hook_components'])) . "<br>";
        echo "Question groups: " . count($config_data['questions']) . "<br>";
        
        if (!empty($config_data['authority_hook_components']['complete'])) {
            echo "<strong>Authority Hook:</strong> " . substr($config_data['authority_hook_components']['complete'], 0, 100) . "...<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Centralized config failed: " . $e->getMessage() . "<br>";
    }
    echo "</div>";
    
    echo "<div class='debug-step'>";
    echo "<h4>Step 5: Test Service Methods</h4>";
    
    // Test get_entry_data
    try {
        $entry_data = $formidable_service->get_entry_data($entry_id);
        echo "✅ get_entry_data(): " . ($entry_data['success'] ? 'Success' : 'Failed') . "<br>";
        if ($entry_data['success']) {
            echo "Fields returned: " . count($entry_data['field_data']) . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ get_entry_data() failed: " . $e->getMessage() . "<br>";
    }
    
    // Test get_post_id_from_entry
    try {
        $post_id = $formidable_service->get_post_id_from_entry($entry_id);
        echo "✅ get_post_id_from_entry(): " . ($post_id ? "Post ID {$post_id}" : 'No post') . "<br>";
    } catch (Exception $e) {
        echo "❌ get_post_id_from_entry() failed: " . $e->getMessage() . "<br>";
    }
    
    echo "</div>";
    
    return $config_data ?? null;
}

/**
 * Quick database analysis
 */
function analyze_database() {
    global $wpdb;
    
    echo "<h3>📊 Database Analysis</h3>";
    
    // Formidable entries
    $total_entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}frm_items");
    echo "<strong>Total Formidable Entries:</strong> {$total_entries}<br>";
    
    // Entries with post associations
    $entries_with_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}frm_items WHERE post_id IS NOT NULL AND post_id > 0");
    echo "<strong>Entries with Posts:</strong> {$entries_with_posts}<br>";
    
    // Posts with MKCG meta
    $posts_with_mkcg = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'mkcg_%'");
    echo "<strong>Posts with MKCG Meta:</strong> {$posts_with_mkcg}<br>";
    
    // MKCG meta breakdown
    $meta_counts = $wpdb->get_results("
        SELECT meta_key, COUNT(*) as count 
        FROM {$wpdb->prefix}postmeta 
        WHERE meta_key LIKE 'mkcg_%' 
        GROUP BY meta_key 
        ORDER BY count DESC
    ");
    
    echo "<strong>MKCG Meta Breakdown:</strong><br>";
    foreach ($meta_counts as $meta) {
        echo "- {$meta->meta_key}: {$meta->count}<br>";
    }
    
    // Formidable field data
    $field_data = $wpdb->get_results("
        SELECT field_id, COUNT(*) as count 
        FROM {$wpdb->prefix}frm_item_metas 
        WHERE field_id IN (10387, 10297, 10298, 10359, 10360)
        GROUP BY field_id 
        ORDER BY field_id
    ");
    
    echo "<strong>Formidable Field Data:</strong><br>";
    $field_names = [
        10387 => 'WHEN',
        10297 => 'WHAT', 
        10298 => 'HOW',
        10359 => 'WHERE',
        10360 => 'WHY'
    ];
    
    foreach ($field_data as $field) {
        $name = $field_names[$field->field_id] ?? 'Unknown';
        echo "- Field {$field->field_id} ({$name}): {$field->count} entries<br>";
    }
}

// Handle AJAX debugging
if (isset($_GET['debug_entry'])) {
    $entry_id = intval($_GET['debug_entry']);
    header('Content-Type: text/html');
    echo "<style>
        .debug-step { 
            margin: 15px 0; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            background: #f9f9f9; 
        }
        .debug-step h4 { 
            margin-top: 0; 
            color: #0073aa; 
        }
    </style>";
    
    debug_data_flow($entry_id);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Flow Debug - MKCG</title>
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
        
        .debug-step {
            margin: 15px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .debug-step h4 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .button {
            background: #0073aa;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .button:hover {
            background: #005a87;
        }
        
        .form-group {
            margin: 15px 0;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .form-group select, .form-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }
        
        #debug-output {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            background: #fafafa;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .analysis-box {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Data Flow Debug Tool</h1>
        <p>Debug and monitor data flow through the centralized services</p>

        <div class="analysis-box">
            <?php analyze_database(); ?>
        </div>

        <div class="debug-step">
            <h4>🧪 Live Entry Debugging</h4>
            <div class="form-group">
                <label for="entry_select">Select Entry to Debug:</label>
                <select id="entry_select">
                    <option value="">Choose an entry...</option>
                    <!-- Your specific test entry -->
                    <option value="74492" style="background: #e8f4fd; font-weight: bold;">
                        🎯 TEST ENTRY: ID 74492 (Key: y8ver) - YOUR TEST DATA
                    </option>
                    <option value="" disabled>-- Other Entries --</option>
                    <?php
                    global $wpdb;
                    $entries = $wpdb->get_results("
                        SELECT i.id, i.item_key, i.post_id, 
                               COUNT(m.field_id) as field_count
                        FROM {$wpdb->prefix}frm_items i
                        LEFT JOIN {$wpdb->prefix}frm_item_metas m ON i.id = m.item_id
                        GROUP BY i.id
                        ORDER BY i.id DESC
                        LIMIT 20
                    ");
                    
                    foreach ($entries as $entry) {
                        echo "<option value='{$entry->id}'>";
                        echo "ID: {$entry->id} (Key: {$entry->item_key})";
                        if ($entry->post_id) {
                            echo " - Post: {$entry->post_id}";
                        }
                        echo " - {$entry->field_count} fields";
                        echo "</option>";
                    }
                    ?>
                </select>
                <button type="button" class="button" onclick="debugEntry()">Debug Entry</button>
            </div>
            
            <div class="form-group">
                <label for="manual_entry">Or enter Entry ID manually:</label>
                <input type="number" id="manual_entry" placeholder="Enter ID">
                <button type="button" class="button" onclick="debugManualEntry()">Debug</button>
            </div>
        </div>

        <div class="debug-step">
            <h4>🔧 Quick Actions</h4>
            <button type="button" class="button" onclick="refreshAnalysis()">Refresh Analysis</button>
            <button type="button" class="button" onclick="clearDebugOutput()">Clear Output</button>
            <a href="test-centralized-services.php" class="button">Open Main Test</a>
        </div>

        <div id="debug-output" style="display: none;">
            <h4>Debug Output:</h4>
            <div id="debug-content"></div>
        </div>

        <div class="debug-step">
            <h4>📝 Field Reference</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <strong>Formidable Fields:</strong><br>
                    10387 - WHEN<br>
                    10297 - WHAT<br>
                    10298 - HOW<br>
                    10359 - WHERE<br>
                    10360 - WHY
                </div>
                <div>
                    <strong>Custom Post Meta:</strong><br>
                    mkcg_who - WHO<br>
                    mkcg_topic_1-5 - Topics<br>
                    mkcg_question_{topic}_{num} - Questions
                </div>
            </div>
        </div>
    </div>

    <script>
        function debugEntry() {
            const select = document.getElementById('entry_select');
            const entryId = select.value;
            
            if (!entryId) {
                alert('Please select an entry');
                return;
            }
            
            loadDebugData(entryId);
        }
        
        function debugManualEntry() {
            const input = document.getElementById('manual_entry');
            const entryId = input.value;
            
            if (!entryId) {
                alert('Please enter an entry ID');
                return;
            }
            
            loadDebugData(entryId);
        }
        
        function loadDebugData(entryId) {
            const output = document.getElementById('debug-output');
            const content = document.getElementById('debug-content');
            
            output.style.display = 'block';
            content.innerHTML = '<p>Loading debug data for entry ' + entryId + '...</p>';
            
            fetch('?debug_entry=' + entryId)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(error => {
                    content.innerHTML = '<p style="color: red;">Error loading debug data: ' + error + '</p>';
                });
        }
        
        function refreshAnalysis() {
            location.reload();
        }
        
        function clearDebugOutput() {
            const output = document.getElementById('debug-output');
            output.style.display = 'none';
        }
    </script>
</body>
</html>
