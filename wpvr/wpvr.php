<?php

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
 * Plugin Name:       WP VR
 * Plugin URI:        https://rextheme.com/wpvr/
 * Description:       WP VR - 360 Panorama and virtual tour creator for WordPress is a customized panaroma & virtual builder tool for WordPress Website.
 * Version:           8.5.36
 * Tested up to:      6.8.2
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

require plugin_dir_path(__FILE__) . 'elementor/elementor.php';

if ( wp_get_theme('bricks')->exists() && 'bricks' === get_template()) {
    require_once plugin_dir_path(__FILE__) . 'bricks/bricks.php';
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WPVR_VERSION', '8.5.36');
define('WPVR_FILE', __FILE__);
define("WPVR_PLUGIN_DIR_URL", plugin_dir_url(__FILE__));
define("WPVR_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
define("WPVR_PLUGIN_PUBLIC_DIR_URL", plugin_dir_url(__FILE__) . 'public/');
define('WPVR_BASE', plugin_basename(WPVR_FILE));
define('WPVR_DEV_MODE', false);
define('WPVR_JS_PATH', plugin_dir_url(__FILE__) . 'admin/js/');
define('WPVR_ASSET_PATH', plugin_dir_url(__FILE__) . 'admin/');
define( 'WPVR_WEBHOOK_URL', sanitize_url( 'https://rextheme.com/?mailmint=1&route=webhook&topic=contact&hash=bbd19901-6d42-4ae5-a7a8-01eb1013c553' ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpvr-activator.php
 */
function activate_wpvr()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wpvr-activator.php';
    Wpvr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpvr-deactivator.php
 */
function deactivate_wpvr()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wpvr-deactivator.php';
    Wpvr_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wpvr');
register_deactivation_hook(__FILE__, 'deactivate_wpvr');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wpvr.php';

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

    // black friday banner class initialization
    new WPVR_Special_Occasion_Banner(
        '4th_of_july_deal_2025',
        '2025-07-02 00:00:01',
        '2025-07-14 23:59:59'
    );

    // if (!defined('WPVR_PRO_VERSION') && 'no' === get_option('wpvr_sell_notification_bar', 'no')) {
    //     new WPVR_Notification_Bar();
    // }


}
run_wpvr();


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


/**
 * Initialize the plugin tracker
 *
 * @return void
 * @since 7.3.6
 */
function appsero_init_tracker_wpvr()
{
    if (!class_exists('Appsero\Client')) {
        require_once __DIR__ . '/appsero/src/Client.php';
    }
    $client = new Appsero\Client('cab9761e-b067-4824-9c71-042df5d58598', 'WP VR', __FILE__);

    // Active insights
    $client->insights()->init();
}

appsero_init_tracker_wpvr();


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
                'border_width' => array(
                    'type' => 'string',
                    'default' => '0px',
                ),
                'border_style' => array(
                    'type' => 'string',
                    'default' => 'none',
                ),
                'border_color' => array(
                    'type' => 'string',
                    'default' => 'none',
                ),
                'radius_unit' => array(
                    'type' => 'string',
                    'default' => 'px',
                ),
                'content' => array(
                    'type' => 'string',
                    'source' => 'html',
                    'default' => '<script>          </script>'
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
    if (isset($attributes['id'])) {
        $id = $attributes['id'];
    } else {
        $id = 0;
    }

    $memberpress_active = defined('MEPR_VERSION');
    $rcp_active = is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' );

    if (($memberpress_active || $rcp_active ) && apply_filters('is_wpvr_pro_active', false)) {

        if (!is_user_logged_in()) {
            return esc_html__('You need to log in to access this content.', 'wpvr');
        }

        $user_id = get_current_user_id();
        $allowed_access = false;

        // Get saved membership levels from post meta
        $allowed_membership_levels = get_post_meta($id, '_wpvr_allowed_roles_levels', true);
        if ( isset($allowed_membership_levels) && 'none' !== $allowed_membership_levels ) {
            if (!is_array($allowed_membership_levels)) {
                $allowed_membership_levels = array($allowed_membership_levels);
            }

            // Check MemberPress Access
            if ($memberpress_active) {
                $user = new MeprUser($user_id);
                $active_memberships = $user->active_product_subscriptions();
                if (array_intersect($allowed_membership_levels, $active_memberships) ) {
                    $allowed_access = true;
                }
            }

            // Check Restrict Content Pro Access
            if ($rcp_active) {
                $user_rcp_memberships = rcp_get_customer_memberships($user_id);
                foreach ($user_rcp_memberships as $membership) {
                    $rcp_access_level = array(
                        $membership->get_object_id()
                    );
                    if (array_intersect($allowed_membership_levels, $rcp_access_level) ) {
                        $allowed_access = true;
                        break;
                    }
                }
            }
        } else {
            // If no membership restriction is set, allow access by default
            $allowed_access = true;
        }

        if (!$allowed_access) {
            return esc_html__('You do not have access to this content.', 'wpvr');
        }
    }


    if (isset($attributes['width'])) {
        $width = $attributes['width'];
    }
    if (isset($attributes['width_unit'])) {
        $width_unit = $attributes['width_unit'];
    }
    if (isset($attributes['height'])) {
        $height = $attributes['height'];
    }
    if (isset($attributes['height_unit'])) {
        $height_unit = $attributes['height_unit'];
    }
    if (isset($attributes['mobile_height'])) {
        $mobile_height = $attributes['mobile_height'];
    }
    if (isset($attributes['mobile_height_unit'])) {
        $mobile_height_unit = $attributes['mobile_height_unit'];
    }
    if (isset($attributes['radius']) && isset($attributes['radius_unit'])) {
        $radius = $attributes['radius'] . $attributes['radius_unit'];
    }
    $border_style = '';
    if (isset($attributes['border_width'], $attributes['border_style'], $attributes['border_color'])) {
        $border_style = $attributes['border_width'] . 'px ' . $attributes['border_style'] . ' ' . $attributes['border_color'];
    }

    if (isset($attributes['className'])) {
        $className = $attributes['className'];
    } else {
        $className = '';
    }
    $get_post = get_post_status($id);
    if ($get_post !== 'publish') {
        return esc_html__('Oops! It seems like this post isn\'t published yet. Stay tuned for updates!', 'wpvr');
    }
    if (post_password_required($id)) {
        return get_the_password_form();
    }
    $postdata = get_post_meta($id, 'panodata', true);
    $panoid = 'pano' . $id;
    $panoid2 = 'pano2' . $id;
    if (!isset($postdata) || empty($postdata)) {
        return wp_kses(
            __('Oops! It seems that you have not selected any tour yet. Please select a tour from the dropdown of WPVR block.<br>', 'wpvr'),
            ['br' => []]
        );
    }
    if (isset($postdata['streetviewdata'])) {
        if (empty($width)) {
            $width = '600px';
        }
        if (empty($height)) {
            $height = '400px';
        }
        $streetviewurl = $postdata['streetviewurl'];
        $html = '';
        $html .= '<div class="vr-streetview ' . esc_attr( $className ) . '" style="text-align: center; max-width:100%; width:' . esc_attr( $width ) . esc_attr( $width_unit ) . '; height:' . esc_attr( $height ) . esc_attr( $height_unit ) . '; margin: 0 auto;">';
        $html .= '<iframe src="' . esc_url( $streetviewurl ) . '" frameborder="0" style="border:0; width:100px; height:100%;" allowfullscreen=""></iframe>';
        $html .= '</div>';

        return $html;
    }
    $is_pro = apply_filters('is_wpvr_pro_active', false);

    if (isset($postdata['vidid'])) {
        if (empty($width)) {
            $width = '600';
        }
        if (empty($height)) {
            $height = '400';
        }
        $videourl = $postdata['vidurl'];

        $videourl = $postdata['vidurl'];
        $autoplay = 'off';
        if (isset($postdata['autoplay'])) {
            $autoplay = $postdata['autoplay'];
        }
        $loop = 'off';
        if (isset($postdata['loop'])) {
            $loop = $postdata['loop'];
        }

        if (strpos($videourl, 'youtube') > 0 || strpos($videourl, 'youtu') > 0) {
            $explodeid = '';
            $explodeid = explode("=", $videourl);
            $foundid = '';
            $muted = '&mute=1';

            if ($autoplay == 'on') {
                $autoplay = '&autoplay=1';
            } else {
                $autoplay = '';
            }

            if ($loop == 'on') {
                $loop = '&loop=1';
            } else {
                $loop = '';
            }

            if (strpos($videourl, 'youtu') > 0) {
                $explodeid = explode("/", $videourl);
                $foundid = $explodeid[3] . '?' . $autoplay . $loop;
                $expdata = $explodeid[3];
            } else {
                $foundid = $explodeid[1] . '?' . $autoplay . $loop;
                $expdata = $explodeid[1];
            }

            $playlist = '&playlist=' . $expdata;
            $playlist = str_replace("?feature=shared", "", $playlist);


            $html = '';
            $html .= '<div class="' . esc_attr($className) . '" style="text-align:center; max-width:100%; width:' . esc_attr($width) . esc_attr($width_unit) . '; height:' . esc_attr($height) . esc_attr($height_unit) . '; border-radius: ' . esc_attr($radius) . '; margin: 0 auto;">';

            // Compatibility check script
            $html .= '<script>
            function wpvr_check_360_support() {
                var support = {
                    supported: false,
                    fullySupported: false,
                    webgl: false,
                    orientation: false,
                    gyroscope: false,
                    touch: false,
                    browser: "unknown",
                    isMobile: false,
                    isIPhone: false,
                    details: []
                };
            
                support.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                support.isIPhone = /iPhone/i.test(navigator.userAgent);
                if (support.isMobile) support.details.push("Mobile device detected");
                if (support.isIPhone) support.details.push("iPhone detected");
            
                var ua = navigator.userAgent;
                if (/^((?!chrome|android).)*safari/i.test(ua)) {
                    support.browser = "safari";
                    support.details.push("Safari browser detected");
                } else if (ua.indexOf("Chrome") > -1) {
                    support.browser = "chrome";
                    support.details.push("Chrome browser detected");
                } else if (ua.indexOf("Firefox") > -1) {
                    support.browser = "firefox";
                    support.details.push("Firefox browser detected");
                } else if (ua.indexOf("MSIE") > -1 || ua.indexOf("Trident") > -1) {
                    support.browser = "ie";
                    support.details.push("Internet Explorer detected");
                } else if (ua.indexOf("Edge") > -1 || ua.indexOf("Edg") > -1) {
                    support.browser = "edge";
                    support.details.push("Edge browser detected");
                } else if (ua.indexOf("Opera") > -1 || ua.indexOf("OPR") > -1) {
                    support.browser = "opera";
                    support.details.push("Opera browser detected");
                }
            
                try {
                    var canvas = document.createElement("canvas");
                    support.webgl = !!(window.WebGLRenderingContext &&
                        (canvas.getContext("webgl") || canvas.getContext("experimental-webgl")));
                    support.details.push(support.webgl ? "WebGL supported" : "WebGL not supported");
                } catch (e) {
                    support.webgl = false;
                    support.details.push("WebGL detection error: " + e.message);
                }
            
                support.orientation = !!(window.DeviceOrientationEvent);
                support.details.push(support.orientation ? "Device Orientation API supported" : "Device Orientation API not supported");
            
                support.gyroscope = !!window.Gyroscope;
                support.details.push(support.gyroscope ? "Gyroscope supported" : "Gyroscope not supported");
            
                support.touch = "ontouchstart" in window || navigator.maxTouchPoints > 0;
                support.details.push(support.touch ? "Touch supported" : "Touch not supported");
            
                support.supported = support.webgl;
                support.fullySupported = support.webgl && ((support.isMobile && support.orientation) || !support.isMobile);
            
                if (support.browser === "safari" && support.isIPhone) {
                    support.browserWarning = "iPhone has limited support for 360 videos in browser. The experience may not be optimal.";
                    support.fullySupported = false;
                } else if (support.browser === "ie") {
                    support.browserWarning = "Internet Explorer has limited support for 360 videos.";
                    support.fullySupported = false;
                } else if (!support.supported) {
                    support.browserWarning = "Your browser does not support 360° videos. Use Chrome or Firefox with WebGL.";
                }
            
                return support;
            }
            </script>';

            $random_id = 'video-container-' . rand(10000, 99999);
            $html .= '<div id="' . $random_id . '-container" style="position:relative; width:100%; height:100%;">';

            // Compatibility check screen (initially hidden for all, will show only for iPhone)
            $html .= '<div id="' . $random_id . '-compatibility-check" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:#f9f9f9; border-radius:' . esc_attr( $radius ) . ';">
                        <div style="margin-bottom:20px; text-align:center;">
                            <div class="wpvr-loading-spinner" style="border:5px solid #f3f3f3; border-top:5px solid #3498db; border-radius:50%; width:50px; height:50px; margin:0 auto 15px; animation:wpvr-spin 1s linear infinite;"></div>
                            <p style="margin:0;">Checking browser compatibility...</p>
                        </div>
                       </div>';

            // Spinner animation
            $html .= '<style>@keyframes wpvr-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';

            // Iframe (initial display set to block)
            $html .= '<div id="' . $random_id . '-frame" class="' . esc_attr($className) . '" style="display:block; max-width:100%; width:' . esc_attr($width) . esc_attr($width_unit) . '; height:' . esc_attr($height) . esc_attr($height_unit) . '; border-radius: ' . esc_attr($radius) . '; margin: 0 auto;">';
            $html .= '<iframe id="' . $random_id . '-iframe" src="https://www.youtube.com/embed/' . rawurlencode(sanitize_text_field($expdata)) . '?rel=0&modestbranding=1' . esc_attr($loop) . '&autohide=1' . esc_attr($muted) . '&showinfo=0&controls=1' . esc_attr($autoplay) . esc_attr($playlist) . '&enablejsapi=1" width="100%" height="100%" style="border-radius: ' . esc_attr($radius) . ';" frameborder="0" allowfullscreen allow="accelerometer; gyroscope; picture-in-picture"></iframe>';
            $html .= '</div>';

            // Permission request (mobile only)
            $html .= '<div id="' . $random_id . '-permission-request" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.7); color:white; text-align:center; border-radius:' . $radius . ';">';
            $html .= '<p style="font-size:16px; margin:0 20px 15px;">For the best 360° video experience on mobile</p>';
            $html .= '<button id="' . $random_id . '-permission-button" style="padding:10px 15px; background-color:#0085ba; color:#fff; border:none; border-radius:4px; cursor:pointer;">Allow motion and orientation access</button>';
            $html .= '</div>';

            // Browser warning
            $html .= '<div id="' . $random_id . '-browser-warning" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.7); color:white; text-align:center; border-radius:' . $radius . ';">';
            $html .= '<p id="' . $random_id . '-warning-text" style="font-size:16px; margin:0 20px 5px;"></p>';
            $html .= '<p style="font-size:14px; margin:5px 20px 15px;">For the best experience, use Chrome or Firefox.</p>';
            $html .= '<button id="' . $random_id . '-browser-continue" style="padding:10px 15px; background-color:#0085ba; color:#fff; border:none; border-radius:4px; cursor:pointer;">Continue Anyway</button>';
            $html .= '</div>';

            // Main script
            $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var supportInfo = wpvr_check_360_support();
                var compatibilityCheck = document.getElementById("' . $random_id . '-compatibility-check");
                var frameContainer = document.getElementById("' . $random_id . '-frame");
                var warning = document.getElementById("' . $random_id . '-browser-warning");
                var warningText = document.getElementById("' . $random_id . '-warning-text");
                var continueBtn = document.getElementById("' . $random_id . '-browser-continue");
                
                // Only show compatibility check and warning for iPhone
                if (supportInfo.isIPhone) {
                    // Hide the iframe initially for iPhone
                    frameContainer.style.display = "none";
                    
                    // Show compatibility check for iPhone
                    compatibilityCheck.style.display = "flex";
                    
                    // After delay, hide compatibility check and show warning
                    setTimeout(function() {
                        compatibilityCheck.style.display = "none";
                        warningText.textContent = supportInfo.browserWarning || "Your browser may not fully support 360° videos.";
                        warning.style.display = "flex";
            
                        continueBtn.addEventListener("click", function() {
                            warning.style.display = "none";
            
                            if (supportInfo.fullySupported && supportInfo.isMobile) {
                                document.getElementById("' . $random_id . '-permission-request").style.display = "flex";
                                document.getElementById("' . $random_id . '-permission-button").addEventListener("click", function() {
                                    requestDevicePermissions();
                                });
                            } else {
                                showVideo();
                            }
                        });
                    }, 1000);
                } else {
                    // For non-iPhone devices, directly initialize the player
                    initializeYouTubePlayer();
                }
            
                function requestDevicePermissions() {
                    try {
                        if (typeof DeviceOrientationEvent !== "undefined" && 
                            typeof DeviceOrientationEvent.requestPermission === "function") {
                            DeviceOrientationEvent.requestPermission().then(function(response) {
                                if (response === "granted") {
                                    if (typeof DeviceMotionEvent !== "undefined" && 
                                        typeof DeviceMotionEvent.requestPermission === "function") {
                                        DeviceMotionEvent.requestPermission().then(showVideo).catch(showVideo);
                                    } else {
                                        showVideo();
                                    }
                                } else {
                                    showVideo();
                                }
                            }).catch(showVideo);
                        } else {
                            showVideo();
                        }
                    } catch (e) {
                        showVideo();
                    }
                }
            
                function showVideo() {
                    document.getElementById("' . $random_id . '-permission-request").style.display = "none";
                    frameContainer.style.display = "block";
                    initializeYouTubePlayer();
                }
            
                function initializeYouTubePlayer() {
                    if (window.YT) {
                        initPlayer();
                    } else {
                        var tag = document.createElement("script");
                        tag.src = "https://www.youtube.com/iframe_api";
                        var firstScriptTag = document.getElementsByTagName("script")[0];
                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                        window.onYouTubeIframeAPIReady = initPlayer;
                    }
            
                    function initPlayer() {
                        new YT.Player("' . $random_id . '-iframe", {
                            events: {
                                "onReady": function(event) {}
                            }
                        });
                    }
                }
            });
            </script>';

            $html .= '</div>'; // Close container
            $html .= '</div>'; // Close outer wrapper
        } else {
            $html = '';
            $html .= '<div id="pano' . esc_attr($id) . '" class="pano-wrap ' . esc_attr($className) . '" style="max-width:100%; width:' . esc_attr($width) . esc_attr($width_unit) . '; height: ' . esc_attr($height) . esc_attr($height_unit) . '; border-radius:' . esc_attr($radius) . '; margin: 0 auto;">';
            $html .= '<div style="width:100%; height:100%; border-radius: ' . esc_attr($radius) . ';">' . $postdata['panoviddata'] . '</div>';

            $html .= '
            <style>
                .video-js {
                    border-radius:' . $radius . ';
                }
                .video-js canvas{
                    border-radius:' . $radius . ';
                }
                #pano' . $id . ' .vjs-poster {
                    border-radius: ' . $radius . ';
                }
            </style>
            
            ';

            // $html .= '<script>';
            // $html .= 'videojs(' . $postdata['vidid'] . ', {';
            // $html .= 'plugins: {';
            // $html .= 'pannellum: {}';
            // $html .= '}';
            // $html .= '});';
            // $html .= '</script>';
            $html .= '</div>';

            //video js vr setup //
            $html .= '<script>';
            $html .= '
                (function (window, videojs) {
                    var player = window.player = videojs("' . $postdata['vidid'] . '");
                    player.mediainfo = player.mediainfo || {};
                    player.mediainfo.projection = "equirectangular";
                
                    // AUTO is the default and looks at mediainfo
                    var vr = window.vr = player.vr({ projection: "AUTO", debug: true, forceCardboard: false, antialias: false });
                    }(window, window.videojs));
                
                ';
            $html .= '</script>';
            //video js vr end //
        }
        return $html;
    }

    $control = false;
    if (isset($postdata['showControls'])) {
        $control = $postdata['showControls'];
    }

    if ($control) {
        if (isset($postdata['customcontrol'])) {
            $custom_control = $postdata['customcontrol'];
            if ($custom_control['panupSwitch'] == "on" || $custom_control['panDownSwitch'] == "on" || $custom_control['panLeftSwitch'] == "on" || $custom_control['panRightSwitch'] == "on" || $custom_control['panZoomInSwitch'] == "on" || $custom_control['panZoomOutSwitch'] == "on" || $custom_control['panFullscreenSwitch'] == "on" || $custom_control['gyroscopeSwitch'] == "on" || $custom_control['backToHomeSwitch'] == "on") {
                $control = false;
            }
        }
    }

    $floor_plan_enable = 'off';
    $floor_plan_image = '';
    if (isset($postdata['floor_plan_tour_enabler']) && $postdata['floor_plan_tour_enabler'] == 'on') {
        $floor_plan_enable = $postdata['floor_plan_tour_enabler'];
        if (isset($postdata['floor_plan_attachment_url']) && !empty($postdata['floor_plan_attachment_url'])) {
            $floor_plan_image = $postdata['floor_plan_attachment_url'];
        }
    }

    $vrgallery = false;
    if (isset($postdata['vrgallery'])) {
        $vrgallery = $postdata['vrgallery'];
    }

    $vrgallery_title = false;
    if (isset($postdata['vrgallery_title'])) {
        $vrgallery_title = $postdata['vrgallery_title'];
    }

    $vrgallery_display = false;
    if (isset($postdata['vrgallery_display'])) {
        $vrgallery_display = $postdata['vrgallery_display'];
    }
    $vrgallery_icon_size = false;
    if (isset($postdata['vrgallery_icon_size'])) {
        $vrgallery_icon_size = $postdata['vrgallery_icon_size'];
    }

    $gyro = false;
    $gyro_orientation = false;
    if (isset($postdata['gyro'])) {
        $gyro = $postdata['gyro'];
        if (isset($postdata['deviceorientationcontrol'])) {
            $gyro_orientation = $postdata['deviceorientationcontrol'];
        }
    }

    $compass = false;
    $audio_right = "5px";
    if (isset($postdata['compass'])) {
        $compass = $postdata['compass'] == 'on' || $postdata['compass'] != null ? true : false;
        if ($compass) {
            $audio_right = "60px";
        }
    }
    $floor_map_right = "10px";
    if ((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')) {
        $floor_map_right = "85px";
    } elseif (isset($postdata['compass']) && $postdata['compass'] == 'on') {
        $floor_map_right = "55px";
    } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
        $floor_map_right = "25px";
    }

    //===explainer  handle===//

    //===explainer  handle===//
    $explainer_right = "10px";
    if ((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on') && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
        $explainer_right = "130px";
    } elseif (isset($postdata['compass']) && $postdata['compass'] == 'on' && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
        $explainer_right = "100px";
    } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on" && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
        $explainer_right = "60px";
    } elseif ((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')) {
        $explainer_right = "80px";
    } elseif (isset($postdata['compass']) && $postdata['compass'] == 'on') {
        $explainer_right = "55px";
    } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
        $explainer_right = "30px";
    } elseif ($floor_plan_enable == 'on' && !empty($floor_plan_image)) {
        $explainer_right = "40px";
    }
    $enable_cardboard = '';
    $is_cardboard = get_option('wpvr_cardboard_disable');
    if (wpvr_isMobileDevice() && $is_cardboard == 'true') {
        $enable_cardboard = 'enable-cardboard';
        $audio_right = "73px";
        if (isset($postdata['compass'])) {
            $compass = $postdata['compass'] == 'on' || $postdata['compass'] != null ? true : false;
            if ($compass) {
                $audio_right = "130px";
            }
        }

        $floor_map_right = "60px";
        if ((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')) {
            $floor_map_right = "150px";
        } elseif (isset($postdata['compass']) && $postdata['compass'] == 'on') {
            $floor_map_right = "120px";
        } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
            $floor_map_right = "90px";
        }

        //===explainer  handle===//

        $explainer_right = "65px";

        if ((isset($postdata['compass']) && $postdata['compass'] == true) && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')) {
            $explainer_right = "150px";
        } elseif ((isset($postdata['compass']) && $postdata['compass'] == true) && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on') && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
            $explainer_right = "180px";
        } elseif (isset($postdata['compass']) && $postdata['compass'] == true && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
            $explainer_right = "150px";
        } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on" && ($floor_plan_enable == 'on' && !empty($floor_plan_image))) {
            $explainer_right = "120px";
        } elseif (isset($postdata['compass']) && $postdata['compass'] == true) {
            $explainer_right = "130px";
        } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
            $explainer_right = "90px";
        } elseif ($floor_plan_enable == 'on' && !empty($floor_plan_image)) {
            $explainer_right = "90px";
        }
    }

    //===explainer  handle===//

    $mouseZoom = true;
    if (isset($postdata['mouseZoom'])) {
        if ($postdata['mouseZoom'] == "off") {
            $mouseZoom = false;
        } else {
            $mouseZoom = true;
        }
    }

    $draggable = true;
    if (isset($postdata['draggable'])) {
        $draggable = $postdata['draggable'] == 'on' || $postdata['draggable'] != null ? true : false;
        if ($postdata['draggable'] === 'off') {
            $draggable = false;
        }
    }
    $diskeyboard = false;
    if (isset($postdata['diskeyboard'])) {
        $diskeyboard = $postdata['diskeyboard'] == 'off' || $postdata['diskeyboard'] == null ? false : true;
    }

    $keyboardzoom = true;
    if (isset($postdata['keyboardzoom'])) {
        $keyboardzoom = $postdata['keyboardzoom'];
    }

    $autoload = false;

    if (isset($postdata['autoLoad'])) {
        $autoload = $postdata['autoLoad'];
    }

    $default_scene = '';
    if (isset($postdata['defaultscene'])) {
        $default_scene = $postdata['defaultscene'];
    }

    $preview = '';
    if (isset($postdata['preview'])) {
        $preview = $postdata['preview'];
    }

    $autorotation = '';
    if (isset($postdata["autoRotate"])) {
        $autorotation = $postdata["autoRotate"];
    }
    $autorotationinactivedelay = '';
    if (isset($postdata["autoRotateInactivityDelay"])) {
        $autorotationinactivedelay = $postdata["autoRotateInactivityDelay"];
    }
    $autorotationstopdelay = '';
    if (isset($postdata["autoRotateStopDelay"])) {
        $autorotationstopdelay = $postdata["autoRotateStopDelay"];
    }

    $scene_fade_duration = '';
    if (isset($postdata['scenefadeduration'])) {
        $scene_fade_duration = $postdata['scenefadeduration'];
    }

    $panodata = array();
    if (isset($postdata['panodata'])) {
        $panodata = $postdata['panodata'];
    }

    $default_zoom_global = 100;
    if (isset($postdata['hfov']) && $postdata['hfov'] != '') {
        $default_zoom_global = $postdata['hfov'];
    }

    $min_zoom_global = 50;
    if (isset($postdata['minHfov']) && $postdata['minHfov'] != '') {
        $min_zoom_global = $postdata['minHfov'];
    }

    $max_zoom_global = 120;
    if (isset($postdata['maxHfov']) && $postdata['maxHfov'] != '') {
        $max_zoom_global = $postdata['maxHfov'];
    }

    $hotspoticoncolor = '#00b4ff';
    $hotspotblink = 'on';
    $default_data = array();
    $default_data = array('firstScene' => $default_scene, 'sceneFadeDuration' => $scene_fade_duration, 'hfov' => $default_zoom_global, 'maxHfov' => $max_zoom_global, 'minHfov' => $min_zoom_global);
    $scene_data = array();
    if (is_array($panodata) && isset($panodata['scene-list'])) {
        if (!empty($panodata['scene-list'])) {
            foreach ($panodata['scene-list'] as $panoscenes) {
                $scene_ititle = '';
                if (isset($panoscenes["scene-ititle"])) {
                    $scene_ititle = sanitize_text_field($panoscenes["scene-ititle"]);
                }

                $scene_author = '';
                if (isset($panoscenes["scene-author"])) {
                    $scene_author = sanitize_text_field($panoscenes["scene-author"]);
                }

                $scene_author_url = '';
                if (isset($panoscenes["scene-author-url"])) {
                    $scene_author_url = sanitize_text_field($panoscenes["scene-author-url"]);
                }

                $scene_vaov = 180;
                if (isset($panoscenes["scene-vaov"])) {
                    $scene_vaov = (float)$panoscenes["scene-vaov"];
                }

                $scene_haov = 360;
                if (isset($panoscenes["scene-haov"])) {
                    $scene_haov = (float)$panoscenes["scene-haov"];
                }

                $scene_vertical_offset = 0;
                if (isset($panoscenes["scene-vertical-offset"])) {
                    $scene_vertical_offset = (float)$panoscenes["scene-vertical-offset"];
                }

                $default_scene_pitch = null;
                if (isset($panoscenes["scene-pitch"])) {
                    $default_scene_pitch = (float)$panoscenes["scene-pitch"];
                }

                $default_scene_yaw = null;
                if (isset($panoscenes["scene-yaw"])) {
                    $default_scene_yaw = (float)$panoscenes["scene-yaw"];
                }

                $scene_max_pitch = '';
                if (isset($panoscenes["scene-maxpitch"])) {
                    $scene_max_pitch = (float)$panoscenes["scene-maxpitch"];
                }


                $scene_min_pitch = '';
                if (isset($panoscenes["scene-minpitch"])) {
                    $scene_min_pitch = (float)$panoscenes["scene-minpitch"];
                }


                $scene_max_yaw = '';
                if (isset($panoscenes["scene-maxyaw"])) {
                    $scene_max_yaw = (float)$panoscenes["scene-maxyaw"];
                }


                $scene_min_yaw = '';
                if (isset($panoscenes["scene-minyaw"])) {
                    $scene_min_yaw = (float)$panoscenes["scene-minyaw"];
                }

                $default_zoom = 100;
                if (isset($panoscenes["scene-zoom"]) && $panoscenes["scene-zoom"] != '') {
                    $default_zoom = (int)$panoscenes["scene-zoom"];
                } else {
                    if ($default_zoom_global != '') {
                        $default_zoom =  (int)$default_zoom_global;
                    }
                }


                $max_zoom = 120;
                if (isset($panoscenes["scene-maxzoom"]) && $panoscenes["scene-maxzoom"] != '') {
                    $max_zoom = (int)$panoscenes["scene-maxzoom"];
                } else {
                    if ($max_zoom_global != '') {
                        $max_zoom =  (int)$max_zoom_global;
                    }
                }

                $min_zoom = 120;
                if (isset($panoscenes["scene-minzoom"]) && $panoscenes["scene-minzoom"] != '') {
                    $min_zoom = (int)$panoscenes["scene-minzoom"];
                } else {
                    if ($min_zoom_global != '') {
                        $min_zoom =  (int)$min_zoom_global;
                    }
                }

                $hotspot_datas = array();
                if (isset($panoscenes['hotspot-list'])) {
                    $hotspot_datas = $panoscenes['hotspot-list'];
                }

                $hotspots = array();

                foreach ($hotspot_datas as $hotspot_data) {
                    $status  = get_option('wpvr_edd_license_status');
                    if ($status !== false && $status == 'valid') {

                        if (isset($hotspot_data["hotspot-customclass-pro"]) && $hotspot_data["hotspot-customclass-pro"] != 'none') {
                            $hotspot_data['hotspot-customclass'] = $hotspot_data["hotspot-customclass-pro"] . ' custom-' . $id . '-' . $panoscenes['scene-id'] . '-' . $hotspot_data['hotspot-title'];
                        }
                        if (isset($hotspot_data['hotspot-blink'])) {
                            $hotspotblink = $hotspot_data['hotspot-blink'];
                        }
                    }
                    $hotspot_scene_pitch = '';
                    if (isset($hotspot_data["hotspot-scene-pitch"])) {
                        $hotspot_scene_pitch = $hotspot_data["hotspot-scene-pitch"];
                    }
                    $hotspot_scene_yaw = '';
                    if (isset($hotspot_data["hotspot-scene-yaw"])) {
                        $hotspot_scene_yaw = $hotspot_data["hotspot-scene-yaw"];
                    }

                    $hotspot_type = $hotspot_data["hotspot-type"] !== 'scene' ? 'info' : $hotspot_data["hotspot-type"];
                    $hotspot_content = '';

                    ob_start();
                    do_action('wpvr_hotspot_content', $hotspot_data);
                    $hotspot_content = ob_get_clean();

                    if (!$hotspot_content) {
                        $hotspot_content = $hotspot_data["hotspot-content"];
                    }

                    if (isset($hotspot_data["wpvr_url_open"][0])) {
                        $wpvr_url_open = $hotspot_data["wpvr_url_open"][0];
                    } else {
                        $wpvr_url_open = "off";
                    }
                    $on_hover_content = preg_replace_callback(
                        '/<p>\s*(<img[^>]*>)\s*<br>\s*<\/p>/i',
                        function ($matches) {
                            return $matches[1];
                        },
                        $hotspot_data['hotspot-hover'] ?? ''
                    );
                    $on_hover_content = sanitize_content_preserve_styles($on_hover_content ?? '');
                    $on_click_content = preg_replace_callback('/<img[^>]*>/', "replace_callback", $hotspot_content ?? '');
                    $hotspot_shape = 'round';
                    if (isset($hotspot_data["hotspot-customclass-pro"]) && $hotspot_data["hotspot-customclass-pro"] != 'none') {
                        $hotspot_shape = isset($hotspot_data["hotspot-shape"]) ? $hotspot_data["hotspot-shape"] : 'round';
                    }

                    $hotspot_data_for_on_click=[];
                    if('info' === $hotspot_type && !empty($on_click_content)){
                        $hotspot_data_for_on_click = [
                            'on_click_content' => $on_click_content,
                            'tour_id' => $id,
                            'scene_id' => $panoscenes['scene-id'],
                            'hotspot_id' => $hotspot_data['hotspot-title'],
                        ];
                    } elseif('info' === $hotspot_type && !empty($hotspot_data["hotspot-url"])){
                        $hotspot_data_for_on_click = [
                            'on_click_content' => '',
                            'tour_id' => $id,
                            'scene_id' => $panoscenes['scene-id'],
                            'hotspot_id' => $hotspot_data['hotspot-title'],
                        ];
                    }

                    $hotspot_info = array(
                        'text' => $hotspot_data['hotspot-title'],
                        'pitch' => $hotspot_data['hotspot-pitch'],
                        'yaw' => $hotspot_data['hotspot-yaw'],
                        'type' => $hotspot_type,
                        'cssClass' => $hotspot_data['hotspot-customclass'],
                        'URL' => $hotspot_data['hotspot-url'],
                        "wpvr_url_open" => $wpvr_url_open,
                        "clickHandlerArgs" => $hotspot_data_for_on_click,
                        'createTooltipArgs' => !empty(trim($on_hover_content)) ? trim($on_hover_content) : '',
                        "sceneId" => $hotspot_data["hotspot-scene"],
                        "targetPitch" => (float)$hotspot_scene_pitch,
                        "targetYaw" => (float)$hotspot_scene_yaw,
                        'hotspot_type' => $hotspot_data['hotspot-type'],
                        'hotspot_shape' => $hotspot_shape,
                    );

                    $hotspot_info['URL'] = ($hotspot_data['hotspot-type'] === 'fluent_form' || $hotspot_data['hotspot-type'] === 'wc_product') ? '' : $hotspot_info['URL'];

                    if ($hotspot_data["hotspot-customclass"] == 'none' || $hotspot_data["hotspot-customclass"] == '') {
                        unset($hotspot_info["cssClass"]);
                    }
                    if (empty($hotspot_data["hotspot-scene"])) {
                        unset($hotspot_info['targetPitch']);
                        unset($hotspot_info['targetYaw']);
                    }
                    array_push($hotspots, $hotspot_info);
                }

                $device_scene = $panoscenes['scene-attachment-url'];
                $mobile_media_resize = get_option('mobile_media_resize');
                $file_accessible = ini_get('allow_url_fopen');
                if ($mobile_media_resize == "true" && $device_scene) {
                    if ($file_accessible == "1") {
                        $image_info = getimagesize($device_scene);
                        if ($image_info && $image_info[0] > 4096) {
                            $src_to_id_for_mobile = '';
                            $src_to_id_for_desktop = '';
                            if (wpvr_isMobileDevice()) {
                                $src_to_id_for_mobile = attachment_url_to_postid($panoscenes['scene-attachment-url']);
                                if ($src_to_id_for_mobile) {
                                    $mobile_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'wpvr_mobile');
                                    if ($mobile_scene[3]) {
                                        $device_scene = $mobile_scene[0];
                                    }
                                }
                            } else {
                                $src_to_id_for_desktop = attachment_url_to_postid($panoscenes['scene-attachment-url']);
                                if ($src_to_id_for_desktop) {
                                    $desktop_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'full');
                                    if ($desktop_scene && $desktop_scene[0]) {
                                        $device_scene = $desktop_scene[0];
                                    }
                                }
                            }
                        }
                    }
                }

                $scene_info = array();

                if ($panoscenes["scene-type"] == 'cubemap') {
                    $pano_type = 'cubemap';
                    $pano_attachment = array(
                        $panoscenes["scene-attachment-url-face0"],
                        $panoscenes["scene-attachment-url-face1"],
                        $panoscenes["scene-attachment-url-face2"],
                        $panoscenes["scene-attachment-url-face3"],
                        $panoscenes["scene-attachment-url-face4"],
                        $panoscenes["scene-attachment-url-face5"]
                    );

                    $scene_info = array('type' => $panoscenes['scene-type'], 'cubeMap' => $pano_attachment, "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "authorURL" => $scene_author_url, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, 'hotSpots' => $hotspots);
                } else {
                    $scene_info = array('type' => $panoscenes['scene-type'], 'panorama' => $device_scene, "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "authorURL" => $scene_author_url, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, 'hotSpots' => $hotspots);
                }



                if (isset($panoscenes["ptyscene"])) {
                    if ($panoscenes["ptyscene"] == "off") {
                        unset($scene_info['pitch']);
                        unset($scene_info['yaw']);
                    }
                }

                if (empty($panoscenes["scene-ititle"])) {
                    unset($scene_info['title']);
                }
                if (empty($panoscenes["scene-author"])) {
                    unset($scene_info['author']);
                }
                if (empty($panoscenes["scene-author-url"])) {
                    unset($scene_info['authorURL']);
                }

                if (empty($scene_vaov)) {
                    unset($scene_info['vaov']);
                }

                if (empty($scene_haov)) {
                    unset($scene_info['haov']);
                }

                if (empty($scene_vertical_offset)) {
                    unset($scene_info['vOffset']);
                }

                if (isset($panoscenes["cvgscene"])) {
                    if ($panoscenes["cvgscene"] == "off") {
                        unset($scene_info['maxPitch']);
                        unset($scene_info['minPitch']);
                    }
                }
                if (empty($panoscenes["scene-maxpitch"])) {
                    unset($scene_info['maxPitch']);
                }

                if (empty($panoscenes["scene-minpitch"])) {
                    unset($scene_info['minPitch']);
                }

                if (isset($panoscenes["chgscene"])) {
                    if ($panoscenes["chgscene"] == "off") {
                        unset($scene_info['maxYaw']);
                        unset($scene_info['minYaw']);
                    }
                }
                if (empty($panoscenes["scene-maxyaw"])) {
                    unset($scene_info['maxYaw']);
                }

                if (empty($panoscenes["scene-minyaw"])) {
                    unset($scene_info['minYaw']);
                }

                // if (isset($panoscenes["czscene"])) {
                //     if ($panoscenes["czscene"] == "off") {
                //         unset($scene_info['hfov']);
                //         unset($scene_info['maxHfov']);
                //         unset($scene_info['minHfov']);
                //     }
                // }

                $scene_array = array();
                $scene_array = array(
                    $panoscenes['scene-id'] => $scene_info
                );

                $scene_data[$panoscenes['scene-id']] = $scene_info;
            }
        }
    }
    $pano_id_array = array();
    $pano_id_array = array('panoid' => $panoid);
    $pano_response = array();
    $pano_response = array('autoLoad' => $autoload, 'showControls' => $control, 'compass' => $compass, 'orientationOnByDefault' => $gyro_orientation, 'mouseZoom' => $mouseZoom, 'draggable' => $draggable, 'disableKeyboardCtrl' => $diskeyboard, 'keyboardZoom' => $keyboardzoom, "preview" => $preview, "autoRotate" => $autorotation, "autoRotateInactivityDelay" => $autorotationinactivedelay, "autoRotateStopDelay" => $autorotationstopdelay, 'default' => $default_data, 'scenes' => $scene_data);
    if (empty($autorotation)) {
        unset($pano_response['autoRotate']);
        unset($pano_response['autoRotateInactivityDelay']);
        unset($pano_response['autoRotateStopDelay']);
    }
    if (empty($autorotationinactivedelay)) {
        unset($pano_response['autoRotateInactivityDelay']);
    }
    if (empty($autorotationstopdelay)) {
        unset($pano_response['autoRotateStopDelay']);
    }

    $response = array();
    $response = array($pano_id_array, $pano_response);
    if (!empty($response)) {
        $response = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    if (empty($width)) {
        $width = '600';
    }
    if (empty($height)) {
        $height = '400';
    }
    if ('fullwidth' == $width) {
        $width = "100%";
    }

    $foreground_color = '#fff';
    $pulse_color = wpvr_hex2rgb($hotspoticoncolor);
    $rgb = wpvr_HTMLToRGB($hotspoticoncolor);
    $hsl = wpvr_RGBToHSL($rgb);
    if ($hsl->lightness > 200) {
        $foreground_color = '#000000';
    } else {
        $foreground_color = '#fff';
    }

    $class = 'myclass';
    $html = 'test';
    $html = '';
    $html .= '<style>';
    if ($width == 'embed') {
        $html .= 'body{
             overflow: hidden;
        }';
    }
    $status  = get_option('wpvr_edd_license_status');
    $status  = get_option('wpvr_edd_license_status');
    if ($status !== false && $status == 'valid') {
        if (isset($postdata['customcss_enable']) && $postdata['customcss_enable'] == 'on') {
            $html .= isset($postdata['customcss']) ? $postdata['customcss'] : '';
        }
    }
    if ($status !== false && $status == 'valid') {
        if (is_array($panodata) && isset($panodata['scene-list'])) {
            foreach ($panodata['scene-list'] as $panoscenes) {

                foreach ($panoscenes['hotspot-list'] as $hotspot) {
                    if (isset($hotspot['hotspot-customclass-color-icon-value']) && !empty($hotspot['hotspot-customclass-color-icon-value'])) {
                        $hotspoticoncolor = $hotspot['hotspot-customclass-color-icon-value'];
                    } else {
                        $hotspoticoncolor = "#00b4ff";
                    }
                    $hotspot_border = '';
                    if(isset($hotspot['hotspot-border']) && $hotspot['hotspot-border'] == 'on'){
                        $border_width = $hotspot['hotspot-border-width'];
                        $border_color = $hotspot['hotspot-border-color'];
                        $border_style = $hotspot['hotspot-border-style'];
                    
                        $hotspot_border = 'border: '.$border_width.'px '.$border_style.' '.$border_color.';';
                    }
                    $hotspot_background_color = 'background-color: ' . $hotspoticoncolor . ';';
                    $hotspot_animation = ' animation: icon-pulse' . $panoid . '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' 1.5s infinite cubic-bezier(.25, 0, 0, 1);
                              '. $hotspot_border.'';


                    $pulse_color = wpvr_hex2rgb($hotspoticoncolor);
                    if (isset($hotspot["hotspot-customclass-pro"]) && $hotspot["hotspot-customclass-pro"] != 'none') {
                        $border_radius = ' border-radius: 100%;';
                        $hotspot_shape = isset($hotspot["hotspot-shape"]) ? $hotspot["hotspot-shape"] : 'round';
                        if($hotspot_shape === 'square'){
                            $border_radius = '';
                        }
                        if ($hotspot_shape === 'hexagon') {
                            $border_radius = '';
                            $hotspot_background_color = 'background-color: transparent;';
                            $hotspot_animation = '';
                        }

                        if(isset($hotspot['hotspot-custom-icon-color-value']) && !empty($hotspot['hotspot-custom-icon-color-value'])){
                            $foreground_color = $hotspot['hotspot-custom-icon-color-value'];
                        }
                        $html .= '#' . esc_attr( $panoid ). ' div.pnlm-hotspot-base.fas.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                          #' . esc_attr( $panoid ). ' div.pnlm-hotspot-base.fab.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                          #' . esc_attr( $panoid ). ' div.pnlm-hotspot-base.fa.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                          #' . esc_attr( $panoid ). ' div.pnlm-hotspot-base.fa-solid.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                          #' . esc_attr( $panoid ). ' div.pnlm-hotspot-base.far.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' {
                              display: block !important;
                             '.$hotspot_background_color.'
                              color: ' . $foreground_color . ';
                              '.$border_radius.'
                              width: 30px;
                              height: 30px;
                              font-size: 16px;
                              line-height: 30px;
                             '.$hotspot_animation.'
                        }';
                        if($hotspot_shape === 'hexagon'){

                            $html .= '#' . esc_attr( $panoid ) . ' .custom-' . esc_attr( $id ) . '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' .hexagon-wrapper svg path {
                                fill: ' . $hotspoticoncolor . ';
                             }';

                            $html .= '#' . esc_attr( $panoid ) . ' .custom-' . esc_attr( $id ) . '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . '.pnlm-tooltip:after{
                                content: "";
                                position: absolute;
                                left: 50%;
                                top: 50%;
                                transform: translate(-50%, -50%);
                                width: 85%;
                                height: 85%;
                                animation: icon-pulse' . esc_attr( $panoid ) . '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' 1.5s infinite cubic-bezier(.25, 0, 0, 1);
                                border-radius: 100%;
                                z-index: -2;
                             }';

                        }
                        $html .= '#' . esc_attr( $panoid2 ). ' div.pnlm-hotspot-base.fas.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                              #' . esc_attr( $panoid2 ). ' div.pnlm-hotspot-base.fab.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                              #' . esc_attr( $panoid2 ). ' div.pnlm-hotspot-base.fa-solid.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                              #' . esc_attr( $panoid2) . ' div.pnlm-hotspot-base.fa.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ',
                              #' . esc_attr( $panoid2 ). ' div.pnlm-hotspot-base.far.custom-' . esc_attr( $id ). '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' {
                              display: block !important;
                             '.esc_attr( $hotspot_background_color).'
                              color: ' . esc_attr( $foreground_color) . ';
                              '.esc_attr( $border_radius).'
                              width: 30px;
                              height: 30px;
                              font-size: 16px;
                              line-height: 30px;
                              '.esc_attr( $hotspot_animation).'
                      }';
                    }
                    if (isset($hotspot['hotspot-blink'])) {
                        $hotspotblink = $hotspot['hotspot-blink'];
                        if ($hotspotblink == 'on') {
                            $html .= '@-webkit-keyframes icon-pulse' . esc_attr( $panoid) . '-' . $panoscenes['scene-id'] . '-' . $hotspot['hotspot-title'] . ' {
                0% {
                    box-shadow: 0 0 0 0px rgba(' . esc_attr( $pulse_color[0]) . ', 1);
                }
                100% {
                    box-shadow: 0 0 0 10px rgba(' . esc_attr( $pulse_color[0]) . ', 0);
                }
            }
            @keyframes icon-pulse' . esc_attr( $panoid) . ' {
                0% {
                    box-shadow: 0 0 0 0px rgba(' . esc_attr( $pulse_color[0]) . ', 1);)
                }
                100% {
                    box-shadow: 0 0 0 10px rgba(' . esc_attr( $pulse_color[0] ). ', 0);
                }
            }';
                        }
                    }
                }
            }
        }
    }

    $status  = get_option('wpvr_edd_license_status');
    if ($status !== false && $status == 'valid') {
        if (!$gyro) {
            $html .= '#' . esc_attr( $panoid ). ' div.pnlm-orientation-button {
                    display: none;
                }';
        }
    } else {
        $html .= '#' . esc_attr( $panoid ). ' div.pnlm-orientation-button {
                    display: none;
                }';
    }
    $floor_plan_custom_color = isset($postdata['floor_plan_custom_color']) ? $postdata['floor_plan_custom_color'] : '#cca92c';
    $foreground_color_pointer = '#fff';
    if ($floor_plan_custom_color != '') {
        $pointer_pulse = wpvr_hex2rgb($floor_plan_custom_color);
        $floor_rgb = wpvr_HTMLToRGB($floor_plan_custom_color);
        $floor_hsl = wpvr_RGBToHSL($floor_rgb);
        if ($floor_hsl->lightness > 200) {
            $foreground_color_pointer = '#000000';
        }
        $html .= '
            .wpvr-floor-map .floor-plan-pointer.add-pulse:before {
                border: 17px solid ' . esc_attr( $floor_plan_custom_color ) . ';
            }
            @-webkit-keyframes pulse {
                0% {
                    -webkit-box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0.7);
                }
                70% {
                    -webkit-box-shadow: 0 0 0 10px rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                }
                100% {
                    -webkit-box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                }
            }
            @keyframes pulse {
                0% {
                    -moz-box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0.7);
                    box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0.7);
                }
                70% {
                    -moz-box-shadow: 0 0 0 10px rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                    box-shadow: 0 0 0 10px rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                }
                100% {
                    -moz-box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                    box-shadow: 0 0 0 0 rgba(' . esc_attr( $pointer_pulse[0] ) . ', 0);
                }
            }';
    }
    $html .= '</style>';

    $scene_animation = isset($postdata['sceneAnimation']) ? $postdata['sceneAnimation'] : 'off';

    if( $scene_animation === 'on'){
        $animation_type = isset($postdata['sceneAnimationName']) ? $postdata['sceneAnimationName'] : 'none';
        $animationDuration = isset($postdata['sceneAnimationTransitionDuration']) ? $postdata['sceneAnimationTransitionDuration'] : '500ms';
        $animationDelay = isset($postdata['sceneAnimationTransitionDelay']) ? $postdata['sceneAnimationTransitionDelay'] : '0ms';
        $animation_css = apply_filters('wpvr_tour_scene_animation',$animation_type,$animationDuration, $animationDelay, $postdata,$id);
        $html .= $animation_css;
    }

    if (wpvr_isMobileDevice()) {
        $html .= '<div id="master-container" class="wpvr-cardboard ' . esc_attr( $className ) . ' ' . esc_attr( $enable_cardboard ) . ' " style="max-width:' . esc_attr( $width ) . esc_attr( $width_unit ) . '; width: 100%; height: ' . esc_attr( $mobile_height ) . esc_attr( $mobile_height_unit ) . '; border-radius:' . esc_attr( $radius ) . '; direction:ltr; border : ' . esc_attr( $border_style ) . ' ">';
    } else {
        $html .= '<div id="master-container" class="wpvr-cardboard ' . esc_attr( $className ) . ' ' . esc_attr( $enable_cardboard ) . '" style="max-width:' . esc_attr( $width ) . esc_attr( $width_unit ) . '; width: 100%; height: ' . esc_attr( $height ) . esc_attr( $height_unit ) . '; border-radius:' . esc_attr( $radius ) . '; direction:ltr; border : ' . esc_attr( $border_style ) . '">';
    }
    $status  = get_option('wpvr_edd_license_status');
    $is_cardboard = get_option('wpvr_cardboard_disable');
    if ($status !== false &&  'valid' == $status  && $is_pro &&  wpvr_isMobileDevice() && $is_cardboard == 'true') {
        $html .= '<button class="fullscreen-button">';
        $html .= '<span class="expand">';
        $html .= '<i class="fa fa-expand" aria-hidden="true"></i>';
        $html .= '</span>';

        $html .= '<span class="compress">';
        $html .= '<i class="fa fa-compress" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '</button>';
        $html .= '<label class="wpvr-cardboard-switcher">
                <input type="checkbox" class="vr_mode_change' . $id . '" name="vr_mode_change" value="off">
                <span class="switcher-box">
                    <span class="normal-mode-tooltip">Normal VR Mode</span>
                    <svg width="78" height="60" viewBox="0 0 78 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42.25 21.4286C42.25 22.2811 41.9076 23.0986 41.2981 23.7014C40.6886 24.3042 39.862 24.6429 39 24.6429C38.138 24.6429 37.3114 24.3042 36.7019 23.7014C36.0924 23.0986 35.75 22.2811 35.75 21.4286C35.75 20.5761 36.0924 19.7585 36.7019 19.1557C37.3114 18.5529 38.138 18.2143 39 18.2143C39.862 18.2143 40.6886 18.5529 41.2981 19.1557C41.9076 19.7585 42.25 20.5761 42.25 21.4286ZM19.5 30C18.9254 30 18.3743 30.2258 17.9679 30.6276C17.5616 31.0295 17.3333 31.5745 17.3333 32.1429C17.3333 32.7112 17.5616 33.2562 17.9679 33.6581C18.3743 34.06 18.9254 34.2857 19.5 34.2857H28.1667C28.7413 34.2857 29.2924 34.06 29.6987 33.6581C30.1051 33.2562 30.3333 32.7112 30.3333 32.1429C30.3333 31.5745 30.1051 31.0295 29.6987 30.6276C29.2924 30.2258 28.7413 30 28.1667 30H19.5ZM47.6667 32.1429C47.6667 31.5745 47.8949 31.0295 48.3013 30.6276C48.7076 30.2258 49.2587 30 49.8333 30H58.5C59.0746 30 59.6257 30.2258 60.0321 30.6276C60.4384 31.0295 60.6667 31.5745 60.6667 32.1429C60.6667 32.7112 60.4384 33.2562 60.0321 33.6581C59.6257 34.06 59.0746 34.2857 58.5 34.2857H49.8333C49.2587 34.2857 48.7076 34.06 48.3013 33.6581C47.8949 33.2562 47.6667 32.7112 47.6667 32.1429ZM32.5 0C31.9254 0 31.3743 0.225765 30.9679 0.627629C30.5616 1.02949 30.3333 1.57454 30.3333 2.14286V8.57143H18.4167C14.8693 8.57183 11.4528 9.89617 8.84994 12.2798C6.24706 14.6634 4.64954 17.9306 4.37667 21.4286H2.16667C1.59203 21.4286 1.04093 21.6543 0.634602 22.0562C0.228273 22.4581 0 23.0031 0 23.5714V36.4286C0 36.9969 0.228273 37.5419 0.634602 37.9438C1.04093 38.3457 1.59203 38.5714 2.16667 38.5714H4.33333V46.0714C4.33333 49.7655 5.81711 53.3083 8.45825 55.9204C11.0994 58.5325 14.6815 60 18.4167 60H25.3933C29.1269 59.9986 32.7071 58.5311 35.347 55.92L37.921 53.3786C38.0618 53.2393 38.229 53.1288 38.4131 53.0534C38.5971 52.978 38.7943 52.9392 38.9935 52.9392C39.1927 52.9392 39.3899 52.978 39.5739 53.0534C39.758 53.1288 39.9252 53.2393 40.066 53.3786L42.6357 55.92C45.2766 58.5322 48.8586 59.9998 52.5937 60H59.5833C63.3185 60 66.9006 58.5325 69.5418 55.9204C72.1829 53.3083 73.6667 49.7655 73.6667 46.0714V38.5714H75.8333C76.408 38.5714 76.9591 38.3457 77.3654 37.9438C77.7717 37.5419 78 36.9969 78 36.4286V23.5714C78 23.0031 77.7717 22.4581 77.3654 22.0562C76.9591 21.6543 76.408 21.4286 75.8333 21.4286H73.6233C73.3505 17.9306 71.753 14.6634 69.1501 12.2798C66.5472 9.89617 63.1307 8.57183 59.5833 8.57143H47.6667V2.14286C47.6667 1.57454 47.4384 1.02949 47.0321 0.627629C46.6257 0.225765 46.0746 0 45.5 0H32.5ZM69.3333 22.5V46.0714C69.3333 48.6289 68.3061 51.0816 66.4776 52.89C64.6491 54.6983 62.1692 55.7143 59.5833 55.7143H52.5937C50.0093 55.7132 47.5311 54.6973 45.7037 52.89L43.1297 50.3486C42.5864 49.8108 41.9413 49.3842 41.2312 49.0931C40.5211 48.8021 39.76 48.6522 38.9913 48.6522C38.2227 48.6522 37.4616 48.8021 36.7515 49.0931C36.0414 49.3842 35.3963 49.8108 34.853 50.3486L32.2833 52.89C30.4559 54.6973 27.9777 55.7132 25.3933 55.7143H18.4167C15.8308 55.7143 13.3509 54.6983 11.5224 52.89C9.6939 51.0816 8.66667 48.6289 8.66667 46.0714V22.5C8.66667 19.9426 9.6939 17.4899 11.5224 15.6815C13.3509 13.8731 15.8308 12.8571 18.4167 12.8571H59.5833C62.1692 12.8571 64.6491 13.8731 66.4776 15.6815C68.3061 17.4899 69.3333 19.9426 69.3333 22.5Z" fill="#216DF0"/>
                    </svg>
                </span>
            </label>';
    }

    if ($width == 'fullwidth') {
        if (wpvr_isMobileDevice()) {
            $html .= '<div class="cardboard-vrfullwidth vrfullwidth">';
            $html .= '<div id="pano2' . esc_attr( $id ) . '" class="pano-wrap  pano-left cardboard-half" style="width: 49%!important; border-radius:' . esc_attr( $radius  ). ' ;text-align:center; direction:ltr;" ><div id="center-pointer2' . esc_attr( $id ). '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
            $html .= '<div id="pano' . esc_attr( $id ) . '" class="pano-wrap  pano-right" style="width: 100%; text-align:center; direction:ltr; border-radius:' . esc_attr( $radius ) . '" >';
        } else {
            $html .= '<div id="pano2' . esc_attr( $id ) . '" class="pano-wrap pano-left" style="width: 49%; border-radius:' . esc_attr( $radius ) . ';"><div id="center-pointer2' . esc_attr( $id  ). '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
            $html .= '<div id="pano' . esc_attr( $id ) . '" class="pano-wrap vrfullwidth" style=" text-align:center; height: ' . esc_attr( $height ) . esc_attr( $height_unit ) . '; border-radius:' . esc_attr( $radius ) . '; direction:ltr;" >';
        }
    } else {
        if (wpvr_isMobileDevice()) {
            $html .= '<div id="pano2' . esc_attr( $id ) . '" class="pano-wrap pano-left cardboard-half" style="width: 49%; border-radius:' . esc_attr( $radius ) . ';">
                        <div id="center-pointer2' . esc_attr( $id ) . '" class="vr-pointer-container">
                            <span class="center-pointer"></span>
                        </div>
                       </div>';
            $html .= '<div id="pano' . esc_attr( $id ) . '" class="pano-wrap pano-right" style=" width: 100%; border-radius:' . esc_attr( $radius ) . ';">';
        } else {

            $html .= '<div id="pano2' . esc_attr( $id ) . '" class="pano-wrap pano-left" style="width: 49%; border-radius:' . esc_attr( $radius ) . ';"><div id="center-pointer2' . esc_attr( $id ) . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';

            $html .= '<div id="pano' . esc_attr( $id ) . '" class="pano-wrap pano-right" style="width: 100%; border-radius:' . esc_attr( $radius ) . ';">';
        }
    }
    // Vr mode transction scene to scene
    if ($status !== false &&  'valid' == $status  && $is_pro &&  wpvr_isMobileDevice() && $is_cardboard == 'true') {
        $html .= '<div id="center-pointer' . esc_attr( $id ) . '" class="vr-pointer-container" style="display:none"><span class="center-pointer"></span></div>';
    }


    //===company logo===//
    if (isset($postdata['cpLogoSwitch'])) {
        $cpLogoImg = $postdata['cpLogoImg'];
        $cpLogoContent = $postdata['cpLogoContent'];
        if ($postdata['cpLogoSwitch'] == 'on') {
            $html .= '<div id="cp-logo-controls">';
            $html .= '<div class="cp-logo-ctrl" id="cp-logo">';
            if ($cpLogoImg) {
                $html .= '<img loading="lazy" src="' . esc_attr( $cpLogoImg ) . '" alt="Company Logo">';
            }

            if ($cpLogoContent) {
                $html .= '<div class="cp-info">' . esc_attr( $cpLogoContent ) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
    }
    //===company logo ends===//

    //===Background Tour===//
    if (isset($postdata['bg_tour_enabler'])) {

        $bg_tour_enabler = $postdata['bg_tour_enabler'];
        if ($bg_tour_enabler == 'on') {
            $bg_tour_navmenu = $postdata['bg_tour_navmenu'] ?? 'off';
            $bg_tour_title = $postdata['bg_tour_title'] ?? '';
            $bg_tour_subtitle = $postdata['bg_tour_subtitle'] ?? '';

            if ($bg_tour_navmenu == 'on') {
                $menuLocations = get_nav_menu_locations();
                $menuID = $menuLocations['primary'];
                $primaryNav = wp_get_nav_menu_items($menuID);

                if ($primaryNav) {
                    $html .= '<div class="wpvr-navbar-container">';
                    foreach ($primaryNav as $primaryNav_key => $primaryNav_value) {
                        if ($primaryNav_value->menu_item_parent == "0") {
                            $html .= '<li>';
                            $html .= '<a href="' . esc_url( $primaryNav_value->url ) . '">' . esc_attr( $primaryNav_value->title ) . '</a>';
                            $html .= '<ul class="wpvr-navbar-dropdown">';
                            foreach ($primaryNav as $pm_key => $pm_value) {
                                if ($pm_value->menu_item_parent == $primaryNav_value->ID) {
                                    $html .= '<li>';
                                    $html .= '<a href="' . esc_url( $pm_value->url ) . '">' . esc_attr( $pm_value->title ) . '</a>';
                                    $html .= '</li>';
                                }
                            }
                            $html .= '</ul>';
                            $html .= '</li>';
                        }
                    }
                    $html .= '</div>';
                }
            }

            $html .= '<div class="wpvr-home-content">';
            $html .= '<div class="wpvr-home-title">' . esc_attr( $bg_tour_title ) . '</div>';
            $html .= '<div class="wpvr-home-subtitle">' . esc_attr( $bg_tour_subtitle ) . '</div>';
            $html .= '</div>';
        }
    }
    //===Background Tour End===//

    //===Custom Control===//
    if (isset($custom_control)) {
        if ($custom_control['panZoomInSwitch'] == "on" || $custom_control['panZoomOutSwitch'] == "on" || $custom_control['gyroscopeSwitch'] == "on" || $custom_control['backToHomeSwitch'] == "on") {
            $html .= '<div id="zoom-in-out-controls' . esc_attr( $id ) . '" class="zoom-in-out-controls">';

            if ($custom_control['backToHomeSwitch'] == "on") {
                $html .= '<div class="ctrl" id="backToHome' . esc_attr( $id ) . '"><i class="' . $custom_control['backToHomeIcon'] . '" style="color:' . esc_attr( $custom_control['backToHomeColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panZoomInSwitch'] == "on") {
                $html .= '<div class="ctrl" id="zoom-in' . esc_attr( $id ) . '"><i class="' . $custom_control['panZoomInIcon'] . '" style="color:' . esc_attr( $custom_control['panZoomInColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panZoomOutSwitch'] == "on") {
                $html .= '<div class="ctrl" id="zoom-out' . esc_attr( $id ) . '"><i class="' . $custom_control['panZoomOutIcon'] . '" style="color:' . esc_attr( $custom_control['panZoomOutColor'] ) . ';"></i></div>';
            }

            if ($custom_control['gyroscopeSwitch'] == "on") {
                $html .= '<div class="ctrl" id="gyroscope' . esc_attr( $id ) . '"><i class="' . $custom_control['gyroscopeIcon'] . '" style="color:' . esc_attr( $custom_control['gyroscopeColor'] ) . ';"></i></div>';
            }

            $html .= '</div>';
        }
        //===zoom in out Control===//

        if ($custom_control['panupSwitch'] == "on" || $custom_control['panDownSwitch'] == "on" || $custom_control['panLeftSwitch'] == "on" || $custom_control['panRightSwitch'] == "on" || $custom_control['panFullscreenSwitch'] == "on") {

            //===Custom Control===//
            $html .= '<div class="controls" id="controls' . esc_attr( $id ) . '">';

            if ($custom_control['panupSwitch'] == "on") {
                $html .= '<div class="ctrl pan-up" id="pan-up' . esc_attr( $id ) . '"><i class="' . $custom_control['panupIcon'] . '" style="color:' . esc_attr( $custom_control['panupColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panDownSwitch'] == "on") {
                $html .= '<div class="ctrl pan-down" id="pan-down' . esc_attr( $id ) . '"><i class="' . $custom_control['panDownIcon'] . '" style="color:' . esc_attr( $custom_control['panDownColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panLeftSwitch'] == "on") {
                $html .= '<div class="ctrl pan-left" id="pan-left' . esc_attr( $id ) . '"><i class="' . $custom_control['panLeftIcon'] . '" style="color:' . esc_attr( $custom_control['panLeftColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panRightSwitch'] == "on") {
                $html .= '<div class="ctrl pan-right" id="pan-right' . esc_attr( $id ) . '"><i class="' . $custom_control['panRightIcon'] . '" style="color:' . esc_attr( $custom_control['panRightColor'] ) . ';"></i></div>';
            }

            if ($custom_control['panFullscreenSwitch'] == "on") {
                $html .= '<div class="ctrl fullscreen" id="fullscreen' . esc_attr( $id ) . '"><i class="' . $custom_control['panFullscreenIcon'] . '" style="color:' . esc_attr( $custom_control['panFullscreenColor'] ) . ';"></i></div>';
            }
            $html .= '</div>';
        }

        //===explainer button===//
        $explainerControlSwitch = '';
        if (isset($custom_control['explainerSwitch'])) {
            $explainerControlSwitch = $custom_control['explainerSwitch'];
        }
        if ($custom_control['explainerSwitch'] == "on") {
            $explainer_style = empty($postdata['explainerContent']) || ( isset( $postdata['explainerSwitch'] ) && 'off' === $postdata['explainerSwitch'] )? 'pointer-events: none; opacity: 0.5;' : '';
            $html .= '<div class="explainer_button" id="explainer_button_' . esc_attr( $id ) . '" style="right:' . esc_attr( $explainer_right ) . '; ' . esc_attr( $explainer_style ) . '">';
            $html .= '<div class="ctrl" id="explainer_target_' . esc_attr( $id ) . '"><i class="' . $custom_control['explainerIcon'] . '" style="color:' . esc_attr( $custom_control['explainerColor'] ) . ';"></i></div>';
            $html .= '</div>';
        }

        //===explainer button===//

    }
    //===Custom Control===//

    //===Scene navigation Control===//
    if (isset($postdata['scene_navigation']) && $postdata['scene_navigation'] === 'on') {
        $html .= '<style>
                #et-boc .et-l .pnlm-controls-container, 
                .pnlm-controls-container{
                    top: 33px;
                }
                
                #et-boc .et-l .zoom-in-out-controls, 
                .zoom-in-out-controls {
                    top: 37px;
                }
            </style>';
        $html .= '<div id="custom-scene-navigation' . esc_attr( $id ) . '" class="custom-scene-navigation">
                <span class="hamburger-menu"><svg width="16" height="10" fill="none" viewBox="0 0 22 15" xmlns="http://www.w3.org/2000/svg"><rect width="21.177" height="2.647" fill="#f7fffb" rx="1.324"/><rect width="21.177" height="2.647" y="6.177" fill="#f7fffb" rx="1.324"/><rect width="21.177" height="2.647" y="12.352" fill="#f7fffb" rx="1.324"/></svg></span> 
              </div>
              
              <div id="custom-scene-navigation-nav' . esc_attr( $id ) . '" class="custom-scene-navigation-nav">
                  <ul></ul>
              </div> 
              ';
    }

    //===Scene navigation  Control===//

    /**
     * Generic Form Handler
     * Renders a generic form with modal functionality if enabled in post data
     * 
     * @param array $postdata Array containing form configuration data
     * @param string $id Unique identifier for the form instance
     * @return string $html Generated HTML content
     */

    // Check if generic form is enabled and validate the setting
    if (isset($postdata["genericform"]) && $postdata["genericform"] === 'on') {

        // $shortcode_val = isset($postdata["genericformshortcode"]) && $postdata["genericformshortcode"] !== "" ? do_shortcode($postdata["genericformshortcode"]) : "No shortcode found or shortcode is empty";

        // Process shortcode with fallback handling
        $shortcode_content = '';
        if (isset($postdata["genericformshortcode"]) && !empty(trim($postdata["genericformshortcode"]))) {
            $shortcode_content = do_shortcode($postdata["genericformshortcode"]);
        } else {
            $shortcode_content = '<p class="error-message">No shortcode found.</p>';
        }

        // Generate the form trigger button
        $html .= '<div class="generic_form_button" id="generic_form_button_' . esc_attr( $id ) . '">';
        $html .= '<div class="generic-form-icon" title ="Generic Form" id="generic_form_target_' . esc_attr( $id ) . '"><i class="fab fa-wpforms" style="color:#f7fffb;"></i></div>';
        $html .= '</div>';

        // Generate the modal form container
        $html .= '<div class="wpvr-generic-form" id="wpvr-generic-form' . esc_attr( $id ) . '" style="display: none">';
        $html .= '<span class="close-generic-form"><i class="fa fa-times"></i></span>';
        $html .= '<div class="generic-form-container">' . $shortcode_content . '</div>';
        $html .= '</div>';
    }
    //=====custom generic form=====//

    //===Floor map button===//
    $status  = get_option('wpvr_edd_license_status');
    if ($status !== false &&  'valid' == $status  && $is_pro) {
        if ($floor_plan_enable == "on" && !empty($floor_plan_image)) {
            $html .= '<div class="floor_map_button" id="floor_map_button_' . esc_attr( $id ) . '" style="right:' . esc_attr( $floor_map_right ) . '">';
            $html .= '<div class="ctrl" id="floor_map_target_' . esc_attr( $id ) . '"><i class="fas fa-map" style="color:#f7fffb;"></i></div>';
            $html .= '</div>';
        }
    }

    //===floor map button===//
    if ($vrgallery) {
        //===Carousal setup===//
        $size = '';
        if ($vrgallery_icon_size) {
            $size = 'vrg-icon-size-large';
        }
        $html .= '<div id="vrgcontrols' . esc_attr( $id ) . '" class="vrgcontrols">';

        $html .= '<div class="vrgctrl' . esc_attr( $id ) . ' vrbounce ' . esc_attr( $size ) . '">';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div id="sccontrols' . esc_attr( $id ) . '" class="scene-gallery vrowl-carousel owl-theme">';
        if (isset($panodata["scene-list"])) {
            foreach ($panodata["scene-list"] as $panoscenes) {
                $scene_key = $panoscenes['scene-id'];
                if ($vrgallery_title == 'on') {
                    $scene_key_title = $panoscenes['scene-ititle'];
                    // $scene_key_title = $panoscenes['scene-id'];
                } else {
                    $scene_key_title = "";
                }

                if ($panoscenes['scene-type'] == 'cubemap') {
                    $img_src_url = $panoscenes['scene-attachment-url-face0'];
                } else {
                    $img_src_url = $panoscenes['scene-attachment-url'];
                }


                $src_to_id = attachment_url_to_postid($img_src_url);
                $thumbnail_array = wp_get_attachment_image_src($src_to_id, 'thumbnail');
                if ($thumbnail_array) {
                    $thumbnail = $thumbnail_array[0];
                } else {
                    $thumbnail = $img_src_url;
                }
                if( isset($postdata['tourLayout']['layout']) && 'layout1' !== $postdata['tourLayout']['layout']) {
                    $html .= '<ul><li title="Double click to view scene"><span class="scene-title">' . $scene_key_title . '</span><img loading="lazy" class="scctrl" id="' . $scene_key . '_gallery_' . $id . '" src="' . $thumbnail . '"></li></ul>';
                }else {
                    $html .= '<ul><li title="Double click to view scene"><span class="scene-title">' . $scene_key_title . '</span><img loading="lazy" class="scctrl" id="' . $scene_key . '_gallery_' . $id . '" src="' . $thumbnail . '"></li></ul>';
                }
            }
        }
        $html .= '</div>';
        $html .= '
        <div class="owl-nav wpvr_slider_nav">
        <button type="button" role="presentation" class="owl-prev wpvr_owl_prev">
            <div class="nav-btn prev-slide"><i class="fa fa-angle-left"></i></div>
        </button>
        <button type="button" role="presentation" class="owl-next wpvr_owl_next">
            <div class="nav-btn next-slide"><i class="fa fa-angle-right"></i></div>
        </button>
        </div>
        ';
        //===Carousal setup end===//
    }

    $bg_music           = isset($postdata['bg_music']) ? $postdata['bg_music'] : 'off';
    $bg_music_url       = isset($postdata['bg_music_url']) ? $postdata['bg_music_url'] : '';
    $autoplay_bg_music  = isset($postdata['autoplay_bg_music']) ? $postdata['autoplay_bg_music'] : 'off';
    $loop_bg_music      = isset($postdata['loop_bg_music']) ? $postdata['loop_bg_music'] : 'off';

    $bg_loop = ($loop_bg_music === 'on') ? 'loop' : '';
    $autoplay_attr = ($autoplay_bg_music === 'on') ? 'autoplay' : '';
    $audio_muted_attr = ($autoplay_bg_music === 'on') ? 'muted' : '';
    $audio_icon_class = 'fa-volume-mute'; // Always start with mute icon

    if ($bg_music === 'on') {
        $html .= '<div id="adcontrol' . esc_attr( $id ) . '" class="adcontrol" style="right:' . esc_attr( $audio_right ) . '">';
        $html .= '<audio id="vrAudio' . esc_attr($id) . '" class="vrAudioDefault" data-autoplay="' . esc_attr($autoplay_bg_music) . '" onended="audionEnd' . esc_attr($id) . '()" ' . $autoplay_attr . ' ' . $audio_muted_attr . ' ' . $bg_loop . '>
                        <source src="' . esc_url($bg_music_url) . '" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>';
        $html .= '<button onclick="playPause' . esc_attr($id) . '()" class="ctrl audio_control" id="audio_control' . esc_attr($id) . '">
                        <i id="vr-volume' . esc_attr($id) . '" class="wpvrvolumeicon' . esc_attr($id) . ' fas ' . esc_attr($audio_icon_class) . '" style="color:#fff;"></i>
                    </button>';
        $html .= '</div>';
    }

    //===Explainer video section===//
    $explainerContent = "";
    if (isset($postdata['explainerContent'])) {
        $explainerContent = $postdata['explainerContent'];
    }
    $html .= '<div class="explainer" id="explainer' . esc_attr( $id ) . '" style="display: none">';
    $html .= '<span class="close-explainer-video"><i class="fa fa-times"></i></span>';
    $html .= '' . $explainerContent . '';
    $html .= '</div>';
    //===Explainer video section End===//

    //===Floor plan section===//
    $floor_map_image = "";
    $floor_map_pointer = array();
    $floor_map_scene_id = '';
    $floor_plan_custom_color = '#cca92c';

    if (isset($postdata['floor_plan_attachment_url'])) {
        $floor_map_image = $postdata['floor_plan_attachment_url'];
        $floor_map_pointer = $postdata['floor_plan_pointer_position'];
        $floor_map_scene_id = $postdata['floor_plan_data_list'];
        $floor_plan_custom_color = $postdata['floor_plan_custom_color'];
    }
    $html .= '<div class="wpvr-floor-map" id="wpvr-floor-map' . $id . '" style="display: none">';
    $html .= '<span class="close-floor-map-plan"><i class="fa fa-times"></i></span>';
    $html .= '<img loading="lazy" src="' . $floor_map_image . '">';

    foreach ($floor_map_pointer as $key => $pointer_position) {
        $html .= '<div class="floor-plan-pointer ui-draggable ui-draggable-handle" scene_id = "' . esc_attr( $floor_map_scene_id[$key]->value ) . '" id="' . esc_attr( $pointer_position->id ) . '" data-top="' . esc_attr( $pointer_position->data_top ) . '" data-left="' . esc_attr( $pointer_position->data_left ) . '" style="' . esc_attr( $pointer_position->style ) . '">                        
                                    
                                    <svg class="floor-pointer-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="11.5" stroke="' . esc_attr( $floor_plan_custom_color ) . '"/>
                                        <circle cx="12" cy="12" r="5" fill="' . esc_attr( $foreground_color_pointer ) . '"/>
                                    </svg>
                                    <svg class="floor-pointer-flash" width="54" height="35" viewBox="0 0 54 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0.454054 1.32433L11.7683 34.3243C11.9069 34.7285 12.287 35 12.7143 35H41.2857C41.713 35 42.0931 34.7285 42.2317 34.3243L53.5459 1.32432C53.7685 0.675257 53.2862 0 52.6 0H1.4C0.713843 0 0.231517 0.675258 0.454054 1.32433Z" fill="url(#paint0_linear_1_10)"/>
                                        <defs>
                                        <linearGradient id="paint0_linear_1_10" x1="27" y1="4.59807e-08" x2="26.5" y2="28" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="' . esc_attr( $floor_plan_custom_color ) . '" stop-opacity="0"/>
                                        <stop offset="1" stop-color="' . esc_attr( $floor_plan_custom_color ) . '"/>
                                        </linearGradient>
                                        </defs>
                                    </svg>
    
                                </div>';
    }
    $html .= '</div>';
    //===Floor plan section===//

    $html .= '<div class="wpvr-hotspot-tweak-contents-wrapper" style="display: none">';
    $html .= '<i class="fa fa-times cross" data-id="' . esc_attr( $id ) . '"></i>';
    $html .= '<div class="wpvr-hotspot-tweak-contents-flex">';
    $html .= '<div class="wpvr-hotspot-tweak-contents">';
    ob_start();
    do_action('wpvr_hotspot_tweak_contents', $scene_data);
    $hotspot_content = ob_get_clean();
    $html .= $hotspot_content;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="custom-ifram-wrapper" style="display: none;">';
    $html .= '<i class="fa fa-times cross" data-id="' . esc_attr( $id ) . '"></i>';
    $html .= '<div class="custom-ifram-flex">';
    $html .= '<div class="custom-ifram">';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';


    if ("fullwidth" == $width) {
        $html .= '</div>';
    }
    if ($status !== false &&  'valid' == $status  && $is_pro) {
        $call_to_action = isset($postdata['calltoaction']) ? $postdata['calltoaction'] : 'off';
        if ('on' == $call_to_action) {
            $buttontext = isset($postdata['buttontext']) ? $postdata['buttontext'] : '';
            $buttonurl = isset($postdata['buttonurl']) ? $postdata['buttonurl'] : '';
            $cta_btn_style = isset($postdata['button_configuration']) ? $postdata['button_configuration'] : array();

            $button_open_new_tab = isset($cta_btn_style['button_open_new_tab']) ? $cta_btn_style['button_open_new_tab'] : "off";
            $target = '_self';
            $button_position = isset($cta_btn_style['button_position']) ? $cta_btn_style['button_position'] : "";
            $background_color = isset($cta_btn_style['button_background_color']) ? $cta_btn_style['button_background_color'] : "";
            $color = isset($cta_btn_style['button_font_color']) ? $cta_btn_style['button_font_color'] : "";
            $font_size = isset($cta_btn_style['button_font_size']) ? $cta_btn_style['button_font_size'] : "";
            $font_weight = isset($cta_btn_style['button_font_weight']) ? $cta_btn_style['button_font_weight'] : "";
            $text_align = isset($cta_btn_style['button_alignment']) ? $cta_btn_style['button_alignment'] : "";
            $text_transform = isset($cta_btn_style['button_transform']) ? $cta_btn_style['button_transform'] : "";
            $font_style = isset($cta_btn_style['button_text_style']) ? $cta_btn_style['button_text_style'] : "";
            $text_decoration = isset($cta_btn_style['button_text_decoration']) ? $cta_btn_style['button_text_decoration'] : "";
            $line_height = isset($cta_btn_style['button_line_height']) ? $cta_btn_style['button_line_height'] : "";
            $letter_spacing = isset($cta_btn_style['button_letter_spacing']) ? $cta_btn_style['button_letter_spacing'] : "";
            $word_spacing = isset($cta_btn_style['button_word_spacing']) ? $cta_btn_style['button_word_spacing'] : "";

            $border_width = isset($cta_btn_style['button_border_width']) ? $cta_btn_style['button_border_width'] : "";
            $border_style = isset($cta_btn_style['button_border_style']) ? $cta_btn_style['button_border_style'] : "";
            $border_color = isset($cta_btn_style['button_border_color']) ? $cta_btn_style['button_border_color'] : "";
            $border_radius = isset($cta_btn_style['button_border_radius']) ? $cta_btn_style['button_border_radius'] : "";

            $button_pt = isset($cta_btn_style['button_pt']) ? $cta_btn_style['button_pt'] : "";
            $button_pr = isset($cta_btn_style['button_pr']) ? $cta_btn_style['button_pr'] : "";
            $button_pb = isset($cta_btn_style['button_pb']) ? $cta_btn_style['button_pb'] : "";
            $button_pl = isset($cta_btn_style['button_pl']) ? $cta_btn_style['button_pl'] : "";

            if ($button_open_new_tab == 'on') {
                $target = '_blank';
            }
            $style = 'background-color: ' . esc_attr( $background_color ) . ';
                      color: ' . esc_attr( $color ) . ';
                      font-size: ' . esc_attr( $font_size ) . 'px;
                      font-weight: ' . esc_attr( $font_weight ) . ';
                      text-align: center;
                      display: inline-block;
                      text-transform: ' . esc_attr( $text_transform ) . ';
                      font-style: ' . esc_attr( $font_style ) . ';
                      text-decoration: ' . esc_attr( $text_decoration ) . ';
                      line-height: ' . esc_attr( $line_height ) . ';
                      letter-spacing: ' . esc_attr( $letter_spacing ) . 'px;
                      word-spacing: ' . esc_attr( $word_spacing ) . 'px;
                      border: ' . esc_attr( $border_width ) . 'px ' . esc_attr( $border_style ) . ' ' . esc_attr( $border_color ) . ';
                      border-radius: ' . esc_attr(  $border_radius ). 'px;
                      padding: ' . esc_attr( $button_pt ) . 'px ' . esc_attr( $button_pr ) . 'px ' . esc_attr( $button_pb ) . 'px ' . esc_attr( $button_pl ) . 'px;
                     ';
            $html .= '<div class="wpvr-call-to-action-button position-' . esc_attr( $text_align ) . '" style="max-width:' . esc_attr( $width ) . esc_attr( $width_unit ) . '">
                        <a href="' . esc_url( $buttonurl ) . '" style="' . esc_attr( $style ) . '" target="' . esc_attr( $target ) . '">' . esc_attr( $buttontext ) . '</a>
                      </div>';
        }
    }

    //script started

    $html .= '<script>';
    if (isset($postdata['bg_music']) && $bg_music == 'on') {
        $html .= '
            var x' . $id . ' = document.getElementById("vrAudio' . $id . '");
            var playing' . $id . ' = false;
            var autoplaySupported' . $id . ' = false;
            var alertShown' . $id . ' = false;
            var autoplayChecked' . $id . ' = false;
        
            function playPause' . $id . '() {
                if (playing' . $id . ') {
                    jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                    x' . $id . '.pause();
                    jQuery("#audio_control' . $id . '").attr("data-play", "off");
                    playing' . $id . ' = false;
                } else {
                    x' . $id . '.muted = false;
                    x' . $id . '.play().then(function() {
                        jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                        jQuery("#audio_control' . $id . '").attr("data-play", "on");
                        playing' . $id . ' = true;
                    }).catch(function(e) {
                        console.log("Play failed:", e);
                    });
                }
            }
        
            function audionEnd' . $id . '() {
                playing' . $id . ' = false;
                jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                jQuery("#audio_control' . $id . '").attr("data-play", "off");
            }
        
            x' . $id . '.addEventListener("ended", audionEnd' . $id . ');';

        if ($autoplay_bg_music == 'on') {
            $html .= '
        
                x' . $id . '.addEventListener("loadeddata", function() {
                    if (!autoplayChecked' . $id . ') {
                        checkAutoplayStatus' . $id . '();
                    }
                });
        
                x' . $id . '.addEventListener("canplay", function() {
                    if (!autoplayChecked' . $id . ') {
                        checkAutoplayStatus' . $id . '();
                    }
                });
        
                x' . $id . '.addEventListener("canplaythrough", function() {
                    if (!autoplayChecked' . $id . ') {
                        checkAutoplayStatus' . $id . '();
                    }
                });
        
                setTimeout(function() {
                    if (!autoplayChecked' . $id . ') {
                        checkAutoplayStatus' . $id . '();
                    }
                }, 1000);
        
                x' . $id . '.addEventListener("play", function() {
                    jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                    jQuery("#audio_control' . $id . '").attr("data-play", "on");
                    playing' . $id . ' = true;
                });
        
                x' . $id . '.addEventListener("pause", function() {
                    if (!playing' . $id . ') {
                        jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                        jQuery("#audio_control' . $id . '").attr("data-play", "off");
                    }
                });
        
                function checkAutoplayStatus' . $id . '() {
                    autoplayChecked' . $id . ' = true;
        
                    x' . $id . '.muted = true;
                    var playPromise = x' . $id . '.play();
        
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            if (x' . $id . '.muted || x' . $id . '.volume === 0) {
                                handleAutoplayBlocked' . $id . '();
                            } else {
                                autoplaySupported' . $id . ' = true;
                                jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $id . '").attr("data-play", "on");
                                playing' . $id . ' = true;
                            }
                        }).catch(function () {
                            handleAutoplayBlocked' . $id . '();
                        });
                    } else {
                        setTimeout(function () {
                            if (x' . $id . '.paused || x' . $id . '.currentTime === 0) {
                                handleAutoplayBlocked' . $id . '();
                            } else {
                                autoplaySupported' . $id . ' = true;
                                jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $id . '").attr("data-play", "on");
                                playing' . $id . ' = true;
                            }
                        }, 300);
                    }
                }
        
                function handleAutoplayBlocked' . $id . '() {
                    autoplaySupported' . $id . ' = false;
                    if (!alertShown' . $id . ') {
                        alert("Autoplay is not supported in your browser. Please click the audio button to play music.");
                        alertShown' . $id . ' = true;
                    }
        
                    x' . $id . '.pause();
                    x' . $id . '.currentTime = 0;
                    x' . $id . '.muted = true;
        
                    jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                    jQuery("#audio_control' . $id . '").attr("data-play", "off");
        
                    document.getElementById("pano' . $id . '").addEventListener("click", musicPlay' . $id . ');
                    document.addEventListener("touchstart", musicPlay' . $id . ', { once: true });
                    document.addEventListener("click", musicPlay' . $id . ', { once: true });
                }
        
                function musicPlay' . $id . '() {
                    x' . $id . '.muted = false;
                    var playPromise = x' . $id . '.play();
        
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                            jQuery("#audio_control' . $id . '").attr("data-play", "on");
                            playing' . $id . ' = true;
                        }).catch(function(e) {
                            console.log("Play failed:", e);
                        });
                    } else {
                        setTimeout(function () {
                            if (!x' . $id . '.paused) {
                                jQuery("#vr-volume' . $id . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $id . '").attr("data-play", "on");
                                playing' . $id . ' = true;
                            }
                        }, 100);
                    }
        
                    document.getElementById("pano' . $id . '").removeEventListener("click", musicPlay' . $id . ');
                    document.removeEventListener("touchstart", musicPlay' . $id . ');
                    document.removeEventListener("click", musicPlay' . $id . ');
                }
                ';
        }
    }
    $html .= 'jQuery(document).ready(function() {';
    $html .= 'var response = ' . $response . ';';
    $html .= 'var scenes = response[1];';
    $html .= 'if(scenes) {';
    $html .= 'var scenedata = scenes.scenes;';
    $html .= 'for(var i in scenedata) {';
    $html .= 'var scenehotspot = scenedata[i].hotSpots;';
    $html .= 'for(var i = 0; i < scenehotspot.length; i++) {';
    $html .= 'if(scenehotspot[i].type === "info") {';
    $html .= '    scenehotspot[i]["clickHandlerFunc"] = wpvrhotspot;';
    $html .= '} else if(scenehotspot[i].type === "scene") {';
    $html .= '    scenehotspot[i]["clickHandlerArgs"] = scenehotspot[i]["text"];';
    $html .='if(wpvr_public.is_pro_active) {';
    $html .= '    scenehotspot[i]["clickHandlerFunc"] = wpvrhotspotscene;';
    $html .='}';
    $html .= '}';
    if (wpvr_isMobileDevice() && get_option('dis_on_hover') == "true") {
    } else {
        $html .= 'if(scenehotspot[i]["createTooltipArgs"] != "") {';
        $html .= 'scenehotspot[i]["createTooltipFunc"] = wpvrtooltip;';
        $html .= '}';
    }
    $html .= '}';
    $html .= '}';
    $html .= '}';
    $html .= 'var panoshow' . $id . ' = pannellum.viewer(response[0]["panoid"], scenes);';


    //===Dplicate mode only for vr mode===//
    $response2 = json_decode($response);
    $response2[1]->compass = false;
    $response2[1]->autoRotate = false;
    $response = json_encode($response2, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $html .= 'var response_duplicate = ' . $response . ';';
    $html .= 'var scenes_duplicate = response_duplicate[1];';

    $html .= 'if(scenes_duplicate) {';
    $html .= 'var scenedata = scenes_duplicate.scenes;';
    $html .= 'for(var i in scenedata) {';
    $html .= 'var scenehotspot = scenedata[i].hotSpots;';
    $html .= 'for(var i = 0; i < scenehotspot.length; i++) {';
    $html .= 'if(scenehotspot[i]["clickHandlerArgs"] != "") {';
    $html .= 'scenehotspot[i]["clickHandlerFunc"] = wpvrhotspot;';
    $html .= '}';
    if (wpvr_isMobileDevice() && get_option('dis_on_hover') == "true") {
    } else {
        $html .= 'if(scenehotspot[i]["createTooltipArgs"] != "") {';
        $html .= 'scenehotspot[i]["createTooltipFunc"] = wpvrtooltip;';
        $html .= '}';
    }
    $html .= '}';
    $html .= '}';
    $html .= '}';
    $html .= 'var vr_mode = "off";';
    $status  = get_option('wpvr_edd_license_status');
    if ($status !== false &&  'valid' == $status  && $is_pro) {
        $html .= 'var panoshow2' . $id . ' = pannellum.viewer("pano2' . $id . '", scenes_duplicate);';

        // Show Cardboard Mode in Tour
        $html .= '
        var tim;
        var im = 0;
        var active_scene = "' . $default_scene . '";
        var c_time;
        c_time = new Date();
        var timer = c_time.getTime() + 2000;
       function panoShowCardBoardOnTrigger(data){
            if(scenes_duplicate) {
                var scenedata = scenes_duplicate.scenes;
                for(var i in scenedata) {
                    if(active_scene === i) {
                        var scenehotspot = scenedata[i].hotSpots;
                        for(var j in scenehotspot) {
                            var plusFiveYaw = Math.round(scenehotspot[j].yaw) + 5;
                            var minusFiveYaw = Math.round(scenehotspot[j].yaw) - 5;
                            var plusFivePitch = Math.round(scenehotspot[j].pitch) + 5;
                            var minusFivePitch = Math.round(scenehotspot[j].pitch) - 5;
                            var firstCondition = ( Math.round(data.pitch) > minusFivePitch) && (Math.round(data.pitch) < plusFivePitch) ;
                            var secCondition = (Math.round(data.yaw) > minusFiveYaw) && (Math.round(data.yaw) < plusFiveYaw);
                            if(( Math.round(data.pitch) > minusFivePitch) && (Math.round(data.pitch) < plusFivePitch) ){
                                if((Math.round(data.yaw) > minusFiveYaw) && (Math.round(data.yaw) < plusFiveYaw)){
                                    jQuery(".center-pointer").addClass("wpvr-pluse-effect")
                                    var getScene = scenehotspot[j].sceneId;
                                    if(scenehotspot[j].type == "scene"){
                                            panoshow' . $id . '.loadScene(getScene);
                                            panoshow2' . $id . '.loadScene(getScene);
//                                            var inside_current_time_object = new Date();
//                                            var inside_timer = inside_current_time_object.getTime();
//                                            if(inside_timer > timer) {
//                                                panoshow' . $id . '.loadScene(getScene);
//                                                panoshow2' . $id . '.loadScene(getScene);
//                                                jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
//                                            }
                                    }else{
                                        jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
                                    }
                                }
                                else {
                                    jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
                                    c_time = new Date();
                                    timer = c_time.getTime() + 2000;
                                }
                            }
                            else {
                                c_time = new Date();
                                timer = c_time.getTime() + 2000;
                            }
                        }
                    }
                }
            }
       };
       
       function vrDeviseOrientation(){
            var data = {
                pitch: panoshow' . $id . '.getPitch(),
                yaw: panoshow' . $id . '.getYaw(),
            };
            panoShowCardBoardOnTrigger(data);
       }
    ';
        $html .= '
           
            function requestFullScreen(){
                var elem = document.getElementById("master-container");
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                  } else if (elem.webkitRequestFullscreen) { /* Safari */
                    elem.webkitRequestFullscreen();
                  } else if (elem.msRequestFullscreen) { /* IE11 */
                    elem.msRequestFullscreen();
                  }
            }
            function requestExitFullscreen(){
                var elem = document.getElementById("master-container");
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                 } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                 } else if (document.msExitFullscreen) { /* IE11 */
                    document.msExitFullscreen();
                 }
            }
            jQuery(document).on("click",".fullscreen-button .expand",function() {
                jQuery(this).hide()
                jQuery(this).parent().find(".compress").show()
                requestFullScreen()
                screen.orientation.lock("landscape-primary")
                .then(function() {
                })
                .catch(function(error) {
                    alert("Not Supported for this devise");
                });
        
            });   
            jQuery(document).on("click",".fullscreen-button .compress",function() {
                jQuery(this).hide()
                jQuery(this).parent().find(".expand").show()
                requestExitFullscreen()
                screen.orientation.unlock();
                 
            }); 
            
            let onLoadAnalytics = false;
            let sceneLoadAnalytics = false;
            function storeAnalyticsData(data) {
              if(wpvr_public.is_pro_active) {
                jQuery.ajax({
                    url: wpvrAnalyticsObj.ajaxUrl,
                    type: "POST",
                    data: {
                        action: "store_scene_hotspot_data",
                        scene_id: data.scene_id,
                        tour_id: data.tour_id,
                        type: data.type,
                        hotspot_id: data.hotspot_id || "",
                        user_agent: navigator.userAgent,
                        device_type: getDeviceType() || "desktop",
                        nonce: wpvrAnalyticsObj.nonce,
                    },
                    success: function (response) {
                        console.log("Data stored successfully");
                    },
                    error: function (error) {
                        console.log("Error in storing data");
                    }
                });
              }
            }
            
            function getDeviceType() {
                const userAgent = navigator.userAgent.toLowerCase();

                if (/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent)) {
                    return "mobile";
                } else if (/tablet|ipad/i.test(userAgent)) {
                     return "tablet";
                } else {
                    return "desktop";
                }
            }

            panoshow' . $id . '.on("scenechange", function(scene) {
                onLoadAnalytics = true;
                sceneLoadAnalytics = true;
                let scene_id = scene;
                let tour_id = ' . $id . ';
                let type = "scene";
                let hotspot_id = "";
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                storeAnalyticsData({
                    scene_id: scene_id,
                    tour_id: tour_id,
                    type: type,
                    hotspot_id: hotspot_id,
                    user_agent: user_agent,
                    device_type: device_type,
                });
            });
            
            panoshow' . $id . '.on("load", function() {
                let scene_id = panoshow' . $id . '.getScene();
                let tour_id = ' . $id . ';
                let type = "scene";
                let hotspot_id = "";
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                if(!onLoadAnalytics && !sceneLoadAnalytics){
                    storeAnalyticsData({
                        scene_id: scene_id,
                        tour_id: tour_id,
                        type: type,
                        hotspot_id: hotspot_id,
                        user_agent: user_agent,
                        device_type: device_type,
                    });
                }
            });
            
            function wpvrhotspotscene(hotSpotDiv, args) {
                onLoadAnalytics = true;
                let scene_id = panoshow' . $id . '.getScene();
                let tour_id = ' . $id . ';
                let type = "hotspot";
                let hotspot_id = args;
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                storeAnalyticsData({
                    scene_id: scene_id,
                    tour_id: tour_id,
                    type: type,
                    hotspot_id: hotspot_id,
                    user_agent: user_agent,
                    device_type: device_type
                });
            }
            
            ';

        $html .= '
        panoshow' . $id . '.on("scenechange", function (scene){
            jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
            active_scene = scene;
        });
        var compassBlock = "";
        var infoBlock = "";
        var getValue = "";
        jQuery(document).on("click",".vr_mode_change' . $id . '",function (){
          
          jQuery("#pano2' . $id . ' .pnlm-load-button").trigger("click");
          jQuery("#pano' . $id . ' .pnlm-load-button").trigger("click");
        
          getValue =   jQuery(this).val();
          var getParent = jQuery(this).parent().parent();
          var fullWidthParent = getParent.parent();
          var compass = getParent.find("#pano2' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display");
          var panoInfo = getParent.find("#pano' . $id . ' .pnlm-panorama-info").css("display");
          if(compass == "block"){
            compassBlock = "block";
          }
          if(panoInfo == "block"){
            infoBlock = "block";
          }
            if (getValue == "off") {
                requestFullScreen()
                screen.orientation.lock("landscape-primary")
                .then(function() {
                })
                .catch(function(error) {
                    alert("VR Glass Mode not supported in this device");
                });
                getParent.find(".pano-wrap").addClass("wpvr-cardboard-disable-event");
                // localStorage.setItem("vr_mode", "on");
                vr_mode = "on";
                jQuery(".vr-mode-title").show();
                jQuery(this).val("on");
                getParent.find("#pano2' . $id . '").css({
                    "opacity": "1", 
                    "visibility": "visible",
                    "position": "relative",
                });
                gyroSwitch = true;
                panoshow' . $id . '.startOrientation();
                panoshow2' . $id . '.startOrientation();
                panoshow2' . $id . '.setPitch(panoshow' . $id . '.getPitch(), 0);
                panoshow2' . $id . '.setYaw(panoshow' . $id . '.getYaw(), 0);
                getParent.find("#pano' . $id . ' #zoom-in-out-controls' . $id . '").hide();
                getParent.find("#pano' . $id . ' #controls' . $id . '").hide();
                getParent.find("#pano' . $id . ' #explainer_button_' . $id . '").hide();
                getParent.find("#pano' . $id . ' #generic_form_button_' . $id . '").hide();
                getParent.find("#pano' . $id . ' #floor_map_button_' . $id . '").hide();
                getParent.find("#pano' . $id . ' #vrgcontrols' . $id . '").hide();
                getParent.find("#pano' . $id . ' #sccontrols' . $id . '").hide();
                getParent.find("#pano' . $id . ' #adcontrol' . $id . '").hide();
                getParent.find("#pano' . $id . ' .owl-nav.wpvr_slider_nav").hide();
                getParent.find("#pano' . $id . ' #cp-logo-controls").hide();
                
                getParent.find("#pano' . $id . ' #custom-scene-navigation' . $id . '").hide();
                
                getParent.find("#pano2' . $id . ' .pnlm-controls-container").hide();
                getParent.find("#pano' . $id . ' .pnlm-controls-container").hide();
                
                getParent.find("#pano2' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").hide();
                getParent.find("#pano' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").hide();
                
               getParent.find("#pano2' . $id . ' .pnlm-panorama-info").hide();
               getParent.find("#pano' . $id . ' .pnlm-panorama-info").hide();
               getParent.find("#pano' . $id . '").addClass("cardboard-half");
               
                getParent.find("#center-pointer' . $id . '").show();
                getParent.find(".fullscreen-button").hide();
    
                
                if (window.DeviceOrientationEvent) {
                    window.addEventListener("deviceorientation", vrDeviseOrientation);
                }
              
                panoshow' . $id . '.on("mousemove", function (data){
                    panoshow2' . $id . '.setPitch(data.pitch, 0);
                    panoshow2' . $id . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
            
                });
                panoshow2' . $id . '.on("mousemove", function (data){
                    panoshow2' . $id . '.setPitch(data.pitch, 0);
                    panoshow' . $id . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
            
                });

                panoshow' . $id . '.on("zoomchange", function (data){
                    panoshow2' . $id . '.setHfov(data, 0);
                });

                panoshow2' . $id . '.on("zoomchange", function (data){
                    panoshow' . $id . '.setHfov(data, 0);
                });
                
                panoshow' . $id . '.on("touchmove", function (data){
                    panoshow' . $id . '.stopOrientation();
                    panoshow2' . $id . '.stopOrientation();
                    panoshow2' . $id . '.setPitch(data.pitch, 0);
                    panoshow2' . $id . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
            
                });
                
                panoshow2' . $id . '.on("touchmove", function (data){
                    panoshow' . $id . '.stopOrientation();
                    panoshow2' . $id . '.stopOrientation();
                    panoshow' . $id . '.setPitch(data.pitch, 0);
                    panoshow' . $id . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
            
                });
                
            }
            else if(getValue == "on") {
                screen.orientation.unlock();
                requestExitFullscreen();
                 // localStorage.setItem("vr_mode", "off");
                vr_mode = "off";
                jQuery(".vr-mode-title").hide();
                jQuery(this).val("off");
                getParent.find("#pano2' . $id . '").css({
                    "opacity": "0", 
                    "visibility": "hidden",
                    "position": "absolute",
                });
                panoshow' . $id . '.stopOrientation();
                panoshow2' . $id . '.stopOrientation();
                getParent.find(".pano-wrap").removeClass("wpvr-cardboard-disable-event");
                getParent.find("#pano' . $id . ' #zoom-in-out-controls' . $id . '").show();
                getParent.find("#pano' . $id . ' #controls' . $id . '").show();
                getParent.find("#pano' . $id . ' #explainer_button_' . $id . '").show();
                getParent.find("#pano' . $id . ' #generic_form_button_' . $id . '").show();
                getParent.find("#pano' . $id . ' #floor_map_button_' . $id . '").show();

                getParent.find("#pano2' . $id . ' .pnlm-controls-container").show();
                getParent.find("#pano' . $id . ' .pnlm-controls-container").show();
                getParent.find("#pano' . $id . ' #vrgcontrols' . $id . '").show();
                getParent.find("#pano' . $id . ' #sccontrols' . $id . '").hide();
                getParent.find("#pano' . $id . ' #adcontrol' . $id . '").show();
                getParent.find("#pano' . $id . ' .owl-nav.wpvr_slider_nav").hide();
                getParent.find("#pano' . $id . ' #cp-logo-controls").show();
                getParent.find("#pano' . $id . ' #custom-scene-navigation' . $id . '").show();
                
                if(compassBlock == "block"){
                    getParent.find("#pano2' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").show();
                    getParent.find("#pano' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").show();
                }
                if(infoBlock == "block"){
                    getParent.find("#pano2' . $id . ' .pnlm-panorama-info").show();
                    getParent.find("#pano' . $id . ' .pnlm-panorama-info").show();
                }
               
                getParent.find("#pano' . $id . '").removeClass("cardboard-half");
                getParent.find("#center-pointer' . $id . '").hide();
                getParent.find(".fullscreen-button").hide();
                panoshow' . $id . '.off("mousemove");
                panoshow' . $id . '.off("touchmove");
                panoshow2' . $id . '.off("mousemove");
                panoshow2' . $id . '.off("touchmove");
                if (window.DeviceOrientationEvent) {
                    window.removeEventListener("deviceorientation", vrDeviseOrientation);
                }
            }
        });';

        $html .= 'panoshow2' . $id . '.on("load", function (){
//                if(localStorage.getItem("vr_mode") == "off") {
                 if( vr_mode == "off") {
                      jQuery(".vr-mode-title").hide();
                    }
                 else {
                    jQuery("#pano2' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                    jQuery("#pano' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                    jQuery("#pano2' . $id . ' .pnlm-panorama-info").hide();
                    jQuery("#pano' . $id . ' .pnlm-panorama-info").hide();
                    jQuery(".vr-mode-title").show();
                 }
         });';
    }

    $html .= 'jQuery("#pano' . $id . ' .wpvr-floor-map .floor-plan-pointer").on("click",function(){
           var scene_id = jQuery(this).attr("scene_id");
           panoshow' . $id . '.loadScene(scene_id)
           jQuery(".floor-plan-pointer").removeClass("add-pulse")
           jQuery(this).addClass("add-pulse")
    });';

    if ($scene_animation == 'on') {
        $animation_js = apply_filters('wpvr_scene_animation_js', $id, $animation_type, $animationDuration, $animationDelay);
        if (!empty($animation_js)) {
            $html .= $animation_js;
            $html .= 'panoshow' . $id . '.on("load", function (scene){
                if (typeof changeScene === "function") {
                    changeScene();
                } else {
                    console.warn("changeScene function is not defined.");
                }
            });';
        }
    }


    $html .= '
    panoshow' . $id . '.on("mousemove", function (data){
        jQuery(".add-pulse").css({"transform":"rotate("+data.yaw+"deg)"});
    });
';



    $status  = get_option('wpvr_edd_license_status');
    if ($status !== false &&  'valid' == $status  && $is_pro) {
        $html .= 'panoshow' . $id . '.on("scenechange", function (scene){
            jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
            jQuery(".floor-plan-pointer").each(function(index ,element){
                var scene_id = jQuery(this).attr("scene_id");
                if( active_scene == scene_id ){
                    jQuery(".floor-plan-pointer").removeClass("add-pulse")
                    jQuery(this).addClass("add-pulse")
                }
            });
            
        });';
        $html .= 'panoshow' . $id . '.on("load", function (){
           if(jQuery(".floor-plan-pointer").length > 0){
               jQuery(".floor-plan-pointer").each(function(index ,element){
                    var scene_id = jQuery(this).attr("scene_id");
                    if( active_scene == scene_id ){
                        jQuery(".floor-plan-pointer").removeClass("add-pulse")
                        jQuery(this).addClass("add-pulse")
                    }
                });
           }
        });';
    }

    if ($status !== false &&  'valid' == $status  && $is_pro) {
        $scene_navigation_content_type = isset($postdata['scene_navigation_content_type']) ? $postdata['scene_navigation_content_type'] : 'scene_id';
        $html .= '
         jQuery("#pano' . $id . ' .custom-scene-navigation").on("click",function(){
            jQuery("#custom-scene-navigation-nav' . $id . ' ul").empty()
                if(scenes){
                    var scene_navigation_content_type = ' . "'" . $scene_navigation_content_type . "'" . ';
                    var sceneList = scenes.scenes;                    
                    var getScene = panoshow' . $id . '.getScene();
                    for (const key in sceneList) {
                        let title;
                        if (scene_navigation_content_type === "scene_title") {
                            if (sceneList[key].title) {
                                title = sceneList[key].title;
                                
                            }else{
                                if(title == "" || title == undefined){
                                    if (sceneList[key].panorama) {
                                        title = getImageNameWithoutExtension(sceneList[key].panorama); 
                                    }
                                }
                            }
                        } else if (scene_navigation_content_type === "scene_image_name") {
                            if (sceneList[key].panorama) {
                                title = getImageNameWithoutExtension(sceneList[key].panorama); 
                            }
                        } else {
                            title = key;
                        }
                    if (sceneList.hasOwnProperty(key)) {
                    if( key === getScene){
                        jQuery("#custom-scene-navigation-nav' . $id . ' ul").append("<li class=\"scene-navigation-list active\" scene_id= " + key + " >" + title + "</li>");
                    }else{
                            jQuery("#custom-scene-navigation-nav' . $id . ' ul").append("<li class=\"scene-navigation-list\" scene_id= " + key + " >" + title + "</li>");
                        }
                    }
                }
            }
             jQuery("#custom-scene-navigation-nav' . $id . '").toggleClass("visible");
         });
     ';

        $html .= 'function getImageNameWithoutExtension(imageUrl) {
                    // Split the URL by "/"
                    var parts = imageUrl.split("/");
                
                    // Get the last part which contains the image name
                    var imageNameWithExtension = parts[parts.length - 1];
                
                    // Split the image name by period (.)
                    var imageNameParts = imageNameWithExtension.split(".");
                
                    // Remove the last part (which is the extension) and join the remaining parts
                    var imageNameWithoutExtension = imageNameParts.slice(0, -1).join(".");
                
                    // Return the image name without extension
                    return imageNameWithoutExtension;
                }';


        $html .= '
        jQuery("#pano' . $id . ' #custom-scene-navigation-nav' . $id . ' ul").on("click", "li.scene-navigation-list", function() {
            if (scenes) {
                jQuery(this).siblings("li").removeClass("active");
                jQuery(this).addClass("active");

                var scene_key = jQuery(this).attr("scene_id");
                panoshow' . $id . '.loadScene(scene_key);
            }
        });
     ';
    }

    //Duplicate mode only for vr mode end===//
    $html .= 'panoshow' . $id . '.on("load", function (){
    
//              if(localStorage.getItem("vr_mode") == "off") {
               if( vr_mode == "off") {
                  jQuery(".vr-mode-title").hide();
             } 
             else {
                jQuery("#pano2' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                jQuery("#pano' . $id . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                jQuery("#pano2' . $id . ' .pnlm-panorama-info").hide();
                jQuery("#pano' . $id . ' .pnlm-panorama-info").hide();
                jQuery(".vr-mode-title").show();
             }
             
    
          if (jQuery("#pano' . $id . '").children().children(".pnlm-panorama-info:visible").length > 0) {
               jQuery("#controls' . $id . '").css("bottom", "55px");
           }
           else {
             jQuery("#controls' . $id . '").css("bottom", "5px");
           }
        });';


    $html .= '
        if (scenes.autoRotate) {
          panoshow' . $id . '.on("load", function (){
           setTimeout(function(){ panoshow' . $id . '.startAutoRotate(scenes.autoRotate, 0); }, 3000);
          });
          panoshow' . $id . '.on("scenechange", function (){
           setTimeout(function(){ panoshow' . $id . '.startAutoRotate(scenes.autoRotate, 0); }, 3000);
          });
        }
        ';
    $html .= 'var touchtime = 0;';
    if ($vrgallery) {
        if (isset($panodata["scene-list"])) {
            foreach ($panodata["scene-list"] as $panoscenes) {
                $scene_key = $panoscenes['scene-id'];
                $scene_key_gallery = $panoscenes['scene-id'] . '_gallery_' . $id;
                $img_src_url = $panoscenes['scene-attachment-url'];
                // $html .= 'document.getElementById("' . $scene_key_gallery . '").addEventListener("click", function(e) { ';
                // $html .= 'if (touchtime == 0) {';
                // $html .= 'touchtime = new Date().getTime();';
                // $html .= '} else {';
                // $html .= 'if (((new Date().getTime()) - touchtime) < 800) {';
                // $html .= 'panoshow' . $id . '.loadScene("' . $scene_key . '");';
                // $html .= 'touchtime = 0;';
                // $html .= '} else {';
                // $html .= 'touchtime = new Date().getTime();';
                // $html .= '}';
                // $html .= '}';
                // $html .= '});';
                $html .= '
                jQuery(document).on("click","#' . $scene_key_gallery . '",function() {
                    panoshow' . $id . '.loadScene("' . $scene_key . '");
                });
                ';
            }
        }
    }



    //===Custom Control===//
    if (isset($custom_control)) {
        if ($custom_control['panupSwitch'] == "on") {
            $html .= 'document.getElementById("pan-up' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setPitch(panoshow' . $id . '.getPitch() + 10);';
            $html .= '});';
        }

        if ($custom_control['panDownSwitch'] == "on") {
            $html .= 'document.getElementById("pan-down' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setPitch(panoshow' . $id . '.getPitch() - 10);';
            $html .= '});';
        }

        if ($custom_control['panLeftSwitch'] == "on") {
            $html .= 'document.getElementById("pan-left' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setYaw(panoshow' . $id . '.getYaw() - 10);';
            $html .= '});';
        }

        if ($custom_control['panRightSwitch'] == "on") {
            $html .= 'document.getElementById("pan-right' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setYaw(panoshow' . $id . '.getYaw() + 10);';
            $html .= '});';
        }

        if ($custom_control['panZoomInSwitch'] == "on") {
            $html .= 'document.getElementById("zoom-in' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setHfov(panoshow' . $id . '.getHfov() - 10);';
            $html .= '});';
        }

        if ($custom_control['panZoomOutSwitch'] == "on") {
            $html .= 'document.getElementById("zoom-out' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.setHfov(panoshow' . $id . '.getHfov() + 10);';
            $html .= '});';
        }

        if ($custom_control['panFullscreenSwitch'] == "on") {
            $html .= 'document.getElementById("fullscreen' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.toggleFullscreen();';
            $html .= '});';
        }

        if ($custom_control['backToHomeSwitch'] == "on") {
            $html .= 'document.getElementById("backToHome' . $id . '").addEventListener("click", function(e) {';
            $html .= 'panoshow' . $id . '.loadScene("' . $default_scene . '");';
            $html .= '});';
        }

        if ($custom_control['gyroscopeSwitch'] == "on") {
            $html .= '
    var element = document.getElementById("gyroscope' . $id . '");
    var gyroSwitch = true;
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    var permissionGranted = false;

    function requestOrientationPermission() {
        if (isIOS) {
            if (typeof DeviceOrientationEvent.requestPermission === "function") {
                DeviceOrientationEvent.requestPermission()
                    .then(function(state) {
                        if (state === "granted") {
                            permissionGranted = true;
                            startOrientation();
                        } else {
                            element.children[0].style.color = "red";
                            gyroSwitch = false;
                            alert("Permission to use motion sensors was denied.");
                        }
                    })
                    .catch(function(error) {
                        element.children[0].style.color = "red";
                        gyroSwitch = false;
                        console.error("Error requesting device orientation permission:", error);
                    });
            }
        } else {
            startOrientation();
        }
    }

    function startOrientation() {
        panoshow' . $id . '.startOrientation();
        element.children[0].style.color = "'.$custom_control['gyroscopeColor'].'";
        gyroSwitch = true;
    }

    panoshow' . $id . '.on("load", function() {
        if (!isIOS || permissionGranted) {
            if (gyroSwitch) {
                startOrientation();
            } else {
                panoshow' . $id . '.stopOrientation();
                element.children[0].style.color = "red";
            }
        }
    });

    panoshow' . $id . '.on("scenechange", function() {
        if (!isIOS || permissionGranted) {
            if (panoshow' . $id . '.isOrientationActive()) {
                element.children[0].style.color = "'.$custom_control['gyroscopeColor'].'";
            } else {
                element.children[0].style.color = "red";
            }
        }
    });
    
    panoshow' . $id . '.on("touchstart", function() {
        if (!isIOS || permissionGranted) {
            if (panoshow' . $id . '.isOrientationActive()) {
                gyroSwitch = true;
                element.children[0].style.color = "'.$custom_control['gyroscopeColor'].'";
            } else {
                gyroSwitch = false;
                element.children[0].style.color = "red";
            }
        }
    });';

            $html .= 'document.getElementById("gyroscope' . $id . '").addEventListener("click", function(e) {
        var element = document.getElementById("gyroscope' . $id . '");
        if (isIOS && typeof DeviceOrientationEvent.requestPermission === "function" && !permissionGranted) {
            requestOrientationPermission();
        } else {
            if (panoshow' . $id . '.isOrientationActive()) {
                panoshow' . $id . '.stopOrientation();
                gyroSwitch = false;
                element.children[0].style.color = "red";
            } else {
                startOrientation();
            }
        }
    });';
        }
    }

    $angle_up = '<i class="fa fa-angle-up"></i>';
    $angle_down = '<i class="fa fa-angle-down"></i>';
    $sin_qout = "'";

    //===Explainer Script===//
    $html .= '
    jQuery(document).on("click","#explainer_button_' . $id . '",function() {
        jQuery("#explainer' . $id . '").slideToggle();
        // jQuery(".explainer").slideToggle();
      });

      jQuery(document).on("click",".close-explainer-video",function() {
        jQuery(this).parent(".explainer").hide();
      });
      jQuery(document).on("click","#pano' . $id . '",function(event) {
        var isActiveModal = event.target.closest(".custom-ifram-wrapper");
        var isForm = event.target.closest(".wpvr-hotspot-tweak-contents");
        if( isActiveModal == null && isForm == null){
             jQuery(".custom-ifram-wrapper").hide();
             jQuery(this).removeClass("show-modal");
             jQuery(".wpvr-hotspot-tweak-contents-wrapper").hide("show-modal");
        }else if(isForm != null){
            jQuery(this).addClass("show-modal");
        }
      });

    ';
    //===Explainer Script End===//

    //===generic form script===//
    if (isset($postdata["genericform"]) && $postdata["genericform"] === 'on') {
        $html .= '
        jQuery(document).on("click","#generic_form_button_' . $id . '",function() {
          jQuery("#wpvr-generic-form' . $id . '").fadeToggle();
        });
  
        jQuery(document).on("click",".close-generic-form",function() {
          jQuery(this).parent(".wpvr-generic-form").fadeOut()
        });
        ';
    }

    //===generic from script===//

    //===Floor map  Script===//
    $html .= '
    jQuery(document).on("click","#floor_map_button_' . $id . '",function() {
        jQuery("#wpvr-floor-map' . $id . '").toggle().removeClass("fullwindow");
      });

      jQuery(document).on("dblclick","#wpvr-floor-map' . $id . '",function(){
        jQuery(this).addClass("fullwindow");
        jQuery(this).parents(".pano-wrap").addClass("show-modal");
      });
      
      jQuery(document).on("click",".close-floor-map-plan",function() {
        jQuery(this).parent(".wpvr-floor-map").hide();
        jQuery(this).parent(".wpvr-floor-map").removeClass("fullwindow");
        jQuery(this).parents(".pano-wrap").removeClass("show-modal");
      });

    ';
    //===Floor map Script End===//

    if ($vrgallery_display) {
        if (!$autoload) {
            $html .= '
          jQuery(document).ready(function($){
              jQuery("#sccontrols' . $id . '").hide();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
              jQuery("#sccontrols' . $id . '").hide();
              jQuery(".wpvr_slider_nav").hide();
          });
          ';

            $html .= '
            var slide' . $id . ' = "down";
            jQuery(document).on("click","#vrgcontrols' . $id . '",function() {

              if (slide' . $id . ' == "up") {
                jQuery(".vrgctrl' . $id . '").empty();
                jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
                slide' . $id . ' = "down";
                jQuery(".wpvr_slider_nav").slideToggle();
    		    jQuery("#sccontrols' . $id . '").slideToggle();
              }
              else {
                jQuery(".vrgctrl' . $id . '").empty();
                jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
                slide' . $id . ' = "up";
                jQuery(".wpvr_slider_nav").slideToggle();
    		    jQuery("#sccontrols' . $id . '").slideToggle();
              }
            });
            ';
        } else {
            $html .= '
          jQuery(document).ready(function($){
            jQuery("#sccontrols' . $id . '").show();
            jQuery(".wpvr_slider_nav").show();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
          });
          ';

            $html .= '
          var slide' . $id . ' = "down";
          jQuery(document).on("click","#vrgcontrols' . $id . '",function() {

            if (slide' . $id . ' == "up") {
              jQuery(".vrgctrl' . $id . '").empty();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
              slide' . $id . ' = "down";
            }
            else {
              jQuery(".vrgctrl' . $id . '").empty();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
              slide' . $id . ' = "up";
            }
            jQuery(".wpvr_slider_nav").slideToggle();
            jQuery("#sccontrols' . $id . '").slideToggle();
          });
          ';
        }
    } else {
        $html .= '
          jQuery(document).ready(function($){
            jQuery("#sccontrols' . $id . '").hide();
            jQuery(".wpvr_slider_nav").hide();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
          });
          ';

        $html .= '
          var slide' . $id . ' = "down";
          jQuery(document).on("click","#vrgcontrols' . $id . '",function() {

            if (slide' . $id . ' == "up") {
              jQuery(".vrgctrl' . $id . '").empty();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
              slide' . $id . ' = "down";
            }
            else {
              jQuery(".vrgctrl' . $id . '").empty();
              jQuery(".vrgctrl' . $id . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
              slide' . $id . ' = "up";
            }
            jQuery(".wpvr_slider_nav").slideToggle();
            jQuery("#sccontrols' . $id . '").slideToggle();
          });
          ';
    }

    if (!$autoload) {
        $html .= '

          jQuery(document).ready(function(){
                jQuery("#controls' . $id . '").hide();
                jQuery("#zoom-in-out-controls' . $id . '").hide();
                jQuery("#adcontrol' . $id . '").hide();
                jQuery("#explainer_button_' . $id . '").hide();
                jQuery("#generic_form_button_' . $id . '").hide();
                jQuery("#floor_map_button_' . $id . '").hide();
                jQuery("#vrgcontrols' . $id . '").hide();
                jQuery("#cp-logo-controls").hide();
                jQuery(".custom-scene-navigation").hide();
                jQuery("#pano' . $id . '").find(".pnlm-panorama-info").hide();
          });

          ';

        if ($vrgallery_display) {
            $html .= 'var load_once = "true";';
            $html .= 'panoshow' . $id . '.on("load", function (){
                    if (load_once == "true") {
                      load_once = "false";
                      jQuery("#sccontrols' . $id . '").slideToggle();
                      jQuery(".wpvr_slider_nav").slideToggle();
                    }
            });';
        }

        $html .= 'panoshow' . $id . '.on("load", function (){
              jQuery("#controls' . $id . '").show();
              jQuery("#zoom-in-out-controls' . $id . '").show();
              jQuery("#adcontrol' . $id . '").show();
              jQuery("#explainer_button_' . $id . '").show();
              jQuery("#generic_form_button_' . $id . '").show();
              jQuery("#floor_map_button_' . $id . '").show();
              jQuery("#vrgcontrols' . $id . '").show();
              jQuery("#cp-logo-controls").show();
              jQuery(".custom-scene-navigation").show();
              jQuery("#pano' . $id . '").find(".pnlm-panorama-info").show();
          });';
    }

    $previewText = $postdata['previewtext'] ?? 'Click To Load Panorama';
    if ($previewText) {
        $html .= '
        jQuery("#pano' . $id . '").children(".pnlm-ui").find(".pnlm-load-button p").text("' . $previewText. '")
        ';
    } else {
        $html .= '
        jQuery("#pano' . $id . '").children(".pnlm-ui").find(".pnlm-load-button p").text("Click To Load Panorama")
        ';
    }

    $html .= 'jQuery("#pano' . $id . ' .pnlm-title-box").on("mouseenter", function(){
                jQuery(this).attr("title", jQuery(this).text());
            });
            jQuery("#pano' . $id . ' .pnlm-title-box").on("mouseleave", function(){
                jQuery(this).removeAttr("title");
            });';
    $html .= '});';



    $html .= '</script>';

    $tour_data = [];
    if(defined("WPVR_PRO_VERSION")){
        $tour_data = array(
            'explainerControlSwitch' => $explainerControlSwitch ?? false,
            'floor_plan_enable' => $floor_plan_enable ?? false,
            'floor_plan_image' => $floor_plan_image ?? '',
            'custom_control' => $custom_control ?? [],
        );
    }

    return apply_filters('wpvr_generate_tour_layout_html', $html ,$postdata ,$id, $tour_data);
}

function sanitize_content_preserve_styles($content) {
    // Remove script tags completely
    $content = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $content);

    // Remove dangerous protocols
    $content = preg_replace('/javascript:/i', '', $content);
    $content = preg_replace('/vbscript:/i', '', $content);
    $content = preg_replace('/data:/i', '', $content);
    $content = preg_replace('/about:/i', '', $content);

    // Remove all event handlers (onclick, onload, etc.)
    $content = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
    $content = preg_replace('/\s*on\w+\s*=\s*[^>\s]+/i', '', $content);

    // Remove dangerous tags that can execute code
    $content = preg_replace('/<(object|embed|applet|iframe|frame|frameset|meta|link|base|form|input|button|textarea|select|option)\b[^>]*>/i', '', $content);
    $content = preg_replace('/<\/(object|embed|applet|iframe|frame|frameset|meta|link|base|form|input|button|textarea|select|option)>/i', '', $content);

    // Sanitize CSS in style attributes - remove dangerous CSS functions
    $content = preg_replace_callback('/style\s*=\s*["\']([^"\']*)["\']/', function($matches) {
        $style = $matches[1];

        // Remove dangerous CSS functions
        $style = preg_replace('/expression\s*\(/i', '', $style);
        $style = preg_replace('/javascript:/i', '', $style);
        $style = preg_replace('/vbscript:/i', '', $style);
        $style = preg_replace('/data:/i', '', $style);
        $style = preg_replace('/about:/i', '', $style);
        $style = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', '', $style);
        $style = preg_replace('/url\s*\(\s*["\']?\s*vbscript:/i', '', $style);
        $style = preg_replace('/url\s*\(\s*["\']?\s*data:/i', '', $style);
        $style = preg_replace('/import\s*["\']?\s*javascript:/i', '', $style);
        $style = preg_replace('/behavior\s*:/i', '', $style);
        $style = preg_replace('/-moz-binding\s*:/i', '', $style);

        return 'style="' . $style . '"';
    }, $content);

    // Sanitize CSS in style tags
    $content = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/si', function($matches) {
        $css = $matches[1];

        // Remove dangerous CSS functions
        $css = preg_replace('/expression\s*\(/i', '', $css);
        $css = preg_replace('/javascript:/i', '', $css);
        $css = preg_replace('/vbscript:/i', '', $css);
        $css = preg_replace('/data:/i', '', $css);
        $css = preg_replace('/about:/i', '', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', '', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*vbscript:/i', '', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*data:/i', '', $css);
        $css = preg_replace('/import\s*["\']?\s*javascript:/i', '', $css);
        $css = preg_replace('/behavior\s*:/i', '', $css);
        $css = preg_replace('/-moz-binding\s*:/i', '', $css);

        return '<style>' . $css . '</style>';
    }, $content);

    // Remove dangerous attributes from any tag
    $content = preg_replace('/\s*srcdoc\s*=\s*["\'][^"\']*["\']/i', '', $content);
    $content = preg_replace('/\s*formaction\s*=\s*["\'][^"\']*["\']/i', '', $content);
    $content = preg_replace('/\s*action\s*=\s*["\'][^"\']*["\']/i', '', $content);

    // Clean up malformed HTML that might bypass filters
    $content = preg_replace('/<[^>]*script[^>]*>/i', '', $content);
    $content = preg_replace('/<[^>]*on\w+[^>]*>/i', '', $content);

    // Remove comments that might contain dangerous code
    $content = preg_replace('/<!--.*?-->/s', '', $content);

    // Remove any remaining dangerous patterns
    $content = preg_replace('/\beval\s*\(/i', '', $content);
    $content = preg_replace('/\bsetTimeout\s*\(/i', '', $content);
    $content = preg_replace('/\bsetInterval\s*\(/i', '', $content);

    // Final cleanup - remove any orphaned closing tags from removed elements
    $content = preg_replace('/<\/(script|object|embed|applet|iframe|frame|frameset|meta|link|base|form|input|button|textarea|select|option)>/i', '', $content);
    error_log(wp_kses_post($content));
    // Final pass with wp_kses_post for additional security
    return wp_kses_post($content);
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
        $pluginBaseUrl = rtrim(plugins_url(basename(__DIR__)), '\\/');
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
        $url = admin_url('admin.php?page=wpvr-setup-wizard');
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