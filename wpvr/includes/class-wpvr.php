<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/includes
 */

use WPVR\Builder\DIVI\WPVR_Divi_modules;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      8.0.0
 * @package    Wpvr
 * @subpackage Wpvr/includes
 * @author     Rextheme <support@rextheme.com>
 */
class Wpvr
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      Wpvr_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The post type for the plugin
	 *
	 * @since 8.0.0
	 * @var      string $version The current version of the plugin.
	 */
	protected $post_type;

	/**
	 * Instacne of WPVR_Post_Type
	 *
	 * @var object
	 * @since 8.0.0
	 */
	protected $wpvr_post_type;

	/**
	 * Instance of Wpvr_Admin class
	 *
	 * @var object
	 * @since 8.0.0
	 */
	protected $plugin_admin;

	/**
	 * Holds instances or information about the DIVI modules used in the builder.
	 *
	 * This property may store an array of module instances or configurations, depending
	 * on how it's utilized within the class. It's used to manage and reference DIVI modules
	 * effectively within custom builder implementations.
	 *
	 * @var array
	 */
	protected $divi_modules;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function __construct()
	{
		if (defined('WPVR_VERSION')) {
			$this->version = WPVR_VERSION;
		} else {
			$this->version = '8.0.0';
		}
		$this->plugin_name = 'wpvr';
		$this->post_type   = 'wpvr_item';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		add_action('plugins_loaded', array($this, 'load_plugin'), 99);
		add_action('init', array($this, 'register_wpvr_setup_wizard'));
        add_action('admin_init', array($this, 'admin_redirects'));
	}
	/**
	 * Initializes and loads the DIVI modules into the class property.
	 *
	 * This method sets up the DIVI modules by fetching an instance of the WPVR_Divi_modules class
	 * and storing it in the divi_modules property. It ensures that the modules are ready to be
	 * used within the plugin.
	 *
	 * @since 8.4.8  This method was introduced in version 8.4.8 to streamline the initialization of DIVI modules.
	 */
	public function load_plugin()
	{
		$this->divi_modules = WPVR_Divi_modules::instance();
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpvr_Loader. Orchestrates the hooks of the plugin.
	 * - Wpvr_i18n. Defines internationalization functionality.
	 * - Wpvr_Admin. Defines all hooks for the admin area.
	 * - Wpvr_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for auto loading all files of the core plugin.
		 */
		require_once plugin_dir_path(__DIR__) . 'vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path(__DIR__) . 'includes/class-wpvr-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once plugin_dir_path(__DIR__) . 'includes/class-wpvr-i18n.php';

		$this->loader = new Wpvr_Loader();
	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpvr_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Wpvr_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
		$this->plugin_admin = new Wpvr_Admin($this->get_plugin_name(), $this->get_version(), $this->get_post_type());

		$this->loader->add_filter('plugin_action_links_' . WPVR_BASE, $this->plugin_admin, 'plugin_action_links_wpvr', 10, 4);
		$this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('publish_wpvr_item', $this->plugin_admin, 'show_review_request_markups', 99999);
		$this->loader->add_action('admin_init', $this->plugin_admin, 'wpvr_trigger_based_review_helper', 99999);
		$high_res_image = get_option('high_res_image');

		if ($high_res_image == 'true') { //phpcs:ignore
			add_filter('big_image_size_threshold', '__return_false');
		}

		$this->loader->add_action('admin_init', $this->plugin_admin, 'trigger_rollback');

        if (!is_plugin_active('wpvr-pro/wpvr-pro.php')){
            $this->loader->add_action('include_floor_plan_meta_content', $this, 'include_floor_plan_meta_content', 10, 1);
            $this->loader->add_action('include_background_tour_meta_content', $this, 'include_background_tour_meta_content', 10, 1);
            $this->loader->add_action('include_street_view_meta_content', $this, 'include_street_view_meta_content', 10, 1);
            $this->loader->add_action('include_export_meta_content', $this, 'include_export_meta_content', 10, 2);
        }

	}

    public function include_floor_plan_meta_content($post_data)
    {
        ?>

        <div class="rex-pano-tab floor-plan" id="floorPlan">
            <h6 class="title"> <?php echo __('Floor Plan Settings : ', 'wpvr-pro');?> </h6>

            <div class="content-wrapper">

                <div class="floor-plan-left">
                    <!-- bg tour on/off -->
                    <div class="single-settings inline-style">
                        <span> <?php echo __('Enable Floor Plan: ', 'wpvr-pro') ?> </span>
                        <span class="wpvr-switcher">
							<input id="wpvr_floor_plan_enabler" class="vr-switcher-check wpvr_floor_plan_enabler" name="wpvr_floor_plan_enabler" disabled type="checkbox"/>
							<label for="wpvr_floor_plan_enabler"></label>
                		</span>
                    </div>

                </div>

            </div>

        </div>

        <?php
    }

    public function include_background_tour_meta_content($post_data)
    {
        ?>

        <div class="rex-pano-tab background-tour" id="backgroundTour">
            <h6 class="title"> <?= __('Background Tour Settings : ', 'wpvr');?> </h6>

            <div class="content-wrapper">
                <div class="background-tour-left">
                    <!-- bg tour on/off -->
                    <div class="single-settings inline-style">
                        <span> <?= __('Enable background tour: ', 'wpvr') ?> </span>
                        <span class="wpvr-switcher">
                    <input id="wpvr_bg_tour_enabler" class="vr-switcher-check wpvr_bg_tour_enabler" name="wpvr_bg_tour_enabler" type="checkbox" disabled />
                    <label for="wpvr_bg_tour_enabler"></label>
                </span>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    public function include_street_view_meta_content($post_data)
    {
        ?>

        <div class="rex-pano-tab streetview" id="streetview">
            <h6 class="title"> <?= __('Embed Google Street View : ', 'wpvr-pro'); ?> </h6>
            <div class="single-settings">
                <span> <?php echo __('Enable Street View:','wpvr-pro')  ?> </span>
                <ul>
                    <li class="radio-btn">
                        <input class="styled-radio wpvrStreetView_off" id="styled-radio-sv-off" type="radio" name="wpvrStreetView" value="off" >
                        <label for="styled-radio-sv-off">Off</label>
                    </li>

                    <li class="radio-btn">
                        <input class="styled-radio wpvrStreetView_on" id="styled-radio-sv-on" type="radio" name="wpvrStreetView" value="on">
                        <label for="styled-radio-sv-on">On</label>
                    </li>
                </ul>
                <div class="field-tooltip">
                    <img loading="lazy" src="<?= WPVR_PLUGIN_DIR_URL . 'admin/icon/question.png'; ?>" alt="icon" />
                    <span><?= __('Please only add street view iframe source. For more details check documentation.', 'wpvr-pro'); ?></span>
                </div>
            </div>
        </div>
        <?php

    }

    public function include_export_meta_content($post_data ,$post)
    {
        ?>

        <div class="rex-pano-tab export" id="import">
            <h6 class="title"> <?= __('Export Tour : ', 'wpvr-pro') ?></h6>
            <a  class="vr-export"><?= __('Download', 'wpvr-pro') ?></a>
        </div>
        <?php

    }


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		if (apply_filters('is_wpvr_pro_active', false)) {
			if (class_exists('Wpvrpropublic')) {
				$plugin_public = new Wpvrpropublic($this->get_plugin_name(), $this->get_version());
			} else {
				$plugin_public = new Wpvr_Public($this->get_plugin_name(), $this->get_version());
			}
		} else {
			$plugin_public = new Wpvr_Public($this->get_plugin_name(), $this->get_version());
		}

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$plugin_public->public_init();
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    8.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     8.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     8.0.0
	 * @return    Wpvr_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     8.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}


	/**
	 * Retrieve the post type of the plugin.
	 *
	 * @since 8.0.0
	 */
	public function get_post_type()
	{
		return $this->post_type;
	}

	public function register_wpvr_setup_wizard()
	{
		if (!empty($_GET['page']) && 'rex-wpvr-setup-wizard' == sanitize_text_field($_GET['page'])) {
			add_action('admin_menu', function () {
				add_dashboard_page('WPVR Setup', 'WPVR Setup', 'manage_options', 'rex-wpvr-setup-wizard', function () {
					return '';
				});
			});
			add_action('current_screen', function () {
				(new WPVR_Setup_Wizard())->setup_wizard();
			}, 999);
		}
	}

    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
     */
    public function admin_redirects()
    {
        // Setup wizard redirect.
        if (get_transient('_wpvr_activation_redirect')) {
            $do_redirect = true;
            // On these pages, or during these events, postpone the redirect.
            if (wp_doing_ajax() || is_network_admin() || !current_user_can('manage_options')) {
                $do_redirect = false;
            }

            if ( $do_redirect ) {
                delete_transient('_wpvr_activation_redirect');
                $url = admin_url('admin.php?page=rex-wpvr-setup-wizard');
                wp_safe_redirect(  wp_sanitize_redirect( esc_url_raw( $url ) ) );
                exit;
            }
        }
    }
}
