    /**
     * UNIFIED DUAL-SAVE: Save topics and authority hook data to Formidable entry fields
     * Uses centralized field mappings for correct Formidable field assignment
     * 
     * @param int $entry_id Formidable entry ID
     * @param array $topics_data Topics data (topic_1 through topic_5)
     * @param array $authority_hook_data Authority hook components (who, result, when, how, complete)
     * @return array Save result with success status, saved fields, and any errors
     */
    public function save_topics_and_authority_hook_to_formidable($entry_id, $topics_data, $authority_hook_data) {
        if (!$entry_id) {
            return [
                'success' => false,
                'errors' => ['No entry ID provided'],
                'saved_fields' => []
            ];
        }
        
        error_log('MKCG Unified Formidable Save: Starting save for entry ' . $entry_id);
        
        $saved_fields = [];
        $errors = [];
        $config = MKCG_Config::get_field_mappings();
        
        try {
            // Validate entry exists first
            if (!$this->validate_entry_exists($entry_id)) {
                return [
                    'success' => false,
                    'errors' => ['Entry ID ' . $entry_id . ' does not exist'],
                    'saved_fields' => []
                ];
            }
            
            // Save all 5 topics to Formidable fields (8498-8502)
            if (!empty($topics_data) && isset($config['topics']['fields'])) {
                foreach ($config['topics']['fields'] as $topic_key => $field_id) {
                    if (isset($topics_data[$topic_key]) && !empty(trim($topics_data[$topic_key]))) {
                        $result = $this->save_to_formidable_field($entry_id, $field_id, trim($topics_data[$topic_key]));
                        
                        if ($result) {
                            $saved_fields['topics'][$topic_key] = $field_id;
                            error_log("MKCG Unified Formidable Save: ✅ Saved {$topic_key} to field {$field_id}");
                        } else {
                            $errors[] = "Failed to save {$topic_key} to Formidable field {$field_id}";
                            error_log("MKCG Unified Formidable Save: ❌ Failed {$topic_key} to field {$field_id}");
                        }
                    }
                }
            }
            
            // Save Authority Hook components to Formidable fields (10296, 10297, 10387, 10298, 10358)
            if (!empty($authority_hook_data) && isset($config['authority_hook']['fields'])) {
                foreach ($config['authority_hook']['fields'] as $component => $field_id) {
                    if (isset($authority_hook_data[$component]) && !empty(trim($authority_hook_data[$component]))) {
                        $result = $this->save_to_formidable_field($entry_id, $field_id, trim($authority_hook_data[$component]));
                        
                        if ($result) {
                            $saved_fields['authority_hook'][$component] = $field_id;
                            error_log("MKCG Unified Formidable Save: ✅ Saved authority_{$component} to field {$field_id}");
                        } else {
                            $errors[] = "Failed to save authority_{$component} to Formidable field {$field_id}";
                            error_log("MKCG Unified Formidable Save: ❌ Failed authority_{$component} to field {$field_id}");
                        }
                    }
                }
            }
            
            // Update entry modification timestamp
            $this->update_entry_timestamp($entry_id);
            
            $success = !empty($saved_fields);
            $total_saved = count($saved_fields, COUNT_RECURSIVE) - count($saved_fields);
            
            error_log("MKCG Unified Formidable Save: Completed - success: " . ($success ? 'true' : 'false') . ", saved: {$total_saved} fields");
            
            return [
                'success' => $success,
                'saved_fields' => $saved_fields,
                'errors' => $errors,
                'entry_id' => $entry_id,
                'total_saved' => $total_saved
            ];
            
        } catch (Exception $e) {
            error_log('MKCG Unified Formidable Save: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['Exception: ' . $e->getMessage()],
                'saved_fields' => $saved_fields
            ];
        }
    }
    
    /**
     * ENHANCED 4-STRATEGY ENTRY LOOKUP: Get entry ID from post ID with comprehensive fallback strategies
     * Strategy 1: Check post_meta '_frm_entry_id'
     * Strategy 2: Check post_meta 'frm_entry_id' 
     * Strategy 3: Query frm_items table by post_id
     * Strategy 4: Create new entry if none found
     * 
     * @param int $post_id WordPress post ID
     * @return int|null Entry ID or null if all strategies fail
     */
    public function get_entry_id_from_post_enhanced($post_id) {
        if (!$post_id) {
            error_log('MKCG 4-Strategy Lookup: No post ID provided');
            return null;
        }
        
        global $wpdb;
        error_log('MKCG 4-Strategy Lookup: Starting comprehensive lookup for post ' . $post_id);
        
        // Strategy 1: Check post_meta '_frm_entry_id'
        $entry_id = get_post_meta($post_id, '_frm_entry_id', true);
        if ($entry_id && is_numeric($entry_id)) {
            error_log('MKCG 4-Strategy Lookup: ✅ SUCCESS via Strategy 1 (_frm_entry_id): ' . $entry_id);
            return intval($entry_id);
        }
        
        // Strategy 2: Check post_meta 'frm_entry_id'
        $entry_id = get_post_meta($post_id, 'frm_entry_id', true);
        if ($entry_id && is_numeric($entry_id)) {
            error_log('MKCG 4-Strategy Lookup: ✅ SUCCESS via Strategy 2 (frm_entry_id): ' . $entry_id);
            // Save as backup for faster future lookups
            update_post_meta($post_id, '_frm_entry_id', $entry_id);
            return intval($entry_id);
        }
        
        // Strategy 3: Query frm_items table by post_id
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE post_id = %d",
            $post_id
        ));
        
        if ($entry_id) {
            error_log('MKCG 4-Strategy Lookup: ✅ SUCCESS via Strategy 3 (frm_items lookup): ' . $entry_id);
            // Save for future lookups
            update_post_meta($post_id, '_frm_entry_id', $entry_id);
            return intval($entry_id);
        }
        
        // Strategy 4: Create new entry if none found
        error_log('MKCG 4-Strategy Lookup: No existing entry found, executing Strategy 4 - create new entry for post ' . $post_id);
        
        try {
            $config = MKCG_Config::get_system_config();
            $form_id = $config['form_id']; // Default to form 515
            
            // Create new Formidable entry
            $new_entry_data = [
                'form_id' => $form_id,
                'post_id' => $post_id,
                'item_key' => wp_generate_password(10, false),
                'name' => 'Auto-created for post ' . $post_id,
                'description' => 'Created by unified dual-save system',
                'created_date' => current_time('mysql')
            ];
            
            $result = $wpdb->insert(
                $wpdb->prefix . 'frm_items',
                $new_entry_data,
                ['%d', '%d', '%s', '%s', '%s', '%s']
            );
            
            if ($result !== false) {
                $new_entry_id = $wpdb->insert_id;
                error_log('MKCG 4-Strategy Lookup: ✅ SUCCESS via Strategy 4 (new entry created): ' . $new_entry_id);
                
                // Save association for future lookups
                update_post_meta($post_id, '_frm_entry_id', $new_entry_id);
                update_post_meta($post_id, '_mkcg_entry_auto_created', time());
                
                return intval($new_entry_id);
            } else {
                error_log('MKCG 4-Strategy Lookup: ❌ Strategy 4 FAILED - could not create new entry: ' . $wpdb->last_error);
            }
            
        } catch (Exception $e) {
            error_log('MKCG 4-Strategy Lookup: ❌ Strategy 4 EXCEPTION: ' . $e->getMessage());
        }
        
        error_log('MKCG 4-Strategy Lookup: ❌ ALL STRATEGIES FAILED for post ' . $post_id);
        return null;
    }
    
    /**
     * UNIFIED DUAL-SAVE ORCHESTRATOR: Save to both WordPress post_meta and Formidable entry
     * Orchestrates the complete dual-save operation with comprehensive error handling
     * 
     * @param int $post_id WordPress post ID
     * @param array $topics_data Topics data (topic_1 through topic_5)
     * @param array $authority_hook_data Authority hook components
     * @return array Comprehensive status for both save locations
     */
    public function save_to_both_locations($post_id, $topics_data, $authority_hook_data) {
        error_log('MKCG Dual-Save Orchestrator: Starting unified save for post ' . $post_id);
        
        $result = [
            'success' => false,
            'post_meta' => [
                'attempted' => false,
                'success' => false,
                'saved_fields' => [],
                'errors' => []
            ],
            'formidable' => [
                'attempted' => false,
                'success' => false,
                'saved_fields' => [],
                'errors' => [],
                'entry_id' => null
            ],
            'overall_errors' => [],
            'summary' => [
                'post_meta_fields_saved' => 0,
                'formidable_fields_saved' => 0,
                'total_fields_attempted' => 0
            ],
            'timestamp' => time()
        ];
        
        if (!$post_id) {
            $result['overall_errors'][] = 'No post ID provided';
            return $result;
        }
        
        // SAVE 1: WordPress Post Meta (Primary Save Location)
        error_log('MKCG Dual-Save: Starting WordPress post meta save...');
        $result['post_meta']['attempted'] = true;
        
        try {
            $post_meta_result = $this->save_topics_and_authority_hook_to_post($post_id, $topics_data, $authority_hook_data);
            
            $result['post_meta']['success'] = $post_meta_result['success'];
            $result['post_meta']['saved_fields'] = $post_meta_result['saved_fields'] ?? [];
            $result['post_meta']['errors'] = $post_meta_result['errors'] ?? [];
            $result['summary']['post_meta_fields_saved'] = $post_meta_result['total_saved'] ?? 0;
            
            if ($post_meta_result['success']) {
                error_log('MKCG Dual-Save: ✅ WordPress post meta save SUCCESSFUL');
            } else {
                error_log('MKCG Dual-Save: ❌ WordPress post meta save FAILED');
                $result['overall_errors'][] = 'WordPress post meta save failed';
            }
            
        } catch (Exception $e) {
            error_log('MKCG Dual-Save: ❌ WordPress post meta save EXCEPTION: ' . $e->getMessage());
            $result['post_meta']['errors'][] = 'Exception: ' . $e->getMessage();
            $result['overall_errors'][] = 'WordPress post meta exception';
        }
        
        // SAVE 2: Formidable Entry Fields (Secondary Save Location)
        error_log('MKCG Dual-Save: Starting Formidable entry save...');
        $result['formidable']['attempted'] = true;
        
        // Get entry ID using 4-strategy lookup
        $entry_id = $this->get_entry_id_from_post_enhanced($post_id);
        
        if ($entry_id) {
            $result['formidable']['entry_id'] = $entry_id;
            error_log('MKCG Dual-Save: Entry ID resolved: ' . $entry_id);
            
            try {
                $formidable_result = $this->save_topics_and_authority_hook_to_formidable($entry_id, $topics_data, $authority_hook_data);
                
                $result['formidable']['success'] = $formidable_result['success'];
                $result['formidable']['saved_fields'] = $formidable_result['saved_fields'] ?? [];
                $result['formidable']['errors'] = $formidable_result['errors'] ?? [];
                $result['summary']['formidable_fields_saved'] = $formidable_result['total_saved'] ?? 0;
                
                if ($formidable_result['success']) {
                    error_log('MKCG Dual-Save: ✅ Formidable entry save SUCCESSFUL');
                } else {
                    error_log('MKCG Dual-Save: ❌ Formidable entry save FAILED');
                    $result['overall_errors'][] = 'Formidable entry save failed';
                }
                
            } catch (Exception $e) {
                error_log('MKCG Dual-Save: ❌ Formidable entry save EXCEPTION: ' . $e->getMessage());
                $result['formidable']['errors'][] = 'Exception: ' . $e->getMessage();
                $result['overall_errors'][] = 'Formidable entry exception';
            }
            
        } else {
            error_log('MKCG Dual-Save: ⚠️ Could not resolve entry ID - Formidable save skipped');
            $result['formidable']['errors'][] = 'Could not resolve entry ID for post ' . $post_id;
            $result['overall_errors'][] = 'Entry ID resolution failed';
        }
        
        // Determine overall success (graceful degradation - succeeds if at least post meta saves)
        $result['success'] = $result['post_meta']['success'];
        
        // Enhanced success criteria: both saves successful = excellent, post meta only = acceptable
        if ($result['post_meta']['success'] && $result['formidable']['success']) {
            $status = 'excellent';
            error_log('MKCG Dual-Save: ✅ EXCELLENT - Both saves successful');
        } elseif ($result['post_meta']['success']) {
            $status = 'acceptable';
            error_log('MKCG Dual-Save: ⚠️ ACCEPTABLE - WordPress save successful, Formidable partial/failed');
        } else {
            $status = 'failed';
            error_log('MKCG Dual-Save: ❌ FAILED - Primary WordPress save failed');
        }
        
        $result['status'] = $status;
        $result['summary']['total_fields_attempted'] = count($topics_data) + count($authority_hook_data);
        
        // Save operation summary for debugging and monitoring
        update_post_meta($post_id, '_mkcg_last_dual_save', [
            'timestamp' => time(),
            'status' => $status,
            'post_meta_success' => $result['post_meta']['success'],
            'formidable_success' => $result['formidable']['success'],
            'entry_id' => $entry_id,
            'post_meta_fields_saved' => $result['summary']['post_meta_fields_saved'],
            'formidable_fields_saved' => $result['summary']['formidable_fields_saved'],
            'overall_errors' => $result['overall_errors']
        ]);
        
        return $result;
    }
    
    /**
     * HELPER: Validate that a Formidable entry exists
     * @param int $entry_id Entry ID to validate
     * @return bool True if entry exists
     */
    private function validate_entry_exists($entry_id) {
        global $wpdb;
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}frm_items WHERE id = %d",
            $entry_id
        ));
        
        return !empty($exists);
    }
    
    /**
     * HELPER: Update Formidable entry timestamp
     * @param int $entry_id Entry ID to update
     */
    private function update_entry_timestamp($entry_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'frm_items',
            ['updated_date' => current_time('mysql')],
            ['id' => $entry_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * HELPER: Check if string is serialized data
     * @param string $data Data to check
     * @return bool True if serialized
     */
    private function is_serialized($data) {
        // Basic check for serialized data patterns
        if (!is_string($data)) {
            return false;
        }
        
        $data = trim($data);
        
        if ('N;' === $data) {
            return true;
        }
        
        if (strlen($data) < 4) {
            return false;
        }
        
        if (':' !== $data[1]) {
            return false;
        }
        
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        
        $token = $data[0];
        switch ($token) {
            case 's':
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
                // No break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = substr($data, 2, -1);
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$/", $data);
        }
        
        return false;
    }
    
    /**
     * HELPER: Emergency string extraction from malformed data
     * @param string $data Malformed serialized data
     * @param string|null $field_id Field ID for logging
     * @return string|null Extracted string or null
     */
    private function emergency_string_extraction($data, $field_id = null) {
        // Extract quoted strings
        if (preg_match('/"([^"]{3,})"/', $data, $matches)) {
            $extracted = trim($matches[1]);
            if ($field_id) {
                error_log("MKCG Emergency Extraction: Field {$field_id} - Found quoted string: '{$extracted}'");
            }
            return $extracted;
        }
        
        // Extract text after colons (common in serialized data)
        if (preg_match('/:(\w+)/', $data, $matches)) {
            $extracted = trim($matches[1]);
            if (strlen($extracted) > 3) {
                if ($field_id) {
                    error_log("MKCG Emergency Extraction: Field {$field_id} - Found text after colon: '{$extracted}'");
                }
                return $extracted;
            }
        }
        
        return null;
    }
    
    /**
     * HELPER: Repair encoding issues in serialized data
     * @param string $data Serialized data with encoding issues
     * @param string|null $field_id Field ID for logging
     * @return mixed|false Repaired data or false
     */
    private function repair_encoding_issues($data, $field_id = null) {
        try {
            // Try to fix UTF-8 encoding issues
            $fixed_data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            
            if ($fixed_data !== $data) {
                if ($field_id) {
                    error_log("MKCG Encoding Repair: Field {$field_id} - Fixed encoding issues");
                }
                
                $result = @unserialize($fixed_data);
                if ($result !== false) {
                    return $result;
                }
            }
            
        } catch (Exception $e) {
            if ($field_id) {
                error_log("MKCG Encoding Repair: Field {$field_id} - Exception: " . $e->getMessage());
            }
        }
        
        return false;
    }
    
    /**
     * HELPER: Repair serialization structure issues
     * @param string $data Malformed serialized data
     * @param string|null $field_id Field ID for logging
     * @return mixed|false Repaired data or false
     */
    private function repair_serialization_structure($data, $field_id = null) {
        try {
            // Try to fix common structure issues
            $patterns = [
                '/^a:(\d+):\{(.+)\}$/' => 'array structure',
                '/^s:(\d+):"(.+)";$/' => 'string structure',
                '/^i:(\d+);$/' => 'integer structure'
            ];
            
            foreach ($patterns as $pattern => $description) {
                if (preg_match($pattern, $data, $matches)) {
                    if ($field_id) {
                        error_log("MKCG Structure Repair: Field {$field_id} - Detected {$description}");
                    }
                    
                    // Try to rebuild the structure
                    $rebuilt = $this->rebuild_serialized_structure($matches, $description);
                    if ($rebuilt !== false) {
                        $result = @unserialize($rebuilt);
                        if ($result !== false) {
                            if ($field_id) {
                                error_log("MKCG Structure Repair: Field {$field_id} - Successfully rebuilt {$description}");
                            }
                            return $result;
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            if ($field_id) {
                error_log("MKCG Structure Repair: Field {$field_id} - Exception: " . $e->getMessage());
            }
        }
        
        return false;
    }
    
    /**
     * HELPER: Rebuild serialized structure
     * @param array $matches Regex matches
     * @param string $type Structure type
     * @return string|false Rebuilt structure or false
     */
    private function rebuild_serialized_structure($matches, $type) {
        switch ($type) {
            case 'string structure':
                if (isset($matches[2])) {
                    $string = $matches[2];
                    $actual_length = strlen($string);
                    return 's:' . $actual_length . ':"' . $string . '";';
                }
                break;
                
            case 'array structure':
                // More complex array rebuilding would go here
                // For now, return false to try other strategies
                break;
        }
        
        return false;
    }
    
    /**
     * HELPER: Determine processing context based on field ID
     * @param string $field_id Field ID to determine context for
     * @return string Processing context
     */
    private function determine_processing_context($field_id) {
        // Topic fields
        if (in_array($field_id, ['8498', '8499', '8500', '8501', '8502'])) {
            return 'topic';
        }
        
        // Authority hook fields
        if (in_array($field_id, ['10296', '10297', '10387', '10298', '10358'])) {
            return 'authority_hook';
        }
        
        // Question fields
        if (preg_match('/^(8505|851[0-4]|1037[0-9]|1038[0-4])$/', $field_id)) {
            return 'question';
        }
        
        return 'general';
    }
    
    /**
     * HELPER: Process field value safely with context awareness
     * @param mixed $raw_value Raw field value
     * @param string $field_id Field ID
     * @param string $context Processing context
     * @return string Processed value
     */
    private function process_field_value_safe($raw_value, $field_id, $context) {
        // Use enhanced processing for authority hook fields
        if ($context === 'authority_hook') {
            return $this->process_field_value_enhanced($raw_value, $field_id);
        }
        
        // Standard processing for other fields
        if (is_string($raw_value)) {
            $trimmed = trim($raw_value);
            
            if ($this->is_serialized($trimmed)) {
                $unserialized = @unserialize($trimmed);
                if ($unserialized !== false) {
                    if (is_array($unserialized)) {
                        return trim((string)reset($unserialized));
                    }
                    return trim((string)$unserialized);
                }
            }
            
            return $trimmed;
        }
        
        if (is_array($raw_value)) {
            return trim((string)reset($raw_value));
        }
        
        return trim((string)$raw_value);
    }
    
    /**
     * HELPER: Assess field data quality
     * @param string $processed_value Processed field value
     * @param string $context Processing context
     * @return string Quality assessment
     */
    private function assess_field_data_quality($processed_value, $context) {
        if (empty($processed_value)) {
            return 'empty';
        }
        
        $length = strlen($processed_value);
        
        // Check for placeholders
        if (preg_match('/^(Topic|Question|Click|Add|Placeholder|Empty|Todo)/i', $processed_value)) {
            return 'placeholder';
        }
        
        // Context-specific quality assessment
        switch ($context) {
            case 'topic':
                if ($length >= 20 && $length <= 150) {
                    return 'excellent';
                } elseif ($length >= 10) {
                    return 'good';
                } else {
                    return 'poor';
                }
                
            case 'question':
                if ($length >= 15 && preg_match('/\?$/', $processed_value)) {
                    return 'excellent';
                } elseif ($length >= 8) {
                    return 'good';
                } else {
                    return 'poor';
                }
                
            case 'authority_hook':
                if ($length >= 10 && $length <= 500) {
                    return 'good';
                } else {
                    return 'fair';
                }
                
            default:
                if ($length >= 5) {
                    return 'fair';
                } else {
                    return 'poor';
                }
        }
    }
    
    /**
     * HELPER: Generate data quality summary
     * @param array $field_data Field data array
     * @return array Quality summary
     */
    private function generate_data_quality_summary($field_data) {
        $summary = [
            'total_fields' => count($field_data),
            'quality_counts' => [
                'excellent' => 0,
                'good' => 0,
                'fair' => 0,
                'poor' => 0,
                'empty' => 0,
                'placeholder' => 0
            ],
            'by_context' => []
        ];
        
        foreach ($field_data as $field) {
            $quality = $field['data_quality'];
            $context = $field['processing_context'];
            
            $summary['quality_counts'][$quality]++;
            
            if (!isset($summary['by_context'][$context])) {
                $summary['by_context'][$context] = [
                    'total' => 0,
                    'quality_counts' => array_fill_keys(array_keys($summary['quality_counts']), 0)
                ];
            }
            
            $summary['by_context'][$context]['total']++;
            $summary['by_context'][$context]['quality_counts'][$quality]++;
        }
        
        return $summary;
    }
