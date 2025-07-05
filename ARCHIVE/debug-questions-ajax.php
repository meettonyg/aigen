<?php
/**
 * Questions Generator AJAX Debug Test
 * 
 * This test simulates the exact AJAX request being made by the JavaScript
 * to identify where the data extraction is failing.
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
    die('ERROR: Could not find wp-load.php');
}

require_once($wp_load_path);

// Security check
if (!current_user_can('administrator')) {
    die('ERROR: Administrator access required');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Questions Generator AJAX Debug Test</title>
    <style>
        body { font-family: monospace; margin: 40px; }
        .test-section { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        pre { background: white; padding: 15px; border: 1px solid #ddd; overflow-x: auto; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Questions Generator AJAX Debug Test</h1>
    
    <div class="test-section">
        <h3>1. Test Questions Data Extraction</h3>
        <p>This will simulate exactly what the JavaScript sends to PHP:</p>
        
        <?php
        // Simulate the AJAX request data that JavaScript would send
        $test_questions_data = [
            1 => [1 => 'Test question 1-1', 2 => 'Test question 1-2'],
            2 => [1 => 'Test question 2-1', 2 => 'Test question 2-2'],
            3 => [1 => 'Test question 3-1']
        ];
        
        $json_encoded = json_encode($test_questions_data);
        
        echo '<div class="info">';
        echo '<strong>Simulated JavaScript Data:</strong><br>';
        echo '<pre>' . print_r($test_questions_data, true) . '</pre>';
        echo '<strong>JSON Encoded (what gets sent):</strong><br>';
        echo '<pre>' . $json_encoded . '</pre>';
        echo '</div>';
        
        // Now test the PHP extraction
        if (class_exists('Enhanced_Questions_Generator')) {
            // Create a temporary $_POST array to simulate the AJAX request
            $original_post = $_POST;
            $_POST = [
                'action' => 'mkcg_save_questions',
                'post_id' => '32372',
                'questions' => $json_encoded,
                'nonce' => wp_create_nonce('mkcg_nonce')
            ];
            
            echo '<div class="info">';
            echo '<strong>Simulated $_POST data:</strong><br>';
            echo '<pre>' . print_r($_POST, true) . '</pre>';
            echo '</div>';
            
            // Test the extraction method
            try {
                $generator = new Enhanced_Questions_Generator(null);
                
                // Use reflection to access the private method
                $reflection = new ReflectionClass($generator);
                $method = $reflection->getMethod('extract_questions_data');
                $method->setAccessible(true);
                
                $extracted = $method->invoke($generator);
                
                if (!empty($extracted)) {
                    echo '<div class="success">';
                    echo '<strong>✅ SUCCESS: Questions extracted successfully!</strong><br>';
                    echo '<strong>Extracted ' . count($extracted) . ' questions:</strong><br>';
                    echo '<pre>' . print_r($extracted, true) . '</pre>';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>❌ FAILED: No questions extracted</strong><br>';
                    echo 'The extract_questions_data() method returned empty array';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ ERROR: Exception during extraction</strong><br>';
                echo $e->getMessage();
                echo '</div>';
            }
            
            // Restore original $_POST
            $_POST = $original_post;
            
        } else {
            echo '<div class="error">❌ Enhanced_Questions_Generator class not found</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h3>2. Test Manual AJAX Call</h3>
        <p>Click the button below to make a real AJAX call with test data:</p>
        
        <button onclick="testRealAjax()">Test Real AJAX Call</button>
        <div id="ajax-result"></div>
    </div>
    
    <div class="test-section">
        <h3>3. Check WordPress Error Log</h3>
        <p>Check your WordPress error log for detailed debug messages from the PHP extraction method.</p>
        <p>Look for messages starting with "MKCG Questions:" to see the extraction process.</p>
        
        <?php
        $error_log_paths = [
            ABSPATH . 'wp-content/debug.log',
            ABSPATH . 'error_log',
            ini_get('error_log')
        ];
        
        echo '<strong>Possible error log locations:</strong><ul>';
        foreach ($error_log_paths as $path) {
            if ($path && file_exists($path)) {
                echo '<li>✅ ' . $path . ' (exists)</li>';
            } else {
                echo '<li>❌ ' . $path . ' (not found)</li>';
            }
        }
        echo '</ul>';
        ?>
    </div>

    <script>
        function testRealAjax() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.innerHTML = '<p style="background: #fff3cd; padding: 10px;">Testing AJAX call...</p>';
            
            const testData = {
                1: {1: 'Real AJAX test question 1-1', 2: 'Real AJAX test question 1-2'},
                2: {1: 'Real AJAX test question 2-1'}
            };
            
            const formData = new FormData();
            formData.append('action', 'mkcg_save_questions');
            formData.append('post_id', '32372');
            formData.append('questions', JSON.stringify(testData));
            formData.append('nonce', '<?php echo wp_create_nonce('mkcg_nonce'); ?>');
            
            console.log('🧪 Sending test data:', testData);
            console.log('🧪 JSON encoded:', JSON.stringify(testData));
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('🧪 AJAX Response:', data);
                
                if (data.success) {
                    resultDiv.innerHTML = '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px;"><strong>✅ SUCCESS!</strong><br>Response: <pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                } else {
                    resultDiv.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px;"><strong>❌ FAILED!</strong><br>Error: ' + (data.data ? data.data.message || data.data : JSON.stringify(data)) + '<br><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                }
            })
            .catch(error => {
                console.error('🧪 AJAX Error:', error);
                resultDiv.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px;"><strong>❌ AJAX ERROR!</strong><br>' + error.message + '</div>';
            });
        }
    </script>
</body>
</html>
