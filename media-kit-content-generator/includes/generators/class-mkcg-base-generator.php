<?php
/**
 * MKCG Base Generator
 * Abstract base class that all generators extend
 */

abstract class MKCG_Base_Generator {
    
    protected $api_service;
    protected $formidable_service;
    protected $authority_hook_service;
    protected $generator_type;
    
    public function __construct($api_service, $formidable_service, $authority_hook_service) {
        $this->api_service = $api_service;
        $this->formidable_service = $formidable_service;
        $this->authority_hook_service = $authority_hook_service;
    }
    
    /**
     * Initialize the generator (called by main plugin)
     */
    public function init() {
        $this->register_ajax_actions();
        $this->register_shortcodes();
        $this->enqueue_generator_assets();
    }
    
    /**
     * Register AJAX actions for this generator
     */
    protected function register_ajax_actions() {
        $ajax_action = 'generate_' . $this->generator_type;
        add_action('wp_ajax_' . $ajax_action, [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_' . $ajax_action, [$this, 'handle_ajax_generation']);
        
        // Debug action
        $debug_action = 'debug_' . $this->generator_type;
        add_action('wp_ajax_' . $debug_action, [$this, 'handle_ajax_debug']);
        add_action('wp_ajax_nopriv_' . $debug_action, [$this, 'handle_ajax_debug']);
    }
    
    /**
     * Register shortcodes for this generator
     */
    protected function register_shortcodes() {
        add_shortcode('mkcg_' . $this->generator_type, [$this, 'render_shortcode']);
    }
    
    /**
     * Enqueue generator-specific assets
     */
    protected function enqueue_generator_assets() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Handle AJAX generation requests
     */
    public function handle_ajax_generation() {
        // Verify nonce
        if (!check_ajax_referer('mkcg_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get and validate input data
        $input_data = $this->get_ajax_input_data();
        $validation_result = $this->validate_input($input_data);
        
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build the prompt
        $prompt = $this->build_prompt($input_data);
        
        if (!$prompt) {
            wp_send_json_error(['message' => 'Failed to build prompt']);
            return;
        }
        
        // Generate content using API service
        $api_response = $this->api_service->generate_content(
            $prompt, 
            $this->generator_type,
            $this->get_api_options($input_data)
        );
        
        if (!$api_response['success']) {
            wp_send_json_error($api_response);
            return;
        }
        
        // Format the output for this generator
        $formatted_output = $this->format_output($api_response['content']);
        
        // Save to Formidable if entry ID provided
        if (!empty($input_data['entry_id'])) {
            $this->save_to_formidable($input_data['entry_id'], $formatted_output);
        }
        
        // Return success response
        wp_send_json_success([
            'content' => $formatted_output,
            'generator_type' => $this->generator_type,
            'entry_id' => $input_data['entry_id'] ?? null
        ]);
    }
    
    /**
     * Handle AJAX debug requests
     */
    public function handle_ajax_debug() {
        if (!check_ajax_referer('mkcg_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'No entry ID provided']);
            return;
        }
        
        $debug_info = $this->formidable_service->debug_entry_fields($entry_id);
        $debug_info['generator_type'] = $this->generator_type;
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'template' => 'default',
            'entry_id' => '',
            'class' => ''
        ], $atts);
        
        ob_start();
        $this->render_form($atts);
        return ob_get_clean();
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if ($this->should_load_generator_assets()) {
            // Enqueue generator-specific JavaScript
            $js_file = 'assets/js/generators/' . $this->generator_type . '.js';
            if (file_exists(MKCG_PLUGIN_PATH . $js_file)) {
                wp_enqueue_script(
                    'mkcg-' . $this->generator_type,
                    MKCG_PLUGIN_URL . $js_file,
                    ['mkcg-form-utils'],
                    MKCG_VERSION,
                    true
                );
            }
            
            // Localize script with generator-specific data
            wp_localize_script('mkcg-form-utils', 'mkcg_' . $this->generator_type . '_vars', [
                'generator_type' => $this->generator_type,
                'ajax_action' => 'generate_' . $this->generator_type,
                'debug_action' => 'debug_' . $this->generator_type,
                'nonce' => wp_create_nonce('mkcg_nonce')
            ]);
        }
    }
    
    /**
     * Check if generator assets should be loaded
     */
    protected function should_load_generator_assets() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check for this generator's shortcode
        if (has_shortcode($post->post_content, 'mkcg_' . $this->generator_type)) {
            return true;
        }
        
        // Check for Formidable edit pages
        if (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit' && isset($_GET['entry'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get input data from AJAX request
     */
    protected function get_ajax_input_data() {
        $data = [];
        
        // Common fields
        $data['entry_id'] = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $data['entry_key'] = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        
        // If we have entry_key but no entry_id, resolve it
        if (!$data['entry_id'] && $data['entry_key']) {
            $entry_data = $this->formidable_service->get_entry_data($data['entry_key']);
            if ($entry_data['success']) {
                $data['entry_id'] = $entry_data['entry_id'];
            }
        }
        
        // Get Authority Hook if available
        if ($data['entry_id']) {
            $authority_hook_result = $this->authority_hook_service->get_authority_hook($data['entry_id']);
            if ($authority_hook_result['success']) {
                $data['authority_hook'] = $authority_hook_result['value'];
            }
        }
        
        // Add generator-specific fields
        $generator_data = $this->get_generator_specific_input();
        $data = array_merge($data, $generator_data);
        
        return $data;
    }
    
    /**
     * Save generated content to Formidable
     */
    protected function save_to_formidable($entry_id, $content) {
        $field_mappings = $this->get_field_mappings();
        
        if (!empty($field_mappings)) {
            $result = $this->formidable_service->save_generated_content(
                $entry_id, 
                $content, 
                $field_mappings
            );
            
            if ($result['success']) {
                error_log("MKCG {$this->generator_type}: Content saved to Formidable entry {$entry_id}");
            } else {
                error_log("MKCG {$this->generator_type}: Failed to save content to Formidable entry {$entry_id}");
            }
        }
    }
    
    /**
     * Render the generator form
     */
    protected function render_form($atts) {
        $template_path = $this->get_template_path($atts['template']);
        
        if (file_exists($template_path)) {
            // Make variables available to template
            $generator_type = $this->generator_type;
            $form_fields = $this->get_form_fields();
            $authority_hook_service = $this->authority_hook_service;
            $formidable_service = $this->formidable_service;
            $api_service = $this->api_service;
            
            include $template_path;
        } else {
            echo '<p>Template not found: ' . esc_html($template_path) . '</p>';
        }
    }
    
    /**
     * Get template path
     */
    protected function get_template_path($template_name = 'default') {
        $template_file = $template_name . '.php';
        $template_path = MKCG_PLUGIN_PATH . 'templates/generators/' . $this->generator_type . '/' . $template_file;
        
        // Fallback to default template
        if (!file_exists($template_path)) {
            $template_path = MKCG_PLUGIN_PATH . 'templates/generators/' . $this->generator_type . '/default.php';
        }
        
        return $template_path;
    }
    
    // Abstract methods that each generator must implement
    
    /**
     * Get form fields configuration for this generator
     */
    abstract public function get_form_fields();
    
    /**
     * Validate input data
     */
    abstract public function validate_input($data);
    
    /**
     * Build the prompt for API generation
     */
    abstract public function build_prompt($data);
    
    /**
     * Format the API response for this generator
     */
    abstract public function format_output($api_response);
    
    /**
     * Get generator-specific input from AJAX request
     */
    abstract protected function get_generator_specific_input();
    
    /**
     * Get field mappings for saving to Formidable
     */
    abstract protected function get_field_mappings();
    
    /**
     * Get API options for this generator
     */
    abstract protected function get_api_options($input_data);
}