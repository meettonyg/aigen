<?php
/**
 * Test if the plugin is loading and AJAX is registered
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Check if plugin is active
if (!is_plugin_active('media-kit-content-generator/media-kit-content-generator.php')) {
    die('❌ Plugin is NOT active! Please activate it first.');
}

echo '<h2>Media Kit Content Generator - Plugin Status</h2>';

// Check if the main class exists
if (class_exists('Media_Kit_Content_Generator')) {
    echo '✅ Main plugin class exists<br>';
    
    // Check if instance exists
    $instance = Media_Kit_Content_Generator::get_instance();
    if ($instance) {
        echo '✅ Plugin instance created<br>';
    } else {
        echo '❌ Failed to create plugin instance<br>';
    }
} else {
    echo '❌ Main plugin class NOT found<br>';
}

// Check if AJAX actions are registered
echo '<h3>AJAX Actions Status:</h3>';
$ajax_actions = [
    'wp_ajax_mkcg_save_topics_data',
    'wp_ajax_mkcg_get_topics_data',
    'wp_ajax_mkcg_save_authority_hook',
    'wp_ajax_mkcg_generate_topics',
    'wp_ajax_mkcg_save_topic_field'
];

foreach ($ajax_actions as $action) {
    if (has_action($action)) {
        echo "✅ {$action} is registered<br>";
    } else {
        echo "❌ {$action} is NOT registered<br>";
    }
}

// Check for PHP errors
echo '<h3>PHP Error Check:</h3>';
$error = error_get_last();
if ($error && $error['type'] === E_ERROR) {
    echo '❌ PHP Fatal Error: ' . $error['message'] . '<br>';
    echo 'File: ' . $error['file'] . '<br>';
    echo 'Line: ' . $error['line'] . '<br>';
} else {
    echo '✅ No PHP fatal errors detected<br>';
}

// Test AJAX handler directly
echo '<h3>Direct AJAX Test:</h3>';
if (class_exists('Media_Kit_Content_Generator')) {
    $instance = Media_Kit_Content_Generator::get_instance();
    if (method_exists($instance, 'ajax_save_topics')) {
        echo '✅ ajax_save_topics method exists<br>';
    } else {
        echo '❌ ajax_save_topics method NOT found<br>';
    }
}

echo '<hr>';
echo '<p><strong>If everything shows ✅ above, the plugin is working and AJAX should be registered.</strong></p>';
echo '<p>If not, check the WordPress debug.log for errors.</p>';
