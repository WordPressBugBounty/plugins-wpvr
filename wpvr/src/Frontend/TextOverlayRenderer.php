<?php

namespace RexTheme\WPVR\Frontend;

use RexTheme\WPVR\Content\TextOverlayContent;

/**
 * TextOverlayRenderer
 *
 * Handles frontend rendering of text overlay layers on virtual tours,
 * including multi-layer overlays, predefined & custom templates, custom corner
 * border radii, backdrop blur/brightness filters, close/reopen controls,
 * and seamless Pannellum viewer synchronization.
 *
 * @package RexTheme\WPVR\Frontend
 * @since   9.2.0
 */
class TextOverlayRenderer {

    /**
     * Predefined template bounding geometries (in percentage).
     *
     * @var array<string, array{top: int, left: int, width: int, height: int}>
     */
    const PREDEFINED_TEMPLATES = [
        'left'   => [ 'top' => 0, 'left' => 0, 'width' => 50, 'height' => 100 ],
        'right'  => [ 'top' => 0, 'left' => 50, 'width' => 50, 'height' => 100 ],
        'top'    => [ 'top' => 0, 'left' => 0, 'width' => 100, 'height' => 50 ],
        'bottom' => [ 'top' => 50, 'left' => 0, 'width' => 100, 'height' => 50 ],
    ];

    /**
     * Register filter hooks for frontend tour rendering.
     *
     * @return void
     */
    public static function init() {
        if ( ! has_filter( 'wpvr_generate_tour_layout_html', [ __CLASS__, 'render' ] ) ) {
            add_filter( 'wpvr_generate_tour_layout_html', [ __CLASS__, 'render' ], 25, 3 );
        }
    }

    /**
     * Convert HEX color and opacity percentage to RGBA CSS string.
     *
     * @param string    $hex             Hex color code (e.g. #281E19 or 281E19).
     * @param float|int $opacity_percent Opacity percentage (0 to 100).
     *
     * @return string CSS rgba(...) color string.
     */
    public static function hex_to_rgba( $hex, $opacity_percent = 55 ) {
        $hex = ltrim( (string) $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( strlen( $hex ) !== 6 ) {
            $hex = '281E19';
        }

        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        $a = round( max( 0, min( 1, (float) $opacity_percent / 100 ) ), 2 );

        return "rgba({$r}, {$g}, {$b}, {$a})";
    }

    /**
     * Filter callback to inject text overlay layers into tour layout HTML.
     *
     * @param string $html     Generated tour HTML.
     * @param array  $postdata Saved tour settings.
     * @param int    $id       Tour post ID.
     *
     * @return string Modified HTML with text overlays, scoped CSS, and companion JS.
     */
    public static function render( $html, $postdata, $id ) {
        // Resolve panorama DOM element ID
        if ( preg_match( '/id=[\'"](pano' . absint( $id ) . '(?:_\d+)?)[\'"]/', $html, $m ) ) {
            $pano_id = $m[1];
        } else {
            $pano_id = 'pano' . absint( $id );
        }

        // Retrieve scenes list
        $scenes = self::extract_scenes( $postdata, $id );
        if ( empty( $scenes ) ) {
            return $html;
        }

        // Identify default scene ID
        $default_scene_id = self::get_default_scene_id( $scenes, $postdata );

        // Extract valid active overlays grouped by scene
        $overlays_by_scene = self::get_valid_overlays_by_scene( $scenes );
        if ( empty( $overlays_by_scene ) ) {
            return $html;
        }

        // Detect Tour Layout (layout1 is Modern, default/others are Classic)
        $tour_layout = is_array( $postdata['tourLayout'] ?? null )
            ? ( $postdata['tourLayout']['layout'] ?? 'default' )
            : ( $postdata['tourLayout'] ?? 'default' );
        $is_modern = ( 'layout1' === $tour_layout );
        $layout_bg_color = is_array( $postdata['tourLayout'] ?? null ) && ! empty( $postdata['tourLayout']['layout_icon_bg_color'] )
            ? sanitize_hex_color( $postdata['tourLayout']['layout_icon_bg_color'] ) ?: '#5a536e'
            : '#5a536e';
        $layout_icon_color = is_array( $postdata['tourLayout'] ?? null ) && ! empty( $postdata['tourLayout']['layout_icon_color'] )
            ? sanitize_hex_color( $postdata['tourLayout']['layout_icon_color'] ) ?: '#ffffff'
            : '#ffffff';

        // Generate overlay containers, reopen buttons, and bottom-right toggle button
        $markup = self::render_markup( $overlays_by_scene, $pano_id, $default_scene_id, $is_modern );

        $wrapper_html = '<div id="wpvr-text-overlays-' . esc_attr( $pano_id ) . '" class="wpvr-frontend-text-overlays" data-pano-id="' . esc_attr( $pano_id ) . '">'
            . $markup['overlays']
            . $markup['reopen_buttons']
            . $markup['toggle_button']
            . '</div>';

        $scene_toggle_config = [];
        foreach ( $scenes as $sc ) {
            $sc_id = (string) ( $sc['scene-id'] ?? $sc['id'] ?? '' );
            if ( $sc_id !== '' ) {
                $toggle_val = $sc['scene-show-layer-toggle'] ?? $sc['showLayerToggle'] ?? 'on';
                $scene_toggle_config[ $sc_id ] = ( $toggle_val !== 'off' && $toggle_val !== false && $toggle_val !== 0 && $toggle_val !== '0' );
            }
        }

        $style_html  = self::render_styles( $pano_id, $is_modern, $layout_bg_color, $layout_icon_color );
        $script_html = self::render_script( $pano_id, $default_scene_id, $is_modern, $scene_toggle_config );

        // Insert wrapper directly into the container or append
        $pattern = '/(<div\s+id=[\'"]' . preg_quote( $pano_id, '/' ) . '[\'"][^>]*>)/i';
        if ( preg_match( $pattern, $html ) ) {
            $html = preg_replace( $pattern, '$1' . $wrapper_html, $html, 1 );
        } else {
            $html .= $wrapper_html;
        }

        $html .= $style_html . "\n" . $script_html;

        return $html;
    }

    /**
     * Extract scenes from postdata or database post meta.
     *
     * @param array $postdata Tour post data array.
     * @param int   $id       Tour post ID.
     *
     * @return array List of scenes.
     */
    public static function extract_scenes( $postdata, $id ) {
        if ( ! empty( $postdata['panodata']['scene-list'] ) && is_array( $postdata['panodata']['scene-list'] ) ) {
            return $postdata['panodata']['scene-list'];
        }

        if ( ! empty( $postdata['scene-list'] ) && is_array( $postdata['scene-list'] ) ) {
            return $postdata['scene-list'];
        }

        if ( ! empty( $postdata['panoscenes'] ) && is_array( $postdata['panoscenes'] ) ) {
            return $postdata['panoscenes'];
        }

        $meta = get_post_meta( $id, 'panodata', true );
        if ( is_array( $meta ) ) {
            if ( ! empty( $meta['panodata']['scene-list'] ) && is_array( $meta['panodata']['scene-list'] ) ) {
                return $meta['panodata']['scene-list'];
            }
            if ( ! empty( $meta['scene-list'] ) && is_array( $meta['scene-list'] ) ) {
                return $meta['scene-list'];
            }
        }

        return [];
    }

    /**
     * Determine the initial default scene ID.
     *
     * @param array $scenes   List of scenes.
     * @param array $postdata Tour postdata array.
     *
     * @return string Default scene ID.
     */
    public static function get_default_scene_id( array $scenes, array $postdata ) {
        $default_scene_id = $postdata['defaultscene'] ?? '';

        if ( empty( $default_scene_id ) ) {
            foreach ( $scenes as $sc ) {
                if ( ( $sc['dscene'] ?? 'off' ) === 'on' && ! empty( $sc['scene-id'] ) ) {
                    $default_scene_id = (string) $sc['scene-id'];
                    break;
                }
            }
        }

        if ( empty( $default_scene_id ) && ! empty( $scenes ) ) {
            $first            = reset( $scenes );
            $default_scene_id = (string) ( $first['scene-id'] ?? $first['id'] ?? '' );
        }

        return $default_scene_id;
    }

    /**
     * Filter and group valid enabled overlays by scene ID.
     *
     * @param array $scenes List of scenes.
     *
     * @return array<string, array> Grouped overlays by scene ID.
     */
    public static function get_valid_overlays_by_scene( array $scenes ) {
        $overlays_by_scene = [];

        foreach ( $scenes as $scene ) {
            $scene_id = (string) ( $scene['scene-id'] ?? $scene['id'] ?? '' );
            if ( $scene_id === '' ) {
                continue;
            }

            $raw_overlays = $scene['text-overlays'] ?? $scene['textOverlays'] ?? [];
            if ( is_string( $raw_overlays ) ) {
                $raw_overlays = json_decode( $raw_overlays, true ) ?: [];
            }
            if ( ! is_array( $raw_overlays ) ) {
                continue;
            }

            foreach ( $raw_overlays as $overlay ) {
                if ( ! is_array( $overlay ) ) {
                    continue;
                }

                // Check enabled flag
                $is_enabled = ! isset( $overlay['enabled'] ) || ! empty( $overlay['enabled'] );
                if ( ! $is_enabled || $overlay['enabled'] === 'false' || $overlay['enabled'] === false ) {
                    continue;
                }

                // Check valid template
                $template = $overlay['template'] ?? '';
                if ( ! in_array( $template, [ 'left', 'right', 'top', 'bottom', 'custom' ], true ) ) {
                    continue;
                }

                // If custom template, require valid containerRect
                if ( $template === 'custom' && ( empty( $overlay['containerRect'] ) || ! is_array( $overlay['containerRect'] ) ) ) {
                    continue;
                }

                // Ignore empty rich text, including editor-only paragraphs and spaces.
                $overlay['text'] = TextOverlayContent::sanitize( (string) ( $overlay['text'] ?? '' ) );
                $plain_text = html_entity_decode( wp_strip_all_tags( $overlay['text'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                $plain_text = preg_replace( '/[\s\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $plain_text );
                if ( $plain_text === '' && ! preg_match( '/<(?:img|video|audio|iframe|svg|canvas|object|embed|hr)\b/i', $overlay['text'] ) ) {
                    continue;
                }

                if ( ! isset( $overlays_by_scene[ $scene_id ] ) ) {
                    $overlays_by_scene[ $scene_id ] = [];
                }

                $overlays_by_scene[ $scene_id ][] = $overlay;
            }
        }

        return $overlays_by_scene;
    }

    /**
     * Render the overlay DOM elements, reopen buttons, and bottom-right toggle button HTML.
     *
     * @param array  $overlays_by_scene Overlays grouped by scene ID.
     * @param string $pano_id           Panorama container ID.
     * @param string $default_scene_id  Default scene ID.
     * @param bool   $is_modern         Whether current tour layout is Modern (layout1).
     *
     * @return array{overlays: string, reopen_buttons: string, toggle_button: string}
     */
    protected static function render_markup( array $overlays_by_scene, $pano_id, $default_scene_id, $is_modern = false ) {
        $overlays_html       = '';
        $reopen_buttons_html = '';

        foreach ( $overlays_by_scene as $scene_id => $scene_overlays ) {
            $total_overlays = count( $scene_overlays );
            foreach ( $scene_overlays as $idx => $overlay ) {
                $overlay_id   = esc_attr( (string) ( $overlay['id'] ?? wp_generate_uuid4() ) );
                $overlay_name = sanitize_text_field( $overlay['name'] ?? 'Text Overlay' );
                $template     = $overlay['template'];
                $z_index      = 20 - max( 0, (int) $idx );

                // Container rect
                if ( $template === 'custom' || ! empty( $overlay['containerRect'] ) ) {
                    $c_rect = $overlay['containerRect'] ?? [];
                    $rect   = [
                        'top'    => max( 0, min( 100, (float) ( $c_rect['top'] ?? 20 ) ) ),
                        'left'   => max( 0, min( 100, (float) ( $c_rect['left'] ?? 20 ) ) ),
                        'width'  => max( 5, min( 100, (float) ( $c_rect['width'] ?? 60 ) ) ),
                        'height' => max( 5, min( 100, (float) ( $c_rect['height'] ?? 60 ) ) ),
                    ];
                } else {
                    $rect = self::PREDEFINED_TEMPLATES[ $template ] ?? [ 'top' => 0, 'left' => 0, 'width' => 50, 'height' => 100 ];
                }

                // Background color & opacity
                $bg_color   = sanitize_hex_color( $overlay['bgColor'] ?? '' ) ?: '#281E19';
                $bg_opacity = isset( $overlay['bgOpacity'] ) ? (float) $overlay['bgOpacity'] : 55.0;
                $bg_opacity = max( 0, min( 100, $bg_opacity ) );
                $rgba_bg    = self::hex_to_rgba( $bg_color, $bg_opacity );

                // Blur & brightness
                $blur             = max( 0, min( 40, isset( $overlay['blur'] ) ? (float) $overlay['blur'] : 14.0 ) );
                $brightness       = max( 0, min( 200, isset( $overlay['brightness'] ) ? (float) $overlay['brightness'] : 85.0 ) );
                $brightness_ratio = round( $brightness / 100, 2 );

                // Corner border radius
                $raw_radius = $overlay['borderRadius'] ?? 0;
                $tl = 0; $tr = 0; $br = 0; $bl = 0;
                if ( is_array( $raw_radius ) ) {
                    $tl = max( 0, (float) ( $raw_radius['topLeft'] ?? 0 ) );
                    $tr = max( 0, (float) ( $raw_radius['topRight'] ?? 0 ) );
                    $br = max( 0, (float) ( $raw_radius['bottomRight'] ?? 0 ) );
                    $bl = max( 0, (float) ( $raw_radius['bottomLeft'] ?? 0 ) );
                } elseif ( is_numeric( $raw_radius ) ) {
                    $val = max( 0, (float) $raw_radius );
                    $tl  = $tr = $br = $bl = $val;
                }
                $border_radius_css = "{$tl}px {$tr}px {$br}px {$bl}px";

                // Text inner positioning & clamping
                $pos_x = isset( $overlay['position']['x'] ) ? (float) $overlay['position']['x'] : 10.0;
                $pos_y = isset( $overlay['position']['y'] ) ? (float) $overlay['position']['y'] : 15.0;
                $width = isset( $overlay['width'] )
                    ? (float) $overlay['width']
                    : ( isset( $overlay['position']['width'] ) ? (float) $overlay['position']['width'] : 75.0 );

                $pos_x         = max( 0, min( 95, $pos_x ) );
                $pos_y         = max( 0, min( 95, $pos_y ) );
                $clamped_width = max( 5, min( 100 - $pos_x, $width ) );

                // Rich text HTML
                $text_html = $overlay['text'];

                // Close button setting
                $show_close = ! isset( $overlay['showClose'] ) || ! empty( $overlay['showClose'] );

                // Initial display state matching default scene
                $is_initial_scene = ( $scene_id === $default_scene_id );
                $initial_display  = $is_initial_scene ? 'block' : 'none';

                $text_align = in_array( $overlay['textAlign'] ?? '', [ 'left', 'center', 'right' ], true ) ? $overlay['textAlign'] : 'left';

                $container_style = 'display: ' . $initial_display . ';'
                    . ' z-index: ' . $z_index . ';'
                    . ' --wpvr-overlay-z-index: ' . $z_index . ';'
                    . ' --wpvr-overlay-close-inset: ' . ( 12 + $tr * 0.3 ) . 'px;'
                    . ' top: ' . $rect['top'] . '%;'
                    . ' left: ' . $rect['left'] . '%;'
                    . ' width: ' . $rect['width'] . '%;'
                    . ' height: ' . $rect['height'] . '%;'
                    . ' background: ' . $rgba_bg . ';'
                    . ' backdrop-filter: blur(' . $blur . 'px) brightness(' . $brightness_ratio . ');'
                    . ' -webkit-backdrop-filter: blur(' . $blur . 'px) brightness(' . $brightness_ratio . ');'
                    . ' border-radius: ' . $border_radius_css . ';'
                    . ' text-align: ' . $text_align . ';';

                $content_style = 'left: ' . $pos_x . '%;'
                    . ' top: ' . $pos_y . '%;'
                    . ' width: ' . $clamped_width . '%;'
                    . ' max-width: calc(100% - ' . $pos_x . '%);'
                    . ' text-align: ' . $text_align . ';';

                $close_btn_html = '';
                if ( $show_close ) {
                    $close_btn_html = '<button type="button" class="wpvr-text-overlay-close-btn" data-overlay-id="' . $overlay_id . '" aria-label="' . esc_attr__( 'Close overlay', 'wpvr' ) . '">'
                        . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                        . '</button>';

                    $reopen_pos_style = 'top: ' . max( 15, min( 85, $rect['top'] + 2 ) ) . '%; left: ' . max( 15, min( 85, $rect['left'] + 2 ) ) . '%;';
                    if ( $template === 'right' ) {
                        $reopen_pos_style = 'top: 20px; right: 20px;';
                    } elseif ( $template === 'left' ) {
                        $reopen_pos_style = 'top: 20px; left: 70px;';
                    } elseif ( $template === 'top' ) {
                        $reopen_pos_style = 'top: 20px; left: 70px;';
                    } elseif ( $template === 'bottom' ) {
                        $reopen_pos_style = 'bottom: 85px; left: 95px;';
                    }

                    $reopen_buttons_html .= '<button type="button" class="wpvr-text-overlay-reopen-btn" data-overlay-id="' . $overlay_id . '" data-scene-id="' . esc_attr( $scene_id ) . '" style="display: none; ' . $reopen_pos_style . '" aria-label="' . esc_attr__( 'Show overlay', 'wpvr' ) . '">'
                        . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
                        . '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>'
                        . '<polyline points="14 2 14 8 20 8"></polyline>'
                        . '<line x1="16" y1="13" x2="8" y2="13"></line>'
                        . '<line x1="16" y1="17" x2="8" y2="17"></line>'
                        . '</svg>'
                        . '<span>' . esc_html( $overlay_name ) . '</span>'
                        . '</button>';
                }

                $overlays_html .= '<div id="wpvr-overlay-' . esc_attr( $pano_id ) . '-' . $overlay_id . '" class="wpvr-frontend-text-overlay wpvr-frontend-text-overlay--template-' . esc_attr( $template ) . '" data-overlay-id="' . $overlay_id . '" data-scene-id="' . esc_attr( $scene_id ) . '" style="' . $container_style . '">'
                    . $close_btn_html
                    . '<div class="wpvr-frontend-text-overlay__body" style="border-radius: ' . $border_radius_css . ';">'
                    . '<div class="wpvr-frontend-text-overlay__content" style="' . $content_style . '">'
                    . $text_html
                    . '</div>'
                    . '</div>'
                    . '</div>';
            }
        }

        $layout_class = $is_modern ? 'wpvr-text-overlay-toggle-btn--modern' : 'wpvr-text-overlay-toggle-btn--classic';
        $toggle_btn_html = '<button type="button" id="wpvr-text-overlay-toggle-' . esc_attr( $pano_id ) . '" class="wpvr-text-overlay-toggle-btn ' . esc_attr( $layout_class ) . '" aria-label="' . esc_attr__( 'Hide text overlay', 'wpvr' ) . '" title="' . esc_attr__( 'Hide text overlay', 'wpvr' ) . '" style="display: none;">'
            . '<svg class="wpvr-toggle-icon-visible" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>'
            . '<circle cx="12" cy="12" r="3"></circle>'
            . '</svg>'
            . '<svg class="wpvr-toggle-icon-hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">'
            . '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>'
            . '<line x1="1" y1="1" x2="23" y2="23"></line>'
            . '</svg>'
            . '</button>';

        return [
            'overlays'       => $overlays_html,
            'reopen_buttons' => $reopen_buttons_html,
            'toggle_button'  => $toggle_btn_html,
        ];
    }

    /**
     * Render the scoped CSS for the text overlay layer and show/hide controls.
     *
     * @param string $pano_id           Panorama container ID.
     * @param bool   $is_modern         Whether modern tour layout is active.
     * @param string $layout_bg_color   Tour layout background color.
     * @param string $layout_icon_color Tour layout icon color.
     *
     * @return string CSS style block.
     */
    protected static function render_styles( $pano_id, $is_modern = false, $layout_bg_color = '#5a536e', $layout_icon_color = '#ffffff' ) {
        $p = '#' . esc_attr( $pano_id );
        $bg_color_css   = esc_attr( $layout_bg_color );
        $icon_color_css = esc_attr( $layout_icon_color );

        return '<style id="wpvr-text-overlays-style-' . esc_attr( $pano_id ) . '">
            /* Text alignment defaults */
            ' . $p . ' .wpvr-frontend-text-overlay,
            ' . $p . ' .wpvr-frontend-text-overlay__body,
            ' . $p . ' .wpvr-frontend-text-overlay__content {
                text-align: left !important;
            }
            ' . $p . ' .wpvr-frontend-text-overlay__content p:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h1:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h2:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h3:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h4:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h5:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content h6:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content div:not([style*="text-align"]),
            ' . $p . ' .wpvr-frontend-text-overlay__content span:not([style*="text-align"]) {
                text-align: left !important;
            }

            /* Hotspots must remain behind text overlays */
            ' . $p . ' .pnlm-hotspot-base,
            ' . $p . ' .pnlm-hotspot,
            ' . $p . ' .pnlm-tooltip {
                z-index: 2 !important;
            }

            /* Text overlays layer */
            ' . $p . ' .wpvr-frontend-text-overlays {
                z-index: 10 !important;
            }
            ' . $p . ' .wpvr-frontend-text-overlay {
                z-index: var(--wpvr-overlay-z-index, 10) !important;
            }

            /* Pannellum UI overlay container and drag surface stay below text overlays and controls */
            ' . $p . ' .pnlm-ui {
                z-index: auto !important;
            }
            ' . $p . ' .pnlm-dragfix {
                z-index: 1 !important;
                pointer-events: auto !important;
            }

            /* Tour control buttons & other tour-related buttons have preference over overlay */
            ' . $p . ' .pnlm-controls-container,
            ' . $p . ' .pnlm-controls-container *,
            ' . $p . ' .pnlm-controls,
            ' . $p . ' .pnlm-controls *,
            ' . $p . ' .pnlm-control,
            ' . $p . ' .pnlm-control *,
            ' . $p . ' .pnlm-zoom-controls,
            ' . $p . ' .pnlm-zoom-controls *,
            ' . $p . ' .pnlm-fullscreen-toggle-button,
            ' . $p . ' .pnlm-orientation-button,
            ' . $p . ' .pnlm-compass,
            ' . $p . ' .pnlm-load-button,
            ' . $p . ' .pnlm-load-box,
            ' . $p . ' .pnlm-panorama-info,
            ' . $p . ' .zoom-in-out-controls,
            ' . $p . ' .zoom-in-out-controls *,
            ' . $p . ' .controls,
            ' . $p . ' .controls *,
            ' . $p . ' .ctrl,
            ' . $p . ' .ctrl *,
            ' . $p . ' .explainer_button,
            ' . $p . ' .explainer_button *,
            ' . $p . ' .floor_map_button,
            ' . $p . ' .floor_map_button *,
            ' . $p . ' .generic_form_button,
            ' . $p . ' .generic_form_button *,
            ' . $p . ' .adcontrol,
            ' . $p . ' .adcontrol *,
            ' . $p . ' .audio_control,
            ' . $p . ' .audio_control *,
            ' . $p . ' #cp-logo-controls,
            ' . $p . ' #cp-logo-controls *,
            ' . $p . ' .cp-logo-ctrl,
            ' . $p . ' .cp-logo-ctrl *,
            ' . $p . ' .scene-gallery,
            ' . $p . ' .scene-gallery *,
            ' . $p . ' .vrowl-carousel,
            ' . $p . ' .vrowl-carousel *,
            ' . $p . ' .wpvr_slider_nav,
            ' . $p . ' .wpvr_slider_nav *,
            ' . $p . ' .wpvr_owl_prev,
            ' . $p . ' .wpvr_owl_prev *,
            ' . $p . ' .wpvr_owl_next,
            ' . $p . ' .wpvr_owl_next *,
            ' . $p . ' .vrgcontrols,
            ' . $p . ' .vrgcontrols *,
            ' . $p . ' .wpvr-scene-info-row,
            ' . $p . ' .wpvr-cardboard-switcher,
            ' . $p . ' .wpvr-cardboard-switcher *,
            ' . $p . ' .wpvr-call-to-action-button,
            ' . $p . ' .fullscreen-button,
            ' . $p . ' .vr-pointer-container {
                z-index: 40 !important;
                pointer-events: auto !important;
            }

            /* Overlay action buttons (close and reopen) */
            ' . $p . ' .wpvr-text-overlay-close-btn,
            ' . $p . ' .wpvr-text-overlay-reopen-btn {
                z-index: 40 !important;
                pointer-events: auto !important;
            }

            /* Show/Hide Text Overlay Toggle Button - Common */
            ' . $p . ' .wpvr-text-overlay-toggle-btn {
                position: absolute;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                z-index: 40 !important;
                pointer-events: auto !important;
                cursor: pointer !important;
                outline: none;
                padding: 0;
                margin: 0;
                box-sizing: border-box;
                -webkit-tap-highlight-color: transparent;
                user-select: none;
                -webkit-user-select: none;
            }

            /* Show/Hide Toggle Button - Modern Layout (matches layout1 Scene UI) */
            ' . $p . ' .wpvr-text-overlay-toggle-btn--modern {
                width: 40px;
                height: 40px;
                border-radius: 50% !important;
                background-color: ' . $bg_color_css . ' !important;
                color: ' . $icon_color_css . ' !important;
                border: none !important;
                box-shadow: 0px 3px 3px 0px rgba(0, 0, 0, 0.16) !important;
                bottom: 45px;
                right: 140px;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            ' . $p . ' .wpvr-text-overlay-toggle-btn--modern:hover {
                transform: scale(1.08);
            }
            ' . $p . ' .wpvr-text-overlay-toggle-btn--modern svg {
                width: 20px;
                height: 20px;
                stroke: ' . $icon_color_css . ';
            }

            /* Show/Hide Toggle Button - Classic Layout (matches Classic Explainer Video Button UI & size) */
            ' . $p . ' .wpvr-text-overlay-toggle-btn--classic {
                width: 30px !important;
                height: 30px !important;
                border-radius: 0 !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #f7fffb !important;
                border: none !important;
                box-shadow: none !important;
                bottom: 49px;
                right: 110px;
                filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.85)) !important;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            ' . $p . ' .wpvr-text-overlay-toggle-btn--classic:hover {
                background: transparent !important;
                background-color: transparent !important;
                transform: scale(1.15) !important;
                opacity: 0.9 !important;
            }
            ' . $p . ' .wpvr-text-overlay-toggle-btn--classic:focus,
            ' . $p . ' .wpvr-text-overlay-toggle-btn--classic:active {
                outline: none !important;
                background: transparent !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }
            ' . $p . ' .wpvr-text-overlay-toggle-btn--classic svg {
                width: 18px !important;
                height: 18px !important;
                stroke: #f7fffb !important;
                fill: none !important;
            }

            /* Responsive styles for mobile devices */
            @media (max-width: 767px) {
                ' . $p . ' .wpvr-text-overlay-toggle-btn--modern {
                    width: 25px !important;
                    height: 25px !important;
                    bottom: 42px;
                    right: 95px;
                }
                ' . $p . ' .wpvr-text-overlay-toggle-btn--modern svg {
                    width: 14px !important;
                    height: 14px !important;
                }

                ' . $p . ' .wpvr-text-overlay-toggle-btn--classic {
                    width: 26px !important;
                    height: 26px !important;
                    bottom: 49px;
                    right: 95px;
                }
                ' . $p . ' .wpvr-text-overlay-toggle-btn--classic svg {
                    width: 16px !important;
                    height: 16px !important;
                }
            }

            /* Scene Navigation Menu (Hamburger) & Dropdown */
            #master-container .custom-scene-navigation,
            #master-container ' . $p . ' .custom-scene-navigation,
            ' . $p . ' .custom-scene-navigation,
            ' . $p . ' [id^="custom-scene-navigation"],
            .custom-scene-navigation {
                z-index: 40 !important;
                pointer-events: auto !important;
                cursor: pointer !important;
            }

            ' . $p . ' .custom-scene-navigation .hamburger-menu,
            ' . $p . ' .custom-scene-navigation .hamburger-menu svg,
            ' . $p . ' .custom-scene-navigation .hamburger-menu svg rect {
                pointer-events: auto !important;
                cursor: pointer !important;
            }

            #master-container .custom-scene-navigation-nav,
            #master-container ' . $p . ' .custom-scene-navigation-nav,
            ' . $p . ' .custom-scene-navigation-nav,
            ' . $p . ' [id^="custom-scene-navigation-nav"],
            .custom-scene-navigation-nav {
                z-index: 50 !important;
                pointer-events: auto !important;
            }

            ' . $p . ' .custom-scene-navigation-nav ul,
            ' . $p . ' .custom-scene-navigation-nav ul .scene-navigation-list {
                pointer-events: auto !important;
                cursor: pointer !important;
            }
        </style>';
    }

    /**
     * Render companion script for scene switching, show/hide toggle, close, and Pannellum event isolation.
     *
     * @param string $pano_id          Panorama container ID.
     * @param string $default_scene_id Default scene ID.
     * @param bool   $is_modern        Whether modern tour layout is active.
     *
     * @return string Script block.
     */
    protected static function render_script( $pano_id, $default_scene_id, $is_modern = false, array $scene_toggle_config = [] ) {
        $pano_id_json          = wp_json_encode( $pano_id );
        $default_scene_id_json = wp_json_encode( $default_scene_id );
        $is_modern_json        = wp_json_encode( (bool) $is_modern );
        $scene_toggle_json     = wp_json_encode( (object) $scene_toggle_config );

        return '<script id="wpvr-text-overlays-script-' . esc_attr( $pano_id ) . '">
            (function () {
                var panoId = ' . $pano_id_json . ';
                var defaultSceneId = ' . $default_scene_id_json . ';
                var isModern = ' . $is_modern_json . ';
                var sceneToggleConfig = ' . $scene_toggle_json . ';

                function initTextOverlays() {
                    var pano = document.getElementById(panoId);
                    if (!pano) return;

                    var wrap = document.getElementById("wpvr-text-overlays-" + panoId);
                    if (wrap && wrap.parentNode !== pano) {
                        pano.appendChild(wrap);
                    }

                    var toggleBtn = document.getElementById("wpvr-text-overlay-toggle-" + panoId);

                    function insertToggleBtn() {
                        if (!toggleBtn || !pano) return;
                        var compass = pano.querySelector(".pnlm-compass");
                        if (compass && compass.parentNode) {
                            if (toggleBtn.parentNode !== compass.parentNode || toggleBtn.nextSibling !== compass) {
                                compass.parentNode.insertBefore(toggleBtn, compass);
                            }
                        } else {
                            var uiContainer = pano.querySelector(".pnlm-ui");
                            var targetParent = uiContainer || pano;
                            if (toggleBtn.parentNode !== targetParent) {
                                targetParent.insertBefore(toggleBtn, targetParent.firstChild);
                            }
                        }
                        updateToggleBtnPosition();
                    }

                    function updateToggleBtnPosition() {
                        if (!toggleBtn || !pano) return;

                        var isMobile = window.innerWidth <= 767;
                        var gap = isModern ? (isMobile ? 8 : 10) : 10;

                        // Check all bottom-right controls so we position before the leftmost one
                        var selectors = [
                            ".pnlm-compass",
                            ".explainer_button",
                            ".floor_map_button",
                            ".adcontrol",
                            ".pnlm-controls-container"
                        ];
                        if (isModern) {
                            selectors.push(".controls");
                        }

                        var maxRightEdge = 0;
                        var matchedBottom = null;

                        for (var i = 0; i < selectors.length; i++) {
                            var el = pano.querySelector(selectors[i]);
                            if (!el) continue;
                            var style = window.getComputedStyle(el);
                            if (style.display === "none" || style.visibility === "hidden") continue;

                            var r = parseFloat(style.right);
                            var w = parseFloat(style.width);
                            var b = style.bottom;

                            var panoW = pano.offsetWidth || 1000;
                            if (!isNaN(r) && r >= 0 && r < panoW * 0.7) {
                                var fallbackWidth = isModern ? 40 : (el.classList.contains("explainer_button") || el.classList.contains("floor_map_button") ? 50 : 30);
                                var elemWidth = (!isNaN(w) && w > 0) ? w : (el.offsetWidth || fallbackWidth);
                                var rightEdge = r + elemWidth;
                                if (rightEdge > maxRightEdge) {
                                    maxRightEdge = rightEdge;
                                    matchedBottom = b;
                                }
                            }
                        }

                        var defaultBottom = isModern ? (isMobile ? "42px" : "45px") : "49px";
                        var finalBottom = matchedBottom || defaultBottom;

                        if (maxRightEdge > 0) {
                            toggleBtn.style.right = (maxRightEdge + gap) + "px";
                            toggleBtn.style.bottom = finalBottom;
                        } else {
                            toggleBtn.style.right = isModern ? (isMobile ? "20px" : "40px") : "15px";
                            toggleBtn.style.bottom = defaultBottom;
                        }

                        // Dimensions: on Classic layout, match explainer button size (30px / 26px mobile)
                        if (!isModern) {
                            var classicSize = isMobile ? "26px" : "30px";
                            toggleBtn.style.width = classicSize;
                            toggleBtn.style.height = classicSize;
                        }
                    }

                    function setToggleState(isVisible) {
                        if (!toggleBtn) return;
                        var iconVisible = toggleBtn.querySelector(".wpvr-toggle-icon-visible");
                        var iconHidden = toggleBtn.querySelector(".wpvr-toggle-icon-hidden");
                        if (isVisible) {
                            if (iconVisible) iconVisible.style.display = "block";
                            if (iconHidden) iconHidden.style.display = "none";
                            toggleBtn.setAttribute("title", "Hide text overlay");
                            toggleBtn.setAttribute("aria-label", "Hide text overlay");
                            toggleBtn.classList.remove("wpvr-toggle-btn--hidden");
                            toggleBtn.classList.add("wpvr-toggle-btn--visible");
                        } else {
                            if (iconVisible) iconVisible.style.display = "none";
                            if (iconHidden) iconHidden.style.display = "block";
                            toggleBtn.setAttribute("title", "Show text overlay");
                            toggleBtn.setAttribute("aria-label", "Show text overlay");
                            toggleBtn.classList.remove("wpvr-toggle-btn--visible");
                            toggleBtn.classList.add("wpvr-toggle-btn--hidden");
                        }
                    }

                    var isLifting = false;
                    function liftPannellumControls() {
                        if (!pano || isLifting) return;
                        isLifting = true;
                        try {
                            var pnlmControls = pano.querySelector(".pnlm-ui > .pnlm-controls-container");
                            if (pnlmControls && pnlmControls.parentNode !== pano) {
                                pano.appendChild(pnlmControls);
                            }
                            var pnlmCompass = pano.querySelector(".pnlm-ui > .pnlm-compass");
                            if (pnlmCompass && pnlmCompass.parentNode !== pano) {
                                pano.appendChild(pnlmCompass);
                            }
                            var pnlmInfo = pano.querySelector(".pnlm-ui > .pnlm-panorama-info");
                            if (pnlmInfo && pnlmInfo.parentNode !== pano) {
                                pano.appendChild(pnlmInfo);
                            }
                            insertToggleBtn();
                        } finally {
                            isLifting = false;
                        }
                    }

                    liftPannellumControls();
                    insertToggleBtn();

                    var activeSceneId = defaultSceneId;

                    function syncOverlays(sceneId) {
                        if (!sceneId) return;
                        activeSceneId = sceneId;

                        insertToggleBtn();

                        var allOverlays = pano.querySelectorAll(".wpvr-frontend-text-overlay[data-scene-id]");
                        var sceneOverlaysCount = 0;
                        var anyVisible = false;

                        for (var i = 0; i < allOverlays.length; i++) {
                            var el = allOverlays[i];
                            var overlayScene = el.getAttribute("data-scene-id");
                            var overlayId = el.getAttribute("data-overlay-id");

                            if (overlayScene === sceneId) {
                                sceneOverlaysCount++;
                                var isClosed = el.getAttribute("data-closed") === "true";
                                if (isClosed) {
                                    el.style.display = "none";
                                } else {
                                    el.style.display = "block";
                                    anyVisible = true;
                                }
                            } else {
                                el.style.display = "none";
                            }
                        }

                        if (toggleBtn) {
                            var isToggleAllowed = sceneToggleConfig && sceneToggleConfig[sceneId] !== false;
                            if (sceneOverlaysCount > 0 && isToggleAllowed) {
                                toggleBtn.style.display = "inline-flex";
                                setToggleState(anyVisible);
                                updateToggleBtnPosition();
                            } else {
                                toggleBtn.style.display = "none";
                            }
                        }
                    }

                    function attachViewer(viewer) {
                        if (!viewer || viewer._wpvrTextOverlaysBound) return;
                        viewer._wpvrTextOverlaysBound = true;
                        liftPannellumControls();

                        viewer.on("scenechange", function (newSceneId) {
                            syncOverlays(newSceneId);
                            liftPannellumControls();
                        });
                        viewer.on("load", function () {
                            var s = viewer.getScene();
                            if (s) syncOverlays(s);
                            liftPannellumControls();
                        });

                        var initial = viewer.getScene();
                        if (initial) {
                            syncOverlays(initial);
                        }
                    }

                    if (window.wpvrViewers && window.wpvrViewers[panoId]) {
                        attachViewer(window.wpvrViewers[panoId]);
                    }

                    document.addEventListener("wpvr:viewer-ready", function (e) {
                        if (e.detail && e.detail.containerId === panoId && e.detail.viewer) {
                            attachViewer(e.detail.viewer);
                        }
                    });

                    if (window.jQuery) {
                        window.jQuery(document).on("wpvr_scene_changed", function (e, scId) {
                            syncOverlays(scId);
                        });
                    }

                    window.addEventListener("resize", updateToggleBtnPosition);

                    var attempts = 0;
                    var checkInterval = setInterval(function () {
                        attempts++;
                        liftPannellumControls();
                        if (window.wpvrViewers && window.wpvrViewers[panoId]) {
                            attachViewer(window.wpvrViewers[panoId]);
                            clearInterval(checkInterval);
                        } else if (attempts > 40) {
                            clearInterval(checkInterval);
                        }
                    }, 100);

                    if (window.MutationObserver) {
                        var obs = new MutationObserver(function () {
                            if (!isLifting) {
                                liftPannellumControls();
                            }
                        });
                        obs.observe(pano, { childList: true });
                    }

                    syncOverlays(defaultSceneId);

                    // Show/Hide toggle button click handler
                    if (toggleBtn) {
                        toggleBtn.addEventListener("click", function (e) {
                            e.stopPropagation();
                            e.preventDefault();
                            var sceneOverlays = pano.querySelectorAll(\'.wpvr-frontend-text-overlay[data-scene-id="\' + activeSceneId + \'"]\');
                            if (!sceneOverlays.length) return;

                            var anyVisible = false;
                            for (var i = 0; i < sceneOverlays.length; i++) {
                                if (sceneOverlays[i].getAttribute("data-closed") !== "true" && sceneOverlays[i].style.display !== "none") {
                                    anyVisible = true;
                                    break;
                                }
                            }

                            if (anyVisible) {
                                for (var j = 0; j < sceneOverlays.length; j++) {
                                    sceneOverlays[j].setAttribute("data-closed", "true");
                                    sceneOverlays[j].style.display = "none";
                                }
                                setToggleState(false);
                            } else {
                                for (var k = 0; k < sceneOverlays.length; k++) {
                                    sceneOverlays[k].removeAttribute("data-closed");
                                    sceneOverlays[k].style.display = "block";
                                }
                                setToggleState(true);
                            }
                        });
                    }

                    // Window resize repositioning
                    window.addEventListener("resize", updateToggleBtnPosition);

                    // Prevent dragging panorama when interacting with overlay or toggle button
                    var stopEvents = ["mousedown", "pointerdown", "touchstart", "touchmove", "wheel", "dblclick"];
                    var stopFn = function (e) {
                        e.stopPropagation();
                    };

                    var stopTargets = pano.querySelectorAll(".wpvr-frontend-text-overlay, .wpvr-text-overlay-reopen-btn, .wpvr-text-overlay-toggle-btn");
                    for (var j = 0; j < stopTargets.length; j++) {
                        for (var k = 0; k < stopEvents.length; k++) {
                            stopTargets[j].addEventListener(stopEvents[k], stopFn, { passive: stopEvents[k].indexOf("touch") === 0 || stopEvents[k] === "wheel" });
                        }
                    }

                    // Close buttons
                    var closeButtons = pano.querySelectorAll(".wpvr-text-overlay-close-btn");
                    for (var c = 0; c < closeButtons.length; c++) {
                        closeButtons[c].addEventListener("click", function (e) {
                            e.stopPropagation();
                            e.preventDefault();
                            var overlayId = this.getAttribute("data-overlay-id");
                            var overlayEl = pano.querySelector(\'.wpvr-frontend-text-overlay[data-overlay-id="\' + overlayId + \'"]\');
                            if (overlayEl) {
                                overlayEl.setAttribute("data-closed", "true");
                                overlayEl.style.display = "none";
                            }
                            var sceneOverlays = pano.querySelectorAll(\'.wpvr-frontend-text-overlay[data-scene-id="\' + activeSceneId + \'"]\');
                            var anyVisible = false;
                            for (var s = 0; s < sceneOverlays.length; s++) {
                                if (sceneOverlays[s].getAttribute("data-closed") !== "true" && sceneOverlays[s].style.display !== "none") {
                                    anyVisible = true;
                                    break;
                                }
                            }
                            setToggleState(anyVisible);
                        });
                    }

                    // Reopen buttons (if present)
                    var reopenButtons = pano.querySelectorAll(".wpvr-text-overlay-reopen-btn");
                    for (var r = 0; r < reopenButtons.length; r++) {
                        reopenButtons[r].addEventListener("click", function (e) {
                            e.stopPropagation();
                            e.preventDefault();
                            var overlayId = this.getAttribute("data-overlay-id");
                            var overlayEl = pano.querySelector(\'.wpvr-frontend-text-overlay[data-overlay-id="\' + overlayId + \'"]\');
                            if (overlayEl) {
                                overlayEl.removeAttribute("data-closed");
                                overlayEl.style.display = "block";
                            }
                            this.style.display = "none";
                            setToggleState(true);
                        });
                    }
                }

                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", initTextOverlays);
                } else {
                    initTextOverlays();
                }
            }());
        </script>';
    }
}
