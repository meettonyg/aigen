/**
 * CRITICAL DATA FLOW FIXES - COMPREHENSIVE TEST SUITE
 * 
 * Tests all implemented fixes for data synchronization between
 * Topics and Questions generators
 * 
 * FIXES TESTED:
 * ✅ Centralized Data Manager
 * ✅ Topics Generator Sync
 * ✅ Questions Generator Sync
 * ✅ Auto-save Functionality
 * ✅ Save Data Integrity
 */

window.MKCG_CriticalTestSuite = (function() {
    'use strict';
    
    const testResults = {
        total: 0,
        passed: 0,
        failed: 0,
        errors: []
    };
    
    // Test utilities
    function assert(condition, message) {
        testResults.total++;
        if (condition) {
            testResults.passed++;
            console.log(`%c✅ PASS: ${message}`, 'color: #27ae60; font-weight: bold;');
            return true;
        } else {
            testResults.failed++;
            testResults.errors.push(message);
            console.error(`%c❌ FAIL: ${message}`, 'color: #e74c3c; font-weight: bold;');
            return false;
        }
    }
    
    function wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // Test categories
    const tests = {
        
        /**
         * TEST CATEGORY 1: Centralized Data Manager
         */
        async testDataManager() {
            console.log('%c🎯 TESTING: Centralized Data Manager', 'color: #3498db; font-size: 16px; font-weight: bold;');
            
            // Test 1: Data Manager exists and is accessible
            assert(
                typeof window.MKCG_DataManager !== 'undefined',
                'MKCG_DataManager is globally accessible'
            );
            
            if (typeof window.MKCG_DataManager === 'undefined') {
                console.error('❌ CRITICAL: Data Manager not found - cannot continue tests');
                return;
            }
            
            // Test 2: Core methods exist
            const requiredMethods = ['init', 'getTopic', 'setTopic', 'on', 'trigger', 'getState'];
            requiredMethods.forEach(method => {
                assert(
                    typeof MKCG_DataManager[method] === 'function',
                    `DataManager.${method}() method exists`
                );
            });
            
            // Test 3: Data Manager initialization
            try {
                const initialState = MKCG_DataManager.getState();
                assert(
                    typeof initialState === 'object' && initialState.topics,
                    'Data Manager has valid state structure'
                );
            } catch (error) {
                assert(false, `Data Manager state access failed: ${error.message}`);
            }
            
            // Test 4: Topic data operations
            try {
                const testTopic = 'Test Topic for Data Flow Verification';
                MKCG_DataManager.setTopic(1, testTopic);
                const retrievedTopic = MKCG_DataManager.getTopic(1);
                
                assert(
                    retrievedTopic === testTopic,
                    'Data Manager can set and retrieve topic data'
                );
            } catch (error) {
                assert(false, `Topic data operations failed: ${error.message}`);
            }
            
            // Test 5: Event system
            let eventFired = false;
            try {
                MKCG_DataManager.on('topic:updated', (data) => {
                    eventFired = true;
                });
                
                MKCG_DataManager.setTopic(2, 'Event Test Topic');
                
                await wait(100); // Give event time to fire
                
                assert(
                    eventFired,
                    'Data Manager event system fires topic:updated events'
                );
            } catch (error) {
                assert(false, `Event system test failed: ${error.message}`);
            }
        },
        
        /**
         * TEST CATEGORY 2: Topics Generator Integration
         */
        async testTopicsGenerator() {
            console.log('%c📝 TESTING: Topics Generator Integration', 'color: #f39c12; font-size: 16px; font-weight: bold;');
            
            // Test 1: Topics Generator exists
            assert(
                typeof window.TopicsGenerator !== 'undefined',
                'Topics Generator is globally accessible'
            );
            
            if (typeof window.TopicsGenerator === 'undefined') {
                console.warn('⚠️ Topics Generator not found - skipping integration tests');
                return;
            }
            
            // Test 2: Data Manager integration methods exist
            const integrationMethods = ['initializeDataManager', 'handleExternalTopicUpdate', 'handleTopicSelectionChange'];
            integrationMethods.forEach(method => {
                assert(
                    typeof TopicsGenerator[method] === 'function',
                    `TopicsGenerator.${method}() method exists`
                );
            });
            
            // Test 3: Test auto-save integration
            const topicField = document.querySelector('#topics-generator-topic-field-1');
            if (topicField) {
                const originalValue = topicField.value;
                const testValue = 'Auto-save Integration Test Topic';
                
                topicField.value = testValue;
                
                // Trigger auto-save
                if (typeof TopicsGenerator.autoSaveField === 'function') {
                    try {
                        TopicsGenerator.autoSaveField(topicField);
                        
                        // Check if centralized data was updated
                        await wait(200);
                        const centralizedValue = MKCG_DataManager.getTopic(1);
                        
                        assert(
                            centralizedValue === testValue,
                            'Topics Generator auto-save updates centralized data'
                        );
                        
                        // Restore original value
                        topicField.value = originalValue;
                    } catch (error) {
                        assert(false, `Auto-save integration test failed: ${error.message}`);
                    }
                } else {
                    assert(false, 'TopicsGenerator.autoSaveField() method not found');
                }
            } else {
                console.warn('⚠️ Topic field not found - skipping auto-save test');
            }
        },
        
        /**
         * TEST CATEGORY 3: Questions Generator Integration
         */
        async testQuestionsGenerator() {
            console.log('%c❓ TESTING: Questions Generator Integration', 'color: #9b59b6; font-size: 16px; font-weight: bold;');
            
            // Test 1: Questions Generator exists
            assert(
                typeof window.QuestionsGenerator !== 'undefined',
                'Questions Generator is globally accessible'
            );
            
            if (typeof window.QuestionsGenerator === 'undefined') {
                console.warn('⚠️ Questions Generator not found - skipping integration tests');
                return;
            }
            
            // Test 2: Data Manager integration methods exist
            const integrationMethods = ['initializeCentralizedDataManager', 'handleTopicUpdate', 'syncLatestTopicData'];
            integrationMethods.forEach(method => {
                assert(
                    typeof QuestionsGenerator[method] === 'function',
                    `QuestionsGenerator.${method}() method exists`
                );
            });
            
            // Test 3: Topic sync functionality
            try {
                const testTopic = 'Questions Generator Sync Test';
                MKCG_DataManager.setTopic(3, testTopic);
                
                // Test sync method
                QuestionsGenerator.syncLatestTopicData();
                
                // Check if local data was updated
                const localTopic = QuestionsGenerator.topicsData[3];
                assert(
                    localTopic === testTopic,
                    'Questions Generator syncs latest topic data from centralized manager'
                );
            } catch (error) {
                assert(false, `Topic sync test failed: ${error.message}`);
            }
            
            // Test 4: Heading update with centralized data
            try {
                QuestionsGenerator.selectedTopicId = 3;
                QuestionsGenerator.selectedTopicText = 'Old Topic Text';
                
                const testTopic = 'Heading Update Test Topic';
                MKCG_DataManager.setTopic(3, testTopic);
                
                // Test heading update (should pull from centralized data)
                QuestionsGenerator.updateSelectedTopicHeading();
                
                // Check if heading was updated with centralized data
                const questionsHeading = document.querySelector('#mkcg-questions-heading');
                if (questionsHeading) {
                    const headingIncludesNewTopic = questionsHeading.textContent.includes(testTopic);
                    assert(
                        headingIncludesNewTopic,
                        'Questions Generator heading updates with centralized data'
                    );
                } else {
                    console.warn('⚠️ Questions heading element not found - skipping heading test');
                }
            } catch (error) {
                assert(false, `Heading update test failed: ${error.message}`);
            }
        },
        
        /**
         * TEST CATEGORY 4: Cross-Generator Communication
         */
        async testCrossGeneratorSync() {
            console.log('%c🔄 TESTING: Cross-Generator Communication', 'color: #e67e22; font-size: 16px; font-weight: bold;');
            
            // Test 1: Topic update propagation
            let questionsReceivedUpdate = false;
            
            if (typeof QuestionsGenerator.handleTopicUpdate === 'function') {
                // Mock the handler to detect if it's called
                const originalHandler = QuestionsGenerator.handleTopicUpdate;
                QuestionsGenerator.handleTopicUpdate = function(data) {
                    questionsReceivedUpdate = true;
                    originalHandler.call(this, data);
                };
                
                // Update topic from Topics Generator perspective
                MKCG_DataManager.setTopic(4, 'Cross-Generator Communication Test');
                
                await wait(100);
                
                assert(
                    questionsReceivedUpdate,
                    'Questions Generator receives topic updates from Topics Generator'
                );
                
                // Restore original handler
                QuestionsGenerator.handleTopicUpdate = originalHandler;
            }
            
            // Test 2: Data consistency across generators
            const testTopic = 'Data Consistency Test Topic';
            MKCG_DataManager.setTopic(5, testTopic);
            
            // Check if both generators see the same data
            if (typeof TopicsGenerator !== 'undefined' && typeof QuestionsGenerator !== 'undefined') {
                QuestionsGenerator.syncLatestTopicData();
                
                const questionsData = QuestionsGenerator.topicsData[5];
                const centralizedData = MKCG_DataManager.getTopic(5);
                
                assert(
                    questionsData === centralizedData && centralizedData === testTopic,
                    'Both generators maintain data consistency through centralized manager'
                );
            }
        },
        
        /**
         * TEST CATEGORY 5: Save Data Integrity
         */
        async testSaveDataIntegrity() {
            console.log('%c💾 TESTING: Save Data Integrity', 'color: #27ae60; font-size: 16px; font-weight: bold;');
            
            // Test 1: Pre-save data sync
            if (typeof QuestionsGenerator.syncLatestTopicData === 'function') {
                try {
                    // Set different data in centralized manager vs local
                    const centralizedTopic = 'Latest Centralized Topic';
                    const outdatedTopic = 'Outdated Local Topic';
                    
                    MKCG_DataManager.setTopic(1, centralizedTopic);
                    QuestionsGenerator.topicsData[1] = outdatedTopic;
                    
                    // Run sync (this is called before save)
                    QuestionsGenerator.syncLatestTopicData();
                    
                    // Check if local data was updated to match centralized
                    const syncedData = QuestionsGenerator.topicsData[1];
                    assert(
                        syncedData === centralizedTopic,
                        'Save process syncs latest data before saving'
                    );
                } catch (error) {
                    assert(false, `Save data sync test failed: ${error.message}`);
                }
            }
            
            // Test 2: Data validation before save
            if (typeof QuestionsGenerator.validateGenerationInput === 'function') {
                try {
                    // Test with valid data
                    QuestionsGenerator.selectedTopicText = 'Valid topic for testing validation';
                    const validValidation = QuestionsGenerator.validateGenerationInput();
                    
                    assert(
                        validValidation.valid === true,
                        'Save validation accepts valid topic data'
                    );
                    
                    // Test with invalid data
                    QuestionsGenerator.selectedTopicText = 'short';
                    const invalidValidation = QuestionsGenerator.validateGenerationInput();
                    
                    assert(
                        invalidValidation.valid === false,
                        'Save validation rejects invalid topic data'
                    );
                } catch (error) {
                    assert(false, `Save validation test failed: ${error.message}`);
                }
            }
        },
        
        /**
         * TEST CATEGORY 6: Error Recovery
         */
        async testErrorRecovery() {
            console.log('%c🛡️ TESTING: Error Recovery', 'color: #e74c3c; font-size: 16px; font-weight: bold;');
            
            // Test 1: Graceful handling when Data Manager is unavailable
            const originalDataManager = window.MKCG_DataManager;
            window.MKCG_DataManager = undefined;
            
            try {
                if (typeof QuestionsGenerator.syncLatestTopicData === 'function') {
                    QuestionsGenerator.syncLatestTopicData(); // Should not throw error
                    assert(true, 'Gracefully handles missing Data Manager');
                }
            } catch (error) {
                assert(false, `Error recovery failed: ${error.message}`);
            }
            
            // Restore Data Manager
            window.MKCG_DataManager = originalDataManager;
            
            // Test 2: Invalid topic ID handling
            try {
                const result = MKCG_DataManager.getTopic(999); // Invalid ID
                assert(
                    result === null || result === '',
                    'Gracefully handles invalid topic IDs'
                );
            } catch (error) {
                assert(false, `Invalid topic ID handling failed: ${error.message}`);
            }
        }
    };
    
    // Main test runner
    async function runAllTests() {
        console.log('%c🚀 STARTING CRITICAL DATA FLOW FIXES TEST SUITE', 'color: #2c3e50; font-size: 18px; font-weight: bold; background: #ecf0f1; padding: 10px;');
        console.log('Testing all implemented fixes for data synchronization issues...\n');
        
        const startTime = performance.now();
        
        // Reset test results
        testResults.total = 0;
        testResults.passed = 0;
        testResults.failed = 0;
        testResults.errors = [];
        
        // Run all test categories
        await tests.testDataManager();
        await tests.testTopicsGenerator();
        await tests.testQuestionsGenerator();
        await tests.testCrossGeneratorSync();
        await tests.testSaveDataIntegrity();
        await tests.testErrorRecovery();
        
        const endTime = performance.now();
        const duration = (endTime - startTime).toFixed(2);
        
        // Print results
        console.log('\n' + '='.repeat(60));
        console.log('%c📊 TEST RESULTS SUMMARY', 'color: #2c3e50; font-size: 16px; font-weight: bold;');
        console.log('='.repeat(60));
        
        const successRate = ((testResults.passed / testResults.total) * 100).toFixed(1);
        const resultColor = successRate >= 95 ? '#27ae60' : successRate >= 80 ? '#f39c12' : '#e74c3c';
        
        console.log(`%cTotal Tests: ${testResults.total}`, 'font-weight: bold;');
        console.log(`%c✅ Passed: ${testResults.passed}`, 'color: #27ae60; font-weight: bold;');
        console.log(`%c❌ Failed: ${testResults.failed}`, 'color: #e74c3c; font-weight: bold;');
        console.log(`%c📈 Success Rate: ${successRate}%`, `color: ${resultColor}; font-size: 18px; font-weight: bold;`);
        console.log(`⏱️ Duration: ${duration}ms`);
        
        // Show failed tests
        if (testResults.failed > 0) {
            console.log('\n%c❌ FAILED TESTS:', 'color: #e74c3c; font-weight: bold;');
            testResults.errors.forEach((error, index) => {
                console.log(`${index + 1}. ${error}`);
            });
        }
        
        // Overall assessment
        console.log('\n' + '='.repeat(60));
        if (successRate >= 95) {
            console.log('%c🎉 EXCELLENT! Critical fixes are working correctly.', 'color: #27ae60; font-size: 16px; font-weight: bold;');
        } else if (successRate >= 80) {
            console.log('%c⚠️ GOOD but some issues need attention.', 'color: #f39c12; font-size: 16px; font-weight: bold;');
        } else {
            console.log('%c❌ CRITICAL ISSUES DETECTED! Fixes need immediate attention.', 'color: #e74c3c; font-size: 16px; font-weight: bold;');
        }
        console.log('='.repeat(60));
        
        return {
            success: successRate >= 95,
            results: testResults,
            duration: duration
        };
    }
    
    // Quick test function
    function quickTest() {
        console.log('%c⚡ QUICK TEST: Critical Data Flow', 'color: #3498db; font-weight: bold;');
        
        const checks = [
            {
                name: 'Data Manager Available',
                test: () => typeof window.MKCG_DataManager !== 'undefined'
            },
            {
                name: 'Topics Generator Available',
                test: () => typeof window.TopicsGenerator !== 'undefined'
            },
            {
                name: 'Questions Generator Available',
                test: () => typeof window.QuestionsGenerator !== 'undefined'
            },
            {
                name: 'Data Manager Set/Get Works',
                test: () => {
                    try {
                        MKCG_DataManager.setTopic(1, 'Quick Test');
                        return MKCG_DataManager.getTopic(1) === 'Quick Test';
                    } catch (e) {
                        return false;
                    }
                }
            }
        ];
        
        let passed = 0;
        checks.forEach(check => {
            const result = check.test();
            if (result) {
                console.log(`%c✅ ${check.name}`, 'color: #27ae60;');
                passed++;
            } else {
                console.log(`%c❌ ${check.name}`, 'color: #e74c3c;');
            }
        });
        
        const rate = (passed / checks.length * 100).toFixed(0);
        console.log(`%cQuick Test: ${passed}/${checks.length} (${rate}%)`, 
                   rate >= 75 ? 'color: #27ae60; font-weight: bold;' : 'color: #e74c3c; font-weight: bold;');
    }
    
    // Public API
    return {
        runAllTests,
        quickTest,
        getResults: () => testResults
    };
})();

// Console shortcuts
console.log('%c🧪 CRITICAL DATA FLOW TEST SUITE LOADED', 'color: #2c3e50; font-weight: bold;');
console.log('Available commands:');
console.log('• MKCG_CriticalTestSuite.runAllTests() - Run comprehensive test suite');
console.log('• MKCG_CriticalTestSuite.quickTest() - Run quick validation');
console.log('• testCriticalFixes() - Shortcut for comprehensive tests');
console.log('• quickTestFixes() - Shortcut for quick test');

// Global shortcuts
window.testCriticalFixes = () => MKCG_CriticalTestSuite.runAllTests();
window.quickTestFixes = () => MKCG_CriticalTestSuite.quickTest();