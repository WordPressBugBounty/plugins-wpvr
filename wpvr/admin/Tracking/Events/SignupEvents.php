<?php
/**
 * Signup moment events.
 *
 * @package WpVr\Tracking
 * @since   8.5.37
 */

namespace Wpvr\Admin\Tracking\Events;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Wpvr\Admin\Tracking\AbstractEvent;

/**
 * Class SignupEvents
 *
 * Tracks signup-related events.
 *
 * @package WpVr\Tracking\Events
 * @since  8.5.37
 */
class SignupEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 8.5.37
	 */
	public function register_hooks() {
		add_action( 'wpvr_plugin_activated', array( $this, 'track_plugin_activation' ) );
	}

	/**
	 * Track plugin activation.
	 *
	 * @since 8.5.37
	 */
	public function track_plugin_activation() {
		$this->track_signup_moment( 'plugin_activation', array(
			'plugin_version' => defined('WPVR_VERSION') ? WPVR_VERSION : 'unknown',
			'wordpress_version' => get_bloginfo( 'version' ),
			'site_url' => get_site_url(),
			'admin_email' => get_option( 'admin_email' ),
			'activation_time' => current_time( 'c' ),
		) );
	}
}
