<?php
/**
 * Debug Authority Hook Data - WordPress Admin Page
 * Add this to your functions.php temporarily or run via WP-CLI
 */

// Add to WordPress admin menu
add_action('admin_menu', 'mkcg_debug_menu');

function mkcg_debug_menu() {
    add_management_page(
        'MKCG Debug Authority Hook', 
        'MKCG Debug', 
        'manage_options', 
        'mkcg-debug-authority-hook', 
        'mkcg_debug_authority_hook_page'
    );
}

function mkcg_debug_authority_hook_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Access denied');
    }
    
    echo '<div class="wrap">';
    echo '<h1>🔍 Authority Hook Data Debug</h1>';
    echo '<style>.debug{background:#f0f0f0;padding:10px;margin:10px 0;border-radius:4px;} .found{background:#e8f5e8;} .missing{background:#ffebee;}</style>';

    // Get the most recent guest post
    $guest_posts = get_posts([
        'post_type' => 'guests',
        'post_status' => 'publish',
        'numberposts' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    if (empty($guest_posts)) {
        echo '<div class="debug missing">❌ No guest posts found!</div>';
        
        // Show all custom post types
        $post_types = get_post_types(['public' => true], 'names');
        echo '<div class="debug">Available post types: ' . implode(', ', $post_types) . '</div>';
        echo '</div>';
        return;
    }

    echo '<div class="debug found">✅ Found ' . count($guest_posts) . ' guest posts</div>';

    foreach ($guest_posts as $post) {
        echo "<h2>🎯 Guest Post: {$post->post_title} (ID: {$post->ID})</h2>";
        
        // Get ALL meta fields for this post
        $all_meta = get_post_meta($post->ID);
        echo '<div class="debug">📊 Total meta fields: ' . count($all_meta) . '</div>';
        
        // Authority hook related fields we're looking for
        $authority_fields = [
            'guest_title' => 'WHO',
            'hook_what' => 'WHAT', 
            'hook_when' => 'WHEN',
            'hook_how' => 'HOW',
            'hook_where' => 'WHERE',
            'hook_why' => 'WHY'
        ];
        
        echo '<h3>🔑 Authority Hook Fields:</h3>';
        $found_count = 0;
        
        foreach ($authority_fields as $field_name => $label) {
            $value = get_post_meta($post->ID, $field_name, true);
            $class = !empty($value) ? 'found' : 'missing';
            $status = !empty($value) ? '✅' : '❌';
            $display_value = !empty($value) ? esc_html($value) : 'EMPTY';
            
            echo "<div class='debug {$class}'>{$status} {$label} ({$field_name}): <strong>{$display_value}</strong></div>";
            
            if (!empty($value)) {
                $found_count++;
            }
        }
        
        echo '<div class="debug">📈 Authority hook fields found: ' . $found_count . '/6</div>';
        
        // Topics fields
        echo '<h3>📝 Topics Fields:</h3>';
        $topics_found = 0;
        
        for ($i = 1; $i <= 5; $i++) {
            $topic = get_post_meta($post->ID, "topic_{$i}", true);
            $class = !empty($topic) ? 'found' : 'missing';
            $status = !empty($topic) ? '✅' : '❌';
            $display_value = !empty($topic) ? esc_html($topic) : 'EMPTY';
            
            echo "<div class='debug {$class}'>{$status} topic_{$i}: <strong>{$display_value}</strong></div>";
            
            if (!empty($topic)) {
                $topics_found++;
            }
        }
        
        echo '<div class="debug">📈 Topics found: ' . $topics_found . '/5</div>';
        
        // Show action buttons
        echo '<div style="margin: 20px 0;">';
        echo '<a href="' . admin_url('tools.php?page=mkcg-debug-authority-hook&action=populate&post_id=' . $post->ID) . '" class="button button-primary">🔧 Populate Test Data for This Post</a> ';
        
        // Get the Topics Generator URL with this post ID
        $topics_page = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'topics-generator']); 
        if (!empty($topics_page)) {
            $topics_url = get_permalink($topics_page[0]->ID) . '?post_id=' . $post->ID;
        } else {
            // Fallback - try to find a page with the shortcode
            $pages_with_shortcode = get_posts([
                'post_type' => 'page',
                'meta_query' => [
                    [
                        'key' => '_',
                        'value' => 'mkcg_topics',
                        'compare' => 'LIKE'
                    ]
                ]
            ]);
            
            if (!empty($pages_with_shortcode)) {
                $topics_url = get_permalink($pages_with_shortcode[0]->ID) . '?post_id=' . $post->ID;
            } else {
                $topics_url = home_url('/?post_id=' . $post->ID);
            }
        }
        
        echo '<a href="' . $topics_url . '" class="button">📱 Test Topics Generator</a>';
        echo '</div>';
        
        echo '<hr style="margin: 30px 0;">';
        
        // Show only first 2 posts to avoid overwhelming
        if ($post === $guest_posts[1]) {
            break;
        }
    }

    // Handle populate action
    if (isset($_GET['action']) && $_GET['action'] === 'populate' && isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
        echo '<h2>🔧 Populating Test Data</h2>';
        
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

        $success_count = 0;

        // Save authority hook data
        foreach ($test_data as $field_name => $value) {
            $result = update_post_meta($post_id, $field_name, $value);
            if ($result !== false) {
                $success_count++;
            }
        }

        // Save topics data  
        foreach ($test_topics as $field_name => $value) {
            $result = update_post_meta($post_id, $field_name, $value);
            if ($result !== false) {
                $success_count++;
            }
        }

        echo "<div class='debug found'>🎉 Successfully saved {$success_count} fields to post {$post_id}!</div>";
        echo '<div class="debug">Now refresh this page to see the data, or click "Test Topics Generator" above.</div>';
    }

    echo '</div>';
}
?>