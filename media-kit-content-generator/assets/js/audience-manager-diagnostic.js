/**
 * Audience Manager Diagnostic Script
 * Comprehensive testing of audience manager functionality
 * 
 * This script will identify why the audience manager is not working:
 * - Check DOM elements exist
 * - Test event handler attachment
 * - Verify JavaScript functions are available
 * - Test manual audience addition
 * - Check for JavaScript errors
 */

(function() {
    'use strict';
    
    console.log('🔍 AUDIENCE MANAGER DIAGNOSTIC - Starting comprehensive test...');
    
    const AudienceManagerDiagnostic = {
        
        /**
         * Check if all required DOM elements exist
         */
        checkDOMElements: function() {
            console.log('\n🏗️ Checking DOM Elements...');
            
            const elements = {
                'Tag Input': '#tag_input',
                'Add Button': '#add_tag', 
                'Tags Container': '#tags_container',
                'WHO Field': '#mkcg-who',
                'Authority Hook Builder': '#topics-generator-authority-hook-builder',
                'Audience Count': '#audience-count',
                'Selected Count': '#selected-count'
            };
            
            const results = {};
            let foundElements = 0;
            
            Object.keys(elements).forEach(name => {
                const selector = elements[name];
                const element = document.querySelector(selector);
                const exists = !!element;
                
                results[name] = {
                    selector: selector,
                    exists: exists,
                    visible: exists ? element.offsetParent !== null : false,
                    value: exists ? (element.value || element.textContent || '').trim() : null
                };
                
                if (exists) {
                    foundElements++;
                    console.log(`✅ ${name} (${selector}): FOUND`, exists ? `- visible: ${results[name].visible}` : '');
                    if (results[name].value) {
                        console.log(`   Current value: "${results[name].value}"`);
                    }
                } else {
                    console.log(`❌ ${name} (${selector}): NOT FOUND`);
                }
            });
            
            console.log(`\n📊 DOM Elements Summary: ${foundElements}/${Object.keys(elements).length} found`);
            
            // Check if Authority Hook Builder is visible
            const builder = document.querySelector('#topics-generator-authority-hook-builder');
            if (builder) {
                const isHidden = builder.classList.contains('generator__builder--hidden');
                console.log(`🏠 Authority Hook Builder visibility: ${isHidden ? 'HIDDEN' : 'VISIBLE'}`);
                if (isHidden) {
                    console.log('💡 SOLUTION: Click "Edit Components" button to show the builder');
                }
            }
            
            return results;
        },
        
        /**
         * Test event handlers and JavaScript functionality
         */
        testEventHandlers: function() {
            console.log('\n⚡ Testing Event Handlers...');
            
            // Test if authority hook builder script is loaded
            const hasAuthorityHookBuilder = typeof window.AuthorityHookBuilder !== 'undefined';
            console.log(`🔧 Authority Hook Builder Available: ${hasAuthorityHookBuilder}`);
            
            // Test add button click handler
            const addButton = document.querySelector('#add_tag');
            if (addButton) {
                console.log('🖱️ Testing Add Button click handler...');
                
                // Check if button has event listeners
                const hasListeners = addButton.onclick || getEventListeners(addButton);
                console.log(`   Has click handlers: ${!!hasListeners}`);
                
                // Try to simulate a click
                try {
                    const clickEvent = new Event('click', { bubbles: true });
                    addButton.dispatchEvent(clickEvent);
                    console.log('✅ Add button click simulation: SUCCESS');
                } catch (error) {
                    console.log('❌ Add button click simulation: ERROR -', error.message);
                }
            }
            
            // Test tag input enter handler  
            const tagInput = document.querySelector('#tag_input');
            if (tagInput) {
                console.log('⌨️ Testing Tag Input enter handler...');
                
                try {
                    tagInput.value = 'Test Audience';
                    const enterEvent = new KeyboardEvent('keypress', { 
                        key: 'Enter', 
                        bubbles: true 
                    });
                    tagInput.dispatchEvent(enterEvent);
                    console.log('✅ Tag input enter simulation: SUCCESS');
                    
                    // Clear test value
                    tagInput.value = '';
                } catch (error) {
                    console.log('❌ Tag input enter simulation: ERROR -', error.message);
                }
            }
            
            // Test example chip handlers
            console.log('🏷️ Testing Example Chip handlers...');
            const exampleChips = document.querySelectorAll('.tag__add-link');
            console.log(`   Found ${exampleChips.length} example chips with add links`);
            
            if (exampleChips.length > 0) {
                try {
                    const firstChip = exampleChips[0];
                    const chipClickEvent = new Event('click', { bubbles: true });
                    firstChip.dispatchEvent(chipClickEvent);
                    console.log('✅ Example chip click simulation: SUCCESS');
                } catch (error) {
                    console.log('❌ Example chip click simulation: ERROR -', error.message);
                }
            } else {
                console.log('⚠️ No example chips found - Authority Hook Builder may be hidden');
            }
            
            return {
                hasAuthorityHookBuilder,
                addButtonExists: !!addButton,
                tagInputExists: !!tagInput,
                exampleChipsCount: exampleChips.length
            };
        },
        
        /**
         * Test manual audience addition
         */
        testManualAudienceAddition: function() {
            console.log('\n🧪 Testing Manual Audience Addition...');
            
            // Check if audience manager functions are available
            const functions = {
                'addAudienceTag': typeof addAudienceTag !== 'undefined',
                'createVisualTag': typeof createVisualTag !== 'undefined', 
                'updateWhoField': typeof updateWhoField !== 'undefined',
                'removeAudienceTag': typeof removeAudienceTag !== 'undefined'
            };
            
            console.log('🔧 Available Functions:');
            Object.keys(functions).forEach(func => {
                console.log(`   ${func}: ${functions[func] ? '✅ AVAILABLE' : '❌ NOT FOUND'}`);
            });
            
            // Try to manually add an audience tag if functions are available
            if (typeof addAudienceTag !== 'undefined') {
                console.log('\n🎯 Attempting manual audience addition...');
                
                try {
                    // Add a test audience
                    addAudienceTag('Test Manual Audience', true);
                    console.log('✅ Manual addAudienceTag call: SUCCESS');
                    
                    // Check if tag appeared in container
                    const container = document.querySelector('#tags_container');
                    if (container) {
                        const tags = container.querySelectorAll('.audience-tag');
                        console.log(`📊 Tags now in container: ${tags.length}`);
                        
                        // Check WHO field was updated
                        const whoField = document.querySelector('#mkcg-who');
                        if (whoField) {
                            console.log(`📝 WHO field updated to: "${whoField.value}"`);
                        }
                        
                        // Clean up test tag
                        if (typeof removeAudienceTag !== 'undefined') {
                            removeAudienceTag('Test Manual Audience');
                            console.log('🧹 Test tag cleaned up');
                        }
                    }
                    
                } catch (error) {
                    console.log('❌ Manual audience addition: ERROR -', error.message);
                    console.error('Full error:', error);
                }
            } else {
                console.log('❌ addAudienceTag function not available - this is the root issue!');
            }
            
            return functions;
        },
        
        /**
         * Check JavaScript errors and console
         */
        checkJavaScriptErrors: function() {
            console.log('\n🚨 Checking for JavaScript Errors...');
            
            // Check if authority-hook-builder.js loaded properly
            const scriptsLoaded = {
                'authority-hook-builder.js': document.querySelector('script[src*="authority-hook-builder"]'),
                'topics-generator.js': document.querySelector('script[src*="topics-generator"]')
            };
            
            console.log('📜 Script Loading Status:');
            Object.keys(scriptsLoaded).forEach(script => {
                const loaded = !!scriptsLoaded[script];
                console.log(`   ${script}: ${loaded ? '✅ LOADED' : '❌ NOT FOUND'}`);
            });
            
            // Check for error indicators
            const tagsContainer = document.querySelector('#tags_container');
            if (tagsContainer && tagsContainer.children.length === 0) {
                console.log('🔍 Tags container is empty - normal if no audiences added yet');
            }
            
            return scriptsLoaded;
        },
        
        /**
         * Check Authority Hook Builder initialization status
         */
        checkInitializationStatus: function() {
            console.log('\n🚀 Checking Initialization Status...');
            
            // Check if Authority Hook Builder is initialized
            const initStatus = {
                'Authority Hook Builder Module': !!window.AuthorityHookBuilder,
                'Topics Generator Module': !!window.TopicsGenerator,
                'Global AJAX Function': typeof window.makeAjaxRequest !== 'undefined',
                'WordPress AJAX URL': !!window.ajaxurl,
                'MKCG Topics Data': !!window.MKCG_Topics_Data
            };
            
            Object.keys(initStatus).forEach(module => {
                const status = initStatus[module];
                console.log(`   ${module}: ${status ? '✅ READY' : '❌ NOT AVAILABLE'}`);
            });
            
            // Check specific initialization
            const builder = document.querySelector('#topics-generator-authority-hook-builder');
            if (builder) {
                const isVisible = !builder.classList.contains('generator__builder--hidden');
                console.log(`🏠 Authority Hook Builder State: ${isVisible ? 'VISIBLE' : 'HIDDEN'}`);
                
                if (!isVisible) {
                    console.log('💡 NOTE: Audience manager only works when Authority Hook Builder is visible');
                    console.log('   Click "Edit Components" to show it');
                }
            }
            
            return initStatus;
        },
        
        /**
         * Comprehensive fix attempt
         */
        attemptFix: function() {
            console.log('\n🔧 Attempting Audience Manager Fix...');
            
            // Step 1: Show Authority Hook Builder if hidden
            const builder = document.querySelector('#topics-generator-authority-hook-builder');
            if (builder && builder.classList.contains('generator__builder--hidden')) {
                console.log('Step 1: Showing Authority Hook Builder...');
                builder.classList.remove('generator__builder--hidden');
            }
            
            // Step 2: Wait for DOM to stabilize
            setTimeout(() => {
                
                // Step 3: Check if audience manager elements are now available
                const tagInput = document.querySelector('#tag_input');
                const addButton = document.querySelector('#add_tag');
                const tagsContainer = document.querySelector('#tags_container');
                
                if (!tagInput || !addButton || !tagsContainer) {
                    console.log('❌ Step 3: Audience manager elements still not found after showing builder');
                    return;
                }
                
                console.log('✅ Step 3: Audience manager elements found');
                
                // Step 4: Manually attach event handlers if they're missing
                console.log('Step 4: Manually attaching event handlers...');
                
                // Remove existing listeners to prevent duplicates
                addButton.removeEventListener('click', this.manualAddHandler);
                tagInput.removeEventListener('keypress', this.manualKeyHandler);
                
                // Add new event handlers
                this.manualAddHandler = () => {
                    console.log('🔘 Manual add button clicked');
                    const text = tagInput.value.trim();
                    if (text) {
                        this.manualAddAudience(text);
                        tagInput.value = '';
                    }
                };
                
                this.manualKeyHandler = (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        console.log('⌨️ Manual enter key pressed');
                        const text = tagInput.value.trim();
                        if (text) {
                            this.manualAddAudience(text);
                            tagInput.value = '';
                        }
                    }
                };
                
                addButton.addEventListener('click', this.manualAddHandler);
                tagInput.addEventListener('keypress', this.manualKeyHandler);
                
                console.log('✅ Step 4: Manual event handlers attached');
                
                // Step 5: Test functionality
                console.log('Step 5: Testing manual functionality...');
                tagInput.value = 'Test Fix Audience';
                this.manualAddAudience('Test Fix Audience');
                tagInput.value = '';
                
                console.log('✅ AUDIENCE MANAGER FIX ATTEMPT COMPLETE');
                console.log('💡 Try adding an audience now!');
                
            }, 500);
        },
        
        /**
         * Manual audience addition function
         */
        manualAddAudience: function(text) {
            if (!text || !text.trim()) return;
            
            console.log(`🎯 Adding audience manually: "${text}"`);
            
            const tagsContainer = document.querySelector('#tags_container');
            const whoField = document.querySelector('#mkcg-who');
            
            if (!tagsContainer) {
                console.log('❌ Tags container not found');
                return;
            }
            
            // Check if audience already exists
            const existingTags = tagsContainer.querySelectorAll('.audience-tag');
            const exists = Array.from(existingTags).some(tag => {
                const span = tag.querySelector('span');
                return span && span.textContent === text;
            });
            
            if (exists) {
                console.log('⚠️ Audience already exists');
                return;
            }
            
            // Create visual tag
            const tagElement = document.createElement('div');
            tagElement.className = 'audience-tag active';
            tagElement.innerHTML = `
                <input type="checkbox" class="credential-checkbox" checked> 
                <span>${this.escapeHtml(text)}</span> 
                <span class="credential-remove">&times;</span>
            `;
            
            // Add remove handler
            const removeBtn = tagElement.querySelector('.credential-remove');
            removeBtn.addEventListener('click', () => {
                tagElement.remove();
                this.updateWhoFieldManual();
                this.updateStatusManual();
                console.log(`🗑️ Removed audience: "${text}"`);
            });
            
            // Add checkbox handler
            const checkbox = tagElement.querySelector('.credential-checkbox');
            checkbox.addEventListener('change', () => {
                tagElement.classList.toggle('active', checkbox.checked);
                this.updateWhoFieldManual();
                this.updateStatusManual();
                console.log(`☑️ Toggled audience: "${text}" - checked: ${checkbox.checked}`);
            });
            
            tagsContainer.appendChild(tagElement);
            
            // Update WHO field and status
            this.updateWhoFieldManual();
            this.updateStatusManual();
            
            console.log(`✅ Added audience tag: "${text}"`);
        },
        
        /**
         * Manual WHO field update
         */
        updateWhoFieldManual: function() {
            const tagsContainer = document.querySelector('#tags_container');
            const whoField = document.querySelector('#mkcg-who');
            
            if (!tagsContainer || !whoField) return;
            
            // Get all checked audiences
            const checkedTags = Array.from(tagsContainer.querySelectorAll('.audience-tag')).filter(tag => {
                const checkbox = tag.querySelector('.credential-checkbox');
                return checkbox && checkbox.checked;
            }).map(tag => {
                const span = tag.querySelector('span');
                return span ? span.textContent : '';
            }).filter(text => text.trim());
            
            // Format naturally
            let whoValue = '';
            if (checkedTags.length === 1) {
                whoValue = checkedTags[0];
            } else if (checkedTags.length === 2) {
                whoValue = checkedTags.join(' and ');
            } else if (checkedTags.length > 2) {
                const last = checkedTags.pop();
                whoValue = checkedTags.join(', ') + ', and ' + last;
            }
            
            whoField.value = whoValue;
            
            // Trigger input event
            whoField.dispatchEvent(new Event('input', { bubbles: true }));
            
            console.log(`📝 Updated WHO field: "${whoValue}"`);
        },
        
        /**
         * Manual status update
         */
        updateStatusManual: function() {
            const tagsContainer = document.querySelector('#tags_container');
            const audienceCount = document.querySelector('#audience-count');
            const selectedCount = document.querySelector('#selected-count');
            
            if (!tagsContainer) return;
            
            const allTags = tagsContainer.querySelectorAll('.audience-tag').length;
            const checkedTags = tagsContainer.querySelectorAll('.audience-tag .credential-checkbox:checked').length;
            
            if (audienceCount) audienceCount.textContent = allTags;
            if (selectedCount) selectedCount.textContent = checkedTags;
            
            console.log(`📊 Status updated: ${allTags} total, ${checkedTags} selected`);
        },
        
        /**
         * HTML escape function
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Run complete diagnostic
         */
        runFullDiagnostic: function() {
            console.log('\n🩺 RUNNING COMPLETE AUDIENCE MANAGER DIAGNOSTIC...');
            console.log('================================================');
            
            const domCheck = this.checkDOMElements();
            const eventCheck = this.testEventHandlers();
            const initCheck = this.checkInitializationStatus();
            
            console.log('\n📋 DIAGNOSTIC SUMMARY:');
            console.log('=====================');
            
            // Identify the primary issue
            const builder = document.querySelector('#topics-generator-authority-hook-builder');
            const builderHidden = builder && builder.classList.contains('generator__builder--hidden');
            
            if (builderHidden) {
                console.log('🔍 PRIMARY ISSUE: Authority Hook Builder is hidden');
                console.log('💡 SOLUTION: Click "Edit Components" button to show it');
                console.log('   The audience manager only works when the builder is visible');
                return;
            }
            
            const tagInput = document.querySelector('#tag_input');
            const addButton = document.querySelector('#add_tag');
            const tagsContainer = document.querySelector('#tags_container');
            
            if (!tagInput || !addButton || !tagsContainer) {
                console.log('🔍 PRIMARY ISSUE: Audience manager DOM elements missing');
                console.log('💡 SOLUTION: Authority Hook Service may not be rendering properly');
                return;
            }
            
            if (!eventCheck.hasAuthorityHookBuilder) {
                console.log('🔍 PRIMARY ISSUE: Authority Hook Builder JavaScript not loaded');
                console.log('💡 SOLUTION: Check if authority-hook-builder.js is properly enqueued');
                return;
            }
            
            console.log('🔍 PRIMARY ISSUE: Event handlers may not be properly attached');
            console.log('💡 SOLUTION: Running automatic fix...');
            
            this.attemptFix();
        },
        
        /**
         * Emergency manual audience manager setup
         */
        emergencySetup: function() {
            console.log('\n🚨 EMERGENCY AUDIENCE MANAGER SETUP...');
            
            // Force show Authority Hook Builder
            const builder = document.querySelector('#topics-generator-authority-hook-builder');
            if (builder) {
                builder.classList.remove('generator__builder--hidden');
                console.log('✅ Forced Authority Hook Builder to show');
            }
            
            setTimeout(() => {
                
                // Create manual audience manager if elements don't exist
                const existingContainer = document.querySelector('#tags_container');
                if (!existingContainer) {
                    console.log('🏗️ Creating emergency audience manager...');
                    
                    const whoField = document.querySelector('#mkcg-who');
                    if (whoField) {
                        const container = document.createElement('div');
                        container.innerHTML = `
                            <div class="credentials-manager credentials-manager--primary">
                                <label>🎯 <strong>Emergency Audience Manager</strong> - Add and Select Your Target Audiences:</label>
                                <div class="input-container">
                                    <input type="text" id="tag_input" placeholder="Type an audience and press Enter">
                                    <button type="button" id="add_tag" class="button">Add Audience</button>
                                </div>
                                <div id="tags_container" class="tags-container--enhanced"></div>
                                <div class="audience-manager-status">
                                    <small class="status-text">📊 <span id="audience-count">0</span> audiences added | <span id="selected-count">0</span> selected</small>
                                </div>
                            </div>
                        `;
                        
                        whoField.parentNode.insertBefore(container, whoField.nextSibling);
                        console.log('✅ Emergency audience manager created');
                    }
                }
                
                // Attempt fix after ensuring elements exist
                this.attemptFix();
                
            }, 200);
        }
    };
    
    // Make globally available
    window.AudienceManagerDiagnostic = AudienceManagerDiagnostic;
    
    console.log('🩺 AUDIENCE MANAGER DIAGNOSTIC LOADED');
    console.log('📖 Usage:');
    console.log('   window.AudienceManagerDiagnostic.runFullDiagnostic() - Run complete diagnostic');
    console.log('   window.AudienceManagerDiagnostic.checkDOMElements() - Check if elements exist');
    console.log('   window.AudienceManagerDiagnostic.testEventHandlers() - Test JavaScript handlers');
    console.log('   window.AudienceManagerDiagnostic.attemptFix() - Try to fix the issue');
    console.log('   window.AudienceManagerDiagnostic.emergencySetup() - Emergency manual setup');
    
    // Auto-run diagnostic on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => AudienceManagerDiagnostic.runFullDiagnostic(), 1000);
        });
    } else {
        setTimeout(() => AudienceManagerDiagnostic.runFullDiagnostic(), 1000);
    }
    
})();
