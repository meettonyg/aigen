<?php
/**
 * MKCG Questions Generator - Enhanced Unified Implementation
 * Generates interview questions based on selected topics with enhanced UI and Formidable integration
 */

class MKCG_Questions_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'questions';
    
    // Enhanced configuration
    protected $max_questions_per_topic = 10;
    protected $max_retries = 3;
    protected $cache_duration = 3600; // 1 hour
    
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
     * ENHANCED SYNC VERIFICATION - Get topics with real-time validation
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
        
        // Enhanced validation: Get and validate post association
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if (!$post_id) {
            wp_send_json_error([
                'message' => 'No custom post found for this entry',
                'debug_info' => 'Entry ' . $entry_id . ' has no associated post',
                'suggested_action' => 'Please check your Formidable form configuration'
            ]);
            return;
        }
        
        // Validate post association integrity
        $validation_result = $this->formidable_service->validate_post_association($entry_id, $post_id);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Post association validation failed',
                'issues' => $validation_result['issues'],
                'auto_fixed' => $validation_result['auto_fixed']
            ]);
            return;
        }
        
        // Enhanced topic retrieval with quality validation
        $topics_result = $this->formidable_service->get_topics_from_post_enhanced($post_id);
        
        if (empty($topics_result['topics']) || count(array_filter($topics_result['topics'])) === 0) {
            // Attempt auto-healing
            $healing_result = $this->formidable_service->heal_missing_data($post_id, 5);
            
            wp_send_json_error([
                'message' => 'No topics found in custom post. Please generate topics first.',
                'data_quality' => $topics_result['data_quality'],
                'healing_attempted' => $healing_result['success'],
                'suggested_action' => 'Generate topics using the Topics Generator first'
            ]);
            return;
        }
        
        // Verify generator sync status
        $sync_status = $this->verify_generator_sync($post_id);
        
        error_log('MKCG Enhanced Questions: Successfully found ' . count(array_filter($topics_result['topics'])) . ' topics from post ' . $post_id . ' (quality: ' . $topics_result['data_quality'] . ')');
        
        wp_send_json_success([
            'topics' => $topics_result['topics'],
            'data_quality' => $topics_result['data_quality'],
            'source_pattern' => $topics_result['source_pattern'],
            'sync_status' => $sync_status,
            'validation_status' => $validation_result,
            'auto_healed' => $topics_result['auto_healed'],
            'metadata' => $topics_result['metadata']
        ]);
    }
    
    /**
     * REAL-TIME SYNC VERIFICATION - Check data consistency between generators
     */
    public function verify_generator_sync($post_id) {
        $sync_status = [
            'in_sync' => false,
            'topics_updated' => null,
            'questions_updated' => null,
            'sync_lag' => 0,
            'issues' => [],
            'recommendations' => []
        ];
        
        if (!$post_id) {
            $sync_status['issues'][] = 'No post ID provided';
            return $sync_status;
        }
        
        // Get timestamps for data modification
        $topics_timestamp = get_post_meta($post_id, '_mkcg_topics_updated', true);
        $questions_timestamp = get_post_meta($post_id, '_mkcg_questions_updated', true);
        
        $sync_status['topics_updated'] = $topics_timestamp ? intval($topics_timestamp) : null;
        $sync_status['questions_updated'] = $questions_timestamp ? intval($questions_timestamp) : null;
        
        // Calculate sync lag
        if ($sync_status['topics_updated'] && $sync_status['questions_updated']) {
            $sync_status['sync_lag'] = abs($sync_status['topics_updated'] - $sync_status['questions_updated']);
            
            // Consider in sync if updated within 5 minutes of each other
            $sync_status['in_sync'] = ($sync_status['sync_lag'] <= 300);
            
            if (!$sync_status['in_sync']) {
                if ($sync_status['topics_updated'] > $sync_status['questions_updated']) {
                    $sync_status['issues'][] = 'Topics are newer than questions';
                    $sync_status['recommendations'][] = 'Consider regenerating questions for updated topics';
                } else {
                    $sync_status['issues'][] = 'Questions are newer than topics';
                    $sync_status['recommendations'][] = 'Topics may have been updated after questions were generated';
                }
            }
        } else {
            if (!$sync_status['topics_updated']) {
                $sync_status['issues'][] = 'No topics timestamp found';
                $sync_status['recommendations'][] = 'Topics may need to be regenerated';
            }
            
            if (!$sync_status['questions_updated']) {
                $sync_status['issues'][] = 'No questions timestamp found';
                $sync_status['recommendations'][] = 'Questions have not been generated yet';
            }
        }
        
        // Check data completeness
        $topics_result = $this->formidable_service->get_topics_from_post_enhanced($post_id);
        $questions_result = $this->formidable_service->get_questions_with_integrity_check($post_id);
        
        if ($topics_result['data_quality'] === 'poor' || $topics_result['data_quality'] === 'missing') {
            $sync_status['issues'][] = 'Topics data quality is ' . $topics_result['data_quality'];
            $sync_status['recommendations'][] = 'Regenerate topics to improve data quality';
        }
        
        if ($questions_result['integrity_status'] === 'poor' || $questions_result['integrity_status'] === 'fair') {
            $sync_status['issues'][] = 'Questions integrity is ' . $questions_result['integrity_status'];
            $sync_status['recommendations'][] = 'Regenerate questions to improve data integrity';
        }
        
        return $sync_status;
    }
    
    /**
     * DATA HEALTH MONITORING - Get comprehensive system status
     */
    public function get_data_health_status($post_id) {
        $health_status = [
            'overall_health' => 'unknown',
            'post_association' => [],
            'topics_health' => [],
            'questions_health' => [],
            'sync_health' => [],
            'recommendations' => [],
            'timestamp' => time()
        ];
        
        if (!$post_id) {
            $health_status['overall_health'] = 'critical';
            $health_status['recommendations'][] = 'No post ID provided';
            return $health_status;
        }
        
        // Check post association health
        $validation_result = $this->formidable_service->validate_post_association(0, $post_id);
        $health_status['post_association'] = $validation_result;
        
        // Check topics health
        $topics_result = $this->formidable_service->get_topics_from_post_enhanced($post_id);
        $health_status['topics_health'] = [
            'data_quality' => $topics_result['data_quality'],
            'total_topics' => $topics_result['metadata']['total_topics'],
            'quality_score' => $topics_result['metadata']['quality_score'],
            'auto_healed' => $topics_result['auto_healed']
        ];
        
        // Check questions health
        $questions_result = $this->formidable_service->get_questions_with_integrity_check($post_id);
        $health_status['questions_health'] = [
            'integrity_status' => $questions_result['integrity_status'],
            'total_found' => $questions_result['metadata']['total_found'],
            'gap_count' => $questions_result['metadata']['gap_count'],
            'auto_healed' => $questions_result['auto_healed']
        ];
        
        // Check sync health
        $sync_status = $this->verify_generator_sync($post_id);
        $health_status['sync_health'] = $sync_status;
        
        // Calculate overall health score
        $health_scores = [];
        
        // Post association score (0-25)
        $health_scores['post'] = $validation_result['valid'] ? 25 : 0;
        
        // Topics score (0-25)
        $topics_scores = [
            'excellent' => 25,
            'good' => 20,
            'poor' => 10,
            'missing' => 0
        ];
        $health_scores['topics'] = $topics_scores[$topics_result['data_quality']] ?? 0;
        
        // Questions score (0-25)
        $questions_scores = [
            'excellent' => 25,
            'good' => 20,
            'fair' => 15,
            'poor' => 5
        ];
        $health_scores['questions'] = $questions_scores[$questions_result['integrity_status']] ?? 0;
        
        // Sync score (0-25)
        $health_scores['sync'] = $sync_status['in_sync'] ? 25 : (count($sync_status['issues']) <= 1 ? 15 : 5);
        
        $total_score = array_sum($health_scores);
        
        // Determine overall health
        if ($total_score >= 90) {
            $health_status['overall_health'] = 'excellent';
        } elseif ($total_score >= 75) {
            $health_status['overall_health'] = 'good';
        } elseif ($total_score >= 50) {
            $health_status['overall_health'] = 'fair';
        } elseif ($total_score >= 25) {
            $health_status['overall_health'] = 'poor';
        } else {
            $health_status['overall_health'] = 'critical';
        }
        
        // Generate recommendations
        if ($health_status['overall_health'] === 'critical' || $health_status['overall_health'] === 'poor') {
            $health_status['recommendations'][] = 'Immediate attention required';
        }
        
        if (!$validation_result['valid']) {
            $health_status['recommendations'][] = 'Fix post association issues';
        }
        
        if ($topics_result['data_quality'] === 'poor' || $topics_result['data_quality'] === 'missing') {
            $health_status['recommendations'][] = 'Regenerate topics to improve quality';
        }
        
        if ($questions_result['integrity_status'] === 'poor' || $questions_result['integrity_status'] === 'fair') {
            $health_status['recommendations'][] = 'Regenerate questions to improve integrity';
        }
        
        if (!$sync_status['in_sync']) {
            $health_status['recommendations'] = array_merge($health_status['recommendations'], $sync_status['recommendations']);
        }
        
        $health_status['score_breakdown'] = $health_scores;
        $health_status['total_score'] = $total_score;
        
        return $health_status;
    }
    
    /**
     * Initialize Questions Generator with enhanced AJAX handlers and monitoring
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions for backwards compatibility
        add_action('wp_ajax_generate_interview_questions', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_interview_questions', [$this, 'handle_ajax_generation']);
        
        // Add enhanced unified AJAX actions
        add_action('wp_ajax_mkcg_get_topics', [$this, 'handle_get_topics_ajax']);
        add_action('wp_ajax_nopriv_mkcg_get_topics', [$this, 'handle_get_topics_ajax']);
        
        // Add auto-save AJAX actions
        add_action('wp_ajax_mkcg_save_question', [$this, 'handle_save_question_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_question', [$this, 'handle_save_question_ajax']);
        
        // Add topic editing AJAX handlers
        add_action('wp_ajax_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        
        // Enhanced save with monitoring
        add_action('wp_ajax_mkcg_save_all_data', [$this, 'handle_save_all_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_all_data', [$this, 'handle_save_all_data_ajax']);
        
        // NEW: Health monitoring endpoints
        add_action('wp_ajax_mkcg_health_check', [$this, 'handle_health_check_ajax']);
        add_action('wp_ajax_nopriv_mkcg_health_check', [$this, 'handle_health_check_ajax']);
        
        // NEW: Sync verification endpoint
        add_action('wp_ajax_mkcg_verify_sync', [$this, 'handle_verify_sync_ajax']);
        add_action('wp_ajax_nopriv_mkcg_verify_sync', [$this, 'handle_verify_sync_ajax']);
    }
    
    /**
     * AJAX endpoint for sync verification
     */
    public function handle_verify_sync_ajax() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        $sync_status = $this->verify_generator_sync($post_id);
        
        wp_send_json_success($sync_status);
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
     * CRITICAL FIX: AJAX handler for saving individual topics (inline editing)
     */
    public function handle_save_topic_ajax() {
        if (!check_ajax_referer('generate_topics_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 0;
        $topic_text = isset($_POST['topic_text']) ? sanitize_textarea_field($_POST['topic_text']) : '';
        
        if (!$post_id || !$topic_number || ($topic_number < 1 || $topic_number > 5)) {
            wp_send_json_error(['message' => 'Missing or invalid parameters']);
            return;
        }
        
        // Save topic to post meta using Formidable service
        if (!$this->formidable_service) {
            wp_send_json_error(['message' => 'Formidable service not available']);
            return;
        }
        
        $result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_number, $topic_text);
        
        if ($result) {
            error_log("MKCG Questions: Saved topic {$topic_number} to post {$post_id}: " . substr($topic_text, 0, 50));
            wp_send_json_success([
                'message' => 'Topic saved successfully',
                'post_id' => $post_id,
                'topic_number' => $topic_number,
                'topic_text' => $topic_text
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save topic']);
        }
    }
    
    /**
     * ENHANCED AJAX handler for saving all topics and questions data with monitoring
     */
    public function handle_save_all_data_ajax() {
        if (!check_ajax_referer('generate_topics_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $topics_data = isset($_POST['topics']) ? $_POST['topics'] : [];
        $questions_data = isset($_POST['questions']) ? $_POST['questions'] : [];
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        if (!$this->formidable_service) {
            wp_send_json_error(['message' => 'Formidable service not available']);
            return;
        }
        
        // Pre-save validation
        $validation_result = $this->formidable_service->validate_post_association($entry_id, $post_id);
        if (!$validation_result['valid']) {
            wp_send_json_error([
                'message' => 'Cannot save: Post validation failed',
                'issues' => $validation_result['issues']
            ]);
            return;
        }
        
        $saved_topics = 0;
        $saved_questions = 0;
        $data_quality_before = null;
        $data_quality_after = null;
        
        // Get before state for comparison
        $before_health = $this->get_data_health_status($post_id);
        $data_quality_before = [
            'topics' => $before_health['topics_health']['data_quality'],
            'questions' => $before_health['questions_health']['integrity_status']
        ];
        
        // Save topics with enhanced validation
        if (!empty($topics_data) && is_array($topics_data)) {
            $clean_topics = [];
            foreach ($topics_data as $num => $text) {
                $topic_num = intval($num);
                if ($topic_num >= 1 && $topic_num <= 5) {
                    $sanitized_text = sanitize_textarea_field($text);
                    if (!empty($sanitized_text)) {
                        // Validate topic quality before saving
                        $validation = $this->formidable_service->validate_topic_content($sanitized_text);
                        if ($validation['valid']) {
                            $clean_topics[$topic_num] = $validation['cleaned_content'];
                        } else {
                            error_log("MKCG Enhanced Save: Topic {$topic_num} failed validation: " . implode(', ', $validation['issues']));
                            // Save anyway but log the issue
                            $clean_topics[$topic_num] = $sanitized_text;
                        }
                    }
                }
            }
            
            if (!empty($clean_topics)) {
                $result = $this->formidable_service->save_topics_to_post($post_id, $clean_topics);
                if ($result) {
                    $saved_topics = count($clean_topics);
                    // Update topics timestamp for sync tracking
                    update_post_meta($post_id, '_mkcg_topics_updated', time());
                }
            }
        }
        
        // Save questions with enhanced validation
        if (!empty($questions_data) && is_array($questions_data)) {
            foreach ($questions_data as $topic_num => $topic_questions) {
                if (is_array($topic_questions)) {
                    $clean_questions = [];
                    foreach ($topic_questions as $q_num => $question) {
                        $sanitized_question = sanitize_textarea_field($question);
                        if (!empty($sanitized_question)) {
                            // Validate question quality before saving
                            $validation = $this->formidable_service->validate_question_content($sanitized_question);
                            if ($validation['valid']) {
                                $clean_questions[] = $validation['cleaned_content'];
                            } else {
                                error_log("MKCG Enhanced Save: Question for topic {$topic_num} failed validation: " . implode(', ', $validation['issues']));
                                // Save anyway but log the issue
                                $clean_questions[] = $sanitized_question;
                            }
                        }
                    }
                    
                    if (!empty($clean_questions)) {
                        $result = $this->formidable_service->save_questions_to_post($post_id, $clean_questions, intval($topic_num));
                        if ($result) {
                            $saved_questions += count($clean_questions);
                        }
                    }
                }
            }
            
            if ($saved_questions > 0) {
                // Update questions timestamp for sync tracking
                update_post_meta($post_id, '_mkcg_questions_updated', time());
            }
        }
        
        // Get after state for comparison
        $after_health = $this->get_data_health_status($post_id);
        $data_quality_after = [
            'topics' => $after_health['topics_health']['data_quality'],
            'questions' => $after_health['questions_health']['integrity_status']
        ];
        
        // Calculate improvement metrics
        $quality_improved = [
            'topics' => $this->compare_quality_levels($data_quality_before['topics'], $data_quality_after['topics']),
            'questions' => $this->compare_quality_levels($data_quality_before['questions'], $data_quality_after['questions'])
        ];
        
        error_log("MKCG Enhanced Save: Saved {$saved_topics} topics and {$saved_questions} questions. Quality improvement - Topics: {$quality_improved['topics']}, Questions: {$quality_improved['questions']}");
        
        wp_send_json_success([
            'message' => "Successfully saved {$saved_topics} topics and {$saved_questions} questions",
            'saved_topics' => $saved_topics,
            'saved_questions' => $saved_questions,
            'post_id' => $post_id,
            'data_quality_before' => $data_quality_before,
            'data_quality_after' => $data_quality_after,
            'quality_improved' => $quality_improved,
            'overall_health' => $after_health['overall_health'],
            'sync_status' => $this->verify_generator_sync($post_id)
        ]);
    }
    
    /**
     * Compare quality levels and return improvement status
     */
    private function compare_quality_levels($before, $after) {
        $quality_levels = ['missing' => 0, 'poor' => 1, 'fair' => 2, 'good' => 3, 'excellent' => 4];
        
        $before_score = $quality_levels[$before] ?? 0;
        $after_score = $quality_levels[$after] ?? 0;
        
        if ($after_score > $before_score) {
            return 'improved';
        } elseif ($after_score < $before_score) {
            return 'degraded';
        } else {
            return 'unchanged';
        }
    }
    
    /**
     * AJAX endpoint for health monitoring
     */
    public function handle_health_check_ajax() {
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Post ID is required']);
            return;
        }
        
        $health_status = $this->get_data_health_status($post_id);
        
        wp_send_json_success($health_status);
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