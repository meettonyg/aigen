// 🔍 SIMPLE DEBUG TEST - Run this to see exactly what's being sent
console.log('🔍 Testing AJAX data transmission...');

// Test exactly what the makeAjaxRequest function sends
const testData = {
    post_id: '32372',
    topics: {
        topic_1: 'Test Topic 1',
        topic_2: 'Test Topic 2'
    },
    authority_hook: {
        who: 'test users',
        what: 'test their systems',
        when: 'they need help',
        how: 'through testing'
    }
};

console.log('📤 About to send:', testData);

// Manually create the same request that makeAjaxRequest would send
const formData = new URLSearchParams();
formData.append('action', 'mkcg_save_topics_data');
formData.append('nonce', window.mkcg_vars?.nonce || '');
formData.append('security', window.mkcg_vars?.nonce || '');

// Add each piece of data exactly like makeAjaxRequest does
Object.keys(testData).forEach(key => {
    if (testData[key] !== null && testData[key] !== undefined) {
        if (typeof testData[key] === 'object') {
            const jsonValue = JSON.stringify(testData[key]);
            formData.append(key, jsonValue);
            console.log(`📝 Adding ${key} as JSON:`, jsonValue);
        } else {
            formData.append(key, testData[key]);
            console.log(`📝 Adding ${key} as string:`, testData[key]);
        }
    }
});

console.log('📤 FormData entries:');
for (let [key, value] of formData.entries()) {
    console.log(`  ${key}: ${value}`);
}

// Send the request and see what happens
fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: formData.toString()
})
.then(response => {
    console.log('📥 Response status:', response.status);
    return response.text(); // Get as text first to see the raw response
})
.then(text => {
    console.log('📥 Raw response text:', text);
    try {
        const json = JSON.parse(text);
        console.log('📥 Parsed JSON response:', json);
        
        if (json.success === false) {
            console.log('❌ Server error:', json.data);
            if (json.data.debug) {
                console.log('🔍 Debug info:', json.data.debug);
            }
        } else {
            console.log('✅ Success!');
        }
    } catch (e) {
        console.log('❌ Failed to parse JSON:', e);
        console.log('📄 Response was not JSON');
    }
})
.catch(error => {
    console.error('❌ Network error:', error);
});

console.log('🚀 Test request sent! Check the output above and the WordPress error logs.');