<?php
/**
 * Quick debug script to check what data the Pods service is actually retrieving
 */

// Include WordPress
require_once(__DIR__ . '/../../../wp-load.php');

// Include our services
require_once(__DIR__ . '/../media-kit-content-generator/includes/services/class-mkcg-pods-service.php');

echo "<h1>Debugging Data Retrieval</h1>";

// Get entry ID from URL
$entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 32372; // Use the entry from your debug log

echo "<h2>Testing with Entry ID: {$entry_id}</h2>";

// Convert entry to post_id
global $wpdb;
$post_id = $wpdb->get_var($wpdb->prepare(
    "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $entry_id
));

echo "<p><strong>Post ID:</strong> {$post_id}</p>";

if (!$post_id) {
    echo "<p style='color:red'>No post_id found for entry {$entry_id}</p>";
    exit;
}

// Check post type
$post = get_post($post_id);
echo "<p><strong>Post Type:</strong> {$post->post_type}</p>";
echo "<p><strong>Post Title:</strong> {$post->post_title}</p>";

// Test Pods service
$pods_service = new MKCG_Pods_Service();

echo "<h3>Testing Pods Service Methods:</h3>";

// Test get_topics
echo "<h4>Topics:</h4>";
$topics = $pods_service->get_topics($post_id);
echo "<pre>" . print_r($topics, true) . "</pre>";

// Test get_authority_hook_components  
echo "<h4>Authority Hook Components:</h4>";
$auth_components = $pods_service->get_authority_hook_components($post_id);
echo "<pre>" . print_r($auth_components, true) . "</pre>";

// Test get_guest_data (the main method)
echo "<h4>Complete Guest Data:</h4>";
$guest_data = $pods_service->get_guest_data($post_id);
echo "<pre>" . print_r($guest_data, true) . "</pre>";

// Check raw post meta
echo "<h4>Raw Post Meta:</h4>";
$all_meta = get_post_meta($post_id);
echo "<pre>" . print_r($all_meta, true) . "</pre>";

// Check what the Enhanced_Topics_Generator returns
require_once(__DIR__ . '/../media-kit-content-generator/includes/generators/enhanced_topics_generator.php');
require_once(__DIR__ . '/../media-kit-content-generator/includes/services/class-mkcg-api-service.php');

echo "<h4>Enhanced Topics Generator Template Data:</h4>";
$api_service = new MKCG_API_Service();
$topics_generator = new Enhanced_Topics_Generator($api_service);

// Simulate the URL parameter
$_GET['entry'] = $entry_id;
$template_data = $topics_generator->get_template_data($entry_id);
echo "<pre>" . print_r($template_data, true) . "</pre>";

echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<ul>";
echo "<li>Entry ID: {$entry_id}</li>";
echo "<li>Post ID: {$post_id}</li>";
echo "<li>Topics found: " . count(array_filter($topics)) . "/5</li>";
echo "<li>Auth components meaningful: " . count(array_filter($auth_components, function($v, $k) { 
    return $k !== 'complete' && !empty($v) && !in_array($v, ['they need help', 'achieve their goals', 'through your method', 'in their situation', 'because they deserve success', 'your audience']); 
}, ARRAY_FILTER_USE_BOTH)) . "/6</li>";
echo "</ul>";
?>