<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Responsible for managing Shortcode on frontend
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/public/classes
 */

class WPVR_Shortcode {

    /**
	 * The ID of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

    /**
     * Post type for wpvr
     * 
     * @var string
     * @since 8.0.0
     */
    private $post_type = 'wpvr_item';

    /**
     * Instance of WPVR_StreetView class
     * 
     * @var object
     * @since 8.0.0
     */
    private $streetview;

    /**
     * Instance of WPVR_Video class
     * 
     * @var object
     * @since 8.0.0
     */
    private $video;

    /**
     * Instance of WPVR_Scene class
     * 
     * @var object
     * @since 8.0.0
     */
    private $scene;

    function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;
        $this->streetview = new WPVR_StreetView();
        $this->video = new WPVR_Video();
        $this->scene = new WPVR_Scene();
    }

    /**
     * Shortcode output for the plugin
     * 
     * @param array $atts
     *
     * @return string
     * @since 8.0.0
     */
    public function wpvr_shortcode($atts)
    {

        extract(
            shortcode_atts(
                array(
                    'id' => 0,
                    'slug' => '',
                    'width' => null,
                    'height' => null,
                    'mobile_height' => null,
                    'radius' => null
                ),
                $atts
            )
        );
        $id = esc_attr($id);
        $slug = esc_attr($slug);
        $width = esc_attr($width);
        $height = esc_attr($height);
        $mobile_height = esc_attr($mobile_height);
        $radius = esc_attr($radius);
        if (!$id) {
            $obj = get_page_by_path($slug, OBJECT, $this->post_type);
            if ($obj) {
                $id = $obj->ID;
            } else {
                return __('Invalid Wpvr slug attribute', $this->plugin_name);
            }
        }
        
        do_action('rex_wpvr_embadded_tour', $id);
    
        if (empty($mobile_height)) {
            $mobile_height = "300px";
        }
        $get_post = get_post_status($id);

        if (empty($get_post)) {
            return wp_kses(
                __('Oops! It seems that the entered tour doesn\'t exist. Please enter correct shortcode.<br>', 'wpvr'),
                ['br' => []]
            );
        }

        if ( $get_post !== 'publish' ) {
            return esc_html__('Oops! It seems like this post isn\'t published yet. Stay tuned for updates!', 'wpvr') ;
        }
        if( post_password_required(  $id ) ){
            return get_the_password_form();
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
                    $customer = rcp_get_customer_by_user_id($user_id);

                    if ($customer) { // Ensure customer exists
                        $user_rcp_memberships = $customer->get_memberships(); // Use method on customer object

                        if (!empty($user_rcp_memberships)) { // Ensure memberships exist
                            foreach ($user_rcp_memberships as $membership) {
                                $rcp_access_level = [$membership->get_object_id()];

                                if (array_intersect($allowed_membership_levels, $rcp_access_level)) {
                                    $allowed_access = true;
                                    break;
                                }
                            }
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

        $postdata = get_post_meta($id, 'panodata', true);
        $panoid = 'pano'.$id;

        if (isset($postdata['streetviewdata'])){
            $html = $this->streetview->render_streetview_shortcode($postdata, $width, $height);
            return $html;
        }


        if (isset($postdata['vidid'])) {
            $html = $this->video->render_video_shortcode($postdata, $id, $width, $height, $radius);
            return $html;
        }

        $html = $this->scene->render_scene_shortcode($postdata, $panoid, $id, $radius, $width, $height, $mobile_height);
        return $html;
    }
}