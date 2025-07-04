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
     * SIMPLIFIED: Get authority hook components - direct field values
     */
    public function get_authority_hook_components($post_id) {
        $components = [];
        $defaults = $this->get_default_authority_hook();

        // WHO Component: Try audience taxonomy first, then guest_title
        $who_value = $this->get_audience_from_taxonomy($post_id);
        if (empty($who_value)) {
            $who_value = get_post_meta($post_id, 'guest_title', true);
        }
        $components['who'] = !empty($who_value) ? trim($who_value) : $defaults['who'];

        // Other Components: Pull directly from hook_ fields
        $components['what'] = get_post_meta($post_id, 'hook_what', true) ?: $defaults['what'];
        $components['when'] = get_post_meta($post_id, 'hook_when', true) ?: $defaults['when'];
        $components['how'] = get_post_meta($post_id, 'hook_how', true) ?: $defaults['how'];
        $components['where'] = get_post_meta($post_id, 'hook_where', true) ?: $defaults['where'];
        $components['why'] = get_post_meta($post_id, 'hook_why', true) ?: $defaults['why'];

        // Build complete authority hook
        $components['complete'] = $this->build_complete_authority_hook(
            $components['who'], 
            $components['what'], 
            $components['when'], 
            $components['how'],
            $components['where'],
            $components['why']
        );
        
        return $components;
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
     * SIMPLIFIED: Save authority hook components to Pods fields
     */
    public function save_authority_hook_components($post_id, $hook_data) {
        if (!$post_id || empty($hook_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $saved_count = 0;
        $field_mapping = [
            'when' => 'hook_when',
            'what' => 'hook_what', 
            'how' => 'hook_how',
            'where' => 'hook_where',
            'why' => 'hook_why'
        ];
        
        foreach ($hook_data as $component => $value) {
            if (isset($field_mapping[$component]) && !empty($value)) {
                $field_name = $field_mapping[$component];
                $result = update_post_meta($post_id, $field_name, $value);
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Authority hook saved successfully' : 'No authority hook saved'
        ];
    }
    
    /**
     * ROOT FIX: Save questions organized by topic to sequential Pods fields
     */
    public function save_questions($post_id, $questions_data) {
        if (!$post_id || empty($questions_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $saved_count = 0;
        
        // Handle both formats: topic-based and direct field mapping
        if (isset($questions_data['questions']) && is_array($questions_data['questions'])) {
            // Topic-based format: questions[topic][position] = value
            foreach ($questions_data['questions'] as $topic => $topic_questions) {
                if (is_array($topic_questions)) {
                    foreach ($topic_questions as $position => $question_value) {
                        if (!empty(trim($question_value))) {
                            // Calculate sequential question number
                            // Topic 1: questions 1-5, Topic 2: questions 6-10, etc.
                            $question_num = (($topic - 1) * 5) + $position;
                            $field_name = "question_{$question_num}";
                            
                            $result = update_post_meta($post_id, $field_name, trim($question_value));
                            if ($result !== false) {
                                $saved_count++;
                            }
                        }
                    }
                }
            }
        } else {
            // Direct field mapping format: question_1, question_2, etc.
            foreach ($questions_data as $question_key => $question_value) {
                if (!empty($question_value) && strpos($question_key, 'question_') === 0) {
                    $result = update_post_meta($post_id, $question_key, $question_value);
                    if ($result !== false) {
                        $saved_count++;
                    }
                }
            }
        }
        
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
     * SIMPLIFIED: Get audience from taxonomy
     */
    private function get_audience_from_taxonomy($post_id) {
        if (!$post_id) {
            return '';
        }

        // Clear cache and get audience terms
        wp_cache_delete($post_id, 'audience_relationships');
        $audience_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);

        if (is_wp_error($audience_terms) || empty($audience_terms)) {
            return '';
        }

        // Join multiple terms with a comma
        return implode(', ', $audience_terms);
    }
    
    /**
     * Get default authority hook values
     */
    private function get_default_authority_hook() {
        return [
            'who' => 'your audience',
            'what' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method',
            'where' => 'in their situation',
            'why' => 'because they deserve success'
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
     * Get default data structure
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
            'authority_hook_components' => [
                'who' => 'your audience',
                'what' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method',
                'where' => 'in their situation',
                'why' => 'because they deserve success',
                'complete' => 'I help your audience achieve their goals when they need help by showing them through your method in their situation because they deserve success.'
            ],
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
