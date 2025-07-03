/**
 * Simple Notifications System
 * Replaces complex enhanced-ui-feedback.js (400+ lines) with simple toast system
 * Single responsibility: Show non-blocking user notifications
 * 
 * Implementation: Phase 2.3 - Simple notification function
 */

/**
 * Simple notification function - replaces alert()
 */
function showNotification(message, type = 'info', duration = 3000) {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `mkcg-notification mkcg-notification--${type}`;
  
  // Set notification content
  if (typeof message === 'object' && message.title) {
    notification.innerHTML = `
      <div class=\"mkcg-notification__title\">${message.title}</div>
      <div class=\"mkcg-notification__message\">${message.message || ''}</div>
    `;
  } else {
    notification.textContent = typeof message === 'string' ? message : (message.message || 'Notification');
  }
  
  // Position and style
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
    z-index: 9999;
    max-width: 400px;
    word-wrap: break-word;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease;
    cursor: pointer;
  `;
  
  // Type-specific colors
  const colors = {
    success: '#27ae60',
    error: '#e74c3c',
    warning: '#f39c12',
    info: '#3498db'
  };
  notification.style.backgroundColor = colors[type] || colors.info;
  
  // Add to DOM
  document.body.appendChild(notification);
  
  // Auto-dismiss
  if (duration > 0) {
    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.remove();
          }
        }, 300);
      }
    }, duration);
  }
  
  // Click to dismiss
  notification.addEventListener('click', () => {
    if (notification.parentNode) {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 300);
    }
  });
  
  return notification;
}

/**
 * Convenience methods
 */
function showSuccess(message, duration = 3000) {
  return showNotification(message, 'success', duration);
}

function showError(message, duration = 5000) {
  return showNotification(message, 'error', duration);
}

function showWarning(message, duration = 4000) {
  return showNotification(message, 'warning', duration);
}

function showInfo(message, duration = 3000) {
  return showNotification(message, 'info', duration);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
  
  .mkcg-notification {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.4;
  }
  
  .mkcg-notification__title {
    font-weight: 600;
    margin-bottom: 4px;
  }
  
  .mkcg-notification__message {
    font-weight: 400;
    opacity: 0.9;
  }
`;
document.head.appendChild(style);

// Make globally available
window.showNotification = showNotification;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;

console.log('✅ Simple Notifications System loaded - replaced complex enhanced-ui-feedback.js');