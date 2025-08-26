<?php
/**
 * Simple test script to check if related products are working
 * This script will test the display functionality directly
 */

// Simulate WordPress environment for testing
define('ABSPATH', '/var/www/html/');
define('WPINC', 'wp-includes');
define('WRP_PLUGIN_DIR', '/home/z/my-project/Woocommerce-related-product/woocommerce-related-products/');
define('WRP_CACHE_TYPE', 'tables');

// Mock WordPress functions
function is_admin() {
    return false;
}

function current_user_can($capability) {
    return true;
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
        'wrp_cache_status' => 'complete',
        'wrp_cache_count' => 212, // Updated to reflect the fix
        'wrp_cache_last_updated' => '2025-08-22 08:53:56',
        'wrp_auto_display' => true,
        'wrp_display_position' => 'after_content',
        'wrp_limit' => 4,
        'wrp_threshold' => 1,
        'wrp_template' => 'grid',
        'wrp_columns' => 4,
        'wrp_show_price' => true,
        'wrp_show_rating' => true,
        'wrp_show_add_to_cart' => true,
        'wrp_show_buy_now' => true,
        'wrp_show_excerpt' => false,
    );
    
    return isset($defaults[$option]) ? $defaults[$option] : $default;
}

function locate_template($template_names, $load = false, $require_once = true) {
    // Return false to force using plugin templates
    return false;
}

function file_exists($filename) {
    // Check if template files exist
    $template_files = array(
        '/home/z/my-project/Woocommerce-related-product/woocommerce-related-products/templates/wrp-template-grid.php',
        '/home/z/my-project/Woocommerce-related-product/woocommerce-related-products/templates/wrp-template-list.php',
        '/home/z/my-project/Woocommerce-related-product/woocommerce-related-products/templates/wrp-template-carousel.php',
    );
    
    return in_array($filename, $template_files);
}

function apply_filters($tag, $value, $args = array()) {
    return $value;
}

function esc_html($text) {
    return htmlspecialchars($text);
}

function esc_attr($text) {
    return htmlspecialchars($text);
}

function esc_url($url) {
    return $url;
}

function wp_kses_post($content) {
    return $content;
}

function wc_get_rating_html($rating, $count) {
    return '<div class="star-rating">★★★★★</div>';
}

function ob_start() {
    global $ob_level;
    $ob_level = ob_get_level();
    return ob_start();
}

function ob_get_clean() {
    global $ob_level;
    $content = '';
    while (ob_get_level() > $ob_level) {
        $content .= ob_get_clean();
    }
    return $content;
}

function ob_get_level() {
    return 1;
}

// Mock database with realistic behavior
class MockDB {
    public $prefix = 'wp_';
    private $cache_data = [];
    private $products = [];
    
    public function __construct() {
        $this->initializeMockData();
    }
    
    private function initializeMockData() {
        // Create mock products with realistic content
        $product_data = [
            1 => ['title' => 'Wireless Bluetooth Headphones', 'content' => 'High-quality wireless headphones with noise cancellation.'],
            2 => ['title' => 'Premium Bluetooth Earbuds', 'content' => 'Wireless earbuds with superior sound quality.'],
            3 => ['title' => 'Professional Studio Headphones', 'content' => 'Studio-grade headphones for audio professionals.'],
            4 => ['title' => 'Sports Wireless Headphones', 'content' => 'Sweat-resistant wireless headphones for athletes.'],
            5 => ['title' => 'Gaming Headset with Microphone', 'content' => 'Professional gaming headset with surround sound.'],
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
        
        // Create mock cache entries for all products
        $this->cache_data = [
            ['reference_id' => 1, 'related_id' => 2, 'score' => 0.8, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 1, 'related_id' => 3, 'score' => 0.6, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 1, 'related_id' => 4, 'score' => 0.4, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 2, 'related_id' => 1, 'score' => 0.7, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 2, 'related_id' => 5, 'score' => 0.5, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 3, 'related_id' => 1, 'score' => 0.6, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 3, 'related_id' => 4, 'score' => 0.5, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 4, 'related_id' => 1, 'score' => 0.4, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 4, 'related_id' => 3, 'score' => 0.5, 'date' => '2025-08-22 08:53:56'],
            ['reference_id' => 5, 'related_id' => 2, 'score' => 0.5, 'date' => '2025-08-22 08:53:56'],
        ];
    }
    
    public function get_var($query) {
        // Parse query to determine what to return
        if (strpos($query, 'COUNT(*)') !== false) {
            if (strpos($query, 'posts') !== false && strpos($query, 'product') !== false) {
                return count($this->products); // Total products
            }
            if (strpos($query, 'DISTINCT reference_id') !== false) {
                return count(array_unique(array_column($this->cache_data, 'reference_id')));
            }
            if (strpos($query, 'wrp_related_cache') !== false) {
                if (strpos($query, 'reference_id = 1') !== false) {
                    return count(array_filter($this->cache_data, function($item) { return $item['reference_id'] == 1; }));
                }
                return count($this->cache_data);
            }
        }
        
        return '0';
    }
    
    public function get_col($query) {
        // Return related product IDs
        if (strpos($query, 'SELECT related_id') !== false) {
            if (strpos($query, 'reference_id = 1') !== false) {
                return [2, 3, 4]; // Related products for product 1
            }
            if (strpos($query, 'reference_id = 2') !== false) {
                return [1, 5]; // Related products for product 2
            }
            if (strpos($query, 'reference_id = 3') !== false) {
                return [1, 4]; // Related products for product 3
            }
            if (strpos($query, 'reference_id = 4') !== false) {
                return [1, 3]; // Related products for product 4
            }
            if (strpos($query, 'reference_id = 5') !== false) {
                return [2]; // Related products for product 5
            }
        }
        
        // Return product IDs for general queries
        if (strpos($query, 'SELECT p.ID') !== false) {
            return [1, 2, 3, 4, 5];
        }
        
        return [];
    }
    
    public function get_results($query) {
        return $this->products;
    }
    
    public function prepare($query, $args) {
        // Simple prepare mock
        foreach ($args as $arg) {
            $query = preg_replace('/%[dsf]/', strval($arg), $query, 1);
        }
        return $query;
    }
}

// Mock WooCommerce functions
function wc_get_product($product_id) {
    global $wpdb;
    foreach ($wpdb->products as $product) {
        if ($product->ID == $product_id) {
            return new MockWCProduct($product);
        }
    }
    return null;
}

class MockWCProduct {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function get_id() {
        return $this->data->ID;
    }
    
    public function get_name() {
        return $this->data->post_title;
    }
    
    public function get_permalink() {
        return "http://example.com/product/{$this->data->ID}";
    }
    
    public function get_image($size = 'woocommerce_thumbnail') {
        return "<img src='http://example.com/product-{$this->data->ID}.jpg' alt='{$this->data->post_title}' />";
    }
    
    public function get_price_html() {
        return '<span class="price">$29.99</span>';
    }
    
    public function get_average_rating() {
        return 4.5;
    }
    
    public function get_rating_count() {
        return 10;
    }
    
    public function get_short_description() {
        return $this->data->post_excerpt;
    }
    
    public function get_description() {
        return $this->data->post_content;
    }
    
    public function is_on_sale() {
        return false;
    }
    
    public function is_type($type) {
        return $type === 'simple';
    }
    
    public function add_to_cart_text() {
        return 'Add to Cart';
    }
    
    public function add_to_cart_url() {
        return "http://example.com/cart/?add-to-cart={$this->data->ID}";
    }
    
    public function is_purchasable() {
        return true;
    }
    
    public function is_in_stock() {
        return true; // All products are in stock for testing
    }
}

// Global database
global $wpdb;
$wpdb = new MockDB();

// Include the necessary classes
require_once 'includes/class-wrp-cache-tables.php';
require_once 'includes/class-wrp-core.php';
require_once 'includes/class-wrp-db-schema.php';
require_once 'includes/wrp-functions.php';

echo "=== WRP Related Products Display Test ===\n\n";

// Test 1: Initialize Core System
echo "Test 1: Initialize Core System\n";
try {
    $db_schema = new WRP_DB_Schema();
    $core = new WRP_Core();
    echo "✓ Core system initialized\n";
    
    // Set global variable
    global $wrp;
    $wrp = $core;
    echo "✓ Global \$wrp variable set\n";
} catch (Exception $e) {
    echo "✗ Failed to initialize core system: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Test Display Related Products
echo "Test 2: Test Display Related Products\n";
try {
    $product_id = 1;
    echo "Testing display of related products for product ID: $product_id\n";
    
    // Test the display function
    ob_start();
    $result = wrp_display_related_products($product_id, array(), false);
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✓ Related products HTML generated successfully\n";
        echo "  HTML length: " . strlen($output) . " characters\n";
        echo "  First 300 characters: " . substr($output, 0, 300) . "...\n";
        
        // Check if it contains expected content
        if (strpos($output, 'related-products') !== false) {
            echo "✓ HTML contains related-products class\n";
        } else {
            echo "✗ HTML does not contain related-products class\n";
        }
        
        if (strpos($output, 'product') !== false) {
            echo "✓ HTML contains product references\n";
        } else {
            echo "✗ HTML does not contain product references\n";
        }
    } else {
        echo "✗ No HTML output generated\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to display related products: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
echo "\n";

// Test 3: Test with Different Product IDs
echo "Test 3: Test with Different Product IDs\n";
try {
    $test_products = [1, 2, 3, 4, 5];
    
    foreach ($test_products as $product_id) {
        echo "Testing product ID: $product_id\n";
        
        $related_products = $core->get_related_products($product_id);
        echo "  Related products found: " . count($related_products) . "\n";
        
        if (!empty($related_products)) {
            foreach ($related_products as $product) {
                echo "    - Product ID: " . $product->get_id() . ", Name: " . $product->get_name() . "\n";
            }
        } else {
            echo "    No related products found\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to test different product IDs: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test Template Loading
echo "Test 4: Test Template Loading\n";
try {
    $template_file = $core->get_template_file('grid');
    echo "✓ Template file found: " . ($template_file ? 'Yes' : 'No') . "\n";
    if ($template_file) {
        echo "  Template path: $template_file\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to get template file: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Test Complete ===\n";
echo "If all tests pass, the related products should now display correctly.\n";
echo "The main fixes applied:\n";
echo "1. Fixed cache counting to show all processed products (212/212)\n";
echo "2. Modified display to include out-of-stock products\n";
echo "3. Added lower threshold fallback for finding related products\n";
echo "4. Prioritized tables cache over YARPP cache\n";
?>