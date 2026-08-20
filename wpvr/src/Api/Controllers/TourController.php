<?php

namespace RexTheme\WPVR\Api\Controllers;

use RexTheme\WPVR\Api\Contracts\ControllerInterface;
use RexTheme\WPVR\Api\Services\TourService;
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
                'permission_callback' => [ $this, 'permission_check' ],
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
                'permission_callback' => [ $this, 'permission_check' ],
            ],
        ] );

        register_rest_route( self::NAMESPACE, '/' . self::BASE . '/(?P<id>[\d]+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'show' ],
                'permission_callback' => [ $this, 'permission_check' ],
                'args'                => $id_arg,
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update' ],
                'permission_callback' => [ $this, 'permission_check' ],
                'args'                => $id_arg,
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete' ],
                'permission_callback' => [ $this, 'delete_permission_check' ],
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

        if ( ! $success ) {
            return new WP_REST_Response( [ 'message' => __( 'Save failed.', 'wpvr' ) ], 500 );
        }

        $post_update      = [ 'ID' => $tour_id ];
        $allowed_statuses = [ 'draft', 'publish' ];
        if ( ! empty( $body['status'] ) && in_array( $body['status'], $allowed_statuses, true ) ) {
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

        return new WP_REST_Response( [ 'deleted' => true, 'id' => $tour_id ], 200 );
    }

    public function switch_ui_mode( WP_REST_Request $request ): WP_REST_Response {
        $body = $request->get_json_params() ?? [];
        $mode = isset( $body['mode'] ) && $body['mode'] === 'latest' ? 'latest' : 'legacy';
        update_option( 'wpvr_ui_mode', $mode );
        return new WP_REST_Response( [ 'mode' => $mode ], 200 );
    }

    public function permission_check(): bool {
        return current_user_can( 'edit_posts' );
    }

    public function delete_permission_check( WP_REST_Request $request ): bool {
        return current_user_can( 'delete_post', (int) $request->get_param( 'id' ) );
    }
}
