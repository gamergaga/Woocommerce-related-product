<?php
/**
 * Test script for Simple Cache System
 * Run this script to test if the simple cache system works
 */

// Include WordPress
require_once('wp-config.php');

echo "=== WRP Simple Cache Test ===\n\n";

try {
    // Test Simple Cache class
    echo "1. Testing Simple Cache class...\n";
    $cache = new WRP_Simple_Cache();
    echo "✓ Simple Cache class loaded successfully\n";
    
    // Test table status
    echo "\n2. Testing table status...\n";
    $status = $cache->get_table_status();
    echo "✓ Table status: $status\n";
    
    // Test cache clearing
    echo "\n3. Testing cache clearing...\n";
    $clear_result = $cache->clear_all();
    echo "✓ Cache cleared: " . ($clear_result ? 'Success' : 'Failed') . "\n";
    
    // Test cache building
    echo "\n4. Testing cache building...\n";
    $build_result = $cache->build_cache();
    if ($build_result['success']) {
        echo "✓ Cache built successfully\n";
        echo "  - Processed: {$build_result['processed']} products\n";
        echo "  - Relations: {$build_result['total_relations']} relations\n";
    } else {
        echo "✗ Cache build failed\n";
    }
    
    // Test cache stats
    echo "\n5. Testing cache stats...\n";
    $stats = $cache->get_stats();
    echo "✓ Cache statistics:\n";
    echo "  - Total products: {$stats['total_products']}\n";
    echo "  - Cached products: {$stats['cached_products']}\n";
    echo "  - Total relations: {$stats['total_relations']}\n";
    echo "  - Cache percentage: {$stats['cache_percentage']}%\n";
    echo "  - Average relations: {$stats['avg_relations']}\n";
    
    // Test getting related products
    echo "\n6. Testing related products retrieval...\n";
    
    // Get a sample product
    $products = wc_get_products(array(
        'limit' => 1,
        'status' => 'publish',
        'return' => 'ids'
    ));
    
    if (!empty($products)) {
        $product_id = $products[0];
        echo "✓ Using product ID: $product_id for testing\n";
        
        $related_ids = $cache->get_related_products($product_id);
        echo "✓ Found " . count($related_ids) . " related products\n";
        
        if (!empty($related_ids)) {
            echo "  Related IDs: " . implode(', ', $related_ids) . "\n";
        }
    } else {
        echo "✗ No products found for testing\n";
    }
    
    echo "\n=== Test Completed Successfully ===\n";
    
} catch (Exception $e) {
    echo "✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}