// Quick Test for Topics Generator Save Fix
// Copy and paste this entire block into your browser console

(function() {
    console.log('🚀 Testing Topics Generator Save Fix...\n');
    
    // Check if we're on the right page
    const postId = document.querySelector('#topics-generator-post-id')?.value;
    if (!postId || postId === '0') {
        console.error('❌ ERROR: Not on Topics Generator page or no post ID found');
        console.log('Make sure you are on the Topics Generator page with a valid post');
        return;
    }
    
    console.log('✅ Found post ID:', postId);
    
    // Test data
    const testData = {
        post_id: postId,
        topics: {
            topic_1: 'Test Topic 1: ' + new Date().toLocaleTimeString(),
            topic_2: 'Test Topic 2: Building Authority',
            topic_3: 'Test Topic 3: Content Strategy'
        },
        authority_hook: {
            who: 'entrepreneurs',
            what: 'scale their business',
            when: 'they hit a growth plateau',
            how: 'through strategic systems'
        }
    };
    
    console.log('📊 Sending test data...');
    console.log(testData);
    
    // Make the request
    window.makeAjaxRequest('mkcg_save_topics_data', testData)
        .then(response => {
            console.log('\n✅ SUCCESS! Data saved successfully');
            console.log('Response:', response);
            console.log('\n🎉 The save functionality is now working!');
            console.log('Try refreshing the page to verify the data persists.');
        })
        .catch(error => {
            console.error('\n❌ FAILED:', error.message);
            console.log('\n🔍 Troubleshooting:');
            console.log('1. Check WordPress debug.log for PHP errors');
            console.log('2. Check Network tab in DevTools');
            console.log('3. Make sure you are logged in with edit_posts capability');
            console.log('4. Try running: window.makeAjaxRequest (should be a function)');
        });
})();
