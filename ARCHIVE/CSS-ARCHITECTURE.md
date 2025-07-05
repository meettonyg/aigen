# CSS Architecture Guide - Media Kit Content Generator

## 🎯 **Overview**

The Media Kit Content Generator uses a unified CSS architecture based on **BEM methodology** combined with **design tokens** to create a scalable, maintainable, and semantically correct styling system.

## 🏗️ **Architecture Principles**

### 1. **BEM (Block Element Modifier) Methodology**
- **Block**: Independent component (`.generator`, `.topics-generator`)
- **Element**: Part of a block (`.generator__button`, `.generator__panel`)
- **Modifier**: Variation or state (`.generator__button--primary`, `.generator__panel--hidden`)

### 2. **Design Tokens System**
All colors, spacing, typography, and design elements are defined as CSS custom properties in `:root`:

```css
:root {
  /* Brand Colors */
  --mkcg-primary: #1a9bdc;
  --mkcg-secondary: #f87f34;
  
  /* Spacing System (8px base unit) */
  --mkcg-space-xs: 8px;
  --mkcg-space-sm: 12px;
  --mkcg-space-md: 20px;
  --mkcg-space-lg: 30px;
  --mkcg-space-xl: 40px;
  
  /* Typography */
  --mkcg-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, ...;
  --mkcg-font-size-sm: 14px;
  --mkcg-font-size-md: 16px;
  --mkcg-font-size-lg: 18px;
}
```

### 3. **Inheritance Hierarchy**

```
Base Generator Classes (.generator__*)
    ↓
Generator-Specific Classes (.topics-generator__*, .offers-generator__*, etc.)
    ↓
State Modifiers (.generator__button--disabled, .topics-generator__topic-item--active)
```

## 📁 **File Structure**

### Main CSS File
- `assets/css/mkcg-unified-styles.css` - Single source of truth for all styling

### Organized Sections
1. **Root Variables & Design Tokens**
2. **Base Generator Classes** 
3. **State Modifiers**
4. **Authority Hook Component**
5. **Form Components**
6. **UI Components**
7. **Utility Classes**
8. **Responsive Design**
9. **Generator-Specific Styles** (Topics, Offers, Questions, Biography)

## 🎨 **Design Token Categories**

### Colors
```css
/* Brand Colors */
--mkcg-primary: #1a9bdc;           /* Primary blue */
--mkcg-secondary: #f87f34;         /* Orange accent */

/* Status Colors */
--mkcg-success: #34c759;           /* Green */
--mkcg-warning: #ff9500;           /* Orange */
--mkcg-error: #ff3b30;             /* Red */

/* Text Colors */
--mkcg-text-primary: #2c3e50;      /* Dark gray */
--mkcg-text-secondary: #5a6d7e;    /* Medium gray */
--mkcg-text-tertiary: #8a9ba8;     /* Light gray */

/* Background Colors */
--mkcg-bg-primary: #ffffff;        /* White */
--mkcg-bg-secondary: #f9fafb;      /* Very light gray */
--mkcg-bg-tertiary: #f5f7fa;       /* Light gray */
```

### Spacing System
Based on 8px grid system for consistency:
```css
--mkcg-space-xs: 8px;    /* Tight spacing */
--mkcg-space-sm: 12px;   /* Small spacing */
--mkcg-space-md: 20px;   /* Default spacing */
--mkcg-space-lg: 30px;   /* Large spacing */
--mkcg-space-xl: 40px;   /* Extra large spacing */
--mkcg-space-xxl: 60px;  /* Section spacing */
```

### Typography Scale
```css
--mkcg-font-size-xs: 12px;   /* Small text */
--mkcg-font-size-sm: 14px;   /* Secondary text */
--mkcg-font-size-md: 16px;   /* Body text */
--mkcg-font-size-lg: 18px;   /* Subheadings */
--mkcg-font-size-xl: 24px;   /* Headings */
--mkcg-font-size-xxl: 32px;  /* Page titles */
```

## 🏗️ **Base Generator Classes**

### Container Structure
```css
.generator__container          /* Main wrapper with max-width and centering */
.generator__header            /* Page title section */
.generator__content           /* Main flex layout */
.generator__panel             /* Left/right columns */
.generator__panel--left       /* Left column */
.generator__panel--right      /* Right column with background */
```

### Common Components
```css
.generator__button            /* Base button styles */
.generator__button--primary   /* Primary action button */
.generator__button--secondary /* Secondary action button */
.generator__button--outline   /* Outline style button */

.generator__authority-hook    /* Authority hook component */
.generator__loading          /* Loading indicator */
.generator__results          /* Results container */
.generator__modal           /* Modal dialogs */
```

### Form Elements
```css
.generator__field            /* Form field wrapper */
.generator__field-label      /* Field labels */
.generator__field-input      /* Input fields */
.generator__field-helper     /* Helper text */
```

## 🎯 **Generator-Specific Classes**

### Topics Generator
```css
.topics-generator                    /* Root container */
.topics-generator__intro            /* Introduction text */
.topics-generator__topics-container /* Topics display wrapper */
.topics-generator__topic-item       /* Individual topic */
.topics-generator__topic-number     /* Topic number circle */
.topics-generator__topic-input      /* Editable topic field */
.topics-generator__save-section     /* Save controls */
```

### Offers Generator
```css
.offers-generator                      /* Root container */
.offers-generator__business-container  /* Business info form */
.offers-generator__offer-item         /* Individual offer */
.offers-generator__offer-title        /* Offer title */
.offers-generator__offer-price        /* Price display */
.offers-generator__button-group       /* Action buttons */
```

### Questions Generator
```css
.questions-generator                   /* Root container */
.questions-generator__topic-selector  /* Topic selection area */
.questions-generator__topic-card      /* Topic cards */
.questions-generator__topic-questions /* Question form section */
.questions-generator__question-field  /* Question input */
```

## 🔧 **State Management**

### Visibility States
```css
.generator__loading--hidden    /* Hide loading indicator */
.generator__results--hidden    /* Hide results section */
.generator__panel--hidden      /* Hide panels */
.generator__builder--hidden    /* Hide authority hook builder */
```

### Button States
```css
.generator__button--disabled   /* Disabled button */
.generator__button--loading    /* Loading button with spinner */
.generator__button:focus       /* Focus outline */
.generator__button:active      /* Active press state */
```

### Component States
```css
.topics-generator__topic-item--active        /* Active topic */
.questions-generator__topic-card--empty      /* Empty topic card */
.offers-generator__offer-item:hover          /* Hover effects */
```

## 📱 **Responsive Design**

### Breakpoints
```css
@media screen and (max-width: 1024px) { /* Tablet */ }
@media screen and (max-width: 768px)  { /* Mobile */ }
@media screen and (max-width: 480px)  { /* Small mobile */ }
```

### Responsive Patterns
- **Flex to Stack**: `.generator__content` changes from row to column
- **Full Width**: `.generator__panel` becomes 100% width on mobile
- **Typography Scale**: Font sizes reduce on smaller screens
- **Touch Targets**: Buttons become full-width on mobile

## 🎨 **Visual Consistency**

### Shadows
```css
--mkcg-shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
--mkcg-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
--mkcg-shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1);
--mkcg-shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.15);
```

### Border Radius
```css
--mkcg-radius: 8px;        /* Default */
--mkcg-radius-sm: 4px;     /* Small elements */
--mkcg-radius-lg: 12px;    /* Large elements */
--mkcg-radius-full: 50%;   /* Circles */
```

### Transitions
```css
--mkcg-transition-fast: 0.15s ease;
--mkcg-transition-normal: 0.25s ease;
--mkcg-transition-slow: 0.35s ease;
```

## 🔄 **Migration from Legacy Classes**

### Legacy Compatibility
The architecture maintains backward compatibility with existing class names:

```css
/* Legacy classes still work */
.mkcg-container          → .generator__container
.mkcg-questions-generator-wrapper → .questions-generator
.topics-generator__*     → Now inherits from .generator__* base
```

## ✅ **Benefits of This Architecture**

### 1. **Maintainability**
- Single source of truth for all styling
- Easy to make global changes via CSS variables
- Clear inheritance hierarchy reduces duplication

### 2. **Scalability**
- New generators follow established patterns
- Consistent component library
- Easy to extend with new features

### 3. **Performance**
- Reduced CSS file size through inheritance
- Efficient cascade utilization
- Minimal style recalculation

### 4. **Developer Experience**
- Clear naming conventions
- Predictable class structure
- Easy debugging and inspection

### 5. **Professional Quality**
- Industry-standard BEM methodology
- Comprehensive design system
- Accessible and semantic markup

## 🚀 **Future Considerations**

### CSS Custom Properties Support
- Automatic dark mode switching
- Themeable color schemes
- Dynamic spacing adjustments

### Component Isolation
- CSS-in-JS migration path available
- Shadow DOM compatibility
- Framework-agnostic architecture

### Performance Optimization
- Critical CSS extraction
- Progressive enhancement
- Modern CSS features adoption