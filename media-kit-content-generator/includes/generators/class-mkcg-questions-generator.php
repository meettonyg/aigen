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
     * CRITICAL FIX: Legacy questions generation with unified nonce validation
     */
    private function handle_legacy_questions_generation() {
        // CRITICAL FIX: Use unified nonce validation for legacy generation
        $nonce_verified = false;
        if (isset($_POST['security']) && wp_verify_nonce($_POST['security'], 'mkcg_nonce')) {
            $nonce_verified = true;
        } elseif (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'mkcg_nonce')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified) {
            error_log('MKCG Legacy Generation: ❌ Security check failed');
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        // Extract input data
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $topic = isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '';
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 1;
        
        if (empty($topic)) {
            error_log('MKCG Enhanced Questions: No topic provided');
            wp_send_json_error(['message' => 'No topic provided.']);
            return;
        }
        
        error_log('MKCG Enhanced Questions: Generating questions for topic: ' . $topic . ' (Topic ' . $topic_number . ')');
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'topic' => $topic,
            'topic_number' => $topic_number
        ];
        
        // Store for use in other methods
        $this->current_input = $input_data;
        
        // Enhanced validation
        $validation_result = $this->validate_input($input_data);
        if (!$validation_result['valid']) {
            error_log('MKCG Enhanced Questions: Validation failed: ' . implode(', ', $validation_result['errors']));
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
        error_log('MKCG Enhanced Questions: Calling OpenAI API for topic ' . $topic_number);
        $api_response = $this->api_service->generate_content(
            $prompt, 
            $this->generator_type, 
            $api_options
        );
        
        if (!$api_response['success']) {
            error_log('MKCG Enhanced Questions: API Error: ' . print_r($api_response, true));
            wp_send_json_error($api_response);
            return;
        }
        
        // Format output
        $formatted_output = $this->format_output($api_response['content']);
        
        if (empty($formatted_output['questions'])) {
            error_log('MKCG Enhanced Questions: No questions generated from API response');
            wp_send_json_error(['message' => 'No questions were generated. Please try again.']);
            return;
        }
        
        error_log('MKCG Enhanced Questions: Successfully generated ' . count($formatted_output['questions']) . ' questions');
        
        // ENHANCED: Save to both locations if entry_id is provided
        $save_success = false;
        $save_details = [];
        
        if ($entry_id > 0) {
            error_log('MKCG Enhanced Questions: Starting bulletproof save for entry ' . $entry_id);
            $save_success = $this->save_questions_to_formidable($entry_id, $formatted_output['questions'], $topic_number);
            
            if ($save_success) {
                error_log('MKCG Enhanced Questions: ✅ Save completed successfully');
                $save_details['save_status'] = 'success';
                $save_details['message'] = 'Questions saved to both WordPress and Formidable';
            } else {
                error_log('MKCG Enhanced Questions: ⚠️ Save had issues');
                $save_details['save_status'] = 'partial';
                $save_details['message'] = 'Questions may have been saved partially';
            }
        } else {
            error_log('MKCG Enhanced Questions: No entry ID provided - skipping save');
            $save_details['save_status'] = 'skipped';
            $save_details['message'] = 'No entry ID provided';
        }
        
        // Return enhanced success response
        wp_send_json_success([
            'questions' => $formatted_output['questions'],
            'count' => $formatted_output['count'],
            'topic' => $formatted_output['topic'],
            'topic_number' => $topic_number,
            'save_details' => $save_details,
            'generation_successful' => true
        ]);
    }
    
    /**
     * ENHANCED: Save generated questions using bulletproof dual save strategy
     */
    private function save_questions_to_formidable($entry_id, $questions, $topic_number) {
        if (!$this->formidable_service) {
            error_log('MKCG Enhanced Questions: Formidable service not available');
            return false;
        }
        
        try {
            // Get the post ID from the entry using enhanced lookup
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
            
            if (!$post_id) {
                error_log('MKCG Enhanced Questions: No post ID found for entry ' . $entry_id . ' - attempting alternative save strategy');
                
                // Alternative: Try to save using entry-based approach
                return $this->save_questions_entry_based($entry_id, $questions, $topic_number);
            }
            
            // Use enhanced bulletproof save (both post meta + Formidable fields)
            $result = $this->formidable_service->save_questions_to_post($post_id, $questions, $topic_number);
            
            if ($result) {
                error_log('MKCG Enhanced Questions: ✅ Bulletproof save completed for topic ' . $topic_number);
                
                // Verify the save worked by checking both locations
                $verification = $this->verify_save_success($post_id, $entry_id, $topic_number);
                if ($verification['both_locations']) {
                    error_log('MKCG Enhanced Questions: ✅ VERIFICATION PASSED - Questions saved to both locations');
                } else {
                    error_log('MKCG Enhanced Questions: ⚠️ PARTIAL SUCCESS - ' . $verification['message']);
                }
            } else {
                error_log('MKCG Enhanced Questions: ❌ Bulletproof save failed');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('MKCG Enhanced Questions: Exception during enhanced save: ' . $e->getMessage());
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
        
        // Add simple save AJAX handler
        add_action('wp_ajax_mkcg_save_all_data', [$this, 'handle_save_all_data_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_all_data', [$this, 'handle_save_all_data_ajax']);
        
        // CRITICAL FIX: Add inline topic editing AJAX handlers
        add_action('wp_ajax_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_topic', [$this, 'handle_save_topic_ajax']);
        
        // Question auto-save handlers
        add_action('wp_ajax_mkcg_save_question', [$this, 'handle_save_question_ajax']);
        add_action('wp_ajax_nopriv_mkcg_save_question', [$this, 'handle_save_question_ajax']);
        
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
        // UNIFIED NONCE STRATEGY
        if (!check_ajax_referer('mkcg_nonce', 'nonce', false)) {
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
     * CRITICAL FIX: AJAX handler for saving individual topics with proper nonce validation
     */
    public function handle_save_topic_ajax() {
        // CRITICAL FIX: Use unified nonce strategy with proper validation
        $nonce_verified = false;
        $nonce_value = '';
        
        // Try multiple nonce fields with correct actions
        $nonce_checks = [
            ['field' => 'security', 'action' => 'mkcg_save_nonce'],
            ['field' => 'nonce', 'action' => 'mkcg_nonce'], 
            ['field' => 'save_nonce', 'action' => 'mkcg_save_nonce'],
            ['field' => 'mkcg_nonce', 'action' => 'mkcg_nonce']
        ];
        
        foreach ($nonce_checks as $check) {
            if (isset($_POST[$check['field']]) && !empty($_POST[$check['field']])) {
                $nonce_value = $_POST[$check['field']];
                if (wp_verify_nonce($nonce_value, $check['action'])) {
                    $nonce_verified = true;
                    error_log("MKCG Topic Save: ✅ Nonce verified using field '{$check['field']}' with action '{$check['action']}'");
                    break;
                }
            }
        }
        
        if (!$nonce_verified) {
            error_log('MKCG Topic Save: ❌ Security check failed - no valid nonce found');
            error_log('MKCG Topic Save: Available POST fields: ' . implode(', ', array_keys($_POST)));
            wp_send_json_error([
                'message' => 'Security check failed', 
                'debug' => 'Nonce verification failed - please refresh the page'
            ]);
            return;
        }
        
        // Extract and validate parameters
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 0;
        $topic_text = isset($_POST['topic_text']) ? sanitize_textarea_field($_POST['topic_text']) : '';
        
        error_log("MKCG Topic Save: Processing request - post_id: {$post_id}, topic_number: {$topic_number}, text_length: " . strlen($topic_text));
        
        // Enhanced parameter validation
        $validation_errors = [];
        
        if (!$post_id) {
            $validation_errors[] = 'Post ID is required';
        }
        
        if (!$topic_number || $topic_number < 1 || $topic_number > 5) {
            $validation_errors[] = 'Topic number must be between 1 and 5';
        }
        
        if (empty(trim($topic_text))) {
            $validation_errors[] = 'Topic text cannot be empty';
        } elseif (strlen(trim($topic_text)) < 5) {
            $validation_errors[] = 'Topic text must be at least 5 characters';
        } elseif (strlen(trim($topic_text)) > 500) {
            $validation_errors[] = 'Topic text cannot exceed 500 characters';
        }
        
        if (!empty($validation_errors)) {
            error_log('MKCG Topic Save: Validation failed: ' . implode(', ', $validation_errors));
            wp_send_json_error([
                'message' => 'Validation failed: ' . implode(', ', $validation_errors),
                'errors' => $validation_errors
            ]);
            return;
        }
        
        // Verify post exists and is accessible
        $post = get_post($post_id);
        if (!$post) {
            error_log("MKCG Topic Save: Post {$post_id} does not exist");
            wp_send_json_error([
                'message' => 'Post not found',
                'debug' => "Post ID {$post_id} does not exist"
            ]);
            return;
        }
        
        // Check Formidable service availability
        if (!$this->formidable_service) {
            error_log('MKCG Topic Save: Formidable service not available');
            wp_send_json_error([
                'message' => 'Backend service not available',
                'debug' => 'Formidable service initialization failed'
            ]);
            return;
        }
        
        // Trim and prepare final text
        $final_topic_text = trim($topic_text);
        
        try {
            // Save topic to post meta using Formidable service
            $result = $this->formidable_service->save_single_topic_to_post($post_id, $topic_number, $final_topic_text);
            
            if ($result) {
                // Also update topics timestamp for sync tracking
                update_post_meta($post_id, '_mkcg_topics_updated', time());
                
                error_log("MKCG Topic Save: SUCCESS - Saved topic {$topic_number} to post {$post_id}: '" . substr($final_topic_text, 0, 50) . (strlen($final_topic_text) > 50 ? '...' : '') . "'");
                
                wp_send_json_success([
                    'message' => 'Topic saved successfully',
                    'post_id' => $post_id,
                    'topic_number' => $topic_number,
                    'topic_text' => $final_topic_text,
                    'char_count' => strlen($final_topic_text),
                    'timestamp' => time()
                ]);
            } else {
                error_log("MKCG Topic Save: FAILED - save_single_topic_to_post returned false for topic {$topic_number}");
                wp_send_json_error([
                    'message' => 'Failed to save topic to database',
                    'debug' => 'Backend save operation failed'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("MKCG Topic Save: EXCEPTION - " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Save failed due to server error',
                'debug' => 'Exception occurred during save: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ENHANCED AJAX handler for saving all questions data with comprehensive validation
     */
    public function handle_save_all_data_ajax() {
        // Handle both JSON and form-encoded requests
        $input_data = $this->get_request_data();
        
        error_log('MKCG Enhanced Save: Starting save process');
        error_log('MKCG Enhanced Save: Input data keys: ' . implode(', ', array_keys($input_data)));
        
        if (!$this->verify_nonce($input_data)) {
            error_log('MKCG Save All: Security check failed');
            wp_send_json_error([
                'message' => 'Security check failed',
                'debug' => 'Nonce verification failed - please refresh the page'
            ]);
            return;
        }
        
        // Extract and validate basic parameters
        $post_id = isset($input_data['post_id']) ? intval($input_data['post_id']) : 0;
        $entry_id = isset($input_data['entry_id']) ? intval($input_data['entry_id']) : 0;
        $questions_data = isset($input_data['questions']) ? $input_data['questions'] : null;
        
        error_log('MKCG Enhanced Save: post_id=' . $post_id . ', entry_id=' . $entry_id);
        error_log('MKCG Enhanced Save: questions_data type=' . gettype($questions_data));
        
        if (!$post_id) {
            error_log('MKCG Enhanced Save: Missing post_id');
            wp_send_json_error([
                'message' => 'Post ID is required',
                'debug' => 'No post_id parameter found in request'
            ]);
            return;
        }
        
        if (!$this->formidable_service) {
            error_log('MKCG Enhanced Save: Formidable service not available');
            wp_send_json_error([
                'message' => 'Formidable service not available',
                'debug' => 'Backend service initialization failed'
            ]);
            return;
        }
        
        // COMPREHENSIVE QUESTIONS DATA VALIDATION
        $validation_result = $this->validate_questions_data($questions_data);
        
        if (!$validation_result['valid']) {
            error_log('MKCG Enhanced Save: Questions data validation failed: ' . implode(', ', $validation_result['errors']));
            wp_send_json_error([
                'message' => 'Questions data validation failed',
                'errors' => $validation_result['errors'],
                'debug' => $validation_result['debug_info']
            ]);
            return;
        }
        
        // Use validated and normalized data
        $normalized_questions = $validation_result['normalized_data'];
        
        error_log('MKCG Enhanced Save: Validation passed, proceeding with save');
        error_log('MKCG Enhanced Save: Normalized data structure: ' . print_r(array_map(function($topic) { return count($topic) . ' questions'; }, $normalized_questions), true));
        
        $saved_questions = 0;
        $saved_topics = 0;
        $save_errors = [];
        
        // Save questions for all 5 topics with enhanced debugging
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            error_log("MKCG DEBUG: Processing topic {$topic_num}");
            
            if (isset($normalized_questions[$topic_num])) {
                $topic_questions = $normalized_questions[$topic_num];
                
                error_log("MKCG DEBUG: Topic {$topic_num} questions type: " . gettype($topic_questions));
                error_log("MKCG DEBUG: Topic {$topic_num} questions count: " . (is_array($topic_questions) ? count($topic_questions) : 'not_array'));
                error_log("MKCG DEBUG: Topic {$topic_num} questions content: " . print_r($topic_questions, true));
                
                try {
                    $result = $this->formidable_service->save_questions_to_post($post_id, $topic_questions, $topic_num);
                    
                    error_log("MKCG DEBUG: save_questions_to_post returned: " . ($result ? 'true' : 'false') . " for topic {$topic_num}");
                    
                    if ($result) {
                        $non_empty_count = count(array_filter($topic_questions, function($q) { return !empty(trim($q)); }));
                        $saved_questions += $non_empty_count;
                        $saved_topics++;
                        
                        error_log("MKCG Enhanced Save: Topic {$topic_num} - saved {$non_empty_count} non-empty questions out of " . count($topic_questions) . " total");
                    } else {
                        $save_errors[] = "Failed to save questions for Topic {$topic_num}";
                        error_log("MKCG Enhanced Save: Failed to save Topic {$topic_num}");
                        
                        // Additional debug for failed saves
                        $non_empty_count = is_array($topic_questions) ? count(array_filter($topic_questions, function($q) { return !empty(trim($q)); })) : 0;
                        error_log("MKCG DEBUG: Topic {$topic_num} had {$non_empty_count} non-empty questions but save failed");
                    }
                } catch (Exception $e) {
                    $save_errors[] = "Error saving Topic {$topic_num}: " . $e->getMessage();
                    error_log("MKCG Enhanced Save: Exception saving Topic {$topic_num}: " . $e->getMessage());
                }
            } else {
                error_log("MKCG Enhanced Save: No data for Topic {$topic_num}");
            }
        }
        
        if ($saved_questions > 0 || $saved_topics > 0) {
            // Update questions timestamp for sync tracking
            update_post_meta($post_id, '_mkcg_questions_updated', time());
            
            $success_message = "Successfully saved {$saved_questions} questions across {$saved_topics} topics";
            if (!empty($save_errors)) {
                $success_message .= " (with " . count($save_errors) . " warnings)";
            }
            
            error_log("MKCG Enhanced Save: SUCCESS - {$success_message}");
            
            wp_send_json_success([
                'message' => $success_message,
                'saved_questions' => $saved_questions,
                'saved_topics' => $saved_topics,
                'total_topics' => 5,
                'total_slots' => 25,
                'post_id' => $post_id,
                'warnings' => $save_errors,
                'validation_info' => $validation_result['info']
            ]);
        } else {
            $error_message = 'No questions were saved';
            if (!empty($save_errors)) {
                $error_message .= ': ' . implode(', ', $save_errors);
            }
            
            error_log("MKCG Enhanced Save: FAILURE - {$error_message}");
            
            wp_send_json_error([
                'message' => $error_message,
                'errors' => $save_errors,
                'debug' => 'All save operations failed'
            ]);
        }
    }
    
    /**
     * NEW: Comprehensive questions data validation with normalization
     */
    private function validate_questions_data($questions_data) {
        $validation = [
            'valid' => false,
            'errors' => [],
            'debug_info' => [],
            'normalized_data' => [],
            'info' => []
        ];
        
        // Check if questions data exists
        if ($questions_data === null || $questions_data === '') {
            $validation['errors'][] = 'No questions data provided';
            $validation['debug_info'][] = 'questions parameter is null or empty';
            return $validation;
        }
        
        // Handle different data types
        if (is_string($questions_data)) {
            // Try to parse as JSON if it's a string
            $decoded = json_decode($questions_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $questions_data = $decoded;
                $validation['info'][] = 'Converted JSON string to array';
            } else {
                $validation['errors'][] = 'Questions data is a string but not valid JSON';
                $validation['debug_info'][] = 'String content: ' . substr($questions_data, 0, 100);
                return $validation;
            }
        }
        
        // Convert object to array if needed
        if (is_object($questions_data)) {
            $questions_data = (array) $questions_data;
            $validation['info'][] = 'Converted object to array';
        }
        
        // Must be an array at this point
        if (!is_array($questions_data)) {
            $validation['errors'][] = 'Questions data must be an array or object';
            $validation['debug_info'][] = 'Data type: ' . gettype($questions_data);
            return $validation;
        }
        
        // Validate and normalize structure
        $total_questions = 0;
        $total_topics = 0;
        
        for ($topic_num = 1; $topic_num <= 5; $topic_num++) {
            $topic_questions = [];
            
            if (isset($questions_data[$topic_num])) {
                $topic_data = $questions_data[$topic_num];
                $total_topics++;
                
                if (is_array($topic_data)) {
                    // Process each question in the topic
                    for ($q_num = 0; $q_num < 5; $q_num++) {
                        if (isset($topic_data[$q_num])) {
                            $question = $topic_data[$q_num];
                            
                            if (is_string($question)) {
                                $sanitized = sanitize_textarea_field(trim($question));
                                $topic_questions[] = $sanitized;
                                
                                if (!empty($sanitized)) {
                                    $total_questions++;
                                }
                            } else {
                                $topic_questions[] = ''; // Invalid question type
                                $validation['debug_info'][] = "Topic {$topic_num}, Question " . ($q_num + 1) . " is not a string";
                            }
                        } else {
                            $topic_questions[] = ''; // Missing question
                        }
                    }
                } else {
                    // Topic data is not an array
                    $validation['debug_info'][] = "Topic {$topic_num} data is not an array: " . gettype($topic_data);
                    $topic_questions = ['', '', '', '', '']; // Fill with empty questions
                }
            } else {
                // Topic not provided
                $topic_questions = ['', '', '', '', '']; // Fill with empty questions
            }
            
            $validation['normalized_data'][$topic_num] = $topic_questions;
        }
        
        // Final validation
        if ($total_questions === 0) {
            $validation['errors'][] = 'No valid questions found in any topic';
        } else {
            $validation['valid'] = true;
        }
        
        $validation['info'][] = "Found {$total_questions} non-empty questions across {$total_topics} topics";
        $validation['debug_info'][] = "Total structure: 5 topics with 5 questions each (" . ($total_questions) . " non-empty)";
        
        return $validation;
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
     * 🔄 MISSING SAVE HANDLERS - Complete Implementation
     */
     
    /**
     * AJAX handler for saving topic questions (current topic only)
     */
    public function handle_save_topic_questions_ajax() {
        // UNIFIED NONCE STRATEGY  
        if (!check_ajax_referer('mkcg_nonce', 'security', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        $questions = isset($_POST['questions']) ? $_POST['questions'] : [];
        
        if (!$post_id || !$topic_id || empty($questions)) {
            wp_send_json_error(['message' => 'Missing required parameters']);
            return;
        }
        
        if ($topic_id < 1 || $topic_id > 5) {
            wp_send_json_error(['message' => 'Invalid topic ID']);
            return;
        }
        
        try {
            $saved_count = 0;
            
            // Save questions to post meta
            if (is_array($questions)) {
                $result = $this->formidable_service->save_questions_to_post($post_id, $questions, $topic_id);
                
                if ($result) {
                    $saved_count = count($questions);
                    
                    // Update questions timestamp for sync tracking
                    update_post_meta($post_id, '_mkcg_questions_updated', time());
                    
                    error_log("MKCG Questions: Saved {$saved_count} questions for topic {$topic_id} to post {$post_id}");
                }
            }
            
            if ($saved_count > 0) {
                wp_send_json_success([
                    'message' => "Successfully saved {$saved_count} questions for Topic {$topic_id}",
                    'saved_count' => $saved_count,
                    'topic_id' => $topic_id,
                    'post_id' => $post_id
                ]);
            } else {
                wp_send_json_error(['message' => 'No questions were saved']);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Questions: Save topic questions error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Save failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get Formidable field mapping for a topic and question position
     */
    private function getFormidableFieldMapping($topic_id, $field_position) {
        // Field mapping for Questions Generator
        // Topic 1: Questions 1-5 → Fields 8505-8509
        // Topic 2: Questions 6-10 → Fields 8510-8514
        // Topic 3: Questions 11-15 → Fields 10370-10374
        // Topic 4: Questions 16-20 → Fields 10375-10379
        // Topic 5: Questions 21-25 → Fields 10380-10384
        
        $field_mappings = [
            1 => ['8505', '8506', '8507', '8508', '8509'], // Topic 1 → Questions 1-5
            2 => ['8510', '8511', '8512', '8513', '8514'], // Topic 2 → Questions 6-10
            3 => ['10370', '10371', '10372', '10373', '10374'], // Topic 3 → Questions 11-15
            4 => ['10375', '10376', '10377', '10378', '10379'], // Topic 4 → Questions 16-20
            5 => ['10380', '10381', '10382', '10383', '10384']  // Topic 5 → Questions 21-25
        ];
        
        if (isset($field_mappings[$topic_id]) && isset($field_mappings[$topic_id][$field_position - 1])) {
            return $field_mappings[$topic_id][$field_position - 1];
        }
        
        return false;
    }
    
    /**
     * ENHANCED: Get request data from either JSON or form-encoded request with better detection
     */
    private function get_request_data() {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        error_log('MKCG Request Debug: Method=' . $request_method . ', Content-Type=' . $content_type);
        
        // Enhanced JSON detection (check multiple indicators)
        $is_json_request = (
            strpos($content_type, 'application/json') !== false ||
            (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );
        
        if ($is_json_request || ($request_method === 'POST' && empty($_POST))) {
            // Handle JSON request or empty POST (likely JSON)
            $raw_input = file_get_contents('php://input');
            error_log('MKCG Request Debug: Raw input length: ' . strlen($raw_input));
            error_log('MKCG Request Debug: Raw input preview: ' . substr($raw_input, 0, 200));
            
            if (!empty($raw_input)) {
                $json_data = json_decode($raw_input, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('MKCG Request Debug: JSON decode error: ' . json_last_error_msg());
                    error_log('MKCG Request Debug: Falling back to POST data');
                    return $_POST; // Fallback to POST
                }
                
                error_log('MKCG Request Debug: Successfully parsed JSON with ' . count($json_data) . ' keys');
                return $json_data;
            }
        }
        
        // Handle form-encoded request or fallback
        error_log('MKCG Request Debug: Using POST data with ' . count($_POST) . ' keys');
        return $_POST;
    }
    
    /**
     * ENHANCED: Verify nonce from either JSON or form data with multiple nonce support
     */
    private function verify_nonce($input_data) {
        // Check multiple possible nonce field names - UNIFIED STRATEGY
        $possible_nonces = [
            'security' => 'mkcg_nonce',
            'nonce' => 'mkcg_nonce',
            'mkcg_nonce' => 'mkcg_nonce',
            '_wpnonce' => 'mkcg_nonce'
        ];
        
        foreach ($possible_nonces as $field_name => $nonce_action) {
            if (isset($input_data[$field_name]) && !empty($input_data[$field_name])) {
                $nonce = $input_data[$field_name];
                $verified = wp_verify_nonce($nonce, $nonce_action);
                
                if ($verified) {
                    error_log('MKCG Nonce Debug: Successfully verified nonce using field "' . $field_name . '" for action "' . $nonce_action . '"');
                    return true;
                } else {
                    error_log('MKCG Nonce Debug: Failed verification for field "' . $field_name . '" (action: "' . $nonce_action . '"), nonce: ' . substr($nonce, 0, 10) . '...');
                }
            }
        }
        
        // Log all available fields for debugging
        $available_fields = array_keys($input_data);
        error_log('MKCG Nonce Debug: No valid nonce found. Available fields: ' . implode(', ', $available_fields));
        
        return false;
    }
    
    /**
     * NEW: Verify that save was successful in both locations
     */
    private function verify_save_success($post_id, $entry_id, $topic_number) {
        $verification = [
            'post_meta_success' => false,
            'formidable_success' => false,
            'both_locations' => false,
            'message' => '',
            'details' => []
        ];
        
        // Check post meta saves (primary location)
        $post_meta_count = 0;
        for ($i = 1; $i <= 5; $i++) {
            $question_number = (($topic_number - 1) * 5) + $i;
            $meta_key = 'question_' . $question_number;
            $value = get_post_meta($post_id, $meta_key, true);
            
            if (!empty($value)) {
                $post_meta_count++;
            }
        }
        
        $verification['post_meta_success'] = ($post_meta_count > 0);
        $verification['details']['post_meta_count'] = $post_meta_count;
        
        // Check Formidable field saves (secondary location)
        $formidable_count = 0;
        if ($entry_id) {
            $field_mappings = $this->get_field_mappings();
            if (isset($field_mappings['questions'])) {
                foreach ($field_mappings['questions'] as $field_id) {
                    $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                    if (!empty($value)) {
                        $formidable_count++;
                    }
                }
            }
        }
        
        $verification['formidable_success'] = ($formidable_count > 0);
        $verification['details']['formidable_count'] = $formidable_count;
        
        // Overall assessment
        if ($verification['post_meta_success'] && $verification['formidable_success']) {
            $verification['both_locations'] = true;
            $verification['message'] = "Full success: {$post_meta_count} post meta + {$formidable_count} Formidable";
        } elseif ($verification['post_meta_success']) {
            $verification['message'] = "Partial success: {$post_meta_count} post meta only";
        } elseif ($verification['formidable_success']) {
            $verification['message'] = "Partial success: {$formidable_count} Formidable only";
        } else {
            $verification['message'] = "No saves verified";
        }
        
        return $verification;
    }
    
    /**
     * NEW: Fallback save method when post ID lookup fails
     */
    private function save_questions_entry_based($entry_id, $questions, $topic_number) {
        error_log('MKCG Enhanced Questions: Using entry-based save fallback for entry ' . $entry_id);
        
        if (!$this->formidable_service) {
            error_log('MKCG Enhanced Questions: Formidable service not available for entry-based save');
            return false;
        }
        
        $saved_count = 0;
        $field_mappings = $this->get_field_mappings();
        
        if (!isset($field_mappings['questions'])) {
            error_log('MKCG Enhanced Questions: No field mappings available for entry-based save');
            return false;
        }
        
        $target_fields = $field_mappings['questions'];
        
        // Save directly to Formidable entry fields
        foreach ($questions as $index => $question) {
            if ($index < count($target_fields) && !empty(trim($question))) {
                $field_id = $target_fields[$index];
                $question_trimmed = trim($question);
                
                // Use the enhanced field save method
                $result = $this->save_single_question_to_formidable($entry_id, $field_id, $question_trimmed);
                
                if ($result) {
                    $saved_count++;
                    error_log("MKCG Enhanced Questions: ✅ Entry-based save: field {$field_id} = '{$question_trimmed}'");
                } else {
                    error_log("MKCG Enhanced Questions: ❌ Entry-based save failed for field {$field_id}");
                }
            }
        }
        
        $success = ($saved_count > 0);
        
        if ($success) {
            error_log("MKCG Enhanced Questions: ✅ Entry-based save completed: {$saved_count} questions saved");
        } else {
            error_log("MKCG Enhanced Questions: ❌ Entry-based save failed: no questions saved");
        }
        
        return $success;
    }
    
    /**
     * NEW: Save single question directly to Formidable field
     */
    private function save_single_question_to_formidable($entry_id, $field_id, $question) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'frm_item_metas';
        
        // Check if field already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$table} WHERE item_id = %d AND field_id = %d",
            $entry_id, $field_id
        ));
        
        if ($existing !== null) {
            // Update existing field
            $result = $wpdb->update(
                $table,
                ['meta_value' => $question],
                ['item_id' => $entry_id, 'field_id' => $field_id],
                ['%s'],
                ['%d', '%d']
            );
        } else {
            // Insert new field
            $result = $wpdb->insert(
                $table,
                [
                    'item_id' => $entry_id,
                    'field_id' => $field_id,
                    'meta_value' => $question
                ],
                ['%d', '%d', '%s']
            );
        }
        
        return $result !== false;
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
        
        // Pass data to JavaScript - UNIFIED NONCE STRATEGY
        wp_localize_script('mkcg-questions-generator', 'mkcg_questions_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce'),
            'security' => wp_create_nonce('mkcg_nonce')
        ]);
    }
}