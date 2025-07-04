<?php
/**
 * Test AJAX Handler Registration
 * Add this temporarily to check if handlers are registered
 */

// Check if our AJAX actions are registered
add_action('wp_footer', function() {
    if (current_user_can('administrator')) {
        echo '<script>';
        echo 'console.log("🔍 AJAX Handler Registration Test:");';
        
        // Check if our actions are in the WordPress action list
        global $wp_filter;
        
        $actions_to_check = [
            'wp_ajax_mkcg_save_topics_data',
            'wp_ajax_mkcg_get_topics_data', 
            'wp_ajax_mkcg_save_authority_hook',
            'wp_ajax_mkcg_generate_topics',
            'wp_ajax_mkcg_save_topic_field'
        ];
        
        foreach ($actions_to_check as $action) {
            $registered = isset($wp_filter[$action]) && !empty($wp_filter[$action]);
            $status = $registered ? '✅' : '❌';
            echo "console.log('$status $action: " . ($registered ? 'REGISTERED' : 'NOT REGISTERED') . "');";
            
            if ($registered) {
                $callbacks = array_keys($wp_filter[$action]->callbacks);
                echo "console.log('   Priority levels: " . implode(', ', $callbacks) . "');";
            }
        }
        
        echo '</script>';
    }
});

// Also check if our classes exist
add_action('wp_footer', function() {
    if (current_user_can('administrator')) {
        echo '<script>';
        echo 'console.log("🔍 Class Existence Test:");';
        
        $classes_to_check = [
            'Enhanced_AJAX_Handlers',
            'MKCG_Pods_Service', 
            'MKCG_Authority_Hook_Service',
            'Enhanced_Topics_Generator'
        ];
        
        foreach ($classes_to_check as $class) {
            $exists = class_exists($class);
            $status = $exists ? '✅' : '❌';
            echo "console.log('$status $class: " . ($exists ? 'EXISTS' : 'NOT FOUND') . "');";
        }
        
        echo '</script>';
    }
});
?>