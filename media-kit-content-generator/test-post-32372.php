<?php
/**
 * Test specific post ID 32372 for authority hook data
 * Direct debug for post 32372
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

$post_id = 32372;

echo '<h1>🎯 Testing Post ID: ' . $post_id . '</h1>';
echo '<style>body{font-family:Arial;} .debug{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:4px;} .found{background:#e8f5e8;} .missing{background:#ffebee;} .warning{background:#fff3cd;}</style>';

// Check if post exists
$post = get_post($post_id);
if (!$post) {
    echo '<div class="debug missing">❌ Post ' . $post_id . ' does not exist!</div>';
    exit;
}

echo '<div class="debug found">✅ Post exists: "' . esc_html($post->post_title) . '"</div>';
echo '<div class="debug">📊 Post type: ' . $post->post_type . '</div>';
echo '<div class="debug">📅 Post status: ' . $post->post_status . '</div>';

// Check if it's a guests post type
if ($post->post_type !== 'guests') {
    echo '<div class="debug warning">⚠️ This is NOT a "guests" post type! The Pods service expects "guests" posts.</div>';
    echo '<div class="debug">Current post type: ' . $post->post_type . '</div>';
    echo '<div class="debug">The Topics Generator is configured for "guests" post type only.</div>';
} else {
    echo '<div class="debug found">✅ Correct post type: "guests"</div>';
}

// Get ALL meta fields for this post
$all_meta = get_post_meta($post_id);
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

echo '<h2>🔑 Authority Hook Fields Check:</h2>';
$found_count = 0;

foreach ($authority_fields as $field_name => $label) {
    $value = get_post_meta($post_id, $field_name, true);
    $class = !empty($value) ? 'found' : 'missing';
    $status = !empty($value) ? '✅' : '❌';
    $display_value = !empty($value) ? esc_html($value) : 'EMPTY';
    
    echo "<div class='debug {$class}'>{$status} {$label} ({$field_name}): <strong>{$display_value}</strong></div>";
    
    if (!empty($value)) {
        $found_count++;
    }
}

echo '<div class="debug">📈 Authority hook fields found: ' . $found_count . '/6</div>';

// Topics fields check
echo '<h2>📝 Topics Fields Check:</h2>';
$topics_found = 0;

for ($i = 1; $i <= 5; $i++) {
    $topic = get_post_meta($post_id, "topic_{$i}", true);
    $class = !empty($topic) ? 'found' : 'missing';
    $status = !empty($topic) ? '✅' : '❌';
    $display_value = !empty($topic) ? esc_html($topic) : 'EMPTY';
    
    echo "<div class='debug {$class}'>{$status} topic_{$i}: <strong>{$display_value}</strong></div>";
    
    if (!empty($topic)) {
        $topics_found++;
    }
}

echo '<div class="debug">📈 Topics found: ' . $topics_found . '/5</div>';

// Test what the Pods service would return
echo '<h2>🔬 Pods Service Test:</h2>';

if (class_exists('MKCG_Pods_Service')) {
    $pods_service = new MKCG_Pods_Service();
    
    // Test is_guests_post
    $is_guests = $pods_service->is_guests_post($post_id);
    echo '<div class="debug ' . ($is_guests ? 'found' : 'missing') . '">is_guests_post(' . $post_id . '): ' . ($is_guests ? 'TRUE' : 'FALSE') . '</div>';
    
    // CRITICAL: Test audience taxonomy specifically
    echo '<h3>🎯 Audience Taxonomy Debug:</h3>';
    
    // Check if audience taxonomy exists
    $audience_taxonomy = get_taxonomy('audience');
    if ($audience_taxonomy) {
        echo '<div class="debug found">✅ Audience taxonomy exists: ' . $audience_taxonomy->label . '</div>';
        echo '<div class="debug">📊 Taxonomy object types: ' . implode(', ', $audience_taxonomy->object_type) . '</div>';
    } else {
        echo '<div class="debug missing">❌ Audience taxonomy not found!</div>';
    }
    
    // Get audience terms for this post
    $audience_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'all']);
    if (is_wp_error($audience_terms)) {
        echo '<div class="debug missing">❌ Error getting audience terms: ' . $audience_terms->get_error_message() . '</div>';
    } elseif (empty($audience_terms)) {
        echo '<div class="debug missing">❌ No audience terms assigned to post ' . $post_id . '</div>';
        
        // Show all available audience terms
        $all_audience_terms = get_terms(['taxonomy' => 'audience', 'hide_empty' => false]);
        if (!empty($all_audience_terms) && !is_wp_error($all_audience_terms)) {
            echo '<div class="debug">💡 Available audience terms: ';
            foreach ($all_audience_terms as $term) {
                echo $term->name . ' (ID: ' . $term->term_id . '), ';
            }
            echo '</div>';
        } else {
            echo '<div class="debug missing">❌ No audience terms exist in the system</div>';
        }
    } else {
        echo '<div class="debug found">✅ Found ' . count($audience_terms) . ' audience terms for post ' . $post_id . ':</div>';
        foreach ($audience_terms as $term) {
            echo '<div class="debug found">- ' . $term->name . ' (ID: ' . $term->term_id . ', Slug: ' . $term->slug . ')</div>';
        }
    }
    
    // Test the new audience method directly
    echo '<h3>🔧 Direct Method Test:</h3>';
    
    // Use reflection to call the private method
    $reflection = new ReflectionClass($pods_service);
    $get_audience_method = $reflection->getMethod('get_audience_from_taxonomy');
    $get_audience_method->setAccessible(true);
    
    $audience_result = $get_audience_method->invoke($pods_service, $post_id);
    
    if (!empty($audience_result)) {
        echo '<div class="debug found">✅ get_audience_from_taxonomy() returned: "' . esc_html($audience_result) . '"</div>';
    } else {
        echo '<div class="debug missing">❌ get_audience_from_taxonomy() returned empty</div>';
    }
    
    // Test get_guest_data
    echo '<h3>📊 Full Pods Service Results:</h3>';
    $guest_data = $pods_service->get_guest_data($post_id);
    echo '<div class="debug">📊 Pods service get_guest_data results:</div>';
    echo '<div class="debug">- has_data: ' . ($guest_data['has_data'] ? 'TRUE' : 'FALSE') . '</div>';
    echo '<div class="debug">- authority_hook_components WHO: "' . esc_html($guest_data['authority_hook_components']['who']) . '"</div>';
    echo '<div class="debug">- authority_hook_components WHAT: "' . esc_html($guest_data['authority_hook_components']['what']) . '"</div>';
    echo '<div class="debug">- authority_hook_components WHEN: "' . esc_html($guest_data['authority_hook_components']['when']) . '"</div>';
    echo '<div class="debug">- authority_hook_components HOW: "' . esc_html($guest_data['authority_hook_components']['how']) . '"</div>';
    echo '<div class="debug">- complete hook: "' . esc_html($guest_data['authority_hook_components']['complete']) . '"</div>';
    
    $topics_count = count(array_filter($guest_data['topics']));
    echo '<div class="debug">- topics loaded: ' . $topics_count . '/5</div>';
} else {
    echo '<div class="debug missing">❌ MKCG_Pods_Service class not found!</div>';
}

// Show sample meta fields to understand naming patterns
echo '<h2>🔍 Meta Field Sample (to identify naming patterns):</h2>';
$meta_keys = array_keys($all_meta);

// Look for any fields that might contain authority/hook/guest/topic data
$relevant_keys = array_filter($meta_keys, function($key) {
    $key_lower = strtolower($key);
    return strpos($key_lower, 'hook') !== false || 
           strpos($key_lower, 'guest') !== false ||
           strpos($key_lower, 'topic') !== false ||
           strpos($key_lower, 'title') !== false ||
           strpos($key_lower, 'what') !== false ||
           strpos($key_lower, 'when') !== false ||
           strpos($key_lower, 'how') !== false ||
           strpos($key_lower, 'who') !== false ||
           strpos($key_lower, 'why') !== false ||
           strpos($key_lower, 'where') !== false;
});

if (!empty($relevant_keys)) {
    echo '<div class="debug found">🎯 Found ' . count($relevant_keys) . ' potentially relevant meta fields:</div>';
    foreach ($relevant_keys as $key) {
        $value = $all_meta[$key][0];
        $short_value = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
        echo "<div class='debug'><strong>{$key}:</strong> " . esc_html($short_value) . "</div>";
    }
} else {
    echo '<div class="debug missing">❌ No relevant meta fields found with hook/guest/topic keywords</div>';
}

// Show first 10 meta fields anyway to see what's there
echo '<h3>📋 First 10 Meta Fields (to see what exists):</h3>';
$sample_keys = array_slice($meta_keys, 0, 10);
foreach ($sample_keys as $key) {
    $value = $all_meta[$key][0];
    $short_value = strlen($value) > 80 ? substr($value, 0, 80) . '...' : $value;
    echo "<div class='debug'><strong>{$key}:</strong> " . esc_html($short_value) . "</div>";
}

// Action buttons
echo '<h2>🚀 Actions:</h2>';

if ($found_count === 0 && $topics_found === 0) {
    echo '<div class="debug warning">⚠️ No authority hook or topics data found. Need to populate test data.</div>';
    echo '<p><a href="fix-authority-hook-data.php?test_post=' . $post_id . '" style="background:#e67e22;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;">🔧 Populate Test Data for Post ' . $post_id . '</a></p>';
} else {
    echo '<div class="debug found">✅ Some data found. Test in Topics Generator:</div>';
}

// Topics Generator test link
echo '<p><a href="?post_id=' . $post_id . '" style="background:#2196f3;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;" target="_blank">📱 Test Topics Generator with Post ' . $post_id . '</a></p>';

// Check for Formidable connection
global $wpdb;
$formidable_table = $wpdb->prefix . 'frm_items';

if ($wpdb->get_var("SHOW TABLES LIKE '{$formidable_table}'") == $formidable_table) {
    echo '<h2>📋 Formidable Connection:</h2>';
    
    $entry_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$formidable_table} WHERE post_id = %d",
        $post_id
    ));
    
    if ($entry_id) {
        echo "<div class='debug found'>✅ Post {$post_id} connected to Formidable entry {$entry_id}</div>";
        
        // Get some Formidable meta to see if data is there instead
        $form_meta = $wpdb->get_results($wpdb->prepare(
            "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas 
             WHERE item_id = %d AND meta_value != '' LIMIT 10",
            $entry_id
        ));
        
        if (!empty($form_meta)) {
            echo '<div class="debug">📊 Sample Formidable fields with data:</div>';
            foreach ($form_meta as $meta) {
                $short_value = strlen($meta->meta_value) > 60 ? substr($meta->meta_value, 0, 60) . '...' : $meta->meta_value;
                echo "<div class='debug'>Field {$meta->field_id}: " . esc_html($short_value) . "</div>";
            }
        } else {
            echo '<div class="debug missing">❌ No Formidable field data found for entry ' . $entry_id . '</div>';
        }
    } else {
        echo "<div class='debug missing'>❌ Post {$post_id} not connected to any Formidable entry</div>";
    }
} else {
    echo '<div class="debug missing">❌ Formidable tables not found</div>';
}

echo '<h2>💡 Diagnosis Summary:</h2>';

if ($post->post_type !== 'guests') {
    echo '<div class="debug warning">🎯 PRIMARY ISSUE: Post ' . $post_id . ' is type "' . $post->post_type . '" but Topics Generator expects "guests"</div>';
    echo '<div class="debug">SOLUTION: Either change this post to "guests" type, or update the Pods service to handle "' . $post->post_type . '" posts.</div>';
} elseif ($found_count === 0) {
    echo '<div class="debug warning">🎯 PRIMARY ISSUE: No authority hook data saved in post meta fields</div>';
    echo '<div class="debug">SOLUTION: Use the "Populate Test Data" button above, or manually save data through the Topics Generator form.</div>';
} else {
    echo '<div class="debug found">🎯 LOOKS GOOD: Found authority hook data for post ' . $post_id . '</div>';
    echo '<div class="debug">The Topics Generator should show this data instead of defaults.</div>';
}
?>