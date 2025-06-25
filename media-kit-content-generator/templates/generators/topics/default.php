<?php
/**
 * Topics Generator Template - BEM Methodology
 * Default template for generating interview topics
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';

// Try to get entry from URL parameters
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    
    // Use the Formidable service to resolve entry ID
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
        }
    }
}
?>

<div class="generator topics-generator">
    <div class="generator__title">Interview Topics Generator</div>
    
    <!-- Section 1: Authority Hook -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Build Your Authority Hook</div>
        </div>
        <div class="section__content">
            <?php
            // Include the shared Authority Hook component
            $current_values = [];
            include MKCG_PLUGIN_PATH . 'templates/shared/authority-hook-component.php';
            ?>
        </div>
    </div>
    
    <!-- Section 2: Target Audience (Optional) - HIDDEN -->
    <div class="section" style="display: none;">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Target Audience (Optional)</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="topics-audience" class="field__label">Specific Target Audience</label>
                <input type="text" 
                       id="topics-audience" 
                       name="audience" 
                       placeholder="e.g., SaaS startup founders, real estate investors"
                       class="field__input">
                <p class="field__help">
                    Leave blank for general topics, or specify a particular audience to focus the topics on their specific challenges and goals.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Generation Controls -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generate Topics</div>
        </div>
        <div class="section__content">
            <p class="field__description">
                Generate 5 compelling podcast interview topics based on your authority hook and target audience. 
                Topics will be tailored to highlight your expertise and attract podcast hosts.
            </p>
            
            <div class="button-group">
                <button type="button" id="generate-topics-btn" class="button button--ai">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="button__icon">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Generate Topics with AI
                </button>
            </div>
        </div>
    </div>
    
    <!-- Results Section (Initially Hidden) -->
    <div id="topics-results" class="section" style="display: none;">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generated Topics</div>
        </div>
        <div class="section__content">
            <div id="topics-list" class="results">
                <!-- Topics will be populated here by JavaScript -->
            </div>
            
            <div class="button-group">
                <button type="button" id="copy-all-topics-btn" class="button button--copy">
                    Copy All Topics
                </button>
                <button type="button" id="regenerate-topics-btn" class="button button--ai">
                    Regenerate Topics
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="topics-loading-overlay" class="loading" style="display: none;">
        <div class="loading__content">
            <div class="loading__spinner"></div>
            <div class="loading__message">Generating interview topics...</div>
        </div>
    </div>
    
    <!-- Hidden fields for data -->
    <input type="hidden" id="topics-entry-id" value="<?php echo esc_attr($entry_id); ?>">
    <input type="hidden" id="topics-entry-key" value="<?php echo esc_attr($entry_key); ?>">
    <input type="hidden" id="topics-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
</div>

<!-- JavaScript functionality handled by authority-hook-builder.js -->