/**
 * AJAX Save Debugging Script - STEP 2
 * Debug the EXACT data format being sent vs expected
 * 
 * PROGRESS UPDATE:
 * ✅ JSON parse error FIXED 
 * ✅ PHP updated to handle multiple data formats
 * ❓ Need to verify exact data format match
 * 
 * Load this in browser console on Topics Generator page
 */

console.log('🔧 AJAX Save Format Debugging - Load this script in console');

// Function to test the EXACT data format
function testExactDataFormat() {
    console.log('🧪 Testing EXACT data format...');
    
    const entryId = document.getElementById('topics-generator-entry-id')?.value;
    const nonce = document.getElementById('topics-generator-nonce')?.value;
    
    if (!entryId) {
        console.error('❌ No entry ID found');
        return;
    }
    
    // Collect topics exactly like the template JavaScript does
    const topics = {};
    for (let i = 1; i <= 5; i++) {
        const field = document.getElementById(`topics-generator-topic-field-${i}`);
        if (field) {
            topics[`topic_${i}`] = field.value.trim();
        }
    }
    
    console.log('📋 Topics object:', topics);
    
    // Create FormData exactly like template does
    const formData = new FormData();
    formData.append('action', 'mkcg_save_topics_data');
    formData.append('entry_id', entryId);
    formData.append('nonce', nonce);
    
    // Add topics exactly like template does
    Object.keys(topics).forEach(key => {
        formData.append(`topics[${key}]`, topics[key]);
        console.log(`📤 Adding to FormData: topics[${key}] = "${topics[key]}"`);
    });
    
    // Convert FormData to object for debugging
    const formDataObject = {};
    for (let [key, value] of formData.entries()) {
        formDataObject[key] = value;
    }
    
    console.log('📦 Complete FormData object:', formDataObject);
    console.log('📤 FormData structure:');
    console.log('  - action:', formDataObject.action);
    console.log('  - entry_id:', formDataObject.entry_id);
    console.log('  - nonce:', formDataObject.nonce);
    
    // Show exactly what PHP will receive
    console.log('🔍 PHP will receive $_POST with:');
    Object.keys(formDataObject).forEach(key => {
        if (key.startsWith('topics[')) {
            console.log(`  - ${key}: "${formDataObject[key]}"`);
        }
    });
    
    // Test the actual AJAX call
    console.log('📡 Sending test request...');
    
    fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📥 Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📄 Raw response:', text);
        
        try {
            const json = JSON.parse(text);
            console.log('📊 Parsed JSON:', json);
            
            if (json.success) {
                console.log('✅ SUCCESS! Topics saved:', json.data);
            } else {
                console.error('❌ FAILURE details:');
                console.error('  - Code:', json.data?.code);
                console.error('  - Message:', json.data?.message);
                console.error('  - Debug info:', json.data?.debug_info);
                console.error('  - Error details:', json.data?.error_details);
            }
        } catch (e) {
            console.error('❌ Response not JSON:', e);
            console.error('Raw response was:', text);
        }
    })
    .catch(error => {
        console.error('🚨 Network error:', error);
    });
}

// Function to check if there are any topics to save
function checkTopicsData() {
    console.log('📝 Checking current topics data...');
    
    const topics = {};
    let hasAnyTopics = false;
    
    for (let i = 1; i <= 5; i++) {
        const field = document.getElementById(`topics-generator-topic-field-${i}`);
        if (field) {
            const value = field.value.trim();
            topics[`topic_${i}`] = value;
            if (value) hasAnyTopics = true;
            console.log(`Topic ${i}:`, value ? `"${value}"` : '(empty)');
        } else {
            console.error(`❌ Topic field ${i} not found`);
        }
    }
    
    if (!hasAnyTopics) {
        console.warn('⚠️ WARNING: No topics have content - save will likely fail with "NO_TOPICS_DATA"');
        console.log('💡 TIP: Enter some topic text first, then try saving');
    } else {
        console.log('✅ Topics data ready for saving');
    }
    
    return { topics, hasAnyTopics };
}

// Function to simulate the exact save process
function simulateSaveProcess() {
    console.log('🎭 Simulating exact save process...');
    
    const data = checkTopicsData();
    
    if (!data.hasAnyTopics) {
        console.log('⏹️ Stopping simulation - no topics to save');
        return;
    }
    
    console.log('▶️ Proceeding with save simulation...');
    testExactDataFormat();
}

// Function to add test data
function addTestTopics() {
    console.log('🧪 Adding test topics...');
    
    const testTopics = [
        'The Authority Positioning Framework: How to Become the Go-To Expert',
        'Creating Content That Converts: A Strategic Approach to Audience Building',
        'Systems for Success: Automating Your Business for Freedom',
        'The Podcast Guest Formula: Turning Interviews into Clients',
        'Building a Sustainable Business Model for Your Lifestyle'
    ];
    
    for (let i = 1; i <= 5; i++) {
        const field = document.getElementById(`topics-generator-topic-field-${i}`);
        if (field) {
            field.value = testTopics[i - 1] || `Test topic ${i}`;
            console.log(`✅ Added test topic ${i}:`, field.value);
        }
    }
    
    console.log('✅ Test topics added - now try saving');
}

// Make functions available globally
window.testExactDataFormat = testExactDataFormat;
window.checkTopicsData = checkTopicsData;
window.simulateSaveProcess = simulateSaveProcess;
window.addTestTopics = addTestTopics;

console.log('✅ Format debugging loaded');
console.log('📋 Available commands:');
console.log('  - checkTopicsData() - Check current topic field values');
console.log('  - addTestTopics() - Add test topic content');
console.log('  - testExactDataFormat() - Test the exact AJAX request');
console.log('  - simulateSaveProcess() - Full save simulation');
console.log('=====================================');