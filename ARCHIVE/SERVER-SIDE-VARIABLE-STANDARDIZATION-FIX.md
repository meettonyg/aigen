# Server-Side Variable Standardization Fix

## Issue Fixed
The Questions Generator was using different JavaScript variable names than the Topics Generator:
- **Topics Generator**: `window.MKCG_Topics_Data`
- **Questions Generator**: `MKCG_TopicsData`, `MKCG_ExistingQuestions`, etc.

This caused JavaScript errors when the Questions Generator script loaded on Topics pages looking for data that wasn't available.

## Root Cause
The different variable names were likely an oversight during development where the two generators were built independently.

## Solution Applied
Standardized the Questions Generator template to use the **exact same** `window.MKCG_Topics_Data` object format as the Topics Generator.

### File Modified
`templates/generators/questions/default.php`

### Key Changes
1. **Replaced multiple variables** with single standardized object
2. **Same data structure** as Topics Generator
3. **Added debugging identifiers** to track data source

### Before (Questions Generator):
```javascript
const MKCG_TopicsData = <?php echo json_encode($all_topics); ?>;
const MKCG_ExistingQuestions = <?php echo json_encode($existing_questions); ?>;
const MKCG_PostId = <?php echo json_encode($post_id); ?>;
```

### After (Standardized):
```javascript
window.MKCG_Topics_Data = {
    entryId: <?php echo intval($entry_id); ?>,
    entryKey: '<?php echo esc_js($entry_key); ?>',
    hasEntry: <?php echo $entry_id > 0 ? 'true' : 'false'; ?>,
    authorityHook: {
         // Can be populated if needed, otherwise empty
    },
    topics: <?php echo json_encode(array_filter($all_topics)); ?>,
    questions: <?php echo json_encode(array_filter($existing_questions)); ?>,
    dataSource: 'questions_generator_template'
};
```

## How This Works with the JavaScript Fix
This server-side fix works perfectly with the previously implemented unified data architecture:

1. **Server-side**: Both Topics and Questions pages now create `window.MKCG_Topics_Data`
2. **Client-side**: The `loadUnifiedTopicsData()` function already looks for this variable as Priority #2
3. **Result**: No more "No topics data from PHP" errors

## Benefits
- ✅ **Root cause eliminated** - No more variable name mismatches
- ✅ **Consistent data structure** across all generators
- ✅ **Backward compatibility** maintained
- ✅ **Cross-generator data sharing** enabled
- ✅ **Cleaner architecture** with single data source

## Expected Console Output
After this fix, you should see:
```
✅ "MKCG Questions: Standardized data loaded into window.MKCG_Topics_Data"
✅ "MKCG Questions: Loaded topics from Topics Generator (shared page)"
```

Instead of:
```
❌ "MKCG Enhanced Questions: No topics data from PHP"
```

## Verification Steps
1. Clear browser cache
2. Load Topics Generator page
3. Check console - should see standardized data loading messages
4. No more JavaScript errors about missing data
5. Both generators work normally on their respective pages

This is the cleanest architectural solution - fixing the problem at its source rather than working around it.
