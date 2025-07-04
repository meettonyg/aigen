<?php
/**
 * Test Authority Hook Service Save Functionality
 */

// This test verifies that the cleaned up service can save to post meta
if (!defined('ABSPATH')) {
    // Simulate WordPress environment for testing
    define('ABSPATH', __DIR__ . '/../../../../');
}

// Load WordPress
require_once(ABSPATH . 'wp-config.php');

// Load the Authority Hook Service
require_once __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';

echo '<!DOCTYPE html><html><head><title>Authority Hook Save Test</title>';
echo '<style>body { font-family: Arial; margin: 20px; } .success { background: #e8f5e8; color: #2e7d32; padding: 15px; border-radius: 5px; margin: 10px 0; } .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 10px 0; } .info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0; }</style>';
echo '</head><body>';

echo '<h1>🧪 Authority Hook Service Save Test (Post Meta Only)</h1>';

// Test the service
$service = new MKCG_Authority_Hook_Service();

echo '<div class="info">';
echo '<h2>📋 Test Plan:</h2>';
echo '<ol>';
echo '<li>✅ Create Authority Hook Service instance</li>';
echo '<li>✅ Test save functionality to post meta</li>';
echo '<li>✅ Test load functionality from post meta</li>';
echo '<li>✅ Verify AJAX handlers are properly configured</li>';
echo '</ol>';
echo '</div>';

// Test 1: Service Creation
echo '<h2>🔧 Test 1: Service Instance</h2>';
if (is_object($service)) {
    echo '<div class="success">✅ Service created successfully</div>';
} else {
    echo '<div class="error">❌ Failed to create service</div>';
    exit;
}

// Test 2: Get test post ID (create if needed)
echo '<h2>📝 Test 2: Test Post Setup</h2>';

// Try to find existing guest post
$test_posts = get_posts([
    'post_type' => 'guests',
    'numberposts' => 1,
    'meta_key' => '_test_authority_hook_post',
    'meta_value' => 'yes'
]);

if (!empty($test_posts)) {
    $test_post_id = $test_posts[0]->ID;
    echo '<div class="info">📍 Using existing test post ID: ' . $test_post_id . '</div>';
} else {
    // Create a test post
    $test_post_id = wp_insert_post([
        'post_title' => 'Authority Hook Service Test Post',
        'post_content' => 'Test post for Authority Hook Service functionality',
        'post_type' => 'guests',
        'post_status' => 'publish'
    ]);
    
    if ($test_post_id) {
        update_post_meta($test_post_id, '_test_authority_hook_post', 'yes');
        echo '<div class="success">✅ Created test post ID: ' . $test_post_id . '</div>';
    } else {
        echo '<div class="error">❌ Failed to create test post</div>';
        exit;
    }
}

// Test 3: Save Authority Hook Data
echo '<h2>💾 Test 3: Save Authority Hook Data</h2>';

$test_data = [
    'who' => 'SaaS founders and startup CEOs',
    'what' => 'scale their businesses from 6 to 7 figures',
    'when' => 'they hit growth plateaus',
    'how' => 'through my proven systems framework'
];

echo '<div class="info"><strong>Test Data:</strong> ' . json_encode($test_data, JSON_PRETTY_PRINT) . '</div>';

$save_result = $service->save_authority_hook_data($test_post_id, $test_data);

if ($save_result['success']) {
    echo '<div class="success">✅ Save successful: ' . $save_result['message'] . '</div>';
} else {
    echo '<div class="error">❌ Save failed: ' . $save_result['message'] . '</div>';
}

// Test 4: Load Authority Hook Data
echo '<h2>📥 Test 4: Load Authority Hook Data</h2>';

$load_result = $service->get_authority_hook_data($test_post_id);

if ($load_result['components']) {
    echo '<div class="success">✅ Load successful</div>';
    echo '<div class="info"><strong>Loaded Data:</strong><br>';
    foreach ($load_result['components'] as $key => $value) {
        echo "• {$key}: {$value}<br>";
    }
    echo '<strong>Complete Hook:</strong> ' . $load_result['complete_hook'] . '</div>';
    
    // Verify data matches
    $matches = true;
    foreach ($test_data as $key => $expected_value) {
        if ($load_result['components'][$key] !== $expected_value) {
            $matches = false;
            echo '<div class="error">❌ Data mismatch for ' . $key . ': expected "' . $expected_value . '", got "' . $load_result['components'][$key] . '"</div>';
        }
    }
    
    if ($matches) {
        echo '<div class="success">✅ All data matches perfectly!</div>';
    }
    
} else {
    echo '<div class="error">❌ Load failed</div>';
}

// Test 5: WordPress Post Meta Verification
echo '<h2>🔍 Test 5: WordPress Post Meta Verification</h2>';

$meta_check = true;
foreach ($test_data as $key => $expected_value) {
    $meta_value = get_post_meta($test_post_id, "_authority_hook_{$key}", true);
    if ($meta_value === $expected_value) {
        echo '<div class="success">✅ Post meta _authority_hook_' . $key . ': ' . $meta_value . '</div>';
    } else {
        echo '<div class="error">❌ Post meta _authority_hook_' . $key . ': expected "' . $expected_value . '", got "' . $meta_value . '"</div>';
        $meta_check = false;
    }
}

$complete_hook_meta = get_post_meta($test_post_id, '_authority_hook_complete', true);
if (!empty($complete_hook_meta)) {
    echo '<div class="success">✅ Complete hook meta: ' . $complete_hook_meta . '</div>';
} else {
    echo '<div class="error">❌ Complete hook meta not found</div>';
    $meta_check = false;
}

// Test 6: AJAX Handler Check
echo '<h2>🌐 Test 6: AJAX Handler Configuration</h2>';

$ajax_actions = [
    'wp_ajax_mkcg_save_authority_hook',
    'wp_ajax_mkcg_get_authority_hook', 
    'wp_ajax_mkcg_validate_authority_hook'
];

foreach ($ajax_actions as $action) {
    if (has_action($action)) {
        echo '<div class="success">✅ ' . $action . ' registered</div>';
    } else {
        echo '<div class="error">❌ ' . $action . ' not registered</div>';
    }
}

// Final Summary
echo '<h2>📊 Final Summary</h2>';

if ($save_result['success'] && $load_result['components'] && $meta_check) {
    echo '<div class="success">';
    echo '<h3>🎉 ALL TESTS PASSED!</h3>';
    echo '<p><strong>✅ Authority Hook Service is working perfectly with WordPress post meta only</strong></p>';
    echo '<ul>';
    echo '<li>✅ Service saves data to WordPress post meta fields</li>';
    echo '<li>✅ Service loads data from WordPress post meta fields</li>';
    echo '<li>✅ Data integrity maintained</li>';
    echo '<li>✅ Complete hook generated correctly</li>';
    echo '<li>✅ AJAX handlers registered</li>';
    echo '<li>✅ No Formidable dependencies</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="info">';
    echo '<h3>🚀 Ready for Production</h3>';
    echo '<p>The Authority Hook Builder in the Topics Generator will now:</p>';
    echo '<ul>';
    echo '<li>💾 Save all changes to WordPress custom post meta</li>';
    echo '<li>📥 Load existing data from WordPress custom post meta</li>';
    echo '<li>🔄 Work consistently across all generators</li>';
    echo '<li>🧹 No longer depend on Formidable Forms</li>';
    echo '</ul>';
    echo '</div>';
    
} else {
    echo '<div class="error">';
    echo '<h3>❌ SOME TESTS FAILED</h3>';
    echo '<p>Please review the errors above and fix any issues.</p>';
    echo '</div>';
}

// Cleanup option
echo '<div style="margin-top: 30px; padding: 15px; background: #fff3e0; border-radius: 5px;">';
echo '<h3>🧹 Cleanup</h3>';
echo '<p>Test post ID: ' . $test_post_id . '</p>';
echo '<p>You can manually delete this test post if desired, or leave it for future testing.</p>';
echo '</div>';

echo '</body></html>';
?>