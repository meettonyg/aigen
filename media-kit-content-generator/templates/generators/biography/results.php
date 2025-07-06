<?php
/**
 * Biography Generator Results Template - Unified BEM Architecture
 * Comprehensive results page for biography display, modification, and management
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get template data
$template_data = [];
if (isset($generator_instance) && method_exists($generator_instance, 'get_results_data')) {
    $template_data = $generator_instance->get_results_data();
    error_log('MKCG Biography Results: Got data from generator instance');
} else {
    // Fallback: Create sample structure
    $template_data = [
        'post_id' => isset($_GET['post_id']) ? intval($_GET['post_id']) : 0,
        'entry_id' => isset($_GET['entry']) ? intval($_GET['entry']) : 0,
        'has_data' => false,
        'biographies' => [
            'short' => '',
            'medium' => '',
            'long' => ''
        ],
        'settings' => [
            'tone' => 'professional',
            'pov' => 'third'
        ],
        'personal_info' => [
            'name' => '',
            'title' => '',
            'organization' => ''
        ],
        'generation_date' => current_time('mysql')
    ];
    error_log('MKCG Biography Results: Using fallback structure - no instance available');
}

// Extract data for easier access
$post_id = $template_data['post_id'] ?? 0;
$entry_id = $template_data['entry_id'] ?? 0;
$has_data = $template_data['has_data'] ?? false;
$biographies = $template_data['biographies'] ?? ['short' => '', 'medium' => '', 'long' => ''];
$settings = $template_data['settings'] ?? ['tone' => 'professional', 'pov' => 'third'];
$personal_info = $template_data['personal_info'] ?? ['name' => '', 'title' => '', 'organization' => ''];
$generation_date = $template_data['generation_date'] ?? current_time('mysql');

// Format display date
$display_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($generation_date));
?>

<div class="generator__container biography-generator" data-generator="biography">
    <div class="generator__header">
        <h1 class="generator__title">Your Professional Biography</h1>
        <p class="generator__subtitle">Your biography has been generated in three different lengths for various use cases</p>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Results Container -->
            <div class="biography-generator__results-container">
                <div class="biography-generator__results-header">
                    <h3>Your Professional Biography Versions</h3>
                    <p>Select the version that best fits your needs. All versions maintain your key messages while adapting to different contexts.</p>
                </div>
                
                <!-- Biography Tabs -->
                <div class="biography-generator__results-tabs">
                    <div class="biography-generator__results-tab biography-generator__results-tab--active" data-tab="short">
                        Short <span class="biography-generator__length-indicator biography-generator__length-short">(50-75 words)</span>
                    </div>
                    <div class="biography-generator__results-tab" data-tab="medium">
                        Medium <span class="biography-generator__length-indicator biography-generator__length-medium">(100-150 words)</span>
                    </div>
                    <div class="biography-generator__results-tab" data-tab="long">
                        Long <span class="biography-generator__length-indicator biography-generator__length-long">(200-300 words)</span>
                    </div>
                </div>
                
                <!-- Biography Content Panels -->
                <div class="biography-generator__results-content">
                    <!-- Short Biography -->
                    <div class="biography-generator__result-item" id="biography-short-result" style="display: block;">
                        <div class="biography-generator__result-header">
                            <h4 class="biography-generator__result-title">Short Biography</h4>
                            <span class="biography-generator__result-badge">SOCIAL MEDIA & BRIEF INTROS</span>
                        </div>
                        <div class="biography-generator__result-content" id="biography-short-content">
                            <?php echo nl2br(esc_html($biographies['short'])); ?>
                        </div>
                        <div class="biography-generator__result-actions">
                            <button type="button" class="biography-generator__action-button" id="copy-short-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                </svg>
                                Copy to Clipboard
                            </button>
                            <button type="button" class="biography-generator__action-button" id="download-short-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                </svg>
                                Download as Text
                            </button>
                            <button type="button" class="biography-generator__action-button" id="email-short-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                                Email Biography
                            </button>
                        </div>
                    </div>
                    
                    <!-- Medium Biography -->
                    <div class="biography-generator__result-item" id="biography-medium-result" style="display: none;">
                        <div class="biography-generator__result-header">
                            <h4 class="biography-generator__result-title">Medium Biography</h4>
                            <span class="biography-generator__result-badge">WEBSITES & SPEAKER INTROS</span>
                        </div>
                        <div class="biography-generator__result-content" id="biography-medium-content">
                            <?php echo nl2br(esc_html($biographies['medium'])); ?>
                        </div>
                        <div class="biography-generator__result-actions">
                            <button type="button" class="biography-generator__action-button" id="copy-medium-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                </svg>
                                Copy to Clipboard
                            </button>
                            <button type="button" class="biography-generator__action-button" id="download-medium-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                </svg>
                                Download as Text
                            </button>
                            <button type="button" class="biography-generator__action-button" id="email-medium-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                                Email Biography
                            </button>
                        </div>
                    </div>
                    
                    <!-- Long Biography -->
                    <div class="biography-generator__result-item" id="biography-long-result" style="display: none;">
                        <div class="biography-generator__result-header">
                            <h4 class="biography-generator__result-title">Long Biography</h4>
                            <span class="biography-generator__result-badge">DETAILED MARKETING MATERIALS</span>
                        </div>
                        <div class="biography-generator__result-content" id="biography-long-content">
                            <?php echo nl2br(esc_html($biographies['long'])); ?>
                        </div>
                        <div class="biography-generator__result-actions">
                            <button type="button" class="biography-generator__action-button" id="copy-long-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                </svg>
                                Copy to Clipboard
                            </button>
                            <button type="button" class="biography-generator__action-button" id="download-long-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                </svg>
                                Download as Text
                            </button>
                            <button type="button" class="biography-generator__action-button" id="email-long-bio">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                                Email Biography
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Tone Modification Controls -->
                <div class="biography-generator__modification-controls">
                    <h3 class="biography-generator__modification-title">Adjust Biography Tone</h3>
                    <p class="biography-generator__modification-description">
                        Change the tone of your biography to better match your personal brand or target audience.
                    </p>
                    
                    <div class="biography-generator__tone-selector">
                        <label class="biography-generator__tone-option <?php echo $settings['tone'] === 'professional' ? 'biography-generator__tone-option--active' : ''; ?>">
                            <input type="radio" name="biography-tone" value="professional" class="biography-generator__tone-radio" <?php checked($settings['tone'], 'professional'); ?>>
                            Professional
                        </label>
                        <label class="biography-generator__tone-option <?php echo $settings['tone'] === 'conversational' ? 'biography-generator__tone-option--active' : ''; ?>">
                            <input type="radio" name="biography-tone" value="conversational" class="biography-generator__tone-radio" <?php checked($settings['tone'], 'conversational'); ?>>
                            Conversational
                        </label>
                        <label class="biography-generator__tone-option <?php echo $settings['tone'] === 'authoritative' ? 'biography-generator__tone-option--active' : ''; ?>">
                            <input type="radio" name="biography-tone" value="authoritative" class="biography-generator__tone-radio" <?php checked($settings['tone'], 'authoritative'); ?>>
                            Authoritative
                        </label>
                        <label class="biography-generator__tone-option <?php echo $settings['tone'] === 'friendly' ? 'biography-generator__tone-option--active' : ''; ?>">
                            <input type="radio" name="biography-tone" value="friendly" class="biography-generator__tone-radio" <?php checked($settings['tone'], 'friendly'); ?>>
                            Friendly
                        </label>
                    </div>
                    
                    <div class="biography-generator__button-group" style="justify-content: center; margin-top: 20px;">
                        <button type="button" id="biography-update-tone" class="generator__button generator__button--primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
                            </svg>
                            Update Tone
                        </button>
                    </div>
                </div>
                
                <!-- Save to Formidable Section -->
                <div class="biography-generator__save-section">
                    <button type="button" id="biography-save-to-formidable" class="biography-generator__save-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
                            <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                        </svg>
                        Save All Biographies
                    </button>
                    
                    <p style="margin-top: 10px; font-size: 14px; color: #666;">
                        <small>Saves all three biography versions to your guest profile.</small>
                    </p>
                    
                    <!-- Save Status -->
                    <div id="biography-save-status" style="display: none; margin-top: 15px; padding: 10px; border-radius: 4px; background: #e8f4fd; border: 1px solid #bde0fd;">
                        <span id="biography-save-status-text">Saving biographies...</span>
                    </div>
                </div>
                
                <!-- Generation Information -->
                <div style="margin-top: 30px; text-align: center; font-size: 13px; color: #999;">
                    <p>Generated on: <?php echo esc_html($display_date); ?></p>
                    <p>For: <?php echo esc_html($personal_info['name']); ?><?php echo !empty($personal_info['title']) ? ', ' . esc_html($personal_info['title']) : ''; ?><?php echo !empty($personal_info['organization']) ? ' at ' . esc_html($personal_info['organization']) : ''; ?></p>
                </div>
                
                <!-- Return to Generator Button -->
                <div style="margin-top: 30px; text-align: center;">
                    <a href="<?php echo esc_url(add_query_arg(['generator' => 'biography', 'post_id' => $post_id, 'entry' => $entry_id], remove_query_arg('results'))); ?>" class="generator__button generator__button--outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                        </svg>
                        Return to Biography Generator
                    </a>
                </div>
            </div>
            
            <!-- Hidden fields for data transmission -->
            <input type="hidden" id="biography-post-id" value="<?php echo esc_attr($post_id); ?>">
            <input type="hidden" id="biography-entry-id" value="<?php echo esc_attr($entry_id); ?>">
            <input type="hidden" id="biography-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
            <input type="hidden" id="biography-current-tone" value="<?php echo esc_attr($settings['tone']); ?>">
            <input type="hidden" id="biography-current-pov" value="<?php echo esc_attr($settings['pov']); ?>">
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <h2 class="generator__guidance-header">Using Your Professional Biography</h2>
            <p class="generator__guidance-subtitle">Your biography is a versatile marketing asset that can be used in multiple contexts. Each version is optimized for different use cases while maintaining your core message.</p>
            
            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Short Biography (50-75 words)</h3>
                    <p class="generator__process-description">
                        Perfect for social media profiles, speaker introductions, and brief marketing materials. This version focuses on your core value proposition and most impressive credential.
                    </p>
                </div>
            </div>
            
            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Medium Biography (100-150 words)</h3>
                    <p class="generator__process-description">
                        Ideal for website about pages, podcast guest profiles, and press kits. This version balances brevity with comprehensive information about your expertise, results, and mission.
                    </p>
                </div>
            </div>
            
            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Long Biography (200-300 words)</h3>
                    <p class="generator__process-description">
                        Best for comprehensive marketing materials, detailed speaker profiles, and media kits. This version provides the full story of your expertise, achievements, methodology, and mission.
                    </p>
                </div>
            </div>
            
            <div class="generator__formula-box">
                <span class="generator__formula-label">TONE ADJUSTMENT</span>
                You can modify your biography tone to match different contexts:
                <ul style="margin-top: 10px; padding-left: 20px;">
                    <li><strong>Professional</strong>: Formal, authoritative approach for business contexts</li>
                    <li><strong>Conversational</strong>: Relaxed, friendly tone for blogs and casual platforms</li>
                    <li><strong>Authoritative</strong>: Emphasis on expertise and accomplishments</li>
                    <li><strong>Friendly</strong>: Approachable and warm for general audiences</li>
                </ul>
            </div>
            
            <h3 class="generator__examples-header">Placement Recommendations:</h3>
            
            <div class="generator__example-card">
                <strong>Website</strong>
                <p>Use your medium biography on your About page. Consider adding a condensed version (short bio) for sidebars, footers, or team pages.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Speaker/Media Kit</strong>
                <p>Include all three versions in your media kit with clear labels for each length. This allows event organizers to choose the appropriate version for their needs.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Social Media</strong>
                <p>Use your short biography for LinkedIn, Twitter, and other social platforms. You may need to adjust the character count for specific platforms.</p>
            </div>
            
            <div class="generator__example-card">
                <strong>Email Signature</strong>
                <p>Add a one or two-sentence version based on your short biography to your email signature to reinforce your authority in every communication.</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Biography Results data for JavaScript
    window.MKCG_Biography_Results = {
        postId: <?php echo intval($post_id); ?>,
        entryId: <?php echo intval($entry_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        biographies: {
            short: <?php echo json_encode($biographies['short']); ?>,
            medium: <?php echo json_encode($biographies['medium']); ?>,
            long: <?php echo json_encode($biographies['long']); ?>
        },
        settings: {
            tone: '<?php echo esc_js($settings['tone']); ?>',
            pov: '<?php echo esc_js($settings['pov']); ?>'
        },
        personalInfo: {
            name: '<?php echo esc_js($personal_info['name']); ?>',
            title: '<?php echo esc_js($personal_info['title']); ?>',
            organization: '<?php echo esc_js($personal_info['organization']); ?>'
        },
        generationDate: '<?php echo esc_js($generation_date); ?>',
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('mkcg_nonce'); ?>'
    };
    
    // Initialize results page tabs
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const resultsTabs = document.querySelectorAll('.biography-generator__results-tab');
        const resultsItems = document.querySelectorAll('.biography-generator__result-item');
        
        resultsTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Get the tab's data-tab attribute
                const tabType = this.getAttribute('data-tab');
                
                // Remove active class from all tabs
                resultsTabs.forEach(t => t.classList.remove('biography-generator__results-tab--active'));
                // Add active class to clicked tab
                this.classList.add('biography-generator__results-tab--active');
                
                // Hide all result items
                resultsItems.forEach(item => {
                    item.style.display = 'none';
                });
                
                // Show the selected result item
                document.getElementById(`biography-${tabType}-result`).style.display = 'block';
            });
        });
        
        // Tone selector functionality
        const toneOptions = document.querySelectorAll('.biography-generator__tone-option');
        const toneRadios = document.querySelectorAll('.biography-generator__tone-radio');
        
        toneOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Find the radio input inside this option
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    // Uncheck all radios
                    toneRadios.forEach(r => r.checked = false);
                    // Check this radio
                    radio.checked = true;
                    
                    // Remove active class from all options
                    toneOptions.forEach(o => o.classList.remove('biography-generator__tone-option--active'));
                    // Add active class to clicked option
                    this.classList.add('biography-generator__tone-option--active');
                    
                    // Update hidden field
                    document.getElementById('biography-current-tone').value = radio.value;
                }
            });
        });
        
        // Update tone button functionality
        const updateToneButton = document.getElementById('biography-update-tone');
        if (updateToneButton) {
            updateToneButton.addEventListener('click', function() {
                const selectedTone = document.querySelector('.biography-generator__tone-radio:checked').value;
                const postId = document.getElementById('biography-post-id').value;
                const nonce = document.getElementById('biography-nonce').value;
                
                // Add loading state
                this.classList.add('generator__button--loading');
                this.setAttribute('disabled', 'disabled');
                
                // Prepare AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', window.MKCG_Biography_Results.ajaxUrl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    // Remove loading state
                    updateToneButton.classList.remove('generator__button--loading');
                    updateToneButton.removeAttribute('disabled');
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success && response.data) {
                                // Update biography content
                                if (response.data.biographies) {
                                    const biographies = response.data.biographies;
                                    
                                    if (biographies.short) {
                                        document.getElementById('biography-short-content').innerHTML = biographies.short.replace(/\n/g, '<br>');
                                        window.MKCG_Biography_Results.biographies.short = biographies.short;
                                    }
                                    
                                    if (biographies.medium) {
                                        document.getElementById('biography-medium-content').innerHTML = biographies.medium.replace(/\n/g, '<br>');
                                        window.MKCG_Biography_Results.biographies.medium = biographies.medium;
                                    }
                                    
                                    if (biographies.long) {
                                        document.getElementById('biography-long-content').innerHTML = biographies.long.replace(/\n/g, '<br>');
                                        window.MKCG_Biography_Results.biographies.long = biographies.long;
                                    }
                                    
                                    // Display success message
                                    alert('Biography tone updated successfully!');
                                }
                            } else {
                                console.error('Error updating tone:', response);
                                alert('Failed to update biography tone. Please try again.');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Failed to update biography tone. Please try again.');
                        }
                    } else {
                        console.error('AJAX request failed with status:', xhr.status);
                        alert('Failed to update biography tone. Please try again.');
                    }
                };
                
                xhr.onerror = function() {
                    // Remove loading state
                    updateToneButton.classList.remove('generator__button--loading');
                    updateToneButton.removeAttribute('disabled');
                    
                    console.error('AJAX request failed');
                    alert('Failed to update biography tone. Please check your internet connection and try again.');
                };
                
                // Send the request
                xhr.send(`action=mkcg_modify_biography_tone&post_id=${postId}&tone=${selectedTone}&nonce=${nonce}`);
            });
        }
        
        // Copy to clipboard functionality
        const copyButtons = document.querySelectorAll('[id^="copy-"][id$="-bio"]');
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bioType = this.id.replace('copy-', '').replace('-bio', '');
                const bioText = window.MKCG_Biography_Results.biographies[bioType];
                
                // Copy to clipboard
                navigator.clipboard.writeText(bioText).then(() => {
                    // Change button text temporarily
                    const originalText = this.innerHTML;
                    this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> Copied!';
                    
                    // Reset button text after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                    alert('Failed to copy to clipboard. Please try again.');
                });
            });
        });
        
        // Download as text functionality
        const downloadButtons = document.querySelectorAll('[id^="download-"][id$="-bio"]');
        downloadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bioType = this.id.replace('download-', '').replace('-bio', '');
                const bioText = window.MKCG_Biography_Results.biographies[bioType];
                const name = window.MKCG_Biography_Results.personalInfo.name || 'Professional';
                
                // Create file name
                const fileName = `${name.replace(/\s+/g, '_')}_${bioType}_biography.txt`;
                
                // Create temporary link
                const element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(bioText));
                element.setAttribute('download', fileName);
                
                element.style.display = 'none';
                document.body.appendChild(element);
                
                element.click();
                
                document.body.removeChild(element);
            });
        });
        
        // Email functionality
        const emailButtons = document.querySelectorAll('[id^="email-"][id$="-bio"]');
        emailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bioType = this.id.replace('email-', '').replace('-bio', '');
                const bioText = window.MKCG_Biography_Results.biographies[bioType];
                const name = window.MKCG_Biography_Results.personalInfo.name || 'Professional';
                
                // Create email subject and body
                const subject = `Professional Biography - ${name} (${bioType.charAt(0).toUpperCase() + bioType.slice(1)} Version)`;
                const body = `Here is my professional biography (${bioType} version):\n\n${bioText}`;
                
                // Create mailto link
                const mailtoLink = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                
                // Open email client
                window.location.href = mailtoLink;
            });
        });
        
        // Save to Formidable functionality
        const saveButton = document.getElementById('biography-save-to-formidable');
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                const postId = document.getElementById('biography-post-id').value;
                const entryId = document.getElementById('biography-entry-id').value;
                const nonce = document.getElementById('biography-nonce').value;
                
                // Show save status
                const saveStatus = document.getElementById('biography-save-status');
                const saveStatusText = document.getElementById('biography-save-status-text');
                saveStatus.style.display = 'block';
                saveStatusText.textContent = 'Saving biographies...';
                
                // Add loading state
                this.classList.add('generator__button--loading');
                this.setAttribute('disabled', 'disabled');
                
                // Prepare AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', window.MKCG_Biography_Results.ajaxUrl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    // Remove loading state
                    saveButton.classList.remove('generator__button--loading');
                    saveButton.removeAttribute('disabled');
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Update save status
                                saveStatus.style.background = '#d4edda';
                                saveStatus.style.borderColor = '#c3e6cb';
                                saveStatusText.textContent = 'Biographies saved successfully!';
                                
                                // Hide status after 3 seconds
                                setTimeout(() => {
                                    saveStatus.style.display = 'none';
                                }, 3000);
                            } else {
                                console.error('Error saving biographies:', response);
                                
                                // Update save status
                                saveStatus.style.background = '#f8d7da';
                                saveStatus.style.borderColor = '#f5c6cb';
                                saveStatusText.textContent = 'Failed to save biographies. Please try again.';
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            
                            // Update save status
                            saveStatus.style.background = '#f8d7da';
                            saveStatus.style.borderColor = '#f5c6cb';
                            saveStatusText.textContent = 'Failed to save biographies. Please try again.';
                        }
                    } else {
                        console.error('AJAX request failed with status:', xhr.status);
                        
                        // Update save status
                        saveStatus.style.background = '#f8d7da';
                        saveStatus.style.borderColor = '#f5c6cb';
                        saveStatusText.textContent = 'Failed to save biographies. Please try again.';
                    }
                };
                
                xhr.onerror = function() {
                    // Remove loading state
                    saveButton.classList.remove('generator__button--loading');
                    saveButton.removeAttribute('disabled');
                    
                    console.error('AJAX request failed');
                    
                    // Update save status
                    saveStatus.style.background = '#f8d7da';
                    saveStatus.style.borderColor = '#f5c6cb';
                    saveStatusText.textContent = 'Failed to save biographies. Please check your internet connection.';
                };
                
                // Send the request
                xhr.send(`action=mkcg_save_biography_to_formidable&post_id=${postId}&entry_id=${entryId}&nonce=${nonce}`);
            });
        }
    });
</script>