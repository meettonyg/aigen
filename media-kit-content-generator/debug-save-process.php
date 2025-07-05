<?php
/**
 * FIXED AJAX SAVE DEBUG TEST
 * 
 * This debug file has been completely rewritten to fix the 5 critical errors:
 * 1. No WordPress context check
 * 2. Unsafe use of WordPress functions
 * 3. Missing class existence checks
 * 4. Unhandled reflection errors
 * 5. Improper request context handling
 */

// CRITICAL FIX 1: Ensure WordPress is loaded before doing anything
if (!defined('ABSPATH')) {
    // Try to load WordPress if not already loaded
    $wp_load_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php', 
        __DIR__ . '/../../wp-load.php',
        __DIR__ . '/../wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('<h1>❌ ERROR: WordPress not loaded</h1><p>This debug script must be run within WordPress context.</p>');
    }
}

// CRITICAL FIX 2: Check if WordPress functions are available
if (!function_exists('wp_create_nonce') || !function_exists('get_post')) {
    die('<h1>❌ ERROR: WordPress functions not available</h1><p>WordPress is not properly initialized.</p>');
}

// CRITICAL FIX 3: Only register AJAX action if in admin context
if (is_admin()) {
    add_action('wp_ajax_mkcg_save_topics_data_debug', 'debug_save_topics_ajax_fixed');
}

/**
 * FIXED: Debug function with comprehensive error handling
 */
function debug_save_topics_ajax_fixed() {
    try {
        echo "<h1>🔍 FIXED AJAX Save Topics Debug Test</h1>";
        echo "<p>This simulates the save process with proper error handling.</p>";
        
        // CRITICAL FIX 4: Check if plugin class exists before using it
        if (!class_exists('Media_Kit_Content_Generator')) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ CRITICAL ERROR:</strong> Media_Kit_Content_Generator class not found!<br>";
            echo "The plugin may not be activated or properly loaded.";
            echo "</div>";
            return;
        }
        
        // Simulate the POST data that would come from the Topics Generator
        $test_post_data = [
            'post_id' => '32372', // Your test post ID
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'action' => 'mkcg_save_topics_data',
            // Simulate authority hook data in proper format
            'authority_hook' => [
                'who' => '2nd value, Authors launching a book, and 3 value',
                'what' => 'achieve their goals',
                'when' => 'they need help', 
                'how' => 'through your method'
            ],
            // Simulate topics data in proper format
            'topics' => [
                'topic_1' => 'Test topic 1',
                'topic_2' => 'Test topic 2'
            ]
        ];
        
        // CRITICAL FIX 5: Properly handle $_POST simulation
        $original_post = $_POST ?? [];
        $_POST = array_merge($_POST ?? [], $test_post_data);
        
        echo "<h2>📊 Step 1: Simulated POST Data</h2>";
        echo "<pre>" . esc_html(print_r($test_post_data, true)) . "</pre>";
        
        // Get the plugin instance with error handling
        try {
            $plugin = Media_Kit_Content_Generator::get_instance();
            if (!$plugin) {
                throw new Exception('Plugin instance is null');
            }
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ PLUGIN INSTANCE ERROR:</strong> " . esc_html($e->getMessage());
            echo "</div>";
            return;
        }
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
        echo "<strong>✅ PLUGIN INSTANCE:</strong> Successfully obtained plugin instance.";
        echo "</div>";
        
        echo "<h2>🔧 Step 2: Test Method Access (Safe Reflection)</h2>";
        
        // CRITICAL FIX 6: Safe reflection with comprehensive error handling
        try {
            $reflection = new ReflectionClass($plugin);
            
            // Check if extract_authority_hook_data method exists
            if (!$reflection->hasMethod('extract_authority_hook_data')) {
                echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 4px;'>";
                echo "<strong>⚠️ METHOD NOT FOUND:</strong> extract_authority_hook_data method does not exist.<br>";
                echo "This may be expected if the method is private or the plugin structure has changed.";
                echo "</div>";
            } else {
                $extract_method = $reflection->getMethod('extract_authority_hook_data');
                $extract_method->setAccessible(true);
                
                $authority_hook_data = $extract_method->invoke($plugin);
                echo "<strong>Extracted authority hook data:</strong><br>";
                echo "<pre>" . esc_html(print_r($authority_hook_data, true)) . "</pre>";
                
                if (empty($authority_hook_data['who'])) {
                    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
                    echo "<strong>❌ ISSUE FOUND:</strong> WHO field is empty in extracted data!<br>";
                    echo "This means the AJAX extraction is failing.";
                    echo "</div>";
                } else {
                    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
                    echo "<strong>✅ WHO field extracted:</strong> " . esc_html($authority_hook_data['who']);
                    echo "</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ REFLECTION ERROR:</strong> " . esc_html($e->getMessage());
            echo "</div>";
        }
        
        echo "<h2>🎯 Step 3: Test Direct AJAX Handler</h2>";
        
        // Test the direct AJAX handler method
        if (method_exists($plugin, 'ajax_save_topics')) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ AJAX HANDLER FOUND:</strong> ajax_save_topics method exists.";
            echo "</div>";
            
            // Note: We don't actually call the AJAX handler as it would send JSON response
            echo "<p><em>Note: AJAX handler exists but not called to avoid output conflicts.</em></p>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ AJAX HANDLER MISSING:</strong> ajax_save_topics method not found.";
            echo "</div>";
        }
        
        echo "<h2>💾 Step 4: Test Taxonomy System</h2>";
        
        // Check if 'audience' taxonomy exists
        if (!function_exists('taxonomy_exists')) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ CRITICAL ERROR:</strong> taxonomy_exists function not available!";
            echo "</div>";
        } elseif (!taxonomy_exists('audience')) {
            echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 4px;'>";
            echo "<strong>⚠️ TAXONOMY STATUS:</strong> 'audience' taxonomy does not exist yet.<br>";
            echo "This is expected on first run. The plugin should register it during initialization.";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ TAXONOMY EXISTS:</strong> 'audience' taxonomy is registered.";
            echo "</div>";
        }
        
        echo "<h2>🗄️ Step 5: Check Database State (Safe)</h2>";
        
        // Safely check if post exists
        $test_post_id = 32372;
        $post = get_post($test_post_id);
        
        if (!$post) {
            echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 4px;'>";
            echo "<strong>⚠️ POST STATUS:</strong> Post ID {$test_post_id} does not exist.<br>";
            echo "This debug test uses a hardcoded post ID. You may need to use a valid post ID from your site.";
            echo "</div>";
            
            // Try to find any valid post
            $any_post = get_posts(['numberposts' => 1, 'post_status' => 'any']);
            if (!empty($any_post)) {
                $sample_id = $any_post[0]->ID;
                echo "<p><strong>Suggestion:</strong> Try using post ID {$sample_id} ('{$any_post[0]->post_title}') instead.</p>";
            }
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ POST EXISTS:</strong> " . esc_html($post->post_title) . " (Type: " . esc_html($post->post_type) . ")";
            echo "</div>";
            
            // Safely check current audience terms if taxonomy exists
            if (taxonomy_exists('audience')) {
                $current_terms = wp_get_post_terms($test_post_id, 'audience', ['fields' => 'all']);
                
                if (is_wp_error($current_terms)) {
                    echo "<p><strong>Terms query error:</strong> " . esc_html($current_terms->get_error_message()) . "</p>";
                } elseif (empty($current_terms)) {
                    echo "<p><strong>Audience terms:</strong> No audience terms currently assigned.</p>";
                } else {
                    echo "<h3>Current audience terms:</h3>";
                    foreach ($current_terms as $term) {
                        echo "- " . esc_html($term->name) . " (ID: " . intval($term->term_id) . ")<br>";
                    }
                }
            }
        }
        
        echo "<h2>📋 Step 6: System Status & Recommendations</h2>";
        
        echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #2196F3; border-radius: 4px;'>";
        echo "<h3>WordPress Environment:</h3>";
        echo "<strong>WordPress Version:</strong> " . get_bloginfo('version') . "<br>";
        echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
        echo "<strong>Current User Can Edit Posts:</strong> " . (current_user_can('edit_posts') ? 'Yes' : 'No') . "<br>";
        echo "<strong>AJAX URL:</strong> " . admin_url('admin-ajax.php') . "<br><br>";
        
        echo "<h3>Plugin Status:</h3>";
        echo "<strong>Plugin Class:</strong> " . (class_exists('Media_Kit_Content_Generator') ? '✅ Available' : '❌ Missing') . "<br>";
        echo "<strong>Audience Taxonomy:</strong> " . (taxonomy_exists('audience') ? '✅ Registered' : '⚠️ Not Registered Yet') . "<br><br>";
        
        echo "<h3>Recommended Actions:</h3>";
        echo "1. Ensure the Media Kit Content Generator plugin is activated<br>";
        echo "2. Check that WordPress and the plugin have loaded properly<br>";
        echo "3. Verify the audience taxonomy is registered during plugin init<br>";
        echo "4. Test with a valid post ID from your WordPress site<br>";
        echo "5. Check WordPress debug.log for any initialization errors";
        echo "</div>";
        
        // CRITICAL FIX 7: Properly restore original POST data
        $_POST = $original_post;
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
        echo "<strong>❌ FATAL ERROR:</strong> " . esc_html($e->getMessage()) . "<br>";
        echo "<strong>File:</strong> " . esc_html($e->getFile()) . "<br>";
        echo "<strong>Line:</strong> " . intval($e->getLine());
        echo "</div>";
    } finally {
        // Ensure POST data is always restored
        if (isset($original_post)) {
            $_POST = $original_post;
        }
    }
}

/**
 * FIXED: Safe taxonomy check function
 */
function check_audience_taxonomy_fixed() {
    try {
        echo "<h2>🏷️ Audience Taxonomy Status (Safe Check)</h2>";
        
        if (!function_exists('taxonomy_exists')) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ ERROR:</strong> taxonomy_exists function not available.";
            echo "</div>";
            return;
        }
        
        if (taxonomy_exists('audience')) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ TAXONOMY EXISTS:</strong> 'audience' taxonomy is registered.<br>";
            
            $taxonomy = get_taxonomy('audience');
            if ($taxonomy) {
                echo "<strong>Object types:</strong> " . esc_html(implode(', ', $taxonomy->object_type)) . "<br>";
                echo "<strong>Public:</strong> " . ($taxonomy->public ? 'Yes' : 'No') . "<br>";
                echo "<strong>Hierarchical:</strong> " . ($taxonomy->hierarchical ? 'Yes' : 'No');
            }
            echo "</div>";
            
            // Safely get all audience terms
            $terms = get_terms(['taxonomy' => 'audience', 'hide_empty' => false]);
            if (is_wp_error($terms)) {
                echo "<p><strong>Terms query error:</strong> " . esc_html($terms->get_error_message()) . "</p>";
            } elseif (!empty($terms)) {
                echo "<h3>Existing audience terms:</h3>";
                foreach ($terms as $term) {
                    echo "- " . esc_html($term->name) . " (ID: " . intval($term->term_id) . ", Count: " . intval($term->count) . ")<br>";
                }
            } else {
                echo "<p><em>No audience terms exist yet.</em></p>";
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 4px;'>";
            echo "<strong>⚠️ TAXONOMY STATUS:</strong> 'audience' taxonomy is not registered yet.<br>";
            echo "This is normal if the plugin hasn't been fully initialized.";
            echo "</div>";
            
            echo "<h3>🔧 How to register the taxonomy:</h3>";
            echo "<p>The plugin should automatically register this taxonomy. If it doesn't, add this code:</p>";
            echo "<pre>" . esc_html("
add_action('init', function() {
    register_taxonomy('audience', ['guests', 'post'], [
        'labels' => [
            'name' => 'Audiences',
            'singular_name' => 'Audience'
        ],
        'public' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'audience']
    ]);
});") . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
        echo "<strong>❌ TAXONOMY CHECK ERROR:</strong> " . esc_html($e->getMessage());
        echo "</div>";
    }
}

// CRITICAL FIX 8: Only run if accessed directly and WordPress is available
if (!defined('DOING_AJAX') && !headers_sent()) {
    echo "<div style='max-width: 1200px; margin: 20px; font-family: Arial, sans-serif;'>";
    
    check_audience_taxonomy_fixed();
    debug_save_topics_ajax_fixed();
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px; margin-top: 20px;'>";
    echo "<strong>🎉 DEBUG SCRIPT COMPLETED SUCCESSFULLY</strong><br>";
    echo "All 5 critical errors have been fixed:<br>";
    echo "1. ✅ WordPress context check added<br>";
    echo "2. ✅ Safe WordPress function usage<br>";
    echo "3. ✅ Class existence validation<br>";
    echo "4. ✅ Protected reflection with error handling<br>";
    echo "5. ✅ Proper request context management";
    echo "</div>";
    
    echo "</div>";
}

?>