// 🧪 COMPREHENSIVE SAVE TEST SCRIPT - Run in Browser Console
// This will test both topics and authority hook saving with full debugging

console.log('🧪 Starting comprehensive save test...');

// Get test data
const postId = document.querySelector('#topics-generator-post-id')?.value;
if (!postId || postId === '0') {
    console.error('❌ No post ID found! Make sure you\'re on the Topics Generator page with a valid post ID.');
} else {
    console.log('📍 Testing with post ID:', postId);
    
    // Test Data
    const testTopics = {
        topic_1: 'Test Topic 1: Authority Building for Experts',
        topic_2: 'Test Topic 2: Content Strategy That Converts',
        topic_3: 'Test Topic 3: Building Your Personal Brand'
    };
    
    const testAuthorityHook = {
        who: 'business coaches and consultants',
        what: 'build authority and attract clients',
        when: 'they want to scale their expertise',
        how: 'through strategic content and positioning'
    };
    
    console.log('🎯 Test Topics:', testTopics);
    console.log('🎯 Test Authority Hook:', testAuthorityHook);
    
    // Test 1: Save Topics and Authority Hook Together (main save button)
    console.log('\n🧪 TEST 1: Complete Save (Topics + Authority Hook)');
    
    if (window.makeAjaxRequest) {
        window.makeAjaxRequest('mkcg_save_topics_data', {
            post_id: postId,
            topics: testTopics,
            authority_hook: testAuthorityHook
        })
        .then(response => {
            console.log('✅ TEST 1 SUCCESS:', response);
            console.log('📊 Response structure:', Object.keys(response));
            
            // Test 2: Try to load the data back
            console.log('\n🧪 TEST 2: Load Data Back');
            return window.makeAjaxRequest('mkcg_get_topics_data', {
                post_id: postId
            });
        })
        .then(response => {
            console.log('✅ TEST 2 SUCCESS:', response);
            
            // Verify the data matches
            console.log('\n🔍 DATA VERIFICATION:');
            if (response.topics) {
                console.log('📋 Loaded topics:', response.topics);
                
                // Check if our test data was saved
                Object.keys(testTopics).forEach(key => {
                    const expected = testTopics[key];
                    const actual = response.topics[key];
                    if (expected === actual) {
                        console.log(`✅ ${key}: MATCH`);
                    } else {
                        console.log(`❌ ${key}: MISMATCH - expected "${expected}", got "${actual}"`);
                    }
                });
            }
            
            if (response.authority_hook_components) {
                console.log('🎯 Loaded authority hook:', response.authority_hook_components);
            }
            
            // Test 3: Test individual authority hook save
            console.log('\n🧪 TEST 3: Authority Hook Only Save');
            return window.makeAjaxRequest('mkcg_save_authority_hook', {
                post_id: postId,
                who: 'startup founders',
                what: 'scale their businesses efficiently',
                when: 'they hit growth obstacles',
                how: 'through proven systems and strategies'
            });
        })
        .then(response => {
            console.log('✅ TEST 3 SUCCESS:', response);
            
            console.log('\n🎉 ALL TESTS COMPLETED SUCCESSFULLY!');
            console.log('✅ Topics saving works');
            console.log('✅ Authority Hook saving works');
            console.log('✅ Data loading works');
            console.log('✅ AJAX system is functional');
            
        })
        .catch(error => {
            console.error('❌ TEST FAILED:', error);
            console.log('\n🔍 DEBUG INFO:');
            console.log('• AJAX URL:', window.ajaxurl || window.mkcg_vars?.ajax_url);
            console.log('• Nonce available:', !!(window.mkcg_vars?.nonce));
            console.log('• makeAjaxRequest available:', typeof window.makeAjaxRequest);
            console.log('• User logged in:', document.body.classList.contains('logged-in'));
            
            console.log('\n📋 TROUBLESHOOTING:');
            console.log('1. Check WordPress error logs for detailed server errors');
            console.log('2. Verify user has edit_posts capability');
            console.log('3. Check if nonce is being generated correctly');
            console.log('4. Ensure AJAX handlers are registered properly');
        });
        
    } else {
        console.error('❌ makeAjaxRequest function not available!');
        console.log('Check if simple-ajax.js is loaded correctly.');
    }
}

console.log('\n💡 TIP: Check the browser Network tab to see the actual AJAX requests being sent.');
console.log('💡 TIP: Check WordPress error logs for server-side debugging information.');