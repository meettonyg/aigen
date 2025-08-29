# Biography Generator Indentation Fix

## Issue
The generated biographies were displaying with indentation in the results page, making them appear incorrectly formatted.

## Root Cause
The issue was in the PHP backend (`enhanced_biography_generator.php`) where the biographies were being extracted from the OpenAI response. The response parsing was only using `trim()` which removes leading and trailing whitespace from the entire string, but not the indentation at the beginning of each line within the biography.

## Solution
Updated the biography parsing in two functions:
1. `generate_biography()` - The main generation function
2. `modify_biography_tone()` - The tone modification function

### Code Fix
Instead of just using `trim()`:
```php
$biographies['short'] = trim($short_matches[1]);
```

We now remove indentation from each line:
```php
$biographies['short'] = trim(preg_replace('/^\s+/m', '', trim($short_matches[1])));
```

This regex pattern `/^\s+/m` removes all whitespace at the beginning of each line (the `m` flag makes `^` match the start of each line, not just the start of the string).

## Files Modified
1. `/includes/generators/enhanced_biography_generator.php` - Fixed biography parsing in two functions
2. `/assets/css/mkcg-unified-styles.css` - Added biography-specific CSS with proper text formatting

## CSS Enhancement
Also added comprehensive biography-specific CSS to ensure proper text display:
- `text-indent: 0 !important;` - Ensures no text indentation
- `text-align: left !important;` - Ensures left alignment
- `white-space: pre-wrap;` - Preserves line breaks while wrapping text
- `word-wrap: break-word;` - Handles long words properly

## Result
Biographies now display properly without any unwanted indentation, maintaining clean, professional formatting in the results page.
