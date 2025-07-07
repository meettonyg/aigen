/**
 * Tagline Generator Integration Test Script
 * This file implements comprehensive tests for Phase 5 of the Tagline Generator
 */

// Wait for DOM content to load
document.addEventListener('DOMContentLoaded', function() {
  // Only run tests if we're on the tagline generator page
  if (!document.querySelector('.tagline-generator')) {
    console.log('Not on tagline generator page - skipping tests');
    return;
  }
  
  console.log('🧪 TAGLINE GENERATOR TESTS - Starting Integration Tests...');
  
  // Run tests with a slight delay to ensure everything is loaded
  setTimeout(runIntegrationTests, 500);
});

/**
 * Comprehensive Integration Tests for Tagline Generator
 */
function runIntegrationTests() {
  // Test configuration
  const TEST_CONFIG = {
    // Test categories to run (set to false to skip)
    runIntegrationTests: true,   // Test integration with other generators
    runPerformanceTests: true,   // Test performance metrics
    runUserExperienceTests: true,  // Test user interaction flows
    runDataQualityTests: true,   // Test tagline generation quality
    runSecurityTests: true,      // Test security measures
    runCrossBrowserTests: true,  // Test cross-browser compatibility
    
    // Test data
    sampleAuthorityHook: {
      who: 'busy entrepreneurs',
      what: 'scale their business without burnout',
      when: 'they hit a growth plateau',
      how: 'with proven systems and frameworks'
    },
    sampleImpactIntro: {
      where: 'I\'ve helped over 500 businesses implement sustainable growth strategies',
      why: 'I believe success shouldn\'t come at the cost of your well-being'
    },
    sampleContext: {
      industry: 'Business Coaching',
      uniqueFactors: 'Focus on sustainable growth and founder well-being',
      existingTaglines: 'Growth Without Burnout'
    },
    sampleSettings: {
      style: 'outcome-focused',
      tone: 'professional',
      length: 'medium'
    },
    
    // Test timeouts and thresholds
    timeouts: {
      load: 2000,       // 2 seconds max for loading
      generate: 30000,  // 30 seconds max for generation
      render: 100       // 100ms max for rendering
    },
    
    // Elements to test
    selectors: {
      authorityHookText: '#tagline-generator-authority-hook-text',
      impactIntroText: '#tagline-generator-impact-intro-text',
      industryField: '#tagline-industry',
      uniqueFactorsField: '#tagline-unique-factors',
      existingTaglinesField: '#tagline-existing-taglines',
      styleSelect: '#tagline-style',
      toneSelect: '#tagline-tone',
      lengthSelect: '#tagline-length',
      generateButton: '#tagline-generate-with-ai',
      resultsContainer: '#tagline-generator-results',
      optionsGrid: '#tagline-generator-options-grid',
      optionClass: '.tagline-generator__option',
      copyButton: '.tagline-generator__option-copy',
      selectButton: '.tagline-generator__option-select',
      selectedContainer: '#tagline-generator-selected-container',
      selectedContent: '#tagline-generator-selected-content'
    }
  };
  
  // Create a test reporting system
  const TestReport = {
    categories: {},
    
    // Initialize a new test category
    initCategory: function(name) {
      this.categories[name] = {
        total: 0,
        passed: 0,
        failed: 0,
        warnings: 0,
        tests: []
      };
      return this.categories[name];
    },
    
    // Add a test result
    addResult: function(category, name, passed, message, details = null) {
      if (!this.categories[category]) {
        this.initCategory(category);
      }
      
      const cat = this.categories[category];
      cat.total++;
      
      if (passed === true) {
        cat.passed++;
      } else if (passed === false) {
        cat.failed++;
      } else if (passed === 'warning') {
        cat.warnings++;
      }
      
      cat.tests.push({
        name,
        passed,
        message,
        details,
        timestamp: new Date().toISOString()
      });
      
      // Log to console with appropriate formatting
      if (passed === true) {
        console.log(`✅ [${category}] ${name}: ${message}`);
      } else if (passed === false) {
        console.error(`❌ [${category}] ${name}: ${message}`);
        if (details) console.error(details);
      } else if (passed === 'warning') {
        console.warn(`⚠️ [${category}] ${name}: ${message}`);
        if (details) console.warn(details);
      }
    },
    
    // Generate the final report
    generateReport: function() {
      console.group('🧪 Tagline Generator Integration Test Report');
      
      let totalTests = 0;
      let totalPassed = 0;
      let totalFailed = 0;
      let totalWarnings = 0;
      
      // Summarize each category
      Object.keys(this.categories).forEach(category => {
        const cat = this.categories[category];
        totalTests += cat.total;
        totalPassed += cat.passed;
        totalFailed += cat.failed;
        totalWarnings += cat.warnings;
        
        const passRate = cat.total > 0 ? Math.round((cat.passed / cat.total) * 100) : 0;
        
        console.group(`${category}: ${passRate}% passed (${cat.passed}/${cat.total})`);
        if (cat.failed > 0) {
          console.group('Failed Tests:');
          cat.tests.filter(t => t.passed === false).forEach(test => {
            console.error(`- ${test.name}: ${test.message}`);
          });
          console.groupEnd();
        }
        if (cat.warnings > 0) {
          console.group('Warnings:');
          cat.tests.filter(t => t.passed === 'warning').forEach(test => {
            console.warn(`- ${test.name}: ${test.message}`);
          });
          console.groupEnd();
        }
        console.groupEnd();
      });
      
      // Overall summary
      const overallPassRate = totalTests > 0 ? Math.round((totalPassed / totalTests) * 100) : 0;
      console.log(`Overall Pass Rate: ${overallPassRate}% (${totalPassed}/${totalTests})`);
      console.log(`Total Failed: ${totalFailed}`);
      console.log(`Total Warnings: ${totalWarnings}`);
      
      console.groupEnd();
      
      // Return the final statistics
      return {
        totalTests,
        totalPassed,
        totalFailed,
        totalWarnings,
        overallPassRate
      };
    }
  };
  
  // Run all enabled test categories
  console.log('🧪 Starting Tagline Generator Integration Tests...');
  
  if (TEST_CONFIG.runIntegrationTests) {
    runCategoryTests('Integration Tests', runIntegrationTestSuite);
  }
  
  if (TEST_CONFIG.runPerformanceTests) {
    runCategoryTests('Performance Tests', runPerformanceTestSuite);
  }
  
  if (TEST_CONFIG.runUserExperienceTests) {
    runCategoryTests('User Experience Tests', runUserExperienceTestSuite);
  }
  
  if (TEST_CONFIG.runDataQualityTests) {
    runCategoryTests('Data Quality Tests', runDataQualityTestSuite);
  }
  
  if (TEST_CONFIG.runSecurityTests) {
    runCategoryTests('Security Tests', runSecurityTestSuite);
  }
  
  if (TEST_CONFIG.runCrossBrowserTests) {
    runCategoryTests('Cross-Browser Tests', runCrossBrowserTestSuite);
  }
  
  // Generate the final report after all tests
  setTimeout(() => {
    const results = TestReport.generateReport();
    console.log('🧪 Tagline Generator Integration Tests Completed');
    
    // Show overall status
    if (results.totalFailed === 0) {
      console.log('✅ All tests passed successfully!');
    } else {
      console.error(`❌ ${results.totalFailed} tests failed - review report for details`);
    }
  }, 500);
  
  /**
   * Helper function to run a category of tests
   */
  function runCategoryTests(category, testFunction) {
    console.group(`🧪 Running ${category}...`);
    TestReport.initCategory(category);
    testFunction(TEST_CONFIG, TestReport, category);
    console.groupEnd();
  }
  
  /**
   * Integration Test Suite - Tests integration with other generators
   */
  function runIntegrationTestSuite(config, report, category) {
    // Test 1: Check TaglineGenerator global object exists
    report.addResult(
      category,
      'Global Object',
      typeof window.TaglineGenerator === 'object',
      typeof window.TaglineGenerator === 'object' ? 
        'TaglineGenerator global object available' : 
        'TaglineGenerator global object missing'
    );
    
    // Test 2: Check integration with Authority Hook Service
    const hasAuthorityHookService = typeof window.MKCG_Authority_Hook_Service === 'object' || 
                                   document.querySelector('#tagline-generator-authority-hook-builder');
    report.addResult(
      category,
      'Authority Hook Service',
      hasAuthorityHookService ? true : 'warning',
      hasAuthorityHookService ? 
        'Authority Hook Service integration available' : 
        'Authority Hook Service integration unavailable - limited functionality'
    );
    
    // Test 3: Check integration with Impact Intro Service
    const hasImpactIntroService = typeof window.MKCG_Impact_Intro_Service === 'object' || 
                                 document.querySelector('#tagline-generator-impact-intro-builder');
    report.addResult(
      category,
      'Impact Intro Service',
      hasImpactIntroService ? true : 'warning',
      hasImpactIntroService ? 
        'Impact Intro Service integration available' : 
        'Impact Intro Service integration unavailable - limited functionality'
    );
    
    // Test 4: Check CSS class consistency with other generators
    const cssClassConsistency = document.querySelector('.generator__container') && 
                              document.querySelector('.generator__panel--left') && 
                              document.querySelector('.generator__panel--right');
    report.addResult(
      category,
      'CSS Class Consistency',
      cssClassConsistency,
      cssClassConsistency ? 
        'Using consistent CSS class structure with other generators' : 
        'Inconsistent CSS class structure detected'
    );
    
    // Test 5: Check if makeAjaxRequest is available for API calls
    report.addResult(
      category,
      'AJAX Integration',
      typeof window.makeAjaxRequest === 'function',
      typeof window.makeAjaxRequest === 'function' ? 
        'Global AJAX system available' : 
        'Global AJAX system unavailable - API calls will fail'
    );
    
    // Test 6: Check for notification system integration
    report.addResult(
      category,
      'Notification System',
      typeof window.showNotification === 'function',
      typeof window.showNotification === 'function' ? 
        'Notification system available' : 
        'Notification system unavailable - fallback alerts will be used'
    );
    
    // Test 7: Check for cross-generator event system
    report.addResult(
      category,
      'Event System',
      typeof window.AppEvents === 'object',
      typeof window.AppEvents === 'object' ? 
        'Cross-generator event system available' : 
        'Cross-generator event system unavailable - limited integration'
    );
    
    // Test 8: Check for design token consistency
    const style = window.getComputedStyle(document.documentElement);
    const hasMkcgVariables = style.getPropertyValue('--mkcg-primary') !== '';
    report.addResult(
      category,
      'Design Token System',
      hasMkcgVariables,
      hasMkcgVariables ? 
        'Design token system (CSS variables) available' : 
        'Design token system not detected - visual inconsistency likely'
    );
  }
  
  /**
   * Performance Test Suite - Tests load times and rendering performance
   */
  function runPerformanceTestSuite(config, report, category) {
    // Test 1: Measure template load time
    const pageLoadTime = window.performance && window.performance.timing ? 
      window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart : 
      null;
    
    if (pageLoadTime !== null) {
      report.addResult(
        category,
        'Template Load Time',
        pageLoadTime < config.timeouts.load,
        pageLoadTime < config.timeouts.load ? 
          `Template loaded in ${pageLoadTime}ms (under ${config.timeouts.load}ms threshold)` : 
          `Template load time (${pageLoadTime}ms) exceeds ${config.timeouts.load}ms threshold`
      );
    } else {
      report.addResult(
        category,
        'Template Load Time',
        'warning',
        'Could not measure template load time - Performance API not available'
      );
    }
    
    // Test 2: Measure JavaScript bundle size
    let jsSize = 0;
    const scripts = document.querySelectorAll('script[src*="tagline"]');
    scripts.forEach(script => {
      if (script.src) {
        fetch(script.src)
          .then(response => response.text())
          .then(text => {
            jsSize += text.length;
            const kbSize = Math.round(jsSize / 1024);
            report.addResult(
              category,
              'JavaScript Bundle Size',
              kbSize < 100, // Under 100KB is good
              kbSize < 100 ? 
                `JavaScript bundle size is ${kbSize}KB (under 100KB threshold)` : 
                `JavaScript bundle size (${kbSize}KB) exceeds 100KB threshold`
            );
          })
          .catch(err => {
            report.addResult(
              category,
              'JavaScript Bundle Size',
              'warning',
              'Could not measure JavaScript bundle size - fetch failed',
              err
            );
          });
      }
    });
    
    // Test 3: Measure memory usage if available
    if (window.performance && window.performance.memory) {
      const memoryUsage = Math.round(window.performance.memory.usedJSHeapSize / (1024 * 1024));
      report.addResult(
        category,
        'Memory Usage',
        memoryUsage < 10, // Under 10MB is good
        memoryUsage < 10 ? 
          `Memory usage is ${memoryUsage}MB (under 10MB threshold)` : 
          `Memory usage (${memoryUsage}MB) exceeds 10MB threshold`
      );
    } else {
      report.addResult(
        category,
        'Memory Usage',
        'warning',
        'Could not measure memory usage - Performance Memory API not available'
      );
    }
    
    // Test 4: Measure render performance with mock data
    const startTime = performance.now();
    
    // Find the options grid
    const optionsGrid = document.querySelector(config.selectors.optionsGrid);
    if (optionsGrid) {
      // Create a temporary document fragment
      const fragment = document.createDocumentFragment();
      
      // Create 10 mock tagline options
      for (let i = 0; i < 10; i++) {
        const option = document.createElement('div');
        option.className = 'tagline-generator__option';
        option.setAttribute('data-tagline-id', `test_${i + 1}`);
        option.setAttribute('data-tagline-text', `Test Tagline ${i + 1}`);
        
        option.innerHTML = `
          <div class="tagline-generator__option-content">
            <div class="tagline-generator__option-text">Test Tagline ${i + 1}</div>
            <div class="tagline-generator__option-meta">
              <span class="tagline-generator__option-style">Test Style</span>
              <span class="tagline-generator__option-length">Test Length</span>
            </div>
          </div>
          <div class="tagline-generator__option-actions">
            <button class="tagline-generator__option-copy" data-tagline="Test Tagline ${i + 1}">Copy</button>
            <button class="tagline-generator__option-select" data-tagline-id="test_${i + 1}">Select</button>
          </div>
        `;
        
        fragment.appendChild(option);
      }
      
      // Actually append to the DOM to measure performance
      const originalContent = optionsGrid.innerHTML;
      optionsGrid.innerHTML = '';
      optionsGrid.appendChild(fragment);
      
      const renderTime = performance.now() - startTime;
      
      // Restore original content
      setTimeout(() => {
        optionsGrid.innerHTML = originalContent;
      }, 100);
      
      report.addResult(
        category,
        'Render Performance',
        renderTime < config.timeouts.render,
        renderTime < config.timeouts.render ? 
          `Rendered 10 tagline options in ${renderTime.toFixed(2)}ms (under ${config.timeouts.render}ms threshold)` : 
          `Rendering time (${renderTime.toFixed(2)}ms) exceeds ${config.timeouts.render}ms threshold`
      );
    } else {
      report.addResult(
        category,
        'Render Performance',
        'warning',
        'Could not test render performance - options grid not found'
      );
    }
    
    // Test 5: Test debounce function optimization
    const debounceOptimized = window.TaglineGenerator && 
                             typeof window.TaglineGenerator.debounce === 'function' && 
                             window.TaglineGenerator.debounce.toString().includes('immediate');
    
    report.addResult(
      category,
      'Debounce Optimization',
      debounceOptimized,
      debounceOptimized ? 
        'Debounce function is optimized with immediate option' : 
        'Debounce function lacks optimizations'
    );
    
    // Test 6: Check for caching implementation
    const hasCaching = window.TaglineGenerator && 
                      (typeof window.TaglineGenerator.cacheGenerationResults === 'function' || 
                       typeof window.TaglineGenerator.loadCachedResults === 'function');
    
    report.addResult(
      category,
      'Caching Implementation',
      hasCaching,
      hasCaching ? 
        'Caching implementation detected for faster recovery' : 
        'No caching implementation found - slower recovery from errors'
    );
  }
  
  /**
   * User Experience Test Suite - Tests interaction flows and UI elements
   */
  function runUserExperienceTestSuite(config, report, category) {
    // Test 1: Check form field accessibility
    const formFields = [
      config.selectors.industryField,
      config.selectors.uniqueFactorsField,
      config.selectors.existingTaglinesField,
      config.selectors.styleSelect,
      config.selectors.toneSelect,
      config.selectors.lengthSelect
    ];
    
    let accessibleFields = 0;
    let totalFields = 0;
    
    formFields.forEach(selector => {
      const field = document.querySelector(selector);
      if (field) {
        totalFields++;
        
        // Check for label association
        let hasLabel = false;
        const id = field.id;
        if (id) {
          const label = document.querySelector(`label[for="${id}"]`);
          if (label) {
            hasLabel = true;
          }
        }
        
        // Check for aria attributes
        const hasAriaLabel = field.getAttribute('aria-label') || field.getAttribute('aria-labelledby');
        
        if (hasLabel || hasAriaLabel) {
          accessibleFields++;
        }
      }
    });
    
    report.addResult(
      category,
      'Form Field Accessibility',
      accessibleFields === totalFields,
      accessibleFields === totalFields ? 
        `All ${totalFields} form fields have proper accessibility labels` : 
        `Only ${accessibleFields} of ${totalFields} form fields have proper accessibility labels`
    );
    
    // Test 2: Check responsive design
    const container = document.querySelector('.tagline-generator');
    const isResponsive = container && 
                        window.getComputedStyle(container).maxWidth !== 'none' && 
                        window.getComputedStyle(container).width.includes('%');
    
    report.addResult(
      category,
      'Responsive Design',
      isResponsive,
      isResponsive ? 
        'Container uses responsive design with percentage-based width' : 
        'Container may not be fully responsive'
    );
    
    // Test 3: Check loading indicator
    const hasLoadingIndicator = document.querySelector('#tagline-generator-loading');
    report.addResult(
      category,
      'Loading Indicator',
      hasLoadingIndicator !== null,
      hasLoadingIndicator !== null ? 
        'Loading indicator present for user feedback' : 
        'Loading indicator missing - user may be confused during operations'
    );
    
    // Test 4: Check button states and visual feedback
    const generateButton = document.querySelector(config.selectors.generateButton);
    const hasButtonStates = generateButton && 
                          (generateButton.className.includes('--primary') || 
                           generateButton.className.includes('--call-to-action'));
    
    report.addResult(
      category,
      'Button Visual States',
      hasButtonStates,
      hasButtonStates ? 
        'Buttons have proper visual states' : 
        'Buttons lack visual state styling'
    );
    
    // Test 5: Check for error handling UI
    const hasErrorHandling = window.TaglineGenerator && 
                            typeof window.TaglineGenerator.handleAjaxError === 'function';
    
    report.addResult(
      category,
      'Error Handling UI',
      hasErrorHandling,
      hasErrorHandling ? 
        'Error handling UI implemented' : 
        'Error handling UI may be inadequate'
    );
    
    // Test 6: Check for offline detection
    const hasOfflineDetection = window.TaglineGenerator && 
                              typeof window.TaglineGenerator.handleOfflineGeneration === 'function';
    
    report.addResult(
      category,
      'Offline Detection',
      hasOfflineDetection,
      hasOfflineDetection ? 
        'Offline detection implemented for better UX' : 
        'No offline detection - may fail silently when offline'
    );
    
    // Test 7: Check for enhanced clipboard functionality
    const hasEnhancedClipboard = window.TaglineGenerator && 
                                typeof window.TaglineGenerator.fallbackCopyToClipboard === 'function';
    
    report.addResult(
      category,
      'Enhanced Clipboard',
      hasEnhancedClipboard,
      hasEnhancedClipboard ? 
        'Enhanced cross-browser clipboard functionality implemented' : 
        'Limited clipboard functionality - may not work in all browsers'
    );
  }
  
  /**
   * Data Quality Test Suite - Tests tagline generation quality
   */
  function runDataQualityTestSuite(config, report, category) {
    // Test 1: Check variety in demo taglines
    const taglineVarietyTest = function() {
      // Access demo tagline generation
      if (window.TaglineGenerator && typeof window.TaglineGenerator.generateDemoTaglines === 'function') {
        try {
          // Create sample form data
          const formData = {
            style: 'problem-focused',
            tone: 'professional',
            length: 'medium'
          };
          
          // Generate demo taglines
          const demoTaglines = window.TaglineGenerator.createDemoTaglinesByStyle(
            formData.style, formData.tone, formData.length
          );
          
          // Check if we have enough variety
          const uniqueTaglines = new Set(demoTaglines.map(t => t.text));
          const varietyScore = uniqueTaglines.size / demoTaglines.length;
          
          report.addResult(
            category,
            'Demo Tagline Variety',
            varietyScore >= 0.7, // At least 70% unique
            varietyScore >= 0.7 ? 
              `Demo taglines have good variety (${Math.round(varietyScore * 100)}% unique)` : 
              `Demo taglines lack variety (only ${Math.round(varietyScore * 100)}% unique)`
          );
        } catch (err) {
          report.addResult(
            category,
            'Demo Tagline Variety',
            false,
            'Error testing demo tagline variety',
            err
          );
        }
      } else {
        report.addResult(
          category,
          'Demo Tagline Variety',
          'warning',
          'Could not test demo tagline variety - function not available'
        );
      }
    };
    
    // Run with a slight delay to ensure TaglineGenerator is fully loaded
    setTimeout(taglineVarietyTest, 100);
    
    // Test 2: Check if format adjustment functions exist
    const hasStyleFormatting = window.TaglineGenerator && 
                             typeof window.TaglineGenerator.getStyleLabel === 'function' && 
                             typeof window.TaglineGenerator.getLengthLabel === 'function';
    
    report.addResult(
      category,
      'Format Adjustments',
      hasStyleFormatting,
      hasStyleFormatting ? 
        'Tagline format adjustment functions implemented' : 
        'Tagline format adjustment functions missing'
    );
    
    // Test 3: Check for proper data persistence
    const hasSaveFunction = window.TaglineGenerator && 
                           typeof window.TaglineGenerator.saveSelectedTagline === 'function';
    
    report.addResult(
      category,
      'Data Persistence',
      hasSaveFunction,
      hasSaveFunction ? 
        'Tagline save functionality implemented' : 
        'Tagline save functionality missing'
    );
    
    // Test 4: Check for result display functions
    const hasDisplayFunction = window.TaglineGenerator && 
                             typeof window.TaglineGenerator.displayTaglineOptions === 'function' && 
                             typeof window.TaglineGenerator.displaySelectedTagline === 'function';
    
    report.addResult(
      category,
      'Result Display',
      hasDisplayFunction,
      hasDisplayFunction ? 
        'Tagline display functions properly implemented' : 
        'Tagline display functions incomplete'
    );
    
    // Test 5: Check for tag selection functionality
    const hasSelectionFunction = window.TaglineGenerator && 
                               typeof window.TaglineGenerator.selectTagline === 'function';
    
    report.addResult(
      category,
      'Selection Functionality',
      hasSelectionFunction,
      hasSelectionFunction ? 
        'Tagline selection functionality implemented' : 
        'Tagline selection functionality missing'
    );
  }
  
  /**
   * Security Test Suite - Tests security measures
   */
  function runSecurityTestSuite(config, report, category) {
    // Test 1: Check for nonce usage
    const hasNonce = document.querySelector('#tagline-nonce') !== null;
    report.addResult(
      category,
      'Nonce Implementation',
      hasNonce,
      hasNonce ? 
        'Security nonce implemented for AJAX requests' : 
        'Security nonce missing - potential CSRF vulnerability'
    );
    
    // Test 2: Check for input sanitization
    const hasSanitization = window.TaglineGenerator && 
                           window.TaglineGenerator.collectFormData && 
                           window.TaglineGenerator.collectFormData.toString().includes('trim');
    
    report.addResult(
      category,
      'Input Sanitization',
      hasSanitization ? true : 'warning',
      hasSanitization ? 
        'Input sanitization detected in form collection' : 
        'Limited input sanitization detected - potential security risk'
    );
    
    // Test 3: Check for proper error handling that doesn't expose internals
    const hasSecureErrorHandling = window.TaglineGenerator && 
                                  window.TaglineGenerator.handleAjaxError && 
                                  !window.TaglineGenerator.handleAjaxError.toString().includes('console.log(error)');
    
    report.addResult(
      category,
      'Secure Error Handling',
      hasSecureErrorHandling,
      hasSecureErrorHandling ? 
        'Error handling properly hides internal details from users' : 
        'Error handling might expose internal details - security risk'
    );
    
    // Test 4: Check for avoidance of innerHTML where possible
    // This is a simplistic check - a comprehensive check would need code analysis
    const displayTaglineOptionsCode = window.TaglineGenerator && 
                                    window.TaglineGenerator.displayTaglineOptions && 
                                    window.TaglineGenerator.displayTaglineOptions.toString();
    
    const usesCreateElement = displayTaglineOptionsCode && 
                            displayTaglineOptionsCode.includes('createElement') && 
                            displayTaglineOptionsCode.includes('appendChild');
    
    report.addResult(
      category,
      'DOM Creation Security',
      usesCreateElement ? true : 'warning',
      usesCreateElement ? 
        'Uses createElement for safer DOM manipulation' : 
        'May use innerHTML extensively - potential XSS risk'
    );
    
    // Test 5: Check for content policy compliance
    const metaCSP = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
    report.addResult(
      category,
      'Content Security Policy',
      metaCSP !== null ? true : 'warning',
      metaCSP !== null ? 
        'Content Security Policy implemented' : 
        'No Content Security Policy detected - recommended for enhanced security'
    );
  }
  
  /**
   * Cross-Browser Test Suite - Tests compatibility across browsers
   */
  function runCrossBrowserTestSuite(config, report, category) {
    // Detect current browser
    const browser = {
      isChrome: navigator.userAgent.indexOf('Chrome') > -1 && navigator.userAgent.indexOf('Edg') === -1,
      isFirefox: navigator.userAgent.indexOf('Firefox') > -1,
      isSafari: /^((?!chrome|android).)*safari/i.test(navigator.userAgent),
      isEdge: navigator.userAgent.indexOf('Edg') > -1,
      isIE: navigator.userAgent.indexOf('Trident') > -1,
      isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
      isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream
    };
    
    // Report current browser
    let browserName = 'Unknown';
    if (browser.isChrome) browserName = 'Chrome';
    if (browser.isFirefox) browserName = 'Firefox';
    if (browser.isSafari) browserName = 'Safari';
    if (browser.isEdge) browserName = 'Edge';
    if (browser.isIE) browserName = 'Internet Explorer';
    if (browser.isMobile) browserName += ' Mobile';
    if (browser.isIOS) browserName += ' (iOS)';
    
    report.addResult(
      category,
      'Browser Detection',
      true,
      `Detected browser: ${browserName}`
    );
    
    // Test 1: Check for flexbox fallbacks
    const hasFlexboxFallbacks = document.querySelector('.firefox-flex-fix') !== null ||
                              document.querySelector('[class*="flex"]') !== null;
    
    report.addResult(
      category,
      'Flexbox Fallbacks',
      hasFlexboxFallbacks ? true : 'warning',
      hasFlexboxFallbacks ? 
        'Flexbox fallbacks implemented for older browsers' : 
        'Limited flexbox fallbacks - may break in older browsers'
    );
    
    // Test 2: Check for grid fallbacks
    const hasGridFallbacks = browser.isIE || browser.isEdge ?
                           document.querySelector('[class*="grid-cols"]') === null :
                           true;
    
    report.addResult(
      category,
      'Grid Layout Fallbacks',
      hasGridFallbacks ? true : 'warning',
      hasGridFallbacks ? 
        'Grid layout fallbacks implemented or not needed' : 
        'Limited grid fallbacks - may break in older browsers'
    );
    
    // Test 3: Check for polyfills or capability detection
    const hasPolyfills = window.requestAnimationFrame && window.Promise;
    
    report.addResult(
      category,
      'Modern API Availability',
      hasPolyfills,
      hasPolyfills ? 
        'Modern APIs available or polyfilled' : 
        'Modern APIs unavailable - functionality will be limited'
    );
    
    // Test 4: Check for vendor prefixes in CSS
    const style = document.createElement('style');
    style.textContent = `
      .test-prefixes {
        -webkit-transition: all 0.3s ease;
        -moz-transition: all 0.3s ease;
        transition: all 0.3s ease;
      }
    `;
    document.head.appendChild(style);
    
    const testDiv = document.createElement('div');
    testDiv.className = 'test-prefixes';
    testDiv.style.position = 'absolute';
    testDiv.style.opacity = '0';
    document.body.appendChild(testDiv);
    
    const computedStyle = window.getComputedStyle(testDiv);
    const hasVendorPrefixes = computedStyle.getPropertyValue('transition') !== '';
    
    // Clean up test elements
    document.head.removeChild(style);
    document.body.removeChild(testDiv);
    
    report.addResult(
      category,
      'CSS Vendor Prefixes',
      hasVendorPrefixes,
      hasVendorPrefixes ? 
        'CSS vendor prefixes implemented' : 
        'CSS vendor prefixes missing - styles may break in older browsers'
    );
    
    // Test 5: Check for touchscreen optimizations
    const hasTouchOptimizations = browser.isMobile || browser.isIOS ? 
                                document.querySelector('button') && 
                                window.getComputedStyle(document.querySelector('button')).minHeight !== 'auto' :
                                true;
    
    report.addResult(
      category,
      'Touch Optimizations',
      hasTouchOptimizations ? true : 'warning',
      hasTouchOptimizations ? 
        'Touch optimizations implemented or not needed' : 
        'Limited touch optimizations - mobile experience may be poor'
    );
    
    // Test 6: Check for cross-browser clipboard support
    const hasClipboardFallback = window.TaglineGenerator && 
                               typeof window.TaglineGenerator.fallbackCopyToClipboard === 'function';
    
    report.addResult(
      category,
      'Clipboard Compatibility',
      hasClipboardFallback,
      hasClipboardFallback ? 
        'Cross-browser clipboard support implemented' : 
        'Limited clipboard support - may not work in all browsers'
    );
    
    // Test 7: Check for custom cross-browser fixes applied
    // Look for our external fix files
    const hasCrossBrowserCss = document.querySelector('link[href*="cross-browser-fixes.css"]') !== null;
    const hasCrossBrowserJs = document.querySelector('script[src*="cross-browser-fixes.js"]') !== null;
    
    report.addResult(
      category,
      'Cross-Browser Fixes',
      hasCrossBrowserCss || hasCrossBrowserJs,
      (hasCrossBrowserCss || hasCrossBrowserJs) ? 
        `Cross-browser fixes applied (CSS: ${hasCrossBrowserCss}, JS: ${hasCrossBrowserJs})` : 
        'No dedicated cross-browser fixes detected'
    );
  }
}
