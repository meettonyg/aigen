<?php
/**
 * WordPress Admin Test Runner
 * Add this as a temporary admin page to run the root level simplification test
 */

// Add admin menu item for testing
add_action('admin_menu', 'mkcg_add_test_menu');

function mkcg_add_test_menu() {
    add_management_page(
        'MKCG Root Test',
        'MKCG Root Test', 
        'manage_options',
        'mkcg-root-test',
        'mkcg_render_test_page'
    );
}

function mkcg_render_test_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    echo '<div class="wrap">';
    echo '<style>body { font-family: monospace; } .test-output { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; margin: 20px 0; }</style>';
    echo '<h1>🔧 MKCG Root Level Simplification Test</h1>';
    echo '<div class="test-output">';
    
    // Include and run the test
    include __DIR__ . '/test-root-level-simplification.php';
    
    echo '</div>';
    echo '</div>';
}

// Auto-run when this file is included
if (is_admin()) {
    // File is being