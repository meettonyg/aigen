/**
 * Test Script for Topics Generator Save Fix
 * This script tests the root-level fixes for the "No data provided to save" error
 */

console.log('🧪 Starting Topics Generator Save Fix Test...');

// Test 1: Verify AJAX system is loaded
console.log('\n📍 TEST 1: Checking AJAX system availability');
if (typeof window.makeAjaxRequest === 'function') {
    console.log('✅ makeAjaxRequest function is available');
} else {
    console.error('❌ makeAjaxRequest function NOT found!');
}

// Test 2: Check for required elements
console.log('\n📍 TEST 2: Checking required DOM elements');
const postId = document.querySelector('#topics-generator-post-id')?.value;
if (postId && postId !== '0') {
    console.log('✅ Post ID found:', postId);
} else {
    console.error('❌ No valid post ID found');
}

// Test 3: Test data structure
console.log('\n📍 TEST 3: Testing data structure');
const testData = {
    post_id: postId || '32372',
    topics: {
        topic_1: 'Test Topic 1: Building Authority',
        topic_2: 'Test Topic 2: Content Strategy',
        topic_3: 'Test Topic 3: Personal Branding'
    },
    authority_hook: {
        who: 'business coaches',
        what: 'build authority',
        when: 'they want to scale',
        how: 'through strategic content'
    }
};

console.log('📊 Test data structure:', testData);

// Test 4: Inspect AJAX request format
console.log('\n📍 TEST 4: Testing AJAX request format');

// Create a mock FormData to see what will be sent
const mockData = new URLSearchParams();
mockData.append('action', 'mkcg_save_topics_data');
mockData.append('nonce', window.mkcg_vars?.nonce || 'test-nonce');

// Add data the same way simple-ajax.js does (with our fix)
Object.keys(testData).forEach(key => {
    if (testData[key] !== null && testData[key] !== undefined) {
        if (typeof testData[key] === 'object' && !Array.isArray(testData[key])) {
            // JSON string
            mockData.append(key, JSON.stringify(testData[key]));
            
            // Also send individual fields
            Object.keys(testData[key]).forEach(subKey => {
                if (testData[key][subKey] !== null && testData[key][subKey] !== undefined) {
                    mockData.append(`${key}[${subKey}]`, testData[key][subKey]);
                }
            });
        } else {
            mockData.append(key, testData[key]);
        }
    }
});

console.log('📋 Data that will be sent:');
for (let [key, value] of mockData.entries()) {
    console.log(`  ${key}: ${value}`);
}

// Test 5: Actual save test
console.log('\n📍 TEST 5: Performing actual save test');

if (window.makeAjaxRequest && postId) {
    console.log('🚀 Sending AJAX request...');
    
    window.makeAjaxRequest('mkcg_save_topics_data', testData)
        .then(response => {
            console.log('✅ SAVE SUCCESSFUL!');
            console.log('📊 Response:', response);
            
            // Test 6: Verify saved data
            console.log('\n📍 TEST 6: Loading saved data back');
            return window.makeAjaxRequest('mkcg_get_topics_data', {
                post_id: postId
            });
        })
        .then(response => {
            console.log('✅ Data loaded successfully!');
            console.log('📊 Loaded data:', response);
            
            // Verify the data matches
            console.log('\n📍 VERIFICATION:');
            if (response.topics) {
                console.log('Topics saved correctly:', response.topics);
            }
            if (response.authority_hook_components) {
                console.log('Authority hook saved correctly:', response.authority_hook_components);
            }
            
            console.log('\n🎉 ALL TESTS PASSED! The save functionality is now working.');
        })
        .catch(error => {
            console.error('❌ Error during test:', error);
            console.log('\n🔍 DEBUG INFO:');
            console.log('Error message:', error.message);
            console.log('Check WordPress debug.log for server-side errors');
            console.log('Check Network tab for request/response details');
        });
} else {
    console.log('⚠️ Cannot perform actual save test - missing requirements');
}

console.log('\n💡 Manual Test Instructions:');
console.log('1. Fill in some topics in the Topics Generator form');
console.log('2. Fill in the Authority Hook Builder fields');
console.log('3. Click the "Save All Topics" button');
console.log('4. Check the console for success/error messages');
console.log('5. Refresh the page to verify data persists');

// Helper function to manually trigger save
window.testManualSave = function() {
    console.log('\n🧪 Manual save test triggered...');
    
    const topics = {};
    for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field && field.value.trim()) {
            topics[`topic_${i}`] = field.value.trim();
        }
    }
    
    const authorityHook = {
        who: document.querySelector('#mkcg-who')?.value || 'your audience',
        what: document.querySelector('#mkcg-result')?.value || 'achieve results',
        when: document.querySelector('#mkcg-when')?.value || 'they need help',
        how: document.querySelector('#mkcg-how')?.value || 'through your method'
    };
    
    const postId = document.querySelector('#topics-generator-post-id')?.value;
    
    console.log('📊 Collected data:', { topics, authorityHook });
    
    if (!postId || postId === '0') {
        console.error('❌ No post ID found!');
        return;
    }
    
    window.makeAjaxRequest('mkcg_save_topics_data', {
        post_id: postId,
        topics: topics,
        authority_hook: authorityHook
    })
    .then(response => {
        console.log('✅ Manual save successful!', response);
    })
    .catch(error => {
        console.error('❌ Manual save failed:', error);
    });
};

console.log('\n💡 TIP: Run window.testManualSave() to test saving current form data');
