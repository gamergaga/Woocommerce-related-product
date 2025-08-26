<?php
/**
 * Enhanced Admin class for WooCommerce Related Products Pro
 * Provides advanced algorithm configuration and cache management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Enhanced_Admin {

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
        
        // AJAX handlers for enhanced cache management
        add_action( 'wp_ajax_wrp_enhanced_build_cache', array( $this, 'ajax_build_cache' ) );
        add_action( 'wp_ajax_wrp_enhanced_cache_status', array( $this, 'ajax_cache_status' ) );
        add_action( 'wp_ajax_wrp_enhanced_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_wrp_enhanced_rebuild_product', array( $this, 'ajax_rebuild_product' ) );
        add_action( 'wp_ajax_wrp_enhanced_save_settings', array( $this, 'ajax_save_settings' ) );
        
        // Enqueue admin scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Related Products Algorithm', 'woocommerce-related-products' ),
            __( 'Related Products Algorithm', 'woocommerce-related-products' ),
            'manage_options',
            'wrp-enhanced-admin',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap wrp-enhanced-admin">
            <h1><?php esc_html_e( 'WooCommerce Related Products Pro - Enhanced Algorithm', 'woocommerce-related-products' ); ?></h1>
            
            <div class="notice notice-info">
                <p><?php esc_html_e( 'Enhanced Algorithm based on YARPP\'s proven methodology with advanced text analysis, multi-factor scoring, and intelligent caching.', 'woocommerce-related-products' ); ?></p>
            </div>
            
            <div class="wrp-enhanced-tabs">
                <div class="nav-tab-wrapper">
                    <a href="#cache" class="nav-tab nav-tab-active"><?php esc_html_e( 'Cache Management', 'woocommerce-related-products' ); ?></a>
                    <a href="#algorithm" class="nav-tab"><?php esc_html_e( 'Algorithm Settings', 'woocommerce-related-products' ); ?></a>
                    <a href="#weights" class="nav-tab"><?php esc_html_e( 'Scoring Weights', 'woocommerce-related-products' ); ?></a>
                    <a href="#text-analysis" class="nav-tab"><?php esc_html_e( 'Text Analysis', 'woocommerce-related-products' ); ?></a>
                    <a href="#stats" class="nav-tab"><?php esc_html_e( 'Statistics', 'woocommerce-related-products' ); ?></a>
                </div>
                
                <div class="tab-content">
                    <!-- Cache Management Tab -->
                    <div id="cache" class="tab-pane active">
                        <div class="wrp-enhanced-actions">
                            <button id="wrp-enhanced-build-cache" class="button button-primary">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e( 'Build Enhanced Cache', 'woocommerce-related-products' ); ?>
                            </button>
                            
                            <button id="wrp-enhanced-check-status" class="button">
                                <span class="dashicons dashicons-info"></span>
                                <?php esc_html_e( 'Check Status', 'woocommerce-related-products' ); ?>
                            </button>
                            
                            <button id="wrp-enhanced-clear-cache" class="button">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e( 'Clear Cache', 'woocommerce-related-products' ); ?>
                            </button>
                        </div>
                        
                        <div id="wrp-enhanced-progress" class="wrp-enhanced-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar"></div>
                            </div>
                            <div class="progress-text">0%</div>
                        </div>
                        
                        <div id="wrp-enhanced-status" class="wrp-enhanced-status">
                            <!-- Status will be loaded here -->
                        </div>
                        
                        <div class="wrp-enhanced-info">
                            <div class="card">
                                <h3><?php esc_html_e( 'Enhanced Cache Features', 'woocommerce-related-products' ); ?></h3>
                                <ul>
                                    <li><?php esc_html_e( 'Advanced text analysis with stop words and stemming', 'woocommerce-related-products' ); ?></li>
                                    <li><?php esc_html_e( 'Multi-factor scoring (title, content, categories, tags, attributes)', 'woocommerce-related-products' ); ?></li>
                                    <li><?php esc_html_e( 'Fuzzy matching for partial word matches', 'woocommerce-related-products' ); ?></li>
                                    <li><?php esc_html_e( 'Temporal and popularity boosts', 'woocommerce-related-products' ); ?></li>
                                    <li><?php esc_html_e( 'Intelligent candidate selection', 'woocommerce-related-products' ); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Algorithm Settings Tab -->
                    <div id="algorithm" class="tab-pane">
                        <form id="wrp-enhanced-settings-form" class="wrp-enhanced-settings-form">
                            <div class="card">
                                <h3><?php esc_html_e( 'Basic Algorithm Settings', 'woocommerce-related-products' ); ?></h3>
                                
                                <table class="form-table">
                                    <tr>
                                        <th><?php esc_html_e( 'Threshold', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="threshold" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_threshold', 1.5 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Minimum score required for products to be considered related. Lower values = more results.', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Limit', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" min="1" max="50" name="limit" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_limit', 12 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Maximum number of related products to display.', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Cross-Reference', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="cross_reference" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_cross_reference', 1 ) ); ?>>
                                                <?php esc_html_e( 'Enable bidirectional relationship scoring', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Temporal Boost', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="temporal_boost" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_temporal_boost', 1 ) ); ?>>
                                                <?php esc_html_e( 'Boost recently published products', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Popularity Boost', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="popularity_boost" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_popularity_boost', 1 ) ); ?>>
                                                <?php esc_html_e( 'Boost products with higher sales', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Category Boost', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="category_boost" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_category_boost', 1 ) ); ?>>
                                                <?php esc_html_e( 'Boost products in same categories', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p class="submit">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e( 'Save Settings', 'woocommerce-related-products' ); ?>
                                    </button>
                                </p>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Scoring Weights Tab -->
                    <div id="weights" class="tab-pane">
                        <form id="wrp-enhanced-weights-form" class="wrp-enhanced-settings-form">
                            <div class="card">
                                <h3><?php esc_html_e( 'Scoring Weights Configuration', 'woocommerce-related-products' ); ?></h3>
                                <p><?php esc_html_e( 'Adjust the importance of each factor in determining related products. Higher values = more important.', 'woocommerce-related-products' ); ?></p>
                                
                                <table class="form-table">
                                    <tr>
                                        <th><?php esc_html_e( 'Title Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_title" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_title', 3.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of title similarity', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Content Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_content" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_content', 2.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of content/description similarity', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Categories Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_categories" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_categories', 4.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of shared categories', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Tags Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_tags" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_tags', 3.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of shared tags', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Attributes Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_attributes" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_attributes', 2.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of shared attributes', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Price Range Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_price_range" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_price_range', 1.0 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of similar price range', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Brand Weight', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="weight_brand" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_weight_brand', 2.5 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Importance of same brand', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p class="submit">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e( 'Save Weights', 'woocommerce-related-products' ); ?>
                                    </button>
                                </p>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Text Analysis Tab -->
                    <div id="text-analysis" class="tab-pane">
                        <form id="wrp-enhanced-text-form" class="wrp-enhanced-settings-form">
                            <div class="card">
                                <h3><?php esc_html_e( 'Text Analysis Settings', 'woocommerce-related-products' ); ?></h3>
                                
                                <table class="form-table">
                                    <tr>
                                        <th><?php esc_html_e( 'Minimum Word Length', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <input type="number" min="1" max="10" name="min_word_length" 
                                                   value="<?php echo esc_attr( get_option( 'wrp_enhanced_min_word_length', 3 ) ); ?>" 
                                                   class="small-text">
                                            <p class="description"><?php esc_html_e( 'Minimum characters for a word to be considered in analysis', 'woocommerce-related-products' ); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Use Stop Words', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="use_stop_words" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_use_stop_words', 1 ) ); ?>>
                                                <?php esc_html_e( 'Filter out common stop words (the, and, or, etc.)', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Use Stemming', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="use_stemming" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_use_stemming', 1 ) ); ?>>
                                                <?php esc_html_e( 'Apply word stemming (running -> run)', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th><?php esc_html_e( 'Fuzzy Matching', 'woocommerce-related-products' ); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="fuzzy_matching" value="1" 
                                                       <?php checked( get_option( 'wrp_enhanced_fuzzy_matching', 1 ) ); ?>>
                                                <?php esc_html_e( 'Enable fuzzy matching for partial word matches', 'woocommerce-related-products' ); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="card">
                                    <h4><?php esc_html_e( 'Stop Words List', 'woocommerce-related-products' ); ?></h4>
                                    <p><?php esc_html_e( 'Common words that are filtered out during text analysis:', 'woocommerce-related-products' ); ?></p>
                                    <div class="stop-words-list">
                                        <code>the, and, or, but, in, on, at, to, for, of, with, by, is, are, was, were, be, been, being, have, has, had, do, does, did, will, would, could, should, may, might, must, can, this, that, these, those, a, an, as, if, when, where, how, why, what, which, who, whom, whose, your, you, i, me, my, mine, our, ours, their, them, they, he, him, his, she, her, hers, it, its, we, us, some, any, no, not, yes, no</code>
                                    </div>
                                </div>
                                
                                <p class="submit">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e( 'Save Text Settings', 'woocommerce-related-products' ); ?>
                                    </button>
                                </p>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Statistics Tab -->
                    <div id="stats" class="tab-pane">
                        <div class="wrp-enhanced-stats">
                            <div class="card">
                                <h3><?php esc_html_e( 'Cache Statistics', 'woocommerce-related-products' ); ?></h3>
                                <div id="wrp-enhanced-stats-content">
                                    <!-- Stats will be loaded here -->
                                </div>
                            </div>
                            
                            <div class="card">
                                <h3><?php esc_html_e( 'Performance Metrics', 'woocommerce-related-products' ); ?></h3>
                                <div class="performance-metrics">
                                    <div class="metric">
                                        <h4><?php esc_html_e( 'Average Query Time', 'woocommerce-related-products' ); ?></h4>
                                        <div class="metric-value" id="avg-query-time">--</div>
                                    </div>
                                    <div class="metric">
                                        <h4><?php esc_html_e( 'Cache Hit Rate', 'woocommerce-related-products' ); ?></h4>
                                        <div class="metric-value" id="cache-hit-rate">--</div>
                                    </div>
                                    <div class="metric">
                                        <h4><?php esc_html_e( 'Average Related Products', 'woocommerce-related-products' ); ?></h4>
                                        <div class="metric-value" id="avg-related-products">--</div>
                                    </div>
                                    <div class="metric">
                                        <h4><?php esc_html_e( 'Average Score', 'woocommerce-related-products' ); ?></h4>
                                        <div class="metric-value" id="avg-score">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        var wrp_enhanced_admin_vars = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('wrp_enhanced_admin_nonce'); ?>'
        };
        </script>
        
        <style>
        .wrp-enhanced-tabs {
            margin-top: 20px;
        }
        .wrp-enhanced-tabs .nav-tab-wrapper {
            margin-bottom: 20px;
        }
        .wrp-enhanced-tabs .tab-pane {
            display: none;
        }
        .wrp-enhanced-tabs .tab-pane.active {
            display: block;
        }
        .wrp-enhanced-actions {
            margin-bottom: 20px;
        }
        .wrp-enhanced-actions .button {
            margin-right: 10px;
        }
        .wrp-enhanced-progress {
            margin: 20px 0;
            display: none;
        }
        .wrp-enhanced-progress .progress-bar-container {
            background: #f0f0f1;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .wrp-enhanced-progress .progress-bar {
            background: #2271b1;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .wrp-enhanced-progress .progress-text {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        .wrp-enhanced-status {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
        }
        .wrp-enhanced-info {
            margin-top: 30px;
        }
        .wrp-enhanced-info .card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .wrp-enhanced-info .card h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .wrp-enhanced-info .card ul {
            margin-bottom: 0;
        }
        .wrp-enhanced-settings-form .card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stop-words-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.6;
            max-height: 200px;
            overflow-y: auto;
        }
        .performance-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .performance-metrics .metric {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .performance-metrics .metric h4 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 14px;
            color: #646970;
        }
        .performance-metrics .metric .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
        }
        </style>
        <?php
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wrp-enhanced-admin') === false) {
            return;
        }
        
        wp_enqueue_style('wrp-enhanced-admin', plugins_url('assets/css/wrp-enhanced-admin.css', dirname(dirname(__FILE__))));
        wp_enqueue_script('wrp-enhanced-admin', plugins_url('assets/js/wrp-enhanced-admin.js', dirname(dirname(__FILE__))), array('jquery'), '1.0.0', true);
        
        wp_localize_script('wrp-enhanced-admin', 'wrp_enhanced_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wrp_enhanced_admin_nonce')
        ));
    }

    /**
     * AJAX build cache
     */
    public function ajax_build_cache() {
        ob_start();
        
        check_ajax_referer('wrp_enhanced_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        error_log('WRP Enhanced Admin: Starting cache build');

        try {
            $cache = new WRP_Enhanced_Cache(new WRP_Core());
            $result = $cache->build_cache();
            
            ob_end_clean();
            
            if ($result['success']) {
                error_log('WRP Enhanced Admin: Cache build completed successfully');
                wp_send_json_success(array(
                    'message' => $result['message'],
                    'processed' => $result['processed'],
                    'total_relations' => $result['total_relations'],
                    'errors' => $result['errors']
                ));
            } else {
                error_log('WRP Enhanced Admin: Cache build failed');
                wp_send_json_error(array(
                    'message' => $result['message']
                ));
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            
            error_log('WRP Enhanced Admin: Exception during cache build: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error during cache build: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX cache status
     */
    public function ajax_cache_status() {
        ob_start();
        
        check_ajax_referer('wrp_enhanced_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        try {
            $cache = new WRP_Enhanced_Cache(new WRP_Core());
            $stats = $cache->get_stats();
            
            ob_end_clean();
            
            wp_send_json_success(array(
                'stats' => $stats,
                'message' => sprintf(
                    'Enhanced Cache Status: Products: %d/%d (%.1f%%) | Relations: %d | Avg: %.1f per product | Avg Score: %.1f | High Score: %d (%.1f%%)',
                    $stats['cached_products'],
                    $stats['total_products'],
                    $stats['cache_percentage'],
                    $stats['total_relations'],
                    $stats['avg_relations'],
                    $stats['avg_score'],
                    $stats['high_score_relations'],
                    $stats['high_score_percentage']
                )
            ));
            
        } catch (Exception $e) {
            ob_end_clean();
            
            error_log('WRP Enhanced Admin: Exception getting cache status: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error getting cache status: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        ob_start();
        
        check_ajax_referer('wrp_enhanced_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        try {
            $cache = new WRP_Enhanced_Cache(new WRP_Core());
            $result = $cache->clear_all();
            
            ob_end_clean();
            
            if ($result) {
                error_log('WRP Enhanced Admin: Cache cleared successfully');
                wp_send_json_success(array(
                    'message' => 'Enhanced cache cleared successfully!'
                ));
            } else {
                error_log('WRP Enhanced Admin: Cache clear failed');
                wp_send_json_error(array(
                    'message' => 'Failed to clear enhanced cache.'
                ));
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            
            error_log('WRP Enhanced Admin: Exception clearing cache: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX rebuild product cache
     */
    public function ajax_rebuild_product() {
        ob_start();
        
        check_ajax_referer('wrp_enhanced_admin_nonce', 'nonce');
        
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
            $cache = new WRP_Enhanced_Cache(new WRP_Core());
            $count = $cache->rebuild_product($product_id);
            
            ob_end_clean();
            
            wp_send_json_success(array(
                'message' => sprintf('Product %d enhanced cache rebuilt successfully. Found %d related products.', $product_id, $count),
                'product_id' => $product_id,
                'count' => $count
            ));
            
        } catch (Exception $e) {
            ob_end_clean();
            
            error_log('WRP Enhanced Admin: Exception rebuilding product cache: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error rebuilding product cache: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        ob_start();
        
        check_ajax_referer('wrp_enhanced_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        try {
            // Save basic settings
            $basic_settings = array(
                'threshold', 'limit', 'cross_reference', 'temporal_boost', 
                'popularity_boost', 'category_boost'
            );
            
            foreach ($basic_settings as $setting) {
                if (isset($_POST[$setting])) {
                    $value = is_numeric($_POST[$setting]) ? floatval($_POST[$setting]) : intval($_POST[$setting]);
                    update_option('wrp_enhanced_' . $setting, $value);
                }
            }
            
            // Save weight settings
            $weight_settings = array(
                'weight_title', 'weight_content', 'weight_categories', 'weight_tags',
                'weight_attributes', 'weight_price_range', 'weight_brand'
            );
            
            foreach ($weight_settings as $setting) {
                if (isset($_POST[$setting])) {
                    update_option('wrp_enhanced_' . $setting, floatval($_POST[$setting]));
                }
            }
            
            // Save text analysis settings
            $text_settings = array(
                'min_word_length', 'use_stop_words', 'use_stemming', 'fuzzy_matching'
            );
            
            foreach ($text_settings as $setting) {
                if (isset($_POST[$setting])) {
                    $value = is_numeric($_POST[$setting]) ? intval($_POST[$setting]) : intval($_POST[$setting]);
                    update_option('wrp_enhanced_' . $setting, $value);
                }
            }
            
            ob_end_clean();
            
            wp_send_json_success(array(
                'message' => 'Enhanced algorithm settings saved successfully!'
            ));
            
        } catch (Exception $e) {
            ob_end_clean();
            
            error_log('WRP Enhanced Admin: Exception saving settings: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error saving settings: ' . $e->getMessage()
            ));
        }
    }
}
?>