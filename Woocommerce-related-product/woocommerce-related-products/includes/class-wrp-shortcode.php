<?php
/**
 * Shortcode class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Shortcode {

    /**
     * Constructor
     */
    public function __construct() {
        // Shortcodes will be registered when calling register()
    }

    /**
     * Register shortcodes
     */
    public function register() {
        add_shortcode( 'related_products', array( $this, 'related_products_shortcode' ) );
        add_shortcode( 'wrp_related', array( $this, 'related_products_shortcode' ) );
    }

    /**
     * Related products shortcode
     *
     * @param array $atts Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string
     */
    public function related_products_shortcode( $atts, $content = '' ) {
        global $product;

        // Parse attributes
        $atts = shortcode_atts( array(
            'id' => null,
            'limit' => wrp_get_option( 'limit', 4 ),
            'columns' => wrp_get_option( 'columns', 4 ),
            'template' => wrp_get_option( 'template', 'grid' ),
            'show_price' => wrp_get_option( 'show_price', true ),
            'show_rating' => wrp_get_option( 'show_rating', true ),
            'show_add_to_cart' => wrp_get_option( 'show_add_to_cart', true ),
            'show_buy_now' => wrp_get_option( 'show_buy_now', true ),
            'show_excerpt' => wrp_get_option( 'show_excerpt', false ),
            'excerpt_length' => wrp_get_option( 'excerpt_length', 10 ),
            'image_size' => wrp_get_option( 'image_size', 'woocommerce_thumbnail' ),
            'threshold' => wrp_get_option( 'threshold', 1 ),
            'exclude' => wrp_get_option( 'exclude', '' ),
            'recent_only' => wrp_get_option( 'recent_only', false ),
            'recent_period' => wrp_get_option( 'recent_period', '30 day' ),
            'include_out_of_stock' => wrp_get_option( 'include_out_of_stock', false ),
            'weight_title' => null,
            'weight_description' => null,
            'weight_short_description' => null,
            'weight_categories' => null,
            'weight_tags' => null,
            'require_categories' => null,
            'require_tags' => null,
        ), $atts, 'related_products' );

        // Get product ID
        $product_id = $atts['id'];
        if ( ! $product_id && $product ) {
            $product_id = $product->get_id();
        }

        if ( ! $product_id ) {
            return '';
        }

        // Build arguments
        $args = array(
            'limit' => intval( $atts['limit'] ),
            'columns' => intval( $atts['columns'] ),
            'template' => sanitize_text_field( $atts['template'] ),
            'show_price' => filter_var( $atts['show_price'], FILTER_VALIDATE_BOOLEAN ),
            'show_rating' => filter_var( $atts['show_rating'], FILTER_VALIDATE_BOOLEAN ),
            'show_add_to_cart' => filter_var( $atts['show_add_to_cart'], FILTER_VALIDATE_BOOLEAN ),
            'show_buy_now' => filter_var( $atts['show_buy_now'], FILTER_VALIDATE_BOOLEAN ),
            'show_excerpt' => filter_var( $atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN ),
            'excerpt_length' => intval( $atts['excerpt_length'] ),
            'image_size' => sanitize_text_field( $atts['image_size'] ),
            'threshold' => floatval( $atts['threshold'] ),
            'exclude' => sanitize_text_field( $atts['exclude'] ),
            'recent_only' => filter_var( $atts['recent_only'], FILTER_VALIDATE_BOOLEAN ),
            'recent_period' => sanitize_text_field( $atts['recent_period'] ),
            'include_out_of_stock' => filter_var( $atts['include_out_of_stock'], FILTER_VALIDATE_BOOLEAN ),
        );

        // Build weight array
        $weight = array();
        if ( $atts['weight_title'] !== null ) {
            $weight['title'] = floatval( $atts['weight_title'] );
        }
        if ( $atts['weight_description'] !== null ) {
            $weight['description'] = floatval( $atts['weight_description'] );
        }
        if ( $atts['weight_short_description'] !== null ) {
            $weight['short_description'] = floatval( $atts['weight_short_description'] );
        }
        if ( $atts['weight_categories'] !== null ) {
            $weight['tax']['product_cat'] = floatval( $atts['weight_categories'] );
        }
        if ( $atts['weight_tags'] !== null ) {
            $weight['tax']['product_tag'] = floatval( $atts['weight_tags'] );
        }

        if ( ! empty( $weight ) ) {
            $args['weight'] = $weight;
        }

        // Build require_tax array
        $require_tax = array();
        if ( $atts['require_categories'] !== null ) {
            $require_tax['product_cat'] = intval( $atts['require_categories'] );
        }
        if ( $atts['require_tags'] !== null ) {
            $require_tax['product_tag'] = intval( $atts['require_tags'] );
        }

        if ( ! empty( $require_tax ) ) {
            $args['require_tax'] = $require_tax;
        }

        // Get related products
        ob_start();
        wrp_display_related_products( $product_id, $args, false );
        return ob_get_clean();
    }
}