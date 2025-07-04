// Diagnostic script to check nonce issues
console.log('🔍 Checking nonce configuration...\n');

// Check what nonce values are available
console.log('Available nonce sources:');
console.log('- mkcg_vars.nonce:', window.mkcg_vars?.nonce);
console.log('- topics_vars.nonce:', window.topics_vars?.nonce);
console.log('- questions_vars.nonce:', window.questions_vars?.nonce);

// Check nonce from DOM
const nonceInputs = document.querySelectorAll('input[name*="nonce"]');
console.log('\nNonce inputs in DOM:', nonceInputs.length);
nonceInputs.forEach((input, i) => {
    console.log(`  ${i+1}. Name: ${input.name}, Value: ${input.value}`);
});

// Check what makeAjaxRequest is using
console.log('\nChecking makeAjaxRequest nonce logic...');
const testNonce = window.mkcg_vars?.nonce || 
                  window.topics_vars?.nonce || 
                  window.questions_vars?.nonce || 
                  document.querySelector('#topics-generator-nonce')?.value ||
                  document.querySelector('input[name*="nonce"]')?.value || '';
console.log('makeAjaxRequest would use nonce:', testNonce);

// Test nonce directly with WordPress AJAX
console.log('\n🧪 Testing nonce directly...');

const postId = document.querySelector('#topics-generator-post-id')?.value || '32372';

// Method 1: Using FormData (mimics form submission)
const formData = new FormData();
formData.append('action', 'mkcg_save_topics_data');
formData.append('nonce', window.mkcg_vars?.nonce || '');
formData.append('security', window.mkcg_vars?.nonce || ''); // backup field
formData.append('post_id', postId);
formData.append('topics[topic_1]', 'Test topic from FormData');

console.log('\nSending test with FormData...');
fetch(ajaxurl, {
    method: 'POST',
    body: formData
})
.then(r => r.json())
.then(data => {
    console.log('FormData test result:', data);
    if (!data.success) {
        console.error('FormData test failed:', data.data?.message || 'Unknown error');
    }
})
.catch(e => console.error('FormData test error:', e));

// Method 2: Check if we need to refresh the nonce
console.log('\n💡 Possible solutions:');
console.log('1. The nonce might be expired - try refreshing the page');
console.log('2. The nonce name might be different - check wp_create_nonce() calls');
console.log('3. You might need to be logged in as an admin');
console.log('4. Try in an incognito window to rule out caching issues');

// Show current user info
if (window.wp && window.wp.data) {
    const currentUser = wp.data.select('core').getCurrentUser();
    console.log('\nCurrent user:', currentUser);
}
