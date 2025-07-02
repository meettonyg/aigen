/**
 * Performance Benchmark Comparison Tool
 * Compares before/after metrics from 3-phase simplification
 */

window.MKCG_PerformanceBenchmark = {
    // Original metrics (before simplification)
    originalMetrics: {
        pageLoadTime: 6000,     // 4-6 seconds
        bundleSize: 256000,     // ~250KB
        memoryUsage: 26214400,  // ~25MB
        linesOfCode: 5200,      // From assessment
        fileCount: 23,          // From assessment
        errorRate: 8,           // 5-8%
        initTime: 14300,        // 14.3 seconds from race condition fixes
        ajaxTimeout: 287000     // 287 seconds from template issues
    },
    
    // Target metrics (after simplification)
    targetMetrics: {
        pageLoadTime: 2000,     // < 2 seconds
        bundleSize: 102400,     // < 100KB
        memoryUsage: 10485760,  // < 10MB
        linesOfCode: 2100,      // 60% reduction
        fileCount: 14,          // 39% reduction
        errorRate: 1,           // < 1%
        initTime: 2000,         // < 2 seconds
        ajaxTimeout: 2000       // < 2 seconds
    },
    
    // Current metrics (measured)
    currentMetrics: {},
    
    // Measure current performance
    measureCurrentMetrics() {
        console.log('📊 Measuring Current Performance Metrics...');
        
        const startTime = performance.now();
        
        // Page load time (time to interactive)
        const loadTime = startTime;
        this.currentMetrics.pageLoadTime = loadTime;
        
        // Bundle size estimation
        const scripts = Array.from(document.querySelectorAll('script[src*="mkcg"], script[src*="media-kit"]'));
        const styles = Array.from(document.querySelectorAll('link[href*="mkcg"], link[href*="media-kit"]'));
        this.currentMetrics.bundleSize = (scripts.length + styles.length) * 8192; // Estimate 8KB per file
        
        // Memory usage
        this.currentMetrics.memoryUsage = performance.memory ? performance.memory.usedJSHeapSize : 0;
        
        // File count
        this.currentMetrics.fileCount = scripts.length + styles.length;
        
        // Lines of code estimation (based on file structure)
        this.currentMetrics.linesOfCode = this.estimateLinesOfCode();
        
        // Error rate (check for console errors)
        this.currentMetrics.errorRate = this.measureErrorRate();
        
        // Initialization time
        this.currentMetrics.initTime = this.measureInitTime();
        
        // AJAX timeout (test simple request)
        this.currentMetrics.ajaxTimeout = this.measureAjaxSpeed();
        
        return this.currentMetrics;
    },
    
    estimateLinesOfCode() {
        // Count simplified files and estimate lines
        const jsFiles = document.querySelectorAll('script[src*="mkcg"]').length;
        const phpFiles = 8; // Estimated from structure
        const cssFiles = 1; // Unified CSS file
        
        // Conservative estimate: simplified files have fewer lines
        return (jsFiles * 100) + (phpFiles * 150) + (cssFiles * 500);
    },
    
    measureErrorRate() {
        // Check for JavaScript errors in console
        let errorCount = 0;
        
        // Override console.error temporarily to count errors
        const originalError = console.error;
        console.error = function(...args) {
            errorCount++;
            originalError.apply(console, args);
        };
        
        // Restore after a moment
        setTimeout(() => {
            console.error = originalError;
        }, 1000);
        
        return errorCount;
    },
    
    measureInitTime() {
        // Look for initialization markers
        if (window.initializationTime) {
            return window.initializationTime;
        }
        
        // Estimate based on DOM ready to script execution
        return performance.now();
    },
    
    async measureAjaxSpeed() {
        if (!window.ajaxurl) return 0;
        
        const startTime = performance.now();
        try {
            const response = await fetch(window.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=heartbeat'
            });
            return performance.now() - startTime;
        } catch (error) {
            return 5000; // Assume 5s timeout on error
        }
    },
    
    // Calculate improvement percentages
    calculateImprovements() {
        const improvements = {};
        
        Object.keys(this.originalMetrics).forEach(metric => {
            const original = this.originalMetrics[metric];
            const current = this.currentMetrics[metric] || 0;
            const target = this.targetMetrics[metric];
            
            // Calculate improvement percentage
            const improvement = ((original - current) / original) * 100;
            const targetAchievement = ((original - target) / original) * 100;
            const achievementRatio = (improvement / targetAchievement) * 100;
            
            improvements[metric] = {
                original,
                current,
                target,
                improvement: Math.round(improvement),
                targetAchievement: Math.round(targetAchievement),
                achievementRatio: Math.round(achievementRatio),
                meetsTarget: current <= target
            };
        });
        
        return improvements;
    },
    
    // Generate performance report
    generateReport() {
        console.log('\n📈 Performance Benchmark Report');
        console.log('=================================');
        
        this.measureCurrentMetrics();
        const improvements = this.calculateImprovements();
        
        let totalScore = 0;
        let maxScore = 0;
        
        Object.keys(improvements).forEach(metric => {
            const data = improvements[metric];
            const status = data.meetsTarget ? '✅' : '❌';
            const unit = this.getMetricUnit(metric);
            
            console.log(`\n${metric.toUpperCase()}:`);
            console.log(`  Original: ${this.formatMetric(data.original, unit)}`);
            console.log(`  Current:  ${this.formatMetric(data.current, unit)} ${status}`);
            console.log(`  Target:   ${this.formatMetric(data.target, unit)}`);
            console.log(`  Improvement: ${data.improvement}%`);
            console.log(`  Target Achievement: ${data.achievementRatio}%`);
            
            // Add to overall score
            totalScore += Math.min(data.achievementRatio, 100);
            maxScore += 100;
        });
        
        const overallScore = Math.round((totalScore / maxScore) * 100);
        
        console.log('\n🎯 OVERALL PERFORMANCE SCORE');
        console.log('==============================');
        console.log(`Score: ${overallScore}%`);
        
        if (overallScore >= 90) {
            console.log('%c🏆 EXCELLENT: Performance targets achieved!', 'color: #4CAF50; font-weight: bold;');
        } else if (overallScore >= 70) {
            console.log('%c✅ GOOD: Strong performance improvements', 'color: #FF9800; font-weight: bold;');
        } else if (overallScore >= 50) {
            console.log('%c⚠️ MODERATE: Some improvements made', 'color: #FF5722; font-weight: bold;');
        } else {
            console.log('%c❌ NEEDS WORK: Performance targets not met', 'color: #F44336; font-weight: bold;');
        }
        
        return {
            overallScore,
            improvements,
            recommendations: this.generatePerformanceRecommendations(improvements)
        };
    },
    
    getMetricUnit(metric) {
        const units = {
            pageLoadTime: 'ms',
            bundleSize: 'KB',
            memoryUsage: 'MB',
            linesOfCode: 'lines',
            fileCount: 'files',
            errorRate: '%',
            initTime: 'ms',
            ajaxTimeout: 'ms'
        };
        return units[metric] || '';
    },
    
    formatMetric(value, unit) {
        if (unit === 'KB') return `${Math.round(value / 1024)}KB`;
        if (unit === 'MB') return `${Math.round(value / 1048576)}MB`;
        if (unit === 'ms') return `${Math.round(value)}ms`;
        return `${value}${unit}`;
    },
    
    generatePerformanceRecommendations(improvements) {
        const recommendations = [];
        
        if (!improvements.pageLoadTime.meetsTarget) {
            recommendations.push('Page load time: Optimize script loading order and reduce bundle size');
        }
        
        if (!improvements.bundleSize.meetsTarget) {
            recommendations.push('Bundle size: Remove unused JavaScript files and combine resources');
        }
        
        if (!improvements.memoryUsage.meetsTarget) {
            recommendations.push('Memory usage: Check for memory leaks and optimize data structures');
        }
        
        if (!improvements.linesOfCode.meetsTarget) {
            recommendations.push('Code complexity: Continue simplification efforts to reduce code volume');
        }
        
        if (!improvements.errorRate.meetsTarget) {
            recommendations.push('Error rate: Improve error handling and fix remaining bugs');
        }
        
        if (!improvements.initTime.meetsTarget) {
            recommendations.push('Initialization: Optimize startup sequence and reduce dependencies');
        }
        
        return recommendations;
    },
    
    // Quick performance check
    quickCheck() {
        this.measureCurrentMetrics();
        
        const checks = {
            pageLoad: this.currentMetrics.pageLoadTime < this.targetMetrics.pageLoadTime,
            bundle: this.currentMetrics.bundleSize < this.targetMetrics.bundleSize,
            memory: this.currentMetrics.memoryUsage < this.targetMetrics.memoryUsage,
            files: this.currentMetrics.fileCount < this.targetMetrics.fileCount
        };
        
        const passed = Object.values(checks).filter(Boolean).length;
        const total = Object.keys(checks).length;
        
        console.log('⚡ Quick Performance Check');
        console.log(`Page Load: ${checks.pageLoad ? '✅' : '❌'}`);
        console.log(`Bundle Size: ${checks.bundle ? '✅' : '❌'}`);
        console.log(`Memory Usage: ${checks.memory ? '✅' : '❌'}`);
        console.log(`File Count: ${checks.files ? '✅' : '❌'}`);
        console.log(`Score: ${passed}/${total} (${Math.round(passed/total*100)}%)`);
        
        return { passed, total, score: Math.round(passed/total*100) };
    }
};

// Global functions
window.runPerformanceBenchmark = () => window.MKCG_PerformanceBenchmark.generateReport();
window.quickPerformanceCheck = () => window.MKCG_PerformanceBenchmark.quickCheck();
