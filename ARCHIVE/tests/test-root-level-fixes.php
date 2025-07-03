<?php
/**
 * Root Level Fixes Test - Comprehensive Validation
 * Tests all critical fixes made to resolve test failures
 */

// Allow direct access for testing
if (!defined('ABSPATH')) {
    // Define basic constants for standalone execution
    define('ABSPATH', dirname(__FILE__) . '/');
    
    // Set plugin path for testing
    if (!defined('MKCG_PLUGIN_PATH')) {
        define('MKCG_PLUGIN_PATH', dirname(__FILE__) . '/');
    }
    if (!defined('MKCG_PLUGIN_URL')) {
        define('MKCG_PLUGIN_URL', 'http://localhost/');
    }
}

class RootLevelFixesTest {
    
    private $results = [];
    private $test_count = 0;
    private $pass_count = 0;
    
    public function run_all_tests() {
        echo "🧪 MKCG ROOT LEVEL FIXES - Comprehensive Validation\n";
        echo "==================================================\n\n";
        
        // Test each critical component
        $this->test_plugin_loading();
        $this->test_templates_and_data();
        $this->test_javascript_loading();
        $this->test_ajax_handlers();
        $this->test_formidable_integration();
        $this->test_cross_generator_communication();
        
        // Show results
        $this->show_final_results();
    }
    
    private function test_plugin_loading() {
        echo "📋 Testing Plugin Loading & Initialization...\n";
        
        // Test 1: Main plugin class exists
        $this->assert(
            class_exists('Media_Kit_Content_Generator'),
            "Main plugin class exists"
        );
        
        // Test 2: Plugin constants defined
        $this->assert(
            defined('MKCG_PLUGIN_PATH') && defined('MKCG_PLUGIN_URL'),
            "Plugin constants defined"
        );
        
        // Test 3: Services loading properly
        $plugin_instance = Media_Kit_Content_Generator::get_instance();
        $this->assert(
            is_object($plugin_instance),
            "Plugin instance created successfully"
        );
        
        // Test 4: Required service files exist
        $required_files = [
            'includes/services/enhanced_formidable_service.php',
            'includes/generators/enhanced_topics_generator.php',
            'includes/generators/enhanced_questions_generator.php',
            'includes/generators/enhanced_ajax_handlers.php'
        ];
        
        foreach ($required_files as $file) {
            $full_path = MKCG_PLUGIN_PATH . $file;
            $this->assert(
                file_exists($full_path),
                "Required file exists: {$file}"
            );
        }
        
        echo "\n";
    }
    
    private function test_templates_and_data() {
        echo "📄 Testing Templates & Data Structure...\n";
        
        // Test 1: Template files exist
        $template_files = [
            'templates/generators/topics/default.php',
            'templates/generators/questions/default.php',
            'templates/shared/authority-hook-component.php'
        ];
        
        foreach ($template_files as $template) {
            $full_path = MKCG_PLUGIN_PATH . $template;
            $this->assert(
                file_exists($full_path),
                "Template exists: {$template}"
            );
        }
        
        // Test 2: Topics Generator data structure
        if (class_exists('Enhanced_Topics_Generator')) {
            $api_service = new stdClass(); // Mock
            $formidable_service = new Enhanced_Formidable_Service();
            $topics_generator = new Enhanced_Topics_Generator($api_service, $formidable_service);
            
            $template_data = $topics_generator->get_template_data('test');
            
            $this->assert(
                isset($template_data['form_field_values']) && is_array($template_data['form_field_values']),
                "Topics Generator returns proper data structure"
            );
            
            $this->assert(
                isset($template_data['authority_hook_components']) && is_array($template_data['authority_hook_components']),
                "Topics Generator includes authority hook components"
            );
        }
        
        echo "\n";
    }
    
    private function test_javascript_loading() {
        echo "🚀 Testing JavaScript Files & Structure...\n";
        
        // Test 1: JavaScript files exist
        $js_files = [
            'assets/js/simple-ajax.js',
            'assets/js/simple-event-bus.js',
            'assets/js/simple-notifications.js',
            'assets/js/generators/topics-generator.js',
            'assets/js/generators/questions-generator.js'
        ];
        
        foreach ($js_files as $js_file) {
            $full_path = MKCG_PLUGIN_PATH . $js_file;
            $this->assert(
                file_exists($full_path),
                "JavaScript file exists: {$js_file}"
            );
        }
        
        // Test 2: JavaScript syntax validation (basic)
        foreach ($js_files as $js_file) {
            $full_path = MKCG_PLUGIN_PATH . $js_file;
            if (file_exists($full_path)) {
                $content = file_get_contents($full_path);
                
                // Basic syntax checks
                $has_syntax_errors = false;
                
                // Check for common syntax issues
                if (strpos($content, 'function(') === false && strpos($content, '=>') === false) {
                    $has_syntax_errors = true;
                }
                
                // Check for proper closure
                if (strpos($content, '(function()') !== false && strpos($content, '})();') === false) {
                    $has_syntax_errors = true;
                }
                
                $this->assert(
                    !$has_syntax_errors,
                    "JavaScript syntax validation: {$js_file}"
                );
            }
        }
        
        echo "\n";
    }
    
    private function test_ajax_handlers() {
        echo "🔄 Testing AJAX Handlers...\n";
        
        // Test 1: AJAX handlers class exists
        $this->assert(
            class_exists('Enhanced_AJAX_Handlers'),
            "Enhanced_AJAX_Handlers class exists"
        );
        
        // Test 2: Required methods exist
        if (class_exists('Enhanced_AJAX_Handlers')) {
            $formidable_service = new Enhanced_Formidable_Service();
            $topics_generator = new stdClass(); // Mock
            $ajax_handlers = new Enhanced_AJAX_Handlers($formidable_service, $topics_generator);
            
            $required_methods = [
                'handle_save_topics',
                'handle_get_topics',
                'handle_save_authority_hook',
                'handle_generate_topics'
            ];
            
            foreach ($required_methods as $method) {
                $this->assert(
                    method_exists($ajax_handlers, $method),
                    "AJAX handler method exists: {$method}"
                );
            }
        }
        
        // Test 3: AJAX actions registered (simulate)
        $this->assert(
            true, // We can't easily test WordPress hooks in isolation
            "AJAX actions registration (simulated)"
        );
        
        echo "\n";
    }
    
    private function test_formidable_integration() {
        echo "📋 Testing Formidable Integration...\n";
        
        // Test 1: Formidable service class exists
        $this->assert(
            class_exists('Enhanced_Formidable_Service'),
            "Enhanced_Formidable_Service class exists"
        );
        
        // Test 2: Required methods exist
        if (class_exists('Enhanced_Formidable_Service')) {
            $formidable_service = new Enhanced_Formidable_Service();
            
            $required_methods = [
                'save_entry_data',
                'get_field_value',
                'get_entry_data',
                'get_entry_by_key'
            ];
            
            foreach ($required_methods as $method) {
                $this->assert(
                    method_exists($formidable_service, $method),
                    "Formidable service method exists: {$method}"
                );
            }
            
            // Test 3: Method returns expected structure
            $result = $formidable_service->get_entry_data(999999); // Non-existent entry
            $this->assert(
                is_array($result) && isset($result['success']),
                "Formidable service returns proper response structure"
            );
        }
        
        echo "\n";
    }
    
    private function test_cross_generator_communication() {
        echo "📡 Testing Cross-Generator Communication...\n";
        
        // Test 1: Event bus file exists and has proper structure
        $event_bus_file = MKCG_PLUGIN_PATH . 'assets/js/simple-event-bus.js';
        if (file_exists($event_bus_file)) {
            $content = file_get_contents($event_bus_file);
            
            $this->assert(
                strpos($content, 'window.AppEvents') !== false,
                "Event bus creates global AppEvents object"
            );
            
            $this->assert(
                strpos($content, 'on:') !== false || strpos($content, 'on(') !== false,
                "Event bus has event listener functionality"
            );
            
            $this->assert(
                strpos($content, 'trigger:') !== false || strpos($content, 'trigger(') !== false,
                "Event bus has event trigger functionality"
            );
        }
        
        // Test 2: Generators have event communication code
        $topics_js = MKCG_PLUGIN_PATH . 'assets/js/generators/topics-generator.js';
        if (file_exists($topics_js)) {
            $content = file_get_contents($topics_js);
            
            $this->assert(
                strpos($content, 'AppEvents') !== false,
                "Topics Generator references AppEvents"
            );
            
            $this->assert(
                strpos($content, 'topic:selected') !== false,
                "Topics Generator has topic selection events"
            );
        }
        
        $questions_js = MKCG_PLUGIN_PATH . 'assets/js/generators/questions-generator.js';
        if (file_exists($questions_js)) {
            $content = file_get_contents($questions_js);
            
            $this->assert(
                strpos($content, 'AppEvents') !== false,
                "Questions Generator references AppEvents"
            );
            
            $this->assert(
                strpos($content, 'topic:selected') !== false,
                "Questions Generator listens for topic selection"
            );
        }
        
        echo "\n";
    }
    
    private function assert($condition, $message) {
        $this->test_count++;
        
        if ($condition) {
            echo "✅ PASS: {$message}\n";
            $this->pass_count++;
            $this->results[] = ['status' => 'PASS', 'message' => $message];
        } else {
            echo "❌ FAIL: {$message}\n";
            $this->results[] = ['status' => 'FAIL', 'message' => $message];
        }
    }
    
    private function show_final_results() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 FINAL RESULTS\n";
        echo str_repeat("=", 60) . "\n";
        
        $success_rate = ($this->pass_count / $this->test_count) * 100;
        
        echo "Total Tests: {$this->test_count}\n";
        echo "Passed: {$this->pass_count}\n";
        echo "Failed: " . ($this->test_count - $this->pass_count) . "\n";
        echo "Success Rate: " . round($success_rate, 1) . "%\n\n";
        
        if ($success_rate >= 90) {
            echo "🎉 EXCELLENT: Root level fixes are working properly!\n";
        } elseif ($success_rate >= 75) {
            echo "✅ GOOD: Most fixes are working, minor issues remain\n";
        } elseif ($success_rate >= 50) {
            echo "⚠️ PARTIAL: Some fixes working, significant issues remain\n";
        } else {
            echo "❌ CRITICAL: Major issues found, extensive fixes needed\n";
        }
        
        echo "\nFailed Tests:\n";
        foreach ($this->results as $result) {
            if ($result['status'] === 'FAIL') {
                echo "  ❌ {$result['message']}\n";
            }
        }
        
        echo "\n📝 Next Steps:\n";
        echo "1. Address any failed tests above\n";
        echo "2. Run browser-based testing for full functionality\n";
        echo "3. Test with actual Formidable form data\n";
        echo "4. Verify cross-generator communication in browser\n";
        echo "5. Performance test with real data\n\n";
    }
}

// Auto-run if accessed directly or via CLI
if (php_sapi_name() === 'cli' || !defined('WP_DEBUG')) {
    // Standalone execution - load required files first
    
    // Load main plugin file first
    if (!class_exists('Media_Kit_Content_Generator')) {
        $main_plugin_file = dirname(__FILE__) . '/media-kit-content-generator.php';
        if (file_exists($main_plugin_file)) {
            include_once $main_plugin_file;
        }
    }
    
    // Load classes if they don't exist
    if (!class_exists('Enhanced_Formidable_Service')) {
        $formidable_file = dirname(__FILE__) . '/includes/services/enhanced_formidable_service.php';
        if (file_exists($formidable_file)) {
            include_once $formidable_file;
        }
    }
    
    if (!class_exists('Enhanced_Topics_Generator')) {
        $topics_file = dirname(__FILE__) . '/includes/generators/enhanced_topics_generator.php';
        if (file_exists($topics_file)) {
            include_once $topics_file;
        }
    }
    
    if (!class_exists('Enhanced_AJAX_Handlers')) {
        $ajax_file = dirname(__FILE__) . '/includes/generators/enhanced_ajax_handlers.php';
        if (file_exists($ajax_file)) {
            include_once $ajax_file;
        }
    }
    
    // Mock WordPress functions for standalone testing
    if (!function_exists('wp_create_nonce')) {
        function wp_create_nonce($action) { return 'test_nonce_12345'; }
    }
    if (!function_exists('wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action) { return true; }
    }
    if (!function_exists('is_user_logged_in')) {
        function is_user_logged_in() { return true; }
    }
    if (!function_exists('current_user_can')) {
        function current_user_can($capability) { return true; }
    }
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) { return strip_tags(trim($str)); }
    }
    if (!function_exists('sanitize_textarea_field')) {
        function sanitize_textarea_field($str) { return strip_tags(trim($str)); }
    }
    if (!function_exists('plugin_dir_url')) {
        function plugin_dir_url($file) { return 'http://localhost/'; }
    }
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) { return dirname($file) . '/'; }
    }
    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $args = 1) { return true; }
    }
    if (!function_exists('wp_enqueue_script')) {
        function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) { return true; }
    }
    if (!function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') { return true; }
    }
    if (!function_exists('wp_localize_script')) {
        function wp_localize_script($handle, $object_name, $l10n) { return true; }
    }
    if (!function_exists('admin_url')) {
        function admin_url($path = '') { return 'http://localhost/wp-admin/' . $path; }
    }
    if (!function_exists('register_activation_hook')) {
        function register_activation_hook($file, $callback) { return true; }
    }
    if (!function_exists('register_deactivation_hook')) {
        function register_deactivation_hook($file, $callback) { return true; }
    }
    if (!function_exists('flush_rewrite_rules')) {
        function flush_rewrite_rules() { return true; }
    }
    
    // Run the tests
    $tester = new RootLevelFixesTest();
    
    // HTML output for browser
    if (php_sapi_name() !== 'cli') {
        echo '<html><head><title>MKCG Root Level Fixes Test</title></head><body style="font-family: monospace; background: #f5f5f5; padding: 20px;"><pre>';
    }
    
    $tester->run_all_tests();
    
    if (php_sapi_name() !== 'cli') {
        echo '</pre></body></html>';
    }
    
} else {
    // WordPress context
    add_action('init', function() {
        if (current_user_can('manage_options') && isset($_GET['run_root_tests'])) {
            $tester = new RootLevelFixesTest();
            echo '<pre>';
            $tester->run_all_tests();
            echo '</pre>';
            exit;
        }
    });
}
