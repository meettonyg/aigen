<?php
/**
 * Enhanced Guest Intro Generator
 *
 * @package MediaKitContentGenerator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MKCG_Enhanced_Guest_Intro_Generator
 *
 * Handles the generation and management of guest introductions for podcasts and events.
 */
class MKCG_Enhanced_Guest_Intro_Generator {

    /**
     * Instance of this class.
     *
     * @var MKCG_Enhanced_Guest_Intro_Generator
     */
    private static $instance;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->register_ajax_actions();
    }

    /**
     * Get instance of this class.
     *
     * @return MKCG_Enhanced_Guest_Intro_Generator
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register AJAX actions.
     */
    private function register_ajax_actions() {
        add_action( 'wp_ajax_generate_guest_intro', array( $this, 'ajax_generate_guest_intro' ) );
        add_action( 'wp_ajax_save_guest_intro_data', array( $this, 'ajax_save_guest_intro_data' ) );
        add_action( 'wp_ajax_save_guest_intro_results', array( $this, 'ajax_save_guest_intro_results' ) );
    }

    /**
     * AJAX handler for generating guest introductions.
     */
    public function ajax_generate_guest_intro() {
        // Check nonce.
        if ( ! check_ajax_referer( 'mkcg_guest_intro_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
        }

        // Get and sanitize form data.
        $form_data = $this->get_sanitized_form_data();
        if ( is_wp_error( $form_data ) ) {
            wp_send_json_error( array( 'message' => $form_data->get_error_message() ) );
        }

        // Generate the guest introductions.
        $results = $this->generate_guest_intros( $form_data );
        if ( is_wp_error( $results ) ) {
            wp_send_json_error( array( 'message' => $results->get_error_message() ) );
        }

        wp_send_json_success( array(
            'short' => $results['short'],
            'medium' => $results['medium'],
            'long' => $results['long'],
            'message' => 'Guest introductions generated successfully.',
        ) );
    }

    /**
     * AJAX handler for saving guest intro data.
     */
    public function ajax_save_guest_intro_data() {
        // Check nonce.
        if ( ! check_ajax_referer( 'mkcg_guest_intro_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
        }

        // Get and sanitize form data.
        $form_data = $this->get_sanitized_form_data();
        if ( is_wp_error( $form_data ) ) {
            wp_send_json_error( array( 'message' => $form_data->get_error_message() ) );
        }

        // Get post ID.
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Invalid post ID.' ) );
        }

        // Save data to post meta.
        $result = $this->save_guest_intro_data_to_post_meta( $post_id, $form_data );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'Guest intro data saved successfully.',
        ) );
    }

    /**
     * AJAX handler for saving guest intro results.
     */
    public function ajax_save_guest_intro_results() {
        // Check nonce.
        if ( ! check_ajax_referer( 'mkcg_guest_intro_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ) );
        }

        // Check user capabilities.
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
        }

        // Get and sanitize results data.
        $results_data = $this->get_sanitized_results_data();
        if ( is_wp_error( $results_data ) ) {
            wp_send_json_error( array( 'message' => $results_data->get_error_message() ) );
        }

        // Get post ID.
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Invalid post ID.' ) );
        }

        // Save results to post meta.
        $result = $this->save_guest_intro_results_to_post_meta( $post_id, $results_data );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'Guest intro results saved successfully.',
        ) );
    }

    /**
     * Get and sanitize form data from POST request.
     *
     * @return array|WP_Error Sanitized form data or error.
     */
    private function get_sanitized_form_data() {
        if ( ! isset( $_POST['form_data'] ) ) {
            return new WP_Error( 'missing_data', 'Form data is missing.' );
        }

        $form_data = json_decode( stripslashes( $_POST['form_data'] ), true );
        if ( ! $form_data || ! is_array( $form_data ) ) {
            return new WP_Error( 'invalid_data', 'Invalid form data format.' );
        }

        // Sanitize each field.
        $sanitized_data = array();

        // Guest information.
        $sanitized_data['guest_name'] = isset( $form_data['guest_name'] ) ? sanitize_text_field( $form_data['guest_name'] ) : '';
        $sanitized_data['guest_title'] = isset( $form_data['guest_title'] ) ? sanitize_text_field( $form_data['guest_title'] ) : '';
        $sanitized_data['guest_company'] = isset( $form_data['guest_company'] ) ? sanitize_text_field( $form_data['guest_company'] ) : '';

        // Episode information.
        $sanitized_data['episode_title'] = isset( $form_data['episode_title'] ) ? sanitize_text_field( $form_data['episode_title'] ) : '';
        $sanitized_data['episode_topic'] = isset( $form_data['episode_topic'] ) ? sanitize_text_field( $form_data['episode_topic'] ) : '';
        
        // Introduction settings.
        $sanitized_data['intro_tone'] = isset( $form_data['intro_tone'] ) ? sanitize_text_field( $form_data['intro_tone'] ) : 'professional';
        $sanitized_data['intro_hook_style'] = isset( $form_data['intro_hook_style'] ) ? sanitize_text_field( $form_data['intro_hook_style'] ) : 'question';
        $sanitized_data['custom_notes'] = isset( $form_data['custom_notes'] ) ? sanitize_textarea_field( $form_data['custom_notes'] ) : '';

        // Authority Hook and Impact Intro data.
        $sanitized_data['authority_hook'] = isset( $form_data['authority_hook'] ) ? $this->sanitize_authority_hook_data( $form_data['authority_hook'] ) : array();
        $sanitized_data['impact_intro'] = isset( $form_data['impact_intro'] ) ? $this->sanitize_impact_intro_data( $form_data['impact_intro'] ) : array();

        // Validate required fields.
        if ( empty( $sanitized_data['guest_name'] ) ) {
            return new WP_Error( 'required_field', 'Guest name is required.' );
        }

        return $sanitized_data;
    }

    /**
     * Sanitize Authority Hook data.
     *
     * @param array $authority_hook Authority Hook data.
     * @return array Sanitized Authority Hook data.
     */
    private function sanitize_authority_hook_data( $authority_hook ) {
        $sanitized = array();
        
        if ( ! is_array( $authority_hook ) ) {
            return $sanitized;
        }
        
        $sanitized['who'] = isset( $authority_hook['who'] ) ? sanitize_text_field( $authority_hook['who'] ) : '';
        $sanitized['what'] = isset( $authority_hook['what'] ) ? sanitize_text_field( $authority_hook['what'] ) : '';
        $sanitized['when'] = isset( $authority_hook['when'] ) ? sanitize_text_field( $authority_hook['when'] ) : '';
        $sanitized['how'] = isset( $authority_hook['how'] ) ? sanitize_text_field( $authority_hook['how'] ) : '';
        
        return $sanitized;
    }

    /**
     * Sanitize Impact Intro data.
     *
     * @param array $impact_intro Impact Intro data.
     * @return array Sanitized Impact Intro data.
     */
    private function sanitize_impact_intro_data( $impact_intro ) {
        $sanitized = array();
        
        if ( ! is_array( $impact_intro ) ) {
            return $sanitized;
        }
        
        $sanitized['where'] = isset( $impact_intro['where'] ) ? sanitize_text_field( $impact_intro['where'] ) : '';
        $sanitized['why'] = isset( $impact_intro['why'] ) ? sanitize_text_field( $impact_intro['why'] ) : '';
        
        return $sanitized;
    }

    /**
     * Get and sanitize results data from POST request.
     *
     * @return array|WP_Error Sanitized results data or error.
     */
    private function get_sanitized_results_data() {
        if ( ! isset( $_POST['results_data'] ) ) {
            return new WP_Error( 'missing_data', 'Results data is missing.' );
        }

        $results_data = json_decode( stripslashes( $_POST['results_data'] ), true );
        if ( ! $results_data || ! is_array( $results_data ) ) {
            return new WP_Error( 'invalid_data', 'Invalid results data format.' );
        }

        // Sanitize each intro.
        $sanitized_data = array();
        $sanitized_data['short'] = isset( $results_data['short'] ) ? wp_kses_post( $results_data['short'] ) : '';
        $sanitized_data['medium'] = isset( $results_data['medium'] ) ? wp_kses_post( $results_data['medium'] ) : '';
        $sanitized_data['long'] = isset( $results_data['long'] ) ? wp_kses_post( $results_data['long'] ) : '';
        $sanitized_data['settings'] = isset( $results_data['settings'] ) ? $this->sanitize_settings( $results_data['settings'] ) : array();

        return $sanitized_data;
    }

    /**
     * Sanitize settings data.
     *
     * @param array $settings Settings data.
     * @return array Sanitized settings data.
     */
    private function sanitize_settings( $settings ) {
        $sanitized = array();
        
        if ( ! is_array( $settings ) ) {
            return $sanitized;
        }
        
        $sanitized['intro_tone'] = isset( $settings['intro_tone'] ) ? sanitize_text_field( $settings['intro_tone'] ) : 'professional';
        $sanitized['intro_hook_style'] = isset( $settings['intro_hook_style'] ) ? sanitize_text_field( $settings['intro_hook_style'] ) : 'question';
        
        return $sanitized;
    }

    /**
     * Save guest intro data to post meta.
     *
     * @param int   $post_id   Post ID.
     * @param array $form_data Form data.
     * @return bool|WP_Error True on success or error.
     */
    private function save_guest_intro_data_to_post_meta( $post_id, $form_data ) {
        if ( ! $post_id || ! is_array( $form_data ) ) {
            return new WP_Error( 'invalid_data', 'Invalid data for saving to post meta.' );
        }

        // Check if post exists.
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', 'Post does not exist.' );
        }

        // Save guest info data.
        update_post_meta( $post_id, '_mkcg_guest_intro_name', $form_data['guest_name'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_title', $form_data['guest_title'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_company', $form_data['guest_company'] );

        // Save episode info.
        update_post_meta( $post_id, '_mkcg_guest_intro_episode_title', $form_data['episode_title'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_episode_topic', $form_data['episode_topic'] );

        // Save intro settings.
        update_post_meta( $post_id, '_mkcg_guest_intro_tone', $form_data['intro_tone'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_hook_style', $form_data['intro_hook_style'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_custom_notes', $form_data['custom_notes'] );

        // Save authority hook data.
        if ( ! empty( $form_data['authority_hook'] ) ) {
            update_post_meta( $post_id, '_mkcg_guest_intro_authority_hook', $form_data['authority_hook'] );
        }

        // Save impact intro data.
        if ( ! empty( $form_data['impact_intro'] ) ) {
            update_post_meta( $post_id, '_mkcg_guest_intro_impact_intro', $form_data['impact_intro'] );
        }

        return true;
    }

    /**
     * Save guest intro results to post meta.
     *
     * @param int   $post_id      Post ID.
     * @param array $results_data Results data.
     * @return bool|WP_Error True on success or error.
     */
    private function save_guest_intro_results_to_post_meta( $post_id, $results_data ) {
        if ( ! $post_id || ! is_array( $results_data ) ) {
            return new WP_Error( 'invalid_data', 'Invalid data for saving to post meta.' );
        }

        // Check if post exists.
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', 'Post does not exist.' );
        }

        // Save all intros.
        update_post_meta( $post_id, '_mkcg_guest_intro_short', $results_data['short'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_medium', $results_data['medium'] );
        update_post_meta( $post_id, '_mkcg_guest_intro_long', $results_data['long'] );

        // Save settings used for generation.
        if ( ! empty( $results_data['settings'] ) ) {
            update_post_meta( $post_id, '_mkcg_guest_intro_generation_settings', $results_data['settings'] );
        }

        // Save generation timestamp.
        update_post_meta( $post_id, '_mkcg_guest_intro_generated_at', current_time( 'mysql' ) );

        return true;
    }

    /**
     * Generate guest introductions using OpenAI API.
     *
     * @param array $form_data Form data.
     * @return array|WP_Error Generated intros or error.
     */
    private function generate_guest_intros( $form_data ) {
        if ( ! is_array( $form_data ) ) {
            return new WP_Error( 'invalid_data', 'Invalid form data for generation.' );
        }

        // Log the generation attempt.
        $this->log_generation_attempt( $form_data );

        // Check if OpenAI API key is set.
        $api_key = $this->get_openai_api_key();
        if ( is_wp_error( $api_key ) ) {
            return $api_key;
        }

        // Prepare the prompt for short introduction.
        $short_prompt = $this->prepare_intro_prompt( $form_data, 'short' );
        
        // Prepare the prompt for medium introduction.
        $medium_prompt = $this->prepare_intro_prompt( $form_data, 'medium' );
        
        // Prepare the prompt for long introduction.
        $long_prompt = $this->prepare_intro_prompt( $form_data, 'long' );

        // Generate intros using OpenAI API.
        $short_intro = $this->generate_intro_with_openai( $short_prompt, $api_key );
        if ( is_wp_error( $short_intro ) ) {
            return $short_intro;
        }

        $medium_intro = $this->generate_intro_with_openai( $medium_prompt, $api_key );
        if ( is_wp_error( $medium_intro ) ) {
            return $medium_intro;
        }

        $long_intro = $this->generate_intro_with_openai( $long_prompt, $api_key );
        if ( is_wp_error( $long_intro ) ) {
            return $long_intro;
        }

        // Format and clean the generated intros.
        $short_intro = $this->format_intro_content( $short_intro );
        $medium_intro = $this->format_intro_content( $medium_intro );
        $long_intro = $this->format_intro_content( $long_intro );

        return array(
            'short' => $short_intro,
            'medium' => $medium_intro,
            'long' => $long_intro,
        );
    }

    /**
     * Get OpenAI API key.
     *
     * @return string|WP_Error API key or error.
     */
    private function get_openai_api_key() {
        $api_key = get_option( 'mkcg_openai_api_key' );
        if ( ! $api_key ) {
            return new WP_Error( 'missing_api_key', 'OpenAI API key is not set. Please configure it in the settings.' );
        }
        return $api_key;
    }

    /**
     * Prepare introduction prompt based on form data.
     *
     * @param array  $form_data Form data.
     * @param string $length    Length of intro ('short', 'medium', 'long').
     * @return string Prepared prompt.
     */
    private function prepare_intro_prompt( $form_data, $length = 'medium' ) {
        // Set length guidelines based on type.
        $length_description = '';
        $word_count = '';
        $reading_time = '';

        switch ( $length ) {
            case 'short':
                $length_description = 'brief, concise';
                $word_count = '40-60 words';
                $reading_time = '30-45 seconds';
                break;
            case 'long':
                $length_description = 'comprehensive, detailed';
                $word_count = '200-300 words';
                $reading_time = '2-3 minutes';
                break;
            case 'medium':
            default:
                $length_description = 'moderate length';
                $word_count = '100-150 words';
                $reading_time = '60-90 seconds';
                break;
        }

        // Get guest information.
        $guest_name = ! empty( $form_data['guest_name'] ) ? $form_data['guest_name'] : '';
        $guest_title = ! empty( $form_data['guest_title'] ) ? $form_data['guest_title'] : '';
        $guest_company = ! empty( $form_data['guest_company'] ) ? $form_data['guest_company'] : '';

        // Get episode information.
        $episode_title = ! empty( $form_data['episode_title'] ) ? $form_data['episode_title'] : '';
        $episode_topic = ! empty( $form_data['episode_topic'] ) ? $form_data['episode_topic'] : '';

        // Get introduction settings.
        $intro_tone = ! empty( $form_data['intro_tone'] ) ? $form_data['intro_tone'] : 'professional';
        $intro_hook_style = ! empty( $form_data['intro_hook_style'] ) ? $form_data['intro_hook_style'] : 'question';
        $custom_notes = ! empty( $form_data['custom_notes'] ) ? $form_data['custom_notes'] : '';

        // Get authority hook and impact intro data.
        $authority_hook = ! empty( $form_data['authority_hook'] ) ? $form_data['authority_hook'] : array();
        $impact_intro = ! empty( $form_data['impact_intro'] ) ? $form_data['impact_intro'] : array();

        // Build the prompt.
        $prompt = "You are a professional podcast or event host assistant. Your task is to create a {$length_description} guest introduction that will be read aloud to introduce the guest to the audience. The introduction should be approximately {$word_count} (reading time: {$reading_time}).";
        
        $prompt .= "\n\nGUEST INFORMATION:";
        $prompt .= "\nName: {$guest_name}";
        if ( ! empty( $guest_title ) ) {
            $prompt .= "\nTitle/Role: {$guest_title}";
        }
        if ( ! empty( $guest_company ) ) {
            $prompt .= "\nCompany/Organization: {$guest_company}";
        }

        $prompt .= "\n\nEPISODE/EVENT INFORMATION:";
        if ( ! empty( $episode_title ) ) {
            $prompt .= "\nTitle: {$episode_title}";
        }
        if ( ! empty( $episode_topic ) ) {
            $prompt .= "\nTopic: {$episode_topic}";
        }

        // Add Authority Hook data if available.
        if ( ! empty( $authority_hook ) ) {
            $prompt .= "\n\nAUTHORITY HOOK COMPONENTS:";
            if ( ! empty( $authority_hook['who'] ) ) {
                $prompt .= "\nWHO they help: {$authority_hook['who']}";
            }
            if ( ! empty( $authority_hook['what'] ) ) {
                $prompt .= "\nWHAT results they deliver: {$authority_hook['what']}";
            }
            if ( ! empty( $authority_hook['when'] ) ) {
                $prompt .= "\nWHEN they help (situation/problem): {$authority_hook['when']}";
            }
            if ( ! empty( $authority_hook['how'] ) ) {
                $prompt .= "\nHOW they help (method/approach): {$authority_hook['how']}";
            }
        }

        // Add Impact Intro data if available.
        if ( ! empty( $impact_intro ) ) {
            $prompt .= "\n\nIMPACT INTRO COMPONENTS:";
            if ( ! empty( $impact_intro['where'] ) ) {
                $prompt .= "\nWHERE they've demonstrated expertise (credentials): {$impact_intro['where']}";
            }
            if ( ! empty( $impact_intro['why'] ) ) {
                $prompt .= "\nWHY they do what they do (mission): {$impact_intro['why']}";
            }
        }

        // Add introduction style guidance.
        $prompt .= "\n\nINTRODUCTION STYLE:";
        $prompt .= "\nTone: {$intro_tone}";
        $prompt .= "\nHook Style: {$intro_hook_style}";

        // Add specific guidance based on hook style.
        switch ( $intro_hook_style ) {
            case 'question':
                $prompt .= "\nStart with an engaging question that relates to the guest's expertise or the episode topic.";
                break;
            case 'statistic':
                $prompt .= "\nStart with a compelling statistic or data point related to the guest's field or the episode topic.";
                break;
            case 'problem':
                $prompt .= "\nStart by highlighting a common problem or challenge that the guest helps solve.";
                break;
            case 'story':
                $prompt .= "\nStart with a brief, engaging story or anecdote about the guest or their work.";
                break;
            case 'direct':
                $prompt .= "\nStart with a direct, powerful statement about the guest's expertise or impact.";
                break;
        }

        // Add tone guidance.
        switch ( $intro_tone ) {
            case 'professional':
                $prompt .= "\nUse formal language and focus on credentials and professional achievements.";
                break;
            case 'conversational':
                $prompt .= "\nUse a friendly, approachable tone as if speaking to a friend, while maintaining professionalism.";
                break;
            case 'enthusiastic':
                $prompt .= "\nUse dynamic, energetic language that conveys excitement about the guest and their work.";
                break;
            case 'authoritative':
                $prompt .= "\nUse confident, expert-level language that establishes the guest as a true authority.";
                break;
            case 'warm':
                $prompt .= "\nUse compassionate, welcoming language that creates a connection with the audience.";
                break;
        }

        // Add custom notes if available.
        if ( ! empty( $custom_notes ) ) {
            $prompt .= "\n\nADDITIONAL NOTES/REQUESTS:";
            $prompt .= "\n{$custom_notes}";
        }

        // Final instructions.
        $prompt .= "\n\nFINAL GUIDELINES:";
        $prompt .= "\n1. The introduction should be designed to be read aloud by a host, so use natural speech patterns.";
        $prompt .= "\n2. Make it engaging, attention-grabbing, and set up why the audience should be excited to hear from this guest.";
        $prompt .= "\n3. End with a warm welcome that transitions to the guest, such as 'Please join me in welcoming [Guest Name]!'";
        $prompt .= "\n4. Stay within the {$word_count} target (reading time: {$reading_time}).";
        $prompt .= "\n5. Focus on what makes this guest unique and valuable to the audience.";

        $prompt .= "\n\nNow, create a {$length_description} introduction for {$guest_name} that will engage the audience and highlight their expertise and value.";

        return $prompt;
    }

    /**
     * Generate introduction with OpenAI API.
     *
     * @param string $prompt  Prompt for generation.
     * @param string $api_key OpenAI API key.
     * @return string|WP_Error Generated intro or error.
     */
    private function generate_intro_with_openai( $prompt, $api_key ) {
        // Prepare API request.
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        );
        
        $body = array(
            'model'       => 'gpt-4', // Use GPT-4 for best quality.
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => 'You are a professional podcast or event host assistant that creates engaging, accurate guest introductions.',
                ),
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
            'temperature' => 0.7,
            'max_tokens'  => 1000,
        );
        
        $args = array(
            'headers'     => $headers,
            'body'        => json_encode( $body ),
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => 60,
        );
        
        // Make API request.
        $response = wp_remote_post( $url, $args );
        
        // Check for errors.
        if ( is_wp_error( $response ) ) {
            $this->log_error( 'OpenAI API request failed: ' . $response->get_error_message() );
            return new WP_Error( 'api_error', 'Failed to connect to OpenAI API: ' . $response->get_error_message() );
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $response_code ) {
            $body = wp_remote_retrieve_body( $response );
            $this->log_error( 'OpenAI API error: ' . $response_code . ' - ' . $body );
            return new WP_Error( 'api_error', 'OpenAI API error: ' . $response_code );
        }
        
        // Parse response.
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! isset( $body['choices'][0]['message']['content'] ) ) {
            $this->log_error( 'Invalid response from OpenAI API: ' . wp_json_encode( $body ) );
            return new WP_Error( 'api_error', 'Invalid response from OpenAI API.' );
        }
        
        // Extract and return generated text.
        $generated_text = $body['choices'][0]['message']['content'];
        
        return $generated_text;
    }

    /**
     * Format introduction content.
     *
     * @param string $content Raw generated content.
     * @return string Formatted content.
     */
    private function format_intro_content( $content ) {
        // Remove any quotation marks that might be around the content.
        $content = trim( $content, " \t\n\r\0\x0B\"'" );
        
        // Replace multiple newlines with double newlines.
        $content = preg_replace( '/(\r?\n){2,}/', "\n\n", $content );
        
        // Ensure the content ends with appropriate punctuation.
        $last_char = substr( $content, -1 );
        if ( ! in_array( $last_char, array( '.', '!', '?' ), true ) ) {
            $content .= '.';
        }
        
        return $content;
    }

    /**
     * Log generation attempt.
     *
     * @param array $form_data Form data used for generation.
     */
    private function log_generation_attempt( $form_data ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        // Create log entry.
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'user_id'   => get_current_user_id(),
            'form_data' => $form_data,
        );

        // Get existing log.
        $log = get_option( 'mkcg_guest_intro_generation_log', array() );
        
        // Add new entry.
        $log[] = $log_entry;
        
        // Limit log size.
        if ( count( $log ) > 100 ) {
            $log = array_slice( $log, -100 );
        }
        
        // Update log.
        update_option( 'mkcg_guest_intro_generation_log', $log );
    }

    /**
     * Log error message.
     *
     * @param string $message Error message.
     */
    private function log_error( $message ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        // Create log entry.
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'message'   => $message,
            'user_id'   => get_current_user_id(),
        );

        // Get existing log.
        $log = get_option( 'mkcg_guest_intro_error_log', array() );
        
        // Add new entry.
        $log[] = $log_entry;
        
        // Limit log size.
        if ( count( $log ) > 100 ) {
            $log = array_slice( $log, -100 );
        }
        
        // Update log.
        update_option( 'mkcg_guest_intro_error_log', $log );
    }
}

// Initialize the class.
MKCG_Enhanced_Guest_Intro_Generator::get_instance();
