<?php
/**
 * Debug the 500 error in AJAX handler
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('../../../../wp-load.php');

echo "<h2>AJAX Handler Debug</h2>";

// Test 1: Check if main class exists
if (class_exists('Media_Kit_Content_Generator')) {
    echo "✅ Main class exists<br>";
    
    $instance = Media_Kit_Content_Generator::get_instance();
    if ($instance) {
        echo "✅ Instance created<br>";
        
        // Test the ensure_ajax_handlers method
        try {
            // Use reflection to call private method
            $reflection = new ReflectionClass($instance);
            $method = $reflection->getMethod('ensure_ajax_handlers');
            $method->setAccessible(true);
            
            echo "<h3>Testing ensure_ajax_handlers():</h3>";
            $method->invoke($instance);
            echo "✅ ensure_ajax_handlers() executed without errors<br>";
            
        } catch (Exception $e) {
            echo "❌ Error in ensure_ajax_handlers(): " . $e->getMessage() . "<br>";
            echo "File: " . $e->getFile() . "<br>";
            echo "Line: " . $e->getLine() . "<br>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        // Check if services exist
        echo "<h3>Service Status:</h3>";
        
        // Check Pods service
        try {
            if (class_exists('MKCG_Pods_Service')) {
                echo "✅ MKCG_Pods_Service class exists<br>";
                $pods = new MKCG_Pods_Service();
                echo "✅ Pods service instantiated<br>";
            } else {
                echo "❌ MKCG_Pods_Service class NOT found<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error creating Pods service: " . $e->getMessage() . "<br>";
        }
        
        // Check Authority Hook service
        try {
            if (class_exists('MKCG_Authority_Hook_Service')) {
                echo "✅ MKCG_Authority_Hook_Service class exists<br>";
                $auth = new MKCG_Authority_Hook_Service();
                echo "✅ Authority Hook service instantiated<br>";
            } else {
                echo "❌ MKCG_Authority_Hook_Service class NOT found<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error creating Authority Hook service: " . $e->getMessage() . "<br>";
        }
        
        // Check AJAX handlers
        try {
            if (class_exists('Enhanced_AJAX_Handlers')) {
                echo "✅ Enhanced_AJAX_Handlers class exists<br>";
            } else {
                echo "❌ Enhanced_AJAX_Handlers class NOT found<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error checking AJAX handlers: " . $e->getMessage() . "<br>";
        }
        
    }
} else {
    echo "❌ Media_Kit_Content_Generator class NOT found<br>";
}

// Check file paths
echo "<h3>File Path Check:</h3>";
echo "Plugin path: " . (defined('MKCG_PLUGIN_PATH') ? MKCG_PLUGIN_PATH : 'NOT DEFINED') . "<br>";

$files_to_check = [
    'includes/services/class-mkcg-pods-service.php',
    'includes/services/class-mkcg-authority-hook-service.php',
    'includes/generators/enhanced_ajax_handlers.php'
];

foreach ($files_to_check as $file) {
    $full_path = MKCG_PLUGIN_PATH . $file;
    if (file_exists($full_path)) {
        echo "✅ Found: $file<br>";
    } else {
        echo "❌ Missing: $file<br>";
    }
}

echo "<hr>";
echo "<p>Check WordPress debug.log for the actual 500 error details.</p>";
