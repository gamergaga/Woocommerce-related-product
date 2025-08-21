<?php
/**
 * Base Cache class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class WRP_Cache {

    /**
     * Core instance
     *
     * @var WRP_Core
     */
    protected $core;

    /**
     * Whether we're in "WRP time"
     *
     * @var bool
     */
    protected $wrp_time = false;

    /**
     * Last SQL query
     *
     * @var string
     */
    public $last_sql;

    /**
     * Constructor
     *
     * @param WRP_Core $core Core instance.
     */
    public function __construct( $core ) {
        $this->core = $core;
    }

    /**
     * Get related product IDs for a reference product
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     * @return array
     */
    public function get_related_ids( $reference_id, $args = array() ) {
        // Ensure cache is populated
        $this->enforce_cache( $reference_id, $args );

        // Get related IDs from cache
        return $this->get_cached_related_ids( $reference_id, $args );
    }

    /**
     * Ensure cache is populated for a product
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     */
    protected function enforce_cache( $reference_id, $args = array() ) {
        // Check if cache is populated
        if ( ! $this->is_cached( $reference_id ) ) {
            $this->update_cache( $reference_id, $args );
        }
    }

    /**
     * Check if cache is populated for a product
     *
     * @param int $reference_id Reference product ID.
     * @return bool
     */
    abstract public function is_cached( $reference_id );

    /**
     * Update cache for a product
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     */
    protected function update_cache( $reference_id, $args = array() ) {
        // Clear existing cache
        $this->clear( $reference_id );

        // Get related products using algorithm
        $related_data = $this->compute_related_products( $reference_id, $args );

        // Store in cache
        $this->store_related_products( $reference_id, $related_data );
    }

    /**
     * Compute related products using algorithm
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     * @return array Array of related_id => score
     */
    protected function compute_related_products( $reference_id, $args = array() ) {
        global $wpdb;

        $reference_product = wc_get_product( $reference_id );
        if ( ! $reference_product ) {
            return array();
        }

        // Parse arguments
        $args = wp_parse_args( $args, $this->core->default_options );

        // Build SQL query
        $sql = $this->build_related_query( $reference_id, $args );

        $this->last_sql = $sql;

        // Execute query
        $results = $wpdb->get_results( $sql );

        if ( ! $results ) {
            return array();
        }

        // Convert to related_id => score array
        $related_data = array();
        foreach ( $results as $result ) {
            $related_data[ $result->ID ] = (float) $result->score;
        }

        return $related_data;
    }

    /**
     * Build SQL query for finding related products
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     * @return string
     */
    protected function build_related_query( $reference_id, $args ) {
        global $wpdb;

        $reference_product = wc_get_product( $reference_id );
        if ( ! $reference_product ) {
            return '';
        }

        // Get enhanced keywords
        $keywords = $this->core->get_product_keywords( $reference_id );

        // Extract arguments
        extract( $args );

        // Build enhanced SELECT clause with better scoring
        $sql = $wpdb->prepare(
            "SELECT p.ID, ROUND(0",
            $reference_id
        );

        // Enhanced title matching with partial word support
        if ( isset( $weight['title'] ) && $weight['title'] > 0 ) {
            $title_keywords = $keywords['title'];
            if ( ! empty( $title_keywords ) ) {
                $sql .= $wpdb->prepare(
                    " + (CASE 
                        WHEN p.post_title LIKE %s THEN %d * 1.5
                        WHEN p.post_title LIKE %s THEN %d * 1.2
                        WHEN MATCH (p.post_title) AGAINST (%s IN BOOLEAN MODE) THEN %d
                        ELSE 0
                      END)",
                    '%' . $wpdb->esc_like( $title_keywords ) . '%',
                    $weight['title'],
                    '%' . str_replace( ' ', '%', $wpdb->esc_like( $title_keywords ) ) . '%',
                    $weight['title'],
                    '+' . str_replace( ' ', ' +', $title_keywords ),
                    $weight['title']
                );
            }
        }

        // Enhanced description matching
        if ( isset( $weight['description'] ) && $weight['description'] > 0 ) {
            $desc_keywords = $keywords['description'];
            if ( ! empty( $desc_keywords ) ) {
                $sql .= $wpdb->prepare(
                    " + (CASE 
                        WHEN p.post_content LIKE %s THEN %d * 1.3
                        WHEN MATCH (p.post_content) AGAINST (%s IN BOOLEAN MODE) THEN %d
                        ELSE 0
                      END)",
                    '%' . $wpdb->esc_like( $desc_keywords ) . '%',
                    $weight['description'],
                    '+' . str_replace( ' ', ' +', $desc_keywords ),
                    $weight['description']
                );
            }
        }

        // Enhanced short description matching
        if ( isset( $weight['short_description'] ) && $weight['short_description'] > 0 ) {
            $short_desc_keywords = $keywords['short_description'];
            if ( ! empty( $short_desc_keywords ) ) {
                $sql .= $wpdb->prepare(
                    " + (CASE 
                        WHEN p.post_excerpt LIKE %s THEN %d * 1.4
                        WHEN MATCH (p.post_excerpt) AGAINST (%s IN BOOLEAN MODE) THEN %d
                        ELSE 0
                      END)",
                    '%' . $wpdb->esc_like( $short_desc_keywords ) . '%',
                    $weight['short_description'],
                    '+' . str_replace( ' ', ' +', $short_desc_keywords ),
                    $weight['short_description']
                );
            }
        }

        // Enhanced taxonomy matching with hierarchical support
        if ( isset( $weight['tax'] ) && is_array( $weight['tax'] ) ) {
            foreach ( $weight['tax'] as $taxonomy => $tax_weight ) {
                if ( $tax_weight > 0 ) {
                    $sql .= ' + ' . $this->get_enhanced_taxonomy_criteria( $reference_id, $taxonomy ) . ' * ' . intval( $tax_weight );
                }
            }
        }

        // Add price range similarity bonus
        $sql .= $this->get_price_range_criteria( $reference_id, $weight );

        $sql .= ',4) AS score';

        // Build FROM clause with enhanced joins
        $sql .= " FROM {$wpdb->posts} p";

        // Add taxonomy joins if needed
        if ( ! empty( $exclude ) || ! empty( $require_tax ) || ( isset( $weight['tax'] ) && ! empty( $weight['tax'] ) ) ) {
            $sql .= " LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)";
            $sql .= " LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)";
            $sql .= " LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)";
        }

        // Build WHERE clause with enhanced conditions
        $sql .= " WHERE p.post_type = 'product' AND p.post_status = 'publish'";

        // Exclude reference product
        $sql .= $wpdb->prepare( " AND p.ID != %d", $reference_id );

        // Enhanced stock filtering
        if ( ! $include_out_of_stock ) {
            $sql .= " AND p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_stock_status' AND meta_value = 'instock'
            )";
        }

        // Recent only filter
        if ( ! empty( $recent_only ) && ! empty( $recent_period ) ) {
            $sql .= " AND p.post_date > DATE_SUB(NOW(), INTERVAL " . intval( $recent_period ) . ")";
        }

        // Exclude specific terms with enhanced filtering
        if ( ! empty( $exclude ) ) {
            $exclude_ids = wp_parse_id_list( $exclude );
            if ( ! empty( $exclude_ids ) ) {
                $sql .= " AND tt.term_id NOT IN (" . implode( ',', $exclude_ids ) . ")";
            }
        }

        // Build GROUP BY clause
        $sql .= " GROUP BY p.ID";

        // Build HAVING clause with enhanced threshold
        $sql .= $wpdb->prepare( " HAVING score >= %f", max( $threshold, 0.1 ) );

        // Add enhanced taxonomy requirements
        if ( ! empty( $require_tax ) ) {
            foreach ( $require_tax as $taxonomy => $required_count ) {
                if ( $required_count > 0 ) {
                    $sql .= ' AND ' . $this->get_enhanced_taxonomy_criteria( $reference_id, $taxonomy ) . ' >= ' . intval( $required_count );
                }
            }
        }

        // Build ORDER BY with secondary sorting
        $sql .= " ORDER BY score DESC, p.post_date DESC, p.ID DESC";
        
        // Build LIMIT clause
        $sql .= $wpdb->prepare( " LIMIT %d", intval( $limit ) );

        return $sql;
    }

    /**
     * Get enhanced taxonomy criteria for SQL query
     *
     * @param int    $reference_id Reference product ID.
     * @param string $taxonomy Taxonomy name.
     * @return string
     */
    protected function get_enhanced_taxonomy_criteria( $reference_id, $taxonomy ) {
        $terms = get_the_terms( $reference_id, $taxonomy );
        
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '0';
        }

        $term_ids = wp_list_pluck( $terms, 'term_id' );
        $term_names = wp_list_pluck( $terms, 'name' );
        
        // Create enhanced criteria that considers both direct term matches and hierarchical relationships
        $criteria = '(COUNT(DISTINCT CASE WHEN tt.term_id IN (' . implode( ',', $term_ids ) . ') THEN tt.term_id END) * 2)';
        
        // Add partial name matching for better flexibility
        if ( ! empty( $term_names ) ) {
            $name_conditions = array();
            foreach ( $term_names as $term_name ) {
                $name_conditions[] = "t.name LIKE '%" . esc_sql( $term_name ) . "%'";
            }
            $criteria .= ' + (CASE WHEN ' . implode( ' OR ', $name_conditions ) . ' THEN 1 ELSE 0 END)';
        }
        
        return $criteria;
    }

    /**
     * Get price range criteria for SQL query
     *
     * @param int   $reference_id Reference product ID.
     * @param array $weights Weight array.
     * @return string
     */
    protected function get_price_range_criteria( $reference_id, $weights ) {
        $reference_product = wc_get_product( $reference_id );
        if ( ! $reference_product ) {
            return ' + 0';
        }

        $reference_price = $reference_product->get_price();
        if ( ! $reference_price ) {
            return ' + 0';
        }

        // Calculate price range (±20% of reference price)
        $min_price = $reference_price * 0.8;
        $max_price = $reference_price * 1.2;

        global $wpdb;
        
        return " + (CASE 
            WHEN p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_price' 
                AND meta_value BETWEEN " . floatval( $min_price ) . " AND " . floatval( $max_price ) . "
            ) THEN 0.5
            ELSE 0
        END)";
    }

    /**
     * Get taxonomy criteria for SQL query
     *
     * @param int    $reference_id Reference product ID.
     * @param string $taxonomy Taxonomy name.
     * @return string
     */
    protected function get_taxonomy_criteria( $reference_id, $taxonomy ) {
        $terms = get_the_terms( $reference_id, $taxonomy );
        
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '0';
        }

        $term_ids = wp_list_pluck( $terms, 'term_id' );
        return 'COUNT(DISTINCT CASE WHEN tt.term_id IN (' . implode( ',', $term_ids ) . ') THEN tt.term_id END)';
    }

    /**
     * Get cached related product IDs
     *
     * @param int   $reference_id Reference product ID.
     * @param array $args Arguments.
     * @return array
     */
    abstract protected function get_cached_related_ids( $reference_id, $args );

    /**
     * Store related products in cache
     *
     * @param int   $reference_id Reference product ID.
     * @param array $related_data Related data (related_id => score).
     */
    abstract protected function store_related_products( $reference_id, $related_data );

    /**
     * Clear cache for product(s)
     *
     * @param int|array $product_ids Product ID(s).
     */
    abstract public function clear( $product_ids );

    /**
     * Get related score
     *
     * @param int $reference_id Reference product ID.
     * @param int $related_id Related product ID.
     * @return float|false
     */
    abstract public function get_score( $reference_id, $related_id );

    /**
     * Get products that reference this product as related
     *
     * @param int $related_id Related product ID.
     * @return array
     */
    abstract public function get_related_references( $related_id );
}