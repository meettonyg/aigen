# Generator Development Guide - Media Kit Content Generator

## 🎯 **Purpose**

This guide provides step-by-step instructions for creating new generators and extending existing ones using the unified BEM + design tokens architecture.

## 📋 **Quick Reference**

### Generator Creation Checklist
- [ ] Create PHP template using base classes
- [ ] Add generator-specific CSS classes
- [ ] Implement JavaScript functionality
- [ ] Test responsive behavior
- [ ] Validate accessibility
- [ ] Document component variations

## 🏗️ **Creating a New Generator**

### Step 1: Template Structure

Create your generator template following this pattern:

```php
<?php
/**
 * [Generator Name] Template - Unified BEM Architecture
 * Follows base .generator__* classes + .[generator-name]__* specific classes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Data loading logic (following established patterns)
$template_data = [];
// ... data loading code ...
?>

<div class="generator__container [generator-name]-generator" data-generator="[generator-name]">
    <div class="generator__header">
        <h1 class="generator__title">[Generator Title]</h1>
    </div>
    
    <div class="generator__content">
        <!-- LEFT PANEL -->
        <div class="generator__panel generator__panel--left">
            <!-- Generator-specific content -->
        </div>
        
        <!-- RIGHT PANEL -->
        <div class="generator__panel generator__panel--right">
            <!-- Guidance content -->
        </div>
    </div>
</div>
```

### Step 2: CSS Classes

Add generator-specific styles to `mkcg-unified-styles.css`:

```css
/* ============================================================================
   [GENERATOR NAME] SPECIFIC STYLES
   ============================================================================ */

/* Generator Container */
.[generator-name]-generator {
  font-family: var(--mkcg-font-family);
  color: var(--mkcg-text-primary);
  line-height: var(--mkcg-line-height-normal);
}

/* Generator Introduction */
.[generator-name]-generator__intro {
  margin-bottom: var(--mkcg-space-md);
  color: var(--mkcg-text-secondary);
  font-size: var(--mkcg-font-size-md);
  line-height: var(--mkcg-line-height-relaxed);
}

/* Unique Components */
.[generator-name]-generator__unique-component {
  background: var(--mkcg-bg-secondary);
  border: 1px solid var(--mkcg-border-light);
  border-radius: var(--mkcg-radius);
  padding: var(--mkcg-space-md);
  /* ... more styles ... */
}
```

### Step 3: JavaScript Integration

```javascript
// Generator-specific JavaScript
window.MKCG_[GeneratorName]_Data = {
    postId: <?php echo intval($post_id); ?>,
    hasData: <?php echo $has_data ? 'true' : 'false'; ?>,
    // ... generator-specific data ...
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof [GeneratorName]Generator !== 'undefined') {
        [GeneratorName]Generator.init();
    }
});
```

## 🎨 **Styling Guidelines**

### 1. **Always Use Base Classes First**

```html
<!-- ✅ CORRECT: Base + Specific -->
<div class="generator__container my-generator">
<button class="generator__button generator__button--primary my-generator__special-button">

<!-- ❌ WRONG: Only specific classes -->
<div class="my-generator-container">
<button class="my-generator-button-primary">
```

### 2. **Follow Design Token System**

```css
/* ✅ CORRECT: Use design tokens */
.my-generator__component {
  padding: var(--mkcg-space-md);
  color: var(--mkcg-text-primary);
  border-radius: var(--mkcg-radius);
}

/* ❌ WRONG: Hardcoded values */
.my-generator__component {
  padding: 20px;
  color: #2c3e50;
  border-radius: 8px;
}
```

### 3. **Maintain Inheritance Hierarchy**

```css
/* Base provides foundation */
.generator__button {
  /* Common button styles */
}

/* Specific extends base */
.my-generator__button--special {
  /* Only my-generator specific overrides */
  background: var(--mkcg-secondary);
}
```

## 📱 **Responsive Implementation**

### Mobile-First Approach

```css
/* Base styles (mobile-first) */
.my-generator__component {
  display: block;
  width: 100%;
}

/* Tablet and up */
@media screen and (min-width: 768px) {
  .my-generator__component {
    display: flex;
    width: auto;
  }
}

/* Desktop and up */
@media screen and (min-width: 1024px) {
  .my-generator__component {
    max-width: 1200px;
  }
}
```

### Use Established Breakpoints

```css
/* Follow existing breakpoint system */
@media screen and (max-width: 1024px) { /* Tablet */ }
@media screen and (max-width: 768px)  { /* Mobile */ }
@media screen and (max-width: 480px)  { /* Small mobile */ }
```

## 🔧 **Component Patterns**

### 1. **Form Fields**

```html
<div class="generator__field">
    <label for="field-id" class="generator__field-label">Field Label</label>
    <input type="text" 
           id="field-id" 
           class="generator__field-input"
           placeholder="Enter value">
    <p class="generator__field-helper">Helper text here</p>
</div>
```

### 2. **Buttons**

```html
<!-- Primary action -->
<button class="generator__button generator__button--primary">
    Primary Action
</button>

<!-- Secondary action -->
<button class="generator__button generator__button--secondary">
    Secondary Action
</button>

<!-- Outline style -->
<button class="generator__button generator__button--outline">
    Outline Button
</button>
```

### 3. **Loading States**

```html
<div class="generator__loading generator__loading--hidden" id="my-loading">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
    </svg>
    Loading message...
</div>
```

### 4. **Results Display**

```html
<div class="generator__results generator__results--hidden" id="my-results">
    <!-- Results content -->
</div>
```

## 🎯 **Authority Hook Integration**

### Using Centralized Service

```php
<?php 
// Initialize the service if not already available
if (!isset($GLOBALS['authority_hook_service'])) {
    $GLOBALS['authority_hook_service'] = new MKCG_Authority_Hook_Service();
}
$authority_hook_service = $GLOBALS['authority_hook_service'];

// Prepare current values
$current_values = [
    'who' => $authority_hook_components['who'] ?? '',
    'what' => $authority_hook_components['what'] ?? '', 
    'when' => $authority_hook_components['when'] ?? '',
    'how' => $authority_hook_components['how'] ?? ''
];

// Render options
$render_options = [
    'show_preview' => false,
    'show_examples' => true,
    'show_audience_manager' => true,
    'css_classes' => 'authority-hook',
    'field_prefix' => 'mkcg-',
    'tabs_enabled' => true
];

// Render the Authority Hook Builder
echo $authority_hook_service->render_authority_hook_builder('my-generator', $current_values, $render_options);
?>
```

### JavaScript Integration

```javascript
// Listen for authority hook updates
document.addEventListener('authority-hook-updated', function(e) {
    const displayElement = document.getElementById('my-generator-authority-hook-text');
    if (displayElement && e.detail.completeHook) {
        displayElement.textContent = e.detail.completeHook;
    }
});
```

## 🧪 **Testing Guidelines**

### 1. **Visual Consistency Check**

```javascript
// Test that base classes are applied correctly
function testBaseClassesApplied() {
    const container = document.querySelector('.generator__container');
    const buttons = document.querySelectorAll('.generator__button');
    const panels = document.querySelectorAll('.generator__panel');
    
    console.assert(container, 'Generator container should exist');
    console.assert(buttons.length > 0, 'Should have generator buttons');
    console.assert(panels.length >= 2, 'Should have left and right panels');
}
```

### 2. **Responsive Behavior**

```javascript
// Test responsive layout changes
function testResponsiveBehavior() {
    const content = document.querySelector('.generator__content');
    
    // Test mobile layout
    window.resizeTo(375, 667);
    const isMobileLayout = window.getComputedStyle(content).flexDirection === 'column';
    console.assert(isMobileLayout, 'Should stack vertically on mobile');
    
    // Test desktop layout
    window.resizeTo(1200, 800);
    const isDesktopLayout = window.getComputedStyle(content).flexDirection === 'row';
    console.assert(isDesktopLayout, 'Should be horizontal on desktop');
}
```

### 3. **Accessibility Validation**

```javascript
// Test accessibility features
function testAccessibility() {
    const buttons = document.querySelectorAll('.generator__button');
    const inputs = document.querySelectorAll('.generator__field-input');
    const labels = document.querySelectorAll('.generator__field-label');
    
    // Test that buttons have accessible text
    buttons.forEach(button => {
        const hasText = button.textContent.trim().length > 0 || 
                       button.getAttribute('aria-label');
        console.assert(hasText, 'Button should have accessible text');
    });
    
    // Test that inputs have associated labels
    inputs.forEach(input => {
        const hasLabel = input.id && document.querySelector(`label[for="${input.id}"]`);
        console.assert(hasLabel, 'Input should have associated label');
    });
}
```

## 🚀 **Performance Optimization**

### 1. **CSS Optimization**

```css
/* Use transform for animations (hardware accelerated) */
.my-generator__item {
    transition: transform var(--mkcg-transition-fast);
}

.my-generator__item:hover {
    transform: translateY(-2px); /* Better than changing top/margin */
}

/* Avoid expensive properties in transitions */
.my-generator__component {
    /* ✅ GOOD: transform, opacity */
    transition: transform var(--mkcg-transition-fast), 
                opacity var(--mkcg-transition-fast);
    
    /* ❌ AVOID: width, height, box-shadow changes */
}
```

### 2. **JavaScript Performance**

```javascript
// Use event delegation for dynamic content
document.addEventListener('click', function(e) {
    if (e.target.matches('.my-generator__dynamic-button')) {
        handleDynamicClick(e);
    }
});

// Debounce input handlers
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

const debouncedInputHandler = debounce(handleInput, 300);
```

## 🔍 **Debugging Tips**

### 1. **CSS Debugging**

```css
/* Temporary debug styles */
.debug * {
    outline: 1px solid red !important;
}

.debug .generator__container {
    background: rgba(255, 0, 0, 0.1) !important;
}

.debug .generator__panel {
    background: rgba(0, 255, 0, 0.1) !important;
}
```

### 2. **JavaScript Debugging**

```javascript
// Add debugging information
console.log('🎯 My Generator: Template data loaded', {
    postId: window.MKCG_MyGenerator_Data.postId,
    hasData: window.MKCG_MyGenerator_Data.hasData
});

// Test data structure
function validateDataStructure(data) {
    const required = ['postId', 'hasData'];
    const missing = required.filter(key => !(key in data));
    
    if (missing.length > 0) {
        console.error('❌ Missing required data:', missing);
        return false;
    }
    
    console.log('✅ Data structure validation passed');
    return true;
}
```

## 📝 **Documentation Requirements**

### 1. **Code Comments**

```css
/* ============================================================================
   MY GENERATOR SPECIFIC STYLES
   ============================================================================ */

/* Component Description */
.my-generator__special-component {
    /* Purpose: Displays special content with enhanced styling
     * Used by: my-generator/default.php
     * Dependencies: Base generator classes
     */
    background: var(--mkcg-bg-secondary);
    /* ... */
}
```

### 2. **README Updates**

When adding a new generator, update the main README.txt:

```
== Shortcodes ==

* `[mkcg_biography]` - Biography generator
* `[mkcg_offers]` - Offers generator  
* `[mkcg_topics]` - Topics generator
* `[mkcg_questions]` - Questions generator
* `[mkcg_my_generator]` - My new generator

== Changelog ==

= 1.1.0 =
* Added My Generator
* Enhanced responsive design
* Updated CSS architecture
```

## ⚠️ **Common Pitfalls to Avoid**

### 1. **CSS Specificity Issues**

```css
/* ❌ WRONG: Too specific */
.generator__container .my-generator .my-generator__component .my-generator__item {
    color: red;
}

/* ✅ CORRECT: Appropriate specificity */
.my-generator__item {
    color: red;
}
```

### 2. **Breaking Inheritance**

```css
/* ❌ WRONG: Redefining everything */
.my-generator__button {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    /* ... duplicating base styles ... */
}

/* ✅ CORRECT: Extending base */
.my-generator__button--special {
    background: var(--mkcg-warning); /* Only the difference */
}
```

### 3. **Hardcoded Values**

```css
/* ❌ WRONG: Hardcoded */
.my-generator__component {
    margin: 20px;
    color: #2c3e50;
}

/* ✅ CORRECT: Using tokens */
.my-generator__component {
    margin: var(--mkcg-space-md);
    color: var(--mkcg-text-primary);
}
```

### 4. **Mobile-Last Thinking**

```css
/* ❌ WRONG: Desktop-first */
.my-generator__component {
    display: flex; /* Breaks on mobile */
}

@media screen and (max-width: 768px) {
    .my-generator__component {
        display: block; /* Fixing mobile */
    }
}

/* ✅ CORRECT: Mobile-first */
.my-generator__component {
    display: block; /* Works on mobile */
}

@media screen and (min-width: 768px) {
    .my-generator__component {
        display: flex; /* Enhancement for larger screens */
    }
}
```

## 🏆 **Best Practices Summary**

1. **Always use base classes first, then extend**
2. **Use design tokens instead of hardcoded values**
3. **Follow mobile-first responsive design**
4. **Maintain semantic BEM naming**
5. **Test across all device sizes**
6. **Validate accessibility compliance**
7. **Document component purpose and usage**
8. **Use established JavaScript patterns**
9. **Leverage centralized services (Authority Hook)**
10. **Optimize for performance and maintainability**

## 📚 **Additional Resources**

- [CSS-ARCHITECTURE.md](./CSS-ARCHITECTURE.md) - Detailed architecture documentation
- [BEM Methodology](http://getbem.com/) - Official BEM documentation
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*) - MDN Documentation
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) - WordPress best practices