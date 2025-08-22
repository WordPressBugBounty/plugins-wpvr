<?php
/**
 * Habit moment events.
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
 * Class HabitMomentEvents
 *
 * Tracks habit moment-related events.
 *
 * @package WpVr\Tracking\Events
 * @since   8.5.37
 */
class HabitMomentEvents extends AbstractEvent {

    /**
     * Register WordPress hooks for this event.
     *
     * @since 8.5.37
     */
    public function register_hooks() {
        add_action('current_screen', array( $this, 'track_page_view' ) );
    }

    /**
     * Track page views with user information
     *
     * @param \WP_Screen $screen The current screen object
     * @since 8.5.37
     */
    public function track_page_view($screen) {
        if ( ! is_admin() || ! $screen->id ) {
            return;
        }

        $current_user_id = get_current_user_id();
        $current_user_email = (wp_get_current_user())->user_email;
        $page_view_count = (int) get_user_meta($current_user_id, '_rex_page_view_count_' . $screen->id, true);
        $page_view_count++;
        update_user_meta($current_user_id, '_rex_page_view_count_' . $screen->id, $page_view_count);

        $this->track_habit_moment( 'page_view', array(
            'screen_id' => $screen->id,
            'screen_base' => $screen->base,
            'view_count' => $page_view_count,
            'user_email' => $current_user_email
        ) );
    }
}