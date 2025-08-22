<?php
/**
 * Aha moment events.
 *
 * @package WpVr\Tracking
 * @since 8.5.37
 */

namespace Wpvr\Admin\Tracking\Events;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Wpvr\Admin\Tracking\AbstractEvent;

/**
 * Class AhaMomentEvents
 *
 * Tracks aha moment-related events.
 *
 * @package WpVr\Tracking\Events
 * @since 8.5.37
 */
class AhaMomentEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 8.5.37
	 */
	public function register_hooks() {
		add_action( 'rex_wpvr_embadded_tour', array( $this, 'track_embadded_tour' ), 10, 2 );
	}

    /**
     * Track an aha moment when a tour is embedded.
     *
     * @param int   $tour_id The ID of the tour post.
     * @param array $args    Additional arguments for the tour rendering.
     * @since 8.5.37
     */
    public function track_embadded_tour($tour_id, $args) {
        $tour = get_post( $tour_id );

        if ( ! $tour || $tour->post_type !== 'wpvr_item' ) {
            return;
        }

        $post_type = $tour->post_type;
        $post_type_object = get_post_type_object( $post_type );
        $post_type_name = $post_type_object ? strtolower( $post_type_object->labels->singular_name ) : 'post';

        $this->track_aha_moment( 'tour_embadded', array(
            'post_type' => $post_type_name,
            'tour_id' => $tour_id,
            'title' => $tour->post_title,
            'render_type' => $args,
            'timestamp' => current_time( 'c' ),
        ) );
    }

}
