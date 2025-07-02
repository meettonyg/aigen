<?php
/**
 * MKCG Centralized Configuration
 * Single source of truth for all field mappings and data sources
 */

class MKCG_Config {
    
    /**
     * Get field mappings for all generators - CENTRALIZED CONFIGURATION
     */
    public static function get_field_mappings() {
        return [
            // Topics - stored in CUSTOM POST META
            'topics' => [
                'source' => 'post_meta',
                'fields' => [
                    'topic_1' => 'mkcg_topic_1',
                    'topic_2' => 'mkcg_topic_2',
                    'topic_3' => 'mkcg_topic_3', 
                    'topic_4' => 'mkcg_topic_4',
                    'topic_5' => 'mkcg_topic_5'
                ]
            ],
            
            // Authority Hook Components - HYBRID STORAGE
            'authority_hook' => [
                // WHO field comes from CUSTOM POST META
                'who' => [
                    'source' => 'post_meta',
                    'key' => 'mkcg_who'
                ],
                // RESULT, WHEN, HOW come from FORMIDABLE FIELDS
                'result' => [
                    'source' => 'formidable',
                    'field_id' => '10297'
                ],
                'when' => [
                    'source' => 'formidable',
                    'field_id' => '10387'
                ],
                'how' => [
                    'source' => 'formidable',
                    'field_id' => '10298'
                ],
                'complete' => [
                    'source' => 'formidable',
                    'field_id' => '10358'
                ]
            ],
            
            // Questions - stored in CUSTOM POST META
            'questions' => [
                'source' => 'post_meta',
                'pattern' => 'mkcg_question_{topic}_{question}' // e.g., mkcg_question_1_1
            ]
        ];
    }
    
    /**
     * Get data from centralized configuration
     */
    public static function load_data_for_entry($entry_id, $formidable_service) {
        if (!$entry_id || !$formidable_service) {
            return self::get_default_data();
        }
        
        // Get associated post ID
        $post_id = $formidable_service->get_post_id_from_entry($entry_id);
        if (!$post_id) {
            error_log('MKCG Config: No associated post found for entry ' . $entry_id);
            return self::get_default_data();
        }
        
        $mappings = self::get_field_mappings();
        $data = self::get_default_data();
        
        // Load topics from post meta
        foreach ($mappings['topics']['fields'] as $topic_key => $meta_key) {
            $value = get_post_meta($post_id, $meta_key, true);
            if (!empty($value)) {
                $data['form_field_values'][$topic_key] = $value;
                error_log("MKCG Config: Loaded {$topic_key} from post meta: {$value}");
            }
        }
        
        // Load authority hook components (hybrid sources)
        foreach ($mappings['authority_hook'] as $component => $config) {
            if ($config['source'] === 'post_meta') {
                // Load from custom post meta (WHO field)\n                $value = get_post_meta($post_id, $config['key'], true);
                if (!empty($value)) {
                    $data['authority_hook_components'][$component] = $value;
                    error_log("MKCG Config: Loaded {$component} from post meta: {$value}");
                }
            } elseif ($config['source'] === 'formidable') {
                // Load from Formidable field (RESULT, WHEN, HOW, COMPLETE)
                $value = $formidable_service->get_field_value($entry_id, $config['field_id']);
                if (!empty($value)) {
                    $data['authority_hook_components'][$component] = $value;
                    error_log("MKCG Config: Loaded {$component} from Formidable field {$config['field_id']}: {$value}");
                }
            }
        }
        
        // Load questions from post meta if needed
        $data['questions'] = self::load_questions_from_post_meta($post_id);
        
        // Build complete authority hook if we have components
        $components = $data['authority_hook_components'];
        if (!empty($components['who']) && !empty($components['result']) && 
            !empty($components['when']) && !empty($components['how'])) {
            
            $complete_hook = sprintf(
                'I help %s %s when %s %s.',
                $components['who'],
                $components['result'], 
                $components['when'],
                $components['how']
            );
            $data['authority_hook_components']['complete'] = $complete_hook;
            error_log('MKCG Config: Built complete authority hook: ' . $complete_hook);
        }
        
        // Mark as having data if we loaded anything meaningful
        $has_topics = !empty(array_filter($data['form_field_values']));
        $has_auth = !empty($components['who']) || !empty($components['result']);
        $data['has_entry'] = $has_topics || $has_auth;
        
        error_log('MKCG Config: Data loading complete - Topics: ' . ($has_topics ? 'YES' : 'NO') . ', Auth: ' . ($has_auth ? 'YES' : 'NO'));
        
        return $data;
    }
    
    /**
     * Load questions from post meta for Questions Generator
     */
    private static function load_questions_from_post_meta($post_id) {
        $questions = [];
        
        for ($topic = 1; $topic <= 5; $topic++) {
            $topic_questions = [];
            for ($q = 1; $q <= 5; $q++) {
                $meta_key = "mkcg_question_{$topic}_{$q}";
                $value = get_post_meta($post_id, $meta_key, true);
                if (!empty($value)) {
                    $topic_questions[$q] = $value;
                }
            }
            if (!empty($topic_questions)) {
                $questions[$topic] = $topic_questions;
            }
        }
        
        return $questions;
    }
    
    /**
     * Get default data structure
     */
    public static function get_default_data() {
        return [
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
                'result' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method',
                'complete' => 'I help your audience achieve their goals when they need help through your method.'
            ],
            'questions' => [],
            'has_entry' => false
        ];
    }
    
    /**
     * Save data using centralized configuration
     */
    public static function save_data_for_entry($entry_id, $data, $formidable_service) {
        if (!$entry_id || !$formidable_service) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $post_id = $formidable_service->get_post_id_from_entry($entry_id);
        if (!$post_id) {
            return ['success' => false, 'message' => 'No associated post found'];
        }
        
        $mappings = self::get_field_mappings();
        $saved_count = 0;
        
        // Save topics to post meta
        if (isset($data['topics'])) {
            foreach ($data['topics'] as $topic_key => $topic_value) {
                if (isset($mappings['topics']['fields'][$topic_key]) && !empty($topic_value)) {
                    $meta_key = $mappings['topics']['fields'][$topic_key];
                    $result = update_post_meta($post_id, $meta_key, $topic_value);
                    if ($result !== false) {
                        $saved_count++;
                        error_log("MKCG Config: Saved {$topic_key} to post meta {$meta_key}");
                    }
                }
            }
        }
        
        // Save authority hook components (hybrid approach)
        if (isset($data['authority_hook'])) {
            foreach ($data['authority_hook'] as $component => $value) {
                if (isset($mappings['authority_hook'][$component]) && !empty($value)) {
                    $config = $mappings['authority_hook'][$component];
                    
                    if ($config['source'] === 'post_meta') {
                        // Save WHO to post meta
                        $result = update_post_meta($post_id, $config['key'], $value);
                        if ($result !== false) {
                            $saved_count++;
                            error_log("MKCG Config: Saved {$component} to post meta {$config['key']}");
                        }
                    } elseif ($config['source'] === 'formidable') {
                        // Save RESULT, WHEN, HOW to Formidable
                        $result = $formidable_service->save_entry_data($entry_id, [$config['field_id'] => $value]);
                        if ($result['success']) {
                            $saved_count++;
                            error_log("MKCG Config: Saved {$component} to Formidable field {$config['field_id']}");
                        }
                    }
                }
            }
        }
        
        return [
            'success' => $saved_count > 0,
            'saved_count' => $saved_count,
            'message' => $saved_count > 0 ? 'Data saved successfully' : 'No data saved'
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
