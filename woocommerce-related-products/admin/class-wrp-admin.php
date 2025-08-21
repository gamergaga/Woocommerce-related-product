<?php
/**
 * Admin class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Admin {

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
        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        
        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( WRP_PLUGIN_DIR . 'woocommerce-related-products.php' ), array( $this, 'add_settings_link' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_wrp_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_wrp_rebuild_cache', array( $this, 'ajax_rebuild_cache' ) );
        add_action( 'wp_ajax_wrp_cache_progress', array( $this, 'ajax_cache_progress' ) );
        add_action( 'wp_ajax_wrp_optimize_cache', array( $this, 'ajax_optimize_cache' ) );
        add_action( 'wp_ajax_wrp_test_mode', array( $this, 'ajax_test_mode' ) );
        add_action( 'wp_ajax_wrp_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Related Products Pro', 'woocommerce-related-products' ),
            __( 'Related Products', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-products',
            56
        );

        add_submenu_page(
            'wrp-settings',
            __( 'Settings', 'woocommerce-related-products' ),
            __( 'Settings', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'wrp-settings',
            __( 'Cache Status', 'woocommerce-related-products' ),
            __( 'Cache Status', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-cache',
            array( $this, 'render_cache_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General settings
        register_setting( 'wrp_general', 'wrp_threshold' );
        register_setting( 'wrp_general', 'wrp_limit' );
        register_setting( 'wrp_general', 'wrp_excerpt_length' );
        register_setting( 'wrp_general', 'wrp_auto_display' );
        register_setting( 'wrp_general', 'wrp_display_position' );
        register_setting( 'wrp_general', 'wrp_cache_enabled' );
        register_setting( 'wrp_general', 'wrp_cache_timeout' );

        // Display settings
        register_setting( 'wrp_display', 'wrp_show_price' );
        register_setting( 'wrp_display', 'wrp_show_rating' );
        register_setting( 'wrp_display', 'wrp_show_add_to_cart' );
        register_setting( 'wrp_display', 'wrp_show_buy_now' );
        register_setting( 'wrp_display', 'wrp_template' );
        register_setting( 'wrp_display', 'wrp_columns' );
        register_setting( 'wrp_display', 'wrp_image_size' );
        register_setting( 'wrp_display', 'wrp_show_excerpt' );

        // Algorithm settings
        register_setting( 'wrp_algorithm', 'wrp_weight' );
        register_setting( 'wrp_algorithm', 'wrp_require_tax' );
        register_setting( 'wrp_algorithm', 'wrp_exclude' );
        register_setting( 'wrp_algorithm', 'wrp_recent_only' );
        register_setting( 'wrp_algorithm', 'wrp_recent_period' );
        register_setting( 'wrp_algorithm', 'wrp_include_out_of_stock' );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        // Load on our admin pages and related products settings
        $allowed_hooks = array(
            'toplevel_page_wrp-settings',
            'related-products_page_wrp-settings',
            'related-products_page_wrp-cache'
        );
        
        if ( ! in_array( $hook, $allowed_hooks ) && strpos( $hook, 'wrp-' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'wrp-admin',
            WRP_PLUGIN_URL . 'assets/css/wrp-admin.css',
            array(),
            WRP_VERSION
        );

        wp_enqueue_script(
            'wrp-admin',
            WRP_PLUGIN_URL . 'assets/js/wrp-admin.js',
            array( 'jquery', 'wp-util' ),
            WRP_VERSION,
            true
        );

        wp_localize_script(
            'wrp-admin',
            'wrp_admin_vars',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wrp_admin_nonce' ),
                'i18n' => array(
                    'clear_cache_confirm' => __( 'Are you sure you want to clear the cache? This cannot be undone.', 'woocommerce-related-products' ),
                    'rebuild_cache_confirm' => __( 'Are you sure you want to rebuild the cache? This may take some time.', 'woocommerce-related-products' ),
                    'cache_cleared' => __( 'Cache cleared successfully.', 'woocommerce-related-products' ),
                    'cache_rebuilding' => __( 'Rebuilding cache...', 'woocommerce-related-products' ),
                    'cache_rebuilt' => __( 'Cache rebuilt successfully.', 'woocommerce-related-products' ),
                ),
            )
        );
    }

    /**
     * Add settings link to plugins page
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=wrp-settings' ) . '">' . __( 'Settings', 'woocommerce-related-products' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap wrp-admin">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wrp_general' );
                settings_fields( 'wrp_display' );
                settings_fields( 'wrp_algorithm' );
                ?>
                
                <div class="wrp-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'woocommerce-related-products' ); ?></a>
                        <a href="#display" class="nav-tab"><?php _e( 'Display', 'woocommerce-related-products' ); ?></a>
                        <a href="#algorithm" class="nav-tab"><?php _e( 'Algorithm', 'woocommerce-related-products' ); ?></a>
                    </nav>
                    
                    <div class="tab-content">
                        <!-- General Settings -->
                        <div id="general" class="tab-pane active">
                            <?php $this->render_general_settings(); ?>
                        </div>
                        
                        <!-- Display Settings -->
                        <div id="display" class="tab-pane">
                            <?php $this->render_display_settings(); ?>
                        </div>
                        
                        <!-- Algorithm Settings -->
                        <div id="algorithm" class="tab-pane">
                            <?php $this->render_algorithm_settings(); ?>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general settings
     */
    private function render_general_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wrp_threshold"><?php _e( 'Match Threshold', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <select id="wrp_threshold" name="wrp_threshold" class="regular-text">
                        <option value="0.5" <?php selected( wrp_get_option( 'threshold', 1 ), 0.5 ); ?>>5% - Very Broad</option>
                        <option value="1" <?php selected( wrp_get_option( 'threshold', 1 ), 1 ); ?>>10% - Broad</option>
                        <option value="2" <?php selected( wrp_get_option( 'threshold', 1 ), 2 ); ?>>20% - Moderate</option>
                        <option value="3" <?php selected( wrp_get_option( 'threshold', 1 ), 3 ); ?>>30% - Balanced</option>
                        <option value="5" <?php selected( wrp_get_option( 'threshold', 1 ), 5 ); ?>>50% - Precise</option>
                        <option value="7" <?php selected( wrp_get_option( 'threshold', 1 ), 7 ); ?>>70% - Very Precise</option>
                        <option value="10" <?php selected( wrp_get_option( 'threshold', 1 ), 10 ); ?>>100% - Exact Match</option>
                    </select>
                    <p class="description"><?php _e( 'Minimum relevance score required. Higher percentages show fewer but more closely related products.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_limit"><?php _e( 'Number of Products', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="number" id="wrp_limit" name="wrp_limit" 
                           value="<?php echo esc_attr( wrp_get_option( 'limit', 4 ) ); ?>" 
                           min="1" max="20" class="small-text">
                    <p class="description"><?php _e( 'Maximum number of related products to display.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_excerpt_length"><?php _e( 'Excerpt Length', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="number" id="wrp_excerpt_length" name="wrp_excerpt_length" 
                           value="<?php echo esc_attr( wrp_get_option( 'excerpt_length', 10 ) ); ?>" 
                           min="5" max="50" class="small-text">
                    <p class="description"><?php _e( 'Number of words to show in product excerpts.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_auto_display"><?php _e( 'Auto Display', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_auto_display" name="wrp_auto_display" 
                               value="1" <?php checked( wrp_get_option( 'auto_display', true ) ); ?>>
                        <?php _e( 'Automatically display related products on product pages', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_display_position"><?php _e( 'Display Position', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <select id="wrp_display_position" name="wrp_display_position">
                        <option value="before_content" <?php selected( wrp_get_option( 'display_position', 'after_content' ), 'before_content' ); ?>>
                            <?php _e( 'Before Product Content', 'woocommerce-related-products' ); ?>
                        </option>
                        <option value="after_content" <?php selected( wrp_get_option( 'display_position', 'after_content' ), 'after_content' ); ?>>
                            <?php _e( 'After Product Content', 'woocommerce-related-products' ); ?>
                        </option>
                        <option value="after_add_to_cart" <?php selected( wrp_get_option( 'display_position', 'after_content' ), 'after_add_to_cart' ); ?>>
                            <?php _e( 'After Add to Cart Form', 'woocommerce-related-products' ); ?>
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_cache_enabled"><?php _e( 'Enable Caching', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_cache_enabled" name="wrp_cache_enabled" 
                               value="1" <?php checked( wrp_get_option( 'cache_enabled', true ) ); ?>>
                        <?php _e( 'Enable caching for better performance', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_cache_timeout"><?php _e( 'Cache Timeout', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="number" id="wrp_cache_timeout" name="wrp_cache_timeout" 
                           value="<?php echo esc_attr( wrp_get_option( 'cache_timeout', 3600 ) ); ?>" 
                           min="300" max="86400" step="300" class="small-text">
                    <p class="description"><?php _e( 'Cache timeout in seconds (default: 3600 = 1 hour).', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render display settings
     */
    private function render_display_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wrp_show_price"><?php _e( 'Show Price', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_price" name="wrp_show_price" 
                               value="1" <?php checked( wrp_get_option( 'show_price', true ) ); ?>>
                        <?php _e( 'Display product prices', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_show_rating"><?php _e( 'Show Rating', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_rating" name="wrp_show_rating" 
                               value="1" <?php checked( wrp_get_option( 'show_rating', true ) ); ?>>
                        <?php _e( 'Display product ratings', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_show_add_to_cart"><?php _e( 'Show Add to Cart', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_add_to_cart" name="wrp_show_add_to_cart" 
                               value="1" <?php checked( wrp_get_option( 'show_add_to_cart', true ) ); ?>>
                        <?php _e( 'Display add to cart buttons', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_show_buy_now"><?php _e( 'Show Buy Now', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_buy_now" name="wrp_show_buy_now" 
                               value="1" <?php checked( wrp_get_option( 'show_buy_now', true ) ); ?>>
                        <?php _e( 'Display buy now buttons', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_template"><?php _e( 'Display Template', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <div class="wrp-template-selector">
                        <select id="wrp_template" name="wrp_template" class="wrp-template-select">
                            <option value="grid" <?php selected( wrp_get_option( 'template', 'grid' ), 'grid' ); ?>>
                                <?php _e( 'Grid Layout', 'woocommerce-related-products' ); ?>
                            </option>
                            <option value="list" <?php selected( wrp_get_option( 'template', 'grid' ), 'list' ); ?>>
                                <?php _e( 'List Layout', 'woocommerce-related-products' ); ?>
                            </option>
                            <option value="carousel" <?php selected( wrp_get_option( 'template', 'grid' ), 'carousel' ); ?>>
                                <?php _e( 'Carousel Layout', 'woocommerce-related-products' ); ?>
                            </option>
                        </select>
                        
                        <div class="wrp-template-previews">
                            <div class="wrp-preview wrp-preview-grid <?php echo wrp_get_option( 'template', 'grid' ) === 'grid' ? 'active' : ''; ?>">
                                <div class="wrp-preview-title"><?php _e( 'Grid Layout', 'woocommerce-related-products' ); ?></div>
                                <div class="wrp-preview-content">
                                    <div class="wrp-preview-grid-demo">
                                        <div class="wrp-preview-product">
                                            <div class="wrp-preview-product-image"></div>
                                            <div class="wrp-preview-product-title">Product Name</div>
                                            <div class="wrp-preview-product-price">$29.99</div>
                                        </div>
                                        <div class="wrp-preview-product">
                                            <div class="wrp-preview-product-image"></div>
                                            <div class="wrp-preview-product-title">Product Name</div>
                                            <div class="wrp-preview-product-price">$39.99</div>
                                        </div>
                                        <div class="wrp-preview-product">
                                            <div class="wrp-preview-product-image"></div>
                                            <div class="wrp-preview-product-title">Product Name</div>
                                            <div class="wrp-preview-product-price">$49.99</div>
                                        </div>
                                        <div class="wrp-preview-product">
                                            <div class="wrp-preview-product-image"></div>
                                            <div class="wrp-preview-product-title">Product Name</div>
                                            <div class="wrp-preview-product-price">$59.99</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wrp-preview-desc"><?php _e( 'Clean grid layout perfect for product catalogs', 'woocommerce-related-products' ); ?></div>
                            </div>
                            
                            <div class="wrp-preview wrp-preview-list <?php echo wrp_get_option( 'template', 'grid' ) === 'list' ? 'active' : ''; ?>">
                                <div class="wrp-preview-title"><?php _e( 'List Layout', 'woocommerce-related-products' ); ?></div>
                                <div class="wrp-preview-content">
                                    <div class="wrp-preview-list-demo">
                                        <div class="wrp-preview-list-item">
                                            <div class="wrp-preview-list-image"></div>
                                            <div class="wrp-preview-list-content">
                                                <div class="wrp-preview-list-title">Product Name with Detailed Description</div>
                                                <div class="wrp-preview-list-price">$29.99</div>
                                                <div class="wrp-preview-list-excerpt">This is a brief product description showing key features...</div>
                                            </div>
                                            <div class="wrp-preview-list-actions">
                                                <div class="wrp-preview-list-button"></div>
                                            </div>
                                        </div>
                                        <div class="wrp-preview-list-item">
                                            <div class="wrp-preview-list-image"></div>
                                            <div class="wrp-preview-list-content">
                                                <div class="wrp-preview-list-title">Another Product Name</div>
                                                <div class="wrp-preview-list-price">$39.99</div>
                                                <div class="wrp-preview-list-excerpt">Detailed product information with specifications...</div>
                                            </div>
                                            <div class="wrp-preview-list-actions">
                                                <div class="wrp-preview-list-button"></div>
                                            </div>
                                        </div>
                                        <div class="wrp-preview-list-item">
                                            <div class="wrp-preview-list-image"></div>
                                            <div class="wrp-preview-list-content">
                                                <div class="wrp-preview-list-title">Third Product Example</div>
                                                <div class="wrp-preview-list-price">$49.99</div>
                                                <div class="wrp-preview-list-excerpt">More product details and features listed here...</div>
                                            </div>
                                            <div class="wrp-preview-list-actions">
                                                <div class="wrp-preview-list-button"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wrp-preview-desc"><?php _e( 'Vertical list with detailed product information', 'woocommerce-related-products' ); ?></div>
                            </div>
                            
                            <div class="wrp-preview wrp-preview-carousel <?php echo wrp_get_option( 'template', 'grid' ) === 'carousel' ? 'active' : ''; ?>">
                                <div class="wrp-preview-title"><?php _e( 'Carousel Layout', 'woocommerce-related-products' ); ?></div>
                                <div class="wrp-preview-content">
                                    <div class="wrp-preview-carousel-demo">
                                        <div class="wrp-preview-carousel-track">
                                            <div class="wrp-preview-carousel-item">
                                                <div class="wrp-preview-carousel-image"></div>
                                                <div class="wrp-preview-carousel-title">Product 1</div>
                                                <div class="wrp-preview-carousel-price">$29.99</div>
                                            </div>
                                            <div class="wrp-preview-carousel-item">
                                                <div class="wrp-preview-carousel-image"></div>
                                                <div class="wrp-preview-carousel-title">Product 2</div>
                                                <div class="wrp-preview-carousel-price">$39.99</div>
                                            </div>
                                            <div class="wrp-preview-carousel-item">
                                                <div class="wrp-preview-carousel-image"></div>
                                                <div class="wrp-preview-carousel-title">Product 3</div>
                                                <div class="wrp-preview-carousel-price">$49.99</div>
                                            </div>
                                            <div class="wrp-preview-carousel-item">
                                                <div class="wrp-preview-carousel-image"></div>
                                                <div class="wrp-preview-carousel-title">Product 4</div>
                                                <div class="wrp-preview-carousel-price">$59.99</div>
                                            </div>
                                        </div>
                                        <div class="wrp-preview-carousel-nav-left"></div>
                                        <div class="wrp-preview-carousel-nav-right"></div>
                                    </div>
                                </div>
                                <div class="wrp-preview-desc"><?php _e( 'Horizontal scrolling carousel for space efficiency', 'woocommerce-related-products' ); ?></div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_columns"><?php _e( 'Columns', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <select id="wrp_columns" name="wrp_columns">
                        <?php for ( $i = 1; $i <= 6; $i++ ) : ?>
                            <option value="<?php echo $i; ?>" <?php selected( wrp_get_option( 'columns', 4 ), $i ); ?>>
                                <?php echo sprintf( _n( '%d Column', '%d Columns', $i, 'woocommerce-related-products' ), $i ); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_image_size"><?php _e( 'Image Size', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <select id="wrp_image_size" name="wrp_image_size">
                        <?php
                        $image_sizes = get_intermediate_image_sizes();
                        foreach ( $image_sizes as $size ) :
                        ?>
                            <option value="<?php echo esc_attr( $size ); ?>" <?php selected( wrp_get_option( 'image_size', 'woocommerce_thumbnail' ), $size ); ?>>
                                <?php echo esc_html( $size ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_show_excerpt"><?php _e( 'Show Excerpt', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_excerpt" name="wrp_show_excerpt" 
                               value="1" <?php checked( wrp_get_option( 'show_excerpt', false ) ); ?>>
                        <?php _e( 'Display product excerpts', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render algorithm settings
     */
    private function render_algorithm_settings() {
        $weight = wrp_get_option( 'weight', array(
            'title' => 2,
            'description' => 1,
            'short_description' => 1,
            'tax' => array(
                'product_cat' => 3,
                'product_tag' => 2,
            ),
        ) );

        $require_tax = wrp_get_option( 'require_tax', array(
            'product_cat' => 1,
        ) );
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'Content Importance', 'woocommerce-related-products' ); ?></th>
                <td>
                    <p class="description"><?php _e( 'Set how important different content types are for finding related products.', 'woocommerce-related-products' ); ?></p>
                    
                    <table class="wrp-weight-table">
                        <tr>
                            <td><label for="wrp_weight_title"><?php _e( 'Product Title', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <select id="wrp_weight_title" name="wrp_weight[title]" class="regular-text">
                                    <option value="0" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 0 ); ?>>0% - Not Important</option>
                                    <option value="1" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 1 ); ?>>10% - Low Importance</option>
                                    <option value="2" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 2 ); ?>>20% - Medium Importance</option>
                                    <option value="3" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 3 ); ?>>30% - High Importance</option>
                                    <option value="4" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 4 ); ?>>40% - Very High Importance</option>
                                    <option value="5" <?php selected( isset( $weight['title'] ) ? $weight['title'] : 2, 5 ); ?>>50% - Critical</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="wrp_weight_description"><?php _e( 'Product Description', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <select id="wrp_weight_description" name="wrp_weight[description]" class="regular-text">
                                    <option value="0" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 0 ); ?>>0% - Not Important</option>
                                    <option value="1" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 1 ); ?>>10% - Low Importance</option>
                                    <option value="2" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 2 ); ?>>20% - Medium Importance</option>
                                    <option value="3" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 3 ); ?>>30% - High Importance</option>
                                    <option value="4" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 4 ); ?>>40% - Very High Importance</option>
                                    <option value="5" <?php selected( isset( $weight['description'] ) ? $weight['description'] : 1, 5 ); ?>>50% - Critical</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="wrp_weight_short_description"><?php _e( 'Short Description', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <select id="wrp_weight_short_description" name="wrp_weight[short_description]" class="regular-text">
                                    <option value="0" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 0 ); ?>>0% - Not Important</option>
                                    <option value="1" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 1 ); ?>>10% - Low Importance</option>
                                    <option value="2" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 2 ); ?>>20% - Medium Importance</option>
                                    <option value="3" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 3 ); ?>>30% - High Importance</option>
                                    <option value="4" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 4 ); ?>>40% - Very High Importance</option>
                                    <option value="5" <?php selected( isset( $weight['short_description'] ) ? $weight['short_description'] : 1, 5 ); ?>>50% - Critical</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'Category & Tag Importance', 'woocommerce-related-products' ); ?></th>
                <td>
                    <table class="wrp-weight-table">
                        <tr>
                            <td><label for="wrp_weight_tax_product_cat"><?php _e( 'Product Categories', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <select id="wrp_weight_tax_product_cat" name="wrp_weight[tax][product_cat]" class="regular-text">
                                    <option value="0" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 0 ); ?>>0% - Not Important</option>
                                    <option value="1" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 1 ); ?>>10% - Low Importance</option>
                                    <option value="2" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 2 ); ?>>20% - Medium Importance</option>
                                    <option value="3" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 3 ); ?>>30% - High Importance</option>
                                    <option value="4" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 4 ); ?>>40% - Very High Importance</option>
                                    <option value="5" <?php selected( isset( $weight['tax']['product_cat'] ) ? $weight['tax']['product_cat'] : 3, 5 ); ?>>50% - Critical</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="wrp_weight_tax_product_tag"><?php _e( 'Product Tags', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <select id="wrp_weight_tax_product_tag" name="wrp_weight[tax][product_tag]" class="regular-text">
                                    <option value="0" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 0 ); ?>>0% - Not Important</option>
                                    <option value="1" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 1 ); ?>>10% - Low Importance</option>
                                    <option value="2" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 2 ); ?>>20% - Medium Importance</option>
                                    <option value="3" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 3 ); ?>>30% - High Importance</option>
                                    <option value="4" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 4 ); ?>>40% - Very High Importance</option>
                                    <option value="5" <?php selected( isset( $weight['tax']['product_tag'] ) ? $weight['tax']['product_tag'] : 2, 5 ); ?>>50% - Critical</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'Taxonomy Requirements', 'woocommerce-related-products' ); ?></th>
                <td>
                    <p class="description"><?php _e( 'Require products to share at least this many terms from each taxonomy to be considered related.', 'woocommerce-related-products' ); ?></p>
                    
                    <table class="wrp-weight-table">
                        <tr>
                            <td><label for="wrp_require_tax_product_cat"><?php _e( 'Product Categories', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <input type="number" id="wrp_require_tax_product_cat" name="wrp_require_tax[product_cat]" 
                                       value="<?php echo esc_attr( isset( $require_tax['product_cat'] ) ? $require_tax['product_cat'] : 1 ); ?>" 
                                       min="0" max="5" step="1" class="small-text">
                            </td>
                        </tr>
                        <tr>
                            <td><label for="wrp_require_tax_product_tag"><?php _e( 'Product Tags', 'woocommerce-related-products' ); ?></label></td>
                            <td>
                                <input type="number" id="wrp_require_tax_product_tag" name="wrp_require_tax[product_tag]" 
                                       value="<?php echo esc_attr( isset( $require_tax['product_tag'] ) ? $require_tax['product_tag'] : 0 ); ?>" 
                                       min="0" max="5" step="1" class="small-text">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_exclude"><?php _e( 'Exclude Terms', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="text" id="wrp_exclude" name="wrp_exclude" 
                           value="<?php echo esc_attr( wrp_get_option( 'exclude', '' ) ); ?>" 
                           class="regular-text">
                    <p class="description"><?php _e( 'Comma-separated list of term IDs to exclude from related products.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_recent_only"><?php _e( 'Recent Products Only', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_recent_only" name="wrp_recent_only" 
                               value="1" <?php checked( wrp_get_option( 'recent_only', false ) ); ?>>
                        <?php _e( 'Only show products published recently', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_recent_period"><?php _e( 'Recent Period', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="text" id="wrp_recent_period" name="wrp_recent_period" 
                           value="<?php echo esc_attr( wrp_get_option( 'recent_period', '30 day' ) ); ?>" 
                           class="regular-text">
                    <p class="description"><?php _e( 'Time period for recent products (e.g., "30 day", "6 month", "1 year").', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_include_out_of_stock"><?php _e( 'Include Out of Stock', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_include_out_of_stock" name="wrp_include_out_of_stock" 
                               value="1" <?php checked( wrp_get_option( 'include_out_of_stock', false ) ); ?>>
                        <?php _e( 'Include out of stock products in results', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render cache page
     */
    public function render_cache_page() {
        global $wrp;
        
        if ( ! $wrp ) {
            return;
        }

        $cache_stats = $wrp->get_cache_stats();
        ?>
        <div class="wrap wrp-admin">
            <h1><?php _e( 'Cache Status', 'woocommerce-related-products' ); ?></h1>
            
            <div class="wrp-cache-stats">
                <h2><?php _e( 'Cache Statistics', 'woocommerce-related-products' ); ?></h2>
                
                <div class="wrp-stats-grid">
                    <div class="wrp-stat-item">
                        <h3><?php _e( 'Total Products', 'woocommerce-related-products' ); ?></h3>
                        <div class="wrp-stat-value"><?php echo number_format( $cache_stats['total_products'] ); ?></div>
                    </div>
                    
                    <div class="wrp-stat-item">
                        <h3><?php _e( 'Cached Products', 'woocommerce-related-products' ); ?></h3>
                        <div class="wrp-stat-value"><?php echo number_format( $cache_stats['cached_products'] ); ?></div>
                    </div>
                    
                    <div class="wrp-stat-item">
                        <h3><?php _e( 'Total Relations', 'woocommerce-related-products' ); ?></h3>
                        <div class="wrp-stat-value"><?php echo number_format( $cache_stats['total_relations'] ); ?></div>
                    </div>
                    
                    <div class="wrp-stat-item">
                        <h3><?php _e( 'Average Relations', 'woocommerce-related-products' ); ?></h3>
                        <div class="wrp-stat-value"><?php echo number_format( $cache_stats['avg_relations'], 2 ); ?></div>
                    </div>
                    
                    <div class="wrp-stat-item">
                        <h3><?php _e( 'Cache Percentage', 'woocommerce-related-products' ); ?></h3>
                        <div class="wrp-stat-value"><?php echo number_format( $cache_stats['cache_percentage'], 1 ); ?>%</div>
                    </div>
                </div>
            </div>
            
            <div class="wrp-cache-actions">
                <h2><?php _e( 'Cache Actions', 'woocommerce-related-products' ); ?></h2>
                
                <div class="wrp-actions-grid">
                    <button type="button" class="button button-secondary" id="wrp-clear-cache">
                        <?php _e( 'Clear Cache', 'woocommerce-related-products' ); ?>
                    </button>
                    
                    <button type="button" class="button button-secondary" id="wrp-rebuild-cache">
                        <?php _e( 'Rebuild Cache', 'woocommerce-related-products' ); ?>
                    </button>
                    
                    <button type="button" class="button button-secondary" id="wrp-optimize-cache">
                        <?php _e( 'Optimize Cache Table', 'woocommerce-related-products' ); ?>
                    </button>
                    
                    <button type="button" class="button button-primary" id="wrp-test-mode" onclick="handleTestModeClick(event)">
                        <?php _e( 'Test Mode (Simple Algorithm)', 'woocommerce-related-products' ); ?>
                    </button>
                </div>
                
                <div id="wrp-debug-info" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px; display: none;">
                    <strong>Debug Info:</strong>
                    <div id="wrp-debug-content"></div>
                </div>
                
                <!-- Fallback test button -->
                <div style="margin-top: 20px;">
                    <button type="button" class="button button-primary" onclick="handleTestModeClick(event)">
                        <?php _e( 'Fallback Test Button', 'woocommerce-related-products' ); ?>
                    </button>
                    <script>
                        function handleTestModeClick(event) {
                            event.preventDefault();
                            console.log('Test mode button clicked via HTML onclick');
                            
                            // Show confirmation
                            if (!confirm('Test mode will rebuild the cache using a simple algorithm to find any related products. This is for debugging purposes only. Continue?')) {
                                console.log('User cancelled test mode');
                                return;
                            }
                            
                            // Update debug info
                            var debugContent = document.getElementById('wrp-debug-content');
                            if (debugContent) {
                                debugContent.innerHTML += '<br>Starting test mode via HTML onclick...';
                            }
                            
                            // Show progress
                            var progress = document.querySelector('.wrp-cache-progress');
                            if (progress) {
                                progress.style.display = 'block';
                                var progressFill = progress.querySelector('.wrp-progress-fill');
                                var progressText = progress.querySelector('.wrp-progress-text');
                                if (progressFill) progressFill.style.width = '0%';
                                if (progressText) progressText.textContent = '0%';
                            }
                            
                            // Make AJAX request
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', wrp_admin_vars.ajax_url, true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 200) {
                                        try {
                                            var response = JSON.parse(xhr.responseText);
                                            console.log('Test mode response:', response);
                                            
                                            if (debugContent) {
                                                debugContent.innerHTML += '<br>Response: ' + JSON.stringify(response);
                                            }
                                            
                                            // Update progress
                                            if (progress) {
                                                var progressFill = progress.querySelector('.wrp-progress-fill');
                                                var progressText = progress.querySelector('.wrp-progress-text');
                                                if (progressFill) progressFill.style.width = '100%';
                                                if (progressText) progressText.textContent = '100%';
                                            }
                                            
                                            // Show message
                                            if (response.success) {
                                                alert('Success: ' + response.data.message);
                                            } else {
                                                alert('Error: ' + response.data.message);
                                            }
                                            
                                            // Reload page after delay
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 1500);
                                            
                                        } catch (e) {
                                            console.error('Error parsing response:', e);
                                            if (debugContent) {
                                                debugContent.innerHTML += '<br>Error parsing response: ' + e.message;
                                            }
                                            alert('Error processing response. Check console for details.');
                                        }
                                    } else {
                                        console.error('AJAX request failed:', xhr.status, xhr.statusText);
                                        if (debugContent) {
                                            debugContent.innerHTML += '<br>AJAX request failed: ' + xhr.status + ' ' + xhr.statusText;
                                        }
                                        alert('AJAX request failed. Check console for details.');
                                    }
                                }
                            };
                            
                            var data = 'action=wrp_test_mode&nonce=' + encodeURIComponent(wrp_admin_vars.nonce);
                            xhr.send(data);
                            
                            console.log('AJAX request sent for test mode');
                        }
                    </script>
                </div>
            </div>
            
            <div class="wrp-cache-progress" style="display: none;">
                <h3><?php _e( 'Cache Progress', 'woocommerce-related-products' ); ?></h3>
                <div class="wrp-progress-bar">
                    <div class="wrp-progress-fill"></div>
                </div>
                <div class="wrp-progress-text">0%</div>
            </div>
            
            <?php
            // Show debug information if available
            $debug_info = get_transient( 'wrp_cache_debug_info' );
            if ( $debug_info ) :
            ?>
            <div class="wrp-debug-info">
                <h3><?php _e( 'Debug Information', 'woocommerce-related-products' ); ?></h3>
                <div class="wrp-debug-content">
                    <h4><?php _e( 'Summary:', 'woocommerce-related-products' ); ?></h4>
                    <p><?php printf( __( 'Products with relations: %d out of %d', 'woocommerce-related-products' ), 
                        count(array_filter($debug_info, function($info) { return $info['related_count'] > 0; })), 
                        count($debug_info) ); 
                    ?></p>
                    
                    <h4><?php _e( 'Sample Products:', 'woocommerce-related-products' ); ?></h4>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e( 'Product ID', 'woocommerce-related-products' ); ?></th>
                                <th><?php _e( 'Product Name', 'woocommerce-related-products' ); ?></th>
                                <th><?php _e( 'Related Count', 'woocommerce-related-products' ); ?></th>
                                <th><?php _e( 'Related IDs', 'woocommerce-related-products' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( array_slice($debug_info, 0, 10) as $info ) : ?>
                            <tr>
                                <td><?php echo esc_html( $info['product_id'] ); ?></td>
                                <td><?php echo esc_html( $info['product_name'] ); ?></td>
                                <td><?php echo esc_html( $info['related_count'] ); ?></td>
                                <td><?php echo !empty( $info['related_ids'] ) ? implode(', ', $info['related_ids'] ) : 'None'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <p><em><?php _e( 'Debug information is stored temporarily and will expire.', 'woocommerce-related-products' ); ?></em></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        global $wrp;
        if ( $wrp ) {
            $wrp->get_cache()->clear_all();
            wp_send_json_success( array(
                'message' => __( 'Cache cleared successfully.', 'woocommerce-related-products' )
            ) );
        }

        wp_send_json_error( array(
            'message' => __( 'Failed to clear cache.', 'woocommerce-related-products' )
        ) );
    }

    /**
     * AJAX rebuild cache
     */
    public function ajax_rebuild_cache() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        global $wrp;
        if ( $wrp ) {
            $wrp->get_cache()->clear_all();
            
            // Get all products
            $products = wc_get_products( array(
                'status' => 'publish',
                'limit' => -1,
            ) );
            
            $total = count( $products );
            $processed = 0;
            $debug_info = array();
            
            // Process products in batches to avoid timeout
            $batch_size = 5; // Reduced batch size for better debugging
            $batches = array_chunk( $products, $batch_size );
            
            foreach ( $batches as $batch ) {
                foreach ( $batch as $product ) {
                    $product_id = $product->get_id();
                    $product_name = $product->get_name();
                    
                    // Get related products for debugging
                    $related_products = $wrp->get_related_products( $product_id );
                    $related_count = count( $related_products );
                    
                    $debug_info[] = array(
                        'product_id' => $product_id,
                        'product_name' => $product_name,
                        'related_count' => $related_count,
                        'related_ids' => $related_count > 0 ? array_map(function($p) { return $p->get_id(); }, $related_products) : array()
                    );
                    
                    $wrp->get_cache()->enforce_cache( $product->get_id() );
                    $processed++;
                }
                
                // Send progress update after each batch
                $progress = round( ( $processed / $total ) * 100 );
                
                // For AJAX, we need to send only one response at the end
                // Store progress in transient for status checking
                set_transient( 'wrp_cache_progress', array(
                    'progress' => $progress,
                    'processed' => $processed,
                    'total' => $total,
                    'message' => sprintf( __( 'Processed %d of %d products...', 'woocommerce-related-products' ), $processed, $total ),
                    'debug_sample' => array_slice($debug_info, -3) // Show last 3 products processed
                ), 60 );
                
                // Small delay to prevent server overload
                usleep( 200000 ); // 0.2 seconds
            }
            
            // Clear progress transient
            delete_transient( 'wrp_cache_progress' );
            
            // Store final debug info
            set_transient( 'wrp_cache_debug_info', $debug_info, 300 ); // Store for 5 minutes
            
            wp_send_json_success( array(
                'progress' => 100,
                'message' => __( 'Cache rebuilt successfully.', 'woocommerce-related-products' ),
                'processed' => $processed,
                'total' => $total,
                'debug_summary' => array(
                    'products_with_relations' => count(array_filter($debug_info, function($info) { return $info['related_count'] > 0; })),
                    'total_products_processed' => count($debug_info),
                    'sample_products' => array_slice($debug_info, 0, 5) // Show first 5 products
                )
            ) );
        }

        wp_send_json_error( array(
            'message' => __( 'Failed to rebuild cache.', 'woocommerce-related-products' )
        ) );
    }

    /**
     * AJAX cache progress check
     */
    public function ajax_cache_progress() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        $progress = get_transient( 'wrp_cache_progress' );
        
        if ( $progress ) {
            wp_send_json_success( $progress );
        } else {
            wp_send_json_success( array(
                'progress' => 100,
                'message' => __( 'Cache rebuild complete.', 'woocommerce-related-products' )
            ) );
        }
    }

    /**
     * AJAX optimize cache table
     */
    public function ajax_optimize_cache() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }

        global $wrp;
        if ( $wrp ) {
            $wrp->get_cache()->optimize();
            wp_send_json_success( array(
                'message' => __( 'Cache table optimized successfully.', 'woocommerce-related-products' )
            ) );
        }

        wp_send_json_error( array(
            'message' => __( 'Failed to optimize cache table.', 'woocommerce-related-products' )
        ) );
    }

    /**
     * AJAX test mode (simple algorithm)
     */
    public function ajax_test_mode() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        error_log('WRP Test Mode: Starting test mode with simple algorithm');
        
        // Get the WRP core instance
        global $wrp;
        if (!$wrp) {
            error_log('WRP Test Mode: WRP core instance not found');
            wp_send_json_error( array(
                'message' => 'WRP core instance not found'
            ) );
        }
        
        // Get some sample products to test
        $products = wc_get_products(array(
            'status' => 'publish',
            'limit' => 5,
        ));
        
        if (empty($products)) {
            error_log('WRP Test Mode: No published products found');
            wp_send_json_error( array(
                'message' => 'No published products found for testing'
            ) );
        }
        
        $test_results = array();
        $total_products_tested = 0;
        $total_related_found = 0;
        
        // Test each product with the simple algorithm
        foreach ($products as $product) {
            $product_id = $product->get_id();
            $product_title = $product->get_title();
            
            error_log("WRP Test Mode: Testing product ID: $product_id, Title: $product_title");
            
            // Get simple related products
            $simple_related = $wrp->get_simple_related_products($product_id);
            
            $test_results[] = array(
                'product_id' => $product_id,
                'product_title' => $product_title,
                'related_count' => count($simple_related),
                'related_products' => $simple_related
            );
            
            $total_products_tested++;
            $total_related_found += count($simple_related);
        }
        
        // Test cache functionality
        $cache = $wrp->get_cache();
        $cache_stats = array();
        if ($cache) {
            $cache_stats = $cache->get_cache_stats();
            error_log('WRP Test Mode: Cache stats retrieved');
        }
        
        // Log the results
        error_log("WRP Test Mode: Test completed. Products tested: $total_products_tested, Total related found: $total_related_found");
        
        wp_send_json_success( array(
            'message' => sprintf('Test mode completed! Tested %d products, found %d related products using simple algorithm.', 
                $total_products_tested, $total_related_found),
            'test_results' => $test_results,
            'cache_stats' => $cache_stats,
            'total_products_tested' => $total_products_tested,
            'total_related_found' => $total_related_found,
            'time' => date('Y-m-d H:i:s')
        ) );
    }

    /**
     * AJAX add to cart
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

        $added = WC()->cart->add_to_cart( $product_id );
        
        if ( $added ) {
            $fragments = array();
            $cart_hash = WC()->cart->get_cart_hash();
            
            // Add fragments if needed
            if ( function_exists( 'wc_get_cart_url' ) ) {
                $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . WC()->cart->get_cart_html() . '</div>';
            }
            
            wp_send_json_success( array(
                'message' => __( 'Product added to cart successfully.', 'woocommerce-related-products' ),
                'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', $fragments ),
                'cart_hash' => $cart_hash,
                'redirect' => apply_filters( 'woocommerce_add_to_cart_redirect', false )
            ) );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Failed to add product to cart.', 'woocommerce-related-products' )
            ) );
        }
    }
}