/**
 * Cross-Browser Compatibility Fixes for Media Kit Content Generator
 * Addresses specific issues in Safari, Firefox, and Edge
 */

(function() {
  'use strict';
  
  // Only run when document is fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Detect browser for specific fixes
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    const isEdge = /Edge\/\d./i.test(navigator.userAgent) || /Edg\/\d./i.test(navigator.userAgent);
    
    // Fix Safari clipboard issues
    if (isSafari) {
      fixSafariClipboard();
    }
    
    // Fix Firefox focus management
    if (isFirefox) {
      fixFirefoxFocusManagement();
    }
    
    // Fix Edge animation performance
    if (isEdge) {
      fixEdgeAnimations();
    }
    
    // Apply fixes for all browsers
    fixCommonIssues();
    
    // Enhance keyboard accessibility across all browsers
    enhanceKeyboardAccessibility();
  });
  
  /**
   * Fixes clipboard operations in Safari
   */
  function fixSafariClipboard() {
    // Find all copy buttons
    const copyButtons = document.querySelectorAll('.tagline-generator__copy-button');
    
    copyButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        const textToCopy = this.getAttribute('data-clipboard-text');
        
        // Safari needs a fallback method for clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
          // Modern API
          navigator.clipboard.writeText(textToCopy).then(() => {
            showCopySuccess(this);
          }).catch(() => {
            // Fallback for Safari
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            textarea.style.position = 'fixed';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
              document.execCommand('copy');
              showCopySuccess(this);
            } catch (err) {
              console.error('Failed to copy text: ', err);
            }
            
            document.body.removeChild(textarea);
          });
        } else {
          // Fallback for older browsers
          const textarea = document.createElement('textarea');
          textarea.value = textToCopy;
          textarea.style.position = 'fixed';
          document.body.appendChild(textarea);
          textarea.select();
          
          try {
            document.execCommand('copy');
            showCopySuccess(this);
          } catch (err) {
            console.error('Failed to copy text: ', err);
          }
          
          document.body.removeChild(textarea);
        }
      });
    });
  }
  
  /**
   * Shows a success message when copy is successful
   */
  function showCopySuccess(button) {
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    
    setTimeout(() => {
      button.textContent = originalText;
    }, 2000);
  }
  
  /**
   * Fixes focus management issues in Firefox
   */
  function fixFirefoxFocusManagement() {
    // Ensure proper focus for form elements
    const formElements = document.querySelectorAll('.tagline-generator__settings input, .tagline-generator__settings select, .tagline-generator__settings textarea');
    
    formElements.forEach(element => {
      element.addEventListener('focus', function() {
        this.classList.add('is-focused');
      });
      
      element.addEventListener('blur', function() {
        this.classList.remove('is-focused');
      });
    });
    
    // Fix focus trap in modal dialogs for Firefox
    const modals = document.querySelectorAll('.tagline-generator__modal');
    
    modals.forEach(modal => {
      if (modal) {
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        
        if (focusableElements.length) {
          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];
          
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
        }
      }
    });
  }
  
  /**
   * Fixes animation and performance issues in Edge
   */
  function fixEdgeAnimations() {
    // Add will-change hints for performance
    const animatedElements = document.querySelectorAll('.tagline-generator__option, .tagline-generator__loading');
    
    animatedElements.forEach(element => {
      element.style.willChange = 'transform, opacity';
    });
    
    // Simplify complex animations for Edge
    const loadingElements = document.querySelectorAll('.tagline-generator__loading');
    
    loadingElements.forEach(element => {
      if (element.style.animation) {
        // Use simpler animation in Edge
        element.style.animation = 'fadeInOut 1.5s ease-in-out infinite';
      }
    });
  }
  
  /**
   * Fixes common issues across all browsers
   */
  function fixCommonIssues() {
    // Fix for iOS/mobile click delay
    const clickableElements = document.querySelectorAll('.tagline-generator__option, .tagline-generator__copy-button, .tagline-generator__controls button');
    
    clickableElements.forEach(element => {
      element.style.touchAction = 'manipulation';
    });
    
    // Prevent form submission on enter key
    const textInputs = document.querySelectorAll('.tagline-generator__settings input[type="text"], .tagline-generator__settings textarea');
    
    textInputs.forEach(input => {
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
          e.preventDefault();
        }
      });
    });
    
    // Fix for viewport height issues on mobile
    function setMobileHeight() {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setMobileHeight();
    window.addEventListener('resize', setMobileHeight);
  }
  
  /**
   * Enhances keyboard accessibility across all browsers
   */
  function enhanceKeyboardAccessibility() {
    // Make tagline options keyboard selectable
    const taglineOptions = document.querySelectorAll('.tagline-generator__option');
    
    taglineOptions.forEach(option => {
      option.setAttribute('tabindex', '0');
      
      option.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.click();
        }
      });
    });
  }
})();
