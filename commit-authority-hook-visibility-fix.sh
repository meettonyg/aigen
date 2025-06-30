#!/bin/bash

# Authority Hook Builder Visibility Fix - Topics Generator
# Fixes the issue where enhanced Authority Hook component was hidden and non-functional

echo "🔧 CRITICAL FIX: Authority Hook Builder Visibility in Topics Generator"
echo "=================================================================="

cd "media-kit-content-generator"

git add -A

git commit -m "🔧 CRITICAL FIX: Authority Hook Builder Visibility in Topics Generator

ROOT ISSUE RESOLVED: Enhanced Authority Hook component was included but hidden with no toggle functionality

FIXES IMPLEMENTED:
✅ Added working toggle functionality for 'Edit Components' button
✅ Created toggleAuthorityHookBuilder() JavaScript function
✅ Enhanced CSS with smooth animations and proper hiding
✅ Auto-show builder for users with default authority hooks
✅ Added comprehensive debug logging and error handling
✅ Fixed data passing to shared component (generator_type, entry_id)

ENHANCED FEATURES NOW ACCESSIBLE:
✅ Multiple audience management with checkboxes
✅ 'Add to List' functionality on examples
✅ Audience tag system with visual indicators
✅ Status counters for audience selection
✅ Read-only WHO field populated from tags
✅ Smart comma formatting for multiple audiences

FILES MODIFIED:
- templates/generators/topics/default.php (toggle functionality + debug)
- assets/css/mkcg-unified-styles.css (enhanced visibility styles)

TESTING:
1. Clear all caches
2. Go to Topics Generator page
3. Click 'Edit Components' button
4. Verify enhanced Authority Hook Builder appears with full functionality

STATUS: Ready for production deployment"

echo "✅ Committed Authority Hook Builder visibility fix"
echo "🚀 Enhanced audience management now accessible in Topics Generator"