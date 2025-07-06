<?php
/**
 * Enhanced Biography Generator - Backend PHP Class
 * 
 * Handles biography generation, AJAX requests, OpenAI integration, and Formidable Forms compatibility.
 * Follows unified generator architecture and service-based approach.
 *
 * @package Media_Kit_Content_Generator
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKCG_Enhanced_Biography_Generator {
    
    /**
     * Version for cache busting
     */
    const VERSION = '1.0';
    
    /**
     * OpenAI model to use for generation
     */
    const OPENAI_MODEL = 'gpt-4';
    
    /**
     * Default biography settings
     */
    private $default_settings = [
        'tone' => 'professional',
        'pov' => 'third',
        'length' => 'medium',
    ];
    
    /**
     * Formidable Forms field mappings
     */
    private $formidable_fields = [
        'short_bio' => 'field_9001',
        'medium_bio' => 'field_9002',
        'long_bio' => 'field_9003',
        'tone' => 'field_9004',
        'pov' => 'field_9005'
    ];
    
    /**
     * Post meta field mappings
     */
    private $post_meta_fields = [
        'short_bio' => '_biography_short',
        'medium_bio' => '_biography_medium',
        'long_bio' => '_biography_long',
        'tone' => '_biography_tone',
        'pov' => '_biography_pov',
        'generation_date' => '_biography_generation_date'
    ];
    
    /**
     * Initialize the generator
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_mkcg_generate_biography', [$this, 'ajax_generate_biography']);
        add_action('wp_ajax_mkcg_modify_biography_tone', [$this, 'ajax_modify_biography_tone']);
        add_action('wp_ajax_mkcg_save_biography_to_formidable', [$this, 'ajax_save_biography_to_formidable']);
        
        // Register scripts and styles - hook into the main plugin
        add_action('mkcg_register_scripts', [$this, 'register_scripts']);
        add_action('mkcg_enqueue_generator_scripts', [$this, 'enqueue_scripts'], 10, 1);
    }
    
    /**
     * Register scripts and styles
     */
    public function register_scripts() {
        // Register biography generator script
        wp_register_script(
            'mkcg-biography-generator',
            plugins_url('assets/js/generators/biography-generator.js', MKCG_PLUGIN_FILE),
            [],
            self::VERSION,
            true
        );
        
        // Register biography results script
        wp_register_script(
            'mkcg-biography-results',
            plugins_url('assets/js/generators/biography-results.js', MKCG_PLUGIN_FILE),
            [],
            self::VERSION,
            true
        );
    }
    
    /**
     * Enqueue scripts for specific generator
     * 
     * @param string $generator_type The generator type
     */
    public function enqueue_scripts($generator_type) {
        if ($generator_type === 'biography') {
            // Check if we're on the results page
            $is_results_page = isset($_GET['results']) && $_GET['results'] === 'true';
            
            if ($is_results_page) {
                wp_enqueue_script('mkcg-biography-results');
            } else {
                wp_enqueue_script('mkcg-biography-generator');
            }
        }
    }
    
    /**
     * Get template data for rendering
     * 
     * @return array Template data
     */
    public function get_template_data() {
        // Get post ID and entry ID from URL
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
        $entry_key = isset($_GET['entry_key']) ? sanitize_text_field($_GET['entry_key']) : '';
        
        // Initialize template data
        $template_data = [
            'post_id' => $post_id,
            'entry_id' => $entry_id,
            'entry_key' => $entry_key,
            'has_data' => false
        ];
        
        // If we have a post ID, try to get biography data
        if ($post_id > 0) {
            $biography_data = $this->get_biography_data($post_id);
            
            if ($biography_data['has_data']) {
                $template_data = array_merge($template_data, $biography_data);
                $template_data['has_data'] = true;
            }
        }
        
        return $template_data;
    }
    
    /**
     * Get results page template data
     * 
     * @return array Results template data
     */
    public function get_results_data() {
        // Get post ID and entry ID from URL
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $entry_id = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
        
        // Initialize results data
        $results_data = [
            'post_id' => $post_id,
            'entry_id' => $entry_id,
            'has_data' => false,
            'biographies' => [
                'short' => '',
                'medium' => '',
                'long' => ''
            ],
            'settings' => $this->default_settings,
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'generation_date' => current_time('mysql')
        ];
        
        // If we have a post ID, try to get biography data
        if ($post_id > 0) {
            $biography_data = $this->get_biography_data($post_id);
            
            if ($biography_data['has_data']) {
                $results_data = array_merge($results_data, $biography_data);
                $results_data['has_data'] = true;
            }
        }
        
        return $results_data;
    }
    
    /**
     * Get biography data from post meta
     * 
     * @param int $post_id Post ID
     * @return array Biography data
     */
    public function get_biography_data($post_id) {
        // Initialize data structure
        $biography_data = [
            'has_data' => false,
            'biographies' => [
                'short' => '',
                'medium' => '',
                'long' => ''
            ],
            'settings' => $this->default_settings,
            'personal_info' => [
                'name' => '',
                'title' => '',
                'organization' => ''
            ],
            'generation_date' => current_time('mysql')
        ];
        
        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            return $biography_data;
        }
        
        // Get biographies from post meta
        $short_bio = get_post_meta($post_id, $this->post_meta_fields['short_bio'], true);
        $medium_bio = get_post_meta($post_id, $this->post_meta_fields['medium_bio'], true);
        $long_bio = get_post_meta($post_id, $this->post_meta_fields['long_bio'], true);
        
        // Get settings from post meta
        $tone = get_post_meta($post_id, $this->post_meta_fields['tone'], true);
        $pov = get_post_meta($post_id, $this->post_meta_fields['pov'], true);
        $generation_date = get_post_meta($post_id, $this->post_meta_fields['generation_date'], true);
        
        // Get personal info
        $name = get_post_meta($post_id, '_guest_name', true);
        if (empty($name)) {
            $name = $post->post_title;
        }
        
        $title = get_post_meta($post_id, '_guest_title', true);
        $organization = get_post_meta($post_id, '_guest_company', true);
        
        // Check if we have data
        $has_data = !empty($short_bio) || !empty($medium_bio) || !empty($long_bio);
        
        if ($has_data) {
            $biography_data['has_data'] = true;
            $biography_data['biographies'] = [
                'short' => $short_bio,
                'medium' => $medium_bio,
                'long' => $long_bio
            ];
            $biography_data['settings'] = [
                'tone' => $tone ?: $this->default_settings['tone'],
                'pov' => $pov ?: $this->default_settings['pov'],
                'length' => $this->default_settings['length'] // Always use default length
            ];
            $biography_data['personal_info'] = [
                'name' => $name,
                'title' => $title,
                'organization' => $organization
            ];
            $biography_data['generation_date'] = $generation_date ?: current_time('mysql');
        } else {
            // If no biography data, still populate personal info
            $biography_data['personal_info'] = [
                'name' => $name,
                'title' => $title,
                'organization' => $organization
            ];
        }
        
        return $biography_data;
    }
    
    /**
     * Generate biography with OpenAI
     * 
     * @param array $data Form data
     * @return array Generated biographies
     */
    private function generate_biography($data) {
        // Initialize results
        $biographies = [
            'short' => '',
            'medium' => '',
            'long' => ''
        ];
        
        // Set up length guidelines
        $length_guidelines = [
            'short' => '50-75 words',
            'medium' => '100-150 words',
            'long' => '200-300 words'
        ];
        
        // Set up tone guidelines
        $tone_guidelines = [
            'professional' => 'formal, authoritative, and business-appropriate',
            'conversational' => 'friendly, approachable, and conversational',
            'authoritative' => 'expert, confident, and authoritative',
            'friendly' => 'warm, approachable, and personable'
        ];
        
        // Set up POV guidelines
        $pov_guidelines = [
            'first' => 'first person (using "I" and "my")',
            'third' => 'third person (using their name and "he", "she", or "they")'
        ];
        
        // Check if we have the required data
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'Name is required to generate a biography.'
            ];
        }
        
        // Prepare the prompt
        $prompt = "You are a professional biography writer tasked with creating compelling professional biographies for {$data['name']}";
        
        if (!empty($data['title'])) {
            $prompt .= ", a {$data['title']}";
        }
        
        if (!empty($data['organization'])) {
            $prompt .= " at {$data['organization']}";
        }
        
        $prompt .= ".\n\n";
        
        $prompt .= "Please create three versions of the biography:\n";
        $prompt .= "1. SHORT BIO: {$length_guidelines['short']}\n";
        $prompt .= "2. MEDIUM BIO: {$length_guidelines['medium']}\n";
        $prompt .= "3. LONG BIO: {$length_guidelines['long']}\n\n";
        
        $prompt .= "Use a {$tone_guidelines[$data['tone']]} tone and write in the {$pov_guidelines[$data['pov']]}.\n\n";
        
        $prompt .= "Use the following information as the foundation for the biography:\n\n";
        
        if (!empty($data['authority_hook'])) {
            $prompt .= "AUTHORITY HOOK (core expertise and value proposition):\n{$data['authority_hook']}\n\n";
        }
        
        if (!empty($data['impact_intro'])) {
            $prompt .= "IMPACT INTRO (credentials and mission):\n{$data['impact_intro']}\n\n";
        }
        
        if (!empty($data['existing_bio'])) {
            $prompt .= "EXISTING BIOGRAPHY (use this as reference but improve it):\n{$data['existing_bio']}\n\n";
        }
        
        if (!empty($data['additional_notes'])) {
            $prompt .= "ADDITIONAL NOTES:\n{$data['additional_notes']}\n\n";
        }
        
        $prompt .= "Follow these additional guidelines:\n";
        $prompt .= "- Focus on their expertise, achievements, and the value they provide\n";
        $prompt .= "- Include specific credentials and quantifiable results when available\n";
        $prompt .= "- Ensure the biography flows naturally and engages the reader\n";
        $prompt .= "- Maintain consistent messaging across all three versions\n";
        $prompt .= "- Each biography should be self-contained and complete\n\n";
        
        $prompt .= "Format your response as follows:\n";
        $prompt .= "SHORT BIO:\n[Short biography here]\n\n";
        $prompt .= "MEDIUM BIO:\n[Medium biography here]\n\n";
        $prompt .= "LONG BIO:\n[Long biography here]\n";
        
        // Check if OpenAI API key is available
        $api_key = get_option('mkcg_openai_api_key');
        if (empty($api_key)) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is not configured. Please set it in the plugin settings.'
            ];
        }
        
        try {
            // Prepare API request
            $headers = [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ];
            
            $request_body = [
                'model' => self::OPENAI_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional biography writer who creates compelling, accurate, and tailored biographies.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];
            
            // Send request to OpenAI API
            $response = wp_remote_post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => $headers,
                    'body' => wp_json_encode($request_body),
                    'timeout' => 60,
                    'data_format' => 'body'
                ]
            );
            
            // Check for errors
            if (is_wp_error($response)) {
                error_log('MKCG Biography: OpenAI API error - ' . $response->get_error_message());
                return [
                    'success' => false,
                    'message' => 'Error connecting to OpenAI API: ' . $response->get_error_message()
                ];
            }
            
            // Parse response
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            // Check for API errors
            if (isset($response_data['error'])) {
                error_log('MKCG Biography: OpenAI API error - ' . $response_data['error']['message']);
                return [
                    'success' => false,
                    'message' => 'OpenAI API error: ' . $response_data['error']['message']
                ];
            }
            
            // Extract content from response
            $content = $response_data['choices'][0]['message']['content'] ?? '';
            
            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'OpenAI API returned empty response.'
                ];
            }
            
            // Parse the biographies from the response
            preg_match('/SHORT BIO:\s*(.*?)(?=\s*MEDIUM BIO:|$)/s', $content, $short_matches);
            preg_match('/MEDIUM BIO:\s*(.*?)(?=\s*LONG BIO:|$)/s', $content, $medium_matches);
            preg_match('/LONG BIO:\s*(.*?)(?=\s*$)/s', $content, $long_matches);
            
            // Clean up the matches
            if (!empty($short_matches[1])) {
                $biographies['short'] = trim($short_matches[1]);
            }
            
            if (!empty($medium_matches[1])) {
                $biographies['medium'] = trim($medium_matches[1]);
            }
            
            if (!empty($long_matches[1])) {
                $biographies['long'] = trim($long_matches[1]);
            }
            
            // Check if we have at least one biography
            if (empty($biographies['short']) && empty($biographies['medium']) && empty($biographies['long'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to parse biographies from OpenAI response.'
                ];
            }
            
            // Save biographies to post meta if we have a post ID
            if (!empty($data['post_id'])) {
                $this->save_biographies_to_post_meta($data['post_id'], $biographies, [
                    'tone' => $data['tone'],
                    'pov' => $data['pov']
                ]);
            }
            
            return [
                'success' => true,
                'biographies' => $biographies,
                'settings' => [
                    'tone' => $data['tone'],
                    'pov' => $data['pov']
                ],
                'personal_info' => [
                    'name' => $data['name'],
                    'title' => $data['title'],
                    'organization' => $data['organization']
                ],
                'generation_date' => current_time('mysql')
            ];
        } catch (Exception $e) {
            error_log('MKCG Biography: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating biography: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Modify biography tone
     * 
     * @param array $data Form data
     * @return array Modified biographies
     */
    private function modify_biography_tone($data) {
        // Check if we have post ID
        if (empty($data['post_id'])) {
            return [
                'success' => false,
                'message' => 'Post ID is required to modify tone.'
            ];
        }
        
        // Get existing biographies
        $biography_data = $this->get_biography_data($data['post_id']);
        
        // Check if we have biographies to modify
        if (!$biography_data['has_data']) {
            return [
                'success' => false,
                'message' => 'No biographies found to modify.'
            ];
        }
        
        // Set up tone guidelines
        $tone_guidelines = [
            'professional' => 'formal, authoritative, and business-appropriate',
            'conversational' => 'friendly, approachable, and conversational',
            'authoritative' => 'expert, confident, and authoritative',
            'friendly' => 'warm, approachable, and personable'
        ];
        
        // Get current tone
        $current_tone = $biography_data['settings']['tone'];
        
        // Check if requested tone is the same as current tone
        if ($current_tone === $data['tone']) {
            return [
                'success' => true,
                'biographies' => $biography_data['biographies'],
                'settings' => [
                    'tone' => $current_tone,
                    'pov' => $biography_data['settings']['pov']
                ],
                'message' => 'Tone is already set to ' . $data['tone'] . '.'
            ];
        }
        
        // Prepare the prompt for tone modification
        $prompt = "You are a professional biography editor. I have three versions of a professional biography for {$biography_data['personal_info']['name']}";
        
        if (!empty($biography_data['personal_info']['title'])) {
            $prompt .= ", a {$biography_data['personal_info']['title']}";
        }
        
        if (!empty($biography_data['personal_info']['organization'])) {
            $prompt .= " at {$biography_data['personal_info']['organization']}";
        }
        
        $prompt .= ".\n\n";
        
        $prompt .= "The current tone is {$tone_guidelines[$current_tone]}. I need you to rewrite these biographies with a {$tone_guidelines[$data['tone']]} tone instead.\n\n";
        
        $prompt .= "Please maintain the same content, length, and point of view, but change only the tone to be {$tone_guidelines[$data['tone']]}.\n\n";
        
        // Add biographies to prompt
        $prompt .= "SHORT BIO:\n{$biography_data['biographies']['short']}\n\n";
        $prompt .= "MEDIUM BIO:\n{$biography_data['biographies']['medium']}\n\n";
        $prompt .= "LONG BIO:\n{$biography_data['biographies']['long']}\n\n";
        
        $prompt .= "Format your response as follows:\n";
        $prompt .= "SHORT BIO:\n[Modified short biography here]\n\n";
        $prompt .= "MEDIUM BIO:\n[Modified medium biography here]\n\n";
        $prompt .= "LONG BIO:\n[Modified long biography here]\n";
        
        // Check if OpenAI API key is available
        $api_key = get_option('mkcg_openai_api_key');
        if (empty($api_key)) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is not configured. Please set it in the plugin settings.'
            ];
        }
        
        try {
            // Prepare API request
            $headers = [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ];
            
            $request_body = [
                'model' => self::OPENAI_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional biography editor who specializes in tone adjustments.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ];
            
            // Send request to OpenAI API
            $response = wp_remote_post(
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => $headers,
                    'body' => wp_json_encode($request_body),
                    'timeout' => 60,
                    'data_format' => 'body'
                ]
            );
            
            // Check for errors
            if (is_wp_error($response)) {
                error_log('MKCG Biography: OpenAI API error - ' . $response->get_error_message());
                return [
                    'success' => false,
                    'message' => 'Error connecting to OpenAI API: ' . $response->get_error_message()
                ];
            }
            
            // Parse response
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            // Check for API errors
            if (isset($response_data['error'])) {
                error_log('MKCG Biography: OpenAI API error - ' . $response_data['error']['message']);
                return [
                    'success' => false,
                    'message' => 'OpenAI API error: ' . $response_data['error']['message']
                ];
            }
            
            // Extract content from response
            $content = $response_data['choices'][0]['message']['content'] ?? '';
            
            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'OpenAI API returned empty response.'
                ];
            }
            
            // Parse the biographies from the response
            preg_match('/SHORT BIO:\s*(.*?)(?=\s*MEDIUM BIO:|$)/s', $content, $short_matches);
            preg_match('/MEDIUM BIO:\s*(.*?)(?=\s*LONG BIO:|$)/s', $content, $medium_matches);
            preg_match('/LONG BIO:\s*(.*?)(?=\s*$)/s', $content, $long_matches);
            
            // Initialize modified biographies with existing ones
            $modified_biographies = $biography_data['biographies'];
            
            // Update with modified biographies if available
            if (!empty($short_matches[1])) {
                $modified_biographies['short'] = trim($short_matches[1]);
            }
            
            if (!empty($medium_matches[1])) {
                $modified_biographies['medium'] = trim($medium_matches[1]);
            }
            
            if (!empty($long_matches[1])) {
                $modified_biographies['long'] = trim($long_matches[1]);
            }
            
            // Save modified biographies to post meta
            $this->save_biographies_to_post_meta($data['post_id'], $modified_biographies, [
                'tone' => $data['tone'],
                'pov' => $biography_data['settings']['pov']
            ]);
            
            return [
                'success' => true,
                'biographies' => $modified_biographies,
                'settings' => [
                    'tone' => $data['tone'],
                    'pov' => $biography_data['settings']['pov']
                ],
                'message' => 'Biography tone updated successfully.'
            ];
        } catch (Exception $e) {
            error_log('MKCG Biography: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error modifying biography tone: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Save biographies to post meta
     * 
     * @param int $post_id Post ID
     * @param array $biographies Biographies to save
     * @param array $settings Settings to save
     * @return bool Success status
     */
    private function save_biographies_to_post_meta($post_id, $biographies, $settings) {
        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        // Save biographies
        update_post_meta($post_id, $this->post_meta_fields['short_bio'], $biographies['short']);
        update_post_meta($post_id, $this->post_meta_fields['medium_bio'], $biographies['medium']);
        update_post_meta($post_id, $this->post_meta_fields['long_bio'], $biographies['long']);
        
        // Save settings
        update_post_meta($post_id, $this->post_meta_fields['tone'], $settings['tone']);
        update_post_meta($post_id, $this->post_meta_fields['pov'], $settings['pov']);
        update_post_meta($post_id, $this->post_meta_fields['generation_date'], current_time('mysql'));
        
        return true;
    }
    
    /**
     * Save biographies to Formidable Forms entry
     * 
     * @param int $entry_id Entry ID
     * @param array $biographies Biographies to save
     * @param array $settings Settings to save
     * @return bool Success status
     */
    private function save_biographies_to_formidable($entry_id, $biographies, $settings) {
        // Check if Formidable Forms is active
        if (!class_exists('FrmEntry')) {
            return false;
        }
        
        // Check if entry exists
        $entry = FrmEntry::getOne($entry_id);
        if (!$entry) {
            return false;
        }
        
        // Prepare field values
        $field_values = [
            $this->formidable_fields['short_bio'] => $biographies['short'],
            $this->formidable_fields['medium_bio'] => $biographies['medium'],
            $this->formidable_fields['long_bio'] => $biographies['long'],
            $this->formidable_fields['tone'] => $settings['tone'],
            $this->formidable_fields['pov'] => $settings['pov']
        ];
        
        // Update entry
        $result = FrmEntry::update($entry_id, $field_values);
        
        return $result !== false;
    }
    
    /**
     * AJAX handler for generating biography
     */
    public function ajax_generate_biography() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Collect form data
        $data = [
            'post_id' => isset($_POST['post_id']) ? intval($_POST['post_id']) : 0,
            'entry_id' => isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0,
            'authority_hook' => isset($_POST['authority_hook']) ? sanitize_textarea_field($_POST['authority_hook']) : '',
            'impact_intro' => isset($_POST['impact_intro']) ? sanitize_textarea_field($_POST['impact_intro']) : '',
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'organization' => isset($_POST['organization']) ? sanitize_text_field($_POST['organization']) : '',
            'tone' => isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : $this->default_settings['tone'],
            'pov' => isset($_POST['pov']) ? sanitize_text_field($_POST['pov']) : $this->default_settings['pov'],
            'length' => isset($_POST['length']) ? sanitize_text_field($_POST['length']) : $this->default_settings['length'],
            'existing_bio' => isset($_POST['existing_bio']) ? sanitize_textarea_field($_POST['existing_bio']) : '',
            'additional_notes' => isset($_POST['additional_notes']) ? sanitize_textarea_field($_POST['additional_notes']) : ''
        ];
        
        // Generate biography
        $result = $this->generate_biography($data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX handler for modifying biography tone
     */
    public function ajax_modify_biography_tone() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Collect form data
        $data = [
            'post_id' => isset($_POST['post_id']) ? intval($_POST['post_id']) : 0,
            'tone' => isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : $this->default_settings['tone']
        ];
        
        // Modify biography tone
        $result = $this->modify_biography_tone($data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX handler for saving biography to Formidable Forms
     */
    public function ajax_save_biography_to_formidable() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            wp_send_json_error(['message' => 'Security check failed.']);
            return;
        }
        
        // Check if we have entry ID
        if (!isset($_POST['entry_id']) || intval($_POST['entry_id']) <= 0) {
            wp_send_json_error(['message' => 'Entry ID is required.']);
            return;
        }
        
        // Check if we have post ID
        if (!isset($_POST['post_id']) || intval($_POST['post_id']) <= 0) {
            wp_send_json_error(['message' => 'Post ID is required.']);
            return;
        }
        
        // Get post ID and entry ID
        $post_id = intval($_POST['post_id']);
        $entry_id = intval($_POST['entry_id']);
        
        // Get biography data from post meta
        $biography_data = $this->get_biography_data($post_id);
        
        // Check if we have biographies to save
        if (!$biography_data['has_data']) {
            wp_send_json_error(['message' => 'No biographies found to save.']);
            return;
        }
        
        // Save biographies to Formidable Forms
        $result = $this->save_biographies_to_formidable($entry_id, $biography_data['biographies'], $biography_data['settings']);
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Biographies saved to Formidable Forms successfully.',
                'entry_id' => $entry_id
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to save biographies to Formidable Forms.',
                'entry_id' => $entry_id
            ]);
        }
    }
}

// Initialize the generator
new MKCG_Enhanced_Biography_Generator();