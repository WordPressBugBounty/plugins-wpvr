<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/classes
 */

class WPVR_Admin_Page {

	/**
	 * Instance of WPVR_Admin_Page class
	 * 
	 * @var object
	 * @since 8.0.0
	 */
	static $instance;


	private function __construct()
	{
		// Register WPVR menu
		add_action('admin_menu', array($this, 'wpvr_add_admin_pages'));
		// Display confirmation alert
		add_action('admin_footer', array($this, 'vpvr_confirmation_alert_display'));
	}


	/**
	 * Declared to overwrite magic method __clone()
	 * In order to prevent object cloning	
	 * 
	 * @return void
	 * @since 8.0.0
	 */
	private function __clone()
	{
		// Do nothing
	}


	/**
	 * Create instance of this class
	 * 
	 * @return object
	 * @since 8.0.0
	 */
	public static function getInstance()
	{
		if(!(self::$instance instanceof self)) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Admin page setup is specified in this area.
	 * 
	 * @since 8.0.0
	 */
	function wpvr_add_admin_pages() {
		add_menu_page( 'WP VR', 'WP VR', 'manage_options', 'wpvr', array( $this, 'wpvr_admin_doc'),$this->get_menu_icon(), 25);

        add_submenu_page( 'wpvr', 'WP VR', __('Get Started','wpvr'),'manage_options', 'wpvr', array( $this, 'wpvr_admin_doc'));
        remove_submenu_page('wpvr', 'wpvr');

        add_submenu_page( 'wpvr', 'WP VR', __('Tours','wpvr'),'manage_options', 'edit.php?post_type=wpvr_item', NULL);

		add_submenu_page( 'wpvr', 'WP VR', __('Add New Tour','wpvr'),'manage_options', 'post-new.php?post_type=wpvr_item', NULL);

		$status  = get_option('wpvr_edd_license_status');
        if ($status !== false && $status == 'valid') {
			/*
			* Add Analytics page
			* @since 8.5.16
			*/
			do_action('wpvr_pro_analytics_page');
		}

        do_action('wpvr_pro_before_guided_tour');
        add_submenu_page( 'wpvr', 'WP VR', __('Settings','wpvr'),'manage_options', 'wpvr-setting', array($this,'wpvr_setting_page'));

    //    add_submenu_page( 'wpvr', 'WP VR', __('Guided Tour','wpvr'),'manage_options', 'wpvr-setup-wizard', array($this,'wpvr_setup_wizard'));

		if (!is_plugin_active('wpvr-pro/wpvr-pro.php')) {
			add_submenu_page( 'wpvr', 'WP VR', __('Free vs Pro','wpvr'),'manage_options', 'wpvr', array( $this, 'wpvr_admin_doc'));
		}

        add_submenu_page( 'wpvr', 'WP VR', __('Setup Wizard','wpvr'),'manage_options', 'rex-wpvr-setup-wizard', array( $this, 'wpvr_new_setup_wizard'));
        do_action('wpvr_pro_license_page');



        if(!is_plugin_active('wpvr-pro/wpvr-pro.php')){
            add_submenu_page(
                'wpvr',
                '',
                '<span id="wpvr-gopro-submenu" class="dashicons dashicons-star-filled" style="font-size: 17px; color:#1fb3fb;"></span> ' . __( 'Go Pro', 'wpvr' ),
                'manage_options',
                esc_url( 'https://rextheme.com/wpvr/wpvr-pricing/' )
            );
        }
    }


	/**
 	 * Gets the SVG icon for menu
 	 *
 	 * @desc Gets the SVG icon for menu
 	 * @return string
 	 * @since 8.5.26
 	 */

	  private function get_menu_icon() {
		return 'data:image/svg+xml;base64,' . base64_encode(        //phpcs:ignore
			'<?xml version="1.0" encoding="utf-8"?>
			<!-- Generator: Adobe Illustrator 27.1.1, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id="mask0_332_2" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20">
			<rect width="20" height="20" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask0_332_2)">
			<path d="M16.8474 7.35132V10.2309C17.4323 10.2309 17.9047 9.58607 17.9047 8.79095C17.9047 7.99583 17.4312 7.35132 16.8474 7.35132Z" fill="#A8AAAD"/>
			<path d="M3.13659 7.33405V10.2129C2.55167 10.2129 2.07935 9.56807 2.07935 8.77331C2.07935 7.97855 2.5524 7.33441 3.13659 7.33405Z" fill="#A8AAAD"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M3.1 11.2222C3.1 12.197 3.7995 12.9903 4.65868 12.9903H15.3413C16.2013 12.9903 16.9 12.197 16.9 11.2222V10.6211C18.8101 11.0309 20 11.6091 20 12.25C20 13.4927 15.5221 14.5 10 14.5C4.47792 14.5 0 13.4927 0 12.25C0 11.6094 1.19009 11.0313 3.1 10.6216V11.2222Z" fill="#A8AAAD"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M15.1442 5.86572H4.87146C4.17138 5.86572 3.604 6.45686 3.604 7.18581V11.0243C3.604 11.7537 4.17138 12.3444 4.87146 12.3444H4.97492V12.3404C6.48877 12.0918 8.19785 11.9518 10.0075 11.9518C11.8171 11.9518 13.5269 12.0918 15.0407 12.3404V12.3444H15.1438C15.8439 12.3444 16.4116 11.7537 16.4116 11.0243V7.18581C16.412 6.45686 15.8443 5.86572 15.1442 5.86572ZM5.06396 7.9718H5.54433L5.93806 9.7792L6.39284 7.9718H6.89258L7.3214 9.76933L7.71879 7.9718H8.20208L7.57767 10.2994H7.04942L6.62973 8.6408L6.19104 10.2979L5.6657 10.3012L5.06396 7.9718ZM8.94197 9.40558H9.31632C9.89284 9.40558 10.1235 9.04549 10.1235 8.69198C10.1235 8.28875 9.85445 7.9718 9.31632 7.9718H8.49341V10.2994H8.94197V9.40558ZM9.66289 8.69198C9.66289 8.8989 9.55029 9.02904 9.29731 9.02904H8.94197V8.352H9.29731C9.55066 8.352 9.66289 8.47885 9.66289 8.69198ZM12.7335 7.9718H13.2087L12.3891 10.2994H11.8477L11.0266 7.9718H11.507L12.1219 9.8227L12.7335 7.9718ZM15.1251 8.6854C15.1251 8.29533 14.856 7.9718 14.3182 7.9718H13.4792V10.2994H13.9274V9.39242H14.1387L14.6319 10.2994H15.1507L14.6158 9.35586C14.9748 9.2557 15.1251 8.96909 15.1251 8.6854ZM13.9274 8.35858H14.3022C14.5526 8.35858 14.6641 8.48653 14.6641 8.69637C14.6641 8.90621 14.5519 9.04293 14.3022 9.04293H13.9274V8.35858Z" fill="#A8AAAD"/>
			</g>
			</svg>

			'
		);
	}
	

    /**
     * Provide setup wizard area view for the plugin
     *
     * @since 8.0.0
     */
    function wpvr_setup_wizard(){
        require_once plugin_dir_path(__FILE__) . '../partials/wpvr_setup_wizard.php';
    }
    /**
     * Provide setup wizard area view for the plugin
     *
     * @since 8.0.0
     */
    function wpvr_setting_page(){
        require_once plugin_dir_path(__FILE__) . '../partials/wpvr_setting.php';
    }

	/**
	 * Provide a admin area view for the plugin
	 * 
	 * @since 8.0.0
	 */
	function wpvr_admin_doc() {
        require_once plugin_dir_path(__FILE__) . '../partials/wpvr_documentation.php';
	}


	/**
	 * Provide license key submission or plugin activition page
	 * 
	 * @since 8.0.0
	 */
	function wpvr_pro_admin_doc() {
        require_once plugin_dir_path(__FILE__) . '../partials/wpvr_license.php';
	}
	

	/**
	 * Provide cofiramtion alert for events
	 * 
	 * @since 8.0.0
	 */
	function vpvr_confirmation_alert_display() {
		require_once plugin_dir_path(__FILE__) . '../partials/wpvr_confirmation_alert.php';
	}

    function wpvr_new_setup_wizard() {
        add_action('admin_menu', function () {
            add_dashboard_page('WPVR Setup', 'WPVR Setup', 'manage_options', 'wpvr-setup-wizard', function () {
                return '';
            });
        });
        add_action('current_screen', function () {
            ( new WPVR_Setup_Wizard() )->setup_wizard();
        }, 999);
    }

}
