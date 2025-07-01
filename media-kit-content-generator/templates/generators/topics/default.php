<?php
/**
 * Topics Generator Template - BEM Methodology
 * Modern design with proper BEM class structure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
/* Topics Display Container Styles (Matching Questions Generator Design) */
.topics-generator__topics-container {
    background: #ffffff;
    border: 1px solid #e0e6ed;
    border-radius: 12px;
    padding: 25px;
    margin: 25px 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.topics-generator__topics-header {
    margin-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
}

.topics-generator__topics-header h3 {
    color: #2c3e50;
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 5px 0;
}

.topics-generator__topics-subheading {
    font-size: 14px;
    color: #5a6d7e;
    margin: 0;
    font-style: italic;
}

.topics-generator__topics-display {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.topics-generator__topic-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #e67e22;
    transition: all 0.2s ease;
}

.topics-generator__topic-item:hover {
    background: #f1f3f4;
    transform: translateX(2px);
}

.topics-generator__topic-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #e67e22;
    color: white;
    border-radius: 50%;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.topics-generator__topic-content {
    flex: 1;
    min-width: 0;
}

.topics-generator__topic-input {
    width: 100%;
    border: none;
    background: transparent;
    color: #2c3e50;
    font-size: 16px;
    line-height: 1.5;
    font-weight: 500;
    padding: 0;
    outline: none;
    resize: none;
}

.topics-generator__topic-input:focus {
    background: #ffffff;
    border-radius: 4px;
    padding: 8px;
    box-shadow: 0 0 0 2px #e67e22;
}

.topics-generator__topic-input::placeholder {
    color: #95a5a6;
    font-style: italic;
    font-weight: normal;
}

.topics-generator__topic-text {
    color: #2c3e50;
    font-size: 16px;
    line-height: 1.5;
    font-weight: 500;
}

.topics-generator__topic-placeholder {
    color: #95a5a6;
    font-style: italic;
    font-weight: normal;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .topics-generator__topics-container {
        padding: 20px;
        margin: 20px 0;
    }
    
    .topics-generator__topic-item {
        gap: 12px;
        padding: 12px;
    }
    
    .topics-generator__topic-number {
        width: 28px;
        height: 28px;
        font-size: 13px;
    }
    
    .topics-generator__topic-input {
        font-size: 15px;
    }
}

/* Save Section Styles */
.topics-generator__save-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
    text-align: center;
}

.topics-generator__save-button {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(230, 126, 34, 0.3);
}

.topics-generator__save-button:hover {
    background: linear-gradient(135deg, #d35400 0%, #a04000 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(230, 126, 34, 0.4);
}

.topics-generator__save-button:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(230, 126, 34, 0.3);
}

.topics-generator__save-button:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.topics-generator__save-status {
    margin-top: 10px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.topics-generator__save-status--success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.topics-generator__save-status--error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.topics-generator__save-status--loading {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>

<?php

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
                
                <!-- Authority Hook Builder - ENHANCED SHARED COMPONENT -->
                <div class="topics-generator__builder topics-generator__builder--hidden" id="topics-generator-authority-hook-builder">
                    <?php 
                    // Use the enhanced shared Authority Hook component
                    $generator_type = 'topics'; // Specify generator type
                    $current_values = [
                        'who' => $authority_hook_components['who'],
                        'result' => $authority_hook_components['result'],
                        'when' => $authority_hook_components['when'],
                        'how' => $authority_hook_components['how'],
                        'authority_hook' => $authority_hook_components['complete']
                    ];
                    $entry_id = $entry_id; // Pass entry ID to shared component
                    
                    // Include the enhanced shared component
                    $shared_component_path = MKCG_PLUGIN_PATH . 'templates/shared/authority-hook-component.php';
                    
                    // DEBUG: Show what's happening
                    echo '<div style="background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; font-size: 12px;">';
                    echo '<strong>🔍 DEBUG:</strong><br>';
                    echo 'MKCG_PLUGIN_PATH: ' . (defined('MKCG_PLUGIN_PATH') ? MKCG_PLUGIN_PATH : 'NOT DEFINED') . '<br>';
                    echo 'Full path: ' . $shared_component_path . '<br>';
                    echo 'File exists: ' . (file_exists($shared_component_path) ? 'YES' : 'NO') . '<br>';
                    echo '</div>';
                    
                    if (file_exists($shared_component_path)) {
                        echo '<div style="background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0; border-radius: 4px;">✅ Including enhanced Authority Hook component...</div>';
                        include $shared_component_path;
                        error_log('MKCG Topics: Enhanced Authority Hook component included successfully');
                    } else {
                        error_log('MKCG Topics: ERROR - Enhanced component not found at: ' . $shared_component_path);
                        echo '<div style="background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0; border-radius: 4px;"><strong>❌ ERROR:</strong> Enhanced Authority Hook component not found at: ' . htmlspecialchars($shared_component_path) . '</div>';
                        
                        // Fallback: Try relative path
                        $fallback_path = __DIR__ . '/../../shared/authority-hook-component.php';
                        echo '<div style="background: #fff3e0; border: 1px solid #ff9800; padding: 10px; margin: 10px 0; border-radius: 4px;">🔄 Trying fallback path: ' . htmlspecialchars($fallback_path) . '</div>';
                        
                        if (file_exists($fallback_path)) {
                            echo '<div style="background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0; border-radius: 4px;">✅ Fallback path found! Including...</div>';
                            include $fallback_path;
                        } else {
                            echo '<div style="background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0; border-radius: 4px;">❌ Fallback path also failed.</div>';
                        }
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
                
                <!-- Topics Display Container with Editable Form Fields -->
                <div class="topics-generator__topics-container" id="topics-generator-topics-container">
                    <div class="topics-generator__topics-header">
                        <h3 id="topics-generator-topics-heading">Interview Topics for Your Authority Hook</h3>
                        <p class="topics-generator__topics-subheading">Five compelling podcast interview topics</p>
                    </div>
                    
                    <div class="topics-generator__topics-display">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="topics-generator__topic-item" id="topics-generator-topic-item-<?php echo $i; ?>">
                                <div class="topics-generator__topic-number">
                                    <?php echo $i; ?>
                                </div>
                                <div class="topics-generator__topic-content">
                                    <input type="text" 
                                           class="topics-generator__topic-input" 
                                           id="topics-generator-topic-field-<?php echo $i; ?>"
                                           name="field_<?php echo 8497 + $i; ?>" 
                                           data-field-id="<?php echo 8497 + $i; ?>" 
                                           data-topic-number="<?php echo $i; ?>"
                                           placeholder="<?php echo $i == 5 ? 'Click to add your interview topic' : 'Enter your interview topic ' . $i; ?>"
                                           value="<?php echo esc_attr($form_field_values['topic_' . $i] ?? ''); ?>">
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Save Button for Topics -->
                    <div class="topics-generator__save-section">
                        <button class="topics-generator__save-button" id="topics-generator-save-topics" type="button">
                            💾 Save All Topics
                        </button>
                        <div class="topics-generator__save-status" id="topics-generator-save-status" style="display: none;"></div>
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
                
                <!-- Hidden fields for AJAX -->
                <input type="hidden" id="topics-generator-entry-id" value="<?php echo esc_attr($entry_id); ?>">
                <input type="hidden" id="topics-generator-entry-key" value="<?php echo esc_attr($entry_key); ?>">
                <input type="hidden" id="topics-generator-post-id" value="<?php echo esc_attr(isset($formidable_service) && $entry_id ? $formidable_service->get_post_id_from_entry($entry_id) : ''); ?>">
                <input type="hidden" id="topics-generator-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
                <input type="hidden" id="topics-generator-topics-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
                
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
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }
    
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
    
    // Save Topics Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const saveButton = document.getElementById('topics-generator-save-topics');
        const saveStatus = document.getElementById('topics-generator-save-status');
        
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                saveAllTopics();
            });
        }
        
        // Auto-save on blur for individual fields
        for (let i = 1; i <= 5; i++) {
            const field = document.getElementById(`topics-generator-topic-field-${i}`);
            if (field) {
                field.addEventListener('blur', function() {
                    // Auto-save individual topic on blur
                    saveSingleTopic(i, this.value);
                });
            }
        }
    });
    
    // Function to save all topics
    function saveAllTopics() {
        const saveButton = document.getElementById('topics-generator-save-topics');
        const saveStatus = document.getElementById('topics-generator-save-status');
        const entryId = document.getElementById('topics-generator-entry-id')?.value;
        const nonce = document.getElementById('topics-generator-nonce')?.value;
        
        if (!entryId || entryId === '0') {
            showSaveStatus('error', 'No entry ID found. Please refresh the page.');
            return;
        }
        
        // Disable button and show loading
        saveButton.disabled = true;
        saveButton.textContent = '💾 Saving...';
        showSaveStatus('loading', 'Saving topics...');
        
        // Collect all topic values
        const topics = {};
        for (let i = 1; i <= 5; i++) {
            const field = document.getElementById(`topics-generator-topic-field-${i}`);
            if (field) {
                topics[`topic_${i}`] = field.value.trim();
            }
        }
        
        // Prepare AJAX data
        const formData = new FormData();
        formData.append('action', 'mkcg_save_topics_data');
        formData.append('entry_id', entryId);
        formData.append('nonce', nonce);
        
        // Add topics to form data
        Object.keys(topics).forEach(key => {
            formData.append(`topics[${key}]`, topics[key]);
        });
        
        // Get post_id from hidden field
        const postId = document.getElementById('topics-generator-post-id')?.value;
        if (postId && postId !== '0') {
            formData.append('post_id', postId);
        }
        
        // Make AJAX request
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSaveStatus('success', `✅ Successfully saved ${data.data.saved_count || 'all'} topics!`);
                console.log('✅ Topics saved successfully:', data);
            } else {
                showSaveStatus('error', '❌ Failed to save topics: ' + (data.data?.message || 'Unknown error'));
                console.error('❌ Save failed:', data);
            }
        })
        .catch(error => {
            showSaveStatus('error', '❌ Network error while saving topics');
            console.error('❌ Network error:', error);
        })
        .finally(() => {
            // Re-enable button
            saveButton.disabled = false;
            saveButton.textContent = '💾 Save All Topics';
        });
    }
    
    // Function to save individual topic
    function saveSingleTopic(topicNumber, topicText) {
        const entryId = document.getElementById('topics-generator-entry-id')?.value;
        const nonce = document.getElementById('topics-generator-nonce')?.value;
        
        if (!entryId || entryId === '0' || !topicText.trim()) {
            return; // Skip auto-save if no entry ID or empty text
        }
        
        const formData = new FormData();
        formData.append('action', 'mkcg_save_topic');
        formData.append('entry_id', entryId);
        formData.append('topic_number', topicNumber);
        formData.append('topic_text', topicText.trim());
        formData.append('nonce', nonce);
        
        // Get post_id from hidden field
        const postId = document.getElementById('topics-generator-post-id')?.value;
        if (postId && postId !== '0') {
            formData.append('post_id', postId);
        }
        
        // Make AJAX request (silent save)
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`✅ Topic ${topicNumber} auto-saved`);
            } else {
                console.warn(`⚠️ Topic ${topicNumber} auto-save failed:`, data.data?.message);
            }
        })
        .catch(error => {
            console.warn(`⚠️ Topic ${topicNumber} auto-save network error:`, error);
        });
    }
    
    // Function to show save status
    function showSaveStatus(type, message) {
        const saveStatus = document.getElementById('topics-generator-save-status');
        if (!saveStatus) return;
        
        // Clear previous classes
        saveStatus.className = 'topics-generator__save-status';
        
        // Add appropriate class
        saveStatus.classList.add(`topics-generator__save-status--${type}`);
        saveStatus.textContent = message;
        saveStatus.style.display = 'block';
        
        // Auto-hide success/error messages after 5 seconds
        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                saveStatus.style.display = 'none';
            }, 5000);
        }
    }
    
    // Make functions globally available
    window.saveAllTopics = saveAllTopics;
    window.saveSingleTopic = saveSingleTopic;
</script>

<!-- Authority Hook Builder functionality is now handled by topics-generator.js - duplicate script removed -->

<!-- Initialize enhanced elements check -->
<script>
// Check if enhanced elements loaded
setTimeout(() => {
    const credentialsManager = document.querySelector('.credentials-manager');
    const addToListButtons = document.querySelectorAll('.add-to-list');
    
    console.log('🔍 Enhanced elements check after 1 second:');
    console.log('credentials-manager found:', !!credentialsManager);
    console.log('add-to-list buttons found:', addToListButtons.length);
    
    if (!credentialsManager && addToListButtons.length === 0) {
        console.log('❌ Enhanced elements still missing - include may have failed');
    } else {
        console.log('✅ Enhanced elements found - include worked!');
    }
    
    console.log('✅ Topics container with editable fields initialized');
}, 1000);
</script>