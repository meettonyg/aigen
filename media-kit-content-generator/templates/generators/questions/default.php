<?php
/**
 * Questions Generator Template - Unified Implementation
 * Enhanced template for generating interview questions with topic selection
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';
$debug_info = [];

// Try to get entry from URL parameters
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    $debug_info[] = 'Entry key from URL: ' . $entry_key;
    
    // Use the Formidable service to resolve entry ID
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
            $debug_info[] = 'Resolved entry ID: ' . $entry_id;
        } else {
            $debug_info[] = 'Failed to resolve entry: ' . $entry_data['message'];
        }
    } else {
        $debug_info[] = 'Formidable service not available';
    }
} elseif (isset($_GET['entry_id'])) {
    $entry_id = intval($_GET['entry_id']);
    $debug_info[] = 'Entry ID from URL: ' . $entry_id;
}

// Get available topics from Topics Generator
$available_topics = [];
$topics_debug = [];

if ($entry_id && isset($formidable_service)) {
    // FIXED: Topics are in field 10081 as a combined list
    $topics_debug[] = 'CORRECTED: Looking for topics in field 10081 (combined field)';
    $topics_debug[] = 'Entry ID: ' . $entry_id;
    
    global $wpdb;
    $item_metas_table = $wpdb->prefix . 'frm_item_metas';
    
    // Get the topics from field 10081
    $topics_combined = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM $item_metas_table WHERE item_id = %d AND field_id = 10081",
        $entry_id
    ));
    
    $topics_debug[] = 'Field 10081 content: ' . ($topics_combined ? substr($topics_combined, 0, 200) . '...' : 'NULL');
    
    if ($topics_combined && !empty(trim($topics_combined))) {
        // Parse the topics from the combined field
        // Format appears to be: "* Topic 1: Title\n* Topic 2: Title\n..."
        $topics_debug[] = 'Parsing topics from combined field...';
        
        // Split by lines and look for topic patterns
        $lines = explode("\n", $topics_combined);
        $topic_count = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for "Topic X:" or "* Topic X:" patterns
            if (preg_match('/^\*?\s*Topic\s+(\d+):\s*(.+)$/i', $line, $matches)) {
                $topic_number = intval($matches[1]);
                $topic_text = trim($matches[2]);
                
                if ($topic_number >= 1 && $topic_number <= 5 && !empty($topic_text)) {
                    $available_topics[$topic_number] = $topic_text;
                    $topic_count++;
                    $topics_debug[] = "Found Topic {$topic_number}: {$topic_text}";
                }
            }
        }
        
        $topics_debug[] = "SUCCESS: Parsed {$topic_count} topics from field 10081";
    } else {
        $topics_debug[] = 'Field 10081 is empty or null';
    }
} else {
    if (!$entry_id) {
        $topics_debug[] = 'No entry ID available';
    }
    if (!isset($formidable_service)) {
        $topics_debug[] = 'Formidable service not available';
    }
}

// Debug output for development (remove in production)
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- DEBUG INFO: ' . implode(' | ', $debug_info) . ' -->';
    echo '<!-- TOPICS DEBUG: ' . implode(' | ', $topics_debug) . ' -->';
    echo '<!-- FOUND TOPICS: ' . print_r($available_topics, true) . ' -->';
}

// Error if no topics found - redirect to topics generator
if (empty($available_topics)) {
    $topics_url = '';
    if ($entry_key) {
        $topics_url = site_url('/topics/?entry=' . urlencode($entry_key));
    } elseif ($entry_id) {
        $topics_url = site_url('/topics/?entry_id=' . $entry_id);
    }
    
    echo '<div class="mkcg-error-notice">';
    echo '<h3>No Topics Found</h3>';
    echo '<p>Please generate your interview topics first before creating questions.</p>';
    
    // Debug information for development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo '<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px; font-size: 12px;">';
        echo '<strong>Debug Info:</strong><br>';
        echo 'Entry ID: ' . ($entry_id ?: 'None') . '<br>';
        echo 'Entry Key: ' . ($entry_key ?: 'None') . '<br>';
        echo 'Debug: ' . implode(' | ', $debug_info) . '<br>';
        echo 'Topics Debug: ' . implode(' | ', $topics_debug) . '<br>';
        echo '</div>';
    }
    
    if ($topics_url) {
        echo '<a href="' . esc_url($topics_url) . '" class="mkcg-button">Generate Topics First</a>';
    }
    echo '</div>';
    return;
}
?>

<div class="mkcg-questions-generator-wrapper">
    <div class="mkcg-container">
        <div class="mkcg-onboard-header">
            <h1 class="mkcg-tool-title">Create Your Interview Questions</h1>
        </div>
        
        <div class="mkcg-content-wrapper">
            <!-- LEFT PANEL -->
            <div class="mkcg-left-panel">
                <!-- Introduction Text -->
                <p class="mkcg-intro-text">
                    Generate compelling interview questions based on your selected topic. Questions will be crafted to showcase your expertise while providing maximum value to podcast listeners.
                </p>
                
                <!-- Topic Selector -->
                <div class="mkcg-topic-selector">
                    <div class="mkcg-selector-header">
                        <h3 class="mkcg-section-title">Choose Your Topic</h3>
                        <button class="mkcg-edit-topics-button" id="mkcg-edit-topics">
                            ✎ Edit Topics
                        </button>
                    </div>
                    
                    <div class="mkcg-topics-grid" id="mkcg-topics-grid">
                        <?php if (!empty($available_topics)): ?>
                            <?php foreach ($available_topics as $topic_id => $topic_text): ?>
                            <div class="mkcg-topic-card <?php echo $topic_id === 1 ? 'active' : ''; ?>" data-topic="<?php echo esc_attr($topic_id); ?>">
                                <div class="mkcg-topic-number"><?php echo esc_html($topic_id); ?></div>
                                <div class="mkcg-topic-text"><?php echo esc_html($topic_text); ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="mkcg-no-topics-message">
                                <p>No topics available. Please generate topics first.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Selected Topic Result -->
                <div class="mkcg-selected-topic-result" id="mkcg-selected-topic-result">
                    <div class="mkcg-result-header">
                        <span class="mkcg-star-icon">★</span>
                        <h3 class="mkcg-result-title">Selected Topic</h3>
                        <span class="mkcg-ai-badge">FROM TOPICS</span>
                    </div>
                    
                    <div class="mkcg-selected-topic-content">
                        <p id="mkcg-selected-topic-text"><?php echo !empty($available_topics) ? esc_html($available_topics[1]) : 'No topic selected'; ?></p>
                    </div>
                    
                    <div class="mkcg-result-actions">
                        <button class="mkcg-generate-button" id="mkcg-generate-questions">
                            Generate Questions with AI
                        </button>
                    </div>
                </div>
                
                <!-- Loading indicator -->
                <div class="mkcg-ai-loading" id="mkcg-loading" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                    </svg>
                    Generating questions...
                </div>
                
                <!-- Questions result -->
                <div class="mkcg-questions-result" id="mkcg-questions-result" style="display: none;">
                    <div class="mkcg-questions-list" id="mkcg-questions-list">
                        <!-- Generated questions will be listed here -->
                    </div>
                </div>
                
                <!-- Field Selection Modal -->
                <div class="mkcg-modal" id="mkcg-field-modal">
                    <div class="mkcg-modal-content">
                        <div class="mkcg-modal-header">
                            <h3 class="mkcg-modal-title">Enter the field number to update (1-5):</h3>
                        </div>
                        <input type="number" min="1" max="5" class="mkcg-field-input" id="mkcg-field-number" value="1">
                        <div class="mkcg-modal-actions">
                            <button class="mkcg-ok-button" id="mkcg-modal-ok">OK</button>
                            <button class="mkcg-cancel-button" id="mkcg-modal-cancel">Cancel</button>
                        </div>
                    </div>
                </div>
                
                <!-- Form Fields -->
                <div class="mkcg-form-step">
                    <div class="mkcg-form-field">
                        <div class="mkcg-form-field-label">
                            <div class="mkcg-form-field-number">1</div>
                            <div class="mkcg-form-field-title">First Interview Question</div>
                        </div>
                        <textarea class="mkcg-form-field-input" id="mkcg-question-field-1" name="field_8505" placeholder="Enter your first interview question..."></textarea>
                        <div class="mkcg-form-examples">
                            <p>Examples:</p>
                            <div class="mkcg-form-example">"What led you to develop this approach to podcast monetization?"</div>
                            <div class="mkcg-form-example">"Can you walk us through your step-by-step process for landing high-paying sponsors?"</div>
                            <div class="mkcg-form-example">"What's the biggest mistake you see podcasters make when trying to monetize?"</div>
                        </div>
                    </div>
                    
                    <div class="mkcg-form-field">
                        <div class="mkcg-form-field-label">
                            <div class="mkcg-form-field-number">2</div>
                            <div class="mkcg-form-field-title">Second Interview Question</div>
                        </div>
                        <textarea class="mkcg-form-field-input" id="mkcg-question-field-2" name="field_8506" placeholder="Enter your second interview question..."></textarea>
                    </div>
                    
                    <div class="mkcg-form-field">
                        <div class="mkcg-form-field-label">
                            <div class="mkcg-form-field-number">3</div>
                            <div class="mkcg-form-field-title">Third Interview Question</div>
                        </div>
                        <textarea class="mkcg-form-field-input" id="mkcg-question-field-3" name="field_8507" placeholder="Enter your third interview question..."></textarea>
                    </div>
                    
                    <div class="mkcg-form-field">
                        <div class="mkcg-form-field-label">
                            <div class="mkcg-form-field-number">4</div>
                            <div class="mkcg-form-field-title">Fourth Interview Question</div>
                        </div>
                        <textarea class="mkcg-form-field-input" id="mkcg-question-field-4" name="field_8508" placeholder="Enter your fourth interview question..."></textarea>
                    </div>
                    
                    <div class="mkcg-form-field">
                        <div class="mkcg-form-field-label">
                            <div class="mkcg-form-field-number">5</div>
                            <div class="mkcg-form-field-title">Fifth Interview Question</div>
                        </div>
                        <textarea class="mkcg-form-field-input" id="mkcg-question-field-5" name="field_8509" placeholder="Enter your fifth interview question..."></textarea>
                    </div>
                    
                    <!-- Hidden fields for AJAX -->
                    <input type="hidden" id="mkcg-entry-id" value="<?php echo esc_attr($entry_id); ?>">
                    <input type="hidden" id="mkcg-entry-key" value="<?php echo esc_attr($entry_key); ?>">
                    <input type="hidden" id="mkcg-questions-nonce" value="<?php echo wp_create_nonce('generate_topics_nonce'); ?>">
                    <input type="hidden" id="mkcg-selected-topic-id" value="1">
                </div>
            </div>
            
            <!-- RIGHT PANEL -->
            <div class="mkcg-right-panel">
                <h2 class="mkcg-right-panel-header">Crafting Effective Interview Questions</h2>
                <p class="mkcg-right-panel-subtitle">Well-crafted questions help podcast hosts guide the conversation while giving you opportunities to showcase your expertise. Each question should be open-ended and allow you to deliver valuable insights to listeners.</p>
                
                <div class="mkcg-formula-box">
                    <span class="mkcg-formula-label">APPROACH</span>
                    Balance <span class="mkcg-highlight">specific questions</span> that demonstrate your expertise with <span class="mkcg-highlight">story-based questions</span> that engage the audience.
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Frame Questions for Stories</h3>
                        <p class="mkcg-process-description">
                            Include questions that prompt you to share real-world examples and stories. Listeners connect with narratives, and hosts appreciate guests who illustrate points with compelling stories.
                        </p>
                    </div>
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Show Range and Depth</h3>
                        <p class="mkcg-process-description">
                            Mix high-level strategic questions with tactical implementation details. This demonstrates both your big-picture understanding and your practical expertise in executing solutions.
                        </p>
                    </div>
                </div>
                
                <div class="mkcg-process-step">
                    <div class="mkcg-process-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="mkcg-process-content">
                        <h3 class="mkcg-process-title">Include Audience Transformation</h3>
                        <p class="mkcg-process-description">
                            Create questions that allow you to describe the transformation your clients or audience experience. Podcast hosts love when guests can articulate clear before-and-after scenarios.
                        </p>
                    </div>
                </div>
                
                <h3 class="mkcg-examples-header">Question Types to Include:</h3>
                
                <div class="mkcg-example-card">
                    <strong>Origin Questions:</strong>
                    <p>"What led you to develop this approach to content creation?"</p>
                    <p>"How did you discover this common mistake in SaaS scaling?"</p>
                </div>
                
                <div class="mkcg-example-card">
                    <strong>Process Questions:</strong>
                    <p>"Can you walk us through your step-by-step approach to building self-sufficient teams?"</p>
                    <p>"What does your content creation process look like from start to finish?"</p>
                </div>
                
                <div class="mkcg-example-card">
                    <strong>Result Questions:</strong>
                    <p>"What kind of results have your clients seen after implementing these strategies?"</p>
                    <p>"How does a properly scaled SaaS business operate differently than one that's struggling?"</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Topics data from PHP
const MKCG_TopicsData = <?php echo json_encode($available_topics); ?>;
</script>