<?php
/**
 * Public class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Public {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add theme support for templates
        add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
        
        // Add body classes
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
        
        // Enqueue public scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
        
        // Handle AJAX add to cart
        add_action( 'wp_ajax_nopriv_wrp_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
        add_action( 'wp_ajax_wrp_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
    }

    /**
     * Add theme support for custom templates
     */
    public function add_theme_support() {
        // Add theme support for post thumbnails if not already supported
        if ( ! function_exists( 'add_theme_support' ) ) {
            return;
        }

        // Add custom image size if needed
        if ( ! in_array( 'wrp-thumbnail', get_intermediate_image_sizes() ) ) {
            add_image_size( 'wrp-thumbnail', 300, 300, true );
        }
    }

    /**
     * Add body classes
     *
     * @param array $classes Body classes.
     * @return array
     */
    public function add_body_classes( $classes ) {
        // Add class if we're on a product page
        if ( is_product() ) {
            $classes[] = 'wrp-product-page';
        }

        // Add class if auto display is enabled
        if ( wrp_get_option( 'auto_display', true ) ) {
            $classes[] = 'wrp-auto-display';
        }

        return $classes;
    }

    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_scripts() {
        // Only load on product pages or when related products might be displayed
        if ( is_product() || wrp_get_option( 'auto_display', true ) ) {
            // Enqueue styles
            wp_enqueue_style(
                'wrp-public',
                WRP_PLUGIN_URL . 'assets/css/wrp-public.css',
                array(),
                WRP_VERSION
            );

            // Enqueue scripts
            wp_enqueue_script(
                'wrp-public',
                WRP_PLUGIN_URL . 'assets/js/wrp-public.js',
                array( 'jquery', 'wp-util' ),
                WRP_VERSION,
                true
            );

            // Localize script
            wp_localize_script(
                'wrp-public',
                'wrp_public_vars',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'wrp_add_to_cart' ),
                    'i18n' => array(
                        'adding_to_cart' => __( 'Adding to cart...', 'woocommerce-related-products' ),
                        'added_to_cart' => __( 'Added to cart!', 'woocommerce-related-products' ),
                        'cart_error' => __( 'Error adding product to cart.', 'woocommerce-related-products' ),
                    ),
                )
            );
        }
    }

    /**
     * Handle AJAX add to cart
     */
    public function ajax_add_to_cart() {
        check_ajax_referer( 'wrp_add_to_cart', 'nonce' );
        
        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        
        if ( ! $product_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid product ID.', 'woocommerce-related-products' )
            ) );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( array(
                'message' => __( 'Product not found.', 'woocommerce-related-products' )
            ) );
        }

        // Check if product is purchasable
        if ( ! $product->is_purchasable() ) {
            wp_send_json_error( array(
                'message' => __( 'This product cannot be purchased.', 'woocommerce-related-products' )
            ) );
        }

        // Check if product is in stock
        if ( ! $product->is_in_stock() ) {
            wp_send_json_error( array(
                'message' => __( 'This product is out of stock.', 'woocommerce-related-products' )
            ) );
        }

        $added = WC()->cart->add_to_cart( $product_id );
        
        if ( $added ) {
            // Prepare response
            $response = array(
                'success' => true,
                'message' => sprintf(
                    __( '%s has been added to your cart.', 'woocommerce-related-products' ),
                    $product->get_name()
                ),
                'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
                'cart_hash' => WC()->cart->get_cart_hash(),
                'redirect' => apply_filters( 'woocommerce_add_to_cart_redirect', false )
            );

            // Add cart widget fragment if needed
            if ( function_exists( 'wc_get_cart_url' ) ) {
                ob_start();
                woocommerce_mini_cart();
                $mini_cart = ob_get_clean();
                $response['fragments']['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
            }

            wp_send_json_success( $response );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Failed to add product to cart.', 'woocommerce-related-products' )
            ) );
        }
    }

    /**
     * Get product image HTML
     *
     * @param WC_Product $product Product object.
     * @param string     $size Image size.
     * @param array      $attr Image attributes.
     * @return string
     */
    public function get_product_image( $product, $size = 'woocommerce_thumbnail', $attr = array() ) {
        if ( ! $product ) {
            return '';
        }

        $image_size = apply_filters( 'wrp_product_image_size', $size, $product );
        $image = $product->get_image( $image_size, $attr );

        return apply_filters( 'wrp_product_image_html', $image, $product, $image_size, $attr );
    }

    /**
     * Get product price HTML
     *
     * @param WC_Product $product Product object.
     * @return string
     */
    public function get_product_price( $product ) {
        if ( ! $product ) {
            return '';
        }

        $price = $product->get_price_html();
        return apply_filters( 'wrp_product_price_html', $price, $product );
    }

    /**
     * Get product rating HTML
     *
     * @param WC_Product $product Product object.
     * @return string
     */
    public function get_product_rating( $product ) {
        if ( ! $product ) {
            return '';
        }

        $rating = $product->get_average_rating();
        $count = $product->get_rating_count();

        if ( $rating > 0 ) {
            $rating_html = wc_get_rating_html( $rating, $count );
        } else {
            $rating_html = '';
        }

        return apply_filters( 'wrp_product_rating_html', $rating_html, $product, $rating, $count );
    }

    /**
     * Get product excerpt HTML
     *
     * @param WC_Product $product Product object.
     * @param int         $length Excerpt length in words.
     * @return string
     */
    public function get_product_excerpt( $product, $length = 10 ) {
        if ( ! $product ) {
            return '';
        }

        $excerpt = $product->get_short_description();
        
        if ( empty( $excerpt ) ) {
            $excerpt = $product->get_description();
        }

        if ( ! empty( $excerpt ) ) {
            $words = explode( ' ', $excerpt );
            if ( count( $words ) > $length ) {
                $excerpt = implode( ' ', array_slice( $words, 0, $length ) ) . '...';
            }
        }

        return apply_filters( 'wrp_product_excerpt_html', $excerpt, $product, $length );
    }

    /**
     * Get add to cart button HTML
     *
     * @param WC_Product $product Product object.
     * @return string
     */
    public function get_add_to_cart_button( $product ) {
        if ( ! $product ) {
            return '';
        }

        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return '';
        }

        $product_id = $product->get_id();
        $nonce = wp_create_nonce( 'wrp_add_to_cart' );
        $button_text = $product->add_to_cart_text();
        $button_class = 'wrp-add-to-cart button alt';

        if ( $product->is_type( 'simple' ) ) {
            $button = sprintf(
                '<button type="button" class="%s" data-product-id="%d" data-nonce="%s">%s</button>',
                esc_attr( $button_class ),
                esc_attr( $product_id ),
                esc_attr( $nonce ),
                esc_html( $button_text )
            );
        } else {
            $button = sprintf(
                '<a href="%s" class="%s">%s</a>',
                esc_url( $product->get_permalink() ),
                esc_attr( $button_class . ' wrp-view-product' ),
                esc_html__( 'View Product', 'woocommerce-related-products' )
            );
        }

        return apply_filters( 'wrp_add_to_cart_button_html', $button, $product );
    }

    /**
     * Get buy now button HTML
     *
     * @param WC_Product $product Product object.
     * @return string
     */
    public function get_buy_now_button( $product ) {
        if ( ! $product ) {
            return '';
        }

        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return '';
        }

        $button = sprintf(
            '<a href="%s" class="wrp-buy-now button">%s</a>',
            esc_url( $product->add_to_cart_url() ),
            esc_html__( 'Buy Now', 'woocommerce-related-products' )
        );

        return apply_filters( 'wrp_buy_now_button_html', $button, $product );
    }

    /**
     * Get sale badge HTML
     *
     * @param WC_Product $product Product object.
     * @return string
     */
    public function get_sale_badge( $product ) {
        if ( ! $product ) {
            return '';
        }

        if ( ! $product->is_on_sale() ) {
            return '';
        }

        $badge = sprintf(
            '<span class="wrp-sale-badge">%s</span>',
            esc_html__( 'Sale!', 'woocommerce-related-products' )
        );

        return apply_filters( 'wrp_sale_badge_html', $badge, $product );
    }
}