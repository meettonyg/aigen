<?php
/**
 * Quick Authority Hook Debug Test
 * 
 * This script runs the enhanced debugging to see exactly what's in the authority hook fields
 */

// WordPress environment setup
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php'
    ];
    
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
    
    if (!defined('ABSPATH')) {
        die('WordPress not found. Please run this from WordPress directory or adjust paths.');
    }
}

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo '<!DOCTYPE html>
<html>
<head>
    <title>Quick Authority Hook Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>';

echo '<h1>🔍 Quick Authority Hook Debug Test</h1>';

$test_post_id = 32372;

echo '<div class="debug-section info">';
echo '<h3>🎯 Testing Authority Hook Fields for Post ' . $test_post_id . '</h3>';

// Load the enhanced Pods service with debugging
if (class_exists('MKCG_Pods_Service')) {
    echo '<p><strong>✅ MKCG_Pods_Service found - running enhanced debugging...</strong></p>';
    
    // Enable error logging to capture debug messages
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/debug.log');
    
    $pods_service = new MKCG_Pods_Service();
    
    echo '<p><strong>📊 Raw Post Meta Analysis:</strong></p>';
    
    // Show ALL meta fields for this post
    $all_meta = get_post_meta($test_post_id);
    echo '<pre style="max-height: 200px; overflow-y: auto;">';
    echo 'Total meta fields: ' . count($all_meta) . "\n";
    echo 'All meta keys: ' . implode(', ', array_keys($all_meta)) . "\n\n";
    
    // Show hook-specific fields
    $hook_fields = [];
    foreach ($all_meta as $key => $value) {
        if (strpos(strtolower($key), 'hook') !== false) {
            $hook_fields[$key] = is_array($value) ? $value[0] : $value;
        }
    }
    
    echo "Hook-related fields found:\n";
    foreach ($hook_fields as $key => $value) {
        echo "- {$key}: '" . esc_html($value) . "'\n";
    }
    echo '</pre>';
    
    echo '<p><strong>🔬 Running Enhanced Authority Hook Analysis:</strong></p>';
    
    // Clear debug log before test
    file_put_contents(__DIR__ . '/debug.log', '');
    
    // Run the enhanced authority hook component loading
    $authority_hook_components = $pods_service->get_authority_hook_components($test_post_id);
    
    echo '<div class="success">';
    echo '<p><strong>✅ Authority Hook Components Retrieved:</strong></p>';
    echo '<ul>';
    foreach ($authority_hook_components as $key => $value) {
        echo '<li><strong>' . strtoupper($key) . ':</strong> "' . esc_html($value) . '"</li>';
    }
    echo '</ul>';
    echo '</div>';
    
    // Show debug log contents
    $debug_log = file_get_contents(__DIR__ . '/debug.log');
    if ($debug_log) {
        echo '<p><strong>📝 Debug Log Output:</strong></p>';
        echo '<pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;">';
        echo esc_html($debug_log);
        echo '</pre>';
    }
    
} else {
    echo '<div class="error">';
    echo '<p><strong>❌ MKCG_Pods_Service class not found</strong></p>';
    echo '</div>';
}

echo '</div>';

// Direct field checks
echo '<div class="debug-section warning">';
echo '<h3>🔍 Direct Field Value Checks</h3>';

$direct_fields = [
    'hook_what' => get_post_meta($test_post_id, 'hook_what', true),
    'hook_when' => get_post_meta($test_post_id, 'hook_when', true),
    'hook_how' => get_post_meta($test_post_id, 'hook_how', true),
    'hook_where' => get_post_meta($test_post_id, 'hook_where', true),
    'hook_why' => get_post_meta($test_post_id, 'hook_why', true),
];

echo '<p><strong>Direct WordPress get_post_meta() results:</strong></p>';
echo '<ul>';
foreach ($direct_fields as $field => $value) {
    $display_value = empty($value) ? 'EMPTY' : '"' . esc_html($value) . '"';
    $status = empty($value) ? '❌' : '✅';
    echo '<li>' . $status . ' <strong>' . $field . ':</strong> ' . $display_value . '</li>';
}
echo '</ul>';

echo '</div>';

echo '<div class="debug-section info">';
echo '<h3>💡 Analysis & Next Steps</h3>';

$empty_count = count(array_filter($direct_fields, function($value) { return empty($value); }));

if ($empty_count === count($direct_fields)) {
    echo '<div class="warning">';
    echo '<p><strong>⚠️ ALL authority hook fields are empty!</strong></p>';
    echo '<p>This explains why you\'re seeing generic field names. The fields exist in the database but have no content.</p>';
    echo '<p><strong>Recommendation:</strong> You need to populate these fields with actual authority hook content:</p>';
    echo '<ul>';
    echo '<li><strong>hook_what:</strong> What result do you help them achieve?</li>';
    echo '<li><strong>hook_when:</strong> When do they need this help?</li>';
    echo '<li><strong>hook_how:</strong> How do you help them achieve this?</li>';
    echo '</ul>';
    echo '</div>';
} else {
    echo '<div class="success">';
    echo '<p><strong>✅ Some authority hook fields have content!</strong></p>';
    echo '<p>This suggests the data extraction is working but some fields might need attention.</p>';
    echo '</div>';
}

echo '</div>';

echo '</body></html>';
?>
