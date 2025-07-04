<?php
/**
 * Simplified Topics Save Handler
 * This provides a direct save method that bypasses complex dependencies
 * Place this at the end of enhanced_ajax_handlers.php temporarily for testing
 */

// Add this as an additional method in the Enhanced_AJAX_Handlers class:

/**
 * Direct save method - bypasses Pods service for testing
 */
public function handle_save_topics_direct() {
    error_log('MKCG AJAX: Starting DIRECT save handler');
    
    // Verify request
    if (!$this->verify_request()) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    // Get post ID
    $post_id = $this->get_post_id();
    if (!$post_id) {
        wp_send_json_error(['message' => 'Post ID required']);
        return;
    }
    
    // Extract data
    $topics_data = $this->extract_topics_data();
    $authority_hook_data = $this->extract_authority_hook_data();
    
    // Check if we have any data
    if (empty($topics_data) && empty($authority_hook_data)) {
        wp_send_json_error([
            'message' => 'No data provided to save',
            'debug_info' => [
                'post_keys' => array_keys($_POST),
                'topics_raw' => $_POST['topics'] ?? 'not set',
                'authority_raw' => $_POST['authority_hook'] ?? 'not set'
            ]
        ]);
        return;
    }
    
    $results = [];
    
    // Save topics directly to post meta (bypass Pods)
    if (!empty($topics_data)) {
        update_post_meta($post_id, '_mkcg_topics_data', $topics_data);
        
        // Also save individual topic fields for compatibility
        foreach ($topics_data as $key => $value) {
            update_post_meta($post_id, '_mkcg_' . $key, $value);
        }
        
        $results['topics'] = [
            'success' => true,
            'message' => 'Topics saved to post meta',
            'count' => count($topics_data)
        ];
        
        error_log('MKCG AJAX DIRECT: Saved ' . count($topics_data) . ' topics to post meta');
    }
    
    // Save authority hook using the service (already uses post meta)
    if (!empty($authority_hook_data)) {
        // Use the existing authority hook service
        global $authority_hook_service;
        if (!$authority_hook_service) {
            require_once dirname(__FILE__) . '/../services/class-mkcg-authority-hook-service.php';
            $authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        $auth_result = $authority_hook_service->save_authority_hook_data($post_id, $authority_hook_data);
        $results['authority_hook'] = $auth_result;
        
        error_log('MKCG AJAX DIRECT: Authority hook save result: ' . json_encode($auth_result));
    }
    
    // Return success
    wp_send_json_success([
        'message' => 'Data saved successfully (direct method)',
        'post_id' => $post_id,
        'results' => $results,
        'debug_info' => [
            'topics_count' => count($topics_data),
            'authority_components' => count($authority_hook_data),
            'method' => 'direct_post_meta'
        ]
    ]);
}

// Also add this to register the direct handler:
// add_action('wp_ajax_mkcg_save_topics_direct', [$this, 'handle_save_topics_direct']);
