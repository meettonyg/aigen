<?php
/**
 * WHO Component Fix Test
 * Tests the specific fix for retrieving audience taxonomy data
 */

// Load WordPress if not already loaded
if (!defined('ABSPATH')) {
    $wp_load_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php', 
        __DIR__ . '/../../wp-load.php',
        __DIR__ . '/../wp-load.php',
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $wp_load_path) {
        if (file_exists($wp_load_path)) {
            require_once $wp_load_path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        echo "❌ WordPress not found\n";
        exit;
    }
}

// Ensure plugin constants are defined
if (!defined('MKCG_PLUGIN_PATH')) {
    define('MKCG_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

echo "<h1>🧪 WHO Component Fix Test</h1>\n";

// Test with your specific post ID that has the issue
$test_post_id = 32372; // Update this to your test post ID

echo "<h2>Testing Post ID: {$test_post_id}</h2>\n";

// Test 1: Check if post exists and is correct type
$post = get_post($test_post_id);
if (!$post) {
    echo "❌ Post {$test_post_id} does not exist\n";
    exit;
}

echo "✅ Post exists: {$post->post_title} (Type: {$post->post_type})\n";

// Test 2: Direct taxonomy check (what Gemini's fix targets)
echo "<h3>Direct Taxonomy Test</h3>\n";

// Clear cache first (as the fix does)
wp_cache_delete($test_post_id, 'audience_relationships');

$audience_terms = wp_get_post_terms($test_post_id, 'audience', ['fields' => 'names']);

if (is_wp_error($audience_terms)) {
    echo "❌ WP_Error: " . $audience_terms->get_error_message() . "\n";
} elseif (!empty($audience_terms)) {
    echo "✅ SUCCESS: Found audience terms: " . implode(', ', $audience_terms) . "\n";
    echo "📊 Total terms found: " . count($audience_terms) . "\n";
} else {
    echo "⚠️ No audience terms found\n";
}

// Test 3: Check guest_title fallback
echo "<h3>Fallback Test (guest_title)</h3>\n";
$guest_title = get_post_meta($test_post_id, 'guest_title', true);
if (!empty($guest_title)) {
    echo "✅ guest_title fallback available: {$guest_title}\n";
} else {
    echo "⚠️ No guest_title meta found\n";
}

// Test 4: Test the actual Pods service with the fix
echo "<h3>Pods Service Test (With Fix)</h3>\n";

if (class_exists('MKCG_Pods_Service')) {
    $pods_service = new MKCG_Pods_Service();
    
    // Test the specific method that was fixed
    $auth_components = $pods_service->get_authority_hook_components($test_post_id);
    
    echo "🔍 Authority Hook Components:\n";
    echo "<ul>\n";
    foreach ($auth_components as $key => $value) {
        $status = ($key === 'who' && $value !== 'your audience') ? '✅' : '📝';
        echo "<li>{$status} <strong>{$key}:</strong> {$value}</li>\n";
    }
    echo "</ul>\n";
    
    // Highlight the WHO component specifically
    if ($auth_components['who'] !== 'your audience') {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
        echo "🎉 <strong>SUCCESS!</strong> WHO component retrieved successfully: <strong>{$auth_components['who']}</strong>\n";
        echo "</div>\n";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
        echo "⚠️ <strong>ISSUE:</strong> WHO component still showing default value. Check if audience taxonomy is properly set.\n";
        echo "</div>\n";
    }
    
} else {
    echo "❌ MKCG_Pods_Service class not found\n";
}

// Test 5: Raw taxonomy data inspection
echo "<h3>Raw Taxonomy Data Inspection</h3>\n";

// Check all terms assigned to this post
$all_terms = wp_get_post_terms($test_post_id, '', ['fields' => 'all']);
if (!empty($all_terms)) {
    echo "📋 All taxonomies for this post:\n<ul>\n";
    foreach ($all_terms as $term) {
        $highlight = ($term->taxonomy === 'audience') ? 'style="background-color: yellow;"' : '';
        echo "<li {$highlight}><strong>{$term->taxonomy}:</strong> {$term->name} (ID: {$term->term_id})</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "⚠️ No taxonomy terms found for this post\n";
}

// Test 6: Check if audience taxonomy exists
echo "<h3>Taxonomy Registration Check</h3>\n";
if (taxonomy_exists('audience')) {
    echo "✅ 'audience' taxonomy is registered\n";
    
    // Get taxonomy details
    $tax_object = get_taxonomy('audience');
    if ($tax_object && in_array('guests', $tax_object->object_type)) {
        echo "✅ 'audience' taxonomy is associated with 'guests' post type\n";
    } else {
        echo "⚠️ 'audience' taxonomy may not be properly associated with 'guests' post type\n";
    }
} else {
    echo "❌ 'audience' taxonomy is not registered\n";
}

echo "<h2>🎯 Summary</h2>\n";
echo "<p>This test validates Gemini's fix for the WHO component retrieval. If you see 'SUCCESS!' above, the fix is working correctly.</p>\n";
echo "<p>If issues persist, check:</p>\n";
echo "<ul>\n";
echo "<li>Audience taxonomy is properly registered</li>\n";
echo "<li>Post {$test_post_id} has audience terms assigned</li>\n";  
echo "<li>WordPress cache is cleared</li>\n";
echo "</ul>\n";
?>
