<?php
use LinnoSDK\Telemetry\Client;

/**
 * Class WPVR_Linno_Telemetry
 *
 * Minimal telemetry event bridge for Linno SDK.
 * Tracks only: setup, first_strike, KUI trigger actions.
 *
 * @since 8.5.57
 */

class WPVR_Linno_Telemetry {

    /**
     * Linno API key.
     *
     * @var string
     */
    private $api_key;

    /**
     * Linno API secret.
     *
     * @var string
     */
    private $api_secret;

    /**
     * Prevent duplicate client initialization.
     *
     * @var bool
     */
    private $client_initialized = false;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->api_key = defined( 'WPVR_TELEMETRY_API_KEY' ) ? WPVR_TELEMETRY_API_KEY : '';
        $this->api_secret = defined( 'WPVR_TELEMETRY_API_SECRET' ) ? WPVR_TELEMETRY_API_SECRET : '';

        $this->init_client();
        add_filter( 'wpvr_telemetry_report_interval', array( $this, 'set_daily_telemetry_report_interval' ) );
        add_action( 'transition_post_status', array( $this, 'handle_first_tour_published' ), 10, 3 );
        add_action( 'rex_wpvr_embadded_tour', array( $this, 'handle_embedded_tour_view' ), 10, 1 );
    }

    /**
     * Force Linno telemetry queue interval to daily.
     *
     * @return string
     */
    public function set_daily_telemetry_report_interval() {
        return 'daily';
    }

    /**
     * Initialize Linno telemetry client and trigger mappings.
     *
     * @return void
     */
    public function init_client() {
        global $wpvr_telemetry;

        if ( $this->client_initialized ) {
            return;
        }

        if ( ! class_exists( 'LinnoSDK\\Telemetry\\Client' ) || ! defined( 'WPVR_FILE' ) ) {
            error_log( 'WPVR Telemetry: Linno Client class unavailable or WPVR_FILE not defined.' );
            return;
        }

        if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
            error_log( 'WPVR Telemetry: API key or API secret is empty. Telemetry client not initialized.' );
            return;
        }

        Client::set_text_domain( 'wpvr' );

        $wpvr_telemetry = new Client(
            array(
                'pluginFile'    => WPVR_FILE,
                'slug'          => 'wpvr',
                'pluginName'    => 'WP VR',
                'version'       => defined( 'WPVR_VERSION' ) ? WPVR_VERSION : '',
                'apiKey'        => $this->api_key,
                'apiSecret'     => $this->api_secret,
                'driver'        => 'posthog',
                'driver_config' => array(
                    'host'    => defined( 'WPVR_TELEMETRY_HOST' ) ? WPVR_TELEMETRY_HOST : 'https://eu.i.posthog.com',
                    'api_key' => $this->api_key,
                ),
            )
        );

        $wpvr_telemetry->define_triggers(
            array(
                'onboarding'   => array(
                    'hook'     => 'wpvr_setup_wizard_completed_event',
                    'callback' => array( $this, 'build_setup_payload' ),
                ),
                'aha'          => array(
                    'first_tour_published' => array(
                        'hook'     => 'wpvr_first_tour_published_event',
                        'callback' => array( $this, 'build_first_strike_payload' ),
                    ),
                ),
                'feature_used' => array(
                    'tour_creation' => array(
                        'hook'     => 'wpvr_tour_settings_saved',
                        'callback' => array( $this, 'build_tour_creation_payload' ),
                    ),
                ),
            )
        );

        // $wpvr_telemetry->init();
        $this->ensure_daily_telemetry_queue_schedule();
        $this->client_initialized = true;
    }

    /**
     * Ensure telemetry queue cron hook runs daily.
     *
     * @return void
     */
    private function ensure_daily_telemetry_queue_schedule() {
        if ( ! defined( 'WPVR_FILE' ) ) {
            return;
        }

        $slug = dirname( plugin_basename( WPVR_FILE ) );
        $hook = $slug . '_telemetry_queue_process';
        $scheduled_event = wp_get_scheduled_event( $hook );

        if ( $scheduled_event && 'daily' === $scheduled_event->schedule ) {
            return;
        }

        wp_clear_scheduled_hook( $hook );
        wp_schedule_event( time(), 'daily', $hook );
    }

    /**
     * Emit first strike action when first non-demo tour is published.
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Previous post status.
     * @param WP_Post $post       Post object.
     *
     * @return void
     */
    public function handle_first_tour_published( $new_status, $old_status, $post ) {
        if ( ! $post || 'wpvr_item' !== $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status || ! in_array( $old_status, array( 'auto-draft', 'draft', 'new', '' ), true ) ) {
            return;
        }

        $is_demo_tour = get_post_meta( $post->ID, 'wpvr_is_demo_tour', true );
        if ( '1' === $is_demo_tour ) {
            return;
        }

        $args = array(
            'post_type'      => 'wpvr_item',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => 'wpvr_is_demo_tour',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => 'wpvr_is_demo_tour',
                    'value'   => '1',
                    'compare' => '!=',
                ),
            ),
        );

        $user_tours = get_posts( $args );
        $total_user_tours = is_array( $user_tours ) ? count( $user_tours ) : 0;

        if ( 1 === $total_user_tours ) {
            do_action( 'wpvr_first_tour_published_event', $post->ID, $post->post_title );
            global $wpvr_telemetry;
            if ( is_object( $wpvr_telemetry ) && method_exists( $wpvr_telemetry, 'has_sent_event' ) && $wpvr_telemetry->has_sent_event( 'aha_reached_first_tour_published' ) ) {
                update_option( 'wpvr_first_strike_completed', true );
            }
        }
    }

    /**
     * Build setup event payload.
     *
     * @param string $industry Industry from setup wizard.
     * @return array
     */
    public function build_setup_payload( $industry = '' ) {
        return array(
            'industry' => sanitize_text_field( (string) $industry ),
            'time'     => current_time( 'mysql' ),
        );
    }

    /**
     * Build first strike payload.
     *
     * @param int    $tour_id    Tour ID.
     * @param string $tour_title Tour title.
     * @return array
     */
    public function build_first_strike_payload( $tour_id = 0, $tour_title = '' ) {
        return array(
            'tour_id'    => absint( $tour_id ),
            'tour_title' => sanitize_text_field( (string) $tour_title ),
            'time'       => current_time( 'mysql' ),
        );
    }

    /**
     * Build KUI payload.
     *
     * @param int $unique_views Unique active-tour views in 7 days.
     * @param int $tour_id       Last viewed tour ID.
     * @return array
     */
    public function build_kui_payload( $unique_views = 0, $tour_id = 0 ) {
        return array(
            'unique_views' => absint( $unique_views ),
            'tour_id'      => absint( $tour_id ),
            'window_days'  => 7,
            'time'         => current_time( 'mysql' ),
        );
    }

    /**
     * Build tour creation payload with feature usage as properties.
     *
     * Fires when tour settings are saved. Inspects the stored panodata meta to
     * determine which core features the user has configured, so all feature
     * usage is captured in a single event instead of separate ones.
     *
     * @param int $tour_id Tour (post) ID.
     * @return array
     */
    public function build_tour_creation_payload( $tour_id = 0 ) {
        $tour_id  = absint( $tour_id );
        $panodata = get_post_meta( $tour_id, 'panodata', true );

        $hotspot_editor = 'no';
        $floor_plan     = 'no';
        $tour_builder   = 'no';

        if ( is_array( $panodata ) ) {
            // Floor plan — added to panodata by pro version via filter.
            if (
                isset( $panodata['floor_plan_tour_enabler'] ) &&
                'on' === $panodata['floor_plan_tour_enabler'] &&
                ! empty( $panodata['floor_plan_attachment_url'] )
            ) {
                $floor_plan = 'yes';
            }

            // Scene list lives one level deeper inside panodata.
            $scene_list = isset( $panodata['panodata']['scene-list'] ) ? $panodata['panodata']['scene-list'] : array();

            if ( is_array( $scene_list ) ) {
                // Tour builder = more than one scene configured.
                if ( count( $scene_list ) > 1 ) {
                    $tour_builder = 'yes';
                }

                // Hotspot editor = at least one non-empty hotspot in any scene.
                foreach ( $scene_list as $scene ) {
                    if ( ! empty( $scene['hotspot-list'] ) && is_array( $scene['hotspot-list'] ) ) {
                        $hotspot_editor = 'yes';
                        break;
                    }
                }
            }
        }

        return array(
            'hotspot_editor' => $hotspot_editor,
            'floor_plan'     => $floor_plan,
            'tour_builder'   => $tour_builder,
        );
    }

    /**
     * Update rolling 7-day unique active-tour views and emit KUI trigger event.
     *
     * @param int $tour_id Tour ID.
     * @return void
     */
    public function handle_embedded_tour_view( $tour_id ) {
        $tour_id = absint( $tour_id );
        if ( $tour_id <= 0 ) {
            return;
        }

        $tour = get_post( $tour_id );
        if ( ! $tour || 'wpvr_item' !== $tour->post_type || 'publish' !== $tour->post_status ) {
            return;
        }

        $option_key = 'wpvr_kui_unique_views_7d';
        $last_kui_sent_key = 'wpvr_kui_unique_views_7d_last_sent_at';
        $window_seconds = 7 * DAY_IN_SECONDS;
        $now = time();

        $tracked_views = get_option( $option_key, array() );
        if ( ! is_array( $tracked_views ) ) {
            $tracked_views = array();
        }

        foreach ( $tracked_views as $tracked_tour_id => $timestamp ) {
            $tracked_tour_id = absint( $tracked_tour_id );
            $timestamp = absint( $timestamp );

            if (
                $tracked_tour_id <= 0 ||
                $timestamp <= 0 ||
                ( $now - $timestamp ) > $window_seconds ||
                'publish' !== get_post_status( $tracked_tour_id )
            ) {
                unset( $tracked_views[ $tracked_tour_id ] );
            }
        }

        $tracked_views[ $tour_id ] = $now;
        update_option( $option_key, $tracked_views );

        $unique_views = count( $tracked_views );
        $published_tours = wp_count_posts( 'wpvr_item' );
        $total_published_tours = 0;

        if ( is_object( $published_tours ) && isset( $published_tours->publish ) ) {
            $total_published_tours = absint( $published_tours->publish );
        }

        $active_tour_ratio = 0;
        if ( $total_published_tours > 0 ) {
            $active_tour_ratio = (float) ( $unique_views / $total_published_tours );
        }

        update_option( 'wpvr_last_active_tour_ratio', $active_tour_ratio );

        $last_kui_sent_at = absint( get_option( $last_kui_sent_key, 0 ) );
        $minimum_active_tour_ratio = 0.6;

        if ( $active_tour_ratio < $minimum_active_tour_ratio ) {
            return;
        }

        if ( $last_kui_sent_at > 0 && ( $now - $last_kui_sent_at ) < $window_seconds ) {
            return;
        }

        do_action( 'wpvr_kui_unique_views_updated', $unique_views, $tour_id );
        update_option( $last_kui_sent_key, $now );
    }
}

new WPVR_Linno_Telemetry();
