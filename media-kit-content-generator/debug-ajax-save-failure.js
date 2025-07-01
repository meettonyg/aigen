/**
 * AJAX Save Debugging Script
 * Debug the save failure issue after fixing the JSON parse error
 * 
 * PROGRESS UPDATE:
 * ✅ JSON parse error FIXED - we now get proper JSON responses
 * ❌ Save operation failing - need to debug why
 * 
 * This script helps identify the root cause of the save failure
 */

// Debug AJAX save failure
console.log('🔍 AJAX Save Debugging - Load this in browser console on Topics Generator page');

// Function to intercept and debug AJAX requests
function debugAjaxSave() {
    // Store original fetch
    const originalFetch = window.fetch;
    
    // Override fetch to intercept AJAX calls
    window.fetch = function(...args) {
        console.log('🔍 AJAX Request Intercepted:', args);
        
        return originalFetch.apply(this, args)
            .then(response => {
                console.log('📡 AJAX Response:', response);
                
                // Clone response to read it without consuming
                return response.clone().text().then(text => {
                    console.log('📄 Response Text:', text);
                    
                    try {
                        const jsonData = JSON.parse(text);
                        console.log('📊 Parsed JSON:', jsonData);
                        
                        if (!jsonData.success) {
                            console.error('❌ Save Failed - Server Response:', jsonData);
                            console.error('❌ Error Code:', jsonData.data?.code);
                            console.error('❌ Error Message:', jsonData.data?.message);
                            console.error('❌ Debug Info:', jsonData.data?.debug_info);
                            console.error('❌ Error Details:', jsonData.data?.error_details);
                        } else {
                            console.log('✅ Save Successful:', jsonData);
                        }
                    } catch (e) {
                        console.error('❌ JSON Parse Error (Original issue):', e);
                        console.error('Raw response:', text);
                    }
                    
                    return response;
                });
            })
            .catch(error => {
                console.error('🚨 Network Error:', error);
                return Promise.reject(error);
            });
    };
    
    console.log('✅ AJAX debugging enabled - try saving topics now');
}

// Function to check current form data
function checkFormData() {
    console.log('📋 Checking current form data...');
    
    const entryId = document.querySelector('#topics-generator-entry-id')?.value;
    console.log('Entry ID:', entryId);
    
    const topicFields = [];
    for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field) {
            topicFields.push({
                number: i,
                selector: `#topics-generator-topic-field-${i}`,
                value: field.value,
                hasValue: !!field.value.trim()
            });
        }
    }
    
    console.log('📝 Topic Fields:', topicFields);
    
    const nonceField = document.querySelector('#topics-generator-nonce');
    console.log('🔐 Nonce Field:', nonceField?.value);
    
    return {
        entryId,
        topicFields,
        nonce: nonceField?.value
    };
}

// Function to manually test the AJAX call
function testAjaxCall() {
    console.log('🧪 Manual AJAX Test...');
    
    const formData = checkFormData();
    
    if (!formData.entryId) {
        console.error('❌ No entry ID found - cannot test AJAX call');
        return;
    }
    
    // Prepare test data
    const testData = new FormData();
    testData.append('action', 'mkcg_save_topics_data');
    testData.append('entry_id', formData.entryId);
    testData.append('nonce', formData.nonce || '');
    
    // Add topics from form
    formData.topicFields.forEach(field => {
        if (field.hasValue) {
            testData.append(`topic_${field.number}`, field.value);
        }
    });
    
    console.log('📤 Sending test AJAX request...');
    
    fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: testData
    })
    .then(response => response.text())
    .then(text => {
        console.log('📥 Raw response:', text);
        try {
            const json = JSON.parse(text);
            console.log('📊 Parsed response:', json);
            
            if (json.success) {
                console.log('✅ Manual test SUCCESSFUL');
            } else {
                console.error('❌ Manual test FAILED:', json.data);
            }
        } catch (e) {
            console.error('❌ Response is not valid JSON:', e);
        }
    })
    .catch(error => {
        console.error('🚨 Manual test error:', error);
    });
}

// Function to check WordPress globals
function checkWordPressGlobals() {
    console.log('🌐 WordPress Globals Check:');
    console.log('ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'NOT DEFINED');
    console.log('topics_vars:', typeof topics_vars !== 'undefined' ? topics_vars : 'NOT DEFINED');
    console.log('mkcg_vars:', typeof mkcg_vars !== 'undefined' ? mkcg_vars : 'NOT DEFINED');
    
    if (typeof topics_vars !== 'undefined') {
        console.log('📊 topics_vars details:', topics_vars);
    }
}

// Main debugging function
function debugSaveFailure() {
    console.log('🔧 AJAX Save Failure Debugging Started');
    console.log('=====================================');
    
    // Check environment
    checkWordPressGlobals();
    
    // Check form data
    const formData = checkFormData();
    
    // Enable AJAX interception
    debugAjaxSave();
    
    // Test manual AJAX call
    console.log('📋 Run testAjaxCall() to manually test the AJAX endpoint');
    
    // Make functions available globally
    window.testAjaxCall = testAjaxCall;
    window.checkFormData = checkFormData;
    
    console.log('✅ Debugging setup complete');
    console.log('📋 Available commands:');
    console.log('  - testAjaxCall() - Test AJAX endpoint manually');
    console.log('  - checkFormData() - Check current form data');
    console.log('=====================================');
}

// Auto-run debugging
debugSaveFailure();