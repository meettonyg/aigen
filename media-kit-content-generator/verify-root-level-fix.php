<?php
/**
 * ROOT-LEVEL FIX VERIFICATION SCRIPT
 * 
 * This script verifies that the duplicate file removal fixed the class loading issues.
 * Run this after implementing the root-level fixes.
 * 
 * Place in: media-kit-content-generator/verify-root-level-fix.php
 * Access via: /wp-content/plugins/media-kit-content-generator/verify-root-level-fix.php
 */

// Set proper headers for HTML output
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🎯 ROOT-LEVEL FIX VERIFICATION</h1>\n";
echo "<p><strong>Media Kit Content Generator - Class Loading Test</strong></p>\n";
echo "<hr>\n\n";

// Check if WordPress is available
if (!defined('ABSPATH')) {
    echo "<p>🔧 <strong>Loading WordPress...</strong></p>\n";
    
    // Try to find WordPress
    $wp_paths = [
        '../../../wp-config.php',
        '../../../../wp-config.php',
        '../../../../../wp-config.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_paths as $wp_path) {
        if (file_exists(__DIR__ . '/' . $wp_path)) {
            require_once(__DIR__ . '/' . $wp_path);
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        echo "<p>⚠️ WordPress not found. Running standalone test...</p>\n";
        define('ABSPATH', true);
    }
}

// Define plugin constants if not defined
if (!defined('MKCG_PLUGIN_PATH')) {
    define('MKCG_PLUGIN_PATH', __DIR__ . '/');
    define('MKCG_PLUGIN_URL', '/wp-content/plugins/media-kit-content-generator/');
    define('MKCG_VERSION', '1.0.0');
}

$test_results = [];
$total_tests = 0;
$passed_tests = 0;

function test_result($test_name, $passed, $message = '', $details = '') {
    global $test_results, $total_tests, $passed_tests;
    
    $total_tests++;
    if ($passed) {
        $passed_tests++;
        $status = "✅ PASSED";
        $color = "green";
    } else {
        $status = "❌ FAILED";
        $color = "red";
    }
    
    echo "<p style='color: {$color};'><strong>{$status}:</strong> {$test_name}</p>\n";
    if ($message) {
        echo "<p style='margin-left: 20px; color: #666;'>📝 {$message}</p>\n";
    }
    if ($details) {
        echo "<p style='margin-left: 20px; font-family: monospace; font-size: 12px; color: #888;'>{$details}</p>\n";
    }
    echo "\n";
    
    $test_results[] = [
        'test' => $test_name,
        'status' => $status,
        'message' => $message,
        'details' => $details
    ];
}

echo "<h2>🔍 TEST 1: DUPLICATE FILE REMOVAL VERIFICATION</h2>\n";

// Test 1: Check that duplicate files were removed
$removed_files = [
    'includes/services/enhanced-formidable-service.php' => 'HYPHEN version (should be removed)',
    'includes/generators/enhanced-ajax-handlers.php' => 'HYPHEN version (should be removed)', 
    'includes/generators/enhanced-topics-generator.php' => 'HYPHEN version (should be removed)'
];

foreach ($removed_files as $file_path => $description) {
    $full_path = MKCG_PLUGIN_PATH . $file_path;
    $file_removed = !file_exists($full_path);
    
    test_result(
        "Duplicate removed: {$file_path}",
        $file_removed,
        $file_removed ? "✅ Duplicate successfully removed" : "❌ Duplicate still exists - CLASS CONFLICT!",
        $description
    );
}

echo "<h2>🔍 TEST 2: CORRECT FILES EXIST</h2>\n";

// Test 2: Check that correct files exist
$required_files = [
    'includes/services/enhanced_formidable_service.php' => 'Enhanced_Formidable_Service',
    'includes/generators/enhanced_topics_generator.php' => 'Enhanced_Topics_Generator',
    'includes/generators/enhanced_ajax_handlers.php' => 'Enhanced_AJAX_Handlers',
    'includes/services/class-mkcg-api-service.php' => 'MKCG_API_Service',
    'includes/services/class-mkcg-config.php' => 'MKCG_Config'
];

foreach ($required_files as $file_path => $expected_class) {
    $full_path = MKCG_PLUGIN_PATH . $file_path;
    $file_exists = file_exists($full_path);
    $file_readable = $file_exists ? is_readable($full_path) : false;
    
    test_result(
        "Required file exists: {$file_path}",
        $file_exists && $file_readable,
        $file_exists ? 
            ($file_readable ? "File found and readable" : "File found but not readable") : 
            "Required file missing",
        "Expected class: {$expected_class}"
    );
}

echo "<h2>🔍 TEST 3: CLASS LOADING</h2>\n";

// Test 3: Try to load classes
$class_loading_success = true;
foreach ($required_files as $file_path => $expected_class) {
    $full_path = MKCG_PLUGIN_PATH . $file_path;
    
    if (file_exists($full_path)) {
        try {
            // Check if class already exists
            $already_exists = class_exists($expected_class);
            
            if (!$already_exists) {
                require_once $full_path;
            }
            
            $class_exists_now = class_exists($expected_class);
            
            test_result(
                "Class loaded: {$expected_class}",
                $class_exists_now,
                $class_exists_now ? 
                    ($already_exists ? "Class was already loaded" : "Class loaded successfully") :
                    "Class not found after file inclusion",
                "File: {$file_path}"
            );
            
            if (!$class_exists_now) {
                $class_loading_success = false;
            }
            
        } catch (Exception $e) {
            test_result(
                "Class loaded: {$expected_class}",
                false,
                "Exception during loading: " . $e->getMessage(),
                $e->getFile() . ':' . $e->getLine()
            );
            $class_loading_success = false;
        }
    } else {
        test_result(
            "Class loaded: {$expected_class}",
            false,
            "Cannot load - file does not exist"
        );
        $class_loading_success = false;
    }
}

echo "<h2>🔍 TEST 4: PLUGIN INITIALIZATION</h2>\n";

// Test 4: Try to initialize the plugin (if WordPress is available)
if (function_exists('plugin_dir_path') && $class_loading_success) {
    try {
        // Try to load the main plugin file
        if (!class_exists('Media_Kit_Content_Generator')) {
            require_once MKCG_PLUGIN_PATH . 'media-kit-content-generator.php';
        }
        
        $plugin_class_exists = class_exists('Media_Kit_Content_Generator');
        test_result(
            "Main plugin class loaded",
            $plugin_class_exists,
            $plugin_class_exists ? "Media_Kit_Content_Generator class found" : "Plugin class not found"
        );
        
        if ($plugin_class_exists) {
            // Try to get plugin instance
            $plugin_instance = Media_Kit_Content_Generator::get_instance();
            $instance_created = is_object($plugin_instance);
            
            test_result(
                "Plugin instance created",
                $instance_created,
                $instance_created ? "Plugin singleton instance created" : "Failed to create plugin instance"
            );
        }
        
    } catch (Exception $e) {
        test_result(
            "Plugin initialization",
            false,
            "Exception during plugin initialization: " . $e->getMessage(),
            $e->getFile() . ':' . $e->getLine()
        );
    }
} else {
    test_result(
        "Plugin initialization",
        false,
        "Skipped - WordPress not available or class loading failed"
    );
}

echo "<h2>🔍 TEST 5: METHOD VERIFICATION</h2>\n";

// Test 5: Check that required methods exist
if (class_exists('Enhanced_Formidable_Service')) {
    $formidable_service = new Enhanced_Formidable_Service();
    $required_methods = ['save_entry_data', 'get_field_value', 'get_entry_data'];
    
    foreach ($required_methods as $method) {
        $method_exists = method_exists($formidable_service, $method);
        test_result(
            "Method exists: Enhanced_Formidable_Service::{$method}",
            $method_exists,
            $method_exists ? "Method found in class" : "Required method missing"
        );
    }
} else {
    test_result(
        "Enhanced_Formidable_Service methods",
        false,
        "Cannot test methods - class not available"
    );
}

if (class_exists('Enhanced_Topics_Generator')) {
    // We need services to instantiate this
    if (class_exists('MKCG_API_Service') && class_exists('Enhanced_Formidable_Service')) {
        try {
            $api_service = new MKCG_API_Service();
            $formidable_service = new Enhanced_Formidable_Service();
            $topics_generator = new Enhanced_Topics_Generator($api_service, $formidable_service);
            
            $required_methods = ['get_template_data', 'generate_topics', 'save_topics'];
            foreach ($required_methods as $method) {
                $method_exists = method_exists($topics_generator, $method);
                test_result(
                    "Method exists: Enhanced_Topics_Generator::{$method}",
                    $method_exists,
                    $method_exists ? "Method found in class" : "Required method missing"
                );
            }
        } catch (Exception $e) {
            test_result(
                "Enhanced_Topics_Generator instantiation",
                false,
                "Cannot instantiate generator: " . $e->getMessage()
            );
        }
    } else {
        test_result(
            "Enhanced_Topics_Generator methods",
            false,
            "Cannot test methods - dependencies not available"
        );
    }
} else {
    test_result(
        "Enhanced_Topics_Generator methods",
        false,
        "Cannot test methods - class not available"
    );
}

echo "<hr>\n";
echo "<h2>📊 SUMMARY</h2>\n";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><td><strong>Total Tests:</strong></td><td>{$total_tests}</td></tr>\n";
echo "<tr><td><strong>Passed:</strong></td><td style='color: green;'>{$passed_tests}</td></tr>\n";
echo "<tr><td><strong>Failed:</strong></td><td style='color: red;'>" . ($total_tests - $passed_tests) . "</td></tr>\n";
echo "<tr><td><strong>Success Rate:</strong></td><td>" . round(($passed_tests / $total_tests) * 100, 1) . "%</td></tr>\n";
echo "</table>\n\n";

if ($passed_tests === $total_tests) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>🎉 ALL TESTS PASSED! ROOT-LEVEL FIX SUCCESSFUL</h3>\n";
    echo "<ul>\n";
    echo "<li>✅ Duplicate files successfully removed</li>\n";
    echo "<li>✅ Enhanced classes loading correctly</li>\n";
    echo "<li>✅ No more class loading conflicts</li>\n";
    echo "<li>✅ Plugin architecture is now simplified and working</li>\n";
    echo "</ul>\n";
    echo "<p><strong>🚀 The simplified system should now work without the 'Class not found' issues shown in your test results!</strong></p>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>⚠️ SOME TESTS FAILED - ADDITIONAL FIXES NEEDED</h3>\n";
    echo "<p>Review the failed tests above to identify remaining issues.</p>\n";
    echo "</div>\n";
}

echo "<h3>📋 NEXT STEPS</h3>\n";
echo "<ol>\n";
echo "<li><strong>Run the original test:</strong> Go to <code>/wp-content/plugins/media-kit-content-generator/test-simplified-system.php</code></li>\n";
echo "<li><strong>Check the logs:</strong> Look for 'MKCG:' entries in your error logs</li>\n";
echo "<li><strong>Verify functionality:</strong> Test the Topics Generator on your live site</li>\n";
echo "</ol>\n";

echo "<hr>\n";
echo "<p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>\n";
