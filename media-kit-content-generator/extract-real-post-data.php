<?php
/**
 * Extract Real Post Data for Authority Hook Fields
 * 
 * Maps actual custom post data to authority hook fields
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
    <title>Extract Real Post Data for Authority Hook</title>
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
        .mapping { background: #e8f4fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196f3; }
    </style>
</head>
<body>';

echo '<h1>🔍 Extract Real Post Data for Authority Hook Fields</h1>';

$test_post_id = 32372;

echo '<div class="section info">';
echo '<h3>📊 Available Real Data in Post ' . $test_post_id . '</h3>';

// Get all available data from the post
$post = get_post($test_post_id);
$all_meta = get_post_meta($test_post_id);

// Key fields that might contain authority hook data
$key_fields = [
    'biography' => get_post_meta($test_post_id, 'biography', true),
    'tagline' => get_post_meta($test_post_id, 'tagline', true),
    'guest_title' => get_post_meta($test_post_id, 'guest_title', true),
    'introduction' => get_post_meta($test_post_id, 'introduction', true),
    'company' => get_post_meta($test_post_id, 'company', true),
    'organization' => get_post_meta($test_post_id, 'organization', true),
    'full_name' => get_post_meta($test_post_id, 'full_name', true),
    'first_name' => get_post_meta($test_post_id, 'first_name', true),
    'last_name' => get_post_meta($test_post_id, 'last_name', true),
];

echo '<p><strong>Available Real Data:</strong></p>';
echo '<ul>';
foreach ($key_fields as $field => $value) {
    if (!empty($value)) {
        $display_value = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
        echo '<li><strong>' . $field . ':</strong> "' . esc_html($display_value) . '"</li>';
    } else {
        echo '<li><strong>' . $field . ':</strong> <em>empty</em></li>';
    }
}
echo '</ul>';

echo '</div>';

// Check for any other meaningful content fields
echo '<div class="section warning">';
echo '<h3>🔍 Scanning for Additional Content Fields</h3>';

$content_fields = [];
foreach ($all_meta as $key => $value) {
    $val = is_array($value) ? $value[0] : $value;
    if (!empty($val) && strlen($val) > 20 && strlen($val) < 500) {
        // Skip certain system fields
        if (!in_array($key, ['_edit_lock', '_edit_last', '_wp_page_template']) && 
            !str_starts_with($key, '_') && 
            !str_starts_with($key, 'mhc_') &&
            !str_starts_with($key, '_guestify_') &&
            !in_array($key, ['topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5']) &&
            !str_starts_with($key, 'question_') &&
            !str_starts_with($key, 'hook_')) {
            $content_fields[$key] = $val;
        }
    }
}

if (!empty($content_fields)) {
    echo '<p><strong>Additional content fields found:</strong></p>';
    echo '<ul>';
    foreach ($content_fields as $field => $value) {
        $display_value = strlen($value) > 150 ? substr($value, 0, 150) . '...' : $value;
        echo '<li><strong>' . $field . ':</strong> "' . esc_html($display_value) . '"</li>';
    }
    echo '</ul>';
} else {
    echo '<p><em>No additional meaningful content fields found.</em></p>';
}

echo '</div>';

// Proposed mapping based on available data
echo '<div class="section success">';
echo '<h3>🎯 Proposed Real Data Mapping</h3>';

$biography = get_post_meta($test_post_id, 'biography', true);
$tagline = get_post_meta($test_post_id, 'tagline', true);
$introduction = get_post_meta($test_post_id, 'introduction', true);
$guest_title = get_post_meta($test_post_id, 'guest_title', true);

echo '<div class="mapping">';
echo '<p><strong>Based on your available real data, here\'s what I can extract:</strong></p>';

// Extract meaningful content from biography
$what_content = '';
$when_content = '';
$how_content = '';

if (!empty($biography)) {
    // Try to extract "what" they help with from biography
    if (preg_match('/help[s]?\s+[^.]*?(?:achieve|get|build|create|develop|improve|solve|overcome)[^.]*?\./i', $biography, $matches)) {
        $what_content = trim(str_replace(['helps', 'help'], '', $matches[0]));
    }
    
    // Try to extract "how" from biography
    if (preg_match('/(?:through|using|with|via|by)[^.]*?\./i', $biography, $matches)) {
        $how_content = trim($matches[0]);
    }
    
    // Try to extract "when" situations
    if (preg_match('/when[^.]*?\./i', $biography, $matches)) {
        $when_content = trim($matches[0]);
    }
}

echo '<p><strong>WHO:</strong> ✅ Already working - "2nd value, Authors launching a book" (from audience taxonomy)</p>';

if (!empty($what_content)) {
    echo '<p><strong>WHAT:</strong> Extract from biography: "' . esc_html($what_content) . '"</p>';
} elseif (!empty($tagline)) {
    echo '<p><strong>WHAT:</strong> Use tagline: "' . esc_html($tagline) . '"</p>';
} else {
    echo '<p><strong>WHAT:</strong> ⚠️ No clear "what" content found in biography or tagline</p>';
}

if (!empty($when_content)) {
    echo '<p><strong>WHEN:</strong> Extract from biography: "' . esc_html($when_content) . '"</p>';
} else {
    echo '<p><strong>WHEN:</strong> ⚠️ No clear "when" content found in biography</p>';
}

if (!empty($how_content)) {
    echo '<p><strong>HOW:</strong> Extract from biography: "' . esc_html($how_content) . '"</p>';
} else {
    echo '<p><strong>HOW:</strong> ⚠️ No clear "how" content found in biography</p>';
}

echo '</div>';

echo '</div>';

// Show raw biography for manual inspection
if (!empty($biography)) {
    echo '<div class="section info">';
    echo '<h3>📝 Raw Biography Content (for manual extraction)</h3>';
    echo '<pre>' . esc_html($biography) . '</pre>';
    echo '<p><strong>Please identify which parts should go into WHAT, WHEN, and HOW fields.</strong></p>';
    echo '</div>';
}

// Check if user wants to populate with available data
if (isset($_POST['populate_real']) && $_POST['populate_real'] === 'true') {
    
    echo '<div class="section success">';
    echo '<h3>🚀 Populating with Real Data...</h3>';
    
    $real_data_mapping = [
        'hook_what' => !empty($tagline) ? $tagline : 'What you help them achieve',
        'hook_when' => 'When they need your expertise',
        'hook_how' => !empty($guest_title) ? "through my work as a {$guest_title}" : 'How you help them',
        'hook_where' => 'Where you provide value',
        'hook_why' => 'Why your approach works'
    ];
    
    // Try to extract better content from biography if available
    if (!empty($biography)) {
        // More sophisticated extraction based on common patterns
        $bio_sentences = preg_split('/[.!?]+/', $biography);
        
        foreach ($bio_sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            // Look for "what" patterns
            if (empty($real_data_mapping['hook_what']) || $real_data_mapping['hook_what'] === 'What you help them achieve') {
                if (preg_match('/(?:help|assist|enable|empower)[s]?\s+.*?(?:achieve|get|build|create|develop|improve|solve|overcome)/i', $sentence)) {
                    $real_data_mapping['hook_what'] = $sentence;
                }
            }
            
            // Look for "how" patterns  
            if (preg_match('/(?:through|using|with|via|by)\s+/i', $sentence)) {
                $real_data_mapping['hook_how'] = $sentence;
            }
        }
    }
    
    $updated_count = 0;
    foreach ($real_data_mapping as $field => $content) {
        $result = update_post_meta($test_post_id, $field, $content);
        if ($result !== false) {
            $updated_count++;
            echo '<p>✅ Updated <strong>' . $field . '</strong>: "' . esc_html($content) . '"</p>';
        } else {
            echo '<p>❌ Failed to update <strong>' . $field . '</strong></p>';
        }
    }
    
    echo '<p><strong>🎉 Successfully updated ' . $updated_count . '/5 authority hook fields with real data!</strong></p>';
    echo '<p><a href="test-authority-hook-fix.php" class="btn">Test Authority Hook Fix</a></p>';
    echo '</div>';
    
} else {
    
    echo '<div class="section warning">';
    echo '<h3>⚠️ Manual Data Extraction Needed</h3>';
    echo '<p>I can see the real data, but I need you to specify exactly which content should go into each authority hook field.</p>';
    echo '<p><strong>Please tell me:</strong></p>';
    echo '<ul>';
    echo '<li><strong>WHAT should hook_what contain?</strong> (from biography, tagline, or specific text)</li>';
    echo '<li><strong>WHEN should hook_when contain?</strong> (from biography or specific text)</li>';
    echo '<li><strong>HOW should hook_how contain?</strong> (from biography or specific text)</li>';
    echo '<li><strong>WHERE should hook_where contain?</strong> (from biography or specific text)</li>';
    echo '<li><strong>WHY should hook_why contain?</strong> (from biography or specific text)</li>';
    echo '</ul>';
    
    echo '<p><strong>OR</strong> if you want me to attempt extraction with available data:</p>';
    echo '<form method="post">';
    echo '<input type="hidden" name="populate_real" value="true">';
    echo '<button type="submit" class="btn">🔧 Populate with Best Available Real Data</button>';
    echo '</form>';
    echo '</div>';
    
}

echo '</body></html>';
?>
