<?php
/**
 * Grid Template for WooCommerce Related Products Pro
 *
 * This template displays related products in a responsive grid layout
 * with product images, titles, prices, ratings, and action buttons.
 *
 * @package WRP/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract( $args );

// Ensure we have related products
if ( empty( $related_products ) ) {
    return;
}

$columns = isset( $args['columns'] ) ? intval( $args['columns'] ) : 4;
$image_size = isset( $args['image_size'] ) ? $args['image_size'] : 'woocommerce_thumbnail';
$show_price = isset( $args['show_price'] ) ? (bool) $args['show_price'] : true;
$show_rating = isset( $args['show_rating'] ) ? (bool) $args['show_rating'] : true;
$show_add_to_cart = isset( $args['show_add_to_cart'] ) ? (bool) $args['show_add_to_cart'] : true;
$show_buy_now = isset( $args['show_buy_now'] ) ? (bool) $args['show_buy_now'] : true;
$show_excerpt = isset( $args['show_excerpt'] ) ? (bool) $args['show_excerpt'] : false;
$excerpt_length = isset( $args['excerpt_length'] ) ? intval( $args['excerpt_length'] ) : 10;

// Calculate column classes
$column_classes = array(
    1 => 'wrp-grid-1',
    2 => 'wrp-grid-2',
    3 => 'wrp-grid-3',
    4 => 'wrp-grid-4',
    5 => 'wrp-grid-5',
    6 => 'wrp-grid-6',
);

$grid_class = isset( $column_classes[ $columns ] ) ? $column_classes[ $columns ] : $column_classes[4];
?>

<div class="wrp-related-products wrp-template-grid <?php echo esc_attr( $grid_class ); ?>">
    
    <?php if ( apply_filters( 'wrp_show_heading', true, $args ) ) : ?>
        <h2 class="wrp-related-products-heading">
            <?php echo esc_html( apply_filters( 'wrp_related_products_heading', __( 'Related Products', 'woocommerce-related-products' ) ) ); ?>
        </h2>
    <?php endif; ?>

    <div class="wrp-products-grid">
        <?php foreach ( $related_products as $related_product ) : ?>
            <?php
            $product_id = $related_product->get_id();
            $product_link = $related_product->get_permalink();
            $product_title = $related_product->get_name();
            $product_image = $related_product->get_image( $image_size );
            $product_price = $related_product->get_price_html();
            $product_rating = $related_product->get_average_rating();
            $product_rating_count = $related_product->get_rating_count();
            $product_excerpt = $related_product->get_short_description();
            
            // Truncate excerpt
            if ( $show_excerpt && ! empty( $product_excerpt ) ) {
                $words = explode( ' ', $product_excerpt );
                if ( count( $words ) > $excerpt_length ) {
                    $product_excerpt = implode( ' ', array_slice( $words, 0, $excerpt_length ) ) . '...';
                }
            }
            ?>

            <div class="wrp-product-item">
                <div class="wrp-product-inner">
                    
                    <?php if ( $related_product->is_on_sale() ) : ?>
                        <span class="wrp-sale-badge"><?php echo esc_html__( 'Sale!', 'woocommerce-related-products' ); ?></span>
                    <?php endif; ?>

                    <div class="wrp-product-image">
                        <a href="<?php echo esc_url( $product_link ); ?>" title="<?php echo esc_attr( $product_title ); ?>">
                            <?php echo $product_image; ?>
                        </a>
                    </div>

                    <div class="wrp-product-content">
                        
                        <h3 class="wrp-product-title">
                            <a href="<?php echo esc_url( $product_link ); ?>" title="<?php echo esc_attr( $product_title ); ?>">
                                <?php echo esc_html( $product_title ); ?>
                            </a>
                        </h3>

                        <?php if ( $show_rating && $product_rating > 0 ) : ?>
                            <div class="wrp-product-rating">
                                <?php echo wc_get_rating_html( $product_rating, $product_rating_count ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_price ) : ?>
                            <div class="wrp-product-price">
                                <?php echo $product_price; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_excerpt && ! empty( $product_excerpt ) ) : ?>
                            <div class="wrp-product-excerpt">
                                <?php echo wp_kses_post( $product_excerpt ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="wrp-product-actions">
                            <?php if ( $show_add_to_cart ) : ?>
                                <?php if ( $related_product->is_type( 'simple' ) ) : ?>
                                    <button type="button" class="wrp-add-to-cart button alt" 
                                            data-product-id="<?php echo esc_attr( $product_id ); ?>"
                                            data-nonce="<?php echo wp_create_nonce( 'wrp_add_to_cart' ); ?>">
                                        <?php echo esc_html( $related_product->add_to_cart_text() ); ?>
                                    </button>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $product_link ); ?>" class="wrp-view-product button alt">
                                        <?php echo esc_html__( 'View Product', 'woocommerce-related-products' ); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ( $show_buy_now ) : ?>
                                <a href="<?php echo esc_url( $related_product->add_to_cart_url() ); ?>" 
                                   class="wrp-buy-now button">
                                    <?php echo esc_html__( 'Buy Now', 'woocommerce-related-products' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

</div>