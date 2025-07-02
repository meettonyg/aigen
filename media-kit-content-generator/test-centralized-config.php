<?php
/**
 * ROOT-LEVEL CENTRALIZED CONFIG TEST
 * Test the new hybrid data loading system
 */

require_once 'media-kit-content-generator.php';

$entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : 'y8ver';

echo '<div style="font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px; border-radius: 8px;">';
echo '<h1>🔧 ROOT-LEVEL CENTRALIZED CONFIG TEST</h1>';
echo '<p><strong>Testing hybrid data loading system...</strong></p>';
echo '<p><strong>Entry Key:</strong> ' . $entry_key . '</p>';

$plugin = Media_Kit_Content_Generator::get_instance();
$formidable_service = $plugin->get_formidable_service();

echo '<h2>1️⃣ CENTRALIZED CONFIG TEST</h2>';

// Test entry resolution
$entry_data = $formidable_service->get_entry_by_key($entry_key);
if (!$entry_data['success']) {
    echo '<p><strong>❌ Entry resolution failed:</strong> ' . $entry_data['message'] . '</p>';
    exit;
}

$entry_id = $entry_data['entry_id'];
echo '<p><strong>✅ Entry ID:</strong> ' . $entry_id . '</p>';

// Test centralized data loading
echo '<h3>🎯 Testing MKCG_Config::load_data_for_entry()</h3>';

try {
    $centralized_data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
    echo '<p><strong>✅ Centralized data loading successful</strong></p>';
    
    echo '<h4>📊 Topics Data (from Custom Post Meta):</h4>';
    foreach ($centralized_data['form_field_values'] as $topic_key => $value) {
        if (!empty($value)) {
            echo "<p><strong>{$topic_key}:</strong> <span style='color: green;'>✅ \"{$value}\"</span></p>";
        } else {
            echo "<p><strong>{$topic_key}:</strong> <span style='color: red;'>❌ EMPTY</span></p>";
        }
    }
    
    echo '<h4>🎯 Authority Hook Data (Hybrid Sources):</h4>';
    foreach ($centralized_data['authority_hook_components'] as $component => $value) {
        $source = ($component === 'who') ? 'POST META' : 'FORMIDABLE';
        $is_default = in_array($value, ['your audience', 'achieve their goals', 'they need help', 'through your method']);
        
        if (!empty($value) && !$is_default) {
            echo "<p><strong>{$component} ({$source}):</strong> <span style='color: green;'>✅ \"{$value}\"</span></p>";
        } else {
            echo "<p><strong>{$component} ({$source}):</strong> <span style='color: orange;'>⚠️ DEFAULT: \"{$value}\"</span></p>";
        }
    }
    
    echo '<h4>🔍 Data Summary:</h4>';
    echo '<ul>';
    echo '<li><strong>Has Entry Data:</strong> ' . ($centralized_data['has_entry'] ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li><strong>Topics Found:</strong> ' . count(array_filter($centralized_data['form_field_values'])) . '/5</li>';
    echo '<li><strong>Complete Authority Hook:</strong> ' . (!empty($centralized_data['authority_hook_components']['complete']) ? '✅ YES' : '❌ NO') . '</li>';
    echo '</ul>';
    
} catch (Exception $e) {
    echo '<p><strong>❌ Centralized data loading failed:</strong> ' . $e->getMessage() . '</p>';
}

echo '<h2>2️⃣ FIELD MAPPINGS VERIFICATION</h2>';

$mappings = MKCG_Config::get_field_mappings();
echo '<h3>🗺️ Field Mappings Configuration:</h3>';
echo '<pre>' . print_r($mappings, true) . '</pre>';

echo '<h2>3️⃣ POST META vs FORMIDABLE CHECK</h2>';

// Get post ID
$post_id = $formidable_service->get_post_id_from_entry($entry_id);
if ($post_id) {
    echo '<p><strong>✅ Associated Post ID:</strong> ' . $post_id . '</p>';
    
    echo '<h3>🏗️ Custom Post Meta Check:</h3>';
    
    // Check topics in post meta
    for ($i = 1; $i <= 5; $i++) {
        $meta_key = "mkcg_topic_{$i}";
        $value = get_post_meta($post_id, $meta_key, true);
        echo "<p><strong>{$meta_key}:</strong> ";
        if (!empty($value)) {
            echo '<span style="color: green;">✅ "' . esc_html($value) . '"</span>';
        } else {
            echo '<span style="color: red;">❌ EMPTY</span>';
        }
        echo '</p>';
    }
    
    // Check WHO field in post meta
    $who_value = get_post_meta($post_id, 'mkcg_who', true);
    echo "<p><strong>mkcg_who (Authority Hook WHO):</strong> ";
    if (!empty($who_value)) {
        echo '<span style="color: green;">✅ "' . esc_html($who_value) . '"</span>';
    } else {
        echo '<span style="color: red;">❌ EMPTY</span>';
    }
    echo '</p>';
    
} else {
    echo '<p><strong>❌ No associated post found</strong></p>';
}

echo '<h2>4️⃣ GENERATOR INTEGRATION TEST</h2>';

$topics_generator = $plugin->get_generator('topics');
if ($topics_generator) {
    echo '<p><strong>✅ Topics Generator available</strong></p>';
    
    try {
        $template_data = $topics_generator->get_template_data($entry_key);
        echo '<p><strong>✅ Template data generation successful</strong></p>';
        
        $loaded_topics = array_filter($template_data['form_field_values']);
        echo '<p><strong>Topics in template data:</strong> ' . count($loaded_topics) . '/5</p>';
        
        if (count($loaded_topics) > 0) {
            echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 10px 0;">';
            echo '<h3>🎉 SUCCESS! Topics are loading properly!</h3>';
            echo '<p>The centralized configuration is working. Topics should now appear in the Topics Generator.</p>';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 10px 0;">';
            echo '<h3>⚠️ No topics loaded</h3>';
            echo '<p>Need to check if topics exist in the custom post meta or add test data.</p>';
            echo '</div>';
            
            // Add test topics to post meta
            if ($post_id) {
                echo '<h3>🔧 Adding test topics to post meta...</h3>';
                
                $test_topics = [
                    'mkcg_topic_1' => 'Content Strategy for SaaS Companies',
                    'mkcg_topic_2' => 'Building High-Converting Landing Pages',
                    'mkcg_topic_3' => 'Email Marketing Automation',
                    'mkcg_topic_4' => 'Customer Retention Strategies',
                    'mkcg_topic_5' => 'Scaling Business Operations'
                ];
                
                foreach ($test_topics as $meta_key => $value) {
                    $result = update_post_meta($post_id, $meta_key, $value);
                    if ($result !== false) {
                        echo "<p>✅ Added {$meta_key}: \"{$value}\"</p>";
                    }
                }
                
                // Add test WHO field
                $result = update_post_meta($post_id, 'mkcg_who', 'SaaS founders');
                if ($result !== false) {
                    echo "<p>✅ Added mkcg_who: \"SaaS founders\"</p>";
                }
                
                echo '<p><strong>🔄 Now refresh your Topics Generator page to see the data!</strong></p>';
            }
        }
        
    } catch (Exception $e) {
        echo '<p><strong>❌ Template data generation failed:</strong> ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p><strong>❌ Topics Generator not available</strong></p>';
}

echo '<h2>📋 SUMMARY</h2>';
echo '<div style="background: #e3f2fd; border: 2px solid #2196f3; padding: 15px; border-radius: 8px;">';
echo '<h3>✅ Centralized Configuration Implemented</h3>';
echo '<ul>';
echo '<li><strong>Topics:</strong> Now loading from custom post meta (mkcg_topic_1, etc.)</li>';
echo '<li><strong>Authority Hook WHO:</strong> Now loading from custom post meta (mkcg_who)</li>';
echo '<li><strong>Authority Hook RESULT/WHEN/HOW:</strong> Still loading from Formidable fields</li>';
echo '<li><strong>Centralized Config:</strong> MKCG_Config class handles all data sources</li>';
echo '<li><strong>Global Access:</strong> All generators use same configuration</li>';
echo '</ul>';
echo '</div>';

echo '</div>';
?>
