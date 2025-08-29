/**
 * Impact Intro Builder - Complete Vanilla JavaScript Solution
 *
 * Handles all Impact Intro Builder functionality without jQuery dependencies.
 * Adapted from authority-hook-builder.js with Impact Intro specific functionality.
 *
 * @package Media_Kit_Content_Generator
 * @version 3.0
 */

(function() {
    'use strict';
    
    let credentialTags = [];
    let initialized = false;
    let templateData = null;
    
    console.log('🚀 Impact Intro Builder loading v3.0 (Vanilla JS)...');
    
    function init() {
        if (initialized) return;
        console.log('🔧 Initializing Impact Intro Builder...');
        loadTemplateData();
        prePopulateFields();
        setupClearButtons();
        setupCredentialManager();
        setupExampleChips();
        setupLiveUpdates();
        loadExistingCredentials();
        initialized = true;
        console.log('✅ Impact Intro Builder ready!');
    }
    
    function loadTemplateData() {
        console.log('📥 Loading template data from available sources...');
        let dataSource = 'none';
        
        if (window.MKCG_Biography_Data && window.MKCG_Biography_Data.impactIntro) {
            templateData = window.MKCG_Biography_Data.impactIntro;
            dataSource = 'MKCG_Biography_Data';
        } else if (window.MKCG_Topics_Data && window.MKCG_Topics_Data.impactIntro) {
            templateData = window.MKCG_Topics_Data.impactIntro;
            dataSource = 'MKCG_Topics_Data';
        } else if (window.MKCG_Questions_Data && window.MKCG_Questions_Data.impactIntro) {
            templateData = window.MKCG_Questions_Data.impactIntro;
            dataSource = 'MKCG_Questions_Data';
        } else if (window.MKCG_Offers_Data && window.MKCG_Offers_Data.impactIntro) {
            templateData = window.MKCG_Offers_Data.impactIntro;
            dataSource = 'MKCG_Offers_Data';
        } else if (window.MKCG_Guest_Intro_Data && window.MKCG_Guest_Intro_Data.impactIntro) {
            templateData = window.MKCG_Guest_Intro_Data.impactIntro;
            dataSource = 'MKCG_Guest_Intro_Data';
        } else if (window.MKCG_Tagline_Data && window.MKCG_Tagline_Data.impactIntro) {
            templateData = window.MKCG_Tagline_Data.impactIntro;
            dataSource = 'MKCG_Tagline_Data';
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
        
        const fieldMappings = { 'where': 'mkcg-where', 'why': 'mkcg-why' };
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
            setTimeout(updateImpactIntro, 100);
        }
    }

    function setupClearButtons() {
        document.addEventListener('click', e => {
            if (e.target.classList.contains('field__clear')) {
                const fieldId = e.target.getAttribute('data-field-id');
                if (fieldId === 'mkcg-where') clearAllCredentials();
                else if (fieldId) {
                    const field = document.getElementById(fieldId);
                    if (field) field.value = '';
                }
                updateImpactIntro();
            }
        });
    }

    function setupCredentialManager() {
        const credentialInput = document.getElementById('credential_input');
        const addButton = document.getElementById('add_credential');
        if (!credentialInput || !addButton) return;
        
        const add = () => {
            const text = credentialInput.value.trim();
            if (text) addCredentialTag(text);
            credentialInput.value = '';
        };
        
        addButton.addEventListener('click', add);
        credentialInput.addEventListener('keypress', e => e.key === 'Enter' && (e.preventDefault(), add()));
    }

    function addCredentialTag(text, checked = true) {
        if (!text || credentialTags.find(tag => tag.text === text)) return;
        const tagData = { text, checked };
        credentialTags.push(tagData);
        createVisualCredentialTag(tagData);
        updateWhereField();
        updateCredentialStatus();
    }

    function createVisualCredentialTag(tagData) {
        const container = document.getElementById('credentials_container');
        if (!container) return;
        
        const tagEl = document.createElement('div');
        tagEl.className = 'audience-tag' + (tagData.checked ? ' active' : '');
        tagEl.innerHTML = `<input type="checkbox" ${tagData.checked ? 'checked' : ''}> <span>${escapeHtml(tagData.text)}</span> <span class="credential-remove">&times;</span>`;
        
        tagEl.querySelector('.credential-remove').addEventListener('click', () => removeCredentialTag(tagData.text));
        tagEl.querySelector('input').addEventListener('change', function() {
            tagData.checked = this.checked;
            tagEl.classList.toggle('active', this.checked);
            updateWhereField();
            updateCredentialStatus();
        });
        container.appendChild(tagEl);
    }
    
    function removeCredentialTag(text) {
        credentialTags = credentialTags.filter(tag => tag.text !== text);
        const tags = document.querySelectorAll('#credentials_container .audience-tag');
        tags.forEach(tagEl => { 
            if(tagEl.querySelector('span').textContent === text) tagEl.remove(); 
        });
        updateWhereField();
        updateCredentialStatus();
    }

    function clearAllCredentials() {
        credentialTags = [];
        const container = document.getElementById('credentials_container');
        if (container) container.innerHTML = '';
        const whereField = document.getElementById('mkcg-where');
        if (whereField) whereField.value = '';
        updateCredentialStatus();
        updateImpactIntro();
    }

    function updateWhereField() {
        const checked = credentialTags.filter(t => t.checked).map(t => t.text);
        let text = '';
        if (checked.length === 1) text = checked[0];
        else if (checked.length === 2) text = checked.join(' and ');
        else if (checked.length > 2) text = checked.slice(0, -1).join(', ') + ', and ' + checked.slice(-1);
        
        const whereField = document.getElementById('mkcg-where');
        if (whereField) whereField.value = text;
        updateImpactIntro();
    }

    function updateCredentialStatus() {
        const total = credentialTags.length;
        const checked = credentialTags.filter(tag => tag.checked).length;
        const credentialCount = document.getElementById('credential-count');
        const selectedCount = document.getElementById('selected-credential-count');
        if (credentialCount) credentialCount.textContent = total;
        if (selectedCount) selectedCount.textContent = checked;
    }

    function setupExampleChips() {
        // Handle clicks on example chips - delegate to document for dynamic content
        document.addEventListener('click', function(e) {
            // Check if clicked element or its parent is a tag with add link
            const addLink = e.target.closest('.tag__add-link, .add-to-list');
            const tag = e.target.closest('.tag');
            
            if (addLink && tag) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get the target field and value from the tag
                const targetFieldId = tag.getAttribute('data-target');
                const value = tag.getAttribute('data-value');
                
                console.log(`🎯 Impact Intro example chip clicked: target=${targetFieldId}, value=${value}`);
                
                if (targetFieldId && value) {
                    // SPECIAL HANDLING FOR WHERE FIELD - Add to Credential Manager instead of direct field
                    if (targetFieldId === 'mkcg-where') {
                        // Add to credential management system
                        addCredentialTag(value, true);
                        console.log(`✅ Added "${value}" to Credential Manager`);
                        
                        // Visual feedback
                        addLink.textContent = '✓ Added to List';
                        addLink.style.backgroundColor = '#d4edda';
                        addLink.style.color = '#155724';
                        
                        // Reset after 2 seconds
                        setTimeout(() => {
                            addLink.textContent = addLink.classList.contains('add-to-list') ? '+ Add to List' : '+ Add';
                            addLink.style.backgroundColor = '';
                            addLink.style.color = '';
                        }, 2000);
                    } else {
                        // NORMAL HANDLING FOR OTHER FIELDS - Direct field population
                        const targetField = document.getElementById(targetFieldId);
                        if (targetField) {
                            // Add the value to the field
                            targetField.value = value;
                            
                            // Trigger input event to update other systems
                            targetField.dispatchEvent(new Event('input', { bubbles: true }));
                            targetField.dispatchEvent(new Event('change', { bubbles: true }));
                            
                            // Update impact intro display
                            updateImpactIntro();
                            
                            console.log(`✅ Added example "${value}" to field ${targetFieldId}`);
                            
                            // Visual feedback
                            addLink.textContent = '✓ Added';
                            addLink.style.backgroundColor = '#d4edda';
                            addLink.style.color = '#155724';
                            
                            // Reset after 2 seconds
                            setTimeout(() => {
                                addLink.textContent = addLink.classList.contains('add-to-list') ? '+ Add to List' : '+ Add';
                                addLink.style.backgroundColor = '';
                                addLink.style.color = '';
                            }, 2000);
                        } else {
                            console.warn(`⚠️ Target field not found: ${targetFieldId}`);
                        }
                    }
                } else {
                    console.warn('⚠️ Tag missing data-target or data-value attributes');
                }
            }
        });
        
        // Also handle clicks on the entire tag (not just the add link)
        document.addEventListener('click', function(e) {
            const tag = e.target.closest('.tag:not(:has(.tag__add-link))');
            if (tag && !e.target.closest('.tag__add-link')) {
                const targetFieldId = tag.getAttribute('data-target');
                const value = tag.getAttribute('data-value');
                
                if (targetFieldId && value) {
                    const targetField = document.getElementById(targetFieldId);
                    if (targetField) {
                        targetField.value = value;
                        targetField.dispatchEvent(new Event('input', { bubbles: true }));
                        updateImpactIntro();
                        console.log(`✅ Added example "${value}" to field ${targetFieldId} (via tag click)`);
                    }
                }
            }
        });
        
        console.log('✅ Impact Intro example chips click handlers set up');
    }

    function setupLiveUpdates() {
        ['mkcg-where', 'mkcg-why'].forEach(id => {
            const field = document.getElementById(id);
            if (field) field.addEventListener('input', updateImpactIntro);
        });
    }

    function updateImpactIntro() {
        const where = document.getElementById('mkcg-where')?.value || '';
        const why = document.getElementById('mkcg-why')?.value || '';
        
        const isEmpty = !where && !why;
        const intro = isEmpty ? '' : `I've ${where}. My mission is to ${why}.`;
        const html = isEmpty ? '' : `I've <span class="impact-intro__highlight">${escapeHtml(where)}</span>. My mission is to <span class="impact-intro__highlight">${escapeHtml(why)}</span>.`;
        
        document.querySelectorAll('[id$="-impact-intro-text"], #impact-intro-content').forEach(el => el.innerHTML = html);
        const hiddenField = document.getElementById('mkcg-impact-intro');
        if(hiddenField) hiddenField.value = intro;

        document.dispatchEvent(new CustomEvent('impact-intro-updated', { 
            detail: { where, why, completeIntro: intro } 
        }));
    }

    function loadExistingCredentials() {
        const whereField = document.getElementById('mkcg-where');
        if (!whereField || !whereField.value.trim() || credentialTags.length > 0) return;
        
        // Split existing WHERE value into individual credentials
        whereField.value.trim().split(/,\s*and\s*|\s*and\s*|,\s*/).filter(Boolean).forEach(text => addCredentialTag(text));
        updateCredentialStatus();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // CENTRALIZED UX ENHANCEMENT: Impact Intro Builder Toggle Button State Management
    const ImpactIntroUX = {
        /**
         * Update toggle button state based on builder visibility
         * @param {string} buttonId - ID of the toggle button
         * @param {string} builderId - ID of the builder container
         */
        updateToggleButtonState: function(buttonId, builderId) {
            const toggleBtn = document.getElementById(buttonId);
            const builder = document.getElementById(builderId);
            
            if (!toggleBtn || !builder) {
                console.warn(`⚠️ Impact Intro UX Enhancement: Missing elements - button: ${!!toggleBtn}, builder: ${!!builder}`);
                return;
            }
            
            const isHidden = builder.classList.contains('generator__builder--hidden');
            
            if (isHidden) {
                // Builder is closed - show "open" state
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                  </svg>
                  Edit Impact Intro
                `;
                toggleBtn.classList.remove('generator__button--secondary');
                toggleBtn.classList.add('generator__button--outline');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.setAttribute('title', 'Open the Impact Intro Builder to edit components');
            } else {
                // Builder is open - show "close" state
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18,15 12,9 6,15"></polyline>
                  </svg>
                  Hide Builder
                `;
                toggleBtn.classList.remove('generator__button--outline');
                toggleBtn.classList.add('generator__button--secondary');
                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.setAttribute('title', 'Close the Impact Intro Builder');
            }
            
            // Add subtle click animation feedback
            toggleBtn.style.transform = 'scale(0.98)';
            setTimeout(() => {
                toggleBtn.style.transform = 'scale(1)';
            }, 150);
            
            console.log(`✅ Impact Intro UX Enhancement: Button state updated to ${isHidden ? 'closed' : 'open'} for ${buttonId}`);
        },
        
        /**
         * Initialize button state on page load
         * @param {string} buttonId - ID of the toggle button
         * @param {string} builderId - ID of the builder container
         */
        initializeButtonState: function(buttonId, builderId) {
            const toggleBtn = document.getElementById(buttonId);
            const builder = document.getElementById(builderId);
            
            if (!toggleBtn || !builder) {
                // Elements might not be ready yet, try again later
                setTimeout(() => this.initializeButtonState(buttonId, builderId), 500);
                return;
            }
            
            const isHidden = builder.classList.contains('generator__builder--hidden');
            
            // Ensure button has initial icon and state
            if (isHidden) {
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                  </svg>
                  Edit Impact Intro
                `;
                toggleBtn.classList.add('generator__button--outline');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.setAttribute('title', 'Open the Impact Intro Builder to edit components');
            } else {
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18,15 12,9 6,15"></polyline>
                  </svg>
                  Hide Builder
                `;
                toggleBtn.classList.add('generator__button--secondary');
                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.setAttribute('title', 'Close the Impact Intro Builder');
            }
            
            console.log(`✅ Impact Intro UX Enhancement: Initial button state set for ${buttonId}`);
        },
        
        /**
         * Auto-initialize for common generator patterns
         */
        autoInitialize: function() {
            // Common generator patterns
            const patterns = [
                { button: 'biography-generator-toggle-impact-builder', builder: 'biography-generator-impact-intro-builder' },
                { button: 'guest-intro-generator-toggle-impact-builder', builder: 'guest-intro-generator-impact-intro-builder' },
                { button: 'tagline-generator-toggle-impact-builder', builder: 'tagline-generator-impact-intro-builder' },
                { button: 'impact-intro-generator-toggle-impact-builder', builder: 'impact-intro-generator-impact-intro-builder' }
            ];
            
            patterns.forEach(pattern => {
                this.initializeButtonState(pattern.button, pattern.builder);
            });
        }
    };
    
    // Make UX enhancements globally available
    window.ImpactIntroBuilder = window.ImpactIntroBuilder || {};
    window.ImpactIntroBuilder.updateToggleButtonState = ImpactIntroUX.updateToggleButtonState;
    window.ImpactIntroBuilder.initializeButtonState = ImpactIntroUX.initializeButtonState;
    window.ImpactIntroBuilder.autoInitialize = ImpactIntroUX.autoInitialize;
    
    // Auto-initialize for all generators
    setTimeout(() => {
        ImpactIntroUX.autoInitialize();
    }, 1000);
    
    // Enhanced debug function to check example chips
    window.MKCG_DebugImpactIntroChips = function() {
        console.log('🔍 Debugging Impact Intro example chips...');
        
        // Check for all possible chip selectors
        const tags = document.querySelectorAll('.tag[data-target="mkcg-where"], .tag[data-target="mkcg-why"]');
        const addLinks = document.querySelectorAll('.tag[data-target="mkcg-where"] .tag__add-link, .tag[data-target="mkcg-why"] .tag__add-link');
        const examples = document.querySelectorAll('.examples');
        
        console.log(`Found ${tags.length} Impact Intro .tag elements`);
        console.log(`Found ${addLinks.length} Impact Intro add link elements`);
        console.log(`Found ${examples.length} .examples sections`);
        
        // Check all possible builder IDs
        const builderIds = [
            'biography-generator-impact-intro-builder',
            'guest-intro-generator-impact-intro-builder', 
            'tagline-generator-impact-intro-builder',
            'impact-intro-generator-impact-intro-builder'
        ];
        
        let visibleBuilder = null;
        builderIds.forEach(id => {
            const builder = document.getElementById(id);
            if (builder && !builder.classList.contains('generator__builder--hidden')) {
                visibleBuilder = id;
            }
        });
        
        console.log(`Impact Intro Builder visible: ${visibleBuilder || 'None'}`);
        
        if (!visibleBuilder) {
            console.log('⚠️ Impact Intro Builder is hidden - chips may not be rendered yet');
            console.log('💡 Click "Edit Impact Intro" to show the builder and render the chips');
            return;
        }
        
        return {
            tags: tags.length,
            addLinks: addLinks.length,
            examples: examples.length,
            visibleBuilder: visibleBuilder
        };
    };
    
    // Credential Manager Diagnostic Function
    window.MKCG_DebugCredentialManager = function() {
        console.log('🔍 Debugging Credential Manager...');
        console.log('=====================================');
        
        // Check for elements
        const credentialInput = document.getElementById('credential_input');
        const addButton = document.getElementById('add_credential');
        const credentialsContainer = document.getElementById('credentials_container');
        const whereField = document.getElementById('mkcg-where');
        
        console.log('Element Status:');
        console.log(`  Credential Input: ${credentialInput ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Add Button: ${addButton ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Credentials Container: ${credentialsContainer ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  WHERE Field: ${whereField ? '✅ Found' : '❌ Not Found'}`);
        
        // Check credential tags state
        console.log('\nCredential Tags State:');
        console.log(`  Total credentials: ${credentialTags.length}`);
        console.log(`  Credentials array:`, credentialTags);
        
        // Check visual credentials
        const visualCredentials = document.querySelectorAll('#credentials_container .audience-tag');
        console.log(`\nVisual Credentials: ${visualCredentials.length} found`);
        visualCredentials.forEach((tag, index) => {
            const text = tag.querySelector('span:not(.credential-remove)')?.textContent || 'Unknown';
            const checked = tag.querySelector('input[type="checkbox"]')?.checked || false;
            console.log(`  ${index + 1}. "${text}" - ${checked ? 'Selected' : 'Not selected'}`);
        });
        
        // Check WHERE field value
        console.log(`\nWHERE Field Value: "${whereField ? whereField.value : 'N/A'}"`);
        
        return {
            elements: {
                credentialInput: !!credentialInput,
                addButton: !!addButton,
                credentialsContainer: !!credentialsContainer,
                whereField: !!whereField
            },
            state: {
                credentialTags: credentialTags.length,
                visualCredentials: visualCredentials.length,
                whereValue: whereField ? whereField.value : null
            }
        };
    };
    
    console.log('🔧 Impact Intro Builder loaded with credential manager support (Vanilla JS)');
    console.log('   Run window.MKCG_DebugImpactIntroChips() to test example chips');
    console.log('   Run window.MKCG_DebugCredentialManager() to test credential manager');

})();