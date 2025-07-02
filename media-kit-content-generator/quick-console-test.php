<?php
/**
 * Simple Console Test - Quick validation
 */

echo "🧪 MKCG Quick Console Test\n";
echo "==========================\n\n";

// Test 1: Plugin files exist
$plugin_path = __DIR__ . '/';
$required_files = [
    'media-kit-content-generator.php',
    'includes/services/enhanced_formidable_service.php',
    'includes/generators/enhanced_topics_generator.php',
    'includes/generators/enhanced_ajax_handlers.php',
    'assets/js/simple-ajax.js',
    'assets/js/generators/topics-generator.js'
];

$found = 0;
$total = count($required_files);

foreach ($required_files as $file) {
    if (file_exists($plugin_path . $file)) {
        echo "✅ Found: {$file}\n";
        $found++;
    } else {
        echo "❌ Missing: {$file}\n";
    }
}

echo "\n📊 Files Found: {$found}/{$total}\n";

// Test 2: Check PHP syntax
echo "\n🔍 PHP Syntax Check:\n";
$php_files = [
    'includes/services/enhanced_formidable_service.php',
    'includes/generators/enhanced_topics_generator.php',
    'includes/generators/enhanced_ajax_handlers.php'
];

foreach ($php_files as $file) {
    $full_path = $plugin_path . $file;
    if (file_exists($full_path)) {
        $output = shell_exec("php -l {$full_path} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ Syntax OK: {$file}\n";
        } else {
            echo "❌ Syntax Error: {$file}\n";
            echo "   Error: {$output}\n";
        }
    }
}

// Test 3: Basic class loading test
echo "\n🏗️ Class Loading Test:\n";

try {
    include_once $plugin_path . 'includes/services/enhanced_formidable_service.php';
    if (class_exists('Enhanced_Formidable_Service')) {
        echo "✅ Enhanced_Formidable_Service loads\n";
    } else {
        echo "❌ Enhanced_Formidable_Service failed\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading Formidable Service: " . $e->getMessage() . "\n";
}

try {
    include_once $plugin_path . 'includes/generators/enhanced_ajax_handlers.php';
    if (class_exists('Enhanced_AJAX_Handlers')) {
        echo "✅ Enhanced_AJAX_Handlers loads\n";
    } else {
        echo "❌ Enhanced_AJAX_Handlers failed\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading AJAX Handlers: " . $e->getMessage() . "\n";
}

echo "\n🎯 Quick Test Complete!\n";
echo "For full testing, run the browser tests or WordPress integration.\n";
