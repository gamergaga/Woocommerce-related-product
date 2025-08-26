<?php
/*
Plugin Name: WooCommerce Related Products Pro
Plugin URI: https://example.com/woocommerce-related-products-pro
Description: Display related WooCommerce products with add to cart and buy now buttons using an advanced algorithm optimized for products.
Version: 1.0.0
Author: Your Name
Author URI: https://example.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: woocommerce-related-products
Domain Path: /languages
WooCommerce requires at least: 3.0
WooCommerce tested up to: 8.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'WRP_VERSION', '1.0.0' );
define( 'WRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WRP_CACHE_TYPE', 'tables' ); // Use custom tables for caching

// Check if WooCommerce is activated
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', 'wrp_woocommerce_missing_notice' );
    return;
}

/**
 * WooCommerce missing notice
 */
function wrp_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'WooCommerce Related Products Pro requires WooCommerce to be installed and activated.', 'woocommerce-related-products' ); ?></p>
    </div>
    <?php
}

// Include required files
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-autoloader.php';
require_once WRP_PLUGIN_DIR . 'includes/wrp-functions.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-enhanced-algorithm.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-enhanced-cache.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-yarpp-cache.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-core.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-cache.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-cache-tables.php';
require_once WRP_PLUGIN_DIR . 'admin/class-wrp-admin.php';
// Note: Commenting out other admin files temporarily to avoid conflicts
// require_once WRP_PLUGIN_DIR . 'admin/class-wrp-yarpp-admin.php';
// require_once WRP_PLUGIN_DIR . 'admin/class-wrp-enhanced-admin.php';
require_once WRP_PLUGIN_DIR . 'public/class-wrp-public.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-shortcode.php';
require_once WRP_PLUGIN_DIR . 'includes/class-wrp-widget.php';

/**
 * Initialize the plugin
 */
function wrp_init_plugin() {
    if ( class_exists( 'WooCommerce' ) ) {
        // Initialize core plugin class
        $GLOBALS['wrp'] = new WRP_Core();
        
        // Initialize admin
        if ( is_admin() ) {
            new WRP_Admin();
            // Note: Commenting out other admin classes temporarily to avoid conflicts
            // new WRP_YARPP_Admin(); // Add YARPP-style admin for advanced cache operations
            // new WRP_Enhanced_Admin(); // Add enhanced algorithm admin
        }
        
        // Initialize public
        new WRP_Public();
        
        // Register shortcode
        $shortcode = new WRP_Shortcode();
        $shortcode->register();
        
        // Register widget
        add_action( 'widgets_init', function() {
            register_widget( 'WRP_Widget' );
        } );
    }
}

// Hook into WordPress 'plugins_loaded' action
add_action( 'plugins_loaded', 'wrp_init_plugin' );

// Add scheduled event handler
add_action( 'wrp_build_initial_cache', 'wrp_build_initial_cache_handler' );

// Register activation hook
register_activation_hook( __FILE__, 'wrp_activate' );

/**
 * Plugin activation
 */
function wrp_activate() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'WooCommerce Related Products Pro requires WooCommerce to be installed and activated.', 'woocommerce-related-products' ) );
    }
    
    // Create database tables
    require_once WRP_PLUGIN_DIR . 'includes/class-wrp-db-schema.php';
    $db_schema = new WRP_DB_Schema();
    $db_schema->create_tables();
    
    // Verify table creation
    if ( ! $db_schema->cache_table_exists() ) {
        // Try to create table manually if dbDelta failed
        global $wpdb;
        $table_name = $wpdb->prefix . 'wrp_related_cache';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            reference_id bigint(20) NOT NULL,
            related_id bigint(20) NOT NULL,
            score float NOT NULL,
            date datetime NOT NULL,
            PRIMARY KEY  (reference_id, related_id),
            KEY score (score),
            KEY related_id (related_id),
            KEY date (date)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
    
    // Set default options
    $default_options = array(
        'version' => WRP_VERSION,
        'threshold' => 1.5, // Lower threshold for more results like YARPP
        'limit' => 12, // Show more products like YARPP
        'excerpt_length' => 10,
        'show_price' => true,
        'show_rating' => true,
        'show_add_to_cart' => true,
        'show_buy_now' => true,
        'auto_display' => true,
        'display_position' => 'after_content',
        'weight' => array(
            'title' => 3.0, // Increased weight for title matching
            'content' => 2.0, // Increased weight for content matching
            'categories' => 4.0, // Higher weight for categories
            'tags' => 3.0, // Increased weight for tags
            'custom_taxonomies' => 1.0
        ),
        'require_tax' => array(
            'product_cat' => 1,
        ),
        'exclude' => '',
        'recent_only' => false,
        'recent_period' => '30 day',
        'include_out_of_stock' => false,
        'template' => 'grid',
        'columns' => 4,
        'image_size' => 'woocommerce_thumbnail',
        'show_excerpt' => false,
        'cache_enabled' => true,
        'cache_timeout' => 3600, // 1 hour
    );
    
    // Enhanced algorithm default options
    $enhanced_options = array(
        'enhanced_threshold' => 1.5,
        'enhanced_limit' => 12,
        'enhanced_cross_reference' => 1,
        'enhanced_temporal_boost' => 1,
        'enhanced_popularity_boost' => 1,
        'enhanced_category_boost' => 1,
        'enhanced_weight_title' => 3.0,
        'enhanced_weight_content' => 2.0,
        'enhanced_weight_categories' => 4.0,
        'enhanced_weight_tags' => 3.0,
        'enhanced_weight_attributes' => 2.0,
        'enhanced_weight_price_range' => 1.0,
        'enhanced_weight_brand' => 2.5,
        'enhanced_min_word_length' => 3,
        'enhanced_use_stop_words' => 1,
        'enhanced_use_stemming' => 1,
        'enhanced_fuzzy_matching' => 1
    );
    
    foreach ( $default_options as $option => $value ) {
        if ( false === get_option( 'wrp_' . $option ) ) {
            add_option( 'wrp_' . $option, $value );
        }
    }
    
    foreach ( $enhanced_options as $option => $value ) {
        if ( false === get_option( 'wrp_' . $option ) ) {
            add_option( 'wrp_' . $option, $value );
        }
    }
    
    // Schedule initial cache building if we have products
    wp_schedule_single_event( time() + 30, 'wrp_build_initial_cache' );
}

// Register deactivation hook
register_deactivation_hook( __FILE__, 'wrp_deactivate' );

/**
 * Plugin deactivation
 */
function wrp_deactivate() {
    // Clean up if needed
}

/**
 * Build initial cache handler
 */
function wrp_build_initial_cache_handler() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Use the new YARPP-style cache system
    if ( class_exists( 'WRP_YARPP_Cache' ) ) {
        $cache = new WRP_YARPP_Cache();
        $result = $cache->build_cache( 20 ); // Build cache for first 20 products
        
        if ( $result['success'] ) {
            error_log( 'WRP: Initial cache built successfully. Processed ' . $result['processed'] . ' products with ' . $result['total_relations'] . ' relations.' );
        } else {
            error_log( 'WRP: Initial cache build failed: ' . $result['message'] );
        }
    }
}

// Register uninstall hook
register_uninstall_hook( __FILE__, 'wrp_uninstall' );

/**
 * Plugin uninstall
 */
function wrp_uninstall() {
    // Remove options
    $options = array(
        'version',
        'threshold',
        'limit',
        'excerpt_length',
        'show_price',
        'show_rating',
        'show_add_to_cart',
        'show_buy_now',
        'auto_display',
        'display_position',
        'weight',
        'require_tax',
        'exclude',
        'recent_only',
        'recent_period',
        'include_out_of_stock',
        'template',
        'columns',
        'image_size',
        'show_excerpt',
        'cache_enabled',
        'cache_timeout',
    );
    
    foreach ( $options as $option ) {
        delete_option( 'wrp_' . $option );
    }
    
    // Drop database tables
    require_once WRP_PLUGIN_DIR . 'includes/class-wrp-db-schema.php';
    $db_schema = new WRP_DB_Schema();
    $db_schema->drop_tables();
}