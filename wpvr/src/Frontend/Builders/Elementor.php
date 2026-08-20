<?php

namespace RexTheme\WPVR\Frontend\Builders;

/**
 * Wrapper for the legacy WpvrElementor integration.
 */
class Elementor {

    private $legacy;

    public function __construct() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'elementor/elementor.php';
        $this->legacy = new \WpvrElementor();
    }
}
