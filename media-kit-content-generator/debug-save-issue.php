<?php
/**
 * Debug script for Topics Generator save issue
 * Run this to see what data is being received by PHP
 */

// Include WordPress
require_once('../../../../wp-load.php');

// Check if user is logged in
if (!is_user_logged_in()) {
    die('Error: You must be logged in to run this test.');
}

// Check user capabilities
if (!current_user_can('edit_posts')) {
    die('Error: You need edit_posts capability.');
}

echo "<h2>Topics Generator Save Debug</h2>";

// Test 1: Check if services exist
echo "<h3>Test 1: Service Availability</h3>";
$services_dir = dirname(__FILE__) . '/includes/services/';
$required_services = [
    'class-mkcg-pods-service.php',
    'class-mkcg-authority-hook-service.php',
    'class-mkcg-config.php'
];

foreach ($required_services as $service) {
    $path = $services_dir . $service;
    if (file_exists($path)) {
        echo "✅ Found: $service<br>";
    } else {
        echo "❌ Missing: $service<br>";
    }
}

// Test 2: Simulate AJAX data reception
echo "<h3>Test 2: Simulated AJAX Data</h3>";

// Simulate the data that would come from AJAX
$_POST = [
    'action' => 'mkcg_save_topics_data',
    'nonce' => wp_create_nonce('mkcg_nonce'),
    'post_id' => '32372',
    // JSON format (as sent by simple-ajax.js)
    'topics' => '{"topic_1":"Test Topic 1","topic_2":"Test Topic 2","topic_3":"Test Topic 3"}',
    'authority_hook' => '{"who":"business coaches","what":"build authority","when":"they want to scale","how":"through content"}',
    // Array notation format (as sent by our fix)
    'topics[topic_1]' => 'Test Topic 1',
    'topics[topic_2]' => 'Test Topic 2', 
    'topics[topic_3]' => 'Test Topic 3',
    'authority_hook[who]' => 'business coaches',
    'authority_hook[what]' => 'build authority',
    'authority_hook[when]' => 'they want to scale',
    'authority_hook[how]' => 'through content'
];

echo "<h4>Simulated POST data:</h4>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Test 3: Test data extraction logic
echo "<h3>Test 3: Data Extraction</h3>";

// Test topics extraction
$topics = [];

// Method 1: Array notation
$array_topics = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'topics[') === 0) {
        preg_match('/topics\[(.*?)\]/', $key, $matches);
        if (isset($matches[1]) && !empty(trim($value))) {
            $array_topics[$matches[1]] = $value;
        }
    }
}

if (!empty($array_topics)) {
    $topics = $array_topics;
    echo "✅ Extracted topics from array notation: " . count($topics) . " topics<br>";
}

// Method 2: JSON
if (empty($topics) && isset($_POST['topics'])) {
    $decoded = json_decode(stripslashes($_POST['topics']), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $topics = $decoded;
        echo "✅ Extracted topics from JSON: " . count($topics) . " topics<br>";
    } else {
        echo "❌ JSON decode failed: " . json_last_error_msg() . "<br>";
    }
}

echo "<h4>Extracted topics:</h4>";
echo "<pre>";
print_r($topics);
echo "</pre>";

// Test authority hook extraction
$components = [];

// Method 1: Array notation
$array_components = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'authority_hook[') === 0) {
        preg_match('/authority_hook\[(.*?)\]/', $key, $matches);
        if (isset($matches[1]) && !empty(trim($value))) {
            $array_components[$matches[1]] = $value;
        }
    }
}

if (!empty($array_components)) {
    $components = $array_components;
    echo "✅ Extracted authority hook from array notation: " . count($components) . " components<br>";
}

// Method 2: JSON
if (empty($components) && isset($_POST['authority_hook'])) {
    $decoded = json_decode(stripslashes($_POST['authority_hook']), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $components = $decoded;
        echo "✅ Extracted authority hook from JSON: " . count($components) . " components<br>";
    }
}

echo "<h4>Extracted authority hook:</h4>";
echo "<pre>";
print_r($components);
echo "</pre>";

// Test 4: Check if data would be saved
echo "<h3>Test 4: Save Validation</h3>";

if (empty($topics) && empty($components)) {
    echo "❌ WOULD FAIL: No data extracted - would return 'No data provided to save' error<br>";
} else {
    echo "✅ WOULD SUCCEED: Data extracted successfully<br>";
    echo "Topics count: " . count($topics) . "<br>";
    echo "Authority hook components: " . count($components) . "<br>";
}

// Test 5: Check nonce
echo "<h3>Test 5: Security Check</h3>";
$nonce = $_POST['nonce'] ?? '';
$nonce_valid = wp_verify_nonce($nonce, 'mkcg_nonce');
if ($nonce_valid) {
    echo "✅ Nonce verification passed<br>";
} else {
    echo "❌ Nonce verification failed<br>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>If all tests pass above, the save functionality should work. If not, check the failed tests to identify the issue.</p>";
echo "<p>Common issues:</p>";
echo "<ul>";
echo "<li>Missing services files</li>";
echo "<li>Data not being extracted properly</li>";
echo "<li>Nonce verification failing</li>";
echo "<li>User permissions</li>";
echo "</ul>";
