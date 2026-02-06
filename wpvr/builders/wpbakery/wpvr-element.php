<?php
/**
 * WPVR – Virtual Tour WPBakery Element
 *
 * Place this file at: /builders/wpbakery/wpvr-element.php
 * @since 8.5.48
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register VC element
 * @since 8.5.48
 */
add_action( 'vc_before_init', 'wpvr_vc_map_wpvr_virtual_tour' );



function wpvr_vc_map_wpvr_virtual_tour() {
    if ( ! function_exists( 'vc_map' ) ) {
        return;
    }
    
    $tours = array( __( 'Select a tour', 'wpvr' ) => '' );
    $items = get_posts( array(
        'post_type'      => 'wpvr_item',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    if ( $items ) {
        foreach ( $items as $p ) {
            $tours[ $p->post_title . ' (ID:' . $p->ID . ')' ] = $p->ID;
        }
    }

    vc_map( array(
        'name'        => __( 'WPVR - Virtual Tour', 'wpvr' ),
        'base'        => 'wpvr_virtual_tour',
        'category'    => __( 'WPVR', 'wpvr' ),
        'icon'        => plugins_url( 'images/icon.png', dirname(dirname(__FILE__)) ), // Correct icon path
        'description' => __( 'Embed a WPVR virtual tour with full analytics and tracking support.', 'wpvr' ),

        'params' => array(

            // TOUR DROPDOWN.
            array(
                'type'        => 'dropdown',
                'heading'     => __( 'Select Tour', 'wpvr' ),
                'param_name'  => 'id',
                'value'       => $tours,
                'admin_label' => true,
            ),

            // WIDTH VALUE.
            array(
                'type'        => 'textfield',
                'heading'     => __( 'Width (number only)', 'wpvr' ),
                'param_name'  => 'width_value',
                'value'       => '600',
            ),

            // WIDTH UNIT.
            array(
                'type'       => 'dropdown',
                'heading'    => __( 'Width Unit', 'wpvr' ),
                'param_name' => 'width_unit',
                'value'      => array(
                    'px'        => 'px',
                    '%'         => '%',
                    'vw'        => 'vw',
                    'Fullwidth' => 'fullwidth',
                ),
                'std' => 'px',
            ),

            // HEIGHT VALUE.
            array(
                'type'        => 'textfield',
                'heading'     => __( 'Height (number only)', 'wpvr' ),
                'param_name'  => 'height_value',
                'value'       => '400',
            ),

            // HEIGHT UNIT
            array(
                'type'       => 'dropdown',
                'heading'    => __( 'Height Unit', 'wpvr' ),
                'param_name' => 'height_unit',
                'value'      => array(
                    'px' => 'px',
                    'vh' => 'vh',
                ),
                'std' => 'px',
            ),

            // MOBILE HEIGHT VALUE
            array(
                'type'        => 'textfield',
                'heading'     => __( 'Mobile Height (number only)', 'wpvr' ),
                'param_name'  => 'height_secondary_value',
                'value'       => '',
            ),

            // MOBILE HEIGHT UNIT
            array(
                'type'       => 'dropdown',
                'heading'    => __( 'Mobile Height Unit', 'wpvr' ),
                'param_name' => 'height_secondary_unit',
                'value'      => array(
                    'px' => 'px',
                    'vh' => 'vh',
                ),
                'std' => 'px',
            ),
        ),
    ));
}


/**
 * Shortcode class (for backend & frontend editors)
 * @since 8.5.48
 */
if ( ! class_exists( 'WPBakeryShortCode_Wpvr_Virtual_Tour' ) ) {

    class WPBakeryShortCode_Wpvr_Virtual_Tour extends WPBakeryShortCode {

        protected function content( $atts, $content = null ) {

            $atts = shortcode_atts( array(
                'id'                     => '',
                'width_value'            => '600',
                'width_unit'             => 'px',
                'height_value'           => '400',
                'height_unit'            => 'px',
                'height_secondary_value' => '',
                'height_secondary_unit'  => 'px',
                'options'                => array(),
                'el_class'               => '',
            ), $atts, 'wpvr_virtual_tour' );

            // Tour ID.
            $tour_id = intval( $atts['id'] );
            if ( $tour_id <= 0 ) {
                return '<div class="wpvr-vc-error">WPVR: Please select a tour.</div>';
            }

            // WIDTH.
            if ( $atts['width_unit'] === 'fullwidth' ) {
                $width = '100%';
            } else {
                $width = esc_attr( trim( $atts['width_value'] ) . $atts['width_unit'] );
            }

            // HEIGHT.
            $height = esc_attr( trim( $atts['height_value'] ) . $atts['height_unit'] );

            // OPTIONS.
            $opt_attrs = array();
            if ( ! empty( $atts['options'] ) ) {
                foreach ( $atts['options'] as $o ) {
                    $opt_attrs[] = $o . '="true"';
                }
            }

            // Shortcode assembly.
            $shortcode = '[wpvr id="' . $tour_id . '" width="' . $width . '" height="' . $height . '"';
            if ( ! empty( $opt_attrs ) ) {
                $shortcode .= ' ' . implode( ' ', $opt_attrs );
            }
            $shortcode .= ']';

            // If WPVR disabled → fallback.
            if ( ! shortcode_exists( 'wpvr' ) ) {
                return '<div class="wpvr-fallback">WPVR plugin is not active.</div>';
            }

            // Secondary height saved as data-attr for theme usage (not part of shortcode).
            $data_attr = '';
            if ( $atts['height_secondary_value'] !== '' ) {
                $data_attr = ' data-height-secondary="' .
                             esc_attr( $atts['height_secondary_value'] . $atts['height_secondary_unit'] ) . '"';
            }

            // Output wrapper.
            $out  = '<div class="wpvr-vc-wrapper ' . esc_attr( $atts['el_class'] ) . '"' . $data_attr . '>';
            $out .= do_shortcode( $shortcode );
            $out .= '</div>';

            return $out;
        }
    }
}
