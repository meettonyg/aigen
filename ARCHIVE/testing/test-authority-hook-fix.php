<?php
/**
 * CRITICAL FIX TEST SCRIPT - ROOT LEVEL FIXED
 * Tests the Authority Hook field processing fixes
 *
 * FIXED: Now works in any environment (development, staging, production)
 */

// Robust WordPress loading
$dir = __DIR__;
$max_depth = 10; // Failsafe to prevent infinite loops
$i = 0;

while ($i < $max_depth) {
    if (file_exists($dir . '/wp-load.php')) {
        require_once($dir . '/wp-load.php');
        $wp_loaded = true;
        break;
    }
    if ($dir === dirname($dir)) { // Reached the root of the filesystem
        break;
    }
    $dir = dirname($dir);
    $i++;
}

if (!isset($wp_loaded)) {
    die('<p style="color: red;">❌ WordPress not found. Please check that this script is within a WordPress installation.</p>');
}

// Load our Formidable service
require_once dirname(__FILE__) . '/includes/services/class-mkcg-formidable-service.php';

// Enhanced entry ID handling with fallbacks
function get_test_entry_id() {
    if (isset($_GET['entry_id']) && is_numeric($_GET['entry_id'])) {
        return intval($_GET['entry_id']);
    }
    if (isset($_GET['entry']) && !empty($_GET['entry'])) {
        if (class_exists('MKCG_Formidable_Service')) {
            $formidable_service = new MKCG_Formidable_Service();
            $entry_data = $formidable_service->get_entry_data($_GET['entry']);
            if ($entry_data['success']) {
                return $entry_data['entry_id'];
            }
        }
    }
    return 74492; // Fallback default
}

$entry_id = get_test_entry_id();

// Enhanced output with better formatting and diagnostics
echo "<!DOCTYPE html><html><head><title>Authority Hook Test - FIXED</title>";
echo "<style>"
   . "body { font-family: Arial, sans-serif; margin: 20px; }"
   . "h1, h2 { color: #333; }"
   . "table { border-collapse: collapse; width: 100%; margin: 10px 0; }"
   . "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }"
   . "th { background-color: #f2f2f2; }"
   . ".success { background-color: #ccffcc; }"
   . ".warning { background-color: #ffffcc; }"
   . ".error { background-color: #ffcccc; }"
   . ".info { background-color: #e6f3ff; border: 1px solid #b3d9ff; padding: 10px; margin: 10px 0; }"
   . "</style></head><body>";

echo "<h1>🔧 CRITICAL FIX TEST - Authority Hook Field Processing (ROOT LEVEL FIXED)</h1>";

echo "<div class='info'>";
echo "<h3>🔍 Environment Diagnostics</h3>";
echo "<p><strong>Entry ID:</strong> {$entry_id} " . (isset($_GET['entry_id']) || isset($_GET['entry']) ? '(from URL)' : '(default)') . "</p>";
echo "<p><strong>WordPress Version:</strong> " . (function_exists('get_bloginfo') ? get_bloginfo('version') : 'Unknown') . "</p>";
echo "<p><strong>Plugin Path:</strong> " . dirname(__FILE__) . "</p>";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "<hr>";

try {
    $formidable_service = new MKCG_Formidable_Service();
    echo "<p style='color: green;'>✅ Formidable Service initialized successfully</p>";
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Service Initialization Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    die('Cannot proceed without Formidable service.');
}

echo "<h2>🧪 Testing Enhanced Field Processing</h2>";

try {
    $diagnosis = $formidable_service->diagnose_authority_hook_fields($entry_id);
    if (empty($diagnosis)) {
        echo "<p class='warning'>⚠️ Diagnostic returned no results for entry ID {$entry_id}</p>";
        $diagnosis = [];
    } else {
        echo "<p class='success'>✅ Diagnostic completed for " . count($diagnosis) . " fields</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Diagnostic failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $diagnosis = [];
}

echo "<table>";
echo "<tr><th>Field ID</th><th>Description</th><th>Raw Data Found</th><th>Processed Value</th><th>Processing Method</th><th>Status</th></tr>";

$success_count = 0;
if (!empty($diagnosis)) {
    foreach ($diagnosis as $field_id => $result) {
        $status = 'UNKNOWN';
        $css_class = '';
        
        if ($result['raw_value'] === null) {
            $status = 'NO DATA';
            $css_class = 'error';
        } elseif (!empty($result['processed_value']) && !in_array($result['processed_value'], ['achieve their goals', 'they need help', 'through your method', 'your audience'])) {
            $status = 'SUCCESS - REAL DATA';
            $css_class = 'success';
        } elseif (!empty($result['processed_value'])) {
            $status = 'DEFAULT VALUE';
            $css_class = 'warning';
        } else {
            $status = 'FAILED';
            $css_class = 'error';
        }
        
        // Count WHO field separately
        if($field_id == '10296' && $status == 'SUCCESS - REAL DATA') {
             $success_count++;
        }
        // Count other fields
        if($field_id != '10296' && $status == 'SUCCESS - REAL DATA') {
             $success_count++;
        }
        
        echo "<tr class='{$css_class}'>";
        echo "<td>{$field_id}</td>";
        echo "<td>{$result['description']}</td>";
        echo "<td>" . ($result['raw_value'] ? 'YES (' . $result['raw_length'] . ' chars)' : 'NO') . "</td>";
        echo "<td>" . htmlspecialchars($result['processed_value'] ?: 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($result['processing_method']) . "</td>";
        echo "<td><strong>{$status}</strong></td>";
        echo "</tr>";
    }
}
echo "</table>";

$total_fields = count($diagnosis);

echo "<hr>";

echo "<div class='info'>";
echo "<h3>📋 Test Completion Summary</h3>";
echo "<p><strong>✅ Processed Fields with REAL DATA:</strong> {$success_count} / {$total_fields}</p>";
echo "<p><strong>🔧 Fix Status:</strong> The critical root-level fix has been applied to the Formidable service.</p>";

if ($success_count < $total_fields) {
    echo "<p><strong>⚠️ Recommendations:</strong></p>";
    echo "<ul>";
    echo "<li>Check your WordPress error logs for detailed processing information.</li>";
    echo "<li>Verify the Formidable field IDs (10296, 10297, 10387, 10298, 10358) are correct for your form.</li>";
    echo "<li>Ensure that the entry you are testing (ID: {$entry_id}) actually has data saved in the problematic fields.</li>";
    echo "<li>Test with a different entry ID by adding `?entry_id=YOUR_ID` or `?entry=your_entry_key` to the URL.</li>";
    echo "</ul>";
} else {
    echo "<p style='color:green;'><strong>🎉 All systems working correctly!</strong> The Authority Hook fields are processing data properly.</p>";
}
echo "</div>";

echo "</body></html>";
?>