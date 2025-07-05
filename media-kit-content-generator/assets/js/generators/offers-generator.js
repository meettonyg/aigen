/**
 * Offers Generator JavaScript - SIMPLIFIED VERSION
 * Based on Topics Generator pattern with offer-specific functionality
 * Implementation: Clean, maintainable code following established patterns
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
      formData.append('nonce', document.querySelector('#offers-generator-nonce')?.value || '');
      
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
   * SIMPLIFIED Offers Generator
   * 3-step initialization: load data, bind events, update display
   */
  const OffersGenerator = {
    
    // Essential data
    fields: {
      who: '',
      what: '',
      when: '',
      how: ''
    },
    
    // Business data
    businessData: {
      business_type: '',
      target_audience: '',
      price_range: 'mid',
      delivery_method: 'online',
      offer_count: 5
    },
    
    /**
     * SIMPLIFIED: Initialize - Direct and clean
     */
    init: function() {
      console.log('🎯 Offers Generator: Simple initialization starting');
      
      // Step 1: Load existing data
      this.loadExistingData();
      
      // Step 2: Bind form events  
      this.bindEvents();
      
      // Step 3: Update display
      this.updateDisplay();
      
      console.log('✅ Offers Generator: Simple initialization completed');
    },
    
    /**
     * SIMPLIFIED: Load data from PHP or defaults
     */
    loadExistingData: function() {
      // Check if PHP passed data
      if (window.MKCG_Offers_Data) {
        // Check if we're in non-entry mode (user not logged in or no entry parameter)
        if (window.MKCG_Offers_Data.noEntryParam) {
          console.log('📝 No entry parameter - using empty data');
          this.setDefaultData(); // This now sets empty values
        } else if (window.MKCG_Offers_Data.hasData) {
          console.log('📝 Loading data from PHP:', window.MKCG_Offers_Data);
          this.populateFromPHPData(window.MKCG_Offers_Data);
        } else {
          console.log('📝 No data found but entry param exists - using empty data');
          this.setDefaultData();
        }
      } else {
        console.log('📝 MKCG_Offers_Data not available - using empty data');
        this.setDefaultData();
      }
    },
    
    /**
     * SIMPLIFIED: Populate from PHP data
     */
    populateFromPHPData: function(phpData) {
      if (phpData.authorityHook) {
        this.fields.who = phpData.authorityHook.who || '';
        this.fields.what = phpData.authorityHook.what || '';
        this.fields.when = phpData.authorityHook.when || '';
        this.fields.how = phpData.authorityHook.how || '';
        
        this.updateInputFields();
      }
      
      // Load existing business data if available
      if (phpData.businessData) {
        Object.assign(this.businessData, phpData.businessData);
        this.updateBusinessFields();
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
     */
    updateInputFields: function() {
      const fieldMappings = [
        { field: 'who', selector: '#mkcg-who' },
        { field: 'what', selector: '#mkcg-result' },
        { field: 'when', selector: '#mkcg-when' },
        { field: 'how', selector: '#mkcg-how' }
      ];
      
      fieldMappings.forEach(({ field, selector }) => {
        const input = document.querySelector(selector);
        if (input) {
          input.value = this.fields[field] || '';
        }
      });
    },
    
    /**
     * Update business data fields
     */
    updateBusinessFields: function() {
      const businessFields = [
        'business_type',
        'target_audience', 
        'price_range',
        'delivery_method',
        'offer_count'
      ];
      
      businessFields.forEach(field => {
        const element = document.querySelector(`#offers-${field.replace('_', '-')}`);
        if (element && this.businessData[field]) {
          element.value = this.businessData[field];
        }
      });
    },
    
    /**
     * SIMPLIFIED: Bind essential events
     */
    bindEvents: function() {
      // Authority Hook Builder toggle
      const toggleBtn = document.querySelector('#offers-generator-toggle-builder');
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
      } else {
        console.warn('⚠️ Toggle builder button not found: #offers-generator-toggle-builder');
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
      
      // Business data change events
      const businessEvents = [
        'business_type',
        'target_audience',
        'price_range', 
        'delivery_method',
        'offer_count'
      ];
      
      businessEvents.forEach(field => {
        const element = document.querySelector(`#offers-${field.replace('_', '-')}`);
        if (element) {
          element.addEventListener('change', () => {
            this.businessData[field] = element.value;
          });
        }
      });
      
      // Generate offers button - FIXED SELECTOR
      const generateBtn = document.querySelector('#offers-generator-generate-offers');
      if (generateBtn) {
        generateBtn.addEventListener('click', () => {
          this.generateOffers();
        });
      }
      
      // Copy all offers button
      const copyAllBtn = document.querySelector('#copy-all-offers-btn');
      if (copyAllBtn) {
        copyAllBtn.addEventListener('click', () => {
          this.copyAllOffers();
        });
      }
      
      // Regenerate offers button
      const regenerateBtn = document.querySelector('#regenerate-offers-btn');
      if (regenerateBtn) {
        regenerateBtn.addEventListener('click', () => {
          this.generateOffers();
        });
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
     */
    toggleBuilder: function() {
      const builder = document.querySelector('#offers-generator-authority-hook-builder');
      if (!builder) {
        console.warn('⚠️ Authority Hook Builder not found: #offers-generator-authority-hook-builder');
        return;
      }
      
      const isHidden = builder.classList.contains('generator__builder--hidden');
      
      if (isHidden) {
        builder.classList.remove('generator__builder--hidden');
        console.log('✅ Authority Hook Builder shown');
      } else {
        builder.classList.add('generator__builder--hidden');
        console.log('✅ Authority Hook Builder hidden');
      }
    },
    
    /**
     * SIMPLIFIED: Update Authority Hook display
     */
    updateAuthorityHook: function() {
      const hookText = `I help ${this.fields.who || 'your audience'} ${this.fields.what || 'achieve their goals'} when ${this.fields.when || 'they need help'} ${this.fields.how || 'through your method'}.`;
      
      const displayElement = document.querySelector('#offers-generator-authority-hook-text');
      if (displayElement) {
        displayElement.textContent = hookText;
      }
      
      // Update hidden field for form submission
      const hiddenField = document.querySelector('#mkcg-authority-hook');
      if (hiddenField) {
        hiddenField.value = hookText;
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
     * SIMPLIFIED: Generate offers using simple AJAX
     */
    generateOffers: function() {
      const authorityHook = document.querySelector('#offers-generator-authority-hook-text')?.textContent || 
                          document.querySelector('#mkcg-authority-hook')?.value;
      
      if (!authorityHook || authorityHook.trim() === '') {
        this.showNotification('Please build your authority hook first', 'warning');
        return;
      }
      
      // Validate business fields
      if (!this.businessData.business_type) {
        this.showNotification('Please select your business type', 'warning');
        return;
      }
      
      if (!this.businessData.target_audience) {
        this.showNotification('Please describe your target audience', 'warning');
        return;
      }
      
      this.showLoading();
      
      // Use simple AJAX system
      makeAjaxRequest('mkcg_generate_offers', {
        authority_hook: authorityHook,
        business_type: this.businessData.business_type,
        target_audience: this.businessData.target_audience,
        price_range: this.businessData.price_range,
        delivery_method: this.businessData.delivery_method,
        offer_count: this.businessData.offer_count
      })
      .then(data => {
        this.hideLoading();
        if (data.offers && data.offers.length > 0) {
          this.displayOffers(data.offers);
          this.showNotification('Offers generated successfully!', 'success');
        } else {
          this.generateDemoOffers(authorityHook);
          this.showNotification('Using demo offers - AI temporarily unavailable', 'info');
        }
      })
      .catch(error => {
        this.hideLoading();
        this.generateDemoOffers(authorityHook);
        this.showNotification('Using demo offers - Generation failed', 'info');
      });
    },
    
    /**
     * SIMPLIFIED: Generate demo offers - checks for noEntryParam
     */
    generateDemoOffers: function(authorityHook) {
      // If no entry param, don't show demo offers
      if (window.MKCG_Offers_Data && window.MKCG_Offers_Data.noEntryParam) {
        this.showNotification('Please log in to generate offers', 'warning');
        return;
      }
      
      const offers = [
        'Free: "The Business Growth Audit Checklist" – A practical guide to identify opportunities in your business, complete with implementation templates and ROI calculators.',
        'Low-Ticket: "Growth Accelerator Workshop ($497)" – A 3-hour virtual workshop where business owners learn how to implement proven strategies with practical, same-day implementation.',
        'Premium: "Elite Business Growth Accelerator ($2,997)" – A 3-month done-with-you program where we implement complete systems customized for your business, including strategy, setup, and optimization.',
        'Group: "Growth Mastermind ($997)" – A 6-month virtual mastermind where business owners work together to implement proven strategies with weekly group coaching and peer accountability.',
        'VIP: "Done-For-You Growth Implementation ($7,497)" – Complete business transformation where we handle everything from strategy to execution, delivering a fully optimized system in 90 days.'
      ];
      
      this.displayOffers(offers);
    },
    
    /**
     * SIMPLIFIED: Display offers with Use buttons
     */
    displayOffers: function(offers) {
      const offersList = document.querySelector('#offers-list');
      if (!offersList) return;
      
      offersList.innerHTML = '';
      
      offers.forEach((offer, index) => {
        const offerNumber = index + 1;
        
        const offerItem = document.createElement('div');
        offerItem.className = 'offers-generator__offer-item';
        offerItem.innerHTML = `
          <div class="offers-generator__offer-header">
            <h4 class="offers-generator__offer-title">Offer ${offerNumber}</h4>
            <span class="offers-generator__offer-type">GENERATED</span>
          </div>
          <div class="offers-generator__offer-description">${this.escapeHtml(offer)}</div>
          <div class="offers-generator__offer-actions">
            <button class="generator__button generator__button--outline offers-generator__offer-copy" data-offer="${offerNumber}" data-text="${this.escapeHtml(offer)}">Copy Offer</button>
          </div>
        `;
        
        // Bind Copy button
        const copyBtn = offerItem.querySelector('.offers-generator__offer-copy');
        copyBtn.addEventListener('click', () => {
          this.useOffer(offer);
        });
        
        offersList.appendChild(offerItem);
      });
      
      // Show results section
      const results = document.querySelector('#offers-results');
      if (results) {
        results.classList.remove('generator__results--hidden');
        results.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },
    
    /**
     * SIMPLIFIED: Use offer (copy to clipboard)
     */
    useOffer: function(offerText) {
      this.copyToClipboard(offerText);
      this.showNotification('Offer copied to clipboard!', 'success');
      
      // Trigger cross-generator communication
      if (window.AppEvents) {
        window.AppEvents.trigger('offer:selected', {
          offerText: offerText,
          timestamp: Date.now()
        });
      }
    },
    
    /**
     * Copy all offers to clipboard
     */
    copyAllOffers: function() {
      const offerElements = document.querySelectorAll('.offers-generator__offer-description');
      if (offerElements.length === 0) {
        this.showNotification('No offers to copy', 'warning');
        return;
      }
      
      let allOffers = '';
      offerElements.forEach((element, index) => {
        allOffers += `${index + 1}. ${element.textContent}\n\n`;
      });
      
      this.copyToClipboard(allOffers);
      this.showNotification('All offers copied to clipboard!', 'success');
    },
    
    /**
     * Copy text to clipboard
     */
    copyToClipboard: function(text) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
          .catch(() => this.fallbackCopy(text));
      } else {
        this.fallbackCopy(text);
      }
    },
    
    /**
     * Fallback copy method
     */
    fallbackCopy: function(text) {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand('copy');
      } catch (err) {
        console.error('Copy failed:', err);
      }
      document.body.removeChild(textarea);
    },
    
    /**
     * HTML escape utility
     */
    escapeHtml: function(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
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
      const loading = document.querySelector('#offers-generator-loading');
      if (loading) {
        loading.classList.remove('generator__loading--hidden');
      }
      
      // Also show the overlay if it exists
      const overlay = document.querySelector('#offers-loading-overlay');
      if (overlay) {
        overlay.style.display = 'flex';
      }
    },
    
    /**
     * SIMPLIFIED: Hide loading
     */
    hideLoading: function() {
      const loading = document.querySelector('#offers-generator-loading');
      if (loading) {
        loading.classList.add('generator__loading--hidden');
      }
      
      // Also hide the overlay if it exists
      const overlay = document.querySelector('#offers-loading-overlay');
      if (overlay) {
        overlay.style.display = 'none';
      }
    }
  };

  // SIMPLIFIED: Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    // CRITICAL FIX: Only initialize if this generator's DOM elements exist
    const offersContainer = document.querySelector('.offers-generator');
    if (!offersContainer) {
      console.log('🎯 Offers Generator: DOM elements not found - skipping initialization');
      return;
    }
    
    console.log('🎯 Offers Generator: DOM Ready - Starting simple initialization');
    OffersGenerator.init();
  });

  // Make globally available
  window.OffersGenerator = OffersGenerator;
  
  console.log('✅ SIMPLIFIED Offers Generator loaded - Following Topics Generator pattern');

})();
