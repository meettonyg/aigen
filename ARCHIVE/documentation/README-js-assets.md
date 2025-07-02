# Media Kit Content Generator - JavaScript Files

## File Structure

```
assets/js/
├── authority-hook-builder.js     # Main Authority Hook Builder (vanilla JS)
├── mkcg-form-utils.js            # Enhanced FormUtils (jQuery-based)
└── generators/
    └── topics-generator.js       # Topics Generator integration
```

## Core Components

### 1. Authority Hook Builder (`authority-hook-builder.js`)
- **Purpose**: Handles WHO-WHAT-WHEN-HOW tab system and live Authority Hook generation
- **Dependencies**: None (vanilla JavaScript)
- **Features**:
  - Interactive tabs for Authority Hook building
  - Example tag clicking with "+ Add" functionality  
  - Live Authority Hook preview updates
  - Copy to clipboard functionality
  - Target Audience section hiding
  - Integration with Topics Generator

### 2. Topics Generator (`generators/topics-generator.js`)
- **Purpose**: Handles topics generation integration
- **Dependencies**: Authority Hook Builder
- **Features**:
  - Topics generation using Authority Hook
  - Loading state management
  - Integration with WordPress AJAX

### 3. Enhanced FormUtils (`mkcg-form-utils.js`)
- **Purpose**: Enhanced form utilities with BEM support
- **Dependencies**: jQuery
- **Features**:
  - Form field management
  - AJAX request handling
  - UI utilities

## Usage

The scripts are automatically enqueued by the main plugin when:
- Page contains generator shortcodes
- Page is a Formidable edit page (`frm_action=edit&entry=`)

## Global Objects

- `window.authorityHookBuilder` - Main Authority Hook Builder instance
- `window.topicsGenerator` - Topics Generator instance  
- `window.MKCG_FormUtils` - Enhanced FormUtils
- `window.topicsGeneratorUtils` - Topics-specific utilities

## Features

### Target Audience Section Hiding
The Authority Hook Builder automatically hides the Target Audience section using:
- Multiple detection methods (title text, field ID, placeholder text, labels)
- Retry logic for dynamically loaded content
- MutationObserver for runtime content changes
- CSS rules for additional hiding support

### Authority Hook Building
1. User fills WHO, RESULT, WHEN, HOW tabs
2. Live preview updates as they type
3. Example tags can be clicked to populate fields
4. Final Authority Hook is generated and available for topics

### Topics Generation
1. Authority Hook is validated
2. AJAX request sent to WordPress backend
3. OpenAI generates 5 topics based on Authority Hook
4. Topics displayed with "Use" buttons
5. Topics can be saved for use in forms

## Integration Notes

- All scripts use vanilla JavaScript or clearly defined jQuery dependencies
- BEM methodology used for CSS classes
- WordPress nonces for security
- Error handling and fallbacks included
- Mobile responsive design considerations
