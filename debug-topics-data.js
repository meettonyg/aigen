// URGENT DEBUG: Add this to browser console to check actual data
console.log('🔍 DEBUGGING TOPICS DATA');
console.log('Window MKCG_Topics_Data:', window.MKCG_Topics_Data);

// Check each authority hook component
if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.authorityHook) {
    const ah = window.MKCG_Topics_Data.authorityHook;
    console.log('🔍 Authority Hook Data:');
    console.log('- who:', typeof ah.who, '|', ah.who);
    console.log('- result:', typeof ah.result, '|', ah.result);
    console.log('- when:', typeof ah.when, '|', ah.when);
    console.log('- how:', typeof ah.how, '|', ah.how);
    console.log('- complete:', typeof ah.complete, '|', ah.complete);
}

// Check topics data
if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.topics) {
    const topics = window.MKCG_Topics_Data.topics;
    console.log('🔍 Topics Data:');
    for (let i = 1; i <= 5; i++) {
        const topicKey = 'topic_' + i;
        console.log(`- ${topicKey}:`, typeof topics[topicKey], '|', topics[topicKey]);
    }
}

// Check if the elements exist
console.log('🔍 DOM Elements Check:');
const elements = [
    'topics-generator-who-input',
    'topics-generator-result-input', 
    'topics-generator-when-input',
    'topics-generator-how-input',
    'topics-generator-authority-hook-text'
];

elements.forEach(id => {
    const el = document.getElementById(id);
    console.log(`- #${id}:`, el ? '✅ Found' : '❌ Missing');
    if (el) {
        console.log(`  Value: "${el.value || el.textContent}"`);
    }
});

console.log('🔍 Debug complete');
