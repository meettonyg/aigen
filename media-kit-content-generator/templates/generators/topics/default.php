<?php
/**
 * Topics Generator Template - BEM Methodology
 * Modern design with proper BEM class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';
$entry_data = null;
$form_field_values = [];
$authority_hook_components = [
    'who' => '',
    'result' => '',
    'when' => '',
    'how' => '',
    'complete' => ''
];

// Try to get entry from URL parameters
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    
    // Use the Formidable service to get data using the correct field IDs
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
            
            // Get authority hook components using Form 515 field IDs
            $authority_hook_components['who'] = $formidable_service->get_field_value($entry_id, 10296) ?: 'your audience';
            $authority_hook_components['result'] = $formidable_service->get_field_value($entry_id, 10297) ?: 'achieve their goals';
            $authority_hook_components['when'] = $formidable_service->get_field_value($entry_id, 10387) ?: 'they need help';
            $authority_hook_components['how'] = $formidable_service->get_field_value($entry_id, 10298) ?: 'through your method';
            $authority_hook_components['complete'] = $formidable_service->get_field_value($entry_id, 10358);
            
            // If no complete authority hook, build from components
            if (empty($authority_hook_components['complete'])) {
            $authority_hook_components['complete'] = "I help {$authority_hook_components['who']} {$authority_hook_components['result']} when {$authority_hook_components['when']} {$authority_hook_components['how']}.";
            }
        
        // However, if we have component data, always rebuild from components (components take precedence)
        $has_component_data = !empty($authority_hook_components['who']) || !empty($authority_hook_components['result']) || 
                             !empty($authority_hook_components['when']) || !empty($authority_hook_components['how']);
        
        if ($has_component_data) {
            $authority_hook_components['complete'] = "I help {$authority_hook_components['who']} {$authority_hook_components['result']} when {$authority_hook_components['when']} {$authority_hook_components['how']}.";
        }
            
            // Get existing topics using Form 515 field IDs
            $form_field_values['topic_1'] = $formidable_service->get_field_value($entry_id, 8498);
            $form_field_values['topic_2'] = $formidable_service->get_field_value($entry_id, 8499);
            $form_field_values['topic_3'] = $formidable_service->get_field_value($entry_id, 8500);
            $form_field_values['topic_4'] = $formidable_service->get_field_value($entry_id, 8501);
            $form_field_values['topic_5'] = $formidable_service->get_field_value($entry_id, 8502);
            
            error_log('MKCG Topics: Loaded entry ' . $entry_id . ' - Authority Hook: ' . $authority_hook_components['complete']);
            error_log('MKCG Topics: Components - ' . json_encode($authority_hook_components));
            error_log('MKCG Topics: Topics - ' . json_encode($form_field_values));
        } else {
            error_log('MKCG Topics: Failed to load entry data for key: ' . $entry_key);
        }
    }
}

// Fallback for authority hook if still empty
if (empty($authority_hook_components['complete'])) {
    $authority_hook_components['complete'] = "I help {$authority_hook_components['who']} {$authority_hook_components['result']} when {$authority_hook_components['when']} {$authority_hook_components['how']}.";
}
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
                        <button class="topics-generator__button topics-generator__button--edit" id="topics-generator-toggle-builder">
                            Edit Components
                        </button>
                    </div>
                </div>
                
                <!-- Authority Hook Builder - HIDDEN BY DEFAULT -->
                <div class="topics-generator__builder topics-generator__builder--hidden" id="topics-generator-authority-hook-builder">
                    <div class="topics-generator__builder-header">
                        <h3 class="topics-generator__builder-title">Authority Hook Builder</h3>
                        
                        <p class="topics-generator__builder-description">
                            Build your authority statement using the WHO-WHAT-WHEN-HOW framework. 
                            This will be used to generate your topics.
                        </p>
                        
                        <!-- Framework tabs -->
                        <div class="topics-generator__tabs">
                            <button class="topics-generator__tab" data-tab="who">WHO</button>
                            <button class="topics-generator__tab" data-tab="result">RESULT</button>
                            <button class="topics-generator__tab topics-generator__tab--active" data-tab="when">WHEN</button>
                            <button class="topics-generator__tab" data-tab="how">HOW</button>
                        </div>
                    </div>
                    
                    <!-- WHO Tab Content -->
                    <div class="topics-generator__tab-content" id="topics-generator-who-tab">
                        <div class="topics-generator__builder-number">
                            <span class="topics-generator__circle-number">1</span>
                        </div>
                        <h4 class="field__label">WHO do you help?</h4>
                        
                        <div class="topics-generator__input-group">
                            <input type="text" class="topics-generator__input" id="topics-generator-who-input" 
                                   data-field-id="10296" data-component="who"
                                   value="<?php echo esc_attr($authority_hook_components['who']); ?>"
                                   placeholder="e.g. SaaS founders, course creators, consultants">
                            <button class="topics-generator__clear-button" id="topics-generator-clear-who">×</button>
                        </div>
                        
                        <div class="topics-generator__examples">
                            <p class="topics-generator__examples-label">Examples:</p>
                            <div class="topics-generator__examples-list">
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">SaaS founders</span>
                                    <button class="topics-generator__add-button" data-field="who" data-example="SaaS founders">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">course creators</span>
                                    <button class="topics-generator__add-button" data-field="who" data-example="course creators">+ Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- RESULT Tab Content -->
                    <div class="topics-generator__tab-content" id="topics-generator-result-tab">
                        <div class="topics-generator__builder-number">
                            <span class="topics-generator__circle-number">2</span>
                        </div>
                        <h4 class="field__label">What RESULT do you help them achieve?</h4>
                        
                        <div class="topics-generator__input-group">
                            <input type="text" class="topics-generator__input" id="topics-generator-result-input" 
                                   data-field-id="10297" data-component="result"
                                   value="<?php echo esc_attr($authority_hook_components['result']); ?>"
                                   placeholder="e.g. increase revenue by 40%, save 10+ hours per week">
                            <button class="topics-generator__clear-button" id="topics-generator-clear-result">×</button>
                        </div>
                        
                        <div class="topics-generator__examples">
                            <p class="topics-generator__examples-label">Examples:</p>
                            <div class="topics-generator__examples-list">
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">double their revenue</span>
                                    <button class="topics-generator__add-button" data-field="result" data-example="double their revenue">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">save 10+ hours weekly</span>
                                    <button class="topics-generator__add-button" data-field="result" data-example="save 10+ hours weekly">+ Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- WHEN Tab Content -->
                    <div class="topics-generator__tab-content topics-generator__tab-content--active" id="topics-generator-when-tab">
                        <div class="topics-generator__builder-number">
                            <span class="topics-generator__circle-number">3</span>
                        </div>
                        <h4 class="field__label">WHEN do they need this help?</h4>
                        
                        <div class="topics-generator__input-group">
                            <input type="text" class="topics-generator__input" id="topics-generator-when-input" 
                                   data-field-id="10387" data-component="when"
                                   value="<?php echo esc_attr($authority_hook_components['when']); ?>"
                                   placeholder="e.g. during rapid growth, when scaling their team">
                            <button class="topics-generator__clear-button" id="topics-generator-clear-when">×</button>
                        </div>
                        
                        <div class="topics-generator__examples">
                            <p class="topics-generator__examples-label">Examples:</p>
                            <div class="topics-generator__examples-list">
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">they're scaling rapidly</span>
                                    <button class="topics-generator__add-button" data-field="when" data-example="they're scaling rapidly">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">facing cash flow challenges</span>
                                    <button class="topics-generator__add-button" data-field="when" data-example="facing cash flow challenges">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">ready to expand their team</span>
                                    <button class="topics-generator__add-button" data-field="when" data-example="ready to expand their team">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">launching a new product</span>
                                    <button class="topics-generator__add-button" data-field="when" data-example="launching a new product">+ Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- HOW Tab Content -->
                    <div class="topics-generator__tab-content" id="topics-generator-how-tab">
                        <div class="topics-generator__builder-number">
                            <span class="topics-generator__circle-number">4</span>
                        </div>
                        <h4 class="field__label">HOW do you help them?</h4>
                        
                        <div class="topics-generator__input-group">
                            <input type="text" class="topics-generator__input" id="topics-generator-how-input" 
                                   data-field-id="10298" data-component="how"
                                   value="<?php echo esc_attr($authority_hook_components['how']); ?>"
                                   placeholder="e.g. through your method, with your framework">
                            <button class="topics-generator__clear-button" id="topics-generator-clear-how">×</button>
                        </div>
                        
                        <div class="topics-generator__examples">
                            <p class="topics-generator__examples-label">Examples:</p>
                            <div class="topics-generator__examples-list">
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">through your proven system</span>
                                    <button class="topics-generator__add-button" data-field="how" data-example="through your proven system">+ Add</button>
                                </div>
                                <div class="topics-generator__example">
                                    <span class="topics-generator__example-text">with your 3-step framework</span>
                                    <button class="topics-generator__add-button" data-field="how" data-example="with your 3-step framework">+ Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <input type="hidden" id="topics-generator-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
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
    window.MKCG_Topics_Data = {
        entryId: <?php echo intval($entry_id); ?>,
        entryKey: '<?php echo esc_js($entry_key); ?>',
        hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
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
        }
    };
    
    console.log('MKCG Topics: Loaded data', window.MKCG_Topics_Data);
</script>

<!-- JavaScript functionality loaded separately -->