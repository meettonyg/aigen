/**
 * Simple AJAX Manager
 * Single responsibility: Handle AJAX requests cleanly
 * Eliminates: Enterprise error handling, retry logic, network detection, request queuing
 */

class SimpleAjaxManager {
    constructor() {
        this.ajaxUrl = window.ajaxurl || window.mkcg_vars?.ajax_url || '/wp-admin/admin-ajax.php';
        this.nonce = window.mkcg_vars?.nonce || '';
        
        console.log('Simple AJAX Manager initialized');
    }
    
    /**
     * Make AJAX request - direct and simple
     */
    async request(action, data = {}) {
        // Prepare form data
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', this.nonce);
        
        // Add data parameters
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                if (typeof data[key] === 'object') {
                    formData.append(key, JSON.stringify(data[key]));
                } else {
                    formData.append(key, data[key]);
                }
            }
        });
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success === false) {
                throw new Error(result.data?.message || result.data || 'Request failed');
            }
            
            return result.data || result;
            
        } catch (error) {
            console.error('AJAX request failed:', error);
            throw error;
        }
    }
    
    /**
     * Save topics data
     */
    async saveTopics(entryId, topics) {
        return this.request('mkcg_save_topics_data', {
            entry_id: entryId,
            topics: topics
        });
    }
    
    /**
     * Get topics data
     */
    async getTopics(entryId) {
        return this.request('mkcg_get_topics_data', {
            entry_id: entryId
        });
    }
    
    /**
     * Save authority hook
     */
    async saveAuthorityHook(entryId, authorityHookData) {
        return this.request('mkcg_save_authority_hook', {
            entry_id: entryId,
            ...authorityHookData
        });
    }
    
    /**
     * Generate topics
     */
    async generateTopics(authorityHook, audience = '') {
        return this.request('mkcg_generate_topics', {
            authority_hook: authorityHook,
            audience: audience
        });
    }
    
    /**
     * Update nonce if needed
     */
    setNonce(nonce) {
        this.nonce = nonce;
    }
    
    /**
     * Update AJAX URL if needed
     */
    setAjaxUrl(url) {
        this.ajaxUrl = url;
    }
}

// Initialize global instance
window.SimpleAjaxManager = new SimpleAjaxManager();

// Backward compatibility
window.ajaxManager = window.SimpleAjaxManager;

console.log('Simple AJAX Manager loaded successfully');
