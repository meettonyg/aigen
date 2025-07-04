// FINAL TEST - Topics Generator Save Functionality
console.log('🚀 FINAL TEST - Topics Generator Save\n');

// Wait a moment for any async scripts to load
setTimeout(() => {
    console.log('=== Checking Configuration ===');
    console.log('AJAX URL:', window.ajaxurl || 'NOT SET');
    console.log('Nonce available:', window.mkcg_vars?.nonce ? 'YES' : 'NO');
    console.log('makeAjaxRequest available:', typeof window.makeAjaxRequest === 'function' ? 'YES' : 'NO');
    
    const postId = document.querySelector('#topics-generator-post-id')?.value;
    console.log('Post ID:', postId || 'NOT FOUND');
    
    if (!postId || postId === '0') {
        console.error('\n❌ ERROR: Not on Topics Generator page or no valid post ID');
        console.log('Please navigate to a Topics Generator page with a valid post.');
        return;
    }
    
    if (typeof window.makeAjaxRequest !== 'function') {
        console.error('\n❌ ERROR: makeAjaxRequest function not available');
        console.log('The simple-ajax.js script may not be loaded.');
        return;
    }
    
    console.log('\n=== Running Save Test ===');
    
    const testData = {
        post_id: postId,
        topics: {
            topic_1: `FINAL TEST: ${new Date().toLocaleString()}`,
            topic_2: 'Building Authority Through Content',
            topic_3: 'Strategic Business Growth'
        },
        authority_hook: {
            who: 'entrepreneurs and business leaders',
            what: 'scale their impact',
            when: 'they are ready to level up',
            how: 'through proven systems and strategies'
        }
    };
    
    console.log('Sending test data...');
    
    window.makeAjaxRequest('mkcg_save_topics_data', testData)
        .then(response => {
            console.log('\n✅ SUCCESS! Data saved successfully!');
            console.log('Server response:', response);
            
            if (response.results) {
                console.log('\nSave results:');
                if (response.results.topics) {
                    console.log('- Topics:', response.results.topics.success ? '✅ Saved' : '❌ Failed');
                }
                if (response.results.authority_hook) {
                    console.log('- Authority Hook:', response.results.authority_hook.success ? '✅ Saved' : '❌ Failed');
                }
            }
            
            console.log('\n🎉 The Topics Generator save functionality is now working!');
            console.log('💡 Try refreshing the page to verify the data persists.');
            
            // Optional: Load the data back to verify
            return window.makeAjaxRequest('mkcg_get_topics_data', { post_id: postId });
        })
        .then(loadResponse => {
            if (loadResponse) {
                console.log('\n=== Verification: Data Loaded Back ===');
                console.log('Topics:', loadResponse.topics);
                console.log('Authority Hook:', loadResponse.authority_hook_components);
            }
        })
        .catch(error => {
            console.error('\n❌ ERROR:', error.message || error);
            
            if (error.message && error.message.includes('Security check failed')) {
                console.log('\n🔧 NONCE TROUBLESHOOTING:');
                console.log('1. Refresh the page to get a fresh nonce');
                console.log('2. Make sure you are logged in as an admin/editor');
                console.log('3. Clear browser cache and cookies');
                console.log('4. Check browser console for any other errors');
                console.log('\nCurrent nonce:', window.mkcg_vars?.nonce ? 'Present' : 'Missing');
            } else if (error.message && error.message.includes('No data provided')) {
                console.log('\n🔧 DATA TROUBLESHOOTING:');
                console.log('The data serialization issue persists.');
                console.log('Check wp-content/debug.log for PHP error details.');
            }
            
            console.log('\n📋 Debug info:');
            console.log('- Plugin version: 1.0.0');
            console.log('- User logged in:', document.body.classList.contains('logged-in'));
            console.log('- Admin bar visible:', document.getElementById('wpadminbar') !== null);
        });
        
}, 500); // Small delay to ensure scripts are loaded

console.log('Test will run in 500ms...');
