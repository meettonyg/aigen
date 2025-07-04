<?php
/**
 * Quick test to verify Authority Hook Service fix
 */

// This test simulates the Topics Generator page setup
if (!defined('ABSPATH')) {
    // Simulate WordPress environment for testing
    define('ABSPATH', __DIR__ . '/../../../../');
}

// Load WordPress
require_once(ABSPATH . 'wp-config.php');

// Load the Authority Hook Service
require_once __DIR__ . '/includes/services/class-mkcg-authority-hook-service.php';

echo '<!DOCTYPE html><html><head><title>Authority Hook Service Test</title>';
echo '<style>body { font-family: Arial; margin: 20px; } .authority-hook { border: 1px solid #ddd; padding: 20px; } .tabs input[type="radio"] { display: none; } .tabs label { cursor: pointer; padding: 10px; margin-right: 5px; background: #f0f0f0; border: 1px solid #ddd; } .tabs input[type="radio"]:checked + label { background: #2196f3; color: white; } .tabs__panel { display: none; padding: 20px; border: 1px solid #ddd; } .tabs input[type="radio"]:checked ~ .tabs__panel { display: block; } .field__input { width: 100%; padding: 10px; border: 1px solid #ddd; margin: 10px 0; }</style>';
echo '</head><body>';

echo '<h1>🧪 Authority Hook Service Fix Test</h1>';

// Test the service
$service = new MKCG_Authority_Hook_Service();

$test_values = [
    'who' => '2nd value, Authors launching a book',
    'what' => 'achieve their goals',
    'when' => 'they need help', 
    'how' => 'through your method'
];

$options = [
    'show_preview' => false,
    'show_examples' => true,
    'show_audience_manager' => true,
    'css_classes' => 'authority-hook',
    'field_prefix' => 'mkcg-',
    'tabs_enabled' => true
];

echo '<h2>✅ Service Test Results:</h2>';
echo '<div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;">';
echo '<strong>✅ Service Instance:</strong> ' . (is_object($service) ? 'Created successfully' : 'Failed to create') . '<br>';
echo '<strong>✅ Test Values:</strong> ' . json_encode($test_values) . '<br>';
echo '<strong>✅ Expected Field IDs:</strong> mkcg-who, mkcg-result, mkcg-when, mkcg-how<br>';
echo '</div>';

echo '<h2>🎯 Rendered Authority Hook Builder:</h2>';
try {
    $html = $service->render_authority_hook_builder('topics', $test_values, $options);
    
    if (!empty($html)) {
        echo '<div style="background: #e8f5e8; padding: 10px; border-radius: 5px; margin-bottom: 10px;">';
        echo '<strong>✅ SUCCESS:</strong> Service generated ' . strlen($html) . ' characters of HTML';
        echo '</div>';
        
        echo $html;
        
        // Check for critical elements
        echo '<h2>🔍 HTML Validation:</h2>';
        echo '<div style="background: #f0f8ff; padding: 15px; border-radius: 5px;">';
        echo '<strong>Field ID Tests:</strong><br>';
        echo '• mkcg-who: ' . (strpos($html, 'id="mkcg-who"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• mkcg-result: ' . (strpos($html, 'id="mkcg-result"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• mkcg-when: ' . (strpos($html, 'id="mkcg-when"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• mkcg-how: ' . (strpos($html, 'id="mkcg-how"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• tag_input: ' . (strpos($html, 'id="tag_input"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• tags_container: ' . (strpos($html, 'id="tags_container"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        
        echo '<br><strong>Tab Tests:</strong><br>';
        echo '• WHO tab: ' . (strpos($html, 'id="tabwho"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• RESULT tab: ' . (strpos($html, 'id="tabresult"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• WHEN tab: ' . (strpos($html, 'id="tabwhen"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '• HOW tab: ' . (strpos($html, 'id="tabhow"') !== false ? '✅ Found' : '❌ Not Found') . '<br>';
        echo '</div>';
        
    } else {
        echo '<div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px;">';
        echo '<strong>❌ ERROR:</strong> Service returned empty HTML';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px;">';
    echo '<strong>❌ ERROR:</strong> ' . $e->getMessage();
    echo '</div>';
}

echo '<h2>📋 Next Steps:</h2>';
echo '<div style="background: #fff3e0; padding: 15px; border-radius: 5px;">';
echo '<p><strong>If all tests pass above:</strong></p>';
echo '<ol>';
echo '<li>✅ Refresh your Topics Generator page: <a href="/topics/?post_id=32372" target="_blank">guestify.ai/topics/?post_id=32372</a></li>';
echo '<li>✅ The Authority Hook Builder should now have working content in all tabs</li>';
echo '<li>✅ JavaScript functionality should work properly (audience manager, examples, etc.)</li>';
echo '<li>✅ You can then remove this test file</li>';
echo '</ol>';
echo '</div>';

echo '</body></html>';
?>