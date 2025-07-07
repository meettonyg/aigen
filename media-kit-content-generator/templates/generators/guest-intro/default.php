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

// Get post ID from URL
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
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
                <p>Create custom introductions that engage your audience and properly introduce your guest. These introductions are designed to be read aloud by podcast hosts or event moderators.</p>
            </div>

            <!-- Authority Hook Integration -->
            <?php if (function_exists('render_authority_hook_builder')): ?>
                <?php 
                    $current_values = array();
                    $render_options = array(
                        'show_title' => true,
                        'title' => 'Authority Hook',
                        'description' => 'The Authority Hook components help create a compelling introduction that establishes your guest\'s expertise.',
                        'help_text' => 'Complete these fields to add credibility to your guest introduction.'
                    );
                    
                    render_authority_hook_builder('guest_intro', $current_values, $render_options);
                ?>
            <?php endif; ?>

            <!-- Impact Intro Integration -->
            <?php if (function_exists('render_impact_intro_builder')): ?>
                <?php 
                    $current_values = array();
                    $render_options = array(
                        'show_title' => true,
                        'title' => 'Impact Intro',
                        'description' => 'The Impact Intro adds depth to your guest introduction by highlighting their credentials and mission.',
                        'help_text' => 'These components enrich your introduction with personal motivation and social proof.'
                    );
                    
                    render_impact_intro_builder('guest_intro', $current_values, $render_options);
                ?>
            <?php endif; ?>

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

                        <div class="generator__field">
                            <label class="generator__field-label" for="guest_website">Website</label>
                            <input type="url" id="guest_website" name="guest_website" class="generator__field-input" placeholder="https://example.com">
                        </div>
                    </div>

                    <div class="generator__field">
                        <label class="generator__field-label" for="guest_expertise">Areas of Expertise</label>
                        <input type="text" id="guest_expertise" name="guest_expertise" class="generator__field-input" placeholder="Leadership, Marketing, Personal Development, etc.">
                    </div>

                    <div class="generator__field">
                        <label class="generator__field-label" for="guest_achievements">Key Achievements</label>
                        <textarea id="guest_achievements" name="guest_achievements" class="generator__field-input" rows="3" placeholder="Notable accomplishments, publications, awards, etc."></textarea>
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
