<?php
/**
 * YARPP-style Admin class for WooCommerce Related Products Pro
 * Handles cache management with robust AJAX handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_YARPP_Admin {

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
        
        // AJAX handlers for cache management
        add_action( 'wp_ajax_wrp_yarpp_build_cache', array( $this, 'ajax_build_cache' ) );
        add_action( 'wp_ajax_wrp_yarpp_cache_status', array( $this, 'ajax_cache_status' ) );
        add_action( 'wp_ajax_wrp_yarpp_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_wrp_yarpp_rebuild_product', array( $this, 'ajax_rebuild_product' ) );
        
        // Enqueue admin scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Related Products Cache', 'woocommerce-related-products' ),
            __( 'Related Products Cache', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-yarpp-cache',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap wrp-yarpp-admin">
            <h1><?php esc_html_e( 'WooCommerce Related Products Pro - Cache Management', 'woocommerce-related-products' ); ?></h1>
            
            <div class="notice notice-info">
                <p><?php esc_html_e( 'This cache system is based on YARPP\'s proven methodology, using advanced scoring algorithms and batch processing for optimal performance.', 'woocommerce-related-products' ); ?></p>
            </div>
            
            <div class="wrp-yarpp-actions">
                <button id="wrp-yarpp-build-cache" class="button button-primary">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e( 'Build Cache', 'woocommerce-related-products' ); ?>
                </button>
                
                <button id="wrp-yarpp-check-status" class="button">
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e( 'Check Status', 'woocommerce-related-products' ); ?>
                </button>
                
                <button id="wrp-yarpp-clear-cache" class="button">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e( 'Clear Cache', 'woocommerce-related-products' ); ?>
                </button>
            </div>
            
            <div id="wrp-yarpp-progress" class="wrp-yarpp-progress">
                <div class="progress-bar-container">
                    <div class="progress-bar"></div>
                </div>
                <div class="progress-text">0%</div>
            </div>
            
            <div id="wrp-yarpp-status" class="wrp-yarpp-status">
                <!-- Status will be loaded here -->
            </div>
            
            <div class="wrp-yarpp-info">
                <div class="card">
                    <h3><?php esc_html_e( 'How It Works', 'woocommerce-related-products' ); ?></h3>
                    <p><strong><?php esc_html_e( 'YARPP-style Algorithm:', 'woocommerce-related-products' ); ?></strong></p>
                    <ul>
                        <li><?php esc_html_e( 'Calculates similarity scores based on title, content, categories, and tags', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Uses configurable weights for different factors', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Applies threshold filtering to ensure quality', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Processes products in batches for optimal performance', 'woocommerce-related-products' ); ?></li>
                    </ul>
                    
                    <p><strong><?php esc_html_e( 'Scoring Factors:', 'woocommerce-related-products' ); ?></strong></p>
                    <ul>
                        <li><?php esc_html_e( 'Title similarity (word-based matching)', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Content similarity (Jaccard algorithm)', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Category overlap (weighted importance)', 'woocommerce-related-products' ); ?></li>
                        <li><?php esc_html_e( 'Tag similarity (shared tags)', 'woocommerce-related-products' ); ?></li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3><?php esc_html_e( 'Configuration', 'woocommerce-related-products' ); ?></h3>
                    <p><strong><?php esc_html_e( 'Current Settings:', 'woocommerce-related-products' ); ?></strong></p>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Threshold', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_threshold', 5 ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Limit', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_limit', 4 ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Title Weight', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_weight_title', 2 ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Content Weight', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_weight_content', 1 ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Category Weight', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_weight_categories', 3 ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Tag Weight', 'woocommerce-related-products' ); ?></th>
                            <td><?php echo esc_html( get_option( 'wrp_weight_tags', 2 ) ); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        var wrp_yarpp_admin_vars = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('wrp_yarpp_admin_nonce'); ?>'
        };
        </script>
        <?php
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wrp-yarpp-cache') === false) {
            return;
        }
        
        wp_enqueue_style('wrp-yarpp-admin', plugins_url('assets/css/wrp-yarpp-admin.css', dirname(dirname(__FILE__))));
        wp_enqueue_script('wrp-yarpp-admin', plugins_url('assets/js/wrp-yarpp-admin.js', dirname(dirname(__FILE__))), array('jquery'), '1.0.0', true);
        
        wp_localize_script('wrp-yarpp-admin', 'wrp_yarpp_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wrp_yarpp_admin_nonce')
        ));
    }

    /**
     * AJAX build cache
     */
    public function ajax_build_cache() {
        // Start output buffering to catch any unwanted output
        ob_start();
        
        check_ajax_referer('wrp_yarpp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        error_log('WRP YARPP Admin: Starting cache build');

        try {
            $cache = new WRP_YARPP_Cache();
            $result = $cache->build_cache();
            
            // Clear any output that might have been generated
            ob_end_clean();
            
            if ($result['success']) {
                error_log('WRP YARPP Admin: Cache build completed successfully');
                wp_send_json_success(array(
                    'message' => $result['message'],
                    'processed' => $result['processed'],
                    'total_relations' => $result['total_relations'],
                    'errors' => $result['errors']
                ));
            } else {
                error_log('WRP YARPP Admin: Cache build failed');
                wp_send_json_error(array(
                    'message' => $result['message']
                ));
            }
            
        } catch (Exception $e) {
            // Clear any output that might have been generated
            ob_end_clean();
            
            error_log('WRP YARPP Admin: Exception during cache build: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error during cache build: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX cache status
     */
    public function ajax_cache_status() {
        // Start output buffering to catch any unwanted output
        ob_start();
        
        check_ajax_referer('wrp_yarpp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        try {
            $cache = new WRP_YARPP_Cache();
            $stats = $cache->get_stats();
            $table_status = $cache->get_table_status();
            
            // Clear any output that might have been generated
            ob_end_clean();
            
            wp_send_json_success(array(
                'stats' => $stats,
                'table_status' => $table_status,
                'message' => sprintf(
                    'Cache Status: %s | Products: %d/%d (%.1f%%) | Relations: %d | Avg: %.1f per product | Avg Score: %.1f',
                    ucfirst($table_status),
                    $stats['cached_products'],
                    $stats['total_products'],
                    $stats['cache_percentage'],
                    $stats['total_relations'],
                    $stats['avg_relations'],
                    $stats['avg_score']
                )
            ));
            
        } catch (Exception $e) {
            // Clear any output that might have been generated
            ob_end_clean();
            
            error_log('WRP YARPP Admin: Exception getting cache status: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error getting cache status: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        // Start output buffering to catch any unwanted output
        ob_start();
        
        check_ajax_referer('wrp_yarpp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        try {
            $cache = new WRP_YARPP_Cache();
            $result = $cache->clear_cache();
            
            // Clear any output that might have been generated
            ob_end_clean();
            
            if ($result) {
                error_log('WRP YARPP Admin: Cache cleared successfully');
                wp_send_json_success(array(
                    'message' => 'Cache cleared successfully!'
                ));
            } else {
                error_log('WRP YARPP Admin: Cache clear failed');
                wp_send_json_error(array(
                    'message' => 'Failed to clear cache.'
                ));
            }
            
        } catch (Exception $e) {
            // Clear any output that might have been generated
            ob_end_clean();
            
            error_log('WRP YARPP Admin: Exception clearing cache: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX rebuild product cache
     */
    public function ajax_rebuild_product() {
        // Start output buffering to catch any unwanted output
        ob_start();
        
        check_ajax_referer('wrp_yarpp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if ($product_id <= 0) {
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Invalid product ID.'
            ));
        }

        try {
            $cache = new WRP_YARPP_Cache();
            $count = $cache->rebuild_product($product_id);
            
            // Clear any output that might have been generated
            ob_end_clean();
            
            wp_send_json_success(array(
                'message' => sprintf('Product %d cache rebuilt successfully. Found %d related products.', $product_id, $count),
                'product_id' => $product_id,
                'count' => $count
            ));
            
        } catch (Exception $e) {
            // Clear any output that might have been generated
            ob_end_clean();
            
            error_log('WRP YARPP Admin: Exception rebuilding product cache: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error rebuilding product cache: ' . $e->getMessage()
            ));
        }
    }
}
?>