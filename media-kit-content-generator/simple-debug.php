<?php
/**
 * SIMPLE AUTHORITY HOOK DEBUG - MINIMAL VERSION
 * If the main debug script fails, use this one
 */

// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load WordPress
$wp_paths = [
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../wp-load.php',
    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'
];

$loaded = false;
foreach ($wp_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    die('WordPress not found. Check paths.');
}

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 32372;
$action = isset($_GET['action']) ? $_GET['action'] : 'show';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Debug - Post <?php echo $post_id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
        .button { padding: 8px 15px; margin: 5px; text-decoration: none; background: #007cba; color: white; border-radius: 4px; }
        .danger { background: #dc3545; }
    </style>
</head>
<body>

<h1>🔍 Simple Authority Hook Debug</h1>
<p><strong>Post ID:</strong> <?php echo $post_id; ?></p>

<div class="box">
    <a href="?post_id=<?php echo $post_id; ?>&action=show" class="button">📋 Show Data</a>
    <a href="?post_id=<?php echo $post_id; ?>&action=clear" class="button danger">🗑️ Clear Data</a>
</div>

<?php

if ($action === 'clear') {
    echo '<div class="box error">';
    echo '<h3>🗑️ CLEARING DATA...</h3>';
    
    // Clear post meta
    $meta_keys = ['guest_title', 'hook_who', 'hook_what', 'hook_when', 'hook_how', 'topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5'];
    
    foreach ($meta_keys as $key) {
        if (delete_post_meta($post_id, $key)) {
            echo "<p>✅ Deleted: $key</p>";
        }
    }
    
    // Clear cache
    wp_cache_flush();
    echo '<p>✅ Cache cleared</p>';
    
    echo '<p><strong>✅ DONE! Refresh your Topics Generator page.</strong></p>';
    echo '</div>';
}

if ($action === 'show') {
    echo '<div class="box">';
    echo '<h3>📋 POST META DATA:</h3>';
    
    $all_meta = get_post_meta($post_id);
    $found_authority = false;
    
    foreach ($all_meta as $key => $values) {
        if (strpos($key, 'hook_') !== false || 
            strpos($key, 'topic_') !== false || 
            strpos($key, 'guest_') !== false ||
            strpos($key, 'authority_') !== false) {
            
            $found_authority = true;
            echo '<div class="warning">';
            echo '<strong>' . esc_html($key) . ':</strong> ' . esc_html(print_r($values, true));
            echo '</div>';
        }
    }
    
    if (!$found_authority) {
        echo '<div class="success">';
        echo '<p>✅ No authority hook data found in post meta!</p>';
        echo '</div>';
    } else {
        echo '<div class="error">';
        echo '<p>🎯 <strong>FOUND AUTHORITY DATA ABOVE!</strong> This is why your fields are populated.</p>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Check if post exists
    $post = get_post($post_id);
    if ($post) {
        echo '<div class="box">';
        echo '<h3>📄 POST INFO:</h3>';
        echo '<p><strong>Title:</strong> ' . esc_html($post->post_title) . '</p>';
        echo '<p><strong>Type:</strong> ' . esc_html($post->post_type) . '</p>';
        echo '<p><strong>Status:</strong> ' . esc_html($post->post_status) . '</p>';
        echo '</div>';
    }
}

?>

</body>
</html>
