<?php

/**
 * Server-side renderer for the WP VR Breakdance element.
 *
 * @var array $propertiesData
 */

$settings = $propertiesData['content']['wpvr'] ?? [];
$tour_id  = absint( $settings['tour'] ?? 0 );

if ( ! $tour_id ) {
    echo '<p>' . esc_html__( 'No tour has been selected.', 'wpvr' ) . '</p>';
    return true;
}

$to_pixels = static function ( $value, $default ) {
    if ( '' === $value || null === $value || ! is_numeric( $value ) ) {
        $value = $default;
    }

    return max( 0, (float) $value ) . 'px';
};

$width         = $to_pixels( $settings['width'] ?? null, 600 );
$height        = $to_pixels( $settings['height'] ?? null, 400 );
$mobile_height = $to_pixels( $settings['mobile_height'] ?? null, 300 );
$radius        = $to_pixels( $settings['radius'] ?? null, 0 );

$shortcode = sprintf(
    '[wpvr id="%d" width="%s" height="%s" mobile_height="%s" radius="%s"]',
    $tour_id,
    esc_attr( $width ),
    esc_attr( $height ),
    esc_attr( $mobile_height ),
    esc_attr( $radius )
);

$html = do_shortcode( $shortcode );

if ( function_exists( '\Breakdance\isRequestFromBuilderSsr' ) && \Breakdance\isRequestFromBuilderSsr() ) {
    // Scripts inserted through Breakdance's AJAX SSR are inert. The element's
    // builder action executes these after its preview dependencies are ready.
    $html = str_replace(
        '<script>',
        '<script type="application/wpvr-breakdance-preview">',
        $html
    );
}

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

return true;
