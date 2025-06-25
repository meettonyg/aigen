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
    // DOM elements mapping (BEM selectors)
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
      modalCancelButton: '#topics-generator-modal-cancel'
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
      console.log('🎯 Topics Generator: Initializing with BEM methodology');
      
      // Load existing data FIRST before doing anything else
      this.loadExistingData();
      
      this.bindEvents();
      // Don't call updateAuthorityHook() here - let loadExistingData() handle it
    },
    
    /**
     * Load existing data from PHP (prioritize over all other sources)
     */
    loadExistingData: function() {
      console.log('📝 Loading existing data from PHP...');
      
      // Check if PHP data was passed to JavaScript
      if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.hasEntry) {
        console.log('✅ Found existing data:', window.MKCG_Topics_Data);
        
        // Load authority hook components
        const authorityHook = window.MKCG_Topics_Data.authorityHook;
        if (authorityHook) {
          this.fields.who = authorityHook.who || '';
          this.fields.result = authorityHook.result || '';
          this.fields.when = authorityHook.when || '';
          this.fields.how = authorityHook.how || '';
          
          // Update the input fields
          this.updateInputFields();
          
          // Always build from components if we have component data (components take precedence)
          const hasComponentData = this.fields.who || this.fields.result || this.fields.when || this.fields.how;
          
          if (hasComponentData) {
            console.log('✅ Building authority hook from components:', this.fields);
            this.updateAuthorityHook();
          } else if (authorityHook.complete) {
            console.log('✅ Using stored complete authority hook:', authorityHook.complete);
            this.updateAuthorityHookText(authorityHook.complete);
          } else {
            console.log('⚠️ No authority hook data found, using defaults');
            this.updateAuthorityHook();
          }
        }
        
        // Load existing topics
        const topics = window.MKCG_Topics_Data.topics;
        if (topics) {
          Object.keys(topics).forEach(key => {
            if (topics[key]) {
              const fieldNum = key.split('_')[1];
              const field = document.querySelector(`#topics-generator-topic-field-${fieldNum}`);
              if (field) {
                field.value = topics[key];
              }
            }
          });
        }
      } else {
        console.log('⚠️ No existing data found, using defaults');
        // Set fallback defaults only if no PHP data
        this.fields.who = this.fields.who || 'your audience';
        this.fields.result = this.fields.result || 'achieve their goals';
        this.fields.when = this.fields.when || 'they need help';
        this.fields.how = this.fields.how || 'through your method';
        this.updateAuthorityHook();
      }
    },
    
    /**
     * Update input fields with current field values
     */
    updateInputFields: function() {
      const fieldMappings = [
        { field: 'who', selector: '#topics-generator-who-input' },
        { field: 'result', selector: '#topics-generator-result-input' },
        { field: 'when', selector: '#topics-generator-when-input' },
        { field: 'how', selector: '#topics-generator-how-input' }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input && this.fields[field]) {
          input.value = this.fields[field];
        }
      });
    },
    
    /**
     * Update authority hook display with specific text
     */
    updateAuthorityHookText: function(hookText) {
      const hookElement = document.querySelector(this.elements.authorityHookText);
      if (hookElement) {
        hookElement.textContent = hookText;
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
            nonce: document.querySelector('#topics-generator-topics-nonce')?.value
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
      
      // Use our AJAX handlers to save the component
      if (window.MKCG_FormUtils) {
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_update_authority_hook', {
          entry_id: entryId,
          who: this.fields.who,
          result: this.fields.result,
          when: this.fields.when,
          how: this.fields.how,
          nonce: window.topics_vars?.topics_nonce || ''
        }, {
          onSuccess: (data) => {
            console.log('✅ Authority hook components saved:', data);
          },
          onError: (error) => {
            console.log('⚠️ Failed to save authority hook components:', error);
          }
        });
      }
    },
    
    /**
     * Auto-save field to Formidable (if entry exists)
     */
    autoSaveField: function(inputElement) {
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      if (!entryId || entryId === '0') return;
      
      const fieldName = inputElement.getAttribute('name');
      const fieldValue = inputElement.value;
      
      if (!fieldName || !fieldValue.trim()) return;
      
      // Use MKCG_FormUtils to make AJAX request
      if (window.MKCG_FormUtils) {
        MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_topic_field', {
          entry_id: entryId,
          field_name: fieldName,
          field_value: fieldValue,
          nonce: document.querySelector('#topics-generator-topics-nonce')?.value
        }, {
          onSuccess: (response) => {
            // Visual feedback for successful save
            inputElement.style.borderColor = '#27ae60';
            setTimeout(() => {
              inputElement.style.borderColor = '';
            }, 1000);
          },
          onError: () => {
            console.log('Auto-save failed for field:', fieldName);
          }
        });
      }
    },
    
    /**
     * Toggle the Authority Hook Builder visibility
     */
    toggleBuilder: function() {
      const builderEl = document.querySelector(this.elements.authorityHookBuilder);
      const toggleBtn = document.querySelector(this.elements.toggleBuilder);
      
      if (!builderEl || !toggleBtn) return;
      
      if (builderEl.classList.contains('topics-generator__builder--hidden')) {
        builderEl.classList.remove('topics-generator__builder--hidden');
        toggleBtn.textContent = 'Hide Builder';
      } else {
        builderEl.classList.add('topics-generator__builder--hidden');
        toggleBtn.textContent = 'Edit Components';
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
      const entryId = document.querySelector('#topics-generator-entry-id')?.value;
      if (!entryId || entryId === '0') return;
      
      // Debounce the save
      clearTimeout(this.hookSaveTimer);
      this.hookSaveTimer = setTimeout(() => {
        if (window.MKCG_FormUtils) {
          MKCG_FormUtils.wp.makeAjaxRequest('mkcg_save_field', {
            entry_id: entryId,
            field_id: '10358',
            value: hookText,
            nonce: window.topics_vars?.topics_nonce || ''
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
          entry_id: document.querySelector('#topics-generator-entry-id')?.value,
          nonce: document.querySelector('#topics-generator-topics-nonce')?.value
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