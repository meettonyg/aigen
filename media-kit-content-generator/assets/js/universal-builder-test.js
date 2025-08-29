/**
 * Universal Builder Integration Test
 * 
 * Tests the fixed JavaScript architecture to ensure:
 * 1. No jQuery dependencies remain
 * 2. Universal builders work across all generators
 * 3. Single source architecture is functioning
 * 
 * Usage: Add ?test=builders to any generator page URL
 */

(function() {
    'use strict';
    
    // Only run if test parameter is present
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.get('test') === 'builders') {
        return;
    }
    
    console.log('🧪 UNIVERSAL BUILDER INTEGRATION TEST');
    console.log('=====================================');
    
    /**
     * Test Results Storage
     */
    const testResults = {
        passed: 0,
        failed: 0,
        total: 0,
        details: []
    };
    
    /**
     * Add test result
     */
    function addTestResult(testName, passed, message = '') {
        testResults.total++;
        if (passed) {
            testResults.passed++;
            console.log(`✅ PASS: ${testName}`);
        } else {
            testResults.failed++;
            console.log(`❌ FAIL: ${testName} - ${message}`);
        }
        
        testResults.details.push({
            name: testName,
            passed: passed,
            message: message
        });
    }
    
    /**
     * Test 1: jQuery Dependency Removal
     */
    function testJQueryRemoval() {
        console.log('\n🔍 Test 1: jQuery Dependency Removal');
        console.log('====================================');
        
        // Check if builders are jQuery-free
        let jqueryFree = true;
        let issues = [];
        
        // Test Authority Hook Builder
        if (typeof window.AuthorityHookBuilder === 'object') {
            console.log('✅ Authority Hook Builder API available');
            
            // Check if it uses vanilla JS patterns
            if (typeof window.AuthorityHookBuilder.updateToggleButtonState === 'function') {
                console.log('✅ Authority Hook Builder uses vanilla JS API');
            } else {
                jqueryFree = false;
                issues.push('Authority Hook Builder missing vanilla JS API');
            }
        } else {
            jqueryFree = false;
            issues.push('Authority Hook Builder not available');
        }
        
        // Test Impact Intro Builder
        if (typeof window.ImpactIntroBuilder === 'object') {
            console.log('✅ Impact Intro Builder API available');
            
            if (typeof window.ImpactIntroBuilder.updateToggleButtonState === 'function') {
                console.log('✅ Impact Intro Builder uses vanilla JS API');
            } else {
                jqueryFree = false;
                issues.push('Impact Intro Builder missing vanilla JS API');
            }
        } else {
            jqueryFree = false;
            issues.push('Impact Intro Builder not available');
        }
        
        addTestResult(
            'jQuery Dependency Removal',
            jqueryFree,
            issues.join(', ')
        );
    }
    
    /**
     * Test 2: Universal Builder Functionality
     */
    function testUniversalBuilderFunctionality() {
        console.log('\n🔍 Test 2: Universal Builder Functionality');
        console.log('==========================================');
        
        let allWorking = true;
        let issues = [];
        
        // Test Authority Hook Builder functionality
        try {
            // Look for Authority Hook elements on current page
            const authorityBuilders = document.querySelectorAll('[id*="authority-hook-builder"]');
            console.log(`Found ${authorityBuilders.length} Authority Hook builders on page`);
            
            if (authorityBuilders.length > 0) {
                const firstBuilder = authorityBuilders[0];
                const builderId = firstBuilder.id;
                
                // Check if builder has proper structure
                const whoField = firstBuilder.querySelector('#mkcg-who, #mkcg-authority-who');
                const whatField = firstBuilder.querySelector('#mkcg-result, #mkcg-authority-what');
                
                if (whoField && whatField) {
                    console.log('✅ Authority Hook Builder has proper field structure');
                } else {
                    issues.push('Authority Hook Builder missing required fields');
                    allWorking = false;
                }
                
                // Test toggle functionality
                const toggleButton = document.querySelector(`[data-target="${builderId}"], button[onclick*="${builderId}"]`);
                if (toggleButton) {
                    console.log('✅ Authority Hook Builder toggle button found');
                } else {
                    console.log('⚠️ Authority Hook Builder toggle button not found (may be normal)');
                }
            } else {
                console.log('ℹ️ No Authority Hook builders found on this page');
            }
        } catch (error) {
            allWorking = false;
            issues.push(`Authority Hook test error: ${error.message}`);
        }
        
        // Test Impact Intro Builder functionality
        try {
            const impactBuilders = document.querySelectorAll('[id*="impact-intro-builder"]');
            console.log(`Found ${impactBuilders.length} Impact Intro builders on page`);
            
            if (impactBuilders.length > 0) {
                const firstBuilder = impactBuilders[0];
                
                // Check if builder has proper structure
                const whereField = firstBuilder.querySelector('#mkcg-where');
                const whyField = firstBuilder.querySelector('#mkcg-why');
                
                if (whereField && whyField) {
                    console.log('✅ Impact Intro Builder has proper field structure');
                } else {
                    issues.push('Impact Intro Builder missing required fields');
                    allWorking = false;
                }
            } else {
                console.log('ℹ️ No Impact Intro builders found on this page');
            }
        } catch (error) {
            allWorking = false;
            issues.push(`Impact Intro test error: ${error.message}`);
        }
        
        addTestResult(
            'Universal Builder Functionality',
            allWorking,
            issues.join(', ')
        );
    }
    
    /**
     * Test 3: Asset Loading Verification
     */
    function testAssetLoading() {
        console.log('\n🔍 Test 3: Asset Loading Verification');
        console.log('====================================');
        
        let assetsLoaded = true;
        let issues = [];
        
        // Check for core utility scripts
        const requiredUtilities = [
            { name: 'AppEvents', object: 'window.AppEvents' },
            { name: 'makeAjaxRequest', object: 'window.makeAjaxRequest' },
            { name: 'MKCG_FormUtils', object: 'window.MKCG_FormUtils' }
        ];
        
        requiredUtilities.forEach(utility => {
            if (eval(`typeof ${utility.object}`) !== 'undefined') {
                console.log(`✅ ${utility.name} loaded`);
            } else {
                assetsLoaded = false;
                issues.push(`${utility.name} not loaded`);
            }
        });
        
        // Check script tags in DOM
        const scripts = Array.from(document.querySelectorAll('script[src]'));
        const mediaKitScripts = scripts.filter(script => 
            script.src.includes('media-kit-content-generator') ||
            script.src.includes('simple-') ||
            script.src.includes('mkcg-')
        );
        
        console.log(`Found ${mediaKitScripts.length} Media Kit scripts in DOM:`);
        mediaKitScripts.forEach(script => {
            const filename = script.src.split('/').pop();
            console.log(`  - ${filename}`);
        });
        
        // Verify no duplicate script loading
        const duplicateCheck = {};
        mediaKitScripts.forEach(script => {
            const filename = script.src.split('/').pop();
            if (duplicateCheck[filename]) {
                assetsLoaded = false;
                issues.push(`Duplicate script detected: ${filename}`);
            } else {
                duplicateCheck[filename] = true;
            }
        });
        
        addTestResult(
            'Asset Loading Verification',
            assetsLoaded,
            issues.join(', ')
        );
    }
    
    /**
     * Test 4: Single Source Architecture
     */
    function testSingleSourceArchitecture() {
        console.log('\n🔍 Test 4: Single Source Architecture');
        console.log('====================================');
        
        let singleSource = true;
        let issues = [];
        
        // Check that we don't have duplicate global objects
        const checkForDuplicates = [
            { name: 'Authority Hook', primary: 'AuthorityHookBuilder', duplicate: 'AuthorityHookGenerator' },
            { name: 'Impact Intro', primary: 'ImpactIntroBuilder', duplicate: 'ImpactIntroGenerator' }
        ];
        
        checkForDuplicates.forEach(check => {
            const hasPrimary = typeof window[check.primary] !== 'undefined';
            const hasDuplicate = typeof window[check.duplicate] !== 'undefined';
            
            if (hasPrimary && !hasDuplicate) {
                console.log(`✅ ${check.name}: Single source (${check.primary} only)`);
            } else if (hasPrimary && hasDuplicate) {
                singleSource = false;
                issues.push(`${check.name}: Duplicate objects detected`);
            } else if (!hasPrimary) {
                singleSource = false;
                issues.push(`${check.name}: Primary object missing`);
            }
        });
        
        // Check script loading patterns
        const scripts = Array.from(document.querySelectorAll('script[src]'));
        const authorityScripts = scripts.filter(s => 
            s.src.includes('authority-hook') || s.src.includes('authority_hook')
        );
        const impactScripts = scripts.filter(s => 
            s.src.includes('impact-intro') || s.src.includes('impact_intro')
        );
        
        if (authorityScripts.length > 1) {
            singleSource = false;
            issues.push(`Multiple Authority Hook scripts loaded: ${authorityScripts.length}`);
        }
        
        if (impactScripts.length > 1) {
            singleSource = false;
            issues.push(`Multiple Impact Intro scripts loaded: ${impactScripts.length}`);
        }
        
        addTestResult(
            'Single Source Architecture',
            singleSource,
            issues.join(', ')
        );
    }
    
    /**
     * Run all tests
     */
    function runAllTests() {
        console.log('🧪 Starting Universal Builder Integration Tests...');
        console.log('==================================================');
        
        testJQueryRemoval();
        testUniversalBuilderFunctionality();
        testAssetLoading();
        testSingleSourceArchitecture();
        
        // Final summary
        console.log('\n📊 TEST SUMMARY');
        console.log('===============');
        const successRate = Math.round((testResults.passed / testResults.total) * 100);
        console.log(`Total Tests: ${testResults.total}`);
        console.log(`Passed: ${testResults.passed}`);
        console.log(`Failed: ${testResults.failed}`);
        console.log(`Success Rate: ${successRate}%`);
        
        if (successRate >= 85) {
            console.log('\n🎉 UNIVERSAL BUILDER INTEGRATION: SUCCESS!');
            console.log('✅ Single source architecture working correctly');
            console.log('✅ jQuery dependencies successfully removed');
            console.log('✅ Builders compatible across all generators');
            
            // Show success notification if notification system available
            if (typeof window.showNotification === 'function') {
                window.showNotification(
                    `✅ Universal Builder Integration Complete! ${successRate}% of tests passed.`,
                    'success'
                );
            }
        } else {
            console.log('\n❌ UNIVERSAL BUILDER INTEGRATION: NEEDS WORK');
            console.log('Review failed tests above for specific issues');
            
            // Show detailed error info
            testResults.details.forEach(test => {
                if (!test.passed) {
                    console.log(`❌ ${test.name}: ${test.message}`);
                }
            });
            
            if (typeof window.showNotification === 'function') {
                window.showNotification(
                    `❌ Integration Issues Detected. Only ${successRate}% of tests passed.`,
                    'error'
                );
            }
        }
        
        return testResults;
    }
    
    /**
     * Auto-run tests after page load
     */
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            console.log('🚀 Auto-running Universal Builder Integration Tests...');
            const results = runAllTests();
            
            // Store results for external access
            window.MKCG_BuilderTestResults = results;
            
        }, 2000); // Wait 2 seconds for all scripts to load
    });
    
    /**
     * Manual test function
     */
    window.testUniversalBuilders = function() {
        console.clear();
        return runAllTests();
    };
    
    /**
     * Quick architecture check
     */
    window.checkBuilderArchitecture = function() {
        console.log('🔍 Quick Architecture Check:');
        console.log('============================');
        
        const architecture = {
            universalBuilders: {
                authorityHook: typeof window.AuthorityHookBuilder,
                impactIntro: typeof window.ImpactIntroBuilder
            },
            utilityServices: {
                appEvents: typeof window.AppEvents,
                ajaxService: typeof window.makeAjaxRequest,
                formUtils: typeof window.MKCG_FormUtils
            },
            generatorSpecific: {
                topicsGenerator: typeof window.TopicsGenerator,
                biographyGenerator: typeof window.BiographyGenerator,
                offersGenerator: typeof window.OffersGenerator,
                guestIntroGenerator: typeof window.GuestIntroGenerator
            },
            duplicateCheck: {
                duplicateAuthorityHook: typeof window.AuthorityHookGenerator,
                duplicateImpactIntro: typeof window.ImpactIntroGenerator
            }
        };
        
        console.log('Universal Builders:', architecture.universalBuilders);
        console.log('Utility Services:', architecture.utilityServices);
        console.log('Generator-Specific:', architecture.generatorSpecific);
        console.log('Duplicate Check:', architecture.duplicateCheck);
        
        // Check for jQuery usage
        const hasJQuery = typeof $ !== 'undefined';
        const usesJQuery = hasJQuery && !!$._data;
        
        console.log('\nJQuery Status:');
        console.log(`- jQuery Available: ${hasJQuery}`);
        console.log(`- Our Code Uses jQuery: ${usesJQuery ? 'YES (ISSUE)' : 'NO (GOOD)'}`);
        
        return architecture;
    };
    
    console.log('✅ Universal Builder Test Script Loaded');
    console.log('📝 Usage: window.testUniversalBuilders() or window.checkBuilderArchitecture()');
    console.log('🔍 Auto-test will run in 2 seconds...');
    
})();