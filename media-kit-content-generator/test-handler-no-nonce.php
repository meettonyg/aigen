<?php
/**
 * Temporary test handler to bypass nonce verification
 * This confirms that data serialization/extraction is working
 * IMPORTANT: Remove this file after testing!
 */

// Add this temporarily to your enhanced_ajax_handlers.php for testing:

/**
 * TEST ONLY - Save topics without nonce verification
 */
public function handle_save_topics_test() {
    error_log('MKCG AJAX TEST: Starting test save handler (no nonce check)');
    error_log('MKCG AJAX TEST: Raw _POST: ' . print_r($_POST, true));
    
    // Skip nonce verification for testing
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Must be logged in']);
        return;
    }
    
    $post_id = $this->get_post_id();
    if (!$post_id) {
        wp_send_json_error(['message' => 'Post ID required']);
        return;
    }
    
    // Extract data using same methods
    $topics_data = $this->extract_topics_data();
    $authority_hook_data = $this->extract_authority_hook_data();
    
    error_log('MKCG AJAX TEST: Extracted topics: ' . print_r($topics_data, true));
    error_log('MKCG AJAX TEST: Extracted authority hook: ' . print_r($authority_hook_data, true));
    
    // Return what we extracted (don't actually save for test)
    wp_send_json_success([
        'message' => 'TEST - Data extraction successful',
        'post_id' => $post_id,
        'extracted_data' => [
            'topics' => $topics_data,
            'authority_hook' => $authority_hook_data,
            'topics_count' => count($topics_data),
            'authority_count' => count($authority_hook_data)
        ],
        'raw_post_keys' => array_keys($_POST)
    ]);
}

// Also add to init():
// add_action('wp_ajax_mkcg_save_topics_test', [$this, 'handle_save_topics_test']);
