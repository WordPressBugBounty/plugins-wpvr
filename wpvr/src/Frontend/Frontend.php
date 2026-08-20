<?php

namespace RexTheme\WPVR\Frontend;

/**
 * Standalone wrapper for the legacy Wpvr_Public class.
 *
 * Handles public-side init and asset enqueuing via the legacy class.
 * Also instantiates Shortcode internally (Wpvr_Public creates it in its constructor).
 */
class Frontend {

    private $legacy;

    public function __construct() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'public/class-wpvr-public.php';
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'public/classes/class-wpvr-shortcode.php';

        $this->legacy = new \Wpvr_Public( 'wpvr', WPVR_VERSION );

        add_action( 'wp_enqueue_scripts', [ $this->legacy, 'enqueue_styles' ] );
        add_action( 'wp_enqueue_scripts', [ $this->legacy, 'enqueue_scripts' ] );
        $this->legacy->public_init();
    }
}
