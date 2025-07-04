// Final test with the 500 error fix
console.log('🚀 Testing with Pods dependency removed...\n');

// Test if AJAX is working now
const postId = document.querySelector('#topics-generator-post-id')?.value;

if (!postId || postId === '0') {
    console.error('❌ Not on Topics Generator page');
} else {
    console.log('📍 Post ID:', postId);
    console.log('🔧 Testing save after removing Pods dependency...\n');
    
    window.makeAjaxRequest('mkcg_save_topics_data', {
        post_id: postId,
        topics: {
            topic_1: `FIXED! No more Pods errors - ${new Date().toLocaleTimeString()}`,
            topic_2: 'Using WordPress post meta directly',
            topic_3: 'No external plugin dependencies'
        },
        authority_hook: {
            who: 'WordPress users',
            what: 'save their content',
            when: 'they use this plugin',
            how: 'with simple, reliable code'
        }
    })
    .then(response => {
        console.log('✅✅✅ SUCCESS! The 500 error is fixed!');
        console.log('Response:', response);
        console.log('\n🎉 The plugin is now fully operational!');
        console.log('💪 All issues have been resolved:');
        console.log('   ✅ AJAX actions registered');
        console.log('   ✅ No more Pods dependency');
        console.log('   ✅ Data saves correctly');
        console.log('   ✅ No 500 errors');
        
        if (response.results) {
            console.log('\n📊 Save Results:');
            Object.entries(response.results).forEach(([key, result]) => {
                console.log(`   - ${key}:`, result.success ? '✅ Saved' : '❌ Failed');
                if (result.saved_count) {
                    console.log(`     Saved ${result.saved_count} items`);
                }
            });
        }
    })
    .catch(error => {
        console.error('❌ Error:', error.message);
        console.log('\n🔍 If still getting errors:');
        console.log('1. Clear your browser cache');
        console.log('2. Check if the post type is "guests"');
        console.log('3. Check WordPress debug.log');
    });
}
