<?php
/**
 * COMPREHENSIVE SERIALIZATION FIX TEST
 * Tests the complete fix for Formidable Forms serialization bug in field 10296
 */

echo "<h1>🧪 COMPREHENSIVE SERIALIZATION FIX TEST</h1>\n";
echo "<h2>Testing Root-Level Fix for Formidable Forms Serialization Bug</h2>\n";

// Include WordPress if available
if (file_exists('../../../../wp-config.php')) {
    require_once '../../../../wp-config.php';
    echo "<p><strong>✅ WordPress loaded</strong> - Testing with live environment</p>\n";
} else {
    echo "<p><strong>⚠️ WordPress not loaded</strong> - Testing with mock functions</p>\n";
    
    // Mock WordPress functions for standalone testing
    function error_log($message) {
        echo "<div style='font-size: 11px; color: #666; margin: 2px 0;'>LOG: " . htmlspecialchars($message) . "</div>\n";
    }
}

// Test data scenarios
$test_scenarios = [
    'field_10296_original' => [
        'name' => 'Field 10296 Original Bug',
        'data' => 'a:1:{i:0;s:22:"Authors launching a book";}',
        'expected' => 'Authors launching a book',
        'description' => 'The exact malformed serialized data from field 10296'
    ],
    'correct_serialization' => [
        'name' => 'Correct Serialization',
        'data' => 'a:1:{i:0;s:24:"Authors launching a book";}',
        'expected' => 'Authors launching a book',
        'description' => 'How the data should have been serialized'
    ],
    'different_length_bug' => [
        'name' => 'Different Length Bug',
        'data' => 'a:1:{i:0;s:15:"Very long string that was truncated";}',
        'expected' => 'Very long string that was truncated',
        'description' => 'Another length mismatch scenario'
    ],
    'completely_malformed' => [
        'name' => 'Completely Malformed',
        'data' => 'a:1:{i:0;s:99:"Short string";}',
        'expected' => 'Short string', 
        'description' => 'Severely malformed serialization'
    ],
    'plain_text' => [
        'name' => 'Plain Text (No Serialization)',
        'data' => 'Authors launching a book',
        'expected' => 'Authors launching a book',
        'description' => 'Normal text data without serialization'
    ],
    'empty_data' => [
        'name' => 'Empty Data',
        'data' => '',
        'expected' => '',
        'description' => 'Empty field handling'
    ]
];

// Mock the enhanced processing system
class MockFormidableService {
    
    public function process_field_value_enhanced($raw_value, $field_id = null) {
        // Return empty string for null, false, or empty values
        if ($raw_value === null || $raw_value === false || $raw_value === '') {
            if ($field_id) {
                error_log("MKCG Enhanced Processing: Field {$field_id} - NULL/FALSE/EMPTY value, returning empty");
            }
            return '';
        }
        
        // Debug logging for field processing
        if ($field_id) {
            error_log("MKCG Enhanced Processing: Field {$field_id} - Raw type: " . gettype($raw_value) . ", Length: " . (is_string($raw_value) ? strlen($raw_value) : 'N/A') . ", First 100 chars: " . substr(print_r($raw_value, true), 0, 100));
        }
        
        if (is_string($raw_value)) {
            $trimmed = trim($raw_value);
            
            // Check if the string is serialized (CRITICAL FIX for field 10296)
            if ($this->is_serialized($trimmed)) {
                if ($field_id) {
                    error_log("MKCG Enhanced Processing: Field {$field_id} - Detected serialized data");
                }
                
                $unserialized = @unserialize($trimmed);
                
                if ($field_id) {
                    error_log("MKCG Enhanced Processing: Field {$field_id} - Unserialize result type: " . gettype($unserialized) . ", Value: " . print_r($unserialized, true));
                }
                
                // CRITICAL FIX: If standard unserialization failed, activate repair system
                if ($unserialized === false) {
                    if ($field_id) {
                        error_log("MKCG CRITICAL FIX: Field {$field_id} - Formidable serialization BUG detected, activating repair system");
                    }
                    
                    $repaired_unserialized = $this->repair_and_unserialize_malformed_data($trimmed, $field_id);
                    
                    if ($repaired_unserialized !== false) {
                        $unserialized = $repaired_unserialized;
                        if ($field_id) {
                            error_log("MKCG CRITICAL FIX: Field {$field_id} - Repair SUCCESSFUL! Data recovered from Formidable bug");
                        }
                    } else {
                        if ($field_id) {
                            error_log("MKCG CRITICAL FIX: Field {$field_id} - Repair failed, extracting value manually");
                        }
                        
                        // Emergency extraction: try to get the string content manually
                        $manual_extract = $this->emergency_string_extraction($trimmed, $field_id);
                        if (!empty($manual_extract)) {
                            if ($field_id) {
                                error_log("MKCG CRITICAL FIX: Field {$field_id} - Emergency extraction successful: '{$manual_extract}'");
                            }
                            return $manual_extract;
                        }
                        
                        return $trimmed; // Final fallback to original string
                    }
                }
                
                // If unserializing results in an array, extract the first non-empty value
                if (is_array($unserialized)) {
                    if ($field_id) {
                        error_log("MKCG Enhanced Processing: Field {$field_id} - Processing array with " . count($unserialized) . " elements");
                    }
                    
                    foreach ($unserialized as $key => $value) {
                        if ($field_id) {
                            error_log("MKCG Enhanced Processing: Field {$field_id} - Array element {$key}: '" . print_r($value, true) . "' (type: " . gettype($value) . ")");
                        }
                        
                        if (!empty(trim((string)$value))) {
                            $result = trim((string)$value);
                            if ($field_id) {
                                error_log("MKCG Enhanced Processing: Field {$field_id} - Extracted array value: '{$result}'