<?php
/**
 * Authority Hook Generator Diagnostic Test
 * Verifies that the Authority Hook generator is properly implemented and functional
 */

// Prevent direct access from web
if (!defined('ABSPATH') && !defined('MKCG_PLUGIN_PATH')) {
    echo "Please run this test from within WordPress or define MKCG_PLUGIN_PATH\n";
    exit;
}

// Set up WordPress environment if running standalone
if (!defined('ABSPATH')) {
    define('MKCG_PLUGIN_PATH', __DIR__ . '/');
    require_once __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Authority Hook Generator Diagnostic Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .test-section { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .file-path { background: #e9ecef; padding: 5px 10px; border-radius: 4px; font-family: monospace; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .test-pass { background: #d4edda; border: 1px solid #c3e6cb; }
        .test-fail { background: #f8d7da; border: 1px solid #f5c6cb; }
        .test-warn { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>";

echo "<h1>Authority Hook Generator Diagnostic Test</h1>";
echo "<p>Running comprehensive tests to verify the Authority Hook generator implementation...</p>";

$test_results = [];
$overall_status = 'pass';

// Test 1: Check if all required files exist
echo "<div class='test-section'>";
echo "<h2>📁 File Structure Test</h2>";

$required_files = [
    'PHP Generator Class' => 'includes/generators/enhanced_authority_hook_generator.php',
    'Template File' => 'templates/generators/authority-hook/default.php',
    'JavaScript File' => 'assets/js/generators/authority-hook-generator.js',
    'Authority Hook Service' => 'includes/services/class-mkcg-authority-hook-service.php',
    'Main Plugin File' => 'media-kit-content-generator.php',
    'Unified CSS' => 'assets/css/mkcg-unified-styles.css'
];

foreach ($required_files as $name => $path) {
    $full_path = __DIR__ . '/' . $path;
    if (file_exists($full_path)) {
        echo "<div class='test-result test-pass'>✅ <strong>{$name}</strong>: <span class='file-path'>{$path}</span> EXISTS</div>";
        $test_results[$name] = 'pass';
    } else {
        echo "<div class='test-result test-fail'>❌ <strong>{$name}</strong>: <span class='file-path'>{$path}</span> MISSING</div>";
        $test_results[$name] = 'fail';
        $overall_status = 'fail';
    }
}

echo "</div>";

// Test 2: Check shortcode registration in main plugin file
echo "<div class='test-section'>";
echo "<h2>🔧 Plugin Integration Test</h2>";

$main_plugin_content = file_get_contents(__DIR__ . '/media-kit-content-generator.php');

// Check for shortcode registration
if (strpos($main_plugin_content, 'mkcg_authority_hook') !== false) {
    echo "<div class='test-result test-pass'>✅ <strong>Shortcode Registration</strong>: [mkcg_authority_hook] found in main plugin file</div>";
    $test_results['Shortcode Registration'] = 'pass';
} else {
    echo "<div class='test-result test-fail'>❌ <strong>Shortcode Registration</strong>: [mkcg_authority_hook] NOT found in main plugin file</div>";
    $test_results['Shortcode Registration'] = 'fail';
    $overall_status = 'fail';
}

// Check for generator initialization
if (strpos($main_plugin_content, 'Enhanced_Authority_Hook_Generator') !== false) {
    echo "<div class='test-result test-pass'>✅ <strong>Generator Initialization</strong>: Enhanced_Authority_Hook_Generator found in main plugin</div>";
    $test_results['Generator Initialization'] = 'pass';
} else {
    echo "<div class='test-result test-fail'>❌ <strong>Generator Initialization</strong>: Enhanced_Authority_Hook_Generator NOT found in main plugin</div>";
    $test_results['Generator Initialization'] = 'fail';
    $overall_status = 'fail';
}

// Check for Authority Hook Service dependency
if (strpos($main_plugin_content, 'class-mkcg-authority-hook-service.php') !== false) {
    echo "<div class='test-result test-pass'>✅ <strong>Service Dependency</strong>: Authority Hook Service properly loaded</div>";
    $test_results['Service Dependency'] = 'pass';
} else {
    echo "<div class='test-result test-warn'>⚠️ <strong>Service Dependency</strong>: Authority Hook Service loaded via generator (acceptable)</div>";
    $test_results['Service Dependency'] = 'warn';
}

echo "</div>";

// Test 3: Check JavaScript dependencies and configuration
echo "<div class='test-section'>";
echo "<h2>🚀 JavaScript Integration Test</h2>";

$js_content = file_get_contents(__DIR__ . '/assets/js/generators/authority-hook-generator.js');

// Check for key JavaScript components
$js_checks = [
    'Event Binding' => 'bindEvents',
    'AJAX Save Function' => 'saveAuthorityHook',
    'Field Population' => 'populateFields',
    'Real-time Updates' => 'updatePreview',
    'Error Handling' => 'showMessage'
];

foreach ($js_checks as $name => $pattern) {
    if (strpos($js_content, $pattern) !== false) {
        echo "<div class='test-result test-pass'>✅ <strong>{$name}</strong>: {$pattern} function found</div>";
        $test_results["JS {$name}"] = 'pass';
    } else {
        echo "<div class='test-result test-fail'>❌ <strong>{$name}</strong>: {$pattern} function NOT found</div>";
        $test_results["JS {$name}"] = 'fail';
        $overall_status = 'fail';
    }
}

// Check for jQuery dependency
if (strpos($js_content, '(function($)') !== false || strpos($js_content, 'jQuery') !== false) {
    echo "<div class='test-result test-pass'>✅ <strong>jQuery Integration</strong>: jQuery properly wrapped</div>";
    $test_results['jQuery Integration'] = 'pass';
} else {
    echo "<div class='test-result test-warn'>⚠️ <strong>jQuery Integration</strong>: jQuery usage not detected</div>";
    $test_results['jQuery Integration'] = 'warn';
}

echo "</div>";

// Test 4: Check template structure and requirements
echo "<div class='test-section'>";
echo "<h2>📋 Template Structure Test</h2>";

$template_content = file_get_contents(__DIR__ . '/templates/generators/authority-hook/default.php');

// Check for key template components
$template_checks = [
    'Two-Panel Layout' => 'generator__panel--left',
    'Authority Hook Builder' => 'authority-hook-builder',
    'Right Panel Guidance' => 'generator__panel--right',
    'Save Button' => 'authority-hook-generator-save-button',
    'Hidden Form Fields' => 'authority-hook-generator-post-id',
    'JavaScript Data' => 'MKCG_Authority_Hook_Data'
];

foreach ($template_checks as $name => $pattern) {
    if (strpos($template_content, $pattern) !== false) {
        echo "<div class='test-result test-pass'>✅ <strong>{$name}</strong>: {$pattern} found in template</div>";
        $test_results["Template {$name}"] = 'pass';
    } else {
        echo "<div class='test-result test-fail'>❌ <strong>{$name}</strong>: {$pattern} NOT found in template</div>";
        $test_results["Template {$name}"] = 'fail';
        $overall_status = 'fail';
    }
}

// Check for right panel content as specified in requirements
$right_panel_content = [
    'Crafting Your Perfect Authority Hook' => 'Crafting Your Perfect Authority Hook',
    'Formula Section' => 'FORMULA',
    'Why Authority Hooks Matter' => 'Why Authority Hooks Matter',
    'Example Authority Hooks' => 'Example Authority Hooks'
];

foreach ($right_panel_content as $name => $pattern) {
    if (strpos($template_content, $pattern) !== false) {
        echo "<div class='test-result test-pass'>✅ <strong>Right Panel {$name}</strong>: Content found</div>";
        $test_results["Right Panel {$name}"] = 'pass';
    } else {
        echo "<div class='test-result test-fail'>❌ <strong>Right Panel {$name}</strong>: Content NOT found</div>";
        $test_results["Right Panel {$name}"] = 'fail';
        $overall_status = 'fail';
    }
}

echo "</div>";

// Test 5: Authority Hook Service functionality
echo "<div class='test-section'>";
echo "<h2>🔧 Authority Hook Service Test</h2>";

if (class_exists('MKCG_Authority_Hook_Service')) {
    echo "<div class='test-result test-pass'>✅ <strong>Service Class</strong>: MKCG_Authority_Hook_Service class available</div>";
    $test_results['Service Class'] = 'pass';
    
    try {
        $service = new MKCG_Authority_Hook_Service();
        echo "<div class='test-result test-pass'>✅ <strong>Service Instantiation</strong>: Authority Hook Service can be instantiated</div>";
        $test_results['Service Instantiation'] = 'pass';
        
        // Test key methods
        $key_methods = ['get_authority_hook_data', 'save_authority_hook_data', 'render_authority_hook_builder', 'build_complete_hook'];
        foreach ($key_methods as $method) {
            if (method_exists($service, $method)) {
                echo "<div class='test-result test-pass'>✅ <strong>Service Method</strong>: {$method}() available</div>";
                $test_results["Service {$method}"] = 'pass';
            } else {
                echo "<div class='test-result test-fail'>❌ <strong>Service Method</strong>: {$method}() NOT available</div>";
                $test_results["Service {$method}"] = 'fail';
                $overall_status = 'fail';
            }
        }
        
        // Test service functionality with dummy data
        $test_components = ['who' => 'test audience', 'what' => 'test result', 'when' => 'test timing', 'how' => 'test method'];
        $complete_hook = $service->build_complete_hook($test_components);
        if (strpos($complete_hook, 'I help test audience test result when test timing test method.') !== false) {
            echo "<div class='test-result test-pass'>✅ <strong>Service Functionality</strong>: build_complete_hook() works correctly</div>";
            $test_results['Service Functionality'] = 'pass';
        } else {
            echo "<div class='test-result test-fail'>❌ <strong>Service Functionality</strong>: build_complete_hook() returns unexpected result: {$complete_hook}</div>";
            $test_results['Service Functionality'] = 'fail';
            $overall_status = 'fail';
        }
        
    } catch (Exception $e) {
        echo "<div class='test-result test-fail'>❌ <strong>Service Instantiation</strong>: Error - " . esc_html($e->getMessage()) . "</div>";
        $test_results['Service Instantiation'] = 'fail';
        $overall_status = 'fail';
    }
} else {
    echo "<div class='test-result test-fail'>❌ <strong>Service Class</strong>: MKCG_Authority_Hook_Service class NOT available</div>";
    $test_results['Service Class'] = 'fail';
    $overall_status = 'fail';
}

echo "</div>";

// Test Summary
echo "<div class='test-section'>";
echo "<h2>📊 Test Summary</h2>";

$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results, function($status) { return $status === 'pass'; }));
$failed_tests = count(array_filter($test_results, function($status) { return $status === 'fail'; }));
$warning_tests = count(array_filter($test_results, function($status) { return $status === 'warn'; }));

echo "<p><strong>Total Tests:</strong> {$total_tests}</p>";
echo "<p><span class='success'>✅ Passed:</span> {$passed_tests}</p>";
echo "<p><span class='error'>❌ Failed:</span> {$failed_tests}</p>";
echo "<p><span class='warning'>⚠️ Warnings:</span> {$warning_tests}</p>";

if ($overall_status === 'pass' && $failed_tests === 0) {
    echo "<div class='test-result test-pass'>";
    echo "<h3>🎉 AUTHORITY HOOK GENERATOR IS FULLY IMPLEMENTED!</h3>";
    echo "<p>All core components are in place and functional. The Authority Hook generator should work correctly.</p>";
    echo "<p><strong>Usage:</strong> Add the shortcode <code>[mkcg_authority_hook]</code> to any page to display the Authority Hook generator.</p>";
    echo "<p><strong>URL Parameters:</strong> Use <code>?post_id=123</code> to load data for a specific guest post.</p>";
    echo "</div>";
} else {
    echo "<div class='test-result test-fail'>";
    echo "<h3>⚠️ ISSUES DETECTED</h3>";
    echo "<p>Some components need attention. Check the failed tests above for specific issues.</p>";
    echo "</div>";
}

echo "</div>";

// Quick Fix Recommendations
if ($failed_tests > 0) {
    echo "<div class='test-section'>";
    echo "<h2>🔧 Quick Fix Recommendations</h2>";
    
    echo "<ol>";
    if (in_array('fail', $test_results)) {
        echo "<li><strong>Missing Files:</strong> Ensure all required files exist in the correct locations</li>";
        echo "<li><strong>WordPress Integration:</strong> Clear WordPress cache and check that the plugin is properly activated</li>";
        echo "<li><strong>File Permissions:</strong> Ensure all files have proper read permissions</li>";
        echo "<li><strong>JavaScript Errors:</strong> Check browser console for JavaScript errors when testing the generator</li>";
    }
    echo "</ol>";
    
    echo "</div>";
}

// Testing Instructions
echo "<div class='test-section'>";
echo "<h2>🧪 Manual Testing Instructions</h2>";
echo "<ol>";
echo "<li><strong>Create a test page:</strong> Create a new WordPress page and add the shortcode <code>[mkcg_authority_hook]</code></li>";
echo "<li><strong>Test basic functionality:</strong> Visit the page and verify the two-panel layout appears</li>";
echo "<li><strong>Test form fields:</strong> Fill in the WHO, WHAT, WHEN, HOW fields in the Authority Hook Builder</li>";
echo "<li><strong>Test save functionality:</strong> Click the 'Save Authority Hook' button and verify it saves without errors</li>";
echo "<li><strong>Test with post_id:</strong> Add <code>?post_id=123</code> to the URL (replace 123 with a valid guest post ID)</li>";
echo "<li><strong>Check browser console:</strong> Open developer tools and look for any JavaScript errors</li>";
echo "</ol>";
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📞 Support Information</h2>";
echo "<p>If you encounter issues:</p>";
echo "<ul>";
echo "<li>Check WordPress error logs for PHP errors</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify that all plugin dependencies are installed and activated</li>";
echo "<li>Ensure WordPress and plugin files have proper permissions</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><em>Test completed on " . date('Y-m-d H:i:s') . "</em></p>";

echo "</body></html>";
?>