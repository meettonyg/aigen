// DEBUG: Check if AJAX action is registered
console.log('🔍 Checking AJAX registration...\n');

// Test 1: Check if the action exists
console.log('Testing if WordPress knows about our AJAX action...');

// Make a raw request to see what error we get
const formData = new FormData();
formData.append('action', 'mkcg_save_topics_data');

fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
})
.then(response => {
    console.log('Response status:', response.status);
    return response.text(); // Get text first to see raw response
})
.then(text => {
    console.log('Raw response:', text);
    
    if (text === '0' || text === '-1') {
        console.error('❌ AJAX action is NOT registered!');
        console.log('The action "mkcg_save_topics_data" is not hooked in WordPress');
        console.log('\nThis means:');
        console.log('1. The plugin might not be active');
        console.log('2. The AJAX hooks might not be registered');
        console.log('3. There might be a PHP error preventing registration');
    } else {
        try {
            const json = JSON.parse(text);
            console.log('✅ AJAX action IS registered!');
            console.log('Response:', json);
            
            if (!json.success) {
                console.log('\nThe action exists but returned an error:', json.data?.message);
            }
        } catch (e) {
            console.log('⚠️ Got unexpected response:', text);
        }
    }
})
.catch(error => {
    console.error('Network error:', error);
});

console.log('\nAlso check:');
console.log('1. Is the plugin active? Check Plugins page');
console.log('2. Check WordPress debug.log for PHP errors');
console.log('3. Try deactivating and reactivating the plugin');
