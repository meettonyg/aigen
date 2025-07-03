<?php
/**
 * MKCG Root Level Fix Diagnostic Script
 * Tests the Pods service integration and data loading
 */

// WordPress bootstrap
require_once '../../../wp-load.php';

// Load plugin dependencies
require_once 'includes/services/class-mkcg-pods-service.php';
require_once 'includes/services/class-mkcg-config.php';

// Initialize services
$pods_service = new MKCG_Pods_Service();

echo "<h1>MKCG Root Level Fix - Pods Integration Diagnostic</h1>\n";

// Test 1: Check if we can find any guests posts
echo "<h2>Test 1: Finding Guests Posts</h2>\n";
$guests_posts = $pods_service->get_all_guests(10);
if (empty($guests_posts)) {
    echo "<p style='color: red;'>❌ No 'guests' post type found. Check if Pods is properly configured.</p>\n";
} else {
    echo "<p style='color: green;'>✅ Found " . count($guests_posts) . " guests posts</p>\n";
    foreach ($guests_posts as $post) {
        echo "<p>- Post ID: {$post->ID}, Title: '{$post->post_title}', Status: {$post->post_status}</p>\n";
    }
}

// Test 2: Test with a specific post ID (you can modify this)
$test_post_id = !empty($guests_posts) ? $guests_posts[0]->ID : 0;
if (isset($_GET['post_id'])) {
    $test_post_id = intval($_GET['post_id']);
}

if ($test_post_id) {
    echo "<h2>Test 2: Testing Data Loading for Post ID: {$test_post_id}</h2>\n";
    
    // Test Pods service data loading
    echo "<h3>Pods Service Data Loading:</h3>\n";
    $guest_data = $pods_service->get_guest_data($test_post_id);
    
    echo "<h4>Topics:</h4>\n";
    if (empty(array_filter($guest_data['topics']))) {
        echo "<p style='color: orange;'>⚠️ No topics found. Check Pods field names.</p>\n";
    } else {
        foreach ($guest_data['topics'] as $topic_key => $topic_value) {
            if (!empty($topic_value)) {
                echo "<p style='color: green;'>✅ {$topic_key}: {$topic_value}</p>\n";
            } else {
                echo "<p style='color: gray;'>- {$topic_key}: (empty)</p>\n";
            }
        }
    }
    
    echo "<h4>Authority Hook Components:</h4>\n";
    $auth_components = $guest_data['authority_hook_components'];
    foreach (['who', 'what', 'when', 'how', 'where', 'why'] as $component) {
        if (!empty($auth_components[$component])) {
            echo "<p style='color: green;'>✅ {$component}: {$auth_components[$component]}</p>\n";
        } else {
            echo "<p style='color: gray;'>- {$component}: (empty or default)</p>\n";
        }
    }
    
    echo "<h4>Complete Authority Hook:</h4>\n";
    echo "<p><strong>" . $auth_components['complete'] . "</strong></p>\n";
    
    // Test centralized config
    echo "<h3>Centralized Config Data Loading:</h3>\n";
    $config_data = MKCG_Config::load_data_for_post($test_post_id, $pods_service);
    
    echo "<h4>Config Loaded Topics:</h4>\n";
    foreach ($config_data['form_field_values'] as $topic_key => $topic_value) {
        if (!empty($topic_value)) {
            echo "<p style='color: green;'>✅ {$topic_key}: {$topic_value}</p>\n";
        } else {
            echo "<p style='color: gray;'>- {$topic_key}: (empty)</p>\n";
        }
    }
    
    echo "<h4>Config Authority Hook:</h4>\n";
    $config_auth = $config_data['authority_hook_components'];
    foreach (['who', 'what', 'when', 'how', 'where', 'why'] as $component) {
        if (!empty($config_auth[$component])) {
            echo "<p style='color: green;'>✅ {$component}: {$config_auth[$component]}</p>\n";
        } else {
            echo "<p style='color: gray;'>- {$component}: (empty or default)</p>\n";
        }
    }
}

// Test 3: Check Pods field mappings
echo "<h2>Test 3: Checking Raw Post Meta Fields</h2>\n";
if ($test_post_id) {
    $fields_to_check = [
        // Topics
        'topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5',
        // Authority Hook
        'hook_when', 'hook_what', 'hook_how', 'hook_where', 'hook_why',
        // Contact
        'guest_title', 'first_name', 'last_name', 'email',
        // Sample questions
        'question_1', 'question_2', 'question_3'
    ];
    
    echo "<h4>Raw Post Meta Values:</h4>\n";
    foreach ($fields_to_check as $field_name) {
        $value = get_post_meta($test_post_id, $field_name, true);
        if (!empty($value)) {
            echo "<p style='color: green;'>✅ {$field_name}: " . htmlspecialchars($value) . "</p>\n";
        } else {
            echo "<p style='color: red;'>❌ {$field_name}: (empty/not found)</p>\n";
        }
    }
}

// Test 4: Field mapping validation
echo "<h2>Test 4: Field Mapping Validation</h2>\n";
$mappings = MKCG_Config::get_field_mappings();
echo "<h4>Topics Field Mappings:</h4>\n";
foreach ($mappings['topics']['fields'] as $internal => $pods_field) {
    echo "<p>- {$internal} → {$pods_field}</p>\n";
}

echo "<h4>Authority Hook Field Mappings:</h4>\n";
foreach ($mappings['authority_hook'] as $component => $config) {
    echo "<p>- {$component} → {$config['field']} (source: {$config['source']})</p>\n";
}

// Instructions
echo "<h2>Instructions</h2>\n";
echo "<p>To test with a specific post ID, add ?post_id=123 to the URL.</p>\n";
echo "<p>If you see ❌ errors, check:</p>\n";
echo "<ul>\n";
echo "<li>Pods plugin is active and 'guests' post type is configured</li>\n";
echo "<li>Field names match exactly (case sensitive)</li>\n";
echo "<li>Post has data in the expected fields</li>\n";
echo "</ul>\n";

echo "<h2>Expected Results</h2>\n";
echo "<p style='color: green;'>✅ Topics should load from topic_1, topic_2, etc. fields</p>\n";
echo "<p style='color: green;'>✅ Authority Hook should load from hook_when, hook_what, etc. fields</p>\n";
echo "<p style='color: green;'>✅ No more 'No data found' or empty results</p>\n";
?>
