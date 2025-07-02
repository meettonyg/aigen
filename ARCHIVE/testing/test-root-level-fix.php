<?php
/**
 * MEDIA KIT CONTENT GENERATOR - ROOT LEVEL FIX VERIFICATION
 * 
 * This script tests that the duplicate file removal fixed the class loading issues
 * Place in: media-kit-content-generator/test-root-level-fix.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Allow access for testing purposes
    define('ABSPATH', true);
}

echo "<h1>🎯 Media Kit Content Generator - Root Level Fix Verification</h1>\n";
echo "<h2>Testing Class Loading After Duplicate File Removal</h2>\n\n";

// Define plugin constants
if (!defined('MKCG_PLUGIN_PATH')) {
    define('MKCG_PLUGIN_PATH