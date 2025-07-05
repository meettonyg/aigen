<?php
/**
 * COMPREHENSIVE AJAX SAVE DEBUG TEST
 * 
 * This will help us trace exactly what's happening during the save process
 * and identify why the taxonomy assignment is failing.
 */

// Add this to your WordPress debug.log monitoring
add_action('wp_ajax_mkcg_save_topics_data_debug', 'debug_save_topics_ajax');

function debug_save_topics_ajax() {
    echo "<h1>🔍 AJAX Save Topics Debug Test</h1>";
    echo "<p>This simulates the exact save process to identify the taxonomy issue.</p>";
    
    // Simulate the POST data that would come from the Topics Generator
    $test_post_data = [
        'post_id' => '32372', // Your test post ID
        'nonce' => wp_create_nonce('mkcg_nonce'),
        'action' => 'mkcg_save_topics_data',
        // Simulate authority hook data
        'who' => '2nd value, Authors launching a book, and 3 value',
        'what' => 'achieve their goals',
        'when' => 'they need help', 
        'how' => 'through your method',
        // Simulate topics data
        'topic_1' => 'Test topic 1',
        'topic_2' => 'Test topic 2'
    ];
    
    // Temporarily override $_POST for testing
    $original_post = $_POST;
    $_POST = $test_post_data;
    
    echo "<h2>📊 Step 1: Simulated POST Data</h2>";
    echo "<pre>" . print_r($test_post_data, true) . "</pre>";
    
    // Get the plugin instance
    $plugin = Media_Kit_Content_Generator::get_instance();
    
    echo "<h2>🔧 Step 2: Extract Authority Hook Data</h2>";
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($plugin);
    $extract_method = $reflection->getMethod('extract_authority_hook_data');
    $extract_method->setAccessible(true);
    
    $authority_hook_data = $extract_method->invoke($plugin);
    echo "<strong>Extracted authority hook data:</strong><br>";
    echo "<pre>" . print_r($authority_hook_data, true) . "</pre>";
    
    if (empty($authority_hook_data['who'])) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
        echo "<strong>❌ ISSUE FOUND:</strong> WHO field is empty in extracted data!<br>";
        echo "This means the AJAX extraction is failing.";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
        echo "<strong>✅ WHO field extracted:</strong> " . $authority_hook_data['who'];
        echo "</div>";
    }
    
    echo "<h2>🎯 Step 3: Test Audience Parsing</h2>";
    
    if (!empty($authority_hook_data['who'])) {
        $parse_method = $reflection->getMethod('parse_audience_string');
        $parse_method->setAccessible(true);
        
        $parsed_audiences = $parse_method->invoke($plugin, $authority_hook_data['who']);
        echo "<strong>Parsed audiences:</strong><br>";
        echo "<pre>" . print_r($parsed_audiences, true) . "</pre>";
        
        if (count($parsed_audiences) === 3) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ PARSING SUCCESS:</strong> All 3 audiences extracted correctly!";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ PARSING ISSUE:</strong> Expected 3 audiences, got " . count($parsed_audiences);
            echo "</div>";
        }
    }
    
    echo "<h2>💾 Step 4: Test Taxonomy Assignment</h2>";
    
    if (!empty($authority_hook_data['who'])) {
        $post_id = 32372;
        
        // Check if 'audience' taxonomy exists
        if (!taxonomy_exists('audience')) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ CRITICAL ISSUE:</strong> 'audience' taxonomy does not exist!<br>";
            echo "The taxonomy must be registered before terms can be assigned.";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ TAXONOMY EXISTS:</strong> 'audience' taxonomy is registered.";
            echo "</div>";
        }
        
        // Test the save_audience_taxonomy method
        $save_method = $reflection->getMethod('save_audience_taxonomy');
        $save_method->setAccessible(true);
        
        echo "<h3>Testing save_audience_taxonomy method...</h3>";
        $save_result = $save_method->invoke($plugin, $post_id, $authority_hook_data['who']);
        
        echo "<strong>Save result:</strong><br>";
        echo "<pre>" . print_r($save_result, true) . "</pre>";
        
        if ($save_result['success']) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
            echo "<strong>✅ TAXONOMY SAVE SUCCESS:</strong> " . $save_result['message'];
            echo "<br><strong>Term IDs assigned:</strong> " . implode(', ', $save_result['term_ids']);
            echo "</div>";
            
            // Verify the assignment
            echo "<h3>Verifying taxonomy assignment...</h3>";
            $assigned_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);
            echo "<strong>Currently assigned audience terms:</strong><br>";
            echo "<pre>" . print_r($assigned_terms, true) . "</pre>";
            
            if (count($assigned_terms) === 3) {
                echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
                echo "<strong>🎉 COMPLETE SUCCESS:</strong> All 3 audiences are assigned to the post!";
                echo "</div>";
            } else {
                echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 4px;'>";
                echo "<strong>⚠️ PARTIAL SUCCESS:</strong> Expected 3 terms, got " . count($assigned_terms);
                echo "</div>";
            }
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
            echo "<strong>❌ TAXONOMY SAVE FAILED:</strong> " . $save_result['message'];
            echo "</div>";
        }
    }
    
    echo "<h2>🗄️ Step 5: Check Database State</h2>";
    
    // Check if post exists
    $post = get_post(32372);
    if (!$post) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
        echo "<strong>❌ POST NOT FOUND:</strong> Post ID 32372 does not exist!";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
        echo "<strong>✅ POST EXISTS:</strong> " . $post->post_title . " (Type: " . $post->post_type . ")";
        echo "</div>";
    }
    
    // Check current audience terms
    if ($post) {
        $current_terms = wp_get_post_terms(32372, 'audience', ['fields' => 'all']);
        echo "<h3>Current audience taxonomy state:</h3>";
        if (empty($current_terms)) {
            echo "<strong>No audience terms currently assigned.</strong>";
        } else {
            echo "<strong>Assigned terms:</strong><br>";
            foreach ($current_terms as $term) {
                echo "- " . $term->name . " (ID: " . $term->term_id . ")<br>";
            }
        }
    }
    
    echo "<h2>📋 Step 6: Recommendations</h2>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #2196F3; border-radius: 4px;'>";
    
    if (!taxonomy_exists('audience')) {
        echo "<strong>🔧 Action Required:</strong> Register the 'audience' taxonomy.<br>";
        echo "Add this to your theme's functions.php or plugin:<br>";
        echo "<code>register_taxonomy('audience', 'guests', []);</code><br><br>";
    }
    
    if (empty($authority_hook_data['who'])) {
        echo "<strong>🔧 Action Required:</strong> Fix AJAX data extraction.<br>";
        echo "The WHO field data is not being sent correctly in the AJAX request.<br><br>";
    }
    
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Check WordPress debug.log for AJAX save errors<br>";
    echo "2. Ensure 'audience' taxonomy is registered<br>";
    echo "3. Test with real AJAX request from Topics Generator<br>";
    echo "4. Verify JavaScript is sending correct WHO field data";
    echo "</div>";
    
    // Restore original POST data
    $_POST = $original_post;
}

// Test the audience taxonomy registration
function check_audience_taxonomy() {
    echo "<h2>🏷️ Audience Taxonomy Status</h2>";
    
    if (taxonomy_exists('audience')) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
        echo "<strong>✅ TAXONOMY EXISTS:</strong> 'audience' taxonomy is registered.<br>";
        
        $taxonomy = get_taxonomy('audience');
        echo "<strong>Object types:</strong> " . implode(', ', $taxonomy->object_type) . "<br>";
        echo "<strong>Public:</strong> " . ($taxonomy->public ? 'Yes' : 'No') . "<br>";
        echo "<strong>Hierarchical:</strong> " . ($taxonomy->hierarchical ? 'Yes' : 'No');
        echo "</div>";
        
        // Get all audience terms
        $terms = get_terms(['taxonomy' => 'audience', 'hide_empty' => false]);
        if (!empty($terms) && !is_wp_error($terms)) {
            echo "<h3>Existing audience terms:</h3>";
            foreach ($terms as $term) {
                echo "- " . $term->name . " (ID: " . $term->term_id . ", Count: " . $term->count . ")<br>";
            }
        } else {
            echo "<p><em>No audience terms exist yet.</em></p>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 4px;'>";
        echo "<strong>❌ TAXONOMY MISSING:</strong> 'audience' taxonomy is not registered!<br>";
        echo "This is likely the root cause of the saving issue.";
        echo "</div>";
        
        echo "<h3>🔧 Fix: Register the taxonomy</h3>";
        echo "<p>Add this code to register the audience taxonomy:</p>";
        echo "<pre>";
        echo "add_action('init', function() {
    register_taxonomy('audience', 'guests', [
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
});";
        echo "</pre>";
    }
}

// Run the tests
echo "<div style='max-width: 1200px; margin: 20px; font-family: Arial, sans-serif;'>";

check_audience_taxonomy();
debug_save_topics_ajax();

echo "</div>";
?>