<?php
/**
 * Tables Cache implementation for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Cache_Tables extends WRP_Cache {

    /**
     * Cache table name
     */
    private $cache_table;

    /**
     * Constructor
     *
     * @param WRP_Core $core Core instance.
     */
    public function __construct( $core ) {
        parent::__construct( $core );
        global $wpdb;
        $this->cache_table = $wpdb->prefix . 'wrp_related_cache';
    }

    /**
     * Check if cache is populated for a product
     *
     * @param int $reference_id Reference product ID.
     * @return bool
     */
    public function is_cached( $reference_id ) {
        global $wpdb;

        $result = wp_cache_get( "wrp_is_cached_{$reference_id}", 'wrp' );
        if ( false !== $result ) {
            error_log("WRP Cache Tables: Product ID $reference_id cache status from object cache: " . ($result ? 'Cached' : 'Not cached'));
            return $result;
        }

        // Check if there's any cache entry for this product (including empty cache entries)
        $cache_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->cache_table} WHERE reference_id = %d",
                $reference_id
            )
        );

        $is_cached = $cache_count > 0;
        
        error_log("WRP Cache Tables: Product ID $reference_id cache status from DB: " . ($is_cached ? 'Cached' : 'Not cached') . " (cache_count: $cache_count)");
        
        wp_cache_set( "wrp_is_cached_{$reference_id}", $is_cached, 'wrp' );

        return $is_cached;
    }

    /**
     * Get cached related product IDs
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     * @return array
     */
    protected function get_cached_related_ids( $reference_id, $args ) {
        global $wpdb;

        $limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 4;
        $threshold = isset( $args['threshold'] ) ? floatval( $args['threshold'] ) : 1;

        $cache_key = "wrp_related_ids_{$reference_id}_{$limit}_{$threshold}";
        $result = wp_cache_get( $cache_key, 'wrp' );

        if ( false !== $result ) {
            error_log("WRP Cache Tables: Retrieved related IDs from object cache for product ID $reference_id: " . count($result) . " products");
            return $result;
        }

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT related_id FROM {$this->cache_table} 
                WHERE reference_id = %d AND score >= %f AND related_id != 0
                ORDER BY score DESC, related_id DESC
                LIMIT %d",
                $reference_id,
                $threshold,
                $limit
            )
        );

        error_log("WRP Cache Tables: Retrieved related IDs from DB for product ID $reference_id: " . count($ids) . " products");

        wp_cache_set( $cache_key, $ids, 'wrp', 3600 ); // Cache for 1 hour

        return $ids;
    }

    /**
     * Store related products in cache
     *
     * @param int   $reference_id Reference product ID.
     * @param array $related_data Related data (related_id => score).
     */
    protected function store_related_products( $reference_id, $related_data ) {
        global $wpdb;

        error_log("WRP Cache Tables: Storing related products for product ID: $reference_id, count: " . count($related_data));

        // First, clear existing cache for this product
        $cleared = $wpdb->delete(
            $this->cache_table,
            array( 'reference_id' => $reference_id ),
            array( '%d' )
        );
        error_log("WRP Cache Tables: Cleared existing cache entries: $cleared");

        if ( empty( $related_data ) ) {
            // Store empty cache entry to prevent repeated processing
            $result = $wpdb->insert(
                $this->cache_table,
                array(
                    'reference_id' => $reference_id,
                    'related_id' => 0,
                    'score' => 0,
                    'date' => current_time( 'mysql' ),
                ),
                array( '%d', '%d', '%f', '%s' )
            );
            error_log("WRP Cache Tables: Stored empty cache entry: " . ($result ? 'Success' : 'Failed'));
        } else {
            // Prepare values for bulk insert
            $values = array();
            $placeholders = array();
            $now = current_time( 'mysql' );

            foreach ( $related_data as $related_id => $score ) {
                $values[] = $reference_id;
                $values[] = $related_id;
                $values[] = $score;
                $values[] = $now;
                $placeholders[] = '( %d, %d, %f, %s )';
            }

            error_log("WRP Cache Tables: Preparing bulk insert with " . count($related_data) . " entries");

            // Bulk insert with error handling
            try {
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$this->cache_table} (reference_id, related_id, score, date) 
                        VALUES " . implode( ', ', $placeholders ) . "
                        ON DUPLICATE KEY UPDATE score = VALUES(score), date = VALUES(date)",
                        $values
                    )
                );
                error_log("WRP Cache Tables: Bulk insert result: $result");
            } catch (Exception $e) {
                error_log("WRP Cache Tables: Bulk insert failed: " . $e->getMessage() . ", trying individual inserts");
                // If bulk insert fails, try individual inserts
                $success_count = 0;
                foreach ( $related_data as $related_id => $score ) {
                    $result = $wpdb->insert(
                        $this->cache_table,
                        array(
                            'reference_id' => $reference_id,
                            'related_id' => $related_id,
                            'score' => $score,
                            'date' => $now,
                        ),
                        array( '%d', '%d', '%f', '%s' )
                    );
                    if ($result) {
                        $success_count++;
                    }
                }
                error_log("WRP Cache Tables: Individual inserts completed: $success_count/" . count($related_data));
            }
        }

        // Clear object cache for this product
        wp_cache_delete( "wrp_is_cached_{$reference_id}", 'wrp' );
        
        // Clear related IDs cache for all possible limits and thresholds
        $possible_limits = array( 4, 6, 8, 12 );
        $possible_thresholds = array( 1, 2, 3, 5 );
        
        foreach ( $possible_limits as $limit ) {
            foreach ( $possible_thresholds as $threshold ) {
                wp_cache_delete( "wrp_related_ids_{$reference_id}_{$limit}_{$threshold}", 'wrp' );
            }
        }
        
        error_log("WRP Cache Tables: Cache storage completed for product ID: $reference_id");
    }

    /**
     * Clear cache for product(s)
     *
     * @param int|array $product_ids Product ID(s).
     */
    public function clear( $product_ids ) {
        global $wpdb;

        $product_ids = wp_parse_id_list( $product_ids );
        if ( empty( $product_ids ) ) {
            return;
        }

        $ids_format = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );

        // Delete from cache table
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->cache_table} 
                WHERE reference_id IN ($ids_format) OR related_id IN ($ids_format)",
                array_merge( $product_ids, $product_ids )
            )
        );

        // Clear object cache
        foreach ( $product_ids as $id ) {
            wp_cache_delete( "wrp_is_cached_{$id}", 'wrp' );
            
            // Clear related IDs cache for all possible limits and thresholds
            wp_cache_delete( "wrp_related_ids_{$id}_4_1", 'wrp' );
            wp_cache_delete( "wrp_related_ids_{$id}_6_1", 'wrp' );
            wp_cache_delete( "wrp_related_ids_{$id}_8_1", 'wrp' );
            wp_cache_delete( "wrp_related_ids_{$id}_12_1", 'wrp' );
        }
    }

    /**
     * Get related score
     *
     * @param int $reference_id Reference product ID.
     * @param int $related_id Related product ID.
     * @return float|false
     */
    public function get_score( $reference_id, $related_id ) {
        global $wpdb;

        $score = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT score FROM {$this->cache_table} 
                WHERE reference_id = %d AND related_id = %d",
                $reference_id,
                $related_id
            )
        );

        return $score !== null ? (float) $score : false;
    }

    /**
     * Get products that reference this product as related
     *
     * @param int $related_id Related product ID.
     * @return array
     */
    public function get_related_references( $related_id ) {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT reference_id FROM {$this->cache_table} 
                WHERE related_id = %d AND reference_id != 0",
                $related_id
            )
        );
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function get_stats() {
        global $wpdb;

        $total_products = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'product' AND post_status = 'publish'"
        );

        $cached_products = $wpdb->get_var(
            "SELECT COUNT(DISTINCT reference_id) FROM {$this->cache_table} 
            WHERE reference_id != 0"
        );

        $total_relations = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->cache_table} 
            WHERE reference_id != 0 AND related_id != 0"
        );

        $avg_relations = $total_relations > 0 && $cached_products > 0 ? $total_relations / $cached_products : 0;

        return array(
            'total_products' => (int) $total_products,
            'cached_products' => (int) $cached_products,
            'total_relations' => (int) $total_relations,
            'avg_relations' => round( $avg_relations, 2 ),
            'cache_percentage' => $total_products > 0 ? round( ( $cached_products / $total_products ) * 100, 2 ) : 0,
        );
    }

    /**
     * Get uncached products
     *
     * @param int $limit Number of products to return.
     * @param int $offset Offset.
     * @return array
     */
    public function get_uncached_products( $limit = 20, $offset = 0 ) {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$this->cache_table} c ON p.ID = c.reference_id
                WHERE p.post_type = 'product' AND p.post_status = 'publish' 
                AND c.reference_id IS NULL
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }

    /**
     * Clear all cache
     */
    public function clear_all() {
        global $wpdb;

        $wpdb->query( "TRUNCATE TABLE {$this->cache_table}" );
        wp_cache_flush();
    }

    /**
     * Optimize cache table
     */
    public function optimize() {
        global $wpdb;

        $wpdb->query( "OPTIMIZE TABLE {$this->cache_table}" );
    }
}