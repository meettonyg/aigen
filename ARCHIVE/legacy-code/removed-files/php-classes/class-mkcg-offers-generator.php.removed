<?php
/**
 * MKCG Offers Generator
 * Generates service offers and packages based on Authority Hook and business details
 */

class MKCG_Offers_Generator extends MKCG_Base_Generator {
    
    protected $generator_type = 'offers';
    
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
            'business_type' => [
                'type' => 'select',
                'label' => 'Business Type',
                'options' => [
                    'consulting' => 'Consulting',
                    'coaching' => 'Coaching',
                    'training' => 'Training',
                    'service' => 'Service Provider',
                    'product' => 'Product Business',
                    'other' => 'Other'
                ],
                'required' => true
            ],
            'target_audience' => [
                'type' => 'textarea',
                'label' => 'Target Audience',
                'required' => true,
                'description' => 'Who are your ideal clients?'
            ],
            'price_range' => [
                'type' => 'select',
                'label' => 'Price Range',
                'options' => [
                    'budget' => 'Budget ($100-$500)',
                    'mid' => 'Mid-range ($500-$2,000)',
                    'premium' => 'Premium ($2,000-$10,000)',
                    'luxury' => 'Luxury ($10,000+)'
                ],
                'required' => false
            ],
            'delivery_method' => [
                'type' => 'select',
                'label' => 'Delivery Method',
                'options' => [
                    'online' => 'Online/Virtual',
                    'in-person' => 'In-Person',
                    'hybrid' => 'Hybrid',
                    'self-paced' => 'Self-Paced',
                    'group' => 'Group Sessions'
                ],
                'required' => false
            ],
            'offer_count' => [
                'type' => 'number',
                'label' => 'Number of Offers',
                'min' => 1,
                'max' => 10,
                'default' => 5,
                'required' => false
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
        
        if (empty($data['business_type'])) {
            $errors[] = 'Business Type is required';
        }
        
        if (empty($data['target_audience'])) {
            $errors[] = 'Target Audience is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Build prompt for offer generation
     */
    public function build_prompt($data) {
        $authority_hook = $data['authority_hook'];
        $business_type = $data['business_type'];
        $target_audience = $data['target_audience'];
        $price_range = $data['price_range'] ?? 'mid';
        $delivery_method = $data['delivery_method'] ?? 'online';
        $offer_count = intval($data['offer_count'] ?? 5);
        
        $prompt = "You are an expert business strategist specializing in creating compelling service offers and packages. Your task is to generate **$offer_count unique and attractive offers** based on the expert's authority and business details.

**EXPERT AUTHORITY:** \"$authority_hook\"

**BUSINESS DETAILS:**
- Business Type: $business_type
- Target Audience: $target_audience
- Price Range: $price_range
- Delivery Method: $delivery_method

**REQUIREMENTS FOR EACH OFFER:**
1. **Must align with the expert's authority** and demonstrated expertise
2. **Address specific pain points** of the target audience
3. **Include clear value proposition** and outcomes
4. **Be compelling and results-focused**
5. **Match the specified price range** and delivery method
6. **Have an engaging, benefit-driven title**

**OFFER STRUCTURE FOR EACH:**
- **Title:** Compelling, benefit-focused name
- **Description:** 2-3 sentences explaining what it includes
- **Outcome:** Clear result/transformation clients will achieve
- **Ideal For:** Specific type of client who would benefit most

**EXAMPLES OF STRONG OFFERS:**
1. **\"90-Day Revenue Acceleration Program\"** - Complete system to help service businesses increase monthly revenue by 40% through proven client acquisition strategies. Includes weekly coaching calls, custom marketing templates, and direct access to expert guidance. *Ideal for: Service-based business owners ready to scale.*

2. **\"Authority Builder Masterclass\"** - Intensive workshop to establish yourself as the go-to expert in your industry within 60 days. Covers content strategy, media positioning, and thought leadership tactics. *Ideal for: Professionals looking to build industry recognition.*

**NOW GENERATE $offer_count UNIQUE OFFERS** following this structure, formatted as a numbered list. Make each offer distinct and valuable.";
        
        return $prompt;
    }
    
    /**
     * Format API response
     */
    public function format_output($api_response) {
        // The API service handles offer formatting
        if (is_array($api_response)) {
            return [
                'offers' => $api_response,
                'count' => count($api_response)
            ];
        }
        
        // Parse offers from response
        $offers = [];
        
        // Try to extract numbered offers
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $api_response, $matches)) {
            $offers = array_map('trim', $matches[1]);
        } else {
            // Fallback to line splitting
            $lines = explode("\n", $api_response);
            $offers = array_filter(array_map('trim', $lines));
        }
        
        return [
            'offers' => $offers,
            'count' => count($offers)
        ];
    }
    
    /**
     * Get generator-specific input
     */
    protected function get_generator_specific_input() {
        return [
            'business_type' => isset($_POST['business_type']) ? sanitize_text_field($_POST['business_type']) : '',
            'target_audience' => isset($_POST['target_audience']) ? sanitize_textarea_field($_POST['target_audience']) : '',
            'price_range' => isset($_POST['price_range']) ? sanitize_text_field($_POST['price_range']) : 'mid',
            'delivery_method' => isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'online',
            'offer_count' => isset($_POST['offer_count']) ? intval($_POST['offer_count']) : 5
        ];
    }
    
    /**
     * Get field mappings for Formidable
     */
    protected function get_field_mappings() {
        return [
            'offers' => 10363, // Field ID for offers
            'offer_count' => 10364 // Field ID for offer count
        ];
    }
    
    /**
     * Get API options
     */
    protected function get_api_options($input_data) {
        return [
            'temperature' => 0.8,
            'max_tokens' => 2000
        ];
    }
    
    /**
     * Initialize legacy AJAX actions for backwards compatibility
     */
    public function init() {
        parent::init();
        
        // Add legacy AJAX actions if they exist
        add_action('wp_ajax_generate_offers', [$this, 'handle_ajax_generation']);
        add_action('wp_ajax_nopriv_generate_offers', [$this, 'handle_ajax_generation']);
    }
}