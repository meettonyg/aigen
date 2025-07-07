/**
 * Cross-Browser Compatibility Utilities for Media Kit Content Generator
 * Root-level implementation using feature detection instead of browser detection
 */

(function() {
  'use strict';
  
  // Global namespace for compatibility utilities
  window.MKCG_Compatibility = {
    /**
     * Initialize compatibility features when DOM is ready
     */
    init: function() {
      // Only initialize once
      if (this.initialized) return;
      
      console.log('Initializing cross-browser compatibility utilities...');
      
      // Apply enhancements
      this.enhanceClipboardOperations();
      this.enhanceFocusManagement();
      this.enhanceAnimationPerformance();
      this.enhanceCommonInteractions();
      this.enhanceKeyboardAccessibility();
      
      this.initialized = true;
      console.log('Cross-browser compatibility utilities initialized');
    },
    
    /**
     * Enhance clipboard operations with feature detection
     * Works across all browsers without user agent sniffing
     */
    enhanceClipboardOperations: function() {
      // Check if Clipboard API is available
      const hasClipboardAPI = !!navigator.clipboard && !!navigator.clipboard.writeText;
      console.log('Clipboard API available:', hasClipboardAPI);
      
      // Find all copy buttons
      const copyButtons = document.querySelectorAll('.tagline-generator__copy-button, .tagline-generator__option-copy, [data-clipboard-text]');
      
      copyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Get text to copy from either data attribute or target element
          const textToCopy = this.getAttribute('data-clipboard-text') || 
                             this.getAttribute('data-target') ? 
                             document.querySelector(this.getAttribute('data-target')).textContent : 
                             '';
          
          if (!textToCopy) {
            console.error('No text to copy found');
            return;
          }
          
          // Feature detection instead of browser detection
          if (hasClipboardAPI) {
            // Modern Clipboard API
            navigator.clipboard.writeText(textToCopy)
              .then(() => showCopySuccess(this))
              .catch(err => {
                console.error('Clipboard API failed:', err);
                fallbackCopyToClipboard(textToCopy, this);
              });
          } else {
            // Fallback for browsers without Clipboard API
            fallbackCopyToClipboard(textToCopy, this);
          }
        });
      });
      
      /**
       * Fallback clipboard copy method using execCommand
       * @param {string} text - Text to copy
       * @param {HTMLElement} button - Button that triggered the copy
       */
      function fallbackCopyToClipboard(text, button) {
        try {
          const textarea = document.createElement('textarea');
          textarea.value = text;
          
          // Make the textarea out of viewport
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          textarea.style.pointerEvents = 'none';
          document.body.appendChild(textarea);
          
          // Check if we're on iOS
          const isiOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
          
          if (isiOS) {
            // iOS requires a different approach
            const range = document.createRange();
            range.selectNodeContents(textarea);
            
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            textarea.setSelectionRange(0, text.length);
          } else {
            textarea.focus();
            textarea.select();
          }
          
          // Execute copy command
          const successful = document.execCommand('copy');
          
          // Remove temporary element
          document.body.removeChild(textarea);
          
          if (successful) {
            showCopySuccess(button);
          } else {
            console.error('Fallback copy failed with execCommand');
            showCopyError(button);
          }
        } catch (err) {
          console.error('Fallback copy method failed:', err);
          showCopyError(button);
        }
      }
      
      /**
       * Shows a success message when copy is successful
       * @param {HTMLElement} button - Button that triggered the copy
       */
      function showCopySuccess(button) {
        const originalText = button.textContent;
        const hasLabel = button.getAttribute('data-success-label');
        
        // Use either the provided success label or default to "Copied!"
        button.textContent = hasLabel || 'Copied!';
        
        // Add success class if defined in CSS
        button.classList.add('generator__button--success');
        
        setTimeout(() => {
          button.textContent = originalText;
          button.classList.remove('generator__button--success');
        }, 2000);
      }
      
      /**
       * Shows an error message when copy fails
       * @param {HTMLElement} button - Button that triggered the copy
       */
      function showCopyError(button) {
        const originalText = button.textContent;
        button.textContent = 'Failed';
        button.classList.add('generator__button--error');
        
        setTimeout(() => {
          button.textContent = originalText;
          button.classList.remove('generator__button--error');
        }, 2000);
      }
    },
    
    /**
     * Enhance focus management for better accessibility
     * Works across all browsers with focus/blur events
     */
    enhanceFocusManagement: function() {
      // Ensure proper focus for all form elements and interactive components
      const formElements = document.querySelectorAll('.field__input, .tagline-generator__settings input, .tagline-generator__settings select, .tagline-generator__settings textarea');
      
      formElements.forEach(element => {
        element.addEventListener('focus', function() {
          this.classList.add('is-focused');
          const field = this.closest('.field');
          if (field) field.classList.add('field--focused');
        });
        
        element.addEventListener('blur', function() {
          this.classList.remove('is-focused');
          const field = this.closest('.field');
          if (field) field.classList.remove('field--focused');
        });
      });
      
      // Make interactive elements focusable
      const interactiveElements = document.querySelectorAll('.tagline-generator__option');
      
      interactiveElements.forEach(element => {
        // Skip if already has tabindex
        if (!element.hasAttribute('tabindex')) {
          element.setAttribute('tabindex', '0');
        }
        
        // Add keyboard event handling
        element.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
          }
        });
      });
      
      // Add focus trap for modal dialogs for better accessibility
      const modals = document.querySelectorAll('.tagline-generator__modal, .generator__modal');
      
      modals.forEach(modal => {
        if (modal) {
          const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
          
          if (focusableElements.length) {
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            // Trap focus in modal
            lastElement.addEventListener('keydown', function(e) {
              if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                firstElement.focus();
              }
            });
            
            firstElement.addEventListener('keydown', function(e) {
              if (e.key === 'Tab' && e.shiftKey) {
                e.preventDefault();
                lastElement.focus();
              }
            });
            
            // Focus first element when modal opens
            modal.addEventListener('modalopen', function() {
              setTimeout(() => {
                firstElement.focus();
              }, 100);
            });
          }
        }
      });
    },
    
    /**
     * Enhance animation performance with appropriate properties
     * Uses feature detection for hardware acceleration
     */
    enhanceAnimationPerformance: function() {
      // Check for GPU acceleration support
      const hasGPUAcceleration = (function() {
        const el = document.createElement('div');
        const has3d = el.style.transform !== undefined;
        const hasWillChange = el.style.willChange !== undefined;
        return has3d && hasWillChange;
      })();
      
      console.log('GPU acceleration support:', hasGPUAcceleration);
      
      // Apply appropriate optimizations based on feature support
      const animatedElements = document.querySelectorAll('.tagline-generator__option, .tagline-generator__loading, .generator__loading, .generator__fade-in, .generator__slide-in-up');
      
      animatedElements.forEach(element => {
        if (hasGPUAcceleration) {
          // Modern browsers - use will-change for better performance
          element.style.willChange = 'transform, opacity';
        } else {
          // Older browsers - force hardware acceleration with transform
          element.style.transform = 'translateZ(0)';
        }
      });
      
      // Optimize loading animations
      const loadingElements = document.querySelectorAll('.tagline-generator__loading, .generator__loading');
      
      loadingElements.forEach(element => {
        // Check if browser supports CSS transitions
        const hasTransitions = 'transition' in document.documentElement.style || 
                              'WebkitTransition' in document.documentElement.style;
        
        if (!hasTransitions) {
          // Simplified animation for older browsers
          element.style.animation = 'none';
          element.style.opacity = '1';
        }
      });
    },
    
    /**
     * Enhance common interactions with cross-browser optimizations
     */
    enhanceCommonInteractions: function() {
      // Fix for iOS/mobile click delay using touch-action
      const clickableElements = document.querySelectorAll('.tagline-generator__option, .tagline-generator__copy-button, .tagline-generator__controls button, .generator__button');
      
      clickableElements.forEach(element => {
        element.style.touchAction = 'manipulation';
      });
      
      // Prevent form submission on enter key where appropriate
      const textInputs = document.querySelectorAll('.tagline-generator__settings input[type="text"], .tagline-generator__settings textarea');
      
      textInputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && !e.target.form) {
            e.preventDefault();
          }
        });
      });
      
      // Fix for viewport height issues on mobile
      function setMobileHeight() {
        // Only apply on mobile devices
        if (window.innerWidth <= 768) {
          const vh = window.innerHeight * 0.01;
          document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
      }
      
      setMobileHeight();
      window.addEventListener('resize', setMobileHeight);
      
      // Handle orientation change on mobile devices
      window.addEventListener('orientationchange', function() {
        setTimeout(setMobileHeight, 100);
      });
    },
    
    /**
     * Enhance keyboard accessibility across all browsers
     */
    enhanceKeyboardAccessibility: function() {
      // Make cards and option cards keyboard accessible
      const interactiveCards = document.querySelectorAll('.tagline-generator__option, .generator__card');
      
      interactiveCards.forEach(card => {
        if (!card.hasAttribute('tabindex')) {
          card.setAttribute('tabindex', '0');
        }
        
        card.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
          }
        });
      });
      
      // Add skip-to-content link for screen readers and keyboard users
      if (!document.getElementById('skip-to-content')) {
        const skipLink = document.createElement('a');
        skipLink.id = 'skip-to-content';
        skipLink.href = '#main-content';
        skipLink.className = 'generator__skip-link';
        skipLink.textContent = 'Skip to main content';
        
        // Add skip link to the page
        document.body.insertBefore(skipLink, document.body.firstChild);
        
        // Add main content ID to generator container
        const generatorContainer = document.querySelector('.generator__container');
        if (generatorContainer && !generatorContainer.id) {
          generatorContainer.id = 'main-content';
        }
      }
    }
  };
  
  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    window.MKCG_Compatibility.init();
  });
})();