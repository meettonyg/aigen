// Test script to verify the Authority Hook field naming fix
(async function testAuthorityHookFieldFix() {
    console.log('🔧 Testing Authority Hook field naming fix...');
    
    // Get the current post ID
    const postId = window.currentPostId || 
                   document.querySelector('[data-post-id]')?.dataset.postId || 
                   new URLSearchParams(window.location.search).get('post_id') || 
                   '32372';
    
    console.log('📍 Using Post ID:', postId);
    
    try {
        // Test saving authority hook data with the same structure
        const testData = {
            post_id: postId,
            topics: {
                topic_1: 'FIELD FIX TEST - Topic 1 - ' + new Date().toLocaleTimeString(),
                topic_2: 'FIELD FIX TEST - Topic 2',
                topic_3: 'FIELD FIX TEST - Topic 3'
            },
            authority_hook: {
                who: 'Field Fix Test Users',
                what: 'verify the field naming fix',
                when: 'testing the corrected field mapping',
                how: 'by saving to the correct Pods fields'
            }
        };
        
        console.log('📤 Sending test data to verify field fix:', testData);
        
        const result = await makeAjaxRequest('mkcg_save_topics_data', testData);
        
        console.log('✅ AJAX request completed successfully');
        console.log('📥 Server response:', result);
        
        if (result.results) {
            console.log('💾 Save results:');
            console.log('  - Topics:', result.results.topics);
            console.log('  - Authority Hook:', result.results.authority_hook);
            
            if (result.results.authority_hook && result.results.authority_hook.success) {
                console.log('🎉 AUTHORITY HOOK FIELD FIX SUCCESSFUL!');
                console.log('✅ Authority Hook components should now save to the correct fields:');
                console.log('  - WHO component → guest_title field');
                console.log('  - WHAT component → hook_what field');
                console.log('  - WHEN component → hook_when field');
                console.log('  - HOW component → hook_how field');
                console.log('');
                console.log('📝 Check these fields in the WordPress post editor or Pods admin.');
            } else {
                console.log('⚠️ Authority Hook save reported as unsuccessful:', result.results.authority_hook);
            }
        }
        
        if (result.authority_hook_complete) {
            console.log('📝 Complete Authority Hook generated:', result.authority_hook_complete);
        }
        
        console.log('\n🔍 To verify the fix:');
        console.log('1. Go to WordPress admin → Posts → Edit post ' + postId);
        console.log('2. Check the Custom Fields section for:');
        console.log('   - guest_title (should contain "Field Fix Test Users")');
        console.log('   - hook_what (should contain "verify the field naming fix")');
        console.log('   - hook_when (should contain "testing the corrected field mapping")');
        console.log('   - hook_how (should contain "by saving to the correct Pods fields")');
        
    } catch (error) {
        console.error('❌ Test failed:', error);
        console.log('\n🔍 If the test fails:');
        console.log('1. Check the browser console for any JavaScript errors');
        console.log('2. Check the WordPress debug log for PHP errors');
        console.log('3. Verify the authority hook service file changes were saved correctly');
    }
})();
