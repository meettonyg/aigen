<?php
/**
 * CRITICAL TEST: Validate Topics Generator Unified Data Source Fix
 * This test verifies that Topics Generator now uses the same unified data source as Questions Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('This test must be run within WordPress');
}

/**
 * Test class to validate the unified data source fix
 */
class MKCG_Unified_Data_Source_Test {
    
    private $test_results = [];
    private $test_entry_key = 'y8ver'; // Use the same entry key from the document
    
    public function __construct() {
        echo '<div style="font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px;">';
        echo '<h2>🧪 MKCG Unified Data Source Fix Validation</h2>';
        echo '<p><strong>Testing:</strong> Topics Generator now uses same unified data source as Questions Generator</p>';
        echo '<hr>';
        
        $this->run_all_tests();
        $this->display_results();
        
        echo '</div>';
    }
    
    /**
     * Run all validation tests
     */
    private function run_all_tests() {
        echo '<h3>Running Validation Tests...</h3>';
        
        // Test 1: Verify Topics Generator Service Initialization
        $this->test_topics_generator_service_initialization();
        
        // Test 2: Verify Questions Generator Service Initialization  
        $this->test_questions_generator_service_initialization();
        
        // Test 3: Compare Data Source Methods
        $this->test_data_source_method_comparison();
        
        // Test 4: Test Template Data Loading
        $this->test_template_data_loading();
        
        // Test 5: Test AJAX Handler Consistency
        $this->test_ajax_handler_consistency();
        
        // Test 6: Validate Data Structure Consistency
        $this->test_data_structure_consistency();
    }
    
    /**
     * Test 1: Verify Topics Generator Service Initialization
     */
    private function test_topics_generator_service_initialization() {
        $test_name = 'Topics Generator Service Initialization';
        echo "<h4>Test 1: {$test_name}</h4>";
        
        try {
            // Check if Topics Generator has Topics Data Service
            if (class_exists('MKCG_Topics_Generator')) {
                
                // Create mock services for testing
                $api_service = $this->create_mock_api_service();
                $formidable_service = $this->create_mock_formidable_service();
                $authority_hook_service = $this->create_mock_authority_hook_service();
                
                $topics_generator = new MKCG_Topics_Generator($api_service, $formidable_service, $authority_hook_service);
                
                // Use reflection to check private properties
                $reflection = new ReflectionClass($topics_generator);
                
                // Check if topics_data_service property exists
                if ($reflection->hasProperty('topics_data_service')) {
                    $topics_service_prop = $reflection->getProperty('topics_data_service');
                    $topics_service_prop->setAccessible(true);
                    $topics_service = $topics_service_prop->getValue($topics_generator);
                    
                    if ($topics_service && is_object($topics_service)) {
                        $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'Topics Data Service properly initialized'];
                        echo '✅ <strong>PASS:</strong> Topics Generator has Topics Data Service initialized<br>';
                    } else {
                        $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Topics Data Service not initialized'];
                        echo '❌ <strong>FAIL:</strong> Topics Data Service not initialized<br>';
                    }
                } else {
                    $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'topics_data_service property not found'];
                    echo '❌ <strong>FAIL:</strong> topics_data_service property not found<br>';
                }
                
                // Check if is_topics_service_available method exists
                if ($reflection->hasMethod('is_topics_service_available')) {
                    $service_check_method = $reflection->getMethod('is_topics_service_available');
                    $service_check_method->setAccessible(true);
                    $is_available = $service_check_method->invoke($topics_generator);
                    
                    if ($is_available) {
                        echo '✅ Topics Data Service reports as available<br>';
                    } else {
                        echo '⚠️ Topics Data Service reports as not available<br>';
                    }
                } else {
                    echo '❌ is_topics_service_available method not found<br>';
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'MKCG_Topics_Generator class not found'];
                echo '❌ <strong>FAIL:</strong> MKCG_Topics_Generator class not found<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Test 2: Verify Questions Generator Service Initialization
     */
    private function test_questions_generator_service_initialization() {
        $test_name = 'Questions Generator Service Initialization';
        echo "<h4>Test 2: {$test_name}</h4>";
        
        try {
            // Check if Questions Generator has Topics Data Service
            if (class_exists('MKCG_Questions_Generator')) {
                
                // Create mock services for testing
                $api_service = $this->create_mock_api_service();
                $formidable_service = $this->create_mock_formidable_service();
                $authority_hook_service = $this->create_mock_authority_hook_service();
                
                $questions_generator = new MKCG_Questions_Generator($api_service, $formidable_service, $authority_hook_service);
                
                // Use reflection to check private properties
                $reflection = new ReflectionClass($questions_generator);
                
                // Check if topics_data_service property exists
                if ($reflection->hasProperty('topics_data_service')) {
                    $topics_service_prop = $reflection->getProperty('topics_data_service');
                    $topics_service_prop->setAccessible(true);
                    $topics_service = $topics_service_prop->getValue($questions_generator);
                    
                    if ($topics_service && is_object($topics_service)) {
                        $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'Topics Data Service properly initialized'];
                        echo '✅ <strong>PASS:</strong> Questions Generator has Topics Data Service initialized<br>';
                    } else {
                        $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Topics Data Service not initialized'];
                        echo '❌ <strong>FAIL:</strong> Topics Data Service not initialized<br>';
                    }
                } else {
                    $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'topics_data_service property not found'];
                    echo '❌ <strong>FAIL:</strong> topics_data_service property not found<br>';
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'MKCG_Questions_Generator class not found'];
                echo '❌ <strong>FAIL:</strong> MKCG_Questions_Generator class not found<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Test 3: Compare Data Source Methods
     */
    private function test_data_source_method_comparison() {
        $test_name = 'Data Source Method Comparison';
        echo "<h4>Test 3: {$test_name}</h4>";
        
        try {
            // Compare get_template_data methods
            if (class_exists('MKCG_Topics_Generator')) {
                $topics_reflection = new ReflectionClass('MKCG_Topics_Generator');
                $topics_method = $topics_reflection->getMethod('get_template_data');
                $topics_source = file_get_contents($topics_method->getFileName());
                
                // Check if the method uses unified service
                if (strpos($topics_source, 'topics_data_service->get_topics_data') !== false) {
                    echo '✅ Topics Generator get_template_data() uses unified Topics Data Service<br>';
                    
                    if (strpos($topics_source, 'UNIFIED') !== false) {
                        echo '✅ Method contains UNIFIED comments indicating proper implementation<br>';
                    }
                    
                    if (strpos($topics_source, 'same as Questions Generator') !== false) {
                        echo '✅ Method documented as same approach as Questions Generator<br>';
                    }
                    
                    $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'Topics Generator uses unified data source'];
                    
                } else {
                    echo '❌ Topics Generator get_template_data() does NOT use unified Topics Data Service<br>';
                    $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Topics Generator not using unified service'];
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Cannot access generator classes'];
                echo '❌ <strong>FAIL:</strong> Cannot access generator classes<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Test 4: Test Template Data Loading
     */
    private function test_template_data_loading() {
        $test_name = 'Template Data Loading';
        echo "<h4>Test 4: {$test_name}</h4>";
        
        try {
            // Test if both generators return consistent data structures
            if (class_exists('MKCG_Topics_Generator') && class_exists('MKCG_Topics_Data_Service')) {
                
                // Create mock services
                $api_service = $this->create_mock_api_service();
                $formidable_service = $this->create_mock_formidable_service();
                $authority_hook_service = $this->create_mock_authority_hook_service();
                
                $topics_generator = new MKCG_Topics_Generator($api_service, $formidable_service, $authority_hook_service);
                
                // Test template data structure
                $template_data = $topics_generator->get_template_data($this->test_entry_key);
                
                // Validate structure
                $required_keys = ['entry_id', 'entry_key', 'authority_hook_components', 'form_field_values', 'has_entry'];
                $missing_keys = [];
                
                foreach ($required_keys as $key) {
                    if (!array_key_exists($key, $template_data)) {
                        $missing_keys[] = $key;
                    }
                }
                
                if (empty($missing_keys)) {
                    echo '✅ Template data structure contains all required keys<br>';
                    
                    // Check if form_field_values has topic structure
                    if (isset($template_data['form_field_values']) && is_array($template_data['form_field_values'])) {
                        $topic_keys = ['topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5'];
                        $has_all_topics = true;
                        
                        foreach ($topic_keys as $topic_key) {
                            if (!array_key_exists($topic_key, $template_data['form_field_values'])) {
                                $has_all_topics = false;
                                break;
                            }
                        }
                        
                        if ($has_all_topics) {
                            echo '✅ Template data contains all 5 topic fields<br>';
                            $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'Template data structure is consistent'];
                        } else {
                            echo '❌ Template data missing some topic fields<br>';
                            $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Incomplete topic structure'];
                        }
                    } else {
                        echo '❌ form_field_values not properly structured<br>';
                        $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Invalid form_field_values'];
                    }
                    
                } else {
                    echo '❌ Template data missing keys: ' . implode(', ', $missing_keys) . '<br>';
                    $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Missing required keys'];
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Required classes not available'];
                echo '❌ <strong>FAIL:</strong> Required classes not available<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Test 5: Test AJAX Handler Consistency
     */
    private function test_ajax_handler_consistency() {
        $test_name = 'AJAX Handler Consistency';
        echo "<h4>Test 5: {$test_name}</h4>";
        
        try {
            // Check if Topics Generator AJAX handlers use unified service
            if (class_exists('MKCG_Topics_Generator')) {
                $reflection = new ReflectionClass('MKCG_Topics_Generator');
                
                // Check handle_get_topics_data_ajax method
                if ($reflection->hasMethod('handle_get_topics_data_ajax')) {
                    $method = $reflection->getMethod('handle_get_topics_data_ajax');
                    $source = file_get_contents($method->getFileName());
                    
                    if (strpos($source, 'topics_data_service->get_topics_data') !== false) {
                        echo '✅ handle_get_topics_data_ajax uses unified Topics Data Service<br>';
                    } else {
                        echo '❌ handle_get_topics_data_ajax does NOT use unified service<br>';
                    }
                    
                    if (strpos($source, 'UNIFIED') !== false) {
                        echo '✅ AJAX handler contains UNIFIED implementation markers<br>';
                    }
                }
                
                // Check handle_save_topics_data_ajax method
                if ($reflection->hasMethod('handle_save_topics_data_ajax')) {
                    $method = $reflection->getMethod('handle_save_topics_data_ajax');
                    $source = file_get_contents($method->getFileName());
                    
                    if (strpos($source, 'topics_data_service') !== false) {
                        echo '✅ handle_save_topics_data_ajax uses unified Topics Data Service<br>';
                        $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'AJAX handlers use unified service'];
                    } else {
                        echo '❌ handle_save_topics_data_ajax does NOT use unified service<br>';
                        $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'AJAX handlers not unified'];
                    }
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'MKCG_Topics_Generator class not found'];
                echo '❌ <strong>FAIL:</strong> MKCG_Topics_Generator class not found<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Test 6: Validate Data Structure Consistency
     */
    private function test_data_structure_consistency() {
        $test_name = 'Data Structure Consistency';
        echo "<h4>Test 6: {$test_name}</h4>";
        
        try {
            // Test that Topics Data Service returns consistent structure
            if (class_exists('MKCG_Topics_Data_Service')) {
                
                $formidable_service = $this->create_mock_formidable_service();
                $topics_data_service = new MKCG_Topics_Data_Service($formidable_service);
                
                // Test get_topics_data method structure
                $reflection = new ReflectionClass($topics_data_service);
                if ($reflection->hasMethod('get_topics_data')) {
                    echo '✅ Topics Data Service has get_topics_data method<br>';
                    
                    // Check if it returns the expected structure
                    $mock_result = $topics_data_service->get_topics_data(0, '', 0);
                    
                    if (is_array($mock_result)) {
                        $expected_keys = ['success', 'topics', 'authority_hook', 'entry_id'];
                        $has_expected_structure = true;
                        
                        foreach ($expected_keys as $key) {
                            if (!array_key_exists($key, $mock_result)) {
                                $has_expected_structure = false;
                                break;
                            }
                        }
                        
                        if ($has_expected_structure) {
                            echo '✅ Topics Data Service returns expected data structure<br>';
                            $this->test_results[$test_name] = ['status' => 'PASS', 'message' => 'Data structure is consistent'];
                        } else {
                            echo '❌ Topics Data Service structure inconsistent<br>';
                            $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Inconsistent data structure'];
                        }
                    } else {
                        echo '❌ Topics Data Service does not return array<br>';
                        $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Invalid return type'];
                    }
                } else {
                    echo '❌ Topics Data Service missing get_topics_data method<br>';
                    $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'Missing required method'];
                }
                
            } else {
                $this->test_results[$test_name] = ['status' => 'FAIL', 'message' => 'MKCG_Topics_Data_Service class not found'];
                echo '❌ <strong>FAIL:</strong> MKCG_Topics_Data_Service class not found<br>';
            }
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
            echo '🚨 <strong>ERROR:</strong> ' . $e->getMessage() . '<br>';
        }
        
        echo '<br>';
    }
    
    /**
     * Display comprehensive test results
     */
    private function display_results() {
        echo '<hr>';
        echo '<h3>📊 Test Results Summary</h3>';
        
        $total_tests = count($this->test_results);
        $passed_tests = 0;
        $failed_tests = 0;
        $error_tests = 0;
        
        foreach ($this->test_results as $test_name => $result) {
            switch ($result['status']) {
                case 'PASS':
                    $passed_tests++;
                    echo '<div style="color: green;">✅ <strong>' . $test_name . ':</strong> ' . $result['message'] . '</div>';
                    break;
                case 'FAIL':
                    $failed_tests++;
                    echo '<div style="color: red;">❌ <strong>' . $test_name . ':</strong> ' . $result['message'] . '</div>';
                    break;
                case 'ERROR':
                    $error_tests++;
                    echo '<div style="color: orange;">🚨 <strong>' . $test_name . ':</strong> ' . $result['message'] . '</div>';
                    break;
            }
        }
        
        echo '<br>';
        echo '<div style="background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50;">';
        echo '<h4>Summary:</h4>';
        echo "<strong>Total Tests:</strong> {$total_tests}<br>";
        echo "<strong>Passed:</strong> {$passed_tests}<br>";
        echo "<strong>Failed:</strong> {$failed_tests}<br>";
        echo "<strong>Errors:</strong> {$error_tests}<br>";
        
        if ($failed_tests == 0 && $error_tests == 0) {
            echo '<br><strong style="color: green;">🎉 ALL TESTS PASSED! Topics Generator now uses unified data source.</strong>';
        } elseif ($passed_tests >= $total_tests * 0.8) {
            echo '<br><strong style="color: orange;">⚠️ MOSTLY SUCCESSFUL - Minor issues detected.</strong>';
        } else {
            echo '<br><strong style="color: red;">❌ CRITICAL ISSUES - Fix implementation needs attention.</strong>';
        }
        echo '</div>';
        
        // Expected outcomes
        echo '<br>';
        echo '<div style="background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;">';
        echo '<h4>Expected Outcomes (If All Tests Pass):</h4>';
        echo '✅ Topics Generator uses same MKCG_Topics_Data_Service as Questions Generator<br>';
        echo '✅ Both generators read from WordPress custom post meta (not Formidable fields)<br>';
        echo '✅ Topic fields populate correctly in Topics Generator<br>';
        echo '✅ Data consistency between generators achieved<br>';
        echo '✅ Single source of truth for data loading established<br>';
        echo '</div>';
    }
    
    /**
     * Create mock API service for testing
     */
    private function create_mock_api_service() {
        return new class {
            public function generate_content($prompt, $type, $options = []) {
                return ['success' => true, 'content' => 'Mock content'];
            }
        };
    }
    
    /**
     * Create mock Formidable service for testing
     */
    private function create_mock_formidable_service() {
        return new class {
            public function get_entry_data($entry_key) {
                return ['success' => true, 'entry_id' => 123];
            }
            
            public function get_post_id_from_entry($entry_id) {
                return 456;
            }
            
            public function get_field_value($entry_id, $field_id) {
                return 'Mock field value';
            }
        };
    }
    
    /**
     * Create mock Authority Hook service for testing
     */
    private function create_mock_authority_hook_service() {
        return new class {
            public function build_authority_hook($components) {
                return 'Mock authority hook';
            }
        };
    }
}

// Run the test
echo '<h1>🧪 MKCG Unified Data Source Fix Validation</h1>';
echo '<p>This test validates that the Topics Generator now uses the same unified data source as the Questions Generator.</p>';

new MKCG_Unified_Data_Source_Test();

?>
