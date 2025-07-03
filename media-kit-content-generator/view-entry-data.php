<?php
/**
 * Simple Data Comparison View for Entry 74492 (y8ver)
 * Shows Formidable values vs Custom Post values in a clear table
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Load plugin dependencies
require_once plugin_dir_path(__FILE__) . 'includes/services/class-mkcg-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/services/enhanced_formidable_service.php';

// Your specific entry
$entry_id = 74492;
$entry_key = 'y8ver';

// Initialize services
$formidable_service = new Enhanced_Formidable_Service();

// Get data using centralized services
$centralized_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
$field_mappings = MKCG_Config::get_field_mappings();

// Get complete entry data from formidable
$formidable_entry_data = $formidable_service->get_entry_data($entry_id);
$formidable_fields = $formidable_entry_data['success'] ? $formidable_entry_data['field_data'] : [];

// Get entry and post ID
global $wpdb;
$entry = $wpdb->get_row($wpdb->prepare(
    "SELECT id, item_key, post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
    $entry_id
));

$post_id = $entry ? $entry->post_id : null;

// Build comprehensive field list from centralized config
$display_fields = [];

// Authority Hook Components (hybrid storage)
foreach ($field_mappings['authority_hook'] as $component => $config) {
    $field_name = strtoupper($component);
    if ($config['source'] === 'formidable') {
        $display_fields[$field_name] = [
            'formidable_field' => $config['field_id'],
            'custom_post_meta' => null,
            'section' => 'Authority Hook'
        ];
    } elseif ($config['source'] === 'post_meta') {
        $display_fields[$field_name] = [
            'formidable_field' => null, 
            'custom_post_meta' => $config['key'],
            'section' => 'Authority Hook'
        ];
    }
}

// Topics (check both sources - they might be in both)
foreach ($field_mappings['topics']['fields'] as $topic_key => $meta_key) {
    $topic_num = str_replace('topic_', '', $topic_key);
    $field_name = "Topic {$topic_num}";
    
    // Check if there's also a Formidable field for this topic
    $formidable_field = null;
    // You might have Formidable fields for topics - check common field IDs
    $possible_topic_fields = [
        '1' => '8498', '2' => '8499', '3' => '8500', '4' => '8501', '5' => '8502'
    ];
    if (isset($possible_topic_fields[$topic_num])) {
        $formidable_field = $possible_topic_fields[$topic_num];
    }
    
    $display_fields[$field_name] = [
        'formidable_field' => $formidable_field,
        'custom_post_meta' => $meta_key,
        'section' => 'Topics'
    ];
}

// Questions (first 10 as sample - check both sources)
for ($topic = 1; $topic <= 2; $topic++) {
    for ($q = 1; $q <= 5; $q++) {
        $field_name = "Question {$topic}.{$q}";
        $meta_key = "mkcg_question_{$topic}_{$q}";
        
        // Questions might also have Formidable fields - check if they exist
        $formidable_field = null;
        // Add logic here if you have Formidable question fields
        
        $display_fields[$field_name] = [
            'formidable_field' => $formidable_field,
            'custom_post_meta' => $meta_key,
            'section' => 'Questions'
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry 74492 Data Comparison</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f1f1f1;
            color: #23282d;
        }
        
        .container {
            max-width: 1400px;
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
            margin-bottom: 20px;
            text-align: center;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            max-width: 400px;
            word-wrap: break-word;
        }
        
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .field-name {
            font-weight: 600;
            color: #0073aa;
            white-space: nowrap;
            min-width: 120px;
        }
        
        .has-data {
            background: #d4edda !important;
        }
        
        .no-data {
            color: #6c757d;
            font-style: italic;
        }
        
        .formidable-value {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .custom-post-value {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .section-header {
            background: #0073aa !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
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
            margin: 10px 5px;
        }
        
        .refresh-btn:hover {
            background: #005a87;
        }
        
        .meta-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .field-id {
            font-family: monospace;
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Entry 74492 (y8ver) Data Comparison</h1>
            <p>Formidable Fields vs Custom Post Meta Values</p>
        </div>

        <div class="entry-info">
            <?php if ($entry): ?>
                <strong>✅ Entry Found:</strong> ID <?php echo $entry->id; ?> | Key: <?php echo $entry->item_key; ?> | Post ID: <?php echo $entry->post_id ?: 'None'; ?><br>
                <strong>Centralized Data Status:</strong> 
                <?php 
                echo "Has Entry: " . ($centralized_data['has_entry'] ? 'YES' : 'NO') . " | ";
                echo "Topics: " . count(array_filter($centralized_data['form_field_values'])) . "/5 | ";
                echo "Authority Components: " . count(array_filter($centralized_data['authority_hook_components'])) . " | ";
                echo "Question Groups: " . count($centralized_data['questions']);
                ?>
            <?php else: ?>
                <strong style="color: red;">❌ Entry 74492 not found in database</strong>
            <?php endif; ?>
        </div>

        <?php if ($entry): ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <button onclick="location.reload()" class="refresh-btn">🔄 Refresh Data</button>
                <a href="test-entry-74492.php" class="refresh-btn">⚙️ Setup/Manage Data</a>
                <a href="test-tools.php" class="refresh-btn">🏠 Back to Tools</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Field Name</th>
                        <th style="width: 50%;">Formidable Value</th>
                        <th style="width: 50%;">Custom Post Value</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Authority Hook Components Section -->
                    <tr>
                        <td colspan="3" class="section-header">Authority Hook Components</td>
                    </tr>
                    
                    <?php
                    // Show Authority Hook fields
                    $authority_fields = array_filter($display_fields, function($field) {
                        return $field['section'] === 'Authority Hook';
                    });
                    
                    foreach ($authority_fields as $field_name => $mapping):
                        // Get Formidable value using centralized service
                        $formidable_value = '';
                        $formidable_display = '<span class="no-data">Not stored in Formidable</span>';
                        if ($mapping['formidable_field']) {
                            // First check centralized data
                            $component_key = strtolower($field_name);
                            if (isset($centralized_data['authority_hook_components'][$component_key])) {
                                $formidable_value = $centralized_data['authority_hook_components'][$component_key];
                            }
                            // Fallback to direct service call
                            if (empty($formidable_value)) {
                                $formidable_value = $formidable_service->get_field_value($entry_id, $mapping['formidable_field']);
                            }
                            
                            if (!empty($formidable_value)) {
                                $formidable_display = '<div class="formidable-value">' . esc_html($formidable_value) . '</div>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            } else {
                                $formidable_display = '<span class="no-data">No data</span>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            }
                        }
                        
                        // Get Custom Post value using centralized service
                        $custom_post_value = '';
                        $custom_post_display = '<span class="no-data">Not stored in Custom Post</span>';
                        if ($mapping['custom_post_meta'] && $post_id) {
                            // First check centralized data
                            $component_key = strtolower($field_name);
                            if (isset($centralized_data['authority_hook_components'][$component_key])) {
                                $custom_post_value = $centralized_data['authority_hook_components'][$component_key];
                            }
                            // Fallback to direct meta call
                            if (empty($custom_post_value)) {
                                $custom_post_value = get_post_meta($post_id, $mapping['custom_post_meta'], true);
                            }
                            
                            if (!empty($custom_post_value)) {
                                $custom_post_display = '<div class="custom-post-value">' . esc_html($custom_post_value) . '</div>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            } else {
                                $custom_post_display = '<span class="no-data">No data</span>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            }
                        }
                        
                        $has_data = !empty($formidable_value) || !empty($custom_post_value);
                        ?>
                        <tr <?php echo $has_data ? 'class="has-data"' : ''; ?>>
                            <td class="field-name"><?php echo $field_name; ?></td>
                            <td><?php echo $formidable_display; ?></td>
                            <td><?php echo $custom_post_display; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Topics Section -->
                    <tr>
                        <td colspan="3" class="section-header">Topics (Both Sources)</td>
                    </tr>
                    
                    <?php
                    // Show Topics using centralized service
                    $topic_fields = array_filter($display_fields, function($field) {
                        return $field['section'] === 'Topics';
                    });
                    
                    foreach ($topic_fields as $field_name => $mapping):
                        // Get Formidable value (check if topics are also stored in Formidable)
                        $formidable_value = '';
                        $formidable_display = '<span class="no-data">Not stored in Formidable</span>';
                        if ($mapping['formidable_field']) {
                            // Check centralized data first
                            $topic_key = 'topic_' . str_replace('Topic ', '', $field_name);
                            if (isset($centralized_data['form_field_values'][$topic_key])) {
                                $formidable_value = $centralized_data['form_field_values'][$topic_key];
                            }
                            // Also check raw Formidable data
                            if (empty($formidable_value) && isset($formidable_fields[$mapping['formidable_field']])) {
                                $formidable_value = $formidable_fields[$mapping['formidable_field']];
                            }
                            
                            if (!empty($formidable_value)) {
                                $formidable_display = '<div class="formidable-value">' . esc_html($formidable_value) . '</div>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            } else {
                                $formidable_display = '<span class="no-data">No data</span>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            }
                        }
                        
                        // Get Custom Post value using centralized service
                        $custom_post_value = '';
                        $custom_post_display = '<span class="no-data">No data</span>';
                        if ($post_id && $mapping['custom_post_meta']) {
                            // Check centralized data first
                            $topic_key = 'topic_' . str_replace('Topic ', '', $field_name);
                            if (isset($centralized_data['form_field_values'][$topic_key])) {
                                $custom_post_value = $centralized_data['form_field_values'][$topic_key];
                            }
                            // Fallback to direct meta call
                            if (empty($custom_post_value)) {
                                $custom_post_value = get_post_meta($post_id, $mapping['custom_post_meta'], true);
                            }
                            
                            if (!empty($custom_post_value)) {
                                $custom_post_display = '<div class="custom-post-value">' . esc_html($custom_post_value) . '</div>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            } else {
                                $custom_post_display = '<span class="no-data">No data</span>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            }
                        }
                        
                        $has_data = !empty($formidable_value) || !empty($custom_post_value);
                        ?>
                        <tr <?php echo $has_data ? 'class="has-data"' : ''; ?>>
                            <td class="field-name"><?php echo $field_name; ?></td>
                            <td><?php echo $formidable_display; ?></td>
                            <td><?php echo $custom_post_display; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Questions Section (Sample - First 10 questions) -->
                    <tr>
                        <td colspan="3" class="section-header">Questions (Both Sources) - Sample</td>
                    </tr>
                    
                    <?php
                    // Show Questions using centralized service
                    $question_fields = array_filter($display_fields, function($field) {
                        return $field['section'] === 'Questions';
                    });
                    
                    foreach ($question_fields as $field_name => $mapping):
                        // Get Formidable value (check if questions are also stored in Formidable)
                        $formidable_value = '';
                        $formidable_display = '<span class="no-data">Not stored in Formidable</span>';
                        if ($mapping['formidable_field']) {
                            // Check raw Formidable data for question fields
                            if (isset($formidable_fields[$mapping['formidable_field']])) {
                                $formidable_value = $formidable_fields[$mapping['formidable_field']];
                            }
                            
                            if (!empty($formidable_value)) {
                                $formidable_display = '<div class="formidable-value">' . esc_html($formidable_value) . '</div>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            } else {
                                $formidable_display = '<span class="no-data">No data</span>';
                                $formidable_display .= '<div class="meta-info">Field ID: <span class="field-id">' . $mapping['formidable_field'] . '</span></div>';
                            }
                        }
                        
                        // Get Custom Post value using centralized service
                        $custom_post_value = '';
                        $custom_post_display = '<span class="no-data">No data</span>';
                        if ($post_id && $mapping['custom_post_meta']) {
                            // Check centralized questions data
                            preg_match('/Question (\d+)\.(\d+)/', $field_name, $matches);
                            if ($matches && isset($centralized_data['questions'][$matches[1]][$matches[2]])) {
                                $custom_post_value = $centralized_data['questions'][$matches[1]][$matches[2]];
                            }
                            // Fallback to direct meta call
                            if (empty($custom_post_value)) {
                                $custom_post_value = get_post_meta($post_id, $mapping['custom_post_meta'], true);
                            }
                            
                            if (!empty($custom_post_value)) {
                                $custom_post_display = '<div class="custom-post-value">' . esc_html($custom_post_value) . '</div>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            } else {
                                $custom_post_display = '<span class="no-data">No data</span>';
                                $custom_post_display .= '<div class="meta-info">Meta Key: <span class="field-id">' . $mapping['custom_post_meta'] . '</span></div>';
                            }
                        }
                        
                        $has_data = !empty($formidable_value) || !empty($custom_post_value);
                        ?>
                        <tr <?php echo $has_data ? 'class="has-data"' : ''; ?>>
                            <td class="field-name"><?php echo $field_name; ?></td>
                            <td><?php echo $formidable_display; ?></td>
                            <td><?php echo $custom_post_display; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Summary Row -->
                    <tr>
                        <td colspan="3" class="section-header">
                            Summary: 
                            <?php
                            $total_formidable = 0;
                            $total_custom_post = 0;
                            
                            // Count from centralized data
                            $total_formidable = count(array_filter($formidable_fields));
                            $total_custom_post = 0;
                            
                            // Count authority hook components
                            $total_custom_post += count(array_filter($centralized_data['authority_hook_components'], function($value, $key) {
                                return !empty($value) && $key !== 'complete';
                            }, ARRAY_FILTER_USE_BOTH));
                            
                            // Count topics
                            $total_custom_post += count(array_filter($centralized_data['form_field_values']));
                            
                            // Count questions
                            foreach ($centralized_data['questions'] as $topic_questions) {
                                $total_custom_post += count(array_filter($topic_questions));
                            }
                            
                            echo "{$total_formidable} Formidable fields with data | {$total_custom_post} Custom Post fields with data";
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

        <?php else: ?>
            <div style="text-align: center; padding: 40px;">
                <p style="color: #d63638; font-size: 1.2em;">Entry 74492 not found. Please check that the entry exists in your Formidable database.</p>
                <a href="test-tools.php" class="refresh-btn">🏠 Back to Tools</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
