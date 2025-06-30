/**
 * Authority Hook Builder - Vanilla JavaScript
 * Handles WHO-WHAT-WHEN-HOW tab system and live Authority Hook generation
 * Version: 1.0.0
 */

(function() {
  'use strict';
  
  /**
   * Authority Hook Builder Class
   */
  class AuthorityHookBuilder {
    constructor() {
      this.elements = {
        // Tab inputs
        whoInput: document.getElementById('mkcg-who'),
        resultInput: document.getElementById('mkcg-result'), 
        whenInput: document.getElementById('mkcg-when'),
        howInput: document.getElementById('mkcg-how'),
        
        // Tag containers
        tagContainer: document.getElementById('tags_container'),
        tagInput: document.getElementById('tag_input'),
        addTagBtn: document.getElementById('add_tag'),
        
        // Authority Hook display
        authorityHookContent: document.getElementById('authority-hook-content'),
        authorityHookField: document.getElementById('mkcg-authority-hook'),
        
        // Buttons
        copyBtn: document.getElementById('copy-authority-hook-btn'),
        editBtn: document.getElementById('edit-authority-components')
      };
      
      this.init();
    }
    
    init() {
      console.log('🚀 Authority Hook Builder initializing...');
      this.hideTargetAudienceSection();
      this.initAudienceTagManager(); // NEW: Initialize audience tag management
      this.setupEventListeners();
      this.updateAuthorityHook();
      console.log('✅ Authority Hook Builder ready!');
    }
    
    /**
     * ENHANCED: Initialize Audience Tag Manager (from Offer Builder)
     * Supports multiple audiences with checkboxes and Add to List functionality
     */
    initAudienceTagManager() {
      const whoField = this.elements.whoInput;
      const tagInput = this.elements.tagInput;
      const addTagBtn = this.elements.addTagBtn;
      const tagsContainer = this.elements.tagContainer;

      if (!whoField || !tagInput || !addTagBtn || !tagsContainer) {
        console.log('ℹ️ Audience tag manager elements not found, skipping initialization');
        return;
      }
      
      console.log('🏷️ Initializing Audience Tag Manager');

      const saveTags = () => {
        const tagEls = tagsContainer.querySelectorAll('.audience-tag');
        const selectedTags = [];
        let totalCount = 0;
        let selectedCount = 0;
        
        tagEls.forEach(tagEl => {
          totalCount++;
          const checkbox = tagEl.querySelector('.credential-checkbox');
          // Include tag if no checkbox exists or if it's checked
          if (!checkbox || checkbox.checked) {
            selectedTags.push(tagEl.dataset.value);
            selectedCount++;
          }
        });
        
        whoField.value = selectedTags.join(', ');
        whoField.dispatchEvent(new Event('input', { bubbles: true }));
        
        // Update status counters
        this.updateAudienceStatus(totalCount, selectedCount);
        
        // Update Authority Hook immediately
        this.updateAuthorityHook();
        console.log('💾 Saved audiences:', selectedTags.join(', '), `(${selectedCount}/${totalCount} selected)`);
      };

      const addTag = (text, isChecked = true) => {
        const trimmed = text.trim();
        if (!trimmed) return;

        // Check for duplicate
        const exists = [...tagsContainer.querySelectorAll('.audience-tag')]
          .some(tag => tag.dataset.value.toLowerCase() === trimmed.toLowerCase());
        if (exists) {
          console.log('⚠️ Duplicate audience tag, skipping:', trimmed);
          return;
        }

        const tagEl = document.createElement('div');
        tagEl.className = 'audience-tag';
        tagEl.dataset.value = trimmed;

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'credential-checkbox';
        checkbox.checked = isChecked;

        const span = document.createElement('span');
        span.textContent = trimmed;

        const removeBtn = document.createElement('span');
        removeBtn.className = 'credential-remove';
        removeBtn.textContent = '×';

        tagEl.appendChild(checkbox);
        tagEl.appendChild(span);
        tagEl.appendChild(removeBtn);
        tagsContainer.appendChild(tagEl);

        removeBtn.addEventListener('click', function() {
          tagEl.remove();
          saveTags();
          console.log('🗑️ Removed audience tag:', trimmed);
        });

        checkbox.addEventListener('change', saveTags);

        tagInput.value = '';
        saveTags();
        
        console.log('✅ Added audience tag:', trimmed, 'Checked:', isChecked);
      };

      // Expose the addTag function globally (for external access)
      window.addAudienceTag = addTag;

      addTagBtn.addEventListener('click', function() {
        addTag(tagInput.value);
      });

      tagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          addTag(tagInput.value);
        }
      });

      // Load existing tags from the WHO field
      const loadExistingTags = () => {
        const existingVal = whoField.value.trim();
        if (!existingVal) return;
        const splitted = existingVal.split(',').map(x => x.trim()).filter(Boolean);
        
        // Clear the container first
        tagsContainer.innerHTML = '';
        
        // Add each existing audience as a tag
        splitted.forEach(tag => addTag(tag, true));
        console.log('📎 Loaded existing audience tags:', splitted);
      };
      
      // Load any existing tags
      loadExistingTags();
      
      // ENHANCED: Prevent manual editing of the main WHO field
      // Users should only interact with the tag system
      whoField.addEventListener('input', (e) => {
        // If user tries to type in the main field, guide them to use tags
        if (!e.isTrusted) return; // Allow programmatic updates
        
        setTimeout(() => {
          // Reset to the tag-based value
          saveTags();
        }, 100);
      });
      
      // Disable paste and other direct input methods
      whoField.addEventListener('paste', (e) => {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        if (pastedText) {
          // Add pasted audiences as individual tags
          const audiences = pastedText.split(',').map(x => x.trim()).filter(Boolean);
          audiences.forEach(audience => addTag(audience, true));
        }
      });
    }
    
    hideTargetAudienceSection() {
      // Hide Target Audience section with multiple methods and retry logic
      const hideTargetAudience = () => {
        let hiddenCount = 0;
        
        // Method 1: Find sections by title text
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
          const titleElement = section.querySelector('.section__title');
          if (titleElement && titleElement.textContent.includes('Target Audience')) {
            section.style.display = 'none !important';
            hiddenCount++;
            console.log('✅ Target Audience section hidden by title');
          }
        });
        
        // Method 2: Find by field ID and hide container
        const audienceField = document.getElementById('topics-audience');
        if (audienceField) {
          const container = audienceField.closest('.section, .form-group, .field-group, div[class*="section"], div[class*="field"]');
          if (container) {
            container.style.display = 'none !important';
            hiddenCount++;
            console.log('✅ Target Audience field container hidden by field ID');
          }
        }
        
        // Method 3: Find by placeholder text
        const fieldsWithPlaceholder = document.querySelectorAll('[placeholder*="SaaS startup"], [placeholder*="real estate"]');
        fieldsWithPlaceholder.forEach(field => {
          const container = field.closest('.section, .form-group, .field-group, div[class*="section"], div[class*="field"]');
          if (container) {
            container.style.display = 'none !important';
            hiddenCount++;
            console.log('✅ Target Audience field hidden by placeholder');
          }
        });
        
        // Method 4: Find by label text
        const labels = document.querySelectorAll('label');
        labels.forEach(label => {
          if (label.textContent.includes('Target Audience') || label.textContent.includes('Specific Target Audience')) {
            const container = label.closest('.section, .form-group, .field-group, div[class*="section"], div[class*="field"]');
            if (container) {
              container.style.display = 'none !important';
              hiddenCount++;
              console.log('✅ Target Audience section hidden by label');
            }
          }
        });
        
        return hiddenCount;
      };
      
      // Initial attempt
      let hiddenCount = hideTargetAudience();
      
      // Retry after a short delay if nothing was hidden (in case elements load later)
      if (hiddenCount === 0) {
        setTimeout(() => {
          const retryCount = hideTargetAudience();
          if (retryCount === 0) {
            console.log('ℹ️ No Target Audience sections found to hide');
          }
        }, 500);
      }
      
      // Set up observer to catch dynamically added content
      if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
              hideTargetAudience();
            }
          });
        });
        
        observer.observe(document.body, {
          childList: true,
          subtree: true
        });
      }
    }
    
    setupEventListeners() {
      // Input field changes - update Authority Hook live
      ['whoInput', 'resultInput', 'whenInput', 'howInput'].forEach(key => {
        const element = this.elements[key];
        if (element) {
          element.addEventListener('input', () => this.updateAuthorityHook());
          element.addEventListener('change', () => this.updateAuthorityHook());
        }
      });
      
      // Example tag click handlers - ENHANCED for "Add to List" functionality
      document.addEventListener('click', (e) => {
        // Handle "Add to List" clicks for audience tags
        if (e.target.classList.contains('add-to-list')) {
          e.preventDefault();
          const value = e.target.dataset.value;
          if (value && typeof window.addAudienceTag === 'function') {
            window.addAudienceTag(value);
            
            // Visual feedback
            const originalText = e.target.textContent;
            e.target.textContent = '✓ Added to List';
            e.target.style.color = '#10a3be';
            setTimeout(() => {
              e.target.textContent = originalText;
              e.target.style.color = '';
            }, 1500);
            
            console.log('✅ Added to audience list:', value);
          }
          return;
        }
        
        // Handle legacy tag clicks (fallback)
        if (e.target.classList.contains('tag__add-link') || 
            (e.target.parentElement && e.target.parentElement.classList.contains('tag--example'))) {
          e.preventDefault();
          this.handleExampleTagClick(e);
        }
      });
      
      // Add tag button
      if (this.elements.addTagBtn) {
        this.elements.addTagBtn.addEventListener('click', () => this.addCustomTag());
      }
      
      // Tag input enter key
      if (this.elements.tagInput) {
        this.elements.tagInput.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            this.addCustomTag();
          }
        });
      }
      
      // Copy button
      if (this.elements.copyBtn) {
        this.elements.copyBtn.addEventListener('click', () => this.copyToClipboard());
      }
      
      // Clear field buttons - ENHANCED for tag-based management
      document.addEventListener('click', (e) => {
        if (e.target.classList.contains('field__clear')) {
          const fieldId = e.target.dataset.fieldId;
          
          if (fieldId === 'mkcg-who') {
            // Clear all audience tags instead of just the field
            this.clearAllAudienceTags();
          } else {
            // Handle other fields normally
            const field = document.getElementById(fieldId);
            if (field) {
              field.value = '';
              this.updateAuthorityHook();
            }
          }
        }
      });
    }
    
    /**
     * ENHANCED: Update audience status counters
     */
    updateAudienceStatus(totalCount, selectedCount) {
      const audienceCountEl = document.getElementById('audience-count');
      const selectedCountEl = document.getElementById('selected-count');
      
      if (audienceCountEl) {
        audienceCountEl.textContent = totalCount;
      }
      if (selectedCountEl) {
        selectedCountEl.textContent = selectedCount;
      }
      
      // Add visual feedback for the status
      const statusEl = document.querySelector('.audience-manager-status');
      if (statusEl) {
        if (selectedCount > 0) {
          statusEl.style.background = 'rgba(76, 175, 80, 0.1)';
          statusEl.style.borderLeft = '3px solid #4caf50';
        } else {
          statusEl.style.background = 'rgba(33, 150, 243, 0.05)';
          statusEl.style.borderLeft = '';
        }
      }
    }
    
    /**
     * ENHANCED: Clear all audience tags (called by clear button)
     */
    clearAllAudienceTags() {
      const tagsContainer = this.elements.tagContainer;
      if (tagsContainer) {
        // Remove all audience tags
        tagsContainer.innerHTML = '';
        
        // Clear the main WHO field
        if (this.elements.whoInput) {
          this.elements.whoInput.value = '';
        }
        
        // Reset status counters
        this.updateAudienceStatus(0, 0);
        
        // Update Authority Hook
        this.updateAuthorityHook();
        
        console.log('🗑️ Cleared all audience tags');
      }
    }
    
    handleExampleTagClick(e) {
      const tagElement = e.target.closest('.tag--example');
      if (!tagElement) return;
      
      const value = tagElement.dataset.value;
      const target = tagElement.dataset.target;
      
      if (value && target) {
        const targetField = document.getElementById(target);
        if (targetField) {
          // Add to field value (don't replace)
          const currentValue = targetField.value.trim();
          if (currentValue && !currentValue.includes(value)) {
            targetField.value = currentValue + ', ' + value;
          } else if (!currentValue) {
            targetField.value = value;
          }
          
          this.updateAuthorityHook();
          
          // Visual feedback
          const addLink = tagElement.querySelector('.tag__add-link');
          if (addLink) {
            const originalText = addLink.textContent;
            addLink.textContent = '✓ Added';
            addLink.style.color = '#10a3be';
            setTimeout(() => {
              addLink.textContent = originalText;
              addLink.style.color = '';
            }, 1000);
          }
          
          console.log(`✅ Added "${value}" to ${target}`);
        }
      }
    }
    
    addCustomTag() {
      const tagText = this.elements.tagInput?.value?.trim();
      if (!tagText) return;
      
      // Add to current WHO field (assuming that's the active tab)
      if (this.elements.whoInput) {
        const currentValue = this.elements.whoInput.value.trim();
        if (currentValue && !currentValue.includes(tagText)) {
          this.elements.whoInput.value = currentValue + ', ' + tagText;
        } else if (!currentValue) {
          this.elements.whoInput.value = tagText;
        }
      }
      
      // Clear input
      this.elements.tagInput.value = '';
      
      // Create visual tag in container
      this.createVisualTag(tagText);
      
      this.updateAuthorityHook();
      console.log(`✅ Added custom tag: "${tagText}"`);
    }
    
    createVisualTag(text) {
      if (!this.elements.tagContainer) return;
      
      const tagElement = document.createElement('div');
      tagElement.className = 'tag-manager__tag';
      tagElement.innerHTML = `
        <span>${this.escapeHtml(text)}</span>
        <button type="button" class="tag-manager__remove" onclick="this.parentElement.remove(); window.authorityHookBuilder.updateAuthorityHook();">×</button>
      `;
      
      this.elements.tagContainer.appendChild(tagElement);
    }
    
    updateAuthorityHook() {
      const who = this.elements.whoInput?.value?.trim() || 'your audience';
      const result = this.elements.resultInput?.value?.trim() || 'achieve results';
      const when = this.elements.whenInput?.value?.trim() || 'when they need you';
      const how = this.elements.howInput?.value?.trim() || 'through your method';
      
      // ENHANCED: Format WHO field with proper comma handling for multiple audiences
      const formatAudienceList = (audienceText) => {
        if (!audienceText || audienceText === 'your audience') {
          return 'your audience';
        }
        
        // Split on commas, trim each piece, filter out empties
        const audiences = audienceText
          .split(',')
          .map(s => s.trim())
          .filter(Boolean);
        
        if (audiences.length === 0) {
          return 'your audience';
        } else if (audiences.length === 1) {
          return audiences[0];
        } else if (audiences.length === 2) {
          return audiences.join(' and ');
        } else {
          // More than two: "A, B, and C"
          const last = audiences.pop();
          return audiences.join(', ') + ', and ' + last;
        }
      };
      
      const formattedWho = formatAudienceList(who);
      
      // Create the Authority Hook sentence
      let authorityHook = `I help ${formattedWho} ${result} ${when} ${how}.`;
      
      // Clean up any double spaces and ensure proper formatting
      authorityHook = authorityHook.replace(/\s+/g, ' ').trim();
      
      // Update display with highlighted segments
      if (this.elements.authorityHookContent) {
        this.elements.authorityHookContent.innerHTML = `
          I help <span class="authority-hook__highlight">${this.escapeHtml(formattedWho)}</span> 
          <span class="authority-hook__highlight">${this.escapeHtml(result)}</span> 
          <span class="authority-hook__highlight">${this.escapeHtml(when)}</span> 
          <span class="authority-hook__highlight">${this.escapeHtml(how)}</span>.
        `;
      }
      
      // Update hidden field
      if (this.elements.authorityHookField) {
        this.elements.authorityHookField.value = authorityHook;
      }
      
      console.log('Authority Hook updated:', authorityHook);
    }
    
    copyToClipboard() {
      const text = this.elements.authorityHookField?.value || 
                   this.elements.authorityHookContent?.textContent || '';
      
      if (!text || text === 'I help your audience achieve results when they need you through your method.') {
        alert('Please complete your Authority Hook before copying.');
        return;
      }
      
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
          .then(() => {
            this.showCopySuccess();
            console.log('✅ Copied to clipboard:', text);
          })
          .catch(err => {
            console.error('Clipboard copy failed:', err);
            this.fallbackCopy(text);
          });
      } else {
        this.fallbackCopy(text);
      }
    }
    
    showCopySuccess() {
      const btn = this.elements.copyBtn;
      if (btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
          </svg>
          Copied!
        `;
        btn.style.backgroundColor = '#10a3be';
        
        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.backgroundColor = '';
        }, 2000);
      }
    }
    
    fallbackCopy(text) {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      
      try {
        document.execCommand('copy');
        this.showCopySuccess();
        console.log('✅ Copied to clipboard (fallback):', text);
      } catch (err) {
        console.error('Fallback copy failed:', err);
        alert('Unable to copy automatically. Authority Hook: ' + text);
      }
      
      document.body.removeChild(textarea);
    }
    
    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    // Method to get the current Authority Hook for external use (e.g., topics generation)
    getAuthorityHook() {
      return this.elements.authorityHookField?.value || 
             this.elements.authorityHookContent?.textContent || '';
    }
    
    // Method to set Authority Hook from external source
    setAuthorityHook(hook) {
      if (this.elements.authorityHookField) {
        this.elements.authorityHookField.value = hook;
      }
      if (this.elements.authorityHookContent) {
        this.elements.authorityHookContent.textContent = hook;
      }
    }
  }

  /**
   * Topics Generator Integration
   * Enhanced for unified system
   */
  class TopicsGenerator {
    constructor(authorityHookBuilder) {
      this.authorityHookBuilder = authorityHookBuilder;
      this.elements = {
        generateBtn: document.getElementById('generate-topics-btn'),
        regenerateBtn: document.getElementById('regenerate-topics-btn'),
        copyAllBtn: document.getElementById('copy-all-topics-btn'),
        loadingOverlay: document.getElementById('topics-loading-overlay'),
        resultsSection: document.getElementById('topics-results'),
        topicsList: document.getElementById('topics-list'),
        entryIdField: document.getElementById('topics-entry-id'),
        nonceField: document.getElementById('topics-nonce')
      };
      
      this.init();
    }
    
    init() {
      this.setupEventListeners();
      console.log('✅ Topics Generator ready!');
    }
    
    setupEventListeners() {
      if (this.elements.generateBtn) {
        this.elements.generateBtn.addEventListener('click', () => this.generateTopics());
      }
      
      if (this.elements.regenerateBtn) {
        this.elements.regenerateBtn.addEventListener('click', () => this.generateTopics());
      }
      
      if (this.elements.copyAllBtn) {
        this.elements.copyAllBtn.addEventListener('click', () => this.copyAllTopics());
      }
    }
    
    generateTopics() {
      const authorityHook = this.authorityHookBuilder.getAuthorityHook();
      
      if (!authorityHook || authorityHook.trim() === '' || 
          authorityHook === 'I help your audience achieve results when they need you through your method.') {
        alert('Please complete your Authority Hook first by filling in the WHO, RESULT, WHEN, and HOW fields.');
        // Focus on the first tab
        const whoTab = document.getElementById('tabwho');
        if (whoTab) {
          whoTab.checked = true;
          whoTab.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      
      console.log('🎯 Generating topics with Authority Hook:', authorityHook);
      
      this.showLoading();
      
      const entryId = this.elements.entryIdField?.value;
      const nonce = this.elements.nonceField?.value;
      
      const requestData = {
        action: 'generate_interview_topics',
        security: nonce,
        audience: authorityHook,
        authority_hook: authorityHook
      };
      
      if (entryId && entryId !== '0') {
        requestData.entry_id = entryId;
      }
      
      this.makeAjaxRequest(requestData)
        .then(response => {
          this.hideLoading();
          
          if (response.success && response.data.topics) {
            this.displayTopics(response.data.topics);
            console.log('✅ Topics generated successfully');
          } else {
            alert('Error generating topics: ' + (response.data?.message || 'Unknown error'));
          }
        })
        .catch(error => {
          this.hideLoading();
          alert('Network error: ' + error.message);
          console.error('Topics generation error:', error);
        });
    }
    
    makeAjaxRequest(data) {
      const formData = new URLSearchParams();
      Object.keys(data).forEach(key => {
        if (data[key] !== undefined && data[key] !== null) {
          formData.append(key, data[key]);
        }
      });
      
      return fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      }).then(response => response.json());
    }
    
    displayTopics(topics) {
      if (!topics || topics.length === 0) {
        alert('No topics were generated. Please try again.');
        return;
      }
      
      let topicsHtml = '';
      topics.forEach((topic, index) => {
        const cleanTopic = topic.replace(/^\d+\.\s*/, '').trim();
        
        topicsHtml += `
          <div class="results__item">
            <span class="results__number">${index + 1}.</span>
            <span class="results__text">${this.escapeHtml(cleanTopic)}</span>
            <button type="button" class="button button--use" onclick="window.topicsGenerator.useTopic('${this.escapeHtml(cleanTopic)}', ${index + 1})">
              Use Topic
            </button>
          </div>`;
      });
      
      if (this.elements.topicsList) {
        this.elements.topicsList.innerHTML = topicsHtml;
      }
      
      if (this.elements.resultsSection) {
        this.elements.resultsSection.style.display = 'block';
        this.elements.resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
    
    useTopic(topic, number) {
      console.log(`Selected topic ${number}:`, topic);
      
      // Store in localStorage for use in topics form
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem('selected_topic', topic);
        localStorage.setItem('topic_number', number);
      }
      
      // Show success message
      const btn = event.target;
      const originalText = btn.textContent;
      btn.textContent = '✓ Used';
      btn.style.backgroundColor = '#10a3be';
      
      setTimeout(() => {
        btn.textContent = originalText;
        btn.style.backgroundColor = '';
      }, 2000);
      
      alert(`Topic ${number} selected and saved! You can now use this in your topics form.`);
    }
    
    copyAllTopics() {
      const topicElements = document.querySelectorAll('.results__text');
      if (topicElements.length === 0) {
        alert('No topics to copy. Please generate topics first.');
        return;
      }
      
      let allTopics = '';
      topicElements.forEach((element, index) => {
        allTopics += `${index + 1}. ${element.textContent}\n`;
      });
      
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(allTopics)
          .then(() => {
            this.showCopyAllSuccess();
          })
          .catch(() => this.fallbackCopyAll(allTopics));
      } else {
        this.fallbackCopyAll(allTopics);
      }
    }
    
    showCopyAllSuccess() {
      const btn = this.elements.copyAllBtn;
      if (btn) {
        const originalText = btn.textContent;
        btn.textContent = '✓ Copied!';
        btn.style.backgroundColor = '#10a3be';
        
        setTimeout(() => {
          btn.textContent = originalText;
          btn.style.backgroundColor = '';
        }, 2000);
      }
    }
    
    fallbackCopyAll(text) {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      
      try {
        document.execCommand('copy');
        this.showCopyAllSuccess();
      } catch (err) {
        alert('Unable to copy automatically. Please copy the topics manually.');
      }
      
      document.body.removeChild(textarea);
    }
    
    showLoading() {
      if (this.elements.loadingOverlay) {
        this.elements.loadingOverlay.style.display = 'flex';
      }
    }
    
    hideLoading() {
      if (this.elements.loadingOverlay) {
        this.elements.loadingOverlay.style.display = 'none';
      }
    }
    
    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  }

  // Auto-initialize when DOM is ready
  function initializeComponents() {
    console.log('🚀 Initializing Authority Hook Builder and Topics Generator...');
    
    // Initialize Authority Hook Builder
    window.authorityHookBuilder = new AuthorityHookBuilder();
    
    // Initialize Topics Generator (if elements exist)
    if (document.getElementById('generate-topics-btn')) {
      window.topicsGenerator = new TopicsGenerator(window.authorityHookBuilder);
    }
    
    // Make sure Target Audience is hidden after everything loads
    setTimeout(() => {
      if (window.authorityHookBuilder) {
        window.authorityHookBuilder.hideTargetAudienceSection();
      }
    }, 1000);
    
    console.log('✅ All systems ready!');
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeComponents);
  } else {
    // DOM is already loaded
    initializeComponents();
  }
  
  // Fallback initialization for dynamic content
  setTimeout(initializeComponents, 100);

})();