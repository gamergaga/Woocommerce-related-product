<?php
/**
 * Test script for Enhanced Algorithm
 * This script tests the enhanced algorithm functionality
 */

// Include WordPress
require_once('wp-config.php');

// Include plugin files
require_once('includes/class-wrp-enhanced-algorithm.php');
require_once('includes/class-wrp-enhanced-cache.php');
require_once('includes/class-wrp-core.php');

function test_enhanced_algorithm() {
    echo "=== Testing Enhanced Algorithm ===\n\n";
    
    try {
        // Initialize enhanced algorithm
        $algorithm = new WRP_Enhanced_Algorithm();
        echo "✓ Enhanced Algorithm initialized successfully\n";
        
        // Get some test products
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $products = get_posts($args);
        
        if (empty($products)) {
            echo "⚠ No products found for testing\n";
            return;
        }
        
        echo "✓ Found " . count($products) . " products for testing\n";
        
        // Test algorithm with first product
        $test_product = $products[0];
        echo "🧪 Testing with product: " . $test_product->post_title . " (ID: " . $test_product->ID . ")\n";
        
        // Get related products using enhanced algorithm
        $config = array(
            'threshold' => 1.5,
            'limit' => 12,
            'weights' => array(
                'title' => 3.0,
                'content' => 2.0,
                'categories' => 4.0,
                'tags' => 3.0,
                'attributes' => 2.0,
                'price_range' => 1.0,
                'brand' => 2.5
            )
        );
        
        $related_products = $algorithm->get_related_products($test_product->ID, $config);
        
        echo "📊 Found " . count($related_products) . " related products\n";
        
        if (!empty($related_products)) {
            echo "\n📋 Top Related Products:\n";
            $count = 0;
            foreach ($related_products as $product_id => $score) {
                if ($count >= 5) break; // Show top 5
                
                $product = get_post($product_id);
                if ($product) {
                    echo "  " . ($count + 1) . ". " . $product->post_title . " (Score: " . round($score, 2) . ")\n";
                }
                $count++;
            }
        }
        
        // Test enhanced cache
        echo "\n=== Testing Enhanced Cache ===\n";
        
        $core = new WRP_Core();
        $cache = new WRP_Enhanced_Cache($core);
        echo "✓ Enhanced Cache initialized successfully\n";
        
        // Test cache building for test product
        $cache_result = $cache->rebuild_product($test_product->ID);
        echo "📦 Cache built for product: " . $cache_result . " relations stored\n";
        
        // Test cache retrieval
        $cached_products = $cache->get_related_ids($test_product->ID, $config);
        echo "📖 Retrieved " . count($cached_products) . " products from cache\n";
        
        // Get cache statistics
        $stats = $cache->get_stats();
        echo "\n📈 Cache Statistics:\n";
        echo "  Total Products: " . $stats['total_products'] . "\n";
        echo "  Cached Products: " . $stats['cached_products'] . "\n";
        echo "  Cache Coverage: " . $stats['cache_percentage'] . "%\n";
        echo "  Total Relations: " . $stats['total_relations'] . "\n";
        echo "  Average Relations: " . $stats['avg_relations'] . "\n";
        echo "  Average Score: " . $stats['avg_score'] . "\n";
        
        echo "\n✅ Enhanced Algorithm Test Completed Successfully!\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// Run the test
test_enhanced_algorithm();
?>