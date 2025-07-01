/**
 * Topics Generator JavaScript - BEM Methodology
 * Handles topics generation with Authority Hook Builder integration
 * Version: 2.0.0 - BEM Update
 */

(function() {
  'use strict';
  
  /**
   * Topics Generator - Main functionality
   */
  const TopicsGenerator = {
    // DOM elements mapping - CORRECTED to match actual HTML IDs
    elements: {
      toggleBuilder: '#topics-generator-toggle-builder',
      authorityHookBuilder: '#topics-generator-authority-hook-builder',
      whoInput: '#mkcg-who',
      clearWho: '[data-field-id="mkcg-who"]',
      resultInput: '#mkcg-result',
      clearResult: '[data-field-id="mkcg-result"]',
      whenInput: '#mkcg-when',
      clearWhen: '[data-field-id="mkcg-when"]',
      howInput: '#mkcg-how',
      clearHow: '[data-field-id="mkcg-how"]',
      editComponentsButton: '#edit-authority-components',
      addButtons: '.topics-generator__add-button',
      generateButton: '#topics-generator-generate-topics',
      authorityHookText: '#authority-hook-content',
      loadingIndicator: '#topics-generator-loading',
      topicsResult: '#topics-generator-topics-result',
      topicsList: '#topics-generator-topics-list',
      tabButtons: '.topics-generator__tab',
      tabContents: '.topics-generator__tab-content',
      fieldModal: '#topics-generator-field-modal',
      fieldNumberInput: '#topics-generator-field-number',
      modalOkButton: '#topics-generator-modal-ok',
      modalCancelButton: '#topics-generator-modal-cancel',
      entryIdField: '#topics-generator-entry-id',
      nonceField: '#topics-generator-nonce'
    },
    
    /**
     * Handle topic selection changes
     */
    handleTopicSelectionChange: function(data) {
      // This will be used when Questions Generator selects topics
      console.log('🎯 Topics Generator: Topic selection changed to', data.topicId, data.topicText);
    },
    
    // Field values - Initialize from existing data, not hardcoded
    fields: {
      who: '',
      result: '',
      when: '',
      how: ''
    },
    
    // Topics storage
    generatedTopics: [],
    selectedTopic: null,
    
    /**
     * Initialize the Topics Generator
     */
    init: function() {
      console.log('🎯 Topics Generator: Initializing with centralized data manager');
      
      // CRITICAL FIX: Initialize centralized data manager first
      this.initializeDataManager();
      
      // Load existing data FIRST before doing anything else
      this.loadExistingData();
      
      this.bindEvents();
      // Don't call updateAuthorityHook() here - let loadExistingData() handle it
    },
    
    /**
     * STANDALONE MODE: Topics Generator works independently (no data manager needed)
     */
    initializeDataManager: function() {
      console.log('📋 Topics Generator: Running in standalone mode (no cross-generator sync)');
      this.dataManagerAvailable = false;
      // Topics Generator works independently - no data manager initialization needed
    },
    
    /**
     * STANDALONE MODE: No data synchronization (Topics Generator works independently)
     */
    setupDataSyncListeners: function() {
      console.log('📋 Topics Generator: Standalone mode - no sync listeners needed');
      // Topics Generator works independently - no sync listeners needed
    },
    
    /**
     * STANDALONE MODE: No external topic updates (Topics Generator works independently)
     */
    handleExternalTopicUpdate: function(data) {
      console.log('📋 Topics Generator: Standalone mode - ignoring external topic updates');
      // Topics Generator works independently - no external updates needed
    },
    
    /**
     * STANDALONE MODE: No centralized data updates (Topics Generator works independently)
     */
    updateDataManager: function(topicId, topicText) {
      console.log('📋 Topics Generator: Standalone mode - no centralized data updates needed');
      // Topics Generator works independently - no centralized updates needed
    },
    
    /**
    * STANDALONE: Load existing data from Formidable/Custom Post (independent operation)
    */
    loadExistingData: function() {
    console.log('📝 Topics Generator: Loading existing data independently...');
    
    // First check if PHP data was passed to JavaScript
    if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.hasEntry) {
    console.log('✅ Found existing PHP data:', window.MKCG_Topics_Data);
    this.populateFromPHPData(window.MKCG_Topics_Data);
    return;
    }
    
    // If no PHP data, try to load via AJAX (standalone mode)
    const entryId = document.querySelector('#topics-generator-entry-id')?.value;
    const urlParams = new URLSearchParams(window.location.search);
    const entryKey = urlParams.get('entry');
    
    if ((entryId && entryId !== '0') || entryKey) {
    console.log('📡 Topics Generator: Loading data via AJAX...', { entryId, entryKey });
    this.loadDataViaAJAX(entryId, entryKey);
    } else {
    console.log('⚠️ Topics Generator: No entry data found, using defaults');
    this.setDefaultData();
    }
    },
    
    /**
    * STANDALONE: Populate from PHP data
    */
    populateFromPHPData: function(phpData) {
    // Load authority hook components
    if (phpData.authorityHook) {
    this.fields.who = phpData.authorityHook.who || '';
    this.fields.result = phpData.authorityHook.result || '';
    this.fields.when = phpData.authorityHook.when || '';
    this.fields.how = phpData.authorityHook.how || '';
    
    this.updateInputFields();
    
    // CRITICAL FIX: Always rebuild from components if they have actual data
    // Check if components contain non-default values
    const hasRealComponents = (
        this.fields.who && this.fields.who !== 'your audience' ||
        this.fields.result && this.fields.result !== 'achieve their goals' ||
        this.fields.when && this.fields.when !== 'they need help' ||
        this.fields.how && this.fields.how !== 'through your method'
    );
    
    if (hasRealComponents) {
    // Build from components (they have real data)
    console.log('🔧 Building authority hook from real components:', this.fields);
    // PHASE 1 FIX: Only update if updateAuthorityHook method exists
        if (typeof this.updateAuthorityHook === 'function') {
        this.updateAuthorityHook(false); // false = no server save during initialization
    }
    } else if (phpData.authorityHook.complete && phpData.authorityHook.complete !== 'I help your audience achieve their goals when they need help through your method.') {
        // Use complete hook only if it's not the default and components are default
    console.log('🔧 Using complete authority hook from database:', phpData.authorityHook.complete);
    this.updateAuthorityHookText(phpData.authorityHook.complete);
    } else {
        // Build from components (fallback)
                console.log('🔧 Building authority hook from default components');
                // PHASE 1 FIX: Only update if updateAuthorityHook method exists
                if (typeof this.updateAuthorityHook === 'function') {
                    this.updateAuthorityHook(false); // false = no server save during initialization
                }
            }
    }
    
    // Load existing topics into form fields
    if (phpData.topics) {
    Object.keys(phpData.topics).forEach(key => {
        if (phpData.topics[key]) {
            const fieldNum = key.split('_')[1];
            const field = document.querySelector(`#topics-generator-topic-field-${fieldNum}`);
            if (field) {
                field.value = phpData.topics[key];
                console.log(`✅ Loaded topic ${fieldNum}: ${phpData.topics[key].substring(0, 50)}...`);
                }
                }
            });
        }
        
        console.log('✅ Topics Generator: PHP data loaded successfully');
    },
    
    /**
     * STANDALONE: Load data via AJAX (independent operation)
     */
    loadDataViaAJAX: function(entryId, entryKey) {
        if (!window.MKCG_FormUtils) {
            console.error('❌ MKCG FormUtils not available for AJAX data loading');
            this.setDefaultData();
            return;
        }
        
        const requestData = {};
        if (entryId && entryId !== '0') {
            requestData.entry_id = entryId;
        }
        if (entryKey) {
            requestData.entry_key = entryKey;
        }
        
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_get_topics_data', requestData, {
            onSuccess: (data) => {
                console.log('✅ Topics Generator: AJAX data loaded:', data);
                this.populateFromAJAXData(data);
            },
            onError: (error) => {
                console.log('⚠️ Topics Generator: AJAX load failed:', error);
                this.setDefaultData();
            }
        });
    },
    
    /**
     * STANDALONE: Populate from AJAX data
     */
    populateFromAJAXData: function(ajaxData) {
        // Load authority hook components
        if (ajaxData.authority_hook) {
            this.fields.who = ajaxData.authority_hook.who || '';
            this.fields.result = ajaxData.authority_hook.result || '';
            this.fields.when = ajaxData.authority_hook.when || '';
            this.fields.how = ajaxData.authority_hook.how || '';
            
            this.updateInputFields();
            this.updateAuthorityHookText(ajaxData.authority_hook.complete);
        }
        
        // Load existing topics
        if (ajaxData.topics) {
            Object.keys(ajaxData.topics).forEach(key => {
                if (ajaxData.topics[key]) {
                    const fieldNum = key.split('_')[1];
                    const field = document.querySelector(`#topics-generator-topic-field-${fieldNum}`);
                    if (field) {
                        field.value = ajaxData.topics[key];
                        console.log(`✅ AJAX loaded topic ${fieldNum}: ${ajaxData.topics[key].substring(0, 50)}...`);
                    }
                }
            });
        }
        
        // CRITICAL FIX: Only show data quality warning if topics are actually missing
        const hasAnyTopics = ajaxData.topics && Object.values(ajaxData.topics).some(topic => topic && topic.trim());
        
        if (ajaxData.data_quality === 'missing' && !hasAnyTopics) {
            this.showDataQualityWarning('No topics data available');
        } else if (hasAnyTopics && ajaxData.data_quality === 'missing') {
            // Data quality might be wrong - topics are actually present
            console.log('⚠️ Data quality status incorrect - topics are present but marked as missing');
        }
        
        console.log('✅ Topics Generator: AJAX data populated successfully');
    },
    
    /**
     * Set default data when no existing data found
     */
    setDefaultData: function() {
        this.fields.who = this.fields.who || 'your audience';
        this.fields.result = this.fields.result || 'achieve their goals';
        this.fields.when = this.fields.when || 'they need help';
        this.fields.how = this.fields.how || 'through your method';
        // PHASE 1 FIX: Only update if updateAuthorityHook method exists
        if (typeof this.updateAuthorityHook === 'function') {
            this.updateAuthorityHook(false); // false = no server save during initialization
        }
        console.log('✅ Topics Generator: Default data set');
    },
    
    /**
     * Show data quality warning
     */
    showDataQualityWarning: function(message) {
        // Create or update warning banner
        let warningBanner = document.querySelector('.topics-generator__warning');
        if (!warningBanner) {
            warningBanner = document.createElement('div');
            warningBanner.className = 'topics-generator__warning';
            warningBanner.style.cssText = `
                background-color: #f39c12;
                color: white;
                padding: 10px 15px;
                border-radius: 4px;
                margin-bottom: 15px;
                font-weight: 500;
            `;
            
            const container = document.querySelector('.topics-generator__panel--left');
            if (container) {
                container.insertBefore(warningBanner, container.firstChild);
            }
        }
        
        warningBanner.textContent = message;
        console.log('⚠️ Topics Generator: Data quality warning:', message);
    },
    
    /**
     * Update input fields with current field values
     */
    updateInputFields: function() {
      console.log('📝 Updating input fields with values:', this.fields);
      
      const fieldMappings = [
        { field: 'who', selector: this.elements.whoInput },
        { field: 'result', selector: this.elements.resultInput },
        { field: 'when', selector: this.elements.whenInput },
        { field: 'how', selector: this.elements.howInput }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input) {
          const value = this.fields[field] || '';
          input.value = value;
          console.log(`✅ Updated ${field} input (${selector}) with: "${value}"`);
          
          // CRITICAL DEBUG: Check if this is a default value vs real data
          const isDefaultValue = (
            (field === 'who' && value === 'your audience') ||
            (field === 'result' && value === 'achieve their goals') ||
            (field === 'when' && value === 'they need help') ||
            (field === 'how' && value === 'through your method')
          );
          
          if (isDefaultValue) {
            console.log(`⚠️ ${field} field has default value - might need real data`);
          } else if (value) {
            console.log(`🎯 ${field} field has real data: "${value}"`);
          }
        } else {
          console.error(`❌ Input field not found: ${selector}`);
        }
      });
    },
    
    /**
     * Update authority hook display with specific text - ENHANCED with multiple fallbacks
     */
    updateAuthorityHookText: function(hookText) {
      console.log('🎯 Updating Authority Hook text to:', hookText);
      
      // Try multiple selectors for compatibility
      const selectors = [
        this.elements.authorityHookText,  // #authority-hook-content
        '#topics-generator-authority-hook-text',  // Fallback 1
        '.authority-hook__content',  // Fallback 2
        '.topics-generator__authority-hook-content p'  // Fallback 3
      ];
      
      let updated = false;
      for (const selector of selectors) {
        const element = document.querySelector(selector);
        if (element) {
          element.textContent = hookText;
          console.log(`✅ Authority Hook text updated via: ${selector}`);
          updated = true;
          break;
        }
      }
      
      if (!updated) {
        console.error('❌ Authority Hook text element not found with any selector');
      }
    },
    
    /**
     * Load existing Formidable form data if available
     */
    loadFormidableData: function() {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      
      if (entryId && entryId !== '0') {
        console.log('📋 Loading existing Formidable data for entry:', entryId);
        
        // Use MKCG_FormUtils to load data
        if (window.MKCG_FormUtils) {
          MKCG_FormUtils.wp.makeAjaxRequest('mkcg_load_topics_data', {
            entry_id: entryId,
            nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || document.querySelector(this.elements.nonceField)?.value || ''
          }, {
            onSuccess: (data) => {
              this.populateFields(data);
            },
            onError: (error) => {
              console.log('Could not load existing data:', error);
            }
          });
        }
      }
      
      // Check URL parameters for pre-population
      const urlParams = new URLSearchParams(window.location.search);
      const entryKey = urlParams.get('entry');
      
      if (entryKey) {
        console.log('🔗 Entry key found in URL:', entryKey);
        // Note: Data loading is now handled by loadExistingData() from PHP
      }
    },
    
    /**
     * Populate form fields with existing data
     */
    populateFields: function(data) {
      // Populate topic fields
      Object.keys(data).forEach(key => {
        if (key.startsWith('topic_')) {
          const fieldNum = key.split('_')[1];
          const selector = `#topics-generator-topic-field-${fieldNum}`;
          const field = document.querySelector(selector);
          if (field && data[key]) {
            field.value = data[key];
          }
        } else if (this.fields.hasOwnProperty(key)) {
          this.fields[key] = data[key] || this.fields[key];
          const inputSelector = `#topics-generator-${key}-input`;
          const input = document.querySelector(inputSelector);
          if (input) {
            input.value = data[key] || '';
          }
        }
      });
      
      this.updateAuthorityHook(false); // false = no server save during initialization
    },
    
    /**
     * Bind events to DOM elements
     */
    bindEvents: function() {
      // Toggle the Authority Hook Builder - BOTH buttons
      const toggleBtn = document.querySelector(this.elements.toggleBuilder);
      const editBtn = document.querySelector(this.elements.editComponentsButton);
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
        console.log('✅ Topics Generator toggle button event bound');
      }
      
      if (editBtn) {
        editBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
        console.log('✅ Shared component edit button event bound');
      }
      
      // Clear inputs - Updated for actual HTML structure
      document.querySelectorAll('.field__clear').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const fieldId = btn.getAttribute('data-field-id');
          if (fieldId) {
            const input = document.getElementById(fieldId);
            if (input) {
              input.value = '';
              input.dispatchEvent(new Event('input', { bubbles: true }));
              console.log(`✅ Cleared field: ${fieldId}`);
            }
          }
        });
      });
      
      // Input change events
      const inputEvents = [
        { selector: this.elements.whoInput, field: 'who', default: 'your audience' },
        { selector: this.elements.resultInput, field: 'result', default: 'achieve their goals' },
        { selector: this.elements.whenInput, field: 'when', default: 'they need help' },
        { selector: this.elements.howInput, field: 'how', default: 'through your method' }
      ];
      
      inputEvents.forEach(({ selector, field, default: defaultValue }) => {
        const input = document.querySelector(selector);
        if (input) {
          input.addEventListener('input', () => {
            // Update the field value (keep empty if user cleared it)
            this.fields[field] = input.value;
            
            // CRITICAL FIX: Only save to server when user actively changes input
            this.updateAuthorityHook(true); // true = save to server
            
            // Auto-save authority hook components to Formidable
            this.autoSaveAuthorityComponent(field, input.value);
          });
        }
      });
      
      // Add example buttons
      document.querySelectorAll(this.elements.addButtons).forEach(button => {
        button.addEventListener('click', () => {
          const field = button.getAttribute('data-field');
          const example = button.getAttribute('data-example');
          this.addExample(field, example);
        });
      });
      
      // Generate topics
      const generateBtn = document.querySelector(this.elements.generateButton);
      if (generateBtn) {
        generateBtn.addEventListener('click', () => {
          this.generateTopics();
        });
      }
      
      // Tab switching
      document.querySelectorAll(this.elements.tabButtons).forEach(button => {
        button.addEventListener('click', () => {
          const tab = button.getAttribute('data-tab');
          this.switchTab(tab);
        });
      });
      
      // Modal events
      const modalOk = document.querySelector(this.elements.modalOkButton);
      const modalCancel = document.querySelector(this.elements.modalCancelButton);
      
      if (modalOk) {
        modalOk.addEventListener('click', () => {
          this.useTopicInField();
        });
      }
      
      if (modalCancel) {
        modalCancel.addEventListener('click', () => {
          this.closeModal();
        });
      }
      
      // Click on form examples
      document.querySelectorAll('.topics-generator__form-example').forEach(example => {
        example.addEventListener('click', (e) => {
          const field = e.target.closest('.topics-generator__form-field');
          if (field) {
            const input = field.querySelector('.topics-generator__form-field-input');
            if (input) {
              input.value = e.target.textContent;
              this.autoSaveField(input);
            }
          }
        });
      });
      
      // Auto-save form fields (Formidable integration)
      document.querySelectorAll('.topics-generator__form-field-input').forEach(input => {
        input.addEventListener('blur', () => {
          this.autoSaveField(input);
        });
      });
    },
    
    /**
     * ENHANCED: Auto-save authority hook component with standardized AJAX and error recovery
     */
    autoSaveAuthorityComponent: function(component, value) {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      if (!entryId || entryId === '0') {
        console.log('⚠️ Topics Generator: No entry ID available for auto-save');
        return;
      }
      
      // Map component names to field IDs (Form 515)
      const componentFieldMap = {
        'who': '10296',
        'result': '10297', 
        'when': '10387',
        'how': '10298'
      };
      
      const fieldId = componentFieldMap[component];
      if (!fieldId) {
        console.error('❌ Topics Generator: Invalid component for auto-save:', component);
        return;
      }
      
      // Enhanced debouncing with component tracking
      clearTimeout(this.componentSaveTimers?.[component]);
      if (!this.componentSaveTimers) this.componentSaveTimers = {};
      
      this.componentSaveTimers[component] = setTimeout(() => {
        this.saveComponentToFormidableEnhanced(component, fieldId, value);
      }, 1000);
    },
    
    /**
     * ENHANCED: Save component to Formidable with standardized AJAX and comprehensive error handling
     */
    saveComponentToFormidableEnhanced: function(component, fieldId, value) {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      
      console.log(`🔄 Topics Generator: Saving ${component} component to field ${fieldId}`);
      
      // Standardized AJAX request with enhanced error recovery
      this.makeStandardizedAjaxRequest('mkcg_save_authority_hook', {
        entry_id: entryId,
        who: this.fields.who,
        result: this.fields.result,
        when: this.fields.when,
        how: this.fields.how
      }, {
        context: `save_authority_component_${component}`,
        retryAttempts: 2,
        onSuccess: (data) => {
          console.log(`✅ Topics Generator: ${component} component saved successfully`);
          this.showComponentSaveSuccess(component);
          
          // Update complete authority hook display if provided
          if (data.authority_hook) {
            this.updateAuthorityHookText(data.authority_hook);
          }
        },
        onError: (error) => {
          console.error(`❌ Topics Generator: Failed to save ${component} component:`, error);
          this.showComponentSaveError(component, error);
        },
        onComplete: () => {
          console.log(`🏁 Topics Generator: ${component} save operation completed`);
        }
      });
    },
    
    /**
     * PHASE 2B: Enhanced auto-save with professional visual feedback and state management
     */
    autoSaveField: function(inputElement) {
      const entryId = document.querySelector(this.elements.entryIdField)?.value;
      if (!entryId || entryId === '0') {
        console.log('⚠️ No entry ID available for auto-save');
        return;
      }
      
      const fieldName = inputElement.getAttribute('name');
      const fieldValue = inputElement.value;
      
      if (!fieldName || !fieldValue.trim()) return;
      
      // STANDALONE MODE: No centralized data manager updates
      const topicMatch = inputElement.id.match(/topic-field-(\d+)/);
      if (topicMatch) {
        const topicId = parseInt(topicMatch[1]);
        console.log('📋 Topics Generator: Standalone mode - saving topic', topicId, 'locally only');
      }
      
      // PHASE 2B: Professional auto-save with comprehensive state management
      if (window.MKCG_FormUtils) {
        console.log('💾 PHASE 2B Enhanced auto-save for field:', fieldName, 'with value:', fieldValue);
        
        // Clear previous state indicators
        this.clearFieldStateIndicators(inputElement);
        
        // Validate field before saving with Enhanced Validation Manager
        if (window.EnhancedValidationManager) {
          const validation = window.EnhancedValidationManager.validateField(
            fieldName.includes('topic') ? 'topic' : fieldName, 
            fieldValue,
            { context: 'auto_save' }
          );
          
          if (!validation.valid) {
            console.log('⚠️ Field validation failed, showing validation feedback:', validation.errors);
            this.showFieldValidationError(inputElement, validation.errors);
            return;
          }
          
          // Show validation warnings if any
          if (validation.warnings.length > 0) {
            this.showFieldValidationWarning(inputElement, validation.warnings);
          }
        }
        
        // Show saving state
        this.showFieldSavingState(inputElement);
        
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_topic_field', {
          entry_id: entryId,
          field_name: fieldName,
          field_value: fieldValue,
          nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || document.querySelector(this.elements.nonceField)?.value || ''
        }, {
          onStart: () => {
            // Additional loading state setup if needed
            console.log('🔄 Auto-save started for:', fieldName);
          },
          onSuccess: (response) => {
            console.log('✅ PHASE 2B Enhanced auto-save successful for field:', fieldName);
            this.showFieldSavedState(inputElement);
            
            // Optional: Show success toast for important saves
            if (fieldName.includes('authority') && window.EnhancedUIFeedback) {
              window.EnhancedUIFeedback.showToast(
                'Authority hook saved successfully',
                'success',
                2000
              );
            }
          },
          onError: (error) => {
            console.log('❌ PHASE 2B Enhanced auto-save failed for field:', fieldName, error);
            this.showFieldErrorState(inputElement, error);
            
            // Show enhanced error notification
            if (window.EnhancedUIFeedback) {
              window.EnhancedUIFeedback.showToast({
                title: 'Auto-save Failed',
                message: `Failed to save ${fieldName}. Your changes are preserved locally.`,
                actions: ['Try saving manually', 'Check your connection']
              }, 'warning', 5000);
            }
          },
          timeout: 10000, // 10 second timeout for auto-save
          retryAttempts: 2 // 2 retries for auto-save with enhanced feedback
        });
      } else {
        console.log('⚠️ MKCG_FormUtils not available for auto-save');
        if (window.EnhancedUIFeedback) {
          window.EnhancedUIFeedback.showToast({
            title: 'Auto-save Unavailable',
            message: 'Please save your changes manually.',
            actions: ['Use the save button', 'Refresh the page if the problem persists']
          }, 'warning', 4000);
        }
      }
    },
    
    /**
     * PHASE 2B: Professional visual state management for form fields
     */
    clearFieldStateIndicators: function(fieldElement) {
      // Remove all existing state indicators
      const parent = fieldElement.parentNode;
      if (parent) {
        const indicators = parent.querySelectorAll('.field-state-indicator');
        indicators.forEach(indicator => indicator.remove());
      }
      
      // Reset field styling
      fieldElement.style.borderColor = '';
      fieldElement.style.boxShadow = '';
      fieldElement.classList.remove('field--saving', 'field--saved', 'field--error', 'field--warning');
    },
    
    showFieldSavingState: function(fieldElement) {
      fieldElement.classList.add('field--saving');
      fieldElement.style.borderColor = '#3498db';
      fieldElement.style.boxShadow = '0 0 5px rgba(52, 152, 219, 0.3)';
      
      const indicator = this.createFieldStateIndicator('💾 Saving...', '#3498db');
      this.addFieldStateIndicator(fieldElement, indicator);
    },
    
    showFieldSavedState: function(fieldElement) {
      this.clearFieldStateIndicators(fieldElement);
      fieldElement.classList.add('field--saved');
      fieldElement.style.borderColor = '#27ae60';
      fieldElement.style.boxShadow = '0 0 5px rgba(39, 174, 96, 0.3)';
      
      const indicator = this.createFieldStateIndicator('✓ Saved', '#27ae60');
      this.addFieldStateIndicator(fieldElement, indicator);
      
      // Auto-clear success state after 3 seconds
      setTimeout(() => {
        this.clearFieldStateIndicators(fieldElement);
      }, 3000);
    },
    
    showFieldErrorState: function(fieldElement, error) {
      this.clearFieldStateIndicators(fieldElement);
      fieldElement.classList.add('field--error');
      fieldElement.style.borderColor = '#e74c3c';
      fieldElement.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      
      const indicator = this.createFieldStateIndicator('⚠ Save failed', '#e74c3c');
      this.addFieldStateIndicator(fieldElement, indicator);
      
      // Auto-clear error state after 5 seconds
      setTimeout(() => {
        this.clearFieldStateIndicators(fieldElement);
      }, 5000);
    },
    
    showFieldValidationError: function(fieldElement, errors) {
      fieldElement.style.borderColor = '#e74c3c';
      fieldElement.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.3)';
      
      const indicator = this.createFieldStateIndicator('⚠ ' + errors[0], '#e74c3c');
      this.addFieldStateIndicator(fieldElement, indicator);
      
      // Auto-clear validation error when user starts typing
      const clearOnInput = () => {
        this.clearFieldStateIndicators(fieldElement);
        fieldElement.removeEventListener('input', clearOnInput);
      };
      fieldElement.addEventListener('input', clearOnInput);
    },
    
    showFieldValidationWarning: function(fieldElement, warnings) {
      const indicator = this.createFieldStateIndicator('⚠ ' + warnings[0], '#f39c12');
      indicator.style.fontSize = '11px';
      this.addFieldStateIndicator(fieldElement, indicator);
      
      // Auto-clear warning after 4 seconds
      setTimeout(() => {
        indicator.remove();
      }, 4000);
    },
    
    createFieldStateIndicator: function(text, color) {
      const indicator = document.createElement('div');
      indicator.className = 'field-state-indicator';
      indicator.textContent = text;
      indicator.style.cssText = `
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: ${color};
        font-size: 12px;
        font-weight: 500;
        pointer-events: none;
        z-index: 10;
        background: white;
        padding: 2px 4px;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        animation: fadeIn 0.3s ease;
      `;
      
      return indicator;
    },
    
    addFieldStateIndicator: function(fieldElement, indicator) {
      const parent = fieldElement.parentNode;
      if (parent) {
        // Ensure parent has relative positioning
        if (getComputedStyle(parent).position === 'static') {
          parent.style.position = 'relative';
        }
        parent.appendChild(indicator);
      }
    },
    
    /**
     * Toggle the Authority Hook Builder visibility - ENHANCED for dual button support
     */
    toggleBuilder: function() {
      console.log('🔄 Toggling Authority Hook Builder');
      const builderEl = document.querySelector(this.elements.authorityHookBuilder);
      const toggleBtn = document.querySelector(this.elements.toggleBuilder);
      const editBtn = document.querySelector(this.elements.editComponentsButton);
      
      if (!builderEl) {
        console.error('❌ Builder element not found:', this.elements.authorityHookBuilder);
        return;
      }
      
      const isHidden = builderEl.classList.contains('topics-generator__builder--hidden');
      
      if (isHidden) {
        // Show the builder
        builderEl.classList.remove('topics-generator__builder--hidden');
        
        // Update button texts
        if (toggleBtn) toggleBtn.textContent = 'Hide Builder';
        if (editBtn) {
          editBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>Hide Builder';
        }
        
        // Scroll to builder for better UX
        setTimeout(() => {
          builderEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
        
        console.log('✅ Authority Hook Builder shown');
      } else {
        // Hide the builder
        builderEl.classList.add('topics-generator__builder--hidden');
        
        // Update button texts
        if (toggleBtn) toggleBtn.textContent = 'Edit Components';
        if (editBtn) {
          editBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>Edit Components';
        }
        
        console.log('✅ Authority Hook Builder hidden');
      }
    },
    
    /**
     * Add an example to a specific field - FIXED selectors
     */
    addExample: function(field, exampleText) {
      // Use correct field mapping
      const fieldMap = {
        'who': this.elements.whoInput,
        'result': this.elements.resultInput,
        'when': this.elements.whenInput,
        'how': this.elements.howInput
      };
      
      const inputSelector = fieldMap[field];
      if (!inputSelector) {
        console.error(`❌ Unknown field: ${field}`);
        return;
      }
      
      const input = document.querySelector(inputSelector);
      if (!input) {
        console.error(`❌ Input not found for field ${field}: ${inputSelector}`);
        return;
      }
      
      const currentVal = input.value.trim();
      
      if (currentVal === '') {
        input.value = exampleText;
      } else {
        input.value = currentVal + ', ' + exampleText;
      }
      
      // Update field value and authority hook
      this.fields[field] = input.value;
      this.updateAuthorityHook(true); // true = save to server (user action)
      
      // Trigger input event
      input.dispatchEvent(new Event('input', { bubbles: true }));
      console.log(`✅ Added example to ${field}: ${exampleText}`);
    },
    
    /**
     * Switch between WHO/WHAT/WHEN/HOW tabs
     */
    switchTab: function(tab) {
      // Update active tab button
      document.querySelectorAll(this.elements.tabButtons).forEach(button => {
        button.classList.remove('topics-generator__tab--active');
      });
      
      const activeTab = document.querySelector(`.topics-generator__tab[data-tab="${tab}"]`);
      if (activeTab) {
        activeTab.classList.add('topics-generator__tab--active');
      }
      
      // Hide all tab content and show the selected one
      document.querySelectorAll(this.elements.tabContents).forEach(content => {
        content.classList.remove('topics-generator__tab-content--active');
      });
      
      const tabContent = document.querySelector(`#topics-generator-${tab}-tab`);
      if (tabContent) {
        tabContent.classList.add('topics-generator__tab-content--active');
      }
    },
    
    /**
     * Update the Authority Hook text based on input fields - ENHANCED with corrected selector
     */
    updateAuthorityHook: function(saveToServer = false) {
      const hookText = `I help ${this.fields.who || 'your audience'} ${this.fields.result || 'achieve their goals'} when ${this.fields.when || 'they need help'} ${this.fields.how || 'through your method'}.`;
      
      // Use the enhanced updateAuthorityHookText method with multiple fallbacks
      this.updateAuthorityHookText(hookText);
      
      // CRITICAL FIX: Only save to server when explicitly requested (not during initialization)
      if (saveToServer) {
        console.log('🔄 Saving authority hook to server:', hookText);
        this.saveCompleteAuthorityHook(hookText);
      } else {
        console.log('📝 Authority hook updated locally only (no server save):', hookText);
      }
    },
    
    /**
     * Save the complete authority hook to Formidable
     */
    saveCompleteAuthorityHook: function(hookText) {
      const entryId = document.querySelector(this.elements.entryIdField)?.value;
      if (!entryId || entryId === '0') {
        console.log('⚠️ No entry ID available for saving complete authority hook');
        return;
      }
      
      // Debounce the save
      clearTimeout(this.hookSaveTimer);
      this.hookSaveTimer = setTimeout(() => {
        if (window.MKCG_FormUtils) {
          MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_field', {
            entry_id: entryId,
            field_id: '10358',
            value: hookText,
            nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || document.querySelector(this.elements.nonceField)?.value || ''
          }, {
            onSuccess: () => {
              console.log('✅ Complete authority hook saved to field 10358');
            },
            onError: (error) => {
              console.log('⚠️ Failed to save complete authority hook:', error);
            }
          });
        }
      }, 1500);
    },
    
    /**
     * ENHANCED: Topic generation with standardized AJAX and comprehensive error recovery
     */
    generateTopics: function() {
      console.log('🎯 Topics Generator: Starting enhanced topic generation with standardized AJAX');
      
      const authorityHook = document.querySelector(this.elements.authorityHookText)?.textContent;
      
      if (!authorityHook) {
        this.showUserFeedback({
          type: 'warning',
          title: 'Authority Hook Required',
          message: 'Please build your authority hook first before generating topics.',
          actions: ['Click "Edit Components" to build your authority hook']
        });
        return;
      }
      
      // Validate data before submission with enhanced feedback
      const formData = {
        authority_hook: authorityHook,
        who: this.fields.who,
        result: this.fields.result,
        when: this.fields.when,
        how: this.fields.how,
        entry_id: document.querySelector(this.elements.entryIdField)?.value
      };
      
      // PHASE 2B: Enhanced validation with progressive feedback
      if (window.EnhancedValidationManager) {
        const validationResult = window.EnhancedValidationManager.validateBeforeSubmit(
          formData, 
          'generate_topics'
        );
        
        if (!validationResult.valid) {
          console.log('❌ Validation failed for topic generation:', validationResult);
          window.EnhancedValidationManager.showValidationErrors(validationResult);
          return;
        }
        
        // Show validation success if Enhanced UI is available
        if (validationResult.warnings.length === 0 && window.EnhancedUIFeedback) {
          window.EnhancedUIFeedback.showToast('✓ Pre-generation validation passed', 'success', 1500);
        }
      }
      
      // Hide previous results and show progressive loading
      this.hideTopicsResult();
      
      // PHASE 2B: Progressive loading with multiple stages
      let loadingId = null;
      let progressId = null;
      
      if (window.EnhancedUIFeedback) {
        // Show initial loading state
        loadingId = window.EnhancedUIFeedback.showLoadingSpinner(
          '#topics-generator',
          'Preparing topic generation request...',
          { animated: true }
        );
        
        // Add progress bar
        progressId = window.EnhancedUIFeedback.showProgress(
          '#topics-generator', 
          10, 
          { animated: true }
        );
      } else {
        this.showLoading();
      }
      
      // PHASE 2B: Progressive loading stages
      const updateProgress = (stage, message, progress) => {
        console.log(`🔄 Generation stage: ${stage} (${progress}%)`);
        
        if (window.EnhancedUIFeedback) {
          // Update loading message
          const loadingElement = document.querySelector('.mkcg-loading-message');
          if (loadingElement) {
            loadingElement.textContent = message;
          }
          
          // Update progress bar
          if (progressId) {
            window.EnhancedUIFeedback.showProgress('#topics-generator', progress, { animated: true });
          }
        }
      };
      
      // ENHANCED: Use standardized AJAX with comprehensive error recovery
      this.makeStandardizedAjaxRequest('mkcg_generate_topics', formData, {
        context: 'generate_topics',
        timeout: 45000,
        retryAttempts: 2,
        progressCallback: updateProgress,
        onStart: () => {
          console.log('🔄 Topics Generator: Topic generation started with standardized AJAX');
          updateProgress('start', 'Initializing AI request...', 20);
        },
        onProgress: (progress) => {
          if (progress.type === 'retry') {
            updateProgress('retry', `Retrying connection (attempt ${progress.attempt})...`, 30);
            this.showUserFeedback({
              type: 'info',
              title: 'Retrying Generation',
              message: `Attempt ${progress.attempt} of ${progress.maxAttempts} - AI service may be busy`,
              duration: 4000
            });
          } else if (progress.type === 'queued') {
            updateProgress('queued', 'Request queued - will retry when online...', 15);
          }
        },
        onSuccess: (data) => {
          console.log('✅ Topics Generator: Topic generation successful:', data);
          
          updateProgress('processing', 'Processing generated topics...', 80);
          
          // Simulate processing time for smooth UX
          setTimeout(() => {
            updateProgress('complete', 'Topics generated successfully!', 100);
            this.hideLoadingStates({ loadingId, progressId });
            
            if (data.topics && Array.isArray(data.topics) && data.topics.length > 0) {
              this.generatedTopics = data.topics;
              this.displayTopics(data.topics);
              
              this.showUserFeedback({
                type: 'success',
                title: 'Topics Generated Successfully!',
                message: `Generated ${data.topics.length} compelling interview topics tailored to your authority hook.`,
                actions: [
                  'Click "Use" next to any topic to add it to your form',
                  'Topics are automatically saved when you use them'
                ],
                duration: 6000
              });
            } else {
              console.log('⚠️ No topics in response, using demo topics');
              this.generateDemoTopics(authorityHook);
            }
          }, 500);
        },
        onError: (error) => {
          console.error('❌ Topics Generator: Topic generation failed:', error);
          
          updateProgress('error', 'Generation failed - switching to demo topics...', 0);
          this.hideLoadingStates({ loadingId, progressId });
          
          // Show demo topics as graceful fallback
          setTimeout(() => {
            console.log('🎭 Topics Generator: Showing demo topics as graceful fallback');
            this.generateDemoTopics(authorityHook);
            
            this.showUserFeedback({
              type: 'info',
              title: 'Using Demo Topics',
              message: 'AI generation is temporarily unavailable. Here are sample topics you can customize.',
              actions: [
                'These topics are examples based on your authority hook',
                'Edit them to match your specific expertise',
                'Try AI generation again in a few minutes'
              ],
              duration: 8000
            });
          }, 800);
        },
        onComplete: () => {
          console.log('🏁 Topics Generator: Topic generation completed');
        }
      });
        
      
      // Simulate initial progress for better UX
      setTimeout(() => updateProgress('sending', 'Sending request to AI service...', 40), 200);
      setTimeout(() => updateProgress('processing', 'AI is analyzing your authority hook...', 60), 1000);
    },
    
    /**
     * ENHANCED: Fallback demo topics generation with standardized handling
     */
    generateDemoTopicsFallback: function(authorityHook, updateProgressFn, loadingIds) {
      console.log('⚠️ Topics Generator: MKCG_FormUtils not available, using demo topics fallback');
      
      if (updateProgressFn) {
        updateProgressFn('fallback', 'FormUtils unavailable - loading demo topics...', 50);
      }
      
      setTimeout(() => {
        if (updateProgressFn) {
          updateProgressFn('demo', 'Preparing demo topics...', 80);
        }
        
        setTimeout(() => {
          if (updateProgressFn) {
            updateProgressFn('complete', 'Demo topics ready!', 100);
          }
          this.hideLoadingStates(loadingIds || {});
          this.generateDemoTopics(authorityHook);
        }, 800);
      }, 1500);
    },
    
    /**
     * Generate demo topics (fallback when API is not available)
     */
    generateDemoTopics: function(authorityHook) {
      let topics;
      
      if (authorityHook.includes('revenue')) {
        topics = [
          "Navigating Turbulent Times: Proven Strategies for Small Businesses to Survive and Thrive During Crises",
          "From Adversity to Advantage: How Businesses Can Turn Challenges into Opportunities for Growth",
          "The Power of Community: How Small Businesses Can Collaborate to Overcome Economic Uncertainty",
          "Building a Resilient Business: Core Mindset Frameworks That Empower Business Leaders",
          "Streamlining Operations: How to Identify and Eliminate Revenue-Draining Inefficiencies"
        ];
      } else {
        topics = [
          "The Authority Positioning Framework: How to Become the Go-To Expert in Your Niche",
          "Creating Content That Converts: A Strategic Approach to Audience Building",
          "Systems for Success: Automating Your Business to Create More Freedom",
          "The Podcast Guest Formula: How to Turn Interviews into High-Value Clients",
          "Building a Sustainable Business Model That Serves Your Lifestyle Goals"
        ];
      }
      
      this.generatedTopics = topics;
      this.displayTopics(topics);
    },
    
    /**
     * Display generated topics in the UI with Use buttons
     */
    displayTopics: function(topics) {
      const topicsList = document.querySelector(this.elements.topicsList);
      if (!topicsList) return;
      
      topicsList.innerHTML = '';
      
      topics.forEach((topic, index) => {
        const topicNumber = index + 1;
        
        const topicItem = document.createElement('div');
        topicItem.className = 'topics-generator__topic';
        
        const numberDiv = document.createElement('div');
        numberDiv.className = 'topics-generator__topic-number';
        numberDiv.textContent = `Topic ${topicNumber}:`;
        
        const textDiv = document.createElement('div');
        textDiv.className = 'topics-generator__topic-text';
        textDiv.textContent = topic;
        
        const useButton = document.createElement('button');
        useButton.className = 'topics-generator__button topics-generator__button--use';
        useButton.textContent = 'Use';
        useButton.setAttribute('data-topic', topicNumber);
        useButton.addEventListener('click', () => {
          this.openFieldModal(topicNumber, topic);
        });
        
        topicItem.appendChild(numberDiv);
        topicItem.appendChild(textDiv);
        topicItem.appendChild(useButton);
        
        topicsList.appendChild(topicItem);
      });
      
      this.showTopicsResult();
    },
    
    /**
     * Show loading indicator
     */
    showLoading: function() {
      const loading = document.querySelector(this.elements.loadingIndicator);
      if (loading) {
        loading.classList.remove('topics-generator__loading--hidden');
      }
    },
    
    /**
     * Hide loading indicator
     */
    hideLoading: function() {
      const loading = document.querySelector(this.elements.loadingIndicator);
      if (loading) {
        loading.classList.add('topics-generator__loading--hidden');
      }
    },
    
    /**
     * Show topics result section
     */
    showTopicsResult: function() {
      const result = document.querySelector(this.elements.topicsResult);
      if (result) {
        result.classList.remove('topics-generator__results--hidden');
      }
    },
    
    /**
     * Hide topics result section
     */
    hideTopicsResult: function() {
      const result = document.querySelector(this.elements.topicsResult);
      if (result) {
        result.classList.add('topics-generator__results--hidden');
      }
    },
    
    /**
     * Open the field selection modal
     */
    openFieldModal: function(topicNumber, topicText) {
      this.selectedTopic = {
        number: topicNumber,
        text: topicText
      };
      
      const fieldInput = document.querySelector(this.elements.fieldNumberInput);
      if (fieldInput) {
        fieldInput.value = topicNumber;
      }
      
      const modal = document.querySelector(this.elements.fieldModal);
      if (modal) {
        modal.classList.add('topics-generator__modal--active');
      }
    },
    
    /**
     * Close the field selection modal
     */
    closeModal: function() {
      const modal = document.querySelector(this.elements.fieldModal);
      if (modal) {
        modal.classList.remove('topics-generator__modal--active');
      }
      this.selectedTopic = null;
    },
    
    /**
     * ENHANCED: Use selected topic with standardized save and comprehensive feedback
     */
    useTopicInField: function() {
      if (!this.selectedTopic) {
        console.error('❌ Topics Generator: No topic selected for use');
        return;
      }
      
      const fieldNumberInput = document.querySelector(this.elements.fieldNumberInput);
      if (!fieldNumberInput) {
        console.error('❌ Topics Generator: Field number input not found');
        return;
      }
      
      const fieldNumber = parseInt(fieldNumberInput.value);
      
      if (isNaN(fieldNumber) || fieldNumber < 1 || fieldNumber > 5) {
        this.showUserFeedback({
          type: 'warning',
          title: 'Invalid Field Number',
          message: 'Please enter a valid field number (1-5)',
          duration: 3000
        });
        return;
      }
      
      const fieldSelector = `#topics-generator-topic-field-${fieldNumber}`;
      const inputElement = document.querySelector(fieldSelector);
      
      if (inputElement) {
        console.log(`🎯 Topics Generator: Using topic "${this.selectedTopic.text}" in field ${fieldNumber}`);
        
        inputElement.value = this.selectedTopic.text;
        
        // Enhanced auto-save with feedback
        this.autoSaveFieldEnhanced(inputElement, {
          onSuccess: () => {
            this.showUserFeedback({
              type: 'success',
              title: 'Topic Added Successfully',
              message: `Topic ${fieldNumber} has been saved: "${this.selectedTopic.text.substring(0, 50)}..."`,
              duration: 3000
            });
            
            console.log(`✅ Topics Generator: Topic ${fieldNumber} saved successfully`);
          },
          onError: (error) => {
            this.showUserFeedback({
              type: 'error',
              title: 'Save Failed',
              message: 'Topic was added to the form but could not be saved to the server. Please save manually.',
              duration: 5000
            });
            
            console.error(`❌ Topics Generator: Failed to save topic ${fieldNumber}:`, error);
          }
        });
        
        // STANDALONE MODE: Topics Generator works independently
        console.log('📋 Topics Generator: Standalone mode - topic saved locally');
      } else {
        console.error(`❌ Topics Generator: Field element not found: ${fieldSelector}`);
        this.showUserFeedback({
          type: 'error',
          title: 'Field Not Found',
          message: `Could not find topic field ${fieldNumber}. Please refresh the page.`,
          duration: 5000
        });
      }
      
      this.closeModal();
    },
    
    // PHASE 2B: Network awareness integration
    networkStatus: {
      isOnline: true,
      indicator: null
    },
    
    /**
     * PHASE 2B: Update network status from Offline Manager
     */
    updateNetworkStatus: function(isOnline) {
      this.networkStatus.isOnline = isOnline;
      
      console.log(`🌐 Topics Generator: Network status updated - ${isOnline ? 'Online' : 'Offline'}`);
      
      // Update UI based on network status
      this.updateUIForNetworkStatus(isOnline);
      
      // If back online and there are pending operations, show sync option
      if (isOnline && window.MKCG_OfflineManager) {
        const queueStatus = window.MKCG_OfflineManager.getNetworkStatus();
        if (queueStatus.queuedOperations > 0) {
          this.showSyncPendingNotification(queueStatus.queuedOperations);
        }
      }
    },
    
    /**
     * PHASE 2B: Update UI elements based on network status
     */
    updateUIForNetworkStatus: function(isOnline) {
      const generateButton = document.querySelector(this.elements.generateButton);
      
      if (generateButton) {
        if (isOnline) {
          generateButton.disabled = false;
          generateButton.textContent = 'Generate Topics';
          generateButton.style.opacity = '1';
        } else {
          generateButton.disabled = true;
          generateButton.textContent = 'Generate Topics (Offline)';
          generateButton.style.opacity = '0.6';
        }
      }
      
      // Update form field placeholders for offline mode
      const formFields = document.querySelectorAll('.topics-generator__form-field-input');
      formFields.forEach(field => {
        if (isOnline) {
          field.classList.remove('field--offline-mode');
        } else {
          field.classList.add('field--offline-mode');
        }
      });
    },
    
    /**
     * PHASE 2B: Show notification about pending sync operations
     */
    showSyncPendingNotification: function(count) {
      if (window.EnhancedUIFeedback) {
        window.EnhancedUIFeedback.showToast({
          title: 'Offline Changes Detected',
          message: `You have ${count} pending changes that will be synced automatically.`,
          actions: ['Changes are being processed in the background']
        }, 'info', 5000);
      }
    },
    
    /**
     * PHASE 2B: Enhanced auto-save with offline awareness
     */
    autoSaveFieldWithOfflineSupport: function(inputElement) {
      // Check network status first
      if (!this.networkStatus.isOnline && window.MKCG_OfflineManager) {
        console.log('📱 Offline mode: Queuing auto-save for later sync');
        
        const fieldName = inputElement.getAttribute('name');
        const fieldValue = inputElement.value;
        const entryId = document.querySelector(this.elements.entryIdField)?.value;
        
        // Queue the operation for when back online
        window.MKCG_OfflineManager.queueOperation('mkcg_save_topic_field', {
          entry_id: entryId,
          field_name: fieldName,
          field_value: fieldValue,
          nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || ''
        }, {
          onSuccess: () => {
            console.log('✅ Offline queued save completed for:', fieldName);
            this.showFieldSavedState(inputElement);
          },
          onError: (error) => {
            console.log('❌ Offline queued save failed for:', fieldName, error);
            this.showFieldErrorState(inputElement, error);
          }
        });
        
        // Show offline saved state
        this.showFieldOfflineState(inputElement);
        return;
      }
      
      // Use regular auto-save if online
      this.autoSaveField(inputElement);
    },
    
    /**
     * PHASE 2B: Show offline saved state
     */
    showFieldOfflineState: function(fieldElement) {
      this.clearFieldStateIndicators(fieldElement);
      fieldElement.classList.add('field--offline-saved');
      fieldElement.style.borderColor = '#f39c12';
      fieldElement.style.boxShadow = '0 0 5px rgba(243, 156, 18, 0.3)';
      
      const indicator = this.createFieldStateIndicator('📱 Saved offline', '#f39c12');
      this.addFieldStateIndicator(fieldElement, indicator);
      
      // Auto-clear offline state after 4 seconds
      setTimeout(() => {
        this.clearFieldStateIndicators(fieldElement);
      }, 4000);
    },
    
    /**
     * CRITICAL FIX: Add missing makeStandardizedAjaxRequest method
     */
    makeStandardizedAjaxRequest: function(action, data, options = {}) {
      console.log(`🔄 Making standardized AJAX request: ${action}`);
      
      const requestData = {
        action: action,
        nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || '',
        ...data
      };
      
      // Use enhanced AJAX manager if available, otherwise fallback to FormUtils
      if (window.EnhancedAjaxManager) {
        return window.EnhancedAjaxManager.makeRequest(action, requestData, options);
      } else if (window.MKCG_FormUtils) {
        return MKCG_FormUtils.wp.makeAjaxRequest(action, requestData, options);
      } else {
        console.error('❌ No AJAX manager available');
        if (options.onError) {
          options.onError('No AJAX manager available');
        }
      }
    },
    
    /**
     * CRITICAL FIX: Add missing hideLoadingStates method
     */
    hideLoadingStates: function(options = {}) {
      console.log('🔄 Hiding loading states');
      
      if (window.EnhancedUIFeedback) {
        if (options.loadingId) {
          window.EnhancedUIFeedback.hideLoadingSpinner(options.loadingId);
        }
        if (options.progressId) {
          window.EnhancedUIFeedback.hideProgress(options.progressId);
        }
      }
      
      this.hideLoading();
    },
    
    /**
     * CRITICAL FIX: Add missing showUserFeedback method
     */
    showUserFeedback: function(feedbackOptions) {
      console.log('💬 Showing user feedback:', feedbackOptions);
      
      if (window.EnhancedUIFeedback) {
        window.EnhancedUIFeedback.showToast(feedbackOptions, feedbackOptions.type, feedbackOptions.duration);
      } else {
        // Fallback to browser alert
        alert(`${feedbackOptions.title}: ${feedbackOptions.message}`);
      }
    },
    
    /**
     * CRITICAL FIX: Add missing autoSaveFieldEnhanced method
     */
    autoSaveFieldEnhanced: function(inputElement, callbacks = {}) {
      console.log('💾 Enhanced auto-save for field:', inputElement.name);
      
      const entryId = document.querySelector(this.elements.entryIdField)?.value;
      if (!entryId || entryId === '0') {
        console.log('⚠️ No entry ID available for enhanced auto-save');
        if (callbacks.onError) {
          callbacks.onError('No entry ID available');
        }
        return;
      }
      
      const fieldName = inputElement.getAttribute('name');
      const fieldValue = inputElement.value;
      
      this.makeStandardizedAjaxRequest('mkcg_save_topic_field', {
        entry_id: entryId,
        field_name: fieldName,
        field_value: fieldValue
      }, {
        onSuccess: (data) => {
          console.log('✅ Enhanced auto-save successful');
          if (callbacks.onSuccess) {
            callbacks.onSuccess(data);
          }
        },
        onError: (error) => {
          console.error('❌ Enhanced auto-save failed:', error);
          if (callbacks.onError) {
            callbacks.onError(error);
          }
        }
      });
    },
    
    /**
     * CRITICAL FIX: Add missing showComponentSaveSuccess method
     */
    showComponentSaveSuccess: function(component) {
      console.log(`✅ Component ${component} saved successfully`);
      
      if (window.EnhancedUIFeedback) {
        window.EnhancedUIFeedback.showToast(
          `${component.toUpperCase()} component saved`,
          'success',
          2000
        );
      }
    },
    
    /**
     * CRITICAL FIX: Add missing showComponentSaveError method
     */
    showComponentSaveError: function(component, error) {
      console.error(`❌ Component ${component} save failed:`, error);
      
      if (window.EnhancedUIFeedback) {
        window.EnhancedUIFeedback.showToast(
          `Failed to save ${component.toUpperCase()} component`,
          'error',
          4000
        );
      }
    },
    
    /**
     * PHASE 2B: Enhanced topic generation with offline detection
     */
    generateTopicsWithOfflineCheck: function() {
      // Check if we're offline
      if (!this.networkStatus.isOnline) {
        if (window.EnhancedUIFeedback) {
          window.EnhancedUIFeedback.showToast({
            title: 'Generation Requires Internet',
            message: 'Topic generation needs an internet connection to access AI services.',
            actions: [
              'Check your connection and try again',
              'Use demo topics while offline'
            ]
          }, 'warning', 8000);
        } else {
          alert('Topic generation requires an internet connection. Please check your connection and try again.');
        }
        
        // Offer demo topics as alternative
        const authorityHook = document.querySelector(this.elements.authorityHookText)?.textContent || 'default hook';
        this.generateDemoTopics(authorityHook);
        return;
      }
      
      // Use enhanced generation if online
      this.generateTopics();
    }
  };

  // Initialize when DOM is ready with enhanced dependency loading
  document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 PHASE 2B: Topics Generator DOM Ready - Enhanced Version');
    
    // Wait for all required dependencies
    const waitForDependencies = () => {
      const requiredDependencies = {
        FormUtils: window.MKCG_FormUtils,
        OfflineManager: window.MKCG_OfflineManager,
        EnhancedUIFeedback: window.EnhancedUIFeedback,
        EnhancedAjaxManager: window.EnhancedAjaxManager
      };
      
      const missing = Object.keys(requiredDependencies).filter(dep => !requiredDependencies[dep]);
      
      if (missing.length === 0) {
        console.log('✅ PHASE 2B: All dependencies loaded - Starting enhanced initialization');
        
        // Initialize Topics Generator
        TopicsGenerator.init();
        
        // Setup network awareness
        if (window.MKCG_OfflineManager) {
          window.MKCG_OfflineManager.addNetworkStatusListener((isOnline) => {
            TopicsGenerator.updateNetworkStatus(isOnline);
          });
          
          // Get initial network status
          const networkStatus = window.MKCG_OfflineManager.getNetworkStatus();
          TopicsGenerator.updateNetworkStatus(networkStatus.isOnline);
          
          console.log('🌐 PHASE 2B: Network awareness integrated');
        }
        
        // Replace auto-save method with offline-aware version
        document.querySelectorAll('.topics-generator__form-field-input').forEach(input => {
          input.removeEventListener('blur', TopicsGenerator.autoSaveField);
          input.addEventListener('blur', () => {
            TopicsGenerator.autoSaveFieldWithOfflineSupport(input);
          });
        });
        
        // Replace generate button with offline-aware version
        const generateBtn = document.querySelector(TopicsGenerator.elements.generateButton);
        if (generateBtn) {
          generateBtn.removeEventListener('click', TopicsGenerator.generateTopics);
          generateBtn.addEventListener('click', () => {
            TopicsGenerator.generateTopicsWithOfflineCheck();
          });
        }
        
        console.log('✅ PHASE 2B: Enhanced Topics Generator fully initialized');
        
      } else {
        console.log(`⏳ PHASE 2B: Waiting for dependencies: ${missing.join(', ')}`);
        setTimeout(waitForDependencies, 100);
      }
    };
    
    waitForDependencies();
  });

  // PHASE 2B: Make globally available with enhanced debugging
  window.TopicsGenerator = TopicsGenerator;
  
  // PHASE 2B: Add global debugging helpers
  window.MKCG_Debug = {
    getTopicsGeneratorStatus: () => ({
      initialized: !!window.TopicsGenerator,
      networkStatus: TopicsGenerator.networkStatus,
      formUtils: !!window.MKCG_FormUtils,
      offlineManager: !!window.MKCG_OfflineManager,
      enhancedUI: !!window.EnhancedUIFeedback,
      enhancedAjax: !!window.EnhancedAjaxManager
    }),
    
    getOfflineStatus: () => {
      return window.MKCG_OfflineManager ? window.MKCG_OfflineManager.getNetworkStatus() : null;
    },
    
    forceSync: () => {
      if (window.MKCG_OfflineManager) {
        window.MKCG_OfflineManager.forcSync();
      }
    },
    
    clearOfflineQueue: () => {
      if (window.MKCG_OfflineManager) {
        window.MKCG_OfflineManager.clearOfflineQueue();
      }
    }
  };
  
  console.log('✅ PHASE 2B: Topics Generator with full offline support loaded successfully');

})();