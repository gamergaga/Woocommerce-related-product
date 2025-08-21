<?php
/**
 * Autoloader class for WooCommerce Related Products Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRP_Autoloader {

    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * The constructor.
     */
    public function __construct() {
        if ( function_exists( '__autoload' ) ) {
            spl_autoload_register( '__autoload' );
        }

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->include_path = WRP_PLUGIN_DIR . 'includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param  string $class Class name.
     * @return string
     */
    private function get_file_name_from_class( $class ) {
        return 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param  string $path File path.
     * @return bool Successful or not.
     */
    private function load_file( $path ) {
        if ( $path && is_readable( $path ) ) {
            include_once $path;
            return true;
        }
        return false;
    }

    /**
     * Auto-load WRP classes on demand to reduce memory consumption.
     *
     * @param string $class Class name.
     */
    public function autoload( $class ) {
        $class = strtolower( $class );

        if ( 0 !== strpos( $class, 'wrp_' ) ) {
            return;
        }

        $file = $this->get_file_name_from_class( $class );
        $path = '';

        if ( 0 === strpos( $class, 'wrp_admin' ) ) {
            $path = WRP_PLUGIN_DIR . 'admin/';
        } elseif ( 0 === strpos( $class, 'wrp_public' ) ) {
            $path = WRP_PLUGIN_DIR . 'public/';
        } else {
            $path = $this->include_path;
        }

        $this->load_file( $path . $file );
    }
}

new WRP_Autoloader();