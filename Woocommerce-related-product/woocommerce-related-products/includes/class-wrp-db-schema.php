<?php
/**
 * Database Schema class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_DB_Schema {

    /**
     * Cache table name
     */
    const CACHE_TABLE = 'wrp_related_cache';

    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::CACHE_TABLE;

        // First, drop the table if it exists to ensure clean creation
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

        $sql = "CREATE TABLE $table_name (
            reference_id bigint(20) NOT NULL,
            related_id bigint(20) NOT NULL,
            score float NOT NULL DEFAULT 0,
            date datetime NOT NULL,
            PRIMARY KEY  (reference_id, related_id),
            KEY score (score),
            KEY related_id (related_id),
            KEY date (date)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $result = dbDelta( $sql );
        
        // Log the result for debugging
        error_log("WRP DB Schema: Table creation result: " . print_r($result, true));
        
        // Verify table was created
        $table_exists = $this->cache_table_exists();
        error_log("WRP DB Schema: Table exists after creation: " . ($table_exists ? 'Yes' : 'No'));
        
        if (!$table_exists) {
            // Try direct creation
            $wpdb->query($sql);
            $table_exists = $this->cache_table_exists();
            error_log("WRP DB Schema: Table exists after direct creation: " . ($table_exists ? 'Yes' : 'No'));
        }

        // Check if fulltext indexes exist for product title and description
        $this->ensure_fulltext_indexes();
    }

    /**
     * Drop database tables
     */
    public function drop_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    }

    /**
     * Check if cache table exists
     *
     * @return bool
     */
    public function cache_table_exists() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        return $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;
    }

    /**
     * Ensure fulltext indexes exist for product search
     */
    private function ensure_fulltext_indexes() {
        global $wpdb;

        // Check if posts table has fulltext index on post_title
        $title_index = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->posts} WHERE Column_name = 'post_title' AND Index_type = 'FULLTEXT'" );
        
        if ( ! $title_index ) {
            // Try to add fulltext index for title
            $wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT(post_title)" );
        }

        // Check if posts table has fulltext index on post_content
        $content_index = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->posts} WHERE Column_name = 'post_content' AND Index_type = 'FULLTEXT'" );
        
        if ( ! $content_index ) {
            // Try to add fulltext index for content
            $wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT(post_content)" );
        }

        // Check if posts table has fulltext index on post_excerpt
        $excerpt_index = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->posts} WHERE Column_name = 'post_excerpt' AND Index_type = 'FULLTEXT'" );
        
        if ( ! $excerpt_index ) {
            // Try to add fulltext index for excerpt
            $wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT(post_excerpt)" );
        }
    }

    /**
     * Get database engine type
     *
     * @return string|null
     */
    public function get_database_engine() {
        global $wpdb;

        $engine = $wpdb->get_var( "SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->posts}'" );
        return $engine ? $engine : null;
    }

    /**
     * Check if database supports fulltext indexes
     *
     * @return bool
     */
    public function supports_fulltext() {
        $engine = $this->get_database_engine();
        
        // MyISAM and InnoDB (MySQL 5.6+) support fulltext
        if ( in_array( $engine, array( 'MyISAM', 'InnoDB' ) ) ) {
            return true;
        }

        return false;
    }

    /**
     * Clear cache table
     */
    public function clear_cache() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        $wpdb->query( "TRUNCATE TABLE $table_name" );
    }

    /**
     * Get cache status
     *
     * @return float
     */
    public function get_cache_status() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        
        $status = $wpdb->get_var(
            "SELECT (COUNT(p.ID) - SUM(c.related_id IS NULL)) / COUNT(p.ID)
            FROM {$wpdb->posts} AS p
            LEFT JOIN $table_name AS c ON p.ID = c.reference_id
            WHERE p.post_type = 'product' AND p.post_status = 'publish'"
        );

        return $status ? (float) $status : 0;
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

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID
                FROM {$wpdb->posts} AS p
                LEFT JOIN $table_name AS c ON p.ID = c.reference_id
                WHERE p.post_type = 'product' AND p.post_status = 'publish' AND c.related_id IS NULL
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function get_cache_stats() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::CACHE_TABLE;
        
        $total_products = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"
        );

        $cached_products = $wpdb->get_var(
            "SELECT COUNT(DISTINCT reference_id) FROM $table_name"
        );

        $total_relations = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE related_id != 0"
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
}