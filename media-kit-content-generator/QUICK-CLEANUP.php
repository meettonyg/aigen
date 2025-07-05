<?php
/**
 * QUICK CLEANUP TOOL
 * Remove old default values from post 32372
 */

if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once('../../../wp-config.php');
}

echo "<h1>🧹 Quick Cleanup Tool for Post 32372</h1>\n";

$post_id = 32372;

// Field mappings from Authority Hook Service
$field_mappings = [
    'who' => 'guest_title',
    'what' => 'hook_what', 
    'when' => 'hook_when',
    'how' => 'hook_how'
];

// Old default values to remove
$old_defaults = [
    'who' => ['your audience', 'my audience', 'our audience'],
    'what' => ['achieve their goals', 'reach their goals', 'accomplish their goals'],
    'when' => ['they need help', 'they need assistance', 'they struggle'],
    'how' => ['through your method', 'through my method', 'with your help', 'with my help']
];

echo "<h2>Current Values in Database:</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background: #f0f0f0;'><th>Component</th><th>Meta Key</th><th>Current Value</th><th>Action</th></tr>\n";

$cleanup_needed = [];

foreach ($field_mappings as $component => $meta_key) {
    $value = get_post_meta($post_id, $meta_key, true);
    $action = 'Keep';
    $needs_cleanup = false;
    
    if (!empty($value)) {
        // Check if this is an old default
        if (isset($old_defaults[$component]) && in_array(strtolower(trim($value)), array_map('strtolower', $old_defaults[$component]))) {
            $action = '🗑️ WILL DELETE';
            $needs_cleanup = true;
            $cleanup_needed[] = ['component' => $component, 'meta_key' => $meta_key, 'value' => $value];
        }
    } else {
        $action = 'Already empty';
    }
    
    $row_color = $needs_cleanup ? 'background: #ffeeee;' : '';
    
    echo "<tr style='$row_color'>\n";
    echo "<td><strong>$component</strong></td>\n";
    echo "<td>$meta_key</td>\n";
    echo "<td>" . esc_html($value) . "</td>\n";
    echo "<td style='font-weight: bold; color: " . ($needs_cleanup ? 'red' : 'green') . ";'>$action</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";

if (!empty($cleanup_needed)) {
    echo "<h2 style='color: red;'>⚠️ Cleanup Required</h2>\n";
    echo "<p>Found " . count($cleanup_needed) . " old default values that need to be removed:</p>\n";
    
    foreach ($cleanup_needed as $item) {
        echo "<p>• <strong>{$item['component']}</strong> ({$item['meta_key']}): \"{$item['value']}\"</p>\n";
    }
    
    echo "<form method='post' style='margin: 20px 0;'>\n";
    echo "<input type='hidden' name='confirm_cleanup' value='1'>\n";
    echo "<input type='submit' value='🗑️ DELETE OLD DEFAULTS NOW' style='background: red; color: white; padding: 15px 30px; font-size: 16px; border: none; cursor: pointer;' onclick='return confirm(\"This will permanently delete the old default values. Are you sure?\")'>\n";
    echo "</form>\n";
    
    if (isset($_POST['confirm_cleanup'])) {
        echo "<h2 style='color: green;'>🧹 Cleanup in Progress...</h2>\n";
        
        $cleaned_count = 0;
        
        foreach ($cleanup_needed as $item) {
            $result = delete_post_meta($post_id, $item['meta_key']);
            if ($result) {
                echo "<p style='color: green;'>✅ Deleted {$item['component']} ({$item['meta_key']}): \"{$item['value']}\"</p>\n";
                $cleaned_count++;
            } else {
                echo "<p style='color: red;'>❌ Failed to delete {$item['component']} ({$item['meta_key']})</p>\n";
            }
        }
        
        if ($cleaned_count > 0) {
            echo "<h3 style='color: green; background: #eeffee; padding: 10px;'>🎉 SUCCESS: Cleaned $cleaned_count old default values!</h3>\n";
            echo "<p><strong>What to do next:</strong></p>\n";
            echo "<ol>\n";
            echo "<li>Refresh your Topics Generator page</li>\n";
            echo "<li>The form fields should now be empty</li>\n";
            echo "<li>No more 'your audience' or 'achieve their goals' text!</li>\n";
            echo "</ol>\n";
            
            echo "<p><a href='?' style='background: blue; color: white; padding: 10px; text-decoration: none;'>🔄 Refresh This Page to Verify</a></p>\n";
        }
    }
    
} else {
    echo "<h2 style='color: green;'>✅ No Cleanup Needed</h2>\n";
    echo "<p>All values are either empty or contain real data (not old defaults).</p>\n";
}

echo "<hr>\n";
echo "<h3>📍 WHERE THESE VALUES ARE STORED</h3>\n";
echo "<p>The old default values are stored in <strong>WordPress post meta</strong> with these keys:</p>\n";
echo "<ul>\n";
foreach ($field_mappings as $component => $meta_key) {
    echo "<li><strong>$component</strong> → <code>$meta_key</code></li>\n";
}
echo "</ul>\n";

echo "<p><strong>Why you can't see them in the post editor:</strong></p>\n";
echo "<ul>\n";
echo "<li>These are custom meta fields created by the plugin</li>\n";
echo "<li>They're not displayed in the standard WordPress post editor</li>\n";
echo "<li>They're only visible in the Topics Generator interface</li>\n";
echo "<li>You can see them in WordPress Admin → Posts → Edit Post → Custom Fields (if enabled)</li>\n";
echo "</ul>\n";

echo "<h3>🔧 How to Prevent This in Future</h3>\n";
echo "<p>The clean code fix I implemented will prevent new default values from being saved, but existing data in the database needed manual cleanup.</p>\n";

?>
