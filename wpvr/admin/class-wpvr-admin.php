<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin
 * @author     Rextheme <support@rextheme.com>
 */
class Wpvr_Admin
{

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
     * The post type of this plugin.
     *
     * @since 8.0.0
     */
    private $post_type;

    /**
     * Instance of WPVR_Admin_Page class
     * 
     * @var object
     * @since 8.0.0
     */
    private $plugin_admin_page;

    /**
     * Instance of WPVR_Setup_Meta_Box class
     * 
     * @var object
     * @since 8.0.0
     */
    private $setup_metabox;

    /**
     * Instacne of WPVR_Tour_Preview class
     * 
     * @var object
     * @since 8.0.0
     */
    private $preview_metabox;

    /**
     * Instance of Wpvr_Ajax class
     * 
     * @var object
     * @since 8.0.0
     */
    private $plugin_admin_ajax;

    /**
     * Instance of WPVR_Post_Type class
     * 
     * @var object
     * @since 8.0.0
     */
    private $wpvr_post_type;


    /**
     * Initialize the class and set its properties.
     *
     * @since      8.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    		The version of this plugin.
     * @param      string    $post_type			Post type of this plugin
     */
    public function __construct($plugin_name, $version, $post_type)
    {

        $this->plugin_name  = $plugin_name;
        $this->version         = $version;
        $this->post_type     = $post_type;

        $this->wpvr_post_type         = new WPVR_Post_Type($this->plugin_name, $this->version, $this->post_type);
        $this->plugin_admin_page     = WPVR_Admin_Page::getInstance();

        add_action('admin_init', array($this, 'set_custom_meta_box'));

        $this->plugin_admin_ajax     = new Wpvr_Ajax();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    8.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wpvr_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wpvr_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();

        if ($screen->id == "toplevel_page_wpvr" || $screen->id == "wp-vr_page_wpvr-setting") {
            wp_enqueue_style('materialize-css', plugin_dir_url(__FILE__) . 'css/materialize.min.css', array(), $this->version, 'all');
            wp_enqueue_style('materialize-icons', plugin_dir_url(__FILE__) . 'lib/materializeicon.css', array(), $this->version, 'all');
            wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-admin.css', array(), $this->version, 'all');
            wp_enqueue_style('wpvr-rtl', plugin_dir_url(__FILE__) . 'css/wpvr-admin-rtl.css', array(), $this->version, 'all');
        }

        if ($screen->id == "edit-wpvr_item") {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-admin-post-type.css', array(), $this->version, 'all');
        }

        if ($screen->id == "wpvr_item") {
            wp_enqueue_style($this->plugin_name . 'fontawesome', plugin_dir_url(__FILE__) . 'lib/fontawesome/css/all.css', array(), $this->version, 'all');
            wp_enqueue_style('icon-picker-css', plugin_dir_url(__FILE__) . 'css/jquery.fonticonpicker.min.css', array(), $this->version, 'all');
            wp_enqueue_style('icon-picker-css-theme', plugin_dir_url(__FILE__) . 'css/jquery.fonticonpicker.grey.min.css', array(), $this->version, 'all');
            wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
            wp_enqueue_style('panellium-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css', array(), true);
            wp_enqueue_style('videojs-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css', array(), true);
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-admin.css', array(), $this->version, 'all');
            wp_enqueue_style('wpvr-rtl', plugin_dir_url(__FILE__) . 'css/wpvr-admin-rtl.css', array(), $this->version, 'all');
            wp_enqueue_style('summernote', plugin_dir_url(__FILE__) . 'lib/summernote/summernote-lite.min.css', array(), $this->version, 'all');

            if (isset($_REQUEST['wpvr-guide-tour']) && $_REQUEST['wpvr-guide-tour'] == 1) {
                wp_enqueue_style($this->plugin_name . '-shepherd-css', plugin_dir_url(__FILE__) . 'lib/shepherd/css/shepherd-theme-arrows-plain-buttons.css', false, $this->version);
                wp_enqueue_style($this->plugin_name . '-tour-css', plugin_dir_url(__FILE__) . 'lib/shepherd/css/wpvr-tour-guide.min.css', false, $this->version);
            }
        }

        if ($screen->id == "wp-vr_page_wpvr-setup-wizard") {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-admin2.css', array(), $this->version, 'all');
            wp_enqueue_style('wpvr-admin2-rtl', plugin_dir_url(__FILE__) . 'css/wpvr-admin2-rtl.css', array(), $this->version, 'all');
        }

        if ($screen->id == "dashboard_page_rex-wpvr-setup-wizard") {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/style.css', array(), $this->version, 'all');
        }
    }


    /**
     * Register the JavaScript for the admin area.
     *
     * @since    8.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wpvr_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wpvr_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $wpvr_list = array();
        $wpvr_list[] = array('value' => 0, 'label' => 'None');
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'wpvr_item'
        );

        $wpvr_posts = get_posts($args);
        foreach ($wpvr_posts as $wpvr_post) {
            $title = $wpvr_post->ID . ' : ' . $wpvr_post->post_title;
			$wpvr_list[] = array( 'value'=>$wpvr_post->ID,'label'=> $title);
		}
		
		wp_enqueue_script('wp-api');
		wp_enqueue_media();

        $asset_url = apply_filters('change_asset_url', plugin_dir_url(__FILE__));

        wp_enqueue_script('wp-api');
        $adscreen = get_current_screen();
        wp_enqueue_media();
        if ($adscreen->id == "wpvr_item" || $adscreen->id == "toplevel_page_wpvr" || $adscreen->id == "wp-vr_page_wpvr-setting") {
            wp_enqueue_script('summernote', $asset_url . 'lib/summernote/summernote-lite.min.js', array('jquery'), true);
            wp_enqueue_script('wpvr-icon-picker', $asset_url . 'lib/jquery.fonticonpicker.min.js', array(), true);
            wp_enqueue_script('panellium-js', $asset_url . 'lib/pannellum/src/js/pannellum.js', array(), true);
            wp_enqueue_script('panelliumlib-js', $asset_url . 'lib/pannellum/src/js/libpannellum.js', array(), true);
            wp_enqueue_script('videojs-js', $asset_url . 'js/video.js', array('jquery'), true);
            wp_enqueue_script('panelliumvid-js', $asset_url . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array(), true);
            wp_enqueue_script('jquery-repeater', $asset_url . 'js/jquery.repeater.min.js', array('jquery'), true);
            wp_enqueue_script('icon-picker', $asset_url . 'lib/jquery.fonticonpicker.min.js', array(), true);
            wp_enqueue_script('owl', $asset_url . 'js/owl.carousel.js', array('jquery'), false);
            wp_enqueue_script($this->plugin_name, $asset_url . 'js/wpvr-admin.js', array('jquery'), $this->version, true);
            wp_localize_script($this->plugin_name, 'wpvr_localize', array(
                'WriteYourCssHere' => __('Write your css here', 'wpvr'),
                'VideoTourNotice'  => __('Turning On The Video Option Will Erase Your Virtual Tour Data. Are You Sure?', 'wpvr'),
                'StreetViewNotice'  => __('Turning On The StreetView Option Will Erase Your Virtual Tour Data. Are You Sure?', 'wpvr'),
                'AddingHotspotsOnScene'  => __('Adding Hotspots on Scene', 'wpvr'),
                'WPVR_ASSET_PATH'  => WPVR_ASSET_PATH,
            ));
            if (isset($_REQUEST['wpvr-guide-tour']) && $_REQUEST['wpvr-guide-tour'] == 1) {
                wp_enqueue_script($this->plugin_name . '-tether-js', plugin_dir_url(__FILE__) . 'lib/shepherd/tether/tether.js', $this->version, true);
                wp_enqueue_script($this->plugin_name . '-shepherd-js', plugin_dir_url(__FILE__) . 'lib/shepherd/tether-shepherd/shepherd.js', array($this->plugin_name . '-tether-js'), $this->version, true);
                wp_enqueue_script($this->plugin_name . '-tour-guide', plugin_dir_url(__FILE__) . 'js/wpvr-tour-guide.js', array('jquery', $this->plugin_name . '-tether-js'), $this->version, true);
                $tour_guide_translation = new Tour_Guide_Translation();

                wp_localize_script($this->plugin_name . '-tour-guide', 'wpvr_tour_guide_obj', array(
                    'Tour_Guide_Translation' => $tour_guide_translation->get_translatable_string(),
                ));
            }

            wp_localize_script($this->plugin_name, 'wpvr_obj', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('wpvr')
            ));
        }

        if ($adscreen->id == "toplevel_page_wpvr" || $adscreen->id == "wp-vr_page_wpvr-setting") {
            wp_enqueue_script('materialize-js', $asset_url . 'js/materialize.min.js', array('jquery'), $this->version, false);
        }
        if ($adscreen->id == "wpvr_item") {
            wp_enqueue_script($this->plugin_name . '-shortcode', plugin_dir_url(__FILE__) . 'js/wpvr-shortcode.js', array('jquery'), $this->version, true);
        }

        if ($adscreen->id == "dashboard_page_rex-wpvr-setup-wizard") {
            wp_enqueue_script(
                'wpvr-setup-wizard-manager',
                WPVR_JS_PATH . 'library/setupwizard.bundle.js',
                array('jquery'),
                $this->version,
                true
            );
        }


        wp_enqueue_script('owl-js', plugin_dir_url(__FILE__) . 'js/owl.carousel.js', array('jquery'), false);
        wp_enqueue_script('wpvr-global', $asset_url . 'js/wpvr-global.js', array('jquery'), $this->version, false);

        $admin_user = wp_get_current_user();
        $admin_name = $admin_user->display_name ?? '';

        wp_localize_script('wpvr-global', 'wpvr_global_obj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'site_url' => site_url() . '/wp-json/',
            'ajax_nonce' => wp_create_nonce('wpvr'),
            'user_information' => $this->get_logged_in_user_information(),
            'is_wpvr_active' => is_plugin_active('wpvr-pro/wpvr-pro.php'),
            'admin_name' => $admin_name,
            'url_info' => array(
                'admin_url' => admin_url(),
                'screen' => $adscreen->action,
                'url' => $_SERVER['PHP_SELF'],
                'param' => $_GET,
            ),
        ));
        wp_localize_script('wpvr-global', 'wpvr_id_options', $wpvr_list);
    }

    /**
     * Retrieve the currently logged-in user's email and name.
     *
     * @since 8.4.10
     *
     * @return array An associative array containing the logged-in user's email and name.
     */
    public function get_logged_in_user_information(): array
    {
        $admin_user = wp_get_current_user();
        return array(
            'email' => !empty( $admin_user->user_email ) ? $admin_user->user_email : '',
            'name' => !empty( $admin_user->display_name ) ? $admin_user->display_name : '',
        );
    }


    /**
     * Set Preview and Setup custom metabox of this plugin
     * 
     * @since 8.0.0
     */
    public function set_custom_meta_box()
    {
        $this->setup_metabox     = new WPVR_Setup_Meta_Box('setup', __('Setup', 'wpvr'), 'wpvr_item', 'normal', 'high');

        $this->preview_metabox  = new WPVR_Tour_Preview($this->post_type . '_builder__box', __('Tour Preview', 'wpvr'), $this->post_type, 'side', 'high');
    }


    /**
     * Plugin action links
     *
     * @param $actions || $links
     * @return array
     * @since 8.0.0
     */
    public function plugin_action_links_wpvr($actions)
    {
        $actions['get_started'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=wpvr')),
            esc_html__('Get Started', 'wpvr')
        );
        $actions['documentation'] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url('https://rextheme.com/docs-category/wp-vr/'),
            esc_html__('Documentation', 'wpvr')
        );

        if (!apply_filters('is_wpvr_pro_active', false)) {
            $actions['go-pro'] = sprintf(
                '<a href="%s" target="_blank"  style="color: #201cfe; font-weight: bold;">%s</a>',
                esc_url('https://rextheme.com/wpvr/wpvr-pricing/'),
                esc_html__('Go Pro', 'wpvr')
            );
        }
        return $actions;
    }

    /**
     * Rollback execution
     */
    public function trigger_rollback()
    {
        if (!current_user_can('update_plugins') && !current_user_can('install_plugins')) {
            return false;
        }
        $version = isset($_GET['wpvr_version']) ? sanitize_text_field(wp_unslash($_GET['wpvr_version'])) : '';
        if ($version) {
            check_admin_referer('wpvr_rollback', 'wpvr_rollback');
            $plugin_slug = 'wpvr';
            $rollback = new WPVR_Rollback(
                [
                    'version' => $version,
                    'plugin_name' => 'wpvr',
                    'plugin_slug' => $plugin_slug,
                    'package_url' => sprintf('https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, $version),
                ]
            );

            $rollback->run();
        }
    }

    /**
     * Floor plan image Display
     * Display Pro feature demo in free user
     * @return void
     */

    public function floor_plan_image_show_for_free_user()
    {
        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {

?>
            <div class="rex-pano-tab floor-plan" id="floorPlan">

                <img loading="lazy" src="<?= WPVR_PLUGIN_DIR_URL . 'images/floor-plan-demo.png' ?>" alt="icon" />
            </div>
        <?php
        }
    }
    /**
     * Background Tour image Display
     * Display Pro feature demo in free user
     * @return void
     */
    public function background_tour_image_show_for_free_user()
    {
        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {

        ?>
            <div class="rex-pano-tab background-tour" id="backgroundTour">

                <!--            <img src="--><? //= WPVR_PLUGIN_DIR_URL . 'images/floor-plan-demo.png'
                                                ?><!--" alt="icon" />-->
            </div>
        <?php
        }
    }
    /**
     * Street View image Display
     * Display Pro feature demo in free user
     * @return void
     */
    public function street_view_image_show_for_free_user()
    {
        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {

        ?>
            <div class="rex-pano-tab streetview" id="streetview">
                <!--                <img src="--><? //= WPVR_PLUGIN_DIR_URL . 'images/floor-plan-demo.png'
                                                    ?><!--" alt="icon" />-->
            </div>
        <?php
        }
    }

    public function scene_pro_image_show_for_free_user($pano_scene)
    {
        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {

        ?>
            <img loading="lazy" src="<?= WPVR_PLUGIN_DIR_URL . 'images/scene-pro-feature.png' ?>" alt="icon" />
        <?php
        }
    }

    public function empty_scene_pro_image_show_for_free_user()
    {
        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {

        ?>
            <img loading="lazy" src="<?= WPVR_PLUGIN_DIR_URL . 'images/scene-pro-feature.png' ?>" alt="icon" />
<?php
        }
    }

    public function show_review_request_markups()
    {
        $show_review_request = get_option('wpvr_feed_review_request');

        if (empty($show_review_request)) {
            $data = array(
                'show'      => true,
                'time'      => '',
                'frequency' => 'immediate',
            );
            update_option('wpvr_feed_review_request', $data);
        }
    }

    public function wpvr_trigger_based_review_helper()
    {
        $show_review_request = get_option('wpvr_feed_review_request');

        if (!empty($show_review_request) && isset($show_review_request['show']) && $show_review_request['show']) {

            if (isset($show_review_request['frequency'])) {
                if ($show_review_request['frequency'] == 'immediate') {
                    add_action('admin_notices', array($this, 'wpvr_generate_review_request_section'));
                } elseif ($show_review_request['frequency'] == 'one_week') {
                    $last_shown_date = $show_review_request['time'];
                    $current_date    = time();
                    $current_date    = new DateTime(date('Y-m-d', $current_date));
                    $last_shown_date = new DateTime(date('Y-m-d', $last_shown_date));
                    $date_diff       = $last_shown_date->diff($current_date);

                    if ($date_diff->d > 7) {
                        add_action('admin_notices', array($this, 'wpvr_generate_review_request_section'));
                    }
                }
            }
        }
    }
    public function wpvr_generate_review_request_section()
    {

        $screen                     = get_current_screen();
        $promotional_notice_pages   = ['dashboard', 'plugins', 'wpvr_item', 'edit-wpvr_item', 'toplevel_page_wpvr', 'wp-vr_page_wpvr-setup-wizard'];
        if (!in_array($screen->id, $promotional_notice_pages)) {
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'partials/wpvr-review-request-body-content.php';
    }
}