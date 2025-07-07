<?php
/**
 * Guest Intro Generator Template
 *
 * Provides the interface for generating AI-powered guest introductions
 * designed to be read aloud by podcast hosts when introducing guests.
 *
 * @package MediaKitContentGenerator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// COMPREHENSIVE DATA LOADING - Following Topics Generator Pattern
$template_data = [];
$debug_info = [];

// Primary Method: Try to get data from generator instance
if (isset($generator_instance) && method_exists($generator_instance, 'get_template_data')) {
    $template_data = $generator_instance->get_template_data();
    $debug_info[] = '✅ Got data from generator instance';
    error_log('MKCG Guest Intro Template: Got data from generator instance');
} else {
    $debug_info[] = '⚠️ Generator instance not available';
    
    // Try to get post ID from various sources
    $post_id = 0;
    if (isset($_GET['post_id']) && intval($_GET['post_id']) > 0) {
        $post_id = intval($_GET['post_id']);
        $debug_info[] = "📍 Using post_id from URL: {$post_id}";
    } else if (isset($_GET['entry']) && intval($_GET['entry']) > 0) {
        $post_id = intval($_GET['entry']);
        $debug_info[] = "📍 Using entry from URL: {$post_id}";
    } else {
        // Get the most recent guest post for testing
        $recent_guest = get_posts([
            'post_type' => 'guests',
            'post_status' => 'publish',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        if (!empty($recent_guest)) {
            $post_id = $recent_guest[0]->ID;
            $debug_info[] = "🎯 Using most recent guest post: {$post_id}";
        }
    }
    
    if ($post_id > 0) {
        // Load Authority Hook and Impact Intro data using services
        $authority_hook_components = [];
        $impact_intro_components = [];
        
        // Load Authority Hook data
        if (class_exists('MKCG_Authority_Hook_Service')) {
            try {
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                $authority_hook_data = $authority_hook_service->get_authority_hook_data($post_id);
                
                if (!empty($authority_hook_data)) {
                    $authority_hook_components = [
                        'who' => $authority_hook_data['components']['who'] ?? '',
                        'what' => $authority_hook_data['components']['what'] ?? '',
                        'when' => $authority_hook_data['components']['when'] ?? '',
                        'how' => $authority_hook_data['components']['how'] ?? '',
                        'complete' => $authority_hook_data['complete'] ?? ''
                    ];
                    $debug_info[] = "✅ Authority Hook loaded: " . strlen($authority_hook_components['complete']) . " chars";
                } else {
                    $debug_info[] = "⚠️ Authority Hook service returned empty data";
                }
            } catch (Exception $e) {
                $debug_info[] = "❌ Authority Hook service error: " . $e->getMessage();
            }
        }
        
        // Load Impact Intro data
        if (class_exists('MKCG_Impact_Intro_Service')) {
            try {
                $impact_intro_service = new MKCG_Impact_Intro_Service();
                $impact_intro_data = $impact_intro_service->get_impact_intro_data($post_id);
                
                if (!empty($impact_intro_data)) {
                    $impact_intro_components = [
                        'where' => $impact_intro_data['components']['where'] ?? '',
                        'why' => $impact_intro_data['components']['why'] ?? '',
                        'complete' => $impact_intro_data['complete'] ?? ''
                    ];
                    $debug_info[] = "✅ Impact Intro loaded: " . strlen($impact_intro_components['complete']) . " chars";
                } else {
                    $debug_info[] = "⚠️ Impact Intro service returned empty data";
                }
            } catch (Exception $e) {
                $debug_info[] = "❌ Impact Intro service error: " . $e->getMessage();
            }
        }
        
        $template_data = [
            'post_id' => $post_id,
            'authority_hook_components' => $authority_hook_components,
            'impact_intro_components' => $impact_intro_components,
            'has_data' => !empty($authority_hook_components['complete']) || !empty($impact_intro_components['complete'])
        ];
        
        $debug_info[] = "✅ Template data loaded for post {$post_id}";
        
    } else {
        $debug_info[] = "❌ No valid post ID found";
        
        // Create empty structure when no data found
        $template_data = [
            'post_id' => 0,
            'authority_hook_components' => [
                'who' => '',
                'what' => '',
                'when' => '',
                'how' => '',
                'complete' => ''
            ],
            'impact_intro_components' => [
                'where' => '',
                'why' => '',
                'complete' => ''
            ],
            'has_data' => false
        ];
    }
    
    error_log('MKCG Guest Intro Template: ' . implode(' | ', $debug_info));
}

// Extract data for easier access in template
$post_id = $template_data['post_id'];
$authority_hook_components = $template_data['authority_hook_components'];
$impact_intro_components = $template_data['impact_intro_components'];
$has_data = $template_data['has_data'];

// Debug logging
error_log('MKCG Guest Intro Template: Authority Hook Complete: ' . ($authority_hook_components['complete'] ?? 'EMPTY'));
error_log('MKCG Guest Intro Template: Impact Intro Complete: ' . ($impact_intro_components['complete'] ?? 'EMPTY'));
error_log('MKCG Guest Intro Template: Has Data: ' . ($has_data ? 'true' : 'false'));
?>
<div class="generator__container guest-intro-generator">
    <div class="generator__header">
        <h1 class="generator__title">Guest Introduction Creator</h1>
        <p class="generator__subtitle">Create professional introductions for podcast and event guests</p>
    </div>

    <div class="generator__content">
        <!-- Left Panel (Form) -->
        <div class="generator__panel generator__panel--left">
            <div class="guest-intro-generator__intro">
                <p>Create custom introductions that engage your audience and properly introduce your guest. These introductions are designed to be read aloud by podcast hosts or event moderators. The generator uses your Authority Hook and Impact Intro components to create personalized, professional introductions.</p>
            </div>

            <!-- Authority Hook Integration -->
            <div class="generator__authority-hook">
                <div class="generator__authority-hook-header">
                    <span class="generator__authority-hook-icon">★</span>
                    <h3 class="generator__authority-hook-title">Your Authority Hook</h3>
                    <span class="generator__badge">AI GENERATED</span>
                </div>
                
                <div class="generator__authority-hook-content">
                    <p id="guest-intro-generator-authority-hook-text"><?php 
                        // Show Authority Hook content if available
                        if (!empty($authority_hook_components['complete'])) {
                            echo esc_html($authority_hook_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Authority Hook will appear here once you fill in the WHO, WHAT, WHEN, and HOW components below.</em>';
                        }
                    ?></p>
                </div>
                
                <div class="generator__authority-hook-actions">
                    <button type="button" class="generator__button generator__button--outline" id="guest-intro-generator-toggle-authority-builder">
                        Edit Components
                    </button>
                </div>
            </div>
            
            <!-- Authority Hook Builder - Expanded/Collapsible -->
            <div class="generator__builder generator__builder--hidden" id="guest-intro-generator-authority-hook-builder">
            <?php 
            // Render Authority Hook Builder using loaded data
            if (class_exists('MKCG_Authority_Hook_Service')) {
                $authority_hook_service = new MKCG_Authority_Hook_Service();
                
                $render_options = array(
                    'show_preview' => false,
                    'show_examples' => true,
                    'show_audience_manager' => true,
                    'css_classes' => 'authority-hook guest-intro-authority-hook',
                    'field_prefix' => 'mkcg-',
                    'tabs_enabled' => true
                );
                
                echo $authority_hook_service->render_authority_hook_builder('guest_intro', $authority_hook_components, $render_options);
            }
            ?>
            </div>

            <!-- Impact Intro Integration -->
            <div class="generator__impact-intro">
                <div class="generator__impact-intro-header">
                    <span class="generator__impact-intro-icon">🎯</span>
                    <h3 class="generator__impact-intro-title">Your Impact Intro</h3>
                    <span class="generator__badge">CREDENTIALS & MISSION</span>
                </div>
                
                <div class="generator__impact-intro-content">
                    <p id="guest-intro-generator-impact-intro-text"><?php 
                        // Show Impact Intro content if available
                        if (!empty($impact_intro_components['complete'])) {
                            echo esc_html($impact_intro_components['complete']);
                        } else {
                            echo '<em style="color: #666;">Impact Intro will appear here once you fill in the WHERE credentials and WHY mission components below.</em>';
                        }
                    ?></p>
                </div>
                
                <div class="generator__impact-intro-actions">
                    <button type="button" class="generator__button generator__button--outline" id="guest-intro-generator-toggle-impact-builder">
                        Edit Impact Intro
                    </button>
                </div>
            </div>
            
            <!-- Impact Intro Builder - Expanded/Collapsible -->
            <div class="generator__builder generator__builder--hidden" id="guest-intro-generator-impact-intro-builder">
            <?php 
            // Render Impact Intro Builder using loaded data
            if (class_exists('MKCG_Impact_Intro_Service')) {
                $impact_intro_service = new MKCG_Impact_Intro_Service();
                
                $render_options = array(
                    'show_preview' => false,
                    'show_examples' => true,
                    'show_credential_manager' => true,
                    'css_classes' => 'impact-intro guest-intro-impact-intro',
                    'field_prefix' => 'mkcg-',
                    'tabs_enabled' => true
                );
                
                echo $impact_intro_service->render_impact_intro_builder('guest_intro', $impact_intro_components, $render_options);
            }
            ?>
            </div>

            <!-- Guest Intro Form -->
            <form class="guest-intro-generator__form">
                <?php if ($post_id): ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                <?php endif; ?>

                <h3 class="guest-intro-generator__section-title">Guest Information</h3>
                <p class="guest-intro-generator__section-description">Provide details about your guest to create a personalized introduction.</p>

                <div class="guest-intro-generator__info-container">
                    <div class="guest-intro-generator__form-grid">
                        <div class="generator__field">
                            <label class="generator__field-label" for="guest_name">Guest Name <span class="required">*</span></label>
                            <input type="text" id="guest_name" name="guest_name" class="generator__field-input" placeholder="John Smith" required>
                        </div>

                        <div class="generator__field">
                            <label class="generator__field-label" for="guest_title">Title/Role</label>
                            <input type="text" id="guest_title" name="guest_title" class="generator__field-input" placeholder="CEO, Author, Consultant, etc.">
                        </div>

                        <div class="generator__field">
                            <label class="generator__field-label" for="guest_company">Company/Organization</label>
                            <input type="text" id="guest_company" name="guest_company" class="generator__field-input" placeholder="Company Name">
                        </div>
                    </div>
                </div>

                <h3 class="guest-intro-generator__section-title">Episode/Event Information</h3>
                <p class="guest-intro-generator__section-description">Add context about the episode or event where this introduction will be used.</p>

                <div class="guest-intro-generator__info-container">
                    <div class="guest-intro-generator__form-grid">
                        <div class="generator__field">
                            <label class="generator__field-label" for="episode_title">Episode/Event Title</label>
                            <input type="text" id="episode_title" name="episode_title" class="generator__field-input" placeholder="Episode Title">
                        </div>

                        <div class="generator__field">
                            <label class="generator__field-label" for="episode_topic">Main Topic</label>
                            <input type="text" id="episode_topic" name="episode_topic" class="generator__field-input" placeholder="The main topic of discussion">
                        </div>
                    </div>
                </div>

                <h3 class="guest-intro-generator__section-title">Introduction Settings</h3>
                <p class="guest-intro-generator__section-description">Customize the style and tone of your guest introduction.</p>

                <div class="guest-intro-generator__settings-panel">
                    <div class="guest-intro-generator__settings-grid">
                        <div class="guest-intro-generator__setting-option">
                            <label class="guest-intro-generator__setting-label">Tone</label>
                            <div class="guest-intro-generator__radio-group">
                                <input type="radio" id="tone_professional" name="intro_tone" value="professional" class="guest-intro-generator__radio-input" checked>
                                <label for="tone_professional" class="guest-intro-generator__radio-label">Professional</label>
                                
                                <input type="radio" id="tone_conversational" name="intro_tone" value="conversational" class="guest-intro-generator__radio-input">
                                <label for="tone_conversational" class="guest-intro-generator__radio-label">Conversational</label>
                                
                                <input type="radio" id="tone_enthusiastic" name="intro_tone" value="enthusiastic" class="guest-intro-generator__radio-input">
                                <label for="tone_enthusiastic" class="guest-intro-generator__radio-label">Enthusiastic</label>
                                
                                <input type="radio" id="tone_authoritative" name="intro_tone" value="authoritative" class="guest-intro-generator__radio-input">
                                <label for="tone_authoritative" class="guest-intro-generator__radio-label">Authoritative</label>
                                
                                <input type="radio" id="tone_warm" name="intro_tone" value="warm" class="guest-intro-generator__radio-input">
                                <label for="tone_warm" class="guest-intro-generator__radio-label">Warm</label>
                            </div>
                        </div>

                        <div class="guest-intro-generator__setting-option">
                            <label class="guest-intro-generator__setting-label">Hook Style</label>
                            <div class="guest-intro-generator__radio-group">
                                <input type="radio" id="hook_question" name="intro_hook_style" value="question" class="guest-intro-generator__radio-input" checked>
                                <label for="hook_question" class="guest-intro-generator__radio-label">Question</label>
                                
                                <input type="radio" id="hook_statistic" name="intro_hook_style" value="statistic" class="guest-intro-generator__radio-input">
                                <label for="hook_statistic" class="guest-intro-generator__radio-label">Statistic</label>
                                
                                <input type="radio" id="hook_problem" name="intro_hook_style" value="problem" class="guest-intro-generator__radio-input">
                                <label for="hook_problem" class="guest-intro-generator__radio-label">Problem</label>
                                
                                <input type="radio" id="hook_story" name="intro_hook_style" value="story" class="guest-intro-generator__radio-input">
                                <label for="hook_story" class="guest-intro-generator__radio-label">Story</label>
                                
                                <input type="radio" id="hook_direct" name="intro_hook_style" value="direct" class="guest-intro-generator__radio-input">
                                <label for="hook_direct" class="guest-intro-generator__radio-label">Direct</label>
                            </div>
                        </div>
                    </div>

                    <div class="generator__field">
                        <label class="generator__field-label" for="custom_notes">Additional Notes or Requests</label>
                        <textarea id="custom_notes" name="custom_notes" class="generator__field-input" rows="3" placeholder="Any specific details or requirements for this introduction..."></textarea>
                    </div>
                </div>

                <div class="guest-intro-generator__generate-container">
                    <div class="guest-intro-generator__loading" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader">
                            <line x1="12" y1="2" x2="12" y2="6"></line>
                            <line x1="12" y1="18" x2="12" y2="22"></line>
                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                            <line x1="2" y1="12" x2="6" y2="12"></line>
                            <line x1="18" y1="12" x2="22" y2="12"></line>
                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                        </svg>
                        <span>Generating introductions...</span>
                    </div>

                    <button type="button" class="generator__button--call-to-action guest-intro-generator__generate-button">
                        Generate Guest Introductions
                    </button>
                </div>
            </form>

            <!-- Results Container (initially hidden) -->
            <div class="guest-intro-generator__results" style="display: none;">
                <div class="guest-intro-generator__results-header">
                    <h3 class="guest-intro-generator__results-title">Generated Introductions</h3>
                </div>

                <div class="guest-intro-generator__tabs">
                    <div class="guest-intro-generator__tab guest-intro-generator__tab--active" data-type="short">Short (30-45 sec)</div>
                    <div class="guest-intro-generator__tab" data-type="medium">Medium (60-90 sec)</div>
                    <div class="guest-intro-generator__tab" data-type="long">Long (2-3 min)</div>
                </div>

                <div class="guest-intro-generator__intro-content">
                    <!-- Content will be populated by JavaScript -->
                </div>

                <div class="guest-intro-generator__actions">
                    <button type="button" class="guest-intro-generator__copy-button" data-type="short">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Copy to Clipboard
                    </button>

                    <?php if ($post_id): ?>
                    <button type="button" class="generator__button--primary guest-intro-generator__save-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save to Post
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Panel (Guidance) -->
        <div class="generator__panel generator__panel--right">
            <h2 class="generator__guidance-header">Creating Powerful Guest Introductions</h2>
            <p class="generator__guidance-subtitle">A professional guest introduction is vital for setting the right tone and building credibility with your audience.</p>

            <div class="generator__formula-box">
                <span class="generator__formula-label">FORMULA</span>
                <p>
                    <strong>Opening Hook</strong> + <strong>Guest Credentials</strong> + <strong>Relevance to Audience</strong> + <strong>Warm Welcome</strong>
                </p>
            </div>

            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Start With an Engaging Hook</h3>
                    <p class="generator__process-description">Begin with a question, statistic, problem statement, or story that immediately captures your audience's attention and relates to your guest's expertise.</p>
                </div>
            </div>

            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Establish Credibility</h3>
                    <p class="generator__process-description">Highlight your guest's most impressive credentials, experience, and achievements that are relevant to the topic of discussion.</p>
                </div>
            </div>

            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">Connect to Your Audience</h3>
                    <p class="generator__process-description">Explain why this guest and topic matter to your audience. What problems do they solve? What value will listeners gain?</p>
                </div>
            </div>

            <div class="generator__process-step">
                <div class="generator__process-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </div>
                <div class="generator__process-content">
                    <h3 class="generator__process-title">End With a Warm Welcome</h3>
                    <p class="generator__process-description">Conclude with an enthusiastic welcome that transitions smoothly to the conversation, such as "Please join me in welcoming [Guest Name]!"</p>
                </div>
            </div>

            <h3 class="generator__examples-header">Example Introductions</h3>

            <div class="generator__example-card">
                <strong>Short Introduction (30-45 seconds)</strong>
                <p>Have you ever wondered how successful entrepreneurs manage to build multimillion-dollar companies while maintaining work-life balance? Today, I'm thrilled to introduce Jane Smith, CEO of Growth Dynamics and author of "Sustainable Success." With over 15 years of experience helping business leaders optimize their time and energy, Jane has transformed how we think about productivity. Please join me in welcoming Jane Smith!</p>
            </div>

            <div class="generator__example-card">
                <strong>Medium Introduction (60-90 seconds)</strong>
                <p>Did you know that 76% of professionals report feeling burned out at least sometimes? This is exactly the problem our guest today has dedicated his career to solving. John Doe is a renowned performance coach who has worked with Fortune 500 executives, Olympic athletes, and everyday professionals to help them achieve more while working less.</p>
                <p>As the founder of Peak Performance Institute and author of the bestselling book "The Productivity Paradox," John has been featured in Business Insider, Forbes, and The Wall Street Journal for his revolutionary approach to sustainable success. His methods have helped thousands of people reclaim their time, energy, and passion for their work and lives. I'm excited to dive into John's proven strategies that can help you do the same. Ladies and gentlemen, please welcome John Doe!</p>
            </div>
        </div>
    </div>
</div>

<!-- Enqueue script -->
<script type="text/javascript">
    // MKCG Debug Info
    console.log('🎯 MKCG Guest Intro: Template data loaded', {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>
    });
    
    // Pass PHP data to JavaScript
    window.MKCG_Guest_Intro_Data = {
        postId: <?php echo intval($post_id); ?>,
        hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
        authorityHook: {
            who: '<?php echo esc_js($authority_hook_components['who'] ?? ''); ?>',
            what: '<?php echo esc_js($authority_hook_components['what'] ?? ''); ?>',
            when: '<?php echo esc_js($authority_hook_components['when'] ?? ''); ?>',
            how: '<?php echo esc_js($authority_hook_components['how'] ?? ''); ?>',
            complete: '<?php echo esc_js($authority_hook_components['complete'] ?? ''); ?>'
        },
        impactIntro: {
            where: '<?php echo esc_js($impact_intro_components['where'] ?? ''); ?>',
            why: '<?php echo esc_js($impact_intro_components['why'] ?? ''); ?>',
            complete: '<?php echo esc_js($impact_intro_components['complete'] ?? ''); ?>'
        }
    };
    
    console.log('✅ MKCG Guest Intro: Data loaded', window.MKCG_Guest_Intro_Data);
    
    // Set up AJAX URL for WordPress
    if (!window.ajaxurl) {
        window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    }

    // Register guest intro generator script
    if (typeof wp !== 'undefined' && wp.hooks && wp.hooks.addAction) {
        wp.hooks.addAction('mkcg.scriptsLoaded', 'mkcg/guest-intro', function() {
            console.log('Loading Guest Intro Generator script');
            if (document.querySelector('.guest-intro-generator')) {
                const script = document.createElement('script');
                script.src = '<?php echo MKCG_PLUGIN_URL; ?>assets/js/generators/guest-intro-generator.js';
                script.async = true;
                document.head.appendChild(script);
            }
        });
    } else {
        // Fallback for non-WordPress environments
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.guest-intro-generator')) {
                const script = document.createElement('script');
                script.src = '<?php echo MKCG_PLUGIN_URL; ?>assets/js/generators/guest-intro-generator.js';
                script.async = true;
                document.head.appendChild(script);
            }
        });
    }
</script>
