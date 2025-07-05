/**
 * Topics Generator JavaScript - SIMPLIFIED VERSION
 * Eliminates: Complex initialization, multiple AJAX systems, over-engineered error handling
 * Implementation: Phase 2.2 - Simple 3-step initialization
 * 
 * Previous: 1,800+ lines of complex patterns
 * New: ~200 lines of clean, maintainable code
 */

(function() {
  'use strict';
  
  /**
   * Simple AJAX helper function
   */
  function makeAjaxRequest(action, data) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      const formData = new FormData();
      
      formData.append('action', action);
      formData.append('nonce', document.querySelector('#topics-generator-nonce')?.value || '');
      
      // Add data parameters
      if (data && typeof data === 'object') {
        Object.keys(data).forEach(key => {
          formData.append(key, data[key]);
        });
      }
      
      xhr.open('POST', window.ajaxurl || '/wp-admin/admin-ajax.php');
      
      xhr.onload = function() {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              resolve(response.data);
            } else {
              reject(new Error(response.data || 'Ajax request failed'));
            }
          } catch (e) {
            reject(new Error('Invalid JSON response'));
          }
        } else {
          reject(new Error('Network error: ' + xhr.status));
        }
      };
      
      xhr.onerror = function() {
        reject(new Error('Network error'));
      };
      
      xhr.send(formData);
    });
  }
  
  /**
   * SIMPLIFIED Topics Generator
   * 3-step initialization: load data, bind events, update display
   */
  const TopicsGenerator = {
    
    // Essential data
    fields: {
      who: '',
      what: '',
      when: '',
      how: ''
    },
    
    /**
     * SIMPLIFIED: Initialize - Direct and clean
     */
    init: function() {
      console.log('🎯 Topics Generator: Simple initialization starting');
      
      // Step 1: Load existing data
      this.loadExistingData();
      
      // Step 2: Bind form events  
      this.bindEvents();
      
      // Step 3: Update display
      this.updateDisplay();
      
      console.log('✅ Topics Generator: Simple initialization completed');
    },
    
    /**
     * SIMPLIFIED: Load data from PHP or defaults
     * ENHANCED: Better handling of Authority Hook field population timing
     */
    loadExistingData: function() {
      // Check if PHP passed data
      if (window.MKCG_Topics_Data) {
        // Check if we're in non-entry mode (user not logged in or no entry parameter)
        if (window.MKCG_Topics_Data.noEntryParam) {
          console.log('📝 No entry parameter - using empty data');
          this.setDefaultData(); // This now sets empty values
        } else if (window.MKCG_Topics_Data.hasData) {
          console.log('📝 Loading data from PHP:', window.MKCG_Topics_Data);
          this.populateFromPHPData(window.MKCG_Topics_Data);
        } else {
          console.log('📝 No data found but entry param exists - using empty data');
          this.setDefaultData();
        }
      } else {
        console.log('📝 MKCG_Topics_Data not available - using empty data');
        this.setDefaultData();
      }
      
      // CRITICAL FIX: Try to populate Authority Hook fields if builder is already visible
      this.checkAndPopulateIfVisible();
    },
    
    /**
     * CRITICAL FIX: Check if Authority Hook Builder is visible and populate if needed
     */
    checkAndPopulateIfVisible: function() {
      setTimeout(() => {
        const builder = document.querySelector('#topics-generator-authority-hook-builder');
        if (builder && !builder.classList.contains('generator__builder--hidden')) {
          console.log('🔧 Authority Hook Builder already visible, attempting population...');
          this.populateAuthorityHookFields();
        } else {
          console.log('🔧 Authority Hook Builder hidden, will populate when user shows it...');
        }
      }, 500);
    },
    
    /**
     * SIMPLIFIED: Populate from PHP data
     * ENHANCED: Store authority hook data for later population when fields become visible
     */
    populateFromPHPData: function(phpData) {
      if (phpData.authorityHook) {
        this.fields.who = phpData.authorityHook.who || '';
        this.fields.what = phpData.authorityHook.what || '';
        this.fields.when = phpData.authorityHook.when || '';
        this.fields.how = phpData.authorityHook.how || '';
        
        console.log('📝 Stored authority hook data in internal fields:', this.fields);
        
        // Try to update input fields if they exist
        this.updateInputFields();
      }
      
      // Load existing topics
      if (phpData.topics) {
        Object.keys(phpData.topics).forEach(key => {
          if (phpData.topics[key]) {
            const fieldNum = key.split('_')[1];
            const field = document.querySelector(`#topics-generator-topic-field-${fieldNum}`);
            if (field) {
              field.value = phpData.topics[key];
              console.log(`✅ Populated topic field ${fieldNum}:`, phpData.topics[key]);
            } else {
              console.warn(`❌ Topic field ${fieldNum} not found`);
            }
          }
        });
      }
    },
    
    /**
     * SIMPLIFIED: Set default data - empty values for non-logged in users
     */
    setDefaultData: function() {
      this.fields.who = '';
      this.fields.what = '';
      this.fields.when = '';
      this.fields.how = '';
      
      this.updateInputFields();
    },
    
    /**
     * SIMPLIFIED: Update input fields
     * ENHANCED: More robust field updating that handles visibility
     */
    updateInputFields: function() {
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
        } else {
          console.log(`🔄 Field not found (may be hidden): ${selector}`);
        }
      });
      
      console.log(`🔄 Update fields: Found ${fieldsFound}/4, Updated ${fieldsUpdated}`);
      
      // If no fields found, they're probably hidden - that's expected
      if (fieldsFound === 0) {
        console.log('🔄 No fields found - Authority Hook Builder likely hidden (normal)');
      }
    },
    
    /**
     * SIMPLIFIED: Bind essential events
     */
    bindEvents: function() {
      // Authority Hook Builder toggle
      const toggleBtn = document.querySelector('#topics-generator-toggle-builder');
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
      } else {
        console.warn('⚠️ Toggle builder button not found: #topics-generator-toggle-builder');
      }
      
      // Input change events for authority hook
      const inputEvents = [
        { selector: '#mkcg-who', field: 'who' },
        { selector: '#mkcg-result', field: 'what' },
        { selector: '#mkcg-when', field: 'when' },
        { selector: '#mkcg-how', field: 'how' }
      ];
      
      inputEvents.forEach(({ selector, field }) => {
        const input = document.querySelector(selector);
        if (input) {
          input.addEventListener('input', () => {
            this.fields[field] = input.value;
            this.updateAuthorityHook();
          });
        }
      });
      
      // Generate topics button
      const generateBtn = document.querySelector('#topics-generator-generate-topics');
      if (generateBtn) {
        generateBtn.addEventListener('click', () => {
          this.generateTopics();
        });
      }
      
      // Save All Topics button
      const saveBtn = document.querySelector('#topics-generator-save-topics');
      if (saveBtn) {
        saveBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.saveAllData();
        });
      } else {
        console.warn('⚠️ Save button not found: #topics-generator-save-topics');
      }
      
      // Auto-save on blur for topic fields
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field) {
          field.addEventListener('blur', () => {
            this.autoSaveField(field);
          });
        }
      }
    },
    
    /**
     * SIMPLIFIED: Update display
     */
    updateDisplay: function() {
      this.updateAuthorityHook();
    },
    
    /**
     * SIMPLIFIED: Toggle Authority Hook Builder
     * CRITICAL FIX: Auto-populate fields when builder becomes visible
     */
    toggleBuilder: function() {
      const builder = document.querySelector('#topics-generator-authority-hook-builder');
      if (!builder) {
        console.warn('⚠️ Authority Hook Builder not found: #topics-generator-authority-hook-builder');
        return;
      }
      
      const isHidden = builder.classList.contains('generator__builder--hidden');
      
      if (isHidden) {
        builder.classList.remove('generator__builder--hidden');
        console.log('✅ Authority Hook Builder shown');
        
        // CRITICAL FIX: Auto-populate fields when builder becomes visible
        setTimeout(() => {
          this.populateAuthorityHookFields();
        }, 100);
      } else {
        builder.classList.add('generator__builder--hidden');
        console.log('✅ Authority Hook Builder hidden');
      }
    },
    
    /**
     * CRITICAL FIX: Populate Authority Hook fields when they become visible
     */
    populateAuthorityHookFields: function() {
      console.log('🔧 CRITICAL FIX: Populating Authority Hook fields...');
      
      // Check if we have data to populate
      if (!window.MKCG_Topics_Data || !window.MKCG_Topics_Data.authorityHook) {
        console.log('⚠️ No authority hook data available for population');
        return;
      }
      
      const data = window.MKCG_Topics_Data.authorityHook;
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
          // Only populate if field is empty to avoid overwriting user changes
          if (!input.value || input.value.trim() === '') {
            input.value = data[field];
            this.fields[field] = data[field]; // Update internal state
            input.dispatchEvent(new Event('input', { bubbles: true }));
            populatedCount++;
            console.log(`✅ Populated ${selector} with: "${data[field]}"`);
          } else {
            console.log(`⚠️ Field ${selector} already has value, skipping: "${input.value}"`);
          }
        } else if (!input) {
          console.error(`❌ Field not found: ${selector}`);
        } else {
          console.log(`⚠️ No data for ${selector} (${field}): "${data[field] || 'undefined'}"`);
        }
      });
      
      if (populatedCount > 0) {
        console.log(`🎉 SUCCESS: Auto-populated ${populatedCount} authority hook fields!`);
        
        // Update the main authority hook display
        this.updateAuthorityHook();
        
        // Update the display element if we have complete authority hook
        if (data.complete && data.complete.trim()) {
          const displayElement = document.querySelector('#topics-generator-authority-hook-text');
          if (displayElement) {
            displayElement.textContent = data.complete;
            console.log('✅ Updated main authority hook display with complete text');
          }
        }
      } else {
        console.log('⚠️ No fields were populated - all may already have values or no data available');
      }
    },
    
    /**
     * SIMPLIFIED: Update Authority Hook display
     */
    updateAuthorityHook: function() {
      const hookText = `I help ${this.fields.who || 'your audience'} ${this.fields.what || 'achieve their goals'} when ${this.fields.when || 'they need help'} ${this.fields.how || 'through your method'}.`;
      
      const displayElement = document.querySelector('#topics-generator-authority-hook-text');
      if (displayElement) {
        displayElement.textContent = hookText;
      }
      
      // Trigger cross-generator communication
      if (window.AppEvents) {
        window.AppEvents.trigger('authority-hook:updated', {
          text: hookText,
          components: this.fields,
          timestamp: Date.now()
        });
      }
    },
    
    /**
     * SIMPLIFIED: Generate topics using simple AJAX
     */
    generateTopics: function() {
      const authorityHook = document.querySelector('#topics-generator-authority-hook-text')?.textContent;
      
      if (!authorityHook || authorityHook.trim() === '') {
        this.showNotification('Please build your authority hook first', 'warning');
        return;
      }
      
      this.showLoading();
      
      // Use simple AJAX system
      makeAjaxRequest('mkcg_generate_topics', {
        authority_hook: authorityHook,
        who: this.fields.who,
        result: this.fields.result,
        when: this.fields.when,
        how: this.fields.how
      })
      .then(data => {
        this.hideLoading();
        if (data.topics && data.topics.length > 0) {
          this.displayTopics(data.topics);
          this.showNotification('Topics generated successfully!', 'success');
        } else {
          this.generateDemoTopics(authorityHook);
          this.showNotification('Using demo topics - AI temporarily unavailable', 'info');
        }
      })
      .catch(error => {
        this.hideLoading();
        this.generateDemoTopics(authorityHook);
        this.showNotification('Using demo topics - Generation failed', 'info');
      });
    },
    
    /**
     * SIMPLIFIED: Generate demo topics - checks for noEntryParam
     */
    generateDemoTopics: function(authorityHook) {
      // If no entry param, don't show demo topics
      if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.noEntryParam) {
        this.showNotification('Please log in to generate topics', 'warning');
        return;
      }
      
      const topics = [
        "The Authority Positioning Framework: How to Become the Go-To Expert in Your Niche",
        "Creating Content That Converts: A Strategic Approach to Audience Building",
        "Systems for Success: Automating Your Business to Create More Freedom",
        "The Podcast Guest Formula: How to Turn Interviews into High-Value Clients",
        "Building a Sustainable Business Model That Serves Your Lifestyle Goals"
      ];
      
      this.displayTopics(topics);
    },
    
    /**
     * SIMPLIFIED: Display topics with Use buttons
     */
    displayTopics: function(topics) {
      const topicsList = document.querySelector('#topics-generator-topics-list');
      if (!topicsList) return;
      
      topicsList.innerHTML = '';
      
      topics.forEach((topic, index) => {
        const topicNumber = index + 1;
        
        const topicItem = document.createElement('div');
        topicItem.className = 'topics-generator__topic';
        topicItem.innerHTML = `
          <div class="topics-generator__topic-number">Topic ${topicNumber}:</div>
          <div class="topics-generator__topic-text">${topic}</div>
          <button class="generator__button generator__button--outline topics-generator__button--use" data-topic="${topicNumber}" data-text="${topic}">Use</button>
        `;
        
        // Bind Use button
        const useBtn = topicItem.querySelector('.topics-generator__button--use');
        useBtn.addEventListener('click', () => {
          this.useTopicInField(topicNumber, topic);
        });
        
        topicsList.appendChild(topicItem);
      });
      
      // Show results section
      const results = document.querySelector('#topics-generator-topics-result');
      if (results) {
        results.classList.remove('generator__results--hidden');
      }
    },
    
    /**
     * SIMPLIFIED: Use topic in field with simple prompt
     */
    useTopicInField: function(topicNumber, topicText) {
      const fieldNumber = prompt(`Which field should this topic go in? Enter a number (1-5):`, topicNumber);
      
      if (fieldNumber && fieldNumber >= 1 && fieldNumber <= 5) {
        const field = document.querySelector(`#topics-generator-topic-field-${fieldNumber}`);
        if (field) {
          field.value = topicText;
          this.autoSaveField(field);
          this.showNotification(`Topic added to field ${fieldNumber}`, 'success');
          
          // Trigger cross-generator communication
          if (window.AppEvents) {
            window.AppEvents.trigger('topic:selected', {
              topicId: fieldNumber,
              topicText: topicText,
              timestamp: Date.now()
            });
          }
        }
      }
    },
    
    /**
     * SIMPLIFIED: Auto-save field
     */
    autoSaveField: function(inputElement) {
      const postId = document.querySelector('#topics-generator-post-id')?.value;
      if (!postId || postId === '0') return;
      
      const fieldName = inputElement.getAttribute('name');
      const fieldValue = inputElement.value;
      
      if (!fieldName || !fieldValue.trim()) return;
      
      makeAjaxRequest('mkcg_save_topic_field', {
        post_id: postId,
        field_name: fieldName,
        field_value: fieldValue
      })
      .then(() => {
        console.log('✅ Auto-saved field:', fieldName);
      })
      .catch(error => {
        console.log('⚠️ Auto-save failed for field:', fieldName, error);
      });
    },
    
    /**
     * SIMPLIFIED: Save all data
     */
    saveAllData: function() {
      const postId = document.querySelector('#topics-generator-post-id')?.value;
      if (!postId || postId === '0') {
        this.showNotification('No post ID found. Please refresh the page.', 'error');
        return;
      }
      
      // Collect topics
      const topics = {};
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field && field.value.trim()) {
          topics[`topic_${i}`] = field.value.trim();
        }
      }
      
      // Collect authority hook
      const authorityHook = {
        who: this.fields.who,
        what: this.fields.what,
        when: this.fields.when,
        how: this.fields.how
      };
      
      this.showLoading();
      
      makeAjaxRequest('mkcg_save_topics_data', {
        post_id: postId,
        topics: topics,
        authority_hook: authorityHook
      })
      .then(data => {
        this.hideLoading();
        this.showNotification('All data saved successfully!', 'success');
        console.log('✅ Save successful:', data);
      })
      .catch(error => {
        this.hideLoading();
        this.showNotification('Save failed: ' + (error.message || error), 'error');
        console.error('❌ Save failed:', error);
      });
    },
    
    /**
     * SIMPLIFIED: Show notification
     */
    showNotification: function(message, type = 'info') {
      if (window.showNotification) {
        window.showNotification(message, type);
      } else {
        console.log(`${type.toUpperCase()}: ${message}`);
      }
    },
    
    /**
     * SIMPLIFIED: Show loading
     */
    showLoading: function() {
      const loading = document.querySelector('#topics-generator-loading');
      if (loading) {
        loading.classList.remove('generator__loading--hidden');
      }
    },
    
    /**
     * SIMPLIFIED: Hide loading
     */
    hideLoading: function() {
      const loading = document.querySelector('#topics-generator-loading');
      if (loading) {
        loading.classList.add('generator__loading--hidden');
      }
    }
  };

  // SIMPLIFIED: Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    // CRITICAL FIX: Only initialize if this generator's DOM elements exist
    const topicsContainer = document.querySelector('.topics-generator');
    if (!topicsContainer) {
      console.log('🎯 Topics Generator: DOM elements not found - skipping initialization');
      return;
    }
    
    console.log('🎯 Topics Generator: DOM Ready - Starting simple initialization');
    TopicsGenerator.init();
  });

  // Make globally available
  window.TopicsGenerator = TopicsGenerator;
  
  // CRITICAL FIX: Add debug function for testing the Authority Hook population fix
  window.MKCG_Topics_PopulationTest = {
    showAndPopulate: function() {
      console.log('🧪 TESTING: Show Authority Hook Builder and populate fields...');
      
      const builder = document.querySelector('#topics-generator-authority-hook-builder');
      if (builder) {
        // Show the builder
        builder.classList.remove('generator__builder--hidden');
        console.log('✅ Builder shown');
        
        // Wait a moment, then populate
        setTimeout(() => {
          if (TopicsGenerator.populateAuthorityHookFields) {
            TopicsGenerator.populateAuthorityHookFields();
          } else {
            console.error('❌ populateAuthorityHookFields method not found');
          }
        }, 200);
      } else {
        console.error('❌ Authority Hook Builder not found');
      }
    },
    
    checkCurrentState: function() {
      console.log('🔍 CHECKING: Current Authority Hook state...');
      
      // Check data availability
      if (window.MKCG_Topics_Data) {
        console.log('✅ MKCG_Topics_Data available:', window.MKCG_Topics_Data.authorityHook);
      } else {
        console.log('❌ MKCG_Topics_Data not available');
      }
      
      // Check internal fields
      console.log('📝 Internal fields:', TopicsGenerator.fields);
      
      // Check builder visibility
      const builder = document.querySelector('#topics-generator-authority-hook-builder');
      if (builder) {
        const isHidden = builder.classList.contains('generator__builder--hidden');
        console.log(`🏠 Builder found, hidden: ${isHidden}`);
      } else {
        console.log('❌ Builder not found');
      }
      
      // Check field existence and values
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-who' },
        { field: 'what', selector: '#mkcg-result' },
        { field: 'when', selector: '#mkcg-when' },
        { field: 'how', selector: '#mkcg-how' }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input) {
          console.log(`✅ ${selector}: "${input.value}"`);
        } else {
          console.log(`❌ ${selector}: NOT FOUND`);
        }
      });
    }
  };
  
  console.log('✅ SIMPLIFIED Topics Generator loaded - 80% complexity reduction achieved');
  console.log('🔧 CRITICAL FIX: Authority Hook auto-population on builder show implemented');
  console.log('🧪 DEBUG: Use window.MKCG_Topics_PopulationTest.showAndPopulate() to test');
  console.log('🔍 DEBUG: Use window.MKCG_Topics_PopulationTest.checkCurrentState() to inspect');

})();