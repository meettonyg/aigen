<?php
/**
 * CLEAN CODE VERIFICATION
 * Test the simplified Authority Hook Service
 */

echo "<h1>🧪 CLEAN CODE VERIFICATION</h1>\n";

// Test if we're in WordPress
if (defined('ABSPATH')) {
    echo "<p>✅ Running in WordPress environment</p>\n";
} else {
    echo "<p>⚠️ Not in WordPress - running standalone test</p>\n";
    
    // Define basic WordPress functions for testing
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) { return trim(strip_tags($str)); }
    }
    if (!function_exists('wp_parse_args')) {
        function wp_parse_args($args, $defaults) { return array_merge($defaults, $args); }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($str) { return htmlspecialchars($str, ENT_QUOTES); }
    }
    if (!function_exists('get_post_meta')) {
        function get_post_meta($post_id, $key, $single = false) { return ''; }
    }
    if (!function_exists('error_log')) {
        function error_log($message) { echo "[LOG] " . $message . "<br>\n"; }
    }
    if (!function_exists('get_post')) {
        function get_post($post_id) { 
            return (object)['ID' => $post_id, 'post_title' => 'Test Post', 'post_type' => 'guests']; 
        }
    }
}

// Load the cleaned Authority Hook Service
$service_file = __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';
if (file_exists($service_file)) {
    require_once $service_file;
    echo "<p>✅ Authority Hook Service loaded</p>\n";
} else {
    echo "<p>❌ Service file not found at: $service_file</p>\n";
    exit;
}

// Test the CLEAN CODE approach
echo "<h2>🧹 Clean Code Tests</h2>\n";

if (class_exists('MKCG_Authority_Hook_Service')) {
    $service = new MKCG_Authority_Hook_Service();
    echo "<p>✅ Service instance created</p>\n";
    
    // Test 1: Check constants - should only have DEFAULT_COMPONENTS now
    $reflection = new ReflectionClass('MKCG_Authority_Hook_Service');
    
    echo "<h3>Test 1: Constants Check</h3>\n";
    $default_components = $reflection->getConstant('DEFAULT_COMPONENTS');
    echo "<p><strong>DEFAULT_COMPONENTS:</strong></p>\n";
    echo "<pre>" . print_r($default_components, true) . "</pre>\n";
    
    // Check if legacy constants exist (they shouldn't)
    try {
        $legacy_components = $reflection->getConstant('LEGACY_DEFAULT_COMPONENTS');
        if ($legacy_components !== false) {
            echo "<p>⚠️ LEGACY_DEFAULT_COMPONENTS still exists: " . print_r($legacy_components, true) . "</p>\n";
        } else {
            echo "<p>✅ LEGACY_DEFAULT_COMPONENTS removed successfully</p>\n";
        }
    } catch (Exception $e) {
        echo "<p>✅ LEGACY_DEFAULT_COMPONENTS doesn't exist (good!)</p>\n";
    }
    
    // Verify all defaults are empty
    $all_empty = true;
    foreach ($default_components as $key => $value) {
        if (!empty($value)) {
            $all_empty = false;
            echo "<p>❌ DEFAULT_COMPONENTS['$key'] is not empty: '$value'</p>\n";
        }
    }
    if ($all_empty) {
        echo "<p>✅ All DEFAULT_COMPONENTS are empty</p>\n";
    }
    
    // Test 2: Test with no post ID (should return empty)
    echo "<h3>Test 2: No Post ID Test</h3>\n";
    $result_no_post = $service->get_authority_hook_data(0);
    echo "<p><strong>Result with no post ID:</strong></p>\n";
    echo "<pre>" . print_r($result_no_post, true) . "</pre>\n";
    
    $components_empty = true;
    if (isset($result_no_post['components'])) {
        foreach ($result_no_post['components'] as $key => $value) {
            if (!empty($value)) {
                $components_empty = false;
                echo "<p>❌ Component '$key' is not empty: '$value'</p>\n";
            }
        }
    }
    if ($components_empty) {
        echo "<p>✅ All components are empty (as expected)</p>\n";
    }
    
    // Test 3: Test with post ID (simulated - should still return empty defaults)
    echo "<h3>Test 3: With Post ID Test (simulated)</h3>\n";
    $result_with_post = $service->get_authority_hook_data(123);
    echo "<p><strong>Result with post ID 123:</strong></p>\n";
    echo "<pre>" . print_r($result_with_post, true) . "</pre>\n";
    
    $components_empty_with_post = true;
    if (isset($result_with_post['components'])) {
        foreach ($result_with_post['components'] as $key => $value) {
            if (!empty($value)) {
                $components_empty_with_post = false;
                echo "<p>❌ Component '$key' is not empty: '$value'</p>\n";
            }
        }
    }
    if ($components_empty_with_post) {
        echo "<p>✅ All components are empty even with post ID (clean defaults working)</p>\n";
    }
    
    // Test 4: Test render method
    echo "<h3>Test 4: Render Method Test</h3>\n";
    $empty_values = ['who' => '', 'what' => '', 'when' => '', 'how' => ''];
    $rendered_html = $service->render_authority_hook_builder('topics', $empty_values);
    
    // Check for any default text in rendered HTML
    $contains_defaults = false;
    $default_texts = ['achieve their goals', 'your audience', 'they need help', 'through your method'];
    
    foreach ($default_texts as $default_text) {
        if (strpos($rendered_html, $default_text) !== false) {
            $contains_defaults = true;
            echo "<p>❌ Found default text '$default_text' in rendered HTML</p>\n";
        }
    }
    
    if (!$contains_defaults) {
        echo "<p>✅ No default text found in rendered HTML</p>\n";
    } else {
        echo "<p>❌ Default text still present in HTML</p>\n";
    }
    
    // Summary
    echo "<h2>🎯 Clean Code Summary</h2>\n";
    $tests_passed = 0;
    $total_tests = 4;
    
    if ($all_empty) $tests_passed++;
    if ($components_empty) $tests_passed++;
    if ($components_empty_with_post) $tests_passed++;
    if (!$contains_defaults) $tests_passed++;
    
    if ($tests_passed === $total_tests) {
        echo "<h3 style='color: green;'>🎉 ALL TESTS PASSED ($tests_passed/$total_tests)</h3>\n";
        echo "<p style='color: green;'><strong>✅ CLEAN CODE SUCCESS: No default placeholders will appear!</strong></p>\n";
        echo "<p>The Authority Hook Service now has clean, simple code with no legacy defaults.</p>\n";
    } else {
        echo "<h3 style='color: red;'>❌ SOME TESTS FAILED ($tests_passed/$total_tests)</h3>\n";
        echo "<p style='color: red;'><strong>❌ Issues remain</strong></p>\n";
    }
    
} else {
    echo "<p>❌ MKCG_Authority_Hook_Service class not found</p>\n";
}

echo "<hr>\n";
echo "<h3>🔧 How to Test in WordPress:</h3>\n";
echo "<ol>\n";
echo "<li>Access your Topics Generator with <code>?post_id=123</code> parameter</li>\n";
echo "<li>Form fields should be completely empty (no 'achieve their goals' text)</li>\n";
echo "<li>Authority Hook display should be empty until all fields are filled</li>\n";
echo "<li>Check browser console for 'Starting with empty defaults' log messages</li>\n";
echo "</ol>\n";

echo "<p><strong>Expected behavior:</strong> Empty form fields everywhere, regardless of URL parameters!</p>\n";
?>
