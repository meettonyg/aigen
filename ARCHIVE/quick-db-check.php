<?php
// Quick database check - add this to a PHP file and run it
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

global $wpdb;
$entry_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}frm_items WHERE item_key = 'y8ver'");

echo "Entry ID: " . $entry_id . "<br><br>";

if ($entry_id) {
    $auth_fields = ['10296' => 'WHO', '10297' => 'RESULT', '10387' => 'WHEN', '10298' => 'HOW', '10358' => 'COMPLETE'];
    $topic_fields = ['8498' => 'Topic 1', '8499' => 'Topic 2', '8500' => 'Topic 3', '8501' => 'Topic 4', '8502' => 'Topic 5'];
    
    echo "Authority Hook Fields:<br>";
    foreach ($auth_fields as $field_id => $label) {
        $value = $wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = {$entry_id} AND field_id = {$field_id}");
        echo "- {$label} (Field {$field_id}): " . (empty($value) ? 'EMPTY' : substr($value, 0, 50)) . "<br>";
    }
    
    echo "<br>Topic Fields:<br>";
    foreach ($topic_fields as $field_id => $label) {
        $value = $wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = {$entry_id} AND field_id = {$field_id}");
        echo "- {$label} (Field {$field_id}): " . (empty($value) ? 'EMPTY' : substr($value, 0, 50)) . "<br>";
    }
}
?>
