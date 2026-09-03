<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register WP VR's custom elements before Breakdance scans element locations.
 */
add_action(
    'breakdance_loaded',
    function () {
        if (
            ! function_exists( '\Breakdance\ElementStudio\registerSaveLocation' )
            || ! function_exists( '\Breakdance\Util\getDirectoryPathRelativeToPluginFolder' )
        ) {
            return;
        }

        \Breakdance\ElementStudio\registerSaveLocation(
            \Breakdance\Util\getDirectoryPathRelativeToPluginFolder( __DIR__ ) . '/elements',
            'WpvrElement\\Breakdance',
            'element',
            'WP VR',
            true,
            true
        );
    },
    5
);
