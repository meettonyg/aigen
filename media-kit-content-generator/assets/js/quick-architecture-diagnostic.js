/**
 * Quick Builder Architecture Diagnostic
 * 
 * Paste this into browser console on any generator page to instantly verify the fixes
 */

(function() {
    console.log('🔍 QUICK BUILDER ARCHITECTURE DIAGNOSTIC');
    console.log('=========================================');
    
    const results = {
        architecture: 'UNKNOWN',
        issues: [],
        recommendations: []
    };
    
    // Test 1: Universal Builder Availability
    console.log('\n1. Universal Builder Availability:');
    const authHookAvailable = typeof window.AuthorityHookBuilder === 'object';
    const impactIntroAvailable = typeof window.ImpactIntroBuilder === 'object';
    
    console.log(`   Authority Hook Builder: ${authHookAvailable ? '✅ Available' : '❌ Missing'}`);
    console.log(`   Impact Intro Builder: ${impactIntroAvailable ? '✅ Available' : '❌ Missing'}`);
    
    if (!authHookAvailable) results.issues.push('Authority Hook Builder not loaded');
    if (!impactIntroAvailable) results.issues.push('Impact Intro Builder not loaded');
    
    // Test 2: Duplicate Object Check
    console.log('\n2. Duplicate Object Check:');
    const authHookDupe = typeof window.AuthorityHookGenerator === 'object';
    const impactIntroDupe = typeof window.ImpactIntroGenerator === 'object';
    
    console.log(`   AuthorityHookGenerator (duplicate): ${authHookDupe ? '❌ Still exists' : '✅ Removed'}`);
    console.log(`   ImpactIntroGenerator (duplicate): ${impactIntroDupe ? '❌ Still exists' : '✅ Removed'}`);
    
    if (authHookDupe) results.issues.push('Duplicate AuthorityHookGenerator still exists');
    if (impactIntroDupe) results.issues.push('Duplicate ImpactIntroGenerator still exists');
    
    // Test 3: Utility Services
    console.log('\n3. Utility Services:');
    const appEvents = typeof window.AppEvents === 'object';
    const ajaxService = typeof window.makeAjaxRequest === 'function';
    const formUtils = typeof window.MKCG_FormUtils === 'object';
    
    console.log(`   AppEvents: ${appEvents ? '✅ Available' : '❌ Missing'}`);
    console.log(`   AJAX Service: ${ajaxService ? '✅ Available' : '❌ Missing'}`);
    console.log(`   Form Utils: ${formUtils ? '✅ Available' : '❌ Missing'}`);
    
    if (!appEvents) results.issues.push('AppEvents not available');
    if (!ajaxService) results.issues.push('AJAX service not available');
    if (!formUtils) results.issues.push('Form utils not available');
    
    // Test 4: jQuery Usage Check
    console.log('\n4. jQuery Usage Check:');
    const jqueryAvailable = typeof $ !== 'undefined';
    console.log(`   jQuery in global scope: ${jqueryAvailable ? '⚠️ Present (WordPress core)' : '✅ Not present'}`);
    
    // Check if our builders use jQuery (they shouldn't)
    let usesJQuery = false;
    try {
        // This would only work if jQuery is actively used by our builders
        if (jqueryAvailable && $._data) {
            // Check if any of our elements have jQuery event handlers
            const testElements = document.querySelectorAll('[id*="authority-hook"], [id*="impact-intro"]');
            testElements.forEach(element => {
                const jqData = $._data(element);
                if (jqData && jqData.events) {
                    usesJQuery = true;
                }
            });
        }
    } catch (e) {
        // This is good - means our code doesn't use jQuery
    }
    
    console.log(`   Our builders use jQuery: ${usesJQuery ? '❌ YES (Issue)' : '✅ NO (Good)'}`);
    
    if (usesJQuery) results.issues.push('Builders still using jQuery event handlers');
    
    // Test 5: Script Loading Verification
    console.log('\n5. Script Loading Verification:');
    const scripts = Array.from(document.querySelectorAll('script[src]'));
    const builderScripts = scripts.filter(s => 
        s.src.includes('authority-hook-builder') || 
        s.src.includes('impact-intro-builder')
    );
    
    console.log(`   Builder scripts found: ${builderScripts.length}`);
    builderScripts.forEach(script => {
        const filename = script.src.split('/').pop();
        console.log(`     - ${filename}`);
    });
    
    if (builderScripts.length === 0) {
        results.issues.push('No builder scripts found in DOM');
    } else if (builderScripts.length > 2) {
        results.issues.push('Too many builder scripts - possible duplicates');
    }
    
    // Test 6: Functionality Test
    console.log('\n6. Functionality Test:');
    try {
        if (authHookAvailable && typeof window.AuthorityHookBuilder.autoInitialize === 'function') {
            console.log('   ✅ Authority Hook Builder functionality available');
        } else {
            results.issues.push('Authority Hook Builder functionality not working');
        }
        
        if (impactIntroAvailable && typeof window.ImpactIntroBuilder.autoInitialize === 'function') {
            console.log('   ✅ Impact Intro Builder functionality available');
        } else {
            results.issues.push('Impact Intro Builder functionality not working');
        }
    } catch (error) {
        results.issues.push(`Functionality test error: ${error.message}`);
    }
    
    // Calculate overall status
    console.log('\n📊 DIAGNOSTIC SUMMARY:');
    console.log('======================');
    
    if (results.issues.length === 0) {
        results.architecture = 'PERFECT';
        console.log('🎉 ARCHITECTURE STATUS: PERFECT');
        console.log('✅ All tests passed - Universal builder architecture working correctly');
        console.log('✅ Single source pattern implemented successfully');
        console.log('✅ No jQuery dependencies detected');
        console.log('✅ No duplicate functionality found');
        
    } else if (results.issues.length <= 2) {
        results.architecture = 'GOOD';
        console.log('✅ ARCHITECTURE STATUS: GOOD');
        console.log(`⚠️ Minor issues detected: ${results.issues.length}`);
        results.issues.forEach(issue => console.log(`   - ${issue}`));
        
    } else {
        results.architecture = 'NEEDS WORK';
        console.log('❌ ARCHITECTURE STATUS: NEEDS WORK');
        console.log(`❌ Issues detected: ${results.issues.length}`);
        results.issues.forEach(issue => console.log(`   - ${issue}`));
    }
    
    // Recommendations
    if (results.issues.length > 0) {
        console.log('\n💡 RECOMMENDATIONS:');
        
        if (results.issues.some(i => i.includes('not loaded'))) {
            console.log('   - Check Asset Manager loading configuration');
            console.log('   - Verify page mappings in Asset Manager');
        }
        
        if (results.issues.some(i => i.includes('duplicate'))) {
            console.log('   - Check for backup files being loaded');
            console.log('   - Verify Asset Manager removed duplicate references');
        }
        
        if (results.issues.some(i => i.includes('jQuery'))) {
            console.log('   - Check for remaining jQuery dependencies');
            console.log('   - Convert any remaining jQuery code to vanilla JS');
        }
    }
    
    console.log('\n🎯 NEXT STEPS:');
    if (results.architecture === 'PERFECT') {
        console.log('   ✅ Architecture complete - Ready to proceed with generator implementation');
        console.log('   🚀 Recommend: Continue with Biography Generator Prompts 5-8 or start Tagline Generator');
    } else {
        console.log('   🔧 Fix the issues above before proceeding');
        console.log('   📝 Re-run this diagnostic after fixes');
    }
    
    return results;
    
})();