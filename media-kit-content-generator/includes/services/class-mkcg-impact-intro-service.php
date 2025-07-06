<?php
/**
 * MKCG Impact Intro Service - Centralized Impact Intro Management
 * 
 * Handles all Impact Intro functionality across generators:
 * - Data loading and saving (WordPress post meta only)
 * - HTML rendering for all generators
 * - AJAX endpoint handling
 * - Validation and sanitization
 * - Cross-generator consistency
 * 
 * @package Media_Kit_Content_Generator
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Impact_Intro_Service {
    
    /**
     * Service version for cache busting
     */
    const VERSION = '1.0';
    
    /**
     * Default Impact Intro components - ALWAYS EMPTY
     */
    const DEFAULT_COMPONENTS = [
        'where' => '',
        'why' => ''
    ];
    
    /**
     * Field mappings for WordPress post meta
     */
    private $field_mappings = [
        'postmeta' => [
            'where' => 'impact_where',   // WHERE credentials save to post meta
            'why' => 'impact_why'        // WHY mission saves to post meta
        ]
    ];
    
    /**
     * Initialize the service and register hooks
     */
    public function __construct() {
        add_action('wp_ajax_mkcg_save_impact_intro', [$this, 'handle_save_ajax']);
        add_action('wp_ajax_mkcg_get_impact_intro', [$this, 'handle_get_ajax']);
        add_action('wp_ajax_mkcg_validate_impact_intro', [$this, 'handle_validate_ajax']);
    }
    
    /**
     * Get Impact Intro data from WordPress post meta
     * 
     * @param int $post_id WordPress post ID
     * @return array Impact Intro components
     */
    public function get_impact_intro_data($post_id) {
        error_log('MKCG Impact Intro Service: get_impact_intro_data() called with post_id=' . $post_id);
        
        // Always start with empty components
        $components = self::DEFAULT_COMPONENTS;
        
        if (!$post_id || $post_id <= 0) {
            error_log('MKCG Impact Intro Service: No valid post ID - using empty values');
            return $this->build_complete_response($components, false, 'No valid post ID provided');
        }
        
        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            error_log('MKCG Impact Intro Service: Post not found for ID ' . $post_id);
            return $this->build_complete_response($components, false, 'Post not found');
        }
        
        error_log('MKCG Impact Intro Service: Post found: ' . $post->post_title . ' (type: ' . $post->post_type . ')');
        
        // Load from WordPress post meta
        $components = $this->get_from_postmeta($post_id);
        
        // Sanitize components (security only, no defaults added)
        $components = $this->sanitize_components($components);
        
        error_log('MKCG Impact Intro Service: Final components: ' . json_encode($components));
        
        return $this->build_complete_response($components, !$this->is_default_data($components), 'Impact Intro data loaded successfully');
    }
    
    /**
     * Save Impact Intro data to WordPress post meta
     * 
     * @param int $post_id WordPress post ID
     * @param array $components Impact Intro components
     * @return array Save result with status
     */
    public function save_impact_intro_data($post_id, $components) {
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
     * Render Impact Intro Builder HTML for any generator
     * 
     * @param string $generator_type Generator type (topics, questions, biography, offers)
     * @param array $current_values Current component values
     * @param array $options Rendering options
     * @return string Generated HTML
     */
    public function render_impact_intro_builder($generator_type = 'default', $current_values = [], $options = []) {
        // Set default options
        $options = wp_parse_args($options, [
            'show_preview' => true,
            'show_examples' => true,
            'show_credential_manager' => true,
            'css_classes' => 'impact-intro',
            'field_prefix' => 'mkcg-',
            'tabs_enabled' => true
        ]);
        
        // Sanitize for security, don't add defaults
        $sanitized_values = [];
        foreach (['where', 'why'] as $key) {
            $sanitized_values[$key] = isset($current_values[$key]) ? sanitize_text_field($current_values[$key]) : '';
        }
        $current_values = $sanitized_values;
        
        error_log('MKCG Impact Intro Service: render_impact_intro_builder() using values: ' . json_encode($current_values));
        
        // Use consistent IDs for JavaScript compatibility
        $instance_id = $generator_type;
        
        ob_start();
        ?>
        
        <div class="<?php echo esc_attr($options['css_classes']); ?>" data-generator="<?php echo esc_attr($generator_type); ?>" data-version="<?php echo self::VERSION; ?>">
            
            <!-- Impact Intro Builder -->
            <div class="impact-intro__builder">
                <h3 class="impact-intro__builder-title">
                    Impact Intro Builder
                </h3>
                <p class="field__description">
                    Build your impact statement using the WHERE-WHY framework. This follows your Authority Hook and establishes deeper credibility.
                </p>
                
                <?php if ($options['tabs_enabled']): ?>
                <!-- Tab Navigation -->
                <div class="tabs">
                    <!-- WHERE Tab -->
                    <input type="radio" id="tabwhere" name="impact-tabs" class="tabs__input" checked>
                    <label for="tabwhere" class="tabs__label">WHERE</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_where_field($current_values['where'], $options, $instance_id); ?>
                    </div>
                    
                    <!-- WHY Tab -->
                    <input type="radio" id="tabwhy" name="impact-tabs" class="tabs__input">
                    <label for="tabwhy" class="tabs__label">WHY</label>
                    <div class="tabs__panel">
                        <?php echo $this->render_why_field($current_values['why'], $options, $instance_id); ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Simple form layout (no tabs) -->
                <div class="impact-intro__simple-form">
                    <?php echo $this->render_where_field($current_values['where'], $options, $instance_id); ?>
                    <?php echo $this->render_why_field($current_values['why'], $options, $instance_id); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($options['show_preview']): ?>
            <!-- Live Preview -->
            <div class="impact-intro__preview">
                <div class="impact-intro__preview-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Your Impact Intro
                    <span class="impact-intro__ai-label badge badge--ai">AI GENERATED</span>
                </div>
                
                <div id="impact-intro-content" class="impact-intro__content">
                    I've <span class="impact-intro__highlight"><?php echo esc_html($current_values['where']); ?></span>. 
                    My mission is to <span class="impact-intro__highlight"><?php echo esc_html($current_values['why']); ?></span>.
                </div>
                
                <div class="button-group">
                    <button type="button" id="copy-impact-intro-btn" class="button button--copy">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                        </svg>
                        Copy to Clipboard
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Hidden field to store the complete intro -->
            <input type="hidden" id="mkcg-impact-intro" name="impact_intro" value="<?php echo esc_attr($this->build_complete_intro($current_values)); ?>">
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Build complete Impact Intro sentence from components
     * 
     * @param array $components Impact Intro components
     * @return string Complete Impact Intro sentence
     */
    public function build_complete_intro($components) {
        $components = $this->sanitize_components($components);
        return sprintf(
            'I\'ve %s. My mission is to %s.',
            $components['where'],
            $components['why']
        );
    }
    
    /**
     * Validate Impact Intro components
     * 
     * @param array $components Components to validate
     * @return array Validation result with errors/warnings
     */
    public function validate_impact_intro($components) {
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
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'score' => $this->calculate_intro_score($components)
        ];
    }
    
    /**
     * Handle AJAX request to save Impact Intro - ENHANCED WITH ROOT-LEVEL FIX
     */
    public function handle_save_ajax() {
        error_log("MKCG Impact Intro: handle_save_ajax called");
        error_log("MKCG Impact Intro: POST data: " . json_encode($_POST));
        
        // ROOT FIX: Enhanced nonce verification with detailed logging
        $nonce = $_POST['nonce'] ?? $_POST['security'] ?? '';
        if (!wp_verify_nonce($nonce, 'mkcg_nonce')) {
            error_log("MKCG Impact Intro: Nonce verification failed. Provided: {$nonce}");
            wp_send_json_error(['message' => 'Security check failed', 'error_code' => 'NONCE_FAILED']);
            return;
        }
        
        // ROOT FIX: Enhanced post ID validation
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id || $post_id <= 0) {
            error_log("MKCG Impact Intro: Invalid post ID: {$post_id}");
            wp_send_json_error(['message' => 'Invalid post ID provided', 'error_code' => 'INVALID_POST_ID']);
            return;
        }
        
        // ROOT FIX: Enhanced component validation and sanitization
        $components = [
            'where' => sanitize_textarea_field($_POST['where'] ?? ''),
            'why' => sanitize_textarea_field($_POST['why'] ?? '')
        ];
        
        error_log("MKCG Impact Intro: Sanitized components: " . json_encode($components));
        
        // ROOT FIX: Validate at least one component has content
        $has_content = false;
        foreach ($components as $key => $value) {
            if (!empty(trim($value))) {
                $has_content = true;
                break;
            }
        }
        
        if (!$has_content) {
            error_log("MKCG Impact Intro: No content provided in any component");
            wp_send_json_error([
                'message' => 'Please provide content for at least one field (WHERE or WHY)',
                'error_code' => 'NO_CONTENT',
                'components' => $components
            ]);
            return;
        }
        
        // ROOT FIX: Attempt save with enhanced error handling
        try {
            $result = $this->save_impact_intro_data($post_id, $components);
            error_log("MKCG Impact Intro: Save result: " . json_encode($result));
            
            if ($result['success']) {
                error_log("MKCG Impact Intro: ✅ Save successful, sending success response");
                wp_send_json_success([
                    'message' => $result['message'],
                    'post_id' => $post_id,
                    'components' => $result['components'],
                    'save_details' => $result['save_details'] ?? [],
                    'saved_count' => $result['saved_count'] ?? 0
                ]);
            } else {
                error_log("MKCG Impact Intro: ❌ Save failed, sending error response: " . $result['message']);
                wp_send_json_error([
                    'message' => $result['message'],
                    'error_code' => 'SAVE_FAILED',
                    'post_id' => $post_id,
                    'save_details' => $result['save_details'] ?? [],
                    'components' => $components
                ]);
            }
        } catch (Exception $e) {
            error_log("MKCG Impact Intro: Exception in handle_save_ajax: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Server error during save operation: ' . $e->getMessage(),
                'error_code' => 'SERVER_EXCEPTION',
                'post_id' => $post_id
            ]);
        }
    }
    
    /**
     * Handle AJAX request to get Impact Intro data
     */
    public function handle_get_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'mkcg_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_GET['post_id'] ?? 0);
        
        $result = $this->get_impact_intro_data($post_id);
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle AJAX request to validate Impact Intro
     */
    public function handle_validate_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mkcg_nonce')) {
            wp_die('Security check failed');
        }
        
        $components = [
            'where' => sanitize_text_field($_POST['where'] ?? ''),
            'why' => sanitize_text_field($_POST['why'] ?? '')
        ];
        
        $validation = $this->validate_impact_intro($components);
        $validation['complete_intro'] = $this->build_complete_intro($components);
        
        wp_send_json_success($validation);
    }
    
    // Private helper methods
    
    /**
     * Get Impact Intro data from WordPress post meta
     */
    private function get_from_postmeta($post_id) {
        error_log('MKCG Impact Intro Service: get_from_postmeta() called with post_id=' . $post_id);
        
        // Always start with empty components
        $components = self::DEFAULT_COMPONENTS;
        $field_mappings = $this->field_mappings['postmeta'];
        
        error_log('MKCG Impact Intro Service: Starting with empty defaults: ' . json_encode($components));
        error_log('MKCG Impact Intro Service: Field mappings: ' . json_encode($field_mappings));
        
        foreach ($field_mappings as $component => $field_name) {
            $value = get_post_meta($post_id, $field_name, true);
            error_log('MKCG Impact Intro Service: get_post_meta(' . $post_id . ', "' . $field_name . '") = "' . $value . '"');
            
            if (!empty($value)) {
                $components[$component] = $value;
                error_log('MKCG Impact Intro Service: Set component[' . $component . '] = "' . $value . '"');
            } else {
                error_log('MKCG Impact Intro Service: Component[' . $component . '] remains empty');
            }
        }
        
        return $components;
    }
    
    /**
     * Save Impact Intro data to post meta - ENHANCED WITH ROOT-LEVEL FIX
     */
    private function save_to_postmeta($post_id, $components) {
        try {
            error_log("MKCG Impact Intro: Starting save_to_postmeta for post {$post_id}");
            error_log("MKCG Impact Intro: Components to save: " . json_encode($components));
            
            // Validate post exists
            if (!get_post($post_id)) {
                error_log("MKCG Impact Intro: Post {$post_id} does not exist");
                return ['success' => false, 'message' => "Post {$post_id} does not exist"];
            }
            
            $field_mappings = $this->field_mappings['postmeta'];
            $saved_count = 0;
            $save_attempts = 0;
            $save_details = [];
            
            // ROOT FIX: Save WHERE and WHY to post meta with enhanced validation
            foreach ($components as $component => $value) {
                if (isset($field_mappings[$component])) {
                    $field_name = $field_mappings[$component];
                    $save_attempts++;
                    
                    // Get current value for comparison
                    $current_value = get_post_meta($post_id, $field_name, true);
                    
                    error_log("MKCG Impact Intro: Saving {$component} -> {$field_name}");
                    error_log("MKCG Impact Intro: Current value: '" . $current_value . "'");
                    error_log("MKCG Impact Intro: New value: '" . $value . "'");
                    
                    // ROOT FIX: Always attempt save, even for empty values
                    $result = update_post_meta($post_id, $field_name, $value);
                    
                    // ROOT FIX: Enhanced success detection
                    $save_successful = false;
                    if ($result !== false) {
                        // update_post_meta returns meta_id on insert, true on update, false on failure
                        $save_successful = true;
                        error_log("MKCG Impact Intro: update_post_meta returned: " . var_export($result, true));
                    } else {
                        // Check if the value was actually saved despite false return
                        $verification_value = get_post_meta($post_id, $field_name, true);
                        if ($verification_value === $value) {
                            $save_successful = true;
                            error_log("MKCG Impact Intro: Save verified despite false return - value matches");
                        } else {
                            error_log("MKCG Impact Intro: Save failed - verification mismatch. Expected: '{$value}', Got: '{$verification_value}'");
                        }
                    }
                    
                    if ($save_successful) {
                        $saved_count++;
                        $save_details[] = "{$component} -> {$field_name}: '{$value}'";
                        error_log("MKCG Impact Intro: ✅ Successfully saved {$component} to {$field_name}: {$value}");
                    } else {
                        $save_details[] = "{$component} -> {$field_name}: FAILED";
                        error_log("MKCG Impact Intro: ❌ Failed to save {$component} to {$field_name}: {$value}");
                    }
                }
            }
            
            // ROOT FIX: Save complete intro to legacy field for backward compatibility
            $complete_intro = $this->build_complete_intro($components);
            $legacy_result = update_post_meta($post_id, '_impact_intro_complete', $complete_intro);
            error_log("MKCG Impact Intro: Legacy complete intro save result: " . var_export($legacy_result, true));
            
            // ROOT FIX: Determine success based on attempts vs saves, not just saved_count > 0
            $success_rate = $save_attempts > 0 ? ($saved_count / $save_attempts) * 100 : 0;
            $is_successful = $saved_count >= 1; // At least one component must save
            
            $message = $is_successful 
                ? "Successfully saved {$saved_count}/{$save_attempts} components ({$success_rate}% success rate)" 
                : "Failed to save any components. Attempted {$save_attempts} saves.";
            
            error_log("MKCG Impact Intro: Final result - Success: " . ($is_successful ? 'YES' : 'NO') . 
                     ", Saved: {$saved_count}/{$save_attempts}, Rate: {$success_rate}%");
            error_log("MKCG Impact Intro: Save details: " . implode(' | ', $save_details));
            
            return [
                'success' => $is_successful,
                'message' => $message,
                'saved_count' => $saved_count,
                'save_attempts' => $save_attempts,
                'success_rate' => $success_rate,
                'save_details' => $save_details
            ];
        } catch (Exception $e) {
            error_log("MKCG Impact Intro: Save error - " . $e->getMessage());
            error_log("MKCG Impact Intro: Exception trace: " . $e->getTraceAsString());
            return [
                'success' => false, 
                'message' => 'Save error: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Render WHERE field HTML
     */
    private function render_where_field($value, $options, $instance_id) {
        ob_start();
        ?>
        <div class="field">
            <div class="field__group-header">
                <span class="impact-intro__field-number">1</span>
                <h4>Showcase Your Authority (WHERE)</h4>
            </div>
            
            <div class="field field--with-clear">
                <label for="mkcg-where" class="field__label">What specific results or credentials establish your authority?</label>
                <input type="text" 
                id="mkcg-where" 
                name="where" 
                class="field__input" 
                value="<?php echo esc_attr($value); ?>" 
                placeholder="<?php echo empty($value) ? 'e.g., Helped 200+ startups land funding' : ''; ?>">
                <button type="button" class="field__clear" data-field-id="mkcg-where" title="Clear field">×</button>
            </div>
            
            <?php if ($options['show_credential_manager']): ?>
            <div class="credentials-manager credentials-manager--primary">
                <label>🏆 <strong>Credential Manager</strong> - Add and Select Your Credentials:</label>
                <p class="helper-text">This is where you manage your credentials. Add new ones and check the boxes to include them in your Impact Intro.</p>
                <div class="input-container">
                    <input type="text" id="credential_input" placeholder="Type a credential and click Add">
                    <button type="button" id="add_credential" class="button">Add</button>
                </div>
                <div id="credentials_container" class="credentials-container--enhanced"></div>
                
                <div class="credential-manager-status">
                    <small class="status-text">📊 <span id="credential-count">0</span> credentials added | <span id="selected-credential-count">0</span> selected for Impact Intro</small>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="tag tag--example" data-target="mkcg-where" data-value="helped 200+ startups land funding">helped 200+ startups land funding<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-where" data-value="my strategies have helped over 500 coaches generate six-figure businesses">my strategies have helped over 500 coaches generate six-figure businesses<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-where" data-value="my latest book became an Amazon bestseller">my latest book became an Amazon bestseller<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-where" data-value="our organization has provided clean water to 200,000+ people">our organization has provided clean water to 200,000+ people<span class="tag__add-link">+ Add</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WHY field HTML
     */
    private function render_why_field($value, $options, $instance_id) {
        ob_start();
        ?>
        <div class="field">
            <div class="field__group-header">
                <span class="impact-intro__field-number">2</span>
                <h4>Share Your Purpose (WHY)</h4>
            </div>
            
            <div class="field field--with-clear">
                <label for="mkcg-why" class="field__label">What big idea, mission, or purpose drives your work?</label>
                <input type="text" 
                id="mkcg-why" 
                name="why" 
                class="field__input" 
                value="<?php echo esc_attr($value); ?>" 
                placeholder="<?php echo empty($value) ? 'e.g., democratize access to business education' : ''; ?>">
                <button type="button" class="field__clear" data-field-id="mkcg-why" title="Clear field">×</button>
            </div>
            
            <?php if ($options['show_examples']): ?>
            <div class="examples">
                <p class="examples__title"><strong>Examples:</strong></p>
                <span class="tag tag--example" data-target="mkcg-why" data-value="democratize access to growth strategies that were once only available to venture-backed companies">democratize access to growth strategies<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-why" data-value="help thought leaders amplify messages that can transform lives and businesses">help thought leaders amplify transformative messages<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-why" data-value="ensure clean water is a fundamental human right for everyone">ensure clean water as a fundamental human right<span class="tag__add-link">+ Add</span></span>
                <span class="tag tag--example" data-target="mkcg-why" data-value="make expert-led businesses more fulfilling than traditional corporate careers">make expert-led businesses more fulfilling<span class="tag__add-link">+ Add</span></span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Sanitize and validate components
     */
    private function sanitize_components($components) {
        $sanitized = [];
        
        foreach (self::DEFAULT_COMPONENTS as $key => $default) {
            $value = $components[$key] ?? '';
            $sanitized[$key] = sanitize_text_field($value);
        }
        
        return $sanitized;
    }
    
    /**
     * Check if components contain only default data
     */
    private function is_default_data($components) {
        foreach (self::DEFAULT_COMPONENTS as $key => $default) {
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
            'complete_intro' => $this->build_complete_intro($components),
            'has_data' => $has_data,
            'message' => $message,
            'version' => self::VERSION
        ];
    }
    
    /**
     * Calculate Impact Intro quality score
     */
    private function calculate_intro_score($components) {
        $score = 0;
        
        // Check for customization (non-empty values)
        foreach (self::DEFAULT_COMPONENTS as $key => $default) {
            if ($components[$key] !== $default && !empty($components[$key])) {
                $score += 50; // 50 points per customized component
            }
        }
        
        return min($score, 100); // Cap at 100
    }
}
