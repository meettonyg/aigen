// Comprehensive nonce test script
console.log('🔍 COMPREHENSIVE NONCE DEBUGGING\n');

// Step 1: Check all available nonce values
console.log('=== STEP 1: Available Nonces ===');
const nonces = {
    'mkcg_vars.nonce': window.mkcg_vars?.nonce,
    'mkcg_vars.ajax_nonce': window.mkcg_vars?.ajax_nonce,
    'topics_vars.nonce': window.topics_vars?.nonce,
    'questions_vars.nonce': window.questions_vars?.nonce,
    'ajaxurl': window.ajaxurl || window.mkcg_vars?.ajax_url
};

for (const [key, value] of Object.entries(nonces)) {
    console.log(`${key}: ${value || 'NOT FOUND'}`);
}

// Step 2: Check DOM for nonce inputs
console.log('\n=== STEP 2: DOM Nonce Inputs ===');
document.querySelectorAll('input[type="hidden"]').forEach(input => {
    if (input.name.includes('nonce') || input.id.includes('nonce')) {
        console.log(`Found: ${input.name || input.id} = ${input.value}`);
    }
});

// Step 3: Test with current implementation
console.log('\n=== STEP 3: Test Current Implementation ===');
const postId = document.querySelector('#topics-generator-post-id')?.value;

if (!postId) {
    console.error('❌ No post ID found - are you on the Topics Generator page?');
} else {
    console.log('Post ID:', postId);
    
    // Get the nonce that would be used
    const nonce = window.mkcg_vars?.nonce || 
                  window.topics_vars?.nonce || 
                  window.questions_vars?.nonce || 
                  document.querySelector('#topics-generator-nonce')?.value ||
                  document.querySelector('input[name*="nonce"]')?.value || '';
    
    console.log('Nonce that will be used:', nonce);
    
    // Step 4: Make a raw AJAX call to see exact error
    console.log('\n=== STEP 4: Raw AJAX Test ===');
    
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    formData.append('nonce', nonce);
    formData.append('security', nonce); // backup
    formData.append('post_id', postId);
    formData.append('topics[topic_1]', 'Nonce Test Topic');
    formData.append('authority_hook[who]', 'test users');
    
    // Log what we're sending
    console.log('Sending FormData with:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' // Important for cookies/session
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('\n=== AJAX Response ===');
        console.log(data);
        
        if (data.success) {
            console.log('✅ SUCCESS! The nonce is working correctly.');
            console.log('Data saved:', data.data);
        } else {
            console.error('❌ FAILED:', data.data?.message || 'Unknown error');
            
            if (data.data?.message === 'Security check failed') {
                console.log('\n🔧 TROUBLESHOOTING NONCE ISSUE:');
                console.log('1. Try refreshing the page (nonce might be expired)');
                console.log('2. Make sure you are logged in');
                console.log('3. Check WordPress debug.log for detailed error');
                console.log('4. The nonce name might be different than expected');
                
                // Try to generate a fresh nonce if wp object is available
                if (window.wp && window.wp.ajax) {
                    console.log('\n5. Trying to get fresh nonce from WordPress...');
                    // This would require REST API or other method
                }
            }
        }
    })
    .catch(error => {
        console.error('Network error:', error);
    });
}

// Step 5: Alternative test without library
console.log('\n=== STEP 5: Direct XMLHttpRequest Test ===');
if (postId && nonce) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.ajaxurl || '/wp-admin/admin-ajax.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    const params = new URLSearchParams({
        action: 'mkcg_save_topics_data',
        nonce: nonce,
        post_id: postId,
        'topics[topic_1]': 'XHR Test Topic'
    });
    
    xhr.onload = function() {
        try {
            const response = JSON.parse(xhr.responseText);
            console.log('XHR Response:', response);
        } catch (e) {
            console.error('XHR Parse error:', e);
            console.log('Raw response:', xhr.responseText);
        }
    };
    
    xhr.send(params);
}

console.log('\n💡 If all tests fail with "Security check failed", the issue is definitely the nonce.');
console.log('Check wp-content/debug.log for the exact nonce values being compared.');
