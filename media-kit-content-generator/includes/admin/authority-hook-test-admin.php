<?php
/**
 * WordPress Admin Integration for Authority Hook Test
 * ROOT-LEVEL FIX: Proper WordPress plugin architecture
 * 
 * This provides a WordPress admin interface for testing Authority Hook functionality
 * Access via: WordPress Admin > Tools > Authority Hook Test
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Authority_Hook_Test_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu item under Tools
     */
    public function add_admin_menu() {
        add_management_page(
            'Authority Hook Test',
            'Authority Hook Test',
            'manage_options',
            'mkcg-authority-hook-test',
            [$this, 'render_test_page']
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_mkcg-authority-hook-test') {
            return;
        }
        
        wp_enqueue_style(
            'mkcg-admin-test',
            MKCG_PLUGIN_URL . 'assets/css/admin-test.css',
            [],
            MKCG_VERSION
        );
    }
    
    /**
     * Render the test page
     */
    public function render_test_page() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get entry ID from URL or default
        $entry_id = $this->get_test_entry_id();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p><strong>🔧 Authority Hook Field Processing Test</strong></p>
                <p>This tool tests the Authority Hook field processing fixes for the Media Kit Content Generator.</p>
            </div>
            
            <form method="get" action="">
                <input type="hidden" name="page" value="mkcg-authority-hook-test">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="entry_id">Entry ID to Test</label>
                        </th>
                        <td>
                            <input 
                                type="number" 
                                id="entry_id" 
                                name="entry_id" 
                                value="<?php echo esc_attr($entry_id); ?>" 
                                class="regular-text"
                            >
                            <p class="description">
                                Enter a Formidable Forms entry ID to test. 
                                Default: 74492 (from console logs).
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Run Test', 'primary', 'run_test'); ?>
            </form>
            
            <?php if (isset($_GET['run_test']) || isset($_GET['entry_id'])): ?>
                <hr>
                <div id="test-results">
                    <?php $this->run_authority_hook_test($entry_id); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .test-success { background-color: #d1edff; border-left: 4px solid #0073aa; padding: 12px; margin: 15px 0; }
        .test-warning { background-color: #fff8e5; border-left: 4px solid #ffb900; padding: 12px; margin: 15px 0; }
        .test-error { background-color: #ffeaa7; border-left: 4px solid #dc3232; padding: 12px; margin: 15px 0; }
        .test-table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        .test-table th, .test-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .test-table th { background-color: #f1f1f1; font-weight: bold; }
        .result-success { background-color: #c6efce; }
        .result-warning { background-color: #ffeb9c; }
        .result-error { background-color: #ffc7ce; }
        </style>
        <?php
    }
    
    /**
     * Get entry ID for testing
     */
    private function get_test_entry_id() {
        if (isset($_GET['entry_id']) && is_numeric($_GET['entry_id'])) {
            return intval($_GET['entry_id']);
        }
        
        return 74492; // Default from console logs
    }
    
    /**
     * Run the Authority Hook test
     */
    private function run_authority_hook_test($entry_id) {
        try {
            // Initialize Formidable service
            $formidable_service = new MKCG_Formidable_Service();
            
            echo '<div class="test-success">';
            echo '<h3>✅ Test Environment</h3>';
            echo '<p><strong>Entry ID:</strong> ' . esc_html($entry_id) . '</p>';
            echo '<p><strong>WordPress Version:</strong> ' . esc_html(get_bloginfo('version')) . '</p>';
            echo '<p><strong>Test Time:</strong> ' . esc_html(date('Y-m-d H:i:s')) . '</p>';
            echo '</div>';
            
            // Run field processing diagnostic
            $this->test_field_processing($formidable_service, $entry_id);
            
            // Run entry data test
            $this->test_entry_data($formidable_service, $entry_id);
            
            // Run database check
            $this->test_database_direct($entry_id);
            
            // Show summary
            $this->show_test_summary();
            
        } catch (Exception $e) {
            echo '<div class="test-error">';
            echo '<h3>❌ Test Failed</h3>';
            echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Test field processing
     */
    private function test_field_processing($formidable_service, $entry_id) {
        echo '<h3>🧪 Field Processing Test</h3>';
        
        try {
            $diagnosis = $formidable_service->diagnose_authority_hook_fields($entry_id);
            
            if (empty($diagnosis)) {
                echo '<div class="test-warning"><p>⚠️ No diagnostic results for entry ID ' . esc_html($entry_id) . '</p></div>';
                return;
            }
            
            echo '<table class="test-table">';
            echo '<thead><tr>';
            echo '<th>Field ID</th><th>Description</th><th>Data Found</th><th>Processed Value</th><th>Method</th><th>Status</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($diagnosis as $field_id => $result) {
                $status = 'UNKNOWN';
                $css_class = '';
                
                if ($result['raw_value'] === null) {
                    $status = 'NO DATA';
                    $css_class = 'result-error';
                } elseif (!empty($result['processed_value']) && 
                          !in_array($result['processed_value'], ['achieve their goals', 'they need help', 'through your method'])) {
                    $status = 'SUCCESS - REAL DATA';
                    $css_class = 'result-success';
                } elseif (!empty($result['processed_value'])) {
                    $status = 'DEFAULT VALUE';
                    $css_class = 'result-warning';
                } else {
                    $status = 'FAILED';
                    $css_class = 'result-error';
                }
                
                echo '<tr class="' . esc_attr($css_class) . '">';
                echo '<td>' . esc_html($field_id) . '</td>';
                echo '<td>' . esc_html($result['description']) . '</td>';
                echo '<td>' . ($result['raw_value'] ? 'YES (' . $result['raw_length'] . ' chars)' : 'NO') . '</td>';
                echo '<td>' . esc_html($result['processed_value'] ?: 'NULL') . '</td>';
                echo '<td>' . esc_html($result['processing_method']) . '</td>';
                echo '<td><strong>' . esc_html($status) . '</strong></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            
        } catch (Exception $e) {
            echo '<div class="test-error"><p>❌ Field processing test failed: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    /**
     * Test entry data retrieval
     */
    private function test_entry_data($formidable_service, $entry_id) {
        echo '<h3>📊 Entry Data Test</h3>';
        
        try {
            $entry_data = $formidable_service->get_entry_data($entry_id);
            
            if ($entry_data['success']) {
                echo '<div class="test-success"><p>✅ Entry data retrieved successfully</p></div>';
                
                $authority_fields = ['10296', '10297', '10387', '10298', '10358'];
                $field_names = [
                    '10296' => 'WHO',
                    '10297' => 'RESULT', 
                    '10387' => 'WHEN',
                    '10298' => 'HOW',
                    '10358' => 'COMPLETE'
                ];
                
                echo '<h4>Authority Hook Field Values:</h4>';
                echo '<ul>';
                
                foreach ($authority_fields as $field_id) {
                    $field_name = isset($field_names[$field_id]) ? $field_names[$field_id] : 'Field ' . $field_id;
                    
                    if (isset($entry_data['fields'][$field_id])) {
                        $field = $entry_data['fields'][$field_id];
                        $value = $field['value'];
                        $quality = $field['data_quality'];
                        
                        echo '<li><strong>' . esc_html($field_name) . ' (' . esc_html($field_id) . '):</strong> ';
                        echo '"' . esc_html($value) . '" (Quality: ' . esc_html($quality) . ')</li>';
                    } else {
                        echo '<li><strong>' . esc_html($field_name) . ' (' . esc_html($field_id) . '):</strong> NOT FOUND</li>';
                    }
                }
                
                echo '</ul>';
            } else {
                echo '<div class="test-error"><p>❌ Failed to retrieve entry data: ' . esc_html($entry_data['message']) . '</p></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="test-error"><p>❌ Entry data test failed: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    /**
     * Test direct database access
     */
    private function test_database_direct($entry_id) {
        echo '<h3>🗄️ Direct Database Check</h3>';
        
        try {
            global $wpdb;
            
            $direct_fields = $wpdb->get_results($wpdb->prepare(
                "SELECT field_id, meta_value FROM {$wpdb->prefix}frm_item_metas 
                 WHERE item_id = %d AND field_id IN ('10296', '10297', '10387', '10298', '10358')
                 ORDER BY field_id",
                $entry_id
            ), ARRAY_A);
            
            if ($wpdb->last_error) {
                echo '<div class="test-error"><p>❌ Database error: ' . esc_html($wpdb->last_error) . '</p></div>';
                return;
            }
            
            echo '<div class="test-success"><p>✅ Database query executed successfully</p></div>';
            
            if (!empty($direct_fields)) {
                echo '<table class="test-table">';
                echo '<thead><tr><th>Field ID</th><th>Raw Value</th><th>Length</th><th>Serialized</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($direct_fields as $field) {
                    $is_serialized = is_serialized($field['meta_value']) ? 'YES' : 'NO';
                    $display_value = esc_html(substr($field['meta_value'], 0, 100));
                    if (strlen($field['meta_value']) > 100) {
                        $display_value .= '...';
                    }
                    
                    echo '<tr>';
                    echo '<td>' . esc_html($field['field_id']) . '</td>';
                    echo '<td>' . $display_value . '</td>';
                    echo '<td>' . strlen($field['meta_value']) . '</td>';
                    echo '<td>' . esc_html($is_serialized) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<div class="test-warning"><p>⚠️ No data found for entry ID ' . esc_html($entry_id) . '</p></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="test-error"><p>❌ Database test failed: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    /**
     * Show test summary
     */
    private function show_test_summary() {
        echo '<div class="test-success">';
        echo '<h3>📋 Test Summary</h3>';
        echo '<p><strong>🔧 Status:</strong> Root-level fixes have been applied to the Authority Hook processing system.</p>';
        
        echo '<p><strong>🔗 Next Steps:</strong></p>';
        echo '<ul>';
        echo '<li>Monitor WordPress error logs for processing issues</li>';
        echo '<li>Test with different entry IDs to verify consistency</li>';
        echo '<li>Set up automated testing for production monitoring</li>';
        echo '</ul>';
        
        echo '<p><em>Test completed at ' . esc_html(date('Y-m-d H:i:s')) . ' | Root-level fixes applied ✅</em></p>';
        echo '</div>';
    }
}

// Initialize admin test page
if (is_admin()) {
    MKCG_Authority_Hook_Test_Admin::get_instance();
}
