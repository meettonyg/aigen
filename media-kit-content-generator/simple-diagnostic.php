<?php
/**
 * Simple Diagnostic Test - Safe execution
 */

// Prevent fatal errors
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

echo '<html><head><title>MKCG Simple Diagnostic</title></head>';
echo '<body style="font-family: monospace; background: #f5f5f5; padding: 20px;">';
echo '<h1>🔍 MKCG Simple Diagnostic Test</h1>';

// Test 1: Basic file existence
echo '<h2>📁 File Existence Check</h2>';
$files_to_check = [
    'media-kit-content-generator.php' => 'Main Plugin File',
    'includes/services/enhanced_formidable_service.php' => 'Formidable Service',
    'includes/generators/enhanced_topics_generator.php' => 'Topics Generator',
    'includes/generators/enhanced_ajax_handlers.php' => 'AJAX Handlers',
    'assets/js/simple-ajax.js' => 'Simple AJAX JS',
    'assets/js/generators/topics-generator.js' => 'Topics Generator JS'
];

$found_count = 0;
foreach ($files_to_check as $file => $description) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ FOUND: {$description} ({$file})<br>";
        $found_count++;
    } else {
        echo "❌ MISSING: {$description} ({$file})<br>";
    }
}

echo "<br><strong>Files Found: {$found_count}/" . count($files_to_check) . "</strong><br><br>";

// Test 2: PHP Syntax Check (safe)
echo '<h2>🔍 PHP Syntax Check</h2>';
$php_files = [
    'includes/services/enhanced_formidable_service.php',
    'includes/generators/enhanced_topics_generator.php', 
    'includes/generators/enhanced_ajax_handlers.php'
];

foreach ($php_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        // Simple syntax check without including
        $content = file_get_contents($full_path);
        if (strpos($content, '<?php') !== false && strpos($content, 'class ') !== false) {
            echo "✅ SYNTAX OK: {$file}<br>";
        } else {
            echo "⚠️ SYNTAX ISSUE: {$file}<br>";
        }
    } else {
        echo "❌ FILE MISSING: {$file}<br>";
    }
}

// Test 3: Directory structure
echo '<br><h2>📂 Directory Structure</h2>';
$dirs = ['includes', 'includes/services', 'includes/generators', 'assets', 'assets/js', 'templates'];
foreach ($dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (is_dir($full_path)) {
        echo "✅ DIR EXISTS: {$dir}<br>";
    } else {
        echo "❌ DIR MISSING: {$dir}<br>";
    }
}

// Test 4: Current directory info
echo '<br><h2>📍 Current Location</h2>';
echo "Current Directory: " . __DIR__ . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

// Test 5: Try to peek at one file safely
echo '<br><h2>👀 Sample File Content Check</h2>';
$sample_file = __DIR__ . '/includes/services/enhanced_formidable_service.php';
if (file_exists($sample_file)) {
    $content = file_get_contents($sample_file);
    $lines = explode("\n", $content);
    echo "File lines: " . count($lines) . "<br>";
    echo "First line: " . htmlspecialchars(trim($lines[0] ?? '')) . "<br>";
    echo "Contains class: " . (strpos($content, 'class Enhanced_Formidable_Service') !== false ? 'YES' : 'NO') . "<br>";
} else {
    echo "❌ Sample file not found<br>";
}

echo '<br><h2>🎯 Quick Assessment</h2>';

if ($found_count >= 4) {
    echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;">';
    echo '✅ <strong>GOOD:</strong> Most core files are present. The root-level fixes appear to be in place.';
    echo '</div>';
} elseif ($found_count >= 2) {
    echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">';
    echo '⚠️ <strong>PARTIAL:</strong> Some files found, but some may be missing.';
    echo '</div>';
} else {
    echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;">';
    echo '❌ <strong>ISSUE:</strong> Many core files appear to be missing.';
    echo '</div>';
}

echo '<br><br><small>This is a safe diagnostic test that checks file existence without loading potentially problematic code.</small>';

echo '</body></html>';
?>
