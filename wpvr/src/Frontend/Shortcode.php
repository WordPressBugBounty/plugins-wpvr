<?php

namespace RexTheme\WPVR\Frontend;

/**
 * Standalone wrapper for the legacy WPVR_Shortcode class.
 *
 * Registers the [wpvr] shortcode via the legacy class.
 * When using Frontend\Frontend, Wpvr_Public already creates WPVR_Shortcode internally;
 * instantiate this class only when you need the shortcode without the full public stack.
 */
class Shortcode {

    private $legacy;
    private $legacy_video;

    public function __construct() {
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'public/class-wpvr-public.php';
        require_once WPVR_PLUGIN_LEGACY_DIR_PATH . 'public/classes/class-wpvr-shortcode.php';
        $this->legacy       = new \WPVR_Shortcode( 'wpvr' );
        $this->legacy_video = new \WPVR_Video();
        add_shortcode( 'wpvr', [ $this, 'wpvr_shortcode' ] );
    }

    public function wpvr_shortcode( $atts ) {
        $parsed = shortcode_atts( [ 'id' => 0, 'slug' => '', 'width' => null, 'height' => null, 'radius' => null ], $atts );
        $id     = (int) $parsed['id'];

        if ( ! $id && ! empty( $parsed['slug'] ) ) {
            $obj = get_page_by_path( sanitize_text_field( $parsed['slug'] ), OBJECT, 'wpvr_item' );
            $id  = $obj ? $obj->ID : 0;
        }

        if ( $id ) {
            $postdata  = get_post_meta( $id, 'panodata', true );
            $postdata  = is_array( $postdata ) ? wpvr_get_effective_panodata( $postdata ) : [];
            $tour_type = is_array( $postdata ) ? ( $postdata['tour-type'] ?? '' ) : '';

            if ( $tour_type === 'street-view' && ! isset( $postdata['streetviewdata'] ) ) {
                return $this->render_streetview( $postdata, $parsed );
            }

            if ( $tour_type === 'video' && ! isset( $postdata['vidid'] ) ) {
                return $this->render_video( $postdata, $parsed, $id );
            }
        }

        // Pass original $atts so legacy handler can extract all its own params (width, height, radius…).
        return $this->legacy->wpvr_shortcode( $atts );
    }

    private function render_streetview( array $postdata, array $atts ) {
        $width  = ! empty( $atts['width'] )  ? esc_attr( $atts['width'] )  : '600px';
        $height = ! empty( $atts['height'] ) ? esc_attr( $atts['height'] ) : '400px';
        $url    = esc_url( $postdata['streetviewurl'] ?? '' );

        return '<div class="vr-streetview" style="text-align:center;max-width:100%;width:' . $width . ';height:' . $height . ';margin:0 auto;">'
            . '<iframe src="' . $url . '" frameborder="0" style="border:0;width:100%;height:100%;" allowfullscreen=""></iframe>'
            . '</div>';
    }

    private function render_video( array $postdata, array $atts, int $id ) {
        $width  = ! empty( $atts['width'] )  ? esc_attr( $atts['width'] )  : '600px';
        $height = ! empty( $atts['height'] ) ? esc_attr( $atts['height'] ) : '400px';
        $radius = ! empty( $atts['radius'] ) ? esc_attr( $atts['radius'] ) : null;

        // New UI stores video-autoplay / video-loop; legacy renderer expects autoplay / loop.
        $postdata['autoplay'] = $postdata['video-autoplay'] ?? 'off';
        $postdata['loop']     = $postdata['video-loop']     ?? 'off';

        return $this->legacy_video->render_video_shortcode( $postdata, $id, $width, $height, $radius );
    }
}
