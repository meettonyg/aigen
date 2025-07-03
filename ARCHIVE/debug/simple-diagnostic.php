<?php
/**
 * SIMPLE ERROR CHECK FIRST
 * Check what's causing the blank page
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h1>🔍 ERROR DIAGNOSTIC</h1>';

echo '<p><strong>Step 1:</strong> Basic PHP working</p>';

try {
    require_once 'media-kit-content-generator.php';
    echo '<p>✅ Main plugin file loaded</p>';
} catch (Exception $e) {
    echo '<p>❌ Plugin file error: ' . $e->getMessage() . '</p>';
    exit;
}

echo '<p><strong>Step 2:</strong> Plugin instance</p>';

try {
    $plugin = Media_Kit_Content_Generator::get_instance();
    echo '<p>✅ Plugin instance created</p>';
} catch (Exception $e) {
    echo '<p>❌ Plugin instance error: ' . $e->getMessage() . '</p>';
    exit;
}

echo '<p><strong>Step 3:</strong> MKCG_Config class check</p>';

if (class_exists('MKCG_Config')) {
    echo '<p>✅ MKCG_Config class loaded</p>';
} else {
    echo '<p>❌ MKCG_Config class not found</p>';
    
    // Try to load manually
    $config_path = __DIR__ . '/includes/services/class-mkcg-config.php';
    echo '<p>Trying to load from: ' . $config_path . '</p>';
    
    if (file_exists($config_path)) {
        echo '<p>✅ Config file exists</p>';
        try {
            require_once $config_path;
            echo '<p>✅ Config file loaded manually</p>';
        } catch (Exception $e) {
            echo '<p>❌ Config file error: ' . $e->getMessage() . '</p>';
            exit;
        }
    } else {
        echo '<p>❌ Config file not found at: ' . $config_path . '</p>';
        exit;
    }
}

echo '<p><strong>Step 4:</strong> Service check</p>';

try {
    $formidable_service = $plugin->get_formidable_service();
    if ($formidable_service) {
        echo '<p>✅ Formidable service available</p>';
    } else {
        echo '<p>❌ Formidable service not available</p>';
    }
} catch (Exception $e) {
    echo '<p>❌ Service error: ' . $e->getMessage() . '</p>';
}

$entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : 'y8ver';
echo '<p><strong>Entry Key:</strong> ' . $entry_key . '</p>';

echo '<p><strong>Step 5:</strong> Entry resolution</p>';

try {
    $entry_data = $formidable_service->get_entry_by_key($entry_key);
    if ($entry_data['success']) {
        $entry_id = $entry_data['entry_id'];
        echo '<p>✅ Entry ID: ' . $entry_id . '</p>';
        
        // Get post ID
        $post_id = $formidable_service->get_post_id_from_entry($entry_id);
        if ($post_id) {
            echo '<p>✅ Associated Post ID: ' . $post_id . '</p>';
            
            // Check if topics exist in post meta
            echo '<h2>📊 Current Post Meta Data:</h2>';
            for ($i = 1; $i <= 5; $i++) {
                $value = get_post_meta($post_id, "mkcg_topic_{$i}", true);
                echo "<p>mkcg_topic_{$i}: " . (empty($value) ? '❌ EMPTY' : '✅ "' . esc_html($value) . '"') . '</p>';
            }
            
            $who_value = get_post_meta($post_id, 'mkcg_who', true);
            echo "<p>mkcg_who: " . (empty($who_value) ? '❌ EMPTY' : '✅ "' . esc_html($who_value) . '"') . '</p>';
            
            // If no data, add some
            if (empty(get_post_meta($post_id, 'mkcg_topic_1', true))) {
                echo '<h2>🔧 Adding test data...</h2>';
                
                $test_topics = [
                    'mkcg_topic_1' => 'Content Strategy for SaaS Companies',
                    'mkcg_topic_2' => 'Building High-Converting Landing Pages',
                    'mkcg_topic_3' => 'Email Marketing Automation',
                    'mkcg_topic_4' => 'Customer Retention Strategies',
                    'mkcg_topic_5' => 'Scaling Business Operations'
                ];
                
                foreach ($test_topics as $meta_key => $value) {
                    update_post_meta($post_id, $meta_key, $value);
                    echo "<p>✅ Added {$meta_key}</p>";
                }
                
                update_post_meta($post_id, 'mkcg_who', 'SaaS founders');
                echo "<p>✅ Added mkcg_who</p>";
                
                echo '<p><strong>🎉 Test data added! Now go back to your Topics Generator page.</strong></p>';
            }
            
        } else {
            echo '<p>❌ No associated post found</p>';
        }
    } else {
        echo '<p>❌ Entry resolution failed: ' . $entry_data['message'] . '</p>';
    }
} catch (Exception $e) {
    echo '<p>❌ Entry resolution error: ' . $e->getMessage() . '</p>';
}

echo '<h2>✅ DIAGNOSTIC COMPLETE</h2>';
echo '<p>If you see this message, the basic system is working.</p>';
echo '<p><strong>Next step:</strong> Go to your Topics Generator page to see if the data appears.</p>';
?>
