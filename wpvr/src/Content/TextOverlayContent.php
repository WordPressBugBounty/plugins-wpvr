<?php

namespace RexTheme\WPVR\Content;

/**
 * Sanitize overlay rich text without stripping the editor's video embeds.
 */
class TextOverlayContent {

    public static function sanitize( string $html ): string {
        $allowed_html = wp_kses_allowed_html( 'post' );

        // YouTube, Vimeo and other iframe players inserted through Media or HTML.
        // Keep srcdoc and inline event handlers excluded.
        $allowed_html['iframe'] = [
            'src'                   => true,
            'width'                 => true,
            'height'                => true,
            'title'                 => true,
            'class'                 => true,
            'style'                 => true,
            'frameborder'           => true,
            'allow'                 => true,
            'allowfullscreen'       => true,
            'webkitallowfullscreen' => true,
            'mozallowfullscreen'    => true,
            'referrerpolicy'        => true,
            'loading'               => true,
            'sandbox'               => true,
        ];

        // TinyMCE uses child sources for direct video URLs and alternative formats.
        $allowed_html['source'] = [
            'src'   => true,
            'type'  => true,
            'media' => true,
        ];
        // Native video/audio tags, playback attributes and caption tracks use
        // the standard post allowlist above.
        return wp_kses( $html, $allowed_html );
    }
}
