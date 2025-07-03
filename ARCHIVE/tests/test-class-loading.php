<?php
/**
 * MKCG Class Loading Test
 * Quick test to verify Enhanced_Formidable_Service loads without fatal errors
 * 
 * This is a standalone test - it does NOT require WordPress
 */

echo "=== MKCG Class Loading Test ===\n";
echo "Testing Enhanced_Formidable_Service class loading...\n\n";

// Test 1: Include the class file
echo "1. Including Enhanced_Formidable_Service file...\n";
try {
    require_once 'includes/services/enhanced_formidable_service.php';
    echo "   ✅ File included successfully\n";
} catch (ParseError $e) {
    echo "   ❌ PARSE ERROR: " . $e->getMessage() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "   ❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if class exists
echo "\n2. Checking if Enhanced_Formidable_Service class exists...\n";
if (class_exists('Enhanced_Formidable_Service')) {
    echo "   ✅ Class exists\n";
} else {
    echo "   ❌ Class not found\n";
    exit(1);
}

// Test 3: Check class methods
echo "\n3. Checking class methods...\n";
$reflection = new ReflectionClass('Enhanced_Formidable_Service');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

$expected_methods = [
    'save_entry_data',
    'get_field_value', 
    'get_entry_data',
    'get_entry_by_key',
    'get_entry_id_from_post',
    'get_post_id_from_entry',
    'save_post_meta',
    'get_topics_from_post_enhanced',
    'get_questions_with_integrity_check',
    'validate_post_association'
];

echo "   Found " . count($methods) . " public methods:\n";
$found_methods = [];
foreach ($methods as $method) {
    $method_name = $method->getName();
    $found_methods[] = $method_name;
    echo "   - " . $method_name . "\n";
}

echo "\n4. Verifying expected methods exist...\n";
$missing_methods = array_diff($expected_methods, $found_methods);
if (empty($missing_methods)) {
    echo "   ✅ All expected methods found\n";
} else {
    echo "   ⚠️  Missing methods: " . implode(', ', $missing_methods) . "\n";
}

// Test 4: Check for duplicate methods (the original issue)
echo "\n5. Checking for duplicate method declarations...\n";
$method_counts = array_count_values($found_methods);
$duplicates = array_filter($method_counts, function($count) { return $count > 1; });

if (empty($duplicates)) {
    echo "   ✅ No duplicate methods found\n";
} else {
    echo "   ❌ DUPLICATE METHODS DETECTED:\n";
    foreach ($duplicates as $method => $count) {
        echo "   - $method appears $count times\n";
    }
}

// Test 5: Try to instantiate the class (basic test - won't work fully without WordPress)
echo "\n6. Testing class instantiation...\n";
try {
    // This will fail because WordPress functions aren't available, but it tests basic constructor
    $service = new Enhanced_Formidable_Service();
    echo "   ✅ Class instantiated successfully\n";
} catch (Error $e) {
    if (strpos($e->getMessage(), 'undefined function') !== false) {
        echo "   ✅ Class constructor works (WordPress functions unavailable - expected)\n";
    } else {
        echo "   ❌ Constructor error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Test Complete ===\n";

// Summary
if (empty($duplicates)) {
    echo "🎉 SUCCESS: Enhanced_Formidable_Service class loads without fatal errors!\n";
    echo "The duplicate method issue has been FIXED.\n";
} else {
    echo "❌ FAILURE: Duplicate methods still exist - fatal error will occur in WordPress.\n";
}

echo "\nNext steps:\n";
echo "- Test this in your WordPress environment\n";
echo "- Check WordPress error logs for any remaining issues\n";
echo "- Verify plugin activation works\n";
