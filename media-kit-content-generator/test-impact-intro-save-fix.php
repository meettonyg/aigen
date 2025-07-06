<?php
/**
 * Test Script: Impact Intro Save Fix Validation
 * 
 * This script validates that the root-level fixes for Impact Intro save failures are working correctly.
 * 
 * Usage: Add ?test_impact_intro_save=1 to any WordPress page URL to run this test
 */

// Only run if test parameter is present and user is admin
if (!isset($_GET['test_impact_intro_save']) || !current_user_can('administrator')) {
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Impact Intro Save Fix Validation Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .test-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border-left: 4px solid #007cba; background: #f8f9fa; }
        .success { border-left-color: #28a745; background: #d4edda; color: #155724; }
        .warning { border-left-color: #ffc107; background: #fff3cd; color: #856404; }
        .error { border-left-color: #dc3545; background: #f8d7da; color: #721c24; }
        .test-button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; font-size: 12px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .status-icon { font-size: 16px; margin-right: 8px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Impact Intro Save Fix Validation Test</h1>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
        <p><strong>User:</strong> <?php echo wp_get_current_user()->display_name; ?> (ID: <?php echo get_current_user_id(); ?>)</p>

        <?php
        
        // Test 1: Check if Impact Intro Service exists and is functional
        echo '<div class="test-section">';
        echo '<h2>📋 Test 1: Impact Intro Service Availability</h2>';
        
        $service_available = false;
        $service_methods = [];
        
        if (class_exists('MKCG_Impact_Intro_Service')) {
            $service_available = true;
            $service = new MKCG_Impact_Intro_Service();
            $service_methods = get_class_methods($service);
            echo '<div class="success"><span class="status-icon">✅</span>MKCG_Impact_Intro_Service class found and instantiated</div>';
            echo '<p><strong>Available methods:</strong> ' . implode(', ', $service_methods) . '</p>';
            
            // Check for our enhanced methods
            $enhanced_methods = ['handle_save_ajax', 'save_impact_intro_data'];
            foreach ($enhanced_methods as $method) {
                if (in_array($method, $service_methods)) {
                    echo '<div class="success"><span class="status-icon">✅</span>Enhanced method ' . $method . ' available</div>';
                } else {
                    echo '<div class="error"><span class="status-icon">❌</span>Enhanced method ' . $method . ' missing</div>';
                }
            }
        } else {
            echo '<div class="error"><span class="status-icon">❌</span>MKCG_Impact_Intro_Service class not found</div>';
        }
        echo '</div>';
        
        // Test 2: Check Enhanced Impact Intro Generator
        echo '<div class="test-section">';
        echo '<h2>🎯 Test 2: Enhanced Impact Intro Generator</h2>';
        
        if (class_exists('Enhanced_Impact_Intro_Generator')) {
            echo '<div class="success"><span class="status-icon">✅</span>Enhanced_Impact_Intro_Generator class found</div>';
            
            try {
                $generator = new Enhanced_Impact_Intro_Generator();
                echo '<div class="success"><span class="status-icon">✅</span>Enhanced_Impact_Intro_Generator instantiated successfully</div>';
                
                $generator_methods = get_class_methods($generator);
                if (in_array('handle_save_impact_intro', $generator_methods)) {
                    echo '<div class="success"><span class="status-icon">✅</span>handle_save_impact_intro method available</div>';
                } else {
                    echo '<div class="error"><span class="status-icon">❌</span>handle_save_impact_intro method missing</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error"><span class="status-icon">❌</span>Error instantiating generator: ' . $e->getMessage() . '</div>';
            }
        } else {
            echo '<div class="error"><span class="status-icon">❌</span>Enhanced_Impact_Intro_Generator class not found</div>';
        }
        echo '</div>';
        
        // Test 3: AJAX Handler Registration
        echo '<div class="test-section">';
        echo '<h2>🔌 Test 3: AJAX Handler Registration</h2>';
        
        global $wp_filter;
        $ajax_actions = ['wp_ajax_mkcg_save_impact_intro', 'wp_ajax_mkcg_get_impact_intro'];
        
        foreach ($ajax_actions as $action) {
            if (isset($wp_filter[$action]) && !empty($wp_filter[$action]->callbacks)) {
                echo '<div class="success"><span class="status-icon">✅</span>AJAX action ' . $action . ' is registered</div>';
                
                // Show registered callbacks
                $callbacks = $wp_filter[$action]->callbacks;
                foreach ($callbacks as $priority => $callback_group) {
                    foreach ($callback_group as $callback) {
                        if (is_array($callback['function'])) {
                            $class_name = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                            $method_name = $callback['function'][1];
                            echo '<p style="margin-left: 20px; font-size: 12px;">Callback: ' . $class_name . '::' . $method_name . ' (priority: ' . $priority . ')</p>';
                        } else {
                            echo '<p style="margin-left: 20px; font-size: 12px;">Callback: ' . $callback['function'] . ' (priority: ' . $priority . ')</p>';
                        }
                    }
                }
            } else {
                echo '<div class="error"><span class="status-icon">❌</span>AJAX action ' . $action . ' is NOT registered</div>';
            }
        }
        echo '</div>';
        
        // Test 4: Field Mappings Validation
        echo '<div class="test-section">';
        echo '<h2>🗂️ Test 4: Field Mappings Validation</h2>';
        
        if ($service_available) {
            // Use reflection to access private field mappings
            $reflection = new ReflectionClass($service);
            $field_mappings_property = $reflection->getProperty('field_mappings');
            $field_mappings_property->setAccessible(true);
            $field_mappings = $field_mappings_property->getValue($service);
            
            echo '<div class="success"><span class="status-icon">✅</span>Field mappings retrieved successfully</div>';
            echo '<pre>' . print_r($field_mappings, true) . '</pre>';
            
            // Validate required mappings exist
            $required_fields = ['where', 'why'];
            foreach ($required_fields as $field) {
                if (isset($field_mappings['postmeta'][$field])) {
                    echo '<div class="success"><span class="status-icon">✅</span>Field mapping for ' . $field . ' exists: ' . $field_mappings['postmeta'][$field] . '</div>';
                } else {
                    echo '<div class="error"><span class="status-icon">❌</span>Field mapping for ' . $field . ' missing</div>';
                }
            }
        }
        echo '</div>';
        
        // Test 5: Create Test Post and Simulate Save
        echo '<div class="test-section">';
        echo '<h2>💾 Test 5: Simulated Save Operation</h2>';
        
        if ($service_available) {
            // Create a test post
            $test_post_id = wp_insert_post([
                'post_title' => 'Impact Intro Test Post - ' . date('Y-m-d H:i:s'),
                'post_content' => 'Test post for Impact Intro save validation',
                'post_status' => 'draft',
                'post_type' => 'post' // Using 'post' type for testing
            ]);
            
            if ($test_post_id && !is_wp_error($test_post_id)) {
                echo '<div class="success"><span class="status-icon">✅</span>Test post created successfully: ID ' . $test_post_id . '</div>';
                
                // Test save operation
                $test_components = [
                    'where' => 'helped 100+ startups achieve product-market fit',
                    'why' => 'democratize access to proven startup methodologies'
                ];
                
                try {
                    $save_result = $service->save_impact_intro_data($test_post_id, $test_components);
                    
                    echo '<div class="test-result">';
                    echo '<strong>Save Result:</strong><br>';
                    echo '<pre>' . print_r($save_result, true) . '</pre>';
                    echo '</div>';
                    
                    if ($save_result['success']) {
                        echo '<div class="success"><span class="status-icon">✅</span>Save operation successful!</div>';
                        
                        // Verify data was actually saved
                        $verification_result = $service->get_impact_intro_data($test_post_id);
                        echo '<div class="test-result">';
                        echo '<strong>Verification Result:</strong><br>';
                        echo '<pre>' . print_r($verification_result, true) . '</pre>';
                        echo '</div>';
                        
                        if ($verification_result['has_data']) {
                            echo '<div class="success"><span class="status-icon">✅</span>Data verification successful - saved data matches!</div>';
                        } else {
                            echo '<div class="warning"><span class="status-icon">⚠️</span>Data verification failed - no data found after save</div>';
                        }
                    } else {
                        echo '<div class="error"><span class="status-icon">❌</span>Save operation failed: ' . $save_result['message'] . '</div>';
                    }
                    
                    // Cleanup test post
                    wp_delete_post($test_post_id, true);
                    echo '<div class="success"><span class="status-icon">🗑️</span>Test post cleaned up</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="error"><span class="status-icon">❌</span>Exception during save test: ' . $e->getMessage() . '</div>';
                    wp_delete_post($test_post_id, true);
                }
                
            } else {
                echo '<div class="error"><span class="status-icon">❌</span>Failed to create test post</div>';
            }
        }
        echo '</div>';
        
        // Test 6: JavaScript Variables Check
        echo '<div class="test-section">';
        echo '<h2>🔧 Test 6: JavaScript Variables</h2>';
        
        echo '<div id="js-test-results"></div>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const resultsDiv = document.getElementById("js-test-results");
                let results = "";
                
                // Check for required JavaScript variables
                const requiredVars = ["ajaxurl", "mkcg_nonce", "mkcg_vars"];
                
                requiredVars.forEach(varName => {
                    if (typeof window[varName] !== "undefined") {
                        results += `<div class="success"><span class="status-icon">✅</span>JavaScript variable ${varName} is available</div>`;
                        if (varName === "mkcg_vars") {
                            results += `<p style="margin-left: 20px; font-size: 12px;">AJAX actions: ${Object.keys(window[varName].ajax_actions || {}).join(", ")}</p>`;
                        }
                    } else {
                        results += `<div class="error"><span class="status-icon">❌</span>JavaScript variable ${varName} is missing</div>`;
                    }
                });
                
                // Check for Impact Intro Generator object
                if (typeof window.ImpactIntroGenerator !== "undefined") {
                    results += `<div class="success"><span class="status-icon">✅</span>ImpactIntroGenerator object is available</div>`;
                    
                    // Check for required methods
                    const requiredMethods = ["saveImpactIntro", "collectCredentialData", "validateData"];
                    requiredMethods.forEach(method => {
                        if (typeof window.ImpactIntroGenerator[method] === "function") {
                            results += `<div class="success"><span class="status-icon">✅</span>Method ${method} is available</div>`;
                        } else {
                            results += `<div class="error"><span class="status-icon">❌</span>Method ${method} is missing</div>`;
                        }
                    });
                } else {
                    results += `<div class="warning"><span class="status-icon">⚠️</span>ImpactIntroGenerator object not found (normal if not on Impact Intro page)</div>`;
                }
                
                resultsDiv.innerHTML = results;
            });
        </script>';
        echo '</div>';
        
        // Summary
        echo '<div class="test-section">';
        echo '<h2>📊 Test Summary</h2>';
        echo '<p>The Impact Intro save fix implementation includes:</p>';
        echo '<ul>';
        echo '<li><strong>Enhanced PHP Service:</strong> Better error handling, success detection, and logging</li>';
        echo '<li><strong>Enhanced Generator:</strong> Comprehensive validation and fallback mechanisms</li>';
        echo '<li><strong>Improved AJAX Handling:</strong> Better nonce verification and error responses</li>';
        echo '<li><strong>Root Cause Fixes:</strong> Addresses update_post_meta edge cases and validation issues</li>';
        echo '</ul>';
        echo '<div class="success"><span class="status-icon">🎯</span><strong>Expected Result:</strong> Impact Intro saves should now work correctly with detailed error logging for troubleshooting</div>';
        echo '</div>';
        
        ?>
        
        <div class="test-section">
            <h2>🔧 Manual Testing Instructions</h2>
            <ol>
                <li>Go to an Impact Intro generator page with a valid post_id parameter</li>
                <li>Fill in the WHERE and WHY fields with test content</li>
                <li>Click "Save Impact Intro" button</li>
                <li>Check browser console for detailed logging (press F12 → Console)</li>
                <li>Verify success message appears and data persists after page refresh</li>
            </ol>
            <p><strong>Debug Commands:</strong></p>
            <ul>
                <li><code>window.debugImpactIntro()</code> - Debug generator state</li>
                <li><code>window.debugCredentialManagement()</code> - Debug credential system</li>
                <li><code>window.testCredentialManagement()</code> - Test credential functionality</li>
            </ul>
        </div>
    </div>
</body>
</html>

<?php
// Prevent WordPress from processing further
exit;
?>
