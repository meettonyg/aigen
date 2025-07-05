<?php
/**
 * Quick Questions Generator Fix Validation
 * 
 * Drop this in browser console or run as PHP to quickly check if the fix is working
 * Usage: Access this file directly in browser or include in WordPress context
 */

// Quick WordPress detection
if (!defined('ABSPATH')) {
    // Try to load WordPress if not already loaded
    $wp_load_candidates = [
        '../../../wp-load.php',
        '../../../../wp-load.php', 
        '../../../../../wp-load.php'
    ];
    
    foreach ($wp_load_candidates as $candidate) {
        if (file_exists($candidate)) {
            require_once($candidate);
            break;
        }
    }
}

// If WordPress still not loaded, output JavaScript version
if (!defined('ABSPATH')) {
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html><head><title>Questions Fix Quick Check</title></head><body>
    <h2>Questions Generator Fix - JavaScript Validation</h2>
    <div id="results"></div>
    
    <script>
    // Quick JavaScript validation for browser console
    function quickQuestionsFixCheck() {
        const results = [];
        
        // Check if AJAX URL is available
        const ajaxUrl = window.ajaxurl || window.mkcg_vars?.ajax_url;
        results.push(ajaxUrl ? '✅ AJAX URL available' : '❌ AJAX URL missing');
        
        // Check if nonce is available
        const nonce = window.mkcg_vars?.nonce;
        results.push(nonce ? '✅ Nonce available' : '❌ Nonce missing');
        
        // Check for Questions Generator
        const questionsGen = window.QuestionsGenerator;
        results.push(questionsGen ? '✅ QuestionsGenerator loaded' : '❌ QuestionsGenerator missing');
        
        // Check for standardized data
        const topicsData = window.MKCG_Topics_Data;
        results.push(topicsData ? '✅ MKCG_Topics_Data available' : '❌ MKCG_Topics_Data missing');
        
        return results;
    }
    
    // Run check and display results
    const checkResults = quickQuestionsFixCheck();
    document.getElementById('results').innerHTML = '<ul><li>' + checkResults.join('</li><li>') + '</li></ul>';
    
    console.log('🔧 Questions Generator Fix Quick Check:');
    checkResults.forEach(result => console.log(result));
    
    // Test AJAX endpoint
    if (window.ajaxurl && window.mkcg_vars?.nonce) {
        console.log('🧪 Testing AJAX endpoint...');
        
        fetch(window.ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mkcg_save_questions&nonce=' + window.mkcg_vars.nonce + '&post_id=1&questions=' + encodeURIComponent('{"1":{"1":"test"}}')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === false && data.data && data.data.message === 'Post ID required') {
                console.log('❌ AJAX endpoint reached but Post ID validation failed (this is normal for test)');
            } else if (data.success === false && data.data && data.data.message.includes('Security check failed')) {
                console.log('❌ AJAX endpoint reached but security check failed (this is normal for test)');
            } else {
                console.log('✅ AJAX endpoint responding correctly:', data);
            }
        })
        .catch(error => console.log('❌ AJAX endpoint error:', error));
    }
    </script>
    </body></html>
    <?php
    exit;
}

// WordPress is loaded - run PHP validation
?>
<!DOCTYPE html>
<html>
<head>
    <title>Questions Generator Fix - Quick Validation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; }
        .result { padding: 10px; margin: 5px 0; border-radius: 4px; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔧 Questions Generator Fix - Quick Validation</h1>
    <p><strong>Test Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php
    $checks = [];

    // Check 1: Plugin Class
    if (class_exists('Media_Kit_Content_Generator')) {
        $checks[] = ['✅ Main plugin class loaded', 'pass'];
        
        // Check 2: AJAX Actions
        global $wp_filter;
        $required_actions = ['wp_ajax_mkcg_save_questions', 'wp_ajax_mkcg_generate_questions'];
        $registered_count = 0;
        
        foreach ($required_actions as $action) {
            if (isset($wp_filter[$action]) && !empty($wp_filter[$action])) {
                $registered_count++;
            }
        }
        
        if ($registered_count === count($required_actions)) {
            $checks[] = ['✅ All AJAX handlers registered (' . $registered_count . '/' . count($required_actions) . ')', 'pass'];
        } else {
            $checks[] = ['❌ Missing AJAX handlers (' . $registered_count . '/' . count($required_actions) . ')', 'fail'];
        }
        
    } else {
        $checks[] = ['❌ Main plugin class not loaded', 'fail'];
    }

    // Check 3: Questions Generator Class
    if (class_exists('Enhanced_Questions_Generator')) {
        $checks[] = ['✅ Questions Generator class loaded', 'pass'];
    } else {
        $checks[] = ['❌ Questions Generator class not loaded', 'fail'];
    }

    // Check 4: Test post creation capability
    if (function_exists('wp_insert_post')) {
        $checks[] = ['✅ WordPress post functions available', 'pass'];
    } else {
        $checks[] = ['❌ WordPress post functions not available', 'fail'];
    }

    // Display results
    foreach ($checks as $check) {
        echo '<div class="result ' . $check[1] . '">' . $check[0] . '</div>';
    }

    // Overall assessment
    $passed = count(array_filter($checks, function($c) { return $c[1] === 'pass'; }));
    $total = count($checks);
    $rate = round(($passed / $total) * 100);

    echo '<div class="result ' . ($rate >= 75 ? 'pass' : ($rate >= 50 ? 'info' : 'fail')) . '">';
    echo '<strong>Overall: ' . $rate . '% checks passed (' . $passed . '/' . $total . ')</strong>';
    echo '</div>';

    if ($rate >= 75) {
        echo '<div class="result pass"><strong>🎉 Questions Generator fix appears to be working!</strong></div>';
        echo '<div class="result info">Next step: Test with actual Questions Generator form using ?post_id=XXXXX</div>';
    } else {
        echo '<div class="result fail"><strong>❌ Issues detected - check WordPress error logs</strong></div>';
    }
    ?>

    <h3>Test Instructions:</h3>
    <ol>
        <li>Go to a Questions Generator page</li>
        <li>Add <code>?post_id=XXXXX</code> to the URL (use a valid guests post ID)</li>
        <li>Add some questions</li>
        <li>Click "Save All Questions"</li>
        <li>Should see success message instead of "Post ID required" error</li>
    </ol>

    <h3>Debug Commands:</h3>
    <p>Run in browser console on Questions Generator page:</p>
    <code>
        window.QuestionsGenerator.saveAllQuestions();<br>
        console.log(window.MKCG_Topics_Data);<br>
        console.log(window.mkcg_vars);
    </code>

</body>
</html>
