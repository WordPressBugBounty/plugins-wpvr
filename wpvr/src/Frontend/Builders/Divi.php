<?php

namespace RexTheme\WPVR\Frontend\Builders;

/**
 * Wrapper for the legacy Divi module integration.
 */
class Divi {

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ], 99 );
    }

    public function init() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/wpvr-divi-modules/wpvr_divi_modules.php';
        \WPVR_Divi_modules::instance();
    }
}
