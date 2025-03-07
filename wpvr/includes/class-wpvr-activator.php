<?php
/**
 * Fired during plugin activation
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      8.0.0
 * @package    Wpvr
 * @subpackage Wpvr/includes
 * @author     Rextheme <support@rextheme.com>
 */
class Wpvr_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    8.0.0
	 */
	public static function activate() {
        self::set_wpvr_activation_transients();
        self::update_wpvr_version();
        self::update_installed_time();
        if (!get_option('wpvr_plugin_installed')) {
            global $wpdb;
            $existing_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpvr_item'");
            if ($existing_items == 0) {
                self::create_wpvr_sample_tour();
            }
            update_option('wpvr_plugin_installed', time());
        }

	}


    /**
     * Update WPVR version to current.
     *
     * @since 1.0.0
     */
    private static function update_wpvr_version()
    {
        update_site_option('wpvr_version', WPVR_VERSION);
    }




    /**
     * See if we need to redirect the admin to setup wizard or not.
     *
     * @since 1.0.0
     */
    private static function set_wpvr_activation_transients()
    {
        if (self::is_new_install()) {
            set_transient('_wpvr_activation_redirect', 1, 30);
        }
    }

    /**
     * Brand new install of WPVR
     *
     * @return bool
     * @since  1.0.0
     */
    public static function is_new_install()
    {
        return is_null(get_site_option('wpvr_version', null));
    }



    /**
     * Retrieve the time when WPVR is installed
     *
     * @return int|mixed|void
     * @since  2.0.0
     */
    public static function get_installed_time() {
        $installed_time = get_option( 'wpvr_installed_time' );
        if ( ! $installed_time ) {
            $installed_time = time();
            update_site_option( 'wpvr_installed_time', $installed_time );
        }
        return $installed_time;
    }

    /**
     * Update the time when WPVR is installed
     *
     * @since  2.0.0
     */
    public static function update_installed_time() {
        self::get_installed_time();
    }

    /**
     * Create a sample tour for the user
     *
     * @since  8.5.21
     */
    public static function create_wpvr_sample_tour(){
        require_once WPVR_PLUGIN_DIR_PATH . 'admin/classes/class-wpvr-sample-tour.php';
        $sample_tour_instance = new WPVR_Sample_Tour();
        $sample_tour_instance->create_sample_tour();
    }
}
