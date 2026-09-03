<?php

namespace WpvrElement\Breakdance;

use function Breakdance\Elements\c;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

\Breakdance\ElementStudio\registerElementForEditing(
    __NAMESPACE__ . '\\Wpvr',
    \Breakdance\Util\getDirectoryPathRelativeToPluginFolder( __DIR__ )
);

/**
 * WP VR element for Breakdance Builder.
 */
class Wpvr extends \Breakdance\Elements\Element {

    public static function uiIcon() {
        return 'ImageIcon';
    }

    public static function tag() {
        return 'div';
    }

    public static function name() {
        return 'WP VR';
    }

    public static function className() {
        return 'bde-wpvr';
    }

    public static function category() {
        return 'basic';
    }

    public static function slug() {
        return __CLASS__;
    }

    public static function template() {
        return '%%SSR%%';
    }

    public static function defaultCSS() {
        return '.breakdance .bde-wpvr { max-width: 100%; width: 100%; }';
    }

    public static function dependencies() {
        $public_url = trailingslashit( WPVR_PLUGIN_PUBLIC_DIR_URL );
        $main_script = $public_url . 'js/wpvr-public.js';

        if ( defined( 'WPVR_PRO_PLUGIN_DIR_URL' ) ) {
            $main_script = trailingslashit( WPVR_PRO_PLUGIN_DIR_URL ) . 'admin/js/wpvr-public.js';
        }

        $runtime_config = [
            'notice_active'      => get_option( 'wpvr_frontend_notice' ),
            'notice'             => get_option( 'wpvr_frontend_notice' ) ? sanitize_text_field( get_option( 'wpvr_frontend_notice_area' ) ) : '',
            'is_pro_active'      => defined( 'WPVR_PRO_PLUGIN_DIR_URL' ),
            'is_license_active'  => 'valid' === get_option( 'wpvr_edd_license_status' ),
            'dis_on_hover'       => 'true' === get_option( 'dis_on_hover' ),
            'mobile_hotspot_tip' => 'true' === get_option( 'wpvr_mobile_hotspot_tip' ),
        ];

        return [
            [
                'title'             => 'WP VR builder preview',
                'scripts'           => [
                    $public_url . 'lib/pannellum/src/js/pannellum.js',
                    $public_url . 'lib/pannellum/src/js/libpannellum.js',
                    $public_url . 'js/owl.carousel.js',
                    $public_url . 'js/jquery.cookie.js',
                    $main_script,
                ],
                'styles'            => [
                    $public_url . 'css/fontawesome/css/all.css',
                    $public_url . 'css/fontawesome/css/icons-fix.css',
                    $public_url . 'lib/pannellum/src/css/pannellum.css',
                    $public_url . 'css/owl.carousel.css',
                    $public_url . 'css/wpvr-public.css',
                ],
                'inlineScripts'     => [
                    'window.wpvr_public = window.wpvr_public || ' . wp_json_encode( $runtime_config ) . ';',
                ],
                'builderCondition'  => 'return true;',
                'frontendCondition' => 'return false;',
            ],
        ];
    }

    private static function builderPreviewScript() {
        return <<<'JS'
(function () {
    const element = document.querySelector('%%SELECTOR%%');
    let attempts = 0;

    function initializeWpvrPreview() {
        if (!element || attempts > 40) return;

        if (
            typeof window.jQuery === 'undefined' ||
            typeof window.pannellum === 'undefined' ||
            typeof window.wpvrhotspot === 'undefined'
        ) {
            attempts += 1;
            window.setTimeout(initializeWpvrPreview, 50);
            return;
        }

        element
            .querySelectorAll('script[type="application/wpvr-breakdance-preview"]')
            .forEach(function (previewScript) {
                if (previewScript.dataset.wpvrInitialized === 'true') return;

                previewScript.dataset.wpvrInitialized = 'true';

                const executableScript = document.createElement('script');
                executableScript.textContent = previewScript.textContent;
                document.body.appendChild(executableScript);
                executableScript.remove();
            });
    }

    initializeWpvrPreview();
}());
JS;
    }

    public static function actions() {
        $preview_script = self::builderPreviewScript();

        return [
            'onMountedElement' => [
                [ 'script' => $preview_script ],
            ],
            'onPropertyChange' => [
                [ 'script' => $preview_script ],
            ],
        ];
    }

    public static function defaultProperties() {
        return [
            'content' => [
                'wpvr' => [
                    'tour'          => null,
                    'width'         => 600,
                    'height'        => 400,
                    'mobile_height' => 300,
                    'radius'        => 0,
                ],
            ],
        ];
    }

    public static function contentControls() {
        $number_options = [
            'type'         => 'number',
            'layout'       => 'inline',
            'rangeOptions' => [
                'min'  => 0,
                'max'  => 2000,
                'step' => 1,
            ],
        ];

        return [
            c(
                'wpvr',
                'WP VR',
                [
                    c(
                        'tour',
                        __( 'Select Tour', 'wpvr' ),
                        [],
                        [
                            'type'               => 'post_chooser',
                            'layout'             => 'vertical',
                            'postChooserOptions' => [
                                'multiple'       => false,
                                'showThumbnails' => true,
                                'postType'       => 'wpvr_item',
                            ],
                        ],
                        false,
                        false,
                        []
                    ),
                    c( 'width', __( 'Width (px)', 'wpvr' ), [], $number_options, false, false, [] ),
                    c( 'height', __( 'Height (px)', 'wpvr' ), [], $number_options, false, false, [] ),
                    c( 'mobile_height', __( 'Mobile Height (px)', 'wpvr' ), [], $number_options, false, false, [] ),
                    c( 'radius', __( 'Border Radius (px)', 'wpvr' ), [], $number_options, false, false, [] ),
                ],
                [
                    'type'    => 'section',
                    'layout'  => 'vertical',
                ],
                false,
                false,
                []
            ),
        ];
    }

    public static function nestingRule() {
        return [ 'type' => 'final' ];
    }

    public static function propertyPathsToSsrElementWhenValueChanges() {
        return [
            'content.wpvr.tour',
            'content.wpvr.width',
            'content.wpvr.height',
            'content.wpvr.mobile_height',
            'content.wpvr.radius',
        ];
    }
}
