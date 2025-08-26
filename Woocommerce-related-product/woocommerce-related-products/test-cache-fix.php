<?php
/**
 * Test script to verify cache system fixes
 * This script tests the cache building process and helps identify issues
 */

// Simulate WordPress environment for testing
define('ABSPATH', '/var/www/html/');
define('WPINC', 'wp-includes');

// Mock WordPress functions
function check_ajax_referer($action, $query_arg = '_wpnonce', $die = true) {
    return true;
}

function current_user_can($capability) {
    return true;
}

function wp_die() {
    die();
}

function wp_send_json_success($data = null, $status_code = null) {
    header('Content-Type: application/json');
    echo json_encode(array('success' => true, 'data' => $data));
    exit;
}

function wp_send_json_error($data = null, $status_code = null) {
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'data' => $data));
    exit;
}

function admin_url($path = '') {
    return 'http://localhost/wp-admin/' . $path;
}

function wp_create_nonce($action = -1) {
    return 'test_nonce_' . $action;
}

function error_log($message) {
    echo "ERROR LOG: $message\n";
}

function get_option($option, $default = false) {
    // Mock some default options for testing
    $defaults = array(
        'wrp_cache_status' => 'empty',
        'wrp_cache_count' => 0,
        'wrp_cache_last_updated' => 'Never',
        'wrp_threshold' => 5,
        'wrp_limit' => 4,
        'wrp_weight_title' => 2,
        'wrp_weight_content' => 1,
        'wrp_weight_categories' => 3,
        'wrp_weight_tags' => 2,
        'wrp_require_tax_product_cat' => 1,
    );
    
    return isset($defaults[$option]) ? $defaults[$option] : $default;
}

function update_option($option, $value) {
    echo "UPDATE OPTION: $option = " . (is_array($value) ? json_encode($value) : $value) . "\n";
    return true;
}

function set_transient($key, $value, $expiration) {
    echo "SET TRANSIENT: $key = " . json_encode($value) . " (expires in $expiration seconds)\n";
    return true;
}

function delete_transient($key) {
    echo "DELETE TRANSIENT: $key\n";
    return true;
}

function get_transient($key) {
    echo "GET TRANSIENT: $key\n";
    return false; // Simulate no transient data
}

function wp_cache_delete($key, $group) {
    echo "DELETE OBJECT CACHE: $group:$key\n";
    return true;
}

function current_time($type) {
    return date('Y-m-d H:i:s');
}

function is_wp_error($thing) {
    return false;
}

function wp_list_pluck($list, $field) {
    $result = [];
    foreach ($list as $item) {
        if (isset($item->$field)) {
            $result[] = $item->$field;
        }
    }
    return $result;
}

function get_the_terms($product_id, $taxonomy) {
    // Mock terms for testing
    return [
        (object)['term_id' => 1, 'name' => 'Electronics'],
        (object)['term_id' => 2, 'name' => 'Headphones']
    ];
}

function get_post($post_id) {
    // Mock product data
    return (object)[
        'ID' => $post_id,
        'post_title' => 'Test Product ' . $post_id,
        'post_content' => 'Test product content for product ' . $post_id,
        'post_excerpt' => 'Test excerpt for product ' . $post_id,
        'post_type' => 'product',
        'post_status' => 'publish'
    ];
}

function strip_tags($string) {
    return preg_replace('/<[^>]*>/', '', $string);
}

// Mock database with realistic behavior
class MockDB {
    public $prefix = 'wp_';
    private $cache_data = [];
    private $products = [];
    private $terms = [];
    
    public function __construct() {
        // Initialize mock data
        $this->initializeMockData();
    }
    
    private function initializeMockData() {
        // Create mock products with realistic content
        $product_data = [
            1 => ['title' => 'Wireless Bluetooth Headphones', 'content' => 'High-quality wireless headphones with noise cancellation and long battery life. Perfect for music lovers and professionals.'],
            2 => ['title' => 'Premium Bluetooth Earbuds', 'content' => 'Wireless earbuds with superior sound quality and comfortable fit. Great for sports and daily use.'],
            3 => ['title' => 'Professional Studio Headphones', 'content' => 'Studio-grade headphones for audio professionals and music producers. Excellent sound reproduction.'],
            4 => ['title' => 'Sports Wireless Headphones', 'content' => 'Sweat-resistant wireless headphones designed for athletes and fitness enthusiasts.'],
            5 => ['title' => 'Gaming Headset with Microphone', 'content' => 'Professional gaming headset with surround sound and noise-cancelling microphone.'],
        ];
        
        foreach ($product_data as $id => $data) {
            $this->products[] = (object)[
                'ID' => $id,
                'post_title' => $data['title'],
                'post_content' => $data['content'],
                'post_excerpt' => substr($data['content'], 0, 100) . '...',
                'post_type' => 'product',
                'post_status' => 'publish'
            ];
        }
    }
    
    public function get_var($query) {
        // Parse query to determine what to return
        if (strpos($query, 'SHOW TABLES') !== false) {
            return 'wp_wrp_related_cache';
        }
        
        if (strpos($query, 'COUNT(*)') !== false) {
            if (strpos($query, 'posts') !== false && strpos($query, 'product') !== false) {
                return count($this->products); // Total products
            }
            if (strpos($query, 'DISTINCT reference_id') !== false) {
                return count(array_unique(array_column($this->cache_data, 'reference_id')));
            }
            if (strpos($query, 'wrp_related_cache') !== false) {
                return count($this->cache_data);
            }
        }
        
        return '0';
    }
    
    public function get_results($query) {
        // Return mock products
        return $this->products;
    }
    
    public function query($query) {
        if (strpos($query, 'TRUNCATE') !== false) {
            $this->cache_data = [];
            echo "CACHE TABLE TRUNCATED\n";
            return true;
        }
        if (strpos($query, 'DELETE') !== false) {
            // Simple delete implementation for testing
            if (preg_match('/reference_id = (\d+)/', $query, $matches)) {
                $reference_id = intval($matches[1]);
                $this->cache_data = array_filter($this->cache_data, function($item) use ($reference_id) {
                    return $item['reference_id'] != $reference_id;
                });
            }
            return true;
        }
        return true;
    }
    
    public function insert($table, $data, $format) {
        $this->cache_data[] = $data;
        echo "CACHE ENTRY INSERTED: " . json_encode($data) . "\n";
        return true;
    }
    
    public function prepare($query, $args) {
        // Simple prepare mock
        foreach ($args as $arg) {
            $query = preg_replace('/%[dsf]/', strval($arg), $query, 1);
        }
        return $query;
    }
    
    public function get_charset_collate() {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
    
    // Helper method to get cache data for testing
    public function get_cache_data() {
        return $this->cache_data;
    }
}

// Mock WooCommerce functions
function wc_get_products($args) {
    global $wpdb;
    return $wpdb->products;
}

function wc_get_product($product_id) {
    global $wpdb;
    foreach ($wpdb->products as $product) {
        if ($product->ID == $product_id) {
            return $product;
        }
    }
    return null;
}

// Global database
global $wpdb;
$wpdb = new MockDB();

// Include the necessary classes
require_once 'includes/class-wrp-cache-tables.php';
require_once 'includes/class-wrp-core.php';
require_once 'includes/class-wrp-db-schema.php';

echo "=== WRP Cache System Fix Test ===\n\n";

// Test 1: Initialize Core and Cache
echo "Test 1: Initialize Core and Cache\n";
try {
    $db_schema = new WRP_DB_Schema();
    $db_schema->create_tables();
    echo "✓ Database schema initialized\n";
    
    $core = new WRP_Core();
    echo "✓ Core system initialized\n";
    
    $cache = $core->get_cache();
    echo "✓ Cache system initialized\n";
} catch (Exception $e) {
    echo "✗ Failed to initialize systems: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Check Cache Table
echo "Test 2: Check Cache Table\n";
try {
    $table_exists = $db_schema->cache_table_exists();
    echo "✓ Cache table exists: " . ($table_exists ? 'Yes' : 'No') . "\n";
    
    if ($table_exists) {
        $stats = $db_schema->get_cache_stats();
        echo "✓ Cache stats: " . json_encode($stats) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to check cache table: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Test Cache Building for Single Product
echo "Test 3: Test Cache Building for Single Product\n";
try {
    $product_id = 1;
    $args = [
        'limit' => 4,
        'threshold' => 1,
        'weight' => [
            'title' => 2,
            'description' => 1,
            'tax' => [
                'product_cat' => 3,
                'product_tag' => 2,
            ],
        ],
        'require_tax' => [
            'product_cat' => 1,
        ],
    ];
    
    echo "Testing cache enforcement for product ID: $product_id\n";
    
    // Check if product is cached initially
    $is_cached = $cache->is_cached($product_id);
    echo "  Initially cached: " . ($is_cached ? 'Yes' : 'No') . "\n";
    
    // Enforce cache
    $cache->enforce_cache($product_id, $args);
    echo "  Cache enforcement completed\n";
    
    // Check if product is cached after enforcement
    $is_cached = $cache->is_cached($product_id);
    echo "  Cached after enforcement: " . ($is_cached ? 'Yes' : 'No') . "\n";
    
    // Get related products
    $related_ids = $cache->get_related_ids($product_id, $args);
    echo "  Related products found: " . count($related_ids) . "\n";
    foreach ($related_ids as $id) {
        echo "    - Product ID: $id\n";
    }
    
    // Show cache data
    $cache_data = $wpdb->get_cache_data();
    echo "  Total cache entries: " . count($cache_data) . "\n";
    
} catch (Exception $e) {
    echo "✗ Failed to test cache building: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
echo "\n";

// Test 4: Test Cache Clearing
echo "Test 4: Test Cache Clearing\n";
try {
    $cache->clear_all();
    echo "✓ Cache cleared\n";
    
    $cache_data = $wpdb->get_cache_data();
    echo "  Cache entries after clear: " . count($cache_data) . "\n";
    
    $is_cached = $cache->is_cached(1);
    echo "  Product 1 cached after clear: " . ($is_cached ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "✗ Failed to test cache clearing: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test Cache Statistics
echo "Test 5: Test Cache Statistics\n";
try {
    $stats = $cache->get_stats();
    echo "✓ Cache statistics:\n";
    echo "  Total products: {$stats['total_products']}\n";
    echo "  Cached products: {$stats['cached_products']}\n";
    echo "  Total relations: {$stats['total_relations']}\n";
    echo "  Cache percentage: {$stats['cache_percentage']}%\n";
    echo "  Avg relations: {$stats['avg_relations']}\n";
} catch (Exception $e) {
    echo "✗ Failed to get cache statistics: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Test Complete ===\n";
echo "If all tests pass, the cache system should work correctly.\n";
echo "The main fixes applied:\n";
echo "1. Improved cache building process with better error handling\n";
echo "2. Fixed JavaScript progress tracking\n";
echo "3. Added comprehensive logging and debugging\n";
echo "4. Ensured cache table exists and is properly structured\n";
?>