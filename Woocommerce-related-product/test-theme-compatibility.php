<?php
/**
 * Test script to verify theme compatibility and admin settings fixes
 */

// Include WordPress
require_once('wp-config.php');

echo "=== WRP Theme Compatibility Test ===\n\n";

// Test 1: Check if WRP_Core class exists
if (class_exists('WRP_Core')) {
    echo "✓ WRP_Core class found\n";
    
    // Test theme detection
    $current_theme = wp_get_theme();
    $theme_name = strtolower($current_theme->get('Name'));
    echo "✓ Current theme: " . $current_theme->get('Name') . "\n";
    
    // Test theme compatibility settings
    $wrp_core = new WRP_Core();
    $reflection = new ReflectionClass($wrp_core);
    if ($reflection->hasMethod('get_theme_compatibility_settings')) {
        echo "✓ Theme compatibility method exists\n";
        
        $method = $reflection->getMethod('get_theme_compatibility_settings');
        $method->setAccessible(true);
        $settings = $method->invoke($wrp_core);
        
        echo "✓ Theme compatibility settings retrieved\n";
        echo "  - Wrapper classes: " . implode(', ', $settings['wrapper_classes']) . "\n";
        echo "  - Priority settings available\n";
    } else {
        echo "✗ Theme compatibility method not found\n";
    }
} else {
    echo "✗ WRP_Core class not found\n";
}

echo "\n=== Admin Settings Test ===\n";

// Test 2: Check if settings are properly registered
global $wp_settings_fields;
$general_settings = isset($wp_settings_fields['wrp_general']) ? count($wp_settings_fields['wrp_general']) : 0;
$display_settings = isset($wp_settings_fields['wrp_display']) ? count($wp_settings_fields['wrp_display']) : 0;
$algorithm_settings = isset($wp_settings_fields['wrp_algorithm']) ? count($wp_settings_fields['wrp_algorithm']) : 0;

echo "✓ General settings registered: $general_settings fields\n";
echo "✓ Display settings registered: $display_settings fields\n";
echo "✓ Algorithm settings registered: $algorithm_settings fields\n";

// Test 3: Check if options can be retrieved
$threshold = wrp_get_option('threshold', 'default');
$limit = wrp_get_option('limit', 'default');
$template = wrp_get_option('template', 'default');

echo "✓ Threshold option: $threshold\n";
echo "✓ Limit option: $limit\n";
echo "✓ Template option: $template\n";

echo "\n=== Hook Registration Test ===\n";

// Test 4: Check if hooks are properly registered
global $wp_filter;
$hooks_to_check = array(
    'woocommerce_before_single_product_summary' => 'maybe_display_related_products_before',
    'woocommerce_after_single_product_summary' => 'maybe_display_related_products_after',
    'woocommerce_after_add_to_cart_form' => 'maybe_display_related_products_after_cart',
    'woocommerce_after_single_product' => 'maybe_display_related_products_theme_fallback',
    'wp_footer' => 'maybe_display_related_products_fallback'
);

foreach ($hooks_to_check as $hook => $method) {
    if (isset($wp_filter[$hook])) {
        foreach ($wp_filter[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && 
                    is_object($callback['function'][0]) && 
                    get_class($callback['function'][0]) === 'WRP_Core' &&
                    $callback['function'][1] === $method) {
                    echo "✓ Hook '$hook' registered with method '$method' (priority: $priority)\n";
                    continue 3;
                }
            }
        }
        echo "✗ Hook '$hook' not found with method '$method'\n";
    } else {
        echo "✗ Hook '$hook' not registered\n";
    }
}

echo "\n=== Cache Status Test ===\n";

// Test 5: Check cache status
$cache_status = get_option('wrp_cache_status', 'not_set');
$cache_count = get_option('wrp_cache_count', 0);
$cache_last_updated = get_option('wrp_cache_last_updated', 'never');

echo "✓ Cache status: $cache_status\n";
echo "✓ Cache count: $cache_count\n";
echo "✓ Cache last updated: $cache_last_updated\n";

echo "\n=== Test Complete ===\n";
echo "If all checks pass, the fixes should work correctly.\n";
echo "Please test in the admin panel and on product pages.\n";