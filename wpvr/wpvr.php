<?php

use RexTheme\WPVR\Tracker\WPVRLinnoTelemetry;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://rextheme.com/
 * @since             7.3.6
 * @package           Wpvr
 *
 * @wordpress-plugin
 * Plugin Name:       WP VR - 360 Panorama and Virtual Tour Builder
 * Plugin URI:        https://rextheme.com/wpvr/
 * Description:       WP VR - 360 Panorama and virtual tour creator is a customized panaroma & virtual builder tool for your website.
 * Version:           9.0.0
 * Tested up to:      6.9
 * Author:            Rextheme
 * Author URI:        http://rextheme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpvr
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WPVR_VERSION', '9.0.0');
define('WPVR_FILE', __FILE__);
define("WPVR_PLUGIN_DIR_URL", plugin_dir_url(__FILE__).'legacy/');
define("WPVR_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__).'legacy/');
define("WPVR_PLUGIN_LEGACY_DIR_PATH", plugin_dir_path(__FILE__) . 'legacy/');
define("WPVR_PLUGIN_PUBLIC_DIR_URL", plugin_dir_url(__FILE__) . 'legacy/public/');
define('WPVR_BASE', plugin_basename(WPVR_FILE));
define('WPVR_DEV_MODE', false);
define('WPVR_JS_PATH', plugin_dir_url(__FILE__) . 'legacy/admin/js/');
define('WPVR_CSS_PATH', plugin_dir_url(__FILE__) . 'legacy/admin/css/');
define('WPVR_ASSET_PATH', plugin_dir_url(__FILE__) . 'legacy/admin/');

$wpvr_composer_autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
if ( file_exists( $wpvr_composer_autoload ) ) {
    require_once $wpvr_composer_autoload;
}

if ( ! defined( 'WPVR_TELEMETRY_API_KEY' ) ) {
    define( 'WPVR_TELEMETRY_API_KEY', 'phc_amk3VQz1P5ZqZOMRRoWM1B41QMFpf3hu8pg7yRfzSXW' );
}
if ( ! defined( 'WPVR_TELEMETRY_API_SECRET' ) ) {
    define( 'WPVR_TELEMETRY_API_SECRET', 'sec_dfb2ab8e84391ae7a0f9' );
}
if ( ! defined( 'WPVR_TELEMETRY_HOST' ) ) {
    define( 'WPVR_TELEMETRY_HOST', 'https://eu.i.posthog.com' );
}

if ( ! function_exists( 'activate_wpvr' ) ) {
    /**
     * The code that runs during plugin activation.
     */
    function activate_wpvr() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-activator.php';
        Wpvr_Activator::activate();

        // Trigger plugin activation tracking
        do_action( 'wpvr_plugin_activated' );
    }
}

if ( ! function_exists( 'deactivate_wpvr' ) ) {
    /**
     * The code that runs during plugin deactivation.
     */
    function deactivate_wpvr() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-deactivator.php';
        Wpvr_Deactivator::deactivate();

        // Trigger plugin deactivation tracking
        do_action( 'wpvr_plugin_deactivated' );
    }
}

register_activation_hook( WPVR_FILE, 'activate_wpvr' );
register_deactivation_hook( WPVR_FILE, 'deactivate_wpvr' );

if ( class_exists( WPVRLinnoTelemetry::class ) ) {
    new WPVRLinnoTelemetry();
}

if ( ! function_exists( 'wpvr_is_pro_active' ) ) {
    /**
     * Return the effective WP VR Pro entitlement state.
     *
     * A loaded Pro plugin alone is not enough; its license must also be valid.
     */
    function wpvr_is_pro_active(): bool {
        return defined( 'WPVR_PRO_VERSION' )
            && get_option( 'wpvr_edd_license_status' ) === 'valid'
            && (bool) apply_filters( 'is_wpvr_pro_active', false );
    }
}

if ( ! function_exists( 'wpvr_get_effective_panodata' ) ) {
    /**
     * Build a runtime-only Free-plan view of saved tour data.
     *
     * The stored meta is never changed, so reactivating Pro restores every value.
     * Also normalizes values written by newer editor clients to the legacy
     * string format expected by the shared preview and shortcode renderers.
     */
    function wpvr_get_effective_panodata( array $postdata ): array {
        if ( isset( $postdata['scene_navigation'] ) ) {
            $postdata['scene_navigation'] = in_array( $postdata['scene_navigation'], [ true, 1, '1', 'on' ], true ) ? 'on' : 'off';
        }

        if ( wpvr_is_pro_active() ) {
            return $postdata;
        }

        $false_keys = [
            'gyro', 'deviceorientationcontrol', 'compass', 'vrgallery',
            'vrgallery_title', 'vrgallery_icon_size', 'vrgallery_display',
        ];
        foreach ( $false_keys as $key ) {
            $postdata[ $key ] = false;
        }

        $off_keys = [
            'scene_navigation', 'sceneAnimation', 'bg_music',
            'autoplay_bg_music', 'loop_bg_music', 'explainerSwitch',
            'cpLogoSwitch', 'genericform', 'calltoaction', 'customcss_enable',
            'floorplan-enabled', 'floorplan-compass',
            'floor_plan_tour_enabler', 'floor_plan_direction_indicator',
            'bg_tour_enabler', 'video-autoplay', 'video-loop',
        ];
        foreach ( $off_keys as $key ) {
            $postdata[ $key ] = 'off';
        }

        $postdata['tourLayout']  = [
            'layout'               => 'default',
            'layout_icon_bg_color' => '#5a536e',
            'layout_icon_color'    => '#ffffff',
        ];
        $postdata['draggable']   = true;
        $postdata['mouseZoom']   = true;
        $postdata['diskeyboard'] = true;
        $postdata['keyboardzoom'] = true;
        $postdata['hfov']        = '';
        $postdata['maxHfov']     = '';
        $postdata['minHfov']     = '';

        if ( ( $postdata['tour-type'] ?? '' ) === 'street-view' || isset( $postdata['streetviewdata'] ) ) {
            $postdata['tour-type']     = 'image';
            $postdata['streetview']    = 'off';
            $postdata['streetviewurl'] = '';
            unset( $postdata['streetviewdata'] );
        }

        $custom_control = isset( $postdata['customcontrol'] ) && is_array( $postdata['customcontrol'] )
            ? $postdata['customcontrol']
            : [];
        foreach ( [
            'panupSwitch', 'panDownSwitch', 'panLeftSwitch', 'panRightSwitch',
            'panZoomInSwitch', 'panZoomOutSwitch', 'panFullscreenSwitch',
            'gyroscopeSwitch', 'backToHomeSwitch',
        ] as $key ) {
            $custom_control[ $key ] = 'off';
        }
        $postdata['customcontrol'] = $custom_control;

        if ( isset( $postdata['panodata']['scene-list'] ) && is_array( $postdata['panodata']['scene-list'] ) ) {
            foreach ( $postdata['panodata']['scene-list'] as &$scene ) {
                if ( ! is_array( $scene ) ) {
                    continue;
                }

                if ( ( $scene['scene-type'] ?? 'equirectangular' ) === 'cubemap' ) {
                    $scene['scene-type'] = 'equirectangular';
                }

                foreach ( [ 'ptyscene', 'cvgscene', 'chgscene', 'czscene' ] as $key ) {
                    $scene[ $key ] = 'off';
                }
                foreach ( [
                    'scene-author', 'scene-author-url', 'scene-vaov', 'scene-haov',
                    'scene-vertical-offset', 'scene-pitch', 'scene-yaw',
                    'scene-maxpitch', 'scene-minpitch', 'scene-maxyaw', 'scene-minyaw',
                    'scene-zoom', 'scene-maxzoom', 'scene-minzoom',
                ] as $key ) {
                    $scene[ $key ] = '';
                }
                for ( $face = 0; $face < 6; $face++ ) {
                    $scene[ 'scene-attachment-url-face' . $face ] = '';
                }

                if ( isset( $scene['hotspot-list'] ) && is_array( $scene['hotspot-list'] ) ) {
                    foreach ( $scene['hotspot-list'] as &$hotspot ) {
                        if ( ! is_array( $hotspot ) ) {
                            continue;
                        }
                        $hotspot['hotspot-customclass-pro'] = 'none';
                        $hotspot['hotspot-blink']            = 'off';
                        $hotspot['hotspot-shape']            = 'round';
                        $hotspot['hotspot-border']           = 'off';
                        $hotspot['hotspot-scene-pitch']      = '';
                        $hotspot['hotspot-scene-yaw']        = '';
                    }
                    unset( $hotspot );
                }
            }
            unset( $scene );
        }

        return $postdata;
    }
}

if ( ! function_exists( 'wpvr_tour_uses_pro_features' ) ) {
    /**
     * Determine whether a saved tour contains enabled WP VR Pro features.
     *
     * @param mixed $postdata Saved tour data.
     * @return bool
     */
    function wpvr_tour_uses_pro_features( $postdata ) {
        if ( ! is_array( $postdata ) ) {
            return false;
        }

        $is_on = static function ( $value ) {
            return in_array( $value, array( true, 1, '1', 'on', 'true' ), true );
        };

        if (
            isset( $postdata['streetviewdata'] )
            || ( isset( $postdata['streetview'] ) && $is_on( $postdata['streetview'] ) )
            || ! empty( $postdata['streetviewurl'] )
        ) {
            return true;
        }

        foreach ( array(
            'bg_tour_enabler', 'floorplan-enabled', 'floor_plan_tour_enabler',
            'floor_plan_compass', 'floor_plan_direction_indicator',
            'gyro', 'deviceorientationcontrol', 'compass', 'vrgallery',
            'vrgallery_title', 'vrgallery_icon_size', 'vrgallery_display',
            'scene_navigation', 'sceneAnimation', 'bg_music',
            'autoplay_bg_music', 'loop_bg_music', 'explainerSwitch',
            'cpLogoSwitch', 'genericform', 'calltoaction', 'customcss_enable',
        ) as $key ) {
            if ( isset( $postdata[ $key ] ) && $is_on( $postdata[ $key ] ) ) {
                return true;
            }
        }

        if (
            ! empty( $postdata['hfov'] )
            || ! empty( $postdata['maxHfov'] )
            || ! empty( $postdata['minHfov'] )
        ) {
            return true;
        }

        $tour_layout = isset( $postdata['tourLayout'] ) ? $postdata['tourLayout'] : 'default';
        $tour_layout = is_array( $tour_layout )
            ? ( isset( $tour_layout['layout'] ) ? $tour_layout['layout'] : 'default' )
            : $tour_layout;
        if ( $tour_layout !== '' && $tour_layout !== 'default' ) {
            return true;
        }

        $custom_control = isset( $postdata['customcontrol'] ) && is_array( $postdata['customcontrol'] )
            ? $postdata['customcontrol']
            : array();
        foreach ( array(
            'panupSwitch', 'panDownSwitch', 'panLeftSwitch', 'panRightSwitch',
            'panZoomInSwitch', 'panZoomOutSwitch', 'panFullscreenSwitch',
            'gyroscopeSwitch', 'backToHomeSwitch',
        ) as $key ) {
            if ( isset( $custom_control[ $key ] ) && $is_on( $custom_control[ $key ] ) ) {
                return true;
            }
        }

        $scenes = isset( $postdata['panodata']['scene-list'] ) ? $postdata['panodata']['scene-list'] : array();
        if ( ! is_array( $scenes ) ) {
            return false;
        }

        $pro_scene_fields = array(
            'scene-vaov', 'scene-haov', 'scene-vertical-offset', 'scene-pitch',
            'scene-yaw', 'scene-maxpitch', 'scene-minpitch', 'scene-maxyaw',
            'scene-minyaw', 'scene-zoom', 'scene-maxzoom', 'scene-minzoom',
        );

        foreach ( $scenes as $scene ) {
            if ( ! is_array( $scene ) ) {
                continue;
            }

            if ( isset( $scene['scene-type'] ) && 'cubemap' === $scene['scene-type'] ) {
                return true;
            }

            foreach ( array( 'ptyscene', 'cvgscene', 'chgscene', 'czscene' ) as $key ) {
                if ( isset( $scene[ $key ] ) && $is_on( $scene[ $key ] ) ) {
                    return true;
                }
            }

            foreach ( $pro_scene_fields as $key ) {
                if ( isset( $scene[ $key ] ) && '' !== $scene[ $key ] && null !== $scene[ $key ] ) {
                    return true;
                }
            }

            $hotspots = isset( $scene['hotspot-list'] ) ? $scene['hotspot-list'] : array();
            if ( ! is_array( $hotspots ) ) {
                continue;
            }

            foreach ( $hotspots as $hotspot ) {
                if ( ! is_array( $hotspot ) ) {
                    continue;
                }

                if (
                    ( ! empty( $hotspot['hotspot-customclass-pro'] ) && 'none' !== $hotspot['hotspot-customclass-pro'] )
                    || ( ! empty( $hotspot['hotspot-shape'] ) && 'round' !== $hotspot['hotspot-shape'] )
                    || ( isset( $hotspot['hotspot-border'] ) && $is_on( $hotspot['hotspot-border'] ) )
                    || ! empty( $hotspot['hotspot-scene-pitch'] )
                    || ! empty( $hotspot['hotspot-scene-yaw'] )
                    || ( isset( $hotspot['hotspot-scene-entry-point-mode'] ) && 'custom' === $hotspot['hotspot-scene-entry-point-mode'] )
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}

if ( ! function_exists( 'wpvr_get_pro_feature_tour_ids' ) ) {
    /**
     * Return IDs of tours that use at least one enabled Pro feature.
     *
     * @return array
     */
    function wpvr_get_pro_feature_tour_ids() {
        static $tour_ids = null;

        if ( is_array( $tour_ids ) ) {
            return $tour_ids;
        }

        $tour_ids = array();
        $posts    = get_posts( array(
            'post_type'              => 'wpvr_item',
            'post_status'            => 'any',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ) );

        foreach ( $posts as $tour_id ) {
            $postdata = get_post_meta( (int) $tour_id, 'panodata', true );
            if ( wpvr_tour_uses_pro_features( $postdata ) ) {
                $tour_ids[] = (int) $tour_id;
            }
        }

        return $tour_ids;
    }
}

if ( ! function_exists( 'wpvr_has_pro_feature_tours' ) ) {
    function wpvr_has_pro_feature_tours() {
        return ! empty( wpvr_get_pro_feature_tour_ids() );
    }
}

if ( ! function_exists( 'wpvr_enqueue_license_notice_assets' ) ) {
    /**
     * Enqueue the global license notice styles while Pro is installed and inactive.
     *
     * @return void
     */
    function wpvr_enqueue_license_notice_assets() {
        if (
            ! is_admin()
            || ! defined( 'WPVR_PRO_VERSION' )
            || wpvr_is_pro_active()
        ) {
            return;
        }

        wp_enqueue_style(
            'wpvr-license-notice-fontawesome',
            WPVR_PLUGIN_DIR_URL . 'admin/lib/fontawesome/css/all.css',
            array(),
            WPVR_VERSION
        );
        wp_enqueue_style(
            'wpvr-license-notice',
            WPVR_PLUGIN_DIR_URL . 'admin/css/wpvr-license-notice.css',
            array( 'wpvr-license-notice-fontawesome' ),
            WPVR_VERSION
        );
        wp_enqueue_script(
            'wpvr-license-notice',
            WPVR_PLUGIN_DIR_URL . 'admin/js/wpvr-license-notice.js',
            array(),
            WPVR_VERSION,
            true
        );
    }
}

add_action( 'admin_enqueue_scripts', 'wpvr_enqueue_license_notice_assets', 10 );

if ( ! function_exists( 'wpvr_render_pro_feature_fallback_notice' ) ) {
    /**
     * Render the global Pro license notice and explain the saved-tour fallback.
     *
     * @return void
     */
    function wpvr_render_pro_feature_fallback_notice() {
        if (
            ! is_admin()
            || ! current_user_can( 'edit_posts' )
            || ! defined( 'WPVR_PRO_VERSION' )
            || wpvr_is_pro_active()
        ) {
            return;
        }

        $status           = strtolower( (string) get_option( 'wpvr_edd_license_status', 'no' ) );
        $needs_activation = in_array( $status, array( '', 'no' ), true );

        if ( ! $needs_activation && ! wpvr_has_pro_feature_tours() ) {
            return;
        }

        if ( $needs_activation ) {
            $title         = __( 'Please Activate your license key to enjoy the pro features for WPVR', 'wpvr' );
            $subtitle      = '';
            $action_label  = __( 'Activate', 'wpvr' );
            $action_url    = admin_url( 'admin.php?page=wpvrpro' );
            $action_target = '';
            $action_icon   = 'fa-key';
        } else {
            $title         = 'expired' === $status
                ? __( 'Your WPVR Pro license has expired.', 'wpvr' )
                : __( 'Your WPVR Pro license is not active.', 'wpvr' );
            $subtitle      = __( 'Pro features are no longer available. Upgrade to Pro now to restore access to all premium features.', 'wpvr' );
            $action_label  = __( 'Upgrade to Pro', 'wpvr' );
            $action_url    = 'https://rextheme.com/your-account/#purchase';
            $action_target = ' target="_blank" rel="noopener noreferrer"';
            $action_icon   = 'fa-crown';
        }

        $notice_classes = 'notice notice-error is-dismissible wpvr-license-fallback-notice';
        if ( '' === $subtitle ) {
            $notice_classes .= ' wpvr-license-fallback-notice--single-line';
        }

        echo '<div id="wpvr-license-fallback-notice" class="' . esc_attr( $notice_classes ) . '">';
        echo '<img class="wpvr-license-fallback-notice__icon" src="' . esc_url( WPVR_PLUGIN_DIR_URL . 'admin/images/wpvr-license-warning.svg' ) . '" width="20" height="19" alt="" aria-hidden="true">';
        echo '<div class="wpvr-license-fallback-notice__content">';
        echo '<p class="wpvr-license-fallback-notice__title">' . esc_html( $title ) . '</p>';
        if ( '' !== $subtitle ) {
            echo '<p class="wpvr-license-fallback-notice__subtitle">' . esc_html( $subtitle ) . '</p>';
        }
        echo '</div>';
        echo '<a class="wpvr-license-fallback-notice__action" href="' . esc_url( $action_url ) . '"' . $action_target . '>';
        echo '<span class="wpvr-license-fallback-notice__action-icon fa-solid ' . esc_attr( $action_icon ) . '" aria-hidden="true"></span>';
        echo '<span>' . esc_html( $action_label ) . '</span>';
        echo '</a>';
        echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'wpvr' ) . '</span></button>';
        echo '</div>';
    }
}

add_action( 'admin_notices', 'wpvr_render_pro_feature_fallback_notice', 10 );

if ( ! function_exists( 'wpvr_add_pro_feature_tour_row_class' ) ) {
    /**
     * Add a class to affected tour rows while the Pro entitlement is inactive.
     *
     * @param array $classes Existing post classes.
     * @param array $class Additional classes supplied by WordPress.
     * @param int   $post_id Post ID.
     * @return array
     */
    function wpvr_add_pro_feature_tour_row_class( $classes, $class = array(), $post_id = 0 ) {
        if (
            ! is_admin()
            || ! defined( 'WPVR_PRO_VERSION' )
            || wpvr_is_pro_active()
            || 'wpvr_item' !== get_post_type( $post_id )
        ) {
            return $classes;
        }

        $postdata = get_post_meta( (int) $post_id, 'panodata', true );
        if ( wpvr_tour_uses_pro_features( $postdata ) ) {
            $classes[] = 'wpvr-pro-feature-tour';
        }

        return $classes;
    }
}

add_filter( 'post_class', 'wpvr_add_pro_feature_tour_row_class', 10, 3 );

require_once plugin_dir_path( __FILE__ ) . 'src/Admin/UiModeSwitcher.php';
\RexTheme\WPVR\Admin\UiModeSwitcher::init();

add_action( 'wp_ajax_wpvr_export_tour', 'wpvr_handle_export_tour' );
/**
 * AJAX handler: return a signed URL for the Pro tour exporter.
 *
 * WPVR Pro creates and streams the ZIP through its admin-post endpoint. This
 * handler authorizes the New UI request and returns that endpoint URL.
 */
function wpvr_handle_export_tour(): void {
	check_ajax_referer( 'wpvr_export_tour', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( [ 'message' => 'Insufficient permissions.' ], 403 );
	}

	if ( ! wpvr_is_pro_active() ) {
		wp_send_json_error( [ 'message' => 'A valid WP VR Pro license is required.' ], 403 );
	}

	$tour_id = isset( $_POST['tour_id'] ) ? absint( $_POST['tour_id'] ) : 0;
	if ( ! $tour_id ) {
		wp_send_json_error( [ 'message' => 'Missing tour ID.' ] );
	}

	$post = get_post( $tour_id );
	if ( ! $post || $post->post_type !== 'wpvr_item' ) {
		wp_send_json_error( [ 'message' => 'Tour not found.' ] );
	}

	if ( ! current_user_can( 'edit_post', $tour_id ) ) {
		wp_send_json_error( [ 'message' => 'You are not allowed to export this tour.' ], 403 );
	}

	$export_url = add_query_arg(
		[
			'action'   => 'wpvr_export_tour',
			'post_id'  => $tour_id,
			'_wpnonce' => wp_create_nonce( 'wpvr_export_tour_' . $tour_id ),
		],
		admin_url( 'admin-post.php' )
	);

	wp_send_json_success( [ 'url' => $export_url ] );
}

/**
 * Ensure legacy Street View keys always exist in panodata.
 *
 * WPVR Pro reads these keys directly in classic editor screens. Tours created
 * through newer flows may miss them, which triggers PHP warnings.
 */
function wpvr_backfill_legacy_streetview_keys() {
    if ( ! is_admin() ) {
        return;
    }

    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
    if ( ! $post_id ) {
        return;
    }

    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'wpvr_item' ) {
        return;
    }

    $panodata = get_post_meta( $post_id, 'panodata', true );
    if ( ! is_array( $panodata ) ) {
        return;
    }

    $changed = false;

    if ( ! array_key_exists( 'streetview', $panodata ) ) {
        $panodata['streetview'] = 'off';
        $changed = true;
    }

    if ( ! array_key_exists( 'streetviewurl', $panodata ) ) {
        $panodata['streetviewurl'] = '';
        $changed = true;
    }

    if ( $changed ) {
        update_post_meta( $post_id, 'panodata', $panodata );
    }
}

add_action( 'load-post.php', 'wpvr_backfill_legacy_streetview_keys', 1 );

/**
 * Register a lightweight PSR-4 autoloader for src/ classes.
 */
function wpvr_register_src_autoloader() {
    static $loaded = false;

    if ( $loaded ) {
        return;
    }

    spl_autoload_register(
        static function ( $class ) {
            $prefix = 'RexTheme\\WPVR\\';

            if ( strpos( $class, $prefix ) !== 0 ) {
                return;
            }

            $relative_class = substr( $class, strlen( $prefix ) );
            $file           = plugin_dir_path( __FILE__ ) . 'src/' . str_replace( '\\', '/', $relative_class ) . '.php';

            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    );

    $loaded = true;
}

// UI/UX Mode Switch: legacy/classic or latest
// Option: wpvr_ui_mode (default: legacy)
wpvr_register_src_autoloader();

$wpvr_ui_mode = get_option('wpvr_ui_mode', 'legacy');
if ($wpvr_ui_mode === 'latest') {
    require_once plugin_dir_path(__FILE__) . 'src/Bootstrap.php';
    \RexTheme\WPVR\Bootstrap::init();
} else {
    require_once plugin_dir_path(__FILE__) . 'legacy/wpvr.php';
    new \RexTheme\WPVR\Admin\Admin( false );
}
