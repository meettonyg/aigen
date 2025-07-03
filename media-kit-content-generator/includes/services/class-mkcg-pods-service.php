<?php
/**
 * MKCG Pods Service
 * Centralized service for reading data from Pods "guests" custom post type
 * Single source of truth for all guest data
 */

if (!class_exists('MKCG_Pods_Service')) {

class MKCG_Pods_Service {
    
    private $post_type = 'guests';
    
    /**
     * Get all data for a guest post
     */
    public function get_guest_data($post_id) {
        if (!$post_id) {
            return $this->get_default_data();
        }
        
        // Verify this is a guests post type
        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_type) {
            error_log("MKCG Pods Service: Post {$post_id} is not a guests post type");
            return $this->get_default_data();
        }
        
        $data = [
            'post_id' => $post_id,
            'has_data' => true,
            'topics' => $this->get_topics($post_id),
            'authority_hook_components' => $this->get_authority_hook_components($post_id),
            'questions' => $this->get_questions($post_id),
            'contact' => $this->get_contact_info($post_id),
            'messaging' => $this->get_messaging_info($post_id)
        ];
        
        error_log("MKCG Pods Service: Loaded guest data for post {$post_id}");
        return $data;
    }
    
    /**
     * Get topics from Pods fields - ENHANCED with multiple data sources
     */
    public function get_topics($post_id) {
        $topics = [];
        
        error_log("MKCG Pods Service: Loading topics for post {$post_id}");
        
        // Method 1: Try Pods API first (most reliable)
        if (function_exists('pods')) {
            $pod = pods('guests', $post_id);
            if ($pod && $pod->exists()) {
                for ($i = 1; $i <= 5; $i++) {
                    $field_name = "topic_{$i}";
                    $topic = $pod->field($field_name);
                    $topics[$field_name] = !empty($topic) ? $topic : '';
                    
                    if (!empty($topic)) {
                        error_log("MKCG Pods Service: Found {$field_name} via Pods API: {$topic}");
                    }
                }
                
                $filled_count = count(array_filter($topics));
                error_log("MKCG Pods Service: Loaded {$filled_count}/5 topics via Pods API for post {$post_id}");
                
                if ($filled_count > 0) {
                    return $topics;
                }
            }
        }
        
        // Method 2: Fallback to post meta (backup method)
        error_log("MKCG Pods Service: Trying post meta fallback for topics");
        for ($i = 1; $i <= 5; $i++) {
            $field_name = "topic_{$i}";
            $topic = get_post_meta($post_id, $field_name, true);
            $topics[$field_name] = !empty($topic) ? $topic : '';
            
            if (!empty($topic)) {
                error_log("MKCG Pods Service: Found {$field_name} via post meta: {$topic}");
            }
        }
        
        $filled_count = count(array_filter($topics));
        error_log("MKCG Pods Service: Final result - {$filled_count}/5 topics loaded for post {$post_id}");
        
        return $topics;
    }
    
    /**
     * Get authority hook components from Pods fields - ENHANCED with multiple data sources
     */
    public function get_authority_hook_components($post_id) {
        error_log("MKCG Pods Service: Loading authority hook components for post {$post_id}");
        
        $components = [];
        
        // Method 1: Try Pods API first (most reliable)
        if (function_exists('pods')) {
            $pod = pods('guests', $post_id);
            if ($pod && $pod->exists()) {
                error_log("MKCG Pods Service: Using Pods API for authority hook components");
                
                $when = $pod->field('hook_when') ?: 'they need help';
                $what = $pod->field('hook_what') ?: 'achieve their goals';
                $how = $pod->field('hook_how') ?: 'through your method';
                $where = $pod->field('hook_where') ?: 'in their situation';
                $why = $pod->field('hook_why') ?: 'because they deserve success';
                $who = $pod->field('guest_title') ?: 'your audience';
                
                // Log what we found
                error_log("MKCG Pods Service: Pods API results - who: {$who}, what: {$what}, when: {$when}, how: {$how}");
                
                $components = [
                    'who' => $who,
                    'what' => $what,
                    'when' => $when,
                    'how' => $how,
                    'where' => $where,
                    'why' => $why
                ];
                
                // Check if we got meaningful data
                $meaningful_count = 0;
                foreach ($components as $key => $value) {
                    if (!empty($value) && !in_array($value, ['they need help', 'achieve their goals', 'through your method', 'in their situation', 'because they deserve success', 'your audience'])) {
                        $meaningful_count++;
                    }
                }
                
                if ($meaningful_count > 0) {
                    error_log("MKCG Pods Service: Found {$meaningful_count} meaningful authority hook components via Pods API");
                } else {
                    error_log("MKCG Pods Service: No meaningful data via Pods API, trying post meta fallback");
                }
            }
        }
        
        // Method 2: Fallback to post meta if Pods didn't work or no meaningful data
        if (empty($components) || $this->hasOnlyDefaults($components)) {
            error_log("MKCG Pods Service: Using post meta fallback for authority hook components");
            
            $when = get_post_meta($post_id, 'hook_when', true) ?: 'they need help';
            $what = get_post_meta($post_id, 'hook_what', true) ?: 'achieve their goals';
            $how = get_post_meta($post_id, 'hook_how', true) ?: 'through your method';
            $where = get_post_meta($post_id, 'hook_where', true) ?: 'in their situation';
            $why = get_post_meta($post_id, 'hook_why', true) ?: 'because they deserve success';
            $who = get_post_meta($post_id, 'guest_title', true) ?: 'your audience';
            
            error_log("MKCG Pods Service: Post meta results - who: {$who}, what: {$what}, when: {$when}, how: {$how}");
            
            $components = [
                'who' => $who,
                'what' => $what,
                'when' => $when,
                'how' => $how,
                'where' => $where,
                'why' => $why
            ];
        }
        
        // Enhance WHO field if needed
        if (empty($components['who']) || $components['who'] === 'your audience') {
            // Try introduction field as fallback
            $intro = get_post_meta($post_id, 'introduction', true);
            if (!empty($intro)) {
                // Extract audience from introduction if possible
                $extracted_who = $this->extract_audience_from_intro($intro);
                if ($extracted_who !== 'your audience') {
                    $components['who'] = $extracted_who;
                    error_log("MKCG Pods Service: Enhanced WHO from introduction: {$extracted_who}");
                }
            }
        }
        
        // Build complete authority hook
        $complete = $this->build_complete_authority_hook(
            $components['who'], 
            $components['what'], 
            $components['when'], 
            $components['how'], 
            $components['where'], 
            $components['why']
        );
        
        $components['complete'] = $complete;
        
        error_log("MKCG Pods Service: Final authority hook components loaded for post {$post_id}");
        error_log("MKCG Pods Service: Complete hook: {$complete}");
        
        return $components;
    }
    
    /**
     * Get questions from Pods fields
     */
    public function get_questions($post_id) {
        $questions = [];
        
        // Get questions 1-25 from Pods fields
        for ($i = 1; $i <= 25; $i++) {
            $question = get_post_meta($post_id, "question_{$i}", true);
            if (!empty($question)) {
                $questions["question_{$i}"] = $question;
            }
        }
        
        error_log("MKCG Pods Service: Loaded " . count($questions) . " questions for post {$post_id}");
        return $questions;
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
     * Save topics to Pods fields
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
                    error_log("MKCG Pods Service: Saved {$topic_key} to post {$post_id}");
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
     * Save authority hook components to Pods fields
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
                    error_log("MKCG Pods Service: Saved {$component} to field {$field_name} on post {$post_id}");
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
     * Save questions to Pods fields
     */
    public function save_questions($post_id, $questions_data) {
        if (!$post_id || empty($questions_data)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $saved_count = 0;
        
        foreach ($questions_data as $question_key => $question_value) {
            if (!empty($question_value)) {
                $result = update_post_meta($post_id, $question_key, $question_value);
                if ($result !== false) {
                    $saved_count++;
                    error_log("MKCG Pods Service: Saved {$question_key} to post {$post_id}");
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
     * Extract audience from introduction text
     */
    private function extract_audience_from_intro($intro) {
        // Simple extraction - look for common patterns
        $patterns = [
            '/I help ([^.]+) (?:achieve|reach|get|find|overcome)/i',
            '/I work with ([^.]+) (?:to help|who want|who need)/i',
            '/I specialize in helping ([^.]+) (?:with|achieve|reach)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $intro, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return 'your audience'; // Default fallback
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
     * Validate that post is guests post type
     */
    public function is_guests_post($post_id) {
        if (!$post_id) {
            return false;
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
}

} // End class_exists check
