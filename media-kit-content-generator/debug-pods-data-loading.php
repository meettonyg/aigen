<?php
/**
 * Debug script to test Pods data loading
 * Located in plugin root directory
 */

// Include WordPress
$wp_load_path = '';
$current_dir = dirname(__FILE__);

// Updated paths for plugin root location
$possible_paths = [
    $current_dir . '/../../../wp-load.php', // Most common: plugins/plugin-name/
    $current_dir . '/../../../../wp-load.php', // Alternative structure
    $current_dir . '/../../../../../wp-load.php',
    ABSPATH . 'wp-load.php', // If ABSPATH is defined
    dirname(dirname(dirname($current_dir))) . '/wp-load.php'
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $wp_load_path = $path;
        break;
    }
}

if (empty($wp_load_path)) {
    die('WordPress not found. Please adjust the path to wp-load.php in this script.');
}

require_once $wp_load_path;

echo "<h1>Pods Data Loading Debug</h1>";
echo "<p><strong>Script Location:</strong> Plugin Root Directory</p>";

// 1. Check if Pods plugin is active
if (!function_exists('pods')) {
    echo "<div style='color:red'>❌ Pods plugin is not active!</div>";
    exit;
}

echo "<div style='color:green'>✅ Pods plugin is active</div>";

// 2. Check for guests post type
$guests_posts = get_posts([
    'post_type' => 'guests',
    'post_status' => 'any',
    'numberposts' => 5
]);

echo "<h2>Guest Posts Found:</h2>";
if (empty($guests_posts)) {
    echo "<div style='color:red'>❌ No guest posts found! Make sure you have created some guest entries.</div>";
    echo "<p>To test the fixes, you need to:</p>";
    echo "<ol>";
    echo "<li>Create a guest post in WordPress admin</li>";
    echo "<li>Add some data to the topic and authority hook fields</li>";
    echo "<li>Run this test again</li>";
    echo "</ol>";
    exit;
}

echo "<div style='color:green'>✅ Found " . count($guests_posts) . " guest posts</div>";

// Display guest posts in a table
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; margin:20px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Post ID</th><th>Title</th><th>Topics</th><th>Authority Hook</th><th>Actions</th></tr>";

foreach ($guests_posts as $post) {
    // Count topics
    $topic_count = 0;
    $topics_data = [];
    for ($i = 1; $i <= 5; $i++) {
        $topic = get_post_meta($post->ID, "topic_{$i}", true);
        if (!empty($topic)) {
            $topic_count++;
            $topics_data[$i] = $topic;
        }
    }
    
    // Count authority hook components
    $hook_fields = ['guest_title', 'hook_when', 'hook_what', 'hook_how', 'hook_where', 'hook_why'];
    $hook_count = 0;
    $hook_data = [];
    foreach ($hook_fields as $field) {
        $value = get_post_meta($post->ID, $field, true);
        if (!empty($value)) {
            $hook_count++;
            $hook_data[$field] = $value;
        }
    }
    
    $topic_status = $topic_count > 0 ? "✅ {$topic_count}/5" : "❌ 0/5";
    $hook_status = $hook_count > 0 ? "✅ {$hook_count}/6" : "❌ 0/6";
    
    echo "<tr>";
    echo "<td>{$post->ID}</td>";
    echo "<td>{$post->post_title}</td>";
    echo "<td>{$topic_status}</td>";
    echo "<td>{$hook_status}</td>";
    echo "<td><a href='?post_id={$post->ID}' style='background:#0073aa;color:white;padding:5px 10px;text-decoration:none;border-radius:3px;'>Test This Post</a></td>";
    echo "</tr>";
    
    // Show detailed data if testing specific post
    if (isset($_GET['post_id']) && $_GET['post_id'] == $post->ID) {
        echo "<tr><td colspan='5' style='background:#f9f9f9;'>";
        echo "<h4>Detailed Data for Post {$post->ID}:</h4>";
        
        echo "<h5>Topics:</h5>";
        if (!empty($topics_data)) {
            foreach ($topics_data as $num => $topic) {
                echo "• Topic {$num}: " . esc_html($topic) . "<br>";
            }
        } else {
            echo "No topics found<br>";
        }
        
        echo "<h5>Authority Hook Components:</h5>";
        if (!empty($hook_data)) {
            foreach ($hook_data as $field => $value) {
                echo "• {$field}: " . esc_html($value) . "<br>";
            }
        } else {
            echo "No authority hook data found<br>";
        }
        
        echo "</td></tr>";
    }
}

echo "</table>";

// 3. Test Pods service if it exists
echo "<h2>Testing MKCG Pods Service:</h2>";
$pods_service_path = dirname(__FILE__) . '/includes/services/class-mkcg-pods-service.php';
if (file_exists($pods_service_path)) {
    require_once $pods_service_path;
    
    if (class_exists('MKCG_Pods_Service')) {
        $pods_service = new MKCG_Pods_Service();
        echo "<div style='color:green'>✅ MKCG_Pods_Service loaded successfully</div>";
        
        if (!empty($guests_posts)) {
            $test_post = $guests_posts[0];
            echo "<h3>Testing service with Post ID: {$test_post->ID}</h3>";
            
            echo "<h4>Topics Test:</h4>";
            $topics = $pods_service->get_topics($test_post->ID);
            $filled_topics = array_filter($topics);
            echo "Found " . count($filled_topics) . "/5 topics with data<br>";
            
            echo "<h4>Authority Hook Test:</h4>";
            $auth_components = $pods_service->get_authority_hook_components($test_post->ID);
            $meaningful_components = 0;
            $defaults = ['they need help', 'achieve their goals', 'through your method', 'in their situation', 'because they deserve success', 'your audience'];
            foreach ($auth_components as $key => $value) {
                if ($key !== 'complete' && !empty($value) && !in_array($value, $defaults)) {
                    $meaningful_components++;
                }
            }
            echo "Found {$meaningful_components}/6 components with meaningful data<br>";
            echo "WHO: " . $auth_components['who'] . "<br>";
            echo "Complete Hook: " . $auth_components['complete'] . "<br>";
        }
    } else {
        echo "<div style='color:red'>❌ MKCG_Pods_Service class not found</div>";
    }
} else {
    echo "<div style='color:red'>❌ MKCG Pods Service file not found at: {$pods_service_path}</div>";
}

// 4. Test URL parameters
echo "<h2>URL Parameter Testing:</h2>";
echo "<p>Current parameters:</p>";
echo "• entry: " . (isset($_GET['entry']) ? $_GET['entry'] : 'not set') . "<br>";
echo "• post_id: " . (isset($_GET['post_id']) ? $_GET['post_id'] : 'not set') . "<br>";

if (isset($_GET['entry'])) {
    $entry_id = intval($_GET['entry']);
    
    // Check if this entry exists in Formidable
    global $wpdb;
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
        $entry_id
    ));
    
    if ($post_id) {
        echo "<div style='color:green'>✅ Entry {$entry_id} links to Post ID: {$post_id}</div>";
    } else {
        echo "<div style='color:red'>❌ Entry {$entry_id} not found or not linked to a post</div>";
    }
}

// 5. Quick access links
echo "<h2>Quick Test Links:</h2>";
echo "<div style='background:#f0f8ff;padding:15px;border:1px solid #2196f3;border-radius:5px;'>";
if (!empty($guests_posts)) {
    echo "<h3>Test with specific posts:</h3>";
    foreach ($guests_posts as $post) {
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $base_url = strtok($current_url, '?');
        echo "<a href='{$base_url}?post_id={$post->ID}' style='display:inline-block;margin:5px;padding:8px 15px;background:#0073aa;color:white;text-decoration:none;border-radius:3px;'>Test Post {$post->ID}: {$post->post_title}</a> ";
    }
} else {
    echo "<p>No guest posts available to test. Create some guest posts first!</p>";
}
echo "</div>";

// 6. Usage instructions
echo "<h2>How to Use:</h2>";
echo "<div style='background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:5px;'>";
echo "<ol>";
echo "<li><strong>Create guest posts:</strong> Go to WordPress admin → Guest One Sheets → Add New</li>";
echo "<li><strong>Add data:</strong> Fill in topic fields (topic_1, topic_2, etc.) and authority hook fields</li>";
echo "<li><strong>Test here:</strong> Click the test links above to see if data loads correctly</li>";
echo "<li><strong>Use in Topics Generator:</strong> Add <code>[mkcg_topics]</code> shortcode to any page</li>";
echo "<li><strong>URL testing:</strong> Use <code>?post_id=123</code> parameter to test specific posts</li>";
echo "</ol>";
echo "</div>";

echo "<hr><p><small>Debug completed at: " . date('Y-m-d H:i:s') . " | Plugin Root Directory</small></p>";
?>