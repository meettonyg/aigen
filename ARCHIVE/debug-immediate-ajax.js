/**
 * IMMEDIATE AJAX Test - Load in browser console AFTER page refresh
 * Tests if AJAX actions are now registered immediately during plugin loading
 */

console.log('🚀 Testing IMMEDIATE AJAX registration fix...');

function testImmediateAjaxRegistration() {
    console.log('🧪 Testing if AJAX actions are now registered...');
    
    // Test the specific action that was failing
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    formData.append('entry_id', '74492');
    formData.append('nonce', 'test');
    formData.append('topics[topic_1]', 'Test Topic 1');
    
    console.log('📡 Sending test request to check action registration...');
    
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📥 Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📄 Raw response length:', text.length);
        console.log('📄 Raw response:', text);
        
        if (text.length === 0) {
            console.error('❌ STILL EMPTY RESPONSE');
            console.error('This could mean:');
            console.error('  1. Plugin changes not loaded (need to refresh/clear cache)');
            console.error('  2. PHP fatal error preventing AJAX handler execution');
            console.error('  3. AJAX action still not registered properly');
        } else if (text === '0') {
            console.error('❌ WordPress returned "0" - Action still not found');
            console.error('AJAX handler registration timing fix did not work');
        } else if (text.includes('{"success":false')) {
            console.log('✅ PROGRESS! Got JSON error response - action IS registered!');
            console.log('Now the save is failing for a different reason (data/nonce)');
            try {
                const json = JSON.parse(text);
                console.log('📊 Error details:', json);
            } catch (e) {
                console.log('Response parsing issue:', e);
            }
        } else if (text.includes('{"success":true')) {
            console.log('🎉 COMPLETE SUCCESS! Topics saved successfully!');
            try {
                const json = JSON.parse(text);
                console.log('📊 Success response:', json);
            } catch (e) {
                console.log('Response parsing issue:', e);
            }
        } else {
            console.log('📄 Got unexpected response - analyzing...');
            console.log('First 200 chars:', text.substring(0, 200));
        }
    })
    .catch(error => {
        console.error('🚨 Network error:', error);
    });
}

function testWithValidNonce() {
    console.log('🔐 Testing with real nonce...');
    
    const entryId = document.getElementById('topics-generator-entry-id')?.value;
    const nonce = document.getElementById('topics-generator-nonce')?.value;
    
    if (!entryId || !nonce) {
        console.error('❌ Cannot find entry ID or nonce on page');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    formData.append('entry_id', entryId);
    formData.append('nonce', nonce);
    formData.append('topics[topic_1]', 'Test Topic with Valid Nonce');
    
    console.log('📡 Sending test with real nonce...', { entryId, nonce });
    
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('📄 Response with valid nonce:', text);
        
        if (text.includes('{"success":true')) {
            console.log('🎉 SUCCESS! AJAX handler is working with valid nonce!');
        } else if (text.includes('{"success":false')) {
            console.log('⚠️ Handler working but save failed - check error details');
            try {
                const json = JSON.parse(text);
                console.log('Error details:', json.data);
            } catch (e) {
                console.log('JSON parse error:', e);
            }
        } else {
            console.log('❓ Unexpected response:', text.substring(0, 200));
        }
    })
    .catch(error => {
        console.error('🚨 Error with valid nonce test:', error);
    });
}

// Make functions available
window.testImmediateAjaxRegistration = testImmediateAjaxRegistration;
window.testWithValidNonce = testWithValidNonce;

console.log('🚀 Immediate AJAX test loaded');
console.log('📋 Available commands:');
console.log('  - testImmediateAjaxRegistration() - Test if action is registered');
console.log('  - testWithValidNonce() - Test with real page nonce');
console.log('=====================================');

// Auto-run the test
testImmediateAjaxRegistration();