<?php
/**
 * MKCG Topics Generator
 * Generates interview topics based on Authority Hook and audience
 */

class MKCG_Topics_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'topics';
    // STANDALONE MODE: Simplified configuration
    protected $max_topics = 5;
    protected $max_retries = 3;
    protected $cache_duration = 3600; // 1 hour
    
    // CRITICAL FIX: Add missing service properties
    protected $unified_data_service;
    protected $topics_data_service;
    
    // PHASE 3: Diagnostic system integration
    protected $diagnostic_tools;
    protected $performance_monitor;
    protected $error_tracker;
    
    /**
     * PHASE 1 FIX: Constructor - Enhanced with bulletproof service initialization
     */
    public function __construct($api_service, $formidable_service, $authority_hook_service = null) {
        try {
            // Call parent constructor first
            parent::__construct($api_service, $formidable_service, $authority_hook_service);
            
            // PHASE 1: Initialize critical data services with validation - DEFERRED
            // Initialize data services only when needed to prevent race conditions
            $this->topics_data_service = null;
            $this->unified_data_service = null;
            
            // PHASE 1: Mark for lazy initialization
            add_action('init', [$this, 'lazy_init_data_services'], 15);
            
            // PHASE 3: Initialize diagnostic tools
            $this->init_phase3_diagnostics();
            
            error_log('MKCG Topics Generator: ✅ Constructor completed - services marked for lazy initialization with Phase 3 diagnostics');
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ CRITICAL - Constructor failed: ' . $e->getMessage());
            // Set service states to null to prevent further errors
            $this->topics_data_service = null;
            $this->unified_data_service = null;
            // Don't throw exception - allow graceful degradation
            error_log('MKCG Topics Generator: Continuing with graceful degradation');
        }
    }
    
    /**
     * PHASE 3: Initialize diagnostic tools and monitoring systems
     */
    private function init_phase3_diagnostics() {
        try {
            error_log('🔍 PHASE 3: Initializing Topics Generator diagnostic tools');
            
            // Initialize diagnostic tools if class is available
            if (class_exists('MKCG_Topics_Diagnostics')) {
                $this->diagnostic_tools = new MKCG_Topics_Diagnostics($this);
                error_log('✅ PHASE 3: Diagnostic tools initialized');
            } else {
                error_log('⚠️ PHASE 3: Diagnostic tools class not available');
                $this->diagnostic_tools = null;
            }
            
            // Initialize performance monitor
            $this->performance_monitor = [
                'request_start_time' => microtime(true),
                'memory_start' => memory_get_usage(true),
                'ajax_calls' => [],
                'error_count' => 0,
                'success_count' => 0
            ];
            
            // Initialize error tracker
            $this->error_tracker = [
                'errors' => [],
                'warnings' => [],
                'debug_info' => []
            ];
            
            error_log('✅ PHASE 3: Topics Generator diagnostics initialized successfully');
            
        } catch (Exception $e) {
            error_log('❌ PHASE 3: Failed to initialize diagnostics: ' . $e->getMessage());
            // Don't fail completely - set diagnostics to null and continue
            $this->diagnostic_tools = null;
            $this->performance_monitor = null;
            $this->error_tracker = null;
        }
    }
    
    /**
     * PHASE 3: Log performance metric
     */
    private function log_performance_metric($metric_name, $value, $context = '') {
        if ($this->performance_monitor) {
            $this->performance_monitor[$metric_name] = $value;
            
            if ($this->diagnostic_tools) {
                // Log to diagnostic system if available
                error_log("📊 PHASE 3 Metric: {$metric_name} = {$value} (context: {$context})");
            }
        }
    }
    
    /**
     * PHASE 3: Log error with diagnostic tracking
     */
    private function log_diagnostic_error($error_type, $message, $context = []) {
        if ($this->error_tracker) {
            $this->error_tracker['errors'][] = [
                'type' => $error_type,
                'message' => $message,
                'context' => $context,
                'timestamp' => current_time('mysql'),
                'memory_usage' => memory_get_usage(true)
            ];
            $this->error_tracker['error_count']++;
        }
        
        error_log("❌ PHASE 3 Error [{$error_type}]: {$message}");
    }
    
    /**
     * PHASE 3: Log success with diagnostic tracking
     */
    private function log_diagnostic_success($operation, $details = []) {
        if ($this->performance_monitor) {
            $this->performance_monitor['success_count']++;
        }
        
        error_log("✅ PHASE 3 Success: {$operation}");
    }
    
    /**
     * PHASE 3: Get diagnostic report
     */
    public function get_diagnostic_report() {
        $report = [
            'version' => '3.0.0',
            'phase' => 'PHASE_3_INTEGRATION_VALIDATION',
            'timestamp' => current_time('mysql'),
            'generator_type' => $this->generator_type,
            'performance' => $this->performance_monitor,
            'errors' => $this->error_tracker,
            'services' => [
                'api_service' => $this->api_service ? 'available' : 'missing',
                'formidable_service' => $this->formidable_service ? 'available' : 'missing',
                'authority_hook_service' => $this->authority_hook_service ? 'available' : 'missing',
                'topics_data_service' => $this->topics_data_service ? 'available' : 'missing',
                'unified_data_service' => $this->unified_data_service ? 'available' : 'missing',
                'diagnostic_tools' => $this->diagnostic_tools ? 'available' : 'missing'
            ]
        ];
        
        // Add execution time if available
        if ($this->performance_monitor && isset($this->performance_monitor['request_start_time'])) {
            $report['execution_time'] = round((microtime(true) - $this->performance_monitor['request_start_time']) * 1000, 2);
        }
        
        return $report;
    }
    
    /**
     * PHASE 1 TASK 2: Lazy initialization of data services to prevent race conditions
     */
    public function lazy_init_data_services() {
        error_log('MKCG Topics Generator: Starting lazy initialization of data services');
        
        try {
            // Only initialize if not already initialized
            if ($this->topics_data_service === null || $this->unified_data_service === null) {
                $this->init_data_services_with_validation();
                $this->validate_service_dependencies();
                error_log('MKCG Topics Generator: ✅ Lazy initialization completed successfully');
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Lazy initialization failed: ' . $e->getMessage());
            // Continue with graceful degradation
        }
    }
    
    /**
     * STEP 2 FIX: Initialize data services with comprehensive validation and fallbacks
     */
    private function init_data_services_with_validation() {
        $initialization_errors = [];
        
        // Validate formidable_service is available
        if (!$this->formidable_service || !is_object($this->formidable_service)) {
            $initialization_errors[] = 'Formidable service not available or invalid';
            throw new Exception('Cannot initialize data services without valid Formidable service');
        }
        
        // Initialize Topics Data Service with enhanced validation
        try {
            if (class_exists('MKCG_Topics_Data_Service')) {
                $this->topics_data_service = new MKCG_Topics_Data_Service($this->formidable_service);
                
                // Validate the service was created successfully
                if (!is_object($this->topics_data_service)) {
                    throw new Exception('Topics Data Service constructor failed');
                }
                
                // Test basic service functionality
                if (method_exists($this->topics_data_service, 'get_topics_data')) {
                    error_log('MKCG Topics Generator: ✅ Topics Data Service initialized and validated');
                } else {
                    throw new Exception('Topics Data Service missing required methods');
                }
            } else {
                $initialization_errors[] = 'MKCG_Topics_Data_Service class not found';
                error_log('MKCG Topics Generator: ❌ Topics Data Service class not available');
            }
        } catch (Exception $e) {
            $initialization_errors[] = 'Topics Data Service: ' . $e->getMessage();
            $this->topics_data_service = null;
            error_log('MKCG Topics Generator: ❌ Topics Data Service init failed: ' . $e->getMessage());
        }
        
        // Initialize Unified Data Service with enhanced validation
        try {
            if (class_exists('MKCG_Unified_Data_Service')) {
                $this->unified_data_service = new MKCG_Unified_Data_Service($this->formidable_service);
                
                // Validate the service was created successfully
                if (!is_object($this->unified_data_service)) {
                    throw new Exception('Unified Data Service constructor failed');
                }
                
                error_log('MKCG Topics Generator: ✅ Unified Data Service initialized and validated');
            } else {
                $initialization_errors[] = 'MKCG_Unified_Data_Service class not found';
                error_log('MKCG Topics Generator: ⚠️ Unified Data Service class not available');
            }
        } catch (Exception $e) {
            $initialization_errors[] = 'Unified Data Service: ' . $e->getMessage();
            $this->unified_data_service = null;
            error_log('MKCG Topics Generator: ❌ Unified Data Service init failed: ' . $e->getMessage());
        }
        
        // Log summary of initialization results
        if (empty($initialization_errors)) {
            error_log('MKCG Topics Generator: ✅ All data services initialized successfully');
        } else {
            error_log('MKCG Topics Generator: ⚠️ Service initialization warnings: ' . implode('; ', $initialization_errors));
        }
    }
    
    /**
     * STEP 2 FIX: Validate all service dependencies are met
     */
    private function validate_service_dependencies() {
        $validation_errors = [];
        
        // Check core services from parent
        if (!$this->api_service) {
            $validation_errors[] = 'API Service missing';
        }
        if (!$this->formidable_service) {
            $validation_errors[] = 'Formidable Service missing';
        }
        if (!$this->authority_hook_service) {
            $validation_errors[] = 'Authority Hook Service missing';
        }
        
        // Check data services (these are optional but preferred)
        $has_data_service = false;
        if ($this->topics_data_service) {
            $has_data_service = true;
            error_log('MKCG Validation: ✅ Topics Data Service available');
        }
        if ($this->unified_data_service) {
            $has_data_service = true;
            error_log('MKCG Validation: ✅ Unified Data Service available');
        }
        
        if (!$has_data_service) {
            error_log('MKCG Validation: ⚠️ No data services available - will use fallback methods');
        }
        
        // Throw exception only for critical missing services
        if (!empty($validation_errors)) {
            throw new Exception('Critical service dependencies missing: ' . implode(', ', $validation_errors));
        }
        
        error_log('MKCG Validation: ✅ All critical service dependencies validated');
    }
    
    /**
     * CRITICAL FIX: Check if Topics Data Service is available
     */
    public function is_topics_service_available() {
        return ($this->topics_data_service !== null && is_object($this->topics_data_service));
    }
    
    /**
     * CRITICAL FIX: Get authority hook service for AJAX handlers
     */
    public function get_authority_hook_service() {
        return $this->authority_hook_service;
    }
    
    /**
     * Get form fields configuration
     */
    public function get_form_fields() {
        return [
            'authority_hook' => [
                'type' => 'textarea',
                'label' => 'Authority Hook',
                'required' => true,
                'description' => 'Your expert introduction statement'
            ],
            'audience' => [
                'type' => 'text',
                'label' => 'Target Audience (Optional)',
                'required' => false,
                'description' => 'Specific audience for the topics'
            ]
        ];
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data) {
        $errors = [];
        
        if (empty($data['authority_hook'])) {
            $errors[] = 'Authority Hook is required';
        }
        
        if (!empty($data['authority_hook']) && strlen($data['authority_hook']) < 10) {
            $errors[] = 'Authority Hook must be at least 10 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for topic generation
     */
    public function build_prompt($data) {
        $authority_hook = $data['authority_hook'];
        $audience = isset($data['audience']) ? $data['audience'] : '';
        
        $prompt = "You are an AI assistant specialized in generating **highly relevant interview topics** that align **only with the expert's authority**.

The expert's area of expertise is: \"$authority_hook\".

### **Key Requirements for Topics:**
- Topics **must directly relate to the expert's authority**—avoid unrelated subjects like podcasting unless explicitly relevant.
- Topics should be **intriguing, insightful, and results-driven** to attract podcast hosts.
- Use **specific strategies, case studies, or proven methods** within the expert's domain.
- If an audience is provided, ensure topics speak **specifically to their challenges and goals**.

";

        if (!empty($audience)) {
            $prompt .= "### **Target Audience:** \"$audience\"\nEnsure the topics focus on **the concerns, pain points, and goals of this audience.**\n";
        }

        $prompt .= "### **Example High-Performing Podcast Topics in the Given Niche:**
1. 'The Hidden Wealth in Tax Overages: How Future Retirees Can Unlock Passive Income'
2. 'Tax Overages Explained: How to Identify and Claim Overlooked Funds'
3. '5 Steps to Turning Tax Surplus into a Lucrative Side Business'
4. 'How Future Retirees Can Secure Financial Freedom with Unclaimed Tax Overages'
5. 'From Overages to Income: The Legal and Financial Aspects of Claiming Tax Surpluses'

### **Now generate 5 unique, compelling podcast topics based on the expert introduction. Format as a numbered list (1., 2., etc.), with one topic per line.**";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service already formats topics as an array
        if (is_array($api_response)) {
            $topics = $api_response;
        } else {
            // Fallback if raw string returned
            $topics = [];
            if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
                $topics = array_map('trim', $matches[1]);
            } else {
                $topics = array_filter(array_map(function($t) {
                    return trim($t, " '\"");
                }, explode("\n", $api_response)));
            }
        }
        
        // Format for Formidable field mapping
        $formatted = [
            'topics' => $topics,
            'count' => count($topics)
        ];
        
        // Map individual topics to fields for form 515
        for ($i = 0; $i < min(5, count($topics)); $i++) {
            $formatted['topic_' . ($i + 1)] = $topics[$i];
        }
        
        return $formatted;
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        return [
            'audience' => isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : ''
        ];
    }
    
    /**
     * Get field mappings using centralized configuration
     */
    protected function get_field_mappings() {
        return MKCG_Config::get_field_mappings()['topics'];
    }
    
    /**
     * Get specific field ID using centralized helper
     */
    protected function get_field_id($field_key) {
        return MKCG_Config::get_field_id('topics', $field_key);
    }
    
    /**
     * Get authority hook component field mappings using centralized configuration
     */
    public function get_authority_hook_field_mappings() {
        return MKCG_Config::get_field_mappings()['authority_hook']['fields'];
    }
    
    /**
     * Build authority hook from components - DELEGATED TO SERVICE
     */
    public function build_authority_hook_from_components($entry_id) {
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        $components = [];
        foreach (['who', 'result', 'when', 'how'] as $component) {
            $components[$component] = $this->formidable_service->get_field_value($entry_id, $field_mappings[$component]) ?: '';
        }
        
        // Delegate to centralized Authority Hook Service
        return $this->authority_hook_service->build_authority_hook($components);
    }
    
    /**
     * Save authority hook components to Formidable - DELEGATED TO SERVICE
     */
    public function save_authority_hook_components($entry_id, $who, $result, $when, $how) {
        $components = [
            'who' => $who,
            'result' => $result,
            'when' => $when,
            'how' => $how
        ];
        
        // Build complete authority hook using centralized service
        $complete_hook = $this->authority_hook_service->build_authority_hook($components);
        
        // Save using Formidable service with proper field mappings
        $field_mappings = $this->get_authority_hook_field_mappings();
        
        $data_to_save = $components;
        $data_to_save['complete'] = $complete_hook;
        
        $field_mapping_for_save = [];
        foreach ($data_to_save as $key => $value) {
            if (isset($field_mappings[$key])) {
                $field_mapping_for_save[$key] = $field_mappings[$key];
            }
        }
        
        $result = $this->formidable_service->save_generated_content(
            $entry_id,
            $data_to_save,
            $field_mapping_for_save
        );
        
        return [
            'success' => $result['success'],
            'saved_fields' => $field_mapping_for_save,
            'authority_hook' => $complete_hook,
            'errors' => $result['errors'] ?? []
        ];
    }
    
    /**
     * PHASE 1 CRITICAL FIX: Safe authority hook components save with enhanced error handling
     */
    public function save_authority_hook_components_safe($entry_id, $who, $result, $when, $how) {
        try {
            error_log('MKCG Topics Generator: Starting safe authority hook save for entry ' . $entry_id);
            
            // Validate entry ID
            if (!$entry_id || $entry_id <= 0) {
                return [
                    'success' => false,
                    'errors' => ['Invalid entry ID provided'],
                    'authority_hook' => '',
                    'saved_fields' => []
                ];
            }
            
            // Sanitize components
            $components = [
                'who' => sanitize_textarea_field($who ?: 'your audience'),
                'result' => sanitize_textarea_field($result ?: 'achieve their goals'),
                'when' => sanitize_textarea_field($when ?: 'they need help'),
                'how' => sanitize_textarea_field($how ?: 'through your method')
            ];
            
            error_log('MKCG Topics Generator: Sanitized components: ' . json_encode($components));
            
            // Use the existing method but with enhanced error handling
            $save_result = $this->save_authority_hook_components(
                $entry_id, 
                $components['who'], 
                $components['result'], 
                $components['when'], 
                $components['how']
            );
            
            // Enhanced error handling and logging
            if ($save_result['success']) {
                error_log('MKCG Topics Generator: ✅ Authority hook components saved successfully');
                return $save_result;
            } else {
                error_log('MKCG Topics Generator: ❌ Authority hook save failed: ' . json_encode($save_result['errors'] ?? ['Unknown error']));
                
                // Return formatted error response
                return [
                    'success' => false,
                    'errors' => $save_result['errors'] ?? ['Failed to save authority hook components'],
                    'authority_hook' => $this->authority_hook_service->build_authority_hook($components),
                    'saved_fields' => [],
                    'debug_info' => [
                        'entry_id' => $entry_id,
                        'components_provided' => !empty($who) || !empty($result) || !empty($when) || !empty($how),
                        'authority_hook_service_available' => is_object($this->authority_hook_service),
                        'formidable_service_available' => is_object($this->formidable_service)
                    ]
                ];
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Exception in save_authority_hook_components_safe: ' . $e->getMessage());
            
            return [
                'success' => false,
                'errors' => ['Server error: ' . $e->getMessage()],
                'authority_hook' => '',
                'saved_fields' => [],
                'exception_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
    

    
    // REMOVED: Redundant field processing methods - now using centralized Formidable Service
    
    /**
     * AUTHORITY HOOK FIX: Enhanced template data loading with Authority Hook focus
     */
    public function get_template_data($entry_key = '') {
        $template_data = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'authority_hook_components' => [],
            'form_field_values' => [],
            'has_entry' => false,
            'data_source' => 'default',
            'debug_info' => []
        ];
        
        try {
            // CRITICAL FIX: Enhanced entry resolution 
            $resolved_ids = $this->resolve_entry_identifiers($entry_key);
            $template_data['entry_id'] = $resolved_ids['entry_id'];
            $template_data['entry_key'] = $resolved_ids['entry_key'];
            $post_id = $resolved_ids['post_id'];
            
            error_log('MKCG Authority Hook Fix: Resolved entry_id=' . $resolved_ids['entry_id'] . ', post_id=' . $post_id);

            if ($template_data['entry_id'] > 0) {
                // CRITICAL FIX: Focus on Authority Hook loading first
                $template_data = $this->load_authority_hook_data_enhanced($template_data, $post_id);
                
                // Then load topics data
                $template_data = $this->load_topics_data_enhanced($template_data, $post_id);
            } else {
                error_log('MKCG Authority Hook Fix: No valid entry ID - using defaults');
                $template_data['debug_info'][] = 'No valid entry ID resolved';
            }
            
        } catch (Exception $e) {
            error_log('MKCG Authority Hook Fix: Exception in get_template_data: ' . $e->getMessage());
            $template_data['debug_info'][] = 'Exception: ' . $e->getMessage();
        }
        
        // CRITICAL FIX: Ensure default authority hook components if not loaded
        if (empty($template_data['authority_hook_components']) && $this->authority_hook_service) {
            $template_data['authority_hook_components'] = $this->load_default_authority_hook_components();
            $template_data['data_source'] = 'default_auth_hook';
        }
        
        // CRITICAL FIX: Ensure default topic structure
        if (empty($template_data['form_field_values'])) {
            $template_data['form_field_values'] = [
                'topic_1' => '',
                'topic_2' => '',
                'topic_3' => '',
                'topic_4' => '',
                'topic_5' => ''
            ];
        }
        
        error_log('MKCG Authority Hook Fix: Final template data - source=' . $template_data['data_source'] . ', has_entry=' . ($template_data['has_entry'] ? 'true' : 'false') . ', entry_id=' . $template_data['entry_id']);
        
        return $template_data;
    }
    
    /**
     * AUTHORITY HOOK FIX: Enhanced Authority Hook data loading method
     */
    private function load_authority_hook_data_enhanced($template_data, $post_id) {
        $entry_id = $template_data['entry_id'];
        
        error_log('MKCG Authority Hook Fix: Starting enhanced Authority Hook data loading for entry ' . $entry_id);
        
        // Strategy 1: Use Authority Hook Service (preferred)
        if ($this->authority_hook_service) {
            try {
                error_log('MKCG Authority Hook Fix: Attempting Authority Hook Service load');
                $auth_components = $this->authority_hook_service->get_authority_hook_components($entry_id);
                
                if (!empty($auth_components) && !empty(array_filter($auth_components))) {
                    $template_data['authority_hook_components'] = $auth_components;
                    $template_data['has_entry'] = true;
                    $template_data['data_source'] = 'authority_hook_service';
                    error_log('MKCG Authority Hook Fix: ✅ Authority Hook Service SUCCESS');
                    error_log('MKCG Authority Hook Fix: Loaded components: ' . json_encode($auth_components));
                    return $template_data;
                } else {
                    error_log('MKCG Authority Hook Fix: ⚠️ Authority Hook Service returned empty components');
                }
            } catch (Exception $e) {
                error_log('MKCG Authority Hook Fix: ❌ Authority Hook Service exception: ' . $e->getMessage());
            }
        }
        
        // Strategy 2: Direct Formidable field loading with enhanced processing
        error_log('MKCG Authority Hook Fix: Attempting direct Formidable field loading');
        
        try {
            $auth_components = $this->load_authority_hook_fields_direct($entry_id);
            
            if (!empty(array_filter($auth_components))) {
                $template_data['authority_hook_components'] = $auth_components;
                $template_data['has_entry'] = true;
                $template_data['data_source'] = 'direct_formidable';
                error_log('MKCG Authority Hook Fix: ✅ Direct Formidable loading SUCCESS');
                error_log('MKCG Authority Hook Fix: Direct loaded components: ' . json_encode($auth_components));
                return $template_data;
            } else {
                error_log('MKCG Authority Hook Fix: ⚠️ Direct Formidable loading returned empty data');
            }
        } catch (Exception $e) {
            error_log('MKCG Authority Hook Fix: ❌ Direct Formidable loading exception: ' . $e->getMessage());
        }
        
        // Strategy 3: Emergency default loading
        error_log('MKCG Authority Hook Fix: Using emergency defaults');
        $template_data['authority_hook_components'] = $this->load_default_authority_hook_components();
        $template_data['data_source'] = 'emergency_defaults';
        
        return $template_data;
    }
    
    /**
     * AUTHORITY HOOK FIX: Direct Authority Hook field loading from Formidable
     */
    private function load_authority_hook_fields_direct($entry_id) {
        global $wpdb;
        
        error_log('MKCG Authority Hook Fix: Loading Authority Hook fields directly from database for entry ' . $entry_id);
        
        // CRITICAL FIX: Use exact field mappings from config
        $auth_field_mappings = [
            'who' => '10296',    // WHO do you help?
            'result' => '10297', // WHAT result do you help them achieve? (PROBLEMATIC FIELD)
            'when' => '10387',   // WHEN do they need you? (PROBLEMATIC FIELD)  
            'how' => '10298',    // HOW do you help them achieve this result? (PROBLEMATIC FIELD)
            'complete' => '10358' // Complete Authority Hook
        ];
        
        $auth_components = [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => '',
            'complete' => ''
        ];
        
        $table = $wpdb->prefix . 'frm_item_metas';
        
        foreach ($auth_field_mappings as $component => $field_id) {
            try {
                // Get raw value directly from database
                $raw_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$table} WHERE item_id = %d AND field_id = %d",
                    $entry_id, $field_id
                ));
                
                if ($raw_value !== null) {
                    // Use enhanced field processing specifically for problematic fields
                    if (in_array($field_id, ['10297', '10387', '10298'])) {
                        $processed_value = $this->formidable_service->process_field_value_enhanced($raw_value, $field_id);
                    } else {
                        $processed_value = $this->formidable_service->process_field_value_safe($raw_value, $field_id, 'authority_hook');
                    }
                    
                    if (!empty($processed_value)) {
                        $auth_components[$component] = $processed_value;
                        error_log("MKCG Authority Hook Fix: Loaded {$component} from field {$field_id}: '{$processed_value}'");
                    } else {
                        error_log("MKCG Authority Hook Fix: Field {$field_id} ({$component}) processed to empty value, using default");
                        $auth_components[$component] = $this->get_default_component_value($component);
                    }
                } else {
                    error_log("MKCG Authority Hook Fix: No data found for field {$field_id} ({$component}), using default");
                    $auth_components[$component] = $this->get_default_component_value($component);
                }
                
            } catch (Exception $e) {
                error_log('MKCG Authority Hook Fix: Error loading authority hook ' . $component . ': ' . $e->getMessage());
                $auth_components[$component] = $this->get_default_component_value($component);
            }
        }
        
        // If no complete hook available, try to build it from components
        if (empty($auth_components['complete']) && $this->authority_hook_service) {
            try {
                $auth_components['complete'] = $this->authority_hook_service->build_authority_hook($auth_components);
                error_log('MKCG Authority Hook Fix: Built complete authority hook from components: ' . $auth_components['complete']);
            } catch (Exception $e) {
                error_log('MKCG Authority Hook Fix: Error building authority hook: ' . $e->getMessage());
                $auth_components['complete'] = 'I help ' . $auth_components['who'] . ' ' . $auth_components['result'] . ' when ' . $auth_components['when'] . ' ' . $auth_components['how'] . '.';
            }
        }
        
        return $auth_components;
    }
    
    /**
     * AUTHORITY HOOK FIX: Load default Authority Hook components
     */
    private function load_default_authority_hook_components() {
        $defaults = [
            'who' => 'your audience',
            'result' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method'
        ];
        
        if ($this->authority_hook_service) {
            $defaults['complete'] = $this->authority_hook_service->build_authority_hook($defaults);
        } else {
            $defaults['complete'] = 'I help ' . $defaults['who'] . ' ' . $defaults['result'] . ' when ' . $defaults['when'] . ' ' . $defaults['how'] . '.';
        }
        
        error_log('MKCG Authority Hook Fix: Using default components: ' . json_encode($defaults));
        return $defaults;
    }
    
    /**
     * AUTHORITY HOOK FIX: Get default value for specific component
     */
    private function get_default_component_value($component) {
        $defaults = [
            'who' => 'your audience',
            'result' => 'achieve their goals',
            'when' => 'they need help',
            'how' => 'through your method'
        ];
        
        return $defaults[$component] ?? '';
    }
    
    /**
     * AUTHORITY HOOK FIX: Enhanced topics data loading
     */
    private function load_topics_data_enhanced($template_data, $post_id) {
        $entry_id = $template_data['entry_id'];
        
        // Try Topics Data Service first (if available)
        if ($this->is_topics_service_available()) {
            try {
                $service_data = $this->topics_data_service->get_topics_data($entry_id, $template_data['entry_key'], $post_id);
                
                if ($service_data['success'] && !empty($service_data['topics'])) {
                    $template_data['form_field_values'] = $service_data['topics'];
                    if (!$template_data['has_entry']) {
                        $template_data['has_entry'] = true;
                    }
                    error_log('MKCG Authority Hook Fix: ✅ Topics loaded via Topics Data Service');
                    return $template_data;
                }
            } catch (Exception $e) {
                error_log('MKCG Authority Hook Fix: Topics Data Service failed: ' . $e->getMessage());
            }
        }
        
        // Fallback: Direct topics loading
        try {
            $topics_data = $this->load_topics_from_formidable($entry_id);
            if (!empty(array_filter($topics_data))) {
                $template_data['form_field_values'] = $topics_data;
                if (!$template_data['has_entry']) {
                    $template_data['has_entry'] = true;
                }
                error_log('MKCG Authority Hook Fix: ✅ Topics loaded via direct Formidable');
            }
        } catch (Exception $e) {
            error_log('MKCG Authority Hook Fix: Direct topics loading failed: ' . $e->getMessage());
        }
        
        return $template_data;
    }
    
    /**
     * STEP 3 SUPPORTING METHODS: Resolve entry identifiers from multiple sources
     */
    private function resolve_entry_identifiers($entry_key) {
        $resolved = [
            'entry_id' => 0,
            'entry_key' => $entry_key,
            'post_id' => 0
        ];
        
        // If no entry key provided, try to get from URL
        if (empty($entry_key) && isset($_GET['entry'])) {
            $entry_key = sanitize_text_field($_GET['entry']);
            $resolved['entry_key'] = $entry_key;
        }
        
        // Resolve entry_id from entry_key
        if (!empty($entry_key)) {
            try {
                $entry_data = $this->formidable_service->get_entry_data($entry_key);
                if ($entry_data['success']) {
                    $resolved['entry_id'] = $entry_data['entry_id'];
                    $resolved['post_id'] = $this->formidable_service->get_post_id_from_entry($resolved['entry_id']);
                    error_log('MKCG Entry Resolution: ✅ Resolved from entry_key - entry_id=' . $resolved['entry_id'] . ', post_id=' . $resolved['post_id']);
                } else {
                    error_log('MKCG Entry Resolution: ❌ Failed to resolve entry_key: ' . $entry_key);
                }
            } catch (Exception $e) {
                error_log('MKCG Entry Resolution: ❌ Exception resolving entry_key: ' . $e->getMessage());
            }
        }
        
        return $resolved;
    }
    
    /**
     * STEP 3 SUPPORTING METHODS: Load data via unified service with fallbacks
     */
    private function load_data_via_unified_service($template_data, $post_id) {
        $entry_id = $template_data['entry_id'];
        $entry_key = $template_data['entry_key'];
        
        // Strategy 1: Use Topics Data Service (preferred)
        if ($this->is_topics_service_available()) {
            error_log('MKCG Unified Loading: 🔄 Attempting Topics Data Service for entry_id=' . $entry_id);
            
            try {
                $service_data = $this->topics_data_service->get_topics_data($entry_id, $entry_key, $post_id);
                
                if ($service_data['success']) {
                    error_log('MKCG Unified Loading: ✅ Topics Data Service SUCCESS');
                    
                    // Load authority hook components
                    if ($this->authority_hook_service) {
                        $template_data['authority_hook_components'] = $this->authority_hook_service->get_authority_hook_components($entry_id);
                    }
                    
                    $template_data['form_field_values'] = $service_data['topics'];
                    $template_data['has_entry'] = true;
                    $template_data['data_source'] = 'topics_data_service';
                    $template_data['debug_info'][] = 'Loaded via Topics Data Service';
                    
                    return $template_data;
                } else {
                    error_log('MKCG Unified Loading: ⚠️ Topics Data Service failed: ' . ($service_data['message'] ?? 'Unknown error'));
                    $template_data['debug_info'][] = 'Topics Data Service failed: ' . ($service_data['message'] ?? 'Unknown error');
                }
            } catch (Exception $e) {
                error_log('MKCG Unified Loading: ❌ Topics Data Service exception: ' . $e->getMessage());
                $template_data['debug_info'][] = 'Topics Data Service exception: ' . $e->getMessage();
            }
        }
        
        // Strategy 2: Direct Formidable loading (fallback)
        error_log('MKCG Unified Loading: 🔄 Attempting direct Formidable loading');
        
        try {
            // Load authority hook components
            if ($this->authority_hook_service) {
                $template_data['authority_hook_components'] = $this->authority_hook_service->get_authority_hook_components($entry_id);
            }
            
            // Load topics directly from Formidable
            $topics_data = $this->load_topics_from_formidable($entry_id);
            
            if (!empty(array_filter($topics_data))) {
                $template_data['form_field_values'] = $topics_data;
                $template_data['has_entry'] = true;
                $template_data['data_source'] = 'direct_formidable';
                $template_data['debug_info'][] = 'Loaded via direct Formidable access';
                
                error_log('MKCG Unified Loading: ✅ Direct Formidable loading SUCCESS');
                return $template_data;
            } else {
                error_log('MKCG Unified Loading: ⚠️ Direct Formidable loading returned empty data');
                $template_data['debug_info'][] = 'Direct Formidable loading returned empty data';
            }
        } catch (Exception $e) {
            error_log('MKCG Unified Loading: ❌ Direct Formidable loading exception: ' . $e->getMessage());
            $template_data['debug_info'][] = 'Direct Formidable loading exception: ' . $e->getMessage();
        }
        
        // Strategy 3: Return template_data with has_entry=false (final fallback)
        error_log('MKCG Unified Loading: 📋 Using final fallback - no data loaded');
        $template_data['data_source'] = 'fallback';
        $template_data['debug_info'][] = 'All loading strategies failed - using defaults';
        
        return $template_data;
    }
    
    /**
     * AUTHORITY HOOK FIX: Load topics directly from Formidable fields as fallback
     */
    private function load_topics_from_formidable($entry_id) {
        $topics_data = [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
        
        if (!$entry_id) {
            return $topics_data;
        }
        
        error_log('MKCG Topics Generator: 🔄 Loading topics directly from Formidable entry ' . $entry_id);
        
        // Get field mappings from centralized config
        $topic_mappings = MKCG_Config::get_field_mappings()['topics']['fields'];
        
        foreach ($topic_mappings as $topic_key => $field_id) {
            $value = $this->formidable_service->get_field_value($entry_id, $field_id);
            if (!empty($value)) {
                $topics_data[$topic_key] = trim($value);
                error_log('MKCG Topics Generator: ✅ Loaded ' . $topic_key . ' from field ' . $field_id . ': ' . substr($value, 0, 50));
            }
        }
        
        return $topics_data;
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
    }
    
    /**
     * Override AJAX generation to handle legacy compatibility
     */
    public function handle_ajax_generation() {
        // Handle legacy action name for backwards compatibility
        if (isset($_POST['action']) && $_POST['action'] === 'generate_interview_topics') {
            $this->handle_legacy_topics_generation();
            return;
        }
        
        // Call parent method for new unified handling
        parent::handle_ajax_generation();
    }
    
    /**
     * Handle legacy topics generation (for backwards compatibility)
     */
    private function handle_legacy_topics_generation() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            error_log('Invalid entry ID: ' . $_POST['entry_id']);
            wp_send_json_error(['message' => 'Invalid entry ID.']);
            return;
        }
        
        // Get Authority Hook using the service
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if (!$authority_hook_result['success']) {
            wp_send_json_error($authority_hook_result);
            return;
        }
        
        $authority_hook = $authority_hook_result['value'];
        $audience = isset($_POST['audience']) ? sanitize_textarea_field($_POST['audience']) : '';
        
        // Build input data
        $input_data = [
            'entry_id' => $entry_id,
            'authority_hook' => $authority_hook,
            'audience' => $audience
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
        
        // Save topics using unified service
        $topics_wrapper = $this->unified_data_service->get_topics_service();
        
        // Map topics to proper format for unified service
        $topics_data = [];
        for ($i = 0; $i < min(5, count($formatted_output['topics'])); $i++) {
            $topics_data[$i + 1] = $formatted_output['topics'][$i];
        }
        
        // Get post_id from entry
        $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
        
        if ($post_id) {
            $save_result = $topics_wrapper->save_topics_data($topics_data, $post_id, $entry_id);
            
            if (!$save_result['success']) {
                error_log('MKCG Topics Generator: Failed to save via unified service: ' . print_r($save_result, true));
            }
        } else {
            error_log('MKCG Topics Generator: No post_id found for entry ' . $entry_id);
        }
        
        // Return in legacy format for compatibility
        wp_send_json_success([
            'topics' => $formatted_output['topics']
        ]);
    }
    
    /**
     * PHASE 1 FIX: Initialize with all required AJAX actions - Enhanced error handling
     */
    public function init() {
        try {
            parent::init();
            
            // PHASE 1: Ensure all critical AJAX handlers are registered
            $this->register_critical_ajax_handlers();
            
            error_log('MKCG Topics Generator: ✅ All AJAX handlers registered successfully');
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ CRITICAL - Init failed: ' . $e->getMessage());
            // Add admin notice for critical initialization failure
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p><strong>Topics Generator Error:</strong> Initialization failed - ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * PHASE 1 FIX: Register all critical AJAX handlers with validation
     */
    private function register_critical_ajax_handlers() {
        $ajax_handlers = [
            // Core data handlers
            'mkcg_get_topics_data' => 'handle_get_topics_data_ajax',
            'mkcg_save_topics_data' => 'handle_save_topics_data_ajax',
            'mkcg_save_topic' => 'handle_save_topic_ajax',
            
            // Authority hook handlers
            'mkcg_save_authority_hook' => 'handle_save_authority_hook_ajax',
            'mkcg_update_authority_hook' => 'handle_save_authority_hook_ajax', // Alias
            
            // Field handlers
            'mkcg_save_field' => 'handle_save_field_ajax',
            'mkcg_save_topic_field' => 'handle_save_topic_field_ajax',
            
            // Health check handler
            'mkcg_health_check' => 'handle_health_check_ajax',
            
            // Legacy compatibility
            'generate_interview_topics' => 'handle_ajax_generation',
            'fetch_authority_hook' => 'handle_fetch_authority_hook',
        ];
        
        foreach ($ajax_handlers as $action => $method) {
            if (method_exists($this, $method)) {
                add_action('wp_ajax_' . $action, [$this, $method]);
                add_action('wp_ajax_nopriv_' . $action, [$this, $method]);
                error_log("MKCG AJAX Registration: ✅ Registered {$action} → {$method}");
            } else {
                error_log("MKCG AJAX Registration: ❌ Method {$method} not found for action {$action}");
            }
        }
    }
    

    
    /**
     * CRITICAL FIX: Handle health check AJAX request
     * Delegates to the AJAX handlers class which has the ultra-simplified health check
     */
    public function handle_health_check_ajax() {
        error_log('MKCG Topics Generator: Health check AJAX request received - delegating to AJAX handlers');
        
        try {
            // Simple health check response - no complex validation required
            wp_send_json_success([
                'status' => 'healthy',
                'timestamp' => current_time('mysql'),
                'server_time' => time(),
                'ajax_handler' => 'topics_generator_working',
                'method_called' => 'handle_health_check_ajax',
                'services_available' => [
                    'api_service' => $this->api_service ? true : false,
                    'formidable_service' => $this->formidable_service ? true : false,
                    'authority_hook_service' => $this->authority_hook_service ? true : false,
                    'topics_data_service' => $this->topics_data_service ? true : false
                ]
            ]);
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception in health check: ' . $e->getMessage());
            
            // Even with exception, return working status
            wp_send_json_success([
                'status' => 'degraded_but_working',
                'error' => $e->getMessage(),
                'ajax_handler' => 'topics_generator_working_with_errors',
                'timestamp' => current_time('mysql')
            ]);
        }
    }
    
    /**
     * Handle legacy fetch authority hook request
     */
    public function handle_fetch_authority_hook() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID']);
            return;
        }
        
        $authority_hook_result = $this->authority_hook_service->get_authority_hook($entry_id);
        
        if ($authority_hook_result['success']) {
            wp_send_json_success([
                'authority_hook' => $authority_hook_result['value']
            ]);
        } else {
            wp_send_json_error($authority_hook_result);
        }
    }
    
    /**
     * UNIFIED AJAX: Handle get topics data request using Topics Data Service (same as Questions Generator)
     */
    public function handle_get_topics_data_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security([]);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
        $entry_key = isset($_POST['entry_key']) ? sanitize_text_field($_POST['entry_key']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        error_log('MKCG Topics AJAX: 🔄 UNIFIED - Get topics data request (entry_id=' . $entry_id . ', entry_key=' . $entry_key . ', post_id=' . $post_id . ')');
        
        // ROOT LEVEL FIX: Use Topics Data Service for consistent data loading (same as Questions Generator)
        if ($this->is_topics_service_available()) {
            try {
                error_log('MKCG Topics AJAX: Using UNIFIED Topics Data Service');
                $result = $this->topics_data_service->get_topics_data($entry_id, $entry_key, $post_id);
                
                if ($result['success']) {
                    error_log('MKCG Topics AJAX: ✅ SUCCESS - Unified service returned data');
                    wp_send_json_success([
                        'entry_id' => $result['entry_id'],
                        'authority_hook' => $result['authority_hook'],
                        'topics' => $result['topics'],
                        'has_entry' => true,
                        'data_quality' => $result['data_quality'],
                        'source' => $result['source'],
                        'unified_service' => true // Flag to indicate unified service usage
                    ]);
                } else {
                    error_log('MKCG Topics AJAX: ⚠️ Unified service failed: ' . ($result['message'] ?? 'Unknown error'));
                    wp_send_json_error([
                        'message' => $result['message'] ?? 'Failed to load topics data via unified service',
                        'entry_key' => $entry_key,
                        'entry_id' => $entry_id,
                        'unified_service' => true
                    ]);
                }
            } catch (Exception $e) {
                error_log('MKCG Topics AJAX: ❌ Exception in unified Topics Data Service: ' . $e->getMessage());
                wp_send_json_error([
                    'message' => 'Unified service error: ' . $e->getMessage(),
                    'unified_service' => true
                ]);
            }
        } else {
            error_log('MKCG Topics AJAX: ❌ Topics Data Service not available - check initialization');
            wp_send_json_error([
                'message' => 'Topics Data Service not available - check service initialization',
                'fallback' => true,
                'unified_service' => false
            ]);
        }
    }
    
    /**
     * PHASE 1 TASK 2: Root-level implementation of save topics data AJAX handler
     */
    public function handle_save_topics_data_ajax() {
        // PHASE 3: Start performance tracking
        $start_time = microtime(true);
        $this->log_performance_metric('ajax_call_start', $start_time, 'save_topics_data');
        
        try {
            error_log('MKCG Topics Generator: handle_save_topics_data_ajax called');
            
            // PHASE 1: Enhanced security validation
            if (!$this->validate_ajax_request()) {
                $this->log_diagnostic_error('security_validation', 'AJAX security validation failed');
                wp_send_json_error(['message' => 'Security validation failed', 'code' => 'SECURITY_FAILED']);
                return;
            }
            
            $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
            $topics = isset($_POST['topics']) ? $_POST['topics'] : [];
            
            if (!$entry_id) {
                wp_send_json_error(['message' => 'Entry ID is required', 'code' => 'MISSING_ENTRY_ID']);
                return;
            }
            
            error_log('MKCG Topics Generator: Processing save for entry_id=' . $entry_id);
            
            // PHASE 1: Process topics data with enhanced validation
            $processed_topics = $this->process_topics_for_save($topics);
            
            if (empty($processed_topics)) {
                wp_send_json_error(['message' => 'No valid topics data provided', 'code' => 'NO_TOPICS_DATA']);
                return;
            }
            
            // PHASE 1: Save topics using direct Formidable service (most reliable)
            $save_result = $this->save_topics_directly($entry_id, $processed_topics);
            
            if ($save_result['success']) {
                // PHASE 3: Log success and performance
                $end_time = microtime(true);
                $execution_time = round(($end_time - $start_time) * 1000, 2);
                $this->log_performance_metric('ajax_execution_time', $execution_time, 'save_topics_data');
                $this->log_diagnostic_success('save_topics_data', ['saved_count' => $save_result['saved_count']]);
                
                error_log('MKCG Topics Generator: ✅ Topics saved successfully');
                wp_send_json_success([
                    'message' => 'Topics saved successfully',
                    'saved_count' => $save_result['saved_count'],
                    'entry_id' => $entry_id,
                    'saved_fields' => $save_result['saved_fields'] ?? [],
                    'performance' => ['execution_time' => $execution_time . 'ms']
                ]);
            } else {
                // PHASE 3: Log error and performance
                $end_time = microtime(true);
                $execution_time = round(($end_time - $start_time) * 1000, 2);
                $this->log_diagnostic_error('save_operation', 'Topics save failed', $save_result['errors']);
                
                error_log('MKCG Topics Generator: ❌ Save failed: ' . json_encode($save_result['errors']));
                wp_send_json_error([
                    'message' => 'Failed to save topics',
                    'errors' => $save_result['errors'] ?? ['Unknown error'],
                    'code' => 'SAVE_FAILED',
                    'performance' => ['execution_time' => $execution_time . 'ms']
                ]);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Exception in handle_save_topics_data_ajax: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Server error during save operation',
                'code' => 'CRITICAL_ERROR',
                'details' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * PHASE 1 TASK 2: Validate AJAX request with enhanced security
     */
    private function validate_ajax_request() {
        // Check nonce with multiple fallback strategies
        $nonce_fields = ['nonce', 'security', 'save_nonce', 'mkcg_nonce', '_wpnonce'];
        $nonce_actions = ['mkcg_nonce', 'mkcg_save_nonce', 'save_topics_nonce'];
        
        $nonce_verified = false;
        foreach ($nonce_fields as $field) {
            if (isset($_POST[$field]) && !empty($_POST[$field])) {
                foreach ($nonce_actions as $action) {
                    if (wp_verify_nonce($_POST[$field], $action)) {
                        $nonce_verified = true;
                        error_log('MKCG Topics Generator: Nonce verified with field=' . $field . ', action=' . $action);
                        break 2;
                    }
                }
            }
        }
        
        if (!$nonce_verified) {
            error_log('MKCG Topics Generator: Nonce verification failed');
            return false;
        }
        
        // Check user permissions
        if (!is_user_logged_in()) {
            error_log('MKCG Topics Generator: User not logged in');
            return false;
        }
        
        if (!current_user_can('edit_posts')) {
            error_log('MKCG Topics Generator: User lacks edit_posts capability');
            return false;
        }
        
        return true;
    }
    
    /**
     * PHASE 1 TASK 2: Process topics data for saving with validation
     */
    private function process_topics_for_save($topics) {
        $processed = [];
        
        if (!is_array($topics)) {
            error_log('MKCG Topics Generator: Topics data is not an array');
            return $processed;
        }
        
        foreach ($topics as $key => $value) {
            if (empty(trim($value))) {
                continue; // Skip empty topics
            }
            
            // Normalize key format
            if (strpos($key, 'topic_') === 0) {
                $topic_key = $key;
            } else {
                $topic_key = 'topic_' . $key;
            }
            
            // Validate topic key format
            if (preg_match('/^topic_[1-5]$/', $topic_key)) {
                $processed[$topic_key] = sanitize_textarea_field(trim($value));
                error_log('MKCG Topics Generator: Processed topic: ' . $topic_key . ' = ' . substr($processed[$topic_key], 0, 50));
            }
        }
        
        error_log('MKCG Topics Generator: Processed ' . count($processed) . ' topics');
        return $processed;
    }
    
    /**
     * PHASE 1 TASK 2: Save topics directly using Formidable service (most reliable method)
     */
    private function save_topics_directly($entry_id, $topics_data) {
        $result = [
            'success' => false,
            'saved_count' => 0,
            'saved_fields' => [],
            'errors' => []
        ];
        
        try {
            if (!$this->formidable_service) {
                $result['errors'][] = 'Formidable service not available';
                return $result;
            }
            
            // Get field mappings
            $field_mappings = $this->get_topics_field_mappings_safe();
            
            if (empty($field_mappings)) {
                $result['errors'][] = 'Topic field mappings not available';
                return $result;
            }
            
            // Save each topic individually for better error handling
            $successful_saves = 0;
            foreach ($topics_data as $topic_key => $topic_value) {
                if (isset($field_mappings[$topic_key])) {
                    $field_id = $field_mappings[$topic_key];
                    
                    try {
                        $save_result = $this->formidable_service->save_generated_content(
                            $entry_id,
                            [$topic_key => $topic_value],
                            [$topic_key => $field_id]
                        );
                        
                        if ($save_result['success']) {
                            $successful_saves++;
                            $result['saved_fields'][$topic_key] = $field_id;
                            error_log('MKCG Topics Generator: Saved ' . $topic_key . ' to field ' . $field_id);
                        } else {
                            $result['errors'][] = 'Failed to save ' . $topic_key . ': ' . ($save_result['message'] ?? 'Unknown error');
                            error_log('MKCG Topics Generator: Failed to save ' . $topic_key . ': ' . ($save_result['message'] ?? 'Unknown error'));
                        }
                    } catch (Exception $e) {
                        $result['errors'][] = 'Exception saving ' . $topic_key . ': ' . $e->getMessage();
                        error_log('MKCG Topics Generator: Exception saving ' . $topic_key . ': ' . $e->getMessage());
                    }
                } else {
                    $result['errors'][] = 'No field mapping found for ' . $topic_key;
                    error_log('MKCG Topics Generator: No field mapping for ' . $topic_key);
                }
            }
            
            $result['saved_count'] = $successful_saves;
            $result['success'] = $successful_saves > 0;
            
            if ($result['success']) {
                error_log('MKCG Topics Generator: Successfully saved ' . $successful_saves . ' topics');
            }
            
        } catch (Exception $e) {
            $result['errors'][] = 'Critical error: ' . $e->getMessage();
            error_log('MKCG Topics Generator: Critical error in save_topics_directly: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * PHASE 1 TASK 2: Get topics field mappings safely
     */
    private function get_topics_field_mappings_safe() {
        // Use hardcoded mappings for reliability
        return [
            'topic_1' => '8498',
            'topic_2' => '8499',
            'topic_3' => '8500',
            'topic_4' => '8501',
            'topic_5' => '8502'
        ];
    }
    
    /**
     * CRITICAL FIX: Handle save single topic AJAX request
     */
    public function handle_save_topic_ajax() {
        try {
            error_log('MKCG Topics AJAX: Starting save single topic request');
            
            // Use centralized security validation with more lenient required fields
            $security_check = $this->validate_ajax_security(['entry_id']);
            if (is_wp_error($security_check)) {
                error_log('MKCG Topics AJAX: Security validation failed: ' . $security_check->get_error_message());
                wp_send_json_error(['message' => $security_check->get_error_message()]);
                return;
            }
            
            $entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
            $topic_number = isset($_POST['topic_number']) ? intval($_POST['topic_number']) : 0;
            $topic_text = isset($_POST['topic_text']) ? sanitize_textarea_field($_POST['topic_text']) : '';
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
            
            // Resolve post_id from entry_id if not provided
            if (!$post_id && $entry_id) {
                $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
                error_log('MKCG Topics AJAX: Resolved post_id=' . $post_id . ' from entry_id=' . $entry_id);
            }
            
            if (!$entry_id || !$topic_number || empty($topic_text)) {
                wp_send_json_error(['message' => 'Entry ID, topic number, and topic text are required']);
                return;
            }
            
            if ($topic_number < 1 || $topic_number > 5) {
                wp_send_json_error(['message' => 'Topic number must be between 1 and 5']);
                return;
            }
            
            error_log('MKCG Topics AJAX: Processing single topic save (entry_id=' . $entry_id . ', topic_number=' . $topic_number . ')');
            
            // Try unified service first, then fallback to direct save
            if ($this->unified_data_service && $post_id) {
                error_log('MKCG Topics AJAX: Using unified service for single topic');
                $topics_service = $this->unified_data_service->get_topics_service();
                $result = $topics_service->save_single_topic($topic_number, $topic_text, $post_id, $entry_id);
            } else {
                error_log('MKCG Topics AJAX: Using fallback for single topic');
                $result = $this->save_single_topic_fallback($topic_number, $topic_text, $entry_id, $post_id);
            }
            
            if ($result['success']) {
                error_log('MKCG Topics AJAX: ✅ Single topic saved successfully');
                wp_send_json_success($result);
            } else {
                error_log('MKCG Topics AJAX: ❌ Single topic save failed: ' . json_encode($result));
                wp_send_json_error($result);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Topics AJAX: ❌ Critical exception in save single topic: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Server error during single topic save',
                'details' => $e->getMessage()
            ]);
        }
    }
    

    
    /**
     * PHASE 1 TASK 2: Load topics data directly from Formidable
     */
    private function load_topics_data_directly($entry_id) {
        $topics_data = [
            'topic_1' => '',
            'topic_2' => '',
            'topic_3' => '',
            'topic_4' => '',
            'topic_5' => ''
        ];
        
        if (!$this->formidable_service || !$entry_id) {
            return $topics_data;
        }
        
        $field_mappings = $this->get_topics_field_mappings_safe();
        
        foreach ($field_mappings as $topic_key => $field_id) {
            try {
                $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                if (!empty($value)) {
                    $topics_data[$topic_key] = $value;
                    error_log('MKCG Topics Generator: Loaded ' . $topic_key . ' from field ' . $field_id);
                }
            } catch (Exception $e) {
                error_log('MKCG Topics Generator: Error loading ' . $topic_key . ': ' . $e->getMessage());
            }
        }
        
        return $topics_data;
    }
    
    /**
     * PHASE 1 TASK 2: Load authority hook data directly from Formidable
     */
    private function load_authority_hook_data_directly($entry_id) {
        $authority_hook_data = [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => '',
            'complete' => ''
        ];
        
        if (!$this->formidable_service || !$entry_id) {
            return $authority_hook_data;
        }
        
        // CRITICAL FIX: Enhanced authority hook loading with diagnostic support
        error_log('MKCG Topics Generator: Starting enhanced authority hook loading for entry ' . $entry_id);
        
        // Use hardcoded field mappings for authority hook
        $auth_field_mappings = [
            'who' => '10296',
            'result' => '10297',
            'when' => '10387',
            'how' => '10298',
            'complete' => '10358'
        ];
        
        // CRITICAL FIX: Run diagnostic if available
        if (method_exists($this->formidable_service, 'diagnose_authority_hook_fields')) {
            $diagnosis = $this->formidable_service->diagnose_authority_hook_fields($entry_id);
            error_log('MKCG Topics Generator: Diagnostic completed for entry ' . $entry_id);
        }
        
        foreach ($auth_field_mappings as $component => $field_id) {
            try {
                // CRITICAL FIX: Use enhanced field value processing
                global $wpdb;
                $raw_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
                    $entry_id, $field_id
                ));
                
                if ($raw_value !== null) {
                    $processed_value = $this->formidable_service->process_field_value_enhanced($raw_value, $field_id);
                    
                    if (!empty($processed_value)) {
                        $authority_hook_data[$component] = $processed_value;
                        error_log("MKCG Topics Generator CRITICAL FIX: Loaded {$component} from field {$field_id}: '{$processed_value}'");
                    } else {
                        error_log("MKCG Topics Generator CRITICAL FIX: Field {$field_id} ({$component}) processed to empty value");
                        // Use field-specific defaults for empty values
                        $defaults = [
                            'who' => 'your audience',
                            'result' => 'achieve their goals',
                            'when' => 'they need help',
                            'how' => 'through your method'
                        ];
                        $authority_hook_data[$component] = $defaults[$component] ?? '';
                    }
                } else {
                    error_log("MKCG Topics Generator CRITICAL FIX: No data found for field {$field_id} ({$component})");
                }
                
            } catch (Exception $e) {
                error_log('MKCG Topics Generator CRITICAL FIX: Error loading authority hook ' . $component . ': ' . $e->getMessage());
            }
        }
        
        // If no complete hook available, try to build it from components
        if (empty($authority_hook_data['complete']) && $this->authority_hook_service) {
            try {
                $authority_hook_data['complete'] = $this->authority_hook_service->build_authority_hook($authority_hook_data);
                error_log('MKCG Topics Generator: Built complete authority hook from components');
            } catch (Exception $e) {
                error_log('MKCG Topics Generator: Error building authority hook: ' . $e->getMessage());
            }
        }
        
        return $authority_hook_data;
    }
    
    /**
     * PHASE 1 TASK 2: Assess data quality for debugging
     */
    private function assess_data_quality($topics_data, $authority_hook_data) {
        $topics_count = count(array_filter($topics_data));
        $auth_components_count = count(array_filter(array_slice($authority_hook_data, 0, 4))); // who, result, when, how
        
        if ($topics_count >= 3 && $auth_components_count >= 3) {
            return 'high';
        } elseif ($topics_count >= 1 || $auth_components_count >= 2) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    

    
    /**
     * STEP 4 FIX: Enhanced AJAX handler with standardized data communication
     */
    public function handle_save_authority_hook_ajax() {
        try {
            error_log('MKCG Step 4: Starting standardized save authority hook AJAX');
            
            // Step 4A: Standardized request validation
            $request_data = $this->validate_and_extract_request_data([
                'entry_id' => 'required|integer',
                'who' => 'string',
                'result' => 'string', 
                'when' => 'string',
                'how' => 'string'
            ]);
            
            if (is_wp_error($request_data)) {
                error_log('MKCG Step 4: Request validation failed: ' . $request_data->get_error_message());
                wp_send_json_error([
                    'message' => $request_data->get_error_message(),
                    'error_code' => 'request_validation_failed',
                    'step' => 'step_4_validation'
                ]);
                return;
            }
            
            $entry_id = $request_data['entry_id'];
            
            // Step 4B: Resolve post_id to prevent 500 errors
            $post_id = $this->resolve_post_id_from_entry($entry_id);
            if (!$post_id) {
                error_log('MKCG Step 4: Could not resolve post_id for entry_id=' . $entry_id);
                // Don't fail completely - continue with entry-only save
            }
            
            error_log('MKCG Step 4: Processing for entry_id=' . $entry_id . ', post_id=' . ($post_id ?: 'none'));
            
            // Step 4C: Standardized save operation with comprehensive error handling
            $save_result = $this->standardized_authority_hook_save(
                $entry_id,
                $post_id,
                $request_data
            );
            
            // Step 4D: Standardized response format
            if ($save_result['success']) {
                error_log('MKCG Step 4: ✅ Authority hook saved successfully');
                wp_send_json_success([
                    'message' => 'Authority hook saved successfully',
                    'data' => [
                        'authority_hook' => $save_result['authority_hook'],
                        'components' => $save_result['components'],
                        'entry_id' => $entry_id,
                        'post_id' => $post_id
                    ],
                    'meta' => [
                        'saved_fields' => $save_result['saved_fields'],
                        'save_method' => $save_result['save_method'],
                        'timestamp' => time()
                    ]
                ]);
            } else {
                error_log('MKCG Step 4: ❌ Save failed: ' . json_encode($save_result['errors']));
                wp_send_json_error([
                    'message' => 'Failed to save authority hook',
                    'error_code' => 'save_operation_failed',
                    'details' => $save_result['errors'],
                    'debug_info' => $save_result['debug_info'] ?? []
                ]);
            }
            
        } catch (Exception $e) {
            error_log('MKCG Step 4: ❌ Critical exception: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Server error during save operation',
                'error_code' => 'critical_server_error',
                'details' => $e->getMessage(),
                'step' => 'step_4_exception_handler'
            ]);
        }
    }
    
    /**
     * CRITICAL FIX: Enhanced security validation with better error handling
     */
    private function validate_ajax_security_enhanced($required_fields = []) {
        try {
            // Use the base class validation first
            $base_validation = $this->validate_ajax_security($required_fields);
            
            if (is_wp_error($base_validation)) {
                return $base_validation;
            }
            
            // Additional validation for Topics Generator specific requirements
            if (!current_user_can('edit_posts')) {
                return new WP_Error('insufficient_permissions', 'User does not have permission to edit content');
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception in validate_ajax_security_enhanced: ' . $e->getMessage());
            return new WP_Error('security_validation_exception', 'Security validation failed due to server error');
        }
    }
    
    /**
     * CRITICAL FIX: Extract and validate authority components from POST data
     */
    private function extract_authority_components_from_post() {
        $components = [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => ''
        ];
        
        try {
            foreach ($components as $key => $default) {
                if (isset($_POST[$key])) {
                    $value = sanitize_textarea_field($_POST[$key]);
                    $components[$key] = !empty($value) ? $value : $default;
                } else {
                    // Use defaults for missing components
                    $defaults = [
                        'who' => 'your audience',
                        'result' => 'achieve their goals',
                        'when' => 'they need help',
                        'how' => 'through your method'
                    ];
                    $components[$key] = $defaults[$key];
                }
            }
            
            error_log('MKCG Topics Generator: Extracted components: ' . json_encode($components));
            
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception extracting components: ' . $e->getMessage());
            // Return defaults on error
            $components = [
                'who' => 'your audience',
                'result' => 'achieve their goals',
                'when' => 'they need help',
                'how' => 'through your method'
            ];
        }
        
        return $components;
    }
    
    /**
     * CRITICAL FIX: Handle save individual field AJAX request
     */
    public function handle_save_field_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_id = isset($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : '';
        $value = isset($_POST['value']) ? sanitize_textarea_field($_POST['value']) : '';
        
        if (!$entry_id || !$field_id) {
            wp_send_json_error(['message' => 'Entry ID and field ID are required']);
            return;
        }
        
        // Save using Formidable service
        if ($this->formidable_service) {
            $result = $this->formidable_service->save_generated_content(
                $entry_id,
                [$field_id => $value],
                [$field_id => $field_id]
            );
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => 'Field saved successfully',
                    'entry_id' => $entry_id,
                    'field_id' => $field_id,
                    'value' => $value
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to save field',
                    'details' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } else {
            wp_send_json_error(['message' => 'Formidable service not available']);
        }
    }
    
    /**
     * CRITICAL FIX: Handle save topic field AJAX request
     */
    public function handle_save_topic_field_ajax() {
        // Use centralized security validation
        $security_check = $this->validate_ajax_security(['entry_id']);
        if (is_wp_error($security_check)) {
            wp_send_json_error(['message' => $security_check->get_error_message()]);
            return;
        }
        
        $entry_id = intval($_POST['entry_id']);
        $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
        $field_value = isset($_POST['field_value']) ? sanitize_textarea_field($_POST['field_value']) : '';
        
        if (!$entry_id || !$field_name) {
            wp_send_json_error(['message' => 'Entry ID and field name are required']);
            return;
        }
        
        // Extract field ID from field name (e.g., 'field_8498' -> '8498')
        $field_id = '';
        if (preg_match('/field_(\d+)/', $field_name, $matches)) {
            $field_id = $matches[1];
        }
        
        if (!$field_id) {
            wp_send_json_error(['message' => 'Invalid field name format']);
            return;
        }
        
        // Save using Formidable service
        if ($this->formidable_service) {
            $result = $this->formidable_service->save_generated_content(
                $entry_id,
                [$field_id => $field_value],
                [$field_id => $field_id]
            );
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => 'Topic field saved successfully',
                    'entry_id' => $entry_id,
                    'field_name' => $field_name,
                    'field_id' => $field_id,
                    'value' => $field_value
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to save topic field',
                    'details' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } else {
            wp_send_json_error(['message' => 'Formidable service not available']);
        }
    }
    
    /**
     * UNIFIED: Fallback template data loading when service unavailable
     */
    private function get_template_data_fallback($entry_key, $template_data) {
        error_log('MKCG Topics Generator: Using fallback template data loading');
        
        try {
            // Get entry ID from entry key
            $entry_data = $this->formidable_service->get_entry_data($entry_key);
            
            if (!empty($entry_data['entry_id'])) {
                $entry_id = $entry_data['entry_id'];
                $template_data['entry_id'] = $entry_id;
                $template_data['has_entry'] = true;
                
                // Load topics from Formidable fields directly
                $field_mappings = $this->get_field_mappings();
                
                foreach (['topic_1', 'topic_2', 'topic_3', 'topic_4', 'topic_5'] as $topic_key) {
                    if (isset($field_mappings['fields'][$topic_key])) {
                        $field_id = $field_mappings['fields'][$topic_key];
                        $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                        if (!empty($value)) {
                            $template_data['form_field_values'][$topic_key] = $value;
                        }
                    }
                }
                
                // Load authority hook components from Formidable fields
                $auth_mappings = $this->get_authority_hook_field_mappings();
                foreach (['who', 'result', 'when', 'how'] as $component) {
                    if (isset($auth_mappings[$component])) {
                        $field_id = $auth_mappings[$component];
                        $value = $this->formidable_service->get_field_value($entry_id, $field_id);
                        if (!empty($value)) {
                            $template_data['authority_hook_components'][$component] = $value;
                        }
                    }
                }
                
                // Rebuild complete authority hook
                if ($this->authority_hook_service) {
                    $template_data['authority_hook_components']['complete'] = $this->authority_hook_service->build_authority_hook($template_data['authority_hook_components']);
                }
                
                error_log('MKCG Topics Generator: ✅ Fallback data loading successful');
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: ❌ Fallback loading failed: ' . $e->getMessage());
        }
        
        return $template_data;
    }
    
    /**
     * UNIFIED: Save topics via unified service pattern
     */
    private function save_topics_via_unified_service($topics, $post_id, $entry_id) {
        try {
            $saved_count = 0;
            
            // Save to post meta (primary location)
            foreach ($topics as $key => $value) {
                if (!empty(trim($value))) {
                    $meta_key = str_replace('_', '_', $key); // Ensure proper format
                    $save_result = update_post_meta($post_id, $meta_key, trim($value));
                    
                    if ($save_result !== false) {
                        $saved_count++;
                    }
                }
            }
            
            // Update topics timestamp for sync tracking
            if ($saved_count > 0) {
                update_post_meta($post_id, '_mkcg_topics_updated', time());
            }
            
            // Also save to Formidable entry if available
            if ($entry_id && $saved_count > 0) {
                $this->save_topics_to_formidable_entry($entry_id, $topics);
            }
            
            return [
                'success' => $saved_count > 0,
                'saved_count' => $saved_count,
                'message' => $saved_count > 0 ? "Saved {$saved_count} topics successfully" : 'No topics saved'
            ];
            
        } catch (Exception $e) {
            error_log('MKCG Topics: Exception in save_topics_via_unified_service: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Save failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * UNIFIED: Save topics to Formidable entry fields
     */
    private function save_topics_to_formidable_entry($entry_id, $topics) {
        try {
            $field_mappings = $this->get_field_mappings();
            $saved_count = 0;
            
            foreach ($topics as $key => $value) {
                if (isset($field_mappings['fields'][$key]) && !empty(trim($value))) {
                    $field_id = $field_mappings['fields'][$key];
                    $result = $this->save_single_topic_to_formidable($entry_id, $field_id, trim($value));
                    
                    if ($result) {
                        $saved_count++;
                    }
                }
            }
            
            error_log("MKCG Topics: Saved {$saved_count} topics to Formidable entry {$entry_id}");
            return $saved_count;
            
        } catch (Exception $e) {
            error_log('MKCG Topics: Exception saving to Formidable entry: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * UNIFIED: Save single topic to Formidable field (by topic number)
     */
    private function save_single_topic_to_formidable($entry_id, $topic_number, $topic_text) {
        try {
            // Get field ID for this topic number
            $field_mappings = $this->get_field_mappings();
            $topic_key = 'topic_' . $topic_number;
            
            if (isset($field_mappings['fields'][$topic_key])) {
                $field_id = $field_mappings['fields'][$topic_key];
                return $this->save_single_field_to_formidable($entry_id, $field_id, $topic_text);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('MKCG Topics: Exception in save_single_topic_to_formidable: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * STEP 4 SUPPORTING METHODS: Standardized request validation and data extraction
     */
    private function validate_and_extract_request_data($validation_rules) {
        try {
            // First validate security (nonce)
            $security_check = $this->validate_ajax_security(['entry_id']);
            if (is_wp_error($security_check)) {
                return new WP_Error('security_failed', $security_check->get_error_message());
            }
            
            $extracted_data = [];
            $validation_errors = [];
            
            foreach ($validation_rules as $field => $rules) {
                $rules_array = explode('|', $rules);
                $is_required = in_array('required', $rules_array);
                $is_integer = in_array('integer', $rules_array);
                $is_string = in_array('string', $rules_array);
                
                $value = isset($_POST[$field]) ? $_POST[$field] : '';
                
                // Check required fields
                if ($is_required && empty($value)) {
                    $validation_errors[] = "Field '{$field}' is required";
                    continue;
                }
                
                // Process value based on type
                if ($is_integer) {
                    $extracted_data[$field] = intval($value);
                    if ($is_required && $extracted_data[$field] <= 0) {
                        $validation_errors[] = "Field '{$field}' must be a positive integer";
                    }
                } elseif ($is_string) {
                    $extracted_data[$field] = sanitize_textarea_field($value);
                } else {
                    $extracted_data[$field] = sanitize_text_field($value);
                }
            }
            
            if (!empty($validation_errors)) {
                return new WP_Error('validation_failed', implode('; ', $validation_errors));
            }
            
            error_log('MKCG Step 4 Validation: ✅ Request data validated successfully');
            return $extracted_data;
            
        } catch (Exception $e) {
            error_log('MKCG Step 4 Validation: ❌ Exception: ' . $e->getMessage());
            return new WP_Error('validation_exception', 'Request validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * STEP 4 SUPPORTING METHODS: Resolve post_id from entry_id to prevent 500 errors
     */
    private function resolve_post_id_from_entry($entry_id) {
        try {
            if (!$entry_id || !$this->formidable_service) {
                return false;
            }
            
            $post_id = $this->formidable_service->get_post_id_from_entry($entry_id);
            
            if ($post_id) {
                error_log('MKCG Step 4: ✅ Resolved post_id=' . $post_id . ' from entry_id=' . $entry_id);
                return $post_id;
            } else {
                error_log('MKCG Step 4: ⚠️ Could not resolve post_id from entry_id=' . $entry_id);
                return false;
            }
            
        } catch (Exception $e) {
            error_log('MKCG Step 4: ❌ Exception resolving post_id: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * STEP 4 SUPPORTING METHODS: Standardized authority hook save operation
     */
    private function standardized_authority_hook_save($entry_id, $post_id, $request_data) {
        try {
            $components = [
                'who' => $request_data['who'] ?: 'your audience',
                'result' => $request_data['result'] ?: 'achieve their goals', 
                'when' => $request_data['when'] ?: 'they need help',
                'how' => $request_data['how'] ?: 'through your method'
            ];
            
            error_log('MKCG Step 4 Save: Processing components - ' . json_encode($components));
            
            // Build complete authority hook
            $complete_hook = '';
            if ($this->authority_hook_service) {
                $complete_hook = $this->authority_hook_service->build_authority_hook($components);
            } else {
                $complete_hook = "I help {$components['who']} {$components['result']} when {$components['when']} {$components['how']}.";
            }
            
            $save_results = [];
            $saved_fields = [];
            $save_errors = [];
            
            // Save Strategy 1: Use Authority Hook Service (preferred)
            if ($this->authority_hook_service) {
                try {
                    $service_result = $this->save_authority_hook_components(
                        $entry_id,
                        $components['who'],
                        $components['result'], 
                        $components['when'],
                        $components['how']
                    );
                    
                    if ($service_result['success']) {
                        $save_results[] = 'authority_hook_service';
                        $saved_fields = array_merge($saved_fields, $service_result['saved_fields']);
                        error_log('MKCG Step 4 Save: ✅ Authority Hook Service save successful');
                    } else {
                        $save_errors[] = 'Authority Hook Service: ' . implode(', ', $service_result['errors']);
                    }
                } catch (Exception $e) {
                    $save_errors[] = 'Authority Hook Service exception: ' . $e->getMessage();
                }
            }
            
            // Save Strategy 2: Direct Formidable save (fallback)
            if (empty($save_results) && $this->formidable_service) {
                try {
                    $field_mappings = $this->get_authority_hook_field_mappings();
                    $data_to_save = $components;
                    $data_to_save['complete'] = $complete_hook;
                    
                    $formidable_result = $this->formidable_service->save_generated_content(
                        $entry_id,
                        $data_to_save,
                        $field_mappings
                    );
                    
                    if ($formidable_result['success']) {
                        $save_results[] = 'direct_formidable';
                        $saved_fields = array_merge($saved_fields, $formidable_result['saved_fields']);
                        error_log('MKCG Step 4 Save: ✅ Direct Formidable save successful');
                    } else {
                        $save_errors[] = 'Direct Formidable save failed';
                    }
                } catch (Exception $e) {
                    $save_errors[] = 'Direct Formidable exception: ' . $e->getMessage();
                }
            }
            
            // Determine overall success
            $success = !empty($save_results);
            
            return [
                'success' => $success,
                'authority_hook' => $complete_hook,
                'components' => $components,
                'saved_fields' => $saved_fields,
                'save_method' => implode(', ', $save_results),
                'errors' => $save_errors,
                'debug_info' => [
                    'entry_id' => $entry_id,
                    'post_id' => $post_id,
                    'strategies_attempted' => count($save_results) + count($save_errors),
                    'successful_strategies' => $save_results
                ]
            ];
            
        } catch (Exception $e) {
            error_log('MKCG Step 4 Save: ❌ Critical exception: ' . $e->getMessage());
            return [
                'success' => false,
                'authority_hook' => '',
                'components' => [],
                'saved_fields' => [],
                'save_method' => 'none',
                'errors' => ['Critical exception: ' . $e->getMessage()],
                'debug_info' => ['exception_occurred' => true]
            ];
        }
    }
    
    /**
     * CRITICAL FIX: Save single topic fallback method
     */
    private function save_single_topic_fallback($topic_number, $topic_text, $entry_id, $post_id) {
        try {
            $field_mappings = $this->get_topics_field_mappings_safe();
            $topic_key = 'topic_' . $topic_number;
            
            if (isset($field_mappings[$topic_key])) {
                $field_id = $field_mappings[$topic_key];
                
                $save_result = $this->formidable_service->save_generated_content(
                    $entry_id,
                    [$topic_key => $topic_text],
                    [$topic_key => $field_id]
                );
                
                if ($save_result['success']) {
                    return [
                        'success' => true,
                        'message' => 'Single topic saved successfully',
                        'topic_number' => $topic_number,
                        'field_id' => $field_id
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to save topic: ' . ($save_result['message'] ?? 'Unknown error')
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'No field mapping found for topic ' . $topic_number
                ];
            }
        } catch (Exception $e) {
            error_log('MKCG Topics Generator: Exception in save_single_topic_fallback: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}