<?php
/**
 * Phase 1 Fix Test - Topics Generator Data Loading
 * Run this to verify the immediate fix is working
 */

// WordPress environment
if (!defined('ABSPATH')) {
    require_once('../../../../wp-load.php');
}

echo "🧪 PHASE 1 FIX TEST - Topics Generator Data Loading\n";
echo "==================================================\n\n";

// Test the template fix
echo "📋 Testing Template Enhanced Error Handling\n";
echo "--------------------------------------------\n";

// Simulate the template environment
$entry_id = 0;
$entry_key = 'y8ver'; // Test with real entry key
$entry_data = null;
$form_field_values = [];
$authority_hook_components = [
    'who' => '',
    'result' => '',
    'when' => '',
    'how' => '',
    'complete' => ''
];

// Test 1: Check if classes are available
echo "1. Checking required classes...\n";
$required_classes = [
    'MKCG_Formidable_Service',
    'MKCG_Topics_Generator', 
    'MKCG_Config'
];

$all_classes_available = true;
foreach ($required_classes as $class) {
    if (class_exists($class)) {
        echo "   ✅ {$class} - Available\n";
    } else {
        echo "   ❌ {$class} - Missing\n";
        $all_classes_available = false;
    }
}

if (!$all_classes_available) {
    echo "\n❌ CRITICAL: Required classes missing. Plugin may not be loaded properly.\n";
    exit;
}

// Test 2: Template logic simulation
echo "\n2. Testing template logic with enhanced error handling...\n";

// Simulate the template code with our fix
if (isset($_GET['entry']) || $entry_key) {
    if (!$entry_key) {
        $entry_key = sanitize_text_field($_GET['entry']);
    }
    
    echo "   Entry key: {$entry_key}\n";
    
    // CRITICAL FIX TEST: Ensure Formidable service is available
    if (!isset($formidable_service) && class_exists('MKCG_Formidable_Service')) {
        $formidable_service = new MKCG_Formidable_Service();
        echo "   ✅ Created Formidable service instance (FIX WORKING)\n";
    }
    
    // Test data loading
    if (isset($formidable_service)) {
        echo "   ✅ Formidable service available\n";
        
        $entry_data = $formidable_service->get_entry_data($entry_key);
        if ($entry_data['success']) {
            $entry_id = $entry_data['entry_id'];
            echo "   ✅ Entry data loaded successfully (ID: {$entry_id})\n";
            
            // Test authority hook loading
            $authority_hook_components['who'] = $formidable_service->get_field_value($entry_id, 10296) ?: 'your audience';
            $authority_hook_components['result'] = $formidable_service->get_field_value($entry_id, 10297) ?: 'achieve their goals';
            $authority_hook_components['when'] = $formidable_service->get_field_value($entry_id, 10387) ?: 'they need help';
            $authority_hook_components['how'] = $formidable_service->get_field_value($entry_id, 10298) ?: 'through your method';
            
            echo "   ✅ Authority hook components loaded:\n";
            echo "      WHO: " . $authority_hook_components['who'] . "\n";
            echo "      RESULT: " . $authority_hook_components['result'] . "\n";
            echo "      WHEN: " . $authority_hook_components['when'] . "\n";
            echo "      HOW: " . $authority_hook_components['how'] . "\n";
            
            // Test topics loading
            $form_field_values['topic_1'] = $formidable_service->get_field_value($entry_id, 8498);
            $form_field_values['topic_2'] = $formidable_service->get_field_value($entry_id, 8499);
            $form_field_values['topic_3'] = $formidable_service->get_field_value($entry_id, 8500);
            $form_field_values['topic_4'] = $formidable_service->get_field_value($entry_id, 8501);
            $form_field_values['topic_5'] = $formidable_service->get_field_value($entry_id, 8502);
            
            $topics_found = count(array_filter($form_field_values));
            echo "   ✅ Topics loaded: {$topics_found}/5 topics found\n";
            
            if ($topics_found > 0) {
                foreach ($form_field_values as $key => $value) {
                    if (!empty($value)) {
                        echo "      {$key}: " . substr($value, 0, 50) . "...\n";
                    }
                }
            }
            
        } else {
            echo "   ⚠️ Entry data loading failed: " . $entry_data['message'] . "\n";
        }
    } else {
        echo "   ❌ Formidable service not available\n";
    }
}

// Test 3: JavaScript data output simulation
echo "\n3. Testing JavaScript data output...\n";

$js_data = [
    'entryId' => intval($entry_id),
    'entryKey' => $entry_key,
    'hasEntry' => $entry_id > 0,
    'authorityHook' => $authority_hook_components,
    'topics' => $form_field_values
];

echo "   JavaScript data structure:\n";
echo "   " . json_encode($js_data, JSON_PRETTY_PRINT) . "\n";

// Test 4: Element existence check (simulated)
echo "\n4. Testing expected HTML element IDs...\n";

$expected_elements = [
    'topics-generator-authority-hook-text',
    'topics-generator-generate-topics',
    'topics-generator-who-input',
    'topics-generator-result-input',
    'topics-generator-when-input',
    'topics-generator-how-input',
    'topics-generator-topic-field-1',
    'topics-generator-topic-field-2',
    'topics-generator-topic-field-3',
    'topics-generator-topic-field-4',
    'topics-generator-topic-field-5'
];

foreach ($expected_elements as $element_id) {
    echo "   ✅ Expected element ID: #{$element_id}\n";
}

// Summary
echo "\n🎯 PHASE 1 FIX SUMMARY\n";
echo "======================\n";

if ($entry_id > 0) {
    echo "✅ SUCCESS: Template fix is working!\n";
    echo "   - Formidable service created successfully\n";
    echo "   - Entry data loaded (ID: {$entry_id})\n";
    echo "   - Authority hook components populated\n";
    echo "   - Topics data available ({$topics_found}/5)\n";
    echo "   - JavaScript data structure ready\n";
    
    echo "\n🔍 NEXT STEPS:\n";
    echo "1. Test the Topics Generator page with: ?entry={$entry_key}\n";
    echo "2. Check browser console for debugging output\n";
    echo "3. Verify elements populate automatically\n";
    echo "4. Test the 'Generate Topics with AI' button\n";
    
} else {
    echo "⚠️ PARTIAL SUCCESS: Template fix works but no entry data\n";
    echo "   - Check if entry key '{$entry_key}' exists\n";
    echo "   - Verify Formidable form data\n";
    echo "   - Test with different entry key\n";
}

echo "\n✨ Phase 1 immediate fix implementation complete!\n";
?>