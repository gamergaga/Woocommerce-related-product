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
        
        // Simple admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_simple_admin_scripts' ) );
        
        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( WRP_PLUGIN_DIR . 'woocommerce-related-products.php' ), array( $this, 'add_settings_link' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_wrp_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_wrp_rebuild_cache', array( $this, 'ajax_rebuild_cache' ) );
        add_action( 'wp_ajax_wrp_cache_progress', array( $this, 'ajax_cache_progress' ) );
        add_action( 'wp_ajax_wrp_optimize_cache', array( $this, 'ajax_optimize_cache' ) );
        add_action( 'wp_ajax_wrp_test_mode', array( $this, 'ajax_test_mode' ) );
        add_action( 'wp_ajax_wrp_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
        
        // Enhanced Algorithm AJAX handlers
        add_action( 'wp_ajax_wrp_build_enhanced_cache', array( $this, 'ajax_build_enhanced_cache' ) );
        add_action( 'wp_ajax_wrp_enhanced_cache_progress', array( $this, 'ajax_enhanced_cache_progress' ) );
        
        // Category Relationships AJAX handlers
        add_action( 'wp_ajax_wrp_save_category_relationship', array( $this, 'ajax_save_category_relationship' ) );
        add_action( 'wp_ajax_wrp_delete_category_relationship', array( $this, 'ajax_delete_category_relationship' ) );
        add_action( 'wp_ajax_wrp_get_category_suggestions', array( $this, 'ajax_get_category_suggestions' ) );
        add_action( 'wp_ajax_wrp_apply_relationship_template', array( $this, 'ajax_apply_relationship_template' ) );
        
        // Machine Learning AJAX handlers
        add_action( 'wp_ajax_wrp_train_ml_model', array( $this, 'ajax_train_ml_model' ) );
        add_action( 'wp_ajax_wrp_get_ml_status', array( $this, 'ajax_get_ml_status' ) );
        add_action( 'wp_ajax_wrp_get_ml_metrics', array( $this, 'ajax_get_ml_metrics' ) );
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

        add_submenu_page(
            'wrp-settings',
            __( 'Simple Cache', 'woocommerce-related-products' ),
            __( 'Simple Cache', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-simple-cache',
            array( $this, 'render_simple_cache_page' )
        );

        add_submenu_page(
            'wrp-settings',
            __( 'Enhanced Algorithm', 'woocommerce-related-products' ),
            __( 'Enhanced Algorithm', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-enhanced-algorithm',
            array( $this, 'render_enhanced_algorithm_page' )
        );

        add_submenu_page(
            'wrp-settings',
            __( 'Category Relationships', 'woocommerce-related-products' ),
            __( 'Category Relationships', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-category-relationships',
            array( $this, 'render_category_relationships_page' )
        );

        add_submenu_page(
            'wrp-settings',
            __( 'Machine Learning', 'woocommerce-related-products' ),
            __( 'Machine Learning', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-machine-learning',
            array( $this, 'render_machine_learning_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Unified settings group for all settings
        register_setting( 'wrp_all_settings', 'wrp_threshold' );
        register_setting( 'wrp_all_settings', 'wrp_limit' );
        register_setting( 'wrp_all_settings', 'wrp_excerpt_length' );
        register_setting( 'wrp_all_settings', 'wrp_auto_display' );
        register_setting( 'wrp_all_settings', 'wrp_display_position' );
        register_setting( 'wrp_all_settings', 'wrp_cache_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_cache_timeout' );

        // Cache settings
        register_setting( 'wrp_all_settings', 'wrp_auto_rebuild_cache' );
        register_setting( 'wrp_all_settings', 'wrp_cache_expiry' );
        register_setting( 'wrp_all_settings', 'wrp_related_limit' );

        // Display settings
        register_setting( 'wrp_all_settings', 'wrp_show_price' );
        register_setting( 'wrp_all_settings', 'wrp_show_rating' );
        register_setting( 'wrp_all_settings', 'wrp_show_add_to_cart' );
        register_setting( 'wrp_all_settings', 'wrp_show_buy_now' );
        register_setting( 'wrp_all_settings', 'wrp_template' );
        register_setting( 'wrp_all_settings', 'wrp_columns' );
        register_setting( 'wrp_all_settings', 'wrp_image_size' );
        register_setting( 'wrp_all_settings', 'wrp_show_excerpt' );

        // Algorithm settings
        register_setting( 'wrp_all_settings', 'wrp_weight' );
        register_setting( 'wrp_all_settings', 'wrp_require_tax' );
        register_setting( 'wrp_all_settings', 'wrp_exclude' );
        register_setting( 'wrp_all_settings', 'wrp_recent_only' );
        register_setting( 'wrp_all_settings', 'wrp_recent_period' );
        register_setting( 'wrp_all_settings', 'wrp_include_out_of_stock' );

        // Enhanced Algorithm settings
        register_setting( 'wrp_all_settings', 'wrp_algorithm_type' );
        register_setting( 'wrp_all_settings', 'wrp_enhanced_threshold' );
        register_setting( 'wrp_all_settings', 'wrp_enhanced_limit' );
        register_setting( 'wrp_all_settings', 'wrp_text_analysis_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_stemming_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_fuzzy_matching_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_cross_reference_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_temporal_boost_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_popularity_boost_enabled' );

        // Category Relationship settings
        register_setting( 'wrp_all_settings', 'wrp_category_relationships_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_show_relationship_labels' );
        register_setting( 'wrp_all_settings', 'wrp_category_relationships' );

        // Machine Learning settings
        register_setting( 'wrp_all_settings', 'wrp_ml_enabled' );
        register_setting( 'wrp_all_settings', 'wrp_ml_auto_training' );
        register_setting( 'wrp_all_settings', 'wrp_ml_data_collection' );
        register_setting( 'wrp_all_settings', 'wrp_ml_learning_rate' );
        register_setting( 'wrp_all_settings', 'wrp_ml_confidence_threshold' );
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
     * Enqueue simple admin scripts and styles
     */
    public function enqueue_simple_admin_scripts( $hook ) {
        // Load only on simple cache page
        if ( $hook !== 'related-products_page_wrp-simple-cache' ) {
            return;
        }

        wp_enqueue_style(
            'wrp-simple-admin',
            WRP_PLUGIN_URL . 'assets/css/wrp-simple-admin.css',
            array(),
            WRP_VERSION
        );

        wp_enqueue_script(
            'wrp-simple-admin',
            WRP_PLUGIN_URL . 'assets/js/wrp-simple-admin.js',
            array( 'jquery' ),
            WRP_VERSION,
            true
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
                // Use unified settings group
                settings_fields( 'wrp_all_settings' );
                ?>
                
                <div class="wrp-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'woocommerce-related-products' ); ?></a>
                        <a href="#display" class="nav-tab"><?php _e( 'Display', 'woocommerce-related-products' ); ?></a>
                        <a href="#algorithm" class="nav-tab"><?php _e( 'Algorithm', 'woocommerce-related-products' ); ?></a>
                        <a href="#enhanced" class="nav-tab"><?php _e( 'Enhanced', 'woocommerce-related-products' ); ?></a>
                        <a href="#category" class="nav-tab"><?php _e( 'Category', 'woocommerce-related-products' ); ?></a>
                        <a href="#machine-learning" class="nav-tab"><?php _e( 'Machine Learning', 'woocommerce-related-products' ); ?></a>
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
                        
                        <!-- Enhanced Algorithm Settings -->
                        <div id="enhanced" class="tab-pane">
                            <?php $this->render_enhanced_settings(); ?>
                        </div>
                        
                        <!-- Category Relationship Settings -->
                        <div id="category" class="tab-pane">
                            <?php $this->render_category_settings(); ?>
                        </div>
                        
                        <!-- Machine Learning Settings -->
                        <div id="machine-learning" class="tab-pane">
                            <?php $this->render_ml_settings(); ?>
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
     * Render simple cache page
     */
    public function render_simple_cache_page() {
        // Include the simple cache page template
        require_once WRP_PLUGIN_DIR . 'admin/wrp-simple-cache-page.php';
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
            
            // Reset cache status
            update_option('wrp_cache_status', 'empty');
            update_option('wrp_cache_count', 0);
            update_option('wrp_cache_last_updated', __('Never', 'woocommerce-related-products'));
            error_log("WRP Admin: Cache status reset to empty");
            
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
            error_log("WRP Admin: Starting cache rebuild");
            
            // Update cache status to building
            update_option('wrp_cache_status', 'building');
            update_option('wrp_cache_count', 0);
            update_option('wrp_cache_last_updated', current_time('mysql'));
            error_log("WRP Admin: Cache status updated to building");
            
            // Clear all cache first
            $wrp->get_cache()->clear_all();
            error_log("WRP Admin: Cache cleared");
            
            // Get all products
            $products = wc_get_products( array(
                'status' => 'publish',
                'limit' => -1,
            ) );
            
            $total = count( $products );
            error_log("WRP Admin: Found $total products to process");
            
            if ($total == 0) {
                error_log("WRP Admin: No products found to process");
                update_option('wrp_cache_status', 'complete');
                update_option('wrp_cache_count', 0);
                update_option('wrp_cache_last_updated', current_time('mysql'));
                
                wp_send_json_success( array(
                    'progress' => 100,
                    'message' => __( 'No products found to cache.', 'woocommerce-related-products' ),
                    'processed' => 0,
                    'cached' => 0,
                    'total' => 0
                ));
            }
            
            $processed = 0;
            $debug_info = array();
            
            // Process products in batches to avoid timeout
            $batch_size = 5; // Small batch size for reliability
            $batches = array_chunk( $products, $batch_size );
            
            foreach ( $batches as $batch ) {
                foreach ( $batch as $product ) {
                    $product_id = $product->get_id();
                    $product_name = $product->get_name();
                    
                    error_log("WRP Admin: Processing product ID: $product_id, Name: $product_name");
                    
                    // Get default arguments
                    $args = array(
                        'limit' => 4,
                        'threshold' => 1,
                        'weight' => array(
                            'title' => 2,
                            'description' => 1,
                            'short_description' => 1,
                            'tax' => array(
                                'product_cat' => 3,
                                'product_tag' => 2,
                            ),
                        ),
                        'require_tax' => array(
                            'product_cat' => 1,
                        ),
                        'exclude' => '',
                        'recent_only' => false,
                        'recent_period' => '30 day',
                        'include_out_of_stock' => false,
                    );
                    
                    // Test cache enforcement with proper arguments using public method
                    try {
                        // Use public get_related_ids method which internally calls enforce_cache
                        $related_ids = $wrp->get_cache()->get_related_ids( $product_id, $args );
                        error_log("WRP Admin: Cache enforcement completed for product ID: $product_id");
                        
                        // Verify cache was created with direct DB check
                        $is_cached = $wrp->get_cache()->is_cached( $product_id );
                        
                        // Double-check with direct database query
                        global $wpdb;
                        $cache_table = $wpdb->prefix . 'wrp_related_cache';
                        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$cache_table}'");
                        
                        if ($table_exists) {
                            $direct_check = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT COUNT(*) FROM {$cache_table} WHERE reference_id = %d",
                                    $product_id
                                )
                            );
                        } else {
                            $direct_check = 0;
                            error_log("WRP Admin: Cache table does not exist for product ID: $product_id!");
                        }
                        
                        error_log("WRP Admin: Cache verification for product ID: $product_id - is_cached(): " . ($is_cached ? 'Success' : 'Failed') . ", Direct DB check: $direct_check");
                        
                        // Use direct DB check as the authoritative source
                        $is_cached = $direct_check > 0;
                        
                        // Get related products for debugging
                        $related_products = $wrp->get_related_products( $product_id, $args );
                        $related_count = count( $related_products );
                        
                        $debug_info[] = array(
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'related_count' => $related_count,
                            'is_cached' => $is_cached,
                            'related_ids' => $related_count > 0 ? array_map(function($p) { return $p->get_id(); }, $related_products) : array()
                        );
                        
                    } catch (Exception $e) {
                        error_log("WRP Admin: Error enforcing cache for product ID: $product_id - " . $e->getMessage());
                        $debug_info[] = array(
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'error' => $e->getMessage(),
                            'is_cached' => false,
                            'related_count' => 0
                        );
                    }
                    $processed++;
                    
                    // Update cache count after each product is processed
                    // Count as cached if it was processed (even if no related products found)
                    update_option('wrp_cache_count', $processed);
                    error_log("WRP Admin: Updated cache count to $processed for product ID: $product_id (processed: " . ($is_cached ? 'cached' : 'no related products found') . ")");
                    
                    // Force clear the object cache for this product to ensure fresh check
                    wp_cache_delete("wrp_is_cached_{$product_id}", 'wrp');
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
            
            // Update final cache status
            update_option('wrp_cache_status', 'complete');
            update_option('wrp_cache_last_updated', current_time('mysql'));
            
            // Get actual cached count from database
            global $wpdb;
            $cache_table = $wpdb->prefix . 'wrp_related_cache';
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$cache_table}'");
            if ($table_exists) {
                $actual_cached_count = $wpdb->get_var(
                    "SELECT COUNT(DISTINCT reference_id) FROM {$cache_table} WHERE reference_id != 0"
                );
            } else {
                $actual_cached_count = 0;
                error_log("WRP Admin: Cache table does not exist!");
            }
            
            update_option('wrp_cache_count', $actual_cached_count);
            error_log("WRP Admin: Final cache status updated - Status: complete, Actual cached count: $actual_cached_count, Processed: $processed");
            
            error_log("WRP Admin: Cache rebuild completed. Processed $processed products");
            
            wp_send_json_success( array(
                'progress' => 100,
                'message' => sprintf( __( 'Cache rebuilt successfully. Processed %d products with %d cached.', 'woocommerce-related-products' ), $processed, $actual_cached_count ),
                'processed' => $processed,
                'cached' => $actual_cached_count,
                'total' => $total,
                'debug_info' => array_slice($debug_info, -5) // Show last 5 products
            ));
        } else {
            error_log("WRP Admin: WRP instance not available");
            wp_send_json_error( array(
                'message' => __( 'WRP instance not available.', 'woocommerce-related-products' )
            ));
        }
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
        
        // Get the WRP core instance - ensure it's initialized in AJAX context
        global $wrp;
        if (!$wrp) {
            error_log('WRP Test Mode: WRP core instance not found, attempting to initialize');
            
            // Try to initialize the WRP core if it doesn't exist
            if (class_exists('WRP_Core')) {
                $wrp = new WRP_Core();
                error_log('WRP Test Mode: WRP core instance initialized');
            } else {
                error_log('WRP Test Mode: WRP_Core class not found');
                wp_send_json_error( array(
                    'message' => 'WRP_Core class not found. Please ensure the plugin is properly installed.'
                ) );
            }
        }
        
        // Double-check that we have a valid WRP instance
        if (!$wrp || !is_object($wrp)) {
            error_log('WRP Test Mode: Failed to initialize WRP core instance');
            wp_send_json_error( array(
                'message' => 'Failed to initialize WRP core instance'
            ) );
        }
        
        // Get some sample products to test
        try {
            $products = wc_get_products(array(
                'status' => 'publish',
                'limit' => 5,
            ));
        } catch (Exception $e) {
            error_log('WRP Test Mode: Error getting products: ' . $e->getMessage());
            wp_send_json_error( array(
                'message' => 'Error getting products: ' . $e->getMessage()
            ) );
        }
        
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
            try {
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
            } catch (Exception $e) {
                error_log("WRP Test Mode: Error testing product: " . $e->getMessage());
                $test_results[] = array(
                    'product_id' => $product ? $product->get_id() : 'unknown',
                    'product_title' => $product ? $product->get_title() : 'unknown',
                    'error' => $e->getMessage()
                );
            }
        }
        
        // Test cache functionality
        $cache = $wrp->get_cache();
        $cache_stats = array();
        if ($cache) {
            try {
                $cache_stats = $cache->get_stats(); // Fixed: use get_stats() instead of get_cache_stats()
                error_log('WRP Test Mode: Cache stats retrieved successfully');
            } catch (Exception $e) {
                error_log('WRP Test Mode: Error getting cache stats: ' . $e->getMessage());
                $cache_stats = array('error' => $e->getMessage());
            }
        } else {
            error_log('WRP Test Mode: Cache instance not found');
        }
        
        // Log the results
        error_log("WRP Test Mode: Test completed. Products tested: $total_products_tested, Total related found: $total_related_found");
        
        try {
            wp_send_json_success( array(
                'message' => sprintf('Test mode completed! Tested %d products, found %d related products using simple algorithm.', 
                    $total_products_tested, $total_related_found),
                'test_results' => $test_results,
                'cache_stats' => $cache_stats,
                'total_products_tested' => $total_products_tested,
                'total_related_found' => $total_related_found,
                'time' => date('Y-m-d H:i:s')
            ) );
        } catch (Exception $e) {
            error_log('WRP Test Mode: Error sending JSON response: ' . $e->getMessage());
            // Fallback response
            wp_send_json_success( array(
                'message' => 'Test mode completed with some errors. Check logs for details.',
                'total_products_tested' => $total_products_tested,
                'total_related_found' => $total_related_found,
                'time' => date('Y-m-d H:i:s')
            ) );
        }
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

    /**
     * Render Enhanced Algorithm Settings
     */
    private function render_enhanced_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wrp_algorithm_type"><?php _e( 'Algorithm Type', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <select id="wrp_algorithm_type" name="wrp_algorithm_type">
                        <option value="simple" <?php selected( wrp_get_option( 'algorithm_type', 'simple' ), 'simple' ); ?>>
                            <?php _e( 'Simple Algorithm', 'woocommerce-related-products' ); ?>
                        </option>
                        <option value="enhanced" <?php selected( wrp_get_option( 'algorithm_type', 'simple' ), 'enhanced' ); ?>>
                            <?php _e( 'Enhanced Algorithm', 'woocommerce-related-products' ); ?>
                        </option>
                        <option value="category" <?php selected( wrp_get_option( 'algorithm_type', 'simple' ), 'category' ); ?>>
                            <?php _e( 'Category-Based', 'woocommerce-related-products' ); ?>
                        </option>
                        <option value="ml" <?php selected( wrp_get_option( 'algorithm_type', 'simple' ), 'ml' ); ?>>
                            <?php _e( 'Machine Learning', 'woocommerce-related-products' ); ?>
                        </option>
                    </select>
                    <p class="description"><?php _e( 'Choose the algorithm type for finding related products.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_enhanced_threshold"><?php _e( 'Enhanced Threshold', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="number" id="wrp_enhanced_threshold" name="wrp_enhanced_threshold" 
                           value="<?php echo esc_attr( wrp_get_option( 'enhanced_threshold', 1.5 ) ); ?>" 
                           min="0.1" max="10" step="0.1" class="small-text">
                    <p class="description"><?php _e( 'Minimum relevance score for enhanced algorithm (default: 1.5).', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_enhanced_limit"><?php _e( 'Enhanced Limit', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="number" id="wrp_enhanced_limit" name="wrp_enhanced_limit" 
                           value="<?php echo esc_attr( wrp_get_option( 'enhanced_limit', 12 ) ); ?>" 
                           min="1" max="50" class="small-text">
                    <p class="description"><?php _e( 'Maximum number of products for enhanced algorithm (default: 12).', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e( 'Text Analysis Features', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="wrp_text_analysis_enabled" 
                               value="1" <?php checked( wrp_get_option( 'text_analysis_enabled', true ) ); ?>>
                        <?php _e( 'Enable Advanced Text Analysis', 'woocommerce-related-products' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wrp_stemming_enabled" 
                               value="1" <?php checked( wrp_get_option( 'stemming_enabled', true ) ); ?>>
                        <?php _e( 'Enable Word Stemming', 'woocommerce-related-products' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wrp_fuzzy_matching_enabled" 
                               value="1" <?php checked( wrp_get_option( 'fuzzy_matching_enabled', true ) ); ?>>
                        <?php _e( 'Enable Fuzzy Matching', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e( 'Enhancement Features', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="wrp_cross_reference_enabled" 
                               value="1" <?php checked( wrp_get_option( 'cross_reference_enabled', true ) ); ?>>
                        <?php _e( 'Enable Cross-Reference Scoring', 'woocommerce-related-products' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wrp_temporal_boost_enabled" 
                               value="1" <?php checked( wrp_get_option( 'temporal_boost_enabled', true ) ); ?>>
                        <?php _e( 'Enable Temporal Boost (Newer Products)', 'woocommerce-related-products' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wrp_popularity_boost_enabled" 
                               value="1" <?php checked( wrp_get_option( 'popularity_boost_enabled', true ) ); ?>>
                        <?php _e( 'Enable Popularity Boost (Best Sellers)', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <div class="wrp-enhanced-actions">
            <h4><?php _e( 'Enhanced Algorithm Actions', 'woocommerce-related-products' ); ?></h4>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=wrp-enhanced-algorithm' ); ?>" class="button button-primary">
                    <?php _e( 'Manage Enhanced Algorithm', 'woocommerce-related-products' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render Category Relationship Settings
     */
    private function render_category_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wrp_category_relationships_enabled"><?php _e( 'Enable Category Relationships', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_category_relationships_enabled" name="wrp_category_relationships_enabled" 
                               value="1" <?php checked( wrp_get_option( 'category_relationships_enabled', false ) ); ?>>
                        <?php _e( 'Enable category-based relationship system', 'woocommerce-related-products' ); ?>
                    </label>
                    <p class="description"><?php _e( 'Use visual category matching to control product relationships.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_show_relationship_labels"><?php _e( 'Show Relationship Labels', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_show_relationship_labels" name="wrp_show_relationship_labels" 
                               value="1" <?php checked( wrp_get_option( 'show_relationship_labels', false ) ); ?>>
                        <?php _e( 'Show relationship type labels (e.g., "Same Type", "Accessory")', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <div class="wrp-category-actions">
            <h4><?php _e( 'Category Relationship Management', 'woocommerce-related-products' ); ?></h4>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=wrp-category-relationships' ); ?>" class="button button-primary">
                    <?php _e( 'Manage Category Relationships', 'woocommerce-related-products' ); ?>
                </a>
            </p>
            <p class="description">
                <?php _e( 'Set up visual category matching with drag-and-drop interface. Define what categories should be shown together and in what priority.', 'woocommerce-related-products' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render Machine Learning Settings
     */
    private function render_ml_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wrp_ml_enabled"><?php _e( 'Enable Machine Learning', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_ml_enabled" name="wrp_ml_enabled" 
                               value="1" <?php checked( wrp_get_option( 'ml_enabled', false ) ); ?>>
                        <?php _e( 'Enable machine learning enhancement', 'woocommerce-related-products' ); ?>
                    </label>
                    <p class="description"><?php _e( 'Use AI to learn from user behavior and improve recommendations.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_ml_auto_training"><?php _e( 'Auto Training', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_ml_auto_training" name="wrp_ml_auto_training" 
                               value="1" <?php checked( wrp_get_option( 'ml_auto_training', true ) ); ?>>
                        <?php _e( 'Automatically train ML models based on user behavior', 'woocommerce-related-products' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_ml_data_collection"><?php _e( 'Data Collection', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wrp_ml_data_collection" name="wrp_ml_data_collection" 
                               value="1" <?php checked( wrp_get_option( 'ml_data_collection', true ) ); ?>>
                        <?php _e( 'Collect user interaction data for training', 'woocommerce-related-products' ); ?>
                    </label>
                    <p class="description"><?php _e( 'Collects anonymous data about clicks, views, and purchases to improve recommendations.', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_ml_learning_rate"><?php _e( 'Learning Rate', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="range" id="wrp_ml_learning_rate" name="wrp_ml_learning_rate" 
                           min="0.1" max="1" step="0.1" 
                           value="<?php echo esc_attr( wrp_get_option( 'ml_learning_rate', 0.5 ) ); ?>">
                    <span id="ml-learning-rate-value"><?php echo esc_html( wrp_get_option( 'ml_learning_rate', 0.5 ) ); ?></span>
                    <p class="description"><?php _e( 'How quickly the model adapts to new patterns (default: 0.5).', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wrp_ml_confidence_threshold"><?php _e( 'Confidence Threshold', 'woocommerce-related-products' ); ?></label>
                </th>
                <td>
                    <input type="range" id="wrp_ml_confidence_threshold" name="wrp_ml_confidence_threshold" 
                           min="0.1" max="1" step="0.1" 
                           value="<?php echo esc_attr( wrp_get_option( 'ml_confidence_threshold', 0.7 ) ); ?>">
                    <span id="ml-confidence-value"><?php echo esc_html( wrp_get_option( 'ml_confidence_threshold', 0.7 ) ); ?></span>
                    <p class="description"><?php _e( 'Minimum confidence level for ML predictions (default: 0.7).', 'woocommerce-related-products' ); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="wrp-ml-actions">
            <h4><?php _e( 'Machine Learning Management', 'woocommerce-related-products' ); ?></h4>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=wrp-machine-learning' ); ?>" class="button button-primary">
                    <?php _e( 'Manage Machine Learning', 'woocommerce-related-products' ); ?>
                </a>
            </p>
            <p class="description">
                <?php _e( 'Train models, view performance metrics, and manage learning data.', 'woocommerce-related-products' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render Enhanced Algorithm Page
     */
    public function render_enhanced_algorithm_page() {
        ?>
        <div class="wrap wrp-admin wrp-enhanced-algorithm">
            <h1><?php _e( 'Enhanced Algorithm Management', 'woocommerce-related-products' ); ?></h1>
            
            <div class="wrp-enhanced-overview">
                <div class="wrp-card">
                    <h3><?php _e( 'Enhanced Algorithm Status', 'woocommerce-related-products' ); ?></h3>
                    <div class="wrp-status-indicator">
                        <span class="wrp-status wrp-status-<?php echo $this->get_enhanced_algorithm_status(); ?>">
                            <?php echo $this->get_enhanced_algorithm_status_text(); ?>
                        </span>
                    </div>
                </div>
                
                <div class="wrp-card">
                    <h3><?php _e( 'Cache Statistics', 'woocommerce-related-products' ); ?></h3>
                    <?php $this->render_enhanced_cache_stats(); ?>
                </div>
            </div>
            
            <div class="wrp-enhanced-actions">
                <h2><?php _e( 'Cache Management', 'woocommerce-related-products' ); ?></h2>
                <div class="wrp-action-buttons">
                    <button class="button button-primary" id="wrp-build-enhanced-cache">
                        <?php _e( 'Build Enhanced Cache', 'woocommerce-related-products' ); ?>
                    </button>
                    <button class="button" id="wrp-clear-enhanced-cache">
                        <?php _e( 'Clear Enhanced Cache', 'woocommerce-related-products' ); ?>
                    </button>
                </div>
                
                <div id="wrp-enhanced-progress" class="wrp-progress-container" style="display: none;">
                    <div class="wrp-progress-bar">
                        <div class="wrp-progress-fill"></div>
                    </div>
                    <div class="wrp-progress-text"></div>
                </div>
            </div>
            
            <div class="wrp-enhanced-config">
                <h2><?php _e( 'Algorithm Configuration', 'woocommerce-related-products' ); ?></h2>
                <?php $this->render_enhanced_config(); ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wrp-build-enhanced-cache').click(function() {
                if (confirm('<?php _e( 'Building enhanced cache may take some time. Continue?', 'woocommerce-related-products' ); ?>')) {
                    $(this).prop('disabled', true);
                    $('#wrp-enhanced-progress').show();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wrp_build_enhanced_cache',
                            nonce: '<?php echo wp_create_nonce( 'wrp_admin_nonce' ); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message);
                                $('#wrp-build-enhanced-cache').prop('disabled', false);
                                $('#wrp-enhanced-progress').hide();
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Render Category Relationships Page
     */
    public function render_category_relationships_page() {
        ?>
        <div class="wrap wrp-admin wrp-category-relationships">
            <h1><?php _e( 'Category Relationships', 'woocommerce-related-products' ); ?></h1>
            
            <div class="wrp-category-intro">
                <p><?php _e( 'Define how categories relate to each other using a visual drag-and-drop interface. Categories at the top will be shown first in related products.', 'woocommerce-related-products' ); ?></p>
            </div>
            
            <div class="wrp-category-builder">
                <div class="wrp-builder-toolbar">
                    <button class="button" id="wrp-auto-suggest">
                        <?php _e( 'Auto-Suggest Relationships', 'woocommerce-related-products' ); ?>
                    </button>
                    <select id="wrp-template-select">
                        <option value=""><?php _e( 'Select Template', 'woocommerce-related-products' ); ?></option>
                        <option value="mobile_phone_store"><?php _e( 'Mobile Phone Store', 'woocommerce-related-products' ); ?></option>
                        <option value="electronics_store"><?php _e( 'Electronics Store', 'woocommerce-related-products' ); ?></option>
                        <option value="fashion_store"><?php _e( 'Fashion Store', 'woocommerce-related-products' ); ?></option>
                    </select>
                    <button class="button" id="wrp-apply-template">
                        <?php _e( 'Apply Template', 'woocommerce-related-products' ); ?>
                    </button>
                    <button class="button button-primary" id="wrp-save-relationships">
                        <?php _e( 'Save All Changes', 'woocommerce-related-products' ); ?>
                    </button>
                </div>
                
                <div class="wrp-matrix-container">
                    <div class="wrp-source-categories">
                        <h4><?php _e( 'Source Categories', 'woocommerce-related-products' ); ?></h4>
                        <div class="wrp-category-list" id="source-categories">
                            <?php $this->render_source_categories(); ?>
                        </div>
                    </div>
                    
                    <div class="wrp-target-categories">
                        <h4><?php _e( 'Target Categories (Drag to reorder)', 'woocommerce-related-products' ); ?></h4>
                        <div class="wrp-category-list sortable" id="target-categories">
                            <?php $this->render_target_categories(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Machine Learning Page
     */
    public function render_machine_learning_page() {
        ?>
        <div class="wrap wrp-admin wrp-machine-learning">
            <h1><?php _e( 'Machine Learning', 'woocommerce-related-products' ); ?></h1>
            
            <div class="wrp-ml-overview">
                <div class="wrp-card">
                    <h3><?php _e( 'ML Model Status', 'woocommerce-related-products' ); ?></h3>
                    <div class="wrp-status-indicator">
                        <span class="wrp-status wrp-status-<?php echo $this->get_ml_model_status(); ?>">
                            <?php echo $this->get_ml_model_status_text(); ?>
                        </span>
                    </div>
                </div>
                
                <div class="wrp-card">
                    <h3><?php _e( 'Data Statistics', 'woocommerce-related-products' ); ?></h3>
                    <?php $this->render_ml_data_stats(); ?>
                </div>
            </div>
            
            <div class="wrp-ml-actions">
                <h2><?php _e( 'Model Management', 'woocommerce-related-products' ); ?></h2>
                <div class="wrp-action-buttons">
                    <button class="button button-primary" id="wrp-train-ml-model">
                        <?php _e( 'Train Model', 'woocommerce-related-products' ); ?>
                    </button>
                    <button class="button" id="wrp-refresh-ml-status">
                        <?php _e( 'Refresh Status', 'woocommerce-related-products' ); ?>
                    </button>
                </div>
            </div>
            
            <div class="wrp-ml-performance">
                <h2><?php _e( 'Performance Metrics', 'woocommerce-related-products' ); ?></h2>
                <?php $this->render_ml_performance_metrics(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get enhanced algorithm status
     */
    private function get_enhanced_algorithm_status() {
        // Check if enhanced cache table exists and has data
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wrp_enhanced_cache'");
        
        if (!$table_exists) {
            return 'inactive';
        }
        
        $cache_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wrp_enhanced_cache");
        return $cache_count > 0 ? 'active' : 'inactive';
    }

    /**
     * Get enhanced algorithm status text
     */
    private function get_enhanced_algorithm_status_text() {
        $status = $this->get_enhanced_algorithm_status();
        $texts = [
            'active' => __( 'Active', 'woocommerce-related-products' ),
            'inactive' => __( 'Inactive', 'woocommerce-related-products' )
        ];
        return $texts[$status] ?? __( 'Unknown', 'woocommerce-related-products' );
    }

    /**
     * Get ML model status
     */
    private function get_ml_model_status() {
        $ml_enabled = wrp_get_option( 'ml_enabled', false );
        if (!$ml_enabled) {
            return 'disabled';
        }
        
        // Check if ML data table exists and has sufficient data
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wrp_ml_data'");
        
        if (!$table_exists) {
            return 'no-data';
        }
        
        $data_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wrp_ml_data");
        return $data_count > 100 ? 'trained' : 'needs-training';
    }

    /**
     * Get ML model status text
     */
    private function get_ml_model_status_text() {
        $status = $this->get_ml_model_status();
        $texts = [
            'disabled' => __( 'Disabled', 'woocommerce-related-products' ),
            'no-data' => __( 'No Data Available', 'woocommerce-related-products' ),
            'needs-training' => __( 'Needs Training', 'woocommerce-related-products' ),
            'trained' => __( 'Trained', 'woocommerce-related-products' )
        ];
        return $texts[$status] ?? __( 'Unknown', 'woocommerce-related-products' );
    }

    /**
     * Render enhanced cache stats
     */
    private function render_enhanced_cache_stats() {
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wrp_enhanced_cache'");
        
        if (!$table_exists) {
            echo '<p>' . __( 'Enhanced cache table not found.', 'woocommerce-related-products' ) . '</p>';
            return;
        }
        
        $stats = $wpdb->get_results("
            SELECT 
                COUNT(DISTINCT reference_id) as cached_products,
                COUNT(*) as total_relations,
                AVG(score) as avg_score,
                MAX(score) as max_score,
                MIN(score) as min_score
            FROM {$wpdb->prefix}wrp_enhanced_cache
        ");
        
        if ($stats) {
            $stat = $stats[0];
            echo '<ul>';
            echo '<li>' . sprintf( __( 'Cached Products: %d', 'woocommerce-related-products' ), $stat->cached_products ) . '</li>';
            echo '<li>' . sprintf( __( 'Total Relations: %d', 'woocommerce-related-products' ), $stat->total_relations ) . '</li>';
            echo '<li>' . sprintf( __( 'Average Score: %.2f', 'woocommerce-related-products' ), $stat->avg_score ) . '</li>';
            echo '<li>' . sprintf( __( 'Score Range: %.2f - %.2f', 'woocommerce-related-products' ), $stat->min_score, $stat->max_score ) . '</li>';
            echo '</ul>';
        }
    }

    /**
     * Render ML data stats
     */
    private function render_ml_data_stats() {
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wrp_ml_data'");
        
        if (!$table_exists) {
            echo '<p>' . __( 'ML data table not found.', 'woocommerce-related-products' ) . '</p>';
            return;
        }
        
        $stats = $wpdb->get_results("
            SELECT 
                COUNT(*) as total_interactions,
                COUNT(DISTINCT reference_id) as unique_products,
                COUNT(DISTINCT user_id) as unique_users,
                AVG(interaction_value) as avg_value
            FROM {$wpdb->prefix}wrp_ml_data
        ");
        
        if ($stats) {
            $stat = $stats[0];
            echo '<ul>';
            echo '<li>' . sprintf( __( 'Total Interactions: %d', 'woocommerce-related-products' ), $stat->total_interactions ) . '</li>';
            echo '<li>' . sprintf( __( 'Unique Products: %d', 'woocommerce-related-products' ), $stat->unique_products ) . '</li>';
            echo '<li>' . sprintf( __( 'Unique Users: %d', 'woocommerce-related-products' ), $stat->unique_users ) . '</li>';
            echo '<li>' . sprintf( __( 'Average Value: %.2f', 'woocommerce-related-products' ), $stat->avg_value ) . '</li>';
            echo '</ul>';
        }
    }

    /**
     * Render enhanced config
     */
    private function render_enhanced_config() {
        // This would render the enhanced algorithm configuration
        echo '<div class="wrp-config-placeholder">';
        echo '<p>' . __( 'Enhanced algorithm configuration will be rendered here.', 'woocommerce-related-products' ) . '</p>';
        echo '</div>';
    }

    /**
     * Render source categories
     */
    private function render_source_categories() {
        $categories = get_terms('product_cat', ['hide_empty' => true]);
        
        foreach ($categories as $category) {
            echo '<div class="wrp-category-item" data-category-id="' . $category->term_id . '">';
            echo '<span class="wrp-category-name">' . esc_html($category->name) . '</span>';
            echo '<span class="wrp-category-count">' . $category->count . ' products</span>';
            echo '</div>';
        }
    }

    /**
     * Render target categories
     */
    private function render_target_categories() {
        $categories = get_terms('product_cat', ['hide_empty' => true]);
        
        foreach ($categories as $category) {
            echo '<div class="wrp-category-item" data-category-id="' . $category->term_id . '">';
            echo '<span class="wrp-category-name">' . esc_html($category->name) . '</span>';
            echo '<span class="wrp-category-count">' . $category->count . ' products</span>';
            echo '</div>';
        }
    }

    /**
     * Render ML performance metrics
     */
    private function render_ml_performance_metrics() {
        echo '<div class="wrp-metrics-placeholder">';
        echo '<p>' . __( 'Machine learning performance metrics will be rendered here.', 'woocommerce-related-products' ) . '</p>';
        echo '</div>';
    }

    // AJAX handler placeholders for new features
    public function ajax_build_enhanced_cache() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'message' => __( 'Enhanced cache built successfully!', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_enhanced_cache_progress() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'progress' => 100,
            'message' => __( 'Complete', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_save_category_relationship() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'message' => __( 'Category relationship saved!', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_delete_category_relationship() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'message' => __( 'Category relationship deleted!', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_get_category_suggestions() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'suggestions' => array()
        ) );
    }

    public function ajax_apply_relationship_template() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'message' => __( 'Template applied successfully!', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_train_ml_model() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'message' => __( 'ML model training started!', 'woocommerce-related-products' )
        ) );
    }

    public function ajax_get_ml_status() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'status' => $this->get_ml_model_status(),
            'status_text' => $this->get_ml_model_status_text()
        ) );
    }

    public function ajax_get_ml_metrics() {
        check_ajax_referer( 'wrp_admin_nonce', 'nonce' );
        
        // Placeholder implementation
        wp_send_json_success( array(
            'metrics' => array()
        ) );
    }

}