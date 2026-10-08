<?php

namespace RexTheme\WPVR\Api\Transformers;

use RexTheme\WPVR\Api\Contracts\TransformerInterface;
use RexTheme\WPVR\Content\TextOverlayContent;

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
                'socialShare'       => ( $raw['wpvr_social_share'] ?? 'off' ) === 'on',
            ],
            'floorPlan'      => $this->floor_plan_to_api( $raw ),
            'backgroundTour' => [
                'enabled'  => ( $raw['bg_tour_enabler'] ?? 'off' ) === 'on',
                'title'    => $raw['bg_tour_title']    ?? '',
                'subtitle' => $raw['bg_tour_subtitle'] ?? '',
            ],
            'videoData'      => [
                'url'      => $raw['vidurl'] ?? '',
                'autoplay' => ( $raw['video-autoplay'] ?? $raw['autoplay'] ?? 'off' ) === 'on',
                'loop'     => ( $raw['video-loop'] ?? $raw['loop'] ?? 'off' ) === 'on',
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
            'autoLoad'           => ! empty( $settings['autoLoad'] ) && $settings['autoLoad'] !== 'off' && $settings['autoLoad'] !== 'false',
            'showControls'       => ! empty( $settings['showControls'] ),
            'draggable'          => ( isset( $advanced_control['draggable'] ) && ( $advanced_control['draggable'] === 'off' || $advanced_control['draggable'] === false ) ) ? 'off' : 'on',
            'mouseZoom'          => ( isset( $advanced_control['mouseZoom'] ) && ( $advanced_control['mouseZoom'] === 'off' || $advanced_control['mouseZoom'] === false ) ) ? 'off' : 'on',
            'diskeyboard'        => ( isset( $advanced_control['diskeyboard'] ) && ( $advanced_control['diskeyboard'] === 'off' || $advanced_control['diskeyboard'] === false ) ) ? 'on' : 'off',
            'keyboardzoom'       => ( isset( $advanced_control['keyboardzoom'] ) && ( $advanced_control['keyboardzoom'] === 'off' || $advanced_control['keyboardzoom'] === false ) ) ? false : true,
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
            'autoplay'           => ! empty( $video_data['autoplay'] ) ? 'on' : 'off',
            'loop'               => ! empty( $video_data['loop'] ) ? 'on' : 'off',
            'streetviewurl'      => esc_url_raw( $street_view_data['embedUrl'] ?? '' ),
            'streetview'         => ! empty( $street_view_data['embedUrl'] ) ? 'on' : 'off',
            'panodata'           => [
                'scene-list' => $this->scenes_from_api(
                    $scenes,
                    $default_scene_id,
                    ( $settings['showSceneInfo'] ?? true ) !== false
                ),
            ],
            'wpvr_social_share'  => ! empty( $settings['socialShare'] ) ? 'on' : 'off',
        ];

        if ( $tour_type === 'video' && ! empty( $video_data['url'] ) ) {
            $raw['vidid'] = 'vid' . ( $data['tourId'] ?? $data['id'] ?? wp_rand( 1000, 99999 ) );
        }

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
        if ( ! empty( $raw['vidid'] ) || ! empty( $raw['vidurl'] ) ) {
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
            'diskeyboard'                     => ! in_array( $raw['diskeyboard'] ?? 'off', [ 'on', 'true', true ], true ),
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
                'textOverlays'    => $this->text_overlays_to_api( $scene['text-overlays'] ?? [] ),
                'showLayerToggle' => ( $scene['scene-show-layer-toggle'] ?? 'on' ) === 'on',
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
                'dscene'                  => ( $default_scene_id !== '' && $scene_id === $default_scene_id ) ? 'on' : 'off',
                'text-overlays'           => $this->text_overlays_from_api( $scene['textOverlays'] ?? [] ),
                'scene-show-layer-toggle' => ( ! isset( $scene['showLayerToggle'] ) || ! empty( $scene['showLayerToggle'] ) ) ? 'on' : 'off',
                'showLayerToggle'         => ( ! isset( $scene['showLayerToggle'] ) || ! empty( $scene['showLayerToggle'] ) ),
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
                    'text-overlays'              => $this->text_overlays_from_api( $scene['textOverlays'] ?? [] ),
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
            $product_id   = isset( $hotspot['hotspot-product-id'] ) ? (string) $hotspot['hotspot-product-id'] : '';
            $product_name = '';
            if ( ! empty( $product_id ) && function_exists( 'wc_get_product' ) ) {
                $product_obj = wc_get_product( $product_id );
                if ( is_object( $product_obj ) ) {
                    $product_name = $product_obj->get_formatted_name();
                }
            }

            $template_val        = $hotspot['hotspot-sticker-template'] ?? '';
            $is_separate_icon    = $template_val === 'button_with_separate_icon';
            $is_add_to_cart      = $template_val === 'add_to_cart';
            $is_social_share     = $template_val === 'social_share';
            $default_card_radius = $is_separate_icon ? 135 : ( ( $is_add_to_cart || $is_social_share ) ? 15 : 20 );

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
                'fluentFormId'  => isset( $hotspot['fluent-form-id'] ) ? (string) $hotspot['fluent-form-id'] : '',
                'productId'     => $product_id,
                'productName'   => $product_name,
                // Sticker fields
                'stickerTemplate'     => $template_val ?: 'social_proof',
                'stickerReviewText'   => $hotspot['hotspot-sticker-review-text'] ?? 'The support is super responsive and responds without worries to our requests and needs! Big up to the entire RexTheme team!',
                'stickerClientName'   => $hotspot['hotspot-sticker-client-name'] ?? 'Elena R.',
                'stickerClientAvatar' => $hotspot['hotspot-sticker-client-avatar'] ?? '',
                'stickerRating'       => isset( $hotspot['hotspot-sticker-rating'] ) ? (int) $hotspot['hotspot-sticker-rating'] : 5,
                'stickerBgColor'      => $hotspot['hotspot-sticker-bg-color'] ?? '#201b2c',
                'stickerBgOpacity'    => isset( $hotspot['hotspot-sticker-bg-opacity'] ) ? (float) $hotspot['hotspot-sticker-bg-opacity'] : ( ( $is_separate_icon || $is_add_to_cart || $is_social_share ) ? 75 : 95 ),
                'stickerBlur'         => isset( $hotspot['hotspot-sticker-blur'] ) ? (float) $hotspot['hotspot-sticker-blur'] : 12,
                'stickerBrightness'   => isset( $hotspot['hotspot-sticker-brightness'] ) ? (float) $hotspot['hotspot-sticker-brightness'] : 100,
                'stickerTextColor'    => $hotspot['hotspot-sticker-text-color'] ?? '#ffffff',
                'stickerBorderColor'  => $hotspot['hotspot-sticker-border-color'] ?? ( ( $is_separate_icon || $is_add_to_cart || $is_social_share ) ? '#40355a' : '#3a3051' ),
                'stickerBorderRadius' => isset( $hotspot['hotspot-sticker-border-radius'] ) && is_array( $hotspot['hotspot-sticker-border-radius'] ) ? [
                    'topLeft'     => isset( $hotspot['hotspot-sticker-border-radius']['topLeft'] ) ? (float) $hotspot['hotspot-sticker-border-radius']['topLeft'] : $default_card_radius,
                    'topRight'    => isset( $hotspot['hotspot-sticker-border-radius']['topRight'] ) ? (float) $hotspot['hotspot-sticker-border-radius']['topRight'] : $default_card_radius,
                    'bottomRight' => isset( $hotspot['hotspot-sticker-border-radius']['bottomRight'] ) ? (float) $hotspot['hotspot-sticker-border-radius']['bottomRight'] : $default_card_radius,
                    'bottomLeft'  => isset( $hotspot['hotspot-sticker-border-radius']['bottomLeft'] ) ? (float) $hotspot['hotspot-sticker-border-radius']['bottomLeft'] : $default_card_radius,
                ] : ( isset( $hotspot['hotspot-sticker-border-radius'] ) && is_numeric( $hotspot['hotspot-sticker-border-radius'] ) ? [
                    'topLeft'     => (float) $hotspot['hotspot-sticker-border-radius'],
                    'topRight'    => (float) $hotspot['hotspot-sticker-border-radius'],
                    'bottomRight' => (float) $hotspot['hotspot-sticker-border-radius'],
                    'bottomLeft'  => (float) $hotspot['hotspot-sticker-border-radius'],
                ] : [
                    'topLeft'     => $default_card_radius,
                    'topRight'    => $default_card_radius,
                    'bottomRight' => $default_card_radius,
                    'bottomLeft'  => $default_card_radius,
                ] ),
                'stickerPadding'      => isset( $hotspot['hotspot-sticker-padding'] ) && is_array( $hotspot['hotspot-sticker-padding'] ) ? [
                    'top'    => isset( $hotspot['hotspot-sticker-padding']['top'] ) ? (float) $hotspot['hotspot-sticker-padding']['top'] : 21,
                    'right'  => isset( $hotspot['hotspot-sticker-padding']['right'] ) ? (float) $hotspot['hotspot-sticker-padding']['right'] : 21,
                    'bottom' => isset( $hotspot['hotspot-sticker-padding']['bottom'] ) ? (float) $hotspot['hotspot-sticker-padding']['bottom'] : 21,
                    'left'   => isset( $hotspot['hotspot-sticker-padding']['left'] ) ? (float) $hotspot['hotspot-sticker-padding']['left'] : 21,
                ] : ( isset( $hotspot['hotspot-sticker-padding'] ) && is_numeric( $hotspot['hotspot-sticker-padding'] ) ? [
                    'top'    => (float) $hotspot['hotspot-sticker-padding'],
                    'right'  => (float) $hotspot['hotspot-sticker-padding'],
                    'bottom' => (float) $hotspot['hotspot-sticker-padding'],
                    'left'   => (float) $hotspot['hotspot-sticker-padding'],
                ] : [
                    'top'    => 21,
                    'right'  => 21,
                    'bottom' => 21,
                    'left'   => 21,
                ] ),
                'stickerStarColor'    => $hotspot['hotspot-sticker-star-color'] ?? '#EF991F',
                // Text before button (for add_to_cart)
                'stickerPrefixText'   => $hotspot['hotspot-sticker-prefix-text'] ?? '$1,090 -',
                'stickerSubText'      => $hotspot['hotspot-sticker-sub-text'] ?? 'Ready to ship',
                // Discount button, add to cart & social share sticker fields
                'stickerBtnText'      => $hotspot['hotspot-sticker-btn-text'] ?? ( $is_social_share ? 'SHARE EXPERIENCE' : ( $is_add_to_cart ? 'ADD TO CART' : ( $is_separate_icon ? 'Limited “Midnight Horizon” Edition' : 'GET 20% OFF NOW' ) ) ),
                'stickerBtnColor'     => $hotspot['hotspot-sticker-btn-color'] ?? ( $is_social_share ? '#201a2b' : ( $is_add_to_cart ? '#3f04fe' : ( $is_separate_icon ? '#ffffff' : '#EF991F' ) ) ),
                'stickerBtnTextColor' => $hotspot['hotspot-sticker-btn-text-color'] ?? ( ( $is_add_to_cart || $is_social_share ) ? '#ffffff' : '#000000' ),
                'stickerBtnIconColor' => $hotspot['hotspot-sticker-btn-icon-color'] ?? '#000000',
                'stickerBtnUrl'       => $hotspot['hotspot-sticker-btn-url'] ?? '',
                'stickerBtnNewTab'    => $hotspot['hotspot-sticker-btn-new-tab'] ?? 'off',
                'stickerMainBg'       => $hotspot['hotspot-sticker-main-bg'] ?? ( $hotspot['hotspot-sticker-card-bg'] ?? 'on' ),
                'stickerCardBg'       => $hotspot['hotspot-sticker-card-bg'] ?? ( $hotspot['hotspot-sticker-main-bg'] ?? 'on' ),
                'stickerBtnBg'          => $hotspot['hotspot-sticker-btn-bg'] ?? 'on',
                'stickerBtnWidth'       => isset( $hotspot['hotspot-sticker-btn-width'] ) && $hotspot['hotspot-sticker-btn-width'] !== '' ? (float) $hotspot['hotspot-sticker-btn-width'] : ( $is_social_share ? 222 : ( $is_add_to_cart ? 163 : ( $is_separate_icon ? 356 : '' ) ) ),
                'stickerBtnHeight'      => isset( $hotspot['hotspot-sticker-btn-height'] ) && $hotspot['hotspot-sticker-btn-height'] !== '' ? (float) $hotspot['hotspot-sticker-btn-height'] : ( ( $is_social_share || $is_add_to_cart || $is_separate_icon ) ? 60 : '' ),
                'stickerBtnRadius'      => isset( $hotspot['hotspot-sticker-btn-radius'] ) && $hotspot['hotspot-sticker-btn-radius'] !== '' ? (float) $hotspot['hotspot-sticker-btn-radius'] : ( $is_separate_icon ? 30 : 10 ),
                'stickerBtnBorder'      => isset( $hotspot['hotspot-sticker-btn-border'] ) && $hotspot['hotspot-sticker-btn-border'] !== '' ? (float) $hotspot['hotspot-sticker-btn-border'] : ( $is_social_share ? 1 : 0 ),
                'stickerBtnBorderColor' => $hotspot['hotspot-sticker-btn-border-color'] ?? ( $is_social_share ? '#3a3051' : ( ( $is_add_to_cart || $is_separate_icon ) ? '#ffffff' : '#EF991F' ) ),
                'stickerBtnTextSize'    => isset( $hotspot['hotspot-sticker-btn-text-size'] ) && $hotspot['hotspot-sticker-btn-text-size'] !== '' ? (float) $hotspot['hotspot-sticker-btn-text-size'] : 18,
                'stickerBtnTextWeight'  => $hotspot['hotspot-sticker-btn-text-weight'] ?? ( ( $is_social_share || $is_add_to_cart || $is_separate_icon ) ? '600' : '700' ),
                'stickerBtnIcon'        => ( $is_add_to_cart || $is_social_share ) ? 'none' : ( $hotspot['hotspot-sticker-btn-icon'] ?? 'fas fa-tag' ),
                // Button with separate icon fields
                'stickerIconBoxBgColor'     => $hotspot['hotspot-sticker-icon-box-bg-color'] ?? '#ffffff',
                'stickerIconBoxRadius'      => isset( $hotspot['hotspot-sticker-icon-box-radius'] ) && $hotspot['hotspot-sticker-icon-box-radius'] !== '' ? (float) $hotspot['hotspot-sticker-icon-box-radius'] : 30,
                'stickerIconBoxBorder'      => isset( $hotspot['hotspot-sticker-icon-box-border'] ) && $hotspot['hotspot-sticker-icon-box-border'] !== '' ? (float) $hotspot['hotspot-sticker-icon-box-border'] : 0,
                'stickerIconBoxBorderColor' => $hotspot['hotspot-sticker-icon-box-border-color'] ?? '#ffffff',
                'stickerIconBoxSize'        => isset( $hotspot['hotspot-sticker-icon-box-size'] ) && $hotspot['hotspot-sticker-icon-box-size'] !== '' ? (float) $hotspot['hotspot-sticker-icon-box-size'] : 60,
                // Social share sticker fields
                'stickerSocialColor'        => $hotspot['hotspot-sticker-social-color'] ?? '#ffffff',
                'stickerSocialSize'         => isset( $hotspot['hotspot-sticker-social-size'] ) && $hotspot['hotspot-sticker-social-size'] !== '' ? (float) $hotspot['hotspot-sticker-social-size'] : 20,
                'stickerSocialGap'          => isset( $hotspot['hotspot-sticker-social-gap'] ) && $hotspot['hotspot-sticker-social-gap'] !== '' ? (float) $hotspot['hotspot-sticker-social-gap'] : 16,
                'stickerSocialLinks'        => isset( $hotspot['hotspot-sticker-social-links'] ) ? ( is_array( $hotspot['hotspot-sticker-social-links'] ) ? $hotspot['hotspot-sticker-social-links'] : json_decode( (string) $hotspot['hotspot-sticker-social-links'], true ) ) : [
                    [ 'id' => '1', 'icon' => 'fab fa-linkedin-in', 'customSvg' => '', 'url' => 'https://linkedin.com', 'openNewTab' => 'on' ],
                    [ 'id' => '2', 'icon' => 'fab fa-facebook-f', 'customSvg' => '', 'url' => 'https://facebook.com', 'openNewTab' => 'on' ],
                    [ 'id' => '3', 'icon' => 'fab fa-instagram', 'customSvg' => '', 'url' => 'https://instagram.com', 'openNewTab' => 'on' ],
                    [ 'id' => '4', 'icon' => 'fab fa-dribbble', 'customSvg' => '', 'url' => 'https://dribbble.com', 'openNewTab' => 'on' ],
                ],
                'stickerShowPlaceholderText' => $hotspot['hotspot-sticker-show-placeholder-text'] ?? 'on',
                'scale'               => isset( $hotspot['hotspot-scale'] ) ? ( $hotspot['hotspot-scale'] === 'on' || $hotspot['hotspot-scale'] === true ) : ( ( $hotspot['hotspot-type'] ?? '' ) === 'sticker' ),
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
            $hotspot_type = sanitize_key( $hotspot['type'] ?? 'info' );
            $is_fluent_form = $hotspot_type === 'fluent_form';
            $raw_content = (string) ( $hotspot['content'] ?? '' );
            $raw_hover   = (string) ( $hotspot['hover'] ?? '' );

            // For fluent_form hotspots, content is dynamically rendered server-side from fluent-form-id.
            // User-supplied content is never used and must not be saved, completely eliminating stored XSS.
            if ( $is_fluent_form ) {
                $content = '';
            } else {
                $content = function_exists( 'sanitize_content_preserve_styles' )
                    ? sanitize_content_preserve_styles( $raw_content, false )
                    : wp_kses_post( $raw_content );
            }
            $hover = function_exists( 'sanitize_content_preserve_styles' )
                ? sanitize_content_preserve_styles( $raw_hover, false )
                : wp_kses_post( $raw_hover );

            $hs = [
                'hotspot-id'          => $hotspot['id'] ?? wp_generate_uuid4(),
                'hotspot-type'        => $hotspot_type,
                'hotspot-pitch'       => (string) ( $hotspot['pitch'] ?? 0 ),
                'hotspot-yaw'         => (string) ( $hotspot['yaw'] ?? 0 ),
                'hotspot-title'       => sanitize_text_field( $hotspot['text'] ?? '' ),
                'hotspot-content'     => $content,
                'hotspot-url'         => sanitize_text_field( $hotspot['url'] ?? '' ),
                'hotspot-url-open'    => ( $hotspot['urlOpen'] ?? 'off' ) === 'on' ? 'on' : 'off',
                'hotspot-hover'       => $hover,
                'hotspot-scene'       => sanitize_text_field( $hotspot['targetSceneId'] ?? '' ),
                'hotspot-customclass' => sanitize_text_field( $hotspot['customClass'] ?? '' ),
                'hotspot-scene-list'  => 'none',
            ];

            if ( isset( $hotspot['fluentFormId'] ) ) {
                $hs['fluent-form-id'] = (string) absint( $hotspot['fluentFormId'] );
            } elseif ( isset( $hotspot['fluent-form-id'] ) ) {
                $hs['fluent-form-id'] = (string) absint( $hotspot['fluent-form-id'] );
            }

            if ( isset( $hotspot['productId'] ) ) {
                $hs['hotspot-product-id'] = sanitize_text_field( $hotspot['productId'] );
            } elseif ( isset( $hotspot['hotspot-product-id'] ) ) {
                $hs['hotspot-product-id'] = sanitize_text_field( $hotspot['hotspot-product-id'] );
            }

            // Sticker fields
            if ( isset( $hotspot['stickerTemplate'] ) || isset( $hotspot['hotspot-sticker-template'] ) ) {
                $hs['hotspot-sticker-template'] = sanitize_text_field( $hotspot['stickerTemplate'] ?? $hotspot['hotspot-sticker-template'] );
            }
            if ( isset( $hotspot['stickerReviewText'] ) || isset( $hotspot['hotspot-sticker-review-text'] ) ) {
                $hs['hotspot-sticker-review-text'] = sanitize_textarea_field( $hotspot['stickerReviewText'] ?? $hotspot['hotspot-sticker-review-text'] );
            }
            if ( isset( $hotspot['stickerClientName'] ) || isset( $hotspot['hotspot-sticker-client-name'] ) ) {
                $hs['hotspot-sticker-client-name'] = sanitize_text_field( $hotspot['stickerClientName'] ?? $hotspot['hotspot-sticker-client-name'] );
            }
            if ( isset( $hotspot['stickerClientAvatar'] ) || isset( $hotspot['hotspot-sticker-client-avatar'] ) ) {
                $hs['hotspot-sticker-client-avatar'] = esc_url_raw( $hotspot['stickerClientAvatar'] ?? $hotspot['hotspot-sticker-client-avatar'] );
            }
            if ( isset( $hotspot['stickerRating'] ) || isset( $hotspot['hotspot-sticker-rating'] ) ) {
                $hs['hotspot-sticker-rating'] = (string) max( 1, min( 5, (int) ( $hotspot['stickerRating'] ?? $hotspot['hotspot-sticker-rating'] ) ) );
            }
            if ( isset( $hotspot['stickerBgColor'] ) || isset( $hotspot['hotspot-sticker-bg-color'] ) ) {
                $hs['hotspot-sticker-bg-color'] = sanitize_text_field( $hotspot['stickerBgColor'] ?? $hotspot['hotspot-sticker-bg-color'] );
            }
            if ( isset( $hotspot['stickerBgOpacity'] ) || isset( $hotspot['hotspot-sticker-bg-opacity'] ) ) {
                $hs['hotspot-sticker-bg-opacity'] = max( 0, min( 100, (float) ( $hotspot['stickerBgOpacity'] ?? $hotspot['hotspot-sticker-bg-opacity'] ) ) );
            }
            if ( isset( $hotspot['stickerBlur'] ) || isset( $hotspot['hotspot-sticker-blur'] ) ) {
                $hs['hotspot-sticker-blur'] = max( 0, min( 40, (float) ( $hotspot['stickerBlur'] ?? $hotspot['hotspot-sticker-blur'] ) ) );
            }
            if ( isset( $hotspot['stickerBrightness'] ) || isset( $hotspot['hotspot-sticker-brightness'] ) ) {
                $hs['hotspot-sticker-brightness'] = max( 0, min( 200, (float) ( $hotspot['stickerBrightness'] ?? $hotspot['hotspot-sticker-brightness'] ) ) );
            }
            if ( isset( $hotspot['stickerTextColor'] ) || isset( $hotspot['hotspot-sticker-text-color'] ) ) {
                $hs['hotspot-sticker-text-color'] = sanitize_hex_color( $hotspot['stickerTextColor'] ?? $hotspot['hotspot-sticker-text-color'] ) ?: '#ffffff';
            }
            if ( isset( $hotspot['stickerBorderColor'] ) || isset( $hotspot['hotspot-sticker-border-color'] ) ) {
                $raw_border_color = strtolower( trim( (string) ( $hotspot['stickerBorderColor'] ?? $hotspot['hotspot-sticker-border-color'] ) ) );
                if ( $raw_border_color === 'none' || $raw_border_color === 'transparent' ) {
                    $hs['hotspot-sticker-border-color'] = $raw_border_color;
                } else {
                    $hs['hotspot-sticker-border-color'] = sanitize_hex_color( $raw_border_color ) ?: '#3a3051';
                }
            }
            if ( isset( $hotspot['stickerBorderRadius'] ) || isset( $hotspot['hotspot-sticker-border-radius'] ) ) {
                $raw_radius = $hotspot['stickerBorderRadius'] ?? $hotspot['hotspot-sticker-border-radius'];
                if ( is_array( $raw_radius ) ) {
                    $hs['hotspot-sticker-border-radius'] = [
                        'topLeft'     => max( 0, min( 500, (float) ( $raw_radius['topLeft'] ?? 20 ) ) ),
                        'topRight'    => max( 0, min( 500, (float) ( $raw_radius['topRight'] ?? 20 ) ) ),
                        'bottomRight' => max( 0, min( 500, (float) ( $raw_radius['bottomRight'] ?? 20 ) ) ),
                        'bottomLeft'  => max( 0, min( 500, (float) ( $raw_radius['bottomLeft'] ?? 20 ) ) ),
                    ];
                } else {
                    $val = max( 0, min( 500, (float) $raw_radius ) );
                    $hs['hotspot-sticker-border-radius'] = [
                        'topLeft'     => $val,
                        'topRight'    => $val,
                        'bottomRight' => $val,
                        'bottomLeft'  => $val,
                    ];
                }
            }
            if ( isset( $hotspot['stickerPadding'] ) || isset( $hotspot['hotspot-sticker-padding'] ) ) {
                $raw_pad = $hotspot['stickerPadding'] ?? $hotspot['hotspot-sticker-padding'];
                if ( is_array( $raw_pad ) ) {
                    $hs['hotspot-sticker-padding'] = [
                        'top'    => max( 0, min( 200, (float) ( $raw_pad['top'] ?? 21 ) ) ),
                        'right'  => max( 0, min( 200, (float) ( $raw_pad['right'] ?? 21 ) ) ),
                        'bottom' => max( 0, min( 200, (float) ( $raw_pad['bottom'] ?? 21 ) ) ),
                        'left'   => max( 0, min( 200, (float) ( $raw_pad['left'] ?? 21 ) ) ),
                    ];
                } else {
                    $val = max( 0, min( 200, (float) $raw_pad ) );
                    $hs['hotspot-sticker-padding'] = [
                        'top'    => $val,
                        'right'  => $val,
                        'bottom' => $val,
                        'left'   => $val,
                    ];
                }
            }
            if ( isset( $hotspot['stickerStarColor'] ) || isset( $hotspot['hotspot-sticker-star-color'] ) ) {
                $hs['hotspot-sticker-star-color'] = sanitize_hex_color( $hotspot['stickerStarColor'] ?? $hotspot['hotspot-sticker-star-color'] ) ?: '#EF991F';
            }
            // Discount button sticker fields
            if ( isset( $hotspot['stickerBtnText'] ) || isset( $hotspot['hotspot-sticker-btn-text'] ) ) {
                $hs['hotspot-sticker-btn-text'] = sanitize_text_field( $hotspot['stickerBtnText'] ?? $hotspot['hotspot-sticker-btn-text'] );
            }
            if ( isset( $hotspot['stickerBtnColor'] ) || isset( $hotspot['hotspot-sticker-btn-color'] ) ) {
                $hs['hotspot-sticker-btn-color'] = sanitize_hex_color( $hotspot['stickerBtnColor'] ?? $hotspot['hotspot-sticker-btn-color'] ) ?: '#EF991F';
            }
            if ( isset( $hotspot['stickerBtnTextColor'] ) || isset( $hotspot['hotspot-sticker-btn-text-color'] ) ) {
                $hs['hotspot-sticker-btn-text-color'] = sanitize_hex_color( $hotspot['stickerBtnTextColor'] ?? $hotspot['hotspot-sticker-btn-text-color'] ) ?: '#000000';
            }
            if ( isset( $hotspot['stickerBtnIconColor'] ) || isset( $hotspot['hotspot-sticker-btn-icon-color'] ) ) {
                $hs['hotspot-sticker-btn-icon-color'] = sanitize_hex_color( $hotspot['stickerBtnIconColor'] ?? $hotspot['hotspot-sticker-btn-icon-color'] ) ?: '#000000';
            }
            if ( isset( $hotspot['stickerBtnUrl'] ) || isset( $hotspot['hotspot-sticker-btn-url'] ) ) {
                $hs['hotspot-sticker-btn-url'] = esc_url_raw( $hotspot['stickerBtnUrl'] ?? $hotspot['hotspot-sticker-btn-url'] );
            }
            if ( isset( $hotspot['stickerBtnNewTab'] ) || isset( $hotspot['hotspot-sticker-btn-new-tab'] ) ) {
                $raw_new_tab = $hotspot['stickerBtnNewTab'] ?? $hotspot['hotspot-sticker-btn-new-tab'];
                $hs['hotspot-sticker-btn-new-tab'] = ( $raw_new_tab === 'on' || $raw_new_tab === true || $raw_new_tab === 'true' ) ? 'on' : 'off';
            }
            if ( isset( $hotspot['stickerMainBg'] ) || isset( $hotspot['hotspot-sticker-main-bg'] ) || isset( $hotspot['stickerCardBg'] ) || isset( $hotspot['hotspot-sticker-card-bg'] ) ) {
                $raw_main_bg = $hotspot['stickerMainBg'] ?? ( $hotspot['stickerCardBg'] ?? ( $hotspot['hotspot-sticker-main-bg'] ?? $hotspot['hotspot-sticker-card-bg'] ) );
                $val = ( $raw_main_bg === 'off' || $raw_main_bg === false || $raw_main_bg === 'false' ) ? 'off' : 'on';
                $hs['hotspot-sticker-main-bg'] = $val;
                $hs['hotspot-sticker-card-bg'] = $val;
            }
            if ( isset( $hotspot['stickerBtnBg'] ) || isset( $hotspot['hotspot-sticker-btn-bg'] ) ) {
                $raw_bg = $hotspot['stickerBtnBg'] ?? $hotspot['hotspot-sticker-btn-bg'];
                $hs['hotspot-sticker-btn-bg'] = ( $raw_bg === 'off' || $raw_bg === false || $raw_bg === 'false' ) ? 'off' : 'on';
            }
            if ( isset( $hotspot['stickerBtnWidth'] ) || isset( $hotspot['hotspot-sticker-btn-width'] ) ) {
                $raw_w = $hotspot['stickerBtnWidth'] ?? $hotspot['hotspot-sticker-btn-width'];
                $hs['hotspot-sticker-btn-width'] = ( $raw_w !== '' && $raw_w !== null ) ? sanitize_text_field( (string) $raw_w ) : '';
            }
            if ( isset( $hotspot['stickerBtnHeight'] ) || isset( $hotspot['hotspot-sticker-btn-height'] ) ) {
                $raw_h = $hotspot['stickerBtnHeight'] ?? $hotspot['hotspot-sticker-btn-height'];
                $hs['hotspot-sticker-btn-height'] = ( $raw_h !== '' && $raw_h !== null ) ? sanitize_text_field( (string) $raw_h ) : '';
            }
            if ( isset( $hotspot['stickerBtnRadius'] ) || isset( $hotspot['hotspot-sticker-btn-radius'] ) ) {
                $hs['hotspot-sticker-btn-radius'] = (float) ( $hotspot['stickerBtnRadius'] ?? $hotspot['hotspot-sticker-btn-radius'] );
            }
            if ( isset( $hotspot['stickerBtnBorder'] ) || isset( $hotspot['hotspot-sticker-btn-border'] ) ) {
                $hs['hotspot-sticker-btn-border'] = (float) ( $hotspot['stickerBtnBorder'] ?? $hotspot['hotspot-sticker-btn-border'] );
            }
            if ( isset( $hotspot['stickerBtnBorderColor'] ) || isset( $hotspot['hotspot-sticker-btn-border-color'] ) ) {
                $hs['hotspot-sticker-btn-border-color'] = sanitize_hex_color( $hotspot['stickerBtnBorderColor'] ?? $hotspot['hotspot-sticker-btn-border-color'] ) ?: '#EF991F';
            }
            if ( isset( $hotspot['stickerBtnTextSize'] ) || isset( $hotspot['hotspot-sticker-btn-text-size'] ) ) {
                $hs['hotspot-sticker-btn-text-size'] = (float) ( $hotspot['stickerBtnTextSize'] ?? $hotspot['hotspot-sticker-btn-text-size'] );
            }
            if ( isset( $hotspot['stickerBtnTextWeight'] ) || isset( $hotspot['hotspot-sticker-btn-text-weight'] ) ) {
                $hs['hotspot-sticker-btn-text-weight'] = sanitize_text_field( (string) ( $hotspot['stickerBtnTextWeight'] ?? $hotspot['hotspot-sticker-btn-text-weight'] ) );
            }
            if ( ( $hs['hotspot-sticker-template'] ?? '' ) === 'add_to_cart' || ( $hs['hotspot-sticker-template'] ?? '' ) === 'social_share' ) {
                $hs['hotspot-sticker-btn-icon'] = 'none';
            } elseif ( isset( $hotspot['stickerBtnIcon'] ) || isset( $hotspot['hotspot-sticker-btn-icon'] ) ) {
                $hs['hotspot-sticker-btn-icon'] = sanitize_text_field( $hotspot['stickerBtnIcon'] ?? $hotspot['hotspot-sticker-btn-icon'] );
            }
            // Add to cart text fields
            if ( isset( $hotspot['stickerPrefixText'] ) || isset( $hotspot['hotspot-sticker-prefix-text'] ) ) {
                $hs['hotspot-sticker-prefix-text'] = sanitize_text_field( $hotspot['stickerPrefixText'] ?? $hotspot['hotspot-sticker-prefix-text'] );
            }
            if ( isset( $hotspot['stickerSubText'] ) || isset( $hotspot['hotspot-sticker-sub-text'] ) ) {
                $hs['hotspot-sticker-sub-text'] = sanitize_text_field( $hotspot['stickerSubText'] ?? $hotspot['hotspot-sticker-sub-text'] );
            }
            // Button with separate icon fields
            if ( isset( $hotspot['stickerIconBoxBgColor'] ) || isset( $hotspot['hotspot-sticker-icon-box-bg-color'] ) ) {
                $hs['hotspot-sticker-icon-box-bg-color'] = sanitize_hex_color( $hotspot['stickerIconBoxBgColor'] ?? $hotspot['hotspot-sticker-icon-box-bg-color'] ) ?: '#ffffff';
            }
            if ( isset( $hotspot['stickerIconBoxRadius'] ) || isset( $hotspot['hotspot-sticker-icon-box-radius'] ) ) {
                $hs['hotspot-sticker-icon-box-radius'] = (float) ( $hotspot['stickerIconBoxRadius'] ?? $hotspot['hotspot-sticker-icon-box-radius'] );
            }
            if ( isset( $hotspot['stickerIconBoxBorder'] ) || isset( $hotspot['hotspot-sticker-icon-box-border'] ) ) {
                $hs['hotspot-sticker-icon-box-border'] = (float) ( $hotspot['stickerIconBoxBorder'] ?? $hotspot['hotspot-sticker-icon-box-border'] );
            }
            if ( isset( $hotspot['stickerIconBoxBorderColor'] ) || isset( $hotspot['hotspot-sticker-icon-box-border-color'] ) ) {
                $hs['hotspot-sticker-icon-box-border-color'] = sanitize_hex_color( $hotspot['stickerIconBoxBorderColor'] ?? $hotspot['hotspot-sticker-icon-box-border-color'] ) ?: '#ffffff';
            }
            if ( isset( $hotspot['stickerIconBoxSize'] ) || isset( $hotspot['hotspot-sticker-icon-box-size'] ) ) {
                $hs['hotspot-sticker-icon-box-size'] = (float) ( $hotspot['stickerIconBoxSize'] ?? $hotspot['hotspot-sticker-icon-box-size'] );
            }
            // Social share sticker fields
            if ( isset( $hotspot['stickerSocialColor'] ) || isset( $hotspot['hotspot-sticker-social-color'] ) ) {
                $hs['hotspot-sticker-social-color'] = sanitize_hex_color( $hotspot['stickerSocialColor'] ?? $hotspot['hotspot-sticker-social-color'] ) ?: '#ffffff';
            }
            if ( isset( $hotspot['stickerSocialSize'] ) || isset( $hotspot['hotspot-sticker-social-size'] ) ) {
                $hs['hotspot-sticker-social-size'] = (float) ( $hotspot['stickerSocialSize'] ?? $hotspot['hotspot-sticker-social-size'] );
            }
            if ( isset( $hotspot['stickerSocialGap'] ) || isset( $hotspot['hotspot-sticker-social-gap'] ) ) {
                $hs['hotspot-sticker-social-gap'] = (float) ( $hotspot['stickerSocialGap'] ?? $hotspot['hotspot-sticker-social-gap'] );
            }
            if ( isset( $hotspot['stickerSocialLinks'] ) || isset( $hotspot['hotspot-sticker-social-links'] ) ) {
                $raw_links = $hotspot['stickerSocialLinks'] ?? $hotspot['hotspot-sticker-social-links'];
                if ( is_string( $raw_links ) ) {
                    $raw_links = json_decode( $raw_links, true );
                }
                $clean_links = [];
                if ( is_array( $raw_links ) ) {
                    foreach ( $raw_links as $item ) {
                        if ( ! is_array( $item ) ) continue;
                        $clean_links[] = [
                            'id'         => sanitize_text_field( (string) ( $item['id'] ?? wp_generate_uuid4() ) ),
                            'icon'       => sanitize_text_field( (string) ( $item['icon'] ?? '' ) ),
                            'customSvg'  => function_exists( 'wp_kses' ) ? wp_kses( (string) ( $item['customSvg'] ?? '' ), [
                                'svg'    => [ 'class' => true, 'xmlns' => true, 'viewbox' => true, 'viewBox' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'style' => true ],
                                'path'   => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'style' => true ],
                                'g'      => [ 'fill' => true, 'stroke' => true, 'style' => true ],
                                'circle' => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'style' => true ],
                                'rect'   => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'style' => true ],
                            ] ) : (string) ( $item['customSvg'] ?? '' ),
                            'url'        => esc_url_raw( (string) ( $item['url'] ?? '' ) ),
                            'openNewTab' => ( ( $item['openNewTab'] ?? 'on' ) === 'off' || ( $item['openNewTab'] ?? 'on' ) === false ) ? 'off' : 'on',
                        ];
                    }
                }
                $hs['hotspot-sticker-social-links'] = $clean_links;
            }
            if ( isset( $hotspot['stickerShowPlaceholderText'] ) || isset( $hotspot['hotspot-sticker-show-placeholder-text'] ) ) {
                $hs['hotspot-sticker-show-placeholder-text'] = sanitize_text_field( $hotspot['stickerShowPlaceholderText'] ?? $hotspot['hotspot-sticker-show-placeholder-text'] );
            }
            if ( isset( $hotspot['scale'] ) ) {
                $hs['hotspot-scale'] = $hotspot['scale'] ? 'on' : 'off';
            } elseif ( ( $hotspot['type'] ?? '' ) === 'sticker' ) {
                $hs['hotspot-scale'] = 'on';
            }

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
                    'hotspot-scene-pitch' => ( isset( $hotspot['scenePitch'] ) && $hotspot['scenePitch'] !== null ) ? (string) $hotspot['scenePitch'] : '',
                    'hotspot-scene-yaw'   => ( isset( $hotspot['sceneYaw'] ) && $hotspot['sceneYaw'] !== null ) ? (string) $hotspot['sceneYaw'] : '',
                    'hotspot-scene-entry-point-mode' => in_array( $hotspot['sceneEntryPointMode'] ?? '', [ 'inherit', 'custom' ], true )
                        ? $hotspot['sceneEntryPointMode']
                        : 'inherit',
                ] );
            }

            $hotspot_list[] = $hs;
        }
        return $hotspot_list;
    }

    protected function text_overlays_to_api( array $list ): array {
        $overlays = [];
        foreach ( $list as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $overlays[] = [
                'id'         => (string) ( $item['id'] ?? ( 'overlay_' . wp_generate_uuid4() ) ),
                'name'       => sanitize_text_field( (string) ( $item['name'] ?? 'Text Overlay Layer' ) ),
                'enabled'    => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ),
                'template'   => in_array( $item['template'] ?? '', [ 'left', 'right', 'top', 'bottom', 'custom' ], true ) ? $item['template'] : '',
                'bgColor'    => sanitize_hex_color( $item['bgColor'] ?? '' ) ?: '#281E19',
                'bgOpacity'  => isset( $item['bgOpacity'] ) ? (float) $item['bgOpacity'] : 55,
                'blur'       => isset( $item['blur'] ) ? (float) $item['blur'] : 14,
                'brightness' => isset( $item['brightness'] ) ? (float) $item['brightness'] : 85,
                'text'       => TextOverlayContent::sanitize( (string) ( $item['text'] ?? '' ) ),
                'showClose'  => ! isset( $item['showClose'] ) || ! empty( $item['showClose'] ),
                'color'      => sanitize_hex_color( $item['color'] ?? '' ) ?: '#ffffff',
                'fontSize'   => isset( $item['fontSize'] ) ? (int) $item['fontSize'] : 18,
                'fontWeight' => in_array( $item['fontWeight'] ?? '', [ 'normal', 'bold' ], true ) ? $item['fontWeight'] : 'normal',
                'fontStyle'  => in_array( $item['fontStyle'] ?? '', [ 'normal', 'italic' ], true ) ? $item['fontStyle'] : 'normal',
                'textAlign'  => in_array( $item['textAlign'] ?? '', [ 'left', 'center', 'right' ], true ) ? $item['textAlign'] : 'left',
                'width'      => isset( $item['width'] ) ? (float) $item['width'] : ( isset( $item['position']['width'] ) ? (float) $item['position']['width'] : 75 ),
                'position'   => [
                    'x'     => isset( $item['position']['x'] ) ? (float) $item['position']['x'] : 10,
                    'y'     => isset( $item['position']['y'] ) ? (float) $item['position']['y'] : 15,
                    'width' => isset( $item['position']['width'] ) ? (float) $item['position']['width'] : ( isset( $item['width'] ) ? (float) $item['width'] : 75 ),
                ],
                'containerRect' => isset( $item['containerRect'] ) && is_array( $item['containerRect'] ) ? [
                    'top'    => isset( $item['containerRect']['top'] ) ? (float) $item['containerRect']['top'] : 20,
                    'left'   => isset( $item['containerRect']['left'] ) ? (float) $item['containerRect']['left'] : 20,
                    'width'  => isset( $item['containerRect']['width'] ) ? (float) $item['containerRect']['width'] : 60,
                    'height' => isset( $item['containerRect']['height'] ) ? (float) $item['containerRect']['height'] : 60,
                ] : null,
                'borderRadius' => isset( $item['borderRadius'] ) && is_array( $item['borderRadius'] ) ? [
                    'topLeft'     => isset( $item['borderRadius']['topLeft'] ) ? (float) $item['borderRadius']['topLeft'] : 0,
                    'topRight'    => isset( $item['borderRadius']['topRight'] ) ? (float) $item['borderRadius']['topRight'] : 0,
                    'bottomRight' => isset( $item['borderRadius']['bottomRight'] ) ? (float) $item['borderRadius']['bottomRight'] : 0,
                    'bottomLeft'  => isset( $item['borderRadius']['bottomLeft'] ) ? (float) $item['borderRadius']['bottomLeft'] : 0,
                ] : ( isset( $item['borderRadius'] ) && is_numeric( $item['borderRadius'] ) ? [
                    'topLeft'     => (float) $item['borderRadius'],
                    'topRight'    => (float) $item['borderRadius'],
                    'bottomRight' => (float) $item['borderRadius'],
                    'bottomLeft'  => (float) $item['borderRadius'],
                ] : [
                    'topLeft'     => 0,
                    'topRight'    => 0,
                    'bottomRight' => 0,
                    'bottomLeft'  => 0,
                ] ),
            ];
        }
        return array_slice( $overlays, 0, 5 );
    }

    protected function text_overlays_from_api( array $list ): array {
        $overlays = [];
        foreach ( $list as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $overlays[] = [
                'id'         => sanitize_text_field( (string) ( $item['id'] ?? '' ) ),
                'name'       => sanitize_text_field( (string) ( $item['name'] ?? 'Text Overlay Layer' ) ),
                'enabled'    => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ),
                'template'   => in_array( $item['template'] ?? '', [ 'left', 'right', 'top', 'bottom', 'custom' ], true ) ? $item['template'] : '',
                'bgColor'    => sanitize_hex_color( $item['bgColor'] ?? '' ) ?: '#281E19',
                'bgOpacity'  => isset( $item['bgOpacity'] ) ? (float) $item['bgOpacity'] : 55,
                'blur'       => isset( $item['blur'] ) ? (float) $item['blur'] : 14,
                'brightness' => isset( $item['brightness'] ) ? (float) $item['brightness'] : 85,
                'text'       => TextOverlayContent::sanitize( (string) ( $item['text'] ?? '' ) ),
                'showClose'  => ! isset( $item['showClose'] ) || ! empty( $item['showClose'] ),
                'color'      => sanitize_hex_color( $item['color'] ?? '' ) ?: '#ffffff',
                'fontSize'   => isset( $item['fontSize'] ) ? (int) $item['fontSize'] : 18,
                'fontWeight' => in_array( $item['fontWeight'] ?? '', [ 'normal', 'bold' ], true ) ? $item['fontWeight'] : 'normal',
                'fontStyle'  => in_array( $item['fontStyle'] ?? '', [ 'normal', 'italic' ], true ) ? $item['fontStyle'] : 'normal',
                'textAlign'  => in_array( $item['textAlign'] ?? '', [ 'left', 'center', 'right' ], true ) ? $item['textAlign'] : 'left',
                'width'      => isset( $item['width'] ) ? (float) $item['width'] : ( isset( $item['position']['width'] ) ? (float) $item['position']['width'] : 75 ),
                'position'   => [
                    'x'     => isset( $item['position']['x'] ) ? (float) $item['position']['x'] : 10,
                    'y'     => isset( $item['position']['y'] ) ? (float) $item['position']['y'] : 15,
                    'width' => isset( $item['position']['width'] ) ? (float) $item['position']['width'] : ( isset( $item['width'] ) ? (float) $item['width'] : 75 ),
                ],
                'containerRect' => isset( $item['containerRect'] ) && is_array( $item['containerRect'] ) ? [
                    'top'    => isset( $item['containerRect']['top'] ) ? (float) $item['containerRect']['top'] : 20,
                    'left'   => isset( $item['containerRect']['left'] ) ? (float) $item['containerRect']['left'] : 20,
                    'width'  => isset( $item['containerRect']['width'] ) ? (float) $item['containerRect']['width'] : 60,
                    'height' => isset( $item['containerRect']['height'] ) ? (float) $item['containerRect']['height'] : 60,
                ] : null,
                'borderRadius' => isset( $item['borderRadius'] ) && is_array( $item['borderRadius'] ) ? [
                    'topLeft'     => isset( $item['borderRadius']['topLeft'] ) ? (float) $item['borderRadius']['topLeft'] : 0,
                    'topRight'    => isset( $item['borderRadius']['topRight'] ) ? (float) $item['borderRadius']['topRight'] : 0,
                    'bottomRight' => isset( $item['borderRadius']['bottomRight'] ) ? (float) $item['borderRadius']['bottomRight'] : 0,
                    'bottomLeft'  => isset( $item['borderRadius']['bottomLeft'] ) ? (float) $item['borderRadius']['bottomLeft'] : 0,
                ] : ( isset( $item['borderRadius'] ) && is_numeric( $item['borderRadius'] ) ? [
                    'topLeft'     => (float) $item['borderRadius'],
                    'topRight'    => (float) $item['borderRadius'],
                    'bottomRight' => (float) $item['borderRadius'],
                    'bottomLeft'  => (float) $item['borderRadius'],
                ] : [
                    'topLeft'     => 0,
                    'topRight'    => 0,
                    'bottomRight' => 0,
                    'bottomLeft'  => 0,
                ] ),
            ];
        }
        return array_slice( $overlays, 0, 5 );
    }
}
