console.log('🧪 Authority Hook Save Test - Starting...');

// Test data
const testData = {
    who: 'SaaS founders and startup CEOs',
    what: 'scale their businesses to 7 figures', 
    when: 'they hit growth plateaus',
    how: 'through my proven systems framework'
};

// Get post ID
const postId = document.querySelector('#topics-generator-post-id')?.value;
if (!postId || postId === '0') {
    console.error('❌ No post ID found for testing');
} else {
    console.log('📍 Testing with post ID:', postId);
    
    // Test the save functionality
    if (window.makeAjaxRequest) {
        console.log('✅ makeAjaxRequest available, testing save...');
        
        window.makeAjaxRequest('mkcg_save_authority_hook', {
            post_id: postId,
            who: testData.who,
            what: testData.what,
            when: testData.when,
            how: testData.how
        })
        .then(response => {
            console.log('✅ Save test successful!');
            console.log('📊 Response:', response);
            
            // Test if we can load it back
            return window.makeAjaxRequest('mkcg_get_authority_hook', {
                post_id: postId
            });
        })
        .then(response => {
            console.log('✅ Load test successful!');
            console.log('📊 Loaded data:', response);
            
            // Verify data matches
            if (response.components) {
                const matches = {
                    who: response.components.who === testData.who,
                    what: response.components.what === testData.what,
                    when: response.components.when === testData.when,
                    how: response.components.how === testData.how
                };
                
                console.log('🔍 Data verification:', matches);
                
                const allMatch = Object.values(matches).every(Boolean);
                if (allMatch) {
                    console.log('🎉 ALL TESTS PASSED! Authority Hook Service is working perfectly!');
                    console.log('✅ Data is saving to WordPress post meta');
                    console.log('✅ Data is loading from WordPress post meta');
                    console.log('✅ Centralized architecture is functional');
                } else {
                    console.warn('⚠️ Some data didn\'t match - check field mappings');
                }
            }
        })
        .catch(error => {
            console.error('❌ Test failed:', error);
        });
        
    } else {
        console.warn('⚠️ makeAjaxRequest not available - check simple-ajax.js loading');
    }
}

console.log('🧪 Authority Hook Save Test - Test initiated');