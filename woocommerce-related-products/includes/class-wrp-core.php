<?php
/**
 * Core class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Core {

    /**
     * Cache instance
     *
     * @var WRP_Cache
     */
    private $cache;

    /**
     * Database schema instance
     *
     * @var WRP_DB_Schema
     */
    private $db_schema;

    /**
     * Default options
     *
     * @var array
     */
    private $default_options = array(
        'threshold' => 1,
        'limit' => 4,
        'excerpt_length' => 10,
        'show_price' => true,
        'show_rating' => true,
        'show_add_to_cart' => true,
        'show_buy_now' => true,
        'auto_display' => true,
        'display_position' => 'after_content',
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
        'template' => 'grid',
        'columns' => 4,
        'image_size' => 'woocommerce_thumbnail',
        'show_excerpt' => false,
        'cache_enabled' => true,
        'cache_timeout' => 3600,
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize cache
        $cache_class = 'WRP_Cache_' . ucfirst( WRP_CACHE_TYPE );
        $this->cache = new $cache_class( $this );
        
        // Initialize database schema
        $this->db_schema = new WRP_DB_Schema();

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Always register auto display hook - we'll check the option when the hook runs
        add_action( 'woocommerce_before_single_product_summary', array( $this, 'maybe_display_related_products_before' ), 30 );
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'maybe_display_related_products_after' ), 15 );
        add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'maybe_display_related_products_after_cart' ) );

        // Clear cache on product update/delete
        add_action( 'save_post_product', array( $this, 'on_product_save' ), 10, 3 );
        add_action( 'delete_post', array( $this, 'on_product_delete' ) );
        add_action( 'woocommerce_product_set_stock', array( $this, 'on_stock_change' ) );
        
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
    }

    /**
     * Maybe display related products before content
     */
    public function maybe_display_related_products_before() {
        if ( wrp_get_option( 'auto_display', true ) && wrp_get_option( 'display_position', 'after_content' ) === 'before_content' ) {
            $this->auto_display_related_products();
        }
    }

    /**
     * Maybe display related products after content
     */
    public function maybe_display_related_products_after() {
        if ( wrp_get_option( 'auto_display', true ) && wrp_get_option( 'display_position', 'after_content' ) === 'after_content' ) {
            $this->auto_display_related_products();
        }
    }

    /**
     * Maybe display related products after add to cart
     */
    public function maybe_display_related_products_after_cart() {
        if ( wrp_get_option( 'auto_display', true ) && wrp_get_option( 'display_position', 'after_content' ) === 'after_add_to_cart' ) {
            $this->auto_display_related_products();
        }
    }

    /**
     * Auto display related products
     */
    public function auto_display_related_products() {
        global $product;
        
        if ( ! $product ) {
            return;
        }
        
        $this->display_related_products( $product->get_id() );
    }

    /**
     * Get related products for a product
     *
     * @param int   $product_id Product ID.
     * @param array $args Arguments.
     * @return array|false Array of WC_Product objects or false
     */
    public function get_related_products( $product_id, $args = array() ) {
        if ( ! $product_id || ! is_numeric( $product_id ) ) {
            return false;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return false;
        }

        // Parse arguments
        $args = wp_parse_args( $args, $this->default_options );

        try {
            // Try to get related product IDs from cache or compute
            $related_ids = $this->cache->get_related_ids( $product_id, $args );
        } catch (Exception $e) {
            // Fallback: get some sample products if cache fails
            $related_ids = $this->get_fallback_related_products( $product_id, $args );
        }

        if ( empty( $related_ids ) ) {
            // Fallback: get some sample products if no related products found
            $related_ids = $this->get_fallback_related_products( $product_id, $args );
        }

        // Convert to WC_Product objects
        $related_products = array();
        foreach ( $related_ids as $id ) {
            $related_product = wc_get_product( $id );
            if ( $related_product ) {
                // Check if we should include out of stock products
                if ( ! $args['include_out_of_stock'] && ! $related_product->is_in_stock() ) {
                    continue;
                }
                $related_products[] = $related_product;
            }
        }

        return $related_products;
    }

    /**
     * Fallback method to get related products when cache fails
     *
     * @param int   $product_id Product ID.
     * @param array $args Arguments.
     * @return array
     */
    private function get_fallback_related_products( $product_id, $args ) {
        global $wpdb;
        
        $limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 4;
        
        // Get some random products from the same category if possible
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return array();
        }
        
        // Try to get products from the same category first
        $categories = get_the_terms( $product_id, 'product_cat' );
        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            $category_ids = wp_list_pluck( $categories, 'term_id' );
            
            $related_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                    LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE p.post_type = 'product' AND p.post_status = 'publish'
                    AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $category_ids ), '%d' ) ) . ")
                    AND p.ID != %d
                    LIMIT %d",
                    array_merge( $category_ids, array( $product_id, $limit ) )
                )
            );
            
            if ( ! empty( $related_ids ) ) {
                return $related_ids;
            }
        }
        
        // Try to get products with similar tags
        $tags = get_the_terms( $product_id, 'product_tag' );
        if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
            $tag_ids = wp_list_pluck( $tags, 'term_id' );
            
            $related_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                    LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    WHERE p.post_type = 'product' AND p.post_status = 'publish'
                    AND tt.term_id IN (" . implode( ',', array_fill( 0, count( $tag_ids ), '%d' ) ) . ")
                    AND p.ID != %d
                    LIMIT %d",
                    array_merge( $tag_ids, array( $product_id, $limit ) )
                )
            );
            
            if ( ! empty( $related_ids ) ) {
                return $related_ids;
            }
        }
        
        // Fallback to random products (most lenient)
        $related_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND p.ID != %d
                LIMIT %d",
                $product_id,
                $limit
            )
        );
        
        return $related_ids ? $related_ids : array();
    }

    /**
     * Simple test method to get related products (for debugging)
     *
     * @param int   $product_id Product ID.
     * @param array $args Arguments.
     * @return array
     */
    public function get_simple_related_products( $product_id, $args = array() ) {
        global $wpdb;
        
        $limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 4;
        
        // Simple query: get any published products except the current one
        $related_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND p.ID != %d
                LIMIT %d",
                $product_id,
                $limit
            )
        );
        
        return $related_ids ? $related_ids : array();
    }

    /**
     * Display related products
     *
     * @param int   $product_id Product ID.
     * @param array $args Arguments.
     * @param bool  $echo Whether to echo or return.
     * @return string|void
     */
    public function display_related_products( $product_id, $args = array(), $echo = true ) {
        $related_products = $this->get_related_products( $product_id, $args );

        if ( empty( $related_products ) ) {
            // Show visual indicator when no related products found
            $output = $this->get_no_related_products_message();
            if ( $echo ) {
                echo $output;
                return;
            }
            return $output;
        }

        // Parse arguments
        $args = wp_parse_args( $args, $this->default_options );

        // Get template
        $template = $args['template'];
        $template_file = $this->get_template_file( $template );

        if ( ! $template_file ) {
            return '';
        }

        // Prepare template variables
        $template_args = array(
            'related_products' => $related_products,
            'reference_product' => wc_get_product( $product_id ),
            'args' => $args,
        );

        // Load template
        ob_start();
        include $template_file;
        $output = ob_get_clean();

        if ( $echo ) {
            echo $output;
            return;
        }

        return $output;
    }

    /**
     * Get message when no related products are found
     *
     * @return string
     */
    private function get_no_related_products_message() {
        $is_admin = current_user_can( 'manage_options' );
        $message = '';

        if ( $is_admin ) {
            $message = sprintf(
                '<div class="wrp-no-related-products wrp-admin-notice">
                    <div class="wrp-notice-icon">ℹ️</div>
                    <div class="wrp-notice-content">
                        <h4>%s</h4>
                        <p>%s</p>
                        <div class="wrp-notice-actions">
                            <a href="%s" class="button button-secondary">%s</a>
                            <a href="%s" class="button button-primary">%s</a>
                        </div>
                    </div>
                </div>',
                esc_html__( 'No Related Products Found', 'woocommerce-related-products' ),
                esc_html__( 'Related products will appear here when the algorithm finds matching products. This could be because:', 'woocommerce-related-products' ) .
                '<ul>' .
                '<li>' . esc_html__( 'Products lack descriptive content (titles, descriptions)', 'woocommerce-related-products' ) . '</li>' .
                '<li>' . esc_html__( 'Products don\'t share categories or tags', 'woocommerce-related-products' ) . '</li>' .
                '<li>' . esc_html__( 'Match threshold is too high', 'woocommerce-related-products' ) . '</li>' .
                '<li>' . esc_html__( 'Cache needs to be built', 'woocommerce-related-products' ) . '</li>' .
                '</ul>',
                esc_url( admin_url( 'admin.php?page=wrp-cache' ) ),
                esc_html__( 'Check Cache Status', 'woocommerce-related-products' ),
                esc_url( admin_url( 'admin.php?page=wrp-settings' ) ),
                esc_html__( 'Adjust Settings', 'woocommerce-related-products' )
            );
        } else {
            $message = sprintf(
                '<div class="wrp-no-related-products">
                    <p>%s</p>
                </div>',
                esc_html__( 'No related products found at this time.', 'woocommerce-related-products' )
            );
        }

        return $message;
    }

    /**
     * Check if related products exist
     *
     * @param int   $product_id Product ID.
     * @param array $args Arguments.
     * @return bool
     */
    public function has_related_products( $product_id, $args = array() ) {
        $related_products = $this->get_related_products( $product_id, $args );
        return ! empty( $related_products );
    }

    /**
     * Get product keywords for algorithm
     *
     * @param int    $product_id Product ID.
     * @param string $type Type of keywords.
     * @return string|array
     */
    public function get_product_keywords( $product_id, $type = 'all' ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return false;
        }

        $keywords = array(
            'title' => $this->extract_keywords( $product->get_name() ),
            'description' => $this->extract_keywords( $product->get_description() ),
            'short_description' => $this->extract_keywords( $product->get_short_description() ),
        );

        if ( $type === 'all' ) {
            return $keywords;
        }

        return isset( $keywords[ $type ] ) ? $keywords[ $type ] : '';
    }

    /**
     * Extract keywords from text
     *
     * @param string $text Input text.
     * @param int    $max Maximum number of keywords.
     * @return string
     */
    private function extract_keywords( $text, $max = 20 ) {
        if ( empty( $text ) ) {
            return '';
        }

        // Strip HTML tags and entities
        $text = wp_strip_all_tags( $text );
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        
        // Convert to lowercase
        $text = strtolower( $text );
        
        // Enhanced text processing
        $text = $this->preprocess_text( $text );
        
        // Remove special characters and split into words
        $words = preg_split( '/\s+/', preg_replace( '/[^\p{L}\p{N}\s]/u', '', $text ) );
        
        // Remove stop words
        $stop_words = $this->get_stop_words();
        $words = array_diff( $words, $stop_words );
        
        // Apply additional filtering
        $words = $this->filter_keywords( $words );
        
        // Count word frequency with position boosting
        $word_scores = $this->calculate_word_scores( $words );
        
        // Remove words that are too short
        foreach ( array_keys( $word_scores ) as $word ) {
            if ( strlen( $word ) < 2 ) {
                unset( $word_scores[ $word ] );
            }
        }
        
        // Sort by score
        arsort( $word_scores );
        
        // Get top keywords
        $keywords = array_slice( array_keys( $word_scores ), 0, $max );
        
        return implode( ' ', $keywords );
    }

    /**
     * Preprocess text for better keyword extraction
     *
     * @param string $text Input text.
     * @return string
     */
    private function preprocess_text( $text ) {
        // Handle product-specific terms
        $text = preg_replace( '/\b(\d+)\s*(?:gb|mb|kb|tb|ghz|mhz|hz|inch|\"|\'|cm|mm|kg|g|lb|oz)\b/i', '$1 $2', $text );
        
        // Handle currency symbols
        $text = preg_replace( '/[$£€¥]/', ' price ', $text );
        
        // Handle common product separators
        $text = preg_replace( '/[\/\-–—]/', ' ', $text );
        
        // Handle multiple spaces
        $text = preg_replace( '/\s+/', ' ', $text );
        
        return trim( $text );
    }

    /**
     * Filter keywords based on relevance
     *
     * @param array $words Array of words.
     * @return array
     */
    private function filter_keywords( $words ) {
        $filtered = array();
        
        foreach ( $words as $word ) {
            // Skip empty words
            if ( empty( $word ) ) {
                continue;
            }
            
            // Skip single characters (unless meaningful)
            if ( strlen( $word ) === 1 && !in_array( $word, array( 'x', 'l', 's', 'm' ) ) ) {
                continue;
            }
            
            // Skip pure numbers (unless they could be model numbers)
            if ( is_numeric( $word ) && strlen( $word ) > 4 ) {
                continue;
            }
            
            // Skip very common product words that aren't helpful
            $common_product_words = array( 'product', 'item', 'shop', 'store', 'buy', 'sale', 'free', 'new', 'best' );
            if ( in_array( $word, $common_product_words ) ) {
                continue;
            }
            
            $filtered[] = $word;
        }
        
        return $filtered;
    }

    /**
     * Calculate word scores with position boosting
     *
     * @param array $words Array of words.
     * @return array
     */
    private function calculate_word_scores( $words ) {
        $scores = array();
        $total_words = count( $words );
        
        foreach ( $words as $position => $word ) {
            if ( ! isset( $scores[ $word ] ) ) {
                $scores[ $word ] = 0;
            }
            
            // Base score
            $scores[ $word ] += 1;
            
            // Position boost (words in first 30% get higher score)
            if ( $position < $total_words * 0.3 ) {
                $scores[ $word ] += 0.5;
            }
            
            // Title word boost (assuming this might be from title)
            if ( $position < 10 ) {
                $scores[ $word ] += 0.3;
            }
            
            // Multi-word bonus (if this word appears multiple times)
            $count = array_count_values( array_slice( $words, 0, $position + 1 ) );
            if ( $count[ $word ] > 1 ) {
                $scores[ $word ] += 0.2;
            }
        }
        
        return $scores;
    }

    /**
     * Get stop words list
     *
     * @return array
     */
    private function get_stop_words() {
        return array(
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he',
            'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'were',
            'will', 'with', 'i', 'you', 'your', 'they', 'we', 'us', 'our', 'this',
            'these', 'those', 'am', 'been', 'being', 'have', 'had', 'do', 'does', 'did',
            'can', 'could', 'should', 'would', 'will', 'shall', 'may', 'might', 'must',
            'here', 'there', 'where', 'when', 'why', 'how', 'what', 'which', 'who', 'whom',
            'me', 'him', 'her', 'it', 'them', 'us', 'you', 'my', 'your', 'his', 'her',
            'its', 'our', 'their', 'mine', 'yours', 'hers', 'ours', 'theirs', 'get',
            'got', 'go', 'going', 'went', 'gone', 'come', 'came', 'see', 'saw', 'seen',
            'know', 'knew', 'think', 'thought', 'take', 'took', 'taken', 'make', 'made',
            'give', 'gave', 'given', 'use', 'used', 'find', 'found', 'want', 'wanted',
            'say', 'said', 'tell', 'told', 'call', 'called', 'try', 'tried', 'ask',
            'asked', 'need', 'needed', 'feel', 'felt', 'become', 'became', 'leave',
            'left', 'put', 'place', 'set', 'let', 'keep', 'kept', 'seem', 'seemed',
            'help', 'helped', 'show', 'showed', 'hear', 'heard', 'play', 'played',
            'run', 'ran', 'move', 'moved', 'live', 'lived', 'believe', 'believed',
            'bring', 'brought', 'happen', 'happened', 'write', 'wrote', 'provide',
            'sit', 'sat', 'stand', 'stood', 'lose', 'lost', 'pay', 'paid', 'meet',
            'met', 'include', 'included', 'continue', 'continued', 'set', 'setting',
            'learn', 'learned', 'change', 'changed', 'lead', 'led', 'understand',
            'understood', 'watch', 'watched', 'follow', 'followed', 'stop', 'stopped',
            'create', 'created', 'speak', 'spoke', 'read', 'reading', 'allow', 'allowed',
            'add', 'added', 'spend', 'spent', 'grow', 'grew', 'open', 'opened', 'walk',
            'walked', 'win', 'won', 'offer', 'offered', 'remember', 'remembered', 'love',
            'loved', 'consider', 'considered', 'appear', 'appeared', 'buy', 'bought',
            'wait', 'waited', 'serve', 'served', 'die', 'died', 'send', 'sent', 'expect',
            'expected', 'build', 'built', 'stay', 'stayed', 'fall', 'fell', 'cut', 'reach',
            'reached', 'kill', 'killed', 'remain', 'remained'
        );
    }

    /**
     * Get template file
     *
     * @param string $template Template name.
     * @return string|false
     */
    private function get_template_file( $template ) {
        // Check theme directory first
        $template_file = locate_template( "wrp-templates/wrp-template-{$template}.php" );
        
        if ( $template_file ) {
            return $template_file;
        }

        // Check plugin templates directory
        $plugin_template = WRP_PLUGIN_DIR . "templates/wrp-template-{$template}.php";
        
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return false;
    }

    /**
     * Clear cache
     *
     * @param int|array $product_ids Product ID(s).
     */
    public function clear_cache( $product_ids ) {
        $this->cache->clear( $product_ids );
    }

    /**
     * Get related score between two products
     *
     * @param int $reference_id Reference product ID.
     * @param int $related_id Related product ID.
     * @return float|false
     */
    public function get_related_score( $reference_id, $related_id ) {
        return $this->cache->get_score( $reference_id, $related_id );
    }

    /**
     * On product save
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param bool    $update Whether this is an update.
     */
    public function on_product_save( $post_id, $post, $update ) {
        if ( $post->post_type !== 'product' ) {
            return;
        }

        // Clear cache for this product
        $this->clear_cache( $post_id );

        // Clear cache for products that might be related to this one
        $related_references = $this->cache->get_related_references( $post_id );
        if ( ! empty( $related_references ) ) {
            $this->clear_cache( $related_references );
        }
    }

    /**
     * On product delete
     *
     * @param int $post_id Post ID.
     */
    public function on_product_delete( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'product' ) {
            return;
        }

        // Clear cache for this product
        $this->clear_cache( $post_id );

        // Clear cache for products that might be related to this one
        $related_references = $this->cache->get_related_references( $post_id );
        if ( ! empty( $related_references ) ) {
            $this->clear_cache( $related_references );
        }
    }

    /**
     * On stock change
     *
     * @param WC_Product $product Product object.
     */
    public function on_stock_change( $product ) {
        if ( ! wrp_get_option( 'include_out_of_stock', false ) ) {
            // If we don't include out of stock products, clear cache when stock changes
            $this->clear_cache( $product->get_id() );
        }
    }

    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_scripts() {
        // Only enqueue on product pages or when related products might be displayed
        if ( is_product() || wrp_get_option( 'auto_display', true ) ) {
            wp_enqueue_style(
                'wrp-public',
                WRP_PLUGIN_URL . 'assets/css/wrp-public.css',
                array(),
                WRP_VERSION
            );

            wp_enqueue_script(
                'wrp-public',
                WRP_PLUGIN_URL . 'assets/js/wrp-public.js',
                array( 'jquery' ),
                WRP_VERSION,
                true
            );

            wp_localize_script(
                'wrp-public',
                'wrp_vars',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'i18n' => array(
                        'add_to_cart_error' => __( 'Failed to add product to cart. Please try again.', 'woocommerce-related-products' ),
                    ),
                )
            );
        }
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function get_cache_stats() {
        return $this->cache->get_stats();
    }

    /**
     * Get cache instance
     *
     * @return WRP_Cache
     */
    public function get_cache() {
        return $this->cache;
    }
}