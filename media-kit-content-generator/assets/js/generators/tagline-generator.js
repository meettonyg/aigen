/**
 * Tagline Generator JavaScript - Unified Architecture Implementation
 * Following Topics and Biography Generator patterns with multi-option generation
 * 
 * Architecture: Vanilla JS, unified service integration, BEM methodology
 * Features: Multi-option AI generation, selection interface, service integration
 * 
 * @package Media_Kit_Content_Generator
 * @version 1.0 - Phase 3 Implementation following unified patterns
 */

(function() {
  'use strict';
  
  /**
   * Tagline Generator - Following Topics/Biography Generator Architecture
   * 3-step initialization: load data, bind events, update display
   */
  const TaglineGenerator = {
    
    // Essential data storage
    fields: {
      // Authority Hook components
      who: '',
      what: '',
      when: '',
      how: '',
      
      // Impact Intro components  
      where: '',
      why: '',
      
      // Additional context fields
      industry: '',
      uniqueFactors: '',
      existingTaglines: '',
      
      // Tagline settings
      style: 'problem-focused',
      tone: 'professional',
      length: 'medium'
    },
    
    // Generated taglines storage
    taglines: [],
    selectedTagline: null,
    
    // Form metadata
    metadata: {
      postId: 0,
      nonce: '',
      hasData: false
    },
    
    /**
     * Initialize Tagline Generator - Following unified pattern
     */
    init: function() {
      console.log('🎯 Tagline Generator: Simple initialization starting');
      
      // Step 1: Load existing data
      this.loadExistingData();
      
      // Step 2: Bind form events  
      this.bindEvents();
      
      // Step 3: Update display
      this.updateDisplay();
      
      console.log('✅ Tagline Generator: Simple initialization completed');
    },
    
    /**
     * Load data from PHP or defaults - Following Topics Generator pattern
     */
    loadExistingData: function() {
      // Check if PHP passed data
      if (window.MKCG_Tagline_Data) {
        // Load metadata
        this.metadata = {
          postId: window.MKCG_Tagline_Data.postId || 0,
          nonce: document.querySelector('#tagline-nonce')?.value || '',
          hasData: window.MKCG_Tagline_Data.hasData || false
        };
        
        // Check if we're in non-entry mode
        if (window.MKCG_Tagline_Data.noEntryParam) {
          console.log('📝 No entry parameter - using empty data');
          this.setDefaultData();
        } else if (window.MKCG_Tagline_Data.hasData) {
          console.log('📝 Loading data from PHP:', window.MKCG_Tagline_Data);
          this.populateFromPHPData(window.MKCG_Tagline_Data);
        } else {
          console.log('📝 No data found but entry param exists - using empty data');
          this.setDefaultData();
        }
      } else {
        console.log('📝 MKCG_Tagline_Data not available - using empty data');
        this.setDefaultData();
      }
      
      // Try to populate service fields if builders are already visible
      this.checkAndPopulateIfVisible();
    },
    
    /**
     * Check if service builders are visible and populate if needed
     */
    checkAndPopulateIfVisible: function() {
      setTimeout(() => {
        // Check Authority Hook Builder
        const authorityBuilder = document.querySelector('#tagline-generator-authority-hook-builder');
        if (authorityBuilder && !authorityBuilder.classList.contains('generator__builder--hidden')) {
          console.log('🔧 Authority Hook Builder already visible, attempting population...');
          this.populateAuthorityHookFields();
        }
        
        // Check Impact Intro Builder
        const impactBuilder = document.querySelector('#tagline-generator-impact-intro-builder');
        if (impactBuilder && !impactBuilder.classList.contains('generator__builder--hidden')) {
          console.log('🔧 Impact Intro Builder already visible, attempting population...');
          this.populateImpactIntroFields();
        }
      }, 500);
    },
    
    /**
     * Populate from PHP data
     */
    populateFromPHPData: function(phpData) {
      // Load Authority Hook data
      if (phpData.authorityHook) {
        this.fields.who = phpData.authorityHook.who || '';
        this.fields.what = phpData.authorityHook.what || '';
        this.fields.when = phpData.authorityHook.when || '';
        this.fields.how = phpData.authorityHook.how || '';
        
        console.log('📝 Stored authority hook data:', this.fields);
        this.updateInputFields('authority');
      }
      
      // Load Impact Intro data
      if (phpData.impactIntro) {
        this.fields.where = phpData.impactIntro.where || '';
        this.fields.why = phpData.impactIntro.why || '';
        
        console.log('📝 Stored impact intro data:', this.fields);
        this.updateInputFields('impact');
      }
      
      // Load additional context
      if (phpData.additionalContext) {
        this.fields.industry = phpData.additionalContext.industry || '';
        this.fields.uniqueFactors = phpData.additionalContext.uniqueFactors || '';
        this.fields.existingTaglines = phpData.additionalContext.existingTaglines || '';
        
        this.updateInputFields('context');
      }
      
      // Load tagline settings
      if (phpData.taglineData) {
        this.fields.style = phpData.taglineData.style || 'problem-focused';
        this.fields.tone = phpData.taglineData.tone || 'professional';
        this.fields.length = phpData.taglineData.length || 'medium';
        
        this.updateInputFields('settings');
        
        // Load existing generated taglines if available
        if (phpData.taglineData.generatedTaglines && phpData.taglineData.generatedTaglines.length > 0) {
          this.taglines = phpData.taglineData.generatedTaglines;
          this.displayTaglineOptions(this.taglines);
        }
        
        // Load selected tagline if available
        if (phpData.taglineData.selectedTagline) {
          this.selectedTagline = phpData.taglineData.selectedTagline;
          this.displaySelectedTagline(this.selectedTagline);
        }
      }
    },
    
    /**
     * Set default data - empty values when no entry param
     */
    setDefaultData: function() {
      // All fields default to empty
      Object.keys(this.fields).forEach(key => {
        if (typeof this.fields[key] === 'string') {
          this.fields[key] = '';
        }
      });
      
      // Set default values for selection fields
      this.fields.style = 'problem-focused';
      this.fields.tone = 'professional';
      this.fields.length = 'medium';
      
      this.updateInputFields('all');
      this.updateDisplay();
    },
    
    /**
     * Update input fields for specific service or all
     */
    updateInputFields: function(serviceType = 'all') {
      if (serviceType === 'authority' || serviceType === 'all') {
        this.updateAuthorityHookInputs();
      }
      
      if (serviceType === 'impact' || serviceType === 'all') {
        this.updateImpactIntroInputs();
      }
      
      if (serviceType === 'context' || serviceType === 'all') {
        this.updateContextFields();
      }
      
      if (serviceType === 'settings' || serviceType === 'all') {
        this.updateSettingsFields();
      }
    },
    
    /**
     * Update Authority Hook input fields
     */
    updateAuthorityHookInputs: function() {
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-who' },
        { field: 'what', selector: '#mkcg-result' },
        { field: 'when', selector: '#mkcg-when' },
        { field: 'how', selector: '#mkcg-how' }
      ];
      
      let fieldsFound = 0;
      let fieldsUpdated = 0;
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input) {
          fieldsFound++;
          if (this.fields[field]) {
            input.value = this.fields[field];
            fieldsUpdated++;
            console.log(`✅ Updated ${selector} with: "${this.fields[field]}"`);
          }
        }
      });
      
      console.log(`🔄 Authority Hook fields: Found ${fieldsFound}/4, Updated ${fieldsUpdated}`);
    },
    
    /**
     * Update Impact Intro input fields - ROOT FIX: Corrected field selectors
     */
    updateImpactIntroInputs: function() {
      const fieldMappings = [
        { field: 'where', selector: '#mkcg-where' },
        { field: 'why', selector: '#mkcg-why' }
      ];
      
      let fieldsFound = 0;
      let fieldsUpdated = 0;
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input) {
          fieldsFound++;
          if (this.fields[field]) {
            input.value = this.fields[field];
            fieldsUpdated++;
            console.log(`✅ Updated ${selector} with: "${this.fields[field]}"`);
          }
        }
      });
      
      console.log(`🔄 Impact Intro fields: Found ${fieldsFound}/2, Updated ${fieldsUpdated}`);
    },
    
    /**
     * Update additional context fields
     */
    updateContextFields: function() {
      const fieldMappings = [
        { field: 'industry', selector: '#tagline-industry' },
        { field: 'uniqueFactors', selector: '#tagline-unique-factors' },
        { field: 'existingTaglines', selector: '#tagline-existing-taglines' }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input && this.fields[field] !== '') {
          input.value = this.fields[field];
          console.log(`✅ Updated ${selector} with: "${this.fields[field]}"`);
        }
      });
    },
    
    /**
     * Update tagline settings fields
     */
    updateSettingsFields: function() {
      const fieldMappings = [
        { field: 'style', selector: '#tagline-style' },
        { field: 'tone', selector: '#tagline-tone' },
        { field: 'length', selector: '#tagline-length' }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input && this.fields[field] !== '') {
          input.value = this.fields[field];
          console.log(`✅ Updated ${selector} with: "${this.fields[field]}"`);
        }
      });
    },
    
    /**
     * Set up all event listeners - ROOT FIX: Using Guest Intro Generator pattern
     */
    bindEvents: function() {
      // Authority Hook and Impact Intro toggle buttons - same as Guest Intro
      const authorityToggle = document.getElementById('tagline-generator-toggle-authority-builder');
      if (authorityToggle) {
          authorityToggle.addEventListener('click', () => this.toggleBuilder('authority-hook'));
      }

      const impactToggle = document.getElementById('tagline-generator-toggle-impact-builder');
      if (impactToggle) {
          impactToggle.addEventListener('click', () => this.toggleBuilder('impact-intro'));
      }
      
      // Generate and preview buttons
      const generateButton = document.getElementById('tagline-generate-with-ai');
      if (generateButton) {
          generateButton.addEventListener('click', (e) => {
              e.preventDefault();
              this.generateTaglines();
          });
      }
      
      const previewButton = document.getElementById('tagline-preview-data');
      if (previewButton) {
          previewButton.addEventListener('click', (e) => {
              e.preventDefault();
              this.previewData();
          });
      }
      
      // Copy and save selected tagline buttons
      const copySelectedButton = document.getElementById('tagline-copy-selected');
      if (copySelectedButton) {
          copySelectedButton.addEventListener('click', (e) => {
              e.preventDefault();
              this.copySelectedTagline();
          });
      }
      
      const saveSelectedButton = document.getElementById('tagline-save-selected');
      if (saveSelectedButton) {
          saveSelectedButton.addEventListener('click', (e) => {
              e.preventDefault();
              this.saveSelectedTagline();
          });
      }
      
      // Document-level delegation for dynamically created tagline options
      document.addEventListener('click', (e) => {
        if (e.target.closest('.tagline-generator__option-copy')) {
          e.preventDefault();
          this.copyTaglineToClipboard(e.target.closest('.tagline-generator__option-copy'));
        } else if (e.target.closest('.tagline-generator__option-select')) {
          e.preventDefault();
          this.selectTagline(e.target.closest('.tagline-generator__option-select'));
        }
      });
      
      // Authority Hook input events with debounced input handling
      this.bindServiceInputs('authority');
      
      // Impact Intro input events with debounced input handling
      this.bindServiceInputs('impact');
      
      // Additional context field events
      this.bindContextInputs();
      
      // Settings field events
      this.bindSettingsInputs();
      
      // Tagline option selection events
      this.bindTaglineOptionEvents();
    },
    
    /**
     * Debounce function for performance optimization
     */
    // Optimized debounce function with immediate option and proper this binding
    debounce: function(func, wait, immediate = false) {
    let timeout;
    return function executedFunction(...args) {
    const context = this;
    const later = () => {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
      const callNow = immediate && !timeout;
        clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  },
    
    /**
     * Bind service input events (authority hook or impact intro)
     */
    bindServiceInputs: function(serviceType) {
      const containerSelector = serviceType === 'authority' ? 
        '#tagline-generator-authority-hook-builder' : 
        '#tagline-generator-impact-intro-builder';
      
      const container = document.querySelector(containerSelector);
      if (!container) return;
      
      // Define field maps for lookup
      const fieldMap = serviceType === 'authority' ? 
        {
          'mkcg-who': 'who',
          'mkcg-result': 'what',
          'mkcg-when': 'when',
          'mkcg-how': 'how'
        } : 
        {
          'mkcg-where': 'where',
          'mkcg-why': 'why'
        };
      
      // Create debounced update function
      const debouncedUpdate = this.debounce(() => {
        if (serviceType === 'authority') {
          this.updateAuthorityHook();
        } else if (serviceType === 'impact') {
          this.updateImpactIntro();
        }
      }, 300); // 300ms debounce
      
      // Use event delegation for input events
      container.addEventListener('input', (e) => {
        const input = e.target;
        const field = fieldMap[input.id];
        
        if (field) {
          this.fields[field] = input.value;
          debouncedUpdate();
        }
      });
    },
    
    /**
     * Bind additional context input events
     */
    bindContextInputs: function() {
      const contextContainer = document.querySelector('.tagline-generator__additional-context');
      if (!contextContainer) return;
      
      const fieldMap = {
        'tagline-industry': 'industry',
        'tagline-unique-factors': 'uniqueFactors',
        'tagline-existing-taglines': 'existingTaglines'
      };
      
      contextContainer.addEventListener('input', (e) => {
        const field = fieldMap[e.target.id];
        if (field) {
          this.fields[field] = e.target.value;
        }
      });
    },
    
    /**
     * Bind tagline settings input events
     */
    bindSettingsInputs: function() {
      const settingsContainer = document.querySelector('.tagline-generator__settings');
      if (!settingsContainer) return;
      
      const fieldMap = {
        'tagline-style': 'style',
        'tagline-tone': 'tone',
        'tagline-length': 'length'
      };
      
      settingsContainer.addEventListener('change', (e) => {
        const field = fieldMap[e.target.id];
        if (field) {
          this.fields[field] = e.target.value;
          console.log(`🔄 Updated tagline setting: ${field} = ${e.target.value}`);
        }
      });
    },
    
    /**
     * Bind tagline option click events for selection
     */
    bindTaglineOptionEvents: function() {
      const optionsContainer = document.querySelector('#tagline-generator-options-grid');
      if (!optionsContainer) return;
      
      // Use event delegation for dynamically created tagline options
      optionsContainer.addEventListener('click', (e) => {
        const optionCard = e.target.closest('.tagline-generator__option');
        if (optionCard && !e.target.closest('button')) {
          // Select tagline when clicking the card (but not buttons within it)
          this.selectTaglineFromCard(optionCard);
        }
      });
    },
    
    /**
     * Update display - Following Topics Generator pattern
     */
    updateDisplay: function() {
      this.updateAuthorityHook();
      this.updateImpactIntro();
    },
    
    /**
     * Toggle builder visibility - ROOT FIX: Following Guest Intro Generator pattern
     */
    toggleBuilder: function(builderType) {
        const builderId = builderType === 'authority-hook' 
            ? 'tagline-generator-authority-hook-builder'
            : 'tagline-generator-impact-intro-builder';
        
        const builder = document.getElementById(builderId);
        if (!builder) return;
        
        const isHidden = builder.classList.contains('generator__builder--hidden');
        
        if (isHidden) {
            // Show builder
            builder.classList.remove('generator__builder--hidden');
            
            // Update button text
            const buttonId = builderType === 'authority-hook'
                ? 'tagline-generator-toggle-authority-builder'
                : 'tagline-generator-toggle-impact-builder';
            
            const button = document.getElementById(buttonId);
            if (button) {
                button.textContent = builderType === 'authority-hook' ? 'Hide Components' : 'Hide Impact Intro';
            }
            
            // Populate fields with existing data
            setTimeout(() => {
                this.populateBuilderFields(builderType);
            }, 100);
            
            // Scroll to builder
            builder.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            // Hide builder
            builder.classList.add('generator__builder--hidden');
            
            // Reset button text
            const buttonId = builderType === 'authority-hook'
                ? 'tagline-generator-toggle-authority-builder'
                : 'tagline-generator-toggle-impact-builder';
            
            const button = document.getElementById(buttonId);
            if (button) {
                button.textContent = builderType === 'authority-hook' ? 'Edit Components' : 'Edit Impact Intro';
            }
        }
    },
    
    /**
     * Populate Authority Hook fields when visible
     */
    populateAuthorityHookFields: function() {
      console.log('🔧 Populating Authority Hook fields...');
      
      if (!window.MKCG_Tagline_Data || !window.MKCG_Tagline_Data.authorityHook) {
        console.log('⚠️ No authority hook data available for population');
        return;
      }
      
      const data = window.MKCG_Tagline_Data.authorityHook;
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-who' },
        { field: 'what', selector: '#mkcg-result' },
        { field: 'when', selector: '#mkcg-when' },
        { field: 'how', selector: '#mkcg-how' }
      ];
      
      let populatedCount = 0;
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input && data[field] && data[field].trim()) {
          if (!input.value || input.value.trim() === '') {
            input.value = data[field];
            this.fields[field] = data[field];
            input.dispatchEvent(new Event('input', { bubbles: true }));
            populatedCount++;
            console.log(`✅ Populated ${selector} with: "${data[field]}"`);
          }
        }
      });
      
      if (populatedCount > 0) {
        console.log(`🎉 Auto-populated ${populatedCount} authority hook fields!`);
        this.updateAuthorityHook();
      }
    },
    
    /**
     * Populate Impact Intro fields when visible
     */
    populateImpactIntroFields: function() {
      console.log('🔧 Populating Impact Intro fields...');
      
      if (!window.MKCG_Tagline_Data || !window.MKCG_Tagline_Data.impactIntro) {
        console.log('⚠️ No impact intro data available for population');
        return;
      }
      
      const data = window.MKCG_Tagline_Data.impactIntro;
      const fieldMappings = [
        { field: 'where', selector: '#mkcg-where' },
        { field: 'why', selector: '#mkcg-why' }
      ];
      
      let populatedCount = 0;
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input && data[field] && data[field].trim()) {
          if (!input.value || input.value.trim() === '') {
            input.value = data[field];
            this.fields[field] = data[field];
            input.dispatchEvent(new Event('input', { bubbles: true }));
            populatedCount++;
            console.log(`✅ Populated ${selector} with: "${data[field]}"`);
          }
        }
      });
      
      if (populatedCount > 0) {
        console.log(`🎉 Auto-populated ${populatedCount} impact intro fields!`);
        this.updateImpactIntro();
      }
    },
    
    /**
     * Update Authority Hook display - no defaults ever
     */
    // Optimized updateAuthorityHook with improved performance
    updateAuthorityHook: function() {
    // Cache field values to avoid repeated property access
    const who = this.fields.who || '';
    const what = this.fields.what || '';
    const when = this.fields.when || '';
    const how = this.fields.how || '';
    
    // Use cached values for the check
    const hasAllFields = who.trim() && what.trim() && when.trim() && how.trim();
    
    // Only build the string if all fields are present
    const hookText = hasAllFields ? `I help ${who} ${what} when ${when} ${how}.` : '';
    
    // Use document fragment for multiple DOM operations
    const updateDOM = () => {
      // Update display element
      const displayElement = document.querySelector('#tagline-generator-authority-hook-text');
      if (displayElement) {
      displayElement.textContent = hookText;
      }
      
      // Update hidden field for backend processing
      const hiddenField = document.querySelector('#mkcg-authority-hook');
    if (hiddenField) {
    hiddenField.value = hookText;
    }
    };
    
    // Use requestAnimationFrame for DOM updates to avoid layout thrashing
    window.requestAnimationFrame(updateDOM);
    
    // Trigger cross-generator communication
    if (window.AppEvents) {
    window.AppEvents.trigger('authority-hook:updated', {
        text: hookText,
        components: {
          who, what, when, how
        },
        timestamp: Date.now(),
        isEmpty: hookText === ''
      });
    }
    },
    
    /**
     * Update Impact Intro display - no defaults ever
     */
    updateImpactIntro: function() {
      const hasAllFields = this.fields.where && this.fields.why &&
                          this.fields.where.trim() && this.fields.why.trim();
      
      let introText = '';
      
      if (hasAllFields) {
        introText = `${this.fields.where}. ${this.fields.why}`;
      }
      
      const displayElement = document.querySelector('#tagline-generator-impact-intro-text');
      if (displayElement) {
        displayElement.textContent = introText;
      }
      
      // Update hidden field for backend processing
      const hiddenField = document.querySelector('#mkcg-impact-intro');
      if (hiddenField) {
        hiddenField.value = introText;
      }
      
      // Trigger cross-generator communication
      if (window.AppEvents) {
        window.AppEvents.trigger('impact-intro:updated', {
          text: introText,
          components: {
            where: this.fields.where,
            why: this.fields.why
          },
          timestamp: Date.now(),
          isEmpty: introText === ''
        });
      }
    },
    
    /**
     * Preview data that will be used for generation
     */
    previewData: function() {
      const formData = this.collectFormData();
      
      let preview = 'Tagline Generation Preview:\n\n';
      
      // Authority Hook
      preview += `Authority Hook: ${formData.authorityHook || 'Not provided'}\n\n`;
      
      // Impact Intro
      preview += `Impact Intro: ${formData.impactIntro || 'Not provided'}\n\n`;
      
      // Additional Context
      preview += 'Additional Context:\n';
      preview += `  Industry: ${formData.industry || 'Not provided'}\n`;
      preview += `  Unique Factors: ${formData.uniqueFactors || 'Not provided'}\n`;
      preview += `  Existing Taglines: ${formData.existingTaglines || 'Not provided'}\n\n`;
      
      // Settings
      preview += 'Tagline Settings:\n';
      preview += `  Style: ${this.getStyleLabel(formData.style)}\n`;
      preview += `  Tone: ${this.getToneLabel(formData.tone)}\n`;
      preview += `  Length: ${this.getLengthLabel(formData.length)}\n\n`;
      
      // Generation Info
      preview += 'Generation Info:\n';
      preview += `  Will generate: 10 diverse tagline options\n`;
      preview += `  Based on: Authority Hook + Impact Intro + Context`;
      
      alert(preview);
    },
    
    /**
     * Get human-readable labels for form values
     */
    getStyleLabel: function(style) {
      const labels = {
        'problem-focused': 'Problem-Focused',
        'solution-focused': 'Solution-Focused',
        'outcome-focused': 'Outcome-Focused',
        'authority-focused': 'Authority-Focused'
      };
      return labels[style] || style;
    },
    
    getToneLabel: function(tone) {
      const labels = {
        'professional': 'Professional',
        'conversational': 'Conversational',
        'bold': 'Bold',
        'friendly': 'Friendly'
      };
      return labels[tone] || tone;
    },
    
    getLengthLabel: function(length) {
      const labels = {
        'short': 'Short (2-4 words)',
        'medium': 'Medium (5-8 words)',
        'long': 'Long (9-12 words)'
      };
      return labels[length] || length;
    },
    
    /**
     * Collect all form data for generation
     */
    collectFormData: function() {
      // Build complete authority hook
      const hasAllAuthorityFields = this.fields.who && this.fields.what && this.fields.when && this.fields.how &&
                                   this.fields.who.trim() && this.fields.what.trim() && 
                                   this.fields.when.trim() && this.fields.how.trim();
      
      const authorityHook = hasAllAuthorityFields ? 
        `I help ${this.fields.who} ${this.fields.what} when ${this.fields.when} ${this.fields.how}.` : '';
      
      // Build complete impact intro
      const hasAllImpactFields = this.fields.where && this.fields.why &&
                                this.fields.where.trim() && this.fields.why.trim();
      
      const impactIntro = hasAllImpactFields ?
        `${this.fields.where}. ${this.fields.why}` : '';
      
      return {
        // Metadata
        post_id: this.metadata.postId,
        nonce: this.metadata.nonce,
        
        // Service components
        authorityHook: authorityHook,
        impactIntro: impactIntro,
        
        // Authority Hook components
        authority_who: this.fields.who,
        authority_what: this.fields.what,
        authority_when: this.fields.when,
        authority_how: this.fields.how,
        
        // Impact Intro components
        impact_where: this.fields.where,
        impact_why: this.fields.why,
        
        // Additional context
        industry: this.fields.industry,
        uniqueFactors: this.fields.uniqueFactors,
        existingTaglines: this.fields.existingTaglines,
        
        // Tagline settings
        style: this.fields.style,
        tone: this.fields.tone,
        length: this.fields.length
      };
    },
    
    /**
     * Validate form data before generation
     */
    validateFormData: function(formData) {
      // Require at least one service component for context
      if (!formData.authorityHook && !formData.impactIntro) {
        return {
          valid: false,
          message: 'Please complete either an Authority Hook or Impact Intro to provide context for tagline generation.'
        };
      }
      
      // Warn if no additional context provided
      if (!formData.industry && !formData.uniqueFactors && !formData.existingTaglines) {
        return {
          valid: true,
          warning: 'Consider adding industry or unique factors for more targeted taglines.'
        };
      }
      
      return { valid: true };
    },
    
    /**
     * Generate taglines using unified AJAX patterns - Multi-option approach
     */
    // Enhanced generateTaglines with better error handling and performance
    generateTaglines: function() {
    try {
      const formData = this.collectFormData();
      
      // Validate form data
    const validation = this.validateFormData(formData);
    if (!validation.valid) {
        this.showNotification(validation.message, 'warning');
        return;
      }
      
    // Show warning if applicable
      if (validation.warning) {
        this.showNotification(validation.warning, 'info');
      }
      
      console.log('🔄 Starting tagline generation...');
      
      this.showLoading();
      
    // Check for network connectivity first
    if (!navigator.onLine) {
      this.handleOfflineGeneration(formData);
      return;
      }
      
      // Use global AJAX system
    if (!window.makeAjaxRequest) {
    console.error('❌ Global makeAjaxRequest not available');
    this.handleAjaxError(new Error('AJAX service not available'), formData);
    return;
    }
    
    // Add timeout for better user experience
    const timeoutPromise = new Promise((_, reject) => {
    setTimeout(() => reject(new Error('Request timed out')), 30000);
    });
    
    // Track generation for analytics
    const startTime = performance.now();
    
    // Race the AJAX request against a timeout
    Promise.race([
    window.makeAjaxRequest('mkcg_generate_taglines', formData),
    timeoutPromise
    ])
    .then(data => {
      const generationTime = Math.round(performance.now() - startTime);
        console.log(`✅ Taglines generated in ${generationTime}ms`);
        
      this.hideLoading();
      if (data.taglines && data.taglines.length > 0) {
        // Store generated taglines
        this.taglines = data.taglines;
        
        // Display tagline options
      this.displayTaglineOptions(data.taglines);
        this.showNotification(`Generated ${data.taglines.length} tagline options in ${Math.round(generationTime/1000)} seconds!`, 'success');
      
        // Cache results for faster recovery
        this.cacheGenerationResults(formData, data.taglines);
        
        // Trigger cross-generator communication
        if (window.AppEvents) {
          window.AppEvents.trigger('taglines:generated', {
              taglines: data.taglines,
                timestamp: Date.now(),
                generationTime
              });
            }
          } else {
            this.handleEmptyResponse(formData);
          }
        })
        .catch(error => {
          this.handleAjaxError(error, formData);
        });
    } catch (unexpectedError) {
      // Catch any unexpected errors in the generation process
      console.error('❌ Unexpected error during generation:', unexpectedError);
      this.hideLoading();
      this.showNotification('An unexpected error occurred. Please try again.', 'error');
      this.generateDemoTaglines(this.collectFormData());
    }
  },
  
  // New method to handle offline generation
  handleOfflineGeneration: function(formData) {
    console.log('📴 Offline mode detected - using demo taglines');
    this.hideLoading();
    this.showNotification('You appear to be offline. Using demo taglines instead.', 'warning');
    this.generateDemoTaglines(formData);
  },
  
  // New method to handle empty API responses
  handleEmptyResponse: function(formData) {
    console.log('⚠️ API returned empty results - using demo taglines');
    this.generateDemoTaglines(formData);
    this.showNotification('Using demo taglines - AI temporarily unavailable', 'info');
  },
  
  // New method to handle AJAX errors
  handleAjaxError: function(error, formData) {
    this.hideLoading();
    console.error('❌ Tagline generation failed:', error);
    
    // Show helpful error message
    let errorMessage = 'Tagline generation failed';
    if (typeof error === 'string') {
      errorMessage = error;
    } else if (error && error.message) {
      errorMessage = error.message;
    }
    
    this.showNotification(`❌ ${errorMessage}`, 'error');
    
    // Try to load from cache first
    if (this.loadCachedResults(formData)) {
      this.showNotification('Showing previously generated taglines from cache', 'info');
    } else {
      // Fallback to demo taglines if no cache
      this.generateDemoTaglines(formData);
    }
  },
  
  // New method to cache generation results
  cacheGenerationResults: function(formData, taglines) {
    try {
      // Don't use localStorage - just in-memory cache
      this.cachedResults = {
        formData: JSON.stringify(formData),
        taglines: taglines,
        timestamp: Date.now()
      };
    } catch (e) {
      console.warn('Failed to cache results:', e);
    }
  },
  
  // New method to load cached results
  loadCachedResults: function(formData) {
    try {
      if (!this.cachedResults) return false;
      
      // Check if cache is for similar input
      const cachedFormData = JSON.parse(this.cachedResults.formData);
      const keyFields = ['authorityHook', 'impactIntro', 'industry', 'style', 'tone', 'length'];
      
      const isSimilarInput = keyFields.every(key => 
        formData[key] === cachedFormData[key] || 
        (formData[key] && cachedFormData[key] && 
         formData[key].toString() === cachedFormData[key].toString())
      );
      
      // Use cache if input is similar and cache is less than 1 hour old
      const cacheAge = Date.now() - this.cachedResults.timestamp;
      const isCacheFresh = cacheAge < 3600000; // 1 hour
      
      if (isSimilarInput && isCacheFresh) {
        this.taglines = this.cachedResults.taglines;
        this.displayTaglineOptions(this.taglines);
        return true;
      }
      
      return false;
    } catch (e) {
      console.warn('Failed to load cached results:', e);
      return false;
    }
  },
    },
    
    /**
     * Generate demo taglines when AI is unavailable
     */
    generateDemoTaglines: function(formData) {
      console.log('🔄 Generating demo taglines...');
      
      // Create demo taglines based on style preference
      const demoTaglines = this.createDemoTaglinesByStyle(formData.style, formData.tone, formData.length);
      
      this.taglines = demoTaglines;
      this.displayTaglineOptions(demoTaglines);
    },
    
    /**
     * Create demo taglines based on style, tone, and length preferences
     */
    createDemoTaglinesByStyle: function(style, tone, length) {
      const baseTaglines = {
        'problem-focused': [
          'Ending Business Chaos',
          'Profit Without Overwhelm',
          'Growth Without Burnout',
          'Results Without Stress',
          'Success Without Sacrifice'
        ],
        'solution-focused': [
          'The Growth Framework',
          'Systems That Scale',
          'Strategies That Stick',
          'Methods That Matter',
          'Solutions That Deliver'
        ],
        'outcome-focused': [
          'Million-Dollar Results',
          'Exponential Growth',
          'Breakthrough Performance',
          'Remarkable Outcomes',
          'Extraordinary Success'
        ],
        'authority-focused': [
          'The Growth Expert',
          'Trusted Advisor',
          'Industry Leader',
          'Results Authority',
          'Performance Specialist'
        ]
      };
      
      const selectedBase = baseTaglines[style] || baseTaglines['problem-focused'];
      
      // Generate 10 variations by combining base taglines with modifiers
      const taglines = [];
      const modifiers = ['Pro', 'Specialist', 'Expert', 'Master', 'Advisor'];
      
      // Add base taglines
      selectedBase.forEach((tagline, index) => {
        if (index < 5) {
          taglines.push({
            text: this.adjustTaglineForToneAndLength(tagline, tone, length),
            style: style,
            tone: tone,
            length: length,
            id: `demo_${index + 1}`
          });
        }
      });
      
      // Add modified versions for remaining slots
      for (let i = 5; i < 10; i++) {
        const baseIndex = i % selectedBase.length;
        const modifier = modifiers[i % modifiers.length];
        const baseTagline = selectedBase[baseIndex];
        const modifiedTagline = `${baseTagline} ${modifier}`;
        
        taglines.push({
          text: this.adjustTaglineForToneAndLength(modifiedTagline, tone, length),
          style: style,
          tone: tone,
          length: length,
          id: `demo_${i + 1}`
        });
      }
      
      return taglines;
    },
    
    /**
     * Adjust tagline for tone and length preferences
     */
    adjustTaglineForToneAndLength: function(tagline, tone, length) {
      let adjustedTagline = tagline;
      
      // Tone adjustments
      switch (tone) {
        case 'conversational':
          adjustedTagline = `Your ${adjustedTagline}`;
          break;
        case 'bold':
          adjustedTagline = adjustedTagline.toUpperCase();
          break;
        case 'friendly':
          adjustedTagline = `${adjustedTagline} ✨`;
          break;
        // 'professional' needs no adjustment
      }
      
      // Length adjustments
      switch (length) {
        case 'short':
          // Keep as is or shorten
          adjustedTagline = adjustedTagline.split(' ').slice(0, 3).join(' ');
          break;
        case 'long':
          // Add descriptive words
          if (!adjustedTagline.includes('Expert') && !adjustedTagline.includes('Specialist')) {
            adjustedTagline = `${adjustedTagline} for Modern Businesses`;
          }
          break;
        // 'medium' needs no adjustment
      }
      
      return adjustedTagline;
    },
    
    /**
     * Display tagline options with multi-option selection interface
     */
    // Optimized displayTaglineOptions using document fragment for better performance
    displayTaglineOptions: function(taglines) {
    const resultsContainer = document.querySelector('#tagline-generator-results');
    const optionsGrid = document.querySelector('#tagline-generator-options-grid');
    
    if (!resultsContainer || !optionsGrid) {
    console.error('❌ Results container or options grid not found');
      return;
    }
    
    // Performance measurement
    const startTime = performance.now();
    
    // Show results container
    resultsContainer.classList.remove('generator__results--hidden');
    
    // Use requestAnimationFrame for DOM updates
    window.requestAnimationFrame(() => {
    // Use document fragment for better performance
    const fragment = document.createDocumentFragment();
    
    // Clear existing options
    optionsGrid.innerHTML = '';
    
    // Create HTML for each tagline option
    taglines.forEach((tagline, index) => {
    const optionCard = document.createElement('div');
    optionCard.className = 'tagline-generator__option';
    optionCard.setAttribute('data-tagline-id', tagline.id || `option_${index + 1}`);
    optionCard.setAttribute('data-tagline-text', tagline.text);
    
    // Create content div
    const contentDiv = document.createElement('div');
    contentDiv.className = 'tagline-generator__option-content';
    
    // Create text div
    const textDiv = document.createElement('div');
    textDiv.className = 'tagline-generator__option-text';
    textDiv.textContent = tagline.text;
    
    // Create meta div
      const metaDiv = document.createElement('div');
      metaDiv.className = 'tagline-generator__option-meta';
      
        // Create style span
        const styleSpan = document.createElement('span');
        styleSpan.className = 'tagline-generator__option-style';
        styleSpan.textContent = this.getStyleLabel(tagline.style);
          
        // Create length span
        const lengthSpan = document.createElement('span');
        lengthSpan.className = 'tagline-generator__option-length';
        lengthSpan.textContent = this.getLengthLabel(tagline.length);
        
        // Append style and length spans to meta div
        metaDiv.appendChild(styleSpan);
        metaDiv.appendChild(lengthSpan);
        
        // Append text and meta divs to content div
        contentDiv.appendChild(textDiv);
        contentDiv.appendChild(metaDiv);
        
        // Create actions div
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'tagline-generator__option-actions';
        
        // Create copy button
        const copyButton = document.createElement('button');
        copyButton.className = 'tagline-generator__option-copy';
        copyButton.setAttribute('data-tagline', tagline.text);
        copyButton.innerHTML = `
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
          </svg>
          Copy
        `;
        
        // Create select button
        const selectButton = document.createElement('button');
        selectButton.className = 'tagline-generator__option-select';
        selectButton.setAttribute('data-tagline-id', tagline.id || `option_${index + 1}`);
        selectButton.textContent = 'Select';
        
        // Append buttons to actions div
        actionsDiv.appendChild(copyButton);
        actionsDiv.appendChild(selectButton);
        
        // Append content and actions divs to option card
        optionCard.appendChild(contentDiv);
        optionCard.appendChild(actionsDiv);
        
        // Add to fragment
        fragment.appendChild(optionCard);
      });
      
      // Append fragment to options grid
      optionsGrid.appendChild(fragment);
      
      // Log performance metrics
      console.log(`✅ Rendered ${taglines.length} tagline options in ${Math.round(performance.now() - startTime)}ms`);
      
      // Scroll to results with requestAnimationFrame for smoother scrolling
      window.requestAnimationFrame(() => {
        // Use polyfill for Safari/Edge support
        const scrollIntoViewSmoothly = () => {
          try {
            resultsContainer.scrollIntoView({ behavior: 'smooth' });
          } catch (e) {
            // Fallback for browsers that don't support smooth scrolling
            const targetY = resultsContainer.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
              top: targetY,
              behavior: window.navigator.userAgent.indexOf('Safari') !== -1 && 
                      window.navigator.userAgent.indexOf('Chrome') === -1 ? 'auto' : 'smooth'
            });
          }
        };
        
        scrollIntoViewSmoothly();
      });
    });
  },
    
    /**
     * Handle tagline selection from option card click
     */
    selectTaglineFromCard: function(optionCard) {
      // Remove selected state from all options
      const allOptions = document.querySelectorAll('.tagline-generator__option');
      allOptions.forEach(option => {
        option.classList.remove('tagline-generator__option--selected');
      });
      
      // Add selected state to clicked option
      optionCard.classList.add('tagline-generator__option--selected');
      
      // Get tagline data
      const taglineText = optionCard.getAttribute('data-tagline-text');
      const taglineId = optionCard.getAttribute('data-tagline-id');
      
      // Store selected tagline
      this.selectedTagline = {
        text: taglineText,
        id: taglineId
      };
      
      // Display selected tagline
      this.displaySelectedTagline(this.selectedTagline);
      
      this.showNotification('Tagline selected! You can now copy or save it.', 'success');
    },
    
    /**
     * Handle tagline selection from select button
     */
    selectTagline: function(selectButton) {
      const taglineId = selectButton.getAttribute('data-tagline-id');
      const optionCard = selectButton.closest('.tagline-generator__option');
      
      if (optionCard) {
        this.selectTaglineFromCard(optionCard);
      }
    },
    
    /**
     * Display selected tagline in the selection area
     */
    displaySelectedTagline: function(tagline) {
      const selectedContainer = document.querySelector('#tagline-generator-selected-container');
      const selectedContent = document.querySelector('#tagline-generator-selected-content');
      
      if (!selectedContainer || !selectedContent) {
        console.error('❌ Selected tagline container not found');
        return;
      }
      
      // Show selected container
      selectedContainer.classList.remove('generator__hidden');
      
      // Display selected tagline
      selectedContent.innerHTML = `
        <div class="tagline-generator__selected-tagline">
          <div class="tagline-generator__selected-text">${tagline.text}</div>
          <div class="tagline-generator__selected-meta">
            <span class="tagline-generator__selected-label">Selected Tagline</span>
            <span class="tagline-generator__selected-id">#${tagline.id}</span>
          </div>
        </div>
      `;
    },
    
    /**
     * Copy tagline to clipboard from option button
     */
    /**
   * Copy tagline to clipboard using centralized compatibility utilities
   * for better cross-browser support
   * 
   * @param {HTMLElement} copyButton - The button that was clicked
   */
  copyTaglineToClipboard: function(copyButton) {
    // Get tagline text from data attribute
    const taglineText = copyButton.getAttribute('data-tagline');
    
    if (!taglineText) {
      this.showNotification('No tagline text found to copy', 'error');
      return;
    }
    
    // Store original button content
    const originalContent = copyButton.innerHTML;
    
    // Use feature detection for Clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(taglineText)
        .then(() => {
          this.showCopySuccess(copyButton, originalContent);
          this.showNotification('Tagline copied to clipboard!', 'success');
        })
        .catch(err => {
          console.error('Clipboard API failed:', err);
          this.useFallbackCopy(taglineText, copyButton, originalContent);
        });
    } else {
      // For browsers without Clipboard API
      this.useFallbackCopy(taglineText, copyButton, originalContent);
    }
  },
  
  /**
   * Show success state on copy button
   * 
   * @param {HTMLElement} button - The button element
   * @param {string} originalContent - Original HTML content to restore
   */
  showCopySuccess: function(button, originalContent) {
    // Set success state
    button.textContent = 'Copied!';
    button.classList.add('generator__button--success');
    
    // Reset after delay
    setTimeout(() => {
      button.innerHTML = originalContent;
      button.classList.remove('generator__button--success');
    }, 2000);
  },
  
  /**
   * Use fallback clipboard copy method for browsers without Clipboard API
   * 
   * @param {string} text - Text to copy
   * @param {HTMLElement} button - The button element
   * @param {string} originalContent - Original HTML content to restore
   */
  useFallbackCopy: function(text, button, originalContent) {
    try {
      // Create temporary textarea for copy operation
      const textarea = document.createElement('textarea');
      textarea.value = text;
      
      // Make it invisible but accessible
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      textarea.style.pointerEvents = 'none';
      document.body.appendChild(textarea);
      
      // Select the text
      textarea.focus();
      textarea.select();
      
      // Execute copy command
      const successful = document.execCommand('copy');
      
      // Remove temporary element
      document.body.removeChild(textarea);
      
      if (successful) {
        this.showCopySuccess(button, originalContent);
        this.showNotification('Tagline copied to clipboard!', 'success');
      } else {
        this.showCopyError(button, originalContent);
        this.showNotification('Unable to copy to clipboard', 'error');
      }
    } catch (err) {
      console.error('Fallback clipboard method failed:', err);
      this.showCopyError(button, originalContent);
      this.showNotification('Failed to copy to clipboard', 'error');
    }
  },
  
  /**
   * Show error state on copy button
   * 
   * @param {HTMLElement} button - The button element
   * @param {string} originalContent - Original HTML content to restore
   */
  showCopyError: function(button, originalContent) {
    // Set error state
    button.textContent = 'Failed';
    button.classList.add('generator__button--error');
    
    // Reset after delay
    setTimeout(() => {
      button.innerHTML = originalContent;
      button.classList.remove('generator__button--error');
    }, 2000);
  },
    
    /**
     * Copy selected tagline to clipboard
     */
    copySelectedTagline: function() {
      if (!this.selectedTagline) {
        this.showNotification('Please select a tagline first', 'warning');
        return;
      }
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(this.selectedTagline.text).then(() => {
          this.showNotification('Selected tagline copied to clipboard!', 'success');
        }).catch(err => {
          console.error('Failed to copy:', err);
          this.showNotification('Failed to copy to clipboard', 'error');
        });
      } else {
        this.showNotification('Clipboard not available in this browser', 'warning');
      }
    },
    
    /**
     * Save selected tagline to backend
     */
    saveSelectedTagline: function() {
      if (!this.selectedTagline) {
        this.showNotification('Please select a tagline first', 'warning');
        return;
      }
      
      if (this.metadata.postId === 0) {
        this.showNotification('No post ID found. Please refresh the page.', 'error');
        return;
      }
      
      const saveData = {
        post_id: this.metadata.postId,
        nonce: this.metadata.nonce,
        selected_tagline: this.selectedTagline.text,
        tagline_id: this.selectedTagline.id,
        generated_taglines: this.taglines,
        generation_settings: {
          style: this.fields.style,
          tone: this.fields.tone,
          length: this.fields.length
        }
      };
      
      console.log('🔄 Saving selected tagline...', saveData);
      
      this.showLoading();
      
      window.makeAjaxRequest('mkcg_save_tagline', saveData)
        .then(data => {
          this.hideLoading();
          this.showNotification('✅ Tagline saved successfully!', 'success');
          console.log('✅ Save successful:', data);
          
          // Trigger cross-generator communication
          if (window.AppEvents) {
            window.AppEvents.trigger('tagline:saved', {
              selectedTagline: this.selectedTagline,
              allTaglines: this.taglines,
              timestamp: Date.now()
            });
          }
        })
        .catch(error => {
          this.hideLoading();
          console.error('❌ Save failed:', error);
          
          let errorMessage = 'Save operation failed';
          if (typeof error === 'string') {
            errorMessage = error;
          } else if (error && error.message) {
            errorMessage = error.message;
          }
          
          this.showNotification(`❌ ${errorMessage}`, 'error');
        });
    },
    
    /**
     * Utility functions - Following Topics Generator pattern
     */
    showNotification: function(message, type = 'info') {
      if (window.showNotification) {
        window.showNotification(message, type);
      } else {
        console.log(`${type.toUpperCase()}: ${message}`);
      }
    },
    
    showLoading: function() {
      const loading = document.querySelector('#tagline-generator-loading');
      if (loading) {
        loading.classList.remove('generator__loading--hidden');
      }
    },
    
    hideLoading: function() {
      const loading = document.querySelector('#tagline-generator-loading');
      if (loading) {
        loading.classList.add('generator__loading--hidden');
      }
    }
  };

  // Initialize when DOM is ready - Following Topics Generator pattern
  document.addEventListener('DOMContentLoaded', function() {
    const taglineContainer = document.querySelector('.tagline-generator');
    if (!taglineContainer) {
      console.log('🎯 Tagline Generator: DOM elements not found - skipping initialization');
      return;
    }
    
    console.log('🎯 Tagline Generator: DOM Ready - Starting initialization');
    TaglineGenerator.init();
  });

  // Make globally available
  window.TaglineGenerator = TaglineGenerator;
  
  // Debug functions for testing
  window.MKCG_Tagline_Test = {
    showData: function() {
      console.log('🔍 Tagline Generator current state:');
      console.log('Fields:', TaglineGenerator.fields);
      console.log('Taglines:', TaglineGenerator.taglines);
      console.log('Selected:', TaglineGenerator.selectedTagline);
      console.log('Metadata:', TaglineGenerator.metadata);
    },
    
    testGeneration: function() {
      console.log('🧪 Testing tagline generation...');
      TaglineGenerator.generateTaglines();
    },
    
    testSave: function() {
      console.log('🧪 Testing save operation...');
      TaglineGenerator.saveSelectedTagline();
    },
    
    populateTestData: function() {
      console.log('🧪 Populating test data...');
      
      // Set test values
      TaglineGenerator.fields.who = 'overwhelmed entrepreneurs';
      TaglineGenerator.fields.what = 'scale their business without burning out';
      TaglineGenerator.fields.when = 'they feel stuck at their current level';
      TaglineGenerator.fields.how = 'through proven systems and frameworks';
      TaglineGenerator.fields.where = 'I\'ve helped 500+ businesses achieve sustainable growth';
      TaglineGenerator.fields.why = 'I believe success shouldn\'t come at the cost of your well-being';
      TaglineGenerator.fields.industry = 'Business Consulting';
      TaglineGenerator.fields.uniqueFactors = 'Focus on sustainable growth without burnout';
      
      // Update form fields
      TaglineGenerator.updateInputFields('all');
      TaglineGenerator.updateDisplay();
      
      console.log('✅ Test data populated');
    },
    
    generateDemoOptions: function() {
      console.log('🧪 Generating demo tagline options...');
      const demoTaglines = TaglineGenerator.createDemoTaglinesByStyle('problem-focused', 'professional', 'medium');
      TaglineGenerator.displayTaglineOptions(demoTaglines);
      console.log('✅ Demo options generated');
    }
  };
  
  console.log('✅ Tagline Generator loaded - Following unified patterns');
  console.log('🔧 No jQuery dependencies - Pure vanilla JavaScript');
  console.log('🧪 DEBUG: Use window.MKCG_Tagline_Test.populateTestData() to test');

})();