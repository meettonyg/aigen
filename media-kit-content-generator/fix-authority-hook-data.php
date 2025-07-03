<?php
/**
 * Quick fix: Populate test authority hook data
 * Use this if you need test data to see the system working
 */

// WordPress bootstrap - Multiple path attempts for different installations
if (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
} elseif (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
} elseif (file_exists('../../../../../../wp-load.php')) {
    require_once('../../../../../../wp-load.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
} else {
    die('Could not locate wp-load.php. Please run this from WordPress admin or adjust the path.');
}

if (!current_user_can('administrator')) {
    die('Access denied - admin only');
}

echo '<h1>🔧 Authority Hook Data Fixer</h1>';
echo '<style>body{font-family:Arial;} .debug{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:4px;} .success{background:#e8f5e8;} .error{background:#ffebee;}</style>';

// Check if specific post ID requested
$target_post_id = isset($_GET['test_post']) ? intval($_GET['test_post']) : null;

if ($target_post_id) {
    // Test specific post
    $post = get_post($target_post_id);
    if (!$post) {
        echo '<div class="debug error">❌ Post ' . $target_post_id . ' not found!</div>';
        exit;
    }
    
    echo "<h2>🎯 Populating test data for specific post: {$post->post_title} (ID: {$target_post_id})</h2>";
    $posts_to_populate = [$post];
} else {
    // Get the most recent guest post
    $guest_posts = get_posts([
        'post_type' => 'guests',
        'post_status' => 'publish',
        'numberposts' => 1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    if (empty($guest_posts)) {
        echo '<div class="debug error">❌ No guest posts found! Create a guest post first.</div>';
        exit;
    }
    
    $posts_to_populate = $guest_posts;
    echo "<h2>🎯 Populating test data for most recent guest post: {$guest_posts[0]->post_title} (ID: {$guest_posts[0]->ID})</h2>";
}

// Test authority hook data
$test_data = [
    'guest_title' => 'entrepreneurs and business owners',
    'hook_what' => 'scale their revenue to 7-figures',
    'hook_when' => 'they feel stuck at their current plateau',
    'hook_how' => 'my proven 3-phase growth framework',
    'hook_where' => 'without burning out their team',
    'hook_why' => 'because sustainable growth creates lasting impact'
];

// Test topics data
$test_topics = [
    'topic_1' => 'The 3 Hidden Revenue Leaks Killing Your Growth (And How to Plug Them)',
    'topic_2' => 'Why Most Scaling Strategies Fail (And the Framework That Actually Works)',
    'topic_3' => 'From Overwhelmed Owner to Strategic CEO: The Mindset Shift That Changes Everything',
    'topic_4' => 'Building Systems That Scale: How to Remove Yourself from Daily Operations',
    'topic_5' => 'The 7-Figure Timeline: Realistic Milestones for Sustainable Growth'
];

foreach ($posts_to_populate as $post) {
    $success_count = 0;
    
    echo "<h3>💾 Processing: {$post->post_title} (ID: {$post->ID})</h3>";

    // Save authority hook data
    echo '<h4>💾 Saving Authority Hook Components:</h4>';
    foreach ($test_data as $field_name => $value) {
        $result = update_post_meta($post->ID, $field_name, $value);
        $class = $result !== false ? 'success' : 'error';
        $status = $result !== false ? '✅' : '❌';
        
        echo "<div class='debug {$class}'>{$status} {$field_name}: {$value}</div>";
        
        if ($result !== false) {
            $success_count++;
        }
    }

    // Save topics data  
    echo '<h4>📝 Saving Topics:</h4>';
    foreach ($test_topics as $field_name => $value) {
        $result = update_post_meta($post->ID, $field_name, $value);
        $class = $result !== false ? 'success' : 'error';
        $status = $result !== false ? '✅' : '❌';
        
        echo "<div class='debug {$class}'>{$status} {$field_name}: {$value}</div>";
        
        if ($result !== false) {
            $success_count++;
        }
    }

    echo "<div class='debug success'>🎉 Successfully saved {$success_count} fields to post {$post->ID}!</div>";

    // Build complete authority hook
    $complete = sprintf(
        'I help %s %s when %s %s %s %s.',
        $test_data['guest_title'],
        $test_data['hook_what'],
        $test_data['hook_when'],
        $test_data['hook_how'],
        $test_data['hook_where'],
        $test_data['hook_why']
    );

    echo "<h4>🔗 Complete Authority Hook for Post {$post->ID}:</h4>";
    echo "<div class='debug success'><strong>{$complete}</strong></div>";

    echo '<h4>🚀 Next Steps:</h4>';
    echo "<div class='debug'>1. Refresh your Topics Generator page with: ?post_id={$post->ID}</div>";
    echo '<div class="debug">2. You should now see the test authority hook and topics</div>';
    echo '<div class="debug">3. The authority hook builder should show the individual components</div>';
    echo '<div class="debug">4. If it works, you can edit the components and save your real data</div>';

    echo "<p><a href='?post_id={$post->ID}' style='background:#2196f3;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;' target='_blank'>📱 Test Topics Generator with Post {$post->ID}</a></p>";
    
    if (count($posts_to_populate) > 1) {
        echo '<hr style="margin: 30px 0;">';
    }
}
?>