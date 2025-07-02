/**
 * Simple AJAX Manager - Phase 2 Simplification
 * Replaces enhanced-ajax-manager.js (2,500+ lines) with simple fetch() wrapper
 * Single responsibility: Handle WordPress AJAX requests cleanly
 */

/**
 * Simple AJAX function - replaces all complex AJAX systems
 */
async function makeAjaxRequest(action, data = {}) {
    // Get nonce from multiple possible sources
    const nonce = window.mkcg_vars?.nonce || 
                  window.topics_vars?.nonce || 
                  window.questions_vars?.nonce || 
                  document.querySelector('#topics-generator-nonce')?.value ||
                  document.querySelector('input[name*="nonce"]')?.value || '';
    
    // Get AJAX URL
    const ajaxUrl = window.ajaxurl || 
                    window.mkcg_vars?.ajax_url || 
                    '/wp-admin/admin-ajax.php';
    
    // Prepare request data
    const requestData = new URLSearchParams();
    requestData.append('action', action);
    requestData.append('nonce', nonce);
    requestData.append('security', nonce); // WordPress backup nonce field
    
    // Add data parameters
    Object.keys(data).forEach(key => {
        if (data[key] !== null && data[key] !== undefined) {
            if (typeof data[key] === 'object') {
                requestData.append(key, JSON.stringify(data[key]));
            } else {
                requestData.append(key, data[key]);
            }
        }
    });
    
    try {
        const response = await fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: requestData.toString()
        });
        
        if (!response.ok) {
            throw new Error(`Request failed: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();
        
        // Handle WordPress AJAX response format
        if (result.success === false) {
            const message = result.data?.message || result.data || 'Request failed';
            throw new Error(`Server error: ${message}`);
        }
        
        return result.data || result;
        
    } catch (error) {
        console.error(`AJAX request failed for action "${action}":`, error);
        throw error;
    }
}

/**
 * Make AJAX request with callback support (for compatibility)
 */
function makeAjaxRequestWithCallbacks(action, data = {}, options = {}) {
    const { onSuccess, onError, onComplete } = options;
    
    makeAjaxRequest(action, data)
        .then(result => {
            if (onSuccess) onSuccess(result);
            return result;
        })
        .catch(error => {
            if (onError) onError(error.message || error);
            throw error;
        })
        .finally(() => {
            if (onComplete) onComplete();
        });
    
    return makeAjaxRequest(action, data);
}

// Make globally available
window.makeAjaxRequest = makeAjaxRequest;
window.makeAjaxRequestWithCallbacks = makeAjaxRequestWithCallbacks;

console.log('✅ Simple AJAX system loaded - replaced EnhancedAjaxManager');
