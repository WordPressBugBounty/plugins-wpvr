<?php

namespace RexTheme\WPVR\Api\Controllers;

use RexTheme\WPVR\Api\Contracts\ControllerInterface;
use RexTheme\WPVR\Api\Services\TourService;
use RexTheme\WPVR\Tracker\WPVRLinnoTelemetry;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class TourController implements ControllerInterface {

    const NAMESPACE = 'wpvr/v1';
    const BASE      = 'tours';

    private TourService $service;

    public function __construct( TourService $service ) {
        $this->service = $service;
    }

    public function register_routes(): void {
        register_rest_route( self::NAMESPACE, '/' . self::BASE, [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'create' ],
                'permission_callback' => [ $this, 'create_permission_check' ],
            ],
        ] );

        $id_arg = [
            'id' => [
                'type'              => 'integer',
                'required'          => true,
                'validate_callback' => 'rest_validate_request_arg',
                'sanitize_callback' => 'absint',
            ],
        ];

        register_rest_route( self::NAMESPACE, '/ui-mode', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'switch_ui_mode' ],
                'permission_callback' => [ $this, 'ui_mode_permission_check' ],
            ],
        ] );

        register_rest_route( self::NAMESPACE, '/telemetry/event', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'track_telemetry_event' ],
                'permission_callback' => [ $this, 'create_permission_check' ],
                'args'                => [
                    'event' => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => static function ( $value ) {
                            return is_string( $value ) && in_array( $value, WPVRLinnoTelemetry::ALLOWED_CLIENT_EVENTS, true );
                        },
                    ],
                ],
            ],
        ] );

        register_rest_route( self::NAMESPACE, '/' . self::BASE . '/(?P<id>[\d]+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'show' ],
                'permission_callback' => [ $this, 'item_permission_check' ],
                'args'                => $id_arg,
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update' ],
                'permission_callback' => [ $this, 'item_permission_check' ],
                'args'                => $id_arg,
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete' ],
                'permission_callback' => [ $this, 'item_permission_check' ],
                'args'                => $id_arg,
            ],
        ] );

    }

    public function create( WP_REST_Request $request ): WP_REST_Response {
        $body    = $request->get_json_params() ?? [];
        $post_id = $this->service->create( $body );

        if ( is_wp_error( $post_id ) ) {
            return new WP_REST_Response( [ 'message' => $post_id->get_error_message() ], 500 );
        }

        $tour_type = sanitize_key( (string) ( $body['tourType'] ?? 'image' ) );

        /**
         * Fires after a new tour draft is created via the REST API.
         *
         * @since 9.3.2
         * @param int    $post_id   Newly created tour post ID.
         * @param string $tour_type Selected tour type.
         */
        do_action( 'wpvr_tour_draft_created', (int) $post_id, $tour_type );

        return new WP_REST_Response( [ 'id' => $post_id ], 201 );
    }

    public function show( WP_REST_Request $request ): WP_REST_Response {
        $tour_id = (int) $request->get_param( 'id' );
        $post    = get_post( $tour_id );

        if ( ! $post || $post->post_type !== 'wpvr_item' ) {
            return new WP_REST_Response( [ 'message' => __( 'Tour not found.', 'wpvr' ) ], 404 );
        }

        $data            = $this->service->get( $tour_id );
        $data['tourId']  = $tour_id;
        $data['title']   = $post->post_title;
        $data['status']  = $post->post_status;

        return new WP_REST_Response( $data, 200 );
    }

    public function update( WP_REST_Request $request ): WP_REST_Response {
        $tour_id  = (int) $request->get_param( 'id' );
        $post     = get_post( $tour_id );

        if ( ! $post || $post->post_type !== 'wpvr_item' ) {
            return new WP_REST_Response( [ 'message' => __( 'Tour not found.', 'wpvr' ) ], 404 );
        }

        $body    = $request->get_json_params();
        $success = $this->service->update( $tour_id, $body );

        if ( is_wp_error( $success ) ) {
            return new WP_REST_Response( [ 'message' => $success->get_error_message() ], 400 );
        }

        if ( ! $success ) {
            return new WP_REST_Response( [ 'message' => __( 'Save failed.', 'wpvr' ) ], 500 );
        }

        $post_update      = [ 'ID' => $tour_id ];
        $allowed_statuses = [ 'draft', 'publish' ];
        if ( ! empty( $body['status'] ) && in_array( $body['status'], $allowed_statuses, true ) ) {
            $post_type_obj = get_post_type_object( 'wpvr_item' );
            $publish_cap   = $post_type_obj ? $post_type_obj->cap->publish_posts : 'publish_wpvr_tours';
            if ( 'publish' === $body['status'] && ! current_user_can( $publish_cap ) ) {
                return new WP_REST_Response( [ 'message' => __( 'You do not have permission to publish tours.', 'wpvr' ) ], 403 );
            }
            $post_update['post_status'] = $body['status'];
        }
        if ( isset( $body['title'] ) ) {
            $post_update['post_title'] = sanitize_text_field( $body['title'] );
        }
        if ( count( $post_update ) > 1 ) {
            $post_result = wp_update_post( $post_update, true );
            if ( is_wp_error( $post_result ) ) {
                return new WP_REST_Response( [ 'message' => __( 'Tour title or status could not be saved.', 'wpvr' ) ], 500 );
            }
        }

        return new WP_REST_Response( [ 'saved' => true, 'id' => $tour_id ], 200 );
    }

    public function delete( WP_REST_Request $request ): WP_REST_Response {
        $tour_id = (int) $request->get_param( 'id' );
        $post    = get_post( $tour_id );

        if ( ! $post || $post->post_type !== 'wpvr_item' ) {
            return new WP_REST_Response( [ 'message' => __( 'Tour not found.', 'wpvr' ) ], 404 );
        }

        if ( ! wp_trash_post( $tour_id ) ) {
            return new WP_REST_Response( [ 'message' => __( 'Tour could not be deleted.', 'wpvr' ) ], 500 );
        }

        /**
         * Fires after a tour is moved to trash via the REST API.
         *
         * @since 9.3.2
         * @param int $tour_id Trashed tour post ID.
         */
        do_action( 'wpvr_tour_deleted', $tour_id );

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $tour_id ], 200 );
    }

    public function switch_ui_mode( WP_REST_Request $request ): WP_REST_Response {
        $body = $request->get_json_params() ?? [];
        $mode = isset( $body['mode'] ) && $body['mode'] === 'latest' ? 'latest' : 'legacy';
        update_option( 'wpvr_ui_mode', $mode );

        /**
         * Fires after the editor UI mode is switched via REST.
         *
         * @since 9.3.2
         * @param string $mode New UI mode ('latest' or 'legacy').
         */
        do_action( 'wpvr_ui_mode_switched', $mode );

        return new WP_REST_Response( [ 'mode' => $mode ], 200 );
    }

    /**
     * Handle allowlisted client-side telemetry funnel and friction events.
     *
     * @since 9.3.2
     * @param WP_REST_Request $request Incoming REST request.
     * @return WP_REST_Response
     */
    public function track_telemetry_event( WP_REST_Request $request ): WP_REST_Response {
        $body       = $request->get_json_params() ?? [];
        $event_name = (string) $request->get_param( 'event' );
        $properties = isset( $body['properties'] ) && is_array( $body['properties'] ) ? $body['properties'] : [];
        $telemetry  = WPVRLinnoTelemetry::get_instance();

        if ( ! $telemetry ) {
            return new WP_REST_Response( [ 'tracked' => false ], 200 );
        }

        $tracked = $telemetry->track_client_event( $event_name, $properties );
        return new WP_REST_Response( [ 'tracked' => $tracked ], 200 );
    }

    public function create_permission_check(): bool {
        $post_type_obj = get_post_type_object( 'wpvr_item' );
        $create_cap    = $post_type_obj ? $post_type_obj->cap->edit_posts : 'edit_wpvr_tours';
        return current_user_can( $create_cap );
    }

    public function item_permission_check( WP_REST_Request $request ): bool {
        $tour_id = (int) $request->get_param( 'id' );
        $post    = get_post( $tour_id );
        if ( ! $post || $post->post_type !== 'wpvr_item' ) {
            return false;
        }

        $capability = ( 'DELETE' === $request->get_method() ) ? 'delete_post' : 'edit_post';
        return current_user_can( $capability, $tour_id );
    }

    public function ui_mode_permission_check(): bool {
        return current_user_can( 'manage_options' );
    }
}
