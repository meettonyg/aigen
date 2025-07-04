// Test script to verify the AJAX fix
(async function testAjaxFix() {
    console.log('🧪 Testing AJAX fix for stripslashes() error...');
    
    // Get the current post ID
    const postId = window.currentPostId || 
                   document.querySelector('[data-post-id]')?.dataset.postId || 
                   new URLSearchParams(window.location.search).get('post_id') || 
                   '32372';
    
    console.log('📍 Using Post ID:', postId);
    
    try {
        // Test saving topics with the same data structure that was causing the error
        const testData = {
            post_id: postId,
            topics: {
                topic_1: 'AJAX Fix Test - Topic 1 - ' + new Date().toLocaleTimeString(),
                topic_2: 'AJAX Fix Test - Topic 2',
                topic_3: 'AJAX Fix Test - Topic 3'
            },
            authority_hook: {
                who: 'Test Users',
                what: 'test the AJAX fix',
                when: 'running this test',
                how: 'by sending array data'
            }
        };
        
        console.log('📤 Sending test data:', testData);
        
        const result = await makeAjaxRequest('mkcg_save_topics_data', testData);
        
        console.log('✅ SUCCESS! AJAX request completed without 500 error');
        console.log('📥 Server response:', result);
        
        if (result.results) {
            console.log('💾 Save results:', {
                topics: result.results.topics,
                authority_hook: result.results.authority_hook
            });
        }
        
        console.log('\n🎉 The stripslashes() error has been fixed!');
        console.log('The PHP code now properly handles array data.');
        
    } catch (error) {
        console.error('❌ Test failed:', error);
        console.log('\n⚠️ If you still get a 500 error:');
        console.log('1. Make sure you saved the PHP file changes');
        console.log('2. Clear any PHP opcache if enabled');
        console.log('3. Check the error log for new error messages');
    }
})();
