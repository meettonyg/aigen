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
    // DOM elements mapping (BEM selectors) - FIXED to match template IDs
    elements: {
      toggleBuilder: '#topics-generator-toggle-builder',
      authorityHookBuilder: '#topics-generator-authority-hook-builder',
      whoInput: '#topics-generator-who-input',
      clearWho: '#topics-generator-clear-who',
      resultInput: '#topics-generator-result-input',
      clearResult: '#topics-generator-clear-result',
      whenInput: '#topics-generator-when-input',
      clearWhen: '#topics-generator-clear-when',
      howInput: '#topics-generator-how-input',
      clearHow: '#topics-generator-clear-how',
      addButtons: '.topics-generator__add-button',
      generateButton: '#topics-generator-generate-topics',
      authorityHookText: '#topics-generator-authority-hook-text',
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
        this.updateAuthorityHook();
    } else if (phpData.authorityHook.complete && phpData.authorityHook.complete !== 'I help your audience achieve their goals when they need help through your method.') {
        // Use complete hook only if it's not the default and components are default
        console.log('🔧 Using complete authority hook from database:', phpData.authorityHook.complete);
        this.updateAuthorityHookText(phpData.authorityHook.complete);
    } else {
        // Build from components (fallback)
        console.log('🔧 Building authority hook from default components');
        this.updateAuthorityHook();
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
        this.updateAuthorityHook();
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
     * Update authority hook display with specific text
     */
    updateAuthorityHookText: function(hookText) {
      console.log('🎯 Updating Authority Hook text to:', hookText);
      const hookElement = document.querySelector(this.elements.authorityHookText);
      if (hookElement) {
        hookElement.textContent = hookText;
        console.log('✅ Authority Hook text updated successfully');
      } else {
        console.error('❌ Authority Hook text element not found:', this.elements.authorityHookText);
        // Try fallback selector
        const fallbackElement = document.getElementById('topics-generator-authority-hook-text');
        if (fallbackElement) {
          fallbackElement.textContent = hookText;
          console.log('✅ Authority Hook text updated via fallback');
        }
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
      
      this.updateAuthorityHook();
    },
    
    /**
     * Bind events to DOM elements
     */
    bindEvents: function() {
      // Toggle the Authority Hook Builder
      const toggleBtn = document.querySelector(this.elements.toggleBuilder);
      if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
          this.toggleBuilder();
        });
      }
      
      // Clear inputs
      const clearButtons = [
        { selector: this.elements.clearWho, target: this.elements.whoInput },
        { selector: this.elements.clearResult, target: this.elements.resultInput },
        { selector: this.elements.clearWhen, target: this.elements.whenInput },
        { selector: this.elements.clearHow, target: this.elements.howInput }
      ];
      
      clearButtons.forEach(({ selector, target }) => {
        const btn = document.querySelector(selector);
        if (btn) {
          btn.addEventListener('click', () => {
            const input = document.querySelector(target);
            if (input) {
              input.value = '';
              input.dispatchEvent(new Event('input', { bubbles: true }));
            }
          });
        }
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
            this.updateAuthorityHook();
            
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
     * Auto-save authority hook component to Formidable
     */
    autoSaveAuthorityComponent: function(component, value) {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      if (!entryId || entryId === '0') return;
      
      // Map component names to field IDs (Form 515)
      const componentFieldMap = {
        'who': '10296',
        'result': '10297', 
        'when': '10387',
        'how': '10298'
      };
      
      const fieldId = componentFieldMap[component];
      if (!fieldId) return;
      
      // Debounce the save to avoid too many requests
      clearTimeout(this.componentSaveTimer);
      this.componentSaveTimer = setTimeout(() => {
        this.saveComponentToFormidable(component, fieldId, value);
      }, 1000);
    },
    
    /**
     * Save component to Formidable and update complete authority hook
     */
    saveComponentToFormidable: function(component, fieldId, value) {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      
      // CRITICAL FIX: Use unified nonce for authority hook save
      MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_authority_hook', {
      entry_id: entryId,
      who: this.fields.who,
      result: this.fields.result,
      when: this.fields.when,
      how: this.fields.how,
      nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || document.querySelector(this.elements.nonceField)?.value || ''
      }, {
          onSuccess: (data) => {
            console.log('✅ Authority hook components saved:', data);
          },
          onError: (error) => {
            console.log('⚠️ Failed to save authority hook components:', error);
          }
        });
    },
    
    /**
     * Auto-save field to Formidable (if entry exists)
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
        console.log('\ud83d\udccb Topics Generator: Standalone mode - saving topic', topicId, 'locally only');
      }
      
      // Use MKCG_FormUtils to make AJAX request
      if (window.MKCG_FormUtils) {
        console.log('💾 Auto-saving field:', fieldName, 'with value:', fieldValue);
        
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_topic_field', {
          entry_id: entryId,
          field_name: fieldName,
          field_value: fieldValue,
          nonce: window.topics_vars?.nonce || window.mkcg_vars?.nonce || document.querySelector(this.elements.nonceField)?.value || ''
        }, {
          onSuccess: (response) => {
            console.log('✅ Auto-save successful for field:', fieldName);
            // Visual feedback for successful save
            inputElement.style.borderColor = '#27ae60';
            setTimeout(() => {
              inputElement.style.borderColor = '';
            }, 1000);
          },
          onError: (error) => {
            console.log('❌ Auto-save failed for field:', fieldName, error);
          }
        });
      } else {
        console.log('⚠️ MKCG_FormUtils not available for auto-save');
      }
    },
    
    /**
     * Toggle the Authority Hook Builder visibility
     */
    toggleBuilder: function() {
      console.log('🔄 Toggling Authority Hook Builder');
      const builderEl = document.querySelector(this.elements.authorityHookBuilder);
      const toggleBtn = document.querySelector(this.elements.toggleBuilder);
      
      if (!builderEl) {
        console.error('❌ Builder element not found:', this.elements.authorityHookBuilder);
        return;
      }
      
      if (!toggleBtn) {
        console.error('❌ Toggle button not found:', this.elements.toggleBuilder);
        return;
      }
      
      if (builderEl.classList.contains('topics-generator__builder--hidden')) {
        builderEl.classList.remove('topics-generator__builder--hidden');
        toggleBtn.textContent = 'Hide Builder';
        console.log('✅ Builder shown');
      } else {
        builderEl.classList.add('topics-generator__builder--hidden');
        toggleBtn.textContent = 'Edit Components';
        console.log('✅ Builder hidden');
      }
    },
    
    /**
     * Add an example to a specific field
     */
    addExample: function(field, exampleText) {
      const inputSelector = `#topics-generator-${field}-input`;
      const input = document.querySelector(inputSelector);
      
      if (!input) return;
      
      const currentVal = input.value.trim();
      
      if (currentVal === '') {
        input.value = exampleText;
      } else {
        input.value = currentVal + ', ' + exampleText;
      }
      
      // Update field value and authority hook
      this.fields[field] = input.value;
      this.updateAuthorityHook();
      
      // Trigger input event
      input.dispatchEvent(new Event('input', { bubbles: true }));
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
     * Update the Authority Hook text based on input fields
     */
    updateAuthorityHook: function() {
      const hookText = `I help ${this.fields.who || 'your audience'} ${this.fields.result || 'achieve their goals'} when ${this.fields.when || 'they need help'} ${this.fields.how || 'through your method'}.`;
      const hookElement = document.querySelector(this.elements.authorityHookText);
      if (hookElement) {
        hookElement.textContent = hookText;
      }
      
      // Also save the complete authority hook to Formidable (field 10358)
      this.saveCompleteAuthorityHook(hookText);
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
     * Generate topics with the AI service
     */
    generateTopics: function() {
      const authorityHook = document.querySelector(this.elements.authorityHookText)?.textContent;
      
      if (!authorityHook) {
        alert('Please build your authority hook first.');
        return;
      }
      
      // Show loading indicator
      this.showLoading();
      this.hideTopicsResult();
      
      // Use MKCG_FormUtils to make AJAX request to WordPress
      if (window.MKCG_FormUtils) {
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_generate_topics', {
          authority_hook: authorityHook,
          who: this.fields.who,
          result: this.fields.result,
          when: this.fields.when,
          how: this.fields.how,
          entry_id: document.querySelector(this.elements.entryIdField)?.value,
          nonce: document.querySelector(this.elements.nonceField)?.value
        }, {
          onStart: () => {
            this.showLoading();
          },
          onComplete: () => {
            this.hideLoading();
          },
          onSuccess: (data) => {
            if (data.topics) {
              this.generatedTopics = data.topics;
              this.displayTopics(data.topics);
            } else {
              this.generateDemoTopics(authorityHook);
            }
          },
          onError: () => {
            this.generateDemoTopics(authorityHook);
          }
        });
      } else {
        // Fallback to demo topics
        setTimeout(() => {
          this.hideLoading();
          this.generateDemoTopics(authorityHook);
        }, 2000);
      }
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
     * Use the selected topic in the specified form field
     */
    useTopicInField: function() {
      if (!this.selectedTopic) return;
      
      const fieldNumberInput = document.querySelector(this.elements.fieldNumberInput);
      if (!fieldNumberInput) return;
      
      const fieldNumber = parseInt(fieldNumberInput.value);
      
      if (isNaN(fieldNumber) || fieldNumber < 1 || fieldNumber > 5) {
        alert('Please enter a valid field number (1-5)');
        return;
      }
      
      const fieldSelector = `#topics-generator-topic-field-${fieldNumber}`;
      const inputElement = document.querySelector(fieldSelector);
      
      if (inputElement) {
        inputElement.value = this.selectedTopic.text;
        this.autoSaveField(inputElement);
        
        // STANDALONE MODE: No centralized data manager or cross-generator events
        console.log('\ud83d\udccb Topics Generator: Standalone mode - saving topic', fieldNumber, 'locally only');
        
        // Topics Generator works independently - no cross-generator broadcasting needed
      }
      
      this.closeModal();
    }
  };

  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Topics Generator: DOM Ready - BEM Version');
    
    // Wait for MKCG_FormUtils to be available
    const waitForFormUtils = () => {
      if (window.MKCG_FormUtils) {
        console.log('✅ Topics Generator: FormUtils detected - Starting BEM initialization');
        TopicsGenerator.init();
      } else {
        setTimeout(waitForFormUtils, 100);
      }
    };
    
    waitForFormUtils();
  });

  // Make globally available for debugging
  window.TopicsGenerator = TopicsGenerator;

})();