<?php
/**
 * Debug script to test Pods data loading
 * Place this file in your plugin directory and run via browser
 */

// Include WordPress
$wp_load_path = '';
$current_dir = dirname(__FILE__);

// Try to find wp-load.php
$possible_paths = [
    $current_dir . '/../../../../../../wp-load.php', // Standard plugin location
    $current_dir . '/../../../../../wp-load.php',
    $current_dir . '/../../../../wp-load.php',
    $current_dir . '/../../../wp-load.php',
    dirname(dirname(dirname(dirname($current_dir)))) . '/wp-load.php'
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $wp_load_path = $path;
        break;
    }
}

if (empty($wp_load_path)) {
    die('WordPress not found. Please adjust the path to wp-load.php in this script.');
}

require_once $wp_load_path;

echo "<h1>Pods Data Loading Debug</h1>";

// 1. Check if Pods plugin is active
if (!function_exists('pods')) {
    echo "<div style='color:red'>❌ Pods plugin is not active!</div>";
    exit;
}

echo "<div style='color:green'>✅ Pods plugin is active</div>";

// 2. Check for guests post type
$guests_posts = get_posts([
    'post_type' => 'guests',
    'post_status' => 'any',
    'numberposts' => 5
]);

echo "<h2>Guest Posts Found:</h2>";
if (empty($guests_posts)) {
    echo "<div style='color:red'>❌ No guest posts found! Make sure you have created some guest entries.</div>";
} else {
    echo "<div style='color:green'>✅ Found " . count($guests_posts) . " guest posts</div>";
    foreach ($guests_posts as $post) {
        echo "<div style='border:1px solid #ccc; margin:10px; padding:10px;'>";
        echo "<strong>Post ID:</strong> {$post->ID}<br>";
        echo "<strong>Title:</strong> {$post->post_title}<br>";
        echo "<strong>Status:</strong> {$post->post_status}<br>";
        
        // Test topic fields
        echo "<h4>Topic Fields:</h4>";
        for ($i = 1; $i <= 5; $i++) {
            $topic = get_post_meta($post->ID, "topic_{$i}", true);
            $status = !empty($topic) ? '✅' : '❌';
            echo "{$status} topic_{$i}: " . ($topic ?: '[empty]') . "<br>";
        }
        
        // Test authority hook fields
        echo "<h4>Authority Hook Fields:</h4>";
        $hook_fields = ['guest_title', 'hook_when', 'hook_what', 'hook_how', 'hook_where', 'hook_why'];
        foreach ($hook_fields as $field) {
            $value = get_post_meta($post->ID, $field, true);
            $status = !empty($value) ? '✅' : '❌';
            echo "{$status} {$field}: " . ($value ?: '[empty]') . "<br>";
        }
        
        echo "</div>";
    }
}

// 3. Test Pods service if it exists
echo "<h2>Testing MKCG Pods Service:</h2>";
$pods_service_path = dirname(__FILE__) . '/includes/services/class-mkcg-pods-service.php';
if (file_exists($pods_service_path)) {
    require_once $pods_service_path;
    
    if (class_exists('MKCG_Pods_Service')) {
        $pods_service = new MKCG_Pods_Service();
        echo "<div style='color:green'>✅ MKCG_Pods_Service loaded successfully</div>";
        
        if (!empty($guests_posts)) {
            $test_post = $guests_posts[0];
            echo "<h3>Testing with Post ID: {$test_post->ID}</h3>";
            
            $guest_data = $pods_service->get_guest_data($test_post->ID);
            echo "<pre>" . print_r($guest_data, true) . "</pre>";
        }
    } else {
        echo "<div style='color:red'>❌ MKCG_Pods_Service class not found</div>";
    }
} else {
    echo "<div style='color:red'>❌ MKCG Pods Service file not found at: {$pods_service_path}</div>";
}

// 4. Test URL parameters for current setup
echo "<h2>Current URL Parameters:</h2>";
echo "entry: " . (isset($_GET['entry']) ? $_GET['entry'] : 'not set') . "<br>";
echo "post_id: " . (isset($_GET['post_id']) ? $_GET['post_id'] : 'not set') . "<br>";

if (isset($_GET['entry'])) {
    $entry_id = intval($_GET['entry']);
    
    // Check if this entry exists in Formidable
    global $wpdb;
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
        $entry_id
    ));
    
    if ($post_id) {
        echo "<div style='color:green'>✅ Entry {$entry_id} links to Post ID: {$post_id}</div>";
        
        // Test data for this specific post
        if (class_exists('MKCG_Pods_Service')) {
            $pods_service = new MKCG_Pods_Service();
            $guest_data = $pods_service->get_guest_data($post_id);
            echo "<h3>Data for this entry:</h3>";
            echo "<pre>" . print_r($guest_data, true) . "</pre>";
        }
    } else {
        echo "<div style='color:red'>❌ Entry {$entry_id} not found or not linked to a post</div>";
    }
}

// 5. Instructions
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Make sure you have guest posts with data in the topic and authority hook fields</li>";
echo "<li>Test the Topics Generator with: <strong>?entry=[entry_id]</strong> where entry_id is a valid Formidable entry</li>";
echo "<li>Or test with: <strong>?post_id=[post_id]</strong> where post_id is a valid guest post ID</li>";
echo "</ol>";

if (!empty($guests_posts)) {
    echo "<h3>Test URLs:</h3>";
    foreach ($guests_posts as $post) {
        echo "<a href='?post_id={$post->ID}' target='_blank'>Test with Post ID {$post->ID}</a><br>";
    }
}
?>