// 🔍 DIRECT AJAX TEST - Bypass makeAjaxRequest to see exactly what's happening
console.log('🔍 Running DIRECT AJAX test...');

// Get nonce and post ID
const nonce = window.mkcg_vars?.nonce || document.querySelector('#topics-generator-nonce')?.value || '';
const postId = document.querySelector('#topics-generator-post-id')?.value || '32372';

console.log('🔑 Nonce:', nonce);
console.log('📍 Post ID:', postId);

if (!nonce) {
    console.error('❌ No nonce found! This will definitely fail.');
    console.log('Available variables:', {
        mkcg_vars: window.mkcg_vars,
        nonce_element: document.querySelector('#topics-generator-nonce')
    });
}

// Create form data manually to see exactly what gets sent
const formData = new FormData();
formData.append('action', 'mkcg_save_topics_data');
formData.append('nonce', nonce);
formData.append('security', nonce);
formData.append('post_id', postId);

// Add topics as individual fields (simpler approach)
formData.append('topic_1', 'Direct Test Topic 1');
formData.append('topic_2', 'Direct Test Topic 2');

// Add authority hook as individual fields
formData.append('who', 'direct test users');
formData.append('what', 'test the direct method');
formData.append('when', 'they need debugging');
formData.append('how', 'through direct AJAX calls');

console.log('📤 Sending FormData with these entries:');
for (let [key, value] of formData.entries()) {
    console.log(`  ${key}: ${value}`);
}

// Send direct fetch request
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: formData
})
.then(response => {
    console.log('📥 Response status:', response.status);
    console.log('📥 Response headers:', response.headers);
    return response.text();
})
.then(text => {
    console.log('📥 Raw response:', text);
    
    // Try to parse as JSON
    try {
        const json = JSON.parse(text);
        console.log('📥 Parsed response:', json);
        
        if (json.success) {
            console.log('🎉 DIRECT TEST SUCCESS!');
            console.log('✅ Data was saved successfully');
        } else {
            console.log('❌ Server returned error:', json.data);
            if (json.data?.debug) {
                console.log('🔍 Debug info:', json.data.debug);
            }
        }
    } catch (e) {
        console.log('❌ Response is not JSON:', e);
        console.log('📄 Raw response was:', text);
        
        // Check if it's a WordPress error or something else
        if (text.includes('Fatal error') || text.includes('Parse error')) {
            console.log('💥 PHP Error detected in response');
        } else if (text.includes('<!DOCTYPE html>')) {
            console.log('🌐 HTML page returned instead of AJAX response');
        }
    }
})
.catch(error => {
    console.error('❌ Network error:', error);
});

console.log('🚀 Direct test sent! Check output above.');