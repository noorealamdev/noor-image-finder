<?php
/*
Plugin Name: Noor Image Finder
Description: Find and use images for free
Plugin URI: https://codenpy.com
Version: 1.0.0
Author URI: https://codenpy.com
Requires at least: 5.7
Requires PHP: 7.0
*/


if (!defined('ABSPATH')) exit; // Exit if accessed directly


if ( ! class_exists( 'Noor_Image_Finder' ) ) {
    final class Noor_Image_Finder {

        /**
         * Constructor function.
         */
        public function __construct() {
            $this->define();
            $this->includes();
            $this->init();
        }

        public function define() {
            define( 'NOORIMGFND_VER', '1.0.0' );
            define( 'NOORIMGFND_DIR', plugin_dir_path( __FILE__ ) );
            define( 'NOORIMGFND_URL', plugin_dir_url( __FILE__ ) );
        }

        public function includes () {
            include_once( NOORIMGFND_DIR . 'class/class-enqueue.php' );
            include_once( NOORIMGFND_DIR . 'class/class-image-api.php' );
        }

        public function init() {
            add_action( 'plugins_loaded', [ $this, 'noor_image_finder_init' ] );
        }

        public function noor_image_finder_init() {
            //load_plugin_textdomain( 'zerosock_text_domain', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

            // Check if Elementor installed and activated.
//            if ( ! did_action( 'elementor/loaded' ) ) {
//                return;
//            }
//
//            // Check for required Elementor version.
//            if ( ! version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
//                return;
//            }

            // Check for required PHP version.
            if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
                return;
            }

            // Once we get here, We have passed all validation checks so we can safely run our plugin.
            Noor_Image_Finder_Enqueue::instance()->initialize();
            Noor_Image_Finder_API::instance()->initialize();
        }

    }

    new Noor_Image_Finder();
}