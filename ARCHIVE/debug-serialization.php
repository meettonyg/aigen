<?php
/**
 * Debug Serialization Issue - Investigate the unserialization failure
 */

echo "<h1>🔍 Serialization Debug Analysis</h1>\n";

$test_data = 'a:1:{i:0;s:22:"Authors launching a book";}';

echo "<h2>Step-by-Step Serialization Analysis</h2>\n";

echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>\n";
echo "<h3>1. Raw Data Analysis</h3>\n";
echo "<strong>Input:</strong> <code>" . htmlspecialchars($test_data) . "</code><br>\n";
echo "<strong>Length:</strong> " . strlen($test_data) . "<br>\n";
echo "<strong>Type:</strong> " . gettype($test_data) . "<br>\n";
echo "</div>\n";

echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>\n";
echo "<h3>2. Serialization Detection</h3>\n";

// Test different serialization detection methods
$wp_detection = function_exists('is_serialized') ? is_serialized($test_data) : 'N/A';
echo "<strong>WordPress is_serialized():</strong> " . ($wp_detection === 'N/A' ? 'Not available' : ($wp_detection ? 'TRUE' : 'FALSE')) . "<br>\n";

// Manual detection
$manual_detection = (strlen($test_data) >= 4 && $test_data[1] === ':' && substr($test_data, -1) === '}');
echo "<strong>Manual detection:</strong> " . ($manual_detection ? 'TRUE' : 'FALSE') . "<br>\n";

// Enhanced detection (our method)
function enhanced_is_serialized($data) {
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $lastc = substr($data, -1);
    if (';' !== $lastc && '}' !== $lastc) {
        return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's':
            if ('"' !== substr($data, -2, 1)) {
                return false;
            }
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            return (bool) preg_match("/^{$token}:[0-9.E-]+;\$/", $data);
    }
    return false;
}

$enhanced_detection = enhanced_is_serialized($test_data);
echo "<strong>Enhanced detection:</strong> " . ($enhanced_detection ? 'TRUE' : 'FALSE') . "<br>\n";
echo "</div>\n";

echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>\n";
echo "<h3>3. Unserialization Test</h3>\n";

$unserialized = @unserialize($test_data);
echo "<strong>Unserialize result type:</strong> " . gettype($unserialized) . "<br>\n";
echo "<strong>Unserialize result:</strong> <pre>" . print_r($unserialized, true) . "</pre>\n";

if ($unserialized === false) {
    echo "<strong style='color: red;'>❌ Unserialization FAILED</strong><br>\n";
    echo "<strong>Error details:</strong> unserialize() returned FALSE<br>\n";
} else {
    echo "<strong style='color: green;'>✅ Unserialization SUCCESS</strong><br>\n";
    
    if (is_array($unserialized)) {
        echo "<strong>Array contents:</strong><br>\n";
        foreach ($unserialized as $key => $value) {
            echo "Key: <code>$key</code>, Value: <code>" . htmlspecialchars($value) . "</code>, Type: " . gettype($value) . "<br>\n";
        }
        
        // Test extraction logic
        echo "<br><strong>Extraction test:</strong><br>\n";
        foreach ($unserialized as $value) {
            if (!empty(trim($value))) {
                echo "Found non-empty value: '<strong>" . htmlspecialchars(trim($value)) . "</strong>'<br>\n";
                break;
            }
        }
    }
}
echo "</div>\n";

echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>\n";
echo "<h3>4. PHP Error Check</h3>\n";

// Test with error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<strong>Testing unserialization with error reporting:</strong><br>\n";
$test_unserialize = unserialize($test_data);
echo "<strong>Result:</strong> " . print_r($test_unserialize, true) . "<br>\n";

// Test the exact serialized format
echo "<br><strong>Testing manual recreation:</strong><br>\n";
$test_array = array(0 => "Authors launching a book");
$manual_serialize = serialize($test_array);
echo "<strong>Manual serialized:</strong> <code>" . htmlspecialchars($manual_serialize) . "</code><br>\n";
echo "<strong>Matches original:</strong> " . ($manual_serialize === $test_data ? 'YES' : 'NO') . "<br>\n";

if ($manual_serialize !== $test_data) {
    echo "<strong>Difference found:</strong><br>\n";
    echo "Original: <code>" . htmlspecialchars($test_data) . "</code><br>\n";
    echo "Manual:   <code>" . htmlspecialchars($manual_serialize) . "</code><br>\n";
}

echo "</div>\n";

echo "<h2>🎯 Diagnosis</h2>\n";
if ($unserialized !== false && is_array($unserialized)) {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb;'>\n";
    echo "<strong>✅ The serialized data is VALID and unserializes correctly!</strong><br>\n";
    echo "The issue must be in our processing logic, not the unserialization itself.<br>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb;'>\n";
    echo "<strong>❌ The unserialization is failing!</strong><br>\n";
    echo "We need to investigate why the unserialize() function is not working.<br>\n";
    echo "</div>\n";
}
?>
