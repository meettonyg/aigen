<?php
/**
 * Offers Generator Template - BEM Methodology
 * Default template for generating service offers and packages
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

<div class="generator offers-generator">
    <div class="generator__title">Service Offers Generator</div>
    
    <!-- Section 1: Authority Hook -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Authority Hook</div>
        </div>
        <div class="section__content">
            <?php
            // Include the shared Authority Hook component
            $current_values = [];
            include MKCG_PLUGIN_PATH . 'templates/shared/authority-hook-component.php';
            ?>
        </div>
    </div>
    
    <!-- Section 2: Business Details -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Business Information</div>
        </div>
        <div class="section__content">
            <div class="field">
                <label for="offers-business-type" class="field__label">Business Type</label>
                <select id="offers-business-type" name="business_type" class="field__select" required>
                    <option value="">Select your business type</option>
                    <option value="consulting">Consulting</option>
                    <option value="coaching">Coaching</option>
                    <option value="training">Training</option>
                    <option value="service">Service Provider</option>
                    <option value="product">Product Business</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="field">
                <label for="offers-target-audience" class="field__label">Target Audience</label>
                <textarea id="offers-target-audience" 
                          name="target_audience" 
                          class="field__textarea"
                          rows="3" 
                          placeholder="Describe your ideal clients in detail. e.g., Small business owners with 10-50 employees who struggle with marketing"
                          required></textarea>
                <p class="field__help">
                    Be specific about who you serve, their challenges, and their goals.
                </p>
            </div>
            
            <div class="field">
                <label for="offers-price-range" class="field__label">Price Range</label>
                <select id="offers-price-range" name="price_range" class="field__select">
                    <option value="budget">Budget ($100-$500)</option>
                    <option value="mid" selected>Mid-range ($500-$2,000)</option>
                    <option value="premium">Premium ($2,000-$10,000)</option>
                    <option value="luxury">Luxury ($10,000+)</option>
                </select>
            </div>
            
            <div class="field">
                <label for="offers-delivery-method" class="field__label">Delivery Method</label>
                <select id="offers-delivery-method" name="delivery_method" class="field__select">
                    <option value="online" selected>Online/Virtual</option>
                    <option value="in-person">In-Person</option>
                    <option value="hybrid">Hybrid</option>
                    <option value="self-paced">Self-Paced</option>
                    <option value="group">Group Sessions</option>
                </select>
            </div>
            
            <div class="field">
                <label for="offers-count" class="field__label">Number of Offers to Generate</label>
                <input type="number" 
                       id="offers-count" 
                       name="offer_count" 
                       class="field__input"
                       min="1" 
                       max="10" 
                       value="5">
                <p class="field__help">
                    Generate between 1-10 different service offers.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Generation Controls -->
    <div class="section">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generate Offers</div>
        </div>
        <div class="section__content">
            <p class="field__description">
                Generate compelling service offers and packages based on your authority and business details. 
                Each offer will include a benefit-focused title, description, and clear value proposition.
            </p>
            
            <div class="button-group">
                <button type="button" id="generate-offers-btn" class="button button--ai">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="button__icon">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Generate Offers with AI
                </button>
            </div>
        </div>
    </div>
    
    <!-- Results Section (Initially Hidden) -->
    <div id="offers-results" class="section" style="display: none;">
        <div class="section__header">
            <div class="section__number"></div>
            <div class="section__title">Generated Offers</div>
        </div>
        <div class="section__content">
            <div id="offers-list" class="results">
                <!-- Offers will be populated here by JavaScript -->
            </div>
            
            <div class="button-group">
                <button type="button" id="copy-all-offers-btn" class="button button--copy">
                    Copy All Offers
                </button>
                <button type="button" id="regenerate-offers-btn" class="button button--ai">
                    Regenerate Offers
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="offers-loading-overlay" class="loading" style="display: none;">
        <div class="loading__content">
            <div class="loading__spinner"></div>
            <div class="loading__message">Generating service offers...</div>
        </div>
    </div>
    
    <!-- Hidden fields for data -->
    <input type="hidden" id="offers-entry-id" value="<?php echo esc_attr($entry_id); ?>">
    <input type="hidden" id="offers-entry-key" value="<?php echo esc_attr($entry_key); ?>">
    <input type="hidden" id="offers-nonce" value="<?php echo wp_create_nonce('mkcg_nonce'); ?>">
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Offers Generator
    const OffersGenerator = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            const generateBtn = document.getElementById('generate-offers-btn');
            const regenerateBtn = document.getElementById('regenerate-offers-btn');
            const copyAllBtn = document.getElementById('copy-all-offers-btn');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generateOffers());
            }
            
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', () => this.generateOffers());
            }
            
            if (copyAllBtn) {
                copyAllBtn.addEventListener('click', () => this.copyAllOffers());
            }
        },
        
        generateOffers: function() {
            const authorityHook = document.getElementById('mkcg-authority-hook')?.value;
            const businessType = document.getElementById('offers-business-type')?.value;
            const targetAudience = document.getElementById('offers-target-audience')?.value;
            const priceRange = document.getElementById('offers-price-range')?.value;
            const deliveryMethod = document.getElementById('offers-delivery-method')?.value;
            const offerCount = document.getElementById('offers-count')?.value;
            const entryId = document.getElementById('offers-entry-id')?.value;
            const entryKey = document.getElementById('offers-entry-key')?.value;
            
            // Validate required fields
            if (!authorityHook || authorityHook.trim() === '') {
                alert('Please complete your Authority Hook first.');
                return;
            }
            
            if (!businessType) {
                alert('Please select your business type.');
                return;
            }
            
            if (!targetAudience || targetAudience.trim() === '') {
                alert('Please describe your target audience.');
                return;
            }
            
            // Show loading
            this.showLoading('Generating compelling service offers...');
            
            // Prepare data
            const data = {
                authority_hook: authorityHook,
                business_type: businessType,
                target_audience: targetAudience,
                price_range: priceRange || 'mid',
                delivery_method: deliveryMethod || 'online',
                offer_count: parseInt(offerCount) || 5,
                entry_id: entryId || '',
                entry_key: entryKey || ''
            };
            
            // Make AJAX request using FormUtils
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.wp.makeAjaxRequest('generate_offers', data, {
                    onSuccess: (response) => {
                        this.hideLoading();
                        this.displayOffers(response.content.offers);
                    },
                    onError: (error) => {
                        this.hideLoading();
                        alert('Error generating offers: ' + error);
                    }
                });
            } else {
                // Fallback for legacy compatibility
                this.generateOffersLegacy(data);
            }
        },
        
        generateOffersLegacy: function(data) {
            // Legacy AJAX call for backwards compatibility
            const postData = new URLSearchParams();
            postData.append('action', 'generate_offers');
            postData.append('security', document.getElementById('offers-nonce')?.value || '');
            
            // Add all data fields
            Object.keys(data).forEach(key => {
                postData.append(key, data[key]);
            });
            
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
                    this.displayOffers(response.data.offers || response.data.content?.offers);
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to generate offers'));
                }
            })
            .catch(error => {
                this.hideLoading();
                alert('Network error: ' + error.message);
            });
        },
        
        displayOffers: function(offers) {
            const resultsSection = document.getElementById('offers-results');
            const offersList = document.getElementById('offers-list');
            
            if (!offersList || !offers || offers.length === 0) {
                alert('No offers were generated. Please try again.');
                return;
            }
            
            // Clear previous results
            offersList.innerHTML = '';
            
            // Add offers to the list
            offers.forEach((offer, index) => {
                const offerElement = document.createElement('div');
                offerElement.className = 'offer';
                offerElement.innerHTML = `
                    <div class="offer__title">Offer ${index + 1}</div>
                    <div class="offer__description">${this.escapeHtml(offer)}</div>
                    <button type="button" class="button button--use" onclick="OffersGenerator.useOffer('${this.escapeHtml(offer)}', ${index + 1})">
                        Use Offer
                    </button>
                `;
                offersList.appendChild(offerElement);
            });
            
            // Show results section
            resultsSection.style.display = 'block';
            
            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        
        useOffer: function(offer, number) {
            // Copy the offer to clipboard
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.ui.copyToClipboard(offer);
            } else {
                this.copyToClipboard(offer);
            }
        },
        
        copyAllOffers: function() {
            const offerElements = document.querySelectorAll('.offer__description');
            if (offerElements.length === 0) {
                alert('No offers to copy.');
                return;
            }
            
            let allOffers = '';
            offerElements.forEach((element, index) => {
                allOffers += `${index + 1}. ${element.textContent}\n\n`;
            });
            
            if (typeof MKCG_FormUtils !== 'undefined') {
                MKCG_FormUtils.ui.copyToClipboard(allOffers);
            } else {
                this.copyToClipboard(allOffers);
            }
        },
        
        copyToClipboard: function(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(() => alert('Offer copied to clipboard!'))
                    .catch(() => this.fallbackCopy(text));
            } else {
                this.fallbackCopy(text);
            }
        },
        
        fallbackCopy: function(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Offer copied to clipboard!');
            } catch (err) {
                alert('Unable to copy. Please copy manually.');
            }
            document.body.removeChild(textarea);
        },
        
        showLoading: function(message = 'Loading...') {
            const overlay = document.getElementById('offers-loading-overlay');
            if (overlay) {
                const messageEl = overlay.querySelector('.loading__message');
                if (messageEl) {
                    messageEl.textContent = message;
                }
                overlay.style.display = 'flex';
            }
        },
        
        hideLoading: function() {
            const overlay = document.getElementById('offers-loading-overlay');
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
    OffersGenerator.init();
    
    // Make globally available
    window.OffersGenerator = OffersGenerator;
});
</script>