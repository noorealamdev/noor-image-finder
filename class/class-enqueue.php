<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Noor_Image_Finder_Enqueue' ) ) {
    class Noor_Image_Finder_Enqueue {

        protected static $instance = null;

        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function initialize() {
            add_action('admin_enqueue_scripts', [$this, 'noorimgfnd_admin_enqueue_scripts'] );
        }

        public function noorimgfnd_admin_enqueue_scripts() {
            // CSS
            wp_enqueue_style('noorimgfnd-admin-style', NOORIMGFND_URL . 'assets/admin/admin-style.css' );

            // JS
            wp_enqueue_script('noorimgfnd-admin-script', NOORIMGFND_URL . 'assets/admin/admin-script.js', array('jquery'), '1.0.0', true);
            //wp_localize_script( 'noorimgfnd-admin-script', 'noorAjax', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
        }

    }

}