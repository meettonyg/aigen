<?php
/**
 * MKCG Centralized Configuration
 * Single source of truth for all field mappings and data sources
 * UPDATED: Now uses Pods "guests" custom post type as single source of truth
 */

class MKCG_Config {
    
    /**
     * Get field mappings for all generators - PODS CENTRALIZED CONFIGURATION
     */
    public static function get_field_mappings() {
        return [
            // Topics - stored in Pods "guests" custom post fields
            'topics' => [
                'source' => 'pods',
                'fields' => [
                    'topic_1' => 'topic_1',
                    'topic_2' => 'topic_2',
                    'topic_3' => 'topic_3', 
                    'topic_4' => 'topic_4',
                    'topic_5' => 'topic_5'
                ]
            ],
            
            // Authority Hook Components - ALL FROM PODS FIELDS
            'authority_hook' => [
                'who' => [
                    'source' => 'pods',
                    'field' => 'guest_title' // Professional title from contact group
                ],
                'when' => [
                    'source' => 'pods',
                    'field' => 'hook_when'
                ],
                'what' => [
                    'source' => 'pods',
                    'field' => 'hook_what'
                ],
                'how' => [
                    'source' => 'pods',
                    'field' => 'hook_how'
                ],
                'where' => [
                    'source' => 'pods',
                    'field' => 'hook_where'
                ],
                'why' => [
                    'source' => 'pods',
                    'field' => 'hook_why'
                ]
            ],
            
            // Questions - stored in Pods "guests" custom post fields
            'questions' => [
                'source' => 'pods',
                'pattern' => 'question_{number}' // e.g., question_1, question_2, etc.
            ]
        ];
    }
    
    /**
     * Get data from centralized configuration using POST ID as primary key
     * UPDATED: Now uses Pods service as single source of truth
     */
    public static function load_data_for_post($post_id, $pods_service = null) {
        if (!$post_id) {
            return self::get_default_data();
        }
        
        // Create Pods service if not provided
        if (!$pods_service) {
            require_once dirname(__FILE__) . '/class-mkcg-pods-service.php';
            $pods_service = new MKCG_Pods_Service();
        }
        
        // Validate this is a guests post
        if (!$pods_service->is_guests_post($post_id)) {
            error_log('MKCG Config: Post ' . $post_id . ' is not a guests post type');
            return self::get_default_data();
        }
        
        // Load all data from Pods service
        $guest_data = $pods_service->get_guest_data($post_id);
        
        // Transform to expected format for backwards compatibility
        $data = [
            'post_id' => $post_id,
            'entry_id' => $pods_service->get_entry_id_from_post($post_id), // For backwards compatibility
            'entry_key' => '', // Legacy compatibility
            'has_entry' => $guest_data['has_data'],
            'form_field_values' => $guest_data['topics'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            'questions' => $guest_data['questions'],
            'contact' => $guest_data['contact'],
            'messaging' => $guest_data['messaging']
        ];
        
        error_log('MKCG Config: Data loaded from Pods service - Post ID: ' . $post_id . ', Has Data: ' . ($guest_data['has_data'] ? 'YES' : 'NO'));
        
        return $data;
    }
    
    /**
     * Load questions from Pods fields for Questions Generator
     */
    private static function load_questions_from_pods($post_id) {
        $questions = [];
        
        // Load all 25 questions from Pods fields
        for ($i = 1; $i <= 25; $i++) {
            $value = get_post_meta($post_id, "question_{$i}", true);
            if (!empty($value)) {
                $questions["question_{$i}"] = $value;
            }
        }
        
        return $questions;
    }
    
    /**
     * Get default data structure
     */
    public static function get_default_data() {
        return [
            'post_id' => 0,
            'entry_id' => 0,
            'entry_key' => '',
            'form_field_values' => [
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
            'messaging' => [],
            'has_entry' => false
        ];
    }
    
    /**
     * Save data using centralized configuration using POST ID as primary key
     * UPDATED: Now uses Pods service as single source of truth
     */
    public static function save_data_for_post($post_id, $data, $pods_service = null) {
        if (!$post_id) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        // Create Pods service if not provided
        if (!$pods_service) {
            require_once dirname(__FILE__) . '/class-mkcg-pods-service.php';
            $pods_service = new MKCG_Pods_Service();
        }
        
        // Validate this is a guests post
        if (!$pods_service->is_guests_post($post_id)) {
            return ['success' => false, 'message' => 'Post is not a guests post type'];
        }
        
        $saved_count = 0;
        $results = [];
        
        // Save topics using Pods service
        if (isset($data['topics'])) {
            $result = $pods_service->save_topics($post_id, $data['topics']);
            if ($result['success']) {
                $saved_count += $result['saved_count'];
                $results[] = 'Topics: ' . $result['message'];
            }
        }
        
        // Save authority hook components using Pods service
        if (isset($data['authority_hook'])) {
            $result = $pods_service->save_authority_hook_components($post_id, $data['authority_hook']);
            if ($result['success']) {
                $saved_count += $result['saved_count'];
                $results[] = 'Authority Hook: ' . $result['message'];
            }
        }
        
        // Save questions using Pods service
        if (isset($data['questions'])) {
            $result = $pods_service->save_questions($post_id, $data['questions']);
            if ($result['success']) {
                $saved_count += $result['saved_count'];
                $results[] = 'Questions: ' . $result['message'];
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? implode('; ', $results) : 'No data saved',
            'details' => $results
        ];
    }
    
    /**
     * Get JavaScript configuration for templates
     */
    public static function get_js_config() {
        $mappings = self::get_field_mappings();
        
        return [
            'fieldMappings' => $mappings,
            'ajaxActions' => [
                'save_topics' => 'mkcg_save_topics_data',
                'get_topics' => 'mkcg_get_topics_data',
                'save_authority_hook' => 'mkcg_save_authority_hook',
                'generate_topics' => 'mkcg_generate_topics',
                'save_questions' => 'mkcg_save_questions',
                'generate_questions' => 'mkcg_generate_questions'
            ]
        ];
    }
}
