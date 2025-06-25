<?php
/**
 * MKCG Authority Hook Service
 * Manages the shared Authority Hook component across all generators
 */

class MKCG_Authority_Hook_Service {
    
    private $formidable_service;
    
    public function __construct($formidable_service) {
        $this->formidable_service = $formidable_service;
    }
    
    /**
     * Get Authority Hook for a given entry
     */
    public function get_authority_hook($entry_id) {
        return $this->formidable_service->find_authority_hook($entry_id);
    }
    
    /**
     * Build Authority Hook from components (from Biography generator pattern)
     */
    public function build_authority_hook($components) {
        $who = isset($components['who']) ? $components['who'] : 'your audience';
        $result = isset($components['result']) ? $components['result'] : 'achieve results';
        $when = isset($components['when']) ? $components['when'] : 'they need you';
        $how = isset($components['how']) ? $components['how'] : 'through your method';
        
        // Format the WHO field for multiple audiences
        $formatted_who = $this->format_audience_list($who);
        
        $hook = 'I help ' . $formatted_who;
        if ($result) $hook .= ' ' . $result;
        if ($when) $hook .= ' when ' . $when;
        if ($how) $hook .= ' ' . $how;
        $hook .= '.';
        
        return $hook;
    }
    
    /**
     * Format audience list for proper grammar
     */
    private function format_audience_list($audience_string) {
        if (strpos($audience_string, ',') === false) {
            return $audience_string;
        }
        
        $audiences = array_map('trim', explode(',', $audience_string));
        $audiences = array_filter($audiences); // Remove empty values
        
        if (count($audiences) === 0) {
            return 'your audience';
        } elseif (count($audiences) === 1) {
            return $audiences[0];
        } elseif (count($audiences) === 2) {
            return implode(' and ', $audiences);
        } else {
            $last_audience = array_pop($audiences);
            return implode(', ', $audiences) . ', and ' . $last_audience;
        }
    }
    
    /**
     * Generate Authority Hook component HTML (shared across generators)
     */
    public function render_authority_hook_component($current_values = [], $generator_type = '') {
        $who = isset($current_values['who']) ? $current_values['who'] : '';
        $result = isset($current_values['result']) ? $current_values['result'] : '';
        $when = isset($current_values['when']) ? $current_values['when'] : '';
        $how = isset($current_values['how']) ? $current_values['how'] : '';
        
        ob_start();
        ?>
        <div class="authority-hook-section" data-generator="<?php echo esc_attr($generator_type); ?>">
            <div class="authority-hook-builder">
                <h3>Authority Hook Builder</h3>
                <p class="description">Build your authority statement using the WHO-WHAT-WHEN-HOW framework:</p>
                
                <!-- WHO Field -->
                <div class="authority-component">
                    <label for="mkcg-who">WHO do you help?</label>
                    <input type="text" id="mkcg-who" name="who" value="<?php echo esc_attr($who); ?>" 
                           placeholder="e.g., busy executives, small business owners">
                    <small>Separate multiple audiences with commas</small>
                </div>
                
                <!-- RESULT Field -->
                <div class="authority-component">
                    <label for="mkcg-result">WHAT result do you help them achieve?</label>
                    <input type="text" id="mkcg-result" name="result" value="<?php echo esc_attr($result); ?>" 
                           placeholder="e.g., increase revenue, save time, reduce stress">
                </div>
                
                <!-- WHEN Field -->
                <div class="authority-component">
                    <label for="mkcg-when">WHEN do they need this help?</label>
                    <input type="text" id="mkcg-when" name="when" value="<?php echo esc_attr($when); ?>" 
                           placeholder="e.g., during rapid growth, when scaling their team">
                </div>
                
                <!-- HOW Field -->
                <div class="authority-component">
                    <label for="mkcg-how">HOW do you help them?</label>
                    <input type="text" id="mkcg-how" name="how" value="<?php echo esc_attr($how); ?>" 
                           placeholder="e.g., through my proven system, with strategic consulting">
                </div>
            </div>
            
            <!-- Live Preview -->
            <div class="authority-hook-preview">
                <h4>Your Authority Hook:</h4>
                <div id="authority-hook-content" class="hook-preview-content">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <button type="button" id="copy-authority-hook-btn" class="copy-btn">Copy to Clipboard</button>
            </div>
            
            <!-- Hidden field to store the complete hook -->
            <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="">
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Extract Authority Hook components from a complete hook string
     */
    public function parse_authority_hook($hook_string) {
        $components = [
            'who' => '',
            'result' => '',
            'when' => '',
            'how' => ''
        ];
        
        // Basic parsing - this could be enhanced with more sophisticated NLP
        // For now, we'll try to extract based on common patterns
        
        if (preg_match('/I help (.*?) (achieve|get|obtain|reach|build|create|develop|increase|decrease|improve|solve|overcome) (.*?) when (.*?) (through|by|using|with|via) (.*?)\./', $hook_string, $matches)) {
            $components['who'] = trim($matches[1]);
            $components['result'] = trim($matches[2] . ' ' . $matches[3]);
            $components['when'] = trim($matches[4]);
            $components['how'] = trim($matches[6]);
        }
        
        return $components;
    }
    
    /**
     * Validate Authority Hook components
     */
    public function validate_authority_hook($components) {
        $errors = [];
        
        if (empty($components['who'])) {
            $errors[] = 'WHO field is required - specify your target audience';
        }
        
        if (empty($components['result'])) {
            $errors[] = 'RESULT field is required - specify what outcome you provide';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get suggested components based on industry/niche
     */
    public function get_suggested_components($industry = '') {
        $suggestions = [
            'default' => [
                'who' => ['business owners', 'entrepreneurs', 'professionals', 'executives'],
                'result' => ['increase revenue', 'save time', 'reduce costs', 'improve efficiency'],
                'when' => ['during growth phases', 'facing challenges', 'planning expansion', 'seeking improvement'],
                'how' => ['through proven strategies', 'with personalized coaching', 'using systematic approaches', 'via expert guidance']
            ],
            'business' => [
                'who' => ['small business owners', 'startup founders', 'scaling companies', 'established businesses'],
                'result' => ['increase profits', 'streamline operations', 'expand market share', 'optimize processes'],
                'when' => ['during rapid growth', 'facing operational challenges', 'entering new markets', 'scaling their team'],
                'how' => ['through strategic consulting', 'with proven frameworks', 'using data-driven insights', 'via systematic implementation']
            ],
            'health' => [
                'who' => ['busy professionals', 'health-conscious individuals', 'people struggling with wellness', 'fitness enthusiasts'],
                'result' => ['achieve optimal health', 'lose weight sustainably', 'increase energy levels', 'improve overall wellness'],
                'when' => ['feeling overwhelmed', 'struggling with consistency', 'plateauing in progress', 'seeking lasting change'],
                'how' => ['through personalized programs', 'with holistic approaches', 'using science-based methods', 'via sustainable lifestyle changes']
            ]
        ];
        
        return isset($suggestions[$industry]) ? $suggestions[$industry] : $suggestions['default'];
    }
}