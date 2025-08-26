<?php
/**
 * Test script for WooCommerce Related Products Pro layouts
 * 
 * This script tests the display layouts, button functionality, and overall appearance
 * of the related products plugin.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Test Layout Functionality
 */
class WRP_Layout_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'run_tests' ) );
    }
    
    /**
     * Run layout tests
     */
    public function run_tests() {
        if ( ! isset( $_GET['wrp_test_layouts'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        echo '<div class="wrap wrp-test-results">';
        echo '<h1>WRP Layout Test Results</h1>';
        
        // Test 1: Check if settings are saved correctly
        $this->test_settings();
        
        // Test 2: Check if templates exist
        $this->test_templates();
        
        // Test 3: Check if CSS is loaded
        $this->test_css();
        
        // Test 4: Check if JavaScript is loaded
        $this->test_javascript();
        
        // Test 5: Check if AJAX handlers are registered
        $this->test_ajax_handlers();
        
        echo '</div>';
    }
    
    /**
     * Test settings
     */
    private function test_settings() {
        echo '<h2>Settings Test</h2>';
        
        $template = wrp_get_option( 'template', 'grid' );
        $columns = wrp_get_option( 'columns', 4 );
        
        echo '<div class="wrp-test-item">';
        echo '<h3>Template Setting</h3>';
        echo '<p>Current template: <strong>' . esc_html( $template ) . '</strong></p>';
        echo '<p>Status: <span class="wrp-status ' . ( in_array( $template, array( 'grid', 'list', 'carousel' ) ) ? 'success' : 'error' ) . '">' . ( in_array( $template, array( 'grid', 'list', 'carousel' ) ) ? '✓ Valid' : '✗ Invalid' ) . '</span></p>';
        echo '</div>';
        
        echo '<div class="wrp-test-item">';
        echo '<h3>Columns Setting</h3>';
        echo '<p>Current columns: <strong>' . esc_html( $columns ) . '</strong></p>';
        echo '<p>Status: <span class="wrp-status ' . ( $columns >= 1 && $columns <= 6 ? 'success' : 'error' ) . '">' . ( $columns >= 1 && $columns <= 6 ? '✓ Valid' : '✗ Invalid' ) . '</span></p>';
        echo '</div>';
    }
    
    /**
     * Test templates
     */
    private function test_templates() {
        echo '<h2>Templates Test</h2>';
        
        $templates = array( 'grid', 'list', 'carousel' );
        
        foreach ( $templates as $template ) {
            $template_file = WRP_PLUGIN_DIR . "templates/wrp-template-{$template}.php";
            
            echo '<div class="wrp-test-item">';
            echo '<h3>' . ucfirst( $template ) . ' Template</h3>';
            echo '<p>File: <code>' . esc_html( $template_file ) . '</code></p>';
            echo '<p>Status: <span class="wrp-status ' . ( file_exists( $template_file ) ? 'success' : 'error' ) . '">' . ( file_exists( $template_file ) ? '✓ Exists' : '✗ Missing' ) . '</span></p>';
            echo '</div>';
        }
    }
    
    /**
     * Test CSS
     */
    private function test_css() {
        echo '<h2>CSS Test</h2>';
        
        $css_file = WRP_PLUGIN_DIR . 'assets/css/wrp-public.css';
        
        echo '<div class="wrp-test-item">';
        echo '<h3>Public CSS</h3>';
        echo '<p>File: <code>' . esc_html( $css_file ) . '</code></p>';
        echo '<p>Status: <span class="wrp-status ' . ( file_exists( $css_file ) ? 'success' : 'error' ) . '">' . ( file_exists( $css_file ) ? '✓ Exists' : '✗ Missing' ) . '</span></p>';
        echo '</div>';
        
        // Check if CSS is enqueued
        global $wp_scripts, $wp_styles;
        
        echo '<div class="wrp-test-item">';
        echo '<h3>CSS Enqueue Status</h3>';
        echo '<p>Check if CSS is properly enqueued on product pages</p>';
        echo '<p>Status: <span class="wrp-status warning">⚠ Requires front-end test</span></p>';
        echo '</div>';
    }
    
    /**
     * Test JavaScript
     */
    private function test_javascript() {
        echo '<h2>JavaScript Test</h2>';
        
        $js_file = WRP_PLUGIN_DIR . 'assets/js/wrp-public.js';
        
        echo '<div class="wrp-test-item">';
        echo '<h3>Public JavaScript</h3>';
        echo '<p>File: <code>' . esc_html( $js_file ) . '</code></p>';
        echo '<p>Status: <span class="wrp-status ' . ( file_exists( $js_file ) ? 'success' : 'error' ) . '">' . ( file_exists( $js_file ) ? '✓ Exists' : '✗ Missing' ) . '</span></p>';
        echo '</div>';
        
        // Check key JavaScript functions
        if ( file_exists( $js_file ) ) {
            $js_content = file_get_contents( $js_file );
            $functions = array( 'handleAddToCart', 'handleBuyNow', 'initCarousels' );
            
            foreach ( $functions as $function ) {
                echo '<div class="wrp-test-item">';
                echo '<h3>Function: ' . esc_html( $function ) . '</h3>';
                echo '<p>Status: <span class="wrp-status ' . ( strpos( $js_content, $function ) !== false ? 'success' : 'error' ) . '">' . ( strpos( $js_content, $function ) !== false ? '✓ Found' : '✗ Missing' ) . '</span></p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Test AJAX handlers
     */
    private function test_ajax_handlers() {
        echo '<h2>AJAX Handlers Test</h2>';
        
        // Check if AJAX actions are registered
        global $wp_filter;
        
        $ajax_actions = array( 'wp_ajax_nopriv_wrp_add_to_cart', 'wp_ajax_wrp_add_to_cart' );
        
        foreach ( $ajax_actions as $action ) {
            echo '<div class="wrp-test-item">';
            echo '<h3>AJAX Action: ' . esc_html( $action ) . '</h3>';
            echo '<p>Status: <span class="wrp-status ' . ( isset( $wp_filter[$action] ) ? 'success' : 'error' ) . '">' . ( isset( $wp_filter[$action] ) ? '✓ Registered' : '✗ Not Registered' ) . '</span></p>';
            echo '</div>';
        }
    }
}

/**
 * Layout Test Styles
 */
function wrp_layout_test_styles() {
    ?>
    <style>
    .wrp-test-results {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .wrp-test-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
    }
    
    .wrp-test-item h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .wrp-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .wrp-status.success {
        background: #d4edda;
        color: #155724;
    }
    
    .wrp-status.error {
        background: #f8d7da;
        color: #721c24;
    }
    
    .wrp-status.warning {
        background: #fff3cd;
        color: #856404;
    }
    
    code {
        background: #e9ecef;
        padding: 2px 4px;
        border-radius: 3px;
        font-family: monospace;
    }
    </style>
    <?php
}

// Initialize test
add_action( 'admin_head', 'wrp_layout_test_styles' );
new WRP_Layout_Test();

/**
 * Add test link to admin menu
 */
function wrp_add_test_link() {
    add_submenu_page(
        'wrp-settings',
        'Layout Test',
        'Layout Test',
        'manage_options',
        'wrp-layout-test',
        'wrp_layout_test_page'
    );
}

function wrp_layout_test_page() {
    ?>
    <div class="wrap">
        <h1>WRP Layout Test</h1>
        <p>This page tests the layout functionality of WooCommerce Related Products Pro.</p>
        
        <div class="card">
            <h2>Test Layouts</h2>
            <p>Click the button below to run the layout tests:</p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wrp-settings&wrp_test_layouts=1' ) ); ?>" class="button button-primary">
                    Run Layout Tests
                </a>
            </p>
        </div>
        
        <div class="card">
            <h2>Manual Testing</h2>
            <p>To test the layouts manually:</p>
            <ol>
                <li>Go to a product page on the front-end</li>
                <li>Check if related products are displayed</li>
                <li>Test different layout options (Grid, List, Carousel) in the admin settings</li>
                <li>Test the "Add to Cart" and "Buy Now" buttons</li>
                <li>Test the carousel navigation (if carousel layout is selected)</li>
                <li>Test responsive design on different screen sizes</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>Debug Information</h2>
            <p>Add <code>#wrp-debug</code> to any product page URL to see debug information in the console.</p>
        </div>
    </div>
    <?php
}

add_action( 'admin_menu', 'wrp_add_test_link', 99 );

/**
 * Front-end debug script
 */
function wrp_debug_script() {
    if ( isset( $_GET['wrp-debug'] ) && current_user_can( 'manage_options' ) ) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('=== WRP Debug Information ===');
            console.log('Template:', $('.wrp-related-products').attr('class'));
            console.log('Grid items:', $('.wrp-template-grid .wrp-product-item').length);
            console.log('List items:', $('.wrp-template-list .wrp-product-item').length);
            console.log('Carousel items:', $('.wrp-template-carousel .wrp-product-item').length);
            console.log('Carousel containers:', $('.wrp-carousel-container').length);
            console.log('Add to cart buttons:', $('.wrp-add-to-cart').length);
            console.log('Buy now buttons:', $('.wrp-buy-now').length);
            console.log('=== End Debug ===');
        });
        </script>
        <?php
    }
}

add_action( 'wp_footer', 'wrp_debug_script', 99 );