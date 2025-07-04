/**
 * Authority Hook Builder - FIXED JavaScript
 */

(function() {
    'use strict';
    
    let audienceTags = [];
    
    console.log('🚀 Authority Hook Builder loading...');
    
    // Wait for DOM and initialize
    function init() {
        console.log('🔧 Initializing Authority Hook Builder...');
        
        setupClearButtons();
        setupAudienceManager();
        setupExampleChips();
        setupLiveUpdates();
        loadExistingAudiences();
        
        console.log('✅ Authority Hook Builder ready!');
    }
    
    // Fix clear buttons (X buttons)
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
    
    // Setup audience tag manager
    function setupAudienceManager() {
        const tagInput = document.getElementById('tag_input');
        const addButton = document.getElementById('add_tag');
        
        if (!tagInput || !addButton) {
            console.log('⚠️ Audience manager elements not found');
            return;
        }
        
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
    
    // Add audience tag function
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
        
        // Update WHO field
        updateWhoField();
        updateStatus();
        
        console.log('✅ Added audience:', trimmed);
    }
    
    // Create visual tag element
    function createVisualTag(tagData) {
        const container = document.getElementById('tags_container');
        if (!container) return;
        
        const tagEl = document.createElement('div');
        tagEl.className = 'audience-tag';
        tagEl.style.cssText = 'display: inline-flex; align-items: center; background: #2196f3; color: white; padding: 8px 12px; border-radius: 20px; margin: 4px; font-size: 14px; gap: 8px;';
        
        tagEl.innerHTML = '<input type="checkbox" ' + (tagData.checked ? 'checked' : '') + ' style="margin: 0;"> <span>' + escapeHtml(tagData.text) + '</span> <span style="cursor: pointer; font-weight: bold; font-size: 16px;" onclick="removeAudienceTag(\'' + escapeHtml(tagData.text) + '\')">&times;</span>';
        
        // Add checkbox change listener
        const checkbox = tagEl.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', function() {
            const tag = audienceTags.find(t => t.text === tagData.text);
            if (tag) {
                tag.checked = checkbox.checked;
                updateWhoField();
                updateStatus();
            }
        });
        
        container.appendChild(tagEl);
    }
    
    // Remove audience tag
    window.removeAudienceTag = function(text) {
        // Remove from array
        audienceTags = audienceTags.filter(tag => tag.text !== text);
        
        // Remove from DOM
        const container = document.getElementById('tags_container');
        if (container) {
            const tags = container.querySelectorAll('.audience-tag');
            tags.forEach(tagEl => {
                const span = tagEl.querySelector('span');
                if (span && span.textContent === text) {
                    tagEl.remove();
                }
            });
        }
        
        updateWhoField();
        updateStatus();
        console.log('🗑️ Removed audience:', text);
    };
    
    // Clear all audiences
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
        console.log('🗑️ Cleared all audiences');
    }
    
    // Update WHO field with proper formatting
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
    
    // Update status counters
    function updateStatus() {
        const total = audienceTags.length;
        const checked = audienceTags.filter(tag => tag.checked).length;
        
        const audienceCount = document.getElementById('audience-count');
        const selectedCount = document.getElementById('selected-count');
        
        if (audienceCount) audienceCount.textContent = total;
        if (selectedCount) selectedCount.textContent = checked;
    }
    
    // Setup example chip adding
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
    
    // Setup live updates for Authority Hook
    function setupLiveUpdates() {
        const fields = ['mkcg-who', 'mkcg-result', 'mkcg-when', 'mkcg-how'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updateAuthorityHook);
                field.addEventListener('change', updateAuthorityHook);
            }
        });
    }
    
    // Update Authority Hook display
    function updateAuthorityHook() {
        const who = document.getElementById('mkcg-who')?.value || 'your audience';
        const result = document.getElementById('mkcg-result')?.value || 'achieve their goals';
        const when = document.getElementById('mkcg-when')?.value || 'they need help';
        const how = document.getElementById('mkcg-how')?.value || 'through your method';
        
        const authorityHook = 'I help ' + who + ' ' + result + ' when ' + when + ' ' + how + '.';
        
        // Update display element
        const display = document.getElementById('topics-generator-authority-hook-text');
        if (display) {
            display.innerHTML = 'I help <span class="authority-hook__highlight">' + escapeHtml(who) + '</span> <span class="authority-hook__highlight">' + escapeHtml(result) + '</span> when <span class="authority-hook__highlight">' + escapeHtml(when) + '</span> <span class="authority-hook__highlight">' + escapeHtml(how) + '</span>.';
        }
        
        // Update hidden field
        const hiddenField = document.getElementById('mkcg-authority-hook');
        if (hiddenField) {
            hiddenField.value = authorityHook;
        }
        
        console.log('📝 Authority Hook updated:', authorityHook);
    }
    
    // Load existing audiences from WHO field
    function loadExistingAudiences() {
        const whoField = document.getElementById('mkcg-who');
        if (!whoField || !whoField.value.trim()) return;
        
        const existingValue = whoField.value.trim();
        // Split on various separators
        const audiences = existingValue
            .split(/,\s*and\s*|\s*and\s*|,\s*/)
            .map(s => s.trim())
            .filter(Boolean);
        
        audiences.forEach(audience => {
            addAudienceTag(audience, true);
        });
        
        console.log('📥 Loaded existing audiences:', audiences);
    }
    
    // Utility function
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize when ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Also try after a delay for dynamic content
    setTimeout(init, 500);
    
})();
