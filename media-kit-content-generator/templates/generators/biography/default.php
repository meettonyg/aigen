<?php
/**
 * Biography Generator Template - BEM Methodology
 * Default template for generating professional biographies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get entry information
$entry_id = 0;
$entry_key = '';

// Try to get entry from URL parameters  
if (isset($_GET['entry'])) {
    $entry_key = sanitize_text_field($_GET['entry']);
    
    // Use the Formidable service to resolve entry ID
    if (isset($formidable_service)) {
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
        }
    }
}
?>

<div class="generator generator--biography biography-generator">
    <div class="generator__title">Professional Biography Generator</div>
    
    <!-- Section 1: Basic Information -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Basic Information</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="bio-name" class="field__label">Full Name</label>
                <input type="text" 
                       id="bio-name" 
                       name="name" 
                       class="field__input"
                       placeholder="Enter your full name">
            </div>
            
            <div class="field">
                <label for="bio-title" class="field__label">Professional Title</label>
                <input type="text" 
                       id="bio-title" 
                       name="title" 
                       class="field__input"
                       placeholder="e.g., CEO, Marketing Consultant, Business Coach">
            </div>
            
            <div class="field">
                <label for="bio-organization" class="field__label">Organization/Company (Optional)</label>
                <input type="text" 
                       id="bio-organization" 
                       name="organization" 
                       class="field__input"
                       placeholder="Your company or organization name">
            </div>
        </div>
    </div>
    
    <!-- Section 2: Authority Hook -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Authority Hook</div>
        </div>
        <div class="section__content">
            <?php
            // Use centralized Authority Hook Service instead of shared template
            global $authority_hook_service;
            
            if ($authority_hook_service) {
                // Get current authority hook data for Biography Generator
                $authority_hook_data = [];
                if ($entry_id) {
                    $hook_result = $authority_hook_service->get_authority_hook_data($entry_id);
                    $authority_hook_data = $hook_result['components'];
                }
                
                // Render options for Biography Generator
                $render_options = [
                    'show_preview' => true, // Biography shows preview/copy functionality
                    'show_examples' => true,
                    'show_audience_manager' => true,
                    'css_classes' => 'authority-hook',
                    'field_prefix' => 'mkcg-',
                    'tabs_enabled' => true
                ];
                
                // Use centralized service to render Authority Hook Builder
                echo $authority_hook_service->render_authority_hook_builder('biography', $authority_hook_data, $render_options);
                
                error_log('MKCG Biography: Authority Hook Builder rendered via centralized service');
            } else {
                echo '<div style="background: #ffebee; border: 1px solid #f44336; padding: 15px; border-radius: 4px;">❌ Authority Hook Service not available</div>';
                error_log('MKCG Biography: ERROR - Authority Hook Service not available');
            }
            ?>
        </div>
    </div>
    
    <!-- Section 3: Additional Details -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Biography Settings</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="bio-tone" class="field__label">Tone</label>
                <select id="bio-tone" name="tone" class="field__select">
                    <option value="professional">Professional</option>
                    <option value="conversational">Conversational</option>
                    <option value="authoritative">Authoritative</option>
                    <option value="friendly">Friendly</option>
                </select>
            </div>
            
            <div class="field">
                <label for="bio-length" class="field__label">Length</label>
                <select id="bio-length" name="length" class="field__select">
                    <option value="short">Short (50-75 words)</option>
                    <option value="medium" selected>Medium (100-150 words)</option>
                    <option value="long">Long (200-300 words)</option>
                </select>
            </div>
            
            <div class="field">
                <label for="bio-pov" class="field__label">Point of View</label>
                <select id="bio-pov" name="pov" class="field__select">
                    <option value="third" selected>Third Person (He/She/They)</option>
                    <option value="first">First Person (I/My)</option>
                </select>
            </div>
            
            <div class="field">
                <label for="bio-existing" class="field__label">Existing Biography (Optional)</label>
                <textarea id="bio-existing" 
                          name="existing_bio" 
                          class="field__textarea"
                          rows="4" 
                          placeholder="Paste your current biography here to improve it, or leave blank to create a new one"></textarea>
            </div>
            
            <div class="field">
                <label for="bio-notes" class="field__label">Additional Notes (Optional)</label>
                <textarea id="bio-notes" 
                          name="additional_notes" 
                          class="field__textarea"
                          rows="3" 
                          placeholder="Any specific achievements, awards, or details you want included"></textarea>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Generation Controls -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generate Biography</div>
        </div>
        <div class="section__content">
            <p class="field__description">
                Generate a professional biography in three different lengths (short, medium, and long) 
                based on your information and authority hook.
            </p>
            
            <div class="button-group">
                <button type="button" id="preview-bio" class="button button--preview">
                    Preview Information
                </button>
                <button type="button" id="generate-with-ai" class="button button--ai">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="button__icon">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Generate Biography with AI
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="bio-loading-overlay" class="loading" style="display: none;">
        <div class="loading__content">
            <div class="loading__spinner"></div>
            <div class="loading__message">Creating your professional biography...</div>
        </div>
    </div>
    
    <!-- Hidden fields -->
    <input type="hidden" id="bio-impact-intro" name="impact_intro" value="">
    <input type="hidden" id="biography-entry-id" value="<?php echo esc_attr($entry_id); ?>">
    <input type="hidden" id="biography-entry-key" value="<?php echo esc_attr($entry_key); ?>">
    <input type="hidden" id="biography-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
    
    <!-- Authority Hook data populated by centralized service -->
    <input type="hidden" id="mkcg-authority-hook" name="authority_hook" value="">
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Biography Generator JavaScript
    const BiographyGenerator = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            const generateBtn = document.getElementById('generate-with-ai');
            const previewBtn = document.getElementById('preview-bio');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generateBiography());
            }
            
            if (previewBtn) {
                previewBtn.addEventListener('click', () => this.previewBiography());
            }
        },
        
        generateBiography: function() {
            const formData = this.collectFormData();
            
            // Validate essential fields
            if (!formData.name && !formData.title) {
                alert('Please provide at least your name or professional title.');
                return;
            }
            
            if (!formData.authority_hook) {
                alert('Please complete your Authority Hook first.');
                return;
            }
            
            // Show loading
            this.showLoading('Creating your professional biography...');
            
            // Make AJAX request
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.wp.makeAjaxRequest('generate_biography', {form_data: formData}, {
                    onSuccess: (response) => {
                        this.hideLoading();
                        this.handleBiographySuccess(response);
                    },
                    onError: (error) => {
                        this.hideLoading();
                        alert('Error generating biography: ' + error);
                    }
                });
            } else {
                // Fallback for legacy compatibility
                this.generateBiographyLegacy(formData);
            }
        },
        
        generateBiographyLegacy: function(formData) {
            const postData = new URLSearchParams();
            postData.append('action', 'generate_biography');
            postData.append('form_data', JSON.stringify(formData));
            postData.append('security', document.getElementById('biography-nonce')?.value || '');
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: postData.toString()
            })
            .then(response => response.json())
            .then(response => {
                this.hideLoading();
                if (response.success) {
                    this.handleBiographySuccess(response.data);
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to generate biography'));
                }
            })
            .catch(error => {
                this.hideLoading();
                alert('Network error: ' + error.message);
            });
        },
        
        handleBiographySuccess: function(data) {
            // Store biography data and redirect to results page
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('temp_biography_data', JSON.stringify(data));
            }
            
            // For now, just show the biography in an alert (can be enhanced with a proper results page)
            let message = 'Biography generated successfully!\n\n';
            if (data.short) message += 'Short Version:\n' + data.short + '\n\n';
            if (data.medium) message += 'Medium Version:\n' + data.medium + '\n\n';
            if (data.long) message += 'Long Version:\n' + data.long;
            
            alert(message);
        },
        
        previewBiography: function() {
            const formData = this.collectFormData();
            
            // Create preview content
            let previewContent = '<div class="preview-content-inner">';
            if (formData.name) {
                previewContent += '<h3>' + this.escapeHtml(formData.name) + '</h3>';
            }
            if (formData.title || formData.organization) {
                previewContent += '<p><strong>';
                if (formData.title) previewContent += this.escapeHtml(formData.title);
                if (formData.title && formData.organization) previewContent += ' at ';
                if (formData.organization) previewContent += this.escapeHtml(formData.organization);
                previewContent += '</strong></p>';
            }
            if (formData.authority_hook) {
                previewContent += '<p><strong>Authority Hook:</strong><br>' + this.escapeHtml(formData.authority_hook) + '</p>';
            }
            previewContent += '<p><strong>Settings:</strong><br>';
            previewContent += 'Tone: ' + formData.tone + '<br>';
            previewContent += 'Length: ' + formData.length + '<br>';
            previewContent += 'Point of View: ' + formData.pov + '</p>';
            previewContent += '</div>';
            
            this.showModal('Biography Preview', previewContent);
        },
        
        collectFormData: function() {
            return {
                name: document.getElementById('bio-name')?.value || '',
                title: document.getElementById('bio-title')?.value || '',
                organization: document.getElementById('bio-organization')?.value || '',
                authority_hook: document.getElementById('mkcg-authority-hook')?.value || '',
                impact_intro: document.getElementById('bio-impact-intro')?.value || '',
                tone: document.getElementById('bio-tone')?.value || 'professional',
                length: document.getElementById('bio-length')?.value || 'medium',
                pov: document.getElementById('bio-pov')?.value || 'third',
                existing_bio: document.getElementById('bio-existing')?.value || '',
                additional_notes: document.getElementById('bio-notes')?.value || ''
            };
        },
        
        showModal: function(title, content) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal__inner">
                    <div class="modal__header">
                        <h2 class="modal__title">${this.escapeHtml(title)}</h2>
                        <button type="button" class="modal__close">&times;</button>
                    </div>
                    <div class="modal__content">${content}</div>
                    <div class="modal__footer">
                        <button type="button" class="button button--preview modal-close">Close</button>
                        <button type="button" class="button button--ai" onclick="BiographyGenerator.generateBiography(); document.querySelector('.modal').remove();">Generate Biography</button>
                    </div>
                </div>
            `;
            
            // Handle close events
            modal.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal__close') || e.target.classList.contains('modal-close')) {
                    modal.remove();
                }
            });
            
            document.body.appendChild(modal);
        },
        
        showLoading: function(message = 'Loading...') {
            const overlay = document.getElementById('bio-loading-overlay');
            if (overlay) {
                const messageEl = overlay.querySelector('.loading__message');
                if (messageEl) {
                    messageEl.textContent = message;
                }
                overlay.style.display = 'flex';
            }
        },
        
        hideLoading: function() {
            const overlay = document.getElementById('bio-loading-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when page loads
    BiographyGenerator.init();
    
    // Make globally available
    window.BiographyGenerator = BiographyGenerator;
});
</script>