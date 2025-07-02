<?php
/**
 * MKCG Biography Generator
 * Generates professional biographies based on Authority Hook and additional details
 */

class MKCG_Biography_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'biography';
    
    /**
     * Get form fields configuration
     */
    public function get_form_fields() {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'Full Name',
                'required' => true
            ],
            'title' => [
                'type' => 'text',
                'label' => 'Professional Title',
                'required' => true
            ],
            'organization' => [
                'type' => 'text',
                'label' => 'Organization/Company',
                'required' => false
            ],
            'authority_hook' => [
                'type' => 'textarea',
                'label' => 'Authority Hook',
                'required' => true,
                'description' => 'Your expert introduction statement'
            ],
            'impact_intro' => [
                'type' => 'textarea',
                'label' => 'Impact Introduction',
                'required' => false,
                'description' => 'Additional impact statements'
            ],
            'tone' => [
                'type' => 'select',
                'label' => 'Tone',
                'options' => [
                    'professional' => 'Professional',
                    'conversational' => 'Conversational',
                    'authoritative' => 'Authoritative',
                    'friendly' => 'Friendly'
                ],
                'default' => 'professional'
            ],
            'length' => [
                'type' => 'select',
                'label' => 'Length',
                'options' => [
                    'short' => 'Short (50-75 words)',
                    'medium' => 'Medium (100-150 words)',
                    'long' => 'Long (200-300 words)'
                ],
                'default' => 'medium'
            ],
            'pov' => [
                'type' => 'select',
                'label' => 'Point of View',
                'options' => [
                    'third' => 'Third Person (He/She)',
                    'first' => 'First Person (I/My)'
                ],
                'default' => 'third'
            ]
        ];
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data) {
        $errors = [];
        
        if (empty($data['name']) && empty($data['title'])) {
            $errors[] = 'Either name or professional title is required';
        }
        
        if (empty($data['authority_hook'])) {
            $errors[] = 'Authority Hook is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for biography generation
     */
    public function build_prompt($data) {
        $name = $data['name'] ?? '';
        $title = $data['title'] ?? '';
        $organization = $data['organization'] ?? '';
        $authority_hook = $data['authority_hook'];
        $impact_intro = $data['impact_intro'] ?? '';
        $tone = $data['tone'] ?? 'professional';
        $length = $data['length'] ?? 'medium';
        $pov = $data['pov'] ?? 'third';
        
        $prompt = "You are an expert biography writer specializing in creating compelling professional biographies for experts and thought leaders.

**TASK:** Create a professional biography based on the following information:

**Personal Information:**
- Name: $name
- Title: $title";
        
        if ($organization) {
            $prompt .= "\n- Organization: $organization";
        }
        
        $prompt .= "\n\n**Authority Statement:** $authority_hook";
        
        if ($impact_intro) {
            $prompt .= "\n\n**Additional Impact Information:** $impact_intro";
        }
        
        $prompt .= "\n\n**REQUIREMENTS:**
- Tone: $tone
- Length: $length
- Point of View: " . ($pov === 'first' ? 'First person (I/my)' : 'Third person (he/she/they)') . "
- Focus on expertise, credibility, and impact
- Include specific results and achievements when possible
- Make it engaging and professional

Please generate THREE versions:
1. **Short Bio (50-75 words):** Concise version for brief introductions
2. **Medium Bio (100-150 words):** Standard version for most uses  
3. **Long Bio (200-300 words):** Detailed version for speaking engagements

Format each version with clear headers.";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service handles biography formatting
        if (is_array($api_response)) {
            return $api_response;
        }
        
        // Fallback parsing if needed
        $biographies = [];
        
        if (preg_match('/Short Bio.*?:(.*?)(?=Medium Bio|Long Bio|$)/s', $api_response, $matches)) {
            $biographies['short'] = trim($matches[1]);
        }
        
        if (preg_match('/Medium Bio.*?:(.*?)(?=Long Bio|Short Bio|$)/s', $api_response, $matches)) {
            $biographies['medium'] = trim($matches[1]);
        }
        
        if (preg_match('/Long Bio.*?:(.*?)(?=Short Bio|Medium Bio|$)/s', $api_response, $matches)) {
            $biographies['long'] = trim($matches[1]);
        }
        
        if (empty($biographies)) {
            $biographies['full'] = $api_response;
        }
        
        return $biographies;
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        // Handle both JSON form data and individual fields
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
        
        if (!empty($form_data)) {
            return $form_data;
        }
        
        // Fallback to individual field extraction
        return [
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'organization' => isset($_POST['organization']) ? sanitize_text_field($_POST['organization']) : '',
            'impact_intro' => isset($_POST['impact_intro']) ? sanitize_textarea_field($_POST['impact_intro']) : '',
            'tone' => isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : 'professional',
            'length' => isset($_POST['length']) ? sanitize_text_field($_POST['length']) : 'medium',
            'pov' => isset($_POST['pov']) ? sanitize_text_field($_POST['pov']) : 'third'
        ];
    }
    
    /**
     * Get field mappings for Formidable
     */
    protected function get_field_mappings() {
        return [
            'short' => 10365,  // Field ID for short bio
            'medium' => 10366, // Field ID for medium bio
            'long' => 10367    // Field ID for long bio
        ];
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.7,
            'max_tokens' => 1500
        ];
    }
    
    /**
     * Initialize legacy AJAX actions for backwards compatibility
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions
        add_action('wp_ajax_generate_biography', [$this, 'handle_legacy_biography_generation']);
        add_action('wp_ajax_nopriv_generate_biography', [$this, 'handle_legacy_biography_generation']);
    }
    
    /**
     * Handle legacy biography generation for backwards compatibility
     */
    public function handle_legacy_biography_generation() {
        // UNIFIED NONCE STRATEGY - Use unified nonce for all generators
        if (!check_ajax_referer('mkcg_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Get form data
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : [];
        
        if (empty($form_data)) {
            wp_send_json_error(['message' => 'No form data provided']);
            return;
        }
        
        // Validate
        $validation_result = $this->validate_input($form_data);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($form_data);
        
        // Generate content
        $api_response = $this->api_service->generate_content($prompt, $this->generator_type);
        
        if (!$api_response['success']) {
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        // Return in format expected by biography results page
        wp_send_json_success($formatted_output);
    }
}