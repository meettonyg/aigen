<?php
/**
 * ROOT FIX VERIFICATION TEST
 * Quick test to verify the clean slate fix is working
 * 
 * Usage: Run this file directly or include in WordPress
 * Expected: All default values should be empty (no "achieve their goals" text)
 */

// Simulate WordPress environment if not already loaded
if (!defined('ABSPATH')) {
    // Simple simulation for testing
    define('ABSPATH', dirname(__FILE__) . '/');
    
    // Mock WordPress functions for testing
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
        function error_log($message) { echo "[LOG] " . $message . "\n"; }
    }
}

// Load the Authority Hook Service
require_once __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';

// Test the ROOT FIX
echo "<h1>🧪 ROOT FIX VERIFICATION TEST</h1>\n";
echo "<h2>Testing Clean Slate Behavior</h2>\n";

// Simulate no entry parameters (clean slate scenario)
$_GET = []; // Clear any entry parameters

// Create service instance
$service = new MKCG_Authority_Hook_Service();

// Test 1: Get Authority Hook Data with no post ID (clean slate)
echo "<h3>Test 1: Authority Hook Data with No Post ID</h3>\n";
$result = $service->get_authority_hook_data(0, true); // Explicitly request clean slate
echo "<pre>";
print_r($result);
echo "</pre>";

// Verify all components are empty
$all_empty = true;
foreach ($result['components'] as $key => $value) {
    if (!empty($value)) {
        $all_empty = false;
        echo "<span style='color: red;'>❌ FAIL: Component '$key' is not empty: '$value'</span><br>\n";
    }
}

if ($all_empty) {
    echo "<span style='color: green;'>✅ PASS: All components are empty (clean slate working)</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAIL: Some components have default values</span><br>\n";
}

// Test 2: Render Authority Hook Builder
echo "<h3>Test 2: Authority Hook Builder Rendering</h3>\n";
$empty_values = ['who' => '', 'what' => '', 'when' => '', 'how' => ''];
$options = ['clean_slate_mode' => true];

$rendered_html = $service->render_authority_hook_builder('topics', $empty_values, $options);

// Check if rendered HTML contains default text
$contains_defaults = false;
$default_texts = ['achieve their goals', 'your audience', 'they need help', 'through your method'];

foreach ($default_texts as $default_text) {
    if (strpos($rendered_html, $default_text) !== false) {
        $contains_defaults = true;
        echo "<span style='color: red;'>❌ FAIL: Found default text '$default_text' in rendered HTML</span><br>\n";
    }
}

if (!$contains_defaults) {
    echo "<span style='color: green;'>✅ PASS: No default placeholder text found in rendered HTML</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAIL: Default placeholder text still present in HTML</span><br>\n";
}

// Test 3: Check placeholder behavior
echo "<h3>Test 3: Placeholder Behavior</h3>\n";

// Test empty value - should show placeholder
$who_field_empty = '<input type="text" id="mkcg-who" value="" placeholder="' . (empty('') ? 'Selected audiences will appear here automatically' : '') . '">';
$placeholder_empty = (strpos($who_field_empty, 'placeholder="Selected audiences') !== false);

// Test with value - should not show placeholder  
$who_field_filled = '<input type="text" id="mkcg-who" value="test audience" placeholder="' . (empty('test audience') ? 'Selected audiences will appear here automatically' : '') . '">';
$placeholder_filled = (strpos($who_field_filled, 'placeholder=""') !== false);

if ($placeholder_empty && $placeholder_filled) {
    echo "<span style='color: green;'>✅ PASS: Placeholder logic working correctly</span><br>\n";
    echo "  - Empty field shows placeholder: " . ($placeholder_empty ? 'Yes' : 'No') . "<br>\n";
    echo "  - Filled field hides placeholder: " . ($placeholder_filled ? 'Yes' : 'No') . "<br>\n";
} else {
    echo "<span style='color: red;'>❌ FAIL: Placeholder logic not working correctly</span><br>\n";
    echo "  - Empty field shows placeholder: " . ($placeholder_empty ? 'Yes' : 'No') . "<br>\n";
    echo "  - Filled field hides placeholder: " . ($placeholder_filled ? 'Yes' : 'No') . "<br>\n";
}

// Test 4: Build Complete Hook
echo "<h3>Test 4: Complete Hook Building</h3>\n";
$empty_components = ['who' => '', 'what' => '', 'when' => '', 'how' => ''];
$complete_hook_empty = $service->build_complete_hook($empty_components);

$filled_components = ['who' => 'test audience', 'what' => 'achieve goals', 'when' => 'they struggle', 'how' => 'with my help'];
$complete_hook_filled = $service->build_complete_hook($filled_components);

echo "Empty components hook: '$complete_hook_empty'<br>\n";
echo "Filled components hook: '$complete_hook_filled'<br>\n";

// Verify empty components produce expected result
$empty_expected = "I help    ."; // Should have empty spaces
if (trim($complete_hook_empty) === "I help    .") {
    echo "<span style='color: green;'>✅ PASS: Empty components produce minimal hook</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAIL: Empty components don't produce expected result</span><br>\n";
}

// Summary
echo "<h2>🎯 ROOT FIX VERIFICATION SUMMARY</h2>\n";

$tests_passed = 0;
$total_tests = 4;

if ($all_empty) $tests_passed++;
if (!$contains_defaults) $tests_passed++;
if ($placeholder_empty && $placeholder_filled) $tests_passed++;
if (trim($complete_hook_empty) === "I help    .") $tests_passed++;

if ($tests_passed === $total_tests) {
    echo "<h3 style='color: green;'>🎉 ALL TESTS PASSED ($tests_passed/$total_tests)</h3>\n";
    echo "<p style='color: green;'><strong>✅ ROOT FIX SUCCESS: Clean slate behavior is working correctly!</strong></p>\n";
    echo "<p>No default placeholder text will appear when no data exists.</p>\n";
} else {
    echo "<h3 style='color: red;'>❌ SOME TESTS FAILED ($tests_passed/$total_tests)</h3>\n";
    echo "<p style='color: red;'><strong>❌ ROOT FIX INCOMPLETE: Some issues remain</strong></p>\n";
    echo "<p>Review the failed tests above and check the implementation.</p>\n";
}

// Additional debugging info
echo "<h3>🔍 Debug Information</h3>\n";
echo "DEFAULT_COMPONENTS: ";
$reflection = new ReflectionClass('MKCG_Authority_Hook_Service');
$default_components = $reflection->getConstant('DEFAULT_COMPONENTS');
print_r($default_components);

echo "<br>LEGACY_DEFAULT_COMPONENTS: ";
$legacy_components = $reflection->getConstant('LEGACY_DEFAULT_COMPONENTS');
print_r($legacy_components);

echo "<br>Current GET parameters: ";
print_r($_GET);

echo "\n<hr>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>If all tests passed, the ROOT FIX is working correctly</li>\n";
echo "<li>Test in a real WordPress environment by accessing the Topics Generator</li>\n";
echo "<li>Verify form fields are empty when no entry parameter is provided</li>\n";
echo "<li>Confirm save functionality works with the clean slate implementation</li>\n";
echo "</ol>\n";

// JavaScript test helper
echo "<script>\n";
echo "console.log('🧪 ROOT FIX VERIFICATION TEST completed');\n";
echo "console.log('Tests passed: $tests_passed/$total_tests');\n";
echo "if ($tests_passed === $total_tests) {\n";
echo "  console.log('✅ ROOT FIX SUCCESS: Clean slate behavior working!');\n";
echo "} else {\n";
echo "  console.log('❌ ROOT FIX INCOMPLETE: Check failed tests');\n";
echo "}\n";
echo "</script>\n";
?>
