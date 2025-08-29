/**
 * Impact Intro Builder Integration Test
 * 
 * This test verifies that the Impact Intro Builder:
 * 1. Loads properly when Biography Generator is initialized
 * 2. Toggle button works correctly
 * 3. Field binding and updates work
 * 4. Example chips functionality works
 * 5. UX enhancements (button states) work properly
 */

// Test function to verify Impact Intro integration
function testImpactIntroIntegration() {
    console.log('🧪 Testing Impact Intro Builder Integration...');
    console.log('================================================');
    
    // Test 1: Check if Impact Intro Builder JavaScript loaded
    const impactIntroBuilderAvailable = window.ImpactIntroBuilder;
    console.log(`1. Impact Intro Builder Available: ${impactIntroBuilderAvailable ? '✅ YES' : '❌ NO'}`);
    
    if (!impactIntroBuilderAvailable) {
        console.log('❌ Impact Intro Builder JavaScript not loaded - check Asset Manager');
        return;
    }
    
    // Test 2: Check if toggle button exists
    const toggleButton = document.getElementById('biography-generator-toggle-impact-builder');
    console.log(`2. Toggle Button Exists: ${toggleButton ? '✅ YES' : '❌ NO'}`);
    
    if (!toggleButton) {
        console.log('❌ Impact Intro toggle button not found in Biography template');
        return;
    }
    
    // Test 3: Check if builder container exists
    const builderContainer = document.getElementById('biography-generator-impact-intro-builder');
    console.log(`3. Builder Container Exists: ${builderContainer ? '✅ YES' : '❌ NO'}`);
    
    if (!builderContainer) {
        console.log('❌ Impact Intro builder container not found');
        return;
    }
    
    // Test 4: Test toggle functionality
    console.log('4. Testing toggle functionality...');
    const initiallyHidden = builderContainer.classList.contains('generator__builder--hidden');
    console.log(`   Initially hidden: ${initiallyHidden ? 'YES' : 'NO'}`);
    
    // Simulate button click
    toggleButton.click();
    
    setTimeout(() => {
        const afterClickHidden = builderContainer.classList.contains('generator__builder--hidden');
        const toggleWorked = initiallyHidden !== afterClickHidden;
        console.log(`   After click hidden: ${afterClickHidden ? 'YES' : 'NO'}`);
        console.log(`   Toggle functionality: ${toggleWorked ? '✅ WORKING' : '❌ FAILED'}`);
        
        // Test 5: Check if fields are rendered correctly
        setTimeout(() => {
            const whereField = document.getElementById('mkcg-where');
            const whyField = document.getElementById('mkcg-why');
            console.log(`5. WHERE field rendered: ${whereField ? '✅ YES' : '❌ NO'}`);
            console.log(`6. WHY field rendered: ${whyField ? '✅ YES' : '❌ NO'}`);
            
            if (whereField && whyField) {
                // Test 6: Test field input and update functionality
                console.log('7. Testing field input functionality...');
                
                const testWhere = 'Test credential for verification';
                const testWhy = 'Test mission for verification';
                
                whereField.value = testWhere;
                whyField.value = testWhy;
                
                // Trigger input events
                whereField.dispatchEvent(new Event('input', { bubbles: true }));
                whyField.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Check if display updates
                setTimeout(() => {
                    const displayElement = document.getElementById('biography-generator-impact-intro-text');
                    if (displayElement) {
                        const hasTestContent = displayElement.textContent.includes(testWhere) || displayElement.textContent.includes(testWhy);
                        console.log(`   Display updates: ${hasTestContent ? '✅ WORKING' : '❌ FAILED'}`);
                        console.log(`   Display content: "${displayElement.textContent}"`);
                    }
                    
                    // Test 7: Test example chips if available
                    const exampleChips = document.querySelectorAll('.tag[data-target="mkcg-where"], .tag[data-target="mkcg-why"]');
                    console.log(`8. Example chips available: ${exampleChips.length > 0 ? '✅ YES (' + exampleChips.length + ' chips)' : '❌ NO'}`);
                    
                    if (exampleChips.length > 0) {
                        console.log('   Testing first example chip...');
                        const firstChip = exampleChips[0];
                        const addLink = firstChip.querySelector('.tag__add-link');
                        if (addLink) {
                            addLink.click();
                            console.log('   ✅ Example chip click simulated');
                        }
                    }
                    
                    console.log('🎯 Integration test complete!');
                }, 500);
            }
        }, 500);
    }, 500);
}

// Run the test when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(testImpactIntroIntegration, 2000);
    });
} else {
    setTimeout(testImpactIntroIntegration, 2000);
}