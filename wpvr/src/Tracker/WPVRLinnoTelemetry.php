<?php
namespace RexTheme\WPVR\Tracker;

use LinnoSDK\Telemetry\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Telemetry event bridge for the Linno SDK (PostHog driver).
 *
 * Canonical events tracked (consent-gated unless SDK lifecycle):
 *  - activation/plugin_activated     — SDK lifecycle (automatic).
 *  - activation/plugin_deactivated   — SDK lifecycle + wpvr_deactivation_payload context.
 *  - activation/onboarding_completed — Consent-gated immediate send on wizard publish.
 *  - activation/tour_created         — Consent-gated immediate send on first real publish.
 *  - retention/feature_used          — Consent-gated on rex_wpvr_tour_saved & wpvr_tour_settings_saved,
 *                                      plus standalone export_tour and high_res_image_enabled actions.
 *  - wizard/*, editor/*, tour/*      — Consent-gated funnel & friction events.
 *
 * @package RexTheme\WPVR
 * @since 8.5.57
 */
class WPVRLinnoTelemetry {

    /**
     * Post meta key storing whether the creation event has already been emitted.
     *
     * @var string
     */
    const TOUR_CREATED_TRACKED_META_KEY = '_wpvr_linno_tour_created_tracked';

    /**
     * Option key storing local non-PII journey milestone progression.
     *
     * @var string
     */
    const MILESTONES_OPTION_KEY = 'wpvr_journey_milestones';

    /**
     * Allowlisted client-side funnel and friction event names.
     *
     * @var string[]
     */
    const ALLOWED_CLIENT_EVENTS = array(
        'wizard/started',
        'wizard/step_completed',
        'wizard/skipped',
        'wizard/error',
        'editor/opened',
        'editor/onboarding_exited',
        'editor/error',
        'editor/paywall_hit',
        'tour/shortcode_copied',
    );

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
     * Singleton instance for static access from REST controllers.
     *
     * @since 9.3.2
     * @var WPVRLinnoTelemetry|null
     */
    private static $instance = null;

    /**
     * Constructor.
     */
    public function __construct() {
        self::$instance   = $this;
        $this->api_key    = defined( 'WPVR_TELEMETRY_API_KEY' ) ? WPVR_TELEMETRY_API_KEY : '';
        $this->api_secret = defined( 'WPVR_TELEMETRY_API_SECRET' ) ? WPVR_TELEMETRY_API_SECRET : '';

        $this->init_client();
        add_action( 'transition_post_status', array( $this, 'track_tour_created_event' ), 10, 3 );
        add_filter( 'wpvr_telemetry_report_interval', array( $this, 'set_daily_telemetry_report_interval' ) );
        add_action( 'rex_wpvr_embadded_tour', array( $this, 'handle_embedded_tour_view' ), 10, 1 );
        add_action( 'wpvr_plugin_deactivated', array( $this, 'flush_queue_on_deactivation' ) );
        add_filter( 'wpvr_deactivation_payload', array( $this, 'enrich_deactivation_payload' ) );

        // Feature usage across both New UI (REST) and Classic UI (AJAX).
        add_action( 'rex_wpvr_tour_saved', array( $this, 'track_feature_used_on_save' ), 10, 1 );
        add_action( 'wpvr_tour_settings_saved', array( $this, 'track_feature_used_on_save' ), 10, 1 );

        // Funnel, friction, and standalone feature hooks.
        add_action( 'wpvr_tour_draft_created', array( $this, 'track_draft_created' ), 10, 2 );
        add_action( 'wpvr_tour_deleted', array( $this, 'track_tour_deleted' ), 10, 1 );
        add_action( 'wpvr_ui_mode_switched', array( $this, 'track_ui_mode_switched' ), 10, 1 );
        add_action( 'wpvr_tour_exported', array( $this, 'track_tour_exported' ), 10, 1 );
        add_action( 'wpvr_large_image_handler_enabled', array( $this, 'track_large_image_handler_enabled' ) );
    }

    /**
     * Get the active telemetry bridge instance.
     *
     * @since 9.3.2
     * @return WPVRLinnoTelemetry|null
     */
    public static function get_instance() {
        return self::$instance;
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
     * Initialize Linno telemetry client and alias the SDK global lookup key.
     *
     * @return void
     */
    public function init_client() {
        global $wpvr_telemetry, $wpvr_telemetry_client;

        if ( $this->client_initialized ) {
            return;
        }

        if ( ! class_exists( 'LinnoSDK\\Telemetry\\Client' ) || ! defined( 'WPVR_FILE' ) ) {
            return;
        }

        if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
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

        // Alias so LinnoSDK Client::getInstance() / linno_telemetry() resolves cleanly.
        $wpvr_telemetry_client = $wpvr_telemetry;

        add_action( 'wpvr_setup_wizard_completed_event', array( $this, 'track_onboarding_event' ), 10, 1 );

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

        $slug            = dirname( plugin_basename( WPVR_FILE ) );
        $hook            = $slug . '_telemetry_queue_process';
        $scheduled_event = wp_get_scheduled_event( $hook );

        if ( $scheduled_event && 'daily' === $scheduled_event->schedule ) {
            return;
        }

        wp_clear_scheduled_hook( $hook );
        wp_schedule_event( time(), 'daily', $hook );
    }

    /**
     * Determine whether user telemetry consent is currently granted.
     *
     * @since 9.3.2
     * @return bool
     */
    public function is_consent_enabled(): bool {
        global $wpvr_telemetry;

        if ( ! is_object( $wpvr_telemetry ) || ! method_exists( $wpvr_telemetry, 'get_optin_state' ) ) {
            return false;
        }

        return 'yes' === $wpvr_telemetry->get_optin_state();
    }

    /**
     * Flush pending queued events before Client::deactivate() clears the queue.
     *
     * @since 9.3.2
     * @return void
     */
    public function flush_queue_on_deactivation() {
        global $wpvr_telemetry;

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        if ( method_exists( $wpvr_telemetry, 'process_queue' ) ) {
            $wpvr_telemetry->process_queue();
        }
    }

    /**
     * Record a non-PII local journey milestone for deactivation context.
     *
     * @since 9.3.2
     * @param string $milestone Milestone identifier.
     * @return void
     */
    public function record_local_milestone( string $milestone ) {
        $milestone = sanitize_key( $milestone );
        if ( '' === $milestone ) {
            return;
        }

        $data = get_option( self::MILESTONES_OPTION_KEY, array() );
        if ( ! is_array( $data ) ) {
            $data = array();
        }

        $data['last_milestone'] = $milestone;
        if ( 'switched_to_classic' === $milestone ) {
            $data['switched_to_classic'] = true;
        }

        update_option( self::MILESTONES_OPTION_KEY, $data, false );
    }

    /**
     * Attach non-PII journey summary context to deactivation events.
     *
     * @since 9.3.2
     * @param array<string,mixed> $payload Existing deactivation payload.
     * @return array<string,mixed>
     */
    public function enrich_deactivation_payload( array $payload ): array {
        /**
         * Filter whether to attach local journey context to deactivation payload.
         *
         * @since 9.3.2
         * @param bool $include Whether to include journey context.
         */
        if ( ! apply_filters( 'wpvr_telemetry_include_deactivation_context', true ) ) {
            return $payload;
        }

        $milestones     = get_option( self::MILESTONES_OPTION_KEY, array() );
        $milestones     = is_array( $milestones ) ? $milestones : array();
        $installed_time = absint( get_option( 'wpvr_installed_time', 0 ) );
        $counts         = wp_count_posts( 'wpvr_item' );
        $published      = is_object( $counts ) && isset( $counts->publish ) ? absint( $counts->publish ) : 0;
        $drafts         = is_object( $counts ) && isset( $counts->draft ) ? absint( $counts->draft ) : 0;

        $payload['last_milestone']      = sanitize_key( (string) ( $milestones['last_milestone'] ?? 'activated' ) );
        $payload['minutes_since_install'] = $installed_time > 0 ? (int) floor( max( 0, time() - $installed_time ) / 60 ) : 0;
        $payload['ui_mode']             = sanitize_key( (string) get_option( 'wpvr_ui_mode', 'latest' ) );
        $payload['tours_total']         = $published + $drafts;
        $payload['tours_published']     = $published;
        $payload['wizard_completed']    = get_option( 'wpvr_wizard_onboarding_done' ) ? 'yes' : 'no';
        $payload['switched_to_classic'] = ! empty( $milestones['switched_to_classic'] ) ? 'yes' : 'no';

        return $payload;
    }

    /**
     * Dispatch an allowlisted client-side funnel or friction event if consented.
     *
     * @since 9.3.2
     * @param string              $event_name Event name from ALLOWED_CLIENT_EVENTS.
     * @param array<string,mixed> $properties Raw event properties.
     * @return bool True if allowlisted and processed, false otherwise.
     */
    public function track_client_event( string $event_name, array $properties = array() ): bool {
        global $wpvr_telemetry;

        if ( ! in_array( $event_name, self::ALLOWED_CLIENT_EVENTS, true ) ) {
            return false;
        }

        $sanitized = $this->sanitize_flat_properties( $properties );

        if ( 'wizard/started' === $event_name ) {
            $this->record_local_milestone( 'wizard_started' );
        } elseif ( 'wizard/step_completed' === $event_name && ! empty( $sanitized['step_id'] ) ) {
            $this->record_local_milestone( 'wizard_' . $sanitized['step_id'] );
        } elseif ( 'wizard/skipped' === $event_name ) {
            $this->record_local_milestone( 'wizard_skipped' );
        } elseif ( 'editor/opened' === $event_name ) {
            $this->record_local_milestone( 'editor_opened' );
        } elseif ( 'tour/shortcode_copied' === $event_name ) {
            $this->record_local_milestone( 'shortcode_copied' );
        }

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return true;
        }

        $sanitized['ui_mode'] = sanitize_key( (string) get_option( 'wpvr_ui_mode', 'latest' ) );
        $sanitized['is_pro']  = function_exists( 'wpvr_is_pro_active' ) && wpvr_is_pro_active() ? 'yes' : 'no';
        $wpvr_telemetry->track_immediate( $event_name, $sanitized, false );

        return true;
    }

    /**
     * Sanitize flat scalar properties for client-submitted telemetry events.
     *
     * @since 9.3.2
     * @param array<string,mixed> $properties Raw properties.
     * @return array<string,mixed>
     */
    private function sanitize_flat_properties( array $properties ): array {
        $sanitized = array();
        foreach ( $properties as $key => $value ) {
            $clean_key = sanitize_key( (string) $key );
            if ( '' === $clean_key ) {
                continue;
            }
            if ( is_bool( $value ) ) {
                $sanitized[ $clean_key ] = $value ? 'yes' : 'no';
            } elseif ( is_int( $value ) || is_float( $value ) ) {
                $sanitized[ $clean_key ] = $value;
            } elseif ( is_scalar( $value ) ) {
                $sanitized[ $clean_key ] = sanitize_text_field( (string) $value );
            }
        }
        return $sanitized;
    }

    /**
     * Build setup event payload.
     *
     * @param string $industry Industry from setup wizard.
     * @return array<string,mixed>
     */
    public function build_setup_payload( $industry = '' ) {
        return array(
            'industry' => sanitize_text_field( (string) $industry ),
            'time'     => current_time( 'mysql' ),
        );
    }

    /**
     * Inspect saved panodata meta and report all Free and Pro feature usage.
     *
     * @param int $tour_id Tour (post) ID.
     * @return array<string,mixed>
     */
    public function build_tour_creation_payload( $tour_id = 0 ) {
        $tour_id  = absint( $tour_id );
        $panodata = get_post_meta( $tour_id, 'panodata', true );
        $panodata = is_array( $panodata ) ? $panodata : array();

        $is_on = static function ( $val ) {
            return in_array( $val, array( true, 1, '1', 'on', 'true' ), true );
        };

        $tour_type    = isset( $panodata['tour-type'] ) ? sanitize_key( (string) $panodata['tour-type'] ) : 'image';
        $video_source = 'none';
        if ( 'video' === $tour_type && ! empty( $panodata['vidurl'] ) ) {
            $video_source = class_exists( 'RexTheme\\WPVR\\Content\\VideoSource' )
                ? \RexTheme\WPVR\Content\VideoSource::player_type( (string) $panodata['vidurl'], (string) ( $panodata['video-source-type'] ?? '' ) )
                : sanitize_key( (string) ( $panodata['video-source-type'] ?? 'selfhost' ) );
        }

        $scene_list               = isset( $panodata['panodata']['scene-list'] ) && is_array( $panodata['panodata']['scene-list'] )
            ? $panodata['panodata']['scene-list']
            : array();
        $scene_count              = count( $scene_list );
        $has_cubemap              = false;
        $hotspot_count            = 0;
        $hotspot_types            = array();
        $has_text_overlay         = false;
        $has_custom_hotspot_style = false;

        foreach ( $scene_list as $scene ) {
            if ( ! is_array( $scene ) ) {
                continue;
            }
            if ( isset( $scene['scene-type'] ) && 'cubemap' === $scene['scene-type'] ) {
                $has_cubemap = true;
            }
            if ( ! empty( $scene['text-overlays'] ) && is_array( $scene['text-overlays'] ) ) {
                $has_text_overlay = true;
            }
            if ( ! empty( $scene['hotspot-list'] ) && is_array( $scene['hotspot-list'] ) ) {
                foreach ( $scene['hotspot-list'] as $hotspot ) {
                    if ( ! is_array( $hotspot ) ) {
                        continue;
                    }
                    ++$hotspot_count;
                    if ( ! empty( $hotspot['hotspot-type'] ) ) {
                        $hotspot_types[] = sanitize_key( (string) $hotspot['hotspot-type'] );
                    }
                    if (
                        ! empty( $hotspot['hotspot-customclass'] )
                        || ( ! empty( $hotspot['hotspot-customclass-pro'] ) && 'none' !== $hotspot['hotspot-customclass-pro'] )
                        || ( ! empty( $hotspot['hotspot-shape'] ) && 'round' !== $hotspot['hotspot-shape'] )
                        || ( isset( $hotspot['hotspot-border'] ) && $is_on( $hotspot['hotspot-border'] ) )
                    ) {
                        $has_custom_hotspot_style = true;
                    }
                }
            }
        }

        $floor_plan_enabled = (
            ( isset( $panodata['floor_plan_tour_enabler'] ) && $is_on( $panodata['floor_plan_tour_enabler'] ) && ! empty( $panodata['floor_plan_attachment_url'] ) )
            || ( isset( $panodata['floorplan-enabled'] ) && $is_on( $panodata['floorplan-enabled'] ) && ! empty( $panodata['floorplan-image'] ) )
        );

        $custom_controls = false;
        if ( isset( $panodata['customcontrol'] ) && is_array( $panodata['customcontrol'] ) ) {
            foreach ( $panodata['customcontrol'] as $ctrl_val ) {
                if ( $is_on( $ctrl_val ) ) {
                    $custom_controls = true;
                    break;
                }
            }
        }

        $tour_layout = 'default';
        if ( isset( $panodata['tourLayout'] ) ) {
            $tour_layout = is_array( $panodata['tourLayout'] )
                ? sanitize_key( (string) ( $panodata['tourLayout']['layout'] ?? 'default' ) )
                : sanitize_key( (string) $panodata['tourLayout'] );
        }

        $bool_flags = array(
            'autoload'         => isset( $panodata['autoLoad'] ) && $is_on( $panodata['autoLoad'] ),
            'autorotation'     => ( isset( $panodata['autorotate-enabled'] ) && $is_on( $panodata['autorotate-enabled'] ) ) || ! empty( $panodata['autoRotate'] ),
            'scene_fade'       => isset( $panodata['scenefadeduration'] ) && floatval( $panodata['scenefadeduration'] ) > 0,
            'social_share'     => isset( $panodata['wpvr_social_share'] ) && $is_on( $panodata['wpvr_social_share'] ),
            'floor_plan'       => $floor_plan_enabled,
            'background_tour'  => isset( $panodata['bg_tour_enabler'] ) && $is_on( $panodata['bg_tour_enabler'] ),
            'vr_gallery'       => isset( $panodata['vrgallery'] ) && $is_on( $panodata['vrgallery'] ),
            'scene_navigation' => isset( $panodata['scene_navigation'] ) && $is_on( $panodata['scene_navigation'] ),
            'scene_animation'  => isset( $panodata['sceneAnimation'] ) && $is_on( $panodata['sceneAnimation'] ),
            'gyroscope'        => isset( $panodata['gyro'] ) && $is_on( $panodata['gyro'] ),
            'compass'          => isset( $panodata['compass'] ) && $is_on( $panodata['compass'] ),
            'bg_music'         => isset( $panodata['bg_music'] ) && $is_on( $panodata['bg_music'] ),
            'company_logo'     => isset( $panodata['cpLogoSwitch'] ) && $is_on( $panodata['cpLogoSwitch'] ),
            'explainer_video'  => isset( $panodata['explainerSwitch'] ) && $is_on( $panodata['explainerSwitch'] ),
            'generic_form'     => isset( $panodata['genericform'] ) && $is_on( $panodata['genericform'] ),
            'call_to_action'   => isset( $panodata['calltoaction'] ) && $is_on( $panodata['calltoaction'] ),
            'custom_css'       => isset( $panodata['customcss_enable'] ) && $is_on( $panodata['customcss_enable'] ),
            'custom_controls'  => $custom_controls,
        );

        $payload = array(
            'feature'                  => 'tour_save',
            'tour_id'                  => $tour_id,
            'tour_type'                => $tour_type,
            'video_source'             => $video_source,
            'has_cubemap'              => $has_cubemap ? 'yes' : 'no',
            'scene_count'              => $scene_count,
            'tour_builder'             => $scene_count > 1 ? 'yes' : 'no',
            'hotspot_editor'           => $hotspot_count > 0 ? 'yes' : 'no',
            'hotspot_count'            => $hotspot_count,
            'hotspot_types'            => implode( ',', array_values( array_unique( $hotspot_types ) ) ),
            'has_text_overlay'         => $has_text_overlay ? 'yes' : 'no',
            'has_custom_hotspot_style' => $has_custom_hotspot_style ? 'yes' : 'no',
            'tour_layout'              => $tour_layout ?: 'default',
            'ui_mode'                  => sanitize_key( (string) get_option( 'wpvr_ui_mode', 'latest' ) ),
            'is_pro'                   => function_exists( 'wpvr_is_pro_active' ) && wpvr_is_pro_active() ? 'yes' : 'no',
        );

        foreach ( $bool_flags as $key => $enabled ) {
            $payload[ $key ] = $enabled ? 'yes' : 'no';
        }

        return $payload;
    }

    /**
     * Track retention/feature_used whenever a tour is saved in New or Classic UI.
     *
     * @since 9.3.2
     * @param int $tour_id Tour (post) ID.
     * @return void
     */
    public function track_feature_used_on_save( $tour_id ) {
        global $wpvr_telemetry;

        $tour_id = absint( $tour_id );
        if ( $tour_id <= 0 ) {
            return;
        }

        $this->record_local_milestone( 'tour_saved' );

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $wpvr_telemetry->track( 'retention/feature_used', $this->build_tour_creation_payload( $tour_id ) );
    }

    /**
     * Track tour draft creation from the modal or REST API.
     *
     * @since 9.3.2
     * @param int    $tour_id   Tour post ID.
     * @param string $tour_type Tour type (image|video|street-view).
     * @return void
     */
    public function track_draft_created( $tour_id, $tour_type = 'image' ) {
        global $wpvr_telemetry;

        $this->record_local_milestone( 'draft_created' );

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $wpvr_telemetry->track_immediate(
            'tour/draft_created',
            array(
                'tour_id'   => absint( $tour_id ),
                'tour_type' => sanitize_key( (string) $tour_type ),
                'ui_mode'   => sanitize_key( (string) get_option( 'wpvr_ui_mode', 'latest' ) ),
            ),
            false
        );
    }

    /**
     * Track tour trash/deletion.
     *
     * @since 9.3.2
     * @param int $tour_id Deleted tour ID.
     * @return void
     */
    public function track_tour_deleted( $tour_id ) {
        global $wpvr_telemetry;

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $wpvr_telemetry->track_immediate(
            'tour/deleted',
            array(
                'tour_id' => absint( $tour_id ),
                'is_demo' => $this->is_seeded_tour( absint( $tour_id ) ) ? 'yes' : 'no',
            ),
            false
        );
    }

    /**
     * Track switching from the New UI back to Classic UI.
     *
     * @since 9.3.2
     * @param string $mode Target UI mode ('legacy'|'latest').
     * @return void
     */
    public function track_ui_mode_switched( $mode ) {
        global $wpvr_telemetry;

        $mode = sanitize_key( (string) $mode );
        if ( 'legacy' === $mode ) {
            $this->record_local_milestone( 'switched_to_classic' );
        }

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        if ( 'legacy' === $mode ) {
            $wpvr_telemetry->track_immediate(
                'editor/switched_to_classic_ui',
                array(
                    'target_mode' => $mode,
                ),
                false
            );
        }
    }

    /**
     * Track tour export standalone feature usage.
     *
     * @since 9.3.2
     * @param int $tour_id Exported tour ID.
     * @return void
     */
    public function track_tour_exported( $tour_id ) {
        global $wpvr_telemetry;

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $wpvr_telemetry->track_feature_used(
            'export_tour',
            array(
                'tour_id' => absint( $tour_id ),
            )
        );
    }

    /**
     * Track enabling the WordPress high-res large image override.
     *
     * @since 9.3.2
     * @return void
     */
    public function track_large_image_handler_enabled() {
        global $wpvr_telemetry;

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $wpvr_telemetry->track_feature_used( 'high_res_image_enabled' );
    }

    /**
     * Build real tour creation payload.
     *
     * @param int $tour_id Tour (post) ID.
     * @return array<string,mixed>
     */
    public function build_real_tour_creation_payload( $tour_id = 0 ) {
        $tour_id  = absint( $tour_id );
        $panodata = get_post_meta( $tour_id, 'panodata', true );
        $tour_type = is_array( $panodata ) && ! empty( $panodata['tour-type'] )
            ? sanitize_key( (string) $panodata['tour-type'] )
            : 'image';

        return array(
            'tour_id'     => $tour_id,
            'created_via' => get_post_meta( $tour_id, 'wpvr_created_from_wizard', true ) ? 'wizard' : 'manual',
            'tour_type'   => $tour_type,
            'time'        => current_time( 'mysql' ),
        );
    }

    /**
     * Track activation/tour_created when a real tour is first published.
     *
     * Emitted immediately when consent is active, skipped for demo tours, and
     * only marked as tracked after consent verification so pre-consent publishes
     * do not permanently block future tracking.
     *
     * @param string   $new_status New post status.
     * @param string   $old_status Old post status.
     * @param \WP_Post $post       Post object.
     * @return void
     */
    public function track_tour_created_event( $new_status, $old_status, $post ) {
        global $wpvr_telemetry;

        if ( ! is_object( $wpvr_telemetry ) || ! $post instanceof \WP_Post ) {
            return;
        }

        if ( 'wpvr_item' !== $post->post_type || ! $this->is_first_publish_transition( $new_status, $old_status ) ) {
            return;
        }

        if ( $this->is_seeded_tour( $post->ID ) || $this->has_tracked_tour_created_event( $post->ID ) ) {
            return;
        }

        $this->record_local_milestone( 'tour_published' );

        if ( ! $this->is_consent_enabled() ) {
            return;
        }

        $wpvr_telemetry->track_immediate( 'activation/tour_created', $this->build_real_tour_creation_payload( $post->ID ), false );
        update_post_meta( $post->ID, self::TOUR_CREATED_TRACKED_META_KEY, '1' );
    }

    /**
     * Determine if a publish transition represents a first real publish.
     *
     * @param string $new_status New post status.
     * @param string $old_status Old post status.
     * @return bool
     */
    private function is_first_publish_transition( $new_status, $old_status ) {
        if ( 'publish' !== $new_status ) {
            return false;
        }

        return in_array( $old_status, array( 'auto-draft', 'draft', 'new', 'pending', 'private', 'future', '' ), true );
    }

    /**
     * Determine whether a tour is seeded demo/sample content.
     *
     * @param int $tour_id Tour (post) ID.
     * @return bool
     */
    private function is_seeded_tour( $tour_id ) {
        return '1' === (string) get_post_meta( absint( $tour_id ), 'wpvr_is_demo_tour', true );
    }

    /**
     * Check whether the creation event has already been emitted.
     *
     * @param int $tour_id Tour (post) ID.
     * @return bool
     */
    private function has_tracked_tour_created_event( $tour_id ) {
        return '1' === (string) get_post_meta( absint( $tour_id ), self::TOUR_CREATED_TRACKED_META_KEY, true );
    }

    /**
     * Track onboarding completed event while strictly honoring consent.
     *
     * @param string $industry Industry from setup wizard.
     * @return void
     */
    public function track_onboarding_event( $industry = '' ) {
        global $wpvr_telemetry;

        $this->record_local_milestone( 'wizard_completed' );

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        if ( method_exists( $wpvr_telemetry, 'has_sent_event' ) && $wpvr_telemetry->has_sent_event( 'onboarding_completed' ) ) {
            return;
        }

        $wpvr_telemetry->track_immediate( 'activation/onboarding_completed', $this->build_setup_payload( $industry ), false );
        if ( method_exists( $wpvr_telemetry, 'mark_event_sent' ) ) {
            $wpvr_telemetry->mark_event_sent( 'onboarding_completed' );
        }
    }

    /**
     * Track frontend embedded tour views (throttled to once per tour per day).
     *
     * @param int $tour_id Tour ID.
     * @return void
     */
    public function handle_embedded_tour_view( $tour_id ) {
        global $wpvr_telemetry;

        $tour_id = absint( $tour_id );
        if ( $tour_id <= 0 ) {
            return;
        }

        $tour = get_post( $tour_id );
        if ( ! $tour || 'wpvr_item' !== $tour->post_type || 'publish' !== $tour->post_status ) {
            return;
        }

        $this->record_local_milestone( 'tour_embedded' );

        if ( ! $this->is_consent_enabled() || ! is_object( $wpvr_telemetry ) ) {
            return;
        }

        $transient_key = 'wpvr_embed_tracked_' . $tour_id;
        if ( get_transient( $transient_key ) ) {
            return;
        }

        $panodata  = get_post_meta( $tour_id, 'panodata', true );
        $tour_type = is_array( $panodata ) && ! empty( $panodata['tour-type'] )
            ? sanitize_key( (string) $panodata['tour-type'] )
            : 'image';

        set_transient( $transient_key, '1', DAY_IN_SECONDS );
        $wpvr_telemetry->track(
            'tour/embedded_viewed',
            array(
                'tour_id'   => $tour_id,
                'tour_type' => $tour_type,
            )
        );
    }

    /**
     * Replace generic deactivation reasons with VR-specific ones.
     *
     * @param array<int,array<string,string>> $reasons Default reasons.
     * @return array<int,array<string,string>>
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
