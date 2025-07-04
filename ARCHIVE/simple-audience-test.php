<?php
/**
 * Simple test - just check what audience data exists for post 32372
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

echo '<h1>🔍 Simple Audience Test for Post ' . $post_id . '</h1>';
echo '<style>body{font-family:Arial;} .debug{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:4px;} .found{background:#e8f5e8;} .missing{background:#ffebee;}</style>';

echo '<h2>1. Direct wp_get_post_terms() test:</h2>';
$terms = wp_get_post_terms($post_id, 'audience');
if (is_wp_error($terms)) {
    echo '<div class="debug missing">❌ Error: ' . $terms->get_error_message() . '</div>';
} else {
    echo '<div class="debug">📊 Found ' . count($terms) . ' terms</div>';
    foreach ($terms as $term) {
        echo '<div class="debug found">✅ Term: "' . $term->name . '" (ID: ' . $term->term_id . ')</div>';
    }
}

echo '<h2>2. Get terms with names only:</h2>';
$term_names = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);
if (!empty($term_names)) {
    echo '<div class="debug found">✅ First term name: "' . $term_names[0] . '"</div>';
} else {
    echo '<div class="debug missing">❌ No term names found</div>';
}

echo '<h2>3. Test all available taxonomies for this post:</h2>';
$taxonomies = get_object_taxonomies('guests');
echo '<div class="debug">📋 Available taxonomies for guests: ' . implode(', ', $taxonomies) . '</div>';

foreach ($taxonomies as $tax) {
    $tax_terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
    if (!empty($tax_terms)) {
        echo '<div class="debug found">✅ ' . $tax . ': ' . implode(', ', $tax_terms) . '</div>';
    } else {
        echo '<div class="debug">⚪ ' . $tax . ': empty</div>';
    }
}

echo '<h2>4. Quick fix test - just set guest_title to the audience:</h2>';
if (!empty($term_names) && isset($term_names[0])) {
    $audience = $term_names[0];
    $result = update_post_meta($post_id, 'guest_title', $audience);
    echo '<div class="debug found">✅ Set guest_title to: "' . $audience . '" (Result: ' . ($result ? 'SUCCESS' : 'FAILED') . ')</div>';
    
    // Verify it was saved
    $saved_value = get_post_meta($post_id, 'guest_title', true);
    echo '<div class="debug">🔍 Verification - guest_title now contains: "' . $saved_value . '"</div>';
} else {
    echo '<div class="debug missing">❌ No audience found to copy</div>';
}

echo '<h2>5. Test Topics Generator now:</h2>';
echo '<p><a href="?post_id=' . $post_id . '" style="background:#2196f3;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;" target="_blank">📱 Test Topics Generator Now</a></p>';

echo '<hr><h2>💡 Diagnosis:</h2>';
if (!empty($term_names)) {
    echo '<div class="debug found">✅ Audience taxonomy works: "' . $term_names[0] . '"</div>';
    echo '<div class="debug">🔧 Applied quick fix: Copied audience to guest_title field</div>';
    echo '<div class="debug">🚀 Your Topics Generator should now show the correct WHO component</div>';
} else {
    echo '<div class="debug missing">❌ No audience terms found - something is wrong with the taxonomy</div>';
}
?>