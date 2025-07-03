<?php
/**
 * Debug Script: Topics Generator Data Loading
 * Purpose: Debug why entry 32372 isn't loading data from custom post
 */

// WordPress header
require_once('../../../../../wp-config.php');

echo '<h1>🔍 Topics Generator Data Loading Debug</h1>';
echo '<p>Debugging entry 32372 data loading issue</p>';

$entry_id = 32372;

echo '<h2>Step 1: Convert Entry ID to Post ID</h2>';

global $wpdb;

// Find the post_id associated with this entry_id
$post_id = $wpdb->get_var($wpdb->prepare(
    "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $entry_id
));

echo '<p><strong>Entry ID:</strong> ' . $entry_id . '</p>';
echo '<p><strong>Associated Post ID:</strong> ' . ($post_id ?: 'NOT FOUND') . '</p>';

if (!$post_id) {
    echo '<div style="background: #ffebee; padding: 15px; border: 1px solid #f44336; border-radius: 4px;">';
    echo '<strong>❌ PROBLEM FOUND:</strong> No post_id found for entry ' . $entry_id;
    echo '<br>This means the Formidable entry is not associated with a WordPress custom post.';
    echo '</div>';
    
    // Show all entries to help debug
    echo '<h3>Available Formidable Entries:</h3>';
    $entries = $wpdb->get_results("SELECT id, post_id, item_key, created_at FROM {$wpdb->prefix}frm_items ORDER BY id DESC LIMIT 10");
    
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Entry ID</th><th>Post ID</th><th>Item Key</th><th>Created</th></tr>';
    foreach ($entries as $entry) {
        $highlight = ($entry->id == $entry_id) ? ' style="background: #ffeb3b;"' : '';
        echo '<tr' . $highlight . '>';
        echo '<td>' . $entry->id . '</td>';
        echo '<td>' . ($entry->post_id ?: 'NULL') . '</td>';
        echo '<td>' . $entry->item_key . '</td>';
        echo '<td>' . $entry->created_at . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    exit;
}

echo '<div style="background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 4px;">';
echo '<strong>✅ SUCCESS:</strong> Found associated post ID: ' . $post_id;
echo '</div>';

echo '<h2>Step 2: Check Post Exists and Type</h2>';

$post = get_post($post_id);

if (!$post) {
    echo '<div style="background: #ffebee; padding: 15px; border: 1px solid #f44336; border-radius: 4px;">';
    echo '<strong>❌ PROBLEM:</strong> Post ID ' . $post_id . ' does not exist in WordPress';
    echo '</div>';
    exit;
}

echo '<p><strong>Post Title:</strong> ' . $post->post_title . '</p>';
echo '<p><strong>Post Type:</strong> ' . $post->post_type . '</p>';
echo '<p><strong>Post Status:</strong> ' . $post->post_status . '</p>';

echo '<h2>Step 3: Check WordPress Post Meta</h2>';

$meta_fields = [
    'mkcg_who' => 'Authority Hook - WHO',
    'mkcg_result' => 'Authority Hook - RESULT', 
    'mkcg_when' => 'Authority Hook - WHEN',
    'mkcg_how' => 'Authority Hook - HOW',
    'mkcg_topic_1' => 'Topic 1',
    'mkcg_topic_2' => 'Topic 2',
    'mkcg_topic_3' => 'Topic 3',
    'mkcg_topic_4' => 'Topic 4',
    'mkcg_topic_5' => 'Topic 5'
];

echo '<table border="1" cellpadding="5">';
echo '<tr><th>Meta Key</th><th>Description</th><th>Value</th><th>Status</th></tr>';

$found_data = false;

foreach ($meta_fields as $meta_key => $description) {
    $value = get_post_meta($post_id, $meta_key, true);
    $status = $value ? '✅ Found' : '❌ Empty';
    
    if ($value) {
        $found_data = true;
    }
    
    echo '<tr>';
    echo '<td>' . $meta_key . '</td>';
    echo '<td>' . $description . '</td>';
    echo '<td>' . ($value ?: '<em>empty</em>') . '</td>';
    echo '<td>' . $status . '</td>';
    echo '</tr>';
}

echo '</table>';

if (!$found_data) {
    echo '<div style="background: #fff3e0; padding: 15px; border: 1px solid #ff9800; border-radius: 4px;">';
    echo '<strong>⚠️ ISSUE FOUND:</strong> No MKCG post meta found for post ID ' . $post_id;
    echo '<br>The data might be stored in Formidable fields instead of WordPress post meta.';
    echo '</div>';
    
    echo '<h2>Step 4: Check Formidable Fields (Fallback)</h2>';
    
    $formidable_fields = [
        '10296' => 'WHO',
        '10297' => 'RESULT',
        '10387' => 'WHEN', 
        '10298' => 'HOW',
        '8498' => 'Topic 1',
        '8499' => 'Topic 2',
        '8500' => 'Topic 3',
        '8501' => 'Topic 4',
        '8502' => 'Topic 5'
    ];
    
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Field ID</th><th>Description</th><th>Value</th><th>Status</th></tr>';
    
    $formidable_data_found = false;
    
    foreach ($formidable_fields as $field_id => $description) {
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
            $entry_id, $field_id
        ));
        
        // Process serialized data
        if (is_serialized($value)) {
            $unserialized = @unserialize($value);
            if (is_array($unserialized) && !empty($unserialized)) {
                $value = trim(array_values($unserialized)[0]);
            } elseif (is_string($unserialized)) {
                $value = trim($unserialized);
            }
        }
        
        $status = $value ? '✅ Found' : '❌ Empty';
        
        if ($value) {
            $formidable_data_found = true;
        }
        
        echo '<tr>';
        echo '<td>' . $field_id . '</td>';
        echo '<td>' . $description . '</td>';
        echo '<td>' . ($value ?: '<em>empty</em>') . '</td>';
        echo '<td>' . $status . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    if ($formidable_data_found) {
        echo '<div style="background: #e3f2fd; padding: 15px; border: 1px solid #2196f3; border-radius: 4px;">';
        echo '<strong>💡 SOLUTION FOUND:</strong> Data exists in Formidable fields but not in WordPress post meta.';
        echo '<br><strong>Action needed:</strong> Migrate the data from Formidable fields to WordPress post meta.';
        echo '</div>';
        
        echo '<h2>Step 5: Migrate Data to WordPress Post Meta</h2>';
        
        $migrated_count = 0;
        
        // Migrate authority hook components
        $auth_mapping = [
            '10296' => 'mkcg_who',
            '10297' => 'mkcg_result', 
            '10387' => 'mkcg_when',
            '10298' => 'mkcg_how'
        ];
        
        foreach ($auth_mapping as $field_id => $meta_key) {
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($value) {
                // Process serialized data
                if (is_serialized($value)) {
                    $unserialized = @unserialize($value);
                    if (is_array($unserialized) && !empty($unserialized)) {
                        $value = trim(array_values($unserialized)[0]);
                    } elseif (is_string($unserialized)) {
                        $value = trim($unserialized);
                    }
                }
                
                if ($value) {
                    update_post_meta($post_id, $meta_key, $value);
                    echo '<p>✅ Migrated ' . $meta_key . ': ' . $value . '</p>';
                    $migrated_count++;
                }
            }
        }
        
        // Migrate topics
        $topics_mapping = [
            '8498' => 'mkcg_topic_1',
            '8499' => 'mkcg_topic_2',
            '8500' => 'mkcg_topic_3', 
            '8501' => 'mkcg_topic_4',
            '8502' => 'mkcg_topic_5'
        ];
        
        foreach ($topics_mapping as $field_id => $meta_key) {
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                $entry_id, $field_id
            ));
            
            if ($value) {
                // Process serialized data
                if (is_serialized($value)) {
                    $unserialized = @unserialize($value);
                    if (is_array($unserialized) && !empty($unserialized)) {
                        $value = trim(array_values($unserialized)[0]);
                    } elseif (is_string($unserialized)) {
                        $value = trim($unserialized);
                    }
                }
                
                if ($value) {
                    update_post_meta($post_id, $meta_key, $value);
                    echo '<p>✅ Migrated ' . $meta_key . ': ' . $value . '</p>';
                    $migrated_count++;
                }
            }
        }
        
        echo '<div style="background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 4px;">';
        echo '<strong>🎉 MIGRATION COMPLETE:</strong> Migrated ' . $migrated_count . ' fields to WordPress post meta.';
        echo '<br><strong>Next step:</strong> Refresh your Topics Generator page to see the data.';
        echo '</div>';
        
    } else {
        echo '<div style="background: #ffebee; padding: 15px; border: 1px solid #f44336; border-radius: 4px;">';
        echo '<strong>❌ NO DATA FOUND:</strong> Entry ' . $entry_id . ' has no data in either WordPress post meta or Formidable fields.';
        echo '</div>';
    }
} else {
    echo '<div style="background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 4px;">';
    echo '<strong>✅ SUCCESS:</strong> Found MKCG data in WordPress post meta. Topics Generator should load correctly.';
    echo '</div>';
}

echo '<h2>Summary</h2>';
echo '<p><strong>Entry ID:</strong> ' . $entry_id . '</p>';
echo '<p><strong>Post ID:</strong> ' . $post_id . '</p>';
echo '<p><strong>WordPress Post Meta:</strong> ' . ($found_data ? 'Found' : 'Not Found') . '</p>';
echo '<p><strong>Recommended URL:</strong> <a href="/topics/?post_id=' . $post_id . '">/topics/?post_id=' . $post_id . '</a></p>';

?>