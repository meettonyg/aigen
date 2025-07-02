<?php
/**
 * Test Script for Simplified Media Kit Content Generator
 * 
 * BROWSER ACCESS: http://yoursite.com/wp-content/plugins/media-kit-content-generator/test-simplified-system.php
 * 
 * This script validates the core functionality of the simplified system:
 * 1. Enhanced Formidable Service
 * 2. Enhanced Topics Generator  
 * 3. Enhanced AJAX Handlers
 * 4. Simple AJAX Manager (JavaScript)
 */

// WordPress environment is required
if (!defined('ABSPATH')) {
    // Include WordPress from plugin location: wp-content/plugins/media-kit-content-generator/
    // WordPress root is typically 3 levels up: ../../../wp-config.php
    require_once('../../../wp-config.php');
}

echo "<h1>Simplified Media Kit Content Generator - Test Suite</h1>\n";
echo "<pre>\n";

// Test 1: Check if simplified classes are loaded
echo "=== TEST 1: CLASS LOADING ===\n";

$classes_to_test = [
    'Enhanced_Formidable_Service',
    'Enhanced_AJAX_Handlers', 
    'Enhanced_Topics_Generator'
];

foreach ($classes_to_test as $class) {
    if (class_exists($class)) {
        echo "✅ {$class} - LOADED\n";
    } else {
        echo "❌ {$class} - NOT FOUND\n";
    }
}

// Test 2: Initialize main plugin and check services
echo "\n=== TEST 2: PLUGIN INITIALIZATION ===\n";

try {
    $plugin = Media_Kit_Content_Generator::get_instance();
    echo "✅ Plugin instance created\n";
    
    $api_service = $plugin->get_api_service();
    if ($api_service) {
        echo "✅ API Service initialized\n";
    } else {
        echo "❌ API Service not available\n";
    }
    
    $formidable_service = $plugin->get_formidable_service();
    if ($formidable_service) {
        echo "✅ Formidable Service initialized\n";
    } else {
        echo "❌ Formidable Service not available\n";
    }
    
    $topics_generator = $plugin->get_generator('topics');
    if ($topics_generator) {
        echo "✅ Topics Generator initialized\n";
    } else {
        echo "❌ Topics Generator not available\n";
    }
    
} catch (Exception $e) {
    echo "❌ Plugin initialization failed: " . $e->getMessage() . "\n";
}

// Test 3: Test Enhanced Formidable Service basic functionality
echo "\n=== TEST 3: ENHANCED FORMIDABLE SERVICE ===\n";

if ($formidable_service && $formidable_service instanceof Enhanced_Formidable_Service) {
    echo "✅ Enhanced Formidable Service is correct class\n";
    
    // Test method existence
    $methods = ['save_entry_data', 'get_field_value', 'get_entry_data'];
    foreach ($methods as $method) {
        if (method_exists($formidable_service, $method)) {
            echo "✅ Method {$method} exists\n";
        } else {
            echo "❌ Method {$method} missing\n";
        }
    }
} else {
    echo "❌ Enhanced Formidable Service not properly initialized\n";
}

// Test 4: Test Enhanced Topics Generator basic functionality  
echo "\n=== TEST 4: ENHANCED TOPICS GENERATOR ===\n";

if ($topics_generator && $topics_generator instanceof Enhanced_Topics_Generator) {
    echo "✅ Enhanced Topics Generator is correct class\n";
    
    // Test method existence
    $methods = ['get_template_data', 'generate_topics', 'save_topics'];
    foreach ($methods as $method) {
        if (method_exists($topics_generator, $method)) {
            echo "✅ Method {$method} exists\n";
        } else {
            echo "❌ Method {$method} missing\n";
        }
    }
    
    // Test template data structure
    try {
        $template_data = $topics_generator->get_template_data();
        if (is_array($template_data)) {
            echo "✅ Template data returns array\n";
            
            $expected_keys = ['entry_id', 'topics', 'authority_hook', 'has_data'];
            foreach ($expected_keys as $key) {
                if (array_key_exists($key, $template_data)) {
                    echo "✅ Template data has '{$key}' key\n";
                } else {
                    echo "❌ Template data missing '{$key}' key\n";
                }
            }
        } else {
            echo "❌ Template data is not an array\n";
        }
    } catch (Exception $e) {
        echo "❌ Template data generation failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Enhanced Topics Generator not properly initialized\n";
}

// Test 5: Check JavaScript file existence
echo "\n=== TEST 5: JAVASCRIPT FILES ===\n";

$js_files = [
    'assets/js/simple_ajax_manager.js'
];

foreach ($js_files as $js_file) {
    $full_path = dirname(__FILE__) . '/' . $js_file;
    if (file_exists($full_path)) {
        echo "✅ {$js_file} - EXISTS\n";
        
        // Check file size
        $file_size = filesize($full_path);
        echo "   File size: " . number_format($file_size) . " bytes\n";
        
        // Check for key JavaScript classes/functions
        $content = file_get_contents($full_path);
        if (strpos($content, 'SimpleAjaxManager') !== false) {
            echo "✅ SimpleAjaxManager class found\n";
        } else {
            echo "❌ SimpleAjaxManager class not found\n";
        }
        
    } else {
        echo "❌ {$js_file} - NOT FOUND\n";
    }
}

// Test 6: AJAX Action Registration
echo "\n=== TEST 6: AJAX ACTIONS ===\n";

$ajax_actions = [
    'mkcg_save_topics_data',
    'mkcg_get_topics_data', 
    'mkcg_save_authority_hook',
    'mkcg_generate_topics'
];

foreach ($ajax_actions as $action) {
    if (has_action('wp_ajax_' . $action) || has_action('wp_ajax_nopriv_' . $action)) {
        echo "✅ AJAX action '{$action}' registered\n";
    } else {
        echo "❌ AJAX action '{$action}' not registered\n";
    }
}

// Test 7: Shortcode Registration
echo "\n=== TEST 7: SHORTCODES ===\n";

$shortcodes = ['mkcg_topics', 'mkcg_biography', 'mkcg_offers', 'mkcg_questions'];

foreach ($shortcodes as $shortcode) {
    if (shortcode_exists($shortcode)) {
        echo "✅ Shortcode [{$shortcode}] registered\n";
    } else {
        echo "❌ Shortcode [{$shortcode}] not registered\n";
    }
}

// Test Summary
echo "\n=== TEST SUMMARY ===\n";
echo "Simplified Media Kit Content Generator system tested.\n";
echo "Check above results for any ❌ FAILED items that need attention.\n";
echo "All ✅ PASSED items indicate successful simplification.\n";

echo "</pre>\n";

// Add simple HTML interface for manual testing
?>
<h2>Manual Testing Interface</h2>
<div style="background: #f0f0f0; padding: 20px; margin: 20px 0;">
    <h3>JavaScript Console Commands</h3>
    <p>Open browser console and try these commands:</p>
    <ul>
        <li><code>window.SimpleAjaxManager</code> - Should show the AJAX manager object</li>
        <li><code>window.SimpleAjaxManager.request('test_action', {test: 'data'})</code> - Test AJAX request</li>
        <li><code>console.log('Simple AJAX Manager Status:', window.SimpleAjaxManager ? 'LOADED' : 'NOT LOADED')</code></li>
    </ul>
    
    <h3>WordPress Admin Testing</h3>
    <ul>
        <li>Check if scripts load on Topics Generator pages</li>
        <li>Verify AJAX requests work in browser Network tab</li>
        <li>Test topics generation and saving functionality</li>
    </ul>
</div>

<script>
// JavaScript validation
console.log('=== JAVASCRIPT VALIDATION ===');
console.log('SimpleAjaxManager available:', typeof window.SimpleAjaxManager !== 'undefined');
console.log('mkcg_vars available:', typeof window.mkcg_vars !== 'undefined');

if (typeof window.SimpleAjaxManager !== 'undefined') {
    console.log('✅ SimpleAjaxManager loaded successfully');
    console.log('AJAX URL:', window.SimpleAjaxManager.ajaxUrl);
    console.log('Nonce:', window.SimpleAjaxManager.nonce ? 'SET' : 'MISSING');
} else {
    console.log('❌ SimpleAjaxManager not loaded');
}
</script>
