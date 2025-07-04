/**
 * Authority Hook Service Integration - JavaScript
 * 
 * Handles client-side integration with the centralized MKCG_Authority_Hook_Service
 * Provides unified functionality across all generators
 * 
 * @package Media_Kit_Content_Generator
 * @version 2.0
 */

(function() {
    'use strict';
    
    // Authority Hook Service Manager
    window.AuthorityHookServiceManager = {
        
        // Service configuration
        config: {
            ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
            nonce: window.mkcg_vars?.nonce || '',
            endpoints: {
                save: 'mkcg_save_authority_hook',
                get: 'mkcg_get_authority_hook',
                validate: 'mkcg_validate_authority_hook'
            },
            fieldPrefix: 'mkcg-',
            autoSaveDelay: 1000 // Auto-save delay in milliseconds
        },
        
        // Internal state
        state: {
            currentPostId: 0,
            autoSaveTimeout: null,
            isInitialized: false,
            instances: {} // Track multiple instances on same page
        },
        
        /**
         * Initialize the service manager
         */
        init: function(postId = 0) {
            console.log('🚀 Authority Hook Service Manager: Initializing');
            
            this.state.currentPostId = postId;
            this.state.isInitialized = true;
            
            // Set up global event listeners
            this.bindGlobalEvents();
            
            // Auto-detect and initialize all Authority Hook instances
            this.initializeInstances();
            
            console.log('✅ Authority Hook Service Manager: Initialized with post ID:', postId);
        }
    };
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Get post ID from various sources
            let postId = 0;
            
            // Try to get from Topics Generator
            const topicsPostIdField = document.getElementById('topics-generator-post-id');
            if (topicsPostIdField) {
                postId = parseInt(topicsPostIdField.value) || 0;
            }
            
            // Try to get from global data
            if (!postId && window.MKCG_Topics_Data) {
                postId = window.MKCG_Topics_Data.postId || 0;
            }
            
            window.AuthorityHookServiceManager.init(postId);
        });
    } else {
        // DOM already ready
        setTimeout(() => {
            let postId = 0;
            const topicsPostIdField = document.getElementById('topics-generator-post-id');
            if (topicsPostIdField) {
                postId = parseInt(topicsPostIdField.value) || 0;
            }
            window.AuthorityHookServiceManager.init(postId);
        }, 100);
    }
    
})();

console.log('✅ Authority Hook Service Integration loaded');
