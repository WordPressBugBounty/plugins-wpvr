<?php

namespace RexTheme\WPVR\Api\Transformers;

use RexTheme\WPVR\Api\Contracts\TransformerInterface;

class TourTransformer implements TransformerInterface {

    protected bool $is_pro;

    public function __construct( bool $is_pro = false ) {
        $this->is_pro = $is_pro;
    }

    /**
     * Convert raw panodata PHP array → normalized API JSON shape.
     */
    public function toApi( array $raw ): array {
        if ( ! $this->is_pro ) {
            $raw = wpvr_get_effective_panodata( $raw );
        }

        $scenes     = $this->scenes_to_api( $raw['panodata']['scene-list'] ?? [] );
        // Enabled: check new key first, then fall back to legacy `autoRotate` speed (truthy = enabled).
        $auto_rotate_enabled = ( $raw['autorotate-enabled'] ?? '' ) === 'on' || ! empty( $raw['autoRotate'] );
        // Speed: new key first, then legacy `autoRotate` (which doubles as speed in old tours).
        $auto_rotate_speed = isset( $raw['autorotate-speed'] )
            ? (float) $raw['autorotate-speed']
            : ( isset( $raw['autoRotate'] ) ? (float) $raw['autoRotate'] : -5 );
        // Delay: new key first, then legacy `autoRotateInactivityDelay`.
        $auto_rotate_delay = isset( $raw['autorotate-delay'] )
            ? (int) $raw['autorotate-delay']
            : ( isset( $raw['autoRotateInactivityDelay'] ) ? (int) $raw['autoRotateInactivityDelay'] : 2000 );
        $auto_rotate = [
            'enabled'   => $auto_rotate_enabled,
            'speed'     => $auto_rotate_speed,
            'delay'     => $auto_rotate_delay,
            'stopDelay' => isset( $raw['autorotationstopdelay'] ) && $raw['autorotationstopdelay'] !== ''
                ? (int) $raw['autorotationstopdelay']
                : ( isset( $raw['autoRotateStopDelay'] ) && $raw['autoRotateStopDelay'] !== '' ? (int) $raw['autoRotateStopDelay'] : null ),
        ];

        $default_scene_id = ! empty( $raw['defaultscene'] ) ? $raw['defaultscene'] : null;
        $has_explicit_default_scene = ( $raw['defaultscene-auto'] ?? 'off' ) !== 'on';

        return [
            'defaultSceneId' => $has_explicit_default_scene ? $default_scene_id : null,
            'tourType'       => $this->detect_tour_type( $raw ),
            'settings'       => [
                'autoLoad'          => ! empty( $raw['autoLoad'] ),
                'showControls'      => isset( $raw['showControls'] ) ? (bool) $raw['showControls'] : true,
                'previewText'       => $raw['previewtext'] ?? '',
                'previewImage'      => $raw['preview'] ?? '',
                'sceneFadeDuration' => isset( $raw['scenefadeduration'] ) ? (int) $raw['scenefadeduration'] : 0,
                'showSceneInfo'     => ( $raw['scene-info-enabled'] ?? 'on' ) !== 'off',
                'autoRotate'        => $auto_rotate,
            ],
            'floorPlan'      => $this->floor_plan_to_api( $raw ),
            'backgroundTour' => [
                'enabled'  => ( $raw['bg_tour_enabler'] ?? 'off' ) === 'on',
                'title'    => $raw['bg_tour_title']    ?? '',
                'subtitle' => $raw['bg_tour_subtitle'] ?? '',
            ],
            'videoData'      => [
                'url'      => $raw['vidurl'] ?? '',
                'autoplay' => ( $raw['video-autoplay'] ?? 'off' ) === 'on',
                'loop'     => ( $raw['video-loop'] ?? 'off' ) === 'on',
            ],
            'streetViewData' => [
                'embedUrl' => $raw['streetviewurl'] ?? '',
            ],
            'scenes'          => $scenes,
            'advancedSettings' => $this->advanced_settings_to_api( $raw ),
        ];
    }

    /**
     * Convert normalized API JSON → raw panodata PHP array.
     */
    public function fromApi( array $data ): array {
        $settings    = $data['settings']       ?? [];
        $floor_plan  = $data['floorPlan']      ?? [];
        $bg_tour     = $data['backgroundTour'] ?? [];
        $auto_rotate = $settings['autoRotate'] ?? [];

        $video_data      = $data['videoData'] ?? [];
        $street_view_data = $data['streetViewData'] ?? [];
        $advanced_control = is_array( $data['advancedControl'] ?? null )
            ? $data['advancedControl']
            : ( is_array( $data['proData'] ?? null ) ? $data['proData'] : [] );
        $tour_type       = in_array( $data['tourType'] ?? 'image', [ 'image', 'video', 'street-view' ], true )
            ? $data['tourType']
            : 'image';
        if ( ! $this->is_pro && $tour_type === 'street-view' ) {
            $tour_type        = 'image';
            $street_view_data = [];
        }

        $scenes = is_array( $data['scenes'] ?? null ) ? $data['scenes'] : [];
        $requested_default_scene_id = (string) ( $data['defaultSceneId'] ?? '' );
        $has_explicit_default_scene = $requested_default_scene_id !== ''
            && ! empty( array_filter(
                $scenes,
                static function ( $scene ) use ( $requested_default_scene_id ) {
                    return is_array( $scene ) && ( $scene['id'] ?? '' ) === $requested_default_scene_id;
                }
            ) );
        $default_scene_id = $has_explicit_default_scene
            ? $requested_default_scene_id
            : ( $scenes[0]['id'] ?? '' );

        $raw = [
            'autoLoad'           => ! empty( $settings['autoLoad'] ),
            'showControls'       => ! empty( $settings['showControls'] ),
            'previewtext'        => $settings['previewText'] ?? '',
            'scenefadeduration'  => isset( $settings['sceneFadeDuration'] ) ? (string) $settings['sceneFadeDuration'] : '0',
            'scene-info-enabled' => array_key_exists( 'showSceneInfo', $settings ) && ! $settings['showSceneInfo'] ? 'off' : 'on',
            'defaultscene'       => $default_scene_id,
            'defaultscene-auto'  => $has_explicit_default_scene ? 'off' : 'on',
            'autorotate-enabled'     => ! empty( $auto_rotate['enabled'] ) ? 'on' : 'off',
            'autorotate-speed'       => $auto_rotate['speed'] ?? -5,
            'autorotate-delay'       => $auto_rotate['delay'] ?? 2000,
            'autorotationstopdelay'  => $auto_rotate['stopDelay'] ?? '',
            // Legacy keys for PRO plugin / shortcode compatibility.
            'autoRotate'             => ! empty( $auto_rotate['enabled'] ) ? ( $auto_rotate['speed'] ?? -5 ) : '',
            'autoRotateInactivityDelay' => $auto_rotate['delay'] ?? 2000,
            'autoRotateStopDelay'    => $auto_rotate['stopDelay'] ?? '',
            'floorplan-enabled'       => ! empty( $floor_plan['enabled'] ) ? 'on' : 'off',
            'floorplan-image'         => $floor_plan['imageUrl'] ?? '',
            'floorplan-compass'       => ! empty( $floor_plan['directionIndicator']['enabled'] ) ? 'on' : 'off',
            'floorplan-compass-color' => $floor_plan['directionIndicator']['color'] ?? '#6D28D9',
            // Legacy keys — used by legacy rendering and pro plugin.
            'floor_plan_tour_enabler'       => ! empty( $floor_plan['enabled'] ) ? 'on' : 'off',
            'floor_plan_attachment_url'     => $floor_plan['imageUrl'] ?? '',
            'floor_plan_custom_color'       => ! empty( $floor_plan['pointerColor'] ) ? $floor_plan['pointerColor'] : '#cca92c',
            'floor_plan_direction_indicator' => ! empty( $floor_plan['directionIndicator']['enabled'] ) ? 'on' : 'off',
            'floor_plan_pointer_position'   => $this->pointer_positions_from_api( $floor_plan ),
            'floor_plan_data_list'          => $this->pointer_data_list_from_api( $floor_plan ),
            'bg_tour_enabler'    => ! empty( $bg_tour['enabled'] ) ? 'on' : 'off',
            'bg_tour_title'      => $bg_tour['title']    ?? '',
            'bg_tour_subtitle'   => $bg_tour['subtitle'] ?? '',
            'genericform'        => ! empty( $advanced_control['genericform'] ) && $advanced_control['genericform'] !== 'off' ? 'on' : 'off',
            'genericformshortcode' => sanitize_text_field( (string) ( $advanced_control['genericformshortcode'] ?? '' ) ),
            'genericformicon'      => sanitize_text_field( (string) ( $advanced_control['genericformicon'] ?? 'fab fa-wpforms' ) ),
            'genericformiconcolor' => sanitize_hex_color( $advanced_control['genericformiconcolor'] ?? '' ) ?: '#f7fffb',
            'calltoaction'         => ! empty( $advanced_control['calltoaction'] ) && $advanced_control['calltoaction'] !== 'off' ? 'on' : 'off',
            'buttontext'           => sanitize_text_field( (string) ( $advanced_control['buttontext'] ?? 'Click Here' ) ),
            'buttonurl'            => esc_url_raw( (string) ( $advanced_control['buttonurl'] ?? '' ) ),
            'button_configuration' => $this->button_configuration_to_api( $advanced_control['button_configuration'] ?? [] ),
            'preview'            => esc_url_raw( $settings['previewImage'] ?? '' ),
            'panoid'             => '',
            'customcontrol'      => $this->customcontrol_to_api(
                is_array( $advanced_control['customcontrol'] ?? null )
                    ? $advanced_control['customcontrol']
                    : []
            ),
            'tour-type'          => $tour_type,
            'vidurl'             => esc_url_raw( $video_data['url'] ?? '' ),
            'video-autoplay'     => ! empty( $video_data['autoplay'] ) ? 'on' : 'off',
            'video-loop'         => ! empty( $video_data['loop'] ) ? 'on' : 'off',
            'streetviewurl'      => esc_url_raw( $street_view_data['embedUrl'] ?? '' ),
            'streetview'         => ! empty( $street_view_data['embedUrl'] ) ? 'on' : 'off',
            'panodata'           => [
                'scene-list' => $this->scenes_from_api(
                    $scenes,
                    $default_scene_id,
                    ( $settings['showSceneInfo'] ?? true ) !== false
                ),
            ],
        ];

        return $raw;
    }

    // -------------------------------------------------------------------------

    private function floor_plan_to_api( array $raw ): array {
        // Merge pointer positions and scene assignments into a unified array.
        $positions = $raw['floor_plan_pointer_position'] ?? [];
        $data_list = $raw['floor_plan_data_list'] ?? [];

        // Build id → sceneId map from data_list ({id:'1', name:'plan1', value:'scene-id'}).
        $scene_map = [];
        foreach ( $data_list as $item ) {
            $item = is_object( $item ) ? $item : (object) $item;
            $scene_map[ $item->id ?? '' ] = $item->value ?? '';
        }

        $pointers = [];
        foreach ( $positions as $pos ) {
            $pos    = is_object( $pos ) ? $pos : (object) $pos;
            $pos_id = $pos->id ?? '';
            // Extract numeric suffix from 'pointer-N'.
            preg_match( '/(\d+)$/', $pos_id, $m );
            $num      = $m[1] ?? '';
            $scene_id = $scene_map[ $num ] ?? $scene_map[ $pos_id ] ?? '';

            $pointers[] = [
                'id'      => $pos_id,
                'top'     => $pos->data_top ?? '0%',
                'left'    => $pos->data_left ?? '0%',
                'sceneId' => $scene_id,
            ];
        }

        return [
            // Read from new key first, fall back to legacy key.
            'enabled'  => ( $raw['floorplan-enabled'] ?? 'off' ) === 'on' || ( $raw['floor_plan_tour_enabler'] ?? 'off' ) === 'on',
            'imageUrl' => $raw['floorplan-image'] ?? $raw['floor_plan_attachment_url'] ?? '',
            'pointerColor' => ! empty( $raw['floor_plan_custom_color'] ) ? $raw['floor_plan_custom_color'] : '#cca92c',
            'pointers' => $pointers,
            'directionIndicator' => [
                'enabled' => ( $raw['floorplan-compass'] ?? 'off' ) === 'on' || ( $raw['floor_plan_direction_indicator'] ?? 'off' ) === 'on',
                'color'   => $raw['floorplan-compass-color'] ?? '#6D28D9',
            ],
        ];
    }

    private function pointer_positions_from_api( array $floor_plan ): array {
        $pointers = $floor_plan['pointers'] ?? [];
        $color    = $floor_plan['pointerColor'] ?? '#cca92c';
        $result   = [];
        $index    = 1;
        foreach ( $pointers as $pointer ) {
            $top  = $pointer['top']  ?? '0%';
            $left = $pointer['left'] ?? '0%';
            $result[] = (object) [
                'id'        => 'pointer-' . $index,
                'text'      => (string) $index,
                'data_top'  => $top,
                'data_left' => $left,
                'style'     => "background:{$color};top:{$top};left:{$left};",
            ];
            $index++;
        }
        return $result;
    }

    private function pointer_data_list_from_api( array $floor_plan ): array {
        $pointers = $floor_plan['pointers'] ?? [];
        $result   = [];
        $index    = 1;
        foreach ( $pointers as $pointer ) {
            $result[] = (object) [
                'id'    => (string) $index,
                'name'  => 'plan' . $index,
                'value' => $pointer['sceneId'] ?? '',
            ];
            $index++;
        }
        return $result;
    }

    /**
     * Detect tour type supporting both new-UI ('tour-type' key) and legacy
     * ('streetviewdata'/'vidid' keys) save formats.
     */
    private function detect_tour_type( array $raw ): string {
        if ( ! empty( $raw['tour-type'] ) ) {
            $allowed = [ 'image', 'video', 'street-view' ];
            return in_array( $raw['tour-type'], $allowed, true ) ? $raw['tour-type'] : 'image';
        }
        if ( isset( $raw['streetviewdata'] ) || ! empty( $raw['streetviewurl'] ) ) {
            return 'street-view';
        }
        if ( isset( $raw['vidid'] ) || ! empty( $raw['vidurl'] ) ) {
            return 'video';
        }
        return 'image';
    }

    /**
     * Extract pro advanced-control fields from raw panodata for the API response.
     * tourLayout is stored as an array by pro but the React store uses a plain string.
     */
    protected function advanced_settings_to_api( array $raw ): array {
        $b = static function ( $val, $default ) {
            if ( is_bool( $val ) ) return $val;
            return filter_var( $val ?? $default, FILTER_VALIDATE_BOOLEAN );
        };

        $tour_layout_raw = $raw['tourLayout'] ?? 'default';
        $tour_layout     = is_array( $tour_layout_raw )
            ? ( $tour_layout_raw['layout'] ?? 'default' )
            : (string) $tour_layout_raw;

        // globalzoom is on when any of hfov/maxHfov/minHfov is set.
        $globalzoom = ( ! empty( $raw['hfov'] ) || ! empty( $raw['maxHfov'] ) || ! empty( $raw['minHfov'] ) );

        return [
            'tourLayout'                      => $tour_layout,
            'layout_icon_bg_color'            => (string) ( $raw['layout_icon_bg_color'] ?? '#5a536e' ),
            'layout_icon_color'               => (string) ( $raw['layout_icon_color']    ?? '#ffffff' ),
            'diskeyboard'                     => $b( $raw['diskeyboard']             ?? null, true ),
            'keyboardzoom'                    => $b( $raw['keyboardzoom']            ?? null, true ),
            'draggable'                       => $b( $raw['draggable']               ?? null, true ),
            'mouseZoom'                       => $b( $raw['mouseZoom']               ?? null, true ),
            'gyro'                            => $b( $raw['gyro']                    ?? null, false ),
            'deviceorientationcontrol'        => $b( $raw['deviceorientationcontrol']?? null, false ),
            'compass'                         => $b( $raw['compass']                 ?? null, false ),
            'vrgallery'                       => $b( $raw['vrgallery']               ?? null, false ),
            'vrgallery_title'                 => $b( $raw['vrgallery_title']         ?? null, false ),
            'vrgallery_icon_size'             => $b( $raw['vrgallery_icon_size']     ?? null, false ),
            'vrgallery_display'               => $b( $raw['vrgallery_display']       ?? null, false ),
            'scene_navigation'                => $b( $raw['scene_navigation']        ?? null, false ),
            'scene_navigation_content_type'   => (string) ( $raw['scene_navigation_content_type'] ?? 'scene_id' ),
            'sceneAnimation'                  => $b( $raw['sceneAnimation']          ?? null, false ),
            'sceneAnimationName'              => (string) ( $raw['sceneAnimationName']              ?? 'none' ),
            'sceneAnimationTransitionDuration'=> (string) ( $raw['sceneAnimationTransitionDuration'] ?? '500' ),
            'sceneAnimationTransitionDelay'   => (string) ( $raw['sceneAnimationTransitionDelay']   ?? '0' ),
            'bg_music'                        => $b( $raw['bg_music']                ?? null, false ),
            'bg_music_url'                    => (string) ( $raw['bg_music_url']     ?? '' ),
            'autoplay_bg_music'               => $b( $raw['autoplay_bg_music']       ?? null, false ),
            'loop_bg_music'                   => $b( $raw['loop_bg_music']           ?? null, false ),
            'explainerSwitch'                 => $b( $raw['explainerSwitch']         ?? null, false ),
            'explainerContent'                => (string) ( $raw['explainerContent'] ?? '' ),
            'cpLogoSwitch'                    => $b( $raw['cpLogoSwitch']            ?? null, false ),
            'cpLogoImg'                       => (string) ( $raw['cpLogoImg']        ?? '' ),
            'cpLogoContent'                   => (string) ( $raw['cpLogoContent']    ?? '' ),
            'globalzoom'                      => $globalzoom,
            'hfov'                            => isset( $raw['hfov'] )    && $raw['hfov']    !== '' ? (int) $raw['hfov']    : null,
            'maxHfov'                         => isset( $raw['maxHfov'] ) && $raw['maxHfov'] !== '' ? (int) $raw['maxHfov'] : null,
            'minHfov'                         => isset( $raw['minHfov'] ) && $raw['minHfov'] !== '' ? (int) $raw['minHfov'] : null,
            'genericform'                     => ( ( $raw['genericform'] ?? 'off' ) === 'on' ),
            'genericformshortcode'            => (string) ( $raw['genericformshortcode'] ?? '' ),
            'genericformicon'                 => (string) ( $raw['genericformicon'] ?? 'fab fa-wpforms' ),
            'genericformiconcolor'            => (string) ( $raw['genericformiconcolor'] ?? '#f7fffb' ),
            'calltoaction'                    => ( ( $raw['calltoaction'] ?? 'off' ) === 'on' ),
            'buttontext'                      => (string) ( $raw['buttontext']  ?? 'Click Here' ),
            'buttonurl'                       => (string) ( $raw['buttonurl']   ?? '' ),
            'button_configuration'            => $this->button_configuration_to_api( $raw['button_configuration'] ?? [] ),
            'customcss_enable'                => ( ( $raw['customcss_enable'] ?? 'off' ) === 'on' ),
            'customcss'                       => (string) ( $raw['customcss']   ?? '' ),
            'customcontrol'                   => $this->customcontrol_to_api( $raw['customcontrol'] ?? [] ),
        ];
    }

    private function customcontrol_to_api( $raw_ctrl ): array {
        if ( ! is_array( $raw_ctrl ) ) {
            $raw_ctrl = [];
        }
        $defaults = [
            'panupSwitch'         => 'off', 'panupColor'         => '#f7fffb', 'panupIcon'         => 'fas fa-angle-up',
            'panDownSwitch'       => 'off', 'panDownColor'       => '#f7fffb', 'panDownIcon'       => 'fas fa-angle-down',
            'panLeftSwitch'       => 'off', 'panLeftColor'       => '#f7fffb', 'panLeftIcon'       => 'fas fa-angle-left',
            'panRightSwitch'      => 'off', 'panRightColor'      => '#f7fffb', 'panRightIcon'      => 'fas fa-angle-right',
            'panZoomInSwitch'     => 'off', 'panZoomInColor'     => '#f7fffb', 'panZoomInIcon'     => 'fas fa-plus-circle',
            'panZoomOutSwitch'    => 'off', 'panZoomOutColor'    => '#f7fffb', 'panZoomOutIcon'    => 'fas fa-minus-circle',
            'panFullscreenSwitch' => 'off', 'panFullscreenColor' => '#f7fffb', 'panFullscreenIcon' => 'fas fa-expand',
            'gyroscopeSwitch'     => 'off', 'gyroscopeColor'     => '#f7fffb', 'gyroscopeIcon'     => 'fas fa-dot-circle',
            'backToHomeSwitch'    => 'off', 'backToHomeColor'    => '#f7fffb', 'backToHomeIcon'    => 'fas fa-home',
            'explainerColor'      => '#f7fffb', 'explainerIcon'  => 'fas fa-video',
        ];
        return array_merge( $defaults, array_intersect_key( $raw_ctrl, $defaults ) );
    }

    private function button_configuration_to_api( $raw_config ): array {
        if ( ! is_array( $raw_config ) ) {
            $raw_config = [];
        }

        $defaults = [
            'button_open_new_tab'     => 'off',
            'button_background_color' => '#201cfe',
            'button_font_color'       => '#ffffff',
            'button_font_size'        => '14',
            'button_font_weight'      => '400',
            'button_line_height'      => '1',
            'button_text_decoration'  => 'none',
            'button_transform'        => 'none',
            'button_alignment'        => 'left',
            'button_text_style'       => 'normal',
            'button_letter_spacing'   => '1',
            'button_word_spacing'     => '0',
            'button_border_width'     => '1',
            'button_border_style'     => 'solid',
            'button_border_color'     => '#201cfe',
            'button_border_radius'    => '6',
            'button_pt'               => '10',
            'button_pr'               => '15',
            'button_pb'               => '10',
            'button_pl'               => '15',
        ];
        $config = array_merge( $defaults, array_intersect_key( $raw_config, $defaults ) );

        $config['button_open_new_tab'] = in_array(
            $config['button_open_new_tab'],
            [ true, 1, '1', 'on' ],
            true
        ) ? 'on' : 'off';

        $allowed_values = [
            'button_font_weight'     => [ '400', '500', '600', '700', '800', '900' ],
            'button_text_decoration' => [ 'none', 'underline', 'overline', 'line-through' ],
            'button_transform'       => [ 'none', 'uppercase', 'lowercase', 'capitalize' ],
            'button_alignment'       => [ 'left', 'right', 'center', 'justified' ],
            'button_text_style'      => [ 'normal', 'italic', 'oblique' ],
            'button_border_style'    => [ 'solid', 'dashed', 'dotted', 'double', 'none' ],
        ];
        foreach ( $allowed_values as $key => $allowed ) {
            $value          = (string) $config[ $key ];
            $config[ $key ] = in_array( $value, $allowed, true ) ? $value : $defaults[ $key ];
        }

        foreach ( [ 'button_background_color', 'button_font_color', 'button_border_color' ] as $key ) {
            $value          = (string) $config[ $key ];
            $config[ $key ] = preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ? strtolower( $value ) : $defaults[ $key ];
        }

        foreach ( [
            'button_font_size', 'button_line_height', 'button_letter_spacing',
            'button_word_spacing', 'button_border_width', 'button_border_radius',
            'button_pt', 'button_pr', 'button_pb', 'button_pl',
        ] as $key ) {
            $value          = $config[ $key ];
            $config[ $key ] = is_numeric( $value ) && (float) $value >= 0
                ? (string) $value
                : $defaults[ $key ];
        }

        return $config;
    }

    protected function scenes_to_api( array $scene_list ): array {
        $n = static function ( $val, $cast ) {
            if ( ! isset( $val ) || $val === '' ) return null;
            return $cast === 'int' ? (int) $val : (float) $val;
        };

        $scenes = [];
        foreach ( $scene_list as $scene ) {
            $scenes[] = [
                // Free fields
                'id'              => $scene['scene-id']             ?? '',
                'name'            => $scene['scene-ititle']         ?? '',
                'type'            => $scene['scene-type']           ?? 'equirectangular',
                'imageUrl'        => $scene['scene-attachment-url'] ?? '',
                'isDefault'       => ( $scene['dscene'] ?? 'off' )  === 'on',
                'hotspots'        => $this->hotspots_to_api( $scene['hotspot-list'] ?? [] ),
                // Pro: metadata
                'author'          => $scene['scene-author']              ?? '',
                'authorUrl'       => $scene['scene-author-url']          ?? '',
                'vaov'            => $n( $scene['scene-vaov']            ?? null, 'int' ),
                'haov'            => $n( $scene['scene-haov']            ?? null, 'int' ),
                'verticalOffset'  => $n( $scene['scene-vertical-offset'] ?? null, 'float' ),
                // Pro: default face orientation
                'defaultFace'     => $scene['ptyscene']     ?? 'off',
                'pitch'           => $n( $scene['scene-pitch'] ?? null, 'float' ),
                'yaw'             => $n( $scene['scene-yaw']   ?? null, 'float' ),
                // Pro: vertical drag limits
                'limitVertical'   => $scene['cvgscene']         ?? 'off',
                'maxPitch'        => $n( $scene['scene-maxpitch'] ?? null, 'float' ),
                'minPitch'        => $n( $scene['scene-minpitch'] ?? null, 'float' ),
                // Pro: horizontal drag limits
                'limitHorizontal' => $scene['chgscene']         ?? 'off',
                'maxYaw'          => $n( $scene['scene-maxyaw']  ?? null, 'float' ),
                'minYaw'          => $n( $scene['scene-minyaw']  ?? null, 'float' ),
                // Pro: per-scene zoom override
                'customZoom'      => $scene['czscene']           ?? 'off',
                'zoom'            => $n( $scene['scene-zoom']    ?? null, 'int' ),
                'maxZoom'         => $n( $scene['scene-maxzoom'] ?? null, 'int' ),
                'minZoom'         => $n( $scene['scene-minzoom'] ?? null, 'int' ),
                // Pro: cubemap faces (6-element array, null when not set)
                'cubemapFaces'    => [
                    $scene['scene-attachment-url-face0'] ?? null,
                    $scene['scene-attachment-url-face1'] ?? null,
                    $scene['scene-attachment-url-face2'] ?? null,
                    $scene['scene-attachment-url-face3'] ?? null,
                    $scene['scene-attachment-url-face4'] ?? null,
                    $scene['scene-attachment-url-face5'] ?? null,
                ],
            ];
        }
        // Older tours did not store whether a hotspot entry point inherited
        // the target scene face or was edited manually. Keep those links in
        // inherit mode: only edits made through the new Navigation Entry Point
        // controls explicitly record the custom mode.
        foreach ( $scenes as &$scene ) {
            foreach ( $scene['hotspots'] as &$hotspot ) {
                if ( in_array( $hotspot['sceneEntryPointMode'] ?? '', [ 'inherit', 'custom' ], true ) ) {
                    continue;
                }
                $hotspot['sceneEntryPointMode'] = 'inherit';
            }
            unset( $hotspot );
        }
        unset( $scene );

        return $scenes;
    }

    protected function scenes_from_api( array $scenes, string $default_scene_id = '', bool $show_scene_info = true ): array {
        $scene_list = [];
        $index      = 1;
        foreach ( $scenes as $scene ) {
            $scene_id = $scene['id'] ?? '';
            $faces    = $scene['cubemapFaces'] ?? [];
            $scene_data = [
                // Free fields
                'scene-id'             => $scene_id,
                'scene-ititle'         => $scene['name']     ?? '',
                'scene-type'           => $this->is_pro && ( $scene['type'] ?? '' ) === 'cubemap' ? 'cubemap' : 'equirectangular',
                'scene-attachment-url' => $scene['imageUrl'] ?? '',
                'scene-show-info'      => $show_scene_info ? 'on' : 'off',
                'hotspot-list'         => $this->hotspots_from_api( $scene['hotspots'] ?? [] ),
                'dscene'               => ( $default_scene_id !== '' && $scene_id === $default_scene_id ) ? 'on' : 'off',
            ];

            if ( $this->is_pro ) {
                $scene_data = array_merge( $scene_data, [
                    // Pro: metadata
                    'scene-author'               => $scene['author']          ?? '',
                    'scene-author-url'           => $scene['authorUrl']       ?? '',
                    'scene-vaov'                 => $scene['vaov']            ?? '',
                    'scene-haov'                 => $scene['haov']            ?? '',
                    'scene-vertical-offset'      => $scene['verticalOffset']  ?? '',
                    // Pro: default face orientation
                    'ptyscene'                   => $scene['defaultFace']     ?? 'off',
                    'scene-pitch'                => $scene['pitch']           ?? '',
                    'scene-yaw'                  => $scene['yaw']             ?? '',
                    // Pro: vertical drag limits
                    'cvgscene'                   => $scene['limitVertical']   ?? 'off',
                    'scene-maxpitch'             => $scene['maxPitch']        ?? '',
                    'scene-minpitch'             => $scene['minPitch']        ?? '',
                    // Pro: horizontal drag limits
                    'chgscene'                   => $scene['limitHorizontal'] ?? 'off',
                    'scene-maxyaw'               => $scene['maxYaw']          ?? '',
                    'scene-minyaw'               => $scene['minYaw']          ?? '',
                    // Pro: per-scene zoom override
                    'czscene'                    => $scene['customZoom']      ?? 'off',
                    'scene-zoom'                 => $scene['zoom']            ?? '',
                    'scene-maxzoom'              => $scene['maxZoom']         ?? '',
                    'scene-minzoom'              => $scene['minZoom']         ?? '',
                    // Pro: cubemap faces
                    'scene-attachment-url-face0' => $faces[0] ?? '',
                    'scene-attachment-url-face1' => $faces[1] ?? '',
                    'scene-attachment-url-face2' => $faces[2] ?? '',
                    'scene-attachment-url-face3' => $faces[3] ?? '',
                    'scene-attachment-url-face4' => $faces[4] ?? '',
                    'scene-attachment-url-face5' => $faces[5] ?? '',
                ] );
            }

            $scene_list[ $index ] = $scene_data;
            $index++;
        }
        return $scene_list;
    }

    protected function hotspots_to_api( array $hotspot_list ): array {
        $hotspots = [];
        foreach ( $hotspot_list as $hotspot ) {
            if ( empty( $hotspot['hotspot-title'] ) && empty( $hotspot['hotspot-type'] ) ) {
                continue;
            }
            $scene_pitch = isset( $hotspot['hotspot-scene-pitch'] ) && $hotspot['hotspot-scene-pitch'] !== ''
                ? (float) $hotspot['hotspot-scene-pitch']
                : null;
            $scene_yaw = isset( $hotspot['hotspot-scene-yaw'] ) && $hotspot['hotspot-scene-yaw'] !== ''
                ? (float) $hotspot['hotspot-scene-yaw']
                : null;
            $hotspots[] = [
                'id'            => $hotspot['hotspot-id'] ?? wp_generate_uuid4(),
                'type'          => $hotspot['hotspot-type'] ?? 'info',
                'pitch'         => isset( $hotspot['hotspot-pitch'] ) ? (float) $hotspot['hotspot-pitch'] : 0,
                'yaw'           => isset( $hotspot['hotspot-yaw'] ) ? (float) $hotspot['hotspot-yaw'] : 0,
                'text'          => $hotspot['hotspot-title'] ?? '',
                'content'       => $hotspot['hotspot-content'] ?? '',
                'url'           => $hotspot['hotspot-url'] ?? '',
                'urlOpen'       => $hotspot['hotspot-url-open'] ?? 'off',
                'hover'         => $hotspot['hotspot-hover'] ?? '',
                'targetSceneId' => $hotspot['hotspot-scene'] ?? '',
                'customClass'   => $hotspot['hotspot-customclass'] ?? '',
                // Pro styling fields
                'iconClass'     => ( ( $hotspot['hotspot-customclass-pro'] ?? '' ) === 'none' || ( $hotspot['hotspot-customclass-pro'] ?? '' ) === '' ) ? '' : $hotspot['hotspot-customclass-pro'],
                'iconBgColor'   => $hotspot['hotspot-customclass-color-icon-value'] ?? '#00b4ff',
                'iconColor'     => $hotspot['hotspot-custom-icon-color-value'] ?? '#ffffff',
                'blink'         => $hotspot['hotspot-blink'] ?? 'on',
                'shape'         => $hotspot['hotspot-shape'] ?? 'round',
                'border'        => $hotspot['hotspot-border'] ?? 'off',
                'borderWidth'   => $hotspot['hotspot-border-width'] ?? '1',
                'borderStyle'   => $hotspot['hotspot-border-style'] ?? 'none',
                'borderColor'   => $hotspot['hotspot-border-color'] ?? '#00b4ff',
                // Pro navigation entry point fields
                'scenePitch'    => $scene_pitch,
                'sceneYaw'      => $scene_yaw,
                'sceneEntryPointMode' => $hotspot['hotspot-scene-entry-point-mode'] ?? null,
            ];
        }
        return $hotspots;
    }

    protected function hotspots_from_api( array $hotspots ): array {
        $hotspot_list = [];
        foreach ( $hotspots as $hotspot ) {
            $hs = [
                'hotspot-id'          => $hotspot['id'] ?? wp_generate_uuid4(),
                'hotspot-type'        => $hotspot['type'] ?? 'info',
                'hotspot-pitch'       => (string) ( $hotspot['pitch'] ?? 0 ),
                'hotspot-yaw'         => (string) ( $hotspot['yaw'] ?? 0 ),
                'hotspot-title'       => $hotspot['text'] ?? '',
                'hotspot-content'     => $hotspot['content'] ?? '',
                'hotspot-url'         => $hotspot['url'] ?? '',
                'hotspot-url-open'    => $hotspot['urlOpen'] ?? 'off',
                'hotspot-hover'       => $hotspot['hover'] ?? '',
                'hotspot-scene'       => $hotspot['targetSceneId'] ?? '',
                'hotspot-customclass' => $hotspot['customClass'] ?? '',
                'hotspot-scene-list'  => 'none',
            ];

            if ( $this->is_pro ) {
                $hs = array_merge( $hs, [
                    // Pro styling fields
                    'hotspot-customclass-pro'              => !empty( $hotspot['iconClass'] ) ? $hotspot['iconClass'] : 'none',
                    'hotspot-customclass-color-icon-value' => $hotspot['iconBgColor'] ?? '#00b4ff',
                    'hotspot-custom-icon-color-value'      => $hotspot['iconColor'] ?? '#ffffff',
                    'hotspot-blink'                        => $hotspot['blink'] ?? 'on',
                    'hotspot-shape'                        => $hotspot['shape'] ?? 'round',
                    'hotspot-border'                       => $hotspot['border'] ?? 'off',
                    'hotspot-border-width'                 => $hotspot['borderWidth'] ?? '1',
                    'hotspot-border-style'                 => $hotspot['borderStyle'] ?? 'none',
                    'hotspot-border-color'                 => $hotspot['borderColor'] ?? '#00b4ff',
                    // Pro navigation entry point fields
                    'hotspot-scene-pitch' => $hotspot['scenePitch'] !== null ? (string) $hotspot['scenePitch'] : '',
                    'hotspot-scene-yaw'   => $hotspot['sceneYaw'] !== null ? (string) $hotspot['sceneYaw'] : '',
                    'hotspot-scene-entry-point-mode' => in_array( $hotspot['sceneEntryPointMode'] ?? '', [ 'inherit', 'custom' ], true )
                        ? $hotspot['sceneEntryPointMode']
                        : 'inherit',
                ] );
            }

            $hotspot_list[] = $hs;
        }
        return $hotspot_list;
    }
}
