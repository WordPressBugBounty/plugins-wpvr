<?php

namespace RexTheme\WPVR\Frontend\Builders;

/**
 * Wrapper for the legacy Bricks builder integration.
 *
 * Only loads when the Bricks theme is active, matching legacy behaviour.
 */
class Bricks {

    public function __construct() {
        if ( wp_get_theme( 'bricks' )->exists() && 'bricks' === get_template() ) {
            require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'bricks/bricks.php';
            \Manager::instance();
        }
    }
}
