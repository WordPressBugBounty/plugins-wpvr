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
        $panolist = stripslashes($panodata);
        $panodata = (array)json_decode($panolist);
        $panolist = array();
        if (is_array($panodata["scene-list"])) {
            foreach ($panodata["scene-list"] as $scenes_data) {
                $temp_array = array();
                $temp_array = (array)$scenes_data;

                if ($temp_array['hotspot-list']) {
                    $_hotspot_array = array();
                    foreach ($temp_array['hotspot-list'] as $temp_hotspot) {

                        $temp_hotspot = (array)$temp_hotspot;
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
        $explodeid = '';
        $explodeid = explode("/", $videourl);

        if ($videodata['autoplay'] == 'on') {
            $autoplay = '&autoplay=1&muted=1';
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
        $html .= '<iframe src="https://player.vimeo.com/video/' . $foundid . '" width="600" height="400" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
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
        $explodeid = '';
        $explodeid = explode("=", $videourl);

        if ($videodata['autoplay'] == 'on') {
            $autoplay = '&autoplay=1';
            $muted = '&mute=1';
        } else {
            $autoplay = '';
            $muted = '';
        }

        if ($videodata['loop'] == 'on') {
            $loop = '&loop=1';
        } else {
            $loop = '';
        }

        $html = '';
        
        $html .= '
            <iframe src="https://www.youtube.com/embed/' . $explodeid[1] . '?rel=0&modestbranding=1' . $loop . '&autohide=1' . $muted . '&showinfo=0&controls=1' . $autoplay . '"  width="600" height="400"  frameborder="0" allowfullscreen></iframe>
        ';
        return $html;
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
    public function preapre_youtube_video_shortcode_data($postdata, $width, $height, $autoplay, $loop, $radius)
    {
        $explodeid = '';
        $explodeid = explode("=", $postdata['vidurl']);

        $foundid = '';
        $muted = '&mute=1';
        $autoplay = ($autoplay == 'on') ? '&autoplay=1' : '';
        $loop = ($loop == 'on') ? '&loop=1' : '';

        if (strpos($postdata['vidurl'], 'youtu') > 0) {
            $explodeid = explode("/", $postdata['vidurl']);
            $foundid = $explodeid[3] . '?' . $autoplay . $loop;
            $expdata = $explodeid[3];
        } else {
            $foundid = $explodeid[1] . '?' . $autoplay . $loop;
            $expdata = $explodeid[1];
        }

        $playlist = '&playlist='. $expdata;
        $playlist = str_replace("?feature=shared","",$playlist);

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
                details: []
            };

            // Detect mobile devices
            support.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            if (support.isMobile) {
                support.details.push("Mobile device detected");
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
            support.fullySupported = support.webgl && 
                ((support.isMobile && support.orientation) || !support.isMobile);

            // Browser-specific warnings
            if (support.browser === "safari" && /iPhone|iPad|iPod/.test(navigator.userAgent)) {
                support.browserWarning = "iPhone has limited support for 360 videos in browser. The experience may not be optimal.";
                support.fullySupported = false;
            } else if (support.browser === "ie") {
                support.browserWarning = "Internet Explorer has limited support for 360 videos. The experience may not be optimal.";
                support.fullySupported = false;
            } else if (!support.supported) {
                support.browserWarning = "Your browser does not fully support 360° videos. For the best experience, please use a modern browser with WebGL support.";
            }

            return support;
        }
    </script>';

        $random_id = 'video-container-' . rand(10000, 99999);
        $html .= '<div id="' . $random_id . '-container" style="position:relative; width:100%; height:100%;">';

        // First, always check browser compatibility before showing anything
        $html .= '<div id="' . $random_id . '-compatibility-check" style="position:none; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:#f9f9f9; border-radius:' . $radius . ';">
                    <div style="margin-bottom:20px; text-align:center;">
                        <div class="wpvr-loading-spinner" style="border:5px solid #f3f3f3; border-top:5px solid #3498db; border-radius:50%; width:50px; height:50px; margin:0 auto 15px; animation:wpvr-spin 1s linear infinite;"></div>
                        <p style="margin:0;">Checking browser compatibility...</p>
                    </div>
                   </div>';

        // Add animation for spinner
        $html .= '<style>@keyframes wpvr-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';

        // Add the iframe (initially hidden)
        $html .= '<div id="' . esc_attr( $random_id . '-frame' ) . '" style="text-align:center; max-width:100%; width:' . esc_attr( $width ) . '; height:' . esc_attr( $height ) . '; border-radius:' . esc_attr( $radius ) . '; margin: 0 auto; display:none;">';
        $html .= '<iframe id="' . esc_attr( $random_id . '-iframe' ) . '" src="https://www.youtube.com/embed/' . rawurlencode( sanitize_text_field( $expdata ) ) . '?rel=0&modestbranding=1' . $loop . '&autohide=1' . $muted . '&showinfo=0&controls=1' . $autoplay . $playlist . '&enablejsapi=1" width="100%" height="100%" style="border-radius:' . esc_attr( $radius ) . ';" frameborder="0" allowfullscreen allow="accelerometer; gyroscope; picture-in-picture"></iframe>';
        $html .= '</div>';

        // Add permission request button (hidden by default) - Only shown for fully supported devices
        $html .= '<div id="' . $random_id . '-permission-request" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.7); color:white; text-align:center; border-radius:' . $radius . ';">';
        $html .= '<p style="font-size:16px; margin:0 20px 15px;">For the best 360° video experience on mobile</p>';
        $html .= '<button id="' . $random_id . '-permission-button" style="padding:10px 15px; background-color:#0085ba; color:#fff; border:none; border-radius:4px; cursor:pointer;">Allow motion and orientation access</button>';
        $html .= '</div>';

        // Add browser compatibility warning message
        $html .= '<div id="' . $random_id . '-browser-warning" style="position:absolute; top:0; left:0; width:100%; height:100%; display:none; flex-direction:column; justify-content:center; align-items:center; background-color:rgba(0,0,0,0.7); color:white; text-align:center; border-radius:' . $radius . ';">';
        $html .= '<p id="' . $random_id . '-warning-text" style="font-size:16px; margin:0 20px 5px;"></p>';
        $html .= '<p style="font-size:14px; margin:5px 20px 15px;">For the best experience, consider using Chrome or Firefox.</p>';
        $html .= '<button id="' . $random_id . '-browser-continue" style="padding:10px 15px; background-color:#0085ba; color:#fff; border:none; border-radius:4px; cursor:pointer;">Continue Anyway</button>';
        $html .= '</div>';

        // Main script for handling compatibility and permissions
        $html .= '<script>
        // Wait for DOM to be fully loaded
        document.addEventListener("DOMContentLoaded", function() {
            // Check compatibility first
            var supportInfo = wpvr_check_360_support();
            let compatibilityCheck = document.getElementById("' . $random_id . '-compatibility-check");
           if(supportInfo.isMobile && !supportInfo.fullySupported) {
                compatibilityCheck.style.display = "flex";
           }
            // Hide the compatibility check screen after a short delay
            setTimeout(function() {
                document.getElementById("' . $random_id . '-compatibility-check").style.display = "none";
                // If browser doesn\'t support 360 videos well, show warning
                if (!supportInfo.supported || supportInfo.browserWarning) {
                    document.getElementById("' . $random_id . '-warning-text").textContent = 
                        supportInfo.browserWarning || "Your browser may not fully support 360° videos.";
                    document.getElementById("' . $random_id . '-browser-warning").style.display = "flex";
                    
                    // Add continue anyway button handler
                    document.getElementById("' . $random_id . '-browser-continue").addEventListener("click", function() {
                        document.getElementById("' . $random_id . '-browser-warning").style.display = "none";
                        document.getElementById("' . $random_id . '-frame").style.display = "block";
                        initializeYouTubePlayer();
                    });
                } 
                // If browser fully supports 360 and is mobile, show permission request
                else if (supportInfo.fullySupported && supportInfo.isMobile) {
                    document.getElementById("' . $random_id . '-permission-request").style.display = "flex";
                    
                    // Add permission request button handler
                    document.getElementById("' . $random_id . '-permission-button").addEventListener("click", function() {
                        requestDevicePermissions();
                    });
                } 
                // Otherwise just show the video
                else {
                    document.getElementById("' . $random_id . '-frame").style.display = "block";
                    initializeYouTubePlayer();
                }
            }, 1000); // Show compatibility check for at least 1 second

            // Function to request device permissions on supported mobile devices
            function requestDevicePermissions() {
                try {
                    if (typeof DeviceOrientationEvent !== "undefined" && 
                        typeof DeviceOrientationEvent.requestPermission === "function") {
                        
                        DeviceOrientationEvent.requestPermission()
                            .then(function(response) {
                                if (response === "granted") {
                                    // Also request motion permission if available
                                    if (typeof DeviceMotionEvent !== "undefined" && 
                                        typeof DeviceMotionEvent.requestPermission === "function") {
                                        
                                        DeviceMotionEvent.requestPermission()
                                            .then(function() {
                                                showVideo();
                                            })
                                            .catch(function() {
                                                showVideo();
                                            });
                                    } else {
                                        showVideo();
                                    }
                                } else {
                                    showVideo();
                                }
                            })
                            .catch(function() {
                                showVideo();
                            });
                    } else {
                        showVideo();
                    }
                } catch (error) {
                    showVideo();
                }
            }

            // Helper function to show video after permissions
            function showVideo() {
                document.getElementById("' . $random_id . '-permission-request").style.display = "none";
                document.getElementById("' . $random_id . '-frame").style.display = "block";
                initializeYouTubePlayer();
            }

            // Function to initialize YouTube player
            function initializeYouTubePlayer() {
                if (window.YT) {
                    initPlayer();
                } else {
                    var tag = document.createElement("script");
                    tag.src = "https://www.youtube.com/iframe_api";
                    var firstScriptTag = document.getElementsByTagName("script")[0];
                    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                    window.onYouTubeIframeAPIReady = initPlayer;
                }

                function initPlayer() {
                    var player = new YT.Player("' . $random_id . '-iframe", {
                        events: {
                            "onReady": function(event) {
                            }
                        }
                    });
                }
            }
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
        $explodeid = '';
        $explodeid = explode("/", $postdata['autoplay']);
        $foundid = '';

        $autoplay = ($autoplay == 'on') ? '&autoplay=1&muted=1' : '';
        $loop = ($loop == 'on') ? '&loop=1' : '';
        if(!isset($explodeid[3])){
            $vidurl = explode('/',$postdata['vidurl']);
            $foundid = end($vidurl);
        }else{
            $foundid = $explodeid[3] . '?' . $autoplay . $loop;
        }
        $html = '';
        $html .= '<div style="text-align: center; max-width:100%; width:' . $width . '; height:' . $height . '; margin: 0 auto;">';
        $html .= '<iframe src="https://player.vimeo.com/video/' . $foundid . '" width="' . trim($width, 'px') . '" height="' . trim($height, 'px') . '" style="border-radius: ' . $radius . ';" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        $html .= '</div>';
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
