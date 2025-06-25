<?php
/**
 * Shared Authority Hook Component Template - BEM Methodology
 * Used across all generators for consistent Authority Hook functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Set default values
$generator_type = isset($generator_type) ? $generator_type : 'default';
$current_values = isset($current_values) ? $current_values : [];
$entry_id = isset($entry_id) ? $entry_id : 0;

// Extract current values
$who = isset($current_values['who']) ? $current_values['who'] : '';
$result = isset($current_values['result']) ? $current_values['result'] : '';
$when = isset($current_values['when']) ? $current_values['when'] : '';
$how = isset($current_values['how']) ? $current_values['how'] : '';
$authority_hook = isset($current_values['authority_hook']) ? $current_values['authority_hook'] : '';

// Try to load existing Authority Hook if available
if ($entry_id && empty($authority_hook) && isset($authority_hook_service)) {
    $hook_result = $authority_hook_service->get_authority_hook($entry_id);
    if ($hook_result['success']) {
        $authority_hook = $hook_result['value'];
    }
}
?>

<div class="authority-hook" data-generator="<?php echo esc_attr($generator_type); ?>">
    
    <!-- Authority Hook Builder -->
    <div class="authority-hook__builder">
        <h3 class="authority-hook__builder-title">
            <span class="authority-hook__field-number">1</span>
            Authority Hook Builder
        </h3>
        <p class="field__description">
            Build your authority statement using the WHO-WHAT-WHEN-HOW framework. This will be used to generate your <?php echo esc_html($generator_type); ?>.
        </p>
        
        <!-- Tab Navigation -->
        <div class="tabs">
            <!-- WHO Tab -->
            <input type="radio" id="tabwho" name="authority-tabs" class="tabs__input" checked>
            <label for="tabwho" class="tabs__label">WHO</label>
            <div class="tabs__panel">
                <div class="field">
                    <div class="field__group-header">
                        <span class="authority-hook__field-number">1</span>
                        <h4>WHO do you help?</h4>
                    </div>
                    
                    <div class="field field--with-clear">
                        <input type="text" 
                               id="mkcg-who" 
                               name="who" 
                               class="field__input" 
                               value="<?php echo esc_attr($who); ?>" 
                               placeholder="e.g., busy executives, small business owners">
                        <button type="button" class="field__clear" data-field-id="mkcg-who" title="Clear field">×</button>
                    </div>
                    
                    <div class="examples">
                        <p class="examples__title"><strong>Examples:</strong></p>
                        <span class="tag tag--example" data-target="mkcg-who" data-value="busy executives">busy executives <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-who" data-value="small business owners">small business owners <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-who" data-value="entrepreneurs">entrepreneurs <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-who" data-value="sales professionals">sales professionals <span class="tag__add-link">+ Add</span></span>
                    </div>
                    
                    <!-- Audience Tags Manager -->
                    <div class="tag-manager tag-manager--minimal">
                        <div class="tag-manager__input-group">
                            <input type="text" id="tag_input" class="tag-manager__input" placeholder="Add an audience segment">
                            <button type="button" id="add_tag" class="button button--add">Add</button>
                        </div>
                        <div id="tags_container" class="tag-manager__container"></div>
                    </div>
                </div>
            </div>
            
            <!-- RESULT Tab -->
            <input type="radio" id="tabresult" name="authority-tabs" class="tabs__input">
            <label for="tabresult" class="tabs__label">RESULT</label>
            <div class="tabs__panel">
                <div class="field">
                    <div class="field__group-header">
                        <span class="authority-hook__field-number">2</span>
                        <h4>WHAT result do you help them achieve?</h4>
                    </div>
                    
                    <div class="field field--with-clear">
                        <input type="text" 
                               id="mkcg-result" 
                               name="result" 
                               class="field__input" 
                               value="<?php echo esc_attr($result); ?>" 
                               placeholder="e.g., increase revenue, save time, reduce stress">
                        <button type="button" class="field__clear" data-field-id="mkcg-result" title="Clear field">×</button>
                    </div>
                    
                    <div class="examples">
                        <p class="examples__title"><strong>Examples:</strong></p>
                        <span class="tag tag--example" data-target="mkcg-result" data-value="increase revenue by 40%">increase revenue by 40% <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-result" data-value="save 10+ hours per week">save 10+ hours per week <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-result" data-value="reduce operational costs">reduce operational costs <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-result" data-value="scale their business">scale their business <span class="tag__add-link">+ Add</span></span>
                    </div>
                </div>
            </div>
            
            <!-- WHEN Tab -->
            <input type="radio" id="tabwhen" name="authority-tabs" class="tabs__input">
            <label for="tabwhen" class="tabs__label">WHEN</label>
            <div class="tabs__panel">
                <div class="field">
                    <div class="field__group-header">
                        <span class="authority-hook__field-number">3</span>
                        <h4>WHEN do they need this help?</h4>
                    </div>
                    
                    <div class="field field--with-clear">
                        <input type="text" 
                               id="mkcg-when" 
                               name="when" 
                               class="field__input" 
                               value="<?php echo esc_attr($when); ?>" 
                               placeholder="e.g., during rapid growth, when scaling their team">
                        <button type="button" class="field__clear" data-field-id="mkcg-when" title="Clear field">×</button>
                    </div>
                    
                    <div class="examples">
                        <p class="examples__title"><strong>Examples:</strong></p>
                        <span class="tag tag--example" data-target="mkcg-when" data-value="they're scaling rapidly">they're scaling rapidly <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-when" data-value="facing cash flow challenges">facing cash flow challenges <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-when" data-value="ready to expand their team">ready to expand their team <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-when" data-value="launching a new product">launching a new product <span class="tag__add-link">+ Add</span></span>
                    </div>
                </div>
            </div>
            
            <!-- HOW Tab -->
            <input type="radio" id="tabhow" name="authority-tabs" class="tabs__input">
            <label for="tabhow" class="tabs__label">HOW</label>
            <div class="tabs__panel">
                <div class="field">
                    <div class="field__group-header">
                        <span class="authority-hook__field-number">4</span>
                        <h4>HOW do you help them achieve this?</h4>
                    </div>
                    
                    <div class="field field--with-clear">
                        <input type="text" 
                               id="mkcg-how" 
                               name="how" 
                               class="field__input" 
                               value="<?php echo esc_attr($how); ?>" 
                               placeholder="e.g., through my proven system, with strategic consulting">
                        <button type="button" class="field__clear" data-field-id="mkcg-how" title="Clear field">×</button>
                    </div>
                    
                    <div class="examples">
                        <p class="examples__title"><strong>Examples:</strong></p>
                        <span class="tag tag--example" data-target="mkcg-how" data-value="through my proven 90-day system">through my proven 90-day system <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-how" data-value="with personalized coaching">with personalized coaching <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-how" data-value="using data-driven strategies">using data-driven strategies <span class="tag__add-link">+ Add</span></span>
                        <span class="tag tag--example" data-target="mkcg-how" data-value="via strategic consulting">via strategic consulting <span class="tag__add-link">+ Add</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Live Preview -->
    <div class="authority-hook__preview">
        <div class="authority-hook__preview-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Your Authority Hook
            <span class="authority-hook__ai-label badge badge--ai">AI GENERATED</span>
        </div>
        
        <div id="authority-hook-content" class="authority-hook__content">
            <!-- Content will be populated by JavaScript -->
            I help <span class="authority-hook__highlight">your audience</span> <span class="authority-hook__highlight">achieve results</span> when <span class="authority-hook__highlight">they need you</span> <span class="authority-hook__highlight">through your method</span>.
        </div>
        
        <div class="button-group">
            <button type="button" id="copy-authority-hook-btn" class="button button--copy">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                </svg>
                Copy to Clipboard
            </button>
            
            <a href="#edit-authority-components" id="edit-authority-components" class="authority-hook__edit-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
                Edit Components
            </a>
        </div>
    </div>
    
    <!-- Hidden field to store the complete hook -->
    <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="<?php echo esc_attr($authority_hook); ?>">
</div>

<!-- JavaScript functionality handled by authority-hook-builder.js -->