<?php
/**
 * ROOT LEVEL DEFAULT VALUES FIX - VERIFICATION SCRIPT
 * 
 * This script verifies that the default values have been eliminated at the source
 * Run this after applying the ROOT LEVEL FIX to confirm the changes work
 * 
 * Test URL: /your-wordpress-site/wp-content/plugins/media-kit-content-generator/ROOT-LEVEL-DEFAULT-VALUES-FIX-VERIFICATION.php?post_id=32372
 */

// Security check
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
}

// Set up proper headers
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>ROOT LEVEL DEFAULT VALUES FIX - VERIFICATION</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f1f1f1; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1200px; }
        .success { color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #c62828; background: #ffebee; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #f57c00; background: #fff3e0; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #1976d2; background: #e3f2fd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .section { border: 1px solid #ddd; margin: 20px 0; padding: 15px; border-radius: 4px; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 4px; white-space: pre-wrap; font-family: Consolas, monospace; }
        .comparison { display: flex; gap: 20px; }
        .before, .after { flex: 1; }
        .before { background: #ffebee; border-left: 4px solid #f44336; }
        .after { background: #e8f5e9; border-left: 4px solid #4caf50; }
        h1, h2, h3 { color: #333; }
        .debug-data { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 ROOT LEVEL DEFAULT VALUES FIX - VERIFICATION</h1>
    <p><strong>Purpose:</strong> Verify that the default placeholder values have been eliminated at the source</p>
    <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <?php
    
    // Get post ID from query string
    $test_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 32372;
    
    echo "<div class='info'><strong>Testing with Post ID:</strong> {$test_post_id}</div>";
    
    // Check if required files exist
    $pods_service_file = dirname(__FILE__) . '/includes/services/class-mkcg-pods-service.php';
    $authority_hook_service_file = dirname(__FILE__) . '/includes/services/class-mkcg-authority-hook-service.php';
    
    echo "<div class='section'>";
    echo "<h2>📁 File Existence Check</h2>";
    
    if (file_exists($pods_service_file)) {
        echo "<div class='success'>✅ MKCG Pods Service file found</div>";
    } else {
        echo "<div class='error'>❌ MKCG Pods Service file not found: {$pods_service_file}</div>";
        exit;
    }
    
    if (file_exists($authority_hook_service_file)) {
        echo "<div class='success'>✅ MKCG Authority Hook Service file found</div>";
    } else {
        echo "<div class='error'>❌ MKCG Authority Hook Service file not found: {$authority_hook_service_file}</div>";
        exit;
    }
    
    echo "</div>";
    
    // Load the services
    require_once $pods_service_file;
    require_once $authority_hook_service_file;
    
    echo "<div class='section'>";
    echo "<h2>🔧 Service Loading Check</h2>";
    
    if (class_exists('MKCG_Pods_Service')) {
        echo "<div class='success'>✅ MKCG Pods Service class loaded</div>";
        $pods_service = new MKCG_Pods_Service();
    } else {
        echo "<div class='error'>❌ MKCG Pods Service class not found</div>";
        exit;
    }
    
    if (class_exists('MKCG_Authority_Hook_Service')) {
        echo "<div class='success'>✅ MKCG Authority Hook Service class loaded</div>";
        $authority_hook_service = new MKCG_Authority_Hook_Service();
    } else {
        echo "<div class='error'>❌ MKCG Authority Hook Service class not found</div>";
        exit;
    }
    
    echo "</div>";
    
    // Test 1: Check Authority Hook Service directly
    echo "<div class='section'>";
    echo "<h2>🧪 Test 1: Authority Hook Service Direct Test</h2>";
    
    $authority_hook_data = $authority_hook_service->get_authority_hook_data($test_post_id);
    
    echo "<div class='debug-data'>";
    echo "<strong>Authority Hook Service Response:</strong><br>";
    echo "<div class='code'>" . htmlspecialchars(print_r($authority_hook_data, true)) . "</div>";
    echo "</div>";
    
    $authority_components = $authority_hook_data['components'] ?? [];
    $has_authority_defaults = false;
    $default_values = ['your audience', 'achieve their goals', 'they need help', 'through your method'];
    
    foreach ($authority_components as $key => $value) {
        if (in_array($value, $default_values)) {
            $has_authority_defaults = true;
            break;
        }
    }
    
    if ($has_authority_defaults) {
        echo "<div class='fail'>❌ Authority Hook Service still contains default values</div>";
    } else {
        echo "<div class='pass'>✅ Authority Hook Service contains no default values</div>";
    }
    
    echo "</div>";
    
    // Test 2: Check Pods Service
    echo "<div class='section'>";
    echo "<h2>🧪 Test 2: Pods Service Authority Hook Test</h2>";
    
    $pods_authority_components = $pods_service->get_authority_hook_components($test_post_id);
    
    echo "<div class='debug-data'>";
    echo "<strong>Pods Service Authority Hook Response:</strong><br>";
    echo "<div class='code'>" . htmlspecialchars(print_r($pods_authority_components, true)) . "</div>";
    echo "</div>";
    
    $has_pods_defaults = false;
    foreach ($pods_authority_components as $key => $value) {
        if (in_array($value, $default_values)) {
            $has_pods_defaults = true;
            break;
        }
    }
    
    if ($has_pods_defaults) {
        echo "<div class='fail'>❌ Pods Service still contains default values</div>";
    } else {
        echo "<div class='pass'>✅ Pods Service contains no default values</div>";
    }
    
    echo "</div>";
    
    // Test 3: Full Guest Data Test
    echo "<div class='section'>";
    echo "<h2>🧪 Test 3: Full Guest Data Test (What Template Receives)</h2>";
    
    $guest_data = $pods_service->get_guest_data($test_post_id);
    
    echo "<div class='debug-data'>";
    echo "<strong>Full Guest Data (Template Data):</strong><br>";
    echo "<div class='code'>" . htmlspecialchars(print_r($guest_data, true)) . "</div>";
    echo "</div>";
    
    $template_authority_components = $guest_data['authority_hook_components'] ?? [];
    $has_template_defaults = false;
    foreach ($template_authority_components as $key => $value) {
        if (in_array($value, $default_values)) {
            $has_template_defaults = true;
            break;
        }
    }
    
    if ($has_template_defaults) {
        echo "<div class='fail'>❌ Template data still contains default values</div>";
    } else {
        echo "<div class='pass'>✅ Template data contains no default values</div>";
    }
    
    echo "</div>";
    
    // Test 4: Check WordPress Post Meta Directly
    echo "<div class='section'>";
    echo "<h2>🧪 Test 4: WordPress Post Meta Direct Check</h2>";
    
    $meta_fields = [
        'guest_title' => get_post_meta($test_post_id, 'guest_title', true),
        'hook_what' => get_post_meta($test_post_id, 'hook_what', true),
        'hook_when' => get_post_meta($test_post_id, 'hook_when', true),
        'hook_how' => get_post_meta($test_post_id, 'hook_how', true)
    ];
    
    echo "<div class='debug-data'>";
    echo "<strong>WordPress Post Meta Fields:</strong><br>";
    echo "<div class='code'>" . htmlspecialchars(print_r($meta_fields, true)) . "</div>";
    echo "</div>";
    
    $has_meta_data = false;
    foreach ($meta_fields as $field => $value) {
        if (!empty($value)) {
            $has_meta_data = true;
            break;
        }
    }
    
    if ($has_meta_data) {
        echo "<div class='info'>ℹ️ Post meta contains some data - services should use this data</div>";
    } else {
        echo "<div class='info'>ℹ️ Post meta is empty - services should return empty values (not defaults)</div>";
    }
    
    echo "</div>";
    
    // Test 5: Compare Before/After Results
    echo "<div class='section'>";
    echo "<h2>📊 Test 5: Before/After Comparison</h2>";
    
    echo "<div class='comparison'>";
    
    echo "<div class='before'>";
    echo "<h3>❌ BEFORE (Expected Old Behavior)</h3>";
    echo "<div class='code'>";
    echo "authority_hook_components: {\n";
    echo "  who: 'your audience',\n";
    echo "  what: 'achieve their goals',\n";
    echo "  when: 'they need help',\n";
    echo "  how: 'through your method'\n";
    echo "}";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='after'>";
    echo "<h3>✅ AFTER (Expected New Behavior)</h3>";
    echo "<div class='code'>";
    echo "authority_hook_components: {\n";
    echo "  who: '',\n";
    echo "  what: '',\n";
    echo "  when: '',\n";
    echo "  how: ''\n";
    echo "}";
    echo "</div>";
    echo "</div>";
    
    echo "</div>";
    
    echo "</div>";
    
    // Test 6: JavaScript Data Simulation
    echo "<div class='section'>";
    echo "<h2>🧪 Test 6: JavaScript Data Simulation</h2>";
    echo "<p>This simulates what JavaScript would receive via window.MKCG_Topics_Data</p>";
    
    // Simulate the generator workflow
    if (class_exists('Enhanced_Topics_Generator')) {
        echo "<div class='info'>ℹ️ Enhanced_Topics_Generator class is available</div>";
        
        // Load the API service (mock)
        $api_service = null; // Would normally be loaded
        
        try {
            $generator = new Enhanced_Topics_Generator($api_service);
            $template_data = $generator->get_template_data($test_post_id);
            
            echo "<div class='debug-data'>";
            echo "<strong>Simulated JavaScript Data (window.MKCG_Topics_Data):</strong><br>";
            echo "<div class='code'>";
            echo "window.MKCG_Topics_Data = {\n";
            echo "  postId: " . ($template_data['post_id'] ?? 0) . ",\n";
            echo "  hasData: " . (($template_data['has_data'] ?? false) ? 'true' : 'false') . ",\n";
            echo "  authorityHook: {\n";
            $auth_components = $template_data['authority_hook_components'] ?? [];
            echo "    who: '" . htmlspecialchars($auth_components['who'] ?? '') . "',\n";
            echo "    what: '" . htmlspecialchars($auth_components['what'] ?? '') . "',\n";
            echo "    when: '" . htmlspecialchars($auth_components['when'] ?? '') . "',\n";
            echo "    how: '" . htmlspecialchars($auth_components['how'] ?? '') . "'\n";
            echo "  }\n";
            echo "}";
            echo "</div>";
            echo "</div>";
            
            $js_has_defaults = false;
            foreach ($auth_components as $key => $value) {
                if (in_array($value, $default_values)) {
                    $js_has_defaults = true;
                    break;
                }
            }
            
            if ($js_has_defaults) {
                echo "<div class='fail'>❌ JavaScript would still receive default values</div>";
            } else {
                echo "<div class='pass'>✅ JavaScript would receive empty values (no defaults)</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='warning'>⚠️ Could not simulate generator: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='warning'>⚠️ Enhanced_Topics_Generator class not available for simulation</div>";
    }
    
    echo "</div>";
    
    // Summary
    echo "<div class='section'>";
    echo "<h2>📋 VERIFICATION SUMMARY</h2>";
    
    $tests_passed = 0;
    $total_tests = 4;
    
    // Test results
    $test_results = [
        'Authority Hook Service Clean' => !$has_authority_defaults,
        'Pods Service Clean' => !$has_pods_defaults,
        'Template Data Clean' => !$has_template_defaults,
        'Overall Architecture Fixed' => !$has_authority_defaults && !$has_pods_defaults && !$has_template_defaults
    ];
    
    echo "<table>";
    echo "<thead><tr><th>Test</th><th>Result</th><th>Status</th></tr></thead>";
    echo "<tbody>";
    
    foreach ($test_results as $test_name => $passed) {
        if ($passed) $tests_passed++;
        $status_class = $passed ? 'pass' : 'fail';
        $status_icon = $passed ? '✅' : '❌';
        $status_text = $passed ? 'PASS' : 'FAIL';
        
        echo "<tr class='{$status_class}'>";
        echo "<td>{$test_name}</td>";
        echo "<td>{$status_icon} {$status_text}</td>";
        echo "<td>" . ($passed ? 'Default values eliminated' : 'Default values still present') . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    
    if ($tests_passed === $total_tests) {
        echo "<div class='success'>";
        echo "<h3>🎉 SUCCESS: ROOT LEVEL FIX COMPLETE!</h3>";
        echo "<p>All tests passed. The default values have been successfully eliminated at the source.</p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Clear any WordPress caches</li>";
        echo "<li>✅ Test the Topics Generator in your browser</li>";
        echo "<li>✅ Verify JavaScript console shows empty values instead of defaults</li>";
        echo "<li>✅ Test save functionality to ensure it works with empty defaults</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ PARTIAL SUCCESS: {$tests_passed}/{$total_tests} Tests Passed</h3>";
        echo "<p>Some issues remain. Please check the failed tests above and apply additional fixes as needed.</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Debug Information
    echo "<div class='section'>";
    echo "<h2>🔍 Debug Information</h2>";
    
    echo "<div class='debug-data'>";
    echo "<strong>WordPress Environment:</strong><br>";
    echo "WordPress Version: " . get_bloginfo('version') . "<br>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "Post ID Tested: {$test_post_id}<br>";
    echo "Post Exists: " . (get_post($test_post_id) ? 'Yes' : 'No') . "<br>";
    if (get_post($test_post_id)) {
        echo "Post Type: " . get_post_type($test_post_id) . "<br>";
        echo "Post Title: " . get_the_title($test_post_id) . "<br>";
    }
    echo "Test Run Time: " . date('Y-m-d H:i:s') . "<br>";
    echo "</div>";
    
    echo "</div>";
    
    ?>
    
    <div class="section">
        <h2>🔄 Manual Testing</h2>
        <p>To manually test different post IDs, use:</p>
        <div class="code">
            <?php echo $_SERVER['REQUEST_URI']; ?>?post_id=YOUR_POST_ID
        </div>
        
        <p><strong>Common test cases:</strong></p>
        <ul>
            <li><a href="?post_id=0">Test with Post ID 0 (no post)</a></li>
            <li><a href="?post_id=32372">Test with Post ID 32372 (your original test case)</a></li>
            <li><a href="?post_id=1">Test with Post ID 1 (usually a default post)</a></li>
        </ul>
    </div>
    
</div>

</body>
</html>

<?php
// Log the test results for debugging
error_log('MKCG ROOT LEVEL FIX VERIFICATION: Tests passed: ' . $tests_passed . '/' . $total_tests);
if (isset($test_results)) {
    foreach ($test_results as $test => $result) {
        error_log("MKCG VERIFICATION - {$test}: " . ($result ? 'PASS' : 'FAIL'));
    }
}
?>
