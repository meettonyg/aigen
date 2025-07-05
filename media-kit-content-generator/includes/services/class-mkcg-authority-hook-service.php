<?php
/**
 * MKCG Authority Hook Service - Centralized Authority Hook Management
 * 
 * Handles all Authority Hook functionality across generators:
 * - Data loading and saving (WordPress post meta only)
 * - HTML rendering for all generators
 * - AJAX endpoint handling
 * - Validation and sanitization
 * - Cross-generator consistency
 * 
 * @package Media_Kit_Content_Generator
 * @version 2.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Authority_Hook_Service {
    
    /**
     * Service version for cache busting
     */
    const VERSION = '2.1';
    
    /**
     * Default Authority Hook components
     * Updated to be empty when no entry parameter provided
     */
    const DEFAULT_COMPONENTS = [
        'who' => '',
        'what' => '', 
        'when' => '',
        'how' => ''
    ];
    
    /**
     * Legacy default values for when entry parameters exist
     */
    const LEGACY_DEFAULT_COMPONENTS = [
        'who' => 'your audience',
        'what' => 'achieve their goals', 
        'when' => 'they need help',
        'how' => 'through your method'
    ];
    
    /**
     * Field mappings for WordPress post meta - FIXED to match Pods Service expectations
     */
    private $field_mappings = [
        'postmeta' => [
            'who' => 'guest_title', // Use existing guest_title field for WHO component
            'what' => 'hook_what',   // Match Pods Service field names
            'when' => 'hook_when',   // Match Pods Service field names 
            'how' => 'hook_how'      // Match Pods Service field names
        ]
    ];
    
    /**
     * Initialize the service and register hooks
     */
    public function __construct() {
        add_action('wp_ajax_mkcg_save_authority_hook', [$this, 'handle_save_ajax']);
        add_action('wp_ajax_mkcg_get_authority_hook', [$this, 'handle_get_ajax']);
        add_action('wp_ajax_mkcg_validate_authority_hook', [$this, 'handle_validate_ajax']);
    }
    
    /**
     * Get Authority Hook data from WordPress post meta
     * Updated to check for entry parameters and not provide defaults when no entry param
     * 
     * @param int $post_id WordPress post ID
     * @return array Authority Hook components
     */
    public function get_authority_hook_data($post_id) {
        // Check for entry parameter - use different defaults based on presence
        $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                           (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        
        $components = $has_entry_param ? self::LEGACY_DEFAULT_COMPONENTS : self::DEFAULT_COMPONENTS;
        
        if (!$post_id || $post_id <= 0) {
            error_log('MKCG Authority Hook Service: No valid post ID - using ' . ($has_entry_param ? 'legacy defaults' : 'empty values'));
            return $this->build_complete_response($components, false, 'No valid post ID provided');
        }
        
        // Load from WordPress post meta
        $components = $this->get_from_postmeta($post_id, $has_entry_param);
        
        // Ensure all required components exist with fallbacks
        $components = $this->sanitize_components($components, $has_entry_param);
        
        return $this->build_complete_response($components, !$this->is_default_data($components, $has_entry_param), 'Authority Hook data loaded successfully');
    }
    
    /**
     * Save Authority Hook data to WordPress post meta
     * 
     * @param int $post_id WordPress post ID
     * @param array $components Authority Hook components
     * @return array Save result with status
     */
    public function save_authority_hook_data($post_id, $components) {
        if (!$post_id || $post_id <= 0) {
            return ['success' => false, 'message' => 'Invalid post ID'];
        }
        
        $components = $this->sanitize_components($components);
        
        // Save to WordPress post meta only
        $result = $this->save_to_postmeta($post_id, $components);
        
        return [
            'success' => $result['success'],
            'message' => $result['message'],
            'components' => $components
        ];
    }
    
    /**
     * Render Authority Hook Builder HTML for any generator
     * 
     * @param string $generator_type Generator type (topics, questions, biography, offers)
     * @param array $current_values Current component values
     * @param array $options Rendering options
     * @return string Generated HTML
     */
    public function render_authority_hook_builder($generator_type = 'default', $current_values = [], $options = []) {
        // Set default options
        $options = wp_parse_args($options, [
            'show_preview' => false,
            'show_examples' => true,
            'show_audience_manager' => true,
            'css_classes' => 'authority-hook',
            'field_prefix' => 'mkcg-',
            'tabs_enabled' => true
        ]);
        
        // Sanitize current values
        $current_values = $this->sanitize_components($current_values);
        
        // Use consistent IDs for JavaScript compatibility
        // Don't use unique IDs as it breaks JavaScript expectations
        $instance_id = $generator_type; // Use generator type as instance ID
        
        ob_start();
        ?>
        
        <div class="<?php echo esc_attr($options['css_classes']); ?>" data-generator="<?php echo esc_attr($generator_type); ?>" data-version="<?php echo self::VERSION; ?>">
            
            <!-- Authority Hook Builder -->
            <div class="authority-hook__builder">
                <h3 class="authority-hook__builder-title">
                    <span class="authority-hook__field-number">1</span>
                    Authority Hook Builder
                </h3>
                <p class="field__description">
                    Build your authority statement using the WHO-WHAT-WHEN-HOW framework. This will be used to generate your <?php echo esc_html($generator_type); ?>.
                </p>
                
                <?php if ($options['tabs_enabled']): ?>
                <!-- Tab Navigation -->
                <div class="tabs">
                    <!-- WHO Tab -->
                    <input type="radio" id="tabwho" name="authority-tabs" class="tabs__input" checked>
                    <label for="tabwho" class="tabs__label">WHO</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_who_field($current_values['who'], $options, $instance_id); ?>
                    </div>
                    
                    <!-- RESULT Tab -->
                    <input type="radio" id="tabresult" name="authority-tabs" class="tabs__input">
                    <label for="tabresult" class="tabs__label">RESULT</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_result_field($current_values['what'], $options, $instance_id); ?>
                    </div>
                    
                    <!-- WHEN Tab -->
                    <input type="radio" id="tabwhen" name="authority-tabs" class="tabs__input">
                    <label for="tabwhen" class="tabs__label">WHEN</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_when_field($current_values['when'], $options, $instance_id); ?>
                    </div>
                    
                    <!-- HOW Tab -->
                    <input type="radio" id="tabhow" name="authority-tabs" class="tabs__input">
                    <label for="tabhow" class="tabs__label">HOW</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_how_field($current_values['how'], $options, $instance_id); ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Simple form layout (no tabs) -->
                <div class="authority-hook__simple-form">
                    <?php echo $this->render_who_field($current_values['who'], $options, $instance_id); ?>
                    <?php echo $this->render_result_field($current_values['what'], $options, $instance_id); ?>
                    <?php echo $this->render_when_field($current_values['when'], $options, $instance_id); ?>
                    <?php echo $this->render_how_field($current_values['how'], $options, $instance_id); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($options['show_preview']): ?>
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
                    I help <span class="authority-hook__highlight"><?php echo esc_html($current_values['who']); ?></span> 
                    <span class="authority-hook__highlight"><?php echo esc_html($current_values['what']); ?></span> 
                    when <span class="authority-hook__highlight"><?php echo esc_html($current_values['when']); ?></span> 
                    <span class="authority-hook__highlight"><?php echo esc_html($current_values['how']); ?></span>.
                </div>
                
                <div class="button-group">
                    <button type="button" id="copy-authority-hook-btn" class="button button--copy">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                        </svg>
                        Copy to Clipboard
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Hidden field to store the complete hook -->
            <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="<?php echo esc_attr($this->build_complete_hook($current_values)); ?>">
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Build complete Authority Hook sentence from components
     * 
     * @param array $components Authority Hook components
     * @return string Complete Authority Hook sentence
     */
    public function build_complete_hook($components) {
        $components = $this->sanitize_components($components);
        return sprintf(
            'I help %s %s when %s %s.',
            $components['who'],
            $components['what'],
            $components['when'],
            $components['how']
        );
    }
    
    /**
     * Validate Authority Hook components
     * 
     * @param array $components Components to validate
     * @return array Validation result with errors/warnings
     */
    public function validate_authority_hook($components) {
        $errors = [];
        $warnings = [];
        
        // Check required fields
        foreach (self::DEFAULT_COMPONENTS as $key => $default) {
            if (empty($components[$key]) || $components[$key] === $default) {
                $warnings[] = "The '{$key}' component is using default text. Consider customizing it.";
            }
        }
        
        // Check for overly long components
        foreach ($components as $key => $value) {
            if (strlen($value) > 200) {
                $warnings[] = "The '{$key}' component is very long. Consider shortening for better impact.";
            }
        }
        
        // Check for common issues
        if (strpos($components['who'], 'everyone') !== false) {
            $warnings[] = "Targeting 'everyone' is too broad. Consider a more specific audience.";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'score' => $this->calculate_hook_score($components)
        ];
    }
    
    /**
     * Handle AJAX request to save Authority Hook
     */
    public function handle_save_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $components = [
            'who' => sanitize_text_field($_POST['who'] ?? ''),
            'what' => sanitize_text_field($_POST['what'] ?? ''),
            'when' => sanitize_text_field($_POST['when'] ?? ''),
            'how' => sanitize_text_field($_POST['how'] ?? '')
        ];
        
        $result = $this->save_authority_hook_data($post_id, $components);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Handle AJAX request to get Authority Hook data
     */
    public function handle_get_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'mkcg_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_GET['post_id'] ?? 0);
        
        $result = $this->get_authority_hook_data($post_id);
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle AJAX request to validate Authority Hook
     */
    public function handle_validate_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_die('Security check failed');
        }
        
        $components = [
            'who' => sanitize_text_field($_POST['who'] ?? ''),
            'what' => sanitize_text_field($_POST['what'] ?? ''),
            'when' => sanitize_text_field($_POST['when'] ?? ''),
            'how' => sanitize_text_field($_POST['how'] ?? '')
        ];
        
        $validation = $this->validate_authority_hook($components);
        $validation['complete_hook'] = $this->build_complete_hook($components);
        
        wp_send_json_success($validation);
    }
    
    // Private helper methods
    
    /**
     * Get Authority Hook data from WordPress post meta - FIXED to use correct field names
     * Updated to handle entry parameter presence
     */
    private function get_from_postmeta($post_id, $has_entry_param = false) {
        $components = $has_entry_param ? self::LEGACY_DEFAULT_COMPONENTS : self::DEFAULT_COMPONENTS;
        $field_mappings = $this->field_mappings['postmeta'];
        
        foreach ($field_mappings as $component => $field_name) {
            $value = get_post_meta($post_id, $field_name, true);
            if (!empty($value)) {
                $components[$component] = $value;
            }
        }
        
        return $components;
    }
    
    /**
     * Save Authority Hook data to WordPress post meta - FIXED to use correct field names
     */
    private function save_to_postmeta($post_id, $components) {
        try {
            $field_mappings = $this->field_mappings['postmeta'];
            $saved_count = 0;
            
            foreach ($components as $component => $value) {
                if (isset($field_mappings[$component])) {
                    $field_name = $field_mappings[$component];
                    $result = update_post_meta($post_id, $field_name, $value);
                    if ($result !== false) {
                        $saved_count++;
                        error_log("MKCG Authority Hook: Saved {$component} to field {$field_name}: {$value}");
                    }
                }
            }
            
            // Save complete hook to the legacy field for backward compatibility
            $complete_hook = $this->build_complete_hook($components);
            update_post_meta($post_id, '_authority_hook_complete', $complete_hook);
            
            error_log("MKCG Authority Hook: Saved {$saved_count} components to post {$post_id}");
            
            return [
                'success' => $saved_count > 0, 
                'message' => $saved_count > 0 ? "Saved {$saved_count} components to correct fields" : 'No components saved',
                'saved_count' => $saved_count
            ];
        } catch (Exception $e) {
            error_log("MKCG Authority Hook: Save error - " . $e->getMessage());
            return ['success' => false, 'message' => 'Post meta save error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Render WHO field HTML
     */
    private function render_who_field($value, $options, $instance_id) {
        ob_start();
        ?>
        <div class="field">
            <div class="field__group-header">
                <span class="authority-hook__field-number">1</span>
                <h4>WHO do you help?</h4>
            </div>
            
            <div class="field field--with-clear">
                <input type="text" 
                       id="mkcg-who" 
                       name="who" 
                       class="field__input field__input--readonly" 
                       value="<?php echo esc_attr($value); ?>" 
                       placeholder="Selected audiences will appear here automatically"
                       readonly>
                <button type="button" class="field__clear" data-field-id="mkcg-who" title="Clear all audiences">×</button>
            </div>
            
            <?php if ($options['show_audience_manager']): ?>
            <p class="field__helper-text">💡 <strong>Use the audience manager below</strong> to add and select your target audiences</p>
            
            <div class="credentials-manager credentials-manager--primary">
                <label>🎯 <strong>Audience Manager</strong> - Add and Select Your Target Audiences:</label>
                <p class="helper-text">This is where you manage your audiences. Add new ones and check the boxes to include them in your Authority Hook.</p>
                <div class="input-container">
                    <input type="text" id="tag_input" placeholder="Type an audience (e.g., SaaS founders) and press Enter">
                    <button type="button" id="add_tag" class="button">Add Audience</button>
                    </div>
                    <div id="tags_container" class="tags-container--enhanced"></div>
                    
                    <div class="audience-manager-status">
                    <small class="status-text">📊 <span id="audience-count">0</span> audiences added | <span id="selected-count">0</span> selected for Authority Hook</small>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="example-chip field-chip" data-target="mkcg-who" data-value="SaaS founders">SaaS founders<span class="add-to-list" data-value="SaaS founders">+ Add to List</span></span>
                <span class="example-chip field-chip" data-target="mkcg-who" data-value="Business coaches">Business coaches<span class="add-to-list" data-value="Business coaches">+ Add to List</span></span>
                <span class="example-chip field-chip" data-target="mkcg-who" data-value="Authors launching a book">Authors launching a book<span class="add-to-list" data-value="Authors launching a book">+ Add to List</span></span>
                <span class="example-chip field-chip" data-target="mkcg-who" data-value="Real estate investors">Real estate investors<span class="add-to-list" data-value="Real estate investors">+ Add to List</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render RESULT field HTML
     */
    private function render_result_field($value, $options, $instance_id) {
        ob_start();
        ?>
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
                       value="<?php echo esc_attr($value); ?>" 
                       placeholder="e.g., increase revenue, save time, reduce stress">
                <button type="button" class="field__clear" data-field-id="mkcg-result" title="Clear field">×</button>
            </div>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="tag tag--example" data-target="mkcg-result" data-value="increase revenue by 40%">increase revenue by 40% <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-result" data-value="save 10+ hours per week">save 10+ hours per week <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-result" data-value="reduce operational costs">reduce operational costs <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-result" data-value="scale their business">scale their business <span class="tag__add-link">+ Add</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WHEN field HTML
     */
    private function render_when_field($value, $options, $instance_id) {
        ob_start();
        ?>
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
                       value="<?php echo esc_attr($value); ?>" 
                       placeholder="e.g., during rapid growth, when scaling their team">
                <button type="button" class="field__clear" data-field-id="mkcg-when" title="Clear field">×</button>
            </div>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="tag tag--example" data-target="mkcg-when" data-value="they're scaling rapidly">they're scaling rapidly <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-when" data-value="facing cash flow challenges">facing cash flow challenges <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-when" data-value="ready to expand their team">ready to expand their team <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-when" data-value="launching a new product">launching a new product <span class="tag__add-link">+ Add</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render HOW field HTML
     */
    private function render_how_field($value, $options, $instance_id) {
        ob_start();
        ?>
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
                       value="<?php echo esc_attr($value); ?>" 
                       placeholder="e.g., through my proven system, with strategic consulting">
                <button type="button" class="field__clear" data-field-id="mkcg-how" title="Clear field">×</button>
            </div>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="tag tag--example" data-target="mkcg-how" data-value="through my proven 90-day system">through my proven 90-day system <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-how" data-value="with personalized coaching">with personalized coaching <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-how" data-value="using data-driven strategies">using data-driven strategies <span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-how" data-value="via strategic consulting">via strategic consulting <span class="tag__add-link">+ Add</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Sanitize and validate components
     * Updated to handle entry parameter presence
     */
    private function sanitize_components($components, $has_entry_param = null) {
        // Auto-detect entry param if not provided
        if ($has_entry_param === null) {
            $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                               (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        }
        
        $defaults = $has_entry_param ? self::LEGACY_DEFAULT_COMPONENTS : self::DEFAULT_COMPONENTS;
        $sanitized = [];
        
        foreach ($defaults as $key => $default) {
            $value = $components[$key] ?? $default;
            $sanitized[$key] = sanitize_text_field($value);
            
            // Only use default if entry param exists and field is empty
            if (empty($sanitized[$key]) && $has_entry_param) {
                $sanitized[$key] = $default;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Check if components contain only default data
     * Updated to handle entry parameter presence
     */
    private function is_default_data($components, $has_entry_param = null) {
        // Auto-detect entry param if not provided
        if ($has_entry_param === null) {
            $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                               (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        }
        
        $defaults = $has_entry_param ? self::LEGACY_DEFAULT_COMPONENTS : self::DEFAULT_COMPONENTS;
        
        foreach ($defaults as $key => $default) {
            if (($components[$key] ?? $default) !== $default) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Build complete response with metadata
     */
    private function build_complete_response($components, $has_data, $message) {
        return [
            'components' => $components,
            'complete_hook' => $this->build_complete_hook($components),
            'has_data' => $has_data,
            'message' => $message,
            'version' => self::VERSION
        ];
    }
    
    /**
     * Calculate Authority Hook quality score
     * Updated to handle both default sets
     */
    private function calculate_hook_score($components) {
        $score = 0;
        
        // Check for entry parameter
        $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                           (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        
        $defaults = $has_entry_param ? self::LEGACY_DEFAULT_COMPONENTS : self::DEFAULT_COMPONENTS;
        
        // Check for customization (not default values)
        foreach ($defaults as $key => $default) {
            if ($components[$key] !== $default && !empty($components[$key])) {
                $score += 25; // 25 points per customized component
            }
        }
        
        return min($score, 100); // Cap at 100
    }
}
