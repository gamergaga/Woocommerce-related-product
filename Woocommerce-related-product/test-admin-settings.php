<?php
/**
 * Test script to verify admin settings are working correctly
 */

// Include WordPress
require_once('wp-config.php');

echo "=== WRP Admin Settings Test ===\n\n";

// Test 1: Check if settings are properly registered
global $wp_registered_settings;
$all_settings = get_option('wrp_all_settings', array());

echo "1. Settings Registration Test:\n";
if (isset($wp_registered_settings['wrp_threshold'])) {
    echo "   ✓ wrp_threshold registered\n";
} else {
    echo "   ✗ wrp_threshold not registered\n";
}

if (isset($wp_registered_settings['wrp_limit'])) {
    echo "   ✓ wrp_limit registered\n";
} else {
    echo "   ✗ wrp_limit not registered\n";
}

if (isset($wp_registered_settings['wrp_show_price'])) {
    echo "   ✓ wrp_show_price registered\n";
} else {
    echo "   ✗ wrp_show_price not registered\n";
}

if (isset($wp_registered_settings['wrp_template'])) {
    echo "   ✓ wrp_template registered\n";
} else {
    echo "   ✗ wrp_template not registered\n";
}

echo "\n2. Current Settings Values:\n";
echo "   Threshold: " . wrp_get_option('threshold', 'default') . "\n";
echo "   Limit: " . wrp_get_option('limit', 'default') . "\n";
echo "   Show Price: " . (wrp_get_option('show_price', false) ? 'true' : 'false') . "\n";
echo "   Template: " . wrp_get_option('template', 'default') . "\n";
echo "   Auto Display: " . (wrp_get_option('auto_display', false) ? 'true' : 'false') . "\n";

echo "\n3. Settings Group Test:\n";
$all_settings_group = get_option('wrp_all_settings', array());
if (is_array($all_settings_group) && !empty($all_settings_group)) {
    echo "   ✓ wrp_all_settings group exists with " . count($all_settings_group) . " settings\n";
    echo "   Settings in group: " . implode(', ', array_keys($all_settings_group)) . "\n";
} else {
    echo "   ✗ wrp_all_settings group is empty or not an array\n";
}

echo "\n4. Individual Settings Test:\n";
$settings_to_test = array(
    'wrp_threshold',
    'wrp_limit', 
    'wrp_excerpt_length',
    'wrp_auto_display',
    'wrp_display_position',
    'wrp_cache_enabled',
    'wrp_cache_timeout',
    'wrp_show_price',
    'wrp_show_rating',
    'wrp_show_add_to_cart',
    'wrp_show_buy_now',
    'wrp_template',
    'wrp_columns',
    'wrp_image_size',
    'wrp_show_excerpt',
    'wrp_weight',
    'wrp_require_tax',
    'wrp_exclude',
    'wrp_recent_only',
    'wrp_recent_period',
    'wrp_include_out_of_stock'
);

$found_settings = 0;
foreach ($settings_to_test as $setting) {
    $value = get_option($setting, 'NOT_FOUND');
    if ($value !== 'NOT_FOUND') {
        $found_settings++;
        echo "   ✓ $setting: " . (is_array($value) ? 'array' : (is_bool($value) ? ($value ? 'true' : 'false') : $value)) . "\n";
    } else {
        echo "   ✗ $setting: not found\n";
    }
}

echo "\n   Found $found_settings out of " . count($settings_to_test) . " settings\n";

echo "\n5. Settings API Test:\n";
// Test if we can update a setting
$test_value = 'test_' . time();
update_option('wrp_test_setting', $test_value);
$retrieved_value = get_option('wrp_test_setting', 'NOT_FOUND');

if ($retrieved_value === $test_value) {
    echo "   ✓ Settings API working - test setting saved and retrieved\n";
    // Clean up
    delete_option('wrp_test_setting');
} else {
    echo "   ✗ Settings API not working - test setting failed\n";
}

echo "\n6. Form Submission Test:\n";
// Check if the settings page exists
$settings_page = admin_url('admin.php?page=wrp-settings');
echo "   Settings page URL: $settings_page\n";
echo "   ✓ Please manually test form submission by:\n";
echo "     1. Visiting the settings page\n";
echo "     2. Changing some values\n";
echo "     3. Clicking 'Save Changes'\n";
echo "     4. Refreshing the page to verify values persist\n";

echo "\n=== Test Complete ===\n";
echo "If most checks pass, the admin settings should work correctly.\n";
echo "Manual testing of the form submission is still required.\n";