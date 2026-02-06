<?php
/**
 * WPVR WPBakery Loader
 * Loads the WPVR WPBakery integration when WPBakery (Visual Composer) is present.
 *
 * Place this file at: /builders/wpbakery/wpvr-loader.php
 * @since 8.5.48
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load WPVR WPBakery Element
 * @since 8.5.48
 */
add_action( 'vc_before_init', 'wpvr_wpbakery_register' );

/**
 * Require WPVR WPBakery Element file
 * @since 8.5.48
 */
function wpvr_wpbakery_register() {
    $file = dirname( __FILE__ ) . '/wpvr-element.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}
