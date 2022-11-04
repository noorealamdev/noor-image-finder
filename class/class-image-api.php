<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Noor_Image_Finder_API' ) ) {
    class Noor_Image_Finder_API
    {
        protected static $instance = null;

        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function initialize() {
            // Fire AJAX action for both logged in and non-logged in users
            add_action('wp_ajax_noorimgfnd_get_images', [$this, 'noorimgfnd_get_images']);
            add_action('wp_ajax_nopriv_noorimgfnd_get_images', [$this, 'noorimgfnd_get_images']);

            // Fire AJAX action for both logged in and non-logged in users
            add_action('wp_ajax_noorimgfnd_upload_image', [$this, 'noorimgfnd_upload_image']);
            add_action('wp_ajax_nopriv_noorimgfnd_upload_image', [$this, 'noorimgfnd_upload_image']);

        }


        public function noorimgfnd_get_images() {
            $searchQuery = $_REQUEST["searchQuery"];

            $remote_url = 'https://api.pexels.com/v1/search?query='.$searchQuery.'&page=1&per_page=10';
            $api_key = '563492ad6f91700001000001e234ed96190b451fbec3fb76656ee463';
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                ),
            );
            $response = wp_remote_get( $remote_url, $args );
            $response_code = wp_remote_retrieve_response_code( $response );

            if ($response_code !== 200) {
                wp_send_json(['error' => 'failed to load images']);
                return false;
            }
            $response_body = wp_remote_retrieve_body( $response );
            $response_body_decode = json_decode( $response_body );
            $photos = $response_body_decode->photos;

            if ( count($photos) > 0 ) {
                $photos_array = array();
                foreach ( $photos as $photo ) {
                    $newArray = array(
                        'id' => $photo->id,
                        'original' => $photo->src->original,
                        'medium' => $photo->src->medium
                    );
                    array_push($photos_array, $newArray);
                }
                echo json_encode($photos_array);
                exit; // exit ajax call(or it will return useless information to the response)
            }
            else {
                wp_send_json(['error' => 'could not find images, search for another keyword']);
            }

        }


        /**
         * Upload image from URL programmatically
         */
        public function noorimgfnd_upload_file_by_url( $image_url ) {

            // it allows us to use download_url() and wp_handle_sideload() functions
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

            // download to temp dir
            $temp_file = download_url( $image_url );

            if( is_wp_error( $temp_file ) ) {
                return false;
            }

            // move the temp file into the uploads directory
            $file = array(
                'name'     => basename( $image_url ),
                'type'     => mime_content_type( $temp_file ),
                'tmp_name' => $temp_file,
                'size'     => filesize( $temp_file ),
            );
            $sideload = wp_handle_sideload(
                $file,
                array(
                    'test_form'   => false // no needs to check 'action' parameter
                )
            );

            if( ! empty( $sideload[ 'error' ] ) ) {
                // you may return error message if you want
                wp_send_json(['error' => 'upload failed. Try again']);
                return false;
            }

            // it is time to add our uploaded image into WordPress media library
            $attachment_id = wp_insert_attachment(
                array(
                    'guid'           => $sideload[ 'url' ],
                    'post_mime_type' => $sideload[ 'type' ],
                    'post_title'     => basename( $sideload[ 'file' ] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                ),
                $sideload[ 'file' ]
            );

            if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
                wp_send_json(['error' => 'upload failed. Try again']);
                return false;
            }

            // update medatata, regenerate image sizes
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
            );

            wp_send_json(['success' => 'upload successful']);
            return $attachment_id;

        }


        public function noorimgfnd_upload_image() {

            $imageUrl = $_REQUEST["imageUrl"];
            //$imageUrl = 'http://localhost/shop/wp-content/uploads/2022/05/Descuento-50.png';
            $this->noorimgfnd_upload_file_by_url($imageUrl);

        }


    }
}