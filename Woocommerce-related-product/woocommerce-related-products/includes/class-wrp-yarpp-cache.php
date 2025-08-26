<?php
/**
 * YARPP-style Cache System for WooCommerce Related Products Pro
 * Based on YARPP's proven caching methodology
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_YARPP_Cache {

    /**
     * Cache table name
     */
    private $cache_table;

    /**
     * Options
     */
    private $options;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->cache_table = $wpdb->prefix . 'wrp_related_cache';
        $this->options = $this->get_default_options();
        $this->ensure_table_exists();
    }

    /**
     * Get default options
     */
    private function get_default_options() {
        return array(
            'threshold' => 5,
            'limit' => 4,
            'weight' => array(
                'title' => 2,
                'content' => 1,
                'categories' => 3,
                'tags' => 2,
                'custom_taxonomies' => 1
            ),
            'require_tax' => array(
                'product_cat' => 1
            ),
            'exclude' => '',
            'recent_only' => false,
            'recent_period' => '30 day',
            'include_out_of_stock' => false,
            'show_price' => true,
            'show_rating' => true,
            'show_add_to_cart' => true,
            'show_buy_now' => true,
            'template' => 'grid',
            'columns' => 4,
            'image_size' => 'woocommerce_thumbnail',
            'show_excerpt' => false,
            'excerpt_length' => 10,
            'cache_enabled' => true,
            'cache_timeout' => 3600,
        );
    }

    /**
     * Ensure cache table exists
     */
    private function ensure_table_exists() {
        global $wpdb;
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->cache_table}'");
        
        if ($table_exists != $this->cache_table) {
            $this->create_table();
        }
    }

    /**
     * Create cache table with YARPP-style structure
     */
    private function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->cache_table} (
            reference_id bigint(20) NOT NULL,
            related_id bigint(20) NOT NULL,
            score float NOT NULL DEFAULT 0,
            date datetime NOT NULL,
            PRIMARY KEY  (reference_id, related_id),
            KEY score (score),
            KEY related_id (related_id),
            KEY date (date)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        error_log("WRP YARPP Cache: Table created successfully");
    }

    /**
     * Get option value
     */
    public function get_option($key) {
        $option_key = 'wrp_' . $key;
        $value = get_option($option_key);
        
        if ($value === false) {
            $value = isset($this->options[$key]) ? $this->options[$key] : null;
        }
        
        return $value;
    }

    /**
     * Clear all cache
     */
    public function clear_cache() {
        global $wpdb;
        
        $result = $wpdb->query("TRUNCATE TABLE {$this->cache_table}");
        error_log("WRP YARPP Cache: Cache cleared - " . ($result !== false ? 'Success' : 'Failed'));
        
        return $result !== false;
    }

    /**
     * Build cache for all products using YARPP-style batch processing
     */
    public function build_cache($batch_size = 50) {
        global $wpdb;
        
        error_log("WRP YARPP Cache: Starting cache build with batch size: $batch_size");
        
        // Clear existing cache
        $this->clear_cache();
        
        // Get all published products
        $products = $wpdb->get_results("
            SELECT ID, post_title, post_content, post_excerpt 
            FROM {$wpdb->posts} 
            WHERE post_type = 'product' AND post_status = 'publish'
            ORDER BY ID ASC
        ");
        
        if ($products === false) {
            error_log("WRP YARPP Cache: Failed to query products");
            return array(
                'processed' => 0,
                'total_relations' => 0,
                'success' => false,
                'message' => 'Failed to query products'
            );
        }
        
        $total_products = count($products);
        $processed = 0;
        $total_relations = 0;
        $errors = 0;
        
        error_log("WRP YARPP Cache: Found $total_products products to process");
        
        // Process products in batches
        foreach ($products as $product) {
            try {
                $relations = $this->find_related_products($product->ID);
                
                if (!empty($relations)) {
                    foreach ($relations as $related_id => $score) {
                        $result = $wpdb->insert(
                            $this->cache_table,
                            array(
                                'reference_id' => $product->ID,
                                'related_id' => $related_id,
                                'score' => $score,
                                'date' => current_time('mysql')
                            ),
                            array('%d', '%d', '%f', '%s')
                        );
                        
                        if ($result !== false) {
                            $total_relations++;
                        }
                    }
                }
                
                $processed++;
                
                // Log progress every 10 products
                if ($processed % 10 == 0) {
                    error_log("WRP YARPP Cache: Processed $processed/$total_products products, $total_relations relations");
                }
                
            } catch (Exception $e) {
                error_log("WRP YARPP Cache: Error processing product {$product->ID}: " . $e->getMessage());
                $errors++;
            }
        }
        
        error_log("WRP YARPP Cache: Cache build completed. Processed $processed products, created $total_relations relations, $errors errors");
        
        return array(
            'processed' => $processed,
            'total_relations' => $total_relations,
            'errors' => $errors,
            'success' => true,
            'message' => sprintf(
                'Cache built successfully! Processed %d products with %d relations (%d errors).',
                $processed,
                $total_relations,
                $errors
            )
        );
    }

    /**
     * Find related products using YARPP-style scoring algorithm
     */
    private function find_related_products($reference_id, $limit = null) {
        global $wpdb;
        
        if ($limit === null) {
            $limit = $this->get_option('limit');
        }
        
        $threshold = $this->get_option('threshold');
        $weights = $this->get_option('weight');
        $require_tax = $this->get_option('require_tax');
        
        // Get reference product data
        $reference_product = get_post($reference_id);
        if (!$reference_product || $reference_product->post_type !== 'product') {
            return array();
        }
        
        // Get reference product terms
        $reference_terms = $this->get_product_terms($reference_id);
        
        // Build scoring query
        $scores = array();
        
        // Get all other products
        $products = $wpdb->get_results($wpdb->prepare("
            SELECT ID, post_title, post_content, post_excerpt 
            FROM {$wpdb->posts} 
            WHERE post_type = 'product' 
            AND post_status = 'publish'
            AND ID != %d
            ORDER BY ID ASC
        ", $reference_id));
        
        if ($products === false) {
            return array();
        }
        
        foreach ($products as $product) {
            $score = 0;
            $has_required_taxonomy = false;
            
            // Get product terms
            $product_terms = $this->get_product_terms($product->ID);
            
            // Check required taxonomies
            foreach ($require_tax as $taxonomy => $required) {
                if ($required && isset($reference_terms[$taxonomy]) && isset($product_terms[$taxonomy])) {
                    $common_terms = array_intersect($reference_terms[$taxonomy], $product_terms[$taxonomy]);
                    if (!empty($common_terms)) {
                        $has_required_taxonomy = true;
                        break;
                    }
                }
            }
            
            // Skip if required taxonomy not found
            if (!empty($require_tax) && !$has_required_taxonomy) {
                continue;
            }
            
            // Title similarity (simple word matching)
            if (isset($weights['title']) && $weights['title'] > 0) {
                $title_score = $this->calculate_text_similarity(
                    $reference_product->post_title,
                    $product->post_title
                );
                $score += $title_score * $weights['title'];
            }
            
            // Content similarity
            if (isset($weights['content']) && $weights['content'] > 0) {
                $content_score = $this->calculate_text_similarity(
                    $reference_product->post_content . ' ' . $reference_product->post_excerpt,
                    $product->post_content . ' ' . $product->post_excerpt
                );
                $score += $content_score * $weights['content'];
            }
            
            // Category similarity
            if (isset($weights['categories']) && $weights['categories'] > 0 && isset($reference_terms['product_cat']) && isset($product_terms['product_cat'])) {
                $common_categories = array_intersect($reference_terms['product_cat'], $product_terms['product_cat']);
                $category_score = count($common_categories) / max(count($reference_terms['product_cat']), count($product_terms['product_cat']), 1);
                $score += $category_score * $weights['categories'];
            }
            
            // Tag similarity
            if (isset($weights['tags']) && $weights['tags'] > 0 && isset($reference_terms['product_tag']) && isset($product_terms['product_tag'])) {
                $common_tags = array_intersect($reference_terms['product_tag'], $product_terms['product_tag']);
                $tag_score = count($common_tags) / max(count($reference_terms['product_tag']), count($product_terms['product_tag']), 1);
                $score += $tag_score * $weights['tags'];
            }
            
            // Only include if score meets threshold
            if ($score >= $threshold) {
                $scores[$product->ID] = $score;
            }
        }
        
        // Sort by score (highest first) and limit results
        arsort($scores);
        return array_slice($scores, 0, $limit, true);
    }

    /**
     * Calculate text similarity (simple word-based)
     */
    private function calculate_text_similarity($text1, $text2) {
        // Clean and tokenize text
        $words1 = $this->tokenize_text($text1);
        $words2 = $this->tokenize_text($text2);
        
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        // Calculate intersection
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        // Jaccard similarity
        return count($intersection) / count($union);
    }

    /**
     * Tokenize text into words
     */
    private function tokenize_text($text) {
        // Remove HTML tags and special characters
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Split into words and filter common words
        $words = preg_split('/\s+/', $text);
        $stop_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'a', 'an');
        
        $words = array_diff($words, $stop_words);
        $words = array_filter($words, function($word) {
            return strlen($word) > 2;
        });
        
        return array_values($words);
    }

    /**
     * Get product terms organized by taxonomy
     */
    private function get_product_terms($product_id) {
        $terms = array();
        
        // Get WooCommerce product taxonomies
        $taxonomies = array('product_cat', 'product_tag');
        
        foreach ($taxonomies as $taxonomy) {
            $product_terms = get_the_terms($product_id, $taxonomy);
            if (!is_wp_error($product_terms) && !empty($product_terms)) {
                $terms[$taxonomy] = wp_list_pluck($product_terms, 'term_id');
            } else {
                $terms[$taxonomy] = array();
            }
        }
        
        return $terms;
    }

    /**
     * Get related products for a product
     */
    public function get_related_products($product_id, $limit = null) {
        global $wpdb;
        
        if ($limit === null) {
            $limit = $this->get_option('limit');
        }
        
        $related = $wpdb->get_results($wpdb->prepare("
            SELECT related_id, score 
            FROM {$this->cache_table} 
            WHERE reference_id = %d 
            ORDER BY score DESC, date DESC
            LIMIT %d
        ", $product_id, $limit));
        
        $result = array();
        foreach ($related as $item) {
            $result[$item->related_id] = $item->score;
        }
        
        return $result;
    }

    /**
     * Check if product has cached relations
     */
    public function is_cached($product_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$this->cache_table} 
            WHERE reference_id = %d
        ", $product_id));
        
        return $count > 0;
    }

    /**
     * Get cache statistics
     */
    public function get_stats() {
        global $wpdb;
        
        try {
            $total_products = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = 'product' AND post_status = 'publish'
            ");
            
            if ($total_products === false) {
                $total_products = 0;
            }
            
            $cached_products = $wpdb->get_var("
                SELECT COUNT(DISTINCT reference_id) 
                FROM {$this->cache_table}
            ");
            
            if ($cached_products === false) {
                $cached_products = 0;
            }
            
            $total_relations = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$this->cache_table}
            ");
            
            if ($total_relations === false) {
                $total_relations = 0;
            }
            
            $avg_score = $wpdb->get_var("
                SELECT AVG(score) 
                FROM {$this->cache_table}
            ");
            
            if ($avg_score === false) {
                $avg_score = 0;
            }
            
            return array(
                'total_products' => (int) $total_products,
                'cached_products' => (int) $cached_products,
                'total_relations' => (int) $total_relations,
                'cache_percentage' => $total_products > 0 ? round(($cached_products / $total_products) * 100, 2) : 0,
                'avg_relations' => $cached_products > 0 ? round($total_relations / $cached_products, 2) : 0,
                'avg_score' => round($avg_score, 2)
            );
            
        } catch (Exception $e) {
            error_log("WRP YARPP Cache: Error getting stats: " . $e->getMessage());
            return array(
                'total_products' => 0,
                'cached_products' => 0,
                'total_relations' => 0,
                'cache_percentage' => 0,
                'avg_relations' => 0,
                'avg_score' => 0
            );
        }
    }

    /**
     * Get cache table status
     */
    public function get_table_status() {
        global $wpdb;
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->cache_table}'") == $this->cache_table;
        
        if (!$table_exists) {
            return 'missing';
        }
        
        $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->cache_table}");
        
        if ($row_count == 0) {
            return 'empty';
        }
        
        return 'populated';
    }

    /**
     * Invalidate cache for a specific product
     */
    public function invalidate_product($product_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->cache_table,
            array('reference_id' => $product_id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Rebuild cache for a specific product
     */
    public function rebuild_product($product_id) {
        // Remove existing cache for this product
        $this->invalidate_product($product_id);
        
        // Find and cache new related products
        $relations = $this->find_related_products($product_id);
        
        if (!empty($relations)) {
            global $wpdb;
            
            foreach ($relations as $related_id => $score) {
                $wpdb->insert(
                    $this->cache_table,
                    array(
                        'reference_id' => $product_id,
                        'related_id' => $related_id,
                        'score' => $score,
                        'date' => current_time('mysql')
                    ),
                    array('%d', '%d', '%f', '%s')
                );
            }
        }
        
        return count($relations);
    }
}
?>