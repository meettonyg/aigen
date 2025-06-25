<?php
/**
 * MKCG Questions Generator
 * Generates interview questions based on a given topic
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
                'description' => 'The number of this topic in a series'
            ],
            'entry_id' => [
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
        
        $prompt = "You are an expert in generating highly engaging and insightful podcast interview questions. Your task is to generate **10 compelling interview questions** based on the provided **podcast topic**.

### **Podcast Topic:** \"$topic\"

### **Guidelines for Crafting Questions:**
- Each question must be **highly relevant to the topic**.
- Questions should be **open-ended** to encourage meaningful discussion.
- Ensure a **mix of question types** to balance storytelling, strategy, and implementation.

### **Question Categories & Examples:**

**1️⃣ Origin Questions** (The \"why\" behind the topic)
- \"What led you to develop this approach to [$topic]?\"
- \"How did you first realize the impact of [$topic]?\"

**2️⃣ Process Questions** (Step-by-step guidance)
- \"Can you walk us through your method for [$topic]?\"
- \"What does your process look like from start to finish?\"

**3️⃣ Result Questions** (Proof of impact)
- \"What kind of results have people seen from implementing [$topic]?\"
- \"How does someone's situation change when they apply [$topic] effectively?\"

**4️⃣ Common Mistakes & Misconceptions** (Debunking myths)
- \"What are the biggest mistakes people make with [$topic]?\"
- \"What's the most common misconception about [$topic]?\"

**5️⃣ Transformation & Story-Based Questions** (Audience journey)
- \"Can you share a powerful success story related to [$topic]?\"
- \"What's the biggest shift people experience after learning [$topic]?\"

### **Now generate 10 unique, compelling podcast interview questions based on the given topic.**

Format the output as a numbered list (1., 2., etc.), with each question on a new line.";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service already formats questions as an array
        if (is_array($api_response)) {
            return [
                'questions' => $api_response,
                'count' => count($api_response)
            ];
        }
        
        // Fallback if raw string returned
        $questions = [];
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
            $questions = array_map('trim', $matches[1]);
        } else {
            $questions = array_filter(array_map(function($q) {
                return trim($q, " '\"");
            }, explode("\n", $api_response)));
        }
        
        return [
            'questions' => $questions,
            'count' => count($questions)
        ];
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        return [
            'topic' => isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '',
            'topic_number' => isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 1
        ];
    }
    
    /**
     * Get field mappings for Formidable
     */
    protected function get_field_mappings() {
        // Map generated content to Formidable field IDs
        return [
            'questions' => 10361, // Example field ID for questions
            'question_count' => 10362 // Example field ID for question count
        ];
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.8,
            'max_tokens' => 1200
        ];
    }
    
    /**
     * Override AJAX generation to handle legacy compatibility
     */
    public function handle_ajax_generation() {
        // Handle legacy action name for backwards compatibility
        if (isset($_POST['action']) && $_POST['action'] === 'generate_interview_questions') {
            $this->handle_legacy_questions_generation();
            return;
        }
        
        // Call parent method for new unified handling
        parent::handle_ajax_generation();
    }
    
    /**
     * Handle legacy questions generation (for backwards compatibility)
     */
    private function handle_legacy_questions_generation() {
        // Use the original Questions generator logic for existing implementations
        if (!check_ajax_referer('generate_topics_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Make entry_id optional - the function will work with just a topic
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $topic = isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '';
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 1;
        
        if (empty($topic)) {
            error_log('No topic provided for question generation');
            wp_send_json_error(['message' => 'No topic provided.']);
            return;
        }
        
        error_log('Generating questions for topic: ' . $topic);
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'topic' => $topic,
            'topic_number' => $topic_number
        ];
        
        // Validate
        $validation_result = $this->validate_input($input_data);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_result['errors'])
            ]);
            return;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($input_data);
        
        // Generate content
        $api_response = $this->api_service->generate_content($prompt, $this->generator_type);
        
        if (!$api_response['success']) {
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        // Return in legacy format for compatibility
        wp_send_json_success([
            'questions' => $formatted_output['questions']
        ]);
    }
    
    /**
     * Initialize legacy AJAX actions for backwards compatibility
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions
        add_action('wp_ajax_generate_interview_questions', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_questions', [$this, 'handle_ajax_generation']);
    }
}