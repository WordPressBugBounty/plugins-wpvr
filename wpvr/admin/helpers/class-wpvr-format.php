<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Responsible for managing WPVR data formation
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/views
 */

class WPVR_Format
{

    /**
     * Setup checkboxes values for meta fields
     * @param string $arg
     * 
     * @return bool
     * @since 8.0.0
     */
    public function set_checkbox_value($arg)
    {
        $data = sanitize_text_field($arg);
        if ($data == 'off') {
            return $data = false;
        } else {
            return $data = true;
        }
    }


    /**
     * Setup checkboxes values for meta fields while default value is on
     * @param string $arg
     * 
     * @return bool
     * @since 8.0.0
     */
    public function set_checkbox_on_value($arg)
    {
        $data = sanitize_text_field($arg);
        if ($data == 'on') {
            return $data = true;
        } else {
            return $data = false;
        }
    }


    /**
     * Setup checkboxes values for pro version meta fields
     * @param string $arg
     * 
     * @return bool
     * @since 8.0.0
     */
    public function set_pro_checkbox_value($arg)
    {
        $data = sanitize_text_field($arg);
        if ($data == 'on') {
            return $data = 'on';
        } else {
            return $data = 'off';
        }
    }


    /**
     * Preapre default scene ID for Tour 
     * @param array $panodata
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_default_scene($panodata)
    {
        $allsceneids = array();

        foreach ($panodata["scene-list"] as $panoscenes) {
            if (!empty($panoscenes['scene-id'])) {
                array_push($allsceneids, $panoscenes['scene-id']);
            }
            if ($panoscenes['dscene'] == 'on') {
                return $default_scene = $panoscenes['scene-id'];
            }
        }

        if (empty($default_scene)) {
            if ($allsceneids) {
                return $default_scene = $allsceneids[0];
            }
        }
    }


    /**
     * Prepare panaromic data for Tour
     * 
     * @param string $panodata
     * 
     * @return array
     * @since 8.0.0
     */
    public function prepare_panodata($panodata)
    {
        $panodata = (array)json_decode($panodata);
        $panolist = array();
        if (is_array($panodata["scene-list"])) {
            foreach ($panodata["scene-list"] as $scenes_data) {
                $temp_array = array();
                $temp_array = (array)$scenes_data;

                if ($temp_array['hotspot-list']) {
                    $_hotspot_array = array();
                    foreach ($temp_array['hotspot-list'] as $temp_hotspot) {

                        $temp_hotspot = (array)$temp_hotspot;
                        if (isset($temp_hotspot['hotspot-pitch'])) {
                            $temp_hotspot['hotspot-pitch'] = trim($temp_hotspot['hotspot-pitch']);
                        }
                        if (isset($temp_hotspot['hotspot-yaw'])) {
                            $temp_hotspot['hotspot-yaw'] = trim($temp_hotspot['hotspot-yaw']);
                        }
                        $_hotspot_array[] = $temp_hotspot;
                    }
                }

                $temp_array['hotspot-list'] = $_hotspot_array;
                $panolist['scene-list'][] = $temp_array;
            }
        }
        $panodata = $panolist;
        return $panodata;
    }


    /**
     * Remove empty scene and hotspot from panaromic data list
     * 
     * @param array $panodata
     * 
     * @return array
     * @since 8.0.0
     */
    public function remove_empty_scene_and_hotspot($panodata)
    {
        $panolength = count($panodata["scene-list"]);
        for ($i = 0; $i < $panolength; $i++) {
            if (empty($panodata["scene-list"][$i]['scene-id'])) {
                unset($panodata["scene-list"][$i]);
            } else {
                $panohotspotlength = count($panodata["scene-list"][$i]['hotspot-list']);
                for ($j = 0; $j < $panohotspotlength; $j++) {
                    if (empty($panodata["scene-list"][$i]['hotspot-list'][$j]['hotspot-title'])) {
                        unset($panodata["scene-list"][$i]['hotspot-list'][$j]);
                    }
                }
            }
        }
        return $panodata;
    }


    /**
     * Prepare tour rotation wrapper data
     * 
     * @param array $pano_array
     * @param string $rotation
     * 
     * @return array
     * @since 8.0.0
     */
    public function prepare_rotation_wrapper_data($pano_array, $rotation)
    {
        if ($rotation == 'off') {
            unset($pano_array['autoRotate']);
            unset($pano_array['autoRotateInactivityDelay']);
            unset($pano_array['autoRotateStopDelay']);
        }
        if (empty($pano_array['autoRotate'])) {
            unset($pano_array['autoRotate']);
            unset($pano_array['autoRotateInactivityDelay']);
            unset($pano_array['autoRotateStopDelay']);
        }
        if (empty($pano_array['autoRotateInactivityDelay'])) {
            unset($pano_array['autoRotateInactivityDelay']);
        }
        if (empty($pano_array['autoRotateStopDelay'])) {
            unset($pano_array['autoRotateStopDelay']);
        }
        return $pano_array;
    }


    public function prepare_video_settings_data()
    {
        $autoplay = '';
        if (isset($_POST['autoplay'])) {
            $autoplay = sanitize_text_field($_POST['autoplay']);
        }

        $loop = '';
        if (isset($_POST['loop'])) {
            $loop = sanitize_text_field($_POST['loop']);
        }

        return array(
            'autoplay' => $autoplay,
            'loop'     => $loop
        );
    }


    /**
     * Prepare meta data when video url has youtube
     * 
     * @param string $videourl
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_youtube_video_meta_data($videourl, $videodata)
    {
        $explodeid = '';
        $explodeid = explode("=", $videourl);

        if ($videodata['autoplay'] == 'on') {
            $autoplay = '&autoplay=1';
        } else {
            $autoplay = '';
        }

        if ($videodata['loop'] == 'on') {
            $loop = '&loop=1';
        } else {
            $loop = '';
        }

        $foundid = '';
        $foundid = $explodeid[1] . '?' . $autoplay . $loop;
        $html = '';
        $html .= '<iframe width="600" height="400" src="https://www.youtube.com/embed/' . $foundid . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        return $html;
    }

    /**
     * Prepare meta data when video url has youtu.be
     * 
     * @param string $videourl
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_youtu_be_video_meta_data($videourl, $videodata)
    {
        $explodeid = '';
        $explodeid = explode("/", $videourl);

        if ($videodata['autoplay'] == 'on') {
            $autoplay = '&autoplay=1';
        } else {
            $autoplay = '';
        }

        if ($videodata['loop'] == 'on') {
            $loop = '&loop=1';
        } else {
            $loop = '';
        }

        $foundid = '';
        $foundid = $explodeid[3] . '?' . $autoplay . $loop;
        $html = '';
        $html .= '<iframe width="600" height="400" src="https://www.youtube.com/embed/' . $foundid . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        return $html;
    }

    /**
     * Prepare meta data when video url has vimeo
     * 
     * @param string $videourl
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_vimeo_video_meta_data($videourl, $videodata)
    {
        $vid_parts = explode( '/', rtrim( $videourl, '/' ) );
        $vid_id    = end( $vid_parts );

        $do_autoplay = ( $videodata['autoplay'] === 'on' ) ? 'true' : 'false';
        $do_loop     = ( $videodata['loop']     === 'on' ) ? 'true' : 'false';

        $autoplay_param = ( $videodata['autoplay'] === 'on' ) ? 'autoplay=1&muted=1' : '';
        $loop_param     = '';

        $query_parts = array_filter( [ $autoplay_param, $loop_param ] );
        $foundid     = ! empty( $query_parts ) ? $vid_id . '?' . implode( '&', $query_parts ) : $vid_id;

        $iframe_id = 'wpvr-vimeo-preview-' . wp_wp_rand( 10000, 99999 );

        $html  = '';
        $html .= '<iframe id="' . esc_attr( $iframe_id ) . '" src="https://player.vimeo.com/video/' . esc_attr( $foundid ) . '" width="600" height="400" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        $html .= '<script>
(function() {
    var IFRAME_ID   = ' . wp_json_encode( $iframe_id ) . ';
    var DO_AUTOPLAY = ' . $do_autoplay . ';
    var DO_LOOP     = ' . $do_loop . ';

    function wpvrLoadVimeoSdk( callback ) {
        if ( window.Vimeo && window.Vimeo.Player ) { callback(); return; }
        window.wpvrVimeoSdkCallbacks = window.wpvrVimeoSdkCallbacks || [];
        window.wpvrVimeoSdkCallbacks.push( callback );
        if ( window.wpvrVimeoSdkLoading ) { return; }
        window.wpvrVimeoSdkLoading = true;
        var tag = document.createElement( "script" );
        tag.src = "https://player.vimeo.com/api/player.js";
        tag.onload = function() {
            var cbs = window.wpvrVimeoSdkCallbacks || [];
            while ( cbs.length ) { ( cbs.shift() )(); }
        };
        document.head.appendChild( tag );
    }

    function initVimeoPlayer() {
        var iframeEl = document.getElementById( IFRAME_ID );
        if ( !iframeEl ) { return; }
        var player = new Vimeo.Player( iframeEl );

        player.ready().then( function() {
            if ( DO_LOOP ) {
                player.setLoop( true ).catch( function() {} );
            }
            if ( DO_AUTOPLAY ) {
                player.setVolume( 0 ).then( function() {
                    player.play().catch( function() {} );
                } ).catch( function() {
                    player.play().catch( function() {} );
                } );
            }
        } );
    }

    if ( document.readyState === "loading" ) {
        document.addEventListener( "DOMContentLoaded", function() { wpvrLoadVimeoSdk( initVimeoPlayer ); } );
    } else {
        wpvrLoadVimeoSdk( initVimeoPlayer );
    }
}());
</script>';
        return $html;
    }

    /**
     * Prepare meta data when video is selfhosted
     * 
     * @param string $videourl
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_selfhost_video_meta_data($videourl, $vidid, $videodata)
    {
        if ($videodata['autoplay'] == 'on') {
            $autoplay = 'autoplay muted';
        } else {
            $autoplay = '';
        }

        if ($videodata['loop'] == 'on') {
            $loop = 'loop';
        } else {
            $loop = '';
        }

        $html = '';
        $html .= '<video id="' . $vidid . '" class="video-js vjs-default-skin vjs-big-play-centered" ' . $autoplay . ' ' . $loop . ' controls preload="auto" style="width:100%; height: 100%;" poster="" >';
        $html .= '<source src="' . $videourl . '" type="video/mp4"/>';
        $html .= '<p class="vjs-no-js">';
        $html .= 'To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com html5-video-support/" target="_blank">supports HTML5 video</a>';
        $html .= '</p>';
        $html .= '</video>';
        return $html;
    }


    /**
     * Prepare scene data for Tour preview
     * 
     * @param array $panodata
     * 
     * @return array
     * @since 8.0.0
     */
    public function prepare_scene_data_for_preview($panodata)
    {
        $scene_data = array();
        foreach ($panodata["scene-list"] as $panoscenes) {
            if (!empty($panoscenes['scene-id'])) {
                $scene_ititle = sanitize_text_field(@$panoscenes["scene-ititle"]);
                $scene_author = sanitize_text_field(@$panoscenes["scene-author"]);

                $scene_vaov            = isset($panoscenes["scene-vaov"]) ? (float)$panoscenes["scene-vaov"] : 180;
                $scene_haov            = isset($panoscenes["scene-vaov"]) ? (float)$panoscenes["scene-haov"] : 360;
                $scene_vertical_offset = isset($panoscenes["scene-vertical-offset"]) ? (float)$panoscenes["scene-vertical-offset"] : 0;

                $default_scene_pitch   = isset($panoscenes["scene-pitch"]) ? (float)$panoscenes["scene-pitch"] : null;
                $default_scene_yaw     = isset($panoscenes["scene-yaw"]) ? (float)$panoscenes["scene-yaw"] : null;
                $scene_max_pitch       = isset($panoscenes["scene-maxpitch"]) ? (float)$panoscenes["scene-maxpitch"] : '';
                $scene_min_pitch       = isset($panoscenes["scene-minpitch"]) ? (float)$panoscenes["scene-minpitch"] : '';
                $scene_max_yaw         = isset($panoscenes["scene-maxyaw"]) ? (float)$panoscenes["scene-maxyaw"] : '';
                $scene_min_yaw         = isset($panoscenes["scene-minyaw"]) ? (float)$panoscenes["scene-minyaw"] : '';

                $default_zoom = isset($panoscenes["scene-zoom"]) ? $panoscenes["scene-zoom"] : 100;
                if (!empty($default_zoom)) {
                    $default_zoom =  isset($panoscenes["scene-zoom"]) ? (int)$panoscenes["scene-zoom"] : (int)$default_zoom;
                } else {
                    $default_zoom = 100;
                }

                $max_zoom = isset($panoscenes["scene-maxzoom"]) ? $panoscenes["scene-maxzoom"] : 120;
                if (!empty($max_zoom)) {
                    $max_zoom = isset($panoscenes["scene-maxzoom"]) ? (int)$panoscenes["scene-maxzoom"] : (int)$max_zoom;
                } else {
                    $max_zoom = 120;
                }

                $min_zoom = isset($panoscenes["scene-minzoom"]) ? $panoscenes["scene-minzoom"] : 50;
                if (!empty($min_zoom)) {
                    $min_zoom = isset($panoscenes["scene-minzoom"]) ? (int)$panoscenes["scene-minzoom"] : (int)$min_zoom;
                } else {
                    $min_zoom = 50;
                }

                $hotspot_datas = $panoscenes["hotspot-list"];

                $hotspots = $this->prepare_hotspot_data_for_preview($hotspot_datas);

                $scene_info = array();
                if ($panoscenes["scene-type"] == 'cubemap') {
                    $pano_type = 'cubemap';
                    $pano_attachment = array(
                        $panoscenes["scene-attachment-url-face0"],
                        $panoscenes["scene-attachment-url-face1"],
                        $panoscenes["scene-attachment-url-face2"],
                        $panoscenes["scene-attachment-url-face3"],
                        $panoscenes["scene-attachment-url-face4"],
                        $panoscenes["scene-attachment-url-face5"]
                    );

                    $scene_info = array("type" => $panoscenes["scene-type"], "cubeMap" => $pano_attachment, "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, "hotSpots" => $hotspots);
                } else {
                    $scene_info = array("type" => $panoscenes["scene-type"], "panorama" => $panoscenes["scene-attachment-url"], "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, "hotSpots" => $hotspots);
                }

                if (empty($panoscenes["scene-ititle"])) {
                    unset($scene_info['title']);
                }
                if (empty($panoscenes["scene-author"])) {
                    unset($scene_info['author']);
                }

                if (empty($scene_vaov)) {
                    unset($scene_info['vaov']);
                }

                if (empty($scene_haov)) {
                    unset($scene_info['haov']);
                }

                if (empty($scene_vertical_offset)) {
                    unset($scene_info['vOffset']);
                }

                if (isset($panoscenes["cvgscene"])) {
                    if ($panoscenes["cvgscene"] == "off") {
                        unset($scene_info['maxPitch']);
                        unset($scene_info['minPitch']);
                    }
                }

                if (empty($panoscenes["scene-maxpitch"])) {
                    unset($scene_info['maxPitch']);
                }

                if (empty($panoscenes["scene-minpitch"])) {
                    unset($scene_info['minPitch']);
                }

                if (isset($panoscenes["chgscene"])) {
                    if ($panoscenes["chgscene"] == "off") {
                        unset($scene_info['maxYaw']);
                        unset($scene_info['minYaw']);
                    }
                }
                if (empty($panoscenes["scene-maxyaw"])) {
                    unset($scene_info['maxYaw']);
                }

                if (empty($panoscenes["scene-minyaw"])) {
                    unset($scene_info['minYaw']);
                }

                $scene_array = array();
                $scene_array = array(
                    $panoscenes["scene-id"] => $scene_info
                );
                $scene_data[$panoscenes["scene-id"]] = $scene_info;
            }
        }
        return $scene_data;
    }


    /**
     * Preapre hotspot data for Tour preview
     * 
     * @param array $hotspot_datas
     * 
     * @return array
     * @since 8.0.0
     */
    private function prepare_hotspot_data_for_preview($hotspot_datas)
    {
        $hotspots = array();
        foreach ($hotspot_datas as $hotspot_data) {

            if (!empty($hotspot_data["hotspot-title"])) {

                $hotspot_type = $hotspot_data["hotspot-type"] !== 'scene' ? 'info' : $hotspot_data["hotspot-type"];
                $hotspot_content = '';

                ob_start();
                do_action('wpvr_hotspot_content_admin', $hotspot_data);
                $hotspot_content = ob_get_clean();


                if (!$hotspot_content) $hotspot_content = $hotspot_data["hotspot-content"];


                $hotspot_info = array(
                    "text" => $hotspot_data["hotspot-title"],
                    "pitch" => $hotspot_data["hotspot-pitch"],
                    "yaw" => $hotspot_data["hotspot-yaw"],
                    "type" => $hotspot_type,
                    "URL" => $hotspot_data["hotspot-url"],
                    "clickHandlerArgs" => $hotspot_content,
                    "createTooltipArgs" => $hotspot_data["hotspot-hover"],
                    "sceneId" => $hotspot_data["hotspot-scene"],
                    'hotspot_type' => $hotspot_data['hotspot-type']
                );

                array_push($hotspots, $hotspot_info);
                if (empty($hotspot_data["hotspot-scene"])) {
                    unset($hotspot_info['targetPitch']);
                    unset($hotspot_info['targetYaw']);
                }
            }
        }
        return $hotspots;
    }


    /**
     * Prepare youtube video for preview
     * 
     * @param string $videourl
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_youtube_video_preview($videourl, $videodata)
    {
        $muted = ($videodata['autoplay'] == 'on') ? '&mute=1' : '';
        $autoplay = ($videodata['autoplay'] == 'on') ? '&autoplay=1' : '';
        $loop = ($videodata['loop'] == 'on') ? '&loop=1' : '';
        $origin = '&origin=' . rawurlencode( home_url() );

        $expdata = $this->extract_youtube_video_id( $videourl );
        $playlist = '&playlist=' . $expdata;

        $preview_id = 'wpvr-preview-' . wp_rand(10000, 99999);

        $html = '';
        $html .= '<div id="' . esc_attr( $preview_id . '-container' ) . '" style="position:relative; width:100%; max-width:523px; height:400px;">';

        // Iframe
        $html .= '<div id="' . esc_attr( $preview_id . '-frame' ) . '" style="width:100%; height:100%;">';
        $html .= '<iframe id="' . esc_attr( $preview_id . '-iframe' ) . '" src="https://www.youtube.com/embed/' . esc_attr( $expdata ) . '?rel=0&modestbranding=1' . $loop . '&autohide=1' . $muted . '&showinfo=0&controls=0' . $autoplay . $playlist . '&enablejsapi=1&playsinline=1&html5=1' . $origin . '" width="100%" height="100%" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; xr-spatial-tracking"></iframe>';
        $html .= '</div>';

        // Drag surface
        $html .= '<div id="' . esc_attr( $preview_id . '-drag-surface' ) . '" style="position:absolute; top:0; left:0; right:0; bottom:48px; display:none; pointer-events:none; cursor:grab; z-index:3; background:transparent;"></div>';

        // Custom control bar
        $html .= '<div id="' . esc_attr( $preview_id . '-controls' ) . '" style="display:none; position:absolute; bottom:0; left:0; right:0; height:48px; z-index:5; pointer-events:auto; background:linear-gradient(transparent, rgba(0,0,0,0.7)); align-items:center; padding:0 8px; gap:6px; box-sizing:border-box;">';

        // Play/Pause
        $html .= '<button type="button" id="' . esc_attr( $preview_id . '-play-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Play">';
        $html .= '<svg id="' . esc_attr( $preview_id . '-play-icon' ) . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>';
        $html .= '</button>';

        // Time current
        $html .= '<span id="' . esc_attr( $preview_id . '-time-current' ) . '" style="color:#fff; font-size:12px; font-family:Arial,sans-serif; min-width:36px; text-align:center; user-select:none;">0:00</span>';

        // Progress bar
        $html .= '<div id="' . esc_attr( $preview_id . '-progress-wrap' ) . '" style="flex:1; height:4px; background:rgba(255,255,255,0.3); border-radius:2px; cursor:pointer; position:relative;">';
        $html .= '<div id="' . esc_attr( $preview_id . '-progress-buffered' ) . '" style="position:absolute; top:0; left:0; height:100%; background:rgba(255,255,255,0.4); border-radius:2px; pointer-events:none;"></div>';
        $html .= '<div id="' . esc_attr( $preview_id . '-progress-bar' ) . '" style="position:absolute; top:0; left:0; height:100%; background:#ff0000; border-radius:2px; pointer-events:none;"></div>';
        $html .= '<div id="' . esc_attr( $preview_id . '-progress-thumb' ) . '" style="position:absolute; top:50%; left:0; width:12px; height:12px; background:#ff0000; border-radius:50%; transform:translate(-50%,-50%); pointer-events:none; display:none;"></div>';
        $html .= '</div>';

        // Time duration
        $html .= '<span id="' . esc_attr( $preview_id . '-time-duration' ) . '" style="color:#fff; font-size:12px; font-family:Arial,sans-serif; min-width:36px; text-align:center; user-select:none;">0:00</span>';

        // Mute button
        $html .= '<button id="' . esc_attr( $preview_id . '-mute-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative;" title="Mute">';
        $html .= '<svg id="' . esc_attr( $preview_id . '-vol-icon' ) . '" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>';
        $html .= '</button>';

        // Volume slider
        $html .= '<style>#' . esc_attr( $preview_id ) . '-vol-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:12px;height:12px;border-radius:50%;background:#fff;cursor:pointer;margin-top:-4.5px;}#' . esc_attr( $preview_id ) . '-vol-slider::-moz-range-thumb{width:12px;height:12px;border-radius:50%;background:#fff;border:none;cursor:pointer;}#' . esc_attr( $preview_id ) . '-vol-slider::-webkit-slider-runnable-track{height:3px;border-radius:1.5px;}#' . esc_attr( $preview_id ) . '-vol-slider::-moz-range-track{height:3px;border-radius:1.5px;background:rgba(255,255,255,0.3);}</style>';
        $html .= '<div id="' . esc_attr( $preview_id . '-vol-slider-wrap' ) . '" style="display:none; align-items:center; height:36px; padding:0 4px;">';
        $html .= '<input id="' . esc_attr( $preview_id . '-vol-slider' ) . '" type="range" min="0" max="100" value="100" style="width:52px; height:3px; cursor:pointer; -webkit-appearance:none; appearance:none; background:linear-gradient(to right,#fff 100%,rgba(255,255,255,0.3) 100%); border-radius:1.5px; outline:none;" />';
        $html .= '</div>';

        // Fullscreen button
        $html .= '<button id="' . esc_attr( $preview_id . '-fs-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Toggle fullscreen">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>';
        $html .= '</button>';

        $html .= '</div>'; // Close controls
        $html .= '</div>'; // Close container

        // Script
        $html .= '<script>
        (function() {
            var playerInitialised = false;
            var ytPlayer = null;
            var dragSurface = document.getElementById("' . esc_js( $preview_id ) . '-drag-surface");
            var isDragging = false;
            var dragStartX = 0;
            var dragStartY = 0;
            var dragStartView = null;

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function getSphericalView() {
                if (!ytPlayer || typeof ytPlayer.getSphericalProperties !== "function") return null;
                var view = ytPlayer.getSphericalProperties();
                return view && Object.keys(view).length ? view : null;
            }

            function setDragSurfaceEnabled(enabled) {
                if (!dragSurface) return;
                dragSurface.style.display = enabled ? "block" : "none";
                dragSurface.style.pointerEvents = enabled ? "auto" : "none";
            }

            function getEventPoint(event) {
                if (event.touches && event.touches.length) {
                    return event.touches[0];
                }

                if (event.changedTouches && event.changedTouches.length) {
                    return event.changedTouches[0];
                }

                return event;
            }

            function beginDrag(event) {
                var point = getEventPoint(event);
                var sphericalView = getSphericalView();

                if (!sphericalView || !point) return;

                event.preventDefault();
                dragStartX = point.clientX;
                dragStartY = point.clientY;
                dragStartView = sphericalView;
                dragSurface.style.cursor = "grabbing";
            }

            function updateDrag(event) {
                var deltaX;
                var deltaY;
                var nextView;
                var point = getEventPoint(event);

                if (!point) {
                    return;
                }

                if (!isDragging && dragStartView) {
                    deltaX = point.clientX - dragStartX;
                    deltaY = point.clientY - dragStartY;
                    if (Math.sqrt(deltaX * deltaX + deltaY * deltaY) > 5) isDragging = true;
                }

                if (!isDragging || !dragStartView || !ytPlayer || typeof ytPlayer.setSphericalProperties !== "function") return;

                event.preventDefault();
                deltaX = point.clientX - dragStartX;
                deltaY = point.clientY - dragStartY;
                nextView = {
                    yaw: (dragStartView.yaw || 0) + (deltaX * 0.2),
                    pitch: clamp((dragStartView.pitch || 0) + (deltaY * 0.2), -85, 85),
                    roll: dragStartView.roll || 0,
                    fov: dragStartView.fov || 100
                };
                ytPlayer.setSphericalProperties(nextView);
            }

            function endDrag() {
                if (!isDragging && dragStartView && ytPlayer) {
                    var playerState = ytPlayer.getPlayerState();
                    if (playerState === 1) ytPlayer.pauseVideo();
                    else ytPlayer.playVideo();
                }
                isDragging = false;
                dragStartView = null;
                if (dragSurface) dragSurface.style.cursor = "grab";
            }

            function update360DragAvailability() {
                var sphericalView = getSphericalView();
                setDragSurfaceEnabled(!!sphericalView);
            }

            function schedule360AvailabilityChecks() {
                update360DragAvailability();
                window.setTimeout(update360DragAvailability, 500);
                window.setTimeout(update360DragAvailability, 1500);
                window.setTimeout(update360DragAvailability, 3000);
            }

            if (dragSurface) {
                dragSurface.addEventListener("mousedown", function(event) {
                    beginDrag(event);
                });

                dragSurface.addEventListener("touchstart", function(event) {
                    beginDrag(event);
                }, { passive: false });

                dragSurface.addEventListener("dragstart", function(event) { event.preventDefault(); });

                window.addEventListener("mousemove", function(event) {
                    updateDrag(event);
                });

                window.addEventListener("touchmove", function(event) {
                    updateDrag(event);
                }, { passive: false });

                window.addEventListener("mouseup", function() {
                    endDrag();
                });

                window.addEventListener("touchend", function() {
                    endDrag();
                });

                window.addEventListener("touchcancel", function() {
                    endDrag();
                });
            }

            function wpvrLoadYouTubeApi(callback) {
                window.wpvrYoutubeApiCallbacks = window.wpvrYoutubeApiCallbacks || [];
                if (window.YT && window.YT.Player) { callback(); return; }
                window.wpvrYoutubeApiCallbacks.push(callback);
                if (window.wpvrYoutubeApiLoading) return;
                window.wpvrYoutubeApiLoading = true;
                var previousReady = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = function() {
                    if (typeof previousReady === "function") previousReady();
                    var callbacks = window.wpvrYoutubeApiCallbacks || [];
                    while (callbacks.length) {
                        var queuedCallback = callbacks.shift();
                        if (typeof queuedCallback === "function") queuedCallback();
                    }
                };
                var tag = document.createElement("script");
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName("script")[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }

            function initializeYouTubePlayer() {
                if (playerInitialised) return;
                wpvrLoadYouTubeApi(function() {
                    if (playerInitialised || !(window.YT && window.YT.Player)) return;
                    ytPlayer = new YT.Player("' . esc_js( $preview_id . '-iframe' ) . '", {
                        events: {
                            onReady: function() {
                                schedule360AvailabilityChecks();
                                startProgressLoop();
                            },
                            onStateChange: function() {
                                schedule360AvailabilityChecks();
                                startProgressLoop();
                            }
                        }
                    });
                    playerInitialised = true;
                });
            }

            // === Custom control bar logic ===
            var outerContainer = document.getElementById("' . esc_js( $preview_id ) . '-container");
            var controlBar = document.getElementById("' . esc_js( $preview_id ) . '-controls");
            var playBtn = document.getElementById("' . esc_js( $preview_id ) . '-play-btn");
            var playIcon = document.getElementById("' . esc_js( $preview_id ) . '-play-icon");
            var timeCurrent = document.getElementById("' . esc_js( $preview_id ) . '-time-current");
            var timeDuration = document.getElementById("' . esc_js( $preview_id ) . '-time-duration");
            var progressWrap = document.getElementById("' . esc_js( $preview_id ) . '-progress-wrap");
            var progressBar = document.getElementById("' . esc_js( $preview_id ) . '-progress-bar");
            var progressBuffered = document.getElementById("' . esc_js( $preview_id ) . '-progress-buffered");
            var progressThumb = document.getElementById("' . esc_js( $preview_id ) . '-progress-thumb");
            var fsBtn = document.getElementById("' . esc_js( $preview_id ) . '-fs-btn");
            var frameEl = document.getElementById("' . esc_js( $preview_id ) . '-frame");
            var muteBtn = document.getElementById("' . esc_js( $preview_id ) . '-mute-btn");
            var volSlider = document.getElementById("' . esc_js( $preview_id ) . '-vol-slider");
            var volSliderWrap = document.getElementById("' . esc_js( $preview_id ) . '-vol-slider-wrap");
            var volIcon = document.getElementById("' . esc_js( $preview_id ) . '-vol-icon");

            var playPath = "M8 5v14l11-7z";
            var pausePath = "M6 19h4V5H6v14zm8-14v14h4V5h-4z";
            var volIconMuted = "M16.5 12A4.5 4.5 0 0014 8.14v2.07l2.45 2.45c.03-.21.05-.43.05-.66zm2.5 0c0 .93-.21 1.82-.58 2.61l1.47 1.47A8.94 8.94 0 0021 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06a8.99 8.99 0 003.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z";
            var volIconLow = "M7 9v6h4l5 5V4L7 9z";
            var volIconMed = "M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86z";
            var volIconHigh = "M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z";

            function formatTime(sec) {
                if (!sec || isNaN(sec)) return "0:00";
                sec = Math.floor(sec);
                var m = Math.floor(sec / 60);
                var s = sec % 60;
                return m + ":" + (s < 10 ? "0" : "") + s;
            }

            if (controlBar) controlBar.style.display = "flex";

            // --- Play / Pause ---
            if (playBtn) {
                playBtn.addEventListener("click", function() {
                    if (!ytPlayer) return;
                    var state = ytPlayer.getPlayerState();
                    if (state === 1) ytPlayer.pauseVideo();
                    else ytPlayer.playVideo();
                });
            }

            function updatePlayIcon() {
                if (!ytPlayer || !playIcon) return;
                try {
                    var state = ytPlayer.getPlayerState();
                    playIcon.querySelector("path").setAttribute("d", state === 1 ? pausePath : playPath);
                } catch(e) {}
            }

            // --- Progress bar ---
            var progressAnimId = null;
            var isSeeking = false;
            var progressLoopStarted = false;

            function startProgressLoop() {
                if (progressLoopStarted) return;
                progressLoopStarted = true;
                progressAnimId = requestAnimationFrame(updateProgress);
            }

            function updateProgress() {
                if (!ytPlayer || isSeeking) { progressAnimId = requestAnimationFrame(updateProgress); return; }
                try {
                    var current = typeof ytPlayer.getCurrentTime === "function" ? ytPlayer.getCurrentTime() : 0;
                    var duration = typeof ytPlayer.getDuration === "function" ? ytPlayer.getDuration() : 0;
                    if (duration > 0) {
                        var pct = (current / duration) * 100;
                        progressBar.style.width = pct + "%";
                        progressThumb.style.left = pct + "%";
                        timeCurrent.textContent = formatTime(current);
                        timeDuration.textContent = formatTime(duration);
                    }
                    if (typeof ytPlayer.getVideoLoadedFraction === "function") {
                        progressBuffered.style.width = (ytPlayer.getVideoLoadedFraction() * 100) + "%";
                    }
                    updatePlayIcon();
                } catch (e) {}
                progressAnimId = requestAnimationFrame(updateProgress);
            }

            if (progressWrap) {
                function seekFromEvent(e) {
                    var rect = progressWrap.getBoundingClientRect();
                    var x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
                    var pct = x / rect.width;
                    var duration = typeof ytPlayer.getDuration === "function" ? ytPlayer.getDuration() : 0;
                    if (duration > 0 && ytPlayer) {
                        ytPlayer.seekTo(pct * duration, true);
                        progressBar.style.width = (pct * 100) + "%";
                        progressThumb.style.left = (pct * 100) + "%";
                        timeCurrent.textContent = formatTime(pct * duration);
                    }
                }
                progressWrap.addEventListener("mousedown", function(e) {
                    isSeeking = true;
                    seekFromEvent(e);
                    function onMove(e) { seekFromEvent(e); }
                    function onUp() {
                        isSeeking = false;
                        document.removeEventListener("mousemove", onMove);
                        document.removeEventListener("mouseup", onUp);
                    }
                    document.addEventListener("mousemove", onMove);
                    document.addEventListener("mouseup", onUp);
                });
                progressWrap.addEventListener("mouseenter", function() { progressThumb.style.display = "block"; });
                progressWrap.addEventListener("mouseleave", function() { if (!isSeeking) progressThumb.style.display = "none"; });
            }

            // --- Volume ---
            var volHideTimer = null;
            var localVol = 100;
            var localMuted = false;

            function updateVolIconFromLocal() {
                if (!volIcon) return;
                var path;
                if (localMuted || localVol === 0) path = volIconMuted;
                else if (localVol < 40) path = volIconLow;
                else if (localVol < 75) path = volIconMed;
                else path = volIconHigh;
                volIcon.querySelector("path").setAttribute("d", path);
            }

            function updateVolIcon() { updateVolIconFromLocal(); }

            function showVolSlider() {
                if (volHideTimer) { clearTimeout(volHideTimer); volHideTimer = null; }
                if (volSliderWrap) volSliderWrap.style.display = "flex";
            }
            function hideVolSlider() {
                volHideTimer = setTimeout(function() { if (volSliderWrap) volSliderWrap.style.display = "none"; }, 400);
            }

            if (muteBtn) {
                muteBtn.addEventListener("click", function() {
                    if (!ytPlayer) return;
                    if (localMuted) {
                        ytPlayer.unMute(); localMuted = false;
                        if (localVol === 0) { localVol = 100; ytPlayer.setVolume(100); }
                        if (volSlider) volSlider.value = localVol;
                    } else {
                        ytPlayer.mute(); localMuted = true;
                        if (volSlider) volSlider.value = 0;
                    }
                    updateVolIconFromLocal();
                    updateVolSliderFill();
                });
                muteBtn.addEventListener("mouseenter", showVolSlider);
                muteBtn.addEventListener("mouseleave", hideVolSlider);
            }
            if (volSliderWrap) {
                volSliderWrap.addEventListener("mouseenter", showVolSlider);
                volSliderWrap.addEventListener("mouseleave", hideVolSlider);
            }
            function updateVolSliderFill() {
                if (!volSlider) return;
                var v = parseInt(volSlider.value, 10);
                volSlider.style.background = "linear-gradient(to right,#fff " + v + "%,rgba(255,255,255,0.3) " + v + "%)";
            }
            if (volSlider) {
                volSlider.addEventListener("input", function() {
                    if (!ytPlayer) return;
                    var val = parseInt(volSlider.value, 10);
                    localVol = val;
                    ytPlayer.setVolume(val);
                    if (val === 0) { ytPlayer.mute(); localMuted = true; }
                    else if (localMuted) { ytPlayer.unMute(); localMuted = false; }
                    updateVolIconFromLocal();
                    updateVolSliderFill();
                });
                updateVolSliderFill();
            }

            // --- Fullscreen ---
            if (outerContainer) {
                var savedFrameWidth = frameEl ? frameEl.style.width : "";
                var savedFrameHeight = frameEl ? frameEl.style.height : "";
                var savedFrameMaxWidth = frameEl ? frameEl.style.maxWidth : "";
                var savedContainerWidth = outerContainer.style.width;
                var savedContainerHeight = outerContainer.style.height;
                var savedContainerBackground = outerContainer.style.background;

                if(fsBtn){
                    fsBtn.addEventListener("click", function() {
                        var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
                        if (fsEl) {
                            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
                        } else {
                            (outerContainer.requestFullscreen || outerContainer.webkitRequestFullscreen).call(outerContainer);
                        }
                    });
                }

                function onFsChange() {
                    var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
                    var isFs = (fsEl === outerContainer);
                    frameEl.style.width = isFs ? "100%" : savedFrameWidth;
                    frameEl.style.height = isFs ? "100%" : savedFrameHeight;
                    frameEl.style.maxWidth = isFs ? "100%" : savedFrameMaxWidth;
                    outerContainer.style.width = isFs ? "100%" : savedContainerWidth;
                    outerContainer.style.height = isFs ? "100%" : savedContainerHeight;
                    outerContainer.style.background = isFs ? "#000" : savedContainerBackground;
                    fsBtn.querySelector("svg path").setAttribute("d", isFs
                        ? "M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"
                        : "M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"
                    );
                    schedule360AvailabilityChecks();
                }
                document.addEventListener("fullscreenchange", onFsChange);
                document.addEventListener("webkitfullscreenchange", onFsChange);
            }

            // Sync controls after player ready
            var origSchedule = schedule360AvailabilityChecks;
            schedule360AvailabilityChecks = function() {
                origSchedule();
                updateVolIcon();
                updatePlayIcon();
            };

            // Initialize immediately for admin preview
            initializeYouTubePlayer();
        })();
    </script>';

        return $html;
    }


    /**
     * Extract YouTube video ID from various URL formats.
     *
     * Handles youtube.com/watch?v=, youtu.be/, youtube.com/embed/, and youtube.com/v/ formats.
     * Strips extra query parameters and sanitizes the ID to only valid YouTube characters.
     *
     * @param string $url The YouTube video URL.
     *
     * @return string The sanitized YouTube video ID, or empty string on failure.
     * @since 8.0.0
     */
    private function extract_youtube_video_id( $url ) {
        $url    = trim( $url );
        $parsed = wp_parse_url( $url );

        // Handle youtu.be short URLs.
        if ( isset( $parsed['host'] ) && 'youtu.be' === $parsed['host'] ) {
            $video_id = isset( $parsed['path'] ) ? ltrim( $parsed['path'], '/' ) : '';
            return preg_replace( '/[^A-Za-z0-9_\-]/', '', $video_id );
        }

        // Handle youtube.com/embed/VIDEO_ID or youtube.com/v/VIDEO_ID.
        if ( isset( $parsed['path'] ) && preg_match( '#/(embed|v)/([A-Za-z0-9_\-]+)#', $parsed['path'], $matches ) ) {
            return $matches[2];
        }

        // Handle youtube.com/watch?v=VIDEO_ID (standard format).
        if ( isset( $parsed['query'] ) ) {
            parse_str( $parsed['query'], $query_params );
            if ( ! empty( $query_params['v'] ) ) {
                return preg_replace( '/[^A-Za-z0-9_\-]/', '', $query_params['v'] );
            }
        }

        // Fallback: try naive extraction for edge cases.
        if ( preg_match( '/[?&]v=([A-Za-z0-9_\-]+)/', $url, $matches ) ) {
            return $matches[1];
        }

        return '';
    }


    /**
     * Prepare shortcode data for youtube video url
     * 
     * @param array $postdata
     * @param mixed $width
     * @param mixed $height
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_youtube_video_shortcode_data($postdata, $width, $height, $autoplay, $loop, $radius)
    {
        $muted = ($autoplay == 'on') ? '&mute=1' : '';
        $autoplay = ($autoplay == 'on') ? '&autoplay=1' : '';
        $loop = ($loop == 'on') ? '&loop=1' : '';
        $origin = '&origin=' . rawurlencode( home_url() );

        $expdata = $this->extract_youtube_video_id( $postdata['vidurl'] );

        $playlist = '&playlist=' . $expdata;

        $html = '';
        $html .= '<div style="text-align:center; max-width:100%; width:' . $width . '; height:' . $height . '; border-radius: ' . $radius . '; margin: 0 auto;">';

        // Add browser compatibility detection script
        $html .= '<script>
        function wpvr_check_360_support() {
            var support = {
                supported: false,
                fullySupported: false,
                webgl: false,
                orientation: false,
                gyroscope: false,
                touch: false,
                browser: "unknown",
                isMobile: false,
                isIPhone: false,
                isAndroid: false,
                details: []
            };

            // Detect mobile devices
            support.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            support.isIPhone = /iPhone/i.test(navigator.userAgent);
            support.isAndroid = /Android/i.test(navigator.userAgent);
            if (support.isMobile) {
                support.details.push("Mobile device detected");
            }
            if (support.isIPhone) {
                support.details.push("iPhone detected");
            }
            if (support.isAndroid) {
                support.details.push("Android device detected");
            }

            // Browser detection
            var ua = navigator.userAgent;
            if (/^((?!chrome|android).)*safari/i.test(ua)) {
                support.browser = "safari";
                support.details.push("Safari browser detected");
            } else if (ua.indexOf("Chrome") > -1) {
                support.browser = "chrome";
                support.details.push("Chrome browser detected");
            } else if (ua.indexOf("Firefox") > -1) {
                support.browser = "firefox";
                support.details.push("Firefox browser detected");
            } else if (ua.indexOf("MSIE") > -1 || ua.indexOf("Trident") > -1) {
                support.browser = "ie";
                support.details.push("Internet Explorer detected");
            } else if (ua.indexOf("Edge") > -1 || ua.indexOf("Edg") > -1) {
                support.browser = "edge";
                support.details.push("Edge browser detected");
            } else if (ua.indexOf("Opera") > -1 || ua.indexOf("OPR") > -1) {
                support.browser = "opera";
                support.details.push("Opera browser detected");
            }

            // Check WebGL support
            try {
                var canvas = document.createElement("canvas");
                support.webgl = !!(window.WebGLRenderingContext &&
                    (canvas.getContext("webgl") || canvas.getContext("experimental-webgl")));

                if (support.webgl) {
                    support.details.push("WebGL supported");
                } else {
                    support.details.push("WebGL not supported");
                }
            } catch (e) {
                support.webgl = false;
                support.details.push("WebGL detection error: " + e.message);
            }

            // Check device orientation support
            support.orientation = !!(window.DeviceOrientationEvent);
            if (support.orientation) {
                support.details.push("Device Orientation API supported");
            } else {
                support.details.push("Device Orientation API not supported");
            }

            // Check for gyroscope
            if (window.Gyroscope) {
                support.gyroscope = true;
                support.details.push("Gyroscope supported");
            } else {
                support.details.push("Gyroscope not supported");
            }

            // Check touch support for mobile interaction
            support.touch = "ontouchstart" in window || navigator.maxTouchPoints > 0;
            if (support.touch) {
                support.details.push("Touch supported");
            } else {
                support.details.push("Touch not supported");
            }

            // Overall support determination
            support.supported = support.webgl;
            
            // Full support means WebGL + orientation on mobile or WebGL on desktop
            support.fullySupported = support.webgl;

            // Browser-specific warnings
                if (support.browser === "ie") {
                support.browserWarning = "Internet Explorer has limited support for 360 videos. The experience may not be optimal.";
                support.fullySupported = false;
            } else if (!support.supported) {
                support.browserWarning = "Your browser does not fully support 360° videos. For the best experience, please use a modern browser with WebGL support.";
            }

            return support;
        }
    </script>';

        $random_id = 'video-container-' . wp_rand(10000, 99999);
        $html .= '<div id="' . $random_id . '-container" style="position:relative; width:100%; height:100%;">';

        // First, always check browser compatibility before showing anything
        $html .= '<div id="' . $random_id . '-compatibility-check" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; pointer-events:none; flex-direction:column; justify-content:center; align-items:center; background-color:#f9f9f9; border-radius:' . $radius . ';">
                    <div style="margin-bottom:20px; text-align:center;">
                        <div class="wpvr-loading-spinner" style="border:5px solid #f3f3f3; border-top:5px solid #3498db; border-radius:50%; width:50px; height:50px; margin:0 auto 15px; animation:wpvr-spin 1s linear infinite;"></div>
                        <p style="margin:0;">Checking browser compatibility...</p>
                    </div>
                   </div>';

        // Add animation for spinner
        $html .= '<style>@keyframes wpvr-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';

        // Add the iframe (visible by default so YouTube 360° WebGL context gets correct dimensions)
        $html .= '<div id="' . esc_attr( $random_id . '-frame' ) . '" style="text-align:center; max-width:100%; width:' . esc_attr( $width ) . '; height:' . esc_attr( $height ) . '; border-radius:' . esc_attr( $radius ) . '; margin: 0 auto; display:block;">';
        $html .= '<iframe id="' . esc_attr( $random_id . '-iframe' ) . '" src="https://www.youtube.com/embed/' . esc_attr( $expdata ) . '?rel=0&modestbranding=1' . $loop . '&autohide=1' . $muted . '&showinfo=0&controls=0' . $autoplay . $playlist . '&enablejsapi=1&playsinline=1&html5=1' . $origin . '" width="100%" height="100%" style="border-radius:' . esc_attr( $radius ) . ';" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; xr-spatial-tracking"></iframe>';
        $html .= '</div>';
        $html .= '<div id="' . esc_attr( $random_id . '-drag-surface' ) . '" style="position:absolute; top:60px; left:0; right:0; bottom:48px; display:none; pointer-events:none; cursor:grab; z-index:3; background:transparent;"></div>';

        // Custom control bar
        $html .= '<div id="' . esc_attr( $random_id . '-controls' ) . '" style="display:none; position:absolute; bottom:0; left:0; right:0; height:48px; z-index:5; pointer-events:auto; background:linear-gradient(transparent, rgba(0,0,0,0.7)); align-items:center; padding:0 8px; gap:6px; box-sizing:border-box;">';

        // Play/Pause button
        $html .= '<button type="button" id="' . esc_attr( $random_id . '-play-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Play">';
        $html .= '<svg id="' . esc_attr( $random_id . '-play-icon' ) . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>';
        $html .= '</button>';

        // Time current
        $html .= '<span id="' . esc_attr( $random_id . '-time-current' ) . '" style="color:#fff; font-size:12px; font-family:Arial,sans-serif; min-width:36px; text-align:center; user-select:none;">0:00</span>';

        // Progress bar
        $html .= '<div id="' . esc_attr( $random_id . '-progress-wrap' ) . '" style="flex:1; height:4px; background:rgba(255,255,255,0.3); border-radius:2px; cursor:pointer; position:relative;">';
        $html .= '<div id="' . esc_attr( $random_id . '-progress-buffered' ) . '" style="position:absolute; top:0; left:0; height:100%; background:rgba(255,255,255,0.4); border-radius:2px; pointer-events:none;"></div>';
        $html .= '<div id="' . esc_attr( $random_id . '-progress-bar' ) . '" style="position:absolute; top:0; left:0; height:100%; background:#ff0000; border-radius:2px; pointer-events:none;"></div>';
        $html .= '<div id="' . esc_attr( $random_id . '-progress-thumb' ) . '" style="position:absolute; top:50%; left:0; width:12px; height:12px; background:#ff0000; border-radius:50%; transform:translate(-50%,-50%); pointer-events:none; display:none;"></div>';
        $html .= '</div>';

        // Time duration
        $html .= '<span id="' . esc_attr( $random_id . '-time-duration' ) . '" style="color:#fff; font-size:12px; font-family:Arial,sans-serif; min-width:36px; text-align:center; user-select:none;">0:00</span>';

        // Mute button
        $html .= '<button id="' . esc_attr( $random_id . '-mute-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative;" title="Mute">';
        $html .= '<svg id="' . esc_attr( $random_id . '-vol-icon' ) . '" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>';
        $html .= '</button>';

        // Volume slider (popup on mute hover)
        $html .= '<style>#' . esc_attr( $random_id ) . '-vol-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:14px;height:14px;border-radius:50%;background:#fff;cursor:pointer;margin-top:-5px;}#' . esc_attr( $random_id ) . '-vol-slider::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:#fff;border:none;cursor:pointer;}#' . esc_attr( $random_id ) . '-vol-slider::-webkit-slider-runnable-track{height:4px;border-radius:4px;}#' . esc_attr( $random_id ) . '-vol-slider::-moz-range-track{height:4px;border-radius:4px;background:rgba(255,255,255,0.3);}</style>';
        $html .= '<div id="' . esc_attr( $random_id . '-vol-slider-wrap' ) . '" style="display:none; align-items:center; height:40px; padding:0px 4px;">';
        $html .= '<input id="' . esc_attr( $random_id . '-vol-slider' ) . '" type="range" min="0" max="100" value="100" style="width:80px; height:4px; cursor:pointer; -webkit-appearance:none; appearance:none; background:linear-gradient(to right,#fff 100%,rgba(255,255,255,0.3) 100%); border-radius:4px; outline:none; padding:0;" />';
        $html .= '</div>';

        // Fullscreen button
        $html .= '<button id="' . esc_attr( $random_id . '-fs-btn' ) . '" style="width:36px; height:36px; border:none; background:transparent; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; flex-shrink:0;" title="Toggle fullscreen">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>';
        $html .= '</button>';

        $html .= '</div>'; // Close controls

        // Add browser compatibility warning message
        $html .= '<div id="' . $random_id . '-browser-warning" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; pointer-events:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.7); color:white; text-align:center; border-radius:' . $radius . ';">';
        $html .= '<p id="' . $random_id . '-warning-text" style="font-size:16px; margin:0 20px 5px;"></p>';
        $html .= '<p style="font-size:14px; margin:5px 20px 15px;">For the best experience, use a browser with WebGL support.</p>';
        $html .= '<button id="' . $random_id . '-browser-continue" style="padding:10px 15px; background-color:#0085ba; color:#fff; border:none; border-radius:4px; cursor:pointer;">Continue Anyway</button>';
        $html .= '</div>';

        $html .= '<div id="' . $random_id . '-iphone-fallback" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; pointer-events:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.75); color:white; text-align:center; border-radius:' . $radius . ';">';
        $html .= '<p id="' . $random_id . '-mobile-fallback-title" style="font-size:16px; margin:0 20px 8px;">YouTube 360 is limited in mobile browser embeds.</p>';
        $html .= '<p id="' . $random_id . '-mobile-fallback-description" style="font-size:14px; margin:0 20px 15px;">For proper 360 playback, open this video in the YouTube app.</p>';
        $html .= '<a id="' . $random_id . '-iphone-open" href="https://www.youtube.com/watch?v=' . esc_attr( $expdata ) . '" target="_blank" rel="noopener noreferrer" style="color:rgba(255,255,255,0.85); text-decoration:underline; font-size:12px; margin-bottom:8px;">Open in YouTube</a>';
        $html .= '<a id="' . $random_id . '-iphone-continue" href="#" style="color:rgba(255,255,255,0.75); text-decoration:underline; font-size:12px;">Show Here Anyway</a>';
        $html .= '</div>';

        // Main script for handling compatibility and permissions
        $html .= '<script>
        // Wait for DOM to be fully loaded
        document.addEventListener("DOMContentLoaded", function() {
            var playerInitialised = false;
            var ytPlayer = null;
            var dragSurface = document.getElementById("' . $random_id . '-drag-surface");
            var isDragging = false;
            var dragStartX = 0;
            var dragStartY = 0;
            var dragStartView = null;

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function getSphericalView() {
                if (!ytPlayer || typeof ytPlayer.getSphericalProperties !== "function") {
                    return null;
                }

                var view = ytPlayer.getSphericalProperties();
                return view && Object.keys(view).length ? view : null;
            }

            function setDragSurfaceEnabled(enabled) {
                if (!dragSurface) {
                    return;
                }

                dragSurface.style.display = enabled ? "block" : "none";
                dragSurface.style.pointerEvents = enabled ? "auto" : "none";
            }

            function getEventPoint(event) {
                if (event.touches && event.touches.length) {
                    return event.touches[0];
                }

                if (event.changedTouches && event.changedTouches.length) {
                    return event.changedTouches[0];
                }

                return event;
            }

            function beginDrag(event) {
                var point = getEventPoint(event);
                var sphericalView = getSphericalView();

                if (!sphericalView || !point) {
                    return;
                }

                event.preventDefault();
                dragStartX = point.clientX;
                dragStartY = point.clientY;
                dragStartView = sphericalView;
                dragSurface.style.cursor = "grabbing";
            }

            function updateDrag(event) {
                var deltaX;
                var deltaY;
                var nextView;
                var point = getEventPoint(event);

                if (!point) {
                    return;
                }

                if (!isDragging && dragStartView) {
                    deltaX = point.clientX - dragStartX;
                    deltaY = point.clientY - dragStartY;
                    if (Math.sqrt(deltaX * deltaX + deltaY * deltaY) > 5) {
                        isDragging = true;
                    }
                }

                if (!isDragging || !dragStartView || !ytPlayer || typeof ytPlayer.setSphericalProperties !== "function") {
                    return;
                }

                event.preventDefault();
                deltaX = point.clientX - dragStartX;
                deltaY = point.clientY - dragStartY;

                nextView = {
                    yaw: (dragStartView.yaw || 0) + (deltaX * 0.2),
                    pitch: clamp((dragStartView.pitch || 0) + (deltaY * 0.2), -85, 85),
                    roll: dragStartView.roll || 0,
                    fov: dragStartView.fov || 100
                };

                ytPlayer.setSphericalProperties(nextView);
            }

            function endDrag() {
                if (!isDragging && dragStartView && ytPlayer) {
                    var playerState = ytPlayer.getPlayerState();
                    if (playerState === 1) {
                        ytPlayer.pauseVideo();
                    } else {
                        ytPlayer.playVideo();
                    }
                }
                isDragging = false;
                dragStartView = null;
                if (dragSurface) {
                    dragSurface.style.cursor = "grab";
                }
            }

            function update360DragAvailability() {
                var sphericalView = getSphericalView();
                setDragSurfaceEnabled(!!sphericalView);
            }

            function schedule360AvailabilityChecks() {
                update360DragAvailability();
                window.setTimeout(update360DragAvailability, 500);
                window.setTimeout(update360DragAvailability, 1500);
                window.setTimeout(update360DragAvailability, 3000);
            }

            if (dragSurface) {
                dragSurface.addEventListener("mousedown", function(event) {
                    beginDrag(event);
                });

                dragSurface.addEventListener("touchstart", function(event) {
                    beginDrag(event);
                }, { passive: false });

                dragSurface.addEventListener("dragstart", function(event) {
                    event.preventDefault();
                });

                window.addEventListener("mousemove", function(event) {
                    updateDrag(event);
                });

                window.addEventListener("touchmove", function(event) {
                    updateDrag(event);
                }, { passive: false });

                window.addEventListener("mouseup", function() {
                    endDrag();
                });

                window.addEventListener("touchend", function() {
                    endDrag();
                });

                window.addEventListener("touchcancel", function() {
                    endDrag();
                });
            }

            function wpvrLoadYouTubeApi(callback) {
                window.wpvrYoutubeApiCallbacks = window.wpvrYoutubeApiCallbacks || [];

                if (window.YT && window.YT.Player) {
                    callback();
                    return;
                }

                window.wpvrYoutubeApiCallbacks.push(callback);

                if (window.wpvrYoutubeApiLoading) {
                    return;
                }

                window.wpvrYoutubeApiLoading = true;

                var previousReady = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = function() {
                    if (typeof previousReady === "function") {
                        previousReady();
                    }

                    var callbacks = window.wpvrYoutubeApiCallbacks || [];
                    while (callbacks.length) {
                        var queuedCallback = callbacks.shift();
                        if (typeof queuedCallback === "function") {
                            queuedCallback();
                        }
                    }
                };

                var tag = document.createElement("script");
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName("script")[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }

            function initializeYouTubePlayer() {
                if (playerInitialised) {
                    return;
                }

                wpvrLoadYouTubeApi(function() {
                    if (playerInitialised || !(window.YT && window.YT.Player)) {
                        return;
                    }

                    ytPlayer = new YT.Player("' . esc_js( $random_id . '-iframe' ) . '", {
                        events: {
                            onReady: function() {
                                schedule360AvailabilityChecks();
                                startProgressLoop();
                                scheduleIPhoneFallbackCheck();
                            },
                            onStateChange: function() {
                                schedule360AvailabilityChecks();
                                startProgressLoop();
                                scheduleIPhoneFallbackCheck();
                            }
                        }
                    });
                    playerInitialised = true;
                });
            }

            // Check compatibility first
            var supportInfo = wpvr_check_360_support();
            var frameContainer = document.getElementById("' . $random_id . '-frame");
            let compatibilityCheck = document.getElementById("' . $random_id . '-compatibility-check");
            var iphoneFallback = document.getElementById("' . $random_id . '-iphone-fallback");
            var iphoneContinue = document.getElementById("' . $random_id . '-iphone-continue");
            var mobileFallbackTitle = document.getElementById("' . $random_id . '-mobile-fallback-title");
            var mobileFallbackDescription = document.getElementById("' . $random_id . '-mobile-fallback-description");

            function updateMobileFallbackContent() {
                if (!mobileFallbackTitle || !mobileFallbackDescription) {
                    return;
                }

                if (supportInfo.isIPhone) {
                    mobileFallbackTitle.textContent = "YouTube 360 is limited in iPhone Safari embeds.";
                    mobileFallbackDescription.textContent = "For proper 360 playback, open this video in the YouTube app.";
                } else if (supportInfo.isAndroid) {
                    mobileFallbackTitle.textContent = "YouTube 360 is limited in Android browser embeds.";
                    mobileFallbackDescription.textContent = "For reliable 360 playback, open this video in the YouTube app.";
                } else {
                    mobileFallbackTitle.textContent = "YouTube 360 is limited in mobile browser embeds.";
                    mobileFallbackDescription.textContent = "For proper 360 playback, open this video in the YouTube app.";
                }
            }

            function hideIPhoneFallback() {
                if (!iphoneFallback) {
                    return;
                }

                iphoneFallback.style.display = "none";
                iphoneFallback.style.pointerEvents = "none";
            }

            function showIPhoneFallback() {
                if (!iphoneFallback) {
                    return;
                }

                frameContainer.style.display = "none";
                iphoneFallback.style.display = "flex";
                iphoneFallback.style.pointerEvents = "auto";
            }

            function scheduleIPhoneFallbackCheck() {
                if (!supportInfo.isIPhone && !supportInfo.isAndroid) {
                    return;
                }

                updateMobileFallbackContent();

                window.setTimeout(function() {
                    if (!getSphericalView()) {
                        showIPhoneFallback();
                    }
                }, 1800);
            }

            if (iphoneContinue) {
                iphoneContinue.addEventListener("click", function(event) {
                    event.preventDefault();
                    hideIPhoneFallback();
                    showVideo();
                });
            }

            if (supportInfo.isMobile && !supportInfo.fullySupported) {
                compatibilityCheck.style.display = "flex";
                compatibilityCheck.style.pointerEvents = "auto";
            }
            // Check browser compatibility and handle overlays accordingly.
            // The iframe is already visible by default so the YouTube 360° WebGL
            // renderer gets the correct dimensions during initialisation.
            setTimeout(function() {
                document.getElementById("' . $random_id . '-compatibility-check").style.display = "none";
                document.getElementById("' . $random_id . '-compatibility-check").style.pointerEvents = "none";
                // If browser doesn\'t support 360 videos well, hide the iframe and show warning
                if (!supportInfo.supported || supportInfo.browserWarning) {
                    document.getElementById("' . $random_id . '-frame").style.display = "none";
                    document.getElementById("' . $random_id . '-warning-text").textContent = 
                        supportInfo.browserWarning || "Your browser may not fully support 360° videos.";
                    var warningEl = document.getElementById("' . $random_id . '-browser-warning");
                    warningEl.style.display = "flex";
                    warningEl.style.pointerEvents = "auto";
                    
                    // Add continue anyway button handler
                    document.getElementById("' . $random_id . '-browser-continue").addEventListener("click", function() {
                        warningEl.style.display = "none";
                        warningEl.style.pointerEvents = "none";
                        showVideo();
                    });
                }
                // Otherwise (supported desktop browser): iframe is already visible, nothing to do.
                else {
                    initializeYouTubePlayer();
                }
            }, 500);

            function showVideo() {
                hideIPhoneFallback();
                frameContainer.style.display = "block";
                initializeYouTubePlayer();
            }

            // === Custom control bar logic ===
            var outerContainer = document.getElementById("' . $random_id . '-container");
            var controlBar = document.getElementById("' . $random_id . '-controls");
            var playBtn = document.getElementById("' . $random_id . '-play-btn");
            var playIcon = document.getElementById("' . $random_id . '-play-icon");
            var timeCurrent = document.getElementById("' . $random_id . '-time-current");
            var timeDuration = document.getElementById("' . $random_id . '-time-duration");
            var progressWrap = document.getElementById("' . $random_id . '-progress-wrap");
            var progressBar = document.getElementById("' . $random_id . '-progress-bar");
            var progressBuffered = document.getElementById("' . $random_id . '-progress-buffered");
            var progressThumb = document.getElementById("' . $random_id . '-progress-thumb");
            var fsBtn = document.getElementById("' . $random_id . '-fs-btn");
            var frameEl = document.getElementById("' . $random_id . '-frame");
            var muteBtn = document.getElementById("' . $random_id . '-mute-btn");
            var volSlider = document.getElementById("' . $random_id . '-vol-slider");
            var volSliderWrap = document.getElementById("' . $random_id . '-vol-slider-wrap");
            var volIcon = document.getElementById("' . $random_id . '-vol-icon");

            var playPath = "M8 5v14l11-7z";
            var pausePath = "M6 19h4V5H6v14zm8-14v14h4V5h-4z";
            var volIconMuted = "M16.5 12A4.5 4.5 0 0014 8.14v2.07l2.45 2.45c.03-.21.05-.43.05-.66zm2.5 0c0 .93-.21 1.82-.58 2.61l1.47 1.47A8.94 8.94 0 0021 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06a8.99 8.99 0 003.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z";
            var volIconLow = "M7 9v6h4l5 5V4L7 9z";
            var volIconMed = "M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86z";
            var volIconHigh = "M3 9v6h4l5 5V4L7 9H3zm13.5 3A4.5 4.5 0 0014 8.14v7.72c1.48-.73 2.5-2.25 2.5-3.86zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z";

            function formatTime(sec) {
                if (!sec || isNaN(sec)) return "0:00";
                sec = Math.floor(sec);
                var m = Math.floor(sec / 60);
                var s = sec % 60;
                return m + ":" + (s < 10 ? "0" : "") + s;
            }

            // Show control bar once player exists
            if (controlBar) controlBar.style.display = "flex";

            // --- Play / Pause ---
            if (playBtn) {
                playBtn.addEventListener("click", function() {
                    if (!ytPlayer) return;
                    var state = ytPlayer.getPlayerState();
                    if (state === 1) { ytPlayer.pauseVideo(); }
                    else { ytPlayer.playVideo(); }
                });
            }

            function updatePlayIcon() {
                if (!ytPlayer || !playIcon) return;
                var state = ytPlayer.getPlayerState();
                playIcon.querySelector("path").setAttribute("d", state === 1 ? pausePath : playPath);
            }

            // --- Progress bar ---
            var progressAnimId = null;
            var isSeeking = false;
            var progressLoopStarted = false;

            function startProgressLoop() {
                if (progressLoopStarted) return;
                progressLoopStarted = true;
                progressAnimId = requestAnimationFrame(updateProgress);
            }

            function updateProgress() {
                if (!ytPlayer || isSeeking) { progressAnimId = requestAnimationFrame(updateProgress); return; }
                try {
                    var current = typeof ytPlayer.getCurrentTime === "function" ? ytPlayer.getCurrentTime() : 0;
                    var duration = typeof ytPlayer.getDuration === "function" ? ytPlayer.getDuration() : 0;
                    if (duration > 0) {
                        var pct = (current / duration) * 100;
                        progressBar.style.width = pct + "%";
                        progressThumb.style.left = pct + "%";
                        timeCurrent.textContent = formatTime(current);
                        timeDuration.textContent = formatTime(duration);
                    }
                    if (typeof ytPlayer.getVideoLoadedFraction === "function") {
                        progressBuffered.style.width = (ytPlayer.getVideoLoadedFraction() * 100) + "%";
                    }
                    updatePlayIcon();
                } catch (e) {
                    // Player API not ready yet, retry on next frame
                }
                progressAnimId = requestAnimationFrame(updateProgress);
            }

            // Seek on click / drag
            if (progressWrap) {
                function seekFromEvent(e) {
                    var rect = progressWrap.getBoundingClientRect();
                    var x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
                    var pct = x / rect.width;
                    var duration = typeof ytPlayer.getDuration === "function" ? ytPlayer.getDuration() : 0;
                    if (duration > 0 && ytPlayer) {
                        ytPlayer.seekTo(pct * duration, true);
                        progressBar.style.width = (pct * 100) + "%";
                        progressThumb.style.left = (pct * 100) + "%";
                        timeCurrent.textContent = formatTime(pct * duration);
                    }
                }

                progressWrap.addEventListener("mousedown", function(e) {
                    isSeeking = true;
                    seekFromEvent(e);
                    function onMove(e) { seekFromEvent(e); }
                    function onUp() {
                        isSeeking = false;
                        document.removeEventListener("mousemove", onMove);
                        document.removeEventListener("mouseup", onUp);
                    }
                    document.addEventListener("mousemove", onMove);
                    document.addEventListener("mouseup", onUp);
                });

                progressWrap.addEventListener("mouseenter", function() {
                    progressThumb.style.display = "block";
                });
                progressWrap.addEventListener("mouseleave", function() {
                    if (!isSeeking) progressThumb.style.display = "none";
                });
            }

            // --- Volume ---
            var volHideTimer = null;
            var localVol = 100;
            var localMuted = false;

            function updateVolIconFromLocal() {
                if (!volIcon) return;
                var path;
                if (localMuted || localVol === 0) { path = volIconMuted; }
                else if (localVol < 40) { path = volIconLow; }
                else if (localVol < 75) { path = volIconMed; }
                else { path = volIconHigh; }
                volIcon.querySelector("path").setAttribute("d", path);
            }

            // Called from schedule360AvailabilityChecks — only syncs icon, never touches slider
            function updateVolIcon() {
                updateVolIconFromLocal();
            }

            function showVolSlider() {
                if (volHideTimer) { clearTimeout(volHideTimer); volHideTimer = null; }
                if (volSliderWrap) volSliderWrap.style.display = "flex";
            }
            function hideVolSlider() {
                volHideTimer = setTimeout(function() { if (volSliderWrap) volSliderWrap.style.display = "none"; }, 400);
            }

            if (muteBtn) {
                muteBtn.addEventListener("click", function() {
                    if (!ytPlayer) return;
                    if (localMuted) {
                        ytPlayer.unMute();
                        localMuted = false;
                        if (localVol === 0) { localVol = 100; ytPlayer.setVolume(100); }
                        if (volSlider) volSlider.value = localVol;
                    } else {
                        ytPlayer.mute();
                        localMuted = true;
                        if (volSlider) volSlider.value = 0;
                    }
                    updateVolIconFromLocal();
                    updateVolSliderFill();
                });
                muteBtn.addEventListener("mouseenter", showVolSlider);
                muteBtn.addEventListener("mouseleave", hideVolSlider);
            }
            if (volSliderWrap) {
                volSliderWrap.addEventListener("mouseenter", showVolSlider);
                volSliderWrap.addEventListener("mouseleave", hideVolSlider);
            }
            function updateVolSliderFill() {
                if (!volSlider) return;
                var v = parseInt(volSlider.value, 10);
                volSlider.style.background = "linear-gradient(to right,#fff " + v + "%,rgba(255,255,255,0.3) " + v + "%)";
            }
            if (volSlider) {
                volSlider.addEventListener("input", function() {
                    if (!ytPlayer) return;
                    var val = parseInt(volSlider.value, 10);
                    localVol = val;
                    ytPlayer.setVolume(val);
                    if (val === 0) {
                        ytPlayer.mute();
                        localMuted = true;
                    } else if (localMuted) {
                        ytPlayer.unMute();
                        localMuted = false;
                    }
                    updateVolIconFromLocal();
                    updateVolSliderFill();
                });
                updateVolSliderFill();
            }

            // --- Fullscreen ---
            if (outerContainer) {
                var savedFrameWidth = frameEl ? frameEl.style.width : "";
                var savedFrameHeight = frameEl ? frameEl.style.height : "";
                var savedFrameMaxWidth = frameEl ? frameEl.style.maxWidth : "";
                var savedFrameBorderRadius = frameEl ? frameEl.style.borderRadius : "";
                var savedContainerBackground = outerContainer.style.background;
                if(fsBtn){
                    fsBtn.addEventListener("click", function() {
                        var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
                        if (fsEl) {
                            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
                        } else {
                            (outerContainer.requestFullscreen || outerContainer.webkitRequestFullscreen).call(outerContainer);
                        }
                    });
                }
                function onFsChange() {
                    var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
                    var isFs = (fsEl === outerContainer);
                    frameEl.style.width = isFs ? "100%" : savedFrameWidth;
                    frameEl.style.height = isFs ? "100%" : savedFrameHeight;
                    frameEl.style.maxWidth = isFs ? "100%" : savedFrameMaxWidth;
                    frameEl.style.borderRadius = isFs ? "0" : savedFrameBorderRadius;
                    outerContainer.style.background = isFs ? "#000" : savedContainerBackground;
                    fsBtn.querySelector("svg path").setAttribute("d", isFs
                        ? "M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"
                        : "M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"
                    );
                    schedule360AvailabilityChecks();
                }
                document.addEventListener("fullscreenchange", onFsChange);
                document.addEventListener("webkitfullscreenchange", onFsChange);
            }

            // Sync controls after player ready
            var origSchedule = schedule360AvailabilityChecks;
            schedule360AvailabilityChecks = function() {
                origSchedule();
                updateVolIcon();
                updatePlayIcon();
            };
        });
    </script>';
        $html .= '</div>'; // Close container
        $html .= '</div>'; // Close outer container
        return $html;
    }
    /**
     * Prepare shortcode data for Vimeo url
     * 
     * @param array $postdata
     * @param mixed $width
     * @param mixed $height
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_vimeo_video_shortcode_data($postdata, $width, $height, $autoplay, $loop, $radius)
    {
        $autoplay_param = ($autoplay == 'on') ? 'autoplay=1&muted=1' : '';
        $loop_param     = ($loop == 'on') ? 'loop=1' : '';

        $vidurl_parts = explode('/', rtrim( $postdata['vidurl'], '/' ) );
        $vid_id       = end($vidurl_parts);

        $query_parts = array_filter( [ $autoplay_param, $loop_param ] );
        $foundid     = !empty($query_parts) ? $vid_id . '?' . implode('&', $query_parts) : $vid_id;

        $iframe_id   = 'wpvr-vimeo-' . wp_wp_rand( 10000, 99999 );
        $do_autoplay = ( $autoplay == 'on' ) ? 'true' : 'false';
        $do_loop     = ( $loop     == 'on' ) ? 'true' : 'false';

        $html = '';
        $html .= '<div style="text-align: center; max-width:100%; width:' . $width . '; height:' . $height . '; margin: 0 auto;">';
        $html .= '<iframe id="' . esc_attr( $iframe_id ) . '" src="https://player.vimeo.com/video/' . esc_attr( $foundid ) . '" width="' . trim($width, 'px') . '" height="' . trim($height, 'px') . '" style="border-radius: ' . $radius . ';" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        $html .= '</div>';
        $html .= '<script>
(function() {
    var IFRAME_ID   = ' . wp_json_encode( $iframe_id ) . ';
    var DO_AUTOPLAY = ' . $do_autoplay . ';
    var DO_LOOP     = ' . $do_loop . ';

    function wpvrLoadVimeoSdk( callback ) {
        if ( window.Vimeo && window.Vimeo.Player ) { callback(); return; }
        window.wpvrVimeoSdkCallbacks = window.wpvrVimeoSdkCallbacks || [];
        window.wpvrVimeoSdkCallbacks.push( callback );
        if ( window.wpvrVimeoSdkLoading ) { return; }
        window.wpvrVimeoSdkLoading = true;
        var tag = document.createElement( "script" );
        tag.src = "https://player.vimeo.com/api/player.js";
        tag.onload = function() {
            var cbs = window.wpvrVimeoSdkCallbacks || [];
            while ( cbs.length ) { ( cbs.shift() )(); }
        };
        document.head.appendChild( tag );
    }

    function initVimeoPlayer() {
        var iframeEl = document.getElementById( IFRAME_ID );
        if ( !iframeEl ) { return; }
        var player = new Vimeo.Player( iframeEl );

        player.ready().then( function() {
            if ( DO_LOOP ) {
                player.setLoop( true ).catch( function() {} );
            }
            if ( DO_AUTOPLAY ) {
                player.setVolume( 0 ).then( function() {
                    player.play().catch( function() {} );
                } ).catch( function() {
                    player.play().catch( function() {} );
                } );
            }
        } );
    }

    if ( document.readyState === "loading" ) {
        document.addEventListener( "DOMContentLoaded", function() { wpvrLoadVimeoSdk( initVimeoPlayer ); } );
    } else {
        wpvrLoadVimeoSdk( initVimeoPlayer );
    }
}());
</script>';
        return $html;
    }


    /**
     * Prepare shortcode data from regular postdata
     * 
     * @param integer $id
     * @param array $postdata
     * @param string $width
     * @param string $height
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_regular_video_shortcode_data($id, $postdata, $width, $height, $radius)
    {
        $html = '';
        $html .= '<div id="pano' . $id . '" class="pano-wrap" style="max-width:100%; width: ' . $width . '; height: ' . $height . '; border-radius: ' . $radius . '; margin: 0 auto;">';
        $html .= '<div style="width:100%; height:100%; ">' . $postdata['panoviddata'] . '</div>';

        $html .= '
        <style>
            .video-js {
                border-radius:' . $radius . ';
            }
            .video-js canvas{
                border-radius:' . $radius . ';
             }
            #pano' . $id . ' .vjs-poster {
                border-radius: ' . $radius . ';
               }
        </style>
        
        ';

        $html .= '</div>';

        //video js vr setup //
        $html .= '<script>';
        $html .= '
        (function (window, videojs) {
            var player = window.player = videojs("' . $postdata['vidid'] . '");
            player.mediainfo = player.mediainfo || {};
            player.mediainfo.projection = "equirectangular";
      
            // AUTO is the default and looks at mediainfo
            var vr = window.vr = player.vr({ projection: "AUTO", debug: true, forceCardboard: false, antialias: false });
          }(window, window.videojs));
        
        ';
        $html .= '</script>';
        //video js vr end //
        return $html;
    }


    /**
     * Prepare scene data for shortcode
     *
     * @param array $panodata
     *
     * @return array
     * @since 8.0.0
     */
    public function prepare_shortcode_scene_data($panodata)
    {
        $scene_data = array();

        if (!empty($panodata["scene-list"])) {
            $scene_data = $this->prepare_shortcode_scene_list($panodata);
        }

        return $scene_data;
    }


    /**
     * Prepare scene lists data for shortcode
     *
     * @param array $panodata
     *
     * @return array
     * @since 8.0.0
     */
    private function prepare_shortcode_scene_list($panodata)
    {
        $scene_data = array();
        foreach ($panodata["scene-list"] as $panoscenes) {
            $hotspot_datas = array();
            if (isset($panoscenes["hotspot-list"])) {
                $hotspot_datas = $panoscenes["hotspot-list"];
            }
            $hotspots = $this->prepare_shortcode_hotspots_list($hotspot_datas);
            $device_scene = $this->get_shortcode_device_scene($panoscenes['scene-attachment-url']);
            $scene_info = $this->get_shortcode_scene_info($panoscenes, $hotspots, $device_scene);
            $scene_data[$panoscenes["scene-id"]] = $scene_info;
        }
        return $scene_data;
    }


    /**
     * Prepare hotspots list for shortcode
     *
     * @param array $hotspot_datas
     *
     * @return array
     * @since 8.0.0
     */
    private function prepare_shortcode_hotspots_list($hotspot_datas)
    {
        $hotspots = array();
        foreach ($hotspot_datas as $hotspot_data) {
            $hotspot_type = $hotspot_data["hotspot-type"] !== 'scene' ? 'info' : $hotspot_data["hotspot-type"];
            $hotspot_content = $hotspot_data["hotspot-content"];
            $hotspot_info = $this->get_shortcode_hotspot_info($hotspot_data, $hotspot_type, $hotspot_content);
            array_push($hotspots, $hotspot_info);
        }
        return $hotspots;
    }


    /**
     * Return hotspot info array to hotspot list
     *
     * @param array $hotspot_data
     * @param string $hotspot_type
     * @param string $hotspot_content
     *
     * @return array
     * @since 8.0.0
     */
    private function get_shortcode_hotspot_info($hotspot_data, $hotspot_type, $hotspot_content)
    {
        $hotspot_info = array(
            "text" => $hotspot_data["hotspot-title"],
            "pitch" => $hotspot_data["hotspot-pitch"],
            "yaw" => $hotspot_data["hotspot-yaw"],
            "type" => $hotspot_type,
            "cssClass" => $hotspot_data["hotspot-customclass"],
            "URL" => $hotspot_data["hotspot-url"],
            "wpvr_url_open" => isset($hotspot_data["wpvr_url_open"][0]) ? $hotspot_data["wpvr_url_open"][0] : 'off',
            "clickHandlerArgs" => $hotspot_content,
            "createTooltipArgs" => $hotspot_data["hotspot-hover"],
            "sceneId" => $hotspot_data["hotspot-scene"],
            'hotspot_type' => $hotspot_data['hotspot-type']
        );

        $hotspot_info['URL'] = ($hotspot_data['hotspot-type'] === 'fluent_form' || $hotspot_data['hotspot-type'] === 'wc_product') ? '' : $hotspot_info['URL'];

        if ($hotspot_data["hotspot-customclass"] == 'none' || $hotspot_data["hotspot-customclass"] == '') {
            unset($hotspot_info["cssClass"]);
        }
        if (empty($hotspot_data["hotspot-scene"])) {
            unset($hotspot_info['targetPitch']);
            unset($hotspot_info['targetYaw']);
        }
        return $hotspot_info;
    }


    /**
     * Return scene info array for scene list
     *
     * @param array $panoscenes
     * @param array $hotspots
     * @param string $device_scene
     *
     * @return array
     * @since 8.0.0
     */
    private function get_shortcode_scene_info($panoscenes, $hotspots, $device_scene)
    {
        $scene_info = array();
        if ($panoscenes["scene-type"] == 'cubemap') {
            $pano_attachment = array(
                $panoscenes["scene-attachment-url-face0"],
                $panoscenes["scene-attachment-url-face1"],
                $panoscenes["scene-attachment-url-face2"],
                $panoscenes["scene-attachment-url-face3"],
                $panoscenes["scene-attachment-url-face4"],
                $panoscenes["scene-attachment-url-face5"]
            );
            $scene_info = array("type" => $panoscenes["scene-type"], "cubeMap" => $pano_attachment, "hotSpots" => $hotspots);
        } else {
            $scene_info = array("type" => $panoscenes["scene-type"], "panorama" => $device_scene, "hotSpots" => $hotspots);
        }
        return $scene_info;
    }


    /**
     * Return device scene for shortcode
     * 
     * @param string $device_scene
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_device_scene($device_scene)
    {
        $mobile_media_resize = get_option('mobile_media_resize');
        $file_accessible = ini_get('allow_url_fopen');

        if ($mobile_media_resize == "true" && $device_scene) {
            if ($file_accessible == "1") {
                $image_info = getimagesize($device_scene);
                if ($image_info[0] > 4096) {
                    $src_to_id_for_mobile = '';
                    $src_to_id_for_desktop = '';
                    if (wpvr_isMobileDevice()) {
                        $src_to_id_for_mobile = attachment_url_to_postid($device_scene);
                        if ($src_to_id_for_mobile) {
                            $mobile_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'wpvr_mobile');
                            if ($mobile_scene[3]) {
                                $device_scene = $mobile_scene[0];
                            }
                        }
                    } else {
                        $src_to_id_for_desktop = attachment_url_to_postid($device_scene);
                        if ($src_to_id_for_desktop) {
                            $desktop_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'full');
                            if ($desktop_scene[0]) {
                                $device_scene = $desktop_scene[0];
                            }
                        }
                    }
                }
            }
        }
        return $device_scene;
    }


    /**
     * Prepare json response for shortcode
     * 
     * @param string $autorotation
     * @param array $pano_response
     * @param string $panoid
     * @param integer $autorotationinactivedelay
     * @param integer $autorotationstopdelay
     * 
     * @return array
     * @since 8.0.0
     */
    public function prepare_shortcode_response($autorotation, $pano_response, $panoid, $autorotationinactivedelay, $autorotationstopdelay)
    {
        $pano_id_array = array("panoid" => $panoid);
        if (empty($autorotation)) {
            unset($pano_response['autoRotate']);
            unset($pano_response['autoRotateInactivityDelay']);
            unset($pano_response['autoRotateStopDelay']);
        }
        if (empty($autorotationinactivedelay)) {
            unset($pano_response['autoRotateInactivityDelay']);
        }
        if (empty($autorotationstopdelay)) {
            unset($pano_response['autoRotateStopDelay']);
        }
        $response = array();
        $response = array($pano_id_array, $pano_response);
        if (!empty($response)) {
            $response = json_encode($response);
        }
        return $response;
    }


    /**
     * Prepare HTML and script contents for scene shortcode
     * 
     * @param mixed $width
     * @param mixed $panoid
     * @param mixed $hotspoticoncolor
     * @param mixed $foreground_color
     * @param mixed $hotspotblink
     * @param mixed $pulse_color
     * @param mixed $radius
     * @param mixed $id
     * @param mixed $height
     * @param mixed $scene_data
     * @param mixed $response
     * @param mixed $autoload
     * @param mixed $default_scene
     * @param mixed $postdata
     * 
     * @return string
     * @since 8.0.0
     */
    public function prepare_scene_shortcode_html_content($width, $panoid, $hotspoticoncolor, $foreground_color, $hotspotblink, $pulse_color, $radius, $id, $height, $scene_data, $response, $autoload, $default_scene, $postdata, $mobile_height, $gyro)
    {
        $html = '';
        $html .= '<style>';

        if ($width == 'embed') {
            $html .= 'body{overflow: hidden;}';
        }
        // Return ID and Classes for CSS styling //
        $html = $this->get_shortcode_content_classes($html, $panoid, $hotspoticoncolor, $foreground_color);
        // Return webkit keyframes CSS styling //
        if ($hotspotblink == 'on') {
            $html = $this->get_shortcode_webkit_keyframes($html, $panoid, $pulse_color);
        }
        $status  = get_option('wpvr_edd_license_status');
        if ($status !== false && $status == 'valid') {
            if (!$gyro) {
                $html .= '#' . $panoid . ' div.pnlm-orientation-button {
                    display: none;
                }';
            }
        } else {
            $html .= '#' . $panoid . ' div.pnlm-orientation-button {
                    display: none;
                }';
        }
        $html .= '</style>';

        // Render pano wrapper contents //
        $html = $this->get_shortcode_pano_wrap_content($width, $radius, $html, $id, $height, $mobile_height);
        // Render hotspots contents //
        $html = $this->render_shortcode_hotspot_content($html, $id, $scene_data);
        // Render custom iframe content //
        $html = $this->render_shortcode_custom_iframe_wrapper($html, $id);

        $html .= '</div>';

        //script started 
        $html = $this->get_shortcode_script_content($html, $response, $id, $autoload, $default_scene, $postdata);
        //script end
        return $html;
    }


    /**
     * Return ID and Classes for CSS styling
     * 
     * @param mixed $html
     * @param mixed $panoid
     * @param mixed $hotspoticoncolor
     * @param mixed $foreground_color
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_content_classes($html, $panoid, $hotspoticoncolor, $foreground_color)
    {
        $html .= '#' . $panoid . ' div.pnlm-hotspot-base.fas,
        #' . $panoid . ' div.pnlm-hotspot-base.fab,
        #' . $panoid . ' div.pnlm-hotspot-base.fa,
        #' . $panoid . ' div.pnlm-hotspot-base.fa-solid,
        #' . $panoid . ' div.pnlm-hotspot-base.far {
            display: block !important;
            background-color: ' . $hotspoticoncolor . ';
            color: ' . $foreground_color . ';
            border-radius: 100%;
            width: 30px;
            height: 30px;
            animation: icon-pulse' . $panoid . ' 1.5s infinite cubic-bezier(.25, 0, 0, 1);
        }';
        return $html;
    }


    /**
     * Resturn webkit keyframes CSS styling 
     * 
     * @param mixed $html
     * @param mixed $panoid
     * @param mixed $pulse_color
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_webkit_keyframes($html, $panoid, $pulse_color)
    {
        $html .= '@-webkit-keyframes icon-pulse' . $panoid . ' {
            0% {
                box-shadow: 0 0 0 0px rgba(' . $pulse_color[0] . ', 1);
            }
            100% {
                box-shadow: 0 0 0 10px rgba(' . $pulse_color[0] . ', 0);
            }
        }
        @keyframes icon-pulse' . $panoid . ' {
            0% {
                box-shadow: 0 0 0 0px rgba(' . $pulse_color[0] . ', 1);
            }
            100% {
                box-shadow: 0 0 0 10px rgba(' . $pulse_color[0] . ', 0);
            }
        }';
        return $html;
    }


    /**
     * Render pano wrapper content depending on Tour Width, Height and radius
     * 
     * @param mixed $width
     * @param mixed $radius
     * @param mixed $html
     * @param mixed $id
     * @param mixed $height
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_pano_wrap_content($width, $radius, $html, $id, $height, $mobile_height)
    {
        if ($width == 'fullwidth') {

            if (wpvr_isMobileDevice()) {
                if ($radius) {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style="text-align:center; border-radius:' . $radius . '; direction:ltr;">';
                } else {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style="text-align:center;">';
                }
            } else {
                if ($radius) {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap vrfullwidth" style=" text-align:center; height: ' . $height . '; border-radius:' . $radius . '; direction:ltr;" >';
                } else {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap vrfullwidth" style=" text-align:center; height: ' . $height . '; direction:ltr;" >';
                }
            }
        } elseif ($width == 'embed') {
            $html .= '<div id="pano' . $id . '" class="pano-wrap vrembed" style=" text-align:center; direction:ltr;" >';
        } else {
            if (wpvr_isMobileDevice()) {
                if ($radius) {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style=" text-align:center; max-width:100%; width: ' . $width . '; height: ' . $mobile_height . '!important; margin: 0 auto; border-radius:' . $radius . '; direction:ltr;">';
                } else {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style=" text-align:center; max-width:100%; width: ' . $width . '; height: ' . $mobile_height . '!important; margin: 0 auto; direction:ltr;">';
                }
            } else {
                if ($radius) {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style=" text-align:center; max-width:100%; width: ' . $width . '; height: ' . $height . '; margin: 0 auto; border-radius:' . $radius . '; direction:ltr;">';
                } else {
                    $html .= '<div id="pano' . $id . '" class="pano-wrap" style=" text-align:center; max-width:100%; width: ' . $width . '; height: ' . $height . '; margin: 0 auto; direction:ltr;">';
                }
            }
        }
        return $html;
    }


    /**
     * Render hotspot contents inside pano wrapper
     * 
     * @param string $html
     * @param mixed $id
     * @param array $scene_data
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_shortcode_hotspot_content($html, $id, $scene_data)
    {
        $html .= '<div class="wpvr-hotspot-tweak-contents-wrapper" style="display: none">';
        $html .= '<i class="fa fa-times cross" data-id="' . $id . '"></i>';
        $html .= '<div class="wpvr-hotspot-tweak-contents-flex">';
        $html .= '<div class="wpvr-hotspot-tweak-contents">';
        ob_start();
        do_action('wpvr_hotspot_tweak_contents', $scene_data);
        $hotspot_content = ob_get_clean();
        $html .= $hotspot_content;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }


    /**
     * Render custom iframe wrapper inside pano wrapper
     * 
     * @param string $html
     * @param mixed $id
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_shortcode_custom_iframe_wrapper($html, $id)
    {
        $html .= '<div class="custom-ifram-wrapper" style="display: none;">';
        $html .= '<i class="fa fa-times cross" data-id="' . $id . '"></i>';

        $html .= '<div class="custom-ifram-flex">';
        $html .= '<div class="custom-ifram">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }


    /**
     * Return scripts contents for shortcode
     * 
     * @param string $html
     * @param mixed $response
     * @param mixed $id
     * @param mixed $autoload
     * @param mixed $default_scene
     * @param mixed $postdata
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_script_content($html, $response, $id, $autoload, $default_scene, $postdata)
    {
        $html .= '<script>';
        $html .= 'jQuery(document).ready(function() {';
        $html .= 'var response = ' . $response . ';';
        $html .= 'var scenes = response[1];';
        // Hotspot scripts content //
        $html = $this->get_shortcode_hotspot_script_content($html);

        $html .= 'var panoshow' . $id . ' = pannellum.viewer(response[0]["panoid"], scenes);';

        // Panaromic show scripts content //
        $html = $this->render_shortcode_panoshow_script_content($html, $id);
        // Auto rotation scripts content //
        $html = $this->render_shortcode_autoRotate_content($html, $id);

        $html .= 'var touchtime = 0;';

        // Return autoload scripts content //
        if (!$autoload) {
            $html = $this->render_shortcode_autoload_content($html, $id);
        }

        // JQuery scripts for on-click content for elementor tab //
        $previeword = "Click to Load Panorama";
        if (isset($postdata['previewtext']) && $postdata['previewtext'] != '') {
            $previeword = $postdata['previewtext'];
        }
        $html = $this->render_elementor_tab_title_click_content($html, $id, $default_scene, $previeword);
        // JQuery scripts for on-click content for vr tour tab //
        $html = $this->render_vr_tour_tab_click_content($html, $id, $default_scene);

        if (isset($previeword ) && $previeword  != '') {
            $html .= 'jQuery("#pano' . $id . '").children(".pnlm-ui").find(".pnlm-load-button p").text("' . $previeword  . '")';
        }

        $html .= '});';
        $html .= '</script>';
        return $html;
    }


    /**
     * Return scripts content for hotspot data on shortcode
     * 
     * @param mixed $html
     * 
     * @return string
     * @since 8.0.0
     */
    private function get_shortcode_hotspot_script_content($html)
    {
        $html .= 'if(scenes) {';
        $html .= 'var scenedata = scenes.scenes;';
        $html .= 'for(var i in scenedata) {';
        $html .= 'var scenehotspot = scenedata[i].hotSpots;';
        $html .= 'for(var i = 0; i < scenehotspot.length; i++) {';
        $html .= 'if(scenehotspot[i]["clickHandlerArgs"] != "") {';
        $html .= 'scenehotspot[i]["clickHandlerFunc"] = wpvrhotspot;';
        $html .= '}';
        if (wpvr_isMobileDevice() && get_option('dis_on_hover') == "true") {
        } else {
            $html .= 'if(scenehotspot[i]["createTooltipArgs"] != "") {';
            $html .= 'scenehotspot[i]["createTooltipFunc"] = wpvrtooltip;';
            $html .= '}';
        }
        $html .= '}';
        $html .= '}';
        $html .= '}';
        return $html;
    }


    /**
     * Render scripts content for panaromic show on shortcode
     * 
     * @param mixed $html
     * @param mixed $id
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_shortcode_panoshow_script_content($html, $id)
    {
        $html .= 'panoshow' . $id . '.on("load", function (){
                    setTimeout(() => {
                        window.dispatchEvent(new Event("resize"));
                    }, 200);
					if (jQuery("#pano' . $id . '").children().children(".pnlm-panorama-info:visible").length > 0) {
	                    jQuery("#controls' . $id . '").css("bottom", "55px");
	                }
	                else {
	                     jQuery("#controls' . $id . '").css("bottom", "5px");
	                }
				});';
        $html .= 'panoshow' . $id . '.on("render", function (){
                    window.dispatchEvent(new Event("resize"));
                });';
        return $html;
    }


    /**
     * Return auto rotation scripts content on shortcode
     * 
     * @param mixed $html
     * @param mixed $id
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_shortcode_autoRotate_content($html, $id)
    {
        $html .= 'if (scenes.autoRotate) {
            panoshow' . $id . '.on("load", function (){
                setTimeout(function(){ panoshow' . $id . '.startAutoRotate(scenes.autoRotate, 0); }, 3000);
            });
            panoshow' . $id . '.on("scenechange", function (){
                setTimeout(function(){ panoshow' . $id . '.startAutoRotate(scenes.autoRotate, 0); }, 3000);
            });
        }';
        return $html;
    }


    /**
     * Return autoload scripts content on shortcode
     * 
     * @param mixed $html
     * @param mixed $id
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_shortcode_autoload_content($html, $id)
    {
        $html .= 'jQuery(document).ready(function(){
                jQuery("#controls' . $id . '").hide();
                jQuery("#zoom-in-out-controls' . $id . '").hide();
                jQuery("#adcontrol' . $id . '").hide();
                jQuery("#pano' . $id . '").find(".pnlm-panorama-info").hide();
            });';

        $html .= 'panoshow' . $id . '.on("load", function (){
                jQuery("#controls' . $id . '").show();
                jQuery("#zoom-in-out-controls' . $id . '").show();
                jQuery("#adcontrol' . $id . '").show();
                jQuery("#pano' . $id . '").find(".pnlm-panorama-info").show();
            });';
        return $html;
    }


    /**
     * JQuery scripts for on-click content for elementor tab
     * 
     * @param mixed $html
     * @param mixed $id
     * @param mixed $default_scene
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_elementor_tab_title_click_content($html, $id, $default_scene, $previeword)
    {
        $html .= 'jQuery(".elementor-tab-title").click(function(){
            var element_id;
            var pano_id;
            var element_id = this.id;
            element_id = element_id.split("-");
            element_id = element_id[3];
            jQuery("#elementor-tab-content-"+element_id).children("div").addClass("awwww");
            var pano_id = jQuery(".awwww").attr("id");
            jQuery("#elementor-tab-content-"+element_id).children("div").removeClass("awwww");
            if (pano_id != undefined) {
              pano_id = pano_id.split("o");
              pano_id = pano_id[1];
              if (pano_id == "' . $id . '") {
                jQuery("#pano' . $id . '").children(".pnlm-render-container").remove();
                jQuery("#pano' . $id . '").children(".pnlm-ui").remove();
                panoshow' . $id . ' = pannellum.viewer(response[0]["panoid"], scenes);
                jQuery("#pano' . $id . '").children(".pnlm-ui").find(".pnlm-load-button p").text("' . $previeword . '")
                setTimeout(function() {
                        // panoshow' . $id . '.loadScene("' . $default_scene . '");
                        window.dispatchEvent(new Event("resize"));
                        if (jQuery("#pano' . $id . '").children().children(".pnlm-panorama-info:visible").length > 0) {
                             jQuery("#controls' . $id . '").css("bottom", "55px");
                         }
                         else {
                           jQuery("#controls' . $id . '").css("bottom", "5px");
                         }
                }, 200);
              }
            }
        });';
        return $html;
    }


    /**
     * JQuery scripts for on-click content for vr tour tab
     * 
     * @param mixed $html
     * @param mixed $id
     * @param mixed $default_scene
     * 
     * @return string
     * @since 8.0.0
     */
    private function render_vr_tour_tab_click_content($html, $id, $default_scene)
    {
        $html .= 'jQuery(".geodir-tab-head dd, #vr-tour-tab").click(function(){
            jQuery("#pano' . $id . '").children(".pnlm-render-container").remove();
            jQuery("#pano' . $id . '").children(".pnlm-ui").remove();
            panoshow' . $id . ' = pannellum.viewer(response[0]["panoid"], scenes);
            setTimeout(function() {
                    panoshow' . $id . '.loadScene("' . $default_scene . '");
                    window.dispatchEvent(new Event("resize"));
                    if (jQuery("#pano' . $id . '").children().children(".pnlm-panorama-info:visible").length > 0) {
                         jQuery("#controls' . $id . '").css("bottom", "55px");
                     }
                     else {
                       jQuery("#controls' . $id . '").css("bottom", "5px");
                     }
            }, 200);
          });';
        return $html;
    }
}
