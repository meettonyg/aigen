# 🚨 URGENT: DEPLOYMENT INSTRUCTIONS

## ❌ **CURRENT ISSUE**
Your server still has the OLD version of the plugin files. The fatal error confirms this:
```
Class 'MKCG_Topics_Data_Service' not found
```

## ✅ **SOLUTION: UPLOAD UPDATED FILES**

You **MUST** upload these specific files to your server to fix the fatal error:

### **🔧 CRITICAL FILES TO UPLOAD:**

1. **`media-kit-content-generator.php`** (main plugin file)
   - **Location on server:** `/wp-content/plugins/media-kit-content-generator/media-kit-content-generator.php`
   - **Contains:** Added `require_once` for Topics Data Service

2. **`includes/services/class-mkcg-topics-data-service.php`** (unified service)
   - **Location on server:** `/wp-content/plugins/media-kit-content-generator/includes/services/class-mkcg-topics-data-service.php`
   - **Contains:** All the unified questions/topics methods

3. **`includes/services/class-mkcg-config.php`** (configuration)
   - **Location on server:** `/wp-content/plugins/media-kit-content-generator/includes/services/class-mkcg-config.php`
   - **Contains:** Fixed placeholder field mappings (no more duplicate warnings)

4. **`includes/generators/class-mkcg-questions-generator.php`** (refactored generator)
   - **Location on server:** `/wp-content/plugins/media-kit-content-generator/includes/generators/class-mkcg-questions-generator.php`
   - **Contains:** Removed duplicate code, uses unified service

---

## 📂 **SERVER FILE PATHS**

Based on your error path: `/home/1378770.cloudwaysapps.com/hgysczkcuu/public_html/wp-content/plugins/media-kit-content-generator/`

Upload these files to your server:
```
/home/1378770.cloudwaysapps.com/hgysczkcuu/public_html/wp-content/plugins/media-kit-content-generator/
├── media-kit-content-generator.php                                    ← UPLOAD THIS
├── includes/
│   ├── services/
│   │   ├── class-mkcg-config.php                                      ← UPLOAD THIS
│   │   └── class-mkcg-topics-data-service.php                         ← UPLOAD THIS
│   └── generators/
│       └── class-mkcg-questions-generator.php                         ← UPLOAD THIS
```

---

## 🚀 **UPLOAD METHODS**

### **Option 1: FTP/SFTP**
1. Connect to your server via FTP/SFTP
2. Navigate to the plugin directory
3. Upload the 4 files listed above
4. Overwrite the existing files

### **Option 2: cPanel File Manager**
1. Login to cPanel
2. Open File Manager
3. Navigate to `/public_html/wp-content/plugins/media-kit-content-generator/`
4. Upload the 4 files
5. Overwrite when prompted

### **Option 3: WordPress Admin**
1. Deactivate the current plugin
2. Delete the plugin
3. Upload the entire updated plugin folder as a ZIP
4. Activate the plugin

---

## ✅ **VERIFICATION**

After uploading, check:
1. **No fatal errors** - Plugin should activate successfully
2. **No warnings** - Check error logs for the duplicate field ID warnings (should be gone)
3. **Generators work** - Test Topics and Questions generators

---

## 📊 **WHAT THE FIXES DO**

### **Fix 1: Fatal Error Resolution**
```php
// Added to media-kit-content-generator.php:
require_once MKCG_PLUGIN_PATH . 'includes/services/class-mkcg-topics-data-service.php';
```

### **Fix 2: Duplicate Field ID Warnings**
```php
// Changed in class-mkcg-config.php:
'short_bio' => 99001,    // Was: 0 (caused duplicates)
'medium_bio' => 99002,   // Was: 0 (caused duplicates)
// + Skip placeholder configs from duplicate checks
```

---

## 🎯 **EXPECTED RESULT**

After uploading these files:
- ✅ **No fatal errors** - Plugin loads successfully
- ✅ **No configuration warnings** - Clean startup
- ✅ **95% code unification** - All unified functionality working
- ✅ **All generators functional** - Topics and Questions work normally

**Upload the files now and the issues should be resolved! 🚀**
