/**
 * Simple AJAX Test - Load in browser console
 * Tests if the AJAX endpoint is working at all
 */

console.log('🧪 Simple AJAX Test - Testing basic connectivity');

function testBasicAjax() {
    console.log('📡 Testing basic AJAX connectivity...');
    
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    formData.append('entry_id', '74492');
    formData.append('nonce', 'test');
    formData.append('topics[topic_1]', 'Test Topic');
    
    console.log('📤 Sending minimal test request...');
    
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📥 Response received:');
        console.log('  - Status:', response.status);
        console.log('  - Status Text:', response.statusText);
        console.log('  - Headers:', Object.fromEntries(response.headers.entries()));
        
        return response.text();
    })
    .then(text => {
        console.log('📄 Raw response length:', text.length);
        console.log('📄 Raw response content:', text);
        
        if (text.length === 0) {
            console.error('❌ EMPTY RESPONSE - This indicates:');
            console.error('  1. AJAX action not registered with WordPress');
            console.error('  2. PHP fatal error in the handler');
            console.error('  3. WordPress security blocking the request');
            console.error('  4. Plugin not loaded or changes not applied');
        } else if (text.startsWith('0')) {
            console.error('❌ WordPress returned "0" - Action not found');
        } else {
            console.log('✅ Got some response content - analyzing...');
            
            try {
                const json = JSON.parse(text);
                console.log('✅ Valid JSON response:', json);
            } catch (e) {
                console.warn('⚠️ Response is not JSON:', e);
                console.log('Response might be HTML error or WordPress debug output');
            }
        }
    })
    .catch(error => {
        console.error('🚨 Network error:', error);
    });
}

function testWordPressAjax() {
    console.log('🔍 Testing WordPress AJAX system...');
    
    // Test with a WordPress built-in action
    const formData = new FormData();
    formData.append('action', 'heartbeat');
    formData.append('_nonce', 'test');
    
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('📄 WordPress heartbeat test response:', text);
        if (text.length > 0) {
            console.log('✅ WordPress AJAX system is working');
        } else {
            console.error('❌ WordPress AJAX system may be broken');
        }
    })
    .catch(error => {
        console.error('🚨 WordPress AJAX test failed:', error);
    });
}

function testOtherMkcgAction() {
    console.log('🔍 Testing other MKCG actions...');
    
    // Test if any other MKCG actions work
    const actions = [
        'mkcg_save_field',
        'mkcg_save_topic', 
        'mkcg_save_authority_hook',
        'mkcg_generate_topics'
    ];
    
    actions.forEach(action => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('entry_id', '74492');
        formData.append('nonce', 'test');
        
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            if (text.length === 0) {
                console.error(`❌ ${action}: Empty response`);
            } else if (text === '0') {
                console.error(`❌ ${action}: Not found (WordPress returned "0")`);
            } else {
                console.log(`✅ ${action}: Got response (${text.length} chars)`, text.substring(0, 100));
            }
        })
        .catch(error => {
            console.error(`🚨 ${action}: Network error`, error);
        });
    });
}

// Make functions available
window.testBasicAjax = testBasicAjax;
window.testWordPressAjax = testWordPressAjax;
window.testOtherMkcgAction = testOtherMkcgAction;

console.log('🧪 Simple AJAX Test loaded');
console.log('📋 Available commands:');
console.log('  - testBasicAjax() - Test our specific action');
console.log('  - testWordPressAjax() - Test WordPress AJAX system');  
console.log('  - testOtherMkcgAction() - Test other MKCG actions');
console.log('=====================================');

// Auto-run basic test
testBasicAjax();