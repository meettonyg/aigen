<?php
/**
 * Debug script to check authority hook data in database
 * Run this in your browser to see what's actually stored
 */

// WordPress bootstrap - Multiple path attempts for different installations
if (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
} elseif (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
} elseif (file_exists('../../../../../../wp-load.php')) {
    require_once('../../../../../../wp-load.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
} else {
    die('Could not locate wp-load.php. Please run this from WordPress admin or adjust the path.');
}

if (!current_user_can('administrator')) {
    die('Access denied - admin only');
}

echo '<h1>🔍 Authority Hook Data Debug</h1>';
echo '<style>body{font-family:Arial;} .debug{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:4px;} .found{background:#e8f5e8;} .missing{background:#ffebee;}</style>';

// Get the most recent guest post
$guest_posts = get_posts([
    'post_type' => 'guests',
    'post_status' => 'publish',
    'numberposts' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
]);

if (empty($guest_posts)) {
    echo '<div class="debug missing">❌ No guest posts found!</div>';
    
    // Show all custom post types
    $post_types = get_post_types(['public' => true], 'names');
    echo '<div class="debug">Available post types: ' . implode(', ', $post_types) . '</div>';
    exit;
}

echo '<div class="debug found">✅ Found ' . count($guest_posts) . ' guest posts</div>';

foreach ($guest_posts as $post) {
    echo "<h2>🎯 Guest Post: {$post->post_title} (ID: {$post->ID})</h2>";
    
    // Get ALL meta fields for this post
    $all_meta = get_post_meta($post->ID);
    echo '<div class="debug">📊 Total meta fields: ' . count($all_meta) . '</div>';
    
    // Authority hook related fields we're looking for
    $authority_fields = [
        'guest_title' => 'WHO',
        'hook_what' => 'WHAT', 
        'hook_when' => 'WHEN',
        'hook_how' => 'HOW',
        'hook_where' => 'WHERE',
        'hook_why' => 'WHY'
    ];
    
    echo '<h3>🔑 Authority Hook Fields:</h3>';
    $found_count = 0;
    
    foreach ($authority_fields as $field_name => $label) {
        $value = get_post_meta($post->ID, $field_name, true);
        $class = !empty($value) ? 'found' : 'missing';
        $status = !empty($value) ? '✅' : '❌';
        $display_value = !empty($value) ? $value : 'EMPTY';
        
        echo "<div class='debug {$class}'>{$status} {$label} ({$field_name}): <strong>{$display_value}</strong></div>";
        
        if (!empty($value)) {
            $found_count++;
        }
    }
    
    echo '<div class="debug">📈 Authority hook fields found: ' . $found_count . '/6</div>';
    
    // Topics fields
    echo '<h3>📝 Topics Fields:</h3>';
    $topics_found = 0;
    
    for ($i = 1; $i <= 5; $i++) {
        $topic = get_post_meta($post->ID, "topic_{$i}", true);
        $class = !empty($topic) ? 'found' : 'missing';
        $status = !empty($topic) ? '✅' : '❌';
        $display_value = !empty($topic) ? $topic : 'EMPTY';
        
        echo "<div class='debug {$class}'>{$status} topic_{$i}: <strong>{$display_value}</strong></div>";
        
        if (!empty($topic)) {
            $topics_found++;
        }
    }
    
    echo '<div class="debug">📈 Topics found: ' . $topics_found . '/5</div>';
    
    // Show a sample of ALL meta fields to see naming patterns
    echo '<h3>🔍 All Meta Fields (first 20):</h3>';
    $meta_keys = array_keys($all_meta);
    $sample_keys = array_slice($meta_keys, 0, 20);
    
    foreach ($sample_keys as $key) {
        $value = $all_meta[$key][0];
        $short_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        echo "<div class='debug'><strong>{$key}:</strong> {$short_value}</div>";
    }
    
    if (count($meta_keys) > 20) {
        echo '<div class="debug">... and ' . (count($meta_keys) - 20) . ' more fields</div>';
    }
    
    echo '<hr style="margin: 30px 0;">';
}

echo '<h2>💡 Next Steps:</h2>';
echo '<div class="debug">1. If authority hook fields are EMPTY, the data needs to be saved first</div>';
echo '<div class="debug">2. If field names are different, update the Pods service field mappings</div>';
echo '<div class="debug">3. If topics are EMPTY, they also need to be saved</div>';
echo '<div class="debug">4. Check if there are Formidable entries connected to these posts</div>';

// Check for Formidable connection
global $wpdb;
$formidable_table = $wpdb->prefix . 'frm_items';

if ($wpdb->get_var("SHOW TABLES LIKE '{$formidable_table}'") == $formidable_table) {
    echo '<h2>📋 Formidable Connection Check:</h2>';
    
    foreach ($guest_posts as $post) {
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$formidable_table} WHERE post_id = %d",
            $post->ID
        ));
        
        if ($entry_id) {
            echo "<div class='debug found'>✅ Post {$post->ID} connected to Formidable entry {$entry_id}</div>";
            
            // Get some Formidable meta
            $form_meta = $wpdb->get_results($wpdb->prepare(
                "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas 
                 WHERE item_id = %d LIMIT 10",
                $entry_id
            ));
            
            echo '<div class="debug">Formidable fields: ';
            foreach ($form_meta as $meta) {
                echo "Field {$meta->field_id}: " . substr($meta->meta_value, 0, 30) . '... | ';
            }
            echo '</div>';
        } else {
            echo "<div class='debug missing'>❌ Post {$post->ID} not connected to Formidable</div>";
        }
    }
} else {
    echo '<div class="debug missing">❌ Formidable tables not found</div>';
}
?>