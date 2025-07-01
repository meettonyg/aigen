/**
 * Enhanced Validation Manager - Comprehensive Client-Side Validation
 * Provides robust validation before server requests to prevent errors
 */

class EnhancedValidationManager {
    constructor() {
        this.validationRules = {
            // Authority Hook validation
            authority_hook: {
                required: true,
                minLength: 10,
                maxLength: 500,
                pattern: /\w+/,
                customValidation: this.validateAuthorityHook.bind(this)
            },
            
            // Individual authority hook components
            who: {
                required: false,
                minLength: 3,
                maxLength: 100,
                pattern: /\w+/,
                placeholder: 'your audience'
            },
            result: {
                required: false,
                minLength: 3,
                maxLength: 100,
                pattern: /\w+/,
                placeholder: 'achieve their goals'
            },
            when: {
                required: false,
                minLength: 3,
                maxLength: 100,
                pattern: /\w+/,
                placeholder: 'they need help'
            },
            how: {
                required: false,
                minLength: 3,
                maxLength: 100,
                pattern: /\w+/,
                placeholder: 'through your method'
            },
            
            // Topic validation
            topic: {
                required: false,
                minLength: 10,
                maxLength: 200,
                pattern: /\w+/,
                customValidation: this.validateTopic.bind(this)
            },
            
            // Topics array validation
            topics: {
                required: false,
                validateArray: true,
                minItems: 0,
                maxItems: 5,
                itemValidation: 'topic'
            },
            
            // Entry ID validation
            entry_id: {
                required: true,
                pattern: /^\d+$/,
                min: 1
            },
            
            // Question validation
            question: {
                required: false,
                minLength: 10,
                maxLength: 300,
                pattern: /\?/,
                customValidation: this.validateQuestion.bind(this)
            }
        };

        this.errorMessages = {
            required: 'This field is required',
            minLength: 'Must be at least {min} characters long',
            maxLength: 'Must be no more than {max} characters long',
            pattern: 'Please enter a valid value',
            min: 'Value must be at least {min}',
            max: 'Value must be no more than {max}',
            minItems: 'Must have at least {min} items',
            maxItems: 'Must have no more than {max} items',
            custom: 'Please check the value and try again'
        };

        this.validationCache = new Map();
        this.validationHistory = [];
        
        console.log('✅ Enhanced Validation Manager initialized');
    }

    /**
     * Validate form data before submission
     */
    validateBeforeSubmit(formData, action = 'submit') {
        console.log('🔍 Validating form data before submission:', { formData, action });

        const results = {
            valid: true,
            errors: [],
            warnings: [],
            fieldErrors: {},
            summary: {
                total: 0,
                passed: 0,
                failed: 0,
                warnings: 0
            }
        };

        // Determine required fields based on action
        const requiredFields = this.getRequiredFieldsForAction(action);
        
        // Validate each field in form data
        Object.keys(formData).forEach(fieldName => {
            const value = formData[fieldName];
            const isRequired = requiredFields.includes(fieldName);
            
            const fieldResult = this.validateField(fieldName, value, { 
                required: isRequired,
                context: action 
            });
            
            results.summary.total++;
            
            if (fieldResult.valid) {
                results.summary.passed++;
            } else {
                results.summary.failed++;
                results.valid = false;
                results.errors.push(`${fieldName}: ${fieldResult.errors.join(', ')}`);
                results.fieldErrors[fieldName] = fieldResult.errors;
            }
            
            if (fieldResult.warnings.length > 0) {
                results.summary.warnings++;
                results.warnings.push(`${fieldName}: ${fieldResult.warnings.join(', ')}`);
            }
        });

        // Custom cross-field validation
        const crossFieldResult = this.validateCrossFields(formData, action);
        if (!crossFieldResult.valid) {
            results.valid = false;
            results.errors.push(...crossFieldResult.errors);
        }

        // Log validation results
        console.log('📊 Validation results:', results);
        
        // Store in history
        this.validationHistory.push({
            timestamp: Date.now(),
            action,
            formData: { ...formData },
            results: { ...results }
        });

        return results;
    }

    /**
     * Validate individual field
     */
    validateField(fieldName, value, options = {}) {
        const cacheKey = `${fieldName}:${JSON.stringify(value)}:${JSON.stringify(options)}`;
        
        // Check cache first
        if (this.validationCache.has(cacheKey)) {
            return this.validationCache.get(cacheKey);
        }

        console.log(`🔍 Validating field: ${fieldName}`, { value, options });

        const result = {
            valid: true,
            errors: [],
            warnings: [],
            fieldName,
            value
        };

        // Get validation rules for field
        const rules = this.getFieldRules(fieldName, options);
        
        // Skip validation if no rules defined
        if (!rules) {
            console.log(`⚠️ No validation rules found for field: ${fieldName}`);
            this.validationCache.set(cacheKey, result);
            return result;
        }

        // Required field validation
        if (rules.required && this.isEmpty(value)) {
            result.valid = false;
            result.errors.push(this.formatErrorMessage('required', rules));
        }

        // Skip other validations if field is empty and not required
        if (this.isEmpty(value) && !rules.required) {
            this.validationCache.set(cacheKey, result);
            return result;
        }

        // String length validation
        if (typeof value === 'string') {
            if (rules.minLength && value.length < rules.minLength) {
                result.valid = false;
                result.errors.push(this.formatErrorMessage('minLength', rules, { min: rules.minLength }));
            }
            
            if (rules.maxLength && value.length > rules.maxLength) {
                result.valid = false;
                result.errors.push(this.formatErrorMessage('maxLength', rules, { max: rules.maxLength }));
            }
        }

        // Numeric validation
        if (typeof value === 'number' || (typeof value === 'string' && !isNaN(value))) {
            const numValue = Number(value);
            
            if (rules.min !== undefined && numValue < rules.min) {
                result.valid = false;
                result.errors.push(this.formatErrorMessage('min', rules, { min: rules.min }));
            }
            
            if (rules.max !== undefined && numValue > rules.max) {
                result.valid = false;
                result.errors.push(this.formatErrorMessage('max', rules, { max: rules.max }));
            }
        }

        // Pattern validation
        if (rules.pattern && typeof value === 'string') {
            if (!rules.pattern.test(value)) {
                result.valid = false;
                result.errors.push(this.formatErrorMessage('pattern', rules));
            }
        }

        // Array validation
        if (rules.validateArray && Array.isArray(value)) {
            const arrayResult = this.validateArray(value, rules, fieldName);
            if (!arrayResult.valid) {
                result.valid = false;
                result.errors.push(...arrayResult.errors);
            }
            result.warnings.push(...arrayResult.warnings);
        }

        // Placeholder check (warning, not error)
        if (rules.placeholder && value === rules.placeholder) {
            result.warnings.push(`Field appears to contain placeholder text`);
        }

        // Custom validation
        if (rules.customValidation && typeof rules.customValidation === 'function') {
            try {
                const customResult = rules.customValidation(value, fieldName, options);
                if (!customResult.valid) {
                    result.valid = false;
                    result.errors.push(...(customResult.errors || ['Custom validation failed']));
                }
                if (customResult.warnings) {
                    result.warnings.push(...customResult.warnings);
                }
            } catch (error) {
                console.error('Custom validation error:', error);
                result.warnings.push('Custom validation could not be completed');
            }
        }

        // Cache result
        this.validationCache.set(cacheKey, result);
        
        console.log(`📊 Field validation result for ${fieldName}:`, result);
        return result;
    }

    /**
     * Validate array of values
     */
    validateArray(array, rules, fieldName) {
        const result = {
            valid: true,
            errors: [],
            warnings: []
        };

        // Check array length
        if (rules.minItems && array.length < rules.minItems) {
            result.valid = false;
            result.errors.push(this.formatErrorMessage('minItems', rules, { min: rules.minItems }));
        }

        if (rules.maxItems && array.length > rules.maxItems) {
            result.valid = false;
            result.errors.push(this.formatErrorMessage('maxItems', rules, { max: rules.maxItems }));
        }

        // Validate each item if item validation is specified
        if (rules.itemValidation) {
            array.forEach((item, index) => {
                const itemResult = this.validateField(rules.itemValidation, item, { 
                    context: `${fieldName}[${index}]` 
                });
                
                if (!itemResult.valid) {
                    result.valid = false;
                    result.errors.push(`Item ${index + 1}: ${itemResult.errors.join(', ')}`);
                }
                
                if (itemResult.warnings.length > 0) {
                    result.warnings.push(`Item ${index + 1}: ${itemResult.warnings.join(', ')}`);
                }
            });
        }

        return result;
    }

    /**
     * Custom validation for authority hook
     */
    validateAuthorityHook(value, fieldName, options) {
        const result = { valid: true, errors: [], warnings: [] };

        if (typeof value !== 'string') {
            result.valid = false;
            result.errors.push('Authority hook must be text');
            return result;
        }

        // Check for default/placeholder patterns
        const defaultPatterns = [
            /^I help your audience achieve their goals when they need help through your method\.?$/i,
            /^I help .* achieve .* when .* through .*\.?$/i
        ];

        const isDefault = defaultPatterns.some(pattern => pattern.test(value.trim()));
        if (isDefault) {
            result.warnings.push('Authority hook appears to use default template - consider customizing');
        }

        // Check for completeness
        const requiredElements = ['help', 'when', 'through'];
        const missingElements = requiredElements.filter(element => 
            !value.toLowerCase().includes(element)
        );

        if (missingElements.length > 0) {
            result.warnings.push(`Consider including: ${missingElements.join(', ')}`);
        }

        // Check for professional language
        const unprofessionalWords = ['um', 'uh', 'like', 'you know', 'basically'];
        const foundUnprofessional = unprofessionalWords.filter(word => 
            value.toLowerCase().includes(word)
        );

        if (foundUnprofessional.length > 0) {
            result.warnings.push('Consider using more professional language');
        }

        return result;
    }

    /**
     * Custom validation for topic
     */
    validateTopic(value, fieldName, options) {
        const result = { valid: true, errors: [], warnings: [] };

        if (typeof value !== 'string') {
            result.valid = false;
            result.errors.push('Topic must be text');
            return result;
        }

        // Check for placeholder patterns
        const placeholderPatterns = [
            /^topic \d+ - click to add/i,
            /^click to add/i,
            /^enter your topic/i,
            /^placeholder/i
        ];

        const isPlaceholder = placeholderPatterns.some(pattern => pattern.test(value.trim()));
        if (isPlaceholder) {
            result.warnings.push('Topic appears to be placeholder text');
        }

        // Check for question format (topics should not end with ?)
        if (value.trim().endsWith('?')) {
            result.warnings.push('Topics typically work better as statements rather than questions');
        }

        // Check for engaging elements
        const engagingWords = ['secret', 'proven', 'ultimate', 'complete', 'step-by-step', 'framework'];
        const hasEngagingWords = engagingWords.some(word => 
            value.toLowerCase().includes(word)
        );

        if (!hasEngagingWords && value.length > 20) {
            result.warnings.push('Consider adding engaging words to make the topic more compelling');
        }

        return result;
    }

    /**
     * Custom validation for question
     */
    validateQuestion(value, fieldName, options) {
        const result = { valid: true, errors: [], warnings: [] };

        if (typeof value !== 'string') {
            result.valid = false;
            result.errors.push('Question must be text');
            return result;
        }

        // Questions should end with question mark
        if (!value.trim().endsWith('?')) {
            result.warnings.push('Questions typically end with a question mark');
        }

        // Check for question words
        const questionWords = ['what', 'how', 'why', 'when', 'where', 'who', 'which'];
        const hasQuestionWord = questionWords.some(word => 
            value.toLowerCase().includes(word)
        );

        if (!hasQuestionWord) {
            result.warnings.push('Consider starting with a question word (what, how, why, etc.)');
        }

        return result;
    }

    /**
     * Cross-field validation
     */
    validateCrossFields(formData, action) {
        const result = { valid: true, errors: [] };

        // Authority hook consistency validation
        if (formData.who && formData.result && formData.when && formData.how) {
            const constructedHook = `I help ${formData.who} ${formData.result} when ${formData.when} ${formData.how}.`;
            
            if (formData.authority_hook && 
                formData.authority_hook !== constructedHook && 
                !this.isSimilarText(formData.authority_hook, constructedHook)) {
                result.errors.push('Authority hook components do not match the complete authority hook');
            }
        }

        // Topics and questions relationship
        if (action === 'save_questions' && formData.topics && formData.questions) {
            const topicCount = Object.keys(formData.topics).length;
            const questionCount = Object.keys(formData.questions).length;
            
            if (questionCount > topicCount * 5) {
                result.errors.push('Too many questions for the number of topics provided');
            }
        }

        return result;
    }

    /**
     * Get validation rules for field
     */
    getFieldRules(fieldName, options = {}) {
        let rules = this.validationRules[fieldName];
        
        if (!rules) {
            // Try generic patterns
            if (fieldName.startsWith('topic_')) {
                rules = this.validationRules.topic;
            } else if (fieldName.startsWith('question_')) {
                rules = this.validationRules.question;
            }
        }

        if (!rules) {
            return null;
        }

        // Apply option overrides
        return {
            ...rules,
            ...options
        };
    }

    /**
     * Get required fields for specific action
     */
    getRequiredFieldsForAction(action) {
        const actionRequirements = {
            'generate_topics': ['authority_hook'],
            'save_topics': ['entry_id'],
            'save_authority_hook': ['entry_id', 'who', 'result', 'when', 'how'],
            'save_questions': ['entry_id'],
            'submit': ['entry_id']
        };

        return actionRequirements[action] || [];
    }

    /**
     * Format error message with placeholders
     */
    formatErrorMessage(type, rules, params = {}) {
        let message = this.errorMessages[type] || 'Validation failed';
        
        // Replace placeholders
        Object.keys(params).forEach(key => {
            message = message.replace(new RegExp(`{${key}}`, 'g'), params[key]);
        });

        return message;
    }

    /**
     * Check if value is empty
     */
    isEmpty(value) {
        if (value === null || value === undefined) {
            return true;
        }
        
        if (typeof value === 'string') {
            return value.trim() === '';
        }
        
        if (Array.isArray(value)) {
            return value.length === 0;
        }
        
        if (typeof value === 'object') {
            return Object.keys(value).length === 0;
        }
        
        return false;
    }

    /**
     * Check if two texts are similar (for authority hook consistency)
     */
    isSimilarText(text1, text2, threshold = 0.8) {
        const normalize = (text) => text.toLowerCase().replace(/[^\w\s]/g, '').trim();
        
        const norm1 = normalize(text1);
        const norm2 = normalize(text2);
        
        // Simple similarity check based on common words
        const words1 = norm1.split(/\s+/);
        const words2 = norm2.split(/\s+/);
        
        const commonWords = words1.filter(word => words2.includes(word));
        const similarity = commonWords.length / Math.max(words1.length, words2.length);
        
        return similarity >= threshold;
    }

    /**
     * Show validation errors in UI
     */
    showValidationErrors(validationResult) {
        console.log('📢 Showing validation errors:', validationResult);

        if (window.EnhancedUIFeedback) {
            // Clear previous error banners
            window.EnhancedUIFeedback.clearErrorBanners();
            
            // Show field-specific errors
            Object.keys(validationResult.fieldErrors).forEach(fieldName => {
                const errors = validationResult.fieldErrors[fieldName];
                const fieldElement = this.findFieldElement(fieldName);
                
                if (fieldElement) {
                    this.highlightFieldError(fieldElement);
                    window.EnhancedUIFeedback.showErrorBanner(
                        fieldElement.parentNode, 
                        errors.join(', '), 
                        'error'
                    );
                }
            });
            
            // Show general validation summary
            if (validationResult.errors.length > 0) {
                const errorMessage = {
                    title: 'Validation Error',
                    message: 'Please correct the following issues:',
                    actions: validationResult.errors
                };
                
                window.EnhancedUIFeedback.showToast(errorMessage, 'error', 0);
            }
        } else {
            // Fallback to alert
            const errorMessage = 'Please correct the following issues:\n\n' + 
                validationResult.errors.join('\n');
            alert(errorMessage);
        }
    }

    /**
     * Find DOM element for field
     */
    findFieldElement(fieldName) {
        // Try multiple selector patterns
        const selectors = [
            `#${fieldName}`,
            `[name="${fieldName}"]`,
            `#mkcg-${fieldName}`,
            `#topics-generator-${fieldName}`,
            `#questions-generator-${fieldName}`
        ];

        for (const selector of selectors) {
            const element = document.querySelector(selector);
            if (element) {
                return element;
            }
        }

        return null;
    }

    /**
     * Highlight field with error
     */
    highlightFieldError(fieldElement) {
        fieldElement.style.borderColor = '#e74c3c';
        fieldElement.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
        
        // Remove highlight after user interaction
        const removeHighlight = () => {
            fieldElement.style.borderColor = '';
            fieldElement.style.boxShadow = '';
            fieldElement.removeEventListener('input', removeHighlight);
            fieldElement.removeEventListener('focus', removeHighlight);
        };
        
        fieldElement.addEventListener('input', removeHighlight);
        fieldElement.addEventListener('focus', removeHighlight);
    }

    /**
     * Clear validation cache
     */
    clearCache() {
        this.validationCache.clear();
        console.log('🗑️ Validation cache cleared');
    }

    /**
     * Get validation statistics
     */
    getStats() {
        return {
            cacheSize: this.validationCache.size,
            historySize: this.validationHistory.length,
            rules: Object.keys(this.validationRules).length,
            recentValidations: this.validationHistory.slice(-5)
        };
    }
}

// Initialize global instance
window.EnhancedValidationManager = new EnhancedValidationManager();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedValidationManager;
}

console.log('✅ Enhanced Validation Manager loaded successfully');
