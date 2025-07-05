/**
 * Authority Hook Builder - Complete Integrated Solution (Clean Slate Version)
 *
 * Handles all Authority Hook Builder functionality without any default value logic.
 *
 * @package Media_Kit_Content_Generator
 * @version 3.0
 */

(function() {
    'use strict';
    
    let audienceTags = [];
    let initialized = false;
    let templateData = null;
    
    console.log('🚀 Authority Hook Builder loading (Clean Slate v3.0)...');
    
    function init() {
        if (initialized) return;
        console.log('🔧 Initializing Authority Hook Builder (Clean Slate)...');
        loadTemplateData();
        prePopulateFields();
        setupClearButtons();
        setupAudienceManager();
        setupExampleChips();
        setupLiveUpdates();
        loadExistingAudiences();
        initialized = true;
        console.log('✅ Authority Hook Builder ready (Clean Slate)!');
    }
    
    function loadTemplateData() {
        console.log('📥 Loading template data from available sources...');
        let dataSource = 'none';
        
        if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.authorityHook) {
            templateData = window.MKCG_Topics_Data.authorityHook;
            dataSource = 'MKCG_Topics_Data';
        } else if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.authorityHook) {
            templateData = window.MKCG_Questions_Data.authorityHook;
            dataSource = 'MKCG_Questions_Data';
        } else if (window.MKCG_Offers_Data && window.MKCG_Offers_Data.authorityHook) {
            templateData = window.MKCG_Offers_Data.authorityHook;
            dataSource = 'MKCG_Offers_Data';
        }
        
        if (templateData) {
            console.log('✅ Template data loaded from:', dataSource);
            console.log('📋 Template data contents:', templateData);
        } else {
            console.log('❌ No template data found from any source');
        }
    }

    function prePopulateFields() {
        if (!templateData) return;
        
        const fieldMappings = { 'who': 'mkcg-who', 'what': 'mkcg-result', 'when': 'mkcg-when', 'how': 'mkcg-how' };
        let populatedCount = 0;
        
        Object.keys(fieldMappings).forEach(key => {
            const fieldId = fieldMappings[key];
            const value = templateData[key] || '';
            const field = document.getElementById(fieldId);
            
            if (value && field && !field.value) {
                field.value = value;
                populatedCount++;
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        
        if (populatedCount > 0) {
            console.log(`✅ Pre-populated ${populatedCount} fields`);
            setTimeout(updateAuthorityHook, 100);
        }
    }

    function setupClearButtons() {
        document.addEventListener('click', e => {
            if (e.target.classList.contains('field__clear')) {
                const fieldId = e.target.getAttribute('data-field-id');
                if (fieldId === 'mkcg-who') clearAllAudiences();
                else if (fieldId) document.getElementById(fieldId).value = '';
                updateAuthorityHook();
            }
        });
    }

    function setupAudienceManager() {
        const tagInput = document.getElementById('tag_input');
        const addButton = document.getElementById('add_tag');
        if (!tagInput || !addButton) return;
        
        const add = () => {
            const text = tagInput.value.trim();
            if (text) addAudienceTag(text);
            tagInput.value = '';
        };
        
        addButton.addEventListener('click', add);
        tagInput.addEventListener('keypress', e => e.key === 'Enter' && (e.preventDefault(), add()));
    }

    function addAudienceTag(text, checked = true) {
        if (!text || audienceTags.find(tag => tag.text === text)) return;
        const tagData = { text, checked };
        audienceTags.push(tagData);
        createVisualTag(tagData);
        updateWhoField();
        updateStatus();
    }

    function createVisualTag(tagData) {
        const container = document.getElementById('tags_container');
        if (!container) return;
        const tagEl = document.createElement('div');
        tagEl.className = 'audience-tag' + (tagData.checked ? ' active' : '');
        tagEl.innerHTML = `<input type="checkbox" ${tagData.checked ? 'checked' : ''}> <span>${escapeHtml(tagData.text)}</span> <span class="credential-remove">&times;</span>`;
        
        tagEl.querySelector('.credential-remove').addEventListener('click', () => removeAudienceTag(tagData.text));
        tagEl.querySelector('input').addEventListener('change', function() {
            tagData.checked = this.checked;
            tagEl.classList.toggle('active', this.checked);
            updateWhoField();
            updateStatus();
        });
        container.appendChild(tagEl);
    }
    
    function removeAudienceTag(text) {
        audienceTags = audienceTags.filter(tag => tag.text !== text);
        const tags = document.querySelectorAll('#tags_container .audience-tag');
        tags.forEach(tagEl => { if(tagEl.querySelector('span').textContent === text) tagEl.remove(); });
        updateWhoField();
        updateStatus();
    }

    function clearAllAudiences() {
        audienceTags = [];
        document.getElementById('tags_container').innerHTML = '';
        document.getElementById('mkcg-who').value = '';
        updateStatus();
        updateAuthorityHook();
    }

    function updateWhoField() {
        const checked = audienceTags.filter(t => t.checked).map(t => t.text);
        let text = '';
        if (checked.length === 1) text = checked[0];
        else if (checked.length === 2) text = checked.join(' and ');
        else if (checked.length > 2) text = checked.slice(0, -1).join(', ') + ', and ' + checked.slice(-1);
        
        document.getElementById('mkcg-who').value = text;
        updateAuthorityHook();
    }

    function updateStatus() {
        const total = audienceTags.length;
        const checked = audienceTags.filter(tag => tag.checked).length;
        const audienceCount = document.getElementById('audience-count');
        const selectedCount = document.getElementById('selected-count');
        if (audienceCount) audienceCount.textContent = total;
        if (selectedCount) selectedCount.textContent = checked;
    }

    function setupExampleChips() { /* This can remain as is, it provides helpful UI hints not values */ }

    function setupLiveUpdates() {
        ['mkcg-who', 'mkcg-result', 'mkcg-when', 'mkcg-how'].forEach(id => {
            const field = document.getElementById(id);
            if (field) field.addEventListener('input', updateAuthorityHook);
        });
    }

    function updateAuthorityHook() {
        const who = document.getElementById('mkcg-who')?.value || '';
        const result = document.getElementById('mkcg-result')?.value || '';
        const when = document.getElementById('mkcg-when')?.value || '';
        const how = document.getElementById('mkcg-how')?.value || '';
        
        const isEmpty = !who && !result && !when && !how;
        const hook = isEmpty ? '' : `I help ${who} ${result} when ${when} ${how}.`;
        const html = isEmpty ? '' : `I help <span class="authority-hook__highlight">${escapeHtml(who)}</span> <span class="authority-hook__highlight">${escapeHtml(result)}</span> when <span class="authority-hook__highlight">${escapeHtml(when)}</span> <span class="authority-hook__highlight">${escapeHtml(how)}</span>.`;
        
        document.querySelectorAll('[id$="-authority-hook-text"], #authority-hook-content').forEach(el => el.innerHTML = html);
        const hiddenField = document.getElementById('mkcg-authority-hook');
        if(hiddenField) hiddenField.value = hook;

        document.dispatchEvent(new CustomEvent('authority-hook-updated', { detail: { who, what: result, when, how, completeHook: hook } }));
    }

    function loadExistingAudiences() {
        const whoField = document.getElementById('mkcg-who');
        if (!whoField || !whoField.value.trim() || audienceTags.length > 0) return;
        
        whoField.value.trim().split(/,\s*and\s*|\s*and\s*|,\s*/).filter(Boolean).forEach(text => addAudienceTag(text));
        updateStatus();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Simplified initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();