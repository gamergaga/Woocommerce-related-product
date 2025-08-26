<?php
/**
 * Enhanced Cache System for WooCommerce Related Products Pro
 * Uses the enhanced algorithm with improved caching strategy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Enhanced_Cache extends WRP_Cache {

    /**
     * Enhanced algorithm instance
     */
    private $enhanced_algorithm;

    /**
     * Cache table name
     */
    private $cache_table;

    /**
     * Constructor
     */
    public function __construct( $core ) {
        parent::__construct( $core );
        $this->enhanced_algorithm = new WRP_Enhanced_Algorithm();
        global $wpdb;
        $this->cache_table = $wpdb->prefix . 'wrp_enhanced_cache';
        $this->ensure_table_exists();
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
     * Create enhanced cache table
     */
    private function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->cache_table} (
            reference_id bigint(20) NOT NULL,
            related_id bigint(20) NOT NULL,
            score float NOT NULL DEFAULT 0,
            score_details text NOT NULL,
            date datetime NOT NULL,
            PRIMARY KEY  (reference_id, related_id),
            KEY score (score),
            KEY related_id (related_id),
            KEY date (date)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        error_log("WRP Enhanced Cache: Table created successfully");
    }

    /**
     * Check if cache is populated for a product
     */
    public function is_cached( $reference_id ) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$this->cache_table} 
            WHERE reference_id = %d
        ", $reference_id));
        
        return $count > 0;
    }

    /**
     * Get cached related product IDs
     */
    protected function get_cached_related_ids( $reference_id, $args ) {
        global $wpdb;
        
        $limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 12;
        $threshold = isset( $args['threshold'] ) ? floatval( $args['threshold'] ) : 1.5;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT related_id, score 
            FROM {$this->cache_table} 
            WHERE reference_id = %d AND score >= %f
            ORDER BY score DESC, date DESC
            LIMIT %d
        ", $reference_id, $threshold, $limit));
        
        $related_ids = array();
        foreach ( $results as $result ) {
            $related_ids[] = $result->related_id;
        }
        
        return $related_ids;
    }

    /**
     * Store related products in cache
     */
    protected function store_related_products( $reference_id, $related_data ) {
        global $wpdb;
        
        // Clear existing cache for this product
        $this->clear( $reference_id );
        
        // Store new related products
        foreach ( $related_data as $related_id => $score ) {
            $score_details = json_encode(array(
                'score' => $score,
                'timestamp' => current_time('timestamp'),
                'algorithm' => 'enhanced_v1'
            ));
            
            $wpdb->insert(
                $this->cache_table,
                array(
                    'reference_id' => $reference_id,
                    'related_id' => $related_id,
                    'score' => $score,
                    'score_details' => $score_details,
                    'date' => current_time('mysql')
                ),
                array('%d', '%d', '%f', '%s', '%s')
            );
        }
        
        error_log("WRP Enhanced Cache: Stored " . count($related_data) . " related products for product ID: $reference_id");
    }

    /**
     * Clear cache for product(s)
     */
    public function clear( $product_ids ) {
        global $wpdb;
        
        if ( is_array( $product_ids ) ) {
            $product_ids_array = array_map( 'intval', $product_ids );
            $placeholders = implode( ',', array_fill( 0, count( $product_ids_array ), '%d' ) );
            
            $result = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$this->cache_table} WHERE reference_id IN ($placeholders)",
                $product_ids_array
            ));
        } else {
            $result = $wpdb->delete(
                $this->cache_table,
                array( 'reference_id' => intval( $product_ids ) ),
                array( '%d' )
            );
        }
        
        return $result !== false;
    }

    /**
     * Clear all cache
     */
    public function clear_all() {
        global $wpdb;
        
        $result = $wpdb->query("TRUNCATE TABLE {$this->cache_table}");
        error_log("WRP Enhanced Cache: All cache cleared - " . ($result !== false ? 'Success' : 'Failed'));
        
        return $result !== false;
    }

    /**
     * Compute related products using enhanced algorithm
     */
    protected function compute_related_products( $reference_id, $args = array() ) {
        error_log("WRP Enhanced Cache: Computing related products for product ID: $reference_id");
        
        // Use enhanced algorithm
        $config = $this->build_algorithm_config( $args );
        $related_products = $this->enhanced_algorithm->get_related_products( $reference_id, $config );
        
        error_log("WRP Enhanced Cache: Found " . count($related_products) . " related products for product ID: $reference_id");
        
        return $related_products;
    }

    /**
     * Build algorithm configuration from arguments
     */
    private function build_algorithm_config( $args ) {
        $config = array(
            'threshold' => isset( $args['threshold'] ) ? floatval( $args['threshold'] ) : 1.5,
            'limit' => isset( $args['limit'] ) ? intval( $args['limit'] ) : 12,
            'weights' => array(
                'title' => isset( $args['weight']['title'] ) ? floatval( $args['weight']['title'] ) : 3.0,
                'content' => isset( $args['weight']['content'] ) ? floatval( $args['weight']['content'] ) : 2.0,
                'categories' => isset( $args['weight']['categories'] ) ? floatval( $args['weight']['categories'] ) : 4.0,
                'tags' => isset( $args['weight']['tags'] ) ? floatval( $args['weight']['tags'] ) : 3.0,
                'attributes' => isset( $args['weight']['attributes'] ) ? floatval( $args['weight']['attributes'] ) : 2.0,
                'price_range' => isset( $args['weight']['price_range'] ) ? floatval( $args['weight']['price_range'] ) : 1.0,
                'brand' => isset( $args['weight']['brand'] ) ? floatval( $args['weight']['brand'] ) : 2.5
            ),
            'text_analysis' => array(
                'min_word_length' => 3,
                'use_stop_words' => true,
                'use_stemming' => true,
                'fuzzy_matching' => true
            ),
            'scoring' => array(
                'cross_reference' => true,
                'temporal_boost' => true,
                'popularity_boost' => true,
                'category_boost' => true
            ),
            'require_tax' => isset( $args['require_tax'] ) ? $args['require_tax'] : array( 'product_cat' => 1 )
        );
        
        return $config;
    }

    /**
     * Build cache for all products using enhanced algorithm
     */
    public function build_cache( $batch_size = 50 ) {
        global $wpdb;
        
        error_log("WRP Enhanced Cache: Starting cache build with batch size: $batch_size");
        
        // Clear existing cache
        $this->clear_all();
        
        // Get all published products
        $products = $wpdb->get_results("
            SELECT ID, post_title 
            FROM {$wpdb->posts} 
            WHERE post_type = 'product' AND post_status = 'publish'
            ORDER BY ID ASC
        ");
        
        if ($products === false) {
            error_log("WRP Enhanced Cache: Failed to query products");
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
        
        error_log("WRP Enhanced Cache: Found $total_products products to process");
        
        // Process products in batches
        foreach ($products as $product) {
            try {
                $config = $this->build_algorithm_config( $this->core->get_default_options() );
                $relations = $this->enhanced_algorithm->get_related_products( $product->ID, $config );
                
                if (!empty($relations)) {
                    foreach ($relations as $related_id => $score) {
                        $score_details = json_encode(array(
                            'score' => $score,
                            'timestamp' => current_time('timestamp'),
                            'algorithm' => 'enhanced_v1'
                        ));
                        
                        $result = $wpdb->insert(
                            $this->cache_table,
                            array(
                                'reference_id' => $product->ID,
                                'related_id' => $related_id,
                                'score' => $score,
                                'score_details' => $score_details,
                                'date' => current_time('mysql')
                            ),
                            array('%d', '%d', '%f', '%s', '%s')
                        );
                        
                        if ($result !== false) {
                            $total_relations++;
                        }
                    }
                }
                
                $processed++;
                
                // Log progress every 10 products
                if ($processed % 10 == 0) {
                    error_log("WRP Enhanced Cache: Processed $processed/$total_products products, $total_relations relations");
                }
                
            } catch (Exception $e) {
                error_log("WRP Enhanced Cache: Error processing product {$product->ID}: " . $e->getMessage());
                $errors++;
            }
        }
        
        error_log("WRP Enhanced Cache: Cache build completed. Processed $processed products, created $total_relations relations, $errors errors");
        
        return array(
            'processed' => $processed,
            'total_relations' => $total_relations,
            'errors' => $errors,
            'success' => true,
            'message' => sprintf(
                'Enhanced cache built successfully! Processed %d products with %d relations (%d errors).',
                $processed,
                $total_relations,
                $errors
            )
        );
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
            
            $high_score_relations = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$this->cache_table}
                WHERE score >= 3.0
            ");
            
            if ($high_score_relations === false) {
                $high_score_relations = 0;
            }
            
            return array(
                'total_products' => (int) $total_products,
                'cached_products' => (int) $cached_products,
                'total_relations' => (int) $total_relations,
                'cache_percentage' => $total_products > 0 ? round(($cached_products / $total_products) * 100, 2) : 0,
                'avg_relations' => $cached_products > 0 ? round($total_relations / $cached_products, 2) : 0,
                'avg_score' => round($avg_score, 2),
                'high_score_relations' => (int) $high_score_relations,
                'high_score_percentage' => $total_relations > 0 ? round(($high_score_relations / $total_relations) * 100, 2) : 0
            );
            
        } catch (Exception $e) {
            error_log("WRP Enhanced Cache: Error getting stats: " . $e->getMessage());
            return array(
                'total_products' => 0,
                'cached_products' => 0,
                'total_relations' => 0,
                'cache_percentage' => 0,
                'avg_relations' => 0,
                'avg_score' => 0,
                'high_score_relations' => 0,
                'high_score_percentage' => 0
            );
        }
    }

    /**
     * Get score between two products
     */
    public function get_score( $reference_id, $related_id ) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare("
            SELECT score 
            FROM {$this->cache_table} 
            WHERE reference_id = %d AND related_id = %d
        ", $reference_id, $related_id));
        
        return $result !== null ? floatval( $result ) : 0;
    }

    /**
     * Get products that reference this product as related
     *
     * @param int $related_id Related product ID.
     * @return array
     */
    public function get_related_references( $related_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT reference_id, score 
            FROM {$this->cache_table} 
            WHERE related_id = %d
            ORDER BY score DESC, date DESC
        ", $related_id));
        
        $references = array();
        foreach ( $results as $result ) {
            $references[ $result->reference_id ] = floatval( $result->score );
        }
        
        return $references;
    }

    /**
     * Rebuild cache for specific product
     */
    public function rebuild_product( $product_id ) {
        global $wpdb;
        
        // Clear existing cache for this product
        $this->clear( $product_id );
        
        // Build new cache
        $config = $this->build_algorithm_config( $this->core->get_default_options() );
        $relations = $this->enhanced_algorithm->get_related_products( $product_id, $config );
        
        $count = 0;
        foreach ($relations as $related_id => $score) {
            $score_details = json_encode(array(
                'score' => $score,
                'timestamp' => current_time('timestamp'),
                'algorithm' => 'enhanced_v1'
            ));
            
            $result = $wpdb->insert(
                $this->cache_table,
                array(
                    'reference_id' => $product_id,
                    'related_id' => $related_id,
                    'score' => $score,
                    'score_details' => $score_details,
                    'date' => current_time('mysql')
                ),
                array('%d', '%d', '%f', '%s', '%s')
            );
            
            if ($result !== false) {
                $count++;
            }
        }
        
        return $count;
    }
}
?>