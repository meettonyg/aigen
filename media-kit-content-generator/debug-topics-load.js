/**
 * Emergency Topics Generator Diagnostic Script
 * Run this in browser console to diagnose the core issue
 */

console.log('🚨 EMERGENCY DIAGNOSTIC: Topics Generator Root Issue Analysis');

// 1. Check if Topics Generator loaded
console.log('\n1. TOPICS GENERATOR STATUS:');
console.log('- window.TopicsGenerator exists:', !!window.TopicsGenerator);
console.log('- TopicsGenerator.init function exists:', typeof window.TopicsGenerator?.init);

// 2. Check DOM elements
console.log('\n2. CRITICAL DOM ELEMENTS:');
const criticalElements = [
    '#topics-generator-authority-hook-text',
    '.topics-generator',
    '#topics-generator-topic-field-1',
    '#topics-generator-topic-field-2',
    '#topics-generator-topic-field-3',
    '#topics-generator-topic-field-4',
    '#topics-generator-topic-field-5',
    '#mkcg-who',
    '#mkcg-result',
    '#mkcg-when',
    '#mkcg-how'
];

criticalElements.forEach(selector => {
    const element = document.querySelector(selector);
    console.log(`- ${selector}:`, {
        exists: !!element,
        value: element?.value || element?.textContent || 'N/A',
        visible: element ? getComputedStyle(element).display !== 'none' : false
    });
});

// 3. Check data availability
console.log('\n3. DATA AVAILABILITY:');
console.log('- window.MKCG_Topics_Data:', window.MKCG_Topics_Data);
console.log('- window.mkcg_vars:', window.mkcg_vars);
console.log('- URL params:', new URLSearchParams(window.location.search).toString());

// 4. Test AJAX system
console.log('\n4. AJAX SYSTEM TEST:');
console.log('- makeAjaxRequest function exists:', typeof window.makeAjaxRequest);
console.log('- ajaxurl available:', window.ajaxurl);
console.log('- nonce available:', window.mkcg_vars?.nonce || 'MISSING');

// 5. Look for errors
console.log('\n5. ERROR DETECTION:');
const errors = [];

// Check for missing critical functions
if (!window.TopicsGenerator) {
    errors.push('TopicsGenerator not loaded - check JavaScript errors');
}

// Check for missing DOM structure
const topicsContainer = document.querySelector('.topics-generator');
if (!topicsContainer) {
    errors.push('Topics generator container not found - shortcode may not be rendering');
}

// Check for missing AJAX
if (!window.makeAjaxRequest) {
    errors.push('AJAX system not loaded - check simple-ajax.js');
}

// Check for missing entry ID
const entryIdElement = document.querySelector('#topics-generator-entry-id');
if (!entryIdElement || !entryIdElement.value || entryIdElement.value === '0') {
    errors.push('No entry ID found - check if WordPress entry is properly associated');
}

console.log('DETECTED ERRORS:', errors);

// 6. Quick fix attempt
console.log('\n6. ATTEMPTING QUICK FIXES:');

// Try to initialize if not done
if (window.TopicsGenerator && typeof window.TopicsGenerator.init === 'function') {
    console.log('Attempting to re-initialize TopicsGenerator...');
    try {
        window.TopicsGenerator.init();
        console.log('✅ Re-initialization successful');
    } catch (error) {
        console.error('❌ Re-initialization failed:', error);
    }
}

// Try to populate fields manually
if (window.MKCG_Topics_Data) {
    console.log('Attempting manual field population...');
    
    // Populate authority hook components
    const fieldMappings = [
        { id: 'mkcg-who', data: window.MKCG_Topics_Data.authorityHook?.who },
        { id: 'mkcg-result', data: window.MKCG_Topics_Data.authorityHook?.result },
        { id: 'mkcg-when', data: window.MKCG_Topics_Data.authorityHook?.when },
        { id: 'mkcg-how', data: window.MKCG_Topics_Data.authorityHook?.how }
    ];
    
    fieldMappings.forEach(({ id, data }) => {
        const field = document.getElementById(id);
        if (field && data && data.trim()) {
            field.value = data;
            console.log(`✅ Populated ${id} with: "${data}"`);
        }
    });
    
    // Populate topic fields
    for (let i = 1; i <= 5; i++) {
        const field = document.getElementById(`topics-generator-topic-field-${i}`);
        const topicData = window.MKCG_Topics_Data.topics?.[`topic_${i}`];
        
        if (field && topicData && topicData.trim()) {
            field.value = topicData;
            console.log(`✅ Populated topic ${i} with: "${topicData}"`);
        }
    }
    
    // Update authority hook display
    const hookDisplay = document.getElementById('topics-generator-authority-hook-text');
    const completeHook = window.MKCG_Topics_Data.authorityHook?.complete;
    
    if (hookDisplay && completeHook) {
        hookDisplay.textContent = completeHook;
        console.log(`✅ Updated authority hook display: "${completeHook}"`);
    }
}

console.log('\n🎯 DIAGNOSTIC COMPLETE - Check above for issues and fixes');
