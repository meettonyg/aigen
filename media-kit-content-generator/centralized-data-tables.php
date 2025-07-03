<?php
/**
 * Centralized Services Data View for Entry 74492
 * Two tables: Formidable Entry Data + Custom Post Data
 * All data retrieved through centralized services
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Your specific entry
$entry_id = 74492;
$entry_key = 'y8ver';

// Load plugin dependencies and initialize centralized services
require_once plugin_dir_path(__FILE__) . 'includes/services/class-mkcg-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/services/enhanced_formidable_service.php';

$formidable_service = new Enhanced_Formidable_Service();

// Get all data through centralized services
$centralized_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
$field_mappings = MKCG_Config::get_field_mappings();

// Get complete Formidable entry data
$formidable_entry_data = $formidable_service->get_entry_data($entry_id);
$formidable_fields = $formidable_entry_data['success'] ? $formidable_entry_data['field_data'] : [];

// Get post ID through centralized service
$post_id = $formidable_service->get_post_id_from_entry($entry_id);

// Get entry details with error checking
global $wpdb;
// Try multiple approaches to find the entry
$entry = $wpdb->get_row($wpdb->prepare(
    "SELECT id, item_key, post_id, created_date FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $entry_id
));

// If first query failed, try the approach that worked in debug
if (!$entry) {
    $entry = $wpdb->get_row(
        "SELECT id, item_key, post_id, created_date FROM {$wpdb->prefix}frm_items WHERE id = 74492 OR item_key = 'y8ver' LIMIT 1"
    );
}

// Debug: Check if table exists and query is working
if (!$entry) {
    // Check if frm_items table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}frm_items'");
    
    // Try to find the entry with different approaches
    $entry_check = $wpdb->get_results(
        "SELECT id, item_key, post_id FROM {$wpdb->prefix}frm_items WHERE id = 74492 OR item_key = 'y8ver' LIMIT 5"
    );
    
    // Get recent entries to see what's available
    $recent_entries = $wpdb->get_results(
        "SELECT id, item_key, post_id FROM {$wpdb->prefix}frm_items ORDER BY id DESC LIMIT 10"
    );
} else {
    // Entry found - update entry_id to ensure centralized services use the correct ID
    $entry_id = intval($entry->id);
    
    // Re-run centralized services with confirmed entry ID
    $centralized_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
    $formidable_entry_data = $formidable_service->get_entry_data($entry_id);
    $formidable_fields = $formidable_entry_data['success'] ? $formidable_entry_data['field_data'] : [];
    $post_id = $formidable_service->get_post_id_from_entry($entry_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralized Services Data Tables - Entry 74492</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f1f1f1;
            color: #23282d;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #0073aa;
            margin: 0 0 10px 0;
        }
        
        .entry-info {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .table-section {
            margin-bottom: 40px;
        }
        
        .table-section h2 {
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .data-table th {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #495057;
        }
        
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .field-name {
            font-weight: 600;
            color: #0073aa;
            min-width: 150px;
        }
        
        .field-id {
            font-family: monospace;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            color: #6c757d;
            min-width: 80px;
            text-align: center;
        }
        
        .field-value {
            max-width: 600px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        .has-data {
            background: #d4edda !important;
        }
        
        .no-data {
            color: #6c757d;
            font-style: italic;
        }
        
        .long-value {
            max-height: 100px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            padding: 8px;
            border-radius: 4px;
            background: #fafafa;
        }
        
        .refresh-btn {
            background: #0073aa;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
        }
        
        .refresh-btn:hover {
            background: #005a87;
        }
        
        .summary-stats {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Centralized Services Data Tables</h1>
            <p>Entry 74492 (y8ver) - All data retrieved through centralized services</p>
        </div>

        <div class="entry-info">
            <?php if ($entry): ?>
                <strong>✅ Entry Found:</strong> ID <?php echo $entry->id; ?> | Key: <?php echo $entry->item_key; ?> | Post ID: <?php echo $entry->post_id ?: 'None'; ?><br>
                <strong>Created:</strong> <?php echo $entry->created_date; ?> | 
                <strong>Centralized Data Status:</strong> Has Entry: <?php echo $centralized_data['has_entry'] ? 'YES' : 'NO'; ?>
            <?php else: ?>
                <strong style="color: red;">❌ Entry 74492 not found in database</strong><br>
                
                <!-- Debug Information -->
                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; text-align: left;">
                    <strong>🔍 Debug Information:</strong><br>
                    <strong>Table Exists:</strong> <?php echo isset($table_exists) && $table_exists ? 'YES' : 'NO'; ?><br>
                    
                    <?php if (isset($entry_check) && !empty($entry_check)): ?>
                        <strong>Found entries with ID 74492 or key 'y8ver':</strong><br>
                        <?php foreach ($entry_check as $check): ?>
                            - ID: <?php echo $check->id; ?>, Key: <?php echo $check->item_key; ?>, Post: <?php echo $check->post_id; ?><br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <strong>No entries found with ID 74492 or key 'y8ver'</strong><br>
                    <?php endif; ?>
                    
                    <?php if (isset($recent_entries) && !empty($recent_entries)): ?>
                        <strong>Recent entries in database:</strong><br>
                        <?php foreach ($recent_entries as $recent): ?>
                            - ID: <?php echo $recent->id; ?>, Key: <?php echo $recent->item_key; ?>, Post: <?php echo $recent->post_id; ?><br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <strong>No recent entries found</strong><br>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <button onclick="location.reload()" class="refresh-btn">🔄 Refresh Data</button>
            <a href="test-tools.php" class="refresh-btn">🏠 Back to Tools</a>
            <a href="view-entry-data.php" class="refresh-btn">📊 Comparison View</a>
        </div>

        <?php if ($entry): ?>

            <!-- TABLE 1: FORMIDABLE ENTRY DATA -->
            <div class="table-section">
                <h2>📋 Table 1: Formidable Entry Data (Retrieved via Enhanced_Formidable_Service)</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Field ID</th>
                            <th>Field Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Define all expected Formidable fields based on your form structure
                        $formidable_field_definitions = [
                            // Authority Hook Components
                            'WHEN do they need you?' => '10387',
                            'WHAT result do you help them achieve?' => '10297', 
                            'HOW do you help them achieve this result?' => '10298',
                            'WHERE have you demonstrated results?' => '10359',
                            'WHY are you passionate about what you do?' => '10360',
                            'Authority Hook Complete' => '10358',
                            
                            // Topics (Form 515)
                            'Topic 1' => '8498',
                            'Topic 2' => '8499',
                            'Topic 3' => '8500',
                            'Topic 4' => '8501',
                            'Topic 5' => '8502',
                            
                            // Questions (Form 515) - First 10
                            'Question 1' => '8505',
                            'Question 2' => '8506', 
                            'Question 3' => '8507',
                            'Question 4' => '8508',
                            'Question 5' => '8509',
                            'Question 6' => '8510',
                            'Question 7' => '8511',
                            'Question 8' => '8512',
                            'Question 9' => '8513',
                            'Question 10' => '8514',
                            
                            // Questions 11-25 (Extended range)
                            'Question 11' => '10370',
                            'Question 12' => '10371',
                            'Question 13' => '10372',
                            'Question 14' => '10373',
                            'Question 15' => '10374',
                            'Question 16' => '10375',
                            'Question 17' => '10376',
                            'Question 18' => '10377',
                            'Question 19' => '10378',
                            'Question 20' => '10379',
                            'Question 21' => '10380',
                            'Question 22' => '10381',
                            'Question 23' => '10382',
                            'Question 24' => '10383',
                            'Question 25' => '10384'
                        ];
                        
                        $formidable_data_count = 0;
                        
                        foreach ($formidable_field_definitions as $field_name => $field_id):
                            // Get value using centralized service
                            $field_value = $formidable_service->get_field_value($entry_id, $field_id);
                            
                            // Also check raw formidable data as backup
                            if (empty($field_value) && isset($formidable_fields[$field_id])) {
                                $field_value = $formidable_fields[$field_id];
                            }
                            
                            $has_data = !empty($field_value);
                            if ($has_data) $formidable_data_count++;
                            
                            $display_value = $has_data ? $field_value : '<span class="no-data">No data</span>';
                            
                            // Handle long values
                            if ($has_data && strlen($field_value) > 150) {
                                $display_value = '<div class="long-value">' . esc_html($field_value) . '</div>';
                            } elseif ($has_data) {
                                $display_value = esc_html($field_value);
                            }
                            ?>
                            <tr <?php echo $has_data ? 'class="has-data"' : ''; ?>>
                                <td class="field-name"><?php echo $field_name; ?></td>
                                <td class="field-id"><?php echo $field_id; ?></td>
                                <td class="field-value"><?php echo $display_value; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary-stats">
                    <strong>Formidable Data Summary:</strong> <?php echo $formidable_data_count; ?> out of <?php echo count($formidable_field_definitions); ?> fields have data
                </div>
            </div>

            <!-- TABLE 2: CUSTOM POST DATA -->
            <div class="table-section">
                <h2>📝 Table 2: Custom Post Data (Retrieved via Centralized Services)</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Meta Key</th>
                            <th>Field Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Define custom post fields based on centralized config
                        $custom_post_fields = [
                            // Authority Hook Component
                            'WHO do you help?' => 'mkcg_who',
                            
                            // Topics (from centralized config)
                            'Topic 1' => 'mkcg_topic_1',
                            'Topic 2' => 'mkcg_topic_2', 
                            'Topic 3' => 'mkcg_topic_3',
                            'Topic 4' => 'mkcg_topic_4',
                            'Topic 5' => 'mkcg_topic_5',
                            
                            // Questions (25 questions across 5 topics)
                            'Question 1.1' => 'mkcg_question_1_1',
                            'Question 1.2' => 'mkcg_question_1_2',
                            'Question 1.3' => 'mkcg_question_1_3', 
                            'Question 1.4' => 'mkcg_question_1_4',
                            'Question 1.5' => 'mkcg_question_1_5',
                            'Question 2.1' => 'mkcg_question_2_1',
                            'Question 2.2' => 'mkcg_question_2_2',
                            'Question 2.3' => 'mkcg_question_2_3',
                            'Question 2.4' => 'mkcg_question_2_4',
                            'Question 2.5' => 'mkcg_question_2_5',
                            'Question 3.1' => 'mkcg_question_3_1',
                            'Question 3.2' => 'mkcg_question_3_2',
                            'Question 3.3' => 'mkcg_question_3_3',
                            'Question 3.4' => 'mkcg_question_3_4',
                            'Question 3.5' => 'mkcg_question_3_5',
                            'Question 4.1' => 'mkcg_question_4_1',
                            'Question 4.2' => 'mkcg_question_4_2',
                            'Question 4.3' => 'mkcg_question_4_3',
                            'Question 4.4' => 'mkcg_question_4_4',
                            'Question 4.5' => 'mkcg_question_4_5',
                            'Question 5.1' => 'mkcg_question_5_1',
                            'Question 5.2' => 'mkcg_question_5_2',
                            'Question 5.3' => 'mkcg_question_5_3',
                            'Question 5.4' => 'mkcg_question_5_4',
                            'Question 5.5' => 'mkcg_question_5_5'
                        ];
                        
                        $custom_post_data_count = 0;
                        
                        foreach ($custom_post_fields as $field_name => $meta_key):
                            // Get value using WordPress post meta (via centralized services when available)
                            $field_value = '';
                            
                            if ($post_id) {
                                // For WHO field, check centralized authority hook data first
                                if ($meta_key === 'mkcg_who') {
                                    $field_value = $centralized_data['authority_hook_components']['who'] ?? '';
                                    // Fallback to direct meta call if empty
                                    if (empty($field_value) || $field_value === 'your audience') {
                                        $field_value = get_post_meta($post_id, $meta_key, true);
                                    }
                                }
                                // For topics, check centralized form field values
                                elseif (strpos($meta_key, 'mkcg_topic_') === 0) {
                                    $topic_num = str_replace('mkcg_topic_', '', $meta_key);
                                    $topic_key = 'topic_' . $topic_num;
                                    $field_value = $centralized_data['form_field_values'][$topic_key] ?? '';
                                    // Fallback to direct meta call
                                    if (empty($field_value)) {
                                        $field_value = get_post_meta($post_id, $meta_key, true);
                                    }
                                }
                                // For questions, check centralized questions data
                                elseif (strpos($meta_key, 'mkcg_question_') === 0) {
                                    preg_match('/mkcg_question_(\\d+)_(\\d+)/', $meta_key, $matches);
                                    if ($matches) {
                                        $topic_num = $matches[1];
                                        $question_num = $matches[2];
                                        $field_value = $centralized_data['questions'][$topic_num][$question_num] ?? '';
                                        // Fallback to direct meta call
                                        if (empty($field_value)) {
                                            $field_value = get_post_meta($post_id, $meta_key, true);
                                        }
                                    }
                                }
                                // Default: direct meta call
                                else {
                                    $field_value = get_post_meta($post_id, $meta_key, true);
                                }
                            }
                            
                            $has_data = !empty($field_value);
                            if ($has_data) $custom_post_data_count++;
                            
                            $display_value = $has_data ? $field_value : '<span class="no-data">No data</span>';
                            
                            // Handle long values
                            if ($has_data && strlen($field_value) > 150) {
                                $display_value = '<div class="long-value">' . esc_html($field_value) . '</div>';
                            } elseif ($has_data) {
                                $display_value = esc_html($field_value);
                            }
                            ?>
                            <tr <?php echo $has_data ? 'class="has-data"' : ''; ?>>
                                <td class="field-name"><?php echo $field_name; ?></td>
                                <td class="field-id"><?php echo $meta_key; ?></td>
                                <td class="field-value"><?php echo $display_value; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary-stats">
                    <strong>Custom Post Data Summary:</strong> <?php echo $custom_post_data_count; ?> out of <?php echo count($custom_post_fields); ?> fields have data
                </div>
            </div>

            <!-- CENTRALIZED SERVICES STATUS -->
            <div class="table-section">
                <h2>⚙️ Centralized Services Status</h2>
                <div class="summary-stats">
                    <p><strong>MKCG_Config::load_data_for_entry() Results:</strong></p>
                    <ul>
                        <li><strong>Has Entry:</strong> <?php echo $centralized_data['has_entry'] ? 'YES' : 'NO'; ?></li>
                        <li><strong>Topics Loaded:</strong> <?php echo count(array_filter($centralized_data['form_field_values'])); ?>/5</li>
                        <li><strong>Authority Components:</strong> <?php echo count(array_filter($centralized_data['authority_hook_components'], function($v, $k) { return !empty($v) && $k !== 'complete'; }, ARRAY_FILTER_USE_BOTH)); ?></li>
                        <li><strong>Question Groups:</strong> <?php echo count($centralized_data['questions']); ?></li>
                        <li><strong>Total Questions:</strong> <?php 
                            $total_questions = 0;
                            foreach ($centralized_data['questions'] as $topic_questions) {
                                $total_questions += count(array_filter($topic_questions));
                            }
                            echo $total_questions;
                        ?></li>
                    </ul>
                    
                    <?php if (!empty($centralized_data['authority_hook_components']['complete'])): ?>
                        <p><strong>Complete Authority Hook:</strong><br>
                        <em>"<?php echo esc_html($centralized_data['authority_hook_components']['complete']); ?>"</em></p>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px;">
                <p style="color: #d63638; font-size: 1.2em;">Entry 74492 not found. Please check that the entry exists in your Formidable database.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
