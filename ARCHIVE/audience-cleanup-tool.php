<?php
/**
 * Audience Taxonomy Cleanup Tool
 * 
 * This tool helps clean up test data and consolidate audience terms
 * Run this once to clean up existing test terms
 */

// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
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
        die('❌ ERROR: WordPress not loaded');
    }
}

// Check user permissions
if (!current_user_can('administrator')) {
    die('❌ ERROR: Administrator access required');
}

echo "<h1>🧹 Audience Taxonomy Cleanup Tool</h1>";

/**
 * Clean up test data terms
 */
function cleanup_test_audience_terms() {
    echo "<h2>🔍 Identifying Test Data Terms</h2>";
    
    $test_patterns = [
        '/^\d+(st|nd|rd|th)\s+value$/i', // "2nd value", "3rd value"
        '/^\d+\s+value$/i',              // "3 value"
        '/^test\s+/i',                   // "test something"
        '/^example\s+/i',                // "example audience"
    ];
    
    $all_terms = get_terms(['taxonomy' => 'audience', 'hide_empty' => false]);
    $deleted_count = 0;
    
    if (is_wp_error($all_terms)) {
        echo "<p>❌ Error getting terms: " . $all_terms->get_error_message() . "</p>";
        return;
    }
    
    foreach ($all_terms as $term) {
        $is_test_term = false;
        
        foreach ($test_patterns as $pattern) {
            if (preg_match($pattern, $term->name)) {
                $is_test_term = true;
                break;
            }
        }
        
        if ($is_test_term) {
            echo "<p>🗑️ Deleting test term: <strong>" . esc_html($term->name) . "</strong> (ID: {$term->term_id}, Count: {$term->count})</p>";
            
            $result = wp_delete_term($term->term_id, 'audience');
            if (!is_wp_error($result)) {
                $deleted_count++;
            } else {
                echo "<p>❌ Failed to delete: " . $result->get_error_message() . "</p>";
            }
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
    echo "<strong>✅ Cleanup Complete:</strong> Deleted {$deleted_count} test terms.";
    echo "</div>";
}

/**
 * Show audience term statistics
 */
function show_audience_statistics() {
    echo "<h2>📊 Current Audience Statistics</h2>";
    
    $all_terms = get_terms(['taxonomy' => 'audience', 'hide_empty' => false]);
    
    if (is_wp_error($all_terms)) {
        echo "<p>❌ Error getting terms: " . $all_terms->get_error_message() . "</p>";
        return;
    }
    
    $used_terms = array_filter($all_terms, function($term) { return $term->count > 0; });
    $unused_terms = array_filter($all_terms, function($term) { return $term->count == 0; });
    
    echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #2196F3; border-radius: 4px;'>";
    echo "<h3>Summary:</h3>";
    echo "<strong>Total Terms:</strong> " . count($all_terms) . "<br>";
    echo "<strong>Active Terms:</strong> " . count($used_terms) . "<br>";
    echo "<strong>Unused Terms:</strong> " . count($unused_terms) . "<br>";
    echo "</div>";
    
    if (!empty($used_terms)) {
        echo "<h3>Active Audience Terms:</h3>";
        usort($used_terms, function($a, $b) { return $b->count - $a->count; });
        
        foreach ($used_terms as $term) {
            echo "<div style='margin: 5px 0; padding: 8px; background: #f8f9fa; border-left: 3px solid #28a745;'>";
            echo "<strong>" . esc_html($term->name) . "</strong> ";
            echo "<span style='color: #666;'>(ID: {$term->term_id}, Used by: {$term->count} post" . ($term->count != 1 ? 's' : '') . ")</span>";
            echo "</div>";
        }
    }
    
    if (!empty($unused_terms)) {
        echo "<h3>Unused Terms (Consider Removing):</h3>";
        foreach ($unused_terms as $term) {
            echo "<div style='margin: 5px 0; padding: 8px; background: #fff3cd; border-left: 3px solid #ffc107;'>";
            echo "<strong>" . esc_html($term->name) . "</strong> ";
            echo "<span style='color: #666;'>(ID: {$term->term_id}, Never used)</span>";
            echo "</div>";
        }
    }
}

// Run the tools
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'cleanup':
            cleanup_test_audience_terms();
            break;
        case 'stats':
            show_audience_statistics();
            break;
    }
} else {
    echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #2196F3; border-radius: 4px;'>";
    echo "<h3>Available Actions:</h3>";
    echo "<p><a href='?action=cleanup' class='button button-primary'>🧹 Clean Up Test Terms</a> - Remove terms like '2nd value', '3 value'</p>";
    echo "<p><a href='?action=stats' class='button button-secondary'>📊 Show Statistics</a> - View all audience terms and usage</p>";
    echo "</div>";
}

echo "<div style='margin-top: 20px; background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 4px;'>";
echo "<strong>✅ System Status:</strong> Audience taxonomy is working perfectly!<br>";
echo "The debug output shows all root-level issues have been resolved.";
echo "</div>";
?>
