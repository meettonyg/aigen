/**
 * Code Metrics Analyzer
 * Analyzes codebase changes and calculates reduction metrics
 */

window.MKCG_CodeMetrics = {
    // Scan current codebase structure
    analyzeCurrentCodebase() {
        console.log('📊 Analyzing Current Codebase...');
        
        const analysis = {
            files: this.scanFiles(),
            structure: this.analyzeStructure(),
            complexity: this.assessComplexity(),
            patterns: this.identifyPatterns()
        };
        
        return analysis;
    },
    
    scanFiles() {
        const jsFiles = Array.from(document.querySelectorAll('script[src*="mkcg"], script[src*="media-kit"]'));
        const cssFiles = Array.from(document.querySelectorAll('link[href*="mkcg"], link[href*="media-kit"]'));
        
        const fileAnalysis = {
            javascript: {
                count: jsFiles.length,
                files: jsFiles.map(script => ({
                    src: script.src,
                    type: this.categorizeJSFile(script.src)
                }))
            },
            css: {
                count: cssFiles.length,
                files: cssFiles.map(link => ({
                    href: link.href,
                    unified: link.href.includes('unified')
                }))
            },
            total: jsFiles.length + cssFiles.length
        };
        
        console.log(`  JavaScript files: ${fileAnalysis.javascript.count}`);
        console.log(`  CSS files: ${fileAnalysis.css.count}`);
        console.log(`  Total files: ${fileAnalysis.total}`);
        
        return fileAnalysis;
    },
    
    categorizeJSFile(src) {
        if (src.includes('simple-')) return 'simplified';
        if (src.includes('enhanced-')) return 'enhanced';
        if (src.includes('generator')) return 'generator';
        if (src.includes('form-utils')) return 'utility';
        return 'other';
    },
    
    analyzeStructure() {
        const structure = {
            hasSimpleAjax: !!(window.makeAjaxRequest || window.simpleAjax),
            hasSimpleEvents: !!(window.AppEvents || window.eventBus),
            hasSimpleNotifications: !!(window.showNotification || window.EnhancedUIFeedback?.showToast),
            hasUnifiedCSS: !!document.querySelector('link[href*="unified"]'),
            hasDataManager: !!(window.MKCG_DataManager || window.dataManager),
            hasEnhancedSystems: !!(window.EnhancedAjaxManager || window.enhancedSystem)
        };
        
        console.log('  Architecture Analysis:');
        console.log(`    Simple AJAX: ${structure.hasSimpleAjax ? '✅' : '❌'}`);
        console.log(`    Simple Events: ${structure.hasSimpleEvents ? '✅' : '❌'}`);
        console.log(`    Simple Notifications: ${structure.hasSimpleNotifications ? '✅' : '❌'}`);
        console.log(`    Unified CSS: ${structure.hasUnifiedCSS ? '✅' : '❌'}`);
        console.log(`    Data Manager: ${structure.hasDataManager ? '⚠️' : '✅'} (should be simplified)`);
        console.log(`    Enhanced Systems: ${structure.hasEnhancedSystems ? '⚠️' : '✅'} (should be removed)`);
        
        return structure;
    },
    
    assessComplexity() {
        const complexity = {
            dualSystems: this.checkForDualSystems(),
            fallbackLayers: this.checkForFallbackLayers(),
            errorHandlingComplexity: this.checkErrorHandlingComplexity(),
            initializationComplexity: this.checkInitializationComplexity()
        };
        
        console.log('  Complexity Assessment:');
        console.log(`    Dual Systems: ${complexity.dualSystems ? '⚠️ Found' : '✅ Eliminated'}`);
        console.log(`    Fallback Layers: ${complexity.fallbackLayers ? '⚠️ Found' : '✅ Simplified'}`);
        console.log(`    Error Handling: ${complexity.errorHandlingComplexity ? '⚠️ Complex' : '✅ Simplified'}`);
        console.log(`    Initialization: ${complexity.initializationComplexity ? '⚠️ Complex' : '✅ Simplified'}`);
        
        return complexity;
    },
    
    checkForDualSystems() {
        // Check for legacy + enhanced versions
        const hasEnhanced = !!window.EnhancedAjaxManager;
        const hasLegacy = !!window.legacySystem;
        return hasEnhanced && hasLegacy;
    },
    
    checkForFallbackLayers() {
        // Check for multiple fallback strategies
        const scripts = Array.from(document.querySelectorAll('script'));
        return scripts.some(script => 
            script.textContent && script.textContent.includes('fallback')
        );
    },
    
    checkErrorHandlingComplexity() {
        // Check for over-engineered error handling
        return !!(window.EnhancedErrorHandler || window.errorManager);
    },
    
    checkInitializationComplexity() {
        // Check for complex initialization sequences
        return !!(window.initializationManager || window.complexInit);
    },
    
    identifyPatterns() {
        const patterns = {
            simplificationPatterns: this.findSimplificationPatterns(),
            antiPatterns: this.findAntiPatterns(),
            bestPractices: this.findBestPractices()
        };
        
        console.log('  Pattern Analysis:');
        console.log(`    Simplification Patterns: ${patterns.simplificationPatterns.length}`);
        console.log(`    Anti-Patterns: ${patterns.antiPatterns.length}`);
        console.log(`    Best Practices: ${patterns.bestPractices.length}`);
        
        return patterns;
    },
    
    findSimplificationPatterns() {
        const patterns = [];
        
        if (window.AppEvents) patterns.push('Simple Event Bus');
        if (window.makeAjaxRequest) patterns.push('Simple AJAX');
        if (window.showNotification) patterns.push('Simple Notifications');
        if (document.querySelector('link[href*="unified"]')) patterns.push('Unified CSS');
        
        return patterns;
    },
    
    findAntiPatterns() {
        const antiPatterns = [];
        
        if (window.EnhancedAjaxManager) antiPatterns.push('Over-engineered AJAX');
        if (window.MKCG_DataManager && window.dataManager) antiPatterns.push('Dual Data Systems');
        if (window.complexValidation) antiPatterns.push('Over-validation');
        
        return antiPatterns;
    },
    
    findBestPractices() {
        const bestPractices = [];
        
        if (window.AppEvents && !window.MKCG_DataManager) bestPractices.push('Single Event System');
        if (!window.EnhancedAjaxManager && window.fetch) bestPractices.push('Native Fetch Usage');
        if (document.querySelector('link[href*="unified"]')) bestPractices.push('CSS Consolidation');
        
        return bestPractices;
    },
    
    // Calculate metrics against targets
    calculateMetrics() {
        const analysis = this.analyzeCurrentCodebase();
        
        const metrics = {
            fileReduction: this.calculateFileReduction(analysis.files.total),
            complexityReduction: this.calculateComplexityReduction(analysis.complexity),
            architectureScore: this.calculateArchitectureScore(analysis.structure),
            simplificationScore: this.calculateSimplificationScore(analysis.patterns)
        };
        
        return metrics;
    },
    
    calculateFileReduction(currentFiles) {
        const originalFiles = 23; // From simplification plan
        const targetFiles = 14;
        
        const actualReduction = ((originalFiles - currentFiles) / originalFiles) * 100;
        const targetReduction = ((originalFiles - targetFiles) / originalFiles) * 100;
        const achievement = (actualReduction / targetReduction) * 100;
        
        return {
            original: originalFiles,
            current: currentFiles,
            target: targetFiles,
            actualReduction: Math.round(actualReduction),
            targetReduction: Math.round(targetReduction),
            achievement: Math.round(achievement),
            meetsTarget: currentFiles <= targetFiles
        };
    },
    
    calculateComplexityReduction(complexity) {
        const complexityFactors = Object.values(complexity);
        const complexCount = complexityFactors.filter(Boolean).length;
        const simplificationScore = ((4 - complexCount) / 4) * 100;
        
        return {
            complexCount,
            simplificationScore: Math.round(simplificationScore),
            meetsTarget: simplificationScore >= 70
        };
    },
    
    calculateArchitectureScore(structure) {
        const positiveFactors = [
            structure.hasSimpleAjax,
            structure.hasSimpleEvents,
            structure.hasSimpleNotifications,
            structure.hasUnifiedCSS
        ].filter(Boolean).length;
        
        const negativeFactors = [
            structure.hasDataManager,
            structure.hasEnhancedSystems
        ].filter(Boolean).length;
        
        const score = ((positiveFactors * 25) - (negativeFactors * 25));
        
        return {
            positiveFactors,
            negativeFactors,
            score: Math.max(0, score),
            meetsTarget: score >= 75
        };
    },
    
    calculateSimplificationScore(patterns) {
        const simplificationWeight = patterns.simplificationPatterns.length * 25;
        const antiPatternPenalty = patterns.antiPatterns.length * 15;
        const bestPracticeBonus = patterns.bestPractices.length * 10;
        
        const score = simplificationWeight - antiPatternPenalty + bestPracticeBonus;
        
        return {
            simplificationWeight,
            antiPatternPenalty,
            bestPracticeBonus,
            score: Math.max(0, score),
            meetsTarget: score >= 80
        };
    },
    
    // Generate comprehensive report
    generateCodeMetricsReport() {
        console.clear();
        console.log('%c📊 Code Metrics Analysis Report', 'color: #2196F3; font-size: 18px; font-weight: bold;');
        console.log('=====================================\n');
        
        const metrics = this.calculateMetrics();
        
        console.log('📁 FILE REDUCTION ANALYSIS');
        console.log('----------------------------');
        console.log(`Original Files: ${metrics.fileReduction.original}`);
        console.log(`Current Files: ${metrics.fileReduction.current}`);
        console.log(`Target Files: ${metrics.fileReduction.target}`);
        console.log(`Actual Reduction: ${metrics.fileReduction.actualReduction}%`);
        console.log(`Target Achievement: ${metrics.fileReduction.achievement}%`);
        console.log(`Status: ${metrics.fileReduction.meetsTarget ? '✅ TARGET MET' : '❌ NEEDS IMPROVEMENT'}\n`);
        
        console.log('🔧 COMPLEXITY REDUCTION ANALYSIS');
        console.log('----------------------------------');
        console.log(`Complex Systems Remaining: ${metrics.complexityReduction.complexCount}/4`);
        console.log(`Simplification Score: ${metrics.complexityReduction.simplificationScore}%`);
        console.log(`Status: ${metrics.complexityReduction.meetsTarget ? '✅ TARGET MET' : '❌ NEEDS IMPROVEMENT'}\n`);
        
        console.log('🏗️ ARCHITECTURE SCORE ANALYSIS');
        console.log('--------------------------------');
        console.log(`Positive Factors: ${metrics.architectureScore.positiveFactors}/4`);
        console.log(`Negative Factors: ${metrics.architectureScore.negativeFactors}/2`);
        console.log(`Architecture Score: ${metrics.architectureScore.score}%`);
        console.log(`Status: ${metrics.architectureScore.meetsTarget ? '✅ TARGET MET' : '❌ NEEDS IMPROVEMENT'}\n`);
        
        console.log('⚡ SIMPLIFICATION PATTERN ANALYSIS');
        console.log('-----------------------------------');
        console.log(`Simplification Weight: +${metrics.simplificationScore.simplificationWeight}`);
        console.log(`Anti-Pattern Penalty: -${metrics.simplificationScore.antiPatternPenalty}`);
        console.log(`Best Practice Bonus: +${metrics.simplificationScore.bestPracticeBonus}`);
        console.log(`Total Score: ${metrics.simplificationScore.score}%`);
        console.log(`Status: ${metrics.simplificationScore.meetsTarget ? '✅ TARGET MET' : '❌ NEEDS IMPROVEMENT'}\n`);
        
        // Calculate overall success rate
        const successMetrics = [
            metrics.fileReduction.meetsTarget,
            metrics.complexityReduction.meetsTarget,
            metrics.architectureScore.meetsTarget,
            metrics.simplificationScore.meetsTarget
        ];
        
        const successCount = successMetrics.filter(Boolean).length;
        const overallScore = (successCount / successMetrics.length) * 100;
        
        console.log('🎯 OVERALL SIMPLIFICATION SUCCESS');
        console.log('==================================');
        console.log(`Metrics Passed: ${successCount}/4`);
        console.log(`Overall Score: ${overallScore}%`);
        
        if (overallScore === 100) {
            console.log('%c🏆 EXCELLENT: All simplification targets achieved!', 'color: #4CAF50; font-weight: bold; font-size: 16px;');
        } else if (overallScore >= 75) {
            console.log('%c✅ GOOD: Most simplification targets met', 'color: #FF9800; font-weight: bold; font-size: 16px;');
        } else if (overallScore >= 50) {
            console.log('%c⚠️ MODERATE: Some simplification achieved', 'color: #FF5722; font-weight: bold; font-size: 16px;');
        } else {
            console.log('%c❌ NEEDS WORK: Simplification targets not met', 'color: #F44336; font-weight: bold; font-size: 16px;');
        }
        
        console.log('\nRecommendations:');
        const recommendations = this.generateRecommendations(metrics);
        recommendations.forEach((rec, index) => {
            console.log(`${index + 1}. ${rec}`);
        });
        
        return {
            overallScore,
            metrics,
            recommendations
        };
    },
    
    generateRecommendations(metrics) {
        const recommendations = [];
        
        if (!metrics.fileReduction.meetsTarget) {
            recommendations.push('Continue file consolidation - remove remaining unused files');
        }
        
        if (!metrics.complexityReduction.meetsTarget) {
            recommendations.push('Eliminate remaining complex systems (DataManager, Enhanced systems)');
        }
        
        if (!metrics.architectureScore.meetsTarget) {
            recommendations.push('Complete migration to simplified architecture patterns');
        }
        
        if (!metrics.simplificationScore.meetsTarget) {
            recommendations.push('Remove anti-patterns and implement more simplification patterns');
        }
        
        if (recommendations.length === 0) {
            recommendations.push('Excellent work! All simplification targets achieved.');
        }
        
        return recommendations;
    },
    
    // Quick code metrics check
    quickMetricsCheck() {
        const analysis = this.analyzeCurrentCodebase();
        
        const checks = {
            fileCount: analysis.files.total <= 14,
            hasSimpleAjax: analysis.structure.hasSimpleAjax,
            hasUnifiedCSS: analysis.structure.hasUnifiedCSS,
            noComplexSystems: !analysis.structure.hasEnhancedSystems
        };
        
        const passed = Object.values(checks).filter(Boolean).length;
        const total = Object.keys(checks).length;
        
        console.log('⚡ Quick Code Metrics Check');
        console.log(`File Count (≤14): ${checks.fileCount ? '✅' : '❌'} (${analysis.files.total})`);
        console.log(`Simple AJAX: ${checks.hasSimpleAjax ? '✅' : '❌'}`);
        console.log(`Unified CSS: ${checks.hasUnifiedCSS ? '✅' : '❌'}`);
        console.log(`No Complex Systems: ${checks.noComplexSystems ? '✅' : '❌'}`);
        console.log(`Score: ${passed}/${total} (${Math.round(passed/total*100)}%)`);
        
        return { passed, total, score: Math.round(passed/total*100) };
    },
    
    // Lines of Code Estimation
    estimateCurrentLOC() {
        const analysis = this.analyzeCurrentCodebase();
        
        // Estimate based on simplified file structure
        const jsFilesEstimate = analysis.files.javascript.count * 80; // Simplified JS files
        const cssEstimate = analysis.files.css.count * 400; // CSS files
        const phpEstimate = 8 * 120; // Estimated PHP files
        
        const totalEstimate = jsFilesEstimate + cssEstimate + phpEstimate;
        const originalLOC = 5200; // From simplification plan
        const reductionPercent = ((originalLOC - totalEstimate) / originalLOC) * 100;
        
        console.log('📝 Lines of Code Estimation');
        console.log(`JavaScript: ~${jsFilesEstimate} lines (${analysis.files.javascript.count} files)`);
        console.log(`CSS: ~${cssEstimate} lines (${analysis.files.css.count} files)`);
        console.log(`PHP: ~${phpEstimate} lines (estimated)`);
        console.log(`Total Estimated: ~${totalEstimate} lines`);
        console.log(`Original: ${originalLOC} lines`);
        console.log(`Reduction: ${Math.round(reductionPercent)}%`);
        console.log(`Target: 60% reduction`);
        console.log(`Status: ${reductionPercent >= 60 ? '✅ TARGET MET' : '❌ NEEDS IMPROVEMENT'}`);
        
        return {
            current: totalEstimate,
            original: originalLOC,
            reduction: Math.round(reductionPercent),
            target: 60,
            meetsTarget: reductionPercent >= 60
        };
    }
};

// Global functions for easy access
window.runCodeMetricsAnalysis = () => window.MKCG_CodeMetrics.generateCodeMetricsReport();
window.quickCodeMetrics = () => window.MKCG_CodeMetrics.quickMetricsCheck();
window.estimateLOC = () => window.MKCG_CodeMetrics.estimateCurrentLOC();
