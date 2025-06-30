<?php
/**
 * Topics Generator Template - BEM Methodology
 * Modern design with proper BEM class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// STANDALONE MODE: Simplified data loading for standalone operation
$template_data = [];

// Try to get data from generator instance if available
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
    $template_data = $generator_instance->get_template_data($entry_key);
    error_log('MKCG Topics Template: Got data from generator instance');
} else {
    // Fallback: Create default structure
    $entry_key = isset($_GET['entry']) ? sanitize_text_field($_GET['entry']) : '';
    $template_data = [
        'entry_id' => 0,
        'entry_key' => $entry_key,
        'authority_hook_components' => [
            'who' => 'your audience',
            'result' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method',
            'complete' => 'I help your audience achieve their goals when they need help through your method.'
        ],
        'form_field_values' => [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ],
        'has_entry' => false
    ];
    error_log('MKCG Topics Template: Using fallback data - generator not available');
}

// Extract data for easier access in template
$entry_id = $template_data['entry_id'];
$entry_key = $template_data['entry_key'];
$authority_hook_components = $template_data['authority_hook_components'];
$form_field_values = $template_data['form_field_values'];
$has_entry = $template_data['has_entry'];

error_log('MKCG Topics Template: Rendering with entry_id=' . $entry_id . ', has_entry=' . ($has_entry ? 'true' : 'false'));
?>

<div class="topics-generator">
    <div class="topics-generator__container">
        <div class="topics-generator__header">
            <h1 class="topics-generator__title">Create Your Interview Topics</h1>
        </div>
        
        <div class="topics-generator__content">
            <!-- LEFT PANEL -->
            <div class="topics-generator__panel topics-generator__panel--left">
                <!-- Introduction Text -->
                <p class="topics-generator__intro">
                    Generate 5 compelling podcast interview topics based on your authority hook and target audience. 
                    Topics will be tailored to highlight your expertise and attract podcast hosts.
                </p>
                
                <!-- Authority Hook Result -->
                <div class="topics-generator__authority-hook">
                    <div class="topics-generator__authority-hook-header">
                        <span class="topics-generator__authority-hook-icon">★</span>
                        <h3 class="topics-generator__authority-hook-title">Your Authority Hook</h3>
                        <span class="topics-generator__badge">AI GENERATED</span>
                    </div>
                    
                    <div class="topics-generator__authority-hook-content">
                        <p id="topics-generator-authority-hook-text"><?php echo esc_html($authority_hook_components['complete']); ?></p>
                    </div>
                    
                    <div class="topics-generator__authority-hook-actions">
                        <!-- Generate Button -->
                        <button class="topics-generator__button topics-generator__button--generate" id="topics-generator-generate-topics">
                            Generate Topics with AI
                        </button>
                        <button type="button" class="topics-generator__button topics-generator__button--edit" id="topics-generator-toggle-builder">
                            Edit Components
                        </button>
                    </div>
                </div>
                
                <!-- Authority Hook Builder - USES SHARED COMPONENT -->
                <div class="topics-generator__builder topics-generator__builder--hidden" id="topics-generator-authority-hook-builder">
                    <?php 
                    // Use the shared Authority Hook component
                    $generator_type = 'topics'; // Specify generator type
                    $current_values = [
                        'who' => $authority_hook_components['who'],
                        'result' => $authority_hook_components['result'],
                        'when' => $authority_hook_components['when'],
                        'how' => $authority_hook_components['how'],
                        'authority_hook' => $authority_hook_components['complete']
                    ];
                    $entry_id = $entry_id; // Pass entry ID to shared component
                    
                    // Debug: Check if shared component exists
                    $shared_component_path = MKCG_PLUGIN_PATH . 'templates/shared/authority-hook-component.php';
                    if (file_exists($shared_component_path)) {
                        include $shared_component_path;
                        error_log('MKCG Topics: Shared Authority Hook component included successfully');
                    } else {
                        error_log('MKCG Topics: ERROR - Shared component not found at: ' . $shared_component_path);
                        echo '<div style="background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0; border-radius: 4px;"><strong>⚠️ Development Notice:</strong> Enhanced Authority Hook component not found. Please check file path.</div>';
                    }
                    ?>
                </div>
                
                <!-- Loading indicator -->
                <div class="topics-generator__loading topics-generator__loading--hidden" id="topics-generator-loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                    </svg>
                    Generating topics...
                </div>
                
                <!-- Topics result - with "Use" buttons -->
                <div class="topics-generator__results topics-generator__results--hidden" id="topics-generator-topics-result">
                    <div class="topics-generator__topics-list" id="topics-generator-topics-list">
                        <!-- Generated topics will be listed here with "Use" buttons -->
                    </div>
                </div>
                
                <!-- Field Selection Modal -->
                <div class="topics-generator__modal" id="topics-generator-field-modal">
                    <div class="topics-generator__modal-content">
                        <div class="topics-generator__modal-header">
                            <h3 class="topics-generator__modal-title">Enter the field number to update (1-5):</h3>
                        </div>
                        <input type="number" min="1" max="5" class="field__input" id="topics-generator-field-number" value="1">
                        <div class="topics-generator__modal-actions">
                            <button class="button button--ai" id="topics-generator-modal-ok">OK</button>
                            <button class="button button--copy" id="topics-generator-modal-cancel">Cancel</button>
                        </div>
                    </div>
                </div>
                
                <!-- Form Fields (Formidable Form Integration) -->
                <div class="topics-generator__form">
                    <div class="topics-generator__form-field">
                        <div class="topics-generator__form-field-label">
                            <div class="topics-generator__form-field-number">1</div>
                            <div class="topics-generator__form-field-title">First Interview Topic</div>
                        </div>
                        <input type="text" class="topics-generator__form-field-input" id="topics-generator-topic-field-1" 
                               name="field_8498" data-field-id="8498" data-topic-number="1"
                               value="<?php echo esc_attr($form_field_values['topic_1'] ?? ''); ?>">
                        <div class="topics-generator__form-examples">
                            <p>Examples:</p>
                            <div class="topics-generator__form-example">How to create magnetic content that attracts ideal clients</div>
                            <div class="topics-generator__form-example">The 5 most common mistakes when scaling SaaS businesses</div>
                            <div class="topics-generator__form-example">Building a personal brand that stands out in crowded markets</div>
                        </div>
                    </div>
                    
                    <div class="topics-generator__form-field">
                        <div class="topics-generator__form-field-label">
                            <div class="topics-generator__form-field-number">2</div>
                            <div class="topics-generator__form-field-title">Second Interview Topic</div>
                        </div>
                        <input type="text" class="topics-generator__form-field-input" id="topics-generator-topic-field-2" 
                               name="field_8499" data-field-id="8499" data-topic-number="2"
                               value="<?php echo esc_attr($form_field_values['topic_2'] ?? ''); ?>">
                    </div>
                    
                    <div class="topics-generator__form-field">
                        <div class="topics-generator__form-field-label">
                            <div class="topics-generator__form-field-number">3</div>
                            <div class="topics-generator__form-field-title">Third Interview Topic</div>
                        </div>
                        <input type="text" class="topics-generator__form-field-input" id="topics-generator-topic-field-3" 
                               name="field_8500" data-field-id="8500" data-topic-number="3"
                               value="<?php echo esc_attr($form_field_values['topic_3'] ?? ''); ?>">
                    </div>
                    
                    <div class="topics-generator__form-field">
                        <div class="topics-generator__form-field-label">
                            <div class="topics-generator__form-field-number">4</div>
                            <div class="topics-generator__form-field-title">Fourth Interview Topic</div>
                        </div>
                        <input type="text" class="topics-generator__form-field-input" id="topics-generator-topic-field-4" 
                               name="field_8501" data-field-id="8501" data-topic-number="4"
                               value="<?php echo esc_attr($form_field_values['topic_4'] ?? ''); ?>">
                    </div>
                    
                    <div class="topics-generator__form-field">
                        <div class="topics-generator__form-field-label">
                            <div class="topics-generator__form-field-number">5</div>
                            <div class="topics-generator__form-field-title">Fifth Interview Topic</div>
                        </div>
                        <input type="text" class="topics-generator__form-field-input" id="topics-generator-topic-field-5" 
                               name="field_8502" data-field-id="8502" data-topic-number="5"
                               value="<?php echo esc_attr($form_field_values['topic_5'] ?? ''); ?>">
                    </div>
                    
                    <!-- Hidden fields for AJAX -->
                    <input type="hidden" id="topics-generator-entry-id" value="<?php echo esc_attr($entry_id); ?>">
                    <input type="hidden" id="topics-generator-entry-key" value="<?php echo esc_attr($entry_key); ?>">
                    <input type="hidden" id="topics-generator-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
                    <input type="hidden" id="topics-generator-topics-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
                </div>
            </div>
            
            <!-- RIGHT PANEL -->
            <div class="topics-generator__panel topics-generator__panel--right">
                <h2 class="topics-generator__guidance-header">Crafting Perfect Interview Topics</h2>
                <p class="topics-generator__guidance-subtitle">Strong interview topics provide value to listeners, suggest a structure for the conversation, and showcase your expertise. They should be focused on one concept at a time while remaining general enough to allow for discussion.</p>
                
                <div class="topics-generator__formula-box">
                    <span class="topics-generator__formula-label">APPROACH</span>
                    Provide <span class="topics-generator__highlight">solutions</span> that focus on <span class="topics-generator__highlight">one concept</span> per topic while remaining <span class="topics-generator__highlight">general enough</span> to expand upon.
                </div>
                
                <div class="topics-generator__process-step">
                    <div class="topics-generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <div class="topics-generator__process-content">
                        <h3 class="topics-generator__process-title">Focus on Value for Listeners</h3>
                        <p class="topics-generator__process-description">
                            Great topics provide actionable solutions that listeners can implement. Think about your audience's pain points and how your knowledge can help solve their problems. Avoid overly promotional topics and focus on delivering value first.
                        </p>
                    </div>
                </div>
                
                <div class="topics-generator__process-step">
                    <div class="topics-generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="topics-generator__process-content">
                        <h3 class="topics-generator__process-title">One Concept per Topic</h3>
                        <p class="topics-generator__process-description">
                            Each topic should focus on a single concept, similar to a blog post. This makes it easier for hosts to structure the interview and helps listeners follow along. You'll have the opportunity to go into detail during the conversation.
                        </p>
                    </div>
                </div>
                
                <div class="topics-generator__process-step">
                    <div class="topics-generator__process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="topics-generator__process-content">
                        <h3 class="topics-generator__process-title">Tailored to the Audience</h3>
                        <p class="topics-generator__process-description">
                            While you can have core topics you're prepared to discuss, the best approach is to tailor them to each podcast's specific audience. Research the show beforehand and adjust your topics to align with what their listeners would find most valuable.
                        </p>
                    </div>
                </div>
                
                <h3 class="topics-generator__examples-header">Example Topic Sets:</h3>
                
                <div class="topics-generator__example-card">
                    <strong>For a Marketing Podcast:</strong>
                    <p>1. The 3-step framework for landing high-profile podcast interviews</p>
                    <p>2. How to craft a compelling story that makes you memorable</p>
                    <p>3. Converting podcast appearances into high-ticket clients</p>
                </div>
                
                <div class="topics-generator__example-card">
                    <strong>For a Business Growth Podcast:</strong>
                    <p>1. The 5 most common mistakes when scaling SaaS businesses</p>
                    <p>2. Building a team that can operate without your daily involvement</p>
                    <p>3. Creating systems that allow for sustainable growth</p>
                </div>
                
                <div class="topics-generator__example-card">
                    <strong>For an Author/Content Creator Podcast:</strong>
                    <p>1. Turning your expertise into a bestselling book</p>
                    <p>2. Building an audience that eagerly awaits your content</p>
                    <p>3. Leveraging your book to open doors to speaking and media opportunities</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Topics: Template data loaded', {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>
    });
    
    window.MKCG_Topics_Data = {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $has_entry ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who']); ?>',
            result: '<?php echo esc_js($authority_hook_components['result']); ?>',
            when: '<?php echo esc_js($authority_hook_components['when']); ?>',
            how: '<?php echo esc_js($authority_hook_components['how']); ?>',
            complete: '<?php echo esc_js($authority_hook_components['complete']); ?>'
        },
        topics: {
            topic_1: '<?php echo esc_js($form_field_values['topic_1'] ?? ''); ?>',
            topic_2: '<?php echo esc_js($form_field_values['topic_2'] ?? ''); ?>',
            topic_3: '<?php echo esc_js($form_field_values['topic_3'] ?? ''); ?>',
            topic_4: '<?php echo esc_js($form_field_values['topic_4'] ?? ''); ?>',
            topic_5: '<?php echo esc_js($form_field_values['topic_5'] ?? ''); ?>'
        },
        dataSource: '<?php echo isset($generator_instance) ? 'generator_instance' : (isset($mkcg_topics_generator) ? 'global_generator' : 'fallback'); ?>'
    };
    
    console.log('✅ MKCG Topics: Final data loaded', window.MKCG_Topics_Data);
    
    // CRITICAL DEBUG: Check for immediate population
    if (window.MKCG_Topics_Data.hasEntry) {
        console.log('📋 MKCG Topics: Entry found - should populate automatically');
        
        // Check if authority hook text element exists and has content
        const hookText = document.getElementById('topics-generator-authority-hook-text');
        if (hookText) {
            console.log('✅ Authority hook element found with text:', hookText.textContent);
        } else {
            console.error('❌ Authority hook element not found - check selector mismatch');
        }
        
        // Check if topic fields exist and have values
        for (let i = 1; i <= 5; i++) {
            const field = document.getElementById(`topics-generator-topic-field-${i}`);
            if (field) {
                console.log(`✅ Topic ${i} field found with value:`, field.value);
            } else {
                console.error(`❌ Topic ${i} field not found`);
            }
        }
    } else {
        console.log('⚠️ MKCG Topics: No entry data - using defaults');
    }
</script>

<!-- JavaScript functionality loaded separately -->
<script type="text/javascript">
// IMMEDIATE DEBUG TEST
console.log('🚀 MKCG JavaScript: Script block started loading');
console.log('🔍 Current page URL:', window.location.href);
console.log('🔍 DOM ready state:', document.readyState);

// Test if basic elements exist immediately
setTimeout(() => {
    const button = document.getElementById('topics-generator-toggle-builder');
    const builder = document.getElementById('topics-generator-authority-hook-builder');
    console.log('🔍 IMMEDIATE TEST:');
    console.log('- Toggle button exists:', !!button);
    console.log('- Builder exists:', !!builder);
    if (button) {
        console.log('- Button classes:', button.className);
        console.log('- Button text:', button.textContent);
    }
    if (builder) {
        console.log('- Builder classes:', builder.className);
        console.log('- Builder hidden:', builder.classList.contains('topics-generator__builder--hidden'));
    }
}, 100);
// Toggle Authority Hook Builder visibility
function toggleAuthorityHookBuilder(event) {
    // Prevent any default behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const builder = document.getElementById('topics-generator-authority-hook-builder');
    const topicsButton = document.getElementById('topics-generator-toggle-builder');
    const sharedButton = document.getElementById('edit-authority-components');
    
    console.log('🔧 Toggle function called:', {
        builder: !!builder,
        topicsButton: !!topicsButton,
        sharedButton: !!sharedButton,
        builderHidden: builder ? builder.classList.contains('topics-generator__builder--hidden') : 'N/A'
    });
    
    if (builder) {
        const isHidden = builder.classList.contains('topics-generator__builder--hidden');
        
        if (isHidden) {
            builder.classList.remove('topics-generator__builder--hidden');
            
            // Update both button texts if they exist
            if (topicsButton) topicsButton.textContent = 'Hide Builder';
            if (sharedButton) sharedButton.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>Hide Builder';
            
            // Scroll to builder for better UX
            setTimeout(() => {
                builder.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
            console.log('✅ Authority Hook Builder shown');
        } else {
            builder.classList.add('topics-generator__builder--hidden');
            
            // Update both button texts if they exist
            if (topicsButton) topicsButton.textContent = 'Edit Components';
            if (sharedButton) sharedButton.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>Edit Components';
            
            console.log('✅ Authority Hook Builder hidden');
        }
        
        // Also try to trigger Authority Hook Builder initialization if present
        setTimeout(() => {
            if (window.authorityHookBuilder && typeof window.authorityHookBuilder.updateAuthorityHook === 'function') {
                window.authorityHookBuilder.updateAuthorityHook();
                console.log('✅ Authority Hook Builder re-initialized');
            }
        }, 200);
        
    } else {
        console.error('❌ Authority Hook Builder element not found:', {
            builder: !!builder
        });
    }
    
    return false; // Prevent any navigation
}

// Initialize event handlers and check for auto-show
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Topics Generator: DOM loaded, initializing Authority Hook Builder...');
    
    // CRITICAL: Set up event listeners for BOTH toggle buttons
    const button = document.getElementById('topics-generator-toggle-builder');
    const sharedButton = document.getElementById('edit-authority-components');
    
    if (button) {
        // Remove any existing event listeners and add new one
        button.removeEventListener('click', toggleAuthorityHookBuilder);
        button.addEventListener('click', function(event) {
            console.log('🔧 Topics Generator toggle button clicked!');
            toggleAuthorityHookBuilder(event);
        });
        console.log('✅ Topics Generator toggle button event listener attached');
    } else {
        console.error('❌ Topics Generator toggle button not found!');
    }
    
    if (sharedButton) {
        // Remove any existing event listeners and add new one
        sharedButton.removeEventListener('click', toggleAuthorityHookBuilder);
        sharedButton.addEventListener('click', function(event) {
            console.log('🔧 Shared component toggle button clicked!');
            toggleAuthorityHookBuilder(event);
        });
        console.log('✅ Shared component toggle button event listener attached');
    } else {
        console.warn('⚠️ Shared component toggle button not found (may not be visible yet)');
    }
    
    // Debug: Check if elements exist
    const builder = document.getElementById('topics-generator-authority-hook-builder');
    const authorityHookText = document.getElementById('topics-generator-authority-hook-text');
    
    console.log('Builder element found:', !!builder);
    console.log('Toggle button found:', !!button);
    console.log('Authority hook text found:', !!authorityHookText);
    
    if (authorityHookText) {
        console.log('Current authority hook text:', authorityHookText.textContent.trim());
        
        // Auto-show builder for default/empty authority hooks
        if (authorityHookText.textContent.trim() === 'I help your audience achieve results when they need help through your method.' ||
            authorityHookText.textContent.trim() === 'I help your audience achieve their goals when they need help through your method.') {
            console.log('🔧 Auto-showing Authority Hook Builder for first-time users');
            setTimeout(() => toggleAuthorityHookBuilder(), 500); // Small delay to ensure everything is loaded
        }
    }
    
    // Check if shared component loaded
    const authorityHookDiv = document.querySelector('.authority-hook');
    if (authorityHookDiv) {
        console.log('✅ Shared Authority Hook component detected');
    } else {
        console.warn('⚠️ Shared Authority Hook component not found');
    }
    
    // Make toggle function globally available for debugging
    window.toggleAuthorityHookBuilder = toggleAuthorityHookBuilder;
    
    // Additional debug: Check if Authority Hook Builder JS is loaded
    if (typeof window.authorityHookBuilder !== 'undefined') {
        console.log('✅ Authority Hook Builder JS is loaded');
    } else {
        console.warn('⚠️ Authority Hook Builder JS not detected - this is expected for Topics Generator');
    }
    
    console.log('✅ Authority Hook Builder initialization complete');
    
    // Test the toggle function immediately for debugging
    setTimeout(() => {
        console.log('🗋 Testing toggle function availability...');
        if (typeof toggleAuthorityHookBuilder === 'function') {
            console.log('✅ Toggle function is available');
            
    // Add a temporary debug helper
            window.debugToggle = function() {
                console.log('🔧 Manual debug toggle triggered');
                toggleAuthorityHookBuilder();
            };
            
            // Add a direct test function
            window.testToggleNow = function() {
                console.log('🗋 DIRECT TEST: Attempting to toggle builder...');
                const builder = document.getElementById('topics-generator-authority-hook-builder');
                if (builder) {
                    const isHidden = builder.classList.contains('topics-generator__builder--hidden');
                    console.log('Builder currently hidden:', isHidden);
                    if (isHidden) {
                        builder.classList.remove('topics-generator__builder--hidden');
                        console.log('✅ DIRECT TEST: Builder should now be visible');
                    } else {
                        builder.classList.add('topics-generator__builder--hidden');
                        console.log('✅ DIRECT TEST: Builder should now be hidden');
                    }
                    console.log('Updated builder classes:', builder.className);
                } else {
                    console.error('❌ DIRECT TEST: Builder element not found');
                }
            };
            
            // Add a button click simulation test
            window.simulateButtonClick = function() {
                console.log('🗋 SIMULATE CLICK: Testing button click event...');
                const button = document.getElementById('topics-generator-toggle-builder');
                if (button) {
                    console.log('Found button, simulating click...');
                    button.click();
                } else {
                    console.error('❌ Button not found for simulation');
                }
            };
            
            console.log('🗋 Debug commands available:');
            console.log('- debugToggle() - Test the toggle function');
            console.log('- testToggleNow() - Direct CSS class manipulation test');
            console.log('- simulateButtonClick() - Simulate clicking the Edit Components button');
            console.log('🗋 You can manually test with: testToggleNow() or simulateButtonClick()');
        } else {
            console.error('❌ Toggle function is NOT available');
        }
    }, 1000);
});
</script>