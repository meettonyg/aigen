<?php
/**
 * TOPICS GENERATOR ROOT FIXES VALIDATION SCRIPT
 * Tests all 4 steps of the root-level fixes implementation
 * 
 * Run this script to validate that 500 errors are resolved
 */

// Prevent direct access - this should be run in WordPress environment
if (!defined('ABSPATH')) {
    die('This script must be run in WordPress environment');
}

class MKCG_Root_Fixes_Validator {
    
    private $test_results = [];
    private $validation_summary = [];
    
    public function __construct() {
        echo '<h2>🔧 Topics Generator Root Fixes Validation</h2>';
        echo '<p>Testing all 4 steps of Gemini\'s recommended fixes...</p>';
    }
    
    /**
     * Run all validation tests
     */
    public function run_all_tests() {
        $this->test_step_1_ajax_handlers();
        $this->test_step_2_service_initialization();
        $this->test_step_3_unified_data_source();
        $this->test_step_4_standardized_communication();
        
        $this->display_summary();
    }
    
    /**
     * STEP 1 TEST: Missing Backend AJAX Handlers
     */
    private function test_step_1_ajax_handlers() {
        echo '<h3>📡 Step 1: Testing AJAX Handlers Registration</h3>';
        
        $required_actions = [
            'mkcg_save_authority_hook',
            'mkcg_save_topics_data', 
            'mkcg_save_topic',
            'mkcg_get_topics_data',
            'mkcg_save_field',
            'mkcg_save_topic_field'
        ];
        
        $registered_count = 0;
        $missing_actions = [];
        
        foreach ($required_actions as $action) {
            $wp_action = 'wp_ajax_' . $action;
            $nopriv_action = 'wp_ajax_nopriv_' . $action;
            
            $has_auth = has_action($wp_action);
            $has_nopriv = has_action($nopriv_action);
            
            if ($has_auth && $has_nopriv) {
                echo "✅ {$action} - Registered for both authenticated and non-authenticated users<br>";
                $registered_count++;
            } else {
                echo "❌ {$action} - Missing registration (auth: " . ($has_auth ? 'yes' : 'no') . ", nopriv: " . ($has_nopriv ? 'yes' : 'no') . ")<br>";
                $missing_actions[] = $action;
            }
        }
        
        $this->test_results['step_1'] = [
            'total_required' => count($required_actions),
            'registered_count' => $registered_count,
            'missing_actions' => $missing_actions,
            'success_rate' => ($registered_count / count($required_actions)) * 100
        ];
        
        echo "<p><strong>Step 1 Result: {$registered_count}/" . count($required_actions) . " AJAX handlers registered</strong></p>";
    }
    
    /**
     * STEP 2 TEST: Service Initialization
     */
    private function test_step_2_service_initialization() {
        echo '<h3>🔧 Step 2: Testing Service Initialization</h3>';
        
        $services_status = [];
        
        // Test if main plugin class exists
        if (class_exists('Media_Kit_Content_Generator')) {
            echo "✅ Main plugin class exists<br>";
            
            $plugin_instance = Media_Kit_Content_Generator::get_instance();
            $topics_generator = $plugin_instance->get_generator('topics');
            
            if ($topics_generator) {
                echo "✅ Topics Generator instance available<br>";
                
                // Test service availability
                $services_to_test = [
                    'formidable_service',
                    'api_service', 
                    'authority_hook_service'
                ];
                
                $available_services = 0;
                foreach ($services_to_test as $service) {
                    $reflection = new ReflectionClass($topics_generator);
                    $property = $reflection->getProperty($service);
                    $property->setAccessible(true);
                    $service_instance = $property->getValue($topics_generator);
                    
                    if ($service_instance && is_object($service_instance)) {
                        echo "✅ {$service} - Initialized<br>";
                        $available_services++;
                        $services_status[$service] = true;
                    } else {
                        echo "❌ {$service} - Not initialized<br>";
                        $services_status[$service] = false;
                    }
                }
                
                // Test Topics Data Service availability
                if (method_exists($topics_generator, 'is_topics_service_available')) {
                    $topics_service_available = $topics_generator->is_topics_service_available();
                    if ($topics_service_available) {
                        echo "✅ Topics Data Service - Available<br>";
                        $available_services++;
                        $services_status['topics_data_service'] = true;
                    } else {
                        echo "⚠️ Topics Data Service - Not available (will use fallbacks)<br>";
                        $services_status['topics_data_service'] = false;
                    }
                }
                
            } else {
                echo "❌ Topics Generator not available<br>";
            }
        } else {
            echo "❌ Main plugin class not found<br>";
        }
        
        $this->test_results['step_2'] = [
            'services_status' => $services_status,
            'critical_services_available' => count(array_filter($services_status))
        ];
        
        echo "<p><strong>Step 2 Result: " . count(array_filter($services_status)) . "/" . count($services_status) . " services initialized</strong></p>";
    }
    
    /**
     * STEP 3 TEST: Unified Data Source
     */
    private function test_step_3_unified_data_source() {
        echo '<h3>🔄 Step 3: Testing Unified Data Source</h3>';
        
        if (!class_exists('Media_Kit_Content_Generator')) {
            echo "❌ Cannot test - plugin not available<br>";
            return;
        }
        
        $plugin_instance = Media_Kit_Content_Generator::get_instance();
        $topics_generator = $plugin_instance->get_generator('topics');
        
        if (!$topics_generator) {
            echo "❌ Cannot test - Topics Generator not available<br>";
            return;
        }
        
        // Test template data loading
        try {
            $template_data = $topics_generator->get_template_data('');
            
            if (is_array($template_data)) {
                echo "✅ Template data method returns array<br>";
                
                $required_keys = ['entry_id', 'entry_key', 'authority_hook_components', 'form_field_values', 'has_entry'];
                $missing_keys = [];
                
                foreach ($required_keys as $key) {
                    if (array_key_exists($key, $template_data)) {
                        echo "✅ Template data contains '{$key}'<br>";
                    } else {
                        echo "❌ Template data missing '{$key}'<br>";
                        $missing_keys[] = $key;
                    }
                }
                
                // Test data source tracking
                if (isset($template_data['data_source'])) {
                    echo "✅ Data source tracking available: " . $template_data['data_source'] . "<br>";
                } else {
                    echo "⚠️ Data source tracking not available<br>";
                }
                
                $this->test_results['step_3'] = [
                    'template_data_available' => true,
                    'required_keys_present' => count($required_keys) - count($missing_keys),
                    'total_required_keys' => count($required_keys),
                    'missing_keys' => $missing_keys,
                    'data_source' => $template_data['data_source'] ?? 'unknown'
                ];
                
            } else {
                echo "❌ Template data method does not return array<br>";
                $this->test_results['step_3'] = ['template_data_available' => false];
            }
            
        } catch (Exception $e) {
            echo "❌ Exception testing template data: " . $e->getMessage() . "<br>";
            $this->test_results['step_3'] = ['exception' => $e->getMessage()];
        }
        
        echo "<p><strong>Step 3 Result: Unified data source " . (isset($this->test_results['step_3']['template_data_available']) && $this->test_results['step_3']['template_data_available'] ? 'working' : 'failed') . "</strong></p>";
    }
    
    /**
     * STEP 4 TEST: Standardized Communication
     */
    private function test_step_4_standardized_communication() {
        echo '<h3>📡 Step 4: Testing Standardized Communication</h3>';
        
        if (!class_exists('Media_Kit_Content_Generator')) {
            echo "❌ Cannot test - plugin not available<br>";
            return;
        }
        
        $plugin_instance = Media_Kit_Content_Generator::get_instance();
        $topics_generator = $plugin_instance->get_generator('topics');
        
        if (!$topics_generator) {
            echo "❌ Cannot test - Topics Generator not available<br>";
            return;
        }
        
        // Test if enhanced methods exist
        $reflection = new ReflectionClass($topics_generator);
        
        $required_methods = [
            'validate_and_extract_request_data',
            'resolve_post_id_from_entry',
            'standardized_authority_hook_save'
        ];
        
        $methods_available = 0;
        $missing_methods = [];
        
        foreach ($required_methods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "✅ Method '{$method}' exists<br>";
                $methods_available++;
            } else {
                echo "❌ Method '{$method}' missing<br>";
                $missing_methods[] = $method;
            }
        }
        
        // Test AJAX handler enhancements
        if ($reflection->hasMethod('handle_save_authority_hook_ajax')) {
            echo "✅ Enhanced AJAX handler available<br>";
            
            // Get method source to check for Step 4 improvements
            $method = $reflection->getMethod('handle_save_authority_hook_ajax');
            $filename = $method->getFileName();
            $start_line = $method->getStartLine();
            $end_line = $method->getEndLine();
            
            if ($filename && $start_line && $end_line) {
                $source = file($filename);
                $method_source = implode('', array_slice($source, $start_line - 1, $end_line - $start_line + 1));
                
                if (strpos($method_source, 'STEP 4') !== false) {
                    echo "✅ AJAX handler contains Step 4 enhancements<br>";
                } else {
                    echo "⚠️ AJAX handler may not have Step 4 enhancements<br>";
                }
            }
        } else {
            echo "❌ Enhanced AJAX handler missing<br>";
        }
        
        $this->test_results['step_4'] = [
            'required_methods' => count($required_methods),
            'methods_available' => $methods_available,
            'missing_methods' => $missing_methods,
            'enhancement_rate' => ($methods_available / count($required_methods)) * 100
        ];
        
        echo "<p><strong>Step 4 Result: {$methods_available}/" . count($required_methods) . " standardization methods available</strong></p>";
    }
    
    /**
     * Display comprehensive test summary
     */
    private function display_summary() {
        echo '<h3>📊 COMPREHENSIVE TEST SUMMARY</h3>';
        
        $overall_score = 0;
        $total_tests = 0;
        
        // Step 1 Score
        if (isset($this->test_results['step_1'])) {
            $step1_score = $this->test_results['step_1']['success_rate'];
            echo "<p><strong>Step 1 (AJAX Handlers):</strong> {$step1_score}% - " . $this->get_status_text($step1_score) . "</p>";
            $overall_score += $step1_score;
            $total_tests++;
        }
        
        // Step 2 Score
        if (isset($this->test_results['step_2'])) {
            $available = $this->test_results['step_2']['critical_services_available'];
            $total = count($this->test_results['step_2']['services_status']);
            $step2_score = ($available / $total) * 100;
            echo "<p><strong>Step 2 (Service Initialization):</strong> {$step2_score}% - " . $this->get_status_text($step2_score) . "</p>";
            $overall_score += $step2_score;
            $total_tests++;
        }
        
        // Step 3 Score
        if (isset($this->test_results['step_3']) && isset($this->test_results['step_3']['required_keys_present'])) {
            $present = $this->test_results['step_3']['required_keys_present'];
            $total = $this->test_results['step_3']['total_required_keys'];
            $step3_score = ($present / $total) * 100;
            echo "<p><strong>Step 3 (Unified Data Source):</strong> {$step3_score}% - " . $this->get_status_text($step3_score) . "</p>";
            $overall_score += $step3_score;
            $total_tests++;
        }
        
        // Step 4 Score
        if (isset($this->test_results['step_4'])) {
            $step4_score = $this->test_results['step_4']['enhancement_rate'];
            echo "<p><strong>Step 4 (Standardized Communication):</strong> {$step4_score}% - " . $this->get_status_text($step4_score) . "</p>";
            $overall_score += $step4_score;
            $total_tests++;
        }
        
        // Overall Score
        $final_score = $total_tests > 0 ? $overall_score / $total_tests : 0;
        $status_emoji = $final_score >= 90 ? '🎉' : ($final_score >= 70 ? '✅' : ($final_score >= 50 ? '⚠️' : '❌'));
        
        echo "<div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;'>";
        echo "<h4>{$status_emoji} OVERALL IMPLEMENTATION STATUS: " . round($final_score, 1) . "%</h4>";
        
        if ($final_score >= 90) {
            echo "<p style='color: green;'><strong>✅ EXCELLENT:</strong> All root fixes implemented successfully. 500 errors should be resolved.</p>";
        } elseif ($final_score >= 70) {
            echo "<p style='color: orange;'><strong>⚠️ GOOD:</strong> Most fixes implemented. Minor issues may remain.</p>";
        } elseif ($final_score >= 50) {
            echo "<p style='color: orange;'><strong>⚠️ PARTIAL:</strong> Some fixes implemented. Further work needed.</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ NEEDS WORK:</strong> Significant issues remain. Review implementation.</p>";
        }
        
        echo "</div>";
        
        // Recommendations
        echo "<h4>🔧 RECOMMENDATIONS:</h4>";
        if ($final_score < 100) {
            echo "<ul>";
            
            if (isset($this->test_results['step_1']) && $this->test_results['step_1']['success_rate'] < 100) {
                echo "<li>Complete AJAX handler registration for missing actions</li>";
            }
            
            if (isset($this->test_results['step_2']) && count(array_filter($this->test_results['step_2']['services_status'])) < count($this->test_results['step_2']['services_status'])) {
                echo "<li>Fix service initialization issues</li>";
            }
            
            if (isset($this->test_results['step_3']) && isset($this->test_results['step_3']['missing_keys']) && !empty($this->test_results['step_3']['missing_keys'])) {
                echo "<li>Complete unified data source implementation</li>";
            }
            
            if (isset($this->test_results['step_4']) && $this->test_results['step_4']['enhancement_rate'] < 100) {
                echo "<li>Implement remaining standardization methods</li>";
            }
            
            echo "</ul>";
        } else {
            echo "<p style='color: green;'>🎉 All fixes successfully implemented! You can now test the Topics Generator functionality.</p>";
        }
        
        // Testing Instructions
        echo "<h4>🧪 NEXT STEPS - FUNCTIONAL TESTING:</h4>";
        echo "<ol>";
        echo "<li>Clear browser cache and reload the Topics Generator page</li>";
        echo "<li>Open browser developer tools and check the Console tab</li>";
        echo "<li>Try saving Authority Hook components - should see success messages</li>";
        echo "<li>Try editing and saving individual topics - should work without 500 errors</li>";
        echo "<li>Check that AJAX requests return properly formatted JSON responses</li>";
        echo "</ol>";
    }
    
    /**
     * Get status text based on score
     */
    private function get_status_text($score) {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Fair';
        return 'Needs Work';
    }
}

// Run the validation if this script is loaded
if (class_exists('MKCG_Root_Fixes_Validator')) {
    $validator = new MKCG_Root_Fixes_Validator();
    $validator->run_all_tests();
}
?>

<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; }
h2 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
h3 { color: #135e96; margin-top: 30px; }
h4 { color: #1d2327; }
code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
</style>
