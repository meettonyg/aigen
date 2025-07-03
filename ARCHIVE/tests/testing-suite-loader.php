<?php
/**
 * MKCG Testing Suite Loader
 * Add this to your WordPress site to load the testing scripts
 */

// Method 1: Add to your theme's functions.php file
function mkcg_load_testing_suite() {
    // Only load on pages where you want to test (adjust the condition as needed)
    if (is_page() || is_admin()) {
        $plugin_url = plugin_dir_url(__FILE__);
        $testing_path = $plugin_url . 'testing/';
        
        // Load all test suite scripts
        wp_enqueue_script(
            'mkcg-comprehensive-test-suite',
            $testing_path . 'comprehensive-test-suite.js',
            array(),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'mkcg-performance-benchmark',
            $testing_path . 'performance-benchmark.js',
            array(),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'mkcg-code-metrics',
            $testing_path . 'code-metrics-analyzer.js',
            array(),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'mkcg-deployment-readiness',
            $testing_path . 'deployment-readiness-checklist.js',
            array(),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'mkcg_load_testing_suite');

// Method 2: Add to your MKCG plugin file (recommended)
function mkcg_add_testing_capabilities() {
    // Only load in development or when ?test=1 parameter is present
    if (defined('WP_DEBUG') && WP_DEBUG || isset($_GET['test'])) {
        $plugin_dir = plugin_dir_url(__FILE__);
        
        wp_enqueue_script('mkcg-test-comprehensive', $plugin_dir . 'testing/comprehensive-test-suite.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-test-performance', $plugin_dir . 'testing/performance-benchmark.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-test-metrics', $plugin_dir . 'testing/code-metrics-analyzer.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-test-deployment', $plugin_dir . 'testing/deployment-readiness-checklist.js', array(), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'mkcg_add_testing_capabilities');

// Method 3: Create a dedicated testing page template
function mkcg_create_testing_page() {
    // Create a page specifically for testing
    $page = array(
        'post_title'    => 'MKCG Testing Suite',
        'post_content'  => '[mkcg_topics] [mkcg_questions]', // Include your generators
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'mkcg-testing'
    );
    
    // Only create if it doesn't exist
    if (!get_page_by_path('mkcg-testing')) {
        wp_insert_post($page);
    }
}
// Call this once to create the testing page
// mkcg_create_testing_page();

// Method 4: Add testing capability to MKCG plugin activation
function mkcg_testing_on_activation() {
    // Add option to enable testing mode
    add_option('mkcg_testing_enabled', false);
}
register_activation_hook(__FILE__, 'mkcg_testing_on_activation');

function mkcg_conditional_testing_load() {
    if (get_option('mkcg_testing_enabled')) {
        // Load testing scripts when enabled via admin
        $plugin_url = plugin_dir_url(__FILE__);
        wp_enqueue_script('mkcg-testing-suite', $plugin_url . 'testing/comprehensive-test-suite.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-testing-performance', $plugin_url . 'testing/performance-benchmark.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-testing-metrics', $plugin_url . 'testing/code-metrics-analyzer.js', array(), '1.0', true);
        wp_enqueue_script('mkcg-testing-deployment', $plugin_url . 'testing/deployment-readiness-checklist.js', array(), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'mkcg_conditional_testing_load');

// Method 5: Admin interface to enable testing
function mkcg_testing_admin_menu() {
    add_options_page(
        'MKCG Testing Suite',
        'MKCG Testing',
        'manage_options',
        'mkcg-testing',
        'mkcg_testing_admin_page'
    );
}
add_action('admin_menu', 'mkcg_testing_admin_menu');

function mkcg_testing_admin_page() {
    if (isset($_POST['enable_testing'])) {
        update_option('mkcg_testing_enabled', true);
        echo '<div class="notice notice-success"><p>Testing suite enabled!</p></div>';
    }
    
    if (isset($_POST['disable_testing'])) {
        update_option('mkcg_testing_enabled', false);
        echo '<div class="notice notice-success"><p>Testing suite disabled.</p></div>';
    }
    
    $testing_enabled = get_option('mkcg_testing_enabled');
    ?>
    <div class="wrap">
        <h1>MKCG Testing Suite</h1>
        <p>Current status: <strong><?php echo $testing_enabled ? 'ENABLED' : 'DISABLED'; ?></strong></p>
        
        <form method="post">
            <?php if (!$testing_enabled): ?>
                <input type="submit" name="enable_testing" class="button button-primary" value="Enable Testing Suite">
            <?php else: ?>
                <input type="submit" name="disable_testing" class="button" value="Disable Testing Suite">
            <?php endif; ?>
        </form>
        
        <?php if ($testing_enabled): ?>
        <h2>Testing Instructions</h2>
        <ol>
            <li><strong>Go to a page with MKCG generators</strong> (Topics or Questions)</li>
            <li><strong>Open Browser DevTools</strong> (F12 key)</li>
            <li><strong>Click Console tab</strong></li>
            <li><strong>Run test commands:</strong>
                <ul>
                    <li><code>quickTestMKCG()</code> - Basic functionality check</li>
                    <li><code>runComprehensiveTestSuite()</code> - Full test suite</li>
                    <li><code>runPerformanceBenchmark()</code> - Performance analysis</li>
                    <li><code>runDeploymentReadinessCheck()</code> - Deployment validation</li>
                </ul>
            </li>
        </ol>
        
        <h2>Quick Test URLs</h2>
        <p>Visit these pages to run tests:</p>
        <ul>
            <li><a href="<?php echo site_url('/your-topics-page/'); ?>" target="_blank">Topics Generator Test Page</a></li>
            <li><a href="<?php echo site_url('/your-questions-page/'); ?>" target="_blank">Questions Generator Test Page</a></li>
        </ul>
        <?php endif; ?>
    </div>
    <?php
}
?>
