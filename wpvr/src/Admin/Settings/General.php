<?php

namespace RexTheme\WPVR\Admin\Settings;

/**
 * Standalone wrapper for the legacy WPVR_Basic_Setting class.
 *
 * Renders the plugin settings page by delegating to the legacy class.
 */
class General {

    private $legacy;

    public function __construct() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-basic-setting.php';
        $this->legacy = new \WPVR_Basic_Setting();
    }
}
