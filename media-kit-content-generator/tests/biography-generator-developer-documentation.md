# Biography Generator Developer Documentation
## Media Kit Content Generator - Technical Reference

### 📋 **Developer Documentation Overview**

This technical reference provides comprehensive documentation for developers working with the Biography Generator component of the Media Kit Content Generator, including architecture, API references, and extension points.

---

## 🏗️ **Architecture Overview**

### **Component Architecture**

The Biography Generator follows a modular architecture with clear separation of concerns:

1. **PHP Backend**: Handles data processing, API integration, and WordPress hooks
2. **JavaScript Frontend**: Manages user interactions, form handling, and dynamic updates
3. **Templates**: Define the markup structure for generator and results pages
4. **CSS Styling**: Unified BEM-based styling integrated with design tokens

### **File Structure**

```
media-kit-content-generator/
├── assets/
│   ├── css/
│   │   └── mkcg-unified-styles.css (includes biography styles)
│   └── js/
│       └── generators/
│           ├── biography-generator.js (main generator logic)
│           └── biography-results.js (results page logic)
├── includes/
│   ├── generators/
│   │   └── enhanced_biography_generator.php (backend class)
│   └── services/
│       ├── class-mkcg-authority-hook-service.php (shared service)
│       └── class-mkcg-impact-intro-service.php (shared service)
└── templates/
    └── generators/
        └── biography/
            ├── default.php (generator template)
            └── results.php (results template)
```

### **Design Patterns**

The Biography Generator implements several key design patterns:

1. **MVC Pattern**:
   - Model: PHP class for data handling
   - View: Template files for rendering
   - Controller: JavaScript for user interactions

2. **Factory Pattern**:
   - Generator instantiation via WordPress hooks
   - Service instantiation via dependency injection

3. **Observer Pattern**:
   - Event-based communication between components
   - WordPress hooks for extensibility

4. **Strategy Pattern**:
   - Interchangeable generators with consistent interfaces
   - Configurable generation strategies

---

## 🔧 **PHP Backend Reference**

### **Class: MKCG_Enhanced_Biography_Generator**

The main PHP class that handles all backend functionality for the Biography Generator.

#### Key Properties

```php
/**
 * Version for cache busting and feature tracking
 */
const VERSION = '2.0';

/**
 * Rate limiting settings for API protection
 */
const RATE_LIMIT_REQUESTS = 10;
const RATE_LIMIT_PERIOD = 3600; // 1 hour

/**
 * Maximum API request timeout
 */
const API_TIMEOUT = 60;

/**
 * Cache duration for API responses
 */
const CACHE_DURATION = 1800; // 30 minutes

/**
 * OpenAI model to use for generation
 */
const OPENAI_MODEL = 'gpt-4';
```

#### Core Methods

```php
/**
 * Initialize the generator with enhanced features
 */
public function __construct()

/**
 * Get template data for rendering
 * 
 * @return array Template data
 */
public function get_template_data()

/**
 * Get results page template data
 * 
 * @return array Results template data
 */
public function get_results_data()

/**
 * AJAX handler for generating biography
 */
public function ajax_generate_biography()

/**
 * AJAX handler for modifying biography tone
 */
public function ajax_modify_biography_tone()

/**
 * AJAX handler for saving biography to post meta
 */
public function ajax_save_biography_to_post_meta()
```

#### Utility Methods

```php
/**
 * Validate and sanitize input
 */
private function validate_and_sanitize_input($data)

/**
 * Generate biography with comprehensive error handling
 */
private function generate_biography($data)

/**
 * Modify biography tone
 */
private function modify_biography_tone($data)

/**
 * Generate cache key for biography request
 */
private function generate_cache_key($data)

/**
 * Check rate limit for user
 */
private function check_rate_limit()
```

### **Service Integration**

The Biography Generator integrates with shared services:

```php
/**
 * Initialize centralized services
 */
private function init_services()
{
    // Initialize Authority Hook Service
    if (class_exists('MKCG_Authority_Hook_Service')) {
        $this->authority_hook_service = new MKCG_Authority_Hook_Service();
    }
    
    // Initialize Impact Intro Service
    if (class_exists('MKCG_Impact_Intro_Service')) {
        $this->impact_intro_service = new MKCG_Impact_Intro_Service();
    }
}
```

### **WordPress Hooks**

The Biography Generator registers several WordPress hooks:

```php
/**
 * Register WordPress hooks
 */
private function register_hooks()
{
    // Register scripts and styles
    add_action('mkcg_register_scripts', [$this, 'register_scripts']);
    add_action('mkcg_enqueue_generator_scripts', [$this, 'enqueue_scripts'], 10, 1);
    
    // Add meta boxes for biography data
    add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
    
    // Save post hooks
    add_action('save_post', [$this, 'save_post_data'], 10, 2);
}
```

---

## 📝 **JavaScript Architecture**

### **Biography Generator Module**

The main JavaScript module that handles the generator functionality.

#### Core Structure

```javascript
/**
 * Biography Generator - Module Pattern
 */
const BiographyGenerator = {
    
    // Data storage
    fields: {
        // Authority Hook components
        who: '',
        what: '',
        when: '',
        how: '',
        
        // Impact Intro components  
        where: '',
        why: '',
        
        // Biography-specific fields
        name: '',
        title: '',
        organization: '',
        tone: 'professional',
        length: 'medium',
        pov: 'third',
        existingBio: '',
        notes: ''
    },
    
    // Generated biographies storage
    biographies: {
        short: '',
        medium: '',
        long: ''
    },
    
    // Form metadata
    metadata: {
        postId: 0,
        entryId: 0,
        entryKey: '',
        nonce: '',
        hasData: false
    },
    
    // Core methods
    init: function() {
        // Initialization logic
    },
    
    // ...additional methods
};
```

#### Key Methods

```javascript
/**
 * Initialize Biography Generator
 */
init: function()

/**
 * Load existing data from PHP or defaults
 */
loadExistingData: function()

/**
 * Bind event listeners to DOM elements
 */
bindEvents: function()

/**
 * Update display based on current data
 */
updateDisplay: function()

/**
 * Generate biography using AJAX
 */
generateBiography: function()

/**
 * Display generated results
 */
displayResults: function(data)

/**
 * Save biographies to WordPress
 */
saveBiographies: function()
```

### **Biography Results Module**

The JavaScript module that handles the results page functionality.

#### Core Structure

```javascript
/**
 * Biography Results - Module Pattern
 */
const BiographyResults = {
    
    // Configuration
    config: {
        selectors: {
            // DOM selectors
        },
        endpoints: {
            // AJAX endpoints
        },
        classes: {
            // CSS classes
        },
        animations: {
            // Animation settings
        }
    },
    
    // Data storage
    data: {
        postId: 0,
        entryId: 0,
        nonce: '',
        currentTab: 'short',
        currentTone: 'professional',
        currentPov: 'third',
        biographies: {
            short: '',
            medium: '',
            long: ''
        },
        // ...additional data
    },
    
    // Core methods
    init: function() {
        // Initialization logic
    },
    
    // ...additional methods
};
```

#### Key Methods

```javascript
/**
 * Initialize Results functionality
 */
init: function()

/**
 * Cache DOM elements for performance
 */
cacheElements: function()

/**
 * Attach event listeners
 */
attachEventListeners: function()

/**
 * Switch between biography tabs
 */
switchTabEnhanced: function(tab)

/**
 * Update biography tone
 */
updateTone: function()

/**
 * Save biographies to WordPress
 */
saveBiographies: function(silent)
```

---

## 🎨 **Template Structure**

### **Default Template (default.php)**

The main generator template follows a two-panel layout:

```php
<div class="generator__container biography-generator" data-generator="biography">
    <div class="generator__header">
        <!-- Generator title and subtitle -->
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Authority Hook Section -->
            <div class="generator__authority-hook">
                <!-- Authority Hook content and controls -->
            </div>
            
            <!-- Authority Hook Builder -->
            <div class="generator__builder generator__builder--hidden" id="biography-generator-authority-hook-builder">
                <!-- Authority Hook Builder content -->
            </div>
            
            <!-- Impact Intro Section -->
            <div class="generator__authority-hook biography-generator__impact-intro">
                <!-- Impact Intro content and controls -->
            </div>
            
            <!-- Impact Intro Builder -->
            <div class="generator__builder generator__builder--hidden" id="biography-generator-impact-intro-builder">
                <!-- Impact Intro Builder content -->
            </div>
            
            <!-- Basic Information Section -->
            <div class="biography-generator__basic-info">
                <!-- Basic info form fields -->
            </div>
            
            <!-- Biography Settings Section -->
            <div class="biography-generator__settings">
                <!-- Settings form fields -->
            </div>
            
            <!-- Additional Content Section -->
            <div class="biography-generator__additional-content">
                <!-- Additional content form fields -->
            </div>
            
            <!-- Generation Controls -->
            <div class="biography-generator__generation-controls">
                <!-- Generation buttons -->
            </div>
            
            <!-- Results container -->
            <div class="generator__results generator__results--hidden" id="biography-generator-results">
                <!-- Generated biographies will be inserted here -->
            </div>
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <!-- Guidance content -->
        </div>
    </div>
</div>
```

### **Results Template (results.php)**

The results page template follows a similar structure:

```php
<div class="generator__container biography-generator" data-generator="biography">
    <div class="generator__header">
        <!-- Results title and subtitle -->
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Results Container -->
            <div class="biography-generator__results-container">
                <!-- Biography Tabs -->
                <div class="biography-generator__results-tabs">
                    <!-- Tab buttons -->
                </div>
                
                <!-- Biography Content Panels -->
                <div class="biography-generator__results-content">
                    <!-- Short, Medium, Long biography panels -->
                </div>
                
                <!-- Tone Modification Controls -->
                <div class="biography-generator__modification-controls">
                    <!-- Tone selection and modification controls -->
                </div>
                
                <!-- Save Section -->
                <div class="biography-generator__save-section">
                    <!-- Save controls -->
                </div>
            </div>
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <!-- Usage guidance content -->
        </div>
    </div>
</div>
```

---

## 🔄 **Integration with Other Components**

### **Authority Hook Service Integration**

The Biography Generator integrates with the Authority Hook Service for consistent data handling:

```php
// Get Authority Hook Service
$authority_hook_service = null;
if (isset($GLOBALS['authority_hook_service'])) {
    $authority_hook_service = $GLOBALS['authority_hook_service'];
} else {
    // Create new instance if not available
    if (!class_exists('MKCG_Authority_Hook_Service')) {
        $service_path = defined('MKCG_PLUGIN_PATH') 
            ? MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-authority-hook-service.php'
            : dirname(dirname(dirname(__FILE__))) . '/includes/services/class-mkcg-authority-hook-service.php';
        require_once $service_path;
    }
    $authority_hook_service = new MKCG_Authority_Hook_Service();
    $GLOBALS['authority_hook_service'] = $authority_hook_service;
}

// Render Authority Hook Builder
echo $authority_hook_service->render_authority_hook_builder('biography', $current_values, $render_options);
```

### **Impact Intro Service Integration**

Similar integration with the Impact Intro Service:

```php
// Get Impact Intro Service
$impact_intro_service = null;
if (isset($GLOBALS['impact_intro_service'])) {
    $impact_intro_service = $GLOBALS['impact_intro_service'];
} else {
    // Create new instance if not available
    if (!class_exists('MKCG_Impact_Intro_Service')) {
        $service_path = defined('MKCG_PLUGIN_PATH') 
            ? MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-impact-intro-service.php'
            : dirname(dirname(dirname(__FILE__))) . '/includes/services/class-mkcg-impact-intro-service.php';
        require_once $service_path;
    }
    $impact_intro_service = new MKCG_Impact_Intro_Service();
    $GLOBALS['impact_intro_service'] = $impact_intro_service;
}

// Render Impact Intro Builder
echo $impact_intro_service->render_impact_intro_builder('biography', $current_impact_values, $render_options);
```

### **Unified JavaScript System Integration**

The Biography Generator integrates with the unified JavaScript system:

```javascript
// Use global AJAX system
if (!window.makeAjaxRequest) {
    console.error('❌ Global makeAjaxRequest not available');
    this.hideLoading();
    this.showNotification('System error: AJAX service not available', 'error');
    return;
}

window.makeAjaxRequest('mkcg_generate_biography', formData)
    .then(data => {
        // Handle success
    })
    .catch(error => {
        // Handle error
    });
```

### **Event Communication System**

Cross-generator communication via the AppEvents system:

```javascript
// Trigger cross-generator communication
if (window.AppEvents) {
    window.AppEvents.trigger('biography:generated', {
        biographies: data.biographies,
        timestamp: Date.now()
    });
}
```

---

## 🧩 **Extension Points**

### **PHP Filters**

The Biography Generator provides several WordPress filters for customization:

```php
/**
 * Filter biography generation data before API request
 * 
 * @param array $data The data used for generation
 * @return array Modified data
 */
$data = apply_filters('mkcg_biography_generation_data', $data);

/**
 * Filter generated biographies after API response
 * 
 * @param array $biographies The generated biographies
 * @param array $data The data used for generation
 * @return array Modified biographies
 */
$biographies = apply_filters('mkcg_biography_generated_content', $biographies, $data);

/**
 * Filter biography settings
 * 
 * @param array $settings The biography settings
 * @return array Modified settings
 */
$settings = apply_filters('mkcg_biography_settings', $settings);
```

### **PHP Actions**

Several action hooks are available for extending functionality:

```php
/**
 * Fires before biography generation
 * 
 * @param array $data The data used for generation
 */
do_action('mkcg_before_biography_generation', $data);

/**
 * Fires after biography generation
 * 
 * @param array $biographies The generated biographies
 * @param array $data The data used for generation
 */
do_action('mkcg_after_biography_generation', $biographies, $data);

/**
 * Fires when biographies are saved to post meta
 * 
 * @param int $post_id The post ID
 * @param array $biographies The saved biographies
 */
do_action('mkcg_biography_saved', $post_id, $biographies);
```

### **JavaScript Event Hooks**

The JavaScript system provides events for extension:

```javascript
// Register custom event handler
window.AppEvents.on('biography:generated', function(data) {
    // Handle biography generation event
    console.log('Biographies generated:', data.biographies);
});

window.AppEvents.on('biography:saved', function(data) {
    // Handle biography save event
    console.log('Biographies saved for post:', data.post_id);
});
```

### **CSS Extension Points**

The BEM architecture provides clear extension points for CSS customization:

```css
/* Target specific biography components */
.biography-generator__result-item {
    /* Custom styling */
}

/* Target specific result actions */
.biography-generator__result-actions {
    /* Custom styling */
}

/* Target specific biography content */
.biography-generator__result-content {
    /* Custom styling */
}
```

---

## 🔌 **OpenAI API Integration**

### **API Request Format**

The Biography Generator uses the OpenAI Chat Completions API:

```php
$request_body = [
    'model' => self::OPENAI_MODEL,
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a professional biography writer who creates compelling, accurate, and tailored biographies.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 2000
];
```

### **Prompt Structure**

The prompt follows a specific structure for optimal results:

```
You are a professional biography writer tasked with creating compelling professional biographies for {name}[, a {title}][ at {organization}].

Please create three versions of the biography:
1. SHORT BIO: 50-75 words
2. MEDIUM BIO: 100-150 words
3. LONG BIO: 200-300 words

Use a {tone} tone and write in the {pov}.

Use the following information as the foundation for the biography:

AUTHORITY HOOK (core expertise and value proposition):
{authority_hook}

IMPACT INTRO (credentials and mission):
{impact_intro}

[EXISTING BIOGRAPHY (use this as reference but improve it):
{existing_bio}]

[ADDITIONAL NOTES:
{additional_notes}]

Follow these additional guidelines:
- Focus on their expertise, achievements, and the value they provide
- Include specific credentials and quantifiable results when available
- Ensure the biography flows naturally and engages the reader
- Maintain consistent messaging across all three versions
- Each biography should be self-contained and complete

Format your response as follows:
SHORT BIO:
[Short biography here]

MEDIUM BIO:
[Medium biography here]

LONG BIO:
[Long biography here]
```

### **Response Parsing**

The Biography Generator parses the OpenAI API response to extract the three biography versions:

```php
// Parse the biographies from the response
preg_match('/SHORT BIO:\s*(.*?)(?=\s*MEDIUM BIO:|$)/s', $content, $short_matches);
preg_match('/MEDIUM BIO:\s*(.*?)(?=\s*LONG BIO:|$)/s', $content, $medium_matches);
preg_match('/LONG BIO:\s*(.*?)(?=\s*$)/s', $content, $long_matches);

// Clean up the matches
if (!empty($short_matches[1])) {
    $biographies['short'] = trim($short_matches[1]);
}

if (!empty($medium_matches[1])) {
    $biographies['medium'] = trim($medium_matches[1]);
}

if (!empty($long_matches[1])) {
    $biographies['long'] = trim($long_matches[1]);
}
```

---

## 🚀 **Development Guidelines**

### **BEM Naming Conventions**

The Biography Generator follows strict BEM naming conventions:

- **Block**: `biography-generator`
- **Elements**: `biography-generator__element-name`
- **Modifiers**: `biography-generator__element-name--modifier`

### **JavaScript Guidelines**

When modifying or extending the JavaScript functionality:

1. Maintain the module pattern structure
2. Use pure vanilla JavaScript (no jQuery)
3. Follow the initialization pattern: load data → bind events → update display
4. Use event delegation where appropriate
5. Document all functions with JSDoc comments
6. Use consistent error handling patterns

### **PHP Guidelines**

When modifying or extending the PHP functionality:

1. Follow WordPress coding standards
2. Properly sanitize all inputs and escape all outputs
3. Use nonce verification for all AJAX requests
4. Implement proper capability checks
5. Document all functions with PHPDoc comments
6. Maintain backward compatibility when possible

### **CSS Guidelines**

When modifying or extending the CSS:

1. Follow the BEM methodology strictly
2. Use CSS variables for colors, spacing, etc.
3. Organize CSS by component
4. Maintain mobile-first responsive design
5. Test across multiple browsers and devices

---

## 🧪 **Testing Guidelines**

### **PHP Testing**

For testing PHP functionality:

1. Test all AJAX endpoints with various input combinations
2. Verify security measures (nonce, capability checks, etc.)
3. Test error handling for API failures
4. Verify data persistence to WordPress post meta

### **JavaScript Testing**

For testing JavaScript functionality:

1. Test all user interactions and form submissions
2. Verify proper event handling
3. Test biography generation with various input combinations
4. Verify proper display of results

### **Cross-Browser Testing**

Test the Biography Generator in multiple browsers:

1. Chrome (latest)
2. Firefox (latest)
3. Safari (latest)
4. Edge (latest)

### **Responsive Testing**

Test on multiple device sizes:

1. Desktop (1920×1080)
2. Laptop (1366×768)
3. Tablet (768×1024)
4. Mobile (375×667)

---

This comprehensive developer documentation provides all the necessary information for working with, extending, or modifying the Biography Generator component of the Media Kit Content Generator.
