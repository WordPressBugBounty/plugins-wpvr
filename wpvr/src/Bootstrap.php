<?php

namespace RexTheme\WPVR;

class Bootstrap {

    public static function init() {
        self::boot();
    }

    public static function activate() {
        if ( function_exists( 'activate_wpvr' ) ) {
            activate_wpvr();
        } else {
            require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-activator.php';
            \Wpvr_Activator::activate();
            do_action( 'wpvr_plugin_activated' );
        }
    }

    public static function deactivate() {
        if ( function_exists( 'deactivate_wpvr' ) ) {
            deactivate_wpvr();
        } else {
            require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-deactivator.php';
            \Wpvr_Deactivator::deactivate();
            do_action( 'wpvr_plugin_deactivated' );
        }
    }

    public static function boot() {
        // Full legacy stack — zero breaking changes guarantee.
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'wpvr.php';

        // Override shortcode handler to support new-UI tour formats (street-view saved without streetviewdata).
        new Frontend\Shortcode();

        // New tour editor admin UI.
        new Admin\Admin();

        // REST API routes.
        add_action( 'rest_api_init', [ new Api\Router(), 'register' ] );
    }
}
