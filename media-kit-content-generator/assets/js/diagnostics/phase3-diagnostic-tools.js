/**
 * PHASE 3: SYSTEM INTEGRATION & VALIDATION
 * JavaScript Diagnostic and Monitoring Tools
 * 
 * Version: 3.0.0
 * Purpose: Frontend diagnostic interface, real-time monitoring, performance tracking
 */

(function() {
  'use strict';
  
  /**
   * PHASE 3: Diagnostic Tools Manager
   */
  const MKCG_DiagnosticTools = {
    
    // Configuration
    config: {
      version: '3.0.0',
      phase: 'PHASE_3_INTEGRATION_VALIDATION',
      updateInterval: 30000, // 30 seconds
      errorLogLimit: 100,
      metricsRetention: 1440 // 24 hours in minutes
    },
    
    // Runtime data
    runtime: {
      startTime: Date.now(),
      errors: [],
      metrics: [],
      lastHealthCheck: null,
      systemStatus: 'unknown'
    },
    
    // Performance monitoring
    performance: {
      ajaxRequests: [],
      loadTimes: [],
      memoryUsage: [],
      errorRates: []
    },
    
    /**
     * PHASE 3: Initialize diagnostic tools
     */
    init: function() {
      console.log('🔍 PHASE 3: Initializing JavaScript Diagnostic Tools');
      
      this.setupErrorTracking();
      this.setupPerformanceMonitoring();
      this.setupRealTimeMonitoring();
      this.createDiagnosticInterface();
      this.startHealthCheckMonitoring();
      
      // Make globally available for debugging
      window.MKCG_DiagnosticTools = this;
      
      console.log('✅ PHASE 3: JavaScript Diagnostic Tools initialized');
    },
    
    /**
     * PHASE 3: Setup comprehensive error tracking
     */
    setupErrorTracking: function() {
      console.log('🔍 PHASE 3: Setting up error tracking');
      
      // Track JavaScript errors
      window.addEventListener('error', (event) => {
        this.logError({
          type: 'javascript',
          message: event.message,
          filename: event.filename,
          lineno: event.lineno,
          colno: event.colno,
          stack: event.error ? event.error.stack : null,
          timestamp: new Date().toISOString()
        });
      });
      
      // Track unhandled promise rejections
      window.addEventListener('unhandledrejection', (event) => {
        this.logError({
          type: 'promise_rejection',
          message: event.reason.toString(),
          stack: event.reason.stack || null,
          timestamp: new Date().toISOString()
        });
      });
      
      // Track AJAX errors
      this.setupAjaxErrorTracking();
      
      console.log('✅ PHASE 3: Error tracking enabled');
    },
    
    /**
     * PHASE 3: Setup AJAX error tracking
     */
    setupAjaxErrorTracking: function() {
      // Monkey patch jQuery AJAX to track errors
      if (window.jQuery) {
        const originalAjax = jQuery.ajax;
        const self = this;
        
        jQuery.ajax = function(options) {
          const startTime = performance.now();
          
          // Create enhanced options
          const enhancedOptions = jQuery.extend({}, options);
          
          // Wrap success callback
          const originalSuccess = enhancedOptions.success;
          enhancedOptions.success = function(data, textStatus, jqXHR) {
            const endTime = performance.now();
            const responseTime = endTime - startTime;
            
            self.logAjaxSuccess({
              url: enhancedOptions.url,
              method: enhancedOptions.type || 'GET',
              responseTime: responseTime,
              timestamp: new Date().toISOString()
            });
            
            if (originalSuccess) {
              originalSuccess.apply(this, arguments);
            }
          };
          
          // Wrap error callback
          const originalError = enhancedOptions.error;
          enhancedOptions.error = function(jqXHR, textStatus, errorThrown) {
            const endTime = performance.now();
            const responseTime = endTime - startTime;
            
            self.logAjaxError({
              url: enhancedOptions.url,
              method: enhancedOptions.type || 'GET',
              status: jqXHR.status,
              statusText: jqXHR.statusText,
              responseTime: responseTime,
              errorThrown: errorThrown,
              timestamp: new Date().toISOString()
            });
            
            if (originalError) {
              originalError.apply(this, arguments);
            }
          };
          
          return originalAjax.call(this, enhancedOptions);
        };
      }
    },
    
    /**
     * PHASE 3: Setup performance monitoring
     */
    setupPerformanceMonitoring: function() {
      console.log('🔍 PHASE 3: Setting up performance monitoring');
      
      // Monitor page load performance
      window.addEventListener('load', () => {
        const perfData = performance.getEntriesByType('navigation')[0];
        
        this.logPerformanceMetric({
          type: 'page_load',
          metrics: {
            domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
            loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
            domInteractive: perfData.domInteractive - perfData.navigationStart,
            totalLoadTime: perfData.loadEventEnd - perfData.navigationStart
          },
          timestamp: new Date().toISOString()
        });
      });
      
      // Monitor memory usage (if available)
      if (window.performance && window.performance.memory) {
        setInterval(() => {
          this.logMemoryUsage({
            used: window.performance.memory.usedJSHeapSize,
            total: window.performance.memory.totalJSHeapSize,
            limit: window.performance.memory.jsHeapSizeLimit,
            timestamp: new Date().toISOString()
          });
        }, 60000); // Every minute
      }
      
      console.log('✅ PHASE 3: Performance monitoring enabled');
    },
    
    /**
     * PHASE 3: Setup real-time monitoring
     */
    setupRealTimeMonitoring: function() {
      console.log('🔍 PHASE 3: Setting up real-time monitoring');
      
      // Monitor Topics Generator status
      setInterval(() => {
        this.checkTopicsGeneratorHealth();
      }, this.config.updateInterval);
      
      // Monitor network status
      window.addEventListener('online', () => {
        this.logSystemEvent('network_online');
      });
      
      window.addEventListener('offline', () => {
        this.logSystemEvent('network_offline');
      });
      
      // Monitor visibility changes
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.logSystemEvent('page_hidden');
        } else {
          this.logSystemEvent('page_visible');
        }
      });
      
      console.log('✅ PHASE 3: Real-time monitoring enabled');
    },
    
    /**
     * PHASE 3: Create diagnostic interface
     */
    createDiagnosticInterface: function() {
      // Create floating diagnostic panel for quick access
      const panel = document.createElement('div');
      panel.id = 'mkcg-diagnostic-panel';
      panel.innerHTML = `
        <div class="mkcg-diag-header">
          <span>📊 PHASE 3 Diagnostics</span>
          <button onclick="MKCG_DiagnosticTools.togglePanel()">−</button>
        </div>
        <div class="mkcg-diag-content">
          <div class="mkcg-diag-metric">
            <label>System Status:</label>
            <span id="mkcg-diag-status">Checking...</span>
          </div>
          <div class="mkcg-diag-metric">
            <label>AJAX Success Rate:</label>
            <span id="mkcg-diag-ajax-rate">--%</span>
          </div>
          <div class="mkcg-diag-metric">
            <label>Error Count:</label>
            <span id="mkcg-diag-errors">0</span>
          </div>
          <div class="mkcg-diag-actions">
            <button onclick="MKCG_DiagnosticTools.runQuickTest()">Quick Test</button>
            <button onclick="MKCG_DiagnosticTools.exportLogs()">Export Logs</button>
          </div>
        </div>
      `;
      
      // Add styles
      panel.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        width: 280px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        font-size: 13px;
        display: ${localStorage.getItem('mkcg_diag_panel_hidden') === 'true' ? 'none' : 'block'};
      `;
      
      // Add internal styles
      const styles = document.createElement('style');
      styles.textContent = `
        #mkcg-diagnostic-panel .mkcg-diag-header {
          background: #0073aa;
          color: white;
          padding: 8px 12px;
          border-radius: 8px 8px 0 0;
          display: flex;
          justify-content: space-between;
          align-items: center;
          font-weight: 600;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-header button {
          background: none;
          border: none;
          color: white;
          font-size: 16px;
          cursor: pointer;
          padding: 0;
          width: 20px;
          height: 20px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-content {
          padding: 12px;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-metric {
          display: flex;
          justify-content: space-between;
          margin-bottom: 8px;
          padding: 4px 0;
          border-bottom: 1px solid #f0f0f0;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-metric label {
          font-weight: 500;
          color: #333;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-actions {
          margin-top: 12px;
          display: flex;
          gap: 8px;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-actions button {
          flex: 1;
          padding: 6px 12px;
          border: 1px solid #0073aa;
          background: white;
          color: #0073aa;
          border-radius: 4px;
          cursor: pointer;
          font-size: 12px;
        }
        
        #mkcg-diagnostic-panel .mkcg-diag-actions button:hover {
          background: #0073aa;
          color: white;
        }
      `;
      
      document.head.appendChild(styles);
      document.body.appendChild(panel);
      
      // Update interface periodically
      setInterval(() => {
        this.updateDiagnosticInterface();
      }, 5000);
      
      console.log('✅ PHASE 3: Diagnostic interface created');
    },
    
    /**
     * PHASE 3: Start health check monitoring
     */
    startHealthCheckMonitoring: function() {
      console.log('🔍 PHASE 3: Starting health check monitoring');
      
      // Initial health check
      this.performHealthCheck();
      
      // Periodic health checks
      setInterval(() => {
        this.performHealthCheck();
      }, this.config.updateInterval);
      
      console.log('✅ PHASE 3: Health check monitoring started');
    },
    
    /**
     * PHASE 3: Perform comprehensive health check
     */
    performHealthCheck: function() {
      const startTime = performance.now();
      
      jQuery.ajax({
        url: window.ajaxurl || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
          action: 'mkcg_health_check',
          nonce: this.getNonce()
        },
        timeout: 10000,
        success: (response) => {
          const endTime = performance.now();
          const responseTime = endTime - startTime;
          
          if (response.success) {
            this.runtime.lastHealthCheck = {
              status: 'healthy',
              timestamp: new Date().toISOString(),
              responseTime: responseTime,
              data: response.data
            };
            
            this.runtime.systemStatus = 'healthy';
          } else {
            this.handleHealthCheckFailure('server_error', response);
          }
        },
        error: (jqXHR, textStatus, errorThrown) => {
          this.handleHealthCheckFailure('ajax_error', {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            errorThrown: errorThrown
          });
        }
      });
    },
    
    /**
     * PHASE 3: Handle health check failure
     */
    handleHealthCheckFailure: function(type, details) {
      this.runtime.lastHealthCheck = {
        status: 'failed',
        timestamp: new Date().toISOString(),
        type: type,
        details: details
      };
      
      this.runtime.systemStatus = 'degraded';
      
      this.logError({
        type: 'health_check_failure',
        message: `Health check failed: ${type}`,
        details: details,
        timestamp: new Date().toISOString()
      });
    },
    
    /**
     * PHASE 3: Check Topics Generator specific health
     */
    checkTopicsGeneratorHealth: function() {
      // Check if Topics Generator is responsive
      if (window.TopicsGenerator) {
        try {
          // Test basic Topics Generator functionality
          const testResult = this.testTopicsGeneratorFunctionality();
          
          this.logPerformanceMetric({
            type: 'topics_generator_health',
            status: testResult.success ? 'healthy' : 'warning',
            details: testResult,
            timestamp: new Date().toISOString()
          });
          
        } catch (error) {
          this.logError({
            type: 'topics_generator_test_error',
            message: error.message,
            stack: error.stack,
            timestamp: new Date().toISOString()
          });
        }
      } else {
        this.logError({
          type: 'topics_generator_missing',
          message: 'Topics Generator not found in global scope',
          timestamp: new Date().toISOString()
        });
      }
    },
    
    /**
     * PHASE 3: Test Topics Generator functionality
     */
    testTopicsGeneratorFunctionality: function() {
      const tests = [];
      
      // Test 1: Object structure
      tests.push({
        name: 'Object Structure',
        success: typeof window.TopicsGenerator === 'object',
        details: 'Topics Generator object availability'
      });
      
      // Test 2: Required methods
      const requiredMethods = ['init', 'generateTopics', 'saveTopicField'];
      requiredMethods.forEach(method => {
        tests.push({
          name: `Method: ${method}`,
          success: typeof window.TopicsGenerator[method] === 'function',
          details: `${method} method availability`
        });
      });
      
      // Test 3: Error recovery systems
      if (window.TopicsGenerator.errorRecovery) {
        tests.push({
          name: 'Error Recovery',
          success: typeof window.TopicsGenerator.errorRecovery === 'object',
          details: 'Error recovery system availability'
        });
      }
      
      // Test 4: Network awareness
      tests.push({
        name: 'Network Awareness',
        success: typeof window.TopicsGenerator.updateNetworkStatus === 'function',
        details: 'Network status handling availability'
      });
      
      const passedTests = tests.filter(test => test.success).length;
      const totalTests = tests.length;
      
      return {
        success: passedTests === totalTests,
        passedTests: passedTests,
        totalTests: totalTests,
        successRate: Math.round((passedTests / totalTests) * 100),
        tests: tests
      };
    },
    
    /**
     * PHASE 3: Logging methods
     */
    logError: function(error) {
      this.runtime.errors.push(error);
      
      // Limit error log size
      if (this.runtime.errors.length > this.config.errorLogLimit) {
        this.runtime.errors = this.runtime.errors.slice(-this.config.errorLogLimit);
      }
      
      console.error('🔍 PHASE 3 Diagnostic Error:', error);
    },
    
    logAjaxSuccess: function(details) {
      this.performance.ajaxRequests.push({
        status: 'success',
        ...details
      });
      
      this.cleanupOldMetrics();
    },
    
    logAjaxError: function(details) {
      this.performance.ajaxRequests.push({
        status: 'error',
        ...details
      });
      
      this.logError({
        type: 'ajax_error',
        message: `AJAX request failed: ${details.url}`,
        details: details,
        timestamp: new Date().toISOString()
      });
      
      this.cleanupOldMetrics();
    },
    
    logPerformanceMetric: function(metric) {
      this.runtime.metrics.push(metric);
      
      this.cleanupOldMetrics();
    },
    
    logMemoryUsage: function(memory) {
      this.performance.memoryUsage.push(memory);
      
      this.cleanupOldMetrics();
    },
    
    logSystemEvent: function(event) {
      this.logPerformanceMetric({
        type: 'system_event',
        event: event,
        timestamp: new Date().toISOString()
      });
    },
    
    /**
     * PHASE 3: Clean up old metrics to prevent memory leaks
     */
    cleanupOldMetrics: function() {
      const cutoffTime = Date.now() - (this.config.metricsRetention * 60 * 1000);
      
      this.performance.ajaxRequests = this.performance.ajaxRequests.filter(req => 
        new Date(req.timestamp).getTime() > cutoffTime
      );
      
      this.performance.memoryUsage = this.performance.memoryUsage.filter(mem => 
        new Date(mem.timestamp).getTime() > cutoffTime
      );
      
      this.runtime.metrics = this.runtime.metrics.filter(metric => 
        new Date(metric.timestamp).getTime() > cutoffTime
      );
    },
    
    /**
     * PHASE 3: Update diagnostic interface
     */
    updateDiagnosticInterface: function() {
      const statusEl = document.getElementById('mkcg-diag-status');
      const ajaxRateEl = document.getElementById('mkcg-diag-ajax-rate');
      const errorsEl = document.getElementById('mkcg-diag-errors');
      
      if (statusEl) {
        statusEl.textContent = this.runtime.systemStatus;
        statusEl.style.color = this.runtime.systemStatus === 'healthy' ? 'green' : 
                               this.runtime.systemStatus === 'degraded' ? 'orange' : 'red';
      }
      
      if (ajaxRateEl) {
        const ajaxSuccessRate = this.calculateAjaxSuccessRate();
        ajaxRateEl.textContent = ajaxSuccessRate + '%';
        ajaxRateEl.style.color = ajaxSuccessRate >= 95 ? 'green' : 
                                ajaxSuccessRate >= 90 ? 'orange' : 'red';
      }
      
      if (errorsEl) {
        const errorCount = this.runtime.errors.length;
        errorsEl.textContent = errorCount;
        errorsEl.style.color = errorCount === 0 ? 'green' : 
                              errorCount < 5 ? 'orange' : 'red';
      }
    },
    
    /**
     * PHASE 3: Calculate AJAX success rate
     */
    calculateAjaxSuccessRate: function() {
      const recentRequests = this.performance.ajaxRequests.filter(req => 
        new Date(req.timestamp).getTime() > Date.now() - (60 * 60 * 1000) // Last hour
      );
      
      if (recentRequests.length === 0) return 100;
      
      const successfulRequests = recentRequests.filter(req => req.status === 'success').length;
      return Math.round((successfulRequests / recentRequests.length) * 100);
    },
    
    /**
     * PHASE 3: Interface interaction methods
     */
    togglePanel: function() {
      const panel = document.getElementById('mkcg-diagnostic-panel');
      const isHidden = panel.style.display === 'none';
      
      panel.style.display = isHidden ? 'block' : 'none';
      localStorage.setItem('mkcg_diag_panel_hidden', !isHidden);
    },
    
    runQuickTest: function() {
      console.log('🔍 PHASE 3: Running quick diagnostic test');
      
      const startTime = performance.now();
      
      // Test AJAX connectivity
      jQuery.ajax({
        url: window.ajaxurl || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
          action: 'mkcg_health_check',
          nonce: this.getNonce()
        },
        success: (response) => {
          const endTime = performance.now();
          const responseTime = endTime - startTime;
          
          alert(`✅ PHASE 3 Quick Test Results:
            
• AJAX Connectivity: ✅ Working (${Math.round(responseTime)}ms)
• System Status: ${this.runtime.systemStatus}
• AJAX Success Rate: ${this.calculateAjaxSuccessRate()}%
• Error Count: ${this.runtime.errors.length}
• Topics Generator: ${window.TopicsGenerator ? '✅ Available' : '❌ Missing'}
            
System is ${response.success ? 'operational' : 'experiencing issues'}.`);
        },
        error: () => {
          alert(`❌ PHASE 3 Quick Test Results:
            
• AJAX Connectivity: ❌ Failed
• System Status: Error
• Topics Generator: ${window.TopicsGenerator ? '✅ Available' : '❌ Missing'}
            
System requires attention.`);
        }
      });
    },
    
    exportLogs: function() {
      const exportData = {
        version: this.config.version,
        phase: this.config.phase,
        timestamp: new Date().toISOString(),
        runtime: this.runtime,
        performance: {
          ajaxRequests: this.performance.ajaxRequests.slice(-50), // Last 50 requests
          memoryUsage: this.performance.memoryUsage.slice(-50),   // Last 50 measurements
        },
        metrics: this.runtime.metrics.slice(-100), // Last 100 metrics
        topicsGeneratorTest: window.TopicsGenerator ? this.testTopicsGeneratorFunctionality() : null
      };
      
      const blob = new Blob([JSON.stringify(exportData, null, 2)], { 
        type: 'application/json' 
      });
      
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `mkcg-phase3-diagnostics-${Date.now()}.json`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      
      console.log('✅ PHASE 3: Diagnostic logs exported');
    },
    
    /**
     * PHASE 3: Get nonce for AJAX requests
     */
    getNonce: function() {
      // Try multiple sources for nonce
      return window.mkcg_vars?.nonce || 
             window.topics_vars?.nonce || 
             document.querySelector('#topics-generator-nonce')?.value ||
             '';
    },
    
    /**
     * PHASE 3: Get comprehensive system report
     */
    getSystemReport: function() {
      return {
        version: this.config.version,
        phase: this.config.phase,
        uptime: Date.now() - this.runtime.startTime,
        systemStatus: this.runtime.systemStatus,
        lastHealthCheck: this.runtime.lastHealthCheck,
        ajaxSuccessRate: this.calculateAjaxSuccessRate(),
        errorCount: this.runtime.errors.length,
        recentErrors: this.runtime.errors.slice(-10),
        performanceMetrics: {
          totalAjaxRequests: this.performance.ajaxRequests.length,
          averageResponseTime: this.calculateAverageResponseTime(),
          memoryTrend: this.getMemoryTrend()
        },
        topicsGeneratorStatus: window.TopicsGenerator ? 'available' : 'missing',
        topicsGeneratorTest: window.TopicsGenerator ? this.testTopicsGeneratorFunctionality() : null
      };
    },
    
    /**
     * PHASE 3: Calculate average response time
     */
    calculateAverageResponseTime: function() {
      const recentRequests = this.performance.ajaxRequests.filter(req => 
        new Date(req.timestamp).getTime() > Date.now() - (60 * 60 * 1000) // Last hour
      );
      
      if (recentRequests.length === 0) return 0;
      
      const totalTime = recentRequests.reduce((sum, req) => sum + req.responseTime, 0);
      return Math.round(totalTime / recentRequests.length);
    },
    
    /**
     * PHASE 3: Get memory usage trend
     */
    getMemoryTrend: function() {
      if (this.performance.memoryUsage.length < 2) return 'insufficient_data';
      
      const recent = this.performance.memoryUsage.slice(-5);
      const first = recent[0].used;
      const last = recent[recent.length - 1].used;
      
      const change = ((last - first) / first) * 100;
      
      if (change > 10) return 'increasing';
      if (change < -10) return 'decreasing';
      return 'stable';
    }
  };

  // PHASE 3: Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 PHASE 3: DOM Ready - Initializing Diagnostic Tools');
    
    // Wait for other scripts to load
    setTimeout(() => {
      MKCG_DiagnosticTools.init();
    }, 1000);
  });

  // PHASE 3: Initialize immediately if DOM is already ready
  if (document.readyState === 'loading') {
    // DOM is still loading, event listener will handle it
  } else {
    // DOM is already ready
    setTimeout(() => {
      MKCG_DiagnosticTools.init();
    }, 1000);
  }

  // PHASE 3: Export for global access
  window.MKCG_DiagnosticTools = MKCG_DiagnosticTools;
  
  console.log('✅ PHASE 3: JavaScript Diagnostic Tools module loaded');

})();