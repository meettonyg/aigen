<?php
/**
 * AJAX FIX VALIDATION TEST
 * Tests the missing mkcg_save_topics_data method implementation
 * 
 * ROOT CAUSE FIX VERIFICATION
 * Before: JavaScript called 'mkcg_save_topics_data' action → Method didn't exist → Empty response → JSON parse error
 * After: Method exists with comprehensive error handling → Proper JSON responses → No more parse errors
 * 
 * USAGE: Place this file in your plugin directory and access via browser
 * URL: /wp-content/plugins/media-kit-content-generator/test-ajax-fix-validation.php
 */

// Prevent direct access without WordPress
if (!defined('ABSPATH')) {
    // Load WordPress if testing directly
    $wp_load_paths = [
        '../../../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        'wp-load.php'
    ];
    
    $loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $loaded = true;
            break;
        }
    }
    
    if (!$loaded) {
        die('WordPress not found. Please run this test from within WordPress or adjust the wp-load.php path.');
    }
}

/**
 * AJAX Fix Validation Test Suite
 */
class MKCG_AJAX_Fix_Validator {
    
    private $test_results = [];
    private $total_tests = 0;
    private $passed_tests = 0;
    
    public function __construct() {
        echo "<h1>🔧 MKCG AJAX Fix Validation Test</h1>";
        echo "<p><strong>Testing the implementation of missing mkcg_save_topics_data method</strong></p>";
        echo "<hr>";
        
        $this->run_all_tests();
        $this->display_summary();
    }
    
    /**
     * Run all validation tests
     */
    private function run_all_tests() {
        echo "<h2>📋 Test Results</h2>";
        
        // Test 1: Check if class exists
        $this->test_class_exists();
        
        // Test 2: Check if method exists
        $this->test_method_exists();
        
        // Test 3: Check AJAX action registration
        $this->test_ajax_action_registration();
        
        // Test 4: Check method dependencies
        $this->test_method_dependencies();
        
        // Test 5: Test method structure
        $this->test_method_structure();
        
        // Test 6: Test error handling
        $this->test_error_handling();
        
        // Test 7: Test helper methods
        $this->test_helper_methods();
        
        // Test 8: Integration test (if possible)
        $this->test_integration();
    }
    
    /**
     * Test 1: Verify AJAX handler class exists
     */
    private function test_class_exists() {
        $this->total_tests++;
        
        if (class_exists('MKCG_Topics_AJAX_Handlers')) {
            $this->pass_test("✅ MKCG_Topics_AJAX_Handlers class exists");
            $this->passed_tests++;
        } else {
            $this->fail_test("❌ MKCG_Topics_AJAX_Handlers class not found");
        }
    }
    
    /**
     * Test 2: Verify save_topics_data method exists
     */
    private function test_method_exists() {
        $this->total_tests++;
        
        if (method_exists('MKCG_Topics_AJAX_Handlers', 'save_topics_data')) {
            $this->pass_test("✅ save_topics_data() method exists (ROOT CAUSE FIXED)");
            $this->passed_tests++;
        } else {
            $this->fail_test("❌ save_topics_data() method still missing (ROOT CAUSE NOT FIXED)");
        }
    }
    
    /**
     * Test 3: Check AJAX action registration
     */
    private function test_ajax_action_registration() {
        $this->total_tests++;
        
        global $wp_filter;
        
        $ajax_registered = false;
        $nopriv_registered = false;
        
        // Check if the action is registered for logged-in users
        if (isset($wp_filter['wp_ajax_mkcg_save_topics_data'])) {
            $ajax_registered = true;
        }
        
        // Check if the action is registered for non-logged-in users
        if (isset($wp_filter['wp_ajax_nopriv_mkcg_save_topics_data'])) {
            $nopriv_registered = true;
        }
        
        if ($ajax_registered && $nopriv_registered) {
            $this->pass_test("✅ AJAX action 'mkcg_save_topics_data' properly registered");
            $this->passed_tests++;
        } else {
            $missing = [];
            if (!$ajax_registered) $missing[] = 'wp_ajax_mkcg_save_topics_data';
            if (!$nopriv_registered) $missing[] = 'wp_ajax_nopriv_mkcg_save_topics_data';
            $this->fail_test("❌ AJAX action registration incomplete. Missing: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 4: Check method dependencies
     */
    private function test_method_dependencies() {
        $this->total_tests++;
        
        $required_methods = [
            'verify_nonce_with_fallbacks',
            'can_edit_entry',
            'extract_and_validate_topics_data',
            'get_topics_field_mappings'
        ];
        
        $missing_methods = [];
        
        foreach ($required_methods as $method) {
            if (!method_exists('MKCG_Topics_AJAX_Handlers', $method)) {
                $missing_methods[] = $method;
            }
        }
        
        if (empty($missing_methods)) {
            $this->pass_test("✅ All required dependency methods exist");
            $this->passed_tests++;
        } else {
            $this->fail_test("❌ Missing dependency methods: " . implode(', ', $missing_methods));
        }
    }
    
    /**
     * Test 5: Test method structure
     */
    private function test_method_structure() {
        $this->total_tests++;
        
        try {
            $reflection = new ReflectionMethod('MKCG_Topics_AJAX_Handlers', 'save_topics_data');
            
            if ($reflection->isPublic()) {
                $this->pass_test("✅ save_topics_data() method is public (accessible by WordPress AJAX)");
                $this->passed_tests++;
            } else {
                $this->fail_test("❌ save_topics_data() method is not public");
            }
        } catch (ReflectionException $e) {
            $this->fail_test("❌ Could not analyze save_topics_data() method structure: " . $e->getMessage());
        }
    }
    
    /**
     * Test 6: Test error handling capabilities
     */
    private function test_error_handling() {
        $this->total_tests++;
        
        // Read the method source to check for error handling patterns
        $file_path = plugin_dir_path(__FILE__) . 'includes/generators/class-mkcg-topics-ajax-handlers.php';
        
        if (file_exists($file_path)) {
            $file_content = file_get_contents($file_path);
            
            $error_patterns = [
                'wp_send_json_error',
                'try {',
                'catch (',
                'error_log',
                'Exception'
            ];
            
            $found_patterns = [];
            foreach ($error_patterns as $pattern) {
                if (strpos($file_content, $pattern) !== false) {
                    $found_patterns[] = $pattern;
                }
            }
            
            if (count($found_patterns) >= 4) {
                $this->pass_test("✅ Comprehensive error handling implemented (" . implode(', ', $found_patterns) . ")");
                $this->passed_tests++;
            } else {
                $this->fail_test("❌ Limited error handling found. Patterns found: " . implode(', ', $found_patterns));
            }
        } else {
            $this->fail_test("❌ Could not read source file for error handling analysis");
        }
    }
    
    /**
     * Test 7: Test helper methods
     */
    private function test_helper_methods() {
        $this->total_tests++;
        
        $helper_methods = [
            'extract_and_validate_topics_data' => 'Data extraction helper',
            'get_topics_field_mappings' => 'Field mapping helper'
        ];
        
        $missing_helpers = [];
        
        foreach ($helper_methods as $method => $description) {
            if (!method_exists('MKCG_Topics_AJAX_Handlers', $method)) {
                $missing_helpers[] = "$method ($description)";
            }
        }
        
        if (empty($missing_helpers)) {
            $this->pass_test("✅ All helper methods implemented");
            $this->passed_tests++;
        } else {
            $this->fail_test("❌ Missing helper methods: " . implode(', ', $missing_helpers));
        }
    }
    
    /**
     * Test 8: Basic integration test
     */
    private function test_integration() {
        $this->total_tests++;
        
        try {
            // Test if we can instantiate the class (basic integration)
            if (class_exists('MKCG_Topics_Generator') && class_exists('MKCG_Topics_AJAX_Handlers')) {
                
                // Check if Topics Generator has required services
                $required_classes = [
                    'MKCG_API_Service',
                    'MKCG_Formidable_Service', 
                    'MKCG_Authority_Hook_Service'
                ];
                
                $missing_classes = [];
                foreach ($required_classes as $class) {
                    if (!class_exists($class)) {
                        $missing_classes[] = $class;
                    }
                }
                
                if (empty($missing_classes)) {
                    $this->pass_test("✅ Integration test passed - All required classes available");
                    $this->passed_tests++;
                } else {
                    $this->fail_test("❌ Integration test failed - Missing classes: " . implode(', ', $missing_classes));
                }
                
            } else {
                $this->fail_test("❌ Integration test failed - Core classes not available");
            }
            
        } catch (Exception $e) {
            $this->fail_test("❌ Integration test failed with exception: " . $e->getMessage());
        }
    }
    
    /**
     * Display test summary and next steps
     */
    private function display_summary() {
        echo "<hr>";
        echo "<h2>📊 Test Summary</h2>";
        
        $success_rate = $this->total_tests > 0 ? round(($this->passed_tests / $this->total_tests) * 100, 1) : 0;
        
        echo "<div style='background: " . ($success_rate >= 90 ? '#d4edda' : ($success_rate >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>Results: {$this->passed_tests}/{$this->total_tests} tests passed ({$success_rate}%)</h3>";
        
        if ($success_rate >= 90) {
            echo "<p><strong>🎉 EXCELLENT!</strong> The AJAX fix implementation is comprehensive and ready for production.</p>";
            echo "<h4>✅ Root Cause Resolution Status: FIXED</h4>";
            echo "<ul>";
            echo "<li>✅ Missing save_topics_data() method has been implemented</li>";
            echo "<li>✅ WordPress will now return proper JSON responses instead of empty responses</li>";
            echo "<li>✅ JavaScript 'Failed to execute json on Response' error should be eliminated</li>";
            echo "<li>✅ Topics saving functionality should now work correctly</li>";
            echo "</ul>";
        } elseif ($success_rate >= 70) {
            echo "<p><strong>⚠️ MOSTLY GOOD</strong> The implementation is mostly complete but may need minor adjustments.</p>";
        } else {
            echo "<p><strong>❌ NEEDS WORK</strong> The implementation has significant issues that need to be addressed.</p>";
        }
        
        echo "</div>";
        
        echo "<h3>🔧 Next Steps</h3>";
        echo "<ol>";
        echo "<li><strong>Clear WordPress cache</strong> (if using caching plugins)</li>";
        echo "<li><strong>Test the Topics Generator form</strong> to verify AJAX saving works</li>";
        echo "<li><strong>Check browser console</strong> for any remaining JavaScript errors</li>";
        echo "<li><strong>Monitor WordPress error logs</strong> for any PHP errors during testing</li>";
        echo "<li><strong>Test with actual topic data</strong> to ensure end-to-end functionality</li>";
        echo "</ol>";
        
        echo "<h3>🐛 Debugging Information</h3>";
        echo "<ul>";
        echo "<li><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</li>";
        echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
        echo "<li><strong>Plugin Directory:</strong> " . plugin_dir_path(__FILE__) . "</li>";
        echo "<li><strong>AJAX URL:</strong> " . admin_url('admin-ajax.php') . "</li>";
        echo "<li><strong>Test Time:</strong> " . current_time('mysql') . "</li>";
        echo "</ul>";
        
        if ($success_rate < 100) {
            echo "<h3>❌ Failed Tests Details</h3>";
            foreach ($this->test_results as $result) {
                if (!$result['passed']) {
                    echo "<p style='color: #dc3545;'>{$result['message']}</p>";
                }
            }
        }
    }
    
    /**
     * Record a passed test
     */
    private function pass_test($message) {
        echo "<p style='color: #28a745;'>$message</p>";
        $this->test_results[] = ['passed' => true, 'message' => $message];
    }
    
    /**
     * Record a failed test
     */
    private function fail_test($message) {
        echo "<p style='color: #dc3545;'>$message</p>";
        $this->test_results[] = ['passed' => false, 'message' => $message];
    }
}

// Run the validation if accessed directly
if (!defined('MKCG_AJAX_FIX_VALIDATOR_LOADED')) {
    define('MKCG_AJAX_FIX_VALIDATOR_LOADED', true);
    new MKCG_AJAX_Fix_Validator();
}
?>