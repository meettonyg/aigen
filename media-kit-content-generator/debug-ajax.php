<?php
/**
 * QUICK DEBUG: Add this to wp-config.php to see AJAX debugging
 * Add this line to wp-config.php: include_once(__DIR__ . '/wp-content/plugins/media-kit-content-generator/debug-ajax.php');
 */

// Log all AJAX requests to see what's being received
add_action('wp_ajax_mkcg_save_topics_data', function() {
    error_log('=== MKCG AJAX DEBUG START ===');
    error_log('Action: mkcg_save_topics_data');
    error_log('User ID: ' . get_current_user_id());
    error_log('User can edit posts: ' . (current_user_can('edit_posts') ? 'YES' : 'NO'));
    error_log('POST data: ' . print_r($_POST, true));
    error_log('=== MKCG AJAX DEBUG END ===');
}, 1); // Priority 1 to run before the actual handler
?>