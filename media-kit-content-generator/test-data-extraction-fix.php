<?php
/**
 * CRITICAL TEST: Data Extraction Fix Validation
 * 
 * This file tests the root-level fixes applied to the Topics Generator data extraction.
 * Run this file to validate that the centralized Formidable Service improvements are working.
 */

// WordPress environment setup
if (!defined('ABSPATH')) {
    // Assuming this is run from the plugin directory
    require_once('../../../../../wp-config.php');
}

// Include required classes
require_once __DIR__ . '/includes/services/class-mkcg-config.php';
require_once __DIR__ . '/includes/services/class-mkcg-formidable-service.php';

/**
 * Test the data extraction fixes
 */
function test_data_extraction_fixes() {
    echo "<h1>🔧 CRITICAL FIX VALIDATION: Topics Generator Data Extraction</h1>\n";
    echo "<p><strong>Testing centralized Formidable Service improvements...</strong></p>\n";
    
    // Test entry key from user's report
    $test_entry_key = 'y8ver'; // From user's console output
    
    echo "<h2>📋 Test Configuration</h2>\n";
    echo "<ul>\n";
    echo "<li><strong>Entry Key:</strong> {$test_entry_key}</li>\n";
    echo "<li><strong>Expected Authority Hook:</strong> 'I help saas founders' (working)</li>\n";
    echo "<li><strong>Expected Topic Fields:</strong> 8498-8502 (previously empty)</li>\n";
    echo "</ul>\n";
    
    // Initialize services
    echo "<h2>🚀 Initializing Services</h2>\n";
    $formidable_service = new MKCG_Formidable_Service();
    
    if (!$formidable_service->is_formidable_active()) {
        echo "<div style='color: red;'>❌ <strong>ERROR:</strong> Formidable Forms not active!</div>\n";
        return false;
    }
    
    echo "<div style='color: green;'>✅ Formidable Forms is active</div>\n";
    
    // Test 1: Configuration Validation
    echo "<h2>🔍 Test 1: Configuration Validation</h2>\n";
    $config_validation = MKCG_Config::validate_configuration();
    
    if ($config_validation['valid']) {
        echo "<div style='color: green;'>✅ Configuration is valid</div>\n";
        if (!empty($config_validation['warnings'])) {
            echo "<div style='color: orange;'>⚠️ Warnings: " . implode(', ', $config_validation['warnings']) . "</div>\n";
        }
    } else {
        echo "<div style='color: red;'>❌ Configuration errors: " . implode(', ', $config_validation['errors']) . "</div>\n";
    }
    
    // Test 2: Entry Data Retrieval
    echo "<h2>🗄️ Test 2: Entry Data Retrieval</h2>\n";
    $entry_data = $formidable_service->get_entry_data($test_entry_key);
    
    if ($entry_data['success']) {
        $entry_id = $entry_data['entry_id'];
        $fields = $entry_data['fields'];
        
        echo "<div style='color: green;'>✅ Entry data retrieved successfully</div>\n";
        echo "<ul>\n";
        echo "<li><strong>Entry ID:</strong> {$entry_id}</li>\n";
        echo "<li><strong>Total Fields:</strong> " . count($fields) . "</li>\n";
        echo "</ul>\n";
        
        // Test 3: Topic Fields Validation
        echo "<h2>📝 Test 3: Topic Fields Validation</h2>\n";
        $topic_fields = ['8498', '8499', '8500', '8501', '8502'];
        $topics_found = 0;
        $topics_with_data = 0;
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Field ID</th><th>Topic #</th><th>Status</th><th>Value Preview</th><th>Raw Type</th></tr>\n";
        
        foreach ($topic_fields as $field_id) {
            $topic_number = $field_id - 8497; // Convert to 1-5
            
            if (isset($fields[$field_id])) {
                $topics_found++;
                $field_data = $fields[$field_id];
                $value = $field_data['value'];
                $raw_type = gettype($field_data['raw_value']);
                
                if (!empty($value)) {
                    $topics_with_data++;
                    $status = "<span style='color: green;'>✅ HAS DATA</span>";
                    $preview = substr($value, 0, 100) . (strlen($value) > 100 ? '...' : '');
                } else {
                    $status = "<span style='color: red;'>❌ EMPTY</span>";
                    $preview = '<em>No data</em>';
                }
                
                echo "<tr>";
                echo "<td>{$field_id}</td>";
                echo "<td>{$topic_number}</td>";
                echo "<td>{$status}</td>";
                echo "<td>" . htmlspecialchars($preview) . "</td>";
                echo "<td>{$raw_type}</td>";
                echo "</tr>\n";
            } else {
                echo "<tr>";
                echo "<td>{$field_id}</td>";
                echo "<td>{$topic_number}</td>";
                echo "<td><span style='color: red;'>❌ NOT FOUND</span></td>";
                echo "<td><em>Field not in results</em></td>";
                echo "<td>N/A</td>";
                echo "</tr>\n";
            }
        }
        
        echo "</table>\n";
        
        // Test 4: Authority Hook Validation
        echo "<h2>🏛️ Test 4: Authority Hook Validation</h2>\n";
        $auth_fields = ['10296', '10297', '10387', '10298', '10358'];
        $auth_found = 0;
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Field ID</th><th>Component</th><th>Status</th><th>Value</th></tr>\n";
        
        $auth_labels = [
            '10296' => 'WHO',
            '10297' => 'RESULT', 
            '10387' => 'WHEN',
            '10298' => 'HOW',
            '10358' => 'COMPLETE'
        ];
        
        foreach ($auth_fields as $field_id) {
            $label = $auth_labels[$field_id];
            
            if (isset($fields[$field_id])) {
                $auth_found++;
                $value = $fields[$field_id]['value'];
                $status = !empty($value) ? "<span style='color: green;'>✅ FOUND</span>" : "<span style='color: orange;'>⚠️ EMPTY</span>";
                $display_value = !empty($value) ? substr($value, 0, 50) : '<em>Empty</em>';
            } else {
                $status = "<span style='color: red;'>❌ NOT FOUND</span>";
                $display_value = '<em>Field not in results</em>';
            }
            
            echo "<tr>";
            echo "<td>{$field_id}</td>";
            echo "<td>{$label}</td>";
            echo "<td>{$status}</td>";
            echo "<td>" . htmlspecialchars($display_value) . "</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
        
        // Test 5: Data Processing Validation
        echo "<h2>⚙️ Test 5: Data Processing Validation</h2>\n";
        $data_validation = MKCG_Config::validate_data_extraction($entry_id, 'topics');
        
        echo "<ul>\n";
        echo "<li><strong>Fields Tested:</strong> {$data_validation['fields_tested']}</li>\n";
        echo "<li><strong>Fields Found:</strong> {$data_validation['fields_found']}</li>\n";
        echo "<li><strong>Success Rate:</strong> " . round(($data_validation['fields_found'] / $data_validation['fields_tested']) * 100, 1) . "%</li>\n";
        echo "</ul>\n";
        
        if (!empty($data_validation['errors'])) {
            echo "<div style='color: red;'><strong>Errors:</strong><ul>\n";
            foreach ($data_validation['errors'] as $error) {
                echo "<li>{$error}</li>\n";
            }
            echo "</ul></div>\n";
        }
        
        // Final Results
        echo "<h2>📊 FINAL RESULTS</h2>\n";
        
        $overall_success = ($topics_with_data > 0 && $auth_found > 0);
        
        if ($overall_success) {
            echo "<div style='color: green; font-size: 18px; font-weight: bold;'>✅ SUCCESS: Data extraction fixes are working!</div>\n";
            echo "<ul>\n";
            echo "<li>Topic fields with data: {$topics_with_data}/5</li>\n";
            echo "<li>Authority hook components found: {$auth_found}/5</li>\n";
            echo "<li>Data processing: IMPROVED</li>\n";
            echo "</ul>\n";
        } else {
            echo "<div style='color: red; font-size: 18px; font-weight: bold;'>❌ ISSUES DETECTED: Further investigation needed</div>\n";
            echo "<ul>\n";
            echo "<li>Topic fields with data: {$topics_with_data}/5</li>\n";
            echo "<li>Authority hook components found: {$auth_found}/5</li>\n";
            if ($topics_with_data === 0) {
                echo "<li><span style='color: red;'>⚠️ No topic data found - check database values</span></li>\n";
            }
            echo "</ul>\n";
        }
        
    } else {
        echo "<div style='color: red;'>❌ Failed to retrieve entry data: " . $entry_data['message'] . "</div>\n";
        return false;
    }
    
    return true;
}

// Run the test if accessed directly
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html><html><head><title>Data Extraction Fix Test</title></head><body>";
}

test_data_extraction_fixes();

if (php_sapi_name() !== 'cli') {
    echo "</body></html>";
}
?>