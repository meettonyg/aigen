/**
 * Cross-Browser Compatibility Test for Phase 2 Simplifications
 * Tests JavaScript functionality across different browsers and environments
 */

class CrossBrowserCompatibilityTest {
    constructor() {
        this.browserInfo = this.detectBrowser();
        this.features = {};
        this.testResults = [];
        this.compatibilityScore = 0;
    }
    
    /**
     * Detect browser information
     */
    detectBrowser() {
        const userAgent = navigator.userAgent;
        const vendor = navigator.vendor;
        
        let browser = 'Unknown';
        let version = 'Unknown';
        
        // Detect browser
        if (userAgent.includes('Chrome') && vendor.includes('Google')) {
            browser = 'Chrome';
            version = userAgent.match(/Chrome\/(\d+)/)?.[1] || 'Unknown';
        } else if (userAgent.includes('Firefox')) {
            browser = 'Firefox';
            version = userAgent.match(/Firefox\/(\d+)/)?.[1] || 'Unknown';
        } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
            browser = 'Safari';
            version = userAgent.match(/Version\/(\d+)/)?.[1] || 'Unknown';
        } else if (userAgent.includes('Edge')) {
            browser = 'Edge';
            version = userAgent.match(/Edge\/(\d+)/)?.[1] || 'Unknown';
        } else if (userAgent.includes('Trident') || userAgent.includes('MSIE')) {
            browser = 'Internet Explorer';
            version = userAgent.match(/(?:MSIE |rv:)(\d+)/)?.[1] || 'Unknown';
        }
        
        // Detect mobile
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent);
        
        return {
            browser,
            version,
            userAgent,
            isMobile,
            platform: navigator.platform,
            language: navigator.language,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine
        };
    }
    
    /**
     * Test JavaScript features required for Phase 2
     */
    testJavaScriptFeatures() {
        console.log('🔍 Testing JavaScript features compatibility...');
        
        const tests = [
            {
                name: 'Fetch API',
                test: () => typeof fetch === 'function',
                required: true,
                description: 'Required for simplified AJAX system'
            },
            {
                name: 'URLSearchParams',
                test: () => typeof URLSearchParams === 'function',
                required: true,
                description: 'Required for WordPress AJAX data formatting'
            },
            {
                name: 'Promises',
                test: () => typeof Promise === 'function',
                required: true,
                description: 'Required for async/await in AJAX calls'
            },
            {
                name: 'Arrow Functions',
                test: () => {
                    try {
                        new Function('() => {}');
                        return true;
                    } catch (e) {
                        return false;
                    }
                },
                required: true,
                description: 'Used throughout simplified code'
            },
            {
                name: 'const/let',
                test: () => {
                    try {
                        new Function('const x = 1; let y = 2;');
                        return true;
                    } catch (e) {
                        return false;
                    }
                },
                required: true,
                description: 'Modern variable declarations'
            },
            {
                name: 'Template Literals',
                test: () => {
                    try {
                        new Function('`template ${string}`');
                        return true;
                    } catch (e) {
                        return false;
                    }
                },
                required: false,
                description: 'Used for string formatting'
            },
            {
                name: 'Destructuring',
                test: () => {
                    try {
                        new Function('const {a} = {a: 1};');
                        return true;
                    } catch (e) {
                        return false;
                    }
                },
                required: false,
                description: 'Used in some function parameters'
            },
            {
                name: 'Object.assign',
                test: () => typeof Object.assign === 'function',
                required: false,
                description: 'Used for object merging'
            },
            {
                name: 'Array.from',
                test: () => typeof Array.from === 'function',
                required: false,
                description: 'Used for NodeList conversion'
            },
            {
                name: 'addEventListener',
                test: () => typeof document.addEventListener === 'function',
                required: true,
                description: 'Required for event binding'
            },
            {
                name: 'querySelector',
                test: () => typeof document.querySelector === 'function',
                required: true,
                description: 'Required for DOM manipulation'
            },
            {
                name: 'JSON',
                test: () => typeof JSON === 'object' && typeof JSON.parse === 'function',
                required: true,
                description: 'Required for AJAX data parsing'
            }
        ];
        
        this.features = {};
        let passedRequired = 0;
        let totalRequired = 0;
        let passedOptional = 0;
        let totalOptional = 0;
        
        tests.forEach(test => {
            const supported = test.test();
            this.features[test.name] = {
                supported,
                required: test.required,
                description: test.description
            };
            
            if (test.required) {
                totalRequired++;
                if (supported) passedRequired++;
            } else {
                totalOptional++;
                if (supported) passedOptional++;
            }
            
            this.testResults.push({
                category: 'JavaScript Features',
                test: test.name,
                result: supported ? 'PASS' : 'FAIL',
                required: test.required,
                description: test.description
            });
        });
        
        const requiredScore = totalRequired > 0 ? (passedRequired / totalRequired) * 100 : 100;
        const optionalScore = totalOptional > 0 ? (passedOptional / totalOptional) * 100 : 100;
        
        console.log(`JavaScript Features: ${passedRequired}/${totalRequired} required, ${passedOptional}/${totalOptional} optional`);
        
        return {
            requiredScore,
            optionalScore,
            overallScore: (requiredScore * 0.8) + (optionalScore * 0.2)
        };
    }
    
    /**
     * Test CSS features required for notifications
     */
    testCSSFeatures() {
        console.log('🎨 Testing CSS features compatibility...');
        
        const testElement = document.createElement('div');
        document.body.appendChild(testElement);
        
        const tests = [
            {
                name: 'CSS Transforms',
                test: () => 'transform' in testElement.style,
                required: false,
                description: 'Used for notification animations'
            },
            {
                name: 'CSS Transitions',
                test: () => 'transition' in testElement.style,
                required: false,
                description: 'Used for smooth UI effects'
            },
            {
                name: 'CSS Animations',
                test: () => 'animation' in testElement.style,
                required: false,
                description: 'Used for notification slide-in effects'
            },
            {
                name: 'Flexbox',
                test: () => {
                    testElement.style.display = 'flex';
                    return window.getComputedStyle(testElement).display === 'flex';
                },
                required: false,
                description: 'Used for notification layout'
            },
            {
                name: 'CSS Grid',
                test: () => {
                    testElement.style.display = 'grid';
                    return window.getComputedStyle(testElement).display === 'grid';
                },
                required: false,
                description: 'Used in test interfaces'
            },
            {
                name: 'Box Shadow',
                test: () => 'boxShadow' in testElement.style,
                required: false,
                description: 'Used for notification styling'
            },
            {
                name: 'Border Radius',
                test: () => 'borderRadius' in testElement.style,
                required: false,
                description: 'Used for rounded corners'
            }
        ];
        
        let passedTests = 0;
        
        tests.forEach(test => {
            const supported = test.test();
            if (supported) passedTests++;
            
            this.testResults.push({
                category: 'CSS Features',
                test: test.name,
                result: supported ? 'PASS' : 'FAIL',
                required: test.required,
                description: test.description
            });
        });
        
        document.body.removeChild(testElement);
        
        const score = (passedTests / tests.length) * 100;
        console.log(`CSS Features: ${passedTests}/${tests.length} supported`);
        
        return score;
    }
    
    /**
     * Test performance APIs
     */
    testPerformanceAPIs() {
        console.log('⚡ Testing performance APIs...');
        
        const tests = [
            {
                name: 'Performance.now()',
                test: () => typeof performance !== 'undefined' && typeof performance.now === 'function',
                description: 'Used for timing measurements'
            },
            {
                name: 'Performance.memory',
                test: () => typeof performance !== 'undefined' && 'memory' in performance,
                description: 'Used for memory usage monitoring'
            },
            {
                name: 'Performance.timing',
                test: () => typeof performance !== 'undefined' && 'timing' in performance,
                description: 'Used for page load timing'
            },
            {
                name: 'Console.time',
                test: () => typeof console !== 'undefined' && typeof console.time === 'function',
                description: 'Used for debugging timing'
            }
        ];
        
        let passedTests = 0;
        
        tests.forEach(test => {
            const supported = test.test();
            if (supported) passedTests++;
            
            this.testResults.push({
                category: 'Performance APIs',
                test: test.name,
                result: supported ? 'PASS' : 'FAIL',
                required: false,
                description: test.description
            });
        });
        
        const score = (passedTests / tests.length) * 100;
        console.log(`Performance APIs: ${passedTests}/${tests.length} supported`);
        
        return score;
    }
    
    /**
     * Test Phase 2 specific functionality
     */
    testPhase2Functionality() {
        console.log('🚀 Testing Phase 2 specific functionality...');
        
        const tests = [
            {
                name: 'makeAjaxRequest function',
                test: () => typeof makeAjaxRequest === 'function',
                required: true,
                description: 'Core AJAX function from simple-ajax.js'
            },
            {
                name: 'showNotification function',
                test: () => typeof showNotification === 'function',
                required: true,
                description: 'Core notification function'
            },
            {
                name: 'SimpleNotifications object',
                test: () => typeof SimpleNotifications === 'object' && SimpleNotifications !== null,
                required: true,
                description: 'Notification system object'
            },
            {
                name: 'TopicsGenerator object',
                test: () => typeof TopicsGenerator === 'object' && TopicsGenerator !== null,
                required: false,
                description: 'Topics Generator main object'
            },
            {
                name: 'AJAX URL configuration',
                test: () => typeof ajaxurl === 'string' || (typeof window.mkcg_vars === 'object' && window.mkcg_vars.ajax_url),
                required: true,
                description: 'WordPress AJAX URL configuration'
            },
            {
                name: 'Nonce configuration',
                test: () => (typeof window.mkcg_vars === 'object' && window.mkcg_vars.nonce) || 
                           document.querySelector('input[name*="nonce"]'),
                required: true,
                description: 'WordPress nonce for security'
            }
        ];
        
        let passedRequired = 0;
        let totalRequired = 0;
        let passedOptional = 0;
        let totalOptional = 0;
        
        tests.forEach(test => {
            const supported = test.test();
            
            if (test.required) {
                totalRequired++;
                if (supported) passedRequired++;
            } else {
                totalOptional++;
                if (supported) passedOptional++;
            }
            
            this.testResults.push({
                category: 'Phase 2 Functionality',
                test: test.name,
                result: supported ? 'PASS' : 'FAIL',
                required: test.required,
                description: test.description
            });
        });
        
        const requiredScore = totalRequired > 0 ? (passedRequired / totalRequired) * 100 : 100;
        const optionalScore = totalOptional > 0 ? (passedOptional / totalOptional) * 100 : 100;
        
        console.log(`Phase 2 Functionality: ${passedRequired}/${totalRequired} required, ${passedOptional}/${totalOptional} optional`);
        
        return {
            requiredScore,
            optionalScore,
            overallScore: (requiredScore * 0.9) + (optionalScore * 0.1)
        };
    }
    
    /**
     * Run all compatibility tests
     */
    async runAllTests() {
        console.log(`🔍 Starting cross-browser compatibility tests for ${this.browserInfo.browser} ${this.browserInfo.version}`);
        
        const jsResults = this.testJavaScriptFeatures();
        const cssResults = this.testCSSFeatures();
        const perfResults = this.testPerformanceAPIs();
        const phase2Results = this.testPhase2Functionality();
        
        // Calculate overall compatibility score
        this.compatibilityScore = (
            jsResults.overallScore * 0.4 +  // JavaScript features are most important
            phase2Results.overallScore * 0.3 + // Phase 2 functionality is critical
            cssResults * 0.2 +  // CSS features are nice to have
            perfResults * 0.1   // Performance APIs are optional
        );
        
        const report = this.generateCompatibilityReport({
            javascript: jsResults,
            css: cssResults,
            performance: perfResults,
            phase2: phase2Results
        });
        
        console.log(`✅ Compatibility tests completed. Overall score: ${this.compatibilityScore.toFixed(1)}%`);
        
        return report;
    }
    
    /**
     * Generate compatibility report
     */
    generateCompatibilityReport(scores) {
        const grade = this.compatibilityScore >= 90 ? 'A' :
                     this.compatibilityScore >= 80 ? 'B' :
                     this.compatibilityScore >= 70 ? 'C' :
                     this.compatibilityScore >= 60 ? 'D' : 'F';
        
        const compatibility = this.compatibilityScore >= 90 ? 'Excellent' :
                            this.compatibilityScore >= 80 ? 'Good' :
                            this.compatibilityScore >= 70 ? 'Fair' :
                            this.compatibilityScore >= 60 ? 'Poor' : 'Incompatible';
        
        return {
            testDate: new Date().toISOString(),
            browser: this.browserInfo,
            overallScore: this.compatibilityScore,
            grade,
            compatibility,
            
            categoryScores: {
                javascript: scores.javascript,
                css: scores.css,
                performance: scores.performance,
                phase2: scores.phase2
            },
            
            detailedResults: this.testResults,
            
            recommendations: this.generateRecommendations(),
            
            summary: {
                totalTests: this.testResults.length,
                passed: this.testResults.filter(r => r.result === 'PASS').length,
                failed: this.testResults.filter(r => r.result === 'FAIL').length,
                criticalFailures: this.testResults.filter(r => r.result === 'FAIL' && r.required).length
            }
        };
    }
    
    /**
     * Generate recommendations based on test results
     */
    generateRecommendations() {
        const recommendations = [];
        const failedRequired = this.testResults.filter(r => r.result === 'FAIL' && r.required);
        
        if (failedRequired.length > 0) {
            recommendations.push({
                type: 'critical',
                message: `${failedRequired.length} critical features are not supported. Phase 2 functionality may not work properly.`,
                details: failedRequired.map(r => r.test)
            });
        }
        
        if (this.browserInfo.browser === 'Internet Explorer') {
            recommendations.push({
                type: 'warning',
                message: 'Internet Explorer has limited support for modern JavaScript features. Consider using a modern browser.',
                details: ['Fetch API', 'Promises', 'Arrow Functions may need polyfills']
            });
        }
        
        if (this.compatibilityScore < 80) {
            recommendations.push({
                type: 'suggestion',
                message: 'Some features may not work optimally. Consider updating your browser.',
                details: ['Update to latest browser version', 'Enable JavaScript', 'Clear browser cache']
            });
        }
        
        if (this.browserInfo.isMobile) {
            recommendations.push({
                type: 'info',
                message: 'Mobile browser detected. Touch interactions are optimized for mobile use.',
                details: ['Touch-friendly notification dismissal', 'Responsive design applied']
            });
        }
        
        return recommendations;
    }
    
    /**
     * Display results in console
     */
    displayResults() {
        const report = this.runAllTests();
        
        console.log('\n🌐 CROSS-BROWSER COMPATIBILITY REPORT');
        console.log('=========================================');
        console.log(`Browser: ${this.browserInfo.browser} ${this.browserInfo.version}`);
        console.log(`Platform: ${this.browserInfo.platform}`);
        console.log(`Mobile: ${this.browserInfo.isMobile ? 'Yes' : 'No'}`);
        console.log(`Overall Score: ${this.compatibilityScore.toFixed(1)}% (Grade: ${report.grade})`);
        console.log(`Compatibility: ${report.compatibility}`);
        console.log('\nCategory Scores:');
        console.log(`- JavaScript Features: ${report.categoryScores.javascript.overallScore.toFixed(1)}%`);
        console.log(`- Phase 2 Functionality: ${report.categoryScores.phase2.overallScore.toFixed(1)}%`);
        console.log(`- CSS Features: ${report.categoryScores.css.toFixed(1)}%`);
        console.log(`- Performance APIs: ${report.categoryScores.performance.toFixed(1)}%`);
        
        if (report.recommendations.length > 0) {
            console.log('\nRecommendations:');
            report.recommendations.forEach(rec => {
                console.log(`- ${rec.type.toUpperCase()}: ${rec.message}`);
            });
        }
        
        console.log('=========================================\n');
        
        return report;
    }
}

// Auto-run compatibility test when script loads
const compatibilityTest = new CrossBrowserCompatibilityTest();

// Make globally available
window.CrossBrowserCompatibilityTest = compatibilityTest;

// Auto-run tests after page load
window.addEventListener('load', async function() {
    console.log('🚀 Starting cross-browser compatibility tests...');
    
    setTimeout(async () => {
        try {
            const report = await compatibilityTest.runAllTests();
            compatibilityTest.displayResults();
            
            // Show notification with compatibility status
            if (typeof showNotification === 'function') {
                const score = compatibilityTest.compatibilityScore;
                const message = `Browser Compatibility: ${score.toFixed(1)}% (${report.compatibility})`;
                const type = score >= 80 ? 'success' : score >= 60 ? 'warning' : 'error';
                
                showNotification(message, type, 5000);
            }
            
        } catch (error) {
            console.error('❌ Compatibility test failed:', error);
            if (typeof showNotification === 'function') {
                showNotification('Browser compatibility test encountered errors. Check console for details.', 'error');
            }
        }
    }, 1000);
});

console.log('✅ Cross-Browser Compatibility Test script loaded');
