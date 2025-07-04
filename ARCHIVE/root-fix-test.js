// FINAL ROOT-LEVEL FIX TEST
console.log('🚀 Testing root-level AJAX fix...\n');

// Wait for scripts to load
setTimeout(() => {
    console.log('=== Pre-flight Check ===');
    console.log('✅ AJAX URL:', window.ajaxurl);
    console.log('✅ Nonce:', window.mkcg_vars?.nonce ? 'Available' : 'Missing');
    console.log('✅ makeAjaxRequest:', typeof window.makeAjaxRequest === 'function' ? 'Ready' : 'Not found');
    
    const postId = document.querySelector('#topics-generator-post-id')?.value;
    console.log('✅ Post ID:', postId || 'Not found');
    
    if (!postId || postId === '0') {
        console.error('\n❌ Not on Topics Generator page');
        return;
    }
    
    console.log('\n=== Running Save Test ===');
    
    window.makeAjaxRequest('mkcg_save_topics_data', {
        post_id: postId,
        topics: {
            topic_1: `ROOT FIX WORKS! ${new Date().toLocaleTimeString()}`
        },
        authority_hook: {
            who: 'everyone using this plugin'
        }
    })
    .then(response => {
        console.log('\n✅✅✅ SUCCESS! Save is working!');
        console.log('Response:', response);
        console.log('\n🎉 The root-level fix resolved all issues!');
        console.log('💪 No more 400 errors, no more security check failures!');
    })
    .catch(error => {
        console.error('\n❌ Error:', error.message);
        
        if (error.message.includes('400')) {
            console.log('\n🔧 Still getting 400 error. Please:');
            console.log('1. Clear all caches (browser, WordPress, CDN)');
            console.log('2. Refresh the page');
            console.log('3. Check WordPress debug.log');
        }
    });
    
}, 1000);

console.log('Test will run in 1 second...');
