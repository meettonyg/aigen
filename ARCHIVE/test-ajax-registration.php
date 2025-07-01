<?php
/**
 * AJAX Registration Diagnostic Script
 * Check if the AJAX actions are properly registered with WordPress
 */

// Prevent direct access without WordPress
if (!defined('ABSPATH')) {
    // Load WordPress if testing directly
    $wp_load_paths = [
        '../../../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        'wp-load.php'
    ];
    
    $loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $loaded = true;
            break;
        }
    }
    
    if (!$loaded) {
        die('WordPress not found. Please run this test from within WordPress.');
    }
}

echo "<h1>🔍 AJAX Registration Diagnostic</h1>";
echo "<p>Checking if mkcg_save_topics_data action is properly registered...</p>";
echo "<hr>";

// Check if the action is registered
global $wp_filter;

echo "<h2>📋 WordPress AJAX Action Status</h2>";

$actions_to_check = [
    'wp_ajax_mkcg_save_topics_data',
    'wp_ajax_nopriv_mkcg_save_topics_data'
];

foreach ($actions_to_check as $action) {
    if (isset($wp_filter[$action])) {
        echo "<p style='color: green;'>✅ <strong>$action</strong> is registered</p>";
        
        // Show what callbacks are registered
        $callbacks = $wp_filter[$action]->callbacks;
        foreach ($callbacks as $priority => $callback_array) {
            foreach ($callback_array as $callback_id => $callback_info) {
                $callback = $callback_info['function'];
                if (is_array($callback)) {
                    $class_name = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
                    $method_name = $callback[1];
                    echo "<p style='margin-left: 20px; color: #666;'>→ Callback: {$class_name}::{$method_name}() [Priority: $priority]</p>";
                } else {
                    echo "<p style='margin-left: 20px; color: #666;'>→ Callback: $callback [Priority: $priority]</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>$action</strong> is NOT registered</p>";
    }
}

echo "<hr>";

// Check if classes exist
echo "<h2>📦 Class Availability Check</h2>";

$classes_to_check = [
    'Media_Kit_Content_Generator',
    'MKCG_Topics_Generator', 
    'MKCG_Topics_AJAX_Handlers',
    'MKCG_API_Service',
    'MKCG_Formidable_Service',
    'MKCG_Authority_Hook_Service'
];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✅ Class <strong>$class</strong> exists</p>";
        
        // Check if it has the save_topics_data method
        if ($class === 'MKCG_Topics_AJAX_Handlers' && method_exists($class, 'save_topics_data')) {
            echo "<p style='margin-left: 20px; color: green;'>→ save_topics_data() method exists ✅</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Class <strong>$class</strong> does NOT exist</p>";
    }
}

echo "<hr>";

// Check if plugin instance is available
echo "<h2>🎯 Plugin Instance Check</h2>";

if (function_exists('mkcg_init')) {
    echo "<p style='color: green;'>✅ mkcg_init() function exists</p>";
    
    try {
        $plugin_instance = mkcg_init();
        if ($plugin_instance) {
            echo "<p style='color: green;'>✅ Plugin instance created successfully</p>";
            
            // Check service status
            if (method_exists($plugin_instance, 'get_service_status')) {
                $status = $plugin_instance->get_service_status();
                echo "<p><strong>Service Status:</strong></p>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>";
                print_r($status);
                echo "</pre>";
            }
            
            // Check if generators are available
            if (method_exists($plugin_instance, 'get_generator')) {
                $topics_generator = $plugin_instance->get_generator('topics');
                if ($topics_generator) {
                    echo "<p style='color: green;'>✅ Topics Generator instance available</p>";
                } else {
                    echo "<p style='color: red;'>❌ Topics Generator instance NOT available</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Plugin instance creation failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Plugin instance creation failed: " . esc_html($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ mkcg_init() function does NOT exist</p>";
}

echo "<hr>";

// Test direct AJAX call simulation
echo "<h2>🧪 Direct AJAX Call Test</h2>";

if (class_exists('MKCG_Topics_AJAX_Handlers')) {
    echo "<p>Attempting to simulate AJAX call...</p>";
    
    // Simulate $_POST data
    $_POST = [
        'action' => 'mkcg_save_topics_data',
        'entry_id' => '74492',
        'nonce' => wp_create_nonce('mkcg_nonce'),
        'topics' => [
            'topic_1' => 'Test Topic 1',
            'topic_2' => 'Test Topic 2'
        ]
    ];
    
    echo "<p><strong>Simulated POST data:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>";
    print_r($_POST);
    echo "</pre>";
    
    // Try to call the action directly
    try {
        echo "<p>Attempting to call do_action('wp_ajax_mkcg_save_topics_data')...</p>";
        
        // Capture output
        ob_start();
        do_action('wp_ajax_mkcg_save_topics_data');
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "<p style='color: green;'>✅ Action produced output:</p>";
            echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 4px;'>";
            echo htmlspecialchars($output);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Action produced no output</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Direct action call failed: " . esc_html($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Cannot test - MKCG_Topics_AJAX_Handlers class not available</p>";
}

echo "<hr>";

// Check WordPress error log
echo "<h2>📋 Recent Error Log Check</h2>";
echo "<p>Check your WordPress error logs for any PHP fatal errors related to MKCG.</p>";
echo "<p><strong>Common log locations:</strong></p>";
echo "<ul>";
echo "<li>/wp-content/debug.log</li>";
echo "<li>/error_log</li>";
echo "<li>Server error logs</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Diagnostic completed at:</strong> " . current_time('mysql') . "</p>";
?>