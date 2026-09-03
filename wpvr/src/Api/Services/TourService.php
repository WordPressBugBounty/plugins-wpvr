<?php

namespace RexTheme\WPVR\Api\Services;

use RexTheme\WPVR\Api\Contracts\RepositoryInterface;
use RexTheme\WPVR\Api\Contracts\TransformerInterface;

class TourService {

    private RepositoryInterface  $repository;
    private TransformerInterface $transformer;
    private bool                 $is_pro;

    public function __construct( RepositoryInterface $repository, TransformerInterface $transformer, bool $is_pro = false ) {
        $this->repository  = $repository;
        $this->transformer = $transformer;
        $this->is_pro      = $is_pro;
    }

    public function get( int $tour_id ): array {
        $raw = $this->repository->find( $tour_id );
        return $this->transformer->toApi( $raw );
    }

    public function update( int $tour_id, array $api_data ): bool {
        $api_data['tourId'] = $tour_id;
        $api_data['id']     = $tour_id;

        // Read existing panodata first so we can preserve pro-managed fields
        // that the free transformer does not handle.
        $existing_raw = $this->repository->find( $tour_id );

        // Convert the API (free) fields to raw panodata format.
        $raw = $this->transformer->fromApi( $api_data );

        if ( ! $this->is_pro ) {
            $raw = $this->preserve_pro_data( $existing_raw, $raw );
        }

        // Merge: existing supplies fields unknown to the new editor; transformed
        // values overwrite fields the current plan is allowed to manage.
        if ( is_array( $existing_raw ) && ! empty( $existing_raw ) ) {
            $raw = array_merge( $existing_raw, $raw );
        }

        // If the request carries proData (future React pro panels will send this),
        // run the same filter the legacy AJAX stack uses so pro plugin saves its fields.
        if ( $this->is_pro && ! empty( $api_data['proData'] ) && is_array( $api_data['proData'] ) ) {
            // Snapshot floor plan keys before the pro filter runs.
            // The pro filter reads floor plan from legacy $_POST-style fields (floor_list_data,
            // wpvr_floor_plan_enabler, etc.) which are not present in proData when saving from the
            // React editor. Without this, the filter overwrites our correctly-converted floor plan
            // data with empty values.
            $fp_keys    = [
                'floor_plan_tour_enabler', 'floor_plan_attachment_url', 'floor_plan_custom_color',
                'floor_plan_pointer_position', 'floor_plan_data_list', 'floor_plan_direction_indicator',
                'floorplan-enabled', 'floorplan-image', 'floorplan-compass', 'floorplan-compass-color',
            ];
            $fp_snapshot = [];
            foreach ( $fp_keys as $k ) {
                if ( array_key_exists( $k, $raw ) ) {
                    $fp_snapshot[ $k ] = $raw[ $k ];
                }
            }

            $post_format      = array_merge( $api_data['proData'], [ 'postid' => (string) $tour_id ] );
            // React toggles are booleans, while the legacy Pro save filter and
            // shortcode renderer require the exact strings "on" / "off".
            // Without this conversion an enabled scene-navigation toggle is
            // stored as "1", so the renderer's strict "on" check never passes.
            if ( array_key_exists( 'scene_navigation', $post_format ) ) {
                $scene_navigation                 = $post_format['scene_navigation'];
                $post_format['scene_navigation'] = ( ! empty( $scene_navigation ) && $scene_navigation !== 'off' ) ? 'on' : 'off';
            }
            if ( array_key_exists( 'sceneAnimation', $post_format ) ) {
                $scene_animation               = $post_format['sceneAnimation'];
                $post_format['sceneAnimation'] = ( ! empty( $scene_animation ) && $scene_animation !== 'off' ) ? 'on' : 'off';
            }
            if ( ( $post_format['sceneAnimationName'] ?? '' ) === 'fade' ) {
                $post_format['sceneAnimationName'] = 'fade_in';
            }
            $advanced_control = is_array( $api_data['advancedControl'] ?? null ) ? $api_data['advancedControl'] : [];
            $advanced_control = $this->normalize_advanced_control( $advanced_control );

            // The legacy Pro filter expects the CTA toggle under callToAction and
            // every button style as a flat POST field. The React editor keeps
            // these values in advancedControl.button_configuration.
            $post_format['callToAction'] = $advanced_control['calltoaction'] ?? ( $raw['calltoaction'] ?? 'off' );
            $post_format['buttontext']   = $raw['buttontext'] ?? 'Click Here';
            $post_format['buttonurl']    = $raw['buttonurl'] ?? '';
            $button_configuration        = is_array( $raw['button_configuration'] ?? null )
                ? $raw['button_configuration']
                : [];
            foreach ( $button_configuration as $key => $value ) {
                $post_format[ $key ] = $value;
            }

            $raw              = apply_filters( 'prepare_scene_pano_array_with_pro_version', $raw, $post_format, $advanced_control );

            // Restore floor plan keys that the pro filter may have blanked out because it could
            // not find the legacy $_POST floor-plan fields in proData.
            foreach ( $fp_snapshot as $k => $v ) {
                $current = $raw[ $k ] ?? null;
                $blanked = ( $current === null || $current === '' || $current === [] || $current === 'off' );
                $had_val = ( $v !== null && $v !== '' && $v !== [] );
                if ( $blanked && $had_val ) {
                    $raw[ $k ] = $v;
                }
            }
        }

        $saved = $this->repository->save( $tour_id, $raw );

        if ( $saved ) {
            do_action( 'rex_wpvr_tour_saved', $tour_id );
            do_action( 'wpvr_pro_delete_orphaned_analytics_data', $tour_id );
        }

        return $saved;
    }

    /**
     * Keep all stored Pro values during a Free-plan save while still allowing
     * changes to the free scene and hotspot fields.
     */
    private function preserve_pro_data( array $existing, array $incoming ): array {
        $protected_top_level = [
            'tourLayout', 'layout_icon_bg_color', 'layout_icon_color',
            'diskeyboard', 'keyboardzoom', 'draggable', 'mouseZoom', 'gyro',
            'deviceorientationcontrol', 'compass', 'vrgallery', 'vrgallery_title',
            'vrgallery_icon_size', 'vrgallery_display', 'scene_navigation',
            'scene_navigation_content_type', 'sceneAnimation', 'sceneAnimationName',
            'sceneAnimationTransitionDuration', 'sceneAnimationTransitionDelay',
            'bg_music', 'bg_music_url', 'autoplay_bg_music', 'loop_bg_music',
            'explainerSwitch', 'explainerContent', 'cpLogoSwitch', 'cpLogoImg',
            'cpLogoContent', 'hfov', 'maxHfov', 'minHfov', 'genericform',
            'genericformshortcode', 'genericformicon', 'genericformiconcolor',
            'calltoaction', 'buttontext', 'buttonurl', 'button_configuration',
            'customcss_enable', 'customcss', 'customcontrol',
            'floorplan-enabled', 'floorplan-image', 'floorplan-compass',
            'floorplan-compass-color', 'floor_plan_tour_enabler',
            'floor_plan_attachment_url', 'floor_plan_custom_color',
            'floor_plan_direction_indicator', 'floor_plan_pointer_position',
            'floor_plan_data_list', 'bg_tour_enabler', 'bg_tour_title',
            'bg_tour_subtitle', 'video-autoplay', 'video-loop',
        ];

        foreach ( $protected_top_level as $key ) {
            unset( $incoming[ $key ] );
        }

        if ( ( $existing['tour-type'] ?? '' ) === 'street-view' || isset( $existing['streetviewdata'] ) ) {
            foreach ( [ 'tour-type', 'streetview', 'streetviewurl', 'streetviewdata' ] as $key ) {
                unset( $incoming[ $key ] );
            }
        }

        $existing_scenes = $existing['panodata']['scene-list'] ?? [];
        $incoming_scenes = $incoming['panodata']['scene-list'] ?? [];
        $scene_map       = [];
        foreach ( $existing_scenes as $scene ) {
            if ( is_array( $scene ) && ! empty( $scene['scene-id'] ) ) {
                $scene_map[ (string) $scene['scene-id'] ] = $scene;
            }
        }

        foreach ( $incoming_scenes as $index => $scene ) {
            if ( ! is_array( $scene ) ) {
                continue;
            }

            $scene_id       = (string) ( $scene['scene-id'] ?? '' );
            $existing_scene = $scene_map[ $scene_id ] ?? [];
            if ( empty( $existing_scene ) ) {
                continue;
            }

            $existing_hotspots = $existing_scene['hotspot-list'] ?? [];
            $incoming_hotspots = $scene['hotspot-list'] ?? [];
            $hotspot_map       = [];
            foreach ( $existing_hotspots as $hotspot ) {
                if ( is_array( $hotspot ) && ! empty( $hotspot['hotspot-id'] ) ) {
                    $hotspot_map[ (string) $hotspot['hotspot-id'] ] = $hotspot;
                }
            }

            foreach ( $incoming_hotspots as $hotspot_index => $hotspot ) {
                if ( ! is_array( $hotspot ) ) {
                    continue;
                }
                $hotspot_id = (string) ( $hotspot['hotspot-id'] ?? '' );
                if ( isset( $hotspot_map[ $hotspot_id ] ) ) {
                    $incoming_hotspots[ $hotspot_index ] = array_merge( $hotspot_map[ $hotspot_id ], $hotspot );
                }
            }

            $scene['hotspot-list'] = $incoming_hotspots;
            $merged_scene          = array_merge( $existing_scene, $scene );

            if ( ( $existing_scene['scene-type'] ?? '' ) === 'cubemap' ) {
                $merged_scene['scene-type'] = 'cubemap';
            }

            $incoming_scenes[ $index ] = $merged_scene;
        }

        $incoming['panodata'] = array_merge(
            is_array( $existing['panodata'] ?? null ) ? $existing['panodata'] : [],
            is_array( $incoming['panodata'] ?? null ) ? $incoming['panodata'] : [],
            [ 'scene-list' => $incoming_scenes ]
        );

        return $incoming;
    }

    /**
     * Normalize React boolean values to the exact format legacy PHP stores them.
     *
     * set_pro_checkbox_value / sanitize_text_field fields → 'on'/'off' string.
     * set_checkbox_value / set_checkbox_on_value fields   → PHP bool (already correct).
     */
    private function normalize_advanced_control( array $data ): array {
        $on_off_keys = [
            'mouseZoom', 'draggable', 'diskeyboard',
            'bg_music', 'autoplay_bg_music', 'loop_bg_music',
            'cpLogoSwitch', 'explainerSwitch',
            'genericform', 'calltoaction', 'customcss_enable',
        ];
        foreach ( $on_off_keys as $key ) {
            if ( array_key_exists( $key, $data ) ) {
                $v           = $data[ $key ];
                $data[ $key ] = ( ! empty( $v ) && $v !== 'off' ) ? 'on' : 'off';
            }
        }
        return $data;
    }

    /**
     * Create a wpvr_item post. Returns new post ID or WP_Error.
     *
     * @param array $args { title?: string, tourType?: string }
     * @return int|\WP_Error
     */
    public function create( array $args = [] ) {
        $title = ! empty( $args['title'] )
            ? sanitize_text_field( $args['title'] )
            : __( 'New Tour', 'wpvr' );

        $allowed   = [ 'image', 'video', 'street-view' ];
        $tour_type = in_array( $args['tourType'] ?? 'image', $allowed, true )
            ? ( $args['tourType'] ?? 'image' )
            : 'image';

        if ( ! $this->is_pro && $tour_type === 'street-view' ) {
            $tour_type = 'image';
        }

        $post_id = wp_insert_post( [
            'post_type'   => 'wpvr_item',
            'post_status' => 'draft',
            'post_title'  => $title,
        ], true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        $this->repository->save( $post_id, [ 'tour-type' => $tour_type ] );

        return $post_id;
    }
}
