<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/public
 */

use WPVR\Builder\DIVI\WPVR_Divi_modules;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpvr
 * @subpackage Wpvr/public
 * @author     Rextheme <support@rextheme.com>
 */
class Wpvr_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


    /**
     * Instance of WPVR_Shortcode class
     * 
     * @var object
     * @since 8.0.0
     */
    private $shortcode;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->shortcode = new WPVR_Shortcode($this->plugin_name);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_styles() {
		global $wp;
        $wpvr_script_control = get_option('wpvr_script_control');

        $should_load = false;

        if ( $wpvr_script_control == 'true' ) {
            $wpvr_script_list       = get_option('wpvr_script_list');
            $allowed_pages          = isset($wpvr_script_list) && !empty($wpvr_script_list) ? array_map('sanitize_text_field', explode(",", $wpvr_script_list)) : array();
            $allowed_pages_modified = array();
            foreach ($allowed_pages as $value) {
                $allowed_pages_modified[] = untrailingslashit($value);
            }
            $current_url = home_url(add_query_arg(isset($_GET) ? array_map('sanitize_text_field', wp_unslash($_GET)) : array(), isset($wp->request) ? sanitize_text_field($wp->request) : ''));
            foreach ($allowed_pages_modified as $value) {
                if ($value && strpos($current_url, $value) !== false) {
                    $should_load = true;
                    break;
                }
            }
        } else {
            $should_load = true;
        }

        if ( $should_load ) {
            $fontawesome_disable = get_option('wpvr_fontawesome_disable');
            if ($fontawesome_disable != 'true') {
                wp_enqueue_style($this->plugin_name . 'fontawesome', plugin_dir_url(__FILE__) . 'css/fontawesome/css/all.css', array(), $this->version, 'all');
                wp_enqueue_style(
                    $this->plugin_name . '-icons-fix',
                    plugin_dir_url(__FILE__) . 'css/fontawesome/css/icons-fix.css',
                    array($this->plugin_name . 'fontawesome'),
                    $this->version
                );
            }
            wp_enqueue_style('videojs-css',    plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css',  array(), $this->version);
            wp_enqueue_style('videojs-vr-css', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.css',        array(), $this->version);
            wp_enqueue_style('panellium-css',  plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css',  array(), $this->version);
            wp_enqueue_style('owl-css',        plugin_dir_url(__FILE__) . 'css/owl.carousel.css',                 array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-public.css',                array(), $this->version, 'all');
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_scripts() {
        global $wp;
        $wpvr_script_control = get_option('wpvr_script_control');

        if ( $wpvr_script_control == 'true' ) {
            $wpvr_frontend_notice   = get_option('wpvr_frontend_notice');
            $notice                 = $wpvr_frontend_notice ? sanitize_text_field( get_option('wpvr_frontend_notice_area') ) : '';
            $wpvr_script_list       = get_option('wpvr_script_list');
            $allowed_pages          = isset($wpvr_script_list) && !empty($wpvr_script_list) ? array_map('sanitize_text_field', explode(",", $wpvr_script_list)) : array();
            $allowed_pages_modified = array();
            foreach ($allowed_pages as $value) {
                $allowed_pages_modified[] = untrailingslashit($value);
            }
            $current_url = home_url(add_query_arg(isset($_GET) ? array_map('sanitize_text_field', wp_unslash($_GET)) : array(), isset($wp->request) ? sanitize_text_field($wp->request) : ''));

            foreach ($allowed_pages_modified as $value) {
                if ($value && strpos($current_url, $value) !== false) {
                    wp_enqueue_script('videojs-js',      plugin_dir_url(__FILE__) . 'js/video.js',                                        array(),                                                                   $this->version, true);
                    wp_enqueue_script('videojsvr-js',    plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js',                        array('videojs-js'),                                                       $this->version, true);
                    wp_enqueue_script('panellium-js',    plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js',                    array(),                                                                   $this->version, true);
                    wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js',                 array('panellium-js'),                                                     $this->version, true);
                    wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js',     array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'),   $this->version, true);
                    wp_enqueue_script('owl-js',          plugin_dir_url(__FILE__) . 'js/owl.carousel.js',                                  array('jquery'),                                                           $this->version, true);
                    wp_enqueue_script('jquery_cookie',   plugin_dir_url(__FILE__) . 'js/jquery.cookie.js',                                 array('jquery'),                                                           $this->version, true);
                    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpvr-public.js',                                  array('jquery', 'jquery_cookie'),                                          $this->version, true);
                    wp_localize_script('wpvr', 'wpvr_public', array(
                        'notice_active'      => $wpvr_frontend_notice,
                        'notice'             => $notice,
                        'is_pro_active'      => is_plugin_active('wpvr-pro/wpvr-pro.php'),
                        'is_license_active'  => get_option('wpvr_edd_license_status') == 'valid' ? true : false,
                        'dis_on_hover'       => get_option('dis_on_hover') === 'true' ? true : false,
                        'mobile_hotspot_tip' => get_option('wpvr_mobile_hotspot_tip') === 'true' ? true : false,
                    ));
                    break;
                }
            }
        }
        // In auto mode, scripts are enqueued from render functions via wpvr_enqueue_frontend_scripts().
	}

    /**
     * Ensure the Video.js Pannellum adapter loads after its runtime dependencies.
     */
    public function fix_script_deps() {
        $scripts = wp_scripts();

        if (
            isset($scripts->registered['panelliumvid-js'])
            && !wp_script_is('panelliumvid-js', 'done')
        ) {
            $registered = $scripts->registered['panelliumvid-js'];
            $required_dependencies = array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js');

            if (array_diff($required_dependencies, $registered->deps) || !$scripts->get_data('panelliumvid-js', 'group')) {
                $src = $registered->src;
                $ver = $registered->ver;
                wp_deregister_script('panelliumvid-js');
                wp_enqueue_script('panelliumvid-js', $src, $required_dependencies, $ver, true);
            }
        }
    }

	/**
     * Init the edit screen of the plugin post type item
     *
     * @since 8.0.0
     */
    public function public_init()
    {
        add_shortcode($this->plugin_name, array( $this->shortcode , 'wpvr_shortcode'));

    }

}

if ( ! function_exists( 'wpvr_accessibility_labels' ) ) {
    function wpvr_accessibility_labels() {
        return array(
            'tour'             => __( 'Virtual tour', 'wpvr' ),
            'instructions'     => __( 'Use the arrow keys or W, A, S, and D to look around. Use Tab to reach tour controls and hotspots.', 'wpvr' ),
            'load'             => __( 'Load virtual tour', 'wpvr' ),
            'hotspot'          => __( 'Tour hotspot', 'wpvr' ),
            'scene'            => __( 'View scene', 'wpvr' ),
            'scene_menu'       => __( 'Scene menu', 'wpvr' ),
            'previous_scene'   => __( 'Previous scene', 'wpvr' ),
            'next_scene'       => __( 'Next scene', 'wpvr' ),
            'pan_up'           => __( 'Look up', 'wpvr' ),
            'pan_down'         => __( 'Look down', 'wpvr' ),
            'pan_left'         => __( 'Look left', 'wpvr' ),
            'pan_right'        => __( 'Look right', 'wpvr' ),
            'zoom_in'          => __( 'Zoom in', 'wpvr' ),
            'zoom_out'         => __( 'Zoom out', 'wpvr' ),
            'fullscreen'       => __( 'Toggle fullscreen', 'wpvr' ),
            'home'             => __( 'Return to starting scene', 'wpvr' ),
            'orientation'      => __( 'Toggle device orientation', 'wpvr' ),
            'audio'            => __( 'Toggle tour audio', 'wpvr' ),
            'gallery'          => __( 'Toggle scene gallery', 'wpvr' ),
            'explainer'        => __( 'Open tour video', 'wpvr' ),
            'form'             => __( 'Open form', 'wpvr' ),
            'floor_plan'       => __( 'Open floor plan', 'wpvr' ),
            'floor_plan_point' => __( 'View scene from floor plan', 'wpvr' ),
            'company_info'     => __( 'Company information', 'wpvr' ),
            'close'            => __( 'Close dialog', 'wpvr' ),
            'dialog'           => __( 'Tour dialog', 'wpvr' ),
        );
    }
}

if ( ! function_exists( 'wpvr_enqueue_accessibility_script' ) ) {
    function wpvr_enqueue_accessibility_script() {
        $base = plugin_dir_url( __FILE__ );
        $ver  = defined( 'WPVR_VERSION' ) ? WPVR_VERSION : '1.0.0';

        wp_enqueue_script(
            'wpvr-accessibility',
            $base . 'js/wpvr-accessibility.js',
            array( 'jquery', 'wpvr' ),
            $ver,
            true
        );
        wp_localize_script( 'wpvr-accessibility', 'wpvrAccessibility', wpvr_accessibility_labels() );
    }
}

if ( ! function_exists( 'wpvr_maybe_enqueue_accessibility_script' ) ) {
    function wpvr_maybe_enqueue_accessibility_script() {
        if ( wp_script_is( 'wpvr', 'enqueued' ) || wp_script_is( 'panellium-js', 'enqueued' ) ) {
            wpvr_enqueue_accessibility_script();
        }
    }
}
add_action( 'wp_enqueue_scripts', 'wpvr_maybe_enqueue_accessibility_script', 100 );

if ( ! function_exists( 'wpvr_enqueue_frontend_scripts' ) ) {
    function wpvr_enqueue_frontend_scripts( $type = 'scene' ) {
        $base = plugin_dir_url( __FILE__ );
        $ver  = defined( 'WPVR_VERSION' ) ? WPVR_VERSION : '1.0.0';

        /**
         * Allow add-ons to replace or extend frontend dependencies before the
         * free handles are registered. WP VR Pro uses this for page builders
         * that render their content before wp_head(), including Breakdance.
         */
        do_action( 'wpvr_enqueue_frontend_scripts', $type );
        
        if ( ! wp_style_is( 'panellium-css', 'enqueued' ) ) {
            $fontawesome_disable = get_option( 'wpvr_fontawesome_disable' );
            if ( $fontawesome_disable != 'true' ) {
                wp_enqueue_style( 'wpvrfontawesome', $base . 'css/fontawesome/css/all.css', array(), $ver, 'all' );
                wp_enqueue_style(
                    'wpvr-icons-fix',
                    $base . 'css/fontawesome/css/icons-fix.css',
                    array( 'wpvrfontawesome' ),
                    $ver
                );
            }
            wp_enqueue_style( 'panellium-css', $base . 'lib/pannellum/src/css/pannellum.css', array(), $ver );
            wp_enqueue_style( 'owl-css',       $base . 'css/owl.carousel.css',                array(), $ver, 'all' );
            wp_enqueue_style( 'wpvr',          $base . 'css/wpvr-public.css',                 array(), $ver, 'all' );
        }

        if ( $type === 'video' && ! wp_style_is( 'videojs-vr-css', 'enqueued' ) ) {
            wp_enqueue_style( 'videojs-css',    $base . 'lib/pannellum/src/css/video-js.css', array(), $ver );
            wp_enqueue_style( 'videojs-vr-css', $base . 'lib/videojs-vr/videojs-vr.css',       array(), $ver );
        }

        wp_enqueue_script( 'panellium-js',    $base . 'lib/pannellum/src/js/pannellum.js',    array(),              $ver, true );
        wp_enqueue_script( 'panelliumlib-js', $base . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), $ver, true );
        wp_enqueue_script( 'owl-js',          $base . 'js/owl.carousel.js',                   array('jquery'),       $ver, true );
        wp_enqueue_script( 'jquery_cookie',   $base . 'js/jquery.cookie.js',                  array('jquery'),       $ver, true );
        wp_enqueue_script( 'wpvr',            $base . 'js/wpvr-public.js',                    array('jquery', 'jquery_cookie'), $ver, true );

        if ( $type === 'video' ) {
            wp_enqueue_script( 'videojs-js',      $base . 'js/video.js',                                       array(),                                                                 $ver, true );
            wp_enqueue_script( 'videojsvr-js',    $base . 'lib/videojs-vr/videojs-vr.js',                      array('videojs-js'),                                                     $ver, true );
            wp_enqueue_script( 'panelliumvid-js', $base . 'lib/pannellum/src/js/videojs-pannellum-plugin.js',  array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'), $ver, true );
        }

        wp_localize_script( 'wpvr', 'wpvr_public', array(
            'notice_active'      => get_option('wpvr_frontend_notice'),
            'notice'             => get_option('wpvr_frontend_notice') ? sanitize_text_field( get_option('wpvr_frontend_notice_area') ) : '',
            'is_pro_active'      => function_exists('is_plugin_active') ? is_plugin_active('wpvr-pro/wpvr-pro.php') : false,
            'is_license_active'  => get_option('wpvr_edd_license_status') == 'valid',
            'dis_on_hover'       => get_option('dis_on_hover') === 'true',
            'mobile_hotspot_tip' => get_option('wpvr_mobile_hotspot_tip') === 'true',
        ) );

        wpvr_enqueue_accessibility_script();
    }
}
