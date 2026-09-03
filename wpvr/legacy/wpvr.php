<?php
// Legacy bootstrap for WP VR plugin
// This file is a direct copy of the original wpvr.php before architecture migration.
// Do not edit legacy logic here; only update if the legacy code itself changes.

if (!defined('WPINC')) {
    die;
}

require WPVR_PLUGIN_LEGACY_DIR_PATH . 'elementor/elementor.php';
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'breakdance/breakdance.php';
if ( file_exists( dirname( WPVR_PLUGIN_LEGACY_DIR_PATH ) . '/vendor/autoload.php' ) ) {
    require_once dirname( WPVR_PLUGIN_LEGACY_DIR_PATH ) . '/vendor/autoload.php';
} elseif ( file_exists( WPVR_PLUGIN_LEGACY_DIR_PATH . 'vendor/autoload.php' ) ) {
    require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'vendor/autoload.php';
}

if ( wp_get_theme('bricks')->exists() && 'bricks' === get_template()) {
    require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'bricks/bricks.php';
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPVR_WEBHOOK_URL', sanitize_url( 'https://rextheme.com/?mailmint=1&route=webhook&topic=contact&hash=bbd19901-6d42-4ae5-a7a8-01eb1013c553' ) );
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
     * This action is documented in includes/class-wpvr-activator.php
     */
    function activate_wpvr()
    {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-activator.php';
        Wpvr_Activator::activate();

        // Trigger plugin activation tracking
        do_action( 'wpvr_plugin_activated' );
    }
}

if ( ! function_exists( 'deactivate_wpvr' ) ) {
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-wpvr-deactivator.php
     */
    function deactivate_wpvr()
    {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr-deactivator.php';
        Wpvr_Deactivator::deactivate();

        // Trigger plugin deactivation tracking
        do_action( 'wpvr_plugin_deactivated' );
    }
}

register_activation_hook( WPVR_FILE, 'activate_wpvr' );
register_deactivation_hook( WPVR_FILE, 'deactivate_wpvr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WPVR_PLUGIN_LEGACY_DIR_PATH . 'includes/class-wpvr.php';

// Include banner classes
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-occasion-banner.php';
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-sells-notification-bar.php';
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-first-tour-banner.php';
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-onboarding-notice.php';
require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/classes/class-wpvr-new-user-tour.php';

if ( defined( 'WPB_VC_VERSION' ) ) {
    require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'builders/wpbakery/wpvr-loader.php';
    if ( class_exists( 'Vc_Manager' ) ) {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'builders/wpbakery/wpvr-element.php';
    }
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    7.3.6
 */
function run_wpvr()
{

    $plugin = new Wpvr();
    $plugin->run();
//    new Tracker();

    // black friday banner class initialization
    // new WPVR_Special_Occasion_Banner(
    //     'halloween_deal_2025',
    //     '2025-10-01 00:00:01',
    //     '2025-11-05 23:59:59'
    // );

    if (!defined('WPVR_PRO_VERSION') && 'no' === get_option('wpvr_sell_eid_ul_fitr_2026_notification_bar', 'no')) {
        new WPVR_Notification_Bar(
            'Eid_Ul_Fitr_2026',
            '2026-03-16 00:00:00',
            '2026-03-24 23:59:59'
        );
    }

    // Initialize first tour banner
    new WPVR_First_Tour_Banner();

    // Initialize listing-page onboarding notice ("Remind me later" flow)
    new WPVR_Onboarding_Notice();

    // Auto-launch guided tour for truly-new users on the Add New Tour page.
    new WPVR_New_User_Tour();

    // Pro preview banner (shown via JS when free user toggles Advanced Controls)
    if (!defined('WPVR_PRO_VERSION')) {
        add_action('admin_notices', 'wpvr_render_pro_preview_banner', 11);
    }

}
run_wpvr();

/**
 * Render Pro preview banner as admin notice.
 * Hidden by default; shown via JS when free user toggles Advanced Controls.
 *
 * @since 8.5.44
 */
function wpvr_render_pro_preview_banner() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'wpvr_item') {
        return;
    }
    ?>
    <div class="wpvr-pro-preview-banner" id="wpvr-pro-preview-banner" style="display:none;">
        <div class="wpvr-pro-preview-banner__left">
            <span class="wpvr-pro-preview-banner__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
            </span>
            <span class="wpvr-pro-preview-banner__text"><?php esc_html_e('You are previewing Pro features in action.', 'wpvr'); ?></span>
        </div>
        <div class="wpvr-pro-preview-banner__pricing">
            <div class="wpvr-pro-preview-banner__pricing-top">
                <span class="wpvr-pro-preview-banner__old-price"><?php esc_html_e('Normally $99.99/year', 'wpvr'); ?></span>
                <span class="wpvr-pro-preview-banner__badge"><?php esc_html_e('SAVE 20%', 'wpvr'); ?></span>
            </div>
            <span class="wpvr-pro-preview-banner__new-price"><?php esc_html_e('Starting at $79.99/year', 'wpvr'); ?></span>
        </div>
        <a href="<?php echo esc_url('https://rextheme.com/wpvr/wpvr-pricing/?utm_source=plugin&utm_medium=pro-preview-banner&utm_campaign=advanced-controls'); ?>" class="wpvr-pro-preview-banner__btn" target="_blank" rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm0 2h14v1c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1v-1z"/></svg>
            <?php esc_html_e('Upgrade to save changes', 'wpvr'); ?>
        </a>
    </div>
    <?php
}


/**
 * Array information checker
 *
 * @param mixed $needle
 * @param mixed $haystack
 * @param bool $strict
 *
 * @return bool
 * @since 7.3.6
 */
function wpvr_in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if ((($strict ? $item === $needle : $item == $needle)) || is_array($item) && wpvr_in_array_r($needle, $item, $strict)) {
            return true;
        }
    }
    return false;
}

if ( ! function_exists( 'wpvr_parse_custom_control' ) ) {
    /**
     * Normalize custom control data into an array.
     *
     * @param mixed $custom_control Raw custom control value from meta.
     *
     * @return array
     * @since 8.5.63
     */
    function wpvr_parse_custom_control( $custom_control ) {
        if ( is_array( $custom_control ) ) {
            return $custom_control;
        }

        if ( is_object( $custom_control ) ) {
            return (array) $custom_control;
        }

        if ( ! is_string( $custom_control ) || '' === trim( $custom_control ) ) {
            return array();
        }

        $unserialized = maybe_unserialize( $custom_control );
        if ( is_array( $unserialized ) ) {
            return $unserialized;
        }

        $decoded = json_decode( wp_unslash( $custom_control ), true );
        if ( is_array( $decoded ) ) {
            return $decoded;
        }

        return array();
    }
}

if ( ! function_exists( 'wpvr_restore_modern_layout_default_controls' ) ) {
    /**
     * Restore Pannellum's default controls after WP VR Pro adds Modern layout CSS.
     *
     * The Modern layout stylesheet is added by WP VR Pro at filter priority 10.
     * Running at priority 20 keeps this compatibility rule in the Free plugin while
     * ensuring it is output after the Pro stylesheet, regardless of plugin load order.
     *
     * @param string $html     Generated tour HTML.
     * @param array  $postdata Saved tour settings.
     * @param int    $id       Tour post ID.
     *
     * @return string
     * @since 9.0.0
     */
    function wpvr_restore_modern_layout_default_controls( $html, $postdata, $id ) {
        $tour_layout = is_array( $postdata['tourLayout'] ?? null )
            ? ( $postdata['tourLayout']['layout'] ?? 'default' )
            : ( $postdata['tourLayout'] ?? 'default' );

        if (
            ! defined( 'WPVR_PRO_VERSION' )
            || 'layout1' !== $tour_layout
            || empty( $postdata['showControls'] )
        ) {
            return $html;
        }

        $custom_control = wp_parse_args(
            wpvr_parse_custom_control( $postdata['customcontrol'] ?? array() ),
            array(
                'panupSwitch'         => 'off',
                'panDownSwitch'       => 'off',
                'panLeftSwitch'       => 'off',
                'panRightSwitch'      => 'off',
                'panZoomInSwitch'     => 'off',
                'panZoomOutSwitch'    => 'off',
                'panFullscreenSwitch' => 'off',
                'gyroscopeSwitch'     => 'off',
                'backToHomeSwitch'    => 'off',
            )
        );

        $gyro_enabled = isset( $postdata['gyro'] )
            && in_array( $postdata['gyro'], array( true, 1, '1', 'on' ), true );
        $gyro_button_enabled = function_exists( 'wpvr_isMobileDevice' )
            && wpvr_isMobileDevice()
            && $gyro_enabled
            && 'on' === $custom_control['gyroscopeSwitch'];

        $custom_navigation_enabled = $gyro_button_enabled;
        foreach (
            array(
                'panupSwitch',
                'panDownSwitch',
                'panLeftSwitch',
                'panRightSwitch',
                'panZoomInSwitch',
                'panZoomOutSwitch',
                'panFullscreenSwitch',
                'backToHomeSwitch',
            ) as $switch
        ) {
            if ( 'on' === $custom_control[ $switch ] ) {
                $custom_navigation_enabled = true;
                break;
            }
        }

        if ( $custom_navigation_enabled ) {
            return $html;
        }

        $pano_id     = 'pano' . absint( $id );
        $sprites_url = esc_url( WPVR_PLUGIN_PUBLIC_DIR_URL . 'lib/pannellum/src/css/img/sprites.svg' );

        $html .= '<style id="wpvr-modern-default-controls-' . esc_attr( $id ) . '">
            #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-zoom-controls.pnlm-controls {
                display: block !important;
                width: 28px !important;
                height: 52px !important;
                right: auto !important;
                bottom: auto !important;
                margin-top: 4px !important;
                border: 1px solid rgba(0, 0, 0, 0.4) !important;
                border-radius: 3px !important;
                background-color: #fff !important;
                background-image: none !important;
                background-position: 0 0 !important;
                background-repeat: repeat !important;
                background-size: auto !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-fullscreen-toggle-button {
                display: block !important;
                width: 28px !important;
                height: 26px !important;
                right: auto !important;
                bottom: auto !important;
                margin-top: 4px !important;
                border: 1px solid rgba(0, 0, 0, 0.4) !important;
                border-radius: 3px !important;
                background-color: #fff !important;
                background-image: url("' . $sprites_url . '") !important;
                background-repeat: repeat !important;
                background-size: auto !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-fullscreen-toggle-button-inactive {
                background-position: 1px -52px !important;
            }

            #et-boc .et-l #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-fullscreen-toggle-button-inactive {
                background-position: -1px -53px !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-fullscreen-toggle-button-active {
                background-position: 0 -78px !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-controls-container .pnlm-control:hover {
                background-color: #f8f8f8 !important;
            }
        </style>';

        return $html;
    }
}

add_filter( 'wpvr_generate_tour_layout_html', 'wpvr_restore_modern_layout_default_controls', 20, 3 );

if ( ! function_exists( 'wpvr_render_scene_info_row' ) ) {
    /**
     * Keep scene title and author metadata aligned for every tour layout.
     *
     * WP VR Pro can replace the Pannellum asset registered by the Free plugin and
     * adds Modern layout CSS at priority 10. This late, tour-scoped layer keeps
     * both Pannellum copies and both layouts consistent without modifying Pro.
     *
     * @param string $html     Generated tour HTML.
     * @param array  $postdata Saved tour settings.
     * @param int    $id       Tour post ID.
     *
     * @return string
     * @since 9.0.0
     */
    function wpvr_render_scene_info_row( $html, $postdata, $id ) {
        $pano_id      = 'pano' . absint( $id );
        $pano_id_json = wp_json_encode( $pano_id );
        $by_label     = wp_json_encode( __( 'By', 'wpvr' ) );
        $tour_layout  = is_array( $postdata['tourLayout'] ?? null )
            ? ( $postdata['tourLayout']['layout'] ?? 'default' )
            : ( $postdata['tourLayout'] ?? 'default' );
        $is_classic_layout = wp_json_encode( 'layout1' !== $tour_layout );

        $html .= '<style id="wpvr-scene-info-' . esc_attr( $id ) . '">
            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 10px !important;
                box-sizing: border-box !important;
                min-width: 0 !important;
                overflow: hidden !important;
                text-align: center !important;
                z-index: 2147483647 !important;
                -webkit-transform: translateZ(10000px) !important;
                transform: translateZ(10000px) !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row .pnlm-title-box {
                flex: 0 1 auto !important;
                min-width: 0 !important;
                max-width: calc(70% - 5px) !important;
                width: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row .pnlm-author-box {
                flex: 0 1 auto !important;
                min-width: 0 !important;
                max-width: calc(30% - 5px) !important;
                margin: 0 !important;
                border-left: 1px solid rgba(255, 255, 255, 0.45) !important;
                padding: 0 0 0 10px !important;
                display: block !important;
                overflow: hidden !important;
                line-height: 1.2 !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info .wpvr-author-label {
                flex: none !important;
                margin-right: 4px !important;
                font-size: 10px !important;
                font-weight: 700 !important;
                letter-spacing: 0.06em !important;
                opacity: 0.8 !important;
                text-transform: uppercase !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info .pnlm-author-box a {
                color: inherit !important;
                text-decoration: underline !important;
                text-underline-offset: 2px !important;
            }

            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-has-title:not(.wpvr-has-author) .pnlm-title-box,
            #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-has-author:not(.wpvr-has-title) .pnlm-author-box {
                max-width: 100% !important;
                border-left: 0 !important;
                padding-left: 0 !important;
            }

            #' . esc_attr( $pano_id ) . '.wpvr-layout-classic.wpvr-has-scene-info .scene-gallery.wpvr-gallery--classic {
                bottom: calc(var(--wpvr-scene-info-height, 34px) + 8px) !important;
            }

            #' . esc_attr( $pano_id ) . '.wpvr-layout-classic.wpvr-has-scene-info .vrgcontrols {
                bottom: calc(var(--wpvr-scene-info-height, 34px) + 4px) !important;
            }

            #' . esc_attr( $pano_id ) . '.wpvr-layout-classic.wpvr-has-scene-info .wpvr_slider_nav {
                bottom: calc(var(--wpvr-scene-info-height, 34px) + 15px) !important;
            }

            #' . esc_attr( $pano_id ) . '.wpvr-layout-classic.wpvr-has-scene-info .controls {
                bottom: calc(var(--wpvr-scene-info-height, 34px) + 6px) !important;
            }

            @media (max-width: 767px) {
                #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row {
                    gap: 7px !important;
                    padding-right: 10px !important;
                    padding-left: 10px !important;
                }

                #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row .pnlm-title-box {
                    max-width: calc(65% - 4px) !important;
                }

                #' . esc_attr( $pano_id ) . ' .pnlm-panorama-info.wpvr-scene-info-row .pnlm-author-box {
                    max-width: calc(35% - 4px) !important;
                    padding-left: 7px !important;
                }
            }
        </style>
        <script id="wpvr-scene-info-script-' . esc_attr( $id ) . '">
            (function () {
                var pano = document.getElementById(' . $pano_id_json . ');
                var byLabel = ' . $by_label . ';

                if (!pano || !window.MutationObserver) {
                    return;
                }

                function addAuthorLabel(author) {
                    if (!author || !author.textContent.trim() || author.querySelector(".wpvr-author-label")) {
                        return;
                    }

                    var firstNode = author.firstChild;
                    if (firstNode && firstNode.nodeType === 3) {
                        var prefix = byLabel + " ";
                        if (firstNode.nodeValue.indexOf(prefix) === 0) {
                            firstNode.nodeValue = firstNode.nodeValue.slice(prefix.length);
                        }
                    }

                    var label = document.createElement("span");
                    label.className = "wpvr-author-label";
                    label.textContent = byLabel;
                    author.insertBefore(label, author.firstChild);
                }

                function syncSceneInfo() {
                    var info = pano.querySelector(".pnlm-panorama-info");
                    if (!info) {
                        return;
                    }

                    // Keep the Pannellum full-screen drag layer in its original
                    // stacking order so hotspots remain clickable. Promote only
                    // the compact metadata bar to the tour root instead.
                    if (info.parentNode !== pano) {
                        pano.appendChild(info);
                    }

                    var title = info.querySelector(".pnlm-title-box");
                    var author = info.querySelector(".pnlm-author-box");
                    var hasTitle = !!(title && title.textContent.trim());
                    var hasAuthor = !!(author && author.textContent.trim());

                    if (hasTitle) {
                        title.setAttribute("title", title.textContent.trim());
                    } else if (title) {
                        title.removeAttribute("title");
                    }

                    if (hasAuthor) {
                        addAuthorLabel(author);
                        var authorName = author.textContent.replace(byLabel, "").trim();
                        author.setAttribute("aria-label", byLabel + " " + authorName);
                        author.setAttribute("title", authorName);
                    } else if (author) {
                        author.removeAttribute("aria-label");
                        author.removeAttribute("title");
                    }

                    info.classList.toggle("wpvr-scene-info-row", hasTitle || hasAuthor);
                    info.classList.toggle("wpvr-has-title", hasTitle);
                    info.classList.toggle("wpvr-has-author", hasAuthor);
                    info.style.display = hasTitle || hasAuthor ? "flex" : "none";

                    var hasSceneInfo = hasTitle || hasAuthor;
                    var isClassicLayout = ' . $is_classic_layout . ';
                    pano.classList.toggle("wpvr-has-scene-info", hasSceneInfo);
                    pano.classList.toggle("wpvr-layout-classic", isClassicLayout);

                    if (hasSceneInfo) {
                        pano.style.setProperty("--wpvr-scene-info-height", info.offsetHeight + "px");
                    } else {
                        pano.style.removeProperty("--wpvr-scene-info-height");
                    }
                }

                var observer = new MutationObserver(syncSceneInfo);
                observer.observe(pano, { childList: true, subtree: true, characterData: true });
                syncSceneInfo();
            }());
        </script>';

        return $html;
    }
}

add_filter( 'wpvr_generate_tour_layout_html', 'wpvr_render_scene_info_row', 30, 3 );

if ( ! function_exists( 'wpvr_render_explainer_button' ) ) {
    /**
     * Render the WPVR explainer button independently.
     *
     * @param array|null $custom_control
     * @param array      $postdata
     * @param bool       $is_pro
     * @param bool       $autoload
     * @param string     $explainer_right
     * @param int|string $id
     *
     * @return string
     * @since 8.5.63
     */
    function wpvr_render_explainer_button( $custom_control, $postdata, $is_pro, $autoload, $explainer_right, $id ) {
        $html = '';
        $explainer_enabled = isset( $postdata['explainerSwitch'] )
            && in_array( $postdata['explainerSwitch'], array( true, 1, '1', 'on' ), true );
        $explainer_icon  = 'fa fa-video';
        $explainer_color = '#f7fffb';

        if ( isset( $custom_control ) ) {
            $explainer_icon  = isset( $custom_control['explainerIcon'] ) ? $custom_control['explainerIcon'] : $explainer_icon;
            $explainer_color = isset( $custom_control['explainerColor'] ) ? $custom_control['explainerColor'] : $explainer_color;
        } elseif ( isset( $postdata['customcontrol'] ) ) {
            $raw_control     = wpvr_parse_custom_control( $postdata['customcontrol'] );
            $explainer_icon  = isset( $raw_control['explainerIcon'] ) ? $raw_control['explainerIcon'] : $explainer_icon;
            $explainer_color = isset( $raw_control['explainerColor'] ) ? $raw_control['explainerColor'] : $explainer_color;
        }

        $pro_license_status = get_option( 'wpvr_edd_license_status' );
        if ( $explainer_enabled && $pro_license_status === 'valid' && $is_pro ) {
            $explainer_style = empty( $postdata['explainerContent'] )
                ? 'pointer-events: none; opacity: 0.5;'
                : '';

            // Initial display:none to prevent flash for non-autoload tours.
            $initial_display = ! $autoload ? 'display:none; ' : '';

            $html .= '<div class="explainer_button" id="explainer_button_' . esc_attr( $id ) . '" style="' . $initial_display . 'right:' . esc_attr( $explainer_right ) . '; ' . esc_attr( $explainer_style ) . '">';
            $html .= '<div class="ctrl" id="explainer_target_' . esc_attr( $id ) . '"><i class="' . esc_attr( $explainer_icon ) . '" style="color:' . esc_attr( $explainer_color ) . ';"></i></div>';
            $html .= '</div>';
        }

        return $html;
    }
}

// Linno telemetry integration
function wpvr_block()
{
    wp_register_script(
        'wpvr-block',
        plugins_url('build/index.build.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
    );

    if (is_admin()) {
        wp_enqueue_style(
            'gutyblocks/guty-block',
            plugins_url('src/view.css', __FILE__),
            array()
        );
    }

    if (function_exists('register_block_type')) {
        register_block_type('wpvr/wpvr-block', array(
            'attributes'      => array(
                'id' => array(
                    'type' => 'string',
                    'default' => '0',
                ),
                'width' => array(
                    'type' => 'string',
                    'default' => '600',
                ),
                'width_unit' => array(
                    'type' => 'string',
                    'default' => 'px',
                ),
                'height' => array(
                    'type' => 'string',
                    'default' => '400',
                ),
                'height_unit' => array(
                    'type' => 'string',
                    'default' => 'px',
                ),
                'mobile_height' => array(
                    'type' => 'string',
                    'default' => '300',
                ),
                'mobile_height_unit' => array(
                    'type' => 'string',
                    'default' => 'px',
                ),
                'radius' => array(
                    'type' => 'string',
                    'default' => '0',
                ),
                'radius_unit' => array(
                    'type' => 'string',
                    'default' => 'px',
                ),
                'border_width' => array(
                    'type' => 'string',
                    'default' => '0',
                ),
                'border_style' => array(
                    'type' => 'string',
                    'default' => 'none',
                ),
                'border_color' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
            'editor_script' => 'wpvr-block',
            'render_callback' => 'wpvr_block_render',
        ));
    }
}

add_action('init', 'wpvr_block');

function wpvr_block_render($attributes)
{
    $id = isset($attributes['id']) ? (int) $attributes['id'] : 0;
    if (!$id) {
        return '<div class="wpvr-no-tour-selected">' . esc_html__('Please select a tour from the block settings panel.', 'wpvr') . '</div>';
    }

    $width = isset($attributes['width']) ? $attributes['width'] : '600';
    if ('fullwidth' === $width) {
        $width_str = 'fullwidth';
    } else {
        $width_unit = isset($attributes['width_unit']) ? $attributes['width_unit'] : 'px';
        if (preg_match('/(px|%|vw|vh)$/', (string) $width)) {
            $width_str = $width;
        } else {
            $width_str = $width . $width_unit;
        }
    }

    $height      = isset($attributes['height']) ? $attributes['height'] : '400';
    $height_unit = isset($attributes['height_unit']) ? $attributes['height_unit'] : 'px';
    if (!preg_match('/(px|%|vw|vh)$/', (string) $height)) {
        $height .= $height_unit;
    }

    $mobile_height      = isset($attributes['mobile_height']) ? $attributes['mobile_height'] : '300';
    $mobile_height_unit = isset($attributes['mobile_height_unit']) ? $attributes['mobile_height_unit'] : 'px';
    if (!preg_match('/(px|%|vw|vh)$/', (string) $mobile_height)) {
        $mobile_height .= $mobile_height_unit;
    }

    $radius      = isset($attributes['radius']) ? $attributes['radius'] : '0';
    $radius_unit = isset($attributes['radius_unit']) ? $attributes['radius_unit'] : 'px';
    if (!preg_match('/(px|%|vw|vh)$/', (string) $radius)) {
        $radius .= $radius_unit;
    }

    $shortcode = sprintf(
        '[wpvr id="%d" width="%s" height="%s" mobile_height="%s" radius="%s"]',
        $id,
        esc_attr($width_str),
        esc_attr($height),
        esc_attr($mobile_height),
        esc_attr($radius)
    );

    $output = do_shortcode($shortcode);

    // Extract any <script> tags so they are not corrupted by wptexturize (which runs at priority 10 on the_content, after do_blocks at priority 9).
    $scripts = '';
    if (preg_match_all('/<script\b[^>]*>[\s\S]*?<\/script>/i', $output, $matches)) {
        $scripts = implode("\n", $matches[0]);
        $output  = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $output);
    }

    if (!empty($scripts)) {
        if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            $output .= "\n" . $scripts;
        } else {
            add_action('wp_footer', function() use ($scripts) {
                echo $scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }, 20);
        }
    }

    $border_css = '';
    if (!empty($attributes['border_width']) && !empty($attributes['border_style']) && 'none' !== $attributes['border_style']) {
        $border_width = rtrim((string) $attributes['border_width'], 'px') . 'px';
        $border_color = !empty($attributes['border_color']) ? $attributes['border_color'] : 'transparent';
        $border_css   = 'border: ' . esc_attr($border_width . ' ' . $attributes['border_style'] . ' ' . $border_color) . ';';
    }

    $class_name = !empty($attributes['className']) ? ' ' . esc_attr($attributes['className']) : '';

    if ('' !== $border_css || '' !== $class_name) {
        $wrapper_style = $border_css ? ' style="' . $border_css . ' display: inline-block; width: 100%; max-width: ' . esc_attr('fullwidth' === $width_str ? '100%' : $width_str) . ';"' : '';
        return '<div class="wpvr-block-wrapper' . $class_name . '"' . $wrapper_style . '>' . $output . '</div>';
    }

    return $output;
}

function sanitize_content_preserve_styles($content, $allow_forms = false) {
    // Decode HTML entities first (in case content was encoded in database)
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Escape <script> blocks to display as text instead of removing them
    $content = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/si', function($matches) {
        return esc_html($matches[0]); // Convert to plain text
    }, $content);

    // Strip dangerous URL-based attributes
    $content = preg_replace('/(href|action|formaction)\s*=\s*["\']?\s*(javascript|vbscript|data|about):/i', '$1=""', $content);

    // Escape inline event handlers (onclick, onhover, etc.) to display as text
    $content = preg_replace_callback('/\s*(on\w+)\s*=\s*(["\'])([^"\']*)\2/i', function($matches) {
        // Escape the attribute value but keep the attribute name visible as text
        return ' ' . esc_html($matches[1]) . '=' . $matches[2] . esc_html($matches[3]) . $matches[2];
    }, $content);
    $content = preg_replace_callback('/\s*(on\w+)\s*=\s*([^>\s]+)/i', function($matches) {
        // Handle unquoted event handlers
        return ' ' . esc_html($matches[1]) . '=' . esc_html($matches[2]);
    }, $content);

    // Remove unsafe embedded/interactive elements
    if ($allow_forms) {
        $content = preg_replace('/<(object|embed|applet|frame|frameset|meta|link|base)\b[^>]*>/i', '', $content);
        $content = preg_replace('/<\/(object|embed|applet|frame|frameset|meta|link|base)>/i', '', $content);
    } else {
        $content = preg_replace('/<(object|embed|applet|frame|frameset|meta|link|base|form|input|button|textarea|select|option)\b[^>]*>/i', '', $content);
        $content = preg_replace('/<\/(object|embed|applet|frame|frameset|meta|link|base|form|input|button|textarea|select|option)>/i', '', $content);
    }

    // Clean style attributes safely
    $content = preg_replace_callback('/style\s*=\s*["\']([^"\']*)["\']/', function($matches) {
        $style = $matches[1];
        $style = preg_replace('/expression\s*\(/i', '', $style);
        $style = preg_replace('/(javascript|vbscript|data|about)\s*:/i', '', $style);
        $style = preg_replace('/url\s*\(\s*["\']?\s*(javascript|vbscript|data):/i', '', $style);
        $style = preg_replace('/behavior\s*:/i', '', $style);
        $style = preg_replace('/-moz-binding\s*:/i', '', $style);
        return 'style="' . esc_attr($style) . '"';
    }, $content);

    // Sanitize <style> blocks
    $content = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/si', function($matches) {
        $css = $matches[1];
        $css = preg_replace('/(expression|javascript|vbscript|data|about)\s*:/i', '', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*(javascript|vbscript|data):/i', '', $css);
        $css = preg_replace('/behavior\s*:/i', '', $css);
        $css = preg_replace('/-moz-binding\s*:/i', '', $css);
        return '<style>' . esc_html($css) . '</style>';
    }, $content);

    // Allow iframes from safe sources only (e.g., YouTube, Vimeo)
    $allowed_tags = wp_kses_allowed_html('post');
    $allowed_tags['iframe'] = [
        'src'             => true,
        'width'           => true,
        'height'          => true,
        'frameborder'     => true,
        'allowfullscreen' => true,
        'class'           => true,
        'style'           => true,
        'title'           => true,
        'allow'           => true,
        'name'            => true,
        'referrerpolicy'  => true,
        'loading'         => true,
        'sandbox'         => true,
    ];
    $allowed_tags['img'] = [
        'src'      => true,
        'alt'      => true,
        'title'    => true,
        'width'    => true,
        'height'   => true,
        'class'    => true,
        'id'       => true,
        'style'    => true,
        'loading'  => true,
        'srcset'   => true,
        'sizes'    => true,
    ];

    if ($allow_forms) {
        $form_attributes = [
            'id'          => true,
            'class'       => true,
            'style'       => true,
            'name'        => true,
            'value'       => true,
            'type'        => true,
            'placeholder' => true,
            'action'      => true,
            'method'      => true,
            'target'      => true,
            'enctype'     => true,
            'disabled'    => true,
            'readonly'    => true,
            'required'    => true,
            'checked'     => true,
            'selected'    => true,
            'multiple'    => true,
            'size'        => true,
            'rows'        => true,
            'cols'        => true,
            'maxlength'   => true,
            'minlength'   => true,
            'min'         => true,
            'max'         => true,
            'step'        => true,
            'pattern'     => true,
            'autocomplete'=> true,
            'autofocus'   => true,
            'for'         => true,
        ];
        $allowed_tags['form']     = $form_attributes;
        $allowed_tags['input']    = $form_attributes;
        $allowed_tags['button']   = $form_attributes;
        $allowed_tags['textarea'] = $form_attributes;
        $allowed_tags['select']   = $form_attributes;
        $allowed_tags['option']   = $form_attributes;
        $allowed_tags['optgroup'] = $form_attributes;
        $allowed_tags['label']    = $form_attributes;
        $allowed_tags['fieldset'] = $form_attributes;
        $allowed_tags['legend']   = $form_attributes;
    }

    // Apply wp_kses() to keep only allowed tags/attributes
    $content = wp_kses($content, $allowed_tags);

    // Finally, validate iframe src for security (allow https only, block javascript: etc.)
    $content = preg_replace_callback('/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>(?:<\/iframe>)?/i', function($matches) {
        $src = $matches[1];
        // Allow any https:// or http:// URL, but block javascript:, data:, vbscript:, etc.
        if (preg_match('/^(https?:)?\/\//i', $src) && !preg_match('/^(javascript|data|vbscript|about):/i', $src)) {
            return $matches[0]; // keep safe iframe
        }
        // Strip unsafe iframe
        return '';
    }, $content);

    return $content;
}
function wpvr_hex2rgb($colour)
{
    if (isset($colour[0]) && $colour[0] == '#') {
        $colour = substr($colour, 1);
    }
    if (strlen($colour) == 6) {
        list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
    } elseif (strlen($colour) == 3) {
        list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
    } else {
        return false;
    }
    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);
    return array($r . ', ' . $g . ', ' . $b);
}

function wpvr_HTMLToRGB($htmlCode)
{
    $r = 0;
    $g = 0;
    $b = 0;
    if (isset($htmlCode[0]) && $htmlCode[0] == '#') {
        $htmlCode = substr($htmlCode, 1);
    }

    if (strlen($htmlCode) == 3) {
        $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
    }

    if (isset($htmlCode[0]) && isset($htmlCode[1])) {
        $r = hexdec($htmlCode[0] . $htmlCode[1]);
    }
    if (isset($htmlCode[2]) && isset($htmlCode[3])) {
        $g = hexdec($htmlCode[2] . $htmlCode[3]);
    }
    if (isset($htmlCode[4]) && isset($htmlCode[5])) {
        $b = hexdec($htmlCode[4] . $htmlCode[5]);
    }

    return $b + ($g << 0x8) + ($r << 0x10);
}

function wpvr_RGBToHSL($RGB)
{
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if ($maxC == $minC) {
        $s = 0;
        $h = 0;
    } else {
        if ($l < .5) {
            $s = ($maxC - $minC) / ($maxC + $minC);
        } else {
            $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
        }
        if ($r == $maxC) {
            $h = ($g - $b) / ($maxC - $minC);
        }
        if ($g == $maxC) {
            $h = 2.0 + ($b - $r) / ($maxC - $minC);
        }
        if ($b == $maxC) {
            $h = 4.0 + ($r - $g) / ($maxC - $minC);
        }

        $h = $h / 6.0;
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    return (object) array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
}

add_action('rest_api_init', 'wpvr_rest_data_route');
function wpvr_rest_data_route()
{
    register_rest_route('wpvr/v1', '/panodata/', array(
        'methods' => 'GET',
        'callback' => 'wpvr_rest_data_set',
        'permission_callback' => 'wpvr_rest_route_permission'
    ));
}

function wpvr_rest_route_permission()
{
    return true;
}

function wpvr_rest_data_set()
{
    $query = new WP_Query(array(
        'post_type' => 'wpvr_item',
        'posts_per_page' => -1,
    ));

    $wpvr_list = array();
    $list_none = array('value' => 0, 'label' => 'None');
    array_push($wpvr_list, $list_none);
    while ($query->have_posts()) {
        $query->the_post();
        $title = mb_convert_encoding(get_the_title(), 'UTF-8', 'HTML-ENTITIES');
        $post_id = get_the_ID();
        $title = $post_id . ' : ' . $title;
        $list_ob = array('value' => $post_id, 'label' => $title);
        array_push($wpvr_list, $list_ob);
    }

    return $wpvr_list;
}

function wpvr_isMobileDevice()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function wpvr_directory()
{
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir_temp = $upload_dir . '/wpvr/temp/';
    if (!is_dir($upload_dir_temp)) {
        wp_mkdir_p($upload_dir_temp, 0700);
    }
}

add_action('admin_init', 'wpvr_directory');


function wpvr_add_role_cap()
{
    $editor_active = get_option('wpvr_editor_active');

    $author_active = get_option('wpvr_author_active');

    $admin = get_role('administrator');
    $admin->add_cap('publish_wpvr_tour');
    $admin->add_cap('edit_wpvr_tours');
    $admin->add_cap('read_wpvr_tour');
    $admin->add_cap('edit_wpvr_tour');
    $admin->add_cap('edit_wpvr_tours');
    $admin->add_cap('publish_wpvr_tours');
    $admin->add_cap('publish_wpvr_tour');
    $admin->add_cap('delete_wpvr_tour');
    $admin->add_cap('edit_other_wpvr_tours');
    $admin->add_cap('delete_other_wpvr_tours');

    if ($editor_active == "true") {
        $editor = get_role('editor');
        if ($editor) {
            $editor->add_cap('publish_wpvr_tour');
            $editor->add_cap('edit_wpvr_tours');
            $editor->add_cap('read_wpvr_tour');
            $editor->add_cap('edit_wpvr_tour');
            $editor->add_cap('edit_wpvr_tours');
            $editor->add_cap('publish_wpvr_tours');
            $editor->add_cap('publish_wpvr_tour');
            $editor->add_cap('delete_wpvr_tour');
            $editor->add_cap('edit_other_wpvr_tours');
            $editor->add_cap('delete_other_wpvr_tours');
        }
    } else {
        $editor = get_role('editor');
        if ($editor) {
            $editor->remove_cap('publish_wpvr_tour');
            $editor->remove_cap('edit_wpvr_tours');
            $editor->remove_cap('read_wpvr_tour');
            $editor->remove_cap('edit_wpvr_tour');
            $editor->remove_cap('edit_wpvr_tours');
            $editor->remove_cap('publish_wpvr_tours');
            $editor->remove_cap('publish_wpvr_tour');
            $editor->remove_cap('delete_wpvr_tour');
            $editor->remove_cap('edit_other_wpvr_tours');
            $editor->remove_cap('delete_other_wpvr_tours');
        }
    }

    if ($author_active == "true") {
        $author = get_role('author');
        if ($author) {
            $author->add_cap('read_wpvr_tour');
            $author->add_cap('edit_wpvr_tour');
            $author->add_cap('edit_wpvr_tours');
            $author->add_cap('publish_wpvr_tours');
            $author->add_cap('publish_wpvr_tour');
            $author->add_cap('delete_wpvr_tour');
        }
    } else {
        $author = get_role('author');
        if ($author) {
            $author->remove_cap('read_wpvr_tour');
            $author->remove_cap('edit_wpvr_tour');
            $author->remove_cap('edit_wpvr_tours');
            $author->remove_cap('publish_wpvr_tours');
            $author->remove_cap('publish_wpvr_tour');
            $author->remove_cap('delete_wpvr_tour');
        }
    }

    if(is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active( 'dokan-pro/dokan.php' )){
        $dokan_vendor_active = get_option('dokan_vendor_active');

        if( 'true' === $dokan_vendor_active){
            $seller = get_role('seller');
            if ($seller) {
                $seller->add_cap('read_wpvr_tour');
                $seller->add_cap('edit_wpvr_tour');
                $seller->add_cap('edit_wpvr_tours');
                $seller->add_cap('publish_wpvr_tours');
                $seller->add_cap('publish_wpvr_tour');
                $seller->add_cap('delete_wpvr_tour');
            }
        } else{
            $seller = get_role('seller');
            if ($seller) {
                $seller->remove_cap('read_wpvr_tour');
                $seller->remove_cap('edit_wpvr_tour');
                $seller->remove_cap('edit_wpvr_tours');
                $seller->remove_cap('publish_wpvr_tours');
                $seller->remove_cap('publish_wpvr_tour');
                $seller->remove_cap('delete_wpvr_tour');
            }
        }
    }


}

add_action('admin_init', 'wpvr_add_role_cap', 999);

function wpvr_role_management_from_post_type($args, $post_type)
{
    if ('wpvr_item' !== $post_type) {
        return $args;
    }

    $editor_active = get_option('wpvr_editor_active');
    $author_active = get_option('wpvr_author_active');
    $user = wp_get_current_user();

    if ($editor_active == "true") {
        if (in_array('editor', (array) $user->roles)) {
            $args['show_in_menu'] = true;
        }
    }

    if ($author_active == "true") {
        if (in_array('author', (array) $user->roles)) {
            $args['show_in_menu'] = true;
        }
    }

    if(is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active( 'dokan-pro/dokan.php' )){
        $dokan_vendor_active = get_option('dokan_vendor_active');
        if( 'true' === $dokan_vendor_active){
            if (in_array('seller', (array) $user->roles)) {
                $args['show_in_menu'] = true;
            }
        }

    }

    return $args;
}
add_filter('register_post_type_args', 'wpvr_role_management_from_post_type', 10, 2);

function wpvr_cache_admin_notice()
{
    $option = get_option('wpvr_warning');
    if (!$option) {
        ?>
        <div class="notice notice-warning" id="wpvr-warning" style="position: relative;">
            <p><?php _e('Since you have updated the plugin, please clear the browser cache for smooth functioning. Follow these steps if you are using <a href="https://support.google.com/accounts/answer/32050?co=GENIE.Platform%3DDesktop&hl=en" target="_blank">Google Chrome</a>, <a href="https://support.mozilla.org/en-US/kb/how-clear-firefox-cache" target="_blank">Mozilla Firefox</a>, <a href="https://clear-my-cache.com/en/apple-mac-os/safari.html" target="_blank">Safai</a> or <a href="https://support.microsoft.com/en-us/help/10607/microsoft-edge-view-delete-browser-history" target="_blank">Microsoft Edge</a>', 'wpvr'); ?></p>
            <button type="button" id="wpvr-dismissible" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
        <?php
    }
}
// add_action('admin_notices', 'wpvr_cache_admin_notice');

//===Oxygen widget===//
add_action('plugins_loaded', function () {
    if (!class_exists('OxyEl')) {
        return;
    }
    require_once __DIR__ . '/oxygen/oxy-manager.php';
});

add_action('init', 'wpvr_mobile_media_handle');
function wpvr_mobile_media_handle()
{
    add_image_size('wpvr_mobile', 4096, 2048); //mobile
}


add_action(
/**
 * @param $api \VisualComposer\Modules\Api\Factory
 */
    'vcv:api',
    function ($api) {
        $elementsToRegister = [
            'wpvrelement',
        ];
        $pluginBaseUrl = rtrim(WPVR_PLUGIN_DIR_URL, '\\/');
        /** @var \VisualComposer\Modules\Elements\ApiController $elementsApi */
        $elementsApi = $api->elements;
        foreach ($elementsToRegister as $tag) {
            $manifestPath = __DIR__ . '/vc/' . $tag . '/manifest.json';
            $elementBaseUrl = $pluginBaseUrl . '/vc/' . $tag;
            $elementsApi->add($manifestPath, $elementBaseUrl);
        }
    }
);

function wpvr_redirect_after_activation($plugin)
{
    if ($plugin == plugin_basename(__FILE__)) {
        $url = admin_url('admin.php?page=rex-wpvr-setup-wizard');
        $url = esc_url($url, FILTER_SANITIZE_URL);
        exit(wp_safe_redirect($url));
    }
}
//add_action('activated_plugin', 'wpvr_redirect_after_activation');

function replace_callback($matches)
{
    foreach ($matches as $match) {
        return str_replace('<img', '<img decoding="async" ', $match);
    }
}


function wpvr_get_explainer_embed_url( $input ) {
    $url = esc_url_raw( trim( (string) $input ), array( 'http', 'https' ) );

    if ( empty( $url ) || ! preg_match( '#^https?://#i', $url ) ) {
        return '';
    }

    $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
    $host = preg_replace( '/^www\./', '', $host );
    $path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );

    if ( 'youtu.be' === $host ) {
        $segments = explode( '/', $path );
        $video_id = isset( $segments[0] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', $segments[0] ) : '';

        return $video_id ? 'https://www.youtube.com/embed/' . $video_id : '';
    }

    if ( in_array( $host, array( 'youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com' ), true ) ) {
        $video_id = '';
        $segments = explode( '/', $path );

        if ( 'watch' === $path ) {
            $query = array();
            wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
            $video_id = isset( $query['v'] ) ? $query['v'] : '';
        } elseif ( isset( $segments[0], $segments[1] ) && in_array( $segments[0], array( 'embed', 'shorts', 'live' ), true ) ) {
            $video_id = $segments[1];
        }

        $video_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $video_id );

        return $video_id ? 'https://www.youtube.com/embed/' . $video_id : '';
    }

    if ( 'vimeo.com' === $host || 'player.vimeo.com' === $host ) {
        $segments = array_reverse( array_filter( explode( '/', $path ) ) );

        foreach ( $segments as $segment ) {
            if ( ctype_digit( $segment ) ) {
                return 'https://player.vimeo.com/video/' . $segment;
            }
        }

        return '';
    }

    return $url;
}

function wpvr_sanitize_iframe_only( $input ) {
    // Start with standard allowed post HTML (p, a, strong, etc.)
    $allowed_tags = wp_kses_allowed_html( 'post' );

    // Explicitly allow <iframe> with specific safe attributes
    $allowed_tags['iframe'] = array(
        'src'             => true,
        'width'           => true,
        'height'          => true,
        'title'           => true,
        'frameborder'     => true,
        'allow'           => true,
        'allowfullscreen' => true,
        'referrerpolicy'  => true,
    );

    $content = trim( (string) $input );

    if ( '' === $content ) {
        return '';
    }

    if ( false === stripos( $content, '<iframe' ) ) {
        $embed_url = wpvr_get_explainer_embed_url( $content );

        if ( $embed_url ) {
            $content = sprintf(
                '<iframe src="%s" width="100%%" height="100%%" title="Explainer video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>',
                esc_url( $embed_url )
            );
        }
    }

    return wp_kses( $content, $allowed_tags );
}
