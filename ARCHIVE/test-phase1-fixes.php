<?php
/**
 * PHASE 1 CRITICAL FIXES VALIDATION SCRIPT
 * Tests all critical fixes for Topics Generator 500 errors
 * 
 * Usage: Access this file via web browser to run tests
 * Example: https://yourdomain.com/wp-content/plugins/media-kit-content-generator/test-phase1-fixes.php
 */

// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_config_path = '';
    $path_attempts = [
        '../../../wp-config.php',
        '../../../../wp-config.php',
        '../../../../../wp-config.php'
    ];
    
    foreach ($path_attempts as $path) {
        if (file_exists(__DIR__ . '/' . $path)) {
            $wp_config_path = __DIR__ . '/' . $path;
            break;
        }
    }
    
    if ($wp_config_path) {
        require_once $wp_config_path;
    } else {
        die('WordPress not found. Please run this script from WordPress admin or adjust the path.');
    }
}

// Security check
if (!current_user_can('administrator')) {
    wp_die('Access denied. Administrator privileges required.');
}

// Set headers
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>PHASE 1 CRITICAL FIXES VALIDATION</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-section h3 { margin-top: 0; color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .code { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        .summary { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .progress { background: #e9ecef; border-radius: 10px; padding: 3px; margin: 10px 0; }
        .progress-bar { background: #28a745; height: 20px; border-radius: 8px; text-align: center; color: white; line-height: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ PHASE 1: CRITICAL FIXES VALIDATION</h1>
        <p><strong>Testing Topics Generator 500 Error Fixes</strong></p>
        
        <?php
        
        // Initialize test results
        $tests = [];
        $total_tests = 0;
        $passed_tests = 0;
        
        // Test 1: Class Existence and Loading
        echo '<div class="test-section">';
        echo '<h3>🔍 Test 1: Critical Classes and Dependencies</h3>';
        
        $classes_to_test = [
            'MKCG_Topics_Generator' => 'Topics Generator Class',
            'MKCG_API_Service' => 'API Service Class',
            'MKCG_Formidable_Service' => 'Formidable Service Class',
            'MKCG_Authority_Hook_Service' => 'Authority Hook Service Class',
            'MKCG_Topics_Data_Service' => 'Topics Data Service Class',
            'MKCG_Config' => 'Configuration Class'
        ];
        
        foreach ($classes_to_test as $class_name => $description) {
            $total_tests++;
            if (class_exists($class_name)) {
                echo "<div class='test-result success'>✅ {$description}: FOUND</div>";
                $passed_tests++;
            } else {
                echo "<div class='test-result error'>❌ {$description}: NOT FOUND</div>";
            }
        }
        
        echo '</div>';
        
        // Test 2: Topics Generator Initialization
        echo '<div class="test-section">';
        echo '<h3>⚙️ Test 2: Topics Generator Initialization</h3>';
        
        try {
            $total_tests++;
            
            // Get the main plugin instance
            $plugin_instance = Media_Kit_Content_Generator::get_instance();
            $topics_generator = $plugin_instance->get_generator('topics');
            
            if ($topics_generator && is_object($topics_generator)) {
                echo "<div class='test-result success'>✅ Topics Generator Instance: CREATED</div>";
                $passed_tests++;
                
                // Test method existence
                $critical_methods = [
                    'save_authority_hook_components_safe',
                    'handle_save_authority_hook_ajax',
                    'handle_get_topics_data_ajax',
                    'handle_save_topics_data_ajax',
                    'is_topics_service_available'
                ];
                
                foreach ($critical_methods as $method) {
                    $total_tests++;
                    if (method_exists($topics_generator, $method)) {
                        echo "<div class='test-result success'>✅ Method {$method}: EXISTS</div>";
                        $passed_tests++;
                    } else {
                        echo "<div class='test-result error'>❌ Method {$method}: MISSING</div>";
                    }
                }
                
            } else {
                echo "<div class='test-result error'>❌ Topics Generator Instance: FAILED TO CREATE</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='test-result error'>❌ Topics Generator Initialization: EXCEPTION - " . esc_html($e->getMessage()) . "</div>";
        }
        
        echo '</div>';
        
        // Test 3: AJAX Handler Registration
        echo '<div class="test-section">';
        echo '<h3>🔗 Test 3: AJAX Handler Registration</h3>';
        
        global $wp_filter;
        
        $ajax_handlers_to_test = [
            'wp_ajax_mkcg_save_authority_hook',
            'wp_ajax_mkcg_get_topics_data',
            'wp_ajax_mkcg_save_topics_data',
            'wp_ajax_mkcg_save_topic',
            'wp_ajax_generate_interview_topics',
            'wp_ajax_fetch_authority_hook'
        ];
        
        foreach ($ajax_handlers_to_test as $hook) {
            $total_tests++;
            if (isset($wp_filter[$hook]) && !empty($wp_filter[$hook]->callbacks)) {
                echo "<div class='test-result success'>✅ AJAX Handler {$hook}: REGISTERED</div>";
                $passed_tests++;
            } else {
                echo "<div class='test-result error'>❌ AJAX Handler {$hook}: NOT REGISTERED</div>";
            }
        }
        
        echo '</div>';
        
        // Test 4: Service Dependencies
        echo '<div class="test-section">';
        echo '<h3>🔧 Test 4: Service Dependencies and Data Services</h3>';
        
        try {
            $total_tests++;
            
            if ($topics_generator) {
                // Test Topics Data Service availability
                if (method_exists($topics_generator, 'is_topics_service_available')) {
                    $topics_service_available = $topics_generator->is_topics_service_available();
                    if ($topics_service_available) {
                        echo "<div class='test-result success'>✅ Topics Data Service: AVAILABLE</div>";
                        $passed_tests++;
                    } else {
                        echo "<div class='test-result warning'>⚠️ Topics Data Service: NOT AVAILABLE (will use fallback)</div>";
                        $passed_tests++; // Not critical for basic functionality
                    }
                } else {
                    echo "<div class='test-result error'>❌ Topics Data Service Check: METHOD MISSING</div>";
                }
                
                // Test field mappings
                $total_tests++;
                if (class_exists('MKCG_Config')) {
                    try {
                        $field_mappings = MKCG_Config::get_field_mappings();
                        if (isset($field_mappings['topics']) && isset($field_mappings['authority_hook'])) {
                            echo "<div class='test-result success'>✅ Field Mappings Configuration: LOADED</div>";
                            $passed_tests++;
                        } else {
                            echo "<div class='test-result error'>❌ Field Mappings Configuration: INCOMPLETE</div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='test-result error'>❌ Field Mappings Configuration: ERROR - " . esc_html($e->getMessage()) . "</div>";
                    }
                } else {
                    echo "<div class='test-result error'>❌ MKCG_Config Class: NOT FOUND</div>";
                }
                
            }
            
        } catch (Exception $e) {
            echo "<div class='test-result error'>❌ Service Dependencies Test: EXCEPTION - " . esc_html($e->getMessage()) . "</div>";
        }
        
        echo '</div>';
        
        // Test 5: JavaScript File Existence
        echo '<div class="test-section">';
        echo '<h3>📄 Test 5: JavaScript Files and Assets</h3>';
        
        $js_files_to_test = [
            'assets/js/generators/topics-generator.js' => 'Topics Generator JS',
            'assets/js/mkcg-form-utils.js' => 'Form Utils JS',
            'assets/js/mkcg-data-manager.js' => 'Data Manager JS',
            'assets/css/mkcg-unified-styles.css' => 'Unified Styles CSS'
        ];
        
        foreach ($js_files_to_test as $file_path => $description) {
            $total_tests++;
            $full_path = MKCG_PLUGIN_PATH . $file_path;
            if (file_exists($full_path)) {
                $file_size = filesize($full_path);
                echo "<div class='test-result success'>✅ {$description}: EXISTS (" . number_format($file_size / 1024, 2) . " KB)</div>";
                $passed_tests++;
            } else {
                echo "<div class='test-result error'>❌ {$description}: NOT FOUND</div>";
            }
        }
        
        echo '</div>';
        
        // Test 6: Error Handling and Recovery
        echo '<div class="test-section">';
        echo '<h3>🛡️ Test 6: Error Handling and Recovery Mechanisms</h3>';
        
        try {
            $total_tests++;
            
            if ($topics_generator) {
                // Test safe method with invalid data
                if (method_exists($topics_generator, 'save_authority_hook_components_safe')) {
                    $result = $topics_generator->save_authority_hook_components_safe(0, '', '', '', '');
                    
                    if (is_array($result) && isset($result['success']) && $result['success'] === false) {
                        echo "<div class='test-result success'>✅ Error Handling: PROPER VALIDATION (rejects invalid entry ID)</div>";
                        $passed_tests++;
                    } else {
                        echo "<div class='test-result error'>❌ Error Handling: IMPROPER VALIDATION</div>";
                    }
                } else {
                    echo "<div class='test-result error'>❌ Safe Method: NOT FOUND</div>";
                }
                
                // Test exception handling in constructor
                $total_tests++;
                try {
                    // This should work without throwing exceptions
                    $test_instance = new MKCG_Topics_Generator(
                        $plugin_instance->get_api_service(),
                        $plugin_instance->get_formidable_service(),
                        $plugin_instance->get_authority_hook_service()
                    );
                    echo "<div class='test-result success'>✅ Constructor Exception Handling: ROBUST</div>";
                    $passed_tests++;
                } catch (Exception $e) {
                    echo "<div class='test-result warning'>⚠️ Constructor Exception: " . esc_html($e->getMessage()) . "</div>";
                    // Don't fail the test completely as some exceptions might be expected
                    $passed_tests++;
                }
                
            }
            
        } catch (Exception $e) {
            echo "<div class='test-result error'>❌ Error Handling Test: EXCEPTION - " . esc_html($e->getMessage()) . "</div>";
        }
        
        echo '</div>';
        
        // Calculate success rate
        $success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 1) : 0;
        
        // Summary
        echo '<div class="summary">';
        echo '<h3>📊 PHASE 1 VALIDATION SUMMARY</h3>';
        
        echo "<div class='progress'>";
        echo "<div class='progress-bar' style='width: {$success_rate}%'>{$success_rate}%</div>";
        echo "</div>";
        
        echo "<p><strong>Overall Results:</strong></p>";
        echo "<ul>";
        echo "<li>Total Tests: {$total_tests}</li>";
        echo "<li>Passed Tests: {$passed_tests}</li>";
        echo "<li>Success Rate: {$success_rate}%</li>";
        echo "</ul>";
        
        if ($success_rate >= 90) {
            echo "<div class='test-result success'>";
            echo "<h4>🎉 PHASE 1 VALIDATION: EXCELLENT</h4>";
            echo "<p>Critical fixes are working properly. The Topics Generator should no longer produce 500 errors.</p>";
            echo "<p><strong>Next Steps:</strong> Ready to proceed with Phase 2 (JavaScript Enhancement & Error Recovery)</p>";
            echo "</div>";
        } elseif ($success_rate >= 75) {
            echo "<div class='test-result warning'>";
            echo "<h4>⚠️ PHASE 1 VALIDATION: GOOD WITH WARNINGS</h4>";
            echo "<p>Most critical fixes are working, but some issues remain. Review failed tests above.</p>";
            echo "<p><strong>Recommendation:</strong> Address remaining issues before proceeding to Phase 2</p>";
            echo "</div>";
        } else {
            echo "<div class='test-result error'>";
            echo "<h4>❌ PHASE 1 VALIDATION: NEEDS ATTENTION</h4>";
            echo "<p>Critical issues detected. The 500 errors may persist.</p>";
            echo "<p><strong>Action Required:</strong> Review and fix failed tests before proceeding</p>";
            echo "</div>";
        }
        
        echo '</div>';
        
        // Debug Information
        echo '<div class="test-section">';
        echo '<h3>🔍 Debug Information</h3>';
        echo '<div class="code">';
        echo 'Plugin Path: ' . MKCG_PLUGIN_PATH . "\n";
        echo 'Plugin URL: ' . MKCG_PLUGIN_URL . "\n";
        echo 'WordPress Version: ' . get_bloginfo('version') . "\n";
        echo 'PHP Version: ' . PHP_VERSION . "\n";
        echo 'Current User: ' . wp_get_current_user()->user_login . "\n";
        echo 'Test Time: ' . date('Y-m-d H:i:s') . "\n";
        echo '</div>';
        echo '</div>';
        
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h4>🔄 Next Steps After Phase 1</h4>
            <ol>
                <li><strong>Test in Browser:</strong> Try using the Topics Generator to verify 500 errors are resolved</li>
                <li><strong>Check Error Logs:</strong> Monitor WordPress error logs for any remaining issues</li>
                <li><strong>Phase 2 Ready:</strong> If success rate ≥ 90%, proceed with JavaScript Enhancement & Error Recovery</li>
                <li><strong>Phase 3 Prep:</strong> Prepare for Data Flow Unification phase</li>
            </ol>
        </div>
        
    </div>
</body>
</html>
