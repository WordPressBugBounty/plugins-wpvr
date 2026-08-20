<?php

namespace RexTheme\WPVR\Admin;

/**
 * Standalone wrapper for the legacy WPVR_Post_Type class.
 *
 * Registers the wpvr_item custom post type using the legacy class.
 * Can be used independently; Bootstrap delegates this to Wpvr_Admin in Phase 1.
 */
class PostType {

    private $legacy;

    public function __construct() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-post-type.php';
        $this->legacy = new \WPVR_Post_Type( 'wpvr', WPVR_VERSION, 'wpvr_item' );
    }
}
