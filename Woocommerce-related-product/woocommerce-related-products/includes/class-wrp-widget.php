<?php
/**
 * Widget class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'wrp_related_products',
            __( 'Related Products (WRP)', 'woocommerce-related-products' ),
            array(
                'description' => __( 'Display related products using WooCommerce Related Products Pro', 'woocommerce-related-products' ),
                'classname' => 'widget_wrp_related_products',
            )
        );
    }

    /**
     * Front-end display of widget
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        global $product;

        // Only show on single product pages
        if ( ! is_product() || ! $product ) {
            return;
        }

        $title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

        echo $args['before_widget'];

        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Build arguments
        $widget_args = array(
            'limit' => isset( $instance['limit'] ) ? intval( $instance['limit'] ) : 3,
            'columns' => isset( $instance['columns'] ) ? intval( $instance['columns'] ) : 1,
            'template' => isset( $instance['template'] ) ? sanitize_text_field( $instance['template'] ) : 'list',
            'show_price' => isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : true,
            'show_rating' => isset( $instance['show_rating'] ) ? (bool) $instance['show_rating'] : true,
            'show_add_to_cart' => isset( $instance['show_add_to_cart'] ) ? (bool) $instance['show_add_to_cart'] : true,
            'show_buy_now' => isset( $instance['show_buy_now'] ) ? (bool) $instance['show_buy_now'] : false,
            'show_excerpt' => isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : false,
            'excerpt_length' => isset( $instance['excerpt_length'] ) ? intval( $instance['excerpt_length'] ) : 10,
            'image_size' => isset( $instance['image_size'] ) ? sanitize_text_field( $instance['image_size'] ) : 'woocommerce_thumbnail',
            'threshold' => isset( $instance['threshold'] ) ? floatval( $instance['threshold'] ) : 1,
        );

        // Display related products
        wrp_display_related_products( $product->get_id(), $widget_args );

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $defaults = array(
            'title' => __( 'Related Products', 'woocommerce-related-products' ),
            'limit' => 3,
            'columns' => 1,
            'template' => 'list',
            'show_price' => true,
            'show_rating' => true,
            'show_add_to_cart' => true,
            'show_buy_now' => false,
            'show_excerpt' => false,
            'excerpt_length' => 10,
            'image_size' => 'woocommerce_thumbnail',
            'threshold' => 1,
        );

        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'woocommerce-related-products' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" 
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" 
                   value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Number of products:', 'woocommerce-related-products' ); ?></label>
            <input class="small-text" id="<?php echo $this->get_field_id( 'limit' ); ?>" 
                   name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" 
                   value="<?php echo esc_attr( $instance['limit'] ); ?>" min="1" max="10">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Columns:', 'woocommerce-related-products' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'columns' ); ?>" 
                    name="<?php echo $this->get_field_name( 'columns' ); ?>">
                <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                    <option value="<?php echo $i; ?>" <?php selected( $instance['columns'], $i ); ?>>
                        <?php echo sprintf( _n( '%d Column', '%d Columns', $i, 'woocommerce-related-products' ), $i ); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:', 'woocommerce-related-products' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'template' ); ?>" 
                    name="<?php echo $this->get_field_name( 'template' ); ?>">
                <option value="list" <?php selected( $instance['template'], 'list' ); ?>>
                    <?php _e( 'List', 'woocommerce-related-products' ); ?>
                </option>
                <option value="grid" <?php selected( $instance['template'], 'grid' ); ?>>
                    <?php _e( 'Grid', 'woocommerce-related-products' ); ?>
                </option>
                <option value="carousel" <?php selected( $instance['template'], 'carousel' ); ?>>
                    <?php _e( 'Carousel', 'woocommerce-related-products' ); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size:', 'woocommerce-related-products' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>" 
                    name="<?php echo $this->get_field_name( 'image_size' ); ?>">
                <?php
                $image_sizes = get_intermediate_image_sizes();
                foreach ( $image_sizes as $size ) :
                ?>
                    <option value="<?php echo esc_attr( $size ); ?>" <?php selected( $instance['image_size'], $size ); ?>>
                        <?php echo esc_html( $size ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'threshold' ); ?>"><?php _e( 'Match Threshold:', 'woocommerce-related-products' ); ?></label>
            <input class="small-text" id="<?php echo $this->get_field_id( 'threshold' ); ?>" 
                   name="<?php echo $this->get_field_name( 'threshold' ); ?>" type="number" 
                   value="<?php echo esc_attr( $instance['threshold'] ); ?>" min="0" max="10" step="0.1">
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_price'], true ); ?> 
                   id="<?php echo $this->get_field_id( 'show_price' ); ?>" 
                   name="<?php echo $this->get_field_name( 'show_price' ); ?>">
            <label for="<?php echo $this->get_field_id( 'show_price' ); ?>"><?php _e( 'Show price', 'woocommerce-related-products' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_rating'], true ); ?> 
                   id="<?php echo $this->get_field_id( 'show_rating' ); ?>" 
                   name="<?php echo $this->get_field_name( 'show_rating' ); ?>">
            <label for="<?php echo $this->get_field_id( 'show_rating' ); ?>"><?php _e( 'Show rating', 'woocommerce-related-products' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_add_to_cart'], true ); ?> 
                   id="<?php echo $this->get_field_id( 'show_add_to_cart' ); ?>" 
                   name="<?php echo $this->get_field_name( 'show_add_to_cart' ); ?>">
            <label for="<?php echo $this->get_field_id( 'show_add_to_cart' ); ?>"><?php _e( 'Show add to cart button', 'woocommerce-related-products' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_buy_now'], true ); ?> 
                   id="<?php echo $this->get_field_id( 'show_buy_now' ); ?>" 
                   name="<?php echo $this->get_field_name( 'show_buy_now' ); ?>">
            <label for="<?php echo $this->get_field_id( 'show_buy_now' ); ?>"><?php _e( 'Show buy now button', 'woocommerce-related-products' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'], true ); ?> 
                   id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" 
                   name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>">
            <label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Show excerpt', 'woocommerce-related-products' ); ?></label>
        </p>

        <?php if ( $instance['show_excerpt'] ) : ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php _e( 'Excerpt length (words):', 'woocommerce-related-products' ); ?></label>
            <input class="small-text" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" 
                   name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" 
                   value="<?php echo esc_attr( $instance['excerpt_length'] ); ?>" min="5" max="50">
        </p>
        <?php endif; ?>

        <?php
    }

    /**
     * Sanitize widget form values as they are saved
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['limit'] = max( 1, min( 10, intval( $new_instance['limit'] ) ) );
        $instance['columns'] = max( 1, min( 4, intval( $new_instance['columns'] ) ) );
        $instance['template'] = in_array( $new_instance['template'], array( 'list', 'grid', 'carousel' ) ) ? $new_instance['template'] : 'list';
        $instance['image_size'] = sanitize_text_field( $new_instance['image_size'] );
        $instance['threshold'] = max( 0, min( 10, floatval( $new_instance['threshold'] ) ) );
        $instance['show_price'] = isset( $new_instance['show_price'] ) ? (bool) $new_instance['show_price'] : true;
        $instance['show_rating'] = isset( $new_instance['show_rating'] ) ? (bool) $new_instance['show_rating'] : true;
        $instance['show_add_to_cart'] = isset( $new_instance['show_add_to_cart'] ) ? (bool) $new_instance['show_add_to_cart'] : true;
        $instance['show_buy_now'] = isset( $new_instance['show_buy_now'] ) ? (bool) $new_instance['show_buy_now'] : false;
        $instance['show_excerpt'] = isset( $new_instance['show_excerpt'] ) ? (bool) $new_instance['show_excerpt'] : false;
        $instance['excerpt_length'] = max( 5, min( 50, intval( $new_instance['excerpt_length'] ) ) );

        return $instance;
    }
}