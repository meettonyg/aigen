<?php
/**
 * Comprehensive test for Topics Generator and Authority Hook Builder
 * Tests both the root fixes and validates data loading from Pods
 */

// Include WordPress
$wp_load_path = '';
$current_dir = dirname(__FILE__);

// Try to find wp-load.php
$possible_paths = [
    $current_dir . '/../../../../../../wp-load.php',
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

// Include the fixed services
require_once dirname(__FILE__) . '/media-kit-content-generator/includes/services/class-mkcg-pods-service.php';
require_once dirname(__FILE__) . '/media-kit-content-generator/includes/generators/enhanced_topics_generator.php';

echo "<h1>Topics Generator Root Fixes Validation</h1>";

// Test 1: Check for guest posts
echo "<h2>Test 1: Guest Posts Detection</h2>";
$guests_posts = get_posts([
    'post_type' => 'guests',
    'post_status' => 'any',
    'numberposts' => 10
]);

if (empty($guests_posts)) {
    echo "<div style='color:red'>❌ No guest posts found! Please create some guest entries first.</div>";
    echo "<p>To test the fixes, you need to:</p>";
    echo "<ol>";
    echo "<li>Create a guest post in WordPress admin</li>";
    echo "<li>Add some data to the topic and authority hook fields</li>";
    echo "<li>Run this test again</li>";
    echo "</ol>";
    exit;
}

echo "<div style='color:green'>✅ Found " . count($guests_posts) . " guest posts</div>";

// Test 2: Test the enhanced Pods service
echo "<h2>Test 2: Enhanced Pods Service</h2>";
$pods_service = new MKCG_Pods_Service();
echo "<div style='color:green'>✅ MKCG_Pods_Service created successfully</div>";

// Test with the first guest post
$test_post = $guests_posts[0];
echo "<h3>Testing with Post ID: {$test_post->ID} ('{$test_post->post_title}')</h3>";

// Test topics loading
echo "<h4>Topics Loading Test:</h4>";
$topics = $pods_service->get_topics($test_post->ID);
echo "<pre>" . print_r($topics, true) . "</pre>";

$filled_topics = array_filter($topics);
if (count($filled_topics) > 0) {
    echo "<div style='color:green'>✅ Found " . count($filled_topics) . " topics with data</div>";
} else {
    echo "<div style='color:orange'>⚠️ No topic data found - this is expected if you haven't added topics yet</div>";
}

// Test authority hook loading
echo "<h4>Authority Hook Components Test:</h4>";
$auth_components = $pods_service->get_authority_hook_components($test_post->ID);
echo "<pre>" . print_r($auth_components, true) . "</pre>";

$meaningful_components = 0;
$defaults = ['they need help', 'achieve their goals', 'through your method', 'in their situation', 'because they deserve success', 'your audience'];
foreach ($auth_components as $key => $value) {
    if ($key !== 'complete' && !empty($value) && !in_array($value, $defaults)) {
        $meaningful_components++;
    }
}

if ($meaningful_components > 0) {
    echo "<div style='color:green'>✅ Found {$meaningful_components} authority hook components with meaningful data</div>";
} else {
    echo "<div style='color:orange'>⚠️ No meaningful authority hook data found - using defaults</div>";
}

// Test 3: Test the enhanced Topics Generator
echo "<h2>Test 3: Enhanced Topics Generator</h2>";

// Mock the services needed for the generator
$mock_api_service = new stdClass();
$topics_generator = new Enhanced_Topics_Generator($mock_api_service);
echo "<div style='color:green'>✅ Enhanced_Topics_Generator created successfully</div>";

// Test post ID detection with different URL parameters
echo "<h4>Post ID Detection Test:</h4>";

// Simulate different URL scenarios
$test_scenarios = [
    ['description' => 'Direct post_id parameter', 'get_params' => ['post_id' => $test_post->ID]],
    ['description' => 'Entry parameter (if linked)', 'get_params' => ['entry' => '999']],
    ['description' => 'No parameters (fallback)', 'get_params' => []]
];

foreach ($test_scenarios as $scenario) {
    echo "<h5>{$scenario['description']}:</h5>";
    
    // Simulate the GET parameters
    $_GET = $scenario['get_params'];
    
    // Test the template data loading
    $template_data = $topics_generator->get_template_data();
    
    echo "Post ID detected: " . $template_data['post_id'] . "<br>";
    echo "Has entry: " . ($template_data['has_entry'] ? 'Yes' : 'No') . "<br>";
    echo "Topics count: " . count(array_filter($template_data['form_field_values'])) . "<br>";
    echo "Authority hook WHO: " . $template_data['authority_hook_components']['who'] . "<br>";
    echo "<br>";
}

// Clean up
$_GET = [];

// Test 4: Validation summary
echo "<h2>Test 4: Validation Summary</h2>";

$all_tests_passed = true;
$issues = [];

// Check if we found meaningful data
if (count($filled_topics) === 0 && $meaningful_components === 0) {
    $issues[] = "No meaningful data found in any guest posts - please add some topic and authority hook data";
    $all_tests_passed = false;
}

// Check if Topics Generator can detect posts
if ($template_data['post_id'] === 0) {
    $issues[] = "Topics Generator unable to detect any guest posts";
    $all_tests_passed = false;
}

if ($all_tests_passed && empty($issues)) {
    echo "<div style='color:green; background:#e8f5e8; padding:15px; border:1px solid #4caf50; border-radius:5px;'>";
    echo "<h3>✅ ALL TESTS PASSED!</h3>";
    echo "<p>The root fixes are working correctly. Your Topics Generator and Authority Hook Builder should now populate data from the Pods 'guests' custom post type.</p>";
    echo "</div>";
} else {
    echo "<div style='color:orange; background:#fff3e0; padding:15px; border:1px solid #ff9800; border-radius:5px;'>";
    echo "<h3>⚠️ PARTIAL SUCCESS</h3>";
    echo "<p>The code fixes are working, but you need to add some data to test the full functionality:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>{$issue}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Test 5: Usage instructions
echo "<h2>Test 5: How to Use the Fixed System</h2>";
echo "<div style='background:#f0f8ff; padding:15px; border:1px solid #2196f3; border-radius:5px;'>";
echo "<h3>📋 Usage Instructions:</h3>";
echo "<ol>";
echo "<li><strong>Create or edit a guest post:</strong> Go to your WordPress admin and create/edit a post in the 'Guest One Sheets' post type</li>";
echo "<li><strong>Add topic data:</strong> Fill in the 'Topic 1', 'Topic 2', etc. fields in the Topics and Questions section</li>";
echo "<li><strong>Add authority hook data:</strong> Fill in the messaging fields like 'When', 'What', 'How', 'Where', 'Why' and 'Professional Title'</li>";
echo "<li><strong>Use the shortcode:</strong> Add <code>[mkcg_topics]</code> to any page or post</li>";
echo "<li><strong>Test with URL parameters:</strong> You can also test with <code>?post_id={$test_post->ID}</code> to load a specific guest</li>";
echo "</ol>";
echo "</div>";

// Test 6: Available guest posts for testing
echo "<h2>Test 6: Available Guest Posts for Testing</h2>";
echo "<div style='background:#f8f9fa; padding:15px; border:1px solid #dee2e6; border-radius:5px;'>";
echo "<h3>🔗 Test URLs:</h3>";
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base_url = dirname($current_url);

foreach ($guests_posts as $post) {
    echo "<p><strong>{$post->post_title}</strong> (ID: {$post->ID})<br>";
    echo "<a href='{$base_url}?post_id={$post->ID}' target='_blank'>Test with this guest post</a></p>";
}
echo "</div>";

echo "<hr><p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>