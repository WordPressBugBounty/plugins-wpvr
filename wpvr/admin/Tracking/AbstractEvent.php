<?php
/**
 * Abstract class for all event types.
 *
 * @package WpVr\Tracking
 * @since   8.5.37
 */

namespace Wpvr\Admin\Tracking;

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractEvent
 *
 * Base class for all tracking events.
 *
 * @package WpVr\Tracking
 * @since   8.5.37
 */
abstract class AbstractEvent {

	/**
	 * PostHog client instance.
	 *
	 * @var PosthogClient
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param PosthogClient $client PostHog client.
	 * @since 8.5.37
	 */
	public function __construct( $client ) {
		$this->client = $client;
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks for this event.
	 *
	 * Each child class must implement this to define when events are triggered.
	 * @since 8.5.37
	 */
	abstract public function register_hooks();

	/**
	 * Get common properties for all events.
	 *
	 * @return array Common properties.
	 * @since 8.5.37
	 */
	protected function get_common_properties() {
		$properties = array(
			'timestamp' => current_time( 'c' ),
			'plugin' => 'wpvr',
			'version' => defined('WPVR_VERSION') ? WPVR_VERSION : 'unknown',
		);

		return $properties;
	}

	/**
	 * Track signup moment event.
	 *
	 * @param string $action   The specific action that triggered this event.
	 * @param array  $metadata Additional metadata for the event.
	 * @since 8.5.37
	 */
	protected function track_signup_moment( $action, $metadata = array() ) {
		$properties = array_merge(
			$this->get_common_properties(),
			array(
				'funnel_type' => 'signup',
			),
			$metadata
		);

		$this->client->capture( $action, $properties );
	}

	/**
	 * Track setup moment event.
	 *
	 * @param string $action   The specific action that triggered this event.
	 * @param array  $metadata Additional metadata for the event.
	 * @since 8.5.37
	 */
	protected function track_setup_moment( $action, $metadata = array() ) {
		$properties = array_merge(
			$this->get_common_properties(),
			array(
				'funnel_type' => 'setup',
			),
			$metadata
		);

		$this->client->capture( $action, $properties );
	}

	/**
	 * Track aha moment event.
	 *
	 * @param string $action   The specific action that triggered this event.
	 * @param array  $metadata Additional metadata for the event.
	 * @since 8.5.37
	 */
	protected function track_aha_moment( $action, $metadata = array() ) {
		$properties = array_merge(
			$this->get_common_properties(),
			array(
				'funnel_type' => 'aha',
			),
			$metadata
		);

		$this->client->capture( $action, $properties );
	}

    /**
     * Track habit moment event.
     *
     * @param string $action   The specific action that triggered this event.
     * @param array  $metadata Additional metadata for the event.
     * @since 8.5.37
     */
    protected function track_habit_moment( $action, $metadata = array() ) {
        $properties = array_merge(
            $this->get_common_properties(),
            array(
                'funnel_type' => 'habit',
            ),
            $metadata
        );

        $this->client->capture( $action, $properties );
    }
}
