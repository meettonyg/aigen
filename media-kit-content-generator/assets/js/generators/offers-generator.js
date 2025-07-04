/**
 * Offers Generator JavaScript - SIMPLIFIED VERSION
 * Based on Topics Generator pattern with offer-specific functionality
 * Implementation: Clean, maintainable code following established patterns
 */

(function() {
  'use strict';
  
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
      const editBtn = document.querySelector('#edit-authority-components');
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
      }
      
      if (editBtn) {
        editBtn.addEventListener('click', (e) => {
          e.preventDefault();
          this.toggleBuilder();
        });
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
        const element = document.querySelector(`#offers-${field.replace('_', '-')}`);\n        if (element) {\n          element.addEventListener('change', () => {\n            this.businessData[field] = element.value;\n          });\n        }\n      });\n      \n      // Generate offers button\n      const generateBtn = document.querySelector('#generate-offers-btn');\n      if (generateBtn) {\n        generateBtn.addEventListener('click', () => {\n          this.generateOffers();\n        });\n      }\n      \n      // Copy all offers button\n      const copyAllBtn = document.querySelector('#copy-all-offers-btn');\n      if (copyAllBtn) {\n        copyAllBtn.addEventListener('click', () => {\n          this.copyAllOffers();\n        });\n      }\n      \n      // Regenerate offers button\n      const regenerateBtn = document.querySelector('#regenerate-offers-btn');\n      if (regenerateBtn) {\n        regenerateBtn.addEventListener('click', () => {\n          this.generateOffers();\n        });\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Update display\n     */\n    updateDisplay: function() {\n      this.updateAuthorityHook();\n    },\n    \n    /**\n     * SIMPLIFIED: Toggle Authority Hook Builder\n     */\n    toggleBuilder: function() {\n      const builder = document.querySelector('#offers-generator-authority-hook-builder');\n      if (!builder) return;\n      \n      const isHidden = builder.classList.contains('offers-generator__builder--hidden');\n      \n      if (isHidden) {\n        builder.classList.remove('offers-generator__builder--hidden');\n        console.log('✅ Authority Hook Builder shown');\n      } else {\n        builder.classList.add('offers-generator__builder--hidden');\n        console.log('✅ Authority Hook Builder hidden');\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Update Authority Hook display\n     */\n    updateAuthorityHook: function() {\n      const hookText = `I help ${this.fields.who || 'your audience'} ${this.fields.what || 'achieve their goals'} when ${this.fields.when || 'they need help'} ${this.fields.how || 'through your method'}.`;\n      \n      const displayElement = document.querySelector('#offers-generator-authority-hook-text');\n      if (displayElement) {\n        displayElement.textContent = hookText;\n      }\n      \n      // Update hidden field for form submission\n      const hiddenField = document.querySelector('#mkcg-authority-hook');\n      if (hiddenField) {\n        hiddenField.value = hookText;\n      }\n      \n      // Trigger cross-generator communication\n      if (window.AppEvents) {\n        window.AppEvents.trigger('authority-hook:updated', {\n          text: hookText,\n          components: this.fields,\n          timestamp: Date.now()\n        });\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Generate offers using simple AJAX\n     */\n    generateOffers: function() {\n      const authorityHook = document.querySelector('#offers-generator-authority-hook-text')?.textContent || \n                          document.querySelector('#mkcg-authority-hook')?.value;\n      \n      if (!authorityHook || authorityHook.trim() === '') {\n        this.showNotification('Please build your authority hook first', 'warning');\n        return;\n      }\n      \n      // Validate business fields\n      if (!this.businessData.business_type) {\n        this.showNotification('Please select your business type', 'warning');\n        return;\n      }\n      \n      if (!this.businessData.target_audience) {\n        this.showNotification('Please describe your target audience', 'warning');\n        return;\n      }\n      \n      this.showLoading();\n      \n      // Use simple AJAX system\n      makeAjaxRequest('mkcg_generate_offers', {\n        authority_hook: authorityHook,\n        business_type: this.businessData.business_type,\n        target_audience: this.businessData.target_audience,\n        price_range: this.businessData.price_range,\n        delivery_method: this.businessData.delivery_method,\n        offer_count: this.businessData.offer_count\n      })\n      .then(data => {\n        this.hideLoading();\n        if (data.offers && data.offers.length > 0) {\n          this.displayOffers(data.offers);\n          this.showNotification('Offers generated successfully!', 'success');\n        } else {\n          this.generateDemoOffers(authorityHook);\n          this.showNotification('Using demo offers - AI temporarily unavailable', 'info');\n        }\n      })\n      .catch(error => {\n        this.hideLoading();\n        this.generateDemoOffers(authorityHook);\n        this.showNotification('Using demo offers - Generation failed', 'info');\n      });\n    },\n    \n    /**\n     * SIMPLIFIED: Generate demo offers - checks for noEntryParam\n     */\n    generateDemoOffers: function(authorityHook) {\n      // If no entry param, don't show demo offers\n      if (window.MKCG_Offers_Data && window.MKCG_Offers_Data.noEntryParam) {\n        this.showNotification('Please log in to generate offers', 'warning');\n        return;\n      }\n      \n      const offers = [\n        'Free: \"The Business Growth Audit Checklist\" – A practical guide to identify opportunities in your business, complete with implementation templates and ROI calculators.',\n        'Low-Ticket: \"Growth Accelerator Workshop ($497)\" – A 3-hour virtual workshop where business owners learn how to implement proven strategies with practical, same-day implementation.',\n        'Premium: \"Elite Business Growth Accelerator ($2,997)\" – A 3-month done-with-you program where we implement complete systems customized for your business, including strategy, setup, and optimization.',\n        'Group: \"Growth Mastermind ($997)\" – A 6-month virtual mastermind where business owners work together to implement proven strategies with weekly group coaching and peer accountability.',\n        'VIP: \"Done-For-You Growth Implementation ($7,497)\" – Complete business transformation where we handle everything from strategy to execution, delivering a fully optimized system in 90 days.'\n      ];\n      \n      this.displayOffers(offers);\n    },\n    \n    /**\n     * SIMPLIFIED: Display offers with Use buttons\n     */\n    displayOffers: function(offers) {\n      const offersList = document.querySelector('#offers-list');\n      if (!offersList) return;\n      \n      offersList.innerHTML = '';\n      \n      offers.forEach((offer, index) => {\n        const offerNumber = index + 1;\n        \n        const offerItem = document.createElement('div');\n        offerItem.className = 'offer';\n        offerItem.innerHTML = `\n          <div class=\"offer__title\">Offer ${offerNumber}:</div>\n          <div class=\"offer__description\">${this.escapeHtml(offer)}</div>\n          <button class=\"button button--use\" data-offer=\"${offerNumber}\" data-text=\"${this.escapeHtml(offer)}\">Use Offer</button>\n        `;\n        \n        // Bind Use button\n        const useBtn = offerItem.querySelector('.button--use');\n        useBtn.addEventListener('click', () => {\n          this.useOffer(offer);\n        });\n        \n        offersList.appendChild(offerItem);\n      });\n      \n      // Show results section\n      const results = document.querySelector('#offers-results');\n      if (results) {\n        results.style.display = 'block';\n        results.scrollIntoView({ behavior: 'smooth', block: 'start' });\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Use offer (copy to clipboard)\n     */\n    useOffer: function(offerText) {\n      this.copyToClipboard(offerText);\n      this.showNotification('Offer copied to clipboard!', 'success');\n      \n      // Trigger cross-generator communication\n      if (window.AppEvents) {\n        window.AppEvents.trigger('offer:selected', {\n          offerText: offerText,\n          timestamp: Date.now()\n        });\n      }\n    },\n    \n    /**\n     * Copy all offers to clipboard\n     */\n    copyAllOffers: function() {\n      const offerElements = document.querySelectorAll('.offer__description');\n      if (offerElements.length === 0) {\n        this.showNotification('No offers to copy', 'warning');\n        return;\n      }\n      \n      let allOffers = '';\n      offerElements.forEach((element, index) => {\n        allOffers += `${index + 1}. ${element.textContent}\\n\\n`;\n      });\n      \n      this.copyToClipboard(allOffers);\n      this.showNotification('All offers copied to clipboard!', 'success');\n    },\n    \n    /**\n     * Copy text to clipboard\n     */\n    copyToClipboard: function(text) {\n      if (navigator.clipboard && navigator.clipboard.writeText) {\n        navigator.clipboard.writeText(text)\n          .catch(() => this.fallbackCopy(text));\n      } else {\n        this.fallbackCopy(text);\n      }\n    },\n    \n    /**\n     * Fallback copy method\n     */\n    fallbackCopy: function(text) {\n      const textarea = document.createElement('textarea');\n      textarea.value = text;\n      document.body.appendChild(textarea);\n      textarea.select();\n      try {\n        document.execCommand('copy');\n      } catch (err) {\n        console.error('Copy failed:', err);\n      }\n      document.body.removeChild(textarea);\n    },\n    \n    /**\n     * HTML escape utility\n     */\n    escapeHtml: function(text) {\n      const div = document.createElement('div');\n      div.textContent = text;\n      return div.innerHTML;\n    },\n    \n    /**\n     * SIMPLIFIED: Show notification\n     */\n    showNotification: function(message, type = 'info') {\n      if (window.showNotification) {\n        window.showNotification(message, type);\n      } else {\n        console.log(`${type.toUpperCase()}: ${message}`);\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Show loading\n     */\n    showLoading: function() {\n      const loading = document.querySelector('#offers-loading-overlay');\n      if (loading) {\n        loading.style.display = 'flex';\n      }\n    },\n    \n    /**\n     * SIMPLIFIED: Hide loading\n     */\n    hideLoading: function() {\n      const loading = document.querySelector('#offers-loading-overlay');\n      if (loading) {\n        loading.style.display = 'none';\n      }\n    }\n  };\n\n  // SIMPLIFIED: Initialize when DOM is ready\n  document.addEventListener('DOMContentLoaded', function() {\n    console.log('🎯 Offers Generator: DOM Ready - Starting simple initialization');\n    OffersGenerator.init();\n  });\n\n  // Make globally available\n  window.OffersGenerator = OffersGenerator;\n  \n  console.log('✅ SIMPLIFIED Offers Generator loaded - Following Topics Generator pattern');\n\n})();