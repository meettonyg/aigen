# Topics Generator - Form 515 Integration Testing Guide

## ✅ **What Was Fixed**

Your Topics Generator now properly integrates with Formidable Forms using the correct field IDs from form 515. The issue where it wasn't loading data from URL entry parameters has been resolved.

## 🧪 **How to Test**

### **1. Test URL Loading**
- Navigate to: `https://guestify.ai/topics/?frm_action=edit&entry=y8ver`
- Replace `y8ver` with an actual entry key from your form
- The page should load existing data from that entry

### **2. Test Authority Hook Components**
- Fill in the WHO/WHAT/WHEN/HOW fields in the builder
- Values should auto-save as you type (watch browser console for save confirmations)
- The complete authority hook should update in real-time

### **3. Test Topic Generation**
- Click "Generate Topics with AI"
- Topics should appear with "Use" buttons
- Click "Use" next to any topic
- Select which field number (1-5) to save it to
- Topic should save to the correct Formidable field

### **4. Check Formidable Form**
- Go to your Formidable Forms admin
- View the entry that was being edited
- Verify that:
  - Authority hook components are saved to fields 10296, 10297, 10387, 10298
  - Complete authority hook is saved to field 10358
  - Topics are saved to fields 8498-8502

## 🔧 **Field Mappings (Form 515)**

| Component | Field ID | Description |
|-----------|----------|-------------|
| WHO | 10296 | WHO do you help? |
| RESULT | 10297 | WHAT result do you help them achieve? |
| WHEN | 10387 | WHEN do they need you? |
| HOW | 10298 | HOW do you help them? |
| Complete Hook | 10358 | Your Authority Hook |
| Topic 1 | 8498 | First Interview Topic |
| Topic 2 | 8499 | Second Interview Topic |
| Topic 3 | 8500 | Third Interview Topic |
| Topic 4 | 8501 | Fourth Interview Topic |
| Topic 5 | 8502 | Fifth Interview Topic |

## 🐛 **Troubleshooting**

### **If data doesn't load:**
- Check browser console for errors
- Verify the entry key exists in Formidable
- Ensure user has permission to edit the entry

### **If auto-save doesn't work:**
- Check browser console for AJAX errors
- Verify nonces are being passed correctly
- Check WordPress error logs

### **If topics don't save:**
- Verify entry ID is being detected from URL
- Check that field mappings match your Formidable form
- Ensure AJAX handlers are properly initialized

## 📝 **Next Steps**

The Topics Generator is now fully integrated with Form 515. You can:
1. Test with real entry URLs
2. Verify auto-save functionality works
3. Confirm topics save to the correct fields
4. Apply similar patterns to other generators (Biography, Offers, Questions)

All changes have been saved to your local files. The integration should now work seamlessly with your existing Formidable form workflow.
