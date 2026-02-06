<?php
/**
 * Class Rex_WPVR_Telemetry
 *
 * Handles telemetry tracking for the WPVR plugin.
 *
 * @since 8.5.48
 */

class Rex_WPVR_Telemetry {

    /**
     * Rex_WPVR_Telemetry constructor.
     *
     * Initialize telemetry hooks for the plugin.
     *
     * @since 8.5.48
     */
    public function __construct() {
        add_action( 'wpvr_plugin_activated', array( $this, 'track_plugin_activation' ));
        add_action( 'wpvr_plugin_deactivated', array( $this, 'track_plugin_deactivation' ));
        add_action( 'transition_post_status', array( $this, 'track_first_tour_published' ), 10, 3);
        add_action( 'rex_wpvr_tour_created', array( $this, 'track_tour_created' ), 10, 1);
        add_action( 'current_screen', array( $this, 'track_page_view' ) );
        add_action( 'rex_wpvr_embadded_tour', array( $this, 'track_embadded_tour' ), 10, 1 );
        add_action( 'rex_wpvr_tour_saved', array( $this, 'track_detailed_tour_events' ), 10, 1 );
    }

    /**
     * Track detailed tour events on save/published
     *
     * @param int $post_id
     * @since 8.5.48
     */
    public function track_detailed_tour_events( $post_id ) {
        $this->track_tour_type_event( $post_id );
        $this->track_feature_used_events( $post_id );
    }

    /**
     * Track tour type
     *
     * @param int $post_id
     */
    private function track_tour_type_event( $post_id ) {
        $tour_type = $this->get_tour_type( $post_id );
        
        coderex_telemetry_track(
            WPVR_FILE,
            'tour_type',
            array(
                'tour_type' => $tour_type,
            )
        );
    }

    /**
     * Get tour type from post meta
     *
     * @param int $post_id
     * @return string
     */
    private function get_tour_type( $post_id ) {
        $panodata = get_post_meta( $post_id, 'panodata', true );

        $post_panovideo  = isset( $_POST['panovideo'] ) ? sanitize_text_field( wp_unslash( $_POST['panovideo'] ) ) : '';
        $post_streetview = isset( $_POST['streetview'] ) ? sanitize_text_field( wp_unslash( $_POST['streetview'] ) ) : '';

        if ( $post_panovideo === 'on' || ( isset( $panodata['vidid'] ) && ! empty( $panodata['vidid'] ) ) ) {
            return '360 Video Tour';
        }

        if ( $post_streetview === 'on' || ( isset( $panodata['streetview'] ) && ! empty( $panodata['streetview'] ) ) ) {
            return 'Google Street View';
        }

        if ( ! empty( $panodata['panodata']['scene-list'] ) ) {
            foreach ( $panodata['panodata']['scene-list'] as $scene ) {
                if ( isset( $scene['scene-type'] ) && $scene['scene-type'] === 'cubemap' ) {
                    return 'Cubemap Tour';
                }
            }
        }

        // Default to image tour
        return '360 Image Tour';
    }

    /**
     * Track feature used events
     *
     * @param int $post_id
     */
    private function track_feature_used_events( $post_id ) {
        $features = $this->get_enabled_features( $post_id );
        
        foreach ( $features as $feature_name ) {
            coderex_telemetry_track(
                WPVR_FILE,
                'feature_used',
                array(
                    'feature_name' => $feature_name,
                )
            );
        }
    }

    /**
     * Identify all enabled features for a tour
     *
     * @param int $post_id
     * @return array
     */
    private function get_enabled_features( $post_id ) {
        $enabled_features = array();
        $panodata = get_post_meta( $post_id, 'panodata', true );
        
        $post_panovideo    = isset( $_POST['panovideo'] ) ? sanitize_text_field( wp_unslash( $_POST['panovideo'] ) ) : '';
        $post_streetview   = isset( $_POST['wpvrStreetView'] ) ? sanitize_text_field( wp_unslash( $_POST['wpvrStreetView'] ) ) : '';
        $is_video_tour     = $post_panovideo === 'on';
        $is_street_view    = $post_streetview === 'on';
        
        // Feature Mapping Detection
        $feature_map = array(
            'Auto Rotation'           => 'autorotation',
            'Tour Autoload'           => 'autoload',
            'Control Buttons'         => 'control',
            'Gyroscope Control'       => 'gyro',
            'Auto Gyroscope'          => 'deviceorientationcontrol',
            'Scene Gallery'           => 'vrgallery',
            'Scene Navigation Menu'   => 'vrscene_navigation',
            'Compass'                 => 'compass',
            'Background Music'        => 'bg_music',
            'Company Logo'            => 'cpLogoSwitch',
            'Explainer Video'         => 'explainerSwitch',
            'Background Tour'         => 'wpvr_bg_tour_enabler',
            'Generic Form (Contact)'  => 'genericform',
            'Call To Action Button'   => 'button_enable',
            'Custom CSS'              => 'custom_css_switch',
            'Global Zoom Limit'       => 'gzoom',
            'Floor Plan'              => 'wpvr_floor_plan_enabler',
        );

        foreach ( $feature_map as $readable_name => $post_key ) {
            if ( $is_street_view && 'autoload' === $post_key ) {
                continue;
            }

            if ( isset( $_POST[$post_key] ) ) {
                $val = sanitize_text_field( wp_unslash( $_POST[$post_key] ) );
                if ( $val === 'on' || $val === '1' ) {
                    $enabled_features[] = $readable_name;
                }
            }
        }

        // Global Scene Transition
        if ( isset( $_POST['scenefadeduration'] ) ) {
            $scenefadeduration = floatval( wp_unslash( $_POST['scenefadeduration'] ) );
            if ( $scenefadeduration > 0 ) {
                $enabled_features[] = 'Scene Transition (Fade)';
            }
        }

        // Add Video Specific Features
        if ( $is_video_tour ) {
            if ( isset( $_POST['autoplay'] ) && sanitize_text_field( wp_unslash( $_POST['autoplay'] ) ) === 'on' ) {
                $enabled_features[] = 'Video Autoplay';
            }
            if ( isset( $_POST['loop'] ) && sanitize_text_field( wp_unslash( $_POST['loop'] ) ) === 'on' ) {
                $enabled_features[] = 'Video Loop';
            }
            
            // Detect Video Source
            if ( isset( $_POST['videourl'] ) && ! empty( $_POST['videourl'] ) ) {
                $video_url = esc_url_raw( wp_unslash( $_POST['videourl'] ) );
                $video_source = 'Self-hosted Video'; // Default
                
                if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {
                    $video_source = 'YouTube Video';
                } elseif ( strpos( $video_url, 'vimeo.com' ) !== false ) {
                    $video_source = 'Vimeo Video';
                } elseif ( strpos( $video_url, 'instagram.com' ) !== false ) {
                    $video_source = 'Instagram Video';
                } elseif ( strpos( $video_url, 'facebook.com' ) !== false || strpos( $video_url, 'fb.watch' ) !== false ) {
                    $video_source = 'Facebook Video';
                }
                
                $enabled_features[] = $video_source;
            }
        }

        // Layouts (Skip for Video Tour and Street View)
        if ( ! $is_video_tour && ! $is_street_view && isset( $_POST['tourLayout'] ) ) {
            $tourLayout = sanitize_text_field( wp_unslash( $_POST['tourLayout'] ) );
            if ( $tourLayout === 'default' ) {
                $enabled_features[] = 'Classic Layout';
            } elseif ( $tourLayout === 'layout1' ) {
                $enabled_features[] = 'Modern Layout';
            }
        }

        // Detailed JSON data checks
        if ( ! empty( $_POST['panodata'] ) ) {
            $post_panodata = json_decode( wp_unslash( $_POST['panodata'] ), true );
            if ( ! empty( $post_panodata['scene-list'] ) ) {
                foreach ( $post_panodata['scene-list'] as $scene ) {
                    // Hotspots (Skip for Video Tour and Street View)
                    if ( ! $is_video_tour && ! $is_street_view && ! empty( $scene['hotspot-list'] ) ) {
                        foreach ( $scene['hotspot-list'] as $hotspot ) {
                            $type = isset( $hotspot['hotspot-type'] ) ? $hotspot['hotspot-type'] : '';
                            if ( $type === 'info' ) {
                                $enabled_features[] = 'Info Hotspot';
                            } elseif ( $type === 'wc_product' ) {
                                $enabled_features[] = 'WooCommerce Product Hotspot';
                            } elseif ( $type === 'fluent_form' ) {
                                $enabled_features[] = 'Fluent Form Hotspot';
                            } else {
                                $enabled_features[] = 'Scene Type Hotspot';
                            }
                            
                            if ( ! empty( $hotspot['hotspot-customclass'] ) || ! empty( $hotspot['hotspot-custom-icon'] ) ) {
                                $enabled_features[] = 'Custom Hotspot Icon';
                            }
                        }
                    }
                    
                    // Scene specific settings from images (Usually skip for Video Tour as it has separate logic)
                    if ( ! $is_video_tour ) {
                        if ( ! empty( $scene['maxHfov'] ) || ! empty( $scene['minHfov'] ) || ! empty( $scene['maxPitch'] ) || ! empty( $scene['minPitch'] ) ) {
                            $enabled_features[] = 'Custom Viewing Limits';
                        }

                        if ( ! empty( $scene['haov'] ) || ! empty( $scene['vaov'] ) ) {
                            $enabled_features[] = 'Custom Default View';
                        }

                        if ( ! empty( $scene['latitude'] ) || ! empty( $scene['longitude'] ) ) {
                            $enabled_features[] = 'Map Location';
                        }
                    }
                }
            }
        }

        return array_unique( $enabled_features );
    }

    /**
     * Track plugin activation
     *
     * Sends telemetry event when the plugin is activated.
     *
     * @since 8.5.48
     */
    public function track_plugin_activation() {
        coderex_telemetry_track(
            WPVR_FILE,
            'plugin_activation',
            array(
                'plugin_version' => defined('WPVR_VERSION') ? WPVR_VERSION : 'unknown',
                'activation_time' => current_time( 'c' ),
            )
        );
    }

    /**
     * Track plugin deactivation
     *
     * Sends telemetry event when the plugin is deactivated.
     *
     * @since 8.5.48
     */
    public function track_plugin_deactivation() {
        coderex_telemetry_track(
            WPVR_FILE,
            'plugin_deactivated',
            array(
                'plugin_version' => defined('WPVR_VERSION') ? WPVR_VERSION : 'unknown',
                'deactivation_time' => current_time( 'c' ),
            )
        );
    }

    /**
     * Track the first published tour
     *
     * Sends telemetry when the first tour is published for the plugin.
     *
     * @param string $new_status The new post status
     * @param string $old_status The previous post status
     * @param object $post  \WP_Post The post object
     * @since 8.5.48
     */
    public function track_first_tour_published( $new_status, $old_status, $post ) {
        if ($post->post_type !== 'wpvr_item') {
            return;
        }

        // Skip demo/sample tours created during plugin installation
        $is_demo_tour = get_post_meta($post->ID, 'wpvr_is_demo_tour', true);
        if ($is_demo_tour === '1') {
            return;
        }

        if ($new_status === 'publish' && in_array($old_status, ['auto-draft', 'draft', 'new', ''])) {
            // Count only non-demo tours
            $args = array(
                'post_type'      => 'wpvr_item',
                'post_status'    => array('publish', 'draft'),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'wpvr_is_demo_tour',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'wpvr_is_demo_tour',
                        'value'   => '1',
                        'compare' => '!=',
                    ),
                ),
            );
            
            $user_tours = get_posts($args);
            $total_user_tours = is_array($user_tours) ? count($user_tours) : 0;

            do_action('rex_wpvr_tour_created', $post->post_title);
            
            // Detailed tracking for first publish
            $this->track_detailed_tour_events( $post->ID );

            if (1 === $total_user_tours) {
                coderex_telemetry_track(
                    WPVR_FILE,
                    'first_tour_published',
                    array(
                        'tour_title'    => $post->post_title,
                        'time'          => current_time('mysql'),
                    )
                );
            }
        } elseif ($new_status === 'publish' && $old_status === 'publish') {
            coderex_telemetry_track(
                WPVR_FILE,
                'tour_updated',
                array(
                    'tour_title' => $post->post_title,
                    'time' => current_time('mysql'),
                )
            );
            // Detailed tracking on update
            $this->track_detailed_tour_events( $post->ID );
        }
    }
    /**
     * Track tour creation
     *
     * Sends telemetry when a new tour is created.
     *
     * @param string $post_title The title of the created tour
     * @since 8.5.48
     */
    public function track_tour_created( $post_title ) {
        coderex_telemetry_track(
            WPVR_FILE,
            'tour_created',
            array(
                'tour_title' => isset( $post_title) ? $post_title : '',
                'time' => current_time('mysql'),
            )
        );
    }


    /**
     * Track page views
     *
     * Sends telemetry when specific admin pages for the plugin are viewed.
     *
     * @param WP_Screen $screen Current admin screen object
     * @return void
     * @since 8.5.48
     */
    public function track_page_view( $screen ) {
        if ( ! is_admin() || empty( $screen->id ) ) {
            return;
        }

        $page_map = array(
            '/wp-admin/edit.php?post_type=wpvr_item' => 'Tour list',
            '/wp-admin/admin.php?page=wpvr-settings' => 'Settings',
            '/wp-admin/admin.php?page=wpvr-analytics' => 'Analytics',
            '/wp-admin/admin.php?page=rex-wpvr-setup-wizard' => 'Setup wizard',
            '/wp-admin/admin.php?page=wpvrpro' => 'License',
        );

        $current_page = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';
        if ( '' === $current_page ) {
            return;
        }

        $page_name = null;
        foreach ( $page_map as $fragment => $name ) {
            if ( strpos( $current_page, $fragment ) !== false ) {
                $page_name = $name;
                break;
            }
        }


        if ( null === $page_name && 
            strpos( $current_page, '/wp-admin/post.php' ) !== false && 
            strpos( $current_page, 'action=edit' ) !== false ) {
            if ( isset( $screen->post_type ) && 'wpvr_item' === $screen->post_type ) {
                $page_name = 'Tour edit';
            }
        }

        if ( null === $page_name ) {
            return;
        }

        $current_user = wp_get_current_user();
        if ( ! $current_user->exists() ) {
            return;
        }

        coderex_telemetry_track(
            WPVR_FILE,
            'page_view',
            array(
                'page' => $current_page,
                'page_name' => $page_name,
                'time' => current_time( 'mysql' ),
            )
        );
    }


    /**
     * Track embedded tour event
     *
     * Sends telemetry when a tour is embedded.
     *
     * @param int $tour_id The ID of the tour being embedded.
     * @return void
     * @since 8.5.48
     */
    public function track_embadded_tour( $tour_id ) {
        // Check if tracking is already done for this tour
        if ( get_post_meta( $tour_id, '_wpvr_tour_embedded_tracked', true ) ) {
            return;
        }

        $tour = get_post( $tour_id );

        if ( ! $tour || $tour->post_type !== 'wpvr_item' ) {
            return;
        }

        coderex_telemetry_track(
            WPVR_FILE,
            'tour_embedded',
            array(
                'title' => $tour->post_title,
            )
        );

        // Mark as tracked
        update_post_meta( $tour_id, '_wpvr_tour_embedded_tracked', true );
    }


}

new Rex_WPVR_Telemetry();