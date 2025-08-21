<?php
/**
 * WooCommerce Related Products Pro Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get related products for a given product
 *
 * @param int   $product_id Product ID.
 * @param array $args Arguments for getting related products.
 * @return array|false Array of related products or false if none found.
 */
function wrp_get_related_products( $product_id, $args = array() ) {
    global $wrp;
    
    if ( ! $wrp ) {
        return false;
    }
    
    return $wrp->get_related_products( $product_id, $args );
}

/**
 * Display related products
 *
 * @param int   $product_id Product ID.
 * @param array $args Arguments for displaying related products.
 * @param bool  $echo Whether to echo or return the output.
 * @return string|void HTML output if $echo is false.
 */
function wrp_display_related_products( $product_id = null, $args = array(), $echo = true ) {
    global $wrp, $product;
    
    if ( ! $wrp ) {
        return;
    }
    
    if ( ! $product_id && $product ) {
        $product_id = $product->get_id();
    }
    
    if ( ! $product_id ) {
        return;
    }
    
    return $wrp->display_related_products( $product_id, $args, $echo );
}

/**
 * Check if related products exist
 *
 * @param int   $product_id Product ID.
 * @param array $args Arguments for checking related products.
 * @return bool
 */
function wrp_has_related_products( $product_id = null, $args = array() ) {
    global $wrp, $product;
    
    if ( ! $wrp ) {
        return false;
    }
    
    if ( ! $product_id && $product ) {
        $product_id = $product->get_id();
    }
    
    if ( ! $product_id ) {
        return false;
    }
    
    return $wrp->has_related_products( $product_id, $args );
}

/**
 * Get plugin option
 *
 * @param string $option Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function wrp_get_option( $option, $default = false ) {
    return get_option( 'wrp_' . $option, $default );
}

/**
 * Update plugin option
 *
 * @param string $option Option name.
 * @param mixed  $value Option value.
 * @return bool
 */
function wrp_update_option( $option, $value ) {
    return update_option( 'wrp_' . $option, $value );
}

/**
 * Delete plugin option
 *
 * @param string $option Option name.
 * @return bool
 */
function wrp_delete_option( $option ) {
    return delete_option( 'wrp_' . $option );
}

/**
 * Get product keywords for algorithm
 *
 * @param int    $product_id Product ID.
 * @param string $type Type of keywords (title, description, short_description, all).
 * @return string|array
 */
function wrp_get_product_keywords( $product_id, $type = 'all' ) {
    global $wrp;
    
    if ( ! $wrp ) {
        return false;
    }
    
    return $wrp->get_product_keywords( $product_id, $type );
}

/**
 * Clear related products cache
 *
 * @param int|array $product_ids Product ID(s).
 */
function wrp_clear_cache( $product_ids ) {
    global $wrp;
    
    if ( ! $wrp ) {
        return;
    }
    
    $wrp->clear_cache( $product_ids );
}

/**
 * Get related products score
 *
 * @param int $reference_id Reference product ID.
 * @param int $related_id Related product ID.
 * @return float|false
 */
function wrp_get_related_score( $reference_id, $related_id ) {
    global $wrp;
    
    if ( ! $wrp ) {
        return false;
    }
    
    return $wrp->get_related_score( $reference_id, $related_id );
}