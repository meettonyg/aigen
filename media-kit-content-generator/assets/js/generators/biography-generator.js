/**
 * Biography Generator JavaScript - Unified Architecture Implementation
 * Following Topics Generator patterns with biography-specific enhancements
 * 
 * Architecture: Vanilla JS, unified service integration, BEM methodology
 * Features: AI generation, multiple versions, tone modification, auto-save
 * 
 * @package Media_Kit_Content_Generator
 * @version 3.0 - Complete rewrite following unified patterns
 */

(function() {
  'use strict';
  
  /**
   * Biography Generator - Following Topics Generator Architecture
   * 3-step initialization: load data, bind events, update display
   */
  const BiographyGenerator = {
    
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
      
      // Biography-specific fields
      name: '',
      title: '',
      organization: '',
      tone: 'professional',
      length: 'medium',
      pov: 'third',
      existingBio: '',
      notes: ''
    },
    
    // Generated biographies storage
    biographies: {
      short: '',
      medium: '',
      long: ''
    },
    
    // Form metadata
    metadata: {
      postId: 0,
      entryId: 0,
      entryKey: '',
      nonce: '',
      hasData: false
    },
    
    /**
     * Initialize Biography Generator - Following Topics Generator pattern
     */
    init: function() {
      console.log('🎯 Biography Generator: Simple initialization starting');
      
      // Step 1: Load existing data
      this.loadExistingData();
      
      // Step 2: Bind form events  
      this.bindEvents();
      
      // Step 3: Update display
      this.updateDisplay();
      
      console.log('✅ Biography Generator: Simple initialization completed');
    },
    
    /**
     * Load data from PHP or defaults - Following Topics Generator pattern
     */
    loadExistingData: function() {
      // Check if PHP passed data
      if (window.MKCG_Biography_Data) {
        // Load metadata
        this.metadata = {
          postId: window.MKCG_Biography_Data.postId || 0,
          entryId: window.MKCG_Biography_Data.entryId || 0,
          entryKey: window.MKCG_Biography_Data.entryKey || '',
          nonce: window.MKCG_Biography_Data.nonce || '',
          hasData: window.MKCG_Biography_Data.hasData || false
        };
        
        // Check if we're in non-entry mode
        if (window.MKCG_Biography_Data.noEntryParam) {
          console.log('📝 No entry parameter - using empty data');
          this.setDefaultData();
        } else if (window.MKCG_Biography_Data.hasData) {
          console.log('📝 Loading data from PHP:', window.MKCG_Biography_Data);
          this.populateFromPHPData(window.MKCG_Biography_Data);
        } else {
          console.log('📝 No data found but entry param exists - using empty data');
          this.setDefaultData();
        }
      } else {
        console.log('📝 MKCG_Biography_Data not available - using empty data');
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
        const authorityBuilder = document.querySelector('#biography-generator-authority-hook-builder');
        if (authorityBuilder && !authorityBuilder.classList.contains('generator__builder--hidden')) {
          console.log('🔧 Authority Hook Builder already visible, attempting population...');
          this.populateAuthorityHookFields();
        }
        
        // Check Impact Intro Builder
        const impactBuilder = document.querySelector('#biography-generator-impact-intro-builder');
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
      
      // Load biography-specific fields
      if (phpData.biography) {
        this.fields.name = phpData.biography.name || '';
        this.fields.title = phpData.biography.title || '';
        this.fields.organization = phpData.biography.organization || '';
        this.fields.tone = phpData.biography.tone || 'professional';
        this.fields.length = phpData.biography.length || 'medium';
        this.fields.pov = phpData.biography.pov || 'third';
        this.fields.existingBio = phpData.biography.existing || '';
        this.fields.notes = phpData.biography.notes || '';
        
        this.updateBiographyFields();
      }
      
      // Load existing biographies if available
      if (phpData.biographies) {
        this.biographies = {
          short: phpData.biographies.short || '',
          medium: phpData.biographies.medium || '',
          long: phpData.biographies.long || ''
        };
        
        // Display results if we have biographies
        if (this.biographies.short || this.biographies.medium || this.biographies.long) {
          this.displayResults({ biographies: this.biographies });
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
      this.fields.tone = 'professional';
      this.fields.length = 'medium';
      this.fields.pov = 'third';
      
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
      
      if (serviceType === 'biography' || serviceType === 'all') {
        this.updateBiographyFields();
      }
    },
    
    /**
     * Update Authority Hook input fields
     */
    updateAuthorityHookInputs: function() {
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-authority-who' },
        { field: 'what', selector: '#mkcg-authority-what' },
        { field: 'when', selector: '#mkcg-authority-when' },
        { field: 'how', selector: '#mkcg-authority-how' }
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
     * Update Impact Intro input fields
     */
    updateImpactIntroInputs: function() {
      const fieldMappings = [
        { field: 'where', selector: '#mkcg-impact-where' },
        { field: 'why', selector: '#mkcg-impact-why' }
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
     * Update biography-specific fields
     */
    updateBiographyFields: function() {
      const fieldMappings = [
        { field: 'name', selector: '#biography-name' },
        { field: 'title', selector: '#biography-title' },
        { field: 'organization', selector: '#biography-organization' },
        { field: 'tone', selector: '#biography-tone' },
        { field: 'length', selector: '#biography-length' },
        { field: 'pov', selector: '#biography-pov' },
        { field: 'existingBio', selector: '#biography-existing' },
        { field: 'notes', selector: '#biography-notes' }
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
     * Bind events - Following Topics Generator pattern with biography enhancements
     */
    bindEvents: function() {
    // PERFORMANCE OPTIMIZATION: Use event delegation for most interactions
    // Define container for event delegation
    const containerLeft = document.querySelector('.generator__panel--left');
    if (containerLeft) {
        // Delegate most button clicks
        containerLeft.addEventListener('click', (e) => {
            const target = e.target.closest('button');
            if (!target) return;
            
            // Handle button clicks based on ID
            if (target.id === 'biography-generator-toggle-authority-builder') {
                e.preventDefault();
                this.toggleBuilder('biography-generator-authority-hook-builder', 'authority');
            } else if (target.id === 'biography-generator-toggle-impact-builder') {
                e.preventDefault();
                this.toggleBuilder('biography-generator-impact-intro-builder', 'impact');
            } else if (target.id === 'biography-generator-generate') {
                e.preventDefault();
                console.log('🔘 Generate biography button clicked');
                this.generateBiography();
            } else if (target.id === 'biography-generator-preview') {
                e.preventDefault();
                this.previewData();
            } else if (target.id === 'biography-generator-save') {
                e.preventDefault();
                console.log('🔘 Save biography button clicked');
                    this.saveAllData();
                }
            });
        }
        
        // Authority Hook input events with debounced input handling
        this.bindServiceInputs('authority');
        
        // Impact Intro input events with debounced input handling
        this.bindServiceInputs('impact');
        
        // Biography field events
        this.bindBiographyInputs();
        
        // Auto-save on blur for biography fields
        this.bindAutoSave();
    },
    
    /**
     * Bind toggle button for service builders
     */
    bindToggleButton: function(buttonId, builderId, serviceType) {
      const toggleBtn = document.querySelector(`#${buttonId}`);
      
      if (toggleBtn) {
        // Remove existing listeners to prevent duplicates
        toggleBtn.removeEventListener('click', this[`toggle${serviceType}Handler`]);
        
        this[`toggle${serviceType}Handler`] = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.toggleBuilder(builderId, serviceType);
        };
        
        toggleBtn.addEventListener('click', this[`toggle${serviceType}Handler`]);
        console.log(`✅ ${serviceType} builder toggle bound correctly`);
      } else {
        console.warn(`⚠️ Toggle button not found: #${buttonId}`);
      }
    },
    
    /**
     * Bind generate biography button
     */
    bindGenerateButton: function() {
      const generateBtn = document.querySelector('#biography-generator-generate');
      
      if (generateBtn) {
        generateBtn.removeEventListener('click', this.generateBiographyHandler);
        
        this.generateBiographyHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Generate biography button clicked');
          this.generateBiography();
        };
        
        generateBtn.addEventListener('click', this.generateBiographyHandler);
        console.log('✅ Generate biography button bound correctly');
      } else {
        console.warn('⚠️ Generate button not found: #biography-generator-generate');
      }
    },
    
    /**
     * Bind preview data button
     */
    bindPreviewButton: function() {
      const previewBtn = document.querySelector('#biography-generator-preview');
      
      if (previewBtn) {
        previewBtn.removeEventListener('click', this.previewDataHandler);
        
        this.previewDataHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.previewData();
        };
        
        previewBtn.addEventListener('click', this.previewDataHandler);
        console.log('✅ Preview button bound correctly');
      }
    },
    
    /**
     * Bind save biography button
     */
    bindSaveButton: function() {
      const saveBtn = document.querySelector('#biography-generator-save');
      
      if (saveBtn) {
        saveBtn.removeEventListener('click', this.saveAllDataHandler);
        
        this.saveAllDataHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Save biography button clicked');
          this.saveAllData();
        };
        
        saveBtn.addEventListener('click', this.saveAllDataHandler);
        console.log('✅ Save biography button bound correctly');
      }
    },
    
    /**
     * Bind service input events (authority hook or impact intro)
     */
    // Debounce function for performance optimization
    debounce: function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
    const later = () => {
    clearTimeout(timeout);
    func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    };
    },
    
    bindServiceInputs: function(serviceType) {
    // Define which container to use for event delegation
    const containerSelector = serviceType === 'authority' ? 
        '#biography-generator-authority-hook-builder' : 
        '#biography-generator-impact-intro-builder';
    
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    // Define field maps for lookup
    const fieldMap = serviceType === 'authority' ? 
    {
    'mkcg-authority-who': 'who',
    'mkcg-authority-what': 'what',
    'mkcg-authority-when': 'when',
        'mkcg-authority-how': 'how'
        } : 
            {
                'mkcg-impact-where': 'where',
                'mkcg-impact-why': 'why'
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
     * Bind biography-specific input events
     */
    bindBiographyInputs: function() {
    // PERFORMANCE OPTIMIZATION: Use event delegation for all biography inputs
    const container = document.querySelector('.generator__panel--left');
    if (!container) return;
    
    // Define field mapping for easy lookup
    const fieldMap = {
    'biography-name': 'name',
    'biography-title': 'title',
    'biography-organization': 'organization',
        'biography-tone': 'tone',
        'biography-length': 'length',
        'biography-pov': 'pov',
    'biography-existing': 'existingBio',
    'biography-notes': 'notes'
    };
    
    // Handle all input events with one listener
    container.addEventListener('input', (e) => {
    const field = fieldMap[e.target.id];
    if (field) {
    this.fields[field] = e.target.value;
    }
    });
    
    // Handle change events for select fields
    container.addEventListener('change', (e) => {
            if (e.target.tagName === 'SELECT') {
                const field = fieldMap[e.target.id];
                if (field) {
                    this.fields[field] = e.target.value;
                }
            }
        });
    },
    
    /**
     * Bind auto-save functionality
     */
    bindAutoSave: function() {
    // PERFORMANCE OPTIMIZATION: Use event delegation for autosave
    const container = document.querySelector('.generator__panel--left');
    if (!container) return;
    
    // Define which fields should auto-save
    const autoSaveFieldIds = [
        'biography-name',
        'biography-title', 
        'biography-organization',
    'biography-existing',
    'biography-notes'
    ];
    
    // Listen for blur events on the container
    container.addEventListener('blur', (e) => {
        // Check if the blurred element is one we want to auto-save
            if (autoSaveFieldIds.includes(e.target.id)) {
                this.autoSaveField(e.target);
            }
        }, true); // Use capture phase to ensure we catch the blur
    },
    
    /**
     * Update display - Following Topics Generator pattern
     */
    updateDisplay: function() {
      this.updateAuthorityHook();
      this.updateImpactIntro();
    },
    
    /**
     * Toggle service builder visibility
     */
    toggleBuilder: function(builderId, serviceType) {
      const builder = document.querySelector(`#${builderId}`);
      if (!builder) {
        console.warn(`⚠️ Builder not found: #${builderId}`);
        return;
      }
      
      const isHidden = builder.classList.contains('generator__builder--hidden');
      
      if (isHidden) {
        builder.classList.remove('generator__builder--hidden');
        console.log(`✅ ${serviceType} Builder shown`);
        
        // Auto-populate fields when builder becomes visible
        setTimeout(() => {
          if (serviceType === 'authority') {
            this.populateAuthorityHookFields();
          } else if (serviceType === 'impact') {
            this.populateImpactIntroFields();
          }
        }, 100);
      } else {
        builder.classList.add('generator__builder--hidden');
        console.log(`✅ ${serviceType} Builder hidden`);
      }
      
      // Trigger centralized service for UX enhancements
      if (window.AuthorityHookBuilder && serviceType === 'authority') {
        window.AuthorityHookBuilder.updateToggleButtonState(
          'biography-generator-toggle-authority-builder',
          'biography-generator-authority-hook-builder'
        );
      }
    },
    
    /**
     * Populate Authority Hook fields when visible
     */
    populateAuthorityHookFields: function() {
      console.log('🔧 Populating Authority Hook fields...');
      
      if (!window.MKCG_Biography_Data || !window.MKCG_Biography_Data.authorityHook) {
        console.log('⚠️ No authority hook data available for population');
        return;
      }
      
      const data = window.MKCG_Biography_Data.authorityHook;
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-authority-who' },
        { field: 'what', selector: '#mkcg-authority-what' },
        { field: 'when', selector: '#mkcg-authority-when' },
        { field: 'how', selector: '#mkcg-authority-how' }
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
      
      if (!window.MKCG_Biography_Data || !window.MKCG_Biography_Data.impactIntro) {
        console.log('⚠️ No impact intro data available for population');
        return;
      }
      
      const data = window.MKCG_Biography_Data.impactIntro;
      const fieldMappings = [
        { field: 'where', selector: '#mkcg-impact-where' },
        { field: 'why', selector: '#mkcg-impact-why' }
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
    updateAuthorityHook: function() {
      const hasAllFields = this.fields.who && this.fields.what && this.fields.when && this.fields.how &&
                          this.fields.who.trim() && this.fields.what.trim() && 
                          this.fields.when.trim() && this.fields.how.trim();
      
      let hookText = '';
      
      if (hasAllFields) {
        hookText = `I help ${this.fields.who} ${this.fields.what} when ${this.fields.when} ${this.fields.how}.`;
      }
      
      const displayElement = document.querySelector('#biography-generator-authority-hook-text');
      if (displayElement) {
        displayElement.textContent = hookText;
      }
      
      // Trigger cross-generator communication
      if (window.AppEvents) {
        window.AppEvents.trigger('authority-hook:updated', {
          text: hookText,
          components: {
            who: this.fields.who,
            what: this.fields.what,
            when: this.fields.when,
            how: this.fields.how
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
      
      const displayElement = document.querySelector('#biography-generator-impact-intro-text');
      if (displayElement) {
        displayElement.textContent = introText;
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
      
      let preview = 'Biography Generation Preview:\\n\\n';
      
      // Authority Hook
      preview += `Authority Hook: ${formData.authorityHook || 'Not provided'}\\n\\n`;
      
      // Impact Intro
      preview += `Impact Intro: ${formData.impactIntro || 'Not provided'}\\n\\n`;
      
      // Personal Information
      preview += 'Personal Information:\\n';
      preview += `  Name: ${formData.name || 'Not provided'}\\n`;
      preview += `  Title: ${formData.title || 'Not provided'}\\n`;
      preview += `  Organization: ${formData.organization || 'Not provided'}\\n\\n`;
      
      // Settings
      preview += 'Biography Settings:\\n';
      preview += `  Tone: ${this.getToneLabel(formData.tone)}\\n`;
      preview += `  Length: ${this.getLengthLabel(formData.length)}\\n`;
      preview += `  Point of View: ${this.getPOVLabel(formData.pov)}\\n\\n`;
      
      // Additional Content
      preview += 'Additional Content:\\n';
      preview += `  Existing Biography: ${formData.existingBio ? 'Provided' : 'Not provided'}\\n`;
      preview += `  Additional Notes: ${formData.notes ? 'Provided' : 'Not provided'}`;
      
      alert(preview);
    },
    
    /**
     * Get human-readable labels for form values
     */
    getToneLabel: function(tone) {
      const labels = {
        'professional': 'Professional',
        'conversational': 'Conversational', 
        'authoritative': 'Authoritative',
        'friendly': 'Friendly'
      };
      return labels[tone] || tone;
    },
    
    getLengthLabel: function(length) {
      const labels = {
        'short': 'Short (50-75 words)',
        'medium': 'Medium (100-150 words)',
        'long': 'Long (200-300 words)'
      };
      return labels[length] || length;
    },
    
    getPOVLabel: function(pov) {
      const labels = {
        'first': 'First Person (I/My)',
        'third': 'Third Person (He/She/They)'
      };
      return labels[pov] || pov;
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
        entry_id: this.metadata.entryId,
        entry_key: this.metadata.entryKey,
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
        
        // Biography fields
        name: this.fields.name,
        title: this.fields.title,
        organization: this.fields.organization,
        tone: this.fields.tone,
        length: this.fields.length,
        pov: this.fields.pov,
        existingBio: this.fields.existingBio,
        notes: this.fields.notes
      };
    },
    
    /**
     * Validate form data before generation
     */
    validateFormData: function(formData) {
      // Required personal fields
      if (!formData.name || !formData.name.trim()) {
        return {
          valid: false,
          message: 'Please enter your full name.'
        };
      }
      
      if (!formData.title || !formData.title.trim()) {
        return {
          valid: false,
          message: 'Please enter your professional title.'
        };
      }
      
      // Require at least one service component
      if (!formData.authorityHook && !formData.impactIntro) {
        return {
          valid: false,
          message: 'Please complete either an Authority Hook or Impact Intro before generating your biography.'
        };
      }
      
      return { valid: true };
    },
    
    /**
     * Generate biography using unified AJAX patterns
     */
    generateBiography: function() {
      const formData = this.collectFormData();
      
      // Validate form data
      const validation = this.validateFormData(formData);
      if (!validation.valid) {
        this.showNotification(validation.message, 'warning');
        return;
      }
      
      console.log('🔄 Starting biography generation...', formData);
      
      this.showLoading();
      
      // Use global AJAX system
      if (!window.makeAjaxRequest) {
        console.error('❌ Global makeAjaxRequest not available');
        this.hideLoading();
        this.showNotification('System error: AJAX service not available', 'error');
        return;
      }
      
      window.makeAjaxRequest('mkcg_generate_biography', formData)
        .then(data => {
          this.hideLoading();
          if (data.biographies) {
            // Store generated biographies
            this.biographies = data.biographies;
            
            // Display results
            this.displayResults(data);
            this.showNotification('Biographies generated successfully!', 'success');
            
            // Trigger cross-generator communication
            if (window.AppEvents) {
              window.AppEvents.trigger('biography:generated', {
                biographies: data.biographies,
                timestamp: Date.now()
              });
            }
          } else {
            this.generateDemoBiographies(formData);
            this.showNotification('Using demo biographies - AI temporarily unavailable', 'info');
          }
        })
        .catch(error => {
          this.hideLoading();
          console.error('❌ Biography generation failed:', error);
          
          // Show helpful error message
          let errorMessage = 'Biography generation failed';
          if (typeof error === 'string') {
            errorMessage = error;
          } else if (error && error.message) {
            errorMessage = error.message;
          }
          
          this.showNotification(`❌ ${errorMessage}`, 'error');
          
          // Try to generate demo biographies as fallback
          this.generateDemoBiographies(formData);
        });
    },
    
    /**
     * Generate demo biographies when AI is unavailable
     */
    generateDemoBiographies: function(formData) {
      console.log('🔄 Generating demo biographies...');
      
      const name = formData.name || 'Professional Name';
      const title = formData.title || 'Expert Title';
      const organization = formData.organization || 'Leading Organization';
      
      // Create demo biographies based on provided information
      const demoBiographies = {
        short: `${name} is a ${title}${organization ? ` at ${organization}` : ''}. ${formData.authorityHook || 'A recognized expert in their field.'} Known for delivering exceptional results and innovative solutions.`,
        
        medium: `${name} is a ${title}${organization ? ` at ${organization}` : ''} with extensive experience in their field. ${formData.authorityHook || 'They help clients achieve remarkable results through proven methodologies.'} ${formData.impactIntro || 'With a track record of success, they are committed to delivering exceptional value to every client.'} ${name} is recognized for their expertise and dedication to excellence.`,
        
        long: `${name} is a distinguished ${title}${organization ? ` at ${organization}` : ''} with a proven track record of success in their field. ${formData.authorityHook || 'They specialize in helping clients achieve remarkable results through innovative approaches and proven methodologies.'} \\n\\n${formData.impactIntro || 'With years of experience and a commitment to excellence, they have built a reputation for delivering exceptional value to clients and organizations.'} \\n\\n${name} is passionate about sharing their expertise and helping others succeed. They are frequently sought after for speaking engagements and consulting opportunities, and their insights have been featured in leading industry publications. When not working with clients, ${name} enjoys staying current with industry trends and continuously expanding their knowledge and skills.`
      };
      
      this.biographies = demoBiographies;
      this.displayResults({ biographies: demoBiographies });
    },
    
    /**
     * Display biography results with tabs and copy functionality
     */
    displayResults: function(data) {
      const resultsContainer = document.querySelector('#biography-generator-results');
      const resultsContent = document.querySelector('#biography-generator-results-content');
      
      if (!resultsContainer || !resultsContent) {
        console.error('❌ Results container not found');
        return;
      }
      
      // Show results container
      resultsContainer.classList.remove('generator__results--hidden');
      
      // Create HTML for results with tabs
      const biographiesHtml = `
        <div class="biography-generator__tabs">
          <button class="biography-generator__tab biography-generator__tab--active" data-tab="short">
            Short <span class="biography-generator__length-indicator">(50-75 words)</span>
          </button>
          <button class="biography-generator__tab" data-tab="medium">
            Medium <span class="biography-generator__length-indicator">(100-150 words)</span>
          </button>
          <button class="biography-generator__tab" data-tab="long">
            Long <span class="biography-generator__length-indicator">(200-300 words)</span>
          </button>
        </div>
        
        <div class="biography-generator__content">
          <div class="biography-generator__result-item biography-generator__result-item--active" id="biography-tab-short">
            <div class="biography-generator__result-header">
              <h4 class="biography-generator__result-title">Short Biography</h4>
              <span class="biography-generator__result-badge">Social Media & Brief Intros</span>
            </div>
            <div class="biography-generator__result-content">
              ${data.biographies.short.replace(/\\n/g, '<br>')}
            </div>
            <div class="biography-generator__result-actions">
              <button class="generator__button generator__button--outline biography-generator__copy-button" data-version="short">
                Copy to Clipboard
              </button>
            </div>
          </div>
          
          <div class="biography-generator__result-item" id="biography-tab-medium">
            <div class="biography-generator__result-header">
              <h4 class="biography-generator__result-title">Medium Biography</h4>
              <span class="biography-generator__result-badge">Websites & Speaker Intros</span>
            </div>
            <div class="biography-generator__result-content">
              ${data.biographies.medium.replace(/\\n/g, '<br>')}
            </div>
            <div class="biography-generator__result-actions">
              <button class="generator__button generator__button--outline biography-generator__copy-button" data-version="medium">
                Copy to Clipboard
              </button>
            </div>
          </div>
          
          <div class="biography-generator__result-item" id="biography-tab-long">
            <div class="biography-generator__result-header">
              <h4 class="biography-generator__result-title">Long Biography</h4>
              <span class="biography-generator__result-badge">Detailed Marketing Materials</span>
            </div>
            <div class="biography-generator__result-content">
              ${data.biographies.long.replace(/\\n/g, '<br>')}
            </div>
            <div class="biography-generator__result-actions">
              <button class="generator__button generator__button--outline biography-generator__copy-button" data-version="long">
                Copy to Clipboard
              </button>
            </div>
          </div>
        </div>
        
        <div class="biography-generator__global-actions">
          <button class="generator__button generator__button--secondary" onclick="window.location.href='?generator=biography&post_id=${this.metadata.postId}&entry=${this.metadata.entryId}&results=true'">
            View Full Results Page
          </button>
          <button class="generator__button generator__button--primary biography-generator__save-all" data-action="save-all">
            Save All Biographies
          </button>
        </div>
      `;
      
      resultsContent.innerHTML = biographiesHtml;
      
      // Bind tab switching
      this.bindResultsTabs();
      
      // Bind copy buttons
      this.bindCopyButtons();
      
      // Bind save all button
      this.bindSaveAllButton();
      
      // Scroll to results
      resultsContainer.scrollIntoView({ behavior: 'smooth' });
    },
    
    /**
     * Bind results tab switching
     */
    bindResultsTabs: function() {
      const tabs = document.querySelectorAll('.biography-generator__tab');
      const contents = document.querySelectorAll('.biography-generator__result-item');
      
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          const version = tab.getAttribute('data-tab');
          
          // Update active tab
          tabs.forEach(t => t.classList.remove('biography-generator__tab--active'));
          tab.classList.add('biography-generator__tab--active');
          
          // Update active content
          contents.forEach(c => c.classList.remove('biography-generator__result-item--active'));
          const activeContent = document.querySelector(`#biography-tab-${version}`);
          if (activeContent) {
            activeContent.classList.add('biography-generator__result-item--active');
          }
        });
      });
    },
    
    /**
     * Bind copy to clipboard buttons
     */
    bindCopyButtons: function() {
      const copyButtons = document.querySelectorAll('.biography-generator__copy-button');
      
      copyButtons.forEach(button => {
        button.addEventListener('click', () => {
          const version = button.getAttribute('data-version');
          const biography = this.biographies[version];
          
          if (navigator.clipboard) {
            navigator.clipboard.writeText(biography).then(() => {
              // Show success feedback
              const originalText = button.textContent;
              button.textContent = 'Copied!';
              button.classList.add('generator__button--success');
              
              setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('generator__button--success');
              }, 2000);
              
              this.showNotification(`${version} biography copied to clipboard!`, 'success');
            }).catch(err => {
              console.error('Failed to copy:', err);
              this.showNotification('Failed to copy to clipboard', 'error');
            });
          } else {
            this.showNotification('Clipboard not available in this browser', 'warning');
          }
        });
      });
    },
    
    /**
     * Bind save all biographies button
     */
    bindSaveAllButton: function() {
      const saveAllBtn = document.querySelector('.biography-generator__save-all');
      
      if (saveAllBtn) {
        saveAllBtn.addEventListener('click', () => {
          this.saveAllData();
        });
      }
    },
    
    /**
     * Auto-save individual field
     */
    autoSaveField: function(inputElement) {
      if (this.metadata.postId === 0) return;
      
      const fieldName = inputElement.getAttribute('name') || inputElement.id;
      const fieldValue = inputElement.value;
      
      if (!fieldName || !fieldValue.trim()) return;
      
      console.log(`🔄 Auto-saving field: ${fieldName}`);
      
      window.makeAjaxRequest('mkcg_save_biography_field', {
        post_id: this.metadata.postId,
        field_name: fieldName,
        field_value: fieldValue,
        nonce: this.metadata.nonce
      })
      .then(() => {
        console.log(`✅ Auto-saved field: ${fieldName}`);
      })
      .catch(error => {
        console.log(`⚠️ Auto-save failed for field: ${fieldName}`, error);
      });
    },
    
    /**
     * Save all biography data - Following Topics Generator pattern
     */
    saveAllData: function() {
      console.log('🔄 Starting comprehensive biography save operation...');
      
      if (this.metadata.postId === 0) {
        this.showNotification('No post ID found. Please refresh the page.', 'error');
        return;
      }
      
      // Collect all data
      const formData = this.collectFormData();
      
      // Validate we have something to save
      const hasPersonalInfo = formData.name || formData.title || formData.organization;
      const hasServiceData = formData.authorityHook || formData.impactIntro;
      const hasBiographies = this.biographies.short || this.biographies.medium || this.biographies.long;
      
      if (!hasPersonalInfo && !hasServiceData && !hasBiographies) {
        this.showNotification('No data to save. Please add some information first.', 'warning');
        return;
      }
      
      console.log('📊 Saving biography data...', formData);
      
      this.showLoading();
      
      // Add biographies to form data
      formData.biographies = this.biographies;
      
      window.makeAjaxRequest('mkcg_save_biography_data', formData)
        .then(data => {
          this.hideLoading();
          this.showNotification('✅ Biography data saved successfully!', 'success');
          console.log('✅ Save successful:', data);
          
          // Trigger cross-generator communication
          if (window.AppEvents) {
            window.AppEvents.trigger('biography:saved', {
              formData: formData,
              biographies: this.biographies,
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
      const loading = document.querySelector('#biography-generator-loading');
      if (loading) {
        loading.classList.remove('generator__loading--hidden');
      }
    },
    
    hideLoading: function() {
      const loading = document.querySelector('#biography-generator-loading');
      if (loading) {
        loading.classList.add('generator__loading--hidden');
      }
    }
  };

  // Initialize when DOM is ready - Following Topics Generator pattern
  document.addEventListener('DOMContentLoaded', function() {
    const biographyContainer = document.querySelector('.biography-generator');
    if (!biographyContainer) {
      console.log('🎯 Biography Generator: DOM elements not found - skipping initialization');
      return;
    }
    
    console.log('🎯 Biography Generator: DOM Ready - Starting initialization');
    BiographyGenerator.init();
  });

  // Make globally available
  window.BiographyGenerator = BiographyGenerator;
  
  // Debug functions for testing
  window.MKCG_Biography_Test = {
    showData: function() {
      console.log('🔍 Biography Generator current state:');
      console.log('Fields:', BiographyGenerator.fields);
      console.log('Biographies:', BiographyGenerator.biographies);
      console.log('Metadata:', BiographyGenerator.metadata);
    },
    
    testGeneration: function() {
      console.log('🧪 Testing biography generation...');
      BiographyGenerator.generateBiography();
    },
    
    testSave: function() {
      console.log('🧪 Testing save operation...');
      BiographyGenerator.saveAllData();
    },
    
    populateTestData: function() {
      console.log('🧪 Populating test data...');
      
      // Set test values
      BiographyGenerator.fields.name = 'Jane Smith';
      BiographyGenerator.fields.title = 'Digital Marketing Expert';
      BiographyGenerator.fields.organization = 'Growth Solutions Inc';
      BiographyGenerator.fields.who = 'small business owners';
      BiographyGenerator.fields.what = 'scale their revenue to 7 figures';
      BiographyGenerator.fields.when = 'they feel stuck at their current level';
      BiographyGenerator.fields.how = 'through proven digital marketing strategies';
      
      // Update form fields
      BiographyGenerator.updateInputFields('all');
      BiographyGenerator.updateDisplay();
      
      console.log('✅ Test data populated');
    }
  };
  
  console.log('✅ Biography Generator loaded - Following unified patterns');
  console.log('🔧 No jQuery dependencies - Pure vanilla JavaScript');
  console.log('🧪 DEBUG: Use window.MKCG_Biography_Test.populateTestData() to test');

})();