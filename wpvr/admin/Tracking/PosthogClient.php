<?php
/**
 * PostHog Client for sending events.
 *
 * @package WpVr\Tracking
 * @since   8.5.37
 */

namespace Wpvr\Admin\Tracking;
use Exception;
use PostHog\PostHog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PosthogClient
 *
 * Responsible for sending event data to PostHog via HTTP POST.
 *
 * @package WpVr\Tracking
 * @since   8.5.37
 */
class PosthogClient {

	/**
	 * PostHog Project API Key.
	 *
	 * @since 8.5.37
	 * @var   string
	 */
	private const POSTHOG_API_KEY = 'phc_2RJD4rdX4y49xQYOXDAKCaw1Sm62sJxKonr9y8cVK7f';

	/**
	 * PostHog project ID.
	 *
	 * @since 8.5.37
	 * @var string
	 */
	private $project_id = '80684';

	/**
	 * PostHog API endpoint for capturing events.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const POSTHOG_HOST = 'https://eu.posthog.com';

	/**
	 * Captures an event and sends it to PostHog.
	 * Only captures events on WooCommerce Feed related pages.
	 *
	 * @since 8.5.37
	 *
	 * @param string $event_name The name of the event.
	 * @param array  $properties An array of properties for the event.
	 * @param string|null $distinct_id The distinct ID for the user. If null, uses admin email.
	 *
	 * @return bool True if the event was sent successfully, false otherwise.
	 */
	public function capture( $event_name, $properties = [], $distinct_id = 0 ) {
		if ( empty( self::POSTHOG_API_KEY ) ) {
			return false;
		}

		if ( ! $distinct_id ) {
			$distinct_id = $this->get_distinct_id();
		}

		// Add session context and plugin specific properties
		$properties = array_merge( $properties, [
			'page_url' => isset($_SERVER['REQUEST_URI']) ? esc_url( $_SERVER['REQUEST_URI'] ) : '',
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
			'plugin' => 'WP VR',
			'plugin_version' => defined('WPVR_VERSION') ? WPVR_VERSION : 'unknown',
			'wp_version' => get_bloginfo( 'version' ),
			'site_url' => get_site_url(),
		]);

		try {
			PostHog::init(
				self::POSTHOG_API_KEY,
				[
					'host' => self::POSTHOG_HOST,
				]
			);
			$response = \PostHog\PostHog::capture([
				'distinctId' => $distinct_id,
				'event' 	 => $event_name,
				'properties' => $properties,
			]);
			return $response;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Get distinct ID for PostHog tracking.
	 *
	 * @since 8.5.37
	 *
	 * @return string Distinct ID
	 */
	private function get_distinct_id(): string {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return $current_user->user_email;
		}
		return 'anonymous_' . uniqid( '', true ); // Generate a unique ID for anonymous users
	}

}