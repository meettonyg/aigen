# Impact Intro Debug Guide
## Biography Generator Issue Resolution

### Problem Description
The Impact Intro is not displaying in the Biography Generator even when the WHERE and WHY fields are completed.

### Quick Fix Commands
Run these commands in the browser console:

```javascript
// 1. Check current Impact Intro state
window.MKCG_Biography_Test.debugImpactIntro();

// 2. Manually trigger Impact Intro update
BiographyGenerator.updateImpactIntro();

// 3. Check if values are captured
console.log('WHERE:', BiographyGenerator.fields.where);
console.log('WHY:', BiographyGenerator.fields.why);

// 4. Force update from Impact Intro Builder
const whereField = document.getElementById('mkcg-where');
const whyField = document.getElementById('mkcg-why');
if (whereField && whyField) {
    BiographyGenerator.fields.where = whereField.value;
    BiographyGenerator.fields.why = whyField.value;
    BiographyGenerator.updateImpactIntro();
}
```

### Root Cause
The issue occurs because:
1. The Impact Intro Builder updates fields in its own scope
2. The Biography Generator doesn't always capture these updates
3. The display format needs "I've" prefix for WHERE and "My mission is to" prefix for WHY

### Permanent Fix Applied
1. Added event listeners for 'impact-intro-updated' events
2. Fixed the Impact Intro format in updateImpactIntro() method
3. Fixed the Impact Intro format in collectFormData() method
4. Added debug function to help diagnose issues

### Testing Steps
1. Open Biography Generator
2. Click "Edit Impact Intro"
3. Add credentials and mission
4. Check if Impact Intro displays correctly
5. If not, run the debug commands above
