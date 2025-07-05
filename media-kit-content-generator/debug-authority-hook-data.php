<?php
/**
 * AUTHORITY HOOK DATA DEBUG SCRIPT - FIXED VERSION
 * 
 * Comprehensive investigation of where Authority Hook data is stored and cached
 * Usage: Place in plugin root and access via browser
 */

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Try to load WordPress if not already loaded
if (!defined('ABSPATH')) {
    // Try multiple possible paths to wp-load.php
    $possible_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php', 
        __DIR__ . '/../../wp-load.php',
        __DIR__ . '/../wp-load.php',
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            try {
                require_once($path);
                $wp_loaded = true;
                break;
            } catch (Exception $e) {
                // Continue to next path
            }
        }
    }
    
    if (!$wp_loaded) {
        die('<h1>WordPress Loading Error</h1><p>Could not locate wp-load.php. Tried paths:</p><ul><li>' . implode('</li><li>', $possible_paths) . '</li></ul>');
    }
}

// Simple access check (remove strict admin requirement for debugging)
if (!is_user_logged_in()) {
    die('<h1>Access Denied</h1><p>Please log in to WordPress first.</p>');
}

// Get post ID from URL or default
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 32372;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'investigate';

echo '<!DOCTYPE html>
<html>
<head>
    <title>Authority Hook Data Debug - Post ID: ' . $post_id . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .button { display: inline-block; padding: 8px 15px; margin: 5px; text-decoration: none; border-radius: 4px; color: white; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-info { background: #17a2b8; }
        .btn-success { background: #28a745; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .highlight { background: #ffeb3b; font-weight: bold; }
    </style>
</head>
<body>';

echo '<div class="container">';
echo '<h1>🔍 Authority Hook Data Debug Investigation</h1>';
echo '<p><strong>Post ID:</strong> ' . $post_id . ' | <strong>Action:</strong> ' . $action . '</p>';

// Navigation
echo '<div class="section info">';
echo '<h3>🚀 Quick Actions</h3>';
echo '<a href="?post_id=' . $post_id . '&action=investigate" class="button btn-info">🔍 Investigate Data Sources</a>';
echo '<a href="?post_id=' . $post_id . '&action=clear_cache" class="button btn-warning">🧹 Clear All Cache</a>';
echo '<a href="?post_id=' . $post_id . '&action=clear_post_data" class="button btn-danger">🗑️ Clear Post Data</a>';
echo '<a href="?post_id=' . $post_id . '&action=clear_all" class="button btn-danger">💥 Nuclear Clear All</a>';
echo '</div>';

// Execute actions with error handling
if ($action === 'clear_cache') {
    echo '<div class="section warning">';
    echo '<h3>🧹 CLEARING CACHE...</h3>';
    
    try {
        // Clear WordPress object cache
        wp_cache_flush();
        echo '<p>✅ WordPress object cache cleared</p>';
        
        // Clear any plugin-specific transients
        $transients_cleared = 0;
        global $wpdb;
        
        if ($wpdb) {
            $plugin_transients = $wpdb->get_results(
                "SELECT option_name FROM {$wpdb->options} 
                 WHERE option_name LIKE '_transient_mkcg_%' 
                 OR option_name LIKE '_transient_timeout_mkcg_%'
                 OR option_name LIKE '_transient_topics_%'
                 OR option_name LIKE '_transient_authority_%'"
            );
            
            if ($plugin_transients) {
                foreach ($plugin_transients as $transient) {
                    $key = str_replace(['_transient_', '_transient_timeout_'], '', $transient->option_name);
                    delete_transient($key);
                    $transients_cleared++;
                }
            }
        }
        
        echo '<p>✅ Cleared ' . $transients_cleared . ' plugin transients</p>';
        
        // Clear any Pods cache
        if (function_exists('pods_cache_clear')) {
            pods_cache_clear();
            echo '<p>✅ Pods cache cleared</p>';
        }
        
        echo '<p><strong>🎯 Cache clearing complete. Try refreshing your Topics Generator page.</strong></p>';
    } catch (Exception $e) {
        echo '<p>❌ Error during cache clear: ' . esc_html($e->getMessage()) . '</p>';
    }
    echo '</div>';
}

if ($action === 'clear_post_data') {
    echo '<div class="section error">';
    echo '<h3>🗑️ CLEARING POST DATA...</h3>';
    
    // Get all meta keys for this post
    $meta_keys = get_post_meta($post_id);
    $cleared_count = 0;
    
    // Authority Hook related keys
    $authority_keys = [
        'guest_title', 'hook_who', 'hook_what', 'hook_when', 'hook_how', 'hook_where', 'hook_why',
        'topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5',
        'authority_hook_who', 'authority_hook_what', 'authority_hook_when', 'authority_hook_how'
    ];
    
    foreach ($authority_keys as $key) {
        if (delete_post_meta($post_id, $key)) {
            echo '<p>✅ Deleted meta key: ' . $key . '</p>';
            $cleared_count++;
        }
    }
    
    echo '<p><strong>🎯 Cleared ' . $cleared_count . ' post meta entries.</strong></p>';
    echo '</div>';
}

if ($action === 'clear_all') {
    echo '<div class="section error">';
    echo '<h3>💥 NUCLEAR CLEAR ALL...</h3>';
    
    // Clear cache
    wp_cache_flush();
    echo '<p>✅ WordPress cache cleared</p>';
    
    // Clear post data
    $meta_keys = get_post_meta($post_id);
    foreach ($meta_keys as $key => $values) {
        if (strpos($key, 'hook_') !== false || 
            strpos($key, 'topic_') !== false || 
            strpos($key, 'guest_') !== false ||
            strpos($key, 'authority_') !== false) {
            delete_post_meta($post_id, $key);
            echo '<p>✅ Deleted: ' . $key . '</p>';
        }
    }
    
    // Clear all plugin transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_mkcg_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_mkcg_%'");
    
    echo '<p><strong>💥 NUCLEAR CLEAR COMPLETE. All data should be gone.</strong></p>';
    echo '</div>';
}

// Main investigation
if ($action === 'investigate') {
    
    // 1. WordPress Post Meta Investigation
    echo '<div class="section">';
    echo '<h3>📋 WordPress Post Meta Investigation</h3>';
    
    $all_meta = get_post_meta($post_id);
    $authority_related = [];
    
    echo '<table>';
    echo '<tr><th>Meta Key</th><th>Meta Value</th><th>Authority Related?</th></tr>';
    
    foreach ($all_meta as $key => $values) {
        $is_authority = (strpos($key, 'hook_') !== false || 
                        strpos($key, 'topic_') !== false || 
                        strpos($key, 'guest_') !== false ||
                        strpos($key, 'authority_') !== false ||
                        strpos($key, 'who') !== false ||
                        strpos($key, 'what') !== false);
        
        $class = $is_authority ? 'highlight' : '';
        if ($is_authority) {
            $authority_related[$key] = $values;
        }
        
        echo '<tr class="' . $class . '">';
        echo '<td>' . esc_html($key) . '</td>';
        echo '<td>' . esc_html(substr(print_r($values, true), 0, 100)) . '...</td>';
        echo '<td>' . ($is_authority ? '🎯 YES' : 'No') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    if (!empty($authority_related)) {
        echo '<div class="warning">';
        echo '<h4>⚠️ FOUND AUTHORITY-RELATED DATA IN POST META:</h4>';
        echo '<pre>' . print_r($authority_related, true) . '</pre>';
        echo '</div>';
    } else {
        echo '<div class="success">';
        echo '<h4>✅ No authority-related data found in post meta</h4>';
        echo '</div>';
    }
    echo '</div>';
    
    // 2. Pods Data Investigation
    echo '<div class="section">';
    echo '<h3>🥙 Pods Data Investigation</h3>';
    
    // Check if post is a Pods-managed post type
    $post = get_post($post_id);
    echo '<p><strong>Post Type:</strong> ' . $post->post_type . '</p>';
    
    if (function_exists('pods')) {
        try {
            $pod = pods('guests', $post_id);
            if ($pod && $pod->exists()) {
                echo '<div class="info">';
                echo '<h4>📋 Pods Data Found:</h4>';
                
                // Get all field values
                $pod_fields = $pod->fields();
                echo '<table>';
                echo '<tr><th>Field Name</th><th>Field Value</th><th>Authority Related?</th></tr>';
                
                foreach ($pod_fields as $field_name => $field_data) {
                    $value = $pod->field($field_name);
                    $is_authority = (strpos($field_name, 'hook_') !== false || 
                                    strpos($field_name, 'topic_') !== false || 
                                    strpos($field_name, 'guest_') !== false ||
                                    strpos($field_name, 'who') !== false);
                    
                    $class = $is_authority ? 'highlight' : '';
                    
                    echo '<tr class="' . $class . '">';
                    echo '<td>' . esc_html($field_name) . '</td>';
                    echo '<td>' . esc_html(substr(print_r($value, true), 0, 150)) . '...</td>';
                    echo '<td>' . ($is_authority ? '🎯 YES' : 'No') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="warning">';
                echo '<h4>⚠️ Post exists but no Pods data found</h4>';
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h4>❌ Pods Error: ' . $e->getMessage() . '</h4>';
            echo '</div>';
        }
    } else {
        echo '<div class="warning">';
        echo '<h4>⚠️ Pods function not available</h4>';
        echo '</div>';
    }
    echo '</div>';
    
    // 3. Plugin Service Investigation
    echo '<div class="section">';
    echo '<h3>🔌 Plugin Service Investigation</h3>';
    
    // Check if plugin classes exist
    $plugin_classes = [
        'MKCG_Pods_Service',
        'Enhanced_Topics_Generator', 
        'MKCG_Authority_Hook_Service',
        'Media_Kit_Content_Generator'
    ];
    
    foreach ($plugin_classes as $class) {
        if (class_exists($class)) {
            echo '<p>✅ ' . $class . ' - Available</p>';
            
            // Try to get data using the service
            if ($class === 'MKCG_Pods_Service') {
                try {
                    $service = new MKCG_Pods_Service();
                    $guest_data = $service->get_guest_data($post_id);
                    
                    echo '<div class="info">';
                    echo '<h4>📋 MKCG_Pods_Service Data:</h4>';
                    echo '<pre>' . print_r($guest_data, true) . '</pre>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<p>❌ Error using ' . $class . ': ' . $e->getMessage() . '</p>';
                }
            }
        } else {
            echo '<p>❌ ' . $class . ' - Not Available</p>';
        }
    }
    echo '</div>';
    
    // 4. Transients and Cache Investigation  
    echo '<div class="section">';
    echo '<h3>⚡ Transients and Cache Investigation</h3>';
    
    global $wpdb;
    
    // Check for plugin-related transients
    $transients = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_mkcg_%' 
         OR option_name LIKE '_transient_topics_%'
         OR option_name LIKE '_transient_authority_%'
         OR option_name LIKE '_transient_guest_%'"
    );
    
    if (!empty($transients)) {
        echo '<div class="warning">';
        echo '<h4>⚠️ FOUND CACHED TRANSIENTS:</h4>';
        echo '<table>';
        echo '<tr><th>Transient Key</th><th>Value Preview</th></tr>';
        foreach ($transients as $transient) {
            echo '<tr>';
            echo '<td>' . esc_html($transient->option_name) . '</td>';
            echo '<td>' . esc_html(substr($transient->option_value, 0, 100)) . '...</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="success">';
        echo '<h4>✅ No plugin-related transients found</h4>';
        echo '</div>';
    }
    echo '</div>';
    
    // 5. Database Raw Investigation
    echo '<div class="section">';
    echo '<h3>🗄️ Raw Database Investigation</h3>';
    
    // Direct database query for post meta
    $raw_meta = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} 
         WHERE post_id = %d 
         AND (meta_key LIKE '%hook%' OR meta_key LIKE '%topic%' OR meta_key LIKE '%guest%' OR meta_key LIKE '%authority%')
         ORDER BY meta_key",
        $post_id
    ));
    
    if (!empty($raw_meta)) {
        echo '<div class="error">';
        echo '<h4>🎯 FOUND RAW DATABASE ENTRIES:</h4>';
        echo '<table>';
        echo '<tr><th>Meta Key</th><th>Meta Value</th></tr>';
        foreach ($raw_meta as $meta) {
            echo '<tr>';
            echo '<td class="highlight">' . esc_html($meta->meta_key) . '</td>';
            echo '<td>' . esc_html($meta->meta_value) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<p><strong>🚨 THIS IS WHERE YOUR DATA IS HIDING!</strong></p>';
        echo '</div>';
    } else {
        echo '<div class="success">';
        echo '<h4>✅ No authority-related data in raw database</h4>';
        echo '</div>';
    }
    echo '</div>';
    
    // 6. Plugin Template Data Simulation
    echo '<div class="section">';
    echo '<h3>🎭 Plugin Template Data Simulation</h3>';
    
    // Simulate what the plugin template would generate
    echo '<p>Testing what data the plugin would send to JavaScript...</p>';
    
    // Include plugin files if available
    $plugin_main = dirname(__FILE__) . '/media-kit-content-generator.php';
    if (file_exists($plugin_main)) {
        echo '<p>✅ Plugin main file found</p>';
        
        // Try to simulate the template data generation
        try {
            // Get the plugin instance
            if (function_exists('mkcg_init')) {
                $plugin = mkcg_init();
                
                if (method_exists($plugin, 'get_pods_service')) {
                    $pods_service = $plugin->get_pods_service();
                    if ($pods_service && method_exists($pods_service, 'get_guest_data')) {
                        $simulated_data = $pods_service->get_guest_data($post_id);
                        
                        echo '<div class="warning">';
                        echo '<h4>🎭 SIMULATED TEMPLATE DATA (This is what JavaScript receives):</h4>';
                        echo '<pre>' . print_r($simulated_data, true) . '</pre>';
                        echo '</div>';
                    }
                }
            }
        } catch (Exception $e) {
            echo '<p>❌ Error simulating template data: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p>❌ Plugin main file not found at: ' . $plugin_main . '</p>';
    }
    echo '</div>';
}

// Summary and recommendations
echo '<div class="section info">';
echo '<h3>💡 Recommendations</h3>';
echo '<ol>';
echo '<li><strong>If data found in Post Meta or Pods:</strong> Use "Clear Post Data" to remove it</li>';
echo '<li><strong>If data found in Transients:</strong> Use "Clear Cache" to remove it</li>';
echo '<li><strong>If data persists:</strong> Use "Nuclear Clear All" to remove everything</li>';
echo '<li><strong>After clearing:</strong> Hard refresh your browser (Ctrl+F5) and test the Topics Generator</li>';
echo '</ol>';
echo '</div>';

echo '</div>'; // Close container
echo '</body></html>';
?>
