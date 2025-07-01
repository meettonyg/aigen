<?php
/**
 * Test script to validate Topics Generator data fix
 * Run this to verify that the data extraction fix is working properly
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // For testing outside WordPress, load WordPress
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
}

echo "<h1>Topics Generator Data Fix Validation Test</h1>";

// Test entry key from the console logs
$test_entry_key = 'y8ver';

echo "<h2>Testing Entry Key: {$test_entry_key}</h2>";

// Initialize the Topics Generator
try {
    // Check if our classes are available
    if (!class_exists('MKCG_Topics_Generator')) {
        echo "<p style='color: red;'>❌ MKCG_Topics_Generator class not found. Make sure the plugin is loaded.</p>";
        return;
    }
    
    if (!class_exists('MKCG_Formidable_Service')) {
        echo "<p style='color: red;'>❌ MKCG_Formidable_Service class not found. Make sure the plugin is loaded.</p>";
        return;
    }
    
    // Initialize services
    $formidable_service = new MKCG_Formidable_Service();
    $api_service = new MKCG_API_Service(); // Assuming this exists
    $authority_hook_service = new MKCG_Authority_Hook_Service($formidable_service);
    
    // Initialize Topics Generator
    $topics_generator = new MKCG_Topics_Generator($api_service, $formidable_service, $authority_hook_service);
    
    echo "<p style='color: green;'>✅ Topics Generator initialized successfully</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Failed to initialize Topics Generator: " . $e->getMessage() . "</p>";
    return;
}

echo "<h3>Test 1: Raw Formidable Data Retrieval</h3>";

try {
    // Test raw entry data retrieval
    $entry_data = $formidable_service->get_entry_data($test_entry_key);
    
    if ($entry_data['success']) {
        echo "<p style='color: green;'>✅ Entry data retrieved successfully</p>";
        echo "<p><strong>Entry ID:</strong> " . $entry_data['entry_id'] . "</p>";
        echo "<p><strong>Total Fields:</strong> " . count($entry_data['fields']) . "</p>";
        
        // Show authority hook component fields specifically
        $auth_field_ids = ['10296', '10297', '10387', '10298', '10358']; // who, result, when, how, complete
        echo "<h4>Authority Hook Component Fields (Raw Data):</h4>";
        
        foreach ($auth_field_ids as $field_id) {
            if (isset($entry_data['fields'][$field_id])) {
                $raw_value = $entry_data['fields'][$field_id]['value'];
                echo "<p><strong>Field {$field_id}:</strong> ";
                echo "Type: " . gettype($raw_value) . " | ";
                echo "Value: " . htmlspecialchars(print_r($raw_value, true)) . "</p>";
            } else {
                echo "<p><strong>Field {$field_id}:</strong> <span style='color: orange;'>Not found</span></p>";
            }
        }
        
        // Show topic fields
        $topic_field_ids = ['8498', '8499', '8500', '8501', '8502']; // topics 1-5
        echo "<h4>Topic Fields (Raw Data):</h4>";
        
        foreach ($topic_field_ids as $field_id) {
            if (isset($entry_data['fields'][$field_id])) {
                $raw_value = $entry_data['fields'][$field_id]['value'];
                echo "<p><strong>Field {$field_id}:</strong> ";
                echo "Type: " . gettype($raw_value) . " | ";
                echo "Value: " . htmlspecialchars(substr(print_r($raw_value, true), 0, 100)) . "...</p>";
            } else {
                echo "<p><strong>Field {$field_id}:</strong> <span style='color: orange;'>Not found</span></p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to retrieve entry data: " . $entry_data['message'] . "</p>";
        return;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception during raw data retrieval: " . $e->getMessage() . "</p>";
    return;
}

echo "<h3>Test 2: Processed Template Data</h3>";

try {
    // Test the processed template data (our fix)
    $template_data = $topics_generator->get_template_data($test_entry_key);
    
    echo "<p><strong>Has Entry:</strong> " . ($template_data['has_entry'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Entry ID:</strong> " . $template_data['entry_id'] . "</p>";
    
    echo "<h4>Authority Hook Components (Processed):</h4>";
    foreach ($template_data['authority_hook_components'] as $component => $value) {
        $status = !empty($value) ? '✅' : '❌';
        echo "<p>{$status} <strong>{$component}:</strong> '{$value}'</p>";
    }
    
    echo "<h4>Topic Form Values (Processed):</h4>";
    foreach ($template_data['form_field_values'] as $topic => $value) {
        $status = !empty($value) ? '✅' : '❌';
        $display_value = !empty($value) ? substr($value, 0, 80) . '...' : '[Empty]';
        echo "<p>{$status} <strong>{$topic}:</strong> {$display_value}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception during template data processing: " . $e->getMessage() . "</p>";
    return;
}

echo "<h3>Test 3: Data Processing Method Test</h3>";

// Test the data processing method directly with sample serialized data
$test_data_samples = [
    'Plain string' => 'test value',
    'Serialized string' => 's:10:"test value";',
    'Serialized array' => 'a:1:{i:0;s:10:"test value";}',
    'JSON array' => '["test value"]',
    'Empty string' => '',
    'Null value' => null,
    'Boolean false' => false,
    'Direct array' => ['test value'],
];

echo "<h4>Processing Method Test Results:</h4>";

// Use reflection to access the private method for testing
$reflection = new ReflectionClass($topics_generator);
$process_method = $reflection->getMethod('process_formidable_field_value');
$process_method->setAccessible(true);

foreach ($test_data_samples as $label => $sample_data) {
    try {
        $processed = $process_method->invoke($topics_generator, $sample_data);
        $input_type = gettype($sample_data);
        $input_display = is_string($sample_data) ? "'{$sample_data}'" : print_r($sample_data, true);
        
        echo "<p><strong>{$label}:</strong><br>";
        echo "&nbsp;&nbsp;Input ({$input_type}): " . htmlspecialchars($input_display) . "<br>";
        echo "&nbsp;&nbsp;Output: '{$processed}'</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>{$label}:</strong> Error - " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Test 4: JavaScript Data Output Simulation</h3>";

// Simulate what would be output to JavaScript
$auth_components = $template_data['authority_hook_components'];
$topics = $template_data['form_field_values'];

echo "<h4>JavaScript Data Structure (as would be output to browser):</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo "window.MKCG_Topics_Data = {\n";
echo "    entryId: " . $template_data['entry_id'] . ",\n";
echo "    entryKey: '" . esc_js($template_data['entry_key']) . "',\n";
echo "    hasEntry: " . ($template_data['has_entry'] ? 'true' : 'false') . ",\n";
echo "    authorityHook: {\n";
echo "        who: '" . esc_js($auth_components['who']) . "',\n";
echo "        result: '" . esc_js($auth_components['result']) . "',\n";
echo "        when: '" . esc_js($auth_components['when']) . "',\n";
echo "        how: '" . esc_js($auth_components['how']) . "',\n";
echo "        complete: '" . esc_js($auth_components['complete']) . "'\n";
echo "    },\n";
echo "    topics: {\n";
foreach ($topics as $key => $value) {
    echo "        {$key}: '" . esc_js($value) . "',\n";
}
echo "    }\n";
echo "};";
echo "</pre>";

echo "<h3>Fix Validation Summary</h3>";

// Validation summary
$auth_populated = count(array_filter($auth_components)) > 0;
$topics_populated = count(array_filter($topics)) > 0;

echo "<p><strong>Authority Hook Components:</strong> " . ($auth_populated ? '✅ Data found' : '❌ No data') . "</p>";
echo "<p><strong>Topics:</strong> " . ($topics_populated ? '✅ Data found' : '❌ No data') . "</p>";

if ($auth_populated && $topics_populated) {
    echo "<h2 style='color: green;'>🎉 SUCCESS: The data extraction fix is working!</h2>";
    echo "<p>The Topics Generator should now properly populate authority hook components and topics.</p>";
} elseif ($auth_populated) {
    echo "<h2 style='color: orange;'>⚠️ PARTIAL: Authority hook works, but topics need attention</h2>";
} elseif ($topics_populated) {
    echo "<h2 style='color: orange;'>⚠️ PARTIAL: Topics work, but authority hook needs attention</h2>";
} else {
    echo "<h2 style='color: red;'>❌ FAILED: Data extraction needs more work</h2>";
    echo "<p>Check the raw data above to understand the data format being used.</p>";
}

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>If SUCCESS: Test in the actual Topics Generator page with entry parameter ?entry={$test_entry_key}</li>";
echo "<li>If PARTIAL/FAILED: Review the raw data format above and adjust the processing method accordingly</li>";
echo "<li>Check the error logs for detailed processing information</li>";
echo "</ol>";

echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>
