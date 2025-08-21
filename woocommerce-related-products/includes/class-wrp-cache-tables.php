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
            return $result;
        }

        $max_score = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(score) FROM {$this->cache_table} WHERE reference_id = %d",
                $reference_id
            )
        );

        $is_cached = $max_score !== null && $max_score > 0;
        
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

        // First, clear existing cache for this product
        $wpdb->delete(
            $this->cache_table,
            array( 'reference_id' => $reference_id ),
            array( '%d' )
        );

        if ( empty( $related_data ) ) {
            // Store empty cache entry to prevent repeated processing
            $wpdb->insert(
                $this->cache_table,
                array(
                    'reference_id' => $reference_id,
                    'related_id' => 0,
                    'score' => 0,
                    'date' => current_time( 'mysql' ),
                ),
                array( '%d', '%d', '%f', '%s' )
            );
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

            // Bulk insert with error handling
            try {
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$this->cache_table} (reference_id, related_id, score, date) 
                        VALUES " . implode( ', ', $placeholders ) . "
                        ON DUPLICATE KEY UPDATE score = VALUES(score), date = VALUES(date)",
                        $values
                    )
                );
            } catch ( Exception $e ) {
                // If bulk insert fails, try individual inserts
                foreach ( $related_data as $related_id => $score ) {
                    $wpdb->insert(
                        $this->cache_table,
                        array(
                            'reference_id' => $reference_id,
                            'related_id' => $related_id,
                            'score' => $score,
                            'date' => $now,
                        ),
                        array( '%d', '%d', '%f', '%s' )
                    );
                }
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