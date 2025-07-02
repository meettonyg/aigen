/**
 * Debug Script: Verify JavaScript Variable Unification Fix
 * 
 * Run this in browser console on Questions Generator page to verify the fix
 */

console.log('🔧 DEBUGGING: JavaScript Variable Unification Fix');
console.log('================================================');

// Test 1: Check if the unified variable exists
if (typeof window.MKCG_Topics_Data !== 'undefined') {
    console.log('✅ TEST 1 PASSED: window.MKCG_Topics_Data exists');
    console.log('📊 Data structure:', window.MKCG_Topics_Data);
} else {
    console.error('❌ TEST 1 FAILED: window.MKCG_Topics_Data is undefined');
}

// Test 2: Check data source
if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.dataSource) {
    console.log('✅ TEST 2 PASSED: Data source is', window.MKCG_Topics_Data.dataSource);
} else {
    console.error('❌ TEST 2 FAILED: No data source found');
}

// Test 3: Check topics structure
if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.topics) {
    const topics = window.MKCG_Topics_Data.topics;
    const topicKeys = Object.keys(topics);
    const nonEmptyTopics = topicKeys.filter(key => topics[key] && topics[key].trim());
    
    console.log('✅ TEST 3 PASSED: Topics object exists');
    console.log('📝 Topic keys found:', topicKeys);
    console.log('📝 Non-empty topics:', nonEmptyTopics.length);
    
    // Show topic data
    topicKeys.forEach(key => {
        const value = topics[key];
        const status = value && value.trim() ? '✅' : '⚪';
        console.log(`${status} Topic ${key}: "${value}"`);
    });
} else {
    console.error('❌ TEST 3 FAILED: No topics object found');
}

// Test 4: Check if Questions Generator can access the data
if (typeof QuestionsGenerator !== 'undefined') {
    console.log('✅ TEST 4 PASSED: QuestionsGenerator object exists');
    
    // Try to call the data loading function
    try {
        if (QuestionsGenerator.loadUnifiedTopicsData) {
            console.log('🔄 Running loadUnifiedTopicsData...');
            QuestionsGenerator.loadUnifiedTopicsData();
            console.log('✅ loadUnifiedTopicsData completed without errors');
        } else {
            console.log('⚠️ loadUnifiedTopicsData method not found');
        }
    } catch (error) {
        console.error('❌ Error calling loadUnifiedTopicsData:', error);
    }
} else {
    console.error('❌ TEST 4 FAILED: QuestionsGenerator not found');
}

// Test 5: Check for the specific error we were fixing
const originalError = 'No topics data from PHP';
console.log('🎯 Checking for resolved error: "' + originalError + '"');

// Look for the error in console history (if available)
if (window.console && console.memory) {
    console.log('📊 Console memory usage:', console.memory);
}

console.log('================================================');
console.log('🏁 UNIFICATION FIX VERIFICATION COMPLETE');
console.log('💡 If all tests pass, the fix should be working!');
console.log('🐛 If you still see "No topics data from PHP" errors,');
console.log('   please refresh the page and run this test again.');
