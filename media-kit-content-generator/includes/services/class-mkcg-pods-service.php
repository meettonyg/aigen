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
     * Get topics from Pods fields - ENHANCED with multiple data sources and comprehensive debugging
     */
    public function get_topics($post_id) {
        $topics = [];
        
        error_log("MKCG Pods Service: Loading topics for post {$post_id}");
        
        // ENHANCED DEBUG: Check all meta fields to see what exists
        $all_meta = get_post_meta($post_id);
        error_log("MKCG Pods Service: DEBUG - Total meta fields for post {$post_id}: " . count($all_meta));
        $sample_keys = array_slice(array_keys($all_meta), 0, 10);
        error_log("MKCG Pods Service: DEBUG - Sample meta keys: " . implode(', ', $sample_keys));
        
        // Check if any topic-related fields exist
        $topic_like_keys = array_filter(array_keys($all_meta), function($key) {
            return strpos(strtolower($key), 'topic') !== false;
        });
        error_log("MKCG Pods Service: DEBUG - Topic-like meta keys: " . implode(', ', $topic_like_keys));
        
        // CRITICAL DEBUG: Verify post exists and is correct type
        $post = get_post($post_id);
        if (!$post) {
            error_log("MKCG Pods Service: ERROR - Post {$post_id} does not exist!");
            return $this->get_empty_topics_array();
        }
        
        if ($post->post_type !== 'guests') {
            error_log("MKCG Pods Service: ERROR - Post {$post_id} is not 'guests' type, it's '{$post->post_type}'");
            return $this->get_empty_topics_array();
        }
        
        error_log("MKCG Pods Service: Confirmed post {$post_id} exists and is 'guests' type: '{$post->post_title}'");
        
        // Method 1: Try Pods API first (most reliable)
        if (function_exists('pods')) {
            error_log("MKCG Pods Service: Pods function available, attempting Pods API");
            $pod = pods('guests', $post_id);
            
            if ($pod && $pod->exists()) {
                error_log("MKCG Pods Service: Pods object created and exists for post {$post_id}");
                
                for ($i = 1; $i <= 5; $i++) {
                    $field_name = "topic_{$i}";
                    $topic = $pod->field($field_name);
                    $topics[$field_name] = !empty($topic) ? $topic : '';
                    
                    // Enhanced debugging for each field
                    error_log("MKCG Pods Service: Pods API field '{$field_name}' result: '" . ($topic ?: 'EMPTY') . "'");
                    
                    if (!empty($topic)) {
                        error_log("MKCG Pods Service: ✅ Found {$field_name} via Pods API: {$topic}");
                    } else {
                        error_log("MKCG Pods Service: ❌ Empty {$field_name} via Pods API");
                    }
                }
                
                $filled_count = count(array_filter($topics));
                error_log("MKCG Pods Service: Pods API results - {$filled_count}/5 topics have content");
                
                // Continue to fallback even if Pods API returns empty (field names might be different)
                
            } else {
                error_log("MKCG Pods Service: Pods object creation failed or doesn't exist for post {$post_id}");
            }
        } else {
            error_log("MKCG Pods Service: Pods function not available!");
        }
        
        // Method 2: Try post meta (comprehensive field name attempts)
        error_log("MKCG Pods Service: Trying post meta fallback for topics");
        
        // Get ALL meta fields to see what's available
        $all_meta = get_post_meta($post_id);
        error_log("MKCG Pods Service: All meta fields for post {$post_id}: " . json_encode(array_keys($all_meta)));
        
        // Try various field name patterns
        $field_patterns = [
            'topic_%d',      // topic_1, topic_2, etc.
            'field_%d',      // field_1, field_2, etc. 
            'topic%d',       // topic1, topic2, etc.
            'interview_topic_%d', // interview_topic_1, etc.
            'podcast_topic_%d'    // podcast_topic_1, etc.
        ];
        
        foreach ($field_patterns as $pattern) {
            error_log("MKCG Pods Service: Trying field pattern: {$pattern}");
            $pattern_found_data = false;
            
            for ($i = 1; $i <= 5; $i++) {
                $field_name = sprintf($pattern, $i);
                $topic = get_post_meta($post_id, $field_name, true);
                
                if (!empty($topic)) {
                    $pattern_found_data = true;
                    $topics["topic_{$i}"] = $topic; // Standardize to topic_X format
                    error_log("MKCG Pods Service: ✅ Found topic via meta '{$field_name}': {$topic}");
                } else {
                    error_log("MKCG Pods Service: Empty meta field '{$field_name}'");
                }
            }
            
            if ($pattern_found_data) {
                error_log("MKCG Pods Service: Found data with pattern '{$pattern}', using this pattern");
                break;
            }
        }
        
        // Method 3: Check for Formidable field IDs (if connected via entry)
        error_log("MKCG Pods Service: Checking for Formidable field connections");
        $entry_id = $this->get_entry_id_from_post($post_id);
        
        if ($entry_id > 0) {
            error_log("MKCG Pods Service: Found entry ID {$entry_id} for post {$post_id}");
            
            // Try common Formidable field IDs for topics
            $formidable_field_ids = [8498, 8499, 8500, 8501, 8502]; // Based on template field IDs
            
            global $wpdb;
            for ($i = 0; $i < 5; $i++) {
                $field_id = $formidable_field_ids[$i];
                $value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                    $entry_id, $field_id
                ));
                
                if (!empty($value)) {
                    $topics["topic_" . ($i + 1)] = $value;
                    error_log("MKCG Pods Service: ✅ Found topic via Formidable field {$field_id}: {$value}");
                } else {
                    error_log("MKCG Pods Service: Empty Formidable field {$field_id}");
                }
            }
        } else {
            error_log("MKCG Pods Service: No entry ID found for post {$post_id}");
        }
        
        // Final result
        $filled_count = count(array_filter($topics));
        error_log("MKCG Pods Service: FINAL RESULT - {$filled_count}/5 topics loaded for post {$post_id}");
        error_log("MKCG Pods Service: Final topics array: " . json_encode($topics));
        
        // Ensure we always return the proper structure
        $final_topics = [];
        for ($i = 1; $i <= 5; $i++) {
            $final_topics["topic_{$i}"] = isset($topics["topic_{$i}"]) ? $topics["topic_{$i}"] : '';
        }
        
        return $final_topics;
    }
    
    /**
     * Get authority hook components from Pods fields - ENHANCED with multiple data sources
     */
    public function get_authority_hook_components($post_id) {
        error_log("MKCG Pods Service: Loading authority hook components for post {$post_id}");
        
        // ENHANCED DEBUG: Check authority hook related fields
        $all_meta = get_post_meta($post_id);
        $auth_like_keys = array_filter(array_keys($all_meta), function($key) {
            return strpos(strtolower($key), 'hook') !== false || 
                   strpos(strtolower($key), 'guest') !== false ||
                   strpos(strtolower($key), 'title') !== false;
        });
        error_log("MKCG Pods Service: DEBUG - Authority/hook-like meta keys: " . implode(', ', $auth_like_keys));
        
        // Check specific expected fields
        $expected_auth_fields = ['guest_title', 'hook_when', 'hook_what', 'hook_how', 'hook_where', 'hook_why'];
        foreach ($expected_auth_fields as $field) {
            $value = get_post_meta($post_id, $field, true);
            error_log("MKCG Pods Service: DEBUG - Direct check {$field}: '" . substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '') . "'");
        }
        
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
                
                // FIXED: Get WHO from audience taxonomy instead of guest_title
                $who = $this->get_audience_from_taxonomy($post_id) ?: 'your audience';
                
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
            
            // FIXED: Get WHO from audience taxonomy instead of guest_title meta
            $who = $this->get_audience_from_taxonomy($post_id) ?: 'your audience';
            
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
     * Get audience from taxonomy - ADDED for WHO component
     */
    private function get_audience_from_taxonomy($post_id) {
        if (!$post_id) {
            return '';
        }
        
        error_log("MKCG Pods Service: Getting audience from taxonomy for post {$post_id}");
        
        // Get audience taxonomy terms for this post
        $audience_terms = wp_get_post_terms($post_id, 'audience', ['fields' => 'names']);
        
        if (is_wp_error($audience_terms)) {
            error_log("MKCG Pods Service: Error getting audience terms: " . $audience_terms->get_error_message());
            return '';
        }
        
        if (!empty($audience_terms)) {
            // Use the first audience term as the WHO
            $audience = $audience_terms[0];
            error_log("MKCG Pods Service: Found audience from taxonomy: {$audience}");
            return $audience;
        }
        
        // Fallback: Try Pods API for audience field
        if (function_exists('pods')) {
            $pod = pods('guests', $post_id);
            if ($pod && $pod->exists()) {
                $audience_field = $pod->field('audience');
                if (!empty($audience_field)) {
                    error_log("MKCG Pods Service: Found audience from Pods field: {$audience_field}");
                    return $audience_field;
                }
            }
        }
        
        // Final fallback: Check if there's an audience meta field
        $audience_meta = get_post_meta($post_id, 'audience', true);
        if (!empty($audience_meta)) {
            error_log("MKCG Pods Service: Found audience from meta field: {$audience_meta}");
            return $audience_meta;
        }
        
        error_log("MKCG Pods Service: No audience found for post {$post_id}");
        return '';
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
