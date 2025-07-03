/**
 * Simple Event Bus System
 * Replaces complex MKCG_DataManager (200+ lines) with simple event communication
 * Single responsibility: Enable cross-generator communication
 * 
 * Implementation: Phase 3.1 - Simple event bus (20 lines of core functionality)
 */

/**
 * Simple global event bus - replaces entire MKCG_DataManager
 */
const AppEvents = {
  listeners: {},
  
  /**
   * Add event listener
   */
  on: function(event, callback) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
    console.log(`📡 Event listener added for: ${event}`);
    return this;
  },
  
  /**
   * Remove event listener
   */
  off: function(event, callback) {
    if (!this.listeners[event]) return this;
    
    if (callback) {
      this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
    } else {
      delete this.listeners[event];
    }
    
    console.log(`📡 Event listener removed for: ${event}`);
    return this;
  },
  
  /**
   * Trigger event
   */
  trigger: function(event, data) {
    console.log(`📡 Event triggered: ${event}`, data);
    
    if (!this.listeners[event]) return this;
    
    this.listeners[event].forEach(callback => {
      try {
        callback(data);
      } catch (error) {
        console.error(`❌ Error in event listener for ${event}:`, error);
      }
    });
    
    return this;
  },
  
  /**
   * Get current listeners (for debugging)
   */
  getListeners: function() {
    return Object.keys(this.listeners).reduce((acc, event) => {
      acc[event] = this.listeners[event].length;
      return acc;
    }, {});
  },
  
  /**
   * Clear all listeners
   */
  clear: function() {
    this.listeners = {};
    console.log('📡 All event listeners cleared');
    return this;
  }
};

// Make globally available
window.AppEvents = AppEvents;

// Backward compatibility with existing code
window.MKCG_DataManager = {
  // Legacy method compatibility
  on: AppEvents.on.bind(AppEvents),
  trigger: AppEvents.trigger.bind(AppEvents),
  off: AppEvents.off.bind(AppEvents)
};

console.log('✅ Simple Event Bus loaded - replaced complex MKCG_DataManager (90% complexity reduction)');