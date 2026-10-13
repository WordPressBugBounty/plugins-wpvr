<?php

namespace RexTheme\WPVR\Content;

/** Source selection is separate from the legacy video player type. */
class VideoSource {
    public static function platform( string $url ): string {
        $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
        foreach ( [ 'youtube.com' => 'youtube', 'youtube-nocookie.com' => 'youtube', 'youtu.be' => 'youtube', 'vimeo.com' => 'vimeo' ] as $domain => $type ) {
            if ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
                return $type;
            }
        }
        return 'selfhost';
    }

    public static function normalize( $source, string $url ): string {
        return in_array( $source, [ 'upload', 'url', 'cdn' ], true )
            ? $source
            : ( self::platform( $url ) === 'selfhost' ? 'upload' : 'url' );
    }

    public static function player_type( string $url, $source = '' ): string {
        return self::normalize( $source, $url ) === 'cdn' ? 'selfhost' : self::platform( $url );
    }

    /** Validate syntax locally; browser verification checks actual media access. */
    public static function validate( $url, $source ) {
        if ( ! is_string( $url ) ) {
            return new \WP_Error( 'invalid_video_url', __( 'Please enter a valid video URL.', 'wpvr' ) );
        }
        $url = trim( $url );
        if ( $source !== '' && $source !== null && ! in_array( $source, [ 'upload', 'url', 'cdn' ], true ) ) {
            return new \WP_Error( 'invalid_video_source', __( 'Please select a valid video source.', 'wpvr' ) );
        }
        if ( $url === '' ) {
            return null; // Preserve empty video drafts.
        }
        $parts = wp_parse_url( $url );
        if ( ! is_array( $parts ) || empty( $parts['host'] ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), [ 'http', 'https' ], true ) || ! esc_url_raw( $url, [ 'http', 'https' ] ) ) {
            return new \WP_Error( 'invalid_video_url', __( 'Please enter a valid HTTP or HTTPS video URL.', 'wpvr' ) );
        }
        $source = self::normalize( $source, $url );
        if ( $source === 'url' && self::platform( $url ) === 'selfhost' ) {
            return new \WP_Error( 'invalid_video_platform', __( 'Please enter a YouTube or Vimeo URL, or select CDN URL for a direct video file.', 'wpvr' ) );
        }
        if ( $source === 'cdn' ) {
            if ( is_ssl() && strtolower( $parts['scheme'] ) !== 'https' ) {
                return new \WP_Error( 'video_mixed_content', __( 'Use an HTTPS video URL on an HTTPS site.', 'wpvr' ) );
            }
            $extension = strtolower( pathinfo( $parts['path'] ?? '', PATHINFO_EXTENSION ) );
            if ( self::platform( $url ) !== 'selfhost' || in_array( $extension, [ 'webm', 'ogg', 'ogv', 'mov', 'avi', 'm3u8', 'mpd', 'html', 'htm' ], true ) ) {
                return new \WP_Error( 'invalid_cdn_video', __( 'Please use a direct 360° MP4 video URL for CDN playback.', 'wpvr' ) );
            }
        }
        return null;
    }

    public static function enqueue_verifier(): void {
        $path = WPVR_PLUGIN_LEGACY_DIR_PATH . 'admin/js/wpvr-video-url.js';
        wp_enqueue_script( 'wpvr-video-url', plugin_dir_url( WPVR_FILE ) . 'legacy/admin/js/wpvr-video-url.js', [ 'jquery', 'wp-i18n' ], filemtime( $path ), true );
    }
}
