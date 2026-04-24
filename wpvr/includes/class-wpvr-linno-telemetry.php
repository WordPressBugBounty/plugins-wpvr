<?php
use LinnoSDK\Telemetry\Client;

/**
 * Class WPVR_Linno_Telemetry
 *
 * Telemetry event bridge for the Linno SDK.
 *
 * Canonical events tracked:
 *  - activation/plugin_activated     — SDK lifecycle, no consent (automatic).
 *  - activation/plugin_deactivated   — SDK lifecycle, no consent (automatic).
 *  - activation/onboarding_completed — non-PII, override (track_immediate).
 *  - activation/aha_reached          — consent-gated; wizard completion fires on consent grant,
 *                                       active tour views fire via define_triggers → aha.
 *  - retention/feature_used          — consent-gated (define_triggers → feature_used).
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
        add_action( 'rex_wpvr_embadded_tour', array( $this, 'handle_embedded_tour_view' ), 10, 1 );
        add_action( 'wpvr_telemetry_consent_granted', array( $this, 'track_aha_after_consent' ) );
        add_filter( 'wpvr_telemetry_deactivation_reasons', array( $this, 'set_deactivation_reasons' ) );
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

        // Non-PII onboarding event — bypasses consent via override=true on track_immediate().
        add_action( 'wpvr_setup_wizard_completed_event', array( $this, 'track_onboarding_event' ), 10, 1 );

        // Consent-gated events via SDK trigger system.
        // activation/aha_reached  — queued when ≥60% of tours are actively viewed (recurring).
        // retention/feature_used  — queued on every tour save.
        // Both require opt-in consent.
        $self = $this;
        $wpvr_telemetry->define_triggers(
            array(
                'aha' => array(
                    'tours_actively_viewed' => array(
                        'hook'     => 'wpvr_kui_unique_views_updated',
                        'callback' => function ( $unique_views, $tour_id ) use ( $self ) {
                            return $self->build_kui_payload( $unique_views, $tour_id );
                        },
                    ),
                ),
                'feature_used' => array(
                    'tour_creation' => array(
                        'hook'     => 'wpvr_tour_settings_saved',
                        'callback' => function ( $tour_id ) use ( $self ) {
                            return $self->build_tour_creation_payload( $tour_id );
                        },
                    ),
                ),
            )
        );

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
     * Track onboarding completed event (non-PII, bypasses consent gate).
     *
     * @param string $industry Industry from setup wizard.
     * @return void
     */
    public function track_onboarding_event( $industry = '' ) {
        global $wpvr_telemetry;
        if ( ! is_object( $wpvr_telemetry ) ) {
            return;
        }
        if ( method_exists( $wpvr_telemetry, 'has_sent_event' ) && $wpvr_telemetry->has_sent_event( 'onboarding_completed' ) ) {
            return;
        }
        $wpvr_telemetry->track_immediate( 'activation/onboarding_completed', $this->build_setup_payload( $industry ), true );
        if ( method_exists( $wpvr_telemetry, 'mark_event_sent' ) ) {
            $wpvr_telemetry->mark_event_sent( 'onboarding_completed' );
        }
    }

    /**
     * Track activation/aha_reached after consent is granted.
     *
     * The wizard completion hook fires BEFORE consent is set (separate AJAX calls),
     * so aha cannot use define_triggers. Instead, this method fires once when
     * consent is granted on the Success step, if the wizard was completed.
     *
     * @return void
     */
    public function track_aha_after_consent() {
        global $wpvr_telemetry;
        if ( ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        // Only fire if the wizard was actually completed.
        if ( ! get_option( 'wpvr_wizard_onboarding_done' ) ) {
            return;
        }

        // One-shot guard — never send twice.
        if ( method_exists( $wpvr_telemetry, 'has_sent_event' ) && $wpvr_telemetry->has_sent_event( 'aha_reached_wizard_completed' ) ) {
            return;
        }

        $industry = sanitize_text_field( get_option( 'wpvr_industry_name', '' ) );

        $wpvr_telemetry->track_kui(
            'wizard_completed',
            array(
                'industry' => $industry,
                'time'     => current_time( 'mysql' ),
            )
        );

        if ( method_exists( $wpvr_telemetry, 'mark_event_sent' ) ) {
            $wpvr_telemetry->mark_event_sent( 'aha_reached_wizard_completed' );
        }
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

    /**
     * Replace generic deactivation reasons with VR-specific ones.
     *
     * @param array $reasons
     * @return array
     */
    public function set_deactivation_reasons( $reasons ) {
        return array(
            array(
                'id'          => 'interface_too_complex',
                'text'        => __( 'Setup / Interface is too complex', 'wpvr' ),
                'placeholder' => __( 'What was the biggest blocker?', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 10.6 23 9.6 22.9 8.8 22.7L8.8 22.6C9.3 22.5 9.7 22.3 10 21.9 10.3 21.6 10.4 21.3 10.4 20.9 10.8 21 11.1 21 11.5 21 16.7 21 21 16.7 21 11.5 21 6.3 16.7 2 11.5 2 6.3 2 2 6.3 2 11.5 2 13 2.3 14.3 2.9 15.6 2.7 16 2.4 16.3 2.2 16.8L2.1 17.1 2.1 17.3C2 17.5 2 17.7 2 18 0.7 16.1 0 13.9 0 11.5 0 5.1 5.1 0 11.5 0ZM11.7 11.2C13.1 11.2 14.3 11.7 15.2 12.9 15.3 13 15.4 13.1 15.4 13.2 15.4 13.4 15.3 13.8 15.2 13.8 15 13.9 14.9 13.8 14.8 13.7 14.6 13.5 14.4 13.2 14.1 13.1 13.5 12.6 12.8 12.3 12 12.2 10.7 12.1 9.5 12.3 8.4 12.8 8.3 12.8 8.2 12.8 8.1 12.8 7.9 12.8 7.8 12.4 7.8 12.2 7.7 12.1 7.8 11.9 8 11.8 8.4 11.7 8.8 11.5 9.2 11.4 10 11.2 10.9 11.1 11.7 11.2ZM16.3 5.9C17.3 5.9 18 6.6 18 7.6 18 8.5 17.3 9.3 16.3 9.3 15.4 9.3 14.7 8.5 14.7 7.6 14.7 6.6 15.4 5.9 16.3 5.9ZM8.3 5C9.2 5 9.9 5.8 9.9 6.7 9.9 7.7 9.2 8.4 8.2 8.4 7.3 8.4 6.6 7.7 6.6 6.7 6.6 5.8 7.3 5 8.3 5Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'missing_vr_feature',
                'text'        => __( 'Missing specific VR feature', 'wpvr' ),
                'placeholder' => __( 'Which feature? (Floor Plan, WebVR, etc.)', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 17"><g fill="none"><g fill="#3B86FF"><path d="M19.4 0C19.7 0.6 19.8 1.3 19.8 2 19.8 3.2 19.4 4.4 18.5 5.3 17.6 6.2 16.5 6.7 15.2 6.7 15.2 6.7 15.2 6.7 15.2 6.7 14 6.7 12.9 6.2 12 5.3 11.2 4.4 10.7 3.3 10.7 2 10.7 1.3 10.8 0.6 11.1 0L7.6 0 7 0 6.5 0 6.5 5.7C6.3 5.6 5.9 5.3 5.6 5.1 5 4.6 4.3 4.3 3.5 4.3 3.5 4.3 3.5 4.3 3.4 4.3 1.6 4.4 0 5.9 0 7.9 0 8.6 0.2 9.2 0.5 9.7 1.1 10.8 2.2 11.5 3.5 11.5 4.3 11.5 5 11.2 5.6 10.8 6 10.5 6.3 10.3 6.5 10.2L6.5 10.2 6.5 17 6.5 17 7 17 7.6 17 22.5 17C23.3 17 24 16.3 24 15.5L24 0 19.4 0Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'tour_not_displaying',
                'text'        => __( 'Tour not displaying / Error', 'wpvr' ),
                'placeholder' => __( 'Any error message or URL?', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 5.1 23 0 17.9 0 11.5 0 5.1 5.1 0 11.5 0ZM11.8 14.4C11.2 14.4 10.7 14.8 10.7 15.4 10.7 16 11.2 16.4 11.8 16.4 12.4 16.4 12.8 16 12.8 15.4 12.8 14.8 12.4 14.4 11.8 14.4ZM12 7C10.1 7 9.1 8.1 9 9.6L10.5 9.6C10.5 8.8 11.1 8.3 11.9 8.3 12.7 8.3 13.2 8.8 13.2 9.5 13.2 10.1 13 10.4 12.2 10.9 11.3 11.4 10.9 12 11 12.9L11 13.4 12.5 13.4 12.5 13C12.5 12.4 12.7 12.1 13.5 11.6 14.4 11.1 14.9 10.4 14.9 9.4 14.9 8 13.7 7 12 7Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'performance_issue',
                'text'        => __( 'Image quality / Performance issues', 'wpvr' ),
                'placeholder' => __( 'Image size or browser details help.', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M11.5 0C17.9 0 23 5.1 23 11.5 23 17.9 17.9 23 11.5 23 5.1 23 0 17.9 0 11.5 0 5.1 5.1 0 11.5 0ZM11.5 2C6.3 2 2 6.3 2 11.5 2 16.7 6.3 21 11.5 21 16.7 21 21 16.7 21 11.5 21 6.3 16.7 2 11.5 2ZM12.5 12.9L12.7 5 10.2 5 10.5 12.9 12.5 12.9ZM11.5 17.4C12.4 17.4 13 16.8 13 15.9 13 15 12.4 14.4 11.5 14.4 10.6 14.4 10 15 10 15.9 10 16.8 10.6 17.4 11.5 17.4Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'found_better_solution',
                'text'        => __( 'Found a better solution', 'wpvr' ),
                'placeholder' => __( 'Which tool? What did it do better?', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23"><g fill="none"><g fill="#3B86FF"><path d="M17.1 14L22.4 19.3C23.2 20.2 23.2 21.5 22.4 22.4 21.5 23.2 20.2 23.2 19.3 22.4L19.3 22.4 14 17.1C15.3 16.3 16.3 15.3 17.1 14L17.1 14ZM8.6 0C13.4 0 17.3 3.9 17.3 8.6 17.3 13.4 13.4 17.2 8.6 17.2 3.9 17.2 0 13.4 0 8.6 0 3.9 3.9 0 8.6 0ZM8.6 2.2C5.1 2.2 2.2 5.1 2.2 8.6 2.2 12.2 5.1 15.1 8.6 15.1 12.2 15.1 15.1 12.2 15.1 8.6 15.1 5.1 12.2 2.2 8.6 2.2ZM8.6 3.6L8.6 5C6.6 5 5 6.6 5 8.6L5 8.6 3.6 8.6C3.6 5.9 5.9 3.6 8.6 3.6L8.6 3.6Z"/></g></g></svg>',
            ),
            array(
                'id'          => 'just_testing',
                'text'        => __( 'Just testing / One-time project', 'wpvr' ),
                'placeholder' => __( 'What were you building?', 'wpvr' ),
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 6"><g fill="none"><g fill="#3B86FF"><path d="M3 0C4.7 0 6 1.3 6 3 6 4.7 4.7 6 3 6 1.3 6 0 4.7 0 3 0 1.3 1.3 0 3 0ZM12 0C13.7 0 15 1.3 15 3 15 4.7 13.7 6 12 6 10.3 6 9 4.7 9 3 9 1.3 10.3 0 12 0ZM21 0C22.7 0 24 1.3 24 3 24 4.7 22.7 6 21 6 19.3 6 18 4.7 18 3 18 1.3 19.3 0 21 0Z"/></g></g></svg>',
            ),
        );
    }

}

new WPVR_Linno_Telemetry();
