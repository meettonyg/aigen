<?php
/**
 * MKCG Questions Generator - Enhanced Unified Implementation
 * Generates interview questions based on selected topics with enhanced UI and Formidable integration
 */

class MKCG_Questions_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'questions';
    
    /**
     * Get form fields configuration
     */
    public function get_form_fields() {
        return [
            'topic' => [
                'type' => 'textarea',
                'label' => 'Podcast Topic',
                'required' => true,
                'description' => 'The topic you want to generate questions for'
            ],
            'topic_number' => [
                'type' => 'number',
                'label' => 'Topic Number',
                'required' => false,
                'description' => 'The number of this topic in a series (1-5)'
            ],
            'entry_id' => [
                'type' => 'hidden',
                'required' => false
            ],
            'entry_key' => [
                'type' => 'hidden',
                'required' => false
            ]
        ];
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data) {
        $errors = [];
        
        if (empty($data['topic'])) {
            $errors[] = 'Topic is required';
        }
        
        if (!empty($data['topic']) && strlen($data['topic']) < 5) {
            $errors[] = 'Topic must be at least 5 characters';
        }
        
        if (!empty($data['topic_number'])) {
            $topic_number = intval($data['topic_number']);
            if ($topic_number < 1 || $topic_number > 5) {
                $errors[] = 'Topic number must be between 1 and 5';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for question generation
     */
    public function build_prompt($data) {
        $topic = $data['topic'];
        $topic_number = isset($data['topic_number']) ? intval($data['topic_number']) : 1;
        
        $prompt = "You are an expert in generating highly engaging and insightful podcast interview questions. Your task is to generate **10 compelling interview questions** based on the provided **podcast topic**.

### **Podcast Topic:** \"$topic\"

### **Guidelines for Crafting Questions:**
- Each question must be **highly relevant to the topic**.
- Questions should be **open-ended** to encourage meaningful discussion.
- Ensure a **mix of question types** to balance storytelling, strategy, and implementation.
- Questions should help the guest showcase their expertise while providing value to listeners.

### **Question Categories & Examples:**

**1️⃣ Origin Questions** (The \"why\" behind the topic)
- \"What led you to develop this approach to [topic area]?\"
- \"How did you first realize the impact of [topic concept]?\"

**2️⃣ Process Questions** (Step-by-step guidance)
- \"Can you walk us through your method for [topic implementation]?\"
- \"What does your process look like from start to finish?\"

**3️⃣ Result Questions** (Proof of impact)
- \"What kind of results have people seen from implementing [topic strategy]?\"
- \"How does someone's situation change when they apply [topic] effectively?\"

**4️⃣ Common Mistakes & Misconceptions** (Debunking myths)
- \"What are the biggest mistakes people make with [topic area]?\"
- \"What's the most common misconception about [topic]?\"

**5️⃣ Transformation & Story-Based Questions** (Audience journey)
- \"Can you share a powerful success story related to [topic]?\"
- \"What's the biggest shift people experience after learning [topic concept]?\"

### **Requirements:**
1. Generate exactly 10 unique, compelling questions
2. Each question should be interview-ready (clear and concise)
3. Questions should flow logically and build upon each other
4. Include a mix of strategic and tactical questions
5. Ensure questions allow for storytelling opportunities

### **Output Format:**
Please provide the questions as a numbered list (1., 2., etc.), with each question on a new line. Do not include any additional formatting or explanations.";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service should return formatted questions
        if (is_array($api_response)) {
            return [
                'questions' => $api_response,
                'count' => count($api_response),
                'topic' => isset($this->current_input['topic']) ? $this->current_input['topic'] : ''
            ];
        }
        
        // Parse questions from string response
        $questions = [];
        
        // Try to extract numbered questions
        if (preg_match_all('/^\s*\d+\.\s*(.+?)(?=^\s*\d+\.|$)/m', $api_response, $matches)) {
            $questions = array_map(function($q) {
                return trim($q, " '\"");
            }, $matches[1]);
        } else {
            // Fallback: split by lines and filter
            $lines = explode("\n", $api_response);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && !preg_match('/^(#|\*|\-|Guidelines|Requirements|Output)/', $line)) {
                    // Remove numbering if present
                    $line = preg_replace('/^\d+\.\s*/', '', $line);
                    $line = trim($line, " '\"");
                    if (strlen($line) > 10) { // Minimum question length
                        $questions[] = $line;
                    }
                }
            }
        }
        
        // Limit to 10 questions and ensure we have questions
        $questions = array_slice($questions, 0, 10);
        
        return [
            'questions' => $questions,
            'count' => count($questions),
            'topic' => isset($this->current_input['topic']) ? $this->current_input['topic'] : ''
        ];
    }
    
    /**
     * Get generator-specific input from POST data
     */
    protected function get_generator_specific_input() {
        return [
            'topic' => isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '',
            'topic_number' => isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 1,
            'entry_id' => isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0,
            'entry_key' => isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : ''
        ];
    }
    
    /**
     * Get field mappings for Formidable Forms based on topic number
     */
    protected function get_field_mappings() {
        $topic_number = isset($this->current_input['topic_number']) ? $this->current_input['topic_number'] : 1;
        
        // Map questions to appropriate Formidable field IDs based on topic
        $field_mappings = [
            1 => ['8505', '8506', '8507', '8508', '8509'], // Topic 1 → Questions 1-5
            2 => ['8510', '8511', '8512', '8513', '8514'], // Topic 2 → Questions 6-10
            3 => ['10370', '10371', '10372', '10373', '10374'], // Topic 3 → Questions 11-15
            4 => ['10375', '10376', '10377', '10378', '10379'], // Topic 4 → Questions 16-20
            5 => ['10380', '10381', '10382', '10383', '10384']  // Topic 5 → Questions 21-25
        ];
        
        return [
            'questions' => $field_mappings[$topic_number] ?? $field_mappings[1],
            'topic_number' => $topic_number,
            'generated_count' => '10361' // Field to store number of generated questions
        ];
    }
    
    /**
     * Get entry fields by field IDs (missing method)
     */
    public function get_entry_fields($entry_id, $field_ids) {
        if (!$this->formidable_service) {
            return ['success' => false, 'message' => 'Formidable service not available'];
        }
        
        $fields = [];
        foreach ($field_ids as $field_id) {
            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
            $fields[] = ['value' => $value];
        }
        
        return [
            'success' => true,
            'fields' => $fields
        ];
    }
    
    /**
     * Update entry fields (missing method)
     */
    public function update_entry_fields($entry_id, $field_data) {
        if (!$this->formidable_service) {
            return ['success' => false, 'message' => 'Formidable service not available'];
        }
        
        return $this->formidable_service->save_generated_content($entry_id, $field_data, $field_data);
    }
    
    /**
     * Get API options for question generation
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.8,
            'max_tokens' => 1500, // Increased for 10 detailed questions
            'top_p' => 0.9
        ];
    }
    
    /**
     * Enhanced AJAX generation handler
     */
    public function handle_ajax_generation() {
        // Handle legacy action name for backwards compatibility
        if (isset($_POST['action']) && $_POST['action'] === 'generate_interview_questions') {
            $this->handle_legacy_questions_generation();
            return;
        }
        
        // Use parent method for unified handling
        parent::handle_ajax_generation();
    }
    
    /**
     * Handle legacy questions generation for backwards compatibility
     */
    private function handle_legacy_questions_generation() {
        if (!check_ajax_referer('generate_topics_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Extract input data
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $topic = isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '';
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 1;
        
        if (empty($topic)) {
            error_log('MKCG Questions Generator: No topic provided');
            wp_send_json_error(['message' => 'No topic provided.']);
            return;
        }
        
        error_log('MKCG Questions Generator: Generating questions for topic: ' . $topic);
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'topic' => $topic,
            'topic_number' => $topic_number
        ];
        
        // Store for use in other methods
        $this->current_input = $input_data;
        
        // Validate input
        $validation_result = $this->validate_input($input_data);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($input_data);
        
        // Get API options
        $api_options = $this->get_api_options($input_data);
        
        // Generate content using API service
        $api_response = $this->api_service->generate_content(
            $prompt, 
            $this->generator_type, 
            $api_options
        );
        
        if (!$api_response['success']) {
            error_log('MKCG Questions Generator API Error: ' . print_r($api_response, true));
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        if (empty($formatted_output['questions'])) {
            wp_send_json_error(['message' => 'No questions were generated. Please try again.']);
            return;
        }
        
        // Save to Formidable if entry_id is provided
        if ($entry_id > 0) {
            $this->save_questions_to_formidable($entry_id, $formatted_output['questions'], $topic_number);
        }
        
        // Return success response
        wp_send_json_success([
            'questions' => $formatted_output['questions'],
            'count' => $formatted_output['count'],
            'topic' => $formatted_output['topic']
        ]);
    }
    
    /**
     * Save generated questions to Formidable Forms
     */
    private function save_questions_to_formidable($entry_id, $questions, $topic_number) {
        if (!$this->formidable_service) {
            error_log('MKCG Questions Generator: Formidable service not available');
            return false;
        }
        
        try {
            // Get the post ID from the entry
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
            
            if (!$post_id) {
                error_log('MKCG Questions Generator: No post ID found for entry ' . $entry_id);
                return false;
            }
            
            // Save questions to post meta
            $result = $this->formidable_service->save_questions_to_post($post_id, $questions, $topic_number);
            
            if ($result) {
                error_log('MKCG Questions Generator: Successfully saved ' . count($questions) . ' questions to post meta for topic ' . $topic_number);
            } else {
                error_log('MKCG Questions Generator: Failed to save questions to post meta');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('MKCG Questions Generator: Error saving to post meta: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * CORRECTED: Get topics from custom post associated with Formidable entry
     * Based on original working implementation
     */
    public function handle_get_topics_ajax() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        
        if (!$entry_id && !$entry_key) {
            wp_send_json_error(['message' => 'No entry ID or key provided']);
            return;
        }
        
        if ($entry_key) {
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            if (!$entry_data['success']) {
                wp_send_json_error(['message' => 'Entry not found: ' . $entry_key]);
                return;
            }
            $entry_id = $entry_data['entry_id'];
        }
        
        // Get the post ID associated with this Formidable entry
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'No custom post found for this entry']);
            return;
        }
        
        // Get topics from custom post meta fields
        $topics = $this->formidable_service->get_topics_from_post($post_id);
        
        if (empty($topics)) {
            wp_send_json_error(['message' => 'No topics found in custom post. Please generate topics first.']);
            return;
        }
        
        error_log('MKCG Questions: Successfully found ' . count($topics) . ' topics from custom post ' . $post_id);
        wp_send_json_success(['topics' => $topics]);
    }
    
    /**
     * Initialize Questions Generator with AJAX handlers
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions for backwards compatibility
        add_action('wp_ajax_generate_interview_questions', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_questions', [$this, 'handle_ajax_generation']);
        
        // Add new unified AJAX actions
        add_action('wp_ajax_mkcg_get_topics', [$this, 'handle_get_topics_ajax']);
        add_action('wp_ajax_nopriv_mkcg_get_topics', [$this, 'handle_get_topics_ajax']);
        
        // Add auto-save AJAX action
        add_action('wp_ajax_mkcg_save_question', [$this, 'handle_save_question_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_question', [$this, 'handle_save_question_ajax']);
    }
    
    /**
     * AJAX handler for auto-saving individual questions
     */
    public function handle_save_question_ajax() {
        if (!check_ajax_referer('generate_topics_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $question_number = isset($_POST['question_number']) ? intval($_POST['question_number']) : 0;
        $question = isset($_POST['question']) ? sanitize_textarea_field($_POST['question']) : '';
        
        if (!$post_id || !$question_number || empty($question)) {
            wp_send_json_error(['message' => 'Missing required parameters']);
            return;
        }
        
        // Save question to post meta
        $meta_key = 'question_' . $question_number;
        $result = update_post_meta($post_id, $meta_key, trim($question));
        
        if ($result !== false) {
            error_log("MKCG Questions: Auto-saved question {$question_number} to post {$post_id}");
            wp_send_json_success([
                'message' => 'Question saved successfully',
                'post_id' => $post_id,
                'question_number' => $question_number,
                'meta_key' => $meta_key
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save question']);
        }
    }
    
    /**
     * Enqueue scripts and styles for Questions Generator
     */
    public function enqueue_scripts() {
        parent::enqueue_scripts();
        
        // Enqueue Questions Generator specific script
        wp_enqueue_script(
            'mkcg-questions-generator',
            plugin_dir_url(__FILE__) . '../../assets/js/generators/questions-generator.js',
            ['mkcg-form-utils'],
            MKCG_VERSION,
            true
        );
        
        // Pass data to JavaScript
        wp_localize_script('mkcg-questions-generator', 'mkcg_questions_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'topics_nonce' => wp_create_nonce('generate_topics_nonce')
        ]);
    }
}