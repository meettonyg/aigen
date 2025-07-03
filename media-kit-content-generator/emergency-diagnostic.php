<?php
/**
 * Emergency Diagnostic for Entry 74492
 * Direct database queries to find missing data
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

$entry_id = 74492;
$post_id = 32372; // From your screenshot

echo "<h1>🚨 Emergency Diagnostic: Missing Data Investigation</h1>";

echo "<h2>1. Direct Database Check - Formidable Data</h2>";
global $wpdb;

// Check all Formidable data for this entry
$formidable_data = $wpdb->get_results($wpdb->prepare(
    "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d ORDER BY field_id",
    $entry_id
));

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field ID</th><th>Value</th><th>Expected For</th></tr>";

$expected_fields = [
    '8498' => 'Topic 1',
    '8499' => 'Topic 2', 
    '8500' => 'Topic 3',
    '8501' => 'Topic 4',
    '8502' => 'Topic 5',
    '10297' => 'WHAT/RESULT',
    '10387' => 'WHEN',
    '10298' => 'HOW',
    '10359' => 'WHERE',
    '10360' => 'WHY',
    '10358' => 'COMPLETE'
];

foreach ($formidable_data as $field) {
    $expected = isset($expected_fields[$field->field_id]) ? $expected_fields[$field->field_id] : 'Unknown';
    $value = strlen($field->meta_value) > 100 ? substr($field->meta_value, 0, 100) . '...' : $field->meta_value;
    echo "<tr>";
    echo "<td>{$field->field_id}</td>";
    echo "<td>" . esc_html($value) . "</td>";
    echo "<td>{$expected}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>2. Direct Database Check - Custom Post Meta</h2>";

// Check all custom post meta for this post
$post_meta = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key LIKE 'mkcg_%' ORDER BY meta_key",
    $post_id
));

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Meta Key</th><th>Value</th><th>Expected For</th></tr>";

$expected_meta = [
    'mkcg_who' => 'WHO',
    'mkcg_topic_1' => 'Topic 1',
    'mkcg_topic_2' => 'Topic 2',
    'mkcg_topic_3' => 'Topic 3', 
    'mkcg_topic_4' => 'Topic 4',
    'mkcg_topic_5' => 'Topic 5'
];

foreach ($post_meta as $meta) {
    $expected = isset($expected_meta[$meta->meta_key]) ? $expected_meta[$meta->meta_key] : 'Unknown MKCG field';
    $value = strlen($meta->meta_value) > 100 ? substr($meta->meta_value, 0, 100) . '...' : $meta->meta_value;
    echo "<tr>";
    echo "<td>{$meta->meta_key}</td>";
    echo "<td>" . esc_html($value) . "</td>";
    echo "<td>{$expected}</td>";
    echo "</tr>";
}

if (empty($post_meta)) {
    echo "<tr><td colspan='3' style='color: red; text-align: center;'>❌ NO MKCG CUSTOM POST META FOUND</td></tr>";
}
echo "</table>";

echo "<h2>3. Check for Questions Data</h2>";

// Look for question patterns
$question_meta = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key LIKE 'mkcg_question_%' ORDER BY meta_key",
    $post_id
));

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Meta Key</th><th>Value</th></tr>";

if (!empty($question_meta)) {
    foreach ($question_meta as $meta) {
        $value = strlen($meta->meta_value) > 100 ? substr($meta->meta_value, 0, 100) . '...' : $meta->meta_value;
        echo "<tr>";
        echo "<td>{$meta->meta_key}</td>";
        echo "<td>" . esc_html($value) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='2' style='color: red; text-align: center;'>❌ NO QUESTION DATA FOUND</td></tr>";
}
echo "</table>";

echo "<h2>4. Alternative Topic Storage Check</h2>";

// Check if topics are stored with different field IDs
$all_formidable_data = $wpdb->get_results($wpdb->prepare(
    "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND meta_value != '' ORDER BY field_id",
    $entry_id
));

echo "<p><strong>All non-empty Formidable fields for entry {$entry_id}:</strong></p>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field ID</th><th>Value Preview</th></tr>";

foreach ($all_formidable_data as $field) {
    // Check if this could be topic data
    $value_preview = strlen($field->meta_value) > 80 ? substr($field->meta_value, 0, 80) . '...' : $field->meta_value;
    $highlight = '';
    
    // Highlight potential topic fields
    if (strlen($field->meta_value) < 100 && !in_array($field->field_id, ['10297', '10387', '10298', '10359', '10360', '10358'])) {
        $highlight = 'style="background: yellow;"';
    }
    
    echo "<tr {$highlight}>";
    echo "<td>{$field->field_id}</td>";
    echo "<td>" . esc_html($value_preview) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>5. Check All Post Meta (not just MKCG)</h2>";

// Check if data is stored with different meta keys
$all_post_meta = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_value != '' AND meta_key NOT LIKE '_%%' ORDER BY meta_key",
    $post_id
));

echo "<p><strong>All non-empty, non-private post meta for post {$post_id}:</strong></p>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Meta Key</th><th>Value Preview</th></tr>";

foreach ($all_post_meta as $meta) {
    $value_preview = strlen($meta->meta_value) > 80 ? substr($meta->meta_value, 0, 80) . '...' : $meta->meta_value;
    $highlight = '';
    
    // Highlight potential topic/question fields
    if (strpos($meta->meta_key, 'topic') !== false || strpos($meta->meta_key, 'question') !== false) {
        $highlight = 'style="background: yellow;"';
    }
    
    echo "<tr {$highlight}>";
    echo "<td>{$meta->meta_key}</td>";
    echo "<td>" . esc_html($value_preview) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>6. Test Centralized Service Calls</h2>";

// Load services and test them
require_once plugin_dir_path(__FILE__) . 'includes/services/class-mkcg-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/services/enhanced_formidable_service.php';

$formidable_service = new Enhanced_Formidable_Service();

echo "<h3>6.1 Enhanced_Formidable_Service::get_entry_data()</h3>";
$entry_data = $formidable_service->get_entry_data($entry_id);
echo "<pre>" . print_r($entry_data, true) . "</pre>";

echo "<h3>6.2 Enhanced_Formidable_Service::get_topics_from_post_enhanced()</h3>";
$topics_data = $formidable_service->get_topics_from_post_enhanced($post_id);
echo "<pre>" . print_r($topics_data, true) . "</pre>";

echo "<h3>6.3 Enhanced_Formidable_Service::get_questions_with_integrity_check()</h3>";
$questions_data = $formidable_service->get_questions_with_integrity_check($post_id);
echo "<pre>" . print_r($questions_data, true) . "</pre>";

echo "<h3>6.4 MKCG_Config::load_data_for_entry()</h3>";
$config_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
echo "<pre>" . print_r($config_data, true) . "</pre>";

echo "<h2>🔍 Investigation Complete</h2>";
echo "<p><a href='view-entry-data.php'>← Back to Data View</a></p>";
?>
