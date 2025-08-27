<?php
/**
 * Setup moment events.
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
 * Class SetupEvents
 *
 * Tracks setup-related events.
 *
 * @package WpVr\Tracking\Events
 * @since   8.5.37
 */
class SetupEvents extends AbstractEvent {

	/**
	 * Register WordPress hooks for this event.
	 *
	 * @since 8.5.37
	 */
	public function register_hooks() {
        add_action( 'wpvr_tour_status', array( $this, 'track_tour_status' ), 10, 2 );
	}

    /**
     * Track tour status changes.
     *
     * @param int    $tour_id The ID of the tour post.
     * @param string $status  The new status of the tour post.
     * @since 8.5.37
     */
    public function track_tour_status( $tour_id, $status ) {
        $tour = get_post( $tour_id );

        if ( ! $tour || $tour->post_type !== 'wpvr_item' ) {
            return;
        }
        

        $post_type = $tour->post_type;
        $post_type_name = 'tour';

        $event_type = '';
        $previous_status = get_post_meta( $tour_id, '_previous_status', true );
        switch ( $status ) {
            case 'publish':
                if ( $previous_status === 'draft' || $previous_status === 'auto-draft' || empty( $previous_status ) ) {
                    $event_type = 'first_' . $post_type_name . '_created';
                    $existing_posts = get_posts( array(
                        'post_type' => $post_type,
                        'post_status' => 'publish',
                        'author' => $tour->post_author,
                        'posts_per_page' => 1,
                        'exclude' => array( $tour_id ),
                        'fields' => 'ids'
                    ) );

                    if ( ! empty( $existing_posts ) ) {
                        $event_type = $post_type_name . '_updated';
                    }
                } else {
                    $event_type = $post_type_name . '_updated';
                }
                break;

            case 'draft':
                if ( empty( $previous_status ) || $previous_status === 'auto-draft' ) {
                    $event_type = $post_type_name . '_published';
                } else {
                    $event_type = $post_type_name . '_updated';
                }
                break;

            default:
                $event_type = $post_type_name . '_updated';
                break;
        }

        if ( ! empty( $event_type ) ) {
            $this->track_setup_moment( $event_type, array(
                'post_title' => $tour->post_title,
                'post_type' => $post_type,
                'creation_time' => current_time( 'c' ),
            ) );
        }
        update_post_meta( $tour_id, '_previous_status', $status );
    }

}
