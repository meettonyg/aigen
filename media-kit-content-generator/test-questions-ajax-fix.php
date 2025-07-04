<?php
/**
 * ROOT-LEVEL QUESTIONS GENERATOR AJAX FIX VALIDATION TEST
 * 
 * This test validates that the Questions Generator save functionality is working
 * by checking all the components involved in the AJAX request flow.
 * 
 * Access via: https://yoursite.com/wp-content/plugins/media-kit-content-generator/test-questions-ajax-fix.php
 */

// Load WordPress
$wp_load_path = '';
$current_dir = dirname(__FILE__);
for ($i = 0; $i < 5; $i++) {
    $potential_path = $current_dir . str_repeat('/..', $i) . '/wp-load.php';
    if (file_exists($potential_path)) {
        $wp_load_path = $potential_path;
        break;
    }
}

if (empty($wp_load_path)) {
    die('ERROR: Could not find wp-load.php. Please check the file path.');
}

require_once($wp_load_path);

// Security check
if (!current_user_can('administrator')) {
    die('ERROR: Administrator access required to run this test.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Questions Generator AJAX Fix Validation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; line-height: 1.6; }
        .test-container { max-width: 1000px; margin: 0 auto; }
        .test-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .test-section { background: white; border: 1px solid #e1e5e9; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-result { padding: 15px; border-radius: 6px; margin: 10px 0; font-weight: 500; }
        .test-pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .test-fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .test-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .ajax-test-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .test-button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px; }
        .test-button:hover { background: #005a87; }
        .ajax-result { margin-top: 15px; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
        .step-indicator { display: inline-block; width: 25px; height: 25px; background: #007cba; color: white; border-radius: 50%; text-align: center; line-height: 25px; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🔧 Questions Generator AJAX Fix Validation</h1>
            <p>Comprehensive test to validate that the Questions Generator save functionality is working correctly after root-level fixes.</p>
            <p><strong>Test Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <?php
        // Test Results Array
        $test_results = [];
        $critical_issues = [];

        // STEP 1: Check Plugin Loading
        echo '<div class="test-section">';
        echo '<h3><span class="step-indicator">1</span>Plugin Loading & Class Availability</h3>';
        
        $plugin_class_exists = class_exists('Media_Kit_Content_Generator');
        $plugin_instance = null;
        
        if ($plugin_class_exists) {
            try {
                $plugin_instance = Media_Kit_Content_Generator::get_instance();
                echo '<div class="test-result test-pass">✅ Media_Kit_Content_Generator class loaded successfully</div>';
                $test_results['plugin_class'] = true;
            } catch (Exception $e) {
                echo '<div class="test-result test-fail">❌ Failed to get plugin instance: ' . $e->getMessage() . '</div>';
                $test_results['plugin_class'] = false;
                $critical_issues[] = 'Plugin instance creation failed';
            }
        } else {
            echo '<div class="test-result test-fail">❌ Media_Kit_Content_Generator class not found</div>';
            $test_results['plugin_class'] = false;
            $critical_issues[] = 'Main plugin class not loaded';
        }

        // Check Questions Generator
        $questions_generator_exists = class_exists('Enhanced_Questions_Generator');
        if ($questions_generator_exists) {
            echo '<div class="test-result test-pass">✅ Enhanced_Questions_Generator class loaded</div>';
            $test_results['questions_generator_class'] = true;
        } else {
            echo '<div class="test-result test-fail">❌ Enhanced_Questions_Generator class not found</div>';
            $test_results['questions_generator_class'] = false;
            $critical_issues[] = 'Questions Generator class not loaded';
        }

        echo '</div>';

        // STEP 2: Check AJAX Handler Registration
        echo '<div class="test-section">';
        echo '<h3><span class="step-indicator">2</span>AJAX Handler Registration</h3>';
        
        global $wp_filter;
        $ajax_actions_to_check = [
            'wp_ajax_mkcg_save_questions',
            'wp_ajax_mkcg_generate_questions', 
            'wp_ajax_mkcg_save_single_question',
            'wp_ajax_mkcg_get_questions_data'
        ];
        
        $registered_actions = [];
        foreach ($ajax_actions_to_check as $action) {
            if (isset($wp_filter[$action]) && !empty($wp_filter[$action])) {
                $registered_actions[] = $action;
                echo '<div class="test-result test-pass">✅ ' . $action . ' is registered</div>';
            } else {
                echo '<div class="test-result test-fail">❌ ' . $action . ' is NOT registered</div>';
                $critical_issues[] = $action . ' missing';
            }
        }
        
        $test_results['ajax_registrations'] = count($registered_actions) === count($ajax_actions_to_check);
        echo '</div>';

        // STEP 3: Test Post ID Detection
        echo '<div class="test-section">';
        echo '<h3><span class="step-indicator">3</span>Test Post ID & Data Flow</h3>';
        
        // Create a test post for validation
        $test_post_id = wp_insert_post([
            'post_type' => 'guests',
            'post_title' => 'Test Guest for AJAX Validation',
            'post_status' => 'publish',
            'meta_input' => [
                'topic_1' => 'Test Topic 1',
                'topic_2' => 'Test Topic 2'
            ]
        ]);
        
        if ($test_post_id && !is_wp_error($test_post_id)) {
            echo '<div class="test-result test-pass">✅ Test guest post created (ID: ' . $test_post_id . ')</div>';
            
            // Test Questions Generator data loading
            if ($plugin_instance && $questions_generator_exists) {
                $questions_generator = $plugin_instance->get_generator('questions');
                if ($questions_generator) {
                    try {
                        $template_data = $questions_generator->get_template_data($test_post_id);
                        if (is_array($template_data) && isset($template_data['post_id'])) {
                            echo '<div class="test-result test-pass">✅ Template data loading works (Post ID: ' . $template_data['post_id'] . ')</div>';
                            echo '<div class="test-result test-info">📊 Template data structure: ' . implode(', ', array_keys($template_data)) . '</div>';
                        } else {
                            echo '<div class="test-result test-fail">❌ Template data structure invalid</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="test-result test-fail">❌ Template data loading failed: ' . $e->getMessage() . '</div>';
                    }
                }
            }
        } else {
            echo '<div class="test-result test-warning">⚠️ Could not create test post</div>';
        }
        
        echo '</div>';

        // STEP 4: Summary
        echo '<div class="test-section">';
        echo '<h3><span class="step-indicator">4</span>Test Summary</h3>';
        
        $total_tests = count($test_results);
        $passed_tests = count(array_filter($test_results));
        $success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100) : 0;
        
        if ($success_rate >= 90) {
            echo '<div class="test-result test-pass">🎉 <strong>EXCELLENT:</strong> ' . $success_rate . '% tests passed (' . $passed_tests . '/' . $total_tests . ')</div>';
            echo '<div class="test-result test-info">✅ Questions Generator AJAX fix appears to be working correctly!</div>';
        } elseif ($success_rate >= 70) {
            echo '<div class="test-result test-warning">⚠️ <strong>PARTIAL:</strong> ' . $success_rate . '% tests passed (' . $passed_tests . '/' . $total_tests . ')</div>';
            echo '<div class="test-result test-warning">Some issues detected but core functionality may work.</div>';
        } else {
            echo '<div class="test-result test-fail">❌ <strong>CRITICAL ISSUES:</strong> Only ' . $success_rate . '% tests passed (' . $passed_tests . '/' . $total_tests . ')</div>';
            echo '<div class="test-result test-fail">Major problems detected - Questions Generator likely won\'t work.</div>';
        }
        
        if (!empty($critical_issues)) {
            echo '<div class="test-result test-fail"><strong>Critical Issues Found:</strong><br>' . implode('<br>', $critical_issues) . '</div>';
        }
        
        echo '</div>';

        // Clean up test post
        if ($test_post_id && !is_wp_error($test_post_id)) {
            wp_delete_post($test_post_id, true);
        }
        ?>

        <div class="test-section">
            <h3><span class="step-indicator">5</span>Manual AJAX Test</h3>
            <p>Use this form to manually test the AJAX save functionality:</p>
            
            <div class="ajax-test-form">
                <p><strong>Instructions:</strong></p>
                <ol>
                    <li>Enter a valid guests post ID (or use the form on a Questions Generator page)</li>
                    <li>Click "Test Save Questions" to test the AJAX endpoint</li>
                    <li>Check the result below</li>
                </ol>
                
                <input type="number" id="test-post-id" placeholder="Enter post ID" style="padding: 8px; margin: 5px;" />
                <button class="test-button" onclick="testAjaxSave()">Test Save Questions</button>
                <button class="test-button" onclick="testAjaxGenerate()">Test Generate Questions</button>
                
                <div id="ajax-result" class="ajax-result" style="display: none;"></div>
            </div>
        </div>

        <div class="test-section">
            <h3>Next Steps</h3>
            <p>After running this test:</p>
            <ol>
                <li>If all tests pass, the Questions Generator should work correctly</li>
                <li>If issues are found, check the WordPress error logs</li>
                <li>Test the actual Questions Generator form on a page with <code>?post_id=XXXXX</code></li>
                <li>Monitor browser console for JavaScript errors</li>
            </ol>
        </div>
    </div>

    <script>
        function testAjaxSave() {
            const postId = document.getElementById('test-post-id').value;
            const resultDiv = document.getElementById('ajax-result');
            
            if (!postId) {
                alert('Please enter a post ID');
                return;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'ajax-result test-info';
            resultDiv.textContent = 'Testing AJAX save...';
            
            // Prepare test data
            const formData = new FormData();
            formData.append('action', 'mkcg_save_questions');
            formData.append('post_id', postId);
            formData.append('nonce', '<?php echo wp_create_nonce('mkcg_nonce'); ?>');
            formData.append('questions', JSON.stringify({
                1: { 1: 'Test question 1', 2: 'Test question 2' },
                2: { 1: 'Test question 3', 2: 'Test question 4' }
            }));
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.className = 'ajax-result ' + (data.success ? 'test-pass' : 'test-fail');
                resultDiv.textContent = 'RESULT: ' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultDiv.className = 'ajax-result test-fail';
                resultDiv.textContent = 'ERROR: ' + error.message;
            });
        }
        
        function testAjaxGenerate() {
            const resultDiv = document.getElementById('ajax-result');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'ajax-result test-info';
            resultDiv.textContent = 'Testing AJAX generate...';
            
            const formData = new FormData();
            formData.append('action', 'mkcg_generate_questions');
            formData.append('topic', 'Test Topic for Questions');
            formData.append('nonce', '<?php echo wp_create_nonce('mkcg_nonce'); ?>');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.className = 'ajax-result ' + (data.success ? 'test-pass' : 'test-fail');
                resultDiv.textContent = 'RESULT: ' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultDiv.className = 'ajax-result test-fail';
                resultDiv.textContent = 'ERROR: ' + error.message;
            });
        }
    </script>
</body>
</html>
