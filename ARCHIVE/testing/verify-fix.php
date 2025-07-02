<?php
/**
 * Quick Fix Verification for Simplified Media Kit Content Generator
 * 
 * BROWSER ACCESS: http://yoursite.com/wp-content/plugins/media-kit-content-generator/verify-fix.php
 */

// WordPress environment is required
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

echo "<h1>🔧 Simplified System Fix Verification</h1>\n";
echo "<pre>\n";

echo "=== CONFLICTING FILES REMOVED ===\n";

$removed_files = [
    'includes/services/class-mkcg-formidable-service.php',
    'includes/generators/class-mkcg-topics-generator.php', 
    'includes/generators/class-mkcg-topics-ajax-handlers.php',
    'includes/services/class-mkcg-authority-hook-service.php',
    'includes/services/class-mkcg-topics-data-service.php',
    'includes/services/class-mkcg-unified-data-service.php'
];

foreach ($removed_files as $file) {
    $full_path = dirname(__FILE__) . '/' . $file;
    if (!file_exists($full_path)) {
        echo "✅ {$file} - REMOVED\n";
    } else {
        echo "❌ {$file} - STILL EXISTS\n";
    }
}

echo "\n=== SIMPLIFIED FILES PRESENT ===\n";

$simplified_files = [
    'includes/services/enhanced_formidable_service.php',
    'includes/generators/enhanced_topics_generator.php',
    'includes/generators/enhanced_ajax_handlers.php'
];

foreach ($simplified_files as $file) {
    $full_path = dirname(__FILE__) . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ {$file} - EXISTS\n";
    } else {
        echo "❌ {$file} - MISSING\n";
    }
}

echo "\n=== PLUGIN TEST ===\n";

try {
    $plugin = Media_Kit_Content_Generator::get_instance();
    echo "✅ Plugin instance created\n";
    
    $formidable_service = $plugin->get_formidable_service();
    if ($formidable_service) {
        $class_name = get_class($formidable_service);
        echo "✅ Formidable Service: {$class_name}\n";
        
        if ($class_name === 'Enhanced_Formidable_Service') {
            echo "🎉 SUCCESS: Using Enhanced_Formidable_Service!\n";
        } else {
            echo "⚠️ WARNING: Still using {$class_name}\n";
        }
    }
    
    $topics_generator = $plugin->get_generator('topics');
    if ($topics_generator) {
        $class_name = get_class($topics_generator);
        echo "✅ Topics Generator: {$class_name}\n";
        
        if ($class_name === 'Enhanced_Topics_Generator') {
            echo "🎉 SUCCESS: Using Enhanced_Topics_Generator!\n";
        } else {
            echo "⚠️ WARNING: Still using {$class_name}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Plugin test failed: " . $e->getMessage() . "\n";
}

echo "\n=== INSTRUCTIONS ===\n";
echo "1. Clear any WordPress caches\n";
echo "2. If still showing warnings, restart your web server\n";
echo "3. Run the full test suite after clearing caches\n";

echo "</pre>\n";
?>

<style>
body { font-family: monospace; background: #f0f0f0; padding: 20px; }
h1 { color: #333; }
pre { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
</style>
