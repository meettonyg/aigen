<?php
/**
 * Test specific post ID 32372 with WHO Component Fix
 * Validates that all authority hook data, topics, and questions are loading correctly
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

$post_id = 32372;

echo '<h1>🎯 Testing Post ID: ' . $post_id . ' - WITH WHO COMPONENT FIX</h1>';
echo '<style>
body{font-family:Arial;line-height:1.6;} 
.debug{background:#f0f0f0;padding:12px;margin:10px 0;border-radius:6px;border-left:4px solid #ccc;} 
.found{background:#e8f5e8;border-left-color:#4caf50;} 
.missing{background:#ffebee;border-left-color:#f44336;} 
.warning{background:#fff3cd;border-left-color:#ff9800;}
.success{background:#d4edda;border-left-color:#28a745;padding:20px;font-weight:bold;}
.section{margin:30px 0;padding:20px;background:#f8f9fa;border-radius:8px;}
.data-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:15px;margin:15px 0;}
.data-item{background:white;padding:15px;border-radius:6px;border:1px solid #ddd;}
.fix-badge{background:#e67e22;color:white;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:bold;}
</style>';

// Check if post exists
$post = get_post($post_id);
if (!$post) {
    echo '<div class="debug missing">❌ Post ' . $post_id . ' does not exist!</div>';
    exit;
}

echo '<div class="debug found">✅ Post exists: "' . esc_html($post->post_title) . '"</div>';
echo '<div class="debug">📊 Post type: ' . $post->post_type . ' | Status: ' . $post->post_status . '</div>';

// Validate post type
if ($post->post_type !== 'guests') {
    echo '<div class="debug warning">⚠️ This is NOT a "guests" post type! Expected: guests, Found: ' . $post->post_type . '</div>';
} else {
    echo '<div class="debug found">✅ Correct post type: "guests"</div>';
}

echo '<div class="section">';
echo '<h2>🔧 WHO Component Fix Test <span class="fix-badge">FIXED</span></h2>';

// Test the WHO component fix specifically
if (class_exists('MKCG_Pods_Service')) {
    $pods_service = new MKCG_Pods_Service();
    
    echo '<h3>📊 Audience Taxonomy Test (Primary WHO Source)</h3>';
    
    // Clear cache as the fix does
    wp_cache_delete($post_id, 'audience_relationships');
    
    // Test audience taxonomy (primary WHO source)
    $audience_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);
    
    if (is_wp_error($audience_terms)) {
        echo '<div class="debug missing">❌ WP_Error getting audience terms: ' . $audience_terms->get_error_message() . '</div>';
    } elseif (!empty($audience_terms)) {
        $audience_string = implode(', ', $audience_terms);
        echo '<div class="debug found">✅ PRIMARY SUCCESS: Found audience taxonomy: <strong>"' . esc_html($audience_string) . '"</strong></div>';
        echo '<div class="debug found">📊 Total audience terms: ' . count($audience_terms) . '</div>';
    } else {
        echo '<div class="debug missing">❌ No audience terms found in taxonomy</div>';
    }
    
    echo '<h3>🔄 Fallback Test (guest_title meta)</h3>';
    $guest_title = get_post_meta($post_id, 'guest_title', true);
    if (!empty($guest_title)) {
        echo '<div class="debug found">✅ FALLBACK available: guest_title = "' . esc_html($guest_title) . '"</div>';
    } else {
        echo '<div class="debug warning">⚠️ No guest_title meta found (fallback would use default)</div>';
    }
    
    echo '<h3>🧪 Complete Authority Hook Test (With Fix)</h3>';
    $auth_components = $pods_service->get_authority_hook_components($post_id);
    
    // Test the WHO component specifically 
    if ($auth_components['who'] !== 'your audience') {
        echo '<div class="success">🎉 WHO COMPONENT FIX SUCCESS! Found: <strong>"' . esc_html($auth_components['who']) . '"</strong></div>';
    } else {
        echo '<div class="debug warning">⚠️ WHO component still showing default value</div>';
    }
    
    // Show all authority hook components
    echo '<div class="data-grid">';
    $components = ['who', 'what', 'when', 'how', 'where', 'why'];
    foreach ($components as $component) {
        $value = $auth_components[$component];
        $is_default = in_array($value, ['your audience', 'achieve their goals', 'they need help', 'through your method', 'in their situation', 'because they deserve success']);
        $class = $is_default ? 'missing' : 'found';
        $status = $is_default ? '📝 Default' : '✅ Custom';
        
        echo '<div class="data-item">';
        echo '<strong>' . strtoupper($component) . ':</strong><br>';
        echo '<div class="debug ' . $class . '">' . $status . ': ' . esc_html($value) . '</div>';
        echo '</div>';
    }
    echo '</div>';
    
    echo '<div class="debug">';
    echo '<strong>Complete Authority Hook:</strong><br>';
    echo '"' . esc_html($auth_components['complete']) . '"';
    echo '</div>';
    
} else {
    echo '<div class="debug missing">❌ MKCG_Pods_Service class not found!</div>';
}

echo '</div>'; // End WHO component section

echo '<div class="section">';
echo '<h2>📝 Topics Verification (5 Expected)</h2>';

$topics_found = 0;
$topics_data = [];

for ($i = 1; $i <= 5; $i++) {
    $topic = get_post_meta($post_id, "topic_{$i}", true);
    $topics_data["topic_{$i}"] = $topic;
    
    if (!empty($topic)) {
        echo '<div class="debug found">✅ topic_' . $i . ': "' . esc_html($topic) . '"</div>';
        $topics_found++;
    } else {
        echo '<div class="debug missing">❌ topic_' . $i . ': EMPTY</div>';
    }
}

echo '<div class="debug">📊 Topics found: <strong>' . $topics_found . '/5</strong></div>';

if ($topics_found > 0) {
    echo '<div class="debug found">✅ Topics data available for Topics Generator</div>';
} else {
    echo '<div class="debug warning">⚠️ No topics found - Topics Generator will show empty fields</div>';
}

echo '</div>'; // End topics section

echo '<div class="section">';
echo '<h2>❓ Questions Verification (25 Expected)</h2>';

$questions_found = 0;
$questions_data = [];

// Test all 25 questions as defined in Pods structure
for ($i = 1; $i <= 25; $i++) {
    $question = get_post_meta($post_id, "question_{$i}", true);
    $questions_data["question_{$i}"] = $question;
    
    if (!empty($question)) {
        echo '<div class="debug found">✅ question_' . $i . ': "' . esc_html(substr($question, 0, 100)) . (strlen($question) > 100 ? '...' : '') . '"</div>';
        $questions_found++;
    } else {
        // Only show first 10 empty questions to avoid clutter
        if ($i <= 10) {
            echo '<div class="debug missing">❌ question_' . $i . ': EMPTY</div>';
        }
    }
}

if ($questions_found < 25 && $questions_found > 0) {
    echo '<div class="debug warning">⚠️ Showing only first 10 empty questions to avoid clutter. ' . (25 - $questions_found) . ' more questions are empty.</div>';
}

echo '<div class="debug">📊 Questions found: <strong>' . $questions_found . '/25</strong></div>';

if ($questions_found > 0) {
    echo '<div class="debug found">✅ Questions data available for Questions Generator</div>';
} else {
    echo '<div class="debug warning">⚠️ No questions found - Questions Generator will show empty fields</div>';
}

echo '</div>'; // End questions section

echo '<div class="section">';
echo '<h2>🔬 Complete Pods Service Test</h2>';

if (class_exists('MKCG_Pods_Service')) {
    $pods_service = new MKCG_Pods_Service();
    $guest_data = $pods_service->get_guest_data($post_id);
    
    echo '<div class="debug found">✅ Pods Service get_guest_data() successful</div>';
    echo '<div class="debug">📊 has_data: ' . ($guest_data['has_data'] ? 'TRUE' : 'FALSE') . '</div>';
    echo '<div class="debug">📊 post_id: ' . $guest_data['post_id'] . '</div>';
    
    // Test topics from Pods service
    $pods_topics = array_filter($guest_data['topics']);
    echo '<div class="debug">📝 Topics via Pods service: ' . count($pods_topics) . '/5</div>';
    
    // Test questions from Pods service  
    $pods_questions = array_filter($guest_data['questions']);
    echo '<div class="debug">❓ Questions via Pods service: ' . count($pods_questions) . '/25</div>';
    
    // Test authority hook from Pods service
    $pods_auth = $guest_data['authority_hook_components'];
    echo '<div class="debug">🔑 Authority Hook WHO via Pods: "' . esc_html($pods_auth['who']) . '"</div>';
    
    // Contact and messaging
    $contact_fields = array_filter($guest_data['contact']);
    $messaging_fields = array_filter($guest_data['messaging']);
    echo '<div class="debug">👤 Contact fields: ' . count($contact_fields) . '</div>';
    echo '<div class="debug">💬 Messaging fields: ' . count($messaging_fields) . '</div>';
    
} else {
    echo '<div class="debug missing">❌ MKCG_Pods_Service class not found!</div>';
}

echo '</div>'; // End Pods service section

echo '<div class="section">';
echo '<h2>🎯 Summary & Recommendations</h2>';

$total_data_points = $topics_found + $questions_found + ($auth_components['who'] !== 'your audience' ? 1 : 0);

if ($total_data_points === 0) {
    echo '<div class="debug missing">❌ NO DATA FOUND - Post ' . $post_id . ' appears to be empty</div>';
    echo '<div class="debug">🔧 SOLUTION: Populate test data or check if data exists in Formidable fields instead</div>';
} elseif ($total_data_points < 10) {
    echo '<div class="debug warning">⚠️ PARTIAL DATA - Some fields populated (' . $total_data_points . ' data points found)</div>';
    echo '<div class="debug">🔧 SUGGESTION: Check if more data exists or populate missing fields</div>';
} else {
    echo '<div class="debug found">✅ GOOD DATA - Post ' . $post_id . ' has substantial content (' . $total_data_points . ' data points)</div>';
    echo '<div class="debug found">🚀 READY: This post should work well with the Topics and Questions Generators</div>';
}

// WHO component specific summary
if (isset($auth_components) && $auth_components['who'] !== 'your audience') {
    echo '<div class="success">🎉 WHO COMPONENT FIX VERIFIED: Successfully retrieving "' . esc_html($auth_components['who']) . '" instead of default</div>';
} else {
    echo '<div class="debug warning">⚠️ WHO component still using default - check audience taxonomy assignment</div>';
}

echo '</div>'; // End summary section

// Test links
echo '<div class="section">';
echo '<h2>🚀 Test Links</h2>';

echo '<p><a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit" style="background:#2196f3;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin:5px;" target="_blank">✏️ Edit Post ' . $post_id . ' in WordPress</a></p>';

echo '<p><a href="test-who-component-fix.php" style="background:#e67e22;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin:5px;" target="_blank">🧪 Run WHO Component Fix Test</a></p>';

echo '<p><a href="test-root-level-simplification.php" style="background:#4caf50;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin:5px;" target="_blank">🔧 Run Complete Simplification Test</a></p>';

echo '</div>';

// Debug info for developers
echo '<div class="section">';
echo '<h2>🔍 Developer Debug Info</h2>';

echo '<details><summary><strong>Click to show all post meta fields</strong></summary>';
$all_meta = get_post_meta($post_id);
echo '<div style="background:#f8f9fa;padding:15px;margin:10px 0;border-radius:6px;max-height:400px;overflow-y:scroll;">';
foreach ($all_meta as $key => $values) {
    $value = is_array($values) ? $values[0] : $values;
    $short_value = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
    echo '<div><strong>' . esc_html($key) . ':</strong> ' . esc_html($short_value) . '</div>';
}
echo '</div>';
echo '</details>';

echo '<details><summary><strong>Click to show audience taxonomy debug</strong></summary>';
echo '<div style="background:#f8f9fa;padding:15px;margin:10px 0;border-radius:6px;">';
$all_terms = wp_get_post_terms($post_id, '', ['fields' => 'all']);
if (!empty($all_terms) && !is_wp_error($all_terms)) {
    echo '<strong>All taxonomy terms for this post:</strong><br>';
    foreach ($all_terms as $term) {
        $highlight = ($term->taxonomy === 'audience') ? 'style="background:yellow;padding:2px 4px;"' : '';
        echo '<div ' . $highlight . '>' . $term->taxonomy . ': ' . $term->name . ' (ID: ' . $term->term_id . ')</div>';
    }
} else {
    echo 'No taxonomy terms found for this post.';
}
echo '</div>';
echo '</details>';

echo '</div>';

echo '<div style="margin-top:40px;padding:20px;background:#e3f2fd;border-radius:8px;border-left:6px solid #2196f3;">';
echo '<h3>🎯 Test Results Summary for Post ' . $post_id . ':</h3>';
echo '<ul>';
echo '<li><strong>WHO Component Fix:</strong> ' . (isset($auth_components) && $auth_components['who'] !== 'your audience' ? '✅ Working' : '❌ Using Default') . '</li>';
echo '<li><strong>Topics Available:</strong> ' . $topics_found . '/5</li>';
echo '<li><strong>Questions Available:</strong> ' . $questions_found . '/25</li>';
echo '<li><strong>Post Type:</strong> ' . ($post->post_type === 'guests' ? '✅ Correct' : '❌ Wrong') . '</li>';
echo '<li><strong>Overall Status:</strong> ' . ($total_data_points > 10 ? '✅ Ready for Testing' : '⚠️ Needs More Data') . '</li>';
echo '</ul>';
echo '</div>';
?>
