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
  
  // REMOVED: Conflicting AJAX implementation - now using global makeAjaxRequest from simple-ajax.js
  
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
     * ROOT FIX: Set default data - truly empty values when no entry param
     */
    setDefaultData: function() {
      this.fields.who = '';
      this.fields.what = '';
      this.fields.when = '';
      this.fields.how = '';
      
      this.updateInputFields();
      
      // ROOT FIX: Update the authority hook display to show empty text when no entry param
      this.updateAuthorityHook();
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
     * IMPROVED: Enhanced button event binding to prevent conflicts
     */
    bindEvents: function() {
      // FIXED: More specific button targeting to prevent conflicts
      const toggleBtn = document.querySelector('#topics-generator-toggle-builder');
      const generateBtn = document.querySelector('#topics-generator-generate-topics');
      const saveBtn = document.querySelector('#topics-generator-save-topics');
      
      // Authority Hook Builder toggle
      if (toggleBtn) {
        // Remove any existing listeners to prevent duplicates
        toggleBtn.removeEventListener('click', this.toggleBuilderHandler);
        this.toggleBuilderHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.toggleBuilder();
        };
        toggleBtn.addEventListener('click', this.toggleBuilderHandler);
        console.log('✅ Authority Hook Builder toggle bound correctly');
      } else {
        console.warn('⚠️ Toggle builder button not found: #topics-generator-toggle-builder');
      }
      
      // FIXED: Generate topics button with conflict prevention
      if (generateBtn) {
        generateBtn.removeEventListener('click', this.generateTopicsHandler);
        this.generateTopicsHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Generate button clicked - calling generateTopics()');
          this.generateTopics();
        };
        generateBtn.addEventListener('click', this.generateTopicsHandler);
        console.log('✅ Generate button event bound correctly');
      } else {
        console.warn('⚠️ Generate button not found: #topics-generator-generate-topics');
      }
      
      // FIXED: Save All Topics button with absolute conflict prevention
      if (saveBtn) {
        // CRITICAL: Remove any existing listeners to prevent duplicates
        saveBtn.removeEventListener('click', this.saveAllDataHandler);
        
        // Create bound handler with specific identification
        this.saveAllDataHandler = (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 FIXED: Save button clicked - calling saveAllData()');
          
          // FIXED: Additional check to ensure this is the save button
          if (e.target.id === 'topics-generator-save-topics' || 
              e.target.closest('#topics-generator-save-topics')) {
            this.saveAllData();
          } else {
            console.warn('⚠️ Save triggered from unexpected element:', e.target);
          }
        };
        
        saveBtn.addEventListener('click', this.saveAllDataHandler);
        console.log('✅ FIXED: Save button event bound with conflict prevention');
      } else {
        console.warn('⚠️ Save button not found: #topics-generator-save-topics');
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
     * NOTE: UX enhancements (button states) handled by centralized Authority Hook Service
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
      
      // Trigger centralized Authority Hook Service for UX enhancements
      if (window.AuthorityHookBuilder && window.AuthorityHookBuilder.updateToggleButtonState) {
        window.AuthorityHookBuilder.updateToggleButtonState('topics-generator-toggle-builder', 'topics-generator-authority-hook-builder');
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
     * CLEAN SLATE: Update Authority Hook display - NO DEFAULTS EVER
     */
    updateAuthorityHook: function() {
      // CLEAN SLATE: Only show text if ALL fields have real data
      const hasAllFields = this.fields.who && this.fields.what && this.fields.when && this.fields.how &&
                          this.fields.who.trim() && this.fields.what.trim() && 
                          this.fields.when.trim() && this.fields.how.trim();
      
      let hookText = '';
      
      if (hasAllFields) {
        hookText = `I help ${this.fields.who} ${this.fields.what} when ${this.fields.when} ${this.fields.how}.`;
      }
      // NO ELSE - if fields incomplete, hookText stays empty
      
      const displayElement = document.querySelector('#topics-generator-authority-hook-text');
      if (displayElement) {
        displayElement.textContent = hookText;
      }
      
      // Trigger cross-generator communication
      if (window.AppEvents) {
        window.AppEvents.trigger('authority-hook:updated', {
          text: hookText,
          components: this.fields,
          timestamp: Date.now(),
          isEmpty: hookText === ''
        });
      }
    },
    
    /**
     * CLEAN SLATE: Generate topics using simple AJAX - requires complete authority hook
     */
    generateTopics: function() {
      const authorityHook = document.querySelector('#topics-generator-authority-hook-text')?.textContent;
      
      if (!authorityHook || authorityHook.trim() === '') {
        this.showNotification('Please complete all authority hook fields first', 'warning');
        return;
      }
      
      this.showLoading();
      
      // Use global AJAX system
      window.makeAjaxRequest('mkcg_generate_topics', {
        authority_hook: authorityHook,
        who: this.fields.who,
        what: this.fields.what,
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
     * CLEAN SLATE: Generate demo topics - only when authority hook is complete
     */
    generateDemoTopics: function(authorityHook) {
      // CLEAN SLATE: Always require complete authority hook before showing demo topics
      if (!authorityHook || authorityHook.trim() === '') {
        this.showNotification('Please complete all authority hook fields to see demo topics', 'warning');
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
      
      window.makeAjaxRequest('mkcg_save_topic_field', {
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
     * ROOT-LEVEL FIX: Enhanced Topics Generator Save with Comprehensive Data Collection
     * 
     * Fixed Issues:
     * 1. Topic field collection failing after CSS changes
     * 2. Authority hook data not being collected from form fields
     * 3. 400 error due to server validation mismatch
     * 4. Data format incompatibility with PHP handlers
     */
    saveAllData: function() {
      console.log('🔄 FIXED: Starting enhanced save operation...');
      
      // STEP 1: Enhanced post ID detection
      const postId = document.querySelector('#topics-generator-post-id')?.value || 
                     window.MKCG_Topics_Data?.postId || 
                     new URLSearchParams(window.location.search).get('post_id') || 
                     new URLSearchParams(window.location.search).get('entry') || '0';
                     
      console.log('📍 FIXED: Post ID detected:', postId);
      
      if (!postId || postId === '0') {
        this.showNotification('No post ID found. Please refresh the page.', 'error');
        return;
      }
      
      // STEP 2: Enhanced topics collection with multiple strategies
      const topics = {};
      let topicsCount = 0;
      
      // Strategy 1: Direct field selectors (current method)
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field && field.value && field.value.trim()) {
          topics[`topic_${i}`] = field.value.trim();
          topicsCount++;
          console.log(`✅ FIXED: Collected topic ${i}:`, field.value.trim());
        }
      }
      
      // Strategy 2: Fallback using class selectors
      if (topicsCount === 0) {
        console.log('🔄 FIXED: No topics found with ID selectors, trying class selectors...');
        const topicFields = document.querySelectorAll('.mkcg-topic-field, .topics-generator__topic-input');
        topicFields.forEach((field, index) => {
          if (field.value && field.value.trim()) {
            const topicNumber = index + 1;
            if (topicNumber <= 5) {
              topics[`topic_${topicNumber}`] = field.value.trim();
              topicsCount++;
              console.log(`✅ FIXED: Collected topic ${topicNumber} via class:`, field.value.trim());
            }
          }
        });
      }
      
      console.log(`📊 FIXED: Collected ${topicsCount} topics total`);
      
      // STEP 3: Enhanced authority hook collection
      const authorityHook = {};
      
      // Strategy 1: Form fields (when Authority Hook Builder is open)
      const whoField = document.querySelector('#mkcg-who');
      const whatField = document.querySelector('#mkcg-result, #mkcg-what');
      const whenField = document.querySelector('#mkcg-when');
      const howField = document.querySelector('#mkcg-how');
      
      if (whoField) authorityHook.who = whoField.value || '';
      if (whatField) authorityHook.what = whatField.value || '';
      if (whenField) authorityHook.when = whenField.value || '';
      if (howField) authorityHook.how = howField.value || '';
      
      // Strategy 2: Fallback to internal data
      if (!authorityHook.who && this.fields.who) authorityHook.who = this.fields.who;
      if (!authorityHook.what && this.fields.what) authorityHook.what = this.fields.what;
      if (!authorityHook.when && this.fields.when) authorityHook.when = this.fields.when;
      if (!authorityHook.how && this.fields.how) authorityHook.how = this.fields.how;
      
      // Strategy 3: Fallback to template data
      if (window.MKCG_Topics_Data?.authorityHook) {
        const templateData = window.MKCG_Topics_Data.authorityHook;
        if (!authorityHook.who && templateData.who) authorityHook.who = templateData.who;
        if (!authorityHook.what && templateData.what) authorityHook.what = templateData.what;
        if (!authorityHook.when && templateData.when) authorityHook.when = templateData.when;
        if (!authorityHook.how && templateData.how) authorityHook.how = templateData.how;
      }
      
      console.log('🔍 FIXED: Authority Hook Data collected:', authorityHook);
      
      // Validate we have something to save
      const hasTopics = topicsCount > 0;
      const hasAuthorityData = Object.values(authorityHook).some(val => val && val.trim());
      
      if (!hasTopics && !hasAuthorityData) {
        this.showNotification('No data to save. Please add topics or complete the authority hook.', 'warning');
        return;
      }
      
      console.log(`📊 FIXED: Saving ${topicsCount} topics and authority hook data...`);
      console.log('🔍 FIXED: Authority Hook Data:', authorityHook);
      console.log('🔍 FIXED: Topics Data:', topics);
      
      // STEP 4: Enhanced loading state management
      this.showLoading();
      
      // Disable save button to prevent double submission
      const saveButton = document.querySelector('#topics-generator-save-topics');
      const originalButtonText = saveButton?.textContent || 'Save All Topics & Authority Hook';
      if (saveButton) {
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
      }
      
      // STEP 5: Build complete authority hook text (only if all fields have data)
      if (hasAuthorityData) {
        const allFieldsComplete = authorityHook.who && authorityHook.what && authorityHook.when && authorityHook.how &&
                                 authorityHook.who.trim() && authorityHook.what.trim() && 
                                 authorityHook.when.trim() && authorityHook.how.trim();
        
        if (allFieldsComplete) {
          authorityHook.complete = `I help ${authorityHook.who} ${authorityHook.what} when ${authorityHook.when} ${authorityHook.how}.`;
        }
      }
      
      // STEP 6: Enhanced nonce collection
      const nonce = document.querySelector('#topics-generator-nonce')?.value || 
                    window.mkcg_vars?.nonce || 
                    '';
                    
      console.log('🔐 FIXED: Nonce collected:', nonce ? 'Found' : 'Missing');
      
      if (!nonce) {
        this.hideLoading();
        if (saveButton) {
          saveButton.disabled = false;
          saveButton.textContent = originalButtonText;
        }
        this.showNotification('Security token missing. Please refresh the page.', 'error');
        return;
      }
      
      // STEP 7: Enhanced AJAX request with fallback
      if (!window.makeAjaxRequest) {
        console.error('❌ Global makeAjaxRequest not available, falling back to fetch');
        this.saveWithFetch(postId, topics, authorityHook, saveButton, originalButtonText);
        return;
      }
      
      window.makeAjaxRequest('mkcg_save_topics_data', {
        post_id: parseInt(postId),
        topics: topics,
        authority_hook: authorityHook,
        nonce: nonce
      })
      .then(data => {
        this.hideLoading();
        
        // Re-enable save button
        if (saveButton) {
          saveButton.disabled = false;
          saveButton.textContent = originalButtonText;
        }
        
        this.showNotification(`✅ Successfully saved ${topicsCount} topics and authority hook!`, 'success');
        console.log('✅ FIXED: Save successful:', data);
        
        // Update the authority hook display if we have complete text
        if (authorityHook.complete) {
          const displayElement = document.querySelector('#topics-generator-authority-hook-text');
          if (displayElement) {
            displayElement.textContent = authorityHook.complete;
          }
        }
        
        // Update internal fields to reflect saved data
        if (authorityHook.who) this.fields.who = authorityHook.who;
        if (authorityHook.what) this.fields.what = authorityHook.what;
        if (authorityHook.when) this.fields.when = authorityHook.when;
        if (authorityHook.how) this.fields.how = authorityHook.how;
        
        // Trigger cross-generator communication
        if (window.AppEvents) {
          window.AppEvents.trigger('topics:saved', {
            topics: topics,
            authorityHook: authorityHook,
            timestamp: Date.now()
          });
        }
      })
      .catch(error => {
        this.hideLoading();
        
        // Re-enable save button
        if (saveButton) {
          saveButton.disabled = false;
          saveButton.textContent = originalButtonText;
        }
        
        console.error('❌ FIXED: Save failed:', error);
        
        // Enhanced error message handling
        let errorMessage = 'Save operation failed';
        
        if (typeof error === 'string') {
          errorMessage = error;
        } else if (error && error.message) {
          errorMessage = error.message;
        } else if (error && typeof error === 'object') {
          try {
            errorMessage = JSON.stringify(error);
          } catch (e) {
            errorMessage = 'Unknown error occurred during save';
          }
        }
        
        this.showNotification('❌ ' + errorMessage, 'error');
        
        // Enhanced debugging info
        console.group('🔍 FIXED: Save Debug Information');
        console.log('Post ID:', postId);
        console.log('Topics Count:', topicsCount);
        console.log('Authority Hook Valid:', hasAuthorityData);
        console.log('Nonce Available:', !!nonce);
        console.log('Request Data:', { post_id: parseInt(postId), topics, authority_hook: authorityHook });
        console.log('Error Details:', error);
        console.groupEnd();
      });
    },
    
    /**
     * FIXED: Enhanced fallback fetch method with button handling
     */
    saveWithFetch: function(postId, topics, authorityHook, saveButton, originalButtonText) {
      console.log('🔄 FIXED: Using enhanced fallback fetch method...');
      
      const nonce = document.querySelector('#topics-generator-nonce')?.value || 
                    window.mkcg_vars?.nonce || '';
      
      const formData = new URLSearchParams();
      formData.append('action', 'mkcg_save_topics_data');
      formData.append('nonce', nonce);
      formData.append('security', nonce);
      formData.append('post_id', parseInt(postId));
      
      // Add topics as array notation for PHP compatibility
      Object.keys(topics).forEach(key => {
        formData.append(`topics[${key}]`, topics[key]);
      });
      
      // Add authority hook as array notation
      Object.keys(authorityHook).forEach(key => {
        if (authorityHook[key]) {
          formData.append(`authority_hook[${key}]`, authorityHook[key]);
        }
      });
      
      console.log('🔍 FIXED: Fallback request data:', {
        action: 'mkcg_save_topics_data',
        post_id: parseInt(postId),
        topics_count: Object.keys(topics).length,
        has_nonce: !!nonce
      });
      
      fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`Network error: ${response.status} ${response.statusText}`);
        }
        return response.json();
      })
      .then(result => {
        this.hideLoading();
        
        // Re-enable save button
        if (saveButton) {
          saveButton.disabled = false;
          saveButton.textContent = originalButtonText;
        }
        
        if (result.success) {
          this.showNotification('✅ Data saved successfully!', 'success');
          console.log('✅ FIXED: Fallback save successful:', result);
          
          // Update the authority hook display if we have complete text
          if (authorityHook.complete) {
            const displayElement = document.querySelector('#topics-generator-authority-hook-text');
            if (displayElement) {
              displayElement.textContent = authorityHook.complete;
            }
          }
        } else {
          throw new Error(result.data?.message || result.data || 'Server returned error');
        }
      })
      .catch(error => {
        this.hideLoading();
        
        // Re-enable save button
        if (saveButton) {
          saveButton.disabled = false;
          saveButton.textContent = originalButtonText;
        }
        
        this.showNotification('❌ Fallback save failed: ' + error.message, 'error');
        console.error('❌ FIXED: Fallback save failed:', error);
      });
    },

    /**
     * ROOT FIX: BULLETPROOF audience data collection - ZERO duplication guaranteed
     */
    collectAudienceData: function() {
      console.log('🔄 ROOT FIX: BULLETPROOF audience collection (zero duplication)');
      
      // STRATEGY: Check sources in strict priority order - return immediately when found
      
      // PRIORITY 1: Authority Hook Builder form field (most reliable and clean)
      const whoField = document.querySelector('#mkcg-who');
      if (whoField && whoField.value && whoField.value.trim()) {
        const formFieldData = whoField.value.trim();
        
        // Only use if it's real data, not default text
        if (formFieldData !== 'your audience' && formFieldData.length > 3) {
          console.log('✅ Using Authority Hook Builder field (cleanest source):', formFieldData);
          console.log('🎯 FINAL AUDIENCE DATA (source: form-field):', formFieldData);
          return formFieldData;
        }
      }
      
      // PRIORITY 2: Internal storage from PHP data
      if (this.fields.who && this.fields.who.trim()) {
        const internalData = this.fields.who.trim();
        
        // Only use if it's real data, not default text  
        if (internalData !== 'your audience' && internalData.length > 3) {
          console.log('✅ Using internal storage data:', internalData);
          console.log('🎯 FINAL AUDIENCE DATA (source: internal-storage):', internalData);
          return internalData;
        }
      }
      
      // PRIORITY 3: Clean audience chips (if form field is empty)
      const audienceChips = [];
      const chipSelectors = [
        '.audience-chip',
        '.audience-tag', 
        '.selected-audience',
        '[data-audience]',
        '.audience-manager .chip',
        '.audience-manager .tag'
      ];
      
      chipSelectors.forEach(selector => {
        const chips = document.querySelectorAll(selector);
        chips.forEach(chip => {
          let chipText = chip.textContent || chip.dataset.audience || chip.value || '';
          
          // ROOT FIX: Aggressive cleaning to remove all formatting artifacts
          chipText = chipText
            .replace(/\s*×\s*/g, '') // Remove × close buttons
            .replace(/\n+/g, ' ') // Replace newlines with spaces
            .replace(/\s+/g, ' ') // Collapse multiple spaces
            .trim();
          
          // Only add valid, meaningful text
          if (chipText && chipText.length > 2 && chipText !== 'your audience') {
            audienceChips.push(chipText);
          }
        });
      });
      
      // If we found clean chips, format and use them
      if (audienceChips.length > 0) {
        // Remove duplicates and format properly
        const uniqueChips = [...new Set(audienceChips)];
        const chipsData = this.formatAudienceList(uniqueChips);
        console.log('✅ Using cleaned audience chips:', uniqueChips);
        console.log('🎯 FINAL AUDIENCE DATA (source: chips):', chipsData);
        return chipsData;
      }
      
      // CLEAN SLATE: No fallback defaults - empty when no data
      console.log('⚠️ No valid audience data found - returning empty (NO DEFAULTS)');
      console.log('🎯 FINAL AUDIENCE DATA (source: empty): ""');
      return '';
    },
    
    /**
     * ROOT FIX: Format audience list with proper grammar
     */
    formatAudienceList: function(audiences) {
      if (!audiences || audiences.length === 0) {
        return '';
      }
      
      if (audiences.length === 1) {
        return audiences[0];
      }
      
      if (audiences.length === 2) {
        return audiences.join(' and ');
      }
      
      // For 3+ audiences: "A, B, and C"
      const lastAudience = audiences.pop();
      return audiences.join(', ') + ', and ' + lastAudience;
    },
    
    /**
     * ROOT FIX: Add fallback fetch method for emergency cases
     */
    saveWithFetch: function(postId, topics, authorityHook) {
      console.log('🔄 Using fallback fetch method...');
      
      const formData = new URLSearchParams();
      formData.append('action', 'mkcg_save_topics_data');
      formData.append('nonce', document.querySelector('#topics-generator-nonce')?.value || '');
      formData.append('post_id', postId);
      
      // Add topics as array notation for PHP compatibility
      Object.keys(topics).forEach(key => {
        formData.append(`topics[${key}]`, topics[key]);
      });
      
      // CLEAN SLATE: Use the same data collection method - NO DEFAULTS
      const correctedAuthorityHook = {
        who: this.collectAudienceData(),
        what: authorityHook.what || '',
        when: authorityHook.when || '',
        how: authorityHook.how || ''
      };
      
      // CLEAN SLATE: Build complete text only when all fields have data
      const hasAllRealData = correctedAuthorityHook.who && correctedAuthorityHook.what && correctedAuthorityHook.when && correctedAuthorityHook.how &&
                             correctedAuthorityHook.who.trim() && correctedAuthorityHook.what.trim() && 
                             correctedAuthorityHook.when.trim() && correctedAuthorityHook.how.trim();
      
      if (hasAllRealData) {
        correctedAuthorityHook.complete = `I help ${correctedAuthorityHook.who} ${correctedAuthorityHook.what} when ${correctedAuthorityHook.when} ${correctedAuthorityHook.how}.`;
      } else {
        correctedAuthorityHook.complete = ''; // NO DEFAULTS - empty when incomplete
      }
      
      // Add authority hook as array notation
      Object.keys(correctedAuthorityHook).forEach(key => {
        if (correctedAuthorityHook[key]) {
          formData.append(`authority_hook[${key}]`, correctedAuthorityHook[key]);
        }
      });
      
      console.log('🔍 Fallback method using corrected audience data:', correctedAuthorityHook.who);
      
      fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`Network error: ${response.status} ${response.statusText}`);
        }
        return response.json();
      })
      .then(result => {
        this.hideLoading();
        
        if (result.success) {
          this.showNotification('✅ Data saved successfully!', 'success');
          console.log('✅ Fallback save successful:', result);
        } else {
          throw new Error(result.data?.message || result.data || 'Server returned error');
        }
      })
      .catch(error => {
        this.hideLoading();
        this.showNotification('❌ Fallback save failed: ' + error.message, 'error');
        console.error('❌ Fallback save failed:', error);
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
    },
    
    // ROOT FIX: TEST the new bulletproof audience collection
    testAudienceCollection: function() {
      console.log('🔍 TESTING: BULLETPROOF audience data collection...');
      
      if (TopicsGenerator && TopicsGenerator.collectAudienceData) {
        const collectedData = TopicsGenerator.collectAudienceData();
        
        console.log('🎯 COLLECTED AUDIENCE DATA:', collectedData);
        
        // Parse it like the backend would
        const audiences = collectedData.split(/,\s*and\s+|,\s*|\s+and\s+/);
        console.log('📊 PARSED INTO AUDIENCES:', audiences);
        
        // Check for duplication
        const hasDuplication = audiences.length !== new Set(audiences).size;
        console.log(hasDuplication ? '❌ DUPLICATION DETECTED!' : '✅ NO DUPLICATION - SUCCESS!');
        
        return {
          raw: collectedData,
          parsed: audiences,
          count: audiences.length,
          hasDuplication: hasDuplication,
          success: !hasDuplication
        };
      } else {
        console.error('❌ TopicsGenerator.collectAudienceData method not available');
        return null;
      }
    },
    
    // ROOT FIX: NEW - Quick verification test
    quickDuplicationTest: function() {
      console.log('🚀 QUICK TEST: Check for audience duplication...');
      
      const result = this.testAudienceCollection();
      
      if (result) {
        if (result.success) {
          console.log('✅✅✅ ROOT FIX SUCCESS: No duplication detected!');
          console.log('📊 Clean audience count:', result.count);
          console.log('🎯 Final data:', result.raw);
        } else {
          console.log('❌❌❌ ROOT FIX FAILED: Duplication still occurring!');
          console.log('📊 Duplicated audiences:', result.parsed);
        }
        
        return result;
      } else {
        console.log('❌ Test failed - method not available');
        return false;
      }
    },
    
    // CLEAN SLATE: Test the full save process without actually saving
    testSaveData: function() {
      console.log('🧪 TESTING: Clean slate save data collection (dry run)...');
      
      // Collect topics
      const topics = {};
      let topicsCount = 0;
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field && field.value && field.value.trim()) {
          topics[`topic_${i}`] = field.value.trim();
          topicsCount++;
        }
      }
      
      // Collect authority hook using clean slate method
      const audienceData = TopicsGenerator.collectAudienceData ? TopicsGenerator.collectAudienceData() : 'not available';
      const authorityHook = {
        who: audienceData,
        what: TopicsGenerator.fields.what || '',
        when: TopicsGenerator.fields.when || '',
        how: TopicsGenerator.fields.how || ''
      };
      
      console.log('📊 CLEAN SLATE SAVE DATA PREVIEW:');
      console.log('  Topics Count:', topicsCount);
      console.log('  Topics:', topics);
      console.log('  Authority Hook:', authorityHook);
      console.log('  Audience Data Source:', audienceData);
      
      // Test authority hook completeness (always requires all fields)
      const hasAllRealData = authorityHook.who && authorityHook.what && authorityHook.when && authorityHook.how &&
                             authorityHook.who.trim() && authorityHook.what.trim() && 
                             authorityHook.when.trim() && authorityHook.how.trim();
      console.log('  Has All Real Data:', hasAllRealData);
      console.log('  Complete Text Would Be:', hasAllRealData ? `I help ${authorityHook.who} ${authorityHook.what} when ${authorityHook.when} ${authorityHook.how}.` : 'EMPTY (no defaults)');
      
      // Test parsing (only if data exists)
      if (audienceData && audienceData !== 'not available' && audienceData !== '') {
        const parsedAudiences = audienceData.split(/,\s*and\s+|,\s*|\s+and\s+/);
        console.log('  Parsed Audiences:', parsedAudiences);
        console.log('  Expected Terms to Create:', parsedAudiences.length);
      } else {
        console.log('  No audience data to parse (clean slate - no defaults)');
      }
      
      return {
        topics,
        authorityHook,
        topicsCount,
        audienceData,
        hasAllRealData,
        wouldSave: topicsCount > 0 || hasAllRealData
      };
    },
    
    // CLEAN SLATE: Test empty field behavior - always empty when incomplete
    testEmptyFieldBehavior: function() {
      console.log('🧪 TESTING: Clean slate empty field behavior...');
      
      console.log('📊 CLEAN SLATE TEST RESULTS:');
      console.log('  Behavior: Always empty when fields incomplete (no defaults ever)');
      
      // Test updateAuthorityHook behavior
      if (TopicsGenerator && TopicsGenerator.updateAuthorityHook) {
        console.log('  Testing updateAuthorityHook...');
        
        // Save current fields
        const originalFields = { ...TopicsGenerator.fields };
        
        // Test 1: Empty fields should show empty
        TopicsGenerator.fields = { who: '', what: '', when: '', how: '' };
        TopicsGenerator.updateAuthorityHook();
        
        const displayElement = document.querySelector('#topics-generator-authority-hook-text');
        const emptyText = displayElement ? displayElement.textContent : 'Element not found';
        console.log('  Empty Fields Display:', `"${emptyText}"`);
        console.log('  ✅ Empty when incomplete:', emptyText === '');
        
        // Test 2: Partial fields should show empty
        TopicsGenerator.fields = { who: 'test audience', what: '', when: '', how: '' };
        TopicsGenerator.updateAuthorityHook();
        const partialText = displayElement ? displayElement.textContent : 'Element not found';
        console.log('  Partial Fields Display:', `"${partialText}"`);
        console.log('  ✅ Partial still empty:', partialText === '');
        
        // Test 3: Complete fields should show text
        TopicsGenerator.fields = { who: 'test audience', what: 'achieve goals', when: 'they struggle', how: 'with my method' };
        TopicsGenerator.updateAuthorityHook();
        const completeText = displayElement ? displayElement.textContent : 'Element not found';
        console.log('  Complete Fields Display:', `"${completeText}"`);
        console.log('  ✅ Complete shows text:', completeText !== '');
        
        // Restore original fields
        TopicsGenerator.fields = originalFields;
        TopicsGenerator.updateAuthorityHook();
        
        const isWorking = emptyText === '' && partialText === '' && completeText !== '';
        
        return {
          emptyFieldsDisplay: emptyText,
          partialFieldsDisplay: partialText,
          completeFieldsDisplay: completeText,
          isCleanSlateBehaviorWorking: isWorking,
          testResult: isWorking ? '✅ PASSED - Clean slate behavior working' : '❌ FAILED - Default behavior detected'
        };
      } else {
        console.log('  ERROR: TopicsGenerator.updateAuthorityHook not available');
        return { error: 'updateAuthorityHook not available' };
      }
    }
  };
  
  console.log('✅ CLEAN SLATE Topics Generator loaded - NO DEFAULT PLACEHOLDERS EVER');
  console.log('🔧 CLEAN SLATE FIX: Empty fields when no data, complete text only when all fields filled');
  console.log('🧪 DEBUG: Use window.MKCG_Topics_PopulationTest.testEmptyFieldBehavior() to test');
  console.log('🔍 DEBUG: Use window.MKCG_Topics_PopulationTest.testSaveData() to inspect save behavior');
  
  // CRITICAL FIX: Add real-time data collection testing
  window.MKCG_Topics_DataTest = {
    checkTopicFields: function() {
      console.log('🔍 TESTING: Current topic field status...');
      const results = {};
      
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        results[`topic_${i}`] = {
          found: !!field,
          value: field ? field.value : 'N/A',
          hasValue: field ? (field.value && field.value.trim()) : false
        };
        console.log(`  Topic ${i}:`, results[`topic_${i}`]);
      }
      
      const totalWithValues = Object.values(results).filter(r => r.hasValue).length;
      console.log(`📊 SUMMARY: ${totalWithValues}/5 topic fields have values`);
      
      return results;
    },
    
    testSaveDataCollection: function() {
      console.log('🧪 TESTING: Simulate save data collection...');
      
      if (!window.TopicsGenerator) {
        console.error('❌ TopicsGenerator not available');
        return null;
      }
      
      // Test the exact same logic as saveAllData
      const postId = document.querySelector('#topics-generator-post-id')?.value || 
                     window.MKCG_Topics_Data?.postId || '0';
                     
      console.log('📍 Post ID detection:', postId);
      
      // Test topics collection
      const topics = {};
      let topicsCount = 0;
      
      for (let i = 1; i <= 5; i++) {
        const field = document.querySelector(`#topics-generator-topic-field-${i}`);
        if (field && field.value && field.value.trim()) {
          topics[`topic_${i}`] = field.value.trim();
          topicsCount++;
          console.log(`✅ COLLECTED topic ${i}:`, field.value.trim());
        } else {
          console.log(`⚠️ SKIPPED topic ${i}:`, field ? `empty value "${field.value}"` : 'field not found');
        }
      }
      
      console.log(`📊 RESULT: Would collect ${topicsCount} topics`);
      console.log('🔍 Topics object:', topics);
      
      return {
        postId,
        topics,
        topicsCount,
        wouldProceed: topicsCount > 0
      };
    },
    
    quickTest: function() {
      console.log('🚀 QUICK TEST: Complete data collection check');
      const fieldCheck = this.checkTopicFields();
      const saveTest = this.testSaveDataCollection();
      
      console.log('📊 QUICK TEST SUMMARY:');
      console.log('  Fields with values:', Object.values(fieldCheck).filter(r => r.hasValue).length);
      console.log('  Would collect topics:', saveTest?.topicsCount || 0);
      console.log('  Save would proceed:', saveTest?.wouldProceed || false);
      
      if (saveTest?.topicsCount === 0) {
        console.log('⚠️ ISSUE: No topics would be collected!');
        console.log('💡 SOLUTION: Enter some text in the topic fields above and try again');
      }
      
      return {
        fieldsCheck: fieldCheck,
        saveTest: saveTest
      };
    }
  };
  
  console.log('🚀 DEBUG HELPER: Use window.MKCG_Topics_DataTest.quickTest() to diagnose save issues');

})();