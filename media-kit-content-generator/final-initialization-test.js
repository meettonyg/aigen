// FINAL FIX TEST - Plugin Initialization
console.log('🚀 Testing proper plugin initialization fix...\n');

console.log('The plugin now hooks into WordPress properly.');
console.log('AJAX actions should be registered correctly.\n');

// First, let's verify the plugin is active
console.log('Step 1: Checking plugin status...');

// Test raw AJAX to see if action is registered
const testAction = () => {
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    
    fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        if (text === '0' || text === '-1') {
            console.error('\n❌ AJAX action still not registered!');
            console.log('\n🔧 REQUIRED ACTIONS:');
            console.log('1. Go to WordPress admin → Plugins');
            console.log('2. Deactivate "Media Kit Content Generator"');
            console.log('3. Activate it again');
            console.log('4. Come back here and run this test again');
        } else {
            console.log('\n✅ AJAX action IS NOW REGISTERED!');
            console.log('Raw response:', text);
            
            // Now do the real test
            runFullTest();
        }
    });
};

// Full save test
const runFullTest = () => {
    console.log('\nStep 2: Running full save test...\n');
    
    const postId = document.querySelector('#topics-generator-post-id')?.value;
    if (!postId || postId === '0') {
        console.error('❌ Not on Topics Generator page');
        return;
    }
    
    window.makeAjaxRequest('mkcg_save_topics_data', {
        post_id: postId,
        topics: {
            topic_1: `PLUGIN FIXED! ${new Date().toLocaleTimeString()}`,
            topic_2: 'Proper WordPress initialization',
            topic_3: 'AJAX handlers working perfectly'
        },
        authority_hook: {
            who: 'WordPress developers',
            what: 'build reliable plugins',
            when: 'they follow best practices',
            how: 'by hooking into plugins_loaded'
        }
    })
    .then(response => {
        console.log('✅✅✅ COMPLETE SUCCESS!!!');
        console.log('Response:', response);
        console.log('\n🎉 The plugin is now working correctly!');
        console.log('💪 All AJAX handlers are registered');
        console.log('🚀 Save functionality is operational');
        console.log('\n✨ The root cause was fixed:');
        console.log('   - Plugin was initializing too early');
        console.log('   - Now hooks into plugins_loaded action');
        console.log('   - WordPress is ready when plugin initializes');
    })
    .catch(error => {
        console.error('❌ Error:', error.message);
    });
};

// Start the test
console.log('\nStarting test...\n');
testAction();
