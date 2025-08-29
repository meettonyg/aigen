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
    
    console.log('🚀 Authority Hook Builder loading v5.0...');
    
    function init() {
        if (initialized) return;
        console.log('🔧 Initializing Authority Hook Builder...');
        loadTemplateData();
        prePopulateFields();
        setupClearButtons();
        setupAudienceManager();
        setupExampleChips();
        setupLiveUpdates();
        loadExistingAudiences();
        initialized = true;
        console.log('✅ Authority Hook Builder ready!');
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
                
                console.log(`🎯 Example chip clicked: target=${targetFieldId}, value=${value}`);
                
                if (targetFieldId && value) {
                    // SPECIAL HANDLING FOR WHO FIELD - Add to Audience Manager instead of direct field
                    if (targetFieldId === 'mkcg-who') {
                        // Add to audience management system
                        addAudienceTag(value, true);
                        console.log(`✅ Added "${value}" to Audience Manager`);
                        
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
                            
                            // Update authority hook display
                            updateAuthorityHook();
                            
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
                        updateAuthorityHook();
                        console.log(`✅ Added example "${value}" to field ${targetFieldId} (via tag click)`);
                    }
                }
            }
        });
        
        console.log('✅ Example chips click handlers set up');
    }

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
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // CENTRALIZED UX ENHANCEMENT: Authority Hook Builder Toggle Button State Management
    // This handles the dynamic button states for ALL generators
    const AuthorityHookUX = {
        /**
         * Update toggle button state based on builder visibility
         * @param {string} buttonId - ID of the toggle button (e.g., 'topics-generator-toggle-builder')
         * @param {string} builderId - ID of the builder container (e.g., 'topics-generator-authority-hook-builder')
         */
        updateToggleButtonState: function(buttonId, builderId) {
            const toggleBtn = document.getElementById(buttonId);
            const builder = document.getElementById(builderId);
            
            if (!toggleBtn || !builder) {
                console.warn(`⚠️ UX Enhancement: Missing elements - button: ${!!toggleBtn}, builder: ${!!builder}`);
                return;
            }
            
            const isHidden = builder.classList.contains('generator__builder--hidden');
            
            if (isHidden) {
                // Builder is closed - show "open" state
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                  </svg>
                  Edit Components
                `;
                toggleBtn.classList.remove('generator__button--secondary');
                toggleBtn.classList.add('generator__button--outline');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.setAttribute('title', 'Open the Authority Hook Builder to edit components');
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
                toggleBtn.setAttribute('title', 'Close the Authority Hook Builder');
            }
            
            // Add subtle click animation feedback
            toggleBtn.style.transform = 'scale(0.98)';
            setTimeout(() => {
                toggleBtn.style.transform = 'scale(1)';
            }, 150);
            
            console.log(`✅ UX Enhancement: Button state updated to ${isHidden ? 'closed' : 'open'} for ${buttonId}`);
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
                  Edit Components
                `;
                toggleBtn.classList.add('generator__button--outline');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.setAttribute('title', 'Open the Authority Hook Builder to edit components');
            } else {
                toggleBtn.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18,15 12,9 6,15"></polyline>
                  </svg>
                  Hide Builder
                `;
                toggleBtn.classList.add('generator__button--secondary');
                toggleBtn.setAttribute('aria-expanded', 'true');
                toggleBtn.setAttribute('title', 'Close the Authority Hook Builder');
            }
            
            console.log(`✅ UX Enhancement: Initial button state set for ${buttonId}`);
        },
        
        /**
         * Auto-initialize for common generator patterns
         */
        autoInitialize: function() {
            // Common generator patterns
            const patterns = [
                { button: 'topics-generator-toggle-builder', builder: 'topics-generator-authority-hook-builder' },
                { button: 'offers-generator-toggle-builder', builder: 'offers-generator-authority-hook-builder' },
                { button: 'questions-generator-toggle-builder', builder: 'questions-generator-authority-hook-builder' },
                { button: 'biography-generator-toggle-builder', builder: 'biography-generator-authority-hook-builder' }
            ];
            
            patterns.forEach(pattern => {
                this.initializeButtonState(pattern.button, pattern.builder);
            });
        }
    };
    
    // Make UX enhancements globally available
    window.AuthorityHookBuilder = window.AuthorityHookBuilder || {};
    window.AuthorityHookBuilder.updateToggleButtonState = AuthorityHookUX.updateToggleButtonState;
    window.AuthorityHookBuilder.initializeButtonState = AuthorityHookUX.initializeButtonState;
    window.AuthorityHookBuilder.autoInitialize = AuthorityHookUX.autoInitialize;
    
    // Auto-initialize for all generators
    setTimeout(() => {
        AuthorityHookUX.autoInitialize();
    }, 1000);
    
    // Enhanced debug function to check example chips
    window.MKCG_DebugExampleChips = function() {
        console.log('🔍 Debugging example chips...');
        
        // Check for all possible chip selectors
        const tags = document.querySelectorAll('.tag');
        const addLinks = document.querySelectorAll('.tag__add-link, .add-to-list');
        const examples = document.querySelectorAll('.examples');
        
        console.log(`Found ${tags.length} .tag elements`);
        console.log(`Found ${addLinks.length} add link elements`);
        console.log(`Found ${examples.length} .examples sections`);
        
        // Check if Authority Hook Builder is visible
        const builder = document.getElementById('topics-generator-authority-hook-builder');
        const isBuilderVisible = builder && !builder.classList.contains('generator__builder--hidden');
        console.log(`Authority Hook Builder visible: ${isBuilderVisible}`);
        
        if (!isBuilderVisible) {
            console.log('⚠️ Authority Hook Builder is hidden - chips may not be rendered yet');
            console.log('💡 Click "Edit Components" to show the builder and render the chips');
            return;
        }
        
        // Check tabs
        const tabs = document.querySelectorAll('.tabs__panel');
        console.log(`Found ${tabs.length} tab panels`);
        
        tabs.forEach((panel, index) => {
            const panelTags = panel.querySelectorAll('.tag');
            const panelAddLinks = panel.querySelectorAll('.tag__add-link, .add-to-list');
            const panelExamples = panel.querySelectorAll('.examples');
            
            console.log(`Tab panel ${index + 1}:`, {
                tags: panelTags.length,
                addLinks: panelAddLinks.length,
                examples: panelExamples.length
            });
            
            if (panelTags.length > 0) {
                console.log(`  First tag in panel ${index + 1}:`, {
                    target: panelTags[0].getAttribute('data-target'),
                    value: panelTags[0].getAttribute('data-value'),
                    text: panelTags[0].textContent.trim().substring(0, 50)
                });
            }
        });
        
        // Test click simulation
        if (addLinks.length > 0) {
            console.log('🧪 Testing first add link click...');
            const firstAddLink = addLinks[0];
            firstAddLink.click();
            console.log('✅ Simulated click on first add link');
        } else {
            console.log('❌ No add links found to test');
        }
        
        // Check for show_examples option
        const builderContent = builder ? builder.innerHTML : '';
        const hasExamplesHTML = builderContent.includes('Examples:');
        console.log(`Examples HTML present: ${hasExamplesHTML}`);
        
        return {
            tags: tags.length,
            addLinks: addLinks.length,
            examples: examples.length,
            builderVisible: isBuilderVisible,
            hasExamplesHTML
        };
    };
    
    console.log('🔧 Authority Hook Builder loaded with example chips support');
    console.log('   Run window.MKCG_DebugExampleChips() to test example chips');
    console.log('   Run window.MKCG_DebugAudienceManager() to test audience manager');
    
    // Audience Manager Diagnostic Function
    window.MKCG_DebugAudienceManager = function() {
        console.log('🔍 Debugging Audience Manager...');
        console.log('=====================================');
        
        // Check for elements
        const tagInput = document.getElementById('tag_input');
        const addButton = document.getElementById('add_tag');
        const tagsContainer = document.getElementById('tags_container');
        const whoField = document.getElementById('mkcg-who');
        
        console.log('Element Status:');
        console.log(`  Tag Input: ${tagInput ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Add Button: ${addButton ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Tags Container: ${tagsContainer ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  WHO Field: ${whoField ? '✅ Found' : '❌ Not Found'}`);
        
        // Check audience tags state
        console.log('\nAudience Tags State:');
        console.log(`  Total tags: ${audienceTags.length}`);
        console.log(`  Tags array:`, audienceTags);
        
        // Check visual tags
        const visualTags = document.querySelectorAll('#tags_container .audience-tag');
        console.log(`\nVisual Tags: ${visualTags.length} found`);
        visualTags.forEach((tag, index) => {
            const text = tag.querySelector('span:not(.credential-remove)')?.textContent || 'Unknown';
            const checked = tag.querySelector('input[type="checkbox"]')?.checked || false;
            console.log(`  ${index + 1}. "${text}" - ${checked ? 'Selected' : 'Not selected'}`);
        });
        
        // Check WHO field value
        console.log(`\nWHO Field Value: "${whoField ? whoField.value : 'N/A'}"`);
        
        // Test adding an audience programmatically
        console.log('\n🧪 Testing programmatic add...');
        try {
            addAudienceTag('Test Audience ' + Date.now(), true);
            console.log('✅ Successfully added test audience');
        } catch (e) {
            console.error('❌ Failed to add test audience:', e);
        }
        
        // Check for event listeners
        console.log('\n🎯 Simulating button click...');
        if (addButton) {
            // Set a test value in the input
            if (tagInput) {
                tagInput.value = 'Click Test Audience';
            }
            // Simulate click
            addButton.click();
            console.log('✅ Button click simulated');
        } else {
            console.log('❌ Cannot simulate - button not found');
        }
        
        // Check Authority Hook Builder visibility
        const builder = document.querySelector('.authority-hook__builder');
        const builderContainer = document.querySelector('[id$="-authority-hook-builder"]');
        const isHidden = builderContainer && builderContainer.classList.contains('generator__builder--hidden');
        
        console.log('\n🏗️ Authority Hook Builder Status:');
        console.log(`  Builder element: ${builder ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Builder container: ${builderContainer ? '✅ Found' : '❌ Not Found'}`);
        console.log(`  Is hidden: ${isHidden ? 'Yes (click "Edit Components" to show)' : 'No (visible)'}`);
        
        if (isHidden) {
            console.log('\n💡 TIP: The Authority Hook Builder is hidden.');
            console.log('   Click the "Edit Components" button to show it and access the Audience Manager.');
        }
        
        return {
            elements: {
                tagInput: !!tagInput,
                addButton: !!addButton,
                tagsContainer: !!tagsContainer,
                whoField: !!whoField
            },
            state: {
                audienceTags: audienceTags.length,
                visualTags: visualTags.length,
                whoValue: whoField ? whoField.value : null
            },
            builderVisible: !isHidden
        };
    };
    
    // Auto-diagnose on load if there's an issue
    setTimeout(() => {
        const container = document.getElementById('tags_container');
        const button = document.getElementById('add_tag');
        if ((container || button) && audienceTags.length === 0) {
            console.log('⚠️ Audience Manager detected but no tags loaded.');
            console.log('   Run window.MKCG_DebugAudienceManager() for diagnostics.');
        }
    }, 2000);

})();