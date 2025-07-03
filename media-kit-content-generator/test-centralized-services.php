<?php
/**
 * Centralized Services Test File
 * Tests Formidable integration and custom post data handling
 * 
 * This file provides a comprehensive interface to test the centralized
 * configuration system and verify data flow between all components.
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    // If not in WordPress, try to load it
    require_once('../../../wp-config.php');
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Load plugin dependencies
require_once plugin_dir_path(__FILE__) . 'includes/services/class-mkcg-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/services/enhanced_formidable_service.php';

// Initialize services
$formidable_service = new Enhanced_Formidable_Service();

// Handle form submissions
$message = '';
$message_type = '';

if ($_POST) {
    $action = sanitize_text_field($_POST['action'] ?? '');
    
    switch ($action) {
        case 'load_entry_data':
            $entry_id = intval($_POST['entry_id'] ?? 0);
            $result = test_load_entry_data($entry_id, $formidable_service);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'save_formidable_data':
            $entry_id = intval($_POST['entry_id'] ?? 0);
            $field_data = $_POST['formidable_fields'] ?? [];
            $result = test_save_formidable_data($entry_id, $field_data, $formidable_service);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'save_custom_post_data':
            $post_id = intval($_POST['post_id'] ?? 0);
            $meta_data = $_POST['custom_post_fields'] ?? [];
            $result = test_save_custom_post_data($post_id, $meta_data);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'test_centralized_config':
            $entry_id = intval($_POST['entry_id'] ?? 0);
            $result = test_centralized_config($entry_id, $formidable_service);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
    }
}

/**
 * Test loading entry data
 */
function test_load_entry_data($entry_id, $formidable_service) {
    if (!$entry_id) {
        return ['success' => false, 'message' => 'Please provide an entry ID'];
    }
    
    try {
        $result = $formidable_service->get_entry_data($entry_id);
        
        if ($result['success']) {
            $field_count = count($result['field_data']);
            return [
                'success' => true, 
                'message' => "Successfully loaded {$field_count} fields from entry {$entry_id}",
                'data' => $result['field_data']
            ];
        } else {
            return ['success' => false, 'message' => $result['message']];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Test saving Formidable data
 */
function test_save_formidable_data($entry_id, $field_data, $formidable_service) {
    if (!$entry_id) {
        return ['success' => false, 'message' => 'Please provide an entry ID'];
    }
    
    if (empty($field_data)) {
        return ['success' => false, 'message' => 'No field data provided'];
    }
    
    try {
        $result = $formidable_service->save_entry_data($entry_id, $field_data);
        return $result;
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Test saving custom post data
 */
function test_save_custom_post_data($post_id, $meta_data) {
    if (!$post_id) {
        return ['success' => false, 'message' => 'Please provide a post ID'];
    }
    
    if (empty($meta_data)) {
        return ['success' => false, 'message' => 'No meta data provided'];
    }
    
    try {
        $saved_count = 0;
        foreach ($meta_data as $meta_key => $meta_value) {
            if (!empty($meta_value)) {
                $result = update_post_meta($post_id, $meta_key, $meta_value);
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? "Saved {$saved_count} post meta fields" : 'No data saved'
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Test centralized configuration
 */
function test_centralized_config($entry_id, $formidable_service) {
    if (!$entry_id) {
        return ['success' => false, 'message' => 'Please provide an entry ID'];
    }
    
    try {
        $data = MKCG_Config::load_data_for_entry($entry_id, $formidable_service);
        
        $topics_count = count(array_filter($data['form_field_values']));
        $auth_components = count(array_filter($data['authority_hook_components']));
        $questions_count = count($data['questions']);
        
        return [
            'success' => true,
            'message' => "Centralized config loaded: {$topics_count} topics, {$auth_components} auth components, {$questions_count} question groups",
            'data' => $data
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get sample entry IDs for testing
 */
function get_sample_entries() {
    global $wpdb;
    $entries = $wpdb->get_results(
        "SELECT id, item_key, post_id FROM {$wpdb->prefix}frm_items ORDER BY id DESC LIMIT 10",
        ARRAY_A
    );
    return $entries ?: [];
}

/**
 * Get sample post IDs for testing
 */
function get_sample_posts() {
    $posts = get_posts([
        'post_type' => 'any',
        'numberposts' => 10,
        'meta_query' => [
            [
                'key' => '_frm_entry_id',
                'compare' => 'EXISTS'
            ]
        ]
    ]);
    
    return array_map(function($post) {
        return [
            'ID' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'entry_id' => get_post_meta($post->ID, '_frm_entry_id', true)
        ];
    }, $posts);
}

// Get sample data for dropdowns
$sample_entries = get_sample_entries();
$sample_posts = get_sample_posts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralized Services Test - Media Kit Content Generator</title>
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
            border-bottom: 2px solid #0073aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #0073aa;
            margin: 0 0 10px 0;
        }
        
        .header p {
            margin: 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fafafa;
        }
        
        .section h2 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #23282d;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        .button:hover {
            background: #005a87;
        }
        
        .button-secondary {
            background: #666;
        }
        
        .button-secondary:hover {
            background: #555;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .data-display {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .data-display pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .field-mapping {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .field-mapping h4 {
            margin: 0 0 10px 0;
            color: #0056b3;
        }
        
        .field-mapping ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            margin-right: 2px;
        }
        
        .tab.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Centralized Services Test</h1>
            <p>Test Formidable fields and custom post data integration with the Media Kit Content Generator</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <div class="field-mapping">
            <h4>📋 Field Mappings Reference</h4>
            <div class="form-row">
                <div>
                    <strong>Formidable Fields:</strong>
                    <ul>
                        <li><strong>WHEN:</strong> Field ID 10387</li>
                        <li><strong>WHAT:</strong> Field ID 10297</li>
                        <li><strong>HOW:</strong> Field ID 10298</li>
                        <li><strong>WHERE:</strong> Field ID 10359</li>
                        <li><strong>WHY:</strong> Field ID 10360</li>
                    </ul>
                </div>
                <div>
                    <strong>Custom Post Meta:</strong>
                    <ul>
                        <li><strong>WHO:</strong> mkcg_who</li>
                        <li><strong>Topics:</strong> mkcg_topic_1 to mkcg_topic_5</li>
                        <li><strong>Questions:</strong> mkcg_question_{topic}_{num}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" onclick="showTab('load-data')">📖 Load Data</div>
            <div class="tab" onclick="showTab('save-formidable')">💾 Save Formidable</div>
            <div class="tab" onclick="showTab('save-custom-post')">📝 Save Custom Post</div>
            <div class="tab" onclick="showTab('test-config')">⚙️ Test Config</div>
        </div>

        <!-- Load Data Tab -->
        <div id="load-data" class="tab-content active">
            <div class="section">
                <h2>📖 Load Entry Data</h2>
                <p>Load and display data from a Formidable entry</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="load_entry_data">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="entry_id_load">Entry ID:</label>
                            <select name="entry_id" id="entry_id_load">
                                <option value="">Select an entry...</option>
                                <!-- Your specific test entry -->
                                <option value="74492" style="background: #e8f4fd; font-weight: bold;">
                                    🎯 TEST ENTRY: ID 74492 (Key: y8ver) - YOUR TEST DATA
                                </option>
                                <option value="" disabled>-- Other Entries --</option>
                                <?php foreach ($sample_entries as $entry): ?>
                                    <option value="<?php echo $entry['id']; ?>">
                                        ID: <?php echo $entry['id']; ?> 
                                        (Key: <?php echo $entry['item_key']; ?>)
                                        <?php if ($entry['post_id']): ?>
                                            - Post: <?php echo $entry['post_id']; ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="button">Load Data</button>
                        </div>
                    </div>
                </form>

                <?php if (isset($result) && $_POST['action'] === 'load_entry_data' && $result['success']): ?>
                    <div class="data-display">
                        <strong>Loaded Data:</strong>
                        <pre><?php echo esc_html(json_encode($result['data'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Save Formidable Tab -->
        <div id="save-formidable" class="tab-content">
            <div class="section">
                <h2>💾 Save Formidable Data</h2>
                <p>Save data to Formidable form fields</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="save_formidable_data">
                    
                    <div class="form-group">
                        <label for="entry_id_save">Entry ID:</label>
                        <select name="entry_id" id="entry_id_save">
                            <option value="">Select an entry...</option>
                            <!-- Your specific test entry -->
                            <option value="74492" style="background: #e8f4fd; font-weight: bold;">
                                🎯 TEST ENTRY: ID 74492 (Key: y8ver) - YOUR TEST DATA
                            </option>
                            <option value="" disabled>-- Other Entries --</option>
                            <?php foreach ($sample_entries as $entry): ?>
                                <option value="<?php echo $entry['id']; ?>">
                                    ID: <?php echo $entry['id']; ?> 
                                    (Key: <?php echo $entry['item_key']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h3>Formidable Fields:</h3>
                    
                    <div class="form-group">
                        <label for="when_field">WHEN do they need you? (Field 10387):</label>
                        <textarea name="formidable_fields[10387]" id="when_field" placeholder="e.g., When they're struggling with content creation..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="what_field">WHAT result do you help them achieve? (Field 10297):</label>
                        <textarea name="formidable_fields[10297]" id="what_field" placeholder="e.g., Create engaging content that converts..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="how_field">HOW do you help them achieve this result? (Field 10298):</label>
                        <textarea name="formidable_fields[10298]" id="how_field" placeholder="e.g., Through my proven content framework..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="where_field">WHERE have you demonstrated results? (Field 10359):</label>
                        <textarea name="formidable_fields[10359]" id="where_field" placeholder="e.g., Featured in major publications..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="why_field">WHY are you passionate about this? (Field 10360):</label>
                        <textarea name="formidable_fields[10360]" id="why_field" placeholder="e.g., I believe everyone deserves to share their story..."></textarea>
                    </div>
                    
                    <button type="submit" class="button">Save Formidable Data</button>
                </form>
            </div>
        </div>

        <!-- Save Custom Post Tab -->
        <div id="save-custom-post" class="tab-content">
            <div class="section">
                <h2>📝 Save Custom Post Data</h2>
                <p>Save data to custom post meta fields</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="save_custom_post_data">
                    
                    <div class="form-group">
                        <label for="post_id">Post ID:</label>
                        <select name="post_id" id="post_id">
                            <option value="">Select a post...</option>
                            <?php foreach ($sample_posts as $post): ?>
                                <option value="<?php echo $post['ID']; ?>">
                                    ID: <?php echo $post['ID']; ?> - <?php echo esc_html($post['title']); ?> 
                                    (<?php echo $post['type']; ?>)
                                    <?php if ($post['entry_id']): ?>
                                        - Entry: <?php echo $post['entry_id']; ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h3>WHO Field:</h3>
                    <div class="form-group">
                        <label for="who_field">WHO do you help?:</label>
                        <textarea name="custom_post_fields[mkcg_who]" id="who_field" placeholder="e.g., entrepreneurs, small business owners..."></textarea>
                    </div>

                    <h3>Topics (Custom Post Meta):</h3>
                    <div class="form-group">
                        <label for="topic_1">Topic 1:</label>
                        <input type="text" name="custom_post_fields[mkcg_topic_1]" id="topic_1" placeholder="First topic">
                    </div>
                    
                    <div class="form-group">
                        <label for="topic_2">Topic 2:</label>
                        <input type="text" name="custom_post_fields[mkcg_topic_2]" id="topic_2" placeholder="Second topic">
                    </div>
                    
                    <div class="form-group">
                        <label for="topic_3">Topic 3:</label>
                        <input type="text" name="custom_post_fields[mkcg_topic_3]" id="topic_3" placeholder="Third topic">
                    </div>
                    
                    <div class="form-group">
                        <label for="topic_4">Topic 4:</label>
                        <input type="text" name="custom_post_fields[mkcg_topic_4]" id="topic_4" placeholder="Fourth topic">
                    </div>
                    
                    <div class="form-group">
                        <label for="topic_5">Topic 5:</label>
                        <input type="text" name="custom_post_fields[mkcg_topic_5]" id="topic_5" placeholder="Fifth topic">
                    </div>

                    <h3>Sample Questions:</h3>
                    <div class="form-group">
                        <label for="question_1_1">Question 1 for Topic 1:</label>
                        <input type="text" name="custom_post_fields[mkcg_question_1_1]" id="question_1_1" placeholder="Sample question for topic 1">
                    </div>
                    
                    <div class="form-group">
                        <label for="question_1_2">Question 2 for Topic 1:</label>
                        <input type="text" name="custom_post_fields[mkcg_question_1_2]" id="question_1_2" placeholder="Another question for topic 1">
                    </div>
                    
                    <button type="submit" class="button">Save Custom Post Data</button>
                </form>
            </div>
        </div>

        <!-- Test Config Tab -->
        <div id="test-config" class="tab-content">
            <div class="section">
                <h2>⚙️ Test Centralized Configuration</h2>
                <p>Test the centralized configuration system that loads data from both sources</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="test_centralized_config">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="entry_id_config">Entry ID:</label>
                            <select name="entry_id" id="entry_id_config">
                            <option value="">Select an entry...</option>
                            <!-- Your specific test entry -->
                            <option value="74492" style="background: #e8f4fd; font-weight: bold;">
                            🎯 TEST ENTRY: ID 74492 (Key: y8ver) - YOUR TEST DATA
                            </option>
                            <option value="" disabled>-- Other Entries --</option>
                            <?php foreach ($sample_entries as $entry): ?>
                            <option value="<?php echo $entry['id']; ?>">
                                ID: <?php echo $entry['id']; ?> 
                                    (Key: <?php echo $entry['item_key']; ?>)
                                        <?php if ($entry['post_id']): ?>
                                        - Post: <?php echo $entry['post_id']; ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="button">Test Centralized Config</button>
                        </div>
                    </div>
                </form>

                <?php if (isset($result) && $_POST['action'] === 'test_centralized_config' && $result['success']): ?>
                    <div class="data-display">
                        <strong>Centralized Configuration Result:</strong>
                        <pre><?php echo esc_html(json_encode($result['data'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?>

                <div class="field-mapping">
                    <h4>How Centralized Config Works:</h4>
                    <p>The centralized configuration system (MKCG_Config) loads data from both sources:</p>
                    <ul>
                        <li><strong>Formidable Fields:</strong> WHEN, WHAT, HOW, WHERE, WHY fields</li>
                        <li><strong>Custom Post Meta:</strong> WHO field, Topics, Questions</li>
                        <li><strong>Authority Hook:</strong> Combines data from both sources into complete hook</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🔧 Quick Actions</h2>
            <div class="form-row">
                <div>
                    <h4>Create Test Data:</h4>
                    <button type="button" class="button button-secondary" onclick="fillSampleData('formidable')">
                        Fill Sample Formidable Data
                    </button>
                </div>
                <div>
                    <h4>Debug Info:</h4>
                    <button type="button" class="button button-secondary" onclick="showDebugInfo()">
                        Show WordPress Debug Info
                    </button>
                </div>
            </div>
        </div>

        <div id="debug-info" style="display: none;" class="data-display">
            <strong>WordPress Debug Info:</strong>
            <pre><?php
                echo "WordPress Version: " . get_bloginfo('version') . "\n";
                echo "Plugin Path: " . plugin_dir_path(__FILE__) . "\n";
                echo "Current User: " . wp_get_current_user()->user_login . "\n";
                echo "Formidable Active: " . (class_exists('FrmDb') ? 'YES' : 'NO') . "\n";
                echo "MKCG Config Available: " . (class_exists('MKCG_Config') ? 'YES' : 'NO') . "\n";
                echo "Enhanced Formidable Service Available: " . (class_exists('Enhanced_Formidable_Service') ? 'YES' : 'NO') . "\n";
                
                global $wpdb;
                $frm_entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}frm_items");
                echo "Total Formidable Entries: " . $frm_entries . "\n";
                
                $posts_with_meta = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'mkcg_%'");
                echo "Posts with MKCG Meta: " . $posts_with_meta . "\n";
            ?></pre>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Deactivate all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content and activate tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function fillSampleData(type) {
            if (type === 'formidable') {
                document.getElementById('when_field').value = 'when they\'re struggling to create compelling content that converts';
                document.getElementById('what_field').value = 'develop a strong personal brand and create content that attracts their ideal clients';
                document.getElementById('how_field').value = 'through my proven storytelling framework and content strategy system';
                document.getElementById('where_field').value = 'featured in Forbes, entrepreneur.com, and spoken at 50+ industry events';
                document.getElementById('why_field').value = 'I believe every entrepreneur has a unique story that can transform their business when told correctly';
                
                alert('Sample Formidable data filled! You can now save it.');
            }
        }

        function showDebugInfo() {
            const debugDiv = document.getElementById('debug-info');
            if (debugDiv.style.display === 'none') {
                debugDiv.style.display = 'block';
            } else {
                debugDiv.style.display = 'none';
            }
        }

        // Auto-refresh page every 5 minutes to keep session active
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
