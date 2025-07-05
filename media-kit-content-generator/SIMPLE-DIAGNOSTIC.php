<?php
/**
 * SIMPLE ROOT FIX DIAGNOSTIC TEST
 * Basic test to check if the clean slate fix is working
 */

echo "<h1>🔍 Simple Root Fix Diagnostic</h1>\n";

// Check if we're in WordPress
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
}

// Test 1: Check if Authority Hook Service file exists
$service_file = __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';
echo "<h2>Test 1: File Existence</h2>\n";
if (file_exists($service_file)) {
    echo "<p>✅ Authority Hook Service file found</p>\n";
    
    // Try to load it
    try {
        require_once $service_file;
        echo "<p>✅ Authority Hook Service loaded successfully</p>\n";
        
        // Check if class exists
        if (class_exists('MKCG_Authority_Hook_Service')) {
            echo "<p>✅ MKCG_Authority_Hook_Service class available</p>\n";
            
            // Test 2: Check the constants
            echo "<h2>Test 2: Default Constants</h2>\n";
            $reflection = new ReflectionClass('MKCG_Authority_Hook_Service');
            
            $default_components = $reflection->getConstant('DEFAULT_COMPONENTS');
            $legacy_components = $reflection->getConstant('LEGACY_DEFAULT_COMPONENTS');
            
            echo "<p><strong>DEFAULT_COMPONENTS:</strong></p>\n";
            echo "<pre>" . print_r($default_components, true) . "</pre>\n";
            
            echo "<p><strong>LEGACY_DEFAULT_COMPONENTS:</strong></p>\n";
            echo "<pre>" . print_r($legacy_components, true) . "</pre>\n";
            
            // Test 3: Check if defaults are empty
            echo "<h2>Test 3: Clean Slate Check</h2>\n";
            $all_empty = true;
            foreach ($default_components as $key => $value) {
                if (!empty($value)) {
                    $all_empty = false;
                    echo "<p>❌ DEFAULT_COMPONENTS['$key'] is not empty: '$value'</p>\n";
                }
            }
            
            if ($all_empty) {
                echo "<p>✅ All DEFAULT_COMPONENTS are empty (clean slate working)</p>\n";
            } else {
                echo "<p>❌ Some DEFAULT_COMPONENTS have values (fix needed)</p>\n";
            }
            
            // Test 4: Create service instance and test
            echo "<h2>Test 4: Service Instance Test</h2>\n";
            try {
                $_GET = []; // Clear GET parameters for clean slate test
                $service = new MKCG_Authority_Hook_Service();
                echo "<p>✅ Service instance created successfully</p>\n";
                
                // Test get_authority_hook_data with clean slate
                $result = $service->get_authority_hook_data(0, true);
                echo "<p><strong>Authority Hook Data Result:</strong></p>\n";
                echo "<pre>" . print_r($result, true) . "</pre>\n";
                
                // Check if components are empty
                $components_empty = true;
                if (isset($result['components'])) {
                    foreach ($result['components'] as $key => $value) {
                        if (!empty($value)) {
                            $components_empty = false;
                            echo "<p>❌ Component '$key' is not empty: '$value'</p>\n";
                        }
                    }
                }
                
                if ($components_empty) {
                    echo "<p>✅ All components returned empty (ROOT FIX working)</p>\n";
                } else {
                    echo "<p>❌ Some components returned with values (ROOT FIX not working)</p>\n";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Error creating service instance: " . $e->getMessage() . "</p>\n";
            }
            
        } else {
            echo "<p>❌ MKCG_Authority_Hook_Service class not found after loading file</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error loading Authority Hook Service: " . $e->getMessage() . "</p>\n";
        echo "<p>Error details: " . $e->getFile() . " line " . $e->getLine() . "</p>\n";
    }
    
} else {
    echo "<p>❌ Authority Hook Service file not found at: $service_file</p>\n";
    echo "<p>Current directory: " . __DIR__ . "</p>\n";
    echo "<p>Directory contents:</p>\n";
    
    if (is_dir(__DIR__ . '/includes')) {
        echo "<ul>\n";
        $files = scandir(__DIR__ . '/includes');
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "<li>includes/$file</li>\n";
                if (is_dir(__DIR__ . "/includes/$file")) {
                    $subfiles = scandir(__DIR__ . "/includes/$file");
                    foreach ($subfiles as $subfile) {
                        if ($subfile !== '.' && $subfile !== '..') {
                            echo "<li>&nbsp;&nbsp;includes/$file/$subfile</li>\n";
                        }
                    }
                }
            }
        }
        echo "</ul>\n";
    } else {
        echo "<p>❌ includes directory not found</p>\n";
    }
}

// Test 5: Quick template check
echo "<h2>Test 5: Template File Check</h2>\n";
$template_file = __DIR__ . '/templates/generators/topics/default.php';
if (file_exists($template_file)) {
    echo "<p>✅ Topics template file found</p>\n";
    
    // Check for our ROOT FIX comments
    $template_content = file_get_contents($template_file);
    if (strpos($template_content, 'ROOT FIX') !== false) {
        echo "<p>✅ ROOT FIX comments found in template</p>\n";
    } else {
        echo "<p>⚠️ ROOT FIX comments not found in template</p>\n";
    }
    
    if (strpos($template_content, 'clean_slate_mode') !== false) {
        echo "<p>✅ clean_slate_mode configuration found in template</p>\n";
    } else {
        echo "<p>❌ clean_slate_mode configuration not found in template</p>\n";
    }
    
} else {
    echo "<p>❌ Topics template file not found at: $template_file</p>\n";
}

// Summary
echo "<h2>🎯 Diagnostic Summary</h2>\n";
echo "<p>This diagnostic checks if the ROOT FIX has been applied correctly.</p>\n";
echo "<p><strong>Key Points:</strong></p>\n";
echo "<ul>\n";
echo "<li>DEFAULT_COMPONENTS should all be empty strings</li>\n";
echo "<li>Service should return empty components when no data exists</li>\n";
echo "<li>Template should have clean_slate_mode configuration</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><strong>How to run this test:</strong></p>\n";
echo "<ol>\n";
echo "<li>Place this file in your plugin root directory (same level as media-kit-content-generator.php)</li>\n";
echo "<li>Access it via browser: http://yoursite.com/wp-content/plugins/media-kit-content-generator/SIMPLE-DIAGNOSTIC.php</li>\n";
echo "<li>Or run via command line: <code>php SIMPLE-DIAGNOSTIC.php</code></li>\n";
echo "</ol>\n";

echo "<p><em>Current file location: " . __FILE__ . "</em></p>\n";
?>
