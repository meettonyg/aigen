<?php
/**
 * MKCG Pods Service
 * 
 * Originally designed for Pods integration, but now uses WordPress post meta directly.
 * This service handles all data operations for the "guests" custom post type.
 * 
 * Note: The Pods plugin is NOT required - we use standard WordPress functions.
 * 
 * Single source of truth for all guest data
 */

if (!class_exists('MKCG_Pods_Service')) {

class MKCG_Pods_Service {
    
    private $post_type = 'guests';
    private $allow_any_post_type = true; // Allow saving to any post type
    

    /**
     * SIMPLIFIED: Get all data for a guest post
     */
    public function get_guest_data($post_id) {
        if (!$post_id) {
            return $this->get_default_data();
        }
        
        // Verify this is a guests post type (or allow any if flag is set)
        $post = get_post($post_id);
        if (!$post || (!$this->allow_any_post_type && $post->post_type !== $this->post_type)) {
            return $this->get_default_data();
        }
        
        return [
            'post_id' => $post_id,
            'has_data' => true,
            'topics' => $this->get_topics($post_id),
            'authority_hook_components' => $this->get_authority_hook_components($post_id),
            'impact_intro_components' => $this->get_impact_intro_components($post_id),
            'questions' => $this->get_questions($post_id),
            'contact' => $this->get_contact_info($post_id),
            'messaging' => $this->get_messaging_info($post_id)
        ];
    }
    
    /**
     * SIMPLIFIED: Get topics from Pods fields - direct approach
     */
    public function get_topics($post_id) {
        $topics = [];
        
        // REMOVED Pods API dependency - use post meta directly
        for ($i = 1; $i <= 5; $i++) {
            $field_name = "topic_{$i}";
            $topic = get_post_meta($post_id, $field_name, true);
            $topics[$field_name] = !empty($topic) ? $topic : '';
        }
        
        // Ensure we always return the proper structure
        $final_topics = [];
        for ($i = 1; $i <= 5; $i++) {
            $final_topics["topic_{$i}"] = isset($topics["topic_{$i}"]) ? $topics["topic_{$i}"] : '';
        }
        
        return $final_topics;
    }
    
    /**
     * ROOT FIX: Get authority hook components using the cleaned Authority Hook Service
     * This ensures unified data handling and eliminates default values at the source
     */
    public function get_authority_hook_components($post_id) {
        error_log('MKCG Pods Service: get_authority_hook_components() - Using Authority Hook Service');
        
        // ROOT FIX: Load the cleaned Authority Hook Service if not already loaded
        if (!isset($this->authority_hook_service)) {
            if (!class_exists('MKCG_Authority_Hook_Service')) {
                require_once dirname(__FILE__) . '/class-mkcg-authority-hook-service.php';
            }
            $this->authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        // ROOT FIX: Use the cleaned Authority Hook Service instead of our own logic
        $authority_hook_data = $this->authority_hook_service->get_authority_hook_data($post_id);
        
        if ($authority_hook_data && isset($authority_hook_data['components'])) {
            $components = $authority_hook_data['components'];
            
            // Add the complete hook
            $components['complete'] = $authority_hook_data['complete_hook'] ?? '';
            
            // Add backward compatibility fields that some templates might expect
            $components['where'] = '';
            $components['why'] = '';
            
            error_log('MKCG Pods Service: Using cleaned Authority Hook Service data: ' . json_encode($components));
            
            return $components;
        }
        
        // Fallback to empty structure (no defaults)
        error_log('MKCG Pods Service: Authority Hook Service returned no data, using empty structure');
        return $this->get_empty_authority_hook();
    }
    
    /**
     * ROOT FIX: Get impact intro components using the cleaned Impact Intro Service
     * This ensures unified data handling and eliminates default values at the source
     */
    public function get_impact_intro_components($post_id) {
        error_log('MKCG Pods Service: get_impact_intro_components() - Using Impact Intro Service');
        
        // ROOT FIX: Load the cleaned Impact Intro Service if not already loaded
        if (!isset($this->impact_intro_service)) {
            if (!class_exists('MKCG_Impact_Intro_Service')) {
                require_once dirname(__FILE__) . '/class-mkcg-impact-intro-service.php';
            }
            $this->impact_intro_service = new MKCG_Impact_Intro_Service();
        }
        
        // ROOT FIX: Use the cleaned Impact Intro Service instead of our own logic
        $impact_intro_data = $this->impact_intro_service->get_impact_intro_data($post_id);
        
        if ($impact_intro_data && isset($impact_intro_data['components'])) {
            $components = $impact_intro_data['components'];
            
            // Add the complete intro
            $components['complete'] = $impact_intro_data['complete_intro'] ?? '';
            
            error_log('MKCG Pods Service: Using cleaned Impact Intro Service data: ' . json_encode($components));
            
            return $components;
        }
        
        // Fallback to empty structure (no defaults)
        error_log('MKCG Pods Service: Impact Intro Service returned no data, using empty structure');
        return $this->get_empty_impact_intro();
    }
    
    /**
     * ROOT FIX: Get questions organized by topic (5 questions per topic)
     */
    public function get_questions($post_id) {
        $questions_by_topic = [];
        
        // Initialize empty structure for 5 topics
        for ($topic = 1; $topic <= 5; $topic++) {
            $questions_by_topic[$topic] = [];
        }
        
        // Get questions 1-25 from Pods fields and organize by topic
        // Topic 1: questions 1-5, Topic 2: questions 6-10, etc.
        for ($question_num = 1; $question_num <= 25; $question_num++) {
            $question_value = get_post_meta($post_id, "question_{$question_num}", true);
            
            if (!empty($question_value)) {
                // Calculate which topic this question belongs to
                $topic = ceil($question_num / 5);
                // Calculate position within the topic (1-5)
                $position = (($question_num - 1) % 5) + 1;
                
                $questions_by_topic[$topic][$position] = $question_value;
            }
        }
        
        return $questions_by_topic;
    }
    
    /**
     * Get contact information
     */
    public function get_contact_info($post_id) {
        return [
            'email' => get_post_meta($post_id, 'email', true),
            'first_name' => get_post_meta($post_id, 'first_name', true),
            'last_name' => get_post_meta($post_id, 'last_name', true),
            'full_name' => get_post_meta($post_id, 'full_name', true),
            'company' => get_post_meta($post_id, 'company', true),
            'guest_title' => get_post_meta($post_id, 'guest_title', true),
            'skype' => get_post_meta($post_id, 'skype', true)
        ];
    }
    
    /**
     * Get messaging information
     */
    public function get_messaging_info($post_id) {
        return [
            'biography' => get_post_meta($post_id, 'biography', true),
            'introduction' => get_post_meta($post_id, 'introduction', true),
            'tagline' => get_post_meta($post_id, 'tagline', true)
        ];
    }
    
    /**
     * SIMPLIFIED: Save topics to Pods fields
     */
    public function save_topics($post_id, $topics_data) {
        if (!$post_id || empty($topics_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $saved_count = 0;
        
        foreach ($topics_data as $topic_key => $topic_value) {
            if (!empty($topic_value)) {
                $result = update_post_meta($post_id, $topic_key, $topic_value);
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Topics saved successfully' : 'No topics saved'
        ];
    }
    
    /**
     * ROOT FIX: Save authority hook components using the cleaned Authority Hook Service
     */
    public function save_authority_hook_components($post_id, $hook_data) {
        if (!$post_id || empty($hook_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        error_log('MKCG Pods Service: save_authority_hook_components() - Using Authority Hook Service');
        
        // ROOT FIX: Load the cleaned Authority Hook Service if not already loaded
        if (!isset($this->authority_hook_service)) {
            if (!class_exists('MKCG_Authority_Hook_Service')) {
                require_once dirname(__FILE__) . '/class-mkcg-authority-hook-service.php';
            }
            $this->authority_hook_service = new MKCG_Authority_Hook_Service();
        }
        
        // ROOT FIX: Use the cleaned Authority Hook Service for saving
        $result = $this->authority_hook_service->save_authority_hook_data($post_id, $hook_data);
        
        error_log('MKCG Pods Service: Authority Hook Service save result: ' . json_encode($result));
        
        return $result;
    }
    
    /**
     * ROOT FIX: Save impact intro components using the cleaned Impact Intro Service
     */
    public function save_impact_intro_components($post_id, $intro_data) {
        if (!$post_id || empty($intro_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        error_log('MKCG Pods Service: save_impact_intro_components() - Using Impact Intro Service');
        
        // ROOT FIX: Load the cleaned Impact Intro Service if not already loaded
        if (!isset($this->impact_intro_service)) {
            if (!class_exists('MKCG_Impact_Intro_Service')) {
                require_once dirname(__FILE__) . '/class-mkcg-impact-intro-service.php';
            }
            $this->impact_intro_service = new MKCG_Impact_Intro_Service();
        }
        
        // ROOT FIX: Use the cleaned Impact Intro Service for saving
        $result = $this->impact_intro_service->save_impact_intro_data($post_id, $intro_data);
        
        error_log('MKCG Pods Service: Impact Intro Service save result: ' . json_encode($result));
        
        return $result;
    }
    
    /**
     * ROOT FIX: Save questions in flat format from JavaScript - Handle mkcg_question_X_Y format
     */
    public function save_questions($post_id, $questions_data) {
        if (!$post_id || empty($questions_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        error_log('MKCG Pods: === QUESTIONS SAVE START ===');
        error_log('MKCG Pods: Post ID: ' . $post_id);
        error_log('MKCG Pods: Questions data: ' . print_r($questions_data, true));
        
        $saved_count = 0;
        
        // ROOT FIX: Handle new flat format from JavaScript extraction
        // Input format: mkcg_question_1_1, mkcg_question_1_2, etc.
        // Output format: question_1, question_2, etc. (sequential)
        
        foreach ($questions_data as $question_key => $question_value) {
            if (!empty(trim($question_value))) {
                // Handle mkcg_question_X_Y format from extraction
                if (strpos($question_key, 'mkcg_question_') === 0) {
                    // Extract topic and position from mkcg_question_1_1
                    $key_parts = str_replace('mkcg_question_', '', $question_key);
                    $parts = explode('_', $key_parts);
                    
                    if (count($parts) == 2) {
                        $topic = intval($parts[0]);
                        $position = intval($parts[1]);
                        
                        // Convert to sequential numbering: topic 1 pos 1 = question 1, topic 1 pos 2 = question 2, etc.
                        $question_num = (($topic - 1) * 5) + $position;
                        $field_name = "question_{$question_num}";
                        
                        error_log("MKCG Pods: Converting {$question_key} to {$field_name}: {$question_value}");
                        
                        $result = update_post_meta($post_id, $field_name, trim($question_value));
                        if ($result !== false) {
                            $saved_count++;
                        }
                    }
                }
                // Handle legacy formats
                elseif (strpos($question_key, 'question_') === 0) {
                    // Direct question_X format
                    error_log("MKCG Pods: Direct format {$question_key}: {$question_value}");
                    $result = update_post_meta($post_id, $question_key, trim($question_value));
                    if ($result !== false) {
                        $saved_count++;
                    }
                }
            }
        }
        
        error_log('MKCG Pods: Questions saved count: ' . $saved_count);
        error_log('MKCG Pods: === QUESTIONS SAVE END ===');
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Questions saved successfully' : 'No questions saved'
        ];
    }
    
    /**
     * SIMPLIFIED: Save single field to Pods/post meta (for auto-save)
     */
    public function save_single_field($post_id, $field_name, $field_value) {
        if (!$post_id || empty($field_name)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        // Sanitize the field value
        $sanitized_value = sanitize_textarea_field($field_value);
        
        // Save to post meta
        $result = update_post_meta($post_id, $field_name, $sanitized_value);
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => 'Field saved successfully',
                'field_name' => $field_name,
                'field_value' => $sanitized_value
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save field'
            ];
        }
    }
    
    /**
     * Get post ID from entry ID (for backwards compatibility)
     */
    public function get_post_id_from_entry($entry_id) {
        if (!$entry_id) {
            return 0;
        }
        
        global $wpdb;
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        return $post_id ? intval($post_id) : 0;
    }
    
    /**
     * Get entry ID from post ID (for backwards compatibility)
     */
    public function get_entry_id_from_post($post_id) {
        if (!$post_id) {
            return 0;
        }
        
        // Check post meta first
        $entry_id = get_post_meta($post_id, '_frm_entry_id', true);
        if ($entry_id) {
            return intval($entry_id);
        }
        
        // Database query as fallback
        global $wpdb;
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
            $post_id
        ));
        
        return $entry_id ? intval($entry_id) : 0;
    }
    
    /**
     * Build complete authority hook from components
     */
    private function build_complete_authority_hook($who, $what, $when, $how, $where, $why) {
        // Create a comprehensive authority hook statement
        $hook = "I help {$who} {$what} when {$when} by showing them {$how}";
        
        if (!empty($where) && $where !== 'in their situation') {
            $hook .= " {$where}";
        }
        
        if (!empty($why) && $why !== 'because they deserve success') {
            $hook .= " {$why}";
        }
        
        $hook .= ".";
        
        return $hook;
    }
    
    /**
     * ROOT FIX: Get audience from taxonomy - Return properly formatted string for JavaScript parsing
     */
    private function get_audience_from_taxonomy($post_id) {
        if (!$post_id) {
            return '';
        }

        // Clear cache and get audience terms
        wp_cache_delete($post_id, 'audience_relationships');
        $audience_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);

        if (is_wp_error($audience_terms) || empty($audience_terms)) {
            error_log('MKCG Pods: No audience terms found for post ' . $post_id);
            return '';
        }

        error_log('MKCG Pods: Raw audience terms for post ' . $post_id . ': ' . print_r($audience_terms, true));

        // ROOT FIX: Return properly formatted string for JavaScript parsing
        // Format for natural language: "A and B" or "A, B, and C"
        $formatted_audience = '';
        if (count($audience_terms) === 1) {
            $formatted_audience = $audience_terms[0];
        } elseif (count($audience_terms) === 2) {
            $formatted_audience = $audience_terms[0] . ' and ' . $audience_terms[1];
        } else {
            $last = array_pop($audience_terms);
            $formatted_audience = implode(', ', $audience_terms) . ', and ' . $last;
        }

        error_log('MKCG Pods: Formatted audience string: "' . $formatted_audience . '"');
        return $formatted_audience;
    }
    
    /**
     * ROOT FIX: Get empty authority hook structure - NO DEFAULTS
     */
    private function get_empty_authority_hook() {
        return [
            'who' => '',
            'what' => '',
            'when' => '',
            'how' => '',
            'where' => '',
            'why' => '',
            'complete' => ''
        ];
    }
    
    /**
     * ROOT FIX: Get empty impact intro structure - NO DEFAULTS
     */
    private function get_empty_impact_intro() {
        return [
            'where' => '',
            'why' => '',
            'complete' => ''
        ];
    }
    
    /**
     * Check if components have only default values
     */
    private function hasOnlyDefaults($components) {
        $defaults = ['they need help', 'achieve their goals', 'through your method', 'in their situation', 'because they deserve success', 'your audience'];
        
        foreach ($components as $value) {
            if (!empty($value) && !in_array($value, $defaults)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * ROOT FIX: Get default data structure - EMPTY DEFAULTS ONLY
     */
    private function get_default_data() {
        return [
            'post_id' => 0,
            'has_data' => false,
            'topics' => [
                'topic_1' => '',
                'topic_2' => '',
                'topic_3' => '',
                'topic_4' => '',
                'topic_5' => ''
            ],
            'authority_hook_components' => $this->get_empty_authority_hook(),
            'questions' => [],
            'contact' => [],
            'messaging' => []
        ];
    }
    
    /**
     * Validate that post is guests post type (or any type if allowed)
     */
    public function is_guests_post($post_id) {
        if (!$post_id) {
            return false;
        }
        
        if ($this->allow_any_post_type) {
            return true; // Allow any post type
        }
        
        $post = get_post($post_id);
        return $post && $post->post_type === $this->post_type;
    }
    
    /**
     * Get all guests posts
     */
    public function get_all_guests($limit = 100) {
        $args = [
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        return get_posts($args);
    }
    
    /**
     * Get empty topics array - private helper method
     */
    private function get_empty_topics_array() {
        return [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
    }

} // End MKCG_Pods_Service class

} // End class_exists check
