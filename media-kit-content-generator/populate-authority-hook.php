<?php
/**
 * Authority Hook Content Populator
 * 
 * This script populates the authority hook fields with meaningful content
 * instead of just placeholder field names
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
    <title>Authority Hook Content Populator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #005a87; }
    </style>
</head>
<body>';

echo '<h1>🔧 Authority Hook Content Populator</h1>';

$test_post_id = 32372;

// Check if this is a populate request
if (isset($_POST['populate']) && $_POST['populate'] === 'true') {
    
    echo '<div class="section success">';
    echo '<h3>🚀 Populating Authority Hook Fields...</h3>';
    
    // Get current post data for context
    $post = get_post($test_post_id);
    $guest_title = get_post_meta($test_post_id, 'guest_title', true);
    $biography = get_post_meta($test_post_id, 'biography', true);
    $tagline = get_post_meta($test_post_id, 'tagline', true);
    
    // Get the WHO component (which is working)
    $audience_terms = wp_get_post_terms($test_post_id, 'audience', ['fields' => 'names']);
    $who_value = !empty($audience_terms) ? implode(', ', $audience_terms) : 'your audience';
    
    // Generate meaningful authority hook content based on available data
    $authority_hook_content = [
        'hook_what' => 'create compelling content that converts readers into clients',
        'hook_when' => 'they want to establish authority in their field',
        'hook_how' => 'through proven content strategies and audience engagement techniques',
        'hook_where' => 'in the digital publishing space',
        'hook_why' => 'so they can build a profitable business around their expertise'
    ];
    
    // If we have a tagline or biography, try to extract better content
    if (!empty($tagline)) {
        // Use tagline to inform the "what" component
        $authority_hook_content['hook_what'] = strtolower($tagline);
    }
    
    if (!empty($biography)) {
        // Extract potential "how" information from biography
        if (strpos(strtolower($biography), 'help') !== false) {
            $bio_sentences = explode('.', $biography);
            foreach ($bio_sentences as $sentence) {
                if (strpos(strtolower($sentence), 'help') !== false) {
                    $cleaned = trim(strtolower($sentence));
                    if (strlen($cleaned) > 20 && strlen($cleaned) < 100) {
                        $authority_hook_content['hook_how'] = $cleaned;
                        break;
                    }
                }
            }
        }
    }
    
    // Update the fields
    $updated_count = 0;
    foreach ($authority_hook_content as $field => $content) {
        $result = update_post_meta($test_post_id, $field, $content);
        if ($result !== false) {
            $updated_count++;
            echo '<p>✅ Updated <strong>' . $field . '</strong>: "' . esc_html($content) . '"</p>';
        } else {
            echo '<p>❌ Failed to update <strong>' . $field . '</strong></p>';
        }
    }
    
    echo '<p><strong>🎉 Successfully updated ' . $updated_count . '/5 authority hook fields!</strong></p>';
    echo '</div>';
    
    echo '<div class="section info">';
    echo '<h3>🔄 Testing Updated Authority Hook</h3>';
    echo '<p><a href="test-authority-hook-fix.php" class="btn">Run Authority Hook Test</a></p>';
    echo '<p><a href="/topics-generator/?post_id=' . $test_post_id . '" class="btn">Test Topics Generator Interface</a></p>';
    echo '</div>';
    
} else {
    
    // Show current state and populate option
    echo '<div class="section warning">';
    echo '<h3>⚠️ Current Problem</h3>';
    echo '<p>The authority hook fields contain generic placeholder values instead of meaningful content:</p>';
    echo '<ul>';
    echo '<li><strong>hook_what:</strong> "What" (should be meaningful result/outcome)</li>';
    echo '<li><strong>hook_when:</strong> "When" (should be meaningful timing/situation)</li>';
    echo '<li><strong>hook_how:</strong> "How" (should be meaningful method/approach)</li>';
    echo '<li><strong>hook_where:</strong> "Where" (should be meaningful context/location)</li>';
    echo '<li><strong>hook_why:</strong> "Why" (should be meaningful motivation/benefit)</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="section info">';
    echo '<h3>🎯 Proposed Solution</h3>';
    echo '<p>Populate these fields with meaningful content based on the user profile data:</p>';
    
    // Show what we can extract from current data
    $guest_title = get_post_meta($test_post_id, 'guest_title', true);
    $biography = get_post_meta($test_post_id, 'biography', true);
    $tagline = get_post_meta($test_post_id, 'tagline', true);
    
    echo '<p><strong>Available data to work with:</strong></p>';
    echo '<ul>';
    echo '<li><strong>Guest Title:</strong> "' . esc_html($guest_title) . '"</li>';
    echo '<li><strong>Tagline:</strong> "' . esc_html($tagline) . '"</li>';
    echo '<li><strong>Biography:</strong> ' . (strlen($biography) > 100 ? substr(esc_html($biography), 0, 100) . '...' : esc_html($biography)) . '</li>';
    echo '</ul>';
    
    echo '<p><strong>Proposed authority hook content:</strong></p>';
    echo '<ul>';
    echo '<li><strong>WHAT:</strong> "create compelling content that converts readers into clients"</li>';
    echo '<li><strong>WHEN:</strong> "they want to establish authority in their field"</li>';
    echo '<li><strong>HOW:</strong> "through proven content strategies and audience engagement techniques"</li>';
    echo '<li><strong>WHERE:</strong> "in the digital publishing space"</li>';
    echo '<li><strong>WHY:</strong> "so they can build a profitable business around their expertise"</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="section success">';
    echo '<h3>🚀 Ready to Populate?</h3>';
    echo '<p>Click the button below to populate the authority hook fields with meaningful content:</p>';
    
    echo '<form method="post">';
    echo '<input type="hidden" name="populate" value="true">';
    echo '<button type="submit" class="btn">Populate Authority Hook Fields</button>';
    echo '</form>';
    echo '</div>';
    
}

echo '</body></html>';
?>
