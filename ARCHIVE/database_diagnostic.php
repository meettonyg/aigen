<?php
/**
 * DIAGNOSTIC SCRIPT: Find Exact Source of Authority Hook Data
 * 
 * Place this file in your WordPress root directory and run:
 * https://yoursite.com/debug-authority-hook-data.php?post_id=32372
 */

// Include WordPress
require_once('wp-config.php');
require_once('wp-load.php');

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 32372;

echo "<h1>🔍 Authority Hook Data Source Diagnostic</h1>";
echo "<h2>📋 Post ID: {$post_id}</h2>";
echo "<style>
    body { font-family: monospace; background: #f5f5f5; padding: 20px; }
    .found { background: #ffeb3b; padding: 5px; }
    .empty { color: #999; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 3px; }
</style>";

// 1. CHECK ALL POST META for this post
echo "<div class='section'>";
echo "<h3>🗃️ ALL POST META for Post {$post_id}</h3>";

global $wpdb;
$meta_results = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_key",
    $post_id
));

if ($meta_results) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Meta Key</th><th>Meta Value</th><th>Analysis</th></tr>";
    
    foreach ($meta_results as $meta) {
        $analysis = '';
        $class = '';
        
        // Check if this looks like authority hook data
        if (strpos($meta->meta_value, '2nd value') !== false || 
            strpos($meta->meta_value, 'Authors launching') !== false ||
            strpos($meta->meta_value, 'What results') !== false) {
            $analysis = "🎯 CONTAINS TARGET DATA!";
            $class = 'found';
        }
        
        // Check field names that might contain authority hook data
        if (preg_match('/(who|what|when|how|hook|authority|guest_title)/i', $meta->meta_key)) {
            $analysis .= " 🔍 Authority Hook Field!";
            $class = 'found';
        }
        
        echo "<tr class='{$class}'>";
        echo "<td><strong>{$meta->meta_key}</strong></td>";
        echo "<td>" . htmlspecialchars(substr($meta->meta_value, 0, 100)) . (strlen($meta->meta_value) > 100 ? '...' : '') . "</td>";
        echo "<td>{$analysis}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='empty'>No post meta found for this post.</p>";
}
echo "</div>";

// 2. CHECK SPECIFIC AUTHORITY HOOK FIELDS
echo "<div class='section'>";
echo "<h3>🎯 SPECIFIC AUTHORITY HOOK FIELD CHECKS</h3>";

$authority_fields = [
    'guest_title',
    'hook_who', 
    'hook_what',
    'hook_when', 
    'hook_how',
    'hook_where',
    'hook_why',
    'authority_hook_who',
    'authority_hook_what',
    'authority_hook_when',
    'authority_hook_how',
    'who',
    'what', 
    'when',
    'how'
];

foreach ($authority_fields as $field) {
    $value = get_post_meta($post_id, $field, true);
    $class = '';
    
    if (!empty($value)) {
        if (strpos($value, '2nd value') !== false || strpos($value, 'Authors launching') !== false) {
            $class = 'found';
        }
        echo "<div class='{$class}'><strong>{$field}:</strong> " . htmlspecialchars($value) . "</div>";
    } else {
        echo "<div class='empty'><strong>{$field}:</strong> (empty)</div>";
    }
}
echo "</div>";

// 3. CHECK PODS DATA if Pods is available
echo "<div class='section'>";
echo "<h3>🍃 PODS DATA CHECK</h3>";

if (class_exists('Pods')) {
    try {
        $pod = pods('guests', $post_id);
        if ($pod && $pod->exists()) {
            echo "<h4>✅ Pods 'guests' post found</h4>";
            
            // Check common authority hook fields in Pods
            $pods_fields = ['guest_title', 'hook_who', 'hook_what', 'hook_when', 'hook_how', 'who', 'what', 'when', 'how'];
            
            foreach ($pods_fields as $field) {
                $value = $pod->field($field);
                $class = '';
                
                if (!empty($value)) {
                    if (is_string($value) && (strpos($value, '2nd value') !== false || strpos($value, 'Authors launching') !== false)) {
                        $class = 'found';
                    }
                    echo "<div class='{$class}'><strong>pods.{$field}:</strong> ";
                    if (is_array($value) || is_object($value)) {
                        echo "<pre>" . print_r($value, true) . "</pre>";
                    } else {
                        echo htmlspecialchars($value);
                    }
                    echo "</div>";
                } else {
                    echo "<div class='empty'><strong>pods.{$field}:</strong> (empty)</div>";
                }
            }
        } else {
            echo "<p class='empty'>No Pods 'guests' post found for ID {$post_id}</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Pods error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='empty'>Pods plugin not available</p>";
}
echo "</div>";

// 4. CHECK FORMIDABLE FORMS DATA
echo "<div class='section'>";
echo "<h3>📝 FORMIDABLE FORMS DATA CHECK</h3>";

if (class_exists('FrmEntry')) {
    // Look for entries related to this post
    $entries = $wpdb->get_results($wpdb->prepare(
        "SELECT id, form_id, item_key, created_at FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
        $post_id
    ));
    
    if ($entries) {
        foreach ($entries as $entry) {
            echo "<h4>📄 Formidable Entry ID: {$entry->id} (Form: {$entry->form_id})</h4>";
            
            // Get all field values for this entry
            $field_values = $wpdb->get_results($wpdb->prepare(
                "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d",
                $entry->id
            ));
            
            foreach ($field_values as $field_value) {
                $class = '';
                if (strpos($field_value->meta_value, '2nd value') !== false || 
                    strpos($field_value->meta_value, 'Authors launching') !== false ||
                    strpos($field_value->meta_value, 'What results') !== false) {
                    $class = 'found';
                }
                
                echo "<div class='{$class}'><strong>Field {$field_value->field_id}:</strong> " . htmlspecialchars($field_value->meta_value) . "</div>";
            }
        }
    } else {
        echo "<p class='empty'>No Formidable entries found for post {$post_id}</p>";
    }
} else {
    echo "<p class='empty'>Formidable Forms not available</p>";
}
echo "</div>";

// 5. GENERATE CLEANUP COMMANDS
echo "<div class='section'>";
echo "<h3>🧹 DATA CLEANUP COMMANDS</h3>";
echo "<p>Based on the findings above, here are the commands to completely clear this data:</p>";

echo "<h4>🗑️ Delete All Post Meta:</h4>";
echo "<pre>";
echo "DELETE FROM {$wpdb->postmeta} WHERE post_id = {$post_id} AND meta_key IN (\n";
foreach ($authority_fields as $field) {
    echo "    '{$field}',\n";
}
echo "    'complete_authority_hook'\n";
echo ");";
echo "</pre>";

echo "<h4>🍃 Clear Pods Data (if using Pods):</h4>";
echo "<pre>";
foreach ($authority_fields as $field) {
    echo "update_post_meta({$post_id}, '{$field}', '');\n";
}
echo "</pre>";

echo "<h4>📝 Clear Formidable Data (if using Formidable):</h4>";
echo "<pre>";
echo "// You'll need to identify the specific field IDs from the results above\n";
echo "// and update them individually in the Formidable admin\n";
echo "</pre>";

echo "</div>";

// 6. PROVIDE INSTANT CLEANUP BUTTON
echo "<div class='section'>";
echo "<h3>⚡ INSTANT CLEANUP</h3>";

if (isset($_GET['clear_all']) && $_GET['clear_all'] === 'yes') {
    echo "<h4>🧹 CLEARING ALL AUTHORITY HOOK DATA...</h4>";
    
    $cleared_count = 0;
    foreach ($authority_fields as $field) {
        $result = delete_post_meta($post_id, $field);
        if ($result) {
            echo "✅ Cleared: {$field}<br>";
            $cleared_count++;
        }
    }
    
    // Clear any additional meta that might contain the target data
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND (meta_value LIKE %s OR meta_value LIKE %s)",
        $post_id, '%2nd value%', '%Authors launching%'
    ));
    
    echo "<p><strong>✅ Cleared {$cleared_count} fields and any meta containing target data.</strong></p>";
    echo "<p>🔄 <a href='?post_id={$post_id}'>Refresh to verify cleanup</a></p>";
} else {
    echo "<p>⚠️ <strong>WARNING:</strong> This will permanently delete all authority hook data for post {$post_id}.</p>";
    echo "<p><a href='?post_id={$post_id}&clear_all=yes' style='background: red; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🗑️ CLEAR ALL DATA NOW</a></p>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h3>🔄 NEXT STEPS</h3>";
echo "<ol>";
echo "<li>Review the data sources above to see exactly where '2nd value and Authors launching a book' is stored</li>";
echo "<li>Use the cleanup commands or instant cleanup button to remove the data</li>";
echo "<li>Clear any WordPress object cache</li>";
echo "<li>Refresh your Topics Generator page to verify the data is gone</li>";
echo "</ol>";
echo "</div>";
?>