<?php
/**
 * Enhanced Offers Generator - Pure Pods Integration
 * Single responsibility: Generate service offers using Pods "guests" custom post type
 * Uses: Dynamic post_id from query string, no Formidable dependencies
 */

class Enhanced_Offers_Generator {
    
    private $api_service;
    private $pods_service;
    
    /**
     * Constructor - Pure Pods service integration
     */
    public function __construct($api_service) {
        $this->api_service = $api_service;
        
        // Initialize Pods service
        require_once dirname(__FILE__) . '/../services/class-mkcg-pods-service.php';
        $this->pods_service = new MKCG_Pods_Service();
        
        $this->init();
    }
    
    /**
     * Initialize - direct and simple
     */
    public function init() {
        // AJAX handlers now initialized at root level in main plugin file
        // This prevents timing issues and ensures handlers are registered properly
        
        // Add any WordPress hooks needed
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue required scripts
     */
    public function enqueue_scripts() {
        // Only enqueue on offers generator pages
        if (!$this->is_offers_page()) {
            return;
        }
        
        wp_enqueue_script(
            'enhanced-offers-generator',
            plugins_url('assets/js/offers-generator.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
        
        wp_localize_script('enhanced-offers-generator', 'offersVars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkcg_nonce')
        ]);
    }
    
    /**
     * Check if current page is offers generator
     */
    private function is_offers_page() {
        // Simple check - customize as needed
        return is_page() && (strpos(get_post()->post_content, '[offers_generator]') !== false);
    }
    
    /**
     * Get template data using dynamic post_id - Pure Pods integration
     */
    public function get_template_data($post_id = null) {
        error_log('MKCG Offers Generator: Starting get_template_data - Pure Pods Integration');
        
        // Get post_id from request parameters or use provided one
        if (!$post_id) {
            $post_id = $this->get_post_id_from_request();
        }
        
        if (!$post_id) {
            error_log('MKCG Offers Generator: No valid post ID found');
            return $this->get_default_template_data();
        }
        
        // Validate this is a guests post
        if (!$this->pods_service->is_guests_post($post_id)) {
            error_log('MKCG Offers Generator: Post ' . $post_id . ' is not a guests post type');
            return $this->get_default_template_data();
        }
        
        error_log('MKCG Offers Generator: Loading data for guests post ID: ' . $post_id);
        
        // Load ALL data from Pods service
        $guest_data = $this->pods_service->get_guest_data($post_id);
        
        // Get business data from post meta
        $business_data = [
            'business_type' => get_post_meta($post_id, 'offers_business_type', true) ?: '',
            'target_audience' => get_post_meta($post_id, 'offers_target_audience', true) ?: '',
            'price_range' => get_post_meta($post_id, 'offers_price_range', true) ?: 'mid',
            'delivery_method' => get_post_meta($post_id, 'offers_delivery_method', true) ?: 'online',
            'offer_count' => get_post_meta($post_id, 'offers_offer_count', true) ?: 5
        ];
        
        // Transform to template format (no Formidable references)
        $template_data = [
            'post_id' => $post_id,
            'has_data' => $guest_data['has_data'],
            'authority_hook_components' => $guest_data['authority_hook_components'],
            'business_data' => $business_data,
            'contact' => $guest_data['contact'],
            'messaging' => $guest_data['messaging']
        ];
        
        error_log('MKCG Offers Generator: Data loaded successfully from Pods service');
        return $template_data;
    }
    
    /**
     * Get post_id from request parameters - Dynamic post ID detection
     */
    private function get_post_id_from_request() {
        error_log('MKCG Offers Generator: Starting dynamic post ID detection');
        
        // Strategy 1: Direct post_id parameter (primary method)
        if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
            $post_id = intval($_GET['post_id']);
            error_log('MKCG Offers Generator: Found post_id parameter: ' . $post_id);
            
            // Validate it's a guests post
            if (get_post($post_id) && get_post($post_id)->post_type === 'guests') {
                return $post_id;
            } else {
                error_log('MKCG Offers Generator: Post ID ' . $post_id . ' is not a valid guests post');
            }
        }
        
        // Strategy 2: Check for global post context
        global $post;
        if ($post && $post->ID && $post->post_type === 'guests') {
            error_log('MKCG Offers Generator: Using global post context: ' . $post->ID);
            return $post->ID;
        }
        
        // Strategy 3: Check if we're on a guest post page
        if (is_single() || is_page()) {
            $current_id = get_the_ID();
            if ($current_id && get_post_type($current_id) === 'guests') {
                error_log('MKCG Offers Generator: Found guest post page: ' . $current_id);
                return $current_id;
            }
        }
        
        // Strategy 4: Look for the most recent guest post for testing
        $recent_guest = get_posts([
            'post_type' => 'guests',
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (!empty($recent_guest)) {
            $post_id = $recent_guest[0]->ID;
            error_log('MKCG Offers Generator: Using most recent guest post for testing: ' . $post_id);
            return $post_id;
        }
        
        error_log('MKCG Offers Generator: No valid post ID found with any strategy');
        return 0;
    }
    
    /**
     * Get default template data structure - Pure Pods
     * Updated to check for entry parameters and not provide defaults when no entry param
     */
    private function get_default_template_data() {
        // Check for entry parameter - don't show defaults if no entry param provided
        $has_entry_param = isset($_GET['entry']) || isset($_GET['post_id']) || 
                           (isset($_GET['frm_action']) && $_GET['frm_action'] === 'edit');
        
        if (!$has_entry_param) {
            // NO ENTRY PARAM: Return empty structure with no defaults
            error_log('MKCG Offers Generator: No entry parameter found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'business_data' => [
                    'business_type' => '',
                    'target_audience' => '',
                    'price_range' => 'mid',
                    'delivery_method' => 'online',
                    'offer_count' => 5
                ],
                'contact' => [],
                'messaging' => [],
                'no_entry_param' => true
            ];
        } else {
            // HAS ENTRY PARAM: Return empty structure (entry param exists but no data found)
            error_log('MKCG Offers Generator: Entry parameter exists but no data found - returning empty structure');
            return [
                'post_id' => 0,
                'has_data' => false,
                'authority_hook_components' => [
                    'who' => '',
                    'what' => '',
                    'when' => '',
                    'how' => '',
                    'where' => '',
                    'why' => '',
                    'complete' => ''
                ],
                'business_data' => [
                    'business_type' => '',
                    'target_audience' => '',
                    'price_range' => 'mid',
                    'delivery_method' => 'online',
                    'offer_count' => 5
                ],
                'contact' => [],
                'messaging' => []
            ];
        }
    }
    
    /**
     * Generate offers using API service
     */
    public function generate_offers($authority_hook, $business_data = []) {
        if (empty($authority_hook)) {
            return [
                'success' => false,
                'message' => 'Authority hook is required'
            ];
        }
        
        $prompt = $this->build_prompt($authority_hook, $business_data);
        
        $api_response = $this->api_service->generate_content($prompt, 'offers');
        
        if (!$api_response['success']) {
            return $api_response;
        }
        
        $offers = $this->parse_offers_from_response($api_response['content']);
        
        return [
            'success' => true,
            'offers' => $offers,
            'count' => count($offers)
        ];
    }
    
    /**
     * Save business data using post meta
     */
    public function save_business_data($post_id, $business_data) {
        if (!$post_id || empty($business_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        $saved_fields = [];
        
        // Save each business data field as post meta
        foreach ($business_data as $key => $value) {
            $meta_key = 'offers_' . $key;
            $result = update_post_meta($post_id, $meta_key, sanitize_text_field($value));
            $saved_fields[$key] = $result;
        }
        
        return [
            'success' => true,
            'message' => 'Business data saved successfully',
            'saved_fields' => $saved_fields
        ];
    }
    
    /**
     * Save authority hook using Pods service
     */
    public function save_authority_hook($post_id, $authority_hook_data) {
        if (!$post_id || empty($authority_hook_data)) {
            return [
                'success' => false,
                'message' => 'Invalid parameters'
            ];
        }
        
        // Use Pods service for saving
        return $this->pods_service->save_authority_hook_components($post_id, $authority_hook_data);
    }
    
    /**
     * Build prompt for API service
     */
    private function build_prompt($authority_hook, $business_data = []) {
        $prompt = "Generate compelling service offers based on this expert's authority:\n\n";
        $prompt .= "Expert Authority: {$authority_hook}\n\n";
        
        if (!empty($business_data['business_type'])) {
            $prompt .= "Business Type: {$business_data['business_type']}\n";
        }
        
        if (!empty($business_data['target_audience'])) {
            $prompt .= "Target Audience: {$business_data['target_audience']}\n";
        }
        
        if (!empty($business_data['price_range'])) {
            $price_ranges = [
                'budget' => '$100-$500',
                'mid' => '$500-$2,000',
                'premium' => '$2,000-$10,000',
                'luxury' => '$10,000+'
            ];
            $price_range = $price_ranges[$business_data['price_range']] ?? $business_data['price_range'];
            $prompt .= "Price Range: {$price_range}\n";
        }
        
        if (!empty($business_data['delivery_method'])) {
            $prompt .= "Delivery Method: {$business_data['delivery_method']}\n";
        }
        
        $offer_count = $business_data['offer_count'] ?? 5;
        
        $prompt .= "\nRequirements:\n";
        $prompt .= "- Create {$offer_count} distinct service offers\n";
        $prompt .= "- Include a tiered structure: Free lead magnet, Low-ticket offer, Premium offer\n";
        $prompt .= "- Make offers directly relate to the expert's authority area\n";
        $prompt .= "- Include specific deliverables and value propositions\n";
        $prompt .= "- Format each offer with: Title, Description, Price Point\n";
        $prompt .= "- Use numbered list format (1., 2., 3., etc.)\n\n";
        
        return $prompt;
    }
    
    /**
     * Parse offers from API response
     */
    private function parse_offers_from_response($response) {
        $offers = [];
        
        // Split by lines and look for numbered items
        $lines = explode("\n", $response);
        $current_offer = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Match numbered format (1., 2., etc.)
            if (preg_match('/^\d+\.\s*(.+)/', $line, $matches)) {
                // Save previous offer if exists
                if ($current_offer) {
                    $offers[] = $current_offer;
                }
                
                // Start new offer
                $current_offer = [
                    'title' => trim($matches[1], ' "\''),
                    'description' => '',
                    'price' => ''
                ];
            } elseif ($current_offer && !empty($line)) {
                // Add to description if we have a current offer
                if (!empty($current_offer['description'])) {
                    $current_offer['description'] .= ' ';
                }
                $current_offer['description'] .= $line;
            }
        }
        
        // Don't forget the last offer
        if ($current_offer) {
            $offers[] = $current_offer;
        }
        
        // If parsing failed, try simpler approach
        if (empty($offers)) {
            $lines = explode("\n", $response);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && count($offers) < 10) {
                    $offers[] = [
                        'title' => $line,
                        'description' => '',
                        'price' => ''
                    ];
                }
            }
        }
        
        return $offers;
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
            $errors[] = 'Authority Hook must be at least 10 characters long';
        }
        
        if (!empty($data['business_type']) && !in_array($data['business_type'], ['consulting', 'coaching', 'training', 'service', 'product', 'other'])) {
            $errors[] = 'Invalid business type';
        }
        
        if (!empty($data['price_range']) && !in_array($data['price_range'], ['budget', 'mid', 'premium', 'luxury'])) {
            $errors[] = 'Invalid price range';
        }
        
        if (!empty($data['offer_count']) && (intval($data['offer_count']) < 1 || intval($data['offer_count']) > 10)) {
            $errors[] = 'Offer count must be between 1 and 10';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
