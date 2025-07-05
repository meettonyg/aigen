/**
 * Authority Hook Builder - Complete Integrated Solution
 * 
 * Handles all Authority Hook Builder functionality:
 * 1. Field pre-population from template data
 * 2. Audience management (add/remove/check/clear)
 * 3. Live Authority Hook updates
 * 4. Example chip functionality
 * 5. Clear button handling
 * 
 * @package Media_Kit_Content_Generator
 * @version 2.1
 */

(function() {
    'use strict';
    
    let audienceTags = [];
    let initialized = false;
    let templateData = null;
    
    console.log('🚀 Authority Hook Builder loading (INTEGRATED VERSION)...');
    
    // INTEGRATED: Initialize with data loading and UI setup
    function init() {
        console.log('🔧 Initializing Authority Hook Builder (INTEGRATED)...');
        
        if (initialized) {
            console.log('⚠️ Already initialized, skipping...');
            return;
        }
        
        // STEP 1: Load template data from available sources
        loadTemplateData();
        
        // STEP 2: Pre-populate fields from template data
        prePopulateFields();
        
        // STEP 3: Setup UI functionality
        setupClearButtons();
        setupAudienceManager();
        setupExampleChips();
        setupLiveUpdates();
        
        // STEP 4: Load existing audiences from WHO field
        loadExistingAudiences();
        
        initialized = true;
        console.log('✅ Authority Hook Builder ready (INTEGRATED)!');
    }
    
    // INTEGRATED: Load template data from multiple sources
    function loadTemplateData() {
        console.log('📥 Loading template data from available sources...');
        
        let dataSource = 'none';
        
        // Method 1: Check window.MKCG_Topics_Data
        if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.authorityHook) {
            templateData = window.MKCG_Topics_Data.authorityHook;
            dataSource = 'MKCG_Topics_Data';
        }
        // Method 2: Check window.MKCG_Questions_Data  
        else if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.authorityHook) {
            templateData = window.MKCG_Questions_Data.authorityHook;
            dataSource = 'MKCG_Questions_Data';
        }
        // Method 3: Check window.MKCG_Offers_Data
        else if (window.MKCG_Offers_Data && window.MKCG_Offers_Data.authorityHook) {
            templateData = window.MKCG_Offers_Data.authorityHook;
            dataSource = 'MKCG_Offers_Data';
        }
        // Method 4: Extract from hidden field as fallback
        else {
            templateData = extractFromHiddenField();
            dataSource = 'hidden_field';
        }
        
        if (templateData) {
            console.log('✅ Template data loaded from:', dataSource, templateData);
        } else {
            console.log('⚠️ No template data found');
        }
    }
    
    // INTEGRATED: Extract data from hidden field as fallback
    function extractFromHiddenField() {
        const hiddenField = document.getElementById('mkcg-authority-hook');
        if (hiddenField && hiddenField.value) {
            const completeHook = hiddenField.value;
            if (completeHook.includes('I help')) {
                console.log('📄 Extracting from complete hook:', completeHook);
                return parseCompleteHook(completeHook);
            }
        }
        return null;
    }
    
    // INTEGRATED: Parse complete hook to extract components
    function parseCompleteHook(hook) {
        const match = hook.match(/I help (.+?) (.+?) when (.+?) (.+?)\./);
        if (match) {
            return {
                who: match[1].trim(),
                what: match[2].trim(),
                when: match[3].trim(),
                how: match[4].trim(),
                complete: hook
            };
        }
        return null;
    }
    
    // INTEGRATED: Pre-populate fields from template data
    function prePopulateFields() {
        if (!templateData) {
            console.log('⚠️ No template data available for field population');
            return;
        }
        
        console.log('📋 Pre-populating Authority Hook fields from template data...');
        
        const fieldMappings = {
            'who': 'mkcg-who',
            'what': 'mkcg-result', // Note: 'what' maps to 'result' field
            'when': 'mkcg-when', 
            'how': 'mkcg-how'
        };
        
        let populatedCount = 0;
        
        Object.keys(fieldMappings).forEach(key => {
            const fieldId = fieldMappings[key];
            const value = templateData[key] || '';
            
            if (value && value.trim()) {
                const field = document.getElementById(fieldId);
                if (field && !field.value) { // Only populate if field is empty
                    field.value = value;
                    populatedCount++;
                    console.log(`✅ Pre-populated ${fieldId} with: "${value}"`);
                    
                    // Special handling for WHO field - extract audiences
                    if (key === 'who' && value.includes(' and ') || value.includes(', ')) {
                        extractAudiencesFromWhoField(value);
                    }
                } else if (!field) {
                    console.warn(`❌ Field not found: ${fieldId}`);
                }
            }
        });
        
        if (populatedCount > 0) {
            console.log(`✅ Successfully pre-populated ${populatedCount} fields`);
            // Update Authority Hook display after population
            setTimeout(updateAuthorityHook, 100);
        }
    }
    
    // INTEGRATED: Extract audiences from WHO field for audience builder
    function extractAudiencesFromWhoField(whoValue) {
        if (!whoValue || !whoValue.trim()) return;
        
        console.log('🔍 Extracting audiences from WHO field:', whoValue);
        
        // Split on various separators
        const audiences = whoValue
            .split(/,\\s*and\\s*|\\s*and\\s*|,\\s*/)
            .map(s => s.trim())
            .filter(Boolean);
        
        // Add each audience as a tag
        audiences.forEach(audience => {
            if (audience && !audienceTags.find(tag => tag.text === audience)) {
                addAudienceTag(audience, true);
            }
        });
        
        console.log('✅ Extracted audiences:', audiences);
    }
    
    // UI FUNCTIONALITY: Clear buttons
    function setupClearButtons() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('field__clear')) {
                const fieldId = e.target.getAttribute('data-field-id');
                console.log('🗑️ Clear button clicked for:', fieldId);
                
                if (fieldId === 'mkcg-who') {
                    clearAllAudiences();
                } else if (fieldId) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.value = '';
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        console.log('✅ Cleared field:', fieldId);
                    }
                }
                
                updateAuthorityHook();
            }
        });
    }
    
    // UI FUNCTIONALITY: Audience manager
    function setupAudienceManager() {
        const tagInput = document.getElementById('tag_input');
        const addButton = document.getElementById('add_tag');
        
        if (!tagInput || !addButton) {
            console.log('⚠️ Audience manager elements not found');
            return;
        }
        
        console.log('✅ Audience manager elements found');
        
        // Add button click
        addButton.addEventListener('click', function() {
            const text = tagInput.value.trim();
            if (text) {
                addAudienceTag(text);
                tagInput.value = '';
            }
        });
        
        // Enter key in input
        tagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const text = tagInput.value.trim();
                if (text) {
                    addAudienceTag(text);
                    tagInput.value = '';
                }
            }
        });
        
        console.log('✅ Audience manager setup complete');
    }
    
    // UI FUNCTIONALITY: Add audience tag
    function addAudienceTag(text, checked = true) {
        const trimmed = text.trim();
        if (!trimmed) return;
        
        // Check for duplicates
        if (audienceTags.find(tag => tag.text === trimmed)) {
            console.log('⚠️ Duplicate audience:', trimmed);
            return;
        }
        
        // Add to array
        const tagData = { text: trimmed, checked: checked };
        audienceTags.push(tagData);
        
        // Create visual tag
        createVisualTag(tagData);
        
        // Update WHO field and status
        updateWhoField();
        updateStatus();
        
        console.log('✅ Added audience:', trimmed);
    }
    
    // UI FUNCTIONALITY: Create visual tag
    function createVisualTag(tagData) {
        const container = document.getElementById('tags_container');
        if (!container) {
            console.warn('❌ Tags container not found');
            return;
        }
        
        const tagEl = document.createElement('div');
        tagEl.className = 'audience-tag';
        
        // Add checked class if needed
        if (tagData.checked) {
            tagEl.classList.add('active');
        }
        
        tagEl.innerHTML = `
            <input type="checkbox" ${tagData.checked ? 'checked' : ''} class="credential-checkbox"> 
            <span>${escapeHtml(tagData.text)}</span> 
            <span class="credential-remove" onclick="removeAudienceTag('${escapeHtml(tagData.text)}')">&times;</span>
        `;
        
        // Add checkbox change listener
        const checkbox = tagEl.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', function() {
            const tag = audienceTags.find(t => t.text === tagData.text);
            if (tag) {
                tag.checked = checkbox.checked;
                // Update visual state
                if (checkbox.checked) {
                    tagEl.classList.add('active');
                } else {
                    tagEl.classList.remove('active');
                }
                updateWhoField();
                updateStatus();
            }
        });
        
        container.appendChild(tagEl);
    }
    
    // UI FUNCTIONALITY: Remove audience tag
    window.removeAudienceTag = function(text) {
        // Remove from array
        audienceTags = audienceTags.filter(tag => tag.text !== text);
        
        // Remove from DOM
        const container = document.getElementById('tags_container');
        if (container) {
            const tags = container.querySelectorAll('.audience-tag');
            tags.forEach(tagEl => {
                const span = tagEl.querySelector('span:not(.credential-remove)');
                if (span && span.textContent === text) {
                    tagEl.remove();
                }
            });
        }
        
        updateWhoField();
        updateStatus();
        console.log('🗑️ Removed audience:', text);
    };
    
    // UI FUNCTIONALITY: Clear all audiences
    function clearAllAudiences() {
        audienceTags = [];
        
        const container = document.getElementById('tags_container');
        if (container) {
            container.innerHTML = '';
        }
        
        const whoField = document.getElementById('mkcg-who');
        if (whoField) {
            whoField.value = '';
        }
        
        updateStatus();
        updateAuthorityHook();
        console.log('🗑️ Cleared all audiences');
    }
    
    // UI FUNCTIONALITY: Update WHO field with proper formatting
    function updateWhoField() {
        const checkedAudiences = audienceTags
            .filter(tag => tag.checked)
            .map(tag => tag.text);
        
        let formattedText = '';
        if (checkedAudiences.length === 0) {
            formattedText = '';
        } else if (checkedAudiences.length === 1) {
            formattedText = checkedAudiences[0];
        } else if (checkedAudiences.length === 2) {
            formattedText = checkedAudiences.join(' and ');
        } else {
            const last = checkedAudiences.pop();
            formattedText = checkedAudiences.join(', ') + ', and ' + last;
        }
        
        const whoField = document.getElementById('mkcg-who');
        if (whoField) {
            whoField.value = formattedText;
        }
        
        updateAuthorityHook();
    }
    
    // UI FUNCTIONALITY: Update status counters
    function updateStatus() {
        const total = audienceTags.length;
        const checked = audienceTags.filter(tag => tag.checked).length;
        
        const audienceCount = document.getElementById('audience-count');
        const selectedCount = document.getElementById('selected-count');
        
        if (audienceCount) audienceCount.textContent = total;
        if (selectedCount) selectedCount.textContent = checked;
    }
    
    // UI FUNCTIONALITY: Setup example chip functionality
    function setupExampleChips() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-list') || 
                e.target.classList.contains('tag__add-link')) {
                e.preventDefault();
                
                let value = e.target.getAttribute('data-value');
                let target = e.target.closest('[data-target]')?.getAttribute('data-target');
                
                // Check parent elements for data
                if (!value) {
                    const parent = e.target.closest('[data-value]');
                    if (parent) {
                        value = parent.getAttribute('data-value');
                        target = parent.getAttribute('data-target');
                    }
                }
                
                if (!value) return;
                
                console.log('📌 Example chip clicked:', value, 'target:', target);
                
                // Add to appropriate field
                if (!target || target === 'mkcg-who') {
                    addAudienceTag(value);
                } else {
                    const field = document.getElementById(target);
                    if (field) {
                        field.value = value;
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                    } else {
                        console.warn('❌ Target field not found:', target);
                    }
                }
                
                // Visual feedback
                const originalText = e.target.textContent;
                e.target.textContent = '✓ Added';
                e.target.style.color = '#4caf50';
                
                setTimeout(() => {
                    e.target.textContent = originalText;
                    e.target.style.color = '';
                }, 1500);
                
                updateAuthorityHook();
            }
        });
    }
    
    // UI FUNCTIONALITY: Setup live updates
    function setupLiveUpdates() {
        const fields = ['mkcg-who', 'mkcg-result', 'mkcg-when', 'mkcg-how'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updateAuthorityHook);
                field.addEventListener('change', updateAuthorityHook);
                console.log('✅ Live updates setup for:', fieldId);
            } else {
                console.warn('❌ Field not found for live updates:', fieldId);
            }
        });
    }
    
    // CORE FUNCTIONALITY: Update Authority Hook display
    function updateAuthorityHook() {
        const who = document.getElementById('mkcg-who')?.value || '';
        const result = document.getElementById('mkcg-result')?.value || '';
        const when = document.getElementById('mkcg-when')?.value || '';
        const how = document.getElementById('mkcg-how')?.value || '';
        
        // Check if all components are empty
        const isEmpty = !who && !result && !when && !how;
        const authorityHook = isEmpty ? '' : `I help ${who} ${result} when ${when} ${how}.`;
        
        // Update multiple display elements across generators
        const displaySelectors = [
            'topics-generator-authority-hook-text',
            'questions-generator-authority-hook-text',
            'offers-generator-authority-hook-text',
            'authority-hook-content'
        ];
        
        displaySelectors.forEach(selector => {
            const display = document.getElementById(selector);
            if (display) {
                if (isEmpty) {
                    display.innerHTML = '';
                } else {
                    display.innerHTML = `I help <span class="authority-hook__highlight">${escapeHtml(who)}</span> <span class="authority-hook__highlight">${escapeHtml(result)}</span> when <span class="authority-hook__highlight">${escapeHtml(when)}</span> <span class="authority-hook__highlight">${escapeHtml(how)}</span>.`;
                }
            }
        });
        
        // Update hidden field
        const hiddenField = document.getElementById('mkcg-authority-hook');
        if (hiddenField) {
            hiddenField.value = authorityHook;
        }
        
        console.log('📝 Authority Hook updated:', authorityHook);
        
        // Dispatch custom event for other components
        document.dispatchEvent(new CustomEvent('authority-hook-updated', {
            detail: {
                who: who,
                what: result,
                when: when,
                how: how,
                completeHook: authorityHook
            }
        }));
    }
    
    // UI FUNCTIONALITY: Load existing audiences from WHO field
    function loadExistingAudiences() {
        const whoField = document.getElementById('mkcg-who');
        if (!whoField || !whoField.value.trim()) {
            console.log('⚠️ No existing WHO field value to load audiences from');
            return;
        }
        
        const existingValue = whoField.value.trim();
        console.log('📥 Loading existing audiences from WHO field:', existingValue);
        
        // Don't reload if audiences are already loaded
        if (audienceTags.length > 0) {
            console.log('⚠️ Audiences already loaded, skipping...');
            return;
        }
        
        extractAudiencesFromWhoField(existingValue);
        updateStatus();
    }
    
    // Utility function
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Enhanced initialization with multiple triggers
    function initializeWhenReady() {
        // Try immediate initialization
        init();
        
        // Also try after DOM content loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        }
        
        // Try after a small delay for dynamic content
        setTimeout(init, 500);
        
        // Try after longer delay as final fallback
        setTimeout(init, 2000);
    }
    
    // Initialize
    initializeWhenReady();
    
    // Expose for debugging and external access
    window.AuthorityHookBuilder = {
        init,
        updateAuthorityHook,
        addAudienceTag,
        clearAllAudiences,
        audienceTags: () => audienceTags,
        templateData: () => templateData
    };
    
    console.log('✅ Authority Hook Builder script loaded (INTEGRATED VERSION)');
    
})();